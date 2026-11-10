<?php
/**
 * AITF Database Schema Auto-Sync Helper
 * 
 * Automatically ensures all required tables, columns, indexes, and initial data migrations
 * exist in the database on BOTH localhost and LIVE environments.
 * 
 * IMPORTANT ARCHITECTURAL RULE:
 * 1. NEVER manually create/alter tables or columns in MySQL directly without adding them here.
 * 2. All new database structures must be defined in this file idempotently
 *    (using IF NOT EXISTS / SHOW COLUMNS checks).
 * 3. This file is executed automatically on page loads and AJAX requests so that
 *    when code is deployed/pushed to LIVE, the live database is automatically updated
 *    without any manual database intervention.
 */

require_once __DIR__ . '/agreement_sync_helper.php';
require_once __DIR__ . '/payment_ledger.php';
require_once __DIR__ . '/bad_debts_helper.php';

if (!function_exists('aitf_ensure_user_profile_columns')) {
    function aitf_ensure_user_profile_columns($db) {
        if (!$db) return;
        static $userColsEnsured = false;
        if ($userColsEnsured) return;

        try {
            $userCols = [];
            $stmt = $db->query("SHOW COLUMNS FROM `user`");
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $userCols[strtolower($row['Field'])] = true;
                }
            }
            if (!empty($userCols)) {
                if (!isset($userCols['e_id'])) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `e_id` VARCHAR(50) NULL");
                }
                if (!isset($userCols['pc_id'])) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `pc_id` VARCHAR(50) NULL");
                }
                if (!isset($userCols['designation_assigned'])) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `designation_assigned` VARCHAR(255) NULL");
                }
                if (!isset($userCols['applicable_records'])) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `applicable_records` TEXT NULL");
                }
                if (!isset($userCols['work_description'])) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `work_description` TEXT NULL");
                }
                if (!isset($userCols['resource_issue_record'])) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `resource_issue_record` TEXT NULL");
                }
                $userColsEnsured = true;
            }
        } catch (Throwable $e) {
            error_log("Failed to ensure user profile columns: " . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_auto_sync_schema')) {
    function aitf_auto_sync_schema($db) {
        if (!$db) return;
        aitf_ensure_user_profile_columns($db);
        if (function_exists('aitf_payment_schema')) aitf_payment_schema($db);
        if (function_exists('aitf_bad_debts_schema')) aitf_bad_debts_schema($db);

        // Also ensure guidelines schema if library is available
        if (function_exists('aitf_ensure_guidelines_schema')) {
            aitf_ensure_guidelines_schema($db);
        }

        // Also ensure tasks schema
        require_once __DIR__ . '/task_lib.php';
        if (function_exists('aitf_ensure_tasks_schema')) {
            aitf_ensure_tasks_schema($db);
        }

        // Also ensure payment reminder logs schema
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `payment_reminder_mail_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `customer_id` INT NOT NULL,
                `header_key` VARCHAR(150) NULL,
                `service_type` VARCHAR(150) NULL,
                `recipient_email` VARCHAR(255) NOT NULL,
                `cc_emails` VARCHAR(255) NULL,
                `subject` VARCHAR(255) NULL,
                `sent_by` VARCHAR(100) NULL,
                `attachments_count` INT NOT NULL DEFAULT 0,
                `attachments_list` TEXT NULL,
                `sent_at` DATETIME NOT NULL,
                INDEX `idx_prml_cust` (`customer_id`),
                INDEX `idx_prml_sent` (`sent_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {}

        static $alreadySynced = false;
        if ($alreadySynced) {
            return;
        }

        // Check if database actually has the latest schema before honoring temp flag cache
        $dbNeedsSync = false;
        try {
            $colCheck = $db->query("SHOW COLUMNS FROM `user` LIKE 'designation_assigned'");
            if (!$colCheck || $colCheck->rowCount() === 0) {
                $dbNeedsSync = true;
            }
            if (!$dbNeedsSync) {
                $tblCheck = $db->query("SHOW TABLES LIKE 'staff_guidelines'");
                if (!$tblCheck || $tblCheck->rowCount() === 0) {
                    $dbNeedsSync = true;
                }
            }
        } catch (Throwable $e) {
            $dbNeedsSync = true;
        }

        $flagFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'aitf_schema_synced.flag';
        if (!$dbNeedsSync && file_exists($flagFile) && filemtime($flagFile) >= filemtime(__FILE__) && (time() - filemtime($flagFile) < 43200)) {
            $alreadySynced = true;
            return;
        }

        try {
            // 1. Ensure columns in certification_aggrement & certification_agreement (Agreement mail feature)
            foreach (array('certification_aggrement', 'certification_agreement') as $tbl) {
                $aggCols = array();
                try {
                    $stmt = $db->query("SHOW COLUMNS FROM `{$tbl}`");
                    if ($stmt) {
                        while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $aggCols[strtolower($col['Field'])] = true;
                        }
                    }
                } catch (Throwable $e) {
                    continue;
                }

                if (!empty($aggCols)) {
                    if (!isset($aggCols['mail_sent'])) {
                        $db->exec("ALTER TABLE `{$tbl}` ADD COLUMN `mail_sent` TINYINT(1) NOT NULL DEFAULT 0");
                    }
                    if (!isset($aggCols['mail_sent_at'])) {
                        $db->exec("ALTER TABLE `{$tbl}` ADD COLUMN `mail_sent_at` DATETIME NULL");
                    }
                    if (!isset($aggCols['mail_sent_to'])) {
                        $db->exec("ALTER TABLE `{$tbl}` ADD COLUMN `mail_sent_to` VARCHAR(255) NULL");
                    }
                    if (!isset($aggCols['mail_sent_by'])) {
                        $db->exec("ALTER TABLE `{$tbl}` ADD COLUMN `mail_sent_by` VARCHAR(100) NULL");
                    }
                }
            }

            // 2. Ensure audit_notification_sent_log table exists for stage notifications
            try {
                $db->exec("CREATE TABLE IF NOT EXISTS `audit_notification_sent_log` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `service_id` INT NOT NULL DEFAULT 0,
                    `customer_id` INT NOT NULL DEFAULT 0,
                    `stage` VARCHAR(50) NULL,
                    `action` VARCHAR(100) NOT NULL,
                    `sent_date` DATETIME NULL,
                    `recipient_email` VARCHAR(255) NULL,
                    `sent_by` VARCHAR(100) NULL,
                    `status` VARCHAR(20) DEFAULT 'sent',
                    `is_deleted` VARCHAR(5) DEFAULT 'N',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_service_action` (`service_id`, `action`),
                    INDEX `idx_cust_stage` (`customer_id`, `stage`),
                    INDEX `idx_cust_service` (`customer_id`, `service_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                // Auto-migrate any notification records mistakenly logged in ca_service_dates on live
                try {
                    $hasNotiStmt = $db->query("SELECT COUNT(*) FROM `ca_service_dates` WHERE `action` LIKE '%Notification%'");
                    if ($hasNotiStmt && (int)$hasNotiStmt->fetchColumn() > 0) {
                        $db->exec("INSERT INTO `audit_notification_sent_log` (`service_id`, `customer_id`, `stage`, `action`, `sent_date`, `recipient_email`, `sent_by`, `status`, `is_deleted`)
                            SELECT 
                                c.service_id, 
                                COALESCE(s.customer_id, 0) as customer_id,
                                CASE 
                                    WHEN c.action LIKE '%Stage 2%' OR c.action LIKE '%Stage II%' THEN 'stage2'
                                    WHEN c.action LIKE '%SA-I%' OR c.action LIKE '%SA 1%' THEN 'sai'
                                    WHEN c.action LIKE '%SA-II%' OR c.action LIKE '%SA 2%' THEN 'saii'
                                    ELSE 'stage1'
                                END as stage,
                                c.action,
                                c.sent_date,
                                '' as recipient_email,
                                'Migration' as sent_by,
                                'sent' as status,
                                c.is_deleted
                            FROM ca_service_dates c
                            LEFT JOIN service s ON c.service_id = s.service_id
                            WHERE c.action LIKE '%Notification%'
                            AND NOT EXISTS (
                                SELECT 1 FROM audit_notification_sent_log l 
                                WHERE l.service_id = c.service_id AND l.action = c.action
                            )");

                        $db->exec("DELETE FROM `ca_service_dates` WHERE `action` LIKE '%Notification%'");
                    }
                } catch (Throwable $migrateEx) {
                    error_log("Notification migration error: " . $migrateEx->getMessage());
                }
            } catch (Throwable $e) {
                error_log("audit_notification_sent_log creation error: " . $e->getMessage());
            }

            // 2b. Ensure certificate_sent_log table exists for certificate upload emails
            try {
                $db->exec("CREATE TABLE IF NOT EXISTS `certificate_sent_log` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `service_id` INT NOT NULL DEFAULT 0,
                    `customer_id` INT NOT NULL DEFAULT 0,
                    `service_type_id` INT NOT NULL DEFAULT 0,
                    `stage` VARCHAR(50) NOT NULL,
                    `certificate_path` VARCHAR(500) NULL,
                    `standard` VARCHAR(255) NULL,
                    `sent_date` DATETIME NULL,
                    `recipient_email` VARCHAR(255) NULL,
                    `cc_emails` VARCHAR(500) NULL,
                    `sent_by` VARCHAR(100) NULL,
                    `status` VARCHAR(20) DEFAULT 'sent',
                    `is_deleted` VARCHAR(5) DEFAULT 'N',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_cert_service_stage` (`service_id`, `stage`),
                    INDEX `idx_cert_cust_stage` (`customer_id`, `stage`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } catch (Throwable $e) {
                error_log("certificate_sent_log creation error: " . $e->getMessage());
            }

            // 3. Ensure ca_service_dates.sent_date column is DATE (not DATETIME)
            try {
                $colInfo = $db->query("SHOW COLUMNS FROM `ca_service_dates` LIKE 'sent_date'")->fetch(PDO::FETCH_ASSOC);
                if ($colInfo && stripos($colInfo['Type'], 'datetime') !== false) {
                    $db->exec("ALTER TABLE `ca_service_dates` MODIFY COLUMN `sent_date` DATE NULL");
                }
            } catch (Throwable $e) {}

            // 4. Ensure user.e_id, designation_assigned, applicable_records columns exist
            try {
                $uStmt = $db->query("SHOW COLUMNS FROM `user` LIKE 'e_id'");
                if ($uStmt && empty($uStmt->fetchAll())) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `e_id` VARCHAR(50) NULL");
                }
                $uStmtPcId = $db->query("SHOW COLUMNS FROM `user` LIKE 'pc_id'");
                if ($uStmtPcId && empty($uStmtPcId->fetchAll())) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `pc_id` VARCHAR(50) NULL");
                }
                $uStmtDesig = $db->query("SHOW COLUMNS FROM `user` LIKE 'designation_assigned'");
                if ($uStmtDesig && empty($uStmtDesig->fetchAll())) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `designation_assigned` VARCHAR(255) NULL");
                }
                $uStmtAppRec = $db->query("SHOW COLUMNS FROM `user` LIKE 'applicable_records'");
                if ($uStmtAppRec && empty($uStmtAppRec->fetchAll())) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `applicable_records` TEXT NULL");
                }
                $uStmtWorkDesc = $db->query("SHOW COLUMNS FROM `user` LIKE 'work_description'");
                if ($uStmtWorkDesc && empty($uStmtWorkDesc->fetchAll())) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `work_description` TEXT NULL");
                }
                $uStmtResIssue = $db->query("SHOW COLUMNS FROM `user` LIKE 'resource_issue_record'");
                if ($uStmtResIssue && empty($uStmtResIssue->fetchAll())) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `resource_issue_record` TEXT NULL");
                }
            } catch (Throwable $e) {}

            // 5. Ensure soft-delete columns in service and customer_info
            try {
                $svcCols = $db->query("SHOW COLUMNS FROM `service` LIKE 'is_deleted'")->fetchAll();
                if (empty($svcCols)) {
                    $db->exec("ALTER TABLE `service` ADD COLUMN `is_deleted` VARCHAR(10) DEFAULT 'NO'");
                    $db->exec("UPDATE `service` SET `is_deleted` = 'NO' WHERE `is_deleted` IS NULL");
                }
                $svcDate = $db->query("SHOW COLUMNS FROM `service` LIKE 'deleted_date'")->fetchAll();
                if (empty($svcDate)) {
                    $db->exec("ALTER TABLE `service` ADD COLUMN `deleted_date` DATETIME NULL");
                }

                $custCols = $db->query("SHOW COLUMNS FROM `customer_info` LIKE 'is_deleted'")->fetchAll();
                if (empty($custCols)) {
                    $db->exec("ALTER TABLE `customer_info` ADD COLUMN `is_deleted` VARCHAR(10) DEFAULT 'NO'");
                    $db->exec("UPDATE `customer_info` SET `is_deleted` = 'NO' WHERE `is_deleted` IS NULL");
                }
                $custDate = $db->query("SHOW COLUMNS FROM `customer_info` LIKE 'deleted_date'")->fetchAll();
                if (empty($custDate)) {
                    $db->exec("ALTER TABLE `customer_info` ADD COLUMN `deleted_date` DATETIME NULL");
                }
            } catch (Throwable $e) {}

            // 6. Ensure ca_service_dates columns (is_deleted, status)
            try {
                $dtDel = $db->query("SHOW COLUMNS FROM `ca_service_dates` LIKE 'is_deleted'")->fetchAll();
                if (empty($dtDel)) {
                    $db->exec("ALTER TABLE `ca_service_dates` ADD COLUMN `is_deleted` VARCHAR(1) DEFAULT 'N'");
                }
                $dtStat = $db->query("SHOW COLUMNS FROM `ca_service_dates` LIKE 'status'")->fetchAll();
                if (empty($dtStat)) {
                    $db->exec("ALTER TABLE `ca_service_dates` ADD COLUMN `status` VARCHAR(10) NULL");
                }
            } catch (Throwable $e) {}

            // 7. Ensure recycle bin schema exists
            try {
                if (file_exists(__DIR__ . '/customer_recycle_helper.php')) {
                    require_once __DIR__ . '/customer_recycle_helper.php';
                    if (function_exists('aitfEnsureRecycleSchema')) {
                        aitfEnsureRecycleSchema($db);
                    }
                }
            } catch (Throwable $e) {}

            // 8. Ensure cdc_iso_metadata table exists
            try {
                if (file_exists(__DIR__ . '/cdc_iso_metadata.php')) {
                    require_once __DIR__ . '/cdc_iso_metadata.php';
                    if (function_exists('ensureCdcIsoMetadataTable')) {
                        ensureCdcIsoMetadataTable($db);
                    }
                }
            } catch (Throwable $e) {}

            // 9. Ensure statement tables exist
            try {
                if (file_exists(__DIR__ . '/statement_helper.php')) {
                    require_once __DIR__ . '/statement_helper.php';
                    if (function_exists('aitf_ensure_statement_tables')) {
                        aitf_ensure_statement_tables($db);
                    }
                }
            } catch (Throwable $e) {}

            // 10. Ensure invoice_details reminder and due date columns
            try {
                $invCols = [];
                $invStmt = $db->query("SHOW COLUMNS FROM `invoice_details`");
                if ($invStmt) {
                    while ($col = $invStmt->fetch(PDO::FETCH_ASSOC)) {
                        $invCols[strtolower($col['Field'])] = true;
                    }
                }
                if (!empty($invCols)) {
                    if (!isset($invCols['reminder_sent_count'])) {
                        $db->exec("ALTER TABLE `invoice_details` ADD COLUMN `reminder_sent_count` INT NOT NULL DEFAULT 0");
                    }
                    if (!isset($invCols['reminder_last_sent'])) {
                        $db->exec("ALTER TABLE `invoice_details` ADD COLUMN `reminder_last_sent` DATETIME NULL");
                    }
                    if (!isset($invCols['reminder_last_to'])) {
                        $db->exec("ALTER TABLE `invoice_details` ADD COLUMN `reminder_last_to` VARCHAR(255) NULL");
                    }
                    if (!isset($invCols['payment_due_date'])) {
                        $db->exec("ALTER TABLE `invoice_details` ADD COLUMN `payment_due_date` DATE NULL");
                    }
                }
            } catch (Throwable $e) {
                error_log("invoice_details columns sync error: " . $e->getMessage());
            }

            // 11. Ensure payment_system tds_amount column exists
            try {
                $payStmt = $db->query("SHOW COLUMNS FROM `payment_system` LIKE 'tds_amount'");
                if (!$payStmt || !$payStmt->fetch()) {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `tds_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `payment_received`");
                }
            } catch (Throwable $e) {
                error_log("payment_system tds_amount sync error: " . $e->getMessage());
            }

            // 12. Ensure payment_system discount_amount column exists
            try {
                $payStmt = $db->query("SHOW COLUMNS FROM `payment_system` LIKE 'discount_amount'");
                if (!$payStmt || !$payStmt->fetch()) {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `tds_amount`");
                }
            } catch (Throwable $e) {
                error_log("payment_system discount_amount sync error: " . $e->getMessage());
            }

            // 13. Ensure ca_service_dates is_intimation_deleted column exists
            try {
                $csdStmt = $db->query("SHOW COLUMNS FROM `ca_service_dates` LIKE 'is_intimation_deleted'");
                if (!$csdStmt || !$csdStmt->fetch()) {
                    $db->exec("ALTER TABLE `ca_service_dates` ADD COLUMN `is_intimation_deleted` VARCHAR(1) NOT NULL DEFAULT 'N' AFTER `is_deleted`");
                }
            } catch (Throwable $e) {
                error_log("ca_service_dates is_intimation_deleted sync error: " . $e->getMessage());
            }

            // 14. Auto-sync customer_info.unique_id and allotment_count with authoritative certification_aggrement
            try {
                require_once __DIR__ . '/agreement_sync_helper.php';
                aitf_sync_customer_agreement_numbers($db);

                $syncAllotSql = "UPDATE allotment_count ac
                                 INNER JOIN customer_info ci ON ac.client_id = ci.id
                                 SET ac.client_count = CAST(SUBSTRING_INDEX(ci.unique_id, '/', -1) AS UNSIGNED)
                                 WHERE ci.unique_id LIKE 'AITF/%'
                                   AND ci.unique_id NOT LIKE '% Rc-%'
                                   AND ci.unique_id NOT LIKE '%-RC-%'
                                   AND ac.client_count != CAST(SUBSTRING_INDEX(ci.unique_id, '/', -1) AS UNSIGNED)";
                $db->exec($syncAllotSql);

                // Sync previously saved audit forms reference numbers with current client unique_id
                $db->exec("UPDATE stage_oneaudit_id soa
                           INNER JOIN customer_info ci ON soa.ca_customer_id = ci.id
                           SET soa.audit_ref_no = ci.unique_id
                           WHERE ci.unique_id IS NOT NULL 
                             AND ci.unique_id != '' 
                             AND ci.unique_id NOT LIKE '% Rc-%'
                             AND ci.unique_id NOT LIKE '%-RC-%'
                             AND soa.audit_ref_no != ci.unique_id");

                $db->exec("UPDATE audit_schedule_id asi
                           INNER JOIN customer_info ci ON asi.customer_ca_id = ci.id
                           SET asi.ref_no = ci.unique_id
                           WHERE ci.unique_id IS NOT NULL 
                             AND ci.unique_id != '' 
                             AND ci.unique_id NOT LIKE '% Rc-%'
                             AND ci.unique_id NOT LIKE '%-RC-%'
                             AND asi.ref_no != ci.unique_id");

                $db->exec("UPDATE stage_twoaudit_id sta
                           INNER JOIN customer_info ci ON sta.customer_id = ci.id
                           SET sta.audit_ref_no = ci.unique_id
                           WHERE ci.unique_id IS NOT NULL 
                             AND ci.unique_id != '' 
                             AND ci.unique_id NOT LIKE '% Rc-%'
                             AND ci.unique_id NOT LIKE '%-RC-%'
                             AND sta.audit_ref_no != ci.unique_id");

                $db->exec("UPDATE stage_i_audit_id sia
                           INNER JOIN customer_info ci ON sia.customer_id = ci.id
                           SET sia.referenceNumber = ci.unique_id
                           WHERE ci.unique_id IS NOT NULL 
                             AND ci.unique_id != '' 
                             AND ci.unique_id NOT LIKE '% Rc-%'
                             AND ci.unique_id NOT LIKE '%-RC-%'
                             AND sia.referenceNumber != ci.unique_id");

                $db->exec("UPDATE stage_ii_audit_id siia
                           INNER JOIN customer_info ci ON siia.customer_id = ci.id
                           SET siia.reference_number = ci.unique_id
                           WHERE ci.unique_id IS NOT NULL 
                             AND ci.unique_id != '' 
                             AND ci.unique_id NOT LIKE '% Rc-%'
                             AND ci.unique_id NOT LIKE '%-RC-%'
                             AND siia.reference_number != ci.unique_id");

                $db->exec("UPDATE control_formati_id cfi
                           INNER JOIN customer_info ci ON cfi.customer_id = ci.id
                           SET cfi.control_ref_no = ci.unique_id
                           WHERE ci.unique_id IS NOT NULL 
                             AND ci.unique_id != '' 
                             AND ci.unique_id NOT LIKE '% Rc-%'
                             AND ci.unique_id NOT LIKE '%-RC-%'
                             AND cfi.control_ref_no != ci.unique_id");

                $db->exec("UPDATE control_formatii_id cfii
                           INNER JOIN customer_info ci ON cfii.customer_id = ci.id
                           SET cfii.control2_ref_no = ci.unique_id
                           WHERE ci.unique_id IS NOT NULL 
                             AND ci.unique_id != '' 
                             AND ci.unique_id NOT LIKE '% Rc-%'
                             AND ci.unique_id NOT LIKE '%-RC-%'
                             AND cfii.control2_ref_no != ci.unique_id");
            } catch (Throwable $e) {
                error_log("agreement auto-sync error: " . $e->getMessage());
            }

            // Ensure service_type column in invoice_details
            try {
                $chkInvCol = $db->query("SHOW COLUMNS FROM `invoice_details` LIKE 'service_type'");
                if ($chkInvCol && $chkInvCol->rowCount() === 0) {
                    $db->exec("ALTER TABLE `invoice_details` ADD COLUMN `service_type` VARCHAR(100) NULL");
                }
            } catch (Throwable $e) {
                // Ignore if already exists
            }

            // Ensure reconciliation column in payment_system
            try {
                $chkPayCol = $db->query("SHOW COLUMNS FROM `payment_system` LIKE 'reconciliation'");
                if ($chkPayCol && $chkPayCol->rowCount() === 0) {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `reconciliation` VARCHAR(100) NULL AFTER `cheque_details`");
                }
            } catch (Throwable $e) {
                // Ignore if already exists
            }

            // Ensure agreement_no and po_ref columns in invoice_details
            try {
                $chkInvAgr = $db->query("SHOW COLUMNS FROM `invoice_details` LIKE 'agreement_no'");
                if ($chkInvAgr && $chkInvAgr->rowCount() === 0) {
                    $db->exec("ALTER TABLE `invoice_details` ADD COLUMN `agreement_no` VARCHAR(150) NULL AFTER `invoice_date`");
                }
            } catch (Throwable $e) {
                // Ignore if already exists
            }

            try {
                $chkInvPo = $db->query("SHOW COLUMNS FROM `invoice_details` LIKE 'po_ref'");
                if ($chkInvPo && $chkInvPo->rowCount() === 0) {
                    $db->exec("ALTER TABLE `invoice_details` ADD COLUMN `po_ref` VARCHAR(150) NULL AFTER `agreement_no`");
                }
            } catch (Throwable $e) {
                // Ignore if already exists
            }

            // Ensure tds_verified column in payment_system
            try {
                $chkTdsVer = $db->query("SHOW COLUMNS FROM `payment_system` LIKE 'tds_verified'");
                if ($chkTdsVer && $chkTdsVer->rowCount() === 0) {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `tds_verified` VARCHAR(50) NOT NULL DEFAULT 'unverified' AFTER `reconciliation`");
                }
            } catch (Throwable $e) {
                // Ignore if already exists
            }

            // Ensure tds_verified column in statement_payments
            try {
                $chkSpTds = $db->query("SHOW COLUMNS FROM `statement_payments` LIKE 'tds_verified'");
                if ($chkSpTds && $chkSpTds->rowCount() === 0) {
                    $db->exec("ALTER TABLE `statement_payments` ADD COLUMN `tds_verified` VARCHAR(50) NOT NULL DEFAULT 'unverified' AFTER `tds_received`");
                }
            } catch (Throwable $e) {
                // Ignore if already exists
            }

            $alreadySynced = true;
            if (isset($flagFile)) {
                @touch($flagFile);
            }
        } catch (Throwable $e) {
            error_log("aitf_auto_sync_schema error: " . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_ensure_invoice_agreement_columns')) {
    function aitf_ensure_invoice_agreement_columns($db) {
        static $checked = false;
        if ($checked) {
            return true;
        }
        if (!$db) {
            return false;
        }
        try {
            $cols = array();
            $stmt = $db->query("SHOW COLUMNS FROM `invoice_details`");
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $cols[strtolower($row['Field'])] = true;
                }
            }
            if (!isset($cols['agreement_no'])) {
                try {
                    $db->exec("ALTER TABLE `invoice_details` ADD COLUMN `agreement_no` VARCHAR(150) NULL AFTER `invoice_date`");
                    $cols['agreement_no'] = true;
                } catch (Throwable $e) {}
            }
            if (!isset($cols['po_ref'])) {
                try {
                    $db->exec("ALTER TABLE `invoice_details` ADD COLUMN `po_ref` VARCHAR(150) NULL AFTER `agreement_no`");
                    $cols['po_ref'] = true;
                } catch (Throwable $e) {}
            }
            $checked = (!empty($cols['agreement_no']) && !empty($cols['po_ref']));
            return $checked;
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('aitf_ensure_tds_columns')) {
    function aitf_ensure_tds_columns($db) {
        static $checked = false;
        if ($checked) {
            return true;
        }
        if (!$db) {
            return false;
        }
        try {
            // Check payment_system columns
            $payCols = array();
            $stmt = $db->query("SHOW COLUMNS FROM `payment_system`");
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $payCols[strtolower($row['Field'])] = true;
                }
            }
            if (!isset($payCols['tds_verified'])) {
                try {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `tds_verified` VARCHAR(50) NOT NULL DEFAULT 'unverified'");
                } catch (Throwable $e) {}
            }
            if (!isset($payCols['tds_amount'])) {
                try {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `tds_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `payment_received`");
                } catch (Throwable $e) {}
            }
            if (!isset($payCols['tds_received'])) {
                try {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `tds_received` DECIMAL(10,2) NULL DEFAULT NULL AFTER `tds_amount`");
                } catch (Throwable $e) {}
            }
            if (!isset($payCols['discount_amount'])) {
                try {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `tds_amount`");
                } catch (Throwable $e) {}
            }
            if (!isset($payCols['reconciliation'])) {
                try {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `reconciliation` VARCHAR(100) NULL AFTER `cheque_details`");
                } catch (Throwable $e) {}
            }
            if (!isset($payCols['tds_received_date'])) {
                try {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `tds_received_date` DATE NULL DEFAULT NULL AFTER `payment_date`");
                } catch (Throwable $e) {}
            }
            if (!isset($payCols['credit_date_booking'])) {
                try {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `credit_date_booking` VARCHAR(100) NULL DEFAULT NULL AFTER `payment_date`");
                } catch (Throwable $e) {}
            }
            if (!isset($payCols['tan_of_deductor'])) {
                try {
                    $db->exec("ALTER TABLE `payment_system` ADD COLUMN `tan_of_deductor` VARCHAR(100) NULL DEFAULT NULL AFTER `credit_date_booking`");
                } catch (Throwable $e) {}
            }

            // Check statement_payments columns
            try {
                $spCols = array();
                $spStmt = $db->query("SHOW COLUMNS FROM `statement_payments`");
                if ($spStmt) {
                    while ($row = $spStmt->fetch(PDO::FETCH_ASSOC)) {
                        $spCols[strtolower($row['Field'])] = true;
                    }
                    if (!isset($spCols['tds_verified'])) {
                        $db->exec("ALTER TABLE `statement_payments` ADD COLUMN `tds_verified` VARCHAR(50) NOT NULL DEFAULT 'unverified'");
                    }
                    if (!isset($spCols['reconciliation'])) {
                        $db->exec("ALTER TABLE `statement_payments` ADD COLUMN `reconciliation` VARCHAR(50) NOT NULL DEFAULT 'Verify'");
                    }
                    if (!isset($spCols['tds_decided'])) {
                        $db->exec("ALTER TABLE `statement_payments` ADD COLUMN `tds_decided` DECIMAL(12,2) DEFAULT 0.00");
                    }
                    if (!isset($spCols['tds_received'])) {
                        $db->exec("ALTER TABLE `statement_payments` ADD COLUMN `tds_received` DECIMAL(12,2) DEFAULT 0.00");
                    }
                    if (!isset($spCols['tds_received_date'])) {
                        $db->exec("ALTER TABLE `statement_payments` ADD COLUMN `tds_received_date` DATE NULL DEFAULT NULL AFTER `payment_date`");
                    }
                    if (!isset($spCols['credit_date_booking'])) {
                        $db->exec("ALTER TABLE `statement_payments` ADD COLUMN `credit_date_booking` VARCHAR(100) NULL DEFAULT NULL AFTER `payment_date`");
                    }
                    if (!isset($spCols['tan_of_deductor'])) {
                        $db->exec("ALTER TABLE `statement_payments` ADD COLUMN `tan_of_deductor` VARCHAR(100) NULL DEFAULT NULL AFTER `credit_date_booking`");
                    }
                }
            } catch (Throwable $e) {}

            // Auto-migrate any standalone legacy TDS rows into payment_system.tds_amount
            aitf_migrate_legacy_tds_to_payment_columns($db);

            $checked = true;
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('aitf_migrate_legacy_tds_to_payment_columns')) {
    /**
     * Migrates legacy standalone TDS rows (where service_type was saved as 'TDS', 'TDS 1200', etc.)
     * into the parent service payment row's dedicated `tds_amount` column.
     * 
     * This ensures:
     * 1. Old clients' TDS is reflected in the Payment System under the actual service (e.g. CA, SA-I, RC).
     * 2. Remaining balance balances out against invoice gross totals.
     * 3. No orphan 'SERVICE SEPARATION • TDS' dummy blocks are shown.
     * 4. Idempotent and safe to run on both localhost and LIVE.
     */
    function aitf_migrate_legacy_tds_to_payment_columns($db) {
        if (!$db) return;
        try {
            $stmt = $db->query("SELECT * FROM `payment_system` WHERE `service_type` LIKE '%tds%' ORDER BY `customer_id`, `payment_id`");
            if (!$stmt) return;
            $tdsRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($tdsRows)) {
                return;
            }

            foreach ($tdsRows as $tr) {
                $cid = (int)($tr['customer_id'] ?? 0);
                $tdsPayId = (int)($tr['payment_id'] ?? 0);
                if ($cid <= 0 || $tdsPayId <= 0) continue;

                $tdsAmount = floatval($tr['tds_amount'] ?? 0) ?: (floatval($tr['payment_received'] ?? 0) ?: floatval($tr['amount_decided'] ?? 0));
                if ($tdsAmount == 0 && preg_match('/tds\s*(\d+(?:\.\d+)?)/i', (string)$tr['service_type'], $m)) {
                    $tdsAmount = floatval($m[1]);
                }

                if ($tdsAmount <= 0) {
                    $del = $db->prepare("DELETE FROM `payment_system` WHERE `payment_id` = :id");
                    $del->execute([':id' => $tdsPayId]);
                    continue;
                }

                // Find candidate service payment rows for this customer
                $st = $db->prepare("SELECT * FROM `payment_system` WHERE `customer_id` = :cid AND `service_type` NOT LIKE '%tds%' ORDER BY `payment_id` ASC");
                $st->execute([':cid' => $cid]);
                $svcRows = $st->fetchAll(PDO::FETCH_ASSOC);

                $matchedRow = null;

                // 1. Exact balance match
                foreach ($svcRows as $sr) {
                    $dec = floatval($sr['amount_decided'] ?? 0);
                    $rcv = floatval($sr['payment_received'] ?? 0);
                    $curTds = floatval($sr['tds_amount'] ?? 0);
                    $diff = round($dec - ($rcv + $curTds), 2);
                    if ($curTds == 0 && abs($diff - $tdsAmount) < 0.01) {
                        $matchedRow = $sr;
                        break;
                    }
                }

                // 2. Service name hint match (e.g. 'TDS(CA)' => 'CA')
                if (!$matchedRow) {
                    $tdsSvcName = strtoupper((string)$tr['service_type']);
                    foreach (['CA', 'SA-01', 'SA-1', 'SA-02', 'SA-2', 'RC', 'CR'] as $hint) {
                        if (stripos($tdsSvcName, $hint) !== false) {
                            foreach ($svcRows as $sr) {
                                if (stripos(strtoupper((string)$sr['service_type']), $hint) !== false && floatval($sr['tds_amount'] ?? 0) == 0) {
                                    $matchedRow = $sr;
                                    break 2;
                                }
                            }
                        }
                    }
                }

                // 3. Preceding row in same payment_sy_id group
                if (!$matchedRow) {
                    $prevRows = array_filter($svcRows, function($sr) use ($tdsPayId, $tr) {
                        return $sr['payment_id'] < $tdsPayId && floatval($sr['tds_amount'] ?? 0) == 0 && ($sr['payment_sy_id'] == $tr['payment_sy_id'] || empty($tr['payment_sy_id']));
                    });
                    if (!empty($prevRows)) {
                        $matchedRow = end($prevRows);
                    }
                }

                // 4. Fallback any service row with tds_amount == 0
                if (!$matchedRow) {
                    foreach ($svcRows as $sr) {
                        if (floatval($sr['tds_amount'] ?? 0) == 0) {
                            $matchedRow = $sr;
                            break;
                        }
                    }
                }

                // 5. Fallback latest service row
                if (!$matchedRow && !empty($svcRows)) {
                    $matchedRow = end($svcRows);
                }

                if ($matchedRow) {
                    $targetId = (int)$matchedRow['payment_id'];
                    $newTds = floatval($matchedRow['tds_amount'] ?? 0) + $tdsAmount;
                    $rcvAmt = floatval($matchedRow['payment_received'] ?? 0);
                    $discAmt = floatval($matchedRow['discount_amount'] ?? 0);
                    $newDecided = max(floatval($matchedRow['amount_decided'] ?? 0), round($rcvAmt + $newTds + $discAmt, 2));
                    $newBalance = max(0, round($newDecided - ($rcvAmt + $newTds + $discAmt), 2));
                    
                    $isVerified = (isset($tr['tds_verified']) && in_array(strtolower(trim($tr['tds_verified'])), ['verified', 'yes', '1']))
                               || (isset($matchedRow['tds_verified']) && in_array(strtolower(trim($matchedRow['tds_verified'])), ['verified', 'yes', '1']));
                    $tdsVerVal = $isVerified ? 'Verified' : (trim($matchedRow['tds_verified'] ?? '') ?: 'unverified');

                    $comment = trim($matchedRow['comment'] ?? '');
                    if (empty($comment) && !empty($tr['comment'])) {
                        $comment = trim($tr['comment']);
                    }

                    $upd = $db->prepare("UPDATE `payment_system` 
                                         SET `tds_amount` = :tds, 
                                             `amount_decided` = :decided, 
                                             `balance` = :balance, 
                                             `tds_verified` = :tds_ver,
                                             `comment` = :comment
                                         WHERE `payment_id` = :id");
                    $upd->execute([
                        ':tds' => $newTds,
                        ':decided' => $newDecided,
                        ':balance' => $newBalance,
                        ':tds_ver' => $tdsVerVal,
                        ':comment' => $comment,
                        ':id' => $targetId
                    ]);

                    $del = $db->prepare("DELETE FROM `payment_system` WHERE `payment_id` = :id");
                    $del->execute([':id' => $tdsPayId]);
                } else {
                    $svcStmt = $db->prepare("SELECT service_type FROM `service` WHERE `customer_id` = :cid ORDER BY `service_id` DESC LIMIT 1");
                    $svcStmt->execute([':cid' => $cid]);
                    $defSvc = $svcStmt->fetchColumn() ?: 'Payment';

                    $upd = $db->prepare("UPDATE `payment_system` 
                                         SET `service_type` = :svc,
                                             `tds_amount` = :tds,
                                             `payment_received` = 0.00,
                                             `amount_decided` = :tds,
                                             `balance` = 0.00
                                         WHERE `payment_id` = :id");
                    $upd->execute([
                        ':svc' => $defSvc,
                        ':tds' => $tdsAmount,
                        ':id' => $tdsPayId
                    ]);
                }
            }

            // Ensure payment_block_comments table for independent payment service comments
            $db->exec("CREATE TABLE IF NOT EXISTS `payment_block_comments` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `customer_id` INT NOT NULL,
                `service_type` VARCHAR(100) NOT NULL,
                `comment` TEXT NULL,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_cust_service` (`customer_id`, `service_type`),
                KEY `idx_cust` (`customer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

            // Ensure payment_page_comments table for paymentPage.php dedicated follow-up notes (isolated from customerAccount.php)
            $db->exec("CREATE TABLE IF NOT EXISTS `payment_page_comments` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `customer_id` INT NOT NULL UNIQUE,
                `comment` TEXT NULL,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_ppc_cust` (`customer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Ensure Microsoft To-Do style aitf_tasks schema
            require_once __DIR__ . '/task_lib.php';
            if (function_exists('aitf_ensure_tasks_schema')) {
                aitf_ensure_tasks_schema($db);
            }

            // Ensure Mobile Attendance, Biometrics & Mobile Tokens Schema
            $db->exec("CREATE TABLE IF NOT EXISTS `aitf_systems` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `system_code` VARCHAR(50) NOT NULL UNIQUE,
                `system_name` VARCHAR(150) NOT NULL,
                `address` TEXT NULL,
                `contact_email` VARCHAR(150) NULL,
                `contact_phone` VARCHAR(50) NULL,
                `api_base_url` VARCHAR(255) DEFAULT NULL,
                `db_host` VARCHAR(150) DEFAULT 'localhost',
                `db_name` VARCHAR(100) NULL,
                `db_user` VARCHAR(100) NULL,
                `db_password` VARCHAR(255) NULL,
                `created_by_user_id` INT NULL,
                `secret_key` VARCHAR(128) NOT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Ensure columns exist on existing aitf_systems table
            $sysCols = [];
            $scStmt = $db->query("SHOW COLUMNS FROM `aitf_systems`");
            if ($scStmt) {
                while ($r = $scStmt->fetch(PDO::FETCH_ASSOC)) {
                    $sysCols[strtolower($r['Field'])] = true;
                }
            }
            if (!isset($sysCols['address'])) $db->exec("ALTER TABLE `aitf_systems` ADD COLUMN `address` TEXT NULL");
            if (!isset($sysCols['contact_email'])) $db->exec("ALTER TABLE `aitf_systems` ADD COLUMN `contact_email` VARCHAR(150) NULL");
            if (!isset($sysCols['contact_phone'])) $db->exec("ALTER TABLE `aitf_systems` ADD COLUMN `contact_phone` VARCHAR(50) NULL");
            if (!isset($sysCols['db_host'])) $db->exec("ALTER TABLE `aitf_systems` ADD COLUMN `db_host` VARCHAR(150) DEFAULT 'localhost'");
            if (!isset($sysCols['db_name'])) $db->exec("ALTER TABLE `aitf_systems` ADD COLUMN `db_name` VARCHAR(100) NULL");
            if (!isset($sysCols['db_user'])) $db->exec("ALTER TABLE `aitf_systems` ADD COLUMN `db_user` VARCHAR(100) NULL");
            if (!isset($sysCols['db_password'])) $db->exec("ALTER TABLE `aitf_systems` ADD COLUMN `db_password` VARCHAR(255) NULL");
            if (!isset($sysCols['created_by_user_id'])) $db->exec("ALTER TABLE `aitf_systems` ADD COLUMN `created_by_user_id` INT NULL");

            $db->exec("INSERT INTO `aitf_systems` (`system_code`, `system_name`, `secret_key`, `is_active`)
                       VALUES ('AITF_MAIN', 'AITF-Management Certification Limited', SHA2('AITF_SECURE_2026', 256), 1)
                       ON DUPLICATE KEY UPDATE `system_name` = 'AITF-Management Certification Limited'");

            try {
                $db->exec("UPDATE `aitf_systems` SET `system_code` = 'QMS_CARE', `system_name` = 'QMS Care', `db_name` = NULL, `db_user` = NULL, `db_password` = NULL WHERE `system_code` = '01' OR `system_name` LIKE '%QMS%'");
            } catch (Throwable $ignore) {}

            // Ensure QMS Care users exist in main tenant database for seamless mobile attendance
            try {
                $qmsUsers = [
                    ['Gaurav Oli', 'GO', 'gaurav@qmscare.com', '$2y$10$6ScAizpe.w2/BzsQZzN7t.lqWOwS9D0Lc/zJJsKHuBdG9ytqEUpYC', 'Main Admin'],
                    ['Kajal', 'KJ', 'ks@qmscare.com', '$2y$10$ExNRd7x7v1aUp6WeYu6Ixuoai3Bz.U8GSWv2WQrR7GDAvSG8FsDSW', 'Main Admin'],
                    ['Bhagyashree', 'BH', 'bh@qmscare.com', '$2y$10$Gm8WaOeRSIpkzwkW43gTjOYtTYdttR8sqRibjSdF7hOlHytDYoNay', 'Office Staff']
                ];
                $chkStmt = $db->prepare("SELECT user_id FROM user WHERE email_id = :email LIMIT 1");
                $insStmt = $db->prepare("
                    INSERT INTO user (user_name, abbreviation, email_id, password, role, is_deleted, account_status)
                    VALUES (:name, :abbr, :email, :pwd, :role, '0', 'active')
                ");
                foreach ($qmsUsers as $u) {
                    $chkStmt->execute([':email' => $u[2]]);
                    if (!$chkStmt->fetch()) {
                        $insStmt->execute([
                            ':name'  => $u[0],
                            ':abbr'  => $u[1],
                            ':email' => $u[2],
                            ':pwd'   => $u[3],
                            ':role'  => $u[4]
                        ]);
                    }
                }
            } catch (Throwable $ignore) {}

            $db->exec("CREATE TABLE IF NOT EXISTS `aitf_mobile_tokens` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `token` VARCHAR(64) NOT NULL UNIQUE,
                `system_code` VARCHAR(50) DEFAULT 'AITF_MAIN',
                `device_id` VARCHAR(150) NULL,
                `device_model` VARCHAR(100) NULL,
                `os_version` VARCHAR(50) NULL,
                `last_used_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `expires_at` DATETIME NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_token_lookup` (`token`, `expires_at`),
                INDEX `idx_token_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $db->exec("CREATE TABLE IF NOT EXISTS `aitf_user_biometrics` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `system_code` VARCHAR(50) DEFAULT 'AITF_MAIN',
                `enrollment_photo_url` VARCHAR(500) NOT NULL,
                `face_embedding` MEDIUMTEXT NOT NULL,
                `embedding_dimension` INT DEFAULT 192,
                `enrollment_device_info` VARCHAR(255) NULL,
                `is_verified` TINYINT(1) DEFAULT 1,
                `enrolled_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_user_system` (`user_id`, `system_code`),
                INDEX `idx_bio_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $db->exec("CREATE TABLE IF NOT EXISTS `aitf_attendance` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `system_code` VARCHAR(50) DEFAULT 'AITF_MAIN',
                `attendance_date` DATE NOT NULL,
                `in_time` TIME NULL,
                `out_time` TIME NULL,
                `total_working_hours` DECIMAL(4,2) DEFAULT 0.00,
                `status` ENUM('present', 'late', 'halfday', 'leave', 'holiday', 'absent') DEFAULT 'present',
                `punch_in_selfie_url` VARCHAR(500) NULL,
                `punch_out_selfie_url` VARCHAR(500) NULL,
                `face_match_score` DECIMAL(5,4) NULL,
                `in_latitude` DECIMAL(10,8) NULL,
                `in_longitude` DECIMAL(11,8) NULL,
                `in_address` VARCHAR(255) NULL,
                `out_latitude` DECIMAL(10,8) NULL,
                `out_longitude` DECIMAL(11,8) NULL,
                `out_address` VARCHAR(255) NULL,
                `device_id` VARCHAR(150) NULL,
                `remarks` VARCHAR(255) NULL,
                `is_manual_override` TINYINT(1) DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_user_date` (`user_id`, `attendance_date`, `system_code`),
                INDEX `idx_att_user_date` (`user_id`, `attendance_date`),
                INDEX `idx_att_date` (`attendance_date`),
                INDEX `idx_att_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $db->exec("CREATE TABLE IF NOT EXISTS `aitf_user_devices` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `system_code` VARCHAR(50) DEFAULT 'AITF_MAIN',
                `fcm_token` VARCHAR(500) NOT NULL,
                `device_model` VARCHAR(100) NULL,
                `os_version` VARCHAR(50) NULL,
                `app_version` VARCHAR(30) NULL,
                `last_active` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_user_device_fcm` (`user_id`, `fcm_token`(191)),
                INDEX `idx_dev_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $db->exec("CREATE TABLE IF NOT EXISTS `aitf_notifications` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `title` VARCHAR(200) NOT NULL,
                `body` TEXT NOT NULL,
                `notification_type` VARCHAR(50) DEFAULT 'task_assigned',
                `reference_id` INT NULL,
                `is_read` TINYINT(1) DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_notif_user` (`user_id`, `is_read`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {}
    }
}

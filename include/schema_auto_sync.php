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

if (!function_exists('aitf_auto_sync_schema')) {
    function aitf_auto_sync_schema($db) {
        static $alreadySynced = false;
        if ($alreadySynced || !$db) {
            return;
        }

        $flagFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'aitf_schema_synced.flag';
        if (file_exists($flagFile) && filemtime($flagFile) >= filemtime(__FILE__) && (time() - filemtime($flagFile) < 43200)) {
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

            // 4. Ensure user.e_id column exists
            try {
                $uStmt = $db->query("SHOW COLUMNS FROM `user` LIKE 'e_id'");
                if ($uStmt && empty($uStmt->fetchAll())) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `e_id` VARCHAR(50) NULL");
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

            $alreadySynced = true;
            if (isset($flagFile)) {
                @touch($flagFile);
            }
        } catch (Throwable $e) {
            error_log("aitf_auto_sync_schema error: " . $e->getMessage());
        }
    }
}

<?php
/**
 * AITF Database Schema Auto-Sync Helper
 * 
 * Automatically ensures all required tables and columns exist in the database
 * on both localhost and live environments. Runs safely on connection without
 * manual DB execution.
 */

if (!function_exists('aitf_auto_sync_schema')) {
    function aitf_auto_sync_schema($db) {
        static $alreadySynced = false;
        if ($alreadySynced || !$db) {
            return;
        }

        try {
            // 1. Ensure columns in certification_aggrement (Agreement mail feature)
            $aggCols = [];
            try {
                $stmt = $db->query("SHOW COLUMNS FROM `certification_aggrement`");
                if ($stmt) {
                    while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $aggCols[strtolower($col['Field'])] = true;
                    }
                }
            } catch (Throwable $e) {
                // Ignore if table does not exist yet
            }

            if (!empty($aggCols)) {
                if (!isset($aggCols['mail_sent'])) {
                    $db->exec("ALTER TABLE `certification_aggrement` ADD COLUMN `mail_sent` TINYINT(1) NOT NULL DEFAULT 0");
                }
                if (!isset($aggCols['mail_sent_at'])) {
                    $db->exec("ALTER TABLE `certification_aggrement` ADD COLUMN `mail_sent_at` DATETIME NULL");
                }
                if (!isset($aggCols['mail_sent_to'])) {
                    $db->exec("ALTER TABLE `certification_aggrement` ADD COLUMN `mail_sent_to` VARCHAR(255) NULL");
                }
                if (!isset($aggCols['mail_sent_by'])) {
                    $db->exec("ALTER TABLE `certification_aggrement` ADD COLUMN `mail_sent_by` VARCHAR(100) NULL");
                }
            }

            // 2. Ensure user.e_id column exists
            try {
                $userCols = [];
                $uStmt = $db->query("SHOW COLUMNS FROM `user` LIKE 'e_id'");
                if ($uStmt && empty($uStmt->fetchAll())) {
                    $db->exec("ALTER TABLE `user` ADD COLUMN `e_id` VARCHAR(50) NULL");
                }
            } catch (Throwable $e) {}

            // 3. Ensure soft-delete columns in service and customer_info
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

            // 4. Ensure ca_service_dates columns (is_deleted, status)
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

            // 5. Ensure statement tables exist
            try {
                if (file_exists(__DIR__ . '/statement_helper.php')) {
                    require_once __DIR__ . '/statement_helper.php';
                    if (function_exists('aitf_ensure_statement_tables')) {
                        aitf_ensure_statement_tables($db);
                    }
                }
            } catch (Throwable $e) {}

            $alreadySynced = true;
        } catch (Throwable $e) {
            error_log("aitf_auto_sync_schema error: " . $e->getMessage());
        }
    }
}

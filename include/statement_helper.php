<?php
/**
 * Statement System Helper Functions
 * 
 * Ensures statement_payments and statement_bank_reconciliation tables exist
 * at the code level, eliminating the need to manually run queries on live DB.
 */

if (!function_exists('aitf_ensure_statement_tables')) {
    function aitf_ensure_statement_tables($db) {
        static $tablesChecked = false;
        if ($tablesChecked || !$db) {
            return;
        }

        $flagFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'aitf_statement_tables.flag';
        if (file_exists($flagFile) && filemtime($flagFile) >= filemtime(__FILE__) && (time() - filemtime($flagFile) < 43200)) {
            $tablesChecked = true;
            return;
        }

        try {
            // 1. statement_payments table
            $db->exec("
                CREATE TABLE IF NOT EXISTS `statement_payments` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `customer_id` INT(11) NOT NULL,
                    `service_type` VARCHAR(255) DEFAULT '',
                    `amount_decided` DECIMAL(12,2) DEFAULT 0.00,
                    `payment_received` DECIMAL(12,2) DEFAULT 0.00,
                    `payment_date` DATE DEFAULT NULL,
                    `instrument` VARCHAR(255) DEFAULT '',
                    `balance` DECIMAL(12,2) DEFAULT 0.00,
                    `tds_decided` DECIMAL(12,2) DEFAULT 0.00,
                    `tds_received` DECIMAL(12,2) DEFAULT 0.00,
                    `comment` TEXT DEFAULT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_customer_id` (`customer_id`),
                    KEY `idx_balance` (`balance`),
                    KEY `idx_tds` (`tds_decided`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");

            // 2. statement_bank_reconciliation table
            $db->exec("
                CREATE TABLE IF NOT EXISTS `statement_bank_reconciliation` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `customer_id` INT(11) NOT NULL,
                    `acknowledged_by` VARCHAR(255) DEFAULT '',
                    `acknowledged_at` DATETIME DEFAULT NULL,
                    `acknowledgement_text` TEXT DEFAULT NULL,
                    `bank_statement_file` VARCHAR(500) DEFAULT NULL,
                    `original_filename` VARCHAR(255) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_customer_id` (`customer_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");

            $tablesChecked = true;
            @touch($flagFile);
        } catch (Throwable $e) {
            error_log("aitf_ensure_statement_tables warning: " . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_statement_table_exists')) {
    function aitf_statement_table_exists($db, $tableName) {
        if (!$db) return false;
        try {
            $stmt = $db->query("SHOW TABLES LIKE " . $db->quote($tableName));
            return ($stmt && $stmt->rowCount() > 0);
        } catch (Throwable $e) {
            return false;
        }
    }
}

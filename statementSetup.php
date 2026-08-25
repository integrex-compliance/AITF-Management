<?php
/**
 * Statement System - Database Setup Script
 * Run this ONCE to create the required tables.
 * 
 * Tables created:
 *  1. statement_payments   – New payment entries (separate from old payment_system)
 *  2. statement_bank_reconciliation – Bank statement verification records
 */

include_once './dbConnect.php';

$messages = [];

try {
    // 1. statement_payments
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
    $messages[] = '✅ Table `statement_payments` created successfully.';

    // 2. statement_bank_reconciliation
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
    $messages[] = '✅ Table `statement_bank_reconciliation` created successfully.';

} catch (PDOException $e) {
    $messages[] = '❌ Error: ' . $e->getMessage();
}

// Output results
echo "<html><head><title>Statement System Setup</title></head><body style='font-family:monospace;padding:40px;'>";
echo "<h2>Statement System - Database Setup</h2>";
foreach ($messages as $msg) {
    echo "<p>" . $msg . "</p>";
}
echo "<br><a href='statementPage.php'>→ Go to Statement Page</a>";
echo "</body></html>";
?>

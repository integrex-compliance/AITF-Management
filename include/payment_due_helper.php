<?php
/**
 * Payment Due & Follow-Up Helper
 * 
 * Provides unified, single-source-of-truth logic for calculating live due payments across
 * paymentPage.php, exportPaymentReport.php, exportPaymentReportExcel.php, and emailPaymentReport.php.
 * 
 * Calculation Formula:
 * Outstanding Balance = Total Final Invoices - (Payments Received + TDS Deductions + Settlement/Discounts)
 */

if (!function_exists('aitf_ensure_payment_due_indexes')) {
    function aitf_ensure_payment_due_indexes(PDO $db) {
        static $done = false;
        if ($done) return;
        try {
            $stmt = $db->query("SHOW INDEX FROM `invoice_details` WHERE Key_name = 'idx_inv_cust_type'");
            if (!$stmt->fetch()) {
                $db->exec("ALTER TABLE `invoice_details` ADD INDEX `idx_inv_cust_type` (`customer_id`, `invoice_type`)");
            }
        } catch (Throwable $e) {}
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `payment_page_comments` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `customer_id` INT NOT NULL UNIQUE,
                `comment` TEXT NULL,
                `reminder_count` INT NOT NULL DEFAULT 0,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_ppc_cust` (`customer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {}
        try {
            $db->exec("ALTER TABLE `payment_page_comments` ADD COLUMN `reminder_count` INT NOT NULL DEFAULT 0");
        } catch (Throwable $e) {}
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
        $done = true;
    }
}

require_once __DIR__ . '/payment_due_services.php';

if (!function_exists('aitf_get_due_payments')) {
    function aitf_get_due_payments(PDO $db, array $filters = []) {
        aitf_ensure_payment_due_indexes($db);
        return aitf_fetch_service_dues($db, $filters);
    }
}

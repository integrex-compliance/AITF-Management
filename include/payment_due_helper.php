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
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_ppc_cust` (`customer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {}
        $done = true;
    }
}

if (!function_exists('aitf_get_due_payments')) {
    function aitf_get_due_payments(PDO $db, array $filters = []) {
        aitf_ensure_payment_due_indexes($db);

        $clientName = trim((string)($filters['clientName'] ?? ''));
        $minAmount = isset($filters['minAmount']) && $filters['minAmount'] !== '' ? (float)$filters['minAmount'] : null;
        $maxAmount = isset($filters['maxAmount']) && $filters['maxAmount'] !== '' ? (float)$filters['maxAmount'] : null;

        $sql = "SELECT 
            ci.id, 
            ci.customer_name, 
            ci.unique_id, 
            ci.certification_no, 
            ci.audit_status, 
            ci.contact_person, 
            ci.contact,
            ci.email_id,
            COALESCE(inv_agg.total_inv, 0) as total_inv,
            COALESCE(pay_agg.total_paid, 0) as total_paid,
            COALESCE(pay_agg.total_tds, 0) as total_tds,
            COALESCE(pay_agg.total_disc, 0) as total_disc,
            ROUND(COALESCE(inv_agg.total_inv, 0) - (COALESCE(pay_agg.total_paid, 0) + COALESCE(pay_agg.total_tds, 0) + COALESCE(pay_agg.total_disc, 0)), 2) as balance,
            COALESCE(NULLIF(inv_agg.services, ''), NULLIF(svc.service_type, ''), 'CA') as service_type,
            COALESCE(ppc.comment, '') as comment
        FROM customer_info ci
        LEFT JOIN (
            SELECT 
                customer_id, 
                SUM(final_amount) as total_inv,
                GROUP_CONCAT(DISTINCT NULLIF(service_type, '') ORDER BY invoice_date DESC SEPARATOR ', ') as services
            FROM invoice_details 
            WHERE invoice_type = 'final' AND (is_deleted = 'No' OR is_deleted IS NULL)
            GROUP BY customer_id
        ) inv_agg ON inv_agg.customer_id = ci.id
        LEFT JOIN (
            SELECT 
                customer_id,
                SUM(CASE WHEN (discount_amount <> 0 OR service_type LIKE '%tds%' OR service_type LIKE '%discount%' OR service_type LIKE '%short%') THEN 0 ELSE payment_received END) as total_paid,
                SUM(CASE WHEN tds_amount > 0 THEN tds_amount WHEN service_type LIKE '%tds%' THEN payment_received ELSE 0 END) as total_tds,
                SUM(CASE WHEN discount_amount <> 0 THEN discount_amount WHEN (service_type LIKE '%discount%' OR service_type LIKE '%short%') THEN payment_received ELSE 0 END) as total_disc
            FROM payment_system
            GROUP BY customer_id
        ) pay_agg ON pay_agg.customer_id = ci.id
        LEFT JOIN (
            SELECT s1.customer_id, s1.service_type
            FROM service s1
            INNER JOIN (
                SELECT customer_id, MAX(service_id) as max_sid
                FROM service
                WHERE (is_deleted = 'NO' OR is_deleted IS NULL)
                GROUP BY customer_id
            ) s2 ON s1.customer_id = s2.customer_id AND s1.service_id = s2.max_sid
        ) svc ON svc.customer_id = ci.id
        LEFT JOIN payment_page_comments ppc ON ppc.customer_id = ci.id
        WHERE ci.is_deleted = 'NO'";

        $params = [];
        if ($clientName !== '') {
            $sql .= " AND ci.customer_name LIKE :clientName";
            $params[':clientName'] = '%' . $clientName . '%';
        }

        $havingClauses = ["balance > 0.01"];
        if ($minAmount !== null) {
            $havingClauses[] = "balance >= :minAmount";
            $params[':minAmount'] = $minAmount;
        }
        if ($maxAmount !== null) {
            $havingClauses[] = "balance <= :maxAmount";
            $params[':maxAmount'] = $maxAmount;
        }
        $sql .= " HAVING " . implode(" AND ", $havingClauses);
        $sql .= " ORDER BY balance DESC";

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            if (is_int($v)) {
                $stmt->bindValue($k, $v, PDO::PARAM_INT);
            } elseif (is_float($v)) {
                $stmt->bindValue($k, $v);
            } else {
                $stmt->bindValue($k, $v, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Strip internal block duplicate counters like " (2)", " (3)" to keep displayed headers clean
        foreach ($rows as &$r) {
            if (!empty($r['service_type'])) {
                $services = array_map('trim', explode(',', $r['service_type']));
                $cleaned = [];
                foreach ($services as $s) {
                    $sClean = trim(preg_replace('/\s*\(\d+\)$/', '', $s));
                    if ($sClean !== '' && !in_array($sClean, $cleaned, true)) {
                        $cleaned[] = $sClean;
                    }
                }
                $r['service_type'] = implode(', ', $cleaned);
            }
        }
        unset($r);

        return $rows;
    }
}

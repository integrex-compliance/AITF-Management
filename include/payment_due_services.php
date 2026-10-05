<?php
require_once __DIR__ . '/payment_ledger.php';

/** Build independent invoice-header balances using the Payment System's grouping rules. */
function aitf_due_service_rows(array $customer, array $invoices, array $payments, array $catalog, $multiCycle) {
    $groups = [];
    foreach ($invoices as $invoice) {
        $raw = trim((string)($invoice['service_type'] ?? ''));
        $header = $multiCycle
            ? (($raw !== '' && strcasecmp($raw, 'Payment') !== 0 && !preg_match('/^CD\//i', $raw)) ? $raw : 'Till Sep 2026')
            : ($raw !== '' ? $raw : normalizePaymentServiceType($invoice['invoice_service_type'] ?? ''));
        $key = strtoupper(trim($header));
        if (!isset($groups[$key])) {
            $label = trim((string)($catalog[$key]['header_name'] ?? ''));
            $groups[$key] = array_merge($customer, [
                'header_key' => $header, 'service_type' => $label !== '' ? $label : $header,
                'total_inv' => 0, 'total_paid' => 0, 'total_tds' => 0, 'total_disc' => 0,
                'invoices' => [],
            ]);
        }
        $number = trim((string)($invoice['final_prefix'] ?? '') . '-' . (string)($invoice['final_invoice_no'] ?? ''), '- ');
        if ($number === '') $number = trim((string)($invoice['invoice_no'] ?? ''));
        if ($number === '') $number = 'Invoice #' . (int)$invoice['invoice_id'];
        $groups[$key]['invoices'][] = [
            'id' => (int)$invoice['invoice_id'], 'number' => $number,
            'amount' => (float)$invoice['final_amount'], 'date' => $invoice['invoice_date'] ?? '',
        ];
        $groups[$key]['total_inv'] += (int)round((float)$invoice['final_amount'] * 100);
    }
    foreach ($payments as $payment) {
        $key = strtoupper(trim(aitf_payment_header($payment, $multiCycle)));
        // Unassigned advances/credits must never pay off a different invoice header.
        if (!isset($groups[$key])) continue;
        $service = strtolower((string)($payment['service_type'] ?? ''));
        $isDiscount = !empty($payment['settlement_kind']) || strpos($service, 'discount') !== false || strpos($service, 'short') !== false;
        $isTds = !$isDiscount && strpos($service, 'tds') !== false && (float)($payment['tds_amount'] ?? 0) == 0;
        $discount = (float)($payment['discount_amount'] ?? 0);
        if ($discount == 0 && $isDiscount) $discount = (float)$payment['payment_received'];
        $received = ($isDiscount || $isTds) ? 0 : (float)($payment['payment_received'] ?? 0);
        $tds = (float)($payment['tds_amount'] ?? 0) > 0 ? (float)$payment['tds_amount'] : ($isTds ? (float)$payment['payment_received'] : 0);
        $groups[$key]['total_paid'] += (int)round($received * 100);
        $groups[$key]['total_tds'] += (int)round($tds * 100);
        $groups[$key]['total_disc'] += (int)round($discount * 100);
    }
    foreach ($groups as &$group) {
        $group['balance'] = ($group['total_inv'] - $group['total_paid'] - $group['total_tds'] - $group['total_disc']) / 100;
        foreach (['total_inv', 'total_paid', 'total_tds', 'total_disc'] as $field) $group[$field] /= 100;
        usort($group['invoices'], function ($a, $b) {
            return strcmp($a['date'], $b['date']) ?: strnatcasecmp($a['number'], $b['number']) ?: ($a['id'] <=> $b['id']);
        });
        $group['invoice_count'] = count($group['invoices']);
        if (empty($group['reminder_count']) && !empty($group['comment'])) {
            $group['reminder_count'] = substr_count($group['comment'], 'Reminder email sent');
        }
    }
    unset($group);
    return array_values($groups);
}

function aitf_fetch_service_dues(PDO $db, array $filters = []) {
    $sql = "SELECT ci.id, ci.customer_name, ci.unique_id, ci.certification_no, ci.audit_status,
        ci.contact_person, ci.contact, ci.email_id, COALESCE(ppc.comment, '') AS comment,
        COALESCE(ppc.reminder_count, 0) AS reminder_count
        FROM customer_info ci LEFT JOIN payment_page_comments ppc ON ppc.customer_id=ci.id
        WHERE ci.is_deleted='NO'";
    $params = [];
    if (trim((string)($filters['clientName'] ?? '')) !== '') {
        $sql .= ' AND LOCATE(LOWER(?), LOWER(ci.customer_name)) > 0';
        $params[] = trim($filters['clientName']);
    }
    if (isset($filters['customerId'])) {
        $sql .= ' AND ci.id=?';
        $params[] = (int)$filters['customerId'];
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = [];
    // Batch queries avoid one database round trip per customer/header.
    foreach (array_chunk($customers, 300) as $batch) {
        $ids = array_column($batch, 'id');
        $slots = implode(',', array_fill(0, count($ids), '?'));
        $queries = [
            'invoices' => "SELECT inv.*, (SELECT GROUP_CONCAT(DISTINCT service_desc SEPARATOR ', ') FROM invoice_services WHERE invoice_ids=inv.invoice_id) AS invoice_service_type FROM invoice_details inv WHERE customer_id IN ($slots) AND invoice_type='final' AND (is_deleted='No' OR is_deleted IS NULL)",
            'payments' => "SELECT * FROM payment_system WHERE customer_id IN ($slots)",
            'catalog' => "SELECT * FROM payment_block_comments WHERE customer_id IN ($slots)",
            'cycles' => "SELECT customer_id, service_type, COUNT(*) AS total FROM service WHERE customer_id IN ($slots) AND service_type IN ('CR','RC') AND (is_deleted='NO' OR is_deleted IS NULL) GROUP BY customer_id,service_type",
        ];
        $records = [];
        foreach ($queries as $type => $query) {
            $stmt = $db->prepare($query);
            $stmt->execute($ids);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $records[$type][$row['customer_id']][] = $row;
        }
        foreach ($batch as $customer) {
            $id = $customer['id'];
            $catalog = [];
            foreach ($records['catalog'][$id] ?? [] as $row) $catalog[strtoupper(trim($row['service_type']))] = $row;
            $counts = [];
            foreach ($records['cycles'][$id] ?? [] as $row) $counts[strtoupper($row['service_type'])] = (int)$row['total'];
            $multi = ($counts['CR'] ?? 0) > 1 || (($counts['RC'] ?? 0) >= 1 && ($counts['CR'] ?? 0) >= 1) || $id == 1191;
            foreach (aitf_due_service_rows($customer, $records['invoices'][$id] ?? [], $records['payments'][$id] ?? [], $catalog, $multi) as $row) {
                if ($row['balance'] <= 0.01) continue;
                if (isset($filters['headerKey']) && strcasecmp($row['header_key'], trim($filters['headerKey'])) !== 0) continue;
                if (isset($filters['minAmount']) && $filters['minAmount'] !== '' && $row['balance'] < (float)$filters['minAmount']) continue;
                if (isset($filters['maxAmount']) && $filters['maxAmount'] !== '' && $row['balance'] > (float)$filters['maxAmount']) continue;
                $result[] = $row;
            }
        }
    }
    usort($result, function ($a, $b) {
        return ($b['balance'] <=> $a['balance']) ?: ($a['id'] <=> $b['id']) ?: strnatcasecmp($a['header_key'], $b['header_key']);
    });
    return $result;
}

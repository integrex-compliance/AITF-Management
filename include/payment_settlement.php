<?php
require_once __DIR__ . '/payment_ledger.php';

function aitf_settlement_snapshot(PDO $db, $customer, $header) {
    $header = aitf_payment_service($header);
    $stmt = $db->prepare("SELECT service_type,COUNT(*) AS total FROM service WHERE customer_id=? AND service_type IN ('CR','RC') AND (is_deleted='NO' OR is_deleted IS NULL) GROUP BY service_type");
    $stmt->execute([$customer]);
    $counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $multi = ($counts['CR'] ?? 0) > 1 || (($counts['RC'] ?? 0) >= 1 && ($counts['CR'] ?? 0) >= 1) || $customer == 1191;
    $stmt = $db->prepare("SELECT inv.*, (SELECT GROUP_CONCAT(DISTINCT service_desc SEPARATOR ', ') FROM invoice_services WHERE invoice_ids=inv.invoice_id) AS invoice_service_type FROM invoice_details inv WHERE customer_id=? AND invoice_type='final' AND (is_deleted='No' OR is_deleted IS NULL) ORDER BY invoice_id");
    $stmt->execute([$customer]);
    $invoices = []; $remaining = 0;
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $invoice) {
        $raw = trim((string)($invoice['service_type'] ?? ''));
        $key = $multi ? (($raw !== '' && strcasecmp($raw,'Payment') !== 0 && !preg_match('/^CD\//i',$raw)) ? $raw : 'Till Sep 2026') : ($raw !== '' ? $raw : normalizePaymentServiceType($invoice['invoice_service_type'] ?? ''));
        if (strcasecmp($key,$header) !== 0) continue;
        $remaining += (int)round((float)$invoice['final_amount'] * 100);
        $invoices[] = ['id'=>(int)$invoice['invoice_id'],'number'=>$invoice['final_prefix'].'-'.$invoice['final_invoice_no'],'amount'=>number_format((float)$invoice['final_amount'],2,'.','')];
    }
    $note = '';
    $stmt = $db->prepare('SELECT * FROM payment_system WHERE customer_id=?'); $stmt->execute([$customer]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (strcasecmp(aitf_payment_header($row,$multi),$header) !== 0) continue;
        if (!empty($row['settlement_kind']) && trim((string)($row['comment'] ?? '')) !== '') $note = trim((string)$row['comment']);
        $service = strtolower($row['service_type'] ?? '');
        $isDiscount = !empty($row['settlement_kind']) || strpos($service,'discount') !== false || strpos($service,'short') !== false;
        $isTds = !$isDiscount && strpos($service,'tds') !== false && (float)$row['tds_amount'] == 0;
        $discount = (float)$row['discount_amount'];
        if ($discount == 0 && $isDiscount) $discount = (float)$row['payment_received'];
        $received = ($isDiscount || $isTds) ? 0 : (float)$row['payment_received'];
        $tds = (float)$row['tds_amount'] > 0 ? (float)$row['tds_amount'] : ($isTds ? (float)$row['payment_received'] : 0);
        $remaining -= (int)round(($received + $tds + $discount) * 100);
    }
    return ['success'=>true,'service_type'=>$header,'remaining'=>number_format($remaining/100,2,'.',''),'kind'=>$remaining < 0 ? 'excess' : ($remaining > 0 ? 'short' : 'settled'),'invoices'=>$invoices,'note'=>$note];
}

function aitf_payment_settle(PDO $db, $customer, array $req) {
    $header = aitf_payment_service($req['service_type'] ?? '');
    $key = (string)($req['settlement_key'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_-]{8,80}$/D',$key)) throw new InvalidArgumentException('Reload the settlement dialog and try again.');
    $amount = (int)round((float)aitf_payment_money($req['discount_amount'] ?? 0)*100);
    if ($amount <= 0) throw new InvalidArgumentException('Enter a settlement amount greater than zero.');
    $db->beginTransaction();
    try {
        aitf_payment_lock_customer($db,$customer);
        $stmt = $db->prepare('SELECT payment_id,settlement_kind,payment_header FROM payment_system WHERE customer_id=? AND client_row_key=?');
        $stmt->execute([$customer,$key]);
        if ($existing = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (empty($existing['settlement_kind']) || strcasecmp($existing['payment_header'],$header) !== 0) throw new RuntimeException('Invalid settlement retry.');
            $db->commit();
            return ['success'=>true,'payment_id'=>(int)$existing['payment_id'],'replayed'=>true];
        }
        $snapshot = aitf_settlement_snapshot($db,$customer,$header);
        $remaining = (int)round((float)$snapshot['remaining']*100);
        if (!isset($req['expected_remaining']) || !is_numeric($req['expected_remaining']) || (int)round((float)$req['expected_remaining']*100) !== $remaining) throw new RuntimeException('This header balance changed. Reopen settlement to use its latest balance.');
        if (!$remaining) throw new RuntimeException('This header is already settled.');
        if ($amount > abs($remaining)) throw new RuntimeException('Settlement cannot exceed this header balance.');
        $invoiceId = (int)($req['invoice_id'] ?? 0);
        $invoice = null;
        foreach ($snapshot['invoices'] as $candidate) if ($candidate['id'] === $invoiceId) $invoice = $candidate;
        if ($invoiceId && !$invoice) throw new RuntimeException('Select an invoice from this header.');
        $kind = $remaining < 0 ? 'excess' : 'short';
        $label = $kind === 'excess' ? 'Excess settle' : 'Short settle';
        $signed = number_format(($kind === 'excess' ? -$amount : $amount)/100,2,'.','');
        $date = aitf_payment_date($req['discount_date'] ?? date('Y-m-d'));
        $comment = trim((string)($req['remarks'] ?? ''));
        if ($invoice) $comment = $invoice['number'] . ($comment !== '' ? ' | '.$comment : '');
        $stmt = $db->prepare('SELECT payment_sy_id FROM payment_system_id WHERE customer_id=? ORDER BY payment_sy_id LIMIT 1'); $stmt->execute([$customer]);
        $payid = (int)$stmt->fetchColumn();
        if (!$payid) { $db->prepare('INSERT INTO payment_system_id (customer_id) VALUES (?)')->execute([$customer]); $payid = (int)$db->lastInsertId(); }
        $stmt = $db->prepare("INSERT INTO payment_system (payment_sy_id,customer_id,service_id,service_type,payment_header,amount_decided,payment_received,tds_amount,discount_amount,payment_date,cheque_details,balance,comment,reconciliation,client_row_key,settlement_kind) VALUES (?,?,'',?,?,?,0,0,?,?,?,0,?,'Verify',?,?)");
        $stmt->execute([$payid,$customer,$label,$header,$signed,$signed,$date,$label,$comment,$key,$kind]);
        $id = (int)$db->lastInsertId();
        $db->commit();
        return ['success'=>true,'payment_id'=>$id,'kind'=>$kind,'remaining'=>number_format(($remaining-($kind==='excess' ? -$amount : $amount))/100,2,'.','')];
    } catch (Throwable $e) { if ($db->inTransaction()) $db->rollBack(); throw $e; }
}

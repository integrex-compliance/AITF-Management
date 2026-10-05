<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/payment_ledger_test.php'; // Connection-local temporary fixtures.
$startChecks = $checks;
$input = ['key'=>'independent_verify_row', 'header'=>'CA', 'received'=>'2000', 'tds'=>'100', 'date'=>'05.10.2026', 'instrument'=>'NEFT'];
$saved = aitf_payment_save($db, 380, [$input]);
$id = $input['id'] = $saved['rows'][0]['id'];
$read = function () use ($db, $id) {
    $q = $db->prepare('SELECT * FROM payment_system WHERE payment_id=?');
    $q->execute([$id]); return $q->fetch(PDO::FETCH_ASSOC);
};
$result = aitf_payment_verify($db, $id, 'Verified', 'reconciliation');
check($result['payment_verified'] && !$result['tds_verified'], 'Payment verification does not verify TDS');
$input['version'] = $result['version']; $input['tds'] = '125';
$saved = aitf_payment_save($db, 380, [$input]);
$input['version'] = $saved['rows'][0]['version'];
check((float)$read()['tds_amount'] === 125.0 && $read()['reconciliation'] === 'Verified', 'TDS edit persists while payment remains verified');
rejected(function () use ($db, $input) { $input['received'] = '2001'; aitf_payment_save($db, 380, [$input]); }, 'Verified received amount stays locked');
rejected(function () use ($db, $input) { $input['date'] = '06.10.2026'; aitf_payment_save($db, 380, [$input]); }, 'Shared date stays locked');
$result = aitf_payment_verify($db, $id, 'Verified', 'tds_verified');
check($result['payment_verified'] && $result['tds_verified'], 'Both verifications can coexist');
$input['version'] = $result['version'];
rejected(function () use ($db, $input) { $input['tds'] = '126'; aitf_payment_save($db, 380, [$input]); }, 'Verified TDS stays locked');
$result = aitf_payment_verify($db, $id, 'Verify', 'reconciliation');
check(!$result['payment_verified'] && $result['tds_verified'], 'Unverify payment preserves TDS verification');
$input['version'] = $result['version']; $input['received'] = '2200';
$saved = aitf_payment_save($db, 380, [$input]);
$input['version'] = $saved['rows'][0]['version'];
check((float)$read()['payment_received'] === 2200.0 && $read()['tds_verified'] === 'Verified', 'Received amount editable when only TDS is verified');
rejected(function () use ($db, $input) { $input['tds'] = '150'; aitf_payment_save($db, 380, [$input]); }, 'TDS lock cannot be bypassed in save request');
rejected(function () use ($db, $input) { aitf_payment_delete($db, 380, $input['id'], $input['version']); }, 'Cannot delete a row containing verified TDS');
$result = aitf_payment_verify($db, $id, 'Verify', 'tds_verified');
check(!$result['payment_verified'] && !$result['tds_verified'], 'Both unverified restores independent editing');
$input['version'] = $result['version']; $input['tds'] = '150'; $input['date'] = '06.10.2026';
aitf_payment_save($db, 380, [$input]);
check($read()['payment_date'] === '2026-10-06', 'Shared details editable once both are unverified');
// Statement receipts previously wrote payment verification into the TDS field.
$db->exec("CREATE TEMPORARY TABLE statement_payments (id INT PRIMARY KEY, customer_id INT, payment_received DECIMAL(12,2), tds_received DECIMAL(12,2), reconciliation VARCHAR(50) DEFAULT 'Verify', tds_verified VARCHAR(50) DEFAULT 'unverified')");
$db->exec('INSERT INTO statement_payments (id,customer_id,payment_received,tds_received) VALUES (1,380,2000,100)');
foreach (['reconciliation', 'tds_verified'] as $first) {
    $second = $first === 'reconciliation' ? 'tds_verified' : 'reconciliation';
    $db->exec("UPDATE statement_payments SET reconciliation='Verify',tds_verified='Verify'");
    aitf_payment_verify($db, 1, 'Verified', $first, true);
    $row = $db->query('SELECT * FROM statement_payments')->fetch(PDO::FETCH_ASSOC);
    check($row[$first] === 'Verified' && $row[$second] === 'Verify', 'Statement verification changes only its own status');
    aitf_payment_verify($db, 1, 'Verified', $second, true);
    aitf_payment_verify($db, 1, 'Verify', $first, true);
    $row = $db->query('SELECT * FROM statement_payments')->fetch(PDO::FETCH_ASSOC);
    check($row[$first] === 'Verify' && $row[$second] === 'Verified', 'Statement unverify preserves the other status');
}
// Run the page's actual queries and KPI calculation against all four status combinations.
// SHOW TABLES hides connection-local temporary tables, so use a read probe in this test.
function aitf_statement_table_exists($db, $table) { $db->query('SELECT id FROM statement_payments LIMIT 1'); return true; }
$db->exec("ALTER TABLE statement_payments ADD service_type VARCHAR(100) DEFAULT 'CA', ADD amount_decided DECIMAL(12,2) DEFAULT 2100, ADD payment_date DATE NULL, ADD instrument VARCHAR(100) DEFAULT '', ADD balance DECIMAL(12,2) DEFAULT 0, ADD comment TEXT NULL");
$db->exec('DELETE FROM payment_system');
$db->exec('DELETE FROM statement_payments');
$db->exec("UPDATE customer_info SET is_deleted='NO' WHERE id=380");
$number = 0;
foreach (['Verify', 'Verified'] as $paymentState) foreach (['Verify', 'Verified'] as $tdsState) {
    ++$number;
    $saved = aitf_payment_save($db, 380, [['key'=>'filter_verify_' . $number, 'header'=>'CA', 'received'=>2000, 'tds'=>100]]);
    $db->prepare('UPDATE payment_system SET reconciliation=?,tds_verified=? WHERE payment_id=?')->execute([$paymentState,$tdsState,$saved['rows'][0]['id']]);
    $db->prepare('INSERT INTO statement_payments (id,customer_id,payment_received,tds_received,reconciliation,tds_verified) VALUES (?,380,2000,100,?,?)')->execute([$number,$paymentState,$tdsState]);
}
$page = file_get_contents(__DIR__ . '/../receivedPayments.php');
$start = strpos($page, '// Fetch all received payment entries');
$finish = strpos($page, "if (!function_exists('aitf_format_service_display'))", $start);
$queryCode = substr($page, $start, $finish - $start);
$startDate = $endDate = $clientSearch = ''; $serviceFilter = 'all'; $sortOrder = 'latest';
foreach (['all'=>[8,4,4], 'reconciled'=>[4,4,0], 'pending'=>[4,0,4]] as $statusFilter => $expected) {
    eval($queryCode);
    check([$totalReceivedCount,$totalReconciledCount,$totalPendingCount] === $expected, 'Page filters and KPI counts use payment status only: ' . $statusFilter);
    check((float)$totalReconciledSum === 2000.0 * $expected[1] && (float)$totalPendingSum === 2000.0 * $expected[2], 'Page totals use payment status only: ' . $statusFilter);
}
echo 'PASS: ' . ($checks - $startChecks) . " independent verification checks (temporary tables only).\n";

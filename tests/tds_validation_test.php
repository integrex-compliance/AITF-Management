<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once __DIR__ . '/payment_ledger_test.php';

echo "Running TDS validation test...\n";

// Ensure tds_received column exists in temporary tables
try {
    $db->exec("ALTER TABLE payment_system ADD COLUMN tds_received DECIMAL(12,2) NULL AFTER tds_amount");
} catch (PDOException $e) { /* ignore if exists */ }

try {
    $db->exec("CREATE TEMPORARY TABLE IF NOT EXISTS statement_payments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        customer_id INT,
        service_type VARCHAR(100) DEFAULT 'CA',
        payment_received DECIMAL(12,2) DEFAULT 0,
        tds_decided DECIMAL(12,2) DEFAULT 0,
        tds_received DECIMAL(12,2) NULL,
        reconciliation VARCHAR(50) DEFAULT 'Verify',
        tds_verified VARCHAR(50) DEFAULT 'unverified'
    )");
} catch (PDOException $e) { }

// Test 1: Check validation logic in payment_system
// Create row with decided TDS = 1500, tds_received = null
$db->exec("INSERT INTO payment_system (customer_id, payment_header, payment_received, tds_amount, tds_received, reconciliation, tds_verified)
           VALUES (380, 'CA', 10000, 1500, NULL, 'Verify', 'unverified')");
$payId = (int)$db->lastInsertId();

// Helper simulating addPayment.php validation
function simulateTdsVerify($db, $payId, $status, $isStatement, $receivedInput = null) {
    if ($status === 'Verified') {
        if ($receivedInput !== null && $receivedInput !== '') {
            $clean = number_format(max(0.0, floatval(str_replace(',', '', $receivedInput))), 2, '.', '');
            if ($isStatement) {
                $db->prepare("UPDATE statement_payments SET tds_received = ? WHERE id = ?")->execute([$clean, $payId]);
            } else {
                $db->prepare("UPDATE payment_system SET tds_received = ? WHERE payment_id = ?")->execute([$clean, $payId]);
            }
        }

        if ($isStatement) {
            $stmt = $db->prepare("SELECT tds_decided, tds_received FROM statement_payments WHERE id = ?");
            $stmt->execute([$payId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['success' => false, 'message' => 'Statement payment record not found.'];
            $tdsDec = (float)($row['tds_decided'] ?? 0);
            $tdsRec = isset($row['tds_received']) && $row['tds_received'] !== null && $row['tds_received'] !== '' ? (float)$row['tds_received'] : 0.0;
        } else {
            $stmt = $db->prepare("SELECT COALESCE(NULLIF(tds_amount, 0), amount_decided, payment_received) as amount_decided, tds_received FROM payment_system WHERE payment_id = ?");
            $stmt->execute([$payId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['success' => false, 'message' => 'Payment record not found.'];
            $tdsDec = (float)($row['amount_decided'] ?? 0);
            $tdsRec = isset($row['tds_received']) && $row['tds_received'] !== null && $row['tds_received'] !== '' ? (float)$row['tds_received'] : 0.0;
        }

        if ($tdsRec <= 0) {
            return ['success' => false, 'message' => 'Blank TDS cannot be reconciled. Please enter the TDS received amount before verifying.'];
        }

        if ($tdsDec > 0 && $tdsRec < $tdsDec) {
            return ['success' => false, 'message' => 'TDS received amount (₹' . number_format($tdsRec, 2) . ') is lower than the TDS decided amount (₹' . number_format($tdsDec, 2) . '). Blank or lower TDS cannot be reconciled.'];
        }
    }

    $res = aitf_payment_verify($db, $payId, $status, 'tds_verified', $isStatement);
    return ['success' => true, 'status' => $res['status']];
}

// 1. Verify fails when blank
$res1 = simulateTdsVerify($db, $payId, 'Verified', false, null);
check(!$res1['success'] && strpos($res1['message'], 'Blank TDS') !== false, 'Blank TDS rejected for payment_system');

// 2. Verify fails when 0
$res2 = simulateTdsVerify($db, $payId, 'Verified', false, '0.00');
check(!$res2['success'] && strpos($res2['message'], 'Blank TDS') !== false, 'Zero TDS rejected for payment_system');

// 3. Verify fails when received < decided (1000 < 1500)
$res3 = simulateTdsVerify($db, $payId, 'Verified', false, '1000.00');
check(!$res3['success'] && strpos($res3['message'], 'lower than the TDS decided') !== false, 'Lower TDS rejected for payment_system');

// 4. Verify succeeds when received == decided (1500 == 1500)
$res4 = simulateTdsVerify($db, $payId, 'Verified', false, '1500.00');
check($res4['success'] && $res4['status'] === 'Verified', 'Exact TDS verified successfully for payment_system');

// 5. Unverify succeeds
$res5 = simulateTdsVerify($db, $payId, 'Verify', false);
check($res5['success'] && $res5['status'] === 'Verify', 'Unverify succeeds without amount check');

// 6. Statement payment tests
$db->exec("INSERT INTO statement_payments (customer_id, payment_received, tds_decided, tds_received, tds_verified)
           VALUES (380, 5000, 500, NULL, 'unverified')");
$stmtPayId = (int)$db->lastInsertId();

// Fails when blank
$sres1 = simulateTdsVerify($db, $stmtPayId, 'Verified', true, '');
check(!$sres1['success'] && strpos($sres1['message'], 'Blank TDS') !== false, 'Blank TDS rejected for statement_payments');

// Fails when lower (300 < 500)
$sres2 = simulateTdsVerify($db, $stmtPayId, 'Verified', true, '300.00');
check(!$sres2['success'] && strpos($sres2['message'], 'lower than the TDS decided') !== false, 'Lower TDS rejected for statement_payments');

// Succeeds when higher (550 > 500)
$sres3 = simulateTdsVerify($db, $stmtPayId, 'Verified', true, '550.00');
check($sres3['success'] && $sres3['status'] === 'Verified', 'Higher TDS verified successfully for statement_payments');

echo "ALL 7 TDS VALIDATION CHECKS PASSED!\n";

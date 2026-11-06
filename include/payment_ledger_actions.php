<?php
require_once __DIR__ . '/payment_ledger.php';

function aitf_payment_json($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$ledgerAction = $req['action'] ?? '';
$ledgerActions = ['autoSavePayments', 'deletePayment', 'createPaymentHeader', 'renamePaymentHeader', 'removePaymentHeader', 'updateInvoiceServiceType', 'updatePaymentServiceType', 'getPaymentSettlement', 'saveDiscount', 'markBadDebt', 'revertBadDebt'];
if (in_array($ledgerAction, $ledgerActions, true)) {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Use POST for payment changes.');
        if (empty($_SESSION['username']) || in_array(strtolower($_SESSION['role'] ?? ''), ['client', 'external'], true)) throw new RuntimeException('Please sign in with a staff account.');
        aitf_payment_schema($db);
        $customer = (int)($req['customer_id'] ?? $req['customer'] ?? 0);
        if ($ledgerAction === 'getPaymentSettlement' || $ledgerAction === 'saveDiscount') {
            require_once __DIR__ . '/payment_settlement.php';
            if ($customer <= 0) throw new InvalidArgumentException('Invalid customer.');
            aitf_payment_json($ledgerAction === 'saveDiscount' ? aitf_payment_settle($db,$customer,$req) : aitf_settlement_snapshot($db,$customer,$req['service_type'] ?? ''));
        }
        if ($ledgerAction === 'markBadDebt') {
            require_once __DIR__ . '/bad_debts_helper.php';
            if ($customer <= 0) throw new InvalidArgumentException('Invalid customer.');
            $username = $_SESSION['username'] ?? 'staff';
            aitf_payment_json(aitf_mark_bad_debt($db, $customer, $req, $username));
        }
        if ($ledgerAction === 'revertBadDebt') {
            require_once __DIR__ . '/bad_debts_helper.php';
            $badDebtId = (int)($req['bad_debt_id'] ?? 0);
            $username = $_SESSION['username'] ?? 'staff';
            aitf_payment_json(aitf_revert_bad_debt($db, $badDebtId, $username));
        }
        if ($ledgerAction === 'autoSavePayments') {
            $rows = json_decode($req['rows'] ?? 'null', true);
            if (!is_array($rows) || count($rows) > 1000) throw new InvalidArgumentException('Invalid payment rows. Reload this page.');
            aitf_payment_json(aitf_payment_save($db, $customer, $rows));
        }
        if ($ledgerAction === 'deletePayment') {
            aitf_payment_delete($db, $customer, (int)($req['payment_id'] ?? 0), $req['version'] ?? null);
            try {
                $db->prepare("UPDATE `bad_debts` SET `status` = 'reverted', `reverted_by` = ?, `reverted_at` = NOW() WHERE `payment_id` = ? AND `status` = 'active'")
                   ->execute([$_SESSION['username'] ?? 'staff', (int)($req['payment_id'] ?? 0)]);
            } catch (Throwable $e) {}
            aitf_payment_json(['success' => true]);
        }
        if ($ledgerAction === 'updateInvoiceServiceType') {
            $target = aitf_payment_move_invoice($db, (int)($req['invoice_id'] ?? 0), $req['service_type'] ?? '', $customer);
            aitf_payment_json(['success' => true, 'service_type' => $target]);
        }
        if ($ledgerAction === 'updatePaymentServiceType') {
            aitf_payment_json(aitf_payment_move_receipt($db, $customer, (int)($req['payment_id'] ?? 0), $req['service_type'] ?? '', $req['version'] ?? null));
        }
        aitf_payment_json(aitf_payment_change_header($db, $customer, $ledgerAction, $req));
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        aitf_payment_json(['success' => false, 'message' => $e->getMessage()]);
    }
}

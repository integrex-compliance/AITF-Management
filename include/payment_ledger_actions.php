<?php
require_once __DIR__ . '/payment_ledger.php';

function aitf_payment_json($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$ledgerAction = $req['action'] ?? '';
$ledgerActions = ['autoSavePayments', 'deletePayment', 'createPaymentHeader', 'renamePaymentHeader', 'removePaymentHeader', 'updateInvoiceServiceType', 'updatePaymentServiceType'];
if (in_array($ledgerAction, $ledgerActions, true)) {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Use POST for payment changes.');
        if (empty($_SESSION['username']) || in_array(strtolower($_SESSION['role'] ?? ''), ['client', 'external'], true)) throw new RuntimeException('Please sign in with a staff account.');
        aitf_payment_schema($db);
        $customer = (int)($req['customer_id'] ?? $req['customer'] ?? 0);
        if ($ledgerAction === 'autoSavePayments') {
            $rows = json_decode($req['rows'] ?? 'null', true);
            if (!is_array($rows) || count($rows) > 1000) throw new InvalidArgumentException('Invalid payment rows. Reload this page.');
            aitf_payment_json(aitf_payment_save($db, $customer, $rows));
        }
        if ($ledgerAction === 'deletePayment') {
            aitf_payment_delete($db, $customer, (int)($req['payment_id'] ?? 0), $req['version'] ?? null);
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

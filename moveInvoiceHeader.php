<?php
ob_start();
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

function sendJsonResponse($data) {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method']);
}

$invoiceId = intval($_POST['invoice_id'] ?? 0);
$targetService = trim((string)($_POST['target_service'] ?? ''));

if ($invoiceId <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid Invoice ID']);
}
if ($targetService === '') {
    sendJsonResponse(['success' => false, 'message' => 'Please select a valid target service header']);
}

try {
    require_once __DIR__ . '/include/payment_ledger.php';
    if (empty($_SESSION['username']) || in_array(strtolower($_SESSION['role'] ?? ''), ['client', 'external'], true)) {
        throw new RuntimeException('Please sign in with a staff account.');
    }
    aitf_payment_schema($db);
    $customerId = (int)($_POST['customer_id'] ?? 0);
    if ($customerId <= 0) throw new RuntimeException('Invalid customer.');
    if (isset($_POST['payment_ids']) || ($_POST['action'] ?? '') === 'preview') throw new RuntimeException('Payment mapping has been removed. Reload the account page.');
    // Check if column service_type exists
    $colsStmt = $db->query("SHOW COLUMNS FROM invoice_details LIKE 'service_type'");
    if (!$colsStmt || $colsStmt->rowCount() === 0) {
        $db->exec("ALTER TABLE invoice_details ADD COLUMN service_type VARCHAR(100) NULL AFTER invoice_type");
    }

    $targetService = aitf_payment_move_invoice($db, $invoiceId, $targetService, (int)($_POST['customer_id'] ?? 0));

    sendJsonResponse([
        'success' => true,
        'message' => 'Invoice moved successfully to ' . $targetService,
        'invoice_id' => $invoiceId,
        'target_service' => $targetService
    ]);
} catch (Exception $ex) {
    sendJsonResponse(['success' => false, 'message' => 'Error moving invoice: ' . $ex->getMessage()]);
}

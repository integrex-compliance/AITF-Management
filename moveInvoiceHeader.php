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
    // Check if column service_type exists
    $colsStmt = $db->query("SHOW COLUMNS FROM invoice_details LIKE 'service_type'");
    if (!$colsStmt || $colsStmt->rowCount() === 0) {
        $db->exec("ALTER TABLE invoice_details ADD COLUMN service_type VARCHAR(100) NULL AFTER invoice_type");
    }

    $updateStmt = $db->prepare("UPDATE invoice_details SET service_type = :target_service WHERE invoice_id = :invoice_id");
    $updateStmt->bindParam(':target_service', $targetService);
    $updateStmt->bindParam(':invoice_id', $invoiceId);
    $updateStmt->execute();

    if ($targetService !== '' && strcasecmp($targetService, 'Till Sep 2026') !== 0) {
        $custStmt = $db->prepare("SELECT customer_id FROM invoice_details WHERE invoice_id = :invoice_id");
        $custStmt->execute([':invoice_id' => $invoiceId]);
        $invCustId = $custStmt->fetchColumn();

        if ($invCustId) {
            $chkComm = $db->prepare("SELECT id FROM payment_block_comments WHERE customer_id = :cid AND UPPER(TRIM(service_type)) = :st");
            $chkComm->execute([':cid' => $invCustId, ':st' => strtoupper(trim($targetService))]);
            if (!$chkComm->fetch()) {
                $insComm = $db->prepare("INSERT INTO payment_block_comments (customer_id, service_type, comment) VALUES (:cid, :st, '')");
                $insComm->execute([':cid' => $invCustId, ':st' => $targetService]);
            }
        }
    }

    sendJsonResponse([
        'success' => true,
        'message' => 'Invoice moved successfully to ' . $targetService,
        'invoice_id' => $invoiceId,
        'target_service' => $targetService
    ]);
} catch (Exception $ex) {
    sendJsonResponse(['success' => false, 'message' => 'Error moving invoice: ' . $ex->getMessage()]);
}

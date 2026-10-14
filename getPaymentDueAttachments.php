<?php
/**
 * getPaymentDueAttachments.php
 * 
 * Fetches default attachments (invoice PDFs and service documents) for a given
 * customer and invoice header (service) from paymentPage.php.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

include_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/payment_due_attachments.php';

date_default_timezone_set('Asia/Kolkata');

if (empty($_SESSION['username']) || in_array(strtolower($_SESSION['role'] ?? ''), ['client', 'external'], true)) {
    echo json_encode(['success' => false, 'message' => 'Please sign in with an authorized staff account.']);
    exit;
}

require_once __DIR__ . '/include/rbac_matrix.php';
if (!aitf_has_access("Payment Due")) {
    echo json_encode(['success' => false, 'message' => 'Access Denied. Only authorized roles can view attachments.']);
    exit;
}

$customerId = isset($_REQUEST['customerId']) ? (int)$_REQUEST['customerId'] : 0;
$headerKey = isset($_REQUEST['headerKey']) ? trim((string)$_REQUEST['headerKey']) : '';

if ($customerId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Customer ID.']);
    exit;
}

try {
    $attachments = aitf_get_service_attachments($db, $customerId, $headerKey);
    echo json_encode([
        'success' => true,
        'customerId' => $customerId,
        'headerKey' => $headerKey,
        'attachments' => $attachments
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching attachments: ' . $e->getMessage()
    ]);
}

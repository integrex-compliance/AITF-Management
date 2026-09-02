<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/pending_doc_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Unauthorized. Please log in.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
    exit;
}

$userRole = $_SESSION['role'] ?? '';
$sessionUsername = trim($_SESSION['username'] ?? '');
$sessionAuditorId = (int)($_SESSION['auditor_id'] ?? 0);

$serviceType = strtoupper(trim($_POST['service_type'] ?? ''));
$serviceId = (int)($_POST['service_id'] ?? 0);
$customerId = (int)($_POST['customer_id'] ?? 0);
$customerName = trim($_POST['customer_name'] ?? '');
$documentKey = trim($_POST['document_key'] ?? '');
$documentName = trim($_POST['document_name'] ?? '');
$status = strtoupper(trim($_POST['status'] ?? ''));
$note = trim($_POST['note'] ?? '');
$targetAuditorName = trim($_POST['auditor_name'] ?? '');

if ($serviceType === '' || $serviceId <= 0 || $documentKey === '' || !in_array($status, array('YES', 'NO'), true)) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'message' => 'Invalid parameters provided'));
    exit;
}

// For auditor role, use their own session name
$auditorName = $sessionUsername;
$auditorId = $sessionAuditorId;

// If manager / admin is responding and specified an auditor, use that
if ($userRole !== 'Auditor' && $targetAuditorName !== '') {
    $auditorName = $targetAuditorName;
}

try {
    $savedId = aitf_save_pending_doc_communication($db, array(
        'service_type' => $serviceType,
        'service_id' => $serviceId,
        'customer_id' => $customerId,
        'customer_name' => $customerName,
        'document_key' => $documentKey,
        'document_name' => $documentName,
        'auditor_id' => $auditorId,
        'auditor_name' => $auditorName,
        'status' => $status,
        'note' => $note
    ));

    echo json_encode(array(
        'success' => true,
        'message' => $status === 'YES' ? "Marked as Sent by {$auditorName}" : "Marked as Pending from {$auditorName}",
        'id' => $savedId,
        'status' => $status,
        'auditor_name' => $auditorName,
        'note' => $note,
        'updated_at' => date('d M Y, h:i A')
    ));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
}

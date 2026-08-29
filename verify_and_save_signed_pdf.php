<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
session_start();

header('Content-Type: application/json; charset=utf-8');

include_once './dbConnect.php';
require_once __DIR__ . '/include/dsc_audit_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Method Not Allowed'));
    exit;
}

// Read JSON input
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    // Fallback to $_POST
    $data = $_POST;
}

$signedPdfBase64 = isset($data['signed_pdf_base64']) ? $data['signed_pdf_base64'] : '';
$signingTicket = isset($data['signing_ticket']) ? $data['signing_ticket'] : null;
$signerMeta = isset($data['signer_meta']) ? $data['signer_meta'] : array();
$certificateNumber = isset($data['certificate_number']) ? trim($data['certificate_number']) : '';

if (empty($signedPdfBase64) || empty($signingTicket)) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => 'Missing signed_pdf_base64 or signing_ticket payload'));
    exit;
}

// 1. Verify Signing Ticket HMAC & Nonce & Expiry
$ticketVerification = aitf_verify_signing_ticket($db, $signingTicket);
if (!$ticketVerification['valid']) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => $ticketVerification['error']));
    exit;
}

$requestId = $ticketVerification['request_id'];

// 2. Decode Signed PDF bytes
$signedPdfBytes = base64_decode($signedPdfBase64, true);
if ($signedPdfBytes === false || strlen($signedPdfBytes) < 1000) {
    aitf_record_signing_failure($db, $requestId, 'Malformed base64 PDF stream received');
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => 'Malformed base64 PDF stream received'));
    exit;
}

// 3. Record Signed Certificate, Verify PKCS#7 Structure & Store on Server Disk
$saveResult = aitf_record_signed_certificate($db, $requestId, $signedPdfBytes, $signerMeta, $certificateNumber);

if (!$saveResult['success']) {
    aitf_record_signing_failure($db, $requestId, $saveResult['error']);
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => $saveResult['error']));
    exit;
}

// 4. Return Verified Success with permanent download URL
echo json_encode(array(
    'success' => true,
    'message' => 'Signed Certificate verified and archived successfully',
    'filename' => $saveResult['filename'],
    'signed_pdf_path' => $saveResult['relative_path'],
    'sha256' => $saveResult['signed_pdf_sha256'],
    'request_id' => $requestId
));
exit;

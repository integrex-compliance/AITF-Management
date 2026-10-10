<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/include/rbac_matrix.php';
if (!aitf_has_access("Payment Due")) {
    http_response_code(403);
    exit('Access Denied');
}
// Buffer includes so the PDF response starts with its binary signature.
ob_start();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/payment_due_helper.php';
require_once __DIR__ . '/include/payment_report_pdf.php';

$filters = [];
foreach (['clientName', 'minAmount', 'maxAmount'] as $key) {
    $filters[$key] = is_scalar($_POST[$key] ?? '') ? trim((string)($_POST[$key] ?? '')) : '';
}
$payments = aitf_get_due_payments($db, $filters);
$pdf = aitf_build_payment_report_pdf($payments, $filters);
while (ob_get_level() > 0) ob_end_clean();
$pdf->Output('Payment_Pending_Report.pdf', 'I');

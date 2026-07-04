<?php
/**
 * exportInvoiceReport.php
 * Streams the Invoice Report as a PDF *download* (attachment).
 * All generation logic lives in include/invoice_report_pdf.php so it can be
 * shared with the email endpoint.
 */
$invoiceReportStartTime = microtime(true);
include_once __DIR__ . '/dbConnect.php';
include_once __DIR__ . '/commonutil.php';
include_once __DIR__ . '/include/invoice_report_pdf.php';
invoiceReportRegisterShutdownLogger('pdf');

while (ob_get_level() > 0) {
    ob_end_clean();
}

$invoiceType = $_POST['invoiceType'] ?? 'proforma';
$fromDate    = $_POST['fromDate'] ?? '';
$toDate      = $_POST['toDate'] ?? '';
$clientName  = $_POST['clientName'] ?? '';

invoiceReportLog('pdf.start', [
    'invoice_type' => $invoiceType,
    'from_date' => $fromDate,
    'to_date' => $toDate,
    'client_filter' => ($clientName !== '') ? 'yes' : 'no',
]);

try {
    invoiceReportLog('pdf.fetch.start');
    $data = fetchInvoiceReportData($db, $invoiceType, $fromDate, $toDate, $clientName);
    invoiceReportLog('pdf.fetch.done', [
        'invoice_count' => count($data['invoices']),
        'service_invoice_count' => count($data['svcMap']),
    ]);

    invoiceReportLog('pdf.build.start', ['invoice_count' => count($data['invoices'])]);
    $bytes = buildInvoiceReportPdfBytes($data, $invoiceType, $fromDate, $toDate, $clientName);
    invoiceReportLog('pdf.build.done', ['bytes' => strlen($bytes)]);
} catch (Throwable $e) {
    invoiceReportLog('pdf.exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    http_response_code(500);
    exit;
}

$typeLabel = ($invoiceType == 'proforma') ? 'PROFORMA' : 'FINAL';
$fileName  = 'Invoice_Report_' . $typeLabel . '_' . date('Ymd_His') . '.pdf';

while (ob_get_level() > 0) {
    ob_end_clean();
}

// Force a download instead of opening inline in a new tab.
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($bytes));
header('Cache-Control: max-age=0');
echo $bytes;
invoiceReportLog('pdf.output.done', ['bytes' => strlen($bytes)]);
exit;

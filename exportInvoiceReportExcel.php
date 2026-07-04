<?php
/**
 * exportInvoiceReportExcel.php
 * Streams the Invoice Report as a native .xlsx download.
 * All generation logic lives in include/invoice_report_lib.php so it can be
 * shared with the email endpoint.
 */
$invoiceReportStartTime = microtime(true);
include_once __DIR__ . '/dbConnect.php';
include_once __DIR__ . '/commonutil.php';
include_once __DIR__ . '/include/invoice_report_lib.php';
invoiceReportRegisterShutdownLogger('excel');

while (ob_get_level() > 0) {
    ob_end_clean();
}

$invoiceType = $_POST['invoiceType'] ?? 'proforma';
$fromDate    = $_POST['fromDate'] ?? '';
$toDate      = $_POST['toDate'] ?? '';
$clientName  = $_POST['clientName'] ?? '';

invoiceReportLog('excel.start', [
    'invoice_type' => $invoiceType,
    'from_date' => $fromDate,
    'to_date' => $toDate,
    'client_filter' => ($clientName !== '') ? 'yes' : 'no',
    'ziparchive' => class_exists('ZipArchive') ? 'available' : 'missing',
    'tmp_dir' => sys_get_temp_dir(),
    'tmp_writable' => is_writable(sys_get_temp_dir()) ? 'yes' : 'no',
]);

try {
    invoiceReportLog('excel.fetch.start');
    $data = fetchInvoiceReportData($db, $invoiceType, $fromDate, $toDate, $clientName);
    invoiceReportLog('excel.fetch.done', [
        'invoice_count' => count($data['invoices']),
        'service_invoice_count' => count($data['svcMap']),
    ]);

    invoiceReportLog('excel.build.start', ['invoice_count' => count($data['invoices'])]);
    $bytes = buildInvoiceReportXlsxBytes($data, $invoiceType, $fromDate, $toDate, $clientName);
    invoiceReportLog('excel.build.done', ['bytes' => strlen($bytes)]);
} catch (Throwable $e) {
    invoiceReportLog('excel.exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(500);
    exit;
}

$typeLabel = ($invoiceType == 'proforma') ? 'PROFORMA' : 'FINAL';
$fileName  = 'Invoice_Report_' . $typeLabel . '_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($bytes));
header('Cache-Control: max-age=0');
echo $bytes;
invoiceReportLog('excel.output.done', ['bytes' => strlen($bytes)]);
exit;

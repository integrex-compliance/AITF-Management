<?php
/**
 * exportInvoiceReportExcel.php
 * Streams the Invoice Report as a native .xlsx download.
 * All generation logic lives in include/invoice_report_lib.php so it can be
 * shared with the email endpoint.
 */
include_once './dbConnect.php';
include_once './commonutil.php';
include_once './include/invoice_report_lib.php';

while (ob_get_level() > 0) {
    ob_end_clean();
}

$invoiceType = $_POST['invoiceType'] ?? 'proforma';
$fromDate    = $_POST['fromDate'] ?? '';
$toDate      = $_POST['toDate'] ?? '';
$clientName  = $_POST['clientName'] ?? '';

$data  = fetchInvoiceReportData($db, $invoiceType, $fromDate, $toDate, $clientName);
$bytes = buildInvoiceReportXlsxBytes($data, $invoiceType, $fromDate, $toDate, $clientName);

$typeLabel = ($invoiceType == 'proforma') ? 'PROFORMA' : 'FINAL';
$fileName  = 'Invoice_Report_' . $typeLabel . '_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($bytes));
header('Cache-Control: max-age=0');
echo $bytes;
exit;

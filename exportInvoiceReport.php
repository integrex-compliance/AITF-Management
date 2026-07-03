<?php
/**
 * exportInvoiceReport.php
 * Streams the Invoice Report as a PDF *download* (attachment).
 * All generation logic lives in include/invoice_report_pdf.php so it can be
 * shared with the email endpoint.
 */
include_once './dbConnect.php';
include_once './commonutil.php';
include_once './include/invoice_report_pdf.php';

while (ob_get_level() > 0) {
    ob_end_clean();
}

$invoiceType = $_POST['invoiceType'] ?? 'proforma';
$fromDate    = $_POST['fromDate'] ?? '';
$toDate      = $_POST['toDate'] ?? '';
$clientName  = $_POST['clientName'] ?? '';

$data  = fetchInvoiceReportData($db, $invoiceType, $fromDate, $toDate, $clientName);
$bytes = buildInvoiceReportPdfBytes($data, $invoiceType, $fromDate, $toDate, $clientName);

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
exit;

<?php
include_once './dbConnect.php';
include_once './commonutil.php';
include_once './include/tds_report_pdf.php';

while (ob_get_level() > 0) {
    ob_end_clean();
}

$status = $_REQUEST['status'] ?? 'all';
$startDateStr = $_REQUEST['startDate'] ?? '';
$endDateStr = $_REQUEST['endDate'] ?? '';
$clientSearch = $_REQUEST['clientSearch'] ?? '';
$sortOrder = $_REQUEST['sortOrder'] ?? '';

$data = fetchTdsReportData($db, $status, $startDateStr, $endDateStr, $clientSearch, $sortOrder);
$bytes = buildTdsReportPdfBytes($data, $status, $startDateStr, $endDateStr, $clientSearch, $sortOrder);
$fileName = 'TDS_Report_' . date('Ymd_His') . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($bytes));
header('Cache-Control: max-age=0');
echo $bytes;
exit;

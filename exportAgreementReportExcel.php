<?php
include_once './dbConnect.php';
include_once './commonutil.php';
include_once './include/agreement_report_lib.php';

while (ob_get_level() > 0) {
    ob_end_clean();
}

$fromDate = $_POST['fromDate'] ?? '';
$toDate = $_POST['toDate'] ?? '';
$clientName = $_POST['clientName'] ?? '';

$data = fetchAgreementReportData($db, $fromDate, $toDate, $clientName);
$bytes = buildAgreementReportXlsxBytes($data, $fromDate, $toDate, $clientName);
$fileName = 'Agreement_Report_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($bytes));
header('Cache-Control: max-age=0');
echo $bytes;
exit;

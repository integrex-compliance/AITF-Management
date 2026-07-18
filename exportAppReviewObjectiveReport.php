<?php
include_once __DIR__ . '/dbConnect.php';
include_once __DIR__ . '/include/app_review_objective_report_pdf.php';

$fromDate = trim($_POST['fromDate'] ?? '');
$toDate = trim($_POST['toDate'] ?? '');
$clientName = trim($_POST['clientName'] ?? '');

try {
    $records = fetchAppReviewObjectiveReportData($db, $fromDate, $toDate, $clientName);
    $bytes = buildAppReviewObjectiveReportPdfBytes($records, $fromDate, $toDate, $clientName);
} catch (Throwable $e) {
    error_log('APP REVIEW OBJECTIVE PDF EXPORT FAIL: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Could not generate the PDF report. ' . $e->getMessage();
    exit;
}

while (ob_get_level() > 0) {
    ob_end_clean();
}

$fileName = 'Application_Review_Objective_Report_' . date('Ymd_His') . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($bytes));
header('Cache-Control: max-age=0');
echo $bytes;
exit;

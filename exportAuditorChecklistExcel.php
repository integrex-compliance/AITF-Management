<?php

require_once __DIR__ . '/include/auditor_checklist_report_lib.php';

date_default_timezone_set('Asia/Kolkata');

while (ob_get_level() > 0) {
    ob_end_clean();
}

function auditorChecklistExportError($message, $statusCode = 400)
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode(array('success' => false, 'message' => $message));
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    auditorChecklistExportError('Only POST requests are allowed.', 405);
}

$rawBody = file_get_contents('php://input');
if ($rawBody === false || trim($rawBody) === '') {
    auditorChecklistExportError('No checklist data was received.');
}

$payload = json_decode($rawBody, true);
if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
    auditorChecklistExportError('The checklist export data is invalid.');
}

$rows = isset($payload['rows']) && is_array($payload['rows']) ? $payload['rows'] : array();
$filters = isset($payload['filters']) && is_array($payload['filters']) ? $payload['filters'] : array();

if (count($rows) > 5000) {
    auditorChecklistExportError('Too many rows were submitted for one Excel export.');
}

try {
    $bytes = buildAuditorChecklistXlsxBytes($rows, $filters);
} catch (Throwable $error) {
    error_log('AUDITOR_CHECKLIST_EXCEL_EXPORT: ' . $error->getMessage());
    auditorChecklistExportError('Excel report could not be generated. Please try again.', 500);
}

$fileName = 'Auditor_Checklist_Report_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($bytes));
header('Cache-Control: max-age=0');
header('Pragma: public');
header('X-Content-Type-Options: nosniff');

echo $bytes;
exit;


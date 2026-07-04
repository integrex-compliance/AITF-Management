<?php
/**
 * exportInvoiceReport.php
 * Streams the Invoice Report as a PDF *download* (attachment).
 * All generation logic lives in include/invoice_report_pdf.php so it can be
 * shared with the email endpoint.
 */
$invoiceReportStartTime = microtime(true);
$invoiceExportType = 'PDF';
$invoiceExportStage = 'bootstrap';
$invoiceExportDebugRendered = false;

function invoiceExportDebugBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . $units[$i];
}

function invoiceExportDebugLog(array $diagnostics) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . json_encode($diagnostics) . PHP_EOL;
    @file_put_contents(__DIR__ . '/invoice_export_debug.log', $line, FILE_APPEND);
}

function invoiceExportDebugRender($errorType, $message, $file, $line) {
    global $invoiceExportDebugRendered, $invoiceExportType, $invoiceExportStage;
    if ($invoiceExportDebugRendered) {
        return;
    }
    $invoiceExportDebugRendered = true;

    $diagnostics = [
        'export_type' => $invoiceExportType,
        'stage' => $invoiceExportStage,
        'error_type' => $errorType,
        'message' => $message,
        'file' => $file,
        'line' => $line,
        'php_version' => PHP_VERSION,
        'memory_usage' => invoiceExportDebugBytes(memory_get_usage(true)),
        'peak_memory_usage' => invoiceExportDebugBytes(memory_get_peak_usage(true)),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
    ];
    invoiceExportDebugLog($diagnostics);

    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    }

    $rows = '';
    foreach ($diagnostics as $label => $value) {
        $rows .= '<div class="diag-row"><div class="diag-label">' . htmlspecialchars(str_replace('_', ' ', strtoupper($label)), ENT_QUOTES, 'UTF-8') . '</div><div class="diag-value">' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</div></div>';
    }

    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>Invoice Report Export Failed</title>'
        . '<style>'
        . 'body{margin:0;background:#f3f4f6;color:#111827;font-family:Arial,Helvetica,sans-serif;}'
        . '.wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px;}'
        . '.card{width:100%;max-width:860px;background:#fff;border-radius:14px;box-shadow:0 20px 45px rgba(15,23,42,.14);border:1px solid #e5e7eb;overflow:hidden;}'
        . '.head{padding:24px 28px;border-bottom:1px solid #e5e7eb;}'
        . '.badge{display:inline-block;background:#dc2626;color:#fff;font-size:12px;font-weight:700;border-radius:999px;padding:6px 10px;margin-bottom:12px;}'
        . 'h1{font-size:24px;line-height:1.2;margin:0 0 6px;}'
        . '.note{color:#6b7280;font-size:13px;margin:0;}'
        . '.body{padding:10px 28px 28px;}'
        . '.diag-row{display:grid;grid-template-columns:230px 1fr;gap:16px;padding:13px 0;border-bottom:1px solid #f1f5f9;}'
        . '.diag-label{color:#6b7280;font-size:12px;font-weight:700;letter-spacing:.04em;}'
        . '.diag-value{font-family:Consolas,Menlo,monospace;font-size:13px;line-height:1.45;word-break:break-word;color:#111827;}'
        . '@media(max-width:700px){.diag-row{grid-template-columns:1fr;gap:4px}.wrap{padding:16px}.head,.body{padding-left:18px;padding-right:18px}}'
        . '</style></head><body><main class="wrap"><section class="card"><div class="head">'
        . '<span class="badge">EXPORT ERROR</span><h1>Invoice Report Export Failed</h1><p class="note">Temporary Debug Mode</p>'
        . '</div><div class="body">' . $rows . '</div></section></main></body></html>';
}

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        invoiceExportDebugRender('Fatal Error ' . $error['type'], $error['message'], $error['file'], $error['line']);
    }
});

$invoiceExportStage = 'loading dependencies';
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

$invoiceExportStage = 'request initialized';
invoiceReportLog('pdf.start', [
    'invoice_type' => $invoiceType,
    'from_date' => $fromDate,
    'to_date' => $toDate,
    'client_filter' => ($clientName !== '') ? 'yes' : 'no',
]);

try {
    $invoiceExportStage = 'fetching invoice report data';
    invoiceReportLog('pdf.fetch.start');
    $data = fetchInvoiceReportData($db, $invoiceType, $fromDate, $toDate, $clientName);
    invoiceReportLog('pdf.fetch.done', [
        'invoice_count' => count($data['invoices']),
        'service_invoice_count' => count($data['svcMap']),
    ]);

    $invoiceExportStage = 'building PDF report';
    invoiceReportLog('pdf.build.start', ['invoice_count' => count($data['invoices'])]);
    $bytes = buildInvoiceReportPdfBytes($data, $invoiceType, $fromDate, $toDate, $clientName);
    invoiceReportLog('pdf.build.done', ['bytes' => strlen($bytes)]);
} catch (Throwable $e) {
    invoiceReportLog('pdf.exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    invoiceExportDebugRender(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
    exit;
}

$typeLabel = ($invoiceType == 'proforma') ? 'PROFORMA' : 'FINAL';
$fileName  = 'Invoice_Report_' . $typeLabel . '_' . date('Ymd_His') . '.pdf';

while (ob_get_level() > 0) {
    ob_end_clean();
}

// Force a download instead of opening inline in a new tab.
$invoiceExportStage = 'sending PDF response';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($bytes));
header('Cache-Control: max-age=0');
echo $bytes;
invoiceReportLog('pdf.output.done', ['bytes' => strlen($bytes)]);
exit;

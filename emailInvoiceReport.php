<?php
/**
 * emailInvoiceReport.php
 * Generates the Invoice Report (PDF or Excel) and emails it as an attachment
 * from certification@aitf.co.in to the address supplied by the user.
 *
 * Expects POST: invoiceType, fromDate, toDate, clientName, format (pdf|excel), email
 * Responds with JSON: { success: bool, message: string }
 */
$invoiceReportStartTime = microtime(true);
header('Content-Type: application/json');

include_once __DIR__ . '/dbConnect.php';
include_once __DIR__ . '/commonutil.php';
include_once __DIR__ . '/include/invoice_report_lib.php';
include_once __DIR__ . '/include/invoice_mail_helper.php';
invoiceReportRegisterShutdownLogger('mail');

function respond($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

$invoiceType = $_POST['invoiceType'] ?? 'proforma';
$fromDate    = $_POST['fromDate'] ?? '';
$toDate      = $_POST['toDate'] ?? '';
$clientName  = $_POST['clientName'] ?? '';
$format      = strtolower($_POST['format'] ?? 'excel');
$email       = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}

if ($format !== 'pdf' && $format !== 'excel') {
    $format = 'excel';
}

$typeLabel = ($invoiceType === 'proforma') ? 'PROFORMA' : (($invoiceType === 'pending') ? 'PENDING' : 'FINAL');

try {
    invoiceReportLog('mail.fetch.start', [
        'invoice_type' => $invoiceType,
        'from_date' => $fromDate,
        'to_date' => $toDate,
        'client_filter' => ($clientName !== '') ? 'yes' : 'no',
        'format' => $format,
    ]);
    $data = fetchInvoiceReportData($db, $invoiceType, $fromDate, $toDate, $clientName);
    invoiceReportLog('mail.fetch.done', [
        'invoice_count' => count($data['invoices']),
        'service_invoice_count' => count($data['svcMap']),
    ]);

    if ($format === 'pdf') {
        include_once __DIR__ . '/include/invoice_report_pdf.php';
        invoiceReportLog('mail.pdf_build.start', ['invoice_count' => count($data['invoices'])]);
        $bytes = buildInvoiceReportPdfBytes($data, $invoiceType, $fromDate, $toDate, $clientName);
        invoiceReportLog('mail.pdf_build.done', ['bytes' => strlen($bytes)]);
        $attachmentName = 'Invoice_Report_' . $typeLabel . '_' . date('Ymd_His') . '.pdf';
        $mimeType = 'application/pdf';
        $formatWord = 'PDF';
    } else {
        invoiceReportLog('mail.excel_build.start', ['invoice_count' => count($data['invoices'])]);
        $bytes = buildInvoiceReportXlsxBytes($data, $invoiceType, $fromDate, $toDate, $clientName);
        invoiceReportLog('mail.excel_build.done', ['bytes' => strlen($bytes)]);
        $attachmentName = 'Invoice_Report_' . $typeLabel . '_' . date('Ymd_His') . '.xlsx';
        $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $formatWord = 'Excel';
    }
} catch (Exception $e) {
    error_log('INVOICE REPORT MAIL BUILD FAIL: ' . $e->getMessage());
    respond(false, 'Could not generate the report. Please try again.');
}

// --- Compose the mail ---
try {
    aitfLoadPHPMailer();
    $mailConfig = aitfGetInvoiceMailConfig();

    // Filter description for the mail body
    $periodText = '';
    if ($fromDate !== '' || $toDate !== '') {
        $periodText = ($fromDate !== '' ? $fromDate : 'Start') . ' to ' . ($toDate !== '' ? $toDate : 'End');
    }

    $bodyRows = '<li><strong>Report:</strong> ' . aitfInvoiceEscape($typeLabel) . ' Tax Invoice Report</li>'
              . '<li><strong>Format:</strong> ' . aitfInvoiceEscape($formatWord) . '</li>';
    if ($periodText !== '') {
        $bodyRows .= '<li><strong>Period:</strong> ' . aitfInvoiceEscape($periodText) . '</li>';
    }
    if ($clientName !== '') {
        $bodyRows .= '<li><strong>Client:</strong> ' . aitfInvoiceEscape($clientName) . '</li>';
    }

    $body = '<html><body>'
        . '<p>Dear Sir/Madam,</p>'
        . '<p>Greetings from AITF Management Certification Limited.</p>'
        . '<p>As per your request, the <strong>' . aitfInvoiceEscape($formatWord)
        . '</strong> sheet has been sent to your mail id. Please find the '
        . aitfInvoiceEscape($typeLabel) . ' Tax Invoice Report attached with this email.</p>'
        . '<p><strong>Report Details:</strong></p>'
        . '<ul>' . $bodyRows . '</ul>'
        . '<p>If you need any clarification, please feel free to contact us.</p>'
        . '<p>Thank you.</p>'
        . '<p>Warm regards,<br>'
        . 'AITF Management Certification Limited<br>'
        . 'Email: <a href="mailto:certification@aitf.co.in">certification@aitf.co.in</a><br>'
        . 'Website: <a href="http://www.aitf.co.in">www.aitf.co.in</a></p>'
        . '</body></html>';

    $mailer = new PHPMailer();
    $mailer->isHTML(true);
    $mailer->Subject = $typeLabel . ' Tax Invoice Report (' . $formatWord . ')';
    $mailer->Body = $body;
    $mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</li>'], "\n", $body));

    $added = aitfAddEmailRecipients($mailer, $email);
    if ($added === 0) {
        respond(false, 'Please enter a valid email address.');
    }

    $mailer->addStringAttachment($bytes, $attachmentName, 'base64', $mimeType);

    list($mailSent, $mailError) = aitfSendInvoiceMailer($mailer);

    if ($mailSent) {
        respond(true, 'The ' . $formatWord . ' report has been sent to ' . $email . '.');
    }

    error_log('INVOICE REPORT MAIL SEND FAIL: ' . $mailError);
    respond(false, 'Could not send the email right now. Please try again later.');
} catch (Exception $e) {
    error_log('INVOICE REPORT MAIL EXCEPTION: ' . $e->getMessage());
    respond(false, 'Could not send the email. Please try again later.');
}

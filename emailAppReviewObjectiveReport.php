<?php
$invoiceReportStartTime = microtime(true);
header('Content-Type: application/json; charset=UTF-8');

include_once __DIR__ . '/dbConnect.php';
include_once __DIR__ . '/include/app_review_objective_report_lib.php';
include_once __DIR__ . '/include/invoice_mail_helper.php';

function appReviewObjectiveMailResponse($success, $message) {
    echo json_encode(array('success' => $success, 'message' => $message));
    exit;
}

$fromDate = trim($_POST['fromDate'] ?? '');
$toDate = trim($_POST['toDate'] ?? '');
$clientName = trim($_POST['clientName'] ?? '');
$format = strtolower(trim($_POST['format'] ?? 'excel'));
$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    appReviewObjectiveMailResponse(false, 'Please enter a valid email address.');
}
if (!in_array($format, array('pdf', 'excel'), true)) {
    $format = 'excel';
}

try {
    $records = fetchAppReviewObjectiveReportData($db, $fromDate, $toDate, $clientName);
    if ($format === 'pdf') {
        include_once __DIR__ . '/include/app_review_objective_report_pdf.php';
        $bytes = buildAppReviewObjectiveReportPdfBytes($records, $fromDate, $toDate, $clientName);
        $attachmentName = 'Application_Review_Objective_Report_' . date('Ymd_His') . '.pdf';
        $mimeType = 'application/pdf';
        $formatLabel = 'PDF';
    } else {
        $bytes = buildAppReviewObjectiveReportXlsxBytes($records, $fromDate, $toDate, $clientName);
        $attachmentName = 'Application_Review_Objective_Report_' . date('Ymd_His') . '.xlsx';
        $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $formatLabel = 'Excel';
    }
} catch (Throwable $e) {
    error_log('APP REVIEW OBJECTIVE REPORT BUILD FAIL: ' . $e->getMessage());
    appReviewObjectiveMailResponse(false, 'Could not generate the report. Please try again.');
}

try {
    aitfLoadPHPMailer();
    $periodText = ($fromDate !== '' || $toDate !== '')
        ? (($fromDate !== '' ? $fromDate : 'Start') . ' to ' . ($toDate !== '' ? $toDate : 'End'))
        : 'All Records';

    $body = '<html><body>'
        . '<p>Dear Sir/Madam,</p>'
        . '<p>Please find the Application Review Objective Report attached with this email.</p>'
        . '<p><strong>Report Details:</strong></p>'
        . '<ul>'
        . '<li><strong>Format:</strong> ' . aitfInvoiceEscape($formatLabel) . '</li>'
        . '<li><strong>Period:</strong> ' . aitfInvoiceEscape($periodText) . '</li>'
        . '<li><strong>Total Records:</strong> ' . count($records) . '</li>'
        . ($clientName !== '' ? '<li><strong>Client:</strong> ' . aitfInvoiceEscape($clientName) . '</li>' : '')
        . '</ul>'
        . '<p>Warm regards,<br>AITF Management Certification Limited<br>'
        . 'Email: <a href="mailto:certification@aitf.co.in">certification@aitf.co.in</a></p>'
        . '</body></html>';

    $mailer = new PHPMailer();
    $mailer->isHTML(true);
    $mailer->Subject = 'Application Review Objective Report (' . $formatLabel . ')';
    $mailer->Body = $body;
    $mailer->AltBody = strip_tags(str_replace(array('<br>', '<br/>', '<br />', '</p>', '</li>'), "\n", $body));

    if (aitfAddEmailRecipients($mailer, $email) === 0) {
        appReviewObjectiveMailResponse(false, 'Please enter a valid email address.');
    }

    $mailer->addStringAttachment($bytes, $attachmentName, 'base64', $mimeType);
    list($sent, $mailError) = aitfSendInvoiceMailer($mailer);
    if ($sent) {
        appReviewObjectiveMailResponse(true, 'The ' . $formatLabel . ' report has been sent to ' . $email . '.');
    }

    error_log('APP REVIEW OBJECTIVE REPORT MAIL FAIL: ' . $mailError);
    appReviewObjectiveMailResponse(false, 'Could not send the email right now. Please try again later.');
} catch (Throwable $e) {
    error_log('APP REVIEW OBJECTIVE REPORT MAIL EXCEPTION: ' . $e->getMessage());
    appReviewObjectiveMailResponse(false, 'Could not send the email. Please try again later.');
}

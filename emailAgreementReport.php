<?php
header('Content-Type: application/json');

include_once './dbConnect.php';
include_once './commonutil.php';
include_once './include/agreement_report_lib.php';
include_once './include/invoice_mail_helper.php';

function agreementReportRespond($success, $message) {
    echo json_encode(array('success' => $success, 'message' => $message));
    exit;
}

$fromDate = $_POST['fromDate'] ?? '';
$toDate = $_POST['toDate'] ?? '';
$clientName = $_POST['clientName'] ?? '';
$format = strtolower($_POST['format'] ?? 'excel');
$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    agreementReportRespond(false, 'Please enter a valid email address.');
}
if ($format !== 'pdf' && $format !== 'excel') {
    $format = 'excel';
}

try {
    $data = fetchAgreementReportData($db, $fromDate, $toDate, $clientName);
    if ($format === 'pdf') {
        include_once './include/agreement_report_pdf.php';
        $bytes = buildAgreementReportPdfBytes($data, $fromDate, $toDate, $clientName);
        $attachmentName = 'Agreement_Report_' . date('Ymd_His') . '.pdf';
        $mimeType = 'application/pdf';
        $formatWord = 'PDF';
    } else {
        $bytes = buildAgreementReportXlsxBytes($data, $fromDate, $toDate, $clientName);
        $attachmentName = 'Agreement_Report_' . date('Ymd_His') . '.xlsx';
        $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $formatWord = 'Excel';
    }
} catch (Exception $e) {
    error_log('AGREEMENT REPORT MAIL BUILD FAIL: ' . $e->getMessage());
    agreementReportRespond(false, 'Could not generate the report. Please try again.');
}

try {
    aitfLoadPHPMailer();

    $periodText = '';
    if ($fromDate !== '' || $toDate !== '') {
        $periodText = ($fromDate !== '' ? $fromDate : 'Start') . ' to ' . ($toDate !== '' ? $toDate : 'End');
    }

    $bodyRows = '<li><strong>Report:</strong> Certification Agreement Report</li>'
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
        . '</strong> agreement report has been sent to your mail id. Please find the Certification Agreement Report attached with this email.</p>'
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
    $mailer->Subject = 'Certification Agreement Report (' . $formatWord . ')';
    $mailer->Body = $body;
    $mailer->AltBody = strip_tags(str_replace(array('<br>', '<br/>', '<br />', '</p>', '</li>'), "\n", $body));

    $added = aitfAddEmailRecipients($mailer, $email);
    if ($added === 0) {
        agreementReportRespond(false, 'Please enter a valid email address.');
    }

    $mailer->addStringAttachment($bytes, $attachmentName, 'base64', $mimeType);
    list($mailSent, $mailError) = aitfSendInvoiceMailer($mailer);

    if ($mailSent) {
        agreementReportRespond(true, 'The ' . $formatWord . ' report has been sent to ' . $email . '.');
    }

    error_log('AGREEMENT REPORT MAIL SEND FAIL: ' . $mailError);
    agreementReportRespond(false, 'Could not send the email right now. Please try again later.');
} catch (Exception $e) {
    error_log('AGREEMENT REPORT MAIL EXCEPTION: ' . $e->getMessage());
    agreementReportRespond(false, 'Could not send the email. Please try again later.');
}

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/include/rbac_matrix.php';
if (!aitf_has_access("TDS Deducted")) {
    echo json_encode(array('success' => false, 'message' => 'Access Denied. Only Top Mgmt and Finance roles are authorized.'));
    exit;
}

include_once './dbConnect.php';
include_once './commonutil.php';
include_once './include/tds_report_lib.php';
include_once './include/invoice_mail_helper.php';

function tdsReportRespond($success, $message) {
    echo json_encode(array('success' => $success, 'message' => $message));
    exit;
}

$status = $_POST['status'] ?? 'all';
$startDateStr = $_POST['startDate'] ?? '';
$endDateStr = $_POST['endDate'] ?? '';
$clientSearch = $_POST['clientSearch'] ?? '';
$sortOrder = $_POST['sortOrder'] ?? '';
$format = strtolower($_POST['format'] ?? 'excel');
$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    tdsReportRespond(false, 'Please enter a valid email address.');
}
if ($format !== 'pdf' && $format !== 'excel') {
    $format = 'excel';
}

try {
    $data = fetchTdsReportData($db, $status, $startDateStr, $endDateStr, $clientSearch, $sortOrder);
    if ($format === 'pdf') {
        include_once './include/tds_report_pdf.php';
        $bytes = buildTdsReportPdfBytes($data, $status, $startDateStr, $endDateStr, $clientSearch, $sortOrder);
        $attachmentName = 'TDS_Report_' . date('Ymd_His') . '.pdf';
        $mimeType = 'application/pdf';
        $formatWord = 'PDF';
    } else {
        $bytes = buildTdsReportXlsxBytes($data, $status, $startDateStr, $endDateStr, $clientSearch, $sortOrder);
        $attachmentName = 'TDS_Report_' . date('Ymd_His') . '.xlsx';
        $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $formatWord = 'Excel';
    }
} catch (Exception $e) {
    error_log('TDS REPORT MAIL BUILD FAIL: ' . $e->getMessage());
    tdsReportRespond(false, 'Could not generate the report. Please try again.');
}

try {
    aitfLoadPHPMailer();

    $periodText = '';
    if ($startDateStr !== '' || $endDateStr !== '') {
        $periodText = ($startDateStr !== '' ? $startDateStr : 'Start') . ' to ' . ($endDateStr !== '' ? $endDateStr : 'End');
    }

    $bodyRows = '<li><strong>Report:</strong> ' . aitfInvoiceEscape(tdsReportTitle($status)) . '</li>'
        . '<li><strong>Format:</strong> ' . aitfInvoiceEscape($formatWord) . '</li>'
        . '<li><strong>Status:</strong> ' . aitfInvoiceEscape(strtoupper($status)) . '</li>';
    if ($periodText !== '') {
        $bodyRows .= '<li><strong>Period:</strong> ' . aitfInvoiceEscape($periodText) . '</li>';
    }
    if ($clientSearch !== '') {
        $bodyRows .= '<li><strong>Client:</strong> ' . aitfInvoiceEscape($clientSearch) . '</li>';
    }

    $body = '<html><body>'
        . '<p>Dear Sir/Madam,</p>'
        . '<p>Greetings from AITF Management Certification Limited.</p>'
        . '<p>As per your request, the <strong>' . aitfInvoiceEscape($formatWord)
        . '</strong> TDS report has been sent to your mail id. Please find the report attached with this email.</p>'
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
    $mailer->Subject = tdsReportTitle($status) . ' (' . $formatWord . ')';
    $mailer->Body = $body;
    $mailer->AltBody = strip_tags(str_replace(array('<br>', '<br/>', '<br />', '</p>', '</li>'), "\n", $body));

    $added = aitfAddEmailRecipients($mailer, $email);
    if ($added === 0) {
        tdsReportRespond(false, 'Please enter a valid email address.');
    }

    $mailer->addStringAttachment($bytes, $attachmentName, 'base64', $mimeType);
    list($mailSent, $mailError) = aitfSendInvoiceMailer($mailer);

    if ($mailSent) {
        tdsReportRespond(true, 'The ' . $formatWord . ' report has been sent to ' . $email . '.');
    }

    error_log('TDS REPORT MAIL SEND FAIL: ' . $mailError);
    tdsReportRespond(false, 'Could not send the email right now. Please try again later.');
} catch (Exception $e) {
    error_log('TDS REPORT MAIL EXCEPTION: ' . $e->getMessage());
    tdsReportRespond(false, 'Could not send the email. Please try again later.');
}

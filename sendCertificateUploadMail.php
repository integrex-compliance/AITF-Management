<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
header('Content-Type: application/json');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

include_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/schema_auto_sync.php';
if (isset($db) && $db) {
    aitf_auto_sync_schema($db);
}
require_once __DIR__ . '/testemail.php';

register_shutdown_function(function () {
    $err = error_get_last();
    if (!$err) {
        return;
    }
    $fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
    if (!in_array($err['type'], $fatalTypes, true)) {
        return;
    }
    if (headers_sent() === false) {
        header('Content-Type: application/json');
        http_response_code(500);
    }
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    echo json_encode(array(
        'success' => false,
        'message' => 'Server error: ' . ($err['message'] ?? 'Unknown error')
    ));
});

/**
 * Helper to resolve the correct ISO standard for a given service & customer
 */
function aitfResolveServiceStandard($db, $crrId, $serviceId, $universalServiceId, $customerId)
{
    // 1. Check cdc_report_review_office_use by crr_id
    if (!empty($crrId)) {
        try {
            $isoStmt = $db->prepare("SELECT DISTINCT iso_label FROM cdc_report_review_office_use WHERE crr_id = ? AND iso_label IS NOT NULL AND TRIM(iso_label) != '' ORDER BY iso_index ASC");
            $isoStmt->execute(array($crrId));
            $isoRows = $isoStmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($isoRows)) {
                $cleaned = array_filter(array_map('trim', $isoRows));
                if (!empty($cleaned)) {
                    return implode(' & ', $cleaned);
                }
            }
        } catch (Throwable $e) {}

        // 2. Check cdc_report_review covered_for
        try {
            $crrStmt = $db->prepare("SELECT covered_for FROM cdc_report_review WHERE crr_id = ? LIMIT 1");
            $crrStmt->execute(array($crrId));
            $coveredFor = trim($crrStmt->fetchColumn() ?: '');
            if (!empty($coveredFor)) {
                return $coveredFor;
            }
        } catch (Throwable $e) {}
    }

    // 3. If crrId is empty, try to find crr by serviceca_id
    if (!empty($serviceId)) {
        try {
            $crrStmt2 = $db->prepare("SELECT crr_id, covered_for FROM cdc_report_review WHERE ca_service_id = ? ORDER BY crr_id DESC LIMIT 1");
            $crrStmt2->execute(array($serviceId));
            $crrRow = $crrStmt2->fetch(PDO::FETCH_ASSOC);
            if ($crrRow && !empty($crrRow['crr_id'])) {
                $subIsoStmt = $db->prepare("SELECT DISTINCT iso_label FROM cdc_report_review_office_use WHERE crr_id = ? AND iso_label IS NOT NULL AND TRIM(iso_label) != '' ORDER BY iso_index ASC");
                $subIsoStmt->execute(array($crrRow['crr_id']));
                $subIsoRows = $subIsoStmt->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($subIsoRows)) {
                    $cleaned = array_filter(array_map('trim', $subIsoRows));
                    if (!empty($cleaned)) {
                        return implode(' & ', $cleaned);
                    }
                }
                if (!empty(trim($crrRow['covered_for'] ?? ''))) {
                    return trim($crrRow['covered_for']);
                }
            }
        } catch (Throwable $e) {}
    }

    // 4. Check audit_schedule_id by serviceca_id or customer_ca_id
    try {
        if (!empty($serviceId)) {
            $schStmt = $db->prepare("SELECT schedule_app_standard FROM audit_schedule_id WHERE serviceca_id = ? AND schedule_app_standard IS NOT NULL AND TRIM(schedule_app_standard) != '' ORDER BY audit_schedule_id DESC LIMIT 1");
            $schStmt->execute(array($serviceId));
            $std = trim($schStmt->fetchColumn() ?: '');
            if (!empty($std)) {
                return $std;
            }
        }
        if (!empty($customerId)) {
            $schStmt2 = $db->prepare("SELECT schedule_app_standard FROM audit_schedule_id WHERE customer_ca_id = ? AND schedule_app_standard IS NOT NULL AND TRIM(schedule_app_standard) != '' ORDER BY audit_schedule_id DESC LIMIT 1");
            $schStmt2->execute(array($customerId));
            $std2 = trim($schStmt2->fetchColumn() ?: '');
            if (!empty($std2)) {
                return $std2;
            }
        }
    } catch (Throwable $e) {}

    // 5. Check customer_info standard_for
    if (!empty($customerId)) {
        try {
            $custStmt = $db->prepare("SELECT standard_for FROM customer_info WHERE id = ? LIMIT 1");
            $custStmt->execute(array($customerId));
            $cStd = trim($custStmt->fetchColumn() ?: '');
            if (!empty($cStd)) {
                return $cStd;
            }
        } catch (Throwable $e) {}
    }

    // 6. Default standard fallback
    return 'ISO 9001:2015';
}

/**
 * Resolve local disk path of an uploaded certificate
 */
function aitfResolveCertificateDiskPath($rawPath)
{
    $rawPath = trim((string)$rawPath);
    if ($rawPath === '') {
        return null;
    }
    $norm = str_replace('\\', '/', $rawPath);
    $relPath = ltrim($norm, '/');
    $candidate1 = __DIR__ . '/' . $relPath;
    if (file_exists($candidate1) && is_file($candidate1)) {
        return $candidate1;
    }
    if (file_exists($rawPath) && is_file($rawPath)) {
        return $rawPath;
    }
    if (file_exists($norm) && is_file($norm)) {
        return $norm;
    }
    // Fallback: check if directory exists and has any uploaded file
    $dir = dirname($candidate1);
    if (is_dir($dir)) {
        $files = glob($dir . '/*.*');
        if (!empty($files)) {
            usort($files, function ($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            foreach ($files as $f) {
                if (is_file($f)) {
                    return $f;
                }
            }
        }
    }
    return null;
}

try {
    $reqAction = $_GET['action'] ?? $_POST['action'] ?? '';
    $customerId = intval($_GET['customer'] ?? $_POST['customer'] ?? 0);
    $serviceId = intval($_GET['service'] ?? $_POST['service'] ?? 0);
    $universalServiceId = intval($_GET['universalservice'] ?? $_POST['universalservice'] ?? 0);
    $crrId = intval($_GET['crr'] ?? $_POST['crr'] ?? 0);
    $stage = strtolower(trim($_GET['stage'] ?? $_POST['stage'] ?? 'ca'));
    $customEmail = trim($_GET['email'] ?? $_POST['email'] ?? '');

    // Normalize stage aliases
    if ($stage === 'sa1' || $stage === 'sa-1' || $stage === 'sa_1') $stage = 'sai';
    if ($stage === 'sa2' || $stage === 'sa-2' || $stage === 'sa_2') $stage = 'saii';

    $stageLabels = array(
        'ca' => 'CA',
        'sai' => 'SA-I',
        'saii' => 'SA-II',
        'rc' => 'RC'
    );
    $stageLabel = $stageLabels[$stage] ?? strtoupper($stage);

    // -------------------------------------------------------------
    // ENDPOINT 1: get_details (Fetch recipient, standard, file info for modal)
    // -------------------------------------------------------------
    if ($reqAction === 'get_details') {
        if ($customerId <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Customer ID is required.'));
            exit;
        }

        $cStmt = $db->prepare("SELECT id, customer_name, contact_person, email_id FROM customer_info WHERE id = ? LIMIT 1");
        $cStmt->execute(array($customerId));
        $customer = $cStmt->fetch(PDO::FETCH_ASSOC);
        if (!$customer) {
            echo json_encode(array('success' => false, 'message' => 'Customer record not found.'));
            exit;
        }

        // Fetch certificate_print and crr_id from service table if needed
        $certPath = '';
        $foundCrrId = $crrId;
        $mainServiceId = $universalServiceId;

        if ($stage === 'ca') {
            $stmt = $db->prepare("SELECT ca_id, service, crr_id, certificate_print FROM service_type_ca WHERE ca_id = ? OR (service = ? AND service > 0) LIMIT 1");
            $stmt->execute(array($serviceId, $universalServiceId));
            $sRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sRow) {
                $certPath = $sRow['certificate_print'] ?? '';
                if (empty($foundCrrId)) $foundCrrId = intval($sRow['crr_id'] ?? 0);
                if (empty($mainServiceId)) $mainServiceId = intval($sRow['service'] ?? 0);
            }
        } else if ($stage === 'sai') {
            $stmt = $db->prepare("SELECT sai_id, service, crr_id, certificate_print FROM service_type_sai WHERE sai_id = ? OR (service = ? AND service > 0) LIMIT 1");
            $stmt->execute(array($serviceId, $universalServiceId));
            $sRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sRow) {
                $certPath = $sRow['certificate_print'] ?? '';
                if (empty($foundCrrId)) $foundCrrId = intval($sRow['crr_id'] ?? 0);
                if (empty($mainServiceId)) $mainServiceId = intval($sRow['service'] ?? 0);
            }
        } else if ($stage === 'saii') {
            $stmt = $db->prepare("SELECT saii_id, service, crr_id, certificate_print FROM service_type_saii WHERE saii_id = ? OR (service = ? AND service > 0) LIMIT 1");
            $stmt->execute(array($serviceId, $universalServiceId));
            $sRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sRow) {
                $certPath = $sRow['certificate_print'] ?? '';
                if (empty($foundCrrId)) $foundCrrId = intval($sRow['crr_id'] ?? 0);
                if (empty($mainServiceId)) $mainServiceId = intval($sRow['service'] ?? 0);
            }
        } else if ($stage === 'rc') {
            $stmt = $db->prepare("SELECT rc_id, service, crr_id, certificate_print FROM service_type_rc WHERE rc_id = ? OR (service = ? AND service > 0) LIMIT 1");
            $stmt->execute(array($serviceId, $universalServiceId));
            $sRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sRow) {
                $certPath = $sRow['certificate_print'] ?? '';
                if (empty($foundCrrId)) $foundCrrId = intval($sRow['crr_id'] ?? 0);
                if (empty($mainServiceId)) $mainServiceId = intval($sRow['service'] ?? 0);
            }
        }

        $standardName = aitfResolveServiceStandard($db, $foundCrrId, $serviceId, $mainServiceId, $customerId);

        // Check if certificate exists on disk
        $resolvedDiskPath = aitfResolveCertificateDiskPath($certPath);
        $hasCertificate = (!empty($resolvedDiskPath) && file_exists($resolvedDiskPath));

        // Check if already sent
        $alreadySent = false;
        $sentDateStr = '';
        $sentToStr = '';
        try {
            $logStmt = $db->prepare("SELECT sent_date, recipient_email FROM certificate_sent_log WHERE (service_id = ? OR (customer_id = ? AND stage = ?)) AND status = 'sent' AND (is_deleted = 'N' OR is_deleted IS NULL) ORDER BY id DESC LIMIT 1");
            $logStmt->execute(array($mainServiceId, $customerId, $stage));
            $logRow = $logStmt->fetch(PDO::FETCH_ASSOC);
            if ($logRow) {
                $alreadySent = true;
                $sentDateStr = !empty($logRow['sent_date']) ? date('d.m.Y h:i A', strtotime($logRow['sent_date'])) : '';
                $sentToStr = $logRow['recipient_email'] ?? '';
            }
        } catch (Throwable $e) {}

        echo json_encode(array(
            'success' => true,
            'customer_name' => trim($customer['customer_name'] ?? ''),
            'contact_person' => trim($customer['contact_person'] ?? ''),
            'to_email' => trim($customer['email_id'] ?? ''),
            'standard' => $standardName,
            'stage' => $stage,
            'stage_name' => $stageLabel,
            'has_certificate' => $hasCertificate,
            'certificate_filename' => $hasCertificate ? basename($resolvedDiskPath) : '',
            'already_sent' => $alreadySent,
            'sent_date' => $sentDateStr,
            'sent_to' => $sentToStr,
            'cc_office' => 'aitfmcl@gmail.com',
            'confirmation_email' => 'certification@aitf.co.in'
        ));
        exit;
    }

    // -------------------------------------------------------------
    // ENDPOINT 2: Send Certificate Email
    // -------------------------------------------------------------
    if ($customerId <= 0) {
        throw new Exception('Invalid Customer ID.');
    }

    $cStmt = $db->prepare("SELECT id, customer_name, contact_person, email_id, unique_id FROM customer_info WHERE id = ? LIMIT 1");
    $cStmt->execute(array($customerId));
    $customer = $cStmt->fetch(PDO::FETCH_ASSOC);
    if (!$customer) {
        throw new Exception('Customer record not found.');
    }

    $recipientEmail = !empty($customEmail) ? $customEmail : trim($customer['email_id'] ?? '');
    if (empty($recipientEmail)) {
        throw new Exception('Customer email address is not specified. Please update customer email.');
    }

    // Validate email addresses
    $emailList = preg_split('/[,;]+/', $recipientEmail);
    $validEmails = array();
    foreach ($emailList as $eml) {
        $eml = trim($eml);
        if ($eml !== '' && filter_var($eml, FILTER_VALIDATE_EMAIL)) {
            $validEmails[] = $eml;
        }
    }
    if (empty($validEmails)) {
        throw new Exception('Please enter a valid recipient email address.');
    }
    $finalRecipientEmail = implode(', ', $validEmails);

    // Fetch certificate_print and resolve file path
    $certPath = trim($_POST['certpath'] ?? '');
    $foundCrrId = $crrId;
    $mainServiceId = $universalServiceId;

    if (empty($certPath) || empty($foundCrrId) || empty($mainServiceId)) {
        if ($stage === 'ca') {
            $stmt = $db->prepare("SELECT ca_id, service, crr_id, certificate_print FROM service_type_ca WHERE ca_id = ? OR (service = ? AND service > 0) LIMIT 1");
            $stmt->execute(array($serviceId, $universalServiceId));
            $sRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sRow) {
                if (empty($certPath)) $certPath = $sRow['certificate_print'] ?? '';
                if (empty($foundCrrId)) $foundCrrId = intval($sRow['crr_id'] ?? 0);
                if (empty($mainServiceId)) $mainServiceId = intval($sRow['service'] ?? 0);
            }
        } else if ($stage === 'sai') {
            $stmt = $db->prepare("SELECT sai_id, service, crr_id, certificate_print FROM service_type_sai WHERE sai_id = ? OR (service = ? AND service > 0) LIMIT 1");
            $stmt->execute(array($serviceId, $universalServiceId));
            $sRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sRow) {
                if (empty($certPath)) $certPath = $sRow['certificate_print'] ?? '';
                if (empty($foundCrrId)) $foundCrrId = intval($sRow['crr_id'] ?? 0);
                if (empty($mainServiceId)) $mainServiceId = intval($sRow['service'] ?? 0);
            }
        } else if ($stage === 'saii') {
            $stmt = $db->prepare("SELECT saii_id, service, crr_id, certificate_print FROM service_type_saii WHERE saii_id = ? OR (service = ? AND service > 0) LIMIT 1");
            $stmt->execute(array($serviceId, $universalServiceId));
            $sRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sRow) {
                if (empty($certPath)) $certPath = $sRow['certificate_print'] ?? '';
                if (empty($foundCrrId)) $foundCrrId = intval($sRow['crr_id'] ?? 0);
                if (empty($mainServiceId)) $mainServiceId = intval($sRow['service'] ?? 0);
            }
        } else if ($stage === 'rc') {
            $stmt = $db->prepare("SELECT rc_id, service, crr_id, certificate_print FROM service_type_rc WHERE rc_id = ? OR (service = ? AND service > 0) LIMIT 1");
            $stmt->execute(array($serviceId, $universalServiceId));
            $sRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sRow) {
                if (empty($certPath)) $certPath = $sRow['certificate_print'] ?? '';
                if (empty($foundCrrId)) $foundCrrId = intval($sRow['crr_id'] ?? 0);
                if (empty($mainServiceId)) $mainServiceId = intval($sRow['service'] ?? 0);
            }
        }
    }

    if (empty($certPath)) {
        throw new Exception('Certificate has not been uploaded yet. Please upload the certificate first.');
    }

    $diskCertPath = aitfResolveCertificateDiskPath($certPath);
    if (!$diskCertPath || !file_exists($diskCertPath)) {
        throw new Exception('Certificate file could not be found on the server at: ' . htmlspecialchars($certPath) . '. Please re-upload the certificate.');
    }

    // Resolve standard
    $standardDisplay = aitfResolveServiceStandard($db, $foundCrrId, $serviceId, $mainServiceId, $customerId);

    // Prepare attachments:
    // 1) Certificate file
    // 2) Logo usage guideline PDF
    // 3) Logos ZIP (if present)
    $attachments = array($diskCertPath);

    $guidelinePdf = __DIR__ . '/guidelines/Guideline_Client_to_Use_of_Logo.pdf';
    if (file_exists($guidelinePdf)) {
        $attachments[] = $guidelinePdf;
    }

    $logosZip = __DIR__ . '/guidelines/AITF Logo For Client.zip';
    if (file_exists($logosZip)) {
        $attachments[] = $logosZip;
    }

    // Prepare email body
    $orgName = trim($customer['customer_name'] ?? '');
    $contactPerson = trim($customer['contact_person'] ?? '');
    $clientDisplayName = !empty($contactPerson) ? $contactPerson : $orgName;
    $certFileName = basename($diskCertPath);

    $subject = 'Congratulations: ' . $standardDisplay . ' Certification - ' . $orgName;

    $body = '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<meta charset="UTF-8">'
        . '<title>Certificate Issued - AITF</title>'
        . '</head>'
        . '<body style="margin: 0; padding: 20px; background-color: #f1f5f9; font-family: \'Segoe UI\', Arial, sans-serif; color: #1e293b; line-height: 1.65;">'
        . '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 650px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0;">'
        . '<tr>'
        . '<td style="background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%); padding: 24px 30px; text-align: center; color: #ffffff;">'
        . '<h2 style="margin: 0; font-size: 21px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">AITF Management Certification Limited</h2>'
        . '<div style="font-size: 13px; color: #bae6fd; margin-top: 4px; font-weight: 500;">QCI-NABCB Accredited Certification Body</div>'
        . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td style="padding: 28px 32px;">'
        . '<p style="font-size: 15px; margin: 0 0 16px 0; color: #334155;">Dear <strong>' . htmlspecialchars($clientDisplayName) . '</strong>,</p>'
        . '<p style="font-size: 15px; margin: 0 0 16px 0; color: #334155;">Greetings from <strong>AITF Management Certification Ltd..</strong></p>'
        . '<div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 14px 18px; border-radius: 4px; margin: 18px 0;">'
        . '<p style="margin: 0; font-size: 15px; color: #166534; line-height: 1.6;">'
        . 'Congratulations on the achievement of <strong>' . htmlspecialchars($standardDisplay) . '</strong> Certification for your organization <strong>' . htmlspecialchars($orgName) . '</strong>.'
        . '</p>'
        . '</div>'
        . '<p style="font-size: 15px; margin: 0 0 14px 0; color: #334155;">'
        . 'Please find the attachment of the <strong>' . htmlspecialchars($standardDisplay) . '</strong> certificate.'
        . '</p>'
        . '<p style="font-size: 15px; margin: 0 0 14px 0; color: #334155;">'
        . 'We have also attached herewith <strong>Logos &amp; Guidelines</strong> to use for your organization.'
        . '</p>'
        . '<p style="font-size: 15px; margin: 0 0 14px 0; color: #334155;">'
        . 'If any query or information is required feel free to contact us.'
        . '</p>'
        . '<div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 4px; margin: 18px 0;">'
        . '<p style="margin: 0; font-size: 13.5px; color: #92400e; line-height: 1.5;">'
        . '<strong>Kindly note:</strong> The organization is required to comply with a yearly surveillance audit for the continuity of maintaining the certification.'
        . '</p>'
        . '</div>'
        . '<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 18px; margin: 20px 0;">'
        . '<div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 6px;"><i class="fa fa-paperclip"></i> Enclosed Attachments:</div>'
        . '<ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #475569; line-height: 1.8;">'
        . '<li><strong>' . htmlspecialchars($certFileName) . '</strong> (Official Certificate Copy)</li>'
        . (file_exists($guidelinePdf) ? '<li><strong>Guideline_Client_to_Use_of_Logo.pdf</strong> (AITF Logo Usage Guidelines)</li>' : '')
        . (file_exists($logosZip) ? '<li><strong>AITF Logo For Client.zip</strong> (Accredited Logo Pack)</li>' : '')
        . '</ul>'
        . '</div>'
        . '<div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 14px; color: #334155; line-height: 1.6;">'
        . '<p style="margin: 0 0 6px 0;">Thanks &amp; Regards,</p>'
        . '<p style="margin: 0; font-weight: 700; color: #0f172a;">STO -AITF <span style="font-weight: normal; color: #64748b;">(Technical Operational Staff -AITF)</span></p>'
        . '<p style="margin: 0; font-weight: 600; color: #1e3a8a;">AITF Management Certification Limited</p>'
        . '<p style="margin: 0; color: #059669; font-weight: 600; font-size: 13px;">QCI-NABCB Accredited Certification Body</p>'
        . '<p style="margin: 4px 0 0 0; color: #475569; font-size: 13px;"><strong>Contact No.:</strong> 9422011836 / 9422011837</p>'
        . '<p style="margin: 2px 0 0 0; font-size: 13px;"><strong>Website:</strong> <a href="http://aitf.co.in" style="color: #0284c7; text-decoration: none;">http://aitf.co.in</a></p>'
        . '</div>'
        . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td style="background-color: #f8fafc; padding: 12px 30px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;">'
        . 'This is an automated communication from AITF Management Certification Limited.'
        . '</td>'
        . '</tr>'
        . '</table>'
        . '</body>'
        . '</html>';

    // 1. Send email to client with CC: aitfmcl@gmail.com
    $mailSender = new AITFServiceEmailSender();
    $officeCc = 'aitfmcl@gmail.com';
    $ccEmails = array($officeCc);

    $mailSender->sendMultipleAttachmentsEmail(
        $validEmails,
        $orgName,
        $body,
        $subject,
        $attachments,
        $ccEmails
    );

    $nowDate = date('Y-m-d H:i:s');
    $sentBy = $_SESSION['username'] ?? 'Technical Operational Staff -AITF';

    // 2. Send automated confirmation email to certification@aitf.co.in with attachments
    try {
        $confirmSubject = 'Confirmation: ' . $stageLabel . ' Certificate Sent - ' . $orgName . ' (' . $standardDisplay . ')';
        $confirmBody = '<html><body style="font-family: Arial, sans-serif; font-size: 14px; color: #333333; line-height: 1.6; padding: 15px;">'
            . 'Dear AITF Certification Team,<br><br>'
            . 'This is an automated confirmation that the <strong>' . htmlspecialchars($stageLabel) . ' Certificate</strong> has been successfully emailed to the client.<br><br>'
            . '<div style="background:#f8fafc; border:1px solid #cbd5e1; border-left:4px solid #10b981; padding:12px 16px; border-radius:4px; margin-bottom:15px;">'
            . '<p style="margin:4px 0;"><strong>Organization Name:</strong> <span style="color:#0f172a; font-weight:600;">' . htmlspecialchars($orgName) . '</span></p>'
            . '<p style="margin:4px 0;"><strong>Client Contact:</strong> ' . htmlspecialchars($clientDisplayName) . '</p>'
            . '<p style="margin:4px 0;"><strong>Recipient Email (Client):</strong> <span style="color:#2563eb; font-weight:600;">' . htmlspecialchars($finalRecipientEmail) . '</span></p>'
            . '<p style="margin:4px 0;"><strong>CC (Office):</strong> ' . htmlspecialchars($officeCc) . '</p>'
            . '<p style="margin:4px 0;"><strong>Standard:</strong> <span style="color:#d97706; font-weight:600;">' . htmlspecialchars($standardDisplay) . '</span></p>'
            . '<p style="margin:4px 0;"><strong>Service Stage:</strong> ' . htmlspecialchars($stageLabel) . '</p>'
            . (!empty($customer['unique_id']) ? '<p style="margin:4px 0;"><strong>Unique / Agreement ID:</strong> ' . htmlspecialchars($customer['unique_id']) . '</p>' : '')
            . '<p style="margin:4px 0;"><strong>Certificate File:</strong> ' . htmlspecialchars($certFileName) . '</p>'
            . '<p style="margin:4px 0;"><strong>Dispatched Date & Time:</strong> ' . date('d.m.Y h:i A', strtotime($nowDate)) . '</p>'
            . '<p style="margin:4px 0;"><strong>Dispatched By:</strong> ' . htmlspecialchars($sentBy) . '</p>'
            . '</div>'
            . 'Copies of the sent Certificate, Logo Guidelines, and Logos are attached to this email for your records.<br><br>'
            . 'Sincerely,<br><strong>AITF System</strong>'
            . '</body></html>';

        $mailSender->sendMultipleAttachmentsEmail(
            'certification@aitf.co.in',
            'AITF Certification Team',
            $confirmBody,
            $confirmSubject,
            $attachments
        );
    } catch (Exception $confirmEx) {
        error_log("sendCertificateUploadMail: Failed to send confirmation to certification@aitf.co.in: " . $confirmEx->getMessage());
    }

    // 3. Record entry in certificate_sent_log
    try {
        $checkStmt = $db->prepare("SELECT id FROM certificate_sent_log WHERE (service_id = ? OR (customer_id = ? AND stage = ?)) AND is_deleted = 'N' LIMIT 1");
        $checkStmt->execute(array($mainServiceId, $customerId, $stage));
        $existingLog = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingLog) {
            $updateStmt = $db->prepare("UPDATE certificate_sent_log SET sent_date = ?, status = 'sent', recipient_email = ?, cc_emails = ?, sent_by = ?, customer_id = ?, service_id = ?, service_type_id = ?, certificate_path = ?, standard = ? WHERE id = ?");
            $updateStmt->execute(array($nowDate, $finalRecipientEmail, $officeCc, $sentBy, $customerId, $mainServiceId, $serviceId, $certPath, $standardDisplay, $existingLog['id']));
        } else {
            $insertStmt = $db->prepare("INSERT INTO certificate_sent_log (service_id, customer_id, service_type_id, stage, certificate_path, standard, sent_date, recipient_email, cc_emails, sent_by, status, is_deleted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'sent', 'N')");
            $insertStmt->execute(array($mainServiceId, $customerId, $serviceId, $stage, $certPath, $standardDisplay, $nowDate, $finalRecipientEmail, $officeCc, $sentBy));
        }
    } catch (Exception $dbEx) {
        error_log("sendCertificateUploadMail: Failed to record certificate_sent_log: " . $dbEx->getMessage());
    }

    $message = $stageLabel . ' Certificate successfully sent to ' . $finalRecipientEmail . ' (CC: ' . $officeCc . ').';
    echo json_encode(array(
        'success' => true,
        'message' => $message,
        'recipient' => $finalRecipientEmail,
        'sent_at' => date('d.m.Y h:i A', strtotime($nowDate))
    ));

} catch (Exception $ex) {
    echo json_encode(array('success' => false, 'message' => $ex->getMessage()));
}

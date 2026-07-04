<?php

// Endpoint to generate certificate PDF and email it to client + office
// Expected POST: customer (customer_id), service (service type id), crr (cdc_report_review id), svctype (CA|SA1|SA2|RC)

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }

    require_once './dbConnect.php';
    require_once __DIR__ . '/PHPMailer/PHPMailerAutoload.php';
    require_once __DIR__ . '/include/env_helper.php';

    $customerId = isset($_POST['customer']) ? (int) $_POST['customer'] : 0;
    $serviceId  = isset($_POST['service']) ? (int) $_POST['service'] : 0;
    $crrId      = isset($_POST['crr']) ? (int) $_POST['crr'] : 0;
    $svcType    = isset($_POST['svctype']) ? strtoupper(trim($_POST['svctype'])) : '';

    if (!$customerId || !$serviceId || !$crrId || $svcType === '') {
        echo json_encode(['success' => false, 'message' => 'Missing required data.']);
        exit;
    }

    // Fetch customer info
    $stmt = $db->prepare('SELECT customer_name, email_id, address FROM customer_info WHERE id = :id');
    $stmt->bindParam(':id', $customerId, PDO::PARAM_INT);
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'Customer not found.']);
        exit;
    }

    // Try to get certificate_print id if exists for this service
    $certifyId = null;
    $certStmt = $db->prepare('SELECT certificate_id FROM certificate_print WHERE service_id = :sid ORDER BY certificate_id DESC LIMIT 1');
    $certStmt->bindParam(':sid', $serviceId, PDO::PARAM_INT);
    $certStmt->execute();
    $certRow = $certStmt->fetch(PDO::FETCH_ASSOC);
    if ($certRow && isset($certRow['certificate_id'])) {
        $certifyId = (int) $certRow['certificate_id'];
    }

    // Generate certificate PDFs (one per ISO entry) for mail attachments
    $attachments = [];

    // Fetch ISO entries for this CDC (if any)
    $isoEntries = [];
    $isoStmt = $db->prepare('SELECT iso_index, iso_label, certificate_no FROM cdc_report_review_office_use WHERE crr_id = :crr ORDER BY iso_index ASC');
    $isoStmt->bindParam(':crr', $crrId, PDO::PARAM_INT);
    $isoStmt->execute();
    $isoEntries = $isoStmt->fetchAll(PDO::FETCH_ASSOC);

    // If no ISO entries found, fall back to single generation
    if (!$isoEntries || !is_array($isoEntries) || count($isoEntries) === 0) {
        $isoEntries = [["iso_index" => null, "iso_label" => null, "certificate_no" => null]];
    }

    foreach ($isoEntries as $isoEntry) {
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cdc_cert_' . uniqid() . '.pdf';
        $pdfContent = '';

        // Prepare POST variables expected by CertificatePrintPdf.php
        $originalPost = $_POST;
        $_POST['crr_id']   = $crrId;
        $_POST['customer'] = $customerId;
        $_POST['service']  = $serviceId;
        $_POST['use_bg']   = 1; // apply background only for mailed certificates
        if ($certifyId) {
            $_POST['certify_id'] = $certifyId;
        }
        if (isset($isoEntry['iso_index']) && $isoEntry['iso_index'] !== null && $isoEntry['iso_index'] !== '') {
            $_POST['iso_index'] = $isoEntry['iso_index'];
        }
        if (isset($isoEntry['iso_label']) && $isoEntry['iso_label'] !== '') {
            $_POST['iso_label'] = $isoEntry['iso_label'];
        }

        try {
            ob_start();
            include './CertificatePrintPdf.php';
            $pdfContent = ob_get_clean();
        } catch (Throwable $genEx) {
            @ob_end_clean();
            error_log('CDC MAIL: PDF generation failed for ISO ' . ($isoEntry['iso_label'] ?? '') . ' - ' . $genEx->getMessage());
            $_POST = $originalPost;
            continue;
        }

        // Restore POST
        $_POST = $originalPost;

        if (empty($pdfContent)) {
            error_log('CDC MAIL: Empty PDF content for ISO ' . ($isoEntry['iso_label'] ?? '')); 
            continue; // skip this attachment if generation failed
        }

        if (file_put_contents($tmpFile, $pdfContent) === false) {
            error_log('CDC MAIL: Failed to write PDF for ISO ' . ($isoEntry['iso_label'] ?? '') . ' at ' . $tmpFile);
            continue; // skip if cannot write
        }

        // Build a friendly filename
        $labelPart = '';
        if (!empty($isoEntry['iso_label'])) {
            $labelPart = preg_replace('/[^A-Za-z0-9._-]+/', '_', $isoEntry['iso_label']);
        }
        $certNoPart = '';
        if (!empty($isoEntry['certificate_no'])) {
            $certNoPart = preg_replace('/[^A-Za-z0-9._-]+/', '_', $isoEntry['certificate_no']);
        }
        $fileName = 'Certificate';
        if ($labelPart !== '') {
            $fileName .= '_' . $labelPart;
        }
        if ($certNoPart !== '') {
            $fileName .= '_' . $certNoPart;
        }
        $fileName .= '.pdf';

        $attachments[] = ['path' => $tmpFile, 'name' => $fileName];
    }

    if (empty($attachments)) {
        echo json_encode(['success' => false, 'message' => 'Failed to generate certificate PDF(s).']);
        exit;
    }

    // Email recipients
    $clientEmails = array_filter(array_map('trim', explode(',', (string) $customer['email_id'])));
    if (empty($clientEmails)) {
        foreach ($attachments as $att) {
        if (isset($att['path']) && file_exists($att['path'])) {
            @unlink($att['path']);
        }
    }
        echo json_encode(['success' => false, 'message' => 'No client email found to send certificate.']);
        exit;
    }

    $adminEmail = aitf_env('MAIL_ADMIN_EMAIL', aitf_env('MAIL_FROM_EMAIL', aitf_env('SMTP_USERNAME', ''))); // admin/office mailbox

    $mailConfig = aitf_get_mail_config([
        'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Certification Team')
    ]);
    $mailConfig['smtp_auth'] = ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== '';

    $configureSmtp = function (PHPMailer $mailer, array $cfg) {
        $mailer->isSMTP();
        $mailer->SMTPAutoTLS = true;
        $mailer->Host = $cfg['host'];
        $mailer->Hostname = $cfg['host'];
        $mailer->Port = $cfg['port'];
        $mailer->SMTPAuth = $cfg['smtp_auth'];
        $mailer->SMTPSecure = $cfg['secure'];
        if ($cfg['smtp_auth']) {
            $mailer->Username = $cfg['username'];
            $mailer->Password = $cfg['password'];
        }
        $mailer->CharSet = 'UTF-8';
        $mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        $mailer->setFrom($cfg['from_email'] ?: 'no-reply@localhost', $cfg['from_name']);
        $mailer->isHTML(true);
        $mailer->SMTPDebug = 0; // keep silent for UI; enable if needed
        $mailer->Timeout = 15;
    };

    // Prepare standard list for email body
    $standardList = array_filter(array_map(function($r){ return isset($r['iso_label']) ? trim($r['iso_label']) : ''; }, $isoEntries));
    $standardDisplay = $standardList ? implode(', ', $standardList) : 'ISO certificate';

    // Send to client
    $clientMailer = new PHPMailer(true);
    try {
        $configureSmtp($clientMailer, $mailConfig);
        foreach ($clientEmails as $eml) {
            $clientMailer->addAddress($eml, $customer['customer_name']);
        }
        $clientMailer->Subject = 'Certificate Issued - ' . $customer['customer_name'];
        $clientMailer->Body    = '<p>Dear ' . htmlspecialchars($customer['customer_name']) . ',</p>'
            . '<p>Greetings from AITF Management Certification Limited.</p>'
            . '<p>We are pleased to inform you that your ISO ' . htmlspecialchars($standardDisplay) . ' certificate has been successfully issued.</p>'
            . '<p>Please find the certificate attached for your reference. The certificate is valid as per the defined scope and validity dates mentioned therein.</p>'
            . '<p>Thank you for your cooperation.</p>'
            . '<p>Warm regards,<br><strong>AITF Certification Team</strong><br>AITF Management Certification Limited<br>Devi Indrayani, Wing B-26,<br>Near IT Park, Talawade,<br>Pune – 411062, Maharashtra, India</p>'
            . '<p>Website: <a href="http://www.aitf.co.in">www.aitf.co.in</a><br>Phone: +91 94220 11836 / +91 94220 11837</p>';
        foreach ($attachments as $att) {
            $clientMailer->addAttachment($att['path'], $att['name']);
        }
        if (!$clientMailer->send()) {
            $primaryErr = $clientMailer->ErrorInfo;
            $clientMailer->smtpClose();
            $clientMailer->SMTPSecure = 'ssl';
            $clientMailer->Port = 465;
            $clientMailer->SMTPAutoTLS = false;
            $clientMailer->send();
            if ($clientMailer->ErrorInfo) {
                throw new Exception('Client mail failed: ' . $primaryErr . ' | Fallback: ' . $clientMailer->ErrorInfo);
            }
        }
    } catch (Exception $e) {
        @unlink($tmpFile);
        echo json_encode(['success' => false, 'message' => 'Client mail failed: ' . $e->getMessage()]);
        exit;
    }

    // Send to admin
    $adminMailer = new PHPMailer(true);
    try {
        $configureSmtp($adminMailer, $mailConfig);
        $adminMailer->addAddress($adminEmail, 'Admin');
        $adminMailer->Subject = 'Client certificate sent - ' . $customer['customer_name'];
        $adminMailer->Body    = '<p>Certificate mail was sent to client.</p>'
            . '<p>Client: <strong>' . htmlspecialchars($customer['customer_name']) . '</strong></p>'
            . '<p>Client emails: ' . htmlspecialchars(implode(', ', $clientEmails)) . '</p>'
            . '<p>Standard(s): ' . htmlspecialchars($standardDisplay) . '</p>'
            . '<p>The certificate PDF(s) are attached.</p>';
        foreach ($attachments as $att) {
            $adminMailer->addAttachment($att['path'], $att['name']);
        }
        if (!$adminMailer->send()) {
            $primaryErr = $adminMailer->ErrorInfo;
            $adminMailer->smtpClose();
            $adminMailer->SMTPSecure = 'ssl';
            $adminMailer->Port = 465;
            $adminMailer->SMTPAutoTLS = false;
            $adminMailer->send();
            if ($adminMailer->ErrorInfo) {
                throw new Exception('Admin mail failed: ' . $primaryErr . ' | Fallback: ' . $adminMailer->ErrorInfo);
            }
        }
    } catch (Exception $e) {
        @unlink($tmpFile);
        echo json_encode(['success' => false, 'message' => 'Admin mail failed: ' . $e->getMessage()]);
        exit;
    }

    @unlink($tmpFile);
    echo json_encode(['success' => true, 'message' => 'Certificate email sent to client and admin.']);
    exit;

} catch (Exception $ex) {
    echo json_encode(['success' => false, 'message' => 'Unexpected error: ' . $ex->getMessage()]);
    exit;
}

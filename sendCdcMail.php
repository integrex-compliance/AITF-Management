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

    // Generate certificate PDF using existing generator; capture output as file
    $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cdc_cert_' . uniqid() . '.pdf';
    $pdfContent = '';
    
    // Prepare POST variables expected by CertificatePrintPdf.php
    $originalPost = $_POST;
    $_POST['crr_id']   = $crrId;
    $_POST['customer'] = $customerId;
    $_POST['service']  = $serviceId;
    if ($certifyId) {
        $_POST['certify_id'] = $certifyId;
    }

    ob_start();
    include './CertificatePrintPdf.php';
    $pdfContent = ob_get_clean();

    // Restore POST
    $_POST = $originalPost;

    if (empty($pdfContent)) {
        echo json_encode(['success' => false, 'message' => 'Failed to generate certificate PDF.']);
        exit;
    }

    if (file_put_contents($tmpFile, $pdfContent) === false) {
        echo json_encode(['success' => false, 'message' => 'Unable to write certificate PDF.']);
        exit;
    }

    // Email recipients
    $clientEmails = array_filter(array_map('trim', explode(',', (string) $customer['email_id'])));
    if (empty($clientEmails)) {
        @unlink($tmpFile);
        echo json_encode(['success' => false, 'message' => 'No client email found to send certificate.']);
        exit;
    }

    $adminEmail = 'gauravoli777@gmail.com'; // admin notification mailbox (can be changed later)

    // Build SMTP host list with resolution fallback (mirrors contactUS)
    $smtpPrimaryHost = 'smtp.gmail.com';
    $smtpResolved = @gethostbyname($smtpPrimaryHost);
    $smtpHosts = [];
    $dnsFailed = ($smtpResolved === $smtpPrimaryHost);
    if (!$dnsFailed && $smtpResolved) {
        $smtpHosts[] = $smtpResolved; // prefer resolved IPv4
        $smtpHosts[] = $smtpPrimaryHost;
        $smtpHosts[] = 'smtp.googlemail.com';
    } else {
        // DNS failed: static Gmail IPv4 list
        $smtpHosts = [
            '64.233.184.108',
            '64.233.184.109',
            '64.233.184.26'
        ];
    }
    $smtpHostList = implode(';', array_unique($smtpHosts));

    $smtpUsername = 'gauravoli777@gmail.com';
    $smtpPassword = 'mezfslphhrielhpq';
    $fromName     = 'AITF Website ';

    $configureSmtp = function (PHPMailer $mailer, $hostList, $primaryHost, $port, $secure, $username, $password, $fromName) {
        $mailer->isSMTP();
        $mailer->SMTPAuth = true;
        $mailer->SMTPSecure = $secure;
        $mailer->Host = $hostList;
        $mailer->Hostname = $primaryHost;
        $mailer->Port = $port;
        $mailer->Username = $username;
        $mailer->Password = $password;
        $mailer->CharSet = 'UTF-8';
        $mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        $mailer->setFrom($username, $fromName);
        $mailer->isHTML(true);
        $mailer->SMTPDebug = 0; // keep silent for UI; logs can be enabled if needed
    };

    $sendWithFallback = function (PHPMailer $mailer, callable $builder) use ($configureSmtp, $smtpHostList, $smtpPrimaryHost, $smtpUsername, $smtpPassword, $fromName) {
        // Primary: TLS 587
        $configureSmtp($mailer, $smtpHostList, $smtpPrimaryHost, 587, 'tls', $smtpUsername, $smtpPassword, $fromName);
        $builder();
        if ($mailer->send()) {
            return true;
        }
        $primaryErr = $mailer->ErrorInfo;
        // Fallback: SSL 465
        $mailer->smtpClose();
        $configureSmtp($mailer, $smtpHostList, $smtpPrimaryHost, 465, 'ssl', $smtpUsername, $smtpPassword, $fromName);
        $builder(true); // allow builder to skip re-adding recipients/attachments when false
        if ($mailer->send()) {
            return true;
        }
        throw new Exception($primaryErr . ' | Fallback: ' . $mailer->ErrorInfo);
    };

    // Send to client
    $clientMailer = new PHPMailer(true);
    try {
        $builder = function ($isFallback = false) use ($clientMailer, $clientEmails, $customer, $tmpFile) {
            if (!$isFallback) {
                // Only add recipients/attachments once
                foreach ($clientEmails as $eml) {
                    $clientMailer->addAddress($eml, $customer['customer_name']);
                }
                $clientMailer->Subject = 'Certificate Received - ' . $customer['customer_name'];
                $clientMailer->Body    = '<p>Congratulations, your certificate has been received.</p>'
                    . '<p>Client: <strong>' . htmlspecialchars($customer['customer_name']) . '</strong></p>'
                    . '<p>The certificate PDF is attached for your reference.</p>';
                $clientMailer->addAttachment($tmpFile, 'Certificate.pdf');
            }
        };
        $sendWithFallback($clientMailer, $builder);
    } catch (Exception $e) {
        @unlink($tmpFile);
        echo json_encode(['success' => false, 'message' => 'Client mail failed: ' . $e->getMessage()]);
        exit;
    }

    // Send to admin
    $adminMailer = new PHPMailer(true);
    try {
        $builder = function ($isFallback = false) use ($adminMailer, $adminEmail, $customer, $clientEmails, $tmpFile) {
            if (!$isFallback) {
                $adminMailer->addAddress($adminEmail, 'Admin');
                $adminMailer->Subject = 'Client certificate sent - ' . $customer['customer_name'];
                $adminMailer->Body    = '<p>Certificate mail was sent to client.</p>'
                    . '<p>Client: <strong>' . htmlspecialchars($customer['customer_name']) . '</strong></p>'
                    . '<p>Client emails: ' . htmlspecialchars(implode(', ', $clientEmails)) . '</p>'
                    . '<p>The certificate PDF is attached.</p>';
                $adminMailer->addAttachment($tmpFile, 'Certificate.pdf');
            }
        };
        $sendWithFallback($adminMailer, $builder);
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

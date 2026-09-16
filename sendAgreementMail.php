<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Basic auth check
$currentRole = strtolower(trim((string) ($_SESSION['role'] ?? '')));
if ($currentRole === 'client' || $currentRole === 'external') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/env_helper.php';
require_once __DIR__ . '/include/invoice_mail_helper.php';

$customerId = isset($_POST['customer']) ? (int) $_POST['customer'] : 0;
$serviceId = isset($_POST['service']) ? (int) $_POST['service'] : 0;
$certificationId = isset($_POST['certification']) ? (int) $_POST['certification'] : 0;
$saiiCount = isset($_POST['saiicount']) ? trim((string) $_POST['saiicount']) : '';
$recipientEmail = isset($_POST['email']) ? trim((string) $_POST['email']) : '';

if (!$customerId) {
    echo json_encode(['success' => false, 'message' => 'Customer ID is required.']);
    exit;
}

// 1. Fetch customer information
$custStmt = $db->prepare("SELECT id, customer_name, email_id, address, unique_id FROM customer_info WHERE id = :id");
$custStmt->bindParam(':id', $customerId, PDO::PARAM_INT);
$custStmt->execute();
$customer = $custStmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    echo json_encode(['success' => false, 'message' => 'Customer not found.']);
    exit;
}

// 2. Fetch certification agreement information
$certInfo = false;
if ($certificationId) {
    $certStmt = $db->prepare("SELECT * FROM certification_aggrement WHERE id = :id");
    $certStmt->bindParam(':id', $certificationId, PDO::PARAM_INT);
    $certStmt->execute();
    $certInfo = $certStmt->fetch(PDO::FETCH_ASSOC);
}

if (!$certInfo && $serviceId) {
    $certStmt = $db->prepare("SELECT * FROM certification_aggrement WHERE service = :svc ORDER BY id DESC LIMIT 1");
    $certStmt->bindParam(':svc', $serviceId);
    $certStmt->execute();
    $certInfo = $certStmt->fetch(PDO::FETCH_ASSOC);
}

if (!$certInfo) {
    $certStmt = $db->prepare("SELECT * FROM certification_aggrement WHERE customer_id = :cid ORDER BY id DESC LIMIT 1");
    $certStmt->bindParam(':cid', $customerId);
    $certStmt->execute();
    $certInfo = $certStmt->fetch(PDO::FETCH_ASSOC);
}

if (!$certInfo) {
    echo json_encode(['success' => false, 'message' => 'Certification agreement record not found for this client. Please generate the agreement first.']);
    exit;
}

$certificationId = (int) $certInfo['id'];

// Check if already sent
if (!empty($certInfo['mail_sent'])) {
    $sentTime = !empty($certInfo['mail_sent_at']) ? date('d.m.Y h:i A', strtotime($certInfo['mail_sent_at'])) : 'previously';
    $sentTo = !empty($certInfo['mail_sent_to']) ? ' to ' . $certInfo['mail_sent_to'] : '';
    echo json_encode([
        'success' => false,
        'already_sent' => true,
        'sent_at' => !empty($certInfo['mail_sent_at']) ? date('d.m.Y h:i A', strtotime($certInfo['mail_sent_at'])) : '',
        'sent_to' => $certInfo['mail_sent_to'] ?? '',
        'message' => 'Certification Agreement has already been sent' . $sentTo . ' on ' . $sentTime . '. It can only be sent once.'
    ]);
    exit;
}
$clientName = trim($customer['customer_name'] ?? '');
$agreementNo = trim($certInfo['aggrement_no'] ?? ($customer['unique_id'] ?? ''));

// If agreement number is available on agreement but not on customer unique_id, keep them aligned
if (!empty($agreementNo) && empty($customer['unique_id'])) {
    try {
        $upd = $db->prepare("UPDATE customer_info SET unique_id = :uid WHERE id = :id");
        $upd->execute([':uid' => $agreementNo, ':id' => $customerId]);
    } catch (Exception $e) {}
}

if ($recipientEmail === '') {
    $recipientEmail = trim($customer['email_id'] ?? '');
}

if ($recipientEmail === '') {
    echo json_encode(['success' => false, 'message' => 'Recipient email address is missing. Please provide a valid email.']);
    exit;
}

$emailList = preg_split('/[,;]+/', $recipientEmail);
$validEmails = [];
foreach ($emailList as $eml) {
    $eml = trim($eml);
    if ($eml !== '' && filter_var($eml, FILTER_VALIDATE_EMAIL)) {
        $validEmails[] = $eml;
    }
}

if (empty($validEmails)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid recipient email address.']);
    exit;
}
$recipientEmail = implode(', ', $validEmails);

// 3. Generate the CSA PDF
try {
    $csaCustomerId = $customerId;
    $csaServiceId = $serviceId ?: (int) $certInfo['service'];
    $csaCertificationId = $certificationId;
    $csaSaiiCount = $saiiCount;
    $csaOutputMode = 'S';
    $csaOutputName = 'Certification_Agreement_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $agreementNo ?: 'CSA') . '.pdf';

    ob_start();
    $pdfBytes = require __DIR__ . '/Csa.php';
    $extraOutput = ob_get_clean();

    if (empty($pdfBytes) && !empty($extraOutput) && strpos($extraOutput, '%PDF') === 0) {
        $pdfBytes = $extraOutput;
    }

    if (empty($pdfBytes) || strpos($pdfBytes, '%PDF') !== 0) {
        error_log('CSA MAIL FAIL: PDF generation returned invalid content for customer ' . $customerId);
        echo json_encode(['success' => false, 'message' => 'Failed to generate Certification Agreement PDF. Please check agreement details.']);
        exit;
    }
} catch (Throwable $t) {
    @ob_end_clean();
    error_log('CSA MAIL PDF EXCEPTION: ' . $t->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error generating agreement PDF: ' . $t->getMessage()]);
    exit;
}

// 4. Build Email
try {
    aitfLoadPHPMailer();

    $escClientName = htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8');
    $escAgreementNo = htmlspecialchars($agreementNo, ENT_QUOTES, 'UTF-8');

    $htmlBody = '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: Arial, Helvetica, sans-serif; color: #1e293b;">
  <div style="max-width: 650px; margin: 25px auto; background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.06);">
    
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #0F2742 0%, #1e40af 100%); color: #ffffff; padding: 22px 28px;">
      <h2 style="margin: 0; font-size: 19px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">AITF Management Certification Limited</h2>
      <p style="margin: 4px 0 0; font-size: 12.5px; color: #cbd5e1;">QCI-NABCB Accredited Certification Body</p>
    </div>

    <!-- Body Content -->
    <div style="padding: 28px 30px; font-size: 14.5px; line-height: 1.65; color: #334155;">
      
      <p style="margin-top: 0;">Dear <strong>' . $escClientName . '</strong> Team,</p>

      <p>Greetings from <strong>A.I.T.F. (Assessment And Institute for Technical Forum)</strong>.</p>

      <p>Please find the attached <strong>Certification Service Agreement</strong> including <strong>fees structure</strong> for the ISO Certification of your organization.</p>

      <div style="background-color: #eff6ff; border-left: 4px solid #2563eb; border-radius: 4px; padding: 12px 18px; margin: 18px 0; color: #1e40af; font-size: 14px;">
        You are requested to <strong>review and reply to this email for your acceptance</strong>.
      </div>

      <p>AITF also made client portal provision, for easy access to clients for their documents and communication, you can access your supporting documents and other relevant information through the <strong>AITF Client login Portal</strong>:</p>

      <!-- Portal & Credentials Box -->
      <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 22px; margin: 20px 0;">
        <div style="font-size: 14px; margin-bottom: 12px;">
          <strong style="color: #0f172a;">AITF Client Portal:</strong> 
          <a href="https://www.aitf.co.in/login" target="_blank" style="color: #2563eb; font-weight: bold; text-decoration: underline; word-break: break-all;">https://www.aitf.co.in/login</a>
        </div>
        
        <div style="border-top: 1px solid #e2e8f0; padding-top: 12px;">
          <div style="font-weight: bold; color: #0f172a; margin-bottom: 8px; font-size: 13.5px;">Login Credentials:</div>
          <table cellpadding="0" cellspacing="0" style="width: 100%; font-size: 14px;">
            <tr>
              <td style="padding: 5px 0; width: 110px; color: #475569;"><strong>Username:</strong></td>
              <td style="padding: 5px 0; font-weight: bold; color: #0f172a;">' . $escClientName . '</td>
            </tr>
            <tr>
              <td style="padding: 5px 0; color: #475569;"><strong>Password:</strong></td>
              <td style="padding: 5px 0;">
                <span style="background-color: #e0e7ff; color: #3730a3; padding: 3px 10px; border-radius: 4px; font-family: monospace; font-size: 14.5px; font-weight: bold;">' . $escAgreementNo . '</span>
              </td>
            </tr>
          </table>
        </div>
      </div>

      <p>Please use the above credentials to log in to the portal and access your available documents and information.</p>

      <p>For any clarification regarding the agreement or the portal, please feel free to contact us.</p>

      <!-- Signoff -->
      <div style="margin-top: 28px; border-top: 1px solid #f1f5f9; padding-top: 18px;">
        <p style="margin: 0 0 6px; color: #475569;">Regards,</p>
        <p style="margin: 0; line-height: 1.45;">
          <strong>AITF Management Certification Limited.</strong><br>
          <strong>QCI-NABCB Accredited Certification Body.</strong><br><br>
          <strong>Authorised Signatory</strong>
        </p>
      </div>

    </div>

    <!-- Footer -->
    <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 30px; font-size: 12px; color: #64748b; line-height: 1.5;">
      <strong>AITF Management Certification Limited</strong><br>
      Devi Indrayani, Wing B-26, Near IT Park, Talawade, Pune &ndash; 411062, Maharashtra, India<br>
      Email: <a href="mailto:certification@aitf.co.in" style="color: #2563eb; text-decoration: none;">certification@aitf.co.in</a> | Website: <a href="https://www.aitf.co.in" target="_blank" style="color: #2563eb; text-decoration: none;">www.aitf.co.in</a>
    </div>

  </div>
</body>
</html>';

    $altBody = "Dear " . $clientName . " Team,\n\n"
        . "Greetings from A.I.T.F. (Assessment And Institute for Technical Forum).\n\n"
        . "Please find the attached Certification Service Agreement including fees structure for the ISO Certification of your organization.\n\n"
        . "You are requested to review and reply to this email for your acceptance.\n\n"
        . "AITF also made client portal provision, for easy access to clients for their documents and communication, you can access your supporting documents and other relevant information through the AITF Client login Portal:\n\n"
        . "AITF Client Portal – https://www.aitf.co.in/login\n\n"
        . "Login Credentials:\n"
        . "Username: " . $clientName . "\n"
        . "Password: " . $agreementNo . "\n\n"
        . "Please use the above credentials to log in to the portal and access your available documents and information.\n\n"
        . "For any clarification regarding the agreement or the portal, please feel free to contact us.\n\n"
        . "Regards,\n"
        . "AITF Management Certification Limited.\n\n"
        . "QCI-NABCB Accredited Certification Body.\n\n"
        . "Authorised Signatory\n";

    $mailer = new PHPMailer();
    $mailer->isHTML(true);
    $mailer->Subject = 'Certification Service Agreement - ' . $clientName . (!empty($agreementNo) ? ' (' . $agreementNo . ')' : '');
    $mailer->Body = $htmlBody;
    $mailer->AltBody = $altBody;

    $addedRecipients = aitfAddEmailRecipients($mailer, $recipientEmail);
    if ($addedRecipients === 0) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid recipient email address.']);
        exit;
    }

    $mailConfig = aitfGetInvoiceMailConfig();
    if (!empty($mailConfig['office_copy'])) {
        $mailer->addBCC($mailConfig['office_copy']);
    }

    $attachmentName = 'Certification_Agreement_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $agreementNo ?: $clientName) . '.pdf';
    $mailer->addStringAttachment($pdfBytes, $attachmentName, 'base64', 'application/pdf');

    list($mailSent, $mailError) = aitfSendInvoiceMailer($mailer);

    if ($mailSent) {
        $sentAt = date('Y-m-d H:i:s');
        $sentBy = $_SESSION['username'] ?? 'Staff';
        try {
            $updCert = $db->prepare("UPDATE certification_aggrement 
                SET mail_sent = 1, 
                    mail_sent_at = :sent_at, 
                    mail_sent_to = :sent_to, 
                    mail_sent_by = :sent_by 
                WHERE id = :id");
            $updCert->execute([
                ':sent_at' => $sentAt,
                ':sent_to' => $recipientEmail,
                ':sent_by' => $sentBy,
                ':id' => $certificationId
            ]);
        } catch (Exception $ex) {
            error_log('Failed to update mail_sent in certification_aggrement: ' . $ex->getMessage());
        }

        echo json_encode([
            'success' => true,
            'already_sent' => false,
            'sent_at' => date('d.m.Y h:i A', strtotime($sentAt)),
            'sent_to' => $recipientEmail,
            'message' => 'Certification Service Agreement has been successfully sent to ' . htmlspecialchars($recipientEmail) . '.'
        ]);
        exit;
    }

    error_log('CSA MAIL SEND FAILED: ' . $mailError);
    echo json_encode([
        'success' => false,
        'message' => 'Could not send email via mail server: ' . $mailError
    ]);
} catch (Throwable $e) {
    error_log('CSA MAIL SEND EXCEPTION: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send email: ' . $e->getMessage()
    ]);
}

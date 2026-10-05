<?php
/**
 * sendPaymentDueReminder.php
 * 
 * Sends an email reminder to a client for outstanding due payment from paymentPage.php.
 * Validates session/role, customer ID, email address, calculates live outstanding balance,
 * formats a branded HTML payment reminder email with bank details, and sends via PHPMailer.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

include_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/invoice_mail_helper.php';
require_once __DIR__ . '/include/payment_due_helper.php';

date_default_timezone_set('Asia/Kolkata');

if (empty($_SESSION['username']) || in_array(strtolower($_SESSION['role'] ?? ''), ['client', 'external'], true)) {
    echo json_encode(['success' => false, 'message' => 'Please sign in with an authorized staff account.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$customerId = isset($_POST['customerId']) ? (int)$_POST['customerId'] : 0;
$recipientEmail = trim((string)($_POST['recipientEmail'] ?? ''));

if ($customerId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Customer ID.']);
    exit;
}

$rawEmails = preg_split('/[,;]+/', $recipientEmail);
$validEmails = [];
foreach ($rawEmails as $em) {
    $em = trim($em);
    if ($em !== '' && filter_var($em, FILTER_VALIDATE_EMAIL)) {
        $validEmails[] = $em;
    }
}

if (empty($validEmails)) {
    echo json_encode(['success' => false, 'message' => 'Please provide at least one valid recipient email address.']);
    exit;
}

// Fetch customer and live due details
$stmtCust = $db->prepare("SELECT id, customer_name, contact_person, contact, email_id, address, gstin_no FROM customer_info WHERE id = :id AND is_deleted = 'NO'");
$stmtCust->bindParam(':id', $customerId, PDO::PARAM_INT);
$stmtCust->execute();
$customer = $stmtCust->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    echo json_encode(['success' => false, 'message' => 'Customer not found or record is deleted.']);
    exit;
}

// The internal header key distinguishes repeated headers with the same display name.
$headerKey = trim((string)($_POST['headerKey'] ?? ''));
if ($headerKey === '') {
    echo json_encode(['success' => false, 'message' => 'Select an invoice header and reopen the reminder.']);
    exit;
}
$allDues = aitf_get_due_payments($db, ['customerId' => $customerId, 'headerKey' => $headerKey]);
$clientDue = $allDues[0] ?? null;

if (!$clientDue || (float)$clientDue['balance'] <= 0.01) {
    echo json_encode(['success' => false, 'message' => 'This invoice header has no pending outstanding dues. Refresh the page.']);
    exit;
}

$clientName = trim($customer['customer_name']);
$contactPerson = trim($customer['contact_person'] ?? '');
$phone = trim($customer['contact'] ?? '');
$serviceType = trim($clientDue['service_type'] ?? 'Certification Services');
$dueAmount = number_format((float)$clientDue['balance'], 2, '.', ',');
$totalInvoiced = number_format((float)$clientDue['total_inv'], 2, '.', ',');
$totalPaid = number_format((float)$clientDue['total_paid'], 2, '.', ',');
$totalTds = number_format((float)$clientDue['total_tds'], 2, '.', ',');

// Bank Details
$stmtFirm = $db->query("SELECT * FROM consulting_firm LIMIT 1");
$firmInfo = $stmtFirm ? $stmtFirm->fetch(PDO::FETCH_ASSOC) : [];
$bankName = htmlspecialchars($firmInfo['firm_bank'] ?? 'Bank', ENT_QUOTES, 'UTF-8');
$bankAc = htmlspecialchars($firmInfo['bank_ac_no'] ?? '', ENT_QUOTES, 'UTF-8');
$bankIfsc = htmlspecialchars($firmInfo['bank_ifsc_code'] ?? '', ENT_QUOTES, 'UTF-8');

// Build Subject
$subject = "Outstanding Payment Reminder - ₹{$dueAmount} - " . $clientName;
if (!empty($_POST['subject']) && trim($_POST['subject']) !== '') {
    $subject = trim($_POST['subject']);
}

// Build HTML Body
$escClientName = htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8');
$escContactPerson = htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8');
$escServiceType = htmlspecialchars($serviceType, ENT_QUOTES, 'UTF-8');

$greetingName = $escContactPerson !== '' ? $escContactPerson : $escClientName;

$isCustom = !empty($_POST['is_custom']) && $_POST['is_custom'] == '1';
$customBody = isset($_POST['body']) ? trim((string)$_POST['body']) : '';

if ($isCustom && $customBody !== '') {
    if (strip_tags($customBody) === $customBody) {
        $bodyHtml = '<html><head><meta charset="utf-8">'
            . '<style>'
            . 'body { font-family: "Segoe UI", Arial, sans-serif; font-size: 14.5px; line-height: 1.65; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px; }'
            . '.mail-card { max-width: 650px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; box-shadow: 0 4px 16px rgba(15,23,42,0.06); }'
            . '.header-badge { display: inline-block; padding: 4px 12px; border-radius: 999px; background: #fee2e2; color: #b91c1c; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; }'
            . '.mail-content { font-size: 14.5px; line-height: 1.7; color: #1e293b; }'
            . '</style></head>'
            . '<body>'
            . '<div class="mail-card">'
            . '<div class="header-badge">Outstanding Payment Reminder</div>'
            . '<div class="mail-content">' . nl2br(htmlspecialchars($customBody, ENT_QUOTES, 'UTF-8')) . '</div>'
            . '</div>'
            . '</body></html>';
    } else {
        $bodyHtml = '<html><head><meta charset="utf-8">'
            . '<style>'
            . 'body { font-family: "Segoe UI", Arial, sans-serif; font-size: 14.5px; line-height: 1.65; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px; }'
            . '.mail-card { max-width: 650px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; }'
            . '</style></head>'
            . '<body><div class="mail-card">' . $customBody . '</div></body></html>';
    }
    $altBody = strip_tags($customBody);
} else {
    $bodyHtml = '<html><head><meta charset="utf-8">'
        . '<style>'
        . 'body { font-family: "Segoe UI", Arial, sans-serif; font-size: 14.5px; line-height: 1.65; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px; }'
        . '.mail-card { max-width: 650px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; box-shadow: 0 4px 16px rgba(15,23,42,0.06); }'
        . '.header-badge { display: inline-block; padding: 4px 12px; border-radius: 999px; background: #fee2e2; color: #b91c1c; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; }'
        . '.details-card { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #0d4d8b; border-radius: 8px; padding: 18px 22px; margin: 20px 0; }'
        . '.details-title { font-size: 15px; font-weight: 700; color: #0d4d8b; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }'
        . '.details-table { width: 100%; border-collapse: collapse; font-size: 14px; }'
        . '.details-table td { padding: 7px 4px; vertical-align: top; }'
        . '.details-table td.lbl { width: 44%; color: #64748b; font-weight: 600; }'
        . '.details-table td.val { width: 56%; color: #0f172a; font-weight: 700; }'
        . '.outstanding-highlight td { color: #dc2626 !important; font-size: 16px !important; font-weight: 800 !important; }'
        . '.bank-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; margin: 20px 0; font-size: 13.5px; color: #166534; }'
        . '.footer-section { margin-top: 28px; padding-top: 20px; border-top: 2px solid #e2e8f0; font-size: 13.5px; color: #475569; }'
        . '</style></head>'
        . '<body>'
        . '<div class="mail-card">'
        . '<div class="header-badge">Outstanding Payment Reminder</div>'
        . '<p>Dear <strong>' . $greetingName . '</strong> (' . $escClientName . '),</p>'
        . '<p>Greetings from <strong>AITF MANAGEMENT CERTIFICATION LIMITED</strong>.</p>'
        . '<p>We hope this email finds you well. This is a gentle reminder regarding the outstanding payment balance pending in your account.</p>'
        . '<div class="details-card">'
        . '<div class="details-title">Account &amp; Payment Statement:</div>'
        . '<table class="details-table">'
        . '<tr><td class="lbl">Client Name:</td><td class="val">' . $escClientName . '</td></tr>'
        . '<tr><td class="lbl">Invoice Header:</td><td class="val">' . $escServiceType . '</td></tr>'
        . '<tr><td class="lbl">Total Invoiced Amount:</td><td class="val">&#8377;' . $totalInvoiced . '</td></tr>'
        . '<tr><td class="lbl">Total Payment Received:</td><td class="val">&#8377;' . $totalPaid . '</td></tr>';

    if ((float)$clientDue['total_tds'] > 0) {
        $bodyHtml .= '<tr><td class="lbl">TDS Deducted:</td><td class="val">&#8377;' . $totalTds . '</td></tr>';
    }

    $bodyHtml .= '<tr class="outstanding-highlight"><td class="lbl" style="color: #dc2626;">Total Outstanding Due:</td><td class="val" style="color: #dc2626;">&#8377;' . $dueAmount . '/-</td></tr>'
        . '</table>'
        . '</div>'
        . '<p>As per our accounting records, the total outstanding balance of <strong style="color: #dc2626; font-size: 15px;">&#8377;' . $dueAmount . '/-</strong> is currently overdue.</p>'
        . '<p>We kindly request you to clear the outstanding balance at your earliest convenience. You may transfer the amount via NEFT / RTGS / IMPS to our company account details below:</p>'
        . '<div class="bank-box">'
        . '<strong>Company Bank Details:</strong><br>'
        . 'Bank: <b>' . $bankName . '</b><br>'
        . 'Account No: <b>' . $bankAc . '</b><br>'
        . 'IFSC Code: <b>' . $bankIfsc . '</b><br>'
        . 'Beneficiary Name: <b>AITF Management Certification Limited</b>'
        . '</div>'
        . '<p>If the payment has already been initiated or processed, please share the transaction confirmation / UTR details with us so that our accounts team can reconcile your ledger.</p>'
        . '<p>Should you have any queries or require copies of the invoices, please feel free to contact us.</p>'
        . '<p>Thank you for your prompt attention and cooperation.</p>'
        . '<div class="footer-section">'
        . '<p style="margin:0 0 4px 0;"><strong>Best Regards,</strong></p>'
        . '<p style="margin:0 0 2px 0;">Accounts &amp; Finance Team</p>'
        . '<p style="margin:0 0 8px 0; font-weight: 700; color: #0d4d8b;">AITF MANAGEMENT CERTIFICATION LIMITED</p>'
        . '<p style="margin:0 0 3px 0;">Phone: +91 9222 1836</p>'
        . '<p style="margin:0 0 3px 0;">Email: <a href="mailto:aitfacct@gmail.com" style="color: #0d4d8b; text-decoration: none;">aitfacct@gmail.com</a></p>'
        . '<p style="margin:0;">Website: <a href="http://www.aitf.co.in" style="color: #0d4d8b; text-decoration: none;">www.aitf.co.in</a></p>'
        . '</div>'
        . '</div>'
        . '</body></html>';

    $altBody = "Dear {$greetingName},\n\nThis is a reminder regarding your total outstanding payment of Rs. {$dueAmount}/- pending with AITF MANAGEMENT CERTIFICATION LIMITED.\nKindly arrange payment at your earliest convenience.\n\nRegards,\nAITF Accounts Team";
}

try {
    aitfLoadPHPMailer();
    $mailConfig = aitfGetInvoiceMailConfig();
    $mailer = new PHPMailer();
    $mailer->isHTML(true);
    $mailer->Subject = $subject;
    $mailer->Body = $bodyHtml;
    $mailer->AltBody = !empty($altBody) ? $altBody : "Dear {$greetingName},\n\nThis is a reminder regarding your total outstanding payment of Rs. {$dueAmount}/- pending with AITF MANAGEMENT CERTIFICATION LIMITED.\nKindly arrange payment at your earliest convenience.\n\nRegards,\nAITF Accounts Team";

    aitfAddEmailRecipients($mailer, $recipientEmail);
    $mailer->addCC('aitfmcl@gmail.com');
    $mailer->addCC('aitfacct@gmail.com');

    list($mailSent, $mailError) = aitfSendInvoiceMailer($mailer);

    if ($mailSent) {
        $logNote = "Reminder email sent to " . $recipientEmail . " on " . date('d.m.Y H:i') . " for " . $serviceType . " [" . $headerKey . "]";
        try {
            $stmtLog = $db->prepare("INSERT INTO `payment_page_comments` (`customer_id`, `comment`, `reminder_count`)
                                     VALUES (:cid, :comm, 1)
                                     ON DUPLICATE KEY UPDATE
                                        `comment` = CONCAT(IFNULL(NULLIF(`comment`, ''), ''), IF(TRIM(IFNULL(`comment`, '')) = '', '', '\n'), :comm2),
                                        `reminder_count` = `reminder_count` + 1");
            $stmtLog->execute([
                ':cid' => $customerId,
                ':comm' => $logNote,
                ':comm2' => $logNote
            ]);
        } catch (Throwable $e) {}

        echo json_encode([
            'success' => true,
            'message' => 'Payment reminder email successfully sent to ' . htmlspecialchars($recipientEmail) . '!',
            'loggedNote' => $logNote
        ]);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send email: ' . ($mailError ?: 'SMTP connection error. Please try again.')
        ]);
        exit;
    }
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error sending email: ' . $e->getMessage()
    ]);
    exit;
}

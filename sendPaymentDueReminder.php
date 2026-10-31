<?php
/**
 * sendPaymentDueReminder.php
 * 
 * Sends an email reminder to a client for outstanding due payment from paymentPage.php.
 * Validates session/role, customer ID, email address, calculates live outstanding balance,
 * formats a branded HTML payment reminder email with LOCKED, NON-EDITABLE bank details,
 * attaches service invoices (PDF), service files, and manually uploaded attachments,
 * and sends via PHPMailer.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

include_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/invoice_mail_helper.php';
require_once __DIR__ . '/include/payment_due_helper.php';
require_once __DIR__ . '/include/payment_due_attachments.php';

date_default_timezone_set('Asia/Kolkata');

// Ensure reminder logs table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `payment_reminder_mail_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `customer_id` INT NOT NULL,
        `header_key` VARCHAR(150) NULL,
        `service_type` VARCHAR(150) NULL,
        `recipient_email` VARCHAR(255) NOT NULL,
        `cc_emails` VARCHAR(255) NULL,
        `subject` VARCHAR(255) NULL,
        `sent_by` VARCHAR(100) NULL,
        `attachments_count` INT NOT NULL DEFAULT 0,
        `attachments_list` TEXT NULL,
        `sent_at` DATETIME NOT NULL,
        INDEX `idx_prml_cust` (`customer_id`),
        INDEX `idx_prml_sent` (`sent_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

// Sourced from consulting_firm table
$firmContactNo = '9422011836';
try {
    $stmtFirm = $db->query("SELECT firm_contact FROM consulting_firm LIMIT 1");
    if ($stmtFirm && ($fc = $stmtFirm->fetchColumn())) {
        $firmContactNo = trim($fc);
    }
} catch (Throwable $e) {}
$firmFormattedPhone = '+91 ' . preg_replace('/^(\d{5})(\d{5})$/', '$1 $2', $firmContactNo);

if (empty($_SESSION['username']) || in_array(strtolower($_SESSION['role'] ?? ''), ['client', 'external'], true)) {
    echo json_encode(['success' => false, 'message' => 'Please sign in with an authorized staff account.']);
    exit;
}

require_once __DIR__ . '/include/rbac_matrix.php';
if (!aitf_has_access("Payment Due")) {
    echo json_encode(['success' => false, 'message' => 'Access Denied. You do not have permission to send reminders.']);
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
    echo json_encode(['success' => false, 'message' => 'Select a service and reopen the reminder.']);
    exit;
}
$allDues = aitf_get_due_payments($db, ['customerId' => $customerId, 'headerKey' => $headerKey]);
$clientDue = $allDues[0] ?? null;

if (!$clientDue || (float)$clientDue['balance'] <= 0.01) {
    echo json_encode(['success' => false, 'message' => 'This service has no pending outstanding dues. Refresh the page.']);
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

// LOCKED, NON-EDITABLE COMPANY BANK DETAILS
// Mandatory: IDBI Bank Ltd, Account No: 0087102000027849, IFSC: IBKL0000087, Beneficiary: AITF Management Certification Limited
$bankName = 'IDBI Bank Ltd';
$bankAc = '0087102000027849';
$bankIfsc = 'IBKL0000087';
$beneficiary = 'AITF Management Certification Limited';

// Build Subject
$subject = "Outstanding Payment Reminder - ₹{$dueAmount} - " . $clientName;
if (!empty($_POST['subject']) && trim($_POST['subject']) !== '') {
    $subject = trim($_POST['subject']);
}

// Build HTML and Plain Text Bodies
$escClientName = htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8');
$escContactPerson = htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8');
$escServiceType = htmlspecialchars($serviceType, ENT_QUOTES, 'UTF-8');
$greetingName = $escContactPerson !== '' ? $escContactPerson : $escClientName;

$isCustom = !empty($_POST['is_custom']) && $_POST['is_custom'] == '1';
$bodyIntro = isset($_POST['body_intro']) ? trim((string)$_POST['body_intro']) : '';
$bodyClosing = isset($_POST['body_closing']) ? trim((string)$_POST['body_closing']) : '';
$rawBody = isset($_POST['body']) ? trim((string)$_POST['body']) : '';

// Non-editable Bank Box HTML
$bankBoxHtml = '<div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 14px 18px; margin: 18px 0; font-size: 13.5px; color: #166534; box-shadow: 0 1px 3px rgba(22, 101, 52, 0.05);">'
    . '<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px dashed #bbf7d0; padding-bottom: 6px;">'
    . '<strong style="color: #14532d; font-size: 14px;"><i class="fa fa-university"></i> Company Bank Details:</strong>'
    . '<span style="font-size: 11px; background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 4px; font-weight: 600;">Verified Company Account</span>'
    . '</div>'
    . '<table style="width: 100%; border-collapse: collapse; font-size: 13px;">'
    . '<tr><td style="padding: 3px 0; width: 135px; color: #166534; font-weight: 600;">Bank:</td><td style="padding: 3px 0; color: #0f172a; font-weight: 700;">' . htmlspecialchars($bankName) . '</td></tr>'
    . '<tr><td style="padding: 3px 0; color: #166534; font-weight: 600;">Account No:</td><td style="padding: 3px 0; color: #15803d; font-weight: 800; font-size: 14.5px; letter-spacing: 0.5px;">' . htmlspecialchars($bankAc) . '</td></tr>'
    . '<tr><td style="padding: 3px 0; color: #166534; font-weight: 600;">IFSC Code:</td><td style="padding: 3px 0; color: #0f172a; font-weight: 700;">' . htmlspecialchars($bankIfsc) . '</td></tr>'
    . '<tr><td style="padding: 3px 0; color: #166534; font-weight: 600;">Beneficiary Name:</td><td style="padding: 3px 0; color: #0f172a; font-weight: 700;">' . htmlspecialchars($beneficiary) . '</td></tr>'
    . '</table>'
    . '</div>';

$bankBoxText = "\n\nCompany Bank Details:\n"
    . "Bank: {$bankName}\n"
    . "Account No: {$bankAc}\n"
    . "IFSC Code: {$bankIfsc}\n"
    . "Beneficiary Name: {$beneficiary}\n\n";

if ($isCustom && ($bodyIntro !== '' || $bodyClosing !== '' || $rawBody !== '')) {
    // If intro and closing were sent separately, use them with the locked bank box in the middle
    if ($bodyIntro !== '' || $bodyClosing !== '') {
        $contentHtml = '<div style="font-size: 14px; line-height: 1.65; color: #1e293b;">'
            . nl2br(htmlspecialchars($bodyIntro, ENT_QUOTES, 'UTF-8'))
            . $bankBoxHtml
            . nl2br(htmlspecialchars($bodyClosing, ENT_QUOTES, 'UTF-8'))
            . '</div>';
        $altBody = $bodyIntro . $bankBoxText . $bodyClosing;
    } else {
        // Fallback if legacy body was posted: ensure the bank box is always present
        $cleanRaw = preg_replace('/(Bank:\s*[^\n]+|Account No:\s*[^\n]+|IFSC Code:\s*[^\n]+|Beneficiary Name:\s*[^\n]+)/i', '', $rawBody);
        $cleanRaw = trim($cleanRaw);
        $contentHtml = '<div style="font-size: 14px; line-height: 1.65; color: #1e293b;">'
            . nl2br(htmlspecialchars($cleanRaw, ENT_QUOTES, 'UTF-8'))
            . $bankBoxHtml
            . '</div>';
        $altBody = $cleanRaw . $bankBoxText;
    }

    $bodyHtml = '<html><head><meta charset="utf-8">'
        . '<style>'
        . 'body { font-family: "Segoe UI", Arial, sans-serif; font-size: 14.5px; line-height: 1.65; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px; }'
        . '.mail-card { max-width: 650px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; box-shadow: 0 4px 16px rgba(15,23,42,0.06); }'
        . '.header-badge { display: inline-block; padding: 4px 12px; border-radius: 999px; background: #fee2e2; color: #b91c1c; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; }'
        . '</style></head>'
        . '<body><div class="mail-card">'
        . '<div class="header-badge">Outstanding Payment Reminder</div>'
        . $contentHtml
        . '</div></body></html>';
} else {
    // Default branded template with locked bank details
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
        . '<tr><td class="lbl">Service Name:</td><td class="val">' . $escServiceType . '</td></tr>'
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
        . $bankBoxHtml
        . '<p>If the payment has already been initiated or processed, please share the transaction confirmation / UTR details with us so that our accounts team can reconcile your ledger.</p>'
        . '<p>Should you have any queries or require copies of the invoices, please feel free to contact us.</p>'
        . '<p>Thank you for your prompt attention and cooperation.</p>'
        . '<div class="footer-section">'
        . '<p style="margin:0 0 4px 0;"><strong>Best Regards,</strong></p>'
        . '<p style="margin:0 0 2px 0;">Accounts &amp; Finance Team</p>'
        . '<p style="margin:0 0 8px 0; font-weight: 700; color: #0d4d8b;">AITF MANAGEMENT CERTIFICATION LIMITED</p>'
        . '<p style="margin:0 0 3px 0;">Phone: ' . $firmFormattedPhone . '</p>'
        . '<p style="margin:0 0 3px 0;">Email: <a href="mailto:aitfacct@gmail.com" style="color: #0d4d8b; text-decoration: none;">aitfacct@gmail.com</a></p>'
        . '<p style="margin:0;">Website: <a href="http://www.aitf.co.in" style="color: #0d4d8b; text-decoration: none;">www.aitf.co.in</a></p>'
        . '</div>'
        . '</div>'
        . '</body></html>';

    $altBody = "Dear {$greetingName},\n\nThis is a reminder regarding your total outstanding payment of Rs. {$dueAmount}/- pending with AITF MANAGEMENT CERTIFICATION LIMITED.\nKindly arrange payment at your earliest convenience." . $bankBoxText . "Regards,\nAITF Accounts Team";
}

// -------------------------------------------------------------------------
// ATTACHMENT PROCESSING
// -------------------------------------------------------------------------
$tempFilesToCleanup = [];
$attachedFileNames = [];

try {
    aitfLoadPHPMailer();
    $mailConfig = aitfGetInvoiceMailConfig();
    $mailer = new PHPMailer();
    $mailer->isHTML(true);
    $mailer->Subject = $subject;
    $mailer->Body = $bodyHtml;
    $mailer->AltBody = !empty($altBody) ? $altBody : "Payment reminder for Rs. {$dueAmount}/-";

    aitfAddEmailRecipients($mailer, $recipientEmail);
    $mailer->addCC('aitfmcl@gmail.com');
    $mailer->addCC('aitfacct@gmail.com');

    $notifDir = __DIR__ . '/notification';
    if (!is_dir($notifDir)) {
        @mkdir($notifDir, 0777, true);
    }
    $uploadTempDir = $notifDir . '/uploads';
    if (!is_dir($uploadTempDir)) {
        @mkdir($uploadTempDir, 0777, true);
    }

    // 1. Process Kept Service Invoices
    $serviceInvoicesParam = $_POST['service_invoices'] ?? null;
    $invoicesToAttach = [];

    if ($serviceInvoicesParam !== null) {
        // User explicitly passed selected invoice IDs (may be empty array if user deleted all)
        if (is_string($serviceInvoicesParam)) {
            $decoded = json_decode($serviceInvoicesParam, true);
            $invoicesToAttach = is_array($decoded) ? array_map('intval', $decoded) : [];
        } elseif (is_array($serviceInvoicesParam)) {
            $invoicesToAttach = array_map('intval', $serviceInvoicesParam);
        }
    } else {
        // Default: If parameter not passed, attach all invoices belonging to this service header
        if (!empty($clientDue['invoices'])) {
            foreach ($clientDue['invoices'] as $inv) {
                $invoicesToAttach[] = (int)$inv['id'];
            }
        }
    }

    // Generate PDF for each kept invoice and attach
    foreach ($invoicesToAttach as $invId) {
        if ($invId <= 0) continue;

        // Fetch invoice number for clean attachment filename
        $stmtInv = $db->prepare("SELECT invoice_id, customer_id, final_prefix, final_invoice_no, invoice_no FROM invoice_details WHERE invoice_id = :id AND customer_id = :cid");
        $stmtInv->execute([':id' => $invId, ':cid' => $customerId]);
        $invRow = $stmtInv->fetch(PDO::FETCH_ASSOC);

        if (!$invRow) continue;

        $rawNo = trim((string)($invRow['final_prefix'] !== '' ? $invRow['final_prefix'] . '-' . $invRow['final_invoice_no'] : $invRow['final_invoice_no']));
        if ($rawNo === '') $rawNo = trim((string)$invRow['invoice_no']);
        if ($rawNo === '') $rawNo = 'Invoice_' . $invId;

        $cleanNo = aitf_sanitize_attachment_filename($rawNo);
        $tempPdfPath = $notifDir . '/inv_' . $invId . '_' . time() . '_' . mt_rand(1000, 9999) . '.pdf';

        if (aitf_generate_final_invoice_pdf($db, $invId, $tempPdfPath)) {
            $attDisplayName = 'Invoice-' . $cleanNo . '.pdf';
            if ($mailer->addAttachment($tempPdfPath, $attDisplayName)) {
                $attachedFileNames[] = $attDisplayName;
                $tempFilesToCleanup[] = $tempPdfPath;
            }
        }
    }

    // 2. Process Kept Service Documents
    $serviceFilesParam = $_POST['service_files'] ?? null;
    $serviceDocsToAttach = [];

    if ($serviceFilesParam !== null) {
        if (is_string($serviceFilesParam)) {
            $decoded = json_decode($serviceFilesParam, true);
            $serviceDocsToAttach = is_array($decoded) ? $decoded : [];
        } elseif (is_array($serviceFilesParam)) {
            $serviceDocsToAttach = $serviceFilesParam;
        }
    }

    $baseWebDir = realpath(__DIR__);
    foreach ($serviceDocsToAttach as $relPath) {
        $cleanRel = ltrim(str_replace('\\', '/', (string)$relPath), '/');
        // Prevent directory traversal
        if (strpos($cleanRel, '..') !== false) continue;

        $fullDocPath = $baseWebDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanRel);
        if (file_exists($fullDocPath) && is_file($fullDocPath)) {
            $docName = basename($fullDocPath);
            if ($mailer->addAttachment($fullDocPath, $docName)) {
                $attachedFileNames[] = $docName;
            }
        }
    }

    // 3. Process Manually Added Uploads from PC / Phone
    if (!empty($_FILES['manual_attachments']) && is_array($_FILES['manual_attachments']['name'])) {
        $fileCount = count($_FILES['manual_attachments']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            $err = $_FILES['manual_attachments']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            if ($err !== UPLOAD_ERR_OK) continue;

            $origName = $_FILES['manual_attachments']['name'][$i];
            $tmpUploadedPath = $_FILES['manual_attachments']['tmp_name'][$i];
            $fileSize = $_FILES['manual_attachments']['size'][$i] ?? 0;

            if ($fileSize > 26214400) { // 25 MB max
                continue;
            }

            $cleanName = aitf_sanitize_attachment_filename($origName);
            $targetUploadPath = $uploadTempDir . '/' . time() . '_' . mt_rand(1000, 9999) . '_' . $cleanName;

            if (move_uploaded_file($tmpUploadedPath, $targetUploadPath)) {
                if ($mailer->addAttachment($targetUploadPath, $origName)) {
                    $attachedFileNames[] = $origName;
                    $tempFilesToCleanup[] = $targetUploadPath;
                }
            }
        }
    }

    // Send Mail
    list($mailSent, $mailError) = aitfSendInvoiceMailer($mailer);

    // Clean up temporary generated files
    foreach ($tempFilesToCleanup as $tFile) {
        if (file_exists($tFile)) {
            @unlink($tFile);
        }
    }

    if ($mailSent) {
        $sentByUsername = trim($_SESSION['username'] ?? 'Staff');

        // Record structured log entry in payment_reminder_mail_logs with exact logged-in username
        try {
            $stmtHist = $db->prepare("INSERT INTO `payment_reminder_mail_logs` 
                (`customer_id`, `header_key`, `service_type`, `recipient_email`, `cc_emails`, `subject`, `sent_by`, `attachments_count`, `attachments_list`, `sent_at`)
                VALUES (:cid, :hkey, :stype, :em, :cc, :subj, :sby, :attcnt, :attlist, NOW())");
            $stmtHist->execute([
                ':cid' => $customerId,
                ':hkey' => $headerKey,
                ':stype' => $serviceType,
                ':em' => $recipientEmail,
                ':cc' => 'aitfmcl@gmail.com, aitfacct@gmail.com',
                ':subj' => $subject,
                ':sby' => $sentByUsername,
                ':attcnt' => count($attachedFileNames),
                ':attlist' => json_encode($attachedFileNames)
            ]);
        } catch (Throwable $e) {}

        // Increment reminder_count in payment_page_comments WITHOUT modifying manual staff notes/comments!
        try {
            $stmtLog = $db->prepare("INSERT INTO `payment_page_comments` (`customer_id`, `comment`, `reminder_count`)
                                     VALUES (:cid, '', 1)
                                     ON DUPLICATE KEY UPDATE
                                        `reminder_count` = COALESCE(`reminder_count`, 0) + 1");
            $stmtLog->execute([
                ':cid' => $customerId
            ]);
        } catch (Throwable $e) {}

        $attMsgPart = count($attachedFileNames) > 0 
            ? ' with ' . count($attachedFileNames) . ' attachment(s)' 
            : ' without attachments';

        echo json_encode([
            'success' => true,
            'message' => 'Payment reminder email successfully sent to ' . htmlspecialchars($recipientEmail) . $attMsgPart . '!',
            'sent_by' => $sentByUsername,
            'sent_at' => date('d M Y, h:i A'),
            'attachments_count' => count($attachedFileNames),
            'attachments' => $attachedFileNames
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
    // Clean up temporary generated files on exception
    foreach ($tempFilesToCleanup as $tFile) {
        if (file_exists($tFile)) {
            @unlink($tFile);
        }
    }

    echo json_encode([
        'success' => false,
        'message' => 'Error sending email: ' . $e->getMessage()
    ]);
    exit;
}

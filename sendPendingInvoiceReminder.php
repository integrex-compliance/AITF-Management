<?php
/**
 * sendPendingInvoiceReminder.php
 * 
 * Sends a payment reminder / follow-up email to the client for a pending invoice.
 * Fetches invoice & client details, formats the requested email template,
 * optionally attaches the invoice PDF, and logs the reminder sent count.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/invoice_mail_helper.php';
require_once __DIR__ . '/include/schema_auto_sync.php';

// Ensure required columns exist
aitf_auto_sync_schema($db);
aitfLoadPHPMailer();
date_default_timezone_set('Asia/Kolkata');

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');

function sendReminderResponse($success, $message, $extra = []) {
    global $isAjax;
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }

    if ($success) {
        header('Location: invoicePage.php?mailsend=1#pendingPanel');
    } else {
        header('Location: invoicePage.php?mailsend=0&mailerror=' . urlencode($message) . '#pendingPanel');
    }
    exit;
}

$invoiceId = isset($_POST['invoice_id']) ? (int) $_POST['invoice_id'] : 0;
if ($invoiceId <= 0) {
    sendReminderResponse(false, 'Invalid Invoice ID provided.');
}

// Fetch invoice and customer info
$stmt = $db->prepare("SELECT id.*, ci.customer_name, ci.address as customer_address, ci.gstin_no as customer_gstin, 
                             ci.contact_person, ci.contact, ci.email_id as customer_email
                      FROM invoice_details id
                      LEFT JOIN customer_info ci ON id.customer_id = ci.id
                      WHERE id.invoice_id = :id AND id.is_deleted = 'No'");
$stmt->bindParam(':id', $invoiceId);
$stmt->execute();
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    sendReminderResponse(false, 'Invoice not found or deleted.');
}

// Fetch consulting firm info
$stmtFirm = $db->query("SELECT * FROM consulting_firm LIMIT 1");
$firmInfo = $stmtFirm->fetch(PDO::FETCH_ASSOC);

// Fetch invoice services
$stmtSvc = $db->prepare("SELECT * FROM invoice_services WHERE invoice_ids = :id");
$stmtSvc->bindParam(':id', $invoiceId);
$stmtSvc->execute();
$invoiceServices = $stmtSvc->fetchAll(PDO::FETCH_ASSOC);

// Prepare invoice details
$isProforma = ($invoice['invoice_type'] === 'proforma');
$invoiceTypeLabel = $isProforma ? 'Proforma Invoice' : 'Final Invoice';

$invoiceNo = '';
if ($isProforma) {
    $invoiceNo = trim((string)$invoice['profarma_prefix']) !== '' ? $invoice['profarma_prefix'] . '-' . $invoice['invoice_no'] : $invoice['invoice_no'];
} else {
    $invoiceNo = trim((string)$invoice['final_prefix']) !== '' ? $invoice['final_prefix'] . '-' . $invoice['final_invoice_no'] : $invoice['final_invoice_no'];
}

$clientName = !empty($_POST['client_name']) ? trim($_POST['client_name']) : (!empty($invoice['customer_name']) ? trim($invoice['customer_name']) : 'Valued Client');

$invoiceDateRaw = !empty($invoice['invoice_date']) ? $invoice['invoice_date'] : date('Y-m-d');
$invoiceDateFormatted = date('d.m.Y', strtotime($invoiceDateRaw));

$totalAmountNum = (float) str_replace(',', '', (string)$invoice['final_amount']);
$totalAmountFormatted = number_format($totalAmountNum, 2);

// Paid amount & Outstanding amount (can be passed from form if partial payment recorded)
$paidAmountNum = isset($_POST['paid_amount']) && trim($_POST['paid_amount']) !== '' 
    ? (float) str_replace(',', '', $_POST['paid_amount']) 
    : 0.00;
$paidAmountFormatted = number_format($paidAmountNum, 2);

$pendingAmountNum = isset($_POST['pending_amount']) && trim($_POST['pending_amount']) !== ''
    ? (float) str_replace(',', '', $_POST['pending_amount'])
    : max(0, $totalAmountNum - $paidAmountNum);
$pendingAmountFormatted = number_format($pendingAmountNum, 2);

// Due Date calculation: use posted if provided, or stored, or invoice_date + 15 days
$dueDateFormatted = '';
if (!empty($_POST['due_date'])) {
    $dueDateFormatted = trim($_POST['due_date']);
} elseif (!empty($invoice['payment_due_date'])) {
    $dueDateFormatted = date('d.m.Y', strtotime($invoice['payment_due_date']));
} else {
    $dueDateFormatted = date('d.m.Y', strtotime($invoiceDateRaw . ' + 15 days'));
}

// Update payment_due_date in DB if provided
if (!empty($dueDateFormatted)) {
    $parts = explode('.', $dueDateFormatted);
    if (count($parts) === 3) {
        $dbDueDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        try {
            $upDue = $db->prepare("UPDATE invoice_details SET payment_due_date = :due WHERE invoice_id = :id");
            $upDue->execute([':due' => $dbDueDate, ':id' => $invoiceId]);
        } catch (Throwable $e) {}
    }
}

// Recipient email(s)
$recipientEmail = !empty($_POST['recipient_email']) ? trim($_POST['recipient_email']) : trim((string)$invoice['customer_email']);
if (empty($recipientEmail)) {
    sendReminderResponse(false, 'Recipient email address is required. Please enter a valid email address.');
}

// Build email body (HTML and AltBody)
$body = aitfBuildPendingPaymentReminderMailBody(
    $clientName,
    $invoiceNo,
    $invoiceDateFormatted,
    $invoiceTypeLabel,
    $totalAmountFormatted,
    $paidAmountFormatted,
    $pendingAmountFormatted,
    $dueDateFormatted,
    true
);

$altBody = aitfBuildPendingPaymentReminderMailBody(
    $clientName,
    $invoiceNo,
    $invoiceDateFormatted,
    $invoiceTypeLabel,
    $totalAmountFormatted,
    $paidAmountFormatted,
    $pendingAmountFormatted,
    $dueDateFormatted,
    false
);

// Optional PDF attachment
$attachPdf = isset($_POST['attach_pdf']) ? (int) $_POST['attach_pdf'] : 1;
$attachmentPath = '';
$attachmentName = '';

if ($attachPdf === 1) {
    try {
        require_once __DIR__ . '/tcpdf_include.php';

        if (!class_exists('AITF_REMINDER_PDF')) {
            class AITF_REMINDER_PDF extends TCPDF {
                public $pdfHeaderTitle = 'AITF MANAGEMENT CERTIFICATION LIMITED';
                public $pdfDocType = 'INVOICE';

                public function Header() {
                    $image_file = K_PATH_IMAGES . 'aitf.jpg';
                    if (file_exists($image_file)) {
                        $this->Image($image_file, 17, 5, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                    }
                    $this->SetFont('helvetica', 'B', 17.5);
                    $this->SetXY(60, 10);
                    $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

                    $this->SetFont('times', 'B', 12);
                    $this->Ln(7);
                    $this->SetXY(60, 15);
                    $this->SetTextColor(128, 184, 224);
                    $this->Cell(0, 15, '(NABCB ACCREDITED CERTIFICATION REGISTRAR FOR QMS)', 0, false, 'C', 0, '', 0, false, 'M', 'M');

                    $this->SetFont('times', 'B', 12);
                    $this->Ln(7);
                    $this->SetXY(60, 20);
                    $this->SetTextColor(128, 128, 128);
                    $this->Cell(0, 15, '(' . $this->pdfDocType . ')', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                }

                public function Footer() {
                    $this->SetY(-15);
                    $this->SetFont('helvetica', 'I', 8);
                    $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                }
            }
        }

        $pdf = new AITF_REMINDER_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->pdfDocType = $isProforma ? 'PROFORMA INVOICE' : 'TAX INVOICE';
        $pdf->SetCreator('AITF');
        $pdf->SetAuthor('AITF');
        $pdf->SetTitle($invoiceTypeLabel . ' - ' . $invoiceNo);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage();

        // Build Service rows
        $serviceRowsHtml = '';
        $sCount = 1;
        foreach ($invoiceServices as $svc) {
            $sDesc = htmlspecialchars($svc['service_desc'] ?? '', ENT_QUOTES, 'UTF-8');
            $sAmt = number_format((float) ($svc['service_amount'] ?? 0), 2);
            $serviceRowsHtml .= '<tr><td width="5%">' . $sCount . '</td><td width="60%">' . $sDesc . '</td><td width="35%">' . $sAmt . '/-</td></tr>';
            $sCount++;
        }
        if (empty($serviceRowsHtml)) {
            $serviceRowsHtml = '<tr><td width="5%">1</td><td width="60%">Certification Services</td><td width="35%">' . number_format((float) $invoice['net_price'], 2) . '/-</td></tr>';
        }

        $gstRows = '';
        if ($invoice['tax_type'] === 'CGST & SGST') {
            $gstRows .= '<tr><td colspan="2" style="text-align:right">CGST@9%</td><td>' . number_format((float)$invoice['cgst'], 2) . '/-</td></tr>';
            $gstRows .= '<tr><td colspan="2" style="text-align:right">SGST@9%</td><td>' . number_format((float)$invoice['sgst'], 2) . '/-</td></tr>';
        } elseif ($invoice['tax_type'] === 'IGST') {
            $gstRows .= '<tr><td colspan="2" style="text-align:right">IGST@18%</td><td>' . number_format((float)$invoice['igst'], 2) . '/-</td></tr>';
        }

        $noteProforma = $isProforma ? '<br/><br/><b>Note:</b>This is Proforma, don’t take any tax credit on basis of this, we send final invoice only after payment realization.' : '';

        $pdfHtml = '<br><br>
        <table border="1" cellspacing="1" cellpadding="4">
            <tr>
                <td height="90"><b>TO:</b><br><b>NAME - ' . htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') . '</b><br>ADDRESS - ' . htmlspecialchars($invoice['customer_address'] ?? '', ENT_QUOTES, 'UTF-8') . '<br>
                <b>GSTIN No.: </b>' . htmlspecialchars($invoice['customer_gstin'] ?? '', ENT_QUOTES, 'UTF-8') . '<br>
                <b>STATE: </b>' . htmlspecialchars($invoice['state'] ?? '', ENT_QUOTES, 'UTF-8') . '&nbsp; &nbsp; &nbsp; <b>STATE CODE: </b>' . htmlspecialchars($invoice['state_code'] ?? '', ENT_QUOTES, 'UTF-8') . '
                </td>
                <td><b>INVOICE NO:</b> ' . htmlspecialchars($invoiceNo, ENT_QUOTES, 'UTF-8') . '<br/><b>DATE:</b> ' . $invoiceDateFormatted . ' ' . $noteProforma . '</td>
            </tr>
            <tr><td colspan="2"><b>SAC code : </b> Other Technical And Scientific Services N.e.c – 998349</td></tr>
        </table>
        <table border="1" cellspacing="1" cellpadding="4">
            <tr><th width="5%"><b>Sr.</b></th><th width="60%"><b>Description</b></th><th width="35%"><b>Amount in Rs.</b></th></tr>
            ' . $serviceRowsHtml . '
            <tr><td colspan="2" style="text-align:right">Net Price</td><td>' . number_format((float)$invoice['net_price'], 2) . '/-</td></tr>
            ' . $gstRows . '
            <tr><td colspan="2" style="text-align:right"><b>Final Amount</b></td><td><b>' . $totalAmountFormatted . '/-</b></td></tr>
        </table>
        <table border="1" cellspacing="1" cellpadding="4">
            <tr><td><b>Bank Details:</b> ' . htmlspecialchars($firmInfo['firm_bank'] ?? 'Bank', ENT_QUOTES, 'UTF-8') . ' | <b>A/c No:</b> ' . htmlspecialchars($firmInfo['bank_ac_no'] ?? '', ENT_QUOTES, 'UTF-8') . ' | <b>IFSC:</b> ' . htmlspecialchars($firmInfo['bank_ifsc_code'] ?? '', ENT_QUOTES, 'UTF-8') . '<br/>* Please issue cheque / NEFT in favor of <b>AITF Management Certification Limited</b>.</td></tr>
        </table>';

        $pdf->writeHTML($pdfHtml, true, false, true, false, '');

        $otp = rand(100000, 999999);
        $notifDir = __DIR__ . '/notification';
        if (!is_dir($notifDir)) {
            @mkdir($notifDir, 0777, true);
        }
        $attachmentPath = $notifDir . '/' . $otp . '_reminder_invoice.pdf';
        $attachmentName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $invoiceNo) . '.pdf';
        $pdf->Output($attachmentPath, 'F');
    } catch (Throwable $e) {
        error_log('Reminder PDF Generation Notice: ' . $e->getMessage());
        $attachmentPath = '';
    }
}

// Mailer setup
$mailConfig = aitfGetInvoiceMailConfig();
$email = new PHPMailer();
$email->isHTML(true);
$email->Subject = 'Pending Payment Reminder - ' . $invoiceNo . ' - ' . $clientName;
$email->Body = $body;
$email->AltBody = $altBody;

aitfAddEmailRecipients($email, $recipientEmail);

// Add CC recipients: aitfmcl@gmail.com and aitfacct@gmail.com
$email->addCC('aitfmcl@gmail.com');
$email->addCC('aitfacct@gmail.com');

// Attach PDF if generated
if (!empty($attachmentPath) && file_exists($attachmentPath)) {
    $email->AddAttachment($attachmentPath, $attachmentName);
}

// Send email
list($mailSent, $mailError) = aitfSendInvoiceMailer($email);

if ($mailSent) {
    // Increment reminder_sent_count & record last sent timestamp
    try {
        $upStmt = $db->prepare("UPDATE invoice_details 
                                SET reminder_sent_count = reminder_sent_count + 1,
                                    reminder_last_sent = NOW(),
                                    reminder_last_to = :to
                                WHERE invoice_id = :id");
        $upStmt->execute([
            ':to' => $recipientEmail,
            ':id' => $invoiceId
        ]);
    } catch (Throwable $e) {
        error_log('Reminder counter update error: ' . $e->getMessage());
    }

    // Send confirmation message to certification@aitf.co.in
    try {
        $confirmation = new PHPMailer();
        $confirmation->isHTML(true);
        $confirmation->Subject = 'Payment Reminder Sent Confirmation - ' . $invoiceNo . ' - ' . $clientName;
        $confirmation->Body = '<html><body>'
            . '<p>Following payment reminder email and details have been sent to the client:</p>'
            . '<p><strong>Invoice Number:</strong> ' . aitfInvoiceEscape($invoiceNo) . '<br>'
            . '<strong>Invoice Type:</strong> ' . aitfInvoiceEscape($invoiceTypeLabel) . '<br>'
            . '<strong>Client:</strong> ' . aitfInvoiceEscape($clientName) . '<br>'
            . '<strong>Recipient(s):</strong> ' . aitfInvoiceEscape($recipientEmail) . '<br>'
            . '<strong>CC:</strong> aitfmcl@gmail.com, aitfacct@gmail.com<br>'
            . '<strong>Outstanding Amount:</strong> &#8377;' . aitfInvoiceEscape($pendingAmountFormatted) . '<br>'
            . '<strong>Payment Due Date:</strong> ' . aitfInvoiceEscape($dueDateFormatted) . '<br>'
            . '<strong>Sent At:</strong> ' . date('d.m.Y h:i A') . '</p>'
            . '<hr><p><strong>Message Content sent to client:</strong></p>'
            . $body
            . '</body></html>';
        $confirmation->AltBody = 'Following payment reminder has been sent to the client.' . "\n"
            . 'Invoice Number: ' . $invoiceNo . "\n"
            . 'Invoice Type: ' . $invoiceTypeLabel . "\n"
            . 'Client: ' . $clientName . "\n"
            . 'Recipient(s): ' . $recipientEmail . "\n"
            . 'CC: aitfmcl@gmail.com, aitfacct@gmail.com' . "\n"
            . 'Outstanding Amount: ₹' . $pendingAmountFormatted . "\n"
            . 'Payment Due Date: ' . $dueDateFormatted;
        $confirmation->addAddress('certification@aitf.co.in');
        if (!empty($attachmentPath) && file_exists($attachmentPath)) {
            $confirmation->AddAttachment($attachmentPath, $attachmentName);
        }
        aitfSendInvoiceMailer($confirmation);
    } catch (Throwable $e) {}

    sendReminderResponse(true, 'Payment reminder email sent successfully to ' . $recipientEmail . '.', [
        'invoice_id' => $invoiceId,
        'sent_to' => $recipientEmail,
        'sent_date' => date('d.m.Y H:i'),
        'new_count' => (int)($invoice['reminder_sent_count'] ?? 0) + 1
    ]);
} else {
    sendReminderResponse(false, 'Failed to send payment reminder email: ' . $mailError);
}

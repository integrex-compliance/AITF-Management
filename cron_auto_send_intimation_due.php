<?php
/**
 * Cron Job Script: cron_auto_send_intimation_due.php
 * Frequency: Daily at 12:00 PM (Noon) -> Cron expression: 0 12 * * *
 * Purpose: Automatically generates PDF due letters and emails them directly to clients
 *          on their due date without any manual button clicks. Also sends a confirmation
 *          mail to certification@aitf.co.in.
 */

date_default_timezone_set('Asia/Kolkata');

// If executed via web browser directly, display clean formatting
$isCli = (php_sapi_name() === 'cli');
if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "=== AITF AUTOMATED INTIMATION DUE LETTER MAILER (URL CRON TRIGGER) ===\n\n";
}

// Prevent output buffering issues during TCPDF generation
if (ob_get_level()) {
    ob_end_clean();
}

require_once __DIR__ . '/tcpdf_include.php';
include_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/schema_auto_sync.php';
if (isset($db) && $db) {
    aitf_auto_sync_schema($db);
}
require_once __DIR__ . '/include/env_helper.php';
require_once __DIR__ . '/include/certification_cycle_helper.php';

// Ensure PHPMailer is available
$phpMailerAutoloadCandidates = array(
    __DIR__ . '/PHPMailer/PHPMailerAutoload.php',
    __DIR__ . '/phpmailer/PHPMailerAutoload.php',
    __DIR__ . '/PHPMailerAutoload.php',
);
$phpMailerAutoloadLoaded = false;
foreach ($phpMailerAutoloadCandidates as $phpMailerAutoloadPath) {
    if (file_exists($phpMailerAutoloadPath)) {
        require_once $phpMailerAutoloadPath;
        $phpMailerAutoloadLoaded = true;
        break;
    }
}
if (!$phpMailerAutoloadLoaded) {
    die("Error: PHPMailer autoload not found.\n");
}

if (!class_exists('MYPDF_Cron', false)) {
    class MYPDF_Cron extends TCPDF {
        protected $header = '';
        protected $footer = '';
        
        public function Header() {
            $image_file = K_PATH_IMAGES . 'aitf.jpg';
            if (file_exists($image_file)) {
                $this->Image($image_file, 17, 5, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            }
            $this->SetFont('helvetica', 'B', 17.5);
            $this->SetXY(60, 10);
            $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));
        }

        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
        }
    }
}

function logCronEvent($message) {
    $timestamp = date("Y-m-d H:i:s");
    $formatted = "[$timestamp] " . trim($message) . "\n";
    echo $formatted;
    
    $logDir = __DIR__ . '/test_logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    @file_put_contents($logDir . '/cron_intimation_due_mail_log.txt', $formatted, FILE_APPEND);
}

function runAutoSendIntimationDue($db) {
    @set_time_limit(0);
    @ini_set('max_execution_time', '0');
    logCronEvent("=== STARTING AUTOMATED INTIMATION DUE LETTER CRON JOB (12:00 PM) ===");

    $mailConfig = aitf_get_mail_config([
        'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Intimation')
    ]);
    
    // Ensure from_email defaults to certification@aitf.co.in if empty or unset
    if (empty($mailConfig['from_email'])) {
        $mailConfig['from_email'] = 'certification@aitf.co.in';
    }
    
    $mailFromEmailHtml = htmlspecialchars($mailConfig['from_email'], ENT_QUOTES, 'UTF-8');
    $mailCcEmailHtml = !empty($mailConfig['cc_email']) ? htmlspecialchars($mailConfig['cc_email'], ENT_QUOTES, 'UTF-8') : '';

    $smtpOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $attempts = array(
        array('host' => $mailConfig['host'], 'port' => $mailConfig['port'], 'secure' => $mailConfig['secure'], 'autotls' => true, 'auth' => true),
        array('host' => 'localhost', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false),
        array('host' => 'relay-hosting.secureserver.net', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false),
    );

    $applySmtpConfig = function(PHPMailer $mailer, array $cfg) use ($mailConfig, $smtpOptions) {
        $mailer->isSMTP();
        $mailer->SMTPDebug = 0;
        $mailer->Debugoutput = 'error_log';
        $mailer->Host = $cfg['host'];
        $mailer->Hostname = $cfg['host'];
        $mailer->Port = $cfg['port'];
        $mailer->SMTPAuth = $cfg['auth'];
        $mailer->SMTPSecure = $cfg['secure'] ?: false;
        $mailer->SMTPAutoTLS = $cfg['autotls'];
        if ($cfg['auth']) {
            $mailer->Username = $mailConfig['username'];
            $mailer->Password = $mailConfig['password'];
        }
        $mailer->SMTPOptions = $smtpOptions;
        $mailer->CharSet = 'UTF-8';
        $mailer->Timeout = 10;
        $mailer->SMTPKeepAlive = false;
    };

    $notDeletedIntimation = " AND (svcdt.is_intimation_deleted IS NULL OR svcdt.is_intimation_deleted = 'N')";
    $excludeWithdrawn = " AND (ci.audit_status IS NULL OR ci.audit_status <> 'Withdrawn')";
    $latestCertificationService = aitfLatestCertificationServiceSubquery('ci');
    $validActions = "'Suspension Reminder', 'Suspend Letter', 'Withdrawal Reminder', 'Withdrawal Letter', 'Withdraw Letter', '1st Reminder', '2nd Reminder', 'Expire Letter'";

    $query = "SELECT ci.id AS customer_id, ci.customer_name, ci.unique_id, ci.email_id, svc.service_id, svc.service_type, svcdt.due_date, svcdt.sent_date, svcdt.action, svcdt.action_id 
              FROM customer_info ci 
              LEFT JOIN service svc ON ci.id = svc.customer_id 
              LEFT JOIN ca_service_dates svcdt ON svc.service_id = svcdt.service_id 
              WHERE svc.service_id = " . $latestCertificationService . " 
                AND svcdt.action IN (" . $validActions . ") 
                AND svcdt.due_date <= NOW() 
                AND svcdt.status != 'sent' 
                AND svcdt.is_deleted = 'N'" . $notDeletedIntimation . $excludeWithdrawn . " 
              ORDER BY svcdt.due_date ASC";

    $statement = $db->prepare($query);
    $statement->execute();
    $dueLetters = $statement->fetchAll(PDO::FETCH_ASSOC);

    $totalFound = count($dueLetters);
    logCronEvent("Found $totalFound pending due letter(s) eligible for automatic mail sending.");

    if ($totalFound == 0) {
        logCronEvent("No pending due letters found for today. Job completed.");
        return;
    }

    $successCount = 0;
    $failCount = 0;

    foreach ($dueLetters as $due) {
        $customer_id = $due['customer_id'];
        $actionId = $due['action_id'];
        $lettertype = $due['action'];

        logCronEvent("-------------------------------------------------------------------");
        logCronEvent("Processing due letter -> Customer: {$due['customer_name']} (ID: $customer_id) | Letter: $lettertype | Due Date: {$due['due_date']}");

        // Fetch complete customer row
        $stmtCust = $db->prepare("SELECT * FROM customer_info WHERE id = :id");
        $stmtCust->execute([':id' => $customer_id]);
        $customer = $stmtCust->fetch(PDO::FETCH_ASSOC);
        if (!$customer) {
            logCronEvent("ERROR: Customer info not found in DB for ID $customer_id. Skipping.");
            $failCount++;
            continue;
        }

        // Fetch complete ca_service_dates row
        $stmtAction = $db->prepare("SELECT * FROM ca_service_dates WHERE action_id = :action_id");
        $stmtAction->execute([':action_id' => $actionId]);
        $action1Info = $stmtAction->fetch(PDO::FETCH_ASSOC);
        if (!$action1Info) {
            logCronEvent("ERROR: Service date record not found for action_id $actionId. Skipping.");
            $failCount++;
            continue;
        }

        $date = date("d.m.Y", strtotime($action1Info['due_date']));

        $LetterParamJSON = new stdClass();
        $LetterParamJSON->dummyValue = "";
        $mailBodyPart = "";
        $letterDisplayName = $lettertype;
        $statusWord = 'pending action';
        $servicedesc = "";
        $htmlFormatFilePath = "";
        $pdfTitle = "";

        if ($lettertype == "Suspension Reminder") {
            $servicedesc = "Surveillance audit due";
            $htmlFormatFilePath = __DIR__ . "/Initimation_PDF_Formats/SuspensionReminder.txt";
            $pdfTitle = "Sureillance Audit Due";
            $letterDisplayName = 'Suspension Reminder';
            $statusWord = 'suspension';

            $reg_date = date("d.m.Y", strtotime($customer['certification_stage_date']));
            require_once __DIR__ . '/include/intimation_letter_auto_helper.php';
            $dbNextDueDate = aitfFindNextActionDueDateFromDb($db, $action1Info['service_id'] ?? null, 'Suspension Reminder', $date);
            $NextDueDate = !empty($dbNextDueDate) ? $dbNextDueDate : date("d.m.Y", strtotime('+1 month', strtotime($date)));

            $LetterParamJSON->customer_unique_id = $customer['unique_id'];
            $LetterParamJSON->due_date = $date;
            $LetterParamJSON->customer_customer_name = $customer['customer_name'];
            $LetterParamJSON->customer_address = $customer['address'];
            $LetterParamJSON->customer_contact_person = $customer['contact_person'];
            $LetterParamJSON->servicedesc = $servicedesc;
            $LetterParamJSON->customer_certification_no = $customer['certification_no'];
            $LetterParamJSON->reg_date = $reg_date;
            $LetterParamJSON->NextDueDate = $NextDueDate;
        }
        else if ($lettertype == "Suspend Letter") {
            $servicedesc = "Suspension of ISO 9001 Certificate";
            $htmlFormatFilePath = __DIR__ . "/Initimation_PDF_Formats/SuspendLetter.txt";
            $pdfTitle = "Sureillance Suspension letter";
            $letterDisplayName = 'Suspend Letter';
            $statusWord = 'suspension';
            
            $NextDueDate = date("d.m.Y", strtotime('+4 month', strtotime($date)));
            $PrevDueDate = date("M Y", strtotime('-1 month', strtotime($date)));
            
            $LetterParamJSON->customer_unique_id = $customer['unique_id'];
            $LetterParamJSON->due_date = $date;
            $LetterParamJSON->customer_customer_name = $customer['customer_name'];
            $LetterParamJSON->customer_address = $customer['address'];
            $LetterParamJSON->customer_contact_person = $customer['contact_person'];
            $LetterParamJSON->servicedesc = $servicedesc;
            $LetterParamJSON->NextDueDate = $NextDueDate;
            $LetterParamJSON->PrevDueDateMonYr = $PrevDueDate;
        }
        else if ($lettertype == "Withdrawal Reminder") {
            $servicedesc = "Reminder for Withdrawal of ISO 9001 Certificate";
            $htmlFormatFilePath = __DIR__ . "/Initimation_PDF_Formats/WithdrawalReminder.txt";
            $pdfTitle = "Sureillance Withdrawal Reminder";
            $letterDisplayName = 'Withdrawal Reminder';
            $statusWord = 'withdrawal review';
            
            require_once __DIR__ . '/include/intimation_letter_auto_helper.php';
            $dbNextDueDate = aitfFindNextActionDueDateFromDb($db, $action1Info['service_id'] ?? null, 'Withdrawal Reminder', $date);
            $NextDueDate = !empty($dbNextDueDate) ? $dbNextDueDate : date("d.m.Y", strtotime('+2 month', strtotime($date)));
            $PrevDueDate = date("d.m.Y", strtotime('-2 month', strtotime($date)));
            
            $LetterParamJSON->customer_unique_id = $customer['unique_id'];
            $LetterParamJSON->due_date = $date;
            $LetterParamJSON->customer_customer_name = $customer['customer_name'];
            $LetterParamJSON->customer_address = $customer['address'];
            $LetterParamJSON->customer_contact_person = $customer['contact_person'];
            $LetterParamJSON->servicedesc = $servicedesc;
            $LetterParamJSON->NextDueDate = $NextDueDate;
            $LetterParamJSON->PrevDueDate = $PrevDueDate;
        }
        else if ($lettertype == "Withdraw Letter" || $lettertype == "Withdrawal Letter") {
            $servicedesc = "Withdrawn Of ISO 9001 Certificate";
            $htmlFormatFilePath = __DIR__ . "/Initimation_PDF_Formats/WithdrawalLetter.txt";
            $pdfTitle = "Sureillance Withdrawal Letter";
            $letterDisplayName = 'Withdraw Letter';
            $statusWord = 'withdrawal';
            
            $PrevDueDate = date("d.m.Y", strtotime('-2 month', strtotime($date)));
            
            $LetterParamJSON->customer_unique_id = $customer['unique_id'];
            $LetterParamJSON->due_date = $date;
            $LetterParamJSON->customer_customer_name = $customer['customer_name'];
            $LetterParamJSON->customer_address = $customer['address'];
            $LetterParamJSON->customer_contact_person = $customer['contact_person'];
            $LetterParamJSON->servicedesc = $servicedesc;
            $LetterParamJSON->PrevDueDate = $PrevDueDate;
        }
        else if ($lettertype == "1st Reminder") {
            $servicedesc = "1st Reminder for Recertification audit due";
            $htmlFormatFilePath = __DIR__ . "/Initimation_PDF_Formats/FirstRCReminder.txt";
            $pdfTitle = "1st RC Reminder";
            $letterDisplayName = '1st Reminder';
            $statusWord = 'recertification reminder';
            
            $reg_date = date("d.m.Y", strtotime($customer['certification_stage_date']));
            $NextDueDate = date("d.m.Y", strtotime('+2 month', strtotime($date)));
            
            $LetterParamJSON->customer_unique_id = $customer['unique_id'];
            $LetterParamJSON->due_date = $date;
            $LetterParamJSON->customer_customer_name = $customer['customer_name'];
            $LetterParamJSON->customer_address = $customer['address'];
            $LetterParamJSON->customer_contact_person = $customer['contact_person'];
            $LetterParamJSON->servicedesc = $servicedesc;
            $LetterParamJSON->customer_certification_no = $customer['certification_no'];
            $LetterParamJSON->reg_date = $reg_date;
            $LetterParamJSON->NextDueDate = $NextDueDate;
        }
        else if ($lettertype == "2nd Reminder") {
            $servicedesc = "2nd Reminder for Recertification audit due";
            $htmlFormatFilePath = __DIR__ . "/Initimation_PDF_Formats/SecondRCReminder.txt";
            $pdfTitle = "2nd RC Reminder";
            $letterDisplayName = '2nd Reminder';
            $statusWord = 'recertification reminder';
            
            $PrevDueDate = date("d.m.Y", strtotime('-1 month', strtotime($date)));
            $NextDueDate = date("d.m.Y", strtotime('+1 month', strtotime($date)));
            
            $LetterParamJSON->customer_unique_id = $customer['unique_id'];
            $LetterParamJSON->due_date = $date;
            $LetterParamJSON->customer_customer_name = $customer['customer_name'];
            $LetterParamJSON->customer_address = $customer['address'];
            $LetterParamJSON->customer_contact_person = $customer['contact_person'];
            $LetterParamJSON->servicedesc = $servicedesc;
            $LetterParamJSON->PrevDueDate = $PrevDueDate;
            $LetterParamJSON->NextDueDate = $NextDueDate;
        }
        else if ($lettertype == "Expire Letter") {
            $servicedesc = "Expiry of ISO certification";
            $htmlFormatFilePath = __DIR__ . "/Initimation_PDF_Formats/ExpireLetter.txt";
            $pdfTitle = "Expire Letter";
            $letterDisplayName = 'Expire Letter';
            $statusWord = 'expired status';

            $PrevPrevDueDate = date("d.m.Y", strtotime('-2 month', strtotime($date)));
            $PrevDueDate = date("d.m.Y", strtotime('-1 month', strtotime($date)));
            
            $LetterParamJSON->customer_unique_id = $customer['unique_id'];
            $LetterParamJSON->due_date = $date;
            $LetterParamJSON->customer_customer_name = $customer['customer_name'];
            $LetterParamJSON->customer_address = $customer['address'];
            $LetterParamJSON->customer_contact_person = $customer['contact_person'];
            $LetterParamJSON->servicedesc = $servicedesc;
            $LetterParamJSON->PrevPrevDueDate = $PrevPrevDueDate;
            $LetterParamJSON->PrevDueDate = $PrevDueDate;
        }

        if (empty($htmlFormatFilePath) || !file_exists($htmlFormatFilePath)) {
            logCronEvent("ERROR: Template file missing at $htmlFormatFilePath. Skipping.");
            $failCount++;
            continue;
        }

        $LetterParamJSONObj = json_encode($LetterParamJSON);
        $html = file_get_contents($htmlFormatFilePath);
        $LetterParamJSONArray = json_decode($LetterParamJSONObj, true);
        foreach ($LetterParamJSONArray as $LetterParamName => $LetterParamValue) {
            $ActualLetterParamName = '&&' . $LetterParamName;
            $html = str_replace($ActualLetterParamName, $LetterParamValue, $html);
        }

        // Generate PDF
        $pdf = new MYPDF_Cron(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('AITF');
        $pdf->SetTitle($pdfTitle);
        $pdf->SetSubject('AITF');
        $pdf->SetMargins(17, 25, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('Times', '', 11);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        $fileName = $lettertype . ".pdf";
        $fileSufix = date("YmdHis") . "_" . $customer_id;
        $notificationDir = __DIR__ . '/notification';
        if (!is_dir($notificationDir)) {
            @mkdir($notificationDir, 0777, true);
        }
        $fullFilePath = $notificationDir . '/' . $fileSufix . $fileName;
        
        if (ob_get_level()) {
            ob_clean();
        }
        $pdf->Output($fullFilePath, 'F');
        logCronEvent("Generated PDF letter at: $fullFilePath");

        // Build email body using standardized assessment-body template
        require_once __DIR__ . '/include/intimation_letter_auto_helper.php';
        $mailBodyPart = aitfBuildIntimationMailBodyPart(
            $customer['customer_name'] ?? '',
            $letterDisplayName ?? $lettertype,
            $date ?? '',
            $NextDueDate ?? '',
            $fileName ?? '',
            $mailConfig['from_email'] ?? '',
            $mailConfig['cc_email'] ?? '',
            $action1Info['service_id'] ?? null,
            $db
        );

        $mailBody = '<html><body>' . $mailBodyPart . '</body></html>';
        $subject = $servicedesc . ' - ' . $customer['customer_name'];
        $contactEmail = explode(',', $customer['email_id']);
        
        // Send email to client first
        $clientMail = new PHPMailer;
        $clientMail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $clientMail->addReplyTo($mailConfig['from_email'], $mailConfig['from_name']);
        $clientMail->addCustomHeader('Return-Path', $mailConfig['from_email']);
        $validRecipientCount = 0;
        foreach ($contactEmail as $e) {
            $e = trim($e);
            if (!empty($e) && filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $clientMail->AddAddress($e);
                $validRecipientCount++;
            }
        }
        
        if ($validRecipientCount === 0) {
            logCronEvent("ERROR: No valid client email addresses found in '{$customer['email_id']}'. Skipping.");
            $failCount++;
            continue;
        }

        $clientMail->isHTML(true);
        $clientMail->Subject = $subject;
        $clientMail->Body = $mailBody;
        $clientMail->AddAttachment($fullFilePath, $fileName);

        $clientSent = false;
        $clientErrors = array();
        foreach ($attempts as $attempt) {
            $applySmtpConfig($clientMail, $attempt);
            $clientSent = $clientMail->send();
            if ($clientSent) {
                break;
            }
            $clientErrors[] = '[' . $attempt['host'] . ':' . $attempt['port'] . '] ' . $clientMail->ErrorInfo;
            $clientMail->smtpClose();
        }

        if (!$clientSent) {
            logCronEvent("ERROR: Failed sending email to client. Reasons: " . implode(' | ', $clientErrors));
            $failCount++;
        } else {
            logCronEvent("SUCCESS: Emailed due letter to client recipient(s): " . implode(', ', $contactEmail));

            // Update database status
            $mailSendSql = "UPDATE ca_service_dates SET sent_date = :date, status = 'sent' WHERE action_id = :id";
            $stmtUpdate = $db->prepare($mailSendSql);
            $sentDate = date("Y-m-d");
            $stmtUpdate->execute([':id' => $actionId, ':date' => $sentDate]);
            logCronEvent("Updated ca_service_dates action_id $actionId status to 'sent' (sent_date: $sentDate).");

            // ===== AUTO STATUS CHANGE: Update customer_info.audit_status based on letter type =====
            // This mirrors the manual send scripts' behavior so status changes happen immediately
            // when the automated cron sends the letter, without depending on manual clicks.
            $newAuditStatus = null;
            if ($lettertype == "Suspend Letter") {
                $newAuditStatus = "Suspended";
            } else if ($lettertype == "Withdraw Letter" || $lettertype == "Withdrawal Letter") {
                $newAuditStatus = "Withdrawn";
            } else if ($lettertype == "Expire Letter") {
                $newAuditStatus = "Expired";
            }
            // Note: Reminders (Suspension Reminder, Withdrawal Reminder, 1st Reminder, 2nd Reminder)
            // do NOT change the client status - client stays Active during reminder phase.

            if ($newAuditStatus !== null) {
                $updateStatusSql = "UPDATE customer_info SET audit_status = :status WHERE id = :id";
                $stmtStatus = $db->prepare($updateStatusSql);
                $stmtStatus->execute([':status' => $newAuditStatus, ':id' => $customer_id]);
                logCronEvent("AUTO STATUS CHANGE: Customer ID $customer_id audit_status updated to '$newAuditStatus' (triggered by '$lettertype' auto-mail).");
            }
            // ===== END AUTO STATUS CHANGE =====

            // Send Confirmation Mail to certification@aitf.co.in
            $officeMail = new PHPMailer;
            $officeMail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
            $officeMail->addReplyTo($mailConfig['from_email'], $mailConfig['from_name']);
            $officeMail->addCustomHeader('Return-Path', $mailConfig['from_email']);
            
            $officeRecipient = 'certification@aitf.co.in';
            $officeMail->addAddress($officeRecipient);
            if (!empty($mailConfig['admin_email']) && strtolower($mailConfig['admin_email']) !== strtolower($officeRecipient)) {
                $officeMail->addAddress($mailConfig['admin_email']);
            }

            $officeMail->isHTML(true);
            $officeMail->Subject = 'Automatic mail sent to the client for ' . $letterDisplayName . ' - ' . $customer['customer_name'];
            $officeRecipientsStr = implode(', ', array_filter($contactEmail));
            $officeMail->addAttachment($fullFilePath, $fileName);

            $statusChangeNote = ($newAuditStatus !== null) 
                ? 'The client status in the ERP has been automatically updated to <strong>' . htmlspecialchars($newAuditStatus, ENT_QUOTES, 'UTF-8') . '</strong>.'
                : 'The client status in the ERP has been automatically updated to <strong>Sent</strong> (no status change for reminder letters).';

            $officeBody = '<html><body style="font-family: Arial, sans-serif; color: #333; line-height: 1.5;">'
                . '<div style="background: #053c6d; color: #ffffff; padding: 14px 20px; border-radius: 4px 4px 0 0;">'
                . '<h3 style="margin: 0; font-size: 18px; color: #ffffff;">Automatic Due Letter Mail Sent Confirmation</h3>'
                . '</div>'
                . '<div style="border: 1px solid #d3dfe8; border-top: none; padding: 20px; background: #fdfdfd; border-radius: 0 0 4px 4px;">'
                . '<p style="font-size: 16px; color: #222;"><strong>Automatic mail sent to the client</strong> for <strong>' . htmlspecialchars($letterDisplayName, ENT_QUOTES, 'UTF-8') . '</strong>.</p>'
                . '<table border="0" cellpadding="8" cellspacing="0" style="width: 100%; background: #f4f8fa; border-radius: 4px; margin-bottom: 20px; border: 1px solid #e1ebf2;">'
                . '<tr><td style="width: 150px; color: #555;"><strong>Customer Name:</strong></td><td><strong style="color:#053c6d;">' . htmlspecialchars($customer['customer_name'], ENT_QUOTES, 'UTF-8') . '</strong> (ID: ' . htmlspecialchars($customer['unique_id'], ENT_QUOTES, 'UTF-8') . ')</td></tr>'
                . '<tr><td style="color: #555;"><strong>Client Email(s):</strong></td><td><span style="color: #0d6efd;">' . htmlspecialchars($officeRecipientsStr, ENT_QUOTES, 'UTF-8') . '</span></td></tr>'
                . '<tr><td style="color: #555;"><strong>Due Date:</strong></td><td>' . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="color: #555;"><strong>Mail Subject:</strong></td><td>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="color: #555;"><strong>Dispatch System:</strong></td><td><span style="color: green; font-weight: bold;">Automatic Sent</span></td></tr>'
                . ($newAuditStatus !== null ? '<tr><td style="color: #555;"><strong>Status Changed To:</strong></td><td><span style="color: #dc3545; font-weight: bold;">' . htmlspecialchars($newAuditStatus, ENT_QUOTES, 'UTF-8') . '</span></td></tr>' : '')
                . '</table>'
                . '<h4 style="color: #053c6d; margin-bottom: 10px; border-bottom: 2px solid #e1ebf2; padding-bottom: 6px;">Exact Message Sent to Client:</h4>'
                . '<div style="background: #ffffff; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px;">'
                . $mailBodyPart
                . '</div>'
                . '<p style="font-size: 13.5px; color: #555; margin-bottom: 0; background: #eef5fa; padding: 10px; border-left: 4px solid #1d63b0;">'
                . 'ℹ️ <strong>Note:</strong> A copy of the generated PDF letter (<strong>' . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . '</strong>) is attached to this confirmation email for your office records. ' . $statusChangeNote
                . '</p>'
                . '</div>'
                . '</body></html>';

            $officeMail->Body = $officeBody;

            $officeSent = false;
            foreach ($attempts as $attempt) {
                $applySmtpConfig($officeMail, $attempt);
                if ($officeMail->send()) {
                    $officeSent = true;
                    break;
                }
                $officeMail->smtpClose();
            }
            if ($officeSent) {
                logCronEvent("SUCCESS: Sent confirmation email to office ($officeRecipient).");
            } else {
                logCronEvent("WARNING: Failed to send confirmation copy to office ($officeRecipient): " . $officeMail->ErrorInfo);
            }

            $successCount++;
        }
    }

    logCronEvent("=== AUTOMATED CRON JOB COMPLETED: $successCount succeeded, $failCount failed ===");

    // Mark today as dispatched so webcron in header.php does not duplicate
    $cronFlagDir = __DIR__ . '/test_logs';
    if (!is_dir($cronFlagDir)) { @mkdir($cronFlagDir, 0777, true); }
    @file_put_contents($cronFlagDir . '/last_due_mail_cron_date.txt', date('Y-m-d'));
}

runAutoSendIntimationDue($db);

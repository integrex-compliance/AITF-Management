<?php

require_once __DIR__ . '/../tcpdf_include.php';
require_once __DIR__ . '/../dbConnect.php';
require_once __DIR__ . '/certification_cycle_helper.php';
require_once __DIR__ . '/invoice_mail_helper.php';

if (!defined('AITF_INTIMATION_OFFICE_COPY')) {
    define('AITF_INTIMATION_OFFICE_COPY', 'certification@aitf.co.in');
}

if (!class_exists('AITFIntimationPDF')) {
    class AITFIntimationPDF extends TCPDF
    {
        protected $header = '';

        public function Header()
        {
            $imageFile = K_PATH_IMAGES . 'aitf.jpg';
            $this->Image($imageFile, 17, 5, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $this->SetFont('helvetica', 'B', 17.5);
            $this->SetXY(60, 10);
            $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->writeHTMLCell('', '', '', '', $this->header, 0, 0, 0, true, 'L', true);
            $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));
        }

        public function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
        }
    }
}

function aitfIntimationEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function aitfIntimationDisplayDate($dateValue)
{
    $timestamp = strtotime((string) $dateValue);
    return $timestamp ? date('d.m.Y', $timestamp) : '';
}

function aitfIntimationBuildLetterContext(array $customer, array $actionRow)
{
    $letterType = $actionRow['action'];
    $date = aitfIntimationDisplayDate($actionRow['due_date']);
    $context = array(
        'letter_type' => $letterType,
        'display_name' => $letterType,
        'service_desc' => '',
        'pdf_title' => '',
        'template' => '',
        'status_word' => 'pending action',
        'params' => array(
            'dummyValue' => '',
            'customer_unique_id' => isset($customer['unique_id']) ? $customer['unique_id'] : '',
            'due_date' => $date,
            'customer_customer_name' => isset($customer['customer_name']) ? $customer['customer_name'] : '',
            'customer_address' => isset($customer['address']) ? $customer['address'] : '',
            'customer_contact_person' => isset($customer['contact_person']) ? $customer['contact_person'] : '',
            'customer_certification_no' => isset($customer['certification_no']) ? $customer['certification_no'] : '',
        ),
    );

    if ($letterType === 'Suspension Reminder') {
        $context['service_desc'] = 'Surveillance audit due';
        $context['template'] = __DIR__ . '/../Initimation_PDF_Formats/SuspensionReminder.txt';
        $context['pdf_title'] = 'Sureillance Audit Due';
        $context['status_word'] = 'suspension';
        $context['params']['servicedesc'] = $context['service_desc'];
        $context['params']['reg_date'] = aitfIntimationDisplayDate(isset($customer['certification_stage_date']) ? $customer['certification_stage_date'] : '');
        $context['params']['NextDueDate'] = date('d.m.Y', strtotime('+1 month', strtotime($date)));
    } elseif ($letterType === 'Suspend Letter') {
        $context['service_desc'] = 'Suspension of ISO 9001 Certificate';
        $context['template'] = __DIR__ . '/../Initimation_PDF_Formats/SuspendLetter.txt';
        $context['pdf_title'] = 'Sureillance Suspension letter';
        $context['status_word'] = 'suspension';
        $context['params']['servicedesc'] = $context['service_desc'];
        $context['params']['NextDueDate'] = date('d.m.Y', strtotime('+4 month', strtotime($date)));
        $context['params']['PrevDueDateMonYr'] = date('M Y', strtotime('-1 month', strtotime($date)));
    } elseif ($letterType === 'Withdrawal Reminder') {
        $context['service_desc'] = 'Reminder for Withdrawal of ISO 9001 Certificate';
        $context['template'] = __DIR__ . '/../Initimation_PDF_Formats/WithdrawalReminder.txt';
        $context['pdf_title'] = 'Sureillance Withdrawal Reminder';
        $context['status_word'] = 'withdrawal review';
        $context['params']['servicedesc'] = $context['service_desc'];
        $context['params']['NextDueDate'] = date('d.m.Y', strtotime('+2 month', strtotime($date)));
        $context['params']['PrevDueDate'] = date('d.m.Y', strtotime('-2 month', strtotime($date)));
    } elseif ($letterType === 'Withdraw Letter' || $letterType === 'Withdrawal Letter') {
        $context['letter_type'] = 'Withdraw Letter';
        $context['display_name'] = 'Withdraw Letter';
        $context['service_desc'] = 'Withdrawn Of ISO 9001 Certificate';
        $context['template'] = __DIR__ . '/../Initimation_PDF_Formats/WithdrawalLetter.txt';
        $context['pdf_title'] = 'Sureillance Withdrawal Letter';
        $context['status_word'] = 'withdrawal';
        $context['params']['servicedesc'] = $context['service_desc'];
        $context['params']['PrevDueDate'] = date('d.m.Y', strtotime('-2 month', strtotime($date)));
    } elseif ($letterType === '1st Reminder') {
        $context['service_desc'] = '1st Reminder for Recertification audit due';
        $context['template'] = __DIR__ . '/../Initimation_PDF_Formats/FirstRCReminder.txt';
        $context['pdf_title'] = '1st RC Reminder';
        $context['status_word'] = 'recertification reminder';
        $context['params']['servicedesc'] = $context['service_desc'];
        $context['params']['reg_date'] = aitfIntimationDisplayDate(isset($customer['certification_stage_date']) ? $customer['certification_stage_date'] : '');
        $context['params']['NextDueDate'] = date('d.m.Y', strtotime('+2 month', strtotime($date)));
    } elseif ($letterType === '2nd Reminder') {
        $context['service_desc'] = '2nd Reminder for Recertification audit due';
        $context['template'] = __DIR__ . '/../Initimation_PDF_Formats/SecondRCReminder.txt';
        $context['pdf_title'] = '2nd RC Reminder';
        $context['status_word'] = 'recertification reminder';
        $context['params']['servicedesc'] = $context['service_desc'];
        $context['params']['PrevDueDate'] = date('d.m.Y', strtotime('-1 month', strtotime($date)));
        $context['params']['NextDueDate'] = date('d.m.Y', strtotime('+1 month', strtotime($date)));
    } elseif ($letterType === 'Expire Letter') {
        $context['service_desc'] = 'Expiry of ISO certification';
        $context['template'] = __DIR__ . '/../Initimation_PDF_Formats/ExpireLetter.txt';
        $context['pdf_title'] = 'Expire Letter';
        $context['status_word'] = 'expired status';
        $context['params']['servicedesc'] = $context['service_desc'];
        $context['params']['PrevPrevDueDate'] = date('d.m.Y', strtotime('-2 month', strtotime($date)));
        $context['params']['PrevDueDate'] = date('d.m.Y', strtotime('-1 month', strtotime($date)));
    }

    return $context;
}

function aitfIntimationRenderLetterHtml(array $context)
{
    if (empty($context['template']) || !file_exists($context['template'])) {
        throw new Exception('Letter template not found for ' . $context['letter_type']);
    }

    $html = file_get_contents($context['template']);
    foreach ($context['params'] as $name => $value) {
        $html = str_replace('&&' . $name, $value, $html);
    }

    return $html;
}

function aitfIntimationBuildPdf($html, array $context, $outputPath = '')
{
    $pdf = new AITFIntimationPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('AITF');
    $pdf->SetTitle($context['pdf_title']);
    $pdf->SetSubject('AITF');
    $pdf->SetKeywords('AITF, PDF, intimation, letter');
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(17, 25, 5);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    if (@file_exists(__DIR__ . '/../lang/eng.php')) {
        require_once __DIR__ . '/../lang/eng.php';
        if (isset($l)) {
            $pdf->setLanguageArray($l);
        }
    }

    $pdf->SetFont('Times', '', 11);
    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');

    if ($outputPath !== '') {
        $pdf->Output($outputPath, 'F');
        return $outputPath;
    }

    return $pdf->Output('', 'S');
}

function aitfIntimationMailBody(array $customer, array $context, $fileName)
{
    return '<html><body>'
        . '<p>Dear ' . aitfIntimationEscape(isset($customer['customer_name']) ? $customer['customer_name'] : '') . ',</p>'
        . '<p>We would like to inform you that your ISO certification is currently under <strong>' . aitfIntimationEscape($context['status_word']) . '</strong> due to pending certification requirements.</p>'
        . '<p>Please find the attached <strong>' . aitfIntimationEscape($context['display_name']) . '</strong> for complete details, including the reason and the actions needed.</p>'
        . '<p>Kindly review the letter and share your <strong>corrective action plan with supporting documents</strong> within the mentioned timeline. Once we receive and verify the required information, we will move ahead accordingly.</p>'
        . '<p>If you have already completed the necessary actions, please ignore this email and send us the relevant documents for our review.</p>'
        . '<p>We look forward to your support in resolving this at the earliest.</p>'
        . '<p>Sincerely,<br>AITF Certification</p>'
        . '<p>Attachment: ' . aitfIntimationEscape($fileName) . '</p>'
        . '</body></html>';
}

function aitfIsClientStatusLetterApplicableForAutoSend($db, $serviceId, $customerId, $dueDate)
{
    $nextAuditDate = aitfFindNextCertificationServiceAuditDate($db, $serviceId, $customerId);
    if ($nextAuditDate !== '' && $nextAuditDate <= aitfNormalizeDateForComparison($dueDate)) {
        return false;
    }

    return true;
}

function aitfFetchAutoIntimationCandidates($db, $limit = 25, $backlogDays = null)
{
    $actions = aitfAllClientStatusActions();
    $placeholders = implode(',', array_fill(0, count($actions), '?'));
    $limit = max(1, min(100, (int) $limit));
    if ($backlogDays === null) {
        $configuredBacklogDays = aitf_env('AUTO_INTIMATION_BACKLOG_DAYS', 30);
        $backlogDays = is_numeric($configuredBacklogDays) ? (int) $configuredBacklogDays : 30;
    }
    $backlogDays = max(-1, min(3650, (int) $backlogDays));
    $backlogWhere = $backlogDays >= 0 ? " AND csd.due_date >= DATE_SUB(CURDATE(), INTERVAL " . $backlogDays . " DAY)" : "";

    $sql = "SELECT csd.action_id, csd.service_id, csd.action, csd.due_date, csd.sent_date, csd.status,
                   s.customer_id, s.service_type, ci.customer_name, ci.email_id
            FROM ca_service_dates csd
            INNER JOIN service s ON csd.service_id = s.service_id
            INNER JOIN customer_info ci ON s.customer_id = ci.id
            WHERE csd.is_deleted = 'N'
            AND ci.is_deleted <> 'YES'
            AND (ci.audit_status IS NULL OR ci.audit_status NOT IN ('Withdrawn', 'Application canceled'))
            AND csd.action IN ($placeholders)
            AND csd.due_date IS NOT NULL
            AND csd.due_date <> ''
            AND csd.due_date <> '0000-00-00'
            AND csd.due_date <= CURDATE()
            $backlogWhere
            AND (csd.status IS NULL OR csd.status <> 'sent')
            AND ci.email_id IS NOT NULL
            AND ci.email_id <> ''
            ORDER BY csd.due_date ASC, csd.action_id ASC
            LIMIT " . $limit;
    $statement = $db->prepare($sql);
    $statement->execute($actions);
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
    $applicable = array();

    foreach ($rows as $row) {
        if (aitfIsClientStatusLetterApplicableForAutoSend($db, $row['service_id'], $row['customer_id'], $row['due_date'])) {
            $applicable[] = $row;
        }
    }

    return $applicable;
}

function aitfSendIntimationLetterByActionId($db, $actionId, array $options = array())
{
    $defaults = array(
        'auto' => false,
        'sent_date_mode' => 'today',
    );
    $options = array_merge($defaults, $options);

    $statement = $db->prepare(
        "SELECT csd.*, s.customer_id, s.service_type
         FROM ca_service_dates csd
         INNER JOIN service s ON csd.service_id = s.service_id
         WHERE csd.action_id = :action_id
         LIMIT 1"
    );
    $statement->execute(array(':action_id' => $actionId));
    $actionRow = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$actionRow) {
        return array('success' => false, 'message' => 'Letter action not found.');
    }

    if (!empty($actionRow['status']) && $actionRow['status'] === 'sent') {
        return array('success' => true, 'message' => 'Already sent.', 'skipped' => true);
    }

    if (!aitfIsClientStatusLetterApplicableForAutoSend($db, $actionRow['service_id'], $actionRow['customer_id'], $actionRow['due_date'])) {
        return array('success' => true, 'message' => 'Not applicable because next audit was completed before the proposed due date.', 'skipped' => true);
    }

    $customerStatement = $db->prepare("SELECT * FROM customer_info WHERE id = :id LIMIT 1");
    $customerStatement->execute(array(':id' => $actionRow['customer_id']));
    $customer = $customerStatement->fetch(PDO::FETCH_ASSOC);
    if (!$customer) {
        return array('success' => false, 'message' => 'Customer not found.');
    }

    aitfLoadPHPMailer();
    $mailConfig = aitfGetInvoiceMailConfig();
    $context = aitfIntimationBuildLetterContext($customer, $actionRow);
    $html = aitfIntimationRenderLetterHtml($context);
    $fileName = $context['letter_type'] . '.pdf';
    $notificationDir = __DIR__ . '/../notification';
    if (!is_dir($notificationDir)) {
        mkdir($notificationDir, 0775, true);
    }

    $fullFilePath = $notificationDir . '/auto_' . date('YmdHis') . '_' . $actionRow['action_id'] . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);
    aitfIntimationBuildPdf($html, $context, $fullFilePath);

    $subject = $context['service_desc'] . ' - ' . $customer['customer_name'];
    $mailBody = aitfIntimationMailBody($customer, $context, $fileName);

    $clientMail = new PHPMailer;
    $recipientCount = aitfAddEmailRecipients($clientMail, $customer['email_id']);
    if ($recipientCount === 0) {
        return array('success' => false, 'message' => 'No valid client recipient email found.');
    }
    $clientMail->isHTML(true);
    $clientMail->Subject = $subject;
    $clientMail->Body = $mailBody;
    $clientMail->AddAttachment($fullFilePath, $fileName);

    list($clientSent, $clientError) = aitfSendInvoiceMailer($clientMail);
    if (!$clientSent) {
        $db->prepare("UPDATE ca_service_dates SET status = 'auto_failed' WHERE action_id = :action_id")->execute(array(':action_id' => $actionId));
        return array('success' => false, 'message' => $clientError);
    }

    $officeMail = new PHPMailer;
    $officeRecipients = array_filter(array(AITF_INTIMATION_OFFICE_COPY, $mailConfig['admin_email']));
    aitfAddEmailRecipients($officeMail, implode(',', $officeRecipients));
    $officeMail->isHTML(true);
    $officeMail->Subject = 'Following letter has been sent to client for ' . $context['display_name'];
    $officeMail->Body = '<html><body>'
        . '<p>Following letter has been sent to client for <strong>' . aitfIntimationEscape($context['display_name']) . '</strong>.</p>'
        . '<p><strong>Customer:</strong> ' . aitfIntimationEscape($customer['customer_name']) . '<br>'
        . '<strong>Client Email:</strong> ' . aitfIntimationEscape($customer['email_id']) . '<br>'
        . '<strong>Due Date:</strong> ' . aitfIntimationEscape(aitfIntimationDisplayDate($actionRow['due_date'])) . '<br>'
        . '<strong>Subject:</strong> ' . aitfIntimationEscape($subject) . '</p>'
        . '<p>Attached: ' . aitfIntimationEscape($fileName) . '</p>'
        . '</body></html>';
    $officeMail->AddAttachment($fullFilePath, $fileName);
    list($officeSent, $officeError) = aitfSendInvoiceMailer($officeMail);
    if (!$officeSent) {
        error_log('INTIMATION AUTO OFFICE COPY FAIL: ' . $officeError);
    }

    $sentDate = ($options['sent_date_mode'] === 'due_date') ? $actionRow['due_date'] : date('Y-m-d');
    $updateStatement = $db->prepare("UPDATE ca_service_dates SET sent_date = :sent_date, status = 'sent' WHERE action_id = :action_id");
    $updateStatement->execute(array(
        ':sent_date' => $sentDate,
        ':action_id' => $actionId,
    ));

    return array(
        'success' => true,
        'message' => 'Mail sent successfully.',
        'office_copy_sent' => $officeSent,
        'customer_name' => $customer['customer_name'],
        'letter_name' => $context['display_name'],
    );
}

function aitfRunAutoIntimationLetters($db, $limit = 25, $dryRun = false, $backlogDays = null)
{
    $rows = aitfFetchAutoIntimationCandidates($db, $limit, $backlogDays);
    $result = array(
        'checked' => count($rows),
        'sent' => 0,
        'failed' => 0,
        'skipped' => 0,
        'items' => array(),
    );

    foreach ($rows as $row) {
        if ($dryRun) {
            $result['skipped']++;
            $result['items'][] = array(
                'action_id' => $row['action_id'],
                'customer' => $row['customer_name'],
                'letter' => $row['action'],
                'due_date' => $row['due_date'],
                'message' => 'Dry run only.',
            );
            continue;
        }

        $sendResult = aitfSendIntimationLetterByActionId($db, $row['action_id'], array(
            'auto' => true,
            'sent_date_mode' => 'due_date',
        ));

        if (!empty($sendResult['success']) && empty($sendResult['skipped'])) {
            $result['sent']++;
        } elseif (!empty($sendResult['skipped'])) {
            $result['skipped']++;
        } else {
            $result['failed']++;
        }

        $result['items'][] = array_merge(array(
            'action_id' => $row['action_id'],
            'customer' => $row['customer_name'],
            'letter' => $row['action'],
            'due_date' => $row['due_date'],
        ), $sendResult);
    }

    return $result;
}

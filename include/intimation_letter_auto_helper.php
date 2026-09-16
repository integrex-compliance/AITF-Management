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

    $actionServiceId = isset($actionRow['service_id']) ? $actionRow['service_id'] : (isset($customer['service_id']) ? $customer['service_id'] : null);
    $dbInstance = isset($GLOBALS['db']) ? $GLOBALS['db'] : null;

    if ($letterType === 'Suspension Reminder') {
        $context['service_desc'] = 'Surveillance audit due';
        $context['template'] = __DIR__ . '/../Initimation_PDF_Formats/SuspensionReminder.txt';
        $context['pdf_title'] = 'Sureillance Audit Due';
        $context['status_word'] = 'suspension';
        $context['params']['servicedesc'] = $context['service_desc'];
        $context['params']['reg_date'] = aitfIntimationDisplayDate(isset($customer['certification_stage_date']) ? $customer['certification_stage_date'] : '');
        $dbNext = aitfFindNextActionDueDateFromDb($dbInstance, $actionServiceId, 'Suspension Reminder', $date);
        $context['params']['NextDueDate'] = !empty($dbNext) ? $dbNext : date('d.m.Y', strtotime('+1 month', strtotime($date)));
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
        $dbNext = aitfFindNextActionDueDateFromDb($dbInstance, $actionServiceId, 'Withdrawal Reminder', $date);
        $context['params']['NextDueDate'] = !empty($dbNext) ? $dbNext : date('d.m.Y', strtotime('+2 month', strtotime($date)));
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

function aitfFindNextActionDueDateFromDb($db, $serviceId, $currentAction, $currentDueDate = '')
{
    if (!$db && isset($GLOBALS['db'])) {
        $db = $GLOBALS['db'];
    }

    if (!$db || empty($serviceId)) {
        return '';
    }

    $targetAction = '';
    if ($currentAction === 'Suspension Reminder') {
        $targetAction = 'Suspend Letter';
    } elseif ($currentAction === 'Withdrawal Reminder') {
        $targetAction = 'Withdraw Letter';
    } elseif ($currentAction === '1st Reminder') {
        $targetAction = '2nd Reminder';
    } elseif ($currentAction === '2nd Reminder') {
        $targetAction = 'Expire Letter';
    }

    if ($targetAction === '') {
        return '';
    }

    try {
        if ($targetAction === 'Suspend Letter') {
            $stmt = $db->prepare("SELECT due_date FROM ca_service_dates WHERE service_id = :sid AND action IN ('Suspend Letter', 'Suspension Letter', 'Suspention Letter') AND is_deleted = 'N' AND due_date IS NOT NULL AND due_date != '0000-00-00' ORDER BY due_date ASC LIMIT 1");
        } elseif ($targetAction === 'Withdraw Letter') {
            $stmt = $db->prepare("SELECT due_date FROM ca_service_dates WHERE service_id = :sid AND action IN ('Withdraw Letter', 'Withdrawal Letter') AND is_deleted = 'N' AND due_date IS NOT NULL AND due_date != '0000-00-00' ORDER BY due_date ASC LIMIT 1");
        } else {
            $stmt = $db->prepare("SELECT due_date FROM ca_service_dates WHERE service_id = :sid AND action = :act AND is_deleted = 'N' AND due_date IS NOT NULL AND due_date != '0000-00-00' ORDER BY due_date ASC LIMIT 1");
            $stmt->bindValue(':act', $targetAction);
        }
        $stmt->bindValue(':sid', $serviceId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['due_date']) && $row['due_date'] !== '0000-00-00') {
            $ts = strtotime($row['due_date']);
            if ($ts) {
                return date('d.m.Y', $ts);
            }
        }
    } catch (Exception $e) {
        // ignore and fallback
    }

    return '';
}

function aitfBuildIntimationMailBodyPart($customerName, $lettertype, $date = '', $nextDueDate = '', $fileName = '', $fromEmail = '', $ccEmail = '', $serviceId = null, $db = null)
{
    $clientNameSafe = htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8');
    $letterNameSafe = htmlspecialchars($lettertype, ENT_QUOTES, 'UTF-8');
    $attachmentNameSafe = htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8');
    $actionType = trim($lettertype);

    if (empty($nextDueDate) && !empty($serviceId)) {
        $nextDueDate = aitfFindNextActionDueDateFromDb($db ?: ($GLOBALS['db'] ?? null), $serviceId, $actionType, $date);
    }
    $targetDate = !empty($nextDueDate) ? $nextDueDate : $date;

    $standardSignoff = '<div style="margin-top: 28px; border-top: 1px solid #e2e8f0; padding-top: 18px;">'
        . '<p style="margin: 0 0 6px; color: #475569;">Regards,</p>'
        . '<p style="margin: 0; line-height: 1.45;">'
        . '<strong>AITF Management Certification Limited.</strong><br>'
        . '<strong>QCI-NABCB Accredited Certification Body.</strong><br><br>'
        . '<strong>Authorised Signatory</strong>'
        . '</p>'
        . '</div>'
        . '<div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 20px; font-size: 12px; color: #64748b; line-height: 1.5; margin-top: 24px; border-radius: 4px;">'
        . '<strong>AITF Management Certification Limited</strong><br>'
        . 'Devi Indrayani, Wing B-26, Near IT Park, Talawade, Pune &ndash; 411062, Maharashtra, India<br>'
        . 'Email: <a href="mailto:certification@aitf.co.in" style="color: #2563eb; text-decoration: none;">certification@aitf.co.in</a> | Website: <a href="https://www.aitf.co.in" target="_blank" style="color: #2563eb; text-decoration: none;">www.aitf.co.in</a>'
        . '</div>';

    if ($actionType === "Suspension Reminder") {
        return '<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14.5px; line-height: 1.65; color: #222222; max-width: 680px;">'
            . '<p style="margin: 0 0 16px 0;">Dear ' . $clientNameSafe . ',</p>'
            . '<p style="margin: 0 0 14px 0;">This is a formal reminder regarding the continuation of your ISO certification.</p>'
            . '<p style="margin: 0 0 14px 0;">Your ISO certification is approaching the date by which the required surveillance audit and compliance requirements must be completed. You are requested to schedule and complete the required audit on or before <strong>' . $targetDate . '</strong> to maintain the validity and continuity of your ISO certification.</p>'
            . '<p style="margin: 0 0 14px 0;">Please note that failure to complete the required audit within the specified timeframe may result in the suspension of your ISO certification.</p>'
            . '<p style="margin: 0 0 14px 0;">Please find attached the <strong>Suspension Reminder</strong> letter containing the applicable audit requirements, timelines, and further details.</p>'
            . '<p style="margin: 0 0 14px 0;">We request you to take the necessary action at the earliest to avoid any interruption to your ISO certification status.</p>'
            . '<p style="margin: 0 0 14px 0;">If you have already scheduled the audit or submitted the required documents, please disregard this reminder.</p>'
            . '<p style="margin: 18px 0 0 0; font-size: 13px; color: #555555;"><strong>Attachment:</strong> ' . $attachmentNameSafe . '</p>'
            . $standardSignoff
            . '</div>';
    } 
    else if ($actionType === "Suspend Letter" || $actionType === "Suspension Letter" || $actionType === "Suspention Letter") {
        $suspensionDate = !empty($date) ? $date : (!empty($nextDueDate) ? $nextDueDate : date('d.m.Y'));
        return '<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14.5px; line-height: 1.65; color: #222222; max-width: 680px;">'
            . '<p style="margin: 0 0 16px 0;">Dear ' . $clientNameSafe . ',</p>'
            . '<p style="margin: 0 0 14px 0;">This is to formally notify you that your <strong>ISO certification will be placed under SUSPENSION effective from ' . $suspensionDate . '</strong> due to the non-completion of the required <strong>surveillance audit and/or applicable compliance requirements</strong> within the stipulated timeframe.</p>'
            . '<p style="margin: 0 0 14px 0;">Accordingly, your ISO certification will <strong>not remain valid during the suspension period</strong> and the certification status will be recorded as <strong>SUSPENDED</strong> from the effective date mentioned above.</p>'
            . '<p style="margin: 0 0 14px 0;">The suspension will remain in effect until the outstanding requirements are satisfactorily completed and the suspension is formally lifted by <strong>AITF Management Certification Limited</strong> in accordance with the applicable certification requirements.</p>'
            . '<p style="margin: 0 0 14px 0;">Please find attached the official <strong>Suspension Letter</strong>, which contains the details of the suspension, applicable requirements, and the necessary actions to be taken.</p>'
            . '<p style="margin: 0 0 14px 0;">You are requested to review the attached letter carefully and take the required action at the earliest to resolve the suspension and restore the validity of your ISO certification.</p>'
            . '<p style="margin: 0 0 14px 0;">For any clarification regarding the suspension or the required actions, please contact the Assessment Department.</p>'
            . '<p style="margin: 18px 0 0 0; font-size: 13px; color: #555555;"><strong>Attachment:</strong> ' . $attachmentNameSafe . '</p>'
            . $standardSignoff
            . '</div>';
    } 
    else if ($actionType === "Withdrawal Reminder") {
        return '<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14.5px; line-height: 1.65; color: #222222; max-width: 680px;">'
            . '<p style="margin: 0 0 16px 0;">Dear ' . $clientNameSafe . ',</p>'
            . '<p style="margin: 0 0 14px 0;">This is a <strong>final reminder</strong> regarding your ISO certification and the pending surveillance audit and/or compliance requirements.</p>'
            . '<p style="margin: 0 0 14px 0;">Please be advised that your certification is <strong>at risk of withdrawal</strong> due to the pending requirements. You are required to complete the necessary <strong>surveillance audit and compliance requirements</strong> on or before <strong>' . $targetDate . '</strong> to maintain the validity of your ISO certification.</p>'
            . '<p style="margin: 0 0 14px 0;"><strong>If the required requirements are not completed within the specified timeframe, your ISO certification will be subject to withdrawal</strong> in accordance with the applicable certification requirements.</p>'
            . '<p style="margin: 0 0 14px 0;">Please find attached the official <strong>Withdrawal Reminder</strong> letter containing the relevant details, timelines, and requirements.</p>'
            . '<p style="margin: 0 0 14px 0;">We strongly request you to take the necessary action within the specified timeframe to <strong>avoid withdrawal of your ISO certification</strong>.</p>'
            . '<p style="margin: 0 0 14px 0;">If you have already completed the required audit or submitted the necessary documents, please disregard this reminder.</p>'
            . '<p style="margin: 18px 0 0 0; font-size: 13px; color: #555555;"><strong>Attachment:</strong> ' . $attachmentNameSafe . '</p>'
            . $standardSignoff
            . '</div>';
    } 
    else if ($actionType === "Withdraw Letter" || $actionType === "Withdrawal Letter") {
        $withdrawalDate = !empty($date) ? $date : (!empty($nextDueDate) ? $nextDueDate : date('d.m.Y'));
        return '<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14.5px; line-height: 1.65; color: #222222; max-width: 680px;">'
            . '<p style="margin: 0 0 16px 0;">Dear ' . $clientNameSafe . ',</p>'
            . '<p style="margin: 0 0 14px 0;">This is to formally notify you that your <strong>ISO certification will be withdrawn effective from ' . $withdrawalDate . '</strong> due to the non-completion of the required <strong>surveillance audit and/or applicable compliance requirements</strong> within the stipulated timeframe.</p>'
            . '<p style="margin: 0 0 14px 0;">Accordingly, effective from <strong>' . $withdrawalDate . '</strong>, your ISO certification will <strong>no longer be valid</strong>, and your certification status will be recorded as <strong>WITHDRAWN</strong> by <strong>AITF Management Certification Limited</strong>.</p>'
            . '<p style="margin: 0 0 14px 0;">Please find attached the official <strong>Withdrawal Letter</strong>, which contains the details of the withdrawal, applicable requirements, and the relevant actions to be taken.</p>'
            . '<p style="margin: 0 0 14px 0;">You are requested to review the attached letter carefully. Any reference to or use of the withdrawn ISO certification after the effective withdrawal date must be discontinued in accordance with the applicable certification requirements.</p>'
            . '<p style="margin: 0 0 14px 0;">If you have any questions or require clarification regarding the withdrawal, please contact the Assessment Department.</p>'
            . '<p style="margin: 18px 0 0 0; font-size: 13px; color: #555555;"><strong>Attachment:</strong> ' . $attachmentNameSafe . '</p>'
            . $standardSignoff
            . '</div>';
    } 
    else if ($actionType === "1st Reminder") {
        return '<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14.5px; line-height: 1.65; color: #222222; max-width: 680px;">'
            . '<p style="margin: 0 0 16px 0;">Dear ' . $clientNameSafe . ',</p>'
            . '<p style="margin: 0 0 14px 0;">This is the <strong>1st Reminder</strong> to inform you that your ISO recertification / surveillance audit is due on or before <strong>' . $targetDate . '</strong>.</p>'
            . '<p style="margin: 0 0 14px 0;">Please find attached the <strong>1st Reminder</strong> notice for complete details regarding the audit schedule and requirements.</p>'
            . '<p style="margin: 0 0 14px 0;">Kindly coordinate with our assessment department to plan and confirm your audit dates at the earliest to maintain uninterrupted certification validity.</p>'
            . '<p style="margin: 0 0 14px 0;">If you have already submitted the audit plan, please treat this email as a routine reminder.</p>'
            . '<p style="margin: 18px 0 0 0; font-size: 13px; color: #555555;"><strong>Attachment:</strong> ' . $attachmentNameSafe . '</p>'
            . $standardSignoff
            . '</div>';
    } 
    else if ($actionType === "2nd Reminder") {
        return '<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14.5px; line-height: 1.65; color: #222222; max-width: 680px;">'
            . '<p style="margin: 0 0 16px 0;">Dear ' . $clientNameSafe . ',</p>'
            . '<p style="margin: 0 0 14px 0;">This is the <strong>2nd Reminder</strong> regarding your pending ISO recertification / surveillance audit, which must be completed on or before <strong>' . $targetDate . '</strong>.</p>'
            . '<p style="margin: 0 0 14px 0;">Please find attached the <strong>2nd Reminder</strong> notice with the necessary details.</p>'
            . '<p style="margin: 0 0 14px 0;">Kindly arrange for the audit schedule at the earliest to prevent the initiation of certificate suspension procedures.</p>'
            . '<p style="margin: 18px 0 0 0; font-size: 13px; color: #555555;"><strong>Attachment:</strong> ' . $attachmentNameSafe . '</p>'
            . $standardSignoff
            . '</div>';
    } 
    else if ($actionType === "Expire Letter") {
        return '<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14.5px; line-height: 1.65; color: #222222; max-width: 680px;">'
            . '<p style="margin: 0 0 16px 0;">Dear ' . $clientNameSafe . ',</p>'
            . '<p style="margin: 0 0 14px 0;">This is to inform you that the validity of your ISO certification has <strong>EXPIRED</strong> on <strong>' . $date . '</strong>.</p>'
            . '<p style="margin: 0 0 14px 0;">Please find attached the <strong>Expire Letter</strong> for your reference.</p>'
            . '<p style="margin: 0 0 14px 0;">If you wish to re-apply or obtain fresh certification, kindly contact our assessment department.</p>'
            . '<p style="margin: 18px 0 0 0; font-size: 13px; color: #555555;"><strong>Attachment:</strong> ' . $attachmentNameSafe . '</p>'
            . $standardSignoff
            . '</div>';
    }
    else {
        return '<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14.5px; line-height: 1.65; color: #222222; max-width: 680px;">'
            . '<p style="margin: 0 0 16px 0;">Dear ' . $clientNameSafe . ',</p>'
            . '<p style="margin: 0 0 14px 0;">Please find attached the official communication regarding your ISO certification: <strong>' . $letterNameSafe . '</strong>' . (!empty($date) ? ' (dated: ' . $date . ')' : '') . '.</p>'
            . '<p style="margin: 0 0 14px 0;">Kindly review the attached document and arrange for the required audit coordination / compliance at the earliest.</p>'
            . '<p style="margin: 18px 0 0 0; font-size: 13px; color: #555555;"><strong>Attachment:</strong> ' . $attachmentNameSafe . '</p>'
            . $standardSignoff
            . '</div>';
    }

}

function aitfIntimationMailBody(array $customer, array $context, $fileName)
{
    $customerName = isset($customer['customer_name']) ? $customer['customer_name'] : '';
    $letterType = isset($context['display_name']) ? $context['display_name'] : (isset($context['action']) ? $context['action'] : '');
    $dueDate = isset($context['due_date']) ? date("d.m.Y", strtotime($context['due_date'])) : '';
    $nextDueDate = isset($context['NextDueDate']) ? $context['NextDueDate'] : '';

    $serviceId = isset($context['service_id']) ? $context['service_id'] : (isset($customer['service_id']) ? $customer['service_id'] : null);
    $bodyPart = aitfBuildIntimationMailBodyPart($customerName, $letterType, $dueDate, $nextDueDate, $fileName, '', '', $serviceId);
    return '<html><body>' . $bodyPart . '</body></html>';
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

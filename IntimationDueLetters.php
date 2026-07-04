<?php

//============================================================+
// File name   : example_003.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 003 for TCPDF class
//               Custom Header and Footer
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
// database details get and display
include_once './dbConnect.php';
require_once __DIR__ . '/include/env_helper.php';
date_default_timezone_set('Asia/Kolkata');

$mailConfig = aitf_get_mail_config([
    'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Intimation')
]);
$mailFromEmailHtml = htmlspecialchars($mailConfig['from_email'], ENT_QUOTES, 'UTF-8');
$mailCcEmailHtml = htmlspecialchars($mailConfig['cc_email'], ENT_QUOTES, 'UTF-8');

// buffer output to avoid TCPDF header issues if any notices/whitespace are emitted
ob_start();

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';

    //Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        $this->Image($image_file, 17, 5, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 17.5);
        // Title
        $this->SetXY(60, 10);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->writeHTMLCell($w = '', $h = '', $x = '', $y = '', $this->header, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'L', $autopadding = true);
        $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
    }

}
// ---------------------------------------------------------

$date = "";
$reg_date = "";
$NextDueDate = "";
$PrevDueDate = "";
$servicedesc = "";
$service_id = "";
$servicetype = "";
$htmlFormatFilePath = "";
$pdfTitle = "";
$PrevDueDate = "";

//Get post parameters
$actionId = $_POST['action'];
$sourcePage = $_POST['SourcePage'];
$lettertype = $_POST['lettertype'];
$PageAction = $_POST['pageAction'];
$customer_id = $_POST['customer'];
	
if (isset($_POST['service']) && !empty($_POST['service']))
{
	$service_id = $_POST['service'];
	$servicetype = $_POST["servicetype"];
}

//Common for all
$customerinfo = "SELECT * FROM customer_info WHERE id = :id";
$statement = $db->prepare($customerinfo);
$statement->bindParam(':id', $_POST['customer']);
$statement->execute();
$customer = $statement->fetch();

if (isset($_POST['action']) && !empty($_POST['action'])) {
    $action1 = "SELECT * FROM ca_service_dates WHERE action_id=:action_id";
    $statement = $db->prepare($action1);
    $statement->bindParam(':action_id', $actionId);
    $statement->execute();
    $action1Info = $statement->fetch();

    $date = date("d.m.Y", strtotime($action1Info['due_date']));
}

//KeyValue pair for parameters
$LetterParamJSON = new stdClass();
$LetterParamJSON->dummyValue = "";
$mailBodyPart = "";
$letterDisplayName = $lettertype;
$statusWord = 'pending action';

if ($lettertype == "Suspension Reminder") {
	$servicedesc = "Surveillance audit due";
	$htmlFormatFilePath = "./Initimation_PDF_Formats/SuspensionReminder.txt";
	$pdfTitle = "Sureillance Audit Due";
	$letterDisplayName = 'Suspension Reminder';
	$statusWord = 'suspension';
	$mailBodyPart = '<p>This is to inform you that your management system certificate is <strong>approaching its expiry date</strong>, as per our records.</p>'
        . '<p>To ensure continuity of certification, you are required to <strong>inform us immediately</strong> so that the necessary <strong>audit can be planned and conducted within the stipulated timeline</strong>.</p>'
        . '<p>Please note that <strong>all relevant details regarding the due audit, scope, and validity period are clearly mentioned in the attached Due Letter</strong> for your reference.</p>'
        . '<p>We request you to review the attached document carefully and confirm your availability at the earliest to avoid any lapse in certification.</p>'
        . '<p>If you have already initiated communication regarding this matter, please consider this email as a reminder.</p>'
        . '<p>For any clarification or coordination, feel free to reply to this email.</p>'
        . '<p>Warm regards,<br><strong>AITF Team</strong><br>Assessment Department<br>AITF</p>'
        . '<p>Email: <a href="mailto:' . $mailFromEmailHtml . '">' . $mailFromEmailHtml . '</a><br>'
        . ($mailCcEmailHtml !== '' ? 'Email: <a href="mailto:' . $mailCcEmailHtml . '">' . $mailCcEmailHtml . '</a><br>' : '')
        . 'Website: <a href="http://www.aitf.in">www.aitf.in</a></p>';
	
	//build require parameters
	$reg_date = date("d.m.Y", strtotime($customer['certification_stage_date']));
	$NextDueDate = date("d.m.Y", strtotime('+1 month', strtotime($date)));
	
	//Create json for parameter values
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
	$htmlFormatFilePath = "./Initimation_PDF_Formats/SuspendLetter.txt";
	$pdfTitle = "Sureillance Suspension letter";
	$letterDisplayName = 'Suspend Letter';
	$statusWord = 'suspension';
	
	//build require parameters
	$NextDueDate = date("d.m.Y", strtotime('+4 month', strtotime($date)));
	$PrevDueDate = date("M Y", strtotime('-1 month', strtotime($date)));
	
	//Create json for parameter values
	$LetterParamJSON->customer_unique_id = $customer['unique_id'];
	$LetterParamJSON->due_date = $date;
	$LetterParamJSON->customer_customer_name = $customer['customer_name'];
	$LetterParamJSON->customer_address = $customer['address'];
	$LetterParamJSON->customer_contact_person = $customer['contact_person'];
	$LetterParamJSON->servicedesc = $servicedesc;
	$LetterParamJSON->NextDueDate = $NextDueDate;
	$LetterParamJSON->PrevDueDateMonYr = $PrevDueDate;
}
else if($lettertype == "Withdrawal Reminder"){
	$servicedesc = "Reminder for Withdrawal of ISO 9001 Certificate";
	$htmlFormatFilePath = "./Initimation_PDF_Formats/WithdrawalReminder.txt";
	$pdfTitle = "Sureillance Withdrawal Reminder";
	$letterDisplayName = 'Withdrawal Reminder';
	$statusWord = 'withdrawal review';
	
	//build require parameters
	$NextDueDate = date("d.m.Y", strtotime('+2 month', strtotime($date)));
	$PrevDueDate = date("d.m.Y", strtotime('-2 month', strtotime($date)));
	
	//Create json for parameter values
	$LetterParamJSON->customer_unique_id = $customer['unique_id'];
	$LetterParamJSON->due_date = $date;
	$LetterParamJSON->customer_customer_name = $customer['customer_name'];
	$LetterParamJSON->customer_address = $customer['address'];
	$LetterParamJSON->customer_contact_person = $customer['contact_person'];
	$LetterParamJSON->servicedesc = $servicedesc;
	$LetterParamJSON->NextDueDate = $NextDueDate;
	$LetterParamJSON->PrevDueDate = $PrevDueDate;
}
else if($lettertype == "Withdraw Letter"){
	$servicedesc = "Withdrawn Of ISO 9001 Certificate";
	$htmlFormatFilePath = "./Initimation_PDF_Formats/WithdrawalLetter.txt";
	$pdfTitle = "Sureillance Withdrawal Letter";
	$letterDisplayName = 'Withdraw Letter';
	$statusWord = 'withdrawal';
	
	//build require parameters
	$PrevDueDate = date("d.m.Y", strtotime('-2 month', strtotime($date)));
	
	//Create json for parameter values
	$LetterParamJSON->customer_unique_id = $customer['unique_id'];
	$LetterParamJSON->due_date = $date;
	$LetterParamJSON->customer_customer_name = $customer['customer_name'];
	$LetterParamJSON->customer_address = $customer['address'];
	$LetterParamJSON->customer_contact_person = $customer['contact_person'];
	$LetterParamJSON->servicedesc = $servicedesc;
	$LetterParamJSON->PrevDueDate = $PrevDueDate;
}
else if($lettertype == "1st Reminder"){
	$servicedesc = "1st Reminder for Recertification audit due";
	$htmlFormatFilePath = "./Initimation_PDF_Formats/FirstRCReminder.txt";
	$pdfTitle = "1st RC Reminder";
	$letterDisplayName = '1st Reminder';
	$statusWord = 'recertification reminder';
	
	//build require parameters
	$reg_date = date("d.m.Y", strtotime($customer['certification_stage_date']));
	$NextDueDate = date("d.m.Y", strtotime('+2 month', strtotime($date)));
	
	//Create json for parameter values
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
else if($lettertype == "2nd Reminder"){
	$servicedesc = "2nd Reminder for Recertification audit due";
	$htmlFormatFilePath = "./Initimation_PDF_Formats/SecondRCReminder.txt";
	$pdfTitle = "2nd RC Reminder";
	$letterDisplayName = '2nd Reminder';
	$statusWord = 'recertification reminder';
	
	//build require parameters
	$PrevDueDate = date("d.m.Y", strtotime('-1 month', strtotime($date)));
	$NextDueDate = date("d.m.Y", strtotime('+1 month', strtotime($date)));
	
	//Create json for parameter values
	$LetterParamJSON->customer_unique_id = $customer['unique_id'];
	$LetterParamJSON->due_date = $date;
	$LetterParamJSON->customer_customer_name = $customer['customer_name'];
	$LetterParamJSON->customer_address = $customer['address'];
	$LetterParamJSON->customer_contact_person = $customer['contact_person'];
	$LetterParamJSON->servicedesc = $servicedesc;
	$LetterParamJSON->PrevDueDate = $PrevDueDate;
	$LetterParamJSON->NextDueDate = $NextDueDate;
}
else if($lettertype == "Expire Letter"){
	$servicedesc = "Expiry of ISO certification";
	$htmlFormatFilePath = "./Initimation_PDF_Formats/ExpireLetter.txt";
	$pdfTitle = "Expire Letter";
	$letterDisplayName = 'Expire Letter';
	$statusWord = 'expired status';

	
	//build require parameters
	$PrevPrevDueDate = date("d.m.Y", strtotime('-2 month', strtotime($date)));
	$PrevDueDate = date("d.m.Y", strtotime('-1 month', strtotime($date)));
	
	//Create json for parameter values
	$LetterParamJSON->customer_unique_id = $customer['unique_id'];
	$LetterParamJSON->due_date = $date;
	$LetterParamJSON->customer_customer_name = $customer['customer_name'];
	$LetterParamJSON->customer_address = $customer['address'];
	$LetterParamJSON->customer_contact_person = $customer['contact_person'];
	$LetterParamJSON->servicedesc = $servicedesc;
	$LetterParamJSON->PrevPrevDueDate = $PrevPrevDueDate;
	$LetterParamJSON->PrevDueDate = $PrevDueDate;
}
$LetterParamJSONObj = json_encode($LetterParamJSON);

//html content Start Here
$html = file_get_contents($htmlFormatFilePath);

//Replace file string with JSON param
$LetterParamJSONArray = json_decode($LetterParamJSONObj, true);
$ActualLetterParamName="";
foreach ($LetterParamJSONArray as $LetterParamName => $LetterParamValue) {
	$ActualLetterParamName =  '&&' . $LetterParamName;
	$html = str_replace($ActualLetterParamName,$LetterParamValue,$html);
}

//Code to generate PDF letters
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle($pdfTitle);
$pdf->SetSubject('AITF');
$pdf->SetKeywords('AITF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(17, 25, 5);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// set font
$pdf->SetFont('Times', '', 11);

// add a page
$pdf->AddPage();

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');
$fileName = $lettertype . ".pdf";

// Unified mail body (letter-specific name/status applied)
$clientNameSafe = htmlspecialchars($customer['customer_name'], ENT_QUOTES, 'UTF-8');
$letterNameSafe = htmlspecialchars($letterDisplayName, ENT_QUOTES, 'UTF-8');
$attachmentNameSafe = htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8');
$statusWordSafe = htmlspecialchars($statusWord, ENT_QUOTES, 'UTF-8');
$mailBodyPart = '<p>Dear ' . $clientNameSafe . ',</p>'
    . '<p>We would like to inform you that your ISO certification is currently under <strong>' . $statusWordSafe . '</strong> due to pending certification requirements.</p>'
    . '<p>Please find the attached <strong>' . $letterNameSafe . '</strong> for complete details, including the reason and the actions needed.</p>'
    . '<p>Kindly review the letter and share your <strong>corrective action plan with supporting documents</strong> within the mentioned timeline. Once we receive and verify the required information, we will move ahead accordingly.</p>'
    . '<p>If you have already completed the necessary actions, please ignore this email and send us the relevant documents for our review.</p>'
    . '<p>We look forward to your support in resolving this at the earliest.</p>'
    . '<p>Sincerely,<br>AITF Certification</p>'
    . '<p>Attachment: ' . $attachmentNameSafe . '</p>';

if($PageAction == "PreviewPDF"){
	if (ob_get_length()) {
		ob_end_clean();
	}
	$pdf->Output($fileName, 'I');
}
else if($PageAction == "SendMail"){
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
		throw new Exception('PHPMailer autoload not found');
	}
	$isAjaxRequest = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

		$mailBody = '<html><body>' . $mailBodyPart . '</body></html>';
	$subject = $servicedesc .' - '. $customer['customer_name'];
	$contactEmail = explode(',',$customer['email_id']);
	$isMailSend = 0;
	$response = array('success' => false, 'message' => '');

	$fileSufix = date("YmdHis") . "_" . $customer_id;
	$fullFilePath = __DIR__ . '/notification/' . $fileSufix . $fileName;	
	if (ob_get_length()) {
		ob_end_clean();
	}
	$pdf->Output($fullFilePath, 'F');

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
		$mailer->Timeout = 8;
		$mailer->SMTPKeepAlive = false;
	};

	// Send email to client first
	$clientMail = new PHPMailer;
	$clientMail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
	$clientMail->addReplyTo($mailConfig['from_email'], $mailConfig['from_name']);
	$clientMail->addCustomHeader('Return-Path', $mailConfig['from_email']);
	foreach($contactEmail as $e)
	{
		if($e != NULL) $clientMail->AddAddress($e);
	}
	$clientMail->isHTML(true);
	$clientMail->Subject=$subject;
	$clientMail->Body=$mailBody;
	$clientMail->AddAttachment($fullFilePath , $fileName);

	$clientSent = false;
	$clientErrors = array();
	$successfulAttempt = null;
	foreach ($attempts as $attempt) {
		$applySmtpConfig($clientMail, $attempt);
		$clientSent = $clientMail->send();
		if ($clientSent) {
			$successfulAttempt = $attempt;
			break;
		}
		$clientErrors[] = '[' . $attempt['host'] . ':' . $attempt['port'] . ' secure=' . ($attempt['secure'] ?: 'none') . ' auth=' . ($attempt['auth'] ? 'yes' : 'no') . '] ' . $clientMail->ErrorInfo;
		error_log('INTIMATION CLIENT MAIL FAIL: ' . end($clientErrors));
		$clientMail->smtpClose();
	}

	if(!$clientSent) {
		$isMailSend = 0;
		$response['message'] = 'Mailer Error: ' . implode(' | ', $clientErrors);
	} else {
		$isMailSend = 1;
		
		// Send simple email to office
		$officeMail = new PHPMailer;
		$officeMail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
		$officeMail->addReplyTo($mailConfig['from_email'], $mailConfig['from_name']);
		$officeMail->addCustomHeader('Return-Path', $mailConfig['from_email']);
		$officeMail->addAddress($mailConfig['admin_email']);
		$officeMail->isHTML(true);
		$officeMail->Subject = 'Letter Sent Confirmation';
		$officeRecipients = implode(', ', array_filter($contactEmail));
		$officeMail->addAttachment($fullFilePath, $fileName);
		$officeMail->Body = '<html><body>'
			. '<p>Client letter sent successfully.</p>'
			. '<p><strong>Customer:</strong> ' . htmlspecialchars($customer['customer_name'], ENT_QUOTES, 'UTF-8') . '<br>'
			. '<strong>Recipients:</strong> ' . htmlspecialchars($officeRecipients, ENT_QUOTES, 'UTF-8') . '<br>'
			. '<strong>Subject:</strong> ' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</p>'
			. '<p><strong>Message sent to client:</strong></p>'
			. $mailBody
			. '<p>Attached: ' . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . '</p>'
			. '</body></html>';

		$officeSent = false;
		foreach ($attempts as $attempt) {
			$applySmtpConfig($officeMail, $attempt);
			$officeSent = $officeMail->send();
			if ($officeSent) {
				break;
			}
			error_log('INTIMATION OFFICE MAIL FAIL: [' . $attempt['host'] . ':' . $attempt['port'] . ' secure=' . ($attempt['secure'] ?: 'none') . ' auth=' . ($attempt['auth'] ? 'yes' : 'no') . '] ' . $officeMail->ErrorInfo);
			$officeMail->smtpClose();
		}
	}

	//check if mail send successfully
	if($isMailSend == 0)
	{
		if ($isAjaxRequest) {
			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		} else {
			echo '<script> localStorage.setItem("isMailSend", ' . json_encode('Failed: ' . ($response['message'] ?? 'Mailer Error')) . '); window.close(); </script>';
		}
	}
	else if($isMailSend == 1)
	{
		$mailSend = "UPDATE ca_service_dates SET sent_date = :date, status = 'sent' WHERE  action_id  = :id";
		$statement = $db->prepare($mailSend);
		$sentDate = date("Y-m-d");
		$statement->bindParam(":id", $actionId);
		$statement->bindParam(":date", $sentDate);
		$statement->execute();
		$response['success'] = true;
		$response['message'] = 'Mail sent successfully.';
		if ($isAjaxRequest) {
			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		} else {
			echo '<script> localStorage.setItem("isMailSend", ' . json_encode($response['message']) . '); window.close(); </script>';
		}
	}
		
	
	
}

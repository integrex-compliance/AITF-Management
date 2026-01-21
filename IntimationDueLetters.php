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
date_default_timezone_set('Asia/Kolkata');

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

if ($lettertype == "Suspension Reminder") {
	$servicedesc = "Surveillance audit due";
	$htmlFormatFilePath = "./Initimation_PDF_Formats/SuspensionReminder.txt";
	$pdfTitle = "Sureillance Audit Due";
	$mailBodyPart = "<p>Your First Surveillance Audit has been due, please find herewith an attachment of reminder letter.</p><br>";
	
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
	$mailBodyPart = "<p>Your Suspension Of ISO 9001 Certificate has been sent, please find an attachment.</p><br>";
	
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
	$mailBodyPart = "<p>Your ISO 9001 Certificate is about to Withdraw, please find an attachment.</p><br>";
	
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
	$mailBodyPart = "<p>Your withdrawn Of ISO 9001 Certificate has been sent, please find an attachment.</p><br>";
	
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
	$mailBodyPart = "<p>Your ISO 9001 Certificate is about to expire, please find an attachment.</p><br>";
	
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
	$mailBodyPart = "<p>Your ISO 9001 Certificate is about to expire, please find an attachment.</p><br>";
	
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
	$mailBodyPart = "<p>Your ISO 9001 Certificate is expired, please find an attachment.</p><br>";
	
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

if($PageAction == "PreviewPDF"){
	$pdf->Output($fileName, 'I');
}
else if($PageAction == "SendMail"){
	//echo '<script>alert("Mail functionality is not yet coded.")</script>';
	require 'PHPMailer/PHPMailerAutoload.php';
	
	$mailBody = '<html><body><p>Dear Sir / Maam,</p>'
		. $mailBodyPart
		. '<p>Thanks,</p>'
		. '<br>AITF-MCL'
		. '<body></html>';
	$subject = $servicedesc .' - '. $customer['customer_name'];
	$contactEmail = explode(',',$customer['email_id']);
	$isMailSend = 0;
	
	$fileSufix = date("YmdHis") . "_" . $customer_id;
	$fullFilePath = __DIR__ . '/notification/' . $fileSufix . $fileName;	
	$pdf->Output($fullFilePath, 'F');
	
	$mail = new PHPMailer;
	$mail->isSMTP();
	$mail->SMTPDebug = 2;
	$mail->Host='smtp.gmail.com';
	$mail->Port=587;
	$mail->SMTPAuth=true;
	$mail->SMTPSecure='tls';
	$mail->SMTPOptions = array(
		'ssl' => array(
			'verify_peer' => false,
			'verify_peer_name' => false,
			'allow_self_signed' => true
		)
	);
	
	$mail->Username='aitfintimation@gmail.com';
	// Gmail App Password provided for intimation account
	$mail->Password='grvbsdotegpyheib';
	$mail->setFrom('aitfintimation@gmail.com', 'AITF Intimation');
$mail->addReplyTo('aitfintimation@gmail.com', 'AITF Intimation');
	
	$mail->addCC('aitfintimation@gmail.com');
	foreach($contactEmail as $e)
	{
		if($e != NULL) $mail->AddAddress($e);
	}
	$mail->isHTML(true);
	$mail->Subject=$subject;
	$mail->Body=$mailBody;
	$mail->AddAttachment($fullFilePath , $fileName);
	
	if(!$mail->send()) {
		$isMailSend = 0;
		
	} else {
		$isMailSend = 1;		
	}
	
	//check if mail send successfully
	if($isMailSend == 0)
	{
		echo 'Mailer Error: ' . $mail->ErrorInfo;
	}
	else if($isMailSend == 1)
	{
		$mailSend = "UPDATE ca_service_dates SET sent_date = :date, status = 'sent' WHERE  action_id  = :id";
		$statement = $db->prepare($mailSend);
		$statement->bindParam(":id",$actionId);
		$statement->bindParam(":date", date("Y-m-d"));
		$statement->execute();
		echo '<script> localStorage.setItem("isMailSend", "Completed"); window.close(); </script>';
	}
		
	
	
}

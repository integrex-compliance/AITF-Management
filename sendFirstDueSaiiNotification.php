<?php

/**
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */
// Include the main TCPDF library (search for installation path).
require_once 'testemail.php';
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

//        $this->Line(5, 5, $this->getPageWidth() - 5, 5);

//        $this->Line($this->getPageWidth() - 5, 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, $this->getPageHeight() - 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, 5, 5, $this->getPageHeight() - 5);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        //$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('First Sureillance Audit Due');
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

// ---------------------------------------------------------

$customerinfo = "SELECT * FROM customer_info WHERE id = :id";
$statement = $db->prepare($customerinfo);
$statement->bindParam(':id', $_POST['customer']);
$statement->execute();
$customer = $statement->fetch();

$date = "";

if (isset($_POST['acction']) && !empty($_POST['acction'])) {

    $action1 = "SELECT * FROM ca_service_dates WHERE action_id=:action_id";
    $statement = $db->prepare($action1);
    $statement->bindParam(':action_id', $_POST['acction']);
    $statement->execute();
    $action1Info = $statement->fetch();

    $date = date("d.m.Y", strtotime($action1Info['due_date']));
}

$valid_until = "";
$before_date = "";
$StageIIDate = "";
$reg_date = "";



if (isset($_POST['service']) && !empty($_POST['service'])) {

    $service = "SELECT service_id,service_type FROM service WHERE service_id=:service_id";
    $statement = $db->prepare($service);
    $statement->bindParam(':service_id', $_POST['service']);
    $statement->execute();
    $serviceInfo = $statement->fetch();

    if ($serviceInfo['service_type'] == "CA") {

        $caService = "SELECT ca_id FROM service_type_ca WHERE service=:service_id";
        $statement = $db->prepare($caService);
        $statement->bindParam(':service_id', $serviceInfo['service_id']);
        $statement->execute();
        $caserviceInfo = $statement->fetch();

        $certification = "SELECT * FROM certificate_print WHERE service_id=:service_id";
        $statement = $db->prepare($certification);
        $statement->bindParam(':service_id', $caserviceInfo['ca_id']);
        $statement->execute();
        $certificationInfo = $statement->fetch();

        $StageII = "SELECT Date FROM cdc_report_review WHERE ca_service_id=:service_id";
        $statement = $db->prepare($StageII);
        $statement->bindParam(':service_id', $caserviceInfo['ca_id']);
        $statement->execute();
        $StageIIInfo = $statement->fetch();

        $StageIIDate = date("d.m.Y", strtotime($StageIIInfo['Date']));
        $reg_date = date("d.m.Y", strtotime($customer['certification_stage_date']));
        $valid_until = date("d.m.Y", strtotime($certificationInfo['valid_until']));
    }
    if ($serviceInfo['service_type'] == "SA-I") {

        $saiService = "SELECT sai_id FROM service_type_sai WHERE service=:service_id";
        $statement = $db->prepare($saiService);
        $statement->bindParam(':service_id', $serviceInfo['service_id']);
        $statement->execute();
        $saiserviceInfo = $statement->fetch();

        $certification = "SELECT * FROM certificate_print WHERE service_id=:service_id";
        $statement = $db->prepare($certification);
        $statement->bindParam(':service_id', $saiserviceInfo['sai_id']);
        $statement->execute();
        $certificationInfo = $statement->fetch();

        $StageII = "SELECT audit_date FROM cdc_report_review WHERE ca_service_id=:service_id";
        $statement = $db->prepare($StageII);
        $statement->bindParam(':service_id', $saiserviceInfo['sai_id']);
        $statement->execute();
        $StageIIInfo = $statement->fetch();

        $StageIIDate = date("d.m.Y", strtotime($StageIIInfo['audit_date']));
        $reg_date = date("d.m.Y", strtotime($customer['certification_stage_date']));
        $valid_until = date("d.m.Y", strtotime($certificationInfo['valid_until']));
    }
    if ($serviceInfo['service_type'] == "SA-II") {

        $saiiService = "SELECT saii_id FROM service_type_saii WHERE service=:service_id";
        $statement = $db->prepare($saiiService);
        $statement->bindParam(':service_id', $serviceInfo['service_id']);
        $statement->execute();
        $saiiserviceInfo = $statement->fetch();

        $certification = "SELECT * FROM certificate_print WHERE service_id=:service_id";
        $statement = $db->prepare($certification);
        $statement->bindParam(':service_id', $saiiserviceInfo['saii_id']);
        $statement->execute();
        $certificationInfo = $statement->fetch();

        $StageII = "SELECT audit_date FROM cdc_report_review WHERE ca_service_id=:service_id";
        $statement = $db->prepare($StageII);
        $statement->bindParam(':service_id', $saiiserviceInfo['saii_id']);
        $statement->execute();
        $StageIIInfo = $statement->fetch();

        $StageIIDate = date("d.m.Y", strtotime($StageIIInfo['audit_date']));
        $reg_date = date("d.m.Y", strtotime($customer['certification_stage_date']));
        $valid_until = date("d.m.Y", strtotime($certificationInfo['valid_until']));
    }
    if ($serviceInfo['service_type'] == "RC") {

        $rcService = "SELECT rc_id FROM service_type_rc WHERE service=:service_id";
        $statement = $db->prepare($rcService);
        $statement->bindParam(':service_id', $serviceInfo['service_id']);
        $statement->execute();
        $rcserviceInfo = $statement->fetch();

        $certification = "SELECT * FROM certificate_print WHERE service_id=:service_id";
        $statement = $db->prepare($certification);
        $statement->bindParam(':service_id', $rcserviceInfo['rc_id']);
        $statement->execute();
        $certificationInfo = $statement->fetch();

        $StageII = "SELECT audit_date FROM cdc_report_review WHERE ca_service_id=:service_id";
        $statement = $db->prepare($StageII);
        $statement->bindParam(':service_id', $rcserviceInfo['rc_id']);
        $statement->execute();
        $StageIIInfo = $statement->fetch();

        $StageIIDate = date("d.m.Y", strtotime($StageIIInfo['audit_date']));
        $reg_date = date("d.m.Y", strtotime($customer['certification_stage_date']));
        $valid_until = date("d.m.Y", strtotime($certificationInfo['valid_until']));
    }
}
$servicetype = $_POST["servicetype"];
$servicedesc = "";
if($servicetype == 'CA'){
    $servicedesc = "First surveillance audit due";
} else if($servicetype == 'SA-I'){
  $servicedesc = "Second surveillance audit due";  
} else if($servicetype == 'SA-II'){
  $servicedesc = "Recertification audit due";    
} else if($servicetype == 'RC'){
   $servicedesc = "First surveillance audit due.";   
}
// set font
$pdf->SetFont('Times', '', 11);


// add a page
$pdf->AddPage();

//html content Start Here
$html = '<span style="text-align:justify;">'
        . '<pre>Ref: ' . $customer['unique_id'] . '                                 Date:' . $date . ' </pre>
   
   <span>TO, </span>
    <br><b>'. $customer['customer_name'] . '</b> 
    <br>'. $customer['address'] . '
    <br>
    <br>Kind Attn:- M/S:' . $customer['contact_person'] . '
    <br>
    <br><b>Subject:</b>'.$servicedesc.'
    <br>
    <br><span>Dear Sir/Madam, </span>
   <br><br><span>Your organization is certified For ISO 9001 and the certification details as per our records are as below:
    <br><br> Certificate no.: '. $customer['certification_no'] .' Validity: From ' . $reg_date . ' To ' . $valid_until . '</span>
    <br><br><span>As three years validity of certificate will completed on date ' . $valid_until . ', after this date your certification will expire. To avoid expiry of your certification your organization required to successfully complete the recertification activities before expiry date.</span>
    <br><br><span>Kindly send the application form with required details to conduct the recertification audit. </span>
    <br><br><br><br><br><br><br><span>Thanks & regards,</span>
    <br><br><br><br><br><br>(STO)
    <br>(AITF Management Certification Ltd.)
    
</font></p></span>';
$contactEmail = explode(',', $customer['email_id'] ?? '');
$otp = rand(100000, 999999);

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output(__DIR__ . '/notification/'.$otp.'firstDue.pdf', 'F');

$body = '<html><body style="font-family: Arial, sans-serif; font-size: 14px; color: #333333; line-height: 1.6; padding: 15px;">'
        . '<p>Dear <strong>' . htmlspecialchars($customer['customer_name']) . '</strong> Team,</p>'
        . '<p>Your Second Surveillance Audit is due. Please find attached the reminder letter for your reference.</p>'
        . '<div style="margin-top: 28px; border-top: 1px solid #e2e8f0; padding-top: 18px;">'
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
        . '</div>'
        . '</body></html>';


$email = new PHPMailer();
$email->From      = 'aitfmcl@gmail.com';
$email->FromName  = 'AITF-MCL';
$email->Subject   = $servicedesc .' for '. $customer['customer_name'];
$email->Body      = $body;
$email->IsHTML(true);
$email->addCC('aitf.intimation@gmail.com');
//$email->addAddress('aitfmcl@gmail.com');
foreach($contactEmail as $e)
{
    if($e != NULL)
$email->AddAddress($e);
}

$file_to_attach = __DIR__ . '/notification/'.$otp.'firstDue.pdf';

$email->AddAttachment( $file_to_attach , 'First_reminder.pdf' );

$email->Send();

//$mail = new AITFServiceEmailSender();
//$mail->sendAttachEmail($customer['email_id'], $customer['customer_name'], "Your First Surveillance Audit  has been due, please find herewith an attachment of reminder letter.", $servicedesc .' for '. $customer['customer_name'], $file_to_attach);

$mailSend = "UPDATE ca_service_dates SET sent_date = :date, status = 'sent' WHERE  action_id  = :id";
$statement = $db->prepare($mailSend);
$statement->bindParam(":id",$_POST['acction']);
$statement->bindParam(":date", date("Y-m-d"));
$statement->execute();
//die;
if(isset($_POST["redirect"]) && !empty($_POST["redirect"])){
    header("Location: customerAccount.php?customerid=".$_POST['customer']);
} else {
header("Location: intimationLetter.php");
}

<?php



/**
 * Creates an example PDF TEST document using TCPDF
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
        // Page number
        //$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Suspension Of ISO 9001');
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
$pdf->SetMargins(PDF_MARGIN_LEFT, 25, 5);
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

$customerinfo = "SELECT * FROM customer_info WHERE id = :id";
$statement = $db->prepare($customerinfo);
$statement->bindParam(':id', $_POST['customer']);
$statement->execute();
$customer = $statement->fetch();

$action1 = "SELECT * FROM ca_service_dates WHERE action_id=:action_id";
$statement = $db->prepare($action1);
$statement->bindParam(':action_id', $_POST['acction']);
$statement->execute();
$action1Info = $statement->fetch();
//print_r($action1Info);
$suspensiondate = date("d.m.Y", strtotime($action1Info['due_date']));
//echo $_POST['action'].' -> '.$action1Info['due_date'].' -> '.$suspensiondate; die;
$reminder1date = date("M-Y", strtotime('-2 month', strtotime($suspensiondate)));
//echo $_POST['acction'];die();
$valid_until = "";
$before_date = "";
$StageIIDate = "";

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
        $valid_until = date("d.m.Y", strtotime($certificationInfo['valid_until']));
        $before_date = date("d.m.Y", strtotime($certificationInfo['before_date']));
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
        $valid_until = date("d.m.Y", strtotime($certificationInfo['registration_date']));
        $before_date = date("d.m.Y", strtotime($certificationInfo['valid_until']));
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
        $valid_until = date("d.m.Y", strtotime($certificationInfo['valid_until']));
        $before_date = date("d.m.Y", strtotime($certificationInfo['before_date']));
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
        $valid_until = date("d.m.Y", strtotime($certificationInfo['valid_until']));
        $before_date = date("d.m.Y", strtotime($certificationInfo['before_date']));
    }
}
$servicetype = $_POST["servicetype"];
$servicedesc = "";
if($servicetype == 'CA'){
    $servicedesc = "first surveillance audit due";
} else if($servicetype == 'SA-I'){
  $servicedesc = "second surveillance audit due";  
} else if($servicetype == 'SA-II'){
  $servicedesc = "Recertification audit due";    
} else if($servicetype == 'RC'){
   $servicedesc = "first surveillance audit due.";   
}

// set font
$pdf->SetFont('Times', '', 11);

// add a page
$pdf->AddPage();

//html content Start Here
$html = '<span style="text-align:justify;">'
        . '<pre>Ref: ' . $customer['unique_id'] . '                                         Date:' . $suspensiondate . ' </pre>
   
    <span>TO: </span>
    <br>
    <span><b>M/S:  ' . $customer['customer_name'] . '</b> </span>
    <br>
   <span>' . $customer['address'] . '</span>
    <br>
    <br>
    <span>Kind Attn:  M/S: ' . $customer['contact_person'] . '</span>
    <br>
    <br><span><b>Subject:</b> Suspension Of ISO 9001 Certificate</span>
    
    <br><br>
    <span> Dear Sir/Madam, </span>
    <br>
    <br>
    
     Your '.$servicedesc.' is due since ' . $reminder1date . '. It is requirement that the '.$servicedesc.' has to be completed and the non-conformances raised during the audit shall resolved within 12 months from the last day of the Certification Decision (Registration Date).
    <br>
    <br>  
    <span>Since the '.$servicedesc.' of your organization is not completed within the stipulated period, your
    certification stands suspended with effect from ' . $suspensiondate . '.</span>
    <br>
    <br>  
    <span>You are required to inform us immediately the dates convenient to you for conducting the audit
    so as to revoke the suspension and <b>to avoid permanently cancelation of your certificate i.e Withdrawal</b></span>
    <br>
    <br>  
    <span>During the period of suspension, your organization can not claim as ISO 9001
    certified and you are required to restrain from using the logos of certification body with immediate effect.</span>
    <br>
    <br>
     <br><span>Thanks & regards,</span>
    <br>AITF Management Certification Ltd.
    <br>(Technical Operational Staff)
    
</font></p></span>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');
$otp = rand(100000, 999999);
$contactEmail = explode(',',$customer['email_id']);
$pdf->Output(__DIR__ . '/notification/' . $otp . 'suspension.pdf', 'F');

$body = '<html><body><p>Dear Sir / Mam,</p>'
        . '<p>Your Suspension Of ISO 9001 Certificate has been sent, please find an attachment.</p>'
        . '<p>Thanks,</p>'
        . '<p>AITF-MCL</p>'
        . '<body></html>';

$email = new PHPMailer();
$email->From = 'aitfmcl@gmail.com';
$email->FromName = 'AITF-MCL';
$email->Subject = 'Suspension Of ISO 9001 Certificate - '.$customer['customer_name'];
$email->Body = $body;
$email->IsHTML(true);
$email->addCC('aitf.intimation@gmail.com');
foreach($contactEmail as $e)
{
    if($e != NULL)
$email->AddAddress($e);
}

$file_to_attach = __DIR__ . '/notification/' . $otp . 'suspension.pdf';

$email->AddAttachment($file_to_attach, 'Suspention.pdf');

$email->Send();

$mailSend = "UPDATE ca_service_dates SET sent_date = :date, status = 'sent' WHERE  action_id  = :id";
$statement = $db->prepare($mailSend);
$statement->bindParam(":id", $_POST['acction']);
$statement->bindParam(":date", date("Y-m-d"));
$statement->execute();

$customerStatus = "suspend";

$updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
$statement = $db->prepare($updateCustomerStatus);
$statement->bindParam(':status', $customerStatus);
$statement->bindParam(':customer', $_POST['customer']);
$statement->execute();

if (isset($_POST["redirect"]) && !empty($_POST["redirect"])) {
    header("Location: customerAccount.php?customerid=" . $_POST['customer']);
} else {
    header("Location: intimationLetter.php");
}

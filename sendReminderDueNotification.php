<?php



/**
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */

require_once('tcpdf_include.php');

include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

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

//        $this->Line(5, 5, $this->getPageWidth() - 5, 5);
//
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
//        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Reminder/Intimation');
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
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
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
$statement->bindParam(':action_id', $_POST['action']);
$statement->execute();
$action1Info = $statement->fetch();

$reminder2date = date("d.m.Y", strtotime($action1Info['due_date']));
$reminder1date = date("d.m.Y", strtotime('-1 month', strtotime($reminder2date)));
$suspensiondate = date("d.m.Y", strtotime('+1 month', strtotime($reminder2date)));

$servicetype = $_POST["servicetype"];
$servicedesc = "";
if($servicetype == 'CA'){
    $servicedesc = "first surveillance audit due";
} else if($servicetype == 'SA-I'){
  $servicedesc = "second surveillance audit due";  
} else if($servicetype == 'SA-II'){
  $servicedesc = "Last Intimation for recertification (ISO 9001)";    
} else if($servicetype == 'RC'){
   $servicedesc = "first surveillance audit due.";   
}
// ---------------------------------------------------------
// set font
$pdf->SetFont('Times', '', 11);

// add a page
$pdf->AddPage();

//html content Start Here
$html = '<span style="text-align:justify;">'
        . '<pre>Ref: ' . $customer['unique_id'] . '                                        Date:' . $reminder2date . ' </pre>
    
    <span>TO: </span>
    <br>
    <span><b>M/S: ' . $customer['customer_name'] . '</b> </span>
    <br>
   <span> ' . $customer['address'] . '</span>
    <br>
    <br>
    <span>Kind Attn:  M/S: ' . $customer['contact_person'] . '</span>
    <br>
    <br>
    <span><b>Subject:</b> Reminder / intimation (ISO 9001:2015)</span>
    <br>
    <br>
    <span>Dear Sir/Madam, </span>
    <br><br>
    <span>We Would like to draw your attention to our letter dated ' . $reminder1date . ' and to remind you that:</span>
    <br><br>
    <span>Your recertification audit is due, Please inform us urgently the date(s) convenient to you for the audit to avoid expiry of certificate.</span>
    <br><br>
    <span>Kindly note that failure to successfully conduct the recertification audit before expiry of validity of certificate would lead to the cancellation of your certificate, as per procedural requirement and agreement. <u>If recertification not conducted before date ' . $suspensiondate . ' Your ISO certificate will be expired.</u>
    </span>
    <br>
    <br><span>Please contact us in case you need any clarification.</span>
    <br>
    <br>
    <br><br><br><br><br><span>Thanks & regards,</span>
    <br><br><br><br>(STO)
    <br>(AITF Management Certification Ltd.)
    
</font></p></span>';
$contactEmail = explode(',',$customer['email_id']);

$otp = rand(100000, 999999);
// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
//$pdf->Output('reminder.pdf', 'I');
$pdf->Output(__DIR__ . '/notification/'.$otp.'reminder.pdf', 'F');

$body = '<html><body><p>Dear Sir / Mam,</p>'
        . '<p>Your Reminder due notification has been sent, please find an attachment.</p>'
        . '<p>Thanks,</p>'
        . '<p>AITF-MCL</p>'
        . '<body></html>';

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

$mailConfigPath = __DIR__ . '/config/mail_config.php';
$mailConfig = file_exists($mailConfigPath) ? require $mailConfigPath : [];
$mailConfig = array_merge(
    [
        'host' => 'smtpout.secureserver.net',
        'port' => 587,
        'secure' => 'tls',
        'username' => 'certification@aitf.co.in',
        'password' => 'AITF999@@HasTag#'
    ],
    is_array($mailConfig) ? $mailConfig : []
);

$email = new PHPMailer;
$email->IsSMTP();
$email->Host = $mailConfig['host'];
$email->Port = $mailConfig['port'];
$email->SMTPAuth = ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== '';
$email->SMTPSecure = $mailConfig['secure'];
$email->Username = $mailConfig['username'];
$email->Password = $mailConfig['password'];
$email->setFrom('aitfintimation@gmail.com');
$email->addReplyTo('aitfintimation@gmail.com');
$email->Subject = '2nd reminder for '.$servicedesc.' - '.$customer['customer_name'];
$email->Body = $body;
$email->isHTML(true);
$email->addCC('aitfmcl@gmail.com');
foreach($contactEmail as $e)
{
    if($e != NULL)
$email->AddAddress($e);
}
//$email->AddBCC('pallavidevane8@gmail.com');

$file_to_attach = __DIR__ . '/notification/'.$otp.'reminder.pdf';

$email->AddAttachment( $file_to_attach , 'Second_reminder.pdf' );

$email->Send();

$mailSend = "UPDATE ca_service_dates SET sent_date = :date, status = 'sent' WHERE  action_id  = :id";
$statement = $db->prepare($mailSend);
$statement->bindParam(":id",$_POST['acction']);
$statement->bindParam(":date", date("Y-m-d"));
$statement->execute();

if(isset($_POST["redirect"]) && !empty($_POST["redirect"])){
    header("Location: customerAccount.php?customerid=".$_POST['customer']);
} else {
header("Location: intimationLetter.php");
}

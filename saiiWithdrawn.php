<?php



/**
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF
{

    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';

    //Page header
    public function Header()
    {
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
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Expiry Of ISO 9001:2008');
$pdf->SetSubject('AITF');
$pdf->SetKeywords('AITF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

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

// database details get and display
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

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

$withdrawndate = date("d-m-Y", strtotime($action1Info['due_date']));

$suspensiondate = date("d-m-Y", strtotime('-3 month', strtotime($withdrawndate)));

$service = "SELECT service_id,service_type FROM service WHERE service_id=:service_id";
$statement = $db->prepare($service);
$statement->bindParam(':service_id', $_POST['service']);
$statement->execute();
$serviceInfo = $statement->fetch();

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

$date1 = date("d-m-Y", strtotime('-2 month', strtotime($withdrawndate)));
$date2 = date("d-m-Y", strtotime('-1 month', strtotime($withdrawndate)));

// ---------------------------------------------------------
// set font
$pdf->SetFont('Times', '', 11);

// add a page
$pdf->AddPage();

//html content Start Here
$html = '<span style="text-align:justify;">'
    . '<pre>Ref: ' . $customer['unique_id'] . '                                         Date: ' . date("d.m.Y", strtotime($withdrawndate)) . '</pre>
    
    <span>TO: </span>
    <br><br>
    <span><b>M/S: ' . $customer['customer_name'] . '</b> </span>
    <br>
   <span>' . $customer['address'] . '</span>
    <br>
    <br>
    <span>Kind Attn:   M/S: ' . $customer['contact_person'] . '</span>
    
    <br>
    <br>
    <span><b>Subject:</b> Expiry of ISO certification</span>
    <br>
    <br>
    <span> Dear Sir/Madam, </span>
    <br>
    <br>
    <span>Please refer our earlier letter dated ' . date("d.m.Y", strtotime($date1)) . ',' . date("d.m.Y", strtotime($date2)) . '.</span>
    <br>
    <br>  
    <span>Since you have failed to complete recertification audit before expiry of certification, Your ISO certificate has expired from Dt. ' . date("d.m.Y", strtotime($withdrawndate)) . '.</span>
     <br>
    <br>  
    <span>Your organization was certified for ISO 9001 and the certification details as per our records are as below:</span>
    <br>
    <br>
    <span>- Certificate no.: ' . $customer['certification_no'] . ' Validity: From Dt. ' . $reg_date . ' To ' . $valid_until . '</span>
    <br>
    <br>
    <span>In case you have already applied or recertification process has initiated by AITF then AITF can restore certification within 6 months provided that the outstanding recertification activities are completed, otherwise at least a stage 2 shall be conducted. If this is not the case then your organization needs to apply for new certification.</span>
    <br>
    <br>
    <span>You are required to restrain from using the logos of certification on any of your promotional material, website, stationary etc.</span>
    <br>
    <br><br><br>
    <span>Please contact us in case you need any clarification.</span>
    <br>
    <br>
    <br><span>Thanks & regards,</span>
    <br><br><br><br><br>
    <br>Certification Manager
    <br>(AITF Management Certification Ltd.)
</font></p><span>';




// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


//Close and output PDF document
$pdf->Output('withdrawn.pdf', 'I');


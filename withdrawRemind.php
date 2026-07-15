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
       // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('withdrawn Of ISO 9001:2008');
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
  $servicedesc = "Recertification audit due";    
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
    <span><b>Subject:</b> Withdrawal Reminder / intimation (ISO 9001)</span>
    <br>
    <br>
    <span>Dear Sir/Mam, </span>
    <br><br>
    <span>We Would like to draw your attention to our previous suspension letter and to remind you that:</span>
    <br><br>
    
    <span>As per procedural requirements and contract agreement, surveillance audit shall be conducted before each 12 months intervals from the date of the certification registration.</span>
    <br>
    <br>
    <span>Your organization has not conducted surveillance within the due date hence certification is now in suspension, if the suspension is not revoked within next 2 months your ISO certificate will be permanently withdrawn.</u></span>
    <br>
    <br><span>Please contact us in case you need any clarification.</span>
    <br>
    <br>
     <br><span>Thanks & regards,</span>
    <br>AITF Management Certification Ltd.
    <br>(Technical Operational Staff)
    <br>( PH - 9422011836 / 37)

    
</font></p></span>';
// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


//Close and output PDF document
$pdf->Output('withdrawRemind.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

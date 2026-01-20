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
        $image_file = K_PATH_IMAGES . 'logo_example.jpg';
        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');
      
        $this->writeHTMLCell($w = '', $h = '', $x = '', $y = '', $this->header, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'L', $autopadding = true);
        $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));

        $this->Line(5, 5, $this->getPageWidth() - 5, 5);

        $this->Line($this->getPageWidth() - 5, 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
        $this->Line(5, $this->getPageHeight() - 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
        $this->Line(5, 5, 5, $this->getPageHeight() - 5);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
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

// ---------------------------------------------------------

$customerinfo = "SELECT * FROM customer_info WHERE id = :id";
$statement = $db->prepare($customerinfo);
$statement->bindParam(':id',$_POST['customer']);
$statement->execute();
$customer = $statement->fetch();

// set font
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

//html content Start Here
$html = '<pre>Ref: AITF-MCl/QMS/SELF/002                                         Date: 01-01-2016</pre>
    <p style="text-align: justify;text-justify: inter-word;"><font size="9">
    <span>TO: </span>
    <br><br>
    <span><b> '.$customer['customer_name'].'</b> </span>
    <br>
   <span>'.$customer['address'].'</span>
    <br>
    <br>
    <span> Kind Attn: Mrs.Shailaja Jagadale(proprietor)</span>
    <br>
    <br>
    <span><b> Subject:</b> First Surillance Audit Due</span>
    <br>
    <br>
    <span> Dear Sir/Madam, </span>
    <br>
   <br><span>1)Client details (as on our records)
         <br>- Certification no.:14 02 Validity: From 22.03.2014 To 21-02-2017
         <br>- Audits done:initial (certification) audit 14-03-2014
         <br>During last assessment of Quality Management System it is observed that number of employee, including contract
         workers of your organization</span>
   <br><span>2)You are required to intimate to us in case of any organization covered or change in the manpower,address,scope
   (reduction or extension sought),or any major changes in the organization since from last audit conducted by AITF-MCL.
   With submission of Information updating form F 7.31</span> 
   <br><span>3)<u>We have earlier sent to you the following documents: </u><br>
             - Copy of the contract signed by you and AITF-MCL unanimously.<br>
             - Report of Last audit along with the nonconformity reports (NCRs), if any, and other encolsures mentioned there in.<br>
             - Guidelines on the use of longos.<br>
             Please inform us in case you have not received any of the above documents.</span>
    <br><span>Please ensure that the above documents are retained by you for future reference.</span> 
    <br><span>4)You are required to continue to maintain the quality management system as per ISO 9001:2006 standard requirements including documentation,records keeping,effective conduct 
    of Internal audits and management reviews as scheduled, monitoring of quality objectives and demonstration of continual improvement,etc.</span>
   <br><span>5)Please ensure that the logos,If used,are asper the guildelines issued by AITF-MCL</span>
   <br><span>6)Your first Surveillance audit falls due on 13-01-2015
         <br>- Kindly let us know  the convenient dates for the audits within 30 days from the due date so 
            can plan the audit accordingly.
          <br>-Please note that the expenses of sureillance audit are 9000+S.tax as per signed agreement
          during initial certification.</span>
    <br><span>7)Failure to successfully completion of surveillance assment as scheduled may call for
                suspension of certification. Ref. Agreeement.</span>      
   <br><br><span>Kindly contact us if any more clarification. or information needed.</span>
    <br>
    <br>
    <br><span>Thanks &regards,</span>
    <br><span>  Sign</span>
    <br>
    <br>Your faithfully,(certification manager)
    <br>AITF Management Certification Ltd.
    
</font></p>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
//$pdf->Output('firstDue.pdf', 'I');
$pdf->Output('firstDue.pdf', 'D');

//============================================================+
// END OF FILE
//============================================================+<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


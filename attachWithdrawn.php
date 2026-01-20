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

// set font
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

//html content Start Here
$html = '<pre>Ref: AITF-MCl/QMS/SELF/002                                         Date: 13-02-2016</pre>
    <p style="text-align: justify;text-justify: inter-word;"><font size="9">
    <span>TO: </span>
    <br><br>
    <span><b> M/S: Soniya Industries</b> </span>
    <br>
   <span> Gate No.165 nandadeep Industrial
   <br>&nbsp;&nbsp; Complex Sahayognagar
   <br>&nbsp;&nbsp; Talawade Pune-411062
   <br>&nbsp;&nbsp; Maharashtra,India</span>
    <br>
    <br>
    <span>Kind Attn: Mrs.Shailaja Jagadale(proprietor)</span>
    
    <br>
    <br>
    <span><b>Subject:</b> withdrawn Of ISO 9001:2008 Certificate</span>
    <br>
    <br>
    <span> Dear Sir/Madam, </span>
    <br>
    <br>
    <span> &ensp;&ensp;&ensp; please refer our earlier latter dated 16-03-2015 suspending your ISO 9001:2008
    certification. Since you have failed to complete surveillance audit within the period specified
    above latter we are forced to withdraw your certification with immediate effect.</span>
    <br>
    <br>  
    <span> &ensp;&ensp;&ensp;You are required to restrain from using the logos of certification body
    of any of your promotional material, website , stationary etc. and return to us certificate and related documents
    issued to earlier.</span>
     <br>
    <br>  
    <span> &ensp;&ensp;&ensp; Please contact us within 15 days in case there is an error in our records or you need any clarification.</span>
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
//$pdf->Output('withdrawn.pdf', 'I');
$pdf->Output('withdrawn.pdf', 'D');

//header("Location: https://mail.google.com/mail/u/0/?tab=wm#inbox?compose=156fe5b386046605");
//============================================================+
// END OF FILE
//============================================================+
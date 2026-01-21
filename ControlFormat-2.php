<?php

// Prevent premature output that breaks PDF headers
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ob_start();
session_start();

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

include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
$control2_client = "";
$control2_ref_no = "";
$control2_address = "";
$control2_date = "";
$review_date = "";
$comments1 = "";
$risks1 = "";
$risk_degree1 = "";
$controls1 = "";
$remarks = "";
$user = "";
$comments2 = "";
$risks2 = "";
$risk_degree2 = "";
$controls2 = "";

$comments3 = "";
$risks3 = "";
$risk_degree3 = "";
$controls3 = "";

$comments4 = "";
$risks4 = "";
$risk_degree4 = "";
$controls4 = "";

$comments5 = "";
$risks5 = "";
$risk_degree5 = "";
$controls5 = "";

$comments6 = "";
$risks6 = "";
$risk_degree6 = "";
$controls6 = "";

$comments7 = "";
$risks7 = "";
$risk_degree7 = "";
$controls7 = "";

if (isset($_POST['control2_id'])) {

    $controlformatii = "SELECT * FROM control_formatii_id WHERE control2_id = :id";
    $statement = $db->prepare($controlformatii);
    $statement->bindParam(':id', $_POST['control2_id']);
    $statement->execute();
    $controlformatiiDetails = $statement->fetch();

    $control2_client = $controlformatiiDetails['control2_client'];
    $control2_ref_no = $controlformatiiDetails['control2_ref_no'];
    $control2_address = $controlformatiiDetails['control2_address'];
    $remarks = $controlformatiiDetails['remarks'];
    $user = $controlformatiiDetails['username'];
    $control2_date = date("d.m.Y", strtotime($controlformatiiDetails['control2_date']));
    if (isset($controlformatiiDetails['review_date']) && !empty($controlformatiiDetails['review_date'])) {
        $review_date = date("d.m.Y", strtotime($controlformatiiDetails['review_date']));
    } else {
        $review_date = " ";
    }
    $formatiiData = "SELECT * FROM  control_formatii WHERE control2_id = :id";
    $statement = $db->prepare($formatiiData);
    $statement->bindParam(':id', $_POST['control2_id']);
    $statement->execute();
    $formatiiDetails = $statement->fetchAll();

    foreach ($formatiiDetails as $value) {

        if ($value['control2_question'] == "Was there any evidence or information to indicate that AITF-MCL has offered or provided consultancy services to the client?") {
            $comments1 = $value['comments2'];
            $risks1 = $value['risks2'];
            $risk_degree1 = $value['risk_degree2'];
            $controls1 = $value['controls2'];
        }
        if ($value['control2_question'] == "Did AITF offer any services other than certification services to the client?") {
            $comments2 = $value['comments2'];
            $risks2 = $value['risks2'];
            $risk_degree2 = $value['risk_degree2'];
            $controls2 = $value['controls2'];
        }
        if ($value['control2_question'] == "Has the auditor signed AITF's policy statement on Confidentiality & Conflict of Interest?") {
            $comments3 = $value['comments2'];
            $risks3 = $value['risks2'];
            $risk_degree3 = $value['risk_degree2'];
            $controls3 = $value['controls2'];
        }
        if ($value['control2_question'] == "Has the auditor signed AITF's Confidentiality & Impartiality Undertaking F 11.03") {
            $comments4 = $value['comments2'];
            $risks4 = $value['risks2'];
            $risk_degree4 = $value['risk_degree2'];
            $controls4 = $value['controls2'];
        }
        if ($value['control2_question'] == "Did the group or individual that took the decision on granting certification understand the applicable standard and certification requirements, and demonstrated competence to evaluate the audit processes and related recommendations of the audit team?") {
            $comments5 = $value['comments2'];
            $risks5 = $value['risks2'];
            $risk_degree5 = $value['risk_degree2'];
            $controls5 = $value['controls2'];
        }
        if ($value['control2_question'] == "Has there been any inference to personnel, either internal or external, or committees, who could influence the certification activities, did not act impartially and/or allowed commercial, financial or other pressures to compromise impartiality?") {
            $comments6 = $value['comments2'];
            $risks6 = $value['risks2'];
            $risk_degree6 = $value['risk_degree2'];
            $controls6 = $value['controls2'];
        }
        if ($value['control2_question'] == "Was/is there any link between the certified client and any member of the Certification decision committee?") {
            $comments7 = $value['comments2'];
            $risks7 = $value['risks2'];
            $risk_degree7 = $value['risk_degree2'];
            $controls7 = $value['controls2'];
        }
    }
}

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';
    protected $count = '1';

    public function setInfo($info) {
        $this->count = $count;
    }

    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        $this->Image($image_file, 17, 5, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 17.5);
        // Title
        $this->SetXY(60, 10);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        //SubTitle
        $this->SetXY(50, 18);
        $this->SetFont('helvetica', 'B', 11);

        $this->Cell(0, 25, 'CONTROL FORMAT-2 (F 12.02) Annex 2 ', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetXY(50, 22);
        $this->SetFont('helvetica', 'B', 11);

        $this->Cell(0, 25, 'CONFLICT OF INTERESTS& IMPARTIALITY', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        //page border
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
        $this->SetFont('helvetica', 'I', 10);
        $this->Cell(0, 0, '', 0, false, 'C', 0, '', 0, false, 'T', 'M');
        // Page number
        $this->Cell(0, 10, 'Page- ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        //page border
        $this->writeHTMLCell($w = '', $h = '', $x = '', $y = '', $this->header, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'L', $autopadding = true);
        $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));

//        $this->Line(5, 5, $this->getPageWidth() - 5, 5);
//
//        $this->Line($this->getPageWidth() - 5, 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, $this->getPageHeight() - 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, 5, 5, $this->getPageHeight() - 5);
    }

}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Control Format 2');
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
$pdf->SetMargins(PDF_MARGIN_LEFT, 22, 5);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 10);

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
$html = '<br><p style="text-align: justify;text-justify: inter-word;"><font size="8"> <table border="1"  cellpadding="4" cellspacing="0" width="100%" >
   
<tr bgcolor="#dcd8c4">
   <td align="center"><font size="10" style="text-align:justify;">In compliance with the requirements of ISO 17021, AITF is 
   performing a review of its management system to address the issue of Conflict of Interest (COI) and analyze any existing or 
   potential risk to impartiality of its activities related to certification.  "Control Points” are established and exercised, for 
   each client, and analysis is done and the results are recorded.
       
       <br>
       <br>If any risk is identified, and, if its degree is “high,” then steps shall be taken to make it low or none.  If any risk is 
       likely to occur frequently, it shall be marked “high”.   If chances of its occurrence are low but probability is less, it shall
       be marked “low”, else, it shall be marked “none”.

        </font>
        </td> 
</tr>

       
</table>

<table border="1"  cellpadding="4" cellspacing="0" width="100%" >

<tr>
<td > Name of client : ' . $control2_client . '</td> 
<td > Certificate No. ' . $control2_ref_no . '</td> 
</tr>

<tr>
<td > Address/Location: ' . $control2_address . '</td> 
<td>Date of CDC: ' . $control2_date . '</td> 
</tr>
</table>

<table border="1"  >
<tr>

<td width="7%" align="center"><br><br>Sr.No.</td> 
<td width="30%" align="center" ><br><br>Question (Risks)</td> 

<td width="63%" >


<table border="1" cellpadding="4" cellspacing="0">

<tr>

<td colspan="4" align="center"><b>CONTROL POINT-2</b> 
<br><b>Member of (SHC)</b>
</td>

</tr>

<tr>

<td rowspan="1" align="center">Comments</td>
<td rowspan="1" align="center">Risk,if any</td>
<td rowspan="1" align="center">Degree of Risk</td>
<td rowspan="1" align="center" >Controls</td>
</tr>
</table>
</td> 
</tr>
</table>
<table border="1"  cellpadding="4" cellspacing="0" width="100%" >
<tr>
<td align="center">
<b>CONSULTANCY</b>
</td>
</tr>
</table>
<table  border="1" cellpadding="4" cellspacing="0" >
<tr>
<td width="7%" align="center"><br><br>1</td> 
<td width="30%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">Was there any evidence or information to indicate that AITF-MCL has offered or provided consultancy services to the client?
</font></p>
</td> 
<td width="15.75%" >
' . $comments1 . '
</td> 
<td width="15.75%" >
' . $risks1 . '
</td> 
<td width="15.75%" >
' . $risk_degree1 . '
</td> 
<td width="15.75%" >
' . $controls1 . '
</td> 
</tr>
<tr>
<td align="center"  width="100%" >
<b>OTHER SERVICES</b>
</td>
</tr>
<tr>
<td width="7%" align="center"><br><br>2</td> 
<td width="30%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">Did AITF-MCL offer any services other than certification services to the client?
</font></p>
</td> 
<td width="15.75%" >
' . $comments2 . '
</td> 
<td width="15.75%" >
' . $risks2 . '
</td> 
<td width="15.75%" >
' . $risk_degree2 . '
</td> 
<td width="15.75%" >
' . $controls2 . '
</td> 
</tr>
<tr>
<td align="center"  width="100%" >
<b>AUDITOR ALLOCATION</b>
</td>
</tr>
<tr>
<td width="7%" align="center"><br><br>3</td> 
<td width="30%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">Has the auditor signed AITF-MCL’s policy statement on Confidentiality & Conflict of Interest? 
</font></p>
</td> 
<td width="15.75%" >
' . $comments3 . '
</td> 
<td width="15.75%" >
' . $risks3 . '
</td> 
<td width="15.75%" >
' . $risk_degree3 . '
</td> 
<td width="15.75%" >
' . $controls3 . '
</td> 
</tr>
<tr>
<td width="7%" align="center"><br><br>4</td> 
<td width="30%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">Has the auditor signed AITF-MCL’s Confidentiality & Impartiality Undertaking                  
F 11.03
services to the client?
</font></p>
</td> 
<td width="15.75%" >
' . $comments4 . '
</td> 
<td width="15.75%" >
' . $risks4 . '
</td> 
<td width="15.75%" >
' . $risk_degree4 . '
</td> 
<td width="15.75%" >
' . $controls4 . '
</td> 
</tr>
</table>
<table border="1"  cellpadding="4" cellspacing="0" width="100%" >
<tr>
<td align="center">
<b>CERTIFICATION DECISION</b>
</td>
</tr>
</table>
<table  border="1" cellpadding="4" cellspacing="0" >
<tr>
<td width="7%" align="center"><br><br>5</td> 
<td width="30%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">Did the group or individual 
that took the decision on granting certification understand the applicable 
standard and certification requirements, and demonstrated competence 
to evaluate the audit processes and related recommendations of the audit team?
</font></p>
</td> 
<td width="15.75%" >
' . $comments5 . '
</td> 
<td width="15.75%" >
' . $risks5 . '
</td> 
<td width="15.75%" >
' . $risk_degree5 . '
</td> 
<td width="15.75%" >
' . $controls5 . '
</td> 
</tr>
</table>
<table border="1"  cellpadding="4" cellspacing="0" width="100%" >
<tr>
<td width="7%" align="center"><br><br>6</td> 
<td width="30%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">Has there been any inference to personnel,
either internal or external, or committees, who could influence the 
certification activities, did not act impartially and/or allowed commercial,
financial or other pressures to compromise impartiality?
</font></p>
</td> 
<td width="15.75%" >
' . $comments6 . '
</td> 
<td width="15.75%" >
' . $risks6 . '
</td> 
<td width="15.75%" >
' . $risk_degree6 . '
</td> 
<td width="15.75%" >
' . $controls6 . '
</td> 
</tr>
<tr>
<td width="7%" align="center"><br><br>7</td> 
<td width="30%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">Was/is there any link between the certified client and any member of the Certification decision committee?
</font></p>
</td> 
<td width="15.75%" >
' . $comments7 . '
</td> 
<td width="15.75%" >
' . $risks7 . '
</td> 
<td width="15.75%" >
' . $risk_degree7 . '
</td> 
<td width="15.75%" >
' . $controls7 . '
</td> 
</tr>
 <tr>   
<td width="100%">
<p><b>REMARKS:</b> '.$remarks.'</p>
<p align="right"><b>Signature & Date: ' . $review_date . '</b><br><b>' . $user . '</b></p>
</td>
</tr>
</table>
</font></p>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
if (ob_get_length()) {
    ob_end_clean();
} else {
    @ob_end_clean();
}
$pdf->Output('CONTROLFORMAT-2.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

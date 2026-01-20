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
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
$control_client = "";
$control_ref_no = "";
$control_address = "";
$control_date = "";
$review_date = "";
$allrow1 = "";
$allrow2 = "";
$allrow3 = "";
$allrow4 = "";
$initial = "";
$recert = "";
$other = "";
$otherText = "";

if (isset($_POST['control_id'])) {

    $controlformati = "SELECT * FROM control_formati_id WHERE control_id = :id";
    $statement = $db->prepare($controlformati);
    $statement->bindParam(':id', $_POST['control_id']);
    $statement->execute();
    $controlformatiDetails = $statement->fetch();

    $control_client = $controlformatiDetails['control_client'];
    $control_ref_no = $controlformatiDetails['control_ref_no'];
    $control_address = $controlformatiDetails['control_address'];
    $control_date = date("d-m-Y", strtotime($controlformatiDetails['control_date']));

    if (isset($controlformatiDetails['review_date']) && !empty($controlformatiDetails['review_date'])) {
        $review_date = date("d-m-Y", strtotime($controlformatiDetails['review_date']));
    } else {
        $review_date = " ";
    }

    $formatiData = "SELECT * FROM  control_formati WHERE control_id = :id";
    $statement = $db->prepare($formatiData);
    $statement->bindParam(':id', $_POST['control_id']);
    $statement->execute();
    $formatiDetails = $statement->fetchAll();

    if ($controlformatiDetails['cert_type'] == "Initial Certification") {
        $initial = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
    } else {
        $initial = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }

    if ($controlformatiDetails['cert_type'] == "Recertification") {
        $recert = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
    } else {
        $recert = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }

    if ($controlformatiDetails['cert_type'] != "Recertification" && $controlformatiDetails['cert_type'] != "Initial Certification") {
        $other = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $otherText = $controlformatiDetails['cert_type'];
    } else {
        $other = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    foreach ($formatiDetails as $value) {

        if ($value['control_title'] == "CONSULTANCY") {

            $allrow1 = $allrow1 . '
<tr>

<td width="3%" align="center">' . $value['control_sr'] . '</td> 
<td width="55%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">' . $value['control_question'] . '
</font></p>
</td> 

<td width="10.5%" >
' . $value['comments'] . '

</td> 
<td width="10.5%" >

' . $value['risks'] . '
</td> 
<td width="10.5%" >
' . $value['risk_degree'] . '
    
</td> 
<td width="10.5%" >
' . $value['controls'] . '

</td> 
</tr>
';
        } elseif ($value['control_title'] == "TRAINING, INTERNAL AUDITS & OTHER SERVICES") {

            $allrow2 = $allrow2 . '
<tr>

<td width="3%" align="center">' . $value['control_sr'] . '</td> 
<td width="55%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">' . $value['control_question'] . '
</font></p>
</td> 

<td width="10.5%" >
' . $value['comments'] . '

</td> 
<td width="10.5%" >

' . $value['risks'] . '
</td> 
<td width="10.5%" >
' . $value['risk_degree'] . '
    
</td> 
<td width="10.5%" >
' . $value['controls'] . '

</td> 
</tr>
';
        } elseif ($value['control_title'] == "AUDITOR SELECTION/ALLOCATION") {

            $allrow3 = $allrow3 . '
<tr>

<td width="3%" align="center">' . $value['control_sr'] . '</td> 
<td width="55%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">' . $value['control_question'] . '
</font></p>
</td> 

<td width="10.5%" >
' . $value['comments'] . '

</td> 
<td width="10.5%" >

' . $value['risks'] . '
</td> 
<td width="10.5%" >
' . $value['risk_degree'] . '
    
</td> 
<td width="10.5%" >
' . $value['controls'] . '

</td> 
</tr>
';
        } elseif ($value['control_title'] == "CERTIFICATION DECISION") {

            $allrow4 = $allrow4 . '
<tr>

<td width="3%" align="center">' . $value['control_sr'] . '</td> 
<td width="55%" align="center" >
<p style="text-align: justify;text-justify: inter-word;"><font size="8">' . $value['control_question'] . '
</font></p>
</td> 

<td width="10.5%" >
' . $value['comments'] . '

</td> 
<td width="10.5%" >

' . $value['risks'] . '
</td> 
<td width="10.5%" >
' . $value['risk_degree'] . '
    
</td> 
<td width="10.5%" >
' . $value['controls'] . '

</td> 
</tr>
';
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

        $this->Cell(0, 25, 'CONTROL FORMAT -1 - CONFLICT OF INTERESTS AND IMPARTIALITY', 0, false, 'C', 0, '', 0, false, 'M', 'M');

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

        $html = '<table border="1"  cellpadding="4" cellspacing="0">';
        $html .= '<tr><td colspan="2">Form No. (F 12.02) Annex 1</td>';
        $html .= '<td colspan="2">Rev.:01, Date: 10.05.2017</td>';
        $html .= '<td colspan="2">Issue.: 01, Date: 10.05.2017</td>';
        $html .= '<td colspan="1">[P a g e ' . $this->count . ' of 1]</td></tr>';
        $html .= '</table>';
        // Position at 15 mm from bottom
        $this->SetY(-10);
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
//        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->writeHTMLCell(0, 0, '', '', $html, 0, 0, false, "L", true);
        $this->count = $this->count + 1;
    }

}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('CONTROL FORMAT -1');
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
$html = ' <table border="1"  cellpadding="1" cellspacing="0" width="100%" >
    
<tr bgcolor="#dcd8c4">
   <td align="center"><font size="9" style="text-align:justify;">In compliance with the requirements of ISO 17021, AITF is performing a review of its certification activities to
                    address the issue of conflict of Interest (COI) and analyze any existing or potential risk to impartiality of its activities.
                "Control Points" are established and analysis is carried out for each client while initial/ Recertification assessment and whenever need arrives.
       <br><br>If any risk is identified, and, if its degree is “high,” then steps shall be taken to make it low or none. If any risk is likely
       to occur frequently, it shall be marked “high”. 
       <br>If ther are chances of occurrence of risk,it shall be marked “low”, <br>else, it shall be marked “none”.
        </font>
        </td> 
</tr>
</table>
<table border="1"  cellpadding="1" cellspacing="0" width="100%" >
<tr>
<td > Name of client : ' . $control_client . '
<br> Certificate No. ' . $control_ref_no . ' 
<br> Date of review: ' . $control_date . '
</td> 
<td > ' . $initial . '  Initial certification 
<br> ' . $recert . '  Recertification
<br> ' . $other . '  Other : <u>' . $otherText . '</u>
</td> 
</tr>
</table>
<table border="1"  >
<tr>
<td width="3%" align="center"><br><br>Sr</td> 
<td width="55%" align="center" ><br><br>Question (Risks)</td> 
<td width="42%" >
<table border="1" cellpadding="4" cellspacing="0">
<tr>
<td rowspan="1" align="center">Comment</td>
<td rowspan="1" align="center">Risk,if any</td>
<td rowspan="1" align="center">Degree of Risk</td>
<td rowspan="1" align="center" >Controls</td>
</tr>
</table>
</td> 
</tr>
</table>
<table border="1"  cellpadding="1" cellspacing="0" width="100%" >
<tr>
<td>
<b>CONSULTANCY</b>
</td>
</tr>
</table>
<table  border="1" cellpadding="1" cellspacing="0" >
' . $allrow1 . '
</table>
<table border="1"  cellpadding="1" cellspacing="0" width="100%" >
<tr>
<td>
<b>TRAINING,INTERNAL AUDITS & OTHER SERVICES</b>
</td>
</tr>
</table>
<table  border="1" cellpadding="1" cellspacing="0" >
' . $allrow2 . '
</table>
<table border="1"  cellpadding="1" cellspacing="0" width="100%" >
<tr>
<td>
<b>AUDITOR SELECTION/ALLOCATION</b>
</td>
</tr>
</table>
<table  border="1" cellpadding="1" cellspacing="0" >
' . $allrow3 . '
</table>
<table border="1"  cellpadding="1" cellspacing="0" width="100%" >
<tr>
<td>
<b>CERTIFICATION DECISION</b>
</td>
</tr>
</table>  
<table  border="1" cellpadding="1" cellspacing="0" >
' . $allrow4 . '
 <tr>   
 <td width="100%">
<b>REMARKS: NO ANY COI OBSERVED</b> <img src="./images/uncheckedCheckbox.png"  width="10" height="10">  
<br>If Yes ?:	
<br><br><br>
<b>  Verified by</b> 
<b>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;
&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;Approved by (MD- AITF)</b> 
</td>
</tr>
</table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

//Close and output PDF document
$pdf->Output('CONTROLFORMAT-1.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

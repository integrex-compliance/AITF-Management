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

$customersurvey = "SELECT * FROM  customer_survey_id WHERE survey_id = :id";
$statement = $db->prepare($customersurvey);
$statement->bindParam(':id', $_POST['survey_id']);
$statement->execute();
$customersurveyDetails = $statement->fetch();

$auditer_org = $customersurveyDetails['auditer_org'];

$survayDetails = 'SELECT * FROM customer_survey WHERE survayid = :survayid';
$statement = $db->prepare($survayDetails);
$statement->bindParam(':survayid', $_POST['survey_id']);
$statement->execute();
$survayInfo = $statement->fetchAll();
if ($customersurveyDetails['initial_cert'] == "Initial Certification(Stage-II)")
    $initialCert = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
else
    $initialCert = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($customersurveyDetails['initial_cert'] == "Recertification")
    $recert = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
else
    $recert = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($customersurveyDetails['initial_cert'] == "Extension")
    $extension = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
else
    $extension = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($customersurveyDetails['initial_cert'] == "Scope")
    $auditScope = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
else
    $auditScope = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($customersurveyDetails['initial_cert'] == "Site")
    $auditSite = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
else
    $auditSite = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($customersurveyDetails['initial_cert'] == "Surveillance")
    $surveillance = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
else
    $surveillance = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

$auditorRow = '';

$yesRow = '';
$noRow = '';

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

        //SubTitle
        $this->SetFont('helvetica', 'B', 10);
        $this->Ln(7);
        $this->Cell(0, 15, 'Review of feedback', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->writeHTMLCell($w = '', $h = '', $x = '', $y = '', $this->header, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'L', $autopadding = true);
        $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));
//
//        $this->Line(5, 5, $this->getPageWidth() - 5, 5);
//
//        $this->Line($this->getPageWidth() - 5, 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, $this->getPageHeight() - 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, 5, 5, $this->getPageHeight() - 5);
    }

    // Page footer
    public function Footer() {

//        $html = '<table  cellpadding="4" cellspacing="0">';
//        $html .= '<tr><td colspan="1">F 9.12</td>';
//        $html .= '<td colspan="2"></td>';
//        $html .= '<td colspan="1"></td>';
//        $html .= '<td colspan="2">Reviewed &amp; Approved By:</td></tr>';
//        $html .= '</table>';
//        // Position at 15 mm from bottom
//        $this->SetY(-15);
//        // Set font
//        $this->SetFont('helvetica', '', 8);
//        // Page number
////        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
//        $this->writeHTMLCell(0, 0, '', '', $html, 0, 0, false, "L", true);
//        $this->count = $this->count + 1;
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Review of client feedback');
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

foreach ($survayInfo as $servay) {
    if ($servay['action'] == 'Yes' && $servay['action'] != '' && $servay['action'] != null) {
        $yesRow = 'Yes';
    } else {
        $yesRow = '';
    }
    if ($servay['action'] == 'No' && $servay['action'] != '' && $servay['action'] != null) {
        $noRow = 'No';
    } else {
        $noRow = '';
    }
    $auditorRow = $auditorRow . '<tr> <td colspan="3" align="center"> ' . $servay['lead_auditor'] . ' </td> <td colspan="1" align="center"> ' . $servay['marks'] . ' </td><td colspan="1" align="center"> ' . $servay['result'] . ' </td> <td colspan="1" align="center"> ' . $servay['action'] . ' </td>  </tr>';
}
// set font
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();


//html content Start Here
$html = '<p style="text-align: justify;text-justify: inter-word;"><font size="8.5">'
        . '<span>Name of Audited Organinsation: ' . $auditer_org . '<span> <br>'
        . '<br>'
        . '<span>Date of survey: ' . date("d.m.Y", strtotime($customersurveyDetails['survey_date'])) . ' <span> <br>'
        . '<br>'
        . '<span>Date(s) of Audit Conducted: ' . $customersurveyDetails['audit_conducted_date'] . ' <span> <br>'
        . '<br>'
        . '<span>Type of audit Imparted: <span><br>'
        . '<br><span>' . $initialCert . '&nbsp;&nbsp;<label for="INITIALCERTIFICATION">INITIAL CERTIFICATION</label>&nbsp;&nbsp;&nbsp;&nbsp;</span>
          <span>' . $recert . '&nbsp;&nbsp;<label for="RECERTIFICATION">RECERTIFICATION</label>&nbsp;&nbsp;&nbsp;&nbsp;</span>
          <span>' . $extension . '&nbsp;&nbsp;<label for="EXTENSION">EXTENSION</label>&nbsp;&nbsp;&nbsp;&nbsp;</span>
          [Special Audit(<span>' . $auditScope . '&nbsp;&nbsp;<label for="SCOPE">SCOPE</label>&nbsp;&nbsp;&nbsp;&nbsp;</span>
          <span>' . $auditSite . '&nbsp;&nbsp;<label for="SITE">SITE</label>)]&nbsp;&nbsp;&nbsp;&nbsp;</span>
          <span>' . $surveillance . '&nbsp;&nbsp;<label for="SURVEILLANCE">SURVEILLANCE</label>&nbsp;&nbsp;&nbsp;&nbsp;</span><br>
<br>
    <table border="1"  cellpadding="4" cellspacing="0" >
<tr  bgcolor="#DCDCDC">
     <td align="center" colspan="6"><b>1.Rating of Auditing Quality</b></td>
</tr>
  <tr>
        <td colspan="6">
        <br>Overall rating of Auditing Quality (A)- ' . $customersurveyDetails['auditing_quality'] . ' in % is = ' . $customersurveyDetails['auditing_percent'] . '
        <br>Result – ' . $customersurveyDetails['auditing_result'] . '
        </td>
</tr>      
 <tr>
        <td colspan="6">
        <br>If Ranking is Partially Satisfied or Dissatisfied,<b>Recommended action to be taken:</b> ' . $customersurveyDetails['audit_ranking_good'] . '
        <br>
        </td>
</tr> 
<tr>
        <td colspan="6">
        <br>If Ranking is not Excellent,<b>Recommended action:</b> ' . $customersurveyDetails['audit_ranking_bad'] . '
        <br>
        </td>
</tr> 
<tr  bgcolor="#DCDCDC">
        <td colspan="6">
        <br><b>2. Rating of Auditor (Individual) Out of 5 Marks.:</b> Excellent (5/5 Marks), Very Satisfactory (4/5 Marks), Satisfied (3/5 Marks), Partially Satisfied (2/5 Marks), Dissatisfied (1/5 Marks).
        <br>
        </td>
</tr> 
 <tr>
        
        <td colspan="3" align="center">Lead Auditor/Auditor/Technical Expert Details</td>
        <td colspan="1" align="center">Mark Obtain</td>
        <td colspan="1" align="center">Result</td>
        <td colspan="1" align="center">Action Recommended <br>(Yes/No)        
        </td>
     
        
</tr>
' . $auditorRow . '
<tr>
        <td colspan="6">
        <br>If Ranking is Partially Satisfied or Dissatisfied,<b>Recommended action to be taken:</b> ' . $customersurveyDetails['individual_ranking'] . '
        <br>
        </td>
</tr> 

<tr  bgcolor="#DCDCDC">
     <td align="center" colspan="6"><b>3.Rating for AITF Management Certification Limited (Including certification division staff &amp; committees)</b></td>
</tr>
  <tr>
        <td colspan="6">
        <br>Overall rating of Satisfaction (B)- ' . $customersurveyDetails['mgmt_satisfaction'] . ' in % is = ' . $customersurveyDetails['mgmt_percent'] . '
        <br>Result – ' . $customersurveyDetails['mgmt_result'] . '
        </td>
</tr>      
 <tr>
        <td colspan="6">
        <br>If Ranking is Partially Satisfied or Dissatisfied,<b>Recommended action to be taken:</b> ' . $customersurveyDetails['mgmt_ranking_good'] . '
        <br>
        </td>
</tr> 
<tr>
        <td colspan="6">
        <br>If Ranking is not Excellent,<b>Recommended action:</b> ' . $customersurveyDetails['mgmt_ranking_bad'] . '
        <br>
        </td>
</tr> 
<tr  bgcolor="#DCDCDC">
     <td colspan="6"><b>*Client Cumulative Satisfaction Rating for assessment Process (i.e. Mean (µ) of A and B) =  ' . $customersurveyDetails['aggregate'] . '</b>
     <br>
     <br><b>*Result- ' . $customersurveyDetails['aggregate_result'] . '</b>

     </td>
</tr>


</table>
<br><br>
    <table border="1"  cellpadding="4" cellspacing="0" >
    <tr>
     <td align="center" colspan="6"><b><u>Chart for Marking</u></b></td>
</tr>
<tr>
        <td colspan="1" align="center">Excellent</td>
        <td colspan="2" align="center">Very Satisfactory   </td>
        <td colspan="1" align="center">Satisfied</td>
        <td colspan="1" align="center">Partially Satisfied </td>
        <td colspan="1" align="center">Dissatisfied </td>
        
</tr> 
<tr>
 <td colspan="1" align="center">5</td>
 <td colspan="2" align="center"> 4 </td>
 <td colspan="1" align="center">3</td>
 <td colspan="1" align="center"> 2 </td>
 <td colspan="1" align="center"> < 2 </td>
</tr> 

<tr>
     <td colspan="6">
     <b>Formula.</b>
    <b>&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;Total of marks obtained</b>    
    <br>
    <b>Customer Satisfaction Index = &ensp;&ensp;---------------------------------------------- X 100</b>    
    <br>
    <b> &ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;5 X No. of question answered</b>
    </td>
</tr>
<tr>
     <td colspan="6">
     Criteria for Result –  <br>
    - If obtain mark is Equal to or more than 90 % = Excellent.<br>
    - Between 80 - 89 % = Very Satisfactory.<br>
    - Between 70-79%= Satisfied- Identify the area of improvement.<br>
    - Between 69-60% = Partially Satisfied-Identification the dissatisfaction area &amp;<br>
      &ensp;&ensp;initiate a plan to eliminate them.<br>
    - Less Than 60% = Dissatisfied- Immediate action to be taken with communication <br>
      &ensp;&ensp;to client &amp; AITF-MCL M.D.
     </td>
</tr>
</table><br><br><br>
<table  cellpadding="4" cellspacing="0">
        <tr><td colspan="1">F 9.12</td>
        <td colspan="2"></td>
        <td colspan="1"></td>
        <td colspan="2">Reviewed &amp; Approved By:</td></tr>
        <tr><td colspan="1"></td>
        <td colspan="2"></td>
        <td colspan="1"></td>
        <td colspan="2">Review Date: '.date("d.m.Y", strtotime($customersurveyDetails["review_date"])).'</td></tr>
        </table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
if (ob_get_length()) {
    ob_end_clean();
} else {
    @ob_end_clean();
}
$pdf->Output('ReviewOfClientFeedback.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
<?php

//============================================================+
// File name   : example_003.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 003 for TCPDF class
//Custom Header and Footer
//
// Author: Nicola Asuni
//
// (c) Copyright:
//Nicola Asuni
//Tecnick.com LTD
//www.tecnick.com
//info@tecnick.com
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

$audit_Organisation = "";
$audit_dates = "";
$lead_auditor = "";
$reference_number = "";
$initial = "";
$recertification = "";
$surveillance = "";
$extension = "";
$scope = "";
$site = "";
$or_data = "";
$accepted = "";
$notAccepted = "";
$reviewer_comments = "";
$reviewDate="";
$reviewerName = "";
$r="";
$rowval = '';
if (isset($_POST['stage_ii_id'])) {

    $auditii = "SELECT * FROM stage_ii_audit_id WHERE stage_ii_id = :id";
    $statement = $db->prepare($auditii);
    $statement->bindParam(':id', $_POST['stage_ii_id']);
    $statement->execute();
    $auditiiDetails = $statement->fetch();
    
    $serviceid = $auditiiDetails['ca_service_id'];

    $auditii = "SELECT * FROM stage_audit_two WHERE service_id = :id";
    $statement = $db->prepare($auditii);
    $statement->bindParam(':id', $serviceid);
    $statement->execute();
    $auditiiDetails1 = $statement->fetchAll();

    $auditii = "SELECT * FROM stage_audit_one WHERE ca_service_id = :id";
    $statement = $db->prepare($auditii);
    $statement->bindParam(':id', $serviceid);
    $statement->execute();
    $auditiiDetails2 = $statement->fetchAll();
    $auditorNames = [];

// Extract auditor names from stage_audit_two
foreach ($auditiiDetails1 as $row) {
    $auditorNames[] = $row['auditor_name'];
}

// Extract auditor names from stage_audit_one
foreach ($auditiiDetails2 as $row) {
    $auditorNames[] = $row['auditor_name'];
}

// Remove duplicates and create comma-separated string
$uniqueAuditorNames = array_unique($auditorNames);
$commaSeparatedAuditorNames = implode(', ', $uniqueAuditorNames);

    $audit_Organisation = $auditiiDetails['audit_Organisation'];
    $reviewerName = $auditiiDetails['ReviewerName'];
    $audit_dates = $auditiiDetails['audit_dates'];
    if (isset($auditiiDetails['review_date']) && !empty($auditiiDetails['review_date'])) {
        $reviewDate = date("d-m-Y", strtotime($auditiiDetails['review_date']));
    } else {
        $reviewDate = "";
    }
    $lead_auditor = $commaSeparatedAuditorNames;
    $reference_number = $auditiiDetails['reference_number'];
    if ($auditiiDetails['remark_check'] == "YES"){
    $r = '<img src="./images/checkboxChecked.png"  width="10" height="10">';    
    } else {
    $r = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } 
    if ($auditiiDetails['initial'] == "Initial Certification(Stage-II)"){
    $initial = '<img src="./images/checkboxChecked.png"  width="10" height="10">';    
    } else {
    $initial = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } 
    if ($auditiiDetails['initial'] == "Recertification"){
    $recertification = '<img src="./images/checkboxChecked.png"  width="10" height="10">';    
    } else {
    $recertification = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    if ($auditiiDetails['initial'] == "Survellance"){
    $surveillance = '<img src="./images/checkboxChecked.png"  width="10" height="10">';    
    } else {
    $surveillance = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    if ($auditiiDetails['initial'] == "Extension"){
    $extension = '<img src="./images/checkboxChecked.png"  width="10" height="10">';    
    } else {
    $extension = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    if ($auditiiDetails['initial'] == "Scope"){
    $scope = '<img src="./images/checkboxChecked.png"  width="10" height="10">';    
    } else {
    $scope = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    if ($auditiiDetails['initial'] == "Site"){
    $site = '<img src="./images/checkboxChecked.png"  width="10" height="10">';    
    } else {
    $site = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    if ($auditiiDetails['initial'] == "Other"){
    $or_data = '<img src="./images/checkboxChecked.png"  width="10" height="10">';    
    } else {
    $or_data = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
        
    if ($auditiiDetails['accepted'] == "Accepted"){
    $accepted = '<img src="./images/checkboxChecked.png"  width="10" height="10">';    
    } else {
    $accepted = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    if ($auditiiDetails['accepted'] == "Not Accepted"){
    $notaccepted = '<img src="./images/checkboxChecked.png"  width="10" height="10">';    
    } else {
    $notaccepted = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    
    $reviewer_comments = $auditiiDetails['reviewer_comments'];
    
    $stageiiData = "SELECT * FROM stage_ii_audit WHERE ca_service_audit_ii = :id";
    $statement = $db->prepare($stageiiData);
    $statement->bindParam(':id', $_POST['stage_ii_id']);
    $statement->execute();
    $stageiiDetails = $statement->fetchAll();
    
    foreach ($stageiiDetails as $value) {
        $rowval = $rowval.'<tr>
        <td style="width:70%;">'.$value['audit_aspect_ii'].'</td>
        <td style="width:15%";> ' . $value['audit_response_ii'] . ' </td>
        <td style="width:15%"> ' . $value['remark_ii'] . '</td>
    </tr>';
    } 
//    echo $audit_response_ii17." -> ".$audit_response_ii18." -> ". $remark_ii18. die;
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

       $this->SetXY(50, 18);
        $this->SetFont('helvetica', 'B', 12);

        $this->Cell(0, 15, 'Annex-2:Review of Audit processes', 0, false, 'C', 0, '', 0, false, 'M', 'M');

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
        $html .= '<tr><td colspan="2">Form No. F 7.32 (Anexture-2) Rev.:03</td>';
        $html .= '<td colspan="2">Date: 01.06.2017</td>';
        $html .= '<td colspan="1">Issue: 01</td>';
        $html .= '<td colspan="2">Date: 01.06.2017</td></tr>';
        $html .= '</table>';
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
//        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->writeHTMLCell(0, 0, '', '', $html, 0, 0, false, "L", true);
        $this->count = $this->count + 1;
    } 
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('F7.32Anex-2');
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
$pdf->SetMargins(PDF_MARGIN_LEFT, 22,5);
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

// create some HTML content
$html = '';
// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// create some HTML content
$html = '<p style="text-align: justify;text-justify: inter-word;"><font size="8">
        <table border="1"  cellpadding="7" cellspacing="0" >
        <tr> 
        <td> Name of Audited Organisation: ' . $audit_Organisation . ' </td>
        <td> Agreement Number: ' . $reference_number . '   </td>
        </tr>
        <tr> 
        <td> Date(s) of Audit Conducted: ' . $audit_dates . '  </td> 
        <td> Lead Auditor: ' . $lead_auditor . '</td>
        </tr>
        <tr> <td style="width:100%;">Type of audit Imparted: 
        
        ' . $initial . '&nbsp;&nbsp;<label for="INITIALCERTIFICATION">INITIAL CERTIFICATION (Stage II)&nbsp;&nbsp;</label> 
        ' . $recertification . '&nbsp;&nbsp;<label for="RECERTIFICATION">RECERTIFICATION&nbsp;&nbsp;</label>
        ' . $surveillance . '&nbsp;&nbsp;<label for="SURVEILLANCE">SURVEILLANCE&nbsp;&nbsp;</label>
        ' . $extension . '&nbsp;&nbsp;<label for="EXTENSION ">EXTENSION [Special Audit(</label>
        ' . $scope . '&nbsp;&nbsp;<label for="SCOPE ">&nbsp;&nbsp;SCOPE &nbsp;&nbsp;</label>
        ' . $site . '&nbsp;&nbsp;<label for="SCOPE ">SITE )],&nbsp;&nbsp;</label>
        ' . $or_data . '&nbsp;&nbsp;<label for="OR ">OR</label>    others_________________ 
        </td> </tr>
         <tr><td style="width:100%;">Reviewer Acceptance: for no Conflict of interest with the above organization: 
         
         ' . $accepted . '&nbsp;&nbsp;<label for="Accepted">Accepted</label>&nbsp;&nbsp;
        ' . $notaccepted . '&nbsp;&nbsp;<label for="NotAccepted">Not Accepted</label> </td></tr>
        </table>
<table border="1"  cellpadding="6" cellspacing="0" >
    <tr bgcolor="#DCDCDC">
        <th align="center" style="width:70%;">ASPECT TO VERIFY</th>
        <th align="center" style="width:15%";>Yes/No/NA</th>
        <th align="center" style="width:15%">REMARK/DETAILS (IF ANY)</th>
       
    </tr>
    
    '.$rowval.'
     <tr>
        <td colspan="3" ><b>Remark: </b>' . $r .' All the audit process of audit and audit report has been verified, The information and evidences provided in the audit report are sufficient for recommendation made by auditor and acceptable to AITF<br>?:--
        <br><br>
       ' . $reviewer_comments . '
        </td></tr>
    <tr>
        <td colspan="3"> <br>      
        <b>DATE OF REVIEW: </b>'.$reviewDate.'
        <br>
        <br>
       <b>REVIEWER NAME :</b> '.$reviewerName.'
       <br></td>
     </tr>
    </table>
    </font></p>';
// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
$pdf->Output('F7.32Anex-2.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
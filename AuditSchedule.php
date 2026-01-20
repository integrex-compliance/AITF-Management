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
// code by Pallavi to display pdf

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$stageISchedule = "SELECT * FROM audit_schedule_id WHERE audit_schedule_id = :scheduleid and serviceca_id = :service and customer_ca_id = :customer ";
$statement = $db->prepare($stageISchedule);
$statement->bindParam(':scheduleid', $_POST['auditscheduleid']);
$statement->bindParam(':service', $_POST['serviceid']);
$statement->bindParam(':customer', $_POST['customerid']);
$statement->execute();
$stageIInfo = $statement->fetch();

$formNo = $stageIInfo['stage_i_form'];
$rev_date = $stageIInfo['stage_i_date'];
$rev = $stageIInfo['stage_i_rev'];
$isue = $stageIInfo['stage_i_isue'];
$isuedate = $stageIInfo['stage_i_isuedate'];

$stageI_info = "SELECT * FROM audit_schedule WHERE audit_scheduleid = :schedule and schedule_service_id = :service ";
$statement = $db->prepare($stageI_info);
$statement->bindParam(':schedule', $_POST['auditscheduleid']);
$statement->bindParam(':service', $_POST['serviceid']);
$statement->execute();
$stageIDetails = $statement->fetchAll();

$auditid = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :service ";
$statement = $db->prepare($auditid);
$statement->bindParam(':service', $_POST['serviceid']);
$statement->execute();
$auditInfo = $statement->fetch();

    $auditii = "SELECT * FROM stage_audit_one WHERE ca_service_id = :id";
    $statement = $db->prepare($auditii);
    $statement->bindParam(':id', $_POST['serviceid']);
    $statement->execute();
    $auditiiDetails2 = $statement->fetchAll();
    $auditorNames = [];

// Extract auditor names from stage_audit_two
foreach ($auditiiDetails2 as $row) {
    $auditorNames[] = $row['auditor_name'];
}


// Remove duplicates and create comma-separated string
$uniqueAuditorNames = array_unique($auditorNames);
$commaSeparatedAuditorNames = implode(', ', $uniqueAuditorNames);


$iafcode = $stageIInfo['schedule_aifcode'];
    $iafcode_array = explode(",", $iafcode);
    $nacecode = $stageIInfo['schedule_nacfcode'];
    $nacecode_array = explode(",", $nacecode);
    $naceclass = $stageIInfo['schedule_naceclass'];
    $naceclass_array = explode(",", $naceclass);
    $combined_strings = [];
    for ($i = 0; $i < count($iafcode_array); $i++) {
    $combined_strings[] = $iafcode_array[$i] . "/" . $nacecode_array[$i] . "/" . $naceclass_array[$i];
    }
    $combined_string_str = implode(', ', $combined_strings);
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
        $this->Cell(0, 25, 'AUDIT PLAN - STAGE 1', 0, false, 'C', 0, '', 0, false, 'M', 'M');


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
        $html .= '<tr><td colspan="1">Form No. F 7.07 </td>';
        $html .= '<td colspan="2">Rev.: 02, Date: 01.12.2016</td>';
        $html .= '<td colspan="2">Issue.: 01, Date: 01.12.2016</td>';
        $html .= '<td colspan="1">[P a g e ' . $this->count . ' of '. $this->getAliasNbPages() .']</td></tr>';
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

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Audit Schedule');
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
$pdf->SetMargins(17, 22, 5);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 7);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}
$teamauditor = explode(',', $stageIInfo['schedule_auditors']);
// ---------------------------------------------------------
$stageI = "";
//foreach ($stageIDetails as $stagei) {
//    print_r($stagei);
//    if($stagei[$i] == 4){
//    $stageI = $stageI . '<tr><td colspan="1" align="center"><font size="10"><font color="black">' . $stagei['schedule_dates'] . '</font></font></td><td colspan="1" align="center"><font size="10"><font color="black">' . $stagei['schedule_time'] . '</font></font></td><td colspan="4"><font size="9"><font size="5"></font> ' . $stagei['schedule_activity'] . ' </font></td><td colspan="2" align="center"><font size="10"><font color="black">' . $stagei['schedule_auditors'] . '</font></font></td></tr>';
//    }
//} 
for ($i = 0; $i < count($stageIDetails); ++$i) {
    $stageI = $stageI .'<tr><td colspan="1"  cellpadding="3" align="center" ><font size="10"><font color="black">' . $stageIDetails[$i]['schedule_dates'] . '</font></font></td><td colspan="1" align="center"><font size="10"><font color="black">' . $stageIDetails[$i]['schedule_time'] . '</font></font></td><td colspan="6"><font size="9.5"> ' . $stageIDetails[$i]['schedule_activity'] . ' </font></td><td colspan="1" align="center"><font size="10"><font color="black">' . $stageIDetails[$i]['schedule_auditors'] . '</font></font></td></tr>';
}
//die;
// set font
$pdf->SetFont('Times', '', 11);

// add a page

$pdf->AddPage();
$lp=0;

$space = '/';
//html content Start Here
$html = '<table border="1"  cellpadding="3" cellspacing="0">
<tr>
    <td colspan="4" >
        <font size="10">Client Name: <font color="black">' . $stageIInfo['client_name'] . '</font></font>
    </td>
    <td colspan="4" >
        <font size="10">Agreement No:<font color="black">' . $stageIInfo['ref_no'] . '</font></font>
    </td>
</tr>
<tr>
    <td colspan="8" >
        <font size="10">Address: <font color="black">' . $stageIInfo['schedule_address'] . '</font></font>
    </td>
</tr>
<tr>
    <td colspan="8" >
        <font size="10">Contact Person: <font color="black">' . $stageIInfo['schedule_contact_person'] . '</font></font>
    </td>
</tr>
<tr>
    <td colspan="8" >
        <font size="10">Scope applied by client: <font color="black">' . $stageIInfo['schedule_scope'] . '</font></font>
    </td>
</tr>
    <tr>
<td colspan="4" >
        <font size="10">Applicable Standard: <font color="black">' . $stageIInfo['schedule_app_standard'] . '</font></font>
    </td>
    <td colspan="4" >
        <font size="10">Technical Area : <font color="black">' . $naceclass . '</font></font>
    </td>
    </tr>
    <tr>
<td colspan="4" >
        <font size="10">Audit Type: <font color="black">' . $stageIInfo['schedule_audit_type'] . '</font></font>
    </td>
    <td colspan="4">
        <font size="10">No. of Mandays: <font color="black">' . $stageIInfo['schedule_mandays'] . '</font></font>
    </td>
    </tr>
    <tr>
<td colspan="4">
        <font size="10">Audit Date: <font color="black">' . date("d.m.Y", strtotime($stageIInfo['schedule_audit_date'])) . '</font></font>
    </td>
    <td colspan="4" >
        <font size="10">Auditor/s: <font color="black">' . $commaSeparatedAuditorNames . '</font></font>
    </td>
    </tr>
</table><br>
<table border="1"  cellpadding="3" cellspacing="0">
<tr>
    <td colspan="1" align="center">Date(s)</td>
    <td colspan="1" align="center">Time </td>
    <td colspan="6" align="center">Activity </td>
    <td colspan="1" align="center">Auditor Abb. </td>
</tr>
' . $stageI . '
    <tr><td colspan="8" align="center"><font size="10"><font color="black">'.$stageIInfo["break_from"].' To '.$stageIInfo["break_to"].' Lunch</font></font></td></tr>
    </table>

<p style="text-align: justify; text-justify: inter-word;"><font size="9">
<u><b>Objective of the visit:</b></u>
<br>&emsp;&emsp;&emsp;1.To review the client'."'".'s management system documented information;
<br>&emsp;&emsp;&emsp;2.To evaluate the client'."'".'s site-specific conditions and to undertake discussions with the client'."'".'s personnel to determine the preparedness for the stage 2;
<br>&emsp;&emsp;&emsp;3.To review the client'."'".'s status and understanding regarding requirements of the standard, in particular with respect to the identification of key performance or significant aspects, processes, objectives and operation of the management system; 
<br>&emsp;&emsp;&emsp;4.To obtain necessary information regarding the scope of the QMS that information including the client site/s, process and equipment used, levels of controls established (particularly in case of multisite clients), and applicable statutory and regulatory requirements (e.g. quality, environmental, legal aspects of the client'."'".'s operation, associated risks, etc.);
<br>&emsp;&emsp;&emsp;5.To review the allocation of resources for stage 2 audit and agree the details of the stage 2 with client; 
<br>&emsp;&emsp;&emsp;6.To provide a focus for planning the stage 2 audit by gaining a sufficient understanding of the client'."'".'s management system and site operations in the context of management system standard or other normative documents;
<br>&emsp;&emsp;&emsp;7.To evaluate if the internal audits and management review are being planned and performed, and that the level of implementation of the management system substantiates that the client is ready for the stage 2.
</font></p>
<br>
<br>
<span align="left">Team Leader: <font size="10">' . $teamauditor[0] . '</font>  <span>
<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Date: '.  date("d.m.Y", strtotime($auditInfo['intimation_date'])) .'</span>
</font>';

$lp=$pdf->PageNo();

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//html content Start Here
// output the HTML content
//$pdf->writeHTML($html, true, false, true, false, '');
//Close and output PDF document
$pdf->Output('AuditSchedule.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

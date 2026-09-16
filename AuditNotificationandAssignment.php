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
require_once(__DIR__ . '/tcpdf_include.php');
// code by Pallavi to display pdf
include_once __DIR__ . '/dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
$auditDate = "";
$lastDate = "";
$reqAuditId = isset($_POST['auditid']) ? $_POST['auditid'] : '';
$reqCustomerId = isset($_POST['customerid']) ? $_POST['customerid'] : (isset($_POST['customer']) ? $_POST['customer'] : '');
$reqServiceId = isset($_POST['serviceid']) ? $_POST['serviceid'] : (isset($_POST['service']) ? $_POST['service'] : '');

$auditInfo = false;
if (!empty($reqAuditId) && !empty($reqCustomerId) && !empty($reqServiceId)) {
    $auditidQuery = "SELECT * FROM stage_oneaudit_id WHERE audit_stage_id = :audit and ca_customer_id = :customer and ca_service_id = :service ";
    $statement = $db->prepare($auditidQuery);
    $statement->bindParam(':audit', $reqAuditId);
    $statement->bindParam(':customer', $reqCustomerId);
    $statement->bindParam(':service', $reqServiceId);
    $statement->execute();
    $auditInfo = $statement->fetch();
}

if (!$auditInfo && !empty($reqCustomerId)) {
    $fallbackQuery = "SELECT * FROM stage_oneaudit_id WHERE ca_customer_id = :customer ORDER BY audit_stage_id DESC LIMIT 1";
    $statement = $db->prepare($fallbackQuery);
    $statement->bindParam(':customer', $reqCustomerId);
    $statement->execute();
    $auditInfo = $statement->fetch();
    if ($auditInfo) {
        $reqAuditId = $auditInfo['audit_stage_id'];
        $reqServiceId = $auditInfo['ca_service_id'];
    }
}

$auditDates = explode(',',$auditInfo['audit_dates']);
foreach($auditDates as $dates){
   $lastDate =  $dates;
}

$auditDetails = "SELECT * FROM stage_audit_one WHERE audit_stage_id = :auditid and ca_service_id = :service ";
$statement = $db->prepare($auditDetails);
$statement->bindParam(':auditid', $reqAuditId);
$statement->bindParam(':service', $reqServiceId);
$statement->execute();
$auditInformation = $statement->fetchAll();

if ($auditInfo['auditor_check_last_two'] == 'YES')
    $tllasty = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
//if($auditInfo['stage_i_audit'] == 'false')
else
    $tllasty = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($auditInfo['auditor_check_last_two'] == 'NO')
    $tllastn = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
//if($auditInfo['stage_i_audit'] == 'false')
else
    $tllastn = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($auditInfo['special_audit'] == 'Stage-i')
    $suditStagei = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
//if($auditInfo['stage_i_audit'] == 'false')
else
    $suditStagei = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($auditInfo['special_audit'] == 'Stage-ii')
    $suditStageii = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
//if($auditInfo['stage_ii_audit'] == 'false')
else
    $suditStageii = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($auditInfo['special_audit'] == 'Surveillance')
    $auditsurveillance = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
//if($auditInfo['stage_ii_audit'] == 'false')
else
    $auditsurveillance = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($auditInfo['special_audit'] == 'Re-Certification Audit')
    $auditrecertification = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
//if($auditInfo['stage_ii_audit'] == 'false')
else
    $auditrecertification = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($auditInfo['special_audit'] == 'Follow-up Audit')
    $followupaudit = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
//if($auditInfo['stage_ii_audit'] == 'false')
else
    $followupaudit = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

if ($auditInfo['special_audit'] == 'Special Audit')
    $specialaudit = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
//if($auditInfo['stage_ii_audit'] == 'false')
else
    $specialaudit = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';

// Extend the TCPDF class to create custom Header and Footer
if (!class_exists('MYPDF_AuditNotificationStage1', false)) {
    class MYPDF_AuditNotificationStage1 extends TCPDF {

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
            $this->Image($image_file, 17, 5, 43, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            // Set font
           $this->SetFont('helvetica', 'B', 17.5);
            // Title
            $this->SetXY(60, 10);
            $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

            //SubTitle

           $this->SetXY(60, 18);
            $this->SetFont('helvetica', 'B', 12);
            $this->Cell(0, 25, 'AUDIT NOTIFICATION WITH ASSIGNMENT & DECLARATION', 0, false, 'C', 0, '', 0, false, 'M', 'M');


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
            $html .= '<tr><td colspan="1">Form No. F 7.06</td>';
            $html .= '<td colspan="2">Rev.:02, Date: 10.05.2017</td>';
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
}

// create new PDF document
$pdf = new MYPDF_AuditNotificationStage1(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Audit Notification and Assignment');
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
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

$auditRow = '';
foreach ($auditInformation as $audit_info) {
    $auditRow = $auditRow . '<tr><td style="width:20%;">' . $audit_info['auditor_name'] . '</td><td style="width:20%";>' . $audit_info['auditor_category'] . '</td><td style="width:20%">' . $audit_info['audit_contact'] . '</td><td style="width:20%">' . $audit_info['audit_wokrdays'] . '</td><td style="width:20%" bgcolor="#f2dbdb">' . $audit_info['auditor_acceptance'] . '</td></tr>';
}

// set font
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

// set some text to print
$txt = <<<EOD

EOD;

// print a block of text using Write()
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);

// create some HTML content
$html = '<table border="1"  cellpadding="4" cellspacing="0" >
<tr>
        <td>
        <br>
        <b>Subject:</b> ' . $auditInfo['audit_subject'] . '
        </td>
</tr>
<tr>
        <td style="width:50%;">
        <br>
       <b>For the Organisation:</b> ' . $auditInfo['for_org'] . '
        </td>
        <td style="width:50%;">
        <br>
        <b>Date of Intimation :</b> ' . date("d.m.Y", strtotime($auditInfo['intimation_date'])) . ' </td>
</tr>
<tr>
        <td style="width:50%;">
        <br>
       <b>Site (S) :</b> ' . $auditInfo['location'] . '
       <br>
       <br>
        </td>
        <td style="width:50%;">
        <br>
        <b>Agreement No.: </b> ' . $auditInfo['audit_ref_no'] . '
        <br>
        <br>
        <hr style="width:102%;">
       <b> For the standard :</b> 
       <span><img src="./images/checkboxChecked.png"  width="10" height="10"> ' . $auditInfo['audit_standard'] . '</span>
    </td>
</tr>
<tr>
        <td style="width:50%;">
        <br>
       <b>Contact Person Name:</b> ' . $auditInfo['audit_contact_person'] . '
        <br>
        <br>
        <b>Designation: </b> ' . $auditInfo['audit_designation'] . '
        <br>
        <br>
        <b>Telephone/Mobile: </b> ' . $auditInfo['audit_telephone'] . '
        <br>
        <br>
        <b>E-mail: </b> ' . $auditInfo['audit_email'] . '
        <br>
        </td>
        <td style="width:50%;">
        <br>
        <b>Audit date(s):</b> ' .$auditInfo['audit_dates'] . '
        <br>
        <br>
        <br>
        <br>
        <hr style="width:102%;">
        <span>' . $suditStagei . '&nbsp;&nbsp;<label for="Stage-I">Stage-I Audit</label>&nbsp;&nbsp;&nbsp;&nbsp;</span>
        <span>' . $suditStageii . '&nbsp;&nbsp;<label for="Stage-II">Stage-II Audit</label>&nbsp;&nbsp;&nbsp;&nbsp;</span>
        <hr style="width:102%;">
        <span>' . $auditsurveillance . '&nbsp;&nbsp;<label for="Surveillance">Surveillance</label>&nbsp;&nbsp;&nbsp;&nbsp;</span>
        <span>' . $auditrecertification . '&nbsp;&nbsp;<label for="Re-CertificationAudit">Re-Certification Audit</label>&nbsp;&nbsp;&nbsp;&nbsp;</span>
        <hr style="width:102%;">
        <span>' . $followupaudit . '&nbsp;&nbsp;<label for="Follow-up">Follow-up Audit</label>&nbsp;&nbsp;&nbsp;&nbsp;</span>
        <span>' . $specialaudit . '&nbsp;&nbsp;<label for="SpecialAudit">Special Audit</label>&nbsp;&nbsp;&nbsp;&nbsp;</span>
        </td>
</tr>
<tr>
        <td style="width:100%;" bgcolor="#f2dbdb">
        <br>
       <b>DECLARATION FOR <u>IMPARTIALITY</u> AND<u> CONFIDENTIALITY</u> BY AITF AUDIT TEAM MEMBER/S:<br>
       </b>
       <br>
       I undersign declare that I have no any linkage or I am not related to the consultancy organization, Neither 
       I nor my employers have provided  any consulting  and / or internal  audit  services  to the  auditee  organization
       nor  I have  been its employee within the prior  2 years.  I have no commercial and / or financial interest in the 
       auditee organization. I will not provide any such service during validity of above mention organisations certificate
       or assessment service.
       <br>
        Also I declare that, the Information arising from contact, visit or audit will not be disclosed to any third party 
        except where the auditee has agreed and when it is required by the accreditation authority.
       </td>
</tr>
<tr>
        <th align="center" style="width:20%;"><b>Auditor Name</b></th>
        <th align="center" style="width:20%";><b>Category of Auditor</b></th>
        <th align="center" style="width:20%"><b>Contact Number</b></th>
        <th align="center" style="width:20%"><b>Auditor Work Days</b></th>
        <th align="center" style="width:20%" bgcolor="#f2dbdb"><b>Auditor Acceptance</b></th>
</tr>
      ' . $auditRow . '  
<tr>
        <th style="width:100%;">Whether same team leader was appointed for last two consequence audits <font size="12">(Yes
        '. $tllasty . '  / No '. $tllastn .').</font>
        <br><b>If Yes ?: '. $auditInfo['auditor_check_comm'] .'</b>
        </th>
</tr>
<tr>
        <th style="width:100%;">&nbsp;*Auditor nomination is subject to any objections that may be raised by the client. If there is any 
        <br>
        objection to the individual(s) assigned to the Audit, the client has to be communicate to the
        <br>
        AITF-MCL including the justification for such un-acceptance, The same will be considered on 
        <br>
        merits.
        </th>
</tr>
<tr>
        <th  style="width:60%;" bgcolor="#DCDCDC"><b>Client Acceptance:</b>
        <hr style="width:102%;" bgcolor="#DCDCDC">
        Name: 
        <br>
        Designation:
        <br>
        <br>
        <br>
        Client Signature:
        </th>
        <th  style="width:40%";><b>AITF MGMT. CERT. LTD.</b>
       
        <hr style="width:103%;">
        Name: '.$auditInfo['appointed_by'].'
                <br>
        <br>
        <br>
        AITF-MCL Signature:
        <br>
        Designation: '.$auditInfo['mcl_designation'].'
        </th>
</tr>
<tr>
        <td style="width:60%;" bgcolor="#DCDCDC"><br>
            If any change required please describe with Justification:
            <br>
            <br>
            <br>
            <br>
            <br>
        </td>
        <td style="width:40%;"><br>
            <b>Re-approval-AITF–MCL.</b>
        </td>
</tr>
</table>
';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
if (ob_get_length()) {
    ob_end_clean();
} else {
    @ob_end_clean();
}
$outputMode = (isset($_POST['output_mode']) && $_POST['output_mode'] === 'S') ? 'S' : 'I';
$pdfOutput = $pdf->Output('AuditNotificationandAssignment.pdf', $outputMode);

if ($outputMode === 'S') {
    return $pdfOutput;
}

//============================================================+
// END OF FILE
//============================================================+

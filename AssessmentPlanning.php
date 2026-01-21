<?php

// Prevent premature output that breaks PDF headers
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ob_start();
// Start session FIRST before any output
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
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

// Check if assessmentId is provided
if (!isset($_POST['assessmentId']) || empty($_POST['assessmentId'])) {
    die('Assessment ID is required');
}

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

$assessmentDetails = "SELECT * FROM assessment_planning WHERE assessment_id = :id";
$statement = $db->prepare($assessmentDetails);
$statement->bindParam(':id', $_POST['assessmentId']);
$statement->execute();
$assessmentInfo = $statement->fetch();

// Check if assessment record was found
if ($assessmentInfo === false) {
    die('Assessment Planning record not found for ID: ' . htmlspecialchars($_POST['assessmentId']));
}

$scope = "";
$products = "";
$customer_base = "";
$organization = "";

if ($assessmentInfo['modification'] == "Scope") {
    $scope = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
} else {
    $scope = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
}
if ($assessmentInfo['modification'] == "Products") {
    $products = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
} else {
    $products = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
}
if ($assessmentInfo['modification'] == "Customer base") {
    $customer_base = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
} else {
    $customer_base = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
}
if ($assessmentInfo['modification'] == "Organization") {
    $organization = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
} else {
    $organization = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
}

$clientYes = "";
$clientNo = "";

if ($assessmentInfo['client_instructions'] == "Yes") {
    $clientYes = "√";
} else if ($assessmentInfo['client_instructions'] == "No") {
    $clientNo = "√";
}

if ($assessmentInfo['lead_auditor_code'] == "Lead Auditor") {
    $leadAuditor = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
} else {
    $leadAuditor = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
}

if ($assessmentInfo['code_auditor'] == "Auditors") {
    $auditor = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
} else {
    $auditor = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
}
$validityofCertificate = "";
$certifiedpreviously = "";
if (isset($assessmentInfo['cert_validatity']) && !empty($assessmentInfo['cert_validatity'])) {

    $validityofCertificate = date("d.m.Y", strtotime($assessmentInfo['cert_validatity']));
    $certifiedpreviously = "Yes";
} elseif ($assessmentInfo['cert_validatity'] == NULL) {
    $validityofCertificate = "";
    $certifiedpreviously = "No";
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

        $this->Cell(0, 25, 'ASSESSMENT PLANNING', 0, false, 'C', 0, '', 0, false, 'M', 'M');

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
        $html .= '<tr><td colspan="1">Form No.F 7.05</td>';
        $html .= '<td colspan="2">Rev.:01, Date: 11.04.2016</td>';
        $html .= '<td colspan="2">Issue.: 01, Date: 11.04.2016</td>';
        $html .= '<td colspan="1">[P a g e ' . $this->count . ' of 1]</td></tr>';
        $html .= '</table>';
        // Position at 15 mm from bottom
        $this->SetY(-8);
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
$pdf->SetTitle('Assessment Planning');
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
$pdf->SetAutoPageBreak(TRUE, 9);

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
$html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9">
            <table border="1"  cellpadding="4" cellspacing="0" >
                <tr bgcolor="#DCDCDC">
                    <td align="" colspan="9">Applicant/Client Name: ' . $assessmentInfo['app_client_name'] . '</td>
                    <td align="" colspan="6"><b>Agreement No.:</b><b>'. $assessmentInfo['app_client_ref'] . '</b></td>
                </tr>
                <tr>
                    <td align="" colspan="15">Site (S): ' . $assessmentInfo['location'] . '</td>
                </tr>
                <tr>
                    <td align="" colspan="15">Business /Operations (Scope of Certification):' . $assessmentInfo['business'] . '<br></td>
                </tr>
                <tr>
                    <td align="" colspan="11">Certification required for standard: ' . $assessmentInfo['cert_standard'] . '</td>
                    <td align="" colspan="4">Technical Area : '. $assessmentInfo['nace_class'] . '</td>
                </tr>
                <tr>
                    <td align="" colspan="15">Are the certification requirements clearly defined, documented and understood by Applicant: ' . $assessmentInfo['defined_cert_req'] . '<br></td>
                </tr>
                <tr>
                    <td align="" colspan="15">Any difference in Applicant’s understanding pending resolution: ' . $assessmentInfo['app_standard_diff'] . '<br></td>
                </tr>
                <tr>
                    <td align="" colspan="15">Special  aspects, such  as,  language,  if  any : ' . $assessmentInfo['special_aspects'] . '<br></td>
                </tr>
            </table>
            <br><br>
            <table border="1"  cellpadding="4" cellspacing="0" >
                <tr bgcolor="#DCDCDC">
                    <td align="" colspan="15"><b>Applicant certified</b></td>
                </tr>
                <tr>
                    <td align="" colspan="7">Certification Body : ' . $assessmentInfo['certification_body'] . '<br></td>
                    <td align="" colspan="4">Technical area: ' . $assessmentInfo['aif_scope'] . '<br></td>
                    <td align="" colspan="4">Validity of Certificate :' . $validityofCertificate . '<br></td>
                </tr>
                <tr>
                    <td align="" colspan="15">Reasons If delay in renewal : ' . $assessmentInfo['renewal_delay'] . '</td>
                </tr>
                <tr>
                    <td align="" colspan="15">Change / Modification :
                        &nbsp;&nbsp;&nbsp;&nbsp;<label for="Scope">Scope</label>&nbsp; ' . $scope . '
                        &nbsp;&nbsp;&nbsp;&nbsp;<label for="Products">Products</label>&nbsp; ' . $products . '
                        &nbsp;&nbsp;&nbsp;&nbsp;<label for="Customer">Customer base</label>&nbsp; ' . $customer_base . '
                        &nbsp;&nbsp;&nbsp;&nbsp;<label for="Organization">Organization</label>&nbsp; ' . $organization . '
                        <br><br>Reasons: 
                    </td>
                </tr>
            </table>
            <br><br>
            <table border="1"  cellpadding="4" cellspacing="0" >
                <tr bgcolor="#DCDCDC">
                    <td align="" colspan="15"><b> AITF–MCL Capability Assessment</b></td>
                </tr>
                <tr>
                    <td colspan="15">Availability of QMS Scope : ' . $assessmentInfo['qms_scope'] . '</td>
                </tr>
                <tr>
                    <td colspan="15">Availability of Assessment Personnel : ' . $assessmentInfo['assessment_personnel'] . '</td>
                </tr>
                <tr>
                    <td colspan="15">Need for Technical Expert : ' . $assessmentInfo['need_technical_expert'] . '<br><br>Availability: ' . $assessmentInfo['availability'] . '</td>
                </tr>
                <tr>
                    <td colspan="15">Logistic arrangement : ' . $assessmentInfo['logistic_arrangement'] . '  </td>
                </tr>
                <tr>
                    <td colspan="15">Any conflict of interest : ' . $assessmentInfo['conflict_interest'] . ' </td>
                </tr>
                <tr bgcolor="#DCDCDC">
                    <td align="" colspan="15"><b> Implementation status of the Applicant -</b></td>
                </tr>
                <tr>
                    <td colspan="15"><b>- Documentation -</b>
                        <br><br>Date of QMS Implementation : ' . date("d.m.Y", strtotime($assessmentInfo['qms_implementation_date'])) . '<br>
                        <br>Documented Information Sent by client? 
                        <span><label for="Yes">Yes</label> ' . $clientYes . '</span>
                        <span><label for="No">No</label> ' . $clientNo . '</span>
                        <br>Comments : 
                        <span>' . $assessmentInfo['assessment_comment'] . '</span>
                    </td>
                </tr>
            </table>
            <br><br>
            <table border="1"  cellpadding="4" cellspacing="0" >
                <tr bgcolor="#DCDCDC">
                    <td align="" colspan="15"><b> Tentative Assessment Plan</b></td>
                </tr>
                <tr>
                    <td colspan="9">Effective No. of Employees: ' . $assessmentInfo['no_of_employes'] . '</td>
                    <td colspan="6">Auditor Man days: ' . $assessmentInfo['auditor_man_days'] . '</td>
                </tr>
            </table>
            <table border="1"  cellpadding="4" cellspacing="0" >
                <tr>
                    <td rowspan="3" style="width:20%;">● Proposed Audit Team</td>
                    <td style="width:40%;">Lead Auditor: ' . $assessmentInfo['lead_auditor'] . ' </td>
                    <td style="width:40%;"><label for="CodeAuditor">Code Auditor</label> ' . $leadAuditor . '</td>
                </tr>
                <tr>
                    <td colspan="2">Technical  Expert: ' . $assessmentInfo['technical_expert'] . '</td>
                </tr>
                <tr>
                    <td>Auditors: ' . $assessmentInfo['auditors'] . '</td>
                    <td><label for="CodeAuditor">Code Auditor</label> ' . $auditor . ' </td>
                </tr>
            </table> 
            <table border="1"  cellpadding="4" cellspacing="0" >
                <tr>
                    <td rowspan="3" style="width:20%;">● Proposed Audit Plan</td>
                    <td><b>Activity</b></td>
                    <td><b>Proposed Date</b></td>
                    <td><b>Activity</b></td>
                    <td><b>Proposed Date</b></td>
                </tr>
                <tr>
                    <td>Document Review</td>
                    <td> ' . $assessmentInfo['document_review_date'] . ' </td>
                    <td>Stage-2 Audit</td>
                    <td> ' . $assessmentInfo['stage_2_audit_date'] . ' </td>
                </tr>
                <tr>
                    <td>Stage I</td>
                    <td>' . $assessmentInfo['stage_1_date'] . ' </td>
                    <td>If Recertification</td>
                    <td>' . $assessmentInfo['if_recertification_date'] . ' </td>
                </tr>
            </table>
            <table border="1"  cellpadding="4" cellspacing="0" >
                <tr>
                    <td> If Stage- I Audit not recommended Reason: ' . $assessmentInfo['recommended_reason'] . '</td>
                </tr>
            </table> 
            <table border="1"  cellpadding="4" cellspacing="0" >
                <tr>
                    <td> Date: ' . date("d.m.Y", strtotime($assessmentInfo['date'])) . '<br></td>
                    <td> Verified by: ' . $assessmentInfo['verified_by'] . '</td>
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
$pdf->Output('AssessmentPlanning.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
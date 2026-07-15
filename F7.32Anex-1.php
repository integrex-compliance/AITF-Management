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

//database details get and display
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$auditOrganisation = "";
$auditDate = "";
$leadAuditor = "";
$referenceNumber = "";
$reviewDate = "";
$accepted = "";
$notAccepted = "";
$reviewer_comments = "";
$rowval = '';
if (isset($_POST['stageiid'])) {

    $stagei = "SELECT * FROM stage_i_audit_id WHERE stage_i_id = :id";
    $statement = $db->prepare($stagei);
    $statement->bindParam(':id', $_POST['stageiid']);
    $statement->execute();
    $stageiInfo = $statement->fetch();

    $auditOrganisation = $stageiInfo['auditOrganisation'];
    $auditDate = $stageiInfo['auditDate'];
    $leadAuditor = $stageiInfo['leadAuditor'];
    $referenceNumber = $stageiInfo['referenceNumber'];
    $reviewerName = $stageiInfo['reviewer_name'];
    if (isset($stageiInfo['reviewer_comments']) && !empty($stageiInfo['reviewer_comments'])) {
        $reviewer_comments = '<tr>
        <td colspan="3" ><br> 
        REVIEWER COMMENTS: ' . $stageiInfo['reviewer_comments'] . '        
        </td>
        </tr>';
    } else {
        $reviewer_comments = '<tr>
        <td colspan="3" ><br> 
        REMARK: <img src="./images/checkboxChecked.png"  width="10" height="10"> The stage 1 report has been verified and recommended to conduct stage 2.        
        </td>
        </tr>';
    }
    if (isset($stageiInfo['review_date']) && !empty($stageiInfo['review_date'])) {
        $reviewDate = date("d-m-Y", strtotime($stageiInfo['review_date']));
    } else {
        $reviewDate = "";
    }

    if ($stageiInfo['accepted'] == "Accepted") {
        $accepted = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
    } else {
        $accepted = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    if ($stageiInfo['accepted'] == "Not Accepted") {
        $notAccepted = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
    } else {
        $notAccepted = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    if ($stageiInfo['remark'] == "eremark") {
        $r = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
    } else {
        $r = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    $stageiData = "SELECT * FROM stage_i_audit WHERE ca_service_audit = :id";
    $statement = $db->prepare($stageiData);
    $statement->bindParam(':id', $_POST['stageiid']);
    $statement->execute();
    $stageiInfoi = $statement->fetchAll();
    
    foreach ($stageiInfoi as $value) {
        $rowval = $rowval.'<tr>
                                <td style="width:70%;">'.$value['audit_aspect'].'</td>
                                <td style="width:12%;"> ' . $value['audit_response'] . ' </td>
                                <td style="width:18%;"> ' . $value['remark'] . ' </td>
                            </tr>'; 
        
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
        $this->Cell(0, 15, 'Annex-1:Review of Audit processes(Stage 1 Audit)', 0, false, 'C', 0, '', 0, false, 'M', 'M');

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
        $html .= '<tr><td colspan="2">Form No. F 7.32 (Anexture-1)     Rev.:01</td>';
        $html .= '<td colspan="2">Date: 01.12.2016</td>';
        $html .= '<td colspan="1">Issue: 01</td>';
        $html .= '<td colspan="1">[P a g e ' . $this->count . ' of 1]</td></tr>';
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
$pdf->SetTitle('F7.32Anex-1');
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
$pdf->SetFont('helvetica', '', 10);

// add a page
$pdf->AddPage();



// create some HTML content
$html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9">
    <br>
        <span>Name of Audited Organisation: ' . $auditOrganisation . '  </span> <br>
        <br>
        <span>Date(s) of Audit Conducted: ' . $auditDate . '  </span> <br>
        <br>
        <span>Lead Auditor: ' . $leadAuditor . '  </span><br>
          <br>
        <span>Agreement Number: ' . $referenceNumber . '  </span><br>
        
      <br><span> Reviewer Acceptance: for no Conflict of interest with the above organization 
        ' . $accepted . '&nbsp;&nbsp;<label for="Accepted">Accepted</label>
        ' . $notAccepted . '&nbsp;&nbsp;<label for="NotAccepted">Not Accepted</label> </span><br><br>
    <table border="1"  cellpadding="4" cellspacing="0">
    <tr bgcolor="#DCDCDC">
        <th align="center" style="width:70%;">ASPECT TO VERIFY</th>
        <th align="center" style="width:12%";>Yes/No/NA</th>
        <th align="center" style="width:18%">REMARK/DETAILS(IF ANY)</th>
       
    </tr>
    '.$rowval.'
    
    '.$reviewer_comments.'
    <tr>
        <td colspan="3"> <br>
      
        Date Of REVIEWS: ' . $reviewDate . '
        <br><br>
        
       REVIEWER NAME : ' . $reviewerName . '
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
$pdf->Output('F7.32Anex-1.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

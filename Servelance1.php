<?php

// Prevent premature output that breaks PDF headers
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ob_start();
session_start();

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

include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
//echo $_POST['auditnotiid'];
$audit2 = "SELECT * FROM servelance_1_id WHERE servelance_id = :audit and service_id = :service and customer_id = :customer ";
$statement = $db->prepare($audit2);
$statement->bindParam(':audit', $_POST['auditnotiid']);
$statement->bindParam(':service', $_POST['serviceid']);
$statement->bindParam(':customer', $_POST['customerid']);
$statement->execute();
$audit2info = $statement->fetch();
//print_r($audit2info);
$audit2Details = "SELECT * FROM servelance_audit WHERE servelance_id = :audit and service_id = :service";
$statement = $db->prepare($audit2Details);
$statement->bindParam(':audit', $_POST['auditnotiid']);
$statement->bindParam(':service', $_POST['serviceid']);
$statement->execute();
$audit2_info = $statement->fetchAll();

$teamauditor= explode(',', $audit2info['auditors_name']);
$time1 = $audit2_info[0]['time_schedule'];
$time2 = $audit2_info[1]['time_schedule'];
$time3 = $audit2_info[2]['time_schedule'];
$time4 = $audit2_info[3]['time_schedule'];
$time5 = $audit2_info[4]['time_schedule'];
$time6 = $audit2_info[5]['time_schedule'];
$time7 = $audit2_info[6]['time_schedule'];
$time8 = $audit2_info[7]['time_schedule'];
$time9 = $audit2_info[8]['time_schedule'];
$time10 = $audit2_info[9]['time_schedule'];
$time11 = $audit2_info[10]['time_schedule'];
$time12 = $audit2_info[11]['time_schedule'];

$date1 = $audit2_info[0]['schedule_dates'];
$date2 = $audit2_info[1]['schedule_dates'];
$date3 = $audit2_info[2]['schedule_dates'];
$date4 = $audit2_info[3]['schedule_dates'];
$date5 = $audit2_info[4]['schedule_dates'];
$date6 = $audit2_info[5]['schedule_dates'];
$date7 = $audit2_info[6]['schedule_dates'];
$date8 = $audit2_info[7]['schedule_dates'];
$date9 = $audit2_info[8]['schedule_dates'];
$date10 = $audit2_info[9]['schedule_dates'];
$date11 = $audit2_info[10]['schedule_dates'];
$date12 = $audit2_info[11]['schedule_dates'];

$auditor1 = $audit2_info[0]['auditor'];
$auditor2 = $audit2_info[1]['auditor'];
$auditor3 = $audit2_info[2]['auditor'];
$auditor4 = $audit2_info[3]['auditor'];
$auditor5 = $audit2_info[4]['auditor'];
$auditor6 = $audit2_info[5]['auditor'];
$auditor7 = $audit2_info[6]['auditor'];
$auditor8 = $audit2_info[7]['auditor'];
$auditor9 = $audit2_info[8]['auditor'];
$auditor10 = $audit2_info[9]['auditor'];
$auditor11 = $audit2_info[10]['auditor'];
$auditor12 = $audit2_info[11]['auditor'];
//print_r($audit2_info); die;

// Extend the TCPDF class to create custom Header and Footer
if (!class_exists('MYPDF_Servelance1', false)) {
    class MYPDF_Servelance1 extends TCPDF {

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
            $this->Cell(0, 15, 'Audit Plan – 1st SURVEILLANCE ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            
           $this->writeHTMLCell($w='', $h='', $x='', $y='', $this->header, $border=0, $ln=0, $fill=0, $reseth=true, $align='L', $autopadding=true);
           $this->SetLineStyle( array( 'width' => 0.40, 'color' => array(0, 0, 0)));


        }

        // Page footer
       public function Footer() {

            $html = '<table border="1"  cellpadding="4" cellspacing="0">';
             $html .= '<tr><td colspan="6">Confidential when completed</td></tr>';
            $html .= '<tr><td colspan="1">Form No. F 7.14</td>';
            $html .= '<td colspan="2">Rev.:02, Date: 01.06.2017</td>';
            $html .= '<td colspan="2">Issue.: 01, Date: 01.06.2017</td>';
            $html .= '<td colspan="1">[P a g e ' . $this->count . ' of '. $this->getAliasNbPages() .']</td></tr>';
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
}

// create new PDF document
$pdf = new MYPDF_Servelance1(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Schedule-1st Surveillance Audit');
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

$auditNotiRow = "";

foreach ($audit2_info as $audit2) {
    $auditNotiRow = $auditNotiRow . '<tr><td colspan="1"> '.$audit2["schedule_dates"].'</td><td colspan="1"> '.$audit2["time_schedule"].'</td><td colspan="4">'.$audit2["activity"].'</td><td colspan="1">'.$audit2["clause"].'</td><td colspan="1"> '.$audit2["auditor"].' </td></tr>';
}

// set font
$pdf->SetFont('Times', '', 10);

// add a page
$pdf->AddPage();


//html content Start Here
$html = '
<style>
{
  table { page-break-after:auto }
  tr    { page-break-inside:avoid; page-break-after:auto }
  td    { page-break-inside:avoid; page-break-after:auto }
  thead { display:table-header-group }
  tfoot { display:table-footer-group }
}
</style>    
<table border="1"  cellpadding="4" cellspacing="0" width="100%" >
     <tr>
        <td colspan="2">
        <b>Client Name:</b>
        </td>
        <td colspan="4">
        '.$audit2info['client_name'].'
        </td>
        <td colspan="2">
        <b>Standard: </b>
        '.$audit2info['ios_standards'].'
        </td>
    </tr>   
    <tr>
        <td colspan="2">
        <b>Site(s):</b>
        </td>
        <td colspan="4">
        '.$audit2info['sites'].'
        </td>
        <td colspan="2">
        <b>Mandays: </b>
        '.$audit2info['mandays'].'
        </td>
    </tr>        
     <tr>
        <td colspan="2">
        <b>Scope of Certification: </b>
        </td>
        <td colspan="6">
        '.$audit2info['certification_scope'].'
        </td>
    </tr>   
    <tr>
        <td colspan="2">
        <b>Auditor/s Name: </b>
        </td>
        <td colspan="6">        
        '.$audit2info['auditors_name'].'
        </td>
    </tr>   
   
 </table>
 
 <br>
 <br>
<table border="1"  cellpadding="4" cellspacing="0" width="100%" >   
    <tr>
        <td colspan="1">
        <br><b>Date</b>
        </td>

        <td colspan="1">
        <br><b>Time</b>
        </td>
        
        <td colspan="4">
         <br><b>Department/Function/Process/Activity</b>
        </td>

        <td colspan="1">
        <br><b>Clause reference</b>
        </td>
        
     <td colspan="1">
        <br><b>Auditor Abb.</b>
        </td>
    </tr>      
 </table>
<br>
 <br>
<table border="1"  cellpadding="4" cellspacing="0" width="100%" >
   '.$auditNotiRow.'
    <tr><td colspan="8" align="center"><font size="10"><font color="">' . $audit2info["time_from"] . ' To ' . $audit2info["time_to"] . ' Lunch</font></font></td></tr>
 </table> 
';



// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//
//// reset pointer to the last page
//$pdf->lastPage();
//
//
//
//// add a page
//$pdf->AddPage();

//html content Start Here
$html = '<br>
<br>
<span align="right"><b>Prepared by : ' . $audit2info["prepared_by"] . '</b></span><br>
<span align="right"><b>Date : ' . date('d.m.Y', strtotime($audit2info["prepared_date"])) . '</b></span>
<br>
<br><b>Distributed to: <img src="./images/checkboxChecked.png"  width="10" height="10"> Client &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="./images/checkboxChecked.png"  width="10" height="10"> Auditor / Technical Experts &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="./images/checkboxChecked.png"  width="10" height="10"> AITF-MCL</b>
<br><b>Note:</b>
<p style="text-align: justify; text-justify: inter-word;"><font size="9">&emsp;&emsp;&emsp;1. Auditors shall verify and report the capability of the client to meet all product requirements that forms the basis of the contract
<br>&emsp;&emsp;&emsp;2. Internal communication, risk analysis, QMS awareness, QOs, documents & records control, to be verified as relevant in all depts. audited
<br>&emsp;&emsp;&emsp;3. Allot sufficient time for auditing Production/ Service provision and release of product and services.
<br>&emsp;&emsp;&emsp;4. The time indicated is approximate and may change based on audit trails and interaction meeting in event of auditee is responsible for multi functions which are audited by different auditors.
<br>&emsp;&emsp;&emsp;5. Performance evaluation and improvement will be applicable for function as appropriate.
<br>&emsp;&emsp;&emsp;6. As per defined surveillance visit schedule the HR function, storage and sale function during 1st SA and Maintenance, Purchase function during 2nd SA may skip.    
<br>
<br>
<br>
<span ><b>The tasks assigned to the audit team in connection with this audit plan (Objectives of visit) are:</b></span>
<br>1. Internal audits, management review, treatment of complaints if any, corrective and preventive action including review of actions taken on nonconformities identified during the previous audit 
    <br>2. Changes of Certified QMS with the standard’s requirements and effective implementation of certified management systems of organisation with regard to achieving the certified client\'s objectives and the intended result of the management system.
   <br>3. Continual improvement, continuing operation control and monitoring the client performance
  <br>4. Review of use of quality mark and accreditation logo, any changes and client\'s statements with respect to its operations with verification of promotional material, website, etc...
  <br>5. Control on externally provided processes, supporting process, design & development, production/service provision if any.
  <br>6. In case of multisite central office shall be covered during each surveillance audit.
  <br>7. Acceptance of the management system for continuation of recommendation to certificate of compliance with/without conditions.
</font></p>

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
$pdfOutput = $pdf->Output('Schedule_1stSurveillanceAudit.pdf', $outputMode);

if ($outputMode === 'S') {
    return $pdfOutput;
}


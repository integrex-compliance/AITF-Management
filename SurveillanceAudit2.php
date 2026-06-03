<?php
// Prevent premature output that breaks PDF headers
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
// Start output buffering to catch any output before PDF
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
require_once(__DIR__ . '/tcpdf_include.php');
// code by Pallavi to display pdf
include_once __DIR__ . '/dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$audit1 = "SELECT * FROM `stage_oneaudit_id` where ca_service_id = :service and ca_customer_id = :customer";
$statement = $db->prepare($audit1);
$statement->bindParam(':service', $_POST['serviceid']);
$statement->bindParam(':customer', $_POST['customerid']);
$statement->execute();
$audit1info = $statement->fetch();

if ($audit1info === false) {
    $audit1info = ['audit_dates' => '', 'intimation_date' => ''];
}

$auditdate1 = explode(', ',$audit1info["audit_dates"]);
$dateval1 = !empty($audit1info["intimation_date"]) ? date("d.m.Y", strtotime($audit1info["intimation_date"])) : '';
//for ($i = 0; $i < count($auditdate1); ++$i) {
//   $dateval1 =  $auditdate1[$i];
//}

$audit2 = "SELECT * FROM audit_scheduleii_id WHERE audit_scheduleii_id = :audit and ca_service = :service and ca_customer = :customer ";
$statement = $db->prepare($audit2);
$statement->bindParam(':audit', $_POST['auditnotiid']);
$statement->bindParam(':service', $_POST['serviceid']);
$statement->bindParam(':customer', $_POST['customerid']);
$statement->execute();
$audit2info = $statement->fetch();

if ($audit2info === false) {
    $audit2info = [
        'scheduleii_auditors' => '',
        'scheduleii_date' => '',
        'clieint_name' => '',
        'scheduleii_standards' => '',
        'scheduleii_address' => '',
        'scheduleii_cert_scope' => '',
        'scheduleii_mandays' => '',
        'scheduleii_mgmt' => '',
        'break_from' => '',
        'break_to' => '',
        'prepared_by' => '',
        'prepared_date' => date('Y-m-d')
    ];
}

$audit2Details = "SELECT * FROM audit_scheduleii WHERE scheduleii_id = :audit and ca_service = :service";
$statement = $db->prepare($audit2Details);
$statement->bindParam(':audit', $_POST['auditnotiid']);
$statement->bindParam(':service', $_POST['serviceid']);
$statement->execute();
$audit2_info = $statement->fetchAll();

$schedule1 = "SELECT * FROM `audit_schedule_id` WHERE serviceca_id = :service ";
$statement = $db->prepare($schedule1);
$statement->bindParam(':service', $_POST['serviceid']);
$statement->execute();
$schedule1_info = $statement->fetch();

if ($schedule1_info === false) {
    $schedule1_info = ['schedule_audit_date' => ''];
}

$teamauditor = explode(',', $audit2info['scheduleii_auditors']);

$auditdate = explode(', ',$audit2info["scheduleii_date"]);
$dateval_info = $schedule1_info["schedule_audit_date"];
//for ($i = 0; $i < count($auditdate); ++$i) {
//   $dateval =  $auditdate[$i];
//}

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_AuditScheduleStage2 extends TCPDF {

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
        
        $this->SetXY(50, 18);
        $this->SetFont('helvetica', 'B', 12);
       // $this->Ln(7);
        //SubTitle
        $this->Cell(0, 15, 'Audit Plan - Stage2 / Recertification', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->writeHTMLCell($w = '', $h = '', $x = '', $y = '', $this->header, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'L', $autopadding = true);
        $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));


    }

    // Page footer
    public function Footer() {

        $html = '<table border="1"  cellpadding="4" cellspacing="0">';
        $html .= '<tr><td colspan="1">Form No.F 7.13</td>';
        $html .= '<td colspan="2">Rev.:04, Date: 01.06.2017</td>';
        $html .= '<td colspan="2">Issue.: 01, Date: 01.06.2017</td>';
        $html .= '<td colspan="1">[P a g e ' . $this->count . ' of '. $this->getAliasNbPages() .']</td></tr>';
        $html .= '</table>';
        // Position at 15 mm from bottom
        $this->SetY(-11);
        // Set font
        $this->SetFont('times', '', 8);
        // Page number
//        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->writeHTMLCell(0, 0, '', '', $html, 0, 0, false, "L", true);
        $this->count = $this->count + 1;
    }

}

// create new PDF document
$pdf = new MYPDF_AuditScheduleStage2(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Audit Schedule 2');
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
$pdf->SetAutoPageBreak(TRUE, 15);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

$auditNoteRow = "";

foreach ($audit2_info as $audit2) {
//    $auditNotiRow = $auditNotiRow . '<tr><td colspan="1">'.date('d.m.Y', strtotime($audit2['audit_scheduleii_dates'])).' </td> <td colspan="1"> '.date('H:i', strtotime($audit2['audit_scheduleii_time'])).' </td> <td colspan="4"> '.$audit2['audit_scheduleii_department'].' </td> <td colspan="1"> '.$audit2['clause_ref'].' </td> <td colspan="1"> '.$audit2['audit_scheduleii_auditor'].' </td> </tr>';
    $auditNoteRow = $auditNoteRow . '<tr><td colspan="1"><font color="black"> '. $audit2['audit_scheduleii_dates'] . ' / ' . $audit2['audit_scheduleii_time'] . ' </font></td><td colspan="4"><font color="black"> ' . $audit2['audit_scheduleii_department'] . ' </font></td><td colspan="1"><font color="black">  ' . $audit2['clause_ref'] . ' </font></td><td colspan="1"><font color="black"> ' . $audit2['audit_scheduleii_auditor'] . ' </font></td></tr>';
}

// set font
$pdf->SetFont('times', '', 10);

// add a page
$pdf->AddPage();


//html content Start Here
$html= '
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
        <td colspan="1">
        <b>Client Name:</b>
        </td>
        <td colspan="4">
        <font color="black">' . $audit2info['clieint_name'] . '</font>
        </td>
        <td colspan="2">
        <b>Standard/s: </b>
        <font color="black">' . $audit2info['scheduleii_standards'] . '</font>
        </td>
    </tr>   
   <tr>
   <td colspan="1">
        <b>Site(s):</b>
        </td>
        <td colspan="4">
        <font color="black">' . $audit2info['scheduleii_address'] . '</font>
        </td>
        <td colspan="2">
        <b>Date(s): </b>
        <font color="black">' . $audit2info['scheduleii_date'] . '</font>
        </td>
   </tr>
   <tr> 
   <td colspan="1">
        <b>Scope of Certification: </b>
        </td>
        <td colspan="5">
        <font color="black">' . $audit2info['scheduleii_cert_scope'] . '</font>
        </td>
        <td colspan="2">
        <b>Mandays:</b>
        <font color="black">' . $audit2info['scheduleii_mandays'] . '</font>
        </td>
   </tr>   
   <tr>
        <td colspan="2">
        <b>Management Rep.: </b>
        </td>
        <td colspan="6">
        <font color="black">' . $audit2info['scheduleii_mgmt'] . '</font>        
        </td>
   </tr>
   <tr>
   <td colspan="2">
        <b>Auditor/s Name: </b>
        </td>
        <td colspan="6">        
        <font color="black">' . $audit2info['scheduleii_auditors'] . '</font>        
        </td>
   </tr>
 </table>
 <br>
 <br>
<table border="1"  cellpadding="4" cellspacing="0" width="100%" >
     
    <tr>
        <td colspan="1">
        <br><b>Date / Time </b>
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
 <br><br>
<table border="1"  cellpadding="2" cellspacing="0" width="100%" >
   ' . $auditNoteRow . '
       <tr><td colspan="8" align="center"><font size="10"><font color="black">' . $audit2info["break_from"] . ' To ' . $audit2info["break_to"] . ' Lunch</font></font></td></tr>
 </table> 
';



// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


$html = '<br>
<span align="right"><b>Prepared by: ' . $audit2info["prepared_by"] . '</b></span><br>
<span align="right"><b>Date : ' . date('d.m.Y', strtotime($audit2info["prepared_date"])) . '</b></span>

<br><b>Distributed to: <img src="./images/checkboxChecked.png"  width="10" height="10"> Client &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="./images/checkboxChecked.png"  width="10" height="10"> Auditor / Technical Experts &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="./images/checkboxChecked.png"  width="10" height="10"> AITF-MCL</b>
<br><b>Note:</b>
<p style="text-align: justify; text-justify: inter-word;"><font size="9">&emsp;&emsp;&emsp;1. Auditors shall verify and report the capability of the client to meet all product requirements that forms the basis of the contract
<br>&emsp;&emsp;&emsp;2.Internal communication, risk analysis, QMS awareness, QOs, documents & records control, to be verified as relevant in all depts. audited.
<br>&emsp;&emsp;&emsp;3.Allot sufficient time for auditing Production/ Service provision and release of product and services. 
<br>&emsp;&emsp;&emsp;4.The time indicated is approximate and may change based on audit trails and interaction meeting in event of auditee is responsible for multi functions which are audited by different auditors.
<br>&emsp;&emsp;&emsp;5.Performance evaluation and improvement will be applicable for function as appropriate.
<br>
<br>
<br>
<b><u>Objectives of visit:</u></b>
<br>&emsp;&emsp;&emsp;1.Information and evidence of conformity for all the requirements applicable to Quality management system standard and normative document.
<br>&emsp;&emsp;&emsp;2.Performance monitoring, measuring, reporting and reviewing against key performance objectives and targets.
<br>&emsp;&emsp;&emsp;3.Client'."'".'s management system and performance as regards to applicable legal compliance.
<br>&emsp;&emsp;&emsp;4.Operational control of the clients processes including control on outsourcing process, supporting process, design & development, production/service provision if any.
<br>&emsp;&emsp;&emsp;5.Internal Auditing covering all sites and Management Review.
<br>&emsp;&emsp;&emsp;6.Management commitment, responsibility for client'."'".'s policies.
<br>&emsp;&emsp;&emsp;7.Links between the normative documents, policy, performance objectives and targets, any applicable legal requirements, responsibilities, competence of personnel, operations, procedures, performance data and internal audit findings and conclusion.
<br>&emsp;&emsp;&emsp;8.Acceptance of the management system for Recommendation for issue of certificate of compliance with/without conditions or otherwise 
</font></p>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
if (ob_get_length()) {
    ob_end_clean();
} else {
    @ob_end_clean();
}
$outputMode = (isset($_POST['output_mode']) && $_POST['output_mode'] === 'S') ? 'S' : 'I';
$pdfOutput = $pdf->Output('AuditSchedule_2.pdf', $outputMode);

if ($outputMode === 'S') {
    return $pdfOutput;
}

//============================================================+
// END OF FILE
//============================================================+

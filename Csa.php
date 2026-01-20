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
$orgName = "";
$scope = "";
$aif_code = "";
$nace_code = "";
$accreditation = "";
$refundable_amount = "";
$audit_stage_i = "";
$audit_stage_iI = "";
$initial_assessment = "";
$certification_maintenance = "";
$address = "";
$reviewDate = "";
$date = "";
$latestService = "SELECT service_id, service_type FROM service WHERE customer_id = :id ";
$statement = $db->prepare($latestService);
$statement->bindParam(":id", $_POST["csId"]);
$statement->execute();
$latestService = $statement->fetchall();

$max = 0;
foreach ($latestService as $ls) {
    if ($ls['service_type'] == 'CA' || $ls['service_type'] == 'RC') {
        if ($ls['service_id'] > $max)
            $max = $ls['service_id'];
    }
}

$casDetails = "SELECT * FROM audit_application WHERE service_id = :id";
$statement = $db->prepare($casDetails);
$statement->bindParam(':id', $_POST["service"]);
$statement->execute();
$casInfo = $statement->fetch();

$iafnace = "SELECT * FROM `audit_iaf_nace` where audit_id = :id";
$statement = $db->prepare($iafnace);
$statement->bindParam(':id', $casInfo["audit_id"]);
$statement->execute();
$naceInfo = $statement->fetch();

//echo $casInfo['org_name'];
//print_r($casInfo);die;
$iafcode = $naceInfo['iaf_code'];
$iafcode_array = explode(",", $iafcode);
$nacecode = $naceInfo['nace_codes'];
$nacecode_array = explode(",", $nacecode);
$naceclass = $naceInfo['nace_class'];
$naceclass_array = explode(",", $naceclass);
$combined_strings = [];
for ($i = 0; $i < count($iafcode_array); $i++) {
    $combined_strings[] = $iafcode_array[$i] . "/" . $nacecode_array[$i] . "/" . $naceclass_array[$i];
}
$aifNaceCode = implode(', ', $combined_strings);
$orgName = $casInfo['org_name'];
//$aif_code = $naceInfo['iaf_code'];
//$nace_code = $naceInfo["nace_codes"];
$scope = $casInfo["proposed_scope"];
$accreditation = $casInfo["accereditation_requirement"];
$refundable_amount = $casInfo['refundable_amount'];
$audit_stage_i = $casInfo['audit_stage_i'];
$audit_stage_iI = $casInfo['audit_stage_iI'];
$initial_assessment = $casInfo['initial_assessment'];
$certification_maintenance = $casInfo['certification_maintenance'];
$reviewDate = date("d.m.Y", strtotime($casInfo["review_date"]));
$assessmentstandard = $casInfo["certification"];

$csDetails = "SELECT * FROM customer_info WHERE id=:id";
$statement = $db->prepare($csDetails);
$statement->bindParam(':id', $_POST['csId']);
$statement->execute();
$csInfo = $statement->fetch();
$address = $csInfo['address'];

$allotmentDetails = "SELECT * FROM `certification_aggrement` where id = :id";
$statement = $db->prepare($allotmentDetails);
$statement->bindParam(":id", $_POST['certification']);
$statement->execute();
$certnoinfo = $statement->fetch();

$alotno = $certnoinfo["aggrement_no"];
$regDate = date("d.m.Y", strtotime($certnoinfo["aggrement_date"]));

$certificationRow = "";
$stage1row = "";
$stage2row = "";
if (isset($_POST["saiicount"]) && !empty($_POST["saiicount"]) && $_POST["saiicount"] == 'No-SA-II') {
    $certificationRow = '<br>As per your application for ' . $assessmentstandard . ' management system certification, 
    this is certification proposal for your organization:
    <br>';

    $stage1row = '<td colspan="5" valign="certer" height="30">Initial Audit(Stage 1)Documentation Review:</td>';

    $stage2row = '<td colspan="5" valign="certer" height="30">Initial Audit (Stage 2) Certification Audit:</td>';
} else {
    $certificationRow = '<br>As per your application for ' . $assessmentstandard . ' management system certification, 
    this is re-certification proposal for your organization:
    <br>';

    $stage1row = '<td colspan="5" valign="certer" height="30">Documentation Review:</td>';

    $stage2row = '<td colspan="5" valign="certer" height="30">Re-certification Audit:</td>';
}

$_POST["saiicount"];

global $header_content, $clientID;
$header_content = $regDate;
$clientID = $alotno;

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';

    //Page header
    public function Header() {
        
        // watermarking image
        $image_file = K_PATH_IMAGES . 'aitfWatermark.jpg';
        
        // draw opaque red square
        $this->SetFillColor(255, 0, 0);
        $this->SetDrawColor(127, 0, 0);
//        $this->Rect(30, 40, 60, 60, 'DF');

// set alpha to semi-transparency
        $this->SetAlpha(0.5);

// draw green square
        $this->SetFillColor(0, 255, 0);
        $this->SetDrawColor(0, 127, 0);
//        $this->Rect(50, 60, 60, 60, 'DF');

// draw blue square
        $this->SetFillColor(0, 0, 255);
        $this->SetDrawColor(0, 0, 127);
//        $this->Rect(70, 80, 60, 60, 'DF');
        
        $this->Image($image_file, 60, 100, 100, 100, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetAlpha(1);

        // Logo
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        $this->Image($image_file, 17, 5, 43, '', 'JPEG', '', 'T', false, 500, '', false, false, 0, false, false, false);
//        
        // Set font
        $this->SetFont('helvetica', 'B', 14);
        // Position at 15 mm from bottom
//        $this->SetY(+15);
        // Title
        $this->SetXY(60, 10);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetXY(60, 18);
        $this->SetFont('helvetica', 'B', 12);
//        $this->SetXY(20, 14);
        $this->Cell(0, 0, 'CERTIFICATION SERVICE AGREEMENT', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->SetFont('helvetica', 'B', 10);
//        $this->Ln(7);
        $this->SetXY(20, 20);
        $this->writeHTMLCell($w = '', $h = '', $x = '', $y = '', $this->header, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'L', $autopadding = true);
        $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));

//        $this->Line(10, 10, $this->getPageWidth() - 10, 10);
//        $this->Line($this->getPageWidth() - 5, 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, $this->getPageHeight() - 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, 5, 5, $this->getPageHeight() - 5);
    }

    // Page footer
    public function Footer() {
        global $header_content, $clientID;
        // Position at 15 mm from bottom
        $this->SetY(-10);
        // Set font
        $this->SetFont('helvetica', '', 10);
        // Page number
        $this->Cell(0, 0, 'F 7.03. Rev. 06 dt.26.05.2019        Agreement No: ' . $clientID . ',   Dt. ' . date("d.m.Y", strtotime($header_content)) . ',             [P a g e ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages() . ']', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('CERTIFICATION SERVICE AGREEMENT');
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
$pdf->SetMargins(PDF_MARGIN_LEFT, 15, 5);
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
$pdf->SetFont('helvetica', '', 9.5);

// add a page
$pdf->AddPage();

$html = '<br>
    <p  style="text-align: justify; text-justify: inter-word;"><font size="10"><b>To-</b> ' . $orgName . '
    <p><b>Address: </b>' . $address . '
    <br>
    ' . $certificationRow . '
    <br>This proposal (certification service agreement) having the following enclosures.
    <br>
    <br>
   
    <br>1. 	Information about A.I.T.F. (this abbreviation used in entire agreement on behalf of A.I.T.F. 
    Management Certification Limited.)

    <br>
    <br>2. STRATEGY
    <br>
    <br>3. Process of certification activity
    <br>
    <br>4. Service agreement for management system assessment:
        <br>(The Services, Warranty, Timing &amp; Progress of the service, Postponement of Services, Duration &amp; termination, Confidentiality, Intellectual Property 
        rights, Payment, Indemnities and limitation of liabilities, assignment, force majeure, Dispute resolution and
        Governing Law, Publication)
    <br>
    <br>5. Complaints &amp; Appeals   
    <br>
    <br>6. Suspension, Withdrawal and Cancellation of certificate
    <br>
    <br>7. Miscellaneous
    <br>
    <br>8. Fees structure
    <br>
    <br>9.<i>Basic service details</i>
      <br><span>    </span><span>a. Scope of service</span>
      <br><span>    </span><span>b. Nonconformity Type</span>
      <br><span>    </span><span>c. Corrective actions &amp; surveillance audit</span>
      <br><span>    </span><span>d. Certificate of conformity &amp; Retention</span>
      <br><span>    </span><span>e. Renewal of the certificate</span>
      <br><span>    </span><span>f. Audit team</span>
      <br><span>    </span><span>g. Control of mark</span>
      <br><span>    </span><span>h. Stake holder committee (SHC) </span>
      <br><span>    </span><span>i. Confidentiality and independence</span>
      
<br>
<br>
<br>Read &amp; review all above attachment. We have send this in (Two) copies. For your acceptance send one copy to us
    with your duly sign &amp; stamped by authorised person.
<br>
<br>
<br>
<br>

<br>
Sincerely, <br><br>
<b>Vaibhav Aajbe</b><br>
<b>Managing Director</b><br>
<b>AITF MANAGEMENT CERTIFICATION LIMITED</b>
    </font>
</p>
';


// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');



// reset pointer to the last page
$pdf->lastPage();


// add a page
$pdf->AddPage();

//html content Start Here

$html = '<br>
     <p  style="text-align: justify; text-justify: inter-word;"><font size="9"><b>1.<u>Information about certification &amp; A.I.T.F. Management Certification Limited (AITF)</u></b>:<br>
    
     <br>AITF (Assessment &amp; Institute of Technical Forum) Management Certification Limited is pleased to introduce itself as an assessment agency. Established on July 1st, 2011, AITF is a public limited company functioning as a management certification assessment organization. With a team of qualified and experienced auditors, AITF aims to provide necessary support to industry and service organizations in achieving their goals towards<b> <i>"Excellence in Quality and Continual Improvement"</i></b>
     <br>
     <br><b>AITF Management Certification Limited</b> is established with plan as a certification body with its main objectives to safeguard life, property, and environment through Impartial, Independent and value-added assessment as per international standards like ISO 9001 (QMS), ISO14001 (EMS), and ISO 45001 (OHSMS).
     <br>
     <br><b>Policy</b>
     <br>
     <br>The AITF is committed to provide assessment service for management certification to its clients as per the
        requirements specified by ISO/IEC 17021 and accreditation board guidelines.
     <br>
     <br>We are also committed to comply all applicable legal and other pertinent requirements, and protect the interests
        of the other parties while providing our services in an independent and impartial manner with strictly adhering to
        the code of ethics
     <br>
     <br>We are committed to manage conflict of interests and shall ensure integrity, objectivity and confidentiality while
         providing proactive, Independent assessment service for the benefit of human kind
     <br>
     <br>We are committed to maintain effective management systems through continual improvement of our
         processes and certification services to obtain enhanced customer satisfaction.
     <br>
     <br>We are committed to provide training periodically as per needs to our auditor, technical &amp; management staff with
         the availability of all the necessary resources for service realisation.
     <br>
     <br>We are committed to maintain, measures and analyse objectives periodically to achieve our Policies.
     <br>
     <br><b>2.<u>STRATEGY</u></b>
    <br>- Assessment as per latest recognized standards, codes, procedures &amp; methods.
    <br>- Consistency in service quality.
    <br>- Competent auditors and audit execution as per guideline of ISO 17021.
    <br>- Assessment with value addition by well qualified and adequately experienced auditors.
    <br>
    <br><b>3.<u>PROCESS OF CERTIFICATION ACTIVITY</u></b>
    <br><b><i>Our methodology &amp; road map for certification of management system</i></b>
    <br>STEPS:
    <br>- Application review and Study of organization`s processes to develop Certification proposal &amp; action plan.
    <br>- Review of contract, organization documents and Plan of audit, program, auditor selection
    <br>- Stage 1 (audit)
    <br>- Stage 2 (audit)
    <br>- Review of NC if any (follow up audit if required).
    <br>- Certification decision committee review&amp; deliberation.
    <br>- Provide certificate of compliance.
    <br>- Periodical surveillance
    <br>- Recertification
  </p>

  
    
    <p style="text-align: justify; text-justify: inter-word;">4.<b><u>MANAGEMENT SYSTEM ASSESSMENT AGREEMENT</u></b><br><font size="9">4.1.<u>The Services</u>
       <br>AITF Management certification Limited agrees to provide the assessment services to its clients related 
       with the scope of work to a management system certification and section 8 – Fees structure. In the execution
       of services AITF shall act as independent facilitator and auditors.
       The services will include audit of client business operation, business related documents and working site/s. 
       prior to the commencement of services, dates and the schedule for the execution of the services shall be 
       communicated in writing.Subsequent to the audit by AITF, AITF will issue a report. The report will provide 
       result and summary of the assessment made by AITF along with findings and areas of improvements if any.
';


// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();


// add a page
$pdf->AddPage();

//html content Start Here

$html = '
        <br><br>
       <br>4.2.<u>Warranty</u>
       <br>AITF ensures that the services shall be free from errors or defects. Any documented error or defect in 
       the service will be corrected by AITF within a reasonable period of time at AITF’s expense. The said error 
       or defect is not attributable to client for reasonable action of AITF relying on Information provided by 
       client and that AITF is notified of said error or defect within 60 – sixty- days after completion of the 
       relevant element of the service.
       <br>4.3.<u>Obligation</u>
        <br>In consideration of the satisfactory services provided, Client shall pay to AITF as per agreed price based on this agreement 
        as detailed in section 8 - Fees structures.
            Client shall use its best endeavors to co-operate with AITF in order to facilitate the performance of the 
            services, hereunder ensure that AITF without undue delay, receives all relevant Information and documentation 
            needed to perform the services and that AITF\'s representative(s) is provided with necessary access to all work 
            sites.
        Client acknowledges that AITF is an independent third party and that decisions concerning execution of the 
        services may be taken at AITF\'s discretion. Client confirms that it shall not in any way attempt to 
        influence the execution of the services and/or AITF in a manner, which may jeopardize AITF\'s independence, 
        integrity or its role.
        When AITF representatives are at client premises, client shall provide all adequate safety measures to 
        ensure working environment is safe and in accordance with all relevant legislation. Client shall indemnify, 
        defend and hold AITF harmless from all costs, expenses and/or claims incurred by AITF as a direct 
        consequence of client\'s failure to fulfill its obligations.
        <br>4.4.<u>Timing and Progress of the services</u>
        <br>AITF shall provide to client with a suggested time audit notification and assessment plan, including dates and 
        Sites for the execution of the services. When audit notification is accepted by client with its duly authorised signature, such 
        time schedule and assessment plan will form an incorporated part of this agreement. In case of any 
        postponement or agreed changes AITF and client shall agree on a revised time schedule and assessment 
        plan as soon as possible to replace the previous one as an integrated part of this agreement.
            AITF shall execute the services according to the agreed lime schedule and assessment plan. 
            AITF shall execute the services with due progress and keep informed to client of the progress of the services.
            <br><u>4.5. Postponement of Services</u>
<br>Client may postpone execution of the services as agreed in the time schedule and assessment plan, upon prior 
written notice to AITF (hereinafter referred to as notice of postponement).
Subject only to force majeure (clause 4.11) and in the event that notice of postponement is received by AITF later 
than 7 (seven) days prior to the agreed date of Service, AITF may entitled to 1 (one) manday rate if postponement 
happen as compensation for the postponement.
In case of postponement of any audit from AITF due to certain unavoidable circumstances other than force majeure 
clause at 4.11, in that case AITF will mutually agree with client the desired audit date .

<br>4.6.<u>Duration &amp;Termination</u>
<br>This agreement shall remain in full force and effect until terminated by one of the party giving the other 
no less than 3 weeks prior written notice to that effect.
In the event of termination by client with no fault of AITF at any stage of certification process, Client shall 
pay AITF the value as per agreed fees structure as detailed in section 8 of this agreement.

<br>4.7.<u>Confidentiality</u>
<br>AITF undertakes that both for the duration of this agreement and after its termination it will;
<br>a) preserve and cause all AITF personnel and sub-contractors to preserve the secrecy of any confidential information;

<br>b) not disclose to any third party any confidential information except with client&#39;s prior, written consent;
<br>c) not use any confidential information other than for the purpose for which it has been disclosed to AITF.
<br>
<u>The undertaking in this clause 4.7 shall continue only insofar as the Confidential Information in question is not;</u>
<br>a) at the time of disclosure already in the possession of AITF, or
<br>b) received by AITF from a third party with the right to disclose such Information, or
<br>c) now or later in the public domain, other than by an unauthorized act of AITF, or
<br>d) required to be disclosed by public authorities in accordance with applicable law, or
<br>e) Required to be disclosed by the relevant accreditation authority.
<br><br>Irrespective of the provisions in this clause 4.7 and clause 4.8 (Intellectual property rights), Client 
hereby authorises AITF to use confidential information for statistical- and analytical purposes, even when such 
statistics and analysis will be published, always provided that such confidential information is made anonymous.
<br><br>4.8.<u>Intellectual property rights</u>
<br>Neither Client nor AITF shall have the right of use other than for the purpose of and in accordance with this 
agreement of any patent, copyright, proprietary right or confidential know how or trademark provided by the other 
party.
<br><br>All rights of title to, copyright in and ownership of any items which AITF provides in relation to the services 
which are merely supplemented, enhanced, modified or adapted in the course of the services or any such item 
developed by AITF outside the agreement shall remain with AITF.
Client shall save, indemnify, defend and hold harmless to AITF from all claims, losses, damages, costs 
(including legal costs), expenses and liabilities of every kind arising out of any alleged infringement of any 
patent or proprietary or protected right arising out of or in connection with the execution of the obligations of 
AITF under this agreement or the use by AITF of any Information, material or data supplied by client.
</p>
';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();


// add a page
$pdf->AddPage();

$html = '<br>
<p style="text-align: justify; text-justify: inter-word;">
 
<br>4.9.<u>Payment</u>
<br>Client shall pay the agreement price and all other payments agreed herein to AITF. Government tax and levies 
will be charged as per prevailing rates at the time of execution of services. Payment shall be made within 30 days 
after issuance of the invoice. For late payment interest will be charged at a rate of 10% per month or part thereof.
<br>4.10.<u>Indemnities and limitation of liabilities</u> 
<br>For the purposes of this clause 4.10 "third party" shall mean any party which is not a member of client group.
For the purposes of this Clause 4.10 the expression "Consequential Loss" shall mean indirect losses and/or loss 
of production, loss of product, loss of use and loss of revenue, profit or anticipated profit arising from or 
related to the performance of the agreement and whether or not such losses were foreseeable at the time of entering 
into the agreement.

<br>AITF shall be responsible for and shall save, indemnify, defend and hold harmless from and against all claims, 
losses, damages, costs (Including legal costs) expenses and liabilities in the respect of:
<br>a. loss or damage to property whether owned, hired, leased or otherwise provided by AITF arising from or 
relating to the performance of the agreement; and
<br>b. personal injury Including death or disease to any person employed by AITF arising from or relating to the 
performance of the agreement; and
<br>c. personal injury Including death or diseases or loss of or damage to the property of any third party to the 
extent that any such injury, loss or damage is caused by the negligence or breach of duty (whether statutory or 
otherwise) of AITF.
<br>
<br>Client shall be responsible for and shall save, indemnify, defend and hold harmless AITF from and against all 
claims, losses, damages, costs (Including legal costs) expenses and liabilities in respect of:
<br>a. loss or damage to property of client group whether owned, hired, leased or otherwise provided by client 
arising from or relating to the performance of the agreement; and
    <br>b. personal injury including death or disease to any person employed by client arising from or relating to 
    the performance of the agreement: and
    <br>c. personal injury Including death or disease or loss of or damage to the property of any third party to 
    the extent that any such injury, loss or damage is caused by the negligence or breach of the duty 
    (whether statutory or otherwise) of client group.
    <br>d. d.	Subject to clause 4.6 (Termination) and except to the extent of any agreed liquidated damages 
    (Including any predetermined termination fees) provided for in the agreement, client shall save, identify, defend 
    and hold harmless to AITF.

    <br>Subject only to the indemnities given in this Clause 4.10, AITF\'s maximum cumulative liability arising out 
        of or related this agreement shall be limited to an amount equal to 5 - five - times the fee paid to AITF 
        by client under this agreement for the relevant plan of the services or INR 100,000 (or the equivalent 
        thereto), whichever is the less,
<br>All exclusions and indemnities given under clause 4.10 shall apply irrespective of cause and notwithstanding
the negligence or breach of duty (whether statutory or otherwise) of the indemnified party or any other entity or 
party and shall apply irrespectively of any claim, under contract or otherwise at law. Both parties shall co-operate
fully in investigating the incident.
<br>4.11.<u>Assignment</u>
<br>Neither the rights nor the obligations of any party under this agreement may be assigned or transferred, in 
whole or in part, without the prior written consent of the other party, such consent not to be unreasonably withheld.
<br>4.12.<u>Force majeure</u> 
<br>Delay in or failure of performance of either party shall not constitute a default here under or give rise to any
claim for damage if and to the extent such delay or failure is caused by fire, strike, lockout dispute with workmen,
accident, delay in transport, shortage of fuel, default of any sub-contractor. Inability to obtain material, embargo,
act or demand or requirement of any government or any government department or local authority or as a consequence 
of war hostilities (whether war be declared or not) or to any other cause; always provided that the reason is beyond
the reasonable control of the party in question. If any such delay occurs then (unless the cause thereof shall 
frustrate or render impossible or illegal the performance of the agreement or shall otherwise discharge the same) 
the party\'s period of performance shall be extended by such period (not limited to the length of the delay) as the 
party may reasonably require completing the services.
<br>The affected party shall immediately notify the other party in writing of the causes and expected duration of any such occurrence. 
<br>
4.13.<u>Dispute resolution and Governing Law</u>
<br><i>This agreement shall be governed and considered in accordance with the laws of India.</i>
<br>Any dispute arising in relation to or as a consequence of this agreement, which cannot be settled amicably 
through negotiations between the parties, shall be subject to the courts of Pune, India.
<br>4.14.<u>Publication and use of AITF and Accreditation mark</u>
<br>a.	The valid certificate holder may publish a complete copy of the actual certificate of conformity.
<br>b.	The certificate of conformity may only be used in advertisements or brochures in complete reproduction.
<br>c.	Client may provide copies of audit reports issued by AITF to interested third parties, but only in their entirety.
<br>d.	The Client required to conforms to the requirements of AITF when making reference to its certification status in communication media such as the internet, brochures or advertising, or other documents,
<br>e.  If statements published by the holder of a certificate are found to be misleading or incorrect, they are to be retracted after written notification by AITF.
<br>f.	Client shall not use or permit to use of a certification document or any part thereof in a misleading manner.
<br>g.	Upon suspension or withdrawal of its certification, client shall discontinues its use of all advertising matter that contains a reference to certification.
</p>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

// reset pointer to the last page
$pdf->lastPage();


// add a page
$pdf->AddPage();

$html = '<br><p  style="text-align: justify; text-justify: inter-word;">
<br>h.	Client required to amends all advertising matter if scope of certification has been reduced,
<br>i.	Reference to the certificate of conformity can be made; this should however, not be done in such a way as to imply that products, services or process is certified.
<br>j.	Also does not imply that the certification applies to activities that are outside the scope of certification.
<br>k.	Does not use certification in such a manner that would bring the AITF and/or certification system into disrepute and lose public trust.
<br>The holder of a certificate of conformity may provide copies of audit reports issued by AITF to 
interested third parties, but only in their entirety.
<br>If statements published by the holder of a certificate are found to be misleading or incorrect, they are to 
be retracted after written notification by AITF.  
 
<br>4.15.<u>Complaints regarding certified companies</u>
<br>In case AITF receives written complaints in relation to the certified management system from a third party, 
AITF has an obligation to investigate the cause of the complaint. The holder of the certificate is obliged to give 
assistance to the extent found necessary by AITF.
 
<br>4.16.<u>Initial certification</u>
<br>The initial certification includes site/s audit (Stage 1 and Stage 2), document review may conduct at AITF 
office or during stage 1 audit (onsite visit) with the purpose to determine the level of implementation of the 
management system in order to find out readiness of the client for stage 2 and planning of stage 2. During stage 
1 audit if any significant changes occurred that may impact to the management system then AITF is considering the 
needs to repeat all or part of stage 1 i.e. stage 1 may lead to postponement or cancellation of stage 2 audit. 
Further in affirmatives AITF conducts (Stage 2) audit at client’s premises to determine the conformity of client 
management system including its effectiveness. However intervals between stage 1 and stage 2 shall not exceeds six 
months.

<br>4.17.<u>Follow-up audit</u>
<br>Performed in the events of major nonconformance occurs during certification, recertification or surveillance audit 
and if such nonconformance require onsite verification to verify implementation corrective action/s.
<br>4.18.<u>Surveillance audit</u>
<br>Audits necessary to verify the continuation of implementation of effective management system, if any changes 
made during corrective actions, changes in organisation, changes in process, scope/activities as per agreed frequency. 

<br>4.19.<u>Special audit</u>
<br>Perform in the circumstances of following:
<br>- If client had made a significant change in his management system.
<br>- If client wishes to extend existing scope of certified by AITF.
<br>- If there is evidence that client’s management system is not adequately implemented and any valid third party 
complaint to AITF against certified client.
<br>4.20.<u>Recertification</u>
<br>Performed during every cycle of three years of certification. (Before expiry of current certificate). 
Recertification would be considered the performance of the management system over the most recent certification 
cycle and the review of previous surveillance audit reports of client. Upon cancellation or interruption or in case 
the fails to restore the re-certification, client has to apply for new certification, AITF shall be entitled to 
fees as detailed in the agreement.
<br>
<br><b>5. COMPLAINT &amp; APPEALS</b>

<br>5.1.<u>Complaint</u>
<br>Complaint can be made to Director of AITF regarding functioning of AITF in writing. AITF will deal with the 
complaint if valid and investigate as per AITF procedure. The outcome will be communicated to complainer.
<br>In case of complaints relevant to public interest, the certified organization and the complainant shall be 
consulted and if felt necessary information about the subject matter of the com-plaint as well as its solution and 
resolution will be made available for public viewing.

<br>5.2.<u>Appeals</u>
<br>Appeals against the decisions taken by AITF concerning the non-issuance, suspension or withdrawal of 
certification. AITF will deal and decision will communicate to the appealer.
<br>
<br><b>6.SUSPENSION, WITHDRAWAL AND CANCELLATION OF CERTIFICATE</b>
<br>In case of following cl. 6.1, 6.2, 6.3 the client\'s management system certification will invalid and client 
shall refrain from further promotion of its certification. AITF may make the status of the certification publicly 
available and shall take any other measures it deems appropriate.
<br>6.1.<u>Suspension</u> 
<br>- AITF shall suspend certification in cases when, for example,  a certified client\'s management system
has persistently or seriously failed to meet certification requirements, including requirements for the 
effectiveness of the management system, Periodical surveillance audit not complied as per agreed frequency
<br>- Requested corrective actions are not performed within agreed time limits;
<br>- The certified client has voluntarily requested a suspension.
<br>- Improper use of the certificate of conformity or accreditation mark, e.g. misleading publication or advertisement;
</p>
';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();


// add a page
$pdf->AddPage();

$html = '<br><p  style="text-align: justify; text-justify: inter-word;">

- The holder of the certificate neglects to inform AITF about matters of vital importance for the certified management system.
<br>- Failure to settle the outstanding dues due to AITF in spite of several reminders.
<br>During suspension period if the issue of suspension resolved then the suspension shall be revoke and AITF. 
However the suspension would not exceed 6 months & will be resulted in to withdrawal or reducing the scope of 
certification if cause of suspension not resolved. 
<br>
<br>6.2.<u>Withdrawal</u>
<br>The certificate may be withdrawn in the following cases:
<br>- if periodical audits reveal nonconformities of serious nature;
<br>- if the certificate holder fails to comply with the due settlement of his financial obligation;
<br>- if inadequate measures are taken by the certificate holder in case of suspension.
<br><br>6.3.<u>Cancellation</u>
 <br>The certificate will be cancelled if the certificate holder does not wish to prolong the agreement.
 AITF will withdraw/ cancel the certificate by informing the certificate holder in writing.
    <br>
<br><b>7.MISCELLANEOUS</b>
<br>7.1 The client shall Inform AITF without delay, of matters that may affect the capability of the management 
system to continue to fulfill the requirements of the standard used for certification. These include: legal, 
commercial, organizational status or ownership, organization and management (e.g. key managerial, decision-making 
or technical staff), contact address and sites, scope of operations under the certified management system, and 
major changes to the management system and processes.
<br><br>7.2 Facilitate scheduling and conducting of - 1) yearly surveillance audits. 2) audits at short notice to
investigate complaints or in response to changes or as follow up on suspended clients, by AITF. 3)  The first 
surveillance audit following the initial certification audit becomes due in the 10th month and the second 
surveillance audit in the 22nd month after the date of conduct of initial certification audit. Client is required 
to intimate sufficiently in advance for the dates of the surveillance audits. The date of the first surveillance 
audit following the initial certification shall not be more than 12 months from the certification decision. Any 
advance intimations or reminders, which AITF may send to the client regarding the surveillance audits due, will be 
an act of courtesy and non-receipt of such intimations or reminders by the client shall not be accepted as a 
justification by the client for any delay in the surveillance audits.
<br><br>- Permit the presence of observers & trainee auditors if assigned by AITF during an audit.
<br>- Client shall permit assessors of the accreditation body to witness the audit being carried out by the AITF 
assessors at the client\'s premises or to visit the client\'s premises independently as per the requirements of the
accreditation body. The client shall make at its cost the necessary logistic arrangements including accommodation 
and local travel for the accreditation body assessors. For the assessment like OHSMS, the individuals responsible for legal accountability, 
responsible for employee health monitoring, and relevant to health and safety matters, shall available during audit process and at closing meeting.
 <br><br>7.3 The planning of the audit e.g. mandays, audit scheduling- are based on the information provided by the 
 client at the time of submitting the application. For any changes in planning which may necessitate increase in the
 mandays requirement and / or visits to additional Site an additional amount will be charged, as will be decided by
 the AITF.
 <br><br>7.4 AITF issues the certificate based on the recommendation made by the audit team and subsequent review of
 the audit report by certification decision committee. AITF shall have the right to ask for any further 
 clarifications from the client and/or make a follow-up visit, if necessary.
 <br><br>7.5 Changes to the applicable ISO 9000 standard series must be implemented by the client within the time 
 frame specified by AITF\'s accreditation body in order to maintain certification. Secondly, the resultant changes 
 to the client\'s management system must be evaluated by AITF for their adequacy and implementation.  
<br><br>7.6 Once certified, AITF may put up the client\'s certification status on its website or provide to any third party on request.
<br><br>7.7 In the events of multi-site certification (same management operate same management system at a 
number of sites under controlled and administered centralisely with the same scope). Failure to maintain the system
at any Site places the entire certification at the risk of being withdrawn.
<br><br>7.8 Client shall inform AITF without delay, of any serious incident or breach of regulation if observed or any such enquiry, investigation that necessitating the involvement of a competent regulatory authority. 
In such case AITF may conduct special audit on short intimation and after the assessment AITF reserve its rights of potential suspension or withdrawal of the relevant certification like OHSMS, on the basis of audit observation.
<br><br></p>
';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


// reset pointer to the last page
$pdf->lastPage();

// add a page
$pdf->AddPage();

$html = '
        <br><br><br><b><u>FEES STRUCTURE</u> </b> (Management system assessment Fees)
       This Fees structure is to provide assessment service as per agreement and fees structure Sr. No. A - H of
       following chart as applicable to client till period of three years certification cycle. 
        <br><br><b>For organisation: </b> ' . $orgName . '
        <br><br><b><u>Site/s to be audited</u>: </b> ' . $address . '
        <br><br><b><u>Scope </u>:</b>' . $scope . '
         <br><br><b><u>Assessment Standard/s </u>:</b>' . $assessmentstandard . '
         <br><br><b><u> Technical Area </u>:</b>' . $naceclass . '
        <br><br><b><u>Accreditation</u>-' . $accreditation . '</b>
      <br><br><br> 
<table border="1"  cellpadding="3" cellspacing="0" >

<tr>
        <td  colspan="1" align="center" valign="certer" height="30">Sr. </td>
        <td  colspan="5" align="center" valign="certer" height="30">Particulars With Professional Fees</td>
        <td  colspan="2"  align="center" valign="certer" height="30">Cost (INR.)</td>
        
 
</tr> 
<tr height="15">
        <td rowspan="4" align="center" valign="certer" height="30">A. </td>
        <td colspan="5" valign="certer" height="30">Application: (Fee Covers Administration Costs &amp; is non refundable)</td>
        <td colspan="2" align="center" valign="certer" height="30">@Rs. <span> ' . $refundable_amount . '</span> </td>
 
</tr> 
<tr>
       
        ' . $stage1row . '
        <td colspan="2" align="center" valign="certer" height="30">@Rs. <span> ' . $audit_stage_i . '</span> </td>
 
</tr> 
<tr>
        ' . $stage2row . '
        <td colspan="2" align="center" valign="certer" height="30">@Rs. <span> ' . $audit_stage_iI . '</span> </td>
 
</tr>

<tr>
       
        <td  colspan="5" valign="certer" height="30"><b>Total Cost of Initial Assessment :</b></td>
        <td colspan="2" align="center" valign="certer" height="30">@Rs. <span> ' . $initial_assessment . '</span> </td>
 
</tr>
<tr>
        <td colspan="1" align="center" valign="certer" height="30">B. </td>
        <td colspan="8" valign="certer" height="30">Maintenance of certification :
        <br>Total two surveillance audits before interval of each 12 months from the initial certification decision (Registration) date. The cost of surveillance <b>@Rs.</b> <span> ' . $certification_maintenance . '</span>/- for each year.
        </td>
        
 
</tr> 
<tr>
        <td colspan="1" align="center" valign="certer" height="50">C. </td>
        <td colspan="8" valign="certer" height="50">Follow up Visits :
        <br>If non-conformance reported at Initial or Surveillance audit and for the verification of corrective actions
            follow-up visit required, such visit will be charged on the daily rate basis (applicable at the time of visit.)
        </td>
       
 
</tr> 
<tr>
        <td colspan="1" align="center" valign="certer" height="50">D. </td>
        <td colspan="8" valign="certer" height="50">Special Surveillance :
        <br>A fee based on the daily rate applicable at the time of visit will be chargeable.
        </td>
 
</tr> 

<tr>
        <td colspan="1" align="center" valign="certer" height="50">E. </td>
        <td colspan="8" valign="certer" height="50">Recertification :
        <br>This fee will be quoted by AITF prior to the expiration of current certification cycle with new agreement.
        </td>
 
</tr> 
<tr>
        <td colspan="1" align="center" valign="certer" height="50">F. </td>
        <td colspan="8" valign="certer" height="50">Travel and living expenses :
        <br>Auditor travelling expenses will charge extra.
        </td>
        
 
</tr> 

<tr>
        <td colspan="1" align="center" valign="certer" height="50">G. </td>
        <td colspan="8" valign="certer" height="50">GST is applicable and charge extra as per governments specified regulations and notifications. 
         </td>
 
</tr> 
<tr>
        <td colspan="1" align="center" valign="certer" height="50">H. </td>
        <td colspan="8" valign="certer" height="50">In the event of revision of standard and if transition required for existing certification while surveillance/re-certification/special audit, mandays will be applicable as per published guideline for transition process by AITF on its website - www.aitf.co.in
         </td>
 
</tr> 
</table>
<br><br><br>
<table border="1"  cellpadding="2" cellspacing="0" >
<tr>
        
        <td colspan="4" >CLIENT :  ' . $orgName . '
        <br>Name &amp; Designation :
        <br>
        <br>
        <br>
        <br>Client acceptance -Authorise sign with duly stamped
        </td>
        <td colspan="4" >A.I.T.F. MANAGEMENT CERTIFICATION LIMITED
        <br>
        <br>
        <br>
        <br>
        <br>A.I.T.F. (Authorised Signatory).
        <br>
        </td>
 
</tr> 

</table>
';


// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Print a table
// add a page
$pdf->AddPage();

//html content Start Here

$html = '<br>
 <p  style="text-align: justify; text-justify: inter-word;">
 9.<b>BASIC SERVICE DETAILS</b>
 <br><b>A.<u>Scope of Service</u></b>
 <br>The management system certification applies to the industrial and service sectors
 <br>
 <br>The scope of the certification service is to assess and certify that a management system 
 complies with the requirements of a specified standard and the AITF Policies.
 <br>
 <br>The Management System Certification contains four main elements:
 <br>- review of the documented information (Stage 1 audit) conducted with an visit to the client\'s premises;
 <br>- initial system audit at the auditee’s premises in order to verify conformance between the system as 
 documented and its implementation: this may include audits in order to follow-up corrective actions;
<br>- issue of the certificate of conformity;
<br>- Periodical audits of the system throughout the certificate\'s validity period, in order to verify that the 
system is being maintained.

<br>
<br>Upon completion of each stage as described above a report is issued to the client. On satisfactory completion 
of the certification assessment a certificate of conformity will be issued to the audited organisation. The 
certificate of conformity with its appendices contains the following Information:
<br>- certificate number;

<br>- name, Site[s] and country of the certified company;

<br>- applicable standard;

<br>- product or service range (Scope) to be covered by certification;

<br>- certificate registration (effective) date and period of validity

<br>- conditions of certification;

<br>- Conditions and regulations for the use of the certificate/ certification marks.
<br>

<br><b>B.<u>Nonconformity</u></b>
<br>The auditor will categories all the nonconformities and will also report any observations found during the audit.
<br>
<br><u>Major Non-conformance: NC (M)</u>
<br>Nonconformity  that affects the capability of the management system to achieve the intended results, such finding is a major NC:
<br>- If there is a significant doubt that effective process control is in place, or that products or services will meet specified requirements; 
<br>- Failure to fulfill one or more requirements of the management system standard, or Non meeting statutory / regulatory requirements
<br>- Not taking corrective actions for the previous NCRs
<br>- Repetitive minor non-conformances of similar nature across the organization/ across the sites sampled.
<br>- Number of defective samples is more than 50% of total samples size.
<br>- Mandatory requirements of the standard not covered.
<br>
<br><u>Minor Non-conformance: NC (m)</u>
<br>Nonconformity that does not affect the capability of the management system to achieve the intended results:
 <br>- Evidence of implementation observed except isolated cases.
 <br>- Defective samples less than 50% of total sample size considered for audit.
<br><u>An observation is defined as:</u>
        <br>
        <br>A statement of concern by the auditor as an “areas of concern”, which does not allow itself to be 
        recorded as nonconformity, but which should be investigated. It is possible that nonconformity may arise if 
        investigation result not converts to conformity.
        <br>
        <br><b>C.<u>Corrective actions</u></b>
        <br>
        <br>The auditee is expected to take actions for the nonconformities identified during the audit, if any, 
        Depending on the nature and degree of the nonconformities, a time scale will be specified for implementation
        of the corrective actions, if not immediately corrected.
       
        <br>
        <br><b><u>Major Nonconformance </u></b> 
        <br><b>•	Initial audit:</b>
        NC closer shall be carried out with verification of evidence within 90 days from the date of audit. 
        however in the event if additional time required for closer of any major nonconformity 
        (i.e. verification and acceptance of implementation of corrections and corrective actions by AITF) 
        shall not exceed 6 months after the last day of stage 2, if it exceeds then AITF required to conduct 
        another full stage 2 for recommend the certification, however full stage 2 audit shall be conducted 
        within 3 months. If this is not the case then the existing certification process will be terminated and 
        client has to apply for fresh certification.  
      
        
</p>';


// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();


// add a page
$pdf->AddPage();
$html = '<br><p  style="text-align: justify; text-justify: inter-word;">
        <b>•	Surveillance/ Recertification: </b>
       NC closer reviewed and accepted with verification of evidence within 90 days from the date of audit. 
       However in the event if additional time required for closer of any major nonconformity can be provide but
       shall not exceed 6 months from the date of suspension for surveillance and from the date of expiration in 
       case of recertification to restore. If it exceeds, certification will be withdrawal.
        <br><br><b><u>Minor Nonconformance Rules:</b></u>
        <br><b>•	Initial audit: </b>
        reviewed and accepted clients plan for correction and corrective action with root-cause analysis and 
        verification of evidence on next audit. Time period for submission of action plan for minor NC is maximum 
        up to 90 days. however in the event if additional time required for verification and acceptance to clients 
        plan for corrections and corrective actions, shall not exceed 6 months after the last day of stage 2 If it 
        exceeds then AITF required conducting another onsite audit limited to NC closer for further recommend/ 
        continuation for certification, however such verification shall be completed within next 3 months. 
        If this is not the case then the existing certification process will be terminated.
        <br><b>•	Surveillance/ Recertification: </b>
        Clients plan for correction and corrective action with root-cause analysis shall be reviewed and accepted 
        within 90 days from the date of audit. In the event if additional time required, can be provide but shall 
        not exceed 6 months from the date of suspension for surveillance and from the date of expiration in case of 
        recertification to restore. If it exceeds, certification will be withdrawal. 
        The effectiveness of NC closer with evidence verification for minor NC will be carried out during next periodical audit.

        <br><br><b>D.<u>Certificate of conformity and retention</u></b>        
        <br>A certificate of compliance will be issued upon acceptance of the system documentation and when audit 
        results prove that the implementation of the system is in accordance with the requirement of applicable 
        standard and that nonconformities are cleared or answered to the satisfaction of the auditor.
        <br>
        <br>The Managing Director of the AITF shall decide upon approval of the certification and sign the 
        certificate of conformity. The certificate of conformity, dated on granted the certification decision, will 
        be given a validity period of three years, provided continued fulfillment of certification conditions.
        <br>
        <br>A periodical audit plan will be agreed, specifying AITF\'s further involvement during the validity period
        
        <br><b>E.<u>Renewal of the certificate</u></b>
        
        <br>The holder of the certificate is to apply for renewal of the certificate at least two months before the
        period of validity expires. A renewal audit, similar to the initial audit, will be carried out covering all 
        requirements of the standard. A new certificate of conformity and a new periodical audit plan will be issued.
        <br>
        <br><b>F.<u>Audit team</u></b>
      
        <br>Initial, surveillance, and recertification audits are to be planned and performed by a lead auditor, who 
        is qualified according to AITF\'s qualification criteria. If necessary he may be assisted by one or more auditors.
        <br>
        <br>In case of a periodical audit, the lead auditor may appoint an auditor to execute the audit on his own, under the 
        supervision of the lead auditor.
        
        <br>The audit team shall be composed in such a way that the necessary expertise is available in the team to 
        cover the specific line of business of the auditee. In order to accomplish this, AITF approved technical 
        specialists may supplement the audit team. Such a specialist may be an AITF employee or an advisor from
        outside AITF. It is the responsibility of the lead auditor to decide on the use of specialists in the audit 
        team.
        <br>
        <br><b>G.<u>Control of mark</u></b>
        
        <br>Requirements to the use of the AITF and accreditation authority marks are given in the guideline G 1.02 -
        for client to use of logo (AITF and accreditation mark) belonging to the certificate of conformity.
        <br>
        <br><b>H.<u> Stake holder committee (SHC) </u></b>
       
        <br>AITF has established a committee of experts. The members of the committee are representatives of the 
        interested parties from industry. The committee evaluates and comments on the certification service quality and scope of AITF\'s 
        management system certification services.
        <br>
        <br><b>I.<u>Confidentiality and independence</u></b>
        
        <br>Confidentiality is committed and facilitated through signed commitments by personnel and procedures 
        requiring limited controlled distribution of records and properly lockable facilities for archiving.
        <br>
        <br>In addition to the restriction of the circulation of documents of the auditee and the certifying unit, 
        AITF wish to advise that any confidential Information arising from any contact, visit or audit will not be 
        disclosed to any third party except where the auditee has agreed and when it is required by the 
        accreditation authority.<br>Members of the audit team, who have been involved in a company as employee or consultant in this field, 
        shall not participate in an audit of the same company for a period of minimum two years.
      </p></font>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
$pdf->Output('Csa.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
?>
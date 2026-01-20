<?php


/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');


// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
    
    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';
    //Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES.'logo_example.jpg';
        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        //SubTitle
        $this->SetFont('helvetica', 'B', 10);
        $this->Ln(7);
        
        $this->Cell(0, 15, 'SCHEDULE –1 st Surveillance Audit', 0, false, 'C', 0, '', 0, false, 'M', 'M');
       
       //page border
       $this->writeHTMLCell($w='', $h='', $x='', $y='', $this->header, $border=0, $ln=0, $fill=0, $reseth=true, $align='L', $autopadding=true);
       $this->SetLineStyle( array( 'width' => 0.40, 'color' => array(0, 0, 0)));


        
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('SCHEDULE –1 st Surveillance Audit');
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
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();


//html content Start Here
$html='<br>
       <br> 
    <table border="1"  cellpadding="4" cellspacing="0" width="100%" >
     <tr>
        <td colspan="2">
        <br><b>Client Name:</b>
         <br>Value
        </td>
        <td colspan="4">
        <br>
        <br>
        Value
        </td>
        <td colspan="2">
        <br><b>Standard: </b>
            Value
        </td>
    </tr>   
    <tr>
        <td colspan="2">
        <br><b>Address:</b>
        </td>
        <td colspan="4">
        <br>
        <br>
        Value
        </td>
        <td colspan="2">
        <br><b>Date(s): </b>
        Value
        </td>
    </tr>   
     <tr>
        <td colspan="2">
        <br><b>Scope of Certification: </b>
        </td>
        <td colspan="4">
        <br>
        <br>
        Value
        </td>
        <td colspan="2">
        <br><b>Mandays:</b>
        Value
        </td>
    </tr>   
     <tr>
        <td colspan="2">
        <br><b>Management Rep.: </b>
        </td>
        <td colspan="6">
        <br>
        Value
        </td>
    </tr>   
    <tr>
        <td colspan="2">
        <br><b>Auditor/s Name: </b>
        </td>
        <td colspan="6">
        <br>
        Value
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
        <br><b>Auditor</b>
        </td>
    </tr>   
   

 </table>
 

 <br>
<table border="1"  cellpadding="4" cellspacing="0" width="100%" >
   
<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>9.00
        </td>
        
        <td colspan="4">
         <br>Arrive at site
        </td>
        
        <td colspan="1">
        <br>
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>   

<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>9.15
        </td>
        
        <td colspan="4">
         <br><b>Opening meeting with senior management,</b> Preparation
            and planning for audit 
        </td>
        
        <td colspan="1">
        <br> -
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>   
<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>9.30
        </td>
        
        <td colspan="4">
         <br><b>Plant/s Tour /Facilities</b> 
        </td>
        
        <td colspan="1">
        <br> -
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>   

<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>9.45
        </td>
        
        <td colspan="4">
         <br><b>Top Management Function</b> 
         <br ><span style="text-align: justify;text-justify: inter-word;">Q. Policy &amp; Objectives, Management review, Customer
            focus, Responsibility, authority, communication, Legal
            compliance of the organisation</span>
        </td>
        
        <td colspan="1">
        <br>5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 4.2 
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>   

<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>10.15
        </td>
        
        <td colspan="4">
         <br><b>M.R. Function</b> 
       <br><span style="text-align: justify;text-justify: inter-word;">Document Review Clarifications, Control of out sourced
            processes, Quality Procedures, Doc. &amp; record control,
            Internal audit, CA &amp; PA, Management review, Data analysis,
            QP &amp; QOs Monitoring, Continual Improvement,
            Responsibility &amp; authority, Compliance to Statutory &amp;
            Regulatory (Legal) Requirements applicable to product.</span>
        </td>
        
        <td colspan="1">
        <br>4.1, 4.2,
            8.2.2,8.5.2,
            8.5.3, 5.6, 8.4,
            5.3, 5.4.1 8.5.1,
            5.5.1, 7.2.1, 4.2
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>   

<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>NA
        </td>
        
        <td colspan="4">
         <br><b>Design &amp; Development (If applicable)</b> 
         <br> <span style="text-align: justify;text-justify: inter-word;">Design and Development Process Planning, Input &amp; Outputs,
            Review, Verification, Validation &amp; Changes
            </span>
        </td>
        
        <td colspan="1">
        <br>7.3, 7.5.2, 4.2
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>   


<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>11.45
        </td>
        
        <td colspan="4">
         <br><b>Planning of product/ Service realization,</b> 
         <br> <span style="text-align: justify;text-justify: inter-word;">Including Statutory &amp; Regulatory (Legal) Requirements
            applicable to product, Product standards (e.g. IS ….) licensed
            or stated as applicable
            </span>
        </td>
        
        <td colspan="1">
        <br>7.1, 4.2
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>  

<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>1.30
        </td>
        
        <td colspan="4">
         <br><b>Production/ Service Provision</b> 
         <br> <span style="text-align: justify;text-justify: inter-word;">Planning of product realization, Control of prodn. &amp; service
                provision, Process validation, Identification and traceability,
                Customer property, Monitoring and measurement of
                processes, Data analysis, Control of nonconforming product,
                Concession, Work environment, Safety measures
            </span>
        </td>
        
        <td colspan="1">
        <br>7.1, 7.5.1, 7.5.2,
            7.5.3, 7.5.4, 8.1,
            8.2.3, 8.4 c, 8.3,
            8.3 b, 6.1, 6.4,
            4.2
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>   


 </table>
 
';



// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


// reset pointer to the last page
$pdf->lastPage();



// add a page
$pdf->AddPage();

//html content Start Here
$html='<br>
       <br> 
    <table border="1"  cellpadding="4" cellspacing="0" width="100%" >
    
<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>15.00
        </td>
        
        <td colspan="4">
         <br><b>Maintenance</b> 
         <br><span style="text-align: justify;text-justify: inter-word;">Infrastructure, Preventive, Breakdown maintenance
            </span>
        </td>
        
        <td colspan="1">
        <br>6.3,4.2
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>  

<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>
        </td>
        
        <td colspan="4">
         <br><b>Quality Control</b> 
         <br><span style="text-align: justify;text-justify: inter-word;">Monitoring &amp; Measurement of Product- Receiving, In
                process, Final Inspection and Testing activities, National
                Standard applicability, acceptance criteria (Specifications),
                Control of NC Product, Authorised persons for release of
                product Data analysis
            </span>
        </td>
        
        <td colspan="1">
        <br>
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>  

<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>
        </td>
        
        <td colspan="4">
         <br><b>Purchase</b> 
         <br><span style="text-align: justify;text-justify: inter-word;">Purchase Process, Suppliers selection &amp; evaluation,
                            Purchase Information- Authorisation to issue PO,
                            Verification of Purchased Products, Control of outsourced
                            Processes
            </span>
        </td>
        
        <td colspan="1">
        <br>
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>  

<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>
        </td>
        
        <td colspan="4">
         <br><b>Stores &amp; Dispatch</b> 
         <br><span style="text-align: justify;text-justify: inter-word;">Identification , Handling, Packaging, Storage, Shelf life’
                    Protection, Delivery of Product
            </span>
        </td>
        
        <td colspan="1">
        <br>
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>

<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>
        </td>
        
        <td colspan="4">
         <br><b>Auditor Time</b> 
        
        </td>
        
        <td colspan="1">
        <br>
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>  
           

<tr>
        <td colspan="1">
        <br>XYZ
        </td>
        
        <td colspan="1">
        <br>
        </td>
        
        <td colspan="4">
         <br><b>Closing Meeting</b> 
        
        </td>
        
        <td colspan="1">
        <br>
        </td>
        
     <td colspan="1">
        <br>XYZ
        </td>
        
</tr>  
           

</table>
<span align="center"><b>-- -- - LUNCH: 13.00 to 13.30 -- -- -</b></span>
<br>
<br>
<br>
<span align="right"><b>Prepared by Team Leader:___XYZ___________</b></span>
<br>
<br><b>Note:</b>
<p style="text-align: justify; text-justify: inter-word;"><font size="8">&emsp;&emsp;&emsp;1. Auditors shall verify and report the capability of the Client to meet all Product requirements that forms the basis of the
contract
<br>&emsp;&emsp;&emsp;2. Internal communication, QMS awareness, QOs, documents &amp; records control, to be verified as relevant in all depts. audited.
<br>&emsp;&emsp;&emsp;3. Allot sufficient time for auditing Production/ Service provision and QC depts../ functions. Refer Guideline
document/Checklist F 7.23) as also any other relevant sector specific checklists as applicable.
<br>&emsp;&emsp;&emsp;4. The time indicated is approximate &amp; may change based on audit trails.
<br>&emsp;&emsp;&emsp;5. As per defined surveillance visit schedule the marketing and HR department during 1 st SA and Purchase- Store.
    Dispatch &amp; Maintenance dpt. during 2 nd SA may skip.
<br>
<br>
<br>
<span ><b>The tasks assigned to the Audit Team in connection with this Audit Plan are –</b></span>
<br>•  Examination and verification of the structure, policies, processes, procedures, records and related documents relevant to the
      management system.
    <br>•  Determining if these meet all the relevant requirements relevant to the scope of certification.
   <br>•  evaluating the extent of establishment, implementation and maintenance of the processes and procedures, to provide a
        basis for confidence in the management system.
  <br>•  Communicating any inconsistence between the policies / procedures, objectives and targets and non-conformities for
         necessary action.
  <br>•  Process approach / PDCA / close loop criteria to be followed during audit.
  <br><b>• Surveillance Audit</b> mainly covers - evaluation of conformance, Changes of Certified QMS with the standard’s requirements,

Organisation’s performance against objectives &amp; targets, Legal and regulatory compliance, Process control, internal audit

and Management review, Responsibility for policies, Links between policies, performance objectives and targets.
</font></p>

';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
$pdf->Output('SCHEDULE–1Surveillance Audit.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


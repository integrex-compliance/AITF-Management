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
        
         $this->SetFont('helvetica', 'B', 10);
        $this->Ln(7);
        //SubTitle
        $this->Cell(0, 15, 'REVIEW OF AUDIT APPLICATION', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
       $this->writeHTMLCell($w='', $h='', $x='', $y='', $this->header, $border=0, $ln=0, $fill=0, $reseth=true, $align='L', $autopadding=true);
       $this->SetLineStyle( array( 'width' => 0.40, 'color' => array(0, 0, 0)));

       $this->Line(5, 5, $this->getPageWidth()-5, 5); 

       $this->Line($this->getPageWidth()-5, 5, $this->getPageWidth()-5,  $this->getPageHeight()-5);
       $this->Line(5, $this->getPageHeight()-5, $this->getPageWidth()-5, $this->getPageHeight()-5);
       $this->Line(5, 5, 5, $this->getPageHeight()-5);
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
$pdf->SetTitle('Review Of Audit Application');
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
$html='<p style="text-align: justify;text-justify: inter-word;"><font size="8">
        <table border="1"  cellpadding="4" cellspacing="0" >
        <tr  bgcolor="#DCDCDC">
     <td align="center" colspan="7"><b>Input(From Audit application- F 7.01)</b></td>     
</tr>
  <tr>
        <td colspan="7">
        <br>Application Date:       
        </td>
</tr>        
<tr>
        <td colspan="7">
        <br>Name of the Organisation:       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>Legal Status :
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>Industry  Classification:
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>Whether part of large group:
       
        </td>
</tr>    


<tr>
        <td colspan="7">
        <br>No. Location/s cover under audit:
       
        </td>
</tr>        
<tr>
        <td colspan="7">
        <br>No. Employees: Permanent /Temporary/Contract / Seasonal
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>No. of  Shifts:
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>Products / Services:
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>Out sourced  Processes:
       
        </td>
</tr>    

<tr>
        <td colspan="7">
        <br>Certification Required for standard:
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>Accreditation Requirement:
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>Consultancy services or in-house training wasperformed by whom and period of performed? :
        <br>
        </td>
</tr>    


<tr  bgcolor="#DCDCDC">
     <td align="center" colspan="7"><b>General analysis &amp; requirement</b></td>
     
</tr>

<tr>
        <td colspan="7">
        <br>Exclusion of Standards clauses:
       
        </td>
</tr>        
<tr>
        <td colspan="7">
        <br>Technical  Area:
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>Any Legal Obligations:
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>Conflict of Interest:
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>Is there any Statutory / Regulatory requirements:
       
        </td>
</tr>    

<tr>
        <td colspan="7">
        <br>Is there any customer specific requirement:
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>Organisation mfg/service risk involved (High/Medium/ low) / Class:
       
        </td>
</tr>    
<tr>
        <td colspan="7">
        <br>any other requirements:
        <br>
        </td>
</tr> 

<tr  bgcolor="#DCDCDC">
     <td align="center" colspan="7"><b>Feasibility Analysis</b></td>
     
</tr>
<tr>
        <td colspan="7">
        <br><u>Feasibility &amp; Risk Analysis Considerations</u>
      <br><br>AITF team considered the following questions, intended to be all-inclusive in performing
      <br>certification assessment service. The audit application form and/or Information provided
      <br>by client have been used as a basis for analyse. All &quot;no&quot; answers are supported with comments 
      <br>identifying our concerns and/or proposed change to enable requirements with the decision of 
      <br>accepting/Refusing application.
        </td>
</tr> 


<tr>
        <td colspan="1" align="center">Sr. </td>
        <td colspan="4" align="center">CONSIDERATION : </td>
        <td colspan="1" align="center">YES </td>
        <td colspan="1" align="center">NO</td>
 
</tr> 

<tr>
      <td colspan="1" align="center">1. </td>
      <td colspan="4" >Is AITF having adequate capacityfor providing assessment &amp; certification service.</td>
      <td colspan="1" align="center"> </td>
      <td colspan="1" align="center">  </td>
</tr> 

<tr>
      <td colspan="1" align="center">2. </td>
      <td colspan="4" >Does AITF having adequate resources to render the service.</td>
      <td colspan="1" align="center"> </td>
      <td colspan="1" align="center">  </td>
</tr> 

<tr>
      <td colspan="1" align="center">3. </td>
      <td colspan="4" >Is product/service (Scope) adequately defined in application to enable feasibility.</td>
      <td colspan="1" align="center"> </td>
      <td colspan="1" align="center">  </td>
</tr> 

<tr>
      <td colspan="1" align="center">4. </td>
      <td colspan="4" >Within scope.</td>
      <td colspan="1" align="center"> </td>
      <td colspan="1" align="center">  </td>
</tr> 

<tr>
      <td colspan="1" align="center">5. </td>
      <td colspan="4" >AITF Having scope auditor including competent team for the certification decision.</td>
      <td colspan="1" align="center"> </td>
      <td colspan="1" align="center">  </td>
</tr> 

<tr>
      <td colspan="1" align="center">6. </td>
      <td colspan="4" >Exclusion of Standard clause/s Justified.
      <br>(Subject to verification during stage I audit)</td>
      <td colspan="1" align="center"> </td>
      <td colspan="1" align="center">  </td>
</tr> 

<tr>
      <td colspan="1" align="center">7. </td>
      <td colspan="4" >Verified that there is no any risk involved regarding Statutory, Regulatory, and Legal
                        obligations.</td>
      <td colspan="1" align="center"> </td>
      <td colspan="1" align="center">  </td>
</tr> 

<tr>
      <td colspan="1" align="center">8. </td>
      <td colspan="4" >Verified that there is no risk involved regarding conflict of interest or any breach of AITF
                       policies</td>
      <td colspan="1" align="center"> </td>
      <td colspan="1" align="center">  </td>
</tr> 



</table>


</font></p>';




// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


//html content Start Here

$html='<p style="text-align: justify;text-justify: inter-word;"><font size="8">
    <table border="1"  cellpadding="4" cellspacing="0" >

<tr>
        <td colspan="7">
        <br><u>Conclusion</u>:
        <br>
        </td>
</tr> 

<tr>
        <td colspan="7">
        <br>A. Feasible: Certification service can perform.
        <br>B. Feasible but changes recommended to perform the service(see attached)
        <br>C. Not Feasible AITF refuse to application
        <br>
        </td>
</tr> 
<tr>
        <td colspan="7">
        <br>In the event of conclusion(B or C)<u>comments</u>:
        <br>
        </td>
</tr> 

<tr>
        <td colspan="7">
        <br><b><u>Review In the Event of Multisite with eligibility of sampling creator:</u></b>
        <br>
        <br>
        </td>
</tr>

<tr   bgcolor="#DCDCDC">
        <td colspan="7">
        <br><b>Review Outcome</b>(for inputsto agreement &amp; quotation)
        </td>
</tr> 


<tr>
        <td colspan="7">
        <br>Assessment Standard:
        
        </td>
</tr> 

<tr>
        <td colspan="7">
        <br>IAF Code / NACE Code with class :
       
        </td>
</tr> 
<tr>
        <td colspan="7">
        <br>Accreditation:
        
        </td>
</tr> 

<tr>
        <td colspan="7">
        <br>Proposed scope of certification:
        <br>
        <br>
        </td>
</tr>

<tr>
        <td colspan="7">
        <br>Proposed month/year for Initial Audit / Recertification Audit:
        
        </td>
</tr> 

<tr>
        <td colspan="7">
        <br>Man-days required as per IAF MD 5 :
       
        </td>
</tr> 
<tr>
        <td colspan="7">
        <br>Reducing Factor,Man-days:
        
        </td>
</tr> 

<tr>
        <td colspan="7">
        <br>Justification of Reducing:
        <br>
        <br>
        </td>
</tr>
<tr>
        <td colspan="7">
        <br>Additive Factor If any, Man-days:
        
        </td>
</tr> 

<tr>
        <td colspan="7">
        <br><u>Actual Effective man-days required:</u>
       
        </td>
</tr> 
<tr>
        <td colspan="7">
        <br>Requirement of Technical Expert:
        
        </td>
</tr> 
<tr>
        <td colspan="7">
        <br>Were Travel Logistics Discussed &amp; finalized:
        
        </td>
</tr> 

<tr>
        <td colspan="7">
        <br>Quotation AmountCost (INR.): (Re/Certification+1 st surveillance+ 2 nd Surveillance):
        
        </td>
</tr> 
 <tr>
       
        <td colspan="5">
        <br>Application:(Fee Covers Administration including work of enquiry realisation Costs &amp; is non – refundable):
        </td>
        <td colspan="2">
        <br>@Rs.
        
        </td>
</tr>  

<tr>
        <td colspan="5">
        <br>Initial Audit (Stage 1) – Documentation Review and/or Adequacy Audit:
        </td>
        <td colspan="2">
        <br>@Rs.
        
        </td>
</tr>    

<tr>
        <td colspan="5">
        <br>Initial Audit (Stage 2) – Certification/Recertification Audit OR________________Audit:
        </td>
        <td colspan="2">
        <br>@Rs.
        
        </td>
</tr>    

<tr>
        <td colspan="5">
        <br><b>Total Cost of Initial Assessment</b>
        </td>
        <td colspan="2">
        <br>@Rs.
        
        </td>
</tr>    

<tr>
        <td colspan="5">
        <br>Maintenance of Certification:Total Two surveillance audits with interval of 12 monthsfrom the certification stage-II audit. The cost of surveillance For each Year: 
        </td>
        <td colspan="2">
        <br><b>@Rs.</b>
        
        </td>
</tr>    

<tr>
        <td colspan="7">
        <br>Allotment of Agreement Number :
        </td>
        
</tr>    

<tr>
        <td colspan="7">
        <br>Comments if any:
        <br>
        <br>
        </td>
        
</tr>    
</table>

<br><br><span>Review Date:  </span><br>

<br><br><span>Name: </span><br>

<br><br><span>Sign: </span>

</font></p>';


// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');



//Close and output PDF document
$pdf->Output('ReviewOfAuditApplication.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
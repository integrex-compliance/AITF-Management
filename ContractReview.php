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

    //Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES.'logo_example.jpg';
        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-8);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 7, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Contract Review');
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
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

// set some text to print
$txt = <<<EOD
    CONTRACT REVIEW
EOD;

// print a block of text using Write()
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);

// create some HTML content
$html='<br>
       <br> 
    <table border="1"  cellpadding="4" cellspacing="0" width="100%" >
    <tr>
        <td colspan="2.5">
        <br>
        <br>Name of the customer:
      
        <br>
        </td>
        <td colspan="4.5">
        <br>
        <br>
        Value
        <br>
        </td>
       
    </tr>
    <tr>
        
        <td colspan="2.5">
        <br>
        <br>Customer location/Address:
       
        <br>
        </td>
        <td colspan="4.5">
        <br>
        <br>
        Value 
        <br>
        </td>
        
    </tr>
    <tr>
        
        <td colspan="2.5">
        <br>
        <br>Contract Agreement No:
       
        </td>
        <td colspan="4.5">
        <br>
        <br>
        Value 
        </td>
        <br>
    </tr>
    
</table>
';



// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');


// create some HTML content
$html='<table border="1"  cellpadding="4" cellspacing="0" >
        
<tr>
        <td rowspan="2"><br>
        <br>
        <br>
        <br>Contract review check point <br><br>
        
        </td>
        <td align="center" colspan="4">Review comments ( √ Mark at appropriate )</td>
        <td rowspan="2"><br>
        <br>
        <br>Remark and follow up actions,if any. <br><br>
        </td>
</tr>
  
<tr>
        <td><br><br>Requirement stated & accepted </td>
        <td><br><br>Requirement not stated to be clarified </td>
        <td><br><br>Requirement stated and not acceptable to be resolved </td>
        <td><br><br>Not stated /Not acceptable </td>
</tr>

<tr  bgcolor="#DCDCDC">
     <td align="center" colspan="6"><b>General</b></td>
</tr>

<tr>
        
        <td colspan="1">Process of certification activity </td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr>    

<tr>
        <td colspan="1">Scope for certification &amp; standard </td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr>  

<tr  bgcolor="#DCDCDC">
     <td align="center" colspan="6"><b>Service</b></td>
</tr>
 <tr>
        
        <td colspan="1">Service agreement for management system assessment
        
        </td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr> 
<tr  bgcolor="#DCDCDC">
     <td align="center" colspan="6"><b>Processes</b></td>
</tr>

<tr>
        
        <td colspan="1">Complaints &amp; Appeals </td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr>    

<tr>
        <td colspan="1">Suspension, Withdrawal, Cancellation of certificate standard </td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr> 

<tr>
        <td colspan="1">Miscellaneous</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
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

// create some HTML content
$html='<br><br>
    <table border="1"  cellpadding="4" cellspacing="0" >
<tr  bgcolor="#DCDCDC">
     <td align="center" colspan="6"><b>Basic service details</b></td>
</tr>

<tr>
        
        <td colspan="1">Scope of service </td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr>    

<tr>
        <td colspan="1">Nonconformity </td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr> 

<tr>
        <td colspan="1">Corrective actions &amp; surveillance audit</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr>
<tr>
        
        <td colspan="1">Certificate of conformity &amp; Retention </td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr>    

<tr>
        <td colspan="1">Renewal of the certificate</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr> 

<tr>
        <td colspan="1">Audit team</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr> 

<tr>
        
        <td colspan="1">Control of mark </td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr>    

<tr>
        <td colspan="1">Stake holder committee(SHC)<br>(Impartiality committee)</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr> 

<tr>
        <td colspan="1">Confidentiality and independence</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
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

// create some HTML content
$html='<br><br>
    <table border="1"  cellpadding="4" cellspacing="0" >
<tr  bgcolor="#DCDCDC">
     <td align="center" colspan="6"><b>Commercial</b></td>
</tr>

<tr>
        
        <td colspan="1">Initial Certification</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr>    

<tr>
        <td colspan="1">Maintenance of Certification</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr> 

<tr>
        <td colspan="1">Special Surveillance</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr>
<tr>
        
        <td colspan="1">Recertification</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr>    

<tr>
        <td colspan="1">Follow – up Visits</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr> 

<tr>
        <td colspan="1">Travel and Living Expenses</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr> 

<tr>
        
        <td colspan="1">Service tax</td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value   <br><br></td>
        <td colspan="1"><br><br> value <br><br></td>
</tr>    

</table>
<br><br><span>Conclusion:  </span><br>

<br><br><span>Sign: </span><br>

<br><br><span>Reviewed by: </span>

';


// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');



     
//Close and output PDF document
$pdf->Output('ContractReview.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
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
include_once('dbConnect.php');
date_default_timezone_set('Asia/Kolkata');
$feeCovers = "";
$stage1Audit = "";
$stage2Audit = "";
$maintenance = "";
$total_amt = "";
$quotationDate = "";
$quotationid = "";
$quot_condition = "";

$customer = "SELECT * FROM customer_info WHERE id = :customerid ";
$statement = $db->prepare($customer);
$statement->bindParam(':customerid', $_POST['quoteCustomerId']);
$statement->execute();
$customerInfo = $statement->fetch();

$firm = "SELECT * FROM consulting_firm";
$statement = $db->prepare($firm);
//$statement->bindParam(':firmid', $_POST['consultingFirmId']);
$statement->execute();
$firmInfo = $statement->fetch();

if (isset($_POST['quotationId']) && !empty($_POST['quotationId'])) {
    $quotationid = $_POST['quotationId'];
    $total_amt = $_POST['feeCovers'] + $_POST['stage1Audit'] + $_POST['stage2Audit'];
    $quotationUpdate = "UPDATE quotation_detail SET quotation_date = :quotationDate, free_covers = :freeCovers, "
            . "stage_i_audit = :stage1, stage_ii_audit = :stage2, total_cost = :totalCost, maintenance = :maintenance WHERE quotation_id = :id";
    $statement = $db->prepare($quotationUpdate);
    $statement->bindParam(':id', $_POST['quotationId']);
    $statement->bindParam(':quotationDate', date("Y-m-d", strtotime($_POST['quotationDt'])));
    $statement->bindParam(':freeCovers', $_POST['feeCovers']);
    $statement->bindParam(':stage1', $_POST['stage1Audit']);
    $statement->bindParam(':stage2', $_POST['stage2Audit']);
    $statement->bindParam(':totalCost', $total_amt);
    $statement->bindParam(':maintenance', $_POST['maintenanceAmt']);
    $statement->execute();

    $dltCondition = "DELETE FROM quotation_condition WHERE quotation_id = :id";
    $statement = $db->prepare($dltCondition);
    $statement->bindParam(':id', $_POST['quotationId']);
    $statement->execute();

    $feeCovers = $_POST['feeCovers'];
    $stage1Audit = $_POST['stage1Audit'];
    $stage2Audit = $_POST['stage2Audit'];
    $maintenance = $_POST['maintenanceAmt'];
    $quotationDate = $_POST['quotationDt'];
} else if (isset($_POST['quotation_id']) && !empty($_POST['quotation_id'])) {
    $quotationid = $_POST['quotation_id'];

    $quotation = "SELECT * FROM quotation_detail WHERE quotation_id = :id";
    $statement = $db->prepare($quotation);
    $statement->bindParam(':id', $quotationid);
    $statement->execute();
    $quotationInfo = $statement->fetch();

    $quotCondition = "SELECT * FROM quotation_condition WHERE quotation_id = :id";
    $statement = $db->prepare($quotCondition);
    $statement->bindParam(':id', $quotationid);
    $statement->execute();
    $quot_condition = $statement->fetchAll();

    $feeCovers = $quotationInfo['free_covers'];
    $stage1Audit = $quotationInfo['stage_i_audit'];
    $stage2Audit = $quotationInfo['stage_ii_audit'];
    $maintenance = $quotationInfo['maintenance'];
    $quotationDate = $quotationInfo['quotation_date'];
    $total_amt = $quotationInfo['total_cost'];
} else {
    $total_amt = $_POST['feeCovers'] + $_POST['stage1Audit'] + $_POST['stage2Audit'];

    $quotation = "INSERT INTO quotation_detail (customer_id, quotationno, quotation_date, free_covers, stage_i_audit, "
            . "stage_ii_audit, total_cost, maintenance) VALUES (:customer, :quotno, :quotationDate, :freeCovers, "
            . ":stage1, :stage2, :totalCost, :maintenance)";
    $statement = $db->prepare($quotation);
    $statement->bindParam(':customer', $_POST['quoteCustomerId']);
    $statement->bindParam(':quotno', $firmInfo['firm_quotation_no']);
    $statement->bindParam(':quotationDate', date("Y-m-d", strtotime($_POST['quotationDt'])));
    $statement->bindParam(':freeCovers', $_POST['feeCovers']);
    $statement->bindParam(':stage1', $_POST['stage1Audit']);
    $statement->bindParam(':stage2', $_POST['stage2Audit']);
    $statement->bindParam(':totalCost', $total_amt);
    $statement->bindParam(':maintenance', $_POST['maintenanceAmt']);
    $statement->execute();

    $feeCovers = $_POST['feeCovers'];
    $stage1Audit = $_POST['stage1Audit'];
    $stage2Audit = $_POST['stage2Audit'];
    $maintenance = $_POST['maintenanceAmt'];
    $quotationDate = $_POST['quotationDt'];

    $sql = "SELECT max(LAST_INSERT_ID(quotation_id)) as id From quotation_detail";
    $statement = $db->prepare($sql);
    $statement->execute();
    $row = $statement->fetch();
    $quotationid = $row['id'];

    $firmQuot = $firmInfo['firm_quotation_no'] + 1;
    $firmQuotNo = "UPDATE consulting_firm SET firm_quotation_no = :quot WHERE firm_id = '1' ";
    $statement = $db->prepare($firmQuotNo);
    $statement->bindParam(':quot', $firmQuot);
    $statement->execute();
}

if (isset($_POST['conditionsHidden'])) {
    $conditions = explode('~', $_POST['conditionsHidden']);
    for ($i = 0; $i < count($conditions); ++$i) {
        $quotationCondition = "INSERT INTO quotation_condition (quotation_id, condition_desc) VALUES (:id, :condition)";
        $statement = $db->prepare($quotationCondition);
        $statement->bindParam(':id', $quotationid);
        $statement->bindParam(':condition', $conditions[$i]);
        $statement->execute();
    }
}

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES . 'logo_example.jpg';
        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', '', 20);
        // Title
        $this->Cell(0, 15, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('helvetica', 'B', 12);
        $this->Ln(7);
        $this->Cell(0, 15, 'CERTIFICATION SERVICE AGREEMENT (FEES STRUCTURE)', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

//// set document information
//$pdf->SetCreator(PDF_CREATOR);
//$pdf->SetAuthor('AITF');
//$pdf->SetTitle('CERTIFICATION SERVICE AGREEMENT (FESS STRUCTURE)');
//$pdf->SetSubject('AITF');
//$pdf->SetKeywords('AITF, PDF, example, test, guide');
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
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

// set some text to print
$txt = <<<EOD
   
EOD;

// print a block of text using Write()
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
$addRow = "";
$char = 72;
$count = 0;

if (isset($_POST['conditionsHidden'])) {
    foreach ($conditions as $quotationCondition) {
        $addRow = $addRow . '<tr>
        <td colspan="1" align="center">' . chr($char) . '. </td>
        <td colspan="6" >' . $conditions[$count] . '.
         </td> 
        </tr>';
        $char++;
        $count = $count + 1;
    }
} else {
    foreach ($quot_condition as $quotationCondition){
        $addRow = $addRow . '<tr>
        <td colspan="1" align="center">' . chr($char) . '. </td>
        <td colspan="6" >' . $quotationCondition['condition_desc'] . '.
         </td> 
        </tr>';
        $char++;
        $count = $count + 1;
    }
}

$html = '<table border="1"  cellpadding="4" cellspacing="0" >
<tr>
        <td colspan="7">
        <br><b><u>FEES STRUCTURE</u> –</b> (Management system assessment Fees)
        This Fees structure is for provide assessment service as per signed agreement &amp; fees structure Sr. No. A-G of
        following chart as applicable to client till period of three years &amp; for the standard ISO 9001:2008 and the scope of
        activities, location, IAF.NACE Code.  
        <br><b>For organization: value </b> ' . $customerInfo['customer_name'] . '
        <br><br><b><u>With address (Location(S) to be audited)</u>:</b><br/>' . $customerInfo['address'] . '
        <br><br><b><u>Accreditation</u>-</b> NABCB
        </td>
</tr> 
<tr>
        <td  colspan="1" align="center">Sr. </td>
        <td  colspan="5" align="center">Particulars – With Professional Fees</td>
        <td  colspan="2"  align="center">Cost (INR.) </td>         
</tr> 
<tr>
        <td rowspan="4" align="center">A. </td>
        <td colspan="5">Application: (Fee Covers Administration Costs &amp; is non – refundable)</td>
        <td colspan="2" align="center">' . convertcash($feeCovers) . ' </td>
</tr> 
<tr>       
        <td  colspan="5" >Initial Audit(Stage 1)–Documentation Review and/or Adequacy Audit :</td>
        <td colspan="2" align="center"> ' . convertcash($stage1Audit) . ' </td>
</tr> 
<tr>       
        <td  colspan="5">Initial Audit (Stage 2) – Certification Audit:</td>
        <td colspan="2" align="center"> ' . convertcash($stage2Audit) . ' </td>
</tr>
<tr>       
        <td  colspan="5" ><b>Total Cost of Initial Assessment :</b></td>
        <td colspan="2" align="center"> ' . convertcash($total_amt) . ' </td>
</tr>
<tr>
        <td colspan="1" align="center">B. </td>
        <td colspan="6" >Maintenance of Certification :
        <br>Total Two surveillance audits with interval of 12 months from the certification stage-II audit. The cost of
            surveillance @Rs.' . convertcash($maintenance) . '/- For each Year.
        </td>         
</tr> 
<tr>
        <td colspan="1" align="center">C. </td>
        <td colspan="6" >Follow – up Visits :
        <br>If non-conformance reported at Initial or Surveillance audit and for the verification of corrective actions
            follow-up visit required, such visit will be charged on the daily rate basis (applicable at the time of visit.)
        </td>        
</tr> 
<tr>
        <td colspan="1" align="center">D. </td>
        <td colspan="6" >Special Surveillance :
        <br>A fee based on the daily rate applicable at the time of visit will be chargeable.
        </td>
</tr> 
<tr>
        <td colspan="1" align="center">E. </td>
        <td colspan="6" >Recertification:
        <br>This fee will be quoted prior to the expiration of three – year approval
        </td>
</tr> 
<tr>
        <td colspan="1" align="center">F. </td>
        <td colspan="6" >Travel and Living Expenses :
        <br>For outstation clients, except Pune. Auditor travelling expenses will charge extra in the same service Invoice.
        </td>
</tr> 
<tr>
        <td colspan="1" align="center">G. </td>
        <td colspan="6" >Service Tax is applicable &amp; charge extra as per governments specified regulations &amp; notifications.
         </td> 
</tr> 
' . $addRow . '
<tr>        
        <td colspan="7" align="center" >Note: Kindly sign for approval &amp; send back to us.
         </td> 
</tr> 
</table>
    <table border="1"  cellpadding="4" cellspacing="0" >
    <tr>        
        <td colspan="3.5" >CLIENT–
        <br>Name &amp; Designation :
        <br>
        <br>
        <b>Client acceptance -Authorise sign with duly stamped</b>
        </td>
        <td colspan="3.5" >AITF MANAGEMENT CERTIFICATION LIMITED
        <br>
        <br>
        <br>
        <b>Director</b>        
        </td>
</tr> 
<tr>        
        <td colspan="6" align="center" ><b>Registered office Address</b>:-Devi Indrayani Project, Wing.-B 26, Near IT Park, Talawade,
        <br>Pune-412 119.(PH) 020- 65116677, E mail : aitfmktg@gmail.com , Web – www.aitf.co.in </td>         
</tr>
<tr>        
        <td colspan="7" align="center" >*Subject to Pune Jurisdiction only.
        </td> 
</tr> 
    </table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
$pdf->Output('CsaFessStructure.pdf', 'I');

function convertcash($num) {
    if (strlen($num) > 3) {
        $lastthree = substr($num, strlen($num) - 3, strlen($num));
        $restunits = substr($num, 0, strlen($num) - 3); // extracts the last three digits 
        $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping. 
        $explrestunits = "";
        $expunit = str_split($restunits, 2);
        for ($i = 0; $i < sizeof($expunit); $i++) {
            $explrestunits .= (int) $expunit[$i] . ","; // creates each of the 2's group and adds a comma to the end 
        }
        $thecash = $explrestunits . $lastthree;
    } else {
        $thecash = $num;
    }
    return $thecash . ".00"; // writes the final format where $currency is the currency symbol. 
}

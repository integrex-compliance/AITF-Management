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
require_once __DIR__ . '/include/invoice_mail_helper.php';
aitfLoadPHPMailer();
date_default_timezone_set('Asia/Kolkata');

$invoiceid = "";
$total_amt = "";
$innvoice = "";
$invoiceSvc = "";
$invDate = "";
$netPrice = "";
$svcTax = "";
$swachBharat = "";
$krushiKalyan = "";
$gsts = "";
$firmSign = "";

$customer = "SELECT * FROM customer_info WHERE id = :id";
$statement = $db->prepare($customer);
$statement->bindParam(':id', $_POST['invoiceCustomerId']);
$statement->execute();
$customerInfo = $statement->fetch();
 

$consultingFirm = "SELECT * FROM consulting_firm ";
$statement = $db->prepare($consultingFirm);
$statement->execute();
$firmInfo = $statement->fetch();
$firmSign = $firmInfo["signature"];

if (isset($_POST['invoiceid']) && !empty($_POST['invoiceid'])) {
    $invoiceInfo = "SELECT * FROM invoice_details WHERE invoice_id = :id";
    $statement = $db->prepare($invoiceInfo);
    $statement->bindParam(':id', $_POST['invoiceid']);
    $statement->execute();
    $innvoice = $statement->fetch();
    $proformaprefix = $innvoice['profarma_prefix'];

    $invoiceDetail = "SELECT * FROM invoice_services WHERE invoice_ids = :id";
    $statement = $db->prepare($invoiceDetail);
    $statement->bindParam(':id', $_POST['invoiceid']);
    $statement->execute();
    $invoiceSvc = $statement->fetchAll();

    $invno = $innvoice['invoice_no'];
    $invDate = $innvoice['invoice_date'];
    $netPrice = $innvoice['net_price'];
    $sgst = $innvoice['sgst'];
    $cgst = $innvoice["cgst"];
    $igst = $innvoice['igst'];
    $total_amt = $innvoice['final_amount'];
    $state = $innvoice['state'];
    $stateCode = $innvoice['state_code'];
    if ($innvoice["tax_type"] == "CGST & SGST") {
        $gsts = '<tr><td colspan="2" style="text-align:right">CGST@9%</td><td> ' . $cgst . ' </td></tr>
                <tr><td colspan="2" style="text-align:right">SGST@9%</td><td> ' . $sgst . ' </td></tr>';
    } else if ($innvoice["tax_type"] == "IGST") {
        $gsts = '<tr><td colspan="2" style="text-align:right">IGST@18%</td><td> ' . $igst . ' </td></tr>';
    }
}

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';

    //Page header
    public function Header() {
        // Logo
        // Logo
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        $this->Image($image_file, 17, 5, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 17.5);
        // Title
        $this->SetXY(60, 10);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('times', 'B', 12);
        $this->Ln(7);
        $this->SetXY(60, 15);
        $this->SetTextColor(128, 184, 224);
        $this->Cell(0, 30, '(NABCB ACCREDITED CERTIFICATION REGISTER FOR QMS)', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('times', 'B', 14);
        $this->Ln(7);
        $this->SetXY(60, 20);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 30, '(PROFORMA)', 0, false, 'C', 0, '', 0, false, 'M', 'M');

    }

   
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('times', 'I', 12);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

//        $this->SetFont('helvetica', 'B', 12);
//        $this->Ln(7);
//        $this->Cell(0, 15, 'CERTIFICATION SERVICE AGREEMENT (FEES STRUCTURE)', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Proforma');
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
$pdf->SetFont('times', '', 12);

/// add a page
$pdf->AddPage();


$subtable = '<table border="2" cellspacing="6" cellpadding="4"><tr><td>a</td><td>b</td></tr><tr><td>c</td><td>d</td></tr></table>';

$serviceRow = '';
$count = 0;
//    print_r($invoiceSvc);
foreach ($invoiceSvc as $svc) {
    $serviceRow = $serviceRow . '<tr><td>' . ($count + 1) . '</td><td>' . $svc['service_desc'] . '</td><td>' . $svc['service_amount'] . '</td></tr>';
    $count = $count + 1;
}

//amount convertion function
function convertcash($num, $currency) {
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
    return $currency . $thecash . ".00"; // writes the final format where $currency is the currency symbol. 
}

function convertToWord($number) {
    $no = round($number);
    $point = round($number - $no, 2) * 100;
    $hundred = null;
    $digits_1 = strlen($no);
    $i = 0;
    $str = array();
    $words = array('0' => '', '1' => 'One', '2' => 'Two',
        '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
        '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
        '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
        '13' => 'Thirteen', '14' => 'Fourteen',
        '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
        '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
        '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
        '60' => 'Sixty', '70' => 'Seventy',
        '80' => 'Eighty', '90' => 'Ninety');
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number] .
                    " " . $digits[$counter] . $plural . " " . $hundred :
                    $words[floor($number / 10) * 10]
                    . " " . $words[$number % 10] . " "
                    . $digits[$counter] . $plural . " " . $hundred;
        } else
            $str[] = null;
    }
    $str = array_reverse($str);
    $result = implode('', $str);
    $points = ($point) ?
            "." . $words[$point / 10] . " " .
            $words[$point = $point % 10] : '';
    return $result . " " . $points . " Only";
}


$html = '<br><br>
<table border="1" cellspacing="1" cellpadding="4">	
    <tr >
    <td height="100"><b>TO:</b><br><b>NAME - ' . $customerInfo['customer_name'] . '</b><br>ADDRESS - ' . $customerInfo['address'] . '<br>
        
        <br><b>GSTIN No.: </b>' . $customerInfo['gstin_no'] . '
        
        <br><b>STATE: </b>' . $state . '&nbsp; &nbsp; &nbsp; <b>STATE CODE: </b>' . $stateCode . '
         </td>		
    <td ><b>DATE:</b> ' . date("d.m.Y", strtotime($invDate)) . ' <br/><br/><b>Note:</b>This is Proforma, don’t take any tax credit on basis of this, we send final invoice only after payment realization.</td>
    </tr>
    <tr><td colspan="3"><b>SAC code : </b> Other Technical And Scientific Services N.e.c – 998349</td></tr>
    </table>
    <table border="1" cellspacing="1" cellpadding="4">
    <tr><th width="5%">Sr.</th><th width="60%">Description</th><th width="35%">Amount in Rs. </th></tr> 
    ' . $serviceRow . '
    <tr><td colspan="2" style="text-align:right">Net Price</td><td> ' . $netPrice . '/-</td></tr>
    ' . $gsts . '
    <tr><td colspan="2" style="text-align:right"><b>Final Amount</b></td><td> ' . $total_amt . '/- </td></tr>
    <tr><td colspan="3">Amount In Word : ' . convertToWord($total_amt) . '/-</td></tr>
    <tr><td colspan="3"><b>
    NOTE-</b>
    <ul style= "list-style-type:none; ">
    <li>* Please issue cheque in favor of AITF Management Certification Limited. Cash Payment is not acceptable.</li>
    <li>* <b>GSTIN No.: 27AAJCA6651K1ZB,</b></li>
    <li>* PAN Number :AAJCA6651K</li>
    <li>* Bank details: ' . $firmInfo['firm_bank'] . ' Address - ' . $firmInfo['bank_address'] . '</li>
    <li>* Current Account No.- ' . $firmInfo['bank_ac_no'] . ',IFSC Code - ' . $firmInfo['bank_ifsc_code'] . ', MICR No.- ' . $firmInfo['bank_micr_no'] . '</li>
    <li>* For late payment interest will be charged at a rate of 10 % per month.</li>
    <li>* Signature not required if proforma is computer generated and send by AITF registered A/c mail ID i.e. aitfacct@gmail.com .</li>
    </ul>
    </td></tr>
    </table>
    <table border="1" cellspacing="1" cellpadding="4"> 
    <tr><td style="text-align:left;border-top:0.1px solid white"><b>Registered office Address -</b><br/>
    ' . $firmInfo['firm_address'] . '<br/>
    (PH) ' . $firmInfo['firm_contact'] . '<br/>
    E mail : ' . $firmInfo['firm_email'] . ' <br/> 
    Web – www.aitf.co.in <br/> 
    </td>
    <td style="text-align:right;border-bottom:0.1px solid white"><b>AITF MANAGEMENT CERTIFICATION LIMITED.</b><br/>
    <img src="' . $firmSign . '" alt="logo" height="50" width="90"><br/>
    Administrator
    </td>
    </tr>
    </table>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');
$otp = rand(100000, 999999);
//Close and output PDF document
//$pdf->Output('Final Invoice.pdf', 'I'); die;

$pdf->Output(__DIR__ . '/notification/' . $otp . 'proforma_invoice.pdf', 'F');

$body = aitfBuildProformaMailBody($customerInfo, $innvoice, $invoiceSvc);
$mailConfig = aitfGetInvoiceMailConfig();
$attachmentPath = __DIR__ . '/notification/' . $otp . 'proforma_invoice.pdf';
$attachmentName = 'Proforma-Invoice-' . $proformaprefix . '-' . $invno . '.pdf';


$email = new PHPMailer();
$email->isHTML(true);
$email->Subject = 'Proforma Invoice - ' . $customerInfo['customer_name'];
$email->Body = $body;
$email->AltBody = strip_tags(str_replace(array('<br>', '<br/>', '<br />', '</p>', '</li>'), array("\n", "\n", "\n", "\n", "\n"), $body));
aitfAddEmailRecipients($email, $customerInfo['email_id']);
if (!empty($mailConfig['office_copy'])) {
    $email->addBCC($mailConfig['office_copy']);
}
$email->AddAttachment($attachmentPath, $attachmentName);

list($mailSent, $mailError) = aitfSendInvoiceMailer($email);



if ($mailSent) {
    $invoicesent = "UPDATE invoice_details SET pro_inv_sent_count= pro_inv_sent_count + 1 WHERE invoice_id = :id";
    $statement = $db->prepare($invoicesent);
    $statement->bindParam(':id', $_POST['invoiceid']);
    $statement->execute();

    if (!empty($mailConfig['office_copy'])) {
        $confirmation = new PHPMailer();
        $confirmation->isHTML(true);
        $confirmation->Subject = 'Invoice Sent Confirmation - Proforma Invoice - ' . $customerInfo['customer_name'];
        $confirmation->Body = '<html><body>'
            . '<p>Following email and document have been sent to the client.</p>'
            . '<p><strong>Invoice Type:</strong> Proforma Invoice<br>'
            . '<strong>Client:</strong> ' . aitfInvoiceEscape($customerInfo['customer_name']) . '<br>'
            . '<strong>Recipients:</strong> ' . aitfInvoiceEscape($customerInfo['email_id']) . '<br>'
            . '<strong>Subject:</strong> ' . aitfInvoiceEscape($email->Subject) . '<br>'
            . '<strong>Attachment:</strong> ' . aitfInvoiceEscape($attachmentName) . '</p>'
            . '<hr>'
            . '<p><strong>Message sent to client:</strong></p>'
            . $body
            . '</body></html>';
        $confirmation->AltBody = 'Following email and document have been sent to the client.' . "\n"
            . 'Invoice Type: Proforma Invoice' . "\n"
            . 'Client: ' . $customerInfo['customer_name'] . "\n"
            . 'Recipients: ' . $customerInfo['email_id'] . "\n"
            . 'Subject: ' . $email->Subject . "\n"
            . 'Attachment: ' . $attachmentName;
        $confirmation->addAddress($mailConfig['office_copy']);
        $confirmation->AddAttachment($attachmentPath, $attachmentName);
        aitfSendInvoiceMailer($confirmation);
    }
}

header("Location: " . aitfBuildInvoiceRedirectUrl($mailSent, $mailError));

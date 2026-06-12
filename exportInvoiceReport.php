<?php
require_once('tcpdf_include.php');
include_once './dbConnect.php';
include_once './commonutil.php';

// Ensure no stray output
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

$invoiceType = $_POST['invoiceType'] ?? 'proforma';
$fromDate = $_POST['fromDate'] ?? '';
$toDate = $_POST['toDate'] ?? '';
$clientName = $_POST['clientName'] ?? '';

// Robust amount cleaning function matching adminDashboard logic
function cleanAmount($value) {
    if ($value === null || $value === '') return 0.0;
    // Remove currency symbols and commas
    $clean = str_replace(['Rs.', '₹', ','], '', $value);
    // Remove any trailing /- or other non-numeric chars except decimal point
    $clean = preg_replace('/[^\d.]/', '', $clean);
    return (float)$clean;
}

function formatIndian($num) {
    $num = round($num, 2);
    $parts = explode('.', $num);
    $whole = $parts[0];
    $decimal = isset($parts[1]) ? str_pad($parts[1], 2, '0', STR_PAD_RIGHT) : '00';

    $lastThree = substr($whole, -3);
    $restUnits = substr($whole, 0, -3);
    if ($restUnits != '' && $restUnits != '-') {
        $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
        $whole = $restUnits . "," . $lastThree;
    }
    return $whole . "." . $decimal;
}

function convertToWord($number) {
    $no = (int) floor($number);
    $point = (int) round(($number - $no) * 100);
    if ($point === 100) {
        $no = $no + 1;
        $point = 0;
    }
    $hundred = null;
    $digits_1 = strlen((string) $no);
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
    $n = $no;
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($n % $divider);
        $n = floor($n / $divider);
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
    $result = trim(implode('', $str));
    if ($result === '') {
        $result = 'Zero';
    }
    $pointsWords = '';
    if ($point) {
        $pointsWords = ($point < 21) ? $words[$point] : ($words[floor($point / 10) * 10] . " " . $words[$point % 10]);
        $pointsWords = trim($pointsWords);
    }
    return $result . " Rupees" . ($pointsWords !== '' ? (" and " . $pointsWords . " Paise") : "") . " Only";
}

$sql = "SELECT id.invoice_id, id.customer_id, id.profarma_prefix, id.invoice_no, id.final_prefix, id.final_invoice_no, 
               id.invoice_date, id.final_amount, ci.customer_name, ci.gstin_no 
        FROM invoice_details id 
        LEFT JOIN customer_info ci ON id.customer_id = ci.id 
        WHERE id.invoice_type = :invoiceType AND id.is_deleted = 'No'";

$params = [':invoiceType' => $invoiceType];

if ($fromDate != '') {
    $sql .= " AND id.invoice_date >= :fromDate";
    $params[':fromDate'] = date("Y-m-d", strtotime(str_replace('.', '-', $fromDate)));
}
if ($toDate != '') {
    $sql .= " AND id.invoice_date <= :toDate";
    $params[':toDate'] = date("Y-m-d", strtotime(str_replace('.', '-', $toDate)));
}
if ($clientName != '') {
    $sql .= " AND ci.customer_name LIKE :clientName";
    $params[':clientName'] = '%' . $clientName . '%';
}

$sql .= " ORDER BY id.invoice_date DESC, id.invoice_id DESC";

$statement = $db->prepare($sql);
foreach ($params as $key => &$val) {
    $statement->bindParam($key, $val);
}
$statement->execute();
$invoices = $statement->fetchAll(PDO::FETCH_ASSOC);

// Batch load services
$invoiceIds = array_column($invoices, 'invoice_id');
$svcMap = [];
if (!empty($invoiceIds)) {
    $placeholders = implode(',', array_fill(0, count($invoiceIds), '?'));
    $svcSql = "SELECT invoice_ids, service_desc FROM invoice_services WHERE invoice_ids IN ($placeholders)";
    $stmtSvc = $db->prepare($svcSql);
    $stmtSvc->execute($invoiceIds);
    foreach ($stmtSvc->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $svcMap[$row['invoice_ids']][] = $row['service_desc'];
    }
}

class REPORTPDF extends TCPDF {
    public function Header() {
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        if (file_exists($image_file)) {
            // Logo on the left
            $this->Image($image_file, 10, 6, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Main Heading - AITF MANAGEMENT CERTIFICATION LIMITED (Larger)
        $this->SetFont('helvetica', 'B', 22);
        $this->SetTextColor(0, 0, 0);
        $this->SetY(6);
        $this->SetX(50);
        $this->Cell(237, 12, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, 1, 'C');
        
        // Subtitle - FINAL TAX INVOICE REPORT (Larger and Bold)
        $this->SetFont('helvetica', 'B', 15);
        $this->SetX(50);
        $type = (isset($_POST['invoiceType']) && $_POST['invoiceType'] == 'proforma' ? 'PROFORMA' : 'FINAL');
        $this->Cell(237, 10, $type . ' TAX INVOICE REPORT', 0, 1, 'C');
        
        // Filters metadata (Period/Client) - Styled Bold
        $filters = "";
        if(!empty($_POST['fromDate']) || !empty($_POST['toDate'])) {
            $filters .= "PERIOD: " . ($_POST['fromDate'] ?? 'Start') . " TO " . ($_POST['toDate'] ?? 'End') . "    ";
        }
        if(!empty($_POST['clientName'])) {
            $filters .= "CLIENT: " . strtoupper($_POST['clientName']);
        }
        if($filters) {
            $this->SetFont('helvetica', 'B', 10);
            $this->SetX(50);
            $this->Cell(237, 6, $filters, 0, 1, 'C');
        }
        
        $this->Ln(2);
        $this->Line(10, 38, 287, 38);
    }
    
    public function Footer() {
        // Position at 25 mm from bottom to avoid overlap
        $this->SetY(-25);
        $this->SetFont('helvetica', 'B', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'NOTE: THIS IS A SYSTEM GENERATED DOCUMENT AND DOES NOT REQUIRE A PHYSICAL SIGNATURE.', 0, 1, 'C');
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new REPORTPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Invoice Report');
$pdf->SetMargins(10, 42, 10);
$pdf->SetAutoPageBreak(TRUE, 28);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

$html = '<table border="1" cellpadding="6">
<thead>
    <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b;">
        <th width="5%" align="center">Sr.</th>
        <th width="20%">Client Name</th>
        <th width="15%">GSTIN No</th>
        <th width="15%">Invoice No.</th>
        <th width="10%" align="center">Date</th>
        <th width="25%">Particulars</th>
        <th width="10%" align="right">Amount</th>
    </tr>
</thead>
<tbody>';

$totalSum = 0;
$count = 1;
if (empty($invoices)) {
    $html .= '<tr><td colspan="7" align="center">No records found</td></tr>';
} else {
    foreach ($invoices as $inv) {
        $invoiceNo = ($invoiceType == 'proforma') 
            ? $inv['profarma_prefix'] . '-' . $inv['invoice_no']
            : $inv['final_prefix'] . '-' . $inv['final_invoice_no'];
        
        $particularsArray = isset($svcMap[$inv['invoice_id']]) ? $svcMap[$inv['invoice_id']] : [];
        $particulars = implode(', ', $particularsArray);
        
        $amount = cleanAmount($inv['final_amount']);
        $totalSum += $amount;

        $html .= '<tr>
            <td align="center" width="5%">' . $count++ . '</td>
            <td width="20%"><b>' . htmlspecialchars($inv['customer_name']) . '</b></td>
            <td width="15%">' . htmlspecialchars($inv['gstin_no']) . '</td>
            <td width="15%">' . htmlspecialchars($invoiceNo) . '</td>
            <td align="center" width="10%">' . simpleDateConversion($inv['invoice_date']) . '</td>
            <td width="25%">' . htmlspecialchars($particulars) . '</td>
            <td align="right" width="10%"><b>' . formatIndian($amount) . '/-</b></td>
        </tr>';
    }
}
$html .= '</tbody></table>';

// REPORT SUMMARY SECTION
$invoiceCount = count($invoices);
$summaryTitle = strtoupper($invoiceType) . ' INVOICE REPORT SUMMARY';
$amountInWords = convertToWord($totalSum);

$html .= '<br><br>
<table border="1" cellpadding="8" style="background-color: #fcfcfc;">
    <tr>
        <th colspan="2" align="center" style="background-color: #0d4d8b; color: #ffffff; font-weight: bold; font-size: 12px;">' . $summaryTitle . '</th>
    </tr>
    <tr>
        <td width="50%" style="font-weight: bold;">TOTAL NUMBER OF INVOICES</td>
        <td width="50%" align="right" style="font-weight: bold; font-size: 11px;">' . $invoiceCount . '</td>
    </tr>
    <tr>
        <td width="50%" style="font-weight: bold;">TOTAL INVOICE AMOUNT SUM</td>
        <td width="50%" align="right" style="font-weight: bold; font-size: 12px; color: #0d4d8b;">Rs. ' . formatIndian($totalSum) . '/-</td>
    </tr>
    <tr>
        <td width="100%" colspan="2" style="font-style: italic;"><b>AMOUNT IN WORDS:</b> ' . strtoupper($amountInWords) . '</td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf->Output('Invoice_Report.pdf', 'I');

<?php
require_once('tcpdf_include.php');
include_once './dbConnect.php';
include_once './commonutil.php';

// Ensure no stray output
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

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

$sql = "SELECT s.service_id, service_type, s.customer_id, ci.customer_name, ci.audit_status, aa.audit_id as quotation_id, 
               aa.service_id as casvcid, uni_svc_id as quotationno, application_date as quotation_date,
               refundable_amount, audit_stage_i, audit_stage_iI, ca.aggrement_date, ca.aggrement_no 
        FROM customer_info ci 
        JOIN service s ON ci.id = s.customer_id 
        JOIN audit_application aa ON s.service_id = aa.uni_svc_id 
        LEFT JOIN certification_aggrement ca ON aa.service_id = ca.service 
        WHERE ci.is_deleted = 'NO'";

$params = [];

if ($fromDate != '') {
    $sql .= " AND ca.aggrement_date >= :fromDate";
    $params[':fromDate'] = date("Y-m-d", strtotime(str_replace('.', '-', $fromDate)));
}
if ($toDate != '') {
    $sql .= " AND ca.aggrement_date <= :toDate";
    $params[':toDate'] = date("Y-m-d", strtotime(str_replace('.', '-', $toDate)));
}
if ($clientName != '') {
    $sql .= " AND ci.customer_name LIKE :clientName";
    $params[':clientName'] = '%' . $clientName . '%';
}

$sql .= " ORDER BY ca.aggrement_date DESC";

$statement = $db->prepare($sql);
foreach ($params as $key => &$val) {
    $statement->bindParam($key, $val);
}
$statement->execute();
$agreements = $statement->fetchAll(PDO::FETCH_ASSOC);

class REPORTPDF extends TCPDF {
    public function Header() {
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        if (file_exists($image_file)) {
            $this->Image($image_file, 10, 6, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        $this->SetFont('helvetica', 'B', 22);
        $this->SetTextColor(0, 0, 0);
        $this->SetY(6);
        $this->SetX(50);
        $this->Cell(237, 12, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, 1, 'C');
        
        $hasFilters = !empty($_POST['fromDate']) || !empty($_POST['toDate']) || !empty($_POST['clientName']);
        $mainTitle = $hasFilters ? 'CERTIFICATION AGREEMENT REPORT (FILTERED)' : 'CERTIFICATION AGREEMENT REPORT (ALL)';
        
        $this->SetFont('helvetica', 'B', 15);
        $this->SetX(50);
        $this->Cell(237, 10, $mainTitle, 0, 1, 'C');
        
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
$pdf->SetTitle('Agreement Report');
$pdf->SetMargins(10, 42, 10);
$pdf->SetAutoPageBreak(TRUE, 28);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

$html = '<table border="1" cellpadding="6">
<thead>
    <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b;">
        <th width="5%" align="center">Sr.</th>
        <th width="20%">Client Name</th>
        <th width="15%">Service Type</th>
        <th width="15%">Agreement No.</th>
        <th width="10%" align="center">Date</th>
        <th width="10%" align="right">Amount</th>
        <th width="25%">Status</th>
    </tr>
</thead>
<tbody>';

$totalSum = 0;
$count = 1;
if (empty($agreements)) {
    $html .= '<tr><td colspan="7" align="center">No records found</td></tr>';
} else {
    foreach ($agreements as $agr) {
        $amount = (float)$agr['refundable_amount'] + (float)$agr['audit_stage_i'] + (float)$agr['audit_stage_iI'];
        $totalSum += $amount;

        $html .= '<tr>
            <td align="center" width="5%">' . $count++ . '</td>
            <td width="20%"><b>' . htmlspecialchars($agr['customer_name']) . '</b></td>
            <td width="15%">' . htmlspecialchars($agr['service_type']) . '</td>
            <td width="15%">' . htmlspecialchars($agr['aggrement_no']) . '</td>
            <td align="center" width="10%">' . simpleDateConversion($agr['aggrement_date']) . '</td>
            <td align="right" width="10%"><b>' . formatIndian($amount) . '/-</b></td>
            <td width="25%">' . htmlspecialchars($agr['audit_status']) . '</td>
        </tr>';
    }
}
$html .= '</tbody></table>';

// Premium Horizontal Divider
$html .= '<br><br><table border="0" cellpadding="0" cellspacing="0"><tr><td style="border-bottom: 2px solid #0d4d8b;"></td></tr></table><br>';

// REPORT SUMMARY SECTION
$agreementCount = count($agreements);
$summaryTitle = 'CERTIFICATION AGREEMENT REPORT SUMMARY';
$amountInWords = convertToWord($totalSum);

$html .= '<br><br>
<table border="1" cellpadding="8" style="background-color: #fcfcfc;">
    <tr>
        <th colspan="2" align="center" style="background-color: #0d4d8b; color: #ffffff; font-weight: bold; font-size: 12px;">' . $summaryTitle . '</th>
    </tr>
    <tr>
        <td width="50%" style="font-weight: bold;">TOTAL NUMBER OF AGREEMENTS</td>
        <td width="50%" align="right" style="font-weight: bold; font-size: 11px;">' . $agreementCount . '</td>
    </tr>
    <tr>
        <td width="50%" style="font-weight: bold;">TOTAL AGREEMENT AMOUNT SUM</td>
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
$pdf->Output('Agreement_Report.pdf', 'I');

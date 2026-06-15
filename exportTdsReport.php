<?php
ob_start();
require_once('tcpdf_include.php');
include_once './dbConnect.php';
include_once './commonutil.php';

// Ensure no stray output before this point
while (ob_get_level() > 1) {
    ob_end_clean();
}

$status = $_REQUEST['status'] ?? 'all';
$startDateStr = $_REQUEST['startDate'] ?? '';
$endDateStr = $_REQUEST['endDate'] ?? '';
$action = $_REQUEST['action'] ?? 'export';
$clientSearch = $_REQUEST['clientSearch'] ?? '';
$sortOrder = $_REQUEST['sortOrder'] ?? '';

$startDate = '';
$endDate = '';

if (!empty($startDateStr)) {
    $parts = explode('.', $startDateStr);
    if(count($parts) == 3) {
        $startDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
}
if (!empty($endDateStr)) {
    $parts = explode('.', $endDateStr);
    if(count($parts) == 3) {
        $endDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
}

function formatIndian($num) {
    $num = round((float)$num, 2);
    $parts = explode('.', (string)$num);
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

$sql = "SELECT ci.id, customer_name, ci.unique_id, ps.service_type as tds, ps.amount_decided, ps.payment_received, ps.payment_date, ps.comment 
        FROM customer_info ci 
        LEFT JOIN payment_system ps ON ci.id = ps.customer_id 
        LEFT JOIN service svc ON ps.customer_id = svc.customer_id 
        WHERE svc.service_id = (SELECT MAX(tmp.service_id) FROM service tmp WHERE tmp.customer_id = ci.id) 
        AND ps.service_type LIKE '%tds%'";

$queryParams = [];

if ($status == 'received') {
    $sql .= " AND ps.payment_received > 0 AND ps.payment_received IS NOT NULL AND ps.payment_received <> ''";
} elseif ($status == 'not_received') {
    $sql .= " AND (ps.payment_received IS NULL OR ps.payment_received = 0 OR ps.payment_received = '' OR ps.payment_received = '0.00')";
}

if (!empty($clientSearch)) {
    $sql .= " AND ci.customer_name LIKE :clientSearch";
    $queryParams[':clientSearch'] = '%' . $clientSearch . '%';
}

if (!empty($startDate)) {
    $sql .= " AND ps.payment_date >= :startDate";
    $queryParams[':startDate'] = $startDate;
}
if (!empty($endDate)) {
    $sql .= " AND ps.payment_date <= :endDate";
    $queryParams[':endDate'] = $endDate;
}

if ($sortOrder == 'high') {
    $sql .= " ORDER BY CAST(ps.amount_decided AS DECIMAL(10,2)) DESC";
} elseif ($sortOrder == 'low') {
    $sql .= " ORDER BY CAST(ps.amount_decided AS DECIMAL(10,2)) ASC";
} else {
    $sql .= " ORDER BY ci.id DESC";
}

$statement = $db->prepare($sql);
foreach ($queryParams as $key => $val) {
    $statement->bindValue($key, $val);
}
$statement->execute();
$tdsList = $statement->fetchAll(PDO::FETCH_ASSOC);

class REPORTPDF extends TCPDF {
    public $reportTitle = 'TDS REPORT';
    public $filters = '';

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
        
        $this->SetFont('helvetica', 'B', 15);
        $this->SetX(50);
        $this->Cell(237, 10, $this->reportTitle, 0, 1, 'C');
        
        if($this->filters) {
            $this->SetFont('helvetica', 'B', 10);
            $this->SetX(50);
            $this->Cell(237, 6, $this->filters, 0, 1, 'C');
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

$pdf->reportTitle = ($status == 'not_received') ? 'TDS NOT RECEIVED REPORT' : (($status == 'received') ? 'TDS RECEIVED REPORT' : 'TDS ALL REPORT');
$filters = "STATUS: " . strtoupper($status);
if($clientSearch) $filters .= " | CLIENT: " . strtoupper($clientSearch);
if($startDate) $filters .= " | FROM: " . date("d.m.Y", strtotime($startDate));
if($endDate) $filters .= " | TO: " . date("d.m.Y", strtotime($endDate));
$pdf->filters = $filters;

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetMargins(10, 42, 10);
$pdf->SetAutoPageBreak(TRUE, 28);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

$html = '<table border="1" cellpadding="6" cellspacing="0">
<thead>
    <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b;">
        <th width="5%" align="center">Sr.</th>
        <th width="15%">Unique ID</th>
        <th width="25%">Client Name</th>
        <th width="15%">Service Type</th>
        <th width="15%" align="right">TDS Deducted</th>
        <th width="15%" align="right">Payment Received</th>
        <th width="10%" align="center">Date</th>
    </tr>
</thead>
<tbody>';

$totalDeducted = 0;
$totalReceived = 0;
$count = 1;

if (empty($tdsList)) {
    $html .= '<tr><td colspan="7" align="center">No records found</td></tr>';
} else {
    foreach ($tdsList as $tds) {
        $totalDeducted += (float)$tds['amount_decided'];
        $totalReceived += (float)$tds['payment_received'];
        $payDate = (isset($tds['payment_date']) && !empty($tds['payment_date'])) ? date("d.m.Y", strtotime($tds['payment_date'])) : '-';

        $html .= '<tr>
            <td align="center" width="5%">' . $count++ . '</td>
            <td width="15%">' . htmlspecialchars($tds['unique_id']) . '</td>
            <td width="25%"><b>' . htmlspecialchars($tds['customer_name']) . '</b></td>
            <td width="15%">' . htmlspecialchars($tds['tds']) . '</td>
            <td align="right" width="15%">' . formatIndian($tds['amount_decided']) . '</td>
            <td align="right" width="15%">' . formatIndian($tds['payment_received']) . '</td>
            <td align="center" width="10%">' . $payDate . '</td>
        </tr>';
    }
}
$html .= '</tbody></table>';

// Premium Horizontal Divider
$html .= '<br><br><table border="0" cellpadding="0" cellspacing="0"><tr><td style="border-bottom: 2px solid #0d4d8b;"></td></tr></table><br>';

// SUMMARY SECTION
$html .= '<br><br>
<div nobr="true">
    <table border="1" cellpadding="8" style="background-color: #fcfcfc;">
        <tr style="background-color: #0d4d8b; color: #ffffff;">
            <th colspan="2" align="center" style="font-weight: bold; font-size: 12px;">TDS REPORT SUMMARY</th>
        </tr>
        <tr>
            <td width="50%"><b>TOTAL CASES</b></td>
            <td width="50%" align="right"><b>' . count($tdsList) . '</b></td>
        </tr>
        <tr>
            <td width="50%"><b>TOTAL TDS DEDUCTED</b></td>
            <td width="50%" align="right" style="color: #0d4d8b; font-size: 12px;"><b>Rs. ' . formatIndian($totalDeducted) . '/-</b></td>
        </tr>
        <tr>
            <td width="50%"><b>TOTAL PAYMENT RECEIVED</b></td>
            <td width="50%" align="right" style="color: #0d4d8b; font-size: 12px;"><b>Rs. ' . formatIndian($totalReceived) . '/-</b></td>
        </tr>';
if($status == 'not_received') {
    $html .= '<tr>
            <td width="100%" colspan="2" style="font-style: italic;"><b>TOTAL PENDING AMOUNT IN WORDS:</b> ' . strtoupper(convertToWord($totalDeducted - $totalReceived)) . '</td>
        </tr>';
}
$html .= '    </table>
</div>';

$pdf->writeHTML($html, true, false, true, false, '');

if ($action == 'print') {
    $js = 'print(true);';
    $pdf->IncludeJS($js);
}

// Close and output PDF document
while (ob_get_level() > 0) {
    ob_end_clean();
}

$filename = 'TDS_Report_' . date('YmdHis') . '.pdf';
$pdf->Output($filename, 'I');

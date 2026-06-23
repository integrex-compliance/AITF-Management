<?php
require_once('tcpdf_include.php');
include_once './dbConnect.php';
include_once './commonutil.php';

// Ensure no stray output
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

$searchQuery = trim($_POST['searchQuery'] ?? '');
$statusFilter = $_POST['statusFilter'] ?? 'All';

// Today's date context (User's system local date context: 2026-06-23)
$todayStr = '2026-06-23';

function getBillStatusInfoForReport($bill, $todayStr)
{
    $today = new DateTime($todayStr);

    $paymentStatus = $bill['payment_status'];
    $amount = (float) $bill['amount'];
    $paidAmount = (float) $bill['paid_amount'];
    $dueDateStr = $bill['due_date'];

    if ($paymentStatus === 'Paid' || $paidAmount >= $amount) {
        return array(
            'status' => 'Paid',
            'label' => 'Paid',
        );
    }

    $isOverdue = false;
    if (!empty($dueDateStr)) {
        $dueDate = new DateTime($dueDateStr);
        if ($dueDate < $today) {
            $isOverdue = true;
        }
    }

    if ($isOverdue) {
        return array(
            'status' => 'Overdue',
            'label' => 'Overdue',
        );
    }

    if ($paidAmount > 0) {
        return array(
            'status' => 'Partially Paid',
            'label' => 'Partially Paid',
        );
    }

    return array(
        'status' => 'Unpaid',
        'label' => 'Unpaid',
    );
}

function cleanAmount($value) {
    if ($value === null || $value === '') return 0.0;
    $clean = str_replace(['Rs.', '₹', ','], '', $value);
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

// Fetch all bills
$bills = [];
try {
    $statement = $db->query('SELECT * FROM bills ORDER BY created_at DESC, bill_id DESC');
    $allBills = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Apply search and status filters
    foreach ($allBills as $bill) {
        $statusInfo = getBillStatusInfoForReport($bill, $todayStr);
        
        // Filter by Status
        if ($statusFilter !== 'All' && $statusInfo['status'] !== $statusFilter) {
            continue;
        }
        
        // Filter by Search Query
        if ($searchQuery !== '') {
            $searchLower = strtolower($searchQuery);
            $vendorLower = strtolower($bill['vendor_name'] ?? '');
            $descLower = strtolower($bill['description'] ?? '');
            
            if (strpos($vendorLower, $searchLower) === false && strpos($descLower, $searchLower) === false) {
                continue;
            }
        }
        
        $bill['calculated_status'] = $statusInfo['label'];
        $bills[] = $bill;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}

class REPORTPDF extends TCPDF {
    public function Header() {
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        if (file_exists($image_file)) {
            $this->Image($image_file, 10, 6, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Main Heading
        $this->SetFont('helvetica', 'B', 22);
        $this->SetTextColor(0, 0, 0);
        $this->SetY(6);
        $this->SetX(50);
        $this->Cell(237, 12, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, 1, 'C');
        
        // Subtitle
        $this->SetFont('helvetica', 'B', 15);
        $this->SetX(50);
        $this->Cell(237, 10, 'VENDOR BILLS REPORT', 0, 1, 'C');
        
        // Filters metadata
        $filters = "";
        $statusVal = $_POST['statusFilter'] ?? 'All';
        $searchVal = $_POST['searchQuery'] ?? '';
        
        if($statusVal !== 'All' || !empty($searchVal)) {
            $filterItems = [];
            if($statusVal !== 'All') {
                $filterItems[] = "STATUS: " . strtoupper($statusVal);
            }
            if(!empty($searchVal)) {
                $filterItems[] = "SEARCH: " . strtoupper($searchVal);
            }
            $filters = implode("   |   ", $filterItems);
        }
        
        if($filters) {
            $this->SetFont('helvetica', 'B', 10);
            $this->SetTextColor(100, 100, 100);
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
$pdf->SetTitle('Vendor Bills Report');
$pdf->SetMargins(10, 42, 10);
$pdf->SetAutoPageBreak(TRUE, 28);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

$html = '<table border="1" cellpadding="6">
<thead>
    <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b;">
        <th width="5%" align="center">Sr.</th>
        <th width="20%">Vendor Name</th>
        <th width="12%" align="center">Due Date</th>
        <th width="12%" align="center">Status</th>
        <th width="23%">Description/Notes</th>
        <th width="10%" align="right">Bill Amount</th>
        <th width="9%" align="right">Paid Amount</th>
        <th width="9%" align="right">Outstanding</th>
    </tr>
</thead>
<tbody>';

$totalAmount = 0;
$totalPaid = 0;
$totalOutstanding = 0;
$count = 1;

if (empty($bills)) {
    $html .= '<tr><td colspan="8" align="center">No bills found matching filters</td></tr>';
} else {
    foreach ($bills as $bill) {
        $amt = (float)$bill['amount'];
        $paid = (float)$bill['paid_amount'];
        $outstanding = $amt - $paid;
        
        $totalAmount += $amt;
        $totalPaid += $paid;
        $totalOutstanding += $outstanding;
        
        $dueDateText = !empty($bill['due_date']) ? simpleDateConversion($bill['due_date']) : 'N/A';
        
        // Status color highlighting
        $statusStyle = '';
        if ($bill['calculated_status'] === 'Paid') {
            $statusStyle = 'color:#16a34a; font-weight:bold;';
        } else if ($bill['calculated_status'] === 'Overdue') {
            $statusStyle = 'color:#dc2626; font-weight:bold;';
        } else if ($bill['calculated_status'] === 'Partially Paid') {
            $statusStyle = 'color:#2563eb; font-weight:bold;';
        } else {
            $statusStyle = 'color:#ea580c; font-weight:bold;';
        }

        $html .= '<tr>
            <td align="center" width="5%">' . $count++ . '</td>
            <td width="20%"><b>' . htmlspecialchars($bill['vendor_name']) . '</b></td>
            <td align="center" width="12%">' . $dueDateText . '</td>
            <td align="center" width="12%"><span style="' . $statusStyle . '">' . htmlspecialchars($bill['calculated_status']) . '</span></td>
            <td width="23%">' . htmlspecialchars($bill['description'] ?? '-') . '</td>
            <td align="right" width="10%"><b>' . formatIndian($amt) . '/-</b></td>
            <td align="right" width="9%">' . formatIndian($paid) . '/-</td>
            <td align="right" width="9%"><b>' . formatIndian($outstanding) . '/-</b></td>
        </tr>';
    }
}
$html .= '</tbody></table>';

// SUMMARY SECTION
$billCount = count($bills);
$summaryTitle = 'VENDOR BILLS REPORT SUMMARY';
$wordsOutstanding = convertToWord($totalOutstanding);

$html .= '<br><br>
<table border="1" cellpadding="8" style="background-color: #fcfcfc;">
    <tr>
        <th colspan="2" align="center" style="background-color: #0d4d8b; color: #ffffff; font-weight: bold; font-size: 12px;">' . $summaryTitle . '</th>
    </tr>
    <tr>
        <td width="50%" style="font-weight: bold;">TOTAL NUMBER OF BILLS RECORDED</td>
        <td width="50%" align="right" style="font-weight: bold; font-size: 11px;">' . $billCount . '</td>
    </tr>
    <tr>
        <td width="50%" style="font-weight: bold;">TOTAL BILLS AMOUNT</td>
        <td width="50%" align="right" style="font-weight: bold; font-size: 11px;">Rs. ' . formatIndian($totalAmount) . '/-</td>
    </tr>
    <tr>
        <td width="50%" style="font-weight: bold;">TOTAL PAID AMOUNT</td>
        <td width="50%" align="right" style="font-weight: bold; font-size: 11px; color: #16a34a;">Rs. ' . formatIndian($totalPaid) . '/-</td>
    </tr>
    <tr>
        <td width="50%" style="font-weight: bold;">TOTAL OUTSTANDING DUES SUM</td>
        <td width="50%" align="right" style="font-weight: bold; font-size: 12px; color: #dc2626;">Rs. ' . formatIndian($totalOutstanding) . '/-</td>
    </tr>
    <tr>
        <td width="100%" colspan="2" style="font-style: italic;"><b>TOTAL OUTSTANDING DUES IN WORDS:</b> ' . strtoupper($wordsOutstanding) . '</td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf->Output('Vendor_Bills_Report.pdf', 'I');

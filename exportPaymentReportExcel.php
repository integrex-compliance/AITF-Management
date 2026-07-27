<?php
include_once './dbConnect.php';
include_once './commonutil.php';
include_once __DIR__ . '/include/invoice_report_lib.php';

// Ensure no stray output
while (ob_get_level() > 0) {
    ob_end_clean();
}

$minAmount = $_POST['minAmount'] ?? '';
$maxAmount = $_POST['maxAmount'] ?? '';
$clientName = $_POST['clientName'] ?? '';

$sql = "SELECT ci.id, customer_name, unique_id, contact_person, contact, ps.payment_id, ps.service_type, balance, ps.comment 
        FROM customer_info ci 
        JOIN payment_system ps on ps.customer_id = ci.id 
        WHERE ps.balance > 0 AND ps.service_type <> 'TDS' AND ci.is_deleted = 'NO'";

$params = [];
if ($clientName != '') {
    $sql .= " AND ci.customer_name LIKE :clientName";
    $params[':clientName'] = '%' . $clientName . '%';
}
if ($minAmount != '') {
    $sql .= " AND ps.balance >= :minAmount";
    $params[':minAmount'] = (float)$minAmount;
}
if ($maxAmount != '') {
    $sql .= " AND ps.balance <= :maxAmount";
    $params[':maxAmount'] = (float)$maxAmount;
}

$sql .= " ORDER BY ps.balance DESC";

$statement = $db->prepare($sql);
foreach ($params as $key => &$val) {
    $statement->bindParam($key, $val);
}
$statement->execute();
$payments = $statement->fetchAll(PDO::FETCH_ASSOC);

$xlsx = new SimpleXlsx();

// Columns configuration
$xlsx->setColWidth(1, 6);
$xlsx->setColWidth(2, 35);
$xlsx->setColWidth(3, 25);
$xlsx->setColWidth(4, 20);
$xlsx->setColWidth(5, 20);
$xlsx->setColWidth(6, 20);

$filters = '';
if (!empty($minAmount) || !empty($maxAmount)) {
    $filters .= "AMOUNT RANGE: " . ($minAmount !== '' ? $minAmount : '0') . " TO " . ($maxAmount !== '' ? $maxAmount : 'ANY') . "    ";
}
if (!empty($clientName)) {
    $filters .= "CLIENT: " . strtoupper($clientName);
}
if ($filters === '') { $filters = 'All Records'; }

// Row 1
$xlsx->addRow([['v' => 'AITF MANAGEMENT CERTIFICATION LIMITED', 's' => 1, 't' => 's']]);
$xlsx->merge('A1:F1');
$xlsx->setRowHeight(1, 28);

// Row 2
$xlsx->addRow([['v' => 'PAYMENT REPORT (' . $filters . ')', 's' => 2, 't' => 's']]);
$xlsx->merge('A2:F2');
$xlsx->setRowHeight(2, 20);

// Row 3
$accentRow = [];
for ($ac = 0; $ac < 6; $ac++) {
    $accentRow[] = ['v' => '', 's' => 38, 't' => 's'];
}
$xlsx->addRow($accentRow);
$xlsx->setRowHeight(3, 8);

// Row 4
$headers = ['Sr.', 'Client Name', 'Contact Person', 'Phone', 'Service Type', 'Pending Amount'];
$headerRow = [];
foreach ($headers as $h) {
    $headerRow[] = ['v' => $h, 's' => 4, 't' => 's'];
}
$xlsx->addRow($headerRow);
$xlsx->setRowHeight(4, 26);

// Normal row styles
$sCenter = 7;
$sClient = 8;
$sText = 9;
$sAmount = 19; // Amount style

$count = 1;
$totalPending = 0;

if (empty($payments)) {
    $xlsx->addRow([['v' => 'No records found', 's' => 7, 't' => 's']]);
    $xlsx->merge('A5:F5');
} else {
    foreach ($payments as $pay) {
        $totalPending += (float)$pay['balance'];
        
        $row = [];
        $row[] = ['v' => $count++, 's' => $sCenter, 't' => 'n'];
        $row[] = ['v' => $pay['customer_name'], 's' => $sClient, 't' => 's'];
        $row[] = ['v' => $pay['contact_person'], 's' => $sText, 't' => 's'];
        $row[] = ['v' => $pay['contact'], 's' => $sText, 't' => 's'];
        $row[] = ['v' => $pay['service_type'], 's' => $sCenter, 't' => 's'];
        $row[] = ['v' => round($pay['balance'], 2), 's' => $sAmount, 't' => 'n'];
        $xlsx->addRow($row);
    }
}

// --- SUMMARY BREAKDOWN ---
$xlsx->addRow([['v' => '', 's' => 0, 't' => 's']]); // spacer

$curRow = 4 + (empty($payments) ? 1 : count($payments)) + 1;

$curRow++;
$xlsx->addRow([['v' => 'PAYMENT REPORT SUMMARY', 's' => 16, 't' => 's']]);
$xlsx->merge('A' . $curRow . ':C' . $curRow);

$curRow++;
$xlsx->addRow([
    ['v' => 'TOTAL PENDING CASES', 's' => 18, 't' => 's'],
    null, null,
    ['v' => count($payments), 's' => 20, 't' => 'n'],
]);
$xlsx->merge('A' . $curRow . ':C' . $curRow);

$curRow++;
$xlsx->addRow([
    ['v' => 'TOTAL PENDING AMOUNT', 's' => 18, 't' => 's'],
    null, null,
    ['v' => round($totalPending, 2), 's' => 19, 't' => 'n'],
]);
$xlsx->merge('A' . $curRow . ':C' . $curRow);

$bytes = $xlsx->toString('Payment Report');
$fileName = "Payment_Pending_Report_" . date('Ymd_His') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($bytes));
header('Cache-Control: max-age=0');
echo $bytes;
exit;

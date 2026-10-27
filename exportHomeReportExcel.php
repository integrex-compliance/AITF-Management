<?php
include_once './dbConnect.php';
include_once './commonutil.php';
include_once __DIR__ . '/include/invoice_report_lib.php';

// Ensure no stray output
while (ob_get_level() > 0) {
    ob_end_clean();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$hp_role = trim($_SESSION['role'] ?? '');
$isMarketingHead = in_array($hp_role, array('Marketing Head', 'Marketing Executive'), true)
    || strtolower(str_replace(' ', '', $hp_role)) === 'marketinghead'
    || (isset($_SESSION['username']) && strtolower(trim($_SESSION['username'])) === 'marketingh');
$isOfficeStaff = in_array($hp_role, array('Office Staff-A', 'Office Staff-B', 'BDM'), true)
    || in_array(strtolower(str_replace(array(' ', '-'), '', $hp_role)), array('officestaffa', 'officestaffb', 'bdm'), true);

$fromDate = $_POST['fromDate'] ?? '';
$toDate = $_POST['toDate'] ?? '';
$serviceType = $_POST['serviceType'] ?? '';
$clientName = $_POST['clientName'] ?? '';
$statusFilter = $_POST['statusFilter'] ?? '';

if (!function_exists('hpNormalizeStatus')) {
    function hpNormalizeStatus($value)
    {
        $status = trim((string) $value);
        if ($status === '') {
            return 'No Status';
        }
        $key = strtolower(str_replace(array('-', '_', ' '), '', $status));
        if ($key === 'active' || $key === 'valid' || $key === 'granted')
            return 'Active';
        if ($key === 'suspended' || $key === 'suspend')
            return 'Suspended';
        if ($key === 'inprocess')
            return 'In-process';
        if ($key === 'contractinprocess')
            return 'Contract In-process';
        if ($key === 'withdrawn' || $key === 'withdraw')
            return 'Withdrawn';
        if ($key === 'applicationcanceled' || $key === 'applicationcancelled' || $key === 'cancelled' || $key === 'canceled')
            return 'Application canceled';
        if ($key === 'expired' || $key === 'expire')
            return 'Expired';
        return $status;
    }
}

if (!function_exists('hpDerivedStatus')) {
    function hpDerivedStatus($customer, $serviceMap)
    {
        $norm = hpNormalizeStatus(isset($customer['audit_status']) ? $customer['audit_status'] : '');
        if ($norm === 'Application canceled') {
            return 'Application canceled';
        }
        $cid = isset($customer['id']) ? $customer['id'] : null;
        $svc = ($cid !== null && isset($serviceMap[$cid])) ? $serviceMap[$cid] : null;
        if (empty($svc)) {
            return 'New Enquiry';
        }
        if (isset($svc['service_type']) && strtoupper(trim($svc['service_type'])) === 'CR') {
            return 'Contract-Review';
        }
        return $norm;
    }
}

require_once __DIR__ . '/include/rbac_helper.php';
$export1191Filter = !aitf_can_access_customer(1191, $hp_role) ? " AND ci.id != 1191" : "";
$customerListBase = "SELECT ci.* , ac.certification_date FROM customer_info ci left join allotment_count ac on ci.id = ac.client_id where is_deleted <> 'YES'" . $export1191Filter;
$cleanRole = strtolower(trim((string)$hp_role));
$cleanRoleNoSpace = str_replace(array(' ', '-'), '', $cleanRole);
$isTopMgmt = (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($hp_role))
    || in_array($cleanRole, array('top mgmt', 'director', 'main admin', 'mgmt', 'top management'), true)
    || in_array($cleanRoleNoSpace, array('topmgmt', 'mainadmin', 'topmanagement'), true);
$isAdmin = ($cleanRole === 'admin');
$isFinance = ($cleanRole === 'finance');
$isFullAccess = ($isTopMgmt || $isAdmin || $isFinance);

if ($isFullAccess) {
    $customerList = $customerListBase . " ORDER BY ac.certification_date DESC";
} else {
    $customerList = $customerListBase . " AND (
        (ac.certification_date IS NOT NULL AND ac.certification_date != '0000-00-00' AND ac.certification_date >= DATE_FORMAT(CURDATE() - INTERVAL 2 YEAR, '%Y-%m-01'))
        OR
        ((ac.certification_date IS NULL OR ac.certification_date = '0000-00-00') AND (ci.certification_stage_date IS NULL OR ci.certification_stage_date = '0000-00-00' OR ci.certification_stage_date >= DATE_FORMAT(CURDATE() - INTERVAL 2 YEAR, '%Y-%m-01')))
    ) ORDER BY ac.certification_date DESC";
}
$statement = $db->prepare($customerList);
$statement->execute();
$listOfCustomer = $statement->fetchAll();

$users = "SELECT * FROM `handler`";
$statement = $db->prepare($users);
$statement->execute();
$allUsers = $statement->fetchAll();

$handlerMap = array();
foreach ($allUsers as $handlerRow) {
    $handlerMap[$handlerRow['id']] = $handlerRow['handler_name'];
}

$customerIds = array_column($listOfCustomer, 'id');
$serviceMap = array();
$serviceNonSpMap = array();
$dueMap = array();

if (!empty($customerIds)) {
    $inPlaceholders = implode(',', array_fill(0, count($customerIds), '?'));

    $sqlLatestService = "SELECT s.* FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
    $stmt = $db->prepare($sqlLatestService);
    $stmt->execute($customerIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $serviceMap[$row['customer_id']] = $row;
    }

    $sqlNonSpService = "SELECT s.customer_id, s.service_id FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE service_type <> 'SP' AND customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
    $stmt = $db->prepare($sqlNonSpService);
    $stmt->execute($customerIds);
    $serviceIdsForDates = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $serviceNonSpMap[$row['customer_id']] = $row['service_id'];
        $serviceIdsForDates[] = $row['service_id'];
    }

    if (!empty($serviceIdsForDates)) {
        $inServiceIds = implode(',', array_fill(0, count($serviceIdsForDates), '?'));
        $sqlDates = "SELECT service_id, action, due_date FROM ca_service_dates WHERE service_id IN ($inServiceIds) AND TRIM(action) IN ('Suspension Letter','Suspend Letter','Expire Letter','Withdrawal Letter','Withdraw Letter')";
        $stmt = $db->prepare($sqlDates);
        $stmt->execute($serviceIdsForDates);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $dueMap[$row['service_id']][trim($row['action'])] = $row['due_date'];
        }
    }
}

$filteredCustomers = [];
foreach ($listOfCustomer as $customer) {
    $derivedStatus = hpDerivedStatus($customer, $serviceMap);

    if ($clientName != '') {
        $searchVal = strtolower(trim($clientName));
        $custName = strtolower(trim($customer['customer_name'] ?? ''));
        $uniqId = strtolower(trim($customer['unique_id'] ?? ''));
        $isMatch = (stripos($custName, $searchVal) !== false || stripos($uniqId, $searchVal) !== false);
        if (!$isMatch) {
            continue;
        }
    }
    if ($serviceType != '' && strcasecmp($customer['next_service'], $serviceType) !== 0) {
        continue;
    }
    if ($statusFilter != '') {
        if ($derivedStatus !== $statusFilter) {
            continue;
        }
    }

    $sid = isset($serviceNonSpMap[$customer['id']]) ? $serviceNonSpMap[$customer['id']] : null;
    $dueDate = null;
    if ($sid && isset($dueMap[$sid])) {
        $dueDate = isset($dueMap[$sid]['Suspension Letter']) ? $dueMap[$sid]['Suspension Letter'] : (isset($dueMap[$sid]['Suspend Letter']) ? $dueMap[$sid]['Suspend Letter'] : (isset($dueMap[$sid]['Expire Letter']) ? $dueMap[$sid]['Expire Letter'] : null));
    }

    if ($fromDate != '') {
        $fDate = date("Y-m-d", strtotime(str_replace('.', '-', $fromDate)));
        if (!$dueDate || $dueDate < $fDate) continue;
    }
    if ($toDate != '') {
        $tDate = date("Y-m-d", strtotime(str_replace('.', '-', $toDate)));
        if (!$dueDate || $dueDate > $tDate) continue;
    }

    $customer['calculated_due_date'] = $dueDate;
    $filteredCustomers[] = $customer;
}

$xlsx = new SimpleXlsx();
$xlsx->freezePanesAbove(5);

$xlsx->setColWidth(1, 6);
$xlsx->setColWidth(2, 20);
$xlsx->setColWidth(3, 14);
$xlsx->setColWidth(4, 30);
$xlsx->setColWidth(5, 30);
$xlsx->setColWidth(6, 18);
$xlsx->setColWidth(7, 14);
$xlsx->setColWidth(8, 12);
$xlsx->setColWidth(9, 15);
$xlsx->setColWidth(10, 35); // Comment

$filters = '';
if (!empty($fromDate) || !empty($toDate)) {
    $filters = 'For Period: ' . ($fromDate !== '' ? $fromDate : 'Start') . ' to ' . ($toDate !== '' ? $toDate : 'End');
}
if (!empty($clientName)) {
    $filters .= ($filters !== '' ? ', ' : '') . 'Client: ' . strtoupper($clientName);
}
if (!empty($serviceType)) {
    $filters .= ($filters !== '' ? ', ' : '') . 'Service: ' . strtoupper($serviceType);
}
if (!empty($statusFilter)) {
    $filters .= ($filters !== '' ? ', ' : '') . 'Status: ' . strtoupper($statusFilter);
}
if ($filters === '') { $filters = 'All Records'; }

// Row 1
$xlsx->addRow([['v' => 'AITF MANAGEMENT CERTIFICATION LIMITED', 's' => 1, 't' => 's']]);
$xlsx->merge('A1:J1');
$xlsx->setRowHeight(1, 28);

// Row 2
$xlsx->addRow([['v' => 'CLIENT REPORT (' . $filters . ')', 's' => 2, 't' => 's']]);
$xlsx->merge('A2:J2');
$xlsx->setRowHeight(2, 20);

// Row 3
$accentRow = [];
for ($ac = 0; $ac < 10; $ac++) {
    $accentRow[] = ['v' => '', 's' => 38, 't' => 's'];
}
$xlsx->addRow($accentRow);
$xlsx->setRowHeight(3, 8);

// Row 4
$headers = ['Sr.', 'Unique Id', 'Agr. Date', 'Client Name', 'Contact Person', 'Status', 'Due Date', 'Next Type', 'Hand', 'Comment'];
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

$count = 1;
if (empty($filteredCustomers)) {
    $xlsx->addRow([['v' => 'No records found', 's' => 7, 't' => 's']]);
    $xlsx->merge('A5:J5');
} else {
    foreach ($filteredCustomers as $customer) {
        $serviceInfo = isset($serviceMap[$customer['id']]) ? $serviceMap[$customer['id']] : array();
        $statusStr = $customer['audit_status'];
        if ($statusStr == "in-process" && isset($serviceInfo["service_type"])) {
            $statusStr .= ' (' . $serviceInfo["service_type"] . ')';
        }
        $handlerName = isset($handlerMap[$customer['handler']]) ? $handlerMap[$customer['handler']] : '';

        $row = [];
        $row[] = ['v' => $count++, 's' => $sCenter, 't' => 'n'];
        $row[] = ['v' => $customer['unique_id'] ?? '', 's' => $sCenter, 't' => 's'];
        $row[] = ['v' => $customer['certification_date'] ?? '', 's' => $sCenter, 't' => 's'];
        $row[] = ['v' => $customer['customer_name'], 's' => $sClient, 't' => 's'];
        $row[] = ['v' => $customer['contact_person'], 's' => $sText, 't' => 's'];
        $row[] = ['v' => $statusStr, 's' => $sCenter, 't' => 's'];
        $row[] = ['v' => simpleDateConversion($customer['calculated_due_date']), 's' => $sCenter, 't' => 's'];
        $row[] = ['v' => $customer['next_service'] ?? '', 's' => $sCenter, 't' => 's'];
        $row[] = ['v' => $handlerName, 's' => $sCenter, 't' => 's'];
        $row[] = ['v' => $customer['comment'] ?? '', 's' => $sText, 't' => 's'];
        $xlsx->addRow($row);
    }
}

// --- SUMMARY BREAKDOWN ---
$xlsx->addRow([['v' => '', 's' => 0, 't' => 's']]); // spacer

$statusCounts = [];
foreach ($filteredCustomers as $customer) {
    $st = hpDerivedStatus($customer, $serviceMap);
    $statusCounts[$st] = ($statusCounts[$st] ?? 0) + 1;
}

$curRow = 4 + (empty($filteredCustomers) ? 1 : count($filteredCustomers)) + 1;

$curRow++;
$xlsx->addRow([['v' => 'CLIENT REPORT SUMMARY', 's' => 16, 't' => 's']]);
$xlsx->merge('A' . $curRow . ':C' . $curRow);

$curRow++;
$xlsx->addRow([
    ['v' => 'TOTAL CLIENTS', 's' => 18, 't' => 's'],
    null, null,
    ['v' => count($filteredCustomers), 's' => 20, 't' => 'n'],
]);
$xlsx->merge('A' . $curRow . ':C' . $curRow);

if (!empty($statusCounts)) {
    $curRow++;
    $xlsx->addRow([['v' => '', 's' => 0, 't' => 's']]); // spacer

    $curRow++;
    $xlsx->addRow([['v' => 'STATUS BREAKDOWN', 's' => 16, 't' => 's']]);
    $xlsx->merge('A' . $curRow . ':C' . $curRow);

    $curRow++;
    $xlsx->addRow([
        ['v' => 'Status', 's' => 22, 't' => 's'],
        null,
        ['v' => 'No. of Clients', 's' => 22, 't' => 's'],
    ]);
    $xlsx->merge('A' . $curRow . ':B' . $curRow);

    arsort($statusCounts);
    foreach ($statusCounts as $st => $cnt) {
        $curRow++;
        $xlsx->addRow([
            ['v' => strtoupper($st), 's' => 24, 't' => 's'],
            null,
            ['v' => $cnt, 's' => 20, 't' => 'n'],
        ]);
        $xlsx->merge('A' . $curRow . ':B' . $curRow);
    }
}

$bytes = $xlsx->toString('Client Report');
$fileName = "Home_Report_" . date('Ymd_His') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($bytes));
header('Cache-Control: max-age=0');
echo $bytes;
exit;

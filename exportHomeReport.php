<?php
require_once('tcpdf_include.php');
include_once './dbConnect.php';
include_once './commonutil.php';

// Ensure no stray output
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

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

// Helper to format Indian currency if needed, though mostly dates and text here
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

// Data Fetching Logic (Mirrored from homePage.php)
require_once __DIR__ . '/include/rbac_helper.php';
$export1191Filter = !aitf_can_access_customer(1191, $hp_role) ? " AND ci.id != 1191" : "";
$customerList = "SELECT ci.* , ac.certification_date FROM customer_info ci left join allotment_count ac on ci.id = ac.client_id where is_deleted <> 'YES'" . $export1191Filter . " ORDER BY ac.certification_date DESC";
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
        // Two naming conventions exist in the data: older 'Suspension/Withdrawal Letter'
        // and newer 'Suspend/Withdraw Letter' (used for recent clients). Match both.
        $sqlDates = "SELECT service_id, action, due_date FROM ca_service_dates WHERE service_id IN ($inServiceIds) AND TRIM(action) IN ('Suspension Letter','Suspend Letter','Expire Letter','Withdrawal Letter','Withdraw Letter')";
        $stmt = $db->prepare($sqlDates);
        $stmt->execute($serviceIdsForDates);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $dueMap[$row['service_id']][trim($row['action'])] = $row['due_date'];
        }
    }
}

// Filtering the results in PHP to match DataTables search
$filteredCustomers = [];
foreach ($listOfCustomer as $customer) {
    $derivedStatus = hpDerivedStatus($customer, $serviceMap);

    if ($isMarketingHead && in_array($derivedStatus, array('Active', 'Withdrawn', 'Withdrawal', 'Withdraw'), true)) {
        continue;
    }

    $isCurrentOrRecent = empty($customer['certification_date']) || ($customer['certification_date'] >= date('Y-m-01', strtotime('-1 month')));
    if ($clientName != '') {
        $searchVal = strtolower(trim($clientName));
        $custName = strtolower(trim($customer['customer_name'] ?? ''));
        $uniqId = strtolower(trim($customer['unique_id'] ?? ''));
        $isMatch = (stripos($custName, $searchVal) !== false || stripos($uniqId, $searchVal) !== false);
        if (!$isMatch) {
            continue;
        }
        if ($isOfficeStaff && !$isCurrentOrRecent) {
            if ($custName !== $searchVal && $uniqId !== $searchVal) {
                continue;
            }
        }
    } else {
        if ($isOfficeStaff && empty($fromDate) && empty($toDate)) {
            if (!$isCurrentOrRecent) {
                continue;
            }
        }
    }

    // Service Type filter
    if ($serviceType != '' && strcasecmp($customer['next_service'], $serviceType) !== 0) {
        continue;
    }

    // Status Tab filter
    if ($statusFilter != '') {
        if ($derivedStatus !== $statusFilter) {
            continue;
        }
    }

    // Date filters
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

// Calculate Breakdowns for Summary
$statusCounts = [];
$nextServiceCounts = [];
foreach ($filteredCustomers as $cust) {
    $status = !empty($cust['audit_status']) ? $cust['audit_status'] : 'N/A';
    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
    
    $nextSvc = !empty($cust['next_service']) ? $cust['next_service'] : 'N/A';
    $nextServiceCounts[$nextSvc] = ($nextServiceCounts[$nextSvc] ?? 0) + 1;
}

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
        
        $hasFilters = !empty($_POST['fromDate']) || !empty($_POST['toDate']) || !empty($_POST['serviceType']) || !empty($_POST['clientName']);
        $mainTitle = $hasFilters ? 'CLIENT REPORT (FILTERED)' : 'CLIENT REPORT (ALL)';
        
        $this->SetFont('helvetica', 'B', 15);
        $this->SetX(50);
        $this->Cell(237, 10, $mainTitle, 0, 1, 'C');
        
        $filters = "";
        if(!empty($_POST['fromDate']) || !empty($_POST['toDate'])) {
            $filters .= "DATE PERIOD: " . ($_POST['fromDate'] ?? 'START') . " TO " . ($_POST['toDate'] ?? 'END') . "    ";
        } else if (!$hasFilters) {
            $filters .= "PERIOD: ALL TIME    ";
        }
        
        if(!empty($_POST['serviceType'])) {
            $filters .= "TYPE: " . strtoupper($_POST['serviceType']) . "    ";
        }
        if(!empty($_POST['clientName'])) {
            $filters .= "SEARCH: " . strtoupper($_POST['clientName']);
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
$pdf->SetTitle('Dashboard Report');
$pdf->SetMargins(10, 42, 10);
$pdf->SetAutoPageBreak(TRUE, 28);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 8);

$html = '<table border="1" cellpadding="5" cellspacing="0">
<thead>
    <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b;">
        <th width="3%" align="center">Sr.</th>
        <th width="10%" align="center">Unique Id</th>
        <th width="8%" align="center">Agr. Date</th>
        <th width="16%">Client Name</th>
        <th width="14%">Contact Person</th>
        <th width="9%">Status</th>
        <th width="8%" align="center">Due Date</th>
        <th width="6%" align="center">Next Type</th>
        <th width="6%" align="center">Hand</th>
        <th width="20%">Comment</th>
    </tr>
</thead>
<tbody>';

$count = 1;
if (empty($filteredCustomers)) {
    $html .= '<tr><td colspan="10" align="center">No records found</td></tr>';
} else {
    foreach ($filteredCustomers as $customer) {
        $serviceInfo = isset($serviceMap[$customer['id']]) ? $serviceMap[$customer['id']] : array();
        $statusStr = $customer['audit_status'];
        if ($statusStr == "in-process" && isset($serviceInfo["service_type"])) {
            $statusStr .= ' (' . $serviceInfo["service_type"] . ')';
        }
        
        $handlerName = isset($handlerMap[$customer['handler']]) ? $handlerMap[$customer['handler']] : '';

        $html .= '<tr>
            <td align="center" width="3%">' . $count++ . '</td>
            <td align="center" width="10%">' . htmlspecialchars($customer['unique_id'] ?? '') . '</td>
            <td align="center" width="8%">' . htmlspecialchars($customer['certification_date'] ?? '') . '</td>
            <td width="16%"><b>' . htmlspecialchars($customer['customer_name']) . '</b></td>
            <td width="14%">' . htmlspecialchars($customer['contact_person']) . '</td>
            <td width="9%">' . htmlspecialchars($statusStr) . '</td>
            <td align="center" width="8%">' . simpleDateConversion($customer['calculated_due_date']) . '</td>
            <td align="center" width="6%">' . htmlspecialchars($customer['next_service'] ?? '') . '</td>
            <td align="center" width="6%">' . htmlspecialchars($handlerName) . '</td>
            <td width="20%">' . htmlspecialchars($customer['comment'] ?? '') . '</td>
        </tr>';
    }
}
$html .= '</tbody></table>';

// Premium Horizontal Divider
$html .= '<br><br><table border="0" cellpadding="0" cellspacing="0"><tr><td style="border-bottom: 2px solid #0d4d8b;"></td></tr></table><br>';

// SUMMARY SECTION
$html .= '<br><br>
<div nobr="true">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td width="32%">
            <table border="1" cellpadding="6" style="background-color: #fcfcfc;">
                <tr style="background-color: #0d4d8b; color: #ffffff;">
                    <th colspan="2" align="center" style="font-weight: bold;">GENERAL SUMMARY</th>
                </tr>
                <tr>
                    <td width="70%"><b>TOTAL CLIENTS</b></td>
                    <td width="30%" align="right"><b>' . count($filteredCustomers) . '</b></td>
                </tr>
            </table>
        </td>
        <td width="2%"></td>
        <td width="32%">
            <table border="1" cellpadding="6" style="background-color: #fcfcfc;">
                <tr style="background-color: #0d4d8b; color: #ffffff;">
                    <th colspan="2" align="center" style="font-weight: bold;">STATUS BREAKDOWN</th>
                </tr>';
foreach ($statusCounts as $status => $count) {
    $html .= '<tr>
        <td width="75%">' . htmlspecialchars($status) . '</td>
        <td width="25%" align="right">' . $count . '</td>
    </tr>';
}
if (empty($statusCounts)) {
    $html .= '<tr><td colspan="2" align="center">N/A</td></tr>';
}
$html .= '    </table>
        </td>
        <td width="2%"></td>
        <td width="32%">
            <table border="1" cellpadding="6" style="background-color: #fcfcfc;">
                <tr style="background-color: #0d4d8b; color: #ffffff;">
                    <th colspan="2" align="center" style="font-weight: bold;">SERVICE TYPE BREAKDOWN</th>
                </tr>';
foreach ($nextServiceCounts as $svc => $count) {
    $html .= '<tr>
        <td width="75%">' . htmlspecialchars($svc) . '</td>
        <td width="25%" align="right">' . $count . '</td>
    </tr>';
}
if (empty($nextServiceCounts)) {
    $html .= '<tr><td colspan="2" align="center">N/A</td></tr>';
}
$html .= '    </table>
        </td>
    </tr>
    </table>
</div>';

$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf->Output('Dashboard_Report.pdf', 'I');

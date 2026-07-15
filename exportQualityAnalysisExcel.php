<?php
/**
 * exportQualityAnalysisExcel.php
 * Exports the Quality Analysis report as an .xlsx download.
 * Reuses the SimpleXlsx class from invoice_report_lib.php.
 */

include_once __DIR__ . '/dbConnect.php';
include_once __DIR__ . '/commonutil.php';
include_once __DIR__ . '/include/invoice_report_lib.php';

// SimpleXlsx internally calls invoiceReportLog(); define a no-op if not already available
if (!function_exists('invoiceReportLog')) {
    function invoiceReportLog() {}
}

date_default_timezone_set('Asia/Kolkata');

while (ob_get_level() > 0) {
    ob_end_clean();
}

// Read filter params
$serviceType   = $_POST['serviceType'] ?? '';
$auditorFilter = $_POST['auditorFilter'] ?? '';
$fromDate      = $_POST['fromDate'] ?? '';
$toDate        = $_POST['toDate'] ?? '';

// ---- Fetch data (same logic as qualityanalysis.php) ----

$sql = "SELECT ci.id, customer_name, svc.service_id, service_type, svc.audit_date 
        FROM customer_info ci 
        JOIN service svc ON ci.id = svc.customer_id 
        WHERE is_deleted <> 'Y' 
        ORDER BY svc.audit_date DESC";
$statement = $db->prepare($sql);
$statement->execute();
$listOfCustomer = $statement->fetchAll();

$serviceIdsByType = [
    'CA' => [], 'SA-I' => [], 'SA-II' => [], 'RC' => [], 'SP' => []
];
foreach ($listOfCustomer as $c) {
    if (isset($serviceIdsByType[$c['service_type']])) {
        $serviceIdsByType[$c['service_type']][] = $c['service_id'];
    }
}

$loadMap = function($table, $serviceCol, $filterIds) use (&$db) {
    if (empty($filterIds)) return [];
    $ph = implode(',', array_fill(0, count($filterIds), '?'));
    $sql = "SELECT * FROM {$table} WHERE {$serviceCol} IN ($ph)";
    $stmt = $db->prepare($sql);
    $stmt->execute($filterIds);
    $out = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $out[$r[$serviceCol]] = $r;
    }
    return $out;
};

$caMap = $loadMap('service_type_ca', 'service', $serviceIdsByType['CA']);
$saiMap = $loadMap('service_type_sai', 'service', $serviceIdsByType['SA-I']);
$saiiMap = $loadMap('service_type_saii', 'service', $serviceIdsByType['SA-II']);
$rcMap = $loadMap('service_type_rc', 'service', $serviceIdsByType['RC']);
$spMap = $loadMap('service_type_sp', 'service', $serviceIdsByType['SP']);

$subtypeIds = array_merge(
    array_column($caMap, 'ca_id'),
    array_column($saiMap, 'sai_id'),
    array_column($saiiMap, 'saii_id'),
    array_column($rcMap, 'rc_id'),
    array_column($spMap, 'sp_id')
);
$subtypeIds = array_filter($subtypeIds, function($v){ return $v !== null && $v !== ''; });

// customer_survey_id
$surveyIdMap = [];
$surveyDetailMap = [];
if (!empty($subtypeIds)) {
    $ph = implode(',', array_fill(0, count($subtypeIds), '?'));
    $sqlSurveyIds = "SELECT ca_serviceid, survey_id, auditing_quality, mgmt_satisfaction, aggregate, ncval, ofival, oplusval FROM customer_survey_id WHERE ca_serviceid IN ($ph)";
    $stmt = $db->prepare($sqlSurveyIds);
    $stmt->execute($subtypeIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $surveyIdMap[$r['ca_serviceid']] = $r['survey_id'];
        $surveyDetailMap[$r['ca_serviceid']] = $r;
    }
}

// customer_survey aggregated
$surveyAgg = [];
$surveyIds = array_values(array_filter($surveyIdMap));
if (!empty($surveyIds)) {
    $ph = implode(',', array_fill(0, count($surveyIds), '?'));
    $sqlSurvey = "SELECT survayid, lead_auditor, marks FROM customer_survey WHERE survayid IN ($ph)";
    $stmt = $db->prepare($sqlSurvey);
    $stmt->execute($surveyIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $sid = $r['survayid'];
        if (!isset($surveyAgg[$sid])) {
            $surveyAgg[$sid] = ['auditors' => [], 'marks' => []];
        }
        $surveyAgg[$sid]['auditors'][] = $r['lead_auditor'];
        $surveyAgg[$sid]['marks'][] = $r['marks'];
    }
    foreach ($surveyAgg as $sid => $vals) {
        $surveyAgg[$sid]['auditors'] = implode(', ', array_filter($vals['auditors']));
        $surveyAgg[$sid]['marks'] = implode(', ', array_filter($vals['marks']));
    }
}

// cdc_report_review
$cdcMap = [];
if (!empty($subtypeIds)) {
    $ph = implode(',', array_fill(0, count($subtypeIds), '?'));
    $sqlCdc = "SELECT * FROM cdc_report_review WHERE ca_service_id IN ($ph)";
    $stmt = $db->prepare($sqlCdc);
    $stmt->execute($subtypeIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $cdcMap[$r['ca_service_id']] = $r;
    }
}

// ---- Date filter helper ----
function compareDates($d1, $d2) {
    // d1, d2 in dd.mm.yyyy format, returns d1 - d2 comparison
    $p1 = explode('.', $d1);
    $p2 = explode('.', $d2);
    if (count($p1) < 3 || count($p2) < 3) return 0;
    $t1 = mktime(0, 0, 0, $p1[1], $p1[0], $p1[2]);
    $t2 = mktime(0, 0, 0, $p2[1], $p2[0], $p2[2]);
    return $t1 - $t2;
}

// ---- Build rows ----
$reportRows = [];
foreach ($listOfCustomer as $customer) {
    $svcId = $customer['service_id'];
    $svcType = $customer['service_type'];

    if ($svcType == 'CA') {
        $sub = isset($caMap[$svcId]) ? $caMap[$svcId] : null;
    } elseif ($svcType == 'SA-I') {
        $sub = isset($saiMap[$svcId]) ? $saiMap[$svcId] : null;
    } elseif ($svcType == 'SA-II') {
        $sub = isset($saiiMap[$svcId]) ? $saiiMap[$svcId] : null;
    } elseif ($svcType == 'RC') {
        $sub = isset($rcMap[$svcId]) ? $rcMap[$svcId] : null;
    } elseif ($svcType == 'SP') {
        $sub = isset($spMap[$svcId]) ? $spMap[$svcId] : null;
    } else {
        $sub = null;
    }

    if (!$sub) continue;

    $subId = null;
    if ($svcType == 'CA') $subId = $sub['ca_id'];
    else if ($svcType == 'SA-I') $subId = $sub['sai_id'];
    else if ($svcType == 'SA-II') $subId = $sub['saii_id'];
    else if ($svcType == 'RC') $subId = $sub['rc_id'];
    else if ($svcType == 'SP') $subId = $sub['sp_id'];

    if (!$subId) continue;

    $surveyId = isset($surveyIdMap[$subId]) ? $surveyIdMap[$subId] : null;
    $agg = isset($surveyAgg[$surveyId]) ? $surveyAgg[$surveyId] : ['auditors' => '', 'marks' => ''];
    $surveyDetail = isset($surveyDetailMap[$subId]) ? $surveyDetailMap[$subId] : null;
    $cdc = isset($cdcMap[$subId]) ? $cdcMap[$subId] : null;
    if (!$surveyDetail) continue;

    $auditDateFormatted = simpleDateConversion($customer['audit_date']);

    // Auditor display
    $auditorDisplay = $agg['auditors'];
    if (empty($auditorDisplay) && $cdc && !empty($cdc['lead_auditor'])) {
        $auditorDisplay = $cdc['lead_auditor'];
    }

    // NC/OFI/O+ with fallback
    $ncDisplay = $cdc ? $cdc['ncval'] : '';
    if (empty($ncDisplay) && $surveyDetail && !empty($surveyDetail['ncval'])) $ncDisplay = $surveyDetail['ncval'];
    $ofiDisplay = $cdc ? $cdc['ofival'] : '';
    if (empty($ofiDisplay) && $surveyDetail && !empty($surveyDetail['ofival'])) $ofiDisplay = $surveyDetail['ofival'];
    $oplusDisplay = $cdc ? $cdc['oplusval'] : '';
    if (empty($oplusDisplay) && $surveyDetail && !empty($surveyDetail['oplusval'])) $oplusDisplay = $surveyDetail['oplusval'];

    // Apply filters
    // Service type filter
    if ($serviceType !== '' && strtoupper(trim($svcType)) !== strtoupper(trim($serviceType))) {
        continue;
    }

    // Auditor filter
    if ($auditorFilter !== '' && stripos($auditorDisplay, $auditorFilter) === false) {
        continue;
    }

    // Date filter
    if ($fromDate !== '' && $auditDateFormatted !== '') {
        if (compareDates($auditDateFormatted, $fromDate) < 0) continue;
    }
    if ($toDate !== '' && $auditDateFormatted !== '') {
        if (compareDates($auditDateFormatted, $toDate) > 0) continue;
    }

    $reportRows[] = [
        'client' => $customer['customer_name'],
        'service_type' => $svcType,
        'audit_date' => $auditDateFormatted,
        'auditing_quality' => $surveyDetail ? $surveyDetail['auditing_quality'] : '',
        'mgmt_satisfaction' => $surveyDetail ? $surveyDetail['mgmt_satisfaction'] : '',
        'auditor' => $auditorDisplay,
        'individual_marks' => $agg['marks'],
        'aggregate' => $surveyDetail ? $surveyDetail['aggregate'] : '',
        'nc' => $ncDisplay,
        'ofi' => $ofiDisplay,
        'oplus' => $oplusDisplay,
    ];
}

// ---- Build Excel ----
$xlsx = new SimpleXlsx();

// Freeze header rows
$xlsx->freezePanesAbove(5);

// Column widths (A..K)
$xlsx->setColWidth(1, 6);    // Sr
$xlsx->setColWidth(2, 30);   // Client
$xlsx->setColWidth(3, 14);   // Service Type
$xlsx->setColWidth(4, 14);   // Audit Date
$xlsx->setColWidth(5, 18);   // Rating A
$xlsx->setColWidth(6, 18);   // Rating B
$xlsx->setColWidth(7, 22);   // Auditor
$xlsx->setColWidth(8, 14);   // Individual marks
$xlsx->setColWidth(9, 18);   // Assessment Process
$xlsx->setColWidth(10, 8);   // NC
$xlsx->setColWidth(11, 8);   // OFI
$xlsx->setColWidth(12, 8);   // O+

// Build filter description
$filters = '';
if (!empty($serviceType)) {
    $filters .= 'Service: ' . strtoupper($serviceType);
}
if (!empty($auditorFilter)) {
    $filters .= ($filters !== '' ? ', ' : '') . 'Auditor: ' . $auditorFilter;
}
if (!empty($fromDate) || !empty($toDate)) {
    $filters .= ($filters !== '' ? ', ' : '') . 'Period: ' . ($fromDate !== '' ? $fromDate : 'Start') . ' to ' . ($toDate !== '' ? $toDate : 'End');
}
if ($filters === '') { $filters = 'All Records'; }

// Row 1: Title
$xlsx->addRow([['v' => 'AITF MANAGEMENT CERTIFICATION LIMITED', 's' => 1, 't' => 's']]);
$xlsx->merge('A1:L1');
$xlsx->setRowHeight(1, 28);

// Row 2: Subtitle
$xlsx->addRow([['v' => 'QUALITY ANALYSIS REPORT (' . $filters . ')', 's' => 2, 't' => 's']]);
$xlsx->merge('A2:L2');
$xlsx->setRowHeight(2, 20);

// Row 3: Accent spacer
$accentRow = [];
for ($ac = 0; $ac < 12; $ac++) {
    $accentRow[] = ['v' => '', 's' => 38, 't' => 's'];
}
$xlsx->addRow($accentRow);
$xlsx->setRowHeight(3, 8);

// Row 4: Column headers
$headers = ['Sr.', 'Client', 'Service Type', 'Audit Date', 
            'Rating (A) (Out of 50)', 'Certification (B) (Out of 35)', 
            'Auditor', 'Individual Marks', 
            'Assessment Process (u) of A & B', 'NC', 'OFI', 'O+'];
$headerRow = [];
foreach ($headers as $h) {
    $headerRow[] = ['v' => $h, 's' => 4, 't' => 's'];
}
$xlsx->addRow($headerRow);
$xlsx->setRowHeight(4, 30);

// Data rows
if (empty($reportRows)) {
    $xlsx->addRow([['v' => 'No records found', 's' => 7, 't' => 's']]);
    $xlsx->merge('A5:L5');
} else {
    $count = 1;
    foreach ($reportRows as $row) {
        $dataRow = [];
        $dataRow[] = ['v' => $count++, 's' => 5, 't' => 'n'];
        $dataRow[] = ['v' => $row['client'], 's' => 6, 't' => 's'];
        $dataRow[] = ['v' => $row['service_type'], 's' => 5, 't' => 's'];
        $dataRow[] = ['v' => $row['audit_date'], 's' => 5, 't' => 's'];
        $dataRow[] = ['v' => $row['auditing_quality'], 's' => 5, 't' => 's'];
        $dataRow[] = ['v' => $row['mgmt_satisfaction'], 's' => 5, 't' => 's'];
        $dataRow[] = ['v' => $row['auditor'], 's' => 6, 't' => 's'];
        $dataRow[] = ['v' => $row['individual_marks'], 's' => 5, 't' => 's'];
        $dataRow[] = ['v' => $row['aggregate'], 's' => 5, 't' => 's'];
        $dataRow[] = ['v' => $row['nc'], 's' => 5, 't' => 's'];
        $dataRow[] = ['v' => $row['ofi'], 's' => 5, 't' => 's'];
        $dataRow[] = ['v' => $row['oplus'], 's' => 5, 't' => 's'];
        $xlsx->addRow($dataRow);
    }
}

// Summary row
$xlsx->addRow([['v' => '', 's' => 0, 't' => 's']]);
$summaryRowNum = count($reportRows) + 6;
$xlsx->addRow([
    ['v' => '', 's' => 0, 't' => 's'],
    ['v' => 'Total Records: ' . count($reportRows), 's' => 1, 't' => 's']
]);
$xlsx->merge('B' . $summaryRowNum . ':D' . $summaryRowNum);

$fileName = 'Quality_Analysis_Report_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$bytes = $xlsx->toString('Quality Analysis');
header('Content-Length: ' . strlen($bytes));
echo $bytes;
exit;

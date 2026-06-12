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
$auditorSearch = $_POST['auditor'] ?? '';
$serviceTypeSearch = $_POST['serviceType'] ?? '';
$clientNameSearch = $_POST['clientName'] ?? '';

// Data Fetching Logic (Mirrored from auditPlanning.php)
$sql = "SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, svc.service_type, service_status, 'sa1' as stage1
        FROM customer_info ci
        LEFT JOIN service svc on ci.id = svc.customer_id
        WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id)
        AND svc.service_status = 'Audit planning'
        UNION
        SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, svc.service_type, service_status, 'sa2' as stage1
        FROM customer_info ci
        LEFT JOIN service svc on ci.id = svc.customer_id
        WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id)
        AND svc.service_status = 'Audit planning'";

$statement = $db->prepare($sql);
$statement->execute();
$allRawData = $statement->fetchAll(PDO::FETCH_ASSOC);

$filteredRecords = [];
foreach ($allRawData as $row) {
    $serviceid = "";
    if ($row["service_type"] == "CA") {
        $sqlSvc = "SELECT ca_id FROM `service_type_ca` where service = :service";
        $st = $db->prepare($sqlSvc); $st->execute([':service' => $row["service_id"]]);
        $serviceid = $st->fetchColumn();
    } else if ($row["service_type"] == "SA-I") {
        $sqlSvc = "SELECT sai_id FROM `service_type_sai` where service = :service";
        $st = $db->prepare($sqlSvc); $st->execute([':service' => $row["service_id"]]);
        $serviceid = $st->fetchColumn();
    } else if ($row["service_type"] == "SA-II") {
        $sqlSvc = "SELECT saii_id FROM `service_type_saii` where service = :service";
        $st = $db->prepare($sqlSvc); $st->execute([':service' => $row["service_id"]]);
        $serviceid = $st->fetchColumn();
    } else if ($row["service_type"] == "RC") {
        $sqlSvc = "SELECT rc_id FROM `service_type_rc` where service = :service";
        $st = $db->prepare($sqlSvc); $st->execute([':service' => $row["service_id"]]);
        $serviceid = $st->fetchColumn();
    } else if ($row["service_type"] == "SP") {
        $sqlSvc = "SELECT sp_id FROM `service_type_sp` where service = :service";
        $st = $db->prepare($sqlSvc); $st->execute([':service' => $row["service_id"]]);
        $serviceid = $st->fetchColumn();
    }

    $saidetails = null;
    if ($row['stage1'] == "sa1") {
        $saiSql = "SELECT `audit_dates`, `auditor_list` FROM `stage_oneaudit_id` WHERE `ca_service_id` = :id";
        $st = $db->prepare($saiSql); $st->execute([':id' => $serviceid]);
        $saidetails = $st->fetch(PDO::FETCH_ASSOC);
    } else if ($row['stage1'] == "sa2") {
        $saiiSql = "SELECT `audit_dates`, `auditor_list` FROM `stage_twoaudit_id` WHERE `service_id` = :id";
        $st = $db->prepare($saiiSql); $st->execute([':id' => $serviceid]);
        $saidetails = $st->fetch(PDO::FETCH_ASSOC);
    }

    if (!$saidetails || empty($saidetails['audit_dates'])) continue;

    $row['audit_dates'] = $saidetails['audit_dates'];
    $row['auditor_list'] = $saidetails['auditor_list'];

    // PHP Filtering to match JS search
    if ($clientNameSearch != '' && stripos($row['customer_name'], $clientNameSearch) === false) continue;
    if ($auditorSearch != '' && stripos($row['auditor_list'], $auditorSearch) === false) continue;
    if ($serviceTypeSearch != '' && strcasecmp(trim($row['service_type']), trim($serviceTypeSearch)) !== 0) continue;

    if ($fromDate != '') {
        $fDate = date("Y-m-d", strtotime(str_replace('.', '-', $fromDate)));
        $rDate = date("Y-m-d", strtotime(str_replace('.', '-', $row['audit_dates'])));
        if ($rDate < $fDate) continue;
    }
    if ($toDate != '') {
        $tDate = date("Y-m-d", strtotime(str_replace('.', '-', $toDate)));
        $rDate = date("Y-m-d", strtotime(str_replace('.', '-', $row['audit_dates'])));
        if ($rDate > $tDate) continue;
    }

    $filteredRecords[] = $row;
}

// Breakdowns
$auditorCounts = [];
$serviceCounts = [];
foreach ($filteredRecords as $rec) {
    $auditors = array_map('trim', explode(',', $rec['auditor_list']));
    foreach ($auditors as $aud) {
        if ($aud != '') $auditorCounts[$aud] = ($auditorCounts[$aud] ?? 0) + 1;
    }
    $svc = $rec['service_type'];
    $serviceCounts[$svc] = ($serviceCounts[$svc] ?? 0) + 1;
}

class REPORTPDF extends TCPDF {
    public function Header() {
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        if (file_exists($image_file)) {
            $this->Image($image_file, 10, 6, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        $this->SetFont('helvetica', 'B', 22);
        $this->SetY(6); $this->SetX(50);
        $this->Cell(237, 12, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, 1, 'C');

        $hasFilters = !empty($_POST['fromDate']) || !empty($_POST['toDate']) || !empty($_POST['auditor']) || !empty($_POST['clientName']) || !empty($_POST['serviceType']);
        $mainTitle = $hasFilters ? 'AUDIT PLANNING REPORT (FILTERED)' : 'AUDIT PLANNING REPORT (ALL)';
        $this->SetFont('helvetica', 'B', 15);
        $this->SetX(50);
        $this->Cell(237, 10, $mainTitle, 0, 1, 'C');

        $filters = "";
        if(!empty($_POST['fromDate']) || !empty($_POST['toDate'])) $filters .= "PERIOD: ".($_POST['fromDate'] ?? 'START')." TO ".($_POST['toDate'] ?? 'END')."  ";
        if(!empty($_POST['auditor'])) $filters .= "AUDITOR: ".strtoupper($_POST['auditor'])."  ";
        if(!empty($_POST['serviceType'])) $filters .= "TYPE: ".strtoupper($_POST['serviceType'])."  ";
        if(!empty($_POST['clientName'])) $filters .= "SEARCH: ".strtoupper($_POST['clientName']);
        if($filters) {
            $this->SetFont('helvetica', 'B', 10);
            $this->SetX(50); $this->Cell(237, 6, $filters, 0, 1, 'C');
        }
        $this->Ln(2);
        $this->Line(10, 38, 287, 38);
    }
    public function Footer() {
        $this->SetY(-20);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C');
    }
}

$pdf = new REPORTPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetTitle('Audit Planning Report');
$pdf->SetMargins(10, 42, 10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

$html = '<table border="1" cellpadding="5" cellspacing="0" width="100%">
<thead>
    <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b;">
        <th width="4%" align="center">Sr.</th>
        <th width="12%" align="center">Unique Id</th>
        <th width="28%">Client Name</th>
        <th width="8%" align="center">Svc Type</th>
        <th width="12%" align="center">Audit Date</th>
        <th width="36%">Auditor(s)</th>
    </tr>
</thead>
<tbody>';

$count = 1;
if (empty($filteredRecords)) {
    $html .= '<tr><td colspan="6" align="center">No records found</td></tr>';
} else {
    foreach ($filteredRecords as $rec) {
        $html .= '<tr>
            <td align="center" width="4%">' . $count++ . '</td>
            <td align="center" width="12%">' . htmlspecialchars($rec['unique_id']) . '</td>
            <td width="28%"><b>' . htmlspecialchars($rec['customer_name']) . '</b></td>
            <td align="center" width="8%">' . htmlspecialchars($rec['service_type']) . '</td>
            <td align="center" width="12%">' . htmlspecialchars($rec['audit_dates']) . '</td>
            <td width="36%">' . htmlspecialchars($rec['auditor_list']) . '</td>
        </tr>';
    }
}
$html .= '</tbody></table>';

// Premium Horizontal Divider
$html .= '<br><br><table border="0" cellpadding="0" cellspacing="0"><tr><td style="border-bottom: 2px solid #0d4d8b;"></td></tr></table><br>';

// SUMMARY
$html .= '<br><br><div nobr="true">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td width="48%">
    <table border="1" cellpadding="6" style="background-color: #fcfcfc;">
        <tr style="background-color: #0d4d8b; color: #ffffff;"><th colspan="2" align="center"><b>';

if (!empty($auditorSearch) && !empty($serviceTypeSearch)) {
    $html .= 'TOTAL ' . strtoupper($serviceTypeSearch) . ' AUDITS BY ' . strtoupper($auditorSearch);
} else if (!empty($auditorSearch)) {
    $html .= 'TOTAL AUDITS BY ' . strtoupper($auditorSearch);
} else if (!empty($serviceTypeSearch)) {
    $html .= 'TOTAL ' . strtoupper($serviceTypeSearch) . ' AUDITS';
} else {
    $html .= 'AUDITOR BREAKDOWN';
}

$html .= '</b></th></tr>';

if (!empty($auditorSearch) || !empty($serviceTypeSearch)) {
    // If targeted filter is active, show descriptive summary row as requested
    $finalSummaryStr = 'Total ';
    if (!empty($serviceTypeSearch)) $finalSummaryStr .= strtoupper($serviceTypeSearch) . ' ';
    $finalSummaryStr .= 'Audit ';
    if (!empty($auditorSearch)) $finalSummaryStr .= 'by ' . strtoupper($auditorSearch) . ' ';
    $finalSummaryStr .= '- ' . count($filteredRecords);

    $html .= '<tr><td width="100%" colspan="2"><b>' . $finalSummaryStr . '</b></td></tr>';
} else {
    // Generic breakdown when no specific search
    foreach ($auditorCounts as $aud => $c) {
        $html .= '<tr><td width="80%">' . $aud . '</td><td width="20%" align="right">' . $c . '</td></tr>';
    }
}
$html .= '</table></td>
<td width="4%"></td>
<td width="48%">
    <table border="1" cellpadding="6" style="background-color: #fcfcfc;">
        <tr style="background-color: #0d4d8b; color: #ffffff;"><th colspan="2" align="center"><b>SERVICE BREAKDOWN</b></th></tr>';
foreach ($serviceCounts as $s => $c) {
    $html .= '<tr><td width="80%">' . $s . '</td><td width="20%" align="right">' . $c . '</td></tr>';
}
$html .= '<tr><td style="background-color:#eee;"><b>GRAND TOTAL SUM</b></td><td align="right" style="background-color:#eee;"><b>' . count($filteredRecords) . '</b></td></tr>
    </table></td></tr></table></div>';

$pdf->writeHTML($html, true, false, true, false, '');
while (ob_get_level() > 0) ob_end_clean();
$pdf->Output('Audit_Planning_Report.pdf', 'I');

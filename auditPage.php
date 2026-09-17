<div id="wrapper" class="active">
    <!-- Sidebar -->

    <?php
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
    $pageIdentifier = "Audit Records";
    include 'header.php';
    include 'dbConnect.php';
    include_once './include/customer_feature_flags.php';
    $auditDateConflictVisible = aitfCustomerFeatureIsEnabled($db, 'audit_date_conflict_visible');
    $auditMemberConflictVisible = aitfCustomerFeatureIsEnabled($db, 'audit_member_conflict_visible');

    $invRole = trim($_SESSION['role'] ?? '');
    $isMarketingHead = in_array($invRole, array('Marketing Head', 'Marketing Executive'), true)
        || strtolower(str_replace(' ', '', $invRole)) === 'marketinghead'
        || (isset($_SESSION['username']) && strtolower(trim($_SESSION['username'])) === 'marketingh');
    $isOfficeStaff = in_array($invRole, array('Office Staff-A', 'Office Staff-B'), true)
        || in_array(strtolower(str_replace(array(' ', '-'), '', $invRole)), array('officestaffa', 'officestaffb'), true);
    $isRestrictedRole = $isMarketingHead || $isOfficeStaff;

    //    $marketingInfo = "SELECT distinct ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, comment, certification_stage_date, add_to, cas.ca_id, service_ca_status, cas.customer_id, saia.sai_id, saia.customer_id,saia.service_sai_status, saiis.saii_id, saiis.customer_id, saiis.service_saii_ststus, rcs.rc_id, rcs.rc_customer, rcs.service_rc_status, sps.sp_id, sps.sp_customer, sps.service_sp_status FROM customer_info ci left join service_type_ca cas on ci.id = cas.customer_id left join service_type_sai saia on ci.id = saia.customer_id left join service_type_saii saiis on ci.id = saiis.customer_id left join service_type_rc rcs on ci.id = rcs.rc_customer left join service_type_sp sps on ci.id = sps.sp_customer WHERE ((cas.service_ca_status = 'Marketing' and cas.ca_id = (select max(tmp.ca_id) from service_type_ca tmp where tmp.customer_id = ci.id)) or (saia.service_sai_status = 'Marketing' and saia.sai_id = (select max(tmp1.sai_id) from service_type_sai tmp1 where tmp1.customer_id = ci.id) ) or (saiis.service_saii_ststus = 'Marketing' and saiis.saii_id = (select max(tmp2.saii_id) from service_type_saii tmp2 where tmp2.customer_id = ci.id)) or (rcs.service_rc_status = 'Marketing' and rcs.rc_id = (select max(tmp3.rc_id) from service_type_rc tmp3 where tmp3.rc_customer = ci.id) ) or (sps.service_sp_status = 'Marketing' and sps.sp_id = (select max(tmp4.sp_id) from service_type_sp tmp4 where tmp4.sp_customer = ci.id)) )";
    $marketingInfo = "SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, next_service, svc.service_id, service_type, service_status, audit_date FROM customer_info ci left join service svc on ci.id = svc.customer_id where svc.audit_date is not null ORDER BY audit_date DESC";
    $statement = $db->prepare($marketingInfo);
    $statement->execute();
    $allMarketingInfo = $statement->fetchAll();

    $services = "SELECT * FROM service_types";
    $statement = $db->prepare($services);
    $statement->execute();
    $servicesTypes = $statement->fetchAll();

    $auditors = "SELECT * FROM `auditors`";
    $statement = $db->prepare($auditors);
    $statement->execute();
    $auditorsList = $statement->fetchAll();

    // Batch preload service subtype data to avoid per-row queries
    $serviceIdsByType = [
        'CA' => [],
        'SA-I' => [],
        'SA-II' => [],
        'RC' => [],
        'SP' => [],
        'TA' => []
    ];
    foreach ($allMarketingInfo as $row) {
        if (isset($serviceIdsByType[$row['service_type']])) {
            $serviceIdsByType[$row['service_type']][] = $row['service_id'];
        }
    }

    $caMap = $saiMap = $saiiMap = $rcMap = $spMap = $taMap = [];

    $loadMap = function ($table, $serviceCol, $filterIds) use (&$db) {
        if (empty($filterIds))
            return [];
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
    $taMap = $loadMap('service_type_ta', 'service_id', $serviceIdsByType['TA']);

    $subtypeIds = array_merge(
        array_column($caMap, 'ca_id'),
        array_column($saiMap, 'sai_id'),
        array_column($saiiMap, 'saii_id'),
        array_column($rcMap, 'rc_id'),
        array_column($spMap, 'sp_id')
    );
    $subtypeIds = array_filter($subtypeIds, function ($v) {
        return $v !== null && $v !== ''; });

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

    function auditPageSplitAuditors($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }
        $parts = preg_split('/\s*(?:,|;|\/|&|\band\b)\s*/i', $value);
        $auditors = [];
        foreach ($parts as $part) {
            $part = preg_replace('/\s+/', ' ', trim($part));
            if ($part !== '') {
                $auditors[] = $part;
            }
        }
        return $auditors;
    }

    function auditPageNormalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00') {
            return '';
        }

        $formats = ['Y-m-d', 'Y/m/d', 'Y.m.d', 'd.m.Y', 'd-m-Y', 'd/m/Y', 'd M Y', 'd F Y', 'M d Y', 'F d Y'];
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat('!' . $format, $value);
            if ($date instanceof DateTime) {
                $errors = DateTime::getLastErrors();
                if (($errors === false || ($errors['warning_count'] == 0 && $errors['error_count'] == 0))) {
                    return $date->format('Y-m-d');
                }
            }
        }

        $timestamp = strtotime($value);
        return $timestamp !== false ? date('Y-m-d', $timestamp) : '';
    }

    function auditPageAuditDates($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        $candidates = [];
        $patterns = [
            '/\b\d{4}[-\/.]\d{1,2}[-\/.]\d{1,2}\b/',
            '/\b\d{1,2}[-\/.]\d{1,2}[-\/.]\d{4}\b/',
            '/\b\d{1,2}\s+[A-Za-z]{3,9}\s+\d{4}\b/',
            '/\b[A-Za-z]{3,9}\s+\d{1,2}\s+\d{4}\b/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $value, $matches)) {
                foreach ($matches[0] as $match) {
                    $candidates[] = $match;
                }
            }
        }
        if (empty($candidates)) {
            $candidates[] = $value;
        }

        $dates = [];
        foreach ($candidates as $candidate) {
            $date = auditPageNormalizeDate($candidate);
            if ($date !== '') {
                $dates[$date] = true;
            }
        }
        return array_keys($dates);
    }

    function auditPageAuditRowKey($marketingDetail, $auditType)
    {
        return (isset($marketingDetail['service_id']) ? $marketingDetail['service_id'] : '') . '|' . $auditType;
    }

    function auditPageConflictClass(array $auditConflictRows, $marketingDetail, $auditType)
    {
        return isset($auditConflictRows[auditPageAuditRowKey($marketingDetail, $auditType)]) ? 'audit-conflict-row' : '';
    }

    function auditPageTrackAuditRow(array &$auditorDateRows, array &$auditorDateMeta, $rowKey, $auditors, $dates)
    {
        foreach (auditPageSplitAuditors($auditors) as $auditor) {
            $auditorKey = strtolower($auditor);
            foreach (auditPageAuditDates($dates) as $date) {
                $conflictKey = $auditorKey . '|' . $date;
                if (!isset($auditorDateRows[$conflictKey])) {
                    $auditorDateRows[$conflictKey] = [];
                }
                if (!isset($auditorDateMeta[$conflictKey])) {
                    $auditorDateMeta[$conflictKey] = ['auditor' => $auditor, 'date' => $date];
                }
                $auditorDateRows[$conflictKey][$rowKey] = true;
            }
        }
    }

    function auditPageDisplayAuditDates($value)
    {
        $displayDates = [];
        foreach (auditPageAuditDates($value) as $date) {
            $timestamp = strtotime($date);
            $displayDates[] = $timestamp !== false ? date('d.m.Y', $timestamp) : $date;
        }
        return !empty($displayDates) ? implode(', ', $displayDates) : trim((string) $value);
    }

    function auditPageAddAuditRow(array &$auditorDateRows, array &$auditorDateMeta, array &$auditRowDetails, $marketingDetail, $auditType, $auditors, $dates)
    {
        $rowKey = auditPageAuditRowKey($marketingDetail, $auditType);
        $auditRowDetails[$rowKey] = [
            'customer_id' => isset($marketingDetail['id']) ? $marketingDetail['id'] : '',
            'unique_id' => isset($marketingDetail['unique_id']) ? $marketingDetail['unique_id'] : '',
            'customer_name' => isset($marketingDetail['customer_name']) ? $marketingDetail['customer_name'] : '',
            'service_type' => $auditType,
            'service_status' => isset($marketingDetail['service_status']) ? $marketingDetail['service_status'] : '',
            'audit_status' => isset($marketingDetail['audit_status']) ? $marketingDetail['audit_status'] : '',
            'next_service' => isset($marketingDetail['next_service']) ? $marketingDetail['next_service'] : '',
            'auditors' => trim((string) $auditors),
            'dates' => auditPageDisplayAuditDates($dates),
        ];
        auditPageTrackAuditRow($auditorDateRows, $auditorDateMeta, $rowKey, $auditors, $dates);
    }

    $auditorDateRows = [];
    $auditorDateMeta = [];
    $auditRowDetails = [];
    foreach ($allMarketingInfo as $marketingDetail) {
        if ($marketingDetail["service_type"] == "CA") {
            $auditDetails = isset($caMap[$marketingDetail['service_id']]) ? $caMap[$marketingDetail['service_id']] : null;
            if (!$auditDetails) {
                continue;
            }
            auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'ST-1', isset($auditDetails["stage1_auditor"]) ? $auditDetails["stage1_auditor"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : '');
            if (!empty($auditDetails["stage2_audit"])) {
                auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'ST-2', isset($auditDetails["stage2_auditor"]) ? $auditDetails["stage2_auditor"] : '', $auditDetails["stage2_audit"]);
            }
        } else if ($marketingDetail["service_type"] == "SA-I") {
            $auditDetails = isset($saiMap[$marketingDetail['service_id']]) ? $saiMap[$marketingDetail['service_id']] : null;
            if ($auditDetails) {
                auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'SA-I', isset($auditDetails["auditors"]) ? $auditDetails["auditors"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : '');
            }
        } else if ($marketingDetail["service_type"] == "SA-II") {
            $auditDetails = isset($saiiMap[$marketingDetail['service_id']]) ? $saiiMap[$marketingDetail['service_id']] : null;
            if ($auditDetails) {
                auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'SA-II', isset($auditDetails["auditors"]) ? $auditDetails["auditors"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : '');
            }
        } else if ($marketingDetail["service_type"] == "RC") {
            $auditDetails = isset($rcMap[$marketingDetail['service_id']]) ? $rcMap[$marketingDetail['service_id']] : null;
            if ($auditDetails) {
                auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'RC', isset($auditDetails["auditors"]) ? $auditDetails["auditors"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : '');
            }
        } else if ($marketingDetail["service_type"] == "SP") {
            $auditDetails = isset($spMap[$marketingDetail['service_id']]) ? $spMap[$marketingDetail['service_id']] : null;
            if ($auditDetails) {
                auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'SP', isset($auditDetails["auditors"]) ? $auditDetails["auditors"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : '');
            }
        } else if ($marketingDetail["service_type"] == "TA") {
            $auditDetails = isset($taMap[$marketingDetail['service_id']]) ? $taMap[$marketingDetail['service_id']] : null;
            if ($auditDetails) {
                auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'TA', isset($auditDetails["auditors"]) ? $auditDetails["auditors"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : '');
            }
        }
    }

    $auditConflictRows = [];
    $auditConflictGroups = [];
    foreach ($auditorDateRows as $conflictKey => $rowsByAudit) {
        if (count($rowsByAudit) > 1) {
            $groupRows = [];
            foreach ($rowsByAudit as $rowKey => $unused) {
                $auditConflictRows[$rowKey] = true;
                if (isset($auditRowDetails[$rowKey])) {
                    $groupRows[] = $auditRowDetails[$rowKey];
                }
            }
            if (!empty($groupRows)) {
                $meta = isset($auditorDateMeta[$conflictKey]) ? $auditorDateMeta[$conflictKey] : ['auditor' => '', 'date' => ''];
                $timestamp = strtotime($meta['date']);
                $auditConflictGroups[] = [
                    'auditor' => $meta['auditor'],
                    'date' => $timestamp !== false ? date('d.m.Y', $timestamp) : $meta['date'],
                    'sort_date' => $meta['date'],
                    'rows' => $groupRows,
                ];
            }
        }
    }
    usort($auditConflictGroups, function ($a, $b) {
        return strcmp($b['sort_date'], $a['sort_date']);
    });

    $memberConflictGroups = [];
    foreach ($allMarketingInfo as $marketingDetail) {
        $serviceId = $marketingDetail['service_id'];
        $subtypeId = null;
        if ($marketingDetail["service_type"] == "CA" && isset($caMap[$serviceId]))
            $subtypeId = $caMap[$serviceId]['ca_id'];
        if ($marketingDetail["service_type"] == "SA-I" && isset($saiMap[$serviceId]))
            $subtypeId = $saiMap[$serviceId]['sai_id'];
        if ($marketingDetail["service_type"] == "SA-II" && isset($saiiMap[$serviceId]))
            $subtypeId = $saiiMap[$serviceId]['saii_id'];
        if ($marketingDetail["service_type"] == "RC" && isset($rcMap[$serviceId]))
            $subtypeId = $rcMap[$serviceId]['rc_id'];
        if ($marketingDetail["service_type"] == "SP" && isset($spMap[$serviceId]))
            $subtypeId = $spMap[$serviceId]['sp_id'];

        if ($subtypeId && isset($cdcMap[$subtypeId])) {
            $cdc = $cdcMap[$subtypeId];

            // Extract individuals
            $leadAuditor = strtolower(trim((string) $cdc['lead_auditor']));
            $auditorsStr = strtolower(trim((string) $cdc['auditors']));
            $reviewer1 = strtolower(trim((string) $cdc['reviewer_1']));
            $reviewer2 = strtolower(trim((string) $cdc['reviewer_2']));
            $grantedBy = strtolower(trim((string) $cdc['granted_by']));

            $allAuditors = [];
            if ($leadAuditor !== '')
                $allAuditors[] = $leadAuditor;
            foreach (auditPageSplitAuditors($auditorsStr) as $aud) {
                if ($aud !== '')
                    $allAuditors[] = $aud;
            }

            $cdcMembers = [];
            if ($reviewer1 !== '')
                $cdcMembers[] = $reviewer1;
            if ($reviewer2 !== '')
                $cdcMembers[] = $reviewer2;
            if ($grantedBy !== '')
                $cdcMembers[] = $grantedBy;

            $conflictReasons = [];

            // 1. Auditor vs Reviewer / CDC Member
            foreach ($allAuditors as $aud) {
                if (in_array($aud, $cdcMembers)) {
                    $conflictReasons[] = "Auditor ($aud) is also a CDC Member/Reviewer";
                }
            }

            // 3. CDC Member Duplicates
            if (count($cdcMembers) > count(array_unique($cdcMembers))) {
                $conflictReasons[] = "Duplicate CDC Members (Reviewer 1, Reviewer 2, Granted By)";
            }

            if (!empty($conflictReasons)) {
                $memberConflictGroups[] = [
                    'customer_name' => $marketingDetail['customer_name'],
                    'unique_id' => $marketingDetail['unique_id'],
                    'service_type' => $marketingDetail['service_type'],
                    'reasons' => array_unique($conflictReasons)
                ];
            }
        }
    }

    $totalAuditsCount = count($auditRowDetails);
    $totalAuditorsCount = count($auditorsList);
    $dateConflictCount = count($auditConflictGroups);
    $memberConflictCount = count($memberConflictGroups);
    ?>

    <style>
        /* =====================================================================
           AUDIT RECORDS — MODERN CLEAN SAAS DESIGN SYSTEM (masterCreator Theme)
           ===================================================================== */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body, #wrapper {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        .audit-page-container {
            max-width: 1540px;
            margin: 0 auto;
            padding: 14px 20px 60px 20px;
        }

        /* =====================================================================
           COMPACT HERO HEADER
           ===================================================================== */
        .ap-hero-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #eff6ff 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 18px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            position: relative;
            overflow: hidden;
        }

        .ap-hero-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0d4d8b 0%, #2563eb 50%, #38bdf8 100%);
        }

        .ap-hero-title {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
        }

        .ap-hero-stats {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .ap-stat-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 12px;
            display: flex;
            align-items: center;
            gap: 9px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .ap-stat-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.04);
        }

        .ap-stat-pill.pill-danger {
            background: #fff5f5;
            border-color: #fecaca;
        }

        .ap-stat-pill.pill-warning {
            background: #fffaf5;
            border-color: #fed7aa;
        }

        .ap-stat-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .ap-stat-icon.icon-blue { background: #eff6ff; color: #2563eb; }
        .ap-stat-icon.icon-purple { background: #f5f3ff; color: #7c3aed; }
        .ap-stat-icon.icon-red { background: #fef2f2; color: #dc2626; }
        .ap-stat-icon.icon-orange { background: #fff7ed; color: #ea580c; }
        .ap-stat-icon.icon-green { background: #ecfdf5; color: #059669; }

        .ap-stat-num {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .ap-stat-lbl {
            font-size: 9.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            line-height: 1;
            margin-top: 2px;
        }

        /* =====================================================================
           FILTER TOOLBAR (masterCreator Theme)
           ===================================================================== */
        .ap-filter-panel {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin: 0 0 16px 0;
            padding: 12px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
        }

        .ap-filter-item {
            position: relative;
        }

        .ap-filter-item.ap-filter-search {
            flex: 1 1 260px;
            min-width: 220px;
        }

        .ap-filter-item.ap-filter-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 13px;
            pointer-events: none;
        }

        .ap-filter-item.ap-filter-search input {
            padding-left: 34px !important;
        }

        .ap-filter-item.ap-filter-select {
            flex: 0 1 190px;
            min-width: 160px;
        }

        .ap-filter-item.ap-filter-date {
            flex: 0 1 135px;
            min-width: 115px;
        }

        .ap-filter-item.ap-filter-btn {
            flex: 0 0 auto;
        }

        .ap-filter-item input,
        .ap-filter-item select {
            width: 100% !important;
            height: 38px !important;
            padding: 6px 12px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-sizing: border-box !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            background-color: #f8fafc !important;
            color: #0f172a !important;
            transition: all 0.15s ease !important;
        }

        .ap-filter-item select {
            padding-right: 32px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 10px center !important;
            background-size: 11px !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            cursor: pointer !important;
        }

        .ap-filter-item input::placeholder {
            color: #94a3b8 !important;
        }

        .ap-filter-item input:focus,
        .ap-filter-item select:focus {
            border-color: #0d4d8b !important;
            background-color: #ffffff !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12) !important;
        }

        #auditClientSearch:-webkit-autofill,
        #auditClientSearch:-webkit-autofill:hover, 
        #auditClientSearch:-webkit-autofill:focus,
        #auditClientSearch:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #f8fafc inset !important;
            -webkit-text-fill-color: #0f172a !important;
            transition: background-color 5000s ease-in-out 0s !important;
        }

        .ap-btn-reset {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 38px;
            padding: 0 14px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .ap-btn-reset:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }

        /* =====================================================================
           MAIN CARD & TABLE (masterCreator Theme)
           ===================================================================== */
        .ap-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .ap-card-header {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            padding: 12px 20px !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(13, 77, 139, 0.15);
        }

        .ap-card-title {
            font-size: 13.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff !important;
        }

        .ap-card-header-stats {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .ap-count-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            color: #ffffff;
        }

        .ap-conflict-badge {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800;
            color: #b91c1c;
        }

        .ap-card-body {
            padding: 0;
        }

        .ap-table {
            width: 100% !important;
            margin-bottom: 0 !important;
            border-collapse: collapse !important;
        }

        .ap-table thead th {
            background: #f8fafc !important;
            border: none !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 12px 16px !important;
            font-size: 11.5px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #475569 !important;
            vertical-align: middle !important;
        }

        .ap-table tbody td {
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 11px 16px !important;
            font-size: 13px !important;
            color: #334155 !important;
            vertical-align: middle !important;
            transition: background 0.15s ease !important;
        }

        .ap-table tbody tr:last-child td {
            border-bottom: none !important;
        }

        .ap-table tbody tr:nth-of-type(even) td {
            background-color: #fbfcfd !important;
        }

        .ap-table tbody tr:hover td {
            background: #f1f5f9 !important;
        }

        .ap-table tbody tr {
            cursor: pointer;
        }

        /* Critical overrides: Prevent css/app.css (.table-striped span { display:none }) from hiding spans */
        #tab_customer_list td span,
        #tab_customer_list th span,
        .ap-table td span,
        .ap-table th span {
            display: inline-block !important;
        }

        #tab_customer_list td .ap-badge,
        .ap-table td .ap-badge {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        #tab_customer_list td .ap-date-badge,
        .ap-table td .ap-date-badge {
            display: inline-flex !important;
            align-items: center !important;
        }

        #tab_customer_list td .ap-uid-badge,
        .ap-table td .ap-uid-badge {
            display: inline-block !important;
        }

        #tab_customer_list td .ap-status-tag,
        .ap-table td .ap-status-tag {
            display: inline-block !important;
        }

        #tab_customer_list td .ap-audit-status,
        .ap-table td .ap-audit-status {
            display: inline !important;
        }

        #tab_customer_list td .ap-next-service,
        .ap-table td .ap-next-service {
            display: inline !important;
        }

        /* Sorting helper token is strictly hidden */
        #tab_customer_list td span.ap-sort-prefix,
        #tab_customer_list td span[style*="display:none"],
        #tab_customer_list td span[style*="display: none"],
        .ap-table td span.ap-sort-prefix {
            display: none !important;
        }

        /* Service Type Badges */
        .ap-badge {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 3px 8px !important;
            border-radius: 6px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: 0.3px !important;
            line-height: 1.2 !important;
            text-transform: uppercase !important;
        }
        .ap-badge-st1 { background: #eff6ff !important; color: #1d4ed8 !important; border: 1px solid #bfdbfe !important; }
        .ap-badge-st2 { background: #e0e7ff !important; color: #4338ca !important; border: 1px solid #c7d2fe !important; }
        .ap-badge-sai { background: #ecfdf5 !important; color: #047857 !important; border: 1px solid #a7f3d0 !important; }
        .ap-badge-saii { background: #f0fdfa !important; color: #0f766e !important; border: 1px solid #99f6e4 !important; }
        .ap-badge-rc { background: #faf5ff !important; color: #7e22ce !important; border: 1px solid #e9d5ff !important; }
        .ap-badge-sp { background: #fffbeb !important; color: #b45309 !important; border: 1px solid #fde68a !important; }
        .ap-badge-ta { background: #ecfeff !important; color: #0e7490 !important; border: 1px solid #a5f3fc !important; }
        .ap-badge-default { background: #f1f5f9 !important; color: #475569 !important; border: 1px solid #cbd5e1 !important; }

        .ap-uid-badge {
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #0d4d8b !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
            background: #eff6ff !important;
            padding: 2px 7px !important;
            border-radius: 4px !important;
            border: 1px solid #dbeafe !important;
            display: inline-block !important;
        }

        .ap-client-name {
            font-weight: 700 !important;
            color: #0f172a !important;
            font-size: 13px !important;
            line-height: 1.3 !important;
        }

        .ap-status-tag {
            display: inline-block !important;
            padding: 2px 8px !important;
            border-radius: 4px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            background: #f8fafc !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0 !important;
        }

        .ap-auditor {
            font-size: 12.5px !important;
            font-weight: 600 !important;
            color: #334155 !important;
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        .ap-date-badge {
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #334155 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            padding: 2px 7px !important;
            border-radius: 5px !important;
            white-space: nowrap !important;
        }

        .ap-present-status {
            font-size: 12px !important;
            line-height: 1.3 !important;
        }

        .ap-audit-status {
            font-weight: 600 !important;
            color: #1e293b !important;
            display: inline !important;
        }

        .ap-next-service {
            font-size: 11px !important;
            color: #64748b !important;
            margin-left: 2px !important;
            display: inline !important;
        }

        /* Conflict row styling */
        #tab_customer_list tbody tr.audit-conflict-row td {
            background-color: #fff1f2 !important;
            color: #9f1239 !important;
        }

        #tab_customer_list tbody tr.audit-conflict-row:hover td {
            background-color: #ffe4e6 !important;
        }

        #tab_customer_list tbody tr.audit-conflict-row td:first-child {
            border-left: 3px solid #e11d48 !important;
        }

        /* =====================================================================
           CONFLICT SLIDE PANELS & TABS
           ===================================================================== */
        .audit-conflict-tab {
            position: fixed;
            right: 0;
            top: 210px;
            z-index: 9998;
            border: 0;
            border-radius: 8px 0 0 8px;
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
            padding: 12px 14px;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            box-shadow: -2px 4px 16px rgba(185, 28, 28, 0.35);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .audit-conflict-tab:hover {
            transform: translateX(-3px);
            box-shadow: -4px 6px 20px rgba(185, 28, 28, 0.45);
        }

        .member-conflict-tab {
            position: fixed;
            right: 0;
            top: 360px;
            z-index: 9998;
            border: 0;
            border-radius: 8px 0 0 8px;
            background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%);
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
            padding: 12px 14px;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            box-shadow: -2px 4px 16px rgba(234, 88, 12, 0.35);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .member-conflict-tab:hover {
            transform: translateX(-3px);
            box-shadow: -4px 6px 20px rgba(234, 88, 12, 0.45);
        }

        .audit-conflict-count {
            display: inline-block;
            min-width: 20px;
            height: 20px;
            margin-top: 8px;
            border-radius: 999px;
            background: #ffffff;
            color: #b91c1c;
            font-weight: 800;
            font-size: 11px;
            line-height: 20px;
            text-align: center;
            writing-mode: horizontal-tb;
            box-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        .member-conflict-tab .audit-conflict-count {
            color: #ea580c;
        }

        .audit-conflict-panel,
        .member-conflict-panel {
            position: fixed;
            top: 0;
            right: -450px;
            z-index: 9999;
            width: 440px;
            max-width: calc(100vw - 24px);
            height: 100vh;
            background: #ffffff;
            box-shadow: -10px 0 30px rgba(15, 23, 42, 0.15);
            transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .audit-conflict-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .member-conflict-panel .audit-conflict-panel-header {
            background: linear-gradient(135deg, #9a3412 0%, #ea580c 100%) !important;
        }

        .audit-conflict-panel-title {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .audit-conflict-close {
            border: 0;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-size: 20px;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            cursor: pointer;
            transition: background 0.15s;
        }

        .audit-conflict-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .audit-conflict-panel-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            background: #f8fafc;
        }

        .audit-conflict-panel-body::-webkit-scrollbar {
            width: 6px;
        }
        .audit-conflict-panel-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .audit-conflict-empty {
            padding: 16px;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            background: #f0fdf4;
            color: #166534;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .audit-conflict-card {
            border: 1px solid #fecaca;
            border-left: 4px solid #b91c1c;
            border-radius: 8px;
            margin-bottom: 12px;
            background: #ffffff;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .audit-conflict-card-head {
            padding: 10px 14px;
            background: #fff5f5;
            border-bottom: 1px solid #fee2e2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .audit-conflict-card-title {
            color: #7f1d1d;
            font-size: 13px;
            font-weight: 800;
        }

        .audit-conflict-card-meta {
            color: #991b1b;
            font-size: 11px;
            font-weight: 700;
            background: #fee2e2;
            padding: 2px 8px;
            border-radius: 999px;
        }

        .audit-conflict-item {
            padding: 10px 14px;
            border-top: 1px solid #f1f5f9;
        }

        .audit-conflict-item:first-child {
            border-top: 0;
        }

        .audit-conflict-client {
            color: #0f172a;
            font-size: 13px;
            font-weight: 700;
        }

        .audit-conflict-detail {
            color: #64748b;
            font-size: 12px;
            margin-top: 3px;
        }

        /* ===== Modern DataTable Footer (Showing entries + Pagination) ===== */
        .dt-bottom-row {
            margin: 0 !important;
            padding: 12px 18px !important;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 10px;
        }
        .dataTables_wrapper .dataTables_info {
            padding: 0 !important;
            margin: 0 !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #475569 !important;
            line-height: 32px;
            display: block !important;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding: 0 !important;
            margin: 0 !important;
            text-align: right !important;
        }
        .dataTables_wrapper .dataTables_paginate ul.pagination {
            margin: 0 !important;
            white-space: nowrap;
        }
        .dataTables_wrapper .dataTables_paginate .pagination > li > a,
        .dataTables_wrapper .dataTables_paginate .pagination > li > span {
            color: #0d4d8b;
            border-color: #e2e8f0;
            font-size: 12.5px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            margin: 0 2px;
            transition: all 0.15s ease;
        }
        .dataTables_wrapper .dataTables_paginate .pagination > li > a:hover {
            background-color: #eff6ff;
            border-color: #bfdbfe;
            color: #2563eb;
        }
        .dataTables_wrapper .dataTables_paginate .pagination > .active > a,
        .dataTables_wrapper .dataTables_paginate .pagination > .active > span {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            border-color: #0d4d8b !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(13, 77, 139, 0.25);
        }
        .dataTables_wrapper .dataTables_paginate .pagination > .disabled > a,
        .dataTables_wrapper .dataTables_paginate .pagination > .disabled > span {
            color: #94a3b8 !important;
            border-color: #eef2f6 !important;
            background-color: #f8fafc !important;
        }

        /* jQuery UI Datepicker Modernization */
        .ui-datepicker {
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.1) !important;
            padding: 10px !important;
            font-family: 'Inter', sans-serif !important;
            z-index: 9999 !important;
        }
        .ui-datepicker .ui-datepicker-header {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 6px !important;
            font-weight: 700 !important;
            padding: 6px 0 !important;
        }
        .ui-datepicker .ui-datepicker-title select {
            color: #0f172a !important;
            border-radius: 4px !important;
            padding: 2px 4px !important;
            font-weight: 600 !important;
            font-size: 12px !important;
            margin: 0 2px !important;
        }
        .ui-datepicker th {
            font-size: 11px !important;
            font-weight: 700 !important;
            color: #64748b !important;
            text-transform: uppercase !important;
            padding: 6px 2px !important;
        }
        .ui-datepicker td span,
        .ui-datepicker td a {
            text-align: center !important;
            padding: 6px 4px !important;
            border-radius: 6px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #1e293b !important;
        }
        .ui-datepicker td a:hover {
            background: #eff6ff !important;
            color: #2563eb !important;
            border-color: #bfdbfe !important;
        }
        .ui-datepicker td .ui-state-active {
            background: #0d4d8b !important;
            color: #ffffff !important;
            border-color: #0d4d8b !important;
        }

        /* =====================================================================
           DARK MODE SUPPORT
           ===================================================================== */
        html.theme-dark {
            --hpd-bg: #0a0e14;
            --hpd-surface: #111722;
            --hpd-surface-2: #161d2b;
            --hpd-border: #232c3d;
            --hpd-border-2: #2e3a4f;
            --hpd-text: #dbe3f0;
            --hpd-text-dim: #93a1b8;
            --hpd-text-faint: #6b7890;
            --hpd-accent: #4a9eff;
        }

        html.theme-dark body, 
        html.theme-dark #page-content-wrapper,
        html.theme-dark .page-content.inset {
            background: var(--hpd-bg);
            color: var(--hpd-text);
        }

        html.theme-dark .ap-hero-header {
            background: linear-gradient(135deg, #111722 0%, #0d131e 100%);
            border-color: var(--hpd-border);
        }
        html.theme-dark .ap-hero-title { color: #f8fafc; }
        html.theme-dark .ap-stat-pill {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
        }
        html.theme-dark .ap-stat-pill.pill-danger {
            background: #2a1014;
            border-color: #7f1d1d;
        }
        html.theme-dark .ap-stat-pill.pill-warning {
            background: #2c160c;
            border-color: #9a3412;
        }
        html.theme-dark .ap-stat-num { color: #f8fafc; }
        html.theme-dark .ap-stat-lbl { color: var(--hpd-text-dim); }

        html.theme-dark .ap-filter-panel {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
        }
        html.theme-dark .ap-filter-item input,
        html.theme-dark .ap-filter-item select {
            background-color: var(--hpd-surface-2) !important;
            border-color: var(--hpd-border-2) !important;
            color: var(--hpd-text) !important;
        }
        html.theme-dark .ap-filter-item input:focus,
        html.theme-dark .ap-filter-item select:focus {
            border-color: var(--hpd-accent) !important;
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.18) !important;
        }
        html.theme-dark .ap-btn-reset {
            background: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark .ap-btn-reset:hover {
            background: var(--hpd-border-2);
            color: #ffffff;
        }

        html.theme-dark .ap-card {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
        }
        html.theme-dark .ap-card-header {
            background: linear-gradient(135deg, #16243d 0%, #0d1b33 100%) !important;
            border-bottom: 1px solid var(--hpd-border-2);
        }
        html.theme-dark .ap-table thead th {
            background: var(--hpd-surface-2) !important;
            border-bottom-color: var(--hpd-border-2) !important;
            color: var(--hpd-text-dim) !important;
        }
        html.theme-dark .ap-table tbody td {
            background: var(--hpd-surface) !important;
            border-bottom-color: var(--hpd-border) !important;
            color: var(--hpd-text) !important;
        }
        html.theme-dark .ap-table tbody tr:hover td {
            background: var(--hpd-surface-2) !important;
        }

        html.theme-dark #tab_customer_list tbody tr.audit-conflict-row td {
            background-color: #3b1118 !important;
            color: #fca5a5 !important;
        }
        html.theme-dark #tab_customer_list tbody tr.audit-conflict-row:hover td {
            background-color: #4c1722 !important;
        }

        html.theme-dark .ap-uid-badge {
            background: rgba(37, 99, 235, 0.2);
            border-color: rgba(37, 99, 235, 0.4);
            color: #93c5fd;
        }
        html.theme-dark .ap-client-name { color: #f8fafc; }
        html.theme-dark .ap-status-tag {
            background: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text-dim);
        }
        html.theme-dark .ap-auditor { color: var(--hpd-text); }
        html.theme-dark .ap-date-badge {
            background: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark .ap-audit-status { color: #f8fafc; }
        html.theme-dark .ap-next-service { color: var(--hpd-text-dim); }

        html.theme-dark .audit-conflict-panel,
        html.theme-dark .member-conflict-panel {
            background: var(--hpd-surface);
        }
        html.theme-dark .audit-conflict-panel-body {
            background: var(--hpd-bg);
        }
        html.theme-dark .audit-conflict-card {
            background: var(--hpd-surface);
            border-color: #7f1d1d;
        }
        html.theme-dark .audit-conflict-card-head {
            background: #2a1014;
            border-bottom-color: #7f1d1d;
        }
        html.theme-dark .audit-conflict-card-title { color: #fca5a5; }
        html.theme-dark .audit-conflict-card-meta {
            background: #3b1118;
            color: #fca5a5;
        }
        html.theme-dark .audit-conflict-item {
            border-top-color: var(--hpd-border);
        }
        html.theme-dark .audit-conflict-client { color: #f8fafc; }
        html.theme-dark .audit-conflict-detail { color: var(--hpd-text-dim); }

        html.theme-dark .dt-bottom-row {
            border-top-color: var(--hpd-border) !important;
            background: var(--hpd-surface) !important;
        }
        html.theme-dark .dataTables_wrapper .dataTables_info {
            color: var(--hpd-text-dim) !important;
        }
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > li > a,
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > li > span {
            background-color: var(--hpd-surface-2) !important;
            border-color: var(--hpd-border-2) !important;
            color: var(--hpd-text) !important;
        }
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > .active > a,
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > .active > span {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
        }
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > .disabled > a,
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > .disabled > span {
            background-color: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
            color: var(--hpd-text-faint) !important;
        }

        html.theme-dark .ui-datepicker {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
            color: var(--hpd-text) !important;
        }
        html.theme-dark .ui-datepicker td span,
        html.theme-dark .ui-datepicker td a {
            color: var(--hpd-text) !important;
        }
        html.theme-dark .ui-datepicker td a:hover {
            background: var(--hpd-surface-2) !important;
            color: var(--hpd-accent) !important;
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid audit-page-container">

                <!-- Compact Hero Header (masterCreator Theme) -->
                <div class="ap-hero-header">
                    <div class="ap-hero-left">
                        <h1 class="ap-hero-title">
                            <i class="fa fa-calendar-check-o" style="color: #0d4d8b; font-size: 17px; margin-right: 8px;"></i> Audit Records
                        </h1>
                    </div>
                    <div class="ap-hero-stats">
                        <div class="ap-stat-pill">
                            <span class="ap-stat-icon icon-blue"><i class="fa fa-calendar"></i></span>
                            <div>
                                <div class="ap-stat-num"><?php echo $totalAuditsCount; ?></div>
                                <div class="ap-stat-lbl">Total Audits</div>
                            </div>
                        </div>
                        <div class="ap-stat-pill">
                            <span class="ap-stat-icon icon-purple"><i class="fa fa-user-secret"></i></span>
                            <div>
                                <div class="ap-stat-num"><?php echo $totalAuditorsCount; ?></div>
                                <div class="ap-stat-lbl">Auditors</div>
                            </div>
                        </div>
                        <?php if ($auditDateConflictVisible) { ?>
                        <div class="ap-stat-pill <?php echo $dateConflictCount > 0 ? 'pill-danger' : ''; ?>" id="heroDateConflictPill" style="cursor: pointer;" title="Click to view Date Conflicts">
                            <span class="ap-stat-icon icon-red"><i class="fa fa-calendar-times-o"></i></span>
                            <div>
                                <div class="ap-stat-num" style="<?php echo $dateConflictCount > 0 ? 'color: #dc2626;' : ''; ?>"><?php echo $dateConflictCount; ?></div>
                                <div class="ap-stat-lbl" style="<?php echo $dateConflictCount > 0 ? 'color: #dc2626;' : ''; ?>">Date Conflicts</div>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($auditMemberConflictVisible) { ?>
                        <div class="ap-stat-pill <?php echo $memberConflictCount > 0 ? 'pill-warning' : ''; ?>" id="heroMemberConflictPill" style="cursor: pointer;" title="Click to view Member Conflicts">
                            <span class="ap-stat-icon icon-orange"><i class="fa fa-users"></i></span>
                            <div>
                                <div class="ap-stat-num" style="<?php echo $memberConflictCount > 0 ? 'color: #ea580c;' : ''; ?>"><?php echo $memberConflictCount; ?></div>
                                <div class="ap-stat-lbl" style="<?php echo $memberConflictCount > 0 ? 'color: #ea580c;' : ''; ?>">Member Conflicts</div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Decoy inputs to capture browser credential autofill -->
                <div style="position: absolute; left: -9999px; top: -9999px; width: 0; height: 0; overflow: hidden;" aria-hidden="true" tabindex="-1">
                    <input type="text" name="fake_username_prevent_autofill" tabindex="-1" autocomplete="username">
                    <input type="password" name="fake_password_prevent_autofill" tabindex="-1" autocomplete="current-password">
                </div>

                <!-- Modern SaaS Filter Toolbar -->
                <div class="ap-filter-panel">
                    <div class="ap-filter-item ap-filter-search">
                        <i class="fa fa-search"></i>
                        <input type="search" class="form-control auditClientSearch" id="auditClientSearch"
                            name="search_client_filter_no_autofill"
                            placeholder="Search by client name or unique ID..."
                            autocomplete="new-password"
                            autocorrect="off"
                            autocapitalize="off"
                            spellcheck="false"
                            data-lpignore="true"
                            data-1p-ignore="true"
                            data-form-type="other"
                            value=""
                            readonly
                            onfocus="this.removeAttribute('readonly');"
                            onmouseover="this.removeAttribute('readonly');">
                    </div>
                    <div class="ap-filter-item ap-filter-select">
                        <select name="service-type" class="form-control service-type" id="service-type">
                            <option value="">All Service Types</option>
                            <option value="ST-1">ST-1</option>
                            <option value="ST-2">ST-2</option>
                            <?php foreach ($servicesTypes as $service) { ?>
                                <?php if ($service['service_type'] === 'CA') {
                                    continue;
                                } ?>
                                <option value="<?php echo htmlspecialchars($service['service_type'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($service['service_type'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="ap-filter-item ap-filter-select">
                        <select name="auditors" class="form-control auditor-filter" id="auditor-filter">
                            <option value="">All Auditors</option>
                            <?php foreach ($auditorsList as $auditor) { ?>
                                <option value="<?php echo htmlspecialchars($auditor['auditor_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($auditor['auditor_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="ap-filter-item ap-filter-date">
                        <input type="text" class="filterFromDate form-control" id="filterFromDate" name="filterFromDate"
                            placeholder="Date From" value="" autocomplete="off">
                    </div>
                    <div class="ap-filter-item ap-filter-date">
                        <input type="text" class="filterToDate form-control" id="filterToDate" name="filterToDate"
                            placeholder="Date To" value="" autocomplete="off">
                    </div>
                    <div class="ap-filter-item ap-filter-btn">
                        <button type="button" class="ap-btn-reset" id="btnResetAuditFilters" title="Reset all filters">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </div>
                </div>

                <!-- Main Data Card -->
                <div class="ap-card">
                    <div class="ap-card-header">
                        <h3 class="ap-card-title">
                            <i class="fa fa-list-ul"></i> Audit Records List
                        </h3>
                        <div class="ap-card-header-stats">
                            <span class="ap-count-badge">
                                <i class="fa fa-database"></i> <?php echo $totalAuditsCount; ?> Records
                            </span>
                            <?php if ($dateConflictCount > 0) { ?>
                                <span class="ap-conflict-badge">
                                    <i class="fa fa-exclamation-triangle"></i> <?php echo $dateConflictCount; ?> Date Conflicts
                                </span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="ap-card-body table-responsive">
                        <table class="table ap-table table-hover" id="tab_customer_list" width="100%">
                            <thead>
                                <tr>
                                    <th>Unique ID</th>
                                    <th>Client Name</th>
                                    <th>Service Type</th>
                                    <th>Service Status</th>
                                    <th>Auditor(s)</th>
                                    <th>Audit Date(s)</th>
                                    <th>Present Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($allMarketingInfo as $marketingDetail) {
                                    $auditrDetails = "";
                                    $auditDateDetails = "";
                                    $uniqueIdVal = trim((string)($marketingDetail['unique_id'] ?? ''));
                                    $clientNameVal = trim((string)($marketingDetail['customer_name'] ?? ''));
                                    $serviceStatusVal = trim((string)($marketingDetail['service_status'] ?? ''));
                                    $presentStatusVal = trim((string)($marketingDetail['audit_status'] ?? ''));
                                    $nextServiceVal = trim((string)($marketingDetail['next_service'] ?? ''));

                                    if ($marketingDetail["service_type"] == "CA") {
                                        $auditDetails = isset($caMap[$marketingDetail['service_id']]) ? $caMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) {
                                            continue;
                                        }
                                        $auditdates1 = explode(',', $auditDetails["audit_date"]);
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'ST-1');
                                        $st1AuditorVal = trim((string)($auditDetails["stage1_auditor"] ?? ''));
                                        $st1DateVal = trim((string)($auditDetails["audit_date"] ?? ''));
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>'
                                            class="<?php echo $auditConflictClass; ?>"
                                            title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-uid-badge"><?php echo $uniqueIdVal !== '' ? htmlspecialchars($uniqueIdVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-client-name"><?php echo $clientNameVal !== '' ? htmlspecialchars($clientNameVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-badge ap-badge-st1">ST-1</span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-status-tag"><?php echo $serviceStatusVal !== '' ? htmlspecialchars($serviceStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-auditor"><i class="fa fa-user-circle-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $st1AuditorVal !== '' ? htmlspecialchars($st1AuditorVal, ENT_QUOTES, 'UTF-8') : '<span style="color: #94a3b8; font-style: italic; font-weight: normal;">Not Assigned</span>'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-sort-prefix" style="display:none !important;"><?php echo htmlspecialchars($auditdates1[0] ?? '', ENT_QUOTES, 'UTF-8'); ?>~</span>
                                                <span class="ap-date-badge"><i class="fa fa-calendar-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $st1DateVal !== '' ? htmlspecialchars($st1DateVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-present-status">
                                                    <span class="ap-audit-status"><?php echo $presentStatusVal !== '' ? htmlspecialchars($presentStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                                    <?php if ($nextServiceVal !== '') { ?>
                                                        <span class="ap-next-service">(<?php echo htmlspecialchars($nextServiceVal, ENT_QUOTES, 'UTF-8'); ?>)</span>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php if ($auditDetails["stage2_audit"] != "") {
                                            $auditdatest2 = explode(',', $auditDetails["stage2_audit"]);
                                            $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'ST-2');
                                            $st2AuditorVal = trim((string)($auditDetails["stage2_auditor"] ?? ''));
                                            $st2DateVal = trim((string)($auditDetails["stage2_audit"] ?? ''));
                                            ?>
                                            <tr id='addr<?php echo $marketingDetail['id'] ?>'
                                                class="<?php echo $auditConflictClass; ?>"
                                                title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                    <span class="ap-uid-badge"><?php echo $uniqueIdVal !== '' ? htmlspecialchars($uniqueIdVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                    <div class="ap-client-name"><?php echo $clientNameVal !== '' ? htmlspecialchars($clientNameVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></div>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                    <span class="ap-badge ap-badge-st2">ST-2</span>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                    <span class="ap-status-tag"><?php echo $serviceStatusVal !== '' ? htmlspecialchars($serviceStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                    <div class="ap-auditor"><i class="fa fa-user-circle-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $st2AuditorVal !== '' ? htmlspecialchars($st2AuditorVal, ENT_QUOTES, 'UTF-8') : '<span style="color: #94a3b8; font-style: italic; font-weight: normal;">Not Assigned</span>'; ?></div>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                    <span class="ap-sort-prefix" style="display:none !important;"><?php echo htmlspecialchars($auditdatest2[0] ?? '', ENT_QUOTES, 'UTF-8'); ?>~</span>
                                                    <span class="ap-date-badge"><i class="fa fa-calendar-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $st2DateVal !== '' ? htmlspecialchars($st2DateVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                    <div class="ap-present-status">
                                                        <span class="ap-audit-status"><?php echo $presentStatusVal !== '' ? htmlspecialchars($presentStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                                        <?php if ($nextServiceVal !== '') { ?>
                                                            <span class="ap-next-service">(<?php echo htmlspecialchars($nextServiceVal, ENT_QUOTES, 'UTF-8'); ?>)</span>
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else if ($marketingDetail["service_type"] == "SA-I") {
                                        $auditDetails = isset($saiMap[$marketingDetail['service_id']]) ? $saiMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) {
                                            continue;
                                        }
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'SA-I');
                                        $saiAuditorVal = trim((string)($auditDetails["auditors"] ?? ''));
                                        $saiDateVal = trim((string)($auditDetails["audit_date"] ?? ''));
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>'
                                            class="<?php echo $auditConflictClass; ?>"
                                            title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-uid-badge"><?php echo $uniqueIdVal !== '' ? htmlspecialchars($uniqueIdVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-client-name"><?php echo $clientNameVal !== '' ? htmlspecialchars($clientNameVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-badge ap-badge-sai">SA-I</span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-status-tag"><?php echo $serviceStatusVal !== '' ? htmlspecialchars($serviceStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-auditor"><i class="fa fa-user-circle-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $saiAuditorVal !== '' ? htmlspecialchars($saiAuditorVal, ENT_QUOTES, 'UTF-8') : '<span style="color: #94a3b8; font-style: italic; font-weight: normal;">Not Assigned</span>'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-sort-prefix" style="display:none !important;"><?php echo htmlspecialchars(simpleDateConversion($marketingDetail["audit_date"]), ENT_QUOTES, 'UTF-8'); ?>~</span>
                                                <span class="ap-date-badge"><i class="fa fa-calendar-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $saiDateVal !== '' ? htmlspecialchars($saiDateVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-present-status">
                                                    <span class="ap-audit-status"><?php echo $presentStatusVal !== '' ? htmlspecialchars($presentStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                                    <?php if ($nextServiceVal !== '') { ?>
                                                        <span class="ap-next-service">(<?php echo htmlspecialchars($nextServiceVal, ENT_QUOTES, 'UTF-8'); ?>)</span>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    } else if ($marketingDetail["service_type"] == "SA-II") {
                                        $auditDetails = isset($saiiMap[$marketingDetail['service_id']]) ? $saiiMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) {
                                            continue;
                                        }
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'SA-II');
                                        $saiiAuditorVal = trim((string)($auditDetails["auditors"] ?? ''));
                                        $saiiDateVal = trim((string)($auditDetails["audit_date"] ?? ''));
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>'
                                            class="<?php echo $auditConflictClass; ?>"
                                            title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-uid-badge"><?php echo $uniqueIdVal !== '' ? htmlspecialchars($uniqueIdVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-client-name"><?php echo $clientNameVal !== '' ? htmlspecialchars($clientNameVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-badge ap-badge-saii">SA-II</span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-status-tag"><?php echo $serviceStatusVal !== '' ? htmlspecialchars($serviceStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-auditor"><i class="fa fa-user-circle-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $saiiAuditorVal !== '' ? htmlspecialchars($saiiAuditorVal, ENT_QUOTES, 'UTF-8') : '<span style="color: #94a3b8; font-style: italic; font-weight: normal;">Not Assigned</span>'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-sort-prefix" style="display:none !important;"><?php echo htmlspecialchars(simpleDateConversion($marketingDetail["audit_date"]), ENT_QUOTES, 'UTF-8'); ?>~</span>
                                                <span class="ap-date-badge"><i class="fa fa-calendar-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $saiiDateVal !== '' ? htmlspecialchars($saiiDateVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-present-status">
                                                    <span class="ap-audit-status"><?php echo $presentStatusVal !== '' ? htmlspecialchars($presentStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                                    <?php if ($nextServiceVal !== '') { ?>
                                                        <span class="ap-next-service">(<?php echo htmlspecialchars($nextServiceVal, ENT_QUOTES, 'UTF-8'); ?>)</span>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    } else if ($marketingDetail["service_type"] == "RC") {
                                        $auditDetails = isset($rcMap[$marketingDetail['service_id']]) ? $rcMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) {
                                            continue;
                                        }
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'RC');
                                        $rcAuditorVal = trim((string)($auditDetails["auditors"] ?? ''));
                                        $rcDateVal = trim((string)($auditDetails["audit_date"] ?? ''));
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>'
                                            class="<?php echo $auditConflictClass; ?>"
                                            title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-uid-badge"><?php echo $uniqueIdVal !== '' ? htmlspecialchars($uniqueIdVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-client-name"><?php echo $clientNameVal !== '' ? htmlspecialchars($clientNameVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-badge ap-badge-rc">RC</span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-status-tag"><?php echo $serviceStatusVal !== '' ? htmlspecialchars($serviceStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-auditor"><i class="fa fa-user-circle-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $rcAuditorVal !== '' ? htmlspecialchars($rcAuditorVal, ENT_QUOTES, 'UTF-8') : '<span style="color: #94a3b8; font-style: italic; font-weight: normal;">Not Assigned</span>'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-sort-prefix" style="display:none !important;"><?php echo htmlspecialchars(simpleDateConversion($marketingDetail["audit_date"]), ENT_QUOTES, 'UTF-8'); ?>~</span>
                                                <span class="ap-date-badge"><i class="fa fa-calendar-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $rcDateVal !== '' ? htmlspecialchars($rcDateVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-present-status">
                                                    <span class="ap-audit-status"><?php echo $presentStatusVal !== '' ? htmlspecialchars($presentStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                                    <?php if ($nextServiceVal !== '') { ?>
                                                        <span class="ap-next-service">(<?php echo htmlspecialchars($nextServiceVal, ENT_QUOTES, 'UTF-8'); ?>)</span>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    } else if ($marketingDetail["service_type"] == "SP") {
                                        $auditDetails = isset($spMap[$marketingDetail['service_id']]) ? $spMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) {
                                            continue;
                                        }
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'SP');
                                        $spAuditorVal = trim((string)($auditDetails["auditors"] ?? ''));
                                        $spDateVal = trim((string)($auditDetails["audit_date"] ?? ''));
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>'
                                            class="<?php echo $auditConflictClass; ?>"
                                            title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-uid-badge"><?php echo $uniqueIdVal !== '' ? htmlspecialchars($uniqueIdVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-client-name"><?php echo $clientNameVal !== '' ? htmlspecialchars($clientNameVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-badge ap-badge-sp">SP</span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-status-tag"><?php echo $serviceStatusVal !== '' ? htmlspecialchars($serviceStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-auditor"><i class="fa fa-user-circle-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $spAuditorVal !== '' ? htmlspecialchars($spAuditorVal, ENT_QUOTES, 'UTF-8') : '<span style="color: #94a3b8; font-style: italic; font-weight: normal;">Not Assigned</span>'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-sort-prefix" style="display:none !important;"><?php echo htmlspecialchars(simpleDateConversion($marketingDetail["audit_date"]), ENT_QUOTES, 'UTF-8'); ?>~</span>
                                                <span class="ap-date-badge"><i class="fa fa-calendar-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $spDateVal !== '' ? htmlspecialchars($spDateVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-present-status">
                                                    <span class="ap-audit-status"><?php echo $presentStatusVal !== '' ? htmlspecialchars($presentStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                                    <?php if ($nextServiceVal !== '') { ?>
                                                        <span class="ap-next-service">(<?php echo htmlspecialchars($nextServiceVal, ENT_QUOTES, 'UTF-8'); ?>)</span>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    } else if ($marketingDetail["service_type"] == "TA") {
                                        $auditDetails = isset($taMap[$marketingDetail['service_id']]) ? $taMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) {
                                            continue;
                                        }

                                        $auditrDetails = $auditDetails["auditors"];
                                        $auditDateDetails = $auditDetails["audit_date"];
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'TA');
                                        $taAuditorVal = trim((string)($auditrDetails ?? ''));
                                        $taDateVal = trim((string)($auditDateDetails ?? ''));
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>'
                                            class="<?php echo $auditConflictClass; ?>"
                                            title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-uid-badge"><?php echo $uniqueIdVal !== '' ? htmlspecialchars($uniqueIdVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-client-name"><?php echo $clientNameVal !== '' ? htmlspecialchars($clientNameVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-badge ap-badge-ta">TA</span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-status-tag"><?php echo $serviceStatusVal !== '' ? htmlspecialchars($serviceStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-auditor"><i class="fa fa-user-circle-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $taAuditorVal !== '' ? htmlspecialchars($taAuditorVal, ENT_QUOTES, 'UTF-8') : '<span style="color: #94a3b8; font-style: italic; font-weight: normal;">Not Assigned</span>'; ?></div>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <span class="ap-sort-prefix" style="display:none !important;"><?php echo htmlspecialchars(simpleDateConversion($marketingDetail["audit_date"]), ENT_QUOTES, 'UTF-8'); ?>~</span>
                                                <span class="ap-date-badge"><i class="fa fa-calendar-o" style="color: #64748b; margin-right: 4px;"></i><?php echo $taDateVal !== '' ? htmlspecialchars($taDateVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name">
                                                <div class="ap-present-status">
                                                    <span class="ap-audit-status"><?php echo $presentStatusVal !== '' ? htmlspecialchars($presentStatusVal, ENT_QUOTES, 'UTF-8') : '&mdash;'; ?></span>
                                                    <?php if ($nextServiceVal !== '') { ?>
                                                        <span class="ap-next-service">(<?php echo htmlspecialchars($nextServiceVal, ENT_QUOTES, 'UTF-8'); ?>)</span>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($auditDateConflictVisible) { ?>
    <button type="button" class="audit-conflict-tab" id="auditConflictTab" title="Check Date conflict">
        Date Conflict
        <span class="audit-conflict-count"><?php echo count($auditConflictGroups); ?></span>
    </button>

    <div class="audit-conflict-panel" id="auditConflictPanel" aria-hidden="true">
        <div class="audit-conflict-panel-header">
            <h4 class="audit-conflict-panel-title"><i class="fa fa-calendar-times-o"></i> Check Date Conflict</h4>
            <button type="button" class="audit-conflict-close" id="auditConflictClose" aria-label="Close">&times;</button>
        </div>
        <div class="audit-conflict-panel-body">
            <?php if (empty($auditConflictGroups)) { ?>
                <div class="audit-conflict-empty"><i class="fa fa-check-circle"></i> No same-day auditor conflicts found.</div>
            <?php } else { ?>
                <?php foreach ($auditConflictGroups as $conflictGroup) { ?>
                    <div class="audit-conflict-card">
                        <div class="audit-conflict-card-head">
                            <div class="audit-conflict-card-title">
                                <i class="fa fa-calendar-o" style="margin-right: 4px;"></i>
                                <?php echo htmlspecialchars($conflictGroup['date'], ENT_QUOTES, 'UTF-8'); ?> &mdash;
                                <?php echo htmlspecialchars($conflictGroup['auditor'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="audit-conflict-card-meta">
                                <?php echo count($conflictGroup['rows']); ?> audits on same day
                            </div>
                        </div>
                        <div>
                            <?php foreach ($conflictGroup['rows'] as $conflictRow) { ?>
                                <div class="audit-conflict-item">
                                    <div class="audit-conflict-client">
                                        <?php echo htmlspecialchars($conflictRow['customer_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div class="audit-conflict-detail">
                                        <span class="ap-uid-badge" style="font-size: 11px; padding: 1px 5px;"><?php echo htmlspecialchars($conflictRow['unique_id'], ENT_QUOTES, 'UTF-8'); ?></span> |
                                        <span class="ap-badge ap-badge-st1" style="font-size: 10px; padding: 1px 6px;"><?php echo htmlspecialchars($conflictRow['service_type'], ENT_QUOTES, 'UTF-8'); ?></span> |
                                        <i class="fa fa-calendar-o"></i> <?php echo htmlspecialchars($conflictRow['dates'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div class="audit-conflict-detail" style="margin-top: 4px;">
                                        Status: <strong><?php echo htmlspecialchars($conflictRow['audit_status'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        (<?php echo htmlspecialchars($conflictRow['next_service'], ENT_QUOTES, 'UTF-8'); ?>)
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
<?php } ?>

<?php if ($auditMemberConflictVisible) { ?>
    <button type="button" class="member-conflict-tab" id="memberConflictTab" title="Check Member conflict">
        Member Conflict
        <span class="audit-conflict-count"><?php echo count($memberConflictGroups); ?></span>
    </button>

    <div class="member-conflict-panel" id="memberConflictPanel" aria-hidden="true">
        <div class="audit-conflict-panel-header" style="background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%);">
            <h4 class="audit-conflict-panel-title"><i class="fa fa-users"></i> Check Member Conflict</h4>
            <button type="button" class="audit-conflict-close" id="memberConflictClose" aria-label="Close">&times;</button>
        </div>
        <div class="audit-conflict-panel-body">
            <?php if (empty($memberConflictGroups)) { ?>
                <div class="audit-conflict-empty"><i class="fa fa-check-circle"></i> No member conflicts found.</div>
            <?php } else { ?>
                <?php foreach ($memberConflictGroups as $conflict) { ?>
                    <div class="audit-conflict-card" style="border-left-color: #ea580c;">
                        <div class="audit-conflict-card-head" style="background: #fffaf5; border-bottom-color: #ffedd5;">
                            <div class="audit-conflict-card-title" style="color: #9a3412;">
                                <?php echo htmlspecialchars($conflict['unique_id'], ENT_QUOTES, 'UTF-8'); ?> &mdash;
                                <span class="ap-badge ap-badge-st1" style="font-size: 10px; padding: 1px 6px;"><?php echo htmlspecialchars($conflict['service_type'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>
                        <div>
                            <div class="audit-conflict-item">
                                <div class="audit-conflict-client">
                                    <?php echo htmlspecialchars($conflict['customer_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <?php foreach ($conflict['reasons'] as $reason) { ?>
                                    <div class="audit-conflict-detail" style="color: #b91c1c; font-weight: 600; margin-top: 3px;">
                                        &bull; <?php echo htmlspecialchars($reason, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
<?php } ?>

<script type="text/javascript">
    function debounce(callback, delay) {
        var debounceTimer;
        return function () {
            var context = this;
            var args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                callback.apply(context, args);
            }, delay);
        };
    }

    function normalizeSearchText(str) {
        if (!str) return "";
        return str.replace(/<[^>]*>/g, "")
                  .replace(/&amp;/g, '&')
                  .replace(/\s+/g, ' ')
                  .trim()
                  .toLowerCase();
    }

    function parseDateToObj(dateStr) {
        if (!dateStr) return null;
        dateStr = dateStr.replace(/<[^>]*>/g, "").trim();
        if (!dateStr || dateStr === '0000-00-00' || dateStr === '&mdash;' || dateStr === '—') return null;

        var parts = dateStr.split(/[.\-\/]/);
        if (parts.length === 3) {
            var p0 = parseInt(parts[0], 10);
            var p1 = parseInt(parts[1], 10);
            var p2 = parseInt(parts[2], 10);
            if (parts[0].length === 4) {
                // YYYY-MM-DD
                return new Date(p0, p1 - 1, p2, 0, 0, 0, 0);
            } else if (parts[2].length === 4) {
                // DD.MM.YYYY
                return new Date(p2, p1 - 1, p0, 0, 0, 0, 0);
            } else if (parts[2].length === 2) {
                // DD.MM.YY
                var yr = p2 < 50 ? 2000 + p2 : 1900 + p2;
                return new Date(yr, p1 - 1, p0, 0, 0, 0, 0);
            }
        }
        var d = new Date(dateStr);
        return isNaN(d.getTime()) ? null : d;
    }

    function extractDatesFromCell(cellContent) {
        if (!cellContent) return [];
        var clean = cellContent.replace(/<[^>]*>/g, " ");
        var matches = clean.match(/\b(?:\d{1,2}[.\-\/]\d{1,2}[.\-\/]\d{2,4}|\d{4}[.\-\/]\d{1,2}[.\-\/]\d{1,2})\b/g);
        if (!matches) return [];
        var dates = [];
        for (var i = 0; i < matches.length; i++) {
            var d = parseDateToObj(matches[i]);
            if (d) dates.push(d);
        }
        return dates;
    }

    function isAuditDateInRange(cellContent, fromDateStr, toDateStr) {
        var fromDateObj = parseDateToObj(fromDateStr);
        var toDateObj = parseDateToObj(toDateStr);
        if (toDateObj) {
            toDateObj.setHours(23, 59, 59, 999);
        }
        if (fromDateObj) {
            fromDateObj.setHours(0, 0, 0, 0);
        }

        var rowDates = extractDatesFromCell(cellContent);
        if (rowDates.length === 0) {
            return false;
        }

        // If at least one date in the row is within [fromDate, toDate]
        for (var i = 0; i < rowDates.length; i++) {
            var d = rowDates[i];
            var afterFrom = fromDateObj ? (d >= fromDateObj) : true;
            var beforeTo = toDateObj ? (d <= toDateObj) : true;
            if (afterFrom && beforeTo) {
                return true;
            }
        }
        return false;
    }

    function compareMinDates(fromDateStr, toDateStr) {
        var f = parseDateToObj(fromDateStr);
        var t = parseDateToObj(toDateStr);
        if (!f || !t) return true;
        return f <= t;
    }

    var isMarketingHead = <?php echo $isMarketingHead ? 'true' : 'false'; ?>;
    var isOfficeStaff = <?php echo $isOfficeStaff ? 'true' : 'false'; ?>;
    var isRestrictedRole = <?php echo $isRestrictedRole ? 'true' : 'false'; ?>;

    function validateDateRange() {
        var fromDate = ($("#filterFromDate").val() || $(".filterFromDate").val() || "").trim();
        var toDate = ($("#filterToDate").val() || $(".filterToDate").val() || "").trim();
        if (fromDate !== "" && toDate !== "") {
            if (!compareMinDates(fromDate, toDate)) {
                alert("To Date cannot be before From Date");
                $("#filterToDate, .filterToDate").val("");
            }
        }
    }

    $('#filterFromDate').datepicker({
        minDate: '-100Y',
        maxDate: '+100Y',
        changeYear: true,
        changeMonth: true,
        yearRange: "-20:+100",
        dateFormat: 'dd.mm.yy',
        onSelect: function () {
            validateDateRange();
            table.draw();
        }
    });

    $("#filterFromDate").keydown(function (e) {
        //            e.preventDefault();
    });

    $('#filterToDate').datepicker({
        minDate: '-100Y',
        maxDate: '+100Y',
        changeYear: true,
        changeMonth: true,
        yearRange: "-20:+100",
        dateFormat: 'dd.mm.yy',
        onSelect: function () {
            validateDateRange();
            table.draw();
        }
    });

    $("#filterToDate").keydown(function (e) {
        //            e.preventDefault();
    });

    // DataTable custom multi-filter logic
    $.fn.DataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var tableId = settings.nTable.id;
            if (tableId !== 'tab_customer_list') {
                return true;
            }

            var serviceType = ($("#service-type").val() || $(".service-type").val() || "").trim();
            var auditor = ($("#auditor-filter").val() || $(".auditor-filter").val() || "").trim();
            var fromDate = ($("#filterFromDate").val() || $(".filterFromDate").val() || "").trim();
            var toDate = ($("#filterToDate").val() || $(".filterToDate").val() || "").trim();
            var rawClientSearch = ($("#auditClientSearch").val() || $(".auditClientSearch").val() || "").trim();
            // If browser autofills user email (e.g. mainadmin@aitf.com), ignore it in table filter
            var clientSearch = (rawClientSearch.indexOf('@') !== -1 || rawClientSearch.toLowerCase() === 'mainadmin@aitf.com') ? "" : rawClientSearch;

            var rowUniqueId = normalizeSearchText(data[0] || "");
            var rowClient = normalizeSearchText(data[1] || "");
            var servieTypeStr = (data[2] || "").replace(/<[^>]*>/g, "").trim();
            var auditorStr = (data[4] || "").replace(/<[^>]*>/g, "").trim();
            var cellDateContent = data[5] || "";

            // 1. Service Type filter
            if (serviceType !== "") {
                if (servieTypeStr.toUpperCase() !== serviceType.toUpperCase()) {
                    return false;
                }
            }

            // 2. Auditor filter
            if (auditor !== "") {
                if (auditorStr.toUpperCase().indexOf(auditor.toUpperCase()) === -1) {
                    return false;
                }
            }

            // 3. Client Search (search by client name or unique ID):
            var searchVal = normalizeSearchText(clientSearch);
            var now = new Date();
            var firstDayOfPrevMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1, 0, 0, 0, 0);
            var rowDates = extractDatesFromCell(cellDateContent);
            var latestRowDate = rowDates.length > 0 ? rowDates[rowDates.length - 1] : null;
            var isCurrentOrRecent = (latestRowDate && latestRowDate >= firstDayOfPrevMonth);

            if (searchVal !== "") {
                var isMatch = (rowClient.indexOf(searchVal) !== -1 || rowUniqueId.indexOf(searchVal) !== -1);
                if (!isMatch) {
                    return false;
                }
                // For restricted roles: past records (older than 1 month) are ONLY unlocked when full client name/unique ID is typed
                if (isRestrictedRole && !isCurrentOrRecent) {
                    var isExactMatch = (rowClient === searchVal || rowUniqueId === searchVal);
                    if (!isExactMatch) {
                        return false;
                    }
                }
            } else {
                // When client search is empty:
                // If any filter is active (serviceType, auditor, fromDate, toDate), do NOT hide records for restricted roles
                var hasActiveFilter = (serviceType !== "" || auditor !== "" || fromDate !== "" || toDate !== "");
                if (isRestrictedRole && !hasActiveFilter) {
                    if (!isCurrentOrRecent) {
                        return false;
                    }
                }
            }

            // 4. Explicit From Date and To Date range filters
            if (fromDate !== "" || toDate !== "") {
                if (!isAuditDateInRange(cellDateContent, fromDate, toDate)) {
                    return false;
                }
            }

            return true;
        }
    );

    $(document).ready(function () {
        $('#auditConflictTab, #heroDateConflictPill').click(function () {
            $('#auditConflictPanel').css('right', '0').attr('aria-hidden', 'false');
        });

        $('#auditConflictClose').click(function () {
            $('#auditConflictPanel').css('right', '-450px').attr('aria-hidden', 'true');
        });

        $('#memberConflictTab, #heroMemberConflictPill').click(function () {
            $('#memberConflictPanel').css('right', '0').attr('aria-hidden', 'false');
        });

        $('#memberConflictClose').click(function () {
            $('#memberConflictPanel').css('right', '-450px').attr('aria-hidden', 'true');
        });

        $('#btnResetAuditFilters').click(function () {
            $('#service-type').val('');
            $('#auditor-filter').val('');
            $('#filterFromDate').val('');
            $('#filterToDate').val('');
            $('#auditClientSearch').val('');
            table.draw();
        });

        $(document).mouseup(function (e) {
            var container = $("#auditConflictPanel");
            var tab = $("#auditConflictTab");
            var pill1 = $("#heroDateConflictPill");
            if (!container.is(e.target) && container.has(e.target).length === 0 && !tab.is(e.target) && tab.has(e.target).length === 0 && !pill1.is(e.target) && pill1.has(e.target).length === 0) {
                container.css('right', '-450px').attr('aria-hidden', 'true');
            }

            var mContainer = $("#memberConflictPanel");
            var mTab = $("#memberConflictTab");
            var pill2 = $("#heroMemberConflictPill");
            if (!mContainer.is(e.target) && mContainer.has(e.target).length === 0 && !mTab.is(e.target) && mTab.has(e.target).length === 0 && !pill2.is(e.target) && pill2.has(e.target).length === 0) {
                mContainer.css('right', '-450px').attr('aria-hidden', 'true');
            }
        });

        var debouncedAuditSearch = debounce(function () {
            table.draw();
        }, 200);

        $('#auditClientSearch').on('input keyup change', debouncedAuditSearch);

        // Actively detect and wipe browser credential autofill (e.g. mainadmin@aitf.com) from search
        function clearAutofilledSearch() {
            var $search = $('#auditClientSearch');
            var val = ($search.val() || '').trim();
            if (val.indexOf('@') !== -1 || val.toLowerCase() === 'mainadmin@aitf.com') {
                $search.val('');
                if (typeof table !== 'undefined') {
                    table.draw();
                }
            }
        }

        clearAutofilledSearch();
        setTimeout(clearAutofilledSearch, 50);
        setTimeout(clearAutofilledSearch, 150);
        setTimeout(clearAutofilledSearch, 400);
        setTimeout(clearAutofilledSearch, 1000);

        $('.customerAccount').click(function () {
            var customerID = $(this).attr('data-id');
            //            alert(customerID);
            // open popup
            localStorage['visited'] = customerID;
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.sendAuditPlaning').click(function () {
            var service = $(this).attr('data-id');
            var customer = $(this).attr('data-customer');
            var url = 'addToAudit.php'
            var form = $('<form action="' + url + '" method="post">' +
                '<input type="hidden" name="service" value="' + service + '" />' +
                '<input type="hidden" name="customer" value="' + customer + '" />' +
                '</form>');
            $('body').append(form);
            form.submit();
        });

        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
            table.draw();
        });
    });

    var table = $('#tab_customer_list').DataTable({
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": true,
        "ordering": false,
        "lengthChange": false,
        "pageLength": 50,
        "info": true,
        "dom": "tr<'row dt-bottom-row'<'col-sm-6 col-xs-12'i><'col-sm-6 col-xs-12'p>>",
        "columnDefs": [
            {
                "width": "12%",
                "targets": 0
            },
            {
                "width": "20%",
                "targets": 1
            },
            {
                "width": "10%",
                "targets": 2
            },
            {
                "width": "12%",
                "targets": 3
            },
            {
                "width": "16%",
                "targets": 4
            },
            {
                "width": "15%",
                "targets": 5
            },
            {
                "width": "15%",
                "targets": 6
            }
        ],
        "language": {
            "emptyTable": "No data available in table",
            "zeroRecords": "No client found or incorrect spelling, check the spelling of client",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
    table.columns.adjust().draw();

    $('.service-type, .auditor-filter, .filterFromDate, .filterToDate, #service-type, #auditor-filter, #filterFromDate, #filterToDate').on('change input', function () {
        validateDateRange();
        table.draw();
    });

</script>
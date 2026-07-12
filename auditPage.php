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
        'CA' => [], 'SA-I' => [], 'SA-II' => [], 'RC' => [], 'SP' => [], 'TA' => []
    ];
    foreach ($allMarketingInfo as $row) {
        if (isset($serviceIdsByType[$row['service_type']])) {
            $serviceIdsByType[$row['service_type']][] = $row['service_id'];
        }
    }

    $caMap = $saiMap = $saiiMap = $rcMap = $spMap = $taMap = [];

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
    $taMap = $loadMap('service_type_ta', 'service_id', $serviceIdsByType['TA']);

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
            if (!$auditDetails) { continue; }
            auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'ST-1', isset($auditDetails["stage1_auditor"]) ? $auditDetails["stage1_auditor"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : '');
            if (!empty($auditDetails["stage2_audit"])) {
                auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'ST-2', isset($auditDetails["stage2_auditor"]) ? $auditDetails["stage2_auditor"] : '', $auditDetails["stage2_audit"]);
            }
        } else if ($marketingDetail["service_type"] == "SA-I") {
            $auditDetails = isset($saiMap[$marketingDetail['service_id']]) ? $saiMap[$marketingDetail['service_id']] : null;
            if ($auditDetails) { auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'SA-I', isset($auditDetails["auditors"]) ? $auditDetails["auditors"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : ''); }
        } else if ($marketingDetail["service_type"] == "SA-II") {
            $auditDetails = isset($saiiMap[$marketingDetail['service_id']]) ? $saiiMap[$marketingDetail['service_id']] : null;
            if ($auditDetails) { auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'SA-II', isset($auditDetails["auditors"]) ? $auditDetails["auditors"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : ''); }
        } else if ($marketingDetail["service_type"] == "RC") {
            $auditDetails = isset($rcMap[$marketingDetail['service_id']]) ? $rcMap[$marketingDetail['service_id']] : null;
            if ($auditDetails) { auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'RC', isset($auditDetails["auditors"]) ? $auditDetails["auditors"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : ''); }
        } else if ($marketingDetail["service_type"] == "SP") {
            $auditDetails = isset($spMap[$marketingDetail['service_id']]) ? $spMap[$marketingDetail['service_id']] : null;
            if ($auditDetails) { auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'SP', isset($auditDetails["auditors"]) ? $auditDetails["auditors"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : ''); }
        } else if ($marketingDetail["service_type"] == "TA") {
            $auditDetails = isset($taMap[$marketingDetail['service_id']]) ? $taMap[$marketingDetail['service_id']] : null;
            if ($auditDetails) { auditPageAddAuditRow($auditorDateRows, $auditorDateMeta, $auditRowDetails, $marketingDetail, 'TA', isset($auditDetails["auditors"]) ? $auditDetails["auditors"] : '', isset($auditDetails["audit_date"]) ? $auditDetails["audit_date"] : ''); }
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
    ?>

    <style>
        .custom-float-label-control {
            margin-bottom: 0em;
        }

        #tab_customer_list tbody tr.audit-conflict-row td {
            background-color: #fee2e2 !important;
            color: #7f1d1d;
        }

        #tab_customer_list tbody tr.audit-conflict-row small,
        #tab_customer_list tbody tr.audit-conflict-row span {
            color: #7f1d1d;
            font-weight: 700;
        }

        .audit-conflict-tab {
            position: fixed;
            right: 0;
            top: 210px;
            z-index: 9998;
            border: 0;
            border-radius: 6px 0 0 6px;
            background: #b91c1c;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0;
            padding: 10px 12px;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.25);
        }

        .audit-conflict-count {
            display: inline-block;
            min-width: 18px;
            margin-top: 7px;
            border-radius: 999px;
            background: #fff;
            color: #b91c1c;
            line-height: 18px;
            text-align: center;
            writing-mode: horizontal-tb;
        }

        .audit-conflict-panel {
            position: fixed;
            top: 0;
            right: -430px;
            z-index: 9999;
            width: 420px;
            max-width: calc(100vw - 24px);
            height: 100vh;
            background: #ffffff;
            border-left: 1px solid #fecaca;
            box-shadow: -14px 0 34px rgba(15, 23, 42, 0.22);
            transition: right 0.2s ease;
        }

        .audit-conflict-panel.is-open {
            right: 0;
        }

        .audit-conflict-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: #7f1d1d;
            color: #fff;
        }

        .audit-conflict-panel-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
        }

        .audit-conflict-close {
            border: 0;
            background: transparent;
            color: #fff;
            font-size: 24px;
            line-height: 1;
        }

        .audit-conflict-panel-body {
            height: calc(100vh - 54px);
            overflow-y: auto;
            padding: 12px;
        }

        .audit-conflict-empty {
            margin: 8px 0 0;
            padding: 12px;
            border: 1px solid #dcfce7;
            background: #f0fdf4;
            color: #166534;
            font-weight: 700;
        }

        .audit-conflict-card {
            border: 1px solid #fecaca;
            border-left: 4px solid #b91c1c;
            border-radius: 6px;
            margin-bottom: 10px;
            background: #fff7f7;
        }

        .audit-conflict-card-head {
            padding: 10px 12px;
            border-bottom: 1px solid #fecaca;
        }

        .audit-conflict-card-title {
            color: #7f1d1d;
            font-size: 13px;
            font-weight: 800;
        }

        .audit-conflict-card-meta {
            color: #991b1b;
            font-size: 12px;
            margin-top: 3px;
        }

        .audit-conflict-item {
            padding: 9px 12px;
            border-top: 1px solid #fee2e2;
        }

        .audit-conflict-item:first-child {
            border-top: 0;
        }

        .audit-conflict-client {
            color: #111827;
            font-size: 13px;
            font-weight: 700;
        }

        .audit-conflict-detail {
            color: #4b5563;
            font-size: 12px;
            margin-top: 2px;
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container">

                <div class="row">
                    <div class="col-sm-12 main_content" style="">
                        <div class="row" style="padding-top:10px;">

                            <div class="col-sm-3 float-label-control custom-float-label-control">
                                <select name="service-type" class="form-control service-type" id="service-type">
                                    <option value="">Select Service Type</option>
                                    <option value="ST-1">ST-1</option>
                                        <option value="ST-2">ST-2</option>
                                    <?php foreach ($servicesTypes as $service) { ?>
                                        <?php if ($service['service_type'] === 'CA') { continue; } ?>
                                        <option value="<?php echo $service['service_type'] ?>">
                                            <?php echo $service['service_type'] ?>
                                        </option>
                                    <?php } ?>
                                        
                                </select>
                            </div>
                            <!--                            <div class="col-sm-3 float-label-control custom-float-label-control">
                                                            <select name="auditors" class="form-control auditors" id="service-type">
                                                                <option value="">Select Auditor</option>
                            <?php foreach ($auditorsList as $auditor) { ?>
                                                                                <option value="<?php echo $auditor['auditor_name'] ?>">
                                <?php echo $auditor['auditor_name'] ?>
                                                                                </option>
                            <?php } ?>
                                                            </select>
                                                        </div>-->
                            <div class="col-sm-3 float-label-control custom-float-label-control">
                                <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="Audit Date From" value="">
                            </div>
                            <div class="col-sm-3 form-group float-label-control custom-float-label-control">
                                <input type="text" class="filterToDate" id="filterToDate" name="filterToDate" placeholder="Audit Date To" value="">
                            </div>
                            <div class="col-sm-3 float-label-control custom-float-label-control">
                                <select name="auditors" class="form-control auditor-filter" id="auditor-filter">
                                    <option value="">Select Auditor</option>
                                    <?php foreach ($auditorsList as $auditor) { ?>
                                        <option value="<?php echo htmlspecialchars($auditor['auditor_name'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?php echo htmlspecialchars($auditor['auditor_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row clearfix">
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list" width="100%">
                            <thead>
                                <tr>
                                    <th class="">
                                        <small><a>Unique ID</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Client Name</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Service Type</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Service Status</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Auditor(s)</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Audit Date(s)</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Present Status</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($allMarketingInfo as $marketingDetail) {
                                    $auditrDetails = "";
                                    $auditDateDetails = "";
                                    if ($marketingDetail["service_type"] == "CA") {
                                        $auditDetails = isset($caMap[$marketingDetail['service_id']]) ? $caMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) { continue; }
                                        $auditdates1 = explode(',', $auditDetails["audit_date"]);
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'ST-1');
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="<?php echo $auditConflictClass; ?>" title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small>ST-1</small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditDetails["stage1_auditor"]; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo $auditdates1[0] ?>~</span><small><?php echo $auditDetails["audit_date"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                            </td>
                                        </tr>
                                        <?php if ($auditDetails["stage2_audit"] != "") { 
                                            $auditdatest2 = explode(',', $auditDetails["stage2_audit"]);
                                            $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'ST-2');
                                            ?>
                                            <tr id='addr<?php echo $marketingDetail['id'] ?>' class="<?php echo $auditConflictClass; ?>" title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small><?php echo $marketingDetail['unique_id'] ?></small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small><?php echo $marketingDetail['customer_name'] ?></small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small>ST-2</small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small><?php echo $marketingDetail['service_status'] ?></small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small><?php echo $auditDetails["stage2_auditor"]; ?></small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <span><?php echo $auditdatest2[0] ?>~</span><small><?php echo $auditDetails["stage2_audit"] ?></small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else if ($marketingDetail["service_type"] == "SA-I") {
                                        $auditDetails = isset($saiMap[$marketingDetail['service_id']]) ? $saiMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) { continue; }
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'SA-I');
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="<?php echo $auditConflictClass; ?>" title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditDetails["auditors"]; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo simpleDateConversion($marketingDetail["audit_date"]) ?>~</span><small><?php echo $auditDetails["audit_date"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                            </td>
                                        </tr>
                                        <?php
                                    }  else if ($marketingDetail["service_type"] == "SA-II") {
                                        $auditDetails = isset($saiiMap[$marketingDetail['service_id']]) ? $saiiMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) { continue; }
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'SA-II');
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="<?php echo $auditConflictClass; ?>" title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditDetails["auditors"]; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo simpleDateConversion($marketingDetail["audit_date"]) ?>~</span><small><?php echo $auditDetails["audit_date"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                            </td>
                                        </tr>
                                        <?php
                                    } else if ($marketingDetail["service_type"] == "RC") {
                                        $auditDetails = isset($rcMap[$marketingDetail['service_id']]) ? $rcMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) { continue; }
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'RC');
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="<?php echo $auditConflictClass; ?>" title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditDetails["auditors"]; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo simpleDateConversion($marketingDetail["audit_date"]) ?>~</span><small><?php echo $auditDetails["audit_date"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                            </td>
                                        </tr>
                                        <?php
                                    } else if ($marketingDetail["service_type"] == "SP") {
                                        $auditDetails = isset($spMap[$marketingDetail['service_id']]) ? $spMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) { continue; }
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'SP');
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="<?php echo $auditConflictClass; ?>" title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditDetails["auditors"]; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo simpleDateConversion($marketingDetail["audit_date"]) ?>~</span><small><?php echo $auditDetails["audit_date"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                            </td>
                                        </tr>
                                        <?php
                                    } else if ($marketingDetail["service_type"] == "TA") {
                                        $auditDetails = isset($taMap[$marketingDetail['service_id']]) ? $taMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) { continue; }

                                        $auditrDetails = $auditDetails["auditors"];
                                        $auditDateDetails = $auditDetails["audit_date"];
                                        $auditConflictClass = auditPageConflictClass($auditConflictRows, $marketingDetail, 'TA');
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="<?php echo $auditConflictClass; ?>" title="<?php echo $auditConflictClass ? 'Same auditor has another audit on this date' : ''; ?>">
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditrDetails; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo simpleDateConversion($marketingDetail["audit_date"]) ?>~</span><small><?php echo $auditDateDetails ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
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

<button type="button" class="audit-conflict-tab" id="auditConflictTab" title="Check Date  conflict">
    Date  Conflict
    <span class="audit-conflict-count"><?php echo count($auditConflictGroups); ?></span>
</button>

<div class="audit-conflict-panel" id="auditConflictPanel" aria-hidden="true">
    <div class="audit-conflict-panel-header">
        <h4 class="audit-conflict-panel-title">Check Date Conflict</h4>
        <button type="button" class="audit-conflict-close" id="auditConflictClose" aria-label="Close">&times;</button>
    </div>
    <div class="audit-conflict-panel-body">
        <?php if (empty($auditConflictGroups)) { ?>
            <div class="audit-conflict-empty">No same-day auditor conflicts found.</div>
        <?php } else { ?>
            <?php foreach ($auditConflictGroups as $conflictGroup) { ?>
                <div class="audit-conflict-card">
                    <div class="audit-conflict-card-head">
                        <div class="audit-conflict-card-title">
                            <?php echo htmlspecialchars($conflictGroup['date'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($conflictGroup['auditor'], ENT_QUOTES, 'UTF-8'); ?>
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
                                    <?php echo htmlspecialchars($conflictRow['unique_id'], ENT_QUOTES, 'UTF-8'); ?> |
                                    <?php echo htmlspecialchars($conflictRow['service_type'], ENT_QUOTES, 'UTF-8'); ?> |
                                    <?php echo htmlspecialchars($conflictRow['dates'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="audit-conflict-detail">
                                    Status: <?php echo htmlspecialchars($conflictRow['audit_status'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($conflictRow['next_service'], ENT_QUOTES, 'UTF-8'); ?>)
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
    $('#filterFromDate').datepicker({
        minDate: '-100Y',
        maxDate: '+100Y',
        changeYear: true,
        changeMonth: true,
        yearRange: "-20:+100",
        dateFormat: 'dd.mm.yy'
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
        dateFormat: 'dd.mm.yy'
    });

    $("#filterToDate").keydown(function (e) {
        //            e.preventDefault();
    });

    function compareMinDates(filterDate, rowDate) {
        var filterDateSplit = filterDate.split(".");
        var rowDateSplit = rowDate.split(".");
        if (filterDateSplit[2] < rowDateSplit[2]) {
            return true;
        } else if (filterDateSplit[2] == rowDateSplit[2]) {
            if (filterDateSplit[1] < rowDateSplit[1]) {
                return true;
            } else if (filterDateSplit[1] == rowDateSplit[1]) {
                if (filterDateSplit[0] < rowDateSplit[0]) {
                    return true;
                } else if (filterDateSplit[0] == rowDateSplit[0]) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    function compareMaxDates(filterDate, rowDate) {
        var filterDateSplit = filterDate.split(".");
        var rowDateSplit = rowDate.split(".");
        //year comparison
        if (filterDateSplit[2] > rowDateSplit[2]) {
            return true;
        } else if (filterDateSplit[2] == rowDateSplit[2]) {
            if (filterDateSplit[1] > rowDateSplit[1]) {
                return true;
            } else if (filterDateSplit[1] == rowDateSplit[1]) {
                if (filterDateSplit[0] > rowDateSplit[0]) {
                    return true;
                } else if (filterDateSplit[0] == rowDateSplit[0]) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    //for filtering

    $.fn.DataTable.ext.search.push(
            function (settings, data, dataIndex) {
                var serviceType = $(".service-type").val();
                var auditor = $(".auditor-filter").val();
                var fromDate = $(".filterFromDate").val();
                var toDate = $(".filterToDate").val();
                var isServiceType = false;
                var isAuditor = false;
                var isfromDate = false;
                var isToDate = false;
                //            alert(serviceType);
                if (serviceType == "" && auditor == "" && fromDate == "" && toDate == "") {
                    return true;
                }
                if (serviceType != "") {
                    var servieTypeStr = data[2] || ""; // use data for the age column
//                    alert(servieTypeStr);
                    if (servieTypeStr.toUpperCase().trim() == serviceType.toUpperCase().trim()) {
                        //                alert("test");
                        isServiceType = true;
                    }
                } else {
                    isServiceType = true;
                }
                if (auditor != "") {
                    var auditorStr = data[4] || "";
                    isAuditor = auditorStr.toUpperCase().indexOf(auditor.toUpperCase()) !== -1;
                } else {
                    isAuditor = true;
                }
                var dateValue = data[5] || "";
                var dateSplit = dateValue.split("~");
                var rowDate = dateSplit.length > 1 ? dateSplit[1] : dateValue;
                //            alert(dateSplit[1]);
                //            alert(fromDate);
                if (fromDate != "") {
//                      alert(dateValue);
                    isfromDate = compareMinDates(fromDate, rowDate);
                } else {
                    isfromDate = true;
                }
                if (toDate != "") {
                    isToDate = compareMaxDates(toDate, rowDate);
                } else {
                    isToDate = true;
                }
                finalResult = (isServiceType && isAuditor && isfromDate && isToDate);
                return finalResult;
            }
    );

    $(document).ready(function () {
        $('#auditConflictTab').click(function () {
            $('#auditConflictPanel').addClass('is-open').attr('aria-hidden', 'false');
        });

        $('#auditConflictClose').click(function () {
            $('#auditConflictPanel').removeClass('is-open').attr('aria-hidden', 'true');
        });

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
//            "scrollY": "450px",
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": false,
        "ordering": false,
        "lengthChange": false,
//            "pageLength": 10,
        "paging": false,
        "columnDefs": [
            {
                "width": "15%",
                "targets": 0
            },
            {
                "width": "15%",
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
                "width": "15%",
                "targets": 4
            },
            {
                "width": "15%",
                "targets": 5
            },
        ]
                //        "paging":         false
    });
    table.columns.adjust().draw();

    $('.service-type, .auditor-filter, .filterFromDate, .filterToDate').change(function () {
        var fromDate = $(".filterFromDate").val();
        var toDate = $(".filterToDate").val();
        if (fromDate != "" && toDate != "") {
            if (!compareMinDates(fromDate, toDate)) {
                alert("To Date can't be after From Date");
                $(".filterToDate").val("");
            }
        }
        //            alert("t");
        table.draw();
    });

</script>

<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "IAF Dashboard";
    include 'header.php';
    if (!isset($db)) {
        include_once './dbConnect.php';
    }
    require_once __DIR__ . '/include/iaf_dashboard_data.php';
    $clients = [];
    $iafStatusOptions = [];
    $iafInitialLimit = 30;
    $iafInitialNextOffset = 0;
    $iafInitialHasMore = false;
    $iafInitialTotalCount = 0;

    if (isset($db) && $db instanceof PDO) {
        try {
            $initialData = iafDashboardFetchRows($db, 0, $iafInitialLimit, '');
            $clients = $initialData['rows'];
            $iafInitialNextOffset = (int) ($initialData['next_offset'] ?? 0);
            $iafInitialHasMore = !empty($initialData['has_more']);
            $iafInitialTotalCount = (int) ($initialData['total_count'] ?? 0);
            $iafStatusOptions = iafDashboardFetchStatusOptions($db);
        } catch (Exception $e) {
            error_log('IAF Dashboard initial preload failed: ' . $e->getMessage());
            $clients = [];
            $iafStatusOptions = [];
            $iafInitialNextOffset = 0;
            $iafInitialHasMore = false;
            $iafInitialTotalCount = 0;
        }
    }
    if (false) {

    // SERVICE-LEVEL QUERY: One row per service per customer
  
 
    
    $query = "SELECT
        c.id as internal_client_id,
        c.customer_name,
        'India' as country,
        c.address,
        c.scope,
        svc.service_id as svc_id,
        svc.service_type as svc_type,
        cou.id as cdc_office_use_id,
        cou.iso_index as iso_index,
        -- Per-service status: prefer cdc_status (Granted/Not Granted), then service.service_status, then customer audit_status
        COALESCE(
            NULLIF(TRIM(cdc.cdc_status), ''),
            NULLIF(TRIM(svc.service_status), ''),
            c.audit_status
        ) as certification_status,
        cp.certificate_id as certificate_tracking_id,
        -- Prefer ISO-level office-use values so multi-standard clients keep separate certificate numbers.
        COALESCE(NULLIF(cou.certificate_no, ''), NULLIF(cp.certificate_no, ''), NULLIF(cdc.certificateNo, '')) as certificate_number,
        CASE WHEN UPPER(latest_cert_svc.service_type) = 'RC' THEN COALESCE(latest_rc_cp.registration_date, latest_rc_cdc.Date) WHEN UPPER(latest_cert_svc.service_type) = 'CA' THEN COALESCE(latest_ca_cou.rev_date, latest_ca_cp.registration_date, latest_ca_cdc.Date) ELSE COALESCE(cou.rev_date, cp.registration_date, cdc.Date) END as certificate_issue_date,
        CASE WHEN UPPER(latest_cert_svc.service_type) = 'RC' THEN COALESCE(latest_rc_cp.registration_date, latest_rc_cdc.Date) WHEN UPPER(latest_cert_svc.service_type) = 'CA' THEN COALESCE(latest_ca_cou.rev_date, latest_ca_cp.registration_date, latest_ca_cdc.Date) ELSE COALESCE(cou.rev_date, cp.registration_date, cdc.Date) END as issue_date,
        COALESCE(cou.rev_date, cp.registration_date, cdc.Date) as original_issue_date,
        CASE WHEN UPPER(latest_cert_svc.service_type) = 'RC' THEN COALESCE(latest_rc_cp.valid_until, latest_rc_cdc.expiredate) WHEN UPPER(latest_cert_svc.service_type) = 'CA' THEN COALESCE(latest_ca_cou.expire_date, latest_ca_cp.valid_until, latest_ca_cdc.expiredate) ELSE COALESCE(cou.expire_date, cp.valid_until, cdc.expiredate) END as expiry_date,
        CASE WHEN UPPER(latest_cert_svc.service_type) = 'RC' THEN COALESCE(latest_rc_cp.valid_until, latest_rc_cdc.expiredate) WHEN UPPER(latest_cert_svc.service_type) = 'CA' THEN COALESCE(latest_ca_cou.expire_date, latest_ca_cp.valid_until, latest_ca_cdc.expiredate) ELSE COALESCE(cou.expire_date, cp.valid_until, cdc.expiredate) END as expire_date,
        cp.certificate_scope,
        COALESCE(NULLIF(aa.proposed_scope, ''), cp.certificate_scope, c.scope) as certification_scope,
        COALESCE(
            NULLIF(TRIM(aa.accereditation_requirement), ''),
            NULLIF(TRIM(aa.review_accredition), '')
        ) as accreditation_body_name,
        COALESCE(
            NULLIF(TRIM(aa.accereditation_requirement), ''),
            NULLIF(TRIM(aa.review_accredition), '')
        ) as accreditation_body,
        NULLIF(TRIM(aa.accereditation_requirement), '') as accreditation_requirement,
        COALESCE(
            NULLIF(cou.iso_label, ''),
            NULLIF(aa.assessment_standard, ''),
            (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id AND asi.serviceca_id = stca.ca_id ORDER BY asi.audit_schedule_id DESC LIMIT 1),
            (SELECT asi2.schedule_app_standard FROM audit_schedule_id asi2 WHERE asi2.customer_ca_id = c.id ORDER BY asi2.audit_schedule_id DESC LIMIT 1)
        ) as standard_name,
        '' as schema_name,
        '' as scheme_name,
        aa.n_location as site
    FROM customer_info c
    INNER JOIN service svc ON svc.customer_id = c.id
    LEFT JOIN service latest_cert_svc ON latest_cert_svc.service_id = (
        SELECT MAX(s_latest.service_id) FROM service s_latest
        WHERE s_latest.customer_id = c.id AND UPPER(s_latest.service_type) IN ('CA', 'RC')
    )
    LEFT JOIN service_type_rc latest_rc ON latest_rc.rc_id = (
        SELECT MAX(strc.rc_id) FROM service_type_rc strc
        INNER JOIN service s_rc ON s_rc.service_id = strc.service
        WHERE s_rc.customer_id = c.id AND UPPER(s_rc.service_type) = 'RC'
    )
    LEFT JOIN service_type_ca latest_ca ON latest_ca.ca_id = (
        SELECT MAX(stca_latest.ca_id) FROM service_type_ca stca_latest
        INNER JOIN service s_ca ON s_ca.service_id = stca_latest.service
        WHERE s_ca.customer_id = c.id AND UPPER(s_ca.service_type) = 'CA'
    )
    LEFT JOIN certificate_print latest_ca_cp ON latest_ca_cp.service_id = latest_ca.ca_id
        AND latest_ca_cp.certificate_id = (
            SELECT MAX(cp_ca.certificate_id) FROM certificate_print cp_ca WHERE cp_ca.service_id = latest_ca.ca_id
        )
    LEFT JOIN cdc_report_review latest_ca_cdc ON latest_ca_cdc.ca_service_id = latest_ca.ca_id
        AND latest_ca_cdc.customer_id = c.id
        AND latest_ca_cdc.crr_id = (
            SELECT MAX(cdc_ca.crr_id) FROM cdc_report_review cdc_ca
            WHERE cdc_ca.ca_service_id = latest_ca.ca_id AND cdc_ca.customer_id = c.id
        )
    LEFT JOIN cdc_report_review_office_use latest_ca_cou ON latest_ca_cou.crr_id = latest_ca_cdc.crr_id
        AND latest_ca_cou.id = (
            SELECT MAX(cou_ca.id) FROM cdc_report_review_office_use cou_ca WHERE cou_ca.crr_id = latest_ca_cdc.crr_id
        )
    LEFT JOIN certificate_print latest_rc_cp ON latest_rc_cp.service_id = latest_rc.rc_id
        AND latest_rc_cp.certificate_id = (
            SELECT MAX(cp_rc.certificate_id) FROM certificate_print cp_rc WHERE cp_rc.service_id = latest_rc.rc_id
        )
    LEFT JOIN cdc_report_review latest_rc_cdc ON latest_rc_cdc.ca_service_id = latest_rc.rc_id
        AND latest_rc_cdc.customer_id = c.id
        AND latest_rc_cdc.crr_id = (
            SELECT MAX(cdc_rc.crr_id) FROM cdc_report_review cdc_rc
            WHERE cdc_rc.ca_service_id = latest_rc.rc_id AND cdc_rc.customer_id = c.id
        )
    -- Per-service certificate_print (each service has its own cert)
    LEFT JOIN certificate_print cp ON cp.service_id = svc.service_id
        AND cp.certificate_id = (
            SELECT MAX(cp_s.certificate_id) FROM certificate_print cp_s WHERE cp_s.service_id = svc.service_id
        )
    -- Per-service CDC report: link through service_type_ca
    LEFT JOIN service_type_ca stca ON stca.service = svc.service_id
    LEFT JOIN cdc_report_review cdc ON cdc.ca_service_id = stca.ca_id
        AND cdc.customer_id = c.id
        AND cdc.crr_id = (
            SELECT MAX(cdc_s.crr_id) FROM cdc_report_review cdc_s
            WHERE cdc_s.ca_service_id = stca.ca_id AND cdc_s.customer_id = c.id
        )
    LEFT JOIN cdc_report_review_office_use cou ON cou.crr_id = cdc.crr_id
    -- Per-service audit_application
    LEFT JOIN audit_application aa ON aa.audit_id = (
        SELECT MAX(aa_s.audit_id) FROM audit_application aa_s
        WHERE aa_s.uni_svc_id = svc.service_id OR aa_s.service_id = svc.service_id OR aa_s.service_id = stca.ca_id
    )
    WHERE NOT (UPPER(svc.service_type) = 'CR' AND cp.certificate_id IS NULL AND cdc.crr_id IS NULL)
    ORDER BY c.id DESC, svc.service_id ASC, cou.iso_index ASC";

    $clients = [];
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Fallback: if service-level query fails, use simpler version
        error_log("IAF Dashboard service-level query failed: " . $e->getMessage());
        $clients = [];
    }

    // =========================================================================
    // FALLBACK: Clients with NO services should still appear (one row each)
    // =========================================================================
    $clientIdsWithServices = [];
    foreach ($clients as $c) {
        $clientIdsWithServices[$c['internal_client_id']] = true;
    }

    $fallbackQuery = "SELECT
        c.id as internal_client_id,
        c.customer_name,
        'India' as country,
        c.address,
        c.scope,
        '' as svc_id,
        '' as svc_type,
        '' as cdc_office_use_id,
        '' as iso_index,
        c.audit_status as certification_status,
        '' as certificate_tracking_id,
        '' as certificate_number,
        '' as certificate_issue_date,
        '' as issue_date,
        '' as original_issue_date,
        '' as expiry_date,
        '' as expire_date,
        '' as certificate_scope,
        c.scope as certification_scope,
        '' as accreditation_body_name,
        '' as accreditation_body,
        '' as accreditation_requirement,
        '' as standard_name,
        '' as schema_name,
        '' as scheme_name,
        '' as site
    FROM customer_info c
    WHERE c.id NOT IN (SELECT DISTINCT s.customer_id FROM service s WHERE s.customer_id IS NOT NULL)
    ORDER BY c.id DESC";

    try {
        $stmt2 = $db->prepare($fallbackQuery);
        $stmt2->execute();
        $noServiceClients = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $clients = array_merge($clients, $noServiceClients);
    } catch (Exception $e) {
        error_log("IAF Dashboard fallback query failed: " . $e->getMessage());
    }

    function iafDashboardHasRealValue($value)
    {
        $value = trim((string) $value);
        return $value !== '' && $value !== '0000-00-00' && strtoupper($value) !== 'N/A';
    }

    function iafDashboardStandardKey($standardName)
    {
        $standardName = strtoupper(trim((string) $standardName));
        if ($standardName === '') {
            return 'NO_STANDARD';
        }

        if (preg_match('/(9001|14001|45001|18001|27001|22000|22301|13485|50001|20000|21001)/', $standardName, $matches)) {
            return 'ISO_' . $matches[1];
        }

        return preg_replace('/[^A-Z0-9]+/', '_', $standardName);
    }

    function iafDashboardRowScore(array $client)
    {
        $status = strtolower(trim((string) ($client['certification_status'] ?? '')));
        $score = 0;

        if (iafDashboardHasRealValue($client['certificate_number'] ?? '')) {
            $score += 1000;
        }
        if ($status === 'granted' || strpos($status, 'grant') !== false || strpos($status, 'active') !== false || strpos($status, 'valid') !== false) {
            $score += 200;
        }
        if (iafDashboardHasRealValue($client['certificate_issue_date'] ?? '') || iafDashboardHasRealValue($client['issue_date'] ?? '')) {
            $score += 80;
        }
        if (iafDashboardHasRealValue($client['expire_date'] ?? '') || iafDashboardHasRealValue($client['expiry_date'] ?? '')) {
            $score += 80;
        }
        if (iafDashboardHasRealValue($client['cdc_office_use_id'] ?? '')) {
            $score += 20;
        }
        if (iafDashboardHasRealValue($client['certificate_tracking_id'] ?? '')) {
            $score += 10;
        }

        return $score;
    }

    // =========================================================================
    // Normalize all client data and remove same-client/same-ISO duplicates.
    // Multi-ISO clients still keep one row per ISO, but repeated services for the same ISO are merged.
    // =========================================================================
    $dedupedClients = [];
    foreach ($clients as $client) {
        $client = iafCertSearchNormalizeClientData($client);
        $client['certification_scope'] = iafCertSearchFirstValue($client, ['certification_scope', 'proposed_scope', 'certificate_scope', 'scope']);
        $client['scheme_name'] = iafCertSearchFirstValue($client, ['scheme_name', 'schema_name']);
        $client['accreditation_body'] = iafCertSearchNormalizeAccreditationBodyName(iafCertSearchFirstValue($client, ['accreditation_body', 'accreditation_body_name', 'accreditation_requirement', 'accereditation_requirement'], 'NABCB'));
        $client['certificate_issue_date'] = iafCertSearchFirstValue($client, ['certificate_issue_date', 'issue_date']);
        $client['issue_date'] = iafCertSearchFirstValue($client, ['issue_date', 'certificate_issue_date']);
        $client['expire_date'] = iafCertSearchFirstValue($client, ['expire_date', 'expiry_date']);

        $standardKey = iafDashboardStandardKey($client['standard_name'] ?? '');
        $dedupeKey = ($client['internal_client_id'] ?? '') . '|' . $standardKey;

        if (!isset($dedupedClients[$dedupeKey]) || iafDashboardRowScore($client) > iafDashboardRowScore($dedupedClients[$dedupeKey])) {
            $dedupedClients[$dedupeKey] = $client;
        }
    }
    $clients = array_values($dedupedClients);
    }

    ?>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <style>
        :root {
            --iaf-navbar-height: 0px;
            --iaf-sidebar-width: 180px;
            --iaf-sidebar-collapsed: 50px;
            --iaf-radius: 12px;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #1e293b;
        }

        body #sidebar-wrapper {
            position: fixed;
            top: 0;
            bottom: 0;
            height: 100vh;
            z-index: 11060;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #wrapper {
            min-height: 100vh;
            overflow-x: auto;
        }

        #page-content-wrapper.iaf-dashboard-page {
            min-height: 100vh;
            margin-top: 72px;
            padding-top: 0;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .iaf-dashboard-page .page-content {
            min-height: 100vh;
        }

        .iaf-dashboard-page .container-fluid {
            width: 100%;
            max-width: 100%;
            padding: 8px 12px 14px !important;
        }

        .page-header-container {
            background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 12px 18px;
            margin: 0 0 10px;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(13, 77, 139, 0.16);
            display: flex;
            justify-content: flex-start;
            align-items: center;
            min-height: 56px;
            flex: 0 0 auto;
        }

        .page-title {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.3px;
            line-height: 1.2;
            color: #ffffff;
        }

        .page-subtitle {
            margin: 3px 0 0;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.3;
            color: rgba(255, 255, 255, 0.88);
        }

        .modern-card {
            background: #ffffff;
            border-radius: var(--iaf-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            min-height: 0;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
        }

        .iaf-table-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 10px;
            flex: 0 0 auto;
            flex-wrap: wrap;
        }

        .iaf-filter-group {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .client-filter-label {
            margin: 0;
            color: #0d4d8b;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .client-filter-input {
            width: min(320px, 100%);
            height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 7px 12px;
            outline: none;
            background: #ffffff;
            color: #1e293b;
            font-size: 12.5px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .client-filter-input:focus {
            border-color: #0d4d8b;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12);
        }

        .status-filter-select {
            width: min(220px, 100%);
            height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 7px 34px 7px 12px;
            outline: none;
            background: #ffffff;
            color: #1e293b;
            font-size: 12.5px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .status-filter-select:focus {
            border-color: #0d4d8b;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12);
        }

        /* ===== Search field: icon + clear button ===== */
        .client-filter-field {
            position: relative;
            display: inline-flex;
            align-items: center;
            width: min(320px, 100%);
        }

        .client-filter-field .client-filter-input {
            width: 100%;
            padding-left: 36px;
            padding-right: 34px;
        }

        .client-filter-icon {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            font-size: 13px;
            pointer-events: none;
        }

        .client-filter-clear {
            position: absolute;
            right: 8px;
            display: none;
            align-items: center;
            justify-content: center;
            border: none;
            background: #e2e8f0;
            color: #475569;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 13px;
            line-height: 1;
            cursor: pointer;
            padding: 0;
            transition: background-color 0.15s;
        }

        .client-filter-clear:hover {
            background: #cbd5e1;
        }

        .client-filter-field.has-value .client-filter-clear {
            display: inline-flex;
        }

        /* ===== Loading states + empty state ===== */
        .iaf-table-wrap {
            position: relative;
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        @keyframes iaf-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .iaf-spinner {
            width: 38px;
            height: 38px;
            border: 3.5px solid #e2e8f0;
            border-top-color: #0d4d8b;
            border-radius: 50%;
            animation: iaf-spin 0.75s linear infinite;
        }

        .iaf-loading-overlay {
            position: absolute;
            inset: 0;
            z-index: 30;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 12px;
        }

        .iaf-loading-overlay.active {
            display: flex;
        }

        .iaf-loading-text {
            color: #0f172a;
            font-weight: 700;
            font-size: 13px;
        }

        .iaf-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding-top: 14px;
            width: 100%;
            box-sizing: border-box;
            flex: 0 0 auto;
        }

        .iaf-pagination:empty {
            display: none;
        }

        .iaf-page-info {
            color: #64748b;
            font-size: 12.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .iaf-page-controls {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
            white-space: nowrap;
        }

        .iaf-page-btn {
            min-width: 36px;
            height: 34px;
            padding: 0 12px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .iaf-page-btn:hover:not(:disabled):not(.current) {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .iaf-page-btn.current {
            background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%);
            border-color: #0d4d8b;
            color: #ffffff;
            cursor: default;
        }

        .iaf-page-btn:disabled {
            opacity: 0.45;
            cursor: default;
        }

        .iaf-page-ellipsis {
            padding: 0 4px;
            color: #94a3b8;
            align-self: center;
        }

        table.dataTable td.dataTables_empty {
            text-align: center;
            padding: 50px 16px !important;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
        }

        .dataTables_wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .dataTables_wrapper .row:first-child,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_paginate {
            display: none !important;
        }

        .dataTables_wrapper .dataTables_info {
            color: #64748b;
            font-size: 12px;
            padding-top: 10px;
        }

        table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0;
            width: 100% !important;
            margin: 0 !important;
            white-space: nowrap;
        }

        .table-responsive {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow-x: auto !important;
            overflow-y: visible !important;
            width: 100%;
            max-width: 100%;
            background: #ffffff;
            -webkit-overflow-scrolling: touch;
        }

        #iafTable thead th {
            background: #0f2744 !important;
            color: #ffffff !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 11px 14px;
            border-top: none;
            border-bottom: 2px solid #08192c;
            vertical-align: middle;
            position: relative;
            z-index: 10;
            will-change: transform;
        }

        table.dataTable tbody td {
            height: 44px;
            padding: 9px 14px;
            vertical-align: middle;
            color: #334155;
            font-size: 12.5px;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
        }

        table.dataTable tbody tr {
            transition: background-color 0.15s ease;
        }

        table.dataTable tbody tr:hover td {
            background-color: #f8fafc;
            color: #0f172a;
        }

        #iafTable th,
        #iafTable td {
            white-space: nowrap;
        }

        .status-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 22px;
            line-height: 1;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-active {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .status-pending {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .status-expired {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .status-default {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .page-item.active .page-link {
            background-color: #0d4d8b;
            border-color: #0d4d8b;
            border-radius: 8px;
        }

        .page-link {
            color: #475569;
            border: 1px solid transparent;
            margin: 0 2px;
            border-radius: 8px;
        }

        .page-link:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .company-name {
            font-weight: 700;
            color: #0f172a;
        }

        .company-link {
            text-decoration: none;
            color: #0d4d8b;
            font-weight: 700;
            transition: color 0.15s ease;
        }

        .company-link:hover {
            color: #11599e;
            text-decoration: underline;
        }

        .cert-number {
            font-family: Consolas, Monaco, monospace;
            background: #eff6ff;
            padding: 3px 8px;
            border-radius: 6px;
            color: #0d4d8b;
            font-weight: 700;
            font-size: 11.5px;
            border: 1px solid #bfdbfe;
        }

        .action-btns-container {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn {
            min-height: 28px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .btn-insert {
            background-color: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }

        .btn-insert:hover {
            background-color: #c7d2fe;
            box-shadow: 0 4px 10px rgba(55, 48, 163, 0.15);
            transform: translateY(-1px);
        }

        .btn-update {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .btn-update:hover {
            background-color: #bbf7d0;
            box-shadow: 0 4px 10px rgba(22, 101, 52, 0.15);
            transform: translateY(-1px);
        }

        .btn-delete {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .btn-delete:hover {
            background-color: #fecaca;
            box-shadow: 0 4px 10px rgba(153, 27, 27, 0.15);
            transform: translateY(-1px);
        }

        .action-btn i {
            margin-right: 4px;
        }

        .action-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .cell-ellipsis {
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 991px) {
            .iaf-dashboard-page .container-fluid {
                padding: 8px 10px 10px !important;
            }

            .page-header-container {
                min-height: auto;
                padding: 14px 16px;
            }

            .modern-card {
                padding: 12px;
            }
        }

        @media (max-width: 767px) {
            .page-title {
                font-size: 18px;
            }

            .page-subtitle {
                font-size: 11.5px;
            }

            .iaf-table-toolbar {
                align-items: stretch;
                flex-direction: column;
                gap: 8px;
            }

            .client-filter-input,
            .iaf-filter-group,
            .client-filter-field,
            .status-filter-select {
                width: 100%;
            }
        }

        /* =====================================================================
           PREMIUM DARK MODE — iaf_dashboard.php
           ===================================================================== */
        html.theme-dark {
            --iafd-bg:        #0a0e14;
            --iafd-surface:   #111722;
            --iafd-surface-2: #161d2b;
            --iafd-border:    #232c3d;
            --iafd-border-2:  #2e3a4f;
            --iafd-text:      #dbe3f0;
            --iafd-text-dim:  #93a1b8;
            --iafd-text-faint:#6b7890;
            --iafd-accent:    #4a9eff;
        }

        html.theme-dark body { background-color: var(--iafd-bg); color: var(--iafd-text); }
        html.theme-dark #page-content-wrapper.iaf-dashboard-page {
            background: #0a0e14;
        }

        html.theme-dark .page-header-container {
            background: linear-gradient(135deg, #091e36 0%, #0d2f54 100%);
            border-color: #1e3a5f;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
        }

        html.theme-dark .modern-card {
            background: var(--iafd-surface);
            border-color: var(--iafd-border);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }

        html.theme-dark .client-filter-label { color: #7aa9f5; }
        html.theme-dark .client-filter-input {
            background: var(--iafd-surface-2) !important;
            border-color: var(--iafd-border-2) !important;
            color: var(--iafd-text) !important;
        }
        html.theme-dark .client-filter-input::placeholder { color: var(--iafd-text-faint); }
        html.theme-dark .client-filter-input:focus {
            border-color: var(--iafd-accent) !important;
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.18) !important;
        }
        html.theme-dark .status-filter-select {
            background: var(--iafd-surface-2) !important;
            border-color: var(--iafd-border-2) !important;
            color: var(--iafd-text) !important;
        }
        html.theme-dark .status-filter-select:focus {
            border-color: var(--iafd-accent) !important;
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.18) !important;
        }
        html.theme-dark .client-filter-icon { color: var(--iafd-text-faint); }
        html.theme-dark .client-filter-clear {
            background: #263043;
            color: #b8c4d8;
        }
        html.theme-dark .client-filter-clear:hover { background: #313d54; }

        html.theme-dark .table-responsive {
            background: var(--iafd-surface);
            border-color: var(--iafd-border);
        }
        html.theme-dark table.dataTable thead th {
            background: #090e17 !important;
            color: #aeb9cc !important;
            border-bottom: 1px solid var(--iafd-border-2);
        }
        html.theme-dark table.dataTable tbody td {
            background: var(--iafd-surface);
            color: var(--iafd-text);
            border-bottom-color: var(--iafd-border);
        }
        html.theme-dark table.dataTable tbody tr:hover td {
            background-color: var(--iafd-surface-2);
            color: #f1f5f9;
        }
        html.theme-dark table.dataTable td.dataTables_empty { color: var(--iafd-text-faint); }
        html.theme-dark .dataTables_wrapper .dataTables_info { color: var(--iafd-text-dim); }

        html.theme-dark .company-name { color: #e5eaf2; }
        html.theme-dark .company-link { color: #7aa9f5; }
        html.theme-dark .company-link:hover { color: #93c5fd; }
        html.theme-dark .cert-number {
            background: rgba(37, 99, 235, 0.16);
            color: #93c5fd;
            border-color: rgba(37, 99, 235, 0.3);
        }

        html.theme-dark .status-active {
            background-color: rgba(22, 163, 74, 0.16);
            color: #6ee7b7;
            border-color: rgba(22, 163, 74, 0.32);
        }
        html.theme-dark .status-pending {
            background-color: rgba(56, 189, 248, 0.15);
            color: #7dd3fc;
            border-color: rgba(56, 189, 248, 0.3);
        }
        html.theme-dark .status-expired {
            background-color: rgba(220, 38, 38, 0.16);
            color: #fca5a5;
            border-color: rgba(220, 38, 38, 0.32);
        }
        html.theme-dark .status-default {
            background-color: rgba(100, 116, 139, 0.16);
            color: #cbd5e1;
            border-color: rgba(100, 116, 139, 0.3);
        }

        html.theme-dark .btn-insert {
            background-color: rgba(67, 56, 202, 0.2);
            color: #a5b4fc;
            border-color: rgba(99, 102, 241, 0.34);
        }
        html.theme-dark .btn-insert:hover { background-color: rgba(67, 56, 202, 0.32); }
        html.theme-dark .btn-update {
            background-color: rgba(22, 163, 74, 0.18);
            color: #6ee7b7;
            border-color: rgba(22, 163, 74, 0.32);
        }
        html.theme-dark .btn-update:hover { background-color: rgba(22, 163, 74, 0.28); }
        html.theme-dark .btn-delete {
            background-color: rgba(220, 38, 38, 0.16);
            color: #fca5a5;
            border-color: rgba(220, 38, 38, 0.32);
        }
        html.theme-dark .btn-delete:hover { background-color: rgba(220, 38, 38, 0.26); }

        html.theme-dark .iaf-page-info { color: var(--iafd-text-dim); }
        html.theme-dark .iaf-page-btn {
            background: var(--iafd-surface-2);
            border-color: var(--iafd-border-2);
            color: var(--iafd-text);
        }
        html.theme-dark .iaf-page-btn:hover:not(:disabled):not(.current) {
            background: #1c2942;
            border-color: #2f4266;
            color: #ffffff;
        }
        html.theme-dark .iaf-page-btn.current {
            background: var(--iafd-accent);
            border-color: var(--iafd-accent);
            color: #061020;
        }
        html.theme-dark .iaf-page-ellipsis { color: var(--iafd-text-faint); }

        html.theme-dark .iaf-loading-overlay { background: rgba(10, 14, 20, 0.85); }
        html.theme-dark .iaf-loading-text { color: var(--iafd-text); }
        html.theme-dark .iaf-spinner {
            border-color: #263043;
            border-top-color: var(--iafd-accent);
        }
    </style>

    <div id="page-content-wrapper" class="iaf-dashboard-page">
        <div class="page-content inset">
            <div class="container-fluid" style="padding: 0 30px;">
                <div class="page-header-container">
                    <div>
                        <h1 class="page-title">IAF Dashboard</h1>
                        <p class="page-subtitle">Comprehensive Overview of Client Certifications</p>
                    </div>
                </div>

                <div class="modern-card">
                    <div class="iaf-table-toolbar">
                        <div class="iaf-filter-group">
                            <label for="iafStatusFilter" class="client-filter-label">Select by Status</label>
                            <select id="iafStatusFilter" class="status-filter-select">
                                <option value="">All Status</option>
                                <?php foreach ($iafStatusOptions as $statusOption): ?>
                                    <option value="<?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($statusOption); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="iaf-filter-group">
                            <label for="clientNameFilter" class="client-filter-label">Client Name</label>
                            <div class="client-filter-field">
                                <i class="fa fa-search client-filter-icon"></i>
                                <input type="text" id="clientNameFilter" class="client-filter-input"
                                    placeholder="Search by client name" autocomplete="off">
                                <button type="button" class="client-filter-clear" id="clientFilterClear"
                                    aria-label="Clear search" title="Clear">&times;</button>
                            </div>
                        </div>
                    </div>
                    <div class="iaf-table-wrap">
                        <div class="table-responsive">
                            <table id="iafTable" class="table table-hover nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Company Name</th>
                                    <th>Country</th>
                                    <th>Address</th>
                                    <th>Accreditation Body</th>
                                    <th>Standard</th>
                                    <th>Scheme</th>
                                    <th>Scope</th>
                                    <th>Cert. Number</th>
                                    <th>Status</th>
                                    <th>Cert. Type</th>
                                    <th>CDC Date</th>
                                    <th>Issue Date</th>
                                    <th>Expire Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $c): ?>
                                    <?php echo iafDashboardRenderRow($c); ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                        <div class="iaf-pagination" id="iafPagination">
                            <div class="iaf-page-info" id="iafPageInfo"></div>
                            <div class="iaf-page-controls">
                                <button type="button" class="iaf-page-btn" id="iafPrevBtn">Previous</button>
                                <button type="button" class="iaf-page-btn" id="iafNextBtn">Next</button>
                            </div>
                        </div>
                        <div class="iaf-loading-overlay" id="iafLoadingOverlay">
                            <div class="iaf-spinner"></div>
                            <div class="iaf-loading-text" id="iafLoadingText">Loading clients…</div>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

            <script>
                $(document).ready(function () {
                    var table = $('#iafTable').DataTable({
                        autoWidth: false,
                        paging: false,
                        info: false,
                        ordering: true,
                        searching: false,
                        dom: 'rt<"row"<"col-sm-12"i>>',
                        order: [[1, 'asc']],
                        language: {
                            emptyTable: 'No clients found',
                            zeroRecords: 'No clients found'
                        }
                    });
                    var iafOffset = 0;
                    var iafNextOffset = <?php echo (int) $iafInitialNextOffset; ?>;
                    var iafLimit = <?php echo (int) $iafInitialLimit; ?>;
                    var iafCurrentPage = 1;
                    var iafPageHistory = [0];
                    var iafPageHistoryIndex = 0;
                    var iafHasMore = <?php echo $iafInitialHasMore ? 'true' : 'false'; ?>;
                    var iafTotalCount = <?php echo (int) $iafInitialTotalCount; ?>;
                    var iafLoading = false;
                    var iafSearchTimer = null;
                    var iafLoadToken = 0;   // bumped on every search/reset to discard stale in-flight responses

                    var $overlay = $('#iafLoadingOverlay');
                    var $overlayText = $('#iafLoadingText');
                    var $filter = $('#clientNameFilter');
                    var $filterField = $('.client-filter-field');
                    var $filterClear = $('#clientFilterClear');
                    var $statusFilter = $('#iafStatusFilter');
                    var $pageInfo = $('#iafPageInfo');
                    var $prevBtn = $('#iafPrevBtn');
                    var $nextBtn = $('#iafNextBtn');

                    function applyInsertedButtonState(context) {
                        $(context).find('.btn-insert').addBack('.btn-insert').each(function () {
                            const btnId = $(this).attr('id');
                            if (btnId && localStorage.getItem(btnId) === 'inserted') {
                                $(this).prop('disabled', true);
                                $(this).html('<i class="fa fa-check"></i> Inserted');
                                $(this).css({ 'background-color': '#f1f5f9', 'color': '#94a3b8', 'border-color': '#e2e8f0' });
                            }
                        });
                    }

                    function updatePaginationUi(returnedRows) {
                        var fromRow = returnedRows > 0 ? (iafOffset + 1) : 0;
                        var toRow = iafOffset + returnedRows;
                        var baseInfo = returnedRows > 0
                            ? ('Showing ' + fromRow + ' to ' + toRow + ' of ' + iafTotalCount + ' total clients (Page ' + iafCurrentPage + ')')
                            : ('No clients found (Page ' + iafCurrentPage + ')');

                        $pageInfo.text(baseInfo);
                        $prevBtn.prop('disabled', iafPageHistoryIndex === 0 || iafLoading);
                        $nextBtn.prop('disabled', !iafHasMore || iafLoading);
                    }

                    function loadIafRows(options) {
                        var opts = options || {};
                        var reset = !!opts.reset;
                        var targetOffset = typeof opts.offset === 'number' ? opts.offset : 0;
                        var targetPage = typeof opts.page === 'number' ? opts.page : 1;
                        var historyOffset = typeof opts.historyOffset === 'number' ? opts.historyOffset : null;

                        if (iafLoading) {
                            return;
                        }

                        if (reset) {
                            iafLoadToken++;
                            iafOffset = 0;
                            iafNextOffset = 0;
                            iafCurrentPage = 1;
                            iafPageHistory = [0];
                            iafPageHistoryIndex = 0;
                            iafHasMore = true;
                            table.clear().draw();
                            $overlayText.text(($filter.val().trim() || $statusFilter.val()) ? 'Filtering clients…' : 'Loading clients…');
                            $overlay.addClass('active');
                        } else {
                            $overlayText.text('Loading page...');
                            $overlay.addClass('active');
                        }

                        var token = iafLoadToken;
                        iafLoading = true;
                        updatePaginationUi(table.rows().count());

                        $.ajax({
                            url: 'iaf_dashboard_data.php',
                            type: 'GET',
                            dataType: 'json',
                            data: {
                                offset: targetOffset,
                                limit: iafLimit,
                                search: $filter.val(),
                                status: $statusFilter.val()
                            },
                            success: function (response) {
                                if (token !== iafLoadToken) {
                                    return; // a newer search superseded this request
                                }
                                if (response.status !== 'success') {
                                    console.error(response.message || 'IAF dashboard load failed.');
                                    return;
                                }

                                var nodes = $($.parseHTML(response.rows_html || '', document, true)).filter('tr');
                                table.clear();

                                if (nodes.length) {
                                    table.rows.add(nodes).draw(false);
                                    applyInsertedButtonState($(nodes));
                                    table.columns.adjust();
                                } else {
                                    table.draw(false);
                                }

                                iafOffset = targetOffset;
                                iafCurrentPage = targetPage;
                                if (historyOffset !== null) {
                                    iafPageHistory = iafPageHistory.slice(0, iafPageHistoryIndex + 1);
                                    iafPageHistory.push(historyOffset);
                                    iafPageHistoryIndex = iafPageHistory.length - 1;
                                }

                                iafNextOffset = parseInt(response.next_offset, 10) || (iafOffset + nodes.length);
                                iafHasMore = !!response.has_more;
                                iafTotalCount = parseInt(response.total_count, 10) || 0;
                                updatePaginationUi(nodes.length);
                            },
                            complete: function () {
                                if (token !== iafLoadToken) {
                                    return; // stale; the newer request owns the UI state
                                }
                                iafLoading = false;
                                $overlay.removeClass('active');
                                updatePaginationUi(table.rows().count());
                            },
                            error: function () {
                                if (token !== iafLoadToken) {
                                    return;
                                }
                                iafLoading = false;
                                $overlay.removeClass('active');
                                updatePaginationUi(table.rows().count());
                                console.error('IAF dashboard data request failed.');
                            }
                        });
                    }

                    function syncFilterUi() {
                        $filterField.toggleClass('has-value', $filter.val().trim().length > 0);
                    }

                    $filter.on('input', function () {
                        syncFilterUi();
                        clearTimeout(iafSearchTimer);
                        iafSearchTimer = setTimeout(function () {
                            loadIafRows({ reset: true, offset: 0, page: 1 });
                        }, 350);
                    });

                    $filter.on('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            clearTimeout(iafSearchTimer);
                            loadIafRows({ reset: true, offset: 0, page: 1 });
                        } else if (e.key === 'Escape' && $filter.val()) {
                            e.preventDefault();
                            $filterClear.trigger('click');
                        }
                    });

                    $filterClear.on('click', function () {
                        $filter.val('');
                        syncFilterUi();
                        $filter.trigger('focus');
                        clearTimeout(iafSearchTimer);
                        loadIafRows({ reset: true, offset: 0, page: 1 });
                    });

                    $statusFilter.on('change', function () {
                        clearTimeout(iafSearchTimer);
                        loadIafRows({ reset: true, offset: 0, page: 1 });
                    });

                    $prevBtn.on('click', function () {
                        if (iafLoading || iafPageHistoryIndex === 0) {
                            return;
                        }
                        var previousIndex = iafPageHistoryIndex - 1;
                        var previousOffset = iafPageHistory[previousIndex];
                        iafPageHistoryIndex = previousIndex;
                        loadIafRows({
                            reset: false,
                            offset: previousOffset,
                            page: previousIndex + 1
                        });
                    });

                    $nextBtn.on('click', function () {
                        if (iafLoading || !iafHasMore) {
                            return;
                        }
                        loadIafRows({
                            reset: false,
                            offset: iafNextOffset,
                            page: iafCurrentPage + 1,
                            historyOffset: iafNextOffset
                        });
                    });

                    // Toggle Menu
                    $("#menu-toggle").click(function (e) {
                        e.preventDefault();
                        $("#wrapper").toggleClass("active");
                        $(".container-fluid").toggleClass("active");
                        setTimeout(function () {
                            table.columns.adjust().draw();
                        }, 300);
                    });

                    applyInsertedButtonState($('#iafTable tbody'));
                    syncFilterUi();
                    table.columns.adjust().draw(false);
                    updatePaginationUi(table.rows().count());
                });
                function handleAction(actionType, clientData, btnElement) {
                    const btn = $(btnElement);
                    const originalText = btn.html();
                    const row = btn.closest('tr');

                    // For update and delete, clientData is just the string ID. For insert, it's the full object.
                    const clientId = typeof clientData === 'object' ? clientData.internal_client_id : clientData;

                    // Config based on action
                    let confirmTitle = '';
                    let confirmText = '';
                    let confirmButtonText = '';
                    let confirmColor = '';
                    let ajaxData = { action: actionType };

                    if (actionType === 'delete') {
                        confirmTitle = 'Delete client from IAF CertSearch?';
                        confirmText = `Are you sure you want to delete client #${clientId} from the IAF CertSearch portal? This will permanently remove the certified entity and all of its certifications from IAF. This action cannot be undone.`;
                        confirmButtonText = 'Yes, delete from IAF';
                        confirmColor = '#d33';
                        ajaxData.client_id = clientId;
                    } else if (actionType === 'update') {
                        confirmTitle = 'Check for Updates?';
                        confirmText = `Check if client #${clientId} details have been updated in the database?`;
                        confirmButtonText = 'Yes, Check Update';
                        confirmColor = '#3085d6';
                        ajaxData.client_id = clientId;
                        ajaxData.client_data = clientData; // Send full data to verify for changes
                    } else if (actionType === 'insert') {
                        confirmTitle = 'Insert / Send Data?';
                        confirmText = `Do you want to send and insert full data for client #${clientId} via API?`;
                        confirmButtonText = 'Yes, Send Full Data';
                        confirmColor = '#4338ca';
                        ajaxData.client_data = clientData; // Send the complete object array
                        ajaxData.client_id = clientId;
                    }

                    Swal.fire({
                        title: confirmTitle,
                        text: confirmText,
                        icon: actionType === 'delete' ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: confirmColor,
                        cancelButtonColor: '#aaa',
                        confirmButtonText: confirmButtonText
                    }).then((result) => {
                        if (result.isConfirmed) {

                            // Loading State Setup
                            btn.prop('disabled', true);
                            btn.html('<i class="fa fa-spinner fa-spin"></i> Processing');

                            // Perform AJAX Call to Local Mock API
                            $.ajax({
                                url: 'iaf_action_api.php',
                                type: 'POST',
                                data: ajaxData,
                                dataType: 'json',
                                success: function (response) {

                                    if (response.status === 'success' || response.status === 'info') {
                                        // Use provided Custom Title if 'response.title' exists
                                        const popupTitle = response.title ? response.title : (response.status === 'success' ? 'Success!' : 'Information');

                                        Swal.fire({
                                            title: popupTitle,
                                            text: response.message,
                                            icon: response.status,
                                            showConfirmButton: true,
                                            confirmButtonColor: '#1e3a8a',
                                            timer: response.is_updated ? 2500 : null
                                        });

                                        // Handle Post-Success Button UI states
                                        if (actionType === 'insert' && response.status === 'success') {
                                            // Keep it disabled and mark as inserted
                                            btn.html('<i class="fa fa-check"></i> Inserted');
                                            btn.css({ 'background-color': '#f1f5f9', 'color': '#94a3b8', 'border-color': '#e2e8f0' });

                                            // Save to localStorage so it stays disabled even after page reload
                                            const btnId = btn.attr('id') || `btn-insert-${clientId}`;
                                            if (btnId) localStorage.setItem(btnId, 'inserted');

                                        } else if (actionType === 'delete' && response.status === 'success') {
                                            const table = $('#iafTable').DataTable();
                                            table.row(row).remove().draw();
                                        } else if (actionType === 'update' && response.status === 'success' && response.is_updated) {
                                            // Clear the inserted status so it can be re-sent!
                                            // Use the actual button id (service-aware) from the same row's Insert button
                                            const rowInsertBtn = row.find('.btn-insert');
                                            const rowInsertBtnId = rowInsertBtn.attr('id');
                                            if (rowInsertBtnId) localStorage.removeItem(rowInsertBtnId);

                                            // Reload the page to bring the new fresh data to the table and re-enable the Insert button naturally
                                            setTimeout(function () {
                                                location.reload();
                                            }, 1500); // 1.5 second delay so user can read the success message
                                        } else {
                                            // Re-enable for other actions/info
                                            btn.prop('disabled', false);
                                            btn.html(originalText);
                                        }


                                    } else {
                                        Swal.fire(
                                            'Error!',
                                            response.message || 'Something went wrong.',
                                            'error'
                                        );
                                    }
                                },
                                error: function () {
                                    btn.prop('disabled', false);
                                    btn.html(originalText);
                                    Swal.fire(
                                        'Failed!',
                                        'Server communication failed. Check your connection.',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                }

                function syncStickyHeader() {
                    var $table = $('#iafTable');
                    if (!$table.length) return;
                    var navHeight = 72;
                    var tableTop = $table.offset().top;
                    var scrollTop = $(window).scrollTop();
                    var tableHeight = $table.outerHeight();
                    var theadHeight = $table.find('thead').outerHeight() || 40;
                    var diff = scrollTop + navHeight - tableTop;

                    if (diff > 0 && diff < (tableHeight - theadHeight - 40)) {
                        $table.find('thead th').css({
                            'transform': 'translateY(' + diff + 'px)',
                            'box-shadow': '0 3px 6px rgba(15, 39, 68, 0.25)'
                        });
                    } else {
                        $table.find('thead th').css({
                            'transform': 'translateY(0px)',
                            'box-shadow': 'none'
                        });
                    }
                }

                $(window).on('scroll resize', syncStickyHeader);
            </script>

        </div> <!-- /.container-fluid -->
    </div> <!-- /.page-content -->
</div> <!-- /#page-content-wrapper -->
</div> <!-- /#wrapper -->

<!-- Scripts already loaded by header/head, but keep these if unique -->

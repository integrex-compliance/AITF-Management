<div id="wrapper" class="active">
<?php
$pageIdentifier = "TDS list";
include_once './dbConnect.php';
include_once './header.php';
require_once __DIR__ . '/include/payment_service_names.php';
date_default_timezone_set('Asia/Kolkata');

if (function_exists('aitf_auto_sync_schema')) {
    aitf_auto_sync_schema($db);
}
if (function_exists('aitf_ensure_tds_columns')) {
    aitf_ensure_tds_columns($db);
}

$tdsRole = trim($_SESSION['role'] ?? '');
$isMarketingHead = in_array($tdsRole, array('Marketing Head', 'Marketing Executive'), true)
    || strtolower(str_replace(' ', '', $tdsRole)) === 'marketinghead'
    || (isset($_SESSION['username']) && strtolower(trim($_SESSION['username'])) === 'marketingh');
$isOfficeStaff = in_array($tdsRole, array('Office Staff-A', 'Office Staff-B'), true)
    || in_array(strtolower(str_replace(array(' ', '-'), '', $tdsRole)), array('officestaffa', 'officestaffb'), true);
$isRestrictedRole = $isMarketingHead || $isOfficeStaff;

$canVerifyTds = (
    (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserAdmin() || Utitlity::checkForUserFinance()))
    || in_array(trim($tdsRole), array('Top Mgmt', 'Admin', 'Finance', 'Director', 'Management'), true)
    || (isset($_SESSION['username']) && in_array(strtolower(trim($_SESSION['username'])), array('admin', 'mainadmin', 'director', 'finance'), true))
);

$status = $_GET['status'] ?? 'all';
$startDateStr = $_GET['startDate'] ?? '';
$endDateStr = $_GET['endDate'] ?? '';
$clientSearch = trim($_GET['clientSearch'] ?? '');
$sortOrder = $_GET['sortOrder'] ?? '';

$startDate = '';
$endDate = '';

if (!empty($startDateStr)) {
    // dd.mm.yy to Y-m-d
    $parts = explode('.', $startDateStr);
    if(count($parts) == 3) {
        $startDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
}
if (!empty($endDateStr)) {
    // dd.mm.yy to Y-m-d
    $parts = explode('.', $endDateStr);
    if(count($parts) == 3) {
        $endDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
}

$listOfTds = [];

$allTds = "SELECT ci.id, customer_name, ci.unique_id, ps.customer_id, ps.service_type as tds, ps.service_type, ps.payment_header, 
           COALESCE(NULLIF(ps.tds_amount, 0), ps.amount_decided, ps.payment_received) as amount_decided,
           ps.tds_received,
           ps.payment_date, ps.tds_received_date, ps.credit_date_booking, ps.tan_of_deductor, ps.comment, ps.payment_id, ps.tds_verified, svc.service_id, svc.service_type as svc_service_type, 0 as is_statement
           FROM customer_info ci
           left join payment_system ps ON ci.id=ps.customer_id
           left join service svc on ps.customer_id = svc.customer_id
           WHERE (svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) OR svc.service_id IS NULL)
           and (ps.service_type LIKE '%tds%' OR (ps.tds_amount IS NOT NULL AND ps.tds_amount > 0))";

$params = [];
if ($status == 'received') {
    $allTds .= " AND (ps.tds_received IS NOT NULL AND ps.tds_received > 0)";
} elseif ($status == 'not_received') {
    $allTds .= " AND (ps.tds_received IS NULL OR ps.tds_received = 0 OR ps.tds_received = '' OR ps.tds_received = '0.00' OR ps.tds_received = '0')";
}

if ($sortOrder == 'high') {
    $allTds .= " ORDER BY CAST(COALESCE(NULLIF(ps.tds_amount, 0), ps.amount_decided, ps.payment_received) AS DECIMAL(10,2)) DESC";
} elseif ($sortOrder == 'low') {
    $allTds .= " ORDER BY CAST(COALESCE(NULLIF(ps.tds_amount, 0), ps.amount_decided, ps.payment_received) AS DECIMAL(10,2)) ASC";
} else {
    $allTds .= " ORDER BY ci.id DESC";
}

try {
    $statement = $db->prepare($allTds);
    foreach ($params as $key => $val) {
        $statement->bindValue($key, $val);
    }
    $statement->execute();
    $listOfTds = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If column is missing, ensure schema and retry, or gracefully fallback
    if (function_exists('aitf_ensure_tds_columns')) {
        aitf_ensure_tds_columns($db);
    }
    try {
        $statement = $db->prepare($allTds);
        foreach ($params as $key => $val) {
            $statement->bindValue($key, $val);
        }
        $statement->execute();
        $listOfTds = $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        // Fallback without ps.tds_verified or ps.tds_amount
        $fallbackTds = "SELECT ci.id, customer_name, ci.unique_id, ps.customer_id, ps.service_type as tds, ps.service_type, ps.payment_header, 
                       ps.amount_decided, ps.tds_received, ps.payment_date, ps.tds_received_date, ps.credit_date_booking, ps.tan_of_deductor, ps.comment, ps.payment_id,
                       'unverified' as tds_verified, svc.service_id, svc.service_type as svc_service_type, 0 as is_statement
                       FROM customer_info ci
                       left join payment_system ps ON ci.id=ps.customer_id
                       left join service svc on ps.customer_id = svc.customer_id
                       WHERE (svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) OR svc.service_id IS NULL)
                       and ps.service_type LIKE '%tds%'";
        if ($status == 'received') {
            $fallbackTds .= " AND (ps.tds_received > 0)";
        } elseif ($status == 'not_received') {
            $fallbackTds .= " AND (ps.tds_received IS NULL OR ps.tds_received = 0 OR ps.tds_received = '' OR ps.tds_received = '0.00' OR ps.tds_received = '0')";
        }
        $fallbackTds .= " ORDER BY ci.id DESC";
        try {
            $fbStmt = $db->prepare($fallbackTds);
            $fbStmt->execute();
            $listOfTds = $fbStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e3) {
            $listOfTds = [];
        }
    }
}


// Also fetch TDS from new Statement system (statement_payments where tds_decided > 0)
$newTdsQuery = "SELECT ci.id, ci.customer_name, ci.unique_id, sp.customer_id, 
                sp.service_type as tds, sp.service_type, '' as payment_header,
                sp.tds_decided as amount_decided, sp.tds_received,
                sp.payment_date, sp.tds_received_date, sp.credit_date_booking, sp.tan_of_deductor, sp.comment, sp.id as payment_id, sp.tds_verified,
                0 as service_id, '' as svc_service_type, 1 as is_statement
                FROM customer_info ci
                INNER JOIN statement_payments sp ON ci.id = sp.customer_id
                WHERE sp.tds_decided > 0";
$newTdsParams = [];
if ($status == 'received') {
    $newTdsQuery .= " AND sp.tds_received > 0";
} elseif ($status == 'not_received') {
    $newTdsQuery .= " AND (sp.tds_received IS NULL OR sp.tds_received = 0)";
}
if (!empty($clientSearch)) {
    $newTdsQuery .= " AND ci.customer_name LIKE :clientSearch2";
    $newTdsParams[':clientSearch2'] = '%' . $clientSearch . '%';
} else {
    if ($isRestrictedRole && empty($startDate) && empty($endDate)) {
        $newTdsQuery .= " AND sp.payment_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')";
    }
}
if (!empty($startDate)) {
    $newTdsQuery .= " AND sp.payment_date >= :startDate2";
    $newTdsParams[':startDate2'] = $startDate;
}
if (!empty($endDate)) {
    $newTdsQuery .= " AND sp.payment_date <= :endDate2";
    $newTdsParams[':endDate2'] = $endDate;
}
$newTdsQuery .= " ORDER BY sp.id DESC";

try {
    $stmtNew = $db->prepare($newTdsQuery);
    foreach ($newTdsParams as $key => $val) {
        $stmtNew->bindValue($key, $val);
    }
    $stmtNew->execute();
    $newTdsList = $stmtNew->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($newTdsList)) {
        $listOfTds = array_merge($listOfTds, $newTdsList);
    }
} catch (PDOException $e) {
    // If statement_payments table or tds_verified doesn't exist yet, retry after auto-creating or skip gracefully
    try {
        if (function_exists('aitf_ensure_statement_tables')) {
            aitf_ensure_statement_tables($db);
        }
        if (function_exists('aitf_ensure_tds_columns')) {
            aitf_ensure_tds_columns($db);
        }
        $stmtNew = $db->prepare($newTdsQuery);
        foreach ($newTdsParams as $key => $val) {
            $stmtNew->bindValue($key, $val);
        }
        $stmtNew->execute();
        $newTdsList = $stmtNew->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($newTdsList)) {
            $listOfTds = array_merge($listOfTds, $newTdsList);
        }
    } catch (PDOException $e2) {
        // Silently skip if statement_payments is not active
    }
}
?>

    <style>
        /* Base typography & resets */
        #page-content-wrapper {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        /* Section Title Badge */
        .invoice-section-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 16px;
            padding: 8px 18px;
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.2);
            letter-spacing: -0.2px;
        }

        /* Filter Panel Card */
        .invoice-filter-panel {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            margin: 10px 0 20px;
            padding: 14px 18px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }

        .invoice-filter-item {
            flex: 1 1 130px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            position: relative;
        }

        .invoice-filter-item.item-client {
            flex: 1.5 1 180px;
        }

        .invoice-filter-item.item-action {
            flex: 1.2 1 170px;
        }

        .invoice-filter-item label {
            display: block !important;
            margin: 0 0 6px 0 !important;
            padding: 0 !important;
            color: #0d4d8b !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            height: 16px !important;
            line-height: 16px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .invoice-filter-item select, 
        .invoice-filter-item input {
            width: 100% !important;
            height: 40px !important;
            min-height: 40px !important;
            max-height: 40px !important;
            padding: 0 12px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-sizing: border-box !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            background-color: #fff !important;
            line-height: 38px !important;
            margin: 0 !important;
            vertical-align: middle !important;
            transition: all 0.2s ease;
        }

        .invoice-filter-item input::placeholder {
            color: #94a3b8 !important;
        }

        .invoice-filter-item select:focus, 
        .invoice-filter-item input:focus {
            border-color: #0d4d8b !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12) !important;
        }

        .btn-premium,
        .downloadTdsReport {
            width: 100% !important;
            height: 40px !important;
            min-height: 40px !important;
            max-height: 40px !important;
            line-height: 40px !important;
            padding: 0 16px !important;
            font-family: inherit !important;
            font-weight: 700 !important;
            border-radius: 8px !important;
            font-size: 12.5px !important;
            letter-spacing: 0.3px !important;
            white-space: nowrap !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            border: none !important;
            cursor: pointer !important;
            text-transform: none !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }

        .btn-solid-primary {
            background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(13, 77, 139, 0.25);
        }

        .btn-solid-primary:hover {
            background: linear-gradient(135deg, #11599e 0%, #0a467e 100%) !important;
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.35);
            transform: translateY(-1px);
        }

        /* Table Shell & DataTable Styling */
        .table-responsive {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            overflow: visible !important;
        }

        #table_finance_details,
        .dataTables_scrollHeadInner table {
            width: 100% !important;
            min-width: 1240px !important;
            table-layout: fixed !important;
            border-collapse: separate !important;
        }

        .dataTables_scroll {
            width: 100% !important;
            position: relative;
        }

        .dataTables_scrollHead {
            width: 100% !important;
            overflow: hidden !important;
            border: 0 !important;
        }

        .dataTables_scrollHeadInner {
            box-sizing: content-box !important;
            padding-right: 0 !important;
            width: 100% !important;
        }

        .dataTables_scrollBody {
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: auto !important;
            position: relative !important;
            -webkit-overflow-scrolling: touch;
        }

        #table_finance_details thead th,
        .dataTables_scrollHead thead th {
            background: #0f2744 !important;
            color: #ffffff !important;
            padding: 12px 10px !important;
            border: none !important;
            font-size: 11.5px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
        }

        #table_finance_details thead th small,
        #table_finance_details thead th a,
        .dataTables_scrollHead thead th small,
        .dataTables_scrollHead thead th a {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 11.5px !important;
            text-decoration: none !important;
        }

        #table_finance_details tbody td {
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 10px 14px !important;
            font-size: 12.5px !important;
            color: #334155 !important;
            background-color: #ffffff;
            vertical-align: middle !important;
        }

        #table_finance_details tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        #table_finance_details tbody tr:nth-of-type(odd) td {
            background-color: #fcfdfe;
        }

        /* Client Name click style */
        #table_finance_details tbody td:nth-child(1) small {
            font-weight: 700;
            color: #0d4d8b;
            cursor: pointer;
            transition: color 0.15s ease;
            font-size: 12.5px;
        }

        #table_finance_details tbody td:nth-child(1):hover small {
            color: #0284c7;
            text-decoration: underline;
        }

        /* Invoice Header column badge */
        #table_finance_details tbody td:nth-child(2) small {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            display: inline-block;
        }

        /* TDS Deducted Amount */
        #table_finance_details tbody td:nth-child(3) small {
            color: #d97706;
            font-weight: 800;
            font-size: 13px;
        }

        /* Payment Received Amount */
        #table_finance_details tbody td:nth-child(4) small {
            color: #059669;
            font-weight: 800;
            font-size: 13px;
        }

        /* Deduction Date column */
        #table_finance_details tbody td:nth-child(5) small {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 11.5px;
            display: inline-block;
        }

        /* Editable Input Fields (Credit Date / Booking, TAN of Deductor / Ref) */
        #table_finance_details input.saveChanges {
            width: 100% !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 5px 8px !important;
            font-size: 12px !important;
            color: #1e293b !important;
            background: #f8fafc !important;
            height: 32px !important;
            transition: all 0.2s ease;
            outline: none;
            box-sizing: border-box;
        }

        #table_finance_details input.saveChanges:focus {
            background: #ffffff !important;
            border-color: #0d4d8b !important;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12) !important;
        }

        /* Comments Textarea */
        #table_finance_details textarea.saveChanges {
            width: 100% !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 6px 10px !important;
            font-size: 12px !important;
            color: #1e293b !important;
            background: #f8fafc !important;
            resize: vertical !important;
            min-height: 44px;
            line-height: 1.4;
            transition: all 0.2s ease;
            outline: none;
            box-sizing: border-box;
        }

        #table_finance_details textarea.saveChanges:focus {
            background: #ffffff !important;
            border-color: #0d4d8b !important;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12) !important;
        }

        /* =====================================================================
           PREMIUM DARK MODE  —  listOfTds.php
           ===================================================================== */
        html.theme-dark {
            --tds-bg:        #0a0e14;
            --tds-surface:   #111722;
            --tds-surface-2: #161d2b;
            --tds-border:    #232c3d;
            --tds-border-2:  #2e3a4f;
            --tds-text:      #dbe3f0;
            --tds-text-dim:  #93a1b8;
            --tds-text-faint:#6b7890;
            --tds-accent:    #4a9eff;
        }

        html.theme-dark #page-content-wrapper { background: var(--tds-bg); color: var(--tds-text); }

        /* Section title */
        html.theme-dark .invoice-section-title {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
            color: #ffffff;
        }

        /* Filter panel */
        html.theme-dark .invoice-filter-panel {
            background: var(--tds-surface);
            border-color: var(--tds-border);
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark .invoice-filter-item label { color: #7aa9f5; }
        html.theme-dark .invoice-filter-item input,
        html.theme-dark .invoice-filter-item select {
            background-color: var(--tds-surface-2) !important;
            border-color: var(--tds-border-2) !important;
            color: var(--tds-text) !important;
        }
        html.theme-dark .invoice-filter-item input::placeholder { color: var(--tds-text-faint); }
        html.theme-dark .invoice-filter-item input:focus,
        html.theme-dark .invoice-filter-item select:focus {
            border-color: var(--tds-accent);
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.18);
        }

        /* Download button */
        html.theme-dark .btn-solid-primary {
            background: linear-gradient(135deg, #1d63b0 0%, #174e8c 100%) !important;
            box-shadow: 0 4px 14px rgba(20, 104, 179, 0.4);
        }
        html.theme-dark .btn-solid-primary:hover { 
            background: linear-gradient(135deg, #2f81d6 0%, #1d63b0 100%) !important; 
            box-shadow: 0 8px 22px rgba(20, 104, 179, 0.5); 
        }

        /* Table shell */
        html.theme-dark .table-responsive {
            background: var(--tds-surface);
            border: 1px solid var(--tds-border);
            border-radius: 12px;
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark #table_finance_details thead th,
        html.theme-dark .dataTables_scrollHead thead th {
            background: #090e17 !important;
            border-color: var(--tds-border-2) !important;
        }
        html.theme-dark #table_finance_details thead th small,
        html.theme-dark #table_finance_details thead th a,
        html.theme-dark .dataTables_scrollHead thead th small,
        html.theme-dark .dataTables_scrollHead thead th a { color: #f1f5f9 !important; }
        html.theme-dark #table_finance_details small { color: var(--tds-text) !important; }
        html.theme-dark #table_finance_details tbody td {
            border-color: var(--tds-border) !important;
            color: var(--tds-text) !important;
            background-color: var(--tds-surface) !important;
        }
        html.theme-dark #table_finance_details tbody tr:hover td { background-color: var(--tds-surface-2) !important; }
        html.theme-dark .table-striped tbody tr:nth-of-type(odd) td { background-color: rgba(255, 255, 255, 0.02) !important; }
        html.theme-dark #table_finance_details tbody td:nth-child(1) small { color: #38bdf8 !important; }
        html.theme-dark #table_finance_details tbody td:nth-child(2) small { background: rgba(56, 189, 248, 0.15); color: #7dd3fc; }
        html.theme-dark #table_finance_details tbody td:nth-child(3) small { color: #fbbf24 !important; }
        html.theme-dark #table_finance_details tbody td:nth-child(4) small { color: #34d399 !important; }
        html.theme-dark #table_finance_details tbody td:nth-child(5) small { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; }

        /* Comment textarea */
        html.theme-dark #table_finance_details textarea,
        html.theme-dark .saveChanges {
            background-color: var(--tds-surface-2) !important;
            border: 1px solid var(--tds-border-2) !important;
            color: var(--tds-text) !important;
        }
        html.theme-dark .saveChanges:focus {
            border-color: var(--tds-accent) !important;
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.2) !important;
        }

        /* Report Modal Dark Mode */
        html.theme-dark #tds-report-format-chooser > div {
            background: var(--tds-surface) !important;
            border: 1px solid var(--tds-border) !important;
            color: var(--tds-text) !important;
        }
        html.theme-dark #tds-report-format-chooser p { color: var(--tds-text) !important; }
        html.theme-dark .tds-report-email-box label { color: var(--tds-text) !important; }
        html.theme-dark .tds-report-email-input {
            background-color: var(--tds-surface-2) !important;
            border-color: var(--tds-border-2) !important;
            color: var(--tds-text) !important;
        }
    </style>

    <!-- Page content -->
    <div id="page-content-wrapper">
        <!-- Keep all page content within the page-content inset div! -->
        <div class="page-content inset">
            <div class="row">
                <div class="col-md-12">
                    <div class="container-fluid" style="margin-top: 20px;">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 class="invoice-section-title">TDS Deducted & Its Status</h2>
                                <form method="GET" action="listOfTds.php" id="filterForm">
                                    <div class="invoice-filter-panel">
                                        <div class="invoice-filter-item">
                                            <label for="status">Payment Status</label>
                                            <select name="status" id="status" onchange="$('#filterForm').submit()">
                                                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Status</option>
                                                <option value="received" <?php echo $status == 'received' ? 'selected' : ''; ?>>Received</option>
                                                <option value="not_received" <?php echo $status == 'not_received' ? 'selected' : ''; ?>>Not Received</option>
                                            </select>
                                        </div>
                                        <div class="invoice-filter-item item-client">
                                            <label for="clientSearch">Client Name</label>
                                            <input type="text" name="clientSearch" id="clientSearch" value="<?php echo htmlspecialchars($clientSearch); ?>" placeholder="Search Client..." autocomplete="off">
                                        </div>
                                        <div class="invoice-filter-item">
                                            <label for="startDate">From Date</label>
                                            <input type="text" name="startDate" id="startDate" class="datepicker-tds" value="<?php echo $startDateStr; ?>" autocomplete="off" placeholder="DD.MM.YYYY">
                                        </div>
                                        <div class="invoice-filter-item">
                                            <label for="endDate">To Date</label>
                                            <input type="text" name="endDate" id="endDate" class="datepicker-tds" value="<?php echo $endDateStr; ?>" autocomplete="off" placeholder="DD.MM.YYYY">
                                        </div>
                                        <div class="invoice-filter-item">
                                            <label for="sortOrder">Sort By Amount</label>
                                            <select name="sortOrder" id="sortOrder" onchange="$('#filterForm').submit()">
                                                <option value="" <?php echo $sortOrder == '' ? 'selected' : ''; ?>>Default</option>
                                                <option value="high" <?php echo $sortOrder == 'high' ? 'selected' : ''; ?>>High to Low</option>
                                                <option value="low" <?php echo $sortOrder == 'low' ? 'selected' : ''; ?>>Low to High</option>
                                            </select>
                                        </div>
                                        <div class="invoice-filter-item item-action">
                                            <label>Action</label>
                                            <button type="button" class="btn-premium btn-solid-primary downloadTdsReport">
                                                <i class="fa fa-cloud-download" style="font-size: 14px;"></i> Report
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 contact-form">
                                <div class="row clearfix">
                                    <div class="col-md-12 table-responsive">
                                        <table class="table table-bordered table-striped table-hover table-sortable" id="table_finance_details">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <small><a>Client  Name</a></small>
                                                    </th>
                                                    <th>
                                                        <small><a>Invoice <br>Header</a></small>
                                                    </th>
                                                    <th>
                                                        <small><a>Amount <br> Deducted</a></small>
                                                    </th>
                                                    <th>
                                                        <small><a>TDS(26AS) <br>Booked Amt</a></small>
                                                    </th>
                                                    <!-- <th>
                                                        <small><a>TDS Received Date</a></small>
                                                    </th> -->
                                                    <th>
                                                        <small><a>Credit Date<br>/Booking</a></small>
                                                    </th>
                                                    <th>
                                                        <small><a>TAN of <br> Deductor/Ref</a></small>
                                                    </th>
                                                    <!-- <th>
                                                        <small><a>TDS certificate details</a></small>
                                                    </th> -->
                                                    <th style="text-align: center;">
                                                        <small><a>Verify</a></small>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($listOfTds as $tds) { 
                                                    $isTdsVerified = (isset($tds['tds_verified']) && in_array(strtolower(trim($tds['tds_verified'])), ['verified', 'yes', '1']));
                                                    $tdsStatusText = $isTdsVerified ? 'Verified' : 'Verify';
                                                    $isStatementRow = !empty($tds['is_statement']) ? '1' : '0';
                                                    $tdsRecVal = '';
                                                    if (isset($tds['tds_received']) && $tds['tds_received'] !== '' && $tds['tds_received'] !== null && floatval($tds['tds_received']) > 0) {
                                                        $tdsRecVal = number_format(floatval($tds['tds_received']), 2, '.', '');
                                                    }
                                                    $decidedRaw = !empty($tds['amount_decided']) ? floatval(str_replace(',', '', $tds['amount_decided'])) : 0.0;
                                                    $decidedVal = number_format($decidedRaw, 2, '.', '');
                                                ?>
                                                    <tr id='addr0' data-id="<?php echo $tds['customer_id'] ?>" data-unique-id="<?php echo htmlspecialchars($tds['unique_id'] ?? ''); ?>" data-decided="<?php echo $decidedVal; ?>">
                                                        <td  class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $tds['customer_name'] ?></small>
                                                        </td>
                                                        <td   class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo htmlspecialchars(resolveTdsInvoiceHeader($tds)); ?></small>
                                                        </td>
                                                        <td  class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $decidedVal; ?></small>
                                                        </td>
                                                        <td style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <div class="table-edit-control">
                                                                <input type="text" class="form-control input-sm saveChanges tds-received-input" 
                                                                       data-row="<?php echo $tds['payment_id'] ?>" 
                                                                       data-statement="<?php echo $isStatementRow; ?>" 
                                                                       data-column="tds_received" 
                                                                       data-decided="<?php echo $decidedVal; ?>"
                                                                       name="tds_received" 
                                                                       value="<?php echo htmlspecialchars($tdsRecVal); ?>" 
                                                                       placeholder="0.00" 
                                                                       style="height: 32px; font-size: 12.5px; font-weight: 700; color: #0f172a; border: 1.5px solid #cbd5e1; background-color: #ffffff; border-radius: 6px; padding: 4px 8px; width: 100%; text-align: right;">
                                                            </div>
                                                        </td>
                                                        <td style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <div class="table-edit-control">
                                                                <input type="text" class="form-control input-sm saveChanges datepicker-tds-received" 
                                                                       data-row="<?php echo $tds['payment_id'] ?>" 
                                                                       data-statement="<?php echo $isStatementRow; ?>" 
                                                                       data-column="tds_received_date" 
                                                                       name="tds_received_date" 
                                                                       value="<?php 
                                                                           if (!empty($tds['tds_received_date']) && $tds['tds_received_date'] !== '0000-00-00') {
                                                                               echo date('d.m.Y', strtotime($tds['tds_received_date']));
                                                                           } 
                                                                       ?>" 
                                                                       placeholder="DD.MM.YYYY" 
                                                                       autocomplete="off"
                                                                       style="height: 32px; font-size: 12px; border-radius: 6px; padding: 4px 8px; width: 100%;">
                                                            </div>
                                                        </td>
                                                        <td style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <div class="table-edit-control">
                                                                <input type="text" class="form-control input-sm saveChanges datepicker-booking" 
                                                                       data-row="<?php echo $tds['payment_id'] ?>" 
                                                                       data-statement="<?php echo $isStatementRow; ?>" 
                                                                       data-column="credit_date_booking" 
                                                                       name="credit_date_booking" 
                                                                       value="<?php echo htmlspecialchars($tds['credit_date_booking'] ?? ''); ?>" 
                                                                       placeholder="DD.MM.YYYY / Booking" 
                                                                       style="height: 32px; font-size: 12px; border-radius: 6px; padding: 4px 8px; width: 100%;">
                                                            </div>
                                                        </td>
                                                        <td style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <div class="table-edit-control">
                                                                <input type="text" class="form-control input-sm saveChanges" 
                                                                       data-row="<?php echo $tds['payment_id'] ?>" 
                                                                       data-statement="<?php echo $isStatementRow; ?>" 
                                                                       data-column="tan_of_deductor" 
                                                                       name="tan_of_deductor" 
                                                                       value="<?php echo htmlspecialchars($tds['tan_of_deductor'] ?? ''); ?>" 
                                                                       placeholder="TAN / Ref #" 
                                                                       style="height: 32px; font-size: 12px; border-radius: 6px; padding: 4px 8px; width: 100%;">
                                                            </div>
                                                        </td>
                                                        <td data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <div class="table-edit-control">
                                                                <textarea rows="3" class="saveChanges" data-row="<?php echo $tds['payment_id'] ?>" data-statement="<?php echo $isStatementRow; ?>" data-column="comment" name="payComments" value="" placeholder="" ><?php if (isset($tds['comment'])) echo $tds['comment'] ?></textarea>
                                                            </div>
                                                        </td>
                                                        <td style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; text-align: center; vertical-align: middle;">
                                                            <?php if ($canVerifyTds) { ?>
                                                                <button type="button" class="btn btn-xs toggleTdsVerifyBtn <?php echo $isTdsVerified ? 'tds-btn-verified' : 'tds-btn-unverified'; ?>" 
                                                                        data-status="<?php echo $tdsStatusText; ?>" 
                                                                        data-pay-id="<?php echo htmlspecialchars($tds['payment_id'] ?? ''); ?>" 
                                                                        data-statement="<?php echo !empty($tds['is_statement']) ? '1' : '0'; ?>"
                                                                        data-decided="<?php echo $decidedVal; ?>"
                                                                        style="min-width: 84px; height: 26px; font-size: 11.5px; font-weight: 700; border-radius: 4px; padding: 2px 8px; display: inline-flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; transition: all 0.2s ease; <?php echo $isTdsVerified ? 'background-color: #16a34a; border: 1px solid #15803d; color: #ffffff;' : 'background-color: #ef4444; border: 1px solid #dc2626; color: #ffffff;'; ?>"
                                                                        title="Click to toggle: <?php echo $isTdsVerified ? 'Mark as Unverified' : 'Verify TDS against Bank Statement'; ?>">
                                                                    <i class="fa <?php echo $isTdsVerified ? 'fa-check' : 'fa-times'; ?>"></i>
                                                                    <span><?php echo $tdsStatusText; ?></span>
                                                                </button>
                                                            <?php } else { ?>
                                                                <span class="badge <?php echo $isTdsVerified ? 'tds-badge-verified' : 'tds-badge-unverified'; ?>" 
                                                                      style="min-width: 80px; height: 24px; font-size: 11px; font-weight: 700; border-radius: 4px; padding: 4px 8px; display: inline-flex; align-items: center; justify-content: center; gap: 4px; <?php echo $isTdsVerified ? 'background-color: #16a34a; color: #ffffff;' : 'background-color: #ef4444; color: #ffffff;'; ?>"
                                                                      title="<?php echo $isTdsVerified ? 'Verified by Finance / Admin' : 'Unverified'; ?>">
                                                                    <i class="fa <?php echo $isTdsVerified ? 'fa-check' : 'fa-times'; ?>"></i>
                                                                    <span><?php echo $tdsStatusText; ?></span>
                                                                </span>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
<?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div id="tds-report-format-chooser" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.45); z-index:99999;">
    <div style="max-width:440px; margin:10% auto; background:#fff; border-radius:10px; box-shadow:0 10px 40px rgba(0,0,0,0.3); overflow:hidden;">
        <div style="background:#0d4d8b; color:#fff; padding:14px 18px; font-size:15px; font-weight:600;">
            <i class="fa fa-cloud-download" style="margin-right:8px;"></i> Download TDS Report
        </div>
        <div class="tds-report-step tds-report-step-format" style="padding:22px 18px; text-align:center;">
            <p style="font-size:13px; color:#333; margin:0 0 18px;">Which format would you like?</p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <button type="button" class="btn btn-danger tds-report-format-pdf" style="min-width:130px;">
                    <i class="fa fa-file-pdf-o"></i> PDF
                </button>
                <button type="button" class="btn btn-success tds-report-format-excel" style="min-width:130px;">
                    <i class="fa fa-file-excel-o"></i> Excel
                </button>
            </div>
            <div style="margin-top:18px;">
                <button type="button" class="btn btn-default btn-sm tds-report-cancel">Cancel</button>
            </div>
        </div>
        <div class="tds-report-step tds-report-step-action" style="display:none; padding:22px 18px; text-align:center;">
            <p style="font-size:13px; color:#333; margin:0 0 4px;">Selected format: <strong class="tds-report-format-label">Excel</strong></p>
            <p style="font-size:12px; color:#777; margin:0 0 18px;">How would you like to receive it?</p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <button type="button" class="btn btn-primary tds-report-action-download" style="min-width:150px;">
                    <i class="fa fa-download"></i> Download Report
                </button>
                <button type="button" class="btn btn-warning tds-report-action-email" style="min-width:150px;">
                    <i class="fa fa-envelope"></i> Get Report on Email
                </button>
            </div>
            <div class="tds-report-email-box" style="display:none; margin-top:18px; text-align:left;">
                <label style="font-size:12px; font-weight:600; color:#333;">Enter email address</label>
                <input type="email" class="tds-report-email-input form-control" placeholder="name@example.com" style="margin-top:6px;" autocomplete="off">
                <div class="tds-report-email-msg" style="font-size:12px; margin-top:8px; min-height:16px;"></div>
                <button type="button" class="btn btn-success tds-report-email-send" style="width:100%; margin-top:6px;">
                    <i class="fa fa-paper-plane"></i> Get Report
                </button>
            </div>
            <div style="margin-top:18px;">
                <button type="button" class="btn btn-default btn-sm tds-report-step-back">Back</button>
                <button type="button" class="btn btn-default btn-sm tds-report-cancel">Cancel</button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    function debounce(callback, delay) {
        var timer;
        return function () {
            var context = this;
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, delay);
        };
    }

    function normalizeSearchText(val) {
        return (val || "").toString().toLowerCase().replace(/\s+/g, " ").trim();
    }

    function compareMinDates(filterDate, rowDate) {
        if (!filterDate || !rowDate) return true;
        var filterDateSplit = filterDate.split(".");
        var rowDateSplit = rowDate.split(".");
        if (filterDateSplit.length === 3 && rowDateSplit.length === 3) {
            if (parseInt(filterDateSplit[2], 10) < parseInt(rowDateSplit[2], 10)) {
                return true;
            } else if (filterDateSplit[2] === rowDateSplit[2]) {
                if (parseInt(filterDateSplit[1], 10) < parseInt(rowDateSplit[1], 10)) {
                    return true;
                } else if (filterDateSplit[1] === rowDateSplit[1]) {
                    return parseInt(filterDateSplit[0], 10) <= parseInt(rowDateSplit[0], 10);
                }
            }
        }
        return false;
    }

    function compareMaxDates(filterDate, rowDate) {
        if (!filterDate || !rowDate) return true;
        var filterDateSplit = filterDate.split(".");
        var rowDateSplit = rowDate.split(".");
        if (filterDateSplit.length === 3 && rowDateSplit.length === 3) {
            if (parseInt(filterDateSplit[2], 10) > parseInt(rowDateSplit[2], 10)) {
                return true;
            } else if (filterDateSplit[2] === rowDateSplit[2]) {
                if (parseInt(filterDateSplit[1], 10) > parseInt(rowDateSplit[1], 10)) {
                    return true;
                } else if (filterDateSplit[1] === rowDateSplit[1]) {
                    return parseInt(filterDateSplit[0], 10) >= parseInt(rowDateSplit[0], 10);
                }
            }
        }
        return false;
    }

    var isRestrictedRole = <?php echo ($isRestrictedRole) ? 'true' : 'false'; ?>;

    $.fn.DataTable.ext.search.push(
        function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'table_finance_details') {
                return true;
            }

            var clientSearch = ($("#clientSearch").val() || "").trim();
            var fromDate = ($("#startDate").val() || "").trim();
            var toDate = ($("#endDate").val() || "").trim();

            var trElement = settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
            var rowUniqueId = normalizeSearchText($(trElement).attr('data-unique-id') || "");
            var rowClient = normalizeSearchText(data[0] || "");
            var rowServiceType = normalizeSearchText(data[1] || "");
            var dateValue = (data[4] || "").replace(/<[^>]*>/g, "").trim();

            var searchVal = normalizeSearchText(clientSearch);
            var isCurrentOrRecent = true;
            if (dateValue !== "") {
                var parts = dateValue.split(".");
                if (parts.length === 3) {
                    var day = parseInt(parts[0], 10);
                    var month = parseInt(parts[1], 10) - 1;
                    var year = parseInt(parts[2], 10);
                    var entryDate = new Date(year, month, day);

                    var now = new Date();
                    // 1st day of the previous month at 00:00:00
                    var firstDayOfPrevMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1, 0, 0, 0, 0);
                    if (entryDate < firstDayOfPrevMonth) {
                        isCurrentOrRecent = false;
                    }
                }
            }

            // 1. Client Search:
            // When client search has text, show all matching records.
            // When client search is empty, restrict view to current month + past 1 month (or undated/pending entries) for restricted roles.
            if (clientSearch !== "") {
                var isMatch = (rowClient.indexOf(searchVal) !== -1 || rowUniqueId.indexOf(searchVal) !== -1 || rowServiceType.indexOf(searchVal) !== -1);
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
                if (isRestrictedRole) {
                    if (!isCurrentOrRecent) {
                        return false;
                    }
                }
            }

            // 2. Custom From Date and To Date filters
            if (fromDate !== "" || toDate !== "") {
                if (dateValue === "") {
                    return false;
                }
                var isFrom = (fromDate !== "") ? compareMinDates(fromDate, dateValue) : true;
                var isTo = (toDate !== "") ? compareMaxDates(toDate, dateValue) : true;
                if (!isFrom || !isTo) {
                    return false;
                }
            }

            return true;
        }
    );

    $(document).ready(function () {
        var pendingTdsReport = null;
        var pendingTdsFormat = null;

        function getTdsReportParams() {
            return {
                status: $('#status').val(),
                startDate: $('#startDate').val(),
                endDate: $('#endDate').val(),
                clientSearch: $('#clientSearch').val(),
                sortOrder: $('#sortOrder').val()
            };
        }

        function showTdsReportStep(step) {
            $('.tds-report-step').hide();
            if (step === 'action') {
                $('.tds-report-step-action').show();
            } else {
                $('.tds-report-step-format').show();
            }
        }

        function resetTdsChooser() {
            pendingTdsFormat = null;
            $('.tds-report-email-box').hide();
            $('.tds-report-email-input').val('');
            $('.tds-report-email-msg').text('');
            $('.tds-report-email-send').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
            showTdsReportStep('format');
        }

        function openTdsChooser(params) {
            pendingTdsReport = params;
            resetTdsChooser();
            $('#tds-report-format-chooser').css('display', 'block');
        }

        function closeTdsChooser() {
            $('#tds-report-format-chooser').hide();
            pendingTdsReport = null;
            pendingTdsFormat = null;
        }

        function submitTdsReportDownload() {
            if (!pendingTdsReport || !pendingTdsFormat) {
                return;
            }
            var url = (pendingTdsFormat === 'excel') ? 'exportTdsReportExcel.php' : 'exportTdsReport.php';
            var fallbackName = (pendingTdsFormat === 'excel') ? 'TDS_Report.xlsx' : 'TDS_Report.pdf';
            var $btn = $('.tds-report-action-download');
            if (!$btn.data('orig')) { $btn.data('orig', $btn.html()); }
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Preparing...');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.responseType = 'blob';
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                $btn.prop('disabled', false).html($btn.data('orig'));
                if (xhr.status === 200) {
                    var cd = xhr.getResponseHeader('Content-Disposition') || '';
                    var m = /filename="?([^"]+)"?/.exec(cd);
                    var fname = m ? m[1] : fallbackName;
                    var objUrl = URL.createObjectURL(xhr.response);
                    var link = document.createElement('a');
                    link.href = objUrl;
                    link.download = fname;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    setTimeout(function () { URL.revokeObjectURL(objUrl); }, 4000);
                    closeTdsChooser();
                } else {
                    alert('Download failed. Please try again.');
                }
            };
            xhr.onerror = function () {
                $btn.prop('disabled', false).html($btn.data('orig'));
                alert('Download failed. Please try again.');
            };
            xhr.send('status=' + encodeURIComponent(pendingTdsReport.status) +
                '&startDate=' + encodeURIComponent(pendingTdsReport.startDate) +
                '&endDate=' + encodeURIComponent(pendingTdsReport.endDate) +
                '&clientSearch=' + encodeURIComponent(pendingTdsReport.clientSearch) +
                '&sortOrder=' + encodeURIComponent(pendingTdsReport.sortOrder));
        }

        $('.downloadTdsReport').click(function () {
            openTdsChooser(getTdsReportParams());
        });

        $('.tds-report-format-pdf').click(function () {
            pendingTdsFormat = 'pdf';
            $('.tds-report-format-label').text('PDF');
            $('.tds-report-email-box').hide();
            showTdsReportStep('action');
        });

        $('.tds-report-format-excel').click(function () {
            pendingTdsFormat = 'excel';
            $('.tds-report-format-label').text('Excel');
            $('.tds-report-email-box').hide();
            showTdsReportStep('action');
        });

        $('.tds-report-action-download').click(function () {
            submitTdsReportDownload();
        });

        $('.tds-report-action-email').click(function () {
            $('.tds-report-email-msg').text('');
            $('.tds-report-email-box').show();
            $('.tds-report-email-input').focus();
        });

        $('.tds-report-email-send').click(function () {
            if (!pendingTdsReport || !pendingTdsFormat) {
                return;
            }
            var email = $.trim($('.tds-report-email-input').val());
            var $msg = $('.tds-report-email-msg');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $msg.css('color', '#cc0000').text('Please enter a valid email address.');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            $msg.css('color', '#0d4d8b').text('Sending report, please wait...');

            $.post('emailTdsReport.php', {
                status: pendingTdsReport.status,
                startDate: pendingTdsReport.startDate,
                endDate: pendingTdsReport.endDate,
                clientSearch: pendingTdsReport.clientSearch,
                sortOrder: pendingTdsReport.sortOrder,
                format: pendingTdsFormat,
                email: email
            }, null, 'json').done(function (res) {
                if (res && res.success) {
                    $msg.css('color', '#1a7f37').text(res.message || 'Report sent successfully.');
                    setTimeout(closeTdsChooser, 2000);
                } else {
                    $msg.css('color', '#cc0000').text((res && res.message) ? res.message : 'Could not send the email.');
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
                }
            }).fail(function () {
                $msg.css('color', '#cc0000').text('Server error. Please try again.');
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
            });
        });

        $('.tds-report-step-back').click(function () {
            resetTdsChooser();
        });

        $('.tds-report-cancel').click(function () {
            closeTdsChooser();
        });

        $('#tds-report-format-chooser').click(function (e) {
            if (e.target === this) {
                closeTdsChooser();
            }
        });

        var debouncedTdsSearch = debounce(function () {
            table.draw();
        }, 150);

        $('#clientSearch').on('input keyup change', debouncedTdsSearch);
        $('#startDate, #endDate').on('change', function () {
            table.draw();
        });
        $('#filterForm').on('submit', function (e) {
            e.preventDefault();
            table.draw();
        });

        // Use a slight delay to ensure all scripts are fully loaded
        setTimeout(function() {
            if (typeof $.fn.datepicker !== 'undefined') {
                $('.datepicker-tds').datepicker({
                    minDate: '-100Y',
                    maxDate: '+100Y',
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-20:+100",
                    dateFormat: 'dd.mm.yy',
                    onSelect: function() {
                        table.draw();
                    }
                });
                $('.datepicker-booking, .datepicker-tds-received').datepicker({
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-20:+100",
                    dateFormat: 'dd.mm.yy',
                    onSelect: function() {
                        saveData($(this));
                    }
                });
            }
        }, 100);

        var table = $('#table_finance_details').DataTable({
            "scrollY": '70vh',
            "scrollCollapse": true,
            "scrollX": true,
            "autoWidth": false,
            "paging": true,
            "pageLength": 50,
            "ordering": false,
            "lengthChange": false,
            "dom": "tr<'row dt-bottom-row'<'col-sm-6 col-xs-12'i><'col-sm-6 col-xs-12'p>>",
            "columnDefs": [
                { "width": "200px", "targets": 0 },
                { "width": "130px", "targets": 1 },
                { "width": "120px", "targets": 2 },
                { "width": "120px", "targets": 3 },
                { "width": "130px", "targets": 4 },
                { "width": "150px", "targets": 5 },
                { "width": "150px", "targets": 6 },
                { "width": "160px", "targets": 7 },
                { "width": "100px", "targets": 8 }
            ],
            "language": {
                "emptyTable": "No TDS records available in table",
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

        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
            setTimeout(function () {
                table.columns.adjust();
            }, 250);
        });

        // Toggle TDS Verification Status with Acknowledgment Confirmation
        $(document).on('click', '.toggleTdsVerifyBtn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $btn = $(this);
            var currentStatus = $btn.attr('data-status');
            var isCurrentlyVerified = (currentStatus === 'Verified');
            var payId = $btn.attr('data-pay-id');
            var isStatement = $btn.attr('data-statement');
            var $tr = $btn.closest('tr');
            var decided = parseFloat($btn.attr('data-decided') || $tr.attr('data-decided') || 0);
            var $recInput = $tr.find('input[name="tds_received"]');
            var recRaw = $.trim($recInput.val() || '');
            var received = parseFloat(recRaw.replace(/,/g, ''));
            var displayRec = (!isNaN(received) && received > 0) ? ('₹' + received.toFixed(2)) : (recRaw !== '' ? ('₹' + recRaw) : '₹0.00');

            if (!isCurrentlyVerified) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '<span style="font-size: 19px; font-weight: 700; color: #0d4d8b;"><i class="fa fa-shield" style="margin-right: 6px;"></i> TDS Bank Verification</span>',
                        html: '<div style="font-size: 13.5px; line-height: 1.5; color: #1e293b; text-align: left; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 14px 16px; margin: 10px 0 6px; display: flex; align-items: flex-start; gap: 8px;">' +
                              '<i class="fa fa-info-circle" style="color: #0d4d8b; font-size: 16px; margin-top: 2px; flex-shrink: 0;"></i>' +
                              '<span style="font-weight: 600; font-size: 13px; color: #0f172a; line-height: 1.45;">I acknowledge that I have cross checked bank statement and this TDS entry is valid.<br><small style="color: #059669; font-weight: 700; font-size: 12px; display: block; margin-top: 4px;">• TDS Received: ' + displayRec + (decided > 0 ? (' (Decided: ₹' + decided.toFixed(2) + ')') : '') + '</small></span>' +
                              '</div>' +
                              '<div style="font-size: 11.5px; color: #64748b; text-align: left; margin-top: 4px;"><i class="fa fa-lock"></i> Once verified, this TDS entry will become locked and non-editable in the Payment System.</div>',
                        showCancelButton: true,
                        confirmButtonColor: '#16a34a',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="fa fa-check"></i> Confirm & Verify',
                        cancelButtonText: 'Cancel',
                        focusConfirm: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            sendTdsVerifyUpdate($btn, 'Verified', payId, isStatement, recRaw);
                        }
                    });
                } else {
                    if (confirm('I acknowledge that I have cross checked bank statement and this TDS entry is valid.\n\nTDS Received: ' + displayRec + '\n\nClick OK to verify and lock this TDS entry.')) {
                        sendTdsVerifyUpdate($btn, 'Verified', payId, isStatement, recRaw);
                    }
                }
            } else {
                // User wants to mark as Unverified
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '<span style="font-size: 18px; font-weight: 700; color: #dc2626;"><i class="fa fa-exclamation-triangle"></i> Mark as Unverified?</span>',
                        text: 'Are you sure you want to mark this TDS entry as Unverified? This will re-enable editing in the Payment System.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="fa fa-undo"></i> Yes, Mark Unverified',
                        cancelButtonText: 'Cancel'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            sendTdsVerifyUpdate($btn, 'Verify', payId, isStatement);
                        }
                    });
                } else {
                    if (confirm('Are you sure you want to mark this TDS entry as Unverified?')) {
                        sendTdsVerifyUpdate($btn, 'Verify', payId, isStatement);
                    }
                }
            }
        });

        function sendTdsVerifyUpdate($btn, newStatus, payId, isStatement, receivedAmount) {
            $btn.prop('disabled', true).css('opacity', '0.7');

            var postData = {
                action: 'updateTdsVerifyStatus',
                payment_id: payId,
                status: newStatus,
                is_statement: isStatement
            };
            if (typeof receivedAmount !== 'undefined' && receivedAmount !== null) {
                postData.tds_received = receivedAmount;
            }

            $.ajax({
                url: 'addPayment.php',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function (res) {
                    $btn.prop('disabled', false).css('opacity', '1');
                    if (res && res.success) {
                        $btn.attr('data-status', newStatus);
                        if (newStatus === 'Verified') {
                            $btn.removeClass('tds-btn-unverified')
                                .addClass('tds-btn-verified')
                                .css({
                                    'background-color': '#16a34a',
                                    'border-color': '#15803d',
                                    'color': '#ffffff'
                                })
                                .attr('title', 'Click to toggle: Mark as Unverified')
                                .html('<i class="fa fa-check"></i> <span>Verified</span>');
                        } else {
                            $btn.removeClass('tds-btn-verified')
                                .addClass('tds-btn-unverified')
                                .css({
                                    'background-color': '#ef4444',
                                    'border-color': '#dc2626',
                                    'color': '#ffffff'
                                })
                                .attr('title', 'Click to toggle: Verify TDS against Bank Statement')
                                .html('<i class="fa fa-times"></i> <span>Verify</span>');
                        }
                        if (typeof Swal !== 'undefined') {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'success',
                                title: newStatus === 'Verified' ? 'TDS Verified successfully' : 'TDS marked as Unverified'
                            });
                        }
                    } else {
                        var errMsg = (res && res.message) ? res.message : 'Could not update TDS verify status';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: '<span style="color: #dc2626;">Verification Failed</span>',
                                html: '<div style="font-size: 13.5px; color: #374151;">' + errMsg + '</div>'
                            });
                        } else {
                            alert(errMsg);
                        }
                    }
                },
                error: function (xhr, status, err) {
                    $btn.prop('disabled', false).css('opacity', '1');
                    var errMsg = 'Server error updating TDS verify status.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: errMsg });
                    } else {
                        alert(errMsg);
                    }
                }
            });
        }

        $(document).on('change', '.saveChanges', function (e) {
            saveData($(this));
        });

        function saveData($selector) {
            var checkedValue = $selector.val();
            var colName = $selector.attr('data-column');
            var isTdsRec = (colName === 'tds_received');

            if (isTdsRec) {
                applyTdsInputValidation($selector);
                var decided = parseFloat($selector.attr('data-decided') || $selector.closest('tr').attr('data-decided') || 0);
                var raw = $.trim(checkedValue || '');
                var val = parseFloat(raw.replace(/,/g, ''));
                if (decided > 0 && !isNaN(val) && val > 0 && val < decided) {
                    if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3500,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'warning',
                            title: 'TDS received amount (₹' + val.toFixed(2) + ') is lower than TDS decided amount (₹' + decided.toFixed(2) + ')'
                        });
                    }
                }
            }

            $.ajax({
                url: "deleteFinalInvoice.php",
                type: "POST",
                data: {
                    id: $selector.attr('data-row'),
                    columnName: colName,
                    columnValue: checkedValue,
                    is_statement: $selector.attr('data-statement') || '0'
                },
                success: function (data) {
                    if (isTdsRec) {
                        applyTdsInputValidation($selector);
                    } else {
                        $selector.css('border-color', '#16a34a');
                        setTimeout(function() {
                            $selector.css('border-color', '');
                        }, 1500);
                    }
                },
                error: function() {
                    $selector.css('border-color', '#ef4444');
                }
            });
            $selector.removeClass("glowing-border");
            $selector.addClass("lookslike-label");
        }

        $('.customerAccount').click(function () {
            var customerID = $(this).attr('data-id');
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                    '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.newCustomer').click(function () {
            $(".addNewCustomer").show();
            $('.newCustomer').hide();
        });

        $('.cancelAdding').click(function () {
            $('.newCustomer').show();
            $(".addNewCustomer").hide();
        });



        table.on('draw', function () {
            $('input[name="tds_received"]').each(function() {
                applyTdsInputValidation($(this));
            });
        });
        $('input[name="tds_received"]').each(function() {
            applyTdsInputValidation($(this));
        });

        setTimeout(function () {
            table.columns.adjust();
        }, 150);

        $(window).on('resize', function () {
            table.columns.adjust();
        });
    });
</script>

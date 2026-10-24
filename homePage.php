<div id="wrapper" class="active">
    <?php
    //    session_start();
    $pageIdentifier = "Home Page";
    include 'header.php';
    include 'dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');




    // session handelling 
    




    $ststus = "YES";
    //    $customerList = "SELECT * FROM customer_info where is_deleted <> 'YES' ORDER BY unique_id";
    require_once __DIR__ . '/include/rbac_helper.php';
    $hp_role = $_SESSION['role'] ?? '';
    // Client 1191 restricted: only Top Mgmt, Admin, Finance can access
    $customer1191Filter = !aitf_can_access_customer(1191, $hp_role) ? " AND ci.id != 1191" : "";
$customerListBase = "SELECT ci.* , ac.certification_date FROM customer_info ci left join allotment_count ac on ci.id = ac.client_id where is_deleted <> 'YES'" . $customer1191Filter;

if (in_array(trim($hp_role), array('Office Staff-A', 'Office Staff-B', 'BDM'), true)) {
    // C✔✔ - Active Past One Month+ Running Access
    $customerList = $customerListBase . " AND ci.audit_status = 'Active' AND ac.certification_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01') ORDER BY ac.certification_date DESC";
} elseif (in_array(trim($hp_role), array('Marketing Head', 'Marketing Executive'), true)) {
    // C✔ All other like Suspended, Expired, In process, Application Cancel, New Enquiry
    $customerList = $customerListBase . " AND ci.audit_status IN ('Suspended', 'Expired', 'In process', 'Application Cancel', 'New Enquiry') ORDER BY ac.certification_date DESC";
} else {
    $customerList = $customerListBase . " ORDER BY ac.certification_date DESC";
}
    $statement = $db->prepare($customerList);
    $statement->execute();
    $listOfCustomer = $statement->fetchAll();

    $services = "SELECT * FROM service_types";
    $statement = $db->prepare($services);
    $statement->execute();
    $servicesTypes = $statement->fetchAll();

    $users = "SELECT * FROM `handler`";
    $statement = $db->prepare($users);
    $statement->execute();
    $allUsers = $statement->fetchAll();

    // Build handler lookup to avoid per-row queries
    $handlerMap = array();
    foreach ($allUsers as $handlerRow) {
        $handlerMap[$handlerRow['id']] = $handlerRow['handler_name'];
    }

    // Collect customer ids for batch queries
    $customerIds = array();
    foreach ($listOfCustomer as $c) {
        $customerIds[] = $c['id'];
    }

    $serviceMap = array();
    $serviceNonSpMap = array();
    $dueMap = array();
    $paymentMap = array();

    if (!empty($customerIds)) {
        $inPlaceholders = implode(',', array_fill(0, count($customerIds), '?'));

        // Latest service per customer (any type)
        $sqlLatestService = "SELECT s.* FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE (is_deleted = 'NO' OR is_deleted IS NULL) AND customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
        $stmt = $db->prepare($sqlLatestService);
        $stmt->execute($customerIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $serviceMap[$row['customer_id']] = $row;
        }

        // Latest non-SP service_id per customer for due dates
        $sqlNonSpService = "SELECT s.customer_id, s.service_id FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE (is_deleted = 'NO' OR is_deleted IS NULL) AND service_type <> 'SP' AND customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
        $stmt = $db->prepare($sqlNonSpService);
        $stmt->execute($customerIds);
        $serviceIdsForDates = array();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $serviceNonSpMap[$row['customer_id']] = $row['service_id'];
            $serviceIdsForDates[] = $row['service_id'];
        }

        // Due dates for the latest non-SP service
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

        // Payments per customer
        $sqlPayments = "SELECT customer_id, SUM(balance) as payment_due FROM payment_system WHERE customer_id IN ($inPlaceholders) GROUP BY customer_id";
        $stmt = $db->prepare($sqlPayments);
        $stmt->execute($customerIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $paymentMap[$row['customer_id']] = $row['payment_due'];
        }
    }

    // Normalize audit_status into a canonical status group (used for colored badges + status tabs)
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

    // Derive the display status from the client's running service, falling back to audit_status:
    //  - No service created yet            -> 'New Enquiry'
    //  - Running (latest) service is CR     -> 'Contract-Review' (created, completed or not)
    //  - otherwise                          -> normalized audit_status
    if (!function_exists('hpDerivedStatus')) {
        function hpDerivedStatus($customer, $serviceMap)
        {
            $norm = hpNormalizeStatus(isset($customer['audit_status']) ? $customer['audit_status'] : '');
            // A cancelled application is a terminal state -> takes precedence, even if the
            // client has no service or is still in contract review.
            if ($norm === 'Application canceled') {
                return 'Application canceled';
            }
            $cid = isset($customer['id']) ? $customer['id'] : null;
            $svc = ($cid !== null && isset($serviceMap[$cid])) ? $serviceMap[$cid] : null;
            // No service created yet (and not cancelled) -> just added, new enquiry
            if (empty($svc)) {
                return 'New Enquiry';
            }
            // Running (latest) service is a Contract Review
            if (isset($svc['service_type']) && strtoupper(trim($svc['service_type'])) === 'CR') {
                return 'Contract-Review';
            }
            return $norm;
        }
    }

    // Color theme + preferred ordering for each status
    $statusColorMap = array(
        'Active' => 'green',
        'In-process' => 'yellow',
        'Contract-Review' => 'blue',
        'New Enquiry' => 'purple',
        'Contract In-process' => 'amber',
        'Suspended' => 'orange',
        'Expired' => 'red',
        'Withdrawn' => 'grey',
        'Application canceled' => 'red',
        'No Status' => 'slate',
    );
    $statusPriority = array('New Enquiry', 'Contract-Review', 'In-process', 'Active', 'Withdrawn', 'Suspended', 'Expired', 'Application canceled');

    // Count clients per derived status for the tab badges
    $statusCounts = array();
    foreach ($listOfCustomer as $c) {
        $ns = hpDerivedStatus($c, $serviceMap);
        $statusCounts[$ns] = isset($statusCounts[$ns]) ? $statusCounts[$ns] + 1 : 1;
    }
    $totalClients = count($listOfCustomer);

    // Build the ordered tab list for the client status navigation.
    $tabStatuses = $statusPriority;

    ?>

<style>
    /* =====================================================================
       MASTER CREATOR STYLE PARITY — CLEAN SAAS DESIGN SYSTEM
       ===================================================================== */
    html, body, #wrapper, #page-content-wrapper, .page-content, .page-content.inset {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        background-color: #f8fafc !important;
        color: #0f172a !important;
        -webkit-font-smoothing: antialiased !important;
    }

    #page-content-wrapper .fa {
        font-family: 'FontAwesome' !important;
    }

    .master-creator-container {
        max-width: 100% !important;
        margin: 0 auto !important;
        padding: 6px 16px 20px 16px !important;
    }

    /* =====================================================================
       STATUS TABS (CLEAN SINGLE-LINE PILLS WITH SMOOTH HORIZONTAL SCROLL)
       ===================================================================== */
    .hp-tabs-nav {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        gap: 8px !important;
        margin: 0 0 12px 0 !important;
        padding: 4px 2px 8px 2px !important;
        border-bottom: none !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        scrollbar-width: thin !important;
        scrollbar-color: #cbd5e1 transparent !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .hp-tabs-nav::-webkit-scrollbar {
        height: 4px !important;
    }

    .hp-tabs-nav::-webkit-scrollbar-track {
        background: transparent !important;
    }

    .hp-tabs-nav::-webkit-scrollbar-thumb {
        background-color: #cbd5e1 !important;
        border-radius: 10px !important;
    }

    .hp-tabs-nav::-webkit-scrollbar-thumb:hover {
        background-color: #94a3b8 !important;
    }

    .hp-tab {
        flex: 0 0 auto !important;
        white-space: nowrap !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        height: 34px !important;
        padding: 0 13px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 20px !important;
        background: #ffffff !important;
        color: #475569 !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        cursor: pointer !important;
        outline: none !important;
        transition: all 0.15s ease !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03) !important;
    }

    .hp-tab:hover {
        background: #f1f5f9 !important;
        color: #0d4d8b !important;
        border-color: #cbd5e1 !important;
    }

    .hp-tab .hp-dot {
        width: 7px !important;
        height: 7px !important;
        border-radius: 50% !important;
        background: currentColor !important;
        flex: 0 0 auto !important;
    }

    .hp-tab .hp-count {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 18px !important;
        height: 18px !important;
        padding: 0 6px !important;
        font-size: 10.5px !important;
        font-weight: 700 !important;
        line-height: 1 !important;
        border-radius: 999px !important;
        background: #f1f5f9 !important;
        color: #475569 !important;
        transition: all 0.15s ease !important;
    }

    .hp-tab.active {
        background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
        border-color: #0d4d8b !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(13, 77, 139, 0.25) !important;
        font-weight: 700 !important;
    }

    .hp-tab.active .hp-dot {
        background: #ffffff !important;
    }

    .hp-tab.active .hp-count {
        background: rgba(255, 255, 255, 0.22) !important;
        color: #ffffff !important;
    }

    /* Tab colors */
    .hp-tab.hp-c-all { color: #0d4d8b; }
    .hp-tab.hp-c-green { color: #059669; }
    .hp-tab.hp-c-yellow { color: #d97706; }
    .hp-tab.hp-c-amber { color: #d97706; }
    .hp-tab.hp-c-orange { color: #ea580c; }
    .hp-tab.hp-c-red { color: #dc2626; }
    .hp-tab.hp-c-grey { color: #475569; }
    .hp-tab.hp-c-slate { color: #64748b; }
    .hp-tab.hp-c-blue { color: #2563eb; }
    .hp-tab.hp-c-purple { color: #7c3aed; }

    /* =====================================================================
       FILTER PANEL (PROPER ALIGNMENT & PROPORTIONS)
       ===================================================================== */
    .invoice-filter-panel {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
        margin: 0 0 12px 0 !important;
        padding: 10px 14px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.03) !important;
    }

    .invoice-filter-item {
        position: relative !important;
    }

    .invoice-filter-item.search-item {
        flex: 1 1 220px !important;
        min-width: 180px !important;
    }

    .invoice-filter-item.search-item i {
        position: absolute !important;
        left: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: #94a3b8 !important;
        font-size: 13px !important;
        pointer-events: none !important;
    }

    .invoice-filter-item.search-item input {
        padding-left: 34px !important;
    }

    .invoice-filter-item.select-item {
        flex: 0 1 190px !important;
        min-width: 170px !important;
    }

    .invoice-filter-item.date-item {
        flex: 0 1 135px !important;
        min-width: 115px !important;
    }

    .invoice-filter-item.btn-item {
        flex: 0 0 auto !important;
    }

    .invoice-filter-item input,
    .invoice-filter-item select {
        width: 100% !important;
        height: 38px !important;
        padding: 6px 12px !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 7px !important;
        box-sizing: border-box !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        background-color: #f8fafc !important;
        color: #0f172a !important;
        transition: all 0.15s ease !important;
    }

    .invoice-filter-item select {
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

    .invoice-filter-item input::placeholder {
        color: #94a3b8 !important;
    }

    .invoice-filter-item input:focus,
    .invoice-filter-item select:focus {
        border-color: #0d4d8b !important;
        background-color: #ffffff !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12) !important;
    }

    .btn-report-export {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        height: 38px !important;
        margin: 0 !important;
        padding: 0 18px !important;
        box-sizing: border-box !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        letter-spacing: 0.2px !important;
        border-radius: 7px !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        border: none !important;
        white-space: nowrap !important;
    }

    .btn-solid-primary {
        background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(13, 77, 139, 0.2) !important;
    }

    .btn-solid-primary:hover {
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%) !important;
        box-shadow: 0 4px 12px rgba(13, 77, 139, 0.28) !important;
        transform: translateY(-1px) !important;
        color: #ffffff !important;
    }

    /* =====================================================================
       TABLE OVERHAUL (STRIPPING ALL UGLY BORDERS & NATURAL FIT)
       ===================================================================== */
    .table-responsive {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04) !important;
        padding: 0 !important;
        margin-bottom: 20px !important;
        overflow-x: auto !important;
    }

    #tab_customer_list {
        border: none !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        background: #ffffff !important;
        margin: 0 !important;
        width: 100% !important;
    }

    #tab_customer_list th,
    #tab_customer_list td {
        border-left: none !important;
        border-right: none !important;
    }

    #tab_customer_list thead th {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
        border-top: none !important;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 10px 12px !important;
        height: 42px !important;
        color: #475569 !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.6px !important;
        box-sizing: border-box !important;
        white-space: nowrap !important;
    }

    #tab_customer_list thead th small,
    #tab_customer_list thead th a {
        color: #475569 !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.6px !important;
    }

    #tab_customer_list tbody tr {
        background-color: #ffffff !important;
        transition: background 0.15s ease !important;
    }

    #tab_customer_list tbody tr:nth-of-type(even) {
        background-color: #fbfcfd !important;
    }

    #tab_customer_list tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    #tab_customer_list tbody td {
        padding: 9px 12px !important;
        border-top: none !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-size: 12.5px !important;
        font-weight: 500 !important;
        color: #334155 !important;
        vertical-align: middle !important;
    }

    #tab_customer_list small {
        font-size: 12.5px !important;
        font-weight: 500 !important;
        color: #334155 !important;
    }

    .customerAccount {
        cursor: pointer !important;
    }

    .customerAccount,
    .customerAccount * {
        cursor: pointer !important;
    }

    .customerAccount a {
        color: #0d4d8b !important;
        font-weight: 700 !important;
        font-size: 12.5px !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }

    .customerAccount a:hover,
    .customerAccount:hover a {
        color: #2563eb !important;
        text-decoration: underline !important;
        cursor: pointer !important;
    }

    /* Badges */
    #page-content-wrapper .hp-badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        padding: 3px 9px !important;
        border-radius: 12px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
    }

    #page-content-wrapper .hp-badge::before {
        content: "" !important;
        display: inline-block !important;
        width: 6px !important;
        height: 6px !important;
        border-radius: 50% !important;
        background: currentColor !important;
    }

    #page-content-wrapper .hp-c-green.hp-badge { color: #047857 !important; background: #ecfdf5 !important; }
    #page-content-wrapper .hp-c-yellow.hp-badge { color: #b45309 !important; background: #fef3c7 !important; }
    #page-content-wrapper .hp-c-amber.hp-badge { color: #b45309 !important; background: #fef3c7 !important; }
    #page-content-wrapper .hp-c-orange.hp-badge { color: #c2410c !important; background: #fff7ed !important; }
    #page-content-wrapper .hp-c-red.hp-badge { color: #b91c1c !important; background: #fee2e2 !important; }
    #page-content-wrapper .hp-c-grey.hp-badge { color: #475569 !important; background: #f1f5f9 !important; }
    #page-content-wrapper .hp-c-slate.hp-badge { color: #64748b !important; background: #f1f5f9 !important; }
    #page-content-wrapper .hp-c-blue.hp-badge { color: #1d4ed8 !important; background: #eff6ff !important; }
    #page-content-wrapper .hp-c-purple.hp-badge { color: #6d28d9 !important; background: #f5f3ff !important; }

    /* Handler select inside table */
    #tab_customer_list select.setHandler {
        width: 100% !important;
        min-width: 130px !important;
        height: 34px !important;
        padding: 4px 22px 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        background-color: #ffffff !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 8px center !important;
        background-size: 9px !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        color: #0f172a !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
        cursor: pointer !important;
        box-sizing: border-box !important;
    }

    #tab_customer_list select.setHandler:focus {
        border-color: #0d4d8b !important;
        box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12) !important;
        outline: none !important;
    }

    /* Comments textarea inside table */
    #tab_customer_list textarea.saveChanges {
        width: 100% !important;
        min-width: 200px !important;
        min-height: 48px !important;
        height: 48px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        padding: 5px 8px !important;
        font-size: 12px !important;
        line-height: 1.35 !important;
        color: #0f172a !important;
        background: #ffffff !important;
        resize: vertical !important;
        box-sizing: border-box !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
        transition: all 0.15s ease !important;
    }

    #tab_customer_list textarea.saveChanges:focus {
        background: #ffffff !important;
        border-color: #0d4d8b !important;
        box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12) !important;
        outline: none !important;
    }

    /* DataTables Footer Bar (Info & Pagination tightly attached beneath table) */
    .dataTables_wrapper > .row:last-child {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 10px 14px !important;
        margin: 0 !important;
        background: #ffffff !important;
        border-top: 1px solid #e2e8f0 !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding: 0 !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        color: #64748b !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding: 0 !important;
        display: flex !important;
        align-items: center !important;
        gap: 3px !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid #e2e8f0 !important;
        border-radius: 6px !important;
        background: #ffffff !important;
        color: #475569 !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        padding: 4px 10px !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0d4d8b !important;
        color: #ffffff !important;
        border-color: #0d4d8b !important;
    }

    #tab_customer_list_filter {
        display: none !important;
    }

    .ui-datepicker {
        z-index: 100000 !important;
    }

    /* =====================================================================
       DARK MODE SUPPORT
       ===================================================================== */
    html.theme-dark,
    html.theme-dark body,
    html.theme-dark #wrapper,
    html.theme-dark #page-content-wrapper,
    html.theme-dark .page-content,
    html.theme-dark .page-content.inset {
        background-color: #0a0e14 !important;
        color: #dbe3f0 !important;
    }

    html.theme-dark .hp-tabs-nav {
        scrollbar-color: #2e3a4f transparent !important;
    }

    html.theme-dark .hp-tabs-nav::-webkit-scrollbar-thumb {
        background-color: #2e3a4f !important;
    }

    html.theme-dark .hp-tabs-nav::-webkit-scrollbar-thumb:hover {
        background-color: #475569 !important;
    }

    html.theme-dark .hp-tab {
        background: #111722 !important;
        border-color: #232c3d !important;
        color: #93a1b8 !important;
    }

    html.theme-dark .hp-tab:hover {
        background: #161d2b !important;
        color: #ffffff !important;
    }

    html.theme-dark .hp-tab .hp-count {
        background: #161d2b !important;
        color: #93a1b8 !important;
    }

    html.theme-dark .hp-tab.active {
        background: #1e3a5f !important;
        border-color: #2e5a8f !important;
        color: #ffffff !important;
    }

    html.theme-dark .hp-tab.active .hp-count {
        background: #4a9eff !important;
        color: #061020 !important;
    }

    html.theme-dark .invoice-filter-panel {
        background: #111722 !important;
        border-color: #232c3d !important;
    }

    html.theme-dark .invoice-filter-item input,
    html.theme-dark .invoice-filter-item select {
        background-color: #161d2b !important;
        border-color: #2e3a4f !important;
        color: #dbe3f0 !important;
    }

    html.theme-dark .table-responsive {
        background: #111722 !important;
        border-color: #232c3d !important;
    }

    html.theme-dark #tab_customer_list thead th {
        background: #161d2b !important;
        border-bottom-color: #2e3a4f !important;
        color: #dbe3f0 !important;
    }

    html.theme-dark #tab_customer_list tbody tr {
        background-color: #111722 !important;
    }

    html.theme-dark #tab_customer_list tbody tr:nth-of-type(even) {
        background-color: #131a26 !important;
    }

    html.theme-dark #tab_customer_list tbody tr:hover {
        background-color: #1a2334 !important;
    }

    html.theme-dark #tab_customer_list tbody td {
        border-bottom-color: #232c3d !important;
        color: #dbe3f0 !important;
    }

    html.theme-dark .customerAccount a {
        color: #60a5fa !important;
    }

    html.theme-dark .customerAccount a:hover,
    html.theme-dark .customerAccount:hover a {
        color: #93c5fd !important;
        text-decoration: underline !important;
    }

    html.theme-dark #tab_customer_list select.setHandler {
        background-color: #161d2b !important;
        border-color: #2e3a4f !important;
        color: #dbe3f0 !important;
    }

    html.theme-dark #tab_customer_list textarea.saveChanges {
        background-color: #161d2b !important;
        border-color: #2e3a4f !important;
        color: #dbe3f0 !important;
    }

    html.theme-dark .dataTables_wrapper > .row:last-child {
        background: #111722 !important;
        border-top-color: #232c3d !important;
    }

    html.theme-dark .dataTables_wrapper .dataTables_info {
        color: #93a1b8 !important;
    }

    html.theme-dark .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #161d2b !important;
        border-color: #232c3d !important;
        color: #dbe3f0 !important;
    }

    html.theme-dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #4a9eff !important;
        color: #061020 !important;
        border-color: #4a9eff !important;
    }
</style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid master-creator-container" style="max-width: 100%; padding: 8px 18px 20px 18px;">
                <div class="row" style="margin-bottom: 8px;">
                    <div class="col-sm-12 main_content" style="padding: 0 6px;">
                        <nav class="hp-tabs-nav" role="tablist" aria-label="Client Status Tabs">
                            <button type="button" class="hp-tab hp-c-all active" data-status="" role="tab"
                                aria-selected="true">
                                <span class="hp-dot"></span> All Clients <span class="hp-count"><?php echo $totalClients; ?></span>
                            </button>
                            <?php foreach ($tabStatuses as $st) {
                                $c = isset($statusColorMap[$st]) ? $statusColorMap[$st] : 'slate'; 
                                $cnt = isset($statusCounts[$st]) ? $statusCounts[$st] : 0; ?>
                                <button type="button" class="hp-tab hp-c-<?php echo $c ?>"
                                    data-status="<?php echo htmlspecialchars($st, ENT_QUOTES) ?>" role="tab"
                                    aria-selected="false">
                                    <span class="hp-dot"></span> <?php echo htmlspecialchars($st) ?> <span class="hp-count"><?php echo $cnt; ?></span>
                                </button>
                            <?php } ?>
                        </nav>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 12px;">
                    <div class="col-sm-12 main_content" style="padding: 0 6px;">
                        <div class="invoice-filter-panel">
                            <div class="invoice-filter-item search-item">
                                <i class="fa fa-search"></i>
                                <input type="text" id="clientSearch" placeholder="Search by Client Name...">
                            </div>
                            <div class="invoice-filter-item select-item">
                                <select name="service-type" class="service-type" id="service-type">
                                    <option value="">Select Service Type</option>
                                    <?php foreach ($servicesTypes as $service) { ?>
                                        <option value="<?php echo $service['service_type'] ?>">
                                            <?php echo $service['service_type'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="invoice-filter-item date-item">
                                <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate"
                                    placeholder="Due From Date" value="">
                            </div>
                            <div class="invoice-filter-item date-item">
                                <input type="text" class="filterToDate" id="filterToDate" name="filterToDate"
                                    placeholder="Due To Date" value="">
                            </div>
                            <div class="invoice-filter-item btn-item">
                                <button type="button" class="btn-report-export btn-solid-primary downloadHomeReport">
                                    <i class="fa fa-cloud-download"></i> Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row clearfix">
                    <div class="col-sm-12 main_content table-responsive" style="padding: 0 6px;">
                        <table class="table table-hover" id="tab_customer_list" width="100%">
                            <thead>
                                <tr>
                                    <th style="text-align: center;"><small>#</small></th>
                                    <th style="text-align: center;"><small>Unique ID</small></th>
                                    <th style="text-align: center;"><small>Agreement Date</small></th>
                                    <th style="text-align: left;"><small>Client Name</small></th>
                                    <th style="text-align: left;"><small>Contact Person</small></th>
                                    <th style="text-align: center;"><small>Status</small></th>
                                    <th style="text-align: center;"><small>Next Due Date</small></th>
                                    <th style="text-align: center;"><small>Next Service</small></th>
                                    <th style="text-align: center;"><small>Handler</small></th>
                                    <th style="text-align: left;"><small>Comments</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 0;
                                foreach ($listOfCustomer as $customer) {
                                    $count = $count + 1;
                                    // Latest service for this customer
                                    $serviceInfo = isset($serviceMap[$customer['id']]) ? $serviceMap[$customer['id']] : array();

                                    // Due dates from pre-fetched map
                                    $dueInfo = $dueInfoex = $sentInfo = array();
                                    $sid = isset($serviceNonSpMap[$customer['id']]) ? $serviceNonSpMap[$customer['id']] : null;
                                    if ($sid && isset($dueMap[$sid])) {
                                        $dueInfo['due_date'] = isset($dueMap[$sid]['Suspension Letter']) ? $dueMap[$sid]['Suspension Letter'] : (isset($dueMap[$sid]['Suspend Letter']) ? $dueMap[$sid]['Suspend Letter'] : null);
                                        $dueInfoex['due_date'] = isset($dueMap[$sid]['Expire Letter']) ? $dueMap[$sid]['Expire Letter'] : null;
                                        $sentInfo['due_date'] = isset($dueMap[$sid]['Withdrawal Letter']) ? $dueMap[$sid]['Withdrawal Letter'] : (isset($dueMap[$sid]['Withdraw Letter']) ? $dueMap[$sid]['Withdraw Letter'] : null);
                                    }

                                    // Payment due from map
                                    $payInfo = array('payment_due' => isset($paymentMap[$customer['id']]) ? $paymentMap[$customer['id']] : null);

                                    // Derived status + color for badge / tab filtering
                                    $normStatus = hpDerivedStatus($customer, $serviceMap);
                                    $statusColor = isset($statusColorMap[$normStatus]) ? $statusColorMap[$normStatus] : 'slate';
                                    ?>
                                    <tr id='addr<?php echo $customer['id'] ?>'
                                        data-status="<?php echo htmlspecialchars($normStatus, ENT_QUOTES) ?>">
                                        <td data-name="name">
                                            <small><?php echo $count ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="sel">
                                            <small><?php if (isset($customer['unique_id']))
                                                echo $customer['unique_id'] ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="mail">
                                            <small><a style="cursor: pointer;"><?php echo $customer['certification_date'] ?></a></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="mail">
                                            <small><a style="font-weight: 600; color: #0d4d8b; cursor: pointer;"><?php echo $customer['customer_name'] ?></a></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="desc">
                                            <small><?php echo $customer['contact_person'] ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="desc">
                                            <small><span
                                                    class="hp-badge hp-c-<?php echo $statusColor ?>"><?php echo htmlspecialchars($normStatus); ?><?php if ($normStatus === "In-process" && !empty($serviceInfo["service_type"]))
                                                              echo ' (' . htmlspecialchars($serviceInfo["service_type"]) . ')'; ?></span></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="sel">
                                            <small><span><?php if (isset($dueInfo["due_date"]) && !empty($dueInfo["due_date"])) {
                                                echo simpleDateConversion($dueInfo["due_date"]);
                                            } else {
                                                echo simpleDateConversion($dueInfoex["due_date"]);
                                            } ?>~</span><?php if (isset($dueInfo["due_date"]) && !empty($dueInfo["due_date"])) {
                                                 echo simpleDateConversion($dueInfo["due_date"]);
                                             } else {
                                                 echo simpleDateConversion($dueInfoex["due_date"]);
                                             } ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="sel">
                                            <small><?php echo $customer['next_service']; ?></small>
                                        </td>
                                        <td data-name="sel">
                                            <div class="float-label-control">
                                                <select class="form-control setHandler" data-id="<?php echo $customer['id'] ?>"
                                                    name="setHandler" style="color: black">
                                                    <?php
                                                    if (isset($customer["handler"]) && !empty($customer["handler"]) && isset($handlerMap[$customer["handler"]])) {
                                                        ?>
                                                        <option value="<?php echo $customer["handler"]; ?>">
                                                            <?php echo $handlerMap[$customer["handler"]]; ?></option> <?php } else {
                                                        ?>
                                                        <option value="">Hand</option> <?php }
                                                    ?>
                                                    <?php foreach ($allUsers as $users) { ?>
                                                        <option value="<?php echo $users["id"] ?>">
                                                            <?php echo $users["handler_name"] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </td>
                                        <td data-name="sel">
                                            <div class="table-edit-control">
                                                <!--<small><?php echo $customer['comment'] ?></small>-->
                                                <textarea rows="3" class="saveChanges" data-row="<?php echo $customer['id'] ?>"
                                                    data-column="comment" name="payComments" value=""
                                                    placeholder=""><?php if (isset($customer['comment']))
                                                        echo $customer['comment'] ?></textarea>
                                            </div>
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

<!-- Report chooser: Step 1 pick format (PDF/Excel), Step 2 pick action (download / email) -->
<div id="report-format-chooser" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); backdrop-filter:blur(3px); z-index:99999;">
    <div style="max-width:460px; margin:8% auto; background:#ffffff; border-radius:14px; box-shadow:0 20px 50px rgba(15,23,42,0.25); overflow:hidden; border:1px solid #e2e8f0;">
        <div style="background:linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%); color:#fff; padding:16px 20px; font-size:15px; font-weight:700; display:flex; align-items:center; justify-content:space-between;">
            <span><i class="fa fa-cloud-download" style="margin-right:8px;"></i> Export Client Report</span>
            <button type="button" class="report-format-cancel" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer; opacity:0.85; line-height:1;">&times;</button>
        </div>

        <!-- STEP 1: format -->
        <div class="report-step report-step-format" style="padding:24px 20px; text-align:center;">
            <p style="font-size:14px; color:#334155; font-weight:600; margin:0 0 20px;">Choose your preferred report format:</p>
            <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
                <button type="button" class="btn report-format-pdf" style="min-width:140px; padding:12px 18px; border-radius:10px; background:#fef2f2; border:1.5px solid #fca5a5; color:#dc2626; font-weight:700; transition:all 0.15s ease;">
                    <i class="fa fa-file-pdf-o" style="font-size:18px; display:block; margin-bottom:4px;"></i> PDF Document
                </button>
                <button type="button" class="btn report-format-excel" style="min-width:140px; padding:12px 18px; border-radius:10px; background:#f0fdf4; border:1.5px solid #86efac; color:#16a34a; font-weight:700; transition:all 0.15s ease;">
                    <i class="fa fa-file-excel-o" style="font-size:18px; display:block; margin-bottom:4px;"></i> Excel Spreadsheet
                </button>
            </div>
            <div style="margin-top:22px;">
                <button type="button" class="btn btn-default btn-sm report-format-cancel" style="border-radius:8px; padding:6px 16px; font-weight:600;">Cancel</button>
            </div>
        </div>

        <!-- STEP 2: action (download or email) -->
        <div class="report-step report-step-action" style="display:none; padding:24px 20px; text-align:center;">
            <p style="font-size:13.5px; color:#334155; margin:0 0 6px;">Selected format: <strong class="report-format-label" style="color:#0d4d8b;">Excel</strong></p>
            <p style="font-size:12.5px; color:#64748b; margin:0 0 20px;">How would you like to receive your file?</p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <button type="button" class="btn report-action-download" style="min-width:150px; padding:10px 16px; border-radius:8px; background:linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%); color:#fff; font-weight:700; border:none; box-shadow:0 2px 8px rgba(13,77,139,0.2);">
                    <i class="fa fa-download"></i> Direct Download
                </button>
                <button type="button" class="btn report-action-email" style="min-width:150px; padding:10px 16px; border-radius:8px; background:linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color:#fff; font-weight:700; border:none; box-shadow:0 2px 8px rgba(217,119,6,0.2);">
                    <i class="fa fa-envelope"></i> Send via Email
                </button>
            </div>

            <!-- Email sub-form -->
            <div class="report-email-box" style="display:none; margin-top:20px; text-align:left; background:#f8fafc; padding:14px; border-radius:10px; border:1px solid #e2e8f0;">
                <label style="font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:6px; display:block;">Recipient Email Address <span style="color:#ef4444;">*</span></label>
                <input type="email" class="report-email-input form-control" placeholder="client@example.com"
                       style="height:38px; border-radius:8px; border:1px solid #cbd5e1; font-size:13px;" autocomplete="off">
                <div class="report-email-msg" style="font-size:12px; margin-top:8px; min-height:16px;"></div>
                <button type="button" class="btn btn-success report-email-send" style="width:100%; margin-top:8px; height:38px; border-radius:8px; font-weight:700; background:#10b981; border:none;">
                    <i class="fa fa-paper-plane"></i> Send Report Now
                </button>
            </div>

            <div style="margin-top:22px; display:flex; justify-content:center; gap:8px;">
                <button type="button" class="btn btn-default btn-sm report-step-back" style="border-radius:8px; padding:6px 14px; font-weight:600;"><i class="fa fa-arrow-left"></i> Back</button>
                <button type="button" class="btn btn-default btn-sm report-format-cancel" style="border-radius:8px; padding:6px 14px; font-weight:600;">Cancel</button>
            </div>
        </div>
    </div>
</div>

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

    function _(el) {
        return document.getElementById(el);
    }

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

    $.fn.DataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var serviceType = $(".service-type").val();
            var fromDate = $(".filterFromDate").val();
            var toDate = $(".filterToDate").val();
            var isServiceType = false;
            var isfromDate = false;
            var isToDate = false;

            if (serviceType == "" && fromDate == "" && toDate == "") {
                return true;
            }

            if (serviceType != "") {
                var servieTypeStr = data[7] || ""; // column index 7 is "Next Ser Type"
                if (servieTypeStr.toUpperCase().trim() == serviceType.toUpperCase().trim()) {
                    isServiceType = true;
                }
            } else {
                isServiceType = true;
            }

            var dateValue = data[6] || ""; // corrected from data[5] to data[6]
            var dateSplit = dateValue.split("~");

            if (fromDate != "") {
                isfromDate = compareMinDates(fromDate, dateSplit[1]);
            } else {
                isfromDate = true;
            }

            if (toDate != "") {
                isToDate = compareMaxDates(toDate, dateSplit[1]);
            } else {
                isToDate = true;
            }

            return (isServiceType && isfromDate && isToDate);
        }
    );

    // Status tab filter (client-side, no reload). Empty = show all clients.
    var hpSelectedStatus = '';
    $.fn.DataTable.ext.search.push(
        function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'tab_customer_list') {
                return true;
            }
            if (hpSelectedStatus === '') {
                return true;
            }
            var node = settings.aoData[dataIndex].nTr;
            var rowStatus = node ? node.getAttribute('data-status') : '';
            return rowStatus === hpSelectedStatus;
        }
    );

    $(document).ready(function () {
        <?php if (isset($_GET['error']) && $_GET['error'] === 'client_access_denied'): ?>
        alert("Access Denied: Only Top Management, Admin, and Finance are authorized to open this client.");
        if (window.history.replaceState) {
            var cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        }
        <?php endif; ?>

        // Status tabs: filter instantly on click, no page reload
        $('.hp-tab').on('click', function () {
            if ($(this).hasClass('active')) { return; }
            $('.hp-tab').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');
            hpSelectedStatus = $(this).data('status') || '';
            table.draw();
        });

        var debouncedSearch = debounce(function () {
            table.column(3).search($('#clientSearch').val()).draw();
        }, 200);

        $('#clientSearch').on('input', debouncedSearch);

        // Holds the report parameters chosen when a Download Report button is clicked.
        var pendingReport = null;
        // Format selected in step 1 ('pdf' | 'excel').
        var pendingFormat = null;

        function submitReportDownload() {
            if (!pendingReport || !pendingFormat) {
                return;
            }
            var url = (pendingFormat === 'excel') ? 'exportHomeReportExcel.php' : 'exportHomeReport.php';
            var fields = {
                fromDate: pendingReport.fromDate,
                toDate: pendingReport.toDate,
                serviceType: pendingReport.serviceType,
                clientName: pendingReport.clientName,
                statusFilter: pendingReport.statusFilter
            };
            var $form = $('<form>', {
                method: 'post',
                action: url
            }).css('display', 'none');

            $.each(fields, function (name, value) {
                $('<input>', {
                    type: 'hidden',
                    name: name,
                    value: value || ''
                }).appendTo($form);
            });

            $('body').append($form);
            $form[0].submit();
            setTimeout(function () { $form.remove(); }, 1000);
            closeFormatChooser();
        }

        function showReportStep(step) {
            $('.report-step').hide();
            if (step === 'action') {
                $('.report-step-action').show();
            } else {
                $('.report-step-format').show();
            }
        }

        function resetChooser() {
            pendingFormat = null;
            $('.report-email-box').hide();
            $('.report-email-input').val('');
            $('.report-email-msg').text('');
            $('.report-email-send').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
            showReportStep('format');
        }

        function openFormatChooser(params) {
            pendingReport = params;
            resetChooser();
            $('#report-format-chooser').css('display', 'block');
        }

        function closeFormatChooser() {
            $('#report-format-chooser').hide();
            pendingReport = null;
            pendingFormat = null;
        }

        // --- Step 1: choose format ---
        $('.report-format-pdf').click(function () {
            pendingFormat = 'pdf';
            $('.report-format-label').text('PDF');
            $('.report-email-box').hide();
            showReportStep('action');
        });

        $('.report-format-excel').click(function () {
            pendingFormat = 'excel';
            $('.report-format-label').text('Excel');
            $('.report-email-box').hide();
            showReportStep('action');
        });

        // --- Step 2: download (async; closes the chooser itself once the file is ready) ---
        $('.report-action-download').click(function () {
            submitReportDownload();
        });

        // --- Step 2: reveal the email sub-form ---
        $('.report-action-email').click(function () {
            $('.report-email-msg').text('');
            $('.report-email-box').show();
            $('.report-email-input').focus();
        });

        // --- Step 2 (email): send the report ---
        $('.report-email-send').click(function () {
            if (!pendingReport || !pendingFormat) {
                return;
            }
            var email = $.trim($('.report-email-input').val());
            var $msg = $('.report-email-msg');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                $msg.css('color', '#cc0000').text('Please enter a valid email address.');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            $msg.css('color', '#0d4d8b').text('Sending report, please wait...');

            $.post('emailHomeReport.php', {
                fromDate: pendingReport.fromDate,
                toDate: pendingReport.toDate,
                serviceType: pendingReport.serviceType,
                clientName: pendingReport.clientName,
                statusFilter: pendingReport.statusFilter,
                format: pendingFormat,
                email: email
            }, null, 'json').done(function (res) {
                if (res && res.success) {
                    $msg.css('color', '#1a7f37').text(res.message || 'Report sent successfully.');
                    setTimeout(closeFormatChooser, 2000);
                } else {
                    $msg.css('color', '#cc0000').text((res && res.message) ? res.message : 'Could not send the email.');
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
                }
            }).fail(function () {
                $msg.css('color', '#cc0000').text('Server error. Please try again.');
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
            });
        });

        $('.report-step-back').click(function () {
            resetChooser();
        });

        $('.report-format-cancel').click(function () {
            closeFormatChooser();
        });

        $('#report-format-chooser').click(function (e) {
            if (e.target === this) {
                closeFormatChooser();
            }
        });

        $('.downloadHomeReport').click(function () {
            var fromDate = $('#filterFromDate').val();
            var toDate = $('#filterToDate').val();
            var serviceType = $('#service-type').val();
            var clientName = $('#clientSearch').val();
            
            openFormatChooser({
                fromDate: fromDate,
                toDate: toDate,
                serviceType: serviceType,
                clientName: clientName,
                statusFilter: hpSelectedStatus
            });
        });

        $("#tab_customer_list tbody").on("change", ".setHandler", function () {
            $handler = $(this).val();
            $customer = $(this).attr("data-id");
            $.ajax({
                url: "updateCustomer.php",
                type: "GET",
                data: {
                    handler: $handler,
                    customer: $customer,
                }, error: function (xhr, status, error) {
                    alert("There seems to be issue saving data!");
                },
                success: function (data) {
                    //                    alert(data);
                }
            });
        });

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

        $("#tab_customer_list tbody").on("click", ".customerAccount", function () {
            var customerID = $(this).attr('data-id');
            //            alert(customerID);
            // open popup
            //            localStorage['visited'] = customerID;
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                '<input type="hidden" name="customerid" value="' + customerID + '" />' +
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

        $("#tab_customer_list tbody").on("keyup", ".saveChanges", function (e) {
            saveData($(this));
        });

        function saveData($selector) {

            var checkedValue = $selector.val();
            //            alert(checkedValue);
            $.ajax({
                url: "updateCustomer.php",
                type: "GET",
                //                dataType: 'JSON',
                data: {
                    id: $selector.attr('data-row'),
                    columnName: $selector.attr('data-column'),
                    columnValue: checkedValue,
                }, error: function (xhr, status, error) {
                    //                    alert("There seems to be issue saving data!");
                },
                success: function (data) {
                    //                    alert(data);
                }
            });

            $selector.removeClass("glowing-border");
            //           $(this).removeClass("form-group ");
            $selector.addClass("lookslike-label");
        }

    });

    var table = $('#tab_customer_list').DataTable({
        "paging": true,
        "pageLength": 50,
        "deferRender": true,
        "ordering": false,
        "lengthChange": false,
        "autoWidth": false,
        "columnDefs": [
            { "width": "35px", "targets": 0, "className": "text-center" },
            { "width": "90px", "targets": 1, "className": "text-center" },
            { "width": "95px", "targets": 2, "className": "text-center" },
            { "width": "180px", "targets": 3 },
            { "width": "130px", "targets": 4 },
            { "width": "120px", "targets": 5, "className": "text-center" },
            { "width": "105px", "targets": 6, "className": "text-center" },
            { "width": "90px", "targets": 7, "className": "text-center" },
            { "width": "140px", "targets": 8, "className": "text-center" },
            { "width": "220px", "targets": 9 }
        ]
    });
    table.columns.adjust().draw();

    // Client-side quick search by Client Name
    $('#clientSearch').on('keyup', function () {
        table.column(3).search(this.value).draw();
    });

    $('.service-type, .filterFromDate, .filterToDate').change(function () {
        var fromDate = $(".filterFromDate").val();
        var toDate = $(".filterToDate").val();
        if (fromDate != "" && toDate != "") {
            if (!compareMinDates(fromDate, toDate)) {
                alert("To Date can't be after From Date");
                $(".filterToDate").val("");
            }
        }
        table.draw();
    });
</script>
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
    $customerList = "SELECT ci.* , ac.certification_date FROM customer_info ci left join allotment_count ac on ci.id = ac.client_id where is_deleted <> 'YES' ORDER BY ac.certification_date DESC";
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
        $sqlLatestService = "SELECT s.* FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
        $stmt = $db->prepare($sqlLatestService);
        $stmt->execute($customerIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $serviceMap[$row['customer_id']] = $row;
        }

        // Latest non-SP service_id per customer for due dates
        $sqlNonSpService = "SELECT s.customer_id, s.service_id FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE service_type <> 'SP' AND customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
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
        function hpNormalizeStatus($value) {
            $status = trim((string) $value);
            if ($status === '') {
                return 'No Status';
            }
            $key = strtolower(str_replace(array('-', '_', ' '), '', $status));
            if ($key === 'active' || $key === 'valid' || $key === 'granted') return 'Active';
            if ($key === 'suspended' || $key === 'suspend') return 'Suspended';
            if ($key === 'inprocess') return 'In-process';
            if ($key === 'contractinprocess') return 'Contract In-process';
            if ($key === 'withdrawn' || $key === 'withdraw') return 'Withdrawn';
            if ($key === 'applicationcanceled' || $key === 'applicationcancelled' || $key === 'cancelled' || $key === 'canceled') return 'Application canceled';
            if ($key === 'expired' || $key === 'expire') return 'Expired';
            return $status;
        }
    }

    // Derive the display status from the client's running service, falling back to audit_status:
    //  - No service created yet            -> 'Awaiting Review'
    //  - Running (latest) service is CR     -> 'Contract-Review' (created, completed or not)
    //  - otherwise                          -> normalized audit_status
    if (!function_exists('hpDerivedStatus')) {
        function hpDerivedStatus($customer, $serviceMap) {
            $norm = hpNormalizeStatus(isset($customer['audit_status']) ? $customer['audit_status'] : '');
            // A cancelled application is a terminal state -> takes precedence, even if the
            // client has no service or is still in contract review.
            if ($norm === 'Application canceled') {
                return 'Application canceled';
            }
            $cid = isset($customer['id']) ? $customer['id'] : null;
            $svc = ($cid !== null && isset($serviceMap[$cid])) ? $serviceMap[$cid] : null;
            // No service created yet (and not cancelled) -> just added, awaiting review
            if (empty($svc)) {
                return 'Awaiting Review';
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
        'Active'               => 'green',
        'In-process'           => 'yellow',
        'Contract-Review'      => 'blue',
        'Awaiting Review'      => 'purple',
        'Contract In-process'  => 'amber',
        'Suspended'            => 'orange',
        'Expired'              => 'red',
        'Withdrawn'            => 'grey',
        'Application canceled' => 'slate',
        'No Status'            => 'slate',
    );
    $statusPriority = array('Awaiting Review', 'Contract-Review', 'In-process', 'Active', 'Withdrawn', 'Suspended', 'Expired', 'Application canceled');

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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        #page-content-wrapper,
        #page-content-wrapper * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        /* Keep FontAwesome glyphs working; the wildcard above otherwise forces Inter on <i> icons */
        #page-content-wrapper .fa { font-family: 'FontAwesome' !important; }

        .custom-float-label-control { margin-bottom: 0em; }

        .invoice-section-title {
            display: inline-block;
            margin: 0 0 18px;
            padding: 0;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.3px;
            color: #0f172a;
            background: transparent;
            border: none;
        }

        /* ===== Status Tabs ===== */
        .hp-tabs-nav {
            --hp-tab-border: #d6dbe6;
            --hp-tab-bg: #ffffff;
            --hp-tab-idle: #fafbfc;
            position: relative;
            display: flex;
            align-items: flex-end;
            gap: 0;
            margin: 4px 0 18px;
            padding: 8px 0 0;
            overflow-x: auto;
            overflow-y: visible;
            border-bottom: 1px solid var(--hp-tab-border);
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
        }
        .hp-tabs-nav::-webkit-scrollbar { height: 6px; }
        .hp-tabs-nav::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        .hp-tab {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            min-width: max-content;
            border: 1px solid var(--hp-tab-border);
            border-right-width: 0;
            border-bottom: 0;
            background: var(--hp-tab-idle);
            color: #475569;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0;
            line-height: 1;
            padding: 11px 18px 10px;
            border-radius: 0;
            cursor: pointer;
            outline: none;
            white-space: nowrap;
            isolation: isolate;
            box-shadow: inset 0 -1px 0 var(--hp-tab-border);
            transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }
        .hp-tab:first-child { border-top-left-radius: 2px; }
        .hp-tab:last-child {
            border-right-width: 1px;
            border-top-right-radius: 2px;
        }
        .hp-tab:hover {
            background: #ffffff;
            color: #0f172a;
            box-shadow: inset 0 -1px 0 var(--hp-tab-border), 0 -2px 7px rgba(15, 23, 42, 0.04);
            z-index: 2;
        }
        .hp-tab .hp-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: currentColor; flex: 0 0 auto;
            transition: transform 0.22s ease, background-color 0.22s ease;
        }
        .hp-tab .hp-count {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 22px; height: 20px; padding: 0 7px;
            font-size: 11px; font-weight: 700; line-height: 1;
            border-radius: 999px;
            background: #eef2f7; color: #475569;
            transition: color 0.22s ease, background-color 0.22s ease, transform 0.22s ease;
        }
        .hp-tab.active {
            margin-bottom: -1px;
            min-height: 48px;
            color: #111827;
            border: 1px solid var(--hp-tab-border);
            border-bottom-color: var(--hp-tab-bg);
            background: var(--hp-tab-bg);
            border-radius: 15px 15px 0 0;
            box-shadow: 0 -3px 8px rgba(15, 23, 42, 0.06);
            transform: none;
            z-index: 5;
            animation: hpTabFloat 0.22s ease both;
        }
        .hp-tab.active::before,
        .hp-tab.active::after {
            content: "";
            position: absolute;
            bottom: -1px;
            width: 14px;
            height: 14px;
            background: transparent;
            pointer-events: none;
            z-index: -1;
        }
        .hp-tab.active::before {
            left: -14px;
            border-bottom-right-radius: 14px;
            box-shadow: 6px 6px 0 var(--hp-tab-bg);
        }
        .hp-tab.active::after {
            right: -14px;
            border-bottom-left-radius: 14px;
            box-shadow: -6px 6px 0 var(--hp-tab-bg);
        }
        .hp-tab.active .hp-count { background: #111827; color: #ffffff; transform: scale(1.02); }
        .hp-tab.active .hp-dot { transform: scale(1.14); }
        @keyframes hpTabFloat {
            from { box-shadow: 0 0 0 rgba(15, 23, 42, 0); }
            to { box-shadow: 0 -3px 8px rgba(15, 23, 42, 0.06); }
        }
        /* idle dot colors per theme */
        .hp-tab.hp-c-all    { color: #0d4d8b; }
        .hp-tab.hp-c-green  { color: #059669; }
        .hp-tab.hp-c-yellow { color: #ca8a04; }
        .hp-tab.hp-c-amber  { color: #d97706; }
        .hp-tab.hp-c-orange { color: #ea580c; }
        .hp-tab.hp-c-red    { color: #dc2626; }
        .hp-tab.hp-c-grey   { color: #475569; }
        .hp-tab.hp-c-slate  { color: #64748b; }
        .hp-tab.hp-c-blue   { color: #2563eb; }
        .hp-tab.hp-c-purple { color: #7c3aed; }

        /* ===== Status Badge (in table) ===== */
        #page-content-wrapper .hp-badge {
            display: inline-block !important;
            padding: 4px 11px !important;
            border-radius: 999px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            line-height: 1.4 !important;
            white-space: nowrap !important;
        }
        #page-content-wrapper .hp-badge::before {
            content: "";
            display: inline-block;
            width: 7px; height: 7px; border-radius: 50%;
            background: currentColor;
            margin-right: 6px;
            vertical-align: middle;
        }
        #page-content-wrapper .hp-c-green.hp-badge  { color: #047857 !important; background: #d1fae5 !important; }
        #page-content-wrapper .hp-c-yellow.hp-badge { color: #a16207 !important; background: #fef9c3 !important; }
        #page-content-wrapper .hp-c-amber.hp-badge  { color: #b45309 !important; background: #fef3c7 !important; }
        #page-content-wrapper .hp-c-orange.hp-badge { color: #c2410c !important; background: #ffedd5 !important; }
        #page-content-wrapper .hp-c-red.hp-badge    { color: #b91c1c !important; background: #fee2e2 !important; }
        #page-content-wrapper .hp-c-grey.hp-badge   { color: #475569 !important; background: #e2e8f0 !important; }
        #page-content-wrapper .hp-c-slate.hp-badge  { color: #64748b !important; background: #f1f5f9 !important; }
        #page-content-wrapper .hp-c-blue.hp-badge   { color: #1d4ed8 !important; background: #dbeafe !important; }
        #page-content-wrapper .hp-c-purple.hp-badge { color: #6d28d9 !important; background: #ede9fe !important; }

        /* ===== Filter panel (modernized) ===== */
        .invoice-filter-panel {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin: 0 0 20px;
            padding: 14px 16px;
            background: #ffffff;
            border: 1px solid #eef1f6;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        }
        .invoice-filter-item {
            flex: 1;
            min-width: 150px;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .search-item { flex: 1.5; min-width: 250px; }
        .search-item i {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 1;
            font-size: 14px;
        }
        .search-item input { padding-left: 38px !important; }
        .invoice-filter-item input, .invoice-filter-item select {
            width: 100%;
            height: 42px;
            padding: 8px 14px;
            border: 1px solid #d8dee9;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 14px;
            background-color: #fff;
            color: #0f172a;
            transition: all 0.2s;
        }
        .invoice-filter-item input::placeholder { color: #94a3b8; }
        .invoice-filter-item select option { color: #0f172a; background-color: #fff; }
        .invoice-filter-item input:focus, .invoice-filter-item select:focus {
            border-color: #0d4d8b;
            outline: none;
            box-shadow: 0 0 0 4px rgba(13, 77, 139, 0.12);
            background-color: #fff;
        }

        .btn-report-export {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            margin: 0;
            padding: 0 22px;
            box-sizing: border-box;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.2px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: none;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        .btn-solid-primary {
            background: linear-gradient(135deg, #0d4d8b 0%, #1468b3 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.25);
        }
        .btn-solid-primary:hover { box-shadow: 0 8px 20px rgba(13, 77, 139, 0.32); transform: translateY(-1px); }
        .btn-solid-primary:active { transform: translateY(0); }

        /* ===== Modern table ===== */
        .table-responsive {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
            border: 1px solid #eef1f6;
            background: #ffffff;
        }
        #tab_customer_list { margin-bottom: 0 !important; border-collapse: separate !important; border-spacing: 0; border: 0 !important; }
        #tab_customer_list small { font-size: 13.5px !important; font-weight: 500; color: #334155; }
        #tab_customer_list thead th {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
            border: 0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 15px 12px !important;
            vertical-align: middle;
            position: sticky; top: 0; z-index: 2;
        }
        #tab_customer_list thead th small, #tab_customer_list thead th a {
            color: #0f172a !important;
            text-decoration: none !important;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        #tab_customer_list tbody td {
            padding: 12px !important;
            border-top: 0 !important;
            border-right: 0 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            vertical-align: middle;
        }
        #tab_customer_list tbody tr { transition: background 0.15s ease; }
        #tab_customer_list tbody tr:hover { background-color: #f1f5f9 !important; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: #fcfdff; }
        .customerAccount { cursor: pointer; }
        #tab_customer_list textarea {
            width: 100%; border: 1px solid #e2e8f0; border-radius: 8px;
            padding: 6px 8px; font-size: 13px; color: #334155; resize: vertical;
        }
        #tab_customer_list .setHandler {
            border: 1px solid #d8dee9; border-radius: 8px; height: 36px; font-size: 13px;
        }

        #tab_customer_list_filter { display: none; }

        /* Datepicker above sticky headers */
        .ui-datepicker { z-index: 100000 !important; }

        @media (max-width: 992px) {
            .hp-tab { padding-left: 15px; padding-right: 15px; }
        }
        @media (max-width: 768px) {
            .invoice-filter-item, .search-item { min-width: 100%; }
            .hp-tabs-nav {
                margin-bottom: 16px;
                padding-top: 7px;
            }
            .hp-tab {
                min-height: 40px;
                font-size: 11px;
                padding: 10px 13px 9px;
            }
            .hp-tab.active {
                min-height: 45px;
                border-radius: 13px 13px 0 0;
            }
        }
        @media (max-width: 480px) {
            .hp-tabs-nav { padding-top: 6px; }
            .hp-tab {
                gap: 6px;
                padding-left: 12px;
                padding-right: 12px;
            }
            .hp-tab .hp-count {
                min-width: 20px;
                height: 18px;
                padding: 0 6px;
                font-size: 10px;
            }
        }

        /* =====================================================================
           PREMIUM DARK MODE  —  homePage.php
           Palette: deep charcoal surfaces (not pure black), soft slate text
           (not harsh white), and jewel-toned status accents.
           ===================================================================== */
        html.theme-dark {
            --hpd-bg:        #0a0e14;   /* page canvas — deep near-black       */
            --hpd-surface:   #111722;   /* cards / panels / table              */
            --hpd-surface-2: #161d2b;   /* raised: header row, hover, inputs   */
            --hpd-border:    #232c3d;   /* hairline dividers                   */
            --hpd-border-2:  #2e3a4f;   /* stronger borders / input outline    */
            --hpd-text:      #dbe3f0;   /* primary text — soft, easy on eyes   */
            --hpd-text-dim:  #93a1b8;   /* secondary / muted                   */
            --hpd-text-faint:#6b7890;   /* placeholders, icons                 */
            --hpd-accent:    #4a9eff;   /* brand blue, brightened for dark     */
        }

        html.theme-dark #page-content-wrapper {
            background: var(--hpd-bg);
            color: var(--hpd-text);
        }
        html.theme-dark .page-content.inset { background: var(--hpd-bg); }

        /* Section title */
        html.theme-dark .invoice-section-title { color: #f2f6fc; }

        /* ---- Status Tabs ---- */
        html.theme-dark .hp-tabs-nav {
            --hp-tab-border: #232c3d;
            --hp-tab-bg: var(--hpd-surface);
            --hp-tab-idle: #0e131c;
            border-bottom-color: var(--hpd-border);
        }
        html.theme-dark .hp-tabs-nav::-webkit-scrollbar-thumb { background: #2e3a4f; }
        html.theme-dark .hp-tab { color: var(--hpd-text-dim); }
        html.theme-dark .hp-tab:hover {
            background: var(--hpd-surface-2);
            color: #ffffff;
            box-shadow: inset 0 -1px 0 var(--hp-tab-border), 0 -2px 9px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark .hp-tab .hp-count { background: #1c2534; color: #b8c4d8; }
        html.theme-dark .hp-tab.active {
            color: #ffffff;
            background: var(--hpd-surface);
            box-shadow: 0 -3px 10px rgba(0, 0, 0, 0.5);
        }
        html.theme-dark .hp-tab.active .hp-count { background: var(--hpd-accent); color: #061020; }
        /* idle dot colors — brightened so they glow on dark */
        html.theme-dark .hp-tab.hp-c-all    { color: #5aa9ff; }
        html.theme-dark .hp-tab.hp-c-green  { color: #34d399; }
        html.theme-dark .hp-tab.hp-c-yellow { color: #facc15; }
        html.theme-dark .hp-tab.hp-c-amber  { color: #fbbf24; }
        html.theme-dark .hp-tab.hp-c-orange { color: #fb923c; }
        html.theme-dark .hp-tab.hp-c-red    { color: #f87171; }
        html.theme-dark .hp-tab.hp-c-grey   { color: #94a3b8; }
        html.theme-dark .hp-tab.hp-c-slate  { color: #94a3b8; }
        html.theme-dark .hp-tab.hp-c-blue   { color: #60a5fa; }
        html.theme-dark .hp-tab.hp-c-purple { color: #a78bfa; }

        /* ---- Status badges (soft translucent fills, luminous text) ---- */
        html.theme-dark #page-content-wrapper .hp-c-green.hp-badge  { color: #6ee7b7 !important; background: rgba(16, 185, 129, 0.15) !important; }
        html.theme-dark #page-content-wrapper .hp-c-yellow.hp-badge { color: #fde68a !important; background: rgba(202, 138, 4, 0.16) !important; }
        html.theme-dark #page-content-wrapper .hp-c-amber.hp-badge  { color: #fcd34d !important; background: rgba(217, 119, 6, 0.16) !important; }
        html.theme-dark #page-content-wrapper .hp-c-orange.hp-badge { color: #fdba74 !important; background: rgba(234, 88, 12, 0.16) !important; }
        html.theme-dark #page-content-wrapper .hp-c-red.hp-badge    { color: #fca5a5 !important; background: rgba(220, 38, 38, 0.16) !important; }
        html.theme-dark #page-content-wrapper .hp-c-grey.hp-badge   { color: #cbd5e1 !important; background: rgba(148, 163, 184, 0.14) !important; }
        html.theme-dark #page-content-wrapper .hp-c-slate.hp-badge  { color: #cbd5e1 !important; background: rgba(100, 116, 139, 0.16) !important; }
        html.theme-dark #page-content-wrapper .hp-c-blue.hp-badge   { color: #93c5fd !important; background: rgba(37, 99, 235, 0.16) !important; }
        html.theme-dark #page-content-wrapper .hp-c-purple.hp-badge { color: #c4b5fd !important; background: rgba(124, 58, 237, 0.16) !important; }

        /* ---- Filter panel ---- */
        html.theme-dark .invoice-filter-panel {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark .search-item i { color: var(--hpd-text-faint); }
        html.theme-dark .invoice-filter-item input,
        html.theme-dark .invoice-filter-item select {
            background-color: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark .invoice-filter-item input::placeholder { color: var(--hpd-text-faint); }
        html.theme-dark .invoice-filter-item select option { color: var(--hpd-text); background-color: var(--hpd-surface-2); }
        html.theme-dark .invoice-filter-item input:focus,
        html.theme-dark .invoice-filter-item select:focus {
            border-color: var(--hpd-accent);
            box-shadow: 0 0 0 4px rgba(74, 158, 255, 0.18);
            background-color: var(--hpd-surface-2);
        }
        html.theme-dark .btn-solid-primary {
            background: linear-gradient(135deg, #1d63b0 0%, #2f81d6 100%);
            box-shadow: 0 4px 14px rgba(20, 104, 179, 0.4);
        }
        html.theme-dark .btn-solid-primary:hover { box-shadow: 0 8px 22px rgba(20, 104, 179, 0.5); }

        /* ---- Data table ---- */
        html.theme-dark .table-responsive {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark #tab_customer_list thead th {
            background: linear-gradient(180deg, #161d2b 0%, #121926 100%) !important;
            border-bottom: 1px solid var(--hpd-border-2) !important;
        }
        html.theme-dark #tab_customer_list thead th small,
        html.theme-dark #tab_customer_list thead th a { color: #aeb9cc !important; }
        html.theme-dark #tab_customer_list small { color: var(--hpd-text-dim) !important; }
        html.theme-dark #tab_customer_list tbody td {
            border-bottom: 1px solid var(--hpd-border) !important;
            color: var(--hpd-text);
        }
        html.theme-dark #tab_customer_list tbody tr:hover { background-color: var(--hpd-surface-2) !important; }
        html.theme-dark .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(255, 255, 255, 0.018); }
        html.theme-dark #tab_customer_list a { color: #cdd6e5; }
        html.theme-dark #tab_customer_list textarea {
            background-color: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark #tab_customer_list textarea::placeholder { color: var(--hpd-text-faint); }
        html.theme-dark #tab_customer_list .setHandler {
            background-color: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" 
                <div class="row">
                    <div class="col-sm-12">
                        <h5 class="invoice-section-title">Client Management & Reports </h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 main_content">
                        <nav class="hp-tabs-nav" role="tablist" aria-label="Client Status Tabs">
                            <button type="button" class="hp-tab hp-c-all active" data-status="" role="tab" aria-selected="true">
                                <span class="hp-dot"></span> All Clients
                            </button>
                            <?php foreach ($tabStatuses as $st) {
                                $c = isset($statusColorMap[$st]) ? $statusColorMap[$st] : 'slate'; ?>
                                <button type="button" class="hp-tab hp-c-<?php echo $c ?>" data-status="<?php echo htmlspecialchars($st, ENT_QUOTES) ?>" role="tab" aria-selected="false">
                                    <span class="hp-dot"></span> <?php echo htmlspecialchars($st) ?>
                                </button>
                            <?php } ?>
                        </nav>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 main_content" style="">
                        <div class="invoice-filter-panel">
                            <div class="invoice-filter-item search-item">
                                <i class="fa fa-search"></i>
                                <input type="text" id="clientSearch" placeholder="Search by Client Name...">
                            </div>
                            <div class="invoice-filter-item">
                                <select name="service-type" class="service-type" id="service-type">
                                    <option value="">Select Service Type</option>
                                    <?php foreach ($servicesTypes as $service) { ?>
                                        <option value="<?php echo $service['service_type'] ?>">
                                            <?php echo $service['service_type'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="invoice-filter-item">
                                <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="Due From Date" value="">
                            </div>
                            <div class="invoice-filter-item">
                                <input type="text" class="filterToDate" id="filterToDate" name="filterToDate" placeholder="Due To Date" value="">
                            </div>
                            <div class="invoice-filter-item" style="flex: 0 0 160px;">
                                <button type="button" class="btn-report-export btn-solid-primary downloadHomeReport" style="width: 100%;">
                                    <i class="fa fa-cloud-download" style="font-size: 15px;"></i> Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row clearfix" >
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list" width="100%">
                            <thead>
                                <tr>
                                    <th class="" >
                                        <small>Sr.</small>
                                    </th>
                                    <th class="" >
                                        <small><a>Unique Id</a></small>
                                    </th>
                                    <th class="" >
                                        <small><a>Agreement Date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Name of Client</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Contact person </a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Current Status</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Next Ser due on date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Next Ser Type</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Hand</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Comments</a></small>
                                    </th>
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
                                    <tr id='addr<?php echo $customer['id'] ?>' data-status="<?php echo htmlspecialchars($normStatus, ENT_QUOTES) ?>" class="<?php echo ($customer['audit_status'] === 'Application canceled') ? 'canceled-row' : ''; ?>">
                                        <td data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $count ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><?php if (isset($customer['unique_id'])) echo $customer['unique_id'] ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $customer['certification_date'] ?></a></small>
                                        </td>
                                        <td class="customerAccount<?php echo ($customer['audit_status'] === 'Application canceled') ? ' canceled-status-cell' : ''; ?>" data-id="<?php echo $customer['id'] ?>" data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $customer['customer_name'] ?></a></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><?php echo $customer['contact_person'] ?></small>
                                        </td>
                                        <td class="customerAccount<?php echo ($customer['audit_status'] === 'Application canceled') ? ' canceled-status-cell' : ''; ?>" data-id="<?php echo $customer['id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><span class="hp-badge hp-c-<?php echo $statusColor ?>"><?php echo htmlspecialchars($normStatus); ?><?php if($normStatus === "In-process" && !empty($serviceInfo["service_type"])) echo ' ('. htmlspecialchars($serviceInfo["service_type"]).')'; ?></span></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><span><?php if (isset($dueInfo["due_date"]) && !empty($dueInfo["due_date"])) { echo simpleDateConversion($dueInfo["due_date"]); } else {echo simpleDateConversion($dueInfoex["due_date"]);} ?>~</span><?php if (isset($dueInfo["due_date"]) && !empty($dueInfo["due_date"])) { echo simpleDateConversion($dueInfo["due_date"]); } else {echo simpleDateConversion($dueInfoex["due_date"]);} ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><?php echo $customer['next_service']; ?></small>
                                        </td>
                                        <td data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;"> 
                                            <div class="float-label-control">                                                
                                                <select class="form-control setHandler" data-id="<?php echo $customer['id'] ?>" name="setHandler" style="color: black">
                                                    <?php
                                                    if (isset($customer["handler"]) && !empty($customer["handler"]) && isset($handlerMap[$customer["handler"]])) {
                                                        ?> <option value="<?php echo $customer["handler"]; ?>"><?php echo $handlerMap[$customer["handler"]]; ?></option> <?php } else {
                                                        ?> <option value="">Hand</option> <?php }
                                                    ?>                                                    
                                                    <?php foreach ($allUsers as $users) { ?>
                                                        <option value="<?php echo $users["id"] ?>"><?php echo $users["handler_name"] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </td>
                                        <td data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;"> 
                                            <div class="table-edit-control">  
                                            <!--<small><?php echo $customer['comment'] ?></small>-->
                                                <textarea rows="3" class="saveChanges" data-row="<?php echo $customer['id'] ?>" data-column="comment" name="payComments" value="" placeholder="" ><?php if (isset($customer['comment'])) echo $customer['comment'] ?></textarea>
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

        $('.downloadHomeReport').click(function () {
            var fromDate = $('#filterFromDate').val();
            var toDate = $('#filterToDate').val();
            var serviceType = $('#service-type').val();
            var clientName = $('#clientSearch').val();
            var url = 'exportHomeReport.php';
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                    '<input type="hidden" name="fromDate" value="' + fromDate + '" />' +
                    '<input type="hidden" name="toDate" value="' + toDate + '" />' +
                    '<input type="hidden" name="serviceType" value="' + serviceType + '" />' +
                    '<input type="hidden" name="clientName" value="' + clientName + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
            form.remove();
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
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": true,
        "pageLength": 50,
        "deferRender": true,
        "ordering": false,
        "lengthChange": false,
        "columnDefs": [
            {
                "width": "1%",
                "targets": 0
            },
            {
                "width": "5%",
                "targets": 1
            },
            {
                "width": "5%",
                "targets": 2
            },
            {
                "width": "10%",
                "targets": 3
            },
            {
                "width": "5%",
                "targets": 4
            },
            {
                "width": "5%",
                "targets": 5
            },
            {
                "width": "1%",
                "targets": 6
            },
                        {
                "width": "1%",
                "targets": 7
            },
            {
                "width": "72%",
                "targets": 9
            },
        ]
    });
    table.columns.adjust().draw();

    $('.service-type, .filterFromDate, .filterToDate').change(function () {
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

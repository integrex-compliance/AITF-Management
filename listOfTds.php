<div id="wrapper" class="active">

    <?php

    $pageIdentifier = "TDS list";
    include_once './header.php';
    include_once './dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    $status = $_GET['status'] ?? 'all';
    $startDateStr = $_GET['startDate'] ?? '';
    $endDateStr = $_GET['endDate'] ?? '';
    $clientSearch = $_GET['clientSearch'] ?? '';
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

    $allTds = "SELECT ci.id, customer_name,ci.unique_id,ps.customer_id,ps.service_type as tds, ps.amount_decided,ps.payment_received,ps.payment_date,ps.comment,ps.payment_id, svc.service_id, svc.service_type
               FROM customer_info ci
               left join payment_system ps ON ci.id=ps.customer_id
               left join service svc on ps.customer_id = svc.customer_id
               WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id)
               and ps.service_type LIKE '%tds%'";

    $params = [];
    if ($status == 'received') {
        $allTds .= " AND ps.payment_received > 0";
    } elseif ($status == 'not_received') {
        $allTds .= " AND (ps.payment_received IS NULL OR ps.payment_received = 0 OR ps.payment_received = '' OR ps.payment_received = '0.00' OR ps.payment_received = '0')";
    }

    if (!empty($clientSearch)) {
        $allTds .= " AND ci.customer_name LIKE :clientSearch";
        $params[':clientSearch'] = '%' . $clientSearch . '%';
    }

    if (!empty($startDate)) {
        $allTds .= " AND ps.payment_date >= :startDate";
        $params[':startDate'] = $startDate;
    }
    if (!empty($endDate)) {
        $allTds .= " AND ps.payment_date <= :endDate";
        $params[':endDate'] = $endDate;
    }

    if ($sortOrder == 'high') {
        $allTds .= " ORDER BY CAST(ps.amount_decided AS DECIMAL(10,2)) DESC";
    } elseif ($sortOrder == 'low') {
        $allTds .= " ORDER BY CAST(ps.amount_decided AS DECIMAL(10,2)) ASC";
    } else {
        $allTds .= " ORDER BY ci.id DESC";
    }

    $statement = $db->prepare($allTds);
    foreach ($params as $key => $val) {
        $statement->bindValue($key, $val);
    }
    $statement->execute();
    $listOfTds = $statement->fetchAll();

    // Also fetch TDS from new Statement system (statement_payments where tds_decided > 0)
    $newTdsQuery = "SELECT ci.id, ci.customer_name, ci.unique_id, sp.customer_id, 
                    CONCAT('TDS (Statement #', sp.id, ')') as tds, 
                    sp.tds_decided as amount_decided, sp.tds_received as payment_received,
                    sp.payment_date, sp.comment, sp.id as payment_id,
                    0 as service_id, sp.service_type
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
        $newTdsList = $stmtNew->fetchAll();
        $listOfTds = array_merge($listOfTds, $newTdsList);
    } catch (PDOException $e) {
        // If statement_payments table doesn't exist yet, silently skip
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
            overflow: hidden;
        }

        #table_finance_details thead th {
            background: #0f2744 !important;
            color: #ffffff !important;
            padding: 12px 14px !important;
            border: none !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            vertical-align: middle !important;
        }

        #table_finance_details thead th small,
        #table_finance_details thead th a {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 12px !important;
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

        /* Unique ID column */
        #table_finance_details tbody td:first-child small a {
            font-weight: 700;
            color: #475569;
            font-family: monospace;
            font-size: 12px;
            text-decoration: none;
            cursor: pointer;
        }

        /* Client Name click style */
        #table_finance_details tbody td:nth-child(2) small {
            font-weight: 700;
            color: #0d4d8b;
            cursor: pointer;
            transition: color 0.15s ease;
            font-size: 12.5px;
        }

        #table_finance_details tbody td:nth-child(2):hover small {
            color: #0284c7;
            text-decoration: underline;
        }

        /* Service Type column badge */
        #table_finance_details tbody td:nth-child(3) small {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            display: inline-block;
        }

        /* TDS Deducted Amount */
        #table_finance_details tbody td:nth-child(4) small {
            color: #d97706;
            font-weight: 800;
            font-size: 13px;
        }

        /* Payment Received Amount */
        #table_finance_details tbody td:nth-child(5) small {
            color: #059669;
            font-weight: 800;
            font-size: 13px;
        }

        /* Date column */
        #table_finance_details tbody td:nth-child(6) small {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 11.5px;
            display: inline-block;
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
        html.theme-dark #table_finance_details thead th {
            background: #090e17 !important;
            border-color: var(--tds-border-2) !important;
        }
        html.theme-dark #table_finance_details thead th small,
        html.theme-dark #table_finance_details thead th a { color: #f1f5f9 !important; }
        html.theme-dark #table_finance_details small { color: var(--tds-text) !important; }
        html.theme-dark #table_finance_details tbody td {
            border-color: var(--tds-border) !important;
            color: var(--tds-text) !important;
            background-color: var(--tds-surface) !important;
        }
        html.theme-dark #table_finance_details tbody tr:hover td { background-color: var(--tds-surface-2) !important; }
        html.theme-dark .table-striped tbody tr:nth-of-type(odd) td { background-color: rgba(255, 255, 255, 0.02) !important; }
        html.theme-dark #table_finance_details tbody td:first-child small a { color: #94a3b8 !important; }
        html.theme-dark #table_finance_details tbody td:nth-child(2) small { color: #38bdf8 !important; }
        html.theme-dark #table_finance_details tbody td:nth-child(3) small { background: rgba(56, 189, 248, 0.15); color: #7dd3fc; }
        html.theme-dark #table_finance_details tbody td:nth-child(4) small { color: #fbbf24 !important; }
        html.theme-dark #table_finance_details tbody td:nth-child(5) small { color: #34d399 !important; }
        html.theme-dark #table_finance_details tbody td:nth-child(6) small { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; }

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
                                <h2 class="invoice-section-title"><i class="fa fa-percent"></i> TDS List & Reports</h2>
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
                                            <input type="text" name="clientSearch" id="clientSearch" value="<?php echo $clientSearch; ?>" placeholder="Search Client..." onkeyup="if(event.keyCode == 13) $('#filterForm').submit()">
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
                                                <i class="fa fa-cloud-download" style="font-size: 15px;"></i> Download Report
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
                                                    <th class=""  >
                                                        <small><a>Unique ID</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Client Name</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Service Type</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>TDS amount deducted</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Payment received</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Date</a></small>
                                                    </th>
                                                    <th class="" width="10" >
                                                        <small><a>TDS certificate details</a></small>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($listOfTds as $tds) { ?>
                                                    <tr id='addr0' data-id="<?php echo $tds['customer_id'] ?>">
                                                        <td data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><a data-id="<?php echo $tds['customer_id'] ?>" class="customerAccount"><?php echo $tds['unique_id'] ?></a></small>
                                                        </td>

                                                        <td  class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $tds['customer_name'] ?></small>
                                                        </td>
                                                        <td   class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $tds['tds'] ?></small>
                                                        </td>
                                                        <td  class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $tds['amount_decided'] ?></small>
                                                        </td>
                                                        <td  class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><?php echo $tds['payment_received'] ?></small>
                                                        </td>
                                                        <td   class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><?php if (isset($tds['payment_date']) && !empty($tds['payment_date'])) {
                                                    echo date("d.m.Y", strtotime($tds['payment_date']));
                                                } ?></small>
                                                        </td>
                                                        <td data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <div class="table-edit-control">
                                                                <textarea rows="3" class="saveChanges" data-row="<?php echo $tds['payment_id'] ?>" data-column="comment" name="payComments" value="" placeholder="" ><?php if (isset($tds['comment'])) echo $tds['comment'] ?></textarea>
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
                        $('#filterForm').submit();
                    }
                });
            }
        }, 100);

        var table = $('#table_finance_details').DataTable({
            "scrollY": '70vh',
            "scrollCollapse": true,
            "paging": false,
            "ordering": false,
            "lengthChange": false,
            "columnDefs": [
                { "width": "10%", "targets": 0 },
                { "width": "15%", "targets": 1 },
                { "width": "15%", "targets": 2 },
                { "width": "12%", "targets": 3 },
                { "width": "10%", "targets": 4 },
            ]
        });

        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
            table.draw();
        });

        $('.saveChanges').change(function (e) {
            saveData($(this));
        });

        function saveData($selector) {
            var checkedValue = $selector.val();
            $.ajax({
                url: "deleteFinalInvoice.php",
                type: "GET",
                data: {
                    id: $selector.attr('data-row'),
                    columnName: $selector.attr('data-column'),
                    columnValue: checkedValue,
                },
                success: function (data) {
                    // Success logic
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

        table.columns.adjust().draw();
    });
</script>

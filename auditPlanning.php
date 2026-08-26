<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->

    <?php
    $pageIdentifier = "Audit Planning";
    include 'header.php';
    include 'dbConnect.php';

    //    $auditPlanninginfo = "SELECT distinct ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, comment, certification_stage_date, add_to, cas.ca_id, service_ca_status, cas.customer_id, saia.sai_id, saia.customer_id,saia.service_sai_status, saiis.saii_id, saiis.customer_id, saiis.service_saii_ststus, rcs.rc_id, rcs.rc_customer, rcs.service_rc_status, sps.sp_id, sps.sp_customer, sps.service_sp_status FROM customer_info ci left join service_type_ca cas on ci.id = cas.customer_id left join service_type_sai saia on ci.id = saia.customer_id left join service_type_saii saiis on ci.id = saiis.customer_id left join service_type_rc rcs on ci.id = rcs.rc_customer left join service_type_sp sps on ci.id = sps.sp_customer WHERE ((cas.service_ca_status = 'Audit planning' and cas.ca_id = (select max(tmp.ca_id) from service_type_ca tmp where tmp.customer_id = ci.id)) or (saia.service_sai_status = 'Audit planning' and saia.sai_id = (select max(tmp1.sai_id) from service_type_sai tmp1 where tmp1.customer_id = ci.id) ) or (saiis.service_saii_ststus = 'Audit planning' and saiis.saii_id = (select max(tmp2.saii_id) from service_type_saii tmp2 where tmp2.customer_id = ci.id)) or (rcs.service_rc_status = 'Audit planning' and rcs.rc_id = (select max(tmp3.rc_id) from service_type_rc tmp3 where tmp3.rc_customer = ci.id) ) or (sps.service_sp_status = 'Audit planning' and sps.sp_id = (select max(tmp4.sp_id) from service_type_sp tmp4 where tmp4.sp_customer = ci.id)) )";
//    $auditPlanninginfo = "SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, service_type, service_status, svcdt.action, due_date, sent_date, svcdt.comment FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and svc.service_status = 'Audit planning' and svcdt.action = '1st Remainder' ";
//    $auditPlanninginfo = "SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, service_type, service_status FROM customer_info ci left join service svc on ci.id = svc.customer_id WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and svc.service_status = 'Audit planning'";
    $auditPlanninginfo = "SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, svc.service_type, service_status, 'sa1' as stage1 FROM customer_info ci left join service svc on ci.id = svc.customer_id  WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and svc.service_status = 'Audit planning' union SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, svc.service_type, service_status, 'sa2' as stage1 FROM customer_info ci left join service svc on ci.id = svc.customer_id WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and svc.service_status = 'Audit planning'";
    $statement = $db->prepare($auditPlanninginfo);
    $statement->execute();
    $auditPlanningInfo = $statement->fetchAll();

    $auditorSql = "SELECT auditors_id, auditor_name FROM auditors ORDER BY auditor_name ASC";
    $auditorStmt = $db->prepare($auditorSql);
    $auditorStmt->execute();
    $auditorList = $auditorStmt->fetchAll(PDO::FETCH_ASSOC);
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
    ?>
    <style>
        /* Base typography & resets */
        #page-content-wrapper {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .custom-float-label-control {
            margin-bottom: 0em;
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
            align-items: center;
            margin: 10px 0 20px;
            padding: 14px 18px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .invoice-filter-item {
            flex: 1;
            min-width: 140px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Search Box Specific Styling */
        .search-item {
            flex: 1.5;
            min-width: 220px;
        }

        .search-item i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #0d4d8b;
            z-index: 1;
            font-size: 14px;
        }

        .search-item input {
            padding-left: 35px !important;
        }

        .invoice-filter-item input,
        .invoice-filter-item select {
            width: 100%;
            height: 40px;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 13px;
            font-weight: 500;
            background-color: #fff;
            color: #1e293b;
            transition: all 0.2s ease;
        }

        .invoice-filter-item input::placeholder {
            color: #94a3b8;
        }

        .invoice-filter-item input:focus,
        .invoice-filter-item select:focus {
            border-color: #0d4d8b;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12);
        }

        .btn-report-export {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 40px;
            margin: 0;
            padding: 0 20px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.2px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: none;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .btn-solid-primary {
            background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(13, 77, 139, 0.25);
        }

        .btn-solid-primary:hover {
            background: linear-gradient(135deg, #11599e 0%, #0a467e 100%);
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.35);
            transform: translateY(-1px);
            color: #ffffff;
        }

        .btn-solid-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(13, 77, 139, 0.2);
        }

        #tab_customer_list_filter {
            display: none;
        }

        /* Table Shell & DataTable Styling */
        .table-responsive {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        #tab_customer_list thead th {
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

        #tab_customer_list thead th small,
        #tab_customer_list thead th a {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            text-decoration: none !important;
        }

        #tab_customer_list tbody td {
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 10px 14px !important;
            font-size: 12.5px !important;
            color: #334155 !important;
            background-color: #ffffff;
            vertical-align: middle !important;
        }

        #tab_customer_list tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        #tab_customer_list tbody tr:nth-of-type(odd) td {
            background-color: #fcfdfe;
        }

        /* Unique ID column */
        #tab_customer_list tbody td:first-child small {
            font-weight: 700;
            color: #475569;
            font-family: monospace;
            font-size: 12px;
        }

        /* Client Name click style */
        #tab_customer_list tbody td:nth-child(2) small {
            font-weight: 700;
            color: #0d4d8b;
            cursor: pointer;
            transition: color 0.15s ease;
            font-size: 12.5px;
        }

        #tab_customer_list tbody td:nth-child(2):hover small {
            color: #0284c7;
            text-decoration: underline;
        }

        /* Service Type column badge */
        #tab_customer_list tbody td:nth-child(3) small {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            display: inline-block;
        }

        /* Audit Date column badge */
        #tab_customer_list tbody td:nth-child(4) small {
            background: #fef3c7;
            color: #92400e;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11.5px;
            display: inline-block;
        }

        /* Auditor name column */
        #tab_customer_list tbody td:nth-child(5) small {
            font-weight: 600;
            color: #334155;
        }

        /* Action Buttons */
        .downloadAuditNotification,
        .downloadAuditNotificationii {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: #ecfdf5 !important;
            color: #059669 !important;
            border-radius: 6px !important;
            border: 1px solid #a7f3d0 !important;
            margin: 0 2px !important;
            text-decoration: none !important;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .downloadAuditNotification:hover,
        .downloadAuditNotificationii:hover {
            background: #059669 !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
        }

        .editAuditNotification,
        .editAuditNotificationii {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: #eff6ff !important;
            color: #2563eb !important;
            border-radius: 6px !important;
            border: 1px solid #bfdbfe !important;
            margin: 0 2px !important;
            text-decoration: none !important;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .editAuditNotification:hover,
        .editAuditNotificationii:hover {
            background: #2563eb !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
        }

        /* =====================================================================
           PREMIUM DARK MODE  —  auditPlanning.php
           ===================================================================== */
        html.theme-dark {
            --ap-bg:        #0a0e14;
            --ap-surface:   #111722;
            --ap-surface-2: #161d2b;
            --ap-border:    #232c3d;
            --ap-border-2:  #2e3a4f;
            --ap-text:      #dbe3f0;
            --ap-text-dim:  #93a1b8;
            --ap-text-faint:#6b7890;
            --ap-accent:    #4a9eff;
        }

        html.theme-dark #page-content-wrapper { background: var(--ap-bg); color: var(--ap-text); }

        /* Section title */
        html.theme-dark .invoice-section-title {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
            color: #ffffff;
        }

        /* Filter panel */
        html.theme-dark .invoice-filter-panel {
            background: var(--ap-surface);
            border-color: var(--ap-border);
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark .search-item i { color: #7aa9f5; }
        html.theme-dark .invoice-filter-item input,
        html.theme-dark .invoice-filter-item select {
            background-color: var(--ap-surface-2) !important;
            border-color: var(--ap-border-2) !important;
            color: var(--ap-text) !important;
        }
        html.theme-dark .invoice-filter-item input::placeholder { color: var(--ap-text-faint); }
        html.theme-dark .invoice-filter-item input:focus,
        html.theme-dark .invoice-filter-item select:focus {
            border-color: var(--ap-accent);
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.18);
        }

        /* Download button */
        html.theme-dark .btn-solid-primary {
            background: linear-gradient(135deg, #1d63b0 0%, #174e8c 100%);
            box-shadow: 0 4px 14px rgba(20, 104, 179, 0.4);
        }
        html.theme-dark .btn-solid-primary:hover { 
            background: linear-gradient(135deg, #2f81d6 0%, #1d63b0 100%); 
            box-shadow: 0 8px 22px rgba(20, 104, 179, 0.5); 
        }

        /* Table shell */
        html.theme-dark .table-responsive {
            background: var(--ap-surface);
            border: 1px solid var(--ap-border);
            border-radius: 12px;
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark #tab_customer_list thead th {
            background: #090e17 !important;
            border-color: var(--ap-border-2) !important;
        }
        html.theme-dark #tab_customer_list thead th small,
        html.theme-dark #tab_customer_list thead th a { color: #f1f5f9 !important; }
        html.theme-dark #tab_customer_list small { color: var(--ap-text) !important; }
        html.theme-dark #tab_customer_list tbody td {
            border-color: var(--ap-border) !important;
            color: var(--ap-text) !important;
            background-color: var(--ap-surface) !important;
        }
        html.theme-dark #tab_customer_list tbody tr:hover td { background-color: var(--ap-surface-2) !important; }
        html.theme-dark .table-striped tbody tr:nth-of-type(odd) td { background-color: rgba(255, 255, 255, 0.02) !important; }
        html.theme-dark #tab_customer_list tbody td:first-child small { color: #94a3b8 !important; }
        html.theme-dark #tab_customer_list tbody td:nth-child(2) small { color: #38bdf8 !important; }
        html.theme-dark #tab_customer_list tbody td:nth-child(3) small { background: rgba(56, 189, 248, 0.15); color: #7dd3fc; }
        html.theme-dark #tab_customer_list tbody td:nth-child(4) small { background: rgba(245, 158, 11, 0.15); color: #fcd34d; }
        html.theme-dark #tab_customer_list tbody td:nth-child(5) small { color: #e2e8f0 !important; }

        html.theme-dark .downloadAuditNotification,
        html.theme-dark .downloadAuditNotificationii {
            background: rgba(16, 185, 129, 0.15) !important;
            color: #34d399 !important;
            border-color: rgba(16, 185, 129, 0.3) !important;
        }
        html.theme-dark .downloadAuditNotification:hover,
        html.theme-dark .downloadAuditNotificationii:hover {
            background: #059669 !important;
            color: #ffffff !important;
        }

        html.theme-dark .editAuditNotification,
        html.theme-dark .editAuditNotificationii {
            background: rgba(59, 130, 246, 0.15) !important;
            color: #60a5fa !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }
        html.theme-dark .editAuditNotification:hover,
        html.theme-dark .editAuditNotificationii:hover {
            background: #2563eb !important;
            color: #ffffff !important;
        }
    </style>

    <div id="page-content-wrapper">

        <div class="page-content inset">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <h5 class="invoice-section-title"><i class="fa fa-calendar-check-o"></i> Audit Planning & Reports</h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 main_content" style="">
                        <div class="invoice-filter-panel">
                            <div class="invoice-filter-item search-item">
                                <i class="fa fa-search"></i>
                                <input type="text" id="auditClientSearch" placeholder="Search by Client Name...">
                            </div>
                            <div class="invoice-filter-item">
                                <select id="auditorFilter" class="form-control"
                                    style="color: #333; background-color: #fff;">
                                    <option value="">Select Auditor</option>
                                    <?php foreach ($auditorList as $auditor) { ?>
                                        <option value="<?php echo htmlspecialchars($auditor['auditor_name']); ?>">
                                            <?php echo htmlspecialchars($auditor['auditor_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="invoice-filter-item">
                                <select id="serviceTypeFilter" class="form-control"
                                    style="color: #333; background-color: #fff;">
                                    <option value="">Select Service Type</option>
                                    <option value="CA">CA</option>
                                    <option value="SA-I">SA-I</option>
                                    <option value="SA-II">SA-II</option>
                                    <option value="RC">RC</option>
                                    <option value="SP">SP</option>
                                </select>
                            </div>
                            <div class="invoice-filter-item">
                                <input type="text" class="filterFromDate" id="filterFromDate" placeholder="Date From">
                            </div>
                            <div class="invoice-filter-item">
                                <input type="text" class="filterToDate" id="filterToDate" placeholder="Date To">
                            </div>
                            <div class="invoice-filter-item" style="flex: 0 0 160px;">
                                <button type="button" class="btn-report-export btn-solid-primary downloadAuditReport"
                                    style="width: 100%;">
                                    <i class="fa fa-cloud-download" style="font-size: 15px;"></i> Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list"
                            width="100%">
                            <thead>
                                <tr>
                                    <th class="">
                                        <small><a>Client unique id</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Client Name</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Service type</a></small>
                                    </th>
                                    <!--                                    <th class="">
                                        <small><a>Stage</a></small>
                                    </th>-->
                                    <th class="">
                                        <small><a>Audit date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Auditor</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>view details</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($auditPlanningInfo as $auditPlanningDetail) { ?>
                                    <?php
                                    $serviceid = "";
                                    $saidetails = "";
                                    $auditid = "";
                                    if ($auditPlanningDetail["service_type"] == "CA") {
                                        $sql = "SELECT ca_id FROM `service_type_ca` where service = :service";
                                        $statement = $db->prepare($sql);
                                        $statement->bindParam(":service", $auditPlanningDetail["service_id"]);
                                        $statement->execute();
                                        $svc = $statement->fetch();
                                        $serviceid = $svc["ca_id"];
                                    } else if ($auditPlanningDetail["service_type"] == "SA-I") {
                                        $sql = "SELECT sai_id FROM `service_type_sai` where service = :service";
                                        $statement = $db->prepare($sql);
                                        $statement->bindParam(":service", $auditPlanningDetail["service_id"]);
                                        $statement->execute();
                                        $svc = $statement->fetch();
                                        $serviceid = $svc["sai_id"];
                                    } else if ($auditPlanningDetail["service_type"] == "SA-II") {
                                        $sql = "SELECT saii_id FROM `service_type_saii` where service = :service";
                                        $statement = $db->prepare($sql);
                                        $statement->bindParam(":service", $auditPlanningDetail["service_id"]);
                                        $statement->execute();
                                        $svc = $statement->fetch();
                                        $serviceid = $svc["saii_id"];
                                    } else if ($auditPlanningDetail["service_type"] == "RC") {
                                        $sql = "SELECT rc_id FROM `service_type_rc` where service = :service";
                                        $statement = $db->prepare($sql);
                                        $statement->bindParam(":service", $auditPlanningDetail["service_id"]);
                                        $statement->execute();
                                        $svc = $statement->fetch();
                                        $serviceid = $svc["rc_id"];
                                    } else if ($auditPlanningDetail["service_type"] == "SP") {
                                        $sql = "SELECT sp_id FROM `service_type_sp` where service = :service";
                                        $statement = $db->prepare($sql);
                                        $statement->bindParam(":service", $auditPlanningDetail["service_id"]);
                                        $statement->execute();
                                        $svc = $statement->fetch();
                                        $serviceid = $svc["sp_id"];
                                    }

                                    if ($auditPlanningDetail['stage1'] == "sa1") {
                                        $sai = "SELECT `audit_stage_id`, `audit_dates`, `auditor_list` FROM `stage_oneaudit_id` WHERE `ca_service_id` = :id ";
                                        $statement = $db->prepare($sai);
                                        $statement->bindParam(":id", $serviceid);
                                        $statement->execute();
                                        $saidetails = $statement->fetch();
                                        $auditid = $saidetails["audit_stage_id"];
                                    } else if ($auditPlanningDetail['stage1'] == "sa2") {
                                        $saii = "SELECT `audit_stageii_id`,`audit_dates`, `auditor_list` FROM `stage_twoaudit_id` WHERE `service_id` = :id ";
                                        $statement = $db->prepare($saii);
                                        $statement->bindParam(":id", $serviceid);
                                        $statement->execute();
                                        $saidetails = $statement->fetch();
                                        $auditid = $saidetails["audit_stageii_id"];
                                    }
                                    ?>
                                    <?php if ($saidetails['audit_dates']) { ?>
                                        <tr id='addr<?php echo $auditPlanningDetail['id'] ?>'>
                                            <td class="customerAccount" data-id="<?php echo $auditPlanningDetail['id'] ?>"
                                                data-name="name"
                                                style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditPlanningDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $auditPlanningDetail['id'] ?>"
                                                data-name="name"
                                                style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditPlanningDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $auditPlanningDetail['id'] ?>"
                                                data-name="name"
                                                style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditPlanningDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $auditPlanningDetail['id'] ?>"
                                                data-name="name"
                                                style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $saidetails['audit_dates'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $auditPlanningDetail['id'] ?>"
                                                data-name="name"
                                                style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $saidetails['auditor_list'] ?></small>
                                            </td>
                                            <td data-name="name"
                                                style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <?php if ($auditPlanningDetail['stage1'] == "sa1") { ?>
                                                    <a class="downloadAuditNotification pull-right"
                                                        data-customer="<?php echo $auditPlanningDetail['id'] ?>"
                                                        data-caservice-id="<?php echo $serviceid ?>"
                                                        data-audit="<?php echo $auditid ?>"
                                                        title="Download Release Audit Notification" style="padding: 1px 5px;">
                                                        <i class="fa fa-download fa-lg " style="padding: 3px"></i>
                                                    </a>
                                                    <?php if ($auditPlanningDetail["service_type"] == "CA") { ?>
                                                        <a class="editAuditNotification pull-right"
                                                            data-customer="<?php echo $auditPlanningDetail['id'] ?>"
                                                            data-service="<?php echo $serviceid ?>" data-type="service-type-sai"
                                                            data-notification="<?php echo $auditid ?>"
                                                            title="Add Release Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($auditPlanningDetail["service_type"] == "SA-I") { ?>
                                                        <a class="editAuditNotification pull-right"
                                                            data-customer="<?php echo $auditPlanningDetail['id'] ?>"
                                                            data-service="<?php echo $serviceid ?>" data-type="service-type-sai"
                                                            data-notification="<?php echo $auditid ?>"
                                                            title="Add Release Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($auditPlanningDetail["service_type"] == "SA-II") { ?>
                                                        <a class="editAuditNotification pull-right"
                                                            data-customer="<?php echo $auditPlanningDetail['id'] ?>"
                                                            data-service="<?php echo $serviceid ?>"
                                                            data-notification="<?php echo $auditid ?>"
                                                            title="Update Release Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($auditPlanningDetail["service_type"] == "RC") { ?>
                                                        <a class="editAuditNotification pull-right"
                                                            data-customer="<?php echo $auditPlanningDetail['id'] ?>"
                                                            data-service="<?php echo $serviceid ?>" data-type="service-type-sai"
                                                            data-notification="<?php echo $auditid ?>"
                                                            title="Add Release - Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($auditPlanningDetail["service_type"] == "SP") { ?>
                                                        <a class=" editAuditNotification pull-right"
                                                            data-customer="<?php echo $auditPlanningDetail['id'] ?>"
                                                            data-service="<?php echo $serviceid ?>"
                                                            data-notification="<?php echo $auditid ?>"
                                                            title="Update Release Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                <?php } else if ($auditPlanningDetail['stage1'] == "sa2") { ?>
                                                        <a class="downloadAuditNotificationii pull-right"
                                                            data-customer="<?php echo $auditPlanningDetail['id'] ?>"
                                                            data-caservice-id="<?php echo $serviceid ?>"
                                                            data-audit="<?php echo $auditid ?>"
                                                            title="Download Release Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-download fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php if ($auditPlanningDetail["service_type"] == "CA") { ?>
                                                            <a class="editAuditNotificationii pull-right"
                                                                data-customer="<?php echo $auditPlanningDetail['id'] ?>"
                                                                data-service="<?php echo $serviceid ?>"
                                                                data-notification="<?php echo $auditid ?>" title="Edit Audit Notification"
                                                                style="padding: 1px 5px;">
                                                                <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                            </a>
                                                    <?php } ?>
                                                    <?php if ($auditPlanningDetail["service_type"] == "RC") { ?>
                                                            <a class="editAuditNotification pull-right"
                                                                data-customer="<?php echo $auditPlanningDetail['id'] ?>"
                                                                data-service="<?php echo $serviceid ?>" data-type="service-type-sai"
                                                                data-notification="<?php echo $auditid ?>"
                                                                title="Add Release - Audit Notification" style="padding: 1px 5px;">
                                                                <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                            </a>
                                                    <?php } ?>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
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

    function compareMinDates(filterDate, rowDate) {
        if (!rowDate) return false;
        var filterDateSplit = filterDate.split(".");
        var rowDateSplit = rowDate.split(".");
        if (filterDateSplit[2] < rowDateSplit[2]) return true;
        if (filterDateSplit[2] == rowDateSplit[2]) {
            if (filterDateSplit[1] < rowDateSplit[1]) return true;
            if (filterDateSplit[1] == rowDateSplit[1]) {
                if (filterDateSplit[0] <= rowDateSplit[0]) return true;
            }
        }
        return false;
    }

    function compareMaxDates(filterDate, rowDate) {
        if (!rowDate) return false;
        var filterDateSplit = filterDate.split(".");
        var rowDateSplit = rowDate.split(".");
        if (filterDateSplit[2] > rowDateSplit[2]) return true;
        if (filterDateSplit[2] == rowDateSplit[2]) {
            if (filterDateSplit[1] > rowDateSplit[1]) return true;
            if (filterDateSplit[1] == rowDateSplit[1]) {
                if (filterDateSplit[0] >= rowDateSplit[0]) return true;
            }
        }
        return false;
    }

    $.fn.DataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var fromDate = $('#filterFromDate').val();
            var toDate = $('#filterToDate').val();
            var auditor = $('#auditorFilter').val().toLowerCase();
            var svcType = $('#serviceTypeFilter').val().toLowerCase();

            var rowDate = data[3] || ""; // Column 3 is Audit date
            var rowSvc = data[2].toLowerCase() || ""; // Column 2 is Service type
            var rowAuditor = data[4].toLowerCase() || ""; // Column 4 is Auditor

            var isfromDate = fromDate == "" || compareMinDates(fromDate, rowDate);
            var isToDate = toDate == "" || compareMaxDates(toDate, rowDate);
            var isAuditor = auditor == "" || rowAuditor.indexOf(auditor) !== -1;
            var isSvc = svcType == "" || rowSvc.indexOf(svcType) !== -1;

            return isfromDate && isToDate && isAuditor && isSvc;
        }
    );

    $(document).ready(function () {

        var debouncedSearch = debounce(function () {
            table.column(1).search($('#auditClientSearch').val()).draw();
        }, 200);

        $('#auditClientSearch').on('input', debouncedSearch);

        $('#auditorFilter, #serviceTypeFilter, #filterFromDate, #filterToDate').on('input change keyup', function () {
            table.draw();
        });

        $('#filterFromDate, #filterToDate').datepicker({
            dateFormat: 'dd.mm.yy',
            changeMonth: true,
            changeYear: true
        });

        $('.downloadAuditReport').click(function () {
            var fromDate = $('#filterFromDate').val();
            var toDate = $('#filterToDate').val();
            var auditor = $('#auditorFilter').val();
            var serviceType = $('#serviceTypeFilter').val();
            var clientName = $('#auditClientSearch').val();
            var url = 'exportAuditPlanningReport.php';
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                '<input type="hidden" name="fromDate" value="' + fromDate + '" />' +
                '<input type="hidden" name="toDate" value="' + toDate + '" />' +
                '<input type="hidden" name="auditor" value="' + auditor + '" />' +
                '<input type="hidden" name="serviceType" value="' + serviceType + '" />' +
                '<input type="hidden" name="clientName" value="' + clientName + '" />' +
                '</form>');
            $('body').append(form);
            form.submit();
            form.remove();
        });

        $('.downloadAuditNotificationii').click(function () {
            var notificationid = $(this).attr('data-audit');
            var url = 'AuditNotificationStage2.php';
            var form = $('<form action="' + url + '" method="post" target="_blank" >' +
                '<input type="hidden" name="notificationid" value="' + notificationid + '" />' +
                '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.downloadAuditNotification').click(function () {
            //                        alert($(this).attr('data-customer'));
            //                        alert($(this).attr('data-audit'));
            //                        alert($(this).attr('data-caservice-id'));
            var auditid = $(this).attr('data-audit');
            var service = $(this).attr('data-caservice-id');
            var customer = $(this).attr('data-customer');
            var url = 'AuditNotificationandAssignment.php';
            var form = $('<form action="' + url + '" method="post" target="_blank" >' +
                '<input type="hidden" name="auditid" value="' + auditid + '" />' +
                '<input type="hidden" name="serviceid" value="' + service + '" />' +
                '<input type="hidden" name="customerid" value="' + customer + '" />' +
                '</form>');
            $('body').append(form);
            form.submit();
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
        "paging": false,
        "ordering": false,
        "lengthChange": false,
        //            "pageLength": 10,
        "paging": false,
        "columnDefs": [
            {
                "width": "10%",
                "targets": 0
            },
            {
                "width": "15%",
                "targets": 1
            },
            {
                "width": "15%",
                "targets": 2
            },
            {
                "width": "12%",
                "targets": 3
            },
            {
                "width": "10%",
                "targets": 4
            },
        ]
        //        "paging":         false
    });
</script>

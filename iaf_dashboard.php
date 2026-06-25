<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "IAF Dashboard";
    include 'header.php';
    if (!isset($db)) {
        include_once './dbConnect.php';
    }
    require_once __DIR__ . '/include/iaf_certsearch_helper.php';

    $query = "SELECT
        c.id as internal_client_id,
        c.customer_guid,
        c.customer_name,
        c.country,
        c.address,
        c.scope,
        c.audit_status as certification_status,
        cp.certificate_id as certificate_tracking_id,
        cp.certificate_no as certificate_number,
        cp.registration_date as certificate_issue_date,
        cp.registration_date as issue_date,
        cp.registration_date as original_issue_date,
        cp.valid_until as expiry_date,
        cp.valid_until as expire_date,
        cp.certificate_scope,
        COALESCE(NULLIF(aa.proposed_scope, ''), cp.certificate_scope, c.scope) as certification_scope,
        COALESCE(
            (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req INNER JOIN service svc_req ON aa_req.uni_svc_id = svc_req.service_id OR aa_req.service_id = svc_req.service_id WHERE svc_req.customer_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
            (SELECT NULLIF(TRIM(aa_req.review_accredition), '') FROM audit_application aa_req INNER JOIN service svc_req ON aa_req.uni_svc_id = svc_req.service_id OR aa_req.service_id = svc_req.service_id WHERE svc_req.customer_id = c.id AND TRIM(aa_req.review_accredition) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
            NULLIF(TRIM(aa.accereditation_requirement), ''),
            NULLIF(TRIM(aa.review_accredition), '')
        ) as accreditation_body_name,
        COALESCE(
            (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req INNER JOIN service svc_req ON aa_req.uni_svc_id = svc_req.service_id OR aa_req.service_id = svc_req.service_id WHERE svc_req.customer_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
            (SELECT NULLIF(TRIM(aa_req.review_accredition), '') FROM audit_application aa_req INNER JOIN service svc_req ON aa_req.uni_svc_id = svc_req.service_id OR aa_req.service_id = svc_req.service_id WHERE svc_req.customer_id = c.id AND TRIM(aa_req.review_accredition) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
            NULLIF(TRIM(aa.accereditation_requirement), ''),
            NULLIF(TRIM(aa.review_accredition), '')
        ) as accreditation_body,
        COALESCE(
            (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req INNER JOIN service svc_req ON aa_req.uni_svc_id = svc_req.service_id OR aa_req.service_id = svc_req.service_id WHERE svc_req.customer_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
            NULLIF(TRIM(aa.accereditation_requirement), '')
        ) as accreditation_requirement,
        COALESCE(NULLIF(aa.assessment_standard, ''), (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id ORDER BY asi.audit_schedule_id DESC LIMIT 1)) as standard_name,
        c.schema_name,
        c.schema_name as scheme_name,
        aa.n_location as site
    FROM customer_info c
    LEFT JOIN certificate_print cp ON c.id = cp.customer_id
    LEFT JOIN service svc ON svc.customer_id = c.id AND svc.service_id = (SELECT MAX(tmp.service_id) FROM service tmp WHERE tmp.customer_id = c.id)
    LEFT JOIN audit_application aa ON aa.uni_svc_id = svc.service_id OR aa.service_id = svc.service_id
    GROUP BY c.id
    ORDER BY c.id DESC";

    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // If some columns like customer_guid or schema_name don't exist, we fallback to a safer query
        $query = "SELECT
        c.id as internal_client_id,
        c.customer_name,
        c.address,
        c.scope,
        c.audit_status as certification_status,
        cp.certificate_id as certificate_tracking_id,
        cp.certificate_no as certificate_number,
        cp.registration_date as certificate_issue_date,
        cp.registration_date as issue_date,
        cp.registration_date as original_issue_date,
        cp.valid_until as expiry_date,
        cp.valid_until as expire_date,
        cp.certificate_scope,
        COALESCE(NULLIF(aa.proposed_scope, ''), cp.certificate_scope, c.scope) as certification_scope,
        COALESCE(
            (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req INNER JOIN service svc_req ON aa_req.uni_svc_id = svc_req.service_id OR aa_req.service_id = svc_req.service_id WHERE svc_req.customer_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
            (SELECT NULLIF(TRIM(aa_req.review_accredition), '') FROM audit_application aa_req INNER JOIN service svc_req ON aa_req.uni_svc_id = svc_req.service_id OR aa_req.service_id = svc_req.service_id WHERE svc_req.customer_id = c.id AND TRIM(aa_req.review_accredition) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
            NULLIF(TRIM(aa.accereditation_requirement), ''),
            NULLIF(TRIM(aa.review_accredition), '')
        ) as accreditation_body_name,
        COALESCE(
            (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req INNER JOIN service svc_req ON aa_req.uni_svc_id = svc_req.service_id OR aa_req.service_id = svc_req.service_id WHERE svc_req.customer_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
            (SELECT NULLIF(TRIM(aa_req.review_accredition), '') FROM audit_application aa_req INNER JOIN service svc_req ON aa_req.uni_svc_id = svc_req.service_id OR aa_req.service_id = svc_req.service_id WHERE svc_req.customer_id = c.id AND TRIM(aa_req.review_accredition) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
            NULLIF(TRIM(aa.accereditation_requirement), ''),
            NULLIF(TRIM(aa.review_accredition), '')
        ) as accreditation_body,
        COALESCE(
            (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req INNER JOIN service svc_req ON aa_req.uni_svc_id = svc_req.service_id OR aa_req.service_id = svc_req.service_id WHERE svc_req.customer_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
            NULLIF(TRIM(aa.accereditation_requirement), '')
        ) as accreditation_requirement,
        COALESCE(NULLIF(aa.assessment_standard, ''), (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id ORDER BY asi.audit_schedule_id DESC LIMIT 1)) as standard_name,
        '' as schema_name,
        '' as scheme_name,
        aa.n_location as site
    FROM customer_info c
    LEFT JOIN certificate_print cp ON c.id = cp.customer_id
    LEFT JOIN service svc ON svc.customer_id = c.id AND svc.service_id = (SELECT MAX(tmp.service_id) FROM service tmp WHERE tmp.customer_id = c.id)
    LEFT JOIN audit_application aa ON aa.uni_svc_id = svc.service_id OR aa.service_id = svc.service_id
    GROUP BY c.id
    ORDER BY c.id DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ensure columns that may not exist have a fallback
    foreach ($clients as &$client) {
        $client = iafCertSearchNormalizeClientData($client);
        $client['certification_scope'] = iafCertSearchFirstValue($client, ['certification_scope', 'proposed_scope', 'certificate_scope', 'scope']);
        $client['scheme_name'] = iafCertSearchFirstValue($client, ['scheme_name', 'schema_name']);
        $client['accreditation_body'] = iafCertSearchFirstValue($client, ['accreditation_body', 'accreditation_body_name', 'accreditation_requirement', 'accereditation_requirement']);
        $client['certificate_issue_date'] = iafCertSearchFirstValue($client, ['certificate_issue_date', 'issue_date']);
        $client['expire_date'] = iafCertSearchFirstValue($client, ['expire_date', 'expiry_date']);
    }
    unset($client);

    ?>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <style>
        :root {
            --iaf-navbar-height: 0px;
            --iaf-sidebar-width: 180px;
            --iaf-sidebar-collapsed: 50px;
            --iaf-radius: 14px;
        }

        body {
            background-color: #f4f7f6;
            font-family: 'Inter', sans-serif;
        }

        body .navbar-bootsnipp {
            display: none !important;
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
            overflow-x: hidden;
        }

        #page-content-wrapper.iaf-dashboard-page {
            height: 100vh;
            margin-top: 0;
            padding-top: 0;
            overflow: hidden;
            background: linear-gradient(180deg, #f8fafc 0%, #f4f7f6 42%, #eef4f7 100%);
        }

        .iaf-dashboard-page .page-content {
            height: 100vh;
            overflow: hidden;
        }

        .iaf-dashboard-page .container-fluid {
            width: 100%;
            max-width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 8px 10px 10px !important;
        }

        .page-header-container {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 58%, #3b82f6 100%);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 12px;
            padding: 12px 18px;
            margin: 0 0 8px;
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(30, 58, 138, 0.14);
            display: flex;
            justify-content: flex-start;
            align-items: center;
            min-height: 58px;
            flex: 0 0 auto;
        }

        .page-title {
            margin: 0;
            font-size: 21px;
            font-weight: 700;
            letter-spacing: -0.35px;
            line-height: 1.15;
        }

        .page-subtitle {
            margin: 3px 0 0;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.3;
            opacity: 0.92;
        }

        .modern-card {
            background: #ffffff;
            border-radius: var(--iaf-radius);
            box-shadow: 0 8px 26px rgba(15, 23, 42, 0.06);
            padding: 12px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            margin-bottom: 0;
            min-height: 0;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
        }

        .iaf-table-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 8px;
            flex: 0 0 auto;
        }

        .client-filter-label {
            margin: 0;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .client-filter-input {
            width: min(320px, 100%);
            height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 7px 12px;
            outline: none;
            background: #ffffff;
            color: #1e293b;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .client-filter-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
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
            font-size: 13px;
            padding-top: 12px;
        }

        table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0;
            width: 100% !important;
            margin: 0 !important;
            white-space: nowrap;
        }

        table.dataTable thead th {
            background: #eef4ff !important;
            color: #334155;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.55px;
            padding: 12px 14px;
            border-top: 1px solid #dbeafe;
            border-bottom: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        table.dataTable tbody td {
            height: 48px;
            padding: 10px 14px;
            vertical-align: middle;
            color: #334155;
            font-size: 13px;
            border-bottom: 1px solid #edf2f7;
            background: #ffffff;
        }

        table.dataTable tbody tr {
            transition: background-color 0.18s, box-shadow 0.18s;
        }

        table.dataTable tbody tr:hover td {
            background-color: #f8fbff;
            box-shadow: inset 0 1px 0 #e0edff, inset 0 -1px 0 #e0edff;
        }

        .table-responsive {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow-x: auto !important;
            overflow-y: auto !important;
            flex: 1 1 auto;
            min-height: 0;
            background: #ffffff;
        }

        #iafTable thead th {
            position: sticky !important;
            top: 0 !important;
            z-index: 30;
            box-shadow: 0 2px 0 rgba(148, 163, 184, 0.28);
        }

        #iafTable th,
        #iafTable td {
            white-space: nowrap;
        }

        .status-badge {
            padding: 6px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 26px;
            line-height: 1;
        }

        .status-active {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .status-pending {
            background-color: #fef9c3;
            color: #854d0e;
            border: 1px solid #fef08a;
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
            background-color: #3b82f6;
            border-color: #3b82f6;
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
            color: #1e293b;
        }

        .company-link {
            text-decoration: none;
            color: #1e40af;
            transition: color 0.2s;
        }

        .company-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        .cert-number {
            font-family: Consolas, Monaco, monospace;
            background: #eff6ff;
            padding: 4px 7px;
            border-radius: 7px;
            color: #2563eb;
            font-weight: 700;
        }

        .action-btns-container {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn {
            min-height: 30px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: transform 0.18s, box-shadow 0.18s, background-color 0.18s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .btn-insert {
            background-color: #e0e7ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }

        .btn-insert:hover {
            background-color: #c7d2fe;
            box-shadow: 0 6px 14px rgba(67, 56, 202, 0.14);
            transform: translateY(-1px);
        }

        .btn-update {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .btn-update:hover {
            background-color: #bbf7d0;
            box-shadow: 0 6px 14px rgba(22, 101, 52, 0.12);
            transform: translateY(-1px);
        }

        .btn-delete {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .btn-delete:hover {
            background-color: #fecaca;
            box-shadow: 0 6px 14px rgba(185, 28, 28, 0.12);
            transform: translateY(-1px);
        }

        .action-btn i {
            margin-right: 4px;
        }

        .action-btn:disabled {
            opacity: 0.6;
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
                padding: 18px;
            }

            .modern-card {
                padding: 12px;
                min-height: calc(100vh - var(--iaf-navbar-height) - 16px);
            }

            .table-responsive {
                max-height: calc(100vh - var(--iaf-navbar-height) - 86px);
                min-height: calc(100vh - var(--iaf-navbar-height) - 104px);
            }
        }

        @media (max-width: 767px) {
            #page-content-wrapper.iaf-dashboard-page {
                padding-top: 0;
            }

            body #sidebar-wrapper {
                top: 0;
                z-index: 11060;
            }

            .page-title {
                font-size: 22px;
            }

            .page-subtitle {
                font-size: 13px;
            }

            .iaf-table-toolbar {
                align-items: stretch;
                flex-direction: column;
                gap: 8px;
            }

            .client-filter-input {
                width: 100%;
            }

            .table-responsive {
                max-height: calc(100vh - 136px);
                min-height: calc(100vh - 150px);
            }
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
                        <label for="clientNameFilter" class="client-filter-label">Client Name</label>
                        <input type="text" id="clientNameFilter" class="client-filter-input" placeholder="Search by client name">
                    </div>
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
                                    <th>Certificate Issue Date</th>
                                    <th>Issue Date</th>
                                    <th>Expire Date</th>
                                    <th>Site</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $c): ?>
                                    <tr>
                                        <td>
                                            <div class="action-btns-container">
                                                <button class="action-btn btn-insert"
                                                    id="btn-insert-<?php echo $c['internal_client_id']; ?>"
                                                    onclick='handleAction("insert", <?php echo htmlspecialchars(json_encode($c), ENT_QUOTES, "UTF-8"); ?>, this)'>
                                                    <i class="fa fa-cloud-upload"></i> Insert
                                                </button>
                                                <button class="action-btn btn-update"
                                                    onclick='handleAction("update", <?php echo htmlspecialchars(json_encode($c), ENT_QUOTES, "UTF-8"); ?>, this)'>
                                                    <i class="fa fa-refresh"></i> Update
                                                </button>
                                                <button class="action-btn btn-delete"
                                                    onclick='handleAction("delete", "<?php echo htmlspecialchars($c['internal_client_id']); ?>", this)'>
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                        <td class="company-name">
                                            <a href="customerAccount.php?customerid=<?php echo htmlspecialchars($c['internal_client_id']); ?>"
                                                class="company-link" target="_blank">
                                                <?php echo htmlspecialchars($c['customer_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['country']); ?></td>
                                        <td>
                                            <div class="cell-ellipsis" title="<?php echo htmlspecialchars($c['address']); ?>">
                                                <?php echo htmlspecialchars($c['address']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['accreditation_body'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($c['standard_name'] ?: 'N/A'); ?></td>
                                        <td><span style="font-weight: 600; color: #475569;"><?php echo htmlspecialchars($c['scheme_name']); ?></span></td>
                                        <td>
                                            <div class="cell-ellipsis" title="<?php echo htmlspecialchars($c['certification_scope']); ?>">
                                                <?php echo htmlspecialchars($c['certification_scope'] ?: 'N/A'); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($c['certificate_number']): ?>
                                                <span
                                                    class="cert-number"><?php echo htmlspecialchars($c['certificate_number']); ?></span>
                                            <?php else: ?>
                                                <span style="color: #94a3b8;">Pending</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php
                                            $statusStr = strtolower($c['certification_status']);
                                            $badgeClass = 'status-default';
                                            if (strpos($statusStr, 'active') !== false || strpos($statusStr, 'issue') !== false || strpos($statusStr, 'grant') !== false) {
                                                $badgeClass = 'status-active';
                                            } elseif (strpos($statusStr, 'pending') !== false) {
                                                $badgeClass = 'status-pending';
                                            } elseif (strpos($statusStr, 'suspend') !== false || strpos($statusStr, 'expir') !== false) {
                                                $badgeClass = 'status-expired';
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars($c['certification_status'] ?: 'Unknown'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['certification_type']); ?></td>
                                        <td><?php echo $c['certificate_issue_date'] ? date('d M Y', strtotime($c['certificate_issue_date'])) : '-'; ?></td>
                                        <td><?php echo $c['issue_date'] ? date('d M Y', strtotime($c['issue_date'])) : '-'; ?></td>
                                        <td><?php echo $c['expire_date'] ? date('d M Y', strtotime($c['expire_date'])) : '-'; ?></td>
                                        <td><?php echo htmlspecialchars($c['site'] ?: 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
                        info: true,
                        ordering: true,
                        searching: true,
                        dom: 'rt<"row"<"col-sm-12"i>>',
                        order: [[1, 'asc']]
                    });

                    $('#clientNameFilter').on('keyup input', function () {
                        table.column(1).search(this.value).draw();
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

                    // Check localStorage to disable already inserted buttons on page load
                    $('.btn-insert').each(function () {
                        const btnId = $(this).attr('id');
                        if (btnId && localStorage.getItem(btnId) === 'inserted') {
                            $(this).prop('disabled', true);
                            $(this).html('<i class="fa fa-check"></i> Inserted');
                            $(this).css({ 'background-color': '#f1f5f9', 'color': '#94a3b8', 'border-color': '#e2e8f0' });
                        }
                    });
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
                        confirmTitle = 'Delete Client Data?';
                        confirmText = `Are you sure you want to delete client #${clientId}? This cannot be undone.`;
                        confirmButtonText = 'Yes, Delete it!';
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
                                            localStorage.removeItem(`btn-insert-${clientId}`);

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
            </script>

        </div> <!-- /.container-fluid -->
    </div> <!-- /.page-content -->
</div> <!-- /#page-content-wrapper -->
</div> <!-- /#wrapper -->

<!-- Scripts already loaded by header/head, but keep these if unique -->

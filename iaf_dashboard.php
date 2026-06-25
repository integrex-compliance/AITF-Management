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
        cp.registration_date as issue_date, 
        cp.registration_date as original_issue_date,
        cp.valid_until as expiry_date, 
        cp.certificate_scope,
        (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id ORDER BY asi.audit_schedule_id DESC LIMIT 1) as standard_name,
        c.schema_name
    FROM customer_info c 
    LEFT JOIN certificate_print cp ON c.id = cp.customer_id 
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
        cp.registration_date as issue_date, 
        cp.registration_date as original_issue_date,
        cp.valid_until as expiry_date, 
        cp.certificate_scope,
        (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id ORDER BY asi.audit_schedule_id DESC LIMIT 1) as standard_name
    FROM customer_info c 
    LEFT JOIN certificate_print cp ON c.id = cp.customer_id 
    GROUP BY c.id
    ORDER BY c.id DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ensure columns that may not exist have a fallback
    foreach ($clients as &$client) {
        $client = iafCertSearchNormalizeClientData($client);
        $client['certification_scope'] = iafCertSearchFirstValue($client, ['certificate_scope', 'scope']);
    }
    unset($client);

    ?>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Inter', sans-serif;
        }

        /* Modern Page Header Container */
        .page-header-container {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 12px;
            padding: 30px;
            margin-top: 90px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            margin: 5px 0 0 0;
            font-size: 15px;
            opacity: 0.9;
        }

        /* Dashboard Card */
        .modern-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 25px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 50px;
        }

        /* Table Styling */
        table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
            margin-top: 15px !important;
            white-space: nowrap;
        }

        table.dataTable thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 15px;
            border-bottom: 2px solid #e2e8f0;
            border-top: none;
        }

        table.dataTable tbody td {
            padding: 16px 15px;
            vertical-align: middle;
            color: #334155;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        table.dataTable tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            text-align: center;
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

        /* DataTables custom controls */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 12px;
            margin-left: 8px;
            outline: none;
            transition: border-color 0.2s;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .page-item.active .page-link {
            background-color: #3b82f6;
            border-color: #3b82f6;
            border-radius: 6px;
        }

        .page-link {
            color: #475569;
            border: none;
            margin: 0 2px;
            border-radius: 6px;
        }

        .page-link:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        /* Value Highlight */
        .company-name {
            font-weight: 600;
            color: #1e293b;
        }

        .company-link {
            text-decoration: none;
            color: #1e40af;
            /* Deeper blue for link */
            transition: color 0.2s;
        }

        .company-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        .cert-number {
            font-family: monospace;
            background: #f1f5f9;
            padding: 3px 6px;
            border-radius: 4px;
            color: #3b82f6;
            font-weight: 600;
        }

        /* Action Buttons */
        .action-btns-container {
            display: flex;
            gap: 6px;
        }

        .action-btn {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-insert {
            background-color: #e0e7ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }

        .btn-insert:hover {
            background-color: #c7d2fe;
            transform: translateY(-1px);
        }

        .btn-update {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .btn-update:hover {
            background-color: #bbf7d0;
            transform: translateY(-1px);
        }

        .btn-delete {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .btn-delete:hover {
            background-color: #fecaca;
            transform: translateY(-1px);
        }

        .action-btn i {
            margin-right: 4px;
        }

        .action-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Override page header margin because wrapper has header */
        .page-header-container {
            margin-top: 20px !important;
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid" style="padding: 0 30px;">
                <div class="page-header-container">
                    <div>
                        <h1 class="page-title">IAF Dashboard</h1>
                        <p class="page-subtitle">Comprehensive Overview of Client Certifications</p>
                    </div>
                    <div>
                        <button class="btn btn-light"
                            style="font-weight: 600; border-radius: 8px; padding: 10px 20px; color: #1e3a8a;"
                            onclick="window.print()">
                            <i class="fa fa-print"></i> Print Report
                        </button>
                    </div>
                </div>

                <div class="modern-card">
                    <div class="table-responsive">
                        <table id="iafTable" class="table table-hover nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Company GUID</th>
                                    <th>Int. Client ID</th>
                                    <th>Company Name</th>
                                    <th>Country</th>
                                    <th>Address</th>
                                    <th>Cert. Track ID</th>
                                    <th>Cert. Number</th>
                                    <th>Status</th>
                                    <th>Cert. Type</th>
                                    <th>Issue Date</th>
                                    <th>Expiry Date</th>
                                    <th>Scheme Name</th>
                                    <th>Standard Name</th>
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
                                        <td style="font-size: 12px; color: #64748b;">
                                            <?php echo htmlspecialchars($c['customer_guid']); ?>
                                        </td>
                                        <td><strong>#<?php echo htmlspecialchars($c['internal_client_id']); ?></strong></td>
                                        <td class="company-name">
                                            <a href="customerAccount.php?customerid=<?php echo htmlspecialchars($c['internal_client_id']); ?>"
                                                class="company-link" target="_blank">
                                                <?php echo htmlspecialchars($c['customer_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['country']); ?></td>
                                        <td>
                                            <div style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                title="<?php echo htmlspecialchars($c['address']); ?>">
                                                <?php echo htmlspecialchars($c['address']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['certificate_tracking_id'] ?: 'N/A'); ?></td>
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
                                        <td><?php echo $c['issue_date'] ? date('d M Y', strtotime($c['issue_date'])) : '-'; ?>
                                        </td>
                                        <td><?php echo $c['expiry_date'] ? date('d M Y', strtotime($c['expiry_date'])) : '-'; ?>
                                        </td>
                                        <td><span
                                                style="font-weight: 600; color: #475569;"><?php echo htmlspecialchars($c['schema_name']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['standard_name'] ?: 'N/A'); ?></td>
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
                        scrollX: true,
                        pageLength: 25,
                        language: {
                            search: "Search Records:",
                            lengthMenu: "Display _MENU_ clients per page"
                        },
                        order: [[2, 'desc']] // Sort by Client ID (originally index 1, now index 2 because action is 0)
                    });

                    // Toggle Menu
                    $("#menu-toggle").click(function (e) {
                        e.preventDefault();
                        $("#wrapper").toggleClass("active");
                        $(".container-fluid").toggleClass("active");
                        setTimeout(function () { table.columns.adjust().draw(); }, 300);
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

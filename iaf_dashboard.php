<?php
$pageIdentifier = "IAF Dashboard";
include 'header.php';
include_once './dbConnect.php';


if (!isset($db)) {
    include_once './dbConnect.php';
}

$query = "SELECT 
        c.id as internal_client_id,
        c.customer_guid,
        c.customer_name,
        c.country,
        c.address,
        c.audit_status as certification_status, 
        cp.certificate_id as certificate_tracking_id,
        cp.certificate_no as certificate_number, 
        cp.registration_date as issue_date, 
        cp.valid_until as expiry_date, 
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
    $query = "SELECT 
        c.id as internal_client_id,
        c.customer_name,
        c.address,
        c.audit_status as certification_status, 
        cp.certificate_id as certificate_tracking_id,
        cp.certificate_no as certificate_number, 
        cp.registration_date as issue_date, 
        cp.valid_until as expiry_date, 
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
    // Generate an empty GUID if one isn't set, so the JS logic knows it's un-synced
    if (!isset($client['customer_guid']))
        $client['customer_guid'] = '';
    if (!isset($client['country']))
        $client['country'] = 'India';

    if (!isset($client['schema_name']) || empty($client['schema_name'])) {
        $std = $client['standard_name'];
        if (strpos(strtoupper($std), '9001') !== false)
            $client['schema_name'] = 'QMS';
        elseif (strpos(strtoupper($std), '14001') !== false)
            $client['schema_name'] = 'EMS';
        elseif (strpos(strtoupper($std), '45001') !== false || strpos(strtoupper($std), '18001') !== false)
            $client['schema_name'] = 'OHSMS';
        elseif (strpos(strtoupper($std), '27001') !== false)
            $client['schema_name'] = 'ISMS';
        elseif (strpos(strtoupper($std), '22000') !== false)
            $client['schema_name'] = 'FSMS';
        else
            $client['schema_name'] = 'Other';
    }

    $client['certification_type'] = $client['certification_status'] ? 'Accredited' : 'Unaccredited';
}
unset($client);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Inter', sans-serif;
        }

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

        .modern-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 25px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 50px;
        }

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

        /* Badges */
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

        /* Action Buttons */
        .btn-action {
            margin-right: 4px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            padding: 6px 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-7rt {
            background-color: #e0e7ff;
            color: #4338ca;
        }

        .btn-insert:hover {
            background-color: #c7d2fe;
        }

        .btn-update {
            background-color: #dcfce7;
            color: #15803d;
        }

        .btn-update:hover {
            background-color: #bbf7d0;
        }

        .btn-delete {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .btn-delete:hover {
            background-color: #fecaca;
        }

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Typography */
        .company-name {
            font-weight: 600;
            color: #1e293b;
        }

        .cert-number {
            font-family: monospace;
            background: #f1f5f9;
            padding: 3px 6px;
            border-radius: 4px;
            color: #3b82f6;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="container-fluid" style="padding: 0 30px;">
        <div class="page-header-container">
            <div>
                <h1 class="page-title">IAF Dashboard</h1>
                <p class="page-subtitle">Comprehensive Overview of Client Certifications</p>
            </div>
            <div>
                <button class="btn btn-light"
                    style="font-weight: 600; border-radius: 8px; padding: 10px 20px; color: #1e3a8a; margin-right: 10px; border: none;"
                    onclick="handleSync(this)">
                    <i class="fa fa-sync-alt"></i> Sync Database
                </button>
                <button class="btn btn-light"
                    style="font-weight: 600; border-radius: 8px; padding: 10px 20px; color: #1e3a8a; border: none;"
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
                        <?php foreach ($clients as $c):
                            // Safely encode the row data into JSON so JS can read it
                            $jsonPayload = htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr>
                                <td>
                                    <button class="btn-action btn-insert" onclick="handleInsert(this)"
                                        data-client="<?php echo $jsonPayload; ?>">
                                        <i class="fa fa-cloud-upload-alt"></i> Insert
                                    </button>
                                    <button class="btn-action btn-update" onclick="handleUpdate(this)"
                                        data-client="<?php echo $jsonPayload; ?>">
                                        <i class="fa fa-sync"></i> Update
                                    </button>
                                    <button class="btn-action btn-delete" onclick="handleDelete(this)"
                                        data-client="<?php echo $jsonPayload; ?>">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </td>

                                <td style="font-size: 12px; color: #64748b;">
                                    <?php echo $c['customer_guid'] ? htmlspecialchars($c['customer_guid']) : '<span style="color:#ef4444;">Unsynced</span>'; ?>
                                </td>
                                <td><strong>#<?php echo htmlspecialchars($c['internal_client_id']); ?></strong></td>
                                <td class="company-name"><?php echo htmlspecialchars($c['customer_name']); ?></td>
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
                                        <?php
                                    else: ?>
                                        <span style="color: #94a3b8;">Pending</span>
                                        <?php
                                    endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusStr = strtolower($c['certification_status']);
                                    $badgeClass = 'status-default';
                                    if (strpos($statusStr, 'active') !== false || strpos($statusStr, 'issue') !== false || strpos($statusStr, 'grant') !== false) {
                                        $badgeClass = 'status-active';
                                    } elseif (strpos($statusStr, 'pending') !== false) {
                                        $badgeClass = 'status-pending';
                                    } elseif (strpos($statusStr, 'suspend') !== false || strpos($statusStr, 'expir') !== false || strpos($statusStr, 'withdraw') !== false) {
                                        $badgeClass = 'status-expired';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($c['certification_status'] ?: 'Unknown'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($c['certification_type']); ?></td>
                                <td><?php echo $c['issue_date'] ? date('d M Y', strtotime($c['issue_date'])) : '-'; ?></td>
                                <td><?php echo $c['expiry_date'] ? date('d M Y', strtotime($c['expiry_date'])) : '-'; ?>
                                </td>
                                <td><span
                                        style="font-weight: 600; color: #475569;"><?php echo htmlspecialchars($c['schema_name']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($c['standard_name'] ?: 'N/A'); ?></td>
                            </tr>
                            <?php
                        endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#iafTable').DataTable({
                scrollX: true,
                pageLength: 25,
                language: {
                    search: "Search Records:",
                    lengthMenu: "Display _MENU_ clients per page"
                },
                order: [[2, 'desc']] // Sort by Int. Client ID (which is now column index 2)
            });
        });

        // ==========================================
        // IAF API INTEGRATION LOGIC
        // ==========================================

        // Helper to disable buttons during loading
        function toggleButtonState(btn, isLoading) {
            if (isLoading) {
                btn.classList.add('btn-disabled');
                btn.disabled = true;
                btn.dataset.originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Wait...';
            } else {
                btn.classList.remove('btn-disabled');
                btn.disabled = false;
                btn.innerHTML = btn.dataset.originalHtml;
            }
        }

        async function callOperationsApi(action, data = {}) {
            try {
                const response = await fetch('iaf_operations.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: action, data: data })
                });
                return await response.json();
            } catch (error) {
                console.error(`Error during ${action}:`, error);
                return { error: true, message: 'Network or server error occurred.' };
            }
        }

        function handleInsert(buttonEl) {
            const clientData = JSON.parse(buttonEl.getAttribute('data-client'));
            toggleButtonState(buttonEl, true);

            callOperationsApi('insert', clientData).then(result => {
                toggleButtonState(buttonEl, false);
                if (result.error) alert("Insert failed: " + result.message);
                else {
                    alert("Record successfully inserted!");
                    // Optional: trigger a sync here to fetch the new GUID
                }
            });
        }

        function handleUpdate(buttonEl) {
            const clientData = JSON.parse(buttonEl.getAttribute('data-client'));

            if (!clientData.customer_guid || clientData.customer_guid === '') {
                alert("Cannot update: Missing GUID. Please sync with IAF first.");
                return;
            }

            toggleButtonState(buttonEl, true);
            callOperationsApi('update', clientData).then(result => {
                toggleButtonState(buttonEl, false);
                if (result.error) alert("Update failed: " + result.message);
                else alert("Company name successfully updated!");
            });
        }

        function handleDelete(buttonEl) {
            const clientData = JSON.parse(buttonEl.getAttribute('data-client'));

            if (!clientData.customer_guid || clientData.customer_guid === '') {
                alert("Cannot delete: Missing GUID. Record might not exist in IAF.");
                return;
            }

            if (confirm("Are you sure you want to delete this certification?")) {
                toggleButtonState(buttonEl, true);
                callOperationsApi('delete', clientData).then(result => {
                    toggleButtonState(buttonEl, false);
                    if (result.error) alert("Delete failed: " + result.message);
                    else {
                        alert("Certification successfully deleted.");
                        // Update UI or reload
                    }
                });
            }
        }

        function handleSync(buttonEl) {
            toggleButtonState(buttonEl, true);
            console.log("Fetching and syncing records...");

            fetch('iaf_sync.php')
                .then(response => response.json())
                .then(result => {
                    toggleButtonState(buttonEl, false);
                    if (result.error) {
                        alert("Sync failed: " + result.message);
                    } else {
                        console.log("Sync details:", result);
                        let msg = result.sync_summary || "Sync complete!";
                        if (result.db_error) msg += "\nError: " + result.db_error;
                        alert(msg);
                        window.location.reload();
                    }
                })
                .catch(error => {
                    toggleButtonState(buttonEl, false);
                    alert("A network error occurred during sync.");
                });
        }
    </script>

</body>

</html>
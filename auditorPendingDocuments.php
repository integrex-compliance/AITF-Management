<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/utility.php';
require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/pending_doc_helper.php';

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: login.php?reason=login_required');
    exit;
}

$userRole = $_SESSION['role'] ?? '';
$sessionUsername = trim($_SESSION['username'] ?? '');
$sessionAuditorId = (int)($_SESSION['auditor_id'] ?? 0);
$isAuditor = ($userRole === 'Auditor');

// Determine which auditor to filter by
$selectedAuditor = '';
if ($isAuditor) {
    $selectedAuditor = $sessionUsername;
} else {
    $selectedAuditor = isset($_GET['auditor']) ? trim($_GET['auditor']) : '';
}

// Fetch all auditors for the dropdown filter (if admin/manager)
$allAuditors = array();
if (!$isAuditor) {
    try {
        $aStmt = $db->query("SELECT DISTINCT auditor_name FROM auditors WHERE auditor_name IS NOT NULL AND auditor_name != '' ORDER BY auditor_name ASC");
        $allAuditors = $aStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {}
}

// Fetch pending documents
$pendingDocs = aitf_fetch_all_pending_documents($db, $selectedAuditor !== '' ? $selectedAuditor : null);

// Calculate stats
$totalDocs = count($pendingDocs);
$sentCount = 0;
$pendingReasonCount = 0;
$unrespondedCount = 0;

foreach ($pendingDocs as $doc) {
    $comm = $doc['communication'] ?? null;
    if ($comm) {
        if ($comm['status'] === 'YES') {
            $sentCount++;
        } elseif ($comm['status'] === 'NO') {
            $pendingReasonCount++;
        } else {
            $unrespondedCount++;
        }
    } else {
        $unrespondedCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AITF - Pending Documents</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        :root {
            --primary: #0d4d8b;
            --primary-dark: #083b6a;
            --primary-light: #1e6bb8;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            margin: 0;
            padding: 0;
        }

        .top-navbar {
            background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%);
            color: #ffffff;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .top-navbar .brand {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .top-navbar .brand span.badge-role {
            background: rgba(255,255,255,0.2);
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .top-navbar .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-nav-back {
            background: rgba(255,255,255,0.15);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.25);
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-nav-back:hover {
            background: rgba(255,255,255,0.25);
            color: #ffffff;
            text-decoration: none;
        }

        .page-container {
            max-width: 1320px;
            margin: 24px auto;
            padding: 0 20px;
        }

        /* Stats Row */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 16px 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-card .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-card.total .stat-icon { background: #eff6ff; color: #2563eb; }
        .stat-card.unresponded .stat-icon { background: #fef3c7; color: #d97706; }
        .stat-card.sent .stat-icon { background: #ecfdf5; color: #059669; }
        .stat-card.pending .stat-icon { background: #fef2f2; color: #dc2626; }

        .stat-card .stat-info h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            line-height: 1.1;
        }

        .stat-card .stat-info p {
            margin: 4px 0 0 0;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* Filter Panel */
        .filter-panel {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 8px 14px 8px 36px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 13.5px;
            outline: none;
        }

        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13,77,139,0.1);
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }

        .filter-pills {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-pill {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid var(--border-color);
            background: #ffffff;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .filter-pill:hover,
        .filter-pill.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }

        /* Document Table */
        .doc-table-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        .table-responsive {
            margin: 0;
            border: none;
        }

        .doc-table {
            width: 100%;
            margin-bottom: 0;
        }

        .doc-table th {
            background: #f1f5f9;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
        }

        .doc-table td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        .doc-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .service-tag {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        .service-tag.CA { background: #dbeafe; color: #1e40af; }
        .service-tag.SA-I { background: #e0e7ff; color: #3730a3; }
        .service-tag.SA-II { background: #f3e8ff; color: #6b21a8; }
        .service-tag.RC { background: #fef3c7; color: #92400e; }

        .client-name-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 13.5px;
            display: block;
        }

        .client-id-sub {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }

        .doc-name-badge {
            font-weight: 600;
            color: #1e293b;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11.5px;
            font-weight: 700;
        }

        .status-badge.sent {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .status-badge.pending-reason {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .status-badge.waiting {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .note-preview-box {
            margin-top: 6px;
            font-size: 11.5px;
            color: #475569;
            background: #f8fafc;
            padding: 5px 8px;
            border-radius: 4px;
            border-left: 3px solid #cbd5e1;
            line-height: 1.35;
            max-width: 320px;
            word-break: break-word;
        }

        .note-preview-box.yes {
            border-left-color: #10b981;
            background: #f0fdf4;
            color: #166534;
        }

        .note-preview-box.no {
            border-left-color: #ef4444;
            background: #fef2f2;
            color: #991b1b;
        }

        /* Action Buttons */
        .action-btn-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-action {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.15s ease;
        }

        .btn-yes {
            background: #10b981;
            color: #ffffff;
        }
        .btn-yes:hover {
            background: #059669;
            color: #ffffff;
        }

        .btn-no {
            background: #ef4444;
            color: #ffffff;
        }
        .btn-no:hover {
            background: #dc2626;
            color: #ffffff;
        }

        /* Modal Styles */
        .modal-header {
            background: #0d4d8b;
            color: #ffffff;
            border-radius: 6px 6px 0 0;
            padding: 14px 20px;
        }

        .modal-header .close {
            color: #ffffff;
            opacity: 0.8;
        }

        .modal-header .modal-title {
            font-weight: 700;
            font-size: 16px;
        }

        .modal-body {
            padding: 20px;
        }

        .meta-field {
            margin-bottom: 12px;
            font-size: 13px;
        }

        .meta-field strong {
            color: #475569;
            display: inline-block;
            width: 110px;
        }

        /* Toast notification */
        #statusToast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            min-width: 280px;
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
            align-items: center;
            gap: 10px;
        }
        #statusToast.success { background: #10b981; }
        #statusToast.error { background: #ef4444; }

        .empty-state-row {
            text-align: center;
            padding: 48px 20px;
            color: #94a3b8;
        }
        .empty-state-row i {
            font-size: 40px;
            margin-bottom: 10px;
            opacity: 0.6;
        }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="brand">
            <i class="fa-solid fa-file-lines"></i>
            <span>Pending Documents</span>
            <span class="badge-role"><?php echo htmlspecialchars($userRole ?: 'User'); ?></span>
        </div>
        <div class="nav-actions">
            <?php if ($isAuditor) { ?>
                <a href="auditorDashboard.php" class="btn-nav-back"><i class="fa fa-arrow-left"></i> Auditor Dashboard</a>
            <?php } else { ?>
                <a href="uploadingStatus.php" class="btn-nav-back"><i class="fa fa-arrow-left"></i> Uploading Status</a>
                <a href="homePage.php" class="btn-nav-back"><i class="fa fa-home"></i> Home</a>
            <?php } ?>
        </div>
    </header>

    <div class="page-container">

        <!-- Welcome Banner / Header -->
        <div style="margin-bottom: 20px; display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: #0f172a;">
                    Pending Upload Documents
                </h2>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">
                    <?php if ($isAuditor) { ?>
                        Showing all documents marked as <em>scanning in process</em> for your audited clients.
                    <?php } elseif ($selectedAuditor !== '') { ?>
                        Filtered for Auditor: <strong><?php echo htmlspecialchars($selectedAuditor); ?></strong>
                    <?php } else { ?>
                        Showing pending documents across all auditors.
                    <?php } ?>
                </p>
            </div>

            <?php if (!$isAuditor && !empty($allAuditors)) { ?>
                <!-- Auditor Dropdown Filter for Admins / Managers -->
                <form method="GET" action="" class="form-inline" style="display: flex; align-items: center; gap: 8px;">
                    <label style="font-size: 12.5px; font-weight: 600; color: #475569;">Filter by Auditor:</label>
                    <select name="auditor" class="form-control" style="border-radius: 6px; font-size: 13px;" onchange="this.form.submit()">
                        <option value="">-- All Auditors --</option>
                        <?php foreach ($allAuditors as $audName) { ?>
                            <option value="<?php echo htmlspecialchars($audName); ?>" <?php echo ($selectedAuditor === $audName) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($audName); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <?php if ($selectedAuditor !== '') { ?>
                        <a href="auditorPendingDocuments.php" class="btn btn-sm btn-default" title="Clear Filter"><i class="fa fa-times"></i></a>
                    <?php } ?>
                </form>
            <?php } ?>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon"><i class="fa fa-list-check"></i></div>
                <div class="stat-info">
                    <h3 id="statTotal"><?php echo $totalDocs; ?></h3>
                    <p>Total Pending Docs</p>
                </div>
            </div>
            <div class="stat-card unresponded">
                <div class="stat-icon"><i class="fa fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <h3 id="statWaiting"><?php echo $unrespondedCount; ?></h3>
                    <p>Action Required</p>
                </div>
            </div>
            <div class="stat-card sent">
                <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                <div class="stat-info">
                    <h3 id="statSent"><?php echo $sentCount; ?></h3>
                    <p>Send by Auditor (Yes)</p>
                </div>
            </div>
            <div class="stat-card pending">
                <div class="stat-icon"><i class="fa fa-clock"></i></div>
                <div class="stat-info">
                    <h3 id="statPending"><?php echo $pendingReasonCount; ?></h3>
                    <p>Pending from Auditor (No)</p>
                </div>
            </div>
        </div>

        <!-- Filters & Search Toolbar -->
        <div class="filter-panel">
            <div class="search-box">
                <i class="fa fa-search"></i>
                <input type="text" id="tableSearchInput" placeholder="Search by client or document...">
            </div>

            <div class="filter-pills">
                <button type="button" class="filter-pill active" data-filter="all">All (<?php echo $totalDocs; ?>)</button>
                <button type="button" class="filter-pill" data-filter="waiting">Action Required (<?php echo $unrespondedCount; ?>)</button>
                <button type="button" class="filter-pill" data-filter="sent">Send by Auditor (<?php echo $sentCount; ?>)</button>
                <button type="button" class="filter-pill" data-filter="pending-reason">Pending with Reason (<?php echo $pendingReasonCount; ?>)</button>
            </div>
        </div>

        <!-- Document Table Card -->
        <div class="doc-table-card">
            <div class="table-responsive">
                <table class="table doc-table" id="pendingDocsTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Client Name</th>
                            <th>Service</th>
                            <th>Document Name</th>
                            <th>Assigned Auditor</th>
                            <th>Status & Notes</th>
                            <th style="width: 180px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($pendingDocs)) {
                            $idx = 1;
                            foreach ($pendingDocs as $doc) {
                                $comm = $doc['communication'] ?? null;
                                $status = $comm['status'] ?? '';
                                $note = $comm['note'] ?? '';
                                $commAuditor = $comm['auditor_name'] ?? '';
                                $updatedAt = !empty($comm['updated_at']) ? date('d M Y, h:i A', strtotime($comm['updated_at'])) : '';

                                $rowFilterClass = 'waiting';
                                if ($status === 'YES') {
                                    $rowFilterClass = 'sent';
                                } elseif ($status === 'NO') {
                                    $rowFilterClass = 'pending-reason';
                                }

                                $uniqueRowId = 'row_' . $doc['service_type'] . '_' . $doc['service_id'] . '_' . $doc['document_key'];
                                ?>
                                <tr id="<?php echo $uniqueRowId; ?>" class="doc-row <?php echo $rowFilterClass; ?>"
                                    data-service-type="<?php echo htmlspecialchars($doc['service_type']); ?>"
                                    data-service-id="<?php echo $doc['service_id']; ?>"
                                    data-customer-id="<?php echo $doc['customer_id']; ?>"
                                    data-customer-name="<?php echo htmlspecialchars($doc['customer_name']); ?>"
                                    data-doc-key="<?php echo htmlspecialchars($doc['document_key']); ?>"
                                    data-doc-name="<?php echo htmlspecialchars($doc['document_name']); ?>"
                                    data-assigned-auditor="<?php echo htmlspecialchars($doc['assigned_auditor']); ?>"
                                    data-status="<?php echo htmlspecialchars($status); ?>"
                                    data-note="<?php echo htmlspecialchars($note); ?>">
                                    <td><?php echo $idx++; ?></td>
                                    <td>
                                        <span class="client-name-title"><?php echo htmlspecialchars($doc['customer_name']); ?></span>
                                        <span class="client-id-sub">Client ID: <?php echo $doc['customer_id']; ?></span>
                                    </td>
                                    <td>
                                        <span class="service-tag <?php echo htmlspecialchars($doc['service_type']); ?>">
                                            <?php echo htmlspecialchars($doc['service_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="doc-name-badge">
                                            <i class="fa fa-file-lines" style="color: #64748b; margin-right: 4px;"></i>
                                            <?php echo htmlspecialchars($doc['document_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 12.5px; font-weight: 600; color: #334155;">
                                            <i class="fa fa-user" style="color: #94a3b8; margin-right: 4px;"></i>
                                            <?php echo htmlspecialchars($doc['assigned_auditor'] ?: 'Not Assigned'); ?>
                                        </span>
                                    </td>
                                    <td class="status-cell">
                                        <?php if ($status === 'YES') { ?>
                                            <span class="status-badge sent">
                                                <i class="fa fa-check-circle"></i> Send by <?php echo htmlspecialchars($commAuditor); ?>
                                            </span>
                                            <?php if ($note !== '') { ?>
                                                <div class="note-preview-box yes">
                                                    <strong>Note:</strong> <?php echo htmlspecialchars($note); ?>
                                                    <?php if ($updatedAt) { ?><span style="display:block; font-size:10.5px; color:#6b7280; margin-top:2px;"><?php echo $updatedAt; ?></span><?php } ?>
                                                </div>
                                            <?php } ?>
                                        <?php } elseif ($status === 'NO') { ?>
                                            <span class="status-badge pending-reason">
                                                <i class="fa fa-clock"></i> Pending from <?php echo htmlspecialchars($commAuditor); ?>
                                            </span>
                                            <?php if ($note !== '') { ?>
                                                <div class="note-preview-box no">
                                                    <strong>Reason:</strong> <?php echo htmlspecialchars($note); ?>
                                                    <?php if ($updatedAt) { ?><span style="display:block; font-size:10.5px; color:#6b7280; margin-top:2px;"><?php echo $updatedAt; ?></span><?php } ?>
                                                </div>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <span class="status-badge waiting">
                                                <i class="fa fa-circle-question"></i> Action Required
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="action-btn-group" style="justify-content: flex-end;">
                                            <button type="button" class="btn-action btn-yes btn-open-modal" data-action="YES" title="Mark as Sent">
                                                <i class="fa fa-check"></i> Yes
                                            </button>
                                            <button type="button" class="btn-action btn-no btn-open-modal" data-action="NO" title="Mark as Pending with Reason">
                                                <i class="fa fa-times"></i> No
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state-row">
                                        <i class="fa fa-folder-open"></i>
                                        <h4>No Pending Documents Found</h4>
                                        <p>All documents have been uploaded or no documents match your filter.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Communication Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="actionForm">
                    <input type="hidden" name="service_type" id="modalServiceType">
                    <input type="hidden" name="service_id" id="modalServiceId">
                    <input type="hidden" name="customer_id" id="modalCustomerId">
                    <input type="hidden" name="customer_name" id="modalCustomerName">
                    <input type="hidden" name="document_key" id="modalDocumentKey">
                    <input type="hidden" name="document_name" id="modalDocumentName">
                    <input type="hidden" name="status" id="modalStatus">
                    <input type="hidden" name="auditor_name" id="modalAuditorName">
                    <input type="hidden" id="modalTargetRowId">

                    <div class="modal-header" id="modalHeader">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="actionModalLabel">Auditor Action</h4>
                    </div>
                    <div class="modal-body">
                        <div class="meta-field">
                            <strong>Client:</strong> <span id="modalClientText" style="font-weight: 700; color: #0f172a;"></span>
                        </div>
                        <div class="meta-field">
                            <strong>Service:</strong> <span id="modalServiceText"></span>
                        </div>
                        <div class="meta-field">
                            <strong>Document:</strong> <span id="modalDocText" style="font-weight: 600;"></span>
                        </div>

                        <div class="form-group" style="margin-top: 18px;">
                            <label id="modalNoteLabel" for="modalNoteInput" style="font-weight: 700; font-size: 13px; color: #1e293b;">
                                Note / Reason:
                            </label>
                            <textarea class="form-control" id="modalNoteInput" name="note" rows="4" style="border-radius: 8px; font-size: 13.5px;" placeholder=""></textarea>
                            <p id="modalNoteHelp" style="margin-top: 5px; font-size: 12px; color: #64748b;"></p>
                        </div>
                    </div>
                    <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px; font-weight: 600;">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitModal" style="border-radius: 6px; font-weight: 700;">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="statusToast">
        <i class="fa fa-check-circle" id="toastIcon"></i>
        <span id="toastMsg">Status updated successfully</span>
    </div>

    <script>
        $(document).ready(function() {
            var activeFilter = 'all';

            // Filter Pills Click
            $('.filter-pill').click(function() {
                $('.filter-pill').removeClass('active');
                $(this).addClass('active');
                activeFilter = $(this).data('filter');
                applyFilters();
            });

            // Search Input
            $('#tableSearchInput').on('keyup', function() {
                applyFilters();
            });

            function applyFilters() {
                var searchVal = $('#tableSearchInput').val().toLowerCase().trim();

                $('.doc-row').each(function() {
                    var $row = $(this);
                    var clientText = ($row.data('customer-name') || '').toLowerCase();
                    var docText = ($row.data('doc-name') || '').toLowerCase();
                    var svcText = ($row.data('service-type') || '').toLowerCase();
                    var auditorText = ($row.data('assigned-auditor') || '').toLowerCase();

                    var matchesSearch = searchVal === '' || 
                                        clientText.indexOf(searchVal) !== -1 || 
                                        docText.indexOf(searchVal) !== -1 ||
                                        svcText.indexOf(searchVal) !== -1 ||
                                        auditorText.indexOf(searchVal) !== -1;

                    var matchesPill = true;
                    if (activeFilter === 'waiting') {
                        matchesPill = $row.hasClass('waiting');
                    } else if (activeFilter === 'sent') {
                        matchesPill = $row.hasClass('sent');
                    } else if (activeFilter === 'pending-reason') {
                        matchesPill = $row.hasClass('pending-reason');
                    }

                    if (matchesSearch && matchesPill) {
                        $row.show();
                    } else {
                        $row.hide();
                    }
                });
            }

            // Open Modal Handler
            $('.doc-table').on('click', '.btn-open-modal', function() {
                var action = $(this).data('action'); // 'YES' or 'NO'
                var $row = $(this).closest('.doc-row');

                $('#modalTargetRowId').val($row.attr('id'));
                $('#modalServiceType').val($row.data('service-type'));
                $('#modalServiceId').val($row.data('service-id'));
                $('#modalCustomerId').val($row.data('customer-id'));
                $('#modalCustomerName').val($row.data('customer-name'));
                $('#modalDocumentKey').val($row.data('doc-key'));
                $('#modalDocumentName').val($row.data('doc-name'));
                $('#modalAuditorName').val($row.data('assigned-auditor'));
                $('#modalStatus').val(action);

                $('#modalClientText').text($row.data('customer-name'));
                $('#modalServiceText').text($row.data('service-type'));
                $('#modalDocText').text($row.data('doc-name'));

                var existingNote = $row.data('note') || '';
                $('#modalNoteInput').val(existingNote);

                if (action === 'YES') {
                    $('#actionModalLabel').html('<i class="fa fa-check-circle"></i> Mark Document as Sent (Yes)');
                    $('#modalHeader').css('background', '#10b981');
                    $('#modalNoteLabel').text('Enter your note (Optional / Remarks):');
                    $('#modalNoteInput').attr('placeholder', 'e.g. Sent via email, already handed over to office, etc.');
                    $('#modalNoteHelp').text('Auditor note will appear in the Uploading Status communication tab and card.');
                    $('#btnSubmitModal').text('Confirm Send (Yes)').removeClass('btn-danger').addClass('btn-success');
                } else {
                    $('#actionModalLabel').html('<i class="fa fa-times-circle"></i> Mark Document as Pending (No)');
                    $('#modalHeader').css('background', '#ef4444');
                    $('#modalNoteLabel').text('Enter reason why document is not uploaded yet:');
                    $('#modalNoteInput').attr('placeholder', 'e.g. Client verification pending, sign off awaited from factory manager, etc.');
                    $('#modalNoteHelp').text('This reason will be visible to office staff & management in Uploading Status.');
                    $('#btnSubmitModal').text('Confirm Pending (No)').removeClass('btn-success').addClass('btn-danger');
                }

                $('#actionModal').modal('show');
            });

            // Submit Form via AJAX
            $('#actionForm').submit(function(e) {
                e.preventDefault();

                var $btn = $('#btnSubmitModal');
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

                var formData = $(this).serialize();
                var targetRowId = $('#modalTargetRowId').val();
                var status = $('#modalStatus').val();
                var noteVal = $('#modalNoteInput').val().trim();

                $.ajax({
                    url: 'save_auditor_doc_status.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(resp) {
                        $btn.prop('disabled', false).text('Confirm');
                        $('#actionModal').modal('hide');

                        if (resp.success) {
                            showToast(resp.message, 'success');

                            // Update the row in the table
                            var $row = $('#' + targetRowId);
                            $row.removeClass('waiting sent pending-reason');
                            $row.data('status', status);
                            $row.data('note', noteVal);

                            var audName = resp.auditor_name || 'Auditor';
                            var updatedAt = resp.updated_at || 'Just now';

                            var statusHtml = '';
                            if (status === 'YES') {
                                $row.addClass('sent');
                                statusHtml = '<span class="status-badge sent"><i class="fa fa-check-circle"></i> Send by ' + $('<div>').text(audName).html() + '</span>';
                                if (noteVal !== '') {
                                    statusHtml += '<div class="note-preview-box yes"><strong>Note:</strong> ' + $('<div>').text(noteVal).html() + '<span style="display:block; font-size:10.5px; color:#6b7280; margin-top:2px;">' + updatedAt + '</span></div>';
                                }
                            } else {
                                $row.addClass('pending-reason');
                                statusHtml = '<span class="status-badge pending-reason"><i class="fa fa-clock"></i> Pending from ' + $('<div>').text(audName).html() + '</span>';
                                if (noteVal !== '') {
                                    statusHtml += '<div class="note-preview-box no"><strong>Reason:</strong> ' + $('<div>').text(noteVal).html() + '<span style="display:block; font-size:10.5px; color:#6b7280; margin-top:2px;">' + updatedAt + '</span></div>';
                                }
                            }
                            $row.find('.status-cell').html(statusHtml);

                            // Recalculate stats counters
                            recalculateStats();
                            applyFilters();
                        } else {
                            showToast(resp.message || 'Error updating status', 'error');
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).text('Confirm');
                        var msg = 'An error occurred while saving status.';
                        try {
                            var res = JSON.parse(xhr.responseText);
                            if (res.message) msg = res.message;
                        } catch(e) {}
                        showToast(msg, 'error');
                    }
                });
            });

            function recalculateStats() {
                var total = $('.doc-row').length;
                var waiting = $('.doc-row.waiting').length;
                var sent = $('.doc-row.sent').length;
                var pendingReason = $('.doc-row.pending-reason').length;

                $('#statTotal').text(total);
                $('#statWaiting').text(waiting);
                $('#statSent').text(sent);
                $('#statPending').text(pendingReason);

                $('.filter-pill[data-filter="all"]').text('All (' + total + ')');
                $('.filter-pill[data-filter="waiting"]').text('Action Required (' + waiting + ')');
                $('.filter-pill[data-filter="sent"]').text('Send by Auditor (' + sent + ')');
                $('.filter-pill[data-filter="pending-reason"]').text('Pending with Reason (' + pendingReason + ')');
            }

            function showToast(msg, type) {
                var $toast = $('#statusToast');
                $toast.removeClass('success error').addClass(type);
                $('#toastMsg').text(msg);
                if (type === 'success') {
                    $('#toastIcon').attr('class', 'fa fa-check-circle');
                } else {
                    $('#toastIcon').attr('class', 'fa fa-exclamation-circle');
                }
                $toast.fadeIn(300).delay(3500).fadeOut(400);
            }
        });
    </script>
</body>
</html>

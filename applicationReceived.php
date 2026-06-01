<div id="wrapper" class="active">
<?php
$pageIdentifier = "Application Received";
include 'dbConnect.php';

date_default_timezone_set('Asia/Kolkata');

$applications = [];
$unreadCount = 0;
$unreadInfoUpdateCount = 0;
$unreadAuditCount = 0;
$auditApps = [];
$infoUpdates = [];
if (isset($db)) {
    try {
        // Fetch audit applications
        $stmt = $db->query("SELECT *, 'audit_application' as form_type FROM online_audit_applications ORDER BY created_at DESC");
        $auditApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch information updation forms
        $stmt = $db->query("SELECT *, 'info_updation' as form_type FROM information_updation_forms ORDER BY created_at DESC");
        $infoUpdates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count unread
        foreach ($auditApps as $row) {
            if (isset($row['read_flag']) && (int)$row['read_flag'] === 0) {
                $unreadCount++;
                $unreadAuditCount++;
            }
        }
        foreach ($infoUpdates as $row) {
            if (isset($row['read_flag']) && (int)$row['read_flag'] === 0) {
                $unreadCount++;
                $unreadInfoUpdateCount++;
            }
        }
    } catch (Exception $e) {
        $applications = [];
        $auditApps = [];
        $infoUpdates = [];
    }
}
$GLOBALS['unread_app_count'] = $unreadCount;
include 'header.php';
?>
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" >
                <br>
                <br>
                <style>
                    .app-rec-wrapper {
                        max-width: 900px;
                        margin: 0 auto;
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    }
                    .app-rec-card {
                        background: #ffffff;
                        border-radius: 14px;
                        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.10);
                        padding: 16px 20px 10px 20px;
                        border: 1px solid #e5e7eb;
                        border-top: 4px solid #2563eb;
                    }
                    .app-rec-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 10px;
                    }
                    .app-rec-title {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .app-rec-title-icon {
                        width: 32px;
                        height: 32px;
                        border-radius: 9999px;
                        background: linear-gradient(135deg, #2563eb, #1d4ed8);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #ffffff;
                        box-shadow: 0 6px 12px rgba(37, 99, 235, 0.45);
                    }
                    .app-rec-title-icon .fa {
                        font-size: 15px;
                    }
                    .app-rec-title-text-main {
                        font-size: 16px;
                        font-weight: 600;
                        color: #0f172a;
                        letter-spacing: 0.02em;
                    }
                    .app-rec-title-text-sub {
                        font-size: 11px;
                        color: #6b7280;
                    }
                    .app-rec-count {
                        padding: 4px 10px;
                        border-radius: 9999px;
                        background: #eff6ff;
                        color: #1d4ed8;
                        font-size: 11px;
                        display: inline-flex;
                        align-items: center;
                        gap: 4px;
                    }
                    .app-rec-count .fa {
                        font-size: 11px;
                    }
                    .app-rec-unread {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 6px 10px;
                        border-radius: 9999px;
                        background: #fef3c7;
                        color: #b45309;
                        font-size: 11px;
                        font-weight: 700;
                        box-shadow: 0 4px 12px rgba(180, 83, 9, 0.18);
                    }
                    .app-rec-unread .fa {
                        font-size: 12px;
                    }
                    .app-rec-table thead th {
                        background: linear-gradient(to right, #eef2ff, #e0f2fe);
                        border-color: #e5e7eb !important;
                        font-size: 12px;
                        font-weight: 600;
                        color: #1f2937;
                    }
                    .app-rec-table tbody td {
                        font-size: 13px;
                        vertical-align: middle;
                        border-top: 1px solid #eef2f7;
                    }
                    .app-rec-table th,
                    .app-rec-table td {
                        text-align: left;
                    }
                    .app-rec-table th:first-child,
                    .app-rec-table td:first-child {
                        width: 70px;
                        text-align: center;
                    }
                    .app-rec-row td {
                        transition: background-color 0.18s ease, box-shadow 0.18s ease, transform 0.12s ease;
                    }
                    .app-rec-row:hover td {
                        background: #eff6ff;
                        box-shadow: inset 3px 0 0 #2563eb;
                    }
                    .app-rec-date-icon {
                        margin-right: 6px;
                        color: #9ca3af;
                    }
                    .app-rec-org-icon {
                        margin-right: 6px;
                        color: #9ca3af;
                    }
                    .app-rec-org a {
                        font-weight: 600;
                        color: #1d4ed8;
                        text-decoration: none;
                    }
                    .app-rec-org a:hover {
                        text-decoration: underline;
                    }
                    .app-rec-row.unread td {
                        background: #ecfdf3;
                        box-shadow: inset 3px 0 0 #16a34a;
                        position: relative;
                    }
                    .app-rec-row.read td {
                        background: #ffffff;
                    }
                    .app-rec-unread-pill {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 3px 8px;
                        margin-left: 6px;
                        border-radius: 999px;
                        background: #dcfce7;
                        color: #166534;
                        font-size: 10px;
                        font-weight: 700;
                    }
                    
                    /* Information Updation Form Specific Styles */
                    .app-rec-row.info_updation td {
                        border-left: 3px solid #16a34a;
                    }
                    
                    .app-rec-row.info_updation.unread td {
                        background: #fef3c7;
                        box-shadow: inset 3px 0 0 #f59e0b;
                    }
                    
                    .app-rec-row.info_updation:hover td {
                        background: #f0fdf4;
                        box-shadow: inset 3px 0 0 #16a34a;
                    }
                    
                    .app-rec-type-icon {
                        margin-right: 6px;
                        color: #16a34a;
                    }
                    
                    .app-rec-type-badge {
                        display: inline-flex;
                        align-items: center;
                        padding: 2px 8px;
                        margin-left: 8px;
                        border-radius: 999px;
                        font-size: 10px;
                        font-weight: 700;
                    }
                    
                    .app-rec-type-badge.audit_application {
                        background: #dbeafe;
                        color: #1e40af;
                    }
                    
                    .app-rec-type-badge.info_updation {
                        background: #d1fae5;
                        color: #166534;
                    }
                    
                    .app-rec-unread-pill.info_updation {
                        background: #fef3c7;
                        color: #b45309;
                    }
                    
                    .app-rec-org a em {
                        font-style: italic;
                    }
                    
                    .app-rec-grid {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 18px;
                    }
                    
                    .app-rec-card.audit {
                        border-top-color: #2563eb;
                    }
                    
                    .app-rec-card.info {
                        border-top-color: #16a34a;
                    }
                    
                    .app-rec-title-icon.audit {
                        background: linear-gradient(135deg, #2563eb, #1d4ed8);
                        box-shadow: 0 6px 12px rgba(37, 99, 235, 0.45);
                    }
                    
                    .app-rec-title-icon.info {
                        background: linear-gradient(135deg, #16a34a, #15803d);
                        box-shadow: 0 6px 12px rgba(22, 163, 74, 0.35);
                    }
                    
                    .app-rec-count.info {
                        background: #ecfdf5;
                        color: #166534;
                    }
                    
                    .app-rec-unread.info {
                        background: #fef3c7;
                        color: #b45309;
                    }
                    
                    .app-rec-unread.audit {
                        background: #fef3c7;
                        color: #b45309;
                    }
                    
                    @media (max-width: 992px) {
                        .app-rec-grid {
                            grid-template-columns: 1fr;
                        }
                        .app-rec-wrapper {
                            max-width: 900px;
                        }
                    }
                </style>
                <div class="row clearfix" >
                    <div class="col-sm-12 main_content">
                        <div class="app-rec-wrapper">
                        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1') { ?>
                            <div class="alert alert-success" style="margin-bottom: 10px;">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                Application deleted successfully.
                            </div>
                        <?php } ?>
                        
                        <div class="app-rec-grid">
                            <div class="app-rec-card table-responsive audit">
                                <div class="app-rec-header">
                                    <div class="app-rec-title">
                                        <div class="app-rec-title-icon audit"><i class="fa fa-file-text-o"></i></div>
                                        <div>
                                            <div class="app-rec-title-text-main">Audit Applications</div>
                                            <div class="app-rec-title-text-sub">Latest audit application submissions</div>
                                        </div>
                                    </div>
                                    <div class="app-rec-count"><i class="fa fa-database"></i><span><?php echo !empty($auditApps) ? count($auditApps) : 0; ?></span></div>
                                    <?php if ($unreadAuditCount > 0) { ?>
                                        <div class="app-rec-unread audit"><i class="fa fa-bell"></i><span><?php echo (int)$unreadAuditCount; ?> unread</span></div>
                                    <?php } ?>
                                </div>
                                <table class="table table-hover table-sortable app-rec-table" id="tab_audit_applications" width="100%">
                                    <thead>
                                        <tr>
                                            <th><small>Sr.</small></th>
                                            <th><small>Submitted On</small></th>
                                            <th><small>Organization</small></th>
                                            <th style="width: 90px; text-align: center;"><small>Action</small></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($auditApps)) {
                                        $sr = 0;
                                        foreach ($auditApps as $row) {
                                            $sr++;
                                            $displayDate = '';
                                            if (!empty($row['created_at'])) {
                                                try {
                                                    $dt = new DateTimeImmutable($row['created_at'], new DateTimeZone('Asia/Kolkata'));
                                                    $displayDate = $dt->format('d M Y, h:i A');
                                                } catch (Exception $e) {
                                                    $displayDate = $row['created_at'];
                                                }
                                            }
                                            $isUnread = isset($row['read_flag']) && (int)$row['read_flag'] === 0;
                                            $viewPage = 'applicationReceivedView.php';
                                            ?>
                                            <tr class="app-rec-row <?php echo $isUnread ? 'unread' : 'read'; ?> audit_application">
                                                <td><small><?php echo $sr; ?></small></td>
                                                <td>
                                                    <small>
                                                        <i class="fa fa-file-text-o app-rec-type-icon" style="color:#2563eb;"></i>
                                                        <i class="fa fa-clock-o app-rec-date-icon"></i>
                                                        <?php echo htmlspecialchars($displayDate, ENT_QUOTES, 'UTF-8'); ?>
                                                    </small>
                                                </td>
                                                <td class="app-rec-org">
                                                    <small>
                                                        <i class="fa fa-building-o app-rec-org-icon"></i>
                                                        <a href="<?php echo $viewPage; ?>?id=<?php echo (int)$row['id']; ?>">
                                                            <?php echo htmlspecialchars($row['org_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                        <?php if ($isUnread) { ?>
                                                            <span class="app-rec-unread-pill audit_application">
                                                                <i class="fa fa-bell"></i>Unread
                                                            </span>
                                                        <?php } ?>
                                                        <span class="app-rec-type-badge audit_application">Audit App</span>
                                                    </small>
                                                </td>
                                                <td style="text-align: center;">
                                                    <form action="<?php echo $viewPage; ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this?');" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete_app">
                                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                        <input type="hidden" name="form_type" value="audit_application">
                                                        <button type="submit" class="btn btn-xs btn-danger" title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="4"><small>No audit applications received yet.</small></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="app-rec-card table-responsive info">
                                <div class="app-rec-header">
                                    <div class="app-rec-title">
                                        <div class="app-rec-title-icon info"><i class="fa fa-refresh"></i></div>
                                        <div>
                                            <div class="app-rec-title-text-main">Information Updation Forms</div>
                                            <div class="app-rec-title-text-sub">Latest info updation submissions</div>
                                        </div>
                                    </div>
                                    <div class="app-rec-count info"><i class="fa fa-database"></i><span><?php echo !empty($infoUpdates) ? count($infoUpdates) : 0; ?></span></div>
                                    <?php if ($unreadInfoUpdateCount > 0) { ?>
                                        <div class="app-rec-unread info"><i class="fa fa-bell"></i><span><?php echo (int)$unreadInfoUpdateCount; ?> unread</span></div>
                                    <?php } ?>
                                </div>
                                <table class="table table-hover table-sortable app-rec-table" id="tab_info_updations" width="100%">
                                    <thead>
                                        <tr>
                                            <th><small>Sr.</small></th>
                                            <th><small>Submitted On</small></th>
                                            <th><small>Organization</small></th>
                                            <th style="width: 90px; text-align: center;"><small>Action</small></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($infoUpdates)) {
                                        $sr = 0;
                                        foreach ($infoUpdates as $row) {
                                            $sr++;
                                            $displayDate = '';
                                            if (!empty($row['created_at'])) {
                                                try {
                                                    $dt = new DateTimeImmutable($row['created_at'], new DateTimeZone('Asia/Kolkata'));
                                                    $displayDate = $dt->format('d M Y, h:i A');
                                                } catch (Exception $e) {
                                                    $displayDate = $row['created_at'];
                                                }
                                            }
                                            $isUnread = isset($row['read_flag']) && (int)$row['read_flag'] === 0;
                                            $viewPage = 'info_updation_view.php';
                                            ?>
                                            <tr class="app-rec-row <?php echo $isUnread ? 'unread' : 'read'; ?> info_updation">
                                                <td><small><?php echo $sr; ?></small></td>
                                                <td>
                                                    <small>
                                                        <i class="fa fa-refresh app-rec-type-icon"></i>
                                                        <i class="fa fa-clock-o app-rec-date-icon"></i>
                                                        <?php echo htmlspecialchars($displayDate, ENT_QUOTES, 'UTF-8'); ?>
                                                    </small>
                                                </td>
                                                <td class="app-rec-org">
                                                    <small>
                                                        <i class="fa fa-building-o app-rec-org-icon"></i>
                                                        <a href="<?php echo $viewPage; ?>?id=<?php echo (int)$row['id']; ?>"><em><?php echo htmlspecialchars($row['org_name'], ENT_QUOTES, 'UTF-8'); ?></em></a>
                                                        <?php if ($isUnread) { ?>
                                                            <span class="app-rec-unread-pill info_updation">
                                                                <i class="fa fa-bell"></i>New Update
                                                            </span>
                                                        <?php } ?>
                                                        <span class="app-rec-type-badge info_updation">Info Update</span>
                                                    </small>
                                                </td>
                                                <td style="text-align: center;">
                                                    <form action="<?php echo $viewPage; ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this?');" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete_app">
                                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                        <input type="hidden" name="form_type" value="info_updation">
                                                        <button type="submit" class="btn btn-xs btn-danger" title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="4"><small>No info updation forms received yet.</small></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
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

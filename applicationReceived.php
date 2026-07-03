<div id="wrapper" class="active">
<?php
$pageIdentifier = "Application Received";
include 'dbConnect.php';

date_default_timezone_set('Asia/Kolkata');

$applications = [];
$unreadCount = 0;
$unreadInfoUpdateCount = 0;
$unreadAuditCount = 0;
$unreadJobCount = 0;
$auditApps = [];
$infoUpdates = [];
$jobApps = [];

if (!function_exists('ensureApplicationReceivedJobTable')) {
    function ensureApplicationReceivedJobTable($db)
    {
        if (!$db) {
            return;
        }
        $db->exec("CREATE TABLE IF NOT EXISTS job_applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            contact_no VARCHAR(20) NOT NULL,
            apply_for VARCHAR(80) NULL,
            message TEXT,
            resume_path VARCHAR(500),
            resume_original_name VARCHAR(255),
            read_flag TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        try {
            $db->exec("ALTER TABLE job_applications ADD COLUMN apply_for VARCHAR(80) NULL AFTER contact_no");
        } catch (Exception $ignored) {
        }
    }
}

if (isset($db)) {
    try {
        ensureApplicationReceivedJobTable($db);
    } catch (Exception $e) {
    }

    try {
        // Fetch audit applications
        $stmt = $db->query("SELECT *, 'audit_application' as form_type FROM online_audit_applications ORDER BY created_at DESC");
        $auditApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $auditApps = [];
    }

    try {
        // Fetch information updation forms
        $stmt = $db->query("SELECT *, 'info_updation' as form_type FROM information_updation_forms ORDER BY created_at DESC");
        $infoUpdates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $infoUpdates = [];
    }

    try {
        // Fetch job applications
        $stmt = $db->query("SELECT *, 'job_application' as form_type FROM job_applications ORDER BY created_at DESC");
        $jobApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $jobApps = [];
    }

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
    foreach ($jobApps as $row) {
        if (isset($row['read_flag']) && (int)$row['read_flag'] === 0) {
            $unreadCount++;
            $unreadJobCount++;
        }
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

                    .app-rec-card.job {
                        grid-column: 1 / -1;
                        border-top-color: #7c3aed;
                    }

                    .app-rec-title-icon.job {
                        background: linear-gradient(135deg, #7c3aed, #4f46e5);
                        box-shadow: 0 6px 12px rgba(124, 58, 237, 0.35);
                    }

                    .app-rec-count.job {
                        background: #f3e8ff;
                        color: #6d28d9;
                    }

                    .app-rec-unread.job {
                        background: #fef3c7;
                        color: #b45309;
                    }

                    .app-rec-type-badge.job_application {
                        background: #ede9fe;
                        color: #5b21b6;
                    }

                    .app-rec-job-contact {
                        color: #475569;
                        display: block;
                        margin-top: 3px;
                    }

                    .app-rec-job-actions {
                        display: inline-flex;
                        gap: 6px;
                        align-items: center;
                    }

                    @media (max-width: 992px) {
                        .app-rec-grid {
                            grid-template-columns: 1fr;
                        }
                        .app-rec-wrapper {
                            max-width: 900px;
                        }
                    }

                    /* =============================================================
                       PREMIUM DARK MODE  —  applicationReceived.php
                       Deep charcoal surfaces, soft slate text, jewel accents.
                       Matches homePage / iaf_dashboard dark treatment.
                       ============================================================= */
                    html.theme-dark {
                        --ar-bg:        #0a0e14;
                        --ar-surface:   #111722;
                        --ar-surface-2: #161d2b;
                        --ar-border:    #232c3d;
                        --ar-text:      #dbe3f0;
                        --ar-text-dim:  #93a1b8;
                        --ar-text-faint:#6b7890;
                    }

                    html.theme-dark #page-content-wrapper { background: var(--ar-bg); color: var(--ar-text); }

                    /* Cards — keep colored top accent, dark body */
                    html.theme-dark .app-rec-card {
                        background: var(--ar-surface);
                        border-color: var(--ar-border);
                        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5);
                    }
                    html.theme-dark .app-rec-card.audit { border-top-color: #3b82f6; }
                    html.theme-dark .app-rec-card.info  { border-top-color: #22c55e; }
                    html.theme-dark .app-rec-card.job   { border-top-color: #8b5cf6; }

                    /* Header text */
                    html.theme-dark .app-rec-title-text-main { color: #eef2f8; }
                    html.theme-dark .app-rec-title-text-sub  { color: var(--ar-text-faint); }

                    /* Count + unread chips */
                    html.theme-dark .app-rec-count {
                        background: rgba(37, 99, 235, 0.16);
                        color: #93c5fd;
                    }
                    html.theme-dark .app-rec-count.info { background: rgba(22, 163, 74, 0.16); color: #6ee7b7; }
                    html.theme-dark .app-rec-count.job  { background: rgba(124, 58, 237, 0.18); color: #c4b5fd; }
                    html.theme-dark .app-rec-unread,
                    html.theme-dark .app-rec-unread.audit,
                    html.theme-dark .app-rec-unread.info,
                    html.theme-dark .app-rec-unread.job {
                        background: rgba(217, 119, 6, 0.18);
                        color: #fcd34d;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
                    }

                    /* Table */
                    html.theme-dark .app-rec-table thead th {
                        background: linear-gradient(to right, #161d2b, #142033) !important;
                        border-color: var(--ar-border) !important;
                        color: #aeb9cc;
                    }
                    html.theme-dark .app-rec-table tbody td { border-top-color: var(--ar-border); color: var(--ar-text); }
                    html.theme-dark .app-rec-table small { color: var(--ar-text-dim); }

                    /* Rows: read / unread / hover */
                    html.theme-dark .app-rec-row.read td { background: var(--ar-surface); }
                    html.theme-dark .app-rec-row.unread td {
                        background: rgba(22, 163, 74, 0.1);
                        box-shadow: inset 3px 0 0 #22c55e;
                    }
                    html.theme-dark .app-rec-row:hover td {
                        background: var(--ar-surface-2);
                        box-shadow: inset 3px 0 0 #3b82f6;
                    }
                    html.theme-dark .app-rec-row.info_updation.unread td {
                        background: rgba(217, 119, 6, 0.12);
                        box-shadow: inset 3px 0 0 #f59e0b;
                    }
                    html.theme-dark .app-rec-row.info_updation:hover td {
                        background: rgba(34, 197, 94, 0.08);
                        box-shadow: inset 3px 0 0 #22c55e;
                    }

                    /* Icons + links */
                    html.theme-dark .app-rec-date-icon,
                    html.theme-dark .app-rec-org-icon { color: var(--ar-text-faint); }
                    html.theme-dark .app-rec-org a { color: #7aa9f5; }
                    html.theme-dark .app-rec-job-contact { color: var(--ar-text-dim); }

                    /* Pills + badges — translucent jewel tones */
                    html.theme-dark .app-rec-unread-pill {
                        background: rgba(22, 163, 74, 0.18);
                        color: #6ee7b7;
                    }
                    html.theme-dark .app-rec-unread-pill.info_updation {
                        background: rgba(217, 119, 6, 0.18);
                        color: #fcd34d;
                    }
                    html.theme-dark .app-rec-type-badge.audit_application { background: rgba(37, 99, 235, 0.18); color: #93c5fd; }
                    html.theme-dark .app-rec-type-badge.info_updation     { background: rgba(22, 163, 74, 0.18); color: #6ee7b7; }
                    html.theme-dark .app-rec-type-badge.job_application   { background: rgba(124, 58, 237, 0.2);  color: #c4b5fd; }

                    /* Success alert */
                    html.theme-dark .app-rec-wrapper .alert-success {
                        background: rgba(22, 163, 74, 0.14);
                        border-color: rgba(22, 163, 74, 0.3);
                        color: #6ee7b7;
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

                            <div class="app-rec-card table-responsive job">
                                <div class="app-rec-header">
                                    <div class="app-rec-title">
                                        <div class="app-rec-title-icon job"><i class="fa fa-briefcase"></i></div>
                                        <div>
                                            <div class="app-rec-title-text-main">Job Applications</div>
                                            <div class="app-rec-title-text-sub">Career form submissions from applicants</div>
                                        </div>
                                    </div>
                                    <div class="app-rec-count job"><i class="fa fa-database"></i><span><?php echo !empty($jobApps) ? count($jobApps) : 0; ?></span></div>
                                    <?php if ($unreadJobCount > 0) { ?>
                                        <div class="app-rec-unread job"><i class="fa fa-bell"></i><span><?php echo (int)$unreadJobCount; ?> unread</span></div>
                                    <?php } ?>
                                </div>
                                <table class="table table-hover table-sortable app-rec-table" id="tab_job_applications" width="100%">
                                    <thead>
                                        <tr>
                                            <th><small>Sr.</small></th>
                                            <th><small>Submitted On</small></th>
                                            <th><small>Applicant</small></th>
                                            <th><small>Apply For</small></th>
                                            <th><small>Message</small></th>
                                            <th style="width: 120px; text-align: center;"><small>Action</small></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($jobApps)) {
                                        $sr = 0;
                                        foreach ($jobApps as $row) {
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
                                            $messageText = isset($row['message']) ? trim($row['message']) : '';
                                            if ($messageText !== '' && strlen($messageText) > 90) {
                                                $messageShort = substr($messageText, 0, 90) . '...';
                                            } else {
                                                $messageShort = $messageText;
                                            }
                                            ?>
                                            <tr class="app-rec-row <?php echo $isUnread ? 'unread' : 'read'; ?> job_application" id="job-row-<?php echo (int)$row['id']; ?>">
                                                <td><small><?php echo $sr; ?></small></td>
                                                <td>
                                                    <small>
                                                        <i class="fa fa-briefcase app-rec-type-icon" style="color:#7c3aed;"></i>
                                                        <i class="fa fa-clock-o app-rec-date-icon"></i>
                                                        <?php echo htmlspecialchars($displayDate, ENT_QUOTES, 'UTF-8'); ?>
                                                    </small>
                                                </td>
                                                <td class="app-rec-org">
                                                    <small>
                                                        <i class="fa fa-user-o app-rec-org-icon"></i>
                                                        <strong><?php echo htmlspecialchars($row['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <?php if ($isUnread) { ?>
                                                            <span class="app-rec-unread-pill audit_application">
                                                                <i class="fa fa-bell"></i>New
                                                            </span>
                                                        <?php } ?>
                                                        <span class="app-rec-type-badge job_application">Job App</span>
                                                        <span class="app-rec-job-contact">
                                                            <a href="mailto:<?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></a>
                                                            &nbsp;|&nbsp; <?php echo htmlspecialchars($row['contact_no'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    </small>
                                                </td>
                                                <td><small><?php echo htmlspecialchars($row['apply_for'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small></td>
                                                <td><small title="<?php echo htmlspecialchars($messageText, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $messageShort !== '' ? htmlspecialchars($messageShort, ENT_QUOTES, 'UTF-8') : '-'; ?></small></td>
                                                <td style="text-align: center;">
                                                    <span class="app-rec-job-actions">
                                                        <?php if ($isUnread) { ?>
                                                            <button type="button" class="btn btn-xs btn-success" title="Mark Read" onclick="markJobApplicationRead(<?php echo (int)$row['id']; ?>)">
                                                                <i class="fa fa-check"></i>
                                                            </button>
                                                        <?php } ?>
                                                        <button type="button" class="btn btn-xs btn-danger" title="Delete" onclick="deleteJobApplication(<?php echo (int)$row['id']; ?>)">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="6"><small>No job applications received yet.</small></td>
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
<script>
function markJobApplicationRead(id) {
    if (!id) {
        return;
    }
    $.ajax({
        url: 'submitJobApplication.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'mark_read', job_id: id },
        success: function () {
            location.reload();
        },
        error: function () {
            alert('Unable to update this job application right now.');
        }
    });
}

function deleteJobApplication(id) {
    if (!id || !confirm('Are you sure you want to delete this job application?')) {
        return;
    }
    $.ajax({
        url: 'submitJobApplication.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'delete', job_id: id },
        success: function () {
            location.reload();
        },
        error: function () {
            alert('Unable to delete this job application right now.');
        }
    });
}
</script>

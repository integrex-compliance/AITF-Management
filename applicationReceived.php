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
$unreadNcCount = 0;
$auditApps = [];
$infoUpdates = [];
$jobApps = [];
$ncForms = [];

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

    $isAuditor = isset($_SESSION['role']) && $_SESSION['role'] === 'Auditor';
    $auditorId = $isAuditor ? (int)$_SESSION['id'] : 0;
    
    $assignModalAuditorsList = [];
    try {
        $stmtAuditor = $db->query("SELECT user_id, user_name, email_id FROM user WHERE role = 'Auditor' AND is_deleted = '0'");
        $assignModalAuditorsList = $stmtAuditor->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
    
    $auditorCondition = $isAuditor ? " WHERE assigned_to_auditor_id = $auditorId " : "";

    try {
        // Fetch audit applications
        $stmt = $db->query("SELECT *, 'audit_application' as form_type FROM online_audit_applications $auditorCondition ORDER BY created_at DESC");
        $auditApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $auditApps = [];
    }

    try {
        // Fetch information updation forms
        $stmt = $db->query("SELECT *, 'info_updation' as form_type FROM information_updation_forms $auditorCondition ORDER BY created_at DESC");
        $infoUpdates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $infoUpdates = [];
    }

    try {
        // Fetch job applications
        $stmt = $db->query("SELECT *, 'job_application' as form_type FROM job_applications $auditorCondition ORDER BY created_at DESC");
        $jobApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $jobApps = [];
    }

    try {
        // Fetch nc closing forms
        $stmt = $db->query("SELECT *, 'nc_closing_form' as form_type FROM nc_closing_forms $auditorCondition ORDER BY created_at DESC");
        $ncForms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $ncForms = [];
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
    foreach ($ncForms as $row) {
        if (isset($row['read_flag']) && (int)$row['read_flag'] === 0) {
            $unreadCount++;
            $unreadNcCount++;
        }
    }
}
$GLOBALS['unread_app_count'] = $unreadCount;
include 'header.php';

$isTopMgmt = (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role()) || (isset($_SESSION['role']) && trim($_SESSION['role']) === 'Top Mgmt');
?>
    <?php if ($isAuditor): ?>
    <style>
        /* Hide the left sidebar menu completely for auditors */
        #sidebar-wrapper { display: none !important; }
        #wrapper, #wrapper.active { padding-left: 0 !important; }
        
        /* Disable the delete button for auditors */
        .fa-trash, .btn-danger, [onclick*="delete"], .btn-del { 
            pointer-events: none !important; 
            opacity: 0.5 !important;
            cursor: not-allowed !important;
        }
    </style>
    <?php endif; ?>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid app-rec-container">
                
                <?php if ($isAuditor): ?>
                <div style="margin-bottom: 14px;">
                    <a href="auditorDashboard.php" class="btn btn-sm btn-primary" style="background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%); border: none; border-radius: 8px; font-weight: 600; padding: 7px 15px;">
                        <i class="fa fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <?php endif; ?>

                <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1') { ?>
                    <div class="alert alert-success" style="border-radius: 10px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 16px;">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <i class="fa fa-check-circle" style="margin-right: 6px;"></i> Application deleted successfully.
                    </div>
                <?php } ?>

                <!-- Compact Hero Header (masterCreator Style) -->
                <div class="ar-hero-header">
                    <div class="ar-hero-left">
                        <h1 class="ar-hero-title">
                            <i class="fa fa-wpforms" style="color: #0d4d8b; font-size: 16px; margin-right: 8px;"></i> Application Received
                        </h1>
                    </div>
                    <div class="ar-hero-stats">
                        <div class="ar-stat-pill">
                            <span class="ar-stat-icon icon-blue"><i class="fa fa-file-text-o"></i></span>
                            <div>
                                <div class="ar-stat-num"><?php echo count($auditApps); ?></div>
                                <div class="ar-stat-lbl">Audit Apps</div>
                            </div>
                        </div>
                        <div class="ar-stat-pill">
                            <span class="ar-stat-icon icon-cyan"><i class="fa fa-file-text"></i></span>
                            <div>
                                <div class="ar-stat-num"><?php echo count($ncForms); ?></div>
                                <div class="ar-stat-lbl">NC Closings</div>
                            </div>
                        </div>
                        <div class="ar-stat-pill">
                            <span class="ar-stat-icon icon-green"><i class="fa fa-refresh"></i></span>
                            <div>
                                <div class="ar-stat-num"><?php echo count($infoUpdates); ?></div>
                                <div class="ar-stat-lbl">Info Updates</div>
                            </div>
                        </div>
                        <div class="ar-stat-pill">
                            <span class="ar-stat-icon icon-purple"><i class="fa fa-briefcase"></i></span>
                            <div>
                                <div class="ar-stat-num"><?php echo count($jobApps); ?></div>
                                <div class="ar-stat-lbl">Job Apps</div>
                            </div>
                        </div>
                        <?php if ($unreadCount > 0) { ?>
                        <div class="ar-stat-pill" style="border-color: #fecaca; background: #fff5f5;">
                            <span class="ar-stat-icon icon-red"><i class="fa fa-bell"></i></span>
                            <div>
                                <div class="ar-stat-num" style="color: #dc2626;"><?php echo $unreadCount; ?></div>
                                <div class="ar-stat-lbl" style="color: #ef4444;">Unread Total</div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Clean Folder Tabs Navigation -->
                <div class="ar-tabs">
                    <button type="button" class="ar-tab active" data-target="tab-audit">
                        <i class="fa fa-file-text-o"></i> Application Form
                        <?php if ($unreadAuditCount > 0) { ?>
                            <span class="ar-tab-badge"><?php echo $unreadAuditCount; ?></span>
                        <?php } ?>
                    </button>
                    <button type="button" class="ar-tab" data-target="tab-nc">
                        <i class="fa fa-file-text"></i> NC Closing Form
                        <?php if ($unreadNcCount > 0) { ?>
                            <span class="ar-tab-badge"><?php echo $unreadNcCount; ?></span>
                        <?php } ?>
                    </button>
                    <button type="button" class="ar-tab" data-target="tab-info">
                        <i class="fa fa-refresh"></i> Info Updation Form
                        <?php if ($unreadInfoUpdateCount > 0) { ?>
                            <span class="ar-tab-badge"><?php echo $unreadInfoUpdateCount; ?></span>
                        <?php } ?>
                    </button>
                    <button type="button" class="ar-tab" data-target="tab-job">
                        <i class="fa fa-briefcase"></i> Job Application
                        <?php if ($unreadJobCount > 0) { ?>
                            <span class="ar-tab-badge"><?php echo $unreadJobCount; ?></span>
                        <?php } ?>
                    </button>
                </div>

                <!-- Tab Panes Content -->
                <div class="ar-tab-content">
                    
                    <!-- 1. Audit Application Pane -->
                    <div class="ar-tab-pane active" id="tab-audit">
                        <div class="ar-card-header">
                            <h3 class="ar-card-title"><i class="fa fa-file-text-o"></i> Audit Applications</h3>
                            <div class="ar-card-header-stats">
                                <span class="ar-count-badge"><i class="fa fa-database"></i> <?php echo !empty($auditApps) ? count($auditApps) : 0; ?> Applications</span>
                                <?php if ($unreadAuditCount > 0) { ?>
                                    <span class="ar-unread-pill"><i class="fa fa-bell"></i> <?php echo (int)$unreadAuditCount; ?> unread</span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="ar-card-body table-responsive">
                            <table class="table ar-table table-hover" id="tab_audit_applications">
                                <thead>
                                    <tr>
                                        <th style="width: 70px; text-align: center;">Sr.</th>
                                        <th style="width: 220px;">Submitted On</th>
                                        <th>Organization / Client</th>
                                        <th style="width: 110px; text-align: center;">Action</th>
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
                                        <tr class="<?php echo $isUnread ? 'unread' : 'read'; ?>">
                                            <td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $sr; ?></td>
                                            <td>
                                                <span style="font-weight: 600; color: #334155;">
                                                    <i class="fa fa-clock-o" style="color: #94a3b8; margin-right: 4px;"></i>
                                                    <?php echo htmlspecialchars($displayDate, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 6px;">
                                                    <i class="fa fa-building-o" style="color: #64748b;"></i>
                                                    <a class="ar-org-link" href="<?php echo $viewPage; ?>?id=<?php echo (int)$row['id']; ?>">
                                                        <?php echo htmlspecialchars($row['org_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                    <?php if ($isUnread) { ?>
                                                        <span class="ar-badge ar-badge-unread"><i class="fa fa-bell"></i> Unread</span>
                                                    <?php } ?>
                                                    <span class="ar-badge ar-badge-audit">Audit App</span>
                                                    <?php if (!empty($row['assigned_to_auditor_id'])) { 
                                                        $assignedName = 'Auditor';
                                                        foreach($assignModalAuditorsList as $aud) { if ($aud['user_id'] == $row['assigned_to_auditor_id']) $assignedName = $aud['user_name']; }
                                                        echo '<span class="ar-badge ar-badge-auditor"><i class="fa fa-user"></i> '.htmlspecialchars($assignedName).'</span>';
                                                    } ?>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                                    <?php if (Utitlity::checkForUserMainAdmin()) { ?>
                                                        <button type="button" class="ar-action-btn btn-assign" title="Assign To Auditor" onclick="openAssignModal('audit_application', <?php echo (int)$row['id']; ?>, '<?php echo (int)$row['assigned_to_auditor_id']; ?>')">
                                                            <i class="fa fa-share"></i>
                                                        </button>
                                                    <?php } ?>
                                                    <form action="<?php echo $viewPage; ?>" method="post" onsubmit="return confirmApplicationDelete(event, this);" style="display: inline; margin: 0;">
                                                        <input type="hidden" name="action" value="delete_app">
                                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                        <input type="hidden" name="form_type" value="audit_application">
                                                        <button type="submit" class="ar-action-btn btn-del" title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 30px 15px; color: #94a3b8; font-weight: 500;">
                                            <i class="fa fa-folder-open-o" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                                            No audit applications received yet.
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. NC Closing Form Pane -->
                    <div class="ar-tab-pane" id="tab-nc">
                        <div class="ar-card-header">
                            <h3 class="ar-card-title"><i class="fa fa-file-text"></i> NC Closing Forms</h3>
                            <div class="ar-card-header-stats">
                                <span class="ar-count-badge"><i class="fa fa-database"></i> <?php echo !empty($ncForms) ? count($ncForms) : 0; ?> Forms</span>
                                <?php if ($unreadNcCount > 0) { ?>
                                    <span class="ar-unread-pill"><i class="fa fa-bell"></i> <?php echo (int)$unreadNcCount; ?> unread</span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="ar-card-body table-responsive">
                            <table class="table ar-table table-hover" id="tab_nc_forms">
                                <thead>
                                    <tr>
                                        <th style="width: 70px; text-align: center;">Sr.</th>
                                        <th style="width: 220px;">Submitted On</th>
                                        <th>Organization / Client</th>
                                        <th style="width: 110px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (!empty($ncForms)) {
                                    $sr = 0;
                                    foreach ($ncForms as $row) {
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
                                        $viewPage = 'nc_closing_form_view.php';
                                        ?>
                                        <tr class="<?php echo $isUnread ? 'unread' : 'read'; ?>">
                                            <td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $sr; ?></td>
                                            <td>
                                                <span style="font-weight: 600; color: #334155;">
                                                    <i class="fa fa-clock-o" style="color: #94a3b8; margin-right: 4px;"></i>
                                                    <?php echo htmlspecialchars($displayDate, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 6px;">
                                                    <i class="fa fa-building-o" style="color: #64748b;"></i>
                                                    <a class="ar-org-link" href="<?php echo $viewPage; ?>?id=<?php echo (int)$row['id']; ?>">
                                                        <?php echo htmlspecialchars($row['org_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                    <?php if ($isUnread) { ?>
                                                        <span class="ar-badge ar-badge-unread"><i class="fa fa-bell"></i> New</span>
                                                    <?php } ?>
                                                    <span class="ar-badge ar-badge-nc">NC Form</span>
                                                    <?php if (!empty($row['assigned_to_auditor_id'])) { 
                                                        $assignedName = 'Auditor';
                                                        foreach($assignModalAuditorsList as $aud) { if ($aud['user_id'] == $row['assigned_to_auditor_id']) $assignedName = $aud['user_name']; }
                                                        echo '<span class="ar-badge ar-badge-auditor"><i class="fa fa-user"></i> '.htmlspecialchars($assignedName).'</span>';
                                                    } ?>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                                    <?php if (Utitlity::checkForUserMainAdmin()) { ?>
                                                        <button type="button" class="ar-action-btn btn-assign" title="Assign To Auditor" onclick="openAssignModal('nc_closing_form', <?php echo (int)$row['id']; ?>, '<?php echo (int)$row['assigned_to_auditor_id']; ?>')">
                                                            <i class="fa fa-share"></i>
                                                        </button>
                                                    <?php } ?>
                                                    <form action="<?php echo $viewPage; ?>" method="post" onsubmit="return confirmApplicationDelete(event, this);" style="display: inline; margin: 0;">
                                                        <input type="hidden" name="action" value="delete_app">
                                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                        <input type="hidden" name="form_type" value="nc_closing_form">
                                                        <button type="submit" class="ar-action-btn btn-del" title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 30px 15px; color: #94a3b8; font-weight: 500;">
                                            <i class="fa fa-folder-open-o" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                                            No NC closing forms received yet.
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 3. Info Updation Form Pane -->
                    <div class="ar-tab-pane" id="tab-info">
                        <div class="ar-card-header">
                            <h3 class="ar-card-title"><i class="fa fa-refresh"></i> Information Updation Forms</h3>
                            <div class="ar-card-header-stats">
                                <span class="ar-count-badge"><i class="fa fa-database"></i> <?php echo !empty($infoUpdates) ? count($infoUpdates) : 0; ?> Forms</span>
                                <?php if ($unreadInfoUpdateCount > 0) { ?>
                                    <span class="ar-unread-pill"><i class="fa fa-bell"></i> <?php echo (int)$unreadInfoUpdateCount; ?> unread</span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="ar-card-body table-responsive">
                            <table class="table ar-table table-hover" id="tab_info_updations">
                                <thead>
                                    <tr>
                                        <th style="width: 70px; text-align: center;">Sr.</th>
                                        <th style="width: 220px;">Submitted On</th>
                                        <th>Organization / Client</th>
                                        <th style="width: 110px; text-align: center;">Action</th>
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
                                        <tr class="<?php echo $isUnread ? 'unread' : 'read'; ?>">
                                            <td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $sr; ?></td>
                                            <td>
                                                <span style="font-weight: 600; color: #334155;">
                                                    <i class="fa fa-clock-o" style="color: #94a3b8; margin-right: 4px;"></i>
                                                    <?php echo htmlspecialchars($displayDate, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 6px;">
                                                    <i class="fa fa-building-o" style="color: #64748b;"></i>
                                                    <a class="ar-org-link" href="<?php echo $viewPage; ?>?id=<?php echo (int)$row['id']; ?>">
                                                        <?php echo htmlspecialchars($row['org_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                    <?php if ($isUnread) { ?>
                                                        <span class="ar-badge ar-badge-unread"><i class="fa fa-bell"></i> New Update</span>
                                                    <?php } ?>
                                                    <span class="ar-badge ar-badge-info">Info Update</span>
                                                    <?php if (!empty($row['assigned_to_auditor_id'])) { 
                                                        $assignedName = 'Auditor';
                                                        foreach($assignModalAuditorsList as $aud) { if ($aud['user_id'] == $row['assigned_to_auditor_id']) $assignedName = $aud['user_name']; }
                                                        echo '<span class="ar-badge ar-badge-auditor"><i class="fa fa-user"></i> '.htmlspecialchars($assignedName).'</span>';
                                                    } ?>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                                    <?php if (Utitlity::checkForUserMainAdmin()) { ?>
                                                        <button type="button" class="ar-action-btn btn-assign" title="Assign To Auditor" onclick="openAssignModal('info_updation', <?php echo (int)$row['id']; ?>, '<?php echo (int)$row['assigned_to_auditor_id']; ?>')">
                                                            <i class="fa fa-share"></i>
                                                        </button>
                                                    <?php } ?>
                                                    <form action="<?php echo $viewPage; ?>" method="post" onsubmit="return confirmApplicationDelete(event, this);" style="display: inline; margin: 0;">
                                                        <input type="hidden" name="action" value="delete_app">
                                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                        <input type="hidden" name="form_type" value="info_updation">
                                                        <button type="submit" class="ar-action-btn btn-del" title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 30px 15px; color: #94a3b8; font-weight: 500;">
                                            <i class="fa fa-folder-open-o" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                                            No info updation forms received yet.
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 4. Job Application Pane -->
                    <div class="ar-tab-pane" id="tab-job">
                        <div class="ar-card-header">
                            <h3 class="ar-card-title"><i class="fa fa-briefcase"></i> Job Applications</h3>
                            <div class="ar-card-header-stats">
                                <span class="ar-count-badge"><i class="fa fa-database"></i> <?php echo !empty($jobApps) ? count($jobApps) : 0; ?> Applicants</span>
                                <?php if ($unreadJobCount > 0) { ?>
                                    <span class="ar-unread-pill"><i class="fa fa-bell"></i> <?php echo (int)$unreadJobCount; ?> unread</span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="ar-card-body table-responsive">
                            <table class="table ar-table table-hover" id="tab_job_applications">
                                <thead>
                                    <tr>
                                        <th style="width: 60px; text-align: center;">Sr.</th>
                                        <th style="width: 200px;">Submitted On</th>
                                        <th>Applicant Info</th>
                                        <th style="width: 140px;">Position</th>
                                        <th>Message</th>
                                        <th style="width: 140px; text-align: center;">Action</th>
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
                                        if ($messageText !== '' && strlen($messageText) > 80) {
                                            $messageShort = substr($messageText, 0, 80) . '...';
                                        } else {
                                            $messageShort = $messageText;
                                        }
                                        ?>
                                        <tr class="<?php echo $isUnread ? 'unread' : 'read'; ?>" id="job-row-<?php echo (int)$row['id']; ?>">
                                            <td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $sr; ?></td>
                                            <td>
                                                <span style="font-weight: 600; color: #334155;">
                                                    <i class="fa fa-clock-o" style="color: #94a3b8; margin-right: 4px;"></i>
                                                    <?php echo htmlspecialchars($displayDate, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong style="color: #0f172a; font-size: 13.5px;"><?php echo htmlspecialchars($row['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <?php if ($isUnread) { ?>
                                                        <span class="ar-badge ar-badge-unread"><i class="fa fa-bell"></i> New</span>
                                                    <?php } ?>
                                                    <span class="ar-badge ar-badge-job">Job App</span>
                                                    <?php if (!empty($row['assigned_to_auditor_id'])) { 
                                                        $assignedName = 'Auditor';
                                                        foreach($assignModalAuditorsList as $aud) { if ($aud['user_id'] == $row['assigned_to_auditor_id']) $assignedName = $aud['user_name']; }
                                                        echo '<span class="ar-badge ar-badge-auditor"><i class="fa fa-user"></i> '.htmlspecialchars($assignedName).'</span>';
                                                    } ?>
                                                    <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
                                                        <a href="mailto:<?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="color: #0d4d8b; text-decoration: none;">
                                                            <i class="fa fa-envelope-o"></i> <?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                        &nbsp;|&nbsp;
                                                        <span style="color: #475569;"><i class="fa fa-phone"></i> <?php echo htmlspecialchars($row['contact_no'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="label label-info" style="font-weight: 700; border-radius: 6px; padding: 4px 8px; font-size: 11px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                                    <?php echo htmlspecialchars($row['apply_for'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span title="<?php echo htmlspecialchars($messageText, ENT_QUOTES, 'UTF-8'); ?>" style="color: #475569; font-size: 12.5px;">
                                                    <?php echo $messageShort !== '' ? htmlspecialchars($messageShort, ENT_QUOTES, 'UTF-8') : '<em style="color:#94a3b8;">No message</em>'; ?>
                                                </span>
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="display: inline-flex; gap: 5px; align-items: center;">
                                                    <?php if (Utitlity::checkForUserMainAdmin()) { ?>
                                                        <button type="button" class="ar-action-btn btn-assign" title="Assign To Auditor" onclick="openAssignModal('job_application', <?php echo (int)$row['id']; ?>, '<?php echo (int)$row['assigned_to_auditor_id']; ?>')">
                                                            <i class="fa fa-share"></i>
                                                        </button>
                                                    <?php } ?>
                                                    <button type="button" class="ar-action-btn btn-review" title="Review Comments" onclick="openJobReviewModal(<?php echo (int)$row['id']; ?>, '<?php echo htmlspecialchars($row['auditor_review'] ?? '', ENT_QUOTES, 'UTF-8'); ?>')">
                                                        <i class="fa fa-comment"></i>
                                                    </button>
                                                    <?php if ($isUnread) { ?>
                                                        <button type="button" class="ar-action-btn btn-mark-read" title="Mark As Read" onclick="markJobApplicationRead(<?php echo (int)$row['id']; ?>)">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                    <?php } ?>
                                                    <button type="button" class="ar-action-btn btn-del" title="Delete" onclick="deleteJobApplication(<?php echo (int)$row['id']; ?>)">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 30px 15px; color: #94a3b8; font-weight: 500;">
                                            <i class="fa fa-folder-open-o" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                                            No job applications received yet.
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div> <!-- End Job Pane -->

                </div> <!-- End Tab Content -->

            </div>
        </div>
    </div>
</div>

<!-- =====================================================================
     STYLISH DIALOG MODAL: ACCESS RESTRICTED / PERMISSION DENIED
     ===================================================================== -->
<div class="modal fade stylish-dialog-modal" id="permissionDeniedModal" tabindex="-1" role="dialog" aria-labelledby="permDeniedModalLabel">
  <div class="modal-dialog" role="document" style="max-width: 440px; margin-top: 100px;">
    <div class="modal-content">
      <div class="modal-header modal-header-warning">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="permDeniedModalLabel">
          <i class="fa fa-exclamation-triangle" style="margin-right: 8px;"></i> Access Restricted
        </h4>
      </div>
      <div class="modal-body text-center" style="padding: 28px 24px 20px 24px;">
        <div class="dialog-icon-wrapper warning-glow">
          <div class="dialog-icon warning">
            <i class="fa fa-lock"></i>
          </div>
        </div>
        <h3 class="dialog-title" style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">
          You are not allowed to perform this action
        </h3>
        <p style="font-size: 13.5px; color: #64748b; margin: 0; line-height: 1.5;">
          Only Top Management can delete the form.
        </p>
      </div>
      <div class="modal-footer" style="text-align: center; justify-content: center; border-top: 1px solid #f1f5f9; padding: 14px 20px;">
        <button type="button" class="btn btn-dialog-close" data-dismiss="modal">
          OK
        </button>
      </div>
    </div>
  </div>
</div>

<!-- =====================================================================
     STYLISH DIALOG MODAL: DELETE CONFIRMATION (FOR TABLE ROWS)
     ===================================================================== -->
<div class="modal fade stylish-dialog-modal" id="confirmDeleteRowModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteRowLabel">
  <div class="modal-dialog" role="document" style="max-width: 480px; margin-top: 80px;">
    <div class="modal-content">
      <div class="modal-header modal-header-danger">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="confirmDeleteRowLabel">
          <i class="fa fa-trash-o" style="margin-right: 8px;"></i> Confirm Deletion
        </h4>
      </div>
      <div class="modal-body" style="padding: 24px 22px 18px 22px;">
        <div style="display: flex; align-items: flex-start; gap: 14px;">
          <div class="dialog-icon-wrapper danger-glow" style="margin: 0; flex-shrink: 0;">
            <div class="dialog-icon danger">
              <i class="fa fa-exclamation-triangle"></i>
            </div>
          </div>
          <div style="flex: 1;">
            <h3 class="dialog-title" style="text-align: left; margin-top: 0; color: #991b1b;">Permanently delete application?</h3>
            <p class="dialog-subtitle" style="text-align: left; margin-bottom: 10px;">Are you sure you want to delete this application record? This action cannot be reversed.</p>
            <ul class="danger-list" style="margin-top: 10px; padding-left: 18px; color: #64748b; font-size: 12px;">
              <li>All application data & attachments will be deleted.</li>
              <li>No further access to this record.</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 14px 20px; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-dialog-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dialog-delete" id="btnConfirmDeleteRow">
          <i class="fa fa-trash" style="margin-right: 6px;"></i> Yes, Delete Application
        </button>
      </div>
    </div>
  </div>
</div>

<!-- =====================================================================
     STYLISH DIALOG MODAL: DELETE JOB APPLICATION CONFIRMATION
     ===================================================================== -->
<div class="modal fade stylish-dialog-modal" id="confirmDeleteJobModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteJobLabel">
  <div class="modal-dialog" role="document" style="max-width: 480px; margin-top: 80px;">
    <div class="modal-content">
      <div class="modal-header modal-header-danger">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="confirmDeleteJobLabel">
          <i class="fa fa-trash-o" style="margin-right: 8px;"></i> Delete Job Application
        </h4>
      </div>
      <div class="modal-body" style="padding: 24px 22px 18px 22px;">
        <div style="display: flex; align-items: flex-start; gap: 14px;">
          <div class="dialog-icon-wrapper danger-glow" style="margin: 0; flex-shrink: 0;">
            <div class="dialog-icon danger">
              <i class="fa fa-exclamation-triangle"></i>
            </div>
          </div>
          <div style="flex: 1;">
            <h3 class="dialog-title" style="text-align: left; margin-top: 0; color: #991b1b;">Permanently delete applicant?</h3>
            <p class="dialog-subtitle" style="text-align: left; margin-bottom: 10px;">Are you sure you want to delete this job application record & resume?</p>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 14px 20px; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-dialog-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dialog-delete" id="btnConfirmDeleteJob">
          <i class="fa fa-trash" style="margin-right: 6px;"></i> Yes, Delete Record
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Assign Auditor Modal -->
<div class="modal fade" id="assignAuditorModal" tabindex="-1" role="dialog" aria-labelledby="assignAuditorLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="assignAuditorLabel"><i class="fa fa-user-plus" style="margin-right: 6px;"></i> Assign to Auditor</h4>
      </div>
      <form id="assignAuditorForm">
          <div class="modal-body" style="padding: 20px;">
            <input type="hidden" name="form_type" id="assign_form_type">
            <input type="hidden" name="form_id" id="assign_form_id">
            <input type="hidden" name="action" value="assign_auditor">
            <div class="form-group">
                <label style="font-weight: 700; color: #334155; font-size: 13px;">Select Auditor <span style="color: #ef4444;">*</span></label>
                <select name="auditor_id" id="assign_auditor_id" class="form-control" style="border-radius: 8px; border: 1.5px solid #e2e8f0; height: 40px;" required>
                    <option value="">-- Select Auditor --</option>
                    <?php foreach ($assignModalAuditorsList as $aud) { ?>
                        <option value="<?php echo $aud['user_id']; ?>"><?php echo htmlspecialchars($aud['user_name']); ?> (<?php echo htmlspecialchars($aud['email_id']); ?>)</option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label style="font-weight: 700; color: #334155; font-size: 13px;">Message (Optional)</label>
                <textarea name="assign_message" id="assign_message" class="form-control" rows="3" placeholder="Write a message to the auditor..." style="border-radius: 8px; border: 1.5px solid #e2e8f0;"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane" style="margin-right: 4px;"></i> Assign Form</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Job Application Review Modal -->
<div class="modal fade" id="jobReviewModal" tabindex="-1" role="dialog" aria-labelledby="jobReviewLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="jobReviewLabel"><i class="fa fa-commenting" style="margin-right: 6px;"></i> Job Application Review</h4>
      </div>
      <form id="jobReviewForm">
          <div class="modal-body" style="padding: 20px;">
            <input type="hidden" name="job_id" id="review_job_id">
            <input type="hidden" name="action" value="save_job_review">
            <div class="form-group">
                <label style="font-weight: 700; color: #334155; font-size: 13px;">Review / Feedback Comments</label>
                <textarea name="auditor_review" id="review_job_text" class="form-control" rows="5" placeholder="Enter review or internal notes regarding this applicant..." style="border-radius: 8px; border: 1.5px solid #e2e8f0;"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fa fa-check" style="margin-right: 4px;"></i> Save Review</button>
          </div>
      </form>
    </div>
  </div>
</div>

<style>
/* =====================================================================
   APPLICATION RECEIVED — MODERN CLEAN SAAS DESIGN SYSTEM (masterCreator Theme)
   ===================================================================== */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

body, #wrapper {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: #f8fafc;
    color: #0f172a;
    -webkit-font-smoothing: antialiased;
}

.app-rec-container {
    max-width: 1540px;
    margin: 0 auto;
    padding: 14px 20px 60px 20px;
}

/* =====================================================================
   HERO HEADER
   ===================================================================== */
.ar-hero-header {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #eff6ff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 18px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    margin-bottom: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
}

.ar-hero-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #0d4d8b 0%, #2563eb 50%, #38bdf8 100%);
}

.ar-hero-title {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.01em;
    display: flex;
    align-items: center;
}

.ar-hero-stats {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.ar-stat-pill {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 6px 12px;
    display: flex;
    align-items: center;
    gap: 9px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: transform 0.15s, box-shadow 0.15s;
}

.ar-stat-pill:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.04);
}

.ar-stat-icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    flex-shrink: 0;
}

.ar-stat-icon.icon-blue { background: #eff6ff; color: #2563eb; }
.ar-stat-icon.icon-cyan { background: #f0f9ff; color: #0284c7; }
.ar-stat-icon.icon-green { background: #ecfdf5; color: #059669; }
.ar-stat-icon.icon-purple { background: #f5f3ff; color: #7c3aed; }
.ar-stat-icon.icon-red { background: #fef2f2; color: #dc2626; }

.ar-stat-num {
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
}

.ar-stat-lbl {
    font-size: 9.5px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    line-height: 1;
    margin-top: 2px;
}

/* =====================================================================
   TABS NAVIGATION
   ===================================================================== */
.ar-tabs {
    display: flex;
    align-items: flex-end;
    gap: 6px;
    margin-bottom: 0;
    border-bottom: 2px solid #e2e8f0;
    padding-left: 4px;
    overflow-x: auto;
}

.ar-tab {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 18px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-bottom: none;
    border-radius: 10px 10px 0 0;
    cursor: pointer;
    outline: none;
    transition: all 0.2s ease;
    white-space: nowrap;
    margin-bottom: -2px;
}

.ar-tab:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.ar-tab.active {
    background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%);
    color: #ffffff;
    border-color: #0d4d8b;
    box-shadow: 0 -2px 8px rgba(13, 77, 139, 0.2);
    z-index: 2;
}

.ar-tab-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    padding: 0 5px;
    line-height: 1;
    background: #ef4444;
    color: #ffffff;
}

.ar-tab.active .ar-tab-badge {
    background: #ffffff;
    color: #0d4d8b;
}

/* =====================================================================
   TAB CONTENT & CARDS
   ===================================================================== */
.ar-tab-pane {
    display: none;
    animation: arFadeIn 0.2s ease-out;
}

.ar-tab-pane.active {
    display: block;
}

@keyframes arFadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.ar-card-header {
    background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
    border-radius: 0 12px 0 0 !important;
    padding: 12px 20px !important;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(13, 77, 139, 0.15);
}

.ar-card-title {
    font-size: 13.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff !important;
}

.ar-card-header-stats {
    display: flex;
    gap: 8px;
    align-items: center;
}

.ar-count-badge {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    color: #ffffff;
}

.ar-unread-pill {
    background: #fef3c7;
    color: #92400e;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 800;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.ar-card-body {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    padding: 0;
    margin-bottom: 24px;
    overflow: hidden;
}

/* =====================================================================
   TABLE DESIGN
   ===================================================================== */
.ar-table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: collapse;
}

.ar-table thead th {
    background: #f8fafc !important;
    border: none !important;
    border-bottom: 2px solid #e2e8f0 !important;
    padding: 12px 16px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    color: #475569 !important;
    vertical-align: middle !important;
}

.ar-table tbody td {
    border: none !important;
    border-bottom: 1px solid #f1f5f9 !important;
    padding: 12px 16px !important;
    font-size: 13px !important;
    color: #334155 !important;
    vertical-align: middle !important;
    transition: background 0.15s ease !important;
}

.ar-table tbody tr:last-child td {
    border-bottom: none !important;
}

.ar-table tbody tr:hover td {
    background: #f8fafc !important;
}

.ar-table tbody tr.unread td {
    background: #eff6ff !important;
}

.ar-table tbody tr.unread:hover td {
    background: #e0f2fe !important;
}

.ar-org-link {
    font-weight: 700;
    color: #0d4d8b;
    text-decoration: none;
    transition: color 0.15s;
}

.ar-org-link:hover {
    color: #2563eb;
    text-decoration: underline;
}

/* Badges & Pills */
.ar-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 700;
    line-height: 1;
}

.ar-badge-unread {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fca5a5;
}

.ar-badge-audit { background: #dbeafe; color: #1e40af; }
.ar-badge-nc { background: #e0f2fe; color: #0369a1; }
.ar-badge-info { background: #d1fae5; color: #065f46; }
.ar-badge-job { background: #ede9fe; color: #5b21b6; }
.ar-badge-auditor { background: #fef08a; color: #854d0e; }

/* Action Buttons */
.ar-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    font-size: 12px;
    transition: all 0.15s ease;
    cursor: pointer;
    text-decoration: none !important;
    padding: 0;
}

.ar-action-btn.btn-assign {
    background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%);
    color: #ffffff !important;
}
.ar-action-btn.btn-assign:hover {
    transform: scale(1.06);
    color: #ffffff !important;
}

.ar-action-btn.btn-review {
    background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
    color: #ffffff !important;
}
.ar-action-btn.btn-review:hover {
    transform: scale(1.06);
    color: #ffffff !important;
}

.ar-action-btn.btn-mark-read {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: #ffffff !important;
}
.ar-action-btn.btn-mark-read:hover {
    transform: scale(1.06);
    color: #ffffff !important;
}

.ar-action-btn.btn-del {
    background: #fef2f2;
    color: #ef4444 !important;
    border: 1px solid #fee2e2;
}
.ar-action-btn.btn-del:hover {
    background: #ef4444;
    color: #ffffff !important;
    border-color: #ef4444;
}

/* =====================================================================
   STYLISH DIALOG MODALS
   ===================================================================== */
.stylish-dialog-modal .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25);
    background: #ffffff;
}

.stylish-dialog-modal .modal-header {
    padding: 14px 20px;
    border: none;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-header-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #dc2626 100%) !important;
}

.modal-header-danger {
    background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;
}

.stylish-dialog-modal .modal-title {
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 0.2px;
    color: #ffffff !important;
    display: flex;
    align-items: center;
    margin: 0;
}

.stylish-dialog-modal .close {
    color: #ffffff !important;
    opacity: 0.85;
    text-shadow: none;
    font-size: 22px;
    padding: 0;
    margin: 0;
    line-height: 1;
}
.stylish-dialog-modal .close:hover { opacity: 1; }

.dialog-icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

.dialog-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.dialog-icon.warning {
    background: #fffbeb;
    color: #d97706;
    border: 2px solid #fde68a;
}
.warning-glow {
    filter: drop-shadow(0 6px 16px rgba(245, 158, 11, 0.25));
}

.dialog-icon.danger {
    background: #fef2f2;
    color: #dc2626;
    border: 2px solid #fecaca;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    font-size: 20px;
}
.danger-glow {
    filter: drop-shadow(0 6px 16px rgba(239, 68, 68, 0.25));
}

.dialog-title {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 4px 0;
    letter-spacing: -0.01em;
}

.dialog-subtitle {
    font-size: 13.5px;
    color: #64748b;
    margin: 0 0 14px 0;
    font-weight: 500;
}

.dialog-notice-box {
    background: #fff8f8;
    border: 1px solid #fee2e2;
    border-left: 4px solid #ef4444;
    border-radius: 10px;
    padding: 12px 14px;
    text-align: left;
    margin-bottom: 16px;
}

.notice-header {
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #b91c1c;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.notice-text {
    font-size: 12.5px;
    color: #7f1d1d;
    line-height: 1.5;
}

.dialog-role-badges {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 9px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

.role-badge-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.role-lbl {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.4px;
}

.role-val {
    font-size: 11.5px;
    font-weight: 800;
    padding: 2px 10px;
    border-radius: 999px;
}

.role-val.user-role {
    background: #e2e8f0;
    color: #334155;
}

.role-val.req-role {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.role-divider {
    color: #94a3b8;
    font-size: 12px;
}

.btn-dialog-close {
    background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 700;
    padding: 8px 28px;
    box-shadow: 0 4px 12px rgba(13, 77, 139, 0.25);
    transition: all 0.15s ease;
}
.btn-dialog-close:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(13, 77, 139, 0.35);
}

.btn-dialog-cancel {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #475569;
    font-weight: 700;
    font-size: 13px;
    padding: 7px 16px;
    border-radius: 8px;
    transition: all 0.15s;
}
.btn-dialog-cancel:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.btn-dialog-delete {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 13px;
    padding: 7px 18px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    transition: all 0.15s;
}
.btn-dialog-delete:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35);
}

/* Modals */
.modal-content {
    border-radius: 12px !important;
    border: 1px solid #e2e8f0 !important;
    overflow: hidden !important;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15) !important;
}

.modal-header {
    background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
    color: #ffffff !important;
    border: none !important;
    padding: 14px 20px !important;
}

.modal-header .close {
    color: #ffffff !important;
    opacity: 0.8 !important;
    text-shadow: none !important;
}

.modal-header .close:hover {
    opacity: 1 !important;
}

.modal-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    letter-spacing: 0.2px !important;
}

.modal-footer {
    border-top: 1px solid #f1f5f9 !important;
    padding: 12px 20px !important;
    background: #f8fafc !important;
}

.modal-footer .btn {
    border-radius: 8px !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    padding: 6px 16px !important;
}

.modal-footer .btn-primary {
    background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%) !important;
    border: none !important;
}

/* =====================================================================
   DARK MODE SUPPORT
   ===================================================================== */
html.theme-dark {
    --hpd-bg: #0a0e14;
    --hpd-surface: #111722;
    --hpd-surface-2: #161d2b;
    --hpd-border: #232c3d;
    --hpd-border-2: #2e3a4f;
    --hpd-text: #dbe3f0;
    --hpd-text-dim: #93a1b8;
    --hpd-text-faint: #6b7890;
    --hpd-accent: #4a9eff;
}

html.theme-dark body, 
html.theme-dark #page-content-wrapper,
html.theme-dark .page-content.inset {
    background: var(--hpd-bg) !important;
    color: var(--hpd-text) !important;
}

html.theme-dark .ar-hero-header {
    background: linear-gradient(135deg, #111722 0%, #0d131e 100%);
    border-color: var(--hpd-border);
}
html.theme-dark .ar-hero-title { color: #f8fafc; }
html.theme-dark .ar-stat-pill {
    background: var(--hpd-surface);
    border-color: var(--hpd-border);
}
html.theme-dark .ar-stat-num { color: #f8fafc; }

html.theme-dark .ar-tabs {
    border-bottom-color: var(--hpd-border);
}

html.theme-dark .ar-tab {
    background: var(--hpd-surface-2);
    border-color: var(--hpd-border);
    color: var(--hpd-text-dim);
}

html.theme-dark .ar-tab:hover {
    background: var(--hpd-surface);
    color: var(--hpd-text);
}

html.theme-dark .ar-tab.active {
    background: linear-gradient(135deg, #16243d 0%, #0d1b33 100%);
    border-color: var(--hpd-border-2);
    color: #60a5fa;
}

html.theme-dark .ar-card-header {
    background: linear-gradient(135deg, #16243d 0%, #0d1b33 100%) !important;
    border-color: var(--hpd-border-2);
}

html.theme-dark .ar-card-body {
    background: var(--hpd-surface);
    border-color: var(--hpd-border);
}

html.theme-dark .ar-table thead th {
    background: var(--hpd-surface-2) !important;
    border-bottom-color: var(--hpd-border) !important;
    color: var(--hpd-text-dim) !important;
}

html.theme-dark .ar-table tbody td {
    border-bottom-color: var(--hpd-border) !important;
    color: var(--hpd-text) !important;
}

html.theme-dark .ar-table tbody tr:hover td {
    background: var(--hpd-surface-2) !important;
}

html.theme-dark .ar-table tbody tr.unread td {
    background: rgba(37, 99, 235, 0.12) !important;
}

html.theme-dark .ar-org-link {
    color: #60a5fa;
}

html.theme-dark .modal-content {
    background: var(--hpd-surface);
    border-color: var(--hpd-border);
}

html.theme-dark .modal-body {
    background: var(--hpd-surface);
    color: var(--hpd-text);
}

html.theme-dark .modal-footer {
    background: var(--hpd-surface-2) !important;
    border-top-color: var(--hpd-border) !important;
}

html.theme-dark .form-control {
    background: var(--hpd-surface-2);
    border-color: var(--hpd-border-2);
    color: var(--hpd-text);
}

html.theme-dark .form-control:focus {
    border-color: var(--hpd-accent);
    box-shadow: 0 0 0 4px rgba(74, 158, 255, 0.18);
}
</style>

<script>
var isTopMgmt = <?php echo $isTopMgmt ? 'true' : 'false'; ?>;
var pendingDeleteForm = null;
var pendingJobDeleteId = null;

function confirmApplicationDelete(e, formElem) {
    if (!isTopMgmt) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        $('#permissionDeniedModal').modal('show');
        return false;
    }
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    pendingDeleteForm = formElem;
    $('#confirmDeleteRowModal').modal('show');
    return false;
}

document.getElementById('btnConfirmDeleteRow').addEventListener('click', function() {
    if (pendingDeleteForm) {
        pendingDeleteForm.submit();
    }
});

function openAssignModal(formType, formId, currentAuditorId) {
    document.getElementById('assign_form_type').value = formType;
    document.getElementById('assign_form_id').value = formId;
    document.getElementById('assign_auditor_id').value = currentAuditorId || "";
    $('#assignAuditorModal').modal('show');
}

function openJobReviewModal(jobId, currentReview) {
    document.getElementById('review_job_id').value = jobId;
    document.getElementById('review_job_text').value = currentReview || "";
    $('#jobReviewModal').modal('show');
}

document.getElementById('assignAuditorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var btn = $(this).find('button[type="submit"]');
    var oldText = btn.html();
    btn.html('<i class="fa fa-spinner fa-spin"></i> Assigning...').prop('disabled', true);
    
    $.ajax({
        url: 'assign_form_handler.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            btn.html(oldText).prop('disabled', false);
            if (res.status === 'success') {
                alert('Form assigned and email sent successfully!');
                location.reload();
            } else {
                alert(res.message || 'Error occurred while assigning.');
            }
        },
        error: function() {
            btn.html(oldText).prop('disabled', false);
            alert('Failed to process request.');
        }
    });
});

document.getElementById('jobReviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var btn = $(this).find('button[type="submit"]');
    var oldText = btn.html();
    btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    
    $.ajax({
        url: 'submitJobApplication.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            btn.html(oldText).prop('disabled', false);
            if (res.status === 'ok') {
                alert('Review saved successfully!');
                location.reload();
            } else {
                alert(res.message || 'Error occurred while saving review.');
            }
        },
        error: function() {
            btn.html(oldText).prop('disabled', false);
            alert('Failed to process request.');
        }
    });
});

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
    if (!isTopMgmt) {
        $('#permissionDeniedModal').modal('show');
        return;
    }
    pendingJobDeleteId = id;
    $('#confirmDeleteJobModal').modal('show');
}

document.getElementById('btnConfirmDeleteJob').addEventListener('click', function() {
    if (!pendingJobDeleteId) {
        return;
    }
    var id = pendingJobDeleteId;
    $('#confirmDeleteJobModal').modal('hide');
    $.ajax({
        url: 'submitJobApplication.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'delete', job_id: id },
        success: function (res) {
            if (res.status === 'ok') {
                location.reload();
            } else {
                alert(res.message || 'Unable to delete this job application right now.');
            }
        },
        error: function () {
            alert('Unable to delete this job application right now.');
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    var tabs = document.querySelectorAll('.ar-tab');
    var panes = document.querySelectorAll('.ar-tab-pane');
    
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            // Remove active from all tabs
            tabs.forEach(function(t) { t.classList.remove('active'); });
            // Add active to clicked tab
            this.classList.add('active');
            
            // Hide all panes
            panes.forEach(function(p) { p.classList.remove('active'); });
            // Show target pane
            var targetId = this.getAttribute('data-target');
            var targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });
    });
});
</script>

<?php
/**
 * Task Dashboard Page
 * Access strictly restricted to Top Management
 */

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

if (empty($_SESSION['id']) && empty($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/task_lib.php';

$actualLoggedInRole = (string)($_SESSION['role'] ?? '');
$isTopMgmt = aitf_is_top_mgmt_task_user($actualLoggedInRole)
    || (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($actualLoggedInRole))
    || (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin()));

// Strictly restrict access to Top Management
if (!$isTopMgmt) {
    header('Location: myDashboard.php#pane-task-mgmt');
    exit;
}
?>
<div id="wrapper" class="active">
<?php
$pageIdentifier = "Task Dashboard";
include_once './header.php';
include_once './dbConnect.php';
require_once __DIR__ . '/include/staff_guidelines_lib.php';

date_default_timezone_set('Asia/Kolkata');

$actualLoggedInUserId = (int)($_SESSION['id'] ?? 0);
$actualLoggedInUsername = (string)($_SESSION['username'] ?? 'User');

// Fetch all system users
$allSystemUsers = [];
if (function_exists('aitf_get_all_system_users') && isset($db) && $db) {
    $allSystemUsers = aitf_get_all_system_users($db, false);
}

// Allowed dashboard user IDs for the logged-in user
$allowedUids = aitf_get_user_allowed_dashboard_uids($db, $actualLoggedInUserId, $actualLoggedInRole);
$canSwitchUsers = ($isTopMgmt || (is_array($allowedUids) && count($allowedUids) > 1));

// Filter switchable users: Top Mgmt sees everyone; permitted users see only granted dashboards
$switchableUsers = [];
foreach ($allSystemUsers as $su) {
    $suId = (int)$su['user_id'];
    if ($isTopMgmt || ($allowedUids === 'all') || (is_array($allowedUids) && in_array($suId, $allowedUids, true))) {
        $switchableUsers[] = $su;
    }
}

// Current target user resolution
$targetDashboardUid = $actualLoggedInUserId;
$targetDashboardName = $actualLoggedInUsername;
$targetDashboardRole = $actualLoggedInRole;
$isViewingOther = false;
$currentViewKey = '';

if ($canSwitchUsers && !empty($_GET['view_user'])) {
    $reqUserKey = trim((string)$_GET['view_user']);
    if ($reqUserKey !== '' && $reqUserKey !== 'self') {
        $targetUid = (int)preg_replace('/[^0-9]/', '', $reqUserKey);
        if ($targetUid > 0 && $targetUid !== $actualLoggedInUserId) {
            $isPermitted = $isTopMgmt || (is_array($allowedUids) && in_array($targetUid, $allowedUids, true));
            if ($isPermitted) {
                try {
                    $uStmt = $db->prepare("SELECT user_id, user_name, role FROM user WHERE user_id = :uid LIMIT 1");
                    $uStmt->execute([':uid' => $targetUid]);
                    if ($targetUserRow = $uStmt->fetch(PDO::FETCH_ASSOC)) {
                        $targetDashboardUid = (int)$targetUserRow['user_id'];
                        $targetDashboardName = $targetUserRow['user_name'];
                        $targetDashboardRole = $targetUserRow['role'];
                        $isViewingOther = true;
                        $currentViewKey = $reqUserKey;
                    }
                } catch (Throwable $e) {}
            }
        }
    }
}

// Grouped permissions for Top Management settings view
$existingPermissions = [];
if ($isTopMgmt && isset($db) && $db) {
    $existingPermissions = aitf_get_all_dashboard_permissions_grouped($db);
}
?>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid" style="padding: 24px 30px;">

                <style>
                    /* Setting Card Styling */
                    .task-setting-card {
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-radius: 12px;
                        padding: 20px 24px;
                        margin-top: 28px;
                        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
                        transition: all 0.2s ease;
                    }
                    .task-setting-card:hover {
                        border-color: #cbd5e1;
                        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
                    }
                    .task-setting-header {
                        display: inline-flex;
                        align-items: center;
                        gap: 10px;
                        font-size: 17px;
                        font-weight: 700;
                        color: #0f172a;
                    }
                    .task-setting-header i {
                        font-size: 19px;
                        color: #2563eb;
                    }
                    .task-setting-btn {
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        background: #ffffff;
                        border: 1px solid #cbd5e1;
                        padding: 7px 16px;
                        border-radius: 8px;
                        font-size: 13.5px;
                        font-weight: 600;
                        color: #334155;
                        text-decoration: none;
                        transition: all 0.15s ease;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
                    }
                    .task-setting-btn:hover, .task-setting-btn:focus {
                        background: #f8fafc;
                        color: #0f172a;
                        border-color: #94a3b8;
                        text-decoration: none;
                    }
                    .task-setting-btn i {
                        color: #2563eb;
                        font-size: 14px;
                    }

                    /* Target Users Selection Checkbox Grid */
                    .perm-users-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                        gap: 8px;
                        max-height: 270px;
                        overflow-y: auto;
                        padding: 10px;
                        background: #f8fafc;
                        border: 1px solid #e2e8f0;
                        border-radius: 8px;
                    }
                    .perm-user-item {
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-radius: 7px;
                        padding: 7px 11px;
                        display: flex;
                        align-items: center;
                        gap: 9px;
                        cursor: pointer;
                        user-select: none;
                        transition: all 0.15s ease;
                        font-size: 13px;
                    }
                    .perm-user-item:hover {
                        background: #f1f5f9;
                        border-color: #cbd5e1;
                    }
                    .perm-user-item.is-checked {
                        background: #eff6ff;
                        border-color: #93c5fd;
                    }
                    .perm-user-item input[type="checkbox"] {
                        margin: 0;
                        cursor: pointer;
                    }

                    /* Summary Table */
                    .perm-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 13px;
                    }
                    .perm-table th {
                        background: #f8fafc;
                        color: #475569;
                        font-weight: 700;
                        padding: 10px 14px;
                        border-bottom: 2px solid #e2e8f0;
                        text-align: left;
                    }
                    .perm-table td {
                        padding: 11px 14px;
                        border-bottom: 1px solid #e2e8f0;
                        vertical-align: middle;
                        color: #1e293b;
                    }
                    .perm-badge {
                        display: inline-block;
                        background: #eff6ff;
                        color: #1d4ed8;
                        border: 1px solid #bfdbfe;
                        font-size: 11.5px;
                        font-weight: 600;
                        padding: 2px 8px;
                        border-radius: 12px;
                        margin: 2px 3px;
                    }

                    /* Dark Theme Support */
                    html.theme-dark .task-setting-card {
                        background: #0f172a !important;
                        border-color: #1e293b !important;
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
                    }
                    html.theme-dark .task-setting-header {
                        color: #f8fafc !important;
                    }
                    html.theme-dark .task-setting-header i {
                        color: #3b82f6 !important;
                    }
                    html.theme-dark .task-setting-btn {
                        background: #1e293b !important;
                        border-color: #334155 !important;
                        color: #e2e8f0 !important;
                    }
                    html.theme-dark .task-setting-btn:hover {
                        background: #334155 !important;
                        color: #ffffff !important;
                    }
                    html.theme-dark .task-setting-btn i {
                        color: #60a5fa !important;
                    }
                    html.theme-dark .perm-users-grid {
                        background: #1e293b !important;
                        border-color: #334155 !important;
                    }
                    html.theme-dark .perm-user-item {
                        background: #0f172a !important;
                        border-color: #334155 !important;
                        color: #f8fafc !important;
                    }
                    html.theme-dark .perm-user-item.is-checked {
                        background: #1e3a8a !important;
                        border-color: #3b82f6 !important;
                    }
                    html.theme-dark .perm-table th {
                        background: #1e293b !important;
                        color: #cbd5e1 !important;
                        border-bottom-color: #334155 !important;
                    }
                    html.theme-dark .perm-table td {
                        border-bottom-color: #334155 !important;
                        color: #e2e8f0 !important;
                    }
                    html.theme-dark .perm-badge {
                        background: #1e3a8a !important;
                        color: #bfdbfe !important;
                        border-color: #2563eb !important;
                    }
                </style>

                <!-- Top Bar with Navigation & User Switcher -->
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <a href="myDashboard.php#pane-task-mgmt" class="btn btn-default" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; color: #475569; display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; font-size: 13px;">
                            <i class="fa fa-arrow-left"></i> My Dashboard
                        </a>
                        <h2 style="margin: 0; font-size: 21px; font-weight: 700; color: #0f172a; display: inline-flex; align-items: center; gap: 9px;">
                            <i class="fa fa-tasks" style="color: #2563eb;"></i> Task Dashboard
                        </h2>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <?php if ($canSwitchUsers && !empty($switchableUsers)): ?>
                        <!-- User Dashboard Switcher -->
                        <div style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; padding: 5px 12px; border-radius: 9px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                            <span style="font-size: 12.5px; font-weight: 700; color: #475569; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                                <i class="fa fa-users" style="color: #2563eb;"></i> View User:
                            </span>
                            <div style="position: relative; min-width: 220px;">
                                <select id="taskUserSwitcher" class="form-control" style="height: 34px; font-size: 12.5px; border-radius: 8px; border: 1.5px solid <?php echo $isViewingOther ? '#2563eb' : '#cbd5e1'; ?>; background: <?php echo $isViewingOther ? '#eff6ff' : '#ffffff'; ?>; color: <?php echo $isViewingOther ? '#1e40af' : '#0f172a'; ?>; padding: 4px 26px 4px 10px; font-weight: 600; cursor: pointer;">
                                    <option value="" <?php echo !$isViewingOther ? 'selected' : ''; ?>>👤 My Tasks (Self)</option>
                                    <optgroup label="<?php echo $isTopMgmt ? 'All System Users' : 'Permitted Dashboards'; ?> (<?php echo count($switchableUsers); ?>)">
                                        <?php foreach ($switchableUsers as $su): 
                                            $suKey = $su['key'];
                                            $isSel = ($isViewingOther && $currentViewKey === $suKey);
                                            $isMe = ((int)$su['user_id'] === $actualLoggedInUserId);
                                        ?>
                                        <option value="<?php echo htmlspecialchars($suKey); ?>" <?php echo $isSel ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($su['name']); ?> (<?php echo htmlspecialchars($su['role']); ?>)<?php echo $isMe ? ' - [You]' : ''; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                                <i class="fa fa-chevron-down" style="position: absolute; right: 10px; top: 11px; font-size: 10px; color: <?php echo $isViewingOther ? '#2563eb' : '#94a3b8'; ?>; pointer-events: none;"></i>
                            </div>
                            <?php if ($isViewingOther): ?>
                            <a href="taskManager.php" class="btn btn-xs btn-danger" style="border-radius: 6px; padding: 5px 10px; font-weight: 600;" title="Reset back to your own tasks">
                                <i class="fa fa-times"></i> Reset
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($isTopMgmt): ?>
                        <a href="#setting-section" class="task-setting-btn">
                            <i class="fa fa-cog"></i> Setting
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($isViewingOther): ?>
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-left: 5px solid #2563eb; border-radius: 8px; padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-eye" style="color: #2563eb; font-size: 16px;"></i>
                        <span style="font-size: 13.5px; color: #1e3a8a; font-weight: 600;">
                            Viewing tasks assigned to <strong><?php echo htmlspecialchars($targetDashboardName); ?></strong> (<?php echo htmlspecialchars($targetDashboardRole); ?>).
                        </span>
                    </div>
                    <a href="taskManager.php" class="btn btn-xs btn-default" style="border-radius: 6px; font-weight: 600; color: #1e3a8a; border-color: #bfdbfe; background: #ffffff;">
                        <i class="fa fa-arrow-left"></i> Back to My Tasks
                    </a>
                </div>
                <?php endif; ?>

                <!-- Task Dashboard Component Integration -->
                <?php include __DIR__ . '/include/task_manager_component.php'; ?>

                <?php if ($isTopMgmt): ?>
                <!-- Setting Section: Dashboard Access Control Patch -->
                <div id="setting-section" class="task-setting-card">
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 14px; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
                        <div class="task-setting-header">
                            <i class="fa fa-cog"></i>
                            <span>Setting</span>
                        </div>
                        <span style="background: #eff6ff; color: #1d4ed8; font-size: 12px; font-weight: 600; padding: 4px 11px; border-radius: 12px; border: 1px solid #dbeafe; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa fa-shield"></i> Dashboard Access Control
                        </span>
                    </div>

                    <!-- Policy Info Box -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 22px; font-size: 13px; color: #334155; line-height: 1.5;">
                        <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                            <i class="fa fa-info-circle" style="color: #2563eb;"></i> Dashboard Access Policy:
                        </div>
                        <div>• <strong>Top Management Role:</strong> Top Management and Admin accounts have full access to view and manage all staff task dashboards by default.</div>
                        <div>• <strong>Custom View Permissions:</strong> Grant specific staff members permission to view selected colleagues' task dashboards in their dashboard switcher.</div>
                    </div>

                    <!-- Permission Configuration Form -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px;">
                        <h4 style="margin: 0 0 16px 0; font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <i class="fa fa-user-plus" style="color: #2563eb;"></i> Grant Dashboard Access Permissions
                        </h4>

                        <div class="row">
                            <!-- Step 1: Select Viewer User -->
                            <div class="col-md-4 col-sm-12" style="margin-bottom: 14px;">
                                <label style="font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                                    1. Select Staff Member:
                                </label>
                                <select id="permViewerUser" class="form-control" style="height: 38px; font-size: 13px; font-weight: 600; border-radius: 8px;">
                                    <option value="" disabled selected>-- Select Staff Member --</option>
                                    <?php foreach ($allSystemUsers as $u): 
                                        $uId = (int)$u['user_id'];
                                        $uName = $u['name'];
                                        $uRole = $u['role'];
                                    ?>
                                    <option value="<?php echo $uId; ?>">
                                        <?php echo htmlspecialchars($uName); ?> (<?php echo htmlspecialchars($uRole); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <span style="font-size: 11.5px; color: #64748b; margin-top: 4px; display: block;">
                                    Select the user who will be granted view access to other staff dashboards.
                                </span>
                            </div>

                            <!-- Step 2: Target Dashboards Selection -->
                            <div class="col-md-8 col-sm-12" style="margin-bottom: 14px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; flex-wrap: wrap; gap: 8px;">
                                    <label style="font-size: 13px; font-weight: 700; color: #334155; margin: 0;">
                                        2. Assign Accessible Dashboards:
                                        <span id="selectedCountBadge" class="badge" style="background: #2563eb; margin-left: 6px; font-weight: 600;">0 selected</span>
                                    </label>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" id="btnSelectAllTargets" class="btn btn-xs btn-default" style="font-size: 11.5px; font-weight: 600;">Select All</button>
                                        <button type="button" id="btnDeselectAllTargets" class="btn btn-xs btn-default" style="font-size: 11.5px; font-weight: 600;">Clear All</button>
                                    </div>
                                </div>

                                <!-- Search filter for target users -->
                                <div style="position: relative; margin-bottom: 8px;">
                                    <input type="text" id="targetSearchFilter" class="form-control input-sm" placeholder="Search staff by name or role..." style="border-radius: 6px; padding-left: 28px; font-size: 12.5px;">
                                    <i class="fa fa-search" style="position: absolute; left: 9px; top: 8px; color: #94a3b8; font-size: 12px;"></i>
                                </div>

                                <!-- Checkbox Grid -->
                                <div class="perm-users-grid" id="permTargetUsersGrid">
                                    <?php foreach ($allSystemUsers as $targetU): 
                                        $tId = (int)$targetU['user_id'];
                                        $tName = $targetU['name'];
                                        $tRole = $targetU['role'];
                                    ?>
                                    <label class="perm-user-item target-user-card" data-uid="<?php echo $tId; ?>" data-search="<?php echo htmlspecialchars(strtolower($tName . ' ' . $tRole)); ?>">
                                        <input type="checkbox" name="target_user_ids[]" value="<?php echo $tId; ?>" class="perm-target-checkbox">
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?php echo htmlspecialchars($tName); ?>
                                            </div>
                                            <div style="font-size: 11px; color: #64748b;">
                                                <?php echo htmlspecialchars($tRole); ?>
                                            </div>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div style="text-align: right; margin-top: 14px; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                            <button type="button" id="btnSavePermissions" class="btn btn-primary" style="background: #2563eb; border-color: #2563eb; font-weight: 600; padding: 7px 20px; border-radius: 8px; font-size: 13.5px; display: inline-flex; align-items: center; gap: 7px;">
                                <i class="fa fa-save"></i> Save Permissions
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Existing Granted Permissions Table -->
                    <div>
                        <h4 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <i class="fa fa-list-alt" style="color: #2563eb;"></i> Active Access Permissions
                        </h4>

                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                            <table class="perm-table">
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">Staff Member</th>
                                        <th style="width: 15%;">Role</th>
                                        <th style="width: 45%;">Accessible Staff Dashboards</th>
                                        <th style="width: 15%; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="permSummaryTableBody">
                                    <?php if (!empty($existingPermissions)): ?>
                                        <?php foreach ($existingPermissions as $ep): 
                                            $vId = $ep['viewer_user_id'];
                                            $vName = $ep['viewer_name'];
                                            $vRole = $ep['viewer_role'];
                                            $targets = $ep['targets'];
                                            $targetIdsJson = htmlspecialchars(json_encode(array_column($targets, 'target_user_id')));
                                        ?>
                                        <tr data-viewer-id="<?php echo $vId; ?>">
                                            <td>
                                                <strong style="color: #0f172a;"><?php echo htmlspecialchars($vName); ?></strong>
                                            </td>
                                            <td>
                                                <span class="label label-default" style="font-size: 11px;"><?php echo htmlspecialchars($vRole); ?></span>
                                            </td>
                                            <td>
                                                <?php foreach ($targets as $tg): ?>
                                                    <span class="perm-badge" title="Can view <?php echo htmlspecialchars($tg['target_role']); ?>">
                                                        <i class="fa fa-user"></i> <?php echo htmlspecialchars($tg['target_name']); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                                <span style="font-size: 11px; color: #64748b; margin-left: 4px;">(<?php echo count($targets); ?> total)</span>
                                            </td>
                                            <td style="text-align: center;">
                                                <button type="button" class="btn btn-xs btn-primary btn-edit-perm" data-viewer-id="<?php echo $vId; ?>" data-targets="<?php echo $targetIdsJson; ?>" style="border-radius: 6px; padding: 4px 8px; font-weight: 600;" title="Edit this user's permissions">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-xs btn-danger btn-revoke-perm" data-viewer-id="<?php echo $vId; ?>" data-viewer-name="<?php echo htmlspecialchars($vName); ?>" style="border-radius: 6px; padding: 4px 8px; font-weight: 600; margin-left: 4px;" title="Revoke all access for this user">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr id="noPermsRow">
                                            <td colspan="4" style="text-align: center; color: #64748b; padding: 24px;">
                                                <i class="fa fa-info-circle" style="font-size: 16px; margin-bottom: 4px; display: block; color: #94a3b8;"></i>
                                                No custom dashboard view permissions have been configured yet.
                                                <br>By default, Top Management can view everyone's dashboard, while individual staff only see their own tasks.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // 1. User Switcher Change Handler
    $('#taskUserSwitcher').on('change', function() {
        var val = $(this).val();
        var url = 'taskManager.php';
        if (val) {
            url += '?view_user=' + encodeURIComponent(val);
        }
        window.location.href = url;
    });

    // 2. Target Users Checkbox & Selection Logic
    function updateSelectedCount() {
        var count = $('.perm-target-checkbox:checked').length;
        $('#selectedCountBadge').text(count + ' selected');

        $('.perm-target-checkbox').each(function() {
            var $card = $(this).closest('.perm-user-item');
            if ($(this).is(':checked')) {
                $card.addClass('is-checked');
            } else {
                $card.removeClass('is-checked');
            }
        });
    }

    $(document).on('change', '.perm-target-checkbox', function() {
        updateSelectedCount();
    });

    // Select All / Deselect All
    $('#btnSelectAllTargets').on('click', function() {
        var selectedViewer = parseInt($('#permViewerUser').val()) || 0;
        $('.perm-target-checkbox').each(function() {
            var val = parseInt($(this).val()) || 0;
            if (val !== selectedViewer && $(this).closest('.perm-user-item').is(':visible')) {
                $(this).prop('checked', true);
            }
        });
        updateSelectedCount();
    });

    $('#btnDeselectAllTargets').on('click', function() {
        $('.perm-target-checkbox').prop('checked', false);
        updateSelectedCount();
    });

    // Filter target users by search box
    $('#targetSearchFilter').on('keyup', function() {
        var q = $(this).val().toLowerCase().trim();
        $('.target-user-card').each(function() {
            var searchData = $(this).data('search') || '';
            if (!q || searchData.indexOf(q) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // 3. When Viewer User changes: fetch and populate their existing permissions
    $('#permViewerUser').on('change', function() {
        var viewerId = parseInt($(this).val()) || 0;
        if (!viewerId) return;

        // Disable self checkbox in target grid
        $('.perm-target-checkbox').prop('disabled', false).closest('.perm-user-item').css('opacity', '1');
        $('.perm-target-checkbox[value="' + viewerId + '"]').prop('checked', false).prop('disabled', true).closest('.perm-user-item').css('opacity', '0.45');

        // Fetch granted permissions for this viewer
        $.ajax({
            url: 'task_actions.php',
            type: 'GET',
            data: { action: 'get_user_dashboard_permissions', viewer_user_id: viewerId },
            dataType: 'json',
            success: function(resp) {
                $('.perm-target-checkbox').prop('checked', false);
                if (resp && resp.success && Array.isArray(resp.target_user_ids)) {
                    resp.target_user_ids.forEach(function(tid) {
                        $('.perm-target-checkbox[value="' + tid + '"]').prop('checked', true);
                    });
                }
                updateSelectedCount();
            },
            error: function() {
                $('.perm-target-checkbox').prop('checked', false);
                updateSelectedCount();
            }
        });
    });

    // 4. Save Permissions via AJAX
    $('#btnSavePermissions').on('click', function() {
        var viewerId = parseInt($('#permViewerUser').val()) || 0;
        if (!viewerId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Select Staff Member',
                    text: 'Please select a staff member first.'
                });
            } else {
                alert('Please select a staff member first.');
            }
            return;
        }

        var targetIds = [];
        $('.perm-target-checkbox:checked').each(function() {
            var tid = parseInt($(this).val()) || 0;
            if (tid > 0 && tid !== viewerId) {
                targetIds.push(tid);
            }
        });

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: 'task_actions.php',
            type: 'POST',
            data: {
                action: 'save_dashboard_permissions',
                viewer_user_id: viewerId,
                target_user_ids: targetIds
            },
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Permissions');
                if (resp && resp.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Permissions Saved',
                            text: 'Successfully updated dashboard access permissions for ' + (resp.viewer_name || 'selected user') + '.',
                            timer: 1800,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        alert('Permissions saved successfully!');
                        window.location.reload();
                    }
                } else {
                    var errMsg = (resp && resp.error) ? resp.error : 'Failed to save permissions.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: errMsg });
                    } else {
                        alert(errMsg);
                    }
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Permissions');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Network Error', text: 'Failed to communicate with server.' });
                } else {
                    alert('Network error while saving permissions.');
                }
            }
        });
    });

    // 5. Edit Button click in summary table
    $(document).on('click', '.btn-edit-perm', function() {
        var viewerId = $(this).data('viewer-id');
        var targets = $(this).data('targets') || [];
        $('#permViewerUser').val(viewerId).trigger('change');
        
        // Scroll smoothly to form
        $('html, body').animate({
            scrollTop: $('#setting-section').offset().top - 80
        }, 300);
    });

    // 6. Revoke Button click in summary table
    $(document).on('click', '.btn-revoke-perm', function() {
        var viewerId = $(this).data('viewer-id');
        var viewerName = $(this).data('viewer-name') || 'this user';

        function doRevoke() {
            $.ajax({
                url: 'task_actions.php',
                type: 'POST',
                data: { action: 'revoke_user_dashboard_permissions', viewer_user_id: viewerId },
                dataType: 'json',
                success: function(resp) {
                    if (resp && resp.success) {
                        window.location.reload();
                    } else {
                        alert(resp && resp.error ? resp.error : 'Failed to revoke permissions.');
                    }
                }
            });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Revoke Permissions?',
                text: 'Are you sure you want to remove all dashboard access permissions for ' + viewerName + '?',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Revoke Access'
            }).then(function(result) {
                if (result.isConfirmed) {
                    doRevoke();
                }
            });
        } else {
            if (confirm('Are you sure you want to revoke permissions for ' + viewerName + '?')) {
                doRevoke();
            }
        }
    });

});
</script>


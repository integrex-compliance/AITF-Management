<?php
/**
 * Standalone Task Manager Page
 * Microsoft To-Do Style Task Management
 */

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

if (empty($_SESSION['id']) && empty($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

header('Location: myDashboard.php');
exit;

$pageIdentifier = "Task Manager";
include_once './header.php';
include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/staff_guidelines_lib.php';
require_once __DIR__ . '/include/task_lib.php';

date_default_timezone_set('Asia/Kolkata');

$actualLoggedInUserId = (int)($_SESSION['id'] ?? 0);
$actualLoggedInRole = (string)($_SESSION['role'] ?? '');
$actualLoggedInUsername = (string)($_SESSION['username'] ?? 'User');

$isManagementUser = aitf_can_manage_tasks($actualLoggedInRole)
    || (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($actualLoggedInRole))
    || (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin()))
    || in_array(strtolower(trim($actualLoggedInRole)), ['director', 'top mgmt', 'mgmt', 'main admin', 'admin', 'finance'], true);

// Fetch all system users for Switcher if management/admin/finance
$allSystemUsers = [];
if ($isManagementUser && function_exists('aitf_get_all_system_users') && isset($db) && $db) {
    $allSystemUsers = aitf_get_all_system_users($db, false);
}

// Current target user resolution
$targetDashboardUid = $actualLoggedInUserId;
$targetDashboardName = $actualLoggedInUsername;
$targetDashboardRole = $actualLoggedInRole;
$isViewingOther = false;
$currentViewKey = '';

if ($isManagementUser && !empty($_GET['view_user'])) {
    $reqUserKey = trim((string)$_GET['view_user']);
    if ($reqUserKey !== '' && $reqUserKey !== 'self') {
        $targetUid = (int)preg_replace('/[^0-9]/', '', $reqUserKey);
        if ($targetUid > 0 && $targetUid !== $actualLoggedInUserId) {
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
?>

<div id="wrapper" class="active">
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid" style="padding: 20px 24px;">

                <!-- Top Bar with Navigation & User Switcher -->
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <a href="myDashboard.php#pane-task-mgmt" class="btn btn-default" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; color: #475569; display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; font-size: 13px;">
                            <i class="fa fa-arrow-left"></i> My Dashboard
                        </a>
                        <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #0f172a; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fa fa-check-square-o" style="color: #2563eb;"></i> Task Manager
                        </h2>
                    </div>

                    <?php if ($isManagementUser): ?>
                    <!-- View Other Dashboard Switcher -->
                    <div style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; padding: 6px 12px; border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <span style="font-size: 12.5px; font-weight: 700; color: #475569; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                            <i class="fa fa-users" style="color: #2563eb;"></i> View User Tasks:
                        </span>
                        <div style="position: relative; min-width: 220px;">
                            <select id="taskUserSwitcher" class="form-control" style="height: 34px; font-size: 12.5px; border-radius: 8px; border: 1.5px solid <?php echo $isViewingOther ? '#2563eb' : '#cbd5e1'; ?>; background: <?php echo $isViewingOther ? '#eff6ff' : '#ffffff'; ?>; color: <?php echo $isViewingOther ? '#1e40af' : '#0f172a'; ?>; padding: 4px 26px 4px 10px; font-weight: 600; cursor: pointer;">
                                <option value="" <?php echo !$isViewingOther ? 'selected' : ''; ?>>👤 My Tasks (Self)</option>
                                <?php if (!empty($allSystemUsers)): ?>
                                    <optgroup label="System Users (<?php echo count($allSystemUsers); ?>)">
                                        <?php foreach ($allSystemUsers as $su): 
                                            $suKey = $su['key'];
                                            $isSel = ($isViewingOther && $currentViewKey === $suKey);
                                            $isMe = ((int)$su['user_id'] === $actualLoggedInUserId);
                                        ?>
                                        <option value="<?php echo htmlspecialchars($suKey); ?>" <?php echo $isSel ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($su['name']); ?> (<?php echo htmlspecialchars($su['role']); ?>)<?php echo $isMe ? ' - [You]' : ''; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            </select>
                            <i class="fa fa-chevron-down" style="position: absolute; right: 10px; top: 11px; font-size: 10px; color: <?php echo $isViewingOther ? '#2563eb' : '#94a3b8'; ?>; pointer-events: none;"></i>
                        </div>
                        <?php if ($isViewingOther): ?>
                        <a href="taskManager.php" class="btn btn-xs btn-danger" style="border-radius: 6px; padding: 5px 10px; font-weight: 600;">
                            <i class="fa fa-times"></i> Reset
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($isViewingOther): ?>
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-left: 5px solid #2563eb; border-radius: 8px; padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-eye" style="color: #2563eb; font-size: 16px;"></i>
                        <span style="font-size: 13.5px; color: #1e3a8a; font-weight: 600;">
                            Viewing tasks assigned to <strong><?php echo htmlspecialchars($targetDashboardName); ?></strong> (<?php echo htmlspecialchars($targetDashboardRole); ?>).
                            As manager, you can assign new tasks here. Only <?php echo htmlspecialchars($targetDashboardName); ?> can mark their tasks as completed.
                        </span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Microsoft To-Do Component Integration -->
                <?php include __DIR__ . '/include/task_manager_component.php'; ?>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#taskUserSwitcher').on('change', function() {
        var val = $(this).val();
        var url = 'taskManager.php';
        if (val) {
            url += '?view_user=' + encodeURIComponent(val);
        }
        window.location.href = url;
    });
});
</script>

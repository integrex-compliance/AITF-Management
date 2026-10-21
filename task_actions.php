<?php
/**
 * AJAX Controller for Task Management
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/staff_guidelines_lib.php';
require_once __DIR__ . '/include/task_lib.php';

date_default_timezone_set('Asia/Kolkata');

$loggedInUserId = (int)($_SESSION['id'] ?? 0);
$loggedInRole = (string)($_SESSION['role'] ?? '');
$loggedInUsername = (string)($_SESSION['username'] ?? '');

if ($loggedInUserId <= 0 && empty($loggedInUsername)) {
    echo json_encode(['success' => false, 'error' => 'Your session has expired. Please log in again.']);
    exit;
}

$canManage = aitf_can_manage_tasks($loggedInRole);
$action = trim((string)($_REQUEST['action'] ?? ($_GET['action'] ?? ($_POST['action'] ?? ''))));

switch ($action) {

    // -------------------------------------------------------------
    // 1. Fetch tasks for a specific user
    // -------------------------------------------------------------
    case 'get_tasks':
        $targetUserId = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : $loggedInUserId;
        $status = trim((string)($_GET['status'] ?? 'all'));
        $search = trim((string)($_GET['search'] ?? ''));

        // Check permission: self, top management, or explicitly permitted
        if (!aitf_can_user_view_task_dashboard($db, $loggedInUserId, $targetUserId, $loggedInRole)) {
            echo json_encode(['success' => false, 'error' => 'Permission denied to view this user\'s tasks.']);
            exit;
        }

        $tasks = aitf_get_user_tasks($db, $targetUserId, ['status' => $status, 'search' => $search]);
        
        $pendingCount = 0;
        $completedCount = 0;
        foreach ($tasks as $t) {
            if ($t['status'] === 'completed') {
                $completedCount++;
            } else {
                $pendingCount++;
            }
        }

        echo json_encode([
            'success' => true,
            'tasks' => $tasks,
            'counts' => [
                'total' => count($tasks),
                'pending' => $pendingCount,
                'completed' => $completedCount
            ],
            'current_user_id' => $loggedInUserId,
            'target_user_id' => $targetUserId,
            'can_add' => $canManage,
            'can_delete' => aitf_can_delete_tasks($loggedInRole),
            'is_self' => ($targetUserId === $loggedInUserId)
        ]);
        exit;

    // -------------------------------------------------------------
    // -------------------------------------------------------------
    // 2. Create and assign a task (Management, Admin, Finance, or Self)
    // -------------------------------------------------------------
    case 'create_task':
        $assignedToId = (int)($_POST['assigned_to_user_id'] ?? 0);
        $assignedToKey = trim((string)($_POST['assigned_to_user_key'] ?? ''));

        // Resolve user ID from key if needed (e.g. "user_72")
        if ($assignedToId <= 0 && !empty($assignedToKey)) {
            $assignedToId = (int)preg_replace('/[^0-9]/', '', $assignedToKey);
        }

        if ($assignedToId <= 0) {
            $assignedToId = $loggedInUserId;
        }

        // Permission: Management can assign to anyone; staff can create tasks for themselves
        if (!$canManage && $assignedToId !== $loggedInUserId) {
            echo json_encode([
                'success' => false,
                'error' => 'You do not have permission to assign tasks to other users.'
            ]);
            exit;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        if ($title === '') {
            echo json_encode(['success' => false, 'error' => 'Task title is required.']);
            exit;
        }

        // Fetch user name and role
        $assignedToName = trim((string)($_POST['assigned_to_name'] ?? ''));
        $assignedToRole = trim((string)($_POST['assigned_to_role'] ?? ''));
        if ($assignedToName === '') {
            $uStmt = $db->prepare("SELECT user_name, role FROM `user` WHERE `user_id` = :id LIMIT 1");
            $uStmt->execute([':id' => $assignedToId]);
            if ($u = $uStmt->fetch(PDO::FETCH_ASSOC)) {
                $assignedToName = $u['user_name'];
                $assignedToRole = $u['role'] ?: 'Staff';
            } else {
                // Check auditors table if applicable
                $audStmt = $db->prepare("SELECT auditor_name FROM `auditors` WHERE `auditors_id` = :id LIMIT 1");
                $audStmt->execute([':id' => $assignedToId]);
                if ($aud = $audStmt->fetch(PDO::FETCH_ASSOC)) {
                    $assignedToName = $aud['auditor_name'];
                    $assignedToRole = 'Auditor';
                } else {
                    $assignedToName = $loggedInUsername ?: 'Staff';
                    $assignedToRole = $loggedInRole ?: 'Staff';
                }
            }
        }

        $dueDate = trim((string)($_POST['due_date'] ?? ''));
        if (empty($dueDate) || $dueDate === '0000-00-00') {
            $dueDate = null;
        }

        $res = aitf_create_task($db, [
            'title' => $title,
            'description' => trim((string)($_POST['description'] ?? '')),
            'assigned_to_user_id' => $assignedToId,
            'assigned_to_user_type' => 'user',
            'assigned_to_user_key' => 'user_' . $assignedToId,
            'assigned_to_name' => $assignedToName,
            'assigned_to_role' => $assignedToRole,
            'assigned_by_user_id' => $loggedInUserId,
            'assigned_by_name' => $loggedInUsername,
            'assigned_by_role' => $loggedInRole,
            'due_date' => $dueDate,
            'is_important' => !empty($_POST['is_important']) ? 1 : 0,
            'reminder_time' => trim((string)($_POST['reminder_time'] ?? ''))
        ]);

        // Process "Also assign to" additional users if requested (Top Mgmt, Admin, Finance, Managers)
        $alsoAssignedCount = 0;
        $alsoAssignUids = $_POST['also_assign_to_uids'] ?? [];
        if (!is_array($alsoAssignUids)) {
            $alsoAssignUids = array_filter(array_map('intval', explode(',', (string)$alsoAssignUids)));
        }

        if (!empty($alsoAssignUids) && $canManage) {
            foreach ($alsoAssignUids as $extraUid) {
                $extraUid = (int)$extraUid;
                if ($extraUid <= 0 || $extraUid === $assignedToId) {
                    continue;
                }

                $extraName = '';
                $extraRole = 'Staff';
                try {
                    $uStmt = $db->prepare("SELECT user_name, role FROM `user` WHERE `user_id` = :id LIMIT 1");
                    $uStmt->execute([':id' => $extraUid]);
                    if ($u = $uStmt->fetch(PDO::FETCH_ASSOC)) {
                        $extraName = $u['user_name'];
                        $extraRole = $u['role'] ?: 'Staff';
                    }
                } catch (Throwable $e) {}

                if ($extraName !== '') {
                    aitf_create_task($db, [
                        'title' => $title,
                        'description' => trim((string)($_POST['description'] ?? '')),
                        'assigned_to_user_id' => $extraUid,
                        'assigned_to_user_type' => 'user',
                        'assigned_to_user_key' => 'user_' . $extraUid,
                        'assigned_to_name' => $extraName,
                        'assigned_to_role' => $extraRole,
                        'assigned_by_user_id' => $loggedInUserId,
                        'assigned_by_name' => $loggedInUsername,
                        'assigned_by_role' => $loggedInRole,
                        'due_date' => $dueDate,
                        'is_important' => !empty($_POST['is_important']) ? 1 : 0,
                        'reminder_time' => trim((string)($_POST['reminder_time'] ?? ''))
                    ]);
                    $alsoAssignedCount++;
                }
            }
        }

        if (is_array($res)) {
            $res['also_assigned_count'] = $alsoAssignedCount;
        }

        echo json_encode($res);
        exit;

    // -------------------------------------------------------------
    // 3. Mark task completed / uncompleted (Assigned User or Management)
    // -------------------------------------------------------------
    case 'toggle_status':
        $taskId = (int)($_POST['task_id'] ?? 0);
        if ($taskId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid task ID.']);
            exit;
        }

        // Assigned user or Management can toggle status
        $res = aitf_toggle_task_status($db, $taskId, $loggedInUserId, $loggedInRole);
        echo json_encode($res);
        exit;

    // -------------------------------------------------------------
    // 4. Toggle Star / Important
    // -------------------------------------------------------------
    case 'toggle_star':
        $taskId = (int)($_POST['task_id'] ?? 0);
        if ($taskId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid task ID.']);
            exit;
        }

        $res = aitf_toggle_task_importance($db, $taskId, $loggedInUserId);
        echo json_encode($res);
        exit;

    // -------------------------------------------------------------
    // 5. Delete task (Top Mgmt, Admin, and Finance only)
    // -------------------------------------------------------------
    case 'delete_task':
        $taskId = (int)($_POST['task_id'] ?? 0);
        if ($taskId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid task ID.']);
            exit;
        }

        if (!aitf_can_delete_tasks($loggedInRole)) {
            echo json_encode([
                'success' => false,
                'error' => 'Permission denied. Only Top Management, Admin, and Finance can delete tasks.'
            ]);
            exit;
        }

        $res = aitf_delete_task($db, $taskId, $loggedInUserId, $loggedInRole);
        echo json_encode($res);
        exit;

    // -------------------------------------------------------------
    // 6. Fetch all system users for Assignee dropdown
    // -------------------------------------------------------------
    case 'get_assignable_users':
        if (!$canManage) {
            echo json_encode(['success' => false, 'error' => 'Permission denied.']);
            exit;
        }

        $users = [];
        if (function_exists('aitf_get_all_system_users')) {
            $users = array_values(aitf_get_all_system_users($db, false));
        }

        echo json_encode(['success' => true, 'users' => $users]);
        exit;

    // -------------------------------------------------------------
    // 7. Save Dashboard Permissions (Top Management only)
    // -------------------------------------------------------------
    case 'save_dashboard_permissions':
        if (!aitf_is_top_mgmt_task_user($loggedInRole)) {
            echo json_encode(['success' => false, 'error' => 'Permission denied. Only Top Management can configure dashboard permissions.']);
            exit;
        }

        $viewerUserId = (int)($_POST['viewer_user_id'] ?? 0);
        $targetUserIds = isset($_POST['target_user_ids']) && is_array($_POST['target_user_ids']) ? $_POST['target_user_ids'] : [];

        if ($viewerUserId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Please select a valid user.']);
            exit;
        }

        $res = aitf_save_user_dashboard_permissions($db, $viewerUserId, $targetUserIds, $loggedInUserId);
        echo json_encode($res);
        exit;

    // -------------------------------------------------------------
    // 8. Get User Dashboard Permissions (Top Management only)
    // -------------------------------------------------------------
    case 'get_user_dashboard_permissions':
        if (!aitf_is_top_mgmt_task_user($loggedInRole)) {
            echo json_encode(['success' => false, 'error' => 'Permission denied.']);
            exit;
        }

        $viewerUserId = (int)($_GET['viewer_user_id'] ?? 0);
        if ($viewerUserId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user ID.']);
            exit;
        }

        aitf_ensure_task_permissions_schema($db);
        $targetIds = [];
        try {
            $stmt = $db->prepare("SELECT target_user_id FROM `aitf_task_dashboard_permissions` WHERE `viewer_user_id` = :vid");
            $stmt->execute([':vid' => $viewerUserId]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $targetIds[] = (int)$row['target_user_id'];
            }
        } catch (Throwable $e) {}

        echo json_encode(['success' => true, 'target_user_ids' => $targetIds]);
        exit;

    // -------------------------------------------------------------
    // 9. Revoke User Dashboard Permissions (Top Management only)
    // -------------------------------------------------------------
    case 'revoke_user_dashboard_permissions':
        if (!aitf_is_top_mgmt_task_user($loggedInRole)) {
            echo json_encode(['success' => false, 'error' => 'Permission denied.']);
            exit;
        }

        $viewerUserId = (int)($_POST['viewer_user_id'] ?? 0);
        if ($viewerUserId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user ID.']);
            exit;
        }

        aitf_ensure_task_permissions_schema($db);
        try {
            $del = $db->prepare("DELETE FROM `aitf_task_dashboard_permissions` WHERE `viewer_user_id` = :vid");
            $del->execute([':vid' => $viewerUserId]);
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action requested.']);
        exit;
}

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
$action = trim((string)($_REQUEST['action'] ?? ''));

switch ($action) {

    // -------------------------------------------------------------
    // 1. Fetch tasks for a specific user
    // -------------------------------------------------------------
    case 'get_tasks':
        $targetUserId = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : $loggedInUserId;
        $status = trim((string)($_GET['status'] ?? 'all'));
        $search = trim((string)($_GET['search'] ?? ''));

        // If target user is someone else and current user is NOT manager, deny viewing others' tasks
        if ($targetUserId !== $loggedInUserId && !$canManage) {
            echo json_encode(['success' => false, 'error' => 'Permission denied to view other users tasks.']);
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
            'is_self' => ($targetUserId === $loggedInUserId)
        ]);
        exit;

    // -------------------------------------------------------------
    // 2. Create and assign a task (ONLY Management, Admin, Finance)
    // -------------------------------------------------------------
    case 'create_task':
        if (!$canManage) {
            echo json_encode([
                'success' => false,
                'error' => 'Only Management, Admin, and Finance roles have permission to assign tasks.'
            ]);
            exit;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        if ($title === '') {
            echo json_encode(['success' => false, 'error' => 'Task title is required.']);
            exit;
        }

        $assignedToId = (int)($_POST['assigned_to_user_id'] ?? 0);
        $assignedToKey = trim((string)($_POST['assigned_to_user_key'] ?? ''));

        // Resolve user ID from key if needed (e.g. "user_72")
        if ($assignedToId <= 0 && !empty($assignedToKey)) {
            $assignedToId = (int)preg_replace('/[^0-9]/', '', $assignedToKey);
        }

        if ($assignedToId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Please select a valid user to assign the task to.']);
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
            'is_important' => !empty($_POST['is_important']) ? 1 : 0
        ]);

        echo json_encode($res);
        exit;

    // -------------------------------------------------------------
    // 3. Mark task completed / uncompleted (ONLY Assigned User!)
    // -------------------------------------------------------------
    case 'toggle_status':
        $taskId = (int)($_POST['task_id'] ?? 0);
        if ($taskId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid task ID.']);
            exit;
        }

        // Enforce: only assigned user can toggle status!
        $res = aitf_toggle_task_status($db, $taskId, $loggedInUserId);
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
    // 5. Delete task (Creator or Manager only)
    // -------------------------------------------------------------
    case 'delete_task':
        $taskId = (int)($_POST['task_id'] ?? 0);
        if ($taskId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid task ID.']);
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

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action requested.']);
        exit;
}

<?php
// C:\xampp\htdocs\public_html\api\v1\tasks\create-task.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';
require_once __DIR__ . '/../helpers/push_helper.php';

date_default_timezone_set('Asia/Kolkata');

$user = requireAuth($db);
$currentUserId = (int)$user['user_id'];
$currentUserName = $user['user_name'];
$currentUserRole = $user['role'] ?? 'Staff';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

$input = getJsonInput();

$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$assignedToUserId = (int)($input['assigned_to_user_id'] ?? 0);
$dueDate = !empty($input['due_date']) ? trim($input['due_date']) : null;
$isImportant = !empty($input['is_important']) ? 1 : 0;

if ($assignedToUserId <= 0) {
    $assignedToUserId = $currentUserId;
}

$canManage = !empty($user['can_manage_tasks']);
$isSelfAssign = ($assignedToUserId === $currentUserId);

// Check permission: Managers can assign to anyone; staff can create tasks for themselves
if (!$canManage && !$isSelfAssign) {
    jsonResponse(false, "You do not have permission to assign tasks to other users. You can create tasks for yourself.", [], 403);
}

if (empty($title)) {
    jsonResponse(false, "Task title is required", [], 400);
}

// Look up assignee details
$assignedToName = $currentUserName;
$assignedToRole = $currentUserRole;

if (!$isSelfAssign) {
    $assigneeStmt = $GLOBALS['aitf_identity_db']->prepare("SELECT user_id, user_name, role FROM user WHERE user_id = :uid AND (is_deleted = '0' OR is_deleted = 0 OR is_deleted IS NULL) AND (account_status != 'inactive' OR account_status IS NULL) LIMIT 1");
    $assigneeStmt->execute([':uid' => $assignedToUserId]);
    $assignee = $assigneeStmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignee) {
        jsonResponse(false, 'Employee assignee not found', [], 404);
    } else {
        $assignedToName = $assignee['user_name'];
        $assignedToRole = $assignee['role'] ?? 'Staff';
    }
}

// Insert Task into aitf_tasks (matching web task_lib.php)
$insert = $db->prepare("
    INSERT INTO aitf_tasks (
        title, description, system_code,
        assigned_to_user_id, assigned_to_user_type, assigned_to_user_key,
        assigned_to_name, assigned_to_role,
        assigned_by_user_id, assigned_by_name, assigned_by_role,
        status, is_important, due_date, created_at
    ) VALUES (
        :title, :desc, :system_code,
        :to_id, 'user', :to_key,
        :to_name, :to_role,
        :by_id, :by_name, :by_role,
        'pending', :imp, :due, NOW()
    )
");

$insert->execute([
    ':title' => $title,
    ':system_code' => $user['system_code'],
    ':desc' => $description,
    ':to_id' => $assignedToUserId,
    ':to_key' => 'user_' . $assignedToUserId,
    ':to_name' => $assignedToName,
    ':to_role' => $assignedToRole,
    ':by_id' => $currentUserId,
    ':by_name' => $currentUserName,
    ':by_role' => $currentUserRole,
    ':imp' => $isImportant,
    ':due' => $dueDate
]);

$taskId = (int)$db->lastInsertId();

// Sync task to legacy task_manager table
$syncHelper = dirname(__DIR__, 3) . '/include/task_sync_helper.php';
if (file_exists($syncHelper)) {
    require_once $syncHelper;
    if (function_exists('aitf_sync_app_task_to_web')) {
        aitf_sync_app_task_to_web($db, $taskId);
    }
}

// Sync task to tenant database (e.g. qmscare) if separate
if (isset($GLOBALS['aitf_identity_db']) && $GLOBALS['aitf_identity_db'] instanceof PDO && $GLOBALS['aitf_identity_db'] !== $db && ($user['system_code'] ?? '') !== 'AITF_MAIN') {
    try {
        $tenantDb = $GLOBALS['aitf_identity_db'];
        $insTenant = $tenantDb->prepare("
            INSERT INTO aitf_tasks (
                title, description, system_code,
                assigned_to_user_id, assigned_to_user_type, assigned_to_user_key,
                assigned_to_name, assigned_to_role,
                assigned_by_user_id, assigned_by_name, assigned_by_role,
                status, is_important, due_date, created_at
            ) VALUES (
                :title, :desc, :system_code,
                :to_id, 'user', :to_key,
                :to_name, :to_role,
                :by_id, :by_name, :by_role,
                'pending', :imp, :due, NOW()
            )
        ");
        $insTenant->execute([
            ':title' => $title,
            ':system_code' => $user['system_code'],
            ':desc' => $description,
            ':to_id' => $assignedToUserId,
            ':to_key' => 'user_' . $assignedToUserId,
            ':to_name' => $assignedToName,
            ':to_role' => $assignedToRole,
            ':by_id' => $currentUserId,
            ':by_name' => $currentUserName,
            ':by_role' => $currentUserRole,
            ':imp' => $isImportant,
            ':due' => $dueDate
        ]);
        $tenantTaskId = (int)$tenantDb->lastInsertId();
        if (function_exists('aitf_sync_app_task_to_web')) {
            aitf_sync_app_task_to_web($tenantDb, $tenantTaskId);
        }
    } catch (Throwable $e) {
        error_log("Tenant task sync error: " . $e->getMessage());
    }
}

// Trigger push notification to assignee if not self
if (!$isSelfAssign && $assignedToUserId > 0) {
    $dueText = $dueDate ? "  Due: " . date('d M Y', strtotime($dueDate)) : "";
    aitf_send_fcm_notification(
        $db,
        $assignedToUserId,
        "New Task: {$title}",
        "Assigned by {$currentUserName}{$dueText}" . (!empty($description) ? "\n\"{$description}\"" : ""),
        [
            'type' => 'task_assigned',
            'reference_id' => $taskId,
            'task_id' => $taskId,
            'title' => $title,
            'assigned_by' => $currentUserName
        ],
        $user['system_code']
    );
}

jsonResponse(true, "Task created and assigned successfully", [
    'task_id' => $taskId,
    'title' => $title,
    'assigned_to' => $assignedToName
], 200);

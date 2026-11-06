<?php
// C:\xampp\htdocs\public_html\api\v1\tasks\toggle-status.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';
require_once __DIR__ . '/../helpers/push_helper.php';

date_default_timezone_set('Asia/Kolkata');

$user = requireAuth($db);
$currentUserId = (int)$user['user_id'];
$currentUserName = $user['user_name'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

$input = getJsonInput();
$taskId = (int)($input['task_id'] ?? 0);
$newStatus = trim($input['status'] ?? '');

if ($taskId <= 0) {
    jsonResponse(false, "Invalid task ID", [], 400);
}

// Check task
$taskStmt = $db->prepare("SELECT id, title, status, completed_at, assigned_to_user_id, assigned_by_user_id FROM aitf_tasks WHERE id = :id AND (system_code = :system_code OR system_code IS NULL OR system_code = '') AND is_deleted = 0 LIMIT 1");
$taskStmt->execute([':id' => $taskId, ':system_code' => $user['system_code']]);
$task = $taskStmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    jsonResponse(false, "Task not found", [], 404);
}

// User must be assignee, assigner, or manager
$isAssignee = ((int)$task['assigned_to_user_id'] === $currentUserId);
$isAssigner = ((int)$task['assigned_by_user_id'] === $currentUserId);
$isManager  = !empty($user['can_manage_tasks']);

if (!$isAssignee && !$isAssigner && !$isManager) {
    jsonResponse(false, "Unauthorized to update this task", [], 403);
}

// If status not specified, toggle between pending and completed
if (empty($newStatus)) {
    $newStatus = ($task['status'] === 'completed') ? 'pending' : 'completed';
}

$completedAt = ($newStatus === 'completed') ? date('Y-m-d H:i:s') : null;
if (!in_array($newStatus, ['pending', 'completed'], true)) {
    jsonResponse(false, 'Invalid task status', [], 422);
}
if ($newStatus === $task['status']) {
    jsonResponse(true, 'Task status is already up to date', [
        'task_id' => $taskId, 'status' => $newStatus, 'completed_at' => $task['completed_at']
    ]);
}
$completedBy = ($newStatus === 'completed') ? $currentUserId : null;

$update = $db->prepare("
    UPDATE aitf_tasks SET
        status = :st,
        completed_at = :cat,
        completed_by_user_id = :cby,
        updated_at = NOW()
    WHERE id = :id
");
$update->execute([
    ':st' => $newStatus,
    ':cat' => $completedAt,
    ':cby' => $completedBy,
    ':id' => $taskId
]);

// Sync with tenant database if present
if (isset($GLOBALS['aitf_identity_db']) && $GLOBALS['aitf_identity_db'] instanceof PDO && ($user['system_code'] ?? '') !== 'AITF_MAIN') {
    try {
        $tenantDb = $GLOBALS['aitf_identity_db'];
        $tenantDb->prepare("UPDATE aitf_tasks SET status = :st, completed_at = :cat, completed_by_user_id = :cby, updated_at = NOW() WHERE title = :title AND assigned_to_user_id = :uid")->execute([':st' => $newStatus, ':cat' => $completedAt, ':cby' => $completedBy, ':title' => $task['title'], ':uid' => $task['assigned_to_user_id']]);
        $qmsStatus = ($newStatus === 'completed') ? 'Completed' : 'Pending';
        $tenantDb->prepare("UPDATE task_manager SET status = :st, complete_date = CURDATE(), completed_at = :cat, update_date = CURDATE() WHERE task = :title AND userid = :uid")->execute([':st' => $qmsStatus, ':cat' => $completedAt, ':title' => $task['title'], ':uid' => $task['assigned_to_user_id']]);
    } catch (Throwable $e) {}
}



// If completed, notify assigner (e.g. Vaibhav Ajabe) if someone else completes it
$assignerId = (int)($task['assigned_by_user_id'] ?? 0);
if ($newStatus === 'completed' && $assignerId > 0 && $assignerId !== $currentUserId) {
    aitf_send_fcm_notification(
        $db,
        $assignerId,
        "âœ… Task Completed",
        "{$currentUserName} has marked completed a task: {$task['title']}",
        [
            'type' => 'task_completed',
            'reference_id' => $taskId,
            'task_id' => $taskId,
            'completed_by' => $currentUserName
        ],
        $user['system_code']
    );
}

jsonResponse(true, "Task status updated to {$newStatus}", [
    'task_id' => $taskId,
    'status' => $newStatus,
    'completed_at' => $completedAt
], 200);


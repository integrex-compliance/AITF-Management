<?php
// C:\xampp\htdocs\public_html\api\v1\tasks\toggle-star.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';

$user = requireAuth($db);
$currentUserId = (int)$user['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

$input = getJsonInput();
$taskId = (int)($input['task_id'] ?? 0);

if ($taskId <= 0) {
    jsonResponse(false, "Invalid task ID", [], 400);
}

// Check task
$taskStmt = $db->prepare("SELECT id, is_important, assigned_to_user_id, assigned_by_user_id FROM aitf_tasks WHERE id = :id AND (system_code = :system_code OR system_code IS NULL OR system_code = '') AND is_deleted = 0 LIMIT 1");
$taskStmt->execute([':id' => $taskId, ':system_code' => $user['system_code']]);
$task = $taskStmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    jsonResponse(false, "Task not found", [], 404);
}

if ((int)$task['assigned_to_user_id'] !== $currentUserId &&
    (int)$task['assigned_by_user_id'] !== $currentUserId && empty($user['can_manage_tasks'])) {
    jsonResponse(false, 'Unauthorized to update this task', [], 403);
}

$newStar = isset($input['is_important']) ? (int)$input['is_important'] : ((int)$task['is_important'] === 1 ? 0 : 1);
if (!in_array($newStar, [0, 1], true)) jsonResponse(false, 'Invalid star value', [], 422);

$update = $db->prepare("UPDATE aitf_tasks SET is_important = :star, updated_at = NOW() WHERE id = :id");
$update->execute([
    ':star' => $newStar,
    ':id' => $taskId
]);

jsonResponse(true, "Star updated", [
    'task_id' => $taskId,
    'is_important' => $newStar
], 200);

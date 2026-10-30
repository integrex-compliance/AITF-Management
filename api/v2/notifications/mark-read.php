<?php
// C:\xampp\htdocs\public_html\api\v1\notifications\mark-read.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';

$user = requireAuth($db);
$userId = (int)$user['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

$input = getJsonInput();
$notificationId = isset($input['notification_id']) ? (int)$input['notification_id'] : null;

if ($notificationId) {
    $stmt = $db->prepare("UPDATE aitf_notifications SET is_read = 1 WHERE id = :id AND user_id = :uid AND system_code = :system_code");
    $stmt->execute([':id' => $notificationId, ':uid' => $userId, ':system_code' => $user['system_code']]);
} else {
    // Mark all as read
    $stmt = $db->prepare("UPDATE aitf_notifications SET is_read = 1 WHERE user_id = :uid AND system_code = :system_code");
    $stmt->execute([':uid' => $userId, ':system_code' => $user['system_code']]);
}

jsonResponse(true, "Notifications updated", [], 200);

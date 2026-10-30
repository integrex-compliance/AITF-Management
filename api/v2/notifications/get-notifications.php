<?php
// C:\xampp\htdocs\public_html\api\v1\notifications\get-notifications.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';

$user = requireAuth($db);
$userId = (int)$user['user_id'];

$stmt = $db->prepare("
    SELECT id, title, body, notification_type, reference_id, is_read, created_at
    FROM aitf_notifications
    WHERE user_id = :uid AND system_code = :system_code
    ORDER BY id DESC
    LIMIT 50
");
$stmt->execute([':uid' => $userId, ':system_code' => $user['system_code']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unreadCountStmt = $db->prepare("SELECT COUNT(*) FROM aitf_notifications WHERE user_id = :uid AND system_code = :system_code AND is_read = 0");
$unreadCountStmt->execute([':uid' => $userId, ':system_code' => $user['system_code']]);
$unreadCount = (int)$unreadCountStmt->fetchColumn();

$formatted = [];
foreach ($notifications as $n) {
    $formatted[] = [
        'id' => (int)$n['id'],
        'title' => $n['title'],
        'body' => $n['body'],
        'notification_type' => $n['notification_type'],
        'reference_id' => !empty($n['reference_id']) ? (int)$n['reference_id'] : null,
        'is_read' => (bool)$n['is_read'],
        'created_at' => $n['created_at'],
        'time_ago' => date('d M, h:i A', strtotime($n['created_at']))
    ];
}

jsonResponse(true, "Notifications fetched", [
    'notifications' => $formatted,
    'unread_count' => $unreadCount
], 200);

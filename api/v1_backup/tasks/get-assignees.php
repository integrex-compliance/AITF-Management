<?php
// C:\xampp\htdocs\public_html\api\v1\tasks\get-assignees.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';

$user = requireAuth($db);
$currentUid = (int)$user['user_id'];

$stmt = $db->query("
    SELECT user_id, user_name, role, email_id, e_id 
    FROM user 
    WHERE (is_deleted = '0' OR is_deleted IS NULL) 
      AND (account_status != 'inactive' OR account_status IS NULL)
    ORDER BY user_name ASC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$assignees = [];
$selfFound = false;

foreach ($users as $u) {
    $uid = (int)$u['user_id'];
    $isSelf = ($uid === $currentUid);
    $item = [
        'user_id' => $uid,
        'user_name' => $u['user_name'] . ($isSelf ? ' (Myself)' : ''),
        'role' => $u['role'] ?? '',
        'email_id' => $u['email_id'],
        'e_id' => $u['e_id'] ?? '',
        'is_self' => $isSelf
    ];
    if ($isSelf) {
        $selfFound = true;
        array_unshift($assignees, $item);
    } else {
        $assignees[] = $item;
    }
}

if (!$selfFound) {
    array_unshift($assignees, [
        'user_id' => $currentUid,
        'user_name' => $user['user_name'] . ' (Myself)',
        'role' => $user['role'] ?? 'Staff',
        'email_id' => $user['email_id'] ?? '',
        'e_id' => $user['e_id'] ?? '',
        'is_self' => true
    ]);
}

jsonResponse(true, "Assignees fetched", [
    'assignees' => $assignees
], 200);

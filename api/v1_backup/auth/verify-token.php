<?php
// C:\xampp\htdocs\public_html\api\v1\auth\verify-token.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';

$user = requireAuth($db);

$responseData = [
    'user' => [
        'user_id' => (int)$user['user_id'],
        'user_name' => $user['user_name'],
        'email_id' => $user['email_id'],
        'role' => $user['role'],
        'e_id' => $user['e_id'],
        'system_code' => $user['system_code'] ?? 'AITF_MAIN',
        'can_manage_tasks' => (bool)$user['can_manage_tasks'],
        'can_delete_tasks' => (bool)$user['can_delete_tasks'],
        'is_face_enrolled' => (bool)$user['is_face_enrolled']
    ]
];

jsonResponse(true, "Token valid", $responseData, 200);

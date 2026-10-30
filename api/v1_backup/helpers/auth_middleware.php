<?php
// C:\xampp\htdocs\public_html\api\v1\helpers\auth_middleware.php
require_once __DIR__ . '/cors.php';
require_once 'C:/xampp/htdocs/public_html/dbConnect.php';

function requireAuth($db) {
    if (!$db instanceof PDO) {
        jsonResponse(false, "Database connection unavailable", [], 500);
    }

    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (empty($authHeader) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }

    if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        jsonResponse(false, "Authorization token required", [], 401);
    }

    $token = $matches[1];

    $stmt = $db->prepare("
        SELECT t.*, u.user_id, u.user_name, u.email_id, u.role, u.e_id, u.account_status, u.is_deleted
        FROM aitf_mobile_tokens t
        JOIN user u ON t.user_id = u.user_id
        WHERE t.token = :token AND t.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        jsonResponse(false, "Invalid or expired session token", [], 401);
    }

    if ($user['is_deleted'] === '1' || $user['is_deleted'] === 1 || ($user['account_status'] ?? '') === 'inactive') {
        jsonResponse(false, "User account is suspended or inactive", [], 403);
    }

    // Update last_used_at
    $updateStmt = $db->prepare("UPDATE aitf_mobile_tokens SET last_used_at = NOW() WHERE id = :id");
    $updateStmt->execute([':id' => $user['id']]);

    // Check if face is enrolled
    $bioStmt = $db->prepare("SELECT id FROM aitf_user_biometrics WHERE user_id = :uid LIMIT 1");
    $bioStmt->execute([':uid' => $user['user_id']]);
    $user['is_face_enrolled'] = (bool)$bioStmt->fetchColumn();

    // Permissions
    $mgmtRoles = ['Director', 'Admin', 'Top Mgmt', 'Mgmt', 'Finance'];
    $userRole = trim($user['role'] ?? '');
    $user['can_manage_tasks'] = in_array($userRole, $mgmtRoles);
    $user['can_delete_tasks'] = in_array($userRole, ['Director', 'Admin', 'Top Mgmt']);

    return $user;
}

<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/identity.php';
require_once (getenv('AITF_DB_BOOTSTRAP') ?: dirname(__DIR__, 3) . '/dbConnect.php');

function requireAuth($db) {
    if (!$db instanceof PDO) jsonResponse(false, 'Database unavailable.', [], 503);
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!is_string($auth) || !preg_match('/^Bearer ([a-f0-9]{64})$/D', $auth, $matches)) {
        jsonResponse(false, 'Authorization token required.', [], 401);
    }
    $stmt = $db->prepare('SELECT * FROM aitf_mobile_tokens WHERE token = ? AND expires_at > UTC_TIMESTAMP() LIMIT 1');
    $stmt->execute([$matches[1]]);
    $token = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$token) {
        jsonResponse(false, 'Session expired. Please sign in again.', [], 401);
    }
    $principalType = $token['principal_type'] ?? 'user';
    if ($principalType === 'legacy' || empty($principalType)) {
        $principalType = 'user';
        try {
            $db->prepare("UPDATE aitf_mobile_tokens SET principal_type = 'user' WHERE id = ?")->execute([$token['id']]);
        } catch (Throwable $e) {}
    }
    $token['principal_type'] = $principalType;
    if (!in_array($principalType, ['user', 'auditor'], true)) {
        jsonResponse(false, 'Session expired. Please sign in again.', [], 401);
    }
    $stmt = $db->prepare('SELECT * FROM aitf_systems WHERE system_code = ? AND is_active = 1');
    $stmt->execute([$token['system_code']]);
    $system = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$system) jsonResponse(false, 'Firm is inactive.', [], 403);
    $identityDb = identityDatabase($db, $system);
    if ($token['principal_type'] === 'auditor') {
        $stmt = $identityDb->prepare("SELECT auditors_id AS user_id, auditor_name AS user_name, login_email AS email_id,
            'Auditor' AS role, auditor_number AS e_id, 0 AS is_deleted, 'active' AS account_status
            FROM auditors WHERE auditors_id = ? AND login_enabled = 1");
    } else {
        $stmt = $identityDb->prepare('SELECT user_id, user_name, email_id, role, e_id, account_status, is_deleted FROM user WHERE user_id = ?');
    }
    $stmt->execute([$token['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || (int)$user['is_deleted'] === 1 || strtolower($user['account_status'] ?? '') !== 'active') {
        jsonResponse(false, 'Account is disabled or unavailable.', [], 403);
    }
    $user['token_id'] = (int)$token['id'];
    $user['system_code'] = $token['system_code'];
    $user['principal_type'] = $token['principal_type'];
    requireEmployeeAccount($user);
    $stmt = $db->prepare('UPDATE aitf_mobile_tokens SET last_used_at = UTC_TIMESTAMP() WHERE id = ?');
    $stmt->execute([$token['id']]);
    $stmt = $db->prepare('SELECT id FROM aitf_user_biometrics WHERE user_id = ? AND system_code = ? AND is_verified = 1');
    $stmt->execute([$user['user_id'], $user['system_code']]);
    $user['is_face_enrolled'] = (bool)$stmt->fetchColumn();
    $role = trim($user['role'] ?? '');
    $user['can_manage_tasks'] = in_array($role, ['Director', 'Admin', 'Top Mgmt', 'Mgmt', 'Finance'], true);
    $user['can_delete_tasks'] = in_array($role, ['Director', 'Admin', 'Top Mgmt'], true);
    $GLOBALS['aitf_identity_db'] = $identityDb;
    return $user;
}

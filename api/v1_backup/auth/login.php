<?php
// C:\xampp\htdocs\public_html\api\v1\auth\login.php
require_once __DIR__ . '/../helpers/cors.php';
require_once 'C:/xampp/htdocs/public_html/dbConnect.php';
require_once 'C:/xampp/htdocs/public_html/include/rbac_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

if (!$db instanceof PDO) {
    jsonResponse(false, "Database connection failed", [], 500);
}

$input = getJsonInput();

$systemCode = trim($input['system_code'] ?? 'AITF_MAIN');
$identifier = trim($input['identifier'] ?? '');
$password   = (string)($input['password'] ?? '');
$deviceId   = trim($input['device_id'] ?? '');
$deviceModel = trim($input['device_model'] ?? 'Android Device');
$osVersion  = trim($input['os_version'] ?? 'Android');

if (empty($identifier) || empty($password)) {
    jsonResponse(false, "Email/Employee ID and Password are required", [], 400);
}

// 1. Validate System Code
$sysStmt = $db->prepare("SELECT id, system_code, system_name, is_active, db_host, db_name, db_user, db_password FROM aitf_systems WHERE system_code = :sys LIMIT 1");
$sysStmt->execute([':sys' => $systemCode]);
$system = $sysStmt->fetch(PDO::FETCH_ASSOC);

if (!$system || (int)$system['is_active'] !== 1) {
    jsonResponse(false, "System code '{$systemCode}' is inactive or invalid", [], 400);
}

// Multi-tenant database resolver: switch to dedicated firm DB if configured
$targetDb = $db;
if (!empty($system['db_name'])) {
    $dHost = $system['db_host'] ?: 'localhost';
    $dUser = $system['db_user'] ?: 'root';
    $dPass = (string)($system['db_password'] ?? '');
    $dName = $system['db_name'];
    try {
        $targetDb = new PDO("mysql:host={$dHost};dbname={$dName};charset=utf8mb4", $dUser, $dPass);
        $targetDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Throwable $e) {
        jsonResponse(false, "Could not connect to dedicated database for '{$system['system_name']}': " . $e->getMessage(), [], 500);
    }
}

// 2. Check if Director master credentials match
$isDirectorMaster = function_exists('aitf_verify_director_credentials') && aitf_verify_director_credentials($identifier, $password);

$user = null;
if ($isDirectorMaster) {
    // Find Director account
    $stmt = $targetDb->query("SELECT user_id, user_name, email_id, role, is_deleted, e_id, account_status FROM user WHERE role IN ('Top Mgmt', 'Director') OR email_id = 'director@aitf.co.in' ORDER BY user_id ASC LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $user = [
            'user_id' => 40,
            'user_name' => 'Managing Director',
            'email_id' => $identifier,
            'role' => 'Director',
            'is_deleted' => 0,
            'e_id' => 'DIRECTOR',
            'account_status' => 'active'
        ];
    }
} else {
    // 3. Query user by email_id, e_id, contact_no, or user_name from tenant database
    $userStmt = $targetDb->prepare("
        SELECT user_id, user_name, email_id, password, role, is_deleted, e_id, account_status
        FROM user 
        WHERE (LOWER(TRIM(email_id)) = LOWER(:identifier) 
           OR LOWER(TRIM(e_id)) = LOWER(:identifier)
           OR TRIM(contact_no) = :identifier
           OR LOWER(TRIM(user_name)) = LOWER(:identifier))
        LIMIT 1
    ");
    $userStmt->execute([':identifier' => $identifier]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    // If not found in user table, check auditors table
    if (!$user) {
        $audStmt = $targetDb->prepare("
            SELECT auditors_id AS user_id, auditor_name AS user_name, login_email AS email_id, login_key_hash AS password, 'Auditor' AS role, 0 AS is_deleted, auditor_number AS e_id, 'active' AS account_status
            FROM auditors
            WHERE login_enabled = 1 AND (LOWER(TRIM(login_email)) = LOWER(:identifier) OR LOWER(TRIM(auditor_email)) = LOWER(:identifier) OR TRIM(auditor_number) = :identifier)
            LIMIT 1
        ");
        $audStmt->execute([':identifier' => $identifier]);
        $audUser = $audStmt->fetch(PDO::FETCH_ASSOC);
        if ($audUser) {
            $user = $audUser;
        }
    }

    if (!$user) {
        jsonResponse(false, "Invalid credentials. User not found.", [], 401);
    }

    if ($user['is_deleted'] === '1' || $user['is_deleted'] === 1 || ($user['account_status'] ?? '') === 'inactive') {
        jsonResponse(false, "Account is disabled or deleted. Contact administrator.", [], 403);
    }

    // 4. Verify Password using bcrypt
    if (!password_verify($password, $user['password'])) {
        jsonResponse(false, "Invalid credentials. Incorrect password.", [], 401);
    }
}

// 5. Generate 30-day Bearer Token
$token = bin2hex(random_bytes(32)); // 64-char secure token
$expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

$tokenStmt = $db->prepare("
    INSERT INTO aitf_mobile_tokens (user_id, token, system_code, device_id, device_model, os_version, expires_at)
    VALUES (:uid, :token, :sys, :dev_id, :dev_model, :os, :exp)
");
$tokenStmt->execute([
    ':uid' => (int)$user['user_id'],
    ':token' => $token,
    ':sys' => $systemCode,
    ':dev_id' => $deviceId ?: null,
    ':dev_model' => $deviceModel ?: null,
    ':os' => $osVersion ?: null,
    ':exp' => $expiresAt
]);

// 6. Check if face is enrolled
$bioStmt = $db->prepare("SELECT id FROM aitf_user_biometrics WHERE user_id = :uid LIMIT 1");
$bioStmt->execute([':uid' => (int)$user['user_id']]);
$isFaceEnrolled = (bool)$bioStmt->fetchColumn();

// 7. Role & Permission Flags
$mgmtRoles = ['Director', 'Admin', 'Top Mgmt', 'Mgmt', 'Finance'];
$userRole = trim($user['role'] ?? '');
$canManageTasks = in_array($userRole, $mgmtRoles);
$canDeleteTasks = in_array($userRole, ['Director', 'Admin', 'Top Mgmt']);

$responseData = [
    'token' => $token,
    'expires_at' => $expiresAt,
    'user' => [
        'user_id' => (int)$user['user_id'],
        'user_name' => $user['user_name'],
        'email_id' => $user['email_id'],
        'role' => $user['role'],
        'e_id' => $user['e_id'],
        'system_code' => $systemCode,
        'system_name' => $system['system_name'],
        'can_manage_tasks' => $canManageTasks,
        'can_delete_tasks' => $canDeleteTasks,
        'is_face_enrolled' => $isFaceEnrolled
    ]
];

jsonResponse(true, "Login successful", $responseData, 200);

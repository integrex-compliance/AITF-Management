<?php
// Unified Multi-Firm Auto-Detecting Login Endpoint
require_once __DIR__ . '/../helpers/cors.php';
require_once (getenv('AITF_DB_BOOTSTRAP') ?: dirname(__DIR__, 3) . '/dbConnect.php');
require_once __DIR__ . '/../helpers/identity.php';
$rbacPath = dirname(__DIR__, 3) . '/include/rbac_helper.php';
if (is_file($rbacPath)) require_once $rbacPath;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

if (!$db instanceof PDO) {
    jsonResponse(false, "Database connection failed", [], 500);
}

$input = getJsonInput();

$requestedSystemCode = trim($input['system_code'] ?? '');
$identifier = trim($input['identifier'] ?? '');
$password   = (string)($input['password'] ?? '');
$deviceId   = trim($input['device_id'] ?? '');
$deviceModel = trim($input['device_model'] ?? 'Android Device');
$osVersion  = trim($input['os_version'] ?? 'Android');
$isAuto = (empty($requestedSystemCode) || strtoupper($requestedSystemCode) === 'AUTO');

if (empty($identifier) || empty($password)) {
    jsonResponse(false, "Email/Employee ID and Password are required", [], 400);
}

// 1. Fetch all active systems from centralized aitf_systems table
$sysStmt = $db->query("
    SELECT id, system_code, system_name, is_active, db_host, db_name, db_user, db_password 
    FROM aitf_systems 
    WHERE is_active = 1
    ORDER BY id ASC
");
$systems = $sysStmt ? $sysStmt->fetchAll(PDO::FETCH_ASSOC) : [];
if (empty($systems)) {
    $systems = [[
        'id' => 1,
        'system_code' => 'AITF_MAIN',
        'system_name' => 'AITF-Management Certification Limited',
        'is_active' => 1,
        'db_host' => 'localhost',
        'db_name' => null,
        'db_user' => null,
        'db_password' => null
    ]];
}

// 2. Sort candidate systems intelligently (hint by domain or requested code)
usort($systems, function($a, $b) use ($requestedSystemCode, $identifier, $isAuto) {
    if (!$isAuto && !empty($requestedSystemCode)) {
        $aMatch = (strcasecmp($a['system_code'], $requestedSystemCode) === 0);
        $bMatch = (strcasecmp($b['system_code'], $requestedSystemCode) === 0);
        if ($aMatch && !$bMatch) return -1;
        if (!$aMatch && $bMatch) return 1;
    }
    $aIsQms = (stripos($a['system_code'], 'qms') !== false || stripos($a['system_name'], 'qms') !== false);
    $bIsQms = (stripos($b['system_code'], 'qms') !== false || stripos($b['system_name'], 'qms') !== false);
    $idHasQms = (stripos($identifier, 'qmscare') !== false || stripos($identifier, 'qms') !== false);
    $idHasAitf = (stripos($identifier, 'aitf') !== false);

    if ($idHasQms) {
        if ($aIsQms && !$bIsQms) return -1;
        if (!$aIsQms && $bIsQms) return 1;
    } else if ($idHasAitf) {
        if (!$aIsQms && $bIsQms) return -1;
        if ($aIsQms && !$bIsQms) return 1;
    }
    return 0;
});

// 3. Check if Director master credentials match
$isDirectorMaster = function_exists('aitf_verify_director_credentials') && aitf_verify_director_credentials($identifier, $password);

$matchedSystem = null;
$matchedUser = null;
$matchedPrincipalType = 'user';

if ($isDirectorMaster) {
    $matchedSystem = $systems[0];
    $targetDb = empty($matchedSystem['db_name']) ? $db : identityDatabase($db, $matchedSystem);
    $stmt = $targetDb->query("SELECT user_id, user_name, email_id, role, is_deleted, e_id, account_status FROM user WHERE role IN ('Top Mgmt', 'Director') OR email_id = 'director@aitf.co.in' ORDER BY user_id ASC LIMIT 1");
    $matchedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$matchedUser) {
        jsonResponse(false, 'A valid employee account is required. Contact your administrator.', [], 403);
    }
} else {
    // 4. Auto-detect user across candidate tenant databases
    foreach ($systems as $sys) {
        $targetDb = null;
        if (empty($sys['db_name'])) {
            $targetDb = $db;
        } else {
            try {
                $targetDb = new PDO(
                    'mysql:host=' . ($sys['db_host'] ?: 'localhost') . ';dbname=' . $sys['db_name'] . ';charset=utf8mb4',
                    $sys['db_user'] ?: 'root',
                    (string)($sys['db_password'] ?? ''),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_EMULATE_PREPARES => true,
                        PDO::ATTR_TIMEOUT => 3
                    ]
                );
            } catch (Throwable $e) {
                continue;
            }
        }

        $candPrincipal = 'user';
        // Query user table
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
        $candUser = $userStmt->fetch(PDO::FETCH_ASSOC);

        // If not in user table, check auditors table
        if (!$candUser) {
            try {
                $audStmt = $targetDb->prepare("
                    SELECT auditors_id AS user_id, auditor_name AS user_name, login_email AS email_id, login_key_hash AS password, 'Auditor' AS role, 0 AS is_deleted, auditor_number AS e_id, 'active' AS account_status
                    FROM auditors
                    WHERE login_enabled = 1 AND (LOWER(TRIM(login_email)) = LOWER(:identifier) OR LOWER(TRIM(auditor_email)) = LOWER(:identifier) OR TRIM(auditor_number) = :identifier)
                    LIMIT 1
                ");
                $audStmt->execute([':identifier' => $identifier]);
                $audUser = $audStmt->fetch(PDO::FETCH_ASSOC);
                if ($audUser) {
                    $candUser = $audUser;
                    $candPrincipal = 'auditor';
                }
            } catch (Throwable $t) {
                // Table/columns do not exist on this tenant DB
            }
        }

        if ($candUser) {
            // Verify password using bcrypt
            if (password_verify($password, $candUser['password'])) {
                if ((int)$candUser['is_deleted'] === 1 || strtolower($candUser['account_status'] ?? '') !== 'active') {
                    jsonResponse(false, "Account is disabled or deleted. Contact administrator.", [], 403);
                }
                $matchedSystem = $sys;
                $matchedUser = $candUser;
                $matchedPrincipalType = $candPrincipal;
                break;
            }
        }
    }
}

if (!$matchedUser) {
    jsonResponse(false, "Invalid sign-in details.", [], 401);
}

requireEmployeeAccount(['principal_type' => $matchedPrincipalType]);
if ((int)$matchedUser['is_deleted'] === 1 || strtolower($matchedUser['account_status'] ?? '') !== 'active') {
    jsonResponse(false, 'Account is disabled or unavailable.', [], 403);
}

// 5. Generate 30-day Bearer Token linked to detected system
$token = bin2hex(random_bytes(32)); // 64-char secure token
$expiresAt = gmdate('Y-m-d H:i:s', time() + 30 * 86400);

$tokenStmt = $db->prepare("
    INSERT INTO aitf_mobile_tokens (user_id, token, system_code, device_id, device_model, os_version, expires_at, principal_type)
    VALUES (:uid, :token, :sys, :dev_id, :dev_model, :os, :exp, :principal)
");
$tokenStmt->execute([
    ':uid' => (int)$matchedUser['user_id'],
    ':token' => $token,
    ':sys' => $matchedSystem['system_code'],
    ':dev_id' => $deviceId ?: null,
    ':dev_model' => $deviceModel ?: null,
    ':os' => $osVersion ?: null,
    ':exp' => $expiresAt,
    ':principal' => $matchedPrincipalType
]);

// 6. Check if face is enrolled for this system
$bioStmt = $db->prepare("SELECT id FROM aitf_user_biometrics WHERE user_id = :uid AND system_code = :sys AND is_verified = 1 LIMIT 1");
$bioStmt->execute([':uid' => (int)$matchedUser['user_id'], ':sys' => $matchedSystem['system_code']]);
$isFaceEnrolled = (bool)$bioStmt->fetchColumn();

// 7. Role & Permission Flags
$mgmtRoles = ['Director', 'Admin', 'Top Mgmt', 'Mgmt', 'Finance', 'Main Admin'];
$userRole = trim($matchedUser['role'] ?? '');
$canManageTasks = in_array($userRole, $mgmtRoles);
$canDeleteTasks = in_array($userRole, ['Director', 'Admin', 'Top Mgmt', 'Main Admin']);

$responseData = [
    'token' => $token,
    'expires_at' => $expiresAt,
    'user' => [
        'user_id' => (int)$matchedUser['user_id'],
        'user_name' => $matchedUser['user_name'],
        'email_id' => $matchedUser['email_id'],
        'role' => $matchedUser['role'],
        'e_id' => $matchedUser['e_id'],
        'system_code' => $matchedSystem['system_code'],
        'system_name' => $matchedSystem['system_name'],
        'can_manage_tasks' => $canManageTasks,
        'can_delete_tasks' => $canDeleteTasks,
        'is_face_enrolled' => $isFaceEnrolled
    ]
];

jsonResponse(true, "Login successful", $responseData, 200);

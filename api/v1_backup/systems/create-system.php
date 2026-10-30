<?php
// C:\xampp\htdocs\public_html\api\v1\systems\create-system.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

$currentUser = requireAuth($db);

// Only Top Management or Director roles have rights to create a new firm
$allowedRoles = ['Top Mgmt', 'Director'];
$userRole = trim($currentUser['role'] ?? '');

if (!in_array($userRole, $allowedRoles)) {
    jsonResponse(false, "Access denied. Only Top Management can add a new firm or branch.", [], 403);
}

$input = getJsonInput();

$systemName   = trim($input['system_name'] ?? '');
$systemCode   = strtoupper(trim($input['system_code'] ?? ''));
$address      = trim($input['address'] ?? '');
$contactEmail = trim($input['contact_email'] ?? '');
$contactPhone = trim($input['contact_phone'] ?? '');
$apiBaseUrl   = trim($input['api_base_url'] ?? '');
$dbHost       = trim($input['db_host'] ?? 'localhost');
$dbName       = trim($input['db_name'] ?? '');
$dbUser       = trim($input['db_user'] ?? '');
$dbPassword   = (string)($input['db_password'] ?? '');

if (empty($systemName)) {
    jsonResponse(false, "Firm / Company Name is required", [], 400);
}

// Auto-generate system_code if not provided
if (empty($systemCode)) {
    // Generate slug from name
    $slug = preg_replace('/[^A-Za-z0-9]/', '_', strtoupper($systemName));
    $slug = preg_replace('/_+/', '_', trim($slug, '_'));
    $systemCode = substr('FIRM_' . $slug, 0, 40);
}

// Check if system_code already exists
$checkStmt = $db->prepare("SELECT id FROM aitf_systems WHERE system_code = :code LIMIT 1");
$checkStmt->execute([':code' => $systemCode]);
if ($checkStmt->fetch()) {
    jsonResponse(false, "Firm code '{$systemCode}' already exists. Please choose a different code.", [], 409);
}

// Generate secure secret key for this new firm
$secretKey = bin2hex(random_bytes(32));

try {
    $insertStmt = $db->prepare("
        INSERT INTO aitf_systems (
            system_code, system_name, address, contact_email, contact_phone,
            api_base_url, db_host, db_name, db_user, db_password,
            created_by_user_id, secret_key, is_active
        ) VALUES (
            :code, :name, :address, :email, :phone,
            :api_url, :db_host, :db_name, :db_user, :db_pass,
            :uid, :secret, 1
        )
    ");

    $insertStmt->execute([
        ':code'     => $systemCode,
        ':name'     => $systemName,
        ':address'  => $address ?: null,
        ':email'    => $contactEmail ?: null,
        ':phone'    => $contactPhone ?: null,
        ':api_url'  => $apiBaseUrl ?: null,
        ':db_host'  => $dbHost ?: 'localhost',
        ':db_name'  => $dbName ?: null,
        ':db_user'  => $dbUser ?: null,
        ':db_pass'  => $dbPassword ?: null,
        ':uid'      => (int)$currentUser['user_id'],
        ':secret'   => $secretKey
    ]);

    $newId = (int)$db->lastInsertId();

    $newFirm = [
        'id'            => $newId,
        'system_code'   => $systemCode,
        'system_name'   => $systemName,
        'address'       => $address,
        'contact_email' => $contactEmail,
        'contact_phone' => $contactPhone,
        'api_base_url'  => $apiBaseUrl,
        'db_host'       => $dbHost,
        'db_name'       => $dbName,
        'is_active'     => 1,
        'created_at'    => date('Y-m-d H:i:s')
    ];

    jsonResponse(true, "Firm '{$systemName}' added successfully!", $newFirm, 201);
} catch (Throwable $e) {
    jsonResponse(false, "Failed to create firm: " . $e->getMessage(), [], 500);
}

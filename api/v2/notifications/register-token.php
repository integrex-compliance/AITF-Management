<?php
// C:\xampp\htdocs\public_html\api\v1\notifications\register-token.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';

$user = requireAuth($db);
$userId = (int)$user['user_id'];
$systemCode = $user['system_code'] ?? 'AITF_MAIN';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

$input = getJsonInput();

$fcmToken    = trim($input['fcm_token'] ?? '');
$deviceModel = trim($input['device_model'] ?? 'Android Device');
$osVersion   = trim($input['os_version'] ?? 'Android');
$appVersion  = trim($input['app_version'] ?? '1.0.0');

if (empty($fcmToken)) {
    jsonResponse(false, "fcm_token is required", [], 400);
}

// Ensure table exists on server
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `aitf_user_devices` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `system_code` VARCHAR(50) DEFAULT 'AITF_MAIN',
        `fcm_token` VARCHAR(500) NOT NULL,
        `device_model` VARCHAR(100) NULL,
        `os_version` VARCHAR(50) NULL,
        `app_version` VARCHAR(30) NULL,
        `last_active` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_user_device_fcm` (`user_id`, `fcm_token`(191)),
        INDEX `idx_dev_user` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Exception $e) {}

// Upsert into aitf_user_devices
$stmt = $db->prepare("
    INSERT INTO aitf_user_devices (
        user_id, system_code, fcm_token, device_model, os_version, app_version, last_active
    ) VALUES (
        :uid, :sys, :token, :model, :os, :app, NOW()
    ) ON DUPLICATE KEY UPDATE
        system_code = VALUES(system_code),
        device_model = VALUES(device_model),
        os_version = VALUES(os_version),
        app_version = VALUES(app_version),
        last_active = NOW()
");

$stmt->execute([
    ':uid' => $userId,
    ':sys' => $systemCode,
    ':token' => $fcmToken,
    ':model' => $deviceModel,
    ':os' => $osVersion,
    ':app' => $appVersion
]);

jsonResponse(true, "FCM token registered successfully", [
    'user_id' => $userId,
    'fcm_token' => substr($fcmToken, 0, 15) . '...'
], 200);

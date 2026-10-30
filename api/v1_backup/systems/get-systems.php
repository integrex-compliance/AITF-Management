<?php
// C:\xampp\htdocs\public_html\api\v1\systems\get-systems.php
require_once __DIR__ . '/../helpers/cors.php';
require_once 'C:/xampp/htdocs/public_html/dbConnect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, "Method not allowed", [], 405);
}

if (!$db instanceof PDO) {
    jsonResponse(false, "Database connection failed", [], 500);
}

try {
    $stmt = $db->query("
        SELECT id, system_code, system_name, address, contact_email, contact_phone, api_base_url, is_active, created_at
        FROM aitf_systems
        WHERE is_active = 1
        ORDER BY id ASC
    ");
    $systems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(true, "Active firms retrieved successfully", $systems, 200);
} catch (Throwable $e) {
    jsonResponse(false, "Failed to retrieve firms: " . $e->getMessage(), [], 500);
}

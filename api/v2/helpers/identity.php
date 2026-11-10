<?php
function identityDatabase(PDO $db, array $system): PDO {
    if (empty($system['db_name'])) return $db;
    try {
        return new PDO('mysql:host=' . ($system['db_host'] ?: 'localhost') . ';dbname=' . $system['db_name'] . ';charset=utf8mb4',
            $system['db_user'] ?: '', (string)($system['db_password'] ?? ''),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => true,
             PDO::ATTR_TIMEOUT => 5]);
    } catch (Throwable $e) {
        // Fallback to primary database if user table exists in $db
        try {
            $check = $db->query("SELECT 1 FROM user LIMIT 1");
            if ($check) return $db;
        } catch (Throwable $t) {}
        jsonResponse(false, 'The firm database is unavailable. Contact your administrator.', [], 503);
    }
}

function requireEmployeeAccount(array $user): void {
    // The existing task/attendance schema identifies employees by user_id. An
    // auditor ID may collide with an unrelated employee, so never reinterpret it.
    if (($user['principal_type'] ?? 'user') !== 'user') {
        jsonResponse(false, 'This mobile workflow requires an employee account. Contact your administrator to link your account.', [], 403);
    }
}

<?php
require_once __DIR__ . '/../helpers/auth_middleware.php';
require_once __DIR__ . '/../helpers/attendance_security.php';
try {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') throw new AttendanceRejected('Method not allowed.', 405);
    $user = requireAuth($db);
    $id = filter_var($_GET['evidence_id'] ?? '', FILTER_VALIDATE_INT);
    if (!$id || $id < 1) throw new AttendanceRejected('Photo not found.', 404);
    $stmt = $db->prepare('SELECT photo_path FROM aitf_attendance_evidence WHERE id = ? AND user_id = ? AND system_code = ?');
    $stmt->execute([$id, $user['user_id'], $user['system_code']]);
    $path = $stmt->fetchColumn();
    if (!$path) throw new AttendanceRejected('Photo not found.', 404);
    $file = attendanceBaselinePath($path);
    header('Content-Type: image/jpeg');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');
    readfile($file);
} catch (AttendanceRejected $e) {
    jsonResponse(false, $e->getMessage(), [], $e->status);
} catch (Throwable $e) {
    jsonResponse(false, 'Photo is temporarily unavailable.', [], 503);
}

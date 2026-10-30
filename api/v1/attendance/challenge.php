<?php
require_once __DIR__ . '/../helpers/auth_middleware.php';
require_once __DIR__ . '/../helpers/attendance_security.php';

attendanceEndpoint(function () use ($db) {
    $user = requireAuth($db);
    attendanceConfig();
    $purpose = getJsonInput()['purpose'] ?? '';
    if (!in_array($purpose, ['enroll', 'punch_in', 'punch_out'], true)) throw new AttendanceRejected('Invalid verification purpose.');
    $stmt = $db->prepare('SELECT COUNT(*) FROM aitf_attendance_challenges WHERE token_id = ? AND created_at > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 10 MINUTE)');
    $stmt->execute([$user['token_id']]);
    if ((int)$stmt->fetchColumn() >= 15) throw new AttendanceRejected('Too many attempts. Please wait a few minutes.', 429);
    $id = bin2hex(random_bytes(32));
    $direction = random_int(0, 1) ? 'left' : 'right';
    $stmt = $db->prepare('INSERT INTO aitf_attendance_challenges
        (challenge_hash, user_id, system_code, token_id, purpose, direction, created_at, expires_at)
        VALUES (?, ?, ?, ?, ?, ?, UTC_TIMESTAMP(), DATE_ADD(UTC_TIMESTAMP(), INTERVAL 120 SECOND))');
    $stmt->execute([hash('sha256', $id), $user['user_id'], $user['system_code'], $user['token_id'], $purpose, $direction]);
    jsonResponse(true, 'Camera challenge ready.', ['challenge_id' => $id, 'direction' => $direction,
        'expires_in_seconds' => 120, 'server_time' => gmdate('Y-m-d\TH:i:s\Z')]);
});

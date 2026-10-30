<?php
require_once __DIR__ . '/../helpers/auth_middleware.php';
require_once __DIR__ . '/../helpers/attendance_security.php';

attendanceEndpoint(function () use ($db) {
    $user = requireAuth($db);
    $uid = (int)$user['user_id'];
    $sys = $user['system_code'];
    $existing = $db->prepare('SELECT enrollment_photo_url FROM aitf_user_biometrics WHERE user_id = ? AND system_code = ?');
    $existing->execute([$uid, $sys]);
    $oldPhoto = $existing->fetchColumn();

    $challenge = consumeAttendanceChallenge($db, $user, 'enroll', $_POST['challenge_id'] ?? null);
    $captureTime = attendanceTimestamp($_POST['selfie_captured_at'] ?? null, 90);
    $images = [attendanceUpload('selfie_start'), attendanceUpload('selfie_turn'), attendanceUpload('selfie_image')];
    $verified = runAttendanceVerifier($images, $challenge['direction'], null);
    $photo = saveAttendancePhoto($images[2]);
    $committed = false;
    try {
        $db->beginTransaction();
        $evidenceId = saveAttendanceEvidence($db, $user, 'enroll', $challenge, null, $captureTime, $photo, $verified);
        $stmt = $db->prepare('INSERT INTO aitf_user_biometrics
            (user_id, system_code, enrollment_photo_url, face_embedding, embedding_dimension, enrollment_device_info, is_verified, enrolled_at)
            VALUES (?, ?, ?, ?, 128, ?, 1, NOW())
            ON DUPLICATE KEY UPDATE
            enrollment_photo_url = VALUES(enrollment_photo_url),
            face_embedding = VALUES(face_embedding),
            embedding_dimension = VALUES(embedding_dimension),
            enrollment_device_info = VALUES(enrollment_device_info),
            is_verified = 1,
            enrolled_at = NOW()');
        $stmt->execute([$uid, $sys, $photo, json_encode($verified['embedding']), 'opencv-sface-2021dec']);
        $db->commit();
        $committed = true;
        if ($oldPhoto && strpos($oldPhoto, 'private/') === 0) {
            try { @unlink(attendanceBaselinePath($oldPhoto)); } catch (Throwable $e) {}
        }
    } finally {
        if (!$committed) {
            if ($db->inTransaction()) $db->rollBack();
            @unlink(attendanceBaselinePath($photo));
        }
    }
    jsonResponse(true, 'Face enrolled successfully.', ['is_face_enrolled' => true,
        'dimension' => 128, 'evidence_id' => $evidenceId, 'enrolled_at' => date('Y-m-d H:i:s')]);
});

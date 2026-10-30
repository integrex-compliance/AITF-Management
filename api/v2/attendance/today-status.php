<?php
// C:\xampp\htdocs\public_html\api\v1\attendance\today-status.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';

date_default_timezone_set('Asia/Kolkata');

$user = requireAuth($db);
$userId = (int)$user['user_id'];
$systemCode = $user['system_code'] ?? 'AITF_MAIN';

$todayDate = date('Y-m-d');

$stmt = $db->prepare("
    SELECT id, in_time, out_time, total_working_hours, status, in_address, face_match_score, punch_in_selfie_url
    FROM aitf_attendance 
    WHERE user_id = :uid AND attendance_date = :dt AND system_code = :sys
    ORDER BY id DESC LIMIT 1
");
$stmt->execute([':uid' => $userId, ':dt' => $todayDate, ':sys' => $systemCode]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

$hasPunchedIn = false;
$hasPunchedOut = false;
$inTime = null;
$outTime = null;
$workingHours = 0.0;
$status = 'not_marked';

if ($record) {
    if (!empty($record['in_time'])) {
        $hasPunchedIn = true;
        $inTime = date('h:i A', strtotime($record['in_time']));
        $status = $record['status'];
    }
    if (!empty($record['out_time'])) {
        $hasPunchedOut = true;
        $outTime = date('h:i A', strtotime($record['out_time']));
        $workingHours = (float)$record['total_working_hours'];
    }
}

jsonResponse(true, "Today's status fetched", [
    'date' => $todayDate,
    'has_punched_in' => $hasPunchedIn,
    'has_punched_out' => $hasPunchedOut,
    'in_time' => $inTime,
    'out_time' => $outTime,
    'working_hours' => $workingHours,
    'status' => $status,
    'address' => $record['in_address'] ?? null,
    'punch_in_selfie_url' => $record['punch_in_selfie_url'] ?? null,
    'face_match_score' => isset($record['face_match_score']) ? (float)$record['face_match_score'] : null
], 200);

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
    SELECT id, in_time, out_time, total_working_hours, status, 
           in_address, in_latitude, in_longitude, punch_in_selfie_url,
           out_address, out_latitude, out_longitude, punch_out_selfie_url,
           face_match_score
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

$inAddr = null;
$outAddr = null;

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

    $inAddr = trim($record['in_address'] ?? '');
    if ($inAddr === '' && !empty($record['in_latitude']) && !empty($record['in_longitude'])) {
        $inAddr = $record['in_latitude'] . ', ' . $record['in_longitude'];
    }
    if ($inAddr === '') $inAddr = null;

    $outAddr = trim($record['out_address'] ?? '');
    if ($outAddr === '' && !empty($record['out_latitude']) && !empty($record['out_longitude'])) {
        $outAddr = $record['out_latitude'] . ', ' . $record['out_longitude'];
    }
    if ($outAddr === '') $outAddr = null;
}

jsonResponse(true, "Today's status fetched", [
    'date' => $todayDate,
    'has_punched_in' => $hasPunchedIn,
    'has_punched_out' => $hasPunchedOut,
    'in_time' => $inTime,
    'out_time' => $outTime,
    'working_hours' => $workingHours,
    'status' => $status,
    'address' => $inAddr, // backwards compatibility
    'in_address' => $inAddr,
    'clock_in_location' => $inAddr,
    'in_latitude' => isset($record['in_latitude']) && $record['in_latitude'] !== null ? (float)$record['in_latitude'] : null,
    'in_longitude' => isset($record['in_longitude']) && $record['in_longitude'] !== null ? (float)$record['in_longitude'] : null,
    'punch_in_selfie_url' => $record['punch_in_selfie_url'] ?? null,
    'out_address' => $outAddr,
    'clock_out_location' => $outAddr,
    'out_latitude' => isset($record['out_latitude']) && $record['out_latitude'] !== null ? (float)$record['out_latitude'] : null,
    'out_longitude' => isset($record['out_longitude']) && $record['out_longitude'] !== null ? (float)$record['out_longitude'] : null,
    'punch_out_selfie_url' => $record['punch_out_selfie_url'] ?? null,
    'face_match_score' => isset($record['face_match_score']) && $record['face_match_score'] !== null ? (float)$record['face_match_score'] : null
], 200);

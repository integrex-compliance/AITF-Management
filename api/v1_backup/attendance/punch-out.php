<?php
// C:\xampp\htdocs\public_html\api\v1\attendance\punch-out.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';

date_default_timezone_set('Asia/Kolkata');

$user = requireAuth($db);
$userId = (int)$user['user_id'];
$systemCode = $user['system_code'] ?? 'AITF_MAIN';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

$input = getJsonInput();
$todayDate = date('Y-m-d');
$currentTime = date('H:i:s');
$displayOutTime = date('h:i A');

// 1. Fetch today's punch-in record
$stmt = $db->prepare("
    SELECT id, in_time, out_time, total_working_hours, status 
    FROM aitf_attendance 
    WHERE user_id = :uid AND attendance_date = :dt AND (system_code = :sys OR system_code IS NULL OR system_code = 'AITF_MAIN')
    ORDER BY id DESC LIMIT 1
");
$stmt->execute([':uid' => $userId, ':dt' => $todayDate, ':sys' => $systemCode]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record || empty($record['in_time'])) {
    jsonResponse(false, "Cannot punch out. No punch-in record found for today.", [], 400);
}

// 2. Compute total working hours
$inTimestamp = strtotime("$todayDate " . $record['in_time']);
$outTimestamp = strtotime("$todayDate $currentTime");
$secondsDiff = max(0, $outTimestamp - $inTimestamp);
$hoursWorked = round($secondsDiff / 3600.0, 2);

$latitude  = isset($input['latitude']) ? (float)$input['latitude'] : null;
$longitude = isset($input['longitude']) ? (float)$input['longitude'] : null;
$address   = trim($input['address'] ?? '');

// 3. Update punch out
$update = $db->prepare("
    UPDATE aitf_attendance SET
        out_time = :out_time,
        total_working_hours = :wh,
        out_latitude = :lat,
        out_longitude = :lng,
        updated_at = NOW()
    WHERE id = :id
");
$update->execute([
    ':out_time' => $currentTime,
    ':wh' => $hoursWorked,
    ':lat' => $latitude,
    ':lng' => $longitude,
    ':id' => $record['id']
]);

jsonResponse(true, "Punch-out recorded successfully", [
    'date' => $todayDate,
    'in_time' => date('h:i A', strtotime($record['in_time'])),
    'out_time' => $displayOutTime,
    'working_hours' => number_format($hoursWorked, 2),
    'status' => $record['status']
], 200);

<?php
// C:\xampp\htdocs\public_html\api\v1\attendance\monthly-calendar.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';

date_default_timezone_set('Asia/Kolkata');

$user = requireAuth($db);
$currentUserId = (int)$user['user_id'];
$systemCode = $user['system_code'] ?? 'AITF_MAIN';

// Allow viewing other users if manager/admin
$targetUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $currentUserId;
if ($targetUserId !== $currentUserId && empty($user['can_manage_tasks'])) {
    $targetUserId = $currentUserId;
}

$year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
    jsonResponse(false, 'Invalid calendar month.', [], 422);
}

$startDate = sprintf('%04d-%02d-01', $year, $month);
$endDate   = date('Y-m-t', strtotime($startDate));

// Fetch attendance records for this month
$stmt = $db->prepare("
    SELECT attendance_date, status, in_time, out_time, total_working_hours, remarks, punch_in_selfie_url, punch_out_selfie_url, in_latitude, in_longitude, in_address, out_latitude, out_longitude, out_address
    FROM aitf_attendance
    WHERE user_id = :uid 
      AND system_code = :sys
      AND attendance_date BETWEEN :start AND :end
    ORDER BY attendance_date ASC
");
$stmt->execute([
    ':uid' => $targetUserId,
    ':sys' => $systemCode,
    ':start' => $startDate,
    ':end' => $endDate
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$records = [];
$summary = [
    'present_days' => 0,
    'late_days' => 0,
    'half_days' => 0,
    'leaves' => 0,
    'holidays' => 0
];

foreach ($rows as $r) {
    $dateKey = $r['attendance_date'];
    $st = strtolower($r['status'] ?? 'present');
    
    if ($st === 'present') {
        $summary['present_days']++;
    } elseif ($st === 'late') {
        $summary['late_days']++;
    } elseif ($st === 'halfday') {
        $summary['half_days']++;
    } elseif ($st === 'leave') {
        $summary['leaves']++;
    } elseif ($st === 'holiday') {
        $summary['holidays']++;
    }

    $records[$dateKey] = [
        'status' => $st,
        'label' => ucfirst($st),
        'in_time' => !empty($r['in_time']) ? date('h:i A', strtotime($r['in_time'])) : '--',
        'out_time' => !empty($r['out_time']) ? date('h:i A', strtotime($r['out_time'])) : '--',
        'working_hours' => (float)$r['total_working_hours'],
        'note' => $r['remarks'] ?? '',
        'punch_in_selfie_url' => $r['punch_in_selfie_url'] ?? '',
        'punch_out_selfie_url' => $r['punch_out_selfie_url'] ?? '',
        'in_address' => $r['in_address'] ?? null,
        'in_latitude' => $r['in_latitude'] !== null ? (float)$r['in_latitude'] : null,
        'in_longitude' => $r['in_longitude'] !== null ? (float)$r['in_longitude'] : null,
        'out_address' => $r['out_address'] ?? null,
        'out_latitude' => $r['out_latitude'] !== null ? (float)$r['out_latitude'] : null,
        'out_longitude' => $r['out_longitude'] !== null ? (float)$r['out_longitude'] : null,
    ];
}

jsonResponse(true, "Monthly calendar fetched", [
    'year' => $year,
    'month' => $month,
    'user_id' => $targetUserId,
    'records' => $records,
    'summary' => $summary
], 200);

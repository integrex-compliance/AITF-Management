<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';
include 'utility.php';

if (!Utitlity::checkForDirector()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($userId <= 0) {
    echo json_encode([]);
    exit;
}

function formatSeconds($seconds) {
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    $timeStr = "";
    if ($h > 0) $timeStr .= "{$h}h ";
    if ($m > 0 || $h > 0) $timeStr .= "{$m}m ";
    $timeStr .= "{$s}s";
    return trim($timeStr);
}

// Fetch all records
$stmt = $db->prepare("SELECT record_date, total_seconds FROM user_daily_screen_time WHERE user_id = :uid ORDER BY record_date DESC");
$stmt->execute([':uid' => $userId]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$todaySeconds = 0;
$monthSeconds = 0;
$totalSeconds = 0;

$todayStr = date('Y-m-d');
$monthStr = date('Y-m');

$tableData = [];
$chartMap = []; // for the last 30 days chart

foreach ($records as $row) {
    $dateStr = $row['record_date'];
    $sec = (int)$row['total_seconds'];
    
    // Stats
    $totalSeconds += $sec;
    if ($dateStr === $todayStr) {
        $todaySeconds += $sec;
    }
    if (strpos($dateStr, $monthStr) === 0) {
        $monthSeconds += $sec;
    }
    
    // Table data
    $tableData[] = [
        'date' => date('d M Y', strtotime($dateStr)),
        'time' => formatSeconds($sec)
    ];
    
    $chartMap[$dateStr] = $sec;
}

// Initialize last 30 days with 0 for chart
$chartData = [];
for ($i = 29; $i >= 0; $i--) {
    $dStr = date('Y-m-d', strtotime("-$i days"));
    $sec = isset($chartMap[$dStr]) ? $chartMap[$dStr] : 0;
    $chartData[] = [
        'date' => date('d M', strtotime($dStr)),
        'minutes' => round($sec / 60, 2),
        'label' => formatSeconds($sec)
    ];
}

$response = [
    'stats' => [
        'today' => formatSeconds($todaySeconds),
        'month' => formatSeconds($monthSeconds),
        'total' => formatSeconds($totalSeconds)
    ],
    'chart' => $chartData,
    'table' => $tableData
];

echo json_encode($response);

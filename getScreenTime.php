<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
date_default_timezone_set('Asia/Kolkata');

include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';
include 'utility.php';

header('Content-Type: application/json');

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
    $seconds = max(0, (int)$seconds);
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    $timeStr = "";
    if ($h > 0) $timeStr .= "{$h}h ";
    if ($m > 0 || $h > 0) $timeStr .= "{$m}m ";
    $timeStr .= "{$s}s";
    return trim($timeStr);
}

// Ensure table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS user_daily_screen_time (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        record_date DATE,
        total_seconds INT DEFAULT 0,
        UNIQUE KEY user_date (user_id, record_date)
    )");
} catch (Exception $e) {
    // table may already exist
}

// Fetch screen time records from user_daily_screen_time
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

// If no screen_time data exists, compute from login_activity sessions as fallback
if (empty($records)) {
    $fallbackStmt = $db->prepare("SELECT DATE(login_at) as session_date, 
        SUM(TIMESTAMPDIFF(SECOND, login_at, COALESCE(logout_at, last_seen_at))) as total_sec
        FROM user_login_activity 
        WHERE user_id = :uid 
        GROUP BY DATE(login_at) 
        ORDER BY session_date DESC 
        LIMIT 90");
    $fallbackStmt->execute([':uid' => $userId]);
    $fallbackRecords = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($fallbackRecords as $row) {
        $dateStr = $row['session_date'];
        $sec = max(0, (int)$row['total_sec']);
        
        $totalSeconds += $sec;
        if ($dateStr === $todayStr) {
            $todaySeconds += $sec;
        }
        if (strpos($dateStr, $monthStr) === 0) {
            $monthSeconds += $sec;
        }
        
        $tableData[] = [
            'date' => date('d M Y', strtotime($dateStr)),
            'time' => formatSeconds($sec)
        ];
        
        $chartMap[$dateStr] = $sec;
    }
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

// Fetch today's login sessions for this user (detailed activity log)
$sessionsStmt = $db->prepare("SELECT login_at, logout_at, last_seen_at 
    FROM user_login_activity 
    WHERE user_id = :uid AND DATE(login_at) = CURDATE()
    ORDER BY login_at DESC");
$sessionsStmt->execute([':uid' => $userId]);
$todaySessions = $sessionsStmt->fetchAll(PDO::FETCH_ASSOC);

$sessionsList = [];
foreach ($todaySessions as $sess) {
    $loginTime = date('h:i A', strtotime($sess['login_at']));
    $logoutTime = !empty($sess['logout_at']) ? date('h:i A', strtotime($sess['logout_at'])) : 'Active Now';
    $endTime = !empty($sess['logout_at']) ? $sess['logout_at'] : $sess['last_seen_at'];
    $duration = '';
    if ($endTime) {
        $diffSec = max(0, strtotime($endTime) - strtotime($sess['login_at']));
        $duration = formatSeconds($diffSec);
    }
    $sessionsList[] = [
        'login' => $loginTime,
        'logout' => $logoutTime,
        'duration' => $duration,
        'is_active' => empty($sess['logout_at'])
    ];
}

$response = [
    'stats' => [
        'today' => formatSeconds($todaySeconds),
        'month' => formatSeconds($monthSeconds),
        'total' => formatSeconds($totalSeconds)
    ],
    'chart' => $chartData,
    'table' => $tableData,
    'sessions' => $sessionsList
];

echo json_encode($response);

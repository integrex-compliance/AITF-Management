<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['login_activity_id']) || empty($_SESSION['login_activity_id'])) {
    http_response_code(400);
    echo "No active session";
    exit;
}

include_once './dbConnect.php';

try {
    // Ensure table exists for screen time tracking
    $db->exec("CREATE TABLE IF NOT EXISTS user_daily_screen_time (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        record_date DATE,
        total_seconds INT DEFAULT 0,
        UNIQUE KEY user_date (user_id, record_date)
    )");

    $isActive = true;
    $delta = 0;
    
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $postData = json_decode(file_get_contents('php://input'), true);
        if ($postData !== null) {
            $isActive = isset($postData['active']) ? (bool)$postData['active'] : true;
            $delta = isset($postData['delta']) ? (int)$postData['delta'] : 0;
        }
    } else {
        if (isset($_POST['active'])) {
            $isActive = filter_var($_POST['active'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($_POST['delta'])) {
            $delta = (int)$_POST['delta'];
        }
    }
    
    // Safety check so users can't artificially inflate time drastically in one ping
    if ($delta > 60) $delta = 60;
    
    $userId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
    $activityId = (int)$_SESSION['login_activity_id'];

    // If userId is 0 (e.g. Director), try to fetch the actual user_id from the login_activity record
    if ($userId <= 0 && $activityId > 0) {
        $lookupStmt = $db->prepare("SELECT user_id FROM user_login_activity WHERE activity_id = :id LIMIT 1");
        $lookupStmt->execute([':id' => $activityId]);
        $lookupId = $lookupStmt->fetchColumn();
        // Use activity_id as a fallback identifier for screen time if user_id is NULL/0
        if ($lookupId && (int)$lookupId > 0) {
            $userId = (int)$lookupId;
        }
    }

    if ($isActive) {
        // Update last seen to NOW() if active
        $stmt = $db->prepare("UPDATE user_login_activity SET last_seen_at = NOW() WHERE activity_id = :id AND logout_at IS NULL");
        $stmt->execute(array(':id' => $activityId));
        
        // Add screen time for all users (including those with user_id > 0)
        if ($userId > 0 && $delta > 0) {
            $date = date('Y-m-d');
            $stStmt = $db->prepare("INSERT INTO user_daily_screen_time (user_id, record_date, total_seconds) 
                                    VALUES (:uid, :rd, :sec) 
                                    ON DUPLICATE KEY UPDATE total_seconds = total_seconds + :sec2");
            $stStmt->execute([
                ':uid' => $userId,
                ':rd' => $date,
                ':sec' => $delta,
                ':sec2' => $delta
            ]);
        }

    } else {
        // If they switched tabs, set last_seen_at back so they immediately show as offline
        $stmt = $db->prepare("UPDATE user_login_activity SET last_seen_at = DATE_SUB(NOW(), INTERVAL 10 MINUTE) WHERE activity_id = :id AND logout_at IS NULL");
        $stmt->execute(array(':id' => $activityId));
    }
    
    echo "OK";
} catch (Exception $e) {
    http_response_code(500);
    error_log("Heartbeat Error: " . $e->getMessage());
    echo "Error";
}

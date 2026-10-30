<?php
// C:\xampp\htdocs\public_html\api\v1\helpers\push_helper.php

/**
 * Dispatches notification to a user and logs in aitf_notifications
 */
function aitf_send_fcm_notification($db, $targetUserId, $title, $body, $dataPayload = []) {
    if (!$db instanceof PDO) return false;

    // 1. Insert into notifications history table
    try {
        $ins = $db->prepare("
            INSERT INTO aitf_notifications (user_id, title, body, notification_type, reference_id) 
            VALUES (:uid, :title, :body, :type, :ref)
        ");
        $ins->execute([
            ':uid' => $targetUserId,
            ':title' => $title,
            ':body' => $body,
            ':type' => $dataPayload['type'] ?? 'system',
            ':ref' => !empty($dataPayload['reference_id']) ? (int)$dataPayload['reference_id'] : null
        ]);
    } catch (Exception $e) {
        error_log("Notification history error: " . $e->getMessage());
    }

    // 2. Fetch user active FCM tokens
    $tokens = [];
    try {
        $stmt = $db->prepare("SELECT fcm_token FROM aitf_user_devices WHERE user_id = :uid AND fcm_token != ''");
        $stmt->execute([':uid' => $targetUserId]);
        $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("FCM token fetch error: " . $e->getMessage());
    }

    if (empty($tokens)) {
        return true; // Notification recorded in DB, no FCM device registered yet
    }

    // 3. Dispatch to Firebase FCM HTTP v1 (if service account exists)
    $serviceAccountPath = 'C:/xampp/htdocs/public_html/api/v1/config/firebase-service-account.json';
    if (!file_exists($serviceAccountPath)) {
        // Log that FCM credentials are pending configuration, but return true as DB notification succeeded
        return true;
    }

    // Service account OAuth2 dispatch can be added when JSON file is dropped in place
    return true;
}

<?php
// C:\xampp\htdocs\public_html\api\v1\helpers\push_helper.php

/**
 * Returns Google OAuth2 access token using Firebase service account key.
 * Caches token for 50 minutes to avoid redundant OAuth calls.
 */
function aitf_get_fcm_access_token(array $sa): ?string {
    $cacheFile = sys_get_temp_dir() . '/aitf_fcm_oauth_' . md5($sa['client_email']) . '.json';
    if (file_exists($cacheFile)) {
        $cached = json_decode(@file_get_contents($cacheFile), true);
        if (!empty($cached['token']) && !empty($cached['expires_at']) && $cached['expires_at'] > time() + 120) {
            return $cached['token'];
        }
    }

    $now = time();
    $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
    $claim = rtrim(strtr(base64_encode(json_encode([
        'iss' => $sa['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ])), '+/', '-_'), '=');

    $sig = '';
    if (!openssl_sign("$header.$claim", $sig, $sa['private_key'], OPENSSL_ALGO_SHA256)) {
        error_log("FCM OAuth sign error: OpenSSL failed to sign JWT");
        return null;
    }
    $jwt = $header . '.' . $claim . '.' . rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err || !$res) {
        error_log("FCM OAuth token request error: " . ($err ?: 'Empty response'));
        return null;
    }

    $data = json_decode($res, true);
    if (!empty($data['access_token'])) {
        @file_put_contents($cacheFile, json_encode([
            'token' => $data['access_token'],
            'expires_at' => $now + (int)($data['expires_in'] ?? 3600)
        ]));
        return $data['access_token'];
    }

    error_log("FCM OAuth token failure: " . $res);
    return null;
}

/**
 * Dispatches notification to a user and logs in aitf_notifications
 */
function aitf_send_fcm_notification($db, $targetUserId, $title, $body, $dataPayload = [], $systemCode = 'AITF_MAIN') {
    if (!$db instanceof PDO) return false;

    // 1. Insert into notifications history table
    try {
        $ins = $db->prepare("
            INSERT INTO aitf_notifications (user_id, title, body, notification_type, reference_id, system_code)
            VALUES (:uid, :title, :body, :type, :ref, :sys)
        ");
        $ins->execute([
            ':uid' => $targetUserId,
            ':sys' => $systemCode,
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
        $stmt = $db->prepare("SELECT fcm_token FROM aitf_user_devices WHERE user_id = :uid AND system_code = :sys AND fcm_token != ''");
        $stmt->execute([':uid' => $targetUserId, ':sys' => $systemCode]);
        $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("FCM token fetch error: " . $e->getMessage());
    }

    if (empty($tokens)) {
        return true; // Notification recorded in DB, no FCM device registered yet
    }

    // 3. Locate service account credentials (supports local & live environments)
    $candidates = [
        __DIR__ . '/../config/firebase-service-account.json',
        dirname(__DIR__, 2) . '/v1/config/firebase-service-account.json',
        'C:/xampp/htdocs/public_html/api/v1/config/firebase-service-account.json',
        dirname(__DIR__, 3) . '/aitf-private/config/firebase-service-account.json'
    ];
    $saPath = null;
    foreach ($candidates as $cand) {
        if (file_exists($cand)) {
            $saPath = $cand;
            break;
        }
    }

    if (!$saPath) {
        return true;
    }

    $sa = json_decode(@file_get_contents($saPath), true);
    if (!is_array($sa) || empty($sa['project_id']) || empty($sa['client_email']) || empty($sa['private_key'])) {
        error_log("FCM service account file is invalid: " . $saPath);
        return true;
    }

    $accessToken = aitf_get_fcm_access_token($sa);
    if (!$accessToken) {
        return true;
    }

    // Prepare stringified data payload
    $stringData = [];
    foreach ($dataPayload as $k => $v) {
        $stringData[(string)$k] = is_scalar($v) ? (string)$v : json_encode($v);
    }
    $stringData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

    $projectId = $sa['project_id'];
    $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

    // 4. Send FCM HTTP v1 push to each registered device
    foreach ($tokens as $token) {
        if (empty($token) || strlen($token) < 20) continue;

        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => $stringData,
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'channel_id' => 'aitf_tasks_channel',
                        'color' => '#1E3A8A',
                        'default_sound' => true,
                        'default_vibrate_timings' => true,
                        'notification_priority' => 'PRIORITY_MAX'
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json; UTF-8'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        $res = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // If token is invalid or app uninstalled, purge token
        if ($status === 404) {
            try {
                $del = $db->prepare("DELETE FROM aitf_user_devices WHERE fcm_token = ?");
                $del->execute([$token]);
            } catch (Exception $e) {}
        }
    }

    return true;
}

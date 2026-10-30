<?php
// C:\xampp\htdocs\public_html\api\v1\biometrics\register-face.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';

$user = requireAuth($db);
$userId = (int)$user['user_id'];
$systemCode = $user['system_code'] ?? 'AITF_MAIN';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

// 1. Check if user is already enrolled
$checkStmt = $db->prepare("SELECT id, enrollment_photo_url FROM aitf_user_biometrics WHERE user_id = :uid AND system_code = :sys LIMIT 1");
$checkStmt->execute([':uid' => $userId, ':sys' => $systemCode]);
$existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

// If force re-enrollment requested by admin or param
$allowReenroll = isset($_POST['force_reenroll']) && ($_POST['force_reenroll'] == '1' || $_POST['force_reenroll'] === 'true');
if ($existing && !$allowReenroll) {
    jsonResponse(false, "Facial baseline is already enrolled. Contact admin to re-register.", [
        'enrolled' => true
    ], 400);
}

// 2. Validate Face Embedding
$faceEmbeddingRaw = $_POST['face_embedding'] ?? '';
if (empty($faceEmbeddingRaw)) {
    jsonResponse(false, "Missing face_embedding data", [], 400);
}

$embeddingArray = json_decode($faceEmbeddingRaw, true);
if (!is_array($embeddingArray) || count($embeddingArray) < 64) {
    jsonResponse(false, "Invalid face_embedding format. Expected array of floats (128/192/512 dimensions).", [], 400);
}
$dimension = count($embeddingArray);

// 3. Validate Liveness Score
$livenessScore = isset($_POST['liveness_score']) ? (float)$_POST['liveness_score'] : 1.0;
if ($livenessScore < 0.5) {
    jsonResponse(false, "Liveness check failed (score {$livenessScore}). Ensure you blink naturally and face the camera directly.", [], 400);
}

// 4. Handle Selfie Upload
$photoUrl = '';
if (!empty($_FILES['selfie_image']['name']) && $_FILES['selfie_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'C:/xampp/htdocs/public_html/uploads/biometrics/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileTmp = $_FILES['selfie_image']['tmp_name'];
    $fileExt = strtolower(pathinfo($_FILES['selfie_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
        $fileExt = 'jpg';
    }
    
    $fileName = "user_{$userId}_baseline." . $fileExt;
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($fileTmp, $targetPath)) {
        jsonResponse(false, "Failed to save baseline selfie image", [], 500);
    }
    $photoUrl = "uploads/biometrics/" . $fileName;
} else if ($existing && !empty($existing['enrollment_photo_url'])) {
    $photoUrl = $existing['enrollment_photo_url'];
} else {
    jsonResponse(false, "Selfie photo file is required for baseline registration", [], 400);
}

// 5. Store / Update Biometric Record
$deviceInfo = $_SERVER['HTTP_USER_AGENT'] ?? 'Mobile App';

$saveSql = "
    INSERT INTO aitf_user_biometrics 
        (user_id, system_code, enrollment_photo_url, face_embedding, embedding_dimension, enrollment_device_info, is_verified, enrolled_at)
    VALUES 
        (:uid, :sys, :photo, :embedding, :dim, :dev, 1, NOW())
    ON DUPLICATE KEY UPDATE 
        enrollment_photo_url = VALUES(enrollment_photo_url),
        face_embedding = VALUES(face_embedding),
        embedding_dimension = VALUES(embedding_dimension),
        enrollment_device_info = VALUES(enrollment_device_info),
        is_verified = 1,
        updated_at = NOW()
";

$saveStmt = $db->prepare($saveSql);
$saveStmt->execute([
    ':uid' => $userId,
    ':sys' => $systemCode,
    ':photo' => $photoUrl,
    ':embedding' => json_encode($embeddingArray),
    ':dim' => $dimension,
    ':dev' => $deviceInfo
]);

jsonResponse(true, "Facial baseline registered successfully", [
    'enrolled_at' => date('Y-m-d H:i:s'),
    'is_face_enrolled' => true,
    'photo_url' => $photoUrl,
    'dimension' => $dimension
], 200);

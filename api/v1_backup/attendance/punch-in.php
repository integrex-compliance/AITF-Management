<?php
// C:\xampp\htdocs\public_html\api\v1\attendance\punch-in.php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/auth_middleware.php';
require_once __DIR__ . '/../helpers/face_matcher.php';

date_default_timezone_set('Asia/Kolkata');

$user = requireAuth($db);
$userId = (int)$user['user_id'];
$systemCode = $user['system_code'] ?? 'AITF_MAIN';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", [], 405);
}

$todayDate = date('Y-m-d');
$currentTime = date('H:i:s');
$displayInTime = date('h:i A');

// 1. Check if user has already punched in today
$checkAtt = $db->prepare("
    SELECT id, in_time, out_time, status 
    FROM aitf_attendance 
    WHERE user_id = :uid AND attendance_date = :dt AND system_code = :sys 
    LIMIT 1
");
$checkAtt->execute([':uid' => $userId, ':dt' => $todayDate, ':sys' => $systemCode]);
$existingRecord = $checkAtt->fetch(PDO::FETCH_ASSOC);

if ($existingRecord && !empty($existingRecord['in_time'])) {
    jsonResponse(false, "You have already clocked in today at " . date('h:i A', strtotime($existingRecord['in_time'])), [
        'already_punched_in' => true,
        'in_time' => date('h:i A', strtotime($existingRecord['in_time'])),
        'status' => $existingRecord['status']
    ], 400);
}

// 2. Fetch baseline face vector from aitf_user_biometrics
$bioStmt = $db->prepare("
    SELECT face_embedding, embedding_dimension, is_verified 
    FROM aitf_user_biometrics 
    WHERE user_id = :uid AND system_code = :sys 
    LIMIT 1
");
$bioStmt->execute([':uid' => $userId, ':sys' => $systemCode]);
$bioRecord = $bioStmt->fetch(PDO::FETCH_ASSOC);

if (!$bioRecord || empty($bioRecord['face_embedding'])) {
    jsonResponse(false, "Facial baseline not found. Please complete one-time face enrollment first.", [
        'needs_enrollment' => true
    ], 400);
}

$baselineVector = json_decode($bioRecord['face_embedding'], true);

// 3. Process Live Face Embedding from Request
$rawLiveEmbedding = $_POST['face_embedding'] ?? '';
if (empty($rawLiveEmbedding)) {
    // Check if JSON body sent
    $input = getJsonInput();
    $rawLiveEmbedding = $input['face_embedding'] ?? '';
}

if (empty($rawLiveEmbedding)) {
    jsonResponse(false, "Live face embedding data is missing", [], 400);
}

$liveVector = is_array($rawLiveEmbedding) ? $rawLiveEmbedding : json_decode($rawLiveEmbedding, true);
if (!is_array($liveVector) || count($liveVector) === 0) {
    jsonResponse(false, "Invalid live face embedding format", [], 400);
}

// 4. Biometric Face Verification (Cosine Similarity >= 0.75)
$matchResult = aitf_verify_face_match($baselineVector, $liveVector, 0.75);

if (!$matchResult['is_match']) {
    jsonResponse(false, "Face verification failed. Please align your face clearly under good lighting. (Score: {$matchResult['similarity_score']})", [
        'verified' => false,
        'similarity_score' => $matchResult['similarity_score'],
        'required_threshold' => 0.75
    ], 400);
}

// 5. Save Selfie Image
$selfieUrl = null;
if (!empty($_FILES['selfie_image']['name']) && $_FILES['selfie_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'C:/xampp/htdocs/public_html/uploads/selfies/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExt = strtolower(pathinfo($_FILES['selfie_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
        $fileExt = 'jpg';
    }
    
    $timestamp = time();
    $fileName = "punch_in_{$userId}_{$todayDate}_{$timestamp}." . $fileExt;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['selfie_image']['tmp_name'], $targetPath)) {
        $selfieUrl = "uploads/selfies/" . $fileName;
    }
}

// 6. Calculate Attendance Status based on punch time
// 09:45 AM threshold: present
// 09:46 - 11:00 AM: late
// After 11:00 AM: halfday
$timeSeconds = strtotime("1970-01-01 $currentTime");
$t0945 = strtotime("1970-01-01 09:45:00");
$t1100 = strtotime("1970-01-01 11:00:00");

if ($timeSeconds <= $t0945) {
    $status = 'present';
    $statusLabel = 'Present';
} elseif ($timeSeconds <= $t1100) {
    $status = 'late';
    $statusLabel = 'Late Login';
} else {
    $status = 'halfday';
    $statusLabel = 'Half Day';
}

// 7. Geolocation parameters
$latitude  = isset($_POST['latitude']) ? (float)$_POST['latitude'] : null;
$longitude = isset($_POST['longitude']) ? (float)$_POST['longitude'] : null;
$address   = trim($_POST['address'] ?? '');
$deviceId  = trim($_POST['device_id'] ?? '');

// 8. Insert or Update Record in aitf_attendance
$attSql = "
    INSERT INTO aitf_attendance (
        user_id, system_code, attendance_date, in_time, status,
        punch_in_selfie_url, face_match_score, in_latitude, in_longitude,
        in_address, device_id, remarks
    ) VALUES (
        :uid, :sys, :dt, :in_time, :status,
        :selfie, :score, :lat, :lng,
        :address, :dev, :remarks
    ) ON DUPLICATE KEY UPDATE
        in_time = VALUES(in_time),
        status = VALUES(status),
        punch_in_selfie_url = VALUES(punch_in_selfie_url),
        face_match_score = VALUES(face_match_score),
        in_latitude = VALUES(in_latitude),
        in_longitude = VALUES(in_longitude),
        in_address = VALUES(in_address),
        remarks = VALUES(remarks),
        updated_at = NOW()
";

$attStmt = $db->prepare($attSql);
$attStmt->execute([
    ':uid' => $userId,
    ':sys' => $systemCode,
    ':dt' => $todayDate,
    ':in_time' => $currentTime,
    ':status' => $status,
    ':selfie' => $selfieUrl,
    ':score' => $matchResult['similarity_score'],
    ':lat' => $latitude,
    ':lng' => $longitude,
    ':address' => $address ?: 'Office / Field Location',
    ':dev' => $deviceId ?: 'Mobile APK',
    ':remarks' => "Punch In ({$statusLabel})"
]);

jsonResponse(true, "Attendance marked successfully! Status: {$statusLabel}", [
    'date' => $todayDate,
    'in_time' => $displayInTime,
    'status' => $status,
    'status_label' => $statusLabel,
    'similarity_score' => $matchResult['similarity_score'],
    'location' => $address ?: 'Captured',
    'selfie_url' => $selfieUrl
], 200);

<?php
// Security decisions are made from server state and uploaded images, never
// client-generated embeddings or asserted face/liveness scores.
class AttendanceRejected extends RuntimeException {
    public $status;
    public function __construct(string $message, int $status = 422) {
        parent::__construct($message);
        $this->status = $status;
    }
}

function attendanceConfig(): array {
    static $config;
    if ($config !== null) return $config;
    $publicRoot = dirname(__DIR__, 3);
    $path = getenv('AITF_SECURITY_CONFIG');
    if (!$path || !is_file($path)) {
        $candidates = [
            $publicRoot . '/aitf-private/security.php',             // Inside repo (public_html/aitf-private/security.php)
            dirname($publicRoot) . '/aitf-private/security.php',    // Linux cPanel outside webroot (/home/aitfgroup/aitf-private/security.php)
            dirname($publicRoot, 2) . '/aitf-private/security.php', // Windows XAMPP (C:/xampp/aitf-private/security.php)
        ];
        foreach ($candidates as $cand) {
            if (is_file($cand)) {
                $path = $cand;
                break;
            }
        }
    }
    if (!$path || !is_file($path)) throw new AttendanceRejected('Attendance verification is not configured. Contact your administrator.', 503);
    $config = require $path;
    if (!is_array($config) || empty($config['python']) || empty($config['verifier']) || empty($config['storage'])) {
        throw new AttendanceRejected('Attendance verification configuration is invalid.', 503);
    }
    return $config;
}

function attendanceTimestamp($value, int $maxAge, ?int $now = null): DateTimeImmutable {
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,6})?Z$/D', $value)) {
        throw new AttendanceRejected('A valid UTC capture timestamp is required.');
    }
    try { $time = new DateTimeImmutable($value); }
    catch (Exception $e) { throw new AttendanceRejected('Capture timestamp is invalid.'); }
    $warnings = DateTimeImmutable::getLastErrors();
    if ($warnings && ($warnings['warning_count'] || $warnings['error_count'])) {
        throw new AttendanceRejected('Capture timestamp is invalid.');
    }
    $age = ($now ?? time()) - $time->getTimestamp();
    if ($age < -5 || $age > $maxAge) {
        throw new AttendanceRejected('Capture is stale or device time is incorrect. Capture again.');
    }
    return $time;
}

function attendanceLocation(array $input, ?int $now = null): array {
    $values = [];
    foreach (['latitude', 'longitude', 'location_accuracy_m'] as $key) {
        if (!isset($input[$key]) || !is_scalar($input[$key]) || is_bool($input[$key]) || !is_numeric($input[$key])) {
            throw new AttendanceRejected('Accurate GPS coordinates are required.');
        }
        $values[$key] = (float)$input[$key];
        if (!is_finite($values[$key])) throw new AttendanceRejected('GPS values are invalid.');
    }
    if (abs($values['latitude']) > 90 || abs($values['longitude']) > 180 ||
        $values['location_accuracy_m'] <= 0 || $values['location_accuracy_m'] > 25) {
        throw new AttendanceRejected('GPS accuracy must be within 25 metres. Move near an open area and retry.');
    }
    if (!array_key_exists('location_is_mocked', $input) ||
        !in_array($input['location_is_mocked'], [false, 0, '0', 'false'], true)) {
        throw new AttendanceRejected('Mock or unverified GPS locations are not accepted.');
    }
    $values['captured_at'] = attendanceTimestamp($input['location_captured_at'] ?? null, 45, $now);
    if (isset($input['address']) && !is_string($input['address'])) throw new AttendanceRejected('Address is invalid.');
    $values['address'] = mb_substr(trim($input['address'] ?? ''), 0, 255);
    if ($values['address'] === '') $values['address'] = $values['latitude'] . ', ' . $values['longitude'];
    return $values;
}

function consumeAttendanceChallenge(PDO $db, array $user, string $purpose, $id): array {
    if (!is_string($id) || !preg_match('/^[a-f0-9]{64}$/D', $id)) {
        throw new AttendanceRejected('Start a new camera verification challenge.', 409);
    }
    $hash = hash('sha256', $id);
    $params = [':hash' => $hash, ':uid' => $user['user_id'], ':sys' => $user['system_code'],
               ':tid' => $user['token_id'], ':purpose' => $purpose];
    // Atomic claim: simultaneous requests can never consume the same challenge.
    $claim = $db->prepare('UPDATE aitf_attendance_challenges SET consumed_at = UTC_TIMESTAMP()
        WHERE challenge_hash = :hash AND user_id = :uid AND system_code = :sys
        AND token_id = :tid AND purpose = :purpose AND consumed_at IS NULL AND expires_at > UTC_TIMESTAMP()');
    $claim->execute($params);
    if ($claim->rowCount() !== 1) throw new AttendanceRejected('Camera challenge expired or was already used. Start again.', 409);
    $stmt = $db->prepare('SELECT * FROM aitf_attendance_challenges WHERE challenge_hash = ?');
    $stmt->execute([$hash]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function attendanceUpload(string $key): string {
    $file = $_FILES[$key] ?? null;
    if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size']) ||
        $file['error'] !== UPLOAD_ERR_OK || !is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new AttendanceRejected('All three fresh camera photos are required.');
    }
    if ($file['size'] < 1024 || $file['size'] > 5 * 1024 * 1024) throw new AttendanceRejected('Selfie file size is invalid.');
    $info = @getimagesize($file['tmp_name']);
    if (!$info || $info[2] !== IMAGETYPE_JPEG || min($info[0], $info[1]) < 240 || $info[0] * $info[1] > 12000000) {
        throw new AttendanceRejected('A valid camera JPEG image is required.');
    }
    return $file['tmp_name'];
}

function runAttendanceVerifier(array $images, string $direction, ?string $baseline): array {
    $config = attendanceConfig();
    $env = getenv();
    if (!empty($config['python_lib'])) $env['AITF_FACE_PYTHON_LIB'] = $config['python_lib'];
    if (!empty($config['models'])) $env['AITF_FACE_MODELS'] = $config['models'];
    // Array command avoids shell expansion of paths on PHP 7.4+.
    $process = @proc_open([$config['python'], $config['verifier']],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $env,
        ['bypass_shell' => true, 'create_no_window' => true]);
    if (!is_resource($process)) throw new AttendanceRejected('Face verification is unavailable.', 503);
    fwrite($pipes[0], json_encode(['images' => $images, 'direction' => $direction, 'baseline' => $baseline]));
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $output = '';
    $deadline = microtime(true) + 20;
    do {
        $output .= stream_get_contents($pipes[1]);
        // Drain stderr without returning private paths or image data.
        stream_get_contents($pipes[2]);
        $status = proc_get_status($process);
        if (!$status['running']) break;
        usleep(20000);
    } while (microtime(true) < $deadline && strlen($output) < 65536);
    if ($status['running']) proc_terminate($process);
    $output .= stream_get_contents($pipes[1]);
    fclose($pipes[1]); fclose($pipes[2]); proc_close($process);
    $result = json_decode($output, true);
    if (!is_array($result) || !isset($result['success'])) throw new AttendanceRejected('Face verification is unavailable.', 503);
    if ($result['success'] !== true) {
        throw new AttendanceRejected($result['message'] ?? 'Face check failed.', !empty($result['unavailable']) ? 503 : 422);
    }
    if (count($result['embedding'] ?? []) !== 128 || count($result['image_hashes'] ?? []) !== 3) {
        throw new AttendanceRejected('Face verification returned an invalid result.', 503);
    }
    return $result;
}

function attendanceBaselinePath(string $stored): string {
    $config = attendanceConfig();
    if (preg_match('#^private/([a-f0-9]{48}\.jpg)$#D', $stored, $match)) {
        $candidate = $config['storage'] . '/' . $match[1];
    } elseif (preg_match('#^uploads/biometrics/[a-zA-Z0-9_.-]+\.(?:jpg|jpeg|png|webp)$#D', $stored)) {
        $candidate = dirname(__DIR__, 3) . '/' . $stored;
    } else {
        throw new AttendanceRejected('Enrolled photo is unavailable. Contact your administrator.', 409);
    }
    if (!is_file($candidate)) throw new AttendanceRejected('Enrolled photo is unavailable. Contact your administrator.', 409);
    return $candidate;
}

function saveAttendancePhoto(string $tempPath): string {
    $config = attendanceConfig();
    $directory = $config['storage'];
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) throw new AttendanceRejected('Cannot store attendance photo.', 503);
    $name = bin2hex(random_bytes(24)) . '.jpg';
    // Decode and re-encode: strips EXIF, appended payloads and untrusted filenames.
    $image = @imagecreatefromjpeg($tempPath);
    if (!$image) throw new AttendanceRejected('Selfie could not be decoded.');
    // OpenCV reads EXIF orientation during verification. Apply the same
    // orientation before stripping metadata, or the saved baseline can rotate.
    if (!function_exists('exif_read_data')) {
        imagedestroy($image);
        throw new AttendanceRejected('Photo orientation support is unavailable.', 503);
    }
    $exif = @exif_read_data($tempPath);
    $orientation = (int)($exif['Orientation'] ?? 1);
    if (in_array($orientation, [2, 5, 7], true)) imageflip($image, IMG_FLIP_HORIZONTAL);
    if ($orientation === 4) imageflip($image, IMG_FLIP_VERTICAL);
    $angle = [3 => 180, 5 => 90, 6 => -90, 7 => -90, 8 => 90][$orientation] ?? 0;
    if ($angle !== 0) {
        $rotated = imagerotate($image, $angle, 0);
        imagedestroy($image);
        if (!$rotated) throw new AttendanceRejected('Photo orientation could not be corrected.');
        $image = $rotated;
    }
    $saved = imagejpeg($image, $directory . '/' . $name, 92);
    imagedestroy($image);
    if (!$saved) throw new AttendanceRejected('Cannot store attendance photo.', 503);
    @chmod($directory . '/' . $name, 0600);
    return 'private/' . $name;
}

function saveAttendanceEvidence(PDO $db, array $user, string $purpose, array $challenge,
    ?array $location, DateTimeImmutable $captureTime, string $photo, array $verified): int {
    $stmt = $db->prepare('INSERT INTO aitf_attendance_evidence
        (user_id, system_code, purpose, challenge_hash, latitude, longitude, accuracy_m,
         location_captured_at, selfie_captured_at, photo_path, face_score, verification_model, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())');
    $stmt->execute([$user['user_id'], $user['system_code'], $purpose, $challenge['challenge_hash'],
        $location['latitude'] ?? null, $location['longitude'] ?? null, $location['location_accuracy_m'] ?? null,
        $location ? $location['captured_at']->format('Y-m-d H:i:s') : null,
        $captureTime->format('Y-m-d H:i:s'), $photo, $verified['similarity_score'], $verified['model']]);
    $id = (int)$db->lastInsertId();
    $hashStmt = $db->prepare('INSERT INTO aitf_selfie_hashes (image_hash, evidence_id, created_at) VALUES (?, ?, UTC_TIMESTAMP())');
    foreach ($verified['image_hashes'] as $hash) $hashStmt->execute([$hash, $id]);
    return $id;
}

function attendanceEndpoint(callable $action): void {
    global $db;
    try {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') throw new AttendanceRejected('Method not allowed.', 405);
        $action();
    } catch (Throwable $e) {
        if ($db instanceof PDO && $db->inTransaction()) $db->rollBack();
        if ($e instanceof AttendanceRejected) jsonResponse(false, $e->getMessage(), [], $e->status);
        if ($e instanceof PDOException && $e->getCode() === '23000') {
            jsonResponse(false, 'This attendance or photo was already submitted. Refresh today\'s status.', [], 409);
        }
        error_log('Attendance error: ' . get_class($e) . ' code=' . $e->getCode());
        jsonResponse(false, 'Attendance could not be completed. Refresh today\'s status before retrying.', [], 503);
    }
}

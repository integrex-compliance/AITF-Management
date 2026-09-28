<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/utility.php';
require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/staff_guidelines_lib.php';

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: login.php?reason=login_required');
    exit;
}

$attachmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($attachmentId <= 0) {
    http_response_code(400);
    die('Invalid attachment ID.');
}

try {
    $stmt = $db->prepare("SELECT a.*, g.target_role, g.title as guideline_title 
                          FROM staff_guideline_attachments a 
                          JOIN staff_guidelines g ON a.guideline_id = g.id 
                          WHERE a.id = :id LIMIT 1");
    $stmt->execute([':id' => $attachmentId]);
    $attachment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attachment) {
        http_response_code(404);
        die('Document not found in database.');
    }

    $userRole = $_SESSION['role'] ?? '';
    $userId = $_SESSION['id'] ?? 0;
    $userEmail = $_SESSION['email'] ?? ($_SESSION['email_id'] ?? '');

    if (!aitf_user_can_view_guideline_doc($db, $attachment['guideline_id'], $userId, $userRole, $userEmail)) {
        http_response_code(403);
        die('Access denied: This guideline document is not assigned to your account.');
    }

    $relativePath = ltrim($attachment['file_path'], '/\\');
    $fullPath = __DIR__ . '/' . $relativePath;

    if (!file_exists($fullPath) || !is_file($fullPath)) {
        http_response_code(404);
        die('File not found on server.');
    }

    $filename = $attachment['original_name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Determine MIME type
    $mimeType = 'application/octet-stream';
    switch ($ext) {
        case 'pdf':
            $mimeType = 'application/pdf';
            break;
        case 'jpg':
        case 'jpeg':
            $mimeType = 'image/jpeg';
            break;
        case 'png':
            $mimeType = 'image/png';
            break;
        case 'webp':
            $mimeType = 'image/webp';
            break;
        case 'gif':
            $mimeType = 'image/gif';
            break;
        case 'txt':
            $mimeType = 'text/plain; charset=utf-8';
            break;
        case 'doc':
            $mimeType = 'application/msword';
            break;
        case 'docx':
            $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            break;
        case 'xls':
            $mimeType = 'application/vnd.ms-excel';
            break;
        case 'xlsx':
            $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            break;
        case 'csv':
            $mimeType = 'text/csv; charset=utf-8';
            break;
        case 'ppt':
            $mimeType = 'application/vnd.ms-powerpoint';
            break;
        case 'pptx':
            $mimeType = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
            break;
        default:
            $detected = @mime_content_type($fullPath);
            if ($detected) {
                $mimeType = $detected;
            }
            break;
    }

    // Clean output buffer completely to prevent corrupted binary stream
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Crucial: Content-Disposition: inline allows browser to read/view PDF and images directly in new tab without forced downloading
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="' . rawurlencode($filename) . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Content-Length: ' . filesize($fullPath));
    header('Accept-Ranges: bytes');
    header('Cache-Control: private, max-age=3600, must-revalidate');
    header('Pragma: public');
    header('X-Content-Type-Options: nosniff');

    readfile($fullPath);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    die('Server error occurred: ' . htmlspecialchars($e->getMessage()));
}

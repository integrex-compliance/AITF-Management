<?php
ob_start();
ini_set('display_errors', '0');
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
header('Content-Type: application/json; charset=utf-8');
$result = array('success' => false, 'message' => 'Unable to send letter.');
try {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        throw new RuntimeException('Invalid request method.');
    }
    if (empty($_SESSION['username']) || empty($_SESSION['role']) ||
        in_array(strtolower($_SESSION['role']), array('client', 'external', 'auditor'), true)) {
        http_response_code(403);
        throw new RuntimeException('Please sign in with an authorized staff account.');
    }
    session_write_close();
    ignore_user_abort(true);
    set_time_limit(240);
    $actionId = filter_var($_POST['action'] ?? '', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
    if (!$actionId) { throw new RuntimeException('A valid letter action is required.'); }
    require_once __DIR__ . '/dbConnect.php';
    if (session_status() === PHP_SESSION_ACTIVE) { session_write_close(); }
    if (!$db) { throw new RuntimeException('Database connection unavailable.'); }
    require_once __DIR__ . '/include/intimation_letter_auto_helper.php';
    $result = aitfSendIntimationLetterByActionId($db, $actionId, array(
        'retry_interrupted' => ($_POST['retry_interrupted'] ?? '') === '1',
    ));
} catch (Throwable $e) {
    error_log('INTIMATION MANUAL: ' . $e->getMessage());
    $result['message'] = $e->getMessage();
}
ob_end_clean();
echo json_encode($result, JSON_INVALID_UTF8_SUBSTITUTE);

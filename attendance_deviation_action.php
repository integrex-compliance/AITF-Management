<?php
/**
 * Attendance Deviation Action Controller
 * Handles AJAX requests for:
 *  - Submitting attendance deviation requests (with file upload)
 *  - Fetching deviation records and statistics
 *  - Approving deviations with day marking (Present, Half Day, Leave, Late Login)
 *  - Rejecting deviations with mandatory rejection reason
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

if (empty($_SESSION['id']) && empty($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Your session has expired. Please log in again.']);
    exit;
}

require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/attendance_deviation_lib.php';

$loggedInUserId = (int)($_SESSION['id'] ?? 0);
$loggedInUsername = (string)($_SESSION['username'] ?? 'User');
$loggedInRole = (string)($_SESSION['role'] ?? 'Staff');
$loggedInEmail = (string)($_SESSION['email'] ?? '');

$action = trim((string)($_POST['action'] ?? ($_GET['action'] ?? '')));

switch ($action) {

    // 1. Submit Deviation Request (User side)
    case 'submit_deviation':
        $date = trim((string)($_POST['attendance_date'] ?? ''));
        $issue = trim((string)($_POST['issue_reason'] ?? ''));
        $targetUid = isset($_POST['user_id']) ? (int)$_POST['user_id'] : $loggedInUserId;

        // Regular users can only submit for themselves; Top Mgmt / Admin can submit for target user
        if ($targetUid !== $loggedInUserId && !aitf_can_manage_deviations($loggedInRole)) {
            $targetUid = $loggedInUserId;
        }

        $file = $_FILES['proof_file'] ?? null;

        $res = aitf_create_deviation($db, [
            'user_id' => $targetUid,
            'attendance_date' => $date,
            'issue_reason' => $issue
        ], $file);

        if (!$res['success']) {
            http_response_code(400);
        }
        echo json_encode($res);
        exit;

    // 2. Fetch Deviations List (Task Manager / Management side)
    case 'get_deviations':
        if (!aitf_can_manage_deviations($loggedInRole)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to view attendance deviations.']);
            exit;
        }

        $status = trim((string)($_GET['status'] ?? 'all'));
        $search = trim((string)($_GET['search'] ?? ''));
        $userId = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : null;

        $filters = [
            'status' => $status,
            'search' => $search,
            'user_id' => $userId
        ];

        $deviations = aitf_get_all_deviations($db, $filters);
        $counts = aitf_get_deviation_counts($db);

        echo json_encode([
            'success' => true,
            'deviations' => $deviations,
            'counts' => $counts
        ]);
        exit;

    // 3. Approve Deviation (Top Mgmt, Admin, Finance)
    case 'approve_deviation':
        if (!aitf_can_manage_deviations($loggedInRole)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to approve attendance deviations.']);
            exit;
        }

        $deviationId = (int)($_POST['deviation_id'] ?? 0);
        $markedAs = trim((string)($_POST['marked_as'] ?? ''));

        if ($deviationId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid deviation record ID.']);
            exit;
        }

        $adminUser = [
            'user_id' => $loggedInUserId,
            'user_name' => $loggedInUsername,
            'role' => $loggedInRole
        ];

        $res = aitf_approve_deviation($db, $deviationId, $markedAs, $adminUser);
        if (!$res['success']) {
            http_response_code(400);
        }
        echo json_encode($res);
        exit;

    // 4. Reject Deviation (Top Mgmt, Admin, Finance)
    case 'reject_deviation':
        if (!aitf_can_manage_deviations($loggedInRole)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to reject attendance deviations.']);
            exit;
        }

        $deviationId = (int)($_POST['deviation_id'] ?? 0);
        $rejectionReason = trim((string)($_POST['rejection_reason'] ?? ''));

        if ($deviationId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid deviation record ID.']);
            exit;
        }

        if (empty($rejectionReason)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Rejection reason is compulsory. Please explain why this request is being rejected.']);
            exit;
        }

        $adminUser = [
            'user_id' => $loggedInUserId,
            'user_name' => $loggedInUsername,
            'role' => $loggedInRole
        ];

        $res = aitf_reject_deviation($db, $deviationId, $rejectionReason, $adminUser);
        if (!$res['success']) {
            http_response_code(400);
        }
        echo json_encode($res);
        exit;

    // 5. Get User Day Deviation Status (Calendar / Modal helper)
    case 'get_day_deviation':
        $date = trim((string)($_GET['date'] ?? ''));
        $targetUid = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : $loggedInUserId;

        if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode(['success' => false, 'deviation' => null]);
            exit;
        }

        try {
            $stmt = $db->prepare("
                SELECT * FROM aitf_attendance_deviations 
                WHERE user_id = :uid AND attendance_date = :dt 
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute([':uid' => $targetUid, ':dt' => $date]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'deviation' => $row ?: null]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid or unknown action requested.']);
        exit;
}

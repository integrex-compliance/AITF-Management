<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/utility.php';
require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/staff_guidelines_lib.php';

date_default_timezone_set('Asia/Kolkata');

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
          || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1')
          || (isset($_GET['is_ajax']) && $_GET['is_ajax'] == '1')
          || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

function sendResponse($success, $message, $extra = [], $isAjax = true) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $extra));
        exit;
    } else {
        if ($success) {
            $_SESSION['guideline_flash_success'] = $message;
        } else {
            $_SESSION['guideline_flash_error'] = $message;
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? 'masterCreator.php';
        header("Location: {$referer}");
        exit;
    }
}

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    sendResponse(false, 'Unauthorized. Please login again.', [], $isAjax);
}

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

function aitf_can_manage_guidelines() {
    return Utitlity::checkForDirector() || aitf_is_top_management_role() || Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin();
}

// Action: CREATE GUIDELINE (Master Creator)
if ($action === 'create_guideline') {
    if (!aitf_can_manage_guidelines()) {
        sendResponse(false, 'Permission denied. Only Management and Admin can create staff guidelines.', [], $isAjax);
    }

    $title = trim((string)($_POST['guideline_title'] ?? ''));
    $description = trim((string)($_POST['guideline_description'] ?? ''));

    if (empty($title)) {
        sendResponse(false, 'Please provide a guideline title / subject.', [], $isAjax);
    }
    $creator = $_SESSION['username'] ?? 'Director';
    $createdBy = in_array(strtolower(trim($creator)), ['main admin', 'top mgmt', 'admin', 'director'], true) ? 'Director' : $creator;
    $createdRole = $_SESSION['role'] ?? 'Top Mgmt';

    // Handle files
    $files = $_FILES['guideline_files'] ?? [];

    $res = aitf_create_guideline($db, $title, $description, $createdBy, $createdRole, $files);

    if ($res['success']) {
        sendResponse(true, 'Guideline posted successfully! You can now send it to users.', [
            'guideline_id' => $res['guideline_id'],
            'attachments_count' => $res['attachments_count']
        ], $isAjax);
    } else {
        sendResponse(false, $res['message'] ?? 'Failed to post guideline.', [], $isAjax);
    }
}

// Action: ASSIGN / SEND GUIDELINE TO USERS (from addUsers.php or masterCreator.php)
if ($action === 'assign_guideline_users') {
    if (!aitf_can_manage_guidelines()) {
        sendResponse(false, 'Permission denied. Only Management and Admin can assign guidelines to users.', [], $isAjax);
    }

    $guidelineId = (int)($_POST['guideline_id'] ?? 0);
    if ($guidelineId <= 0) {
        sendResponse(false, 'Invalid guideline ID provided.', [], $isAjax);
    }

    $recipients = $_POST['recipients'] ?? [];
    if (!is_array($recipients)) {
        $recipients = !empty($recipients) ? explode(',', (string)$recipients) : [];
    }

    $assignedBy = $_SESSION['username'] ?? 'Director';
    $res = aitf_assign_guideline_to_users($db, $guidelineId, $recipients, $assignedBy);

    if ($res['success']) {
        sendResponse(true, $res['message'], [
            'guideline_id' => $guidelineId,
            'recipients_count' => $res['recipients_count'],
            'assigned_names' => $res['assigned_names'] ?? []
        ], $isAjax);
    } else {
        sendResponse(false, $res['message'] ?? 'Failed to assign guideline.', [], $isAjax);
    }
}

// Action: GET GUIDELINE RECIPIENTS & USERS LIST (for the Send modal)
if ($action === 'get_guideline_assignment_info') {
    if (!aitf_can_manage_guidelines()) {
        sendResponse(false, 'Permission denied.', [], true);
    }

    $guidelineId = (int)($_GET['guideline_id'] ?? ($_POST['guideline_id'] ?? 0));
    if ($guidelineId <= 0) {
        sendResponse(false, 'Invalid guideline ID.', [], true);
    }

    $guideline = aitf_get_guideline_by_id($db, $guidelineId);
    if (!$guideline) {
        sendResponse(false, 'Guideline not found.', [], true);
    }

    $allUsers = aitf_get_all_system_users($db);
    $recipients = $guideline['recipients'] ?? [];
    $recipientKeys = $guideline['recipient_keys'] ?? [];

    sendResponse(true, 'Success', [
        'guideline' => [
            'id' => $guideline['id'],
            'title' => $guideline['title'],
            'description' => $guideline['description'],
            'created_at' => date('d M Y, h:i A', strtotime($guideline['created_at']))
        ],
        'all_users' => $allUsers,
        'assigned_keys' => $recipientKeys,
        'recipients' => $recipients
    ], true);
}

// Action: DELETE GUIDELINE
if ($action === 'delete_guideline') {
    if (!aitf_can_manage_guidelines()) {
        sendResponse(false, 'Permission denied. Only Management and Admin can delete staff guidelines.', [], $isAjax);
    }

    $id = (int)($_POST['guideline_id'] ?? 0);
    if ($id <= 0) {
        sendResponse(false, 'Invalid guideline ID provided.', [], $isAjax);
    }

    $deleted = aitf_delete_guideline($db, $id);
    if ($deleted) {
        sendResponse(true, 'Guideline and its assignments deleted successfully.', [], $isAjax);
    } else {
        sendResponse(false, 'Failed to delete guideline.', [], $isAjax);
    }
}

// Action: GET GUIDELINE DETAILS
if ($action === 'get_guideline') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        sendResponse(false, 'Invalid guideline ID.', [], true);
    }

    $guideline = aitf_get_guideline_by_id($db, $id);
    if (!$guideline) {
        sendResponse(false, 'Guideline not found.', [], true);
    }

    // Enhance attachments with file icons and formatted sizes
    if (!empty($guideline['attachments'])) {
        foreach ($guideline['attachments'] as &$att) {
            $att['formatted_size'] = aitf_format_file_size($att['file_size']);
            $iconInfo = aitf_get_file_icon_info($att['original_name']);
            $att['icon_class'] = $iconInfo['icon'];
            $att['icon_color'] = $iconInfo['color'];
            $att['view_url'] = 'view_guideline_doc.php?id=' . $att['id'];
        }
        unset($att);
    }

    sendResponse(true, 'Success', ['guideline' => $guideline], true);
}

sendResponse(false, 'Invalid action.', [], $isAjax);

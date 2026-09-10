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

// Action: CREATE GUIDELINE
if ($action === 'create_guideline') {
    // Only Director / Top Mgmt / Admin authorized to post staff guidelines
    if (!Utitlity::checkForDirector() && !aitf_is_top_management_role()) {
        sendResponse(false, 'Permission denied. Only Top Management can post staff guidelines.', [], $isAjax);
    }

    $title = trim((string)($_POST['guideline_title'] ?? ''));
    $description = trim((string)($_POST['guideline_description'] ?? ''));
    $targetRole = trim((string)($_POST['target_role'] ?? 'All'));

    if (empty($title)) {
        sendResponse(false, 'Please provide a guideline title / subject.', [], $isAjax);
    }
    if (empty($description)) {
        sendResponse(false, 'Please write the guideline description / instructions.', [], $isAjax);
    }

    $creator = $_SESSION['username'] ?? 'Director';
    $createdBy = in_array(strtolower(trim($creator)), ['main admin', 'top mgmt', 'admin', 'director'], true) ? 'Director' : $creator;
    $createdRole = $_SESSION['role'] ?? 'Top Mgmt';

    // Handle files
    $files = $_FILES['guideline_files'] ?? [];

    $res = aitf_create_guideline($db, $title, $description, $targetRole, $createdBy, $createdRole, $files);

    if ($res['success']) {
        sendResponse(true, 'Guideline successfully posted for: ' . ($targetRole === 'All' ? 'All Staff' : $targetRole), [
            'guideline_id' => $res['guideline_id'],
            'attachments_count' => $res['attachments_count']
        ], $isAjax);
    } else {
        sendResponse(false, $res['message'] ?? 'Failed to post guideline.', [], $isAjax);
    }
}

// Action: DELETE GUIDELINE
if ($action === 'delete_guideline') {
    if (!Utitlity::checkForDirector() && !aitf_is_top_management_role()) {
        sendResponse(false, 'Permission denied. Only Top Management can delete staff guidelines.', [], $isAjax);
    }

    $id = (int)($_POST['guideline_id'] ?? 0);
    if ($id <= 0) {
        sendResponse(false, 'Invalid guideline ID provided.', [], $isAjax);
    }

    $deleted = aitf_delete_guideline($db, $id);
    if ($deleted) {
        sendResponse(true, 'Guideline and its attachments deleted successfully.', [], $isAjax);
    } else {
        sendResponse(false, 'Failed to delete guideline.', [], $isAjax);
    }
}

// Action: GET GUIDELINE DETAILS (FOR PREVIEW MODAL)
if ($action === 'get_guideline') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        sendResponse(false, 'Invalid guideline ID.', [], true);
    }

    $guideline = aitf_get_guideline_by_id($db, $id);
    if (!$guideline) {
        sendResponse(false, 'Guideline not found.', [], true);
    }

    $userRole = $_SESSION['role'] ?? '';
    if (!aitf_user_can_view_guideline($userRole, $guideline)) {
        sendResponse(false, 'Access denied.', [], true);
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

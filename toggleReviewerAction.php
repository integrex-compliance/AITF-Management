<?php
header('Content-Type: application/json');

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/auditor_portal_helper.php';
require_once __DIR__ . '/include/reviewer_conflict_helper.php';
include_once './dbConnect.php';
include_once 'utility.php';

// Validate Director, Main Admin, or Admin role
if (!Utitlity::checkForDirector() && !Utitlity::checkForUserMainAdmin() && !Utitlity::checkForUserAdmin()) {
    echo json_encode(["success" => false, "message" => "Unauthorized access. Director or Admin role required."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$auditorId = isset($_POST['auditor_id']) ? (int)$_POST['auditor_id'] : 0;
if ($auditorId <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid Auditor ID."]);
    exit;
}

try {
    aitf_ensure_auditor_portal_schema($db);

    // Fetch auditor
    $stmt = $db->prepare("SELECT auditors_id, auditor_name, is_reviewer, login_email, auditor_email, auditor_number FROM auditors WHERE auditors_id = :id");
    $stmt->execute([':id' => $auditorId]);
    $auditor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$auditor) {
        echo json_encode(["success" => false, "message" => "Auditor not found."]);
        exit;
    }

    $currentStatus = (int)($auditor['is_reviewer'] ?? 0);

    // If new status passed explicitly, use it; otherwise toggle
    if (isset($_POST['is_reviewer']) && $_POST['is_reviewer'] !== '') {
        $newStatus = (int)$_POST['is_reviewer'] ? 1 : 0;
    } else {
        $newStatus = ($currentStatus === 1) ? 0 : 1;
    }

    $updateStmt = $db->prepare("UPDATE auditors SET is_reviewer = :status WHERE auditors_id = :id");
    $updateStmt->execute([
        ':status' => $newStatus,
        ':id' => $auditorId
    ]);

    $auditorName = $auditor['auditor_name'];
    $msg = ($newStatus === 1)
        ? "Auditor '{$auditorName}' has been successfully assigned as a Reviewer."
        : "Reviewer role removed for auditor '{$auditorName}'.";

    echo json_encode([
        "success" => true,
        "auditor_id" => (int)$auditorId,
        "is_reviewer" => $newStatus,
        "auditor_name" => $auditorName,
        "message" => $msg
    ]);
    exit;

} catch (PDOException $e) {
    error_log("Database error in toggleReviewerAction.php: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Database error occurred while processing the request."]);
    exit;
} catch (Exception $e) {
    error_log("Error in toggleReviewerAction.php: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "An unexpected error occurred."]);
    exit;
}

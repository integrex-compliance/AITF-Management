<?php
header('Content-Type: application/json');

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/auditor_portal_helper.php';
include_once './dbConnect.php';
include 'utility.php';

// Validate Director, Main Admin, or Admin role
if (!Utitlity::checkForDirector() && !Utitlity::checkForUserMainAdmin() && !Utitlity::checkForUserAdmin()) {
    echo json_encode(["success" => false, "message" => "Unauthorized access. Director or Admin role required."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$auditor_id = filter_input(INPUT_POST, 'auditor_id', FILTER_SANITIZE_NUMBER_INT);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if (!$auditor_id || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid input parameters. Please provide a valid auditor ID and email."]);
    exit;
}

try {
    // Ensure the auditor portal schema has required columns
    aitf_ensure_auditor_portal_schema($db);
    
    // Fetch auditor details
    $stmt = $db->prepare("SELECT * FROM auditors WHERE auditors_id = :id");
    $stmt->execute([':id' => $auditor_id]);
    $auditor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$auditor) {
        echo json_encode(["success" => false, "message" => "Auditor not found."]);
        exit;
    }

    // Generate and hash key
    $key = aitf_generate_auditor_login_key();
    $hash = password_hash($key, PASSWORD_DEFAULT);

    // Update auditor record
    $updateStmt = $db->prepare("
        UPDATE auditors 
        SET login_email = :email, 
            login_key_hash = :hash, 
            login_key_generated_at = NOW(), 
            login_enabled = 1, 
            last_key_sent_at = NOW() 
        WHERE auditors_id = :id
    ");

    $updateStmt->execute([
        ':email' => $email,
        ':hash' => $hash,
        ':id' => $auditor_id
    ]);

    // Sync auditor to user table so they can register using EID
    $eid = !empty($auditor['auditor_number']) ? $auditor['auditor_number'] : 'AUD-' . $auditor['auditors_id'];
    $checkUserStmt = $db->prepare("SELECT user_id, account_status FROM user WHERE email_id = :email LIMIT 1");
    $checkUserStmt->execute([':email' => $email]);
    $existingUser = $checkUserStmt->fetch();

    if (!$existingUser) {
        $insertUserStmt = $db->prepare("
            INSERT INTO user (user_name, abbreviation, email_id, password, role, is_deleted, e_id, account_status) 
            VALUES (:name, :abbr, :email, :pass, 'Auditor', '0', :eid, 'pending')
        ");
        $insertUserStmt->execute([
            ':name' => $auditor['auditor_name'],
            ':abbr' => $auditor['abbrevation'] ?? '',
            ':email' => $email,
            ':pass' => $hash,
            ':eid' => $eid
        ]);
    } else {
        $updateUserStmt = $db->prepare("
            UPDATE user 
            SET e_id = :eid, account_status = 'pending', role = 'Auditor' 
            WHERE user_id = :uid
        ");
        $updateUserStmt->execute([
            ':eid' => $eid, 
            ':uid' => $existingUser['user_id']
        ]);
    }

    // Send the key via email
    try {
        aitf_send_auditor_key_mail($auditor, $email, $key);
        echo json_encode(["success" => true, "message" => "EID generated and sent successfully."]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "EID saved, but failed to send email: " . $e->getMessage()]);
    }

} catch (PDOException $e) {
    error_log("Database error in auditorKeyAction.php: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Database error occurred while processing the request."]);
} catch (Exception $e) {
    error_log("Error in auditorKeyAction.php: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "An unexpected error occurred."]);
}

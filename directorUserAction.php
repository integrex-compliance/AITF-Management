<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

include_once __DIR__ . '/dbConnect.php';
include_once __DIR__ . '/utility.php';
require_once __DIR__ . '/include/rbac_helper.php';

date_default_timezone_set('Asia/Kolkata');

function director_action_redirect($message)
{
    $_SESSION['director_notice'] = $message;
    header('Location: directorSpace.php');
    exit;
}

if (!Utitlity::checkForDirector()) {
    $_SESSION['login_notice'] = 'Please log in again to continue.';
    header('Location: loginSelection.php?reason=login_required');
    exit;
}

if (!$db) {
    director_action_redirect('Database connection is not available.');
}

aitf_ensure_rbac_schema($db);

$action = trim((string)($_POST['action'] ?? ''));
$userId = (int)($_POST['user_id'] ?? 0);

if ($userId <= 0 || !in_array($action, array('deactivate', 'activate', 'delete'), true)) {
    director_action_redirect('Invalid user action.');
}

try {
    $stmt = $db->prepare("SELECT user_id, user_name, email_id, role, account_status
                          FROM user
                          WHERE user_id = :user_id
                          LIMIT 1");
    $stmt->execute(array(':user_id' => $userId));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        director_action_redirect('User not found.');
    }

    if ($action === 'deactivate') {
        $update = $db->prepare("UPDATE user SET account_status = 'inactive' WHERE user_id = :user_id");
        $update->execute(array(':user_id' => $userId));
        aitf_close_user_active_sessions($db, $userId);
        director_action_redirect($user['user_name'] . ' has been deactivated.');
    }

    if ($action === 'activate') {
        $update = $db->prepare("UPDATE user SET account_status = 'active' WHERE user_id = :user_id");
        $update->execute(array(':user_id' => $userId));
        director_action_redirect($user['user_name'] . ' has been activated.');
    }

    if ($action === 'delete') {
        $db->beginTransaction();

        $otpDelete = $db->prepare("DELETE FROM employee_signup_otps WHERE user_id = :user_id");
        $otpDelete->execute(array(':user_id' => $userId));

        aitf_close_user_active_sessions($db, $userId);

        $delete = $db->prepare("DELETE FROM user WHERE user_id = :user_id");
        $delete->execute(array(':user_id' => $userId));

        $db->commit();
        director_action_redirect($user['user_name'] . ' has been permanently deleted.');
    }
} catch (Exception $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Director user action error: ' . $e->getMessage());
    director_action_redirect('User action could not be completed. Please try again.');
}

director_action_redirect('Invalid user action.');

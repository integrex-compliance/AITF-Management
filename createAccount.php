<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

include_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';

date_default_timezone_set('Asia/Kolkata');

function aitf_create_account_redirect($message)
{
    $_SESSION['login_notice'] = $message;
    header('Location: loginSelection.php?create_account=1');
    exit;
}

if (!$db) {
    aitf_create_account_redirect('Database connection is not available. Please try again.');
}

aitf_ensure_rbac_schema($db);

$action = $_POST['create_account_action'] ?? '';

if ($action === 'request_otp') {
    $email = strtolower(trim((string)($_POST['signup_email'] ?? '')));
    $password = (string)($_POST['signup_password'] ?? '');
    $confirmPassword = (string)($_POST['signup_confirm_password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        aitf_create_account_redirect('Please enter a valid registered email address.');
    }
    if (strlen($password) < 8) {
        aitf_create_account_redirect('Password must be at least 8 characters.');
    }
    if (!preg_match('/[A-Z]/', $password)) {
        aitf_create_account_redirect('Password must contain at least one capital letter.');
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        aitf_create_account_redirect('Password must contain at least one special character.');
    }
    if ($password !== $confirmPassword) {
        aitf_create_account_redirect('Password and confirm password do not match.');
    }

    try {
        $stmt = $db->prepare("SELECT user_id, user_name, abbreviation, email_id, role, e_id, account_status
                              FROM user
                              WHERE LOWER(email_id) = :email AND is_deleted = '0'
                              LIMIT 1");
        $stmt->execute(array(':email' => $email));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || empty($user['e_id'])) {
            aitf_create_account_redirect('You are not allowed to create an account, try using another mail account.');
        }
        if (($user['account_status'] ?? 'active') === 'active') {
            aitf_create_account_redirect('This account is already active. Please use login.');
        }

        $otp = (string)random_int(100000, 999999);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + 600);

        $db->prepare("UPDATE employee_signup_otps SET consumed_at = NOW() WHERE user_id = :user_id AND consumed_at IS NULL")
            ->execute(array(':user_id' => $user['user_id']));

        $insert = $db->prepare("INSERT INTO employee_signup_otps (user_id, email_id, otp_hash, expires_at)
                                VALUES (:user_id, :email_id, :otp_hash, :expires_at)");
        $insert->execute(array(
            ':user_id' => $user['user_id'],
            ':email_id' => $user['email_id'],
            ':otp_hash' => $otpHash,
            ':expires_at' => $expiresAt
        ));

        aitf_send_employee_otp_mail($user, $otp);

        $_SESSION['employee_signup'] = array(
            'user_id' => (int)$user['user_id'],
            'email_id' => $user['email_id'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'requested_at' => time()
        );
        $_SESSION['login_notice'] = 'OTP sent to your registered email. It is valid for 10 minutes.';
        header('Location: loginSelection.php?create_account=1&step=verify');
        exit;
    } catch (Exception $e) {
        error_log('Create account OTP error: ' . $e->getMessage());
        aitf_create_account_redirect('OTP could not be sent. Please try again.');
    }
}

if ($action === 'complete_signup') {
    $signup = $_SESSION['employee_signup'] ?? array();
    $userId = (int)($signup['user_id'] ?? 0);
    $email = strtolower(trim((string)($signup['email_id'] ?? '')));
    $otp = trim((string)($_POST['signup_otp'] ?? ''));
    $eid = strtoupper(trim((string)($_POST['signup_eid'] ?? '')));

    if ($userId <= 0 || $email === '') {
        aitf_create_account_redirect('Please start account creation again.');
    }
    if (!preg_match('/^[0-9]{6}$/', $otp)) {
        $_SESSION['login_notice'] = 'Please enter the 6 digit OTP.';
        header('Location: loginSelection.php?create_account=1&step=verify');
        exit;
    }
    if ($eid === '') {
        $_SESSION['login_notice'] = 'Please enter your employee ID.';
        header('Location: loginSelection.php?create_account=1&step=verify');
        exit;
    }

    try {
        $stmt = $db->prepare("SELECT user_id, user_name, abbreviation, email_id, role, e_id
                              FROM user
                              WHERE user_id = :user_id AND LOWER(email_id) = :email AND is_deleted = '0'
                              LIMIT 1");
        $stmt->execute(array(':user_id' => $userId, ':email' => $email));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || strtoupper((string)$user['e_id']) !== $eid) {
            $_SESSION['login_notice'] = 'Employee ID does not match this email.';
            header('Location: loginSelection.php?create_account=1&step=verify');
            exit;
        }

        $otpStmt = $db->prepare("SELECT otp_id, otp_hash
                                 FROM employee_signup_otps
                                 WHERE user_id = :user_id
                                   AND consumed_at IS NULL
                                   AND expires_at >= NOW()
                                 ORDER BY otp_id DESC
                                 LIMIT 1");
        $otpStmt->execute(array(':user_id' => $userId));
        $otpRow = $otpStmt->fetch(PDO::FETCH_ASSOC);
        if (!$otpRow || !password_verify($otp, $otpRow['otp_hash'])) {
            $_SESSION['login_notice'] = 'Invalid or expired OTP.';
            header('Location: loginSelection.php?create_account=1&step=verify');
            exit;
        }

        $update = $db->prepare("UPDATE user
                                SET password = :password,
                                    account_status = 'active',
                                    password_created_at = NOW()
                                WHERE user_id = :user_id");
        $update->execute(array(
            ':password' => $signup['password_hash'],
            ':user_id' => $userId
        ));

        $db->prepare("UPDATE employee_signup_otps SET consumed_at = NOW() WHERE otp_id = :otp_id")
            ->execute(array(':otp_id' => $otpRow['otp_id']));

        unset($_SESSION['employee_signup']);
        $_SESSION['username'] = $user['user_name'];
        $_SESSION['id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['abbrevation'] = $user['abbreviation'];
        aitf_mark_session_authenticated();
        aitf_record_login_activity($db, array(
            'user_id' => $user['user_id'],
            'user_name' => $user['user_name'],
            'email_id' => $user['email_id'],
            'role' => $user['role'],
            'e_id' => $user['e_id']
        ));

        if ($user['role'] === 'Auditor') {
            $audStmt = $db->prepare("SELECT auditors_id FROM auditors WHERE login_email = :email OR (auditor_number = :eid AND auditor_number != '') LIMIT 1");
            $audStmt->execute(array(':email' => $user['email_id'], ':eid' => $user['e_id']));
            if ($audData = $audStmt->fetch(PDO::FETCH_ASSOC)) {
                $_SESSION['auditor_id'] = $audData['auditors_id'];
            }
        }
        aitf_redirect_user_by_role();
    } catch (Exception $e) {
        error_log('Create account complete error: ' . $e->getMessage());
        aitf_create_account_redirect('Account could not be activated. Please try again.');
    }
}

aitf_create_account_redirect('Please use the create account form.');

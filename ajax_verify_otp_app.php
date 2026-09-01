<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/otp_helper.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$entered_otp = preg_replace('/\D/', '', (string)($_POST['otp'] ?? ''));
$email = strtolower(filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL));

if (empty($entered_otp) || strlen($entered_otp) !== 6) {
    echo json_encode(['success' => false, 'message' => 'Please enter the 6-digit OTP received in your email.']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email address is required.']);
    exit;
}

$is_matched = false;

// 1. Check Session OTP (valid for 10 minutes)
if (!empty($_SESSION['application_otp']) && !empty($_SESSION['application_otp_time'])) {
    if ((time() - (int)$_SESSION['application_otp_time']) <= 600) {
        $sessEmail = strtolower((string)($_SESSION['application_otp_email'] ?? ''));
        if ($sessEmail === $email || empty($sessEmail)) {
            if (hash_equals((string)$_SESSION['application_otp'], (string)$entered_otp)) {
                $is_matched = true;
            }
        }
    }
}

// 2. Check Database OTP Records (persisted for live server stability and resends)
if (!$is_matched && isset($db) && $db) {
    if (aitf_verify_otp_record($db, $email, $entered_otp)) {
        $is_matched = true;
    }
}

if ($is_matched) {
    // Mark as verified in session
    $_SESSION['application_email_verified'] = $email;

    // Clear one-time OTP from session
    unset(
        $_SESSION['application_otp'],
        $_SESSION['application_otp_time'],
        $_SESSION['application_otp_email'],
        $_SESSION['application_otp_attempts']
    );

    echo json_encode([
        'success' => true,
        'message' => 'Email verified successfully! Proceed to submit the application form.'
    ]);
} else {
    // Track attempts
    $_SESSION['application_otp_attempts'] = (int)($_SESSION['application_otp_attempts'] ?? 0) + 1;
    $remaining_attempts = max(0, 5 - $_SESSION['application_otp_attempts']);

    if ($remaining_attempts <= 0) {
        unset(
            $_SESSION['application_otp'],
            $_SESSION['application_otp_time'],
            $_SESSION['application_otp_email'],
            $_SESSION['application_otp_attempts']
        );
        echo json_encode([
            'success' => false,
            'message' => 'Too many failed attempts. Please request a new OTP.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Invalid OTP. Please check the 6-digit code received on your email. ({$remaining_attempts} attempts remaining)"
        ]);
    }
}

<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$entered_otp = trim($_POST['otp'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (empty($entered_otp)) {
    echo json_encode(['success' => false, 'message' => 'OTP is required.']);
    exit;
}

if (!isset($_SESSION['application_otp']) || !isset($_SESSION['application_otp_time'])) {
    echo json_encode(['success' => false, 'message' => 'OTP session expired or not found. Please request a new OTP.']);
    exit;
}

if ($_SESSION['application_otp_email'] !== $email) {
    echo json_encode(['success' => false, 'message' => 'Email address mismatch. Please request a new OTP for this email.']);
    exit;
}

// Check expiration (10 minutes = 600 seconds)
if (time() - $_SESSION['application_otp_time'] > 600) {
    unset($_SESSION['application_otp'], $_SESSION['application_otp_time'], $_SESSION['application_otp_email'], $_SESSION['application_otp_attempts']);
    echo json_encode(['success' => false, 'message' => 'OTP has expired (valid for 10 minutes). Please request a new one.']);
    exit;
}

// Track and limit verification attempts (Max 5 attempts to prevent brute force)
$_SESSION['application_otp_attempts'] = ($_SESSION['application_otp_attempts'] ?? 0) + 1;
if ($_SESSION['application_otp_attempts'] > 5) {
    unset($_SESSION['application_otp'], $_SESSION['application_otp_time'], $_SESSION['application_otp_email'], $_SESSION['application_otp_attempts']);
    echo json_encode(['success' => false, 'message' => 'Too many failed attempts. This OTP has been invalidated for security. Please request a new OTP.']);
    exit;
}

if (hash_equals((string)$_SESSION['application_otp'], (string)$entered_otp)) {
    // Mark as verified
    $_SESSION['application_email_verified'] = $email;
    // Clear OTP from session so it cannot be re-used
    unset($_SESSION['application_otp'], $_SESSION['application_otp_time'], $_SESSION['application_otp_email'], $_SESSION['application_otp_attempts']);
    echo json_encode(['success' => true, 'message' => 'Email verified successfully!']);
} else {
    $remaining_attempts = 5 - $_SESSION['application_otp_attempts'];
    echo json_encode(['success' => false, 'message' => "Invalid OTP. Please try again. ({$remaining_attempts} attempts remaining)"]);
}

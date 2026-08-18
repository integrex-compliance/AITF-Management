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

if (!isset($_SESSION['info_updation_otp']) || !isset($_SESSION['info_updation_otp_time'])) {
    echo json_encode(['success' => false, 'message' => 'OTP session expired or not found. Please request a new OTP.']);
    exit;
}

if ($_SESSION['info_updation_otp_email'] !== $email) {
    echo json_encode(['success' => false, 'message' => 'Email address mismatch. Please request a new OTP for this email.']);
    exit;
}

// Check expiration (10 minutes = 600 seconds)
if (time() - $_SESSION['info_updation_otp_time'] > 600) {
    echo json_encode(['success' => false, 'message' => 'OTP has expired (valid for 10 minutes). Please request a new one.']);
    exit;
}

if ($entered_otp === $_SESSION['info_updation_otp']) {
    // Mark as verified
    $_SESSION['info_updation_email_verified'] = $email;
    echo json_encode(['success' => true, 'message' => 'Email verified successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
}

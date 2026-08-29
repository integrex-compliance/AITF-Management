<?php
session_start();
require_once __DIR__ . '/testemail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$org_name = htmlspecialchars(trim($_POST['org_name'] ?? ''));
$sign_name = htmlspecialchars(trim($_POST['sign_name'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email address is required.']);
    exit;
}

if (empty($org_name)) {
    echo json_encode(['success' => false, 'message' => 'Please enter Organization Name first.']);
    exit;
}

$recipientName = !empty($sign_name) ? $sign_name : $org_name;

// Rate limiting: 60 seconds cooldown between OTP requests
if (isset($_SESSION['nc_closing_otp_last_sent']) && (time() - $_SESSION['nc_closing_otp_last_sent']) < 60) {
    $remaining = 60 - (time() - $_SESSION['nc_closing_otp_last_sent']);
    echo json_encode(['success' => false, 'message' => "Please wait {$remaining} seconds before requesting a new OTP."]);
    exit;
}

// Generate cryptographically secure 6-digit OTP
$otp = sprintf("%06d", random_int(100000, 999999));

// Store in session securely (NEVER returned to browser)
$_SESSION['nc_closing_otp'] = $otp;
$_SESSION['nc_closing_otp_time'] = time();
$_SESSION['nc_closing_otp_email'] = $email;
$_SESSION['nc_closing_otp_last_sent'] = time();
$_SESSION['nc_closing_otp_attempts'] = 0;

// Send email
try {
    $emailSender = new AITFServiceEmailSender();
    $subject = "Email Verification – NC Closing Form | AITF";
    $body = "<p>Dear " . htmlspecialchars($recipientName) . ",</p>
             <p>Greetings from AITF.</p>
             <p>Your One-Time Password (OTP) to verify your email address for the <b>Nonconformance Closer Report</b> submitted on behalf of <b>" . htmlspecialchars($org_name) . "</b> is:</p>
             <h2 style='color: #0284c7; font-size: 26px; letter-spacing: 4px; padding: 12px 20px; background: #f0f9ff; border: 1px solid #bae6fd; display: inline-block; border-radius: 8px;'>{$otp}</h2>
             <p>Please enter this OTP in the NC Closing Form to verify your email address and proceed with form submission.</p>
             <p>This OTP is valid for 10 minutes and confidential.</p>
             <p>Regards,<br>AITF Management Certification Ltd.<br>Certification Body</p>";
    
    $emailSender->sendEmail($email, $recipientName, $body, $subject);
    echo json_encode(['success' => true, 'message' => 'OTP has been sent to your email. Please check your inbox / spam.']);
} catch (Exception $e) {
    error_log("NC OTP Email Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP email. Please try again later.']);
}

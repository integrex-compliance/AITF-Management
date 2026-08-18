<?php
session_start();
require_once __DIR__ . '/testemail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$org_name = htmlspecialchars(trim($_POST['org_name'] ?? ''));
$contact_person = htmlspecialchars(trim($_POST['contact_person'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email is required.']);
    exit;
}

if (empty($org_name) || empty($contact_person)) {
    echo json_encode(['success' => false, 'message' => 'Organization name and Contact person are required before sending OTP.']);
    exit;
}

// Generate 6-digit OTP
$otp = sprintf("%06d", mt_rand(100000, 999999));

// Store in session
$_SESSION['info_updation_otp'] = $otp;
$_SESSION['info_updation_otp_time'] = time();
$_SESSION['info_updation_otp_email'] = $email;

// Send email
try {
    $emailSender = new AITFServiceEmailSender();
    $subject = "Email Verification – Information Updation Form | AITF";
    $body = "<p>Dear {$contact_person},</p>
             <p>Greetings from AITF.</p>
             <p>Your One-Time Password (OTP) to verify your email address for the Information Updation Form submitted on behalf of {$org_name} is:</p>
             <h2 style='color: #0056b3; font-size: 24px; padding: 10px; background: #f4f4f4; display: inline-block; border-radius: 5px;'>{$otp}</h2>
             <p>Please enter this OTP in the Information Updation Form to verify your email address and complete the submission process.</p>
             <p>Once your email address is successfully verified, you can submit the information updation form to AITF for further processing.</p>
             <p>This OTP is valid for 10 minutes and should not be shared with anyone.</p>
             <p>Regards,<br>AITF Management Certification Ltd.<br>Certification Body</p>";
    
    $emailSender->sendEmail($email, $contact_person, $body, $subject);
    echo json_encode(['success' => true, 'message' => 'OTP sent successfully to your email. Valid for 10 minutes.']);
} catch (Exception $e) {
    error_log("OTP Email Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP email. Please try again later.']);
}

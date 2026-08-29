<?php
header('Content-Type: application/json');

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

require_once __DIR__ . '/include/job_mailer_helper.php';

$action = trim($_POST['action'] ?? ($_GET['action'] ?? ''));

if ($action === 'send_otp') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
        exit;
    }

    // Rate limit: 20 seconds cooldown
    if (isset($_SESSION['career_otp_time']) && (time() - $_SESSION['career_otp_time'] < 20)) {
        $remaining = 20 - (time() - $_SESSION['career_otp_time']);
        echo json_encode(['status' => 'error', 'message' => "Please wait {$remaining} seconds before requesting a new OTP."]);
        exit;
    }

    $otp = (string)random_int(100000, 999999);
    $_SESSION['career_otp'] = $otp;
    $_SESSION['career_otp_email'] = strtolower($email);
    $_SESSION['career_otp_time'] = time();
    $_SESSION['career_otp_verified'] = false;

    $mailSent = sendJobMail(function (PHPMailer $mail) use ($email, $otp) {
        $mail->setFrom('certification@aitf.co.in', 'AITF Careers');
        $mail->Sender = 'certification@aitf.co.in';
        $mail->addCustomHeader('Return-Path', 'certification@aitf.co.in');
        $mail->addAddress($email);
        $mail->Subject = 'Your AITF Job Application Verification Code: ' . $otp;
        $mail->isHTML(true);
        $mail->Body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:24px;border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;">'
            . '<h2 style="color:#0f172a;margin-top:0;">Email Verification Code</h2>'
            . '<p style="color:#334155;font-size:15px;line-height:1.6;">You are applying for an opportunity with <strong>AITF Management Certification</strong>. Please use the 6-digit verification code below to verify your email address:</p>'
            . '<div style="margin:24px 0;padding:16px 24px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;text-align:center;">'
            . '<span style="font-size:32px;font-weight:bold;letter-spacing:6px;color:#1d4ed8;">' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</span>'
            . '</div>'
            . '<p style="color:#64748b;font-size:13px;line-height:1.5;">This verification code is valid for 10 minutes. If you did not submit this request, please ignore this email.</p>'
            . '<hr style="border:none;border-top:1px solid #f1f5f9;margin:20px 0;">'
            . '<p style="color:#94a3b8;font-size:12px;margin-bottom:0;">AITF Management Certification &bull; Assessment & Certification Body</p>'
            . '</div>';
        $mail->AltBody = "Your AITF Job Application Verification Code is: $otp\nValid for 10 minutes.";
    });

    if ($mailSent) {
        echo json_encode(['status' => 'ok', 'message' => 'Verification code sent to ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8')]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unable to send verification email. Please verify your email address and try again.']);
    }
    exit;
}

if ($action === 'verify_otp') {
    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
        exit;
    }

    if ($otp === '' || strlen($otp) < 4) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter the 6-digit OTP received in your email.']);
        exit;
    }

    $storedOtp = $_SESSION['career_otp'] ?? '';
    $storedEmail = $_SESSION['career_otp_email'] ?? '';
    $storedTime = $_SESSION['career_otp_time'] ?? 0;

    if ($storedOtp === '' || strtolower($email) !== $storedEmail) {
        echo json_encode(['status' => 'error', 'message' => 'No verification request found for this email. Please click Send OTP.']);
        exit;
    }

    if (time() - $storedTime > 600) {
        echo json_encode(['status' => 'error', 'message' => 'Verification code has expired. Please request a new code.']);
        exit;
    }

    if ($otp === $storedOtp) {
        $_SESSION['career_otp_verified'] = true;
        $_SESSION['career_verified_email'] = strtolower($email);
        echo json_encode(['status' => 'ok', 'message' => 'Email verified successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Incorrect OTP code. Please check your email and try again.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
exit;

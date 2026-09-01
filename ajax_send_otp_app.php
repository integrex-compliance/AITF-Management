<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/otp_helper.php';
require_once __DIR__ . '/testemail.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = strtolower(filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL));
$raw_contact = trim((string)($_POST['contact_person'] ?? ''));
$raw_org = trim((string)($_POST['org_name'] ?? ''));

// Ensure clean UTF-8 string encoding
if (!mb_check_encoding($raw_contact, 'UTF-8')) {
    $raw_contact = mb_convert_encoding($raw_contact, 'UTF-8', 'ISO-8859-1');
}
if (!mb_check_encoding($raw_org, 'UTF-8')) {
    $raw_org = mb_convert_encoding($raw_org, 'UTF-8', 'ISO-8859-1');
}

$contact_person = htmlspecialchars(strip_tags($raw_contact), ENT_QUOTES, 'UTF-8');
$org_name = htmlspecialchars(strip_tags($raw_org), ENT_QUOTES, 'UTF-8');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email address is required.']);
    exit;
}

if (empty($org_name) || empty($contact_person)) {
    echo json_encode(['success' => false, 'message' => 'Organization name and Contact person are required before requesting OTP.']);
    exit;
}

// Rate limiting: 60 seconds cooldown between OTP requests
if (isset($_SESSION['application_otp_last_sent']) && (time() - $_SESSION['application_otp_last_sent']) < 60) {
    $remaining = 60 - (time() - $_SESSION['application_otp_last_sent']);
    echo json_encode(['success' => false, 'message' => "Please wait {$remaining} seconds before requesting a new OTP."]);
    exit;
}

// Generate cryptographically secure 6-digit OTP
$otp = sprintf("%06d", random_int(100000, 999999));

// Store in session
$_SESSION['application_otp'] = $otp;
$_SESSION['application_otp_time'] = time();
$_SESSION['application_otp_email'] = $email;
$_SESSION['application_otp_last_sent'] = time();
$_SESSION['application_otp_attempts'] = 0;

// Also persist in database for high reliability on live servers
if (isset($db) && $db) {
    aitf_save_otp_record($db, $email, $otp);
}

// Send email
try {
    $emailSender = new AITFServiceEmailSender();
    $subject = "Email Verification - Application Form | AITF";
    $body = "<div style=\"background-color: #f8fafc; padding: 30px 15px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;\">
        <div style=\"max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;\">
            <div style=\"background: linear-gradient(135deg, #0b4a78 0%, #0369a1 100%); padding: 24px 28px; text-align: center;\">
                <h2 style=\"color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;\">AITF MANAGEMENT CERTIFICATION LIMITED</h2>
                <p style=\"color: #bae6fd; margin: 6px 0 0 0; font-size: 13px; font-weight: 500;\">Online Audit Application Portal</p>
            </div>
            <div style=\"padding: 28px 32px; color: #334155; font-size: 15px; line-height: 1.6;\">
                <p style=\"margin-top: 0; font-size: 16px; color: #0f172a;\">Dear <strong>{$contact_person}</strong>,</p>
                <p>Greetings from AITF Management Certification.</p>
                <p>Your One-Time Password (OTP) to verify your email address for the Online Audit Application Form submitted on behalf of <strong>{$org_name}</strong> is:</p>
                <div style=\"text-align: center; margin: 26px 0;\">
                    <div style=\"display: inline-block; background: #f0f9ff; border: 2px dashed #0284c7; border-radius: 10px; padding: 14px 34px;\">
                        <span style=\"font-family: 'Courier New', Courier, monospace; font-size: 32px; font-weight: 800; color: #0284c7; letter-spacing: 7px; display: block;\">{$otp}</span>
                    </div>
                    <p style=\"margin: 8px 0 0 0; font-size: 12.5px; color: #64748b;\">(This OTP is valid for 10 minutes)</p>
                </div>
                <p style=\"font-size: 14px; color: #475569;\">Please enter this OTP in the Application Form to complete verification and proceed with your application submission.</p>
                <p style=\"font-size: 13px; color: #94a3b8; margin-bottom: 0;\">This OTP should not be shared with anyone. If you did not request this OTP, please ignore this email.</p>
            </div>
            <div style=\"background: #f1f5f9; padding: 18px 28px; border-top: 1px solid #e2e8f0; font-size: 12.5px; color: #64748b; text-align: center; line-height: 1.5;\">
                <strong>AITF Management Certification Limited</strong><br>
                Talawade, Pune - 411 062, Maharashtra, India<br>
                Website: <a href=\"https://www.aitf.co.in\" style=\"color: #0284c7; text-decoration: none;\">www.aitf.co.in</a> | Email: <a href=\"mailto:aitfmcl@gmail.com\" style=\"color: #0284c7; text-decoration: none;\">aitfmcl@gmail.com</a>
            </div>
        </div>
    </div>";

    $emailSender->sendEmail($email, $contact_person, $body, $subject);
    echo json_encode(['success' => true, 'message' => 'OTP sent successfully to your email. Valid for 10 minutes.']);
} catch (Exception $e) {
    error_log("OTP Email Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP email. Please check your email address and try again.']);
}

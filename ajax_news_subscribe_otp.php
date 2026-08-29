require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/news_helper.php';
require_once __DIR__ . '/testemail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit;
}

// Rate limiting: 60s cooldown
if (isset($_SESSION['news_sub_otp_last_sent']) && (time() - $_SESSION['news_sub_otp_last_sent']) < 60) {
    $remaining = 60 - (time() - $_SESSION['news_sub_otp_last_sent']);
    echo json_encode(['success' => false, 'message' => "Please wait {$remaining} seconds before requesting a new OTP."]);
    exit;
}

try {
    if ($db) {
        aitf_ensure_news_schema($db);
    }
    // Check if already verified
    $checkStmt = $db->prepare("SELECT id, status FROM news_subscribers WHERE email = :email LIMIT 1");
    $checkStmt->execute([':email' => $email]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing && $existing['status'] === 'verified') {
        echo json_encode(['success' => false, 'message' => 'This email is already subscribed to AITF News & Circulars!']);
        exit;
    }

    $otp = sprintf("%06d", random_int(100000, 999999));
    $otpExpiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if ($existing) {
        $updateStmt = $db->prepare("UPDATE news_subscribers SET verification_otp = :otp, otp_expiry = :expiry, status = 'pending', ip_address = :ip WHERE id = :id");
        $updateStmt->execute([':otp' => $otp, ':expiry' => $otpExpiry, ':ip' => $ip, ':id' => $existing['id']]);
    } else {
        $insertStmt = $db->prepare("INSERT INTO news_subscribers (email, verification_otp, otp_expiry, status, ip_address) VALUES (:email, :otp, :expiry, 'pending', :ip)");
        $insertStmt->execute([':email' => $email, ':otp' => $otp, ':expiry' => $otpExpiry, ':ip' => $ip]);
    }

    $_SESSION['news_sub_otp'] = $otp;
    $_SESSION['news_sub_otp_email'] = $email;
    $_SESSION['news_sub_otp_time'] = time();
    $_SESSION['news_sub_otp_last_sent'] = time();

    // Send email
    $emailSender = new AITFServiceEmailSender();
    $subject = "OTP Verification – Subscribe to AITF News & Circulars";
    $body = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff;'>
        <div style='text-align: center; margin-bottom: 20px;'>
            <h2 style='color: #053c6d; margin: 0;'>AITF Management Certification</h2>
            <p style='color: #64748b; font-size: 14px; margin: 5px 0;'>Official News & Circulars Subscription</p>
        </div>
        <p style='font-size: 15px; color: #1e293b;'>Hello,</p>
        <p style='font-size: 14px; color: #334155; line-height: 1.6;'>
            Thank you for your interest in subscribing to AITF News, Circulars, and Process Updates. 
            Use the One-Time Password (OTP) below to verify your email address:
        </p>
        <div style='text-align: center; margin: 25px 0;'>
            <span style='display: inline-block; font-size: 28px; font-weight: bold; letter-spacing: 6px; color: #0b5b94; background: #eff6ff; padding: 12px 28px; border-radius: 8px; border: 1px dashed #3b82f6;'>
                {$otp}
            </span>
        </div>
        <p style='font-size: 13px; color: #64748b; line-height: 1.5;'>
            This OTP is valid for <strong>10 minutes</strong>. Please do not share this OTP with anyone.<br>
            Once verified, you will receive real-time notifications about new accreditation circulars, process updates, and regulatory guidelines.
        </p>
        <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
        <p style='font-size: 12px; color: #94a3b8; text-align: center;'>
            © " . date('Y') . " AITF Management Certification Limited. All rights reserved.
        </p>
    </div>";

    $emailSender->sendEmail($email, 'Subscriber', $body, $subject);
    echo json_encode(['success' => true, 'message' => 'OTP sent successfully to ' . htmlspecialchars($email) . '. Valid for 10 minutes.']);
} catch (Exception $e) {
    error_log("News Subscribe OTP Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP email. Please try again later.']);
}

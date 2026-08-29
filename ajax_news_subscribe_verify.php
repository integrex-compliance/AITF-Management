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
$otp = trim($_POST['otp'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit;
}

if (empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid 6-digit OTP.']);
    exit;
}

try {
    if ($db) {
        aitf_ensure_news_schema($db);
    }
    $stmt = $db->prepare("SELECT id, status, verification_otp, otp_expiry FROM news_subscribers WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$subscriber) {
        echo json_encode(['success' => false, 'message' => 'No subscription request found for this email. Please request an OTP first.']);
        exit;
    }

    if ($subscriber['status'] === 'verified') {
        echo json_encode(['success' => true, 'message' => 'Your email is already verified and subscribed!']);
        exit;
    }

    // Check expiry
    if (!empty($subscriber['otp_expiry']) && strtotime($subscriber['otp_expiry']) < time()) {
        echo json_encode(['success' => false, 'message' => 'The OTP has expired. Please request a new one.']);
        exit;
    }

    // Verify OTP (check DB and session)
    $validInDb = (!empty($subscriber['verification_otp']) && $subscriber['verification_otp'] === $otp);
    $validInSession = (isset($_SESSION['news_sub_otp']) && $_SESSION['news_sub_otp'] === $otp && ($_SESSION['news_sub_otp_email'] ?? '') === $email);

    if (!$validInDb && !$validInSession) {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP. Please check the code and try again.']);
        exit;
    }

    // Mark verified
    $now = date('Y-m-d H:i:s');
    $upStmt = $db->prepare("UPDATE news_subscribers SET status = 'verified', verified_at = :now, verification_otp = NULL, otp_expiry = NULL WHERE id = :id");
    $upStmt->execute([':now' => $now, ':id' => $subscriber['id']]);

    // Clean session
    unset($_SESSION['news_sub_otp'], $_SESSION['news_sub_otp_email'], $_SESSION['news_sub_otp_time']);

    // Send confirmation welcome email
    try {
        $emailSender = new AITFServiceEmailSender();
        $subject = "Subscription Confirmed – AITF News & Circulars";
        $body = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #053c6d; margin: 0;'>AITF Management Certification</h2>
                <p style='color: #16a34a; font-weight: bold; font-size: 14px; margin: 5px 0;'>✓ Email Verified &amp; Subscribed Successfully</p>
            </div>
            <p style='font-size: 15px; color: #1e293b;'>Hello,</p>
            <p style='font-size: 14px; color: #334155; line-height: 1.6;'>
                You have successfully subscribed to <strong>AITF News, Circulars, and Process Updates</strong>. 
                Whenever AITF releases a new circular, policy update, or accreditation advisory, you will automatically receive an email alert directly in your inbox.
            </p>
            <div style='background: #f8fafc; padding: 15px; border-radius: 6px; border-left: 4px solid #0b5b94; margin: 20px 0;'>
                <p style='margin: 0; font-size: 13px; color: #475569;'>
                    <strong>Subscribed Email:</strong> {$email}<br>
                    <strong>Date:</strong> " . date('d M Y, h:i A') . "
                </p>
            </div>
            <p style='font-size: 13px; color: #64748b;'>
                You can explore current circulars and articles on our <a href='https://www.aitf.co.in/news.php' style='color: #0b5b94; font-weight: bold;'>News &amp; Updates Page</a>.
            </p>
            <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
            <p style='font-size: 12px; color: #94a3b8; text-align: center;'>
                © " . date('Y') . " AITF Management Certification Limited.
            </p>
        </div>";

        $emailSender->sendEmail($email, 'Subscriber', $body, $subject);
    } catch (Exception $mailEx) {
        error_log("Confirmation Email Error: " . $mailEx->getMessage());
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Congratulations! Your email has been verified and you are now subscribed to AITF News & Circulars.'
    ]);
} catch (Exception $e) {
    error_log("News Subscribe Verify Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Verification failed. Please try again.']);
}

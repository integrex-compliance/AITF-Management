<?php
require_once __DIR__ . '/include/env_helper.php';
$turnstileHelperPath = __DIR__ . '/include/turnstile_helper.php';
if (file_exists($turnstileHelperPath)) {
    require_once $turnstileHelperPath;
}
$turnstileConfig = function_exists('aitf_get_turnstile_config') ? aitf_get_turnstile_config() : [
    'site_key' => '',
    'secret_key' => ''
];
$turnstileReady = function_exists('aitf_is_turnstile_ready') ? aitf_is_turnstile_ready() : false;

$successMsg = '';
$errorMsg = '';
$formData = [
    'name' => '',
    'email' => '',
    'mobile' => '',
    'company' => '',
    'about' => '',
    'services' => []
];
$serviceOptions = [
    'ISO 9001',
    'ISO 45001',
    'ISO 14001'
];
$mailConfig = aitf_get_mail_config([
    'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Website')
]);
$mailFromEmailHtml = htmlspecialchars($mailConfig['from_email'], ENT_QUOTES, 'UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['mobile'] = trim($_POST['mobile'] ?? '');
    $companyPost = isset($_POST['company']) ? $_POST['company'] : '';
    $formData['company'] = is_string($companyPost) ? trim($companyPost) : '';
    $formData['about'] = trim($_POST['about'] ?? '');
    $formData['services'] = isset($_POST['services']) && is_array($_POST['services'])
        ? array_values(array_intersect($serviceOptions, $_POST['services']))
        : [];
    $turnstileToken = trim($_POST['cf-turnstile-response'] ?? '');

    if (
        $formData['name'] === '' ||
        $formData['email'] === '' ||
        $formData['mobile'] === '' ||
        $formData['company'] === '' ||
        $formData['about'] === '' ||
        empty($formData['services'])
    ) {
        $errorMsg = 'Please fill in all required fields (name, email, mobile, company, about, services).';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[0-9]{10,15}$/', preg_replace('/\D/', '', $formData['mobile']))) {
        $errorMsg = 'Please enter a valid mobile number (10-15 digits).';
    } elseif (function_exists('aitf_verify_turnstile') && !aitf_verify_turnstile($turnstileToken, $errorMsg)) {
        if ($errorMsg === '') {
            $errorMsg = 'Captcha verification failed. Please try again.';
        }
    } else {
        require __DIR__ . '/PHPMailer/PHPMailerAutoload.php';

        $fromEmail = $mailConfig['from_email'] ?: 'no-reply@localhost';

        // GoDaddy shared hosting relay: localhost:25, no auth, no SSL
        $attempts = [
            ['host' => 'localhost', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false],
            ['host' => 'relay-hosting.secureserver.net', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false],
            [
                'host' => $mailConfig['host'],
                'port' => $mailConfig['port'],
                'secure' => $mailConfig['secure'],
                'autotls' => $mailConfig['secure'] === 'tls',
                'auth' => ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== ''
            ],
        ];

        $applySmtpConfig = function (PHPMailer $mailer, array $cfg) use ($mailConfig) {
            $mailer->isSMTP();
            $mailer->SMTPDebug = 0; // keep logs clean on production
            $mailer->Debugoutput = 'error_log';
            $mailer->Host = $cfg['host'];
            $mailer->Hostname = $cfg['host'];
            $mailer->Port = $cfg['port'];
            $mailer->SMTPAuth = $cfg['auth'];
            $mailer->SMTPSecure = $cfg['secure'] ?: false;
            $mailer->SMTPAutoTLS = $cfg['autotls'];
            if ($cfg['auth']) {
                $mailer->Username = $mailConfig['username'];
                $mailer->Password = $mailConfig['password'];
            }
            $mailer->CharSet = 'UTF-8';
            $mailer->Timeout = 8;
            $mailer->SMTPKeepAlive = false;
            $mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
        };

        $mail = new PHPMailer;
        $mail->setFrom($fromEmail, $mailConfig['from_name']);
        $mail->Sender = $fromEmail;
        $mail->addCustomHeader('Return-Path', $fromEmail);
        $mail->addReplyTo($formData['email'], $formData['name']);
        $mail->addAddress($mailConfig['admin_email'], 'AITF Certification');
        $mail->Subject = 'New Contact Enquiry from Website';
        $servicesText = implode(', ', $formData['services']);
        $mobileClean = preg_replace('/\D+/', '', $formData['mobile']);
        $body = "
            <h3>New enquiry received from website contact form</h3>
            <p><strong>Name:</strong> {$formData['name']}<br>
            <strong>Email:</strong> {$formData['email']}<br>
            <strong>Mobile:</strong> {$mobileClean}<br>
            <strong>Company:</strong> {$formData['company']}<br>
            <strong>Services Interested:</strong> {$servicesText}</p>
            <p><strong>About the client / requirement:</strong><br>" . nl2br(htmlspecialchars($formData['about'], ENT_QUOTES, 'UTF-8')) . "</p>
        ";
        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->AltBody = "Name: {$formData['name']}\nEmail: {$formData['email']}\nMobile: {$mobileClean}\nCompany: {$formData['company']}\nServices: {$servicesText}\nAbout:\n{$formData['about']}";

        $primaryOk = false;
        $primaryErrs = [];
        $successfulAttempt = null;
        foreach ($attempts as $attempt) {
            $applySmtpConfig($mail, $attempt);
            $primaryOk = $mail->send();
            if ($primaryOk) {
                $successfulAttempt = $attempt;
                break;
            }
            $errLine = '[' . $attempt['host'] . ':' . $attempt['port'] . ' secure=' . ($attempt['secure'] ?: 'none') . ' auth=' . ($attempt['auth'] ? 'yes' : 'no') . '] ' . $mail->ErrorInfo;
            $primaryErrs[] = $errLine;
            error_log('CONTACT MAIL SEND FAIL: ' . $errLine);
            $mail->smtpClose();
        }
        if (!$primaryOk) {
            $errorMsg = 'Mail error: ' . implode(' | ', array_filter($primaryErrs));
        }

        if ($primaryOk) {
            // Send thank-you to sender
            $confirm = new PHPMailer;
            $applySmtpConfig($confirm, $successfulAttempt ?: $attempts[0]);
            $confirm->setFrom($fromEmail, 'AITF Team');
            $confirm->Sender = $fromEmail;
            $confirm->addCustomHeader('Return-Path', $fromEmail);
            $confirm->addAddress($formData['email'], $formData['name']);
            $confirm->Subject = 'Thank you for contacting AITF';
            $confirmBody = "
                <p>Dear {$formData['name']},</p>
                <p>Thank you for contacting AITF.</p>
                <p>We acknowledge receipt of your enquiry and appreciate your interest in our assessment and certification services. Our team is currently reviewing the details shared by you and will contact you shortly with the relevant information or next steps.</p>
                <p>If you have any additional details to provide or require urgent assistance, please feel free to reply to this email.</p>
                <p>Thank you for considering AITF as your assessment and certification partner.</p>
                <p>Warm regards,<br>
                AITF Team<br>
                Client Support Desk<br>
                AITF – Assessment &amp; Institute for Technical Forum<br>
                Email: <a href=\"mailto:{$mailFromEmailHtml}\">{$mailFromEmailHtml}</a><br>
                Website: <a href=\"http://www.aitf.co.in\" target=\"_blank\" rel=\"noopener\">www.aitf.co.in</a></p>
            ";
            $confirm->isHTML(true);
            $confirm->Body = $confirmBody;
            $confirm->AltBody = "Dear {$formData['name']},\n\nThank you for contacting AITF.\n\nWe acknowledge receipt of your enquiry and appreciate your interest in our assessment and certification services. Our team is currently reviewing the details shared by you and will contact you shortly with the relevant information or next steps.\n\nIf you have any additional details to provide or require urgent assistance, please feel free to reply to this email.\n\nThank you for considering AITF as your assessment and certification partner.\n\nWarm regards,\nAITF Team\nClient Support Desk\nAITF - Assessment & Institute for Technical Forum\nEmail: {$mailConfig['from_email']}\nWebsite: www.aitf.co.in";
            $confirmOk = $confirm->send();

            $successMsg = $confirmOk /*
        if ($errorMsg === '') {
            $errorMsg = 'Captcha verification failed. Please try again.';
        }
    } else {
        require __DIR__ . '/PHPMailer/PHPMailerAutoload.php';

        $fromEmail = $mailConfig['from_email'] ?: 'no-reply@localhost';

        // GoDaddy shared hosting relay: localhost:25, no auth, no SSL
        $attempts = [
            ['host' => 'localhost', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false],
            ['host' => 'relay-hosting.secureserver.net', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false],
            [
                'host' => $mailConfig['host'],
                'port' => $mailConfig['port'],
                'secure' => $mailConfig['secure'],
                'autotls' => $mailConfig['secure'] === 'tls',
                'auth' => ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== ''
            ],
        ];

        $applySmtpConfig = function (PHPMailer $mailer, array $cfg) use ($mailConfig) {
            $mailer->isSMTP();
            $mailer->SMTPDebug = 0; // keep logs clean on production
            $mailer->Debugoutput = 'error_log';
            $mailer->Host = $cfg['host'];
            $mailer->Hostname = $cfg['host'];
            $mailer->Port = $cfg['port'];
            $mailer->SMTPAuth = $cfg['auth'];
            $mailer->SMTPSecure = $cfg['secure'] ?: false;
            $mailer->SMTPAutoTLS = $cfg['autotls'];
            if ($cfg['auth']) {
                $mailer->Username = $mailConfig['username'];
                $mailer->Password = $mailConfig['password'];
            }
            $mailer->CharSet = 'UTF-8';
            $mailer->Timeout = 8;
            $mailer->SMTPKeepAlive = false;
            $mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
        };

        $mail = new PHPMailer;
        $mail->setFrom($fromEmail, $mailConfig['from_name']);
        $mail->Sender = $fromEmail;
        $mail->addCustomHeader('Return-Path', $fromEmail);
        $mail->addReplyTo($formData['email'], $formData['name']);
        $mail->addAddress($mailConfig['admin_email'], 'AITF Certification');
        $mail->Subject = 'New Contact Enquiry from Website';
        $servicesText = implode(', ', $formData['services']);
        $mobileClean = preg_replace('/\D+/', '', $formData['mobile']);
        $body = "
            <h3>New enquiry received from website contact form</h3>
            <p><strong>Name:</strong> {$formData['name']}<br>
            <strong>Email:</strong> {$formData['email']}<br>
            <strong>Mobile:</strong> {$mobileClean}<br>
            <strong>Company:</strong> {$formData['company']}<br>
            <strong>Services Interested:</strong> {$servicesText}</p>
            <p><strong>About the client / requirement:</strong><br>" . nl2br(htmlspecialchars($formData['about'], ENT_QUOTES, 'UTF-8')) . "</p>
        ";
        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->AltBody = "Name: {$formData['name']}\nEmail: {$formData['email']}\nMobile: {$mobileClean}\nCompany: {$formData['company']}\nServices: {$servicesText}\nAbout:\n{$formData['about']}";

        $primaryOk = false;
        $primaryErrs = [];
        $successfulAttempt = null;
        foreach ($attempts as $attempt) {
            $applySmtpConfig($mail, $attempt);
            $primaryOk = $mail->send();
            if ($primaryOk) {
                $successfulAttempt = $attempt;
                break;
            }
            $errLine = '[' . $attempt['host'] . ':' . $attempt['port'] . ' secure=' . ($attempt['secure'] ?: 'none') . ' auth=' . ($attempt['auth'] ? 'yes' : 'no') . '] ' . $mail->ErrorInfo;
            $primaryErrs[] = $errLine;
            error_log('CONTACT MAIL SEND FAIL: ' . $errLine);
            $mail->smtpClose();
        }
        if (!$primaryOk) {
            $errorMsg = 'Mail error: ' . implode(' | ', array_filter($primaryErrs));
        }

        if ($primaryOk) {
            // Send thank-you to sender
            $confirm = new PHPMailer;
            $applySmtpConfig($confirm, $successfulAttempt ?: $attempts[0]);
            $confirm->setFrom($fromEmail, 'AITF Team');
            $confirm->Sender = $fromEmail;
            $confirm->addCustomHeader('Return-Path', $fromEmail);
            $confirm->addAddress($formData['email'], $formData['name']);
            $confirm->Subject = 'Thank you for contacting AITF';
            $confirmBody = "
                <p>Dear {$formData['name']},</p>
                <p>Thank you for contacting AITF.</p>
                <p>We acknowledge receipt of your enquiry and appreciate your interest in our assessment and certification services. Our team is currently reviewing the details shared by you and will contact you shortly with the relevant information or next steps.</p>
                <p>If you have any additional details to provide or require urgent assistance, please feel free to reply to this email.</p>
                <p>Thank you for considering AITF as your assessment and certification partner.</p>
                <p>Warm regards,<br>
                AITF Team<br>
                Client Support Desk<br>
                AITF – Assessment &amp; Institute for Technical Forum<br>
                Email: <a href=\"mailto:{$mailFromEmailHtml}\">{$mailFromEmailHtml}</a><br>
                Website: <a href=\"http://www.aitf.co.in\" target=\"_blank\" rel=\"noopener\">www.aitf.co.in</a></p>
            ";
            $confirm->isHTML(true);
            $confirm->Body = $confirmBody;
            $confirm->AltBody = "Dear {$formData['name']},\n\nThank you for contacting AITF.\n\nWe acknowledge receipt of your enquiry and appreciate your interest in our assessment and certification services. Our team is currently reviewing the details shared by you and will contact you shortly with the relevant information or next steps.\n\nIf you have any additional details to provide or require urgent assistance, please feel free to reply to this email.\n\nThank you for considering AITF as your assessment and certification partner.\n\nWarm regards,\nAITF Team\nClient Support Desk\nAITF - Assessment & Institute for Technical Forum\nEmail: {$mailConfig['from_email']}\nWebsite: www.aitf.co.in";
            $confirmOk = $confirm->send();
*/
                ? 'Thank you! We will get back to you shortly.'
                : 'Thank you! We received your enquiry, but could not send confirmation to your email.';
            $formData = ['name' => '', 'email' => '', 'mobile' => '', 'company' => '', 'about' => '', 'services' => []];
        } else {
            $errorMsg = 'Mail error: ' . $mail->ErrorInfo;
        }
    }
}
$pageTitle = "Contact Us | AITF Management Certification";
$pageDescription = "Get in touch with AITF for ISO certification, queries, or assistance. We are here to help you with Quality Management and Environmental Management certifications.";
$canonicalUrl = "https://www.aitf.co.in/contactUS.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include './headSource.php'; ?>
    <?php if ($turnstileReady): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Contact Us - AITF Management Certification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0f1419;
            color: #e7e9ea;
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Hero Section */
        .contact-hero {
            background: linear-gradient(180deg, #1a2332 0%, #0f1419 100%);
            padding: 120px 20px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .contact-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(ellipse at center, rgba(29, 155, 240, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .hero-content p {
            font-size: 1.25rem;
            color: #8899a6;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Quick Contact Strip */
        .quick-strip {
            background: #192734;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 0;
        }

        .quick-strip-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: center;
            gap: 60px;
            flex-wrap: wrap;
        }

        .quick-item {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: #e7e9ea;
            transition: all 0.3s ease;
        }

        .quick-item:hover {
            color: #1d9bf0;
        }

        .quick-item i {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #1d9bf0, #1a8cd8);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }

        .quick-item span {
            font-size: 0.85rem;
            color: #8899a6;
            display: block;
        }

        .quick-item strong {
            font-size: 1rem;
            font-weight: 600;
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        /* Contact Grid */
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        @media (max-width: 900px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Cards */
        .card {
            background: #192734;
            border-radius: 20px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(4px);
        }

        .card:hover {
            border-color: rgba(29, 155, 240, 0.3);
            transform: translateY(-5px);
            box-shadow: 0 16px 36px rgba(29, 155, 240, 0.18);
        }

        .alert {
            margin-bottom: 20px;
            padding: 15px 18px;
            border-radius: 10px;
            font-weight: 600;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.12);
            border: 1px solid rgba(76, 175, 80, 0.4);
            color: #a5e1b6;
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.12);
            border: 1px solid rgba(244, 67, 54, 0.4);
            color: #f28b82;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #1d9bf0, #1a8cd8);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
        }

        .card-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
        }

        /* Contact Items */
        .contact-item {
            display: flex;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .contact-item:last-child {
            border-bottom: none;
        }

        .contact-item:hover {
            padding-left: 10px;
        }

        .contact-item-icon {
            width: 50px;
            height: 50px;
            background: rgba(29, 155, 240, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1d9bf0;
            font-size: 20px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .contact-item:hover .contact-item-icon {
            background: #1d9bf0;
            color: white;
        }

        .contact-item-content h4 {
            font-size: 0.9rem;
            color: #8899a6;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .contact-item-content p,
        .contact-item-content a {
            font-size: 1rem;
            color: #e7e9ea;
            text-decoration: none;
            line-height: 1.6;
        }

        .contact-item-content a:hover {
            color: #1d9bf0;
        }

        /* Business Hours */
        .hours-section {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .hours-section h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            color: #ffffff;
            margin-bottom: 20px;
        }

        .hours-section h3 i {
            color: #1d9bf0;
        }

        .hours-list {
            list-style: none;
        }

        .hours-list li {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.95rem;
        }

        .hours-list li:last-child {
            border-bottom: none;
        }

        .hours-list li span:first-child {
            color: #8899a6;
        }

        .hours-list li span:last-child {
            color: #e7e9ea;
            font-weight: 500;
        }

        /* Email Card */
        .email-card {
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .email-icon-large {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #1d9bf0, #1a8cd8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
            color: white;
            box-shadow: 0 20px 40px rgba(29, 155, 240, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .email-card h3 {
            font-size: 1.8rem;
            color: #ffffff;
            margin-bottom: 15px;
        }

        .email-card p {
            color: #8899a6;
            font-size: 1rem;
            max-width: 350px;
            margin: 0 auto 30px;
        }

        .email-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #1d9bf0, #1a8cd8);
            color: white;
            padding: 16px 40px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(29, 155, 240, 0.3);
            position: relative;
            z-index: 100;
        }

        .email-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(29, 155, 240, 0.4);
            color: white;
        }

        .email-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 25px;
            color: #8899a6;
            font-size: 0.9rem;
        }

        .email-note i {
            color: #1d9bf0;
        }

        /* Social Links */
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 35px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .social-link {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8899a6;
            font-size: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: #1d9bf0;
            color: white;
            transform: translateY(-3px);
        }

        /* Map Section */
        .map-section {
            margin-top: 60px;
        }

        .map-card {
            background: #192734;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .map-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .map-header h3 {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.3rem;
            color: #ffffff;
        }

        .map-header h3 i {
            color: #1d9bf0;
        }

        .map-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(29, 155, 240, 0.15);
            color: #1d9bf0;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .map-btn:hover {
            background: #1d9bf0;
            color: white;
        }

        .map-container {
            border-radius: 15px;
            overflow: hidden;
            height: 350px;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Form */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
            width: 100%;
        }

        .form-grid.full {
            grid-template-columns: 1fr;
        }

        .form-control {
            width: 100%;
            background: #111820;
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #e7e9ea;
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.35);
        }

        .form-control:focus {
            outline: none;
            border-color: #1d9bf0;
            box-shadow: 0 0 0 3px rgba(29, 155, 240, 0.15);
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #cfd3d7;
            font-weight: 600;
        }

        .services-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 8px;
        }

        .service-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 10px 12px;
            color: #e7e9ea;
            cursor: pointer;
            transition: border-color 0.25s ease, transform 0.2s ease, box-shadow 0.25s ease;
        }

        .service-chip:hover {
            border-color: #1d9bf0;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(29, 155, 240, 0.15);
        }

        .service-chip input {
            accent-color: #1d9bf0;
            width: 18px;
            height: 18px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #1d9bf0, #1a8cd8);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: transform 0.25s ease, box-shadow 0.25s ease, filter 0.25s ease;
            box-shadow: 0 12px 30px rgba(29, 155, 240, 0.35);
        }

        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 16px 36px rgba(29, 155, 240, 0.45);
            filter: brightness(1.05);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            filter: grayscale(0.12);
            box-shadow: 0 8px 18px rgba(29, 155, 240, 0.18);
        }

        .form-note {
            color: #8899a6;
            font-size: 0.95rem;
            margin-top: 10px;
            text-align: center;
        }

        .captcha-wrap {
            width: 100%;
            margin-top: 18px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
        }

        .captcha-wrap .cf-turnstile {
            max-width: 100%;
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .captcha-wrap .cf-turnstile>div {
            max-width: 100% !important;
            margin: 0 auto;
        }

        .captcha-help {
            font-size: 0.88rem;
            color: #8899a6;
            line-height: 1.5;
            text-align: center;
        }

        .captcha-status {
            display: none;
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(255, 193, 7, 0.12);
            border: 1px solid rgba(255, 193, 7, 0.28);
            color: #ffd166;
            font-size: 0.9rem;
            font-weight: 600;
            text-align: center;
        }

        .captcha-status.show {
            display: block;
        }

        /* Submit overlay */
        .submit-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 20, 25, 0.72);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 16px;
            color: #e7e9ea;
            font-weight: 600;
            letter-spacing: 0.2px;
            z-index: 2000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .submit-overlay.show {
            opacity: 1;
            pointer-events: all;
        }

        .loader-ring {
            width: 54px;
            height: 54px;
            border: 4px solid rgba(255, 255, 255, 0.16);
            border-top-color: #1d9bf0;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.04);
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Floating Contact */
        .floating-contact {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 15px;
        }

        .floating-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }

        .floating-contact.active .floating-options {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .floating-option {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            text-decoration: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .floating-option:hover {
            transform: scale(1.1);
        }

        .floating-option.whatsapp {
            background: #25D366;
        }

        .floating-option.phone {
            background: #1d9bf0;
        }

        .floating-option.email {
            background: #EA4335;
        }

        .floating-btn {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1d9bf0, #1a8cd8);
            border-radius: 50%;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(29, 155, 240, 0.4);
            transition: all 0.3s ease;
        }

        .floating-btn:hover {
            transform: scale(1.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .quick-strip-inner {
                gap: 30px;
            }

            .card {
                padding: 30px;
            }

            .email-card h3 {
                font-size: 1.5rem;
            }

            .contact-hero {
                padding: 100px 20px 60px;
            }
        }

        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .quick-strip-inner {
                flex-direction: column;
                gap: 20px;
                align-items: center;
            }

            .main-container {
                padding: 40px 15px;
            }

            .card {
                padding: 25px;
            }

            .services-list {
                grid-template-columns: 1fr;
            }

            .captcha-wrap {
                align-items: center;
            }

            .captcha-wrap .cf-turnstile {
                transform: scale(0.92);
                transform-origin: center top;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Hero Section -->
    <section class="contact-hero" style="margin-top: 0; padding-top: 140px;">
        <div class="hero-content">
            <h1>Lets Start Conversation</h1>
            <p>We're here to answer any questions you might have. Reach out and we'll respond as soon as
                possible.</p>
        </div>
    </section>

    <!-- Quick Contact Strip -->
    <div class="quick-strip">
        <div class="quick-strip-inner">
            <a href="tel:+919881691012" class="quick-item" onclick="window.location.href='tel:+919881691012';">
                <i class="fas fa-phone-alt"></i>
                <div>
                    <span>Call Us</span>
                    <strong>+91 9881691012</strong>
                </div>
            </a>
            <a href="mailto:<?php echo $mailFromEmailHtml; ?>" target="_blank" class="quick-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <span>Email Us</span>
                    <strong><?php echo $mailFromEmailHtml; ?></strong>
                </div>
            </a>
            <a href="https://wa.me/919881691012" class="quick-item" target="_blank">
                <i class="fab fa-whatsapp"></i>
                <div>
                    <span>WhatsApp</span>
                    <strong>Chat Now</strong>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-container">
        <?php if ($successMsg): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php elseif ($errorMsg): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="contact-grid">
            <!-- Contact Information -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-address-card"></i>
                    </div>
                    <h2>Contact Information</h2>
                </div>

                <div class="contact-item">
                    <div class="contact-item-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-item-content">
                        <h4>Our Location</h4>
                        <p>Devi Indrayani Wing B 26,<br>Gat No. 382+3+4/1, Near IT Park,<br>Talwade, Pune - 411
                            062<br>Maharashtra, India</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-item-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="contact-item-content">
                        <h4>Phone Number</h4>
                        <a href="tel:+919881691012" onclick="window.location.href='tel:+919881691012';">+91
                            9881691012</a>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-item-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-item-content">
                        <h4>Email Address</h4>
                        <a href="mailto:<?php echo $mailFromEmailHtml; ?>"
                            target="_blank"><?php echo $mailFromEmailHtml; ?></a>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-item-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div class="contact-item-content">
                        <h4>Website</h4>
                        <a href="https://www.aitf.co.in" target="_blank">www.aitf.co.in</a>
                    </div>
                </div>

                <div class="hours-section">
                    <h3><i class="far fa-clock"></i> Business Hours</h3>
                    <ul class="hours-list">
                        <li><span>Friday - Wednesday</span><span>9:30 AM - 6:30 PM</span></li>
                        <li><span>Thursday</span><span>9:30 AM - 2:30 PM</span></li>
                    </ul>
                </div>
            </div>

            <!-- Email Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <h2>Contact Form</h2>
                </div>
                <form method="post" id="contact-form">
                    <div class="form-grid">
                        <div>
                            <label class="form-label" for="name">Name*</label>
                            <input class="form-control" type="text" id="name" name="name"
                                value="<?php echo htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                        <div>
                            <label class="form-label" for="email">Email*</label>
                            <input class="form-control" type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                        <div>
                            <label class="form-label" for="company">Company Name*</label>
                            <input class="form-control" type="text" id="company" name="company"
                                value="<?php echo htmlspecialchars($formData['company'], ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                        <div>
                            <label class="form-label" for="mobile">Mobile Number*</label>
                            <input class="form-control" type="text" id="mobile" name="mobile"
                                value="<?php echo htmlspecialchars($formData['mobile'], ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="+91 9881691012" required>
                        </div>
                        <div>
                            <label class="form-label"> Select Services Interested In*</label>
                            <div class="services-list">
                                <?php foreach ($serviceOptions as $service): ?>
                                    <label class="service-chip">
                                        <input type="checkbox" name="services[]"
                                            value="<?php echo htmlspecialchars($service, ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array($service, $formData['services']) ? 'checked' : ''; ?>>
                                        <span>
                                            <?php echo htmlspecialchars($service, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="form-grid full">
                            <div>
                                <label class="form-label" for="about">Tell us about yourself / your requirement*</label>
                                <textarea class="form-control" id="about" name="about" rows="5"
                                    required><?php echo htmlspecialchars($formData['about'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 18px;">
                        <div class="captcha-wrap">
                            <?php if ($turnstileReady): ?>
                                <div class="cf-turnstile"
                                    data-sitekey="<?php echo htmlspecialchars($turnstileConfig['site_key'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-callback="contactCaptchaSolved"
                                    data-expired-callback="contactCaptchaExpired"
                                    data-error-callback="contactCaptchaExpired">
                                </div>
                            <?php else: ?>
                                <div class="captcha-help">Captcha widget will appear after Cloudflare Turnstile keys are added
                                    in the `.env` file.</div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="submit-btn">Send Message</button>
                        <div class="captcha-status" id="contact-captcha-status">Please complete the captcha to enable
                            the submit button.</div>
                        <div class="form-note">You will see a confirmation once your enquiry is sent. We typically
                            respond within 24 hours.</div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Map Section -->
        <div class="map-section">
            <div class="map-card">
                <div class="map-header">
                    <h3><i class="fas fa-map-marked-alt"></i> Find Us on Map</h3>
                    <a href="https://maps.app.goo.gl/4iXYZXQyoWEsnAcs5" target="_blank" class="map-btn">
                        <i class="fas fa-external-link-alt"></i>
                        Open in Google Maps
                    </a>
                </div>
                <div class="map-container">
                    <iframe
                        src="https://www.google.com/maps?q=Devi+Indrayani+Wing+B+26,+Gat+No.+382+3+4/1,+Near+IT+Park,+Talwade,+Pune+411062&output=embed"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </main>

    <!-- Floating Contact Button -->
    <div class="floating-contact" id="floatingContact">
        <div class="floating-options">
            <a href="https://wa.me/919881691012" class="floating-option whatsapp" target="_blank" title="WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
            <a href="tel:+919881691012" class="floating-option phone" title="Call"
                onclick="window.location.href='tel:+919881691012';">
                <i class="fas fa-phone-alt"></i>
            </a>
            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=aitfindia@gmail.com" class="floating-option email"
                title="Email" target="_blank">
                <i class="fas fa-envelope"></i>
            </a>
        </div>
        <button class="floating-btn" id="floatingBtn">
            <i class="fas fa-comment-dots"></i>
        </button>
    </div>

    <div class="submit-overlay" id="submitOverlay" aria-live="polite" aria-busy="true">
        <div class="loader-ring" aria-hidden="true"></div>
        <div>Sending your query to AITF...</div>
    </div>

    <?php include './footerSource.php'; ?>

    <script>
        const turnstileEnabled = <?php echo json_encode($turnstileReady); ?>;

        // Floating contact toggle
        const floatingBtn = document.getElementById('floatingBtn');
        const floatingContact = document.getElementById('floatingContact');

        floatingBtn.addEventListener('click', () => {
            floatingContact.classList.toggle('active');
        });

        // Close on outside click - but not for email button
        document.addEventListener('click', (e) => {
            if (!floatingContact.contains(e.target) && !e.target.closest('.email-btn')) {
                floatingContact.classList.remove('active');
            }
        });

        // Show loader on submit
        const contactForm = document.getElementById('contact-form');
        const submitOverlay = document.getElementById('submitOverlay');
        const submitBtn = document.querySelector('.submit-btn');
        const captchaStatus = document.getElementById('contact-captcha-status');
        const captchaBox = contactForm ? contactForm.querySelector('.cf-turnstile') : null;
        const requiresVisibleCaptcha = Boolean(turnstileEnabled && captchaBox);

        function updateContactCaptchaState() {
            if (!submitBtn) {
                return;
            }

            if (!requiresVisibleCaptcha) {
                submitBtn.disabled = false;
                if (captchaStatus) {
                    captchaStatus.classList.remove('show');
                }
                return;
            }

            const tokenField = contactForm.querySelector('input[name="cf-turnstile-response"]');
            const captchaDone = Boolean(tokenField && tokenField.value.trim() !== '');
            submitBtn.disabled = !captchaDone;
            if (captchaStatus) {
                captchaStatus.classList.toggle('show', !captchaDone);
            }
        }

        window.contactCaptchaSolved = function () {
            updateContactCaptchaState();
        };

        window.contactCaptchaExpired = function () {
            updateContactCaptchaState();
        };

        if (contactForm && submitOverlay && submitBtn) {
            updateContactCaptchaState();
            if (requiresVisibleCaptcha) {
                window.setInterval(updateContactCaptchaState, 700);
            }

            contactForm.addEventListener('submit', (event) => {
                if (turnstileEnabled) {
                    const tokenField = contactForm.querySelector('input[name="cf-turnstile-response"]');
                    if (!tokenField || tokenField.value.trim() === '') {
                        event.preventDefault();
                        updateContactCaptchaState();
                        submitOverlay.classList.remove('show');
                        if (submitBtn.dataset.originalText) {
                            submitBtn.textContent = submitBtn.dataset.originalText;
                        }
                        return;
                    }
                }
                submitOverlay.classList.add('show');
                submitBtn.dataset.originalText = submitBtn.textContent;
                submitBtn.textContent = 'Sending...';
                submitBtn.disabled = true;
            });

            window.addEventListener('pageshow', () => {
                submitOverlay.classList.remove('show');
                if (submitBtn.dataset.originalText) {
                    submitBtn.textContent = submitBtn.dataset.originalText;
                }
                updateContactCaptchaState();
            });
        }
    </script>
</body>

</html>

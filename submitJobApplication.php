<?php
include 'dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$recaptchaHelperPath = __DIR__ . '/include/recaptcha_helper.php';
if (file_exists($recaptchaHelperPath)) {
    require_once $recaptchaHelperPath;
}

function redirectCareerWithError($message)
{
    header('Location: career.php?error=' . urlencode($message));
    exit;
}

function ensureJobApplicationsTable($db)
{
    if (!$db) {
        return;
    }

    $db->exec("CREATE TABLE IF NOT EXISTS job_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        contact_no VARCHAR(20) NOT NULL,
        apply_for VARCHAR(80) NULL,
        message TEXT,
        resume_path VARCHAR(500),
        resume_original_name VARCHAR(255),
        read_flag TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $alterStatements = [
        "ALTER TABLE job_applications ADD COLUMN apply_for VARCHAR(80) NULL AFTER contact_no",
        "ALTER TABLE job_applications ADD COLUMN message TEXT NULL",
        "ALTER TABLE job_applications ADD COLUMN resume_path VARCHAR(500) NULL",
        "ALTER TABLE job_applications ADD COLUMN resume_original_name VARCHAR(255) NULL",
        "ALTER TABLE job_applications ADD COLUMN read_flag TINYINT(1) DEFAULT 0",
        "ALTER TABLE job_applications ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP"
    ];

    foreach ($alterStatements as $sql) {
        try {
            $db->exec($sql);
        } catch (Exception $ignored) {
        }
    }
}

try {
    ensureJobApplicationsTable($db);
} catch (Exception $e) {
    error_log('Job applications table setup: ' . $e->getMessage());
}

// Admin AJAX actions used by jobApplications.php and applicationReceived.php.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if (!$db) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
        exit;
    }

    if ($_POST['action'] === 'mark_read' && isset($_POST['job_id'])) {
        $stmt = $db->prepare("UPDATE job_applications SET read_flag = 1 WHERE id = ?");
        $stmt->execute([(int)$_POST['job_id']]);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if ($_POST['action'] === 'delete' && isset($_POST['job_id'])) {
        $stmt = $db->prepare("SELECT resume_path FROM job_applications WHERE id = ?");
        $stmt->execute([(int)$_POST['job_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['resume_path']) && file_exists($row['resume_path'])) {
            unlink($row['resume_path']);
        }

        $stmt = $db->prepare("DELETE FROM job_applications WHERE id = ?");
        $stmt->execute([(int)$_POST['job_id']]);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: career.php');
    exit;
}

if (!$db) {
    redirectCareerWithError('Unable to connect to the database. Please try again later.');
}

$fullName = trim($_POST['applicant_name'] ?? ($_POST['full_name'] ?? ''));
$email = trim($_POST['email'] ?? '');
$contactNoRaw = trim($_POST['contact_no'] ?? '');
$contactNo = preg_replace('/\D+/', '', $contactNoRaw);
$applyFor = trim($_POST['apply_for'] ?? '');
$message = trim($_POST['message'] ?? '');
$allowedRoles = ['Auditor', 'Facilitator'];

if ($fullName === '') {
    redirectCareerWithError('Applicant name is required.');
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectCareerWithError('A valid mail ID is required.');
}

if ($contactNo === '' || !preg_match('/^[0-9]{10,15}$/', $contactNo)) {
    redirectCareerWithError('A valid contact number with 10 to 15 digits is required.');
}

if (!in_array($applyFor, $allowedRoles, true)) {
    redirectCareerWithError('Please select Auditor or Facilitator in Apply For.');
}

$recaptchaToken = trim($_POST['g-recaptcha-response'] ?? '');
$recaptchaError = '';
if (function_exists('aitf_verify_recaptcha') && !aitf_verify_recaptcha($recaptchaToken, $recaptchaError)) {
    redirectCareerWithError($recaptchaError !== '' ? $recaptchaError : 'Captcha verification failed. Please try again.');
}

$resumePath = '';
$resumeOriginalName = '';
if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['resume']['tmp_name'];
    $fileOriginalName = $_FILES['resume']['name'];
    $fileSize = $_FILES['resume']['size'];
    $fileExtension = strtolower(pathinfo($fileOriginalName, PATHINFO_EXTENSION));
    $fileMimeType = function_exists('mime_content_type') ? mime_content_type($fileTmpPath) : '';

    if ($fileExtension !== 'pdf' || ($fileMimeType !== '' && $fileMimeType !== 'application/pdf')) {
        redirectCareerWithError('Only PDF files are allowed for resume upload.');
    }

    if ($fileSize > 5 * 1024 * 1024) {
        redirectCareerWithError('Resume file size must not exceed 5MB.');
    }

    $uploadDir = 'uploads/job_resumes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uniqueFileName = 'resume_' . time() . '_' . random_int(1000, 9999) . '.pdf';
    $resumePath = $uploadDir . $uniqueFileName;
    $resumeOriginalName = $fileOriginalName;

    if (!move_uploaded_file($fileTmpPath, $resumePath)) {
        redirectCareerWithError('Failed to upload resume. Please try again.');
    }
} elseif (isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
    redirectCareerWithError('Resume upload error. Please try again.');
}

try {
    $stmt = $db->prepare("INSERT INTO job_applications (full_name, email, contact_no, apply_for, message, resume_path, resume_original_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fullName, $email, $contactNo, $applyFor, $message, $resumePath, $resumeOriginalName]);
} catch (Exception $e) {
    error_log('Job application insert error: ' . $e->getMessage());
    redirectCareerWithError('Failed to save your application. Please try again later.');
}

function loadJobMailer()
{
    if (class_exists('PHPMailer')) {
        return true;
    }

    $candidates = [
        __DIR__ . '/PHPMailer/PHPMailerAutoload.php',
        __DIR__ . '/phpmailer/PHPMailerAutoload.php',
        __DIR__ . '/PHPMailerAutoload.php'
    ];

    foreach ($candidates as $path) {
        if (file_exists($path)) {
            require_once $path;
            return class_exists('PHPMailer');
        }
    }

    return false;
}

function jobMailAttempts()
{
    require_once __DIR__ . '/include/env_helper.php';
    $mailConfig = aitf_get_mail_config();

    return [
        ['host' => 'localhost', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false, 'username' => '', 'password' => ''],
        ['host' => 'relay-hosting.secureserver.net', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false, 'username' => '', 'password' => ''],
        [
            'host' => $mailConfig['host'],
            'port' => $mailConfig['port'],
            'secure' => $mailConfig['secure'],
            'autotls' => $mailConfig['secure'] === 'tls',
            'auth' => ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== '',
            'username' => $mailConfig['username'] ?? '',
            'password' => $mailConfig['password'] ?? ''
        ]
    ];
}

function configureJobMailer(PHPMailer $mail, array $config)
{
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Debugoutput = 'error_log';
    $mail->Host = $config['host'];
    $mail->Hostname = $config['host'];
    $mail->Port = $config['port'];
    $mail->SMTPAuth = $config['auth'];
    $mail->SMTPSecure = $config['secure'] ?: false;
    $mail->SMTPAutoTLS = $config['autotls'];
    if ($config['auth']) {
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
    }
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 8;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
}

function sendJobMail(callable $buildMail)
{
    if (!loadJobMailer()) {
        error_log('PHPMailer autoload not found for job application mail.');
        return false;
    }

    foreach (jobMailAttempts() as $attempt) {
        try {
            $mail = new PHPMailer;
            configureJobMailer($mail, $attempt);
            $buildMail($mail);
            if ($mail->send()) {
                return true;
            }
            error_log('Job mail SMTP fail: [' . $attempt['host'] . ':' . $attempt['port'] . '] ' . $mail->ErrorInfo);
            $mail->smtpClose();
        } catch (Exception $e) {
            error_log('Job mail exception: ' . $e->getMessage());
        }
    }

    return false;
}

$submissionDate = date('d M Y, h:i A');
$adminBody = '<h3>New Job Application Received</h3>'
    . '<p>A new career application has been submitted from the AITF website.</p>'
    . '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:13px;">'
    . '<tr><th align="left">Applicant Name</th><td>' . htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') . '</td></tr>'
    . '<tr><th align="left">Mail ID</th><td><a href="mailto:' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</a></td></tr>'
    . '<tr><th align="left">Contact No.</th><td>' . htmlspecialchars($contactNo, ENT_QUOTES, 'UTF-8') . '</td></tr>'
    . '<tr><th align="left">Apply For</th><td>' . htmlspecialchars($applyFor, ENT_QUOTES, 'UTF-8') . '</td></tr>'
    . '<tr><th align="left">Message</th><td>' . ($message !== '' ? nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) : '<em>No message provided</em>') . '</td></tr>'
    . '<tr><th align="left">Submitted On</th><td>' . htmlspecialchars($submissionDate, ENT_QUOTES, 'UTF-8') . '</td></tr>'
    . '</table>';

sendJobMail(function (PHPMailer $mail) use ($fullName, $email, $contactNo, $applyFor, $message, $resumePath, $resumeOriginalName, $adminBody) {
    $mail->setFrom('certification@aitf.co.in', 'AITF Careers');
    $mail->Sender = 'certification@aitf.co.in';
    $mail->addCustomHeader('Return-Path', 'certification@aitf.co.in');
    $mail->addReplyTo($email, $fullName);
    $mail->addAddress('certification@aitf.co.in', 'AITF Certification');
    $mail->Subject = 'New Job Application Received - ' . $fullName . ' (' . $applyFor . ')';
    $mail->isHTML(true);
    $mail->Body = $adminBody;
    $mail->AltBody = "New Job Application Received\nApplicant Name: $fullName\nMail ID: $email\nContact No.: $contactNo\nApply For: $applyFor\nMessage: $message";

    if ($resumePath !== '' && file_exists($resumePath)) {
        $mail->addAttachment($resumePath, $resumeOriginalName ?: basename($resumePath));
    }
});

$clientBody = '<p style="font-family:Arial,sans-serif;font-size:14px;">Dear ' . htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') . ',</p>'
    . '<p style="font-family:Arial,sans-serif;font-size:14px;">Thank you for your job application at AITF.</p>'
    . '<p style="font-family:Arial,sans-serif;font-size:14px;">We have received your application for <strong>' . htmlspecialchars($applyFor, ENT_QUOTES, 'UTF-8') . '</strong>. Our hiring team will review your profile and if your expertise matches our requirement, we will contact you shortly.</p>'
    . '<p style="font-family:Arial,sans-serif;font-size:14px;">We appreciate your interest in joining our team and contributing to meaningful work in assessment and certification.</p>'
    . '<p style="font-family:Arial,sans-serif;font-size:14px;">Warm regards,<br><strong>AITF Team</strong><br>Careers Desk<br><a href="mailto:certification@aitf.co.in">certification@aitf.co.in</a></p>';

sendJobMail(function (PHPMailer $mail) use ($fullName, $email, $applyFor, $clientBody) {
    $mail->setFrom('certification@aitf.co.in', 'AITF Careers');
    $mail->Sender = 'certification@aitf.co.in';
    $mail->addCustomHeader('Return-Path', 'certification@aitf.co.in');
    $mail->addAddress($email, $fullName);
    $mail->Subject = 'Thank you for your job application - AITF';
    $mail->isHTML(true);
    $mail->Body = $clientBody;
    $mail->AltBody = "Dear $fullName,\n\nThank you for your job application at AITF. We have received your application for $applyFor. Our hiring team will review your profile and if your expertise matches our requirement, we will contact you shortly.\n\nWarm regards,\nAITF Team\nCareers Desk\ncertification@aitf.co.in";
});

header('Location: career.php?success=1&applicant=' . urlencode($fullName));
exit;

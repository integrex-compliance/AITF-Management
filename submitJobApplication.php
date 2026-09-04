<?php
include 'dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

require_once __DIR__ . '/include/job_mailer_helper.php';

$turnstileHelperPath = __DIR__ . '/include/turnstile_helper.php';
if (file_exists($turnstileHelperPath)) {
    require_once $turnstileHelperPath;
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
        qualification VARCHAR(255) NULL,
        apply_for VARCHAR(80) NULL,
        message TEXT,
        resume_path VARCHAR(500),
        resume_original_name VARCHAR(255),
        read_flag TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $alterStatements = [
        "ALTER TABLE job_applications ADD COLUMN qualification VARCHAR(255) NULL AFTER contact_no",
        "ALTER TABLE job_applications ADD COLUMN apply_for VARCHAR(80) NULL AFTER qualification",
        "ALTER TABLE job_applications ADD COLUMN message TEXT NULL",
        "ALTER TABLE job_applications ADD COLUMN resume_path VARCHAR(500) NULL",
        "ALTER TABLE job_applications ADD COLUMN resume_original_name VARCHAR(255) NULL",
        "ALTER TABLE job_applications ADD COLUMN read_flag TINYINT(1) DEFAULT 0",
        "ALTER TABLE job_applications ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
        "ALTER TABLE job_applications ADD COLUMN assigned_to_auditor_id INT NULL",
        "ALTER TABLE job_applications ADD COLUMN auditor_review TEXT NULL",
        "ALTER TABLE job_applications MODIFY COLUMN apply_for VARCHAR(150) NULL"
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
        $isTopMgmt = (isset($_SESSION['role']) && trim($_SESSION['role']) === 'Top Mgmt') || (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role());
        if (!$isTopMgmt) {
            echo json_encode(['status' => 'error', 'message' => 'You are not allowed to perform this action. Only Top Management can delete the form.']);
            exit;
        }

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

    if ($_POST['action'] === 'save_job_review' && isset($_POST['job_id'])) {
        $stmt = $db->prepare("UPDATE job_applications SET auditor_review = ? WHERE id = ?");
        $stmt->execute([trim($_POST['auditor_review'] ?? ''), (int)$_POST['job_id']]);
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
$qualificationRaw = trim($_POST['qualification'] ?? '');
$qualificationOther = trim($_POST['qualification_other'] ?? '');
$qualification = $qualificationRaw;
if (($qualificationRaw === 'Other' || $qualificationRaw === 'Any other relevant' || $qualificationRaw === 'Any other relevant qualification') && $qualificationOther !== '') {
    $qualification = 'Other: ' . $qualificationOther;
}

$applyFor = trim($_POST['apply_for'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($fullName === '') {
    redirectCareerWithError('Applicant name is required.');
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectCareerWithError('A valid email address is required.');
}

if ($contactNo === '' || !preg_match('/^[0-9]{10,15}$/', $contactNo)) {
    redirectCareerWithError('A valid contact number with 10 to 15 digits is required.');
}

if ($qualification === '') {
    redirectCareerWithError('Please select your qualification.');
}

if ($applyFor === '') {
    redirectCareerWithError('Please enter the position name you are interested in.');
}

if (function_exists('mb_substr')) {
    $applyFor = mb_substr($applyFor, 0, 100);
} else {
    $applyFor = substr($applyFor, 0, 100);
}

// Check Email OTP Verification
$emailLower = strtolower($email);
$isOtpVerified = (isset($_SESSION['career_otp_verified']) && $_SESSION['career_otp_verified'] === true && isset($_SESSION['career_verified_email']) && $_SESSION['career_verified_email'] === $emailLower);

// Fallback: If user typed OTP into the input field and hit submit directly
$submittedOtp = trim($_POST['email_otp'] ?? '');
if (!$isOtpVerified && $submittedOtp !== '') {
    $sessionOtp = $_SESSION['career_otp'] ?? '';
    $sessionEmail = $_SESSION['career_otp_email'] ?? '';
    $sessionTime = $_SESSION['career_otp_time'] ?? 0;
    if ($sessionOtp !== '' && $sessionOtp === $submittedOtp && $sessionEmail === $emailLower && (time() - $sessionTime <= 600)) {
        $isOtpVerified = true;
        $_SESSION['career_otp_verified'] = true;
        $_SESSION['career_verified_email'] = $emailLower;
    }
}

if (!$isOtpVerified) {
    redirectCareerWithError('Please verify your email address with the OTP before submitting the application.');
}

$turnstileToken = trim($_POST['cf-turnstile-response'] ?? '');
$turnstileError = '';
if (function_exists('aitf_verify_turnstile') && !aitf_verify_turnstile($turnstileToken, $turnstileError)) {
    redirectCareerWithError($turnstileError !== '' ? $turnstileError : 'Captcha verification failed. Please try again.');
}

$resumePath = '';
$resumeOriginalName = '';
$uploadedResume = isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE ? $_FILES['resume'] : (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] !== UPLOAD_ERR_NO_FILE ? $_FILES['cv_file'] : null);

if ($uploadedResume && $uploadedResume['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $uploadedResume['tmp_name'];
    $fileOriginalName = $uploadedResume['name'];
    $fileSize = $uploadedResume['size'];
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
} elseif ($uploadedResume && $uploadedResume['error'] !== UPLOAD_ERR_NO_FILE) {
    redirectCareerWithError('Resume upload error. Please try again.');
}

try {
    $stmt = $db->prepare("INSERT INTO job_applications (full_name, email, contact_no, qualification, apply_for, message, resume_path, resume_original_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fullName, $email, $contactNo, $qualification, $applyFor, $message, $resumePath, $resumeOriginalName]);
} catch (Exception $e) {
    error_log('Job application insert error: ' . $e->getMessage());
    redirectCareerWithError('Failed to save your application. Please try again later.');
}

// Reset session verification after successful insert
unset($_SESSION['career_otp'], $_SESSION['career_otp_email'], $_SESSION['career_otp_time'], $_SESSION['career_otp_verified'], $_SESSION['career_verified_email']);

$submissionDate = date('d M Y, h:i A');
$adminBody = '<h3>New Job Application Received</h3>'
    . '<p>A new career application has been submitted on the AITF portal with verified email.</p>'
    . '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:13px;">'
    . '<tr><th align="left">Applicant Name</th><td>' . htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') . '</td></tr>'
    . '<tr><th align="left">Email Address</th><td><a href="mailto:' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</a> (Verified ✓)</td></tr>'
    . '<tr><th align="left">Contact No.</th><td>' . htmlspecialchars($contactNo, ENT_QUOTES, 'UTF-8') . '</td></tr>'
    . '<tr><th align="left">Qualification</th><td>' . htmlspecialchars($qualification, ENT_QUOTES, 'UTF-8') . '</td></tr>'
    . '<tr><th align="left">Apply For</th><td>' . htmlspecialchars($applyFor, ENT_QUOTES, 'UTF-8') . '</td></tr>'
    . '<tr><th align="left">Message</th><td>' . ($message !== '' ? nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) : '<em>No message provided</em>') . '</td></tr>'
    . '<tr><th align="left">Submitted On</th><td>' . htmlspecialchars($submissionDate, ENT_QUOTES, 'UTF-8') . '</td></tr>'
    . '</table>';

sendJobMail(function (PHPMailer $mail) use ($fullName, $email, $contactNo, $qualification, $applyFor, $message, $resumePath, $resumeOriginalName, $adminBody) {
    $mail->setFrom('certification@aitf.co.in', 'AITF Careers');
    $mail->Sender = 'certification@aitf.co.in';
    $mail->addCustomHeader('Return-Path', 'certification@aitf.co.in');
    $mail->addReplyTo($email, $fullName);
    $mail->addAddress('certification@aitf.co.in', 'AITF Certification');
    $mail->Subject = 'New Job Application Received - ' . $fullName . ' (' . $applyFor . ')';
    $mail->isHTML(true);
    $mail->Body = $adminBody;
    $mail->AltBody = "New Job Application Received\nApplicant Name: $fullName\nEmail: $email (Verified)\nContact No.: $contactNo\nQualification: $qualification\nApply For: $applyFor\nMessage: $message";

    if ($resumePath !== '' && file_exists($resumePath)) {
        $mail->addAttachment($resumePath, $resumeOriginalName ?: basename($resumePath));
    }
});

$clientBody = '<div style="font-family:Arial,sans-serif;max-width:580px;margin:0 auto;padding:24px;border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;">'
    . '<h2 style="color:#0f172a;margin-top:0;">Application Received Successfully</h2>'
    . '<p style="font-size:15px;color:#334155;">Dear ' . htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') . ',</p>'
    . '<p style="font-size:14px;color:#334155;line-height:1.6;">Thank you for submitting your job application to <strong>AITF Management Certification</strong>.</p>'
    . '<p style="font-size:14px;color:#334155;line-height:1.6;">We have received your application for <strong>' . htmlspecialchars($applyFor, ENT_QUOTES, 'UTF-8') . '</strong> with qualification <strong>' . htmlspecialchars($qualification, ENT_QUOTES, 'UTF-8') . '</strong>. Our assessment and technical evaluation team will review your profile against our standard requirements and reach out to you shortly.</p>'
    . '<p style="font-size:14px;color:#334155;line-height:1.6;">We value your commitment to raising quality and compliance standards.</p>'
    . '<hr style="border:none;border-top:1px solid #f1f5f9;margin:20px 0;">'
    . '<p style="font-size:13px;color:#64748b;margin-bottom:0;">Warm regards,<br><strong style="color:#0f172a;">AITF Assessment & Certification Team</strong><br><a href="mailto:certification@aitf.co.in" style="color:#2563eb;">certification@aitf.co.in</a></p>'
    . '</div>';

sendJobMail(function (PHPMailer $mail) use ($fullName, $email, $applyFor, $clientBody) {
    $mail->setFrom('certification@aitf.co.in', 'AITF Careers');
    $mail->Sender = 'certification@aitf.co.in';
    $mail->addCustomHeader('Return-Path', 'certification@aitf.co.in');
    $mail->addAddress($email, $fullName);
    $mail->Subject = 'Thank you for your job application - AITF';
    $mail->isHTML(true);
    $mail->Body = $clientBody;
    $mail->AltBody = "Dear $fullName,\n\nThank you for your job application at AITF for $applyFor. Our assessment team will review your profile and contact you shortly.\n\nWarm regards,\nAITF Team\ncertification@aitf.co.in";
});

header('Location: career.php?success=1&applicant=' . urlencode($fullName));
exit;

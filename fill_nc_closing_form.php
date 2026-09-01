<?php
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$errors = [];
$successMessage = '';

$turnstileHelperPath = __DIR__ . '/include/turnstile_helper.php';
if (file_exists($turnstileHelperPath)) {
    require_once $turnstileHelperPath;
}
$turnstileConfig = function_exists('aitf_get_turnstile_config') ? aitf_get_turnstile_config() : [
    'site_key' => '',
    'secret_key' => ''
];
$turnstileReady = function_exists('aitf_is_turnstile_ready') ? aitf_is_turnstile_ready() : false;

// Create table if not exists
$createTableSQL = "CREATE TABLE IF NOT EXISTS nc_closing_forms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    org_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    standard VARCHAR(255) NOT NULL,
    audit_date VARCHAR(50) NOT NULL,
    nc_reference_no VARCHAR(100) NOT NULL,
    corrective_action_date VARCHAR(50) NOT NULL,
    root_cause TEXT NOT NULL,
    correction TEXT NOT NULL,
    corrective_action TEXT NOT NULL,
    sign_name VARCHAR(255) NOT NULL,
    sign_designation VARCHAR(255) NOT NULL,
    proposed_completion_date VARCHAR(50) NOT NULL,
    signature_image VARCHAR(255) NULL,
    attachment1 VARCHAR(255) NULL,
    attachment2 VARCHAR(255) NULL,
    read_flag TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL,
    
    assessor_accepted VARCHAR(50) NULL,
    assessor_comments TEXT NULL,
    assessor_verification_method TEXT NULL,
    team_leader_name VARCHAR(255) NULL,
    team_leader_signature VARCHAR(255) NULL,
    team_leader_date VARCHAR(50) NULL,
    assessor_name VARCHAR(255) NULL,
    assessor_signature VARCHAR(255) NULL,
    assessor_date VARCHAR(50) NULL,
    follow_up_effectiveness VARCHAR(50) NULL,
    verifying_assessor_sign VARCHAR(255) NULL,
    verifying_assessor_date VARCHAR(50) NULL,

    INDEX idx_created_at (created_at),
    INDEX idx_read_flag (read_flag),
    INDEX idx_org_name (org_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->exec($createTableSQL);
    try { $db->exec("ALTER TABLE nc_closing_forms ADD COLUMN attachment1 VARCHAR(255) NULL AFTER signature_image"); } catch (PDOException $e) {}
    try { $db->exec("ALTER TABLE nc_closing_forms ADD COLUMN attachment2 VARCHAR(255) NULL AFTER attachment1"); } catch (PDOException $e) {}
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
    $errors[] = "Database setup error: " . $e->getMessage();
}

/**
 * Security-hardened file upload validator and processor (Antivirus / Malware Prevention)
 */
function aitf_validate_and_upload_nc_file($fileKey, array $allowedExts, $maxBytes, $targetDir, $prefix, &$errors, $fieldLabel) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fileKey];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Upload error for {$fieldLabel}. Please try again.";
        return null;
    }

    if ($file['size'] > $maxBytes) {
        $maxMB = round($maxBytes / (1024 * 1024));
        $errors[] = "{$fieldLabel} size exceeds {$maxMB}MB limit.";
        return null;
    }

    if ($file['size'] === 0) {
        $errors[] = "Uploaded {$fieldLabel} file is empty.";
        return null;
    }

    $originalName = $file['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // 1. Strict extension whitelist
    if (!in_array($ext, $allowedExts, true)) {
        $errors[] = "Invalid file type for {$fieldLabel}. Allowed formats: " . strtoupper(implode(', ', $allowedExts));
        return null;
    }

    // 2. Reject double extensions and script-like keywords
    $lowerName = strtolower($originalName);
    $blocked = ['.php', '.phtml', '.phar', '.exe', '.sh', '.bat', '.cmd', '.vbs', '.py', '.pl', '.cgi', '.asp', '.aspx', '.js', '.jar', '.scr', '.dll', '.msi', '.vbe', '.wsf', '.htaccess'];
    foreach ($blocked as $b) {
        if (strpos($lowerName, $b) !== false) {
            $errors[] = "Security violation: Dangerous file name in {$fieldLabel}.";
            return null;
        }
    }

    // 3. MIME type inspection
    $tmpPath = $file['tmp_name'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMime = $finfo ? finfo_file($finfo, $tmpPath) : mime_content_type($tmpPath);
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowedMimes = [
        'jpg'  => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'png'  => ['image/png'],
        'pdf'  => ['application/pdf', 'application/x-pdf'],
        'doc'  => ['application/msword', 'application/x-msword', 'application/vnd.ms-office', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/x-zip-compressed', 'application/octet-stream']
    ];

    $validMime = false;
    if (isset($allowedMimes[$ext])) {
        if (in_array($detectedMime, $allowedMimes[$ext], true)) {
            $validMime = true;
        }
    }
    if (!$validMime && ($ext === 'docx' || $ext === 'doc')) {
        if (in_array($detectedMime, ['application/zip', 'application/octet-stream', 'application/vnd.ms-office', 'application/x-zip-compressed'], true)) {
            $validMime = true;
        }
    }

    if (!$validMime) {
        $errors[] = "Security violation: File content verification failed for {$fieldLabel}.";
        return null;
    }

    // 4. Magic bytes verification
    $handle = @fopen($tmpPath, 'rb');
    if (!$handle) {
        $errors[] = "Unable to read {$fieldLabel} for security verification.";
        return null;
    }
    $bytes = fread($handle, 16);
    fclose($handle);

    if (in_array($ext, ['jpg', 'jpeg'], true)) {
        if (substr($bytes, 0, 3) !== "\xFF\xD8\xFF") {
            $errors[] = "Corrupted or invalid JPEG image in {$fieldLabel}.";
            return null;
        }
    } elseif ($ext === 'png') {
        if (substr($bytes, 0, 8) !== "\x89PNG\r\n\x1a\n") {
            $errors[] = "Corrupted or invalid PNG image in {$fieldLabel}.";
            return null;
        }
    } elseif ($ext === 'pdf') {
        if (substr($bytes, 0, 4) !== "%PDF") {
            $errors[] = "Corrupted or invalid PDF document in {$fieldLabel}.";
            return null;
        }
    } elseif ($ext === 'doc') {
        if (substr($bytes, 0, 8) !== "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
            $errors[] = "Corrupted or invalid Word DOC document in {$fieldLabel}.";
            return null;
        }
    } elseif ($ext === 'docx') {
        if (substr($bytes, 0, 4) !== "PK\x03\x04") {
            $errors[] = "Corrupted or invalid Word DOCX document in {$fieldLabel}.";
            return null;
        }
    }

    // 5. Ensure directory exists with .htaccess protection
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $htaccessFile = rtrim($targetDir, '/') . '/.htaccess';
    if (!file_exists($htaccessFile)) {
        $htaccessContent = "# Disable script execution\n<FilesMatch \"\.(php|phtml|php3|php4|php5|phps|pht|phar|pl|py|cgi|asp|aspx|sh|bash|exe|js)$\">\n    Order Allow,Deny\n    Deny from all\n</FilesMatch>\nOptions -ExecCGI -Indexes\nphp_flag engine off\n";
        @file_put_contents($htaccessFile, $htaccessContent);
    }

    // 6. Generate unguessable random filename
    $newFilename = $prefix . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $destPath = rtrim($targetDir, '/') . '/' . $newFilename;

    if (move_uploaded_file($tmpPath, $destPath)) {
        return $destPath;
    } else {
        $errors[] = "Failed to store uploaded file for {$fieldLabel}.";
        return null;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_nc_form'])) {
    $turnstileToken = trim($_POST['cf-turnstile-response'] ?? '');
    if ($turnstileReady && empty($turnstileToken)) {
        $errors[] = "Please complete the captcha verification.";
    } elseif ($turnstileReady && function_exists('aitf_verify_turnstile') && !aitf_verify_turnstile($turnstileToken, $error_message)) {
        $errors[] = "Captcha verification failed" . ($error_message ? ": $error_message" : ".");
    }

    $org_name = htmlspecialchars(trim($_POST['org_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $standard_opts = $_POST['standard_opts'] ?? [];
    $standard_other = trim($_POST['standard_other'] ?? '');
    $standard_arr = [];
    if (is_array($standard_opts)) {
        foreach ($standard_opts as $opt) {
            $standard_arr[] = htmlspecialchars(trim($opt), ENT_QUOTES, 'UTF-8');
        }
    }
    if (!empty($standard_other)) {
        $standard_arr[] = htmlspecialchars($standard_other, ENT_QUOTES, 'UTF-8');
    }
    $standard = implode(', ', $standard_arr);

    $audit_date = htmlspecialchars(trim($_POST['audit_date'] ?? ''), ENT_QUOTES, 'UTF-8');
    $nc_reference_no = htmlspecialchars(trim($_POST['nc_reference_no'] ?? ''), ENT_QUOTES, 'UTF-8');
    $corrective_action_date = htmlspecialchars(trim($_POST['corrective_action_date'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    $root_cause = htmlspecialchars(trim($_POST['root_cause'] ?? ''), ENT_QUOTES, 'UTF-8');
    $correction = htmlspecialchars(trim($_POST['correction'] ?? ''), ENT_QUOTES, 'UTF-8');
    $corrective_action = htmlspecialchars(trim($_POST['corrective_action'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    $sign_name = htmlspecialchars(trim($_POST['sign_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $sign_designation = htmlspecialchars(trim($_POST['sign_designation'] ?? ''), ENT_QUOTES, 'UTF-8');
    $proposed_completion_date = htmlspecialchars(trim($_POST['proposed_completion_date'] ?? ''), ENT_QUOTES, 'UTF-8');

    if (empty($org_name)) $errors[] = "Organization name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid Organization Mail ID is required.";
    } else {
        require_once __DIR__ . '/include/client_email_validator.php';
        $emailCheck = aitf_check_client_email_exists($db, $email);
        if (!$emailCheck['found']) {
            $errors[] = "This mail is not valid. Kindly mention the mail ID that is present in your application form.";
        }
    }
    if (empty($standard)) $errors[] = "Standard is required";
    if (empty($audit_date)) $errors[] = "Date of audit is required";
    if (empty($nc_reference_no)) $errors[] = "Nonconformance reference no is required";
    if (empty($corrective_action_date)) $errors[] = "Date of corrective action is required";
    if (empty($root_cause)) $errors[] = "Root cause is required";
    if (empty($correction)) $errors[] = "Correction is required";
    if (empty($corrective_action)) $errors[] = "Corrective Action is required";
    if (empty($sign_name)) $errors[] = "Name is required";
    if (empty($sign_designation)) $errors[] = "Designation is required";
    if (empty($proposed_completion_date)) $errors[] = "Proposed Completion Date is required";

    if (!empty($audit_date) && !empty($corrective_action_date)) {
        if (strtotime($corrective_action_date) < strtotime($audit_date)) {
            $errors[] = "Date of corrective action cannot be before the Date of audit.";
        }
    }

    // Process files with strict security checks
    $signature_image_path = aitf_validate_and_upload_nc_file(
        'signature_image',
        ['jpg', 'jpeg', 'png', 'pdf'],
        2 * 1024 * 1024,
        'uploads/signatures/',
        'nc_sig_',
        $errors,
        'Signature'
    );

    if (empty($signature_image_path) && empty($errors)) {
        $errors[] = "Signature with company's duly stamp is required.";
    }

    $attachment1_path = aitf_validate_and_upload_nc_file(
        'attachment1',
        ['png', 'jpg', 'jpeg', 'pdf', 'doc', 'docx'],
        5 * 1024 * 1024,
        'uploads/nc_attachments/',
        'nc_att1_',
        $errors,
        'Attachment 1'
    );

    $attachment2_path = aitf_validate_and_upload_nc_file(
        'attachment2',
        ['png', 'jpg', 'jpeg', 'pdf', 'doc', 'docx'],
        5 * 1024 * 1024,
        'uploads/nc_attachments/',
        'nc_att2_',
        $errors,
        'Attachment 2'
    );

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO nc_closing_forms (
                org_name, email, standard, audit_date, nc_reference_no, corrective_action_date,
                root_cause, correction, corrective_action,
                sign_name, sign_designation, proposed_completion_date, signature_image,
                attachment1, attachment2,
                read_flag, created_at
            ) VALUES (
                :org_name, :email, :standard, :audit_date, :nc_reference_no, :corrective_action_date,
                :root_cause, :correction, :corrective_action,
                :sign_name, :sign_designation, :proposed_completion_date, :signature_image,
                :attachment1, :attachment2,
                0, NOW()
            )";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':org_name' => $org_name,
                ':email' => $email,
                ':standard' => $standard,
                ':audit_date' => $audit_date,
                ':nc_reference_no' => $nc_reference_no,
                ':corrective_action_date' => $corrective_action_date,
                ':root_cause' => $root_cause,
                ':correction' => $correction,
                ':corrective_action' => $corrective_action,
                ':sign_name' => $sign_name,
                ':sign_designation' => $sign_designation,
                ':proposed_completion_date' => $proposed_completion_date,
                ':signature_image' => $signature_image_path,
                ':attachment1' => $attachment1_path,
                ':attachment2' => $attachment2_path
            ]);
            $last_insert_id = $db->lastInsertId();

            try {
                require_once __DIR__ . '/testemail.php';
                $emailSender = new AITFServiceEmailSender();
                
                // 1. Send mail to client
                if (!empty($email)) {
                    $clientSubject = "Confirmation: NC Closing Form Received";
                    $clientBody = "<p>Dear " . htmlspecialchars($sign_name) . ",</p>
                                   <p>This is a confirmation mail that we have received your NC closing form and our team is reviewing it.</p>
                                   <p>Meanwhile if you need any help kindly feel free to connect with us.</p>
                                   <p>Best regards,<br>AITF Certification Services</p>";
                    $emailSender->sendEmail($email, $sign_name, $clientBody, $clientSubject);
                }
                
                // 2. Send mail to admin
                $adminEmail = "certification@aitf.co.in";
                $adminSubject = htmlspecialchars($org_name) . " NC Closing Form Received";
                $attNotice = ($attachment1_path || $attachment2_path) ? "<p style='color:#0369a1;'><b>Note:</b> Supporting attachments were uploaded with this submission.</p>" : "";
                $adminBody = "<p>Hello,</p>
                              <p>New NC Closing Form received from <b>" . htmlspecialchars($org_name) . "</b>.</p>
                              " . $attNotice . "
                              <p>Please review it from the admin dashboard.</p>";
                $emailSender->sendEmail($adminEmail, "AITF Admin", $adminBody, $adminSubject);
            } catch (Exception $e) {
                error_log("Failed to send emails for NC Closing form: " . $e->getMessage());
            }

            $successMessage = "NC Closing form submitted successfully!";
            unset($_SESSION['nc_closing_email_verified']);
            $_POST = [];
        } catch (PDOException $e) {
            error_log("Database insert error: " . $e->getMessage());
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

$pageIdentifier = "NC Closing Form";
$pageTitle = "NC Closing Form - AITF";
$canonicalUrl = "https://www.aitf.co.in/nc_closing";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($turnstileReady): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <style>
        body {
            background: linear-gradient(135deg, #0b132b 0%, #1c2541 50%, #0f172a 100%);
            min-height: 100vh;
        }

        .nc-form-wrapper {
            max-width: 1060px;
            margin: 120px auto 70px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            width: 100%;
            padding: 0 20px;
        }

        .nc-form-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.28), 0 0 1px 1px rgba(255, 255, 255, 0.08);
            padding: 48px 56px;
            border: 1px solid rgba(226, 232, 240, 0.9);
        }

        @media (max-width: 768px) {
            .nc-form-wrapper {
                margin-top: 90px;
                padding: 0 12px;
            }
            .nc-form-card {
                padding: 30px 20px;
                border-radius: 16px;
            }
            .nc-form-section {
                padding: 24px 18px !important;
            }
        }

        .nc-form-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 28px 30px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.8);
        }

        .nc-header-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            font-size: 12.5px;
            letter-spacing: 1px;
            padding: 5px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .nc-form-header h3 {
            margin: 0;
            font-size: 25px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.3px;
        }

        .nc-form-header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
        }

        .nc-form-section {
            border: 1px solid #e2e8f0;
            padding: 36px 36px 28px;
            margin-bottom: 35px;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 18px -4px rgba(15, 23, 42, 0.04);
            transition: all 0.25s ease;
        }

        .nc-form-section:hover {
            box-shadow: 0 8px 25px -4px rgba(15, 23, 42, 0.07);
            border-color: #cbd5e1;
        }

        .nc-form-section h4 {
            margin-top: 0;
            color: #0f172a;
            font-size: 18px;
            font-weight: 700;
            border-bottom: 1.5px solid #f1f5f9;
            padding-bottom: 18px;
            margin-bottom: 26px;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.2px;
            line-height: 1.4;
        }

        .nc-section-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #eff6ff;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            font-weight: 600;
            color: #334155;
            font-size: 14.5px;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14.5px;
            color: #0f172a;
            background-color: #f8fafc;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
        }

        .form-control:hover {
            border-color: #94a3b8;
            background-color: #ffffff;
        }

        .form-control:focus {
            border-color: #2563eb;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            outline: none;
        }

        textarea.form-control {
            min-height: 125px;
            resize: vertical;
            line-height: 1.6;
            padding: 14px 18px;
        }

        .standard-checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 6px;
        }

        .standard-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            color: #334155;
            font-size: 14px;
            transition: all 0.2s ease;
            user-select: none;
            margin: 0;
        }

        .standard-pill:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .standard-pill input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #2563eb;
            cursor: pointer;
            margin: 0;
        }

        .btn-submit {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: #ffffff;
            border: none;
            padding: 16px 48px;
            font-size: 17px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.2px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 16px 32px -6px rgba(37, 99, 235, 0.5);
            color: #ffffff;
        }

        .btn-submit:disabled,
        .btn-submit[disabled] {
            background: #94a3b8 !important;
            opacity: 0.65 !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
            transform: none !important;
            pointer-events: none !important;
        }
        
        .alert {
            padding: 18px 24px;
            margin-bottom: 30px;
            border-radius: 12px;
            font-size: 15px;
        }
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        @keyframes modalPop {
            0% { transform: scale(0.92); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .auto-filled-highlight {
            animation: fieldHighlight 2.5s ease;
        }
        @keyframes fieldHighlight {
            0% { background-color: #dcfce7 !important; border-color: #16a34a !important; box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.25) !important; }
            50% { background-color: #f0fdf4 !important; border-color: #86efac !important; }
            100% { background-color: #ffffff !important; border-color: #cbd5e1 !important; }
        }
    </style>

    <div class="nc-form-wrapper">
        <div class="nc-form-card">
            <div class="nc-form-header">
                <span class="nc-header-badge">Nonconformance Closure Report</span>
                <h3 style="margin: 4px 0 6px 0; font-size: 25px; font-weight: 800; color: #0f172a;">AITF MANAGEMENT CERTIFICATION LIMITED</h3>
                <p style="margin: 0; font-size: 14px; font-weight: 600; color: #64748b;">Form No. F 7.24 (Annexure-A)</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong><i class="fa fa-exclamation-triangle"></i> Please correct the following errors:</strong>
                    <ul style="margin: 10px 0 0 20px; line-height: 1.6;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <strong><i class="fa fa-check-circle"></i> <?php echo $successMessage; ?></strong>
                </div>
            <?php endif; ?>

            <form id="nc_form" method="POST" action="" enctype="multipart/form-data">
                <!-- 1. General Information -->
                <div class="nc-form-section">
                    <h4>
                        <span class="nc-section-icon"><i class="fa fa-building"></i></span>
                        General Information
                    </h4>
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <label>Organization Name <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="org_name" id="nc_org_name" class="form-control" placeholder="Enter complete organization name" value="<?php echo htmlspecialchars($_POST['org_name'] ?? ''); ?>" required>
                            <small id="nc_org_auto_filled" style="display:none; color:#059669; font-weight:600; font-size:12.5px; margin-top:6px;"><i class="fa fa-magic"></i> Auto-selected from registered client records (you can edit if needed)</small>
                        </div>
                        <div class="col-sm-12 form-group">
                            <label>Standard <span style="color:#ef4444;">*</span></label>
                            <div class="standard-checkbox-group">
                                <label class="standard-pill">
                                    <input type="checkbox" name="standard_opts[]" value="ISO 9001" <?php echo (isset($_POST['standard_opts']) && in_array('ISO 9001', $_POST['standard_opts'])) ? 'checked' : ''; ?>>
                                    <span>ISO 9001</span>
                                </label>
                                <label class="standard-pill">
                                    <input type="checkbox" name="standard_opts[]" value="ISO 14001" <?php echo (isset($_POST['standard_opts']) && in_array('ISO 14001', $_POST['standard_opts'])) ? 'checked' : ''; ?>>
                                    <span>ISO 14001</span>
                                </label>
                                <label class="standard-pill">
                                    <input type="checkbox" name="standard_opts[]" value="ISO 45001" <?php echo (isset($_POST['standard_opts']) && in_array('ISO 45001', $_POST['standard_opts'])) ? 'checked' : ''; ?>>
                                    <span>ISO 45001</span>
                                </label>
                                <label class="standard-pill">
                                    <input type="checkbox" id="standard_other_check" <?php echo !empty($_POST['standard_other']) ? 'checked' : ''; ?>>
                                    <span>Other</span>
                                </label>
                            </div>
                            <input type="text" name="standard_other" id="standard_other_input" class="form-control" style="margin-top: 12px; display: <?php echo !empty($_POST['standard_other']) ? 'block' : 'none'; ?>;" placeholder="Specify other standard (e.g. ISO 27001)..." value="<?php echo htmlspecialchars($_POST['standard_other'] ?? ''); ?>">
                            
                            <script>
                            document.getElementById('standard_other_check').addEventListener('change', function() {
                                var otherInput = document.getElementById('standard_other_input');
                                if(this.checked) {
                                    otherInput.style.display = 'block';
                                    otherInput.focus();
                                } else {
                                    otherInput.style.display = 'none';
                                    otherInput.value = '';
                                }
                            });
                            </script>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Date of audit <span style="color:#ef4444;">*</span></label>
                            <input type="date" name="audit_date" id="audit_date" class="form-control" value="<?php echo htmlspecialchars($_POST['audit_date'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Nonconformance reference no <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="nc_reference_no" class="form-control" placeholder="e.g. NC-01 / AITF-NC-2026" value="<?php echo htmlspecialchars($_POST['nc_reference_no'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Date of corrective action <span style="color:#ef4444;">*</span></label>
                            <input type="date" name="corrective_action_date" id="corrective_action_date" class="form-control" value="<?php echo htmlspecialchars($_POST['corrective_action_date'] ?? ''); ?>" required>
                        </div>
                        
                        <script>
                        document.getElementById('audit_date').addEventListener('change', function() {
                            document.getElementById('corrective_action_date').min = this.value;
                        });
                        </script>
                    </div>

                    <!-- Note Box -->
                    <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-left: 4px solid #0284c7; padding: 16px 20px; border-radius: 12px; margin-top: 10px; display: flex; gap: 14px; align-items: flex-start;">
                        <i class="fa fa-info-circle" style="color: #0284c7; font-size: 18px; margin-top: 2px; flex-shrink: 0;"></i>
                        <p style="font-size: 13.5px; color: #0369a1; font-weight: 500; margin: 0; line-height: 1.6;"><strong>*Note:</strong> Auditee organization shall provide a plan for correction and corrective action with root cause analysis for the Minor and MAJOR Nonconformance identified during audit. However, Submission of evidence of the implementation is compulsory for the MAJOR NC.</p>
                    </div>
                </div>

                <!-- 2. Root Cause Analysis & Corrective Actions -->
                <div class="nc-form-section">
                    <h4 style="margin-bottom: 6px;">
                        <span class="nc-section-icon"><i class="fa fa-search"></i></span>
                        *ROOT CAUSE ANALYSIS, PLAN FOR CORRECTION AND CORRECTIVE ACTION (Submission by Auditee Organization)
                    </h4>
                    <p style="font-size: 13.5px; color: #64748b; font-weight: 600; margin-bottom: 22px; padding-left: 50px;">Please refer to Form F 7.24 – for non-conformance issued.</p>
                    
                    <div class="form-group">
                        <label>Root-cause <span style="color:#ef4444;">*</span></label>
                        <textarea name="root_cause" class="form-control" placeholder="Describe the root cause analysis findings in detail..." required><?php echo htmlspecialchars($_POST['root_cause'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Correction <span style="color:#ef4444;">*</span></label>
                        <textarea name="correction" class="form-control" placeholder="Describe the immediate correction taken..." required><?php echo htmlspecialchars($_POST['correction'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Corrective Action (action to prevent recurrence) <span style="color:#ef4444;">*</span></label>
                        <textarea name="corrective_action" class="form-control" placeholder="Describe the long-term corrective action planned or taken to prevent recurrence..." required><?php echo htmlspecialchars($_POST['corrective_action'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- 3. Person Responsible -->
                <div class="nc-form-section">
                    <h4>
                        <span class="nc-section-icon"><i class="fa fa-user"></i></span>
                        ORGANIZATION PERSON RESPONSIBLE FOR IMPLEMENTING CORRECTIVE ACTION
                    </h4>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Name <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="sign_name" class="form-control" placeholder="Enter authorized person's full name" value="<?php echo htmlspecialchars($_POST['sign_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Designation <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="sign_designation" class="form-control" placeholder="e.g. Quality Manager / Director" value="<?php echo htmlspecialchars($_POST['sign_designation'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Proposed Completion Dt. <span style="color:#ef4444;">*</span></label>
                            <input type="date" name="proposed_completion_date" class="form-control" value="<?php echo htmlspecialchars($_POST['proposed_completion_date'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Signature with Company's Stamp <span style="color:#ef4444;">*</span></label>
                            <div style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px; padding: 12px 16px;">
                                <input type="file" name="signature_image" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf" style="font-size: 14px; color: #475569; width: 100%;" required>
                                <div style="color: #0369a1; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 6px 10px; margin-top: 8px; font-size: 12.5px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa fa-info-circle" style="color: #0284c7; flex-shrink: 0;"></i>
                                    <span>*Note: Only signature with company's duly stamp will be accepted. (JPG, PNG, PDF &bull; Max 2MB)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Optional Attachments -->
                <div class="nc-form-section">
                    <h4>
                        <span class="nc-section-icon"><i class="fa fa-paperclip"></i></span>
                        OPTIONAL ATTACHMENTS (Supporting Documents)
                    </h4>
                    <p style="font-size: 13.5px; color: #64748b; margin-top: -10px; margin-bottom: 22px; padding-left: 50px;">
                        Upload up to 2 optional supporting files (e.g. Evidence of implementation, Root cause analysis diagrams, CAPA documents).
                    </p>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Attachment 1 <span style="font-weight: normal; color: #64748b; font-size: 13px;">(Optional)</span></label>
                            <div style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px; padding: 14px 18px;">
                                <input type="file" name="attachment1" class="form-control-file" accept=".png,.jpg,.jpeg,.pdf,.doc,.docx" style="font-size: 14px; color: #475569; width: 100%;">
                                <small style="color: #64748b; display: block; margin-top: 8px;"><i class="fa fa-shield"></i> Allowed: PNG, JPG, JPEG, PDF, Word (.doc, .docx) &bull; Max: 5MB</small>
                            </div>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Attachment 2 <span style="font-weight: normal; color: #64748b; font-size: 13px;">(Optional)</span></label>
                            <div style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px; padding: 14px 18px;">
                                <input type="file" name="attachment2" class="form-control-file" accept=".png,.jpg,.jpeg,.pdf,.doc,.docx" style="font-size: 14px; color: #475569; width: 100%;">
                                <small style="color: #64748b; display: block; margin-top: 8px;"><i class="fa fa-shield"></i> Allowed: PNG, JPG, JPEG, PDF, Word (.doc, .docx) &bull; Max: 5MB</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Organisation Mail ID Verification -->
                <div class="nc-form-section" id="nc_email_verification_section" style="border: 1.5px solid #0284c7; background: #f0f9ff; border-radius: 16px; padding: 28px 32px;">
                    <h4 style="color: #0369a1; border-bottom: 2px solid #bae6fd; padding-bottom: 12px; margin-bottom: 18px;">
                        <span class="nc-section-icon" style="background: #0284c7; color: #fff;"><i class="fa fa-shield"></i></span>
                        Organisation Mail ID & Email Verification
                    </h4>
                    <p style="font-size: 14px; color: #0369a1; margin-bottom: 20px; font-weight: 500;">
                        Please provide your registered Organisation Mail ID. You must verify it before submitting this form.
                    </p>
                    
                    <div style="max-width: 580px;">
                        <!-- Email Input & Verify Mail Button -->
                        <div class="form-group" id="nc_email_input_wrap" style="margin-bottom: 14px;">
                            <label style="font-weight: 600; color: #0f172a; font-size: 14px;">Organisation Mail ID <span style="color:#ef4444;">*</span></label>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <input type="email" name="email" id="nc_email" class="form-control" placeholder="name@company.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" style="flex: 1; min-width: 240px; background: #fff;" required>
                                <button type="button" id="btn_nc_verify_email" class="btn" style="background: #0284c7; color: #fff; font-weight: 600; padding: 10px 22px; border-radius: 10px; border: none; white-space: nowrap; transition: all 0.2s;">
                                    <i class="fa fa-check-circle"></i> Verify Mail
                                </button>
                            </div>
                        </div>

                        <!-- 2-3s Verifying Effect Loader -->
                        <div id="nc_verifying_loader" style="display: none; align-items: center; gap: 12px; background: #eff6ff; border: 1.5px solid #93c5fd; border-radius: 10px; padding: 13px 18px; color: #1e40af; font-size: 14px; font-weight: 600; margin-bottom: 14px;">
                            <i class="fa fa-circle-o-notch fa-spin" style="font-size: 20px; color: #0284c7;"></i>
                            <span id="nc_loader_text">Verifying your mail ID with system records...</span>
                        </div>

                        <!-- Status Message -->
                        <div id="nc_otp_msg" style="font-size: 13.5px; font-weight: 600; margin-bottom: 14px; display: none;"></div>

                        <!-- Verified Badge -->
                        <div id="nc_verified_badge" style="display: none; background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; border-radius: 12px; padding: 14px 18px; font-weight: 600; font-size: 14px; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <i class="fa fa-check-circle" style="font-size: 24px; color: #10b981; flex-shrink: 0;"></i>
                                <div>
                                    <div style="color: #065f46; font-size: 14.5px; font-weight: 700;">Mail is verified, proceed to submit form.</div>
                                    <div id="nc_verified_text" style="color: #047857; font-size: 13px; font-weight: 500; margin-top: 2px;"></div>
                                </div>
                            </div>
                            <button type="button" id="btn_nc_change_email" style="background: none; border: none; color: #047857; text-decoration: underline; font-size: 13px; cursor: pointer; font-weight: 600; white-space: nowrap;">Change Email</button>
                        </div>
                        
                        <input type="hidden" name="is_email_verified" id="nc_is_email_verified" value="<?php echo (!empty($_SESSION['nc_closing_email_verified']) && !empty($_POST['email']) && $_SESSION['nc_closing_email_verified'] === $_POST['email']) ? '1' : '0'; ?>">
                    </div>
                </div>

                <!-- Email Not Eligible Modal -->
                <div id="nc_email_modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:99999; align-items:center; justify-content:center; padding:20px;">
                    <div style="background:#fff; border-radius:18px; max-width:440px; width:100%; padding:32px 28px; text-align:center; box-shadow:0 25px 50px -12px rgba(0,0,0,0.35); border:1px solid #fee2e2; animation: modalPop 0.25s ease-out;">
                        <div style="width:68px; height:68px; background:#fef2f2; border:2px solid #fecaca; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
                            <i class="fa fa-exclamation-triangle" style="font-size:32px; color:#dc2626;"></i>
                        </div>
                        <h4 style="font-size:20px; font-weight:700; color:#1e293b; margin-bottom:12px;">Email Verification Failed</h4>
                        <p style="font-size:15px; color:#475569; line-height:1.6; margin-bottom:24px;">
                            This mail is not valid. Kindly mention the mail ID that is present in your application form.
                        </p>
                        <button type="button" id="btn_nc_close_modal" style="width:100%; background:linear-gradient(135deg, #0284c7, #0369a1); color:#fff; font-weight:600; font-size:15px; padding:13px 20px; border-radius:10px; border:none; cursor:pointer; box-shadow:0 4px 14px rgba(2,132,199,0.35); transition:all 0.2s;">
                            <i class="fa fa-check"></i> OK, I Understood
                        </button>
                    </div>
                </div>

                <!-- Cloudflare Turnstile Captcha -->
                <?php if ($turnstileReady): ?>
                    <div class="form-group" style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 30px 0 10px;">
                        <div class="cf-turnstile"
                            data-sitekey="<?php echo htmlspecialchars($turnstileConfig['site_key'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-callback="ncTurnstileSolved"
                            data-expired-callback="ncTurnstileExpired"
                            data-error-callback="ncTurnstileExpired">
                        </div>
                        <small id="nc_turnstile_status" style="font-size: 13px; color: #64748b; margin-top: 6px; display: none;"></small>
                    </div>
                <?php endif; ?>

                <div class="text-center" style="margin-top: 35px; margin-bottom: 10px;">
                    <button type="submit" name="submit_nc_form" id="submit_btn" class="btn-submit" <?php echo (empty($_SESSION['nc_closing_email_verified']) || empty($_POST['email']) || $_SESSION['nc_closing_email_verified'] !== $_POST['email']) ? 'disabled' : ''; ?>>
                        <span id="btn_text"><i class="fa fa-paper-plane"></i> Submit NC Closing Form</span>
                        <span id="btn_spinner" style="display:none;"><i class="fa fa-spinner fa-spin"></i> Submitting your form...</span>
                    </button>
                    <div id="submit_btn_hint" style="margin-top: 12px; font-size: 13.5px; font-weight: 500; color: #64748b;">
                        <?php if (!empty($_SESSION['nc_closing_email_verified']) && !empty($_POST['email']) && $_SESSION['nc_closing_email_verified'] === $_POST['email']): ?>
                            <span style="color: #16a34a; font-weight: 600;"><i class="fa fa-check-circle"></i> Mail is verified, proceed to submit form.</span>
                        <?php else: ?>
                            <span style="color: #64748b;"><i class="fa fa-lock"></i> Submit button will be enabled once your Organisation Mail ID is verified above.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <script>
            // Turnstile Captcha Callbacks
            var isNcTurnstileSolved = false;
            function ncTurnstileSolved(token) {
                isNcTurnstileSolved = true;
                var st = document.getElementById('nc_turnstile_status');
                if (st) st.style.display = 'none';
            }
            function ncTurnstileExpired() {
                isNcTurnstileSolved = false;
            }

            // NC Closing Form Email Verification Handler
            document.addEventListener('DOMContentLoaded', function() {
                var btnVerify = document.getElementById('btn_nc_verify_email');
                var btnChange = document.getElementById('btn_nc_change_email');
                var emailInput = document.getElementById('nc_email');
                var otpMsg = document.getElementById('nc_otp_msg');
                var verifiedBadge = document.getElementById('nc_verified_badge');
                var isVerified = document.getElementById('nc_is_email_verified');
                var submitBtn = document.getElementById('submit_btn');
                var submitBtnHint = document.getElementById('submit_btn_hint');
                var verifyingLoader = document.getElementById('nc_verifying_loader');
                var loaderText = document.getElementById('nc_loader_text');
                var modal = document.getElementById('nc_email_modal');
                var btnCloseModal = document.getElementById('btn_nc_close_modal');

                function updateSubmitButtonState() {
                    if (isVerified.value === '1') {
                        submitBtn.disabled = false;
                        submitBtnHint.innerHTML = '<span style="color: #16a34a; font-weight: 600;"><i class="fa fa-check-circle"></i> Mail is verified, proceed to submit form.</span>';
                    } else {
                        submitBtn.disabled = true;
                        submitBtnHint.innerHTML = '<span style="color: #64748b;"><i class="fa fa-lock"></i> Submit button will be enabled once your Organisation Mail ID is verified above.</span>';
                    }
                }

                // Close Modal handler
                if (btnCloseModal && modal) {
                    btnCloseModal.addEventListener('click', function() {
                        modal.style.display = 'none';
                        if (emailInput) {
                            emailInput.focus();
                            emailInput.select();
                        }
                    });
                }

                // Initial state
                updateSubmitButtonState();
                if (isVerified.value === '1' && emailInput.value) {
                    verifiedBadge.style.display = 'flex';
                    document.getElementById('nc_verified_text').textContent = 'Verified Mail: ' + emailInput.value;
                    if (btnVerify) btnVerify.style.display = 'none';
                    emailInput.readOnly = true;
                    emailInput.style.backgroundColor = '#f1f5f9';
                }

                function verifyEmail() {
                    var email = emailInput.value.trim();

                    if (!email || email.indexOf('@') === -1 || email.indexOf('.') === -1) {
                        alert('Please enter a valid Organisation Mail ID.');
                        emailInput.focus();
                        return;
                    }

                    btnVerify.disabled = true;
                    btnVerify.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Verifying...';
                    otpMsg.style.display = 'none';
                    if (modal) modal.style.display = 'none';

                    // Show 2-3s verifying effect loader
                    if (verifyingLoader) {
                        verifyingLoader.style.display = 'flex';
                        loaderText.textContent = 'Verifying your mail ID with system records...';
                    }

                    // Progress text switch after 1.1s to give real fetching feel
                    var progressTimer = setTimeout(function() {
                        if (loaderText) {
                            loaderText.textContent = 'Checking registered client database...';
                        }
                    }, 1100);

                    var startTime = Date.now();
                    var formData = new FormData();
                    formData.append('email', email);

                    fetch('ajax_verify_otp_nc.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        var elapsed = Date.now() - startTime;
                        var waitTime = Math.max(0, 2400 - elapsed);

                        setTimeout(function() {
                            clearTimeout(progressTimer);
                            if (verifyingLoader) verifyingLoader.style.display = 'none';

                            if (res.success) {
                                isVerified.value = '1';
                                updateSubmitButtonState();
                                otpMsg.style.display = 'none';
                                btnVerify.style.display = 'none';
                                emailInput.readOnly = true;
                                emailInput.style.backgroundColor = '#f1f5f9';
                                verifiedBadge.style.display = 'flex';
                                document.getElementById('nc_verified_text').textContent = 'Verified Mail: ' + email;

                                // Auto-select Organization Name from DB (editable)
                                if (res.org_name) {
                                    var orgNameInput = document.getElementById('nc_org_name');
                                    if (orgNameInput) {
                                        orgNameInput.value = res.org_name;
                                        orgNameInput.classList.add('auto-filled-highlight');
                                        setTimeout(function() {
                                            orgNameInput.classList.remove('auto-filled-highlight');
                                        }, 2600);
                                        var autoHint = document.getElementById('nc_org_auto_filled');
                                        if (autoHint) autoHint.style.display = 'block';
                                    }
                                }
                            } else {
                                isVerified.value = '0';
                                updateSubmitButtonState();
                                btnVerify.disabled = false;
                                btnVerify.innerHTML = '<i class="fa fa-check-circle"></i> Verify Mail';

                                // Show the popup modal
                                if (modal) modal.style.display = 'flex';

                                // Also show inline error message
                                otpMsg.style.display = 'block';
                                otpMsg.style.color = '#ef4444';
                                otpMsg.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + res.message;
                            }
                        }, waitTime);
                    })
                    .catch(function() {
                        var elapsed = Date.now() - startTime;
                        var waitTime = Math.max(0, 2000 - elapsed);

                        setTimeout(function() {
                            clearTimeout(progressTimer);
                            if (verifyingLoader) verifyingLoader.style.display = 'none';
                            isVerified.value = '0';
                            updateSubmitButtonState();
                            otpMsg.style.display = 'block';
                            otpMsg.style.color = '#ef4444';
                            otpMsg.textContent = 'Verification error. Please try again.';
                            btnVerify.disabled = false;
                            btnVerify.innerHTML = '<i class="fa fa-check-circle"></i> Verify Mail';
                        }, waitTime);
                    });
                }

                if (btnVerify) btnVerify.addEventListener('click', verifyEmail);

                if (btnChange) {
                    btnChange.addEventListener('click', function() {
                        isVerified.value = '0';
                        updateSubmitButtonState();
                        emailInput.readOnly = false;
                        emailInput.style.backgroundColor = '#fff';
                        emailInput.focus();
                        verifiedBadge.style.display = 'none';
                        if (btnVerify) {
                            btnVerify.style.display = 'inline-block';
                            btnVerify.disabled = false;
                            btnVerify.innerHTML = '<i class="fa fa-check-circle"></i> Verify Mail';
                        }
                        otpMsg.style.display = 'none';
                    });
                }

                if (emailInput) {
                    emailInput.addEventListener('input', function() {
                        if (isVerified.value === '1') {
                            isVerified.value = '0';
                            updateSubmitButtonState();
                            verifiedBadge.style.display = 'none';
                            if (btnVerify) {
                                btnVerify.style.display = 'inline-block';
                                btnVerify.disabled = false;
                                btnVerify.innerHTML = '<i class="fa fa-check-circle"></i> Verify Mail';
                            }
                            otpMsg.style.display = 'none';
                        }
                    });
                }

                // Form submit handler
                document.getElementById('nc_form').addEventListener('submit', function(e) {
                    // Check email verification first
                    if (isVerified.value !== '1') {
                        e.preventDefault();
                        alert('Please verify your Organisation Mail ID before submitting.');
                        var sec = document.getElementById('nc_email_verification_section');
                        if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        if (emailInput) emailInput.focus();
                        return false;
                    }

                    // Check Captcha if present
                    var turnstileWidget = document.querySelector('.cf-turnstile');
                    if (turnstileWidget) {
                        var turnstileInput = document.querySelector('input[name="cf-turnstile-response"]');
                        if (!turnstileInput || !turnstileInput.value) {
                            e.preventDefault();
                            alert('Please complete the Captcha verification.');
                            turnstileWidget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            return false;
                        }
                    }

                    // Check standard selection
                    var checkboxes = document.querySelectorAll('input[name="standard_opts[]"]:checked');
                    var otherInput = document.getElementById('standard_other_input');
                    var isOtherChecked = document.getElementById('standard_other_check').checked;
                    
                    if (checkboxes.length === 0 && (!isOtherChecked || !otherInput.value.trim())) {
                        e.preventDefault();
                        alert('Please select at least one Standard or specify Other standard.');
                        return false;
                    }
                    
                    // Check dates validation
                    var auditDate = document.getElementById('audit_date').value;
                    var corrDate = document.getElementById('corrective_action_date').value;
                    if (auditDate && corrDate && corrDate < auditDate) {
                        e.preventDefault();
                        alert('Date of corrective action cannot be before the Date of audit.');
                        return false;
                    }

                    // Check mandatory signature with stamp
                    var sigFile = document.querySelector('input[name="signature_image"]');
                    if (!sigFile || !sigFile.files || !sigFile.files[0]) {
                        e.preventDefault();
                        alert("Please upload signature with company's duly stamp.");
                        if (sigFile) {
                            sigFile.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            sigFile.focus();
                        }
                        return false;
                    }
                    var sigExt = sigFile.files[0].name.split('.').pop().toLowerCase();
                    var allowedSigExts = ['jpg', 'jpeg', 'png', 'pdf'];
                    if (allowedSigExts.indexOf(sigExt) === -1) {
                        e.preventDefault();
                        alert('Invalid Signature file format. Allowed: JPG, PNG, PDF.');
                        return false;
                    }
                    if (sigFile.files[0].size > 2 * 1024 * 1024) {
                        e.preventDefault();
                        alert('Signature file size must be less than 2MB.');
                        return false;
                    }

                    // Check optional attachments (max 5MB, safe extensions)
                    var allowedDocExts = ['png', 'jpg', 'jpeg', 'pdf', 'doc', 'docx'];
                    var attachmentFields = [
                        { name: 'attachment1', label: 'Attachment 1' },
                        { name: 'attachment2', label: 'Attachment 2' }
                    ];

                    for (var i = 0; i < attachmentFields.length; i++) {
                        var attInput = document.querySelector('input[name="' + attachmentFields[i].name + '"]');
                        if (attInput && attInput.files && attInput.files[0]) {
                            var fileObj = attInput.files[0];
                            var fileExt = fileObj.name.split('.').pop().toLowerCase();
                            if (allowedDocExts.indexOf(fileExt) === -1) {
                                e.preventDefault();
                                alert('Invalid file format in ' + attachmentFields[i].label + '. Only PNG, JPG, JPEG, PDF, and Word (.doc, .docx) are allowed.');
                                return false;
                            }
                            if (fileObj.size > 5 * 1024 * 1024) {
                                e.preventDefault();
                                alert(attachmentFields[i].label + ' file size must be less than 5MB.');
                                return false;
                            }
                        }
                    }
                    
                    var btn = document.getElementById('submit_btn');
                    var btnText = document.getElementById('btn_text');
                    var btnSpinner = document.getElementById('btn_spinner');
                    
                    btn.style.opacity = '0.8';
                    btn.style.cursor = 'wait';
                    btnText.style.display = 'none';
                    btnSpinner.style.display = 'inline-block';
                });
            });
            </script>
        </div>
    </div>

    <?php include './footerSource.php'; ?>
    <script src="js/outer/jquery.min.js"></script>
    <script src="js/outer/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>

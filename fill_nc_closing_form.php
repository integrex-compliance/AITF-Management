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
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
    $errors[] = "Database setup error: " . $e->getMessage();
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
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
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

    $signature_image_path = '';
    if (isset($_FILES['signature_image']) && $_FILES['signature_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['signature_image'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Signature file must be JPG, PNG, or PDF";
        }
        if ($file['size'] > $max_size) {
            $errors[] = "Signature file size must be less than 2MB";
        }

        if (empty($errors)) {
            $upload_dir = 'uploads/signatures/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'nc_sig_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $signature_image_path = $upload_path;
            } else {
                $errors[] = "Failed to upload signature image. Please try again.";
            }
        }
    } elseif (isset($_FILES['signature_image']) && $_FILES['signature_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "Signature image upload error. Please try again.";
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO nc_closing_forms (
                org_name, email, standard, audit_date, nc_reference_no, corrective_action_date,
                root_cause, correction, corrective_action,
                sign_name, sign_designation, proposed_completion_date, signature_image,
                read_flag, created_at
            ) VALUES (
                :org_name, :email, :standard, :audit_date, :nc_reference_no, :corrective_action_date,
                :root_cause, :correction, :corrective_action,
                :sign_name, :sign_designation, :proposed_completion_date, :signature_image,
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
                ':signature_image' => $signature_image_path
            ]);
            $last_insert_id = $db->lastInsertId();

            try {
                require_once __DIR__ . '/testemail.php';
                $emailSender = new AITFServiceEmailSender();
                
                // 1. Send mail to client
                if (!empty($email)) {
                    $clientSubject = "Confirmation: NC Closing Form Received";
                    $clientBody = "<p>Dear " . htmlspecialchars($sign_name) . ",</p>
                                   <p>This is a confirmation mail that we have received your nc clousing form and our team is reviewing it.</p>
                                   <p>meanwhile if you need any help kindly feel free to connect with us</p>
                                   <p>Best regards,<br>AITF Certification Services</p>";
                    $emailSender->sendEmail($email, $sign_name, $clientBody, $clientSubject);
                }
                
                // 2. Send mail to admin
                $adminEmail = "certification@aitf.co.in";
                $adminSubject = htmlspecialchars($org_name) . " NC Closing Form Received";
                $adminBody = "<p>Hello,</p>
                              <p>New NC Closing Form received from <b>" . htmlspecialchars($org_name) . "</b>.</p>
                              <p>Please review it from the dashboard.</p>";
                $emailSender->sendEmail($adminEmail, "AITF Admin", $adminBody, $adminSubject);
            } catch (Exception $e) {
                error_log("Failed to send emails for NC Closing form: " . $e->getMessage());
            }

            $successMessage = "NC Closing form submitted successfully!";
            $_POST = [];
        } catch (PDOException $e) {
            error_log("Database insert error: " . $e->getMessage());
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

$pageIdentifier = "NC Closing Form";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NC Closing Form - AITF</title>
    <?php if ($turnstileReady): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <style>
        body {
            background: linear-gradient(-45deg, #0f2027, #203a43, #2c5364);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .nc-form-wrapper {
            max-width: 1100px;
            margin: 30px auto;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            width: 100%;
            padding: 0 15px;
        }

        .nc-form-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.10);
            padding: 40px 50px;
            border: 2px solid #111827;
        }

        .nc-form-header {
            background: #ffffff;
            color: #111827;
            padding: 20px 15px;
            border-radius: 12px;
            margin-bottom: 35px;
            text-align: center;
            border: 2px solid #111827;
        }

        .nc-form-header h3 { margin: 0; font-size: 28px; font-weight: 800; letter-spacing: 0.2px; }
        .nc-form-header p { margin: 10px 0 0 0; font-size: 16px; font-weight: 700; text-decoration: underline; }

        .nc-form-section {
            border: 2px solid #111827;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 12px;
            background: #ffffff;
        }

        .nc-form-section h4 {
            margin-top: 0;
            color: #111827;
            font-size: 22px;
            font-weight: 700;
            border-bottom: 2px solid #111827;
            padding-bottom: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group { margin-bottom: 25px; }
        .form-group label { font-weight: 600; color: #111827; font-size: 16px; margin-bottom: 10px; display: block; }
        .form-control { border: 2px solid #111827; border-radius: 8px; padding: 12px 15px; font-size: 15px; transition: all 0.3s ease; width: 100%; }
        .form-control:focus { border-color: #111827; box-shadow: 0 0 0 0.25rem rgba(17, 24, 39, 0.15); outline: none; }
        textarea.form-control { min-height: 120px; resize: vertical; }

        .btn-submit { background: #111827; color: #ffffff; border: none; padding: 16px 40px; font-size: 18px; font-weight: 700; border-radius: 10px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(17, 24, 39, 0.35); }
        .btn-submit:hover { background: #000000; transform: translateY(-2px); box-shadow: 0 10px 30px rgba(17, 24, 39, 0.5); }
        
        .alert { padding: 20px; margin-bottom: 25px; border-radius: 10px; font-size: 15px; }
        .alert-danger { background: #fee2e2; border: 2px solid #fecaca; color: #991b1b; }
        .alert-success { background: #dcfce7; border: 2px solid #16a34a; color: #14532d; }
    </style>

    <div class="nc-form-wrapper" style="margin-top: 100px; margin-bottom: 50px;">
        <div class="nc-form-card">
            <div class="nc-form-header">
                <div style="display:flex; align-items:center; gap:14px; justify-content:center; flex-wrap:wrap;">
                    <div style="flex:0 0 auto;">
                        <img src="images/aitf.jpg" alt="AITF" style="height:58px; width:auto; display:block;">
                    </div>
                    <div style="flex:1 1 520px; min-width: 260px;">
                        <h3>AITF MANAGEMENT CERTIFICATION LIMITED</h3>
                        <p>NONCONFORMANCE CLOSER REPORT</p>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Please correct the following errors:</strong>
                    <ul style="margin: 10px 0 0 20px;">
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
                <div class="nc-form-section">
                    <h4><i class="fa fa-building"></i> General Information</h4>
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <label>Organization Name <span style="color:red">*</span></label>
                            <input type="text" name="org_name" class="form-control" value="<?php echo htmlspecialchars($_POST['org_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Organisation Mail ID <span style="color:red">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Standard <span style="color:red">*</span></label>
                            <div style="display:flex; flex-wrap:wrap; gap:15px; margin-top:5px;">
                                <label style="font-weight: normal; cursor: pointer;"><input type="checkbox" name="standard_opts[]" value="ISO 9001" <?php echo (isset($_POST['standard_opts']) && in_array('ISO 9001', $_POST['standard_opts'])) ? 'checked' : ''; ?>> ISO 9001</label>
                                <label style="font-weight: normal; cursor: pointer;"><input type="checkbox" name="standard_opts[]" value="ISO 14001" <?php echo (isset($_POST['standard_opts']) && in_array('ISO 14001', $_POST['standard_opts'])) ? 'checked' : ''; ?>> ISO 14001</label>
                                <label style="font-weight: normal; cursor: pointer;"><input type="checkbox" name="standard_opts[]" value="ISO 45001" <?php echo (isset($_POST['standard_opts']) && in_array('ISO 45001', $_POST['standard_opts'])) ? 'checked' : ''; ?>> ISO 45001</label>
                                <label style="font-weight: normal; cursor: pointer;"><input type="checkbox" id="standard_other_check" <?php echo !empty($_POST['standard_other']) ? 'checked' : ''; ?>> Other</label>
                            </div>
                            <input type="text" name="standard_other" id="standard_other_input" class="form-control" style="margin-top: 10px; display: <?php echo !empty($_POST['standard_other']) ? 'block' : 'none'; ?>;" placeholder="Specify other standard..." value="<?php echo htmlspecialchars($_POST['standard_other'] ?? ''); ?>">
                            
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
                            <label>Date of audit <span style="color:red">*</span></label>
                            <input type="date" name="audit_date" id="audit_date" class="form-control" value="<?php echo htmlspecialchars($_POST['audit_date'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Nonconformance reference no <span style="color:red">*</span></label>
                            <input type="text" name="nc_reference_no" class="form-control" value="<?php echo htmlspecialchars($_POST['nc_reference_no'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Date of corrective action <span style="color:red">*</span></label>
                            <input type="date" name="corrective_action_date" id="corrective_action_date" class="form-control" value="<?php echo htmlspecialchars($_POST['corrective_action_date'] ?? ''); ?>" required>
                        </div>
                        
                        <script>
                        document.getElementById('audit_date').addEventListener('change', function() {
                            document.getElementById('corrective_action_date').min = this.value;
                        });
                        </script>
                    </div>
                    <p style="font-size: 14px; color: #4b5563; font-style: italic;">*Note- Auditee organisation shall provide proposed correction and corrective action with root cause analysis if nonconformance/s identified during audit. Please refer form F 7.24 – for non-conformance details.</p>
                </div>

                <div class="nc-form-section">
                    <h4><i class="fa fa-search"></i> AUDITEE ORGANIZATION'S ROOT CAUSE ANALYSIS, PROPOSED CORRECTION, CORRECTIVE ACTION*</h4>
                    <div class="form-group">
                        <label>Root-cause: <span style="color:red">*</span></label>
                        <textarea name="root_cause" class="form-control" required><?php echo htmlspecialchars($_POST['root_cause'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Correction: <span style="color:red">*</span></label>
                        <textarea name="correction" class="form-control" required><?php echo htmlspecialchars($_POST['correction'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Corrective Action (action to prevent recurrence): <span style="color:red">*</span></label>
                        <textarea name="corrective_action" class="form-control" required><?php echo htmlspecialchars($_POST['corrective_action'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="nc-form-section">
                    <h4><i class="fa fa-user"></i> ORGANIZATION PERSON RESPONSIBLE FOR IMPLEMENTING CORRECTIVE ACTION</h4>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Name <span style="color:red">*</span></label>
                            <input type="text" name="sign_name" class="form-control" value="<?php echo htmlspecialchars($_POST['sign_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Designation <span style="color:red">*</span></label>
                            <input type="text" name="sign_designation" class="form-control" value="<?php echo htmlspecialchars($_POST['sign_designation'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Proposed Completion Dt. <span style="color:red">*</span></label>
                            <input type="date" name="proposed_completion_date" class="form-control" value="<?php echo htmlspecialchars($_POST['proposed_completion_date'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Signature Image (Optional, max 2MB)</label>
                            <input type="file" name="signature_image" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small style="color: #6b7280;">Upload an image or PDF of the signature.</small>
                        </div>
                    </div>
                </div>

                <?php if ($turnstileReady): ?>
                    <div class="form-group" style="display: flex; justify-content: center; margin: 30px 0;">
                        <div class="cf-turnstile" data-sitekey="<?php echo htmlspecialchars($turnstileConfig['site_key']); ?>"></div>
                    </div>
                <?php endif; ?>

                <div class="text-center" style="margin-top: 40px;">
                    <button type="submit" name="submit_nc_form" id="submit_btn" class="btn-submit">
                        <span id="btn_text"><i class="fa fa-paper-plane"></i> Submit NC Closing Form</span>
                        <span id="btn_spinner" style="display:none;"><i class="fa fa-spinner fa-spin"></i> Submitting your form...</span>
                    </button>
                </div>
            </form>
            
            <script>
            document.getElementById('nc_form').addEventListener('submit', function() {
                var btn = document.getElementById('submit_btn');
                var btnText = document.getElementById('btn_text');
                var btnSpinner = document.getElementById('btn_spinner');
                
                // Keep the button functional for the backend but change appearance
                btn.style.opacity = '0.8';
                btn.style.cursor = 'wait';
                btnText.style.display = 'none';
                btnSpinner.style.display = 'inline-block';
                
                // Allow the submit event to proceed, just update UI
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

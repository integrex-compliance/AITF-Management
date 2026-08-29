

php_code = r'''<?php
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

 = [];
 = '';

 = __DIR__ . '/include/turnstile_helper.php';
if (file_exists()) {
    require_once ;
}
 = function_exists('aitf_get_turnstile_config') ? aitf_get_turnstile_config() : [
    'site_key' => '',
    'secret_key' => ''
];
 = function_exists('aitf_is_turnstile_ready') ? aitf_is_turnstile_ready() : false;

// Create table if not exists
 = "CREATE TABLE IF NOT EXISTS nc_closing_forms (
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
    ->exec();
} catch (PDOException ) {
    error_log("Table creation error: " . ->getMessage());
    [] = "Database setup error: " . ->getMessage();
}

// Handle form submission
if (['REQUEST_METHOD'] === 'POST' && isset(['submit_nc_form'])) {
     = trim(['cf-turnstile-response'] ?? '');
    if ( && empty()) {
        [] = "Please complete the captcha verification.";
    } elseif ( && function_exists('aitf_verify_turnstile') && !aitf_verify_turnstile(, )) {
        [] = "Captcha verification failed" . ( ? ": " : ".");
    }

     = htmlspecialchars(trim(['org_name'] ?? ''), ENT_QUOTES, 'UTF-8');
     = filter_var(trim(['email'] ?? ''), FILTER_SANITIZE_EMAIL);
     = htmlspecialchars(trim(['standard'] ?? ''), ENT_QUOTES, 'UTF-8');
     = htmlspecialchars(trim(['audit_date'] ?? ''), ENT_QUOTES, 'UTF-8');
     = htmlspecialchars(trim(['nc_reference_no'] ?? ''), ENT_QUOTES, 'UTF-8');
     = htmlspecialchars(trim(['corrective_action_date'] ?? ''), ENT_QUOTES, 'UTF-8');
    
     = htmlspecialchars(trim(['root_cause'] ?? ''), ENT_QUOTES, 'UTF-8');
     = htmlspecialchars(trim(['correction'] ?? ''), ENT_QUOTES, 'UTF-8');
     = htmlspecialchars(trim(['corrective_action'] ?? ''), ENT_QUOTES, 'UTF-8');
    
     = htmlspecialchars(trim(['sign_name'] ?? ''), ENT_QUOTES, 'UTF-8');
     = htmlspecialchars(trim(['sign_designation'] ?? ''), ENT_QUOTES, 'UTF-8');
     = htmlspecialchars(trim(['proposed_completion_date'] ?? ''), ENT_QUOTES, 'UTF-8');

    if (empty()) [] = "Organization name is required";
    if (empty() || !filter_var(, FILTER_VALIDATE_EMAIL)) [] = "Valid email is required";
    if (empty()) [] = "Standard is required";
    if (empty()) [] = "Date of audit is required";
    if (empty()) [] = "Nonconformance reference no is required";
    if (empty()) [] = "Date of corrective action is required";
    if (empty()) [] = "Root cause is required";
    if (empty()) [] = "Correction is required";
    if (empty()) [] = "Corrective Action is required";
    if (empty()) [] = "Name is required";
    if (empty()) [] = "Designation is required";
    if (empty()) [] = "Proposed Completion Date is required";

     = '';
    if (isset(['signature_image']) && ['signature_image']['error'] === UPLOAD_ERR_OK) {
         = ['signature_image'];
         = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
         = 2 * 1024 * 1024; // 2MB

        if (!in_array(['type'], )) {
            [] = "Signature file must be JPG, PNG, or PDF";
        }
        if (['size'] > ) {
            [] = "Signature file size must be less than 2MB";
        }

        if (empty()) {
             = 'uploads/signatures/';
            if (!file_exists()) mkdir(, 0777, true);
             = pathinfo(['name'], PATHINFO_EXTENSION);
             = 'nc_sig_' . time() . '_' . uniqid() . '.' . ;
             =  . ;

            if (move_uploaded_file(['tmp_name'], )) {
                 = ;
            } else {
                [] = "Failed to upload signature image. Please try again.";
            }
        }
    } elseif (isset(['signature_image']) && ['signature_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        [] = "Signature image upload error. Please try again.";
    }

    if (empty()) {
        try {
             = "INSERT INTO nc_closing_forms (
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

             = ->prepare();
            ->execute([
                ':org_name' => ,
                ':email' => ,
                ':standard' => ,
                ':audit_date' => ,
                ':nc_reference_no' => ,
                ':corrective_action_date' => ,
                ':root_cause' => ,
                ':correction' => ,
                ':corrective_action' => ,
                ':sign_name' => ,
                ':sign_designation' => ,
                ':proposed_completion_date' => ,
                ':signature_image' => 
            ]);
             = ->lastInsertId();

            try {
                require_once __DIR__ . '/testemail.php';
                 = new AITFServiceEmailSender();
                
                // 1. Send mail to client
                if (!empty()) {
                     = "Confirmation: NC Closing Form Received";
                     = "<p>Dear " . htmlspecialchars() . ",</p>
                                   <p>This is a confirmation mail that we have received your NC closing form and our team is reviewing it.</p>
                                   <p>Meanwhile, if you need any help, kindly feel free to connect with us.</p>
                                   <p>Best regards,<br>AITF Certification Services</p>";
                    ->sendEmail(, , , );
                }
                
                // 2. Send mail to admin
                 = "certification@aitf.co.in";
                 = htmlspecialchars() . " NC Closing Form Received";
                 = "<p>Hello,</p>
                              <p>New NC Closing Form received from <b>" . htmlspecialchars() . "</b>.</p>
                              <p>Please review it from the dashboard.</p>";
                ->sendEmail(, "AITF Admin", , );
            } catch (Exception ) {
                error_log("Failed to send emails for NC Closing form: " . ->getMessage());
            }

             = "NC Closing form submitted successfully!";
             = [];
        } catch (PDOException ) {
            error_log("Database insert error: " . ->getMessage());
            [] = "Database error: " . ->getMessage();
        }
    }
}

 = "NC Closing Form";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NC Closing Form - AITF</title>
    <?php if (): ?>
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

            <?php if (!empty()): ?>
                <div class="alert alert-danger">
                    <strong>Please correct the following errors:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php foreach ( as ): ?>
                            <li><?php echo ; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty()): ?>
                <div class="alert alert-success">
                    <strong><i class="fa fa-check-circle"></i> <?php echo ; ?></strong>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="nc-form-section">
                    <h4><i class="fa fa-building"></i> General Information</h4>
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <label>Organization Name <span style="color:red">*</span></label>
                            <input type="text" name="org_name" class="form-control" value="<?php echo htmlspecialchars(['org_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Email Address <span style="color:red">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars(['email'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Standard <span style="color:red">*</span></label>
                            <input type="text" name="standard" class="form-control" placeholder="e.g. ISO 9001:2015" value="<?php echo htmlspecialchars(['standard'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Date of audit <span style="color:red">*</span></label>
                            <input type="date" name="audit_date" class="form-control" value="<?php echo htmlspecialchars(['audit_date'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Nonconformance reference no <span style="color:red">*</span></label>
                            <input type="text" name="nc_reference_no" class="form-control" value="<?php echo htmlspecialchars(['nc_reference_no'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Date of corrective action <span style="color:red">*</span></label>
                            <input type="date" name="corrective_action_date" class="form-control" value="<?php echo htmlspecialchars(['corrective_action_date'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-left: 4px solid #0284c7; padding: 12px 16px; border-radius: 6px; margin-top: 15px;">
                        <p style="font-size: 13.5px; color: #0284c7; font-weight: 500; margin: 0; line-height: 1.5;">*Note: Auditee organization shall provide a plan for correction and corrective action with root cause analysis for the Minor and MAJOR Nonconformance identified during audit. However, Submission of evidence of the implementation is compulsory for the MAJOR NC.</p>
                    </div>
                </div>

                <div class="nc-form-section">
                    <h4 style="margin-bottom: 4px;"><i class="fa fa-search"></i> *ROOT CAUSE ANALYSIS, PLAN FOR CORRECTION AND CORRECTIVE ACTION (Submission by Auditee Organization)</h4>
                    <p style="font-size: 13px; color: #374151; font-weight: 600; margin-bottom: 18px;">Please refer to Form F 7.24 – for non-conformance issued.</p>
                    <div class="form-group">
                        <label>Root-cause: <span style="color:red">*</span></label>
                        <textarea name="root_cause" class="form-control" required><?php echo htmlspecialchars(['root_cause'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Correction: <span style="color:red">*</span></label>
                        <textarea name="correction" class="form-control" required><?php echo htmlspecialchars(['correction'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Corrective Action (action to prevent recurrence): <span style="color:red">*</span></label>
                        <textarea name="corrective_action" class="form-control" required><?php echo htmlspecialchars(['corrective_action'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="nc-form-section">
                    <h4><i class="fa fa-user"></i> ORGANIZATION PERSON RESPONSIBLE FOR IMPLEMENTING CORRECTIVE ACTION</h4>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Name <span style="color:red">*</span></label>
                            <input type="text" name="sign_name" class="form-control" value="<?php echo htmlspecialchars(['sign_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Designation <span style="color:red">*</span></label>
                            <input type="text" name="sign_designation" class="form-control" value="<?php echo htmlspecialchars(['sign_designation'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Proposed Completion Dt. <span style="color:red">*</span></label>
                            <input type="date" name="proposed_completion_date" class="form-control" value="<?php echo htmlspecialchars(['proposed_completion_date'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Signature Image (Optional, max 2MB)</label>
                            <input type="file" name="signature_image" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small style="color: #6b7280;">Upload an image or PDF of the signature.</small>
                        </div>
                    </div>
                </div>

                <?php if (): ?>
                    <div class="form-group" style="display: flex; justify-content: center; margin: 30px 0;">
                        <div class="cf-turnstile" data-sitekey="<?php echo htmlspecialchars(['site_key']); ?>"></div>
                    </div>
                <?php endif; ?>

                <div class="text-center" style="margin-top: 40px;">
                    <button type="submit" name="submit_nc_form" class="btn-submit">
                        <i class="fa fa-paper-plane"></i> Submit NC Closing Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include './footerSource.php'; ?>
    <script src="js/outer/jquery.min.js"></script>
    <script src="js/outer/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>
'''



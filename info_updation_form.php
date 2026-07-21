<?php
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$errors = [];
$successMessage = '';

// Create table if not exists
$createTableSQL = "CREATE TABLE IF NOT EXISTS information_updation_forms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    org_name VARCHAR(255) NOT NULL,
    certificate_number VARCHAR(100) NOT NULL,
    contact_person VARCHAR(255) NOT NULL,
    contact_designation VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    company_name_change ENUM('Yes', 'No') DEFAULT 'No',
    company_name_details TEXT NULL,
    ownership_change ENUM('Yes', 'No') DEFAULT 'No',
    ownership_details TEXT NULL,
    legal_status_change ENUM('Yes', 'No') DEFAULT 'No',
    legal_status_details TEXT NULL,
    address_change ENUM('Yes', 'No') DEFAULT 'No',
    address_details TEXT NULL,
    site_addition_change ENUM('Yes', 'No') DEFAULT 'No',
    site_addition_details TEXT NULL,
    office_address_change ENUM('Yes', 'No') DEFAULT 'No',
    office_address_details TEXT NULL,
    phone_change ENUM('Yes', 'No') DEFAULT 'No',
    phone_change_details TEXT NULL,
    email_change ENUM('Yes', 'No') DEFAULT 'No',
    email_change_details TEXT NULL,
    contact_person_change ENUM('Yes', 'No') DEFAULT 'No',
    contact_person_change_details TEXT NULL,
    activity_change ENUM('Yes', 'No') DEFAULT 'No',
    activity_details TEXT NULL,
    employee_change ENUM('Yes', 'No') DEFAULT 'No',
    employee_details TEXT NULL,
    consultancy_services ENUM('Yes', 'No') DEFAULT 'No',
    consultancy_details TEXT NULL,
    sign_name VARCHAR(255) NOT NULL,
    sign_designation VARCHAR(255) NOT NULL,
    sign_date VARCHAR(50) NOT NULL,
    signature_image VARCHAR(255) NULL,
    read_flag TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL,
    INDEX idx_created_at (created_at),
    INDEX idx_read_flag (read_flag),
    INDEX idx_org_name (org_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->exec($createTableSQL);
    error_log("Table creation/check completed successfully");

    // Try to add signature_image column if it doesn't exist
    try {
        $db->exec("ALTER TABLE information_updation_forms ADD COLUMN signature_image VARCHAR(255) NULL AFTER sign_date");
        error_log("Signature column added successfully");
    } catch (PDOException $e) {
        // Column might already exist, ignore error
        error_log("Signature column already exists or error: " . $e->getMessage());
    }

    try {
        $db->exec("ALTER TABLE information_updation_forms ADD COLUMN aitf_review TEXT NULL AFTER signature_image");
        error_log("AITF review column added successfully");
    } catch (PDOException $e) {
        error_log("AITF review column already exists or error: " . $e->getMessage());
    }
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
    $errors[] = "Database setup error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_info_form'])) {
    // Validation and sanitization
    $org_name = htmlspecialchars(trim($_POST['org_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $certificate_number = htmlspecialchars(trim($_POST['certificate_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    $contact_person = htmlspecialchars(trim($_POST['contact_person'] ?? ''), ENT_QUOTES, 'UTF-8');
    $contact_designation = htmlspecialchars(trim($_POST['contact_designation'] ?? ''), ENT_QUOTES, 'UTF-8');
    $phone = preg_replace('/[^0-9+\-\s()]/', '', $_POST['phone'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

    // Validate required fields
    if (empty($org_name))
        $errors[] = "Organization name is required";
    if (empty($certificate_number))
        $errors[] = "Certificate number is required";
    if (empty($contact_person))
        $errors[] = "Contact person is required";
    if (empty($contact_designation))
        $errors[] = "Designation is required";
    if (empty($phone))
        $errors[] = "Phone is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Valid email is required";

    // Change categories
    $changeCategories = [
        'company_name',
        'ownership',
        'legal_status',
        'address',
        'site_addition',
        'office_address',
        'phone',
        'email',
        'contact_person',
        'activity',
        'employee'
    ];

    $changeData = [];
    foreach ($changeCategories as $category) {
        $changeField = $category . '_change';
        $detailsField = $category . '_details';

        $changeData[$changeField] = $_POST[$changeField] ?? 'No';
        $changeData[$detailsField] = '';

        if ($changeData[$changeField] === 'Yes') {
            $details = '';
            if ($category === 'site_addition') {
                $names = $_POST['site_addition_name'] ?? [];
                $addresses = $_POST['site_addition_address'] ?? [];
                $activities = $_POST['site_addition_activity'] ?? [];
                $staff = $_POST['site_addition_staff'] ?? [];
                $annexure = trim((string) ($_POST['site_addition_annexure'] ?? ''));

                $lines = [];
                $rowCount = max(count($names), count($addresses), count($activities), count($staff));
                for ($i = 0; $i < $rowCount; $i++) {
                    $n = trim((string) ($names[$i] ?? ''));
                    $a = trim((string) ($addresses[$i] ?? ''));
                    $act = trim((string) ($activities[$i] ?? ''));
                    $st = trim((string) ($staff[$i] ?? ''));
                    if ($n === '' && $a === '' && $act === '' && $st === '') {
                        continue;
                    }
                    $lines[] = ($i + 1) . ') Name/Type: ' . $n . ' | Address: ' . $a . ' | Activity: ' . $act . ' | No. Staff: ' . $st;
                }
                if ($annexure !== '') {
                    $lines[] = '';
                    $lines[] = 'Annexure / Additional details:';
                    $lines[] = $annexure;
                }
                $details = implode("\n", $lines);
            } elseif ($category === 'employee') {
                $blocks = $_POST['employee_blocks'] ?? [];
                $lines = [];
                $grandTotal = 0;
                foreach ($blocks as $idx => $block) {
                    $siteName = trim((string) ($block['site'] ?? ''));
                    $siteTitle = 'Site ' . ((int) $idx + 1) . ' Name/Type: ' . $siteName;
                    $lines[] = $siteTitle;

                    $departments = ['administration' => 'Administration (Incl. Mktg. / Others)', 'design' => 'Design', 'production' => 'Production (Incl. Quality)'];
                    foreach ($departments as $key => $label) {
                        $d = $block[$key] ?? [];
                        $gsPer = trim((string) ($d['gs_per'] ?? ''));
                        $gsCas = trim((string) ($d['gs_cas'] ?? ''));
                        $s1Per = trim((string) ($d['s1_per'] ?? ''));
                        $s1Cas = trim((string) ($d['s1_cas'] ?? ''));
                        $s2Per = trim((string) ($d['s2_per'] ?? ''));
                        $s2Cas = trim((string) ($d['s2_cas'] ?? ''));
                        $s3Per = trim((string) ($d['s3_per'] ?? ''));
                        $s3Cas = trim((string) ($d['s3_cas'] ?? ''));

                        $vals = [$gsPer, $gsCas, $s1Per, $s1Cas, $s2Per, $s2Cas, $s3Per, $s3Cas];
                        $hasAny = false;
                        foreach ($vals as $v) {
                            if ($v !== '') {
                                $hasAny = true;
                                break;
                            }
                        }
                        if (!$hasAny) {
                            continue;
                        }

                        $nums = [$gsPer, $gsCas, $s1Per, $s1Cas, $s2Per, $s2Cas, $s3Per, $s3Cas];
                        foreach ($nums as $n) {
                            if ($n !== '' && is_numeric($n)) {
                                $grandTotal += (float) $n;
                            }
                        }

                        $lines[] = $label . ': GS(Per=' . $gsPer . ', Cas=' . $gsCas . '), Shift I(Per=' . $s1Per . ', Cas=' . $s1Cas . '), Shift II(Per=' . $s2Per . ', Cas=' . $s2Cas . '), Shift III(Per=' . $s3Per . ', Cas=' . $s3Cas . ')';
                    }

                    $lines[] = '';
                }
                if ($grandTotal > 0) {
                    $lines[] = 'Total No. Employee (sum of all entered numbers): ' . rtrim(rtrim((string) $grandTotal, '0'), '.');
                }
                $details = trim(implode("\n", $lines));
            } else {
                $details = trim((string) ($_POST[$detailsField] ?? ''));
            }

            $details = htmlspecialchars($details, ENT_QUOTES, 'UTF-8');
            if (empty($details)) {
                $errors[] = ucfirst(str_replace('_', ' ', $category)) . " details are required when change is indicated";
            }
            $changeData[$detailsField] = $details;
        }
    }

    // Consultancy services
    $consultancy_services = $_POST['consultancy_services'] ?? 'No';
    $consultancy_details = '';
    if ($consultancy_services === 'Yes') {
        $consultancy_details = htmlspecialchars(trim($_POST['consultancy_details'] ?? ''), ENT_QUOTES, 'UTF-8');
        if (empty($consultancy_details)) {
            $errors[] = "Consultancy details are required when 'Yes' is selected";
        }
    }

    // Signature
    $sign_name = htmlspecialchars(trim($_POST['sign_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $sign_designation = htmlspecialchars(trim($_POST['sign_designation'] ?? ''), ENT_QUOTES, 'UTF-8');
    $sign_date = htmlspecialchars(trim($_POST['sign_date'] ?? ''), ENT_QUOTES, 'UTF-8');

    if (empty($sign_name))
        $errors[] = "Signature name is required";
    if (empty($sign_designation))
        $errors[] = "Signature designation is required";
    if (empty($sign_date))
        $errors[] = "Signature date is required";

    // Handle signature image upload
    $signature_image_path = '';
    if (isset($_FILES['signature_image']) && $_FILES['signature_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['signature_image'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $max_size = 2 * 1024 * 1024; // 2MB

        // Validate file type
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Signature file must be JPG, PNG, or PDF";
        }

        // Validate file size
        if ($file['size'] > $max_size) {
            $errors[] = "Signature file size must be less than 2MB";
        }

        if (empty($errors)) {
            // Create upload directory if it doesn't exist
            $upload_dir = 'uploads/signatures/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'signature_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $signature_image_path = $upload_path;
            } else {
                $errors[] = "Failed to upload signature image. Please try again.";
            }
        }
    } elseif (isset($_FILES['signature_image']) && $_FILES['signature_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "Signature image upload error. Please try again.";
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO information_updation_forms (
                org_name, certificate_number, contact_person, contact_designation, phone, email,
                company_name_change, company_name_details,
                ownership_change, ownership_details,
                legal_status_change, legal_status_details,
                address_change, address_details,
                site_addition_change, site_addition_details,
                office_address_change, office_address_details,
                phone_change, phone_change_details,
                email_change, email_change_details,
                contact_person_change, contact_person_change_details,
                activity_change, activity_details,
                employee_change, employee_details,
                consultancy_services, consultancy_details,
                sign_name, sign_designation, sign_date, signature_image,
                read_flag, created_at
            ) VALUES (
                :org_name, :certificate_number, :contact_person, :contact_designation, :phone, :email,
                :company_name_change, :company_name_details,
                :ownership_change, :ownership_details,
                :legal_status_change, :legal_status_details,
                :address_change, :address_details,
                :site_addition_change, :site_addition_details,
                :office_address_change, :office_address_details,
                :phone_change, :phone_change_details,
                :email_change, :email_change_details,
                :contact_person_change, :contact_person_change_details,
                :activity_change, :activity_details,
                :employee_change, :employee_details,
                :consultancy_services, :consultancy_details,
                :sign_name, :sign_designation, :sign_date, :signature_image,
                0, NOW()
            )";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':org_name', $org_name);
            $stmt->bindParam(':certificate_number', $certificate_number);
            $stmt->bindParam(':contact_person', $contact_person);
            $stmt->bindParam(':contact_designation', $contact_designation);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);

            // Bind change category parameters
            $stmt->bindParam(':company_name_change', $changeData['company_name_change']);
            $stmt->bindParam(':company_name_details', $changeData['company_name_details']);
            $stmt->bindParam(':ownership_change', $changeData['ownership_change']);
            $stmt->bindParam(':ownership_details', $changeData['ownership_details']);
            $stmt->bindParam(':legal_status_change', $changeData['legal_status_change']);
            $stmt->bindParam(':legal_status_details', $changeData['legal_status_details']);
            $stmt->bindParam(':address_change', $changeData['address_change']);
            $stmt->bindParam(':address_details', $changeData['address_details']);
            $stmt->bindParam(':site_addition_change', $changeData['site_addition_change']);
            $stmt->bindParam(':site_addition_details', $changeData['site_addition_details']);
            $stmt->bindParam(':office_address_change', $changeData['office_address_change']);
            $stmt->bindParam(':office_address_details', $changeData['office_address_details']);
            $stmt->bindParam(':phone_change', $changeData['phone_change']);
            $stmt->bindParam(':phone_change_details', $changeData['phone_change_details']);
            $stmt->bindParam(':email_change', $changeData['email_change']);
            $stmt->bindParam(':email_change_details', $changeData['email_change_details']);
            $stmt->bindParam(':contact_person_change', $changeData['contact_person_change']);
            $stmt->bindParam(':contact_person_change_details', $changeData['contact_person_change_details']);
            $stmt->bindParam(':activity_change', $changeData['activity_change']);
            $stmt->bindParam(':activity_details', $changeData['activity_details']);
            $stmt->bindParam(':employee_change', $changeData['employee_change']);
            $stmt->bindParam(':employee_details', $changeData['employee_details']);

            $stmt->bindParam(':consultancy_services', $consultancy_services);
            $stmt->bindParam(':consultancy_details', $consultancy_details);
            $stmt->bindParam(':sign_name', $sign_name);
            $stmt->bindParam(':sign_designation', $sign_designation);
            $stmt->bindParam(':sign_date', $sign_date);
            $stmt->bindParam(':signature_image', $signature_image_path);

            $stmt->execute();

            $successMessage = "Information updation form submitted successfully!";
            // Clear form data
            $_POST = [];
        } catch (PDOException $e) {
            error_log("Database insert error: " . $e->getMessage());
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

$pageIdentifier = "Information Updation Form";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Information Updation Form - AITF</title>
</head>

<body>
    <?php include './headerSource.php'; ?>

    <style>
        .info-update-wrapper {
            max-width: 1100px;
            margin: 30px auto;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            width: 100%;
            padding: 0 15px;
        }

        .info-update-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.10);
            padding: 40px 50px;
            border: 2px solid #111827;
        }

        .info-update-header {
            background: #ffffff;
            color: #111827;
            padding: 20px 15px;
            border-radius: 12px;
            margin-bottom: 35px;
            text-align: center;
            border: 2px solid #111827;
        }

        .info-update-header h3 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0.2px;
        }

        .info-update-header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 1;
            font-weight: 700;
        }

        .info-update-section {
            border: 2px solid #111827;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 12px;
            background: #ffffff;
        }

        .info-update-section h4 {
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

        .change-category {
            border-bottom: 1px solid #e5e7eb;
            padding: 20px 0;
            margin-bottom: 20px;
        }

        .change-category:last-child {
            border-bottom: none;
        }

        .change-category-label {
            font-weight: 600;
            color: #111827;
            margin-bottom: 15px;
            display: block;
            font-size: 16px;
            line-height: 1.6;
        }

        .change-details {
            display: none;
            margin-top: 15px;
        }

        .change-details label {
            font-size: 15px;
            margin-bottom: 8px;
            color: #111827;
            font-weight: 600;
        }

        .change-details textarea {
            width: 100%;
            min-height: 100px;
            padding: 14px;
            border: 2px solid #ddd6fe;
            border-radius: 8px;
            font-family: inherit;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .change-details textarea:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 0.25rem rgba(139, 92, 246, 0.15);
            outline: none;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            font-weight: 600;
            color: #111827;
            font-size: 16px;
            margin-bottom: 10px;
            display: block;
        }

        .form-control {
            border: 2px solid #111827;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 15px;
            height: auto;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #111827;
            box-shadow: 0 0 0 0.25rem rgba(17, 24, 39, 0.15);
            outline: none;
        }

        .btn-submit {
            background: #111827;
            color: #ffffff;
            border: none;
            padding: 16px 40px;
            font-size: 18px;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(17, 24, 39, 0.35);
        }

        .btn-submit:hover {
            background: #000000;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(17, 24, 39, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .alert {
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 10px;
            font-size: 15px;
        }

        .alert-danger {
            background: #fee2e2;
            border: 2px solid #fecaca;
            color: #991b1b;
        }

        .alert-success {
            background: #dcfce7;
            border: 2px solid #16a34a;
            color: #14532d;
        }

        .alert ul {
            margin: 12px 0 0 25px;
        }

        .alert li {
            margin-bottom: 5px;
        }

        .radio-group {
            display: inline-block;
            margin-right: 30px;
        }

        .radio-group label {
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            color: #111827;
        }

        .radio-group input[type="radio"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #111827;
        }

        .doc-meta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .doc-meta td {
            border: 1px solid #111827;
            padding: 10px 12px;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .form-intro {
            margin-top: 18px;
            color: #111827;
            font-size: 15px;
            line-height: 1.55;
        }

        .method-box {
            border: 1px solid #111827;
            padding: 12px;
            border-radius: 10px;
            margin-top: 12px;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .grid-table th,
        .grid-table td {
            border: 1px solid #111827;
            padding: 10px;
            font-size: 14px;
            vertical-align: top;
        }

        .grid-table th {
            font-weight: 800;
        }


        @media (max-width: 768px) {
            .info-update-wrapper {
                margin: 15px;
            }

            .info-update-card {
                padding: 25px 20px;
            }

            .info-update-header h3 {
                font-size: 22px;
            }

            .info-update-section h4 {
                font-size: 18px;
            }

            .change-category-label {
                font-size: 14px;
            }
        }

        /* Reset any container constraints for footer */
        body {
            overflow-x: hidden !important;
        }

        .info-update-wrapper,
        .info-update-card {
            max-width: 1100px !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
    </style>

    <div class="info-update-wrapper" style="margin-top: 100px; margin-bottom: 50px;">
        <div class="info-update-card">
            <div class="info-update-header">
                <div style="display:flex; align-items:center; gap:14px; justify-content:center; flex-wrap:wrap;">
                    <div style="flex:0 0 auto;">
                        <img src="images/aitf.jpg" alt="AITF" style="height:58px; width:auto; display:block;">
                    </div>
                    <div style="flex:1 1 520px; min-width: 260px;">
                        <h3 style="margin:0;">AITF MANAGEMENT CERTIFICATION LIMITED</h3>
                        <p style="margin:6px 0 0 0; text-decoration: underline;">INFORMATION UPDATION FORM</p>
                    </div>
                </div>
                <table class="doc-meta">
                    <tr>
                        <td style="width:25%;">Form No. F 7.31</td>
                        <td style="width:25%;">Rev.: 00, Date: 01.10.2013</td>
                        <td style="width:25%;">Issue.: 01, Date: 01.10.2013</td>
                        <td style="width:25%;">Page: 1 of 1</td>
                    </tr>
                </table>
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
                    <p style="margin: 10px 0 0 0;">Your information updation form has been submitted successfully. Our team
                        will review it shortly.</p>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="infoUpdationForm" enctype="multipart/form-data">
                <div class="info-update-section">
                    <h4><i class="fa fa-info-circle"></i> Instructions</h4>
                    <div class="form-intro">
                        <div><strong>This form need to be filled before next periodical/surveillance audit for changes
                                made in your organisation since from last certified by AITF.</strong></div>
                        <div class="method-box">
                            <strong>Method of Filling:</strong> all the fields in sections 1, 2, 3, 4, 5 and 6 are to be
                            completed, use tick Mark (&#8730;/&#9745;) whereas appropriate, where not applicable put
                            N.A. or &ldquo; &ndash; &rdquo;.
                        </div>
                    </div>
                </div>

                <!-- Organization Details Section -->
                <div class="info-update-section">
                    <h4><i class="fa fa-building"></i> Organization Details</h4>

                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Name of Certified Organization <span style="color: red;">*</span></label>
                            <input type="text" name="org_name" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['org_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Certificate Number <span style="color: red;">*</span></label>
                            <input type="text" name="certificate_number" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['certificate_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Contact Person <span style="color: red;">*</span></label>
                            <input type="text" name="contact_person" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['contact_person'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Designation <span style="color: red;">*</span></label>
                            <input type="text" name="contact_designation" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['contact_designation'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Phone <span style="color: red;">*</span></label>
                            <input type="text" name="phone" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Email <span style="color: red;">*</span></label>
                            <input type="email" name="email" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                    </div>
                </div>

                <!-- Change Categories Section -->
                <div class="info-update-section">
                    <h4><i class="fa fa-edit"></i> Review of Change in Data of Organization</h4>

                    <?php
                    $categories = [
                        ['name' => 'company_name', 'label' => 'Company Name'],
                        ['name' => 'ownership', 'label' => 'Change of Organization Ownership'],
                        ['name' => 'legal_status', 'label' => 'Change of Legal Status (Ltd / Pvt Ltd etc.)'],
                        ['name' => 'address', 'label' => 'Change of Address/Location/s has been certified'],
                        ['name' => 'site_addition', 'label' => 'Addition of site/s to be covered under the audit scope'],
                        ['name' => 'office_address', 'label' => 'Change in office / Communication address'],
                        ['name' => 'phone', 'label' => 'Telephone, Mobile'],
                        ['name' => 'email', 'label' => 'E-Mail, Web-site'],
                        ['name' => 'contact_person', 'label' => 'Contact Person'],
                        ['name' => 'activity', 'label' => 'Change of certified activity (addition/Reduction) i.e. Scope of the audit'],
                        ['name' => 'employee', 'label' => 'Change of number of employees (addition/Reduction)']
                    ];

                    foreach ($categories as $category):
                        $fieldName = $category['name'];
                        $fieldLabel = $category['label'];
                        $postedValue = $_POST[$fieldName . '_change'] ?? 'No';
                        ?>
                        <div class="change-category">
                            <span class="change-category-label"><?php echo $fieldLabel; ?>:</span>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="<?php echo $fieldName; ?>_change" value="No"
                                        class="change-radio" data-target="<?php echo $fieldName; ?>_details_div" <?php echo ($postedValue !== 'Yes') ? 'checked' : ''; ?>>
                                    No change
                                </label>
                            </div>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="<?php echo $fieldName; ?>_change" value="Yes"
                                        class="change-radio" data-target="<?php echo $fieldName; ?>_details_div" <?php echo ($postedValue === 'Yes') ? 'checked' : ''; ?>>
                                    If change
                                </label>
                            </div>
                            <?php if ($fieldName === 'site_addition'): ?>
                                <div class="change-details" id="<?php echo $fieldName; ?>_details_div"
                                    style="<?php echo ($postedValue === 'Yes') ? 'display:block;' : ''; ?>">
                                    <label style="display:block; margin-bottom:10px;">Addition of site/s to be covered under the
                                        audit scope (other sites to be certified and/or operational sites and/or external works,
                                        for additional details please attach annexure in the same format.)</label>
                                    <table class="grid-table">
                                        <thead>
                                            <tr>
                                                <th style="width:22%;">Name or type</th>
                                                <th style="width:34%;">Address of Site/s</th>
                                                <th style="width:28%;">Activity to be carried-out</th>
                                                <th style="width:16%;">No staff</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php for ($r = 0; $r < 4; $r++): ?>
                                                <tr>
                                                    <td><input type="text" class="form-control" name="site_addition_name[]"
                                                            value="<?php echo htmlspecialchars($_POST['site_addition_name'][$r] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    </td>
                                                    <td><input type="text" class="form-control" name="site_addition_address[]"
                                                            value="<?php echo htmlspecialchars($_POST['site_addition_address'][$r] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    </td>
                                                    <td><input type="text" class="form-control" name="site_addition_activity[]"
                                                            value="<?php echo htmlspecialchars($_POST['site_addition_activity'][$r] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    </td>
                                                    <td><input type="text" class="form-control" name="site_addition_staff[]"
                                                            value="<?php echo htmlspecialchars($_POST['site_addition_staff'][$r] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    </td>
                                                </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                    <div style="margin-top:12px;">
                                        <label>Annexure / Additional details (optional):</label>
                                        <textarea name="site_addition_annexure" class="form-control"
                                            placeholder="If needed, add extra sites details here..."><?php echo htmlspecialchars($_POST['site_addition_annexure'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>
                            <?php elseif ($fieldName === 'employee'): ?>
                                <div class="change-details" id="<?php echo $fieldName; ?>_details_div"
                                    style="<?php echo ($postedValue === 'Yes') ? 'display:block;' : ''; ?>">
                                    <table class="grid-table">
                                        <thead>
                                            <tr>
                                                <th style="width:16%;">Site/s</th>
                                                <th style="width:18%;">Department</th>
                                                <th style="width:8%;">GS Per.</th>
                                                <th style="width:8%;">GS Cas.</th>
                                                <th style="width:8%;">S-I Per.</th>
                                                <th style="width:8%;">S-I Cas.</th>
                                                <th style="width:8%;">S-II Per.</th>
                                                <th style="width:8%;">S-II Cas.</th>
                                                <th style="width:8%;">S-III Per.</th>
                                                <th style="width:8%;">S-III Cas.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $deptRows = [
                                                ['key' => 'administration', 'label' => 'Administration (Incl. Mktg. / Others)'],
                                                ['key' => 'design', 'label' => 'Design'],
                                                ['key' => 'production', 'label' => 'Production (Incl. Quality)']
                                            ];
                                            for ($b = 0; $b < 2; $b++):
                                                $siteVal = $_POST['employee_blocks'][$b]['site'] ?? '';
                                                ?>
                                                <tr>
                                                    <td colspan="10" style="background:#f9fafb;">
                                                        <strong>Name or type:</strong>
                                                        <input type="text" class="form-control"
                                                            name="employee_blocks[<?php echo $b; ?>][site]"
                                                            value="<?php echo htmlspecialchars($siteVal, ENT_QUOTES, 'UTF-8'); ?>"
                                                            style="display:inline-block; width: 320px; max-width: 100%; margin-left:10px;">
                                                    </td>
                                                </tr>
                                                <?php foreach ($deptRows as $dr):
                                                    $k = $dr['key']; ?>
                                                    <tr>
                                                        <td>&nbsp;</td>
                                                        <td><strong><?php echo $dr['label']; ?></strong></td>
                                                        <td><input type="number" class="form-control"
                                                                name="employee_blocks[<?php echo $b; ?>][<?php echo $k; ?>][gs_per]"
                                                                value="<?php echo htmlspecialchars($_POST['employee_blocks'][$b][$k]['gs_per'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        </td>
                                                        <td><input type="number" class="form-control"
                                                                name="employee_blocks[<?php echo $b; ?>][<?php echo $k; ?>][gs_cas]"
                                                                value="<?php echo htmlspecialchars($_POST['employee_blocks'][$b][$k]['gs_cas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        </td>
                                                        <td><input type="number" class="form-control"
                                                                name="employee_blocks[<?php echo $b; ?>][<?php echo $k; ?>][s1_per]"
                                                                value="<?php echo htmlspecialchars($_POST['employee_blocks'][$b][$k]['s1_per'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        </td>
                                                        <td><input type="number" class="form-control"
                                                                name="employee_blocks[<?php echo $b; ?>][<?php echo $k; ?>][s1_cas]"
                                                                value="<?php echo htmlspecialchars($_POST['employee_blocks'][$b][$k]['s1_cas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        </td>
                                                        <td><input type="number" class="form-control"
                                                                name="employee_blocks[<?php echo $b; ?>][<?php echo $k; ?>][s2_per]"
                                                                value="<?php echo htmlspecialchars($_POST['employee_blocks'][$b][$k]['s2_per'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        </td>
                                                        <td><input type="number" class="form-control"
                                                                name="employee_blocks[<?php echo $b; ?>][<?php echo $k; ?>][s2_cas]"
                                                                value="<?php echo htmlspecialchars($_POST['employee_blocks'][$b][$k]['s2_cas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        </td>
                                                        <td><input type="number" class="form-control"
                                                                name="employee_blocks[<?php echo $b; ?>][<?php echo $k; ?>][s3_per]"
                                                                value="<?php echo htmlspecialchars($_POST['employee_blocks'][$b][$k]['s3_per'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        </td>
                                                        <td><input type="number" class="form-control"
                                                                name="employee_blocks[<?php echo $b; ?>][<?php echo $k; ?>][s3_cas]"
                                                                value="<?php echo htmlspecialchars($_POST['employee_blocks'][$b][$k]['s3_cas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr>
                                                    <td colspan="10" style="height:10px; border-left:0; border-right:0;"></td>
                                                </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                    <div style="margin-top:10px; font-size: 14px;"><strong>For additional sites please attach
                                            annexure in the same format.</strong></div>
                                </div>
                            <?php else: ?>
                                <div class="change-details" id="<?php echo $fieldName; ?>_details_div"
                                    style="<?php echo ($postedValue === 'Yes') ? 'display:block;' : ''; ?>">
                                    <label>Please provide details:</label>
                                    <textarea name="<?php echo $fieldName; ?>_details" class="form-control details-required"
                                        placeholder="Enter details about the change..."><?php echo htmlspecialchars($_POST[$fieldName . '_details'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Consultancy Services Section -->
                <div class="info-update-section">
                    <h4><i class="fa fa-question-circle"></i> Consultancy Services</h4>

                    <div class="change-category">
                        <span class="change-category-label">Have consultancy services or in-house training been
                            performed after certified by AITF on the subject of ISO 9001:</span>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="consultancy_services" value="No" class="change-radio"
                                    data-target="consultancy_details_div" <?php echo (($_POST['consultancy_services'] ?? 'No') !== 'Yes') ? 'checked' : ''; ?>>
                                No
                            </label>
                        </div>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="consultancy_services" value="Yes" class="change-radio"
                                    data-target="consultancy_details_div" <?php echo (($_POST['consultancy_services'] ?? 'No') === 'Yes') ? 'checked' : ''; ?>>
                                Yes
                            </label>
                        </div>
                        <div class="change-details" id="consultancy_details_div"
                            style="<?php echo (($_POST['consultancy_services'] ?? 'No') === 'Yes') ? 'display:block;' : ''; ?>">
                            <label>If yes, by whom:</label>
                            <textarea name="consultancy_details" class="form-control details-required"
                                placeholder="Enter consultancy details..."><?php echo htmlspecialchars($_POST['consultancy_details'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Signature Section -->
                <div class="info-update-section">
                    <h4><i class="fa fa-pencil"></i> Signature & Declaration</h4>
                    <p style="margin-bottom: 20px; color: #166534;">
                        We herewith confirm the completeness and accuracy of the change of information given above.
                    </p>

                    <div class="row">
                        <div class="col-sm-4 form-group">
                            <label>Name <span style="color: red;">*</span></label>
                            <input type="text" name="sign_name" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['sign_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Designation <span style="color: red;">*</span></label>
                            <input type="text" name="sign_designation" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['sign_designation'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Date <span style="color: red;">*</span></label>
                            <input type="date" name="sign_date" class="form-control"
                                value="<?php echo htmlspecialchars($_POST['sign_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>"
                                required>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 20px;">
                        <div class="col-sm-12 form-group">
                            <label style="display: block; margin-bottom: 10px;">
                                <i class="fa fa-image"></i> Upload Signature Image <span style="color: red;">*</span>
                                <small style="color: #6b7280; font-weight: normal;">(JPG, PNG, or PDF - Max 2MB)</small>
                            </label>
                            <div
                                style="border: 2px dashed #bbf7d0; border-radius: 8px; padding: 20px; text-align: center; background: #ffffff;">
                                <input type="file" name="signature_image" id="signature_image"
                                    accept="image/jpeg,image/jpg,image/png,application/pdf" required
                                    style="display: none;">
                                <label for="signature_image" style="cursor: pointer; display: block;">
                                    <div id="upload-placeholder">
                                        <i class="fa fa-cloud-upload"
                                            style="font-size: 48px; color: #16a34a; margin-bottom: 10px;"></i>
                                        <p style="margin: 0; color: #166534; font-weight: 600;">Click to upload
                                            signature</p>
                                        <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 13px;">or drag and drop
                                            your file here</p>
                                    </div>
                                    <div id="file-preview" style="display: none;">
                                        <i class="fa fa-file-image-o"
                                            style="font-size: 48px; color: #16a34a; margin-bottom: 10px;"></i>
                                        <p id="file-name" style="margin: 0; color: #166534; font-weight: 600;"></p>
                                        <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 13px;">Click to change
                                            file</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Note Section -->
                <div class="alert" style="background: #fef3c7; border: 1px solid #fde68a; color: #92400e;">
                    <strong><i class="fa fa-info-circle"></i> Note:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>Surveillance manday will be decided with considering present information of the organisation
                            status.</li>
                        <li>Any change in organisation production/Service realisation Process flow, organisation
                            brochure, and customer specific requirements, please enclose with this form.</li>
                    </ul>
                </div>

                <div class="info-update-section">
                    <h4><i class="fa fa-envelope"></i> Communication & Review</h4>
                    <div style="font-size:15px; line-height:1.55; color:#111827;">
                        <div style="border:1px solid #111827; padding:14px; border-radius:10px;">
                            <strong>FOR COMMUNICATION TO AITF- REGISTERED OFFICE ADDRESS:</strong><br>
                            <strong>AITF MANAGEMENT CERTIFICATION LIMITED</strong><br>
                            Devi Indrayani Wing B 26, Gat No. 382+3+4/1, Talawade, Pune &ndash; 411 062. Maharashtra,
                            India.<br>
                            <strong>OFFICE PHONE</strong> 020 65116677, <strong>WEB SITE</strong> &ndash;
                            www.aitf.co.in<br>
                            <strong>Communication MAIL ID</strong> &ndash; aitfmcl@gmail.com (CERTIFICATION MANAGER)
                        </div>
                        <div style="height:14px;"></div>
                        <div style="border:1px solid #111827; padding:14px; border-radius:10px;">
                            <strong>FOR AITF REVIEW :</strong>
                            <div
                                style="height:70px; border-top:1px solid #111827; margin-top:10px; display:flex; align-items:flex-end; justify-content:flex-end; padding-top:10px;">
                                <strong>Sign of reviewer (AITF)</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" name="submit_info_form" class="btn-submit">
                        <i class="fa fa-paper-plane"></i> Submit Information Updation Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // File upload preview
            $('#signature_image').change(function () {
                var file = this.files[0];
                if (file) {
                    var fileName = file.name;
                    var fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB

                    // Check file size (max 2MB)
                    if (fileSize > 2) {
                        alert('File size must be less than 2MB. Your file is ' + fileSize + 'MB');
                        $(this).val('');
                        return;
                    }

                    // Check file type
                    var validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                    if (!validTypes.includes(file.type)) {
                        alert('Please upload only JPG, PNG, or PDF files');
                        $(this).val('');
                        return;
                    }

                    // Show file preview
                    $('#upload-placeholder').hide();
                    $('#file-preview').show();
                    $('#file-name').text(fileName + ' (' + fileSize + ' MB)');
                }
            });

            // Show/hide detail fields based on Yes/No selection
            $('.change-radio').change(function () {
                var targetDiv = $(this).data('target');
                var value = $(this).val();

                if (value === 'Yes') {
                    $('#' + targetDiv).slideDown();
                    $('#' + targetDiv + ' textarea.details-required').prop('required', true);
                } else {
                    $('#' + targetDiv).slideUp();
                    $('#' + targetDiv + ' textarea.details-required').prop('required', false);
                }
            });

            // Form validation
            $('#infoUpdationForm').submit(function (e) {
                var isValid = true;
                var errors = [];

                // Validate email
                var email = $('input[name="email"]').val();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    errors.push('Please enter a valid email address');
                    isValid = false;
                }

                // Validate phone
                var phone = $('input[name="phone"]').val();
                if (phone.length < 10) {
                    errors.push('Please enter a valid phone number');
                    isValid = false;
                }

                // Validate signature upload
                var signatureFile = $('#signature_image')[0].files[0];
                if (!signatureFile) {
                    errors.push('Please upload your signature image');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    alert(errors.join('\n'));
                }
            });
        });
    </script>

    <?php include './footerSource.php'; ?>
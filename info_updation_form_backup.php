<?php
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

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
    read_flag TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL,
    INDEX idx_created_at (created_at),
    INDEX idx_read_flag (read_flag),
    INDEX idx_org_name (org_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->exec($createTableSQL);
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
}

$errors = [];
$successMessage = '';

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
    if (empty($org_name)) $errors[] = "Organization name is required";
    if (empty($certificate_number)) $errors[] = "Certificate number is required";
    if (empty($contact_person)) $errors[] = "Contact person is required";
    if (empty($contact_designation)) $errors[] = "Designation is required";
    if (empty($phone)) $errors[] = "Phone is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    
    // Change categories
    $changeCategories = [
        'company_name', 'ownership', 'legal_status', 'address', 'site_addition',
        'office_address', 'phone', 'email', 'contact_person', 'activity', 'employee'
    ];
    
    $changeData = [];
    foreach ($changeCategories as $category) {
        $changeData[$category . '_change'] = $_POST[$category . '_change'] ?? 'No';
        $changeData[$category . '_details'] = '';
        
        if ($changeData[$category . '_change'] === 'Yes') {
            $details = htmlspecialchars(trim($_POST[$category . '_details'] ?? ''), ENT_QUOTES, 'UTF-8');
            if (empty($details)) {
                $errors[] = ucfirst(str_replace('_', ' ', $category)) . " details are required when change is indicated";
            }
            $changeData[$category . '_details'] = $details;
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
    
    if (empty($sign_name)) $errors[] = "Signature name is required";
    if (empty($sign_designation)) $errors[] = "Signature designation is required";
    if (empty($sign_date)) $errors[] = "Signature date is required";
    
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
                sign_name, sign_designation, sign_date,
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
                :sign_name, :sign_designation, :sign_date,
                0, NOW()
            )";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':org_name', $org_name);
            $stmt->bindParam(':certificate_number', $certificate_number);
            $stmt->bindParam(':contact_person', $contact_person);
            $stmt->bindParam(':contact_designation', $contact_designation);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            
            foreach ($changeCategories as $category) {
                $stmt->bindParam(':' . $category . '_change', $changeData[$category . '_change']);
                $stmt->bindParam(':' . $category . '_details', $changeData[$category . '_details']);
            }
            
            $stmt->bindParam(':consultancy_services', $consultancy_services);
            $stmt->bindParam(':consultancy_details', $consultancy_details);
            $stmt->bindParam(':sign_name', $sign_name);
            $stmt->bindParam(':sign_designation', $sign_designation);
            $stmt->bindParam(':sign_date', $sign_date);
            
            $stmt->execute();
            
            $successMessage = "Information updation form submitted successfully!";
            // Clear form data
            $_POST = [];
        } catch (PDOException $e) {
            error_log("Database insert error: " . $e->getMessage());
            $errors[] = "An error occurred while saving your submission. Please try again later.";
        }
    }
}

include 'header.php';
?>

<style>
.info-update-wrapper {
    max-width: 900px;
    margin: 20px auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.info-update-card {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 14px 35px rgba(15, 23, 42, 0.10);
    padding: 25px 30px;
    border: 1px solid #e5e7eb;
    border-top: 4px solid #16a34a;
}

.info-update-header {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #ffffff;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    text-align: center;
}

.info-update-header h3 {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
}

.info-update-header p {
    margin: 5px 0 0 0;
    font-size: 13px;
    opacity: 0.9;
}

.info-update-section {
    border: 1px solid #d1fae5;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    background: #f0fdf4;
}

.info-update-section h4 {
    margin-top: 0;
    color: #15803d;
    font-size: 18px;
    font-weight: 600;
    border-bottom: 2px solid #16a34a;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.change-category {
    border-bottom: 1px solid #bbf7d0;
    padding: 15px 0;
    margin-bottom: 15px;
}

.change-category:last-child {
    border-bottom: none;
}

.change-category-label {
    font-weight: 600;
    color: #166534;
    margin-bottom: 10px;
    display: block;
}

.change-details {
    display: none;
    margin-top: 10px;
}

.change-details textarea {
    width: 100%;
    min-height: 80px;
    padding: 10px;
    border: 1px solid #bbf7d0;
    border-radius: 6px;
    font-family: inherit;
}

.form-group label {
    font-weight: 600;
    color: #166534;
}

.form-control {
    border: 1px solid #bbf7d0;
    border-radius: 6px;
}

.form-control:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 0.2rem rgba(22, 163, 74, 0.25);
}

.btn-submit {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #ffffff;
    border: none;
    padding: 12px 30px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #15803d, #166534);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(22, 163, 74, 0.4);
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
}

.alert-danger {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

.alert-success {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #166534;
}

.radio-group {
    display: inline-block;
    margin-right: 20px;
}

.radio-group input[type="radio"] {
    margin-right: 5px;
}

@media (max-width: 768px) {
    .info-update-wrapper {
        margin: 10px;
    }
    
    .info-update-card {
        padding: 15px 20px;
    }
}
</style>

<div id="wrapper" class="active">
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="info-update-wrapper">
                <div class="info-update-card">
                    <div class="info-update-header">
                        <h3><i class="fa fa-refresh"></i> AITF MANAGEMENT CERTIFICATION LIMITED</h3>
                        <p>INFORMATION UPDATION FORM (Form No. F 7.31)</p>
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
                            <p style="margin: 10px 0 0 0;">Your information updation form has been submitted successfully. Our team will review it shortly.</p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="infoUpdationForm">
                        <!-- Organization Details Section -->
                        <div class="info-update-section">
                            <h4><i class="fa fa-building"></i> Organization Details</h4>
                            
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label>Name of Certified Organization <span style="color: red;">*</span></label>
                                    <input type="text" name="org_name" class="form-control" value="<?php echo htmlspecialchars($_POST['org_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label>Certificate Number <span style="color: red;">*</span></label>
                                    <input type="text" name="certificate_number" class="form-control" value="<?php echo htmlspecialchars($_POST['certificate_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label>Contact Person <span style="color: red;">*</span></label>
                                    <input type="text" name="contact_person" class="form-control" value="<?php echo htmlspecialchars($_POST['contact_person'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label>Designation <span style="color: red;">*</span></label>
                                    <input type="text" name="contact_designation" class="form-control" value="<?php echo htmlspecialchars($_POST['contact_designation'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label>Phone <span style="color: red;">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label>Email <span style="color: red;">*</span></label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Change Categories Section -->
                        <div class="info-update-section">
                            <h4><i class="fa fa-edit"></i> Review of Change in Data of Organization</h4>
                            <p style="margin-bottom: 20px; color: #166534; font-style: italic;">
                                <strong>Method of Filling:</strong> For each category below, select "No change" or "If change". If you select "If change", please provide details in the text area.
                            </p>
                            
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
                            ?>
                                <div class="change-category">
                                    <span class="change-category-label"><?php echo $fieldLabel; ?>:</span>
                                    <div class="radio-group">
                                        <label>
                                            <input type="radio" name="<?php echo $fieldName; ?>_change" value="No" class="change-radio" data-target="<?php echo $fieldName; ?>_details_div" checked>
                                            No change
                                        </label>
                                    </div>
                                    <div class="radio-group">
                                        <label>
                                            <input type="radio" name="<?php echo $fieldName; ?>_change" value="Yes" class="change-radio" data-target="<?php echo $fieldName; ?>_details_div">
                                            If change
                                        </label>
                                    </div>
                                    <div class="change-details" id="<?php echo $fieldName; ?>_details_div">
                                        <label>Please provide details:</label>
                                        <textarea name="<?php echo $fieldName; ?>_details" class="form-control" placeholder="Enter details about the change..."><?php echo htmlspecialchars($_POST[$fieldName . '_details'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Consultancy Services Section -->
                        <div class="info-update-section">
                            <h4><i class="fa fa-question-circle"></i> Consultancy Services</h4>
                            
                            <div class="change-category">
                                <span class="change-category-label">Have consultancy services or in-house training been performed after certified by AITF on the subject of ISO 9001:</span>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="consultancy_services" value="No" class="change-radio" data-target="consultancy_details_div" checked>
                                        No
                                    </label>
                                </div>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="consultancy_services" value="Yes" class="change-radio" data-target="consultancy_details_div">
                                        Yes
                                    </label>
                                </div>
                                <div class="change-details" id="consultancy_details_div">
                                    <label>If yes, by whom:</label>
                                    <textarea name="consultancy_details" class="form-control" placeholder="Enter consultancy details..."><?php echo htmlspecialchars($_POST['consultancy_details'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
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
                                    <input type="text" name="sign_name" class="form-control" value="<?php echo htmlspecialchars($_POST['sign_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="col-sm-4 form-group">
                                    <label>Designation <span style="color: red;">*</span></label>
                                    <input type="text" name="sign_designation" class="form-control" value="<?php echo htmlspecialchars($_POST['sign_designation'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="col-sm-4 form-group">
                                    <label>Date <span style="color: red;">*</span></label>
                                    <input type="date" name="sign_date" class="form-control" value="<?php echo htmlspecialchars($_POST['sign_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Note Section -->
                        <div class="alert" style="background: #fef3c7; border: 1px solid #fde68a; color: #92400e;">
                            <strong><i class="fa fa-info-circle"></i> Note:</strong>
                            <ul style="margin: 10px 0 0 20px;">
                                <li>Surveillance manday will be decided with considering present information of the organisation status.</li>
                                <li>Any change in organisation production/Service realisation Process flow, organisation brochure, and customer specific requirements, please enclose with this form.</li>
                            </ul>
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
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Show/hide detail fields based on Yes/No selection
    $('.change-radio').change(function() {
        var targetDiv = $(this).data('target');
        var value = $(this).val();
        
        if (value === 'Yes') {
            $('#' + targetDiv).slideDown();
            $('#' + targetDiv + ' textarea').prop('required', true);
        } else {
            $('#' + targetDiv).slideUp();
            $('#' + targetDiv + ' textarea').prop('required', false);
        }
    });
    
    // Form validation
    $('#infoUpdationForm').submit(function(e) {
        var isValid = true;
        var errors = [];
        
        // Check if at least one change is selected
        var hasChange = false;
        $('.change-radio:checked').each(function() {
            if ($(this).val() === 'Yes') {
                hasChange = true;
            }
        });
        
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
        
        if (!isValid) {
            e.preventDefault();
            alert(errors.join('\n'));
        }
    });
});
</script>

<?php include 'footer.php'; ?>

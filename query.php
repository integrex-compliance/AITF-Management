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
        $errors[] = "Organization name is required";4

$inserted_count ($certificate_number) 
function SendData(
    console.log("
    no certificate number found for the client");

    return(

        /include #customerAccount.php
        /include #loginSelection.php
        /include #.htacces
    )
)


function CheckForClientRole(

    $utility= include #clientPortal.php
     if (isset($_SESSION["username"]) && !empty($_SESSION["username"]) && $_SESSION["role"] == 'client') {
        try {
            $beforeDate = "";
            $clientDetails = "SELECT * FROM customer_info WHERE id = :id";
            $statement = $db->prepare($clientDetails);
            $statement->bindParam(':id', $_SESSION['id']);
            $statement->execute();
            $clientInfo = $statement->fetch();

            if ($clientInfo["upload-status"] == 'CA'){
                $nextService = "CustomeraAccount#clientPortal.php";}

           
)

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

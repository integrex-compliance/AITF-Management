<?php
require_once './dbConnect.php';
require_once __DIR__ . '/include/env_helper.php';
$indiaTz = new DateTimeZone('Asia/Kolkata');
date_default_timezone_set('Asia/Kolkata');
$turnstileHelperPath = __DIR__ . '/include/turnstile_helper.php';
if (file_exists($turnstileHelperPath)) {
    require_once $turnstileHelperPath;
}

$turnstileConfig = function_exists('aitf_get_turnstile_config') ? aitf_get_turnstile_config() : [
    'site_key' => '',
    'secret_key' => ''
];
$turnstileReady = function_exists('aitf_is_turnstile_ready') ? aitf_is_turnstile_ready() : false;

$success_message = '';
$error_message = '';
$applications = [];

$mailConfig = aitf_get_mail_config([
    'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Website')
]);
$mailFromEmailHtml = htmlspecialchars($mailConfig['from_email'], ENT_QUOTES, 'UTF-8');
$mailCcEmailHtml = htmlspecialchars($mailConfig['cc_email'], ENT_QUOTES, 'UTF-8');

if (isset($db)) {
    try {
        $createTableSql = "CREATE TABLE IF NOT EXISTS online_audit_applications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            org_name VARCHAR(255) NOT NULL,
            site_address TEXT NULL,
            office_address TEXT NULL,
            contact_person VARCHAR(255) NOT NULL,
            contact_designation VARCHAR(255) NULL,
            phone VARCHAR(50) NOT NULL,
            email VARCHAR(255) NOT NULL,
            website VARCHAR(255) NULL,
            gstin VARCHAR(50) NULL,
            legal_status VARCHAR(100) NULL,
            branch_offices VARCHAR(10) NULL,
            branch_details TEXT NULL,
            legal_obligations_applicable VARCHAR(10) NULL,
            legal_obligations TEXT NULL,
            certification_type TEXT NULL,
            standard_applied VARCHAR(255) NULL,
            accreditation_requirement VARCHAR(255) NULL,
            scope_requirement TEXT NULL,
            multisite VARCHAR(10) NULL,
            multisite_functions TEXT NULL,
            multisite_sites TEXT NULL,
            employee_structure TEXT NULL,
            onsite_installation VARCHAR(10) NULL,
            onsite_details TEXT NULL,
            part_time_personnel VARCHAR(10) NULL,
            part_time_count VARCHAR(50) NULL,
            employees_excluded VARCHAR(10) NULL,
            employees_excluded_details TEXT NULL,
            consultancy_services_taken VARCHAR(10) NULL,
            consultancy_details TEXT NULL,
            org_type VARCHAR(100) NULL,
            major_customers TEXT NULL,
            main_products TEXT NULL,
            raw_materials TEXT NULL,
            operations TEXT NULL,
            external_processes TEXT NULL,
            design_responsibility VARCHAR(255) NULL,
            other_certificates TEXT NULL,
            system_implementation_date VARCHAR(50) NULL,
            proposed_audit_month VARCHAR(50) NULL,
            sign_name VARCHAR(255) NULL,
            sign_designation VARCHAR(255) NULL,
            sign_date VARCHAR(50) NULL,
            sign_image_path VARCHAR(255) NULL,
            process_flow_attachment_path VARCHAR(255) NULL,
            process_flow_attachment_name VARCHAR(255) NULL,
            organisation_brochure_attachment_path VARCHAR(255) NULL,
            organisation_brochure_attachment_name VARCHAR(255) NULL,
            customer_requirements_attachment_path VARCHAR(255) NULL,
            customer_requirements_attachment_name VARCHAR(255) NULL,
            declaration_confirmed TINYINT(1) DEFAULT 0,
            read_flag TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $db->exec($createTableSql);

        // Best-effort schema updates for new columns (ignore errors if already present)
        $alterColumns = [
            "ALTER TABLE online_audit_applications ADD COLUMN sign_name VARCHAR(255) NULL",
            "ALTER TABLE online_audit_applications ADD COLUMN sign_designation VARCHAR(255) NULL",
            "ALTER TABLE online_audit_applications ADD COLUMN sign_date VARCHAR(50) NULL",
            "ALTER TABLE online_audit_applications ADD COLUMN sign_image_path VARCHAR(255) NULL",
            "ALTER TABLE online_audit_applications ADD COLUMN process_flow_attachment_path VARCHAR(255) NULL",
            "ALTER TABLE online_audit_applications ADD COLUMN process_flow_attachment_name VARCHAR(255) NULL",
            "ALTER TABLE online_audit_applications ADD COLUMN organisation_brochure_attachment_path VARCHAR(255) NULL",
            "ALTER TABLE online_audit_applications ADD COLUMN organisation_brochure_attachment_name VARCHAR(255) NULL",
            "ALTER TABLE online_audit_applications ADD COLUMN customer_requirements_attachment_path VARCHAR(255) NULL",
            "ALTER TABLE online_audit_applications ADD COLUMN customer_requirements_attachment_name VARCHAR(255) NULL",
            "ALTER TABLE online_audit_applications ADD COLUMN declaration_confirmed TINYINT(1) DEFAULT 0",
            "ALTER TABLE online_audit_applications ADD COLUMN read_flag TINYINT(1) DEFAULT 0",
            "ALTER TABLE online_audit_applications MODIFY created_at DATETIME NULL"
        ];
        foreach ($alterColumns as $sql) {
            try {
                $db->exec($sql);
            } catch (Exception $ignored) {
            }
        }
    } catch (Exception $e) {
    }

    if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' && isset($_POST['public_audit_application'])) {
        if (!function_exists('getOnlineAuditPostValue')) {
            function getOnlineAuditPostValue($key)
            {
                return isset($_POST[$key]) ? trim($_POST[$key]) : '';
            }
        }

        if (!function_exists('saveOnlineAuditUploadedFile')) {
            function saveOnlineAuditUploadedFile($fieldName, $label, $relativeDir, array $allowedExt, array $allowedMime, &$error_message)
            {
                if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
                    return ['path' => '', 'name' => '', 'mime' => '', 'extension' => ''];
                }

                $uploadError = isset($_FILES[$fieldName]['error']) ? (int) $_FILES[$fieldName]['error'] : UPLOAD_ERR_NO_FILE;
                if ($uploadError === UPLOAD_ERR_NO_FILE) {
                    return ['path' => '', 'name' => '', 'mime' => '', 'extension' => ''];
                }
                if ($uploadError !== UPLOAD_ERR_OK) {
                    $error_message = 'Unable to upload ' . $label . '. Please try again.';
                    return false;
                }

                $tmpName = $_FILES[$fieldName]['tmp_name'] ?? '';
                if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                    $error_message = 'Unable to upload ' . $label . '. Please try again.';
                    return false;
                }

                $maxBytes = 5 * 1024 * 1024;
                $fileSize = isset($_FILES[$fieldName]['size']) ? (int) $_FILES[$fieldName]['size'] : 0;
                if ($fileSize <= 0 || $fileSize > $maxBytes) {
                    $error_message = $label . ' must be 5 MB or smaller.';
                    return false;
                }

                $originalName = basename((string) ($_FILES[$fieldName]['name'] ?? ''));
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExt, true)) {
                    $error_message = $label . ' must be a PNG, JPG or JPEG file.';
                    return false;
                }

                $mime = '';
                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    if ($finfo) {
                        $mime = (string) finfo_file($finfo, $tmpName);
                        finfo_close($finfo);
                    }
                }
                if ($mime === '' && !empty($_FILES[$fieldName]['type'])) {
                    $mime = (string) $_FILES[$fieldName]['type'];
                }
                if ($mime !== '' && !in_array($mime, $allowedMime, true)) {
                    $error_message = $label . ' must be a valid PNG, JPG or JPEG file.';
                    return false;
                }

                $uploadDir = __DIR__ . '/' . trim($relativeDir, '/');
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
                    $error_message = 'Upload folder is not writable for ' . $label . '.';
                    return false;
                }

                $safeOriginal = preg_replace('/[^A-Za-z0-9._ -]+/', '_', $originalName);
                $safeOriginal = trim((string) $safeOriginal);
                if ($safeOriginal === '') {
                    $safeOriginal = $label . '.' . $extension;
                }
                $basename = $fieldName . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
                $targetPath = $uploadDir . '/' . $basename;

                if (!@move_uploaded_file($tmpName, $targetPath)) {
                    $error_message = 'Unable to save ' . $label . '. Please try again.';
                    return false;
                }

                return [
                    'path' => trim($relativeDir, '/') . '/' . $basename,
                    'name' => $safeOriginal,
                    'mime' => $mime,
                    'extension' => $extension
                ];
            }
        }

        $org_name = getOnlineAuditPostValue('org_name');
        $contact_person = getOnlineAuditPostValue('contact_person');
        $phone = getOnlineAuditPostValue('phone');
        $email = getOnlineAuditPostValue('email');
        $turnstileToken = trim($_POST['cf-turnstile-response'] ?? '');

        if ($org_name === '' || $contact_person === '' || $phone === '' || $email === '') {
            $error_message = 'Please fill in all required fields marked with *.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Please enter a valid email address.';
        } elseif (function_exists('aitf_verify_turnstile') && !aitf_verify_turnstile($turnstileToken, $error_message)) {
            if ($error_message === '') {
                $error_message = 'Captcha verification failed. Please try again.';
            }
        } else {
            $site_address = getOnlineAuditPostValue('site_address');
            $office_address = getOnlineAuditPostValue('office_address');
            $contact_designation = getOnlineAuditPostValue('contact_designation');
            $website = getOnlineAuditPostValue('website');
            $gstin = getOnlineAuditPostValue('gstin');
            $legal_status = getOnlineAuditPostValue('legal_status');
            $branch_offices = getOnlineAuditPostValue('branch_offices');
            $branch_details = getOnlineAuditPostValue('branch_details');
            $legal_obligations_applicable = getOnlineAuditPostValue('legal_obligations_applicable');
            $legal_obligations = getOnlineAuditPostValue('legal_obligations');

            $certTypes = [];
            if (isset($_POST['certification_type']) && is_array($_POST['certification_type'])) {
                foreach ($_POST['certification_type'] as $type) {
                    $clean = trim($type);
                    if ($clean !== '') {
                        $certTypes[] = $clean;
                    }
                }
            }
            $certification_type_other = getOnlineAuditPostValue('certification_type_other');
            if ($certification_type_other !== '') {
                $certTypes[] = 'Other: ' . $certification_type_other;
            }
            $certification_type = implode(', ', $certTypes);

            // Standards & Accreditation (multi rows)
            $standard_applied = [];
            $accreditation_requirement = [];
            $standard_input = isset($_POST['standard_applied']) && is_array($_POST['standard_applied']) ? $_POST['standard_applied'] : [];
            $accreditation_input = isset($_POST['accreditation_requirement']) && is_array($_POST['accreditation_requirement']) ? $_POST['accreditation_requirement'] : [];
            $maxPairs = max(count($standard_input), count($accreditation_input), 1);
            for ($i = 0; $i < $maxPairs; $i++) {
                $std = isset($standard_input[$i]) ? trim($standard_input[$i]) : '';
                $acc = isset($accreditation_input[$i]) ? trim($accreditation_input[$i]) : '';
                if ($std !== '') {
                    $standard_applied[] = $std . ($acc !== '' ? ' (' . $acc . ')' : '');
                }
                if ($acc !== '') {
                    $accreditation_requirement[] = $acc;
                }
            }
            $standard_applied_text = implode(', ', $standard_applied);
            $accreditation_requirement_text = implode(', ', $accreditation_requirement);
            // store string values for DB bind
            $standard_applied = $standard_applied_text;
            $accreditation_requirement = $accreditation_requirement_text;
            $scope_requirement = getOnlineAuditPostValue('scope_requirement');
            $multisite = getOnlineAuditPostValue('multisite');
            $multisite_functions = getOnlineAuditPostValue('multisite_functions');
            $multisite_sites = getOnlineAuditPostValue('multisite_sites');
            $employee_structure = getOnlineAuditPostValue('employee_structure');
            $onsite_installation = getOnlineAuditPostValue('onsite_installation');
            $onsite_details = getOnlineAuditPostValue('onsite_details');
            $part_time_personnel = getOnlineAuditPostValue('part_time_personnel');
            $part_time_count = getOnlineAuditPostValue('part_time_count');
            $employees_excluded = getOnlineAuditPostValue('employees_excluded');
            $employees_excluded_details = getOnlineAuditPostValue('employees_excluded_details');
            $consultancy_services_taken = getOnlineAuditPostValue('consultancy_services_taken');
            $consultancy_details = getOnlineAuditPostValue('consultancy_details');
            $org_type = getOnlineAuditPostValue('org_type');
            $major_customers = getOnlineAuditPostValue('major_customers');
            $main_products = getOnlineAuditPostValue('main_products');
            $raw_materials = getOnlineAuditPostValue('raw_materials');
            $operations = getOnlineAuditPostValue('operations');
            $external_processes = getOnlineAuditPostValue('external_processes');
            $design_responsibility = getOnlineAuditPostValue('design_responsibility');
            $other_certificates = getOnlineAuditPostValue('other_certificates');
            $system_implementation_date = getOnlineAuditPostValue('system_implementation_date');
            $proposed_audit_month = getOnlineAuditPostValue('proposed_audit_month');
            $sign_name = getOnlineAuditPostValue('sign_name');
            $sign_designation = getOnlineAuditPostValue('sign_designation');
            $sign_date = getOnlineAuditPostValue('sign_date');
            $declaration_confirmed = isset($_POST['declaration_confirmed']) ? 1 : 0;
            $process_flow_attachment_path = '';
            $process_flow_attachment_name = '';
            $organisation_brochure_attachment_path = '';
            $organisation_brochure_attachment_name = '';
            $customer_requirements_attachment_path = '';
            $customer_requirements_attachment_name = '';
            $uploadedSupportAttachments = [];

            // Multisite (Section 4) validation
            if ($multisite === 'Yes' && ($multisite_functions === '' || $multisite_sites === '')) {
                $error_message = 'For "Multisite" = Yes, please specify the centrally performed functions and list all sites to be covered in Section 4.';
            }

            if ($error_message === '') {
                $sign_image_path = '';
                if (isset($_FILES['sign_image']) && isset($_FILES['sign_image']['tmp_name']) && is_uploaded_file($_FILES['sign_image']['tmp_name'])) {
                    $tmpName = $_FILES['sign_image']['tmp_name'];
                    $originalName = $_FILES['sign_image']['name'];
                    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                    $ext = $ext ? strtolower($ext) : 'png';
                    $allowedExt = ['jpg', 'jpeg', 'png'];
                    $allowedMime = ['image/jpeg', 'image/png'];
                    $mime = '';
                    if (function_exists('finfo_open')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        if ($finfo) {
                            $mime = (string) finfo_file($finfo, $tmpName);
                            finfo_close($finfo);
                        }
                    }
                    if (in_array($ext, $allowedExt, true) && ($mime === '' || in_array($mime, $allowedMime, true))) {
                        $uploadDir = __DIR__ . '/uploads/signatures';
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0755, true);
                        }
                        if (is_dir($uploadDir) && is_writable($uploadDir)) {
                            $basename = 'sign_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                            $targetPath = $uploadDir . '/' . $basename;
                            if (@move_uploaded_file($tmpName, $targetPath)) {
                                $sign_image_path = 'uploads/signatures/' . $basename;
                            }
                        }
                    } else {
                        $error_message = 'Signature must be a valid JPG or PNG image.';
                    }
                }

                if ($error_message === '') {
                    $supportAllowedExt = ['png', 'jpg', 'jpeg'];
                    $supportAllowedMime = ['image/png', 'image/jpeg'];
                    $supportUploadDir = 'uploads/audit_application_attachments';
                    $processFlowUpload = saveOnlineAuditUploadedFile('process_flow_attachment', 'Process flow attachment', $supportUploadDir, $supportAllowedExt, $supportAllowedMime, $error_message);
                    if ($processFlowUpload !== false) {
                        $process_flow_attachment_path = $processFlowUpload['path'];
                        $process_flow_attachment_name = $processFlowUpload['name'];
                        if ($process_flow_attachment_path !== '') {
                            $uploadedSupportAttachments[] = ['label' => 'Process Flow', 'path' => $process_flow_attachment_path, 'name' => $process_flow_attachment_name, 'mime' => $processFlowUpload['mime'], 'extension' => $processFlowUpload['extension']];
                        }
                    }
                    if ($error_message === '') {
                        $brochureUpload = saveOnlineAuditUploadedFile('organisation_brochure_attachment', 'Organisation brochure attachment', $supportUploadDir, $supportAllowedExt, $supportAllowedMime, $error_message);
                        if ($brochureUpload !== false) {
                            $organisation_brochure_attachment_path = $brochureUpload['path'];
                            $organisation_brochure_attachment_name = $brochureUpload['name'];
                            if ($organisation_brochure_attachment_path !== '') {
                                $uploadedSupportAttachments[] = ['label' => 'Organisation Brochure', 'path' => $organisation_brochure_attachment_path, 'name' => $organisation_brochure_attachment_name, 'mime' => $brochureUpload['mime'], 'extension' => $brochureUpload['extension']];
                            }
                        }
                    }
                    if ($error_message === '') {
                        $requirementsUpload = saveOnlineAuditUploadedFile('customer_requirements_attachment', 'Customer requirements attachment', $supportUploadDir, $supportAllowedExt, $supportAllowedMime, $error_message);
                        if ($requirementsUpload !== false) {
                            $customer_requirements_attachment_path = $requirementsUpload['path'];
                            $customer_requirements_attachment_name = $requirementsUpload['name'];
                            if ($customer_requirements_attachment_path !== '') {
                                $uploadedSupportAttachments[] = ['label' => 'Customer Specific Requirements', 'path' => $customer_requirements_attachment_path, 'name' => $customer_requirements_attachment_name, 'mime' => $requirementsUpload['mime'], 'extension' => $requirementsUpload['extension']];
                            }
                        }
                    }
                }
            }

            if ($error_message !== '') {
                $createdUploads = [
                    $sign_image_path ?? '',
                    $process_flow_attachment_path,
                    $organisation_brochure_attachment_path,
                    $customer_requirements_attachment_path
                ];
                foreach ($createdUploads as $createdUpload) {
                    if ($createdUpload === '') {
                        continue;
                    }
                    $createdUploadPath = __DIR__ . '/' . str_replace('\\', '/', $createdUpload);
                    if (@file_exists($createdUploadPath)) {
                        @unlink($createdUploadPath);
                    }
                }
            }

            if ($error_message === '') {
                try {
                    $created_at = (new DateTimeImmutable('now', $indiaTz))->format('Y-m-d H:i:s');
                    $insertSql = "INSERT INTO online_audit_applications (
                    org_name, site_address, office_address, contact_person, contact_designation,
                    phone, email, website, gstin, legal_status,
                    branch_offices, branch_details, legal_obligations_applicable, legal_obligations,
                    certification_type, standard_applied, accreditation_requirement, scope_requirement,
                    multisite, multisite_functions, multisite_sites,
                    employee_structure, onsite_installation, onsite_details,
                    part_time_personnel, part_time_count, employees_excluded, employees_excluded_details,
                    consultancy_services_taken, consultancy_details,
                    org_type, major_customers, main_products, raw_materials, operations,
                    external_processes, design_responsibility, other_certificates,
                    system_implementation_date, proposed_audit_month,
                    sign_name, sign_designation, sign_date, sign_image_path,
                    process_flow_attachment_path, process_flow_attachment_name,
                    organisation_brochure_attachment_path, organisation_brochure_attachment_name,
                    customer_requirements_attachment_path, customer_requirements_attachment_name,
                    declaration_confirmed, created_at
                ) VALUES (
                    :org_name, :site_address, :office_address, :contact_person, :contact_designation,
                    :phone, :email, :website, :gstin, :legal_status,
                    :branch_offices, :branch_details, :legal_obligations_applicable, :legal_obligations,
                    :certification_type, :standard_applied, :accreditation_requirement, :scope_requirement,
                    :multisite, :multisite_functions, :multisite_sites,
                    :employee_structure, :onsite_installation, :onsite_details,
                    :part_time_personnel, :part_time_count, :employees_excluded, :employees_excluded_details,
                    :consultancy_services_taken, :consultancy_details,
                    :org_type, :major_customers, :main_products, :raw_materials, :operations,
                    :external_processes, :design_responsibility, :other_certificates,
                    :system_implementation_date, :proposed_audit_month,
                    :sign_name, :sign_designation, :sign_date, :sign_image_path,
                    :process_flow_attachment_path, :process_flow_attachment_name,
                    :organisation_brochure_attachment_path, :organisation_brochure_attachment_name,
                    :customer_requirements_attachment_path, :customer_requirements_attachment_name,
                    :declaration_confirmed, :created_at
                )";

                    $stmt = $db->prepare($insertSql);
                    $stmt->bindParam(':org_name', $org_name);
                    $stmt->bindParam(':site_address', $site_address);
                    $stmt->bindParam(':office_address', $office_address);
                    $stmt->bindParam(':contact_person', $contact_person);
                    $stmt->bindParam(':contact_designation', $contact_designation);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':website', $website);
                    $stmt->bindParam(':gstin', $gstin);
                    $stmt->bindParam(':legal_status', $legal_status);
                    $stmt->bindParam(':branch_offices', $branch_offices);
                    $stmt->bindParam(':branch_details', $branch_details);
                    $stmt->bindParam(':legal_obligations_applicable', $legal_obligations_applicable);
                    $stmt->bindParam(':legal_obligations', $legal_obligations);
                    $stmt->bindParam(':certification_type', $certification_type);
                    $stmt->bindParam(':standard_applied', $standard_applied);
                    $stmt->bindParam(':accreditation_requirement', $accreditation_requirement);
                    $stmt->bindParam(':scope_requirement', $scope_requirement);
                    $stmt->bindParam(':multisite', $multisite);
                    $stmt->bindParam(':multisite_functions', $multisite_functions);
                    $stmt->bindParam(':multisite_sites', $multisite_sites);
                    $stmt->bindParam(':employee_structure', $employee_structure);
                    $stmt->bindParam(':onsite_installation', $onsite_installation);
                    $stmt->bindParam(':onsite_details', $onsite_details);
                    $stmt->bindParam(':part_time_personnel', $part_time_personnel);
                    $stmt->bindParam(':part_time_count', $part_time_count);
                    $stmt->bindParam(':employees_excluded', $employees_excluded);
                    $stmt->bindParam(':employees_excluded_details', $employees_excluded_details);
                    $stmt->bindParam(':consultancy_services_taken', $consultancy_services_taken);
                    $stmt->bindParam(':consultancy_details', $consultancy_details);
                    $stmt->bindParam(':org_type', $org_type);
                    $stmt->bindParam(':major_customers', $major_customers);
                    $stmt->bindParam(':main_products', $main_products);
                    $stmt->bindParam(':raw_materials', $raw_materials);
                    $stmt->bindParam(':operations', $operations);
                    $stmt->bindParam(':external_processes', $external_processes);
                    $stmt->bindParam(':design_responsibility', $design_responsibility);
                    $stmt->bindParam(':other_certificates', $other_certificates);
                    $stmt->bindParam(':system_implementation_date', $system_implementation_date);
                    $stmt->bindParam(':proposed_audit_month', $proposed_audit_month);
                    $stmt->bindParam(':sign_name', $sign_name);
                    $stmt->bindParam(':sign_designation', $sign_designation);
                    $stmt->bindParam(':sign_date', $sign_date);
                    $stmt->bindParam(':sign_image_path', $sign_image_path);
                    $stmt->bindParam(':process_flow_attachment_path', $process_flow_attachment_path);
                    $stmt->bindParam(':process_flow_attachment_name', $process_flow_attachment_name);
                    $stmt->bindParam(':organisation_brochure_attachment_path', $organisation_brochure_attachment_path);
                    $stmt->bindParam(':organisation_brochure_attachment_name', $organisation_brochure_attachment_name);
                    $stmt->bindParam(':customer_requirements_attachment_path', $customer_requirements_attachment_path);
                    $stmt->bindParam(':customer_requirements_attachment_name', $customer_requirements_attachment_name);
                    $stmt->bindParam(':declaration_confirmed', $declaration_confirmed, PDO::PARAM_INT);
                    $stmt->bindParam(':created_at', $created_at);
                    $stmt->execute();

                    $insertedId = (int) $db->lastInsertId();

                    // Send notification emails (admin + applicant)
                    try {
                        if (!class_exists('PHPMailer')) {
                            require __DIR__ . '/PHPMailer/PHPMailerAutoload.php';
                        }

                        // Generate PDF for attachment
                        $pdfContent = '';
                        $pdfFilename = 'Application_Form_' . ($insertedId ?: time()) . '.pdf';
                        if ($insertedId > 0) {
                            $oldGet = $_GET;
                            $_GET['id'] = $insertedId;
                            if (!defined('ATTACH_PDF')) {
                                define('ATTACH_PDF', true);
                            }
                            $pdfResult = include __DIR__ . '/downloadOnlineApplication.php';
                            if (is_string($pdfResult) && $pdfResult !== '') {
                                $pdfContent = $pdfResult;
                            }
                            $_GET = $oldGet;
                        }

                        $supportAttachmentSummary = 'None uploaded';
                        if (!empty($uploadedSupportAttachments)) {
                            $summaryLines = [];
                            foreach ($uploadedSupportAttachments as $supportAttachment) {
                                $summaryLines[] = htmlspecialchars($supportAttachment['label'] . ': ' . $supportAttachment['name'], ENT_QUOTES, 'UTF-8');
                            }
                            $supportAttachmentSummary = implode('<br>', $summaryLines);
                        }

                        $addSupportAttachmentsToMail = function (PHPMailer $mailer) use ($uploadedSupportAttachments) {
                            foreach ($uploadedSupportAttachments as $supportAttachment) {
                                if (empty($supportAttachment['path'])) {
                                    continue;
                                }
                                $fullPath = __DIR__ . '/' . str_replace('\\', '/', $supportAttachment['path']);
                                if (@file_exists($fullPath)) {
                                    $mailer->addAttachment($fullPath, $supportAttachment['name'] ?: basename($fullPath));
                                }
                            }
                        };

                        $smtpOptions = [
                            'ssl' => [
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            ]
                        ];

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

                        $applySmtpConfig = function (PHPMailer $mailer, array $cfg) use ($mailConfig, $smtpOptions) {
                            $mailer->isSMTP();
                            $mailer->SMTPDebug = 0;
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
                            $mailer->SMTPOptions = $smtpOptions;
                            $mailer->Timeout = 8;
                        };

                        // Admin notification
                        $adminMail = new PHPMailer;
                        $adminMail->setFrom($mailConfig['from_email'] ?: 'no-reply@localhost', $mailConfig['from_name']);
                        $adminMail->Sender = $mailConfig['from_email'] ?: 'no-reply@localhost';
                        $adminMail->addCustomHeader('Return-Path', $mailConfig['from_email'] ?: 'no-reply@localhost');
                        $adminMail->addAddress($mailConfig['admin_email'], 'AITF Certification');
                        if (!empty($mailConfig['cc_email'])) {
                            $adminMail->addCC($mailConfig['cc_email'], 'AITF Team');
                        }
                        $adminMail->Subject = 'New Audit Application Submitted - ' . $org_name;

                        $contactFull = $contact_person . ($contact_designation ? ' (' . $contact_designation . ')' : '');
                        $adminBody = "<h3>New Audit Application Received</h3>"
                            . '<p style="font-family:Arial,sans-serif; font-size:13px; margin:6px 0 10px 0;">'
                            . 'This online audit application was submitted via the AITF website. The completed form PDF is attached below for your verification.'
                            . '</p>'
                            . '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse; font-family:Arial, sans-serif; font-size:13px;">'
                            . '<tr><th align="left">Organization name</th><td>' . htmlspecialchars($org_name, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                            . '<tr><th align="left">Contact person with designation</th><td>' . htmlspecialchars($contactFull, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                            . '<tr><th align="left">Telephone / Mobile</th><td>' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                            . '<tr><th align="left">E-Mail Id</th><td>' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                            . '<tr><th align="left">Address of site to be audited</th><td>' . nl2br(htmlspecialchars($site_address, ENT_QUOTES, 'UTF-8')) . '</td></tr>'
                            . '<tr><th align="left">Office address</th><td>' . nl2br(htmlspecialchars($office_address, ENT_QUOTES, 'UTF-8')) . '</td></tr>'
                            . '<tr><th align="left">Web-site</th><td>' . htmlspecialchars($website, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                            . '<tr><th align="left">Standards applied for</th><td>' . htmlspecialchars($standard_applied, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                            . '<tr><th align="left">GSTIN Number</th><td>' . htmlspecialchars($gstin, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                            . '<tr><th align="left">Supporting attachment(s)</th><td>' . $supportAttachmentSummary . '</td></tr>'
                            . '</table>';

                        $adminMail->isHTML(true);
                        $adminMail->Body = $adminBody;
                        $plainSupportSummary = empty($uploadedSupportAttachments) ? 'None uploaded' : implode(', ', array_map(function ($supportAttachment) {
                            return $supportAttachment['label'] . ': ' . $supportAttachment['name'];
                        }, $uploadedSupportAttachments));
                        $adminMail->AltBody = "Online audit application submitted via AITF website. Audit application form is attached for verification.\nOrganization: $org_name\nContact: $contactFull\nPhone: $phone\nEmail: $email\nSite address: $site_address\nOffice address: $office_address\nWebsite: $website\nStandards applied for: $standard_applied\nGSTIN: $gstin\nSupporting attachments: $plainSupportSummary";
                        if ($pdfContent !== '') {
                            $adminMail->addStringAttachment($pdfContent, $pdfFilename, 'base64', 'application/pdf');
                        }
                        $addSupportAttachmentsToMail($adminMail);

                        $adminSent = false;
                        foreach ($attempts as $attempt) {
                            $applySmtpConfig($adminMail, $attempt);
                            $adminSent = $adminMail->send();
                            if ($adminSent) {
                                break;
                            }
                            error_log('Audit admin mail SMTP fail: [' . $attempt['host'] . ':' . $attempt['port'] . ' secure=' . ($attempt['secure'] ?: 'none') . ' auth=' . ($attempt['auth'] ? 'yes' : 'no') . '] ' . $adminMail->ErrorInfo);
                            $adminMail->smtpClose();
                        }

                        // Applicant thank-you email
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $clientMail = new PHPMailer;
                            $clientMail->setFrom($mailConfig['from_email'] ?: 'no-reply@localhost', 'AITF Team');
                            $clientMail->Sender = $mailConfig['from_email'] ?: 'no-reply@localhost';
                            $clientMail->addCustomHeader('Return-Path', $mailConfig['from_email'] ?: 'no-reply@localhost');
                            $clientMail->addAddress($email, $contact_person);
                            $clientMail->Subject = 'Thank you for your application - AITF';
                            $clientBody = "<p style=\"font-family:Arial,sans-serif; font-size:14px;\">Hi " . htmlspecialchars($contact_person, ENT_QUOTES, 'UTF-8') . ",</p>"
                                . "<p style=\"font-family:Arial,sans-serif; font-size:14px; margin:8px 0;\">Thank you for choosing AITF.</p>"
                                . "<p style=\"font-family:Arial,sans-serif; font-size:14px; margin:8px 0;\">We confirm that we have successfully received your <strong>Audit Application Form</strong>. Our assessment team is currently reviewing the details submitted by you.</p>"
                                . "<p style=\"font-family:Arial,sans-serif; font-size:14px; margin:8px 0;\">Once the verification process is completed, our team will contact you to guide you through the next steps of the audit process.</p>"
                                . "<p style=\"font-family:Arial,sans-serif; font-size:14px; margin:8px 0;\"><strong>Application Summary:</strong><br>Organization Name: " . htmlspecialchars($org_name, ENT_QUOTES, 'UTF-8') . "<br>Contact Number: " . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . "<br>Email Address: " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "<br>Applied Assessment: " . htmlspecialchars($standard_applied, ENT_QUOTES, 'UTF-8') . "</p>"
                                . "<p style=\"font-family:Arial,sans-serif; font-size:14px; margin:8px 0;\">A copy of your submitted Audit Application Form is attached for your reference.</p>"
                                . "<p style=\"font-family:Arial,sans-serif; font-size:14px; margin:8px 0;\">If you have any questions in the meantime, feel free to reply to this email. Our team will be happy to assist you.</p>"
                                . "<p style=\"font-family:Arial,sans-serif; font-size:14px; margin:8px 0;\">Warm regards,<br><strong>AITF Team</strong><br>Assessment Department<br>AITF<br><br>Email: <a href=\"mailto:" . $mailFromEmailHtml . "\">" . $mailFromEmailHtml . "</a><br>" . ($mailCcEmailHtml !== '' ? "Email: <a href=\"mailto:" . $mailCcEmailHtml . "\">" . $mailCcEmailHtml . "</a><br>" : "") . "Website: <a href=\"http://www.aitf.in\" target=\"_blank\" rel=\"noopener\">www.aitf.in</a></p>";
                            $clientMail->isHTML(true);
                            $clientMail->Body = $clientBody;
                            $clientMail->AltBody = "Dear $contact_person,\n\nThank you for submitting your audit application to AITF.\nWe have received your application and will review it.\nA copy of your submitted application form (system generated PDF) is attached for your reference.\n\nRegards,\nAITF Team";
                            if ($pdfContent !== '') {
                                $clientMail->addStringAttachment($pdfContent, $pdfFilename, 'base64', 'application/pdf');
                            }

                            foreach ($attempts as $attempt) {
                                $applySmtpConfig($clientMail, $attempt);
                                $clientSent = $clientMail->send();
                                if ($clientSent) {
                                    break;
                                }
                                error_log('Audit client mail SMTP fail: [' . $attempt['host'] . ':' . $attempt['port'] . ' secure=' . ($attempt['secure'] ?: 'none') . ' auth=' . ($attempt['auth'] ? 'yes' : 'no') . '] ' . $clientMail->ErrorInfo);
                                $clientMail->smtpClose();
                            }
                        }
                    } catch (Exception $mailEx) {
                        // Mail failures should not block submission
                    }

                    header('Location: application.php?submitted=1');
                    exit;
                } catch (Exception $e) {
                    $error_message = 'There was a problem submitting your application. Please try again later.';
                }
            }
        }
    }

    try {
        $stmt = $db->query("SELECT id, org_name, contact_person, email, phone, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i') AS created_at FROM online_audit_applications ORDER BY created_at DESC LIMIT 50");
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $applications = [];
    }
}

if (isset($_GET['submitted']) && $_GET['submitted'] === '1') {
    $success_message = 'Thank you, your application has been received.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include './headSource.php'; ?>
    <?php if ($turnstileReady): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #6c63ff;
            --secondary: #4a90e2;
            --accent: #ff6b6b;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text: #2d3436;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f7fb;
            font-family: 'Poppins', sans-serif;
            color: #1f2933;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header Styling */
        .heading_main h2 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(45deg, #2196F3, #00BCD4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
        }

        .heading_main h2:after {
            content: '';
            position: absolute;
            width: 60%;
            height: 4px;
            background: linear-gradient(90deg, #2196F3, #00BCD4);
            bottom: 0;
            left: 20%;
            border-radius: 2px;
        }

        .form-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 32px;
            margin: 16px 0 24px;
            max-width: 100%;
            width: 100%;
            transition: none;
            position: relative;
            overflow: visible;
            box-shadow: 0 10px 32px rgba(15, 23, 42, 0.08);
        }

        /* 3D Button with Hover Effect */
        .download-btn,
        .submit-btn {
            transition: none;
            box-shadow: none;
        }

        .download-btn:hover,
        .download-btn:active,
        .submit-btn:hover,
        .submit-btn:active {
            transform: none;
            box-shadow: none;
            letter-spacing: 1px;
        }

        .download-btn::before,
        .download-btn:hover::before {
            opacity: 0;
        }

        .download-btn i {
            margin-right: 10px;
            transition: none;
        }

        /* Force full-width layout for this form */
        .section>.container {
            max-width: 100% !important;
            width: 100% !important;
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .container .col-lg-10,
        .container .col-md-10,
        .container .col-sm-12 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        /* Remove spinner buttons on number inputs */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        /* Signature upload block */
        .signature-upload {
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
        }

        .signature-upload-input {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .signature-upload-input input[type="file"] {
            width: 100%;
            border: 1px dashed #cbd5e1;
            padding: 10px;
            border-radius: 10px;
            background: #fff;
        }

        .signature-upload-input small {
            color: #6b7280;
        }

        .signature-preview {
            min-height: 130px;
            max-width: 320px;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            color: #94a3b8;
            font-size: 0.9rem;
            margin-top: 4px;
        }

        #sign-image-preview img {
            max-height: 160px;
            max-width: 260px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .optional-attachments {
            margin-top: 14px;
            padding: 14px;
            background: #ffffff;
            border: 1px solid #dbeafe;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.07);
        }

        .optional-attachments-title {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #1e3a8a;
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .optional-attachments-hint {
            color: #64748b;
            font-size: 0.82rem;
            line-height: 1.5;
            margin: 0 0 12px;
        }

        .attachment-upload-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 10px 0;
            border-top: 1px solid #eef2ff;
        }

        .attachment-upload-item:first-of-type {
            border-top: 0;
            padding-top: 0;
        }

        .attachment-upload-item label {
            margin-bottom: 0;
            color: #334155;
            font-size: 0.86rem;
            font-weight: 700;
        }

        .attachment-upload-item input[type="file"] {
            border: 1px dashed #93c5fd;
            background: #f8fbff;
            padding: 9px;
            border-radius: 10px;
            font-size: 0.86rem;
        }

        .submit-btn {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border: none;
            padding: 14px 38px;
            border-radius: 999px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            cursor: pointer;
            transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.15s ease;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.35);
            width: 100%;
            max-width: 240px;
            display: block;
            margin: 20px auto 0;
        }

        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(29, 78, 216, 0.4);
        }

        .submit-btn:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 8px 18px rgba(29, 78, 216, 0.35);
        }

        .submit-btn:disabled,
        .submit-btn.disabled {
            opacity: 0.55;
            cursor: not-allowed;
            box-shadow: none;
        }

        .required-warning {
            text-align: center;
            color: #d97706;
            font-weight: 700;
            font-size: 0.95rem;
            margin-top: 8px;
            display: none;
        }

        .captcha-wrap {
            width: 100%;
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: center;
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
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
            color: #6b7280;
            text-align: center;
            line-height: 1.5;
        }

        .captcha-status {
            display: none;
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.24);
            color: #b45309;
            font-size: 0.9rem;
            font-weight: 700;
            text-align: center;
        }

        .captcha-status.show {
            display: block;
        }

        /* Submit overlay */
        .submit-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.68);
            backdrop-filter: blur(7px);
            -webkit-backdrop-filter: blur(7px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 2500;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .submit-overlay.show {
            opacity: 1;
            pointer-events: all;
        }

        .submit-modal {
            width: min(420px, 92vw);
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.96);
            border-radius: 18px;
            padding: 30px 28px 26px;
            text-align: center;
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.32);
            transform: translateY(10px) scale(0.98);
            transition: transform 0.2s ease;
        }

        .submit-overlay.show .submit-modal {
            transform: translateY(0) scale(1);
        }

        .loader-ring {
            position: relative;
            width: 74px;
            height: 74px;
            margin: 0 auto 18px;
            border: 5px solid #e0f2fe;
            border-top-color: #2563eb;
            border-right-color: #22c55e;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
        }

        .loader-ring::after {
            content: '';
            position: absolute;
            inset: 17px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #22c55e);
            box-shadow: inset 0 0 0 8px #ffffff;
        }

        .submit-modal h4 {
            margin: 0 0 8px;
            color: #0f172a;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: 0;
        }

        .submit-modal p {
            margin: 0;
            color: #64748b;
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .submit-progress {
            display: flex;
            justify-content: center;
            gap: 7px;
            margin-top: 18px;
        }

        .submit-progress span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2563eb;
            opacity: 0.35;
            animation: submitDot 1s ease-in-out infinite;
        }

        .submit-progress span:nth-child(2) {
            animation-delay: 0.16s;
        }

        .submit-progress span:nth-child(3) {
            animation-delay: 0.32s;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes submitDot {

            0%,
            80%,
            100% {
                transform: translateY(0);
                opacity: 0.35;
            }

            40% {
                transform: translateY(-6px);
                opacity: 1;
            }
        }

        .required-label {
            color: #c53030;
            font-weight: 700;
            margin-left: 4px;
        }

        #add-standard-row {
            margin-left: 12px;
            padding: 6px 12px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #2196F3;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.2);
            outline: none;
        }

        /* Submit Button */
        .submit-btn {
            background: linear-gradient(45deg, #4CAF50, #8BC34A);
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            width: 100%;
            max-width: 200px;
            display: block;
            margin: 20px auto 0;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(76, 175, 80, 0.4);
        }

        /* PDF Viewer Styling */
        .pdf-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin: 30px 0;
            overflow: hidden;
        }

        .pdf-container embed {
            width: 100%;
            height: 700px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        /* Animated Section Headers */
        .section-header {
            text-align: center;
            margin: 50px 0;
            position: relative;
            z-index: 1;
        }

        .section-header h2 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(45deg, #fff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
            box-shadow: 0 0 15px var(--primary);
        }

        .section-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .section-header h3:after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #2196F3, #00BCD4);
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .section-header p {
            color: #666;
            font-size: 1.1rem;
            margin-top: 15px;
        }

        .animate-up,
        .animate-float,
        .animate-pulse {
            opacity: 1;
            animation: none;
        }

        /* Decorative Elements */
        .decorative-shape,
        .shape-1,
        .shape-2 {
            display: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .heading_main h2 {
                font-size: 2.2rem;
            }

            .pdf-container embed {
                height: 500px;
            }

            .form-card {
                padding: 20px 15px;
            }

            .app-hero-header {
                padding: 145px 0 28px !important;
            }

            .app-hero-header h1 {
                font-size: 1.8rem !important;
            }

            .app-hero-header p {
                font-size: 0.9rem !important;
            }
        }

        @media (max-width: 576px) {
            .app-hero-header {
                padding: 140px 0 24px !important;
            }

            .app-hero-header h1 {
                font-size: 1.5rem !important;
            }

            .captcha-wrap .cf-turnstile {
                transform: scale(0.92);
                transform-origin: center top;
            }
        }

        /* ===== Tab Navigation ===== */
        .app-tab-bar {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 12px !important;
            padding: 20px 0 !important;
            margin: 0 auto !important;
            position: relative !important;
            z-index: 10 !important;
            list-style: none !important;
            border-bottom: none !important;
            max-width: 700px !important;
        }

        .app-tab-btn {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px !important;
            padding: 14px 28px !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            letter-spacing: 0.02em !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 12px !important;
            cursor: pointer !important;
            transition: all 0.25s ease !important;
            background: #ffffff !important;
            color: #64748b !important;
            margin: 0 !important;
            outline: none !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
            text-transform: none !important;
            line-height: 1.4 !important;
            white-space: nowrap !important;
            opacity: 1 !important;
            visibility: visible !important;
            flex: 1 !important;
            max-width: 320px !important;
        }

        .app-tab-btn .tab-badge {
            display: inline-block !important;
            padding: 2px 8px !important;
            font-size: 0.58rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            border-radius: 999px !important;
            background: #e0e7ff !important;
            color: #3b82f6 !important;
            line-height: 1.4 !important;
            vertical-align: middle !important;
        }

        .app-tab-btn.active {
            background: linear-gradient(135deg, #eff6ff, #f0f4ff) !important;
            color: #1e3a8a !important;
            border: 2px solid #2563eb !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.15), 0 0 0 1px rgba(37, 99, 235, 0.06) !important;
        }

        .app-tab-btn.active .tab-badge {
            background: #2563eb !important;
            color: #fff !important;
        }

        .app-tab-btn.active i {
            color: #2563eb !important;
        }

        .app-tab-btn:not(.active):hover {
            border-color: #93c5fd !important;
            color: #1e40af !important;
            background: #fafbff !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08) !important;
        }

        .app-tab-btn i {
            font-size: 1.05rem !important;
            color: inherit !important;
        }

        .app-tab-panel {
            display: none !important;
        }

        .app-tab-panel.active {
            display: block !important;
        }

        /* Download Tab Card */
        .download-tab-card {
            background: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 16px !important;
            padding: 40px 36px !important;
            box-shadow: 0 10px 32px rgba(15, 23, 42, 0.08) !important;
            max-width: 780px !important;
            margin: 0 auto !important;
        }

        .download-tab-card .dl-icon {
            width: 72px !important;
            height: 72px !important;
            border-radius: 16px !important;
            background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 auto 20px !important;
        }

        .download-tab-card .dl-icon i {
            font-size: 2rem !important;
            color: #2563eb !important;
        }

        .download-tab-card h3 {
            font-size: 1.4rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin-bottom: 10px !important;
        }

        .download-tab-card .dl-desc {
            color: #64748b !important;
            font-size: 0.95rem !important;
            line-height: 1.7 !important;
            margin-bottom: 24px !important;
        }

        .download-tab-card .dl-steps {
            text-align: left !important;
            margin-bottom: 28px !important;
            list-style: none !important;
            padding: 0 !important;
        }

        .download-tab-card .dl-steps li {
            display: flex !important;
            align-items: flex-start !important;
            gap: 12px !important;
            padding: 10px 0 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            color: #334155 !important;
            font-size: 0.93rem !important;
            line-height: 1.6 !important;
        }

        .download-tab-card .dl-steps li:last-child {
            border-bottom: none !important;
        }

        .download-tab-card .dl-steps .step-num {
            flex-shrink: 0 !important;
            width: 26px !important;
            height: 26px !important;
            border-radius: 50% !important;
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            color: #fff !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-top: 2px !important;
        }

        .dl-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 13px 32px !important;
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            color: #fff !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 0.95rem !important;
            font-weight: 600 !important;
            border: none !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            text-decoration: none !important;
            transition: all 0.25s ease !important;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.25) !important;
        }

        .dl-btn:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 10px 28px rgba(37, 99, 235, 0.35) !important;
            color: #fff !important;
            text-decoration: none !important;
        }

        .dl-btn i {
            font-size: 1.1rem !important;
        }

        .dl-email-note {
            margin-top: 20px !important;
            padding: 12px 18px !important;
            background: #fffbeb !important;
            border: 1px solid #fde68a !important;
            border-radius: 10px !important;
            color: #92400e !important;
            font-size: 0.85rem !important;
            line-height: 1.6 !important;
        }

        .dl-email-note i {
            color: #d97706 !important;
            margin-right: 6px !important;
        }

        @media (max-width: 768px) {
            .app-tab-bar {
                max-width: 100% !important;
                padding: 12px 10px !important;
                gap: 8px !important;
            }

            .app-tab-btn {
                padding: 12px 16px !important;
                font-size: 0.8rem !important;
            }

            .app-tab-btn .tab-badge {
                display: none !important;
            }

            .download-tab-card {
                padding: 28px 20px !important;
            }

            .download-tab-card h3 {
                font-size: 1.2rem !important;
            }
        }

        @media (max-width: 576px) {
            .app-tab-bar {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
                padding: 10px 12px !important;
            }

            .app-tab-btn {
                max-width: 100% !important;
                justify-content: center !important;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">

    <head>
        <?php include './headerSource.php'; ?>
    </head>

    <!-- Online Form Header -->
    <div class="section app-hero-header"
        style="padding: 180px 0 36px; background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); position: relative; z-index: 1;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div style="text-align: center;">
                        <h1
                            style="color: #fff; font-size: 2.4rem; font-weight: 800; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1.5px; position: relative; display: inline-block; line-height: 1.2;">
                            APPLICATION FORM
                            <span
                                style="display: block; width: 60px; height: 3px; background: #ffffff; margin: 10px auto 0; border-radius: 2px;"></span>
                        </h1>
                        <p
                            style="color: rgba(255,255,255,0.85); font-size: 1rem; max-width: 600px; margin: 12px auto 0; line-height: 1.6;">
                            Fill the form below and take the first step towards excellence.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation Bar -->
    <div style="background: #f8fafc; padding: 0; position: relative; z-index: 10; border-bottom: 1px solid #e2e8f0;">
        <div class="container" style="max-width: 1140px; margin: 0 auto; padding: 0 15px;">
            <div class="app-tab-bar" id="appTabBar"
                style="display: flex; justify-content: center; align-items: center; max-width: 700px; margin: 0 auto; padding: 20px 0; gap: 12px; list-style: none; position: relative; z-index: 10;">
                <button class="app-tab-btn active" data-tab="download-tab" type="button"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 14px 28px; font-family: Poppins, sans-serif; font-size: 0.88rem; font-weight: 700; border: 2px solid #2563eb; border-radius: 12px; cursor: pointer; background: linear-gradient(135deg, #eff6ff, #f0f4ff); color: #1e3a8a; transition: all 0.25s ease; outline: none; white-space: nowrap; flex: 1; max-width: 320px; box-shadow: 0 4px 16px rgba(37,99,235,0.15), 0 0 0 1px rgba(37,99,235,0.06);">
                    <i class="fa fa-download" style="color: #2563eb; font-size: 1.05rem;"></i>
                    Download &amp; Submit
                    <span class="tab-badge"
                        style="display: inline-block; padding: 2px 8px; font-size: 0.58rem; font-weight: 700; text-transform: uppercase; border-radius: 999px; background: #2563eb; color: #fff; line-height: 1.4;">Recommended</span>
                </button>
                <button class="app-tab-btn" data-tab="online-tab" type="button"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 14px 28px; font-family: Poppins, sans-serif; font-size: 0.88rem; font-weight: 600; border: 2px solid #e2e8f0; border-radius: 12px; cursor: pointer; background: #ffffff; color: #64748b; transition: all 0.25s ease; outline: none; white-space: nowrap; flex: 1; max-width: 320px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <i class="fa fa-pencil-square-o" style="font-size: 1.05rem;"></i>
                    Fill Online
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 1: Download Application Form (Default) -->
    <div class="app-tab-panel active" id="download-tab">
        <div class="section" style="padding: 36px 0 50px; background: #f5f7fb;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="download-tab-card" style="text-align: center;">
                            <div class="dl-icon">
                                <i class="fa fa-file-pdf-o"></i>
                            </div>
                            <h3>Download Audit Application Form</h3>
                            <p class="dl-desc">
                                Please carefully fill out the application form below. Make sure all fields are completed
                                accurately before submitting. Incomplete or incorrect forms may delay the certification
                                process.
                            </p>

                            <ul class="dl-steps">
                                <li>
                                    <span class="step-num">1</span>
                                    <span><strong>Download</strong> the application form PDF using the button
                                        below.</span>
                                </li>
                                <li>
                                    <span class="step-num">2</span>
                                    <span><strong>Fill out</strong> all required fields carefully. Ensure organization
                                        details, contact information, and scope are accurate.</span>
                                </li>
                                <li>
                                    <span class="step-num">3</span>
                                    <span><strong>Sign</strong> the declaration section with your authorized signature
                                        and date.</span>
                                </li>
                                <li>
                                    <span class="step-num">4</span>
                                    <span><strong>Submit</strong> the completed form via email to <a
                                            href="mailto:certification@aitf.co.in"
                                            style="color:#2563eb; font-weight:600; text-decoration:none;">certification@aitf.co.in</a>
                                        or upload through our portal.</span>
                                </li>
                            </ul>

                            <a href="text/Audit application form QMS, EMS & OHSMS.pdf" download class="dl-btn">
                                <i class="fa fa-download"></i>
                                Download Application Form (PDF)
                            </a>

                            <div class="dl-email-note">
                                <i class="fa fa-info-circle"></i>
                                <strong>Note:</strong> After filling out the form, please email the scanned copy to <a
                                    href="mailto:certification@aitf.co.in"
                                    style="color:#92400e; font-weight:600;">certification@aitf.co.in</a> along with
                                supporting documents. Our team will review and get back to you within 2-3 business days.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: Fill Online Form -->
    <div class="app-tab-panel" id="online-tab">
        <div class="section" id="online-application-form" style="padding: 25px 0 40px;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="form-card" style="background: rgba(255, 255, 255, 0.98); color: #1f2933;">
                            <?php if (!empty($success_message)) { ?>
                                <div class="alert alert-success"
                                    style="border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
                                    <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php } elseif (!empty($error_message)) { ?>
                                <div class="alert alert-danger"
                                    style="border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
                                    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php } ?>
                            <form method="post" action="" enctype="multipart/form-data"
                                id="online-application-form-main">
                                <input type="hidden" name="public_audit_application" value="1">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 style="margin-bottom: 20px; font-weight: 700;">1. Data of Organisation
                                            Applying for Certification</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Organization name <span class="required-label">*</span></label>
                                            <input type="text" name="org_name" class="form-control" required
                                                value="<?php echo isset($_POST['org_name']) ? htmlspecialchars($_POST['org_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Contact person with designation <span
                                                    class="required-label">*</span></label>
                                            <input type="text" name="contact_person" class="form-control" required
                                                value="<?php echo isset($_POST['contact_person']) ? htmlspecialchars($_POST['contact_person'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Designation</label>
                                            <input type="text" name="contact_designation" class="form-control"
                                                value="<?php echo isset($_POST['contact_designation']) ? htmlspecialchars($_POST['contact_designation'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Address of site to be audited <span
                                                    class="required-label">*</span></label>
                                            <textarea name="site_address" class="form-control" rows="2"
                                                required><?php echo isset($_POST['site_address']) ? htmlspecialchars($_POST['site_address'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Office address <span class="required-label">*</span></label>
                                            <textarea name="office_address" class="form-control" rows="2"
                                                required><?php echo isset($_POST['office_address']) ? htmlspecialchars($_POST['office_address'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Telephone / Mobile <span class="required-label">*</span></label>
                                            <input type="text" name="phone" class="form-control" required
                                                value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>E-Mail Id <span class="required-label">*</span></label>
                                            <input type="email" name="email" class="form-control" required
                                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Web-site <span class="required-label">*</span></label>
                                            <input type="text" name="website" class="form-control" required
                                                value="<?php echo isset($_POST['website']) ? htmlspecialchars($_POST['website'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>GSTIN Number <span class="required-label">*</span></label>
                                            <input type="text" name="gstin" class="form-control" required
                                                value="<?php echo isset($_POST['gstin']) ? htmlspecialchars($_POST['gstin'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="margin-top: 15px;">
                                        <h4 style="margin-bottom: 15px; font-weight: 700;">2. Company Structure</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Legal status</label>
                                            <select name="legal_status" class="form-control">
                                                <option value="">Select</option>
                                                <option value="Proprietary" <?php echo (isset($_POST['legal_status']) && $_POST['legal_status'] === 'Proprietary') ? 'selected' : ''; ?>>
                                                    Proprietary</option>
                                                <option value="Partnership" <?php echo (isset($_POST['legal_status']) && $_POST['legal_status'] === 'Partnership') ? 'selected' : ''; ?>>
                                                    Partnership</option>
                                                <option value="Pvt. Ltd." <?php echo (isset($_POST['legal_status']) && $_POST['legal_status'] === 'Pvt. Ltd.') ? 'selected' : ''; ?>>Pvt.
                                                    Ltd.</option>
                                                <option value="Limited" <?php echo (isset($_POST['legal_status']) && $_POST['legal_status'] === 'Limited') ? 'selected' : ''; ?>>Limited
                                                </option>
                                                <option value="Trust" <?php echo (isset($_POST['legal_status']) && $_POST['legal_status'] === 'Trust') ? 'selected' : ''; ?>>Trust
                                                </option>
                                                <option value="Other" <?php echo (isset($_POST['legal_status']) && $_POST['legal_status'] === 'Other') ? 'selected' : ''; ?>>Other
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Branch offices / group companies</label>
                                            <div>
                                                <label style="margin-right:15px;"><input type="radio"
                                                        name="branch_offices" value="Yes" <?php echo (isset($_POST['branch_offices']) && $_POST['branch_offices'] === 'Yes') ? 'checked' : ''; ?>>
                                                    Yes</label>
                                                <label><input type="radio" name="branch_offices" value="No" <?php echo (isset($_POST['branch_offices']) && $_POST['branch_offices'] === 'No') ? 'checked' : ''; ?>>
                                                    No</label>
                                            </div>
                                            <textarea name="branch_details" class="form-control" rows="2"
                                                placeholder="If yes, please specify sites / branches."><?php echo isset($_POST['branch_details']) ? htmlspecialchars($_POST['branch_details'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Any legal (statutory and regulatory) obligations applicable to your
                                                product / service?</label>
                                            <div>
                                                <label style="margin-right:15px;"><input type="radio"
                                                        name="legal_obligations_applicable" value="Yes" <?php echo (isset($_POST['legal_obligations_applicable']) && $_POST['legal_obligations_applicable'] === 'Yes') ? 'checked' : ''; ?>> Yes</label>
                                                <label><input type="radio" name="legal_obligations_applicable"
                                                        value="No" <?php echo (isset($_POST['legal_obligations_applicable']) && $_POST['legal_obligations_applicable'] === 'No') ? 'checked' : ''; ?>> No</label>
                                            </div>
                                            <textarea name="legal_obligations" class="form-control" rows="2"
                                                placeholder="If yes, please specify."><?php echo isset($_POST['legal_obligations']) ? htmlspecialchars($_POST['legal_obligations'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="margin-top: 15px;">
                                        <h4 style="margin-bottom: 15px; font-weight: 700;">3. Certification Requirement
                                        </h4>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Type of certification requested</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label><input type="checkbox" name="certification_type[]"
                                                            value="First Certification" <?php echo (isset($_POST['certification_type']) && is_array($_POST['certification_type']) && in_array('First Certification', $_POST['certification_type'])) ? 'checked' : ''; ?>> First Certification</label>
                                                </div>
                                                <div class="col-md-6">
                                                    <label><input type="checkbox" name="certification_type[]"
                                                            value="Recertification" <?php echo (isset($_POST['certification_type']) && is_array($_POST['certification_type']) && in_array('Recertification', $_POST['certification_type'])) ? 'checked' : ''; ?>> Recertification</label>
                                                </div>
                                                <div class="col-md-6">
                                                    <label><input type="checkbox" name="certification_type[]"
                                                            value="Extension - Scope" <?php echo (isset($_POST['certification_type']) && is_array($_POST['certification_type']) && in_array('Extension - Scope', $_POST['certification_type'])) ? 'checked' : ''; ?>> Extension (Scope)</label>
                                                </div>
                                                <div class="col-md-6">
                                                    <label><input type="checkbox" name="certification_type[]"
                                                            value="Extension - Site" <?php echo (isset($_POST['certification_type']) && is_array($_POST['certification_type']) && in_array('Extension - Site', $_POST['certification_type'])) ? 'checked' : ''; ?>> Extension (Site)</label>
                                                </div>
                                                <div class="col-md-6">
                                                    <label><input type="checkbox" name="certification_type[]"
                                                            value="Transfer of Certificate (Change of CB)" <?php echo (isset($_POST['certification_type']) && is_array($_POST['certification_type']) && in_array('Transfer of Certificate (Change of CB)', $_POST['certification_type'])) ? 'checked' : ''; ?>>
                                                        Transfer of Certificate (Change of CB)</label>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" name="certification_type_other"
                                                        class="form-control" placeholder="Other (please specify)"
                                                        value="<?php echo isset($_POST['certification_type_other']) ? htmlspecialchars($_POST['certification_type_other'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    $isoOptions = ['ISO 9001', 'ISO 14001', 'ISO 45001', 'ISO 27001'];
                                    $accrOptions = ['NABCB', 'SELF'];
                                    $stdInput = isset($_POST['standard_applied']) && is_array($_POST['standard_applied']) ? $_POST['standard_applied'] : [''];
                                    $accInput = isset($_POST['accreditation_requirement']) && is_array($_POST['accreditation_requirement']) ? $_POST['accreditation_requirement'] : [''];
                                    $rowCount = max(count($stdInput), count($accInput), 1);
                                    ?>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label style="display:flex; align-items:center; gap:8px;">
                                                <span>Standards & Accreditation</span>
                                                <button type="button" class="btn btn-sm btn-success"
                                                    id="add-standard-row"><i class="fa fa-plus"></i> Add</button>
                                            </label>
                                            <div id="standard-rows">
                                                <?php for ($i = 0; $i < $rowCount; $i++):
                                                    $stdVal = isset($stdInput[$i]) ? $stdInput[$i] : '';
                                                    $accVal = isset($accInput[$i]) ? $accInput[$i] : '';
                                                    ?>
                                                    <div class="row align-items-end standard-row"
                                                        style="margin-bottom:10px;">
                                                        <div class="col-md-6">
                                                            <label>Standard applied for</label>
                                                            <select name="standard_applied[]" class="form-control">
                                                                <option value="">Select</option>
                                                                <?php foreach ($isoOptions as $opt):
                                                                    $sel = ($stdVal === $opt) ? 'selected' : '';
                                                                    ?>
                                                                    <option
                                                                        value="<?php echo htmlspecialchars($opt, ENT_QUOTES, 'UTF-8'); ?>"
                                                                        <?php echo $sel; ?>>
                                                                        <?php echo htmlspecialchars($opt, ENT_QUOTES, 'UTF-8'); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Accreditation requirement</label>
                                                            <select name="accreditation_requirement[]" class="form-control">
                                                                <option value="">Select</option>
                                                                <?php foreach ($accrOptions as $opt):
                                                                    $sel = ($accVal === $opt) ? 'selected' : '';
                                                                    ?>
                                                                    <option
                                                                        value="<?php echo htmlspecialchars($opt, ENT_QUOTES, 'UTF-8'); ?>"
                                                                        <?php echo $sel; ?>>
                                                                        <?php echo htmlspecialchars($opt, ENT_QUOTES, 'UTF-8'); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                            <small style="color:#6b7280;">Use Add to include multiple ISO
                                                Standards.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Please mention your requirement regarding Scope / Activity to be
                                                certified</label>
                                            <textarea name="scope_requirement" class="form-control"
                                                rows="3"><?php echo isset($_POST['scope_requirement']) ? htmlspecialchars($_POST['scope_requirement'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="margin-top: 15px;">
                                        <h4 style="margin-bottom: 15px; font-weight: 700;">4. Multisite Information</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Is your organisation having multisite?</label>
                                            <div>
                                                <label style="margin-right:15px;"><input type="radio" name="multisite"
                                                        value="Yes" <?php echo (isset($_POST['multisite']) && $_POST['multisite'] === 'Yes') ? 'checked' : ''; ?>> Yes</label>
                                                <label><input type="radio" name="multisite" value="No" <?php echo (isset($_POST['multisite']) && $_POST['multisite'] === 'No') ? 'checked' : ''; ?>> No</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>If yes, what functions are performed centrally?</label>
                                            <textarea name="multisite_functions" class="form-control" rows="2"
                                                placeholder="e.g. Marketing, Purchasing, HR, Top Management, etc."><?php echo isset($_POST['multisite_functions']) ? htmlspecialchars($_POST['multisite_functions'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>List all sites to be covered (with address and key processes)</label>
                                            <textarea name="multisite_sites" class="form-control" rows="3"
                                                placeholder="Site 1: Address, Processes / Operations&#10;Site 2: Address, Processes / Operations&#10;..."><?php echo isset($_POST['multisite_sites']) ? htmlspecialchars($_POST['multisite_sites'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="margin-top: 15px;">
                                        <h4 style="margin-bottom: 15px; font-weight: 700;">5. Employee Structure</h4>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="employee-structure-wrapper">
                                            <p style="font-size: 0.9rem; color: #4b5563; margin-bottom: 8px;">
                                                Please provide approximate number of employees by department and shift,
                                                similar to the official form.
                                            </p>
                                            <div class="table-responsive">
                                                <table class="table table-bordered employee-structure-table"
                                                    id="employee-structure-table">
                                                    <thead>
                                                        <tr>
                                                            <th rowspan="2"
                                                                style="vertical-align: middle; min-width: 180px;">
                                                                Department</th>
                                                            <th colspan="2" style="text-align: center;">General shift
                                                            </th>
                                                            <th colspan="2" style="text-align: center;">Shift I</th>
                                                            <th colspan="2" style="text-align: center;">Shift II</th>
                                                            <th colspan="2" style="text-align: center;">Shift III</th>
                                                        </tr>
                                                        <tr>
                                                            <th style="text-align: center;">Permanent</th>
                                                            <th style="text-align: center;">Contract</th>
                                                            <th style="text-align: center;">Per.</th>
                                                            <th style="text-align: center;">Contract</th>
                                                            <th style="text-align: center;">Per.</th>
                                                            <th style="text-align: center;">Contract</th>
                                                            <th style="text-align: center;">Per.</th>
                                                            <th style="text-align: center;">Contract</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr data-dept-label="Top management / Administrative staff">
                                                            <td>Top management, Administrative staff (Marketing, HR,
                                                                Purchase, Design, etc.)</td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="General shift - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="General shift - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift I - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift I - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift II - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift II - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift III - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift III - Contract"></td>
                                                        </tr>
                                                        <tr data-dept-label="Management / Supervisory staff">
                                                            <td>Management and Supervisory staff</td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="General shift - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="General shift - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift I - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift I - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift II - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift II - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift III - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift III - Contract"></td>
                                                        </tr>
                                                        <tr data-dept-label="Operators">
                                                            <td>Operators</td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="General shift - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="General shift - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift I - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift I - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift II - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift II - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift III - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift III - Contract"></td>
                                                        </tr>
                                                        <tr data-dept-label="Helpers">
                                                            <td>Helpers</td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="General shift - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="General shift - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift I - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift I - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift II - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift II - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift III - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift III - Contract"></td>
                                                        </tr>
                                                        <tr data-dept-label="Others (if any)">
                                                            <td>Others (if any)</td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="General shift - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="General shift - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift I - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift I - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift II - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift II - Contract"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift III - Permanent"></td>
                                                            <td><input type="number" min="0"
                                                                    class="form-control emp-input"
                                                                    data-label="Shift III - Contract"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <small style="font-size: 0.8rem; color: #6b7280;">You may provide
                                                approximate values. Detailed annexures can be attached separately if
                                                required.</small>
                                            <textarea name="employee_structure" id="employee_structure_field"
                                                style="display:none;"><?php echo isset($_POST['employee_structure']) ? htmlspecialchars($_POST['employee_structure'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Onsite installation work / operational yards / temporary
                                                sites?</label>
                                            <div>
                                                <label style="margin-right:15px;"><input type="radio"
                                                        name="onsite_installation" value="Yes" <?php echo (isset($_POST['onsite_installation']) && $_POST['onsite_installation'] === 'Yes') ? 'checked' : ''; ?>>
                                                    Yes</label>
                                                <label><input type="radio" name="onsite_installation" value="No" <?php echo (isset($_POST['onsite_installation']) && $_POST['onsite_installation'] === 'No') ? 'checked' : ''; ?>>
                                                    No</label>
                                            </div>
                                            <textarea name="onsite_details" class="form-control" rows="2"
                                                placeholder="If yes, please explain."><?php echo isset($_POST['onsite_details']) ? htmlspecialchars($_POST['onsite_details'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Any part time personnel?</label>
                                            <div>
                                                <label style="margin-right:15px;"><input type="radio"
                                                        name="part_time_personnel" value="Yes" <?php echo (isset($_POST['part_time_personnel']) && $_POST['part_time_personnel'] === 'Yes') ? 'checked' : ''; ?>>
                                                    Yes</label>
                                                <label><input type="radio" name="part_time_personnel" value="No" <?php echo (isset($_POST['part_time_personnel']) && $_POST['part_time_personnel'] === 'No') ? 'checked' : ''; ?>>
                                                    No</label>
                                            </div>
                                            <input type="text" name="part_time_count" class="form-control"
                                                placeholder="If yes, number of personnel"
                                                value="<?php echo isset($_POST['part_time_count']) ? htmlspecialchars($_POST['part_time_count'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Are there any employees excluded from audit scope?</label>
                                            <div>
                                                <label style="margin-right:15px;"><input type="radio"
                                                        name="employees_excluded" value="Yes" <?php echo (isset($_POST['employees_excluded']) && $_POST['employees_excluded'] === 'Yes') ? 'checked' : ''; ?>>
                                                    Yes</label>
                                                <label><input type="radio" name="employees_excluded" value="No" <?php echo (isset($_POST['employees_excluded']) && $_POST['employees_excluded'] === 'No') ? 'checked' : ''; ?>>
                                                    No</label>
                                            </div>
                                            <textarea name="employees_excluded_details" class="form-control" rows="2"
                                                placeholder="If yes, please explain."><?php echo isset($_POST['employees_excluded_details']) ? htmlspecialchars($_POST['employees_excluded_details'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="margin-top: 15px;">
                                        <h4 style="margin-bottom: 15px; font-weight: 700;">6. Describe your organisation
                                        </h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Organisation type</label>
                                            <div>
                                                <label style="margin-right:15px;"><input type="radio" name="org_type"
                                                        value="Manufacturer" <?php echo (isset($_POST['org_type']) && $_POST['org_type'] === 'Manufacturer') ? 'checked' : ''; ?>>
                                                    Manufacturer</label>
                                                <label><input type="radio" name="org_type" value="Service provider"
                                                        <?php echo (isset($_POST['org_type']) && $_POST['org_type'] === 'Service provider') ? 'checked' : ''; ?>>
                                                    Service provider</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Major customers</label>
                                            <textarea name="major_customers" class="form-control"
                                                rows="2"><?php echo isset($_POST['major_customers']) ? htmlspecialchars($_POST['major_customers'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Main products</label>
                                            <textarea name="main_products" class="form-control"
                                                rows="2"><?php echo isset($_POST['main_products']) ? htmlspecialchars($_POST['main_products'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Main raw materials used or processed</label>
                                            <textarea name="raw_materials" class="form-control"
                                                rows="2"><?php echo isset($_POST['raw_materials']) ? htmlspecialchars($_POST['raw_materials'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Operations involved in the service / manufacturing of your
                                                product</label>
                                            <textarea name="operations" class="form-control"
                                                rows="3"><?php echo isset($_POST['operations']) ? htmlspecialchars($_POST['operations'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Processes provided by external sources related to the product /
                                                service</label>
                                            <textarea name="external_processes" class="form-control"
                                                rows="3"><?php echo isset($_POST['external_processes']) ? htmlspecialchars($_POST['external_processes'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Who designs the products / services that you provide to your
                                                customers?</label>
                                            <select name="design_responsibility" class="form-control">
                                                <option value="">Select</option>
                                                <option value="We manufacture as per customer designs" <?php echo (isset($_POST['design_responsibility']) && $_POST['design_responsibility'] === 'We manufacture as per customer designs') ? 'selected' : ''; ?>>We manufacture the product as per
                                                    customers provided designs and specification.</option>
                                                <option value="We design our own products" <?php echo (isset($_POST['design_responsibility']) && $_POST['design_responsibility'] === 'We design our own products') ? 'selected' : ''; ?>>We design our own products.</option>
                                                <option value="Design at another site" <?php echo (isset($_POST['design_responsibility']) && $_POST['design_responsibility'] === 'Design at another site') ? 'selected' : ''; ?>>We design products at another site and produce
                                                    at the site to be certified.</option>
                                                <option value="Outsourced design" <?php echo (isset($_POST['design_responsibility']) && $_POST['design_responsibility'] === 'Outsourced design') ? 'selected' : ''; ?>>We outsource design activities to external providers /
                                                    subcontractors.</option>
                                                <option value="Distributor of other company products" <?php echo (isset($_POST['design_responsibility']) && $_POST['design_responsibility'] === 'Distributor of other company products') ? 'selected' : ''; ?>>We are a distributor of products
                                                    that are designed and manufactured by another company.</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="margin-top: 15px;">
                                        <h4 style="margin-bottom: 15px; font-weight: 700;">7. Other information</h4>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="other-certificates-wrapper">
                                            <div class="form-group" style="margin-bottom: 10px;">
                                                <label style="display:block; margin-bottom:6px;">Does the company have
                                                    any other valid certificate(s)?</label>
                                                <label style="margin-right:15px; font-weight: 500;"><input type="radio"
                                                        name="other_certificates_flag" value="Yes"> Yes</label>
                                                <label style="font-weight: 500;"><input type="radio"
                                                        name="other_certificates_flag" value="No" checked> No</label>
                                            </div>
                                            <div id="other-certificates-details" style="display:none; margin-top: 8px;">
                                                <p style="font-size: 0.9rem; color: #4b5563; margin-bottom: 6px;">If
                                                    yes, please list below:</p>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered other-cert-table"
                                                        id="other-certificates-table">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 40px;">Sr.</th>
                                                                <th>Certification body</th>
                                                                <th>Accreditation</th>
                                                                <th>Validity until</th>
                                                                <th>Certificate(s) number</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="text-center">1.</td>
                                                                <td><input type="text" class="form-control cert-body">
                                                                </td>
                                                                <td><input type="text" class="form-control cert-accred">
                                                                </td>
                                                                <td><input type="text" class="form-control cert-valid">
                                                                </td>
                                                                <td><input type="text" class="form-control cert-number">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-center">2.</td>
                                                                <td><input type="text" class="form-control cert-body">
                                                                </td>
                                                                <td><input type="text" class="form-control cert-accred">
                                                                </td>
                                                                <td><input type="text" class="form-control cert-valid">
                                                                </td>
                                                                <td><input type="text" class="form-control cert-number">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <button type="button" class="btn btn-default btn-sm add-row-btn"
                                                    id="add-certificate-row"><i class="fa fa-plus"></i> Add another
                                                    certificate</button>
                                            </div>
                                            <textarea name="other_certificates" id="other_certificates_field"
                                                style="display:none;"><?php echo isset($_POST['other_certificates']) ? htmlspecialchars($_POST['other_certificates'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date of system implementation</label>
                                            <input type="text" name="system_implementation_date" class="form-control"
                                                placeholder="dd-mm-yyyy"
                                                value="<?php echo isset($_POST['system_implementation_date']) ? htmlspecialchars($_POST['system_implementation_date'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Proposed month-year to conduct the audit</label>
                                            <input type="text" name="proposed_audit_month" class="form-control"
                                                placeholder="MM-YYYY"
                                                value="<?php echo isset($_POST['proposed_audit_month']) ? htmlspecialchars($_POST['proposed_audit_month'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Have any management system consultancy services been taken?</label>
                                            <div>
                                                <label style="margin-right:15px;"><input type="radio"
                                                        name="consultancy_services_taken" value="Yes" <?php echo (isset($_POST['consultancy_services_taken']) && $_POST['consultancy_services_taken'] === 'Yes') ? 'checked' : ''; ?>> Yes</label>
                                                <label><input type="radio" name="consultancy_services_taken" value="No"
                                                        <?php echo (isset($_POST['consultancy_services_taken']) && $_POST['consultancy_services_taken'] === 'No') ? 'checked' : ''; ?>> No</label>
                                            </div>
                                            <textarea name="consultancy_details" class="form-control" rows="2"
                                                placeholder="If yes, by whom and period of services."><?php echo isset($_POST['consultancy_details']) ? htmlspecialchars($_POST['consultancy_details'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="margin-top: 20px;">
                                        <div class="row">
                                            <div class="col-md-7">
                                                <div class="note-box">
                                                    <p style="font-weight: 600; margin-bottom: 6px;">Note –</p>
                                                    <ul
                                                        style="padding-left: 18px; margin: 0; font-size: 0.9rem; color: #4b5563;">
                                                        <li style="margin-bottom: 4px;">Initial audit will be conducted
                                                            in two stages. Stage 2 audit date(s) will be finalised on
                                                            assessment of Stage 1 audit.</li>
                                                        <li>If available, please attach process flow diagram,
                                                            organisation brochure and any customer specific
                                                            requirements.</li>
                                                    </ul>
                                                </div>
                                                <div class="optional-attachments">
                                                    <div class="optional-attachments-title"><i
                                                            class="fa fa-paperclip"></i> Optional supporting attachments
                                                    </div>
                                                    <p class="optional-attachments-hint">Upload only if available.
                                                        Allowed formats: PNG, JPG, JPEG. Each file can be up to 5
                                                        MB.</p>
                                                    <div class="attachment-upload-item">
                                                        <label for="process-flow-attachment">Process flow
                                                            diagram</label>
                                                        <input type="file" name="process_flow_attachment"
                                                            id="process-flow-attachment"
                                                            accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                                                    </div>
                                                    <div class="attachment-upload-item">
                                                        <label for="organisation-brochure-attachment">Organisation
                                                            brochure</label>
                                                        <input type="file" name="organisation_brochure_attachment"
                                                            id="organisation-brochure-attachment"
                                                            accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                                                    </div>
                                                    <div class="attachment-upload-item">
                                                        <label for="customer-requirements-attachment">Customer specific
                                                            requirements</label>
                                                        <input type="file" name="customer_requirements_attachment"
                                                            id="customer-requirements-attachment"
                                                            accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="signature-info-box">
                                                    <div class="form-group" style="margin-bottom: 10px;">
                                                        <label>Name</label>
                                                        <input type="text" name="sign_name" class="form-control"
                                                            required
                                                            value="<?php echo isset($_POST['sign_name']) ? htmlspecialchars($_POST['sign_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                                    </div>
                                                    <div class="form-group" style="margin-bottom: 10px;">
                                                        <label>Designation <span class="required-label">*</span></label>
                                                        <input type="text" name="sign_designation" class="form-control"
                                                            required
                                                            value="<?php echo isset($_POST['sign_designation']) ? htmlspecialchars($_POST['sign_designation'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                                    </div>
                                                    <div class="form-group" style="margin-bottom: 0;">
                                                        <label>Date <span class="required-label">*</span></label>
                                                        <input type="text" name="sign_date" class="form-control"
                                                            placeholder="dd-mm-yyyy" required
                                                            value="<?php echo isset($_POST['sign_date']) ? htmlspecialchars($_POST['sign_date'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="declaration-box">
                                            <div class="form-group" style="margin-bottom: 10px;">
                                                <label
                                                    style="display:flex; align-items:flex-start; gap:8px; font-weight: 500;">
                                                    <input type="checkbox" name="declaration_confirmed" value="1"
                                                        required style="margin-top: 4px;" <?php echo isset($_POST['declaration_confirmed']) ? 'checked' : ''; ?>>
                                                    <span style="font-size: 0.9rem; color: #111827;">I hereby declare
                                                        that the information provided above is true, correct and
                                                        complete and that we agree to abide by the applicable
                                                        certification requirements of A.I.T.F. Management Certification
                                                        Limited. <span class="required-label">*</span></span>
                                                </label>
                                            </div>
                                            <div class="signature-upload">
                                                <div class="signature-upload-input">
                                                    <label style="font-weight: 600; font-size: 0.9rem;">Sign with duly
                                                        stamped (upload image) <span
                                                            class="required-label">*</span></label>
                                                    <input type="file" name="sign_image"
                                                        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                                        class="form-control" id="sign-image-input" required>
                                                    <small style="font-size: 0.8rem; color: #6b7280;">Accepted formats:
                                                        JPG, PNG. Max size approx. 2 MB.</small>
                                                </div>
                                                <div class="signature-preview" id="sign-image-preview">
                                                    <span style="font-size: 0.8rem; color: #9ca3af;">Signature preview
                                                        will appear here.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12" id="submit-area">
                                        <div class="captcha-wrap">
                                            <?php if ($turnstileReady): ?>
                                                <div class="cf-turnstile"
                                                    data-sitekey="<?php echo htmlspecialchars($turnstileConfig['site_key'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-callback="applicationCaptchaSolved"
                                                    data-expired-callback="applicationCaptchaExpired"
                                                    data-error-callback="applicationCaptchaExpired"></div>
                                            <?php else: ?>
                                                <div class="captcha-help">Captcha widget will appear after Cloudflare Turnstile
                                                    keys are added in the `.env` file.</div>
                                            <?php endif; ?>
                                        </div>
                                        <div id="application-captcha-status" class="captcha-status">Please complete the
                                            captcha to enable the submit button.</div>
                                        <div id="required-warning" class="required-warning">Important fields are
                                            missing.</div>
                                        <button type="submit" class="submit-btn">Submit Application</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
            /* Ensure body and html take full width */
            html,
            body {
                width: 100%;
                overflow-x: hidden;
            }

            /* Force footer to be full width and override any parent styles */
            .footer-box {
                width: 100vw !important;
                max-width: 100vw !important;
                margin: 0 !important;
                padding: 60px 0 0 !important;
                position: relative;
                left: 50%;
                right: 50%;
                margin-left: -50vw !important;
                margin-right: -50vw !important;
            }

            /* Only apply full-width to bottom content sections, NOT hero or tab bar */
            .section {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Hero section must keep its padding below the fixed banner and navbar */
            .app-hero-header {
                padding: 180px 0 36px !important;
            }

            @media (max-width: 768px) {
                .app-hero-header {
                    padding: 145px 0 28px !important;
                }
            }

            @media (max-width: 576px) {
                .app-hero-header {
                    padding: 140px 0 24px !important;
                }
            }

            /* Tab panel sections need their own padding */
            #download-tab>.section {
                padding: 36px 0 50px !important;
            }

            #online-tab>.section {
                padding: 25px 0 40px !important;
            }

            /* Ensure footer container takes full width */
            .footer-box .container {
                max-width: 1200px !important;
                margin: 0 auto !important;
                padding: 0 15px !important;
                width: 100% !important;
            }

            @keyframes rotate {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }
        </style>

        <!-- Main Content Section - Full Width -->
        <div class="section margin-top_50" style="padding: 60px 0; width: 100%;">
            <div class="container" style="max-width: 100%; width: 100%; padding: 0 15px;">
                <div class="row justify-content-center" style="margin: 0 auto; max-width: 1200px; width: 100%;">
                    <div class="col-12">
                        <div class="form-card"
                            style="background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 15px; padding: 40px; width: 100%;">
                            <h3 class="text-center mb-4" style="color: #1a1a2e; font-size: 2rem; font-weight: 700;">ISO
                                Certification Application</h3>
                            <p class="text-center"
                                style="color: #4a5568; font-size: 1.1rem; line-height: 1.8; max-width: 800px; margin: 0 auto 30px;">
                                If you are completing the application offline, please download our Audit Application
                                Form. After filling it out, submit the form through our portal or email it to our team
                                for further assistance.
                            </p>


                            <div class="row mt-5">
                                <div class="col-md-4 mb-4">
                                    <a href="contactUS.php"
                                        style="text-decoration: none; display: block; height: 100%;">
                                        <div class="text-center p-4"
                                            style="background: #ffffff; border-radius: 12px; height: 100%; transition: all 0.3s ease; border: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                            <div
                                                style="width: 60px; height: 60px; background: linear-gradient(135deg, #6c63ff, #4a90e2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; transition: all 0.3s ease;">
                                                <i class="fa fa-phone" style="font-size: 1.5rem; color: white;"></i>
                                            </div>
                                            <h5 style="color: #1a1a2e; font-weight: 600; margin-bottom: 10px;">Need
                                                Help?</h5>
                                            <p style="color: #4a5568; font-size: 0.95rem; margin-bottom: 0;">Contact our
                                                team for any questions about the application process.</p>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-4 mb-4">
                                    <div class="text-center p-4"
                                        style="background: #ffffff; border-radius: 12px; height: 100%; border: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                        <a href="contactUS.php" style="text-decoration: none;">
                                            <div
                                                style="width: 60px; height: 60px; background: linear-gradient(135deg, #6c63ff, #4a90e2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                                <i class="fa fa-clock-o" style="font-size: 1.5rem; color: white;"></i>
                                            </div>
                                            <h5 style="color: #1a1a2e; font-weight: 600; margin-bottom: 10px;">
                                                Processing Time</h5>
                                            <p style="color: #4a5568; font-size: 0.95rem; margin-bottom: 0;">
                                                Applications are typically processed within 2-3 business days of
                                                submission.</p>
                                    </div></a>


                                </div>

                                <div class="col-md-4 mb-4">
                                    <div class="text-center p-4"
                                        style="background: #ffffff; border-radius: 12px; height: 100%; border: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                        <a href="contactUS.php" style="text-decoration: none;">
                                            <div
                                                style="width: 60px; height: 60px; background: linear-gradient(135deg, #6c63ff, #4a90e2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                                <i class="fa fa-envelope" style="font-size: 1.5rem; color: white;"></i>
                                            </div>
                                            <h5 style="color: #1a1a2e; font-weight: 600; margin-bottom: 10px;">Email
                                                Support</h5>
                                            <p style="color: #4a5568; font-size: 0.95rem; margin-bottom: 0;">Send your
                                                completed forms to: <a href="mailto:<?php echo $mailFromEmailHtml; ?>"
                                                    style="color: #2563eb; text-decoration: none; font-weight: 500;"><?php echo $mailFromEmailHtml; ?></a>
                                            </p>
                                    </div></a>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /online-tab panel -->

    <!-- Full Width Footer Wrapper -->
    <div style="width: 100vw; margin-left: calc(50% - 50vw); overflow-x: hidden;">
        <?php include './footerSource.php'; ?>
    </div>
    <div class="submit-overlay" id="submitOverlay" aria-live="polite" aria-busy="true">
        <div class="submit-modal" role="status">
            <div class="loader-ring" aria-hidden="true"></div>
            <h4>Submitting your application to AITF</h4>
            <p>Please wait while we verify the details, prepare your application PDF and send it to our certification
                team.</p>
            <div class="submit-progress" aria-hidden="true"><span></span><span></span><span></span></div>
        </div>
    </div>

    <script>
        (function () {
            var turnstileEnabled = <?php echo json_encode($turnstileReady); ?>;
            var form = document.getElementById('online-application-form-main');
            if (!form) return;

            var declarationCheckbox = form.querySelector('input[name="declaration_confirmed"]');
            var submitBtn = form.querySelector('.submit-btn');
            var requiredWarning = document.getElementById('required-warning');
            var requiredFields = form.querySelectorAll('[required]');
            var submitArea = document.getElementById('submit-area');
            var standardRows = document.getElementById('standard-rows');
            var addStandardRowBtn = document.getElementById('add-standard-row');
            var captchaStatus = document.getElementById('application-captcha-status');
            var captchaBox = form.querySelector('.cf-turnstile');
            var requiresVisibleCaptcha = Boolean(turnstileEnabled && captchaBox);
            var allowedSupportExtensions = ['png', 'jpg', 'jpeg'];
            var allowedSupportMimes = ['image/png', 'image/jpeg', ''];
            var maxSupportFileSize = 5 * 1024 * 1024;

            function getFileExtension(fileName) {
                var parts = String(fileName || '').split('.');
                return parts.length > 1 ? parts.pop().toLowerCase() : '';
            }

            function validateOptionalAttachmentInput(input, showAlert) {
                if (!input || !input.files || !input.files[0]) {
                    return true;
                }
                var file = input.files[0];
                var extension = getFileExtension(file.name);
                if (allowedSupportExtensions.indexOf(extension) === -1 || allowedSupportMimes.indexOf(file.type || '') === -1) {
                    if (showAlert) {
                        alert('Please upload only PNG, JPG or JPEG files for optional attachments.');
                    }
                    input.value = '';
                    return false;
                }
                if (file.size > maxSupportFileSize) {
                    if (showAlert) {
                        alert('Each optional attachment must be 5 MB or smaller.');
                    }
                    input.value = '';
                    return false;
                }
                return true;
            }

            var optionalAttachmentInputs = form.querySelectorAll('input[type="file"][name$="_attachment"]');
            for (var attachmentIndex = 0; attachmentIndex < optionalAttachmentInputs.length; attachmentIndex++) {
                optionalAttachmentInputs[attachmentIndex].addEventListener('change', function () {
                    validateOptionalAttachmentInput(this, true);
                });
            }

            function buildEmployeeStructureText() {
                var table = document.getElementById('employee-structure-table');
                if (!table) return '';
                var lines = [];
                var rows = table.querySelectorAll('tbody tr');
                for (var i = 0; i < rows.length; i++) {
                    var row = rows[i];
                    var dept = row.getAttribute('data-dept-label') || '';
                    var inputs = row.querySelectorAll('input.emp-input');
                    var parts = [];
                    for (var j = 0; j < inputs.length; j++) {
                        var input = inputs[j];
                        var val = (input.value || '').trim();
                        if (!val) continue;
                        var label = input.getAttribute('data-label') || '';
                        parts.push(label + ': ' + val);
                    }
                    if (dept && parts.length) {
                        lines.push(dept + ' - ' + parts.join('; '));
                    }
                }
                return lines.join('\n');
            }

            function buildOtherCertificatesText() {
                var flagYes = form.querySelector('input[name="other_certificates_flag"][value="Yes"]');
                var flagNo = form.querySelector('input[name="other_certificates_flag"][value="No"]');
                var hasCertificates = flagYes && flagYes.checked;
                var lines = [];
                if (hasCertificates) {
                    lines.push('Has other valid certificates: Yes');
                    var table = document.getElementById('other-certificates-table');
                    if (table) {
                        var rows = table.querySelectorAll('tbody tr');
                        var index = 1;
                        for (var i = 0; i < rows.length; i++) {
                            var row = rows[i];
                            var bodyInput = row.querySelector('.cert-body');
                            var accredInput = row.querySelector('.cert-accred');
                            var validInput = row.querySelector('.cert-valid');
                            var numberInput = row.querySelector('.cert-number');
                            var body = bodyInput && bodyInput.value ? bodyInput.value.trim() : '';
                            var accred = accredInput && accredInput.value ? accredInput.value.trim() : '';
                            var valid = validInput && validInput.value ? validInput.value.trim() : '';
                            var number = numberInput && numberInput.value ? numberInput.value.trim() : '';
                            if (!body && !accred && !valid && !number) {
                                continue;
                            }
                            var line = index + '. Certification body: ' + body + '; Accreditation: ' + accred + '; Validity until: ' + valid + '; Certificate number: ' + number;
                            lines.push(line);
                            index++;
                        }
                    }
                } else if (flagNo && flagNo.checked) {
                    lines.push('Has other valid certificates: No');
                }
                return lines.join('\n');
            }

            function populateEmployeeStructureFromText() {
                var empField = document.getElementById('employee_structure_field');
                var table = document.getElementById('employee-structure-table');
                if (!empField || !table) return;
                var raw = (empField.value || '').trim();
                if (!raw) return;
                var lines = raw.split(/\r?\n/);
                for (var i = 0; i < lines.length; i++) {
                    var line = lines[i].trim();
                    if (!line) continue;
                    var parts = line.split(' - ');
                    if (parts.length < 2) continue;
                    var dept = parts[0].trim();
                    var rest = parts[1].trim();
                    var row = table.querySelector('tbody tr[data-dept-label="' + dept.replace(/"/g, '\\"') + '"]');
                    if (!row) continue;
                    var items = rest.split(';');
                    for (var j = 0; j < items.length; j++) {
                        var item = items[j].trim();
                        if (!item) continue;
                        var pair = item.split(':');
                        if (pair.length < 2) continue;
                        var label = pair[0].trim();
                        var value = pair.slice(1).join(':').trim();
                        if (!value) continue;
                        var selector = 'input.emp-input[data-label="' + label.replace(/"/g, '\\"') + '"]';
                        var input = row.querySelector(selector);
                        if (input) {
                            input.value = value;
                        }
                    }
                }
            }

            function populateOtherCertificatesFromText() {
                var otherField = document.getElementById('other_certificates_field');
                var table = document.getElementById('other-certificates-table');
                var addRowBtn = document.getElementById('add-certificate-row');
                if (!otherField || !table) return;
                var raw = (otherField.value || '').trim();
                if (!raw) return;
                var lines = raw.split(/\r?\n/);
                var hasFlag = null;
                var certs = [];
                for (var i = 0; i < lines.length; i++) {
                    var line = lines[i].trim();
                    if (!line) continue;
                    if (line.indexOf('Has other valid certificates:') === 0) {
                        hasFlag = line.substring('Has other valid certificates:'.length).trim();
                        continue;
                    }
                    var withoutIndex = line.replace(/^\d+\.\s*/, '');
                    var parts = withoutIndex.split(';');
                    var rowObj = { body: '', accred: '', valid: '', number: '' };
                    for (var j = 0; j < parts.length; j++) {
                        var part = parts[j].trim();
                        if (!part) continue;
                        var pair = part.split(':');
                        if (pair.length < 2) continue;
                        var label = pair[0].trim();
                        var value = pair.slice(1).join(':').trim();
                        switch (label) {
                            case 'Certification body':
                                rowObj.body = value;
                                break;
                            case 'Accreditation':
                                rowObj.accred = value;
                                break;
                            case 'Validity until':
                                rowObj.valid = value;
                                break;
                            case 'Certificate number':
                            case 'Certificate(s) number':
                                rowObj.number = value;
                                break;
                            default:
                                break;
                        }
                    }
                    if (rowObj.body || rowObj.accred || rowObj.valid || rowObj.number) {
                        certs.push(rowObj);
                    }
                }

                if (hasFlag) {
                    var yesRadio = form.querySelector('input[name="other_certificates_flag"][value="Yes"]');
                    var noRadio = form.querySelector('input[name="other_certificates_flag"][value="No"]');
                    if (/^yes$/i.test(hasFlag) && yesRadio) {
                        yesRadio.checked = true;
                    } else if (noRadio) {
                        noRadio.checked = true;
                    }
                }

                toggleOtherCertificatesDetails();

                if (!certs.length) return;
                var tbody = table.querySelector('tbody');
                if (!tbody) return;
                var rows = tbody.querySelectorAll('tr');
                while (rows.length < certs.length && addRowBtn) {
                    addRowBtn.click();
                    rows = tbody.querySelectorAll('tr');
                }
                rows = tbody.querySelectorAll('tr');
                for (var k = 0; k < certs.length && k < rows.length; k++) {
                    var rRow = rows[k];
                    var c = certs[k];
                    var bodyInput = rRow.querySelector('.cert-body');
                    var accredInput = rRow.querySelector('.cert-accred');
                    var validInput = rRow.querySelector('.cert-valid');
                    var numberInput = rRow.querySelector('.cert-number');
                    if (bodyInput) bodyInput.value = c.body || '';
                    if (accredInput) accredInput.value = c.accred || '';
                    if (validInput) validInput.value = c.valid || '';
                    if (numberInput) numberInput.value = c.number || '';
                }
            }

            function toggleOtherCertificatesDetails() {
                var details = document.getElementById('other-certificates-details');
                if (!details) return;
                var yesRadio = form.querySelector('input[name="other_certificates_flag"][value="Yes"]');
                details.style.display = (yesRadio && yesRadio.checked) ? 'block' : 'none';
            }

            var flagRadios = form.querySelectorAll('input[name="other_certificates_flag"]');
            for (var r = 0; r < flagRadios.length; r++) {
                flagRadios[r].addEventListener('change', toggleOtherCertificatesDetails);
            }
            toggleOtherCertificatesDetails();

            // Populate tables from existing hidden text when form reloads (e.g. after validation error)
            populateEmployeeStructureFromText();
            populateOtherCertificatesFromText();

            var addRowBtn = document.getElementById('add-certificate-row');
            if (addRowBtn) {
                addRowBtn.addEventListener('click', function () {
                    var table = document.getElementById('other-certificates-table');
                    if (!table) return;
                    var tbody = table.querySelector('tbody');
                    if (!tbody) return;
                    var rows = tbody.querySelectorAll('tr');
                    var lastRow = rows[rows.length - 1];
                    var newRow = lastRow.cloneNode(true);
                    var inputs = newRow.querySelectorAll('input');
                    for (var i = 0; i < inputs.length; i++) {
                        inputs[i].value = '';
                    }
                    var srCell = newRow.querySelector('td');
                    if (srCell) {
                        srCell.textContent = (rows.length + 1) + '.';
                    }
                    tbody.appendChild(newRow);
                });
            }

            var signInput = document.getElementById('sign-image-input');
            var signPreview = document.getElementById('sign-image-preview');
            if (signInput && signPreview && window.FileReader) {
                signInput.addEventListener('change', function () {
                    var file = this.files && this.files[0];
                    if (!file) {
                        signPreview.innerHTML = '<span style="font-size: 0.8rem; color: #9ca3af;">Signature preview will appear here.</span>';
                        return;
                    }
                    var signExt = getFileExtension(file.name);
                    if (['jpg', 'jpeg', 'png'].indexOf(signExt) === -1 || ['image/jpeg', 'image/png', ''].indexOf(file.type || '') === -1) {
                        alert('Please upload a valid JPG or PNG image file for the signature.');
                        this.value = '';
                        signPreview.innerHTML = '<span style="font-size: 0.8rem; color: #9ca3af;">Signature preview will appear here.</span>';
                        return;
                    }
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        signPreview.innerHTML = '<img src="' + e.target.result + '" alt="Signature preview" style="max-width: 100%; max-height: 100%; display:block;">';
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Standards + Accreditation dynamic rows
            var isoOptions = ['ISO 9001', 'ISO 14001', 'ISO 45001', 'ISO 27001'];
            var accrOptions = ['NABCB', 'SELF'];

            function createStandardRow(stdVal, accVal) {
                var row = document.createElement('div');
                row.className = 'row align-items-end standard-row';
                row.style.marginBottom = '10px';
                var stdOptionsHtml = '<option value=\"\">Select</option>' + isoOptions.map(function (opt) {
                    var sel = (opt === stdVal) ? ' selected' : '';
                    return '<option value=\"' + opt + '\"' + sel + '>' + opt + '</option>';
                }).join('');
                var accOptionsHtml = '<option value=\"\">Select</option>' + accrOptions.map(function (opt) {
                    var sel = (opt === accVal) ? ' selected' : '';
                    return '<option value=\"' + opt + '\"' + sel + '>' + opt + '</option>';
                }).join('');
                row.innerHTML = '' +
                    '<div class=\"col-md-6\">' +
                    '<label>Standard applied for</label>' +
                    '<select name=\"standard_applied[]\" class=\"form-control\">' + stdOptionsHtml + '</select>' +
                    '</div>' +
                    '<div class=\"col-md-4\">' +
                    '<label>Accreditation requirement</label>' +
                    '<select name=\"accreditation_requirement[]\" class=\"form-control\">' + accOptionsHtml + '</select>' +
                    '</div>' +
                    '<div class=\"col-md-2\">' +
                    '<button type=\"button\" class=\"btn btn-default remove-standard-row\" style=\"margin-top:22px; width:100%;\"><i class=\"fa fa-trash\"></i> Remove</button>' +
                    '</div>';
                return row;
            }

            if (addStandardRowBtn && standardRows) {
                addStandardRowBtn.addEventListener('click', function () {
                    standardRows.appendChild(createStandardRow('', ''));
                });

                standardRows.addEventListener('click', function (e) {
                    if (e.target && (e.target.classList.contains('remove-standard-row') || e.target.closest('.remove-standard-row'))) {
                        var btn = e.target.classList.contains('remove-standard-row') ? e.target : e.target.closest('.remove-standard-row');
                        var row = btn.closest('.standard-row');
                        if (!row) return;
                        var totalRows = standardRows.querySelectorAll('.standard-row').length;
                        if (totalRows > 1) {
                            row.remove();
                        }
                    }
                });
            }

            function isApplicationCaptchaComplete() {
                if (!requiresVisibleCaptcha) {
                    return true;
                }
                var tokenField = form.querySelector('input[name="cf-turnstile-response"]');
                return Boolean(tokenField && tokenField.value.trim() !== '');
            }

            function validateRequiredFields() {
                var missing = false;
                for (var i = 0; i < requiredFields.length; i++) {
                    var f = requiredFields[i];
                    if (f.type === 'radio' || f.type === 'checkbox') {
                        var group = form.querySelectorAll('input[name="' + f.name + '"][required]');
                        var anyChecked = false;
                        for (var j = 0; j < group.length; j++) {
                            if (group[j].checked) {
                                anyChecked = true;
                                break;
                            }
                        }
                        if (!anyChecked) {
                            missing = true;
                            break;
                        }
                    } else {
                        var val = (f.value || '').trim();
                        if (!val) {
                            missing = true;
                            break;
                        }
                    }
                }
                if (submitBtn) {
                    var captchaMissing = !isApplicationCaptchaComplete();
                    submitBtn.disabled = missing || captchaMissing;
                    submitBtn.dataset.missing = missing ? '1' : '0';
                    submitBtn.dataset.captchaMissing = captchaMissing ? '1' : '0';
                }
                if (requiredWarning) {
                    requiredWarning.style.display = 'none';
                }
                if (captchaStatus) {
                    captchaStatus.classList.toggle('show', requiresVisibleCaptcha && !isApplicationCaptchaComplete());
                }
            }

            if (requiredFields && requiredFields.length) {
                for (var k = 0; k < requiredFields.length; k++) {
                    requiredFields[k].addEventListener('input', validateRequiredFields);
                    requiredFields[k].addEventListener('change', validateRequiredFields);
                }
                validateRequiredFields();
            }

            function toggleWarning(show) {
                if (!requiredWarning || !submitBtn) return;
                var missing = submitBtn.dataset.missing === '1';
                if (missing && show) {
                    requiredWarning.style.display = 'block';
                } else {
                    requiredWarning.style.display = 'none';
                }
            }

            window.applicationCaptchaSolved = function () {
                validateRequiredFields();
            };

            window.applicationCaptchaExpired = function () {
                validateRequiredFields();
            };

            if (submitBtn) {
                submitBtn.addEventListener('mouseenter', function () { toggleWarning(true); });
                submitBtn.addEventListener('mouseleave', function () { toggleWarning(false); });
            }

            if (requiresVisibleCaptcha) {
                window.setInterval(validateRequiredFields, 700);
            }

            form.addEventListener('submit', function (e) {
                if (declarationCheckbox && !declarationCheckbox.checked) {
                    alert('Please confirm the declaration before submitting the application.');
                    e.preventDefault();
                    return false;
                }

                validateRequiredFields();
                if (submitBtn && submitBtn.disabled) {
                    e.preventDefault();
                    if (submitBtn.dataset.missing === '1') {
                        alert('Please fill all required fields before submitting.');
                    } else if (submitBtn.dataset.captchaMissing === '1') {
                        alert('Please complete the captcha before submitting.');
                    }
                    return false;
                }

                for (var attachmentSubmitIndex = 0; attachmentSubmitIndex < optionalAttachmentInputs.length; attachmentSubmitIndex++) {
                    if (!validateOptionalAttachmentInput(optionalAttachmentInputs[attachmentSubmitIndex], true)) {
                        e.preventDefault();
                        return false;
                    }
                }

                var empField = document.getElementById('employee_structure_field');
                if (empField) {
                    empField.value = buildEmployeeStructureText();
                }

                var otherField = document.getElementById('other_certificates_field');
                if (otherField) {
                    otherField.value = buildOtherCertificatesText();
                }

                if (turnstileEnabled && !isApplicationCaptchaComplete()) {
                    validateRequiredFields();
                    e.preventDefault();
                    return false;
                }

                var overlay = document.getElementById('submitOverlay');
                if (overlay && submitBtn) {
                    overlay.classList.add('show');
                    submitBtn.dataset.originalText = submitBtn.textContent;
                    submitBtn.textContent = 'Submitting...';
                    submitBtn.disabled = true;
                }
            });

            window.addEventListener('pageshow', function () {
                var overlay = document.getElementById('submitOverlay');
                if (overlay) overlay.classList.remove('show');
                if (submitBtn) {
                    if (submitBtn.dataset.originalText) {
                        submitBtn.textContent = submitBtn.dataset.originalText;
                    }
                }
                validateRequiredFields();
            });
        })();
    </script>

    <!-- Tab Switching Logic -->
    <script>
        (function () {
            var tabBtns = document.querySelectorAll('.app-tab-btn');
            var tabPanels = document.querySelectorAll('.app-tab-panel');

            function activateTab(btn) {
                var targetTab = btn.getAttribute('data-tab');

                // Deactivate all buttons - reset inline styles
                tabBtns.forEach(function (b) {
                    b.classList.remove('active');
                    b.style.color = '#94a3b8';
                    b.style.borderBottom = '3px solid transparent';
                    b.style.fontWeight = '600';
                    var icon = b.querySelector('i');
                    if (icon) icon.style.color = '#94a3b8';
                });

                // Deactivate all panels
                tabPanels.forEach(function (p) {
                    p.classList.remove('active');
                    p.style.display = 'none';
                });

                // Activate selected button with inline styles
                btn.classList.add('active');
                btn.style.color = '#1e3a8a';
                btn.style.borderBottom = '3px solid #2563eb';
                btn.style.fontWeight = '700';
                var activeIcon = btn.querySelector('i');
                if (activeIcon) activeIcon.style.color = '#2563eb';

                // Activate selected panel
                var targetPanel = document.getElementById(targetTab);
                if (targetPanel) {
                    targetPanel.classList.add('active');
                    targetPanel.style.display = 'block';
                }

                // Smooth scroll based on which tab was selected
                if (targetTab === 'online-tab') {
                    var tabBar = document.getElementById('appTabBar');
                    if (tabBar) {
                        setTimeout(function () {
                            tabBar.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }, 50);
                    }
                } else if (targetTab === 'download-tab') {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }

            tabBtns.forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    activateTab(this);
                });
            });

            // If URL has hash #online-application-form, auto-switch to online tab
            if (window.location.hash === '#online-application-form') {
                var onlineBtn = document.querySelector('[data-tab="online-tab"]');
                if (onlineBtn) activateTab(onlineBtn);
            }
        })();
    </script>
</body>

</html>

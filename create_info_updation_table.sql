-- Create table for information updation forms
CREATE TABLE IF NOT EXISTS information_updation_forms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Organization Details
    org_name VARCHAR(255) NOT NULL,
    certificate_number VARCHAR(100) NOT NULL,
    contact_person VARCHAR(255) NOT NULL,
    contact_designation VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    
    -- Change Categories (Yes/No indicators)
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
    
    -- Signature Information
    sign_name VARCHAR(255) NOT NULL,
    sign_designation VARCHAR(255) NOT NULL,
    sign_date VARCHAR(50) NOT NULL,
    
    -- System Fields
    read_flag TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL,
    
    INDEX idx_created_at (created_at),
    INDEX idx_read_flag (read_flag),
    INDEX idx_org_name (org_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

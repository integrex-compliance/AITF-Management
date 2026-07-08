<?php

if (!function_exists('aitfEnsureColumn')) {
    function aitfEnsureColumn($db, $table, $column, $definition)
    {
        $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE :column_name");
        $stmt->execute(array(':column_name' => $column));
        if ($stmt->rowCount() === 0) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN $definition");
        }
    }
}

if (!function_exists('aitfEnsureIndex')) {
    function aitfEnsureIndex($db, $table, $indexName, $definition)
    {
        $stmt = $db->prepare("SHOW INDEX FROM `$table` WHERE Key_name = :index_name");
        $stmt->execute(array(':index_name' => $indexName));
        if ($stmt->rowCount() === 0) {
            $db->exec("ALTER TABLE `$table` ADD $definition");
        }
    }
}

if (!function_exists('aitfEnsureSupplierSchema')) {
    function aitfEnsureSupplierSchema($db)
    {
        if (!$db) {
            return;
        }

        aitfEnsureSupplierCustomerColumns($db);

        $db->exec("CREATE TABLE IF NOT EXISTS suppliers (
            supplier_id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_type ENUM('employee','contractor','goods') NOT NULL,
            supplier_name VARCHAR(255) NOT NULL,
            legal_name VARCHAR(255) NULL,
            pan_no VARCHAR(20) NULL,
            gst_registration_status ENUM('not_applicable','unregistered','registered') NOT NULL DEFAULT 'unregistered',
            gstin VARCHAR(20) NULL,
            contact_person VARCHAR(255) NULL,
            contact_no VARCHAR(50) NULL,
            email VARCHAR(255) NULL,
            address TEXT NULL,
            city VARCHAR(100) NULL,
            state VARCHAR(100) NULL,
            postcode VARCHAR(20) NULL,
            country VARCHAR(100) NOT NULL DEFAULT 'India',
            opening_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            opening_balance_date DATE NULL,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            notes TEXT NULL,
            legacy_vendor_name VARCHAR(255) NULL,
            created_by INT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_supplier_type (supplier_type),
            INDEX idx_supplier_name (supplier_name),
            INDEX idx_supplier_pan (pan_no),
            INDEX idx_supplier_gstin (gstin),
            INDEX idx_supplier_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $db->exec("CREATE TABLE IF NOT EXISTS supplier_employee_details (
            supplier_id INT PRIMARY KEY,
            employee_code VARCHAR(100) NULL,
            department VARCHAR(150) NULL,
            designation VARCHAR(150) NULL,
            joining_date DATE NULL,
            employment_type VARCHAR(100) NULL,
            CONSTRAINT fk_supplier_employee FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $db->exec("CREATE TABLE IF NOT EXISTS supplier_contractor_details (
            supplier_id INT PRIMARY KEY,
            service_category VARCHAR(150) NULL,
            contract_start_date DATE NULL,
            contract_end_date DATE NULL,
            msme_no VARCHAR(100) NULL,
            default_tds_section VARCHAR(50) NULL,
            default_tds_rate DECIMAL(5,2) NULL,
            CONSTRAINT fk_supplier_contractor FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $db->exec("CREATE TABLE IF NOT EXISTS supplier_goods_details (
            supplier_id INT PRIMARY KEY,
            product_category VARCHAR(150) NULL,
            place_of_supply VARCHAR(150) NULL,
            payment_terms VARCHAR(150) NULL,
            msme_no VARCHAR(100) NULL,
            CONSTRAINT fk_supplier_goods FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $db->exec("CREATE TABLE IF NOT EXISTS supplier_bank_accounts (
            bank_account_id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT NOT NULL,
            account_holder_name VARCHAR(255) NULL,
            bank_name VARCHAR(255) NULL,
            branch_name VARCHAR(255) NULL,
            account_no VARCHAR(100) NULL,
            ifsc_code VARCHAR(20) NULL,
            account_type VARCHAR(50) NULL,
            upi_id VARCHAR(100) NULL,
            is_primary TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_supplier_bank FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE,
            INDEX idx_bank_supplier (supplier_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $db->exec("CREATE TABLE IF NOT EXISTS supplier_attachments (
            attachment_id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT NOT NULL,
            document_type VARCHAR(100) NOT NULL,
            document_title VARCHAR(255) NULL,
            file_path VARCHAR(500) NOT NULL,
            original_file_name VARCHAR(255) NULL,
            mime_type VARCHAR(100) NULL,
            uploaded_by INT NULL,
            uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_supplier_attachment FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE,
            INDEX idx_attachment_supplier (supplier_id),
            INDEX idx_attachment_type (document_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $db->exec("CREATE TABLE IF NOT EXISTS bills (
            bill_id INT AUTO_INCREMENT PRIMARY KEY,
            vendor_name VARCHAR(255) NOT NULL,
            amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            due_date DATE NULL,
            paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            payment_status VARCHAR(50) NOT NULL DEFAULT 'Unpaid',
            description TEXT NULL,
            attached_file VARCHAR(500) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        aitfEnsureColumn($db, 'bills', 'supplier_id', 'supplier_id INT NULL AFTER bill_id');
        aitfEnsureColumn($db, 'bills', 'bill_number', 'bill_number VARCHAR(100) NULL AFTER supplier_id');
        aitfEnsureColumn($db, 'bills', 'bill_date', 'bill_date DATE NULL AFTER bill_number');
        aitfEnsureColumn($db, 'bills', 'legacy_vendor_name', 'legacy_vendor_name VARCHAR(255) NULL AFTER vendor_name');
        aitfEnsureIndex($db, 'bills', 'idx_bills_supplier', 'INDEX idx_bills_supplier (supplier_id)');
        aitfEnsureIndex($db, 'bills', 'idx_bills_bill_date', 'INDEX idx_bills_bill_date (bill_date)');

        $db->exec("CREATE TABLE IF NOT EXISTS bill_payments (
            payment_id INT AUTO_INCREMENT PRIMARY KEY,
            bill_id INT NOT NULL,
            supplier_id INT NOT NULL,
            payment_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            payment_date DATE NOT NULL,
            payment_mode ENUM('cash','cheque','bank_transfer','upi','card','other') NOT NULL DEFAULT 'bank_transfer',
            account_reference VARCHAR(255) NULL,
            utr_number VARCHAR(100) NULL,
            tds_deducted DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            notes TEXT NULL,
            payment_proof_file VARCHAR(500) NULL,
            created_by INT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_bill_payment_bill FOREIGN KEY (bill_id) REFERENCES bills(bill_id) ON DELETE CASCADE,
            CONSTRAINT fk_bill_payment_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
            INDEX idx_bill_payments_bill (bill_id),
            INDEX idx_bill_payments_supplier (supplier_id),
            INDEX idx_bill_payments_date (payment_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }
}

if (!function_exists('aitfEnsureSupplierCustomerColumns')) {
    function aitfEnsureSupplierCustomerColumns($db)
    {
        if (!$db) {
            return;
        }

        aitfEnsureColumn($db, 'customer_info', 'customer_category', "customer_category VARCHAR(30) NOT NULL DEFAULT 'client'");
        aitfEnsureColumn($db, 'customer_info', 'supplier_type', "supplier_type VARCHAR(30) NULL");
    }
}

if (!function_exists('aitfCustomerIsSupplier')) {
    function aitfCustomerIsSupplier($clientInfo)
    {
        return isset($clientInfo['customer_category']) && $clientInfo['customer_category'] === 'supplier';
    }
}

/**
 * Ensures the schema needed for the supplier verification + profile flow.
 * All additive / backward compatible (uses aitfEnsureColumn + CREATE IF NOT EXISTS).
 * Keyed on customer_info.id (the live supplier identity used by suppliers.php).
 */
if (!function_exists('aitfEnsureSupplierProfileSchema')) {
    function aitfEnsureSupplierProfileSchema($db)
    {
        if (!$db) {
            return;
        }

        aitfEnsureSupplierCustomerColumns($db);

        // pending until the type-specific verification form is submitted, then verified.
        aitfEnsureColumn($db, 'customer_info', 'verification_status', "verification_status VARCHAR(20) NOT NULL DEFAULT 'pending'");

        $db->exec("CREATE TABLE IF NOT EXISTS supplier_profile (
            customer_id INT PRIMARY KEY,
            legal_name VARCHAR(255) NULL,
            pan_no VARCHAR(20) NULL,
            gstin VARCHAR(20) NULL,
            gst_registration_status VARCHAR(20) NULL,
            contact_person VARCHAR(255) NULL,
            contact_no VARCHAR(50) NULL,
            email VARCHAR(255) NULL,
            address_line TEXT NULL,
            city VARCHAR(100) NULL,
            state VARCHAR(100) NULL,
            postcode VARCHAR(20) NULL,
            country VARCHAR(100) NULL DEFAULT 'India',
            opening_balance DECIMAL(12,2) NULL DEFAULT 0.00,
            opening_balance_date DATE NULL,
            bank_account_holder VARCHAR(255) NULL,
            bank_name VARCHAR(255) NULL,
            bank_branch VARCHAR(255) NULL,
            account_no VARCHAR(100) NULL,
            ifsc_code VARCHAR(20) NULL,
            upi_id VARCHAR(100) NULL,
            notes TEXT NULL,
            employee_code VARCHAR(100) NULL,
            department VARCHAR(150) NULL,
            designation VARCHAR(150) NULL,
            joining_date DATE NULL,
            employment_type VARCHAR(100) NULL,
            service_category VARCHAR(150) NULL,
            contract_start_date DATE NULL,
            contract_end_date DATE NULL,
            msme_no VARCHAR(100) NULL,
            tds_section VARCHAR(50) NULL,
            tds_rate DECIMAL(5,2) NULL,
            product_category VARCHAR(150) NULL,
            place_of_supply VARCHAR(150) NULL,
            payment_terms VARCHAR(150) NULL,
            msme_no_goods VARCHAR(100) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        // Link bills to a customer_info supplier without touching the legacy suppliers-table flow.
        $billsExists = $db->query("SHOW TABLES LIKE 'bills'")->rowCount();
        if ($billsExists) {
            aitfEnsureColumn($db, 'bills', 'customer_id', 'customer_id INT NULL AFTER bill_id');
            aitfEnsureIndex($db, 'bills', 'idx_bills_customer', 'INDEX idx_bills_customer (customer_id)');
        }
    }
}

/**
 * Single source of truth for the verification / profile fields, grouped by section.
 * Drives form rendering, saving, and the read-only profile view.
 */
if (!function_exists('aitfSupplierProfileFields')) {
    function aitfSupplierProfileFields()
    {
        $gstOptions = array(
            'registered' => 'Registered',
            'unregistered' => 'Unregistered',
            'not_applicable' => 'Not Applicable'
        );

        return array(
            'common' => array(
                'title' => 'Basic Details',
                'fields' => array(
                    array('key' => 'legal_name', 'label' => 'Legal / Full Name', 'type' => 'text'),
                    array('key' => 'contact_person', 'label' => 'Contact Person', 'type' => 'text'),
                    array('key' => 'contact_no', 'label' => 'Contact No.', 'type' => 'text'),
                    array('key' => 'email', 'label' => 'Email', 'type' => 'text'),
                    array('key' => 'address_line', 'label' => 'Address', 'type' => 'textarea'),
                    array('key' => 'city', 'label' => 'City', 'type' => 'text'),
                    array('key' => 'state', 'label' => 'State', 'type' => 'text'),
                    array('key' => 'postcode', 'label' => 'Postcode', 'type' => 'text'),
                    array('key' => 'country', 'label' => 'Country', 'type' => 'text'),
                )
            ),
            'tax_bank' => array(
                'title' => 'Tax & Bank',
                'fields' => array(
                    array('key' => 'pan_no', 'label' => 'PAN No.', 'type' => 'text'),
                    array('key' => 'gst_registration_status', 'label' => 'GST Status', 'type' => 'select', 'options' => $gstOptions),
                    array('key' => 'gstin', 'label' => 'GSTIN', 'type' => 'text'),
                    array('key' => 'opening_balance', 'label' => 'Opening Balance', 'type' => 'number'),
                    array('key' => 'opening_balance_date', 'label' => 'Opening Balance Date', 'type' => 'date'),
                    array('key' => 'bank_account_holder', 'label' => 'Account Holder', 'type' => 'text'),
                    array('key' => 'bank_name', 'label' => 'Bank Name', 'type' => 'text'),
                    array('key' => 'bank_branch', 'label' => 'Branch', 'type' => 'text'),
                    array('key' => 'account_no', 'label' => 'Account No.', 'type' => 'text'),
                    array('key' => 'ifsc_code', 'label' => 'IFSC Code', 'type' => 'text'),
                    array('key' => 'upi_id', 'label' => 'UPI ID', 'type' => 'text'),
                )
            ),
            'employee' => array(
                'title' => 'Employee Details',
                'fields' => array(
                    array('key' => 'employee_code', 'label' => 'Employee Code', 'type' => 'text'),
                    array('key' => 'department', 'label' => 'Department', 'type' => 'text'),
                    array('key' => 'designation', 'label' => 'Designation', 'type' => 'text'),
                    array('key' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date'),
                    array('key' => 'employment_type', 'label' => 'Employment Type', 'type' => 'select', 'options' => array(
                        'full_time' => 'Full Time', 'part_time' => 'Part Time', 'intern' => 'Intern', 'consultant' => 'Consultant'
                    )),
                )
            ),
            'contractor' => array(
                'title' => 'Contractor Details',
                'fields' => array(
                    array('key' => 'service_category', 'label' => 'Service Category', 'type' => 'text'),
                    array('key' => 'contract_start_date', 'label' => 'Contract Start', 'type' => 'date'),
                    array('key' => 'contract_end_date', 'label' => 'Contract End', 'type' => 'date'),
                    array('key' => 'msme_no', 'label' => 'MSME / Udyam No.', 'type' => 'text'),
                    array('key' => 'tds_section', 'label' => 'Default TDS Section', 'type' => 'text'),
                    array('key' => 'tds_rate', 'label' => 'Default TDS Rate (%)', 'type' => 'number'),
                )
            ),
            'goods' => array(
                'title' => 'Goods Supplier Details',
                'fields' => array(
                    array('key' => 'product_category', 'label' => 'Product Category', 'type' => 'text'),
                    array('key' => 'place_of_supply', 'label' => 'Place of Supply', 'type' => 'text'),
                    array('key' => 'payment_terms', 'label' => 'Payment Terms', 'type' => 'text'),
                    array('key' => 'msme_no_goods', 'label' => 'MSME / Udyam No.', 'type' => 'text'),
                )
            ),
        );
    }
}

/**
 * The section keys that apply to a given supplier type (common + tax/bank + type-specific).
 */
if (!function_exists('aitfSupplierSectionsForType')) {
    function aitfSupplierSectionsForType($type)
    {
        $sections = array('common', 'tax_bank');
        if (in_array($type, array('employee', 'contractor', 'goods'), true)) {
            $sections[] = $type;
        }
        return $sections;
    }
}

/**
 * Flat list of column keys valid for a supplier type.
 */
if (!function_exists('aitfSupplierFieldKeysForType')) {
    function aitfSupplierFieldKeysForType($type)
    {
        $all = aitfSupplierProfileFields();
        $keys = array();
        foreach (aitfSupplierSectionsForType($type) as $section) {
            if (!isset($all[$section])) {
                continue;
            }
            foreach ($all[$section]['fields'] as $field) {
                $keys[] = $field['key'];
            }
        }
        return $keys;
    }
}

/**
 * Fetch suppliers (customer_info + supplier_profile) filtered by verification status/type/search.
 */
if (!function_exists('aitfFetchSuppliers')) {
    function aitfFetchSuppliers($db, $status = null, $type = 'all', $search = '')
    {
        $where = array("ci.customer_category = 'supplier'", "(ci.is_deleted IS NULL OR ci.is_deleted = 'NO')");
        $params = array();

        if ($status === 'pending' || $status === 'verified') {
            $where[] = "COALESCE(ci.verification_status, 'pending') = :status";
            $params[':status'] = $status;
        }

        $validTypes = array('employee', 'contractor', 'goods');
        if (in_array($type, $validTypes, true)) {
            $where[] = "ci.supplier_type = :supplier_type";
            $params[':supplier_type'] = $type;
        }

        if ($search !== '') {
            $where[] = "(ci.customer_name LIKE :search OR ci.gstin_no LIKE :search OR ci.contact_person LIKE :search OR ci.contact LIKE :search OR ci.email_id LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql = "SELECT ci.id, ci.customer_name, ci.supplier_type, ci.gstin_no, ci.address,
                       ci.contact_person, ci.contact, ci.email_id, ci.scope, ci.comment,
                       COALESCE(ci.verification_status, 'pending') AS verification_status
                FROM customer_info ci
                WHERE " . implode(' AND ', $where) . "
                ORDER BY ci.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * Merged customer_info + supplier_profile row for one supplier.
 */
if (!function_exists('aitfGetSupplierProfile')) {
    function aitfGetSupplierProfile($db, $customerId)
    {
        $stmt = $db->prepare("SELECT * FROM customer_info WHERE id = :id AND customer_category = 'supplier'");
        $stmt->execute(array(':id' => $customerId));
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$customer) {
            return null;
        }

        $profileStmt = $db->prepare("SELECT * FROM supplier_profile WHERE customer_id = :id");
        $profileStmt->execute(array(':id' => $customerId));
        $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

        $merged = $customer;
        if ($profile) {
            foreach ($profile as $key => $value) {
                $merged[$key] = $value;
            }
        }
        // Sensible fallbacks from the base customer record when profile is blank.
        if (empty($merged['legal_name'])) {
            $merged['legal_name'] = $customer['customer_name'];
        }
        if (empty($merged['contact_person'])) {
            $merged['contact_person'] = isset($customer['contact_person']) ? $customer['contact_person'] : '';
        }
        if (empty($merged['contact_no'])) {
            $merged['contact_no'] = isset($customer['contact']) ? $customer['contact'] : '';
        }
        if (empty($merged['email'])) {
            $merged['email'] = isset($customer['email_id']) ? $customer['email_id'] : '';
        }
        if (empty($merged['gstin'])) {
            $merged['gstin'] = isset($customer['gstin_no']) ? $customer['gstin_no'] : '';
        }
        return $merged;
    }
}

/**
 * Upsert the supplier profile for the given type and mark the supplier verified.
 */
if (!function_exists('aitfSaveSupplierProfile')) {
    function aitfSaveSupplierProfile($db, $customerId, $type, $post)
    {
        $customerId = (int) $customerId;
        $keys = aitfSupplierFieldKeysForType($type);
        if (empty($keys)) {
            return false;
        }

        $columns = array('customer_id');
        $placeholders = array(':customer_id');
        $updates = array();
        $params = array(':customer_id' => $customerId);

        foreach ($keys as $key) {
            $value = isset($post[$key]) ? trim((string) $post[$key]) : '';
            if ($value === '') {
                $value = null;
            }
            $columns[] = $key;
            $placeholders[] = ':' . $key;
            $updates[] = "`$key` = VALUES(`$key`)";
            $params[':' . $key] = $value;
        }

        $sql = "INSERT INTO supplier_profile (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")
                ON DUPLICATE KEY UPDATE " . implode(', ', $updates);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $statusStmt = $db->prepare("UPDATE customer_info SET verification_status = 'verified' WHERE id = :id");
        $statusStmt->execute(array(':id' => $customerId));

        return true;
    }
}

if (!function_exists('aitfSupplierTypeLabel')) {
    function aitfSupplierTypeLabel($type)
    {
        $labels = array(
            'employee' => 'Employee',
            'contractor' => 'Contractor / Service Provider',
            'goods' => 'Goods Supplier'
        );
        return isset($labels[$type]) ? $labels[$type] : 'Supplier';
    }
}

if (!function_exists('aitfFetchActiveSuppliers')) {
    function aitfFetchActiveSuppliers($db)
    {
        $stmt = $db->query("SELECT supplier_id, supplier_name, supplier_type, pan_no, gstin, contact_no, email FROM suppliers WHERE status = 'active' ORDER BY supplier_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('aitfSaveUploadedFile')) {
    function aitfSaveUploadedFile($fieldName, $uploadDir, $allowedExtensions, $allowedMimeTypes, &$errorMessage)
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = 'Unable to upload the selected file.';
            return false;
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $originalName = $_FILES[$fieldName]['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $mimeType = mime_content_type($_FILES[$fieldName]['tmp_name']);

        if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
            $errorMessage = 'Only JPG, PNG, and PDF files are allowed.';
            return false;
        }

        $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $fileName = date('YmdHis') . '_' . mt_rand(1000, 9999) . '_' . $safeBaseName . '.' . $extension;
        $destination = rtrim($uploadDir, '/\\') . '/' . $fileName;

        if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $destination)) {
            $errorMessage = 'Unable to save the uploaded file.';
            return false;
        }

        return $destination;
    }
}

if (!function_exists('aitfBillPaymentTotals')) {
    function aitfBillPaymentTotals($db, $billId)
    {
        $stmt = $db->prepare("SELECT COALESCE(SUM(payment_amount), 0) AS paid_amount, COALESCE(SUM(tds_deducted), 0) AS tds_amount FROM bill_payments WHERE bill_id = :bill_id");
        $stmt->execute(array(':bill_id' => $billId));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return array(
            'paid_amount' => (float) ($row ? $row['paid_amount'] : 0),
            'tds_amount' => (float) ($row ? $row['tds_amount'] : 0)
        );
    }
}

if (!function_exists('aitfBillStatusFromAmounts')) {
    function aitfBillStatusFromAmounts($amount, $paidAmount, $tdsAmount)
    {
        $settled = (float) $paidAmount + (float) $tdsAmount;
        $amount = (float) $amount;
        if ($settled <= 0) {
            return 'Unpaid';
        }
        if ($settled >= $amount) {
            return 'Paid';
        }
        return 'Partially Paid';
    }
}

if (!function_exists('aitfSyncBillPaymentCache')) {
    function aitfSyncBillPaymentCache($db, $billId)
    {
        $billStmt = $db->prepare("SELECT amount FROM bills WHERE bill_id = :bill_id");
        $billStmt->execute(array(':bill_id' => $billId));
        $bill = $billStmt->fetch(PDO::FETCH_ASSOC);
        if (!$bill) {
            return;
        }
        $totals = aitfBillPaymentTotals($db, $billId);
        $status = aitfBillStatusFromAmounts($bill['amount'], $totals['paid_amount'], $totals['tds_amount']);
        $update = $db->prepare("UPDATE bills SET paid_amount = :paid_amount, payment_status = :payment_status WHERE bill_id = :bill_id");
        $update->execute(array(
            ':paid_amount' => $totals['paid_amount'],
            ':payment_status' => $status,
            ':bill_id' => $billId
        ));
    }
}

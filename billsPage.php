<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Bills";
    include 'header.php';
    include 'dbConnect.php';
    include_once __DIR__ . '/include/supplier_helpers.php';

    $message = '';
    $errorMessage = '';
    $uploadDir = 'uploads/bills/';
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'pdf');
    $allowedMimeTypes = array('image/jpeg', 'image/png', 'application/pdf');

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    function formatBillAmount($amount)
    {
        return number_format((float) $amount, 2);
    }

    function getBillUploadPath($fieldName, $existingPath, $uploadDir, $allowedExtensions, $allowedMimeTypes, &$errorMessage)
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return $existingPath;
        }

        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = 'Unable to upload the attached file. Please try again.';
            return false;
        }

        $originalName = $_FILES[$fieldName]['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $mimeType = mime_content_type($_FILES[$fieldName]['tmp_name']);

        if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
            $errorMessage = 'Attach File accepts only JPG, PNG, or PDF files.';
            return false;
        }

        $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $fileName = date('YmdHis') . '_' . mt_rand(1000, 9999) . '_' . $safeBaseName . '.' . $extension;
        $destination = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $destination)) {
            $errorMessage = 'Unable to save the attached file. Please check upload permissions.';
            return false;
        }

        return $destination;
    }

    if (!$db) {
        $errorMessage = 'Database connection is not available.';
        $bills = array();
        $summary = array('total_bills' => 0, 'total_amount' => 0, 'paid_amount' => 0, 'remaining_amount' => 0, 'overdue_amount' => 0, 'overdue_count' => 0);
        $vendorSummaries = array();
        $vendorLookup = array();
        $activeSuppliers = array();
    } else {
        try {
            // Migrations: ensure bills table exists
            $db->exec("CREATE TABLE IF NOT EXISTS bills (
                bill_id INT AUTO_INCREMENT PRIMARY KEY,
                vendor_name VARCHAR(255) NOT NULL,
                amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                payment_status VARCHAR(50) NOT NULL DEFAULT 'Unpaid',
                description TEXT NULL,
                attached_file VARCHAR(500) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

            // Safe ALTER columns
            $db->exec("ALTER TABLE bills MODIFY COLUMN payment_status VARCHAR(50) NOT NULL DEFAULT 'Unpaid'");

            $checkDueDate = $db->query("SHOW COLUMNS FROM bills LIKE 'due_date'");
            if ($checkDueDate->rowCount() === 0) {
                $db->exec("ALTER TABLE bills ADD COLUMN due_date DATE NULL AFTER amount");
            }

            $checkPaidAmount = $db->query("SHOW COLUMNS FROM bills LIKE 'paid_amount'");
            if ($checkPaidAmount->rowCount() === 0) {
                $db->exec("ALTER TABLE bills ADD COLUMN paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER due_date");
            }

            // Create vendors table
            $db->exec("CREATE TABLE IF NOT EXISTS vendors (
                vendor_name VARCHAR(255) PRIMARY KEY,
                phone VARCHAR(50) NULL,
                email VARCHAR(255) NULL,
                gst_no VARCHAR(50) NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

            aitfEnsureSupplierSchema($db);

            // Backward-compatible vendor migration: legacy vendor names become contractor suppliers.
            $legacyVendors = $db->query("SELECT vendor_name, phone, email, gst_no FROM vendors")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($legacyVendors as $legacyVendor) {
                $legacyCheck = $db->prepare("SELECT supplier_id FROM suppliers WHERE legacy_vendor_name = :legacy_vendor_name LIMIT 1");
                $legacyCheck->execute(array(':legacy_vendor_name' => $legacyVendor['vendor_name']));
                $legacySupplierId = $legacyCheck->fetchColumn();
                if (!$legacySupplierId) {
                    $legacyInsert = $db->prepare("INSERT INTO suppliers (supplier_type, supplier_name, gst_registration_status, gstin, contact_no, email, status, legacy_vendor_name)
                        VALUES ('contractor', :supplier_name, :gst_registration_status, :gstin, :contact_no, :email, 'active', :legacy_vendor_name)");
                    $legacyInsert->execute(array(
                        ':supplier_name' => $legacyVendor['vendor_name'],
                        ':gst_registration_status' => !empty($legacyVendor['gst_no']) ? 'registered' : 'unregistered',
                        ':gstin' => $legacyVendor['gst_no'],
                        ':contact_no' => $legacyVendor['phone'],
                        ':email' => $legacyVendor['email'],
                        ':legacy_vendor_name' => $legacyVendor['vendor_name']
                    ));
                    $legacySupplierId = $db->lastInsertId();
                }
                $mapStmt = $db->prepare("UPDATE bills SET supplier_id = :supplier_id, legacy_vendor_name = COALESCE(NULLIF(legacy_vendor_name, ''), vendor_name), bill_date = COALESCE(bill_date, DATE(created_at)) WHERE supplier_id IS NULL AND vendor_name = :vendor_name");
                $mapStmt->execute(array(':supplier_id' => $legacySupplierId, ':vendor_name' => $legacyVendor['vendor_name']));
            }

            $activeSuppliers = aitfFetchActiveSuppliers($db);

            // Dynamic Overdue / days diff calculator helper
            function getBillStatusInfo($bill)
            {
                $today = new DateTime(date('Y-m-d'));

                $amount = (float) $bill['amount'];
                $paidAmount = isset($bill['derived_paid_amount']) ? (float) $bill['derived_paid_amount'] : (float) $bill['paid_amount'];
                $tdsAmount = isset($bill['tds_amount']) ? (float) $bill['tds_amount'] : 0;
                $settledAmount = $paidAmount + $tdsAmount;
                $dueDateStr = $bill['due_date'];

                if ($settledAmount >= $amount) {
                    return array(
                        'status' => 'Paid',
                        'class' => 'bill-status-paid',
                        'label' => 'Paid',
                        'subtext' => 'Fully Paid'
                    );
                }

                $isOverdue = false;
                $daysDiff = 0;
                if (!empty($dueDateStr)) {
                    $dueDate = new DateTime($dueDateStr);
                    if ($dueDate < $today) {
                        $isOverdue = true;
                        $interval = $today->diff($dueDate);
                        $daysDiff = $interval->days;
                    } else {
                        $interval = $today->diff($dueDate);
                        $daysDiff = $interval->days;
                    }
                }

                if ($isOverdue) {
                    return array(
                        'status' => 'Overdue',
                        'class' => 'bill-status-overdue',
                        'label' => 'Overdue',
                        'subtext' => 'Overdue by ' . $daysDiff . ' ' . ($daysDiff === 1 ? 'day' : 'days')
                    );
                }

                if ($settledAmount > 0) {
                    return array(
                        'status' => 'Partially Paid',
                        'class' => 'bill-status-partially-paid',
                        'label' => 'Partially Paid',
                        'subtext' => 'Rs. ' . number_format($amount - $settledAmount, 2) . ' left'
                    );
                }

                return array(
                    'status' => 'Unpaid',
                    'class' => 'bill-status-unpaid',
                    'label' => 'Unpaid',
                    'subtext' => !empty($dueDateStr) ? 'Due in ' . $daysDiff . ' ' . ($daysDiff === 1 ? 'day' : 'days') : 'No due date'
                );
            }

            // POST handler
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bill_action'])) {
                $action = $_POST['bill_action'];

                if ($action === 'save_vendor') {
                    $vName = trim(isset($_POST['vendor_name']) ? $_POST['vendor_name'] : '');
                    $vPhone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
                    $vEmail = trim(isset($_POST['email']) ? $_POST['email'] : '');
                    $vGst = trim(isset($_POST['gst_no']) ? $_POST['gst_no'] : '');

                    if ($vName !== '') {
                        $vendorStmt = $db->prepare('INSERT INTO vendors (vendor_name, phone, email, gst_no) VALUES (:vendor_name, :phone, :email, :gst_no) ON DUPLICATE KEY UPDATE phone = :phone2, email = :email2, gst_no = :gst_no2');
                        $vendorStmt->execute(array(
                            ':vendor_name' => $vName,
                            ':phone' => $vPhone,
                            ':email' => $vEmail,
                            ':gst_no' => $vGst,
                            ':phone2' => $vPhone,
                            ':email2' => $vEmail,
                            ':gst_no2' => $vGst
                        ));
                        $message = 'Vendor details updated successfully.';
                    }
                } else if ($action === 'record_payment') {
                    $billId = isset($_POST['bill_id']) ? (int) $_POST['bill_id'] : 0;
                    $paymentAmount = isset($_POST['payment_amount']) ? (float) $_POST['payment_amount'] : 0;
                    $tdsDeducted = isset($_POST['tds_deducted']) ? (float) $_POST['tds_deducted'] : 0;
                    $paymentDate = isset($_POST['payment_date']) && $_POST['payment_date'] !== '' ? $_POST['payment_date'] : date('Y-m-d');
                    $paymentMode = isset($_POST['payment_mode']) ? $_POST['payment_mode'] : 'bank_transfer';
                    $accountReference = trim(isset($_POST['account_reference']) ? $_POST['account_reference'] : '');
                    $utrNumber = trim(isset($_POST['utr_number']) ? $_POST['utr_number'] : '');
                    $paymentNotes = trim(isset($_POST['payment_notes']) ? $_POST['payment_notes'] : '');

                    $billStmt = $db->prepare("SELECT bill_id, supplier_id, amount FROM bills WHERE bill_id = :bill_id");
                    $billStmt->execute(array(':bill_id' => $billId));
                    $billForPayment = $billStmt->fetch(PDO::FETCH_ASSOC);
                    if (!$billForPayment || empty($billForPayment['supplier_id'])) {
                        $errorMessage = 'Please migrate/select a registered supplier before recording payment.';
                    } elseif ($paymentAmount < 0 || $tdsDeducted < 0 || ($paymentAmount + $tdsDeducted) <= 0) {
                        $errorMessage = 'Please enter a valid payment or TDS amount.';
                    } else {
                        $totals = aitfBillPaymentTotals($db, $billId);
                        $settledAfter = $totals['paid_amount'] + $totals['tds_amount'] + $paymentAmount + $tdsDeducted;
                        if ($settledAfter > ((float) $billForPayment['amount'] + 0.01)) {
                            $errorMessage = 'Payment plus TDS cannot exceed the bill amount.';
                        } else {
                            $paymentProof = getBillUploadPath('payment_proof_file', '', $uploadDir, $allowedExtensions, $allowedMimeTypes, $errorMessage);
                            if ($paymentProof !== false) {
                                $payInsert = $db->prepare("INSERT INTO bill_payments (bill_id, supplier_id, payment_amount, payment_date, payment_mode, account_reference, utr_number, tds_deducted, notes, payment_proof_file, created_by)
                                    VALUES (:bill_id, :supplier_id, :payment_amount, :payment_date, :payment_mode, :account_reference, :utr_number, :tds_deducted, :notes, :payment_proof_file, :created_by)");
                                $payInsert->execute(array(
                                    ':bill_id' => $billId,
                                    ':supplier_id' => $billForPayment['supplier_id'],
                                    ':payment_amount' => $paymentAmount,
                                    ':payment_date' => $paymentDate,
                                    ':payment_mode' => $paymentMode,
                                    ':account_reference' => $accountReference,
                                    ':utr_number' => $utrNumber,
                                    ':tds_deducted' => $tdsDeducted,
                                    ':notes' => $paymentNotes,
                                    ':payment_proof_file' => $paymentProof,
                                    ':created_by' => isset($_SESSION['id']) ? (int) $_SESSION['id'] : null
                                ));
                                aitfSyncBillPaymentCache($db, $billId);
                                $message = 'Payment recorded successfully.';
                            }
                        }
                    }
                } else {
                    $supplierId = isset($_POST['supplier_id']) ? (int) $_POST['supplier_id'] : 0;
                    $vendorName = '';
                    if ($supplierId > 0) {
                        $supplierStmt = $db->prepare("SELECT supplier_name FROM suppliers WHERE supplier_id = :supplier_id AND status = 'active'");
                        $supplierStmt->execute(array(':supplier_id' => $supplierId));
                        $vendorName = (string) $supplierStmt->fetchColumn();
                    }
                    $billNumber = trim(isset($_POST['bill_number']) ? $_POST['bill_number'] : '');
                    $billDate = isset($_POST['bill_date']) && !empty($_POST['bill_date']) ? $_POST['bill_date'] : date('Y-m-d');
                    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
                    $dueDate = isset($_POST['due_date']) && !empty($_POST['due_date']) ? $_POST['due_date'] : null;
                    $paidAmount = 0.00;
                    $paymentStatus = 'Unpaid';
                    $description = trim(isset($_POST['description']) ? $_POST['description'] : '');

                    if ($action === 'delete') {
                        $billId = (int) $_POST['bill_id'];
                        $fileStatement = $db->prepare('SELECT attached_file FROM bills WHERE bill_id = :bill_id');
                        $fileStatement->execute(array(':bill_id' => $billId));
                        $fileRow = $fileStatement->fetch(PDO::FETCH_ASSOC);

                        $deleteStatement = $db->prepare('DELETE FROM bills WHERE bill_id = :bill_id');
                        $deleteStatement->execute(array(':bill_id' => $billId));

                        if ($fileRow && !empty($fileRow['attached_file']) && file_exists($fileRow['attached_file'])) {
                            unlink($fileRow['attached_file']);
                        }

                        $message = 'Bill deleted successfully.';
                    } else if ($vendorName === '' || $supplierId <= 0 || $amount < 0) {
                        $errorMessage = 'Please select a registered supplier and enter a valid bill amount.';
                    } else if ($action === 'add') {
                        $attachedFile = getBillUploadPath('attached_file', '', $uploadDir, $allowedExtensions, $allowedMimeTypes, $errorMessage);
                        if ($attachedFile !== false) {
                            $insertStatement = $db->prepare('INSERT INTO bills (supplier_id, bill_number, bill_date, vendor_name, legacy_vendor_name, amount, due_date, paid_amount, payment_status, description, attached_file) VALUES (:supplier_id, :bill_number, :bill_date, :vendor_name, :legacy_vendor_name, :amount, :due_date, :paid_amount, :payment_status, :description, :attached_file)');
                            $insertStatement->execute(array(
                                ':supplier_id' => $supplierId,
                                ':bill_number' => $billNumber,
                                ':bill_date' => $billDate,
                                ':vendor_name' => $vendorName,
                                ':legacy_vendor_name' => $vendorName,
                                ':amount' => $amount,
                                ':due_date' => $dueDate,
                                ':paid_amount' => $paidAmount,
                                ':payment_status' => $paymentStatus,
                                ':description' => $description,
                                ':attached_file' => $attachedFile
                            ));

                            $message = 'Bill added successfully.';
                        }
                    } else if ($action === 'edit') {
                        $billId = (int) $_POST['bill_id'];
                        $currentStatement = $db->prepare('SELECT attached_file FROM bills WHERE bill_id = :bill_id');
                        $currentStatement->execute(array(':bill_id' => $billId));
                        $currentBill = $currentStatement->fetch(PDO::FETCH_ASSOC);
                        $attachedFile = getBillUploadPath('attached_file', $currentBill ? $currentBill['attached_file'] : '', $uploadDir, $allowedExtensions, $allowedMimeTypes, $errorMessage);

                        if ($attachedFile !== false) {
                            if ($currentBill && $attachedFile !== $currentBill['attached_file'] && !empty($currentBill['attached_file']) && file_exists($currentBill['attached_file'])) {
                                unlink($currentBill['attached_file']);
                            }

                            $updateStatement = $db->prepare('UPDATE bills SET supplier_id = :supplier_id, bill_number = :bill_number, bill_date = :bill_date, vendor_name = :vendor_name, amount = :amount, due_date = :due_date, description = :description, attached_file = :attached_file WHERE bill_id = :bill_id');
                            $updateStatement->execute(array(
                                ':supplier_id' => $supplierId,
                                ':bill_number' => $billNumber,
                                ':bill_date' => $billDate,
                                ':vendor_name' => $vendorName,
                                ':amount' => $amount,
                                ':due_date' => $dueDate,
                                ':description' => $description,
                                ':attached_file' => $attachedFile,
                                ':bill_id' => $billId
                            ));
                            aitfSyncBillPaymentCache($db, $billId);

                            $message = 'Bill updated successfully.';
                        }
                    }
                }
            }

            // Fetch all bills with supplier and derived payment totals
            $statement = $db->query("
                SELECT b.*,
                    COALESCE(s.supplier_name, b.vendor_name) AS supplier_display_name,
                    s.supplier_type,
                    COALESCE(pay.paid_amount, b.paid_amount, 0) AS derived_paid_amount,
                    COALESCE(pay.tds_amount, 0) AS tds_amount
                FROM bills b
                LEFT JOIN suppliers s ON s.supplier_id = b.supplier_id
                LEFT JOIN (
                    SELECT bill_id, SUM(payment_amount) AS paid_amount, SUM(tds_deducted) AS tds_amount
                    FROM bill_payments
                    GROUP BY bill_id
                ) pay ON pay.bill_id = b.bill_id
                ORDER BY b.created_at DESC, b.bill_id DESC
            ");
            $bills = $statement->fetchAll(PDO::FETCH_ASSOC);

            // Fetch summaries for cards from derived payment totals.
            $summaryStatement = $db->query("
                SELECT
                    COUNT(*) AS total_bills,
                    COALESCE(SUM(x.amount), 0) AS total_amount,
                    COALESCE(SUM(x.paid_amount), 0) AS paid_amount,
                    COALESCE(SUM(x.amount - x.paid_amount - x.tds_amount), 0) AS remaining_amount,
                    COALESCE(SUM(CASE WHEN (x.amount - x.paid_amount - x.tds_amount) > 0 AND x.due_date < CURDATE() THEN x.amount - x.paid_amount - x.tds_amount ELSE 0 END), 0) AS overdue_amount,
                    COUNT(CASE WHEN (x.amount - x.paid_amount - x.tds_amount) > 0 AND x.due_date < CURDATE() THEN 1 END) AS overdue_count
                FROM (
                    SELECT b.bill_id, b.amount, b.due_date, COALESCE(pay.paid_amount, b.paid_amount, 0) AS paid_amount, COALESCE(pay.tds_amount, 0) AS tds_amount
                    FROM bills b
                    LEFT JOIN (
                        SELECT bill_id, SUM(payment_amount) AS paid_amount, SUM(tds_deducted) AS tds_amount
                        FROM bill_payments
                        GROUP BY bill_id
                    ) pay ON pay.bill_id = b.bill_id
                ) x
            ");
            $summary = $summaryStatement->fetch(PDO::FETCH_ASSOC);

            // Fetch vendor profiles & summaries for the lookup popup
            $vendorSummaryStmt = $db->query("
                SELECT 
                    v.vendor_name, 
                    COALESCE(v.phone, '') AS phone, 
                    COALESCE(v.email, '') AS email, 
                    COALESCE(v.gst_no, '') AS gst_no,
                    COUNT(b.bill_id) AS total_bills,
                    COALESCE(SUM(b.amount - b.paid_amount), 0) AS total_outstanding
                FROM vendors v
                LEFT JOIN bills b ON v.vendor_name = b.vendor_name
                GROUP BY v.vendor_name
            ");
            $vendorSummaries = $vendorSummaryStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $vendorLookup = array();
            foreach ($vendorSummaries as $vSum) {
                $vendorLookup[$vSum['vendor_name']] = array(
                    'phone' => $vSum['phone'],
                    'email' => $vSum['email'],
                    'gst_no' => $vSum['gst_no'],
                    'total_bills' => (int) $vSum['total_bills'],
                    'total_outstanding' => (float) $vSum['total_outstanding']
                );
            }

        } catch (Exception $e) {
            error_log($e->getMessage());
            $errorMessage = 'Database migration or query error: ' . $e->getMessage();
            $bills = array();
            $summary = array('total_bills' => 0, 'total_amount' => 0, 'paid_amount' => 0, 'remaining_amount' => 0, 'overdue_amount' => 0, 'overdue_count' => 0);
            $vendorSummaries = array();
            $vendorLookup = array();
            $activeSuppliers = array();
        }
    }
    ?>

    <style>
        /* Import Outfit font for premium modern typography */
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');

        .bills-page {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: calc(100vh - 70px);
            padding: 30px 0 50px;
            background: radial-gradient(circle at 10% 20%, rgba(244, 247, 252, 1) 0%, rgba(230, 237, 248, 1) 90%);
            color: #1e293b;
        }

        .bills-page .container {
            width: 95%;
            max-width: 1400px;
        }

        /* Premium Header Styling */
        .bills-header {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            min-height: 140px;
            margin-bottom: 30px;
            padding: 30px 40px;
            overflow: hidden;
            color: #fff;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #3b82f6 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
            isolation: isolate;
        }

        .bills-header:before {
            content: "";
            position: absolute;
            right: -50px;
            top: -50px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, rgba(59, 130, 246, 0) 70%);
            z-index: -1;
        }

        .bills-header:after {
            content: "";
            position: absolute;
            left: 20%;
            bottom: -80px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0) 70%);
            z-index: -1;
        }

        .bills-header-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .bills-hero-icon {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 26px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(12px);
        }

        .bills-title {
            margin: 0;
            color: #fff;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .bills-subtitle {
            margin: 6px 0 0;
            color: #94a3b8;
            font-size: 15px;
            font-weight: 400;
        }

        /* Modern Custom Buttons */
        .bills-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 14px;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .bills-btn-primary {
            color: #fff;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
        }

        .bills-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(59, 130, 246, 0.35);
            filter: brightness(1.05);
        }

        .bills-btn-soft {
            color: #1e293b;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }

        .bills-btn-soft:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }

        .bills-btn-danger {
            color: #fff;
            background: #ef4444;
            box-shadow: 0 8px 16px rgba(239, 68, 68, 0.15);
        }

        .bills-btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* Modern Dashboard Layout Cards */
        .bill-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .bill-summary-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.03);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .bill-summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.07);
        }

        .bill-summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .bill-summary-blue {
            color: #3b82f6;
            background: #eff6ff;
        }

        .bill-summary-green {
            color: #10b981;
            background: #ecfdf5;
        }

        .bill-summary-indigo {
            color: #6366f1;
            background: #e0e7ff;
        }

        .bill-summary-red {
            color: #ef4444;
            background: #fef2f2;
        }

        .bill-summary-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .bill-summary-value {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin: 4px 0 2px;
            letter-spacing: -0.5px;
        }

        .bill-summary-meta {
            font-size: 12px;
            color: #64748b;
        }

        /* Container Card Styling */
        .bills-card {
            position: relative;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .bills-card-body {
            padding: 26px;
        }

        /* Modern Bill Filling Forms */
        .bill-form-panel {
            display: none;
            border: 1px solid rgba(59, 130, 246, 0.3);
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .bill-form-panel:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #6366f1, #10b981);
        }

        .bill-form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .bill-form-group {
            display: flex;
            flex-direction: column;
        }

        .bill-form-group label {
            display: block;
            margin-bottom: 8px;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .bill-form-group input,
        .bill-form-group select,
        .bill-form-group textarea,
        .bills-filter-panel input,
        .bills-filter-panel select {
            width: 100%;
            height: 46px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 10px 16px;
            color: #0f172a;
            background: #fff;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .bill-form-group textarea {
            height: auto;
            min-height: 100px;
            resize: vertical;
        }

        .bill-form-group input:focus,
        .bill-form-group select:focus,
        .bill-form-group textarea:focus,
        .bills-filter-panel input:focus,
        .bills-filter-panel select:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            background: #fff;
        }

        .bill-form-full {
            grid-column: 1 / -1;
        }

        .bill-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        /* Filter Panel */
        .bills-filter-panel {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .bills-filter-panel .search-wrap {
            position: relative;
        }

        .bills-filter-panel .search-wrap i {
            position: absolute;
            top: 50%;
            left: 16px;
            color: #64748b;
            transform: translateY(-50%);
            font-size: 16px;
        }

        .bills-filter-panel input {
            padding-left: 46px;
        }

        /* Alert notifications */
        .bills-alert {
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .bills-alert-success {
            color: #065f46;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
        }

        .bills-alert-error {
            color: #991b1b;
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        /* Modern Premium Table Styling */
        .table-responsive {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        }

        .bills-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .bills-table thead th {
            padding: 16px 20px;
            color: #475569;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            white-space: nowrap;
        }

        .bills-table tbody td {
            padding: 16px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #334155;
            background: #fff;
            transition: background-color 0.2s ease;
        }

        .bills-table tbody tr.bill-row:hover td {
            background-color: #f8fafc;
        }

        /* Vendor Details inside row */
        .bill-vendor-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .bill-vendor-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            color: #3b82f6;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            flex-shrink: 0;
        }

        .vendor-name-link {
            color: #0f172a;
            font-weight: 600;
            transition: color 0.15s ease;
        }

        .bill-vendor-cell:hover .vendor-name-link {
            color: #3b82f6;
            text-decoration: underline;
        }

        /* Amount Detail Stack in Table */
        .amount-detail-stack {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .amount-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            font-size: 13px;
        }

        .amount-lbl {
            color: #64748b;
            font-weight: 500;
        }

        .amount-val {
            font-weight: 600;
            color: #1e293b;
        }

        .amount-val.total-amt {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .amount-val.paid-amt {
            color: #10b981;
        }

        .remaining-row {
            border-top: 1px dashed #e2e8f0;
            padding-top: 4px;
            margin-top: 2px;
        }

        .remaining-row.has-remaining .amount-val {
            color: #3b82f6;
            font-weight: 700;
        }

        /* Dates Stack */
        .date-detail-stack {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 13px;
        }

        .date-detail-row {
            display: flex;
            gap: 6px;
        }

        .date-lbl {
            color: #64748b;
            font-weight: 500;
        }

        .date-val {
            color: #334155;
            font-weight: 600;
        }

        .due-date-highlight {
            padding: 1px 6px;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .due-date-highlight.due-overdue {
            color: #ef4444;
            background: #fef2f2;
            border-color: #fecaca;
            font-weight: 700;
        }

        /* Status Colors & Badges */
        .bill-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
            border: 1px solid transparent;
        }

        .bill-status:before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .bill-status-paid {
            color: #16a34a;
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .bill-status-unpaid {
            color: #ea580c;
            background: #fff7ed;
            border-color: #ffedd5;
        }

        .bill-status-overdue {
            color: #dc2626;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .bill-status-partially-paid {
            color: #2563eb;
            background: #eff6ff;
            border-color: #dbeafe;
        }

        /* Attachment Preview Miniature cards */
        .attachment-preview-container {
            position: relative;
            width: 84px;
            height: 52px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        .attachment-preview-container:hover {
            transform: translateY(-2px);
            border-color: #3b82f6;
            box-shadow: 0 6px 12px rgba(59, 130, 246, 0.15);
        }

        .attachment-thumbnail-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .attachment-preview-container.pdf-type {
            flex-direction: column;
            gap: 2px;
            padding: 4px;
            background: #fff5f5;
            border-color: #fee2e2;
        }

        .attachment-pdf-icon {
            color: #dc2626;
            font-size: 16px;
        }

        .attachment-pdf-text {
            font-size: 8px;
            font-weight: 800;
            color: #991b1b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .attachment-preview-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            color: #fff;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .attachment-preview-container:hover .attachment-preview-overlay {
            opacity: 1;
        }

        .no-attachment-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #94a3b8;
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Actions buttons inside Table */
        .bill-action-row {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .bill-action-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .bill-action-btn:hover {
            color: #3b82f6;
            background: #eff6ff;
            border-color: #bfdbfe;
            transform: scale(1.05);
        }

        .bill-action-btn.bill-action-delete {
            color: #ef4444;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .bill-action-btn.bill-action-delete:hover {
            color: #fff;
            background: #ef4444;
            border-color: #ef4444;
        }

        /* Inline edit panel styling */
        .bill-edit-row td {
            padding: 0 !important;
        }

        .bill-edit-panel {
            display: none;
            padding: 24px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: inset 0 4px 10px rgba(0, 0, 0, 0.03);
        }

        .bill-empty {
            padding: 40px;
            text-align: center;
            color: #64748b;
            font-size: 15px;
            font-weight: 600;
            background: #fff;
        }

        /* Custom Premium Modal Styles (Popups) - Rendered over sidebar! */
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(8px);
            z-index: 250000 !important; /* Extremely high to display on top of sidebar (10000) */
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.30s ease;
        }

        .custom-modal-overlay.show {
            display: flex !important;
            opacity: 1;
        }

        .custom-modal-container {
            background: #fff;
            border-radius: 24px;
            width: 100%;
            max-width: 620px;
            max-height: calc(100vh - 40px);
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.3);
            transform: scale(0.95);
            transition: transform 0.30s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            margin: auto;
        }

        .custom-modal-overlay.show .custom-modal-container {
            transform: scale(1);
        }

        .custom-modal-header {
            padding: 22px 28px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            flex-shrink: 0;
        }

        .custom-modal-title {
            font-size: 19px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .custom-modal-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            line-height: 1;
            opacity: 0.8;
            transition: all 0.2s;
        }

        .custom-modal-close:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .custom-modal-body {
            padding: 26px;
            background: #fff;
            overflow-y: auto;
        }

        /* Vendor Details Grid in Popup */
        .vendor-details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .vendor-detail-item {
            display: flex;
            flex-direction: column;
        }

        .vendor-detail-item label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .vendor-detail-val {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            padding: 8px 0;
        }

        .vendor-stat-item {
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .vendor-edit-input {
            width: 100%;
            height: 40px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 14px;
            color: #0f172a;
            transition: all 0.2s ease;
        }

        .vendor-edit-input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        /* Modal Input with Prepended Icons */
        .modal-input-wrapper {
            position: relative;
            margin-top: 4px;
        }

        .modal-input-wrapper .input-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 14px;
            z-index: 2;
        }

        .modal-input-wrapper .vendor-edit-input {
            width: 100%;
            height: 44px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            padding: 10px 14px 10px 38px; /* Left padding for icons */
            font-weight: 500;
            color: #0f172a;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        .modal-input-wrapper .vendor-edit-input:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        /* Responsive styling */
        @media (max-width: 768px) {
            .bills-page .container { width: 100%; }
            .bills-header { flex-direction: column; align-items: stretch; padding: 24px; }
            .bills-title { font-size: 26px; }
            .bill-form-grid { grid-template-columns: 1fr; }
            .bills-filter-panel { grid-template-columns: 1fr; }
            .vendor-details-grid { grid-template-columns: 1fr; }
            .bills-btn { width: 100%; }
        }

        /* =====================================================================
           PREMIUM DARK MODE  —  billsPage.php
           Palette mirrors homePage.php: deep charcoal surfaces, soft slate text,
           jewel-toned accents. Toggle handled globally (head.php + js/app.js).
           ===================================================================== */
        html.theme-dark {
            --hpd-bg:        #0a0e14;
            --hpd-surface:   #111722;
            --hpd-surface-2: #161d2b;
            --hpd-border:    #232c3d;
            --hpd-border-2:  #2e3a4f;
            --hpd-text:      #dbe3f0;
            --hpd-text-dim:  #93a1b8;
            --hpd-text-faint:#6b7890;
            --hpd-accent:    #4a9eff;
        }

        html.theme-dark #page-content-wrapper { background: var(--hpd-bg); }
        html.theme-dark .bills-page {
            background: var(--hpd-bg);
            color: var(--hpd-text);
        }

        /* Hero header — keep the blue gradient, it already reads on dark */
        html.theme-dark .bills-header {
            background: linear-gradient(135deg, #060a12 0%, #101827 50%, #1d63b0 100%);
            border-color: var(--hpd-border-2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }
        html.theme-dark .bills-subtitle { color: var(--hpd-text-dim); }

        /* Buttons */
        html.theme-dark .bills-btn-primary {
            background: linear-gradient(135deg, #2f81d6 0%, #1d4ed8 100%);
            box-shadow: 0 10px 20px rgba(29, 99, 176, 0.35);
        }
        html.theme-dark .bills-btn-soft {
            color: var(--hpd-text);
            background: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
        }
        html.theme-dark .bills-btn-soft:hover { background: #1f2a3d; }

        /* Summary cards */
        html.theme-dark .bill-summary-card,
        html.theme-dark .bills-card {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark .bill-summary-blue   { color: #7fb6ef; background: rgba(59,130,246,0.14); }
        html.theme-dark .bill-summary-green  { color: #34d399; background: rgba(16,185,129,0.14); }
        html.theme-dark .bill-summary-indigo { color: #a78bfa; background: rgba(99,102,241,0.16); }
        html.theme-dark .bill-summary-red    { color: #f87171; background: rgba(239,68,68,0.14); }
        html.theme-dark .bill-summary-label,
        html.theme-dark .bill-summary-meta   { color: var(--hpd-text-dim); }
        html.theme-dark .bill-summary-value  { color: #f2f6fc; }

        /* Form panel */
        html.theme-dark .bill-form-panel {
            border-color: var(--hpd-border-2);
            background: linear-gradient(180deg, #111722 0%, #0e131c 100%);
        }
        html.theme-dark .bill-form-group label { color: var(--hpd-text-dim); }
        html.theme-dark .bill-form-group input,
        html.theme-dark .bill-form-group select,
        html.theme-dark .bill-form-group textarea,
        html.theme-dark .bills-filter-panel input,
        html.theme-dark .bills-filter-panel select,
        html.theme-dark .vendor-edit-input,
        html.theme-dark .modal-input-wrapper .vendor-edit-input {
            background: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark .bill-form-group input:focus,
        html.theme-dark .bill-form-group select:focus,
        html.theme-dark .bill-form-group textarea:focus,
        html.theme-dark .bills-filter-panel input:focus,
        html.theme-dark .bills-filter-panel select:focus,
        html.theme-dark .vendor-edit-input:focus,
        html.theme-dark .modal-input-wrapper .vendor-edit-input:focus {
            border-color: var(--hpd-accent);
            box-shadow: 0 0 0 4px rgba(74, 158, 255, 0.18);
            background: var(--hpd-surface-2);
        }
        html.theme-dark .bill-form-group input::placeholder,
        html.theme-dark .bills-filter-panel input::placeholder,
        html.theme-dark .vendor-edit-input::placeholder { color: var(--hpd-text-faint); }
        html.theme-dark .bill-form-actions { border-top-color: var(--hpd-border); }
        html.theme-dark .bills-filter-panel .search-wrap i { color: var(--hpd-text-faint); }

        /* Alerts */
        html.theme-dark .bills-alert-success {
            color: #6ee7b7; background: rgba(16,185,129,0.14); border-color: rgba(16,185,129,0.3);
        }
        html.theme-dark .bills-alert-error {
            color: #fca5a5; background: rgba(220,38,38,0.14); border-color: rgba(220,38,38,0.3);
        }

        /* Table */
        html.theme-dark .table-responsive {
            border-color: var(--hpd-border);
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark .bills-table thead th {
            color: #aeb9cc;
            background: linear-gradient(180deg, #161d2b 0%, #121926 100%);
            border-bottom: 1px solid var(--hpd-border-2);
        }
        html.theme-dark .bills-table tbody td {
            color: var(--hpd-text);
            background: var(--hpd-surface);
            border-bottom: 1px solid var(--hpd-border);
        }
        html.theme-dark .bills-table tbody tr.bill-row:hover td { background-color: var(--hpd-surface-2); }
        html.theme-dark .bill-empty { color: var(--hpd-text-dim); background: var(--hpd-surface); }

        /* Vendor cell */
        html.theme-dark .bill-vendor-icon { color: #7fb6ef; background: rgba(59,130,246,0.14); border-color: rgba(59,130,246,0.3); }
        html.theme-dark .vendor-name-link { color: var(--hpd-text); }
        html.theme-dark .bill-vendor-cell:hover .vendor-name-link { color: #7fb6ef; }

        /* Amounts / dates */
        html.theme-dark .amount-lbl,
        html.theme-dark .date-lbl { color: var(--hpd-text-dim); }
        html.theme-dark .amount-val,
        html.theme-dark .date-val { color: var(--hpd-text); }
        html.theme-dark .amount-val.total-amt { color: #f2f6fc; }
        html.theme-dark .amount-val.paid-amt { color: #34d399; }
        html.theme-dark .remaining-row { border-top-color: var(--hpd-border-2); }
        html.theme-dark .remaining-row.has-remaining .amount-val { color: #7fb6ef; }
        html.theme-dark .due-date-highlight { background: var(--hpd-surface-2); border-color: var(--hpd-border-2); }
        html.theme-dark .due-date-highlight.due-overdue { color: #fca5a5; background: rgba(220,38,38,0.14); border-color: rgba(220,38,38,0.3); }

        /* Status badges */
        html.theme-dark .bill-status-paid { color: #6ee7b7; background: rgba(22,163,74,0.16); border-color: rgba(22,163,74,0.3); }
        html.theme-dark .bill-status-unpaid { color: #fdba74; background: rgba(234,88,12,0.16); border-color: rgba(234,88,12,0.3); }
        html.theme-dark .bill-status-overdue { color: #fca5a5; background: rgba(220,38,38,0.16); border-color: rgba(220,38,38,0.3); }
        html.theme-dark .bill-status-partially-paid { color: #93c5fd; background: rgba(37,99,235,0.16); border-color: rgba(37,99,235,0.3); }

        /* Attachments */
        html.theme-dark .attachment-preview-container { background: var(--hpd-surface-2); border-color: var(--hpd-border-2); }
        html.theme-dark .attachment-preview-container.pdf-type { background: rgba(220,38,38,0.1); border-color: rgba(220,38,38,0.28); }
        html.theme-dark .attachment-pdf-text { color: #fca5a5; }
        html.theme-dark .no-attachment-badge { color: var(--hpd-text-dim); background: var(--hpd-surface-2); }

        /* Action buttons */
        html.theme-dark .bill-action-btn {
            color: var(--hpd-text-dim);
            background: var(--hpd-surface-2);
            border-color: var(--hpd-border);
        }
        html.theme-dark .bill-action-btn:hover { color: #7fb6ef; background: rgba(59,130,246,0.16); border-color: rgba(59,130,246,0.35); }
        html.theme-dark .bill-action-btn.bill-action-delete { color: #f87171; background: rgba(220,38,38,0.14); border-color: rgba(220,38,38,0.3); }
        html.theme-dark .bill-action-btn.bill-action-delete:hover { color: #fff; background: #dc2626; border-color: #dc2626; }

        /* Inline edit panel */
        html.theme-dark .bill-edit-panel { background: #0e131c; border-bottom-color: var(--hpd-border); }

        /* Custom modal */
        html.theme-dark .custom-modal-container { background: var(--hpd-surface); box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6); }
        html.theme-dark .custom-modal-header { background: linear-gradient(135deg, #060a12 0%, #101827 100%); border-bottom-color: var(--hpd-border); }
        html.theme-dark .custom-modal-body { background: var(--hpd-surface); color: var(--hpd-text); }
        html.theme-dark .vendor-detail-item label { color: var(--hpd-text-dim); }
        html.theme-dark .vendor-detail-val { color: var(--hpd-text); }
        html.theme-dark .vendor-stat-item { background: var(--hpd-surface-2); border-color: var(--hpd-border); }
        html.theme-dark .modal-input-wrapper .input-icon { color: var(--hpd-text-faint); }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset bills-page">
            <div class="container">
                
                <!-- Premium Header -->
                <div class="bills-header">
                    <div class="bills-header-content">
                        <div class="bills-hero-icon"><i class="fa fa-file-text-o"></i></div>
                        <div>
                            <h1 class="bills-title">Bills Management</h1>
                            <p class="bills-subtitle">Track supplier bills, payments, TDS, and outstanding dues.</p>
                        </div>
                    </div>
                    <div class="bills-header-actions" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <button type="button" class="bills-btn bills-btn-soft" id="downloadBillsReport"><i class="fa fa-cloud-download"></i> Download Report</button>
                        <button type="button" class="bills-btn bills-btn-primary" id="toggleBillForm"><i class="fa fa-plus"></i> Add New Bill</button>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if ($message !== '') { ?><div class="bills-alert bills-alert-success"><i class="fa fa-check-circle"></i> <?php echo h($message); ?></div><?php } ?>
                <?php if ($errorMessage !== '') { ?><div class="bills-alert bills-alert-error"><i class="fa fa-exclamation-circle"></i> <?php echo h($errorMessage); ?></div><?php } ?>

                <!-- Add New Bill Panel -->
                <div class="bills-card bill-form-panel" id="billFormPanel">
                    <div class="bills-card-body">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="bill_action" value="add">
                            <div class="bill-form-grid">
                                <div class="bill-form-group">
                                    <label>Supplier</label>
                                    <select name="supplier_id" required>
                                        <option value="">Select registered supplier</option>
                                        <?php foreach ($activeSuppliers as $supplier) { ?>
                                            <option value="<?php echo (int) $supplier['supplier_id']; ?>"><?php echo h($supplier['supplier_name'] . ' - ' . aitfSupplierTypeLabel($supplier['supplier_type'])); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="bill-form-group">
                                    <label>Bill Number</label>
                                    <input type="text" name="bill_number" placeholder="Supplier bill/invoice number">
                                </div>
                                <div class="bill-form-group">
                                    <label>Bill Date</label>
                                    <input type="date" name="bill_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="bill-form-group">
                                    <label>Bill Amount (INR)</label>
                                    <input type="number" name="amount" class="amount-input" min="0" step="0.01" placeholder="0.00" required>
                                </div>
                                <div class="bill-form-group">
                                    <label>Due Date</label>
                                    <input type="date" name="due_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="bill-form-group">
                                    <label>Attach Bill File (JPG, PNG, PDF)</label>
                                    <input type="file" name="attached_file" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                                </div>
                                <div class="bill-form-group bill-form-full">
                                    <label>Description </label>
                                    <textarea name="description" placeholder="Add specific bill details or payment instructions..."></textarea>
                                </div>
                            </div>
                            <div class="bill-form-actions">
                                <button type="button" class="bills-btn bills-btn-soft" id="cancelBillForm">Cancel</button>
                                <button type="submit" class="bills-btn bills-btn-primary">Save Bill</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Dashboard Cards (Table ke Upar) -->
                <div class="bill-summary-grid">
                    <div class="bill-summary-card">
                        <div class="bill-summary-icon bill-summary-blue"><i class="fa fa-file-text-o"></i></div>
                        <div>
                            <div class="bill-summary-label">Total Bills Amount</div>
                            <div class="bill-summary-value">INR. <?php echo formatBillAmount($summary['total_amount']); ?></div>
                            <div class="bill-summary-meta"><?php echo (int) $summary['total_bills']; ?> bills recorded</div>
                        </div>
                    </div>
                    <div class="bill-summary-card">
                        <div class="bill-summary-icon bill-summary-green"><i class="fa fa-check-circle-o"></i></div>
                        <div>
                            <div class="bill-summary-label">Total Paid Amount</div>
                            <div class="bill-summary-value text-success">INR. <?php echo formatBillAmount($summary['paid_amount']); ?></div>
                            <div class="bill-summary-meta">Settled Amount</div>
                        </div>
                    </div>
                    <div class="bill-summary-card">
                        <div class="bill-summary-icon bill-summary-indigo"><i class="fa fa-hourglass-half"></i></div>
                        <div>
                            <div class="bill-summary-label">Remaining Dues</div>
                            <div class="bill-summary-value text-primary">INR. <?php echo formatBillAmount($summary['remaining_amount']); ?></div>
                            <div class="bill-summary-meta">Outstanding balance</div>
                        </div>
                    </div>
                    <div class="bill-summary-card">
                        <div class="bill-summary-icon bill-summary-red"><i class="fa fa-exclamation-triangle"></i></div>
                        <div>
                            <div class="bill-summary-label">Overdue Amount</div>
                            <div class="bill-summary-value text-danger">INR. <?php echo formatBillAmount($summary['overdue_amount']); ?></div>
                            <div class="bill-summary-meta"><?php echo (int) $summary['overdue_count']; ?> overdue bills</div>
                        </div>
                    </div>
                </div>

                <!-- Filters & Table Panel -->
                <div class="bills-card">
                    <div class="bills-card-body">
                        
                        <!-- Premium Filter Panel -->
                        <div class="bills-filter-panel">
                            <div class="search-wrap">
                                <i class="fa fa-search"></i>
                                <input type="text" id="billSearch" placeholder="Search bills by supplier, bill number or description...">
                            </div>
                            <select id="billStatusFilter">
                                <option value="All">All Statuses</option>
                                <option value="Paid">Paid</option>
                                <option value="Unpaid">Unpaid</option>
                                <option value="Partially Paid">Partially Paid</option>
                                <option value="Overdue">Overdue</option>
                            </select>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover bills-table" id="billsTable">
                                <thead>
                                    <tr>
                                        <th>Supplier</th>
                                        <th>Date Details</th>
                                        <th>Amount Details</th>
                                        <th>Status</th>
                                        <th>Attachment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bills as $bill) { 
                                        $statusInfo = getBillStatusInfo($bill);
                                        $isOverdueFlag = ($statusInfo['status'] === 'Overdue');
                                        $supplierDisplayName = !empty($bill['supplier_display_name']) ? $bill['supplier_display_name'] : $bill['vendor_name'];
                                        $derivedPaidAmount = isset($bill['derived_paid_amount']) ? (float) $bill['derived_paid_amount'] : (float) $bill['paid_amount'];
                                        $tdsAmount = isset($bill['tds_amount']) ? (float) $bill['tds_amount'] : 0;
                                        $settledAmount = $derivedPaidAmount + $tdsAmount;
                                        $remainingAmount = max(0, (float) $bill['amount'] - $settledAmount);
                                    ?>
                                        <tr class="bill-row" data-status="<?php echo h($statusInfo['status']); ?>" data-search="<?php echo h(strtolower($supplierDisplayName . ' ' . $bill['bill_number'] . ' ' . $bill['description'])); ?>">
                                            <td>
                                                <div class="bill-vendor-cell" title="Supplier details">
                                                    <span class="bill-vendor-icon"><?php echo strtoupper(substr($supplierDisplayName, 0, 1)); ?></span>
                                                    <div>
                                                        <strong class="vendor-name-link"><?php echo h($supplierDisplayName); ?></strong>
                                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                                            <?php if (!empty($bill['supplier_id'])) { ?>
                                                                <a href="suppliers.php?action=edit&supplier_id=<?php echo (int) $bill['supplier_id']; ?>">View Supplier</a>
                                                            <?php } else { ?>
                                                                Legacy Vendor
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="date-detail-stack">
                                                    <div class="date-detail-row">
                                                        <span class="date-lbl">Bill No:</span>
                                                        <span class="date-val"><?php echo h($bill['bill_number'] ?: '-'); ?></span>
                                                    </div>
                                                    <div class="date-detail-row">
                                                        <span class="date-lbl">Bill Date:</span>
                                                        <span class="date-val"><?php echo !empty($bill['bill_date']) ? date('d M Y', strtotime($bill['bill_date'])) : date('d M Y', strtotime($bill['created_at'])); ?></span>
                                                    </div>
                                                    <div class="date-detail-row">
                                                        <span class="date-lbl">Due:</span>
                                                        <span class="date-val due-date-highlight <?php echo $isOverdueFlag ? 'due-overdue' : ''; ?>">
                                                            <?php echo !empty($bill['due_date']) ? date('d M Y', strtotime($bill['due_date'])) : 'N/A'; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="amount-detail-stack">
                                                    <div class="amount-detail-row">
                                                        <span class="amount-lbl">Total:</span>
                                                        <span class="amount-val total-amt">INR. <?php echo formatBillAmount($bill['amount']); ?></span>
                                                    </div>
                                                    <div class="amount-detail-row">
                                                        <span class="amount-lbl">Paid:</span>
                                                        <span class="amount-val paid-amt">Rs. <?php echo formatBillAmount($derivedPaidAmount); ?></span>
                                                    </div>
                                                    <div class="amount-detail-row">
                                                        <span class="amount-lbl">TDS:</span>
                                                        <span class="amount-val paid-amt">Rs. <?php echo formatBillAmount($tdsAmount); ?></span>
                                                    </div>
                                                    <div class="amount-detail-row remaining-row <?php echo $remainingAmount > 0 ? 'has-remaining' : ''; ?>">
                                                        <span class="amount-lbl">Remaining:</span>
                                                        <span class="amount-val remaining-amt">INR. <?php echo formatBillAmount($remainingAmount); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                                    <span class="bill-status <?php echo $statusInfo['class']; ?>"><?php echo h($statusInfo['label']); ?></span>
                                                    <span style="font-size: 11px; color: #64748b; font-weight: 500;"><?php echo h($statusInfo['subtext']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($bill['attached_file'])) { 
                                                    $ext = strtolower(pathinfo($bill['attached_file'], PATHINFO_EXTENSION));
                                                    $isPdf = ($ext === 'pdf');
                                                ?>
                                                    <div class="attachment-preview-container <?php echo $isPdf ? 'pdf-type' : ''; ?>" data-url="<?php echo h($bill['attached_file']); ?>">
                                                        <?php if ($isPdf) { ?>
                                                            <div class="attachment-pdf-icon"><i class="fa fa-file-pdf-o"></i></div>
                                                            <div class="attachment-pdf-text">PDF Document</div>
                                                        <?php } else { ?>
                                                            <img src="<?php echo h($bill['attached_file']); ?>" class="attachment-thumbnail-img" alt="Invoice Thumbnail">
                                                        <?php } ?>
                                                        <span class="attachment-preview-overlay"><i class="fa fa-eye"></i> View</span>
                                                    </div>
                                                <?php } else { ?>
                                                    <span class="no-attachment-badge"><i class="fa fa-times-circle"></i> No File</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="bill-action-row">
                                                    <button type="button" class="bill-action-btn recordPaymentToggle" data-target="paymentBill<?php echo (int) $bill['bill_id']; ?>" title="Record Payment"><i class="fa fa-money"></i></button>
                                                    <button type="button" class="bill-action-btn editBillToggle" data-target="editBill<?php echo (int) $bill['bill_id']; ?>" title="Edit Bill"><i class="fa fa-pencil"></i></button>
                                                    <form method="post" class="bill-inline-form" onsubmit="return confirm('Are you sure you want to delete this bill?');">
                                                        <input type="hidden" name="bill_action" value="delete">
                                                        <input type="hidden" name="bill_id" value="<?php echo (int) $bill['bill_id']; ?>">
                                                        <button type="submit" class="bill-action-btn bill-action-delete" title="Delete Bill"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="bill-payment-row">
                                            <td colspan="6">
                                                <div class="bill-edit-panel" id="paymentBill<?php echo (int) $bill['bill_id']; ?>">
                                                    <form method="post" enctype="multipart/form-data">
                                                        <input type="hidden" name="bill_action" value="record_payment">
                                                        <input type="hidden" name="bill_id" value="<?php echo (int) $bill['bill_id']; ?>">
                                                        <div class="bill-form-grid">
                                                            <div class="bill-form-group">
                                                                <label>Payment Amount (INR)</label>
                                                                <input type="number" name="payment_amount" min="0" step="0.01" value="<?php echo $remainingAmount > 0 ? h($remainingAmount) : '0.00'; ?>" required>
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Payment Date</label>
                                                                <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Payment Mode</label>
                                                                <select name="payment_mode" required>
                                                                    <option value="bank_transfer">Bank Transfer</option>
                                                                    <option value="upi">UPI</option>
                                                                    <option value="cheque">Cheque</option>
                                                                    <option value="cash">Cash</option>
                                                                    <option value="card">Card</option>
                                                                    <option value="other">Other</option>
                                                                </select>
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Account / Bank Reference</label>
                                                                <input type="text" name="account_reference" placeholder="Bank/account reference">
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Transaction / UTR Number</label>
                                                                <input type="text" name="utr_number" placeholder="UTR / cheque / transaction id">
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>TDS Deducted (INR)</label>
                                                                <input type="number" name="tds_deducted" min="0" step="0.01" value="0.00">
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Payment Proof</label>
                                                                <input type="file" name="payment_proof_file" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                                                            </div>
                                                            <div class="bill-form-group bill-form-full">
                                                                <label>Notes</label>
                                                                <textarea name="payment_notes" placeholder="Payment notes or TDS details..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="bill-form-actions">
                                                            <button type="button" class="bills-btn bills-btn-soft paymentBillCancel" data-target="paymentBill<?php echo (int) $bill['bill_id']; ?>">Cancel</button>
                                                            <button type="submit" class="bills-btn bills-btn-primary">Record Payment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="bill-edit-row">
                                            <td colspan="6">
                                                <div class="bill-edit-panel" id="editBill<?php echo (int) $bill['bill_id']; ?>">
                                                    <form method="post" enctype="multipart/form-data">
                                                        <input type="hidden" name="bill_action" value="edit">
                                                        <input type="hidden" name="bill_id" value="<?php echo (int) $bill['bill_id']; ?>">
                                                        <div class="bill-form-grid">
                                                            <div class="bill-form-group">
                                                                <label>Supplier</label>
                                                                <select name="supplier_id" required>
                                                                    <option value="">Select registered supplier</option>
                                                                    <?php foreach ($activeSuppliers as $supplier) { ?>
                                                                        <option value="<?php echo (int) $supplier['supplier_id']; ?>" <?php echo (int) $bill['supplier_id'] === (int) $supplier['supplier_id'] ? 'selected' : ''; ?>><?php echo h($supplier['supplier_name'] . ' - ' . aitfSupplierTypeLabel($supplier['supplier_type'])); ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Bill Number</label>
                                                                <input type="text" name="bill_number" value="<?php echo h($bill['bill_number']); ?>">
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Bill Date</label>
                                                                <input type="date" name="bill_date" value="<?php echo !empty($bill['bill_date']) ? h($bill['bill_date']) : date('Y-m-d', strtotime($bill['created_at'])); ?>" required>
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Bill Amount (INR.)</label>
                                                                <input type="number" name="amount" class="amount-input" min="0" step="0.01" value="<?php echo h($bill['amount']); ?>" required>
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Due Date</label>
                                                                <input type="date" name="due_date" value="<?php echo !empty($bill['due_date']) ? h($bill['due_date']) : date('Y-m-d'); ?>" required>
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Replace Attached File</label>
                                                                <input type="file" name="attached_file" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                                                            </div>
                                                            <div class="bill-form-group bill-form-full">
                                                                <label>Description</label>
                                                                <textarea name="description"><?php echo h($bill['description']); ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="bill-form-actions">
                                                            <button type="button" class="bills-btn bills-btn-soft editBillCancel" data-target="editBill<?php echo (int) $bill['bill_id']; ?>">Cancel</button>
                                                            <button type="submit" class="bills-btn bills-btn-primary">Update Bill</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <div class="bill-empty" id="billEmptyState" <?php echo count($bills) > 0 ? 'style="display:none;"' : ''; ?>>No bills found.</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Attachment Preview Modal -->
<div class="custom-modal-overlay" id="attachmentPreviewModal">
    <div class="custom-modal-container" style="max-width: 800px;">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title"><i class="fa fa-file-image-o"></i> Preview</h5>
            <button type="button" class="custom-modal-close closeModalBtn">&times;</button>
        </div>
        <div class="custom-modal-body" style="padding: 10px; background: #0f172a; min-height: 400px; display: flex; align-items: center; justify-content: center;">
            <div id="attachmentPreviewContent" style="width: 100%; display: flex; align-items: center; justify-content: center;">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        function filterBills() {
            var query = $('#billSearch').val().toLowerCase();
            var status = $('#billStatusFilter').val();
            var visibleRows = 0;

            $('.bill-row').each(function () {
                var matchesSearch = $(this).attr('data-search').indexOf(query) !== -1;
                var matchesStatus = status === 'All' || $(this).attr('data-status') === status;
                var showRow = matchesSearch && matchesStatus;
                var editRow = $(this).nextAll('.bill-edit-row:first');
                var paymentRow = $(this).nextAll('.bill-payment-row:first');

                $(this).toggle(showRow);
                editRow.toggle(showRow && editRow.find('.bill-edit-panel').is(':visible'));
                paymentRow.toggle(showRow && paymentRow.find('.bill-edit-panel').is(':visible'));

                if (showRow) {
                    visibleRows++;
                }
            });

            $('#billEmptyState').toggle(visibleRows === 0);
        }

        // Toggle creation panel
        $('#toggleBillForm').click(function () {
            $('#billFormPanel').slideToggle(200);
        });

        $('#cancelBillForm').click(function () {
            $('#billFormPanel').slideUp(200);
        });

        // Helper to update amounts and status in a form
        function updateFormAmounts(form, triggerSource) {
            var amountInput = form.find('.amount-input');
            var paidAmountInput = form.find('.paid-amount-input');
            var statusSelect = form.find('.status-select');
            var remainingSpan = form.find('.calc-remaining');
            
            var amount = parseFloat(amountInput.val()) || 0;
            var paidAmount = parseFloat(paidAmountInput.val()) || 0;
            
            // Limit paid amount to not exceed bill amount
            if (paidAmount > amount) {
                paidAmount = amount;
                paidAmountInput.val(paidAmount.toFixed(2));
            }
            
            var remaining = amount - paidAmount;
            if (remaining < 0) remaining = 0;
            
            remainingSpan.text(remaining.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            
            // Auto update status if inputs are changed directly
            if (triggerSource === 'input') {
                if (amount === 0) {
                    statusSelect.val('Unpaid');
                } else if (paidAmount === 0) {
                    statusSelect.val('Unpaid');
                } else if (paidAmount >= amount) {
                    statusSelect.val('Paid');
                } else {
                    statusSelect.val('Partially Paid');
                }
            }
        }

        // Live calculation and status auto-update on user typing
        $(document).on('input change', '.amount-input, .paid-amount-input', function() {
            var form = $(this).closest('form');
            updateFormAmounts(form, 'input');
        });

        // Auto update paid amount when status changes manually
        $(document).on('change', '.status-select', function() {
            var status = $(this).val();
            var form = $(this).closest('form');
            var amountInput = form.find('.amount-input');
            var paidAmountInput = form.find('.paid-amount-input');
            var amountVal = parseFloat(amountInput.val()) || 0;
            
            if (status === 'Paid') {
                paidAmountInput.val(amountVal > 0 ? amountVal.toFixed(2) : '0.00');
            } else if (status === 'Unpaid') {
                paidAmountInput.val('0.00');
            } else if (status === 'Partially Paid') {
                var paidVal = parseFloat(paidAmountInput.val()) || 0;
                if (paidVal === 0 || paidVal === amountVal) {
                    paidAmountInput.val('');
                }
            }
            
            updateFormAmounts(form, 'status');
            
            // Focus on paid amount to help user type
            if (status === 'Partially Paid') {
                paidAmountInput.focus();
            }
        });

        $('#billSearch, #billStatusFilter').on('keyup change input', filterBills);

        $('.editBillToggle').click(function () {
            var target = $('#' + $(this).data('target'));
            $('.bill-edit-panel').not(target).slideUp(160).closest('.bill-edit-row').hide();
            $('.bill-edit-panel').not(target).closest('.bill-payment-row').hide();
            target.closest('.bill-edit-row').show();
            target.slideToggle(160, filterBills);
        });

        $('.editBillCancel').click(function () {
            $('#' + $(this).data('target')).slideUp(160, filterBills);
        });

        $('.recordPaymentToggle').click(function () {
            var target = $('#' + $(this).data('target'));
            $('.bill-edit-panel').not(target).slideUp(160).closest('.bill-edit-row').hide();
            $('.bill-edit-panel').not(target).closest('.bill-payment-row').hide();
            target.closest('.bill-payment-row').show();
            target.slideToggle(160, filterBills);
        });

        $('.paymentBillCancel').click(function () {
            $('#' + $(this).data('target')).slideUp(160, filterBills);
        });

        // Attachment Preview popup handler
        $('.attachment-preview-container').click(function() {
            var url = $(this).data('url');
            var ext = url.split('.').pop().toLowerCase();
            var content = '';
            
            if (ext === 'pdf') {
                content = '<iframe src="' + encodeURI(url) + '" style="width:100%; height:600px; border:none; border-radius:12px;"></iframe>';
            } else {
                content = '<img src="' + encodeURI(url) + '" style="max-width:100%; max-height:80vh; object-fit:contain; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.5);">';
            }
            
            $('#attachmentPreviewContent').html(content);
            openModal('#attachmentPreviewModal');
        });

        // Modal Helpers
        function openModal(selector) {
            var modal = $(selector);
            modal.css('display', 'flex');
            setTimeout(function() {
                modal.addClass('show');
            }, 10);
        }

        function closeModal() {
            $('.custom-modal-overlay').removeClass('show');
            setTimeout(function() {
                $('.custom-modal-overlay').css('display', 'none');
            }, 300);
        }

        $('.closeModalBtn').click(closeModal);
        
        // Close modal when clicking outside content container
        $('.custom-modal-overlay').click(function(e) {
            if ($(e.target).hasClass('custom-modal-overlay')) {
                closeModal();
            }
        });

        // Download Report PDF Handler
        $('#downloadBillsReport').click(function () {
            var searchQuery = $('#billSearch').val();
            var statusFilter = $('#billStatusFilter').val();
            var url = 'exportBillsReport.php';
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                    '<input type="hidden" name="searchQuery" value="' + searchQuery + '" />' +
                    '<input type="hidden" name="statusFilter" value="' + statusFilter + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
            form.remove();
        });

        $('.bill-edit-row, .bill-payment-row').hide();
        filterBills();
    });
</script>

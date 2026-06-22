<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Bills";
    // include 'header.php';
    include 'dbConnect.php';

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
        $summary = array('total_bills' => 0, 'total_amount' => 0, 'paid_amount' => 0, 'unpaid_amount' => 0);
    } else {
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS bills (
                bill_id INT AUTO_INCREMENT PRIMARY KEY,
                vendor_name VARCHAR(255) NOT NULL,
                amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                payment_status ENUM('Paid','Unpaid') NOT NULL DEFAULT 'Unpaid',
                description TEXT NULL,
                attached_file VARCHAR(500) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bill_action'])) {
                $action = $_POST['bill_action'];
                $vendorName = trim(isset($_POST['vendor_name']) ? $_POST['vendor_name'] : '');
                $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
                $paymentStatus = (isset($_POST['payment_status']) && $_POST['payment_status'] === 'Paid') ? 'Paid' : 'Unpaid';
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
                } else if ($vendorName === '' || $amount < 0) {
                    $errorMessage = 'Please enter a vendor name and a valid bill amount.';
                } else if ($action === 'add') {
                    $attachedFile = getBillUploadPath('attached_file', '', $uploadDir, $allowedExtensions, $allowedMimeTypes, $errorMessage);
                    if ($attachedFile !== false) {
                        $insertStatement = $db->prepare('INSERT INTO bills (vendor_name, amount, payment_status, description, attached_file) VALUES (:vendor_name, :amount, :payment_status, :description, :attached_file)');
                        $insertStatement->execute(array(
                            ':vendor_name' => $vendorName,
                            ':amount' => $amount,
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

                        $updateStatement = $db->prepare('UPDATE bills SET vendor_name = :vendor_name, amount = :amount, payment_status = :payment_status, description = :description, attached_file = :attached_file WHERE bill_id = :bill_id');
                        $updateStatement->execute(array(
                            ':vendor_name' => $vendorName,
                            ':amount' => $amount,
                            ':payment_status' => $paymentStatus,
                            ':description' => $description,
                            ':attached_file' => $attachedFile,
                            ':bill_id' => $billId
                        ));
                        $message = 'Bill updated successfully.';
                    }
                }
            }

            $statement = $db->query('SELECT * FROM bills ORDER BY created_at DESC, bill_id DESC');
            $bills = $statement->fetchAll(PDO::FETCH_ASSOC);

            $summaryStatement = $db->query("SELECT COUNT(*) AS total_bills, COALESCE(SUM(amount), 0) AS total_amount, COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN amount ELSE 0 END), 0) AS paid_amount, COALESCE(SUM(CASE WHEN payment_status = 'Unpaid' THEN amount ELSE 0 END), 0) AS unpaid_amount FROM bills");
            $summary = $summaryStatement->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $errorMessage = 'Unable to load bills right now.';
            $bills = array();
            $summary = array('total_bills' => 0, 'total_amount' => 0, 'paid_amount' => 0, 'unpaid_amount' => 0);
        }
    }
    ?>

    <style>
        .bills-page {
            min-height: calc(100vh - 70px);
            padding: 30px 0 44px;
            background: linear-gradient(180deg, #f8fbff 0%, #f3f7fc 100%);
            color: #071d3a;
        }

        .bills-page .container {
            width: 96%;
            max-width: 1480px;
        }

        .bills-header {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            min-height: 185px;
            margin-bottom: 34px;
            padding: 42px 42px 42px 178px;
            overflow: hidden;
            color: #fff;
            background: linear-gradient(112deg, #071f55 0%, #0b4fc2 54%, #248ff4 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 28px;
            box-shadow: 0 22px 60px rgba(11, 42, 98, 0.18);
        }

        .bills-header:before {
            content: "";
            position: absolute;
            right: -80px;
            top: -100px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.13);
        }

        .bills-header:after {
            content: "";
            position: absolute;
            right: 120px;
            bottom: -80px;
            width: 170px;
            height: 170px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        .bills-header > * {
            position: relative;
            z-index: 1;
        }

        .bills-title {
            position: relative;
            margin: 0;
            color: #fff;
            font-size: 48px;
            font-weight: 900;
            letter-spacing: -1.2px;
        }

        .bills-title:before {
            content: "\f1c1";
            position: absolute;
            left: -132px;
            top: 50%;
            width: 96px;
            height: 96px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: FontAwesome;
            font-size: 48px;
            color: #fff;
            border-radius: 24px;
            background: linear-gradient(145deg, #2f6ff2 0%, #1556d6 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18), 0 18px 38px rgba(0, 0, 0, 0.16);
            transform: translateY(-50%);
        }

        .bills-title:after {
            content: "\f155";
            position: absolute;
            left: -58px;
            top: 57%;
            width: 31px;
            height: 31px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: FontAwesome;
            font-size: 16px;
            color: #1a64dc;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.18);
        }

        .bills-subtitle {
            max-width: 680px;
            margin: 16px 0 0;
            color: rgba(255, 255, 255, 0.92);
            font-size: 23px;
            font-weight: 650;
            line-height: 1.45;
        }

        .bills-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            border: 0;
            border-radius: 999px;
            padding: 11px 20px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.2px;
            line-height: 1;
            white-space: nowrap;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
        }

        .bills-btn-primary {
            color: #fff;
            background: linear-gradient(135deg, #18a0fb, #0d4d8b);
            box-shadow: 0 14px 30px rgba(13, 77, 139, 0.28);
        }

        .bills-header .bills-btn-primary {
            color: #0b2545;
            background: #fff;
            box-shadow: 0 18px 38px rgba(0, 0, 0, 0.18);
        }

        .bills-btn-primary:hover,
        .bills-btn-primary:focus {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 18px 38px rgba(13, 77, 139, 0.34);
        }

        .bills-header .bills-btn-primary:hover,
        .bills-header .bills-btn-primary:focus {
            color: #0b2545;
            background: #f3f8ff;
        }

        .bills-btn-soft {
            color: #0d4d8b;
            background: #eef6ff;
            border: 1px solid #d9e9fb;
        }

        .bills-btn-soft:hover {
            color: #083966;
            background: #e0f0ff;
            transform: translateY(-1px);
        }

        .bills-btn-danger {
            color: #dc2626;
            background: #fff1f2;
            border: 1px solid #ffe0e3;
        }

        .bills-btn-danger:hover {
            color: #fff;
            background: #dc2626;
            transform: translateY(-1px);
        }

        .bills-card {
            position: relative;
            margin-bottom: 30px;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e0e9f5;
            border-radius: 26px;
            box-shadow: 0 18px 50px rgba(12, 35, 68, 0.08);
        }

        .bills-card-body {
            padding: 32px;
        }

        .bill-form-panel {
            display: none;
            border-color: rgba(24, 160, 251, 0.28);
        }

        .bill-form-panel:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #18a0fb, #2dd4bf, #0d4d8b);
        }

        .bill-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .bill-form-group label {
            display: block;
            margin-bottom: 8px;
            color: #304761;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .bill-form-group input,
        .bill-form-group select,
        .bill-form-group textarea,
        .bills-filter-panel input,
        .bills-filter-panel select {
            width: 100%;
            border: 1px solid #d6e4f0;
            border-radius: 15px;
            padding: 12px 14px;
            color: #18324c;
            background: #fbfdff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
            transition: border 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .bill-form-group input,
        .bill-form-group select,
        .bills-filter-panel input,
        .bills-filter-panel select {
            height: 76px;
        }

        .bill-form-group textarea {
            min-height: 110px;
            resize: vertical;
        }

        .bill-form-group input:focus,
        .bill-form-group select:focus,
        .bill-form-group textarea:focus,
        .bills-filter-panel input:focus,
        .bills-filter-panel select:focus {
            border-color: #18a0fb;
            outline: none;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(24, 160, 251, 0.13);
        }

        .bill-form-full {
            grid-column: 1 / -1;
        }

        .bill-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 18px;
        }

        .bills-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 16px;
            font-weight: 700;
            box-shadow: 0 12px 28px rgba(15, 39, 68, 0.08);
        }

        .bills-alert-success {
            color: #047857;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
        }

        .bills-alert-error {
            color: #b91c1c;
            background: #fff1f2;
            border: 1px solid #fecaca;
        }

        .bills-filter-panel {
            display: grid;
            grid-template-columns: 1.65fr 0.78fr 0.95fr;
            gap: 22px;
            align-items: center;
            margin-bottom: 32px;
            padding: 0;
            background: transparent;
            border: 0;
            border-radius: 0;
        }

        .bills-filter-panel .search-wrap {
            position: relative;
            flex: 1 1 360px;
        }

        .bills-filter-panel .search-wrap i {
            position: absolute;
            top: 50%;
            left: 16px;
            color: #0d4d8b;
            transform: translateY(-50%);
            z-index: 1;
        }

        .bills-filter-panel input {
            padding-left: 66px;
            font-size: 19px;
            font-weight: 550;
        }

        .bills-filter-panel select {
            min-width: 180px;
            padding-left: 58px;
            font-size: 17px;
            font-weight: 800;
            color: #061b49;
            appearance: none;
            background-image: linear-gradient(45deg, transparent 50%, #071d3a 50%), linear-gradient(135deg, #071d3a 50%, transparent 50%);
            background-position: calc(100% - 22px) 50%, calc(100% - 14px) 50%;
            background-size: 8px 8px, 8px 8px;
            background-repeat: no-repeat;
        }

        .bills-filter-panel:after {
            content: "\f073  Select Date Range  \f107";
            display: flex;
            align-items: center;
            height: 76px;
            padding: 0 32px;
            color: #63769b;
            font-family: FontAwesome, Arial, sans-serif;
            font-size: 19px;
            font-weight: 700;
            white-space: pre;
            background: #fff;
            border: 1px solid #e0e9f5;
            border-radius: 18px;
            box-shadow: 0 10px 28px rgba(14, 42, 82, 0.04);
        }

        .table-responsive {
            overflow: hidden;
            border: 1px solid #e4edf7;
            border-radius: 22px;
            background: #fff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9), 0 14px 34px rgba(15, 39, 68, 0.06);
        }

        .bills-table {
            margin-bottom: 0;
            border: 0 !important;
            border-collapse: separate !important;
            border-spacing: 0;
            background: #fff;
        }

        .bills-table thead th {
            padding: 18px 20px !important;
            color: #61728a;
            background: linear-gradient(180deg, #f8fbff 0%, #f2f7fd 100%);
            border: 0 !important;
            border-bottom: 1px solid #e7eef7 !important;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            white-space: nowrap;
        }

        .bills-table tbody td {
            padding: 20px !important;
            vertical-align: middle !important;
            color: #243850;
            background: #fff;
            border: 0 !important;
            border-bottom: 1px solid #edf3f8 !important;
        }

        .bills-table tbody tr.bill-row:nth-child(4n+3) td {
            background: #fbfdff;
        }

        .bills-table tbody tr.bill-row:hover td {
            background: #f3f9ff;
        }

        .bills-table tbody tr.bill-row td:first-child {
            position: relative;
        }

        .bills-table tbody tr.bill-row td:first-child:before {
            content: "";
            position: absolute;
            left: 0;
            top: 18px;
            bottom: 18px;
            width: 4px;
            border-radius: 0 999px 999px 0;
            background: linear-gradient(180deg, #18a0fb, #0d4d8b);
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .bills-table tbody tr.bill-row:hover td:first-child:before {
            opacity: 1;
        }

        .bills-table tbody tr.bill-row td:first-child strong {
            color: #102a43;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: -0.2px;
        }

        .bill-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .bill-status:before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .bill-status-paid {
            color: #047857;
            background: #dcfce7;
        }

        .bill-status-unpaid {
            color: #d97706;
            background: #fff7ed;
        }

        .bill-file-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            color: #0d4d8b;
            font-weight: 900;
            text-decoration: none;
            background: #eef6ff;
            border: 1px solid #d8e9fb;
            border-radius: 999px;
        }

        .bill-file-link:hover {
            color: #18a0fb;
            text-decoration: none;
        }

        .bill-action-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .bill-action-row .bills-btn {
            min-width: 86px;
            padding: 12px 18px;
        }

        .bill-inline-form {
            margin: 0;
        }

        .bill-edit-panel {
            display: none;
            padding: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f4f9ff 100%);
            border: 1px solid #d9e9fb;
            border-radius: 18px;
            box-shadow: inset 0 1px 0 #fff;
        }

        .bill-empty {
            padding: 36px;
            text-align: center;
            color: #6b7c93;
            font-size: 15px;
            font-weight: 700;
            background: #fff;
        }

        .bill-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            margin-top: 24px;
        }

        .bill-summary-card {
            position: relative;
            padding: 24px;
            overflow: hidden;
            border-radius: 24px;
            background: #fff;
            border: 1px solid rgba(216, 232, 248, 0.95);
            box-shadow: 0 18px 48px rgba(15, 39, 68, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .bill-summary-card:before {
            content: "";
            position: absolute;
            right: -35px;
            top: -35px;
            width: 115px;
            height: 115px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(24, 160, 251, 0.16), rgba(13, 77, 139, 0.08));
        }

        .bill-summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 24px 58px rgba(15, 39, 68, 0.14);
        }

        .bill-summary-label {
            position: relative;
            color: #66788f;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .bill-summary-value {
            position: relative;
            margin-top: 10px;
            color: #0b2545;
            font-size: 28px;
            font-weight: 950;
            letter-spacing: -0.8px;
        }

        .bill-summary-meta {
            position: relative;
            margin-top: 8px;
            color: #7c8da3;
            font-size: 13px;
            font-weight: 700;
        }

        .bills-header-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .bills-hero-icon {
            width: 80px;
            height: 80px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 80px;
            color: #fff;
            font-size: 34px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18), 0 18px 45px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(18px);
        }

        .bills-title:before,
        .bills-title:after {
            display: none;
        }

        .bills-header .bills-btn-primary {
            height: 64px;
            padding: 0 32px;
            border-radius: 20px;
            color: #0f4da5;
            font-size: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .bills-header .bills-btn-primary:hover,
        .bills-header .bills-btn-primary:focus {
            transform: translateY(-3px);
            box-shadow: 0 22px 48px rgba(0, 0, 0, 0.16);
        }

        .bills-header:before {
            width: 360px;
            height: 360px;
            right: -120px;
            top: -135px;
            background: rgba(255, 255, 255, 0.12);
            filter: blur(1px);
        }

        .bills-header:after {
            right: 33%;
            bottom: -115px;
            width: 260px;
            height: 260px;
            background: rgba(255, 255, 255, 0.08);
        }

        .bills-header {
            isolation: isolate;
        }

        .bills-header-content:after {
            content: "";
            position: absolute;
            inset: -40px -520px -40px -50px;
            z-index: -1;
            opacity: 0.18;
            background-image: radial-gradient(rgba(255,255,255,0.55) 1px, transparent 1px);
            background-size: 18px 18px;
            pointer-events: none;
        }

        .bills-card:not(.bill-form-panel) {
            background: rgba(255,255,255,0.9);
            border: 1px solid #e6eef8;
            border-radius: 28px;
            box-shadow: 0 15px 40px rgba(15,77,165,0.06);
            backdrop-filter: blur(20px);
        }

        .bills-filter-panel .search-wrap i {
            left: 26px;
            color: #2d8dff;
            font-size: 20px;
        }

        .bills-filter-panel input,
        .bills-filter-panel select {
            height: 70px;
            border-radius: 18px;
            background-color: #fafbfd;
            border-color: #e5ecf4;
            box-shadow: none;
        }

        .bills-filter-panel input::placeholder {
            color: #94a3b8;
        }

        .table-responsive {
            border-radius: 24px;
            border-color: #e7eef7;
            box-shadow: 0 10px 30px rgba(15,77,165,0.05);
        }

        .bills-table thead th {
            height: 70px;
            padding: 0 24px !important;
            color: #64748b;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            background: #f8fbff;
        }

        .bills-table tbody td {
            height: 100px;
            padding: 22px 24px !important;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .bill-vendor-cell {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .bill-vendor-icon {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 48px;
            color: #0f4da5;
            font-size: 18px;
            font-weight: 800;
            background: #eef5ff;
            border-radius: 50%;
        }

        .bills-table tbody tr.bill-row td:first-child strong {
            font-size: 18px;
            font-weight: 600;
        }

        .bill-amount {
            color: #071d3a;
            font-size: 18px;
            font-weight: 700;
        }

        .bill-status {
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: none;
        }

        .bill-status-paid {
            color: #16a34a;
            background: #eafbf0;
        }

        .bill-status-unpaid {
            color: #f97316;
            background: #fff4ea;
        }

        .bill-file-link {
            padding: 10px 14px;
            color: #0f4da5;
            background: #f5f9ff;
            border: 1px solid #d7e6ff;
            border-radius: 14px;
        }

        .bill-action-row {
            gap: 10px;
        }

        .bill-action-btn {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #0f4da5;
            background: #f7faff;
            border: 1px solid #e0eaf7;
            border-radius: 14px;
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }

        .bill-action-btn:hover {
            color: #0f4da5;
            text-decoration: none;
            transform: scale(1.05);
            box-shadow: 0 10px 24px rgba(15, 77, 165, 0.12);
        }

        .bill-action-delete {
            color: #dc2626;
            background: #fff1f2;
            border-color: #ffe0e3;
        }

        .bill-action-delete:hover {
            color: #dc2626;
        }

        .bill-summary-grid {
            gap: 24px;
            margin-top: 32px;
        }

        .bill-summary-card {
            min-height: 200px;
            padding: 30px;
            border-radius: 28px;
            border-color: #e7eef7;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        }

        .bill-summary-card:before {
            right: -48px;
            top: -48px;
            width: 170px;
            height: 170px;
            opacity: 0.55;
        }

        .bill-summary-icon {
            position: relative;
            width: 72px;
            height: 72px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 28px;
            border-radius: 22px;
        }

        .bill-summary-blue {
            color: #0f4da5;
            background: #eaf2ff;
        }

        .bill-summary-green {
            color: #16a34a;
            background: #eafbf0;
        }

        .bill-summary-orange {
            color: #f97316;
            background: #fff3ea;
        }

        .bill-summary-label {
            font-size: 14px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .bill-summary-value {
            margin-top: 12px;
            font-size: 48px;
            font-weight: 700;
            color: #071d3a;
            letter-spacing: -1.5px;
        }

        .bill-summary-meta {
            margin-top: 10px;
            font-size: 16px;
            font-weight: 500;
            color: #94a3b8;
        }

        /* Compact premium sizing so everything fits comfortably in the admin screen. */
        .bills-page {
            padding: 22px 0 32px;
        }

        .bills-page .container {
            width: 94%;
            max-width: 1320px;
            padding-left: 18px;
            padding-right: 18px;
        }

        .bills-header {
            min-height: 132px;
            margin-bottom: 22px;
            padding: 26px 30px;
            border-radius: 24px;
        }

        .bills-header-content {
            gap: 18px;
        }

        .bills-hero-icon {
            width: 62px;
            height: 62px;
            flex-basis: 62px;
            border-radius: 18px;
            font-size: 26px;
        }

        .bills-title {
            font-size: 34px;
            letter-spacing: -0.7px;
        }

        .bills-subtitle {
            max-width: 620px;
            margin-top: 8px;
            font-size: 16px;
            line-height: 1.45;
        }

        .bills-header .bills-btn-primary {
            height: 52px;
            padding: 0 24px;
            border-radius: 16px;
            font-size: 14px;
        }

        .bills-card:not(.bill-form-panel) {
            margin-bottom: 22px;
            border-radius: 24px;
        }

        .bills-card-body {
            padding: 22px;
        }

        .bills-filter-panel {
            grid-template-columns: minmax(320px, 1.45fr) minmax(180px, 0.62fr) minmax(230px, 0.78fr);
            gap: 16px;
            margin-bottom: 22px;
        }

        .bills-filter-panel input,
        .bills-filter-panel select,
        .bills-filter-panel:after {
            height: 54px;
            border-radius: 15px;
            font-size: 15px;
        }

        .bills-filter-panel input {
            padding-left: 48px;
        }

        .bills-filter-panel .search-wrap i {
            left: 18px;
            font-size: 16px;
        }

        .bills-filter-panel select {
            padding-left: 24px;
            padding-right: 42px;
            font-size: 14px;
        }

        .bills-filter-panel:after {
            padding: 0 20px;
            font-size: 15px;
        }

        .table-responsive {
            border-radius: 20px;
            overflow-x: auto;
        }

        .bills-table {
            min-width: 980px;
        }

        .bills-table thead th {
            height: 52px;
            padding: 0 16px !important;
            font-size: 12px;
            letter-spacing: 0.6px;
        }

        .bills-table tbody td {
            height: 72px;
            padding: 14px 16px !important;
            font-size: 14px;
        }

        .bill-vendor-cell {
            gap: 10px;
        }

        .bill-vendor-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
            font-size: 15px;
        }

        .bills-table tbody tr.bill-row td:first-child strong,
        .bill-amount {
            font-size: 15px;
        }

        .bill-status {
            padding: 6px 12px;
            font-size: 12px;
        }

        .bill-file-link {
            padding: 8px 12px;
            border-radius: 12px;
            font-size: 13px;
        }

        .bill-action-row {
            gap: 8px;
            flex-wrap: nowrap;
        }

        .bill-action-btn {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            border-radius: 12px;
            font-size: 14px;
        }

        .bill-summary-grid {
            gap: 18px;
            margin-top: 22px;
        }

        .bill-summary-card {
            min-height: 142px;
            padding: 20px;
            border-radius: 22px;
        }

        .bill-summary-icon {
            width: 48px;
            height: 48px;
            margin-bottom: 14px;
            border-radius: 16px;
            font-size: 20px;
        }

        .bill-summary-label {
            font-size: 12px;
        }

        .bill-summary-value {
            margin-top: 7px;
            font-size: 30px;
            letter-spacing: -0.7px;
        }

        .bill-summary-meta {
            margin-top: 6px;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .bills-page .container { width: 100%; }
            .bills-header, .bill-form-actions, .bill-action-row { align-items: stretch; flex-direction: column; }
            .bills-header { padding: 22px; border-radius: 20px; }
            .bills-title { font-size: 28px; }
            .bill-form-grid, .bill-summary-grid { grid-template-columns: 1fr; }
            .bills-btn { width: 100%; }
            .bills-card-body { padding: 18px; }
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset bills-page">
            <div class="container">
                <div class="bills-header">
                    <div class="bills-header-content">
                        <div class="bills-hero-icon"><i class="fa fa-file-text-o"></i></div>
                        <div>
                            <h1 class="bills-title">Bills Management</h1>
                            <p class="bills-subtitle">Track vendor bills, payments, attachments, and cash flow in one premium dashboard.</p>
                        </div>
                    </div>
                    <button type="button" class="bills-btn bills-btn-primary" id="toggleBillForm"><i class="fa fa-plus"></i> Add New Bill</button>
                </div>

                <?php if ($message !== '') { ?><div class="bills-alert bills-alert-success"><?php echo h($message); ?></div><?php } ?>
                <?php if ($errorMessage !== '') { ?><div class="bills-alert bills-alert-error"><?php echo h($errorMessage); ?></div><?php } ?>

                <div class="bills-card bill-form-panel" id="billFormPanel">
                    <div class="bills-card-body">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="bill_action" value="add">
                            <div class="bill-form-grid">
                                <div class="bill-form-group">
                                    <label>Add Vendor</label>
                                    <input type="text" name="vendor_name" placeholder="Enter vendor name" required>
                                </div>
                                <div class="bill-form-group">
                                    <label>Amount</label>
                                    <input type="number" name="amount" min="0" step="0.01" placeholder="0.00" required>
                                </div>
                                <div class="bill-form-group">
                                    <label>Payment Status</label>
                                    <select name="payment_status" required>
                                        <option value="Unpaid">Unpaid</option>
                                        <option value="Paid">Paid</option>
                                    </select>
                                </div>
                                <div class="bill-form-group">
                                    <label>Attach File</label>
                                    <input type="file" name="attached_file" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                                </div>
                                <div class="bill-form-group bill-form-full">
                                    <label>Description</label>
                                    <textarea name="description" placeholder="Add bill details"></textarea>
                                </div>
                            </div>
                            <div class="bill-form-actions">
                                <button type="button" class="bills-btn bills-btn-soft" id="cancelBillForm">Cancel</button>
                                <button type="submit" class="bills-btn bills-btn-primary">Save Bill</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bills-card">
                    <div class="bills-card-body">
                        <div class="bills-filter-panel">
                            <div class="search-wrap">
                                <i class="fa fa-search"></i>
                                <input type="text" id="billSearch" placeholder="Search bills by vendor name or description">
                            </div>
                            <select id="billStatusFilter">
                                <option value="All">All Status</option>
                                <option value="Paid">Paid</option>
                                <option value="Unpaid">Unpaid</option>
                            </select>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover bills-table" id="billsTable">
                                <thead>
                                    <tr>
                                        <th>Vendor name</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                        <th>Attached file</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bills as $bill) { ?>
                                        <tr class="bill-row" data-status="<?php echo h($bill['payment_status']); ?>" data-search="<?php echo h(strtolower($bill['vendor_name'] . ' ' . $bill['description'])); ?>">
                                            <td>
                                                <div class="bill-vendor-cell">
                                                    <span class="bill-vendor-icon"><?php echo strtoupper(substr($bill['vendor_name'], 0, 1)); ?></span>
                                                    <strong><?php echo h($bill['vendor_name']); ?></strong>
                                                </div>
                                            </td>
                                            <td><span class="bill-amount">Rs. <?php echo formatBillAmount($bill['amount']); ?></span></td>
                                            <td><span class="bill-status <?php echo $bill['payment_status'] === 'Paid' ? 'bill-status-paid' : 'bill-status-unpaid'; ?>"><?php echo h($bill['payment_status']); ?></span></td>
                                            <td><?php echo nl2br(h($bill['description'])); ?></td>
                                            <td>
                                                <?php if (!empty($bill['attached_file'])) { ?>
                                                    <a class="bill-file-link" href="<?php echo h($bill['attached_file']); ?>" target="_blank"><i class="fa fa-paperclip"></i> View</a>
                                                <?php } else { ?>
                                                    <span class="text-muted">No file</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="bill-action-row">
                                                    <a class="bill-action-btn bill-action-view" href="<?php echo !empty($bill['attached_file']) ? h($bill['attached_file']) : '#'; ?>" target="_blank" title="View"><i class="fa fa-eye"></i></a>
                                                    <button type="button" class="bill-action-btn bill-action-edit editBillToggle" data-target="editBill<?php echo (int) $bill['bill_id']; ?>" title="Edit"><i class="fa fa-pencil"></i></button>
                                                    <form method="post" class="bill-inline-form" onsubmit="return confirm('Delete this bill?');">
                                                        <input type="hidden" name="bill_action" value="delete">
                                                        <input type="hidden" name="bill_id" value="<?php echo (int) $bill['bill_id']; ?>">
                                                        <button type="submit" class="bill-action-btn bill-action-delete" title="Delete"><i class="fa fa-trash"></i></button>
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
                                                                <label>Add Vendor</label>
                                                                <input type="text" name="vendor_name" value="<?php echo h($bill['vendor_name']); ?>" required>
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Amount</label>
                                                                <input type="number" name="amount" min="0" step="0.01" value="<?php echo h($bill['amount']); ?>" required>
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Payment Status</label>
                                                                <select name="payment_status" required>
                                                                    <option value="Unpaid" <?php echo $bill['payment_status'] === 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                                                    <option value="Paid" <?php echo $bill['payment_status'] === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                                                </select>
                                                            </div>
                                                            <div class="bill-form-group">
                                                                <label>Attach File</label>
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

                <div class="bill-summary-grid">
                    <div class="bill-summary-card">
                        <div class="bill-summary-icon bill-summary-blue"><i class="fa fa-clipboard"></i></div>
                        <div class="bill-summary-label">Total Bills</div>
                        <div class="bill-summary-value"><?php echo (int) $summary['total_bills']; ?></div>
                        <div class="bill-summary-meta">Rs. <?php echo formatBillAmount($summary['total_amount']); ?> combined amount</div>
                    </div>
                    <div class="bill-summary-card">
                        <div class="bill-summary-icon bill-summary-green"><i class="fa fa-money"></i></div>
                        <div class="bill-summary-label">Paid Amount</div>
                        <div class="bill-summary-value">Rs. <?php echo formatBillAmount($summary['paid_amount']); ?></div>
                        <div class="bill-summary-meta">Sum of all paid bills</div>
                    </div>
                    <div class="bill-summary-card">
                        <div class="bill-summary-icon bill-summary-orange"><i class="fa fa-credit-card"></i></div>
                        <div class="bill-summary-label">Unpaid Amount</div>
                        <div class="bill-summary-value">Rs. <?php echo formatBillAmount($summary['unpaid_amount']); ?></div>
                        <div class="bill-summary-meta">Sum of all unpaid bills</div>
                    </div>
                </div>
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
                var editRow = $(this).next('.bill-edit-row');

                $(this).toggle(showRow);
                editRow.toggle(showRow && editRow.find('.bill-edit-panel').is(':visible'));

                if (showRow) {
                    visibleRows++;
                }
            });

            $('#billEmptyState').toggle(visibleRows === 0);
        }

        $('#toggleBillForm').click(function () {
            $('#billFormPanel').slideToggle(180);
        });

        $('#cancelBillForm').click(function () {
            $('#billFormPanel').slideUp(180);
        });

        $('#billSearch, #billStatusFilter').on('keyup change input', filterBills);

        $('.editBillToggle').click(function () {
            var target = $('#' + $(this).data('target'));
            $('.bill-edit-panel').not(target).slideUp(160).closest('.bill-edit-row').hide();
            target.closest('.bill-edit-row').show();
            target.slideToggle(160, filterBills);
        });

        $('.editBillCancel').click(function () {
            $('#' + $(this).data('target')).slideUp(160, filterBills);
        });

        $('.bill-edit-row').hide();
        filterBills();
    });
</script>

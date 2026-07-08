<?php
ob_start();
$pageIdentifier = "Suppliers";
include_once './dbConnect.php';
include_once __DIR__ . '/include/supplier_helpers.php';
date_default_timezone_set('Asia/Kolkata');

$errorMessage = '';
$noticeMessage = '';
$supplierTypes = array(
    'employee' => 'Employee',
    'contractor' => 'Contractor / Service Provider',
    'goods' => 'Goods Supplier'
);

function sh($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function supplier_initials($name)
{
    $name = trim((string) $name);
    if ($name === '') {
        return 'S';
    }
    $parts = preg_split('/\s+/', $name);
    $initials = '';
    foreach ($parts as $part) {
        if ($part !== '') {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        if (strlen($initials) >= 2) {
            break;
        }
    }
    return $initials !== '' ? $initials : 'S';
}

function supplier_avatar_color($seed)
{
    $palette = array('#5b21b6', '#0f766e', '#b45309', '#be123c', '#1d4ed8', '#7c3aed', '#0369a1', '#a21caf');
    $index = abs(crc32((string) $seed)) % count($palette);
    return $palette[$index];
}

if (!$db) {
    $errorMessage = 'Database connection is not available.';
} else {
    try {
        aitfEnsureSupplierSchema($db);
        aitfEnsureSupplierProfileSchema($db);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

/* ---------------------------------------------------------------------------
 * POST actions (PRG: process -> redirect). ob_start() keeps header() safe.
 * ------------------------------------------------------------------------- */
if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    try {
        if ($action === 'add_supplier') {
            $name = trim(isset($_POST['supplier_name']) ? $_POST['supplier_name'] : '');
            $type = isset($_POST['supplier_type']) ? $_POST['supplier_type'] : '';
            if ($name === '' || !isset($supplierTypes[$type])) {
                header('Location: suppliers.php?msg=invalid');
                exit;
            }
            $stmt = $db->prepare("INSERT INTO customer_info
                (customer_name, supplier_type, customer_category, verification_status, contact_person, contact, gstin_no, email_id)
                VALUES (:name, :type, 'supplier', 'pending', :person, :contact, :gstin, :email)");
            $stmt->execute(array(
                ':name' => $name,
                ':type' => $type,
                ':person' => trim(isset($_POST['contact_person']) ? $_POST['contact_person'] : ''),
                ':contact' => trim(isset($_POST['contact']) ? $_POST['contact'] : ''),
                ':gstin' => trim(isset($_POST['gstin_no']) ? $_POST['gstin_no'] : ''),
                ':email' => trim(isset($_POST['email_id']) ? $_POST['email_id'] : '')
            ));
            $newId = (int) $db->lastInsertId();
            header('Location: suppliers.php?open=' . $newId . '&action=verify&msg=added');
            exit;
        }

        if ($action === 'save_verification' || $action === 'update_profile') {
            $customerId = (int) (isset($_POST['customer_id']) ? $_POST['customer_id'] : 0);
            $type = isset($_POST['supplier_type']) ? $_POST['supplier_type'] : '';
            if ($customerId > 0 && isset($supplierTypes[$type])) {
                aitfSaveSupplierProfile($db, $customerId, $type, $_POST);
                $suffix = $action === 'save_verification' ? '&msg=verified' : '&msg=updated';
                header('Location: suppliers.php?tab=verified&open=' . $customerId . $suffix);
                exit;
            }
            header('Location: suppliers.php?msg=invalid');
            exit;
        }

        if ($action === 'add_bill') {
            $customerId = (int) (isset($_POST['customer_id']) ? $_POST['customer_id'] : 0);
            $nameStmt = $db->prepare("SELECT customer_name FROM customer_info WHERE id = :id");
            $nameStmt->execute(array(':id' => $customerId));
            $vendorName = (string) $nameStmt->fetchColumn();
            if ($customerId > 0 && $vendorName !== '') {
                $uploadDir = 'uploads/bills/';
                $attachErr = '';
                $attached = aitfSaveUploadedFile('attached_file', $uploadDir, array('jpg', 'jpeg', 'png', 'pdf'), array('image/jpeg', 'image/png', 'application/pdf'), $attachErr);
                if ($attached === false) {
                    $attached = '';
                }
                $amount = (float) (isset($_POST['amount']) ? $_POST['amount'] : 0);
                $billStmt = $db->prepare("INSERT INTO bills
                    (customer_id, vendor_name, bill_number, bill_date, amount, due_date, paid_amount, payment_status, description, attached_file)
                    VALUES (:customer_id, :vendor_name, :bill_number, :bill_date, :amount, :due_date, 0.00, 'Unpaid', :description, :attached_file)");
                $billStmt->execute(array(
                    ':customer_id' => $customerId,
                    ':vendor_name' => $vendorName,
                    ':bill_number' => trim(isset($_POST['bill_number']) ? $_POST['bill_number'] : ''),
                    ':bill_date' => (isset($_POST['bill_date']) && $_POST['bill_date'] !== '') ? $_POST['bill_date'] : null,
                    ':amount' => $amount,
                    ':due_date' => (isset($_POST['due_date']) && $_POST['due_date'] !== '') ? $_POST['due_date'] : null,
                    ':description' => trim(isset($_POST['description']) ? $_POST['description'] : ''),
                    ':attached_file' => $attached
                ));
            }
            header('Location: suppliers.php?tab=verified&open=' . $customerId . '&msg=bill_added');
            exit;
        }

        if ($action === 'bill_mark_paid' || $action === 'bill_delete') {
            $customerId = (int) (isset($_POST['customer_id']) ? $_POST['customer_id'] : 0);
            $billId = (int) (isset($_POST['bill_id']) ? $_POST['bill_id'] : 0);
            if ($billId > 0) {
                if ($action === 'bill_mark_paid') {
                    $u = $db->prepare("UPDATE bills SET paid_amount = amount, payment_status = 'Paid' WHERE bill_id = :bid AND customer_id = :cid");
                    $u->execute(array(':bid' => $billId, ':cid' => $customerId));
                } else {
                    $d = $db->prepare("DELETE FROM bills WHERE bill_id = :bid AND customer_id = :cid");
                    $d->execute(array(':bid' => $billId, ':cid' => $customerId));
                }
            }
            header('Location: suppliers.php?tab=verified&open=' . $customerId . '&msg=bill_updated');
            exit;
        }
    } catch (Exception $e) {
        error_log('Supplier action error: ' . $e->getMessage());
        header('Location: suppliers.php?msg=error');
        exit;
    }
}

/* ---------------------------------------------------------------------------
 * GET: filters, lists, and (optional) opened supplier.
 * ------------------------------------------------------------------------- */
$search = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
$filterType = isset($_GET['filter_type']) ? trim((string) $_GET['filter_type']) : 'all';
$activeTab = (isset($_GET['tab']) && $_GET['tab'] === 'verified') ? 'verified' : 'pending';
$openId = isset($_GET['open']) ? (int) $_GET['open'] : 0;
$openAction = isset($_GET['action']) ? $_GET['action'] : '';
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$pendingSuppliers = array();
$verifiedSuppliers = array();
$summary = array('total' => 0, 'pending' => 0, 'verified' => 0, 'employee' => 0, 'contractor' => 0, 'goods' => 0);
$openProfile = null;
$openBills = array();
$profileFields = aitfSupplierProfileFields();

if ($db && $errorMessage === '') {
    try {
        $pendingSuppliers = aitfFetchSuppliers($db, 'pending', $filterType, $search);
        $verifiedSuppliers = aitfFetchSuppliers($db, 'verified', $filterType, $search);

        $summaryStmt = $db->query("SELECT supplier_type, COALESCE(verification_status,'pending') AS vs, COUNT(*) AS c
            FROM customer_info
            WHERE customer_category = 'supplier' AND (is_deleted IS NULL OR is_deleted = 'NO')
            GROUP BY supplier_type, vs");
        foreach ($summaryStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $c = (int) $row['c'];
            $summary['total'] += $c;
            if ($row['vs'] === 'verified') {
                $summary['verified'] += $c;
            } else {
                $summary['pending'] += $c;
            }
            if (isset($summary[$row['supplier_type']])) {
                $summary[$row['supplier_type']] += $c;
            }
        }

        if ($openId > 0) {
            $openProfile = aitfGetSupplierProfile($db, $openId);
            if ($openProfile) {
                $activeTab = ($openProfile['verification_status'] === 'verified' && $openAction !== 'verify') ? 'verified' : $activeTab;
                $billStmt = $db->prepare("SELECT * FROM bills WHERE customer_id = :cid ORDER BY bill_id DESC");
                $billStmt->execute(array(':cid' => $openId));
                $openBills = $billStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $errorMessage = 'Unable to load suppliers: ' . $e->getMessage();
    }
}

$noticeMap = array(
    'added' => 'Supplier added. Complete the verification form to move it to Verified.',
    'verified' => 'Supplier verified successfully.',
    'updated' => 'Supplier profile updated.',
    'bill_added' => 'Bill added.',
    'bill_updated' => 'Bill updated.',
    'invalid' => 'Please fill the required fields.',
    'error' => 'Something went wrong. Please try again.'
);
if (isset($noticeMap[$msg])) {
    if ($msg === 'invalid' || $msg === 'error') {
        $errorMessage = $noticeMap[$msg];
    } else {
        $noticeMessage = $noticeMap[$msg];
    }
}

// Whether to open the verification form vs the profile view for the opened supplier.
$openIsVerify = false;
$openIsProfile = false;
if ($openProfile) {
    if ($openAction === 'verify' || $openProfile['verification_status'] !== 'verified') {
        $openIsVerify = true;
    } else {
        $openIsProfile = true;
    }
}

/**
 * Render a single editable form field from a field config.
 */
function render_supplier_field($field, $value)
{
    $key = $field['key'];
    $label = $field['label'];
    $type = isset($field['type']) ? $field['type'] : 'text';
    echo '<div class="sp-field">';
    echo '<label for="f_' . sh($key) . '">' . sh($label) . '</label>';
    if ($type === 'textarea') {
        echo '<textarea id="f_' . sh($key) . '" name="' . sh($key) . '" rows="2" class="sp-input">' . sh($value) . '</textarea>';
    } elseif ($type === 'select') {
        echo '<select id="f_' . sh($key) . '" name="' . sh($key) . '" class="sp-input">';
        echo '<option value="">-- Select --</option>';
        foreach ($field['options'] as $ov => $ol) {
            $sel = ((string) $value === (string) $ov) ? ' selected' : '';
            echo '<option value="' . sh($ov) . '"' . $sel . '>' . sh($ol) . '</option>';
        }
        echo '</select>';
    } else {
        $inputType = ($type === 'date') ? 'date' : (($type === 'number') ? 'number' : 'text');
        $step = ($type === 'number') ? ' step="any"' : '';
        echo '<input type="' . $inputType . '"' . $step . ' id="f_' . sh($key) . '" name="' . sh($key) . '" value="' . sh($value) . '" class="sp-input">';
    }
    echo '</div>';
}

/**
 * Render a read-only value row.
 */
function render_profile_value($field, $value)
{
    $type = isset($field['type']) ? $field['type'] : 'text';
    $display = trim((string) $value);
    if ($type === 'select' && isset($field['options'][$value])) {
        $display = $field['options'][$value];
    }
    if ($display === '') {
        $display = '—';
    }
    echo '<div class="sp-kv"><span class="sp-k">' . sh($field['label']) . '</span><span class="sp-v">' . sh($display) . '</span></div>';
}
?>
<div id="wrapper" class="active">
<?php include 'header.php'; ?>

<style>
    :root {
        --sp-primary: #5b21b6;
        --sp-primary-2: #7c3aed;
        --sp-ink: #0f172a;
        --sp-muted: #64748b;
        --sp-line: #e2e8f0;
        --sp-bg: #f4f5fb;
    }

    .supplier-page { padding: 26px 0 60px; background: var(--sp-bg); min-height: calc(100vh - 72px); }
    .supplier-page .container { width: 95%; max-width: 1320px; }

    .sp-hero {
        background: linear-gradient(120deg, #5b21b6, #7c3aed 60%, #9333ea);
        color: #fff; border-radius: 16px; padding: 24px 26px; margin-bottom: 20px;
        display: flex; align-items: center; justify-content: space-between; gap: 18px;
        box-shadow: 0 18px 40px rgba(91, 33, 182, .22);
    }
    .sp-hero h1 { margin: 0; font-size: 26px; font-weight: 900; letter-spacing: .2px; }
    .sp-hero p { margin: 6px 0 0; color: #ede9fe; font-size: 13px; }
    .sp-hero .sp-hero-actions { display: flex; gap: 10px; align-items: center; }

    .sp-summary { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 20px; }
    .sp-stat { background: #fff; border: 1px solid var(--sp-line); border-radius: 14px; padding: 15px 16px; box-shadow: 0 10px 24px rgba(15, 23, 42, .04); }
    .sp-stat .l { color: var(--sp-muted); font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
    .sp-stat .v { margin-top: 6px; color: var(--sp-ink); font-size: 26px; font-weight: 900; }
    .sp-stat.accent { background: linear-gradient(135deg, #f5f3ff, #ffffff); }

    .sp-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
    .sp-tabs { display: inline-flex; background: #ece9f6; padding: 5px; border-radius: 12px; gap: 4px; }
    .sp-tab { border: 0; background: transparent; color: #4c1d95; font-weight: 800; font-size: 13px; padding: 9px 18px; border-radius: 9px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
    .sp-tab .cnt { background: rgba(124, 58, 237, .16); color: #6d28d9; border-radius: 999px; font-size: 11px; padding: 1px 8px; font-weight: 900; }
    .sp-tab.active { background: #fff; color: var(--sp-primary); box-shadow: 0 6px 16px rgba(15, 23, 42, .08); }

    .sp-filter { display: flex; gap: 8px; flex-wrap: wrap; }
    .sp-input, .sp-select { min-height: 40px; border: 1px solid #cbd5e1; border-radius: 10px; padding: 9px 12px; background: #fff; color: var(--sp-ink); font-size: 13px; width: 100%; }
    .sp-filter .sp-input { min-width: 240px; width: auto; }
    .sp-filter .sp-select { min-width: 190px; width: auto; }

    .sp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 0; border-radius: 10px; min-height: 40px; padding: 9px 16px; font-weight: 800; font-size: 13px; text-decoration: none; cursor: pointer; white-space: nowrap; transition: transform .06s ease, box-shadow .15s ease; }
    .sp-btn:active { transform: translateY(1px); }
    .sp-btn-primary { background: #fff; color: var(--sp-primary) !important; }
    .sp-btn-solid { background: var(--sp-primary); color: #fff !important; box-shadow: 0 10px 22px rgba(91, 33, 182, .28); }
    .sp-btn-soft { background: #ede9fe; color: #4c1d95 !important; }
    .sp-btn-ghost { background: #f1f5f9; color: #334155 !important; }
    .sp-btn-success { background: #059669; color: #fff !important; }
    .sp-btn-danger { background: #e11d48; color: #fff !important; }
    .sp-btn-sm { min-height: 32px; padding: 5px 11px; font-size: 12px; border-radius: 8px; }

    .sp-alert { padding: 12px 15px; border-radius: 11px; margin-bottom: 16px; font-weight: 700; font-size: 13px; }
    .sp-alert-error { background: #fee2e2; color: #991b1b; }
    .sp-alert-ok { background: #dcfce7; color: #166534; }

    .sp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }
    .sp-card { background: #fff; border: 1px solid var(--sp-line); border-radius: 14px; padding: 16px; box-shadow: 0 10px 26px rgba(15, 23, 42, .05); display: flex; flex-direction: column; gap: 12px; transition: box-shadow .18s ease, transform .18s ease; }
    .sp-card:hover { box-shadow: 0 18px 38px rgba(15, 23, 42, .12); transform: translateY(-2px); }
    .sp-card-top { display: flex; gap: 12px; align-items: center; }
    .sp-avatar { width: 46px; height: 46px; border-radius: 13px; color: #fff; font-weight: 900; font-size: 16px; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; cursor: pointer; box-shadow: 0 6px 14px rgba(15, 23, 42, .16); }
    .sp-avatar.lg { width: 64px; height: 64px; border-radius: 18px; font-size: 22px; }
    .sp-card-name { font-weight: 900; color: var(--sp-ink); font-size: 15px; line-height: 1.25; cursor: pointer; }
    .sp-card-name:hover { color: var(--sp-primary); }
    .sp-meta { color: var(--sp-muted); font-size: 12px; margin-top: 2px; }
    .sp-badge { display: inline-flex; align-items: center; gap: 5px; border-radius: 999px; font-size: 11px; font-weight: 800; padding: 3px 10px; }
    .sp-badge-type { background: #ede9fe; color: #5b21b6; }
    .sp-badge-pending { background: #fef3c7; color: #92400e; }
    .sp-badge-verified { background: #dcfce7; color: #166534; }
    .sp-card-foot { display: flex; gap: 8px; align-items: center; justify-content: space-between; border-top: 1px dashed var(--sp-line); padding-top: 11px; }

    .sp-empty { grid-column: 1 / -1; padding: 44px; text-align: center; color: var(--sp-muted); background: #fff; border: 1px dashed #cbd5e1; border-radius: 14px; }

    /* Slide-in drawer */
    .sp-drawer-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, .48); z-index: 1050; opacity: 0; visibility: hidden; transition: opacity .25s ease; }
    .sp-drawer-overlay.open { opacity: 1; visibility: visible; }
    .sp-drawer { position: fixed; top: 0; right: 0; height: 100%; width: 560px; max-width: 94vw; background: #f8fafc; z-index: 1060; box-shadow: -20px 0 60px rgba(15, 23, 42, .28); transform: translateX(100%); transition: transform .32s cubic-bezier(.16, 1, .3, 1); display: flex; flex-direction: column; }
    .sp-drawer.open { transform: translateX(0); }
    .sp-drawer-head { background: linear-gradient(120deg, #5b21b6, #7c3aed); color: #fff; padding: 20px 22px; display: flex; align-items: center; gap: 14px; }
    .sp-drawer-head .who { flex: 1; min-width: 0; }
    .sp-drawer-head .who h3 { margin: 0; font-size: 19px; font-weight: 900; }
    .sp-drawer-head .who span { color: #ede9fe; font-size: 12px; }
    .sp-drawer-close { background: rgba(255, 255, 255, .2); border: 0; color: #fff; width: 34px; height: 34px; border-radius: 9px; font-size: 18px; cursor: pointer; }
    .sp-drawer-body { padding: 20px 22px; overflow-y: auto; flex: 1; }

    .sp-section { background: #fff; border: 1px solid var(--sp-line); border-radius: 13px; padding: 16px 17px; margin-bottom: 14px; }
    .sp-section h4 { margin: 0 0 12px; font-size: 13px; font-weight: 900; color: var(--sp-primary); text-transform: uppercase; letter-spacing: .04em; }
    .sp-kv { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
    .sp-kv:last-child { border-bottom: 0; }
    .sp-k { color: var(--sp-muted); font-weight: 700; }
    .sp-v { color: var(--sp-ink); font-weight: 700; text-align: right; word-break: break-word; }

    .sp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .sp-field { display: flex; flex-direction: column; gap: 5px; }
    .sp-field.full { grid-column: 1 / -1; }
    .sp-field label { font-size: 11.5px; font-weight: 800; color: #475569; }

    .sp-bill-row { display: grid; grid-template-columns: 1.4fr 1fr 1fr auto; gap: 8px; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
    .sp-bill-row .bn { font-weight: 800; color: var(--sp-ink); }
    .sp-pill { display: inline-block; border-radius: 999px; font-size: 10.5px; font-weight: 900; padding: 2px 9px; }
    .sp-pill.paid { background: #dcfce7; color: #166534; }
    .sp-pill.unpaid { background: #fee2e2; color: #991b1b; }
    .sp-pill.partial { background: #fef3c7; color: #92400e; }

    /* Modal */
    .sp-modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, .5); z-index: 1055; display: none; align-items: center; justify-content: center; padding: 20px; }
    .sp-modal-overlay.open { display: flex; }
    .sp-modal { background: #fff; border-radius: 16px; width: 460px; max-width: 100%; box-shadow: 0 30px 70px rgba(15, 23, 42, .3); overflow: hidden; }
    .sp-modal-head { background: linear-gradient(120deg, #5b21b6, #7c3aed); color: #fff; padding: 18px 20px; font-weight: 900; font-size: 17px; display: flex; align-items: center; justify-content: space-between; }
    .sp-modal-body { padding: 20px; }
    .sp-modal .sp-field { margin-bottom: 12px; }

    html.theme-dark .supplier-page { background: #0b1220; }
    html.theme-dark .sp-stat, html.theme-dark .sp-card, html.theme-dark .sp-section, html.theme-dark .sp-modal, html.theme-dark .sp-drawer { background: #111827; border-color: #1f2937; }
    html.theme-dark .sp-stat .v, html.theme-dark .sp-card-name, html.theme-dark .sp-v { color: #e5e7eb; }
    html.theme-dark .sp-drawer-body, html.theme-dark .supplier-page { color: #cbd5e1; }
    html.theme-dark .sp-input, html.theme-dark .sp-select { background: #0b1220; border-color: #334155; color: #e5e7eb; }
    html.theme-dark .sp-empty { background: #111827; border-color: #334155; }

    @media (max-width: 1100px) { .sp-summary { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 720px) {
        .sp-summary { grid-template-columns: repeat(2, 1fr); }
        .sp-hero { flex-direction: column; align-items: flex-start; }
        .sp-form-grid { grid-template-columns: 1fr; }
        .sp-bill-row { grid-template-columns: 1fr 1fr; }
    }
</style>

<div id="page-content-wrapper">
    <div class="page-content inset supplier-page">
        <div class="container">

            <div class="sp-hero">
                <div>
                    <h1><i class="fa fa-truck"></i> Suppliers</h1>
                    <p>Add suppliers, complete type-based verification, then manage each supplier's profile & bills.</p>
                </div>
                <div class="sp-hero-actions">
                    <span class="sp-badge" style="background: rgba(255,255,255,.18); color:#fff; padding:7px 12px;"><?php echo (int) $summary['total']; ?> Total</span>
                    <button type="button" class="sp-btn sp-btn-primary" onclick="spOpenAdd()"><i class="fa fa-plus"></i> Add Supplier</button>
                </div>
            </div>

            <?php if ($errorMessage !== '') { ?>
                <div class="sp-alert sp-alert-error"><?php echo sh($errorMessage); ?></div>
            <?php } ?>
            <?php if ($noticeMessage !== '') { ?>
                <div class="sp-alert sp-alert-ok"><?php echo sh($noticeMessage); ?></div>
            <?php } ?>

            <div class="sp-summary">
                <div class="sp-stat accent"><div class="l">Total</div><div class="v"><?php echo (int) $summary['total']; ?></div></div>
                <div class="sp-stat"><div class="l">Pending</div><div class="v"><?php echo (int) $summary['pending']; ?></div></div>
                <div class="sp-stat"><div class="l">Verified</div><div class="v"><?php echo (int) $summary['verified']; ?></div></div>
                <div class="sp-stat"><div class="l">Employees</div><div class="v"><?php echo (int) $summary['employee']; ?></div></div>
                <div class="sp-stat"><div class="l">Contractors</div><div class="v"><?php echo (int) $summary['contractor']; ?></div></div>
                <div class="sp-stat"><div class="l">Goods</div><div class="v"><?php echo (int) $summary['goods']; ?></div></div>
            </div>

            <div class="sp-toolbar">
                <div class="sp-tabs">
                    <button type="button" class="sp-tab <?php echo $activeTab === 'pending' ? 'active' : ''; ?>" data-tab="pending">
                        <i class="fa fa-hourglass-half"></i> Pending <span class="cnt"><?php echo count($pendingSuppliers); ?></span>
                    </button>
                    <button type="button" class="sp-tab <?php echo $activeTab === 'verified' ? 'active' : ''; ?>" data-tab="verified">
                        <i class="fa fa-check-circle"></i> Verified <span class="cnt"><?php echo count($verifiedSuppliers); ?></span>
                    </button>
                </div>
                <form method="get" class="sp-filter">
                    <input type="hidden" name="tab" id="filterTabField" value="<?php echo sh($activeTab); ?>">
                    <input class="sp-input" name="search" value="<?php echo sh($search); ?>" placeholder="Search name, GSTIN, contact, email">
                    <select class="sp-select" name="filter_type">
                        <option value="all">All Types</option>
                        <?php foreach ($supplierTypes as $value => $label) { ?>
                            <option value="<?php echo sh($value); ?>" <?php echo $filterType === $value ? 'selected' : ''; ?>><?php echo sh($label); ?></option>
                        <?php } ?>
                    </select>
                    <button class="sp-btn sp-btn-solid" type="submit"><i class="fa fa-search"></i> Search</button>
                </form>
            </div>

            <!-- PENDING TAB -->
            <div class="sp-tab-pane" data-pane="pending" style="<?php echo $activeTab === 'pending' ? '' : 'display:none;'; ?>">
                <div class="sp-grid">
                    <?php if (empty($pendingSuppliers)) { ?>
                        <div class="sp-empty"><i class="fa fa-inbox" style="font-size:26px; display:block; margin-bottom:8px;"></i>No pending suppliers. Click <strong>Add Supplier</strong> to create one.</div>
                    <?php } ?>
                    <?php foreach ($pendingSuppliers as $s) {
                        $typeLabel = isset($supplierTypes[$s['supplier_type']]) ? $supplierTypes[$s['supplier_type']] : 'Supplier';
                    ?>
                        <div class="sp-card">
                            <div class="sp-card-top">
                                <div class="sp-avatar" style="background: <?php echo supplier_avatar_color($s['id']); ?>;"><?php echo sh(supplier_initials($s['customer_name'])); ?></div>
                                <div style="min-width:0;">
                                    <div class="sp-card-name"><?php echo sh($s['customer_name']); ?></div>
                                    <div class="sp-meta"><span class="sp-badge sp-badge-type"><?php echo sh($typeLabel); ?></span></div>
                                </div>
                            </div>
                            <div class="sp-card-foot">
                                <span class="sp-badge sp-badge-pending"><i class="fa fa-hourglass-half"></i> Verification Pending</span>
                                <a class="sp-btn sp-btn-solid sp-btn-sm" href="suppliers.php?open=<?php echo (int) $s['id']; ?>&action=verify">Continue to Verification <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- VERIFIED TAB -->
            <div class="sp-tab-pane" data-pane="verified" style="<?php echo $activeTab === 'verified' ? '' : 'display:none;'; ?>">
                <div class="sp-grid">
                    <?php if (empty($verifiedSuppliers)) { ?>
                        <div class="sp-empty"><i class="fa fa-check-circle" style="font-size:26px; display:block; margin-bottom:8px;"></i>No verified suppliers yet.</div>
                    <?php } ?>
                    <?php foreach ($verifiedSuppliers as $s) {
                        $typeLabel = isset($supplierTypes[$s['supplier_type']]) ? $supplierTypes[$s['supplier_type']] : 'Supplier';
                        $openUrl = 'suppliers.php?tab=verified&open=' . (int) $s['id'];
                    ?>
                        <div class="sp-card">
                            <div class="sp-card-top">
                                <div class="sp-avatar" style="background: <?php echo supplier_avatar_color($s['id']); ?>;" onclick="location.href='<?php echo $openUrl; ?>'" title="Open profile"><?php echo sh(supplier_initials($s['customer_name'])); ?></div>
                                <div style="min-width:0;">
                                    <div class="sp-card-name" onclick="location.href='<?php echo $openUrl; ?>'"><?php echo sh($s['customer_name']); ?></div>
                                    <div class="sp-meta"><span class="sp-badge sp-badge-type"><?php echo sh($typeLabel); ?></span></div>
                                </div>
                            </div>
                            <div class="sp-card-foot">
                                <span class="sp-badge sp-badge-verified"><i class="fa fa-check-circle"></i> Verified</span>
                                <a class="sp-btn sp-btn-soft sp-btn-sm" href="<?php echo $openUrl; ?>"><i class="fa fa-id-card-o"></i> Open Profile</a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ADD SUPPLIER MODAL -->
<div class="sp-modal-overlay" id="addSupplierModal">
    <div class="sp-modal">
        <div class="sp-modal-head">Add Supplier <button type="button" class="sp-drawer-close" onclick="spCloseAdd()">&times;</button></div>
        <div class="sp-modal-body">
            <form method="post" action="suppliers.php">
                <input type="hidden" name="action" value="add_supplier">
                <div class="sp-field"><label>Supplier Name *</label><input class="sp-input" name="supplier_name" required></div>
                <div class="sp-field"><label>Supplier Type *</label>
                    <select class="sp-input" name="supplier_type" required>
                        <option value="">-- Select Type --</option>
                        <?php foreach ($supplierTypes as $value => $label) { ?>
                            <option value="<?php echo sh($value); ?>"><?php echo sh($label); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="sp-field"><label>Contact Person</label><input class="sp-input" name="contact_person"></div>
                <div class="sp-field"><label>Contact No.</label><input class="sp-input" name="contact"></div>
                <div class="sp-field"><label>GSTIN</label><input class="sp-input" name="gstin_no"></div>
                <div class="sp-field"><label>Email</label><input class="sp-input" name="email_id"></div>
                <div style="display:flex; gap:10px; margin-top:8px;">
                    <button type="submit" class="sp-btn sp-btn-solid" style="flex:1;"><i class="fa fa-check"></i> Add & Continue</button>
                    <button type="button" class="sp-btn sp-btn-ghost" onclick="spCloseAdd()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($openProfile) {
    $p = $openProfile;
    $typeLabel = isset($supplierTypes[$p['supplier_type']]) ? $supplierTypes[$p['supplier_type']] : 'Supplier';
    $sections = aitfSupplierSectionsForType($p['supplier_type']);
?>
<!-- SUPPLIER DRAWER (verify form OR profile) -->
<div class="sp-drawer-overlay" id="drawerOverlay" onclick="spCloseDrawer()"></div>
<div class="sp-drawer" id="supplierDrawer">
    <div class="sp-drawer-head">
        <div class="sp-avatar lg" style="background: rgba(255,255,255,.22);"><?php echo sh(supplier_initials($p['customer_name'])); ?></div>
        <div class="who">
            <h3><?php echo sh($p['customer_name']); ?></h3>
            <span><?php echo sh($typeLabel); ?> &nbsp;•&nbsp; <?php echo $p['verification_status'] === 'verified' ? 'Verified' : 'Pending verification'; ?></span>
        </div>
        <button type="button" class="sp-drawer-close" onclick="spCloseDrawer()">&times;</button>
    </div>
    <div class="sp-drawer-body">

        <?php if ($openIsVerify) { ?>
            <!-- VERIFICATION FORM -->
            <form method="post" action="suppliers.php">
                <input type="hidden" name="action" value="save_verification">
                <input type="hidden" name="customer_id" value="<?php echo (int) $p['id']; ?>">
                <input type="hidden" name="supplier_type" value="<?php echo sh($p['supplier_type']); ?>">
                <?php foreach ($sections as $sectionKey) {
                    $section = $profileFields[$sectionKey]; ?>
                    <div class="sp-section">
                        <h4><?php echo sh($section['title']); ?></h4>
                        <div class="sp-form-grid">
                            <?php foreach ($section['fields'] as $field) {
                                $isFull = in_array($field['type'], array('textarea'), true);
                                echo '<div class="' . ($isFull ? 'sp-field full' : 'sp-field-wrap') . '">';
                                $val = isset($p[$field['key']]) ? $p[$field['key']] : '';
                                render_supplier_field($field, $val);
                                echo '</div>';
                            } ?>
                        </div>
                    </div>
                <?php } ?>
                <div style="display:flex; gap:10px; position:sticky; bottom:0;">
                    <button type="submit" class="sp-btn sp-btn-success" style="flex:1;"><i class="fa fa-check"></i> Submit & Mark Verified</button>
                    <a href="suppliers.php" class="sp-btn sp-btn-ghost">Cancel</a>
                </div>
            </form>

        <?php } else { ?>
            <!-- PROFILE VIEW + EDIT -->
            <div style="display:flex; justify-content:flex-end; margin-bottom:10px;">
                <button type="button" class="sp-btn sp-btn-soft sp-btn-sm" id="editToggleBtn" onclick="spToggleEdit()"><i class="fa fa-pencil"></i> Edit Details</button>
            </div>

            <div id="profileReadView">
                <?php foreach ($sections as $sectionKey) {
                    $section = $profileFields[$sectionKey]; ?>
                    <div class="sp-section">
                        <h4><?php echo sh($section['title']); ?></h4>
                        <?php foreach ($section['fields'] as $field) {
                            render_profile_value($field, isset($p[$field['key']]) ? $p[$field['key']] : '');
                        } ?>
                    </div>
                <?php } ?>
            </div>

            <div id="profileEditView" style="display:none;">
                <form method="post" action="suppliers.php">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="customer_id" value="<?php echo (int) $p['id']; ?>">
                    <input type="hidden" name="supplier_type" value="<?php echo sh($p['supplier_type']); ?>">
                    <?php foreach ($sections as $sectionKey) {
                        $section = $profileFields[$sectionKey]; ?>
                        <div class="sp-section">
                            <h4><?php echo sh($section['title']); ?></h4>
                            <div class="sp-form-grid">
                                <?php foreach ($section['fields'] as $field) {
                                    $isFull = in_array($field['type'], array('textarea'), true);
                                    echo '<div class="' . ($isFull ? 'sp-field full' : 'sp-field-wrap') . '">';
                                    render_supplier_field($field, isset($p[$field['key']]) ? $p[$field['key']] : '');
                                    echo '</div>';
                                } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="sp-btn sp-btn-success" style="flex:1;"><i class="fa fa-save"></i> Save Changes</button>
                        <button type="button" class="sp-btn sp-btn-ghost" onclick="spToggleEdit()">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- BILLS -->
            <div class="sp-section">
                <h4><i class="fa fa-file-text-o"></i> Bills</h4>
                <?php if (empty($openBills)) { ?>
                    <div style="color:var(--sp-muted); font-size:13px; padding:6px 0;">No bills yet for this supplier.</div>
                <?php } else {
                    foreach ($openBills as $bill) {
                        $status = $bill['payment_status'];
                        $pill = $status === 'Paid' ? 'paid' : ($status === 'Partially Paid' ? 'partial' : 'unpaid');
                    ?>
                        <div class="sp-bill-row">
                            <div>
                                <div class="bn"><?php echo sh($bill['bill_number'] ?: ('#' . $bill['bill_id'])); ?></div>
                                <div class="sp-meta"><?php echo $bill['bill_date'] ? sh(date('d M Y', strtotime($bill['bill_date']))) : '—'; ?></div>
                            </div>
                            <div>&#8377; <?php echo number_format((float) $bill['amount'], 2); ?></div>
                            <div><span class="sp-pill <?php echo $pill; ?>"><?php echo sh($status); ?></span></div>
                            <div style="display:flex; gap:5px; justify-content:flex-end;">
                                <?php if (!empty($bill['attached_file'])) { ?>
                                    <a class="sp-btn sp-btn-ghost sp-btn-sm" href="<?php echo sh($bill['attached_file']); ?>" target="_blank" title="View attachment"><i class="fa fa-paperclip"></i></a>
                                <?php } ?>
                                <?php if ($status !== 'Paid') { ?>
                                    <form method="post" action="suppliers.php" style="display:inline;">
                                        <input type="hidden" name="action" value="bill_mark_paid">
                                        <input type="hidden" name="customer_id" value="<?php echo (int) $p['id']; ?>">
                                        <input type="hidden" name="bill_id" value="<?php echo (int) $bill['bill_id']; ?>">
                                        <button type="submit" class="sp-btn sp-btn-success sp-btn-sm" title="Mark paid"><i class="fa fa-check"></i></button>
                                    </form>
                                <?php } ?>
                                <form method="post" action="suppliers.php" style="display:inline;" onsubmit="return confirm('Delete this bill?');">
                                    <input type="hidden" name="action" value="bill_delete">
                                    <input type="hidden" name="customer_id" value="<?php echo (int) $p['id']; ?>">
                                    <input type="hidden" name="bill_id" value="<?php echo (int) $bill['bill_id']; ?>">
                                    <button type="submit" class="sp-btn sp-btn-danger sp-btn-sm" title="Delete"><i class="fa fa-trash-o"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php }
                } ?>

                <div style="margin-top:14px; border-top:1px dashed var(--sp-line); padding-top:14px;">
                    <button type="button" class="sp-btn sp-btn-soft sp-btn-sm" onclick="spToggleBillForm()"><i class="fa fa-plus"></i> Add Bill</button>
                    <form method="post" action="suppliers.php" enctype="multipart/form-data" id="addBillForm" style="display:none; margin-top:12px;">
                        <input type="hidden" name="action" value="add_bill">
                        <input type="hidden" name="customer_id" value="<?php echo (int) $p['id']; ?>">
                        <div class="sp-form-grid">
                            <div class="sp-field"><label>Bill Number</label><input class="sp-input" name="bill_number"></div>
                            <div class="sp-field"><label>Bill Date</label><input type="date" class="sp-input" name="bill_date"></div>
                            <div class="sp-field"><label>Amount</label><input type="number" step="any" class="sp-input" name="amount"></div>
                            <div class="sp-field"><label>Due Date</label><input type="date" class="sp-input" name="due_date"></div>
                            <div class="sp-field full"><label>Description</label><input class="sp-input" name="description"></div>
                            <div class="sp-field full"><label>Attachment (JPG/PNG/PDF)</label><input type="file" class="sp-input" name="attached_file" accept=".jpg,.jpeg,.png,.pdf"></div>
                        </div>
                        <button type="submit" class="sp-btn sp-btn-success sp-btn-sm" style="margin-top:10px;"><i class="fa fa-check"></i> Save Bill</button>
                    </form>
                </div>
            </div>
        <?php } ?>

    </div>
</div>
<?php } ?>

</div>

<script>
    function spOpenAdd() { document.getElementById('addSupplierModal').classList.add('open'); }
    function spCloseAdd() { document.getElementById('addSupplierModal').classList.remove('open'); }
    function spCloseDrawer() { window.location.href = 'suppliers.php?tab=<?php echo sh($activeTab); ?>'; }
    function spToggleEdit() {
        var r = document.getElementById('profileReadView'), e = document.getElementById('profileEditView');
        if (!r || !e) return;
        var showEdit = e.style.display === 'none';
        e.style.display = showEdit ? 'block' : 'none';
        r.style.display = showEdit ? 'none' : 'block';
    }
    function spToggleBillForm() {
        var f = document.getElementById('addBillForm');
        if (f) f.style.display = (f.style.display === 'none') ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Tabs
        var tabs = document.querySelectorAll('.sp-tab');
        var panes = document.querySelectorAll('.sp-tab-pane');
        var filterTabField = document.getElementById('filterTabField');
        tabs.forEach(function (t) {
            t.addEventListener('click', function () {
                var name = t.getAttribute('data-tab');
                tabs.forEach(function (x) { x.classList.remove('active'); });
                t.classList.add('active');
                panes.forEach(function (p) { p.style.display = (p.getAttribute('data-pane') === name) ? 'block' : 'none'; });
                if (filterTabField) filterTabField.value = name;
            });
        });

        // Auto open drawer if a supplier is opened
        var drawer = document.getElementById('supplierDrawer');
        var overlay = document.getElementById('drawerOverlay');
        if (drawer && overlay) {
            setTimeout(function () { drawer.classList.add('open'); overlay.classList.add('open'); }, 30);
        }

        // Auto open the Add Supplier modal when arriving from the global "Add New" menu
        if (new URLSearchParams(window.location.search).get('add') === '1') {
            spOpenAdd();
        }

        // Esc closes overlays
        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape') {
                spCloseAdd();
                if (drawer && drawer.classList.contains('open')) spCloseDrawer();
            }
        });
    });
</script>

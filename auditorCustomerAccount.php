<?php
/**
 * Auditor Customer Account View
 * Restricted version of customerAccount.php for auditor role.
 * Restrictions:
 *  - No payment section
 *  - No invoice creation
 *  - No client name editing
 *  - Can fill reports/forms only for service types auditor performed
 *  - Unaudited services are blurred/disabled
 */

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/auditor_portal_helper.php';
include 'utility.php';
date_default_timezone_set('Asia/Kolkata');

// Enforce auditor role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Auditor' || empty($_SESSION['auditor_id'])) {
    header('Location: login.php?reason=login_required');
    exit;
}

$auditorId = (int)$_SESSION['auditor_id'];
$auditorName = $_SESSION['username'] ?? '';

// Get customer ID
$customerId = isset($_GET['customerid']) ? (int)$_GET['customerid'] : 0;
if ($customerId <= 0 || !aitf_can_access_customer($customerId)) {
    header('Location: auditorDashboard.php');
    exit;
}

// Fetch auditor's service access for this client
$clientServices = aitf_fetch_auditor_client_services($db, $auditorId, $auditorName);

// Check if auditor has ANY access to this client
if (!isset($clientServices[$customerId]) || empty($clientServices[$customerId])) {
    header('Location: auditorDashboard.php');
    exit;
}

$allowedServices = $clientServices[$customerId]; // e.g. ['CA' => true, 'SA-I' => true]

// Fetch client info
$stmt = $db->prepare("SELECT * FROM customer_info WHERE id = :cid LIMIT 1");
$stmt->execute(array(':cid' => $customerId));
$customer = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$customer) {
    header('Location: auditorDashboard.php');
    exit;
}

// Fetch services for this client
$svcStmt = $db->prepare("SELECT * FROM service WHERE customer_id = :cid ORDER BY service_id DESC");
$svcStmt->execute(array(':cid' => $customerId));
$services = $svcStmt->fetchAll(PDO::FETCH_ASSOC);

// Group services by type and check access
$serviceGroups = array();
foreach ($services as $svc) {
    $type = strtoupper(trim($svc['service_type'] ?? ''));
    $hasAccess = aitf_auditor_can_access_service($clientServices, $customerId, $type);
    $svc['auditor_has_access'] = $hasAccess;
    $serviceGroups[$type][] = $svc;
}

// Fetch service type details
$caServices = array();
$saiServices = array();
$saiiServices = array();
$rcServices = array();

foreach ($services as $svc) {
    $svcId = $svc['service_id'];
    $type = strtoupper(trim($svc['service_type'] ?? ''));
    
    if ($type === 'CA') {
        try {
            $s = $db->prepare("SELECT * FROM service_type_ca WHERE service = :sid");
            $s->execute(array(':sid' => $svcId));
            $detail = $s->fetch(PDO::FETCH_ASSOC);
            if ($detail) {
                $detail['auditor_has_access'] = isset($allowedServices['CA']);
                $detail['service_status'] = $svc['service_status'];
                $caServices[] = $detail;
            }
        } catch (Exception $e) {}
    } elseif ($type === 'SA-I' || $type === 'SAI') {
        try {
            $s = $db->prepare("SELECT * FROM service_type_sai WHERE service = :sid");
            $s->execute(array(':sid' => $svcId));
            $detail = $s->fetch(PDO::FETCH_ASSOC);
            if ($detail) {
                $detail['auditor_has_access'] = isset($allowedServices['SA-I']);
                $detail['service_status'] = $svc['service_status'];
                $saiServices[] = $detail;
            }
        } catch (Exception $e) {}
    } elseif ($type === 'SA-II' || $type === 'SAII') {
        try {
            $s = $db->prepare("SELECT * FROM service_type_saii WHERE service = :sid");
            $s->execute(array(':sid' => $svcId));
            $detail = $s->fetch(PDO::FETCH_ASSOC);
            if ($detail) {
                $detail['auditor_has_access'] = isset($allowedServices['SA-II']);
                $detail['service_status'] = $svc['service_status'];
                $saiiServices[] = $detail;
            }
        } catch (Exception $e) {}
    } elseif ($type === 'RC') {
        try {
            $s = $db->prepare("SELECT * FROM service_type_rc WHERE service = :sid");
            $s->execute(array(':sid' => $svcId));
            $detail = $s->fetch(PDO::FETCH_ASSOC);
            if ($detail) {
                $detail['auditor_has_access'] = isset($allowedServices['RC']);
                $detail['service_status'] = $svc['service_status'];
                $rcServices[] = $detail;
            }
        } catch (Exception $e) {}
    }
}

aitf_touch_login_activity($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AITF - <?php echo htmlspecialchars($customer['customer_name']); ?> | Auditor View</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0F2742;
            --primary-light: #1e3a5f;
            --bg: #f0f2f5;
            --card-bg: #ffffff;
            --text: #1a1a2e;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;
            --radius: 16px;
            --shadow: 0 1px 3px rgba(0,0,0,0.08), 0 8px 24px rgba(0,0,0,0.04);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* Top Bar */
        .aud-topbar {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .aud-topbar-left { display: flex; align-items: center; gap: 16px; }
        .aud-topbar .back-btn {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 8px;
            background: rgba(255,255,255,0.1);
            transition: all 0.2s;
            display: flex; align-items: center; gap: 6px;
        }
        .aud-topbar .back-btn:hover { background: rgba(255,255,255,0.2); color: #fff; }
        .aud-topbar h1 { font-size: 18px; font-weight: 700; }
        .aud-topbar .badge-role {
            background: rgba(16,185,129,0.2);
            color: #6ee7b7;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Client Header Card */
        .client-header {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 28px;
            margin: 24px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
        }
        .client-name { font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 8px; }
        .client-meta { display: flex; flex-wrap: wrap; gap: 16px; color: var(--text-muted); font-size: 14px; }
        .client-meta span { display: flex; align-items: center; gap: 6px; }
        .client-status-badge {
            padding: 8px 20px;
            border-radius: 24px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            align-self: flex-start;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-process { background: #dbeafe; color: #1e40af; }
        .status-other { background: #f3f4f6; color: #4b5563; }

        /* Access Notice */
        .access-notice {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 1px solid #f59e0b;
            border-radius: 12px;
            padding: 16px 24px;
            margin: 0 24px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #92400e;
        }
        .access-notice i { font-size: 20px; }

        /* Service Tabs */
        .service-tabs {
            display: flex;
            gap: 8px;
            margin: 0 24px;
            padding: 0;
            flex-wrap: wrap;
        }
        .service-tab {
            padding: 10px 20px;
            border-radius: 10px 10px 0 0;
            border: 1px solid var(--border);
            border-bottom: none;
            background: var(--card-bg);
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-muted);
            transition: all 0.2s;
            display: flex; align-items: center; gap: 8px;
            position: relative;
        }
        .service-tab:hover { color: var(--primary); }
        .service-tab.active { 
            color: var(--primary); 
            background: var(--card-bg);
            box-shadow: 0 -2px 0 var(--primary) inset;
        }
        .service-tab.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            position: relative;
        }
        .service-tab.disabled::after {
            content: '\f023';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 10px;
            position: absolute;
            top: 4px;
            right: 4px;
            color: var(--danger);
        }

        /* Service Content */
        .service-content {
            background: var(--card-bg);
            border-radius: 0 var(--radius) var(--radius) var(--radius);
            box-shadow: var(--shadow);
            margin: 0 24px 24px;
            padding: 24px;
            min-height: 300px;
        }
        .service-panel { display: none; }
        .service-panel.active { display: block; }

        /* Blurred/Locked Section */
        .locked-section {
            filter: blur(3px);
            pointer-events: none;
            user-select: none;
            opacity: 0.5;
            position: relative;
        }
        .locked-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            background: rgba(255,255,255,0.6);
            border-radius: var(--radius);
        }
        .locked-overlay .lock-msg {
            background: var(--card-bg);
            padding: 20px 32px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            text-align: center;
            color: var(--text-muted);
        }
        .locked-overlay .lock-msg i { font-size: 32px; color: var(--danger); margin-bottom: 8px; display: block; }

        /* Document Table */
        .doc-table { width: 100%; border-collapse: collapse; }
        .doc-table th { 
            background: var(--bg);
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-table td { 
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }
        .doc-table tr:hover { background: #f9fafb; }

        .doc-status { display: inline-flex; align-items: center; gap: 6px; }
        .doc-status.uploaded { color: var(--success); }
        .doc-status.pending { color: var(--warning); background: rgba(245, 158, 11, 0.1); }
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 500;
            color: white;
            background-color: var(--info);
            border-radius: 6px;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .btn-download:hover { opacity: 0.9; color: white; }

        /* Forms Section */
        .form-section { margin-top: 20px; }
        .form-section h3 { 
            font-size: 16px; 
            font-weight: 700; 
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--border);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .client-header { grid-template-columns: 1fr; margin: 16px; padding: 20px; }
            .service-tabs { margin: 0 16px; }
            .service-content { margin: 0 16px 16px; padding: 16px; }
            .access-notice { margin: 0 16px 16px; }
            .client-name { font-size: 20px; }
            .aud-topbar h1 { font-size: 15px; }
        }

        /* Animation */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fadeSlideUp 0.4s ease-out forwards; }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="aud-topbar">
        <div class="aud-topbar-left">
            <a href="auditorDashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <h1><?php echo htmlspecialchars($customer['customer_name']); ?></h1>
        </div>
        <span class="badge-role"><i class="fas fa-user-shield"></i> Auditor View</span>
    </div>

    <!-- Client Header -->
    <div class="client-header animate-in">
        <div>
            <div class="client-name"><?php echo htmlspecialchars($customer['customer_name']); ?></div>
            <div class="client-meta">
                <span><i class="fas fa-fingerprint"></i> <?php echo htmlspecialchars($customer['unique_id'] ?? '-'); ?></span>
                <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($customer['email_id'] ?? '-'); ?></span>
                <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($customer['contact'] ?? '-'); ?></span>
                <?php if (!empty($customer['standard'])): ?>
                <span><i class="fas fa-certificate"></i> <?php echo htmlspecialchars($customer['standard']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $statusClass = 'status-other';
        $clientStatus = $customer['status'] ?? 'Unknown';
        if (stripos($clientStatus, 'active') !== false) $statusClass = 'status-active';
        elseif (stripos($clientStatus, 'process') !== false) $statusClass = 'status-process';
        ?>
        <span class="client-status-badge <?php echo $statusClass; ?>">
            <i class="fas fa-circle" style="font-size:8px;"></i>
            <?php echo htmlspecialchars($clientStatus); ?>
        </span>
    </div>

    <!-- Access Notice -->
    <div class="access-notice animate-in">
        <i class="fas fa-shield-halved"></i>
        <div>
            <strong>Auditor Access Mode</strong> — You can view and fill reports/forms for audits you have performed. 
            Payment, invoice, and client editing features are restricted.
            <br><strong>Your access:</strong> 
            <?php 
            $accessList = array_keys($allowedServices);
            echo !empty($accessList) ? implode(', ', $accessList) : 'None';
            ?>
        </div>
    </div>

    <!-- Service Tabs -->
    <div class="service-tabs animate-in">
        <?php
        $allServiceTypes = array('CA', 'SA-I', 'SA-II', 'RC');
        $firstActive = '';
        foreach ($allServiceTypes as $sType) {
            $hasAccess = isset($allowedServices[$sType]);
            $hasData = false;
            if ($sType === 'CA') $hasData = !empty($caServices);
            elseif ($sType === 'SA-I') $hasData = !empty($saiServices);
            elseif ($sType === 'SA-II') $hasData = !empty($saiiServices);
            elseif ($sType === 'RC') $hasData = !empty($rcServices);
            
            $isActive = ($firstActive === '' && $hasAccess);
            if ($isActive) $firstActive = $sType;
            
            $tabClass = $hasAccess ? '' : 'disabled';
            if ($isActive) $tabClass .= ' active';
        ?>
            <div class="service-tab <?php echo $tabClass; ?>" data-tab="<?php echo $sType; ?>" <?php if (!$hasAccess) echo 'title="You did not audit this service"'; ?>>
                <i class="fas fa-<?php echo $sType === 'CA' ? 'file-contract' : ($sType === 'RC' ? 'rotate' : 'clipboard-list'); ?>"></i>
                <?php echo $sType; ?>
                <?php if ($hasData): ?>
                    <span style="background:var(--info);color:#fff;font-size:11px;padding:2px 8px;border-radius:10px;">
                        <?php 
                        if ($sType === 'CA') echo count($caServices);
                        elseif ($sType === 'SA-I') echo count($saiServices);
                        elseif ($sType === 'SA-II') echo count($saiiServices);
                        elseif ($sType === 'RC') echo count($rcServices);
                        ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php } ?>
    </div>

    <!-- Service Content Panels -->
    <div class="service-content animate-in">
        <?php foreach ($allServiceTypes as $sType): ?>
            <?php
            $hasAccess = isset($allowedServices[$sType]);
            $isFirst = ($sType === $firstActive);
            $svcArray = array();
            if ($sType === 'CA') $svcArray = $caServices;
            elseif ($sType === 'SA-I') $svcArray = $saiServices;
            elseif ($sType === 'SA-II') $svcArray = $saiiServices;
            elseif ($sType === 'RC') $svcArray = $rcServices;
            ?>
            <div class="service-panel <?php echo $isFirst ? 'active' : ''; ?>" data-panel="<?php echo $sType; ?>">
                <?php if (!$hasAccess): ?>
                    <div style="position:relative;min-height:200px;">
                        <div class="locked-section">
                            <table class="doc-table">
                                <thead><tr><th>Document</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                                <tbody>
                                    <tr><td>Sample Document 1</td><td>Pending</td><td>--</td><td>View</td></tr>
                                    <tr><td>Sample Document 2</td><td>Pending</td><td>--</td><td>View</td></tr>
                                    <tr><td>Sample Document 3</td><td>Pending</td><td>--</td><td>View</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="locked-overlay">
                            <div class="lock-msg">
                                <i class="fas fa-lock"></i>
                                <strong>Access Restricted</strong>
                                <p style="margin-top:4px;">You did not perform the <?php echo $sType; ?> audit for this client.</p>
                            </div>
                        </div>
                    </div>
                <?php elseif (empty($svcArray)): ?>
                    <div style="text-align:center;padding:60px 20px;color:var(--text-muted);">
                        <i class="fas fa-folder-open" style="font-size:48px;margin-bottom:16px;opacity:0.3;display:block;"></i>
                        <p>No <?php echo $sType; ?> service records found for this client.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($svcArray as $idx => $detail): ?>
                        <div class="form-section">
                            <h3><?php echo $sType; ?> Audit <?php if (count($svcArray) > 1) echo '#' . ($idx + 1); ?></h3>
                            <table class="doc-table">
                                <thead>
                                    <tr>
                                        <th>Document / Report</th>
                                        <th>Status</th>
                                        <th>Auditor</th>
                                        <th>Audit Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($sType === 'CA'): ?>
                                        <tr>
                                            <td><i class="fas fa-file-alt"></i> Application Form</td>
                                            <td><span class="doc-status <?php echo !empty($detail['application_form']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['application_form']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['application_form']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td><?php echo htmlspecialchars($detail['stage1_auditor'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($detail['audit_date'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (!empty($detail['application_form'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['application_form']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-file-signature"></i> Stage 1 Audit Report</td>
                                            <td><span class="doc-status <?php echo !empty($detail['audit_report_I']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['audit_report_I']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['audit_report_I']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td><?php echo htmlspecialchars($detail['stage1_auditor'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($detail['audit_date'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (!empty($detail['audit_report_I'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['audit_report_I']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-file-signature"></i> Stage 2 Audit Report</td>
                                            <td><span class="doc-status <?php echo !empty($detail['audit_report_II']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['audit_report_II']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['audit_report_II']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td><?php echo htmlspecialchars($detail['stage2_auditor'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($detail['stage2_audit'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (!empty($detail['audit_report_II'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['audit_report_II']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-sticky-note"></i> Auditor Notes</td>
                                            <td><span class="doc-status <?php echo !empty($detail['auditor_notes']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['auditor_notes']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['auditor_notes']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td colspan="2">—</td>
                                            <td>
                                                <?php if (!empty($detail['auditor_notes'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['auditor_notes']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-star"></i> Customer Satisfaction</td>
                                            <td><span class="doc-status <?php echo !empty($detail['customer_satisfaction']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['customer_satisfaction']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['customer_satisfaction']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td colspan="2">—</td>
                                            <td>
                                                <?php if (!empty($detail['customer_satisfaction'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['customer_satisfaction']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php elseif ($sType === 'SA-I'): ?>
                                        <tr>
                                            <td><i class="fas fa-file-alt"></i> Info Update Form</td>
                                            <td><span class="doc-status <?php echo !empty($detail['info_update_form']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['info_update_form']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['info_update_form']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td><?php echo htmlspecialchars($detail['auditors'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($detail['audit_date'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (!empty($detail['info_update_form'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['info_update_form']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-file-signature"></i> SA-I Audit Report</td>
                                            <td><span class="doc-status <?php echo !empty($detail['audit_report_sai']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['audit_report_sai']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['audit_report_sai']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td><?php echo htmlspecialchars($detail['auditors'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($detail['audit_date'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (!empty($detail['audit_report_sai'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['audit_report_sai']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-sticky-note"></i> Auditor Notes</td>
                                            <td><span class="doc-status <?php echo !empty($detail['auditor_notes_sai']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['auditor_notes_sai']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['auditor_notes_sai']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td colspan="2">—</td>
                                            <td>
                                                <?php if (!empty($detail['auditor_notes_sai'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['auditor_notes_sai']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php elseif ($sType === 'SA-II'): ?>
                                        <tr>
                                            <td><i class="fas fa-file-alt"></i> Info Update Form</td>
                                            <td><span class="doc-status <?php echo !empty($detail['update_info_saii']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['update_info_saii']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['update_info_saii']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td><?php echo htmlspecialchars($detail['auditors'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($detail['audit_date'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (!empty($detail['update_info_saii'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['update_info_saii']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-file-signature"></i> SA-II Audit Report</td>
                                            <td><span class="doc-status <?php echo !empty($detail['audit_report_saii']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['audit_report_saii']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['audit_report_saii']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td><?php echo htmlspecialchars($detail['auditors'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($detail['audit_date'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (!empty($detail['audit_report_saii'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['audit_report_saii']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-sticky-note"></i> Auditor Notes</td>
                                            <td><span class="doc-status <?php echo !empty($detail['auditor_notes_saii']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['auditor_notes_saii']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['auditor_notes_saii']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td colspan="2">—</td>
                                            <td>
                                                <?php if (!empty($detail['auditor_notes_saii'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['auditor_notes_saii']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php elseif ($sType === 'RC'): ?>
                                        <tr>
                                            <td><i class="fas fa-file-alt"></i> RC Info Update</td>
                                            <td><span class="doc-status <?php echo !empty($detail['rc_info_update']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['rc_info_update']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['rc_info_update']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td><?php echo htmlspecialchars($detail['auditors'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($detail['audit_date'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (!empty($detail['rc_info_update'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['rc_info_update']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-file-signature"></i> RC Audit Report</td>
                                            <td><span class="doc-status <?php echo !empty($detail['rc_audit_report']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['rc_audit_report']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['rc_audit_report']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td><?php echo htmlspecialchars($detail['auditors'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($detail['audit_date'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (!empty($detail['rc_audit_report'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['rc_audit_report']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-sticky-note"></i> Auditor Notes</td>
                                            <td><span class="doc-status <?php echo !empty($detail['auditor_notes_rc']) ? 'uploaded' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo !empty($detail['auditor_notes_rc']) ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo !empty($detail['auditor_notes_rc']) ? 'Uploaded' : 'Pending'; ?>
                                            </span></td>
                                            <td colspan="2">—</td>
                                            <td>
                                                <?php if (!empty($detail['auditor_notes_rc'])): ?>
                                                    <a href="<?php echo htmlspecialchars($detail['auditor_notes_rc']); ?>" target="_blank" class="btn-download"><i class="fas fa-eye"></i> View</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <p style="margin-top:12px;font-size:13px;color:var(--text-muted);">
                                <i class="fas fa-info-circle"></i> 
                                Service Status: <strong><?php echo htmlspecialchars($detail['service_status'] ?? '-'); ?></strong>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        document.querySelectorAll('.service-tab:not(.disabled)').forEach(function(tab) {
            tab.addEventListener('click', function() {
                var target = this.getAttribute('data-tab');
                
                document.querySelectorAll('.service-tab').forEach(function(t) { t.classList.remove('active'); });
                document.querySelectorAll('.service-panel').forEach(function(p) { p.classList.remove('active'); });
                
                this.classList.add('active');
                var panel = document.querySelector('.service-panel[data-panel="' + target + '"]');
                if (panel) panel.classList.add('active');
            });
        });

        // Prevent click on disabled tabs
        document.querySelectorAll('.service-tab.disabled').forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });
    });
    </script>
</body>
</html>

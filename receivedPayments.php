<div id="wrapper" class="active">
<?php
$pageIdentifier = "Received Payments";
include_once './dbConnect.php';
include_once './header.php';
date_default_timezone_set('Asia/Kolkata');

if (function_exists('aitf_auto_sync_schema')) {
    aitf_auto_sync_schema($db);
}
if (function_exists('aitf_ensure_tds_columns')) {
    aitf_ensure_tds_columns($db);
}

$userRole = trim($_SESSION['role'] ?? '');
$canAccess = (
    (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserAdmin() || Utitlity::checkForUserFinance()))
    || in_array($userRole, array('Top Mgmt', 'Admin', 'Finance', 'Director', 'Management', 'Main Admin'), true)
    || (isset($_SESSION['username']) && in_array(strtolower(trim($_SESSION['username'])), array('admin', 'mainadmin', 'director', 'finance'), true))
);

if (!$canAccess) {
    echo "<script>alert('Unauthorized access.'); window.location.href='homePage.php';</script>";
    exit;
}

$statusFilter = $_GET['status'] ?? 'all';
$startDateStr = $_GET['startDate'] ?? '';
$endDateStr = $_GET['endDate'] ?? '';
$clientSearch = trim($_GET['clientSearch'] ?? '');
$serviceFilter = trim($_GET['serviceFilter'] ?? 'all');
$sortOrder = $_GET['sortOrder'] ?? 'latest';

$startDate = '';
$endDate = '';

if (!empty($startDateStr)) {
    $parts = explode('.', $startDateStr);
    if (count($parts) == 3) {
        $startDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
}
if (!empty($endDateStr)) {
    $parts = explode('.', $endDateStr);
    if (count($parts) == 3) {
        $endDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
}

// Fetch all received payment entries from payment_system
$paymentsQuery = "SELECT 
    ps.payment_id,
    ps.payment_sy_id,
    ps.customer_id,
    ci.unique_id,
    ci.customer_name,
    ps.service_type,
    ps.amount_decided,
    ps.payment_received,
    ps.tds_amount,
    ps.discount_amount,
    ps.payment_date,
    ps.cheque_details,
    ps.reconciliation,
    ps.tds_verified,
    ps.balance,
    ps.comment,
    ci.contact_person,
    ci.contact,
    0 as is_statement
FROM payment_system ps
JOIN customer_info ci ON ci.id = ps.customer_id
WHERE ps.payment_received > 0
  AND ci.is_deleted = 'NO'";

$params = [];

if ($statusFilter === 'reconciled') {
    $paymentsQuery .= " AND (LOWER(TRIM(COALESCE(ps.reconciliation, ''))) IN ('verified', 'yes', '1') OR LOWER(TRIM(COALESCE(ps.tds_verified, ''))) IN ('verified', 'yes', '1'))";
} elseif ($statusFilter === 'pending') {
    $paymentsQuery .= " AND (ps.reconciliation IS NULL OR LOWER(TRIM(ps.reconciliation)) NOT IN ('verified', 'yes', '1'))";
}

if (!empty($startDate)) {
    $paymentsQuery .= " AND ps.payment_date >= :startDate";
    $params[':startDate'] = $startDate;
}
if (!empty($endDate)) {
    $paymentsQuery .= " AND ps.payment_date <= :endDate";
    $params[':endDate'] = $endDate;
}
if (!empty($clientSearch)) {
    $paymentsQuery .= " AND (ci.customer_name LIKE :clientSearch OR ci.unique_id LIKE :clientSearch)";
    $params[':clientSearch'] = '%' . $clientSearch . '%';
}
if (!empty($serviceFilter) && $serviceFilter !== 'all') {
    $paymentsQuery .= " AND ps.service_type LIKE :svcFilter";
    $params[':svcFilter'] = '%' . $serviceFilter . '%';
}

if ($sortOrder === 'oldest') {
    $paymentsQuery .= " ORDER BY ps.payment_date ASC, ps.payment_id ASC";
} elseif ($sortOrder === 'amount_high') {
    $paymentsQuery .= " ORDER BY ps.payment_received DESC";
} elseif ($sortOrder === 'amount_low') {
    $paymentsQuery .= " ORDER BY ps.payment_received ASC";
} else {
    $paymentsQuery .= " ORDER BY ps.payment_date DESC, ps.payment_id DESC";
}

$allPayments = [];
try {
    $stmt = $db->prepare($paymentsQuery);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    $allPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $allPayments = [];
}

// Also include received payments from statement_payments if active
try {
    if (function_exists('aitf_statement_table_exists') && aitf_statement_table_exists($db, 'statement_payments')) {
        $stmtQuery = "SELECT 
            sp.id as payment_id,
            0 as payment_sy_id,
            sp.customer_id,
            ci.unique_id,
            ci.customer_name,
            sp.service_type,
            sp.amount_decided,
            sp.payment_received,
            sp.tds_received as tds_amount,
            0.00 as discount_amount,
            sp.payment_date,
            sp.instrument as cheque_details,
            sp.tds_verified as reconciliation,
            sp.tds_verified,
            sp.balance,
            sp.comment,
            ci.contact_person,
            ci.contact,
            1 as is_statement
        FROM statement_payments sp
        JOIN customer_info ci ON ci.id = sp.customer_id
        WHERE sp.payment_received > 0
          AND ci.is_deleted = 'NO'";
        
        $spParams = [];
        if ($statusFilter === 'reconciled') {
            $stmtQuery .= " AND LOWER(TRIM(COALESCE(sp.tds_verified, ''))) IN ('verified', 'yes', '1')";
        } elseif ($statusFilter === 'pending') {
            $stmtQuery .= " AND (sp.tds_verified IS NULL OR LOWER(TRIM(sp.tds_verified)) NOT IN ('verified', 'yes', '1'))";
        }
        if (!empty($startDate)) {
            $stmtQuery .= " AND sp.payment_date >= :spStartDate";
            $spParams[':spStartDate'] = $startDate;
        }
        if (!empty($endDate)) {
            $stmtQuery .= " AND sp.payment_date <= :spEndDate";
            $spParams[':spEndDate'] = $endDate;
        }
        if (!empty($clientSearch)) {
            $stmtQuery .= " AND (ci.customer_name LIKE :spClientSearch OR ci.unique_id LIKE :spClientSearch)";
            $spParams[':spClientSearch'] = '%' . $clientSearch . '%';
        }
        
        $stmtSP = $db->prepare($stmtQuery);
        foreach ($spParams as $k => $v) {
            $stmtSP->bindValue($k, $v);
        }
        $stmtSP->execute();
        $spPayments = $stmtSP->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($spPayments)) {
            $allPayments = array_merge($allPayments, $spPayments);
        }
    }
} catch (Exception $e) {}

// Calculate KPIs
$totalReceivedCount = count($allPayments);
$totalReceivedSum = 0;
$totalReconciledSum = 0;
$totalReconciledCount = 0;
$totalPendingSum = 0;
$totalPendingCount = 0;

foreach ($allPayments as $p) {
    $rcv = floatval($p['payment_received'] ?? 0);
    $totalReceivedSum += $rcv;

    $isRec = in_array(strtolower(trim($p['reconciliation'] ?? '')), ['verified', 'yes', '1'])
          || in_array(strtolower(trim($p['tds_verified'] ?? '')), ['verified', 'yes', '1']);

    if ($isRec) {
        $totalReconciledSum += $rcv;
        $totalReconciledCount++;
    } else {
        $totalPendingSum += $rcv;
        $totalPendingCount++;
    }
}

if (!function_exists('aitf_format_service_display')) {
    function aitf_format_service_display($rawSvc, $custId, $db = null) {
        $s = trim((string)$rawSvc);
        
        // If empty or purely numeric or generic words like 'payment', 'balance', 'remaining', 'rem.'
        if (
            $s === '' || $s === '0' || $s === '-' || 
            preg_match('/^\d+(\.\d+)?(k|lakh)?$/i', $s) || 
            preg_match('/^(payment|balance|rem\.?|remaining|gst|travelling|follow up.*)$/i', $s)
        ) {
            if ($db && $custId) {
                try {
                    $q = $db->prepare("SELECT service_type FROM service WHERE customer_id = :cid AND is_deleted = 'NO' AND service_type NOT IN ('CR') ORDER BY service_id DESC LIMIT 1");
                    $q->execute([':cid' => $custId]);
                    $r = $q->fetch(PDO::FETCH_ASSOC);
                    if ($r && !empty($r['service_type'])) {
                        $s = trim($r['service_type']);
                    }
                } catch (Exception $e) {}
            }
        }
        
        if ($s === '') $s = 'CA';

        $lower = strtolower($s);
        if (stripos($lower, 'tds') !== false) return 'TDS';
        if (stripos($lower, 'discount') !== false || stripos($lower, 'short') !== false) return 'Discount';
        if (stripos($lower, 'contract') !== false) return 'Contract Review';
        if (stripos($lower, 'ptr') !== false) return 'PTR';
        if (preg_match('/\b(transition|ta)\b/i', $s)) return 'Transition Audit';
        if (stripos($lower, 'scope') !== false) return 'Scope Addition';
        if (stripos($lower, 'sp') !== false || stripos($lower, 'special') !== false) return 'SP';

        // Check SA-4 / SA-3 first
        if (preg_match('/sa[-_\s]*0?4\b/i', $s)) return 'SA-IV';
        if (preg_match('/sa[-_\s]*0?3\b/i', $s)) return 'SA-III';

        // Check SA-2 / SA-II / 2nd install (if not RC)
        if (
            preg_match('/sa[-_\s]*0?2\b/i', $s) || 
            preg_match('/sa[-_\s]*ii\b/i', $s) ||
            (stripos($lower, 'sa') !== false && stripos($lower, '2') !== false) ||
            stripos($lower, 'st-02') !== false ||
            (stripos($lower, '2nd') !== false && stripos($lower, 'rc') === false && stripos($lower, 'ca') === false)
        ) {
            return 'SA-II';
        }

        // Check SA-1 / SA-I / surveillance
        if (
            preg_match('/sa[-_\s]*0?1\b/i', $s) || 
            preg_match('/sa[-_\s]*i\b/i', $s) ||
            (stripos($lower, 'sa') !== false && (stripos($lower, '1') !== false || stripos($lower, 'first') !== false)) ||
            stripos($lower, 'surveillance') !== false
        ) {
            return 'SA-I';
        }

        // Check RC / Recertification
        if (
            preg_match('/\brc\b/i', $s) || 
            stripos($lower, 'recert') !== false || 
            stripos($lower, 're-cert') !== false
        ) {
            return 'RC';
        }

        // Check CA / Initial / Stage
        if (
            preg_match('/\bca\b/i', $s) || 
            stripos($lower, 'stage') !== false || 
            stripos($lower, 'initial') !== false ||
            stripos($lower, 'cert') !== false ||
            stripos($lower, '1st') !== false ||
            stripos($lower, 'first') !== false
        ) {
            return 'CA';
        }

        // Fallback: If customer has an active service in DB
        if ($db && $custId) {
            try {
                $q = $db->prepare("SELECT service_type FROM service WHERE customer_id = :cid AND is_deleted = 'NO' AND service_type NOT IN ('CR') ORDER BY service_id DESC LIMIT 1");
                $q->execute([':cid' => $custId]);
                $r = $q->fetch(PDO::FETCH_ASSOC);
                if ($r && !empty($r['service_type'])) {
                    return trim($r['service_type']);
                }
            } catch (Exception $e) {}
        }

        return !empty($s) && strlen($s) <= 20 ? strtoupper($s) : 'CA';
    }
}

if (!function_exists('aitf_format_instrument_display')) {
    function aitf_format_instrument_display($chequeDetails, $comment) {
        $inst = trim((string)$chequeDetails);
        if ($inst !== '' && $inst !== '-' && $inst !== '0' && strtolower($inst) !== 'null') {
            if (preg_match('/^\d{5,8}$/', $inst)) {
                return 'Chq: ' . $inst;
            }
            return $inst;
        }
        $comm = trim((string)$comment);
        if ($comm !== '') {
            if (preg_match('/(neft[-\s:]*[\w\d]+|rtgs[-\s:]*[\w\d]+|imps[-\s:]*[\w\d]+|upi[-\s:]*[\w\d]+|ch[-\s:]*[\w\d]+|cheque[-\s:]*[\w\d]+|utr[-\s:]*[\w\d]+|\b\d{5,8}\b)/i', $comm, $m)) {
                $extracted = trim($m[0]);
                if (preg_match('/^\d{5,8}$/', $extracted)) {
                    return 'Chq: ' . $extracted;
                }
                return $extracted;
            }
            if (strlen($comm) <= 25 && stripos($comm, 'call') === false && stripos($comm, 'visit') === false && stripos($comm, 'remind') === false) {
                return $comm;
            }
        }
        return 'Bank / NEFT';
    }
}

if (!function_exists('aitf_format_payment_date_display')) {
    function aitf_format_payment_date_display($rawDate) {
        $raw = trim((string)$rawDate);
        if ($raw === '' || $raw === '0000-00-00' || $raw === '0000-00-00 00:00:00' || strtolower($raw) === 'null') {
            return '-';
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $raw, $m)) {
            return $m[3] . '.' . $m[2] . '.' . $m[1];
        }
        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})/', $raw)) {
            return $raw;
        }
        $ts = strtotime($raw);
        if ($ts !== false && $ts > 0) {
            return date('d.m.Y', $ts);
        }
        return !empty($raw) ? $raw : '-';
    }
}
?>

    <style>
        /* =====================================================================
           MASTER CREATOR THEME FOR RECEIVED PAYMENTS
           ===================================================================== */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body, #wrapper, #page-content-wrapper {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        .master-creator-container {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 10px 18px 50px 18px !important;
            box-sizing: border-box !important;
        }

        /* =====================================================================
           COMPACT HERO HEADER (Exact MasterCreator Style)
           ===================================================================== */
        .mc-hero-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #eff6ff 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 18px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: nowrap;
            position: relative;
            overflow: hidden;
        }

        .mc-hero-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0d4d8b 0%, #2563eb 50%, #38bdf8 100%);
        }

        .mc-hero-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .mc-hero-stats {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
        }

        .mc-stat-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 5px 12px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: transform 0.15s, box-shadow 0.15s;
            white-space: nowrap;
        }

        .mc-stat-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.04);
        }

        .mc-stat-icon {
            width: 24px;
            height: 24px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .mc-stat-icon.icon-blue { background: #eff6ff; color: #2563eb; }
        .mc-stat-icon.icon-green { background: #ecfdf5; color: #059669; }
        .mc-stat-icon.icon-red { background: #fef2f2; color: #dc2626; }
        .mc-stat-icon.icon-purple { background: #f5f3ff; color: #7c3aed; }
        .mc-stat-icon.icon-amber { background: #fffbeb; color: #d97706; }

        .mc-stat-lbl {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1;
            margin: 0;
            white-space: nowrap;
        }

        .mc-stat-num {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            white-space: nowrap;
            margin-left: 2px;
        }

        /* =====================================================================
           SECTION CARDS & BUTTONS (MasterCreator Design)
           ===================================================================== */
        .qms_complete_btn {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            border: none !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 12px 16px !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(13, 77, 139, 0.15) !important;
            margin: 0 !important;
            transition: all 0.2s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        .mc-filter-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04) !important;
            padding: 14px 18px !important;
            margin-bottom: 20px !important;
        }

        .mc-card-well {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-top: none !important;
            border-radius: 0 0 12px 12px !important;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04) !important;
            padding: 16px !important;
            margin-bottom: 24px !important;
        }

        /* =====================================================================
           FILTER BAR - PIXEL-PERFECT SINGLE LINE LAYOUT
           ===================================================================== */
        .mc-filter-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04) !important;
            padding: 14px 18px !important;
            margin-bottom: 20px !important;
        }

        .mc-filter-row {
            display: grid !important;
            grid-template-columns: minmax(170px, 1.3fr) minmax(220px, 2fr) minmax(130px, 1fr) minmax(130px, 1fr) minmax(160px, 1.2fr) auto !important;
            gap: 12px !important;
            align-items: flex-start !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .mc-filter-col {
            display: flex !important;
            flex-direction: column !important;
            justify-content: flex-start !important;
            min-width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .mc-filter-col label,
        .mc-filter-col .mc-filter-label {
            display: block !important;
            height: 16px !important;
            line-height: 16px !important;
            min-height: 16px !important;
            max-height: 16px !important;
            margin: 0 0 6px 0 !important;
            padding: 0 !important;
            color: #0d4d8b !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.4px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            box-sizing: border-box !important;
        }

        .mc-filter-col input,
        .mc-filter-col select {
            display: block !important;
            width: 100% !important;
            height: 38px !important;
            min-height: 38px !important;
            max-height: 38px !important;
            line-height: 36px !important;
            padding: 0 12px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            font-size: 12.5px !important;
            font-weight: 500 !important;
            background-color: #ffffff !important;
            color: #1e293b !important;
            box-sizing: border-box !important;
            outline: none !important;
            margin: 0 !important;
            transition: border-color 0.2s, box-shadow 0.2s !important;
        }

        .mc-filter-col input:focus,
        .mc-filter-col select:focus {
            border-color: #0d4d8b !important;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12) !important;
        }

        .btn-filter-reset {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            height: 38px !important;
            min-height: 38px !important;
            max-height: 38px !important;
            line-height: 36px !important;
            padding: 0 16px !important;
            margin: 0 !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #64748b !important;
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            text-decoration: none !important;
            box-sizing: border-box !important;
            white-space: nowrap !important;
            transition: all 0.15s ease !important;
        }

        .btn-filter-reset:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }

        html.theme-dark .btn-filter-reset {
            background: var(--hpd-surface-2) !important;
            border-color: var(--hpd-border-2) !important;
            color: var(--hpd-text-dim) !important;
        }
        html.theme-dark .btn-filter-reset:hover {
            background: var(--hpd-border-2) !important;
            color: var(--hpd-text) !important;
        }

        /* =====================================================================
           TABLE DESIGN (MasterCreator SaaS Table - Pixel Perfect Fixed Grid)
           ===================================================================== */
        #table_received_payments,
        .dataTables_scrollHeadInner table {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            margin: 0 !important;
            font-size: 12px;
        }

        #table_received_payments thead th,
        .dataTables_scrollHead thead th {
            background: #f1f5f9;
            color: #0f172a;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 9px 8px;
            border-top: none;
            border-bottom: 2px solid #cbd5e1;
            vertical-align: middle;
            white-space: nowrap !important;
            word-break: keep-all !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #table_received_payments tbody td {
            font-size: 12px;
            color: #334155;
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #table_received_payments tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        /* Badges */
        .service-badge {
            display: inline-flex !important;
            align-items: center !important;
            gap: 3px !important;
            padding: 2px 6px !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            border-radius: 4px !important;
            line-height: 1.2 !important;
            white-space: nowrap !important;
            text-transform: uppercase !important;
        }

        .svc-ca {
            background: #e0f2fe !important;
            color: #0369a1 !important;
            border: 1px solid #bae6fd !important;
        }

        .svc-sai {
            background: #dcfce7 !important;
            color: #15803d !important;
            border: 1px solid #bbf7d0 !important;
        }

        .svc-saii {
            background: #f3e8ff !important;
            color: #7e22ce !important;
            border: 1px solid #e9d5ff !important;
        }

        .svc-rc {
            background: #fef3c7 !important;
            color: #b45309 !important;
            border: 1px solid #fde68a !important;
        }

        .svc-other {
            background: #f1f5f9 !important;
            color: #334155 !important;
            border: 1px solid #cbd5e1 !important;
        }

        .instrument-badge {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace !important;
            font-size: 10px !important;
            font-weight: 600 !important;
            padding: 2px 6px !important;
            border-radius: 4px !important;
            background: #f8fafc !important;
            color: #1e293b !important;
            border: 1px solid #cbd5e1 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            white-space: nowrap !important;
            max-width: 100% !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        html.theme-dark .svc-ca { background: #0c4a6e !important; color: #7dd3fc !important; border-color: #0369a1 !important; }
        html.theme-dark .svc-sai { background: #064e3b !important; color: #86efac !important; border-color: #047857 !important; }
        html.theme-dark .svc-saii { background: #3b0764 !important; color: #d8b4fe !important; border-color: #6b21a8 !important; }
        html.theme-dark .svc-rc { background: #451a03 !important; color: #fcd34d !important; border-color: #78350f !important; }
        html.theme-dark .svc-other { background: #1e293b !important; color: #94a3b8 !important; border-color: #334155 !important; }
        html.theme-dark .instrument-badge { background: #1e293b !important; color: #e2e8f0 !important; border-color: #334155 !important; }

        .unique-id-link {
            font-weight: 700;
            color: #0d4d8b;
            text-decoration: none;
            font-size: 11.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .unique-id-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        /* Action Buttons */
        .btn-reconcile-toggle {
            height: 23px;
            font-size: 10.5px;
            font-weight: 700;
            border-radius: 4px;
            padding: 0 8px;
            min-width: 78px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .reconcile-pending {
            background-color: #ef4444;
            border: 1px solid #dc2626;
            color: #ffffff;
        }

        .reconcile-pending:hover {
            background-color: #dc2626;
            color: #ffffff;
        }

        .reconcile-verified {
            background-color: #059669;
            border: 1px solid #047857;
            color: #ffffff;
        }

        .reconcile-verified:hover {
            background-color: #047857;
            color: #ffffff;
        }

        /* Comment Textarea in Table (Compact Box Style with Scrollbar like Image 2) */
        .rcv-comment-wrap {
            width: 100%;
            display: flex;
            align-items: center;
        }

        .rcv-comment-input {
            width: 100% !important;
            height: 44px !important;
            min-height: 44px !important;
            max-height: 80px !important;
            line-height: 1.35 !important;
            padding: 4px 6px !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            font-family: inherit !important;
            color: #0f172a !important;
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 4px !important;
            outline: none !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
            box-sizing: border-box !important;
            resize: vertical !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            word-break: break-word !important;
            transition: border-color 0.15s, box-shadow 0.15s !important;
        }

        .rcv-comment-input:hover {
            border-color: #94a3b8 !important;
        }

        .rcv-comment-input:focus {
            background: #ffffff !important;
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
            color: #0f172a !important;
        }

        .rcv-comment-saved {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2) !important;
        }

        html.theme-dark .rcv-comment-input {
            color: var(--hpd-text) !important;
            background: var(--hpd-surface-2) !important;
            border: 1px solid var(--hpd-border-2) !important;
            box-shadow: none !important;
        }

        html.theme-dark .rcv-comment-input:hover {
            border-color: #475569 !important;
        }

        html.theme-dark .rcv-comment-input:focus {
            border-color: var(--hpd-accent) !important;
            box-shadow: 0 0 0 2px rgba(74, 158, 255, 0.2) !important;
            color: #ffffff !important;
        }

        html.theme-dark .rcv-comment-saved {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25) !important;
        }

        /* DataTables bottom row */
        .dt-bottom-row {
            margin-top: 10px !important;
            padding: 4px 8px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            font-size: 12px !important;
            color: #475569 !important;
        }

        .dt-bottom-row .dataTables_info {
            padding-top: 0 !important;
            font-weight: 600 !important;
        }

        .dt-bottom-row .dataTables_paginate {
            margin: 0 !important;
        }

        /* Scroll & Layout Containers (Strictly No Horizontal Overflow) */
        .rcv-table-wrapper {
            width: 100% !important;
            overflow: hidden !important;
        }

        .dataTables_wrapper {
            width: 100% !important;
            box-sizing: border-box !important;
            overflow-x: hidden !important;
            position: relative;
            clear: both;
        }

        .dataTables_scroll {
            width: 100% !important;
            overflow: hidden !important;
        }

        .dataTables_scrollHead {
            width: 100% !important;
            overflow: hidden !important;
            border: 0 !important;
        }

        .dataTables_scrollHeadInner {
            width: 100% !important;
            box-sizing: border-box !important;
            padding-right: 0 !important;
        }

        .dataTables_scrollBody {
            width: 100% !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            position: relative !important;
            -webkit-overflow-scrolling: touch;
        }

        /* =====================================================================
           DARK MODE SUPPORT (Identical to masterCreator.php)
           ===================================================================== */
        html.theme-dark {
            --hpd-bg: #0a0e14;
            --hpd-surface: #111722;
            --hpd-surface-2: #161d2b;
            --hpd-border: #232c3d;
            --hpd-border-2: #2e3a4f;
            --hpd-text: #dbe3f0;
            --hpd-text-dim: #93a1b8;
            --hpd-text-faint: #6b7890;
            --hpd-accent: #4a9eff;
        }

        html.theme-dark body, 
        html.theme-dark #page-content-wrapper,
        html.theme-dark .page-content.inset {
            background: var(--hpd-bg) !important;
            color: var(--hpd-text) !important;
        }

        html.theme-dark .mc-hero-header {
            background: linear-gradient(135deg, #111722 0%, #0d131e 100%) !important;
            border-color: var(--hpd-border) !important;
        }

        html.theme-dark .mc-hero-title { color: #f8fafc !important; }
        html.theme-dark .mc-stat-pill {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
        }
        html.theme-dark .mc-stat-num { color: #f8fafc !important; }

        html.theme-dark .qms_complete_btn {
            background: linear-gradient(135deg, #16243d 0%, #0d1b33 100%) !important;
            border-color: var(--hpd-border-2) !important;
        }

        html.theme-dark .mc-filter-card,
        html.theme-dark .mc-card-well {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
        }

        html.theme-dark .mc-filter-col input,
        html.theme-dark .mc-filter-col select {
            background: var(--hpd-surface-2) !important;
            border-color: var(--hpd-border-2) !important;
            color: var(--hpd-text) !important;
        }

        html.theme-dark #table_received_payments thead th {
            background: #0b1320 !important;
            color: #f8fafc !important;
            border-bottom-color: #334155 !important;
        }

        html.theme-dark #table_received_payments tbody td {
            color: #cbd5e1 !important;
            border-bottom-color: #1e293b !important;
        }

        html.theme-dark #table_received_payments tbody tr:hover td {
            background-color: #1e293b !important;
        }
    </style>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid master-creator-container">
                
                <!-- Compact Master Creator Hero Header -->
                <div class="mc-hero-header">
                    <div class="mc-hero-left">
                        <h1 class="mc-hero-title"><i class="fa fa-university" style="color: #0d4d8b; font-size: 16px; margin-right: 8px;"></i> Receive Payment &amp; Bank Reconciliation</h1>
                    </div>
                </div>

                <!-- Filter & Search Section -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="mc-filter-card">
                            <form method="GET" action="receivedPayments.php" id="receivedFilterForm">
                                <div class="mc-filter-row">
                                    <div class="mc-filter-col">
                                        <label for="status">Reconciliation Status</label>
                                        <select name="status" id="status" onchange="$('#receivedFilterForm').submit()">
                                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                            <option value="reconciled" <?php echo $statusFilter === 'reconciled' ? 'selected' : ''; ?>>✔ Reconciled (Verified)</option>
                                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>⏳ Pending Verification</option>
                                        </select>
                                    </div>

                                    <div class="mc-filter-col">
                                        <label for="clientSearch">Client Search</label>
                                        <input type="text" name="clientSearch" id="clientSearch" value="<?php echo htmlspecialchars($clientSearch); ?>" placeholder="Search client name, unique ID..." autocomplete="off">
                                    </div>

                                    <div class="mc-filter-col">
                                        <label for="startDate">From Date</label>
                                        <input type="text" name="startDate" id="startDate" class="datepicker-rcv" value="<?php echo $startDateStr; ?>" placeholder="DD.MM.YYYY" autocomplete="off">
                                    </div>

                                    <div class="mc-filter-col">
                                        <label for="endDate">To Date</label>
                                        <input type="text" name="endDate" id="endDate" class="datepicker-rcv" value="<?php echo $endDateStr; ?>" placeholder="DD.MM.YYYY" autocomplete="off">
                                    </div>

                                    <div class="mc-filter-col">
                                        <label for="sortOrder">Sort By</label>
                                        <select name="sortOrder" id="sortOrder" onchange="$('#receivedFilterForm').submit()">
                                            <option value="latest" <?php echo $sortOrder === 'latest' ? 'selected' : ''; ?>>Latest Date First</option>
                                            <option value="oldest" <?php echo $sortOrder === 'oldest' ? 'selected' : ''; ?>>Oldest Date First</option>
                                            <option value="amount_high" <?php echo $sortOrder === 'amount_high' ? 'selected' : ''; ?>>Amount: High to Low</option>
                                            <option value="amount_low" <?php echo $sortOrder === 'amount_low' ? 'selected' : ''; ?>>Amount: Low to High</option>
                                        </select>
                                    </div>

                                    <div class="mc-filter-col">
                                        <label>&nbsp;</label>
                                        <a href="receivedPayments.php" class="btn-filter-reset" title="Reset all filters">
                                            <i class="fa fa-refresh"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>

                <!-- Payments Table Section Card -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-xs-12 text-center btn qms_complete_btn">
                                <span><i class="fa fa-list"></i> Received Payment Entries &amp; Bank Reconciliation Statements</span>
                                <span style="font-size: 11.5px; text-transform: none; opacity: 0.95; font-weight: 600;">Showing <?php echo count($allPayments); ?> records</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mc-card-well" style="padding: 10px;">
                                <div class="rcv-table-wrapper">
                                    <table class="table table-hover table-striped" id="table_received_payments">
                                        <thead>
                                            <tr>
                                                <th style="width: 10%; white-space: nowrap;">Unique ID</th>
                                                <th style="width: 24%;">Client Name</th>
                                                <th style="width: 7%; text-align: center; white-space: nowrap;">Service</th>
                                                <th style="width: 11%; text-align: right; white-space: nowrap;">Received Amount</th>
                                                <th style="width: 9%; text-align: center; white-space: nowrap;">Received Date</th>
                                                <th style="width: 12%; white-space: nowrap;">Instrument / NEFT</th>
                                                <th style="width: 14%; white-space: nowrap;">Comment</th>
                                                <th style="width: 13%; text-align: center; white-space: nowrap;">Statement Reconciliation</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allPayments as $pay) { 
                                                $rcvAmt = floatval($pay['payment_received'] ?? 0);
                                                $isReconciled = in_array(strtolower(trim($pay['reconciliation'] ?? '')), ['verified', 'yes', '1'])
                                                              || in_array(strtolower(trim($pay['tds_verified'] ?? '')), ['verified', 'yes', '1']);
                                                $statusText = $isReconciled ? 'Reconciled' : 'Reconcile';
                                                $dateFormatted = htmlspecialchars(aitf_format_payment_date_display($pay['payment_date'] ?? ''));
                                                $serviceName = htmlspecialchars(aitf_format_service_display($pay['service_type'] ?? '', $pay['customer_id'] ?? 0, $db));
                                                $clientName = htmlspecialchars($pay['customer_name'] ?? '');
                                                $uniqueId = htmlspecialchars($pay['unique_id'] ?? '');
                                                $chequeDetails = htmlspecialchars(aitf_format_instrument_display($pay['cheque_details'] ?? '', $pay['comment'] ?? ''));

                                                $serviceClass = 'svc-other';
                                                if ($serviceName === 'CA') $serviceClass = 'svc-ca';
                                                elseif ($serviceName === 'SA-I') $serviceClass = 'svc-sai';
                                                elseif (in_array($serviceName, ['SA-II', 'SA-III', 'SA-IV'])) $serviceClass = 'svc-saii';
                                                elseif ($serviceName === 'RC') $serviceClass = 'svc-rc';

                                                $instIcon = 'fa-credit-card';
                                                if (stripos($chequeDetails, 'chq') !== false || stripos($chequeDetails, 'cheque') !== false) {
                                                    $instIcon = 'fa-ticket';
                                                } elseif (stripos($chequeDetails, 'neft') !== false || stripos($chequeDetails, 'rtgs') !== false || stripos($chequeDetails, 'bank') !== false || stripos($chequeDetails, 'imps') !== false) {
                                                    $instIcon = 'fa-university';
                                                }
                                            ?>
                                                <tr id="pay_row_<?php echo $pay['payment_id']; ?>" data-customer-id="<?php echo $pay['customer_id']; ?>">
                                                    <td>
                                                        <a href="customerAccount.php?customerid=<?php echo $pay['customer_id']; ?>" class="unique-id-link" title="Open Customer Account: <?php echo $uniqueId ?: ('#' . $pay['customer_id']); ?>">
                                                            <?php echo $uniqueId ?: ('#' . $pay['customer_id']); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <strong style="color: #0f172a; font-weight: 700; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo $clientName; ?>"><?php echo $clientName; ?></strong>
                                                        <?php if (!empty($pay['contact_person'])) { ?>
                                                            <div style="font-size: 10.5px; color: #64748b; margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><i class="fa fa-user" style="font-size: 9.5px;"></i> <?php echo htmlspecialchars($pay['contact_person']); ?></div>
                                                        <?php } ?>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <span class="service-badge <?php echo $serviceClass; ?>">
                                                            <i class="fa fa-tag" style="font-size: 8.5px; opacity: 0.85;"></i>
                                                            <?php echo $serviceName; ?>
                                                        </span>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <strong style="color: #059669; font-size: 13px; font-weight: 800; white-space: nowrap;">₹<?php echo number_format($rcvAmt, 2); ?></strong>
                                                    </td>
                                                    <td style="text-align: center; white-space: nowrap;">
                                                        <span style="color: #0f172a; font-weight: 700; font-size: 11.5px; white-space: nowrap; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                                                            <i class="fa fa-calendar-o" style="color: #64748b; font-size: 10.5px;"></i>
                                                            <span><?php echo $dateFormatted; ?></span>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="instrument-badge" title="<?php echo $chequeDetails; ?>">
                                                            <i class="fa <?php echo $instIcon; ?>" style="color: #64748b; font-size: 9.5px;"></i> 
                                                            <?php echo $chequeDetails; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="rcv-comment-wrap">
                                                            <textarea class="rcv-comment-input" 
                                                                      data-pay-id="<?php echo htmlspecialchars($pay['payment_id'] ?? ''); ?>" 
                                                                      data-statement="<?php echo !empty($pay['is_statement']) ? '1' : '0'; ?>" 
                                                                      rows="2"
                                                                      placeholder=""><?php echo htmlspecialchars($pay['comment'] ?? ''); ?></textarea>
                                                        </div>
                                                    </td>
                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <button type="button" 
                                                                class="btn btn-xs btn-reconcile-toggle <?php echo $isReconciled ? 'reconcile-verified' : 'reconcile-pending'; ?>"
                                                                data-status="<?php echo $isReconciled ? 'Verified' : 'Verify'; ?>"
                                                                data-pay-id="<?php echo htmlspecialchars($pay['payment_id'] ?? ''); ?>"
                                                                data-client="<?php echo htmlspecialchars($clientName, ENT_QUOTES); ?>"
                                                                data-svc="<?php echo htmlspecialchars($serviceName, ENT_QUOTES); ?>"
                                                                data-amount="₹<?php echo number_format($rcvAmt, 2); ?>"
                                                                data-date="<?php echo $dateFormatted; ?>"
                                                                data-inst="<?php echo htmlspecialchars($chequeDetails, ENT_QUOTES); ?>"
                                                                data-statement="<?php echo !empty($pay['is_statement']) ? '1' : '0'; ?>"
                                                                title="Click to toggle Bank Statement Reconciliation">
                                                            <i class="fa <?php echo $isReconciled ? 'fa-check-circle' : 'fa-clock-o'; ?>"></i>
                                                            <span><?php echo $statusText; ?></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function debounce(callback, delay) {
        var timer;
        return function () {
            var context = this;
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, delay);
        };
    }

    $(document).ready(function () {
        var debouncedRcvSearch = debounce(function () {
            table.draw();
        }, 150);

        $('#clientSearch').on('input keyup change', debouncedRcvSearch);
        $('#startDate, #endDate').on('change', function () {
            table.draw();
        });

        setTimeout(function() {
            if (typeof $.fn.datepicker !== 'undefined') {
                $('.datepicker-rcv').datepicker({
                    minDate: '-100Y',
                    maxDate: '+100Y',
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-20:+100",
                    dateFormat: 'dd.mm.yy',
                    onSelect: function() {
                        $('#receivedFilterForm').submit();
                    }
                });
            }
        }, 100);

        var table = $('#table_received_payments').DataTable({
            "scrollY": '68vh',
            "scrollCollapse": true,
            "paging": true,
            "pageLength": 50,
            "ordering": false,
            "autoWidth": false,
            "lengthChange": false,
            "dom": "tr<'row dt-bottom-row'<'col-sm-6 col-xs-12'i><'col-sm-6 col-xs-12'p>>",
            "columnDefs": [
                { "width": "10%", "targets": 0, "className": "text-nowrap" },
                { "width": "24%", "targets": 1 },
                { "width": "7%", "targets": 2, "className": "text-nowrap text-center" },
                { "width": "11%", "targets": 3, "className": "text-nowrap text-right" },
                { "width": "9%", "targets": 4, "className": "text-nowrap text-center" },
                { "width": "12%", "targets": 5, "className": "text-nowrap" },
                { "width": "14%", "targets": 6, "className": "text-nowrap" },
                { "width": "13%", "targets": 7, "className": "text-nowrap text-center" }
            ],
            "language": {
                "emptyTable": "No received payment entries found",
                "zeroRecords": "No matching payments found",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "initComplete": function() {
                var api = this.api();
                setTimeout(function() {
                    api.columns.adjust();
                }, 50);
            }
        });

        $(window).on('resize', function () {
            table.columns.adjust();
        });

        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
            setTimeout(function () {
                table.columns.adjust();
            }, 200);
        });

        // Interactive Bank Reconciliation Confirmation with Mandatory Acknowledgment
        $(document).on('click', '.btn-reconcile-toggle', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $btn = $(this);
            var currentStatus = $btn.attr('data-status');
            var isCurrentlyVerified = (currentStatus === 'Verified');
            var payId = $btn.attr('data-pay-id');
            var client = $btn.attr('data-client') || '';
            var svc = $btn.attr('data-svc') || '';
            var amount = $btn.attr('data-amount') || '';
            var pDate = $btn.attr('data-date') || '';
            var inst = $btn.attr('data-inst') || '-';
            var isStatement = $btn.attr('data-statement') || '0';

            if (!isCurrentlyVerified) {
                // User wants to Mark as Reconciled -> Show Acknowledgement modal
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '<span style="font-size: 19px; font-weight: 800; color: #0d4d8b;"><i class="fa fa-university" style="margin-right: 6px;"></i> Bank Statement Reconciliation</span>',
                        html: '<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-bottom: 12px; font-size: 13px; text-align: left; line-height: 1.6;">' +
                              '<div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 4px;"><strong>Client:</strong> <span style="color: #0f172a;">' + client + '</span></div>' +
                              '<div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 4px;"><strong>Service:</strong> <span style="color: #0369a1; font-weight: 700;">' + svc + '</span></div>' +
                              '<div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 4px;"><strong>Received Amount:</strong> <span style="color: #059669; font-weight: 800; font-size: 14px;">' + amount + '</span></div>' +
                              '<div style="display: flex; justify-content: space-between;"><strong>Date &amp; Instrument:</strong> <span>' + pDate + ' (' + inst + ')</span></div>' +
                              '</div>' +
                              '<div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 8px; padding: 12px 14px; text-align: left;">' +
                              '<label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-weight: 600; font-size: 13px; margin: 0; color: #92400e;">' +
                              '<input type="checkbox" id="bankAckCheckbox" style="margin-top: 3px; cursor: pointer; width: 18px; height: 18px; flex-shrink: 0; accent-color: #059669;">' +
                              '<span>I acknowledge that this amount is received in bank and that entry is reconciled with bank statement.</span>' +
                              '</label>' +
                              '</div>' +
                              '<div style="font-size: 11.5px; color: #64748b; text-align: left; margin-top: 6px;"><i class="fa fa-lock"></i> Once reconciled, this payment entry will become non-editable and protected from deletion.</div>',
                        showCancelButton: true,
                        confirmButtonColor: '#059669',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="fa fa-check"></i> Mark as Reconciled Payment',
                        cancelButtonText: 'Cancel',
                        focusConfirm: false,
                        didOpen: function () {
                            var confirmBtn = Swal.getConfirmButton();
                            confirmBtn.disabled = true;
                            var cb = document.getElementById('bankAckCheckbox');
                            if (cb) {
                                cb.addEventListener('change', function () {
                                    confirmBtn.disabled = !this.checked;
                                });
                            }
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            sendReconcileUpdate($btn, 'Verified', payId, isStatement);
                        }
                    });
                } else {
                    if (confirm('I acknowledge that this amount is received in bank and that entry is reconciled with bank statement.\n\nClick OK to Mark as Reconciled Payment.')) {
                        sendReconcileUpdate($btn, 'Verified', payId, isStatement);
                    }
                }
            } else {
                // Currently Verified -> Unreconcile Confirmation
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Unmark Reconciled Payment?',
                        text: 'Are you sure you want to change this payment back to unverified status?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Yes, Unmark'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            sendReconcileUpdate($btn, 'Verify', payId, isStatement);
                        }
                    });
                } else {
                    if (confirm('Are you sure you want to change this payment back to unverified status?')) {
                        sendReconcileUpdate($btn, 'Verify', payId, isStatement);
                    }
                }
            }
        });

        function sendReconcileUpdate($btn, newStatus, payId, isStatement) {
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                url: 'addPayment.php',
                type: 'POST',
                data: {
                    action: 'updateReconcileStatus',
                    payment_id: payId,
                    status: newStatus,
                    is_statement: isStatement
                },
                dataType: 'json',
                success: function (res) {
                    $btn.prop('disabled', false);
                    if (res && res.success) {
                        $btn.attr('data-status', newStatus);
                        if (newStatus === 'Verified') {
                            $btn.removeClass('reconcile-pending').addClass('reconcile-verified')
                                .html('<i class="fa fa-check-circle"></i> <span>Reconciled</span>');
                        } else {
                            $btn.removeClass('reconcile-verified').addClass('reconcile-pending')
                                .html('<i class="fa fa-clock-o"></i> <span>Reconcile</span>');
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: newStatus === 'Verified' ? 'Reconciled!' : 'Unmarked!',
                                text: newStatus === 'Verified' ? 'Payment reconciled with bank statement.' : 'Status updated.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: (res && res.message) ? res.message : 'Failed to update reconcile status.'
                            });
                        } else {
                            alert('Error: ' + ((res && res.message) ? res.message : 'Failed to update reconcile status.'));
                        }
                    }
                },
                error: function (xhr, status, err) {
                    $btn.prop('disabled', false);
                    alert('Network error while updating reconcile status.');
                }
            });
        }

        // Auto-save Comment on change or blur
        $(document).on('change', '.rcv-comment-input', function () {
            var $el = $(this);
            var payId = $el.attr('data-pay-id');
            var isStatement = $el.attr('data-statement') || '0';
            var commentVal = $el.val();
            
            $el.css('opacity', '0.65');
            $.ajax({
                url: 'addPayment.php',
                type: 'POST',
                data: {
                    action: 'updatePaymentComment',
                    payment_id: payId,
                    comment: commentVal,
                    is_statement: isStatement
                },
                dataType: 'json',
                success: function (res) {
                    $el.css('opacity', '1');
                    if (res && res.success) {
                        $el.addClass('rcv-comment-saved');
                        setTimeout(function() {
                            $el.removeClass('rcv-comment-saved');
                        }, 1200);
                    }
                },
                error: function () {
                    $el.css('opacity', '1');
                }
            });
        });
    });
</script>

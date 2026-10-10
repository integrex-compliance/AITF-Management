<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
include_once './dbConnect.php';
require_once __DIR__ . '/utility.php';
require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/auditor_portal_helper.php';
date_default_timezone_set('Asia/Kolkata');

// Check auditor role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Auditor' || empty($_SESSION['auditor_id'])) {
    header('Location: login.php?reason=login_required');
    exit;
}

$auditorId = (int)$_SESSION['auditor_id'];
$data = aitf_fetch_auditor_dashboard_data($db, $auditorId);
if (!$data) {
    header('Location: login.php?reason=login_required');
    exit;
}

$auditor = $data['auditor'];
// Determine the correct user_id from the user table
// Forms are assigned using the user_id from the `user` table, not `auditors_id`.
$mappedUserId = (int)$_SESSION['id']; 
$staffUserRow = null;
try {
    $loginEmail = !empty($auditor['login_email']) ? $auditor['login_email'] : ($auditor['email'] ?? '');
    $eid = !empty($auditor['auditor_number']) ? $auditor['auditor_number'] : '';
    
    // Look up by user_id first if $mappedUserId > 0, else by email, e_id, or name
    if ($mappedUserId > 0) {
        $uStmt = $db->prepare("SELECT * FROM user WHERE user_id = :uid LIMIT 1");
        $uStmt->execute([':uid' => $mappedUserId]);
        $staffUserRow = $uStmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$staffUserRow) {
        $uStmt = $db->prepare("SELECT * FROM user WHERE role = 'Auditor' AND ((LOWER(email_id) = LOWER(:email) AND email_id != '') OR (e_id = :eid AND e_id != '') OR LOWER(user_name) = LOWER(:uname)) LIMIT 1");
        $uStmt->execute([':email' => $loginEmail, ':eid' => $eid, ':uname' => $auditor['auditor_name']]);
        $staffUserRow = $uStmt->fetch(PDO::FETCH_ASSOC);
        if ($staffUserRow) {
            $mappedUserId = (int)$staffUserRow['user_id'];
        }
    }
} catch (Exception $e) {}

// Profile & Dashboard values (from user table if linked, fallback to auditor table)
$audStaffName = !empty($staffUserRow['user_name']) ? $staffUserRow['user_name'] : $auditor['auditor_name'];
$audStaffEmail = !empty($staffUserRow['email_id']) ? $staffUserRow['email_id'] : (!empty($auditor['login_email']) ? $auditor['login_email'] : ($auditor['email'] ?? ''));
$audStaffRole = !empty($staffUserRow['role']) ? $staffUserRow['role'] : 'Auditor';
$audStaffEid = !empty($staffUserRow['e_id']) ? $staffUserRow['e_id'] : (!empty($auditor['auditor_number']) ? $auditor['auditor_number'] : ($auditor['auditor_no'] ?? ''));
$audStaffPcId = !empty($staffUserRow['pc_id']) ? $staffUserRow['pc_id'] : '';
$audStaffContact = !empty($staffUserRow['contact_no']) ? $staffUserRow['contact_no'] : ($auditor['phone'] ?? '');
$audStaffStatus = !empty($staffUserRow['account_status']) ? $staffUserRow['account_status'] : 'active';
$audStaffAbbr = !empty($staffUserRow['abbreviation']) ? $staffUserRow['abbreviation'] : ($auditor['abbrevation'] ?? '');
$audDesignation = !empty($staffUserRow['designation_assigned']) ? trim($staffUserRow['designation_assigned']) : 'Senior Auditor';
$audApplicableRecords = !empty($staffUserRow['applicable_records']) ? trim($staffUserRow['applicable_records']) : '';
$audWorkDescription = !empty($staffUserRow['work_description']) ? trim($staffUserRow['work_description']) : '';
$audResourceIssue = !empty($staffUserRow['resource_issue_record']) ? trim($staffUserRow['resource_issue_record']) : '';

require_once __DIR__ . '/include/staff_guidelines_lib.php';
$guidelinesList = aitf_get_guidelines_for_current_user($db, $mappedUserId, 'Auditor', $audStaffEmail, $audStaffName);

// Function parseSectionGroups if not defined
if (!function_exists('parseSectionGroups')) {
    function parseSectionGroups($rawContent) {
        $groups = [];
        $rawContent = trim((string)$rawContent);
        if ($rawContent === '') return $groups;

        if ($rawContent[0] === '[' || $rawContent[0] === '{') {
            $decoded = json_decode($rawContent, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_array($item)) {
                        $header = trim($item['header'] ?? '');
                        $descRaw = trim($item['desc'] ?? '');
                        $items = [];
                        if ($descRaw !== '') {
                            $lines = preg_split('/\r\n|\r|\n/', $descRaw);
                            foreach ($lines as $l) {
                                $tl = trim($l);
                                if ($tl !== '') {
                                    $clean = preg_replace('/^\s*\d+[\.\)\-]\s*/', '', $tl);
                                    if ($clean !== '') $items[] = $clean;
                                }
                            }
                        }
                        if ($header !== '' || !empty($items)) {
                            $groups[] = ['header' => $header, 'items' => $items];
                        }
                    }
                }
                if (!empty($groups)) return $groups;
            }
        }

        $lines = preg_split('/\r\n|\r|\n/', $rawContent);
        $currentHeader = '';
        $currentItems = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;
            if (preg_match('/^([A-Za-z0-9\s\&\(\)\/]+)[\-\:]$/', $trimmed, $m) && strlen(trim($m[1])) > 3) {
                if (!empty($currentItems) || $currentHeader !== '') {
                    $groups[] = ['header' => $currentHeader, 'items' => $currentItems];
                    $currentItems = [];
                }
                $currentHeader = trim($m[1]);
            } else {
                $clean = preg_replace('/^\s*\d+[\.\)\-]\s*/', '', $trimmed);
                if ($clean !== '') $currentItems[] = $clean;
            }
        }
        if (!empty($currentItems) || $currentHeader !== '') {
            $groups[] = ['header' => $currentHeader, 'items' => $currentItems];
        }
        return $groups;
    }
}

$audRecordGroups   = parseSectionGroups($audApplicableRecords);
$audWorkDescGroups = parseSectionGroups($audWorkDescription);
$audResIssueGroups = parseSectionGroups($audResourceIssue);

// Initials for avatar
$audInitials = '';
$cleanName = preg_replace('/[^a-zA-Z0-9\s]/', '', $audStaffName);
$nameParts = array_values(array_filter(preg_split('/\s+/', trim($cleanName))));
if (count($nameParts) >= 2) {
    $audInitials = strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr(end($nameParts), 0, 1));
} elseif (count($nameParts) === 1) {
    $firstWord = $nameParts[0];
    $audInitials = strtoupper(mb_substr($firstWord, 0, min(2, mb_strlen($firstWord))));
}
if (empty($audInitials) && !empty($audStaffAbbr)) {
    $audInitials = strtoupper(substr($audStaffAbbr, 0, 2));
}
if (empty($audInitials)) {
    $audInitials = strtoupper(substr($audStaffName, 0, 1));
}

$auditor = $data['auditor'];
$stats = $data['stats'];
$audits = $data['audits'];
$clients = $data['clients'];
$futureAudits = $data['future_audits'];
$clientServices = aitf_fetch_auditor_client_services($db, $auditorId, $auditor['auditor_name']);

// Also fetch SA-I count from stats
$saiCount = 0;
foreach ($audits as $a) {
    if ($a['service_type'] === 'SA-I') $saiCount++;
}
$stats['sa1'] = $saiCount;

// Get full client details for clients tab
$clientDetails = array();
if (!empty($clients)) {
    $clientIds = array_column($clients, 'customer_id');
    if (!empty($clientIds)) {
        $placeholders = implode(',', array_fill(0, count($clientIds), '?'));
        $stmt = $db->prepare("SELECT ci.*, s.service_type, s.service_status, s.service_id
            FROM customer_info ci
            LEFT JOIN service s ON s.customer_id = ci.id AND (s.is_deleted IS NULL OR s.is_deleted <> 'Y')
            WHERE ci.id IN ($placeholders)
            ORDER BY ci.customer_name ASC");
        $stmt->execute($clientIds);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $cid = $r['id'];
            if (!isset($clientDetails[$cid])) {
                $clientDetails[$cid] = $r;
                $clientDetails[$cid]['services'] = array();
            }
            if (!empty($r['service_type'])) {
                $clientDetails[$cid]['services'][] = array(
                    'type' => $r['service_type'],
                    'status' => $r['service_status'],
                    'service_id' => $r['service_id']
                );
            }
        }
    }
}

// Group audits by date for timeline
$auditsByDate = array();
foreach ($audits as $a) {
    $date = $a['parsed_audit_date'] ?: 'Unknown';
    $auditsByDate[$date][] = $a;
}
krsort($auditsByDate);

aitf_touch_login_activity($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AITF - Auditor Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    
    <style>
        :root {
            --primary: #0F2742;
            --primary-light: #1e3a5f;
            --accent-green: #10B981;
            --accent-amber: #F59E0B;
            --accent-blue: #3B82F6;
            --accent-red: #EF4444;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --sidebar-bg: #ffffff;
            --nav-bg: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.2);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        html.theme-dark {
            --primary: #60a5fa;
            --primary-light: #3b82f6;
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --sidebar-bg: #1e293b;
            --nav-bg: #1e293b;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.05);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.3);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.3);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.3);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            transition: background-color 0.3s, color 0.3s;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Navigation */
        .topbar {
            height: 70px;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px 0 0;
            box-shadow: var(--shadow-sm);
        }

        .topbar-brand-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 180px;
            height: 70px;
            background: linear-gradient(180deg, #081524 0%, #053c6d 100%);
            padding: 0 12px;
            box-sizing: border-box;
            border-right: 1px solid rgba(255, 255, 255, 0.12);
            flex-shrink: 0;
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-right: 12px;
        }

        .theme-toggle {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 20px;
            cursor: pointer;
            transition: var(--transition);
        }

        .theme-toggle:hover {
            color: var(--primary);
        }
        html.theme-dark .theme-toggle:hover { color: #f1f5f9; }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--text-main);
        }

        .user-role {
            font-size: 12px;
            color: var(--text-muted);
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-blue);
            padding: 2px 8px;
            border-radius: 12px;
            display: inline-block;
            margin-top: 2px;
        }

        .logout-btn {
            background: rgba(239, 68, 68, 0.1);
            color: var(--accent-red);
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: var(--accent-red);
            color: white;
        }

        /* Layout */
        .app-container {
            display: flex;
            margin-top: 70px;
            min-height: calc(100vh - 70px);
        }

        /* ----------------------------------------------------------- */
        /* AITF Signature Left Sidebar Styling                         */
        /* ----------------------------------------------------------- */
        .sidebar {
            width: 180px !important;
            background: linear-gradient(180deg, #0d4d8b 0%, #083b6a 100%) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
            position: fixed !important;
            top: 70px !important;
            height: calc(100vh - 70px) !important;
            padding: 0 !important;
            z-index: 99 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            box-shadow: 3px 0 16px rgba(13, 77, 139, 0.22) !important;
            scrollbar-width: thin !important;
            scrollbar-color: rgba(255, 255, 255, 0.25) transparent !important;
            transition: var(--transition) !important;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 4px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.45);
        }

        .sidebar-nav {
            display: block;
            width: 100%;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            color: #f1f5f9 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            text-decoration: none !important;
            width: 100% !important;
            background: transparent !important;
            border: none !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-left: 3.5px solid transparent !important;
            padding: 10.5px 14px !important;
            box-sizing: border-box !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            letter-spacing: 0.45px !important;
            text-transform: uppercase !important;
            line-height: 1.25 !important;
            transition: all .18s cubic-bezier(0.4, 0, 0.2, 1) !important;
            cursor: pointer !important;
        }

        .nav-item:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.14) !important;
            text-decoration: none !important;
        }

        .nav-item:hover .sub_icon {
            color: #ffffff !important;
            transform: scale(1.15) !important;
        }

        /* Active class - AITF Signature deep blue with cyan accent */
        .nav-item.active {
            color: #ffffff !important;
            background: #062b4f !important;
            border-left: 3.5px solid #38bdf8 !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            box-shadow: inset 0 0 14px rgba(6, 43, 79, 0.6) !important;
        }

        .nav-item.active .sub_icon {
            color: #38bdf8 !important;
            transform: scale(1.1) !important;
        }

        .nav-item .sub_icon {
            font-size: 13.5px !important;
            width: 20px !important;
            text-align: right !important;
            vertical-align: middle !important;
            color: #cbd5e1 !important;
            flex-shrink: 0 !important;
            margin-left: auto !important;
            transition: all 0.18s ease !important;
        }

        /* Guidelines Menu Item — Distinct Vibrant Emerald Styling matching AITF */
        .nav-item.guidelines-nav-link {
            color: #34d399 !important;
            font-weight: 700 !important;
            letter-spacing: 0.5px !important;
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.04) 100%) !important;
            border-left: 3.5px solid #10b981 !important;
            border-bottom: 1px solid rgba(16, 185, 129, 0.22) !important;
        }
        .nav-item.guidelines-nav-link .sub_icon {
            color: #34d399 !important;
            text-shadow: 0 0 8px rgba(52, 211, 153, 0.5) !important;
        }
        .nav-item.guidelines-nav-link:hover {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.35) 0%, rgba(16, 185, 129, 0.12) 100%) !important;
            color: #ecfdf5 !important;
        }
        .nav-item.guidelines-nav-link.active {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.45) 0%, rgba(16, 185, 129, 0.2) 100%) !important;
            border-left: 3.5px solid #34d399 !important;
            color: #ffffff !important;
            box-shadow: inset 0 0 12px rgba(16, 185, 129, 0.3) !important;
        }
        .nav-item.guidelines-nav-link.active .sub_icon {
            color: #ffffff !important;
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: #ffffff;
            font-size: 18px;
            cursor: pointer;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 180px !important;
            padding: 32px;
            transition: var(--transition);
        }

        /* Dark Mode Overrides for AITF Sidebar */
        html.theme-dark .sidebar {
            background: linear-gradient(180deg, #091d33 0%, #051424 100%) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.07) !important;
        }
        html.theme-dark .topbar-brand-wrap {
            background: linear-gradient(180deg, #05101a 0%, #072644 100%) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        html.theme-dark .nav-item.active {
            background: #08213b !important;
            border-left: 3.5px solid #38bdf8 !important;
        }
        
        .tab-pane {
            display: none;
            animation: fadeIn 0.4s ease-out;
        }
        
        .tab-pane.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Dashboard Content */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 16px;
            padding: 32px;
            color: white;
            margin-bottom: 32px;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 50%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(-45deg);
        }
        
        html.theme-dark .welcome-banner {
            background: linear-gradient(135deg, #1e3a5f, #0f172a);
        }

        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .welcome-date {
            font-size: 16px;
            opacity: 0.8;
            position: relative;
            z-index: 1;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-icon.total { background: rgba(59, 130, 246, 0.1); color: var(--accent-blue); }
        .stat-icon.ca { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); }
        .stat-icon.sa1 { background: rgba(245, 158, 11, 0.1); color: var(--accent-amber); }
        .stat-icon.sa2 { background: rgba(239, 68, 68, 0.1); color: var(--accent-red); }
        .stat-icon.rc { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 4px;
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
        }

        /* Sections */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-main);
        }

        /* Upcoming Audits */
        .upcoming-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .upcoming-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .upcoming-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .upcoming-client {
            font-weight: 600;
            font-size: 16px;
            color: var(--text-main);
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-ca { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); }
        .badge-sa1 { background: rgba(245, 158, 11, 0.1); color: var(--accent-amber); }
        .badge-sa2 { background: rgba(239, 68, 68, 0.1); color: var(--accent-red); }
        .badge-rc { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

        .upcoming-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .upcoming-details i {
            width: 16px;
            color: var(--primary-light);
        }

        /* Timeline / History */
        .timeline-container {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }
        
        .timeline-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .timeline-table th {
            text-align: left;
            padding: 12px 16px;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .timeline-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        
        .timeline-table tr:last-child td {
            border-bottom: none;
        }

        .timeline-date {
            font-weight: 600;
            color: var(--text-main);
        }

        /* Clients Tab */
        .search-container {
            margin-bottom: 24px;
            position: relative;
        }

        .search-input {
            width: 100%;
            max-width: 400px;
            padding: 12px 20px 12px 48px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            color: var(--text-main);
            font-size: 15px;
            transition: var(--transition);
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .client-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .client-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            cursor: pointer;
            text-decoration: none;
            display: block;
        }
        
        .client-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            text-decoration: none;
        }

        .client-header {
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
        }

        .client-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .client-id {
            font-size: 13px;
            color: var(--text-muted);
            font-family: monospace;
            background: rgba(100, 116, 139, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .client-services {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .service-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            border: 1px solid;
        }

        .service-active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-green);
            border-color: rgba(16, 185, 129, 0.2);
        }

        .service-locked {
            background: rgba(100, 116, 139, 0.1);
            color: var(--text-muted);
            border-color: rgba(100, 116, 139, 0.2);
            filter: grayscale(1);
            opacity: 0.7;
        }

        /* Profile Tab */
        .profile-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 32px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            max-width: 600px;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 32px;
        }

        .profile-info h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
            color: var(--text-main);
        }

        .profile-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .detail-label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }
        
        .detail-value {
            font-size: 16px;
            color: var(--text-main);
            font-weight: 600;
        }

        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
        }
        
        .empty-state i {
            font-size: 48px;
            color: var(--text-muted);
            margin-bottom: 16px;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block !important;
            }
            .topbar {
                padding: 0 14px 0 0 !important;
            }
            .topbar-brand-wrap {
                width: 170px !important;
                padding: 0 12px !important;
            }
            .sidebar {
                transform: translateX(-100%) !important;
                width: 180px !important;
            }
            
            .sidebar.show {
                transform: translateX(0) !important;
                box-shadow: var(--shadow-lg) !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 20px 14px !important;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .timeline-table {
                display: block;
                overflow-x: auto;
            }
            
            .topbar-right .user-name,
            .topbar-right .user-role {
                display: none;
            }
        }
        
        /* ----------------------------------------------------------- */
        /* My Dashboard Integration Styles                             */
        /* ----------------------------------------------------------- */
        .dash-switch-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%);
            color: #ffffff !important;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none !important;
            box-shadow: 0 2px 6px rgba(13, 77, 139, 0.25);
            transition: all 0.2s ease;
        }
        .dash-switch-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.35);
            color: #ffffff !important;
        }

        /* My Dashboard Inner Tabs Bar */
        .mydash-tabs-bar {
            display: flex;
            align-items: center;
            gap: 6px;
            background: var(--card-bg);
            padding: 8px 12px;
            border-radius: 12px;
            margin-bottom: 22px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            flex-wrap: wrap;
        }

        .mydash-tab-btn {
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s ease;
        }

        .mydash-tab-btn:hover {
            color: var(--primary);
            background: rgba(13, 77, 139, 0.08);
        }

        html.theme-dark .mydash-tab-btn:hover {
            color: #93c5fd;
            background: rgba(255, 255, 255, 0.06);
        }

        .mydash-tab-btn.active {
            background: #0d4d8b;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(13, 77, 139, 0.25);
        }

        html.theme-dark .mydash-tab-btn.active {
            background: #2563eb;
            color: #ffffff !important;
        }

        .mydash-tab-pane {
            display: none;
        }

        .mydash-tab-pane.active {
            display: block;
            animation: fadeInTab 0.2s ease;
        }

        .header-theme-attendance {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
            color: #ffffff !important;
        }
        .header-theme-tasks {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            color: #ffffff !important;
        }
        .header-theme-guidelines {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: #ffffff !important;
        }

        /* Profile Hero Card */
        .mydash-hero-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 22px;
            overflow: hidden;
        }

        .mydash-hero-top {
            background: linear-gradient(135deg, #091e3a 0%, #0d4d8b 55%, #1d4ed8 100%);
            padding: 12px 20px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .mydash-hero-body {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--border-color);
        }

        .mydash-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .mydash-avatar-circle {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 50%, #2563eb 100%);
            color: #ffffff;
            font-size: 28px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(13, 77, 139, 0.28);
            border: 3.5px solid var(--card-bg);
            text-transform: uppercase;
        }

        .mydash-avatar-status {
            position: absolute;
            bottom: 3px;
            right: 3px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #10b981;
            border: 3px solid var(--card-bg);
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
        }

        .mydash-hero-info {
            flex: 1;
            min-width: 220px;
        }

        .mydash-hero-name {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main);
            margin: 0 0 4px 0;
            letter-spacing: -0.02em;
        }

        .mydash-hero-sub {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0 0 10px 0;
        }

        .mydash-badges-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .mydash-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            transition: all 0.15s ease;
        }

        .mydash-badge.badge-designation {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
        .mydash-badge.badge-role {
            background: #faf5ff;
            border: 1px solid #e9d5ff;
            color: #6b21a8;
        }
        .mydash-badge.badge-eid {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
        .mydash-badge.badge-pc {
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            color: #6d28d9;
        }
        .mydash-badge.badge-status {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        /* Basic Details Grid */
        .mydash-details-grid {
            padding: 18px 24px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            background: var(--bg-color);
        }

        .mydash-detail-box {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: transform 0.15s;
        }
        .mydash-detail-box:hover {
            transform: translateY(-1px);
        }

        .mydash-detail-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .mydash-detail-icon.icon-blue { background: #eff6ff; color: #2563eb; }
        .mydash-detail-icon.icon-purple { background: #f5f3ff; color: #7c3aed; }
        .mydash-detail-icon.icon-green { background: #ecfdf5; color: #059669; }

        .mydash-detail-text {
            min-width: 0;
            flex: 1;
        }
        .mydash-detail-lbl {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--text-muted);
            margin-bottom: 2px;
        }
        .mydash-detail-val {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text-main);
            word-break: break-word;
        }

        /* Records Container & Themes */
        .profile-records-container {
            border-radius: 12px;
            padding: 16px 18px;
            display: block;
            width: 100%;
            height: auto;
            min-height: 100%;
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .records-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 9px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .records-title-main {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            flex: 1;
        }

        .records-title-icon-wrap {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13.5px;
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .records-title-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .records-title-heading {
            font-size: 13.5px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
        }

        .records-title-sub {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            line-height: 1.2;
            margin-top: 2px;
        }

        .records-badge-count {
            background: rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 12px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .records-clean-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin: 0;
            padding: 0;
        }

        .record-clean-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: var(--card-bg);
            border-radius: 8px;
            padding: 10px 14px;
            transition: all 0.15s ease;
            width: 100%;
            box-sizing: border-box;
        }
        .record-clean-item:hover {
            transform: translateX(2px);
        }

        .record-clean-num {
            font-size: 11px;
            font-weight: 700;
            border-radius: 6px;
            padding: 2px 7px;
            flex-shrink: 0;
            margin-top: 1px;
            line-height: 1.3;
        }

        .record-clean-icon {
            font-size: 13px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .record-clean-text {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-main);
            line-height: 1.5;
            word-break: break-word;
            flex: 1;
        }

        .records-empty-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 24px 16px;
            border: 1px dashed var(--border-color);
            border-radius: 8px;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.6);
            margin: 6px 0;
        }

        .record-category-card {
            border-radius: 8px;
            margin-bottom: 14px;
            overflow: visible;
            flex-shrink: 0;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .record-category-card:last-child {
            margin-bottom: 0;
        }

        .record-category-header {
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border-radius: 7px 7px 0 0;
        }

        .record-category-card .records-clean-list {
            padding: 10px 12px;
            gap: 8px;
            border-radius: 0 0 7px 7px;
        }

        /* 1. BLUE / SAPPHIRE THEME (Applicable Records) */
        .records-theme-blue {
            background: #f8fbff;
            border: 1.5px solid #bfdbfe;
        }
        .records-theme-blue .records-section-title {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 55%, #3b82f6 100%);
        }
        .records-theme-blue .record-category-card {
            background: #ffffff;
            border: 1px solid #bfdbfe;
        }
        .records-theme-blue .record-category-header {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-bottom: 1px solid #bfdbfe;
            color: #1e3a8a;
        }
        .records-theme-blue .record-category-header i { color: #2563eb; }
        .records-theme-blue .record-clean-item {
            background: #ffffff;
            border: 1px solid #e0e7ff;
        }
        .records-theme-blue .record-clean-num {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .records-theme-blue .record-clean-icon { color: #3b82f6; }
        .records-theme-blue .category-count-badge {
            background: #dbeafe;
            color: #1e40af;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
        }

        /* 2. EMERALD / GREEN THEME (Work Description) */
        .records-theme-emerald {
            background: #f6fdf9;
            border: 1.5px solid #a7f3d0;
        }
        .records-theme-emerald .records-section-title {
            background: linear-gradient(135deg, #065f46 0%, #059669 55%, #10b981 100%);
        }
        .records-theme-emerald .record-category-card {
            background: #ffffff;
            border: 1px solid #a7f3d0;
        }
        .records-theme-emerald .record-category-header {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-bottom: 1px solid #a7f3d0;
            color: #064e3b;
        }
        .records-theme-emerald .record-category-header i { color: #059669; }
        .records-theme-emerald .record-clean-item {
            background: #ffffff;
            border: 1px solid #d1fae5;
        }
        .records-theme-emerald .record-clean-num {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .records-theme-emerald .record-clean-icon { color: #10b981; }
        .records-theme-emerald .category-count-badge {
            background: #d1fae5;
            color: #065f46;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
        }

        /* 3. AMBER / ORANGE THEME (Resource Issue Record) */
        .records-theme-amber {
            background: #fffdf7;
            border: 1.5px solid #fde68a;
        }
        .records-theme-amber .records-section-title {
            background: linear-gradient(135deg, #92400e 0%, #d97706 55%, #f59e0b 100%);
        }
        .records-theme-amber .record-category-card {
            background: #ffffff;
            border: 1px solid #fde68a;
        }
        .records-theme-amber .record-category-header {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-bottom: 1px solid #fde68a;
            color: #78350f;
        }
        .records-theme-amber .record-category-header i { color: #d97706; }
        .records-theme-amber .record-clean-item {
            background: #ffffff;
            border: 1px solid #fed7aa;
        }
        .records-theme-amber .record-clean-num {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .records-theme-amber .record-clean-icon { color: #f59e0b; }
        .records-theme-amber .category-count-badge {
            background: #fef3c7;
            color: #92400e;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
        }

        /* Dark mode overrides for My Dashboard */
        html.theme-dark .mydash-badge.badge-designation { background: #1e293b; border-color: #3b82f6; color: #60a5fa; }
        html.theme-dark .mydash-badge.badge-role { background: #1e293b; border-color: #a855f7; color: #c084fc; }
        html.theme-dark .mydash-badge.badge-eid { background: #1e293b; border-color: #3b82f6; color: #93c5fd; }
        html.theme-dark .mydash-badge.badge-pc { background: #1e293b; border-color: #8b5cf6; color: #c4b5fd; }
        html.theme-dark .mydash-badge.badge-status { background: #1e293b; border-color: #10b981; color: #34d399; }
        
        html.theme-dark .records-theme-blue {
            background: #0d1527 !important;
            border-color: #1e3a8a !important;
        }
        html.theme-dark .records-theme-blue .records-section-title {
            background: linear-gradient(135deg, #172554 0%, #1e3a8a 55%, #1d4ed8 100%) !important;
        }
        html.theme-dark .records-theme-blue .record-category-card {
            background: #111a2e !important;
            border-color: #1e3a8a !important;
        }
        html.theme-dark .records-theme-blue .record-category-header {
            background: #162444 !important;
            border-bottom-color: #1e3a8a !important;
            color: #93c5fd !important;
        }
        html.theme-dark .records-theme-blue .record-clean-item {
            background: #0f172a !important;
            border-color: #1e3a8a !important;
        }
        html.theme-dark .records-theme-blue .record-clean-num {
            background: #1e3a8a !important;
            border-color: #2563eb !important;
            color: #bfdbfe !important;
        }

        html.theme-dark .records-theme-emerald {
            background: #091a14 !important;
            border-color: #065f46 !important;
        }
        html.theme-dark .records-theme-emerald .records-section-title {
            background: linear-gradient(135deg, #022c22 0%, #065f46 55%, #047857 100%) !important;
        }
        html.theme-dark .records-theme-emerald .record-category-card {
            background: #0c241c !important;
            border-color: #065f46 !important;
        }
        html.theme-dark .records-theme-emerald .record-category-header {
            background: #103328 !important;
            border-bottom-color: #065f46 !important;
            color: #a7f3d0 !important;
        }
        html.theme-dark .records-theme-emerald .record-clean-item {
            background: #091a14 !important;
            border-color: #065f46 !important;
        }
        html.theme-dark .records-theme-emerald .record-clean-num {
            background: #065f46 !important;
            border-color: #059669 !important;
            color: #d1fae5 !important;
        }

        html.theme-dark .records-theme-amber {
            background: #1c1507 !important;
            border-color: #92400e !important;
        }
        html.theme-dark .records-theme-amber .records-section-title {
            background: linear-gradient(135deg, #451a03 0%, #78350f 55%, #b45309 100%) !important;
        }
        html.theme-dark .records-theme-amber .record-category-card {
            background: #241c0e !important;
            border-color: #92400e !important;
        }
        html.theme-dark .records-theme-amber .record-category-header {
            background: #332512 !important;
            border-bottom-color: #92400e !important;
            color: #fde68a !important;
        }
        html.theme-dark .records-theme-amber .record-clean-item {
            background: #1c1507 !important;
            border-color: #78350f !important;
        }
        html.theme-dark .records-theme-amber .record-clean-num {
            background: #78350f !important;
            border-color: #92400e !important;
            color: #fef3c7 !important;
        }
        html.theme-dark .records-empty-placeholder {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }
    </style>
</head>
<body>

    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-brand-wrap">
            <a href="auditorDashboard.php" class="topbar-brand" title="AITF ERP">
                <div style="display: inline-flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1;">
                    <span style="font-family: 'Poppins', 'Inter', sans-serif; font-weight: 800; font-size: 24px; letter-spacing: 1px; line-height: 1;">
                        <span style="color: #CF2120;">A</span><span style="color: #ffffff;">IT</span><span style="color: #CF2120;">F</span>
                    </span>
                    <span style="font-family: 'Poppins', 'Inter', sans-serif; font-weight: 600; font-size: 10px; color: #ffffff; letter-spacing: 2px; margin-top: 2px; line-height: 1;">ERP</span>
                </div>
            </a>
            <button class="mobile-toggle" id="mobileToggle" title="Toggle Navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <div class="topbar-right">
            <a href="myDashboard.php" class="dash-switch-pill" title="Open My Staff Profile & Responsibilities Dashboard">
                <i class="fas fa-id-card"></i>
                <span class="hidden-xs">My Dashboard</span>
            </a>
            <button class="theme-toggle" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>
            <div class="user-profile">
                <div class="avatar">
                    <?php echo strtoupper(substr($auditor['auditor_name'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($auditor['auditor_name']); ?></span>
                    <span class="user-role">Auditor</span>
                </div>
            </div>
            <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to log out?');">
                <i class="fas fa-sign-out-alt"></i>
                <span class="hidden-xs">Logout</span>
            </a>
        </div>
    </header>

    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <nav class="sidebar-nav">
                <a class="nav-item active animate" data-tab="mydashboard" href="#mydashboard">
                    <span>MY DASHBOARD</span>
                    <i class="sub_icon fas fa-tachometer-alt"></i>
                </a>
                <a class="nav-item animate" data-tab="assignments" href="#assignments">
                    <span>MY ASSIGNMENTS</span>
                    <i class="sub_icon fas fa-tasks"></i>
                </a>
                <a class="nav-item animate" data-tab="clients" href="#clients">
                    <span>MY CLIENTS</span>
                    <i class="sub_icon fas fa-users"></i>
                </a>
                <a class="nav-item animate" data-tab="history" href="#history">
                    <span>AUDIT HISTORY</span>
                    <i class="sub_icon fas fa-history"></i>
                </a>
                <a class="nav-item animate" data-tab="performance" href="#performance">
                    <span>PERFORMANCE</span>
                    <i class="sub_icon fas fa-chart-line"></i>
                </a>
                <a class="nav-item animate guidelines-nav-link" data-tab="guidelines" href="#guidelines">
                    <span><i class="fas fa-book" style="color: #34d399; margin-right: 5px; font-size: 11px;"></i> GUIDELINES</span>
                    <i class="sub_icon fas fa-book"></i>
                </a>
                <?php if (Utitlity::checkUserPermission('client_access')) { ?>
                <a href="homePage.php" class="nav-item animate" style="color: #a5b4fc !important;">
                    <span>COMPANY CLIENTS</span>
                    <i class="sub_icon fas fa-building"></i>
                </a>
                <?php } ?>
                <?php if (Utitlity::checkUserPermission('invoice_access')) { ?>
                <a href="invoicePage.php" class="nav-item animate" style="color: #a5b4fc !important;">
                    <span>INVOICES</span>
                    <i class="sub_icon fas fa-rupee-sign"></i>
                </a>
                <?php } ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">

            <!-- My Staff Dashboard Tab -->
            <div id="tab-mydashboard" class="tab-pane active">
                
                <!-- Inner Tabs Bar for My Dashboard -->
                <div class="mydash-tabs-bar" role="tablist">
                    <button type="button" class="mydash-tab-btn active" data-target="#mydash-pane-profile">
                        <i class="fas fa-user-circle"></i> Profile
                    </button>
                    <button type="button" class="mydash-tab-btn" data-target="#mydash-pane-attendance">
                        <i class="fas fa-calendar-check"></i> Attendance
                    </button>
                    <button type="button" class="mydash-tab-btn" data-target="#mydash-pane-task-mgmt">
                        <i class="fas fa-tasks"></i> Task Management
                    </button>
                    <button type="button" class="mydash-tab-btn" data-target="#mydash-pane-guidelines">
                        <i class="fas fa-book"></i> Work Guidelines
                    </button>
                </div>

                <!-- 1. Profile Tab Pane -->
                <div class="mydash-tab-pane active" id="mydash-pane-profile">

                    <!-- Profile Hero Card -->
                    <div class="mydash-hero-card">
                    <div class="mydash-hero-top">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-user-circle"></i>
                            <span>Staff Profile &amp; Responsibilities</span>
                        </div>
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 3px 10px; border-radius: 20px; font-size: 11px; text-transform: none;">
                            <i class="fas fa-shield-alt"></i> <?php echo htmlspecialchars($audStaffRole); ?>
                        </span>
                    </div>

                    <div class="mydash-hero-body">
                        <div class="mydash-avatar-wrap">
                            <div class="mydash-avatar-circle" title="<?php echo htmlspecialchars($audStaffName); ?>">
                                <?php echo htmlspecialchars($audInitials); ?>
                            </div>
                            <div class="mydash-avatar-status" title="Active"></div>
                        </div>
                        <div class="mydash-hero-info">
                            <h3 class="mydash-hero-name"><?php echo htmlspecialchars($audStaffName); ?></h3>
                            <div class="mydash-hero-sub">
                                Assessment &amp; Institute for Technical Forum (AITF)
                            </div>
                            <div class="mydash-badges-row">
                                <div class="mydash-badge badge-designation" title="Designation Assigned">
                                    <i class="fas fa-briefcase"></i>
                                    <span><?php echo htmlspecialchars($audDesignation); ?></span>
                                </div>
                                <div class="mydash-badge badge-role" title="User Role">
                                    <i class="fas fa-shield-alt"></i>
                                    <span><?php echo htmlspecialchars($audStaffRole); ?></span>
                                </div>
                                <?php if (!empty($audStaffEid)): ?>
                                <div class="mydash-badge badge-eid" title="Employee ID">
                                    <i class="fas fa-id-badge"></i>
                                    <span>Emp ID: <?php echo htmlspecialchars($audStaffEid); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($audStaffPcId)): ?>
                                <div class="mydash-badge badge-pc" title="PC ID">
                                    <i class="fas fa-desktop"></i>
                                    <span>PC: <?php echo htmlspecialchars($audStaffPcId); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="mydash-badge badge-status" title="Account Status">
                                    <i class="fas fa-circle" style="font-size: 8px;"></i>
                                    <span><?php echo htmlspecialchars(ucfirst($audStaffStatus ?: 'Active')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="mydash-details-grid">
                        <div class="mydash-detail-box">
                            <span class="mydash-detail-icon icon-blue"><i class="fas fa-envelope"></i></span>
                            <div class="mydash-detail-text">
                                <div class="mydash-detail-lbl">Email Address</div>
                                <div class="mydash-detail-val"><?php echo $audStaffEmail !== '' ? htmlspecialchars($audStaffEmail) : '-'; ?></div>
                            </div>
                        </div>
                        <div class="mydash-detail-box">
                            <span class="mydash-detail-icon icon-purple"><i class="fas fa-shield-alt"></i></span>
                            <div class="mydash-detail-text">
                                <div class="mydash-detail-lbl">Role</div>
                                <div class="mydash-detail-val"><?php echo htmlspecialchars($audStaffRole); ?></div>
                            </div>
                        </div>
                        <?php if (!empty($audStaffEid)): ?>
                        <div class="mydash-detail-box">
                            <span class="mydash-detail-icon icon-blue"><i class="fas fa-id-card"></i></span>
                            <div class="mydash-detail-text">
                                <div class="mydash-detail-lbl">Employee / Auditor ID</div>
                                <div class="mydash-detail-val"><?php echo htmlspecialchars($audStaffEid); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($audStaffPcId)): ?>
                        <div class="mydash-detail-box">
                            <span class="mydash-detail-icon icon-purple"><i class="fas fa-desktop"></i></span>
                            <div class="mydash-detail-text">
                                <div class="mydash-detail-lbl">PC ID</div>
                                <div class="mydash-detail-val"><?php echo htmlspecialchars($audStaffPcId); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($audStaffContact)): ?>
                        <div class="mydash-detail-box">
                            <span class="mydash-detail-icon icon-green"><i class="fas fa-phone"></i></span>
                            <div class="mydash-detail-text">
                                <div class="mydash-detail-lbl">Contact</div>
                                <div class="mydash-detail-val"><?php echo htmlspecialchars($audStaffContact); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2 Column Row: Applicable Records & Work Description -->
                <div class="row" style="margin: 0 -8px 20px -8px; display: flex; flex-wrap: wrap;">
                    <!-- 1. APPLICABLE RECORDS (BLUE / SAPPHIRE THEME) -->
                    <div class="col-sm-6" style="padding: 0 8px; margin-bottom: 16px; display: flex; flex-direction: column;">
                        <div class="profile-records-container records-theme-blue" style="width: 100%; flex: 1;">
                            <div class="records-section-title">
                                <div class="records-title-main">
                                    <div class="records-title-icon-wrap">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                    <div class="records-title-text">
                                        <span class="records-title-heading">Applicable Records &amp; Documents</span>
                                        <span class="records-title-sub">Reporting documents &amp; location records</span>
                                    </div>
                                </div>
                                <?php if (!empty($audRecordGroups)): ?>
                                    <span class="records-badge-count"><?php echo count($audRecordGroups); ?> Group<?php echo count($audRecordGroups) > 1 ? 's' : ''; ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($audRecordGroups)): ?>
                                <?php foreach ($audRecordGroups as $grp): ?>
                                    <?php if (!empty($grp['header'])): ?>
                                        <div class="record-category-card">
                                            <div class="record-category-header">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <i class="far fa-folder"></i>
                                                    <span><?php echo htmlspecialchars($grp['header']); ?></span>
                                                </div>
                                                <?php if (!empty($grp['items'])): ?>
                                                    <span class="category-count-badge"><?php echo count($grp['items']); ?> item<?php echo count($grp['items']) > 1 ? 's' : ''; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($grp['items'])): ?>
                                                <div class="records-clean-list">
                                                    <?php foreach ($grp['items'] as $index => $item): ?>
                                                        <div class="record-clean-item">
                                                            <span class="record-clean-num"><?php echo ($index + 1); ?></span>
                                                            <i class="far fa-file-alt record-clean-icon"></i>
                                                            <span class="record-clean-text"><?php echo htmlspecialchars($item); ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="records-empty-placeholder" style="padding: 10px 14px;">No items specified under this header</div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif (!empty($grp['items'])): ?>
                                        <div class="records-clean-list" style="margin-bottom: 12px;">
                                            <?php foreach ($grp['items'] as $index => $item): ?>
                                                <div class="record-clean-item">
                                                    <span class="record-clean-num"><?php echo ($index + 1); ?></span>
                                                    <i class="far fa-file-alt record-clean-icon"></i>
                                                    <span class="record-clean-text"><?php echo htmlspecialchars($item); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="records-empty-placeholder">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Not assigned yet</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 2. WORK DESCRIPTION (EMERALD GREEN THEME) -->
                    <div class="col-sm-6" style="padding: 0 8px; margin-bottom: 16px; display: flex; flex-direction: column;">
                        <div class="profile-records-container records-theme-emerald" style="width: 100%; flex: 1;">
                            <div class="records-section-title">
                                <div class="records-title-main">
                                    <div class="records-title-icon-wrap">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div class="records-title-text">
                                        <span class="records-title-heading">Work Description &amp; Responsibilities</span>
                                        <span class="records-title-sub">Main Responsibilities, Authorities &amp; Reporting</span>
                                    </div>
                                </div>
                                <?php if (!empty($audWorkDescGroups)): ?>
                                    <span class="records-badge-count"><?php echo count($audWorkDescGroups); ?> Group<?php echo count($audWorkDescGroups) > 1 ? 's' : ''; ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($audWorkDescGroups)): ?>
                                <?php foreach ($audWorkDescGroups as $grp): ?>
                                    <?php if (!empty($grp['header'])): ?>
                                        <div class="record-category-card">
                                            <div class="record-category-header">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <i class="fas fa-tasks"></i>
                                                    <span><?php echo htmlspecialchars($grp['header']); ?></span>
                                                </div>
                                                <?php if (!empty($grp['items'])): ?>
                                                    <span class="category-count-badge"><?php echo count($grp['items']); ?> item<?php echo count($grp['items']) > 1 ? 's' : ''; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($grp['items'])): ?>
                                                <div class="records-clean-list">
                                                    <?php foreach ($grp['items'] as $index => $item): ?>
                                                        <div class="record-clean-item">
                                                            <span class="record-clean-num"><?php echo ($index + 1); ?></span>
                                                            <i class="far fa-check-square record-clean-icon"></i>
                                                            <span class="record-clean-text"><?php echo htmlspecialchars($item); ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="records-empty-placeholder" style="padding: 10px 14px;">No items specified under this header</div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif (!empty($grp['items'])): ?>
                                        <div class="records-clean-list" style="margin-bottom: 12px;">
                                            <?php foreach ($grp['items'] as $index => $item): ?>
                                                <div class="record-clean-item">
                                                    <span class="record-clean-num"><?php echo ($index + 1); ?></span>
                                                    <i class="far fa-check-square record-clean-icon"></i>
                                                    <span class="record-clean-text"><?php echo htmlspecialchars($item); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="records-empty-placeholder">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Not assigned yet</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 3. RESOURCE ISSUE RECORD (WARM AMBER / ORANGE THEME) -->
                <div style="margin-bottom: 24px;">
                    <div class="profile-records-container records-theme-amber">
                        <div class="records-section-title">
                            <div class="records-title-main">
                                <div class="records-title-icon-wrap">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="records-title-text">
                                    <span class="records-title-heading">Resource Issue Record</span>
                                    <span class="records-title-sub">Hardware, software, access &amp; resource issue logs</span>
                                </div>
                            </div>
                            <?php if (!empty($audResIssueGroups)): ?>
                                <span class="records-badge-count"><?php echo count($audResIssueGroups); ?> Group<?php echo count($audResIssueGroups) > 1 ? 's' : ''; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($audResIssueGroups)): ?>
                            <?php foreach ($audResIssueGroups as $grp): ?>
                                <?php if (!empty($grp['header'])): ?>
                                    <div class="record-category-card">
                                        <div class="record-category-header">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <i class="fas fa-wrench"></i>
                                                <span><?php echo htmlspecialchars($grp['header']); ?></span>
                                            </div>
                                            <?php if (!empty($grp['items'])): ?>
                                                <span class="category-count-badge"><?php echo count($grp['items']); ?> item<?php echo count($grp['items']) > 1 ? 's' : ''; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($grp['items'])): ?>
                                            <div class="records-clean-list">
                                                <?php foreach ($grp['items'] as $index => $item): ?>
                                                    <div class="record-clean-item">
                                                        <span class="record-clean-num"><?php echo ($index + 1); ?></span>
                                                        <i class="fas fa-exclamation-circle record-clean-icon"></i>
                                                        <span class="record-clean-text"><?php echo htmlspecialchars($item); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="records-empty-placeholder" style="padding: 10px 14px;">No items specified under this header</div>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif (!empty($grp['items'])): ?>
                                    <div class="records-clean-list" style="margin-bottom: 12px;">
                                        <?php foreach ($grp['items'] as $index => $item): ?>
                                            <div class="record-clean-item">
                                                <span class="record-clean-num"><?php echo ($index + 1); ?></span>
                                                <i class="fas fa-exclamation-circle record-clean-icon"></i>
                                                <span class="record-clean-text"><?php echo htmlspecialchars($item); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="records-empty-placeholder">
                                <i class="fas fa-info-circle"></i>
                                <span>Not assigned yet</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. Attendance Tab Pane -->
                <div class="mydash-tab-pane" id="mydash-pane-attendance">
                    <div class="qms_complete_btn header-theme-attendance">
                        <i class="fas fa-calendar-check"></i> Attendance
                    </div>
                    <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-top: none; border-radius: 0 0 10px 10px; padding: 30px; box-shadow: var(--shadow-sm);">
                        <div style="text-align: center; padding: 40px 20px;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(139, 92, 246, 0.12); color: #8b5cf6; display: inline-flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 16px;">
                                <i class="far fa-clock"></i>
                            </div>
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Attendance &amp; Log System</h4>
                            <p style="font-size: 13.5px; color: var(--text-muted); max-width: 480px; margin: 0 auto; line-height: 1.6;">
                                Daily attendance tracking and duty login records for your auditor profile. Feature is currently being synchronized with ERP biometric &amp; login gateway.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 3. Task Management Tab Pane -->
                <div class="mydash-tab-pane" id="mydash-pane-task-mgmt">
                    <div class="qms_complete_btn header-theme-tasks">
                        <i class="fas fa-tasks"></i> Task Management
                    </div>
                    <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-top: none; border-radius: 0 0 10px 10px; padding: 24px; box-shadow: var(--shadow-sm);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                            <div>
                                <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main);">
                                    <i class="fas fa-check-square" style="color: #0284c7; margin-right: 6px;"></i> Assigned Tasks &amp; Activities
                                </h4>
                                <p style="margin: 4px 0 0 0; font-size: 12.5px; color: var(--text-muted);">
                                    Track, manage and view your internal tasks and workflow assignments
                                </p>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <span style="padding: 5px 12px; font-size: 12px; background: rgba(2, 132, 199, 0.1); color: #0284c7; border-radius: 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="far fa-clock"></i> Active Tasks
                                </span>
                            </div>
                        </div>
                        
                        <div style="text-align: center; padding: 30px 20px;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(2, 132, 199, 0.12); color: #0284c7; display: inline-flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 16px;">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h4 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Task Management Section</h4>
                            <p style="font-size: 13.5px; color: var(--text-muted); max-width: 480px; margin: 0 auto; line-height: 1.6;">
                                No pending individual task assignments currently. New workflow tasks will be listed here with priority, deadlines, and tracking.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 4. Work Guidelines Tab Pane -->
                <div class="mydash-tab-pane" id="mydash-pane-guidelines">
                    <div class="qms_complete_btn header-theme-guidelines" style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-book"></i> Work Guidelines
                        </div>
                        <div>
                            <span class="badge" style="background: rgba(255,255,255,0.25); color: #fff; font-size: 11px; padding: 4px 10px; border-radius: 12px;">
                                <?php echo count($guidelinesList); ?> Active
                            </span>
                        </div>
                    </div>
                    <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-top: none; border-radius: 0 0 10px 10px; padding: 20px; box-shadow: var(--shadow-sm);">
                        <?php if (empty($guidelinesList)): ?>
                        <div class="empty-state" style="padding: 40px 20px; text-align: center;">
                            <i class="fas fa-folder-open" style="font-size: 32px; color: var(--text-muted); margin-bottom: 12px; display: block;"></i>
                            <h3 style="font-size: 16px; margin-bottom: 6px;">No Guidelines Assigned</h3>
                            <p class="text-muted" style="font-size: 13px;">You do not have any guidelines assigned currently.</p>
                        </div>
                        <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <?php foreach ($guidelinesList as $g): 
                                $createdTime = strtotime($g['created_at']);
                                $formattedDate = date('d M Y, h:i A', $createdTime);
                                $postedBy = !empty($g['created_by']) ? $g['created_by'] : 'Director';
                                if (in_array(strtolower(trim($postedBy)), ['main admin', 'top mgmt', 'admin', 'director'], true)) {
                                    $postedBy = 'Management';
                                }
                                $attachments = $g['attachments'] ?? [];
                                $gDesc = trim($g['description'] ?? '');
                            ?>
                            <div style="background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(13, 77, 139, 0.1); color: #0d4d8b; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div>
                                            <h4 style="margin: 0; font-size: 14.5px; font-weight: 700; color: var(--text-main);">
                                                <?php echo htmlspecialchars($g['title']); ?>
                                            </h4>
                                            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">
                                                <span><i class="far fa-user"></i> By: <strong><?php echo htmlspecialchars($postedBy); ?></strong></span>
                                                <span style="margin: 0 6px;">•</span>
                                                <span><i class="far fa-clock"></i> <?php echo $formattedDate; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <span style="background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-check-circle" style="font-size: 10px;"></i> Active
                                    </span>
                                </div>
                                <?php if ($gDesc !== ''): ?>
                                <div style="margin-top: 10px; font-size: 13px; color: var(--text-main); line-height: 1.55; background: var(--card-bg); padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-color);">
                                    <?php echo nl2br(htmlspecialchars($gDesc)); ?>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($attachments)): ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; padding-top: 8px; border-top: 1px solid var(--border-color); align-items: center;">
                                    <span style="font-size: 11px; font-weight: 600; color: var(--text-muted); margin-right: 4px;">
                                        <i class="fas fa-paperclip"></i> Documents (<?php echo count($attachments); ?>):
                                    </span>
                                    <?php foreach ($attachments as $att): 
                                        $iconInfo = aitf_get_file_icon_info($att['original_name']);
                                        $fileSizeFormatted = aitf_format_file_size($att['file_size']);
                                    ?>
                                    <a href="view_guideline_doc.php?id=<?php echo (int)$att['id']; ?>" target="_blank" 
                                       style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; padding: 4px 10px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-main); text-decoration: none;">
                                        <i class="<?php echo $iconInfo['icon']; ?>" style="color: <?php echo $iconInfo['color']; ?>;"></i>
                                        <strong><?php echo htmlspecialchars($att['original_name']); ?></strong>
                                        <small style="color: var(--text-muted);">(<?php echo $fileSizeFormatted; ?>)</small>
                                        <i class="fas fa-external-link-alt" style="font-size: 9px; color: var(--text-muted);"></i>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Assignments Tab -->
            <div id="tab-assignments" class="tab-pane">
                <div class="section-header">
                    <h2 class="section-title">My Assignments</h2>
                </div>
                
                <div class="timeline-container">
                    <table class="timeline-table">
                        <thead>
                            <tr>
                                <th>Form Type</th>
                                <th>Date Assigned / Submitted</th>
                                <th>Organization / Name</th>
                                <th>Message from Admin</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $assignedForms = [];
                            try {
                                // Fetch Audit Applications
                                $stmt1 = $db->query("SELECT id, created_at, org_name as name, 'Audit Application' as type, 'applicationReceivedView.php' as view_url, admin_assign_message FROM online_audit_applications WHERE assigned_to_auditor_id = $mappedUserId");
                                $forms1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
                                
                                // Fetch Information Updation
                                $stmt2 = $db->query("SELECT id, created_at, org_name as name, 'Information Updation' as type, 'info_updation_view.php' as view_url, admin_assign_message FROM information_updation_forms WHERE assigned_to_auditor_id = $mappedUserId");
                                $forms2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                                
                                // Fetch NC Closing Form
                                $stmt3 = $db->query("SELECT id, created_at, org_name as name, 'NC Closing Form' as type, 'nc_closing_form_view.php' as view_url, admin_assign_message FROM nc_closing_forms WHERE assigned_to_auditor_id = $mappedUserId");
                                $forms3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
                                
                                $assignedForms = array_merge($forms1, $forms2, $forms3);
                                
                                // Sort by created_at DESC
                                usort($assignedForms, function($a, $b) {
                                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                                });
                            } catch (Exception $e) {}

                            if (!empty($assignedForms)):
                                foreach ($assignedForms as $form):
                            ?>
                            <tr>
                                <td style="font-weight: 500; color: var(--text-main);"><?php echo htmlspecialchars($form['type']); ?></td>
                                <td class="timeline-date"><?php echo date('d M Y, h:i A', strtotime($form['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($form['name']); ?></td>
                                <td>
                                    <?php if (!empty($form['admin_assign_message'])): ?>
                                        <div style="font-size:12px; color:#475569; background:#f1f5f9; padding:4px 8px; border-radius:4px; border-left:3px solid #3b82f6; max-width:200px; max-height: 50px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($form['admin_assign_message']); ?>">
                                            <?php echo htmlspecialchars($form['admin_assign_message']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:#94a3b8; font-size:12px;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo $form['view_url']; ?>?id=<?php echo $form['id']; ?>" class="btn btn-sm btn-primary" style="background:#2563eb; color:#fff; border-radius:4px; padding:4px 12px; text-decoration:none; display:inline-block;"><i class="fas fa-eye"></i> View & Review</a>
                                </td>
                            </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px;" class="text-muted">No forms have been assigned to you yet.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Clients Tab -->
            <div id="tab-clients" class="tab-pane">
                <div class="section-header">
                    <h2 class="section-title">My Clients</h2>
                </div>
                
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" id="clientSearch" placeholder="Search clients by name or ID...">
                </div>

                <?php if (!empty($clientDetails)): ?>
                <div class="client-grid" id="clientGrid">
                    <?php foreach ($clientDetails as $cid => $client): 
                        // Determine which services this auditor did for this client
                        $auditorServices = array();
                        if (isset($clientServices[$cid])) {
                            $auditorServices = array_keys($clientServices[$cid]);
                        }
                    ?>
                    <a href="customerAccount.php?customerid=<?php echo $cid; ?>" class="client-card" data-name="<?php echo strtolower(htmlspecialchars($client['customer_name'])); ?>" data-id="<?php echo strtolower(htmlspecialchars($client['unique_id'])); ?>">
                        <div class="client-header">
                            <div class="client-name"><?php echo htmlspecialchars($client['customer_name']); ?></div>
                            <span class="client-id">ID: <?php echo htmlspecialchars($client['unique_id']); ?></span>
                        </div>
                        <div style="color: var(--text-muted); font-size: 14px; margin-bottom: 12px;">
                            <div><i class="fas fa-envelope" style="width: 20px;"></i> <?php echo htmlspecialchars(!empty($client['email_id']) ? $client['email_id'] : ($client['email'] ?? 'N/A')); ?></div>
                            <div><i class="fas fa-phone" style="width: 20px;"></i> <?php echo htmlspecialchars(!empty($client['contact']) ? $client['contact'] : ($client['mobile_no'] ?? 'N/A')); ?></div>
                        </div>
                        
                        <div class="client-services">
                            <?php 
                            $allServiceTypes = ['CA', 'SA-I', 'SA-II', 'RC'];
                            // Show badges based on what services exist for the client overall vs what auditor did
                            $clientServiceTypes = array_column($client['services'], 'type');
                            
                            foreach ($allServiceTypes as $type) {
                                if (in_array($type, $clientServiceTypes)) {
                                    if (in_array($type, $auditorServices)) {
                                        echo '<span class="service-badge service-active"><i class="fas fa-check-circle"></i> ' . $type . '</span>';
                                    } else {
                                        echo '<span class="service-badge service-locked" title="Assigned to another auditor"><i class="fas fa-lock"></i> ' . $type . '</span>';
                                    }
                                }
                            }
                            ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <h3>No clients found</h3>
                    <p class="text-muted">You haven't been assigned to any clients yet.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- History Tab -->
            <div id="tab-history" class="tab-pane">
                <div class="section-header" style="margin-bottom: 20px;">
                    <h2 class="section-title"><i class="fas fa-chart-pie" style="color: var(--accent-blue); margin-right: 8px;"></i>Audits Overview</h2>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon total"><i class="fas fa-clipboard-check"></i></div>
                        <div class="stat-value counter" data-target="<?php echo $stats['total']; ?>"><?php echo (int)$stats['total']; ?></div>
                        <div class="stat-label">Total Audits</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon ca"><i class="fas fa-file-contract"></i></div>
                        <div class="stat-value counter" data-target="<?php echo $stats['ca']; ?>"><?php echo (int)$stats['ca']; ?></div>
                        <div class="stat-label">CA Audits</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sa1"><i class="fas fa-search"></i></div>
                        <div class="stat-value counter" data-target="<?php echo $stats['sa1']; ?>"><?php echo (int)$stats['sa1']; ?></div>
                        <div class="stat-label">SA-I Audits</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sa2"><i class="fas fa-search-plus"></i></div>
                        <div class="stat-value counter" data-target="<?php echo $stats['sa2']; ?>"><?php echo (int)$stats['sa2']; ?></div>
                        <div class="stat-label">SA-II Audits</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon rc"><i class="fas fa-sync-alt"></i></div>
                        <div class="stat-value counter" data-target="<?php echo $stats['rc']; ?>"><?php echo (int)$stats['rc']; ?></div>
                        <div class="stat-label">RC Audits</div>
                    </div>
                </div>

                <div class="section-header" style="margin-top: 30px;">
                    <h2 class="section-title"><i class="far fa-calendar-check" style="color: var(--accent-blue); margin-right: 8px;"></i>Upcoming Audits</h2>
                </div>
                
                <?php if (!empty($futureAudits)): ?>
                <div class="upcoming-grid">
                    <?php foreach ($futureAudits as $fa): 
                        $badgeClass = 'badge-' . strtolower(str_replace('-', '', $fa['service_type']));
                        $displayDate = !empty($fa['planned_date']) ? $fa['planned_date'] : (!empty($fa['audit_dates']) ? $fa['audit_dates'] : ($fa['parsed_audit_date'] ?? 'N/A'));
                        $displayLoc = !empty($fa['location']) ? $fa['location'] : 'N/A';
                    ?>
                    <a href="customerAccount.php?customerid=<?php echo $fa['customer_id']; ?>" class="upcoming-card" style="text-decoration:none; color:inherit; display:block;">
                        <div class="upcoming-header">
                            <div class="upcoming-client"><?php echo htmlspecialchars($fa['customer_name']); ?></div>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($fa['service_type']); ?></span>
                        </div>
                        <div class="upcoming-details">
                            <div><i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($displayDate); ?></div>
                            <div><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($displayLoc); ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No upcoming audits scheduled</h3>
                    <p class="text-muted">You're all caught up for now.</p>
                </div>
                <?php endif; ?>

                <div class="section-header" style="margin-top: 35px;">
                    <h2 class="section-title"><i class="fas fa-history" style="color: var(--accent-blue); margin-right: 8px;"></i>Completed Audits History</h2>
                </div>

                <?php if (!empty($auditsByDate)): ?>
                <div class="timeline-container">
                    <table class="timeline-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Client Name</th>
                                <th>Service Type</th>
                                <th>Ref No.</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($auditsByDate as $date => $dateAudits): 
                                foreach ($dateAudits as $audit):
                                    $badgeClass = 'badge-' . strtolower(str_replace('-', '', $audit['service_type']));
                            ?>
                            <tr>
                                <td class="timeline-date"><?php echo htmlspecialchars($date); ?></td>
                                <td style="font-weight: 500; color: var(--text-main);"><?php echo htmlspecialchars($audit['customer_name']); ?></td>
                                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($audit['service_type']); ?></span></td>
                                <td style="font-family: monospace;"><?php echo htmlspecialchars($audit['audit_no'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php 
                                    $status = $audit['status'] ?? 'Unknown';
                                    $statusColor = $status === 'Completed' ? 'var(--accent-green)' : ($status === 'Pending' ? 'var(--accent-amber)' : 'var(--text-muted)');
                                    echo '<span style="color: ' . $statusColor . '; font-weight: 500;"><i class="fas fa-circle" style="font-size: 8px; vertical-align: middle; margin-right: 4px;"></i>' . htmlspecialchars($status) . '</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php 
                                endforeach;
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h3>No audit history</h3>
                    <p class="text-muted">Your completed audits will appear here.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Performance Tab -->
            <div id="tab-performance" class="tab-pane">
                <div class="panel-header" style="padding-bottom: 15px; border-bottom: 1px solid var(--border-color); margin-bottom: 20px;">
                    <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main);">My Performance (Quality Analysis)</h2>
                </div>
                <div class="panel-content">
                    <?php
                    // Fetch quality analysis data for the logged-in auditor safely
                    $qaListOfCustomer = [];
                    $qaCaMap = []; $qaSaiMap = []; $qaSaiiMap = []; $qaRcMap = []; $qaSpMap = [];
                    $qaSurveyDetailMap = []; $qaSurveyAgg = []; $qaCdcMap = [];
                    try {
                        $qaSql = "SELECT ci.id, customer_name, address, contact, email_id, scope, aif_nace, svc.service_id, service_type, svc.audit_date 
                                  FROM customer_info ci 
                                  JOIN service svc ON ci.id = svc.customer_id 
                                  WHERE (ci.is_deleted IS NULL OR ci.is_deleted <> 'Y') 
                                    AND (svc.is_deleted IS NULL OR svc.is_deleted <> 'Y') 
                                  ORDER BY svc.audit_date DESC";
                        $qaStmt = $db->prepare($qaSql);
                        $qaStmt->execute();
                        $qaListOfCustomer = $qaStmt->fetchAll(PDO::FETCH_ASSOC);

                        $qaServiceIdsByType = [
                            'CA' => [], 'SA-I' => [], 'SA-II' => [], 'RC' => [], 'SP' => []
                        ];
                        foreach ($qaListOfCustomer as $c) {
                            if (isset($qaServiceIdsByType[$c['service_type']])) {
                                $qaServiceIdsByType[$c['service_type']][] = $c['service_id'];
                            }
                        }

                        $qaLoadMap = function($table, $serviceCol, $filterIds) use (&$db) {
                            if (empty($filterIds)) return [];
                            $ph = implode(',', array_fill(0, count($filterIds), '?'));
                            $sql = "SELECT * FROM {$table} WHERE {$serviceCol} IN ($ph)";
                            $stmt = $db->prepare($sql);
                            $stmt->execute($filterIds);
                            $out = [];
                            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                                $out[$r[$serviceCol]] = $r;
                            }
                            return $out;
                        };

                        $qaCaMap = $qaLoadMap('service_type_ca', 'service', $qaServiceIdsByType['CA']);
                        $qaSaiMap = $qaLoadMap('service_type_sai', 'service', $qaServiceIdsByType['SA-I']);
                        $qaSaiiMap = $qaLoadMap('service_type_saii', 'service', $qaServiceIdsByType['SA-II']);
                        $qaRcMap = $qaLoadMap('service_type_rc', 'service', $qaServiceIdsByType['RC']);
                        $qaSpMap = $qaLoadMap('service_type_sp', 'service', $qaServiceIdsByType['SP']);

                        $qaSubtypeIds = array_merge(
                            array_column($qaCaMap, 'ca_id'),
                            array_column($qaSaiMap, 'sai_id'),
                            array_column($qaSaiiMap, 'saii_id'),
                            array_column($qaRcMap, 'rc_id'),
                            array_column($qaSpMap, 'sp_id')
                        );
                        $qaSubtypeIds = array_filter($qaSubtypeIds, function($v){ return $v !== null && $v !== ''; });

                        $qaSurveyIdMap = [];
                        if (!empty($qaSubtypeIds)) {
                            $ph = implode(',', array_fill(0, count($qaSubtypeIds), '?'));
                            $sqlSurveyIds = "SELECT ca_serviceid, survey_id, auditing_quality, mgmt_satisfaction, aggregate, ncval, ofival, oplusval FROM customer_survey_id WHERE ca_serviceid IN ($ph)";
                            $stmt = $db->prepare($sqlSurveyIds);
                            $stmt->execute($qaSubtypeIds);
                            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                                $qaSurveyIdMap[$r['ca_serviceid']] = $r['survey_id'];
                                $qaSurveyDetailMap[$r['ca_serviceid']] = $r;
                            }
                        }

                        $qaSurveyIds = array_values(array_filter($qaSurveyIdMap));
                        if (!empty($qaSurveyIds)) {
                            $ph = implode(',', array_fill(0, count($qaSurveyIds), '?'));
                            $sqlSurvey = "SELECT survayid, lead_auditor, marks FROM customer_survey WHERE survayid IN ($ph)";
                            $stmt = $db->prepare($sqlSurvey);
                            $stmt->execute($qaSurveyIds);
                            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                                $sid = $r['survayid'];
                                if (!isset($qaSurveyAgg[$sid])) {
                                    $qaSurveyAgg[$sid] = ['auditors' => [], 'marks' => []];
                                }
                                $qaSurveyAgg[$sid]['auditors'][] = $r['lead_auditor'];
                                $qaSurveyAgg[$sid]['marks'][] = $r['marks'];
                            }
                            foreach ($qaSurveyAgg as $sid => $vals) {
                                $qaSurveyAgg[$sid]['auditors'] = implode(', ', array_filter($vals['auditors']));
                                $qaSurveyAgg[$sid]['marks'] = implode(', ', array_filter($vals['marks']));
                            }
                        }

                        if (!empty($qaSubtypeIds)) {
                            $ph = implode(',', array_fill(0, count($qaSubtypeIds), '?'));
                            $sqlCdc = "SELECT * FROM cdc_report_review WHERE ca_service_id IN ($ph)";
                            $stmt = $db->prepare($sqlCdc);
                            $stmt->execute($qaSubtypeIds);
                            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                                $qaCdcMap[$r['ca_service_id']] = $r;
                            }
                        }
                    } catch (Exception $e) {}
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table timeline-table">
                            <thead>
                                <tr>                                    
                                    <th>Client</th>
                                    <th>Service Type</th>
                                    <th>Audit Date</th>
                                    <th>Rating (A)</th>
                                    <th>AITF Cert (B)</th>
                                    <th>Auditor</th>
                                    <th>Individual marks</th>
                                    <th>Rating (A & B)</th>
                                    <th>NC</th>
                                    <th>OFI</th>
                                    <th>O+</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $hasQaRows = false;
                                foreach ($qaListOfCustomer as $customer) {
                                    $svcId = $customer['service_id'];
                                    $svcType = $customer['service_type'];

                                    if ($svcType == 'CA') { $sub = isset($qaCaMap[$svcId]) ? $qaCaMap[$svcId] : null; }
                                    elseif ($svcType == 'SA-I') { $sub = isset($qaSaiMap[$svcId]) ? $qaSaiMap[$svcId] : null; }
                                    elseif ($svcType == 'SA-II') { $sub = isset($qaSaiiMap[$svcId]) ? $qaSaiiMap[$svcId] : null; }
                                    elseif ($svcType == 'RC') { $sub = isset($qaRcMap[$svcId]) ? $qaRcMap[$svcId] : null; }
                                    elseif ($svcType == 'SP') { $sub = isset($qaSpMap[$svcId]) ? $qaSpMap[$svcId] : null; }
                                    else { $sub = null; }

                                    if (!$sub) { continue; }

                                    $subId = null;
                                    if ($svcType == 'CA') $subId = $sub['ca_id'];
                                    else if ($svcType == 'SA-I') $subId = $sub['sai_id'];
                                    else if ($svcType == 'SA-II') $subId = $sub['saii_id'];
                                    else if ($svcType == 'RC') $subId = $sub['rc_id'];
                                    else if ($svcType == 'SP') $subId = $sub['sp_id'];

                                    if (!$subId) { continue; }

                                    $surveyId = isset($qaSurveyIdMap[$subId]) ? $qaSurveyIdMap[$subId] : null;
                                    $agg = isset($qaSurveyAgg[$surveyId]) ? $qaSurveyAgg[$surveyId] : ['auditors' => '', 'marks' => ''];
                                    $surveyDetail = isset($qaSurveyDetailMap[$subId]) ? $qaSurveyDetailMap[$subId] : null;
                                    $cdc = isset($qaCdcMap[$subId]) ? $qaCdcMap[$subId] : null;
                                    
                                    if (!$surveyDetail) { continue; }
                                    
                                    $auditorDisplay = $agg['auditors'];
                                    if (empty($auditorDisplay) && $cdc && !empty($cdc['lead_auditor'])) {
                                        $auditorDisplay = $cdc['lead_auditor'];
                                    }

                                    // Check if current auditor is involved
                                    $currentAuditorName = $auditor['auditor_name'];
                                    if (stripos($auditorDisplay, $currentAuditorName) === false && $currentAuditorName !== $auditorDisplay) {
                                        continue; // skip if auditor name doesn't match
                                    }
                                    
                                    $hasQaRows = true;

                                    $displayAuditDate = (!empty($sub['audit_date'])) ? $sub['audit_date'] : $customer["audit_date"];
                                    $displayAuditDateConverted = date('d-m-Y', strtotime($displayAuditDate));
                                    if ($displayAuditDateConverted == '01-01-1970' || !$displayAuditDate) { $displayAuditDateConverted = ''; }

                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($customer['customer_name'] ?? ''); ?></td>
                                        <td><span class="status-badge <?php echo strtolower($svcType ?? ''); ?>"><?php echo htmlspecialchars($svcType ?? ''); ?></span></td>
                                        <td><?php echo htmlspecialchars($displayAuditDateConverted); ?></td>
                                        <td><?php echo htmlspecialchars($surveyDetail['auditing_quality'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($surveyDetail['mgmt_satisfaction'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($auditorDisplay ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($agg['marks'] ?? ''); ?></td>
                                        <td><span class="status-badge valid"><?php echo htmlspecialchars($surveyDetail['aggregate'] ?? ''); ?></span></td>
                                        <td><?php 
                                            $ncDisplay = $cdc ? ($cdc['ncval'] ?? '') : '';
                                            if (empty($ncDisplay) && !empty($surveyDetail['ncval'])) $ncDisplay = $surveyDetail['ncval'];
                                            echo htmlspecialchars($ncDisplay);
                                        ?></td>
                                        <td><?php 
                                            $ofiDisplay = $cdc ? ($cdc['ofival'] ?? '') : '';
                                            if (empty($ofiDisplay) && !empty($surveyDetail['ofival'])) $ofiDisplay = $surveyDetail['ofival'];
                                            echo htmlspecialchars($ofiDisplay);
                                        ?></td>
                                        <td><?php 
                                            $oplusDisplay = $cdc ? ($cdc['oplusval'] ?? '') : '';
                                            if (empty($oplusDisplay) && !empty($surveyDetail['oplusval'])) $oplusDisplay = $surveyDetail['oplusval'];
                                            echo htmlspecialchars($oplusDisplay);
                                        ?></td>                                               
                                    </tr>
                                    <?php
                                }
                                if (!$hasQaRows) {
                                    echo "<tr><td colspan='11' style='text-align:center; padding: 20px;'>No performance records found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Guidelines Tab -->
            <div id="tab-guidelines" class="tab-pane">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 24px;">
                    <div>
                        <h2 class="section-title"><i class="fas fa-book" style="color: #10b981; margin-right: 8px;"></i>Auditor Work Guidelines &amp; Directives</h2>
                        <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">Official company guidelines, inspection protocols, and work directives assigned to you.</p>
                    </div>
                </div>

                <?php if (empty($guidelinesList)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>No Guidelines Assigned to You</h3>
                    <p class="text-muted">You do not have any guidelines assigned to your user account at this moment.</p>
                </div>
                <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <?php foreach ($guidelinesList as $g): 
                        $createdTime = strtotime($g['created_at']);
                        $formattedDate = date('d M Y, h:i A', $createdTime);
                        $postedBy = !empty($g['created_by']) ? $g['created_by'] : 'Director';
                        if (in_array(strtolower(trim($postedBy)), ['main admin', 'top mgmt', 'admin', 'director'], true)) {
                            $postedBy = 'Director';
                        }
                        $attachments = $g['attachments'] ?? [];
                    ?>
                    <div class="guideline-card" style="background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
                        <div style="background: var(--bg-color); padding: 14px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <h4 style="margin: 0; font-size: 15.5px; font-weight: 700; color: var(--text-main);">
                                    <i class="fas fa-file-alt" style="color: #0d4d8b; margin-right: 8px;"></i>
                                    <?php echo htmlspecialchars($g['title']); ?>
                                </h4>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                    <i class="far fa-clock"></i> Posted on <strong><?php echo $formattedDate; ?></strong> by <strong><?php echo htmlspecialchars($postedBy); ?></strong>
                                </div>
                            </div>
                            <div>
                                <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 11.5px; font-weight: 600; padding: 4px 10px; border-radius: 12px;">
                                    <i class="fas fa-check-circle"></i> Active Directive
                                </span>
                            </div>
                        </div>
                        <div style="padding: 16px 20px;">
                            <?php $gDesc = trim($g['description'] ?? ''); ?>
                            <?php if ($gDesc !== ''): ?>
                            <div style="font-size: 13.5px; color: var(--text-main); line-height: 1.6; margin-bottom: 12px; background: var(--bg-color); padding: 12px 14px; border-radius: 8px; border: 1px solid var(--border-color);">
                                <?php echo nl2br(htmlspecialchars($gDesc)); ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($attachments)): ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; <?php echo ($gDesc !== '') ? 'margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border-color);' : ''; ?>">
                                <strong style="font-size: 12px; color: var(--text-muted); margin-right: 4px;">
                                    <i class="fas fa-paperclip"></i> Documents (<?php echo count($attachments); ?>):
                                </strong>
                                <?php foreach ($attachments as $att): 
                                    $iconInfo = aitf_get_file_icon_info($att['original_name']);
                                    $fileSizeFormatted = aitf_format_file_size($att['file_size']);
                                ?>
                                <a href="view_guideline_doc.php?id=<?php echo (int)$att['id']; ?>" target="_blank" 
                                   style="display: inline-flex; align-items: center; gap: 8px; font-size: 12px; padding: 6px 12px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-main); text-decoration: none; box-shadow: var(--shadow-sm); transition: var(--transition);">
                                    <i class="<?php echo $iconInfo['icon']; ?>" style="color: <?php echo $iconInfo['color']; ?>; font-size: 14px;"></i>
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($att['original_name']); ?></span>
                                    <span style="color: var(--text-muted); font-size: 11px;">(<?php echo $fileSizeFormatted; ?>)</span>
                                    <i class="fas fa-external-link-alt" style="font-size: 9px; color: var(--text-muted); margin-left: 2px;"></i>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme Toggle
            const themeToggle = document.getElementById('themeToggle');
            const html = document.documentElement;
            const themeIcon = themeToggle.querySelector('i');
            
            // Check local storage for theme
            if (localStorage.getItem('theme') === 'dark') {
                html.classList.add('theme-dark');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            }
            
            themeToggle.addEventListener('click', () => {
                html.classList.toggle('theme-dark');
                if (html.classList.contains('theme-dark')) {
                    localStorage.setItem('theme', 'dark');
                    themeIcon.classList.replace('fa-moon', 'fa-sun');
                } else {
                    localStorage.setItem('theme', 'light');
                    themeIcon.classList.replace('fa-sun', 'fa-moon');
                }
            });

            // Mobile Sidebar Toggle
            const mobileToggle = document.getElementById('mobileToggle');
            const sidebar = document.getElementById('sidebar');
            
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(e.target) && 
                    !mobileToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            });

            // Tabs Navigation
            const navItems = document.querySelectorAll('.nav-item[data-tab]');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            function switchTab(tabId) {
                if (!tabId) return;
                const targetNav = document.querySelector('.nav-item[data-tab="' + tabId + '"]');
                const targetPane = document.getElementById('tab-' + tabId);
                if (!targetPane) return;

                navItems.forEach(nav => nav.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));

                if (targetNav) targetNav.classList.add('active');
                targetPane.classList.add('active');

                if (window.innerWidth <= 768 && sidebar) {
                    sidebar.classList.remove('show');
                }

                if (tabId === 'history') {
                    animateCounters();
                }
            }

            navItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tabId = this.getAttribute('data-tab');
                    if (history.pushState) {
                        history.pushState(null, null, '#' + tabId);
                    } else {
                        window.location.hash = tabId;
                    }
                    switchTab(tabId);
                });
            });

            // Handle hash on load and hash changes
            window.addEventListener('hashchange', function() {
                let hashTab = window.location.hash.replace('#', '');
                if (hashTab === 'dashboard') hashTab = 'mydashboard';
                if (hashTab && document.getElementById('tab-' + hashTab)) {
                    switchTab(hashTab);
                }
            });

            const initialHash = window.location.hash.replace('#', '');
            if (initialHash && initialHash !== 'dashboard' && document.getElementById('tab-' + initialHash)) {
                switchTab(initialHash);
            } else {
                switchTab('mydashboard');
            }

            // My Dashboard Inner Tabs Switching
            const mydashTabBtns = document.querySelectorAll('.mydash-tab-btn[data-target]');
            const mydashTabPanes = document.querySelectorAll('.mydash-tab-pane');
            
            mydashTabBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = this.getAttribute('data-target');
                    if (!target) return;
                    
                    mydashTabBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    mydashTabPanes.forEach(pane => pane.classList.remove('active'));
                    const targetPane = document.querySelector(target);
                    if (targetPane) targetPane.classList.add('active');
                });
            });

            // Animated Counters
            function animateCounters() {
                const counters = document.querySelectorAll('.counter');
                const speed = 40;
                
                counters.forEach(counter => {
                    const target = +counter.getAttribute('data-target') || 0;
                    if (target > 0) {
                        counter.innerText = '0';
                        const inc = Math.max(1, Math.ceil(target / speed));
                        
                        function updateCount() {
                            const current = +counter.innerText || 0;
                            if (current < target) {
                                counter.innerText = Math.min(target, current + inc);
                                setTimeout(updateCount, 15);
                            } else {
                                counter.innerText = target;
                            }
                        }
                        updateCount();
                    } else {
                        counter.innerText = '0';
                    }
                });
            }
            
            // Initial animation
            animateCounters();

            // Client Search Filtering
            const searchInput = document.getElementById('clientSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const cards = document.querySelectorAll('.client-card');
                    
                    cards.forEach(card => {
                        const name = card.getAttribute('data-name');
                        const id = card.getAttribute('data-id');
                        
                        if (name.includes(searchTerm) || id.includes(searchTerm)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }

        });
    </script>
</body>
</html>

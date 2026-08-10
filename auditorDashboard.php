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
    header('Location: loginSelection.php?reason=login_required');
    exit;
}

$auditorId = (int)$_SESSION['auditor_id'];
$data = aitf_fetch_auditor_dashboard_data($db, $auditorId);
if (!$data) {
    header('Location: loginSelection.php?reason=login_required');
    exit;
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
            LEFT JOIN service s ON s.customer_id = ci.id
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            box-shadow: var(--shadow-sm);
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 24px;
            color: var(--primary);
            text-decoration: none;
        }
        html.theme-dark .topbar-brand { color: #f1f5f9; }

        .topbar-brand i {
            color: var(--accent-blue);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
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

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: calc(100vh - 70px);
            padding: 24px 0;
            z-index: 99;
            transition: var(--transition);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            background: rgba(59, 130, 246, 0.05);
            color: var(--primary);
            text-decoration: none;
        }
        html.theme-dark .nav-item:hover { color: #f1f5f9; }

        .nav-item.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-blue);
            border-left-color: var(--accent-blue);
        }

        .nav-item i {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-main);
            font-size: 24px;
            cursor: pointer;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 32px;
            transition: var(--transition);
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
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
                box-shadow: var(--shadow-lg);
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
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
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Topbar -->
    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 16px;">
            <button class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a href="#" class="topbar-brand">
                <i class="fa-solid fa-leaf"></i> AITF
            </a>
        </div>
        
        <div class="topbar-right">
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
            <nav>
                <a class="nav-item active" data-tab="dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a class="nav-item" data-tab="clients">
                    <i class="fas fa-users"></i> My Clients
                </a>
                <a class="nav-item" data-tab="history">
                    <i class="fas fa-history"></i> Audit History
                </a>
                <a class="nav-item" data-tab="profile">
                    <i class="fas fa-user"></i> Profile
                </a>
                <a class="nav-item" data-tab="performance">
                    <i class="fas fa-chart-bar"></i> Performance
                </a>
                <?php if (Utitlity::checkUserPermission('client_access')) { ?>
                <a href="homePage.php" class="nav-item" style="color: #6366f1;">
                    <i class="fas fa-building"></i> Company Clients
                </a>
                <?php } ?>
                <?php if (Utitlity::checkUserPermission('invoice_access')) { ?>
                <a href="invoicePage.php" class="nav-item" style="color: #6366f1;">
                    <i class="fas fa-rupee-sign"></i> Invoices
                </a>
                <?php } ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            
            <!-- Dashboard Tab -->
            <div id="tab-dashboard" class="tab-pane active">
                <div class="welcome-banner">
                    <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($auditor['auditor_name']); ?>!</h1>
                    <div class="welcome-date"><i class="far fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?></div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon total"><i class="fas fa-clipboard-check"></i></div>
                        <div class="stat-value counter" data-target="<?php echo $stats['total']; ?>">0</div>
                        <div class="stat-label">Total Audits</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon ca"><i class="fas fa-file-contract"></i></div>
                        <div class="stat-value counter" data-target="<?php echo $stats['ca']; ?>">0</div>
                        <div class="stat-label">CA Audits</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sa1"><i class="fas fa-search"></i></div>
                        <div class="stat-value counter" data-target="<?php echo $stats['sa1']; ?>">0</div>
                        <div class="stat-label">SA-I Audits</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sa2"><i class="fas fa-search-plus"></i></div>
                        <div class="stat-value counter" data-target="<?php echo $stats['sa2']; ?>">0</div>
                        <div class="stat-label">SA-II Audits</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon rc"><i class="fas fa-sync-alt"></i></div>
                        <div class="stat-value counter" data-target="<?php echo $stats['rc']; ?>">0</div>
                        <div class="stat-label">RC Audits</div>
                    </div>
                </div>

                <div class="section-header">
                    <h2 class="section-title">Upcoming Audits</h2>
                </div>
                
                <?php if (!empty($futureAudits)): ?>
                <div class="upcoming-grid">
                    <?php foreach ($futureAudits as $fa): 
                        $badgeClass = 'badge-' . strtolower(str_replace('-', '', $fa['service_type']));
                    ?>
                    <div class="upcoming-card">
                        <div class="upcoming-header">
                            <div class="upcoming-client"><?php echo htmlspecialchars($fa['customer_name']); ?></div>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($fa['service_type']); ?></span>
                        </div>
                        <div class="upcoming-details">
                            <div><i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($fa['planned_date']); ?></div>
                            <div><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($fa['location'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No upcoming audits scheduled</h3>
                    <p class="text-muted">You're all caught up for now.</p>
                </div>
                <?php endif; ?>
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
                            <div><i class="fas fa-envelope" style="width: 20px;"></i> <?php echo htmlspecialchars($client['email'] ?? 'N/A'); ?></div>
                            <div><i class="fas fa-phone" style="width: 20px;"></i> <?php echo htmlspecialchars($client['mobile_no'] ?? 'N/A'); ?></div>
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
                <div class="section-header">
                    <h2 class="section-title">Audit History</h2>
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

            <!-- Profile Tab -->
            <div id="tab-profile" class="tab-pane">
                <div class="section-header">
                    <h2 class="section-title">My Profile</h2>
                </div>
                
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($auditor['auditor_name'], 0, 1)); ?>
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($auditor['auditor_name']); ?></h2>
                            <span class="badge badge-ca" style="font-size: 14px;">Senior Auditor</span>
                        </div>
                    </div>
                    
                    <div class="profile-details">
                        <div class="detail-item">
                            <span class="detail-label">Auditor Number</span>
                            <span class="detail-value"><?php echo htmlspecialchars($auditor['auditor_no'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email Address</span>
                            <span class="detail-value"><?php echo htmlspecialchars($auditor['email'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Phone Number</span>
                            <span class="detail-value"><?php echo htmlspecialchars($auditor['phone'] ?? 'Not provided'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Tab -->
            <div id="tab-performance" class="tab-pane">
                <div class="panel-header" style="padding-bottom: 15px; border-bottom: 1px solid var(--border-color); margin-bottom: 20px;">
                    <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main);">My Performance (Quality Analysis)</h2>
                </div>
                <div class="panel-content">
                    <?php
                    // Fetch quality analysis data for the logged-in auditor
                    $qaSql = "SELECT ci.id, customer_name, address, contact, email_id, scope, aif_nace, svc.service_id, service_type, svc.audit_date FROM customer_info ci join service svc on ci.id = svc.customer_id WHERE is_deleted <> 'Y' ORDER BY svc.audit_date DESC";
                    $qaStmt = $db->prepare($qaSql);
                    $qaStmt->execute();
                    $qaListOfCustomer = $qaStmt->fetchAll();

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
                    $qaSurveyDetailMap = [];
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

                    $qaSurveyAgg = [];
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

                    $qaCdcMap = [];
                    if (!empty($qaSubtypeIds)) {
                        $ph = implode(',', array_fill(0, count($qaSubtypeIds), '?'));
                        $sqlCdc = "SELECT * FROM cdc_report_review WHERE ca_service_id IN ($ph)";
                        $stmt = $db->prepare($sqlCdc);
                        $stmt->execute($qaSubtypeIds);
                        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                            $qaCdcMap[$r['ca_service_id']] = $r;
                        }
                    }
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
            
            navItems.forEach(item => {
                item.addEventListener('click', () => {
                    // Remove active from all nav items and panes
                    navItems.forEach(nav => nav.classList.remove('active'));
                    tabPanes.forEach(pane => pane.classList.remove('active'));
                    
                    // Add active to clicked nav and corresponding pane
                    item.classList.add('active');
                    const tabId = item.getAttribute('data-tab');
                    document.getElementById('tab-' + tabId).classList.add('active');
                    
                    // Close sidebar on mobile after clicking
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('show');
                    }
                    
                    // Trigger counters if dashboard tab
                    if (tabId === 'dashboard') {
                        animateCounters();
                    }
                });
            });

            // Animated Counters
            function animateCounters() {
                const counters = document.querySelectorAll('.counter');
                const speed = 200; // lower is faster
                
                counters.forEach(counter => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText;
                    
                    if (target > 0) {
                        const inc = target / speed;
                        
                        function updateCount() {
                            const current = +counter.innerText;
                            if (current < target) {
                                counter.innerText = Math.ceil(current + inc);
                                setTimeout(updateCount, 1);
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

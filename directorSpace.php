<div id="wrapper" class="active director-wrapper">
<?php
$pageIdentifier = "Director Space";
include_once './header.php';
include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';

date_default_timezone_set('Asia/Kolkata');

if (!Utitlity::checkForDirector()) {
    header("Location: loginSelection.php?reason=login_required");
    exit;
}

aitf_ensure_rbac_schema($db);

$notice = trim((string)($_SESSION['director_notice'] ?? ''));
unset($_SESSION['director_notice']);

$rolesStmt = $db->prepare("SELECT role FROM user_role ORDER BY role ASC");
$rolesStmt->execute();
$roles = $rolesStmt->fetchAll(PDO::FETCH_COLUMN);

$usersStmt = $db->prepare("SELECT user_id, user_name, email_id, role, contact_no, e_id, account_status
                           FROM user
                           WHERE is_deleted = '0'
                           ORDER BY user_id DESC
                           LIMIT 80");
$usersStmt->execute();
$employees = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

$totalUsersStmt = $db->prepare("SELECT COUNT(*) FROM user WHERE is_deleted = '0'");
$totalUsersStmt->execute();
$totalUsers = (int)$totalUsersStmt->fetchColumn();

$activeUsersStmt = $db->prepare("SELECT activity_id, user_name, email_id, role, e_id, login_at, last_seen_at
                                 FROM user_login_activity
                                 WHERE logout_at IS NULL
                                   AND last_seen_at >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)
                                 ORDER BY last_seen_at DESC");
$activeUsersStmt->execute();
$activeUsers = $activeUsersStmt->fetchAll(PDO::FETCH_ASSOC);

$todayActivityStmt = $db->prepare("SELECT user_name, email_id, role, e_id, login_at, logout_at, last_seen_at
                                   FROM user_login_activity
                                   WHERE DATE(login_at) = CURDATE()
                                   ORDER BY login_at DESC");
$todayActivityStmt->execute();
$todayActivities = $todayActivityStmt->fetchAll(PDO::FETCH_ASSOC);
$todayLoginCount = count($todayActivities);
?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="director-dashboard">
                
                <!-- Dashboard Hero Section -->
                <header class="dashboard-hero">
                    <div class="hero-left">
                        <h1 class="hero-title" id="greetingText">Welcome, Managing Director</h1>
                        <script>
                            (function(){
                                var h = new Date().getHours();
                                var g = 'Good Evening';
                                if (h < 12) g = 'Good Morning';
                                else if (h < 17) g = 'Good Afternoon';
                                document.getElementById('greetingText').innerText = g + ', Managing Director';
                            })();
                        </script>
                        <p class="hero-subtitle">Welcome back. Here's what's happening across your organization today.</p>
                        
                        <div class="hero-stats">
                            <div class="hero-stat-card">
                                <span class="stat-label">Total Employees</span>
                                <span class="stat-value"><?php echo $totalUsers; ?></span>
                            </div>
                            <div class="hero-stat-card">
                                <span class="stat-label">Currently Online</span>
                                <span class="stat-value"><?php echo count($activeUsers); ?></span>
                            </div>
                            <div class="hero-stat-card">
                                <span class="stat-label">Last Login</span>
                                <span class="stat-value">
                                    <?php 
                                        if (!empty($todayActivities)) {
                                            echo date('h:i A', strtotime($todayActivities[0]['login_at']));
                                        } else {
                                            echo 'None';
                                        }
                                    ?>
                                </span>
                            </div>
                            <div class="hero-stat-card">
                                <span class="stat-label">Today's Date</span>
                                <span class="stat-value"><?php echo date('d M Y'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hero-right">
                        <div class="live-team-card">
                            <div class="live-team-header">
                                <h3>Live Team Status</h3>
                            </div>
                            <div class="live-team-list">
                                <?php 
                                $displayLimit = 5;
                                $count = 0;
                                foreach ($activeUsers as $activeUser) {
                                    if ($count < $displayLimit) {
                                        $firstName = explode(' ', aitf_rbac_escape($activeUser['user_name']))[0];
                                        ?>
                                        <div class="live-user-pill">
                                            <div class="avatar-circle">
                                                <?php echo strtoupper(substr(aitf_rbac_escape($activeUser['user_name']), 0, 1)); ?>
                                                <div class="status-dot"></div>
                                            </div>
                                            <span class="live-user-name"><?php echo $firstName; ?></span>
                                        </div>
                                        <?php
                                    }
                                    $count++;
                                }
                                $remaining = $count - $displayLimit;
                                if ($remaining > 0) {
                                    ?>
                                    <div class="live-user-more">+<?php echo $remaining; ?> more online</div>
                                    <?php
                                }
                                if ($count === 0) {
                                    ?>
                                    <div class="no-users">No users currently online.</div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </header>

                <?php if ($notice !== '') { ?>
                    <div class="alert-success">
                        <i class="fa fa-check-circle"></i>
                        <span><?php echo aitf_rbac_escape($notice); ?></span>
                    </div>
                <?php } ?>

                <!-- Floating Tab -->
                <div class="floating-tabs-container">
                    <div class="floating-tab tab-red" onclick="document.getElementById('activityToggle').click()">
                        <span class="tab-text">Login Activity</span>
                        <span class="tab-badge"><?php echo count($activeUsers); ?></span>
                    </div>
                </div>

                <!-- Activity Drawer Wrapper (Uses existing ID) -->
                <div id="todayActivityPanel" class="drawer-wrapper">
                    <div class="drawer-overlay" onclick="document.getElementById('activityToggle').click()"></div>
                    <div class="drawer-panel">
                        <div class="drawer-header bg-red-header">
                            <h2 style="color: white; margin: 0; font-size: 18px;">Live Login Activity</h2>
                            <button type="button" class="btn-close-white" onclick="document.getElementById('activityToggle').click()">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <div class="drawer-content drawer-gray-bg">
                            <div class="conflict-list">
                                <?php foreach ($activeUsers as $activeUser) { 
                                    $loginTime = !empty($activeUser['login_at']) ? date('d.m.Y H:i', strtotime($activeUser['login_at'])) : '-';
                                ?>
                                    <div class="conflict-card">
                                        <div class="conflict-card-header">
                                            <span class="font-bold"><?php echo aitf_rbac_escape($activeUser['user_name']); ?></span>
                                            <span class="text-sm text-red">Currently Online</span>
                                        </div>
                                        <div class="conflict-card-body">
                                            <div class="font-semibold mb-1"><?php echo aitf_rbac_escape($activeUser['role']); ?></div>
                                            <div class="text-xs text-gray-500 mb-1">
                                                Email: <?php echo aitf_rbac_escape($activeUser['email_id']); ?> | EID: <?php echo aitf_rbac_escape($activeUser['e_id']); ?>
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                Status: <span class="text-green-600 font-semibold">Active</span> (since <?php echo aitf_rbac_escape($loginTime); ?>)
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if (empty($activeUsers)) { ?>
                                    <div class="empty-timeline">No users are currently online.</div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Grid -->
                <div class="dashboard-grid">
                    
                    <!-- Left Column -->
                    <div class="dashboard-col-left">
                        <div class="premium-card">
                            <div class="card-header">
                                <h2>Add New Employee</h2>
                                <p>Generate employee ID and set permissions.</p>
                            </div>
                            <div class="card-body">
                                <form action="addUserToDb.php" method="post" class="premium-form">
                                    <input type="hidden" name="directorAddUser" value="1">
                                    
                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" name="userName" required placeholder="e.g. Jane Doe">
                                    </div>

                                    <div class="form-group">
                                        <label>System Role</label>
                                        <select name="userRole" required>
                                            <option value="" disabled selected>Select a role...</option>
                                            <?php foreach ($roles as $role) { ?>
                                                <option value="<?php echo aitf_rbac_escape($role); ?>"><?php echo aitf_rbac_escape($role); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <input type="email" name="userEmail" required placeholder="name@company.com">
                                    </div>

                                    <div class="form-group">
                                        <label>Contact Number</label>
                                        <input type="text" name="contactNo" required placeholder="+91 00000 00000">
                                    </div>

                                    <button type="submit" class="btn-primary">
                                        Create & Generate EID
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="dashboard-col-right">
                        <div class="premium-card">
                            <div class="card-header flex-between">
                                <div>
                                    <h2>Employee Directory</h2>
                                    <p>Manage existing accounts and statuses</p>
                                </div>
                                <div class="search-wrapper">
                                    <i class="fa fa-search search-icon"></i>
                                    <input type="text" placeholder="Search employees..." id="empSearch" class="search-input">
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="premium-table" id="empTable">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Role</th>
                                            <th>EID</th>
                                            <th>Status</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($employees as $employee) { 
                                            $status = $employee['account_status'] ?? 'active'; 
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="emp-profile">
                                                        <div class="emp-avatar"><?php echo strtoupper(substr(aitf_rbac_escape($employee['user_name']), 0, 1)); ?></div>
                                                        <div class="emp-info">
                                                            <span class="emp-name"><?php echo aitf_rbac_escape($employee['user_name']); ?></span>
                                                            <span class="emp-email"><?php echo aitf_rbac_escape($employee['email_id']); ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge-role"><?php echo aitf_rbac_escape($employee['role']); ?></span>
                                                </td>
                                                <td><span class="badge-eid"><?php echo aitf_rbac_escape($employee['e_id']); ?></span></td>
                                                <td>
                                                    <span class="badge-status status-<?php echo aitf_rbac_escape($status); ?>">
                                                        <?php echo ucfirst(aitf_rbac_escape($status)); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons text-right">
                                                        <?php if ($status === 'inactive') { ?>
                                                            <form action="directorUserAction.php" method="post">
                                                                <input type="hidden" name="user_id" value="<?php echo (int)$employee['user_id']; ?>">
                                                                <input type="hidden" name="action" value="activate">
                                                                <button type="submit" class="btn-action activate">Activate</button>
                                                            </form>
                                                        <?php } elseif ($status === 'active') { ?>
                                                            <form action="directorUserAction.php" method="post">
                                                                <input type="hidden" name="user_id" value="<?php echo (int)$employee['user_id']; ?>">
                                                                <input type="hidden" name="action" value="deactivate">
                                                                <button type="submit" class="btn-action deactivate">Deactivate</button>
                                                            </form>
                                                        <?php } ?>

                                                        <form action="directorUserAction.php" method="post" class="delete-user-form" data-user-name="<?php echo aitf_rbac_escape($employee['user_name']); ?>">
                                                            <input type="hidden" name="user_id" value="<?php echo (int)$employee['user_id']; ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <button type="submit" class="btn-action delete">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <?php if (empty($employees)) { ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-8 text-gray-500">No employees found.</td>
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

<style>
    :root {
        --bg-color: #FAFAFA;
        --surface: #FFFFFF;
        --border: #E5E7EB;
        --text-primary: #111827;
        --text-secondary: #6B7280;
        --accent: #2563EB;
        --accent-hover: #1D4ED8;
        --success: #16A34A;
        --success-bg: #DCFCE7;
        --warning: #F59E0B;
        --warning-bg: #FEF3C7;
        --danger: #DC2626;
        --danger-bg: #FEE2E2;
        --radius: 12px;
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
    }

    body, .director-wrapper {
        background-color: var(--bg-color);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        color: var(--text-primary);
        margin: 0;
    }

    .director-dashboard {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 24px;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Top Hero Section */
    .dashboard-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 32px;
        margin-bottom: 40px;
    }

    .hero-left {
        flex: 1;
    }

    .hero-title {
        font-size: 24px;
        font-weight: 600;
        letter-spacing: -0.01em;
        margin: 0 0 4px 0;
        color: var(--text-primary);
    }

    .hero-subtitle {
        font-size: 15px;
        color: var(--text-secondary);
        margin: 0 0 32px 0;
    }

    .hero-stats {
        display: flex;
        gap: 40px;
    }

    .hero-stat-card {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .stat-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* Live Team Card */
    .hero-right {
        width: 380px;
        flex-shrink: 0;
    }

    .live-team-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow);
    }

    .live-team-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .live-team-header h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
    }

    .live-team-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .live-user-pill {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-circle {
        position: relative;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #F3F4F6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        flex-shrink: 0;
    }

    .status-dot {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 10px;
        height: 10px;
        background: var(--success);
        border: 2px solid var(--surface);
        border-radius: 50%;
    }

    .live-user-name {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-primary);
    }
    
    .live-user-more {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 500;
        margin-top: 4px;
        padding-left: 44px;
    }

    .no-users {
        font-size: 14px;
        color: var(--text-secondary);
    }

    /* Drawer */
    .drawer-wrapper {
        visibility: hidden;
        opacity: 0;
        transition: visibility 0s 0.3s, opacity 0.3s ease;
    }
    
    .drawer-wrapper.open {
        visibility: visible;
        opacity: 1;
        transition: opacity 0.3s ease;
    }
    
    .drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(17, 24, 39, 0.2);
        backdrop-filter: blur(4px);
        z-index: 999;
    }
    
    .drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        width: 100%;
        max-width: 420px;
        height: 100vh;
        background: var(--surface);
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        box-shadow: -10px 0 30px rgba(0,0,0,0.1);
    }
    
    .drawer-wrapper.open .drawer-panel {
        transform: translateX(0);
    }

    .drawer-header {
        padding: 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .drawer-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }
    
    .btn-close {
        background: transparent;
        border: none;
        font-size: 16px;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
    }
    
    .btn-close:hover {
        background: #F3F4F6;
        color: var(--text-primary);
    }

    .drawer-content {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
    }

    .timeline {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    
    .timeline-item {
        display: flex;
        gap: 16px;
        position: relative;
    }
    
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 5px;
        top: 24px;
        bottom: -24px;
        width: 2px;
        background: var(--border);
    }
    
    .timeline-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-top: 6px;
        z-index: 1;
    }
    
    .indicator-green { background: var(--success); }
    .indicator-gray { background: var(--text-secondary); }
    
    .timeline-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        flex: 1;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    
    .timeline-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    
    .timeline-action {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }
    
    .timeline-time {
        font-size: 12px;
        color: #9CA3AF;
    }

    /* Main Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 24px;
    }

    .premium-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .card-header {
        padding: 24px;
        border-bottom: 1px solid var(--border);
    }

    .card-header h2 {
        margin: 0 0 4px 0;
        font-size: 16px;
        font-weight: 600;
    }

    .card-header p {
        margin: 0;
        font-size: 13px;
        color: var(--text-secondary);
    }
    
    .flex-between {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-body {
        padding: 24px;
    }

    /* Form */
    .premium-form .form-group {
        margin-bottom: 20px;
    }

    .premium-form label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .premium-form input, .premium-form select {
        width: 100%;
        padding: 12px 16px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        color: var(--text-primary);
        transition: all 0.2s;
        box-sizing: border-box;
    }
    
    .premium-form input::placeholder {
        color: #9CA3AF;
    }

    .premium-form input:focus, .premium-form select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .btn-primary {
        width: 100%;
        padding: 12px;
        background: var(--accent);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
    }

    .btn-primary:hover {
        background: var(--accent-hover);
    }

    /* Table & Directory */
    .search-wrapper {
        position: relative;
    }
    
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        font-size: 14px;
    }

    .search-input {
        padding: 8px 12px 8px 36px;
        background: var(--bg-color);
        border: 1px solid transparent;
        border-radius: 8px;
        font-size: 13px;
        width: 200px;
        transition: all 0.2s;
        font-family: inherit;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--border);
        background: var(--surface);
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .premium-table {
        width: 100%;
        border-collapse: collapse;
    }

    .premium-table th {
        text-align: left;
        padding: 12px 24px;
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border);
        background: #F9FAFB;
    }

    .premium-table td {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .premium-table tr:last-child td {
        border-bottom: none;
    }

    .premium-table tr:hover td {
        background: #F9FAFB;
    }

    .emp-profile {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .emp-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--bg-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        border: 1px solid var(--border);
    }

    .emp-info {
        display: flex;
        flex-direction: column;
    }

    .emp-name {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-primary);
    }

    .emp-email {
        font-size: 13px;
        color: var(--text-secondary);
    }

    .badge-role {
        font-size: 13px;
        color: var(--text-secondary);
    }

    .badge-eid {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 13px;
        color: var(--text-primary);
    }

    .badge-status {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-active { background: var(--success-bg); color: #15803D; }
    .status-inactive { background: var(--danger-bg); color: #B91C1C; }
    .status-pending { background: var(--warning-bg); color: #B45309; }

    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }
    
    .action-buttons form {
        margin: 0;
    }

    .btn-action {
        padding: 6px 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        background: var(--surface);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-action:hover {
        background: var(--bg-color);
    }

    .btn-action.delete {
        color: var(--danger);
        border-color: #FCA5A5;
    }
    .btn-action.delete:hover {
        background: var(--danger-bg);
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .py-8 { padding-top: 32px; padding-bottom: 32px; }
    .text-gray-500 { color: #6B7280; }

    .empty-timeline {
        color: var(--text-secondary);
        font-size: 14px;
        text-align: center;
        padding: 24px;
    }

    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
        .dashboard-hero {
            flex-direction: column;
        }
        .hero-right {
            width: 100%;
        }
        .hero-stats {
            flex-wrap: wrap;
        }
    }
    /* Floating Tab & Conflict Drawer Styles */
    .floating-tabs-container {
        position: fixed;
        right: 0;
        top: 40%;
        transform: translateY(-50%);
        z-index: 998;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .floating-tab {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 16px 8px;
        border-radius: 8px 0 0 8px;
        cursor: pointer;
        box-shadow: -2px 0px 8px rgba(0,0,0,0.2);
        font-size: 14px;
        font-weight: 600;
        transition: padding-right 0.2s;
    }
    
    .floating-tab:hover {
        padding-right: 12px;
    }
    
    .tab-red {
        background: #B91C1C;
        color: white;
    }
    
    .tab-badge {
        writing-mode: horizontal-tb;
        background: white;
        color: #B91C1C;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        margin-top: 8px;
    }
    
    .bg-red-header {
        background-color: #8B1C1C;
        border-bottom: none;
    }
    
    .btn-close-white {
        background: transparent;
        border: none;
        font-size: 16px;
        color: rgba(255, 255, 255, 0.8);
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
    }
    
    .btn-close-white:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }
    
    .drawer-gray-bg {
        background-color: #F3F4F6;
    }
    
    .conflict-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    .conflict-card {
        background: white;
        border-left: 3px solid #B91C1C;
        border-radius: 6px;
        border-top: 1px solid #E5E7EB;
        border-right: 1px solid #E5E7EB;
        border-bottom: 1px solid #E5E7EB;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .conflict-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .font-bold { font-weight: 700; color: #8B1C1C; }
    .text-sm { font-size: 13px; }
    .text-red { color: #B91C1C; font-weight: 600; }
    
    .font-semibold { font-weight: 600; color: #111827; font-size: 14px; }
    .mb-1 { margin-bottom: 6px; }
    .text-xs { font-size: 12px; }
    .text-gray-500 { color: #6B7280; }
    .text-gray-600 { color: #4B5563; }
    .text-green-600 { color: #16A34A; }
</style>


<script>
    var empSearch = document.getElementById('empSearch');
    if (empSearch) {
        empSearch.addEventListener('input', function(e) {
            let text = e.target.value.toLowerCase();
            let rows = document.querySelectorAll('#empTable tbody tr');

            rows.forEach(row => {
                let nameNode = row.querySelector('.emp-name');
                let emailNode = row.querySelector('.emp-email');
                if (!nameNode || !emailNode) {
                    return;
                }
                let name = nameNode.textContent.toLowerCase();
                let email = emailNode.textContent.toLowerCase();

                row.style.display = (name.includes(text) || email.includes(text)) ? '' : 'none';
            });
        });
    }

    var activityToggle = document.getElementById('activityToggle');
    var todayActivityPanel = document.getElementById('todayActivityPanel');
    if (activityToggle && todayActivityPanel) {
        activityToggle.addEventListener('click', function() {
            todayActivityPanel.classList.toggle('open');
            activityToggle.classList.toggle('active');
        });
    }

    document.querySelectorAll('.delete-user-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            var userName = form.getAttribute('data-user-name') || 'this user';
            var confirmed = window.confirm('Are you sure to permanently delete this user: ' + userName + '?');
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
</script>


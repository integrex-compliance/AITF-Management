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

$empRecordsStmt = $db->prepare("SELECT * FROM employment_records ORDER BY created_at DESC");
$empRecordsStmt->execute();
$empRecords = $empRecordsStmt->fetchAll(PDO::FETCH_ASSOC);

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
                                   AND role != 'Director'
                                   AND last_seen_at >= DATE_SUB(NOW(), INTERVAL 15 SECOND)
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
                        </div>
                    </div>
                    
                    <div class="hero-right">
                        <div class="modern-team-card">
                            <h3 class="team-card-title">Live Team Status</h3>
                            <div class="team-avatar-group">
                                <?php 
                                $displayLimit = 5;
                                $count = 0;
                                foreach ($activeUsers as $activeUser) {
                                    if ($count < $displayLimit) {
                                        $fullName = aitf_rbac_escape($activeUser['user_name']);
                                        // If it's the director, try to fetch their real name using their email
                                        if ($activeUser['role'] === 'Director' && !empty($activeUser['email_id'])) {
                                            $dirNameStmt = $db->prepare("SELECT user_name FROM user WHERE email_id = :email LIMIT 1");
                                            $dirNameStmt->execute([':email' => $activeUser['email_id']]);
                                            $dirActualName = $dirNameStmt->fetchColumn();
                                            if ($dirActualName) {
                                                $fullName = aitf_rbac_escape($dirActualName);
                                            } else {
                                                $fullName = "Director";
                                            }
                                        }
                                        $firstName = explode(' ', $fullName)[0];
                                        $initial = strtoupper(substr($firstName, 0, 1));
                                        ?>
                                        <div class="team-avatar tooltip-container">
                                            <span><?php echo $initial; ?></span>
                                            <div class="online-indicator"></div>
                                            <span class="tooltip-text"><?php echo $fullName; ?> (<?php echo aitf_rbac_escape($activeUser['role']); ?>)</span>
                                        </div>
                                        <?php
                                    }
                                    $count++;
                                }
                                $remaining = $count - $displayLimit;
                                if ($remaining > 0) {
                                    ?>
                                    <div class="team-avatar more-avatar">
                                        <span>+<?php echo $remaining; ?></span>
                                    </div>
                                    <?php
                                }
                                if ($count === 0) {
                                    ?>
                                    <span class="no-online-text">No users online</span>
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

                <!-- Tabs Navigation -->
                <div class="director-tabs">
                    <button class="dir-tab-btn active" data-target="tab-hr">
                        <i class="fa fa-users"></i> Add Employment Record
                    </button>
                    <button class="dir-tab-btn" data-target="tab-software">
                        <i class="fa fa-desktop"></i> Add to Software
                    </button>
                    <button class="dir-tab-btn" data-target="tab-permissions">
                        <i class="fa fa-lock"></i> Permissions
                    </button>
                    <button class="dir-tab-btn" data-target="tab-reports">
                        <i class="fa fa-bar-chart"></i> Reports
                    </button>
                    <button class="dir-tab-btn" data-target="tab-logs">
                        <i class="fa fa-shield"></i> Security Logs
                    </button>
                    <button class="dir-tab-btn" data-target="tab-settings">
                        <i class="fa fa-cog"></i> Global Settings
                    </button>
                </div>

                <div class="director-tab-content">
                    <!-- Tab 1: HR Employment Record (Active) -->
                    <div id="tab-hr" class="dir-tab-pane active">
                        <div class="dashboard-grid">
                            <!-- Left Column: HR Form -->
                            <div class="dashboard-col-left">
                                <div class="premium-card">
                                    <div class="card-header">
                                        <h2>Add Employment Record</h2>
                                        <p>Register a new employee in the company records.</p>
                                    </div>
                                    <div class="card-body">
                                        <form action="addEmploymentRecord.php" method="post" class="premium-form" id="hrForm">
                                            <input type="hidden" name="emp_id" id="hr_emp_id" value="">
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" name="name" id="hr_name" required placeholder="e.g. John Doe">
                                            </div>
                                            <div class="form-group">
                                                <label>Age</label>
                                                <input type="number" name="age" id="hr_age" required placeholder="e.g. 28">
                                            </div>
                                            <div class="form-group">
                                                <label>Email Address</label>
                                                <input type="email" name="email" id="hr_email" required placeholder="name@company.com">
                                            </div>
                                            <div class="form-group">
                                                <label>Mobile Number</label>
                                                <input type="text" name="mobile" id="hr_mobile" required placeholder="+91 00000 00000">
                                            </div>
                                            <div class="form-group">
                                                <label>CTC (Annual)</label>
                                                <input type="text" name="ctc" id="hr_ctc" required placeholder="e.g. 500000">
                                            </div>
                                            <div class="form-group">
                                                <label>Job Profile</label>
                                                <select name="job_profile" id="hr_job_profile" required>
                                                    <option value="" disabled selected>Select Profile...</option>
                                                    <option value="Auditor">Auditor</option>
                                                    <option value="Technical Staff">Technical Staff</option>
                                                    <option value="Operational Staff">Operational Staff</option>
                                                    <option value="Facilitator">Facilitator</option>
                                                    <option value="CEO">CEO</option>
                                                    <option value="Marketing Executive">Marketing Executive</option>
                                                    <option value="Finance">Finance</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div style="display: flex; gap: 8px;">
                                                <button type="submit" class="btn-primary" id="hr_submit_btn" style="flex: 1;">
                                                    Save Employment Record
                                                </button>
                                                <button type="button" class="btn-action deactivate" id="hr_cancel_btn" style="display:none; padding: 10px 16px; border-radius: 8px;" onclick="cancelHrEdit()">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: HR Directory -->
                            <div class="dashboard-col-right">
                                <div class="premium-card">
                                    <div class="card-header flex-between">
                                        <div>
                                            <h2>Registered Employees (HR)</h2>
                                            <p>Manage company employment records</p>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="premium-table" id="hrEmpTable">
                                                <thead>
                                                    <tr>
                                                        <th>Employee</th>
                                                        <th>Profile</th>
                                                        <th>Contact</th>
                                                        <th>CTC</th>
                                                        <th class="text-right">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($empRecords as $record) { ?>
                                                        <tr>
                                                            <td>
                                                                <div class="emp-cell">
                                                                    <div class="emp-avatar"><?php echo strtoupper(substr(aitf_rbac_escape($record['name']), 0, 1)); ?></div>
                                                                    <div class="emp-info">
                                                                        <span class="emp-name"><?php echo aitf_rbac_escape($record['name']); ?> (<?php echo (int)$record['age']; ?>)</span>
                                                                        <span class="emp-email">ID: <?php echo (int)$record['id']; ?></span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><span class="badge-role"><?php echo aitf_rbac_escape($record['job_profile']); ?></span></td>
                                                            <td>
                                                                <div class="text-sm"><?php echo aitf_rbac_escape($record['email']); ?></div>
                                                                <div class="text-xs text-gray-500"><?php echo aitf_rbac_escape($record['mobile']); ?></div>
                                                            </td>
                                                            <td class="font-semibold text-sm" style="white-space: nowrap;">
                                                                <span class="ctc-display" data-actual="<?php echo aitf_rbac_escape($record['ctc']); ?>">********</span>
                                                                <button type="button" class="btn-icon text-gray-500 toggle-ctc" style="border:none; background:transparent; cursor:pointer; margin-left: 4px;" title="Toggle Visibility">
                                                                    <i class="fa fa-eye"></i>
                                                                </button>
                                                            </td>
                                                            <td>
                                                                <div class="action-buttons text-right" style="display:flex; justify-content:flex-end; gap:8px;">
                                                                    <button type="button" class="btn-action activate" onclick='editHrRecord(<?php echo json_encode([
                                                                        "id" => (int)$record["id"],
                                                                        "name" => $record["name"],
                                                                        "age" => $record["age"],
                                                                        "email" => $record["email"],
                                                                        "mobile" => $record["mobile"],
                                                                        "ctc" => $record["ctc"],
                                                                        "job_profile" => $record["job_profile"]
                                                                    ]); ?>)'>Edit</button>
                                                                    <form action="addEmploymentRecord.php" method="post" class="delete-user-form" data-user-name="<?php echo aitf_rbac_escape($record['name']); ?>">
                                                                        <input type="hidden" name="id" value="<?php echo (int)$record['id']; ?>">
                                                                        <input type="hidden" name="action" value="delete">
                                                                        <button type="submit" class="btn-action delete">Delete</button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if (empty($empRecords)) { ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center py-8 text-gray-500">No employment records found.</td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End Tab 1 -->

                    <!-- Tab 2: Software Access -->
                    <div id="tab-software" class="dir-tab-pane">
                        <!-- Main Grid -->
                        <div class="dashboard-grid">
                    
                    <!-- Left Column -->
                    <div class="dashboard-col-left">
                        <div class="premium-card">
                            <div class="card-header">
                                <h2>Grant Software Access</h2>
                                <p>Provide software login credentials and set role permissions.</p>
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
                                                    <div class="action-buttons text-right" style="display:flex; justify-content:flex-end; gap:8px;">
                                                        <button type="button" class="btn-action" style="background:#4B5563; color:white; border-color:#374151;" onclick="openScreenTimeModal(<?php echo (int)$employee['user_id']; ?>, '<?php echo aitf_rbac_escape($employee['user_name']); ?>')">
                                                            <i class="fa fa-clock-o"></i> Time
                                                        </button>
                                                        <?php if ($status === 'inactive') { ?>
                                                            <form action="directorUserAction.php" method="post" style="margin:0;">
                                                                <input type="hidden" name="user_id" value="<?php echo (int)$employee['user_id']; ?>">
                                                                <input type="hidden" name="action" value="activate">
                                                                <button type="submit" class="btn-action activate">Activate</button>
                                                            </form>
                                                        <?php } elseif ($status === 'active') { ?>
                                                            <form action="directorUserAction.php" method="post" style="margin:0;">
                                                                <input type="hidden" name="user_id" value="<?php echo (int)$employee['user_id']; ?>">
                                                                <input type="hidden" name="action" value="deactivate">
                                                                <button type="submit" class="btn-action deactivate">Deactivate</button>
                                                            </form>
                                                        <?php } ?>

                                                        <form action="directorUserAction.php" method="post" class="delete-user-form" data-user-name="<?php echo aitf_rbac_escape($employee['user_name']); ?>" style="margin:0;">
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
                </div> <!-- End Main Grid -->
                </div> <!-- End Tab 1 -->

                <!-- Tab 2: Permissions -->
                <div id="tab-permissions" class="dir-tab-pane">
                    <div class="premium-card">
                        <div class="card-header">
                            <h2>Role Permissions</h2>
                            <p>Manage what each role can access in the system.</p>
                        </div>
                        <div class="card-body" style="padding: 60px 40px; text-align: center; color: var(--text-secondary);">
                            <i class="fa fa-lock" style="font-size: 48px; color: #E5E7EB; margin-bottom: 16px;"></i>
                            <h3>Permissions Module</h3>
                            <p>This section is under construction.</p>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Reports -->
                <div id="tab-reports" class="dir-tab-pane">
                    <div class="premium-card">
                        <div class="card-header">
                            <h2>System Reports</h2>
                            <p>View overall system usage and analytics.</p>
                        </div>
                        <div class="card-body" style="padding: 60px 40px; text-align: center; color: var(--text-secondary);">
                            <i class="fa fa-bar-chart" style="font-size: 48px; color: #E5E7EB; margin-bottom: 16px;"></i>
                            <h3>Reports Module</h3>
                            <p>This section is under construction.</p>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Logs -->
                <div id="tab-logs" class="dir-tab-pane">
                    <div class="premium-card">
                        <div class="card-header">
                            <h2>Security & Audit Logs</h2>
                            <p>Track all critical actions performed by users.</p>
                        </div>
                        <div class="card-body" style="padding: 60px 40px; text-align: center; color: var(--text-secondary);">
                            <i class="fa fa-shield" style="font-size: 48px; color: #E5E7EB; margin-bottom: 16px;"></i>
                            <h3>Security Logs Module</h3>
                            <p>This section is under construction.</p>
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Settings -->
                <div id="tab-settings" class="dir-tab-pane">
                    <div class="premium-card">
                        <div class="card-header">
                            <h2>Global Settings</h2>
                            <p>Configure system-wide parameters.</p>
                        </div>
                        <div class="card-body" style="padding: 60px 40px; text-align: center; color: var(--text-secondary);">
                            <i class="fa fa-cog" style="font-size: 48px; color: #E5E7EB; margin-bottom: 16px;"></i>
                            <h3>Settings Module</h3>
                            <p>This section is under construction.</p>
                        </div>
                    </div>
                </div>
            </div> <!-- End Tab Content Container -->
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
        font-size: 20px;
        font-weight: 500;
        letter-spacing: 0.01em;
        margin: 0 0 6px 0;
        color: #1F2937;
    }

    .hero-subtitle {
        font-size: 14px;
        color: #6B7280;
        font-weight: 400;
        margin: 0 0 24px 0;
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
        font-size: 11px;
        font-weight: 600;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .stat-value {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
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

    .modern-team-card {
        background: #ffffff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-width: 180px;
    }
    
    .team-card-title {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        color: #4B5563;
        letter-spacing: 0.02em;
    }
    
    .team-avatar-group {
        display: flex;
        align-items: center;
    }
    
    .team-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #F3F4F6;
        border: 2px solid #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-left: -8px;
        position: relative;
        cursor: pointer;
        transition: transform 0.2s, z-index 0.2s;
    }
    
    .team-avatar:first-child {
        margin-left: 0;
    }
    
    .team-avatar:hover {
        transform: translateY(-2px);
        z-index: 10;
    }
    
    .online-indicator {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 10px;
        height: 10px;
        background: #10B981;
        border: 2px solid #ffffff;
        border-radius: 50%;
    }
    
    .more-avatar {
        background: #E5E7EB;
        color: #4B5563;
        font-size: 12px;
    }
    
    .no-online-text {
        font-size: 13px;
        color: #9CA3AF;
        font-style: italic;
    }
    
    .tooltip-container .tooltip-text {
        visibility: hidden;
        background-color: #1F2937;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 4px 8px;
        position: absolute;
        z-index: 20;
        bottom: 120%;
        left: 50%;
        transform: translateX(-50%);
        font-size: 11px;
        opacity: 0;
        transition: opacity 0.2s;
        white-space: nowrap;
    }
    
    .tooltip-container:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    /* Tabs Styles */
    .director-tabs {
        display: flex;
        gap: 8px;
        background: #F3F4F6;
        padding: 6px;
        border-radius: 12px;
        margin-bottom: 24px;
        overflow-x: auto;
        border: 1px solid var(--border);
    }
    
    .dir-tab-btn {
        padding: 12px 20px;
        border: none;
        background: transparent;
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 14px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
    
    .dir-tab-btn:hover {
        background: #E5E7EB;
        color: var(--text-primary);
    }
    
    .dir-tab-btn.active {
        background: var(--surface);
        color: var(--accent);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        font-weight: 600;
    }
    
    .dir-tab-pane {
        display: none;
        animation: fadeInTab 0.3s ease-out;
    }
    
    .dir-tab-pane.active {
        display: block;
    }
    
    @keyframes fadeInTab {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
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

    // Tabs functionality
    document.querySelectorAll('.dir-tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            // Remove active from all buttons
            document.querySelectorAll('.dir-tab-btn').forEach(btn => btn.classList.remove('active'));
            // Remove active from all panes
            document.querySelectorAll('.dir-tab-pane').forEach(pane => pane.classList.remove('active'));
            
            // Add active to clicked button
            button.classList.add('active');
            // Show corresponding pane
            const targetId = button.getAttribute('data-target');
            document.getElementById(targetId).classList.add('active');
        });
    });
    // CTC Toggle functionality
    document.querySelectorAll('.toggle-ctc').forEach(button => {
        button.addEventListener('click', function() {
            const displaySpan = this.previousElementSibling;
            const actualCtc = displaySpan.getAttribute('data-actual');
            const icon = this.querySelector('i');
            
            if (displaySpan.textContent === '********') {
                displaySpan.textContent = actualCtc;
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                displaySpan.textContent = '********';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Edit HR Record functionality
    function editHrRecord(record) {
        document.getElementById('hr_emp_id').value = record.id;
        document.getElementById('hr_name').value = record.name;
        document.getElementById('hr_age').value = record.age;
        document.getElementById('hr_email').value = record.email;
        document.getElementById('hr_mobile').value = record.mobile;
        document.getElementById('hr_ctc').value = record.ctc;
        document.getElementById('hr_job_profile').value = record.job_profile;
        
        document.getElementById('hr_submit_btn').textContent = 'Update Employment Record';
        document.getElementById('hr_cancel_btn').style.display = 'block';
        
        // Scroll to form smoothly
        document.getElementById('hrForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Cancel Edit HR Record
    function cancelHrEdit() {
        document.getElementById('hrForm').reset();
        document.getElementById('hr_emp_id').value = '';
        document.getElementById('hr_submit_btn').textContent = 'Save Employment Record';
        document.getElementById('hr_cancel_btn').style.display = 'none';
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div id="screenTimeModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
    <div class="modal-content" style="background:#f9fafb; width:95%; max-width:900px; margin:40px auto; padding:0; border-radius:12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow:hidden; display:flex; flex-direction:column; max-height:90vh;">
        <!-- Header -->
        <div style="background:#ffffff; padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h2 style="margin:0; font-size:20px; color:#1f2937; font-weight:600;">Screen Time Report</h2>
                <p style="margin:4px 0 0 0; color:#6b7280; font-size:14px;" id="stUser"></p>
            </div>
            <button onclick="document.getElementById('screenTimeModal').style.display='none'" style="border:none; background:transparent; font-size:28px; cursor:pointer; color:#9ca3af; transition:color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#9ca3af'">&times;</button>
        </div>
        
        <!-- Body -->
        <div style="padding:24px; overflow-y:auto; flex:1;">
            
            <!-- Summary Stats -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:#ffffff; padding:16px; border-radius:8px; border:1px solid #e5e7eb; box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                    <div style="font-size:13px; color:#6b7280; font-weight:500; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">Today</div>
                    <div id="stToday" style="font-size:24px; font-weight:700; color:#3b82f6;">0m 0s</div>
                </div>
                <div style="background:#ffffff; padding:16px; border-radius:8px; border:1px solid #e5e7eb; box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                    <div style="font-size:13px; color:#6b7280; font-weight:500; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">This Month</div>
                    <div id="stMonth" style="font-size:24px; font-weight:700; color:#10b981;">0m 0s</div>
                </div>
                <div style="background:#ffffff; padding:16px; border-radius:8px; border:1px solid #e5e7eb; box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                    <div style="font-size:13px; color:#6b7280; font-weight:500; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">All Time</div>
                    <div id="stTotal" style="font-size:24px; font-weight:700; color:#8b5cf6;">0m 0s</div>
                </div>
            </div>

            <!-- Chart -->
            <div style="background:#ffffff; padding:20px; border-radius:8px; border:1px solid #e5e7eb; margin-bottom:24px;">
                <h3 style="margin:0 0 16px 0; font-size:16px; color:#374151;">Past 30 Days (Trend)</h3>
                <div style="height:250px;">
                    <canvas id="screenTimeChart"></canvas>
                </div>
            </div>

            <!-- Table -->
            <div style="background:#ffffff; border-radius:8px; border:1px solid #e5e7eb; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead style="background:#f3f4f6; border-bottom:1px solid #e5e7eb;">
                        <tr>
                            <th style="padding:12px 16px; font-size:13px; color:#4b5563; font-weight:600;">Date</th>
                            <th style="padding:12px 16px; font-size:13px; color:#4b5563; font-weight:600;">Screen Time Logged</th>
                        </tr>
                    </thead>
                    <tbody id="stTableBody" style="font-size:14px; color:#1f2937;">
                        <!-- Rows injected via JS -->
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>

<script>
    let stChart = null;
    function openScreenTimeModal(userId, userName) {
        document.getElementById('stUser').innerText = userName;
        document.getElementById('screenTimeModal').style.display = 'block';
        
        fetch('getScreenTime.php?user_id=' + userId)
            .then(res => res.json())
            .then(data => {
                // Populate Stats
                document.getElementById('stToday').innerText = data.stats.today || '0m 0s';
                document.getElementById('stMonth').innerText = data.stats.month || '0m 0s';
                document.getElementById('stTotal').innerText = data.stats.total || '0m 0s';
                
                // Populate Table
                const tbody = document.getElementById('stTableBody');
                tbody.innerHTML = '';
                if (data.table && data.table.length > 0) {
                    data.table.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.style.borderBottom = '1px solid #f3f4f6';
                        tr.innerHTML = `
                            <td style="padding:12px 16px;">${row.date}</td>
                            <td style="padding:12px 16px; font-weight:500; color:#374151;">${row.time}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="2" style="padding:16px; text-align:center; color:#6b7280;">No screen time recorded yet.</td></tr>';
                }

                // Render Chart
                const chartData = data.chart || [];
                const labels = chartData.map(d => d.date);
                const minutes = chartData.map(d => d.minutes);
                const tooltips = chartData.map(d => d.label);
                
                const ctx = document.getElementById('screenTimeChart').getContext('2d');
                if (stChart) stChart.destroy();
                
                stChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Screen Time (Minutes)',
                            data: minutes,
                            backgroundColor: '#3b82f6',
                            borderRadius: 6,
                            barPercentage: 0.6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return tooltips[context.dataIndex];
                                    }
                                }
                            },
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Minutes'
                                }
                            }
                        }
                    }
                });
            })
            .catch(err => {
                console.error("Failed to load screen time data:", err);
            });
    }
</script>

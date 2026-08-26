<div id="wrapper" class="active director-wrapper">
<?php
$pageIdentifier = "Director Space";
include_once './header.php';
include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';

date_default_timezone_set('Asia/Kolkata');

if (!Utitlity::checkForDirector()) {
    header("Location: login.php?reason=login_required");
    exit;
}

aitf_ensure_rbac_schema($db);

// Ensure the employment_records table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS employment_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        age INT NULL,
        email VARCHAR(150) NULL,
        mobile VARCHAR(50) NULL,
        ctc VARCHAR(50) NULL,
        job_profile VARCHAR(100) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
} catch (Exception $e) {
    error_log('employment_records schema error: ' . $e->getMessage());
}

$notice = trim((string)($_SESSION['director_notice'] ?? ''));
unset($_SESSION['director_notice']);

$roles = aitf_allowed_roles();

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
                        <div class="hero-badge">
                            <span class="pulse-beacon"></span> Top MGMT Space — Executive Portal
                        </div>
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
                        <p class="hero-subtitle">Real-time workforce intelligence, employee records, and system administration.</p>
                        
                        <div class="hero-stats">
                            <div class="hero-stat-card">
                                <div class="stat-icon-wrap icon-blue"><i class="fa fa-users"></i></div>
                                <div class="stat-info">
                                    <span class="stat-label">Total Workforce</span>
                                    <span class="stat-value"><?php echo $totalUsers; ?> <small class="stat-unit">Employees</small></span>
                                </div>
                            </div>
                            <div class="hero-stat-card">
                                <div class="stat-icon-wrap icon-green"><i class="fa fa-dot-circle-o"></i></div>
                                <div class="stat-info">
                                    <span class="stat-label">Active Online</span>
                                    <span class="stat-value"><?php echo count($activeUsers); ?> <small class="stat-unit">Live Sessions</small></span>
                                </div>
                            </div>
                            <div class="hero-stat-card">
                                <div class="stat-icon-wrap icon-purple"><i class="fa fa-sign-in"></i></div>
                                <div class="stat-info">
                                    <span class="stat-label">Today's Logins</span>
                                    <span class="stat-value"><?php echo $todayLoginCount; ?> <small class="stat-unit">Active Today</small></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hero-right">
                        <div class="modern-team-card">
                            <div class="team-card-top">
                                <h3 class="team-card-title"><span class="live-dot-pulse"></span> Live Team Presence</h3>
                                <span class="team-live-pill"><?php echo count($activeUsers); ?> Online</span>
                            </div>
                            <div class="team-avatar-group">
                                <?php 
                                $displayLimit = 6;
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
                                    <span class="no-online-text"><i class="fa fa-info-circle"></i> No employees active right now</span>
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
                                                        <button type="button" class="btn-action" style="background:#10B981; color:white; border-color:#059669;" onclick="openMessageModal(<?php echo (int)$employee['user_id']; ?>, '<?php echo addslashes(aitf_rbac_escape($employee['user_name'])); ?>')">
                                                            <i class="fa fa-envelope"></i> Message
                                                        </button>
                                                        <button type="button" class="btn-action" style="background:#4B5563; color:white; border-color:#374151;" onclick="openScreenTimeModal(<?php echo (int)$employee['user_id']; ?>, '<?php echo addslashes(aitf_rbac_escape($employee['user_name'])); ?>')">
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
                            <h2>User-Level Permissions</h2>
                            <p>Select a user from the list to configure their specific access rights.</p>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <div style="display: flex; height: 500px;">
                                <!-- Left side: User List -->
                                <div style="width: 300px; border-right: 1px solid var(--border); overflow-y: auto; background: #FAFAFA;">
                                    <div style="padding: 16px; border-bottom: 1px solid var(--border); background: white; position: sticky; top: 0;">
                                        <input type="text" id="permUserSearch" placeholder="Search user..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; outline: none;">
                                    </div>
                                    <ul id="permUserList" style="list-style: none; padding: 0; margin: 0;">
                                        <li style="padding: 20px; text-align: center; color: #9ca3af;">Loading users...</li>
                                    </ul>
                                </div>
                                
                                <!-- Right side: Settings -->
                                <div style="flex: 1; padding: 32px; background: white;" id="permSettingsPane">
                                    <div style="text-align: center; color: #9ca3af; margin-top: 100px;" id="permPlaceholder">
                                        <i class="fa fa-hand-o-left" style="font-size: 48px; margin-bottom: 16px;"></i>
                                        <h3>Select a user</h3>
                                        <p>Click on a user from the left panel to manage their permissions.</p>
                                    </div>
                                    
                                    <div id="permSettings" style="display: none;">
                                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">
                                            <div class="emp-avatar" style="width: 48px; height: 48px; font-size: 18px;" id="permAvatar">U</div>
                                            <div>
                                                <h3 style="margin: 0; font-size: 18px; color: var(--text-primary);" id="permName">User Name</h3>
                                                <span class="badge-role" id="permRole" style="display: inline-block; margin-top: 4px;">Role</span>
                                            </div>
                                        </div>
                                        
                                        <div style="display: flex; flex-direction: column; gap: 24px;">
                                            <!-- Permission Toggle 1 -->
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: #FAFAFA; border: 1px solid var(--border); border-radius: 8px;">
                                                <div>
                                                    <h4 style="margin: 0 0 4px 0; color: var(--text-primary); font-size: 15px;"><i class="fa fa-building" style="width:20px; color:var(--text-secondary);"></i> Client Access</h4>
                                                    <p style="margin: 0; font-size: 13px; color: var(--text-secondary);">Allow this user to see the CLIENTS tab in the left menu.</p>
                                                </div>
                                                <label class="switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                                                  <input type="checkbox" id="toggleClientAccess" style="opacity: 0; width: 0; height: 0;">
                                                  <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px;"></span>
                                                </label>
                                            </div>
                                            
                                            <!-- Permission Toggle 2 -->
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: #FAFAFA; border: 1px solid var(--border); border-radius: 8px;">
                                                <div>
                                                    <h4 style="margin: 0 0 4px 0; color: var(--text-primary); font-size: 15px;"><i class="fa fa-inr" style="width:20px; color:var(--text-secondary);"></i> Invoice Access</h4>
                                                    <p style="margin: 0; font-size: 13px; color: var(--text-secondary);">Allow this user to see the INVOICES tab in the left menu.</p>
                                                </div>
                                                <label class="switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                                                  <input type="checkbox" id="toggleInvoiceAccess" style="opacity: 0; width: 0; height: 0;">
                                                  <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px;"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    /* ==========================================================================
       DIRECTOR SPACE — EXECUTIVE SAAS DESIGN SYSTEM
       ========================================================================== */
    :root {
        --dir-primary: #0d4d8b;
        --dir-primary-hover: #093968;
        --dir-accent: #2563eb;
        --dir-accent-light: #eff6ff;
        --dir-bg: #f8fafc;
        --dir-surface: #ffffff;
        --dir-surface-hover: #f1f5f9;
        --dir-border: #e2e8f0;
        --dir-border-subtle: #f1f5f9;
        --dir-text: #0f172a;
        --dir-text-secondary: #475569;
        --dir-text-muted: #64748b;
        --dir-success: #10b981;
        --dir-success-bg: #ecfdf5;
        --dir-danger: #ef4444;
        --dir-danger-bg: #fef2f2;
        --dir-warning: #f59e0b;
        --dir-warning-bg: #fffbeb;
        --dir-radius: 14px;
        --dir-shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
        --dir-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
        --dir-shadow-lg: 0 12px 32px -4px rgba(15, 23, 42, 0.08);
    }

    body, .director-wrapper {
        background-color: var(--dir-bg);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: var(--dir-text);
        margin: 0;
        -webkit-font-smoothing: antialiased;
    }

    .director-dashboard {
        max-width: 1540px;
        margin: 0 auto;
        padding: 18px 24px 48px 24px;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ==========================================================================
       EXECUTIVE HERO COMMAND CENTER
       ========================================================================== */
    .dashboard-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #eff6ff 100%);
        border: 1px solid var(--dir-border);
        border-radius: 16px;
        padding: 24px 28px;
        box-shadow: var(--dir-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 28px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .dashboard-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #0d4d8b 0%, #2563eb 50%, #38bdf8 100%);
    }

    .hero-left {
        flex: 1;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        background: #e0f2fe;
        color: #0369a1;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 8px;
    }

    .pulse-beacon {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #0284c7;
        box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.7);
        animation: pulseBeacon 2s infinite;
    }

    @keyframes pulseBeacon {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(2, 132, 199, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(2, 132, 199, 0); }
    }

    .hero-title {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin: 0 0 4px 0;
        color: #0f172a;
    }

    .hero-subtitle {
        font-size: 13.5px;
        color: var(--dir-text-muted);
        font-weight: 400;
        margin: 0 0 20px 0;
    }

    .hero-stats {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .hero-stat-card {
        background: #ffffff;
        border: 1px solid var(--dir-border);
        border-radius: 12px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: var(--dir-shadow-sm);
        min-width: 170px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hero-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--dir-shadow);
    }

    .stat-icon-wrap {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .stat-icon-wrap.icon-blue {
        background: #eff6ff;
        color: #2563eb;
    }

    .stat-icon-wrap.icon-green {
        background: #ecfdf5;
        color: #059669;
    }

    .stat-icon-wrap.icon-purple {
        background: #f5f3ff;
        color: #7c3aed;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-label {
        font-size: 10.5px;
        font-weight: 700;
        color: var(--dir-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .stat-unit {
        font-size: 11px;
        font-weight: 600;
        color: #94a3b8;
        margin-left: 2px;
    }

    /* Live Team Card */
    .hero-right {
        width: 340px;
        flex-shrink: 0;
    }

    .modern-team-card {
        background: #ffffff;
        border: 1px solid var(--dir-border);
        border-radius: 14px;
        padding: 16px 18px;
        box-shadow: var(--dir-shadow-sm);
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .team-card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .team-card-title {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .live-dot-pulse {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseBeacon 2s infinite;
    }

    .team-live-pill {
        font-size: 11px;
        font-weight: 700;
        color: #059669;
        background: #ecfdf5;
        padding: 3px 8px;
        border-radius: 20px;
    }

    .team-avatar-group {
        display: flex;
        align-items: center;
        padding: 4px 0;
    }

    .team-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border: 2.5px solid #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        margin-left: -10px;
        position: relative;
        cursor: pointer;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), z-index 0.2s;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
    }

    .team-avatar:first-child {
        margin-left: 0;
    }

    .team-avatar:hover {
        transform: translateY(-4px) scale(1.08);
        z-index: 10;
    }

    .online-indicator {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 10px;
        height: 10px;
        background: #10b981;
        border: 2px solid #ffffff;
        border-radius: 50%;
    }

    .more-avatar {
        background: #0f172a;
        color: #ffffff;
        font-size: 11.5px;
        font-weight: 700;
    }

    .no-online-text {
        font-size: 12.5px;
        color: #94a3b8;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tooltip-container .tooltip-text {
        visibility: hidden;
        background-color: #0f172a;
        color: #ffffff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 10px;
        position: absolute;
        z-index: 50;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        font-size: 11px;
        font-weight: 600;
        opacity: 0;
        transition: opacity 0.2s, transform 0.2s;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .tooltip-container:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
        transform: translateX(-50%) translateY(-2px);
    }

    /* ==========================================================================
       SEGMENTED EXECUTIVE TABS
       ========================================================================== */
    .director-tabs {
        display: flex;
        gap: 6px;
        background: #f1f5f9;
        padding: 6px;
        border-radius: 12px;
        margin-bottom: 24px;
        overflow-x: auto;
        border: 1px solid var(--dir-border);
        scrollbar-width: none;
    }

    .director-tabs::-webkit-scrollbar {
        display: none;
    }

    .dir-tab-btn {
        padding: 10px 18px;
        border: none;
        background: transparent;
        color: var(--dir-text-muted);
        font-weight: 600;
        font-size: 13.5px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        outline: none;
    }

    .dir-tab-btn:hover {
        background: rgba(255, 255, 255, 0.7);
        color: #0f172a;
    }

    .dir-tab-btn.active {
        background: #ffffff;
        color: var(--dir-primary);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
        font-weight: 700;
    }

    .dir-tab-pane {
        display: none;
        animation: fadeInTab 0.25s ease-out;
    }

    .dir-tab-pane.active {
        display: block;
    }

    @keyframes fadeInTab {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ==========================================================================
       DASHBOARD GRID & PREMIUM CARDS
       ========================================================================== */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 24px;
        align-items: start;
    }

    .premium-card {
        background: #ffffff;
        border: 1px solid var(--dir-border);
        border-radius: var(--dir-radius);
        box-shadow: var(--dir-shadow);
        overflow: hidden;
    }

    .card-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--dir-border-subtle);
        background: linear-gradient(180deg, #ffffff 0%, #fafbfd 100%);
    }

    .card-header h2 {
        margin: 0 0 3px 0;
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.01em;
    }

    .card-header p {
        margin: 0;
        font-size: 12.5px;
        color: var(--dir-text-muted);
    }

    .flex-between {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .card-body {
        padding: 22px 24px;
    }

    /* ==========================================================================
       PREMIUM FORMS & INPUTS
       ========================================================================== */
    .premium-form .form-group {
        margin-bottom: 16px;
    }

    .premium-form label {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }

    .premium-form input, 
    .premium-form select {
        width: 100%;
        padding: 10px 14px;
        background: #ffffff;
        border: 1.5px solid var(--dir-border);
        border-radius: 8px;
        font-size: 13.5px;
        font-family: inherit;
        color: #0f172a;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box;
    }

    .premium-form input::placeholder {
        color: #94a3b8;
    }

    .premium-form input:focus, 
    .premium-form select:focus {
        outline: none;
        border-color: var(--dir-primary);
        box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12);
    }

    .btn-primary {
        width: 100%;
        padding: 11px 18px;
        background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: inherit;
        box-shadow: 0 3px 10px rgba(13, 77, 139, 0.25);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(13, 77, 139, 0.35);
        color: #ffffff;
    }

    /* ==========================================================================
       PREMIUM TABLES & DIRECTORY
       ========================================================================== */
    .search-wrapper {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 13px;
    }

    .search-input {
        padding: 8px 12px 8px 34px;
        background: #f8fafc;
        border: 1.5px solid var(--dir-border);
        border-radius: 20px;
        font-size: 12.5px;
        width: 220px;
        transition: all 0.2s;
        font-family: inherit;
        color: #0f172a;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--dir-primary);
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.1);
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
        padding: 12px 20px;
        font-size: 11.5px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        border-bottom: 2px solid var(--dir-border);
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .premium-table td {
        padding: 14px 20px;
        border-bottom: 1px solid var(--dir-border-subtle);
        vertical-align: middle;
        font-size: 13px;
    }

    .premium-table tr:last-child td {
        border-bottom: none;
    }

    .premium-table tr:hover td {
        background: #f8fafc;
    }

    .emp-cell, .emp-profile {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .emp-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        border: 1.5px solid #ffffff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        flex-shrink: 0;
    }

    .emp-info {
        display: flex;
        flex-direction: column;
    }

    .emp-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
    }

    .emp-email {
        font-size: 12px;
        color: var(--dir-text-muted);
    }

    .badge-role {
        font-size: 12px;
        font-weight: 600;
        color: #1e40af;
        background: #eff6ff;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }

    .badge-eid {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        background: #f1f5f9;
        padding: 3px 7px;
        border-radius: 6px;
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .status-active { background: var(--dir-success-bg); color: #065f46; }
    .status-inactive { background: var(--dir-danger-bg); color: #991b1b; }
    .status-pending { background: var(--dir-warning-bg); color: #92400e; }

    .action-buttons {
        display: flex;
        gap: 6px;
        justify-content: flex-end;
    }

    .action-buttons form {
        margin: 0;
    }

    .btn-action {
        padding: 6px 11px;
        border: 1px solid var(--dir-border);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-action:hover {
        background: var(--dir-surface-hover);
    }

    .btn-action.activate {
        color: #059669;
        border-color: #a7f3d0;
        background: #ecfdf5;
    }
    .btn-action.activate:hover {
        background: #d1fae5;
    }

    .btn-action.deactivate {
        color: #d97706;
        border-color: #fde68a;
        background: #fffbeb;
    }
    .btn-action.deactivate:hover {
        background: #fef3c7;
    }

    .btn-action.delete {
        color: #dc2626;
        border-color: #fecaca;
        background: #fef2f2;
    }
    .btn-action.delete:hover {
        background: #fee2e2;
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .py-8 { padding-top: 32px; padding-bottom: 32px; }
    .text-gray-500 { color: #64748b; }

    /* ==========================================================================
       TOGGLE SWITCH (iOS Modern Style)
       ========================================================================== */
    .switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    input:checked + .slider {
        background-color: #0d4d8b;
    }
    input:checked + .slider:before {
        transform: translateX(20px);
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
        font-size: 13.5px;
        font-weight: 700;
        transition: padding-right 0.2s;
    }
    
    .floating-tab:hover {
        padding-right: 12px;
    }
    
    .tab-red {
        background: linear-gradient(180deg, #b91c1c 0%, #991b1b 100%);
        color: white;
    }
    
    .tab-badge {
        writing-mode: horizontal-tb;
        background: white;
        color: #b91c1c;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        margin-top: 8px;
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
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
        z-index: 999;
    }
    
    .drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        width: 100%;
        max-width: 440px;
        height: 100vh;
        background: #ffffff;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        box-shadow: -10px 0 30px rgba(0,0,0,0.15);
    }
    
    .drawer-wrapper.open .drawer-panel {
        transform: translateX(0);
    }

    .drawer-header {
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .bg-red-header {
        background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
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
        background: rgba(255, 255, 255, 0.15);
        color: white;
    }

    .drawer-content {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }

    .drawer-gray-bg {
        background-color: #f8fafc;
    }

    .conflict-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .conflict-card {
        background: #ffffff;
        border-left: 3px solid #b91c1c;
        border-radius: 10px;
        border-top: 1px solid var(--dir-border);
        border-right: 1px solid var(--dir-border);
        border-bottom: 1px solid var(--dir-border);
        padding: 16px;
        box-shadow: var(--dir-shadow-sm);
    }

    .conflict-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .font-bold { font-weight: 700; color: #b91c1c; font-size: 14px; }
    .text-sm { font-size: 13px; }
    .text-red { color: #b91c1c; font-weight: 600; }
    .font-semibold { font-weight: 600; color: #0f172a; font-size: 13.5px; }
    .mb-1 { margin-bottom: 4px; }
    .text-xs { font-size: 12px; }
    .text-gray-600 { color: #475569; }
    .text-green-600 { color: #059669; }

    /* Alert Banner */
    .alert-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        padding: 14px 18px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13.5px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
        .dashboard-hero {
            flex-direction: column;
            align-items: stretch;
        }
        .hero-right {
            width: 100%;
        }
    }

    /* ==========================================================================
       DARK MODE SUPPORT
       ========================================================================== */
    html.theme-dark {
        --dir-bg: #0b1120;
        --dir-surface: #111827;
        --dir-surface-hover: #1e293b;
        --dir-border: #1f293d;
        --dir-border-subtle: #192233;
        --dir-text: #f1f5f9;
        --dir-text-secondary: #94a3b8;
        --dir-text-muted: #64748b;
    }

    html.theme-dark .dashboard-hero {
        background: linear-gradient(135deg, #111827 0%, #0f172a 100%);
        border-color: var(--dir-border);
    }
    html.theme-dark .hero-title { color: #f8fafc; }
    html.theme-dark .hero-stat-card,
    html.theme-dark .modern-team-card,
    html.theme-dark .premium-card {
        background: var(--dir-surface);
        border-color: var(--dir-border);
    }
    html.theme-dark .stat-value,
    html.theme-dark .team-card-title,
    html.theme-dark .card-header h2,
    html.theme-dark .emp-name { color: #f8fafc; }
    html.theme-dark .card-header {
        background: var(--dir-surface);
        border-bottom-color: var(--dir-border);
    }
    html.theme-dark .director-tabs {
        background: #0f172a;
        border-color: var(--dir-border);
    }
    html.theme-dark .dir-tab-btn.active {
        background: #1e293b;
        color: #60a5fa;
    }
    html.theme-dark .premium-table th {
        background: #0f172a;
        border-color: var(--dir-border);
        color: #94a3b8;
    }
    html.theme-dark .premium-table td {
        border-bottom-color: var(--dir-border-subtle);
        color: #cbd5e1;
    }
    html.theme-dark .premium-table tr:hover td {
        background: #1e293b;
    }
    html.theme-dark .premium-form input,
    html.theme-dark .premium-form select,
    html.theme-dark .search-input {
        background: #0f172a;
        border-color: var(--dir-border);
        color: #f8fafc;
    }
    html.theme-dark .btn-action {
        background: #1e293b;
        border-color: var(--dir-border);
        color: #f1f5f9;
    }
    html.theme-dark .drawer-panel {
        background: #111827;
    }
    html.theme-dark .drawer-gray-bg {
        background-color: #0b1120;
    }
    html.theme-dark .conflict-card {
        background: #111827;
        border-color: var(--dir-border);
    }
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

<script>
// Permissions Logic
let currentPermUser = null;

// Load users on tab click
document.querySelector('[data-target="tab-permissions"]').addEventListener('click', function() {
    fetchUsers();
});

function fetchUsers() {
    fetch('permissions_api.php?action=get_users')
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                const list = document.getElementById('permUserList');
                list.innerHTML = '';
                data.users.forEach(u => {
                    const li = document.createElement('li');
                    li.style.padding = '12px 16px';
                    li.style.borderBottom = '1px solid var(--border)';
                    li.style.cursor = 'pointer';
                    li.style.display = 'flex';
                    li.style.alignItems = 'center';
                    li.style.gap = '12px';
                    li.className = 'perm-user-item';
                    li.dataset.uid = u.id;
                    li.dataset.name = u.username;
                    li.dataset.role = u.role;
                    
                    const char = u.username.charAt(0).toUpperCase();
                    li.innerHTML = `<div class="emp-avatar" style="width: 32px; height: 32px; font-size: 14px;">${char}</div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 500; color: var(--text-primary); font-size: 14px;">${u.username}</div>
                                        <div style="font-size: 12px; color: var(--text-secondary);">${u.role}</div>
                                    </div>`;
                    
                    li.addEventListener('click', function() {
                        document.querySelectorAll('.perm-user-item').forEach(el => el.style.background = 'transparent');
                        li.style.background = '#F3F4F6';
                        loadPermissions(u);
                    });
                    
                    list.appendChild(li);
                });
            }
        });
}

function loadPermissions(user) {
    currentPermUser = user.id;
    document.getElementById('permPlaceholder').style.display = 'none';
    document.getElementById('permSettings').style.display = 'block';
    
    document.getElementById('permAvatar').textContent = user.username.charAt(0).toUpperCase();
    document.getElementById('permName').textContent = user.username;
    document.getElementById('permRole').textContent = user.role;
    
    // reset toggles
    document.getElementById('toggleClientAccess').checked = false;
    document.getElementById('toggleInvoiceAccess').checked = false;
    
    fetch('permissions_api.php?action=get_permissions&user_id=' + user.id)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('toggleClientAccess').checked = data.permissions.client_access;
                document.getElementById('toggleInvoiceAccess').checked = data.permissions.invoice_access;
            }
        });
}

// Attach toggle events
['toggleClientAccess', 'toggleInvoiceAccess'].forEach(id => {
    document.getElementById(id).addEventListener('change', function(e) {
        if (!currentPermUser) return;
        
        const feature = id === 'toggleClientAccess' ? 'client_access' : 'invoice_access';
        const isAllowed = e.target.checked ? 1 : 0;
        
        const fd = new FormData();
        fd.append('action', 'update_permission');
        fd.append('user_id', currentPermUser);
        fd.append('feature', feature);
        fd.append('is_allowed', isAllowed);
        
        fetch('permissions_api.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.status !== 'success') {
                    alert('Error updating permission');
                    e.target.checked = !e.target.checked;
                }
            });
    });
});

// Search filter
document.getElementById('permUserSearch').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll('.perm-user-item').forEach(li => {
        const name = li.dataset.name.toLowerCase();
        li.style.display = name.includes(term) ? 'flex' : 'none';
    });
});
});
</script>

<!-- Message Modal -->
<div id="messageModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:#fff; width:500px; max-width:90%; border-radius:8px; display:flex; flex-direction:column; overflow:hidden;">
        <div style="padding:16px 20px; background:#193048; color:white; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:16px;" id="messageModalTitle">Message User</h3>
            <button type="button" onclick="closeMessageModal()" style="background:transparent; border:none; color:white; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div style="padding:20px; flex:1; overflow-y:auto; max-height:400px; background:#F9FAFB;" id="messageHistory">
            <!-- Messages will be loaded here -->
            <div style="text-align:center; color:#9CA3AF;">Loading...</div>
        </div>
        <div style="padding:16px 20px; background:white; border-top:1px solid #E5E7EB;">
            <form id="messageForm" onsubmit="event.preventDefault(); sendMessage();" enctype="multipart/form-data">
                <input type="hidden" id="msgReceiverId" name="receiver_id">
                <textarea id="msgText" name="message" rows="2" style="width:100%; padding:10px; border:1px solid #D1D5DB; border-radius:6px; resize:none; margin-bottom:10px;" placeholder="Type your message/update..."></textarea>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <input type="file" id="msgFile" name="attachment" accept=".pdf,.doc,.docx,.jpg,.png" style="font-size:12px;">
                    <button type="submit" class="btn-primary" style="padding:8px 16px; border-radius:6px;">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentMessageUserId = null;

function openMessageModal(userId, userName) {
    currentMessageUserId = userId;
    document.getElementById('msgReceiverId').value = userId;
    document.getElementById('messageModalTitle').innerText = 'Conversation with ' + userName;
    document.getElementById('messageModal').style.display = 'flex';
    document.getElementById('msgText').value = '';
    document.getElementById('msgFile').value = '';
    loadMessages();
}

function closeMessageModal() {
    document.getElementById('messageModal').style.display = 'none';
    currentMessageUserId = null;
}

function loadMessages() {
    if (!currentMessageUserId) return;
    const formData = new FormData();
    formData.append('action', 'fetch_conversation');
    formData.append('other_user_id', currentMessageUserId);

    fetch('ajax_messages.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('messageHistory');
            if (data.success) {
                container.innerHTML = '';
                if (data.messages.length === 0) {
                    container.innerHTML = '<div style="text-align:center; color:#9CA3AF; margin-top:20px;">No messages yet. Start the conversation!</div>';
                } else {
                    data.messages.forEach(msg => {
                        const isMe = parseInt(msg.sender_id) === parseInt(data.my_id);
                        const align = isMe ? 'flex-end' : 'flex-start';
                        const bg = isMe ? '#E0F2FE' : '#FFFFFF';
                        const border = isMe ? '#BAE6FD' : '#E5E7EB';
                        
                        let attachmentHtml = '';
                        if (msg.attachment) {
                            attachmentHtml = `<div style="margin-top:8px;"><a href="${msg.attachment}" target="_blank" style="font-size:12px; color:#2563EB; text-decoration:underline;"><i class="fa fa-paperclip"></i> View Attachment</a></div>`;
                        }
                        
                        const timeStr = new Date(msg.created_at).toLocaleString();
                        
                        const msgDiv = document.createElement('div');
                        msgDiv.style.display = 'flex';
                        msgDiv.style.flexDirection = 'column';
                        msgDiv.style.alignItems = align;
                        msgDiv.style.marginBottom = '12px';
                        
                        msgDiv.innerHTML = `
                            <div style="background:${bg}; border:1px solid ${border}; padding:10px 14px; border-radius:8px; max-width:80%; font-size:14px; color:#1F2937;">
                                <div style="margin-bottom:4px; word-wrap:break-word; white-space:pre-wrap;">${msg.message.replace(/</g, '&lt;')}</div>
                                ${attachmentHtml}
                                <div style="font-size:10px; color:#6B7280; text-align:right; margin-top:4px;">${timeStr}</div>
                            </div>
                        `;
                        container.appendChild(msgDiv);
                    });
                    container.scrollTop = container.scrollHeight;
                }
            } else {
                container.innerHTML = '<div style="text-align:center; color:red;">Failed to load messages.</div>';
            }
        });
}

function sendMessage() {
    const form = document.getElementById('messageForm');
    const formData = new FormData(form);
    formData.append('action', 'send_message');
    
    if (!formData.get('message').trim() && !document.getElementById('msgFile').files.length) {
        alert("Please enter a message or attach a file.");
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerText = 'Sending...';

    fetch('ajax_messages.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Send Message';
            if (data.success) {
                document.getElementById('msgText').value = '';
                document.getElementById('msgFile').value = '';
                loadMessages();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Send Message';
            alert("Error sending message.");
        });
}
</script>
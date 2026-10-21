<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "My Dashboard";
    include_once './header.php';
    include_once './dbConnect.php';
    require_once __DIR__ . '/include/staff_guidelines_lib.php';
    date_default_timezone_set('Asia/Kolkata');

    // Auto-ensure user profile schema is up-to-date
    if (isset($db) && $db) {
        require_once __DIR__ . '/include/schema_auto_sync.php';
        if (function_exists('aitf_ensure_user_profile_columns')) {
            aitf_ensure_user_profile_columns($db);
        }
    }

    // 1. Identify actual logged-in user credentials
    $actualLoggedInUserId = (int)($_SESSION['id'] ?? 0);
    $actualLoggedInRole = (string)($_SESSION['role'] ?? '');
    $actualLoggedInUsername = (string)($_SESSION['username'] ?? '');

    // 2. Check if logged-in user has Management / Admin / Finance privileges (for View Other Dashboard & Task Assignment)
    require_once __DIR__ . '/include/task_lib.php';
    $canManageTasks = aitf_can_manage_tasks($actualLoggedInRole);
    $isManagementUser = $canManageTasks
        || (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($actualLoggedInRole))
        || (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin()))
        || in_array(strtolower(trim($actualLoggedInRole)), ['director', 'top mgmt', 'mgmt', 'main admin', 'admin', 'finance'], true);

    // Strictly check if actual logged-in user has 'Top Mgmt' role
    $isTopMgmt = (strcasecmp(trim($actualLoggedInRole), 'Top Mgmt') === 0)
        || (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($actualLoggedInRole));

    // 3. Fetch all system users for Management Switcher & Task Assignment (Strictly only users created via Add User / 'user' table)
    $allSystemUsers = [];
    $switcherUsers = [];
    if (($isManagementUser || $isTopMgmt) && function_exists('aitf_get_all_system_users') && isset($db) && $db) {
        $allSystemUsers = aitf_get_all_system_users($db, false);

        // Build clean deduplicated user list showing ONLY unique names, excluding self
        $seenSwitcherNames = [];
        foreach ($allSystemUsers as $su) {
            if ($su['user_type'] === 'user' && (int)$su['user_id'] === $actualLoggedInUserId) {
                continue;
            }
            $cleanName = trim($su['name']);
            $nameNorm = strtolower($cleanName);
            if (isset($seenSwitcherNames[$nameNorm])) {
                continue;
            }
            $seenSwitcherNames[$nameNorm] = true;
            $su['clean_name'] = $cleanName;
            $switcherUsers[] = $su;
        }
        usort($switcherUsers, function($a, $b) {
            return strcasecmp($a['clean_name'], $b['clean_name']);
        });
    }

    // 4. Fetch default logged-in user details with robust error handling
    $userRow = null;
    $isAuditorUser = ($actualLoggedInRole === 'Auditor') || !empty($_SESSION['auditor_id']);

    if (!empty($_SESSION['id'])) {
        try {
            $uStmt = $db->prepare("SELECT * FROM user WHERE user_id = :id LIMIT 1");
            $uStmt->execute([':id' => $_SESSION['id']]);
            $userRow = $uStmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $userRow = null;
        }
    }
    if (!$userRow && !empty($_SESSION['username'])) {
        try {
            $uStmt = $db->prepare("SELECT * FROM user WHERE LOWER(user_name) = :uname OR LOWER(email_id) = :uemail LIMIT 1");
            $uStmt->execute([':uname' => strtolower($_SESSION['username']), ':uemail' => strtolower($_SESSION['username'])]);
            $userRow = $uStmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $userRow = null;
        }
    }

    // If still not found and this is an Auditor session, resolve from auditors table
    if (!$userRow && $isAuditorUser) {
        $audId = !empty($_SESSION['auditor_id']) ? (int)$_SESSION['auditor_id'] : (int)$_SESSION['id'];
        try {
            $audStmt = $db->prepare("SELECT auditors_id, auditor_name, abbrevation, auditor_number, auditor_email, login_email FROM auditors WHERE auditors_id = :aid LIMIT 1");
            $audStmt->execute([':aid' => $audId]);
            if ($audData = $audStmt->fetch(PDO::FETCH_ASSOC)) {
                $audLoginEmail = !empty($audData['login_email']) ? $audData['login_email'] : ($audData['auditor_email'] ?? '');
                $audNo = !empty($audData['auditor_number']) ? $audData['auditor_number'] : '';
                try {
                    $uStmt = $db->prepare("SELECT * FROM user WHERE (LOWER(email_id) = LOWER(:email) AND email_id != '') OR (e_id = :eid AND e_id != '') OR LOWER(user_name) = LOWER(:uname) LIMIT 1");
                    $uStmt->execute([':email' => $audLoginEmail, ':eid' => $audNo, ':uname' => $audData['auditor_name']]);
                    $userRow = $uStmt->fetch(PDO::FETCH_ASSOC);
                } catch (Throwable $e) {
                    $userRow = null;
                }
                if (!$userRow) {
                    $userRow = [
                        'user_id' => 0,
                        'user_name' => $audData['auditor_name'],
                        'abbreviation' => $audData['abbrevation'] ?? '',
                        'email_id' => $audLoginEmail,
                        'role' => 'Auditor',
                        'e_id' => $audNo,
                        'pc_id' => '',
                        'contact_no' => $audNo,
                        'account_status' => 'active',
                        'designation_assigned' => 'Senior Auditor',
                        'applicable_records' => '',
                        'work_description' => '',
                        'resource_issue_record' => ''
                    ];
                }
            }
        } catch (Throwable $e) {}
    }

    // 5. Management Switcher: Check if viewing another user's dashboard
    $isViewingOther = false;
    $currentViewKey = '';
    $viewingTargetName = '';

    if ($isManagementUser && !empty($_GET['view_user'])) {
        $reqUserKey = trim((string)$_GET['view_user']);
        if ($reqUserKey !== '' && $reqUserKey !== 'self') {
            $targetUserRow = null;
            $isAuditorTarget = false;

            if (strpos($reqUserKey, 'auditor_') === 0) {
                $targetAudId = (int)substr($reqUserKey, 8);
                if ($targetAudId > 0) {
                    try {
                        $audStmt = $db->prepare("SELECT auditors_id, auditor_name, abbrevation, auditor_number, auditor_email, login_email FROM auditors WHERE auditors_id = :aid LIMIT 1");
                        $audStmt->execute([':aid' => $targetAudId]);
                        if ($audData = $audStmt->fetch(PDO::FETCH_ASSOC)) {
                            $isAuditorTarget = true;
                            $audLoginEmail = !empty($audData['login_email']) ? $audData['login_email'] : ($audData['auditor_email'] ?? '');
                            $audNo = !empty($audData['auditor_number']) ? $audData['auditor_number'] : '';
                            try {
                                $uStmt = $db->prepare("SELECT * FROM user WHERE (LOWER(email_id) = LOWER(:email) AND email_id != '') OR (e_id = :eid AND e_id != '') OR LOWER(user_name) = LOWER(:uname) LIMIT 1");
                                $uStmt->execute([':email' => $audLoginEmail, ':eid' => $audNo, ':uname' => $audData['auditor_name']]);
                                $targetUserRow = $uStmt->fetch(PDO::FETCH_ASSOC);
                            } catch (Throwable $e) {}

                            if (!$targetUserRow) {
                                $targetUserRow = [
                                    'user_id' => 0,
                                    'user_name' => $audData['auditor_name'],
                                    'abbreviation' => $audData['abbrevation'] ?? '',
                                    'email_id' => $audLoginEmail,
                                    'role' => 'Auditor',
                                    'e_id' => $audNo,
                                    'pc_id' => '',
                                    'contact_no' => $audNo,
                                    'account_status' => 'active',
                                    'designation_assigned' => 'Senior Auditor',
                                    'applicable_records' => '',
                                    'work_description' => '',
                                    'resource_issue_record' => ''
                                ];
                            }
                        }
                    } catch (Throwable $e) {}
                }
            } else {
                $targetUid = (int)preg_replace('/[^0-9]/', '', $reqUserKey);
                if ($targetUid > 0) {
                    try {
                        $uStmt = $db->prepare("SELECT * FROM user WHERE user_id = :uid LIMIT 1");
                        $uStmt->execute([':uid' => $targetUid]);
                        $targetUserRow = $uStmt->fetch(PDO::FETCH_ASSOC);
                    } catch (Throwable $e) {}
                }
            }

            if ($targetUserRow) {
                // If it's not the same user as actual logged-in user, switch the active dashboard
                $isSameUser = false;
                if (!empty($targetUserRow['user_id']) && $actualLoggedInUserId > 0 && (int)$targetUserRow['user_id'] === $actualLoggedInUserId) {
                    $isSameUser = true;
                }
                if (!$isSameUser) {
                    $userRow = $targetUserRow;
                    $isViewingOther = true;
                    $currentViewKey = $reqUserKey;
                    $viewingTargetName = $targetUserRow['user_name'];
                    $isAuditorUser = ($targetUserRow['role'] === 'Auditor') || ($targetUserRow['role'] === 'Auditors') || $isAuditorTarget;
                }
            }
        }
    }

    $userName = trim($userRow['user_name'] ?? ($_SESSION['username'] ?? 'User'));
    if (empty($userName)) {
        $userName = 'User';
    }

    $designationAssigned = trim((string)($userRow['designation_assigned'] ?? ''));
    $applicableRecords = trim((string)($userRow['applicable_records'] ?? ''));
    $workDescription = trim((string)($userRow['work_description'] ?? ''));
    $resourceIssueRecord = trim((string)($userRow['resource_issue_record'] ?? ''));
    $userEmail = trim((string)($userRow['email_id'] ?? ($_SESSION['email'] ?? '')));
    $userRole = trim((string)($userRow['role'] ?? ($_SESSION['role'] ?? 'User')));
    $userEid = trim((string)($userRow['e_id'] ?? ''));
    $userPcId = trim((string)($userRow['pc_id'] ?? ''));
    $userContact = trim((string)($userRow['contact_no'] ?? ''));
    $userStatus = trim((string)($userRow['account_status'] ?? 'active'));
    $userAbbr = trim((string)($userRow['abbreviation'] ?? ''));
    $isAuditorUser = $isAuditorUser || ($userRole === 'Auditor');

    // Dynamic Initials generation AB )
    $initials = '';
    $cleanName = preg_replace('/[^a-zA-Z0-9\s]/', '', $userName);
    $nameParts = array_values(array_filter(preg_split('/\s+/', trim($cleanName))));
    if (count($nameParts) >= 2) {
        $initials = strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr(end($nameParts), 0, 1));
    } elseif (count($nameParts) === 1) {
        $firstWord = $nameParts[0];
        if (mb_strlen($firstWord) >= 2) {
            $initials = strtoupper(mb_substr($firstWord, 0, 2));
        } else {
            $initials = strtoupper(mb_substr($firstWord, 0, 1));
        }
    }
    if (empty($initials) && !empty($userAbbr)) {
        $initials = strtoupper(substr($userAbbr, 0, 2));
    }
    if (empty($initials)) {
        $initials = 'US';
    }

    // Helper function to parse sections with Headers and Descriptions (supports JSON and legacy text)
    if (!function_exists('parseSectionGroups')) {
        function parseSectionGroups($rawContent) {
            $groups = [];
            $rawContent = trim((string)$rawContent);
            if ($rawContent === '') {
                return $groups;
            }

            // Try parsing as JSON first
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
                                        if ($clean !== '') {
                                            $items[] = $clean;
                                        }
                                    }
                                }
                            }
                            if ($header !== '' || !empty($items)) {
                                $groups[] = [
                                    'header' => $header,
                                    'items'  => $items
                                ];
                            }
                        }
                    }
                    if (!empty($groups)) {
                        return $groups;
                    }
                }
            }

            // Fallback: Legacy plain text parsing with smart header detection
            $lines = preg_split('/\r\n|\r|\n/', $rawContent);
            $currentHeader = '';
            $currentItems = [];

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '') continue;

                if (preg_match('/^([A-Za-z0-9\s\&\(\)\/]+)[\-\:]$/', $trimmed, $m) && strlen(trim($m[1])) > 3) {
                    if (!empty($currentItems) || $currentHeader !== '') {
                        $groups[] = [
                            'header' => $currentHeader,
                            'items'  => $currentItems
                        ];
                        $currentItems = [];
                    }
                    $currentHeader = trim($m[1]);
                } else {
                    $clean = preg_replace('/^\s*\d+[\.\)\-]\s*/', '', $trimmed);
                    if ($clean !== '') {
                        $currentItems[] = $clean;
                    }
                }
            }

            if (!empty($currentItems) || $currentHeader !== '') {
                $groups[] = [
                    'header' => $currentHeader,
                    'items'  => $currentItems
                ];
            }

            return $groups;
        }
    }

    $recordGroups   = parseSectionGroups($applicableRecords);
    $workDescGroups = parseSectionGroups($workDescription);
    $resIssueGroups = parseSectionGroups($resourceIssueRecord);

    //  Dynamic greeting based on current time

    $hour = (int) date('H');
    if ($hour >= 5 && $hour < 12) {
        $greetingTime = "Good Morning";
    } elseif ($hour >= 12 && $hour < 17) {
        $greetingTime = "Good Afternoon";
    } else {
        $greetingTime = "Good Evening";
    }
    ?>

    <style>
    
               /* THEME INTEGRATION FORM THE MASTER CREATOR PAGE */

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body, #wrapper {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        #page-content-wrapper {
            margin-top: 72px !important;
            padding-top: 0 !important;
        }

        .page-content,
        .page-content.inset {
            margin-top: 0 !important;
            padding-top: 10px !important;
            padding-left: 20px !important;
            padding-right: 20px !important;
            padding-bottom: 30px !important;
        }

        .my-dash-container {
            padding: 0 !important;
            margin: 0 auto !important;
            max-width: 1540px !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }

        /* 1. MasterCreator Compact Hero Header */
        .mc-hero-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #eff6ff 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 20px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
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
            font-size: 19px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
        }

        .mc-hero-title span {
            color: #0d4d8b;
            margin-left: 6px;
        }

        .mc-hero-sub {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
            font-weight: 500;
        }

        .mc-hero-sub strong {
            color: #1e293b;
            font-weight: 600;
        }

        .mc-stat-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 7px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .mc-stat-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.04);
        }

        .mc-stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .mc-stat-icon.icon-blue { background: #eff6ff; color: #2563eb; }
        .mc-stat-icon.icon-green { background: #ecfdf5; color: #059669; }
        .mc-stat-icon.icon-purple { background: #f5f3ff; color: #7c3aed; }

        .mc-stat-num {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .mc-stat-lbl {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            line-height: 1;
            margin-top: 3px;
        }

        /* 2. Modern Segmented Tab Bar */
        .dash-tabs-bar {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            gap: 6px !important;
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 5px 6px !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03) !important;
            margin-bottom: 20px !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            white-space: nowrap !important;
            width: 100% !important;
        }

        .dash-tabs-bar:has(.dash-tab-btn.active[data-target="#pane-task-mgmt"]),
        .dash-tabs-bar.tabs-attached-bottom {
            margin-bottom: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        #pane-task-mgmt .todo-app-wrapper {
            margin-top: -1px !important;
            border-top-left-radius: 0 !important;
            border-top-right-radius: 0 !important;
            border-radius: 0 0 12px 12px !important;
        }

        .dash-tabs-scroller {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        .dash-tabs-bar::-webkit-scrollbar {
            height: 3px;
        }
        .dash-tabs-bar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .dash-tab-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 9px 18px !important;
            border-radius: 7px !important;
            border: none !important;
            background: transparent !important;
            color: #64748b !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
            outline: none !important;
            text-decoration: none !important;
        }

        .dash-tab-btn:hover {
            background: #f1f5f9 !important;
            color: #0d4d8b !important;
        }

        .dash-tab-btn.active {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 3px 8px rgba(13, 77, 139, 0.25) !important;
            font-weight: 700 !important;
        }

        .dash-tab-btn i {
            font-size: 13.5px !important;
        }

        /* 3. MasterCreator Section Card */
        .qms_complete_btn {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            border: none !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 12px 20px !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(13, 77, 139, 0.15) !important;
            margin: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .master-profile-well {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-top: none !important;
            border-radius: 0 0 12px 12px !important;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04) !important;
            padding: 0 !important;
            margin-bottom: 24px !important;
            overflow: hidden;
        }

        /* Profile Header Top Aura */
        .profile-header-area {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #eff6ff 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 24px 26px;
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
        }

        .profile-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .profile-avatar-circle {
            width: 86px;
            height: 86px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 50%, #2563eb 100%);
            color: #ffffff;
            font-size: 29px;
            font-weight: 800;
            letter-spacing: 1.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(13, 77, 139, 0.28);
            border: 3.5px solid #ffffff;
            text-transform: uppercase;
            user-select: none;
        }

        .profile-avatar-status {
            position: absolute;
            bottom: 3px;
            right: 3px;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            background: #10b981;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
        }

        .profile-identity-info {
            flex: 1;
            min-width: 220px;
        }

        .profile-user-name {
            font-size: 23px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px 0;
            letter-spacing: -0.02em;
            line-height: 1.25;
        }

        .profile-hero-sub {
            font-size: 13.5px;
            color: #64748b;
            margin: 0 0 10px 0;
            font-weight: 500;
            line-height: 1.45;
        }

        .profile-hero-sub strong {
            color: #0d4d8b;
            font-weight: 700;
        }

        .profile-badges-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

        .profile-sq-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: -0.01em;
            transition: all 0.15s ease;
        }

        .profile-sq-badge.badge-designation {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            box-shadow: 0 1px 3px rgba(30, 64, 175, 0.06);
        }
        .profile-sq-badge.badge-designation i {
            color: #2563eb;
            font-size: 13px;
        }
        .profile-sq-badge.badge-designation.is-empty {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #64748b;
            box-shadow: none;
            font-weight: 600;
        }

        .profile-sq-badge.badge-role {
            background: #faf5ff;
            border: 1px solid #e9d5ff;
            color: #6b21a8;
            box-shadow: 0 1px 3px rgba(107, 33, 168, 0.05);
        }
        .profile-sq-badge.badge-role i {
            color: #9333ea;
            font-size: 12.5px;
        }

        .profile-sq-badge.badge-abbr {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            box-shadow: 0 1px 3px rgba(146, 64, 14, 0.05);
        }
        .profile-sq-badge.badge-abbr i {
            color: #d97706;
            font-size: 12.5px;
        }

        .profile-sq-badge.badge-eid {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            box-shadow: 0 1px 3px rgba(30, 64, 175, 0.05);
        }
        .profile-sq-badge.badge-eid i {
            color: #2563eb;
            font-size: 12.5px;
        }

        .profile-sq-badge.badge-pc {
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            color: #6d28d9;
            box-shadow: 0 1px 3px rgba(109, 40, 217, 0.05);
        }
        .profile-sq-badge.badge-pc i {
            color: #7c3aed;
            font-size: 12.5px;
        }

        .profile-sq-badge.badge-status {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            box-shadow: 0 1px 3px rgba(6, 95, 70, 0.05);
        }
        .profile-sq-badge.badge-status i {
            color: #10b981;
            font-size: 9px;
        }

        .profile-sq-badge:hover {
            transform: translateY(-1px);
        }

        /* Basic Details Strip */
        .profile-details-strip {
            padding: 20px 24px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
        }

        .mc-detail-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .mc-detail-card:hover {
            transform: translateY(-1px);
            border-color: #cbd5e1;
            box-shadow: 0 3px 8px rgba(0,0,0,0.04);
        }

        .detail-info {
            min-width: 0;
            flex: 1;
        }

        .detail-lbl {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748b;
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .detail-val {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
            word-break: break-word;
        }

        /* ------------------------------------------------------------------ */
        /* DISTINCT HEADER & SECTION COLOR THEMES                            */
        /* ------------------------------------------------------------------ */
        .qms_complete_btn.header-theme-profile {
            background: linear-gradient(135deg, #091e3a 0%, #0d4d8b 55%, #1d4ed8 100%) !important;
            border-bottom: 2px solid #3b82f6 !important;
        }
        .qms_complete_btn.header-theme-attendance {
            background: linear-gradient(135deg, #3b0764 0%, #6d28d9 55%, #8b5cf6 100%) !important;
            border-bottom: 2px solid #a855f7 !important;
        }
        .qms_complete_btn.header-theme-tasks {
            background: linear-gradient(135deg, #0c4a6e 0%, #0284c7 55%, #38bdf8 100%) !important;
            border-bottom: 2px solid #38bdf8 !important;
        }
        .qms_complete_btn.header-theme-guidelines {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            border-bottom: 2px solid #38bdf8 !important;
        }

        /* Clean Modern Work Guidelines Styling */
        .guideline-entry-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            transition: all 0.2s ease;
            margin-bottom: 12px;
            padding: 16px 20px;
        }
        .guideline-entry-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
            transform: translateY(-1px);
        }
        .guideline-card-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .guideline-clean-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 14px;
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #d1fae5;
            white-space: nowrap;
        }
        .guideline-file-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #1e293b;
            text-decoration: none !important;
            transition: all 0.15s ease;
        }
        .guideline-file-chip:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0d4d8b;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        .file-chip-icon {
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .file-chip-details {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        .file-chip-name {
            font-size: 12.5px;
            font-weight: 600;
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .file-chip-size {
            font-size: 11px;
            color: #64748b;
        }
        .file-chip-arrow {
            font-size: 10px;
            color: #94a3b8;
            margin-left: 2px;
        }
        .guideline-desc-box {
            margin-top: 12px;
            font-size: 13px;
            color: #334155;
            line-height: 1.55;
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }
        .guideline-attachments-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            padding-top: 10px;
            border-top: 1px solid #f1f5f9;
        }

        .header-role-pill {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            text-transform: none;
            letter-spacing: 0.3px;
            padding: 3px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Profile Sections (Applicable Records, Work Description & Resource Issue Record) */
        .profile-records-container {
            border-radius: 12px;
            padding: 16px 18px;
            transition: all 0.2s ease;
            display: block;
            width: 100%;
            height: auto;
            min-height: 100%;
            overflow: visible;
            box-sizing: border-box;
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
            letter-spacing: -0.01em;
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
            background: #ffffff;
            border-radius: 8px;
            padding: 10px 14px;
            transition: all 0.15s ease;
            flex-shrink: 0;
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
            color: #334155;
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
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            color: #94a3b8;
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
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.05);
        }
        .records-theme-blue .records-section-title {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 55%, #3b82f6 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .records-theme-blue .record-category-card {
            background: #ffffff;
            border: 1px solid #bfdbfe;
            box-shadow: 0 1px 4px rgba(37, 99, 235, 0.04);
        }
        .records-theme-blue .record-category-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 3px 10px rgba(37, 99, 235, 0.09);
        }
        .records-theme-blue .record-category-header {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-bottom: 1px solid #bfdbfe;
            color: #1e3a8a;
        }
        .records-theme-blue .record-category-header i {
            color: #2563eb;
        }
        .records-theme-blue .record-clean-item {
            background: #ffffff;
            border: 1px solid #e0e7ff;
        }
        .records-theme-blue .record-clean-item:hover {
            background: #f0f7ff;
            border-color: #93c5fd;
        }
        .records-theme-blue .record-clean-num {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .records-theme-blue .record-clean-icon {
            color: #3b82f6;
        }
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
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.05);
        }
        .records-theme-emerald .records-section-title {
            background: linear-gradient(135deg, #065f46 0%, #059669 55%, #10b981 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .records-theme-emerald .record-category-card {
            background: #ffffff;
            border: 1px solid #a7f3d0;
            box-shadow: 0 1px 4px rgba(16, 185, 129, 0.04);
        }
        .records-theme-emerald .record-category-card:hover {
            border-color: #6ee7b7;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.09);
        }
        .records-theme-emerald .record-category-header {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-bottom: 1px solid #a7f3d0;
            color: #064e3b;
        }
        .records-theme-emerald .record-category-header i {
            color: #059669;
        }
        .records-theme-emerald .record-clean-item {
            background: #ffffff;
            border: 1px solid #d1fae5;
        }
        .records-theme-emerald .record-clean-item:hover {
            background: #f0fdf4;
            border-color: #6ee7b7;
        }
        .records-theme-emerald .record-clean-num {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .records-theme-emerald .record-clean-icon {
            color: #10b981;
        }
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
            box-shadow: 0 2px 10px rgba(217, 119, 6, 0.05);
        }
        .records-theme-amber .records-section-title {
            background: linear-gradient(135deg, #92400e 0%, #d97706 55%, #f59e0b 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .records-theme-amber .record-category-card {
            background: #ffffff;
            border: 1px solid #fde68a;
            box-shadow: 0 1px 4px rgba(217, 119, 6, 0.04);
        }
        .records-theme-amber .record-category-card:hover {
            border-color: #fcd34d;
            box-shadow: 0 3px 10px rgba(217, 119, 6, 0.09);
        }
        .records-theme-amber .record-category-header {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-bottom: 1px solid #fde68a;
            color: #78350f;
        }
        .records-theme-amber .record-category-header i {
            color: #d97706;
        }
        .records-theme-amber .record-clean-item {
            background: #ffffff;
            border: 1px solid #fed7aa;
        }
        .records-theme-amber .record-clean-item:hover {
            background: #fffbeb;
            border-color: #fcd34d;
        }
        .records-theme-amber .record-clean-num {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .records-theme-amber .record-clean-icon {
            color: #f59e0b;
        }
        .records-theme-amber .category-count-badge {
            background: #fef3c7;
            color: #92400e;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
        }

        .dash-tab-pane {
            display: none;
        }
        .dash-tab-pane.active {
            display: block;
        }

        .dash-placeholder-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
        }

        .dash-placeholder-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #eff6ff;
            color: #0d4d8b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 12px;
        }

        .dash-placeholder-title {
            margin: 0 0 6px 0;
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .dash-placeholder-desc {
            margin: 0;
            font-size: 13px;
            color: #64748b;
            max-width: 480px;
            margin: 0 auto;
        }



        /* 
           NEW THEME (MATCHING MASTER CREATOR)
            */
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
        html.theme-dark #wrapper,
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
        html.theme-dark .mc-hero-title span { color: var(--hpd-accent) !important; }
        html.theme-dark .mc-hero-sub { color: var(--hpd-text-dim) !important; }
        html.theme-dark .mc-hero-sub strong { color: var(--hpd-text) !important; }

        html.theme-dark .mc-stat-pill {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
        }

        html.theme-dark .mc-stat-num { color: #f8fafc !important; }
        html.theme-dark .mc-stat-lbl { color: var(--hpd-text-dim) !important; }

        html.theme-dark .dash-tabs-bar {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
        }

        html.theme-dark .dash-tab-btn {
            color: var(--hpd-text-dim) !important;
        }

        html.theme-dark .dash-tab-btn:hover {
            background: var(--hpd-surface-2) !important;
            color: #ffffff !important;
        }

        html.theme-dark .dash-tab-btn.active {
            background: linear-gradient(135deg, #16243d 0%, #0d1b33 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.4) !important;
        }

        html.theme-dark .dash-tabs-bar {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
        }
        html.theme-dark .dash-user-switcher-container .dash-user-switcher-label {
            color: #94a3b8 !important;
        }
        html.theme-dark #dashUserSwitcherSelect {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        html.theme-dark .dash-viewing-alert {
            background: linear-gradient(135deg, #0d1b32 0%, #091322 100%) !important;
            border-color: #1e3a8a !important;
            border-left-color: #3b82f6 !important;
        }
        html.theme-dark .dash-viewing-alert div {
            color: #cbd5e1 !important;
        }
        html.theme-dark .dash-viewing-alert strong {
            color: #ffffff !important;
        }
        html.theme-dark .dash-viewing-alert .btn {
            background: #111a2e !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }

        html.theme-dark .qms_complete_btn {
            background: linear-gradient(135deg, #16243d 0%, #0d1b33 100%) !important;
            border-color: var(--hpd-border-2) !important;
            color: #ffffff !important;
        }

        html.theme-dark .master-profile-well {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
        }

        html.theme-dark .profile-header-area {
            background: linear-gradient(135deg, #111722 0%, #0d131e 100%) !important;
            border-bottom-color: var(--hpd-border) !important;
        }

        html.theme-dark .profile-user-name,
        html.theme-dark .detail-val,
        html.theme-dark .records-section-title {
            color: var(--hpd-text) !important;
        }

        html.theme-dark .profile-avatar-circle {
            border-color: var(--hpd-surface) !important;
        }

        html.theme-dark .profile-avatar-status {
            border-color: var(--hpd-surface) !important;
        }

        html.theme-dark .profile-sq-badge {
            background: var(--hpd-surface-2) !important;
            border-color: var(--hpd-border-2) !important;
        }
        html.theme-dark .profile-sq-badge.badge-designation {
            color: #60a5fa !important;
        }
        html.theme-dark .profile-sq-badge.badge-designation i {
            color: #60a5fa !important;
        }
        html.theme-dark .profile-sq-badge.badge-role {
            color: #c084fc !important;
        }
        html.theme-dark .profile-sq-badge.badge-role i {
            color: #c084fc !important;
        }
        html.theme-dark .profile-sq-badge.badge-abbr {
            color: #fbbf24 !important;
        }
        html.theme-dark .profile-sq-badge.badge-abbr i {
            color: #fbbf24 !important;
        }
        html.theme-dark .profile-sq-badge.badge-eid {
            color: #93c5fd !important;
        }
        html.theme-dark .profile-sq-badge.badge-eid i {
            color: #60a5fa !important;
        }
        html.theme-dark .profile-sq-badge.badge-pc {
            color: #c4b5fd !important;
        }
        html.theme-dark .profile-sq-badge.badge-pc i {
            color: #a78bfa !important;
        }
        html.theme-dark .profile-sq-badge.badge-status {
            color: #34d399 !important;
        }
        html.theme-dark .profile-sq-badge.badge-status i {
            color: #34d399 !important;
        }
        html.theme-dark .profile-sq-badge.is-empty {
            color: var(--hpd-text-dim) !important;
        }

        html.theme-dark .profile-details-strip {
            background: var(--hpd-surface) !important;
            border-bottom-color: var(--hpd-border) !important;
        }

        html.theme-dark .mc-detail-card {
            background: var(--hpd-surface-2) !important;
            border-color: var(--hpd-border) !important;
        }

        html.theme-dark .mc-detail-card:hover {
            border-color: var(--hpd-border-2) !important;
        }

        html.theme-dark .detail-lbl {
            color: var(--hpd-text-dim) !important;
        }

        /* Dark Mode Header Themes */
        html.theme-dark .qms_complete_btn.header-theme-profile {
            background: linear-gradient(135deg, #0a101d 0%, #0d1e38 55%, #1e3a8a 100%) !important;
            border-bottom-color: #3b82f6 !important;
        }
        html.theme-dark .qms_complete_btn.header-theme-attendance {
            background: linear-gradient(135deg, #180927 0%, #2e1065 55%, #581c87 100%) !important;
            border-bottom-color: #a855f7 !important;
        }
        html.theme-dark .qms_complete_btn.header-theme-tasks {
            background: linear-gradient(135deg, #081d2c 0%, #075985 55%, #0284c7 100%) !important;
            border-bottom-color: #38bdf8 !important;
        }
        html.theme-dark .qms_complete_btn.header-theme-guidelines {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%) !important;
            border-bottom-color: #3b82f6 !important;
        }

        /* Dark Mode for Clean Guidelines */
        html.theme-dark .guideline-entry-card {
            background: #111a2e !important;
            border-color: #1e293b !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25) !important;
        }
        html.theme-dark .guideline-entry-card:hover {
            border-color: #334155 !important;
        }
        html.theme-dark .guideline-card-icon {
            background: rgba(37, 99, 235, 0.15) !important;
            color: #60a5fa !important;
        }
        html.theme-dark .guideline-entry-card h4 {
            color: #f1f5f9 !important;
        }
        html.theme-dark .guideline-file-chip {
            background: #0f172a !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html.theme-dark .guideline-file-chip:hover {
            background: #162444 !important;
            border-color: #3b82f6 !important;
            color: #93c5fd !important;
        }
        html.theme-dark .guideline-clean-badge {
            background: rgba(5, 150, 105, 0.15) !important;
            color: #34d399 !important;
            border-color: rgba(5, 150, 105, 0.3) !important;
        }
        html.theme-dark .guideline-desc-box {
            background: #0b1120 !important;
            border-color: #1e293b !important;
            color: #cbd5e1 !important;
        }
        html.theme-dark .guideline-attachments-wrap {
            border-top-color: #1e293b !important;
        }
        html.theme-dark #guidelinesSearchInput {
            background: #0b1120 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html.theme-dark #guidelinesSearchInput::placeholder {
            color: #64748b !important;
        }

        /* 1. Blue Theme Dark (Applicable Records) */
        html.theme-dark .records-theme-blue {
            background: #0d1527 !important;
            border-color: #1e3a8a !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3) !important;
        }
        html.theme-dark .records-theme-blue .records-section-title {
            background: linear-gradient(135deg, #172554 0%, #1e3a8a 55%, #1d4ed8 100%) !important;
        }
        html.theme-dark .records-theme-blue .record-category-card {
            background: #111a2e !important;
            border-color: #1e3a8a !important;
        }
        html.theme-dark .records-theme-blue .record-category-card:hover {
            border-color: #2563eb !important;
        }
        html.theme-dark .records-theme-blue .record-category-header {
            background: #162444 !important;
            border-bottom-color: #1e3a8a !important;
            color: #93c5fd !important;
        }
        html.theme-dark .records-theme-blue .record-category-header i {
            color: #60a5fa !important;
        }
        html.theme-dark .records-theme-blue .record-clean-item {
            background: #0f172a !important;
            border-color: #1e3a8a !important;
        }
        html.theme-dark .records-theme-blue .record-clean-item:hover {
            background: #162444 !important;
            border-color: #3b82f6 !important;
        }
        html.theme-dark .records-theme-blue .record-clean-num {
            background: #1e3a8a !important;
            border-color: #2563eb !important;
            color: #bfdbfe !important;
        }
        html.theme-dark .records-theme-blue .record-clean-icon {
            color: #60a5fa !important;
        }
        html.theme-dark .records-theme-blue .category-count-badge {
            background: #1e3a8a !important;
            color: #bfdbfe !important;
        }

        /* 2. Emerald Theme Dark (Work Description) */
        html.theme-dark .records-theme-emerald {
            background: #091a14 !important;
            border-color: #065f46 !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3) !important;
        }
        html.theme-dark .records-theme-emerald .records-section-title {
            background: linear-gradient(135deg, #022c22 0%, #065f46 55%, #047857 100%) !important;
        }
        html.theme-dark .records-theme-emerald .record-category-card {
            background: #0c241c !important;
            border-color: #065f46 !important;
        }
        html.theme-dark .records-theme-emerald .record-category-card:hover {
            border-color: #059669 !important;
        }
        html.theme-dark .records-theme-emerald .record-category-header {
            background: #103328 !important;
            border-bottom-color: #065f46 !important;
            color: #a7f3d0 !important;
        }
        html.theme-dark .records-theme-emerald .record-category-header i {
            color: #34d399 !important;
        }
        html.theme-dark .records-theme-emerald .record-clean-item {
            background: #091a14 !important;
            border-color: #065f46 !important;
        }
        html.theme-dark .records-theme-emerald .record-clean-item:hover {
            background: #103328 !important;
            border-color: #10b981 !important;
        }
        html.theme-dark .records-theme-emerald .record-clean-num {
            background: #065f46 !important;
            border-color: #059669 !important;
            color: #d1fae5 !important;
        }
        html.theme-dark .records-theme-emerald .record-clean-icon {
            color: #34d399 !important;
        }
        html.theme-dark .records-theme-emerald .category-count-badge {
            background: #065f46 !important;
            color: #d1fae5 !important;
        }

        /* 3. Amber Theme Dark (Resource Issue Record) */
        html.theme-dark .records-theme-amber {
            background: #1c1507 !important;
            border-color: #92400e !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3) !important;
        }
        html.theme-dark .records-theme-amber .records-section-title {
            background: linear-gradient(135deg, #451a03 0%, #78350f 55%, #b45309 100%) !important;
        }
        html.theme-dark .records-theme-amber .record-category-card {
            background: #241c0e !important;
            border-color: #92400e !important;
        }
        html.theme-dark .records-theme-amber .record-category-card:hover {
            border-color: #d97706 !important;
        }
        html.theme-dark .records-theme-amber .record-category-header {
            background: #332512 !important;
            border-bottom-color: #92400e !important;
            color: #fde68a !important;
        }
        html.theme-dark .records-theme-amber .record-category-header i {
            color: #fbbf24 !important;
        }
        html.theme-dark .records-theme-amber .record-clean-item {
            background: #1c1507 !important;
            border-color: #78350f !important;
        }
        html.theme-dark .records-theme-amber .record-clean-item:hover {
            background: #332512 !important;
            border-color: #f59e0b !important;
        }
        html.theme-dark .records-theme-amber .record-clean-num {
            background: #78350f !important;
            border-color: #92400e !important;
            color: #fef3c7 !important;
        }
        html.theme-dark .records-theme-amber .record-clean-icon {
            color: #fbbf24 !important;
        }
        html.theme-dark .records-theme-amber .category-count-badge {
            background: #78350f !important;
            color: #fef3c7 !important;
        }
        html.theme-dark .records-empty-placeholder {
            border-color: var(--hpd-border-2) !important;
            background: var(--hpd-surface-2) !important;
            color: var(--hpd-text-dim) !important;
        }

        html.theme-dark .profile-hero-sub {
            color: var(--hpd-text-dim) !important;
        }

        html.theme-dark .profile-hero-sub strong {
            color: var(--hpd-accent) !important;
        }

        html.theme-dark .dash-placeholder-box {
            background: var(--hpd-surface-2) !important;
            border-color: var(--hpd-border) !important;
        }

        html.theme-dark .dash-placeholder-icon {
            background: rgba(74, 158, 255, 0.12) !important;
            color: var(--hpd-accent) !important;
        }

        html.theme-dark .dash-placeholder-title {
            color: var(--hpd-text) !important;
        }

        html.theme-dark .dash-placeholder-desc {
            color: var(--hpd-text-dim) !important;
        }

        /* Dark Mode Theme Support for Task Manager Component */
        html.theme-dark .todo-app-wrapper {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
            color: var(--hpd-text) !important;
        }
        html.theme-dark .todo-header {
            border-bottom-color: var(--hpd-border) !important;
        }
        html.theme-dark .todo-title-group h3 {
            color: #f8fafc !important;
        }
        html.theme-dark .todo-title-group .todo-subtitle {
            color: var(--hpd-text-dim) !important;
        }
        html.theme-dark .todo-search-box input {
            background: var(--hpd-surface-2) !important;
            border-color: var(--hpd-border) !important;
            color: var(--hpd-text) !important;
        }
        html.theme-dark .todo-filter-btn {
            background: var(--hpd-surface-2) !important;
            border-color: var(--hpd-border) !important;
            color: var(--hpd-text-dim) !important;
        }
        html.theme-dark .todo-card {
            background: var(--hpd-surface-2) !important;
            border-color: var(--hpd-border) !important;
        }
        html.theme-dark .todo-card:hover {
            background: #192233 !important;
            border-color: var(--hpd-border-2) !important;
        }
        html.theme-dark .todo-card.is-expanded {
            background: #162032 !important;
            border-color: var(--hpd-border-2) !important;
        }
        html.theme-dark .todo-expand-chevron {
            color: var(--hpd-text-dim) !important;
        }
        html.theme-dark .todo-task-title {
            color: #f1f5f9 !important;
        }
        html.theme-dark .todo-meta-row {
            color: var(--hpd-text-dim) !important;
            border-top-color: var(--hpd-border) !important;
        }
        html.theme-dark .todo-meta-row strong {
            color: var(--hpd-text) !important;
        }
        html.theme-dark .todo-meta-pill.pill-assigned-date {
            background: #172554 !important;
            border-color: #1e40af !important;
            color: #93c5fd !important;
        }
        html.theme-dark .todo-meta-pill.pill-assigned-date strong {
            color: #bfdbfe !important;
        }
        html.theme-dark .todo-meta-pill.pill-assigned-by {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        html.theme-dark .todo-meta-pill.pill-assigned-by strong {
            color: #f8fafc !important;
        }
        html.theme-dark .todo-meta-pill.pill-due-date {
            background: #052e16 !important;
            border-color: #166534 !important;
            color: #86efac !important;
        }
        html.theme-dark .todo-meta-pill.pill-due-date strong {
            color: #bbf7d0 !important;
        }
        html.theme-dark .todo-meta-pill.pill-due-date.is-overdue {
            background: #450a0a !important;
            border-color: #991b1b !important;
            color: #fca5a5 !important;
        }
        html.theme-dark .todo-meta-pill.pill-due-date.is-overdue strong {
            color: #fecaca !important;
        }
        html.theme-dark .todo-meta-pill.pill-priority {
            background: #451a03 !important;
            border-color: #92400e !important;
            color: #fde68a !important;
        }
        html.theme-dark .todo-meta-pill.pill-priority strong {
            color: #fef08a !important;
        }
        html.theme-dark .todo-meta-pill.pill-completed-date {
            background: #064e3b !important;
            border-color: #047857 !important;
            color: #a7f3d0 !important;
        }
        html.theme-dark .todo-meta-pill.pill-completed-date strong {
            color: #d1fae5 !important;
        }
        html.theme-dark .todo-check-circle {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border-2) !important;
        }
        html.theme-dark .todo-completed-section {
            border-top-color: var(--hpd-border) !important;
        }
        html.theme-dark .todo-completed-header {
            color: var(--hpd-text-dim) !important;
        }
        html.theme-dark .todo-completed-count {
            background: var(--hpd-surface-2) !important;
            color: var(--hpd-text) !important;
        }

        /* Default Switcher Display (Desktop PC view remains 100% untouched) */
        .dash-switcher-desktop-wrap {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
        .dash-switcher-mobile-wrap {
            display: none !important;
        }

        /* ================================================================== */
        /* FULL-FLEDGED MOBILE RESPONSIVE DESIGN (<= 768px)                   */
        /* PC/Desktop view (> 768px) remains 100% untouched                  */
        /* ================================================================== */
        @media screen and (max-width: 768px) {
            /* 1. Global Page Layout & Insets */
            body, #wrapper {
                overflow-x: hidden !important;
            }

            #wrapper.active {
                padding-left: 0 !important;
            }

            .modern-navbar {
                height: 60px !important;
                min-height: 60px !important;
            }
            .navbar-container {
                margin-left: 0 !important;
                padding: 0 !important;
                height: 100% !important;
            }
            .navbar-content {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                height: 60px !important;
                padding: 0 10px !important;
                gap: 6px !important;
                flex-wrap: nowrap !important;
                width: 100% !important;
            }
            .navbar-toggle {
                display: none !important;
            }
            .navbar-center {
                order: 1 !important;
                flex: 1 !important;
                min-width: 0 !important;
                width: auto !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                display: flex !important;
                align-items: center !important;
            }
            .navbar-left {
                order: 2 !important;
                width: auto !important;
                height: auto !important;
                margin: 0 !important;
                display: flex !important;
                align-items: center !important;
                flex-shrink: 0 !important;
            }
            .navbar-right {
                order: 3 !important;
                width: auto !important;
                margin: 0 !important;
                display: flex !important;
                align-items: center !important;
                gap: 6px !important;
                flex-shrink: 0 !important;
            }
            .modern-add-btn {
                height: 38px !important;
                padding: 0 10px !important;
                min-width: 40px !important;
                border-radius: 10px !important;
                gap: 4px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .modern-add-btn span {
                display: none !important;
            }

            #page-content-wrapper {
                margin-top: 66px !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                width: 100% !important;
                overflow-x: hidden !important;
            }

            .page-content,
            .page-content.inset {
                padding-left: 10px !important;
                padding-right: 10px !important;
                padding-top: 8px !important;
                padding-bottom: 24px !important;
            }

            .my-dash-container {
                max-width: 100% !important;
                padding: 0 !important;
                overflow-x: hidden !important;
            }

            /* 2. Responsive Tabs Bar & User Switcher */
            .dash-tabs-bar {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
                padding: 6px !important;
                margin-bottom: 14px !important;
                border-radius: 10px !important;
                overflow: visible !important;
                white-space: normal !important;
            }

            .dash-tabs-bar:has(.dash-tab-btn.active[data-target="#pane-task-mgmt"]),
            .dash-tabs-bar.tabs-attached-bottom {
                margin-bottom: 0 !important;
                border-bottom-left-radius: 0 !important;
                border-bottom-right-radius: 0 !important;
            }

            .dash-tabs-scroller {
                display: flex !important;
                flex-wrap: nowrap !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                gap: 6px !important;
                width: 100% !important;
                padding-bottom: 3px !important;
                scrollbar-width: none !important;
            }

            .dash-tabs-scroller::-webkit-scrollbar {
                display: none !important;
                height: 0 !important;
            }

            .dash-tab-btn {
                padding: 8px 13px !important;
                font-size: 12px !important;
                border-radius: 6px !important;
                flex-shrink: 0 !important;
                gap: 6px !important;
            }

            .dash-tab-btn i {
                font-size: 12px !important;
            }

            .dash-user-switcher-container {
                width: 100% !important;
                margin-left: 0 !important;
                padding: 7px 2px 2px 2px !important;
                border-top: 1px solid #e2e8f0 !important;
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
            }

            html.theme-dark .dash-user-switcher-container {
                border-top-color: var(--hpd-border) !important;
            }

            /* Hide desktop switcher on mobile */
            .dash-switcher-desktop-wrap {
                display: none !important;
            }

            /* Show mobile custom switcher */
            .dash-switcher-mobile-wrap {
                display: block !important;
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                position: relative !important;
            }

            .dash-mobile-switcher-container {
                position: relative !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .dash-mobile-btn-row {
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                width: 100% !important;
            }

            .dash-mobile-switcher-btn {
                flex: 1 !important;
                min-width: 0 !important;
                height: 38px !important;
                padding: 0 12px !important;
                background: #ffffff !important;
                border: 1.5px solid #cbd5e1 !important;
                border-radius: 8px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 8px !important;
                cursor: pointer !important;
                box-shadow: 0 1px 3px rgba(0,0,0,0.04) !important;
                transition: all 0.15s ease !important;
                outline: none !important;
                box-sizing: border-box !important;
            }

            .dash-mobile-switcher-btn.is-active-view {
                background: #eff6ff !important;
                border-color: #2563eb !important;
            }

            .dash-mobile-btn-left {
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                min-width: 0 !important;
                flex: 1 !important;
                overflow: hidden !important;
            }

            .mobile-user-icon {
                font-size: 13px !important;
                color: #64748b !important;
                flex-shrink: 0 !important;
            }

            .dash-mobile-switcher-btn.is-active-view .mobile-user-icon {
                color: #2563eb !important;
            }

            .mobile-btn-text {
                font-size: 12.5px !important;
                font-weight: 600 !important;
                color: #0f172a !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            .dash-mobile-switcher-btn.is-active-view .mobile-btn-text {
                color: #1e40af !important;
            }

            .dash-mobile-arrow {
                font-size: 11px !important;
                color: #64748b !important;
                flex-shrink: 0 !important;
                transition: transform 0.2s ease !important;
            }

            .dash-mobile-switcher-btn.is-open .dash-mobile-arrow {
                transform: rotate(180deg) !important;
            }

            .dash-mobile-reset-btn {
                height: 38px !important;
                padding: 0 12px !important;
                font-size: 12px !important;
                font-weight: 600 !important;
                border-radius: 8px !important;
                background: #fee2e2 !important;
                border: 1px solid #fecaca !important;
                color: #b91c1c !important;
                text-decoration: none !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 5px !important;
                flex-shrink: 0 !important;
            }

            /* Custom Mobile Dropdown Menu (Strictly contained within mobile viewport) */
            .dash-mobile-dropdown-menu {
                display: none;
                position: absolute !important;
                top: calc(100% + 6px) !important;
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 12px !important;
                box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16), 0 2px 8px rgba(0,0,0,0.06) !important;
                z-index: 1050 !important;
                box-sizing: border-box !important;
                overflow: hidden !important;
            }

            .dash-mobile-dropdown-header {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                padding: 9px 12px !important;
                background: #f8fafc !important;
                border-bottom: 1px solid #e2e8f0 !important;
            }

            .mobile-dropdown-title {
                font-size: 12px !important;
                font-weight: 700 !important;
                color: #334155 !important;
                display: flex !important;
                align-items: center !important;
                gap: 6px !important;
                text-transform: uppercase !important;
                letter-spacing: 0.4px !important;
            }

            .dash-mobile-dropdown-close {
                width: 24px !important;
                height: 24px !important;
                border-radius: 50% !important;
                border: none !important;
                background: #e2e8f0 !important;
                color: #64748b !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                font-size: 11px !important;
                padding: 0 !important;
            }

            .dash-mobile-search-bar {
                position: relative !important;
                padding: 8px 10px !important;
                background: #ffffff !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }

            .dash-mobile-search-bar i {
                position: absolute !important;
                left: 20px !important;
                top: 50% !important;
                transform: translateY(-50%) !important;
                font-size: 12px !important;
                color: #94a3b8 !important;
            }

            .dash-mobile-search-input {
                width: 100% !important;
                height: 32px !important;
                border-radius: 6px !important;
                border: 1px solid #cbd5e1 !important;
                padding: 4px 10px 4px 28px !important;
                font-size: 12px !important;
                outline: none !important;
                background: #f8fafc !important;
                box-sizing: border-box !important;
            }

            .dash-mobile-search-input:focus {
                border-color: #2563eb !important;
                background: #ffffff !important;
            }

            .dash-mobile-user-list {
                max-height: 260px !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }

            .dash-mobile-user-item {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                padding: 9px 12px !important;
                border-bottom: 1px solid #f8fafc !important;
                text-decoration: none !important;
                color: inherit !important;
                transition: background 0.12s ease !important;
            }

            .dash-mobile-user-item:hover,
            .dash-mobile-user-item:active {
                background: #f1f5f9 !important;
                text-decoration: none !important;
            }

            .dash-mobile-user-item.is-selected {
                background: #eff6ff !important;
            }

            .mobile-item-profile {
                display: flex !important;
                align-items: center !important;
                gap: 9px !important;
                min-width: 0 !important;
                flex: 1 !important;
            }

            .mobile-item-avatar {
                width: 30px !important;
                height: 30px !important;
                border-radius: 50% !important;
                background: #e0e7ff !important;
                color: #3730a3 !important;
                font-size: 11px !important;
                font-weight: 700 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                flex-shrink: 0 !important;
            }

            .mobile-item-avatar.avatar-personal {
                background: #dbeafe !important;
                color: #1e40af !important;
            }

            .mobile-item-details {
                min-width: 0 !important;
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
            }

            .mobile-item-name {
                font-size: 13px !important;
                font-weight: 600 !important;
                color: #1e293b !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            .dash-mobile-user-item.is-selected .mobile-item-name {
                color: #1d4ed8 !important;
                font-weight: 700 !important;
            }

            .mobile-item-sub {
                font-size: 11px !important;
                color: #64748b !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            .mobile-item-check {
                color: #2563eb !important;
                font-size: 14px !important;
                margin-left: 6px !important;
                flex-shrink: 0 !important;
            }

            /* Dark Mode Support for Mobile Switcher */
            html.theme-dark .dash-mobile-switcher-btn {
                background: var(--hpd-surface-2) !important;
                border-color: var(--hpd-border) !important;
            }
            html.theme-dark .dash-mobile-switcher-btn.is-active-view {
                background: rgba(37, 99, 235, 0.15) !important;
                border-color: #3b82f6 !important;
            }
            html.theme-dark .mobile-btn-text {
                color: var(--hpd-text) !important;
            }
            html.theme-dark .dash-mobile-switcher-btn.is-active-view .mobile-btn-text {
                color: #60a5fa !important;
            }
            html.theme-dark .dash-mobile-dropdown-menu {
                background: var(--hpd-surface) !important;
                border-color: var(--hpd-border) !important;
            }
            html.theme-dark .dash-mobile-dropdown-header {
                background: var(--hpd-surface-2) !important;
                border-bottom-color: var(--hpd-border) !important;
            }
            html.theme-dark .mobile-dropdown-title {
                color: var(--hpd-text) !important;
            }
            html.theme-dark .dash-mobile-dropdown-close {
                background: var(--hpd-surface-3) !important;
                color: var(--hpd-muted) !important;
            }
            html.theme-dark .dash-mobile-search-bar {
                background: var(--hpd-surface) !important;
                border-bottom-color: var(--hpd-border) !important;
            }
            html.theme-dark .dash-mobile-search-input {
                background: var(--hpd-surface-2) !important;
                border-color: var(--hpd-border) !important;
                color: var(--hpd-text) !important;
            }
            html.theme-dark .dash-mobile-user-item {
                border-bottom-color: var(--hpd-border) !important;
            }
            html.theme-dark .dash-mobile-user-item:hover,
            html.theme-dark .dash-mobile-user-item:active {
                background: var(--hpd-surface-2) !important;
            }
            html.theme-dark .dash-mobile-user-item.is-selected {
                background: rgba(37, 99, 235, 0.15) !important;
            }
            html.theme-dark .mobile-item-name {
                color: var(--hpd-text) !important;
            }
            html.theme-dark .dash-mobile-user-item.is-selected .mobile-item-name {
                color: #60a5fa !important;
            }
            html.theme-dark .mobile-item-sub {
                color: var(--hpd-muted) !important;
            }

            .dash-switcher-reset-btn {
                height: 38px !important;
                padding: 0 12px !important;
                font-size: 12px !important;
                flex-shrink: 0 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            /* 3. Management Alert Banner */
            .dash-viewing-alert {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 12px !important;
                padding: 12px 14px !important;
                margin-bottom: 14px !important;
                border-radius: 9px !important;
            }

            .dash-viewing-alert > div:first-child {
                display: flex !important;
                align-items: flex-start !important;
                gap: 10px !important;
            }

            .dash-viewing-alert > div:last-child {
                width: 100% !important;
            }

            .dash-viewing-alert .btn {
                width: 100% !important;
                justify-content: center !important;
                padding: 8px 14px !important;
                font-size: 12px !important;
            }

            /* 4. Section Card Headers & Pills */
            .qms_complete_btn {
                padding: 10px 14px !important;
                border-radius: 10px 10px 0 0 !important;
                font-size: 12px !important;
                letter-spacing: 0.3px !important;
            }

            .qms_complete_btn > div {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 8px !important;
                width: 100% !important;
            }

            .header-role-pill {
                font-size: 10px !important;
                padding: 2px 7px !important;
                border-radius: 12px !important;
                white-space: nowrap !important;
                flex-shrink: 0 !important;
            }

            .master-profile-well {
                border-radius: 0 0 10px 10px !important;
                margin-bottom: 16px !important;
            }

            /* 5. Profile Header Top Aura & Badges */
            .profile-header-area {
                flex-direction: column !important;
                align-items: center !important;
                text-align: center !important;
                gap: 12px !important;
                padding: 16px 12px !important;
            }

            .profile-avatar-circle {
                width: 70px !important;
                height: 70px !important;
                font-size: 24px !important;
                border-width: 3px !important;
            }

            .profile-avatar-status {
                width: 15px !important;
                height: 15px !important;
                bottom: 2px !important;
                right: 2px !important;
                border-width: 2.5px !important;
            }

            .profile-identity-info {
                width: 100% !important;
                min-width: 0 !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                text-align: center !important;
            }

            .profile-user-name {
                font-size: 18px !important;
                margin-bottom: 3px !important;
                line-height: 1.3 !important;
            }

            .profile-hero-sub {
                font-size: 12px !important;
                margin-bottom: 8px !important;
                line-height: 1.4 !important;
            }

            .profile-badges-row {
                justify-content: center !important;
                gap: 6px !important;
                width: 100% !important;
                margin-top: 4px !important;
            }

            .profile-sq-badge {
                padding: 4px 10px !important;
                font-size: 11px !important;
                border-radius: 6px !important;
                gap: 5px !important;
            }

            .profile-sq-badge i {
                font-size: 11px !important;
            }

            /* 6. Basic Details Strip */
            .profile-details-strip {
                padding: 10px !important;
                grid-template-columns: 1fr !important;
                gap: 8px !important;
            }

            .mc-detail-card {
                padding: 9px 12px !important;
                gap: 10px !important;
                border-radius: 8px !important;
            }

            .mc-stat-icon {
                width: 28px !important;
                height: 28px !important;
                font-size: 12px !important;
            }

            .detail-lbl {
                font-size: 9.5px !important;
            }

            .detail-val {
                font-size: 12.5px !important;
                word-break: break-all !important;
                overflow-wrap: anywhere !important;
            }

            /* 7. Side-by-Side Records Containers on Mobile */
            .profile-records-row {
                margin: 10px 0 0 0 !important;
                display: flex !important;
                flex-direction: column !important;
            }

            .profile-records-col {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
                padding: 0 8px !important;
                margin-bottom: 12px !important;
                display: flex !important;
                flex-direction: column !important;
            }

            .profile-work-desc-wrap {
                margin: 0 8px 14px 8px !important;
                clear: both !important;
            }

            .profile-records-container {
                padding: 12px 10px !important;
                border-radius: 10px !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .records-section-title {
                padding: 8px 10px !important;
                margin-bottom: 10px !important;
                gap: 8px !important;
                border-radius: 8px !important;
            }

            .records-title-icon-wrap {
                width: 26px !important;
                height: 26px !important;
                font-size: 12px !important;
                border-radius: 6px !important;
            }

            .records-title-heading {
                font-size: 12px !important;
                line-height: 1.25 !important;
            }

            .records-title-sub {
                font-size: 10px !important;
                line-height: 1.2 !important;
            }

            .records-badge-count {
                font-size: 10px !important;
                padding: 2px 7px !important;
                border-radius: 10px !important;
            }

            .record-category-card {
                margin-bottom: 10px !important;
                border-radius: 7px !important;
            }

            .record-category-header {
                padding: 8px 10px !important;
                font-size: 12px !important;
                gap: 6px !important;
                border-radius: 6px 6px 0 0 !important;
            }

            .record-category-card .records-clean-list {
                padding: 8px 8px !important;
                gap: 6px !important;
                border-radius: 0 0 6px 6px !important;
            }

            .record-clean-item {
                padding: 7px 9px !important;
                gap: 8px !important;
                border-radius: 6px !important;
            }

            .record-clean-num {
                font-size: 10px !important;
                padding: 2px 5px !important;
                min-width: 18px !important;
                text-align: center !important;
                border-radius: 4px !important;
            }

            .record-clean-icon {
                font-size: 11px !important;
                margin-top: 3px !important;
            }

            .record-clean-text {
                font-size: 12px !important;
                line-height: 1.4 !important;
            }

            .records-empty-placeholder {
                padding: 16px 10px !important;
                font-size: 12px !important;
                gap: 6px !important;
            }

            /* 8. Attendance Tab Mobile Styling */
            .attendance-well {
                padding: 16px 12px !important;
            }

            .dash-placeholder-box {
                padding: 28px 12px !important;
                border-radius: 8px !important;
            }

            .dash-placeholder-icon {
                width: 44px !important;
                height: 44px !important;
                font-size: 18px !important;
                margin-bottom: 10px !important;
            }

            .dash-placeholder-title {
                font-size: 14.5px !important;
            }

            .dash-placeholder-desc {
                font-size: 12px !important;
                line-height: 1.45 !important;
            }

            /* 9. Task Management Tab Mobile Styling */
            .todo-app-wrapper {
                padding: 8px 12px 12px 12px !important;
                border-radius: 0 0 10px 10px !important;
                border-top-left-radius: 0 !important;
                border-top-right-radius: 0 !important;
                margin-top: -1px !important;
            }

            .todo-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 10px !important;
                padding-bottom: 12px !important;
                margin-bottom: 14px !important;
            }

            .todo-title-group h3 {
                font-size: 17px !important;
            }

            .todo-header > div:last-child {
                width: 100% !important;
            }

            .btn-dash-add-task-dummy {
                width: 100% !important;
                justify-content: center !important;
                padding: 8px 14px !important;
            }

            .todo-toolbar {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 10px !important;
                margin-bottom: 12px !important;
            }

            .todo-search-box {
                min-width: 100% !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            .todo-filter-btns {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                width: 100% !important;
                gap: 5px !important;
                padding-bottom: 4px !important;
                scrollbar-width: none !important;
            }

            .todo-filter-btns::-webkit-scrollbar {
                display: none !important;
            }

            .todo-filter-btn {
                font-size: 11px !important;
                padding: 5px 10px !important;
                white-space: nowrap !important;
                flex-shrink: 0 !important;
            }

            .todo-card {
                padding: 10px 12px !important;
                gap: 10px !important;
                border-radius: 8px !important;
            }

            .todo-task-title {
                font-size: 13px !important;
                line-height: 1.35 !important;
            }

            .todo-meta-row {
                gap: 8px !important;
                font-size: 11px !important;
                margin-top: 4px !important;
            }

            .todo-meta-item {
                font-size: 11px !important;
            }

            .todo-meta-tag {
                font-size: 10px !important;
                padding: 1px 6px !important;
            }

            .todo-completed-section {
                margin-top: 14px !important;
                padding-top: 12px !important;
            }

            .todo-completed-header {
                font-size: 12px !important;
                padding: 6px 8px !important;
            }

            /* 10. Work Guidelines Tab Mobile Styling */
            .guidelines-well {
                padding: 12px 10px !important;
            }

            .guidelines-top-bar {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 10px !important;
                margin-bottom: 12px !important;
            }

            .guidelines-search-wrap {
                max-width: 100% !important;
                width: 100% !important;
            }

            #guidelinesSearchInput {
                height: 38px !important;
                font-size: 12.5px !important;
            }

            .guideline-entry-card {
                padding: 12px 10px !important;
                border-radius: 10px !important;
                margin-bottom: 10px !important;
            }

            .guideline-card-head {
                gap: 8px !important;
            }

            .guideline-title-wrap {
                min-width: 0 !important;
                flex: 1 1 100% !important;
                align-items: flex-start !important;
                gap: 10px !important;
            }

            .guideline-card-icon {
                width: 32px !important;
                height: 32px !important;
                font-size: 14px !important;
                border-radius: 8px !important;
            }

            .guideline-title-wrap h4 {
                font-size: 13.5px !important;
                line-height: 1.35 !important;
            }

            .guideline-meta-row {
                font-size: 11px !important;
                gap: 6px !important;
            }

            .guideline-clean-badge {
                font-size: 10px !important;
                padding: 2px 7px !important;
            }

            .guideline-desc-box {
                margin-top: 10px !important;
                padding: 8px 10px !important;
                font-size: 12px !important;
                line-height: 1.45 !important;
            }

            .guideline-attachments-wrap {
                padding-top: 8px !important;
                margin-top: 8px !important;
                gap: 6px !important;
            }

            .guideline-file-chip {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 6px 10px !important;
            }

            .file-chip-name {
                max-width: calc(100vw - 160px) !important;
                font-size: 11.5px !important;
            }

            .file-chip-size {
                font-size: 10.5px !important;
            }
        }

        /* Extra Compact Adjustments for Ultra-narrow Phones (<= 480px) */
        @media screen and (max-width: 480px) {
            .page-content,
            .page-content.inset {
                padding-left: 6px !important;
                padding-right: 6px !important;
                padding-top: 6px !important;
            }

            .profile-records-col {
                padding: 0 4px !important;
                margin-bottom: 10px !important;
            }

            .profile-work-desc-wrap {
                margin: 0 4px 12px 4px !important;
            }

            .profile-records-container {
                padding: 10px 8px !important;
            }

            .file-chip-name {
                max-width: calc(100vw - 130px) !important;
            }

            .profile-user-name {
                font-size: 17px !important;
            }
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid my-dash-container">
                
                <?php
                $targetDashboardUid = (int)($userRow['user_id'] ?? $actualLoggedInUserId);
                $targetDashboardName = $userName;
                $targetDashboardRole = $userRole;
                ?>
                <!-- 1. Tabs Bar -->
                <div class="dash-tabs-bar" role="tablist">
                    <div class="dash-tabs-scroller">
                        <button type="button" class="dash-tab-btn active" data-target="#pane-profile">
                            <i class="fa fa-user-circle"></i> Profile
                        </button>
                        <button type="button" class="dash-tab-btn" data-target="#pane-attendance">
                            <i class="fa fa-calendar-check-o"></i> Attendance
                        </button>
                        <button type="button" class="dash-tab-btn" data-target="#pane-task-mgmt">
                            <i class="fa fa-tasks"></i> Task Management
                        </button>
                        <button type="button" class="dash-tab-btn" data-target="#pane-guidelines">
                            <i class="fa fa-book"></i> Work Guidelines
                        </button>
                        <?php if ($isAuditorUser && !$isViewingOther): ?>
                        <a href="auditorDashboard.php" class="dash-tab-btn" style="<?php echo (!$isManagementUser && !$isTopMgmt) ? 'margin-left: auto;' : ''; ?> background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%); color: #ffffff !important; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(13, 77, 139, 0.25);">
                            <i class="fa fa-clipboard-check"></i> Auditor Dashboard <i class="fa fa-external-link" style="font-size: 10.5px;"></i>
                        </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($isManagementUser): ?>
                    <!-- View Other Dashboard Section (For Management Role) -->
                    <div class="dash-user-switcher-container" style="margin-left: auto; display: inline-flex; align-items: center; gap: 8px; flex-shrink: 0; padding: 2px 4px 2px 14px;">
                        
                        <!-- 1. PC / Desktop View Switcher (Strictly intact for > 768px) -->
                        <div class="dash-switcher-desktop-wrap" style="display: flex; align-items: center; gap: 8px;">
                            <div style="position: relative; min-width: 235px;">
                                <select id="dashUserSwitcherSelect" class="form-control" style="height: 36px; font-size: 13px; border-radius: 8px; border: 1.5px solid <?php echo $isViewingOther ? '#2563eb' : '#cbd5e1'; ?>; background: <?php echo $isViewingOther ? '#eff6ff' : '#ffffff'; ?>; color: <?php echo $isViewingOther ? '#1e40af' : '#0f172a'; ?>; padding: 4px 28px 4px 12px; font-weight: 600; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                                    <?php if (!$isViewingOther): ?>
                                        <option value="__placeholder__" selected disabled hidden>👥 View Other's Dashboard</option>
                                        <option value="">👤 My Dashboard</option>
                                    <?php else: ?>
                                        <option value="">👤 My Dashboard</option>
                                    <?php endif; ?>
                                    <?php if (!empty($switcherUsers)): ?>
                                        <?php foreach ($switcherUsers as $su): 
                                            $suKey = $su['key'];
                                            $isSel = ($isViewingOther && $currentViewKey === $suKey);
                                        ?>
                                        <option value="<?php echo htmlspecialchars($suKey); ?>" <?php echo $isSel ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($su['clean_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <i class="fa fa-chevron-down" style="position: absolute; right: 11px; top: 12px; font-size: 11px; color: <?php echo $isViewingOther ? '#2563eb' : '#64748b'; ?>; pointer-events: none;"></i>
                            </div>
                            <?php if ($isViewingOther): ?>
                            <a href="myDashboard.php" class="dash-switcher-reset-btn" title="Back to my own dashboard" style="display: inline-flex; align-items: center; gap: 5px; height: 36px; padding: 0 12px; font-size: 12px; font-weight: 600; border-radius: 8px; background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; text-decoration: none; transition: all 0.15s ease;">
                                <i class="fa fa-times"></i> Reset
                            </a>
                            <?php endif; ?>
                        </div>

                        <!-- 2. Mobile View Switcher (<= 768px, 100% inside mobile viewport) -->
                        <div class="dash-switcher-mobile-wrap">
                            <div class="dash-mobile-switcher-container">
                                <div class="dash-mobile-btn-row">
                                    <button type="button" id="dashMobileSwitcherToggle" class="dash-mobile-switcher-btn <?php echo $isViewingOther ? 'is-active-view' : ''; ?>">
                                        <div class="dash-mobile-btn-left">
                                            <?php if ($isViewingOther): ?>
                                                <i class="fa fa-user-circle mobile-user-icon"></i>
                                                <span class="mobile-btn-text"><?php echo htmlspecialchars($viewingTargetName ?: 'Viewing Other'); ?></span>
                                            <?php else: ?>
                                                <i class="fa fa-users mobile-user-icon"></i>
                                                <span class="mobile-btn-text">View Other's Dashboard</span>
                                            <?php endif; ?>
                                        </div>
                                        <i class="fa fa-chevron-down dash-mobile-arrow"></i>
                                    </button>
                                    <?php if ($isViewingOther): ?>
                                    <a href="myDashboard.php" class="dash-switcher-reset-btn dash-mobile-reset-btn" title="Back to my own dashboard">
                                        <i class="fa fa-times"></i> Reset
                                    </a>
                                    <?php endif; ?>
                                </div>

                                <!-- Mobile Dropdown List (Strictly contained in mobile view) -->
                                <div id="dashMobileSwitcherDropdown" class="dash-mobile-dropdown-menu">
                                    <div class="dash-mobile-dropdown-header">
                                        <span class="mobile-dropdown-title"><i class="fa fa-users"></i> Switch Dashboard</span>
                                        <button type="button" class="dash-mobile-dropdown-close" id="dashMobileDropdownCloseBtn" aria-label="Close">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>

                                    <?php if (!empty($switcherUsers) && count($switcherUsers) > 4): ?>
                                    <div class="dash-mobile-search-bar">
                                        <i class="fa fa-search"></i>
                                        <input type="text" id="dashMobileUserSearch" class="dash-mobile-search-input" placeholder="Search user..." autocomplete="off">
                                    </div>
                                    <?php endif; ?>

                                    <div class="dash-mobile-user-list">
                                        <!-- Personal / My Dashboard Option -->
                                        <a href="myDashboard.php" class="dash-mobile-user-item <?php echo !$isViewingOther ? 'is-selected' : ''; ?>" data-name="my dashboard">
                                            <div class="mobile-item-profile">
                                                <div class="mobile-item-avatar avatar-personal">
                                                    <i class="fa fa-user"></i>
                                                </div>
                                                <div class="mobile-item-details">
                                                    <div class="mobile-item-name">My Dashboard</div>
                                                    <div class="mobile-item-sub">Return to your personal view</div>
                                                </div>
                                            </div>
                                            <?php if (!$isViewingOther): ?>
                                                <div class="mobile-item-check"><i class="fa fa-check-circle"></i></div>
                                            <?php endif; ?>
                                        </a>

                                        <?php if (!empty($switcherUsers)): ?>
                                            <?php foreach ($switcherUsers as $su): 
                                                $suKey = $su['key'];
                                                $isSel = ($isViewingOther && $currentViewKey === $suKey);
                                                $cleanName = $su['clean_name'];
                                                $suEmail = $su['email_id'] ?? ($su['email'] ?? '');
                                                $suRole = $su['role'] ?? '';
                                                // Dynamic initial
                                                $subParts = preg_split('/\s+/', trim($cleanName));
                                                $suInitial = '';
                                                if (count($subParts) >= 2) {
                                                    $suInitial = mb_strtoupper(mb_substr($subParts[0], 0, 1) . mb_substr(end($subParts), 0, 1));
                                                } else {
                                                    $suInitial = mb_strtoupper(mb_substr($cleanName, 0, 2));
                                                }
                                            ?>
                                            <a href="myDashboard.php?view_user=<?php echo urlencode($suKey); ?>" class="dash-mobile-user-item <?php echo $isSel ? 'is-selected' : ''; ?>" data-name="<?php echo htmlspecialchars(strtolower($cleanName . ' ' . $suEmail)); ?>">
                                                <div class="mobile-item-profile">
                                                    <div class="mobile-item-avatar">
                                                        <?php echo htmlspecialchars($suInitial); ?>
                                                    </div>
                                                    <div class="mobile-item-details">
                                                        <div class="mobile-item-name"><?php echo htmlspecialchars($cleanName); ?></div>
                                                        <?php if (!empty($suEmail)): ?>
                                                            <div class="mobile-item-sub"><?php echo htmlspecialchars($suEmail); ?></div>
                                                        <?php elseif (!empty($suRole)): ?>
                                                            <div class="mobile-item-sub"><?php echo htmlspecialchars($suRole); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php if ($isSel): ?>
                                                    <div class="mobile-item-check"><i class="fa fa-check-circle"></i></div>
                                                <?php endif; ?>
                                            </a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <div id="dashMobileNoUserFound" style="display: none; padding: 22px 14px; text-align: center; color: #94a3b8; font-size: 12.5px;">
                                            <i class="fa fa-search" style="font-size: 18px; margin-bottom: 6px; display: block;"></i>
                                            No user found matching search
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($isViewingOther): ?>
                <!-- Alert Banner when viewing another user's dashboard in Management Mode -->
                <div class="dash-viewing-alert" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; border-left: 5px solid #2563eb; border-radius: 10px; padding: 12px 18px; margin-bottom: 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 2px 6px rgba(37,99,235,0.08);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 9px; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                            <i class="fa fa-eye"></i>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 700; color: #1e3a8a;">
                                Viewing Dashboard of: <strong style="color: #0f172a; font-size: 15px;"><?php echo htmlspecialchars($userName); ?></strong>
                                <span class="badge" style="background: #2563eb; color: #fff; font-size: 11px; margin-left: 6px; padding: 3px 8px; font-weight: 600;"><?php echo htmlspecialchars($userRole); ?></span>
                            </div>
                            <div style="font-size: 12px; color: #475569; margin-top: 2px;">
                                You are reviewing this user's profile, responsibilities, and directives in Management View.
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="myDashboard.php" class="btn" style="background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; font-weight: 600; font-size: 12.5px; border-radius: 7px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-decoration: none;">
                            <i class="fa fa-arrow-left"></i> Return to My Dashboard
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 2. Tab Content Panes -->
                <div class="dash-tab-content">
                    
                    <!-- Profile Tab Pane -->
                    <div class="dash-tab-pane active" id="pane-profile">
                        
                        <!-- MasterCreator Styled Card with Distinct Header -->
                        <div class="qms_complete_btn header-theme-profile">
                            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                <div style="display: flex; align-items: center; gap: 9px;">
                                    <i class="fa fa-id-card-o" style="font-size: 15px;"></i>
                                    <span>User Profile &amp; Responsibilities</span>
                                </div>
                                <span class="header-role-pill"><i class="fa fa-shield"></i> <?php echo htmlspecialchars($userRole); ?></span>
                            </div>
                        </div>
                        <div class="well master-profile-well">
                            
                            <!-- Profile Header Top Area -->
                            <div class="profile-header-area">
                                <div class="profile-avatar-wrap">
                                    <div class="profile-avatar-circle" title="<?php echo htmlspecialchars($userName); ?>">
                                        <?php echo htmlspecialchars($initials); ?>
                                    </div>
                                    <div class="profile-avatar-status" title="Active"></div>
                                </div>
                                <div class="profile-identity-info">
                                    <h3 class="profile-user-name"><?php echo htmlspecialchars($userName); ?></h3>
                                    <div class="profile-hero-sub">
                                        Have a productive day at <strong>A.I.T.F (ASSESSMENT AND INSTITUTE FOR TECHNICAL FORUM)</strong>
                                    </div>
                                    <div class="profile-badges-row">
                                        <div class="profile-sq-badge badge-designation <?php echo $designationAssigned === '' ? 'is-empty' : ''; ?>" title="Designation Assigned">
                                            <i class="fa fa-briefcase"></i>
                                            <span><?php echo $designationAssigned !== '' ? htmlspecialchars($designationAssigned) : 'No Designation Assigned'; ?></span>
                                        </div>
                                        <div class="profile-sq-badge badge-role" title="User Role">
                                            <i class="fa fa-shield"></i>
                                            <span><?php echo htmlspecialchars($userRole); ?></span>
                                        </div>
                                        <?php if (!empty($userEid)): ?>
                                        <div class="profile-sq-badge badge-eid" title="Employee ID">
                                            <i class="fa fa-id-badge"></i>
                                            <span>Emp ID: <?php echo htmlspecialchars($userEid); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($userPcId)): ?>
                                        <div class="profile-sq-badge badge-pc" title="PC ID">
                                            <i class="fa fa-desktop"></i>
                                            <span>PC: <?php echo htmlspecialchars($userPcId); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($userAbbr)): ?>
                                        <div class="profile-sq-badge badge-abbr" title="User Abbreviation">
                                            <i class="fa fa-tag"></i>
                                            <span><?php echo htmlspecialchars($userAbbr); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="profile-sq-badge badge-status" title="Account Status">
                                            <i class="fa fa-circle"></i>
                                            <span><?php echo htmlspecialchars(ucfirst($userStatus ?: 'Active')); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Details Strip matching MasterCreator stat pills -->
                            <div class="profile-details-strip">
                                <div class="mc-detail-card">
                                    <span class="mc-stat-icon icon-blue"><i class="fa fa-envelope-o"></i></span>
                                    <div class="detail-info">
                                        <div class="detail-lbl">Email Address</div>
                                        <div class="detail-val"><?php echo $userEmail !== '' ? htmlspecialchars($userEmail) : '-'; ?></div>
                                    </div>
                                </div>

                                <div class="mc-detail-card">
                                    <span class="mc-stat-icon icon-purple"><i class="fa fa-shield"></i></span>
                                    <div class="detail-info">
                                        <div class="detail-lbl">Role</div>
                                        <div class="detail-val"><?php echo htmlspecialchars($userRole); ?></div>
                                    </div>
                                </div>

                                <?php if (!empty($userEid)): ?>
                                <div class="mc-detail-card">
                                    <span class="mc-stat-icon icon-blue"><i class="fa fa-id-card-o"></i></span>
                                    <div class="detail-info">
                                        <div class="detail-lbl">Employee ID</div>
                                        <div class="detail-val"><?php echo htmlspecialchars($userEid); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($userPcId)): ?>
                                <div class="mc-detail-card">
                                    <span class="mc-stat-icon icon-purple"><i class="fa fa-desktop"></i></span>
                                    <div class="detail-info">
                                        <div class="detail-lbl">PC ID</div>
                                        <div class="detail-val"><?php echo htmlspecialchars($userPcId); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($userContact)): ?>
                                <div class="mc-detail-card">
                                    <span class="mc-stat-icon icon-green"><i class="fa fa-phone"></i></span>
                                    <div class="detail-info">
                                        <div class="detail-lbl">Contact</div>
                                        <div class="detail-val"><?php echo htmlspecialchars($userContact); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Applicable Records & Resource Issue Record Side-by-Side Row -->
                            <div class="row profile-records-row" style="margin: 16px 8px 0 8px; display: flex; flex-wrap: wrap;">
                                <div class="col-sm-6 profile-records-col" style="padding: 0 8px; margin-bottom: 16px; display: flex; flex-direction: column;">
                                    <!-- 1. APPLICABLE RECORDS (BLUE / SAPPHIRE THEME) -->
                                    <div class="profile-records-container records-theme-blue" style="width: 100%; flex: 1;">
                                        <div class="records-section-title">
                                            <div class="records-title-main">
                                                <div class="records-title-icon-wrap">
                                                    <i class="fa fa-folder-open"></i>
                                                </div>
                                                <div class="records-title-text">
                                                    <span class="records-title-heading">Applicable Records &amp; Documents</span>
                                                    <span class="records-title-sub">Reporting documents &amp; location records</span>
                                                </div>
                                            </div>
                                            <?php if (!empty($recordGroups)): ?>
                                                <span class="records-badge-count"><?php echo count($recordGroups); ?> Group<?php echo count($recordGroups) > 1 ? 's' : ''; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($recordGroups)): ?>
                                            <?php foreach ($recordGroups as $grp): ?>
                                                <?php if (!empty($grp['header'])): ?>
                                                    <div class="record-category-card">
                                                        <div class="record-category-header">
                                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                                <i class="fa fa-folder-open-o"></i>
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
                                                                        <i class="fa fa-file-text-o record-clean-icon"></i>
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
                                                                <i class="fa fa-file-text-o record-clean-icon"></i>
                                                                <span class="record-clean-text"><?php echo htmlspecialchars($item); ?></span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="records-empty-placeholder">
                                                <i class="fa fa-info-circle"></i>
                                                <span>Not assigned yet</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-sm-6 profile-records-col" style="padding: 0 8px; margin-bottom: 16px; display: flex; flex-direction: column;">
                                    <!-- 2. RESOURCE ISSUE RECORD (WARM AMBER / ORANGE THEME) -->
                                    <div class="profile-records-container records-theme-amber" style="width: 100%; flex: 1;">
                                        <div class="records-section-title">
                                            <div class="records-title-main">
                                                <div class="records-title-icon-wrap">
                                                    <i class="fa fa-exclamation-circle"></i>
                                                </div>
                                                <div class="records-title-text">
                                                    <span class="records-title-heading">Resource Issue Record</span>
                                                    <span class="records-title-sub">Hardware, software, access &amp; resource issue logs</span>
                                                </div>
                                            </div>
                                            <?php if (!empty($resIssueGroups)): ?>
                                                <span class="records-badge-count"><?php echo count($resIssueGroups); ?> Group<?php echo count($resIssueGroups) > 1 ? 's' : ''; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($resIssueGroups)): ?>
                                            <?php foreach ($resIssueGroups as $grp): ?>
                                                <?php if (!empty($grp['header'])): ?>
                                                    <div class="record-category-card">
                                                        <div class="record-category-header">
                                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                                <i class="fa fa-wrench"></i>
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
                                                                        <i class="fa fa-exclamation-circle record-clean-icon"></i>
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
                                                                <i class="fa fa-exclamation-circle record-clean-icon"></i>
                                                                <span class="record-clean-text"><?php echo htmlspecialchars($item); ?></span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="records-empty-placeholder">
                                                <i class="fa fa-info-circle"></i>
                                                <span>Not assigned yet</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. WORK DESCRIPTION (EMERALD GREEN THEME) -->
                            <div class="profile-work-desc-wrap" style="margin: 0 16px 20px 16px; clear: both;">
                                <div class="profile-records-container records-theme-emerald">
                                    <div class="records-section-title">
                                        <div class="records-title-main">
                                            <div class="records-title-icon-wrap">
                                                <i class="fa fa-briefcase"></i>
                                            </div>
                                            <div class="records-title-text">
                                                <span class="records-title-heading">Work Description &amp; Responsibilities</span>
                                                <span class="records-title-sub">Main Responsibilities, Authorities &amp; Reporting</span>
                                            </div>
                                        </div>
                                        <?php if (!empty($workDescGroups)): ?>
                                            <span class="records-badge-count"><?php echo count($workDescGroups); ?> Group<?php echo count($workDescGroups) > 1 ? 's' : ''; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($workDescGroups)): ?>
                                        <?php foreach ($workDescGroups as $grp): ?>
                                            <?php if (!empty($grp['header'])): ?>
                                                <div class="record-category-card">
                                                    <div class="record-category-header">
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <i class="fa fa-tasks"></i>
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
                                                                    <i class="fa fa-check-square-o record-clean-icon"></i>
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
                                                            <i class="fa fa-check-square-o record-clean-icon"></i>
                                                            <span class="record-clean-text"><?php echo htmlspecialchars($item); ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="records-empty-placeholder">
                                            <i class="fa fa-info-circle"></i>
                                            <span>Not assigned yet</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- 2. Attendance Tab Pane -->
                    <div class="dash-tab-pane" id="pane-attendance">
                        <?php
                        $targetAttendanceUid = (int)($userRow['user_id'] ?? $actualLoggedInUserId);
                        $targetAttendanceName = $userName;
                        $targetAttendanceRole = $userRole;
                        include __DIR__ . '/include/attendance_calendar_component.php';
                        ?>
                    </div>

                    <!-- 3. Task Management Tab Pane -->
                    <div class="dash-tab-pane" id="pane-task-mgmt">
                        <?php
                        $targetDashboardUid = (int)($userRow['user_id'] ?? $actualLoggedInUserId);
                        $targetDashboardName = $userName;
                        $targetDashboardRole = $userRole;
                        include __DIR__ . '/include/task_manager_component.php';
                        ?>
                    </div>

                    <!-- 4. Work Guidelines Tab Pane -->
                    <div class="dash-tab-pane" id="pane-guidelines">
                        <?php
                        $myGuidelineUid = (int)($userRow['user_id'] ?? ($_SESSION['id'] ?? 0));
                        $myGuidelineRole = $userRole ?? ($_SESSION['role'] ?? '');
                        $myGuidelineEmail = $userEmail ?? ($_SESSION['email'] ?? '');
                        $myGuidelineName = $userName ?? ($_SESSION['username'] ?? '');

                        $guidelinesList = aitf_get_guidelines_for_current_user($db, $myGuidelineUid, $myGuidelineRole, $myGuidelineEmail, $myGuidelineName);
                        $guidelinesCount = count($guidelinesList);
                        ?>
                        <div class="qms_complete_btn header-theme-guidelines" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14.5px;">
                                <i class="fa fa-book" style="font-size: 16px;"></i>
                                <span>Work Guidelines</span>
                            </div>
                            <div>
                                <span style="background: rgba(255,255,255,0.22); color: #fff; font-size: 11.5px; padding: 4px 12px; border-radius: 20px; font-weight: 600; letter-spacing: 0.3px; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fa fa-check-circle" style="font-size: 11px;"></i> <?php echo $guidelinesCount; ?> Active Directive<?php echo $guidelinesCount === 1 ? '' : 's'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="well master-profile-well guidelines-well" style="padding: 20px;">
                            <div class="guidelines-top-bar" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 13px; color: #64748b; font-weight: 500;">
                                        <i class="fa fa-info-circle" style="color: #0d4d8b; margin-right: 4px;"></i>
                                        Official directives, protocols, and standard operating procedures assigned to you.
                                    </span>
                                </div>
                                <?php if (!empty($guidelinesList)): ?>
                                <div class="guidelines-search-wrap" style="position: relative; width: 100%; max-width: 260px;">
                                    <input type="text" id="guidelinesSearchInput" class="form-control" placeholder="Search guidelines..." style="height: 34px; font-size: 12.5px; border-radius: 8px; padding-left: 32px; border: 1px solid #cbd5e1; background: #ffffff;">
                                    <i class="fa fa-search" style="position: absolute; left: 11px; top: 10px; color: #94a3b8; font-size: 12px;"></i>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if (empty($guidelinesList)): ?>
                                <div class="dash-placeholder-box" style="padding: 45px 20px; text-align: center;">
                                    <div class="dash-placeholder-icon" style="background: rgba(13, 77, 139, 0.08); color: #0d4d8b; width: 60px; height: 60px; line-height: 60px; border-radius: 50%; font-size: 24px; margin: 0 auto 16px auto;">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>
                                    <h4 class="dash-placeholder-title" style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">No Guidelines Assigned</h4>
                                    <p class="dash-placeholder-desc" style="color: #64748b; font-size: 13px; max-width: 460px; margin: 0 auto;">
                                        You currently do not have any work guidelines assigned. Directives published by management will appear here.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="guidelines-stack" id="guidelinesListContainer" style="display: flex; flex-direction: column; gap: 12px;">
                                    <?php foreach ($guidelinesList as $gl): 
                                        $glTime = !empty($gl['created_at']) ? strtotime($gl['created_at']) : time();
                                        $glDate = date('d M Y, h:i A', $glTime);
                                        $glBy = !empty($gl['created_by']) ? $gl['created_by'] : 'Director';
                                        if (in_array(strtolower(trim($glBy)), ['main admin', 'top mgmt', 'admin', 'director'], true)) {
                                            $glBy = 'Management';
                                        }
                                        $attachments = $gl['attachments'] ?? [];
                                        $glDesc = trim($gl['description'] ?? '');
                                        $searchHaystack = strtolower(($gl['title'] ?? '') . ' ' . $glDesc . ' ' . $glBy);
                                    ?>
                                    <div class="guideline-entry-card guideline-filter-item" data-search="<?php echo htmlspecialchars($searchHaystack); ?>">
                                        <div class="guideline-card-head" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
                                            <div class="guideline-title-wrap" style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 260px;">
                                                <div class="guideline-card-icon">
                                                    <i class="fa fa-file-text-o"></i>
                                                </div>
                                                <div>
                                                    <h4 style="margin: 0; font-size: 14.5px; font-weight: 700; color: #0f172a; line-height: 1.35;">
                                                        <?php echo htmlspecialchars($gl['title']); ?>
                                                    </h4>
                                                    <div class="guideline-meta-row" style="font-size: 11.5px; color: #64748b; margin-top: 3px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                        <span><i class="fa fa-user-circle-o" style="color: #94a3b8;"></i> By: <strong style="color: #334155;"><?php echo htmlspecialchars($glBy); ?></strong></span>
                                                        <span style="color: #cbd5e1;">•</span>
                                                        <span><i class="fa fa-clock-o" style="color: #94a3b8;"></i> <?php echo $glDate; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="guideline-clean-badge">
                                                    <i class="fa fa-check-circle"></i> Active
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <?php if ($glDesc !== ''): ?>
                                        <div class="guideline-desc-box">
                                            <?php echo nl2br(htmlspecialchars($glDesc)); ?>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($attachments)): ?>
                                        <div class="guideline-attachments-wrap" style="margin-top: <?php echo ($glDesc !== '') ? '12px' : '10px'; ?>;">
                                            <span style="font-size: 11.5px; font-weight: 600; color: #64748b; margin-right: 4px;">
                                                <i class="fa fa-paperclip"></i> Documents (<?php echo count($attachments); ?>):
                                            </span>
                                            <?php foreach ($attachments as $att): 
                                                $iconInfo = aitf_get_file_icon_info($att['original_name']);
                                                $fileSizeFormatted = aitf_format_file_size($att['file_size']);
                                            ?>
                                            <a href="view_guideline_doc.php?id=<?php echo (int)$att['id']; ?>" target="_blank" class="guideline-file-chip" title="<?php echo htmlspecialchars($att['original_name']); ?>">
                                                <span class="file-chip-icon"><i class="<?php echo $iconInfo['icon']; ?>" style="color: <?php echo $iconInfo['color']; ?>;"></i></span>
                                                <span class="file-chip-details">
                                                    <span class="file-chip-name"><?php echo htmlspecialchars($att['original_name']); ?></span>
                                                    <span class="file-chip-size">(<?php echo $fileSizeFormatted; ?>)</span>
                                                </span>
                                                <i class="fa fa-external-link file-chip-arrow"></i>
                                            </a>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div id="guidelinesNoMatch" style="display: none; padding: 30px 20px; text-align: center; color: #64748b; font-size: 13px;">
                                    <i class="fa fa-search" style="font-size: 22px; margin-bottom: 8px; display: block; color: #94a3b8;"></i>
                                    No guidelines match your search query.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
$(document).ready(function() {
    $('.dash-tab-btn').on('click', function(e) {
        var target = $(this).data('target');
        if (!target) return;
        e.preventDefault();
        
        $('.dash-tab-btn').removeClass('active');
        $(this).addClass('active');
        
        $('.dash-tab-pane').removeClass('active').hide();
        $(target).addClass('active').fadeIn(150);

        if (target === '#pane-task-mgmt') {
            $('.dash-tabs-bar').addClass('tabs-attached-bottom');
        } else {
            $('.dash-tabs-bar').removeClass('tabs-attached-bottom');
        }

        // Smooth scroll tab into view on mobile
        if (window.innerWidth <= 768 && this.scrollIntoView) {
            try {
                this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } catch (err) {}
        }
    });

    // Auto-activate tab from hash if present
    var hash = window.location.hash;
    if (hash && $('.dash-tab-btn[data-target="' + hash + '"]').length) {
        $('.dash-tab-btn[data-target="' + hash + '"]').click();
    } else if (hash === '#guidelines' || hash === '#pane-guidelines' || hash === '#work-guidelines') {
        $('.dash-tab-btn[data-target="#pane-guidelines"]').click();
    } else if (hash === '#tasks' || hash === '#task-mgmt' || hash === '#pane-task-mgmt' || hash === '#task') {
        $('.dash-tab-btn[data-target="#pane-task-mgmt"]').click();
    } else if (hash === '#attendance' || hash === '#pane-attendance') {
        $('.dash-tab-btn[data-target="#pane-attendance"]').click();
    }

    // Set initial tabs attached state
    if ($('.dash-tab-btn.active').data('target') === '#pane-task-mgmt') {
        $('.dash-tabs-bar').addClass('tabs-attached-bottom');
    }

    // Live filter for work guidelines
    $('#guidelinesSearchInput').on('keyup', function() {
        var query = $(this).val().toLowerCase().trim();
        var matches = 0;
        $('.guideline-filter-item').each(function() {
            var searchData = $(this).attr('data-search') || '';
            if (!query || searchData.indexOf(query) !== -1) {
                $(this).show();
                matches++;
            } else {
                $(this).hide();
            }
        });
        if (matches === 0 && query !== '') {
            $('#guidelinesNoMatch').show();
        } else {
            $('#guidelinesNoMatch').hide();
        }
    });

    // Management Dashboard Switcher Change Event (Desktop PC view)
    $('#dashUserSwitcherSelect').on('change', function() {
        var selectedVal = $(this).val();
        if (selectedVal === '__placeholder__') return;
        var activeTab = $('.dash-tab-btn.active').data('target') || '';
        var targetUrl = 'myDashboard.php';
        if (selectedVal) {
            targetUrl += '?view_user=' + encodeURIComponent(selectedVal);
        }
        if (activeTab) {
            targetUrl += activeTab;
        }
        window.location.href = targetUrl;
    });

    // Mobile Switcher Dropdown Toggle
    $(document).on('click', '#dashMobileSwitcherToggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var $menu = $('#dashMobileSwitcherDropdown');
        if ($menu.is(':visible')) {
            $menu.fadeOut(120);
            $btn.removeClass('is-open');
        } else {
            $menu.fadeIn(150);
            $btn.addClass('is-open');
            setTimeout(function() {
                $('#dashMobileUserSearch').focus();
            }, 60);
        }
    });

    // Mobile Switcher Close Button
    $(document).on('click', '#dashMobileDropdownCloseBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#dashMobileSwitcherDropdown').fadeOut(120);
        $('#dashMobileSwitcherToggle').removeClass('is-open');
    });

    // Close Dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dash-switcher-mobile-wrap').length) {
            if ($('#dashMobileSwitcherDropdown').is(':visible')) {
                $('#dashMobileSwitcherDropdown').fadeOut(120);
                $('#dashMobileSwitcherToggle').removeClass('is-open');
            }
        }
    });

    // User selection in Mobile Dropdown
    $(document).on('click', '.dash-mobile-user-item', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (!href) return;
        var activeTab = $('.dash-tab-btn.active').data('target') || '';
        if (activeTab) {
            href += activeTab;
        }
        window.location.href = href;
    });

    // Live search filter in Mobile Dropdown
    $(document).on('input', '#dashMobileUserSearch', function() {
        var q = $(this).val().toLowerCase().trim();
        var matches = 0;
        $('.dash-mobile-user-item').each(function() {
            var name = $(this).attr('data-name') || '';
            if (!q || name.indexOf(q) !== -1) {
                $(this).show();
                matches++;
            } else {
                $(this).hide();
            }
        });
        if (matches === 0 && q !== '') {
            $('#dashMobileNoUserFound').show();
        } else {
            $('#dashMobileNoUserFound').hide();
        }
    });

    $(document).on('click', '.dash-switcher-reset-btn', function(e) {
        e.preventDefault();
        var activeTab = $('.dash-tab-btn.active').data('target') || '';
        window.location.href = 'myDashboard.php' + activeTab;
    });

    // Static non-working Add Task Button (No action, no testing messages)
    $(document).on('click', '.btn-dash-add-task-dummy', function(e) {
        e.preventDefault();
    });

});
</script>

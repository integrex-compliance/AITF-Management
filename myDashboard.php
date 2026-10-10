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

    // Fetch logged in user details with robust error handling
    $userRow = null;
    $isAuditorUser = (isset($_SESSION['role']) && $_SESSION['role'] === 'Auditor') || !empty($_SESSION['auditor_id']);

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
                // Try matching user table by email, auditor_number (e_id), or name
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

        @media (max-width: 768px) {
            .mc-hero-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                padding: 16px;
            }
            .profile-header-area {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 14px;
                padding: 20px 16px;
            }
            .profile-identity-info {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .profile-meta-tags {
                justify-content: center;
            }
            .profile-details-strip {
                grid-template-columns: 1fr;
                padding: 16px;
            }
            .profile-records-container {
                padding: 16px;
            }
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
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid my-dash-container">
                
                <!-- 1. Tabs Bar -->
                <div class="dash-tabs-bar" role="tablist">
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
                    <?php if ($isAuditorUser): ?>
                    <a href="auditorDashboard.php" class="dash-tab-btn" style="margin-left: auto; background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%); color: #ffffff !important; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(13, 77, 139, 0.25);">
                        <i class="fa fa-clipboard-check"></i> Auditor Dashboard <i class="fa fa-external-link" style="font-size: 10.5px;"></i>
                    </a>
                    <?php endif; ?>
                </div>

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

                            <!-- Applicable Records & Work Description Side-by-Side Row -->
                            <div class="row" style="margin: 16px 8px 0 8px; display: flex; flex-wrap: wrap;">
                                <div class="col-sm-6" style="padding: 0 8px; margin-bottom: 16px; display: flex; flex-direction: column;">
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

                                <div class="col-sm-6" style="padding: 0 8px; margin-bottom: 16px; display: flex; flex-direction: column;">
                                    <!-- 2. WORK DESCRIPTION (EMERALD GREEN THEME) -->
                                    <div class="profile-records-container records-theme-emerald" style="width: 100%; flex: 1;">
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

                            <!-- 3. RESOURCE ISSUE RECORD (WARM AMBER / ORANGE THEME) -->
                            <div style="margin: 0 16px 20px 16px; clear: both;">
                                <div class="profile-records-container records-theme-amber">
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
                    </div>
                    <!-- 2. Attendance Tab Pane -->
                    <div class="dash-tab-pane" id="pane-attendance">
                        <div class="qms_complete_btn header-theme-attendance">
                            <i class="fa fa-calendar-check-o"></i> Attendance
                        </div>
                        <div class="well master-profile-well" style="padding: 24px;">
                            <div class="dash-placeholder-box">
                                <div class="dash-placeholder-icon" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6;">
                                    <i class="fa fa-clock-o"></i>
                                </div>
                                <h4 class="dash-placeholder-title">Coming Soon</h4>
                                <p class="dash-placeholder-desc">
                                    Attendance feature is currently under development and will be available soon.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Task Management Tab Pane -->
                    <div class="dash-tab-pane" id="pane-task-mgmt">
                        <div class="qms_complete_btn header-theme-tasks">
                            <i class="fa fa-tasks"></i> Task Management
                        </div>
                        <div class="well master-profile-well" style="padding: 24px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                                <div>
                                    <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                                        <i class="fa fa-check-square-o" style="color: #0284c7; margin-right: 6px;"></i> Assigned Tasks &amp; Activities
                                    </h4>
                                    <p style="margin: 4px 0 0 0; font-size: 12.5px; color: #64748b;">
                                        Track, manage and view your assigned workflow tasks
                                    </p>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <span class="mc-stat-pill" style="padding: 5px 12px; font-size: 12px;">
                                        <span class="mc-stat-icon icon-blue" style="width: 24px; height: 24px; font-size: 11px;"><i class="fa fa-clock-o"></i></span>
                                        <strong>Active Tasks</strong>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="dash-placeholder-box">
                                <div class="dash-placeholder-icon" style="background: rgba(2, 132, 199, 0.12); color: #0284c7;">
                                    <i class="fa fa-tasks"></i>
                                </div>
                                <h4 class="dash-placeholder-title">Task Management Section</h4>
                                <p class="dash-placeholder-desc">
                                    No pending tasks assigned currently. New tasks will be listed here with status, priority, and deadlines.
                                </p>
                            </div>
                        </div>
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
                        <div class="well master-profile-well" style="padding: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 13px; color: #64748b; font-weight: 500;">
                                        <i class="fa fa-info-circle" style="color: #0d4d8b; margin-right: 4px;"></i>
                                        Official directives, protocols, and standard operating procedures assigned to you.
                                    </span>
                                </div>
                                <?php if (!empty($guidelinesList)): ?>
                                <div style="position: relative; width: 100%; max-width: 260px;">
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
                                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
                                            <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 260px;">
                                                <div class="guideline-card-icon">
                                                    <i class="fa fa-file-text-o"></i>
                                                </div>
                                                <div>
                                                    <h4 style="margin: 0; font-size: 14.5px; font-weight: 700; color: #0f172a; line-height: 1.35;">
                                                        <?php echo htmlspecialchars($gl['title']); ?>
                                                    </h4>
                                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 3px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
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
        e.preventDefault();
        var target = $(this).data('target');
        
        $('.dash-tab-btn').removeClass('active');
        $(this).addClass('active');
        
        $('.dash-tab-pane').removeClass('active').hide();
        $(target).addClass('active').fadeIn(150);
    });

    // Auto-activate tab from hash if present
    var hash = window.location.hash;
    if (hash && $('.dash-tab-btn[data-target="' + hash + '"]').length) {
        $('.dash-tab-btn[data-target="' + hash + '"]').click();
    } else if (hash === '#guidelines' || hash === '#pane-guidelines' || hash === '#work-guidelines') {
        $('.dash-tab-btn[data-target="#pane-guidelines"]').click();
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
});
</script>

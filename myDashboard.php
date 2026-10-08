<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "My Dashboard";
    include_once './header.php';
    include_once './dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    // Fetch logged in user details including designation_assigned & applicable_records
    $userRow = null;
    if (!empty($_SESSION['id'])) {
        $uStmt = $db->prepare("SELECT user_id, user_name, abbreviation, email_id, role, e_id, contact_no, account_status, designation_assigned, applicable_records, work_description FROM user WHERE user_id = :id LIMIT 1");
        $uStmt->execute([':id' => $_SESSION['id']]);
        $userRow = $uStmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$userRow && !empty($_SESSION['username'])) {
        $uStmt = $db->prepare("SELECT user_id, user_name, abbreviation, email_id, role, e_id, contact_no, account_status, designation_assigned, applicable_records, work_description FROM user WHERE LOWER(user_name) = :uname OR LOWER(email_id) = :uemail LIMIT 1");
        $uStmt->execute([':uname' => strtolower($_SESSION['username']), ':uemail' => strtolower($_SESSION['username'])]);
        $userRow = $uStmt->fetch(PDO::FETCH_ASSOC);
    }

    $userName = trim($userRow['user_name'] ?? ($_SESSION['username'] ?? 'User'));
    if (empty($userName)) {
        $userName = 'User';
    }

    $designationAssigned = trim((string)($userRow['designation_assigned'] ?? ''));
    $applicableRecords = trim((string)($userRow['applicable_records'] ?? ''));
    $workDescription = trim((string)($userRow['work_description'] ?? ''));
    $userEmail = trim((string)($userRow['email_id'] ?? ($_SESSION['email'] ?? '')));
    $userRole = trim((string)($userRow['role'] ?? ($_SESSION['role'] ?? 'User')));
    $userEid = trim((string)($userRow['e_id'] ?? ''));
    $userContact = trim((string)($userRow['contact_no'] ?? ''));
    $userStatus = trim((string)($userRow['account_status'] ?? 'active'));
    $userAbbr = trim((string)($userRow['abbreviation'] ?? ''));

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

    // Split records by newline for structured rendering
    $recordLines = [];
    if ($applicableRecords !== '') {
        $rawLines = preg_split('/\r\n|\r|\n/', $applicableRecords);
        foreach ($rawLines as $l) {
            $trimmed = trim($l);
            if ($trimmed !== '') {
                $recordLines[] = $trimmed;
            }
        }
    }

    // Split work description by newline for structured rendering
    $workDescLines = [];
    if ($workDescription !== '') {
        $rawWorkLines = preg_split('/\r\n|\r|\n/', $workDescription);
        foreach ($rawWorkLines as $wl) {
            $trimmedWl = trim($wl);
            if ($trimmedWl !== '') {
                $workDescLines[] = $trimmedWl;
            }
        }
    }

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

        /* Profile Sections (Applicable Records & Work Description) */
        .profile-records-container {
            padding: 22px 24px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
        }

        .profile-records-container:last-child {
            border-bottom: none;
            padding-bottom: 26px;
        }

        .records-section-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .records-section-title i {
            color: #0d4d8b;
            font-size: 15px;
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
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 11px 15px;
            transition: all 0.15s ease;
        }

        .record-clean-item:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }

        .record-clean-num {
            font-size: 12px;
            font-weight: 700;
            color: #0d4d8b;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 2px 7px;
            flex-shrink: 0;
            margin-top: 1px;
            line-height: 1.3;
        }

        .record-clean-icon {
            color: #64748b;
            font-size: 13.5px;
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
            color: #94a3b8;
            font-style: italic;
            font-size: 13px;
            padding: 6px 0;
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

        html.theme-dark .profile-records-container {
            background: var(--hpd-surface) !important;
            border-bottom-color: var(--hpd-border) !important;
        }

        html.theme-dark .records-section-title i,
        html.theme-dark .record-clean-num {
            color: var(--hpd-accent) !important;
        }

        html.theme-dark .record-clean-item {
            background: var(--hpd-surface-2) !important;
            border-color: var(--hpd-border) !important;
        }

        html.theme-dark .record-clean-item:hover {
            background: #192233 !important;
            border-color: var(--hpd-border-2) !important;
        }

        html.theme-dark .record-clean-num {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border-2) !important;
        }

        html.theme-dark .record-clean-text {
            color: var(--hpd-text) !important;
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
                </div>

                <!-- 2. Tab Content Panes -->
                <div class="dash-tab-content">
                    
                    <!-- Profile Tab Pane -->
                    <div class="dash-tab-pane active" id="pane-profile">
                        
                        <!-- MasterCreator Styled Card -->
                        <div class="qms_complete_btn">
                            <i class="fa fa-id-card-o"></i> User Profile &amp; Responsibilities
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

                            <!-- Applicable Records Section matching MasterCreator list-group -->
                            <div class="profile-records-container">
                                <div class="records-section-title">
                                    <i class="fa fa-folder-open-o"></i> Applicable Records and reporting documents with its locations:
                                </div>
                                <?php if (!empty($recordLines)): ?>
                                    <div class="records-clean-list">
                                        <?php foreach ($recordLines as $index => $line): ?>
                                            <?php $displayText = preg_replace('/^\s*\d+[\.\)\-]\s*/', '', $line); ?>
                                            <div class="record-clean-item">
                                                <span class="record-clean-num"><?php echo ($index + 1); ?></span>
                                                <i class="fa fa-file-text-o record-clean-icon"></i>
                                                <span class="record-clean-text"><?php echo htmlspecialchars($displayText); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="records-empty-placeholder">Not assigned yet</div>
                                <?php endif; ?>
                            </div>

                            <!-- Work Description / Responsibilities & Authorities Section -->
                            <div class="profile-records-container">
                                <div class="records-section-title">
                                    <i class="fa fa-list-alt"></i> Work Description (Main Responsibilities, Authorities, and Reporting by Individuals):
                                </div>
                                <?php if (!empty($workDescLines)): ?>
                                    <div class="records-clean-list">
                                        <?php foreach ($workDescLines as $index => $line): ?>
                                            <?php $displayText = preg_replace('/^\s*\d+[\.\)\-]\s*/', '', $line); ?>
                                            <div class="record-clean-item">
                                                <span class="record-clean-num"><?php echo ($index + 1); ?></span>
                                                <i class="fa fa-file-text-o record-clean-icon"></i>
                                                <span class="record-clean-text"><?php echo htmlspecialchars($displayText); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="records-empty-placeholder">Not assigned yet</div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                    <!-- 2. Attendance Tab Pane -->
                    <div class="dash-tab-pane" id="pane-attendance">
                        <div class="qms_complete_btn">
                            <i class="fa fa-calendar-check-o"></i> Attendance
                        </div>
                        <div class="well master-profile-well" style="padding: 24px;">
                            <div class="dash-placeholder-box">
                                <div class="dash-placeholder-icon">
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
                        <div class="qms_complete_btn">
                            <i class="fa fa-tasks"></i> Task Management
                        </div>
                        <div class="well master-profile-well" style="padding: 24px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                                <div>
                                    <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                                        <i class="fa fa-check-square-o" style="color: #0d4d8b; margin-right: 6px;"></i> Assigned Tasks &amp; Activities
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
                                <div class="dash-placeholder-icon">
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
                        <div class="qms_complete_btn">
                            <i class="fa fa-book"></i> Work Guidelines
                        </div>
                        <div class="well master-profile-well" style="padding: 24px;">
                            <div class="dash-placeholder-box">
                                <div class="dash-placeholder-icon">
                                    <i class="fa fa-book"></i>
                                </div>
                                <h4 class="dash-placeholder-title">Work Guidelines</h4>
                                <p class="dash-placeholder-desc">
                                    Work guidelines and procedural documentation will appear here.
                                </p>
                            </div>
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
});
</script>

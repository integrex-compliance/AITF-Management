<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->

    <?php
    $pageIdentifier = "Due letter";
    include 'header.php';
    include 'dbConnect.php';
    include_once './include/certification_cycle_helper.php';

    $excludeWithdrawn = " AND (ci.audit_status IS NULL OR ci.audit_status <> 'Withdrawn')";
    $latestCertificationService = aitfLatestCertificationServiceSubquery('ci');
    $notice = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = 'Suspension Reminder') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($notice);
    $statement->execute();
    $noticeDue = $statement->fetchAll();

    $reminder = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = 'Suspend Letter') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($reminder);
    $statement->execute();
    $reminderDue = $statement->fetchAll();

	$suspension = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = 'Withdrawal Reminder' or action = 'Withdrawal Reminder') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($suspension);
    $statement->execute();
    $suspensionDue = $statement->fetchAll();
    
    $withdrawn = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = 'Withdrawal Letter' or action = 'Withdraw Letter') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($withdrawn);
    $statement->execute();
    $withdrawnDue = $statement->fetchAll();

	$expireReminder1 = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = '1st Reminder') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($expireReminder1);
    $statement->execute();
    $expireReminderDue1 = $statement->fetchAll();
	
	$expireReminder2 = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and (action = '2nd Reminder') and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($expireReminder2);
    $statement->execute();
    $expireReminderDue2 = $statement->fetchAll();
	
    $expired = "SELECT ci.id, customer_name, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where svc.service_id = ".$latestCertificationService." and action = 'Expire Letter' and due_date <= now() and status != 'sent' and svcdt.is_deleted = 'N'".$excludeWithdrawn;
    $statement = $db->prepare($expired);
    $statement->execute();
    $expireDue = $statement->fetchAll();

    // Fetch pending Stage 1 notifications
    $stage1Query = "SELECT ci.id, ci.customer_name, ci.unique_id, svc.service_id, svc.service_type, 
                           soi.intimation_date as due_date, NULL as sent_date, 
                           'Stage 1 Notification' as action, soi.audit_stage_id as action_id, cas.ca_id
                    FROM stage_oneaudit_id soi
                    INNER JOIN service_type_ca cas ON soi.ca_service_id = cas.ca_id
                    INNER JOIN service svc ON cas.service = svc.service_id
                    INNER JOIN customer_info ci ON svc.customer_id = ci.id
                    LEFT JOIN ca_service_dates svcdt ON svc.service_id = svcdt.service_id 
                        AND svcdt.action = 'Stage 1 Notification' 
                        AND svcdt.status = 'sent' 
                        AND svcdt.is_deleted = 'N'
                    WHERE svcdt.action_id IS NULL
                      AND soi.intimation_date = CURDATE()
                      AND (ci.is_deleted IS NULL OR ci.is_deleted <> 'YES')
                      AND (cas.service_ca_status IS NULL OR cas.service_ca_status <> 'Withdrawn')";
    $statement = $db->prepare($stage1Query);
    $statement->execute();
    $stage1Due = $statement->fetchAll();

    // Fetch pending Stage 2 notifications
    $stage2Query = "SELECT ci.id, ci.customer_name, ci.unique_id, svc.service_id, svc.service_type, 
                           stai.intimation_date as due_date, NULL as sent_date, 
                           'Stage 2 Notification' as action, stai.audit_stageii_id as action_id, cas.ca_id
                    FROM stage_twoaudit_id stai
                    INNER JOIN service_type_ca cas ON stai.service_id = cas.ca_id
                    INNER JOIN service svc ON cas.service = svc.service_id
                    INNER JOIN customer_info ci ON svc.customer_id = ci.id
                    LEFT JOIN ca_service_dates svcdt ON svc.service_id = svcdt.service_id 
                        AND svcdt.action = 'Stage 2 Notification' 
                        AND svcdt.status = 'sent' 
                        AND svcdt.is_deleted = 'N'
                    WHERE svcdt.action_id IS NULL
                      AND stai.intimation_date = CURDATE()
                      AND (ci.is_deleted IS NULL OR ci.is_deleted <> 'YES')
                      AND (cas.service_ca_status IS NULL OR cas.service_ca_status <> 'Withdrawn')";
    $statement = $db->prepare($stage2Query);
    $statement->execute();
    $stage2Due = $statement->fetchAll();

    // Calculate remaining seconds for the next 12:00 PM (Noon) mail dispatch
    date_default_timezone_set('Asia/Kolkata');
    $now = time();
    $targetToday = strtotime('today 12:00:00');
    if ($now >= $targetToday) {
        $targetTime = strtotime('tomorrow 12:00:00');
        $targetDayText = "tomorrow";
    } else {
        $targetTime = $targetToday;
        $targetDayText = "today";
    }
    $remainingSeconds = $targetTime - $now;

    // Helper function to parse last cron dispatch info from log file
    if (!function_exists('get_last_dispatch_info')) {
        function get_last_dispatch_info() {
            $logPath = __DIR__ . '/test_logs/cron_intimation_due_mail_log.txt';
            $info = [
                'date' => 'N/A',
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'status' => 'No logs found'
            ];

            if (!file_exists($logPath) || !is_readable($logPath)) {
                return $info;
            }

            $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (empty($lines)) {
                return $info;
            }

            $lastEndIdx = -1;
            $noPendingFoundIdx = -1;

            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = $lines[$i];
                if (strpos($line, '=== AUTOMATED CRON JOB COMPLETED:') !== false) {
                    if ($lastEndIdx === -1) {
                        $lastEndIdx = $i;
                    }
                }
                if (strpos($line, 'No pending due letters found for today.') !== false) {
                    if ($noPendingFoundIdx === -1 && $lastEndIdx === -1) {
                        $noPendingFoundIdx = $i;
                    }
                }
            }

            if ($lastEndIdx !== -1) {
                $line = $lines[$lastEndIdx];
                if (preg_match('/^\[(.*?)\]/', $line, $matches)) {
                    $info['date'] = $matches[1];
                }
                if (preg_match('/COMPLETED:\s*(\d+)\s*succeeded,\s*(\d+)\s*failed/', $line, $matches)) {
                    $info['success'] = (int)$matches[1];
                    $info['failed'] = (int)$matches[2];
                    $info['total'] = $info['success'] + $info['failed'];
                }
                if ($info['failed'] > 0) {
                    $info['status'] = 'Failed (' . $info['failed'] . ' error' . ($info['failed'] > 1 ? 's' : '') . ')';
                } else if ($info['success'] > 0) {
                    $info['status'] = 'Success';
                } else {
                    $info['status'] = 'Completed (0 sent)';
                }
            } else if ($noPendingFoundIdx !== -1) {
                $line = $lines[$noPendingFoundIdx];
                if (preg_match('/^\[(.*?)\]/', $line, $matches)) {
                    $info['date'] = $matches[1];
                }
                $info['total'] = 0;
                $info['success'] = 0;
                $info['failed'] = 0;
                $info['status'] = 'No pending letters';
            }

            return $info;
        }
    }

    $lastDispatch = get_last_dispatch_info();

    // Fetch sent intimations list for the sidebar tab (all entries)
    $sentNoticeQuery = "SELECT ci.id, ci.customer_name, ci.email_id, ci.unique_id, svc.service_id, svc.service_type, svcdt.due_date, svcdt.sent_date, svcdt.action, svcdt.action_id FROM ca_service_dates svcdt INNER JOIN service svc ON svcdt.service_id = svc.service_id INNER JOIN customer_info ci ON svc.customer_id = ci.id WHERE svcdt.status = 'sent' AND svcdt.is_deleted = 'N' AND svcdt.due_date <= NOW() ORDER BY svcdt.sent_date DESC";
    $sentStatement = $db->prepare($sentNoticeQuery);
    $sentStatement->execute();
    $sentNoticeDue = $sentStatement->fetchAll();
    ?>

    <style>
        #loading-div-background{
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.5);
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        #loading-div{
            width: 300px;
            height: 150px;
            background-color: #fff;
            color:#1468b3;
            border: 5px solid #1468b3;
            text-align: center;
            color: #202020;
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -150px;
            margin-top: -100px;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            opacity: 1.0;
            behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */
        }

        /* =====================================================================
           PREMIUM DARK MODE  —  intimationLetter.php
           Palette mirrors homePage.php: deep charcoal surfaces, soft slate text,
           jewel-toned accents. Toggle handled globally (head.php + js/app.js).
           ===================================================================== */
        html.theme-dark {
            --hpd-bg:        #0a0e14;
            --hpd-surface:   #111722;
            --hpd-surface-2: #161d2b;
            --hpd-border:    #232c3d;
            --hpd-border-2:  #2e3a4f;
            --hpd-text:      #dbe3f0;
            --hpd-text-dim:  #93a1b8;
            --hpd-text-faint:#6b7890;
            --hpd-accent:    #4a9eff;
        }

        html.theme-dark #page-content-wrapper {
            background: var(--hpd-bg);
            color: var(--hpd-text);
        }
        html.theme-dark .page-content.inset { background: var(--hpd-bg); }

        /* Section heading labels (inline colored #053c6d) */
        html.theme-dark .panel h4[style*="#053c6d"] { color: #7fb6ef !important; }

        /* Panels / cards */
        html.theme-dark .panel {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark .panel-primary { border-color: var(--hpd-border-2); }
        html.theme-dark .panel-primary > .panel-heading,
        html.theme-dark .panel-heading[style*="#053c6d"] {
            background: linear-gradient(180deg, #10395f 0%, #0c2c49 100%) !important;
            border-color: var(--hpd-border-2);
        }
        html.theme-dark .panel-heading h4 { color: #eaf1fb !important; }

        /* Event list rows */
        html.theme-dark .panel-rightbody,
        html.theme-dark .homePagePanel { background: transparent; }
        html.theme-dark .event-row {
            border-bottom: 1px solid var(--hpd-border);
        }
        html.theme-dark .event-list li { border-color: var(--hpd-border); }
        html.theme-dark .info.showActionItem { color: var(--hpd-text); }

        /* Customer name + meta text */
        html.theme-dark .desc.text-primary,
        html.theme-dark .customerName { color: #7fb6ef !important; }
        html.theme-dark .other-info .text-muted,
        html.theme-dark small.text-muted { color: var(--hpd-text-dim) !important; }
        html.theme-dark .fa-calendar { color: var(--hpd-text-faint); }

        /* Action buttons */
        html.theme-dark .btn-info {
            background: linear-gradient(135deg, #1d63b0 0%, #2f81d6 100%);
            border-color: #2f81d6;
            color: #eaf1fb;
        }
        html.theme-dark .btn-info:hover,
        html.theme-dark .btn-info:focus {
            background: linear-gradient(135deg, #2470c4 0%, #3a8ee0 100%);
            border-color: #3a8ee0;
            color: #ffffff;
        }
        html.theme-dark .deleteLetter.btn-info {
            background: linear-gradient(135deg, #a12a2a 0%, #c23b3b 100%);
            border-color: #c23b3b;
        }

        /* Modal */
        html.theme-dark #sendConfirmModal .modal-content {
            background: var(--hpd-surface);
            border: 1px solid var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark #sendConfirmModal .modal-body p { color: var(--hpd-text) !important; }
        html.theme-dark #sendConfirmModal .modal-footer,
        html.theme-dark #sendConfirmModal .modal-header { border-color: var(--hpd-border); }
        html.theme-dark #sendConfirmModal .btn-default {
            background: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }

        /* Loading overlay */
        html.theme-dark #loading-div-background { background: rgba(4, 8, 14, 0.6); }
        html.theme-dark #loading-div {
            background-color: var(--hpd-surface);
            border-color: var(--hpd-accent);
            color: var(--hpd-text);
        }
        html.theme-dark #loading-div .meeting-loading-info { color: var(--hpd-text-dim); }

        /* Countdown Timer & Auto Cron Banner Premium Styles */
        .auto-cron-banner {
            background: linear-gradient(135deg, rgba(239, 246, 255, 0.9) 0%, rgba(219, 234, 254, 0.6) 100%);
            border-left: 5px solid #2f81d6;
            padding: 16px 22px;
            margin: 0 15px 25px 15px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.06);
            border: 1px solid rgba(191, 219, 254, 0.5);
            border-left-width: 5px;
        }

        .auto-cron-banner .cron-title {
            color: #1e40af;
            font-size: 14.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .auto-cron-banner .cron-title i {
            color: #2563eb;
            animation: pulse-glow 2s infinite ease-in-out;
        }

        .auto-cron-banner .cron-desc {
            font-size: 13.5px;
            color: #334155;
            line-height: 1.5;
            margin-top: 4px;
            display: block;
        }

        .auto-cron-banner .cron-desc strong {
            font-weight: 600;
            color: #1e293b;
        }

        /* Last Dispatch Info Styling */
        .last-dispatch-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            border-radius: 8px;
            display: inline-flex;
            flex-direction: column;
            gap: 4px;
            font-size: 11.5px;
            line-height: 1.4;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            min-width: 175px;
        }

        .last-dispatch-container .dispatch-header {
            font-weight: bold;
            color: #2563eb;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.75px;
            margin-bottom: 2px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 4px;
        }

        .last-dispatch-container div {
            color: #475569;
        }

        .last-dispatch-container strong {
            color: #64748b;
            font-weight: 500;
        }

        .last-dispatch-container span.dispatch-val {
            font-weight: 600;
            color: #1e293b;
        }

        .status-success {
            background-color: #dcfce7;
            color: #15803d;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 10.5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .status-failed {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 10.5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .status-warning {
            background-color: #fef3c7;
            color: #b45309;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 10.5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .timer-container {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 8px;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            min-height: 72px;
        }

        .timer-label-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1.2;
            text-align: center;
        }

        .timer-label-group .next-dispatch-label {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.75px;
            opacity: 0.9;
        }

        .timer-label-group .dispatch-target-text {
            font-size: 9.5px;
            opacity: 0.8;
            font-style: italic;
            margin-top: 1px;
        }

        .timer-container .dispatch-countdown {
            font-size: 18px;
            font-weight: 700;
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 1px;
            line-height: 1;
            margin-top: 2px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        /* Dark Theme Overrides */
        html.theme-dark .auto-cron-banner {
            background: linear-gradient(135deg, rgba(17, 24, 39, 0.8) 0%, rgba(21, 29, 45, 0.6) 100%) !important;
            border-left-color: var(--hpd-accent) !important;
            border-color: rgba(74, 158, 255, 0.15) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
        }

        html.theme-dark .auto-cron-banner .cron-title {
            color: var(--hpd-accent) !important;
        }

        html.theme-dark .auto-cron-banner .cron-title i {
            color: var(--hpd-accent) !important;
        }

        html.theme-dark .auto-cron-banner .cron-desc {
            color: var(--hpd-text-dim) !important;
        }

        html.theme-dark .auto-cron-banner .cron-desc strong {
            color: var(--hpd-text) !important;
        }

        html.theme-dark .last-dispatch-container {
            background: #161d2b !important;
            border-color: #2e3a4f !important;
            color: var(--hpd-text) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2) !important;
        }

        html.theme-dark .last-dispatch-container .dispatch-header {
            color: var(--hpd-accent) !important;
            border-bottom-color: #232c3d !important;
        }

        html.theme-dark .last-dispatch-container div {
            color: var(--hpd-text-dim) !important;
        }

        html.theme-dark .last-dispatch-container strong {
            color: var(--hpd-text-faint) !important;
        }

        html.theme-dark .last-dispatch-container span.dispatch-val {
            color: var(--hpd-text) !important;
        }

        html.theme-dark .status-success {
            background-color: rgba(34, 197, 94, 0.15) !important;
            color: #4ade80 !important;
        }

        html.theme-dark .status-failed {
            background-color: rgba(239, 68, 68, 0.15) !important;
            color: #f87171 !important;
        }

        html.theme-dark .status-warning {
            background-color: rgba(245, 158, 11, 0.15) !important;
            color: #fbbf24 !important;
        }

        html.theme-dark .timer-container {
            background: linear-gradient(135deg, var(--hpd-accent) 0%, #1e63b0 100%) !important;
            color: #0a0e14 !important;
            box-shadow: 0 4px 15px rgba(74, 158, 255, 0.15) !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }

        html.theme-dark .timer-label-group .next-dispatch-label {
            color: #0a0e14 !important;
        }

        html.theme-dark .timer-label-group .dispatch-target-text {
            color: #1e293b !important;
        }

        html.theme-dark .timer-container .dispatch-countdown {
            color: #0a0e14 !important;
            text-shadow: none !important;
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.08); }
        }

        /* Sent Letters Tab & Slide Panel Styles */
        .sent-intimation-tab {
            position: fixed;
            right: 0;
            top: 250px;
            z-index: 9998;
            border: 0;
            border-radius: 6px 0 0 6px;
            background: #053c6d;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 12px 10px;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.25);
            cursor: pointer;
            outline: none;
            transition: background 0.2s, padding-right 0.2s;
        }

        .sent-intimation-tab:hover {
            background: #1468b3;
            padding-right: 14px;
        }

        .sent-intimation-count {
            display: inline-block;
            min-width: 18px;
            margin-top: 7px;
            border-radius: 999px;
            background: #fff;
            color: #053c6d;
            line-height: 18px;
            text-align: center;
            writing-mode: horizontal-tb;
            font-size: 10px;
            font-weight: bold;
        }

        .sent-intimation-panel {
            position: fixed;
            top: 0;
            right: -430px;
            z-index: 9999;
            width: 420px;
            max-width: calc(100vw - 24px);
            height: 100vh;
            background: #ffffff;
            box-shadow: -10px 0 30px rgba(15, 23, 42, 0.15);
            transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .sent-intimation-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: #053c6d;
            color: #fff;
        }

        .sent-intimation-panel-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #fff !important;
        }

        .sent-intimation-close {
            border: 0;
            background: transparent;
            color: #fff;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
            outline: none;
        }

        .sent-intimation-panel-body {
            height: calc(100vh - 54px);
            overflow-y: auto;
            padding: 12px;
            background: #f8fafc;
        }

        .sent-intimation-empty {
            margin: 8px 0 0;
            padding: 12px;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
            color: #475569;
            text-align: center;
            border-radius: 6px;
        }

        .sent-intimation-card {
            border: 1px solid #e2e8f0;
            border-left: 4px solid #053c6d;
            border-radius: 6px;
            margin-bottom: 10px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .sent-intimation-card-head {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            background: #fafafa;
        }

        .sent-intimation-card-title {
            color: #1e293b;
            font-size: 13px;
            font-weight: 700;
        }

        .sent-intimation-card-meta {
            color: #64748b;
            font-size: 11px;
            margin-top: 2px;
        }

        .sent-intimation-item {
            padding: 10px 12px;
        }

        .sent-intimation-detail {
            color: #334155;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .sent-intimation-detail strong {
            color: #475569;
            font-weight: 600;
        }

        /* Dark Mode overrides */
        html.theme-dark .sent-intimation-tab {
            background: var(--hpd-accent) !important;
            color: #0a0e14 !important;
        }

        html.theme-dark .sent-intimation-count {
            background: #0a0e14 !important;
            color: var(--hpd-accent) !important;
        }

        html.theme-dark .sent-intimation-panel {
            background: var(--hpd-surface) !important;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.5) !important;
        }

        html.theme-dark .sent-intimation-panel-header {
            background: var(--hpd-surface-2) !important;
            border-bottom: 1px solid var(--hpd-border) !important;
        }

        html.theme-dark .sent-intimation-panel-body {
            background: var(--hpd-bg) !important;
        }

        html.theme-dark .sent-intimation-card {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
            border-left-color: var(--hpd-accent) !important;
        }

        html.theme-dark .sent-intimation-card-head {
            background: var(--hpd-surface-2) !important;
            border-bottom-color: var(--hpd-border) !important;
        }

        html.theme-dark .sent-intimation-card-title {
            color: var(--hpd-text) !important;
        }

        html.theme-dark .sent-intimation-card-meta {
            color: var(--hpd-text-dim) !important;
        }

        html.theme-dark .sent-intimation-detail {
            color: var(--hpd-text) !important;
        }

        html.theme-dark .sent-intimation-detail strong {
            color: var(--hpd-text-dim) !important;
        }

        html.theme-dark #sent-intimation-search-wrapper {
            background: var(--hpd-bg) !important;
            border-bottom-color: var(--hpd-border) !important;
        }
        
        html.theme-dark #sent-intimation-search,
        html.theme-dark #sent-intimation-filter-type,
        html.theme-dark #sent-intimation-filter-from,
        html.theme-dark #sent-intimation-filter-to {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
            color: var(--hpd-text) !important;
        }

        html.theme-dark .sent-intimation-panel-body .input-group-addon {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
            color: var(--hpd-text-dim) !important;
        }
    </style>

    <div id="page-content-wrapper">

        <div id="sendStatusToast" class="alert" style="display:none; position: fixed; top: 20px; right: 20px; z-index: 1051; min-width: 260px;">
        </div>

        <div class="modal fade" id="sendConfirmModal" tabindex="-1" role="dialog" aria-labelledby="sendConfirmTitle">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background:#053c6d; color: #fff;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="sendConfirmTitle">Send Intimation Letter</h4>
                    </div>
                    <div class="modal-body">
                        <p style="font-size:15px; color:#333; margin-bottom: 0;">Are you sure you want to send this letter to the client?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" id="confirmSendBtn">Yes, Send</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="sendStageConfirmModal" tabindex="-1" role="dialog" aria-labelledby="sendStageConfirmTitle">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background:#053c6d; color: #fff;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="sendStageConfirmTitle">Send Stage Audit Notification</h4>
                    </div>
                    <div class="modal-body">
                        <p style="font-size:15px; color:#333; margin-bottom: 0;">Are you sure you want to send this stage audit notification to the client?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" id="confirmSendStageBtn">Yes, Send</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="loading-div-background" >
            <div id="loading-div" class="ui-corner-all text-center">
                <h1><i class="fa fa-spinner fa-spin" ></i></h1>
                <h4 class="meeting-loading-info"> Please wait...</h4>
            </div>
        </div>

        <div class="page-content inset">
            <div class="container" >
                <br>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary" >
                            <h4 style="color: #053c6d; text-align: center; margin-bottom: 10px;">Intimation Due</h4>
                            <div class="auto-cron-banner">
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                    <div style="flex: 1; min-width: 280px;">
                                        <span class="cron-title"><i class="fa fa-clock-o"></i> Automated Mailling Script Enabled:</span> 
                                        <span class="cron-desc">Due letters will be emailed automatically to clients at 12:00 PM on their due date. If automatic delivery fails due to any technical issue, Admins will be notified and can send the email manually.</span>
                                    </div>
                                    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
                                        <div class="last-dispatch-container">
                                            <div class="dispatch-header">Last Dispatch Info</div>
                                            <div><strong>Date:</strong> <span class="dispatch-val"><?php echo $lastDispatch['date'] !== 'N/A' ? date("d M Y h:i A", strtotime($lastDispatch['date'])) : 'N/A'; ?></span></div>
                                            <div><strong>Mails Sent:</strong> <span class="dispatch-val" style="color: #2563eb;"><?php echo htmlspecialchars($lastDispatch['total'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                                            <div><strong>Status:</strong> 
                                                <?php if ($lastDispatch['status'] === 'Success'): ?>
                                                    <span class="status-success"><i class="fa fa-check-circle"></i> Success</span>
                                                <?php elseif (strpos($lastDispatch['status'], 'Failed') !== false): ?>
                                                    <span class="status-failed"><i class="fa fa-times-circle"></i> <?php echo htmlspecialchars($lastDispatch['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php else: ?>
                                                    <span class="status-warning"><i class="fa fa-info-circle"></i> <?php echo htmlspecialchars($lastDispatch['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="timer-container">
                                            <div class="timer-label-group">
                                                <span class="next-dispatch-label">Next Dispatch In</span>
                                                <span id="dispatch-target-text" class="dispatch-target-text">(<?php echo $targetDayText; ?> at 12:00 PM)</span>
                                            </div>
                                            <div id="dispatch-countdown" class="dispatch-countdown">
                                                --:--:--
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Stage 1 and Stage 2 Notifications Row -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-primary" style="min-height: 140px" >
                            <div class="panel-heading text-center" style="background: #053c6d">
                                <h4 style="color: white">Stage 1 Notification</h4>
                            </div>
                            <?php foreach ($stage1Due as $notiInfo) { ?>
                                <div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
                                    <div class="row event-row" id="dummyActionItemRow">
                                        <div class="col-xs-12 col-sm-12">
                                            <ul class="event-list">
                                                <li>
                                                    <div class="info showActionItem">
                                                        <a class="PreviewStagePDF btn btn-default btn-xs btn-info" title="PDF" data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['ca_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" data-servicetype="<?php echo $notiInfo["service_type"] ?>" style="float: right;">PDF <i class="fa fa-file-pdf-o"></i></a>
                                                        <a class="SendStageMail btn btn-default btn-xs btn-info" title="Send" data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['ca_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" data-stage="stage1" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
                                                        <p class="desc text-primary customerName" data-id="<?php echo $notiInfo['id'] ?>" title="Action item description" > <?php echo htmlspecialchars($notiInfo['customer_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <p class="other-info">
                                                            <small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($notiInfo['due_date']) ?></small>
                                                        </p>
                                                    </div>                                                
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="col-md-6 ">
                        <div class="panel panel-primary" style="min-height: 140px" >
                            <div class="panel-heading text-center" style="background: #053c6d">
                                <h4 style="color: white">Stage 2 Notification</h4>
                            </div>
                            <?php foreach ($stage2Due as $notiInfo) { ?>
                                <div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
                                    <div class="row event-row" id="dummyActionItemRow">
                                        <div class="col-xs-12 col-sm-12">
                                            <ul class="event-list">
                                                <li>
                                                    <div class="info showActionItem">
                                                        <a class="PreviewStagePDF btn btn-default btn-xs btn-info" title="PDF" data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['ca_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" data-servicetype="<?php echo $notiInfo["service_type"] ?>" style="float: right;">PDF <i class="fa fa-file-pdf-o"></i></a>
                                                        <a class="SendStageMail btn btn-default btn-xs btn-info" title="Send" data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['ca_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" data-stage="stage2" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
                                                        <p class="desc text-primary customerName" data-id="<?php echo $notiInfo['id'] ?>" title="Action item description" > <?php echo htmlspecialchars($notiInfo['customer_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <p class="other-info">
                                                            <small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($notiInfo['due_date']) ?></small>
                                                        </p>
                                                    </div>                                                
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>                        
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-primary" style="min-height: 140px" >
                            <div class="panel-heading text-center" style="background: #053c6d">
                                <h4 style="color: white">Suspention Reminder</h4>
                            </div>
                            <?php foreach ($noticeDue as $notiInfo) { ?>
                                <div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
                                    <div class="row event-row" id="dummyActionItemRow">
                                        <div class="col-xs-12 col-sm-12">
                                            <ul class="event-list">
                                                <li>
                                                    <div class="info showActionItem">
                                                        <a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $notiInfo['action'] ?>' data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['service_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" data-servicetype="<?php echo $notiInfo["service_type"] ?>" style="float: right;">PDF <i class="fa fa-file-pdf-o"></i></a>
                                                        <a class="SendMailToCust btn btn-default btn-xs btn-info" title="Send" data-letterType='<?php echo $notiInfo['action'] ?>' data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['service_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" data-servicetype="<?php echo $notiInfo["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
                                                        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
                                                            <a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['service_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
                                                        <?php } ?>
                                                        <p class="desc text-primary customerName" data-id="<?php echo $notiInfo['id'] ?>" title="Action item description" > <?php echo $notiInfo['customer_name'] ?></p>
                                                        <p class="other-info">
                                                            <small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($notiInfo['due_date']) ?></small>
                                                        </p>
                                                    </div>                                                
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="col-md-6 ">
                        <div class="panel panel-primary" style="min-height: 140px" >
                            <div class="panel-heading text-center" style="background: #053c6d">
                                <h4 style="color: white">Suspend Letter</h4>
                            </div>
                            <?php foreach ($reminderDue as $reminderInfo) { ?>
                                <div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
                                    <div class="row event-row" id="dummyActionItemRow">
                                        <div class="col-xs-12 col-sm-12">
                                            <ul class="event-list">
                                                <li>
                                                    <div class="info showActionItem">
                                                        <a class="PreviewPDF btn btn-default btn-xs btn-info"     title="Generate" data-letterType='<?php echo $reminderInfo['action'] ?>' data-customer='<?php echo $reminderInfo['id'] ?>' data-service='<?php echo $reminderInfo['service_id'] ?>' data-action ="<?php echo $reminderInfo['action_id'] ?>" data-servicetype="<?php echo $reminderInfo["service_type"] ?>" style="float: right; ">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
                                                        <a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $reminderInfo['action'] ?>' data-customer='<?php echo $reminderInfo['id'] ?>' data-service='<?php echo $reminderInfo['service_id'] ?>' data-action ="<?php echo $reminderInfo['action_id'] ?>" data-servicetype="<?php echo $reminderInfo["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
                                                        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
                                                            <a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $reminderInfo['id'] ?>' data-service='<?php echo $reminderInfo['service_id'] ?>' data-action ="<?php echo $reminderInfo['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
                                                        <?php } ?>
                                                        <p class="desc text-primary customerName" data-id="<?php echo $reminderInfo['id'] ?>" title="Action item description" > <?php echo $reminderInfo['customer_name'] ?></p>
                                                        <p class="other-info">
                                                            <small title="Notice due date"  id="tourActionItemDate" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($reminderInfo['due_date']) ?></small>
                                                        </p>
                                                    </div>                                                
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>                        
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 ">
                        <div class="panel panel-primary" style="min-height: 140px" >
                            <div class="panel-heading text-center" style="background: #053c6d">
                                <h4 style="color: white">Withdrawal Reminder</h4>
                            </div>
                            <?php foreach ($suspensionDue as $suspensionInfo) { ?>
                                <div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
                                    <div class="row event-row" id="dummyActionItemRow">
                                        <div class="col-xs-12 col-sm-12">
                                            <ul class="event-list">
                                                <li>
                                                    <div class="info showActionItem">
                                                        <a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate"     data-letterType='<?php echo $suspensionInfo['action'] ?>' data-action ="<?php echo $suspensionInfo['action_id'] ?>" data-customer='<?php echo $suspensionInfo['id'] ?>' data-service='<?php echo $suspensionInfo['service_id'] ?>' data-servicetype="<?php echo $suspensionInfo["service_type"] ?>" data-servicetype="<?php echo $suspensionInfo["service_type"] ?>" style="float: right">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
                                                        <a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $suspensionInfo['action'] ?>' data-customer='<?php echo $suspensionInfo['id'] ?>' data-service='<?php echo $suspensionInfo['service_id'] ?>' data-action ="<?php echo $suspensionInfo['action_id'] ?>" data-servicetype="<?php echo $suspensionInfo["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
                                                        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
                                                            <a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $suspensionInfo['id'] ?>' data-service='<?php echo $suspensionInfo['service_id'] ?>' data-action ="<?php echo $suspensionInfo['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
                                                        <?php } ?>
                                                        <p class="desc text-primary customerName" data-id="<?php echo $suspensionInfo['id'] ?>" title="Action item description" > <?php echo $suspensionInfo['customer_name'] ?></p>
                                                        <p class="other-info">
                                                            <small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($suspensionInfo['due_date']) ?></small>
                                                        </p>
                                                    </div>                                                
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>                        
                    </div>
                    <div class="col-md-6 ">
                        <div class="panel panel-primary" style="min-height: 140px" >
                            <div class="panel-heading text-center" style="background: #053c6d">
                                <h4 style="color: white">Withdrawn Letter</h4>
                            </div>
                            <?php foreach ($withdrawnDue as $withdrawnInfo) { ?>
                                <div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
                                    <div class="row event-row" id="dummyActionItemRow">
                                        <div class="col-xs-12 col-sm-12">
                                            <ul class="event-list">
                                                <li>
                                                    <div class="info showActionItem">
                                                        <a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $withdrawnInfo['action'] ?>' data-action ="<?php echo $withdrawnInfo['action_id'] ?>" data-customer='<?php echo $withdrawnInfo['id'] ?>' data-service='<?php echo $withdrawnInfo['service_id'] ?>' data-servicetype="<?php echo $withdrawnInfo["service_type"] ?>" style="float: right">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
                                                        <a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $withdrawnInfo['action'] ?>' data-customer='<?php echo $withdrawnInfo['id'] ?>' data-service='<?php echo $withdrawnInfo['service_id'] ?>' data-action ="<?php echo $withdrawnInfo['action_id'] ?>" data-servicetype="<?php echo $withdrawnInfo["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
                                                        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
                                                            <a class="deleteLetter btn btn-default btn-xs btn-info" title="Delete" data-customer='<?php echo $withdrawnInfo['id'] ?>' data-service='<?php echo $withdrawnInfo['service_id'] ?>' data-action ="<?php echo $withdrawnInfo['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
                                                        <?php } ?>
                                                        <p class="desc text-primary customerName" data-id="<?php echo $withdrawnInfo['id'] ?>" title="Action item description" > <?php echo $withdrawnInfo['customer_name'] ?></p>
                                                        <p class="other-info">
                                                            <small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($withdrawnInfo['due_date']) ?></small>
                                                        </p>
                                                    </div>                                                
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>                        
                    </div>
                </div>
			<div class="row">
				<div class="col-md-12">
					<div class="panel panel-primary" >
						<h4 style="color: #053c6d; text-align: center">For RC</h4>
					</div>
				</div>
			</div>
            <div class="row">
				<div class="col-md-6 ">
					<div class="panel panel-primary" style="min-height: 140px" >
						<div class="panel-heading text-center" style="background: #053c6d">
							<h4 style="color: white">1st Reminder</h4>
						</div>
						<?php foreach ($expireReminderDue1 as $expireReminderInfo1) { ?>
							<div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
								<div class="row event-row" id="dummyActionItemRow">
									<div class="col-xs-12 col-sm-12">
										<ul class="event-list">
											<li>
												<div class="info showActionItem">
													<a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireReminderInfo1['action'] ?>' data-action ="<?php echo $expireReminderInfo1['action_id'] ?>" data-customer='<?php echo $expireReminderInfo1['id'] ?>' data-service='<?php echo $expireReminderInfo1['service_id'] ?>' data-servicetype="<?php echo $expireReminderInfo1["service_type"] ?>" data-servicetype="<?php echo $expireReminderInfo1["service_type"] ?>" style="float: right">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
													<a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireReminderInfo1['action'] ?>' data-customer='<?php echo $expireReminderInfo1['id'] ?>' data-service='<?php echo $expireReminderInfo1['service_id'] ?>' data-action ="<?php echo $expireReminderInfo1['action_id'] ?>" data-servicetype="<?php echo $expireReminderInfo1["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
													<?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
														<a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $expireReminderInfo1['id'] ?>' data-service='<?php echo $expireReminderInfo1['service_id'] ?>' data-action ="<?php echo $expireReminderInfo1['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
													<?php } ?>
													<p class="desc text-primary customerName" data-id="<?php echo $expireReminderInfo1['id'] ?>" title="Action item description" > <?php echo $expireReminderInfo1['customer_name'] ?></p>
													<p class="other-info">
														<small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($expireReminderInfo1['due_date']) ?></small>
													</p>
												</div>                                                
											</li>
										</ul>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
					<div class="panel panel-primary" style="min-height: 140px" >
						<div class="panel-heading text-center" style="background: #053c6d">
							<h4 style="color: white">2nd Reminder</h4>
						</div>
						<?php foreach ($expireReminderDue2 as $expireReminderInfo2) { ?>
							<div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
								<div class="row event-row" id="dummyActionItemRow">
									<div class="col-xs-12 col-sm-12">
										<ul class="event-list">
											<li>
												<div class="info showActionItem">
													<a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireReminderInfo2['action'] ?>' data-action ="<?php echo $expireReminderInfo2['action_id'] ?>" data-customer='<?php echo $expireReminderInfo2['id'] ?>' data-service='<?php echo $expireReminderInfo2['service_id'] ?>' data-servicetype="<?php echo $expireReminderInfo2["service_type"] ?>" data-servicetype="<?php echo $expireReminderInfo2["service_type"] ?>" style="float: right">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
													<a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireReminderInfo2['action'] ?>' data-customer='<?php echo $expireReminderInfo2['id'] ?>' data-service='<?php echo $expireReminderInfo2['service_id'] ?>' data-action ="<?php echo $expireReminderInfo2['action_id'] ?>" data-servicetype="<?php echo $expireReminderInfo2["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
													<?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
														<a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $expireReminderInfo2['id'] ?>' data-service='<?php echo $expireReminderInfo2['service_id'] ?>' data-action ="<?php echo $expireReminderInfo2['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
													<?php } ?>
													<p class="desc text-primary customerName" data-id="<?php echo $expireReminderInfo2['id'] ?>" title="Action item description" > <?php echo $expireReminderInfo2['customer_name'] ?></p>
													<p class="other-info">
														<small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($expireReminderInfo2['due_date']) ?></small>
													</p>
												</div>                                                
											</li>
										</ul>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="col-md-6 ">
					<div class="panel panel-primary" style="min-height: 140px" >
						<div class="panel-heading text-center" style="background: #053c6d">
							<h4 style="color: white">Expire </h4>
						</div>
						<?php foreach ($expireDue as $expireInfo) { ?>
							<div class="panel-rightbody homePagePanel" id="actionItemDisplayPanel">
								<div class="row event-row" id="dummyActionItemRow">
									<div class="col-xs-12 col-sm-12">
										<ul class="event-list">
											<li>
												<div class="info showActionItem">
													<a class="PreviewPDF btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireInfo['action'] ?>' data-action ="<?php echo $expireInfo['action_id'] ?>" data-customer='<?php echo $expireInfo['id'] ?>' data-service='<?php echo $expireInfo['service_id'] ?>' data-servicetype="<?php echo $expireInfo["service_type"] ?>" data-servicetype="<?php echo $expireInfo["service_type"] ?>" style="float: right">PDF <i class="fa fa-file-pdf-o"></i></a>                                                        
													<a class="SendMailToCust btn btn-default btn-xs btn-info" title="Generate" data-letterType='<?php echo $expireInfo['action'] ?>' data-customer='<?php echo $expireInfo['id'] ?>' data-service='<?php echo $expireInfo['service_id'] ?>' data-action ="<?php echo $expireInfo['action_id'] ?>" data-servicetype="<?php echo $expireInfo["service_type"] ?>" style="float: right; margin-right: 10px">Generate <i class="fa fa-envelope-o"></i></a>
													<?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
														<a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $expireInfo['id'] ?>' data-service='<?php echo $expireInfo['service_id'] ?>' data-action ="<?php echo $expireInfo['action_id'] ?>" style="float: right; margin-right: 10px">Delete <i class="fa fa-trash"></i></a>
													<?php } ?>
													<p class="desc text-primary customerName" data-id="<?php echo $expireInfo['id'] ?>" title="Action item description" > <?php echo $expireInfo['customer_name'] ?></p>
													<p class="other-info">
														<small title="Notice due date" class="text-muted"><i style="padding: 3px" class="fa fa-calendar"></i><?php echo simpleDateConversion($expireInfo['due_date']) ?></small>
													</p>
												</div>                                                
											</li>
										</ul>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
            </div>
            </div>
        </div> 
    </div>

    <!-- Sent Letters Sliding Sidebar Panel -->
    <button type="button" class="sent-intimation-tab" id="sentIntimationTab" title="View Sent Letters">
        Sent Letters
        <span class="sent-intimation-count"><?php echo count($sentNoticeDue); ?></span>
    </button>

    <div class="sent-intimation-panel" id="sentIntimationPanel" aria-hidden="true">
        <div class="sent-intimation-panel-header">
            <h4 class="sent-intimation-panel-title">Sent Letters</h4>
            <button type="button" class="sent-intimation-close" id="sentIntimationClose" aria-label="Close">&times;</button>
        </div>
        <div class="sent-intimation-panel-body">
            <div id="sent-intimation-search-wrapper" style="margin-bottom: 12px; position: sticky; top: 0; z-index: 10; background: #f8fafc; padding: 2px 0 10px 0; border-bottom: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 8px;">
                <!-- Search Input -->
                <div class="input-group">
                    <span class="input-group-addon" style="background: #fff; border-right: 0;"><i class="fa fa-search"></i></span>
                    <input type="text" id="sent-intimation-search" class="form-control" placeholder="Search Client, ID..." style="border-left: 0; box-shadow: none; height: 34px;">
                </div>
                
                <!-- Type Selection -->
                <div>
                    <select id="sent-intimation-filter-type" class="form-control" style="height: 34px; box-shadow: none;">
                        <option value="">Select Letter Type</option>
                        <option value="Stage 1 Notification">Stage 1 Notification</option>
                        <option value="Stage 2 Notification">Stage 2 Notification</option>
                        <option value="1st Reminder">1st Reminder</option>
                        <option value="2nd Reminder">2nd Reminder</option>
                        <option value="Suspension Reminder">Suspension Reminder</option>
                        <option value="Suspend Letter">Suspend Letter</option>
                        <option value="Suspension Letter">Suspension Letter</option>
                        <option value="Withdrawal Reminder">Withdrawal Reminder</option>
                        <option value="Withdraw Letter">Withdraw Letter</option>
                        <option value="Withdrawal Letter">Withdrawal Letter</option>
                        <option value="Expire Letter">Expire Letter</option>
                    </select>
                </div>

                <!-- Date Range Selection -->
                <div style="display: flex; gap: 8px;">
                    <div style="flex: 1; position: relative;">
                        <input type="text" id="sent-intimation-filter-from" class="form-control" placeholder="Sent Date From" style="height: 34px; background: #fff; box-shadow: none;" autocomplete="off">
                    </div>
                    <div style="flex: 1; position: relative;">
                        <input type="text" id="sent-intimation-filter-to" class="form-control" placeholder="Sent Date To" style="height: 34px; background: #fff; box-shadow: none;" autocomplete="off">
                    </div>
                </div>
            </div>
            <?php if (empty($sentNoticeDue)) { ?>
                <div class="sent-intimation-empty">No sent letters found.</div>
            <?php } else { ?>
                <?php foreach ($sentNoticeDue as $notiInfo) { 
                    $url = "";
                    if ($notiInfo['action'] == "Stage 1 Notification") {
                        $url = "AuditNotificationandAssignment.php";
                    } else if ($notiInfo['action'] == "Stage 2 Notification") {
                        $url = "AuditNotificationStage2.php";
                    } else if ($notiInfo['action'] == "1st Reminder" && $notiInfo["service_type"] == "SA-II") {
                        $url = "firstReminder.php";
                    } else if ($notiInfo['action'] == "1st Reminder"){
                        $url = "firstDue.php";
                    } else if ($notiInfo['action'] == "2nd Reminder" && $notiInfo["service_type"] == "SA-II") {
                        $url = "secondReminder.php";
                    } else if ($notiInfo['action'] == "2nd Reminder") {
                        $url = "reminder.php";
                    } else if ($notiInfo['action'] == "Suspension Reminder") {
                        $url = "suspension.php";
                    } else if ($notiInfo['action'] == "Suspend Letter" || $notiInfo['action'] == "Suspension Letter") {
                        $url = "suspension.php";
                    } else if ($notiInfo['action'] == "Withdrawal Reminder") {
                        $url = "withdrawRemind.php";
                    } else if ($notiInfo['action'] == "Withdrawal Letter" || $notiInfo['action'] == "Withdraw Letter") {
                        $url = "withdrawn.php";
                    } else if ($notiInfo['action'] == "Expire Letter") {
                        $url = "saiiWithdrawn.php";
                    }

                    if ($notiInfo['service_type'] == "CA" || $notiInfo['service_type'] == "RC") {
                        $nextSVC = "SA-I";
                    } else if ($notiInfo['service_type'] == "SA-I") {
                        $nextSVC = "SA-II";
                    } else if ($notiInfo['service_type'] == "SA-II") {
                        $nextSVC = "RC";
                    } else {
                        $nextSVC = $notiInfo['service_type'];
                    }
                ?>
                    <div class="sent-intimation-card">
                        <div class="sent-intimation-card-head">
                            <div class="sent-intimation-card-title">
                                <?php echo htmlspecialchars($notiInfo['customer_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="sent-intimation-card-meta">
                                ID: <?php echo htmlspecialchars($notiInfo['unique_id'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                        <div class="sent-intimation-card-body-content">
                            <div class="sent-intimation-item">
                                <div class="sent-intimation-detail">
                                    <strong>Letter Type:</strong> <span class="card-letter-type"><?php echo htmlspecialchars($notiInfo['action'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="sent-intimation-detail">
                                    <strong>Service:</strong> <?php echo htmlspecialchars($nextSVC, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="sent-intimation-detail">
                                    <strong>Sent Date:</strong> <span class="card-sent-date"><?php echo simpleDateConversion($notiInfo['sent_date']); ?></span>
                                </div>
                                <div class="sent-intimation-detail">
                                    <strong>Mailed To:</strong> <span style="word-break: break-all;"><?php echo htmlspecialchars($notiInfo['email_id'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div style="margin-top: 8px; overflow: hidden;">
                                    <a class="sentNotificationGenerate btn btn-default btn-xs btn-info" title="PDF Preview" 
                                       data-customer='<?php echo $notiInfo['id'] ?>' 
                                       data-service='<?php echo $notiInfo['service_id'] ?>' 
                                       data-action ="<?php echo $notiInfo['action_id'] ?>" 
                                       data-servicetype="<?php echo $notiInfo["service_type"] ?>" 
                                       data-url="<?php echo $url; ?>" 
                                       style="float: right;"><i class="fa fa-file-pdf-o"></i> View PDF</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>

<script type="text/javascript" >
    function ShowProgressAnimation() {
        $("#loading-div-background").fadeIn(150);
    }
    function HideProgressAnimation() {
        $("#loading-div-background").fadeOut(150);
    }

    function showToast(message, isSuccess) {
        var $toast = $('#sendStatusToast');
        $toast.removeClass('alert-success alert-danger');
        $toast.addClass(isSuccess ? 'alert-success' : 'alert-danger');
        $toast.html(message);
        $toast.fadeIn(200);
        setTimeout(function(){
            $toast.fadeOut(200);
        }, 3500);
    }

	function GenerateOrPreviewPDF(customer, service, action, servicetype, letterType, pageAction){
		var url = 'IntimationDueLetters.php';
		var form = $('<form action="' + url + '" method="post" target="_blank">' +
				'<input type="hidden" name="customer" value="' + customer + '" />' +
				'<input type="hidden" name="service" value="' + service + '" />' +
				'<input type="hidden" name="action" value="' + action + '" />' +
				'<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
				'<input type="hidden" name="lettertype" value="' + letterType + '" />' +
				'<input type="hidden" name="SourcePage" value="intimationLetter.php" />' +
				'<input type="hidden" name="pageAction" value="' + pageAction + '" />' +
				'</form>');
		$('body').append(form);
		form.submit();
	}
	
	$('.PreviewPDF').click(function () {
		var customer = $(this).attr('data-customer');
		var service = $(this).attr('data-service');
		var action = $(this).attr('data-action');
		var servicetype = $(this).attr("data-servicetype");
		var lettertype = $(this).attr("data-letterType");
		
		GenerateOrPreviewPDF(customer,service,action,servicetype,lettertype, "PreviewPDF");
	});
	
	$('.SendMailToCust').click(function () {
		pendingSendPayload = {
			customer: $(this).attr('data-customer'),
			service: $(this).attr('data-service'),
			action: $(this).attr('data-action'),
			servicetype: $(this).attr('data-servicetype'),
			lettertype: $(this).attr('data-letterType')
		};
		$('#sendConfirmModal').modal('show');
	});

	$('#confirmSendBtn').click(function(){
		if(!pendingSendPayload){ return; }
		$('#sendConfirmModal').modal('hide');
		ShowProgressAnimation();
		var payload = $.extend({}, pendingSendPayload, {
			SourcePage: 'intimationLetter.php',
			pageAction: 'SendMail'
		});
		$.ajax({
			url: 'IntimationDueLetters.php',
			type: 'POST',
			data: payload,
			dataType: 'json'
		}).done(function(res){
			HideProgressAnimation();
			if(res && res.success){
				showToast('Letter sent successfully.', true);
				setTimeout(function(){ location.reload(); }, 800);
			} else {
				var msg = (res && res.message) ? res.message : 'Unable to send mail. Please try again.';
				showToast(msg, false);
			}
		}).fail(function(xhr){
			HideProgressAnimation();
			var msg = 'Unable to send mail. Please try again.';
			if(xhr && xhr.responseText){
				msg = xhr.responseText;
			}
			showToast(msg, false);
		});
	});
	
	$('.SendSuspensionReminderPDF').click(function () {
		ShowProgressAnimation();
		var customer = $(this).attr('data-customer');
		var service = $(this).attr('data-service');
		var acction = $(this).attr('data-action');
		var servicetype = $(this).attr("data-servicetype");
		var url = 'TestMailSend.php';
		var form = $('<form action="' + url + '" method="post" >' +
				'<input type="hidden" name="customer" value="' + customer + '" />' +
				'<input type="hidden" name="service" value="' + service + '" />' +
				'<input type="hidden" name="acction" value="' + acction + '" />' +
				'<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
				'</form>');
		$('body').append(form);
		form.submit();
	});
	
	$('.notificationSuspensionSend').click(function () {
		ShowProgressAnimation();
		var customer = $(this).attr('data-customer');
		var service = $(this).attr('data-service');
		var acction = $(this).attr('data-action');
		var servicetype = $(this).attr("data-servicetype");
		var url = 'suspentionMail.php';
		var form = $('<form action="' + url + '" method="post" >' +
				'<input type="hidden" name="customer" value="' + customer + '" />' +
				'<input type="hidden" name="service" value="' + service + '" />' +
				'<input type="hidden" name="acction" value="' + acction + '" />' +
				'<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
				'</form>');
		$('body').append(form);
		form.submit();
	});
		
    $(document).ready(function () {
        // Sent Intimations Sliding Panel Events
        $('#sentIntimationTab').click(function () {
            $('#sentIntimationPanel').css('right', '0').attr('aria-hidden', 'false');
        });

        $('#sentIntimationClose').click(function () {
            $('#sentIntimationPanel').css('right', '-430px').attr('aria-hidden', 'true');
        });

        $(document).mouseup(function (e) {
            var container = $("#sentIntimationPanel");
            var tab = $("#sentIntimationTab");
            var datepicker = $("#ui-datepicker-div");
            
            // Check if the click target is the datepicker panel or is nested inside jQuery UI datepicker elements
            var isClickInsideDatepicker = datepicker.is(e.target) || datepicker.has(e.target).length > 0 || $(e.target).closest('.ui-datepicker').length > 0;
            
            if (!isClickInsideDatepicker && !container.is(e.target) && container.has(e.target).length === 0 && !tab.is(e.target) && tab.has(e.target).length === 0) {
                container.css('right', '-430px').attr('aria-hidden', 'true');
            }
        });

        $('.sentNotificationGenerate').click(function () {
            var customer = $(this).attr('data-customer');
            var service = $(this).attr('data-service');
            var action = $(this).attr('data-action');
            var servicetype = $(this).attr("data-servicetype");
            var url = $(this).attr("data-url");
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                    '<input type="hidden" name="customer" value="' + customer + '" />' +
                    '<input type="hidden" name="customerid" value="' + customer + '" />' +
                    '<input type="hidden" name="service" value="' + service + '" />' +
                    '<input type="hidden" name="serviceid" value="' + service + '" />' +
                    '<input type="hidden" name="action" value="' + action + '" />' +
                    '<input type="hidden" name="auditid" value="' + action + '" />' +
                    '<input type="hidden" name="notificationid" value="' + action + '" />' +
                    '<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        // Combined filter function for Sent Letters
        function filterSentIntimations() {
            var searchText = $('#sent-intimation-search').val().toLowerCase();
            var letterType = $('#sent-intimation-filter-type').val().toLowerCase();
            var fromDateVal = $('#sent-intimation-filter-from').val();
            var toDateVal = $('#sent-intimation-filter-to').val();

            var fromDate = null;
            if (fromDateVal) {
                var parts = fromDateVal.split('.');
                fromDate = new Date(parts[2], parts[1] - 1, parts[0]);
            }

            var toDate = null;
            if (toDateVal) {
                var parts = toDateVal.split('.');
                toDate = new Date(parts[2], parts[1] - 1, parts[0]);
            }

            $('.sent-intimation-card').each(function() {
                var card = $(this);
                var cardText = card.text().toLowerCase();
                var cardLetterType = card.find('.card-letter-type').text().trim().toLowerCase();
                var cardSentDateVal = card.find('.card-sent-date').text().trim();
                
                var cardSentDate = null;
                if (cardSentDateVal) {
                    var parts = cardSentDateVal.split('.');
                    cardSentDate = new Date(parts[2], parts[1] - 1, parts[0]);
                }

                var matchesText = cardText.indexOf(searchText) > -1;

                var matchesLetterType = true;
                if (letterType) {
                    matchesLetterType = (cardLetterType === letterType);
                }

                var matchesDate = true;
                if (cardSentDate) {
                    if (fromDate && cardSentDate < fromDate) {
                        matchesDate = false;
                    }
                    if (toDate && cardSentDate > toDate) {
                        matchesDate = false;
                    }
                } else if (fromDate || toDate) {
                    matchesDate = false;
                }

                card.toggle(matchesText && matchesLetterType && matchesDate);
            });
        }

        // Bind filter events
        $('#sent-intimation-search').on('keyup', filterSentIntimations);
        $('#sent-intimation-filter-type').on('change', filterSentIntimations);
        
        // Initialize datepickers
        $('#sent-intimation-filter-from, #sent-intimation-filter-to').datepicker({
            dateFormat: 'dd.mm.yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function() {
                filterSentIntimations();
            }
        });

        // Trigger filter on date manual input or keyup
        $('#sent-intimation-filter-from, #sent-intimation-filter-to').on('change keyup', filterSentIntimations);

        // Countdown timer for automatic mail dispatch
        (function() {
            const initialRemainingSeconds = <?php echo isset($remainingSeconds) ? $remainingSeconds : 0; ?>;
            const initialTargetText = "<?php echo isset($targetDayText) ? $targetDayText : 'today'; ?>";
            const clientStartTime = Date.now();

            function updateCountdown() {
                const elapsedSeconds = Math.floor((Date.now() - clientStartTime) / 1000);
                let currentRemaining = initialRemainingSeconds - elapsedSeconds;
                let targetText = initialTargetText;
                
                if (currentRemaining <= 0) {
                    targetText = "tomorrow";
                    while (currentRemaining <= 0) {
                        currentRemaining += 86400; // 24 hours
                    }
                }
                
                const hours = Math.floor(currentRemaining / 3600);
                const minutes = Math.floor((currentRemaining % 3600) / 60);
                const seconds = currentRemaining % 60;
                
                const formattedTime = 
                    String(hours).padStart(2, '0') + ':' + 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
                    
                $('#dispatch-countdown').text(formattedTime);
                $('#dispatch-target-text').text('(' + targetText + ' at 12:00 PM)');
            }

            if (initialRemainingSeconds > 0) {
                updateCountdown();
                setInterval(updateCountdown, 1000);
            }
        })();

        $(".deleteLetter").click(function () {
            var rowid = $(this).attr("data-action");
            var url = 'addComments.php'
            var form = $('<form action="' + url + '" method="post">' +
                    '<input type="hidden" name="rowid" value="' + rowid + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
            table.draw();
        });

        $('.customerName').click(function () {
            var customerID = $(this).attr('data-id');
//            alert(customerID);
            // open popup
            localStorage['visited'] = customerID;
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                    '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });
        
        $('.notificationReminderSend').click(function () {
            ShowProgressAnimation();
            var customer = $(this).attr('data-customer');
            var service = $(this).attr('data-service');
            var acction = $(this).attr('data-action');
            var servicetype = $(this).attr("data-servicetype");
            if (servicetype == "SA-II") {
                var url = 'sendReminderDueNotification.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="customer" value="' + customer + '" />' +
                        '<input type="hidden" name="service" value="' + service + '" />' +
                        '<input type="hidden" name="action" value="' + acction + '" />' +
                        '<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            } else {
                var url = 'suspensionDueNotificationSend.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="customer" value="' + customer + '" />' +
                        '<input type="hidden" name="service" value="' + service + '" />' +
                        '<input type="hidden" name="acction" value="' + acction + '" />' +
                        '<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            }
        });

        $('.notificationWithdrawnSend').click(function () {
            ShowProgressAnimation();
            var customer = $(this).attr('data-customer');
            var service = $(this).attr('data-service');
            var acction = $(this).attr('data-action');
            var servicetype = $(this).attr("data-servicetype");
            if (servicetype == "SA-II") {
                var url = 'sendWithdrawnDueSaiiNotification.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="customer" value="' + customer + '" />' +
                        '<input type="hidden" name="service" value="' + service + '" />' +
                        '<input type="hidden" name="acction" value="' + acction + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            } else {
                var url = 'sendWithdrawnDueNotification.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="customer" value="' + customer + '" />' +
                        '<input type="hidden" name="service" value="' + service + '" />' +
                        '<input type="hidden" name="acction" value="' + acction + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            }
        });
        
        $('.expireGenerateSend').click(function () {
            ShowProgressAnimation();
            var customer = $(this).attr('data-customer');
            var service = $(this).attr('data-service');
            var acction = $(this).attr('data-action');
            var servicetype = $(this).attr("data-servicetype");
                var url = 'sendWithdrawnDueSaiiNotification.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="customer" value="' + customer + '" />' +
                        '<input type="hidden" name="service" value="' + service + '" />' +
                        '<input type="hidden" name="acction" value="' + acction + '" />' +
                        '<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            
        });

        // Stage 1 & Stage 2 Notification Handlers
        var pendingStagePayload = null;
        
        $('.SendStageMail').click(function () {
            pendingStagePayload = {
                customer: $(this).attr('data-customer'),
                service: $(this).attr('data-service'),
                audit: $(this).attr('data-action'),
                stage: $(this).attr('data-stage')
            };
            $('#sendStageConfirmModal').modal('show');
        });

        $('#confirmSendStageBtn').click(function(){
            if(!pendingStagePayload){ return; }
            $('#sendStageConfirmModal').modal('hide');
            ShowProgressAnimation();
            
            $.ajax({
                url: 'sendStageNotificationMail.php',
                type: 'POST',
                data: pendingStagePayload,
                dataType: 'json'
            }).done(function(res){
                HideProgressAnimation();
                if(res && res.success){
                    showToast(res.message || 'Stage notification sent successfully.', true);
                    setTimeout(function(){ location.reload(); }, 1000);
                } else {
                    var msg = (res && res.message) ? res.message : 'Unable to send stage notification. Please try again.';
                    showToast(msg, false);
                }
            }).fail(function(xhr){
                HideProgressAnimation();
                var msg = 'Unable to send stage notification. Please try again.';
                if(xhr && xhr.responseText){
                    try {
                        var parsed = JSON.parse(xhr.responseText);
                        if (parsed && parsed.message) msg = parsed.message;
                    } catch(e) {}
                }
                showToast(msg, false);
            });
        });

        $('.PreviewStagePDF').click(function () {
            var customer = $(this).attr('data-customer');
            var service = $(this).attr('data-service');
            var action = $(this).attr('data-action');
            var stage = $(this).siblings('.SendStageMail').attr('data-stage');
            
            var url, form;
            if (stage === 'stage1') {
                url = 'AuditNotificationandAssignment.php';
                form = $('<form action="' + url + '" method="post" target="_blank">' +
                        '<input type="hidden" name="auditid" value="' + action + '" />' +
                        '<input type="hidden" name="customerid" value="' + customer + '" />' +
                        '<input type="hidden" name="serviceid" value="' + service + '" />' +
                        '</form>');
            } else {
                url = 'AuditNotificationStage2.php';
                form = $('<form action="' + url + '" method="post" target="_blank">' +
                        '<input type="hidden" name="notificationid" value="' + action + '" />' +
                        '</form>');
            }
            $('body').append(form);
            form.submit();
        });

    });
</script>

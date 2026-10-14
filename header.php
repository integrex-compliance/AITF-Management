<?php
error_reporting(E_WARNING | E_PARSE);
ob_start();
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

include 'head.php';
include 'modalForms.php';
include 'masterModel.php';
include 'auditApplicationModal.php';
include 'contractReviewModal.php';
include 'auditNotificationModal.php';
include 'reviewReportModal.php';
include 'annexIIModel.php';
include 'scheduleStageModal.php';
include 'cdcModal.php';
include 'auditScheduleIIModal.php';
include 'controlFormatModal1.php';
include 'customerSurveyModal.php';
include 'controlFormatModal2.php';
include 'csaModal.php';
include 'assessmentPlanningModal.php';
include 'auditNotificationIIModal.php';
include 'certificationPrintModal.php';
include 'schedule1servelance.php';
include 'schedule2servelance.php';
include 'utility.php';
require_once __DIR__ . '/include/rbac_matrix.php';
require_once __DIR__ . '/include/customer_recycle_helper.php';
require_once __DIR__ . '/include/schema_auto_sync.php';
if (isset($db) && $db) {
    aitfEnsureRecycleSchema($db);
    aitf_auto_sync_schema($db);
}

// Automated Web-Cron URL Trigger for Daily 12:00 PM Due Letters Mailer
if (!defined('WEB_CRON_TRIGGERED') && (int)date('H') >= 12) {
    define('WEB_CRON_TRIGGERED', true);
    $cronFlagDir = __DIR__ . '/test_logs';
    if (!is_dir($cronFlagDir)) { @mkdir($cronFlagDir, 0777, true); }
    $cronFlagFile = $cronFlagDir . '/last_due_mail_cron_date.txt';
    $todayStr = date('Y-m-d');
    if (!file_exists($cronFlagFile) || trim(@file_get_contents($cronFlagFile)) !== $todayStr) {
        @file_put_contents($cronFlagFile, $todayStr);
        $autoCronUrl = 'cron_auto_send_intimation_due.php?webcron=' . time();
        echo '<script>window.addEventListener("load", function() { setTimeout(function(){ fetch("' . $autoCronUrl . '", {method:"GET", keepalive:true}).catch(function(e){}); }, 2500); });</script>';
    }
}

// Heartbeat JS to keep online status accurate and track screen time
echo '<script>
    let activeSeconds = 0;
    
    // Count active seconds every second
    setInterval(function() {
        if (document.hasFocus() || document.visibilityState === "visible") {
            activeSeconds++;
        }
    }, 1000);

    function sendHeartbeat(isActive = true) {
        let delta = activeSeconds;
        activeSeconds = 0; // Reset counter after grabbing value
        
        const data = new FormData();
        data.append("active", isActive);
        data.append("delta", delta);
        
        if (!isActive && navigator.sendBeacon) {
            navigator.sendBeacon("fetch_status.php", data);
        } else {
            fetch("fetch_status.php", {
                method: "POST", 
                body: data,
                keepalive: true
            }).catch(function(){});
        }
    }
    
    // Ping every 5 seconds
    setInterval(function() { 
        if (document.visibilityState === "visible") {
            sendHeartbeat(true); 
        }
    }, 5000);
    
    // Immediate ping when user comes back or leaves the tab
    document.addEventListener("visibilitychange", function() {
        if (document.visibilityState === "visible") {
            sendHeartbeat(true);
        } else {
            // When leaving tab, send active=false to drop offline instantly
            sendHeartbeat(false);
        }
    });

    window.addEventListener("pagehide", function() {
        sendHeartbeat(false);
    });

    window.addEventListener("beforeunload", function() {
        sendHeartbeat(false);
    });
</script>';

?>

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-ZH2QWSFTB0"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-ZH2QWSFTB0');
    </script>
    <title>AITF</title>
</head>
<style>
    /*@media print{@page {size: landscape}}*/

    /* Slightly compact styling for Application Received menu item */
    .sidebar-nav a.app-rec-nav {
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }

    /* Sidebar Section Categories & Dividers */
    .sidebar-section-divider {
        padding: 16px 10px 6px 10px !important;
        list-style: none !important;
        pointer-events: none !important;
        margin: 0 !important;
    }
    .sidebar-section-divider:first-of-type {
        padding-top: 10px !important;
    }
    .sidebar-section-divider:not(:first-of-type) {
        padding-top: 22px !important;
        margin-top: 4px !important;
    }
    .sidebar-section-divider .section-title {
        font-size: 9.5px !important;
        font-weight: 700 !important;
        letter-spacing: 1.2px !important;
        color: #93c5fd !important;
        text-transform: uppercase !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        opacity: 0.9;
    }
    .sidebar-section-divider .section-title::after {
        content: '' !important;
        flex: 1 !important;
        height: 1px !important;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.22) 0%, rgba(255, 255, 255, 0.02) 100%) !important;
    }
    #wrapper:not(.active) .sidebar-section-divider {
        padding: 4px 6px !important;
        margin: 6px 6px !important;
        border-top: 1px solid rgba(255, 255, 255, 0.12) !important;
    }
    #wrapper:not(.active) .sidebar-section-divider .section-title {
        display: none !important;
    }

    /* Global Sidebar Link Styling & Spacing - Enhanced Readability */
    #sidebar_menu li a,
    .sidebar-nav li a {
        padding: 10.5px 8.5px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        letter-spacing: 0.2px !important;
        white-space: nowrap !important;
    }
    .sidebar-nav li a .dropdown-group-text,
    .sidebar-nav li a .mgmt-group-text {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        letter-spacing: 0.15px !important;
        display: inline-flex !important;
        align-items: center !important;
    }
    .sidebar-nav li a .dropdown-group-text i,
    .sidebar-nav li a .mgmt-group-text i {
        font-size: 12.5px !important;
        width: 15px !important;
        text-align: center !important;
        margin-right: 6px !important;
        display: inline-block !important;
        flex-shrink: 0 !important;
    }
    /* Finance Management fine-tuning so it fits comfortably on one line */
    .sidebar-nav li.finance-group > a .dropdown-group-text {
        font-size: 10.5px !important;
        letter-spacing: -0.15px !important;
    }
    .sidebar-nav li.finance-group > a .dropdown-group-text i {
        margin-right: 5px !important;
    }

    /* Standalone / Single Items: Clean style with comfortable spacing */
    .sidebar-nav li:not(.sidebar-dropdown-group):not(.sidebar-section-divider):not(.sidebar-brand) {
        margin: 1.5px 0 !important;
    }
    .sidebar-nav li:not(.sidebar-dropdown-group):not(.sidebar-section-divider):not(.sidebar-brand) > a {
        background: transparent !important;
        border-left: 3.5px solid transparent !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .sidebar-nav li:not(.sidebar-dropdown-group):not(.sidebar-section-divider):not(.sidebar-brand) > a:hover {
        background: rgba(255, 255, 255, 0.1) !important;
        border-left-color: rgba(255, 255, 255, 0.35) !important;
        color: #ffffff !important;
    }
    .sidebar-nav li:not(.sidebar-dropdown-group):not(.sidebar-section-divider):not(.sidebar-brand) > a .sub_icon {
        font-size: 14px !important;
        opacity: 0.85;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }
    .sidebar-nav li:not(.sidebar-dropdown-group):not(.sidebar-section-divider):not(.sidebar-brand) > a:hover .sub_icon {
        opacity: 1;
        transform: scale(1.15);
        color: #ffffff !important;
    }

    /* Temporarily disabled Task Manager */
    .sidebar-nav li.task-manager-disabled {
        position: relative;
    }
    .sidebar-nav li.task-manager-disabled > a {
        cursor: not-allowed !important;
    }
    .sidebar-nav li.task-manager-disabled > a:hover {
        background: linear-gradient(90deg, rgba(30, 41, 59, 0.72), rgba(15, 23, 42, 0.45)) !important;
        border-left-color: #64748b !important;
    }
    .task-manager-coming-soon {
        position: absolute;
        top: 50%;
        right: 8px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 7px;
        border: 1px solid rgba(148, 163, 184, 0.45);
        border-radius: 5px;
        background: linear-gradient(135deg, #334155, #1e293b);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.28);
        color: #e2e8f0;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: 0.35px;
        line-height: 1;
        opacity: 0;
        pointer-events: none;
        transform: translate(5px, -50%) scale(0.92);
        transition: opacity 0.18s ease, transform 0.18s ease;
        z-index: 2;
    }
    .task-manager-coming-soon i {
        color: #fbbf24;
        font-size: 10px;
    }
    .sidebar-nav li.task-manager-disabled:hover .task-manager-coming-soon,
    .sidebar-nav li.task-manager-disabled:focus-within .task-manager-coming-soon {
        opacity: 1;
        transform: translate(0, -50%) scale(1);
    }
    #wrapper.active .sidebar-nav li.task-manager-disabled:hover .task-manager-coming-soon,
    #wrapper.active .sidebar-nav li.task-manager-disabled:focus-within .task-manager-coming-soon {
        right: -112px;
    }
    .sidebar-nav li.task-manager-disabled > a[aria-disabled="true"] {
        pointer-events: auto;
    }
    .task-manager-disabled-link {
        cursor: not-allowed !important;
    }

    /* Dropdown Groups: Distinct Group Appearance with Colored Accents & Pill Arrow */
    .sidebar-nav li.sidebar-dropdown-group {
        position: relative;
        margin: 1.5px 0 !important;
    }
    .sidebar-nav li.sidebar-dropdown-group > a {
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        user-select: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .sidebar-nav li.sidebar-dropdown-group .group-arrow-icon {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 17px !important;
        height: 17px !important;
        border-radius: 4px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        font-size: 9.5px !important;
        line-height: 1 !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        flex-shrink: 0 !important;
        margin-left: 2px !important;
    }
    .sidebar-nav li.sidebar-dropdown-group:hover .group-arrow-icon,
    .sidebar-nav li.sidebar-dropdown-group.flyout-hover .group-arrow-icon {
        background: rgba(255, 255, 255, 0.22) !important;
        border-color: rgba(255, 255, 255, 0.35) !important;
        color: #ffffff !important;
        transform: translateX(2px) scale(1.05) !important;
    }

    /* Active State: Any currently open Group or Standalone Tab turns vibrant YELLOW */
    .sidebar-nav li.sidebar-dropdown-group > a.active,
    .sidebar-nav li.sidebar-dropdown-group.active > a,
    .sidebar-nav li:not(.sidebar-dropdown-group):not(.sidebar-section-divider):not(.sidebar-brand) > a.active {
        color: #fbbf24 !important;
        background: #062b4f !important;
        border-left: 3.5px solid #fbbf24 !important;
        font-weight: 700 !important;
    }
    .sidebar-nav li.sidebar-dropdown-group > a.active .dropdown-group-text,
    .sidebar-nav li.sidebar-dropdown-group.active > a .dropdown-group-text,
    .sidebar-nav li.sidebar-dropdown-group > a.active .mgmt-group-text,
    .sidebar-nav li.sidebar-dropdown-group.active > a .mgmt-group-text,
    .sidebar-nav li:not(.sidebar-dropdown-group):not(.sidebar-section-divider):not(.sidebar-brand) > a.active span {
        color: #fbbf24 !important;
    }
    .sidebar-nav li.sidebar-dropdown-group > a.active .dropdown-group-text i,
    .sidebar-nav li.sidebar-dropdown-group.active > a .dropdown-group-text i,
    .sidebar-nav li.sidebar-dropdown-group > a.active .mgmt-group-text i,
    .sidebar-nav li.sidebar-dropdown-group.active > a .mgmt-group-text i {
        color: #fbbf24 !important;
    }
    .sidebar-nav li.sidebar-dropdown-group > a.active .group-arrow-icon,
    .sidebar-nav li.sidebar-dropdown-group.active > a .group-arrow-icon {
        color: #fbbf24 !important;
        border-color: rgba(251, 191, 36, 0.5) !important;
        background: rgba(251, 191, 36, 0.18) !important;
    }
    .sidebar-nav li:not(.sidebar-dropdown-group):not(.sidebar-section-divider):not(.sidebar-brand) > a.active .sub_icon,
    .sidebar-nav li.sidebar-dropdown-group > a.active .sub_icon,
    .sidebar-nav li.sidebar-dropdown-group.active > a .sub_icon {
        color: #fbbf24 !important;
        opacity: 1 !important;
    }

    /* 1. TOP MGMT Group */
    .sidebar-nav li.top-mgmt-group > a.mgmt-group-link {
        color: #ffffff !important;
        font-weight: 600 !important;
        border-left: 3.5px solid rgba(245, 158, 11, 0.6) !important;
        background: linear-gradient(90deg, rgba(245, 158, 11, 0.08) 0%, rgba(245, 158, 11, 0.01) 100%) !important;
    }
    .sidebar-nav li.top-mgmt-group > a.mgmt-group-link .group-arrow-icon {
        color: #fbbf24 !important;
        border-color: rgba(251, 191, 36, 0.28) !important;
        background: rgba(251, 191, 36, 0.1) !important;
    }
    .sidebar-nav li.top-mgmt-group > a.mgmt-group-link:hover,
    .sidebar-nav li.top-mgmt-group.flyout-hover > a.mgmt-group-link {
        background: linear-gradient(90deg, rgba(245, 158, 11, 0.2) 0%, rgba(245, 158, 11, 0.05) 100%) !important;
        border-left-color: #fbbf24 !important;
        color: #ffffff !important;
    }
    .sidebar-nav li.top-mgmt-group > a.mgmt-group-link.active,
    .sidebar-nav li.top-mgmt-group.active > a.mgmt-group-link {
        color: #fbbf24 !important;
        background: #062b4f !important;
        border-left: 3.5px solid #fbbf24 !important;
        font-weight: 700 !important;
    }
    #wrapper.active .top-mgmt-sub-icon { display: none !important; }
    #wrapper.active .group-arrow-icon { display: inline-flex !important; }
    #wrapper:not(.active) .top-mgmt-sub-icon { display: inline-block !important; }
    #wrapper:not(.active) .group-arrow-icon { display: none !important; }
    .top-mgmt-group .sidebar-flyout-menu { border-left-color: #fbbf24 !important; }
    .top-mgmt-group .sidebar-flyout-menu .flyout-header { color: #fbbf24 !important; }

    /* 2. Clients Group */
    .sidebar-nav li.clients-group > a.clients-group-link {
        border-left: 3.5px solid rgba(96, 165, 250, 0.6) !important;
        background: linear-gradient(90deg, rgba(96, 165, 250, 0.08) 0%, rgba(96, 165, 250, 0.01) 100%) !important;
    }
    .sidebar-nav li.clients-group > a.clients-group-link .group-arrow-icon {
        color: #60a5fa !important;
        border-color: rgba(96, 165, 250, 0.28) !important;
        background: rgba(96, 165, 250, 0.1) !important;
    }
    .sidebar-nav li.clients-group > a.clients-group-link:hover,
    .sidebar-nav li.clients-group.flyout-hover > a.clients-group-link {
        background: linear-gradient(90deg, rgba(96, 165, 250, 0.2) 0%, rgba(96, 165, 250, 0.05) 100%) !important;
        border-left-color: #38bdf8 !important;
        color: #ffffff !important;
    }
    .sidebar-nav li.clients-group > a.clients-group-link.active,
    .sidebar-nav li.clients-group.active > a.clients-group-link {
        color: #fbbf24 !important;
        background: #062b4f !important;
        border-left: 3.5px solid #fbbf24 !important;
        font-weight: 700 !important;
    }
    #wrapper.active .clients-sub-building { display: none !important; }
    #wrapper:not(.active) .clients-sub-building { display: inline-block !important; }
    .clients-group .sidebar-flyout-menu { border-left-color: #38bdf8 !important; }
    .clients-group .sidebar-flyout-menu .flyout-header { color: #38bdf8 !important; }

    /* 3. Marketing Group */
    .sidebar-nav li.marketing-group > a.marketing-group-link {
        border-left: 3.5px solid rgba(244, 114, 182, 0.6) !important;
        background: linear-gradient(90deg, rgba(244, 114, 182, 0.08) 0%, rgba(244, 114, 182, 0.01) 100%) !important;
    }
    .sidebar-nav li.marketing-group > a.marketing-group-link .group-arrow-icon {
        color: #f472b6 !important;
        border-color: rgba(244, 114, 182, 0.28) !important;
        background: rgba(244, 114, 182, 0.1) !important;
    }
    .sidebar-nav li.marketing-group > a.marketing-group-link:hover,
    .sidebar-nav li.marketing-group.flyout-hover > a.marketing-group-link {
        background: linear-gradient(90deg, rgba(244, 114, 182, 0.2) 0%, rgba(244, 114, 182, 0.05) 100%) !important;
        border-left-color: #f472b6 !important;
        color: #ffffff !important;
    }
    .sidebar-nav li.marketing-group > a.marketing-group-link.active,
    .sidebar-nav li.marketing-group.active > a.marketing-group-link {
        color: #fbbf24 !important;
        background: #062b4f !important;
        border-left: 3.5px solid #fbbf24 !important;
        font-weight: 700 !important;
    }
    #wrapper.active .marketing-sub-briefcase { display: none !important; }
    #wrapper:not(.active) .marketing-sub-briefcase { display: inline-block !important; }
    .marketing-group .sidebar-flyout-menu { border-left-color: #f472b6 !important; }
    .marketing-group .sidebar-flyout-menu .flyout-header { color: #f472b6 !important; }

    /* 4. Finance Group */
    .sidebar-nav li.finance-group > a.finance-group-link {
        border-left: 3.5px solid rgba(16, 185, 129, 0.6) !important;
        background: linear-gradient(90deg, rgba(16, 185, 129, 0.08) 0%, rgba(16, 185, 129, 0.01) 100%) !important;
    }
    .sidebar-nav li.finance-group > a.finance-group-link .group-arrow-icon {
        color: #10b981 !important;
        border-color: rgba(16, 185, 129, 0.28) !important;
        background: rgba(16, 185, 129, 0.1) !important;
    }
    .sidebar-nav li.finance-group > a.finance-group-link:hover,
    .sidebar-nav li.finance-group.flyout-hover > a.finance-group-link {
        background: linear-gradient(90deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.05) 100%) !important;
        border-left-color: #10b981 !important;
        color: #ffffff !important;
    }
    .sidebar-nav li.finance-group > a.finance-group-link.active,
    .sidebar-nav li.finance-group.active > a.finance-group-link {
        color: #fbbf24 !important;
        background: #062b4f !important;
        border-left: 3.5px solid #fbbf24 !important;
        font-weight: 700 !important;
    }
    #wrapper.active .finance-sub-icon { display: none !important; }
    #wrapper:not(.active) .finance-sub-icon { display: inline-block !important; }
    .finance-group .sidebar-flyout-menu { border-left-color: #10b981 !important; }
    .finance-group .sidebar-flyout-menu .flyout-header { color: #10b981 !important; }

    /* 5. Audit Planning Group */
    .sidebar-nav li.audit-group > a.audit-group-link {
        border-left: 3.5px solid rgba(168, 85, 247, 0.6) !important;
        background: linear-gradient(90deg, rgba(168, 85, 247, 0.08) 0%, rgba(168, 85, 247, 0.01) 100%) !important;
    }
    .sidebar-nav li.audit-group > a.audit-group-link .group-arrow-icon {
        color: #a855f7 !important;
        border-color: rgba(168, 85, 247, 0.28) !important;
        background: rgba(168, 85, 247, 0.1) !important;
    }
    .sidebar-nav li.audit-group > a.audit-group-link:hover,
    .sidebar-nav li.audit-group.flyout-hover > a.audit-group-link {
        background: linear-gradient(90deg, rgba(168, 85, 247, 0.2) 0%, rgba(168, 85, 247, 0.05) 100%) !important;
        border-left-color: #a855f7 !important;
        color: #ffffff !important;
    }
    .sidebar-nav li.audit-group > a.audit-group-link.active,
    .sidebar-nav li.audit-group.active > a.audit-group-link {
        color: #fbbf24 !important;
        background: #062b4f !important;
        border-left: 3.5px solid #fbbf24 !important;
        font-weight: 700 !important;
    }
    #wrapper.active .audit-sub-icon { display: none !important; }
    #wrapper:not(.active) .audit-sub-icon { display: inline-block !important; }
    .audit-group .sidebar-flyout-menu { border-left-color: #a855f7 !important; }
    .audit-group .sidebar-flyout-menu .flyout-header { color: #a855f7 !important; }

    /* 6. KPI Analysis Group */
    .sidebar-nav li.kpi-group > a.kpi-group-link {
        border-left: 3.5px solid rgba(6, 182, 212, 0.6) !important;
        background: linear-gradient(90deg, rgba(6, 182, 212, 0.08) 0%, rgba(6, 182, 212, 0.01) 100%) !important;
    }
    .sidebar-nav li.kpi-group > a.kpi-group-link .group-arrow-icon {
        color: #06b6d4 !important;
        border-color: rgba(6, 182, 212, 0.28) !important;
        background: rgba(6, 182, 212, 0.1) !important;
    }
    .sidebar-nav li.kpi-group > a.kpi-group-link:hover,
    .sidebar-nav li.kpi-group.flyout-hover > a.kpi-group-link {
        background: linear-gradient(90deg, rgba(6, 182, 212, 0.2) 0%, rgba(6, 182, 212, 0.05) 100%) !important;
        border-left-color: #06b6d4 !important;
        color: #ffffff !important;
    }
    .sidebar-nav li.kpi-group > a.kpi-group-link.active,
    .sidebar-nav li.kpi-group.active > a.kpi-group-link {
        color: #fbbf24 !important;
        background: #062b4f !important;
        border-left: 3.5px solid #fbbf24 !important;
        font-weight: 700 !important;
    }
    #wrapper.active .kpi-sub-icon { display: none !important; }
    #wrapper:not(.active) .kpi-sub-icon { display: inline-block !important; }
    .kpi-group .sidebar-flyout-menu { border-left-color: #06b6d4 !important; }
    .kpi-group .sidebar-flyout-menu .flyout-header { color: #06b6d4 !important; }
    .kpi-group .sidebar-flyout-menu .flyout-item a.flyout-link:hover { border-left-color: #06b6d4 !important; }

    /* Nested Subflyouts (Sales / Purchase under Finance) */
    .sidebar-flyout-menu .nested-flyout-group {
        position: relative;
    }
    .sidebar-flyout-menu .nested-flyout-group > a.nested-trigger-link {
        cursor: pointer !important;
    }
    .sidebar-flyout-menu .nested-flyout-group > a.nested-trigger-link .flyout-item-arrow {
        transition: transform 0.2s ease, color 0.2s ease !important;
    }
    .sidebar-flyout-menu .nested-flyout-group:hover > a.nested-trigger-link,
    .sidebar-flyout-menu .nested-flyout-group.subflyout-open > a.nested-trigger-link {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.14) !important;
        padding-left: 19px !important;
    }
    .sidebar-flyout-menu .sales-subgroup:hover > a.nested-trigger-link,
    .sidebar-flyout-menu .sales-subgroup.subflyout-open > a.nested-trigger-link {
        border-left-color: #38bdf8 !important;
    }
    .sidebar-flyout-menu .purchase-subgroup:hover > a.nested-trigger-link,
    .sidebar-flyout-menu .purchase-subgroup.subflyout-open > a.nested-trigger-link {
        border-left-color: #f472b6 !important;
    }
    .sidebar-flyout-menu .nested-flyout-group:hover > a.nested-trigger-link .flyout-item-arrow,
    .sidebar-flyout-menu .nested-flyout-group.subflyout-open > a.nested-trigger-link .flyout-item-arrow {
        color: #ffffff !important;
        transform: translateX(4px) scale(1.15);
    }
    .sidebar-flyout-menu .nested-flyout-group.active > a.nested-trigger-link {
        font-weight: 700 !important;
    }
    .sidebar-flyout-menu .sales-subgroup.active > a.nested-trigger-link {
        color: #38bdf8 !important;
        border-left-color: #38bdf8 !important;
        background: rgba(56, 189, 248, 0.14) !important;
    }
    .sidebar-flyout-menu .purchase-subgroup.active > a.nested-trigger-link {
        color: #f472b6 !important;
        border-left-color: #f472b6 !important;
        background: rgba(244, 114, 182, 0.14) !important;
    }

    /* Subflyout Container (Level 2) */
    .sidebar-subflyout-menu {
        position: absolute;
        top: -6px;
        left: 100%;
        display: none;
        min-width: 220px;
        max-width: 260px;
        background: linear-gradient(165deg, #093766 0%, #051e39 100%);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 0 10px 10px 0;
        box-shadow: 8px 12px 35px rgba(0, 0, 0, 0.52), 0 2px 8px rgba(0, 0, 0, 0.25);
        padding: 6px 0;
        margin: 0;
        list-style: none;
        z-index: 10010;
        pointer-events: none;
        opacity: 0;
        transform: translateX(-6px);
        transition: opacity 0.18s ease-out, transform 0.18s ease-out;
    }
    .sales-subflyout {
        border-left: 3.5px solid #38bdf8 !important;
    }
    .purchase-subflyout {
        border-left: 3.5px solid #f472b6 !important;
    }
    /* Invisible hit bridge between Level 1 and Level 2 */
    .sidebar-subflyout-menu::before {
        content: '';
        position: absolute;
        top: -20px;
        bottom: -20px;
        left: -35px;
        width: 40px;
        background: transparent;
        pointer-events: auto;
    }
    .nested-flyout-group:hover > .sidebar-subflyout-menu,
    .nested-flyout-group.subflyout-open > .sidebar-subflyout-menu,
    .sidebar-subflyout-menu.show-subflyout {
        display: block !important;
        pointer-events: auto !important;
        opacity: 1 !important;
        transform: translateX(0) !important;
    }
    .sidebar-subflyout-menu .subflyout-header {
        padding: 7px 16px 6px 16px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        margin-bottom: 3px;
        list-style: none;
        display: flex;
        align-items: center;
    }
    .sales-subflyout .subflyout-header {
        color: #38bdf8 !important;
    }
    .purchase-subflyout .subflyout-header {
        color: #f472b6 !important;
    }
    .sidebar-subflyout-menu .subflyout-item {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .sidebar-subflyout-menu .subflyout-item a.subflyout-link {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 10px 16px !important;
        color: #e2e8f0 !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        letter-spacing: 0.4px !important;
        text-transform: uppercase !important;
        text-decoration: none !important;
        background: transparent !important;
        border-left: 3px solid transparent !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .sidebar-subflyout-menu .subflyout-item:last-child a.subflyout-link {
        border-bottom: none !important;
    }
    .sidebar-subflyout-menu .subflyout-item a.subflyout-link .subflyout-ico {
        font-size: 13px !important;
        width: 18px !important;
        text-align: center !important;
        margin-right: 10px !important;
        flex-shrink: 0 !important;
        transition: transform 0.18s ease;
    }
    .sidebar-subflyout-menu .subflyout-item a.subflyout-link .subflyout-text {
        flex-grow: 1;
        white-space: nowrap;
    }
    .sidebar-subflyout-menu .subflyout-item a.subflyout-link:hover {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.12) !important;
        padding-left: 19px !important;
    }
    .sales-subflyout .subflyout-item a.subflyout-link:hover {
        border-left-color: #38bdf8 !important;
    }
    .purchase-subflyout .subflyout-item a.subflyout-link:hover {
        border-left-color: #f472b6 !important;
    }
    .sidebar-subflyout-menu .subflyout-item a.subflyout-link:hover .subflyout-ico {
        transform: scale(1.15);
    }
    .sidebar-subflyout-menu .subflyout-item a.subflyout-link.active {
        color: #10b981 !important;
        background: rgba(16, 185, 129, 0.18) !important;
        border-left-color: #10b981 !important;
        font-weight: 700 !important;
    }

    /* The right-side flyout menu container */
    .sidebar-flyout-menu {
        position: fixed;
        top: 100px;
        left: 180px;
        display: none;
        min-width: 220px;
        max-width: 260px;
        background: linear-gradient(165deg, #093766 0%, #051e39 100%);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-left: 3.5px solid #f59e0b;
        border-radius: 0 10px 10px 0;
        box-shadow: 6px 10px 30px rgba(0, 0, 0, 0.48), 0 2px 8px rgba(0, 0, 0, 0.2);
        padding: 6px 0;
        margin: 0;
        list-style: none;
        z-index: 10005;
        pointer-events: none;
        opacity: 0;
        transform: translateX(-6px);
        transition: opacity 0.18s ease-out, transform 0.18s ease-out;
    }
    #wrapper:not(.active) .sidebar-flyout-menu {
        left: 50px;
    }
    .sidebar-flyout-menu::before {
        content: '';
        position: absolute;
        top: -20px;
        bottom: -20px;
        left: -40px;
        width: 45px;
        background: transparent;
        pointer-events: auto;
    }
    .sidebar-nav li.sidebar-dropdown-group:hover > .sidebar-flyout-menu,
    .sidebar-nav li.sidebar-dropdown-group.flyout-hover > .sidebar-flyout-menu,
    .sidebar-flyout-menu.show-flyout {
        display: block !important;
        pointer-events: auto !important;
        opacity: 1 !important;
        transform: translateX(0) !important;
    }
    .sidebar-flyout-menu .flyout-header {
        padding: 7px 16px 6px 16px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.8px;
        color: #fbbf24;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        margin-bottom: 3px;
        list-style: none;
        display: flex;
        align-items: center;
    }
    .sidebar-flyout-menu .flyout-item {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .sidebar-flyout-menu .flyout-item a.flyout-link {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        padding: 10px 16px !important;
        color: #e2e8f0 !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        letter-spacing: 0.4px !important;
        text-transform: uppercase !important;
        text-decoration: none !important;
        background: transparent !important;
        border-left: 3px solid transparent !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .sidebar-flyout-menu .flyout-item:last-child a.flyout-link {
        border-bottom: none !important;
    }
    .sidebar-flyout-menu .flyout-item a.flyout-link .flyout-ico {
        font-size: 13px !important;
        width: 18px !important;
        text-align: center !important;
        margin-right: 10px !important;
        flex-shrink: 0 !important;
        transition: transform 0.18s ease;
    }
    .sidebar-flyout-menu .flyout-item a.flyout-link .flyout-text {
        flex-grow: 1;
        white-space: nowrap;
    }
    .sidebar-flyout-menu .flyout-item a.flyout-link .flyout-item-arrow {
        margin-left: auto !important;
        font-size: 11px !important;
        color: rgba(255, 255, 255, 0.3) !important;
        transition: transform 0.18s ease, color 0.18s ease !important;
    }
    .sidebar-flyout-menu .flyout-item a.flyout-link:hover {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.12) !important;
        border-left-color: #fbbf24 !important;
        padding-left: 19px !important;
    }
    .sidebar-flyout-menu .flyout-item a.flyout-link:hover .flyout-ico {
        transform: scale(1.15);
    }
    .sidebar-flyout-menu .flyout-item a.flyout-link:hover .flyout-item-arrow {
        color: #ffffff !important;
        transform: translateX(3px);
    }
    .sidebar-flyout-menu .flyout-item a.flyout-link.active {
        color: #38bdf8 !important;
        background: rgba(56, 189, 248, 0.18) !important;
        border-left-color: #38bdf8 !important;
        font-weight: 700 !important;
    }
    .sidebar-flyout-menu .flyout-item a.flyout-link.active .flyout-item-arrow {
        color: #38bdf8 !important;
    }
</style>

<!--<div id="wrapper" class="active">-->
<?php
if (!Utitlity::checkIfLoggedIn()) {
    header("Location: login.php?reason=login_required");
    exit;
} else if (isset($pageIdentifier) && ($pageIdentifier == "My Dashboard" || $pageIdentifier == "MY DASHBOARD")) {
    $addMyDashboardClass = "active";
} else if (isset($pageIdentifier) && $pageIdentifier == "Home Page") {
    $addClass = "active";
    if (!aitf_has_access("Client List")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && ($pageIdentifier == "Admin Dashboard" || $pageIdentifier == "Management Dashboard" || $pageIdentifier == "Top Mgmt Dashboard" || $pageIdentifier == "MGMT Dashboard" || $pageIdentifier == "Mgmt Dashboard")) {
    $addAdminDashboardClass = "active";
    if (!aitf_has_access("Admin Dashboard") && !aitf_has_access("Management Dashboard") && !aitf_has_access("Top Mgmt Dashboard") && !aitf_has_access("MGMT Dashboard") && !aitf_has_access("Mgmt Dashboard")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Add User") {
    $addUserClass = "active";
    if (!aitf_has_access("Add User")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Director Space") {
    $addDirectorSpaceClass = "active";
    if (!aitf_has_access("Directors Dashboard")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Master Data") {
    $addMasterClass = "active";
    if (!aitf_has_access("Master Creator")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Mktg") {
    $addMarketingClass = "active";
    if (!aitf_has_access("Marketing")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Audit Planning") {
    $addWorkingClass = "active";
    if (!aitf_has_access("Audit Planning")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && ($pageIdentifier == "Received Payments" || $pageIdentifier == "Received Payment")) {
    $addReceivedPaymentClass = "active";
    if (!aitf_has_access("Received Payment")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Finance Page") {
    $addFinanceClass = "active";
    if (!aitf_has_access("Payment Due")) {
        if (isset($_SESSION['username'])) {
            header("Location: homePage.php");
        } else {
            header("Location: login.php?reason=login_required");
        }
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "TDS list") {
    $addtds = "active";
    if (!aitf_has_access("TDS Deducted")) {
        if (isset($_SESSION['username'])) {
            header("Location: homePage.php");
        } else {
            header("Location: login.php?reason=login_required");
        }
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Due letter") {
    $adddueClass = "active";
    if (!aitf_has_access("Intimation Due")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "sent letter") {
    $addsentClass = "active";
    if (!aitf_has_access("Intimation Due")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Invoices") {
    $addInvoiceClass = "active";
    if (!aitf_has_access("Invoices")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Bills") {
    $addBillsClass = "active";
    if (!aitf_has_access("Bills")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Suppliers") {
    $addSuppliersClass = "active";
    if (!aitf_has_access("Creditors")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Quot") {
    $addquotClass = "active";
    if (!aitf_has_access("Quotations")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "agreement") {
    $addagreementClass = "active";
    if (!aitf_has_access("Agreements")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Recycle") {
    $addRecycleClass = "active";
    if (!aitf_has_access("Recycle Cust")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Audit Records") {
    $addAuditClass = "active";
    if (!aitf_has_access("Audit List")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "cert issue list") {
    $addcertissueClass = "active";
    if (!aitf_has_access("Cert Issue List")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "audit perf") {
    $addauditperClass = "active";
    if (!aitf_has_access("Audit Performance")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Client Analysis") {
    $clientanalysis = "active";
    if (!aitf_has_access("Client Analysis")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "cdc objrctive") {
    $cdcobj = "active";
    if (Utitlity::checkExternalUser()) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "app review objrctive") {
    $apprevobj = "active";
    if (!aitf_has_access("App Review Objective")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Application Received") {
    $addAppReceivedClass = "active";
    if (!aitf_has_access("Application")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Document Upload") {
    $docobj = "active";
    if (!aitf_has_access("Doc Upload Status")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "GoAI") {
    $addGoAiClass = "active";
    if (!aitf_has_access("Go AI")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "IAF Dashboard") {
    $iafClass = "active";
    if (!aitf_has_access("IAF Dashboard")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Job Applications") {
    $addJobAppClass = "active";
    if (!aitf_has_access("Job Applications")) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Auditor Checklist") {
    $addAuditorChecklistClass = "active";
    if (!aitf_has_access("Audit Data") || Utitlity::checkExternalUser() || Utitlity::checkClient()) {
        header("Location: login.php?reason=login_required");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Guidelines") {
    $addGuidelinesClass = "active";
    if (!aitf_has_access("Guidelines") || Utitlity::checkExternalUser() || Utitlity::checkClient()) {
        header("Location: login.php?reason=login_required");
        exit;
    }
}

function simpleDateConversion($inputStr)
{
    if ($inputStr === "") {
        return "";
    }
    $strSplit = explode("-", $inputStr);
    if (sizeOf($strSplit) > 1)
        return $strSplit[2] . "." . $strSplit[1] . "." . $strSplit[0];
    else
        return $inputStr;
}

function startsWith($haystack, $needle)
{
    // search backwards starting from haystack length characters from the end   
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function getFlatDate($inputStr)
{
    if ($inputStr === "") {
        return "";
    }
    $strSplit = explode("-", $inputStr);
    if (sizeOf($strSplit) > 1)
        return $strSplit[0] . "" . $strSplit[1] . "" . $strSplit[2];
    else
        return $inputStr;
}
?>

<?php
// Determine if this is a limited-access user (e.g., Auditor with permission-only access)
// on a permission-granted page (like Invoices). If so, hide the full sidebar.
$isLimitedAccessPage = isset($pageIdentifier) && in_array($pageIdentifier, array('Invoices', 'Bills'), true);
$hideSidebar = Utitlity::isLimitedAccessUser() && $isLimitedAccessPage;
?>

<?php if (!$hideSidebar) { ?>
<div id="sidebar-wrapper" class="hidden-xs">
    <ul id="sidebar_menu" class="sidebar-nav">
        <li class="sidebar-brand" style="height: 65px; overflow: hidden;"><a class="" id="menu-toggle" href="#"
                style="display: block; height: 65px; background: linear-gradient(180deg, #081524 0%, #053c6d 100%); padding: 5px 10px; text-decoration: none; box-sizing: border-box;">
                <div
                    style="display: inline-flex; flex-direction: column; align-items: center; justify-content: center; width: 120px; vertical-align: middle; height: 100%;">
                    <span
                        style="font-family: 'Poppins', 'Inter', sans-serif; font-weight: 800; font-size: 26px; letter-spacing: 1px; line-height: 1;">
                        <span style="color: #CF2120;">A</span><span style="color: #ffffff;">IT</span><span
                            style="color: #CF2120;">F</span>
                    </span>
                    <span
                        style="font-family: 'Poppins', 'Inter', sans-serif; font-weight: 600; font-size: 11px; color: #ffffff; letter-spacing: 2px; margin-top: 2px; line-height: 1;">ERP</span>
                </div>
                <span id="main_icon" class="glyphicon glyphicon-align-justify"
                    style="float: right; margin-top: 16px; margin-right: 15px; color: #ffffff; font-size: 18px;"></span>
            </a></li>
    </ul>
    <ul class="sidebar-nav" id="sidebar">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Auditor') { ?>
        <li><a href="auditorDashboard.php#mydashboard" class="animate <?php echo ($pageIdentifier === 'Auditor Dashboard' || $pageIdentifier === 'myDashboard') ? 'active' : ''; ?>"><span>MY DASHBOARD</span><i class="sub_icon fa fa-tachometer"></i></a></li>
        <li><a href="auditorDashboard.php#assignments" class="animate"><span>MY ASSIGNMENTS</span><i class="sub_icon fa fa-tasks"></i></a></li>
        <li><a href="auditorDashboard.php#clients" class="animate"><span>MY CLIENTS</span><i class="sub_icon fa fa-users"></i></a></li>
        <li><a href="auditorDashboard.php#history" class="animate"><span>AUDIT HISTORY</span><i class="sub_icon fa fa-history"></i></a></li>
        <li><a href="auditorDashboard.php#performance" class="animate"><span>PERFORMANCE</span><i class="sub_icon fa fa-line-chart"></i></a></li>
        <li class="guidelines-nav-item">
            <a href="auditorDashboard.php#guidelines" class="animate guidelines-nav-link <?php echo ($pageIdentifier === 'Guidelines') ? 'active' : ''; ?>">
                <span><i class="fa fa-book" style="color: #34d399; margin-right: 4px; font-size: 11px;"></i> GUIDELINES</span>
                <i class="sub_icon fa fa-book"></i>
            </a>
        </li>
        <?php if (Utitlity::checkUserPermission('client_access')) { ?>
        <li><a href="homePage.php" class="animate <?php echo ($pageIdentifier === 'Clients') ? 'active' : ''; ?>" style="color: #a5b4fc !important;"><span>COMPANY CLIENTS</span><i class="sub_icon fa fa-building"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkUserPermission('invoice_access')) { ?>
        <li><a href="invoicePage.php" class="animate <?php echo ($pageIdentifier === 'Invoices') ? 'active' : ''; ?>" style="color: #a5b4fc !important;"><span>INVOICES</span><i class="sub_icon fa fa-inr"></i></a></li>
        <?php } ?>
        <?php } else { 
        $currentScript = basename($_SERVER['PHP_SELF'] ?? '');
        $isMyDashboardActive = !empty($addMyDashboardClass) || $currentScript === 'myDashboard.php';
        $isTaskManagerActive = (isset($pageIdentifier) && $pageIdentifier === 'Task Manager') || $currentScript === 'taskManager.php';
        $isIafDashboardActive = !empty($iafClass) || $currentScript === 'iaf_dashboard.php';
        ?>
        <li class="sidebar-section-divider"><span class="section-title">DASHBOARDS</span></li>
        <li><a href="myDashboard.php" class="animate <?php echo $isMyDashboardActive ? 'active' : ''; ?>"><span>MY DASHBOARD</span><i class="sub_icon fa fa-tachometer"></i></a></li>
        <li class="task-manager-disabled">
            <a href="#" class="animate task-manager-disabled-link <?php echo $isTaskManagerActive ? 'active' : ''; ?>" aria-disabled="true" title="Task Manager is coming soon">
                <span>TASK MANAGER</span><i class="sub_icon fa fa-tasks"></i>
            </a>
            <span class="task-manager-coming-soon"><i class="fa fa-lock" aria-hidden="true"></i> COMING SOON</span>
        </li>
        <?php if (aitf_has_access("IAF Dashboard")) { ?>
        <li><a href="iaf_dashboard.php" class="animate <?php echo $isIafDashboardActive ? 'active' : ''; ?>"><span>IAF DASHBOARD</span><i class="sub_icon fa fa-globe"></i></a></li>
        <?php } ?>
        <li class="sidebar-section-divider"><span class="section-title">DEPARTMENTS</span></li>
        <?php
        $canAccessControlMgmt = Utitlity::checkForDirector();
        $canAccessMgmtDashboard = aitf_has_access("Admin Dashboard") || aitf_has_access("Management Dashboard") || aitf_has_access("Top Mgmt Dashboard") || aitf_has_access("MGMT Dashboard") || aitf_has_access("Mgmt Dashboard");
        $canAccessAddUser = aitf_has_access("Add User");
        $canAccessRecycle = aitf_has_access("Recycle Cust");
        $canAccessMasterCreator = aitf_has_access("Master Creator") || Utitlity::checkForDirector();
        $canAccessTopMgmt = $canAccessControlMgmt || $canAccessMgmtDashboard || $canAccessAddUser || $canAccessRecycle || $canAccessMasterCreator;
        $isTopMgmtActive = !empty($addDirectorSpaceClass) || !empty($addAdminDashboardClass) || !empty($addUserClass) || !empty($addRecycleClass) || !empty($addMasterClass) || in_array($currentScript, ['directorSpace.php', 'adminDashboard.php', 'addUsers.php', 'clientRecycle.php', 'masterCreator.php']);
        ?>
        <?php if ($canAccessTopMgmt) { ?>
        <li class="sidebar-dropdown-group top-mgmt-group <?php echo $isTopMgmtActive ? 'active' : ''; ?>">
            <a href="javascript:void(0);" class="animate dropdown-group-link mgmt-group-link <?php echo $isTopMgmtActive ? 'active' : ''; ?>" title="TOP MGMT">
                <span class="dropdown-group-text">
                    <i class="fa fa-shield top-mgmt-group-icon" style="color: #fbbf24; margin-right: 4px; font-size: 10px;"></i> TOP MGMT
                </span>
                <i class="sub_icon fa fa-angle-right group-arrow-icon"></i>
                <i class="sub_icon fa fa-shield top-mgmt-sub-icon" style="color: #fbbf24;"></i>
            </a>
            <ul class="sidebar-flyout-menu">
                <li class="flyout-header">
                    <span><i class="fa fa-shield" style="color: #fbbf24; margin-right: 5px; font-size: 11px;"></i> TOP MANAGEMENT</span>
                </li>
                <?php if ($canAccessControlMgmt) { ?>
                <li class="flyout-item">
                    <a href="directorSpace.php" class="flyout-link <?php echo $addDirectorSpaceClass ?? ''; ?>">
                        <i class="fa fa-black-tie flyout-ico" style="color: #fbbf24;"></i>
                        <span class="flyout-text">CONTROL MANAGEMENT</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessMgmtDashboard) { ?>
                <li class="flyout-item">
                    <a href="adminDashboard.php" class="flyout-link <?php echo isset($addAdminDashboardClass) ? $addAdminDashboardClass : ''; ?>">
                        <i class="fa fa-line-chart flyout-ico" style="color: #38bdf8;"></i>
                        <span class="flyout-text">MGMT DASHBOARD</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessAddUser) { ?>
                <li class="flyout-item">
                    <a href="addUsers.php" class="flyout-link <?php echo $addUserClass ?? ''; ?>">
                        <i class="fa fa-user-plus flyout-ico" style="color: #34d399;"></i>
                        <span class="flyout-text">ADD USER</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessMasterCreator) { ?>
                <li class="flyout-item">
                    <a href="masterCreator.php" class="flyout-link <?php echo $addMasterClass ?? ''; ?>">
                        <i class="fa fa-wrench flyout-ico" style="color: #38bdf8;"></i>
                        <span class="flyout-text">MASTER CREATOR</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessRecycle) { ?>
                <li class="flyout-item">
                    <a href="clientRecycle.php" class="flyout-link <?php echo $addRecycleClass ?? ''; ?>">
                        <i class="fa fa-trash-o flyout-ico" style="color: #f87171;"></i>
                        <span class="flyout-text">RECYCLE BIN</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </li>
        <?php } ?>


        <?php
        $canAccessClientList = aitf_has_access('Client List');
        $canAccessCertIssueList = aitf_has_access('Cert Issue List');
        $canAccessClientAnalysis = aitf_has_access('Client Analysis');
        $canAccessClientsGroup = $canAccessClientList || $canAccessCertIssueList || $canAccessClientAnalysis;
        $isClientsGroupActive = !empty($addClass) || !empty($addcertissueClass) || !empty($clientanalysis) || in_array($currentScript, ['homePage.php', 'auditanalysis.php', 'clientanalysis.php', 'clientHistory.php', 'editClient.php', 'addClient.php', 'clientList.php']);
        ?>
        <?php if ($canAccessClientsGroup) { ?>
        <li class="sidebar-dropdown-group clients-group <?php echo $isClientsGroupActive ? 'active' : ''; ?>">
            <a href="javascript:void(0);" class="animate dropdown-group-link clients-group-link <?php echo $isClientsGroupActive ? 'active' : ''; ?>" title="CLIENTS">
                <span class="dropdown-group-text">
                    <i class="fa fa-building clients-group-icon" style="color: #60a5fa; margin-right: 4px; font-size: 11px;"></i> CLIENTS
                </span>
                <i class="sub_icon fa fa-angle-right group-arrow-icon"></i>
                <i class="sub_icon fa fa-building clients-sub-building" style="color: #60a5fa;"></i>
            </a>
            <ul class="sidebar-flyout-menu">
                <li class="flyout-header">
                    <span><i class="fa fa-building" style="color: #38bdf8; margin-right: 5px;"></i> CLIENT MANAGEMENT</span>
                </li>
                <?php if ($canAccessClientList) { ?>
                <li class="flyout-item">
                    <a href="homePage.php" class="flyout-link <?php echo $addClass ?? ''; ?>">
                        <i class="fa fa-building flyout-ico" style="color: #60a5fa;"></i>
                        <span class="flyout-text">CLIENTS</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessCertIssueList) { ?>
                <li class="flyout-item">
                    <a href="auditanalysis.php" class="flyout-link <?php echo $addcertissueClass ?? ''; ?>">
                        <i class="fa fa-newspaper-o flyout-ico" style="color: #38bdf8;"></i>
                        <span class="flyout-text">CERT. ISSUED LIST</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessClientAnalysis) { ?>
                <li class="flyout-item">
                    <a href="clientanalysis.php" class="flyout-link <?php echo $clientanalysis ?? ''; ?>">
                        <i class="fa fa-pie-chart flyout-ico" style="color: #a78bfa;"></i>
                        <span class="flyout-text">CLIENT ANALYSIS</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </li>
        <?php } ?>
        <?php
        $canAccessMarketingItem = aitf_has_access("Marketing");
        $canAccessAppReceived = aitf_has_access("Application");
        $canAccessAgreements = aitf_has_access("Agreements");
        $canAccessIntimationDue = aitf_has_access("Intimation Due");
        $canAccessQuotations = aitf_has_access("Quotations");
        $canAccessMarketingGroup = $canAccessMarketingItem || $canAccessAppReceived || $canAccessAgreements || $canAccessIntimationDue || $canAccessQuotations;
        $isMarketingGroupActive = !empty($addMarketingClass) || !empty($addAppReceivedClass) || !empty($addagreementClass) || !empty($adddueClass) || !empty($addquotClass) || in_array($currentScript, ['marketingPage.php', 'applicationReceived.php', 'agreement_list.php', 'due_letter.php', 'mkt_calling_data.php', 'mkt_unassigned_data.php', 'mkt_all_data.php', 'mkt_incomplete_info.php', 'mkt_feedback_data.php', 'mkt_settings.php', 'quotation_list.php', 'add_followup.php', 'quotationPage.php']);
        $mktUnreadBadge = isset($GLOBALS['unread_app_count']) ? (int) $GLOBALS['unread_app_count'] : 0;
        ?>
        <?php if ($canAccessMarketingGroup) { ?>
        <li class="sidebar-dropdown-group marketing-group <?php echo $isMarketingGroupActive ? 'active' : ''; ?>">
            <a href="javascript:void(0);" class="animate dropdown-group-link marketing-group-link <?php echo $isMarketingGroupActive ? 'active' : ''; ?>" title="MARKETING">
                <span class="dropdown-group-text">
                    <i class="fa fa-briefcase marketing-group-icon" style="color: #f472b6; margin-right: 4px; font-size: 11px;"></i> MARKETING
                    <?php if ($mktUnreadBadge > 0) { ?>
                        <span style="margin-left:5px; padding:1px 5px; border-radius:999px; background:#ef4444; color:#fff; font-size:9px; font-weight:700;"><?php echo $mktUnreadBadge; ?></span>
                    <?php } ?>
                </span>
                <i class="sub_icon fa fa-angle-right group-arrow-icon"></i>
                <i class="sub_icon fa fa-briefcase marketing-sub-briefcase" style="color: #f472b6;"></i>
            </a>
            <ul class="sidebar-flyout-menu">
                <li class="flyout-header">
                    <span><i class="fa fa-briefcase" style="color: #f472b6; margin-right: 5px;"></i> MARKETING & DEALS</span>
                </li>
                <?php if ($canAccessMarketingItem) { ?>
                <li class="flyout-item">
                    <a href="marketingPage.php" class="flyout-link <?php echo $addMarketingClass ?? ''; ?>">
                        <i class="fa fa-briefcase flyout-ico" style="color: #f472b6;"></i>
                        <span class="flyout-text">MARKETING PAGE</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessQuotations) { ?>
                <li class="flyout-item">
                    <a href="quotationPage.php" class="flyout-link <?php echo $addquotClass ?? ''; ?>">
                        <i class="fa fa-quote-left flyout-ico" style="color: #a78bfa;"></i>
                        <span class="flyout-text">QUOTATIONS</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessAppReceived) { ?>
                <li class="flyout-item">
                    <a href="applicationReceived.php" class="flyout-link <?php echo $addAppReceivedClass ?? ''; ?>">
                        <i class="fa fa-wpforms flyout-ico" style="color: #38bdf8;"></i>
                        <span class="flyout-text">
                            APPLICATION
                            <?php if ($mktUnreadBadge > 0) { ?>
                                <span style="margin-left:5px; padding:1px 5px; border-radius:999px; background:#ef4444; color:#fff; font-size:9px; font-weight:700;"><?php echo $mktUnreadBadge; ?></span>
                            <?php } ?>
                        </span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessAgreements) { ?>
                <li class="flyout-item">
                    <a href="agreementPage.php" class="flyout-link <?php echo $addagreementClass ?? ''; ?>">
                        <i class="fa fa-handshake-o flyout-ico" style="color: #34d399;"></i>
                        <span class="flyout-text">AGREEMENTS</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessIntimationDue) { ?>
                <li class="flyout-item">
                    <a href="intimationLetter.php" class="flyout-link <?php echo $adddueClass ?? ''; ?>">
                        <i class="fa fa-envelope-o flyout-ico" style="color: #fbbf24;"></i>
                        <span class="flyout-text">INTIMATION DUE</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </li>
        <?php } ?> 
        <?php
        $canAccessInvoices = aitf_has_access('Invoices');
        $canAccessReceivedPayment = aitf_has_access('Received Payment');
        $canAccessPaymentDue = aitf_has_access('Payment Due');
        $canAccessTds = aitf_has_access('TDS Deducted');
        $canAccessBills = aitf_has_access('Bills');
        $canAccessCreditors = aitf_has_access('Creditors');

        $canAccessSales = $canAccessInvoices || $canAccessReceivedPayment || $canAccessPaymentDue || $canAccessTds;
        $canAccessPurchase = $canAccessBills || $canAccessCreditors;
        $canAccessFinance = $canAccessSales || $canAccessPurchase;

        $isSalesActive = !empty($addInvoiceClass) || !empty($addReceivedPaymentClass) || !empty($addFinanceClass) || !empty($addtds);
        $isPurchaseActive = !empty($addBillsClass) || !empty($addSuppliersClass);
        $isFinanceGroupActive = $isSalesActive || $isPurchaseActive || in_array($currentScript, ['finance_dashboard.php', 'invoicePage.php', 'proforma_invoices.php', 'receivedPayments.php', 'paymentPage.php', 'listOfTds.php', 'bills.php', 'suppliers.php']);
        ?>
        <?php if ($canAccessFinance) { ?>
        <li class="sidebar-dropdown-group finance-group <?php echo $isFinanceGroupActive ? 'active' : ''; ?>">
            <a href="javascript:void(0);" class="animate dropdown-group-link finance-group-link <?php echo $isFinanceGroupActive ? 'active' : ''; ?>" title="FINANCE MANAGEMENT">
                <span class="dropdown-group-text">
                    <i class="fa fa-calculator finance-group-icon" style="color: #10b981; margin-right: 4px; font-size: 11px;"></i> FINANCE MANAGEMENT
                </span>
                <i class="sub_icon fa fa-angle-right group-arrow-icon"></i>
                <i class="sub_icon fa fa-calculator finance-sub-icon" style="color: #10b981;"></i>
            </a>
            <ul class="sidebar-flyout-menu finance-flyout">
                <li class="flyout-header">
                    <span><i class="fa fa-calculator" style="color: #10b981; margin-right: 5px;"></i> FINANCE MANAGEMENT</span>
                </li>
                <?php if ($canAccessSales) { ?>
                <li class="flyout-item nested-flyout-group sales-subgroup <?php echo $isSalesActive ? 'active' : ''; ?>">
                    <a href="javascript:void(0);" class="flyout-link nested-trigger-link <?php echo $isSalesActive ? 'active' : ''; ?>" title="SALES">
                        <i class="fa fa-shopping-cart flyout-ico" style="color: #38bdf8;"></i>
                        <span class="flyout-text">SALES</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                    <ul class="sidebar-subflyout-menu sales-subflyout">
                        <li class="subflyout-header">
                            <span><i class="fa fa-shopping-cart" style="color: #38bdf8; margin-right: 5px;"></i> SALES</span>
                        </li>
                        <?php if ($canAccessInvoices) { ?>
                        <li class="subflyout-item">
                            <a href="invoicePage.php" class="subflyout-link <?php echo $addInvoiceClass ?? ''; ?>">
                                <i class="fa fa-inr subflyout-ico" style="color: #38bdf8;"></i>
                                <span class="subflyout-text">INVOICES</span>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if ($canAccessReceivedPayment) { ?>
                        <li class="subflyout-item">
                            <a href="receivedPayments.php" class="subflyout-link <?php echo $addReceivedPaymentClass ?? ''; ?>">
                                <i class="fa fa-check-circle subflyout-ico" style="color: #34d399;"></i>
                                <span class="subflyout-text">RECEIVED PAYMENT</span>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if ($canAccessPaymentDue) { ?>
                        <li class="subflyout-item">
                            <a href="paymentPage.php" class="subflyout-link <?php echo $addFinanceClass ?? ''; ?>">
                                <i class="fa fa-money subflyout-ico" style="color: #fbbf24;"></i>
                                <span class="subflyout-text">DUE PAYMENT</span>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if ($canAccessTds) { ?>
                        <li class="subflyout-item">
                            <a href="listOfTds.php" class="subflyout-link <?php echo $addtds ?? ''; ?>">
                                <i class="fa fa-list subflyout-ico" style="color: #a78bfa;"></i>
                                <span class="subflyout-text">TDS DEDUCTED</span>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
                <?php if ($canAccessPurchase) { ?>
                <li class="flyout-item nested-flyout-group purchase-subgroup <?php echo $isPurchaseActive ? 'active' : ''; ?>">
                    <a href="javascript:void(0);" class="flyout-link nested-trigger-link <?php echo $isPurchaseActive ? 'active' : ''; ?>" title="PURCHASE">
                        <i class="fa fa-truck flyout-ico" style="color: #f472b6;"></i>
                        <span class="flyout-text">PURCHASE</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                    <ul class="sidebar-subflyout-menu purchase-subflyout">
                        <li class="subflyout-header">
                            <span><i class="fa fa-truck" style="color: #f472b6; margin-right: 5px;"></i> PURCHASE</span>
                        </li>
                        <?php if ($canAccessBills) { ?>
                        <li class="subflyout-item">
                            <a href="billsPage.php" class="subflyout-link <?php echo $addBillsClass ?? ''; ?>">
                                <i class="fa fa-file-text-o subflyout-ico" style="color: #f472b6;"></i>
                                <span class="subflyout-text">BILLS</span>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if ($canAccessCreditors) { ?>
                        <li class="subflyout-item">
                            <a href="suppliers.php" class="subflyout-link <?php echo $addSuppliersClass ?? ''; ?>">
                                <i class="fa fa-address-card-o subflyout-ico" style="color: #fbbf24;"></i>
                                <span class="subflyout-text">SUPPLIERS</span>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
            </ul>
        </li>
        <?php } ?>
        <?php
        $canAccessAuditList = aitf_has_access("Audit List");
        $canAccessAuditPlanning = aitf_has_access("Audit Planning");
        $canAccessAuditData = aitf_has_access("Audit Data");
        $canAccessAuditGroup = $canAccessAuditList || $canAccessAuditPlanning || $canAccessAuditData;
        $isAuditGroupActive = !empty($addAuditClass) || !empty($addWorkingClass) || !empty($addAuditorChecklistClass) || in_array($currentScript, ['auditPage.php', 'auditPlanning.php', 'auditorChecklist.php']);
        ?>
        <?php if ($canAccessAuditGroup) { ?>
        <li class="sidebar-dropdown-group audit-group <?php echo $isAuditGroupActive ? 'active' : ''; ?>">
            <a href="javascript:void(0);" class="animate dropdown-group-link audit-group-link <?php echo $isAuditGroupActive ? 'active' : ''; ?>" title="AUDIT PLANNING">
                <span class="dropdown-group-text">
                    <i class="fa fa-calendar-check-o audit-group-icon" style="color: #a855f7; margin-right: 4px; font-size: 11px;"></i> AUDIT PLANNING
                </span>
                <i class="sub_icon fa fa-angle-right group-arrow-icon"></i>
                <i class="sub_icon fa fa-calendar-check-o audit-sub-icon" style="color: #a855f7;"></i>
            </a>
            <ul class="sidebar-flyout-menu audit-flyout">
                <li class="flyout-header">
                    <span><i class="fa fa-calendar-check-o" style="color: #a855f7; margin-right: 5px;"></i> AUDIT PLANNING</span>
                </li>
                <?php if ($canAccessAuditList) { ?>
                <li class="flyout-item">
                    <a href="auditPage.php" class="flyout-link <?php echo $addAuditClass ?? ''; ?>">
                        <i class="fa fa-list-alt flyout-ico" style="color: #38bdf8;"></i>
                        <span class="flyout-text">AUDIT LIST</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessAuditPlanning) { ?>
                <li class="flyout-item">
                    <a href="auditPlanning.php" class="flyout-link <?php echo $addWorkingClass ?? ''; ?>">
                        <i class="fa fa-calendar-check-o flyout-ico" style="color: #a855f7;"></i>
                        <span class="flyout-text">AUDIT PLANNING</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessAuditData) { ?>
                <li class="flyout-item">
                    <a href="auditorChecklist.php" class="flyout-link <?php echo $addAuditorChecklistClass ?? ''; ?>">
                        <i class="fa fa-clipboard flyout-ico" style="color: #34d399;"></i>
                        <span class="flyout-text">AUDIT DATA</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </li>
        <?php } ?>
        <?php
        $canAccessAuditPerformance = aitf_has_access("Audit Performance");
        $canAccessAppRevObjective = aitf_has_access("App Review Objective");
        $canAccessDocUploadStatus = aitf_has_access("Doc Upload Status");
        $canAccessKpiGroup = aitf_has_access("KPI Analysis") || $canAccessAuditPerformance || $canAccessAppRevObjective || $canAccessDocUploadStatus;
        $isKpiGroupActive = !empty($addauditperClass) || !empty($apprevobj) || !empty($docobj) || in_array($currentScript, ['qualityanalysis.php', 'apprevobjective.php', 'uploadingStatus.php']);
        ?>
        <?php if ($canAccessKpiGroup) { ?>
        <li class="sidebar-dropdown-group kpi-group <?php echo $isKpiGroupActive ? 'active' : ''; ?>">
            <a href="javascript:void(0);" class="animate dropdown-group-link kpi-group-link <?php echo $isKpiGroupActive ? 'active' : ''; ?>" title="KPI ANALYSIS">
                <span class="dropdown-group-text">
                    <i class="fa fa-bar-chart kpi-group-icon" style="color: #06b6d4; margin-right: 4px; font-size: 11px;"></i> KPI ANALYSIS
                </span>
                <i class="sub_icon fa fa-angle-right group-arrow-icon"></i>
                <i class="sub_icon fa fa-bar-chart kpi-sub-icon" style="color: #06b6d4;"></i>
            </a>
            <ul class="sidebar-flyout-menu kpi-flyout">
                <li class="flyout-header">
                    <span><i class="fa fa-bar-chart" style="color: #06b6d4; margin-right: 5px;"></i> KPI ANALYSIS</span>
                </li>
                <?php if ($canAccessAuditPerformance) { ?>
                <li class="flyout-item">
                    <a href="qualityanalysis.php" class="flyout-link <?php echo $addauditperClass ?? ''; ?>">
                        <i class="fa fa-line-chart flyout-ico" style="color: #38bdf8;"></i>
                        <span class="flyout-text">AUDIT PERFORMANCE</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessAppRevObjective) { ?>
                <li class="flyout-item">
                    <a href="apprevobjective.php" class="flyout-link <?php echo $apprevobj ?? ''; ?>">
                        <i class="fa fa-eye flyout-ico" style="color: #fbbf24;"></i>
                        <span class="flyout-text">APP. REVIEW OBJECTIVE</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
                <?php if ($canAccessDocUploadStatus) { ?>
                <li class="flyout-item">
                    <a href="uploadingStatus.php" class="flyout-link <?php echo $docobj ?? ''; ?>">
                        <i class="fa fa-cloud-upload flyout-ico" style="color: #34d399;"></i>
                        <span class="flyout-text">DOC UPLOAD STATUS</span>
                        <i class="fa fa-angle-right flyout-item-arrow"></i>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </li>
        <?php } ?>
        <?php } // end if role !== 'Auditor' ?>
    </ul>
</div>
<?php } // end if (!$hideSidebar) ?>
<?php if ($hideSidebar) { ?>
<style>
    /* Full-width layout when sidebar is hidden for limited-access users */
    #wrapper { padding-left: 0 !important; }
    #page-content-wrapper { width: 100% !important; margin-left: 0 !important; padding-left: 0 !important; }
    #wrapper.active #page-content-wrapper { margin-left: 0 !important; }
    .page-content.inset { margin-left: 0 !important; padding: 20px 30px !important; }
    #sidebar-wrapper { display: none !important; }
    .navbar { left: 0 !important; }
    .navbar-container { margin-left: 0 !important; }
</style>
<?php } ?>

<nav class="navbar navbar-fixed-top modern-navbar" role="navigation">
    <div class="navbar-container">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>

        <div class="navbar-content">
            <?php if (!(Utitlity::checkClient() || Utitlity::checkExternalUser() || (isset($_SESSION['role']) && $_SESSION['role'] === 'Auditor'))) { ?>
                <div class="navbar-left">
                    <div class="dropdown add-new-dropdown">
                        <a href="#" class="dropdown-toggle modern-add-btn" data-toggle="dropdown" title="Add New">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            <span>Add New</span>
                            <i class="fa fa-chevron-down" aria-hidden="true" style="font-size: 11px;"></i>
                        </a>
                        <div class="dropdown-menu add-new-menu" role="menu">
                            <div class="add-new-panel">
                                <a href="#" class="addAccountCss add-new-item add-new-item-client">
                                    <span class="add-new-ico"><i class="fa fa-building"></i></span>
                                    <span class="add-new-text">
                                        <span class="add-new-title">Add New Client</span>
                                        <span class="add-new-sub">Create a new client profile</span>
                                    </span>
                                    <span class="add-new-arrow"><i class="fa fa-angle-right"></i></span>
                                </a>
                                <a href="suppliers.php?add=1" class="add-new-item add-new-item-supplier">
                                    <span class="add-new-ico"><i class="fa fa-truck"></i></span>
                                    <span class="add-new-text">
                                        <span class="add-new-title">Add New Supplier</span>
                                        <span class="add-new-sub">Register a new supplier</span>
                                    </span>
                                    <span class="add-new-arrow"><i class="fa fa-angle-right"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="navbar-center">
                    <div class="search-wrapper">
                        <i class="fa fa-search search-icon" aria-hidden="true"></i>
                        <input class="dropdown-toggle searchcustomer modern-search" data-toggle="dropdown"
                            placeholder="Search Client" autocomplete="off" />
                        <ul class="dropdown-menu clientlistappend" role="menu"></ul>
                    </div>
                </div>
            <?php } ?>

            <div class="navbar-right">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Auditor') { ?>
                    <a href="auditorDashboard.php" style="color: #4f46e5; background-color: #e0e7ff; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; border: 1px solid #c7d2fe; padding: 8px 16px; border-radius: 8px; margin-right: 10px; transition: all 0.2s; height: 40px;" title="Back to Dashboard" onmouseover="this.style.backgroundColor='#c7d2fe'" onmouseout="this.style.backgroundColor='#e0e7ff'">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                <?php } ?>
                <button type="button" id="themeToggle" class="modern-card-btn" aria-label="Toggle dark mode"
                    title="Dark Mode">
                    <i class="fa fa-moon-o" aria-hidden="true"></i>
                </button>

                <?php if (isset($_SESSION['username'])) { ?>
                    <div class="dropdown modern-user-dropdown">
                        <a class="dropdown-toggle modern-card-btn modern-profile-card" data-toggle="dropdown" href="#"
                            role="button">
                            <div class="user-avatar">
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-greeting">Hi,</span>
                                <span class="user-name"><?php echo $_SESSION['username']; ?></span>
                            </div>
                            <i class="fa fa-chevron-down dropdown-arrow" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu modern-dropdown-menu" role="menu">
                            <li><a class="passwordChange" href="#" data-id="<?php echo $_SESSION['id']; ?>"><i
                                        class="fa fa-key"></i> Change Password</a></li>
                        </ul>
                    </div>

                    <a href="#" class="modern-card-btn modern-logout-card" data-toggle="modal"
                        data-target="#logoutConfirmModal" title="Logout">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                        <span>Logout</span>
                    </a>
                <?php } ?>
            </div>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <?php if ($hideSidebar) { ?>
            <!-- Limited mobile menu for permission-only access users -->
            <ul class="nav navbar-nav visible-xs">
                <li><a href="myDashboard.php">MY DASHBOARD</a></li>
                <li><a href="#" class="task-manager-disabled-link" aria-disabled="true" title="Task Manager is coming soon">TASK MANAGER</a></li>
                <li><a href="auditorDashboard.php"><i class="fa fa-arrow-left"></i> Back to Dashboard</a></li>
                <li class="active"><a href="invoicePage.php">INVOICES</a></li>
                <li><a href="guidelines.php" style="color: #10b981; font-weight: 700;"><i class="fa fa-book"></i> GUIDELINES</a></li>
            </ul>
            <?php } else { ?>
            <ul class="nav navbar-nav visible-xs">
                <li><a href="myDashboard.php">MY DASHBOARD</a></li>
                <li><a href="#" class="task-manager-disabled-link" aria-disabled="true" title="Task Manager is coming soon">TASK MANAGER</a></li>
                <?php if ($canAccessTopMgmt) { ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #fbbf24; font-weight: 700;">
                        <i class="fa fa-star" style="color: #fbbf24; margin-right: 5px;"></i> TOP MGMT <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu" style="background: #082d54; border: 1px solid rgba(255,255,255,0.15);">
                        <?php if ($canAccessControlMgmt) { ?>
                        <li><a href="directorSpace.php" style="color: #fff;"><i class="fa fa-black-tie" style="color: #fbbf24; margin-right: 6px;"></i> CONTROL MANAGEMENT</a></li>
                        <?php } ?>
                        <?php if ($canAccessMgmtDashboard) { ?>
                        <li><a href="adminDashboard.php" style="color: #fff;"><i class="fa fa-line-chart" style="color: #38bdf8; margin-right: 6px;"></i> MGMT DASHBOARD</a></li>
                        <?php } ?>
                        <?php if ($canAccessAddUser) { ?>
                        <li><a href="addUsers.php" style="color: #fff;"><i class="fa fa-user-plus" style="color: #34d399; margin-right: 6px;"></i> ADD USER</a></li>
                        <?php } ?>
                        <?php if ($canAccessRecycle) { ?>
                        <li><a href="clientRecycle.php" style="color: #fff;"><i class="fa fa-trash-o" style="color: #f87171; margin-right: 6px;"></i> RECYCLE BIN</a></li>
                        <?php } ?>
                        <?php if ($canAccessMasterCreator) { ?>
                        <li><a href="masterCreator.php" style="color: #fff;"><i class="fa fa-cogs" style="color: #e879f9; margin-right: 6px;"></i> MASTER CREATOR</a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
                <?php if (!isset($_SESSION["role"]) || $_SESSION["role"] != 'client') { ?>
                <li><a href="iaf_dashboard.php">IAF DASHBOARD</a></li>
                <?php } ?>
                <?php if ($canAccessClientsGroup) { ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #60a5fa; font-weight: 700;">
                        <i class="fa fa-building" style="color: #60a5fa; margin-right: 5px;"></i> CLIENTS <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu" style="background: #082d54; border: 1px solid rgba(255,255,255,0.15);">
                        <?php if ($canAccessClientList) { ?>
                        <li><a href="homePage.php" style="color: #fff;"><i class="fa fa-building" style="color: #60a5fa; margin-right: 6px;"></i> CLIENTS</a></li>
                        <?php } ?>
                        <?php if ($canAccessCertIssueList) { ?>
                        <li><a href="auditanalysis.php" style="color: #fff;"><i class="fa fa-newspaper-o" style="color: #38bdf8; margin-right: 6px;"></i> CERT. ISSUED LIST</a></li>
                        <?php } ?>
                        <?php if ($canAccessClientAnalysis) { ?>
                        <li><a href="clientanalysis.php" style="color: #fff;"><i class="fa fa-pie-chart" style="color: #a78bfa; margin-right: 6px;"></i> CLIENT ANALYSIS</a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
                <?php if ($canAccessMarketingGroup) { ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #f472b6; font-weight: 700;">
                        <i class="fa fa-briefcase" style="color: #f472b6; margin-right: 5px;"></i> MARKETING <b class="caret"></b>
                        <?php if ($mktUnreadBadge > 0) { ?>
                            <span style="margin-left:4px; padding:1px 5px; border-radius:999px; background:#ef4444; color:#fff; font-size:9px;"><?php echo $mktUnreadBadge; ?></span>
                        <?php } ?>
                    </a>
                    <ul class="dropdown-menu" style="background: #082d54; border: 1px solid rgba(255,255,255,0.15);">
                        <?php if ($canAccessMarketingItem) { ?>
                        <li><a href="marketingPage.php" style="color: #fff;"><i class="fa fa-briefcase" style="color: #f472b6; margin-right: 6px;"></i> MARKETING PAGE</a></li>
                        <?php } ?>
                        <?php if ($canAccessQuotations) { ?>
                        <li><a href="quotationPage.php" style="color: #fff;"><i class="fa fa-quote-left" style="color: #a78bfa; margin-right: 6px;"></i> QUOTATIONS</a></li>
                        <?php } ?>
                        <?php if ($canAccessAppReceived) { ?>
                        <li><a href="applicationReceived.php" style="color: #fff;"><i class="fa fa-wpforms" style="color: #38bdf8; margin-right: 6px;"></i> APPLICATION <?php if ($mktUnreadBadge > 0) { ?><span style="padding:1px 5px; border-radius:999px; background:#ef4444; color:#fff; font-size:9px;"><?php echo $mktUnreadBadge; ?></span><?php } ?></a></li>
                        <?php } ?>
                        <?php if ($canAccessAgreements) { ?>
                        <li><a href="agreementPage.php" style="color: #fff;"><i class="fa fa-handshake-o" style="color: #34d399; margin-right: 6px;"></i> AGREEMENTS</a></li>
                        <?php } ?>
                        <?php if ($canAccessIntimationDue) { ?>
                        <li><a href="intimationLetter.php" style="color: #fff;"><i class="fa fa-envelope-o" style="color: #fbbf24; margin-right: 6px;"></i> INTIMATION DUE</a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
                <?php if ($canAccessFinance) { ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #10b981; font-weight: 700;">
                        <i class="fa fa-calculator" style="color: #10b981; margin-right: 5px;"></i> FINANCE MANAGEMENT <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu" style="background: #082d54; border: 1px solid rgba(255,255,255,0.15);">
                        <?php if ($canAccessSales) { ?>
                        <li class="dropdown-header" style="color: #38bdf8; font-weight: 700; font-size: 11px; padding: 6px 15px; letter-spacing: 0.6px; text-transform: uppercase;">
                            <i class="fa fa-shopping-cart" style="margin-right: 4px;"></i> SALES
                        </li>
                        <?php if ($canAccessInvoices) { ?>
                        <li style="padding-left: 8px;"><a href="invoicePage.php" style="color: #fff;"><i class="fa fa-inr" style="color: #38bdf8; margin-right: 6px;"></i> INVOICES</a></li>
                        <?php } ?>
                        <?php if ($canAccessReceivedPayment) { ?>
                        <li style="padding-left: 8px;"><a href="receivedPayments.php" style="color: #fff;"><i class="fa fa-check-circle" style="color: #34d399; margin-right: 6px;"></i> RECEIVED PAYMENT</a></li>
                        <?php } ?>
                        <?php if ($canAccessPaymentDue) { ?>
                        <li style="padding-left: 8px;"><a href="paymentPage.php" style="color: #fff;"><i class="fa fa-money" style="color: #fbbf24; margin-right: 6px;"></i> DUE PAYMENT</a></li>
                        <?php } ?>
                        <?php if ($canAccessTds) { ?>
                        <li style="padding-left: 8px;"><a href="listOfTds.php" style="color: #fff;"><i class="fa fa-list" style="color: #a78bfa; margin-right: 6px;"></i> TDS DEDUCTED</a></li>
                        <?php } ?>
                        <?php } ?>

                        <?php if ($canAccessPurchase) { ?>
                        <?php if ($canAccessSales) { ?>
                        <li class="divider" style="margin: 5px 0; background: rgba(255,255,255,0.12);"></li>
                        <?php } ?>
                        <li class="dropdown-header" style="color: #f472b6; font-weight: 700; font-size: 11px; padding: 6px 15px; letter-spacing: 0.6px; text-transform: uppercase;">
                            <i class="fa fa-truck" style="margin-right: 4px;"></i> PURCHASE
                        </li>
                        <?php if ($canAccessBills) { ?>
                        <li style="padding-left: 8px;"><a href="billsPage.php" style="color: #fff;"><i class="fa fa-file-text-o" style="color: #f472b6; margin-right: 6px;"></i> BILLS</a></li>
                        <?php } ?>
                        <?php if ($canAccessCreditors) { ?>
                        <li style="padding-left: 8px;"><a href="suppliers.php" style="color: #fff;"><i class="fa fa-address-card-o" style="color: #fbbf24; margin-right: 6px;"></i> SUPPLIERS</a></li>
                        <?php } ?>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
                <?php if ($canAccessAuditGroup) { ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #a855f7; font-weight: 700;">
                        <i class="fa fa-calendar-check-o" style="color: #a855f7; margin-right: 5px;"></i> AUDIT PLANNING <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu" style="background: #082d54; border: 1px solid rgba(255,255,255,0.15);">
                        <?php if ($canAccessAuditList) { ?>
                        <li><a href="auditPage.php" style="color: #fff;"><i class="fa fa-list-alt" style="color: #38bdf8; margin-right: 6px;"></i> AUDIT LIST</a></li>
                        <?php } ?>
                        <?php if ($canAccessAuditPlanning) { ?>
                        <li><a href="auditPlanning.php" style="color: #fff;"><i class="fa fa-calendar-check-o" style="color: #a855f7; margin-right: 6px;"></i> AUDIT PLANNING</a></li>
                        <?php } ?>
                        <?php if ($canAccessAuditData) { ?>
                        <li><a href="auditorChecklist.php" style="color: #fff;"><i class="fa fa-clipboard" style="color: #34d399; margin-right: 6px;"></i> AUDIT DATA</a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
                <?php if ($canAccessKpiGroup) { ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #06b6d4; font-weight: 700;">
                        <i class="fa fa-bar-chart" style="color: #06b6d4; margin-right: 5px;"></i> KPI ANALYSIS <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu" style="background: #082d54; border: 1px solid rgba(255,255,255,0.15);">
                        <?php if ($canAccessAuditPerformance) { ?>
                        <li><a href="qualityanalysis.php" style="color: #fff;"><i class="fa fa-line-chart" style="color: #38bdf8; margin-right: 6px;"></i> AUDIT PERFORMANCE</a></li>
                        <?php } ?>
                        <?php if ($canAccessAppRevObjective) { ?>
                        <li><a href="apprevobjective.php" style="color: #fff;"><i class="fa fa-eye" style="color: #fbbf24; margin-right: 6px;"></i> APP. REVIEW OBJECTIVE</a></li>
                        <?php } ?>
                        <?php if ($canAccessDocUploadStatus) { ?>
                        <li><a href="uploadingStatus.php" style="color: #fff;"><i class="fa fa-cloud-upload" style="color: #34d399; margin-right: 6px;"></i> DOC UPLOAD STATUS</a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
                <li><a href="clientList.php">CLIENT LIST</a></li>
            </ul>
            <?php } ?>
        </div>
    </div>
</nav>

<style>
    /* Navbar Base */
    .modern-navbar {
        background: #ffffff;
        border: none;
        border-bottom: 1px solid #e5e7eb;
        height: 72px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        min-height: 72px;
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        z-index: 999;
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .navbar-container {
        height: 100%;
        padding: 0;
        margin-left: 250px;
    }

    .navbar-content {
        display: grid;
        grid-template-columns: 200px 1fr auto;
        align-items: center;
        height: 72px;
        padding: 0 30px;
        gap: 40px;
    }

    /* Left Section - Add Client Button */
    .navbar-left {
        display: flex;
        align-items: center;
        grid-column: 1;
        height: 72px;
        margin-left: 0;
        padding-top: 0;
    }

    .modern-add-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 48px;
        padding: 0 20px;
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 14px;
        font-family: 'Inter', 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.24);
        white-space: nowrap;
        letter-spacing: -0.01em;
    }

    .modern-add-btn:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #6d28d9 100%);
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.36);
        text-decoration: none;
        color: #ffffff !important;
    }

    .modern-add-btn:active {
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.24);
    }

    .modern-add-btn i {
        font-size: 15px;
    }

    .add-new-dropdown {
        position: relative;
    }

    /* ---- Premium "Add New" action dropdown ----
   .add-new-menu is the Bootstrap/Popper positioning shell (kept transparent so
   Popper's inline transform never conflicts with our open animation).
   .add-new-panel carries all the visuals and the open animation. */
    .add-new-menu {
        min-width: 336px;
        margin: 0;
        padding: 0;
        border: 0;
        background: transparent;
        box-shadow: none;
        z-index: 10001;
    }

    .add-new-panel {
        margin-top: 10px;
        width: 336px;
        border: 1px solid #e6ebf3;
        border-radius: 18px;
        padding: 8px;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.05), 0 18px 44px rgba(15, 23, 42, 0.14);
    }

    /* Open animation: fade + slide-down + slight scale */
    .add-new-menu.show .add-new-panel {
        transform-origin: top center;
        animation: addNewMenuIn 0.24s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes addNewMenuIn {
        from {
            opacity: 0;
            transform: translateY(-8px) scale(0.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Each action row: icon chip + title/subtitle + chevron */
    .add-new-panel>a.add-new-item {
        display: flex;
        align-items: center;
        gap: 13px;
        height: 64px;
        padding: 0 12px;
        border-radius: 13px;
        color: #0f172a;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.22s cubic-bezier(0.4, 0, 0.2, 1),
            transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .add-new-panel>a.add-new-item+a.add-new-item {
        margin-top: 4px;
    }

    .add-new-ico {
        width: 46px;
        height: 46px;
        flex: 0 0 auto;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 18px;
        box-shadow: 0 5px 13px rgba(15, 23, 42, 0.14);
        transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .add-new-text {
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 1.3;
        min-width: 0;
        flex: 1 1 auto;
    }

    .add-new-title {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .add-new-sub {
        font-size: 11.5px;
        font-weight: 500;
        color: #94a3b8;
        margin-top: 1px;
    }

    .add-new-arrow {
        flex: 0 0 auto;
        color: #cbd5e1;
        font-size: 16px;
        opacity: 0;
        transform: translateX(-4px);
        transition: opacity 0.22s ease, transform 0.22s ease;
    }

    .add-new-item:hover .add-new-arrow {
        opacity: 1;
        transform: translateX(0);
    }

    .add-new-item:hover .add-new-ico {
        transform: scale(1.08);
    }

    /* Client = blue */
    .add-new-item-client .add-new-ico {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .add-new-panel>a.add-new-item-client:hover {
        background: #eff6ff;
        color: #1d4ed8;
        transform: translateX(3px);
    }

    .add-new-item-client:hover .add-new-arrow {
        color: #1d4ed8;
    }

    /* Supplier = orange */
    .add-new-item-supplier .add-new-ico {
        background: linear-gradient(135deg, #f5821f 0%, #d9631a 100%);
    }

    .add-new-panel>a.add-new-item-supplier:hover {
        background: #fff5eb;
        color: #c2620f;
        transform: translateX(3px);
    }

    .add-new-item-supplier:hover .add-new-arrow {
        color: #c2620f;
    }

    .add-new-item-supplier:hover .add-new-sub,
    .add-new-item-client:hover .add-new-sub {
        color: inherit;
        opacity: 0.7;
    }

    /* Dark theme */
    html.theme-dark .add-new-panel {
        background: #111827;
        border-color: #1f2937;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4), 0 18px 44px rgba(0, 0, 0, 0.5);
    }

    html.theme-dark .add-new-panel>a.add-new-item {
        color: #e5e7eb;
    }

    html.theme-dark .add-new-sub {
        color: #64748b;
    }

    html.theme-dark .add-new-item-client:hover {
        background: rgba(37, 99, 235, 0.16);
        color: #93c5fd;
    }

    html.theme-dark .add-new-item-supplier:hover {
        background: rgba(245, 130, 31, 0.16);
        color: #fdba74;
    }

    /* Responsive */
    @media (max-width: 420px) {
        .add-new-menu {
            min-width: 300px;
        }

        .add-new-panel {
            width: 300px;
        }
    }

    /* Center Section - Search */
    .navbar-center {
        display: flex;
        justify-content: flex-start;
        grid-column: 2;
        padding: 0 20px;
    }

    .search-wrapper {
        position: relative;
        width: 100%;
        max-width: 520px;
        isolation: isolate;
    }

    .search-wrapper::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.18), rgba(124, 58, 237, 0.16));
        opacity: 0;
        transform: scale(0.98);
        transition: all 0.25s ease;
        z-index: -1;
    }

    .search-wrapper:focus-within::before {
        opacity: 1;
        transform: scale(1);
    }

    .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transform: translateY(-50%);
        color: #2563eb;
        font-size: 15px;
        pointer-events: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #eff6ff 0%, #eef2ff 100%);
        box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.08);
        transition: all 0.25s ease;
    }

    .search-wrapper:focus-within .search-icon {
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
    }

    .modern-search {
        width: 100%;
        height: 48px;
        padding: 0 22px 0 62px;
        border: 1px solid #dbe3ef;
        border-radius: 16px;
        font-family: 'Inter', 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 500;
        color: #0f172a;
        background: #ffffff;
        transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.75);
        letter-spacing: -0.01em;
    }

    .modern-search::placeholder {
        color: #8a98ad;
        font-weight: 500;
    }

    .modern-search:hover {
        border-color: #b9c6da;
        background: #fbfdff;
    }

    .modern-search:focus {
        outline: none;
        border-color: #7aa2f7;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12), 0 14px 34px rgba(37, 99, 235, 0.12);
    }

    .clientlistappend {
        width: 100%;
        max-height: 320px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);
        margin-top: 10px;
        padding: 6px;
        background: #ffffff;
        position: absolute;
        z-index: 9999;
    }

    .client-list {
        padding: 11px 14px !important;
        cursor: pointer;
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
        border-radius: 10px;
        color: #334155;
        font-size: 14px;
        font-weight: 500;
    }

    .client-list:last-child {
        border-bottom: none;
    }

    .client-list:hover,
    .client-list.active-item {
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%) !important;
        color: #ffffff !important;
    }

    /* Right Section - Separate Cards */
    .navbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
        grid-column: 3;
    }

    /* Base Card Style */
    .modern-card-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        text-decoration: none;
    }

    .modern-card-btn:hover {
        background: #f9fafb;
        border-color: #d1d5db;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        text-decoration: none;
    }

    /* Dark Mode Card */
    .modern-card-btn:not(.modern-profile-card):not(.modern-logout-card) {
        width: 44px;
        height: 44px;
        color: #374151;
        font-size: 17px;
    }

    /* Profile Card */
    .modern-user-dropdown {
        position: relative;
    }

    .modern-profile-card {
        gap: 10px;
        padding: 6px 14px 6px 6px;
        min-width: 160px;
        color: #1f2937 !important;
    }

    .user-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #ffffff;
        flex-shrink: 0;
        font-size: 15px;
    }

    .user-info {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 0;
    }

    .user-greeting {
        font-size: 10px;
        color: #9ca3af;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1.2;
    }

    .user-name {
        font-size: 13px;
        color: #1f2937;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
    }

    .dropdown-arrow {
        font-size: 9px;
        color: #9ca3af;
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }

    .modern-user-dropdown.open .dropdown-arrow {
        transform: rotate(180deg);
    }

    /* Logout Card */
    .modern-logout-card {
        gap: 7px;
        padding: 10px 16px;
        background: #fef2f2 !important;
        border-color: #fee2e2;
        color: #dc2626 !important;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
    }

    .modern-logout-card:hover {
        background: #fee2e2 !important;
        border-color: #fecaca;
        color: #b91c1c !important;
    }

    .modern-logout-card i {
        font-size: 14px;
    }

    .modern-dropdown-menu {
        min-width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-top: 8px;
        padding: 6px;
        background: #ffffff;
    }

    .modern-dropdown-menu li {
        list-style: none;
    }

    .modern-dropdown-menu li a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        color: #374151;
        text-decoration: none;
        border-radius: 6px;
        transition: all 0.2s ease;
        font-size: 13px;
        font-weight: 500;
    }

    .modern-dropdown-menu li a:hover {
        background: #f3f4f6;
        color: #6366f1;
    }

    .modern-dropdown-menu li a i {
        font-size: 13px;
        width: 14px;
    }

    /* Mobile Toggle */
    .navbar-toggle {
        display: none;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 10px;
        background: #ffffff;
        margin: 10px 0;
    }

    .navbar-toggle .icon-bar {
        background-color: #374151;
        height: 2px;
        border-radius: 1px;
    }

    /* Mobile Menu */
    .navbar-collapse {
        display: none;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 10px 0;
        position: absolute;
        top: 64px;
        left: 0;
        right: 0;
    }

    .navbar-collapse.in {
        display: block;
    }

    .navbar-collapse .nav {
        margin: 0;
    }

    .navbar-collapse .nav>li>a {
        padding: 14px 24px;
        color: #374151;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .navbar-collapse .nav>li>a:hover {
        background: #f9fafb;
        color: #4f46e5;
    }

    /* Responsive Design */
    @media (max-width: 991px) {
        .navbar-container {
            margin-left: 0;
        }

        .navbar-content {
            flex-wrap: wrap;
            height: auto;
            padding: 12px 20px;
        }

        .navbar-toggle {
            display: block;
        }

        .navbar-left {
            order: 2;
            width: 100%;
            justify-content: center;
            margin: 12px 0 0 0;
        }

        .navbar-center {
            order: 1;
            width: 100%;
            max-width: 100%;
            margin: 0;
        }

        .navbar-right {
            order: 3;
            width: 100%;
            justify-content: space-between;
            margin-top: 12px;
        }
    }

    @media (max-width: 768px) {

        .modern-add-btn span,
        .modern-logout-card span {
            display: none;
        }

        .modern-add-btn {
            padding: 10px 12px;
            min-width: 44px;
        }

        .modern-profile-card {
            min-width: auto;
            max-width: 140px;
        }

        .user-name {
            max-width: 70px;
        }
    }

    @media (max-width: 480px) {
        .navbar-container {
            padding: 0 16px;
        }

        .navbar-right {
            gap: 8px;
        }

        .modern-icon-btn {
            width: 38px;
            height: 38px;
        }
    }
</style>

<script type="text/javascript">
    $(document).ready(function () {
        var selectedIndex = -1;

        $(".searchcustomer").keydown(function (e) {
            var items = $(".clientlistappend .client-list");

            // Arrow Down - Navigate down
            if (e.keyCode === 40) {
                e.preventDefault();
                if (selectedIndex < items.length - 1) {
                    selectedIndex++;
                    updateSelection(items);
                }
            }
            // Arrow Up - Navigate up  
            else if (e.keyCode === 38) {
                e.preventDefault();
                if (selectedIndex > 0) {
                    selectedIndex--;
                    updateSelection(items);
                }
            }
            // Enter - Select highlighted item
            else if (e.keyCode === 13) {
                e.preventDefault();
                if (selectedIndex >= 0 && selectedIndex < items.length) {
                    $(items[selectedIndex]).click();
                }
            }
            // Escape - Close dropdown
            else if (e.keyCode === 27) {
                $(".clientlistappend").empty();
                selectedIndex = -1;
            }
        });

        function updateSelection(items) {
            items.removeClass('active-item');
            if (selectedIndex >= 0 && selectedIndex < items.length) {
                $(items[selectedIndex]).addClass('active-item');
                // Scroll to the selected item
                var dropdown = $(".clientlistappend");
                var item = $(items[selectedIndex]);
                var itemTop = item.position().top;
                var itemHeight = item.outerHeight();
                var dropdownHeight = dropdown.height();
                var scrollTop = dropdown.scrollTop();

                if (itemTop + itemHeight > dropdownHeight) {
                    dropdown.scrollTop(scrollTop + itemTop + itemHeight - dropdownHeight);
                } else if (itemTop < 0) {
                    dropdown.scrollTop(scrollTop + itemTop);
                }
            }
        }

        $(".searchcustomer").keyup(function (e) {
            // Skip if arrow keys or enter
            if (e.keyCode === 38 || e.keyCode === 40 || e.keyCode === 13 || e.keyCode === 27) {
                return;
            }

            selectedIndex = -1; // Reset selection on new search

            $.ajax({
                url: 'autoSearch.php',
                type: 'GET',
                dataType: "json",
                data: {
                    customersearch: $(this).val(),
                },
                success: function (data) {
                    $(".clientlistappend").empty();
                    //                    console.log(data);
                    for (var key in data) {
                        var isSupplier = data[key]['customer_category'] === 'supplier';
                        var label = isSupplier ? '<span style="float:right; background:#ede9fe; color:#5b21b6; border-radius:999px; padding:2px 7px; font-size:10px; font-weight:700;">SUPPLIER</span>' : '';
                        var itemStyle = isSupplier ? 'text-align: left; padding: 8px 12px; cursor: pointer; border-left: 4px solid #6d28d9;' : 'text-align: left; padding: 8px 12px; cursor: pointer;';
                        $(".clientlistappend").append('<li class="client-list" style="' + itemStyle + '" data-id="' + data[key]['id'] + '">' + data[key]['customer_name'] + label + '</li>');
                    }

                    // Show dropdown if there are results
                    if (data.length > 0) {
                        $(".clientlistappend").show();
                    }
                }
            });
        });

        $('body').on("click", ".client-list", function () {
            var customerID = $(this).attr('data-id');
            //            alert(customerID);
            // open popup
            //            localStorage['visited'] = customerID;
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                '</form>');
            $('body').append(form);
            form.submit();
            //        alert("Atleast one action item required");
            //            $(this).parent("div.deleteNewAuditParentEdit").parent('div.newAuditRowParentEdit').remove();
        });
        //        alert(window.parent.document.body.style.zoom = 0.9);
        $('.addAccountCss').click(function () {
            $("#customerAdd").modal("show");
        });

        $('.passwordChange').click(function () {
            $('#changeUserPassword').modal("show");
        });



    });
</script>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" role="dialog" aria-labelledby="logoutConfirmLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutConfirmLabel">Confirm Logout</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p>Are you sure you want to logout?</p>
            </div>
            <div class="modal-footer logout-footer">
                <button type="button" class="btn logout-btn-cancel" data-dismiss="modal">Cancel</button>
                <a href="logout.php" class="btn logout-btn-confirm">Yes, Logout</a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal Backdrop with Blur */
    .modal-backdrop.show {
        opacity: 1 !important;
        background: rgba(0, 0, 0, 0.8) !important;
        backdrop-filter: blur(8px) brightness(0.6);
        -webkit-backdrop-filter: blur(8px) brightness(0.6);
    }

    /* Remove default Bootstrap backdrop */
    .modal-backdrop.fade {
        opacity: 0;
    }

    .modal-backdrop.fade.show {
        opacity: 1 !important;
    }

    /* Modal Animation */
    @keyframes modalSlideIn {
        from {
            transform: translateY(30px) scale(0.98);
            opacity: 0;
        }

        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    #logoutConfirmModal .modal-dialog {
        max-width: 380px;
        margin: 10vh auto;
        animation: modalSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        transform-origin: center center;
        font-family: 'Segoe UI', 'Roboto', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
    }

    #logoutConfirmModal .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 60px -10px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        transform: translateZ(0);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        background: #fff;
        position: relative;
        z-index: 1;
    }

    #logoutConfirmModal .modal-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4f46e5, #7c3aed);
    }

    #logoutConfirmModal .modal-header {
        border: none;
        padding: 24px 28px 16px;
        background: #fff;
        position: relative;
    }

    #logoutConfirmModal .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        letter-spacing: -0.4px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Segoe UI', 'Roboto', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    #logoutConfirmModal .modal-title::before {
        content: '⚠️';
        font-size: 22px;
    }

    #logoutConfirmModal .modal-body {
        padding: 12px 28px 24px;
        font-size: 15.5px;
        line-height: 1.7;
        background: #fff;
        color: #475569;
        text-align: center;
    }

    #logoutConfirmModal .modal-body p {
        margin: 0;
        color: #475569;
        font-size: 16px;
        line-height: 1.7;
        font-family: 'Segoe UI', 'Roboto', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
        font-weight: 400;
        letter-spacing: 0.2px;
    }

    #logoutConfirmModal .logout-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding: 20px 28px 24px;
        text-align: center;
        background: #fcfcfd;
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    /* Button Styles */
    #logoutConfirmModal .btn {
        min-width: 120px;
        border-radius: 10px;
        font-size: 14.5px;
        font-weight: 500;
        padding: 11px 24px;
        letter-spacing: 0.3px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        transform: translateZ(0);
        border: none;
        font-weight: 600;
    }

    #logoutConfirmModal .btn:active {
        transform: translateY(1px) !important;
    }

    /* Cancel Button - Red */
    #logoutConfirmModal .logout-btn-cancel {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        color: #b91c1c;
        border: 1px solid #fecaca;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.15);
        transition: all 0.3s ease;
    }

    #logoutConfirmModal .logout-btn-cancel:hover {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        color: #991b1b;
        border-color: #fca5a5;
    }

    /* Confirm Button - Purple */
    #logoutConfirmModal .logout-btn-confirm {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    #logoutConfirmModal .logout-btn-confirm::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #4338ca, #6d28d9);
        z-index: -1;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    #logoutConfirmModal .logout-btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
    }

    #logoutConfirmModal .logout-btn-confirm:hover::before {
        opacity: 1;
    }

    /* Close Button */
    #logoutConfirmModal .close {
        font-size: 22px;
        font-weight: 300;
        opacity: 0.6;
        transition: all 0.2s ease;
        padding: 6px 10px;
        margin: -12px -10px -6px auto;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.03);
    }

    #logoutConfirmModal .close:hover {
        opacity: 1;
        background: rgba(0, 0, 0, 0.06);
        transform: rotate(90deg);
    }

    #logoutConfirmModal .close:hover {
        opacity: 1;
        background: rgba(255, 255, 255, 0.15);
    }

    /* Modal Content Styling */
    #logoutConfirmModal .modal-body:before {
        content: '⚠️';
        display: block;
        font-size: 36px;
        text-align: center;
        margin-bottom: 15px;
        opacity: 0.9;
    }
</style>

<!-- ===== PENDING LETTER WARNING POPUP SYSTEM ===== -->
<!-- Modal for warning about failed automation mails -->
<div class="modal fade" id="pendingLetterWarningModal" tabindex="-1" role="dialog" aria-labelledby="pendingLetterWarningLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
            <!-- Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; padding: 18px 24px;">
                <h4 class="modal-title" id="pendingLetterWarningLabel" style="color: #fff; font-weight: 700; display: flex; align-items: center; gap: 10px; margin: 0; font-size: 18px;">
                    <span style="font-size: 28px;">⚠️</span> Automation Mail Failed - Action Required!
                </h4>
                <button type="button" class="close pending-letter-dismiss-btn" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.8; text-shadow: none; font-size: 28px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Body -->
            <div class="modal-body" style="padding: 24px; background: #fff;">
                <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                    <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;">
                        <strong>⚠️ Warning:</strong> Automation mail <strong>failed</strong> due to some reason for the following client(s). 
                        Kindly send letters from the <strong>"Letters and Action"</strong> section in the client account OR from the 
                        <strong><a href="intimationLetter.php" style="color: #0d6efd; text-decoration: underline;">Intimation Due Page</a></strong>. 
                        <br><br>
                        <strong style="color: #dc3545;">⛔ It is MANDATORY to send these letters, otherwise the status of our clients can be mismatched!</strong>
                    </p>
                </div>
                
                <!-- Pending letters table -->
                <div style="max-height: 350px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px;">
                    <table class="table table-striped table-hover" style="margin-bottom: 0; font-size: 13px;" id="pendingLetterTable">
                        <thead style="background: #053c6d; color: #fff; position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th style="padding: 12px 10px; font-weight: 600;">#</th>
                                <th style="padding: 12px 10px; font-weight: 600;">Client Name</th>
                                <th style="padding: 12px 10px; font-weight: 600;">Unique ID</th>
                                <th style="padding: 12px 10px; font-weight: 600;">Letter Type</th>
                                <th style="padding: 12px 10px; font-weight: 600;">Due Date</th>
                                <th style="padding: 12px 10px; font-weight: 600;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="pendingLetterTableBody">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 16px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 12px 16px;">
                    <p style="margin: 0; color: #721c24; font-size: 13px;">
                        🔄 This popup will appear every <strong>20 minutes</strong> until all pending letters are sent.
                        <br>Once you send the letter(s), this warning will automatically stop.
                    </p>
                </div>
            </div>
            <!-- Footer -->
            <div class="modal-footer" style="border-top: 1px solid #eee; padding: 14px 24px; background: #f8f9fa; display: flex; gap: 10px; justify-content: space-between;">
                <a href="intimationLetter.php" class="btn" style="background: linear-gradient(135deg, #28a745, #218838); color: #fff; border: none; border-radius: 8px; padding: 10px 24px; font-weight: 600; font-size: 14px;">
                    📩 Go to Intimation Due Page
                </a>
                <button type="button" class="btn pending-letter-dismiss-btn" data-dismiss="modal" style="background: linear-gradient(135deg, #6c757d, #5a6268); color: #fff; border: none; border-radius: 8px; padding: 10px 24px; font-weight: 600; font-size: 14px;">
                    ⏰ Remind Me in 20 Minutes
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Styles for the warning popup -->
<style>
    #pendingLetterWarningModal .modal-dialog {
        margin-top: 80px;
    }
    #pendingLetterWarningModal .modal-content {
        animation: pendingLetterShake 0.5s ease-in-out;
    }
    @keyframes pendingLetterShake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
        20%, 40%, 60%, 80% { transform: translateX(4px); }
    }
    #pendingLetterTable tbody tr:hover {
        background: #fff3cd !important;
    }
    #pendingLetterTable .badge-letter {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    #pendingLetterTable .badge-suspend { background: #dc3545; color: #fff; }
    #pendingLetterTable .badge-withdraw { background: #6f42c1; color: #fff; }
    #pendingLetterTable .badge-expire { background: #fd7e14; color: #fff; }
    #pendingLetterTable .badge-reminder { background: #ffc107; color: #333; }
</style>

<script>
/**
 * PENDING LETTER WARNING SYSTEM
 * Checks every 20 minutes for overdue unsent letters.
 * Shows a warning popup if automation failed to send any letter.
 * Stops showing only when all letters are sent (manually or automatically).
 */
(function() {
    var PENDING_CHECK_INTERVAL = 20 * 60 * 1000; // 20 minutes in milliseconds
    var pendingLetterTimer = null;
    var isModalCurrentlyShown = false;

    function getLetterBadgeClass(action) {
        if (action === 'Suspend Letter') return 'badge-suspend';
        if (action === 'Withdraw Letter' || action === 'Withdrawal Letter') return 'badge-withdraw';
        if (action === 'Expire Letter') return 'badge-expire';
        return 'badge-reminder';
    }

    function formatDate(dateStr) {
        if (!dateStr || dateStr === '0000-00-00') return '-';
        var parts = dateStr.split('-');
        if (parts.length === 3) return parts[2] + '.' + parts[1] + '.' + parts[0];
        return dateStr;
    }

    function checkPendingLetters() {
        $.ajax({
            url: 'checkPendingLetters.php',
            method: 'GET',
            dataType: 'json',
            cache: false,
            success: function(data) {
                if (data && data.pending && data.pending.length > 0) {
                    showPendingLetterWarning(data.pending);
                } else {
                    // No pending letters - hide modal if showing
                    if (isModalCurrentlyShown) {
                        $('#pendingLetterWarningModal').modal('hide');
                        isModalCurrentlyShown = false;
                    }
                }
            },
            error: function() {
                // Silently fail - don't bother admin with AJAX errors
                console.log('[PendingLetters] Check failed, will retry in 20 min.');
            }
        });
    }

    function showPendingLetterWarning(pendingList) {
        var tbody = $('#pendingLetterTableBody');
        tbody.empty();

        for (var i = 0; i < pendingList.length; i++) {
            var item = pendingList[i];
            var badgeClass = getLetterBadgeClass(item.action);
            var row = '<tr>' +
                '<td style="padding: 10px; vertical-align: middle; font-weight: 600;">' + (i + 1) + '</td>' +
                '<td style="padding: 10px; vertical-align: middle; font-weight: 600; color: #053c6d;">' + 
                    escapeHtml(item.customer_name) + '</td>' +
                '<td style="padding: 10px; vertical-align: middle;">' + escapeHtml(item.unique_id || '-') + '</td>' +
                '<td style="padding: 10px; vertical-align: middle;">' +
                    '<span class="badge-letter ' + badgeClass + '">' + escapeHtml(item.action) + '</span></td>' +
                '<td style="padding: 10px; vertical-align: middle;">' + formatDate(item.due_date) + '</td>' +
                '<td style="padding: 10px; vertical-align: middle;">' +
                    '<a href="customerAccount.php?customerid=' + item.customer_id + '" ' +
                    'class="btn btn-sm" style="background: #053c6d; color: #fff; border-radius: 6px; font-size: 11px; padding: 4px 12px;" ' +
                    'title="Open client Letters & Action">' +
                    '📋 Letters & Action</a></td>' +
                '</tr>';
            tbody.append(row);
        }

        // Show the modal
        if (!isModalCurrentlyShown) {
            $('#pendingLetterWarningModal').modal('show');
            isModalCurrentlyShown = true;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // When modal is dismissed (either button), set flag so it comes back in 20 min
    $(document).on('click', '.pending-letter-dismiss-btn', function() {
        isModalCurrentlyShown = false;
    });

    // Also handle modal hidden event
    $('#pendingLetterWarningModal').on('hidden.bs.modal', function() {
        isModalCurrentlyShown = false;
    });

    // Initialize on page load
    $(document).ready(function() {
        // First check after 3 seconds (let page load first)
        setTimeout(function() {
            checkPendingLetters();
        }, 3000);

        // Then check every 20 minutes
        pendingLetterTimer = setInterval(function() {
            checkPendingLetters();
        }, PENDING_CHECK_INTERVAL);
    });
})();
</script>
<!-- ===== END PENDING LETTER WARNING POPUP SYSTEM ===== -->

<!-- ===== SIDEBAR FLYOUT DROPDOWN SUBMENU POSITIONING SCRIPT ===== -->
<script>
(function() {
    function initSidebarFlyout() {
        var $dropdownGroups = $('.sidebar-dropdown-group');
        if (!$dropdownGroups.length) return;

        $dropdownGroups.each(function() {
            var $group = $(this);
            var $flyout = $group.find('.sidebar-flyout-menu');
            if (!$flyout.length) return;

            var closeTimer = null;

            function positionFlyout() {
                var groupEl = $group[0];
                var rect = groupEl.getBoundingClientRect();
                var top = rect.top;
                var left = rect.right;
                var winHeight = $(window).height();
                var flyoutHeight = $flyout.outerHeight() || 190;

                // Prevent overflowing below window
                if (top + flyoutHeight > winHeight - 10) {
                    top = Math.max(10, winHeight - flyoutHeight - 10);
                }

                $flyout.css({
                    'top': top + 'px',
                    'left': (left - 2) + 'px'
                });
            }

            function openMenu() {
                clearTimeout(closeTimer);
                // Immediately close any other open dropdown groups
                $dropdownGroups.not($group).each(function() {
                    $(this).removeClass('flyout-hover').find('.sidebar-flyout-menu').removeClass('show-flyout');
                });
                positionFlyout();
                $group.addClass('flyout-hover');
                $flyout.addClass('show-flyout');
            }

            function closeMenu(delay) {
                clearTimeout(closeTimer);
                closeTimer = setTimeout(function() {
                    $group.removeClass('flyout-hover');
                    $flyout.removeClass('show-flyout');
                    $flyout.find('.nested-flyout-group').removeClass('subflyout-open');
                    $flyout.find('.sidebar-subflyout-menu').removeClass('show-subflyout');
                }, typeof delay === 'number' ? delay : 300);
            }

            // Group hover events
            $group.on('mouseenter', openMenu);
            $group.on('mouseleave', function() {
                closeMenu(300);
            });

            // Flyout hover events: hovering flyout directly keeps it open
            $flyout.on('mouseenter', openMenu);
            $flyout.on('mouseleave', function() {
                closeMenu(250);
            });

            // Nested subflyout handling (Sales / Purchase nested groups)
            var $nestedGroups = $flyout.find('.nested-flyout-group');
            if ($nestedGroups.length) {
                $nestedGroups.each(function() {
                    var $nestedGroup = $(this);
                    var $subflyout = $nestedGroup.find('.sidebar-subflyout-menu');
                    if (!$subflyout.length) return;

                    var subCloseTimer = null;

                    function positionSubflyout() {
                        var nestedEl = $nestedGroup[0];
                        var rect = nestedEl.getBoundingClientRect();
                        var winHeight = $(window).height();
                        var subHeight = $subflyout.outerHeight() || 180;
                        if (rect.top + subHeight > winHeight - 10) {
                            var diff = (rect.top + subHeight) - (winHeight - 10);
                            $subflyout.css('top', (-6 - diff) + 'px');
                        } else {
                            $subflyout.css('top', '-6px');
                        }
                    }

                    function openSubMenu() {
                        clearTimeout(subCloseTimer);
                        clearTimeout(closeTimer); // keep parent Level 1 open!
                        // close sibling submenus
                        $nestedGroups.not($nestedGroup).each(function() {
                            $(this).removeClass('subflyout-open').find('.sidebar-subflyout-menu').removeClass('show-subflyout');
                        });
                        positionSubflyout();
                        $nestedGroup.addClass('subflyout-open');
                        $subflyout.addClass('show-subflyout');
                    }

                    function closeSubMenu(delay) {
                        clearTimeout(subCloseTimer);
                        subCloseTimer = setTimeout(function() {
                            $nestedGroup.removeClass('subflyout-open');
                            $subflyout.removeClass('show-subflyout');
                        }, typeof delay === 'number' ? delay : 250);
                    }

                    $nestedGroup.on('mouseenter', openSubMenu);
                    $nestedGroup.on('mouseleave', function() {
                        closeSubMenu(250);
                    });

                    $subflyout.on('mouseenter', function() {
                        clearTimeout(subCloseTimer);
                        clearTimeout(closeTimer); // keep parent Level 1 open!
                        $nestedGroup.addClass('subflyout-open');
                        $subflyout.addClass('show-subflyout');
                    });
                    $subflyout.on('mouseleave', function() {
                        closeSubMenu(250);
                    });

                    // Click toggle on nested item (Sales / Purchase)
                    $nestedGroup.find('> a.nested-trigger-link').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if ($subflyout.hasClass('show-subflyout')) {
                            closeSubMenu(0);
                        } else {
                            openSubMenu();
                        }
                    });
                });
            }

            // If user deliberately hovers another sidebar item, close faster
            $('#sidebar > li:not(.sidebar-dropdown-group)').on('mouseenter', function() {
                closeMenu(80);
            });

            // Keep aligned if user scrolls the sidebar
            $('#sidebar-wrapper').on('scroll', function() {
                if ($flyout.hasClass('show-flyout')) {
                    positionFlyout();
                }
            });

            // Touch / click toggle support
            $group.find('> a').on('click', function(e) {
                e.preventDefault();
                if ($flyout.hasClass('show-flyout')) {
                    closeMenu(0);
                } else {
                    openMenu();
                }
            });
        });

        // Close when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.sidebar-dropdown-group, .sidebar-flyout-menu, .sidebar-subflyout-menu').length) {
                $('.sidebar-dropdown-group').removeClass('flyout-hover');
                $('.sidebar-flyout-menu').removeClass('show-flyout');
                $('.nested-flyout-group').removeClass('subflyout-open');
                $('.sidebar-subflyout-menu').removeClass('show-subflyout');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarFlyout);
    } else {
        initSidebarFlyout();
    }
})();
</script>
<!-- ===== END SIDEBAR FLYOUT SCRIPT ===== -->

<script>
    document.addEventListener('click', function (event) {
        var disabledTaskManagerLink = event.target.closest('.task-manager-disabled-link');
        if (disabledTaskManagerLink) {
            event.preventDefault();
        }
    });
</script>

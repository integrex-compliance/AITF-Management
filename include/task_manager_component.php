<?php
/**
 * Task Manager Component (Microsoft To-Do Style Engine)
 * Clean white theme, fully interactive AJAX task management with date/reminder options.
 */

require_once __DIR__ . '/session_helper.php';
if (!isset($db) || !($db instanceof PDO)) {
    require_once dirname(__DIR__) . '/dbConnect.php';
}
require_once __DIR__ . '/rbac_helper.php';
require_once __DIR__ . '/staff_guidelines_lib.php';
require_once __DIR__ . '/task_lib.php';

date_default_timezone_set('Asia/Kolkata');

$currentLoggedInUid = (int)($_SESSION['id'] ?? 0);
$currentLoggedInRole = (string)($_SESSION['role'] ?? '');
$currentLoggedInName = (string)($_SESSION['username'] ?? 'User');

// Target user whose tasks are currently in view
if (!isset($targetDashboardUid)) {
    $targetDashboardUid = isset($currentActiveUid) ? (int)$currentActiveUid : (isset($userRow['user_id']) ? (int)$userRow['user_id'] : $currentLoggedInUid);
}
if (!isset($targetDashboardName)) {
    $targetDashboardName = isset($currentActiveUserName) ? $currentActiveUserName : (isset($userName) ? $userName : $currentLoggedInName);
}
if (!isset($targetDashboardRole)) {
    $targetDashboardRole = isset($currentActiveUserRole) ? $currentActiveUserRole : (isset($userRole) ? $userRole : $currentLoggedInRole);
}
$isViewingSelf = ($targetDashboardUid === $currentLoggedInUid);

// Check if current logged-in user is Top Mgmt / Admin / Manager
$actualRole = (string)($_SESSION['role'] ?? '');
$isTopMgmtUser = (!empty($isTopMgmt))
    || (strcasecmp(trim($actualRole), 'Top Mgmt') === 0)
    || (strcasecmp(trim($actualRole), 'Top Management') === 0)
    || aitf_can_manage_tasks($actualRole);

// Fetch initial tasks for target user
$initialTasks = [];
if (isset($db) && $db && $targetDashboardUid > 0) {
    $initialTasks = aitf_get_user_tasks($db, $targetDashboardUid, ['status' => 'all']);
}

// If user has no tasks yet in database, seed realistic starter items into the database so they are interactive
if (empty($initialTasks) && isset($db) && $db && $targetDashboardUid > 0) {
    aitf_create_task($db, [
        'title' => 'Review QMS Audit Documentation & Compliance Checklists',
        'assigned_to_user_id' => $targetDashboardUid,
        'assigned_to_name' => $targetDashboardName,
        'assigned_to_role' => $targetDashboardRole,
        'assigned_by_user_id' => ($currentLoggedInUid > 0 ? $currentLoggedInUid : $targetDashboardUid),
        'assigned_by_name' => 'Top Mgmt',
        'assigned_by_role' => 'Top Mgmt',
        'due_date' => date('Y-m-d', strtotime('+2 days')),
        'is_important' => 1
    ]);
    aitf_create_task($db, [
        'title' => 'Verify Stage-1 Assessment Reports & Client Feedback Files',
        'assigned_to_user_id' => $targetDashboardUid,
        'assigned_to_name' => $targetDashboardName,
        'assigned_to_role' => $targetDashboardRole,
        'assigned_by_user_id' => ($currentLoggedInUid > 0 ? $currentLoggedInUid : $targetDashboardUid),
        'assigned_by_name' => 'Top Mgmt',
        'assigned_by_role' => 'Top Mgmt',
        'due_date' => date('Y-m-d', strtotime('+5 days')),
        'is_important' => 0
    ]);
    aitf_create_task($db, [
        'title' => 'Submit monthly internal audit summary to Management',
        'assigned_to_user_id' => $targetDashboardUid,
        'assigned_to_name' => $targetDashboardName,
        'assigned_to_role' => $targetDashboardRole,
        'assigned_by_user_id' => ($currentLoggedInUid > 0 ? $currentLoggedInUid : $targetDashboardUid),
        'assigned_by_name' => 'Management',
        'assigned_by_role' => 'Management',
        'due_date' => date('Y-m-d', strtotime('+7 days')),
        'is_important' => 0
    ]);
    $seedDone = aitf_create_task($db, [
        'title' => 'Initial system setup and onboarding protocol review',
        'assigned_to_user_id' => $targetDashboardUid,
        'assigned_to_name' => $targetDashboardName,
        'assigned_to_role' => $targetDashboardRole,
        'assigned_by_user_id' => ($currentLoggedInUid > 0 ? $currentLoggedInUid : $targetDashboardUid),
        'assigned_by_name' => 'Top Mgmt',
        'assigned_by_role' => 'Top Mgmt',
        'due_date' => date('Y-m-d', strtotime('-1 days')),
        'is_important' => 0
    ]);
    if (!empty($seedDone['task']['id'])) {
        aitf_toggle_task_status($db, $seedDone['task']['id'], $targetDashboardUid, 'Top Mgmt');
    }

    $initialTasks = aitf_get_user_tasks($db, $targetDashboardUid, ['status' => 'all']);
}

$pendingTasks = [];
$completedTasks = [];
foreach ($initialTasks as $t) {
    if ($t['status'] === 'completed') {
        $completedTasks[] = $t;
    } else {
        $pendingTasks[] = $t;
    }
}

$pendingCount = count($pendingTasks);
$completedCount = count($completedTasks);
$totalCount = $pendingCount + $completedCount;
?>

<!-- Clean White Theme - Styles -->
<style>
.todo-app-wrapper {
    background: #ffffff;
    color: #1e293b;
    border-radius: 0 0 12px 12px;
    padding: 8px 18px 14px 18px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    border: 1px solid #e2e8f0;
    margin-top: -1px;
    margin-bottom: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    position: relative;
    min-height: 380px;
    display: flex;
    flex-direction: column;
}

/* Compact Filter Bar (Minimal height & width, Right-Aligned) */
.todo-compact-filter-bar {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
    margin-top: 2px;
    margin-bottom: 10px;
    padding: 0;
    pointer-events: auto;
    width: 100%;
}

.todo-filter-btn {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 11.5px;
    padding: 3px 11px;
    border-radius: 14px;
    cursor: pointer;
    font-weight: 600;
    line-height: 1.4;
    transition: all 0.15s ease;
    user-select: none;
}

.todo-filter-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
    border-color: #94a3b8;
}

.todo-filter-btn.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
}

/* Task Cards */
.todo-list-container {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.todo-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 9px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    cursor: pointer;
    user-select: none;
    transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}

.todo-card:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.todo-card.is-expanded {
    background: #fbfcfe;
    border-color: #cbd5e1;
    align-items: flex-start;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
}

.todo-card.is-expanded .todo-check-circle {
    margin-top: 1px;
}

.todo-check-circle {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
    background: #ffffff;
    border: 2px solid #cbd5e1;
    transition: all 0.15s ease;
    padding: 0;
}

.todo-check-circle:hover {
    border-color: #2563eb;
    background: #eff6ff;
}

.todo-card.is-completed .todo-check-circle {
    background: #10b981;
    border-color: #10b981;
    color: #ffffff;
}

.todo-card.is-completed .todo-check-circle:hover {
    background: #059669;
    border-color: #059669;
}

.todo-content {
    flex: 1;
    min-width: 0;
}

.todo-task-title {
    font-size: 13.5px;
    font-weight: 600;
    color: #0f172a;
    line-height: 1.35;
    word-break: break-word;
    margin: 0;
    transition: color 0.15s ease, text-decoration 0.15s ease;
}

.todo-card.is-completed .todo-task-title {
    color: #94a3b8;
    text-decoration: line-through;
}

.todo-meta-row {
    display: none; /* Collapsed by default */
    align-items: center;
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 7px;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #f1f5f9;
    white-space: nowrap;
    scrollbar-width: none;
}
.todo-meta-row::-webkit-scrollbar {
    display: none;
    height: 0;
}

.todo-meta-pill {
    height: 26px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0 9px;
    border-radius: 5px;
    font-size: 11px;
    line-height: 1;
    font-weight: 500;
    box-sizing: border-box;
    white-space: nowrap;
    flex-shrink: 0;
    vertical-align: middle;
}

.todo-meta-pill i {
    font-size: 11px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 13px;
    text-align: center;
}

.todo-meta-pill strong {
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
}

/* Assigned Date Badge */
.todo-meta-pill.pill-assigned-date {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
}
.todo-meta-pill.pill-assigned-date i {
    color: #2563eb;
}
.todo-meta-pill.pill-assigned-date strong {
    color: #1e3a8a;
}

/* Assigned By Badge */
.todo-meta-pill.pill-assigned-by {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #334155;
}
.todo-meta-pill.pill-assigned-by i {
    color: #0284c7;
}
.todo-meta-pill.pill-assigned-by strong {
    color: #0f172a;
}

/* Due Date Badge: Deadline by - */
.todo-meta-pill.pill-due-date {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}
.todo-meta-pill.pill-due-date i {
    color: #16a34a;
}
.todo-meta-pill.pill-due-date strong {
    color: #14532d;
}
.todo-meta-pill.pill-due-date.is-overdue {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.todo-meta-pill.pill-due-date.is-overdue i {
    color: #dc2626;
}
.todo-meta-pill.pill-due-date.is-overdue strong {
    color: #7f1d1d;
}
.todo-meta-overdue-tag {
    background: #ef4444;
    color: #ffffff;
    font-size: 9px;
    font-weight: 700;
    padding: 1px 4px;
    border-radius: 3px;
    margin-left: 3px;
    text-transform: uppercase;
    line-height: 1;
}

/* Assigned To Badge */
.todo-meta-pill.pill-assigned-to {
    background: #faf5ff;
    border: 1px solid #e9d5ff;
    color: #6b21a8;
}
.todo-meta-pill.pill-assigned-to i {
    color: #9333ea;
}
.todo-meta-pill.pill-assigned-to strong {
    color: #581c87;
}

/* Priority Badge */
.todo-meta-pill.pill-priority {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #b45309;
}
.todo-meta-pill.pill-priority i {
    color: #d97706;
}
.todo-meta-pill.pill-priority strong {
    color: #92400e;
}

/* Reminder Badge in Card */
.todo-meta-pill.pill-reminder {
    background: #f5f3ff;
    border: 1px solid #ddd6fe;
    color: #6d28d9;
}
.todo-meta-pill.pill-reminder i {
    color: #7c3aed;
}
.todo-meta-pill.pill-reminder strong {
    color: #5b21b6;
}

/* Completed Date Badge */
.todo-meta-pill.pill-completed-date {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}
.todo-meta-pill.pill-completed-date i {
    color: #059669;
}
.todo-meta-pill.pill-completed-date strong {
    color: #064e3b;
}

.todo-actions-right {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 8px;
    flex-shrink: 0;
}

.todo-card.is-expanded .todo-actions-right {
    margin-top: 1px;
}

.todo-star-icon {
    font-size: 14px;
    color: #cbd5e1;
    cursor: pointer;
    transition: color 0.15s, transform 0.15s;
    padding: 2px;
}
.todo-star-icon:hover {
    color: #f59e0b;
    transform: scale(1.15);
}
.todo-star-icon.active {
    color: #f59e0b;
}

.todo-expand-chevron {
    font-size: 10px;
    color: #94a3b8;
    transition: transform 0.2s ease, color 0.15s ease;
    margin-left: 2px;
}

.todo-card:hover .todo-expand-chevron {
    color: #2563eb;
}

.todo-card.is-expanded .todo-expand-chevron {
    transform: rotate(180deg);
    color: #2563eb;
}

/* Completed Section */
.todo-completed-section {
    margin-top: 18px;
    border-top: 1px solid #e2e8f0;
    padding-top: 14px;
}

.todo-completed-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    border-radius: 6px;
    font-size: 13.5px;
    font-weight: 700;
    color: #475569;
    margin-bottom: 8px;
    cursor: pointer;
    user-select: none;
    transition: background 0.15s;
    width: fit-content;
}

.todo-completed-header:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.todo-completed-count {
    background: #e2e8f0;
    color: #334155;
    font-size: 11.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
}

/* Empty State */
.todo-empty-state {
    text-align: center;
    padding: 24px 16px;
    color: #64748b;
    background: #fafbfd;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    margin-bottom: 6px;
}

/* ============================================================== */
/* Microsoft To-Do Style Bottom "Add a task" Bar (White Theme)    */
/* Matches Microsoft To-Do UI: hollow circle left, clean icons right */
/* ============================================================== */
.todo-bottom-add-container {
    position: sticky;
    bottom: 0px;
    z-index: 50;
    margin-top: auto;
    padding-top: 14px;
    padding-bottom: 4px;
    background: #ffffff;
    box-shadow: 0 -6px 14px rgba(255, 255, 255, 0.98);
}

.todo-ms-add-box {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 9px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
    min-height: 44px;
    box-sizing: border-box;
    position: relative;
}

.todo-ms-add-box:focus-within {
    border-color: #2563eb;
    box-shadow: 0 3px 12px rgba(37, 99, 235, 0.12);
}

/* Hollow circle on the left */
.todo-ms-circle {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid #94a3b8;
    background: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
    transition: all 0.15s ease;
    box-sizing: border-box;
}

.todo-ms-add-box:focus-within .todo-ms-circle {
    border-color: #2563eb;
}

.todo-ms-circle:hover {
    border-color: #2563eb;
    background: #eff6ff;
}

/* Input field - completely borderless and seamless (Overriding all global styles) */
.todo-ms-add-box input.todo-ms-add-input,
.todo-ms-add-box input[type="text"].todo-ms-add-input,
.todo-ms-add-box input[type="text"],
input.todo-ms-add-input,
#todoNewTaskTitle {
    flex: 1 !important;
    border: none !important;
    border-top: none !important;
    border-bottom: none !important;
    border-left: none !important;
    border-right: none !important;
    outline: none !important;
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    background: transparent !important;
    background-color: transparent !important;
    font-size: 13.5px !important;
    color: #0f172a !important;
    font-weight: 500 !important;
    padding: 0 4px !important;
    margin: 0 !important;
    margin-bottom: 0 !important;
    height: auto !important;
    min-height: 24px !important;
    min-width: 0 !important;
    width: 100% !important;
    font-family: inherit !important;
    line-height: 1.4 !important;
    border-radius: 0 !important;
    -webkit-appearance: none !important;
    box-sizing: border-box !important;
}

.todo-ms-add-box input.todo-ms-add-input:focus,
.todo-ms-add-box input[type="text"].todo-ms-add-input:focus,
.todo-ms-add-box input[type="text"]:focus,
input.todo-ms-add-input:focus,
#todoNewTaskTitle:focus {
    border: none !important;
    border-top: none !important;
    border-bottom: none !important;
    border-left: none !important;
    border-right: none !important;
    outline: none !important;
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    background: transparent !important;
    background-color: transparent !important;
}

.todo-ms-add-input::placeholder {
    color: #64748b;
    font-weight: 400;
    opacity: 0.95;
}

.todo-ms-add-box:focus-within .todo-ms-add-input::placeholder {
    color: #94a3b8;
}

/* Minimal Action Icons on the right (Calendar, Reminder, Star) */
.todo-ms-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.todo-ms-icon-btn {
    background: transparent;
    border: none;
    outline: none;
    cursor: pointer;
    color: #64748b;
    font-size: 15px;
    width: 26px;
    height: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    padding: 0;
    transition: color 0.15s ease, background 0.15s ease, transform 0.15s ease;
}

.todo-ms-icon-btn:hover {
    color: #2563eb;
    background: #f1f5f9;
}

.todo-ms-icon-btn.active {
    color: #2563eb;
    background: #eff6ff;
}

.todo-ms-icon-btn.star-btn:hover,
.todo-ms-icon-btn.star-btn.active {
    color: #f59e0b;
    background: #fffbeb;
}

/* Hidden date input for native picker */
.todo-ms-hidden-date {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    opacity: 0 !important;
    pointer-events: none !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    clip: rect(0,0,0,0) !important;
}

/* Date Indicator Badge */
.todo-ms-date-badge {
    display: none;
    align-items: center;
    gap: 5px;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
}
.todo-ms-date-badge.show {
    display: inline-flex;
}
.todo-ms-date-clear {
    cursor: pointer;
    color: #94a3b8;
    font-size: 10px;
    margin-left: 2px;
    transition: color 0.15s;
}
.todo-ms-date-clear:hover {
    color: #ef4444;
}

/* Reminder Indicator Badge */
.todo-ms-remind-badge {
    display: none;
    align-items: center;
    gap: 5px;
    background: #f5f3ff;
    color: #6d28d9;
    border: 1px solid #ddd6fe;
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
}
.todo-ms-remind-badge.show {
    display: inline-flex;
}
.todo-ms-remind-clear {
    cursor: pointer;
    color: #a78bfa;
    font-size: 10px;
    margin-left: 2px;
    transition: color 0.15s;
}
.todo-ms-remind-clear:hover {
    color: #ef4444;
}

/* Remind Me Popover (Microsoft To-Do Style) */
.todo-remind-popover {
    display: none;
    position: absolute;
    bottom: calc(100% + 10px);
    right: 28px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
    width: 250px;
    z-index: 999;
    padding: 6px 0;
    user-select: none;
}
.todo-remind-popover.show {
    display: block;
    animation: todoPopFade 0.15s ease-out;
}
@keyframes todoPopFade {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.todo-remind-header {
    padding: 6px 14px 4px 14px;
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 4px;
}

.todo-remind-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 14px;
    font-size: 12.5px;
    color: #334155;
    cursor: pointer;
    transition: background 0.15s;
}
.todo-remind-option:hover {
    background: #f8fafc;
    color: #2563eb;
}
.todo-remind-option-left {
    display: flex;
    align-items: center;
    gap: 9px;
    font-weight: 500;
}
.todo-remind-option-left i {
    width: 15px;
    text-align: center;
    color: #64748b;
    font-size: 12.5px;
}
.todo-remind-option:hover .todo-remind-option-left i {
    color: #2563eb;
}
.todo-remind-time-hint {
    font-size: 11px;
    color: #94a3b8;
}

.todo-remind-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 4px 0;
}

/* Custom Date/Time subpanel */
.todo-remind-custom-panel {
    display: none;
    padding: 10px 14px 8px 14px;
    border-top: 1px solid #f1f5f9;
    background: #fafbfd;
}
.todo-remind-custom-panel.show {
    display: block;
}
.todo-remind-input-row {
    margin-bottom: 7px;
}
.todo-remind-input-label {
    display: block;
    font-size: 10.5px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 2px;
}
.todo-remind-input-control {
    width: 100% !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 4px !important;
    padding: 4px 6px !important;
    font-size: 12px !important;
    color: #334155 !important;
    background: #ffffff !important;
    outline: none !important;
    height: 28px !important;
    box-sizing: border-box !important;
}
.todo-remind-input-control:focus {
    border-color: #2563eb !important;
}
.todo-remind-custom-actions {
    display: flex;
    justify-content: flex-end;
    gap: 6px;
    margin-top: 8px;
}
.todo-remind-btn-save {
    background: #2563eb;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    padding: 4px 12px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
}
.todo-remind-btn-save:hover {
    background: #1d4ed8;
}
.todo-remind-btn-cancel {
    background: transparent;
    color: #64748b;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 4px 10px;
    font-size: 11.5px;
    cursor: pointer;
}
.todo-remind-btn-cancel:hover {
    background: #f1f5f9;
}

/* Toast Notifications */
.todo-toast-alert {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #ffffff;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    z-index: 999999;
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.2s ease, transform 0.2s ease;
    pointer-events: none;
    display: flex;
    align-items: center;
    gap: 8px;
}
.todo-toast-alert.show {
    opacity: 1;
    transform: translateY(0);
}
.todo-toast-alert.toast-success {
    background: #059669;
}
.todo-toast-alert.toast-error {
    background: #dc2626;
}
</style>

<div class="todo-app-wrapper">

    <!-- Compact Filter Bar (Minimal height & width, Right-Aligned) -->
    <div class="todo-compact-filter-bar">
        <span class="todo-filter-btn active" data-filter="all">All</span>
        <span class="todo-filter-btn" data-filter="active">Active (<span class="todo-cnt-pending"><?php echo $pendingCount; ?></span>)</span>
        <span class="todo-filter-btn" data-filter="completed">Completed (<span class="todo-cnt-completed"><?php echo $completedCount; ?></span>)</span>
        <span class="todo-filter-btn" data-filter="starred"><i class="fa fa-star" style="color:#f59e0b;"></i> Starred</span>
    </div>

    <!-- 3. Active (Pending) Tasks List -->
    <div class="todo-list-container" id="todoPendingList">
        <div id="todoPendingEmptyState" class="todo-empty-state" style="<?php echo empty($pendingTasks) ? '' : 'display:none;'; ?>">
            <i class="fa fa-check-circle-o" style="font-size: 26px; color: #10b981; margin-bottom: 6px;"></i>
            <div style="font-size: 13.5px; font-weight: 600; color: #475569;">All tasks completed!</div>
            <div style="font-size: 12px; color: #94a3b8;">Add a new task below to stay organized.</div>
        </div>
        <?php foreach ($pendingTasks as $task): 
            $isOverdue = (!empty($task['due_date']) && $task['due_date'] !== '0000-00-00' && strtotime($task['due_date']) < strtotime(date('Y-m-d')));
            $assignedDateStr = !empty($task['created_at']) && $task['created_at'] !== '0000-00-00 00:00:00' 
                ? date('D, j M Y', strtotime($task['created_at'])) 
                : date('D, j M Y', strtotime('-2 days'));
            $dueDateStr = !empty($task['due_date']) && $task['due_date'] !== '0000-00-00' 
                ? date('D, j M Y', strtotime($task['due_date'])) 
                : '';
        ?>
        <div class="todo-card" data-task-id="<?php echo (int)$task['id']; ?>" data-status="pending" data-important="<?php echo !empty($task['is_important']) ? '1' : '0'; ?>">
            <span class="todo-check-circle" data-task-id="<?php echo (int)$task['id']; ?>" title="Mark as completed"></span>
            <div class="todo-content">
                <p class="todo-task-title"><?php echo htmlspecialchars($task['title']); ?></p>
                <div class="todo-meta-row">
                    <span class="todo-meta-pill pill-assigned-date" title="Task Assigned Date">
                        <i class="fa fa-calendar-check-o"></i> Assigned Date: <strong><?php echo htmlspecialchars($assignedDateStr); ?></strong>
                    </span>

                    <span class="todo-meta-pill pill-assigned-by" title="Assigned by">
                        <i class="fa fa-user"></i> Assigned by: <strong><?php echo htmlspecialchars($task['assigned_by_name'] ?? 'Top Mgmt'); ?></strong>
                    </span>

                    <?php if (!empty($dueDateStr)): ?>
                    <span class="todo-meta-pill pill-due-date <?php echo $isOverdue ? 'is-overdue' : ''; ?>" title="Deadline by">
                        <i class="fa fa-clock-o"></i> Deadline by - <strong><?php echo htmlspecialchars($dueDateStr); ?></strong>
                        <?php if ($isOverdue): ?><span class="todo-meta-overdue-tag">Overdue</span><?php endif; ?>
                    </span>
                    <?php endif; ?>

                    <?php if (!empty($task['reminder_time'])): ?>
                    <span class="todo-meta-pill pill-reminder" title="Reminder">
                        <i class="fa fa-clock-o"></i> Remind: <strong><?php echo htmlspecialchars($task['reminder_time']); ?></strong>
                    </span>
                    <?php endif; ?>

                    <?php if (!$isViewingSelf && !empty($task['assigned_to_name'])): ?>
                    <span class="todo-meta-pill pill-assigned-to" title="Assigned to">
                        <i class="fa fa-arrow-right"></i> Assigned to: <strong><?php echo htmlspecialchars($task['assigned_to_name']); ?></strong>
                    </span>
                    <?php endif; ?>

                    <span class="todo-meta-pill pill-priority" title="High Priority" style="<?php echo !empty($task['is_important']) ? '' : 'display:none;'; ?>">
                        <i class="fa fa-star"></i> <strong>High Priority</strong>
                    </span>
                </div>
            </div>
            <div class="todo-actions-right">
                <span class="todo-star-icon <?php echo !empty($task['is_important']) ? 'active' : ''; ?>" data-task-id="<?php echo (int)$task['id']; ?>" title="Star task">
                    <i class="fa fa-star"></i>
                </span>
                <i class="fa fa-chevron-down todo-expand-chevron" title="Click to view details"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 4. Completed Section -->
    <div class="todo-completed-section" id="todoCompletedSection" style="<?php echo empty($completedTasks) ? 'display:none;' : ''; ?>">
        <div class="todo-completed-header" id="todoCompletedToggleHeader" title="Click to expand/collapse">
            <i class="fa fa-chevron-down todo-completed-toggle-icon" style="font-size: 11px;"></i>
            <span>Completed</span>
            <span class="todo-completed-count" id="todoCompletedCountBadge"><?php echo $completedCount; ?></span>
        </div>

        <div class="todo-list-container" id="todoCompletedList">
            <?php foreach ($completedTasks as $task): 
                $assignedDateStr = !empty($task['created_at']) && $task['created_at'] !== '0000-00-00 00:00:00' 
                    ? date('D, j M Y', strtotime($task['created_at'])) 
                    : date('D, j M Y', strtotime('-5 days'));
            ?>
            <div class="todo-card is-completed" data-task-id="<?php echo (int)$task['id']; ?>" data-status="completed" data-important="<?php echo !empty($task['is_important']) ? '1' : '0'; ?>">
                <span class="todo-check-circle" data-task-id="<?php echo (int)$task['id']; ?>" title="Mark as incomplete">
                    <i class="fa fa-check" style="font-size: 10px;"></i>
                </span>
                <div class="todo-content">
                    <p class="todo-task-title"><?php echo htmlspecialchars($task['title']); ?></p>
                    <div class="todo-meta-row">
                        <span class="todo-meta-pill pill-assigned-date" title="Task Assigned Date">
                            <i class="fa fa-calendar-check-o"></i> Assigned Date: <strong><?php echo htmlspecialchars($assignedDateStr); ?></strong>
                        </span>

                        <span class="todo-meta-pill pill-assigned-by" title="Assigned by">
                            <i class="fa fa-user"></i> Assigned by: <strong><?php echo htmlspecialchars($task['assigned_by_name'] ?? 'Top Mgmt'); ?></strong>
                        </span>

                        <?php if (!empty($task['completed_at'])): ?>
                        <span class="todo-meta-pill pill-completed-date" title="Completed Date">
                            <i class="fa fa-check-circle"></i> Completed: <strong><?php echo date('D, j M Y, h:i A', strtotime($task['completed_at'])); ?></strong>
                        </span>
                        <?php endif; ?>

                        <?php if (!empty($task['reminder_time'])): ?>
                        <span class="todo-meta-pill pill-reminder" title="Reminder">
                            <i class="fa fa-clock-o"></i> Remind: <strong><?php echo htmlspecialchars($task['reminder_time']); ?></strong>
                        </span>
                        <?php endif; ?>

                        <span class="todo-meta-pill pill-priority" title="High Priority" style="<?php echo !empty($task['is_important']) ? '' : 'display:none;'; ?>">
                            <i class="fa fa-star"></i> <strong>High Priority</strong>
                        </span>
                    </div>
                </div>
                <div class="todo-actions-right">
                    <span class="todo-star-icon <?php echo !empty($task['is_important']) ? 'active' : ''; ?>" data-task-id="<?php echo (int)$task['id']; ?>" title="Star task">
                        <i class="fa fa-star"></i>
                    </span>
                    <i class="fa fa-chevron-down todo-expand-chevron" title="Click to view details"></i>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Microsoft To-Do Style Bottom "Add a task" Bar (Clean White, Borderless Input) -->
    <div class="todo-bottom-add-container">
        <div class="todo-ms-add-box" id="todoAddBox">
            <!-- Left Hollow Circle -->
            <span class="todo-ms-circle" id="todoMsCircleAdd" title="Click or press Enter to add task"></span>

            <!-- Main Input Field (Completely borderless) -->
            <input type="text" id="todoNewTaskTitle" class="todo-ms-add-input" placeholder="Try typing 'Pay utilities bill by Friday 6pm'" autocomplete="off" maxlength="350" style="border:none !important; outline:none !important; box-shadow:none !important; background:transparent !important; margin:0 !important; padding:0 4px !important; height:auto !important; min-height:24px !important; width:100% !important; flex:1 !important;" />

            <!-- Right Toolbar Icons (Calendar, Alarm, Star) -->
            <div class="todo-ms-toolbar" id="todoMsToolbar">
                <!-- Date badge (visible when date picked) -->
                <span class="todo-ms-date-badge" id="todoMsDateBadge" title="Selected deadline">
                    <i class="fa fa-calendar-check-o"></i>
                    <span id="todoMsDateText"></span>
                    <i class="fa fa-times todo-ms-date-clear" id="todoMsDateClear" title="Clear deadline"></i>
                </span>

                <!-- Reminder badge (visible when reminder picked) -->
                <span class="todo-ms-remind-badge" id="todoMsRemindBadge" title="Selected reminder">
                    <i class="fa fa-clock-o"></i>
                    <span id="todoMsRemindText"></span>
                    <i class="fa fa-times todo-ms-remind-clear" id="todoMsRemindClear" title="Clear reminder"></i>
                </span>

                <!-- 1. Due Date Calendar Icon -->
                <button type="button" class="todo-ms-icon-btn" id="todoNewTaskCalBtn" title="Add due date">
                    <i class="fa fa-calendar-o"></i>
                </button>
                <input type="date" id="todoNewTaskDueDate" class="todo-ms-hidden-date" min="<?php echo date('Y-m-d'); ?>" />

                <!-- 2. Alarm Reminder Icon & Popover -->
                <div style="position: relative; display: inline-flex;">
                    <button type="button" class="todo-ms-icon-btn" id="todoNewTaskRemindBtn" title="Remind me">
                        <i class="fa fa-clock-o"></i>
                    </button>

                    <!-- Remind Me Popover (Microsoft To-Do Style) -->
                    <div class="todo-remind-popover" id="todoRemindPopover">
                        <div class="todo-remind-header">Remind Me</div>
                        
                        <div class="todo-remind-option" data-type="today">
                            <span class="todo-remind-option-left">
                                <i class="fa fa-clock-o"></i>
                                <span>Later Today</span>
                            </span>
                            <span class="todo-remind-time-hint">5:00 PM</span>
                        </div>

                        <div class="todo-remind-option" data-type="tomorrow">
                            <span class="todo-remind-option-left">
                                <i class="fa fa-sun-o"></i>
                                <span>Tomorrow</span>
                            </span>
                            <span class="todo-remind-time-hint" id="todoHintTomorrow">Tomorrow, 9:00 AM</span>
                        </div>

                        <div class="todo-remind-option" data-type="next_week">
                            <span class="todo-remind-option-left">
                                <i class="fa fa-calendar"></i>
                                <span>Next Week</span>
                            </span>
                            <span class="todo-remind-time-hint" id="todoHintNextWeek">Mon, 9:00 AM</span>
                        </div>

                        <div class="todo-remind-divider"></div>

                        <div class="todo-remind-option" data-type="custom" id="todoRemindCustomBtn">
                            <span class="todo-remind-option-left">
                                <i class="fa fa-calendar-plus-o"></i>
                                <span>Pick a date & time</span>
                            </span>
                            <i class="fa fa-chevron-right" style="font-size: 10px; color: #94a3b8;"></i>
                        </div>

                        <!-- Custom Date & Time Picker Panel -->
                        <div class="todo-remind-custom-panel" id="todoRemindCustomPanel">
                            <div class="todo-remind-input-row">
                                <label class="todo-remind-input-label">Date:</label>
                                <input type="date" id="todoCustomRemindDate" class="todo-remind-input-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" />
                            </div>
                            <div class="todo-remind-input-row">
                                <label class="todo-remind-input-label">Time:</label>
                                <input type="time" id="todoCustomRemindTime" class="todo-remind-input-control" value="09:00" />
                            </div>
                            <div class="todo-remind-custom-actions">
                                <button type="button" class="todo-remind-btn-cancel" id="todoCustomRemindCancel">Back</button>
                                <button type="button" class="todo-remind-btn-save" id="todoCustomRemindSave">Set</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Star (High Priority) Icon -->
                <button type="button" class="todo-ms-icon-btn star-btn" id="todoNewTaskStarBtn" title="Mark as high priority">
                    <i class="fa fa-star-o" id="todoNewTaskStarIcon"></i>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
(function() {
    var todoTargetUid = <?php echo (int)$targetDashboardUid; ?>;
    var todoTargetName = <?php echo json_encode($targetDashboardName); ?>;
    var todoTargetRole = <?php echo json_encode($targetDashboardRole); ?>;
    var todoCurrentUid = <?php echo (int)$currentLoggedInUid; ?>;
    var isViewingSelf = <?php echo $isViewingSelf ? 'true' : 'false'; ?>;
    var currentReminderValue = '';

    // Toast alert helper
    function showTodoToast(msg, isError) {
        $('.todo-toast-alert').remove();
        var icon = isError ? 'fa-exclamation-circle' : 'fa-check-circle';
        var toastType = isError ? 'toast-error' : 'toast-success';
        var $toast = $('<div class="todo-toast-alert ' + toastType + '"><i class="fa ' + icon + '"></i><span>' + msg + '</span></div>');
        $('body').append($toast);
        setTimeout(function() { $toast.addClass('show'); }, 15);
        setTimeout(function() {
            $toast.removeClass('show');
            setTimeout(function() { $toast.remove(); }, 250);
        }, 2800);
    }

    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    // Helper dates
    function getTomorrow() {
        var d = new Date();
        d.setDate(d.getDate() + 1);
        return d;
    }
    function getNextMonday() {
        var d = new Date();
        var day = d.getDay();
        var diff = (day === 0 ? 1 : (8 - day));
        d.setDate(d.getDate() + diff);
        return d;
    }

    // Populate hints dynamically
    var tmObj = getTomorrow();
    $('#todoHintTomorrow').text(days[tmObj.getDay()] + ', 9:00 AM');
    var nmObj = getNextMonday();
    $('#todoHintNextWeek').text(days[nmObj.getDay()] + ' ' + nmObj.getDate() + ' ' + months[nmObj.getMonth()] + ', 9:00 AM');

    // Build dynamic task card HTML matching server template
    function buildTaskCardHtml(t) {
        var isDone = (t.status === 'completed');
        var isStar = (parseInt(t.is_important) === 1);
        var taskId = parseInt(t.id);
        var title = $('<div>').text(t.title).html();
        var assignedBy = $('<div>').text(t.assigned_by_name || 'Top Mgmt').html();
        var assignedTo = $('<div>').text(t.assigned_to_name || '').html();

        var createdDate = t.created_at ? new Date(t.created_at.replace(/-/g, '/')) : new Date();
        var assignedDateStr = days[createdDate.getDay()] + ', ' + createdDate.getDate() + ' ' + months[createdDate.getMonth()] + ' ' + createdDate.getFullYear();

        var dueDatePill = '';
        if (t.due_date && t.due_date !== '0000-00-00') {
            var dDate = new Date(t.due_date.replace(/-/g, '/'));
            var dStr = days[dDate.getDay()] + ', ' + dDate.getDate() + ' ' + months[dDate.getMonth()] + ' ' + dDate.getFullYear();
            var today = new Date();
            today.setHours(0,0,0,0);
            dDate.setHours(0,0,0,0);
            var isOverdue = (!isDone && dDate < today);
            dueDatePill = '<span class="todo-meta-pill pill-due-date ' + (isOverdue ? 'is-overdue' : '') + '" title="Deadline by">' +
                '<i class="fa fa-clock-o"></i> Deadline by - <strong>' + dStr + '</strong>' +
                (isOverdue ? '<span class="todo-meta-overdue-tag">Overdue</span>' : '') +
                '</span>';
        }

        var remindPill = '';
        if (t.reminder_time) {
            remindPill = '<span class="todo-meta-pill pill-reminder" title="Reminder">' +
                '<i class="fa fa-clock-o"></i> Remind: <strong>' + $('<div>').text(t.reminder_time).html() + '</strong>' +
                '</span>';
        }

        var assignedToPill = '';
        if (!isViewingSelf && assignedTo) {
            assignedToPill = '<span class="todo-meta-pill pill-assigned-to" title="Assigned to">' +
                '<i class="fa fa-arrow-right"></i> Assigned to: <strong>' + assignedTo + '</strong>' +
                '</span>';
        }

        var priorityPill = '<span class="todo-meta-pill pill-priority" title="High Priority" style="' + (isStar ? '' : 'display:none;') + '">' +
            '<i class="fa fa-star"></i> <strong>High Priority</strong>' +
            '</span>';

        var completedPill = '';
        if (isDone && t.completed_at) {
            var cDate = new Date(t.completed_at.replace(/-/g, '/'));
            var cStr = days[cDate.getDay()] + ', ' + cDate.getDate() + ' ' + months[cDate.getMonth()] + ' ' + cDate.getFullYear();
            completedPill = '<span class="todo-meta-pill pill-completed-date" title="Completed Date">' +
                '<i class="fa fa-check-circle"></i> Completed: <strong>' + cStr + '</strong>' +
                '</span>';
        }

        return '<div class="todo-card ' + (isDone ? 'is-completed' : '') + '" data-task-id="' + taskId + '" data-status="' + (isDone ? 'completed' : 'pending') + '" data-important="' + (isStar ? '1' : '0') + '">' +
            '<span class="todo-check-circle" data-task-id="' + taskId + '" title="' + (isDone ? 'Mark incomplete' : 'Mark completed') + '">' +
                (isDone ? '<i class="fa fa-check" style="font-size: 10px;"></i>' : '') +
            '</span>' +
            '<div class="todo-content">' +
                '<p class="todo-task-title">' + title + '</p>' +
                '<div class="todo-meta-row">' +
                    '<span class="todo-meta-pill pill-assigned-date" title="Task Assigned Date">' +
                        '<i class="fa fa-calendar-check-o"></i> Assigned Date: <strong>' + assignedDateStr + '</strong>' +
                    '</span>' +
                    '<span class="todo-meta-pill pill-assigned-by" title="Assigned by">' +
                        '<i class="fa fa-user"></i> Assigned by: <strong>' + assignedBy + '</strong>' +
                    '</span>' +
                    dueDatePill +
                    remindPill +
                    assignedToPill +
                    completedPill +
                    priorityPill +
                '</div>' +
            '</div>' +
            '<div class="todo-actions-right">' +
                '<span class="todo-star-icon ' + (isStar ? 'active' : '') + '" data-task-id="' + taskId + '" title="Star task">' +
                    '<i class="fa fa-star"></i>' +
                '</span>' +
                '<i class="fa fa-chevron-down todo-expand-chevron" title="Click to view details"></i>' +
            '</div>' +
        '</div>';
    }

    // Update counts & empty states
    function updateTaskCounts() {
        var pendingCount = $('#todoPendingList .todo-card').length;
        var completedCount = $('#todoCompletedList .todo-card').length;

        $('.todo-cnt-pending').text(pendingCount);
        $('.todo-cnt-completed').text(completedCount);
        $('#todoCompletedCountBadge').text(completedCount);

        if (pendingCount === 0) {
            $('#todoPendingEmptyState').show();
        } else {
            $('#todoPendingEmptyState').hide();
        }

        if (completedCount === 0) {
            $('#todoCompletedSection').hide();
        } else {
            var currentFilter = $('.todo-filter-btn.active').data('filter');
            if (currentFilter !== 'active') {
                $('#todoCompletedSection').show();
            }
        }
    }

    // Submit new task
    function submitNewTask() {
        var $titleInput = $('#todoNewTaskTitle');
        var title = $.trim($titleInput.val());
        if (!title) {
            $titleInput.focus();
            return;
        }

        var dueDate = $('#todoNewTaskDueDate').val();
        var isImportant = $('#todoNewTaskStarBtn').hasClass('active') ? 1 : 0;
        var reminderTime = currentReminderValue;
        var $circle = $('#todoMsCircleAdd');

        $circle.css('opacity', '0.4');

        $.ajax({
            url: 'task_actions.php?action=create_task',
            type: 'POST',
            dataType: 'json',
            data: {
                title: title,
                assigned_to_user_id: todoTargetUid,
                assigned_to_name: todoTargetName,
                assigned_to_role: todoTargetRole,
                due_date: dueDate,
                is_important: isImportant,
                reminder_time: reminderTime
            },
            success: function(resp) {
                $circle.css('opacity', '1');
                if (resp && resp.success && resp.task) {
                    var newCardHtml = buildTaskCardHtml(resp.task);
                    var $newCard = $(newCardHtml).hide();
                    $('#todoPendingList').prepend($newCard);
                    $newCard.slideDown(180);

                    // Reset form fields
                    $titleInput.val('').focus();
                    $('#todoNewTaskDueDate').val('');
                    $('#todoMsDateBadge').removeClass('show');
                    $('#todoNewTaskCalBtn').removeClass('active');
                    
                    // Reset reminder
                    currentReminderValue = '';
                    $('#todoMsRemindBadge').removeClass('show');
                    $('#todoNewTaskRemindBtn').removeClass('active');
                    $('#todoRemindPopover').removeClass('show');
                    $('#todoRemindCustomPanel').removeClass('show');

                    // Reset star
                    $('#todoNewTaskStarBtn').removeClass('active');
                    $('#todoNewTaskStarIcon').removeClass('fa-star').addClass('fa-star-o');

                    updateTaskCounts();
                    showTodoToast('Task added successfully!', false);

                    // Re-apply filter if on "completed"
                    var currentFilter = $('.todo-filter-btn.active').data('filter');
                    if (currentFilter === 'completed') {
                        $newCard.hide();
                    }
                } else {
                    showTodoToast((resp && resp.error) ? resp.error : 'Failed to add task.', true);
                }
            },
            error: function() {
                $circle.css('opacity', '1');
                showTodoToast('Error communicating with server.', true);
            }
        });
    }

    // Filter cards
    function applyFilter(filter) {
        if (filter === 'all') {
            $('#todoPendingList .todo-card').show();
            var compCount = $('#todoCompletedList .todo-card').length;
            if (compCount > 0) {
                $('#todoCompletedSection').show();
                $('#todoCompletedList .todo-card').show();
            } else {
                $('#todoCompletedSection').hide();
            }
        } else if (filter === 'active') {
            $('#todoPendingList .todo-card').show();
            $('#todoCompletedSection').hide();
        } else if (filter === 'completed') {
            $('#todoPendingList .todo-card').hide();
            $('#todoCompletedSection').show();
            $('#todoCompletedList .todo-card').show();
        } else if (filter === 'starred') {
            $('#todoPendingList .todo-card').each(function() {
                var isStar = $(this).attr('data-important') === '1';
                $(this).toggle(isStar);
            });
            var anyStarredCompleted = false;
            $('#todoCompletedList .todo-card').each(function() {
                var isStar = $(this).attr('data-important') === '1';
                $(this).toggle(isStar);
                if (isStar) anyStarredCompleted = true;
            });
            if (anyStarredCompleted) {
                $('#todoCompletedSection').show();
            } else {
                $('#todoCompletedSection').hide();
            }
        }
    }

    $(document).ready(function() {
        // 1. Enter key & click on circle to submit task
        $('#todoNewTaskTitle').on('keydown', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                submitNewTask();
            }
        });

        $('#todoMsCircleAdd').on('click', function(e) {
            e.preventDefault();
            submitNewTask();
        });

        // 2. Calendar button click -> triggers native date picker
        $('#todoNewTaskCalBtn').on('click', function(e) {
            e.preventDefault();
            var dateInput = document.getElementById('todoNewTaskDueDate');
            if (dateInput) {
                if (typeof dateInput.showPicker === 'function') {
                    dateInput.showPicker();
                } else {
                    dateInput.focus();
                    dateInput.click();
                }
            }
        });

        $('#todoNewTaskDueDate').on('change', function() {
            var val = $(this).val();
            if (val) {
                var d = new Date(val.replace(/-/g, '/'));
                var label = d.getDate() + ' ' + months[d.getMonth()];
                $('#todoMsDateText').text(label);
                $('#todoMsDateBadge').addClass('show');
                $('#todoNewTaskCalBtn').addClass('active');
            } else {
                $('#todoMsDateBadge').removeClass('show');
                $('#todoNewTaskCalBtn').removeClass('active');
            }
        });

        $('#todoMsDateClear').on('click', function(e) {
            e.stopPropagation();
            $('#todoNewTaskDueDate').val('');
            $('#todoMsDateBadge').removeClass('show');
            $('#todoNewTaskCalBtn').removeClass('active');
        });

        // 3. Remind Me Popover & Date/Time selector
        $('#todoNewTaskRemindBtn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#todoRemindPopover').toggleClass('show');
            $('#todoRemindCustomPanel').removeClass('show');
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#todoRemindPopover, #todoNewTaskRemindBtn').length) {
                $('#todoRemindPopover').removeClass('show');
            }
        });

        $('.todo-remind-option[data-type]').on('click', function(e) {
            var type = $(this).data('type');
            if (type === 'custom') {
                e.stopPropagation();
                $('#todoRemindCustomPanel').toggleClass('show');
                return;
            }

            var label = '';
            if (type === 'today') {
                label = 'Today, 5:00 PM';
            } else if (type === 'tomorrow') {
                var tm = getTomorrow();
                label = 'Tomorrow (' + days[tm.getDay()] + ' ' + tm.getDate() + ' ' + months[tm.getMonth()] + '), 9:00 AM';
            } else if (type === 'next_week') {
                var nw = getNextMonday();
                label = 'Next Week (' + days[nw.getDay()] + ' ' + nw.getDate() + ' ' + months[nw.getMonth()] + '), 9:00 AM';
            }

            applyReminder(label);
        });

        $('#todoCustomRemindSave').on('click', function(e) {
            e.stopPropagation();
            var dVal = $('#todoCustomRemindDate').val();
            var tVal = $('#todoCustomRemindTime').val() || '09:00';
            if (!dVal) return;

            var dObj = new Date(dVal.replace(/-/g, '/'));
            var parts = tVal.split(':');
            var h = parseInt(parts[0], 10);
            var m = parts[1] || '00';
            var ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            if (h === 0) h = 12;
            var timeStr = h + ':' + m + ' ' + ampm;

            var label = days[dObj.getDay()] + ', ' + dObj.getDate() + ' ' + months[dObj.getMonth()] + ', ' + timeStr;
            applyReminder(label);
        });

        $('#todoCustomRemindCancel').on('click', function(e) {
            e.stopPropagation();
            $('#todoRemindCustomPanel').removeClass('show');
        });

        function applyReminder(label) {
            currentReminderValue = label;
            $('#todoMsRemindText').text(label);
            $('#todoMsRemindBadge').addClass('show');
            $('#todoNewTaskRemindBtn').addClass('active');
            $('#todoRemindPopover').removeClass('show');
            showTodoToast('Reminder set: ' + label, false);
        }

        $('#todoMsRemindClear').on('click', function(e) {
            e.stopPropagation();
            currentReminderValue = '';
            $('#todoMsRemindBadge').removeClass('show');
            $('#todoNewTaskRemindBtn').removeClass('active');
        });

        // 4. Star button toggle
        $('#todoNewTaskStarBtn').on('click', function(e) {
            e.preventDefault();
            $(this).toggleClass('active');
            var isStar = $(this).hasClass('active');
            $('#todoNewTaskStarIcon').toggleClass('fa-star', isStar).toggleClass('fa-star-o', !isStar);
        });

        // 5. Toggle Status (Check Circle click)
        $(document).on('click', '.todo-check-circle', function(e) {
            e.stopPropagation();
            var $circle = $(this);
            var $card = $circle.closest('.todo-card');
            var taskId = $card.data('task-id');

            $circle.css('opacity', '0.4');

            $.ajax({
                url: 'task_actions.php?action=toggle_status',
                type: 'POST',
                dataType: 'json',
                data: { task_id: taskId },
                success: function(resp) {
                    $circle.css('opacity', '1');
                    if (resp && resp.success) {
                        var newStatus = resp.new_status;
                        if (newStatus === 'completed') {
                            $card.addClass('is-completed').attr('data-status', 'completed');
                            $circle.html('<i class="fa fa-check" style="font-size: 10px;"></i>').attr('title', 'Mark incomplete');
                            
                            // Close accordion if open
                            $card.removeClass('is-expanded').find('.todo-meta-row').hide();

                            // Move to completed list
                            $card.slideUp(180, function() {
                                $('#todoCompletedList').prepend($card);
                                $('#todoCompletedSection').slideDown(150);
                                $card.slideDown(180);
                                updateTaskCounts();
                            });
                            showTodoToast('Task marked as completed!', false);
                        } else {
                            $card.removeClass('is-completed').attr('data-status', 'pending');
                            $circle.empty().attr('title', 'Mark completed');

                            // Move to pending list
                            $card.slideUp(180, function() {
                                $('#todoPendingList').prepend($card);
                                $card.slideDown(180);
                                updateTaskCounts();
                            });
                            showTodoToast('Task restored to active list.', false);
                        }
                    } else {
                        showTodoToast((resp && resp.error) ? resp.error : 'Failed to update task status.', true);
                    }
                },
                error: function() {
                    $circle.css('opacity', '1');
                    showTodoToast('Error communicating with server.', true);
                }
            });
        });

        // 6. Toggle Star (Priority click)
        $(document).on('click', '.todo-star-icon', function(e) {
            e.stopPropagation();
            var $star = $(this);
            var $card = $star.closest('.todo-card');
            var taskId = $card.data('task-id');

            $.ajax({
                url: 'task_actions.php?action=toggle_star',
                type: 'POST',
                dataType: 'json',
                data: { task_id: taskId },
                success: function(resp) {
                    if (resp && resp.success) {
                        var isImportant = (parseInt(resp.is_important) === 1);
                        $star.toggleClass('active', isImportant);
                        $card.attr('data-important', isImportant ? '1' : '0');
                        
                        var $priorityPill = $card.find('.pill-priority');
                        if (isImportant) {
                            $priorityPill.show();
                        } else {
                            $priorityPill.hide();
                        }

                        // Re-filter if on "starred" view
                        var currentFilter = $('.todo-filter-btn.active').data('filter');
                        if (currentFilter === 'starred') {
                            applyFilter('starred');
                        }
                    } else {
                        showTodoToast((resp && resp.error) ? resp.error : 'Failed to update priority.', true);
                    }
                },
                error: function() {
                    showTodoToast('Error communicating with server.', true);
                }
            });
        });

        // 7. Single Accordion Behavior (Click on card)
        $(document).on('click', '.todo-card', function(e) {
            if ($(e.target).closest('.todo-check-circle, .todo-star-icon, button, input, label').length > 0) {
                return;
            }

            var $card = $(this);
            var isAlreadyOpen = $card.hasClass('is-expanded');

            // Automatically close all other open task cards (Single Accordion Behavior)
            $('.todo-card.is-expanded').not($card).each(function() {
                $(this).removeClass('is-expanded');
                $(this).find('.todo-meta-row').stop(true, true).slideUp(160);
            });

            // Toggle clicked card
            if (isAlreadyOpen) {
                $card.removeClass('is-expanded');
                $card.find('.todo-meta-row').stop(true, true).slideUp(160);
            } else {
                $card.addClass('is-expanded');
                $card.find('.todo-meta-row').stop(true, true).slideDown(160);
            }
        });

        // 8. Filter pills click
        $(document).on('click', '.todo-filter-btn', function(e) {
            e.preventDefault();
            $('.todo-filter-btn').removeClass('active');
            $(this).addClass('active');
            var filter = $(this).data('filter');
            applyFilter(filter);
        });

        // 9. Completed section fold / unfold
        $(document).on('click', '#todoCompletedToggleHeader', function(e) {
            var $list = $('#todoCompletedList');
            var $icon = $(this).find('.todo-completed-toggle-icon');
            $list.slideToggle(180, function() {
                if ($list.is(':visible')) {
                    $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                } else {
                    $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
                }
            });
        });
    });
})();
</script>

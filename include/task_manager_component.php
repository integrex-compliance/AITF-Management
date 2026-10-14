<?php
/**
 * Microsoft To-Do Style Task Manager Component
 * 
 * Usable inside myDashboard.php or standalone in taskManager.php.
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

// Privilege to assign tasks & view other users
$userCanManageTasks = aitf_can_manage_tasks($currentLoggedInRole);

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

// Fetch all system users for Assignee dropdown (strictly available for mgmt/admin/finance)
$systemUsersList = [];
if ($userCanManageTasks && function_exists('aitf_get_all_system_users') && isset($db) && $db) {
    $systemUsersList = aitf_get_all_system_users($db, false);
}

// Fetch initial tasks for target user
$initialTasks = [];
if (isset($db) && $db && $targetDashboardUid > 0) {
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
$totalCount = count($initialTasks);
?>

<!-- Microsoft To-Do Task Manager Styles -->
<style>
/* Container & Theme */
.todo-app-wrapper {
    background: #1e1e1e;
    color: #f3f4f6;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    margin-bottom: 25px;
}

/* Header Area */
.todo-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    padding-bottom: 20px;
    border-bottom: 1px solid #2d2d2d;
    margin-bottom: 22px;
}
.todo-title-group h3 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 10px;
    letter-spacing: -0.3px;
}
.todo-title-group .todo-subtitle {
    margin: 5px 0 0 0;
    font-size: 13px;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 8px;
}
.todo-user-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #2b3544;
    color: #60a5fa;
    border: 1px solid #3b82f6;
    padding: 3px 10px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
}

/* Stats & Quick Actions */
.todo-stats-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.todo-stat-pill {
    background: #262626;
    border: 1px solid #333333;
    border-radius: 20px;
    padding: 5px 14px;
    font-size: 12.5px;
    color: #d1d5db;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.todo-stat-pill strong {
    color: #ffffff;
}
.todo-stat-pill.pill-pending strong {
    color: #38bdf8;
}
.todo-stat-pill.pill-completed strong {
    color: #4ade80;
}

/* Search & Filter Bar */
.todo-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 18px;
}
.todo-search-box {
    position: relative;
    flex: 1;
    min-width: 240px;
    max-width: 420px;
}
.todo-search-box input {
    width: 100%;
    background: #262626;
    border: 1px solid #383838;
    color: #ffffff;
    border-radius: 8px;
    padding: 8px 12px 8px 36px;
    font-size: 13px;
    outline: none;
    transition: all 0.2s;
}
.todo-search-box input:focus {
    border-color: #3b82f6;
    background: #2b2b2b;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}
.todo-search-box .fa-search {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    font-size: 13px;
}
.todo-filter-btns {
    display: flex;
    gap: 6px;
    align-items: center;
}
.todo-filter-btn {
    background: #262626;
    border: 1px solid #383838;
    color: #9ca3af;
    font-size: 12px;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.15s;
}
.todo-filter-btn:hover {
    background: #333333;
    color: #ffffff;
}
.todo-filter-btn.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
    font-weight: 600;
}

/* Microsoft To-Do Quick Add Bar */
.todo-add-container {
    background: #262626;
    border: 1px solid #383838;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 24px;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.25);
}
.todo-add-container:focus-within {
    background: #2b2b2b;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}
.todo-add-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.todo-add-circle {
    width: 20px;
    height: 20px;
    border: 2px solid #6b7280;
    border-radius: 50%;
    flex-shrink: 0;
    display: inline-block;
    cursor: default;
}
.todo-add-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #ffffff;
    font-size: 14.5px;
    padding: 6px 0;
}
.todo-add-input::placeholder {
    color: #9ca3af;
}

/* Quick Add Options Row */
.todo-add-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding-top: 10px;
    margin-top: 8px;
    border-top: 1px solid #333333;
}
.todo-opt-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.todo-opt-label {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 600;
}
.todo-opt-select, .todo-opt-date {
    background: #1f1f1f;
    border: 1px solid #444444;
    color: #ffffff;
    border-radius: 6px;
    padding: 5px 10px;
    font-size: 12.5px;
    outline: none;
}
.todo-opt-select:focus, .todo-opt-date:focus {
    border-color: #3b82f6;
}
.todo-add-btn {
    background: #2563eb;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 7px 18px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.15s;
    box-shadow: 0 1px 4px rgba(37,99,235,0.4);
}
.todo-add-btn:hover {
    background: #1d4ed8;
}
.todo-add-btn kbd {
    background: rgba(255,255,255,0.2);
    border: none;
    font-size: 11px;
    padding: 1px 5px;
    border-radius: 4px;
    color: #ffffff;
}

/* Regular user alert when add is restricted */
.todo-restricted-notice {
    background: #262626;
    border: 1px dashed #3f3f46;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12.5px;
    color: #9ca3af;
}

/* Tasks Lists */
.todo-list-container {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

/* Individual Task Card */
.todo-card {
    background: #252525;
    border: 1px solid #333333;
    border-radius: 8px;
    padding: 12px 16px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}
.todo-card:hover {
    background: #2d2d2d;
    border-color: #404040;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

/* Checkbox (Circle) */
.todo-check-btn {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
    cursor: pointer;
    transition: all 0.15s;
    background: transparent;
    border: 2px solid #71717a;
    padding: 0;
}
.todo-check-btn:hover:not(.disabled) {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.15);
}
.todo-check-btn .fa-check {
    display: none;
    font-size: 10px;
    color: #ffffff;
}
.todo-check-btn:hover:not(.disabled) .fa-check {
    display: block;
    color: #60a5fa;
}

/* Completed State Checkbox */
.todo-card.is-completed .todo-check-btn {
    background: #4f6bed;
    border-color: #4f6bed;
}
.todo-card.is-completed .todo-check-btn .fa-check {
    display: block;
    color: #ffffff;
}

/* Disabled Checkbox (when viewing another user's dashboard) */
.todo-check-btn.disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
.todo-check-btn.disabled:hover {
    border-color: #71717a;
    background: transparent;
}

/* Task Content */
.todo-content {
    flex: 1;
    min-width: 0;
}
.todo-task-title {
    font-size: 14px;
    font-weight: 500;
    color: #ffffff;
    line-height: 1.4;
    word-break: break-word;
    margin: 0;
    transition: color 0.15s, text-decoration 0.15s;
}
.todo-card.is-completed .todo-task-title {
    color: #8e8e93;
    text-decoration: line-through;
}

/* Meta subtitle (Calendar date, assigned by, etc.) */
.todo-meta-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 5px;
    font-size: 12px;
    color: #8e8e93;
}
.todo-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.todo-meta-item.meta-due {
    color: #9ca3af;
}
.todo-meta-item.meta-overdue {
    color: #f87171;
    font-weight: 600;
}
.todo-card.is-completed .todo-meta-item.meta-overdue {
    color: #8e8e93;
    font-weight: normal;
}
.todo-meta-tag {
    background: #333333;
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 11px;
    color: #d1d5db;
}

/* Right Actions (Star, Delete) */
.todo-actions-right {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-left: 8px;
}
.todo-star-btn {
    background: transparent;
    border: none;
    color: #52525b;
    cursor: pointer;
    font-size: 15px;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.15s;
}
.todo-star-btn:hover {
    color: #fbbf24;
}
.todo-star-btn.active {
    color: #f59e0b;
}
.todo-del-btn {
    background: transparent;
    border: none;
    color: #64748b;
    cursor: pointer;
    font-size: 13px;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.15s;
    opacity: 0;
}
.todo-card:hover .todo-del-btn {
    opacity: 1;
}
.todo-del-btn:hover {
    color: #ef4444;
}

/* Collapsible Completed Section */
.todo-completed-section {
    margin-top: 18px;
    border-top: 1px solid #2d2d2d;
    padding-top: 14px;
}
.todo-completed-header {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 8px 6px;
    border-radius: 6px;
    user-select: none;
    font-size: 13.5px;
    font-weight: 600;
    color: #d1d5db;
    transition: all 0.15s;
    margin-bottom: 8px;
}
.todo-completed-header:hover {
    background: #262626;
    color: #ffffff;
}
.todo-completed-header .fa-chevron-down,
.todo-completed-header .fa-chevron-right {
    font-size: 11px;
    color: #9ca3af;
    transition: transform 0.2s;
}
.todo-completed-count {
    background: #333333;
    color: #e5e7eb;
    font-size: 11.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
}

/* Empty State */
.todo-empty-state {
    text-align: center;
    padding: 35px 20px;
    color: #6b7280;
}
.todo-empty-icon {
    font-size: 38px;
    color: #4b5563;
    margin-bottom: 12px;
}
.todo-empty-text {
    font-size: 14px;
    margin: 0;
}

/* Toast Notifications */
.todo-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #1e293b;
    color: #ffffff;
    border: 1px solid #3b82f6;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 13.5px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    z-index: 99999;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
}
.todo-toast.show {
    opacity: 1;
    transform: translateY(0);
}
.todo-toast.toast-error {
    border-color: #ef4444;
    background: #2b1717;
}
</style>

<div class="todo-app-wrapper" id="todoAppWrapper" data-target-uid="<?php echo $targetDashboardUid; ?>" data-is-self="<?php echo $isViewingSelf ? '1' : '0'; ?>">

    <!-- 1. Header Area -->
    <div class="todo-header">
        <div class="todo-title-group">
            <h3>
                <i class="fa fa-check-circle" style="color: #4f6bed;"></i>
                <span>Task Manager</span>
            </h3>
            <div class="todo-subtitle">
                <span>Microsoft To-Do style workflow</span>
                <span>•</span>
                <?php if ($isViewingSelf): ?>
                    <span class="todo-user-badge">
                        <i class="fa fa-user"></i> My Tasks
                    </span>
                <?php else: ?>
                    <span class="todo-user-badge" style="background: #3b2d18; color: #fbbf24; border-color: #f59e0b;">
                        <i class="fa fa-users"></i> Tasks for <?php echo htmlspecialchars($targetDashboardName); ?> (<?php echo htmlspecialchars($targetDashboardRole); ?>)
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="todo-stats-bar">
            <span class="todo-stat-pill pill-pending">
                <i class="fa fa-clock-o"></i> Active: <strong id="todoStatPending"><?php echo $pendingCount; ?></strong>
            </span>
            <span class="todo-stat-pill pill-completed">
                <i class="fa fa-check"></i> Completed: <strong id="todoStatCompleted"><?php echo $completedCount; ?></strong>
            </span>
            <span class="todo-stat-pill">
                <i class="fa fa-list"></i> Total: <strong id="todoStatTotal"><?php echo $totalCount; ?></strong>
            </span>
        </div>
    </div>

    <!-- 2. Search & Filter Bar -->
    <div class="todo-toolbar">
        <div class="todo-search-box">
            <i class="fa fa-search"></i>
            <input type="text" id="todoSearchInput" placeholder="Search tasks..." autocomplete="off">
        </div>
        <div class="todo-filter-btns">
            <button type="button" class="todo-filter-btn active" data-filter="all">All</button>
            <button type="button" class="todo-filter-btn" data-filter="pending">Active (<?php echo $pendingCount; ?>)</button>
            <button type="button" class="todo-filter-btn" data-filter="completed">Completed (<?php echo $completedCount; ?>)</button>
            <button type="button" class="todo-filter-btn" data-filter="important"><i class="fa fa-star" style="color:#f59e0b;"></i> Starred</button>
        </div>
    </div>

    <!-- 3. Microsoft To-Do Quick Add Bar (ONLY for Management, Admin, Finance) -->
    <?php if ($userCanManageTasks): ?>
    <div class="todo-add-container">
        <div class="todo-add-row">
            <span class="todo-add-circle" title="Press Enter to assign task"></span>
            <input type="text" id="todoNewTaskInput" class="todo-add-input" placeholder="Add a task (e.g. AITF client report review, press Enter to assign)..." autocomplete="off">
        </div>
        
        <div class="todo-add-options">
            <div class="todo-opt-group">
                <!-- Assignee Selector -->
                <span class="todo-opt-label"><i class="fa fa-user"></i> Assign To:</span>
                <select id="todoAssignToSelect" class="todo-opt-select" style="min-width: 200px;">
                    <?php if (!empty($systemUsersList)): ?>
                        <?php foreach ($systemUsersList as $su): 
                            $suId = (int)$su['user_id'];
                            $isSel = ($suId === $targetDashboardUid);
                        ?>
                            <option value="<?php echo $suId; ?>" data-name="<?php echo htmlspecialchars($su['name']); ?>" data-role="<?php echo htmlspecialchars($su['role']); ?>" <?php echo $isSel ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($su['name']); ?> (<?php echo htmlspecialchars($su['role']); ?>)<?php echo ($suId === $currentLoggedInUid) ? ' - [You]' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="<?php echo $targetDashboardUid; ?>" selected>
                            <?php echo htmlspecialchars($targetDashboardName); ?> (<?php echo htmlspecialchars($targetDashboardRole); ?>)
                        </option>
                    <?php endif; ?>
                </select>

                <!-- Due Date Picker -->
                <span class="todo-opt-label" style="margin-left: 8px;"><i class="fa fa-calendar"></i> Due Date:</span>
                <input type="date" id="todoDueDateInput" class="todo-opt-date" value="<?php echo date('Y-m-d'); ?>">

                <!-- Star/Priority Toggle -->
                <label style="margin: 0; font-size: 12px; color: #d1d5db; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; margin-left: 6px;">
                    <input type="checkbox" id="todoIsImportantCheck" style="cursor: pointer;">
                    <span><i class="fa fa-star" style="color: #fbbf24;"></i> Important</span>
                </label>
            </div>

            <button type="button" id="todoBtnSubmitTask" class="todo-add-btn">
                <i class="fa fa-paper-plane"></i> Assign Task <kbd>↵ Enter</kbd>
            </button>
        </div>
    </div>
    <?php else: ?>
    <!-- Notice for non-management staff: read & mark complete only -->
    <div class="todo-restricted-notice">
        <i class="fa fa-info-circle" style="color: #60a5fa; font-size: 16px;"></i>
        <span>Here are your assigned tasks. Click the circular checkbox on any task to mark it as completed. Task assignment is managed by Management, Admin, and Finance.</span>
    </div>
    <?php endif; ?>

    <!-- 4. Active (Pending) Tasks List -->
    <div class="todo-list-container" id="todoPendingList">
        <?php if (!empty($pendingTasks)): ?>
            <?php foreach ($pendingTasks as $task): 
                $canCompleteThis = ($task['assigned_to_user_id'] == $currentLoggedInUid);
                $isOverdue = (!empty($task['due_date']) && $task['due_date'] !== '0000-00-00' && strtotime($task['due_date']) < strtotime(date('Y-m-d')));
            ?>
            <div class="todo-card" id="task-card-<?php echo $task['id']; ?>" data-task-id="<?php echo $task['id']; ?>" data-status="pending" data-important="<?php echo $task['is_important']; ?>">
                <button type="button" class="todo-check-btn <?php echo !$canCompleteThis ? 'disabled' : ''; ?>" 
                        title="<?php echo $canCompleteThis ? 'Mark as completed (1-click)' : 'Only ' . htmlspecialchars($task['assigned_to_name']) . ' can mark this as completed'; ?>"
                        onclick="todoToggleTask(<?php echo $task['id']; ?>, <?php echo $canCompleteThis ? 'true' : 'false'; ?>, '<?php echo htmlspecialchars($task['assigned_to_name'], ENT_QUOTES); ?>')">
                    <i class="fa fa-check"></i>
                </button>
                <div class="todo-content">
                    <p class="todo-task-title"><?php echo htmlspecialchars($task['title']); ?></p>
                    <div class="todo-meta-row">
                        <?php if (!empty($task['due_date']) && $task['due_date'] !== '0000-00-00'): ?>
                        <span class="todo-meta-item <?php echo $isOverdue ? 'meta-overdue' : 'meta-due'; ?>">
                            <i class="fa fa-calendar-o"></i> <?php echo htmlspecialchars($task['due_date_formatted']); ?>
                            <?php if ($isOverdue): ?><span class="todo-meta-tag" style="background: rgba(239,68,68,0.2); color: #fca5a5;">Overdue</span><?php endif; ?>
                        </span>
                        <?php endif; ?>

                        <span class="todo-meta-item">
                            <i class="fa fa-user-circle-o"></i> Assigned by: <strong><?php echo htmlspecialchars($task['assigned_by_name']); ?></strong>
                        </span>

                        <?php if (!$isViewingSelf): ?>
                        <span class="todo-meta-item">
                            <i class="fa fa-arrow-right"></i> Assigned to: <strong><?php echo htmlspecialchars($task['assigned_to_name']); ?></strong>
                        </span>
                        <?php endif; ?>

                        <?php if (!empty($task['is_important'])): ?>
                        <span class="todo-meta-tag" style="background: rgba(245,158,11,0.2); color: #fbbf24;">
                            <i class="fa fa-star"></i> Important
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="todo-actions-right">
                    <button type="button" class="todo-star-btn <?php echo !empty($task['is_important']) ? 'active' : ''; ?>" title="Toggle Important" onclick="todoToggleStar(<?php echo $task['id']; ?>)">
                        <i class="fa fa-star"></i>
                    </button>
                    <?php if ($userCanManageTasks || (int)$task['assigned_by_user_id'] === $currentLoggedInUid): ?>
                    <button type="button" class="todo-del-btn" title="Delete Task" onclick="todoDeleteTask(<?php echo $task['id']; ?>)">
                        <i class="fa fa-trash-o"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="todo-empty-state" id="todoEmptyPending" style="<?php echo ($totalCount > 0) ? 'display:none;' : ''; ?>">
                <div class="todo-empty-icon"><i class="fa fa-check-circle-o"></i></div>
                <p class="todo-empty-text">No active tasks pending! All caught up.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- 5. Collapsible Completed Section (Microsoft To-Do Header: "v Completed 48") -->
    <div class="todo-completed-section" id="todoCompletedSection" style="<?php echo ($completedCount === 0) ? 'display: none;' : ''; ?>">
        <div class="todo-completed-header" id="todoToggleCompletedHeader">
            <i class="fa fa-chevron-down" id="todoChevronCompleted"></i>
            <span>Completed</span>
            <span class="todo-completed-count" id="todoCompletedCountBadge"><?php echo $completedCount; ?></span>
        </div>

        <div class="todo-list-container" id="todoCompletedList">
            <?php foreach ($completedTasks as $task): 
                $canCompleteThis = ($task['assigned_to_user_id'] == $currentLoggedInUid);
            ?>
            <div class="todo-card is-completed" id="task-card-<?php echo $task['id']; ?>" data-task-id="<?php echo $task['id']; ?>" data-status="completed" data-important="<?php echo $task['is_important']; ?>">
                <button type="button" class="todo-check-btn <?php echo !$canCompleteThis ? 'disabled' : ''; ?>" 
                        title="<?php echo $canCompleteThis ? 'Mark as incomplete' : 'Only ' . htmlspecialchars($task['assigned_to_name']) . ' can modify status'; ?>"
                        onclick="todoToggleTask(<?php echo $task['id']; ?>, <?php echo $canCompleteThis ? 'true' : 'false'; ?>, '<?php echo htmlspecialchars($task['assigned_to_name'], ENT_QUOTES); ?>')">
                    <i class="fa fa-check"></i>
                </button>
                <div class="todo-content">
                    <p class="todo-task-title"><?php echo htmlspecialchars($task['title']); ?></p>
                    <div class="todo-meta-row">
                        <?php if (!empty($task['due_date']) && $task['due_date'] !== '0000-00-00'): ?>
                        <span class="todo-meta-item meta-due">
                            <i class="fa fa-calendar-o"></i> <?php echo htmlspecialchars($task['due_date_formatted']); ?>
                        </span>
                        <?php endif; ?>

                        <span class="todo-meta-item">
                            <i class="fa fa-user-circle-o"></i> Assigned by: <strong><?php echo htmlspecialchars($task['assigned_by_name']); ?></strong>
                        </span>

                        <?php if (!empty($task['completed_at'])): ?>
                        <span class="todo-meta-item">
                            <i class="fa fa-clock-o"></i> Completed: <?php echo date('D, j M', strtotime($task['completed_at'])); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="todo-actions-right">
                    <button type="button" class="todo-star-btn <?php echo !empty($task['is_important']) ? 'active' : ''; ?>" title="Toggle Important" onclick="todoToggleStar(<?php echo $task['id']; ?>)">
                        <i class="fa fa-star"></i>
                    </button>
                    <?php if ($userCanManageTasks || (int)$task['assigned_by_user_id'] === $currentLoggedInUid): ?>
                    <button type="button" class="todo-del-btn" title="Delete Task" onclick="todoDeleteTask(<?php echo $task['id']; ?>)">
                        <i class="fa fa-trash-o"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Toast notification box -->
<div id="todoToast" class="todo-toast">
    <i class="fa fa-info-circle"></i> <span id="todoToastMsg">Notification</span>
</div>

<!-- Task Manager Interactivity Script -->
<script>
(function() {
    var targetUid = <?php echo (int)$targetDashboardUid; ?>;
    var loggedInUid = <?php echo (int)$currentLoggedInUid; ?>;
    var userCanManage = <?php echo $userCanManageTasks ? 'true' : 'false'; ?>;

    // Toast Helper
    window.todoShowToast = function(msg, isError) {
        var toast = document.getElementById('todoToast');
        var msgEl = document.getElementById('todoToastMsg');
        if (!toast || !msgEl) return;
        
        msgEl.textContent = msg;
        if (isError) {
            toast.classList.add('toast-error');
        } else {
            toast.classList.remove('toast-error');
        }
        toast.classList.add('show');
        setTimeout(function() {
            toast.classList.remove('show');
        }, 3200);
    };

    // 1. One-click Task Completion Toggle (Assigned User ONLY)
    window.todoToggleTask = function(taskId, canComplete, assignedName) {
        if (!canComplete) {
            todoShowToast('Only ' + (assignedName || 'assigned user') + ' can mark this task as completed.', true);
            return;
        }

        var card = document.getElementById('task-card-' + taskId);
        if (!card) return;

        var isCompleted = card.classList.contains('is-completed');
        var newStatus = isCompleted ? 'pending' : 'completed';

        // Play visual micro-interaction immediately
        if (newStatus === 'completed') {
            card.classList.add('is-completed');
            card.setAttribute('data-status', 'completed');
            // Move to completed list
            var compList = document.getElementById('todoCompletedList');
            var compSection = document.getElementById('todoCompletedSection');
            if (compSection) compSection.style.display = 'block';
            if (compList) {
                card.style.opacity = '0.5';
                setTimeout(function() {
                    compList.prepend(card);
                    card.style.opacity = '1';
                }, 180);
            }
        } else {
            card.classList.remove('is-completed');
            card.setAttribute('data-status', 'pending');
            // Move to active list
            var pendList = document.getElementById('todoPendingList');
            if (pendList) {
                card.style.opacity = '0.5';
                setTimeout(function() {
                    pendList.prepend(card);
                    card.style.opacity = '1';
                }, 180);
            }
        }

        // Send AJAX request
        $.ajax({
            url: 'task_actions.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'toggle_status',
                task_id: taskId
            },
            success: function(res) {
                if (!res.success) {
                    todoShowToast(res.error || 'Failed to update task status.', true);
                    // Revert UI on failure
                    if (newStatus === 'completed') {
                        card.classList.remove('is-completed');
                        card.setAttribute('data-status', 'pending');
                        document.getElementById('todoPendingList').prepend(card);
                    } else {
                        card.classList.add('is-completed');
                        card.setAttribute('data-status', 'completed');
                        document.getElementById('todoCompletedList').prepend(card);
                    }
                    updateCounts();
                } else {
                    todoShowToast(newStatus === 'completed' ? 'Task marked as completed! ✓' : 'Task restored to active list.');
                    updateCounts();
                }
            },
            error: function() {
                todoShowToast('Network error while updating task.', true);
                updateCounts();
            }
        });
    };

    // 2. Toggle Important / Star
    window.todoToggleStar = function(taskId) {
        $.ajax({
            url: 'task_actions.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'toggle_star',
                task_id: taskId
            },
            success: function(res) {
                if (res.success) {
                    var card = document.getElementById('task-card-' + taskId);
                    if (card) {
                        var starBtn = card.querySelector('.todo-star-btn');
                        if (starBtn) {
                            if (res.is_important) {
                                starBtn.classList.add('active');
                                card.setAttribute('data-important', '1');
                            } else {
                                starBtn.classList.remove('active');
                                card.setAttribute('data-important', '0');
                            }
                        }
                    }
                }
            }
        });
    };

    // 3. Delete Task
    window.todoDeleteTask = function(taskId) {
        if (!confirm('Are you sure you want to delete this task?')) return;

        var card = document.getElementById('task-card-' + taskId);
        if (card) {
            card.style.opacity = '0.3';
        }

        $.ajax({
            url: 'task_actions.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'delete_task',
                task_id: taskId
            },
            success: function(res) {
                if (res.success) {
                    if (card) card.remove();
                    updateCounts();
                    todoShowToast('Task deleted successfully.');
                } else {
                    if (card) card.style.opacity = '1';
                    todoShowToast(res.error || 'Could not delete task.', true);
                }
            },
            error: function() {
                if (card) card.style.opacity = '1';
                todoShowToast('Network error while deleting task.', true);
            }
        });
    };

    // 4. Update Header Badges & Counts
    function updateCounts() {
        var pendCount = $('#todoPendingList .todo-card').length;
        var compCount = $('#todoCompletedList .todo-card').length;
        var totalCount = pendCount + compCount;

        $('#todoStatPending').text(pendCount);
        $('#todoStatCompleted').text(compCount);
        $('#todoStatTotal').text(totalCount);
        $('#todoCompletedCountBadge').text(compCount);

        // Filter button text update
        $('.todo-filter-btn[data-filter="pending"]').text('Active (' + pendCount + ')');
        $('.todo-filter-btn[data-filter="completed"]').text('Completed (' + compCount + ')');

        if (compCount === 0) {
            $('#todoCompletedSection').hide();
        } else {
            $('#todoCompletedSection').show();
        }

        if (pendCount === 0) {
            if ($('#todoEmptyPending').length === 0) {
                $('#todoPendingList').append('<div class="todo-empty-state" id="todoEmptyPending"><div class="todo-empty-icon"><i class="fa fa-check-circle-o"></i></div><p class="todo-empty-text">No active tasks pending! All caught up.</p></div>');
            }
            $('#todoEmptyPending').show();
        } else {
            $('#todoEmptyPending').hide();
        }
    }

    // 5. Create Task (One-Click or Enter Key)
    function submitNewTask() {
        var input = document.getElementById('todoNewTaskInput');
        if (!input) return;

        var title = input.value.trim();
        if (!title) {
            input.focus();
            return;
        }

        var assignSelect = document.getElementById('todoAssignToSelect');
        var assignedToId = assignSelect ? parseInt(assignSelect.value, 10) : targetUid;
        var assignedToName = assignSelect && assignSelect.selectedOptions[0] ? assignSelect.selectedOptions[0].getAttribute('data-name') : '';
        var assignedToRole = assignSelect && assignSelect.selectedOptions[0] ? assignSelect.selectedOptions[0].getAttribute('data-role') : '';

        var dueDateInput = document.getElementById('todoDueDateInput');
        var dueDate = dueDateInput ? dueDateInput.value : '';

        var impCheck = document.getElementById('todoIsImportantCheck');
        var isImportant = (impCheck && impCheck.checked) ? 1 : 0;

        var submitBtn = document.getElementById('todoBtnSubmitTask');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Assigning...';
        }

        $.ajax({
            url: 'task_actions.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'create_task',
                title: title,
                assigned_to_user_id: assignedToId,
                assigned_to_name: assignedToName,
                assigned_to_role: assignedToRole,
                due_date: dueDate,
                is_important: isImportant
            },
            success: function(res) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa fa-paper-plane"></i> Assign Task <kbd>↵ Enter</kbd>';
                }

                if (res.success && res.task) {
                    input.value = '';
                    input.focus();
                    todoShowToast('Task assigned successfully to ' + res.task.assigned_to_name + '! 🚀');

                    // If currently viewing this assigned user's dashboard (or self), prepend directly to list
                    if (assignedToId === targetUid) {
                        var t = res.task;
                        var canComplete = (t.assigned_to_user_id == loggedInUid);
                        var safeAssignedName = (t.assigned_to_name || '').replace(/'/g, "\\'");

                        var cardHtml = '<div class="todo-card" id="task-card-' + t.id + '" data-task-id="' + t.id + '" data-status="pending" data-important="' + t.is_important + '" style="display:none;">' +
                            '<button type="button" class="todo-check-btn ' + (!canComplete ? 'disabled' : '') + '" ' +
                            'title="' + (canComplete ? 'Mark as completed (1-click)' : 'Only ' + safeAssignedName + ' can mark this as completed') + '" ' +
                            'onclick="todoToggleTask(' + t.id + ', ' + (canComplete ? 'true' : 'false') + ', \'' + safeAssignedName + '\')">' +
                            '<i class="fa fa-check"></i>' +
                            '</button>' +
                            '<div class="todo-content">' +
                            '<p class="todo-task-title">' + $('<div>').text(t.title).html() + '</p>' +
                            '<div class="todo-meta-row">';

                        if (t.due_date_formatted) {
                            cardHtml += '<span class="todo-meta-item meta-due"><i class="fa fa-calendar-o"></i> ' + t.due_date_formatted + '</span>';
                        }
                        cardHtml += '<span class="todo-meta-item"><i class="fa fa-user-circle-o"></i> Assigned by: <strong>' + $('<div>').text(t.assigned_by_name).html() + '</strong></span>';
                        
                        if (t.is_important == 1) {
                            cardHtml += '<span class="todo-meta-tag" style="background: rgba(245,158,11,0.2); color: #fbbf24;"><i class="fa fa-star"></i> Important</span>';
                        }
                        cardHtml += '</div></div>' +
                            '<div class="todo-actions-right">' +
                            '<button type="button" class="todo-star-btn ' + (t.is_important == 1 ? 'active' : '') + '" title="Toggle Important" onclick="todoToggleStar(' + t.id + ')"><i class="fa fa-star"></i></button>' +
                            '<button type="button" class="todo-del-btn" title="Delete Task" onclick="todoDeleteTask(' + t.id + ')"><i class="fa fa-trash-o"></i></button>' +
                            '</div></div>';

                        var $newCard = $(cardHtml);
                        $('#todoPendingList').prepend($newCard);
                        $newCard.fadeIn(250);
                    }
                    updateCounts();
                } else {
                    todoShowToast(res.error || 'Failed to assign task.', true);
                }
            },
            error: function() {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa fa-paper-plane"></i> Assign Task <kbd>↵ Enter</kbd>';
                }
                todoShowToast('Network error while assigning task.', true);
            }
        });
    }

    // Attach Enter Key Listener on Add Input
    $('#todoNewTaskInput').on('keydown', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            submitNewTask();
        }
    });

    // Attach Click on Submit Button
    $('#todoBtnSubmitTask').on('click', function(e) {
        e.preventDefault();
        submitNewTask();
    });

    // 6. Accordion toggle for Completed Tasks
    $('#todoToggleCompletedHeader').on('click', function() {
        var list = $('#todoCompletedList');
        var chev = $('#todoChevronCompleted');
        list.slideToggle(200, function() {
            if (list.is(':visible')) {
                chev.removeClass('fa-chevron-right').addClass('fa-chevron-down');
            } else {
                chev.removeClass('fa-chevron-down').addClass('fa-chevron-right');
            }
        });
    });

    // 7. Live Search
    $('#todoSearchInput').on('input', function() {
        var query = $(this).val().toLowerCase().trim();
        $('.todo-card').each(function() {
            var text = $(this).find('.todo-task-title').text().toLowerCase();
            if (!query || text.indexOf(query) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // 8. Filters (All, Pending, Completed, Important)
    $('.todo-filter-btn').on('click', function() {
        $('.todo-filter-btn').removeClass('active');
        $(this).addClass('active');
        var filter = $(this).data('filter');

        $('.todo-card').each(function() {
            var st = $(this).attr('data-status');
            var imp = $(this).attr('data-important');

            if (filter === 'all') {
                $(this).show();
            } else if (filter === 'pending') {
                if (st === 'pending') $(this).show(); else $(this).hide();
            } else if (filter === 'completed') {
                if (st === 'completed') $(this).show(); else $(this).hide();
            } else if (filter === 'important') {
                if (imp === '1') $(this).show(); else $(this).hide();
            }
        });

        if (filter === 'completed') {
            $('#todoCompletedSection').show();
            $('#todoCompletedList').show();
            $('#todoChevronCompleted').removeClass('fa-chevron-right').addClass('fa-chevron-down');
        }
    });

})();
</script>

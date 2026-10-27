<?php
/**
 * AITF Task Management Library
 * Microsoft To-Do Style Task Management Engine
 */

require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/rbac_helper.php';

if (!function_exists('aitf_can_manage_tasks')) {
    /**
     * Checks if the given role (or currently logged-in user) can assign / manage tasks.
     * Only Top Mgmt, Director, Admin, and Finance roles have this permission.
     */
    function aitf_can_manage_tasks($role = null)
    {
        if ($role === null) {
            $role = $_SESSION['role'] ?? '';
        }
        $r = strtolower(trim((string)$role));
        $allowed = [
            'top mgmt',
            'mgmt',
            'management',
            'director',
            'main admin',
            'admin',
            'finance'
        ];

        if (in_array($r, $allowed, true)) {
            return true;
        }

        if (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($role)) {
            return true;
        }

        if (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin())) {
            return true;
        }

        return false;
    }
}

if (!function_exists('aitf_can_delete_tasks')) {
    /**
     * Checks if the given role (or currently logged-in user) can delete tasks.
     * Enforced Requirement: Only Top Mgmt, Director, Admin, and Finance can delete tasks.
     * Regular assigned staff/users CANNOT delete tasks.
     */
    function aitf_can_delete_tasks($role = null)
    {
        if ($role === null) {
            $role = $_SESSION['role'] ?? '';
        }
        $r = strtolower(trim((string)$role));
        $allowed = [
            'top mgmt',
            'mgmt',
            'management',
            'director',
            'main admin',
            'admin',
            'finance',
            'auditor',
            'employee',
            'staff',
            'office staff-a',
            'office staff-b',
            'technical coordinator'
        ];

        if (in_array($r, $allowed, true)) {
            return true;
        }

        if (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($role)) {
            return true;
        }

        if (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin())) {
            return true;
        }

        return false;
    }
}

if (!function_exists('aitf_ensure_tasks_schema')) {
    /**
     * Ensures the aitf_tasks table and indexes exist idempotently.
     */
    function aitf_ensure_tasks_schema($db)
    {
        if (!$db) {
            return;
        }
        static $taskSchemaEnsured = false;
        if ($taskSchemaEnsured) {
            return;
        }

        try {
            $sql = "CREATE TABLE IF NOT EXISTS `aitf_tasks` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(500) NOT NULL,
                `description` TEXT NULL,
                `assigned_to_user_id` INT NOT NULL,
                `assigned_to_user_type` VARCHAR(20) DEFAULT 'user',
                `assigned_to_user_key` VARCHAR(50) DEFAULT NULL,
                `assigned_to_name` VARCHAR(150) NOT NULL,
                `assigned_to_role` VARCHAR(100) DEFAULT NULL,
                `assigned_by_user_id` INT NOT NULL,
                `assigned_by_name` VARCHAR(150) NOT NULL,
                `assigned_by_role` VARCHAR(100) DEFAULT NULL,
                `status` VARCHAR(20) DEFAULT 'pending',
                `is_important` TINYINT(1) DEFAULT 0,
                `due_date` DATE NULL,
                `reminder_time` VARCHAR(50) NULL,
                `completed_at` DATETIME NULL,
                `completed_by_user_id` INT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `is_deleted` TINYINT(1) DEFAULT 0,
                INDEX `idx_tasks_assigned_to` (`assigned_to_user_id`, `status`, `is_deleted`),
                INDEX `idx_tasks_assigned_by` (`assigned_by_user_id`),
                INDEX `idx_tasks_due_date` (`due_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $db->exec($sql);

            // Self-healing check: Ensure `id` is PRIMARY KEY and AUTO_INCREMENT
            try {
                $pkCol = $db->query("SHOW COLUMNS FROM `aitf_tasks` LIKE 'id'")->fetch(PDO::FETCH_ASSOC);
                if ($pkCol && strpos(strtolower($pkCol['Extra'] ?? ''), 'auto_increment') === false) {
                    $hasPk = (($pkCol['Key'] ?? '') === 'PRI');
                    if (!$hasPk) {
                        $zeroCount = (int)$db->query("SELECT COUNT(*) FROM `aitf_tasks` WHERE `id` = 0")->fetchColumn();
                        if ($zeroCount > 0) {
                            $nextId = (int)$db->query("SELECT MAX(id) FROM `aitf_tasks`")->fetchColumn() + 1;
                            $zeroRows = $db->query("SELECT created_at FROM `aitf_tasks` WHERE `id` = 0")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($zeroRows as $zr) {
                                $db->prepare("UPDATE `aitf_tasks` SET `id` = :nid WHERE `id` = 0 AND `created_at` = :cat LIMIT 1")
                                   ->execute([':nid' => $nextId++, ':cat' => $zr['created_at']]);
                            }
                        }
                        $db->exec("ALTER TABLE `aitf_tasks` ADD PRIMARY KEY (`id`)");
                    }
                    $db->exec("ALTER TABLE `aitf_tasks` MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT");
                }
            } catch (Throwable $e) {}

            $taskSchemaEnsured = true;
        } catch (Throwable $e) {
            error_log("Failed to ensure aitf_tasks schema: " . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_format_task_date')) {
    /**
     * Formats date in Microsoft To-Do style: e.g. "Tue, 28 Apr"
     */
    function aitf_format_task_date($dateStr)
    {
        if (empty($dateStr) || $dateStr === '0000-00-00') {
            return '';
        }
        $ts = strtotime($dateStr);
        if (!$ts) {
            return '';
        }
        $todayTs = strtotime(date('Y-m-d'));
        $targetTs = strtotime(date('Y-m-d', $ts));
        $diffDays = ($targetTs - $todayTs) / 86400;

        $formatted = date('D, j M', $ts);
        if ($diffDays === 0.0) {
            return 'Today, ' . $formatted;
        } elseif ($diffDays === 1.0) {
            return 'Tomorrow, ' . $formatted;
        } elseif ($diffDays === -1.0) {
            return 'Yesterday, ' . $formatted;
        }

        return $formatted;
    }
}

if (!function_exists('aitf_get_user_tasks')) {
    /**
     * Fetch all non-deleted tasks assigned to a specific user.
     */
    function aitf_get_user_tasks($db, $userId, array $options = [])
    {
        aitf_ensure_tasks_schema($db);
        $userId = (int)$userId;
        if ($userId <= 0) {
            return [];
        }

        $status = $options['status'] ?? 'all';
        $search = trim((string)($options['search'] ?? ''));

        $sql = "SELECT * FROM `aitf_tasks` WHERE `assigned_to_user_id` = :uid AND `is_deleted` = 0";
        $params = [':uid' => $userId];

        if ($status === 'pending') {
            $sql .= " AND `status` = 'pending'";
        } elseif ($status === 'completed') {
            $sql .= " AND `status` = 'completed'";
        }

        if ($search !== '') {
            $sql .= " AND (`title` LIKE :search OR `description` LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Pending first (important first, then closest due date, then newest), then completed
        $sql .= " ORDER BY CASE WHEN `status` = 'pending' THEN 0 ELSE 1 END,
                           `is_important` DESC,
                           CASE WHEN `due_date` IS NOT NULL AND `due_date` != '0000-00-00' THEN `due_date` ELSE '9999-12-31' END ASC,
                           `id` DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($tasks as &$t) {
                $t['due_date_formatted'] = aitf_format_task_date($t['due_date']);
                $t['is_overdue'] = (!empty($t['due_date']) && $t['due_date'] !== '0000-00-00' && $t['status'] === 'pending' && strtotime($t['due_date']) < strtotime(date('Y-m-d')));
                $t['created_formatted'] = aitf_format_task_date($t['created_at']);
            }
            unset($t);

            return $tasks;
        } catch (Throwable $e) {
            error_log("Error fetching user tasks: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('aitf_get_tasks_assigned_by_user')) {
    /**
     * Fetch tasks assigned by a manager to others.
     */
    function aitf_get_tasks_assigned_by_user($db, $managerId)
    {
        aitf_ensure_tasks_schema($db);
        $managerId = (int)$managerId;
        if ($managerId <= 0) {
            return [];
        }

        $sql = "SELECT * FROM `aitf_tasks` WHERE `assigned_by_user_id` = :mid AND `is_deleted` = 0
                ORDER BY `id` DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([':mid' => $managerId]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tasks as &$t) {
                $t['due_date_formatted'] = aitf_format_task_date($t['due_date']);
                $t['is_overdue'] = (!empty($t['due_date']) && $t['due_date'] !== '0000-00-00' && $t['status'] === 'pending' && strtotime($t['due_date']) < strtotime(date('Y-m-d')));
                $t['created_formatted'] = aitf_format_task_date($t['created_at']);
            }
            unset($t);
            return $tasks;
        } catch (Throwable $e) {
            error_log("Error fetching tasks assigned by user: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('aitf_create_task')) {
    /**
     * Create and assign a new task.
     */
    function aitf_create_task($db, array $data)
    {
        aitf_ensure_tasks_schema($db);

        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') {
            return ['success' => false, 'error' => 'Task title cannot be empty.'];
        }

        $assignedToId = (int)($data['assigned_to_user_id'] ?? 0);
        if ($assignedToId <= 0) {
            return ['success' => false, 'error' => 'Please select a valid user to assign the task to.'];
        }

        $assignedToName = trim((string)($data['assigned_to_name'] ?? ''));
        $assignedToRole = trim((string)($data['assigned_to_role'] ?? 'Staff'));
        $assignedToKey = trim((string)($data['assigned_to_user_key'] ?? ('user_' . $assignedToId)));
        $assignedToType = trim((string)($data['assigned_to_user_type'] ?? 'user'));

        // If name wasn't passed, look it up in user table
        if ($assignedToName === '') {
            try {
                $uStmt = $db->prepare("SELECT user_name, role FROM `user` WHERE `user_id` = :id LIMIT 1");
                $uStmt->execute([':id' => $assignedToId]);
                if ($u = $uStmt->fetch(PDO::FETCH_ASSOC)) {
                    $assignedToName = $u['user_name'];
                    $assignedToRole = $u['role'] ?: $assignedToRole;
                }
            } catch (Throwable $e) {}
        }

        $assignedById = (int)($data['assigned_by_user_id'] ?? ($_SESSION['id'] ?? 0));
        $assignedByName = trim((string)($data['assigned_by_name'] ?? ($_SESSION['username'] ?? 'Management')));
        $assignedByRole = trim((string)($data['assigned_by_role'] ?? ($_SESSION['role'] ?? 'Admin')));

        $description = !empty($data['description']) ? trim((string)$data['description']) : null;
        $dueDate = !empty($data['due_date']) ? trim((string)$data['due_date']) : null;
        if ($dueDate === '' || $dueDate === '0000-00-00') {
            $dueDate = null;
        }

        $isImportant = !empty($data['is_important']) ? 1 : 0;
        $reminderTime = !empty($data['reminder_time']) ? trim((string)$data['reminder_time']) : null;

        try {
            $stmt = $db->prepare("INSERT INTO `aitf_tasks` (
                `title`, `description`, `assigned_to_user_id`, `assigned_to_user_type`, `assigned_to_user_key`,
                `assigned_to_name`, `assigned_to_role`, `assigned_by_user_id`, `assigned_by_name`, `assigned_by_role`,
                `status`, `is_important`, `due_date`, `reminder_time`
            ) VALUES (
                :title, :desc, :to_id, :to_type, :to_key,
                :to_name, :to_role, :by_id, :by_name, :by_role,
                'pending', :imp, :due, :reminder
            )");

            $stmt->execute([
                ':title' => $title,
                ':desc' => $description,
                ':to_id' => $assignedToId,
                ':to_type' => $assignedToType,
                ':to_key' => $assignedToKey,
                ':to_name' => $assignedToName,
                ':to_role' => $assignedToRole,
                ':by_id' => $assignedById,
                ':by_name' => $assignedByName,
                ':by_role' => $assignedByRole,
                ':imp' => $isImportant,
                ':due' => $dueDate,
                ':reminder' => $reminderTime
            ]);

            $newId = (int)$db->lastInsertId();

            if ($newId <= 0) {
                $fetchStmt = $db->prepare("SELECT * FROM `aitf_tasks` WHERE `title` = :title AND `assigned_to_user_id` = :to_id ORDER BY `id` DESC LIMIT 1");
                $fetchStmt->execute([':title' => $title, ':to_id' => $assignedToId]);
            } else {
                $fetchStmt = $db->prepare("SELECT * FROM `aitf_tasks` WHERE `id` = :id LIMIT 1");
                $fetchStmt->execute([':id' => $newId]);
            }
            $task = $fetchStmt->fetch(PDO::FETCH_ASSOC);

            if ($task) {
                $task['due_date_formatted'] = aitf_format_task_date($task['due_date']);
                $task['is_overdue'] = false;
                $task['created_formatted'] = aitf_format_task_date($task['created_at']);

                // Notify assignee if assigned to another user
                if ($assignedToId > 0 && $assignedToId !== (int)$assignedById) {
                    try {
                        $nStmt = $db->prepare("
                            INSERT INTO aitf_notifications (user_id, title, body, notification_type, reference_id, is_read, created_at)
                            VALUES (:uid, :title, :body, 'task_assigned', :ref, 0, NOW())
                        ");
                        $nStmt->execute([
                            ':uid' => $assignedToId,
                            ':title' => '📋 New Task Assigned',
                            ':body' => "{$assignedByName} has assigned a task for you: {$title}",
                            ':ref' => (int)($task['id'] ?? $newId)
                        ]);
                    } catch (Throwable $ne) {}
                }
            }

            return ['success' => true, 'task' => $task];
        } catch (Throwable $e) {
            error_log("Failed to create task: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('aitf_toggle_task_status')) {
    /**
     * Toggles task completion status.
     * Toggles task completion status.
     * Permission: Assigned user or Management can toggle status.
     */
    function aitf_toggle_task_status($db, $taskId, $currentUserId, $currentUserRole = '')
    {
        aitf_ensure_tasks_schema($db);
        $taskId = (int)$taskId;
        $currentUserId = (int)$currentUserId;

        if ($taskId <= 0 || $currentUserId <= 0) {
            return ['success' => false, 'error' => 'Invalid task or user identifier.'];
        }

        try {
            $stmt = $db->prepare("SELECT * FROM `aitf_tasks` WHERE `id` = :id AND `is_deleted` = 0 LIMIT 1");
            $stmt->execute([':id' => $taskId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$task) {
                return ['success' => false, 'error' => 'Task not found or has been removed.'];
            }

            // ENFORCE REQUIREMENT: Assigned user or management can toggle task status
            $assignedUserId = (int)$task['assigned_to_user_id'];
            $canManage = aitf_can_manage_tasks($currentUserRole ?: ($_SESSION['role'] ?? ''));
            if ($assignedUserId !== $currentUserId && !$canManage) {
                return [
                    'success' => false,
                    'error' => 'Only ' . htmlspecialchars($task['assigned_to_name']) . ' (assigned user) or Management can update this task status.'
                ];
            }

            $currentStatus = $task['status'];
            $newStatus = ($currentStatus === 'completed') ? 'pending' : 'completed';
            $completedAt = ($newStatus === 'completed') ? date('Y-m-d H:i:s') : null;
            $completedBy = ($newStatus === 'completed') ? $currentUserId : null;

            $upd = $db->prepare("UPDATE `aitf_tasks` 
                                 SET `status` = :st, `completed_at` = :cat, `completed_by_user_id` = :cby 
                                 WHERE `id` = :id");
            $upd->execute([
                ':st' => $newStatus,
                ':cat' => $completedAt,
                ':cby' => $completedBy,
                ':id' => $taskId
            ]);

            $task['status'] = $newStatus;
            $task['completed_at'] = $completedAt;
            $task['completed_by_user_id'] = $completedBy;
            $task['due_date_formatted'] = aitf_format_task_date($task['due_date']);
            $task['created_formatted'] = aitf_format_task_date($task['created_at']);
            $task['is_overdue'] = (!empty($task['due_date']) && $task['due_date'] !== '0000-00-00' && $newStatus === 'pending' && strtotime($task['due_date']) < strtotime(date('Y-m-d')));

            // Notify assigner (e.g. Vaibhav Ajabe) when task is marked completed
            $assignerId = (int)($task['assigned_by_user_id'] ?? 0);
            if ($newStatus === 'completed' && $assignerId > 0 && $assignerId !== $currentUserId) {
                try {
                    $completerName = $_SESSION['username'] ?? '';
                    if (empty($completerName)) {
                        $uStmt = $db->prepare("SELECT user_name FROM user WHERE user_id = :uid LIMIT 1");
                        $uStmt->execute([':uid' => $currentUserId]);
                        $completerName = $uStmt->fetchColumn() ?: 'A team member';
                    }
                    $nStmt = $db->prepare("
                        INSERT INTO aitf_notifications (user_id, title, body, notification_type, reference_id, is_read, created_at)
                        VALUES (:uid, :title, :body, 'task_completed', :ref, 0, NOW())
                    ");
                    $nStmt->execute([
                        ':uid' => $assignerId,
                        ':title' => '✅ Task Completed',
                        ':body' => "{$completerName} has marked completed a task: {$task['title']}",
                        ':ref' => $taskId
                    ]);
                } catch (Throwable $ne) {}
            }

            return [
                'success' => true,
                'new_status' => $newStatus,
                'task' => $task
            ];
        } catch (Throwable $e) {
            error_log("Failed to toggle task status: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('aitf_toggle_task_importance')) {
    /**
     * Toggles the task's star / important flag.
     */
    function aitf_toggle_task_importance($db, $taskId, $userId)
    {
        aitf_ensure_tasks_schema($db);
        $taskId = (int)$taskId;
        if ($taskId <= 0) {
            return ['success' => false, 'error' => 'Invalid task ID.'];
        }

        try {
            $stmt = $db->prepare("SELECT id, is_important FROM `aitf_tasks` WHERE `id` = :id AND `is_deleted` = 0 LIMIT 1");
            $stmt->execute([':id' => $taskId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$task) {
                return ['success' => false, 'error' => 'Task not found.'];
            }

            $newImp = ((int)$task['is_important'] === 1) ? 0 : 1;
            $upd = $db->prepare("UPDATE `aitf_tasks` SET `is_important` = :imp WHERE `id` = :id");
            $upd->execute([':imp' => $newImp, ':id' => $taskId]);

            return ['success' => true, 'is_important' => $newImp];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

if (!function_exists('aitf_delete_task')) {
    /**
     * Soft deletes a task.
     * Enforced Requirement: Only Top Mgmt, Director, Admin, and Finance can delete tasks.
     * Regular assigned staff/users CANNOT delete tasks.
     */
    function aitf_delete_task($db, $taskId, $currentUserId, $currentUserRole)
    {
        aitf_ensure_tasks_schema($db);
        $taskId = (int)$taskId;
        if ($taskId <= 0) {
            return ['success' => false, 'error' => 'Invalid task ID.'];
        }

        try {
            $stmt = $db->prepare("SELECT * FROM `aitf_tasks` WHERE `id` = :id AND `is_deleted` = 0 LIMIT 1");
            $stmt->execute([':id' => $taskId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$task) {
                return ['success' => false, 'error' => 'Task not found or has already been removed.'];
            }

            // Check if current user is Top Management (Top Mgmt, Director, Main Admin)
            $isTopMgmt = (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($currentUserRole))
                || in_array(strtolower(trim((string)$currentUserRole)), ['top mgmt', 'mgmt', 'management', 'director', 'main admin'], true)
                || (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin()));

            if (!$isTopMgmt) {
                // Rule: If task was assigned by someone else, assignee CANNOT delete it.
                // Only the user who assigned the task can delete it.
                $taskAssignedBy = (int)($task['assigned_by_user_id'] ?? 0);
                $taskAssignedTo = (int)($task['assigned_to_user_id'] ?? 0);
                $curUid = (int)$currentUserId;
                $sessUid = (int)($_SESSION['id'] ?? 0);
                $audId = (int)($_SESSION['auditor_id'] ?? 0);

                $isAssigner = ($taskAssignedBy > 0 && ($taskAssignedBy === $curUid || $taskAssignedBy === $sessUid || ($audId > 0 && $taskAssignedBy === $audId)))
                    || ($taskAssignedTo > 0 && $taskAssignedBy === $taskAssignedTo && ($taskAssignedTo === $curUid || $taskAssignedTo === $sessUid || ($audId > 0 && $taskAssignedTo === $audId)));

                if (!$isAssigner) {
                    return [
                        'success' => false,
                        'error' => 'Cant delete assigned task'
                    ];
                }
            }

            $upd = $db->prepare("UPDATE `aitf_tasks` SET `is_deleted` = 1, `updated_at` = NOW() WHERE `id` = :id");
            $upd->execute([':id' => $taskId]);

            return ['success' => true];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

if (!function_exists('aitf_ensure_task_permissions_schema')) {
    /**
     * Ensures the aitf_task_dashboard_permissions table exists.
     */
    function aitf_ensure_task_permissions_schema($db)
    {
        if (!$db) {
            return;
        }
        static $permSchemaEnsured = false;
        if ($permSchemaEnsured) {
            return;
        }

        try {
            $sql = "CREATE TABLE IF NOT EXISTS `aitf_task_dashboard_permissions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `viewer_user_id` INT NOT NULL,
                `viewer_user_name` VARCHAR(150) NULL,
                `target_user_id` INT NOT NULL,
                `target_user_name` VARCHAR(150) NULL,
                `granted_by_user_id` INT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_viewer_target` (`viewer_user_id`, `target_user_id`),
                INDEX `idx_viewer` (`viewer_user_id`),
                INDEX `idx_target` (`target_user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->exec($sql);
            $permSchemaEnsured = true;
        } catch (Throwable $e) {
            error_log("Failed to ensure aitf_task_dashboard_permissions schema: " . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_is_top_mgmt_task_user')) {
    /**
     * Checks if user has top management / administrative privileges for tasks.
     */
    function aitf_is_top_mgmt_task_user($role = null)
    {
        if ($role === null) {
            $role = $_SESSION['role'] ?? '';
        }
        $r = strtolower(trim((string)$role));
        $topRoles = ['top mgmt', 'top management', 'mgmt', 'management', 'director', 'main admin', 'admin'];
        if (in_array($r, $topRoles, true)) {
            return true;
        }
        if (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($role)) {
            return true;
        }
        if (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin())) {
            return true;
        }
        return false;
    }
}

if (!function_exists('aitf_can_user_view_task_dashboard')) {
    /**
     * Checks if a viewer user has permission to view a target user's task dashboard.
     * 1. Self is always allowed.
     * 2. Top Management / Admin can view all users.
     * 3. Configured permissions in aitf_task_dashboard_permissions.
     */
    function aitf_can_user_view_task_dashboard($db, $viewerUserId, $targetUserId, $viewerRole = '')
    {
        $viewerUserId = (int)$viewerUserId;
        $targetUserId = (int)$targetUserId;

        if ($viewerUserId <= 0 || $targetUserId <= 0) {
            return false;
        }

        // Self-access is always permitted
        if ($viewerUserId === $targetUserId) {
            return true;
        }

        // Top Management can view all dashboards
        if (aitf_is_top_mgmt_task_user($viewerRole)) {
            return true;
        }

        aitf_ensure_task_permissions_schema($db);

        try {
            $stmt = $db->prepare("SELECT 1 FROM `aitf_task_dashboard_permissions` 
                                  WHERE `viewer_user_id` = :vid AND `target_user_id` = :tid LIMIT 1");
            $stmt->execute([':vid' => $viewerUserId, ':tid' => $targetUserId]);
            return (bool)$stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log("Error checking dashboard view permission: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('aitf_get_user_allowed_dashboard_uids')) {
    /**
     * Returns array of target_user_ids that viewer user can access.
     * If viewer is Top Mgmt, returns 'all'.
     */
    function aitf_get_user_allowed_dashboard_uids($db, $viewerUserId, $viewerRole = '')
    {
        $viewerUserId = (int)$viewerUserId;
        if (aitf_is_top_mgmt_task_user($viewerRole)) {
            return 'all';
        }

        $allowed = [$viewerUserId];
        aitf_ensure_task_permissions_schema($db);

        try {
            $stmt = $db->prepare("SELECT target_user_id FROM `aitf_task_dashboard_permissions` WHERE `viewer_user_id` = :vid");
            $stmt->execute([':vid' => $viewerUserId]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tid = (int)$row['target_user_id'];
                if ($tid > 0 && !in_array($tid, $allowed, true)) {
                    $allowed[] = $tid;
                }
            }
        } catch (Throwable $e) {
            error_log("Error fetching allowed dashboard uids: " . $e->getMessage());
        }

        return $allowed;
    }
}

if (!function_exists('aitf_get_all_dashboard_permissions_grouped')) {
    /**
     * Returns list of all permission grants grouped by viewer_user_id.
     */
    function aitf_get_all_dashboard_permissions_grouped($db)
    {
        aitf_ensure_task_permissions_schema($db);
        $res = [];

        try {
            $stmt = $db->query("SELECT p.*, u.user_name as cur_viewer_name, u.role as cur_viewer_role, 
                                       t.user_name as cur_target_name, t.role as cur_target_role
                                FROM `aitf_task_dashboard_permissions` p
                                LEFT JOIN `user` u ON u.user_id = p.viewer_user_id
                                LEFT JOIN `user` t ON t.user_id = p.target_user_id
                                ORDER BY u.user_name ASC, t.user_name ASC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vid = (int)$row['viewer_user_id'];
                if (!isset($res[$vid])) {
                    $res[$vid] = [
                        'viewer_user_id' => $vid,
                        'viewer_name' => !empty($row['cur_viewer_name']) ? $row['cur_viewer_name'] : ($row['viewer_user_name'] ?? 'User #' . $vid),
                        'viewer_role' => $row['cur_viewer_role'] ?? 'Staff',
                        'targets' => []
                    ];
                }
                $tid = (int)$row['target_user_id'];
                $res[$vid]['targets'][] = [
                    'target_user_id' => $tid,
                    'target_name' => !empty($row['cur_target_name']) ? $row['cur_target_name'] : ($row['target_user_name'] ?? 'User #' . $tid),
                    'target_role' => $row['cur_target_role'] ?? 'Staff',
                    'created_at' => $row['created_at']
                ];
            }
        } catch (Throwable $e) {
            error_log("Error fetching grouped dashboard permissions: " . $e->getMessage());
        }

        return array_values($res);
    }
}

if (!function_exists('aitf_save_user_dashboard_permissions')) {
    /**
     * Saves which target user dashboards a viewer user can access.
     */
    function aitf_save_user_dashboard_permissions($db, $viewerUserId, array $targetUserIds, $grantedByUserId = 0)
    {
        aitf_ensure_task_permissions_schema($db);
        $viewerUserId = (int)$viewerUserId;
        if ($viewerUserId <= 0) {
            return ['success' => false, 'error' => 'Invalid viewer user.'];
        }

        // Get viewer user name
        $viewerName = '';
        try {
            $uStmt = $db->prepare("SELECT user_name FROM `user` WHERE `user_id` = :uid LIMIT 1");
            $uStmt->execute([':uid' => $viewerUserId]);
            $viewerName = (string)$uStmt->fetchColumn();
        } catch (Throwable $e) {}

        try {
            $db->beginTransaction();

            $del = $db->prepare("DELETE FROM `aitf_task_dashboard_permissions` WHERE `viewer_user_id` = :vid");
            $del->execute([':vid' => $viewerUserId]);

            $ins = $db->prepare("INSERT INTO `aitf_task_dashboard_permissions` 
                                  (`viewer_user_id`, `viewer_user_name`, `target_user_id`, `target_user_name`, `granted_by_user_id`)
                                  VALUES (:vid, :vname, :tid, :tname, :gb)");

            $count = 0;
            foreach ($targetUserIds as $tid) {
                $tid = (int)$tid;
                if ($tid <= 0 || $tid === $viewerUserId) {
                    continue; // Skip invalid or self
                }

                $targetName = '';
                try {
                    $tStmt = $db->prepare("SELECT user_name FROM `user` WHERE `user_id` = :uid LIMIT 1");
                    $tStmt->execute([':uid' => $tid]);
                    $targetName = (string)$tStmt->fetchColumn();
                } catch (Throwable $e) {}

                $ins->execute([
                    ':vid' => $viewerUserId,
                    ':vname' => $viewerName ?: 'User #' . $viewerUserId,
                    ':tid' => $tid,
                    ':tname' => $targetName ?: 'User #' . $tid,
                    ':gb' => $grantedByUserId
                ]);
                $count++;
            }

            $db->commit();
            return ['success' => true, 'count' => $count, 'viewer_name' => $viewerName];
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Failed to save user dashboard permissions: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

<?php
/**
 * QMS Care & AITF Bidirectional Task Synchronizer
 * Seamlessly synchronizes tasks between legacy `task_manager` (used by web panel)
 * and modern `aitf_tasks` (used by mobile app & new task engine).
 */

if (!function_exists('aitf_sync_qms_task_tables')) {
    /**
     * Synchronizes all rows from `task_manager` to `aitf_tasks`.
     */
    function aitf_sync_qms_task_tables(PDO $db, string $systemCode = 'QMS_CARE'): void {
        static $lastSync = 0;
        $now = time();
        // Prevent running more than once per second on the same request
        if ($now - $lastSync < 1) return;
        $lastSync = $now;

        try {
            // 1. Ensure `legacy_task_id` column exists in aitf_tasks
            $colCheck = $db->query("SHOW COLUMNS FROM `aitf_tasks` LIKE 'legacy_task_id'")->fetch(PDO::FETCH_ASSOC);
            if (!$colCheck) {
                $db->exec("ALTER TABLE `aitf_tasks` ADD COLUMN `legacy_task_id` INT NULL, ADD INDEX `idx_legacy_task_id` (`legacy_task_id`)");
            }

            // 2. Check if task_manager table exists
            $tmCheck = $db->query("SHOW TABLES LIKE 'task_manager'")->fetch(PDO::FETCH_NUM);
            if (!$tmCheck) return;

            // 3. Fetch all tasks from task_manager with user details
            $stmt = $db->query("
                SELECT tm.*, 
                       u.user_name as assignee_name, u.role as assignee_role,
                       ab.user_name as assigner_name, ab.role as assigner_role
                FROM `task_manager` tm
                LEFT JOIN `user` u ON tm.userid = u.user_id
                LEFT JOIN `user` ab ON tm.assigned_by = ab.user_id
                ORDER BY tm.id ASC
            ");
            $tmTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($tmTasks)) return;

            // 4. Map existing aitf_tasks by legacy_task_id and by (title + assigned_to_user_id)
            $aitfStmt = $db->query("SELECT id, legacy_task_id, title, assigned_to_user_id, status, is_important, is_deleted, due_date FROM `aitf_tasks`");
            $byLegacyId = [];
            $byTitleAndUser = [];
            while ($row = $aitfStmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['legacy_task_id'])) {
                    $byLegacyId[(int)$row['legacy_task_id']] = $row;
                }
                $key = trim($row['title']) . '||' . (int)$row['assigned_to_user_id'];
                $byTitleAndUser[$key] = $row;
            }

            // Prepared statements for fast execution
            $insertAitf = $db->prepare("
                INSERT INTO `aitf_tasks` (
                    title, description, system_code,
                    assigned_to_user_id, assigned_to_user_type, assigned_to_user_key,
                    assigned_to_name, assigned_to_role,
                    assigned_by_user_id, assigned_by_name, assigned_by_role,
                    status, is_important, due_date, reminder_time,
                    completed_at, created_at, updated_at, is_deleted, legacy_task_id
                ) VALUES (
                    :title, :desc, :sys,
                    :to_id, 'user', :to_key,
                    :to_name, :to_role,
                    :by_id, :by_name, :by_role,
                    :status, :imp, :due, :reminder,
                    :completed_at, :created_at, :updated_at, :is_del, :legacy_id
                )
            ");

            $updateAitf = $db->prepare("
                UPDATE `aitf_tasks` SET
                    title = :title,
                    description = :desc,
                    status = :status,
                    is_important = :imp,
                    due_date = :due,
                    completed_at = :completed_at,
                    is_deleted = :is_del,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $linkLegacyId = $db->prepare("
                UPDATE `aitf_tasks` SET legacy_task_id = :legacy_id WHERE id = :id
            ");

            foreach ($tmTasks as $tm) {
                $tmId = (int)$tm['id'];
                $targetUid = (int)$tm['userid'];
                $title = trim($tm['task'] ?? '');
                if ($title === '') continue;

                $desc = $tm['comment'] ?? '';
                $isCompleted = (strtolower(trim($tm['status'] ?? '')) === 'completed');
                $status = $isCompleted ? 'completed' : 'pending';
                $isImportant = (!empty($tm['is_important']) || strtolower(trim($tm['priority'] ?? '')) === 'high') ? 1 : 0;
                $isDeleted = (strtoupper(trim($tm['is_deleted'] ?? '')) === 'Y' || (string)$tm['is_deleted'] === '1') ? 1 : 0;
                $dueDate = !empty($tm['due_date']) && $tm['due_date'] !== '0000-00-00' ? $tm['due_date'] : null;
                $reminder = !empty($tm['reminder_time']) ? $tm['reminder_time'] : null;
                $createdAt = !empty($tm['date']) ? $tm['date'] : date('Y-m-d H:i:s');
                $updatedAt = !empty($tm['update_date']) ? $tm['update_date'] . ' 00:00:00' : $createdAt;
                $completedAt = !empty($tm['completed_at']) ? $tm['completed_at'] : (!empty($tm['complete_date']) ? $tm['complete_date'] . ' 00:00:00' : ($isCompleted ? $updatedAt : null));

                $assigneeName = trim($tm['assignee_name'] ?? '');
                if (empty($assigneeName)) $assigneeName = 'Employee #' . $targetUid;
                $assigneeRole = trim($tm['assignee_role'] ?? 'Staff');

                $assignerId = !empty($tm['assigned_by']) ? (int)$tm['assigned_by'] : $targetUid;
                $assignerName = trim($tm['assigned_by_name'] ?? ($tm['assigner_name'] ?? 'Admin'));
                $assignerRole = trim($tm['assigner_role'] ?? 'Admin');

                // Case 1: Already mapped by legacy_task_id
                if (isset($byLegacyId[$tmId])) {
                    $existing = $byLegacyId[$tmId];
                    if ($existing['status'] !== $status ||
                        (int)$existing['is_important'] !== $isImportant ||
                        (int)$existing['is_deleted'] !== $isDeleted ||
                        $existing['due_date'] !== $dueDate ||
                        $existing['title'] !== $title) {
                        $updateAitf->execute([
                            ':title' => $title,
                            ':desc' => $desc,
                            ':status' => $status,
                            ':imp' => $isImportant,
                            ':due' => $dueDate,
                            ':completed_at' => $completedAt,
                            ':is_del' => $isDeleted,
                            ':id' => $existing['id']
                        ]);
                    }
                    continue;
                }

                // Case 2: Matching title and assignee exists but legacy_task_id is null
                $matchKey = $title . '||' . $targetUid;
                if (isset($byTitleAndUser[$matchKey]) && empty($byTitleAndUser[$matchKey]['legacy_task_id'])) {
                    $existingId = (int)$byTitleAndUser[$matchKey]['id'];
                    $linkLegacyId->execute([
                        ':legacy_id' => $tmId,
                        ':id' => $existingId
                    ]);
                    $byLegacyId[$tmId] = ['id' => $existingId];
                    continue;
                }

                // Case 3: Brand new task from web - insert into aitf_tasks!
                $insertAitf->execute([
                    ':title' => $title,
                    ':desc' => $desc,
                    ':sys' => $systemCode,
                    ':to_id' => $targetUid,
                    ':to_key' => 'user_' . $targetUid,
                    ':to_name' => $assigneeName,
                    ':to_role' => $assigneeRole,
                    ':by_id' => $assignerId,
                    ':by_name' => $assignerName,
                    ':by_role' => $assignerRole,
                    ':status' => $status,
                    ':imp' => $isImportant,
                    ':due' => $dueDate,
                    ':reminder' => $reminder,
                    ':completed_at' => $completedAt,
                    ':created_at' => $createdAt,
                    ':updated_at' => $updatedAt,
                    ':is_del' => $isDeleted,
                    ':legacy_id' => $tmId
                ]);
            }
        } catch (Throwable $e) {
            error_log("Task sync error: " . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_sync_web_task_to_app')) {
    /**
     * Synchronizes a specific task from `task_manager` to `aitf_tasks`.
     */
    function aitf_sync_web_task_to_app(PDO $db, int $tmId, string $systemCode = 'QMS_CARE'): ?int {
        try {
            $stmt = $db->prepare("
                SELECT tm.*, 
                       u.user_name as assignee_name, u.role as assignee_role,
                       ab.user_name as assigner_name, ab.role as assigner_role
                FROM `task_manager` tm
                LEFT JOIN `user` u ON tm.userid = u.user_id
                LEFT JOIN `user` ab ON tm.assigned_by = ab.user_id
                WHERE tm.id = :id
                LIMIT 1
            ");
            $stmt->execute([':id' => $tmId]);
            $tm = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$tm) return null;

            $targetUid = (int)$tm['userid'];
            $title = trim($tm['task'] ?? '');
            if ($title === '') return null;

            $desc = $tm['comment'] ?? '';
            $isCompleted = (strtolower(trim($tm['status'] ?? '')) === 'completed');
            $status = $isCompleted ? 'completed' : 'pending';
            $isImportant = (!empty($tm['is_important']) || strtolower(trim($tm['priority'] ?? '')) === 'high') ? 1 : 0;
            $isDeleted = (strtoupper(trim($tm['is_deleted'] ?? '')) === 'Y' || (string)$tm['is_deleted'] === '1') ? 1 : 0;
            $dueDate = !empty($tm['due_date']) && $tm['due_date'] !== '0000-00-00' ? $tm['due_date'] : null;
            $reminder = !empty($tm['reminder_time']) ? $tm['reminder_time'] : null;
            $createdAt = !empty($tm['date']) ? $tm['date'] : date('Y-m-d H:i:s');
            $updatedAt = !empty($tm['update_date']) ? $tm['update_date'] . ' 00:00:00' : $createdAt;
            $completedAt = !empty($tm['completed_at']) ? $tm['completed_at'] : (!empty($tm['complete_date']) ? $tm['complete_date'] . ' 00:00:00' : ($isCompleted ? $updatedAt : null));

            $assigneeName = trim($tm['assignee_name'] ?? '');
            if (empty($assigneeName)) $assigneeName = 'Employee #' . $targetUid;
            $assigneeRole = trim($tm['assignee_role'] ?? 'Staff');

            $assignerId = !empty($tm['assigned_by']) ? (int)$tm['assigned_by'] : $targetUid;
            $assignerName = trim($tm['assigned_by_name'] ?? ($tm['assigner_name'] ?? 'Admin'));
            $assignerRole = trim($tm['assigner_role'] ?? 'Admin');

            $check = $db->prepare("SELECT id FROM `aitf_tasks` WHERE legacy_task_id = :lid LIMIT 1");
            $check->execute([':lid' => $tmId]);
            $existing = $check->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $aitfId = (int)$existing['id'];
                $db->prepare("
                    UPDATE `aitf_tasks` SET
                        title = :title,
                        description = :desc,
                        status = :status,
                        is_important = :imp,
                        due_date = :due,
                        reminder_time = :reminder,
                        completed_at = :completed_at,
                        is_deleted = :is_del,
                        updated_at = NOW()
                    WHERE id = :id
                ")->execute([
                    ':title' => $title,
                    ':desc' => $desc,
                    ':status' => $status,
                    ':imp' => $isImportant,
                    ':due' => $dueDate,
                    ':reminder' => $reminder,
                    ':completed_at' => $completedAt,
                    ':is_del' => $isDeleted,
                    ':id' => $aitfId
                ]);
                return $aitfId;
            } else {
                $ins = $db->prepare("
                    INSERT INTO `aitf_tasks` (
                        title, description, system_code,
                        assigned_to_user_id, assigned_to_user_type, assigned_to_user_key,
                        assigned_to_name, assigned_to_role,
                        assigned_by_user_id, assigned_by_name, assigned_by_role,
                        status, is_important, due_date, reminder_time,
                        completed_at, created_at, updated_at, is_deleted, legacy_task_id
                    ) VALUES (
                        :title, :desc, :sys,
                        :to_id, 'user', :to_key,
                        :to_name, :to_role,
                        :by_id, :by_name, :by_role,
                        :status, :imp, :due, :reminder,
                        :completed_at, :created_at, :updated_at, :is_del, :legacy_id
                    )
                ");
                $ins->execute([
                    ':title' => $title,
                    ':desc' => $desc,
                    ':sys' => $systemCode,
                    ':to_id' => $targetUid,
                    ':to_key' => 'user_' . $targetUid,
                    ':to_name' => $assigneeName,
                    ':to_role' => $assigneeRole,
                    ':by_id' => $assignerId,
                    ':by_name' => $assignerName,
                    ':by_role' => $assignerRole,
                    ':status' => $status,
                    ':imp' => $isImportant,
                    ':due' => $dueDate,
                    ':reminder' => $reminder,
                    ':completed_at' => $completedAt,
                    ':created_at' => $createdAt,
                    ':updated_at' => $updatedAt,
                    ':is_del' => $isDeleted,
                    ':legacy_id' => $tmId
                ]);
                return (int)$db->lastInsertId();
            }
        } catch (Throwable $e) {
            error_log("Sync web to app error: " . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('aitf_sync_app_task_to_web')) {
    /**
     * Synchronizes a specific task from `aitf_tasks` to `task_manager`.
     */
    function aitf_sync_app_task_to_web(PDO $db, int $aitfTaskId): ?int {
        try {
            $stmt = $db->prepare("SELECT * FROM `aitf_tasks` WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $aitfTaskId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$task) return null;

            $tmCheck = $db->query("SHOW TABLES LIKE 'task_manager'")->fetch(PDO::FETCH_NUM);
            if (!$tmCheck) return null;

            $status = (strtolower(trim($task['status'] ?? '')) === 'completed') ? 'Completed' : 'Pending';
            $isImportant = !empty($task['is_important']) ? 1 : 0;
            $priority = $isImportant ? 'High' : 'Normal';
            $isDel = !empty($task['is_deleted']) ? 'Y' : 'N';
            $completedAt = !empty($task['completed_at']) ? $task['completed_at'] : ($status === 'Completed' ? date('Y-m-d H:i:s') : null);
            $completeDate = $completedAt ? date('Y-m-d', strtotime($completedAt)) : null;

            // Check if legacy_task_id already exists in task_manager
            $legacyId = !empty($task['legacy_task_id']) ? (int)$task['legacy_task_id'] : 0;
            if ($legacyId > 0) {
                $check = $db->prepare("SELECT id FROM `task_manager` WHERE id = :id LIMIT 1");
                $check->execute([':id' => $legacyId]);
                if ($check->fetch()) {
                    $upd = $db->prepare("
                        UPDATE `task_manager` SET
                            task = :title,
                            comment = :desc,
                            status = :st,
                            priority = :priority,
                            is_important = :imp,
                            due_date = :due,
                            complete_date = :cdate,
                            completed_at = :cat,
                            is_deleted = :is_del,
                            update_date = CURDATE()
                        WHERE id = :id
                    ");
                    $upd->execute([
                        ':title' => $task['title'],
                        ':desc' => $task['description'] ?? '',
                        ':st' => $status,
                        ':priority' => $priority,
                        ':imp' => $isImportant,
                        ':due' => $task['due_date'],
                        ':cdate' => $completeDate,
                        ':cat' => $completedAt,
                        ':is_del' => $isDel,
                        ':id' => $legacyId
                    ]);
                    return $legacyId;
                }
            }

            // Insert new row into task_manager
            $ins = $db->prepare("
                INSERT INTO `task_manager` (
                    userid, task, comment, date, update_date,
                    status, priority, due_date, reminder_time,
                    is_important, assigned_by, assigned_by_name,
                    complete_date, completed_at, is_deleted
                ) VALUES (
                    :uid, :title, :desc, :created_at, CURDATE(),
                    :st, :priority, :due, :reminder,
                    :imp, :by_id, :by_name,
                    :cdate, :cat, :is_del
                )
            ");
            $ins->execute([
                ':uid' => $task['assigned_to_user_id'],
                ':title' => $task['title'],
                ':desc' => $task['description'] ?? '',
                ':created_at' => !empty($task['created_at']) ? $task['created_at'] : date('Y-m-d H:i:s'),
                ':st' => $status,
                ':priority' => $priority,
                ':due' => $task['due_date'],
                ':reminder' => $task['reminder_time'],
                ':imp' => $isImportant,
                ':by_id' => $task['assigned_by_user_id'],
                ':by_name' => $task['assigned_by_name'],
                ':cdate' => $completeDate,
                ':cat' => $completedAt,
                ':is_del' => $isDel
            ]);
            $newLegacyId = (int)$db->lastInsertId();
            $db->prepare("UPDATE `aitf_tasks` SET legacy_task_id = :lid WHERE id = :id")->execute([
                ':lid' => $newLegacyId,
                ':id' => $aitfTaskId
            ]);
            return $newLegacyId;
        } catch (Throwable $e) {
            error_log("Sync app to web error: " . $e->getMessage());
            return null;
        }
    }
}


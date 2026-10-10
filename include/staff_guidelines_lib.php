<?php
require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/rbac_helper.php';
require_once __DIR__ . '/../utility.php';

if (!function_exists('aitf_ensure_guidelines_schema')) {
    function aitf_ensure_guidelines_schema(PDO $db)
    {
        $sql1 = "CREATE TABLE IF NOT EXISTS staff_guidelines (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            target_role VARCHAR(255) NOT NULL DEFAULT 'Users',
            created_by VARCHAR(100) NULL,
            created_role VARCHAR(100) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            INDEX idx_target_role (target_role),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $sql2 = "CREATE TABLE IF NOT EXISTS staff_guideline_attachments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            guideline_id INT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_type VARCHAR(100) NULL,
            file_size BIGINT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            INDEX idx_guideline_id (guideline_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $sql3 = "CREATE TABLE IF NOT EXISTS staff_guideline_recipients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            guideline_id INT NOT NULL,
            user_id INT NOT NULL,
            user_type VARCHAR(50) NOT NULL DEFAULT 'user',
            user_name VARCHAR(255) NULL,
            user_email VARCHAR(255) NULL,
            user_role VARCHAR(100) NULL,
            assigned_by VARCHAR(100) NULL,
            assigned_at DATETIME NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            read_at DATETIME NULL,
            INDEX idx_guideline (guideline_id),
            INDEX idx_user (user_id, user_type),
            INDEX idx_user_email (user_email),
            UNIQUE KEY uq_guideline_recipient (guideline_id, user_id, user_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $db->exec($sql1);
            $db->exec($sql2);
            $db->exec($sql3);
        } catch (Exception $e) {
            error_log("Failed to ensure staff guidelines schema: " . $e->getMessage());
        }

        // Ensure upload directory exists
        $uploadDir = __DIR__ . '/../uploads/guidelines';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
    }
}

if (!function_exists('aitf_get_all_system_users')) {
    function aitf_get_all_system_users(PDO $db)
    {
        $users = [];

        // 1. Fetch from 'user' table
        try {
            $stmt = $db->query("SELECT user_id, user_name, abbreviation, email_id, role, 'user' as user_type 
                                FROM user 
                                WHERE (is_deleted = '0' OR is_deleted = 0 OR is_deleted IS NULL) 
                                ORDER BY user_name ASC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $uid = (int)$row['user_id'];
                $key = 'user_' . $uid;
                $users[$key] = [
                    'key' => $key,
                    'user_id' => $uid,
                    'user_type' => 'user',
                    'name' => trim((string)$row['user_name']),
                    'email' => trim((string)$row['email_id']),
                    'role' => !empty($row['role']) ? trim((string)$row['role']) : 'Staff',
                    'abbreviation' => trim((string)($row['abbreviation'] ?? ''))
                ];
            }
        } catch (Exception $e) {
            error_log("Error fetching users for guidelines: " . $e->getMessage());
        }

        // 2. Also fetch auditors from 'auditors' table if not already represented
        try {
            $audStmt = $db->query("SELECT auditors_id, auditor_name, abbrevation, auditor_email, login_email FROM auditors ORDER BY auditor_name ASC");
            while ($row = $audStmt->fetch(PDO::FETCH_ASSOC)) {
                $aid = (int)$row['auditors_id'];
                $email = !empty($row['login_email']) ? trim($row['login_email']) : trim((string)$row['auditor_email']);
                $name = trim((string)$row['auditor_name']);
                
                // Check if already in $users by email or name
                $alreadyAdded = false;
                foreach ($users as $u) {
                    if ((!empty($email) && strtolower($u['email']) === strtolower($email)) || 
                        (strtolower($u['name']) === strtolower($name))) {
                        $alreadyAdded = true;
                        break;
                    }
                }
                if (!$alreadyAdded && !empty($name)) {
                    $key = 'auditor_' . $aid;
                    $users[$key] = [
                        'key' => $key,
                        'user_id' => $aid,
                        'user_type' => 'auditor',
                        'name' => $name,
                        'email' => $email,
                        'role' => 'Auditor',
                        'abbreviation' => trim((string)($row['abbrevation'] ?? ''))
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("Error fetching auditors for guidelines: " . $e->getMessage());
        }

        return array_values($users);
    }
}

if (!function_exists('aitf_create_guideline')) {
    function aitf_create_guideline(PDO $db, $title, $description, $createdBy = '', $createdRole = '', $files = [])
    {
        aitf_ensure_guidelines_schema($db);

        $title = trim((string)$title);
        $description = trim((string)$description);

        if ($title === '') {
            return ['success' => false, 'message' => 'Guideline title is required.'];
        }
        $now = date('Y-m-d H:i:s');
        $stmt = $db->prepare("INSERT INTO staff_guidelines (title, description, target_role, created_by, created_role, created_at, updated_at, is_active)
                              VALUES (:title, :desc, 'Users', :created_by, :created_role, :created_at, :updated_at, 1)");
        $stmt->execute([
            ':title' => $title,
            ':desc' => $description,
            ':created_by' => $createdBy,
            ':created_role' => $createdRole,
            ':created_at' => $now,
            ':updated_at' => $now
        ]);

        $guidelineId = (int)$db->lastInsertId();
        if (!$guidelineId) {
            return ['success' => false, 'message' => 'Failed to save guideline.'];
        }

        // Process attachments
        $uploadedCount = 0;
        $uploadDir = __DIR__ . '/../uploads/guidelines/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'zip', 'pptx', 'ppt'];

        if (!empty($files) && isset($files['name'])) {
            $fileNames = is_array($files['name']) ? $files['name'] : [$files['name']];
            $fileTmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
            $fileErrors = is_array($files['error']) ? $files['error'] : [$files['error']];
            $fileSizes = is_array($files['size']) ? $files['size'] : [$files['size']];
            $fileTypes = is_array($files['type']) ? $files['type'] : [$files['type']];

            for ($i = 0; $i < count($fileNames); $i++) {
                if (($fileErrors[$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && !empty($fileTmpNames[$i])) {
                    $origName = basename($fileNames[$i]);
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                    if (in_array($ext, $allowedExts, true)) {
                        $sanitizedBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
                        if (strlen($sanitizedBase) > 40) {
                            $sanitizedBase = substr($sanitizedBase, 0, 40);
                        }
                        $savedFileName = 'guideline_' . $guidelineId . '_' . time() . '_' . random_int(1000, 9999) . '_' . $sanitizedBase . '.' . $ext;
                        $destination = $uploadDir . $savedFileName;

                        if (@move_uploaded_file($fileTmpNames[$i], $destination)) {
                            $relPath = 'uploads/guidelines/' . $savedFileName;
                            $fSize = (int)($fileSizes[$i] ?? filesize($destination));
                            $fType = !empty($fileTypes[$i]) ? $fileTypes[$i] : mime_content_type($destination);

                            $attStmt = $db->prepare("INSERT INTO staff_guideline_attachments (guideline_id, file_name, original_name, file_path, file_type, file_size, created_at)
                                                     VALUES (:gid, :fname, :oname, :fpath, :ftype, :fsize, :created_at)");
                            $attStmt->execute([
                                ':gid' => $guidelineId,
                                ':fname' => $savedFileName,
                                ':oname' => $origName,
                                ':fpath' => $relPath,
                                ':ftype' => $fType,
                                ':fsize' => $fSize,
                                ':created_at' => $now
                            ]);
                            $uploadedCount++;
                        }
                    }
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Guideline created successfully! Now assign it to users from Add User page.',
            'guideline_id' => $guidelineId,
            'attachments_count' => $uploadedCount
        ];
    }
}

if (!function_exists('aitf_delete_guideline')) {
    function aitf_delete_guideline(PDO $db, $guidelineId)
    {
        $guidelineId = (int)$guidelineId;
        if ($guidelineId <= 0) {
            return false;
        }

        try {
            // Delete physical files
            $stmt = $db->prepare("SELECT file_path FROM staff_guideline_attachments WHERE guideline_id = :gid");
            $stmt->execute([':gid' => $guidelineId]);
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($files as $f) {
                $fullPath = __DIR__ . '/../' . ltrim($f['file_path'], '/\\');
                if (file_exists($fullPath) && is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }

            // Delete attachments records
            $stmtAtt = $db->prepare("DELETE FROM staff_guideline_attachments WHERE guideline_id = :gid");
            $stmtAtt->execute([':gid' => $guidelineId]);

            // Delete recipients records
            $stmtRec = $db->prepare("DELETE FROM staff_guideline_recipients WHERE guideline_id = :gid");
            $stmtRec->execute([':gid' => $guidelineId]);

            // Delete guideline
            $stmtG = $db->prepare("DELETE FROM staff_guidelines WHERE id = :id");
            $stmtG->execute([':id' => $guidelineId]);

            return true;
        } catch (Exception $e) {
            error_log("Error deleting guideline {$guidelineId}: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('aitf_assign_guideline_to_users')) {
    function aitf_assign_guideline_to_users(PDO $db, $guidelineId, array $recipientKeys, $assignedBy = '')
    {
        aitf_ensure_guidelines_schema($db);
        $guidelineId = (int)$guidelineId;
        if ($guidelineId <= 0) {
            return ['success' => false, 'message' => 'Invalid guideline ID.'];
        }

        // Verify guideline exists
        $checkStmt = $db->prepare("SELECT id, title FROM staff_guidelines WHERE id = :id LIMIT 1");
        $checkStmt->execute([':id' => $guidelineId]);
        $guideline = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$guideline) {
            return ['success' => false, 'message' => 'Guideline not found.'];
        }

        // Fetch all system users to match metadata
        $allUsers = aitf_get_all_system_users($db);
        $usersByKey = [];
        foreach ($allUsers as $u) {
            $usersByKey[$u['key']] = $u;
            // Also allow matching by numeric ID if plain user_id provided
            $usersByKey['user_' . $u['user_id']] = $u;
            $usersByKey[(string)$u['user_id']] = $u;
        }

        $now = date('Y-m-d H:i:s');
        if (empty($assignedBy)) {
            $assignedBy = $_SESSION['username'] ?? 'Director';
        }

        try {
            $db->beginTransaction();

            // Clear previous recipients for this guideline
            $delStmt = $db->prepare("DELETE FROM staff_guideline_recipients WHERE guideline_id = :gid");
            $delStmt->execute([':gid' => $guidelineId]);

            $insertedCount = 0;
            $insStmt = $db->prepare("INSERT INTO staff_guideline_recipients (guideline_id, user_id, user_type, user_name, user_email, user_role, assigned_by, assigned_at)
                                     VALUES (:gid, :uid, :utype, :uname, :uemail, :urole, :assigned_by, :assigned_at)");

            $assignedNames = [];
            foreach ($recipientKeys as $rawKey) {
                $rawKey = trim((string)$rawKey);
                if ($rawKey === '') continue;

                $userType = 'user';
                $userId = 0;

                if (strpos($rawKey, 'auditor_') === 0 || strpos($rawKey, 'auditor:') === 0) {
                    $userType = 'auditor';
                    $userId = (int)str_replace(['auditor_', 'auditor:'], '', $rawKey);
                } elseif (strpos($rawKey, 'user_') === 0 || strpos($rawKey, 'user:') === 0) {
                    $userType = 'user';
                    $userId = (int)str_replace(['user_', 'user:'], '', $rawKey);
                } else {
                    $userId = (int)$rawKey;
                }

                if ($userId <= 0) continue;

                $matched = $usersByKey[$userType . '_' . $userId] ?? ($usersByKey[(string)$userId] ?? null);
                $userName = $matched ? $matched['name'] : 'User #' . $userId;
                $userEmail = $matched ? $matched['email'] : '';
                $userRole = $matched ? $matched['role'] : '';

                $insStmt->execute([
                    ':gid' => $guidelineId,
                    ':uid' => $userId,
                    ':utype' => $userType,
                    ':uname' => $userName,
                    ':uemail' => $userEmail,
                    ':urole' => $userRole,
                    ':assigned_by' => $assignedBy,
                    ':assigned_at' => $now
                ]);

                $assignedNames[] = $userName;
                $insertedCount++;
            }

            $db->commit();

            return [
                'success' => true,
                'message' => $insertedCount > 0 
                    ? "Guideline '{$guideline['title']}' successfully sent to {$insertedCount} user(s)." 
                    : "Guideline recipients updated (no users selected).",
                'guideline_id' => $guidelineId,
                'recipients_count' => $insertedCount,
                'assigned_names' => $assignedNames
            ];

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error assigning guideline recipients: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to assign guideline to users: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('aitf_get_guideline_recipients')) {
    function aitf_get_guideline_recipients(PDO $db, $guidelineId)
    {
        aitf_ensure_guidelines_schema($db);
        $stmt = $db->prepare("SELECT * FROM staff_guideline_recipients WHERE guideline_id = :gid ORDER BY user_name ASC");
        $stmt->execute([':gid' => (int)$guidelineId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('aitf_get_all_guidelines_with_details')) {
    function aitf_get_all_guidelines_with_details(PDO $db, $search = '')
    {
        aitf_ensure_guidelines_schema($db);

        $where = ["g.is_active = 1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(g.title LIKE :search OR g.description LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT g.* FROM staff_guidelines g WHERE {$whereSql} ORDER BY g.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $guidelines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($guidelines)) {
            return [];
        }

        $guidelineIds = array_column($guidelines, 'id');
        $placeholders = implode(',', array_fill(0, count($guidelineIds), '?'));

        // Fetch attachments
        $attStmt = $db->prepare("SELECT * FROM staff_guideline_attachments WHERE guideline_id IN ({$placeholders}) ORDER BY id ASC");
        $attStmt->execute($guidelineIds);
        $allAttachments = $attStmt->fetchAll(PDO::FETCH_ASSOC);

        $attachmentsByGid = [];
        foreach ($allAttachments as $att) {
            $gid = (int)$att['guideline_id'];
            $attachmentsByGid[$gid][] = $att;
        }

        // Fetch recipients
        $recStmt = $db->prepare("SELECT * FROM staff_guideline_recipients WHERE guideline_id IN ({$placeholders}) ORDER BY user_name ASC");
        $recStmt->execute($guidelineIds);
        $allRecipients = $recStmt->fetchAll(PDO::FETCH_ASSOC);

        $recipientsByGid = [];
        foreach ($allRecipients as $rec) {
            $gid = (int)$rec['guideline_id'];
            $recipientsByGid[$gid][] = $rec;
        }

        foreach ($guidelines as &$g) {
            $gid = (int)$g['id'];
            $g['attachments'] = $attachmentsByGid[$gid] ?? [];
            $g['recipients'] = $recipientsByGid[$gid] ?? [];
            $g['recipient_keys'] = array_map(function($r) {
                return ($r['user_type'] ?? 'user') . '_' . $r['user_id'];
            }, $g['recipients']);
        }
        unset($g);

        return $guidelines;
    }
}

if (!function_exists('aitf_get_guidelines_for_current_user')) {
    function aitf_get_guidelines_for_current_user(PDO $db, $userId = null, $userRole = null, $userEmail = null, $userName = null, $search = '')
    {
        aitf_ensure_guidelines_schema($db);

        if ($userId === null) {
            $userId = (int)($_SESSION['id'] ?? 0);
        }
        if ($userRole === null) {
            $userRole = (string)($_SESSION['role'] ?? '');
        }
        if ($userEmail === null) {
            $userEmail = (string)($_SESSION['email'] ?? ($_SESSION['email_id'] ?? ''));
        }
        if ($userName === null) {
            $userName = (string)($_SESSION['user_full_name'] ?? ($_SESSION['username'] ?? ''));
        }

        $isTopMgmt = aitf_is_top_management_role($userRole) || Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin();

        $params = [];
        $where = ["g.is_active = 1"];

        if ($isTopMgmt) {
            // Management can see all posted guidelines
            if (!empty($search)) {
                $where[] = "(g.title LIKE :search OR g.description LIKE :search)";
                $params[':search'] = '%' . trim($search) . '%';
            }
            $whereSql = implode(' AND ', $where);
            $sql = "SELECT DISTINCT g.* FROM staff_guidelines g WHERE {$whereSql} ORDER BY g.id DESC";
        } else {
            // Regular user / Auditor: Guidelines sent to this user or role
            $where[] = "(
                (:uid > 0 AND r.user_id = :uid)
                OR (:uemail != '' AND LOWER(r.user_email) = LOWER(:uemail))
                OR (:uname != '' AND (LOWER(r.user_name) = LOWER(:uname) OR LOWER(r.user_email) = LOWER(:uname)))
                OR (g.target_role = 'All' OR (g.target_role != '' AND LOWER(g.target_role) = LOWER(:urole)))
            )";
            $params[':uid'] = (int)$userId;
            $params[':uemail'] = trim($userEmail);
            $params[':uname'] = trim($userName);
            $params[':urole'] = trim($userRole);

            if (!empty($search)) {
                $where[] = "(g.title LIKE :search OR g.description LIKE :search)";
                $params[':search'] = '%' . trim($search) . '%';
            }

            $whereSql = implode(' AND ', $where);
            $sql = "SELECT DISTINCT g.*, r.assigned_at, r.is_read 
                    FROM staff_guidelines g 
                    LEFT JOIN staff_guideline_recipients r ON g.id = r.guideline_id 
                    WHERE {$whereSql} 
                    ORDER BY g.id DESC";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $guidelines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($guidelines)) {
            return [];
        }

        // Fetch attachments
        $guidelineIds = array_column($guidelines, 'id');
        $placeholders = implode(',', array_fill(0, count($guidelineIds), '?'));
        $attStmt = $db->prepare("SELECT * FROM staff_guideline_attachments WHERE guideline_id IN ({$placeholders}) ORDER BY id ASC");
        $attStmt->execute($guidelineIds);
        $allAttachments = $attStmt->fetchAll(PDO::FETCH_ASSOC);

        $attachmentsByGid = [];
        foreach ($allAttachments as $att) {
            $gid = (int)$att['guideline_id'];
            $attachmentsByGid[$gid][] = $att;
        }

        // Fetch recipients (for display)
        $recStmt = $db->prepare("SELECT * FROM staff_guideline_recipients WHERE guideline_id IN ({$placeholders}) ORDER BY user_name ASC");
        $recStmt->execute($guidelineIds);
        $allRecipients = $recStmt->fetchAll(PDO::FETCH_ASSOC);

        $recipientsByGid = [];
        foreach ($allRecipients as $rec) {
            $gid = (int)$rec['guideline_id'];
            $recipientsByGid[$gid][] = $rec;
        }

        foreach ($guidelines as &$g) {
            $gid = (int)$g['id'];
            $g['attachments'] = $attachmentsByGid[$gid] ?? [];
            $g['recipients'] = $recipientsByGid[$gid] ?? [];
        }
        unset($g);

        return $guidelines;
    }
}

// Backward compatibility alias for aitf_get_guidelines_for_user
if (!function_exists('aitf_get_guidelines_for_user')) {
    function aitf_get_guidelines_for_user(PDO $db, $userRole, $search = '', $filterRole = '')
    {
        return aitf_get_guidelines_for_current_user($db, null, $userRole, null, $search);
    }
}

if (!function_exists('aitf_user_can_view_guideline_doc')) {
    function aitf_user_can_view_guideline_doc(PDO $db, $guidelineId, $userId = null, $userRole = null, $userEmail = null)
    {
        if ($userId === null) {
            $userId = (int)($_SESSION['id'] ?? 0);
        }
        if ($userRole === null) {
            $userRole = (string)($_SESSION['role'] ?? '');
        }
        if ($userEmail === null) {
            $userEmail = (string)($_SESSION['email'] ?? ($_SESSION['email_id'] ?? ''));
        }
        $userName = (string)($_SESSION['username'] ?? '');

        if (aitf_is_top_management_role($userRole) || Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin()) {
            return true;
        }

        aitf_ensure_guidelines_schema($db);
        $stmt = $db->prepare("SELECT COUNT(*) FROM staff_guideline_recipients 
                              WHERE guideline_id = :gid 
                                AND (
                                    user_id = :uid 
                                    OR (:uemail != '' AND user_email = :uemail)
                                    OR (:uname != '' AND user_name = :uname)
                                )");
        $stmt->execute([
            ':gid' => (int)$guidelineId,
            ':uid' => (int)$userId,
            ':uemail' => trim($userEmail),
            ':uname' => trim($userName)
        ]);
        return ((int)$stmt->fetchColumn() > 0);
    }
}

if (!function_exists('aitf_user_can_view_guideline')) {
    function aitf_user_can_view_guideline($userRole, array $guideline)
    {
        global $db;
        if (aitf_is_top_management_role($userRole) || Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin()) {
            return true;
        }

        if (isset($guideline['id']) && isset($db) && $db instanceof PDO) {
            return aitf_user_can_view_guideline_doc($db, $guideline['id']);
        }

        return true;
    }
}

if (!function_exists('aitf_get_guideline_by_id')) {
    function aitf_get_guideline_by_id(PDO $db, $guidelineId)
    {
        aitf_ensure_guidelines_schema($db);
        $stmt = $db->prepare("SELECT * FROM staff_guidelines WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$guidelineId]);
        $guideline = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$guideline) {
            return null;
        }

        $attStmt = $db->prepare("SELECT * FROM staff_guideline_attachments WHERE guideline_id = :gid ORDER BY id ASC");
        $attStmt->execute([':gid' => (int)$guideline['id']]);
        $guideline['attachments'] = $attStmt->fetchAll(PDO::FETCH_ASSOC);

        $recStmt = $db->prepare("SELECT * FROM staff_guideline_recipients WHERE guideline_id = :gid ORDER BY user_name ASC");
        $recStmt->execute([':gid' => (int)$guideline['id']]);
        $guideline['recipients'] = $recStmt->fetchAll(PDO::FETCH_ASSOC);
        $guideline['recipient_keys'] = array_map(function($r) {
            return ($r['user_type'] ?? 'user') . '_' . $r['user_id'];
        }, $guideline['recipients']);

        return $guideline;
    }
}

if (!function_exists('aitf_format_file_size')) {
    function aitf_format_file_size($bytes)
    {
        $bytes = (float)$bytes;
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        return round($bytes / pow(1024, $power), 1) . ' ' . $units[$power];
    }
}

if (!function_exists('aitf_get_file_icon_info')) {
    function aitf_get_file_icon_info($filename)
    {
        $ext = strtolower(pathinfo((string)$filename, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'pdf':
                return ['icon' => 'fa fa-file-pdf-o', 'color' => '#ef4444', 'label' => 'PDF Document'];
            case 'doc':
            case 'docx':
                return ['icon' => 'fa fa-file-word-o', 'color' => '#2563eb', 'label' => 'Word Document'];
            case 'xls':
            case 'xlsx':
            case 'csv':
                return ['icon' => 'fa fa-file-excel-o', 'color' => '#10b981', 'label' => 'Spreadsheet'];
            case 'ppt':
            case 'pptx':
                return ['icon' => 'fa fa-file-powerpoint-o', 'color' => '#f97316', 'label' => 'Presentation'];
            case 'png':
            case 'jpg':
            case 'jpeg':
            case 'webp':
            case 'gif':
                return ['icon' => 'fa fa-file-image-o', 'color' => '#8b5cf6', 'label' => 'Image'];
            case 'txt':
                return ['icon' => 'fa fa-file-text-o', 'color' => '#64748b', 'label' => 'Text File'];
            case 'zip':
            case 'rar':
                return ['icon' => 'fa fa-file-archive-o', 'color' => '#eab308', 'label' => 'Archive'];
            default:
                return ['icon' => 'fa fa-file-o', 'color' => '#6b7280', 'label' => 'Document'];
        }
    }
}

if (!function_exists('aitf_get_role_badge_style')) {
    function aitf_get_role_badge_style($role)
    {
        $role = trim((string)$role);
        switch (strtolower($role)) {
            case 'auditor':
            case 'auditors':
                return ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe', 'icon' => 'fa fa-user-secret'];
            case 'marketing head':
            case 'marketing executive':
            case 'marketing':
                return ['bg' => '#fdf4ff', 'text' => '#a21caf', 'border' => '#f5d0fe', 'icon' => 'fa fa-briefcase'];
            case 'admin':
                return ['bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0', 'icon' => 'fa fa-shield'];
            case 'top mgmt':
            case 'director':
            case 'main admin':
                return ['bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a', 'icon' => 'fa fa-star'];
            case 'finance':
                return ['bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0', 'icon' => 'fa fa-money'];
            case 'office staff':
            case 'office staff-a':
            case 'office staff-b':
                return ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#cbd5e1', 'icon' => 'fa fa-users'];
            case 'application reviewer':
            case 'report reviewer':
                return ['bg' => '#f0fdfa', 'text' => '#0f766e', 'border' => '#99f6e4', 'icon' => 'fa fa-check-square-o'];
            default:
                return ['bg' => '#f1f5f9', 'text' => '#334155', 'border' => '#cbd5e1', 'icon' => 'fa fa-user'];
        }
    }
}

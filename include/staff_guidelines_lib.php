<?php
require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/rbac_helper.php';

if (!function_exists('aitf_ensure_guidelines_schema')) {
    function aitf_ensure_guidelines_schema(PDO $db)
    {
        $sql1 = "CREATE TABLE IF NOT EXISTS staff_guidelines (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            target_role VARCHAR(255) NOT NULL DEFAULT 'All',
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

        try {
            $db->exec($sql1);
            $db->exec($sql2);
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

if (!function_exists('aitf_get_guideline_roles_list')) {
    function aitf_get_guideline_roles_list(PDO $db)
    {
        $roles = [
            'All' => 'All Staff / All Roles',
            'Top Mgmt' => 'Top Mgmt',
            'Admin' => 'Admin',
            'Finance' => 'Finance',
            'Auditor' => 'Auditor',
            'Application Reviewer' => 'Application Reviewer',
            'Report Reviewer' => 'Report Reviewer',
            'Office Staff-A' => 'Office Staff-A',
            'Office Staff-B' => 'Office Staff-B',
            'Office Staff' => 'Office Staff',
            'Marketing Head' => 'Marketing Head',
            'Marketing Executive' => 'Marketing Executive',
            'Marketing' => 'Marketing'
        ];

        try {
            $stmt = $db->query("SELECT DISTINCT role FROM user_role WHERE role IS NOT NULL AND role != '' ORDER BY role ASC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $r = trim($row['role']);
                if ($r !== '' && !isset($roles[$r])) {
                    $roles[$r] = $r;
                }
            }
        } catch (Exception $e) {}

        return $roles;
    }
}

if (!function_exists('aitf_create_guideline')) {
    function aitf_create_guideline(PDO $db, $title, $description, $targetRole, $createdBy = '', $createdRole = '', $files = [])
    {
        aitf_ensure_guidelines_schema($db);

        $title = trim((string)$title);
        $description = trim((string)$description);
        $targetRole = trim((string)$targetRole);
        if ($targetRole === '') {
            $targetRole = 'All';
        }

        if ($title === '') {
            return ['success' => false, 'message' => 'Guideline title is required.'];
        }
        if ($description === '') {
            return ['success' => false, 'message' => 'Guideline description is required.'];
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $db->prepare("INSERT INTO staff_guidelines (title, description, target_role, created_by, created_role, created_at, updated_at, is_active)
                              VALUES (:title, :desc, :target_role, :created_by, :created_role, :created_at, :updated_at, 1)");
        $stmt->execute([
            ':title' => $title,
            ':desc' => $description,
            ':target_role' => $targetRole,
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
            'message' => 'Guideline successfully posted!',
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

if (!function_exists('aitf_user_can_view_guideline')) {
    function aitf_user_can_view_guideline($userRole, array $guideline)
    {
        // Top Mgmt can view all guidelines
        if (aitf_is_top_management_role($userRole)) {
            return true;
        }

        $target = trim((string)($guideline['target_role'] ?? 'All'));
        if (strcasecmp($target, 'All') === 0 || $target === '' || strcasecmp($target, 'All Staff') === 0 || strcasecmp($target, 'All Roles') === 0) {
            return true;
        }

        $userRoleNorm = strtolower(trim((string)$userRole));
        $targetNorm = strtolower($target);

        // Exact or case-insensitive match
        if ($userRoleNorm === $targetNorm) {
            return true;
        }

        // Auditor alias check (Auditor / Auditors)
        if (($userRoleNorm === 'auditor' || $userRoleNorm === 'auditors') && ($targetNorm === 'auditor' || $targetNorm === 'auditors')) {
            return true;
        }

        // Marketing alias check
        if (strpos($userRoleNorm, 'marketing') !== false && $targetNorm === 'marketing') {
            return true;
        }

        // Office Staff alias check
        if (strpos($userRoleNorm, 'office staff') !== false && $targetNorm === 'office staff') {
            return true;
        }

        // Comma-separated target roles support
        if (strpos($targetNorm, ',') !== false) {
            $rolesList = array_map('trim', explode(',', $targetNorm));
            if (in_array($userRoleNorm, $rolesList, true)) {
                return true;
            }
            if (($userRoleNorm === 'auditor' || $userRoleNorm === 'auditors') && (in_array('auditor', $rolesList, true) || in_array('auditors', $rolesList, true))) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('aitf_get_guidelines_for_user')) {
    function aitf_get_guidelines_for_user(PDO $db, $userRole, $search = '', $filterRole = '')
    {
        aitf_ensure_guidelines_schema($db);

        $isTopMgmt = aitf_is_top_management_role($userRole);
        $params = [];
        $where = ["is_active = 1"];

        if ($isTopMgmt) {
            // Mgmt sees everything, but can filter by target_role if requested
            if (!empty($filterRole) && $filterRole !== 'all') {
                $where[] = "target_role = :filterRole";
                $params[':filterRole'] = $filterRole;
            }
        } else {
            // Non-mgmt users only see guidelines for 'All' or their specific role
            $normRole = trim((string)$userRole);
            if (strcasecmp($normRole, 'auditors') === 0) {
                $normRole = 'Auditor';
            }

            $where[] = "(target_role = 'All' OR target_role = 'All Staff' OR target_role = 'All Roles' OR target_role = :role OR target_role LIKE :roleLike OR target_role = 'Auditors')";
            $params[':role'] = $normRole;
            $params[':roleLike'] = '%' . $normRole . '%';
        }

        if (!empty($search)) {
            $where[] = "(title LIKE :search OR description LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT * FROM staff_guidelines WHERE {$whereSql} ORDER BY id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $guidelines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($guidelines)) {
            return [];
        }

        // Fetch attachments for all retrieved guidelines
        $guidelineIds = array_column($guidelines, 'id');
        $placeholders = implode(',', array_fill(0, count($guidelineIds), '?'));
        $attStmt = $db->prepare("SELECT * FROM staff_guideline_attachments WHERE guideline_id IN ({$placeholders}) ORDER BY id ASC");
        $attStmt->execute($guidelineIds);
        $allAttachments = $attStmt->fetchAll(PDO::FETCH_ASSOC);

        $attachmentsByGid = [];
        foreach ($allAttachments as $att) {
            $gid = (int)$att['guideline_id'];
            if (!isset($attachmentsByGid[$gid])) {
                $attachmentsByGid[$gid] = [];
            }
            $attachmentsByGid[$gid][] = $att;
        }

        foreach ($guidelines as &$g) {
            $gid = (int)$g['id'];
            $g['attachments'] = $attachmentsByGid[$gid] ?? [];
        }
        unset($g);

        return $guidelines;
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
            case 'all':
            case 'all staff':
            case 'all roles':
            default:
                return ['bg' => '#f0fdf4', 'text' => '#047857', 'border' => '#86efac', 'icon' => 'fa fa-globe'];
        }
    }
}

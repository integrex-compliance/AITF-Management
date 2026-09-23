<?php
/**
 * Helper functions for Auditor NACE Class & Division assignments and client matching.
 */

if (!function_exists('aitf_ensure_auditor_division_schema')) {
    function aitf_ensure_auditor_division_schema(PDO $db)
    {
        static $schemaChecked = false;
        if ($schemaChecked || !$db) {
            return;
        }
        $schemaChecked = true;

        $flagFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'aitf_auditor_division_schema.flag';
        if (file_exists($flagFile) && filemtime($flagFile) >= filemtime(__FILE__) && (time() - filemtime($flagFile) < 43200)) {
            return;
        }

        try {
            // Check if tables already exist first to avoid costly DDL / table metadata locks
            $check1 = $db->query("SHOW TABLES LIKE 'auditor_divisions'");
            $hasDiv = $check1 && $check1->rowCount() > 0;
            $check2 = $db->query("SHOW TABLES LIKE 'auditor_nace_classes'");
            $hasNace = $check2 && $check2->rowCount() > 0;

            if ($hasDiv && $hasNace) {
                // Tables exist, check columns on auditors
                $colCheck = $db->query("SHOW COLUMNS FROM `auditors` LIKE 'assigned_divisions'");
                if ($colCheck && $colCheck->rowCount() > 0) {
                    @touch($flagFile);
                    return; // Everything is already in place, zero DDL needed
                }
            }
        } catch (Exception $e) {
            // If checking fails, proceed to create
        }

        try {
            $tableSql = "CREATE TABLE IF NOT EXISTS `auditor_divisions` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `auditor_id` INT(11) NOT NULL,
                `division_code` VARCHAR(50) NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_aud_div_auditor` (`auditor_id`),
                KEY `idx_aud_div_division` (`division_code`),
                UNIQUE KEY `uniq_aud_div` (`auditor_id`, `division_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->exec($tableSql);
        } catch (Exception $e) {
            error_log("aitf_ensure_auditor_division_schema error creating auditor_divisions table: " . $e->getMessage());
        }

        try {
            $naceTableSql = "CREATE TABLE IF NOT EXISTS `auditor_nace_classes` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `auditor_id` INT(11) NOT NULL,
                `nace_class` VARCHAR(50) NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_aud_nace_auditor` (`auditor_id`),
                KEY `idx_aud_nace_class` (`nace_class`),
                UNIQUE KEY `uniq_aud_nace` (`auditor_id`, `nace_class`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->exec($naceTableSql);
        } catch (Exception $e) {
            error_log("aitf_ensure_auditor_division_schema error creating auditor_nace_classes table: " . $e->getMessage());
        }

        try {
            $colDiv = $db->query("SHOW COLUMNS FROM `auditors` LIKE 'assigned_divisions'");
            if (!$colDiv || $colDiv->rowCount() === 0) {
                $db->exec("ALTER TABLE `auditors` ADD COLUMN `assigned_divisions` TEXT NULL");
            }
        } catch (Exception $e) {
            // Column may already exist
        }

        try {
            $colNace = $db->query("SHOW COLUMNS FROM `auditors` LIKE 'assigned_nace_classes'");
            if (!$colNace || $colNace->rowCount() === 0) {
                $db->exec("ALTER TABLE `auditors` ADD COLUMN `assigned_nace_classes` TEXT NULL");
            }
        } catch (Exception $e) {
            // Column may already exist
        }

        if (isset($flagFile)) {
            @touch($flagFile);
        }
    }
}

if (!function_exists('aitf_get_all_divisions')) {
    function aitf_get_all_divisions(PDO $db)
    {
        try {
            $stmt = $db->prepare("SELECT * FROM `iaf_codes` ORDER BY CAST(`iaf_code` AS UNSIGNED), `iaf_code` ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("aitf_get_all_divisions error: " . $e->getMessage());
            return array();
        }
    }
}

if (!function_exists('aitf_get_all_nace_classes_grouped')) {
    function aitf_get_all_nace_classes_grouped(PDO $db)
    {
        try {
            $sql = "SELECT c.nace2_classes_id, c.nace_class, c.nace_desc, c.ifa_division_id,
                           i.iaf_code, i.iaf_desc
                    FROM `nace2_classes` c
                    LEFT JOIN `iaf_codes` i ON i.iaf_id = c.ifa_division_id
                    ORDER BY CAST(i.iaf_code AS UNSIGNED), i.iaf_code, c.nace_class ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $grouped = array();
            foreach ($rows as $row) {
                $divCode = trim((string)$row['iaf_code']);
                if ($divCode === '') {
                    $divCode = 'Other';
                }
                if (!isset($grouped[$divCode])) {
                    $grouped[$divCode] = array(
                        'division_code' => $divCode,
                        'division_desc' => !empty($row['iaf_desc']) ? trim($row['iaf_desc']) : 'Division ' . $divCode,
                        'classes' => array()
                    );
                }
                $grouped[$divCode]['classes'][] = array(
                    'id' => (int)$row['nace2_classes_id'],
                    'nace_class' => trim((string)$row['nace_class']),
                    'nace_desc' => trim((string)$row['nace_desc']),
                    'division_code' => $divCode
                );
            }
            return $grouped;
        } catch (Exception $e) {
            error_log("aitf_get_all_nace_classes_grouped error: " . $e->getMessage());
            return array();
        }
    }
}

if (!function_exists('aitf_save_auditor_divisions')) {
    function aitf_save_auditor_divisions(PDO $db, $auditorId, array $divisionCodes)
    {
        $auditorId = (int)$auditorId;
        if ($auditorId <= 0) {
            return false;
        }

        aitf_ensure_auditor_division_schema($db);

        $cleaned = array();
        foreach ($divisionCodes as $code) {
            $c = trim((string)$code);
            if ($c !== '' && !in_array($c, $cleaned, true)) {
                $cleaned[] = $c;
            }
        }

        try {
            $delStmt = $db->prepare("DELETE FROM `auditor_divisions` WHERE `auditor_id` = :id");
            $delStmt->execute(array(':id' => $auditorId));

            if (!empty($cleaned)) {
                $insStmt = $db->prepare("INSERT INTO `auditor_divisions` (`auditor_id`, `division_code`) VALUES (:aid, :dcode)");
                foreach ($cleaned as $code) {
                    try {
                        $insStmt->execute(array(':aid' => $auditorId, ':dcode' => $code));
                    } catch (Exception $ex) {
                        // ignore duplicate
                    }
                }
            }

            $divString = implode(', ', $cleaned);
            $updStmt = $db->prepare("UPDATE `auditors` SET `assigned_divisions` = :divs WHERE `auditors_id` = :aid");
            $updStmt->execute(array(':divs' => $divString, ':aid' => $auditorId));

            return true;
        } catch (Exception $e) {
            error_log("aitf_save_auditor_divisions error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('aitf_save_auditor_nace_classes')) {
    function aitf_save_auditor_nace_classes(PDO $db, $auditorId, array $naceClasses)
    {
        $auditorId = (int)$auditorId;
        if ($auditorId <= 0) {
            return false;
        }

        aitf_ensure_auditor_division_schema($db);

        // Sanitize and deduplicate NACE class strings
        $cleaned = array();
        foreach ($naceClasses as $cls) {
            $c = trim((string)$cls);
            if ($c !== '' && !in_array($c, $cleaned, true)) {
                $cleaned[] = $c;
            }
        }

        try {
            // 1. Delete existing records from auditor_nace_classes
            $delStmt = $db->prepare("DELETE FROM `auditor_nace_classes` WHERE `auditor_id` = :id");
            $delStmt->execute(array(':id' => $auditorId));

            // 2. Insert new NACE classes
            if (!empty($cleaned)) {
                $insStmt = $db->prepare("INSERT INTO `auditor_nace_classes` (`auditor_id`, `nace_class`) VALUES (:aid, :ncls)");
                foreach ($cleaned as $ncls) {
                    try {
                        $insStmt->execute(array(':aid' => $auditorId, ':ncls' => $ncls));
                    } catch (Exception $ex) {
                        // ignore duplicate
                    }
                }
            }

            // 3. Update assigned_nace_classes column in auditors table
            $naceString = implode(', ', $cleaned);
            $updStmt = $db->prepare("UPDATE `auditors` SET `assigned_nace_classes` = :ncls WHERE `auditors_id` = :aid");
            $updStmt->execute(array(':ncls' => $naceString, ':aid' => $auditorId));

            // 4. Derive associated IAF Divisions from selected NACE classes and sync auditor_divisions
            $derivedDivisions = array();
            if (!empty($cleaned)) {
                $placeholders = implode(',', array_fill(0, count($cleaned), '?'));
                $divSql = "SELECT DISTINCT i.iaf_code 
                           FROM `nace2_classes` c
                           LEFT JOIN `iaf_codes` i ON i.iaf_id = c.ifa_division_id
                           WHERE c.nace_class IN ($placeholders)";
                $divStmt = $db->prepare($divSql);
                $divStmt->execute($cleaned);
                while ($divRow = $divStmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = trim((string)$divRow['iaf_code']);
                    if ($d !== '' && !in_array($d, $derivedDivisions, true)) {
                        $derivedDivisions[] = $d;
                    }
                }
            }
            aitf_save_auditor_divisions($db, $auditorId, $derivedDivisions);

            return true;
        } catch (Exception $e) {
            error_log("aitf_save_auditor_nace_classes error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('aitf_get_auditor_divisions')) {
    function aitf_get_auditor_divisions(PDO $db, $auditorId)
    {
        $auditorId = (int)$auditorId;
        if ($auditorId <= 0) {
            return array();
        }

        aitf_ensure_auditor_division_schema($db);

        try {
            $stmt = $db->prepare("SELECT `division_code` FROM `auditor_divisions` WHERE `auditor_id` = :id ORDER BY CAST(`division_code` AS UNSIGNED), `division_code` ASC");
            $stmt->execute(array(':id' => $auditorId));
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($rows)) {
                return $rows;
            }

            $stmt2 = $db->prepare("SELECT `assigned_divisions` FROM `auditors` WHERE `auditors_id` = :id LIMIT 1");
            $stmt2->execute(array(':id' => $auditorId));
            $raw = $stmt2->fetchColumn();
            if (!empty($raw)) {
                $divs = array();
                foreach (explode(',', (string)$raw) as $item) {
                    $item = trim($item);
                    if ($item !== '' && !in_array($item, $divs, true)) {
                        $divs[] = $item;
                    }
                }
                return $divs;
            }
            return array();
        } catch (Exception $e) {
            error_log("aitf_get_auditor_divisions error: " . $e->getMessage());
            return array();
        }
    }
}

if (!function_exists('aitf_get_auditor_nace_classes')) {
    function aitf_get_auditor_nace_classes(PDO $db, $auditorId)
    {
        $auditorId = (int)$auditorId;
        if ($auditorId <= 0) {
            return array();
        }

        aitf_ensure_auditor_division_schema($db);

        try {
            $stmt = $db->prepare("SELECT `nace_class` FROM `auditor_nace_classes` WHERE `auditor_id` = :id ORDER BY `nace_class` ASC");
            $stmt->execute(array(':id' => $auditorId));
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($rows)) {
                return $rows;
            }

            $stmt2 = $db->prepare("SELECT `assigned_nace_classes` FROM `auditors` WHERE `auditors_id` = :id LIMIT 1");
            $stmt2->execute(array(':id' => $auditorId));
            $raw = $stmt2->fetchColumn();
            if (!empty($raw)) {
                $classes = array();
                foreach (explode(',', (string)$raw) as $item) {
                    $item = trim($item);
                    if ($item !== '' && !in_array($item, $classes, true)) {
                        $classes[] = $item;
                    }
                }
                return $classes;
            }
            return array();
        } catch (Exception $e) {
            error_log("aitf_get_auditor_nace_classes error: " . $e->getMessage());
            return array();
        }
    }
}

if (!function_exists('aitf_get_auditor_divisions_map')) {
    function aitf_get_auditor_divisions_map(PDO $db)
    {
        aitf_ensure_auditor_division_schema($db);
        $map = array();

        try {
            $stmt = $db->prepare("SELECT `auditor_id`, `division_code` FROM `auditor_divisions` ORDER BY CAST(`division_code` AS UNSIGNED), `division_code` ASC");
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $aid = (int)$row['auditor_id'];
                if (!isset($map[$aid])) {
                    $map[$aid] = array();
                }
                if (!in_array($row['division_code'], $map[$aid], true)) {
                    $map[$aid][] = $row['division_code'];
                }
            }

            $stmt2 = $db->prepare("SELECT `auditors_id`, `assigned_divisions` FROM `auditors` WHERE `assigned_divisions` IS NOT NULL AND `assigned_divisions` != ''");
            $stmt2->execute();
            while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $aid = (int)$row['auditors_id'];
                if (empty($map[$aid])) {
                    $map[$aid] = array();
                    foreach (explode(',', (string)$row['assigned_divisions']) as $d) {
                        $d = trim($d);
                        if ($d !== '' && !in_array($d, $map[$aid], true)) {
                            $map[$aid][] = $d;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("aitf_get_auditor_divisions_map error: " . $e->getMessage());
        }

        return $map;
    }
}

if (!function_exists('aitf_get_auditor_nace_classes_map')) {
    function aitf_get_auditor_nace_classes_map(PDO $db)
    {
        aitf_ensure_auditor_division_schema($db);
        $map = array();

        try {
            $stmt = $db->prepare("SELECT `auditor_id`, `nace_class` FROM `auditor_nace_classes` ORDER BY `nace_class` ASC");
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $aid = (int)$row['auditor_id'];
                if (!isset($map[$aid])) {
                    $map[$aid] = array();
                }
                if (!in_array($row['nace_class'], $map[$aid], true)) {
                    $map[$aid][] = $row['nace_class'];
                }
            }

            $stmt2 = $db->prepare("SELECT `auditors_id`, `assigned_nace_classes` FROM `auditors` WHERE `assigned_nace_classes` IS NOT NULL AND `assigned_nace_classes` != ''");
            $stmt2->execute();
            while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $aid = (int)$row['auditors_id'];
                if (empty($map[$aid])) {
                    $map[$aid] = array();
                    foreach (explode(',', (string)$row['assigned_nace_classes']) as $c) {
                        $c = trim($c);
                        if ($c !== '' && !in_array($c, $map[$aid], true)) {
                            $map[$aid][] = $c;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("aitf_get_auditor_nace_classes_map error: " . $e->getMessage());
        }

        return $map;
    }
}

if (!function_exists('aitf_get_client_divisions')) {
    function aitf_get_client_divisions(PDO $db, $customerId, $serviceId = null)
    {
        $customerId = (int)$customerId;
        $divisions = array();

        if ($customerId <= 0) {
            return $divisions;
        }

        try {
            // 1. Check customer_info.aif_nace (format: "25/25.6/25.62, 28/28.1/28.11")
            $stmt = $db->prepare("SELECT `aif_nace` FROM `customer_info` WHERE `id` = :id LIMIT 1");
            $stmt->execute(array(':id' => $customerId));
            $aifNace = $stmt->fetchColumn();
            if (!empty($aifNace)) {
                foreach (explode(',', (string)$aifNace) as $segment) {
                    $segment = trim($segment);
                    if ($segment === '') continue;
                    $parts = explode('/', $segment);
                    $divCode = trim($parts[0]);
                    if ($divCode !== '' && !in_array($divCode, $divisions, true)) {
                        $divisions[] = $divCode;
                    }
                }
            }

            // 2. Check audit_iaf_nace table linked through audit_application
            $appSql = "SELECT ain.iaf_code 
                       FROM `audit_iaf_nace` ain
                       JOIN `audit_application` aa ON ain.audit_id = aa.audit_id
                       LEFT JOIN `service` s ON aa.uni_svc_id = s.service_id
                       WHERE s.customer_id = :cid1 OR aa.cust_id = :cid2
                       ORDER BY ain.id DESC";
            $stmt2 = $db->prepare($appSql);
            $stmt2->execute(array(':cid1' => $customerId, ':cid2' => $customerId));
            while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['iaf_code'])) {
                    foreach (explode(',', (string)$row['iaf_code']) as $c) {
                        $c = trim($c);
                        if ($c !== '' && !in_array($c, $divisions, true)) {
                            $divisions[] = $c;
                        }
                    }
                }
            }

            // 3. Check assessment_planning table
            $stmt3 = $db->prepare("SELECT `iaf_code` FROM `assessment_planning` WHERE `customer_id` = :cid ORDER BY `assessment_id` DESC");
            $stmt3->execute(array(':cid' => $customerId));
            while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['iaf_code'])) {
                    foreach (explode(',', (string)$row['iaf_code']) as $c) {
                        $c = trim($c);
                        if ($c !== '' && !in_array($c, $divisions, true)) {
                            $divisions[] = $c;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("aitf_get_client_divisions error: " . $e->getMessage());
        }

        return array_values(array_unique($divisions));
    }
}

if (!function_exists('aitf_get_client_nace_classes')) {
    function aitf_get_client_nace_classes(PDO $db, $customerId, $serviceId = null)
    {
        $customerId = (int)$customerId;
        $classes = array();

        if ($customerId <= 0) {
            return $classes;
        }

        try {
            // 1. Check customer_info.aif_nace (format: "25/25.6/25.62, 28/28.1/28.11")
            $stmt = $db->prepare("SELECT `aif_nace` FROM `customer_info` WHERE `id` = :id LIMIT 1");
            $stmt->execute(array(':id' => $customerId));
            $aifNace = $stmt->fetchColumn();
            if (!empty($aifNace)) {
                foreach (explode(',', (string)$aifNace) as $segment) {
                    $segment = trim($segment);
                    if ($segment === '') continue;
                    $parts = explode('/', $segment);
                    // If 3 parts exist (Division / NACE Code / NACE Class)
                    if (isset($parts[2]) && trim($parts[2]) !== '') {
                        $cls = trim($parts[2]);
                        if (!in_array($cls, $classes, true)) {
                            $classes[] = $cls;
                        }
                    } elseif (count($parts) === 1 && preg_match('/^\d+\.\d+$/', $parts[0])) {
                        $cls = trim($parts[0]);
                        if (!in_array($cls, $classes, true)) {
                            $classes[] = $cls;
                        }
                    }
                }
            }

            // 2. Check audit_iaf_nace table linked through audit_application
            $appSql = "SELECT ain.nace_class 
                       FROM `audit_iaf_nace` ain
                       JOIN `audit_application` aa ON ain.audit_id = aa.audit_id
                       LEFT JOIN `service` s ON aa.uni_svc_id = s.service_id
                       WHERE s.customer_id = :cid1 OR aa.cust_id = :cid2
                       ORDER BY ain.id DESC";
            $stmt2 = $db->prepare($appSql);
            $stmt2->execute(array(':cid1' => $customerId, ':cid2' => $customerId));
            while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['nace_class'])) {
                    foreach (explode(',', (string)$row['nace_class']) as $c) {
                        $c = trim($c);
                        if ($c !== '' && !in_array($c, $classes, true)) {
                            $classes[] = $c;
                        }
                    }
                }
            }

            // 3. Check assessment_planning table
            $stmt3 = $db->prepare("SELECT `nace_class` FROM `assessment_planning` WHERE `customer_id` = :cid ORDER BY `assessment_id` DESC");
            $stmt3->execute(array(':cid' => $customerId));
            while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['nace_class'])) {
                    foreach (explode(',', (string)$row['nace_class']) as $c) {
                        $c = trim($c);
                        if ($c !== '' && !in_array($c, $classes, true)) {
                            $classes[] = $c;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("aitf_get_client_nace_classes error: " . $e->getMessage());
        }

        return array_values(array_unique($classes));
    }
}

if (!function_exists('aitf_get_eligible_auditors_for_client')) {
    function aitf_get_eligible_auditors_for_client(PDO $db, $customerId, $serviceId = null)
    {
        aitf_ensure_auditor_division_schema($db);
        $clientNaceClasses = aitf_get_client_nace_classes($db, $customerId, $serviceId);
        $hasNaceClasses = !empty($clientNaceClasses);

        $clientDivisions = aitf_get_client_divisions($db, $customerId, $serviceId);
        $hasClientDivisions = !empty($clientDivisions);

        // Fetch all auditors
        try {
            $stmt = $db->prepare("SELECT * FROM `auditors` ORDER BY `auditor_name` ASC");
            $stmt->execute();
            $allAuditors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("aitf_get_eligible_auditors_for_client error: " . $e->getMessage());
            return array(
                'filter_mode' => 'none',
                'client_nace_classes' => $clientNaceClasses,
                'has_nace_classes' => false,
                'client_divisions' => $clientDivisions,
                'has_divisions' => false,
                'auditors' => array(),
                'all_auditors' => array()
            );
        }

        $naceMap = aitf_get_auditor_nace_classes_map($db);
        $divMap = aitf_get_auditor_divisions_map($db);

        // Attach assigned classes and divisions arrays to each auditor
        foreach ($allAuditors as &$aud) {
            $aid = (int)$aud['auditors_id'];
            $aud['assigned_nace_classes_array'] = isset($naceMap[$aid]) ? $naceMap[$aid] : array();
            $aud['assigned_nace_classes_display'] = !empty($aud['assigned_nace_classes_array']) ? implode(', ', $aud['assigned_nace_classes_array']) : '';

            $aud['assigned_divisions_array'] = isset($divMap[$aid]) ? $divMap[$aid] : array();
            $aud['assigned_divisions_display'] = !empty($aud['assigned_divisions_array']) ? implode(', ', $aud['assigned_divisions_array']) : '';
        }
        unset($aud);

        // Filter precedence:
        // 1. Primary: Filter by NACE Class if client has NACE Class(es) defined
        // 2. Fallback: Filter by Division if client only has Division defined
        // 3. None: Return all auditors if client has neither defined
        if ($hasNaceClasses) {
            $filtered = array();
            foreach ($allAuditors as $aud) {
                $audClasses = $aud['assigned_nace_classes_array'];
                $matches = array_intersect($clientNaceClasses, $audClasses);
                if (!empty($matches)) {
                    $filtered[] = $aud;
                }
            }

            return array(
                'filter_mode' => 'nace_class',
                'client_nace_classes' => $clientNaceClasses,
                'has_nace_classes' => true,
                'client_divisions' => $clientDivisions,
                'has_divisions' => $hasClientDivisions,
                'auditors' => $filtered,
                'all_auditors' => $allAuditors
            );
        } elseif ($hasClientDivisions) {
            $filtered = array();
            foreach ($allAuditors as $aud) {
                $audDivs = $aud['assigned_divisions_array'];
                $matches = array_intersect($clientDivisions, $audDivs);
                if (!empty($matches)) {
                    $filtered[] = $aud;
                }
            }

            return array(
                'filter_mode' => 'division',
                'client_nace_classes' => array(),
                'has_nace_classes' => false,
                'client_divisions' => $clientDivisions,
                'has_divisions' => true,
                'auditors' => $filtered,
                'all_auditors' => $allAuditors
            );
        }

        // Client has neither NACE class nor division in contract review yet -> return all auditors
        return array(
            'filter_mode' => 'none',
            'client_nace_classes' => array(),
            'has_nace_classes' => false,
            'client_divisions' => array(),
            'has_divisions' => false,
            'auditors' => $allAuditors,
            'all_auditors' => $allAuditors
        );
    }
}

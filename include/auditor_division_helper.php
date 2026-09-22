<?php
/**
 * Helper functions for Auditor NACE Code / Division assignments and client matching.
 */

if (!function_exists('aitf_ensure_auditor_division_schema')) {
    function aitf_ensure_auditor_division_schema(PDO $db)
    {
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
            error_log("aitf_ensure_auditor_division_schema error creating table: " . $e->getMessage());
        }

        try {
            $db->exec("ALTER TABLE `auditors` ADD COLUMN `assigned_divisions` TEXT NULL");
        } catch (Exception $e) {
            // Column may already exist
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

if (!function_exists('aitf_save_auditor_divisions')) {
    function aitf_save_auditor_divisions(PDO $db, $auditorId, array $divisionCodes)
    {
        $auditorId = (int)$auditorId;
        if ($auditorId <= 0) {
            return false;
        }

        aitf_ensure_auditor_division_schema($db);

        // Sanitize and deduplicate division codes
        $cleaned = array();
        foreach ($divisionCodes as $code) {
            $c = trim((string)$code);
            if ($c !== '' && !in_array($c, $cleaned, true)) {
                $cleaned[] = $c;
            }
        }

        try {
            // Delete old divisions
            $delStmt = $db->prepare("DELETE FROM `auditor_divisions` WHERE `auditor_id` = :id");
            $delStmt->execute(array(':id' => $auditorId));

            // Insert new divisions
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

            // Update assigned_divisions in auditors table as comma-separated string
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

            // Fallback: check assigned_divisions column in auditors table
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

            // Fallback for any auditor not yet in auditor_divisions table
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

if (!function_exists('aitf_get_eligible_auditors_for_client')) {
    function aitf_get_eligible_auditors_for_client(PDO $db, $customerId, $serviceId = null)
    {
        aitf_ensure_auditor_division_schema($db);
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
                'client_divisions' => $clientDivisions,
                'has_divisions' => false,
                'auditors' => array(),
                'all_auditors' => array()
            );
        }

        $divMap = aitf_get_auditor_divisions_map($db);

        // Attach assigned divisions array to each auditor
        foreach ($allAuditors as &$aud) {
            $aid = (int)$aud['auditors_id'];
            $aud['assigned_divisions_array'] = isset($divMap[$aid]) ? $divMap[$aid] : array();
            $aud['assigned_divisions_display'] = !empty($aud['assigned_divisions_array']) ? implode(', ', $aud['assigned_divisions_array']) : '';
        }
        unset($aud);

        if (!$hasClientDivisions) {
            // Client has no division defined in contract review yet -> return all auditors
            return array(
                'client_divisions' => array(),
                'has_divisions' => false,
                'auditors' => $allAuditors,
                'all_auditors' => $allAuditors
            );
        }

        // Filter: only auditors who have at least one matching division
        $filtered = array();
        foreach ($allAuditors as $aud) {
            $audDivs = $aud['assigned_divisions_array'];
            $matches = array_intersect($clientDivisions, $audDivs);
            if (!empty($matches)) {
                $filtered[] = $aud;
            }
        }

        return array(
            'client_divisions' => $clientDivisions,
            'has_divisions' => true,
            'auditors' => $filtered,
            'all_auditors' => $allAuditors
        );
    }
}

<?php
/**
 * Helper functions for Auditor-Reviewer designation and conflict checking.
 */
require_once __DIR__ . '/rbac_helper.php';

if (!function_exists('aitf_is_auditor_reviewer')) {
    function aitf_is_auditor_reviewer(PDO $db, $auditorId)
    {
        $auditorId = (int)$auditorId;
        if ($auditorId <= 0) {
            return false;
        }
        try {
            $stmt = $db->prepare("SELECT is_reviewer FROM auditors WHERE auditors_id = :id LIMIT 1");
            $stmt->execute([':id' => $auditorId]);
            $val = $stmt->fetchColumn();
            return !empty($val);
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('aitf_get_all_reviewers')) {
    function aitf_get_all_reviewers(PDO $db)
    {
        try {
            $stmt = $db->prepare("SELECT auditors_id, auditor_name, auditor_email, auditor_number FROM auditors WHERE is_reviewer = 1 ORDER BY auditor_name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return array();
        }
    }
}

if (!function_exists('aitf_split_auditor_names')) {
    function aitf_split_auditor_names($raw)
    {
        if (empty($raw)) {
            return array();
        }
        // Split on comma, semicolon, newline, slash, or " and "
        $parts = preg_split('/[,;\/\n\r]+|\band\b/i', (string)$raw);
        $names = array();
        foreach ($parts as $p) {
            $p = trim($p);
            // Remove common trailing or leading titles like "(Team Leader)", "(Auditor)"
            $cleaned = preg_replace('/\s*\([^)]*\)/', '', $p);
            $cleaned = trim($cleaned);
            if ($cleaned !== '' && strlen($cleaned) >= 2) {
                $names[] = $cleaned;
            }
        }
        return $names;
    }
}

if (!function_exists('aitf_get_assigned_auditors_for_client')) {
    /**
     * Finds all auditors assigned to a customer/client and/or service.
     * Returns an array of normalized names and auditor IDs.
     */
    function aitf_get_assigned_auditors_for_client(PDO $db, $customerId, $serviceId = null)
    {
        $customerId = trim((string)$customerId);
        $serviceId = trim((string)$serviceId);
        $assignedNames = array();
        $assignedIds = array();

        if ($customerId === '' && $serviceId === '') {
            return array('names' => array(), 'ids' => array());
        }

        // 1. notification_auditor
        try {
            $sql = "SELECT DISTINCT auditor_name, auditor_id FROM notification_auditor WHERE 1=1";
            $params = array();
            if ($customerId !== '' && $serviceId !== '') {
                $sql .= " AND (client_id = :cid OR service_id = :sid)";
                $params[':cid'] = $customerId;
                $params[':sid'] = $serviceId;
            } elseif ($customerId !== '') {
                $sql .= " AND client_id = :cid";
                $params[':cid'] = $customerId;
            } else {
                $sql .= " AND service_id = :sid";
                $params[':sid'] = $serviceId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['auditor_name'])) {
                    foreach (aitf_split_auditor_names($row['auditor_name']) as $n) {
                        $assignedNames[$n] = true;
                    }
                }
                if (!empty($row['auditor_id'])) {
                    $assignedIds[(int)$row['auditor_id']] = true;
                }
            }
        } catch (Exception $e) {}

        // 2. service_type_ca (stage1_auditor, stage2_auditor)
        try {
            $sql = "SELECT ca.stage1_auditor, ca.stage2_auditor 
                    FROM service_type_ca ca 
                    LEFT JOIN service s ON s.service_id = ca.service 
                    WHERE 1=1";
            $params = array();
            if ($customerId !== '' && $serviceId !== '') {
                $sql .= " AND (s.customer_id = :cid OR ca.ca_id = :sid OR ca.service = :sid2)";
                $params[':cid'] = $customerId;
                $params[':sid'] = $serviceId;
                $params[':sid2'] = $serviceId;
            } elseif ($customerId !== '') {
                $sql .= " AND s.customer_id = :cid";
                $params[':cid'] = $customerId;
            } else {
                $sql .= " AND (ca.ca_id = :sid OR ca.service = :sid2)";
                $params[':sid'] = $serviceId;
                $params[':sid2'] = $serviceId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                foreach (array('stage1_auditor', 'stage2_auditor') as $col) {
                    if (!empty($row[$col])) {
                        foreach (aitf_split_auditor_names($row[$col]) as $n) {
                            $assignedNames[$n] = true;
                        }
                    }
                }
            }
        } catch (Exception $e) {}

        // 3. stage_audit_one + stage_oneaudit_id
        try {
            $sql = "SELECT sao.auditor_name, sao.auditor_id 
                    FROM stage_audit_one sao 
                    INNER JOIN stage_oneaudit_id soi ON soi.audit_stage_id = sao.audit_stage_id 
                    WHERE 1=1";
            $params = array();
            if ($customerId !== '' && $serviceId !== '') {
                $sql .= " AND (soi.ca_customer_id = :cid OR soi.ca_service_id = :sid)";
                $params[':cid'] = $customerId;
                $params[':sid'] = $serviceId;
            } elseif ($customerId !== '') {
                $sql .= " AND soi.ca_customer_id = :cid";
                $params[':cid'] = $customerId;
            } else {
                $sql .= " AND soi.ca_service_id = :sid";
                $params[':sid'] = $serviceId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['auditor_name'])) {
                    foreach (aitf_split_auditor_names($row['auditor_name']) as $n) {
                        $assignedNames[$n] = true;
                    }
                }
                if (!empty($row['auditor_id'])) {
                    $assignedIds[(int)$row['auditor_id']] = true;
                }
            }
        } catch (Exception $e) {}

        // 4. stage_audit_two + stage_twoaudit_id
        try {
            $sql = "SELECT sat.auditor_name, sat.auditor_id 
                    FROM stage_audit_two sat 
                    INNER JOIN stage_twoaudit_id sti ON sti.audit_stageii_id = sat.audit_stageii_id 
                    WHERE 1=1";
            $params = array();
            if ($customerId !== '' && $serviceId !== '') {
                $sql .= " AND (sti.customer_id = :cid OR sti.service_id = :sid)";
                $params[':cid'] = $customerId;
                $params[':sid'] = $serviceId;
            } elseif ($customerId !== '') {
                $sql .= " AND sti.customer_id = :cid";
                $params[':cid'] = $customerId;
            } else {
                $sql .= " AND sti.service_id = :sid";
                $params[':sid'] = $serviceId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['auditor_name'])) {
                    foreach (aitf_split_auditor_names($row['auditor_name']) as $n) {
                        $assignedNames[$n] = true;
                    }
                }
                if (!empty($row['auditor_id'])) {
                    $assignedIds[(int)$row['auditor_id']] = true;
                }
            }
        } catch (Exception $e) {}

        // 5. service_type_sai, service_type_saii, service_type_rc
        foreach (array('service_type_sai' => 'sai_id', 'service_type_saii' => 'saii_id', 'service_type_rc' => 'rc_id') as $tbl => $idCol) {
            try {
                $sql = "SELECT t.auditors 
                        FROM $tbl t 
                        LEFT JOIN service s ON s.service_id = t.service 
                        WHERE 1=1";
                $params = array();
                if ($customerId !== '' && $serviceId !== '') {
                    $sql .= " AND (s.customer_id = :cid OR t.$idCol = :sid OR t.service = :sid2)";
                    $params[':cid'] = $customerId;
                    $params[':sid'] = $serviceId;
                    $params[':sid2'] = $serviceId;
                } elseif ($customerId !== '') {
                    $sql .= " AND s.customer_id = :cid";
                    $params[':cid'] = $customerId;
                } else {
                    $sql .= " AND (t.$idCol = :sid OR t.service = :sid2)";
                    $params[':sid'] = $serviceId;
                    $params[':sid2'] = $serviceId;
                }
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (!empty($row['auditors'])) {
                        foreach (aitf_split_auditor_names($row['auditors']) as $n) {
                            $assignedNames[$n] = true;
                        }
                    }
                }
            } catch (Exception $e) {}
        }

        // 6. assessment_planning (lead_auditor, auditors)
        try {
            $sql = "SELECT lead_auditor, auditors FROM assessment_planning WHERE 1=1";
            $params = array();
            if ($customerId !== '' && $serviceId !== '') {
                $sql .= " AND (customer_id = :cid OR service_id = :sid)";
                $params[':cid'] = $customerId;
                $params[':sid'] = $serviceId;
            } elseif ($customerId !== '') {
                $sql .= " AND customer_id = :cid";
                $params[':cid'] = $customerId;
            } else {
                $sql .= " AND service_id = :sid";
                $params[':sid'] = $serviceId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                foreach (array('lead_auditor', 'auditors') as $col) {
                    if (!empty($row[$col])) {
                        foreach (aitf_split_auditor_names($row[$col]) as $n) {
                            $assignedNames[$n] = true;
                        }
                    }
                }
            }
        } catch (Exception $e) {}

        // Resolve any IDs to names from auditors table
        if (!empty($assignedIds)) {
            try {
                $placeholders = implode(',', array_fill(0, count($assignedIds), '?'));
                $stmt = $db->prepare("SELECT auditors_id, auditor_name FROM auditors WHERE auditors_id IN ($placeholders)");
                $stmt->execute(array_keys($assignedIds));
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (!empty($row['auditor_name'])) {
                        $assignedNames[trim($row['auditor_name'])] = true;
                    }
                }
            } catch (Exception $e) {}
        }

        return array(
            'names' => array_values(array_keys($assignedNames)),
            'ids' => array_values(array_keys($assignedIds))
        );
    }
}

if (!function_exists('aitf_normalize_person_name')) {
    function aitf_normalize_person_name($name)
    {
        $name = strtolower(trim((string)$name));
        // Remove multiple spaces, periods, special chars for fuzzy match
        $name = preg_replace('/[^a-z0-9]/', '', $name);
        return $name;
    }
}

if (!function_exists('aitf_check_auditor_reviewer_conflict')) {
    /**
     * Checks if reviewerName conflicts with any auditor assigned to customerId or serviceId.
     * Returns array with 'has_conflict' (bool), 'conflicting_name' (string), 'message' (string).
     */
    function aitf_check_auditor_reviewer_conflict(PDO $db, $customerId, $serviceId, $reviewerName)
    {
        $reviewerName = trim((string)$reviewerName);
        if ($reviewerName === '') {
            return array('has_conflict' => false, 'conflicting_name' => '', 'message' => '');
        }

        $assigned = aitf_get_assigned_auditors_for_client($db, $customerId, $serviceId);
        $normReviewer = aitf_normalize_person_name($reviewerName);

        if ($normReviewer === '') {
            return array('has_conflict' => false, 'conflicting_name' => '', 'message' => '');
        }

        foreach ($assigned['names'] as $auditorName) {
            $normAuditor = aitf_normalize_person_name($auditorName);
            if ($normAuditor === '') {
                continue;
            }

            // Direct equality match or substring match if sufficiently long
            if ($normAuditor === $normReviewer ||
                (strlen($normAuditor) >= 5 && strlen($normReviewer) >= 5 && (strpos($normAuditor, $normReviewer) !== false || strpos($normReviewer, $normAuditor) !== false))) {
                return array(
                    'has_conflict' => true,
                    'conflicting_name' => $auditorName,
                    'message' => "'" . aitf_rbac_escape($auditorName) . "' is already assigned as Auditor of this client, cannot set as Reviewer. Please select another Reviewer."
                );
            }
        }

        return array('has_conflict' => false, 'conflicting_name' => '', 'message' => '');
    }
}

if (!function_exists('aitf_get_appointed_reviewers_for_client')) {
    /**
     * Finds all reviewers appointed for a customer/client and/or service.
     * Checks audit_application (reviewer_name), cdc_report_review, stage_ii_audit_id.
     * Returns array('names' => array of names, 'auditor_ids' => array of matching auditor IDs).
     */
    function aitf_get_appointed_reviewers_for_client(PDO $db, $customerId, $serviceId = null)
    {
        $customerId = trim((string)$customerId);
        $serviceId = trim((string)$serviceId);
        $reviewers = array();
        $reviewerAuditorIds = array();

        if ($customerId === '' && $serviceId === '') {
            return array('names' => array(), 'auditor_ids' => array());
        }

        // Resolve customerId if empty from serviceId
        if ($customerId === '' && $serviceId !== '') {
            try {
                $sStmt = $db->prepare("SELECT customer_id FROM service WHERE service_id = :sid LIMIT 1");
                $sStmt->execute([':sid' => $serviceId]);
                $c = $sStmt->fetchColumn();
                if (!empty($c)) {
                    $customerId = trim((string)$c);
                } else {
                    $caStmt = $db->prepare("SELECT s.customer_id FROM service_type_ca ca JOIN service s ON s.service_id = ca.service WHERE ca.ca_id = :sid LIMIT 1");
                    $caStmt->execute([':sid' => $serviceId]);
                    $c2 = $caStmt->fetchColumn();
                    if (!empty($c2)) {
                        $customerId = trim((string)$c2);
                    }
                }
            } catch (Exception $e) {}
        }

        // Collect all related service_ids and ca_ids for this customer
        $relServiceIds = array();
        $relCaIds = array();
        if ($serviceId !== '') {
            $relServiceIds[$serviceId] = true;
            $relCaIds[$serviceId] = true;
        }
        if ($customerId !== '') {
            try {
                $stmt = $db->prepare("SELECT service_id FROM service WHERE customer_id = :cid");
                $stmt->execute([':cid' => $customerId]);
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $relServiceIds[(string)$r['service_id']] = true;
                }
            } catch (Exception $e) {}

            if (!empty($relServiceIds)) {
                try {
                    $placeholders = implode(',', array_fill(0, count($relServiceIds), '?'));
                    $stmt = $db->prepare("SELECT ca_id, service FROM service_type_ca WHERE service IN ($placeholders) OR ca_id IN ($placeholders)");
                    $stmt->execute(array_merge(array_keys($relServiceIds), array_keys($relServiceIds)));
                    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $relCaIds[(string)$r['ca_id']] = true;
                        $relServiceIds[(string)$r['service']] = true;
                    }
                } catch (Exception $e) {}
            }
        }

        // 1. audit_application (reviewer_name)
        try {
            $conditions = array();
            $params = array();
            if ($customerId !== '') {
                $conditions[] = "(aa.cust_id = :cid AND aa.cust_id > 0)";
                $params[':cid'] = $customerId;
            }
            if (!empty($relServiceIds) || !empty($relCaIds)) {
                $combined = array_unique(array_merge(array_keys($relServiceIds), array_keys($relCaIds)));
                $phList = array();
                $idx = 0;
                foreach ($combined as $idVal) {
                    $ph = ":sid_" . $idx++;
                    $phList[] = $ph;
                    $params[$ph] = $idVal;
                }
                $inStr = implode(',', $phList);
                $conditions[] = "(aa.uni_svc_id IN ($inStr) OR aa.service_id IN ($inStr))";
            }
            if (!empty($conditions)) {
                $sql = "SELECT DISTINCT aa.reviewer_name FROM audit_application aa WHERE (" . implode(' OR ', $conditions) . ") AND aa.reviewer_name IS NOT NULL AND TRIM(aa.reviewer_name) != ''";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    foreach (aitf_split_auditor_names($r['reviewer_name']) as $nm) {
                        $reviewers[$nm] = true;
                    }
                }
            }
        } catch (Exception $e) {}

        // 2. cdc_report_review (reviewer_1, reviewer_2)
        try {
            $conditions = array();
            $params = array();
            if ($customerId !== '') {
                $conditions[] = "customer_id = :cid";
                $params[':cid'] = $customerId;
            }
            if (!empty($relCaIds)) {
                $phList = array();
                $idx = 0;
                foreach (array_keys($relCaIds) as $caIdVal) {
                    $ph = ":caid_" . $idx++;
                    $phList[] = $ph;
                    $params[$ph] = $caIdVal;
                }
                $conditions[] = "ca_service_id IN (" . implode(',', $phList) . ")";
            }
            if (!empty($conditions)) {
                $sql = "SELECT reviewer_1, reviewer_2 FROM cdc_report_review WHERE (" . implode(' OR ', $conditions) . ")";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    foreach (array('reviewer_1', 'reviewer_2') as $col) {
                        if (!empty($r[$col])) {
                            foreach (aitf_split_auditor_names($r[$col]) as $nm) {
                                $reviewers[$nm] = true;
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {}

        // 3. stage_ii_audit_id (ReviewerName)
        try {
            $conditions = array();
            $params = array();
            if ($customerId !== '') {
                $conditions[] = "customer_id = :cid";
                $params[':cid'] = $customerId;
            }
            if (!empty($relCaIds)) {
                $phList = array();
                $idx = 0;
                foreach (array_keys($relCaIds) as $caIdVal) {
                    $ph = ":ca2id_" . $idx++;
                    $phList[] = $ph;
                    $params[$ph] = $caIdVal;
                }
                $conditions[] = "ca_service_id IN (" . implode(',', $phList) . ")";
            }
            if (!empty($conditions)) {
                $sql = "SELECT ReviewerName FROM stage_ii_audit_id WHERE (" . implode(' OR ', $conditions) . ")";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (!empty($r['ReviewerName'])) {
                        foreach (aitf_split_auditor_names($r['ReviewerName']) as $nm) {
                            $reviewers[$nm] = true;
                        }
                    }
                }
            }
        } catch (Exception $e) {}

        // Map reviewer names to auditor IDs if possible
        if (!empty($reviewers)) {
            try {
                $allAuditors = $db->query("SELECT auditors_id, auditor_name FROM auditors")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allAuditors as $aud) {
                    $normAud = aitf_normalize_person_name($aud['auditor_name']);
                    foreach (array_keys($reviewers) as $revName) {
                        $normRev = aitf_normalize_person_name($revName);
                        if ($normAud === $normRev || (strlen($normAud) >= 5 && strlen($normRev) >= 5 && (strpos($normAud, $normRev) !== false || strpos($normRev, $normAud) !== false))) {
                            $reviewerAuditorIds[(int)$aud['auditors_id']] = true;
                        }
                    }
                }
            } catch (Exception $e) {}
        }

        return array(
            'names' => array_values(array_keys($reviewers)),
            'auditor_ids' => array_values(array_keys($reviewerAuditorIds))
        );
    }
}

if (!function_exists('aitf_check_auditor_appointed_as_reviewer')) {
    /**
     * Checks if the given auditor (ID or Name) has already been appointed as Reviewer
     * for this client or service.
     * Returns array('has_conflict' => bool, 'conflicting_name' => string, 'auditor_name' => string, 'message' => string).
     */
    function aitf_check_auditor_appointed_as_reviewer(PDO $db, $customerId, $serviceId, $auditorIdOrName)
    {
        $raw = trim((string)$auditorIdOrName);
        if ($raw === '') {
            return array('has_conflict' => false, 'conflicting_name' => '', 'auditor_name' => '', 'message' => '');
        }

        $auditorName = $raw;
        $auditorId = 0;

        // If numeric ID, fetch the auditor's name
        if (is_numeric($raw)) {
            $auditorId = (int)$raw;
            try {
                $stmt = $db->prepare("SELECT auditor_name FROM auditors WHERE auditors_id = :id LIMIT 1");
                $stmt->execute([':id' => $auditorId]);
                $fetched = $stmt->fetchColumn();
                if (!empty($fetched)) {
                    $auditorName = trim((string)$fetched);
                }
            } catch (Exception $e) {}
        }

        $appointed = aitf_get_appointed_reviewers_for_client($db, $customerId, $serviceId);

        // Check by ID if available
        if ($auditorId > 0 && in_array($auditorId, $appointed['auditor_ids'], true)) {
            return array(
                'has_conflict' => true,
                'conflicting_name' => $auditorName,
                'auditor_name' => $auditorName,
                'message' => "'" . aitf_rbac_escape($auditorName) . "' is Reviewer of this client, cannot set as Auditor. Please select another Auditor."
            );
        }

        // Check by Name matching
        $normAud = aitf_normalize_person_name($auditorName);
        if ($normAud === '') {
            return array('has_conflict' => false, 'conflicting_name' => '', 'auditor_name' => $auditorName, 'message' => '');
        }

        foreach ($appointed['names'] as $revName) {
            $normRev = aitf_normalize_person_name($revName);
            if ($normRev === '') continue;

            if ($normAud === $normRev ||
                (strlen($normAud) >= 5 && strlen($normRev) >= 5 && (strpos($normAud, $normRev) !== false || strpos($normRev, $normAud) !== false))) {
                return array(
                    'has_conflict' => true,
                    'conflicting_name' => $revName,
                    'auditor_name' => $auditorName,
                    'message' => "'" . aitf_rbac_escape($auditorName) . "' is Reviewer of this client, cannot set as Auditor. Please select another Auditor."
                );
            }
        }

        return array('has_conflict' => false, 'conflicting_name' => '', 'auditor_name' => $auditorName, 'message' => '');
    }
}

if (!function_exists('aitf_get_auditors_for_service')) {
    /**
     * Get all assigned auditors specifically for a particular service (e.g. CA service).
     * @param PDO $db
     * @param string|int $serviceId CA service ID (ca_id)
     * @param string|int $customerId Customer ID (optional)
     * @param string $serviceType Service type ('ca', 'sai', etc.)
     * @return array ['names' => [...], 'ids' => [...]]
     */
    function aitf_get_auditors_for_service(PDO $db, $serviceId, $customerId = 0, $serviceType = 'ca')
    {
        $auditorNames = array();
        $auditorIds = array();

        $serviceId = trim((string)$serviceId);
        if ($serviceId === '') {
            return array('names' => array(), 'ids' => array());
        }

        // 1. Check stage_audit_one (Stage 1 Audit Notification) for ca_service_id
        try {
            $stmt = $db->prepare("SELECT auditor_id, auditor_name FROM stage_audit_one WHERE ca_service_id = :ca_id");
            $stmt->execute(array(':ca_id' => $serviceId));
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['auditor_name'])) {
                    $auditorNames[] = trim($row['auditor_name']);
                }
                if (!empty($row['auditor_id'])) {
                    $auditorIds[] = (int)$row['auditor_id'];
                }
            }
        } catch (Exception $e) {}

        // 2. Check stage_audit_two (Stage 2 Audit Notification) for service_id
        try {
            $stmt = $db->prepare("SELECT auditor_id, auditor_name FROM stage_audit_two WHERE service_id = :ca_id");
            $stmt->execute(array(':ca_id' => $serviceId));
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['auditor_name'])) {
                    $auditorNames[] = trim($row['auditor_name']);
                }
                if (!empty($row['auditor_id'])) {
                    $auditorIds[] = (int)$row['auditor_id'];
                }
            }
        } catch (Exception $e) {}

        // 3. Check service_type_ca (stage1_auditor and stage2_auditor text columns)
        try {
            $stmt = $db->prepare("SELECT stage1_auditor, stage2_auditor FROM service_type_ca WHERE ca_id = :ca_id");
            $stmt->execute(array(':ca_id' => $serviceId));
            $caRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($caRow) {
                foreach (array('stage1_auditor', 'stage2_auditor') as $col) {
                    if (!empty($caRow[$col])) {
                        foreach (aitf_split_auditor_names($caRow[$col]) as $p) {
                            if ($p !== '') {
                                $auditorNames[] = $p;
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {}

        // 4. Check stage_oneaudit_id (auditor_list text column)
        try {
            $stmt = $db->prepare("SELECT auditor_list FROM stage_oneaudit_id WHERE ca_service_id = :ca_id");
            $stmt->execute(array(':ca_id' => $serviceId));
            while ($soRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($soRow['auditor_list'])) {
                    foreach (aitf_split_auditor_names($soRow['auditor_list']) as $p) {
                        if (is_numeric($p)) {
                            $auditorIds[] = (int)$p;
                        } elseif ($p !== '') {
                            $auditorNames[] = $p;
                        }
                    }
                }
            }
        } catch (Exception $e) {}

        // 5. Check assessment_planning for this ca_service_id or service_id
        try {
            $stmt = $db->prepare("SELECT lead_auditor, auditors FROM assessment_planning WHERE ca_service_id = :ca_id OR service_id = :ca_id");
            $stmt->execute(array(':ca_id' => $serviceId));
            while ($ap = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($ap['lead_auditor'])) {
                    foreach (aitf_split_auditor_names($ap['lead_auditor']) as $an) {
                        if ($an !== '') $auditorNames[] = $an;
                    }
                }
                if (!empty($ap['auditors'])) {
                    foreach (aitf_split_auditor_names($ap['auditors']) as $an) {
                        if ($an !== '') $auditorNames[] = $an;
                    }
                }
            }
        } catch (Exception $e) {}

        // 6. If auditor IDs exist, also resolve their names from `auditors` table
        if (!empty($auditorIds)) {
            try {
                $uniqueIds = array_values(array_unique(array_filter($auditorIds)));
                if (!empty($uniqueIds)) {
                    $inClause = implode(',', $uniqueIds);
                    $stmt = $db->query("SELECT auditors_id, auditor_name FROM auditors WHERE auditors_id IN ($inClause)");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        if (!empty($row['auditor_name'])) {
                            $auditorNames[] = trim($row['auditor_name']);
                        }
                    }
                }
            } catch (Exception $e) {}
        }

        // Clean, filter, and unique
        $uniqueNames = array();
        foreach ($auditorNames as $nm) {
            $nm = trim((string)$nm);
            if ($nm !== '' && !in_array($nm, $uniqueNames, true)) {
                $uniqueNames[] = $nm;
            }
        }

        return array(
            'names' => $uniqueNames,
            'ids' => array_values(array_unique(array_filter($auditorIds)))
        );
    }
}

if (!function_exists('aitf_check_service_reviewer_conflict')) {
    /**
     * Check if a proposed reviewer for a service is already an auditor of that same service.
     * @param PDO $db
     * @param string|int $serviceId CA service ID (ca_id)
     * @param string $reviewerName Reviewer name
     * @param string|int $customerId Customer ID (optional)
     * @param string $serviceType Service type (default 'ca')
     * @return array
     */
    function aitf_check_service_reviewer_conflict(PDO $db, $serviceId, $reviewerName, $customerId = 0, $serviceType = 'ca')
    {
        $reviewerName = trim((string)$reviewerName);
        if ($reviewerName === '') {
            return array(
                'has_conflict' => false,
                'conflicting_auditor' => '',
                'reviewer_name' => '',
                'message' => '',
                'auditors' => array()
            );
        }

        $assigned = aitf_get_auditors_for_service($db, $serviceId, $customerId, $serviceType);
        $normRev = aitf_normalize_person_name($reviewerName);

        if ($normRev === '') {
            return array(
                'has_conflict' => false,
                'conflicting_auditor' => '',
                'reviewer_name' => $reviewerName,
                'message' => '',
                'auditors' => $assigned['names']
            );
        }

        foreach ($assigned['names'] as $audName) {
            $normAud = aitf_normalize_person_name($audName);
            if ($normAud === '') continue;

            if ($normAud === $normRev ||
                (strlen($normAud) >= 5 && strlen($normRev) >= 5 && (strpos($normAud, $normRev) !== false || strpos($normRev, $normAud) !== false))) {
                return array(
                    'has_conflict' => true,
                    'conflicting_auditor' => $audName,
                    'reviewer_name' => $reviewerName,
                    'message' => "'" . aitf_rbac_escape($audName) . "' is Auditor of this service, cannot set as Reviewer. Please select another Reviewer.",
                    'auditors' => $assigned['names']
                );
            }
        }

        return array(
            'has_conflict' => false,
            'conflicting_auditor' => '',
            'reviewer_name' => $reviewerName,
            'message' => '',
            'auditors' => $assigned['names']
        );
    }
}

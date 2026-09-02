<?php
/**
 * Helper library for Pending Document Tracking & Auditor Communication
 */

if (!function_exists('aitf_ensure_pending_doc_communication_schema')) {
    function aitf_ensure_pending_doc_communication_schema(PDO $db)
    {
        static $ensured = false;
        if ($ensured) {
            return;
        }
        $sql = "CREATE TABLE IF NOT EXISTS pending_doc_communications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_type VARCHAR(30) NOT NULL,
            service_id INT NOT NULL,
            customer_id INT NOT NULL,
            customer_name VARCHAR(255) DEFAULT '',
            document_key VARCHAR(100) NOT NULL,
            document_name VARCHAR(255) NOT NULL,
            auditor_id INT DEFAULT NULL,
            auditor_name VARCHAR(255) NOT NULL,
            status ENUM('YES', 'NO') NOT NULL,
            note TEXT,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX idx_svc_doc (service_type, service_id, document_key),
            INDEX idx_auditor (auditor_name),
            INDEX idx_customer (customer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        try {
            $db->exec($sql);
            $ensured = true;
        } catch (Exception $e) {
            error_log("Schema ensure error for pending_doc_communications: " . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_clean_auditor_names')) {
    function aitf_clean_auditor_names($raw)
    {
        if (empty($raw)) {
            return '';
        }
        $parts = preg_split('/[,;\/]+/', (string)$raw);
        $clean = array();
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '' && !in_array($p, $clean, true)) {
                $clean[] = $p;
            }
        }
        return implode(', ', $clean);
    }
}

if (!function_exists('aitf_auditor_matches')) {
    function aitf_auditor_matches($auditorListStr, $targetAuditorName)
    {
        if (empty($auditorListStr) || empty($targetAuditorName)) {
            return false;
        }
        $target = mb_strtolower(trim($targetAuditorName));
        $parts = preg_split('/[,;\/]+/', (string)$auditorListStr);
        foreach ($parts as $p) {
            $clean = mb_strtolower(trim($p));
            if ($clean !== '' && (strpos($clean, $target) !== false || strpos($target, $clean) !== false)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('aitf_resolve_doc_auditor')) {
    function aitf_resolve_doc_auditor($serviceType, $docKey, array $row)
    {
        $serviceType = strtoupper(trim($serviceType));
        if ($serviceType === 'CA') {
            $stage1Keys = array('signed_notification', 'signed_copy', 'audit_report_I');
            if (in_array($docKey, $stage1Keys, true)) {
                $aud = !empty($row['stage1_auditor']) ? $row['stage1_auditor'] : ($row['stage2_auditor'] ?? '');
            } else {
                $aud = !empty($row['stage2_auditor']) ? $row['stage2_auditor'] : ($row['stage1_auditor'] ?? '');
            }
            return aitf_clean_auditor_names($aud);
        }

        if (in_array($serviceType, array('SA-I', 'SAI', 'SA-II', 'SAII', 'RC'), true)) {
            return aitf_clean_auditor_names($row['auditors'] ?? '');
        }

        return '';
    }
}

if (!function_exists('aitf_get_pending_doc_communications_map')) {
    function aitf_get_pending_doc_communications_map(PDO $db)
    {
        aitf_ensure_pending_doc_communication_schema($db);
        $map = array();
        try {
            // Get the latest communication per (service_type, service_id, document_key)
            $stmt = $db->query("SELECT c1.* FROM pending_doc_communications c1
                INNER JOIN (
                    SELECT service_type, service_id, document_key, MAX(id) as max_id
                    FROM pending_doc_communications
                    GROUP BY service_type, service_id, document_key
                ) c2 ON c1.id = c2.max_id");
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $key = strtoupper(trim($r['service_type'])) . '_' . $r['service_id'] . '_' . trim($r['document_key']);
                $map[$key] = $r;
            }
        } catch (Exception $e) {
            error_log("Error in aitf_get_pending_doc_communications_map: " . $e->getMessage());
        }
        return $map;
    }
}

if (!function_exists('aitf_get_pending_doc_communications_list')) {
    function aitf_get_pending_doc_communications_list(PDO $db, $limit = 500)
    {
        aitf_ensure_pending_doc_communication_schema($db);
        $list = array();
        try {
            $stmt = $db->prepare("SELECT * FROM pending_doc_communications ORDER BY id DESC LIMIT :lim");
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in aitf_get_pending_doc_communications_list: " . $e->getMessage());
        }
        return $list;
    }
}

if (!function_exists('aitf_save_pending_doc_communication')) {
    function aitf_save_pending_doc_communication(PDO $db, array $data)
    {
        aitf_ensure_pending_doc_communication_schema($db);

        $serviceType = strtoupper(trim($data['service_type'] ?? ''));
        $serviceId = (int)($data['service_id'] ?? 0);
        $customerId = (int)($data['customer_id'] ?? 0);
        $customerName = trim((string)($data['customer_name'] ?? ''));
        $docKey = trim((string)($data['document_key'] ?? ''));
        $docName = trim((string)($data['document_name'] ?? ''));
        $auditorId = !empty($data['auditor_id']) ? (int)$data['auditor_id'] : null;
        $auditorName = trim((string)($data['auditor_name'] ?? ''));
        $status = strtoupper(trim((string)($data['status'] ?? '')));
        $note = trim((string)($data['note'] ?? ''));

        if (!in_array($status, array('YES', 'NO'), true)) {
            throw new InvalidArgumentException("Status must be YES or NO");
        }
        if ($serviceType === '' || $serviceId <= 0 || $docKey === '') {
            throw new InvalidArgumentException("Missing service or document key");
        }

        $now = date('Y-m-d H:i:s');

        // Check if an existing record exists for this specific doc & auditor
        $stmt = $db->prepare("SELECT id FROM pending_doc_communications 
            WHERE service_type = :stype AND service_id = :sid AND document_key = :dkey 
            ORDER BY id DESC LIMIT 1");
        $stmt->execute(array(
            ':stype' => $serviceType,
            ':sid' => $serviceId,
            ':dkey' => $docKey
        ));
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $update = $db->prepare("UPDATE pending_doc_communications 
                SET customer_id = :cid, customer_name = :cname, document_name = :dname,
                    auditor_id = :aid, auditor_name = :aname, status = :status, note = :note, updated_at = :up_at
                WHERE id = :id");
            $update->execute(array(
                ':cid' => $customerId,
                ':cname' => $customerName,
                ':dname' => $docName,
                ':aid' => $auditorId,
                ':aname' => $auditorName,
                ':status' => $status,
                ':note' => $note,
                ':up_at' => $now,
                ':id' => $existing['id']
            ));
            return $existing['id'];
        } else {
            $insert = $db->prepare("INSERT INTO pending_doc_communications 
                (service_type, service_id, customer_id, customer_name, document_key, document_name, auditor_id, auditor_name, status, note, created_at, updated_at)
                VALUES (:stype, :sid, :cid, :cname, :dkey, :dname, :aid, :aname, :status, :note, :cat, :uat)");
            $insert->execute(array(
                ':stype' => $serviceType,
                ':sid' => $serviceId,
                ':cid' => $customerId,
                ':cname' => $customerName,
                ':dkey' => $docKey,
                ':dname' => $docName,
                ':aid' => $auditorId,
                ':aname' => $auditorName,
                ':status' => $status,
                ':note' => $note,
                ':cat' => $now,
                ':uat' => $now
            ));
            return (int)$db->lastInsertId();
        }
    }
}

if (!function_exists('aitf_fetch_all_pending_documents')) {
    /**
     * Retrieves all pending documents across CA, SA-I, SA-II, RC.
     * Optionally filters to a specific auditor name.
     */
    function aitf_fetch_all_pending_documents(PDO $db, $filterAuditorName = null)
    {
        $commMap = aitf_get_pending_doc_communications_map($db);
        $pendingList = array();

        $targetAuditor = $filterAuditorName !== null ? trim((string)$filterAuditorName) : '';

        // 1. Service CA
        $caConfigs = array(
            'signed_notification' => array('name' => 'Stage-1 Notification', 'stage' => 1),
            'signed_copy' => array('name' => 'Stage-1 Schedule', 'stage' => 1),
            'audit_report_I' => array('name' => 'Stage-1 Audit Report', 'stage' => 1),
            'signed_notification_II' => array('name' => 'Stage-2 Notification', 'stage' => 2),
            'audit_report_II' => array('name' => 'Stage-2 Audit Report', 'stage' => 2),
            'auditor_notes' => array('name' => 'Stage-2 Auditor Notes', 'stage' => 2),
            'audit_nc_II' => array('name' => 'Stage-2 NC', 'stage' => 2),
            'certificate_print' => array('name' => 'Certificate', 'stage' => 2),
            'customer_satisfaction' => array('name' => 'Client Feedback', 'stage' => 2),
        );

        foreach ($caConfigs as $col => $meta) {
            $stmt = $db->query("SELECT sca.ca_id, sca.service, sca.customer_id, sca.$col AS doc_val, 
                                       sca.stage1_auditor, sca.stage2_auditor, ci.customer_name 
                                FROM service_type_ca sca 
                                LEFT JOIN customer_info ci ON sca.customer_id = ci.id 
                                WHERE sca.$col LIKE '%scanning%' AND ci.is_deleted = 'NO'
                                ORDER BY ci.customer_name ASC");
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $auditors = aitf_resolve_doc_auditor('CA', $col, $r);
                if ($targetAuditor !== '' && !aitf_auditor_matches($auditors, $targetAuditor)) {
                    continue;
                }
                $key = 'CA_' . $r['ca_id'] . '_' . $col;
                $comm = $commMap[$key] ?? null;

                $pendingList[] = array(
                    'service_type' => 'CA',
                    'service_id' => (int)$r['ca_id'],
                    'customer_id' => (int)$r['customer_id'],
                    'customer_name' => $r['customer_name'] ?? 'Unknown Client',
                    'document_key' => $col,
                    'document_name' => $meta['name'],
                    'assigned_auditor' => $auditors,
                    'auditor_name' => $auditors,
                    'communication' => $comm
                );
            }
        }

        // 2. Service SA-I
        $saiConfigs = array(
            'info_update_form' => 'Info Update Form',
            'signed_noti_sai' => 'Signed Notification',
            'signed_copy_sai' => 'Signed Schedule',
            'audit_report_sai' => 'Audit Report',
            'auditor_notes_sai' => 'Auditor Notes',
            'audit_evidance_sai' => 'Audit Evidence',
            'certificate_print' => 'Certificate',
            'customer_review_sai' => 'Client Feedback'
        );
        foreach ($saiConfigs as $col => $docName) {
            $stmt = $db->query("SELECT sai.sai_id, sai.service, sai.customer_id, sai.$col AS doc_val, 
                                       sai.auditors, ci.customer_name 
                                FROM service_type_sai sai 
                                LEFT JOIN customer_info ci ON sai.customer_id = ci.id 
                                WHERE sai.$col LIKE '%scanning%' AND ci.is_deleted = 'NO'
                                ORDER BY ci.customer_name ASC");
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $auditors = aitf_resolve_doc_auditor('SA-I', $col, $r);
                if ($targetAuditor !== '' && !aitf_auditor_matches($auditors, $targetAuditor)) {
                    continue;
                }
                $key = 'SA-I_' . $r['sai_id'] . '_' . $col;
                $comm = $commMap[$key] ?? null;

                $pendingList[] = array(
                    'service_type' => 'SA-I',
                    'service_id' => (int)$r['sai_id'],
                    'customer_id' => (int)$r['customer_id'],
                    'customer_name' => $r['customer_name'] ?? 'Unknown Client',
                    'document_key' => $col,
                    'document_name' => $docName,
                    'assigned_auditor' => $auditors,
                    'auditor_name' => $auditors,
                    'communication' => $comm
                );
            }
        }

        // 3. Service SA-II
        $saiiConfigs = array(
            'update_info_saii' => 'Info Update Form',
            'signed_noti_saii' => 'Signed Notification',
            'signed_copy_saii' => 'Signed Schedule',
            'audit_report_saii' => 'Audit Report',
            'auditor_notes_saii' => 'Auditor Notes',
            'audit_evidences_saii' => 'Audit Evidence',
            'certificate_print' => 'Certificate',
            'customer_review_saii' => 'Client Feedback'
        );
        foreach ($saiiConfigs as $col => $docName) {
            $stmt = $db->query("SELECT saii.saii_id, saii.service, saii.customer_id, saii.$col AS doc_val, 
                                       saii.auditors, ci.customer_name 
                                FROM service_type_saii saii 
                                LEFT JOIN customer_info ci ON saii.customer_id = ci.id 
                                WHERE saii.$col LIKE '%scanning%' AND ci.is_deleted = 'NO'
                                ORDER BY ci.customer_name ASC");
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $auditors = aitf_resolve_doc_auditor('SA-II', $col, $r);
                if ($targetAuditor !== '' && !aitf_auditor_matches($auditors, $targetAuditor)) {
                    continue;
                }
                $key = 'SA-II_' . $r['saii_id'] . '_' . $col;
                $comm = $commMap[$key] ?? null;

                $pendingList[] = array(
                    'service_type' => 'SA-II',
                    'service_id' => (int)$r['saii_id'],
                    'customer_id' => (int)$r['customer_id'],
                    'customer_name' => $r['customer_name'] ?? 'Unknown Client',
                    'document_key' => $col,
                    'document_name' => $docName,
                    'assigned_auditor' => $auditors,
                    'auditor_name' => $auditors,
                    'communication' => $comm
                );
            }
        }

        // 4. Service RC
        $rcConfigs = array(
            'rc_signed_noti' => 'Signed Notification',
            'rc_signed_copy' => 'Signed Schedule',
            'rc_audit_report' => 'Audit Report',
            'auditor_notes_rc' => 'Auditor Notes',
            'rc_audit_evidence' => 'NC & Evidence',
            'certificate_print' => 'Certificate',
            'rc_customer_review' => 'Client Feedback'
        );
        foreach ($rcConfigs as $col => $docName) {
            $stmt = $db->query("SELECT rc.rc_id, rc.service, rc.rc_customer, rc.$col AS doc_val, 
                                       rc.auditors, ci.customer_name 
                                FROM service_type_rc rc 
                                LEFT JOIN customer_info ci ON rc.rc_customer = ci.id 
                                WHERE rc.$col LIKE '%scanning%' AND ci.is_deleted = 'NO'
                                ORDER BY ci.customer_name ASC");
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $auditors = aitf_resolve_doc_auditor('RC', $col, $r);
                if ($targetAuditor !== '' && !aitf_auditor_matches($auditors, $targetAuditor)) {
                    continue;
                }
                $key = 'RC_' . $r['rc_id'] . '_' . $col;
                $comm = $commMap[$key] ?? null;

                $pendingList[] = array(
                    'service_type' => 'RC',
                    'service_id' => (int)$r['rc_id'],
                    'customer_id' => (int)$r['rc_customer'],
                    'customer_name' => $r['customer_name'] ?? 'Unknown Client',
                    'document_key' => $col,
                    'document_name' => $docName,
                    'assigned_auditor' => $auditors,
                    'auditor_name' => $auditors,
                    'communication' => $comm
                );
            }
        }

        return $pendingList;
    }
}

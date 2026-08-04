<?php
require_once __DIR__ . '/rbac_helper.php';

if (!function_exists('aitf_ensure_auditor_portal_schema')) {
    function aitf_ensure_auditor_portal_schema(PDO $db)
    {
        $statements = array(
            "ALTER TABLE auditors ADD COLUMN login_email VARCHAR(100) NULL",
            "ALTER TABLE auditors ADD COLUMN login_key_hash VARCHAR(255) NULL",
            "ALTER TABLE auditors ADD COLUMN login_key_generated_at DATETIME NULL",
            "ALTER TABLE auditors ADD COLUMN login_enabled TINYINT(1) NOT NULL DEFAULT 0",
            "ALTER TABLE auditors ADD COLUMN last_key_sent_at DATETIME NULL"
        );

        foreach ($statements as $sql) {
            try {
                $db->exec($sql);
            } catch (Exception $e) {
                // Column already exists.
            }
        }
    }
}

if (!function_exists('aitf_generate_auditor_login_key')) {
    function aitf_generate_auditor_login_key()
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $parts = array();
        for ($block = 0; $block < 3; $block++) {
            $value = '';
            for ($i = 0; $i < 4; $i++) {
                $value .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $parts[] = $value;
        }
        return 'AITF-AUD-' . date('Y') . '-' . implode('-', $parts);
    }
}

if (!function_exists('aitf_send_auditor_key_mail')) {
    function aitf_send_auditor_key_mail(array $auditor, $email, $key)
    {
        $body = '<p>Dear ' . aitf_rbac_escape($auditor['auditor_name']) . ',</p>'
            . '<p>Your AITF auditor portal login key has been generated.</p>'
            . '<p><strong>Email:</strong> ' . aitf_rbac_escape($email) . '<br>'
            . '<strong>Login Key:</strong> ' . aitf_rbac_escape($key) . '</p>'
            . '<p>Open the AITF login page, choose <strong>Auditor</strong>, and use this email and key to access your personalised auditor dashboard.</p>'
            . '<p>Regards,<br>AITF Management Certification Limited</p>';

        aitf_send_html_mail($email, 'AITF auditor portal login key', $body);
    }
}

if (!function_exists('aitf_parse_audit_date_value')) {
    function aitf_parse_audit_date_value($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/\d{4}-\d{2}-\d{2}/', $value, $match)) {
            return $match[0];
        }

        if (preg_match('/\d{2}[\.\/-]\d{2}[\.\/-]\d{4}/', $value, $match)) {
            $normalized = str_replace(array('.', '/'), '-', $match[0]);
            $parts = explode('-', $normalized);
            if (count($parts) === 3) {
                return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
}

if (!function_exists('aitf_normalize_audit_service_type')) {
    function aitf_normalize_audit_service_type($type)
    {
        $type = strtoupper(trim((string)$type));
        if (in_array($type, array('SA2', 'SAII', 'SA-II', 'STAGE-II'), true)) {
            return 'SA-II';
        }
        if (in_array($type, array('SA1', 'SAI', 'SA-I'), true)) {
            return 'SA-I';
        }
        return $type !== '' ? $type : '-';
    }
}

if (!function_exists('aitf_fetch_auditor_dashboard_data')) {
    function aitf_fetch_auditor_dashboard_data(PDO $db, $auditorId)
    {
        $auditorId = (int)$auditorId;

        $auditorStmt = $db->prepare("SELECT * FROM auditors WHERE auditors_id = :id LIMIT 1");
        $auditorStmt->execute(array(':id' => $auditorId));
        $auditor = $auditorStmt->fetch(PDO::FETCH_ASSOC);
        if (!$auditor) {
            return null;
        }

        $rows = array();

        $stageOne = $db->prepare("SELECT 'Stage I' AS source, sao.stage_one_id AS row_id, sao.auditor_id, sao.auditor_name,
                                         sao.auditor_category, sao.auditor_acceptance, soi.audit_stage_id AS audit_id,
                                         soi.audit_ref_no, soi.audit_dates, soi.intimation_date, soi.service_type,
                                         ci.id AS customer_id, ci.customer_name, ci.unique_id, ci.email_id, ci.contact,
                                         s.service_id AS universal_service_id, s.service_status, s.audit_date
                                  FROM stage_audit_one sao
                                  INNER JOIN stage_oneaudit_id soi ON soi.audit_stage_id = sao.audit_stage_id
                                  LEFT JOIN service_type_ca ca ON ca.ca_id = soi.ca_service_id
                                  LEFT JOIN service s ON s.service_id = ca.service
                                  LEFT JOIN customer_info ci ON ci.id = soi.ca_customer_id
                                  WHERE sao.auditor_id = :auditor_id
                                  ORDER BY soi.audit_stage_id DESC");
        $stageOne->execute(array(':auditor_id' => $auditorId));
        $rows = array_merge($rows, $stageOne->fetchAll(PDO::FETCH_ASSOC));

        $stageTwo = $db->prepare("SELECT 'Stage II' AS source, sat.stage_two_id AS row_id, sat.auditor_id, sat.auditor_name,
                                         sat.auditor_category, sat.auditor_acceptance, sti.audit_stageii_id AS audit_id,
                                         sti.audit_ref_no, sti.audit_dates, sti.intimation_date,
                                         COALESCE(s.service_type, sti.stage_ii_of, 'SA-II') AS service_type,
                                         ci.id AS customer_id, ci.customer_name, ci.unique_id, ci.email_id, ci.contact,
                                         s.service_id AS universal_service_id, s.service_status, s.audit_date
                                  FROM stage_audit_two sat
                                  INNER JOIN stage_twoaudit_id sti ON sti.audit_stageii_id = sat.audit_stageii_id
                                  LEFT JOIN service_type_saii saii ON saii.saii_id = sti.service_id
                                  LEFT JOIN service s ON s.service_id = saii.service
                                  LEFT JOIN customer_info ci ON ci.id = sti.customer_id
                                  WHERE sat.auditor_id = :auditor_id
                                  ORDER BY sti.audit_stageii_id DESC");
        $stageTwo->execute(array(':auditor_id' => $auditorId));
        $rows = array_merge($rows, $stageTwo->fetchAll(PDO::FETCH_ASSOC));

        $today = date('Y-m-d');
        $stats = array(
            'total' => 0,
            'conducted' => 0,
            'future' => 0,
            'ca' => 0,
            'sa2' => 0,
            'rc' => 0,
            'accepted' => 0
        );
        $clients = array();
        $futureAudits = array();

        foreach ($rows as &$row) {
            $row['service_type'] = aitf_normalize_audit_service_type($row['service_type']);
            $row['parsed_audit_date'] = aitf_parse_audit_date_value($row['audit_dates'] ?: $row['audit_date']);
            $stats['total']++;

            if (strtolower(trim((string)$row['auditor_acceptance'])) === 'yes') {
                $stats['accepted']++;
            }

            if ($row['service_type'] === 'CA') {
                $stats['ca']++;
            } elseif ($row['service_type'] === 'SA-II') {
                $stats['sa2']++;
            } elseif ($row['service_type'] === 'RC') {
                $stats['rc']++;
            }

            if (!empty($row['parsed_audit_date']) && $row['parsed_audit_date'] >= $today) {
                $stats['future']++;
                $futureAudits[] = $row;
            } else {
                $stats['conducted']++;
            }

            if (!empty($row['customer_id']) && !isset($clients[$row['customer_id']])) {
                $clients[$row['customer_id']] = array(
                    'customer_id' => $row['customer_id'],
                    'customer_name' => $row['customer_name'],
                    'unique_id' => $row['unique_id'],
                    'email_id' => $row['email_id'],
                    'contact' => $row['contact']
                );
            }
        }
        unset($row);

        $stats['rating'] = $stats['total'] > 0 ? round(($stats['accepted'] / $stats['total']) * 100) : 0;

        usort($futureAudits, function ($a, $b) {
            return strcmp((string)$a['parsed_audit_date'], (string)$b['parsed_audit_date']);
        });

        return array(
            'auditor' => $auditor,
            'stats' => $stats,
            'audits' => $rows,
            'clients' => array_values($clients),
            'future_audits' => $futureAudits
        );
    }
}

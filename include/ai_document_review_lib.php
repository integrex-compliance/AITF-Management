<?php

if (!function_exists('aitfAiEnsureSchema')) {
    function aitfAiEnsureSchema($db)
    {
        if (!$db) {
            return;
        }

        $db->exec("CREATE TABLE IF NOT EXISTS ai_review_rules (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            rule_code VARCHAR(80) NOT NULL,
            title VARCHAR(255) NOT NULL,
            category VARCHAR(120) DEFAULT NULL,
            rule_text TEXT NOT NULL,
            expected_value TEXT DEFAULT NULL,
            rule_scope VARCHAR(40) NOT NULL DEFAULT 'document_scan',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_by VARCHAR(120) DEFAULT NULL,
            updated_by VARCHAR(120) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_ai_review_rule_code (rule_code),
            KEY idx_ai_review_rules_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $db->exec("CREATE TABLE IF NOT EXISTS ai_review_history (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_id INT NOT NULL,
            requested_by VARCHAR(120) DEFAULT NULL,
            model_name VARCHAR(120) DEFAULT NULL,
            document_count INT NOT NULL DEFAULT 0,
            rules_count INT NOT NULL DEFAULT 0,
            status VARCHAR(40) NOT NULL DEFAULT 'pending',
            summary TEXT DEFAULT NULL,
            result_json LONGTEXT DEFAULT NULL,
            raw_response LONGTEXT DEFAULT NULL,
            error_message TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_ai_review_history_customer (customer_id),
            KEY idx_ai_review_history_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $db->exec("CREATE TABLE IF NOT EXISTS ai_review_document_history (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            review_history_id INT UNSIGNED NOT NULL,
            customer_id INT NOT NULL,
            document_key VARCHAR(64) NOT NULL,
            document_name VARCHAR(255) NOT NULL,
            document_label VARCHAR(255) NOT NULL,
            service_type VARCHAR(40) DEFAULT NULL,
            service_id VARCHAR(100) DEFAULT NULL,
            column_name VARCHAR(100) DEFAULT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_hash VARCHAR(64) DEFAULT NULL,
            file_size_bytes BIGINT DEFAULT NULL,
            model_name VARCHAR(120) DEFAULT NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'completed',
            summary TEXT DEFAULT NULL,
            analyzed_by VARCHAR(120) DEFAULT NULL,
            analyzed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_ai_doc_history_customer_doc (customer_id, document_key),
            KEY idx_ai_doc_history_review (review_history_id),
            KEY idx_ai_doc_history_analyzed (analyzed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        aitfAiEnsureColumn($db, 'ai_review_rules', 'rule_scope', "VARCHAR(40) NOT NULL DEFAULT 'document_scan'");
        aitfAiEnsureColumn($db, 'ai_review_history', 'scan_scope', "VARCHAR(40) NOT NULL DEFAULT 'document_scan'");
        try {
            $db->exec("UPDATE ai_review_rules SET rule_scope = 'document_scan' WHERE rule_scope IS NULL OR rule_scope = ''");
            $db->exec("UPDATE ai_review_history
                SET scan_scope = CASE
                    WHEN document_count = 0 AND status = 'completed' THEN 'software_fill'
                    ELSE 'document_scan'
                END
                WHERE scan_scope IS NULL OR scan_scope = ''");
        } catch (Exception $ex) {
            aitfAiLogProviderError('AI schema backfill failed: ' . $ex->getMessage());
        }

        aitfAiSeedDefaultRules($db);
    }
}

if (!function_exists('aitfAiEnsureColumn')) {
    function aitfAiEnsureColumn($db, $table, $column, $definition)
    {
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name");
            $stmt->execute(array(':table_name' => $table, ':column_name' => $column));
            if ((int) $stmt->fetchColumn() === 0) {
                $db->exec("ALTER TABLE `" . str_replace('`', '', $table) . "` ADD COLUMN `" . str_replace('`', '', $column) . "` " . $definition);
            }
        } catch (Exception $ex) {
            aitfAiLogProviderError('AI schema column check failed: ' . $table . '.' . $column . ' - ' . $ex->getMessage());
        }
    }
}

if (!function_exists('aitfAiJsonResponse')) {
    function aitfAiJsonResponse($payload, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }
}

if (!class_exists('AitfAiProviderException')) {
    class AitfAiProviderException extends Exception
    {
        private $rateLimited;
        private $debugDetails;

        public function __construct($message, $rateLimited = false, $code = 0, $debugDetails = array())
        {
            parent::__construct($message, $code);
            $this->rateLimited = (bool) $rateLimited;
            $this->debugDetails = is_array($debugDetails) ? $debugDetails : array();
        }

        public function isRateLimited()
        {
            return $this->rateLimited;
        }

        public function getDebugDetails()
        {
            return $this->debugDetails;
        }
    }
}

if (!function_exists('aitfAiModelConfigs')) {
    function aitfAiModelConfigs()
    {
        return array(
            'fly-512b' => array(
                'provider' => 'gemini',
                'display_name' => 'Fly-512B (MAX)',
                'short_name' => 'Fly-512B',
                'badge' => 'MAX'
            ),
            'swim-128b' => array(
                'provider' => 'groq',
                'model' => 'llama-3.1-8b-instant',
                'display_name' => 'Swim-128B (HIGH)',
                'short_name' => 'Swim-128B',
                'badge' => 'HIGH'
            )
        );
    }
}

if (!function_exists('aitfAiNormalizeModelId')) {
    function aitfAiNormalizeModelId($modelId)
    {
        $modelId = trim((string) $modelId);
        if ($modelId === '') {
            return 'fly-512b';
        }

        $models = aitfAiModelConfigs();
        return isset($models[$modelId]) ? $modelId : '';
    }
}

if (!function_exists('aitfAiModelUnavailableMessage')) {
    function aitfAiModelUnavailableMessage($modelId)
    {
        if ($modelId === 'swim-128b') {
            return 'Swim-128B is temporarily unavailable. You can switch to Fly-512B and try again.';
        }

        return 'Fly-512B is temporarily unavailable. You can switch to Swim-128B and try again.';
    }
}

if (!function_exists('aitfAiLogProviderError')) {
    function aitfAiLogProviderError($message)
    {
        if (function_exists('error_log')) {
            error_log('[AI Document Review] ' . $message);
        }
    }
}

if (!function_exists('aitfAiCurrentCustomerId')) {
    function aitfAiCurrentCustomerId()
    {
        if (isset($_SESSION['current_customer_id']) && (int) $_SESSION['current_customer_id'] > 0) {
            return (int) $_SESSION['current_customer_id'];
        }
        if (isset($_GET['customerid']) && (int) $_GET['customerid'] > 0) {
            return (int) $_GET['customerid'];
        }
        if (isset($_POST['customerid']) && (int) $_POST['customerid'] > 0) {
            return (int) $_POST['customerid'];
        }
        return 0;
    }
}

if (!function_exists('aitfAiTableColumns')) {
    function aitfAiTableColumns($db, $table)
    {
        static $cache = array();
        $table = (string) $table;
        if (isset($cache[$table])) {
            return $cache[$table];
        }

        $stmt = $db->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name");
        $stmt->execute(array(':table_name' => $table));
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $cache[$table] = array_fill_keys($columns ? $columns : array(), true);
        return $cache[$table];
    }
}

if (!function_exists('aitfAiFilterExistingColumns')) {
    function aitfAiFilterExistingColumns($db, $table, array $columns)
    {
        $available = aitfAiTableColumns($db, $table);
        $existing = array();
        foreach ($columns as $column) {
            if (isset($available[$column])) {
                $existing[] = $column;
            }
        }
        return $existing;
    }
}

if (!function_exists('aitfAiNormalizeRuleScope')) {
    function aitfAiNormalizeRuleScope($ruleScope)
    {
        $ruleScope = trim((string) $ruleScope);
        if (in_array($ruleScope, array('document_scan', 'software_fill', 'both'), true)) {
            return $ruleScope;
        }
        return 'document_scan';
    }
}

if (!function_exists('aitfAiRuleScopeLabel')) {
    function aitfAiRuleScopeLabel($ruleScope)
    {
        $ruleScope = aitfAiNormalizeRuleScope($ruleScope);
        if ($ruleScope === 'software_fill') {
            return 'Software Fill Scan';
        }
        if ($ruleScope === 'both') {
            return 'Both Scans';
        }
        return 'Document Scan';
    }
}

if (!function_exists('aitfAiSeedDefaultRules')) {
    function aitfAiSeedDefaultRules($db)
    {
        try {
            $stmt = $db->prepare("INSERT INTO ai_review_rules (rule_code, title, category, rule_text, expected_value, rule_scope, is_active)
                VALUES (:rule_code, :title, :category, :rule_text, :expected_value, :rule_scope, 1)
                ON DUPLICATE KEY UPDATE rule_code = VALUES(rule_code)");

            $rules = array(
                array(
                    'AITF-SOFT-001',
                    'Software form mandatory fields',
                    'Software Fill',
                    'Software-filled client, service, audit, agreement, certificate, CDC, schedule, and review rows must not have missing mandatory values for client identity, service type, standard, scope, key dates, certification decision, auditor/reviewer fields, and certificate data where those forms exist.',
                    'If a required value is empty, null, placeholder, 0000-00-00, or contradictory, report it with table/field evidence.',
                    'software_fill'
                ),
                array(
                    'AITF-SOFT-002',
                    'Software date chronology',
                    'Software Fill',
                    'Dates saved in software forms should follow the certification workflow sequence: application/agreement before planning, notification before audit, audit before review/CDC, CDC before certificate issue, and certificate validity after registration.',
                    'Use saved database rows as evidence. If dates are missing or cannot be compared, mark Manual verification required.',
                    'software_fill'
                ),
                array(
                    'AITF-SOFT-003',
                    'Software client identity consistency',
                    'Software Fill',
                    'Client name, unique id, certification number, GSTIN, address, contact, scope, ISO standard, and service references should remain consistent across customer_info and related software-filled form tables.',
                    'Compare available software rows. Report mismatches and suspicious placeholder values.',
                    'software_fill'
                )
            );

            foreach ($rules as $rule) {
                $stmt->execute(array(
                    ':rule_code' => $rule[0],
                    ':title' => $rule[1],
                    ':category' => $rule[2],
                    ':rule_text' => $rule[3],
                    ':expected_value' => $rule[4],
                    ':rule_scope' => $rule[5]
                ));
            }
        } catch (Exception $ex) {
            aitfAiLogProviderError('AI default rule seed failed: ' . $ex->getMessage());
        }
    }
}

if (!function_exists('aitfAiCompactSoftwareValue')) {
    function aitfAiCompactSoftwareValue($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s+/', ' ', $value);
        if (strlen($value) > 700) {
            $value = substr($value, 0, 700) . '... [truncated]';
        }
        return $value;
    }
}

if (!function_exists('aitfAiCompactSoftwareRows')) {
    function aitfAiCompactSoftwareRows(array $rows)
    {
        $skip = array(
            'result_json' => true,
            'raw_response' => true,
            'password' => true,
            'pass' => true
        );
        $compact = array();
        foreach ($rows as $row) {
            $out = array();
            foreach ($row as $key => $value) {
                if (isset($skip[strtolower((string) $key)])) {
                    continue;
                }
                $out[$key] = aitfAiCompactSoftwareValue($value);
            }
            $compact[] = $out;
        }
        return $compact;
    }
}

if (!function_exists('aitfAiFetchRowsByColumnValues')) {
    function aitfAiFetchRowsByColumnValues($db, $table, array $columnValues, $limit = 25)
    {
        $available = aitfAiTableColumns($db, $table);
        if (empty($available)) {
            return array();
        }

        $whereParts = array();
        $params = array();
        foreach ($columnValues as $column => $values) {
            if (!isset($available[$column])) {
                continue;
            }
            $values = array_values(array_unique(array_filter(array_map('strval', (array) $values), function ($value) {
                return $value !== '';
            })));
            if (empty($values)) {
                continue;
            }

            $placeholders = array();
            foreach ($values as $index => $value) {
                $param = ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $column) . '_' . count($params) . '_' . $index;
                $placeholders[] = $param;
                $params[$param] = $value;
            }
            $whereParts[] = '`' . str_replace('`', '', $column) . '` IN (' . implode(',', $placeholders) . ')';
        }

        if (empty($whereParts)) {
            return array();
        }

        $limit = max(1, min(80, (int) $limit));
        $stmt = $db->prepare("SELECT * FROM `" . str_replace('`', '', $table) . "` WHERE " . implode(' OR ', $whereParts) . " LIMIT " . $limit);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('aitfAiSoftwareSection')) {
    function aitfAiSoftwareSection($label, $table, array $rows)
    {
        return array(
            'label' => $label,
            'table' => $table,
            'row_count' => count($rows),
            'rows' => aitfAiCompactSoftwareRows($rows)
        );
    }
}

if (!function_exists('aitfAiFetchSoftwareFilledContext')) {
    function aitfAiFetchSoftwareFilledContext($db, $customerId)
    {
        $context = array(
            'instruction' => 'These rows are software-filled form data from the AITF application, not uploaded PDF content. Review completeness, date consistency, missing required values, contradictions, and user-requested form checks against client context and active rules.',
            'sections' => array()
        );

        $serviceRows = aitfAiFetchRowsByColumnValues($db, 'service', array('customer_id' => array($customerId)), 80);
        $context['sections'][] = aitfAiSoftwareSection('Client services', 'service', $serviceRows);

        $serviceIds = array();
        foreach ($serviceRows as $row) {
            if (isset($row['service_id'])) {
                $serviceIds[] = $row['service_id'];
            }
        }

        $typedIds = array();
        $serviceTables = array(
            array('label' => 'Certification Audit service form', 'table' => 'service_type_ca', 'id' => 'ca_id'),
            array('label' => 'Surveillance Audit I service form', 'table' => 'service_type_sai', 'id' => 'sai_id'),
            array('label' => 'Surveillance Audit II service form', 'table' => 'service_type_saii', 'id' => 'saii_id'),
            array('label' => 'Recertification service form', 'table' => 'service_type_rc', 'id' => 'rc_id'),
            array('label' => 'Special Audit service form', 'table' => 'service_type_sp', 'id' => 'sp_id'),
            array('label' => 'Pre-transfer review service form', 'table' => 'service_type_ptr', 'id' => 'prt_id'),
            array('label' => 'Transfer Audit service form', 'table' => 'service_type_ta', 'id' => 'ta_id')
        );

        foreach ($serviceTables as $serviceTable) {
            $rows = aitfAiFetchRowsByColumnValues($db, $serviceTable['table'], array(
                'customer_id' => array($customerId),
                'service' => $serviceIds,
                'service_id' => $serviceIds
            ), 40);
            $context['sections'][] = aitfAiSoftwareSection($serviceTable['label'], $serviceTable['table'], $rows);
            foreach ($rows as $row) {
                if (isset($row[$serviceTable['id']])) {
                    $typedIds[] = $row[$serviceTable['id']];
                }
            }
        }

        $directTables = array(
            array('label' => 'Client profile', 'table' => 'customer_info', 'columns' => array('id' => array($customerId))),
            array('label' => 'Certification agreements', 'table' => 'certification_aggrement', 'columns' => array('customer_id' => array($customerId), 'service' => $typedIds)),
            array('label' => 'Audit application forms', 'table' => 'audit_application', 'columns' => array('customer_id' => array($customerId), 'service_id' => $typedIds)),
            array('label' => 'Stage-I notification / release forms', 'table' => 'stage_oneaudit_id', 'columns' => array('ca_customer_id' => array($customerId), 'ca_service_id' => $typedIds)),
            array('label' => 'Stage-II notification / release forms', 'table' => 'stage_twoaudit_id', 'columns' => array('customer_id' => array($customerId), 'service_id' => $typedIds)),
            array('label' => 'Stage-I audit review forms', 'table' => 'stage_i_audit_id', 'columns' => array('customer_id' => array($customerId), 'ca_service_id' => $typedIds)),
            array('label' => 'Stage-II / Annex II review forms', 'table' => 'stage_ii_audit_id', 'columns' => array('customer_id' => array($customerId), 'ca_service_id' => $typedIds)),
            array('label' => 'Surveillance planning forms', 'table' => 'servelance_1_id', 'columns' => array('customer_id' => array($customerId), 'service_id' => $typedIds)),
            array('label' => 'Audit schedule forms', 'table' => 'audit_schedule_id', 'columns' => array('customer_id' => array($customerId), 'serviceca_id' => $typedIds)),
            array('label' => 'Audit schedule II forms', 'table' => 'audit_scheduleii_id', 'columns' => array('customer_id' => array($customerId), 'ca_service' => $typedIds)),
            array('label' => 'Assessment planning forms', 'table' => 'assessment_planning', 'columns' => array('customer_id' => array($customerId), 'service_id' => $typedIds)),
            array('label' => 'Control format I forms', 'table' => 'control_formati_id', 'columns' => array('customer_id' => array($customerId), 'caservice_id' => $typedIds)),
            array('label' => 'Control format II forms', 'table' => 'control_formatii_id', 'columns' => array('customer_id' => array($customerId), 'caservice_id' => $typedIds)),
            array('label' => 'CDC review forms', 'table' => 'cdc_report_review', 'columns' => array('customer_id' => array($customerId), 'ca_service_id' => $typedIds)),
            array('label' => 'Customer survey forms', 'table' => 'customer_survey_id', 'columns' => array('customer_id' => array($customerId), 'ca_serviceid' => $typedIds)),
            array('label' => 'Certificate print forms', 'table' => 'certificate_print', 'columns' => array('customer_id' => array($customerId), 'service_id' => $typedIds))
        );

        foreach ($directTables as $entry) {
            $rows = aitfAiFetchRowsByColumnValues($db, $entry['table'], $entry['columns'], 40);
            $context['sections'][] = aitfAiSoftwareSection($entry['label'], $entry['table'], $rows);
        }

        $contractIds = array();
        $contractIdRows = aitfAiFetchRowsByColumnValues($db, 'contract_id', array(
            'customer_id' => array($customerId),
            'ca_service_id' => $typedIds
        ), 40);
        $context['sections'][] = aitfAiSoftwareSection('Contract review identifiers', 'contract_id', $contractIdRows);
        foreach ($contractIdRows as $row) {
            if (isset($row['contractid'])) {
                $contractIds[] = $row['contractid'];
            }
        }

        $contractReviewRows = aitfAiFetchRowsByColumnValues($db, 'contract_review', array(
            'ca_service_contract' => $contractIds,
            'ca_service' => $typedIds
        ), 80);
        $context['sections'][] = aitfAiSoftwareSection('Contract review checklist rows', 'contract_review', $contractReviewRows);

        return $context;
    }
}

if (!function_exists('aitfAiFetchClientContext')) {
    function aitfAiFetchClientContext($db, $customerId)
    {
        $clientColumns = array(
            'id',
            'customer_name',
            'unique_id',
            'certification_no',
            'audit_status',
            'gstin_no',
            'address',
            'contact_person',
            'contact',
            'email_id',
            'scope',
            'aif_nace',
            'comment',
            'certification_stage_date',
            'add_to',
            'next_service',
            'handler',
            'service_count',
            'standard_for',
            'customer_category',
            'supplier_type',
            'verification_status'
        );
        $clientColumns = aitfAiFilterExistingColumns($db, 'customer_info', $clientColumns);
        if (empty($clientColumns)) {
            $clientColumns = array('id');
        }

        $quotedClientColumns = array();
        foreach ($clientColumns as $column) {
            $quotedClientColumns[] = '`' . str_replace('`', '', $column) . '`';
        }

        $stmt = $db->prepare("SELECT " . implode(', ', $quotedClientColumns) . " FROM customer_info WHERE id = :id LIMIT 1");
        $stmt->execute(array(':id' => $customerId));
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$client) {
            return null;
        }

        $services = array();
        $svcStmt = $db->prepare("SELECT service_id, service_type, customer_id FROM service WHERE customer_id = :id ORDER BY service_id ASC");
        $svcStmt->execute(array(':id' => $customerId));
        $services = $svcStmt->fetchAll(PDO::FETCH_ASSOC);

        $agreements = array();
        $agStmt = $db->prepare("SELECT id, service, aggrement_no, aggrement_date FROM certification_aggrement WHERE customer_id = :id ORDER BY id DESC LIMIT 10");
        $agStmt->execute(array(':id' => $customerId));
        $agreements = $agStmt->fetchAll(PDO::FETCH_ASSOC);

        $certificates = array();
        $certStmt = $db->prepare("SELECT certificate_id, service_id, certificate_no, certificate_scope, registration_date, valid_until, before_date FROM certificate_print WHERE customer_id = :id ORDER BY certificate_id DESC LIMIT 20");
        $certStmt->execute(array(':id' => $customerId));
        $certificates = $certStmt->fetchAll(PDO::FETCH_ASSOC);

        return array(
            'client' => $client,
            'services' => $services,
            'agreements' => $agreements,
            'certificates' => $certificates
        );
    }
}

if (!function_exists('aitfAiDocumentDefinitions')) {
    function aitfAiDocumentDefinitions()
    {
        return array(
            array('table' => 'service_type_cr', 'id' => 'cr_id', 'customer' => 'customer_id', 'service_type' => 'CR', 'columns' => array(
                'application_form' => 'Application Form',
                'certification_aggrement' => 'Certification Agreement'
            )),
            array('table' => 'service_type_ca', 'id' => 'ca_id', 'customer' => 'customer_id', 'service_type' => 'CA', 'columns' => array(
                'info_update_review' => 'Information Updation Review',
                'signed_notification' => 'Signed Notification',
                'signed_copy' => 'Signed Copy',
                'audit_report_I' => 'Stage I Audit Report',
                'signed_notification_II' => 'Stage II Signed Notification',
                'audit_report_II' => 'Stage II Audit Report',
                'auditor_notes' => 'Auditor Notes',
                'audit_nc_II' => 'Stage II Audit NC / Evidence',
                'certificate_print' => 'Certificate Upload',
                'customer_satisfaction' => 'Client Feedback'
            ), 'generated_review_label' => 'Information Updation Review'),
            array('table' => 'service_type_sai', 'id' => 'sai_id', 'customer' => 'customer_id', 'service_type' => 'SA-I', 'columns' => array(
                'info_update_form' => 'Information Updation Form',
                'info_update_review' => 'Information Updation Review',
                'signed_noti_sai' => 'Signed Notification',
                'signed_copy_sai' => 'Signed Copy',
                'audit_report_sai' => 'Audit Report',
                'auditor_notes_sai' => 'Auditor Notes',
                'audit_evidance_sai' => 'Audit Evidence',
                'certificate_print' => 'Certificate Upload',
                'customer_review_sai' => 'Client Feedback'
            ), 'generated_review_label' => 'Information Updation Review'),
            array('table' => 'service_type_saii', 'id' => 'saii_id', 'customer' => 'customer_id', 'service_type' => 'SA-II', 'columns' => array(
                'update_info_saii' => 'Information Updation Form',
                'info_update_review' => 'Information Updation Review',
                'signed_noti_saii' => 'Signed Notification',
                'signed_copy_saii' => 'Signed Copy',
                'audit_report_saii' => 'Audit Report',
                'auditor_notes_saii' => 'Auditor Notes',
                'audit_evidences_saii' => 'Audit Evidence',
                'certificate_print' => 'Certificate Upload',
                'customer_review_saii' => 'Client Feedback'
            ), 'generated_review_label' => 'Information Updation Review'),
            array('table' => 'service_type_rc', 'id' => 'rc_id', 'customer' => 'rc_customer', 'service_type' => 'RC', 'columns' => array(
                'rc_info_update' => 'Information Updation Form',
                'rc_application_form' => 'Application Form',
                'rc_certification_aggrement' => 'Certification Agreement',
                'rc_signed_noti' => 'Signed Notification',
                'rc_signed_copy' => 'Signed Copy',
                'rc_audit_report' => 'Audit Report',
                'auditor_notes_rc' => 'Auditor Notes',
                'rc_audit_evidence' => 'Audit Evidence',
                'certificate_print' => 'Certificate Upload',
                'rc_customer_review' => 'Client Feedback'
            )),
            array('table' => 'service_type_sp', 'id' => 'sp_id', 'customer' => 'sp_customer', 'service_type' => 'SP', 'columns' => array(
                'sp_signed_noti' => 'Signed Notification',
                'sp_signed_copy' => 'Signed Copy',
                'sp_audit_report' => 'Audit Report'
            )),
            array('table' => 'service_type_ta', 'id' => 'ta_id', 'customer' => null, 'service_join' => 'service_id', 'service_type' => 'TA', 'columns' => array(
                'signed_notification' => 'Signed Notification',
                'release_schedule' => 'Release Schedule',
                'audit_report' => 'Audit Report',
                'review_report' => 'Review Report'
            )),
            array('table' => 'service_type_ptr', 'id' => 'prt_id', 'customer' => 'customer_id', 'service_type' => 'PTR', 'columns' => array(
                'audit_report' => 'Audit Report'
            ))
        );
    }
}

if (!function_exists('aitfAiResolvePath')) {
    function aitfAiResolvePath($path)
    {
        $path = trim((string) $path);
        if ($path === '' || strtoupper($path) === 'NONC') {
            return '';
        }

        $normalized = str_replace('\\', '/', $path);
        if (!preg_match('/\.pdf($|\?)/i', $normalized)) {
            return '';
        }

        $normalized = strtok($normalized, '?');
        if (preg_match('#^https?://#i', $normalized)) {
            $parsedPath = parse_url($normalized, PHP_URL_PATH);
            $normalized = $parsedPath ? $parsedPath : $normalized;
        }

        $candidates = array();
        if (preg_match('/^[A-Za-z]:[\/\\\\]/', $normalized)) {
            $candidates[] = $normalized;
            $candidates[] = urldecode($normalized);
        } else {
            $relative = ltrim($normalized, '/');
            $candidates[] = dirname(__DIR__) . '/' . $relative;
            $candidates[] = dirname(__DIR__) . '/' . urldecode($relative);
            if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/' . $relative;
                $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/' . urldecode($relative);
            }
        }

        $root = realpath(dirname(__DIR__));
        foreach ($candidates as $candidate) {
            $real = realpath($candidate);
            if ($real && is_file($real) && is_readable($real)) {
                if ($root && strpos($real, $root) === 0) {
                    return $real;
                }
            }
        }

        return '';
    }
}

if (!function_exists('aitfAiDocumentKey')) {
    function aitfAiDocumentKey(array $doc)
    {
        $parts = array(
            isset($doc['service_type']) ? $doc['service_type'] : '',
            isset($doc['service_id']) ? $doc['service_id'] : '',
            isset($doc['column_name']) ? $doc['column_name'] : '',
            isset($doc['path']) ? $doc['path'] : ''
        );
        return sha1(implode('|', $parts));
    }
}

if (!function_exists('aitfAiDocumentFileHash')) {
    function aitfAiDocumentFileHash(array $doc)
    {
        if (empty($doc['full_path']) || !is_file($doc['full_path'])) {
            return '';
        }
        $hash = @sha1_file($doc['full_path']);
        return $hash ? $hash : '';
    }
}

if (!function_exists('aitfAiPublicDocument')) {
    function aitfAiPublicDocument(array $doc)
    {
        return array(
            'document_key' => isset($doc['document_key']) ? $doc['document_key'] : aitfAiDocumentKey($doc),
            'label' => $doc['label'],
            'document_name' => isset($doc['document_name']) ? $doc['document_name'] : $doc['file_name'],
            'file_name' => $doc['file_name'],
            'service_type' => $doc['service_type'],
            'service_id' => $doc['service_id'],
            'column_name' => $doc['column_name'],
            'size_bytes' => $doc['size_bytes'],
            'scan_status' => isset($doc['scan_status']) ? $doc['scan_status'] : 'Not Scanned',
            'last_analyzed_at' => isset($doc['last_analyzed_at']) ? $doc['last_analyzed_at'] : '',
            'latest_history_id' => isset($doc['latest_history_id']) ? $doc['latest_history_id'] : '',
            'latest_document_review_id' => isset($doc['latest_document_review_id']) ? $doc['latest_document_review_id'] : ''
        );
    }
}

if (!function_exists('aitfAiFetchClientPdfDocuments')) {
    function aitfAiFetchClientPdfDocuments($db, $customerId)
    {
        $documents = array();
        $seen = array();

        foreach (aitfAiDocumentDefinitions() as $def) {
            if (empty(aitfAiTableColumns($db, $def['table']))) {
                continue;
            }

            $definedColumns = array_keys($def['columns']);
            $columns = aitfAiFilterExistingColumns($db, $def['table'], $definedColumns);
            if (empty($columns)) {
                continue;
            }

            $available = aitfAiTableColumns($db, $def['table']);
            $serviceColumn = !empty($def['service_join']) ? $def['service_join'] : 'service';
            if (!isset($available[$def['id']]) || !isset($available[$serviceColumn])) {
                continue;
            }
            if (!empty($def['customer']) && !isset($available[$def['customer']])) {
                continue;
            }

            $selectColumns = array_merge(array($def['id'], $serviceColumn), $columns);
            $selectColumns = array_unique($selectColumns);
            $quoted = array();
            foreach ($selectColumns as $column) {
                $quoted[] = '`' . str_replace('`', '', $column) . '`';
            }

            if (!empty($def['customer'])) {
                $sql = "SELECT " . implode(',', $quoted) . " FROM `" . $def['table'] . "` WHERE `" . $def['customer'] . "` = :customer_id";
                $stmt = $db->prepare($sql);
                $stmt->execute(array(':customer_id' => $customerId));
            } else {
                $sql = "SELECT d." . implode(', d.', $quoted) . " FROM `" . $def['table'] . "` d INNER JOIN service s ON s.service_id = d.`" . $def['service_join'] . "` WHERE s.customer_id = :customer_id";
                $stmt = $db->prepare($sql);
                $stmt->execute(array(':customer_id' => $customerId));
            }

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                foreach ($columns as $column) {
                    $label = $def['columns'][$column];
                    if (empty($row[$column])) {
                        continue;
                    }
                    $fullPath = aitfAiResolvePath($row[$column]);
                    if ($fullPath === '') {
                        continue;
                    }
                    if (isset($seen[$fullPath])) {
                        continue;
                    }
                    $seen[$fullPath] = true;
                    $documents[] = array(
                        'label' => $def['service_type'] . ' - ' . $label,
                        'document_name' => basename($fullPath),
                        'service_type' => $def['service_type'],
                        'service_id' => isset($row[$def['id']]) ? $row[$def['id']] : '',
                        'column_name' => $column,
                        'path' => str_replace('\\', '/', (string) $row[$column]),
                        'full_path' => $fullPath,
                        'file_name' => basename($fullPath),
                        'size_bytes' => filesize($fullPath)
                    );
                }

                if (!empty($def['generated_review_label']) && !empty($row[$def['id']])) {
                    $reviewPath = 'uploads/reviews/info_review_' . $row[$def['id']] . '_' . $customerId . '.pdf';
                    $fullPath = aitfAiResolvePath($reviewPath);
                    if ($fullPath !== '' && !isset($seen[$fullPath])) {
                        $seen[$fullPath] = true;
                        $documents[] = array(
                            'label' => $def['service_type'] . ' - ' . $def['generated_review_label'],
                            'document_name' => basename($fullPath),
                            'service_type' => $def['service_type'],
                            'service_id' => isset($row[$def['id']]) ? $row[$def['id']] : '',
                            'column_name' => 'generated_info_update_review',
                            'path' => $reviewPath,
                            'full_path' => $fullPath,
                            'file_name' => basename($fullPath),
                            'size_bytes' => filesize($fullPath)
                        );
                    }
                }
            }
        }

        usort($documents, function ($a, $b) {
            return strcmp($a['label'] . $a['file_name'], $b['label'] . $b['file_name']);
        });

        foreach ($documents as $index => $doc) {
            $documents[$index]['document_key'] = aitfAiDocumentKey($doc);
        }

        return $documents;
    }
}

if (!function_exists('aitfAiAttachDocumentReviewStatus')) {
    function aitfAiAttachDocumentReviewStatus($db, $customerId, array $documents)
    {
        if (empty($documents)) {
            return $documents;
        }

        $keys = array();
        foreach ($documents as $doc) {
            if (!empty($doc['document_key'])) {
                $keys[] = $doc['document_key'];
            }
        }
        $keys = array_values(array_unique($keys));
        if (empty($keys)) {
            return $documents;
        }

        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $params = array_merge(array($customerId), $keys);
        $sql = "SELECT dh.*
                FROM ai_review_document_history dh
                INNER JOIN (
                    SELECT customer_id, document_key, MAX(id) AS latest_id
                    FROM ai_review_document_history
                    WHERE customer_id = ? AND document_key IN ($placeholders)
                    GROUP BY customer_id, document_key
                ) latest ON latest.latest_id = dh.id";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $latestByKey = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $latestByKey[$row['document_key']] = $row;
        }

        foreach ($documents as $index => $doc) {
            $key = isset($doc['document_key']) ? $doc['document_key'] : aitfAiDocumentKey($doc);
            $documents[$index]['document_key'] = $key;
            $documents[$index]['scan_status'] = 'Not Scanned';
            $documents[$index]['last_analyzed_at'] = '';
            $documents[$index]['latest_history_id'] = '';
            $documents[$index]['latest_document_review_id'] = '';
            if (isset($latestByKey[$key]) && $latestByKey[$key]['status'] === 'completed') {
                $documents[$index]['scan_status'] = 'Scanned';
                $documents[$index]['last_analyzed_at'] = $latestByKey[$key]['analyzed_at'];
                $documents[$index]['latest_history_id'] = $latestByKey[$key]['review_history_id'];
                $documents[$index]['latest_document_review_id'] = $latestByKey[$key]['id'];
            }
        }

        return $documents;
    }
}

if (!function_exists('aitfAiSelectDocumentsByKeys')) {
    function aitfAiSelectDocumentsByKeys(array $documents, array $documentKeys)
    {
        $wanted = array_fill_keys($documentKeys, true);
        $selected = array();
        foreach ($documents as $doc) {
            $key = isset($doc['document_key']) ? $doc['document_key'] : aitfAiDocumentKey($doc);
            if (isset($wanted[$key])) {
                $selected[] = $doc;
            }
        }
        return $selected;
    }
}

if (!function_exists('aitfAiFetchActiveRules')) {
    function aitfAiFetchActiveRules($db)
    {
        $stmt = $db->prepare("SELECT id, rule_code, title, category, rule_text, expected_value FROM ai_review_rules WHERE is_active = 1 ORDER BY category ASC, rule_code ASC, id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('aitfAiFetchLatestHistory')) {
    function aitfAiFetchLatestHistory($db, $customerId)
    {
        $stmt = $db->prepare("SELECT id, customer_id, requested_by, model_name, document_count, rules_count, scan_scope, status, summary, result_json, error_message, created_at FROM ai_review_history WHERE customer_id = :customer_id ORDER BY id DESC LIMIT 1");
        $stmt->execute(array(':customer_id' => $customerId));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('aitfAiNormalizeScanScope')) {
    function aitfAiNormalizeScanScope($scanScope, $documentCount)
    {
        $scanScope = trim((string) $scanScope);
        if ($scanScope === 'software_fill') {
            return 'software_fill';
        }
        return ((int) $documentCount > 0) ? 'document_scan' : 'software_fill';
    }
}

if (!function_exists('aitfAiFetchCustomerScanCompletion')) {
    function aitfAiFetchCustomerScanCompletion($db, $customerId)
    {
        $summary = array(
            'document_scan_completed' => false,
            'software_fill_completed' => false,
            'both_completed' => false,
            'last_document_scan_at' => '',
            'last_software_fill_at' => ''
        );

        try {
            $docStmt = $db->prepare("SELECT MAX(analyzed_at) FROM ai_review_document_history WHERE customer_id = :customer_id AND status = 'completed'");
            $docStmt->execute(array(':customer_id' => $customerId));
            $docDate = $docStmt->fetchColumn();
            if ($docDate) {
                $summary['document_scan_completed'] = true;
                $summary['last_document_scan_at'] = $docDate;
            }

            $softwareStmt = $db->prepare("SELECT MAX(created_at) FROM ai_review_history WHERE customer_id = :customer_id AND status = 'completed' AND scan_scope = 'software_fill'");
            $softwareStmt->execute(array(':customer_id' => $customerId));
            $softwareDate = $softwareStmt->fetchColumn();
            if ($softwareDate) {
                $summary['software_fill_completed'] = true;
                $summary['last_software_fill_at'] = $softwareDate;
            }
        } catch (Exception $ex) {
            aitfAiLogProviderError('AI customer scan completion failed: ' . $ex->getMessage());
        }

        $summary['both_completed'] = $summary['document_scan_completed'] && $summary['software_fill_completed'];
        return $summary;
    }
}

if (!function_exists('aitfAiBuildReviewPayload')) {
    function aitfAiBuildReviewPayload($context, $documents, $rules, $userPrompt = '')
    {
        $docList = array();
        foreach ($documents as $index => $doc) {
            $docList[] = array(
                'document_id' => 'DOC-' . ($index + 1),
                'document_key' => isset($doc['document_key']) ? $doc['document_key'] : aitfAiDocumentKey($doc),
                'label' => $doc['label'],
                'file_name' => $doc['file_name'],
                'service_type' => $doc['service_type'],
                'service_id' => $doc['service_id'],
                'database_column' => $doc['column_name']
            );
        }

        return array(
            'client_context' => $context,
            'active_ai_rules_from_database' => $rules,
            'documents_sent' => $docList,
            'user_prompt' => trim((string) $userPrompt)
        );
    }
}

if (!function_exists('aitfAiBuildSharedSystemInstruction')) {
    function aitfAiBuildSharedSystemInstruction()
    {
        return "You are AI Powered by AITF, acting as a certification pre-audit document and software-form reviewer.\n"
            . "Read the supplied PDF document content, attached PDF files, client_context, and any software_filled_forms rows using your own document understanding and reasoning. Do not rely only on file names.\n"
            . "When software_filled_forms is supplied, treat it as filled form data from the application database: contract review, CA, SA-I, SA-II, RC, notifications/releases, schedules, audit review rows, agreement/certificate rows, and related form fields.\n"
            . "Independently detect potential inconsistencies, contradictions, missing information, suspicious values, incorrect-looking information, date issues, chronology problems, client-detail mismatches, scope/standard/manday risks, and any other documentation risk visible in the PDFs or client context.\n"
            . "The active rules supplied in active_ai_rules_from_database are additional mandatory AITF checks. Apply every active rule where relevant, but do not limit your review to those rules.\n"
            . "If multiple PDFs are attached, analyze them together and cross-check names, addresses, GSTIN, contacts, ISO standards, scope, dates, mandays, service stage, certificate details, audit evidence, and other repeated facts across documents.\n"
            . "Use direct evidence from the documents and client_context. Do not invent expected values, dates, standards, mandays, or client details. If a value cannot be confirmed, write \"Manual verification required\".\n"
            . "If user_prompt is not empty, answer that instruction directly while still performing the document risk review and mandatory AITF rule checks.\n"
            . "Use confidence as fair report accuracy, scored strictly from evidence between 0 and 1 or 0 and 100. Do not inflate scores: below 40 means Bad Report, 50-59 Fine, 60-69 Good, 70-89 Very Good, 90-94 Perfect, and 95-100 Outstanding. The rating must not be biased or unnecessarily positive.\n"
            . "Return strict JSON only. Do not wrap in markdown.\n"
            . "JSON shape: {\"overall_status\":\"pass|review_required|manual_verification_required\",\"summary\":\"string\",\"findings\":[{\"severity\":\"critical|major|minor|info\",\"check_area\":\"string\",\"finding\":\"string\",\"evidence\":\"string\",\"current_value\":\"string\",\"expected_value\":\"string\",\"reason\":\"string\",\"rule_reference\":\"string\",\"affected_documents\":[\"string\"],\"confidence\":0.0,\"suggestion\":\"string\",\"recommended_action\":\"string\"}],\"issues\":[{\"severity\":\"critical|major|minor|info\",\"check_area\":\"string\",\"finding\":\"string\",\"evidence\":\"string\",\"current_value\":\"string\",\"expected_value\":\"string\",\"reason\":\"string\",\"rule_reference\":\"string\",\"affected_documents\":[\"string\"],\"confidence\":0.0,\"suggestion\":\"string\",\"recommended_action\":\"string\"}],\"manual_verifications\":[{\"check_area\":\"string\",\"reason\":\"string\",\"evidence\":\"string\",\"affected_documents\":[\"string\"]}],\"documents_reviewed\":[\"string\"]}.\n"
            . "Use rule_reference as the database rule_code/title when an issue is rule-based; otherwise use \"Document evidence\" or \"Manual verification required\".";
    }
}

if (!function_exists('aitfAiBuildPrompt')) {
    function aitfAiBuildPrompt($context, $documents, $rules, $userPrompt = '')
    {
        $payload = aitfAiBuildReviewPayload($context, $documents, $rules, $userPrompt);

        return aitfAiBuildSharedSystemInstruction() . "\n\n" . json_encode($payload);
    }
}

if (!function_exists('aitfAiExtractGeminiText')) {
    function aitfAiExtractGeminiText($response)
    {
        if (isset($response['candidates'][0]['content']['parts'])) {
            $text = '';
            foreach ($response['candidates'][0]['content']['parts'] as $part) {
                if (isset($part['text'])) {
                    $text .= $part['text'];
                }
            }
            return trim($text);
        }
        return '';
    }
}

if (!function_exists('aitfAiDecodeJsonText')) {
    function aitfAiDecodeJsonText($text)
    {
        $text = trim((string) $text);
        if (strpos($text, '```') === 0) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);
        }
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $decoded = json_decode(substr($text, $start, $end - $start + 1), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return null;
    }
}

if (!function_exists('aitfAiNormalizeAiResult')) {
    function aitfAiNormalizeAiResult($result)
    {
        if (!is_array($result)) {
            throw new Exception('AI response was not valid structured JSON.');
        }

        $hasStructuredContent = isset($result['summary']) || isset($result['findings']) || isset($result['issues']) || isset($result['manual_verifications']);
        if (!$hasStructuredContent) {
            throw new Exception('AI response did not include the required review result structure.');
        }

        $allowedStatuses = array('pass', 'review_required', 'manual_verification_required');
        if (empty($result['overall_status']) || !in_array($result['overall_status'], $allowedStatuses, true)) {
            $result['overall_status'] = 'manual_verification_required';
        }
        if (!isset($result['summary'])) {
            $result['summary'] = '';
        }
        foreach (array('findings', 'issues', 'manual_verifications', 'documents_reviewed') as $field) {
            if (!isset($result[$field]) || !is_array($result[$field])) {
                $result[$field] = array();
            }
        }

        return $result;
    }
}

if (!function_exists('aitfAiPdfDecodeTextString')) {
    function aitfAiPdfDecodeTextString($text)
    {
        $text = preg_replace_callback('/\\\\([0-7]{1,3})/', function ($match) {
            return chr(octdec($match[1]));
        }, $text);
        $text = str_replace(
            array('\\n', '\\r', '\\t', '\\b', '\\f', '\\(', '\\)', '\\\\'),
            array("\n", "\r", "\t", "\b", "\f", '(', ')', '\\'),
            $text
        );

        return $text;
    }
}

if (!function_exists('aitfAiPdfDecodeHexText')) {
    function aitfAiPdfDecodeHexText($hex)
    {
        $hex = preg_replace('/[^0-9A-Fa-f]/', '', (string) $hex);
        if ($hex === '') {
            return '';
        }
        if (strlen($hex) % 2 === 1) {
            $hex .= '0';
        }

        $binary = @pack('H*', $hex);
        if ($binary === false) {
            return '';
        }

        if (substr($binary, 0, 2) === "\xFE\xFF" && function_exists('mb_convert_encoding')) {
            return mb_convert_encoding(substr($binary, 2), 'UTF-8', 'UTF-16BE');
        }

        return str_replace("\0", '', $binary);
    }
}

if (!function_exists('aitfAiCleanExtractedPdfText')) {
    function aitfAiCleanExtractedPdfText($text)
    {
        $rawText = (string) $text;
        $text = preg_replace('/[^\P{C}\r\n\t]+/u', ' ', $rawText);
        if ($text === null) {
            $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', ' ', $rawText);
        }
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\R{3,}/', "\n\n", $text);
        return trim($text);
    }
}

if (!function_exists('aitfAiExtractPdfText')) {
    function aitfAiExtractPdfText($path, $maxChars = 60000)
    {
        $data = @file_get_contents($path);
        if ($data === false || $data === '') {
            return '';
        }

        $sources = array();
        if (preg_match_all('/stream\s*(.*?)\s*endstream/s', $data, $streams)) {
            foreach ($streams[1] as $stream) {
                $stream = ltrim($stream, "\r\n");
                $decoded = @gzuncompress($stream);
                if ($decoded === false) {
                    $decoded = @gzinflate($stream);
                }
                $sources[] = $decoded !== false ? $decoded : $stream;
            }
        }
        $sources[] = $data;

        $text = '';
        foreach ($sources as $source) {
            if (preg_match_all('/\((?:\\\\.|[^\\\\)])*\)/s', $source, $matches)) {
                foreach ($matches[0] as $match) {
                    $text .= aitfAiPdfDecodeTextString(substr($match, 1, -1)) . ' ';
                    if (strlen($text) >= $maxChars) {
                        break 2;
                    }
                }
            }
            if (preg_match_all('/<([0-9A-Fa-f\s]{4,})>/', $source, $hexMatches)) {
                foreach ($hexMatches[1] as $hex) {
                    $text .= aitfAiPdfDecodeHexText($hex) . ' ';
                    if (strlen($text) >= $maxChars) {
                        break 2;
                    }
                }
            }
        }

        $text = aitfAiCleanExtractedPdfText($text);
        if ($maxChars > 0 && strlen($text) > $maxChars) {
            $text = substr($text, 0, $maxChars) . "\n[Text truncated for model input limit.]";
        }

        return $text;
    }
}

if (!function_exists('aitfAiBuildGroqDocumentInput')) {
    function aitfAiBuildGroqDocumentInput($context, $documents, $rules, $userPrompt = '', $maxChars = 120000, &$debugDetails = null)
    {
        $payload = aitfAiBuildReviewPayload($context, $documents, $rules, $userPrompt);
        $input = "Analyze the client context, active AITF rules, selected document metadata, and extracted PDF text below.\n";
        $input .= "If extracted text is incomplete or missing for a content-specific check, mark that item as Manual verification required rather than guessing.\n\n";
        $input .= json_encode($payload) . "\n\n";

        $totalExtractedTextLength = 0;
        $documentDebug = array();
        $remaining = $maxChars - strlen($input);
        foreach ($documents as $index => $doc) {
            if ($remaining <= 1000) {
                $input .= "\n[Additional document text omitted because the selected model input limit was reached.]";
                break;
            }

            $perDocLimit = max(1000, min(40000, $remaining - 500));
            $text = aitfAiExtractPdfText($doc['full_path'], $perDocLimit);
            if ($text === '') {
                $text = '[No extractable PDF text found. Content-specific checks require manual verification for this document.]';
            }
            $textLength = strlen($text);
            $totalExtractedTextLength += $textLength;
            $documentDebug[] = array(
                'label' => isset($doc['label']) ? $doc['label'] : '',
                'file_name' => isset($doc['file_name']) ? $doc['file_name'] : '',
                'extracted_text_length' => $textLength
            );

            $section = "\n\n--- DOCUMENT " . ($index + 1) . " ---\n"
                . "Label: " . $doc['label'] . "\n"
                . "File: " . $doc['file_name'] . "\n"
                . "Service: " . $doc['service_type'] . " #" . $doc['service_id'] . "\n"
                . "Database column: " . $doc['column_name'] . "\n"
                . "Extracted PDF text:\n" . $text . "\n";

            if (strlen($section) > $remaining) {
                $section = substr($section, 0, $remaining) . "\n[Document text truncated for model input limit.]";
            }
            $input .= $section;
            $remaining = $maxChars - strlen($input);
        }

        if (is_array($debugDetails)) {
            $debugDetails['extracted_pdf_text_length'] = $totalExtractedTextLength;
            $debugDetails['document_text_lengths'] = $documentDebug;
            $debugDetails['user_message_length'] = strlen($input);
            $debugDetails['input_max_chars'] = $maxChars;
        }

        return $input;
    }
}

if (!function_exists('aitfAiCallGemini')) {
    function aitfAiCallGemini($apiKey, $model, $prompt, $documents)
    {
        $parts = array(array('text' => $prompt));
        foreach ($documents as $doc) {
            $content = file_get_contents($doc['full_path']);
            if ($content === false) {
                throw new Exception('Unable to read PDF: ' . $doc['file_name']);
            }
            $parts[] = array('text' => 'Attached document: ' . $doc['label'] . ' / ' . $doc['file_name']);
            $parts[] = array('inline_data' => array(
                'mime_type' => 'application/pdf',
                'data' => base64_encode($content)
            ));
        }

        $payload = array(
            'contents' => array(array('role' => 'user', 'parts' => $parts)),
            'generationConfig' => array(
                'temperature' => 0.1,
                'responseMimeType' => 'application/json'
            )
        );

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($apiKey);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $curlErr) {
            throw new AitfAiProviderException('Gemini request failed: ' . $curlErr);
        }

        $decoded = json_decode($raw, true);
        if ($status < 200 || $status >= 300) {
            $message = isset($decoded['error']['message']) ? $decoded['error']['message'] : ('HTTP ' . $status);
            throw new AitfAiProviderException('Gemini API error: ' . $message, $status === 429, $status);
        }
        if (!is_array($decoded)) {
            throw new AitfAiProviderException('Gemini returned a non-JSON API response.');
        }

        $text = aitfAiExtractGeminiText($decoded);
        $result = aitfAiDecodeJsonText($text);
        if (!$result) {
            throw new AitfAiProviderException('Gemini response could not be parsed as structured JSON.');
        }

        return array('result' => aitfAiNormalizeAiResult($result), 'raw' => $raw, 'model_text' => $text);
    }
}

if (!function_exists('aitfAiGroqDebugSummary')) {
    function aitfAiGroqDebugSummary(array $debugDetails)
    {
        $parts = array();
        foreach (array(
            'http_status',
            'groq_error_type',
            'groq_error_message',
            'extracted_pdf_text_length',
            'system_prompt_length',
            'user_message_length',
            'final_prompt_length',
            'finish_reason',
            'response_content_exists',
            'response_content_length',
            'json_parse_error'
        ) as $key) {
            if (array_key_exists($key, $debugDetails)) {
                $value = $debugDetails[$key];
                if (is_bool($value)) {
                    $value = $value ? 'yes' : 'no';
                }
                $parts[] = $key . '=' . $value;
            }
        }

        return implode('; ', $parts);
    }
}

if (!function_exists('aitfAiSanitizeGroqErrorMessage')) {
    function aitfAiSanitizeGroqErrorMessage($message)
    {
        $message = preg_replace('/organization `[^`]+`/i', 'organization [redacted]', (string) $message);
        $message = preg_replace('#https?://\S+#i', '[link removed]', $message);
        return trim($message);
    }
}

if (!function_exists('aitfAiCallGroq')) {
    function aitfAiCallGroq($apiKey, $model, $systemPrompt, $userMessage, $debugDetails = array())
    {
        if (!is_array($debugDetails)) {
            $debugDetails = array();
        }
        $debugDetails['system_prompt_length'] = strlen((string) $systemPrompt);
        $debugDetails['user_message_length'] = strlen((string) $userMessage);
        $debugDetails['final_prompt_length'] = $debugDetails['system_prompt_length'] + $debugDetails['user_message_length'];
        $debugDetails['http_status'] = 0;
        $debugDetails['finish_reason'] = '';
        $debugDetails['response_content_exists'] = 'no';
        $debugDetails['response_content_length'] = 0;
        $debugDetails['json_parse_error'] = '';

        $payload = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $systemPrompt
                ),
                array(
                    'role' => 'user',
                    'content' => $userMessage
                )
            ),
            'temperature' => 0.1,
            'max_tokens' => 1200,
            'response_format' => array('type' => 'json_object')
        );

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $debugDetails['http_status'] = $status;
        if ($raw === false || $curlErr) {
            $debugDetails['curl_error'] = $curlErr;
            aitfAiLogProviderError('Groq debug: ' . aitfAiGroqDebugSummary($debugDetails) . '; curl_error=' . $curlErr);
            throw new AitfAiProviderException('Groq request failed: ' . $curlErr, false, 0, $debugDetails);
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded) && isset($decoded['choices'][0]['finish_reason'])) {
            $debugDetails['finish_reason'] = (string) $decoded['choices'][0]['finish_reason'];
        }
        if (is_array($decoded) && isset($decoded['choices'][0]['message']['content'])) {
            $contentForDebug = trim((string) $decoded['choices'][0]['message']['content']);
            $debugDetails['response_content_exists'] = $contentForDebug !== '' ? 'yes' : 'no';
            $debugDetails['response_content_length'] = strlen($contentForDebug);
        }
        if ($status < 200 || $status >= 300) {
            $message = is_array($decoded) && isset($decoded['error']['message']) ? $decoded['error']['message'] : ('HTTP ' . $status);
            $message = aitfAiSanitizeGroqErrorMessage($message);
            $debugDetails['groq_error_type'] = is_array($decoded) && isset($decoded['error']['type']) ? $decoded['error']['type'] : '';
            $debugDetails['groq_error_message'] = $message;
            aitfAiLogProviderError('Groq debug: ' . aitfAiGroqDebugSummary($debugDetails));
            throw new AitfAiProviderException('Groq API error: ' . $message, $status === 429, $status, $debugDetails);
        }
        if (!is_array($decoded)) {
            $debugDetails['json_parse_error'] = 'Groq API envelope JSON decode failed: ' . json_last_error_msg();
            aitfAiLogProviderError('Groq debug: ' . aitfAiGroqDebugSummary($debugDetails));
            throw new AitfAiProviderException('Groq returned a non-JSON API response.', false, 0, $debugDetails);
        }
        if (!isset($decoded['choices'][0]['message']['content']) || trim((string) $decoded['choices'][0]['message']['content']) === '') {
            aitfAiLogProviderError('Groq debug: ' . aitfAiGroqDebugSummary($debugDetails));
            throw new AitfAiProviderException('Groq response did not include message content.', false, 0, $debugDetails);
        }

        $text = trim((string) $decoded['choices'][0]['message']['content']);
        $result = aitfAiDecodeJsonText($text);
        if (!$result) {
            $debugDetails['json_parse_error'] = json_last_error_msg();
            aitfAiLogProviderError('Groq debug: ' . aitfAiGroqDebugSummary($debugDetails));
            throw new AitfAiProviderException('Groq response could not be parsed as structured JSON.', false, 0, $debugDetails);
        }

        return array('result' => aitfAiNormalizeAiResult($result), 'raw' => $raw, 'model_text' => $text);
    }
}

if (!function_exists('aitfAiInsertHistory')) {
    function aitfAiInsertHistory($db, $customerId, $requestedBy, $model, $documentCount, $rulesCount, $status, $summary, $resultJson, $rawResponse, $errorMessage, $scanScope = '')
    {
        $scanScope = aitfAiNormalizeScanScope($scanScope, $documentCount);
        $stmt = $db->prepare("INSERT INTO ai_review_history (customer_id, requested_by, model_name, document_count, rules_count, scan_scope, status, summary, result_json, raw_response, error_message) VALUES (:customer_id, :requested_by, :model_name, :document_count, :rules_count, :scan_scope, :status, :summary, :result_json, :raw_response, :error_message)");
        $stmt->execute(array(
            ':customer_id' => $customerId,
            ':requested_by' => $requestedBy,
            ':model_name' => $model,
            ':document_count' => $documentCount,
            ':rules_count' => $rulesCount,
            ':scan_scope' => $scanScope,
            ':status' => $status,
            ':summary' => $summary,
            ':result_json' => $resultJson,
            ':raw_response' => $rawResponse,
            ':error_message' => $errorMessage
        ));
        return $db->lastInsertId();
    }
}

if (!function_exists('aitfAiInsertDocumentHistory')) {
    function aitfAiInsertDocumentHistory($db, $historyId, $customerId, array $documents, $requestedBy, $model, $status, $summary)
    {
        $stmt = $db->prepare("INSERT INTO ai_review_document_history
            (review_history_id, customer_id, document_key, document_name, document_label, service_type, service_id, column_name, file_path, file_hash, file_size_bytes, model_name, status, summary, analyzed_by)
            VALUES
            (:review_history_id, :customer_id, :document_key, :document_name, :document_label, :service_type, :service_id, :column_name, :file_path, :file_hash, :file_size_bytes, :model_name, :status, :summary, :analyzed_by)");

        foreach ($documents as $doc) {
            $stmt->execute(array(
                ':review_history_id' => $historyId,
                ':customer_id' => $customerId,
                ':document_key' => isset($doc['document_key']) ? $doc['document_key'] : aitfAiDocumentKey($doc),
                ':document_name' => isset($doc['document_name']) ? $doc['document_name'] : $doc['file_name'],
                ':document_label' => $doc['label'],
                ':service_type' => $doc['service_type'],
                ':service_id' => $doc['service_id'],
                ':column_name' => $doc['column_name'],
                ':file_path' => $doc['path'],
                ':file_hash' => aitfAiDocumentFileHash($doc),
                ':file_size_bytes' => $doc['size_bytes'],
                ':model_name' => $model,
                ':status' => $status,
                ':summary' => $summary,
                ':analyzed_by' => $requestedBy
            ));
        }
    }
}

if (!function_exists('aitfAiFetchDocumentReview')) {
    function aitfAiFetchDocumentReview($db, $customerId, $documentKey = '', $historyId = 0)
    {
        if ($historyId > 0) {
            $stmt = $db->prepare("SELECT dh.*, h.result_json, h.raw_response, h.error_message, h.rules_count, h.document_count
                FROM ai_review_document_history dh
                INNER JOIN ai_review_history h ON h.id = dh.review_history_id
                WHERE dh.customer_id = :customer_id AND dh.review_history_id = :history_id
                ORDER BY dh.id ASC");
            $stmt->execute(array(':customer_id' => $customerId, ':history_id' => $historyId));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) {
                return null;
            }
            return array(
                'document_history' => $rows,
                'history_id' => $rows[0]['review_history_id'],
                'model_name' => $rows[0]['model_name'],
                'status' => $rows[0]['status'],
                'summary' => $rows[0]['summary'],
                'analyzed_by' => $rows[0]['analyzed_by'],
                'analyzed_at' => $rows[0]['analyzed_at'],
                'result_json' => $rows[0]['result_json'],
                'raw_response' => $rows[0]['raw_response'],
                'error_message' => $rows[0]['error_message'],
                'rules_count' => $rows[0]['rules_count'],
                'document_count' => $rows[0]['document_count']
            );
        }

        if ($documentKey === '') {
            return null;
        }

        $stmt = $db->prepare("SELECT dh.*, h.result_json, h.raw_response, h.error_message, h.rules_count, h.document_count
            FROM ai_review_document_history dh
            INNER JOIN ai_review_history h ON h.id = dh.review_history_id
            WHERE dh.customer_id = :customer_id AND dh.document_key = :document_key
            ORDER BY dh.id DESC
            LIMIT 1");
        $stmt->execute(array(':customer_id' => $customerId, ':document_key' => $documentKey));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return aitfAiFetchDocumentReview($db, $customerId, '', (int) $row['review_history_id']);
    }
}

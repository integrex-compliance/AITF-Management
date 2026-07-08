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
        $stmt = $db->prepare("SELECT id, customer_id, requested_by, model_name, document_count, rules_count, status, summary, result_json, error_message, created_at FROM ai_review_history WHERE customer_id = :customer_id ORDER BY id DESC LIMIT 1");
        $stmt->execute(array(':customer_id' => $customerId));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('aitfAiBuildPrompt')) {
    function aitfAiBuildPrompt($context, $documents, $rules)
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

        $payload = array(
            'client_context' => $context,
            'active_ai_rules_from_database' => $rules,
            'documents_sent' => $docList
        );

        return "You are AI Powered by AITF, acting as a certification pre-audit document reviewer.\n"
            . "Read the attached PDF files directly using your own document understanding and reasoning. Do not rely only on file names or database metadata.\n"
            . "Independently detect potential inconsistencies, contradictions, missing information, suspicious values, incorrect-looking information, date issues, chronology problems, client-detail mismatches, scope/standard/manday risks, and any other documentation risk visible in the PDFs or client context.\n"
            . "The active rules supplied in active_ai_rules_from_database are additional mandatory AITF checks. Apply every active rule where relevant, but do not limit your review to those rules.\n"
            . "If multiple PDFs are attached, analyze them together and cross-check names, addresses, GSTIN, contacts, ISO standards, scope, dates, mandays, service stage, certificate details, audit evidence, and other repeated facts across documents.\n"
            . "Use direct evidence from the documents and client_context. Do not invent expected values, dates, standards, mandays, or client details. If a value cannot be confirmed, write \"Manual verification required\".\n"
            . "Return strict JSON only. Do not wrap in markdown.\n"
            . "JSON shape: {\"overall_status\":\"pass|review_required|manual_verification_required\",\"summary\":\"string\",\"findings\":[{\"severity\":\"critical|major|minor|info\",\"check_area\":\"string\",\"finding\":\"string\",\"evidence\":\"string\",\"current_value\":\"string\",\"expected_value\":\"string\",\"reason\":\"string\",\"rule_reference\":\"string\",\"affected_documents\":[\"string\"],\"confidence\":0.0,\"suggestion\":\"string\",\"recommended_action\":\"string\"}],\"issues\":[{\"severity\":\"critical|major|minor|info\",\"check_area\":\"string\",\"finding\":\"string\",\"evidence\":\"string\",\"current_value\":\"string\",\"expected_value\":\"string\",\"reason\":\"string\",\"rule_reference\":\"string\",\"affected_documents\":[\"string\"],\"confidence\":0.0,\"suggestion\":\"string\",\"recommended_action\":\"string\"}],\"manual_verifications\":[{\"check_area\":\"string\",\"reason\":\"string\",\"evidence\":\"string\",\"affected_documents\":[\"string\"]}],\"documents_reviewed\":[\"string\"]}.\n"
            . "Use rule_reference as the database rule_code/title when an issue is rule-based; otherwise use \"Document evidence\" or \"Manual verification required\".\n\n"
            . json_encode($payload);
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
            throw new Exception('Gemini request failed: ' . $curlErr);
        }

        $decoded = json_decode($raw, true);
        if ($status < 200 || $status >= 300) {
            $message = isset($decoded['error']['message']) ? $decoded['error']['message'] : ('HTTP ' . $status);
            throw new Exception('Gemini API error: ' . $message);
        }
        if (!is_array($decoded)) {
            throw new Exception('Gemini returned a non-JSON API response.');
        }

        $text = aitfAiExtractGeminiText($decoded);
        $result = aitfAiDecodeJsonText($text);
        if (!$result) {
            throw new Exception('Gemini response could not be parsed as structured JSON.');
        }

        return array('result' => $result, 'raw' => $raw, 'model_text' => $text);
    }
}

if (!function_exists('aitfAiInsertHistory')) {
    function aitfAiInsertHistory($db, $customerId, $requestedBy, $model, $documentCount, $rulesCount, $status, $summary, $resultJson, $rawResponse, $errorMessage)
    {
        $stmt = $db->prepare("INSERT INTO ai_review_history (customer_id, requested_by, model_name, document_count, rules_count, status, summary, result_json, raw_response, error_message) VALUES (:customer_id, :requested_by, :model_name, :document_count, :rules_count, :status, :summary, :result_json, :raw_response, :error_message)");
        $stmt->execute(array(
            ':customer_id' => $customerId,
            ':requested_by' => $requestedBy,
            ':model_name' => $model,
            ':document_count' => $documentCount,
            ':rules_count' => $rulesCount,
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

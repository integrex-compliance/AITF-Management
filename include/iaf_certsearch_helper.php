<?php

function iafCertSearchFirstValue(array $data, array $keys, $default = '')
{
    foreach ($keys as $key) {
        if (isset($data[$key]) && trim((string) $data[$key]) !== '') {
            return trim((string) $data[$key]);
        }
    }

    return $default;
}

function iafCertSearchFormatDate($dateValue)
{
    $dateValue = trim((string) $dateValue);
    if ($dateValue === '') {
        return '';
    }

    $digitsOnly = preg_replace('/\D+/', '', $dateValue);
    if (strlen($digitsOnly) === 8 && preg_match('/^\d{8}$/', $digitsOnly)) {
        return $digitsOnly;
    }

    $timestamp = strtotime($dateValue);
    return $timestamp ? date('Ymd', $timestamp) : '';
}

function iafCertSearchSchemeNameFromStandard($standardName)
{
    $standard = strtoupper((string) $standardName);

    if (strpos($standard, '9001') !== false) {
        return 'Quality Management Systems';
    }
    if (strpos($standard, '14001') !== false) {
        return 'Environmental Management Systems';
    }
    if (strpos($standard, '45001') !== false || strpos($standard, '18001') !== false) {
        return 'Occupational Health and Safety Management Systems';
    }
    if (strpos($standard, '27001') !== false) {
        return 'Information Security Management Systems';
    }
    if (strpos($standard, '22000') !== false) {
        return 'Food Safety Management Systems';
    }

    return 'Management Systems';
}

function iafCertSearchNormalizeSchemeName($schemeName, $standardName = '')
{
    $scheme = trim((string) $schemeName);
    $upper = strtoupper($scheme);

    $knownSchemes = [
        'QMS' => 'Quality Management Systems',
        'EMS' => 'Environmental Management Systems',
        'OHSMS' => 'Occupational Health and Safety Management Systems',
        'OH&S' => 'Occupational Health and Safety Management Systems',
        'ISMS' => 'Information Security Management Systems',
        'FSMS' => 'Food Safety Management Systems',
    ];

    if (isset($knownSchemes[$upper])) {
        return $knownSchemes[$upper];
    }

    return $scheme !== '' ? $scheme : iafCertSearchSchemeNameFromStandard($standardName);
}

function iafCertSearchNormalizeAccreditationBodyName($name)
{
    $name = trim((string) $name);
    if ($name === '' || strtoupper($name) === 'NABCB') {
        return 'National Accreditation Board for Certification Bodies';
    }

    return $name;
}

function iafCertSearchNormalizeAccreditationBodyAcronym($name)
{
    $name = trim((string) $name);
    if ($name === '' || stripos($name, 'National Accreditation Board for Certification Bodies') !== false) {
        return 'NABCB';
    }

    return $name;
}

function iafCertSearchNormalizeClientData(array $data)
{
    if (!isset($data['customer_guid']) || trim((string) $data['customer_guid']) === '') {
        $data['customer_guid'] = iafCertSearchFirstValue($data, ['unique_id', 'aggrement_no', 'agreement_no', 'allotment_no', 'allotmentNo']);
    }

    if (!isset($data['country']) || trim((string) $data['country']) === '') {
        $data['country'] = 'India';
    }

    if (!isset($data['company_country']) || trim((string) $data['company_country']) === '') {
        $data['company_country'] = $data['country'];
    }

    if (!isset($data['schema_name']) || trim((string) $data['schema_name']) === '') {
        $data['schema_name'] = iafCertSearchSchemeNameFromStandard($data['standard_name'] ?? '');
    }

    if (!isset($data['scheme_name']) || trim((string) $data['scheme_name']) === '') {
        $data['scheme_name'] = $data['schema_name'];
    }

    if (!isset($data['accreditation_body_name']) || trim((string) $data['accreditation_body_name']) === '') {
        $data['accreditation_body_name'] = iafCertSearchFirstValue($data, ['accreditation_body', 'accreditation_requirement', 'accereditation_requirement', 'review_accredition']);
    }

    if (!isset($data['certificate_issue_date']) || trim((string) $data['certificate_issue_date']) === '') {
        $data['certificate_issue_date'] = iafCertSearchFirstValue($data, ['issue_date', 'original_issue_date']);
    }

    if (!isset($data['expire_date']) || trim((string) $data['expire_date']) === '') {
        $data['expire_date'] = iafCertSearchFirstValue($data, ['expiry_date']);
    }

    $data['certification_type'] = 'Management System';
    $data['certificate_accreditation_status'] = 'Accredited';

    return $data;
}

function iafCertSearchBuildPayload(array $data)
{
    $data = iafCertSearchNormalizeClientData($data);

    $entityName = iafCertSearchFirstValue($data, ['customer_name']);
    $streetAddress = iafCertSearchFirstValue($data, ['certified_entity_street_address', 'address']);
    $companyCountry = iafCertSearchFirstValue($data, ['company_country', 'country'], 'India');
    $certScope = iafCertSearchFirstValue($data, ['certification_scope', 'proposed_scope', 'certificate_scope', 'scope']);
    $standardName = iafCertSearchFirstValue($data, ['standard_name', 'assessment_standard']);
    $schemeName = iafCertSearchNormalizeSchemeName(iafCertSearchFirstValue($data, ['scheme_name', 'schema_name']), $standardName);
    $accreditationBody = iafCertSearchNormalizeAccreditationBodyName(iafCertSearchFirstValue($data, ['accreditation_body_name', 'accreditation_body', 'accreditation_requirement', 'accereditation_requirement', 'review_accredition'], 'NABCB'));
    $accreditationBodyAcronym = iafCertSearchNormalizeAccreditationBodyAcronym(iafCertSearchFirstValue($data, ['accreditation_body_acronym_name', 'accreditation_body_acronym', 'accreditation_body'], 'NABCB'));
    $status = iafCertSearchNormalizeStatus(iafCertSearchFirstValue($data, ['certification_status'], 'Active'));

    return [
        'certificate_number' => iafCertSearchFirstValue($data, ['certificate_number', 'certificate_no']),
        'certificate_identity_number' => iafCertSearchFirstValue($data, ['certificate_tracking_id', 'certificate_identity_number']),
        'certification_status' => $status,
        'certificate_accreditation_status' => 'Accredited',
        'certification_type' => 'Management System',
        'certification_scope' => $certScope,
        'certification_original_issue_date' => iafCertSearchFormatDate(iafCertSearchFirstValue($data, ['certification_original_issue_date', 'original_issue_date', 'certificate_issue_date', 'issue_date'])),
        'certification_issue_date' => iafCertSearchFormatDate(iafCertSearchFirstValue($data, ['certification_issue_date', 'issue_date', 'certificate_issue_date'])),
        'certification_expiry_date' => iafCertSearchFormatDate(iafCertSearchFirstValue($data, ['certification_expiry_date', 'expire_date', 'expiry_date'])),
        'certified_entity_name' => $entityName,
        'certified_entity_unique_id' => iafCertSearchFirstValue($data, ['customer_guid', 'unique_id', 'aggrement_no', 'agreement_no', 'allotment_no', 'allotmentNo']),
        'certified_entity_street_address' => $streetAddress,
        'certified_entity_country' => $companyCountry,
        'accreditation_body_name' => $accreditationBody,
        'accreditation_body_acronym_name' => $accreditationBodyAcronym,
        'schemes' => [
            [
                'scheme_name' => $schemeName,
                'standard_list' => [
                    [
                        'standards_name' => $standardName,
                    ],
                ],
            ],
        ],
        'sites' => [
            [
                'street' => $streetAddress,
                'country' => $companyCountry,
                'scope_description' => $certScope,
            ],
        ],
    ];
}

function iafCertSearchNormalizeStatus($status)
{
    $normalized = strtolower(trim((string) $status));

    if ($normalized === 'granted' || $normalized === 'completed' || $normalized === 'valid' || $normalized === 'issued') {
        return 'Active';
    }
    if (strpos($normalized, 'suspend') !== false) {
        return 'Suspended';
    }
    if (strpos($normalized, 'withdraw') !== false) {
        return 'Withdrawn';
    }
    if (strpos($normalized, 'cancel') !== false || strpos($normalized, 'terminated') !== false) {
        return 'Cancelled';
    }
    if (strpos($normalized, 'expir') !== false) {
        return 'Expired';
    }
    if (strpos($normalized, 'pending') !== false || strpos($normalized, 'process') !== false) {
        return 'Pending';
    }

    return trim((string) $status) !== '' ? trim((string) $status) : 'Active';
}

function iafCertSearchValidatePayload(array $payload)
{
    $requiredPaths = [
        'certificate_number',
        'certification_status',
        'certificate_accreditation_status',
        'certification_type',
        'certification_scope',
        'certification_issue_date',
        'certification_expiry_date',
        'certified_entity_name',
        'certified_entity_street_address',
        'certified_entity_country',
        'accreditation_body_acronym_name',
        'schemes.0.scheme_name',
        'schemes.0.standard_list.0.standards_name',
    ];

    $missing = [];
    foreach ($requiredPaths as $path) {
        $cursor = $payload;
        foreach (explode('.', $path) as $part) {
            if (is_array($cursor) && array_key_exists($part, $cursor)) {
                $cursor = $cursor[$part];
            } else {
                $cursor = null;
                break;
            }
        }

        if ($cursor === null || trim((string) $cursor) === '') {
            $missing[] = $path;
        }
    }

    return $missing;
}

function iafCertSearchLoadEnv($path = null)
{
    $path = $path ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ($name === '') {
            continue;
        }

        if (
            (strlen($value) >= 2)
            && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

function iafCertSearchEnv($key, $default = '')
{
    $value = getenv($key);
    if ($value === false && isset($_ENV[$key])) {
        $value = $_ENV[$key];
    }
    if ($value === false && isset($_SERVER[$key])) {
        $value = $_SERVER[$key];
    }

    $value = trim((string) $value);
    return $value === '' ? $default : $value;
}

function iafCertSearchGetConfig()
{
    iafCertSearchLoadEnv();

    return [
        'api_key' => iafCertSearchEnv('IAF_API_KEY'),
        'base_url' => rtrim(iafCertSearchEnv('IAF_API_BASE_URL'), '/'),
        'endpoint_path' => '/' . ltrim(iafCertSearchEnv('IAF_API_UPLOAD_CERT_PATH', '/cb/upload-cert'), '/'),
        'timeout' => (int) iafCertSearchEnv('IAF_API_TIMEOUT', '30'),
        'connect_timeout' => (int) iafCertSearchEnv('IAF_API_CONNECT_TIMEOUT', '10'),
    ];
}

function iafCertSearchSendPayload(array $payload, $action = 'insert')
{
    $config = iafCertSearchGetConfig();

    if ($config['api_key'] === '') {
        return [
            'success' => false,
            'http_code' => 0,
            'response' => null,
            'raw_response' => '',
            'curl_error' => '',
            'error_message' => 'IAF_API_KEY is missing from .env.',
            'iaf_record_id' => '',
            'url' => $config['base_url'],
        ];
    }

    if ($config['base_url'] === '' || !filter_var($config['base_url'], FILTER_VALIDATE_URL)) {
        return [
            'success' => false,
            'http_code' => 0,
            'response' => null,
            'raw_response' => '',
            'curl_error' => '',
            'error_message' => 'IAF_API_BASE_URL is missing or invalid in .env.',
            'iaf_record_id' => '',
            'url' => $config['base_url'],
        ];
    }

    if (!function_exists('curl_init')) {
        return [
            'success' => false,
            'http_code' => 0,
            'response' => null,
            'raw_response' => '',
            'curl_error' => '',
            'error_message' => 'PHP cURL extension is not enabled.',
            'iaf_record_id' => '',
            'url' => $config['base_url'],
        ];
    }

    $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($jsonPayload === false) {
        return [
            'success' => false,
            'http_code' => 0,
            'response' => null,
            'raw_response' => '',
            'curl_error' => '',
            'error_message' => 'Unable to encode IAF payload as JSON: ' . json_last_error_msg(),
            'iaf_record_id' => '',
            'url' => $config['base_url'],
        ];
    }

    $url = iafCertSearchBuildUrl($config['base_url'], $config['endpoint_path']);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_HTTPHEADER => [
            'x-http-authorization: ' . $config['api_key'],
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT => max(1, $config['timeout']),
        CURLOPT_CONNECTTIMEOUT => max(1, $config['connect_timeout']),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $rawResponse = curl_exec($ch);
    $curlError = curl_errno($ch) ? curl_error($ch) : '';
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decodedResponse = null;
    if (is_string($rawResponse) && trim($rawResponse) !== '') {
        $decoded = json_decode($rawResponse, true);
        $decodedResponse = json_last_error() === JSON_ERROR_NONE ? $decoded : $rawResponse;
    }

    $errorMessage = iafCertSearchExtractErrorMessage($decodedResponse, $curlError, $httpCode);
    $success = $curlError === '' && $httpCode >= 200 && $httpCode < 300;

    return [
        'success' => $success,
        'http_code' => $httpCode,
        'response' => $decodedResponse,
        'raw_response' => is_string($rawResponse) ? $rawResponse : '',
        'curl_error' => $curlError,
        'error_message' => $success ? '' : $errorMessage,
        'iaf_record_id' => iafCertSearchExtractRecordId($decodedResponse),
        'url' => $url,
    ];
}

function iafCertSearchBuildUrl($baseUrl, $endpointPath)
{
    $baseUrl = rtrim((string) $baseUrl, '/');
    $endpointPath = '/' . ltrim((string) $endpointPath, '/');

    if (preg_match('#/cb/upload-cert$#', $baseUrl)) {
        return $baseUrl;
    }

    return $baseUrl . $endpointPath;
}

function iafCertSearchExtractRecordId($response)
{
    if (!is_array($response)) {
        return '';
    }

    $paths = [
        ['iaf_record_id'],
        ['record_id'],
        ['recordId'],
        ['id'],
        ['data', 'iaf_record_id'],
        ['data', 'record_id'],
        ['data', 'recordId'],
        ['data', 'id'],
        ['certification', 'id'],
        ['certification', 'record_id'],
    ];

    foreach ($paths as $path) {
        $cursor = $response;
        foreach ($path as $part) {
            if (is_array($cursor) && array_key_exists($part, $cursor)) {
                $cursor = $cursor[$part];
            } else {
                $cursor = null;
                break;
            }
        }
        if ($cursor !== null && trim((string) $cursor) !== '') {
            return trim((string) $cursor);
        }
    }

    return '';
}

function iafCertSearchExtractErrorMessage($response, $curlError, $httpCode)
{
    if ($curlError !== '') {
        return $curlError;
    }

    if (is_array($response)) {
        foreach (['message', 'error_message', 'detail'] as $key) {
            if (isset($response[$key]) && !is_bool($response[$key]) && trim((string) $response[$key]) !== '') {
                return trim((string) $response[$key]);
            }
        }

        if (isset($response['errors'])) {
            if (is_array($response['errors'])) {
                foreach (['message', 'detail', 'error', 'error_message', 'code'] as $key) {
                    if (isset($response['errors'][$key]) && !is_bool($response['errors'][$key]) && trim((string) $response['errors'][$key]) !== '') {
                        return trim((string) $response['errors'][$key]);
                    }
                }

                return json_encode($response['errors'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            return trim((string) $response['errors']);
        }

        if (isset($response['error']) && !is_bool($response['error']) && trim((string) $response['error']) !== '') {
            return trim((string) $response['error']);
        }
    }

    if (is_string($response) && trim($response) !== '') {
        return trim($response);
    }

    return $httpCode > 0 ? 'IAF API returned HTTP status ' . $httpCode . '.' : 'IAF API request failed.';
}

function iafCertSearchEnsureSyncTable(PDO $db)
{
    $db->exec("
        CREATE TABLE IF NOT EXISTS iaf_certsearch_sync (
            id INT NOT NULL AUTO_INCREMENT,
            client_id INT NOT NULL,
            action VARCHAR(20) NOT NULL,
            certificate_number VARCHAR(191) DEFAULT NULL,
            iaf_record_id VARCHAR(191) DEFAULT NULL,
            sync_status VARCHAR(30) NOT NULL,
            http_status INT DEFAULT NULL,
            error_message TEXT DEFAULT NULL,
            request_payload LONGTEXT NOT NULL,
            response_payload LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_iaf_client_action (client_id, action)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
}

function iafCertSearchSaveSyncResult(PDO $db, $clientId, $action, array $payload, array $apiResult)
{
    iafCertSearchEnsureSyncTable($db);

    $syncStatus = $apiResult['success'] ? 'synced' : 'failed';
    $responsePayload = $apiResult['response'];
    if ($responsePayload === null && $apiResult['raw_response'] !== '') {
        $responsePayload = $apiResult['raw_response'];
    }

    $stmt = $db->prepare("
        INSERT INTO iaf_certsearch_sync (
            client_id,
            action,
            certificate_number,
            iaf_record_id,
            sync_status,
            http_status,
            error_message,
            request_payload,
            response_payload,
            created_at,
            updated_at
        ) VALUES (
            :client_id,
            :action,
            :certificate_number,
            :iaf_record_id,
            :sync_status,
            :http_status,
            :error_message,
            :request_payload,
            :response_payload,
            NOW(),
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            certificate_number = VALUES(certificate_number),
            iaf_record_id = VALUES(iaf_record_id),
            sync_status = VALUES(sync_status),
            http_status = VALUES(http_status),
            error_message = VALUES(error_message),
            request_payload = VALUES(request_payload),
            response_payload = VALUES(response_payload),
            updated_at = NOW()
    ");

    $stmt->execute([
        ':client_id' => (int) $clientId,
        ':action' => $action,
        ':certificate_number' => iafCertSearchFirstValue($payload, ['certificate_number']),
        ':iaf_record_id' => $apiResult['iaf_record_id'],
        ':sync_status' => $syncStatus,
        ':http_status' => $apiResult['http_code'],
        ':error_message' => $apiResult['error_message'],
        ':request_payload' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ':response_payload' => json_encode($responsePayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);
}

function iafCertSearchLogApiCall($action, $method, $url, array $payload, $httpCode, $response, $curlError = '')
{
    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }

    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'method' => $method,
        'url' => $url,
        'request_payload' => $payload,
        'http_code' => $httpCode,
        'response' => $response,
        'curl_error' => $curlError,
    ];

    file_put_contents(
        $logDir . DIRECTORY_SEPARATOR . 'iaf_certsearch_api.log',
        json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL . str_repeat('-', 80) . PHP_EOL,
        FILE_APPEND
    );
}

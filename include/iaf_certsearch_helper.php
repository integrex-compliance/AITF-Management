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
        return 'QMS';
    }
    if (strpos($standard, '14001') !== false) {
        return 'EMS';
    }
    if (strpos($standard, '45001') !== false || strpos($standard, '18001') !== false) {
        return 'OHSMS';
    }
    if (strpos($standard, '27001') !== false) {
        return 'ISMS';
    }
    if (strpos($standard, '22000') !== false) {
        return 'FSMS';
    }

    return 'Other';
}

function iafCertSearchNormalizeClientData(array $data)
{
    if (!isset($data['customer_guid']) || trim((string) $data['customer_guid']) === '') {
        $data['customer_guid'] = isset($data['internal_client_id'])
            ? 'GUID-' . str_pad((string) $data['internal_client_id'], 6, '0', STR_PAD_LEFT)
            : '';
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

    $data['certification_type'] = 'Management System';
    $data['certificate_accreditation_status'] = 'Accredited';

    return $data;
}

function iafCertSearchBuildPayload(array $data)
{
    $data = iafCertSearchNormalizeClientData($data);

    $entityUniqueId = iafCertSearchFirstValue($data, ['customer_guid', 'internal_client_id']);
    $entityName = iafCertSearchFirstValue($data, ['customer_name']);
    $streetAddress = iafCertSearchFirstValue($data, ['certified_entity_street_address', 'address']);
    $companyCountry = iafCertSearchFirstValue($data, ['company_country', 'country'], 'India');
    $certScope = iafCertSearchFirstValue($data, ['certification_scope', 'certificate_scope', 'scope']);
    $standardName = iafCertSearchFirstValue($data, ['standard_name']);
    $schemeName = iafCertSearchFirstValue($data, ['schema_name', 'scheme_name'], iafCertSearchSchemeNameFromStandard($standardName));

    return [
        'certified_entity' => [
            'certified_entity_unique_id' => $entityUniqueId,
            'certified_entity_name' => $entityName,
            'certified_entity_street_address' => $streetAddress,
            'company_country' => $companyCountry,
            'business_registered_country' => $companyCountry,
            'business_registered_full_address' => $streetAddress,
        ],
        'certification' => [
            'certificate_identity_number' => iafCertSearchFirstValue($data, ['certificate_tracking_id', 'certificate_identity_number']),
            'certificate_number' => iafCertSearchFirstValue($data, ['certificate_number', 'certificate_no']),
            'certification_status' => iafCertSearchFirstValue($data, ['certification_status'], 'Active'),
            'certificate_accreditation_status' => 'Accredited',
            'certification_type' => 'Management System',
            'certification_original_issue_date' => iafCertSearchFormatDate(iafCertSearchFirstValue($data, ['certification_original_issue_date', 'original_issue_date', 'issue_date'])),
            'certification_issue_date' => iafCertSearchFormatDate(iafCertSearchFirstValue($data, ['certification_issue_date', 'issue_date'])),
            'certification_expiry_date' => iafCertSearchFormatDate(iafCertSearchFirstValue($data, ['certification_expiry_date', 'expiry_date', 'expire_date'])),
            'certification_scope' => $certScope,
            'certified_entity_unique_id' => $entityUniqueId,
            'main_site_country' => $companyCountry,
            'main_site_full_address' => $streetAddress,
            'accreditation_body_name' => iafCertSearchFirstValue($data, ['accreditation_body_name'], 'YOUR_AB_NAME'),
            'accreditation_body_acronym_name' => iafCertSearchFirstValue($data, ['accreditation_body_acronym_name'], 'YOUR_AB_ACRONYM'),
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
        ],
    ];
}

function iafCertSearchValidatePayload(array $payload)
{
    $requiredPaths = [
        'certified_entity.certified_entity_unique_id',
        'certified_entity.certified_entity_name',
        'certified_entity.certified_entity_street_address',
        'certified_entity.company_country',
        'certification.certificate_number',
        'certification.certification_issue_date',
        'certification.certification_expiry_date',
        'certification.certification_scope',
        'certification.schemes.0.scheme_name',
        'certification.schemes.0.standard_list.0.standards_name',
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

<?php
// iaf_operations.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$jsonPayload = file_get_contents('php://input');
$request = json_decode($jsonPayload, true);

if (!$request) {
    echo json_encode(['error' => true, 'message' => 'Invalid JSON payload.']);
    exit;
}

$apiKey = 'YOUR_API_KEY_HERE'; // Replace with your API Key
$action = $request['action'] ?? '';

$data = $request['data'] ?? [];

function makeIafApiCall($method, $url, $payload, $apiKey)
{
    $curl = curl_init();
    $curlOptions = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-http-authorization: ' . $apiKey
        ],
    ];

    if (!empty($payload) && in_array($method, ['POST', 'PUT'])) {
        $curlOptions[CURLOPT_POSTFIELDS] = json_encode($payload);
    }

    curl_setopt_array($curl, $curlOptions);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return ['error' => true, 'message' => "cURL Error: $err"];
    }

    $decoded = json_decode($response, true);

    // Fallback if not JSON
    if ($decoded === null) {
        return ['error' => true, 'message' => "Raw Response: " . substr(strip_tags($response), 0, 150)];
    }

    // Normalizing API Error structures (e.g. {"error": "Unauthorized"} lacks 'message')
    if (isset($decoded['error']) && !is_bool($decoded['error']) && !isset($decoded['message'])) {
        $decoded['message'] = is_string($decoded['error']) ? $decoded['error'] : json_encode($decoded['error']);
        $decoded['error'] = true;
    }

    // Explicit API response errors where 'message' might be inside something else
    if (!isset($decoded['message']) && curl_getinfo($curl, CURLINFO_HTTP_CODE) >= 400) {
        $decoded['error'] = true;
        $decoded['message'] = $decoded['Message'] ?? $decoded['error_description'] ?? json_encode($decoded);
    }

    return $decoded;
}

switch ($action) {
    case 'insert':
        $issueDate = str_replace('-', '', $data['issue_date']);
        $expiryDate = str_replace('-', '', $data['expiry_date']);

        $iafPayload = [
            "certified_entity" => [
                "certified_entity_unique_id" => (string)$data['internal_client_id'],
                "certified_entity_name" => $data['customer_name'],
                "business_registered_country" => $data['country'],
                "business_registered_full_address" => $data['address']
            ],
            "certification" => [
                "certificate_identity_number" => (string)$data['certificate_tracking_id'],
                "certificate_number" => (string)$data['certificate_number'],
                "certification_status" => $data['certification_status'],
                "certificate_accreditation_status" => $data['certification_type'],
                "certification_type" => "Management System",
                "certification_issue_date" => $issueDate,
                "certification_expiry_date" => $expiryDate,
                "certified_entity_unique_id" => (string)$data['internal_client_id'],
                "main_site_country" => $data['country'],
                "main_site_full_address" => $data['address'],
                "accreditation_body_name" => "YOUR_AB_NAME", // MUST UPDATE
                "accreditation_body_acronym_name" => "YOUR_AB_ACRONYM", // MUST UPDATE
                "schemes" => [
                    [
                        "scheme_name" => $data['schema_name'],
                        "standard_list" => [["standards_name" => $data['standard_name']]]
                    ]
                ]
            ]
        ];


        $url = 'https://api.sandbox.iafcertsearch.org/api/client/v2/cb/upload-cert';
        echo json_encode(makeIafApiCall('POST', $url, $iafPayload, $apiKey));
        break;

    case 'update':
        if (empty($data['customer_guid'])) {
            echo json_encode(['error' => true, 'message' => 'GUID required for update.']);
            exit;
        }
        $iafPayload = [
            "company_guid" => $data['customer_guid'],
            "company_name" => $data['customer_name']
        ];
        $url = 'https://api.sandbox.iafcertsearch.org/';
        echo json_encode(makeIafApiCall('PUT', $url, $iafPayload, $apiKey));
        break;

    case 'delete':
        $certGuid = $data['certification_guid'] ?? $data['customer_guid'] ?? '';
        if (empty($certGuid)) {
            echo json_encode(['error' => true, 'message' => 'Missing Certification GUID for deletion.']);
            exit;
        }
        $url = 'https://api.iafcertsearch.org/api/client/v2/cb/certification/' . $certGuid;
        echo json_encode(makeIafApiCall('DELETE', $url, [], $apiKey));
        break;

    default:
        echo json_encode(['error' => true, 'message' => 'Invalid action for operations script.']);
        break;
}

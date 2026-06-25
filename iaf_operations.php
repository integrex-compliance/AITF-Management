<?php
// iaf_operations.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once __DIR__ . '/include/iaf_certsearch_helper.php';

$jsonPayload = file_get_contents('php://input');
$request = json_decode($jsonPayload, true);

if (!$request) {
    echo json_encode(['error' => true, 'message' => 'Invalid JSON payload.']);
    exit;
}

$apiKey = getenv('IAF_CERTSEARCH_API_KEY') ?: 'YOUR_API_KEY_HERE'; // Replace with your API Key or set env var
$action = $request['action'] ?? '';

$data = $request['data'] ?? [];

function makeIafApiCall($method, $url, $payload, $apiKey, $action = '')
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
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $decoded = json_decode($response, true);
    $responseForLog = $decoded === null ? $response : $decoded;
    iafCertSearchLogApiCall($action, $method, $url, (array) $payload, $httpCode, $responseForLog, $err);

    if ($err) {
        return [
            'error' => true,
            'message' => "cURL Error: $err",
            'http_code' => $httpCode,
            'request_payload' => $payload,
            'response' => $responseForLog
        ];
    }

    // Fallback if not JSON
    if ($decoded === null) {
        return [
            'error' => true,
            'message' => "Raw Response: " . substr(strip_tags($response), 0, 150),
            'http_code' => $httpCode,
            'request_payload' => $payload,
            'response' => $response
        ];
    }

    // Normalizing API Error structures (e.g. {"error": "Unauthorized"} lacks 'message')
    if (isset($decoded['error']) && !is_bool($decoded['error']) && !isset($decoded['message'])) {
        $decoded['message'] = is_string($decoded['error']) ? $decoded['error'] : json_encode($decoded['error']);
        $decoded['error'] = true;
    }

    // Explicit API response errors where 'message' might be inside something else
    if (!isset($decoded['message']) && $httpCode >= 400) {
        $decoded['error'] = true;
        $decoded['message'] = $decoded['Message'] ?? $decoded['error_description'] ?? json_encode($decoded);
    }

    if ($httpCode >= 400) {
        $decoded['error'] = true;
    }

    $decoded['http_code'] = $httpCode;
    $decoded['request_payload'] = $payload;
    $decoded['response'] = $responseForLog;

    return $decoded;
}

switch ($action) {
    case 'insert':
        $iafPayload = iafCertSearchBuildPayload($data);
        $missingFields = iafCertSearchValidatePayload($iafPayload);
        if (!empty($missingFields)) {
            $validationResponse = [
                'error' => true,
                'message' => 'Missing required IAF CertSearch payload fields.',
                'missing_fields' => $missingFields,
                'request_payload' => $iafPayload
            ];
            iafCertSearchLogApiCall($action, 'POST', 'validation', $iafPayload, 0, $validationResponse);
            echo json_encode($validationResponse);
            exit;
        }


        $url = 'https://api.sandbox.iafcertsearch.org/api/client/v2/cb/upload-cert';
        echo json_encode(makeIafApiCall('POST', $url, $iafPayload, $apiKey, $action));
        break;

    case 'update':
        if (empty($data['customer_guid'])) {
            echo json_encode(['error' => true, 'message' => 'GUID required for update.']);
            exit;
        }
        $iafPayload = iafCertSearchBuildPayload($data);
        $missingFields = iafCertSearchValidatePayload($iafPayload);
        if (!empty($missingFields)) {
            $validationResponse = [
                'error' => true,
                'message' => 'Missing required IAF CertSearch payload fields.',
                'missing_fields' => $missingFields,
                'request_payload' => $iafPayload
            ];
            iafCertSearchLogApiCall($action, 'PUT', 'validation', $iafPayload, 0, $validationResponse);
            echo json_encode($validationResponse);
            exit;
        }

        $url = 'https://api.sandbox.iafcertsearch.org/api/client/v2/cb/upload-cert';
        echo json_encode(makeIafApiCall('PUT', $url, $iafPayload, $apiKey, $action));
        break;

    case 'delete':
        $certGuid = $data['certification_guid'] ?? $data['customer_guid'] ?? '';
        if (empty($certGuid)) {
            echo json_encode(['error' => true, 'message' => 'Missing Certification GUID for deletion.']);
            exit;
        }
        $url = 'https://api.iafcertsearch.org/api/client/v2/cb/certification/' . $certGuid;
        echo json_encode(makeIafApiCall('DELETE', $url, [], $apiKey, $action));
        break;

    default:
        echo json_encode(['error' => true, 'message' => 'Invalid action for operations script.']);
        break;
}

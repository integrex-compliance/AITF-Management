<?php
// iaf_sync.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$apiKey = 'YOUR_API_KEY_HERE'; // Replace with your API Key

function makeIafApiCall($method, $url, $payload, $apiKey)
{
    $curl = curl_init();
    $curlOptions = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60, // Longer timeout for fetching all records
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-http-authorization: ' . $apiKey
        ],
    ];

    curl_setopt_array($curl, $curlOptions);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    return $err ? ['error' => true, 'message' => "cURL Error: $err"] : json_decode($response, true);
}

$url = 'https://sandboxapi.iafcertsearch.org/api/client/v2/cb/cert';
$iafResponse = makeIafApiCall('GET', $url, [], $apiKey);

if (!isset($iafResponse['error']) && isset($iafResponse['data'])) {
    $certifications = $iafResponse['data'];
    $syncedCount = 0;

    try {
        // --- DATABASE CONFIGURATION ---
        $host = 'localhost';
        $db = 'your_database_name';
        $user = 'your_database_user';
        $pass = 'your_database_password';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);

        // Prepare the update statement
        // UPDATE table_name SET company_guid = :company_guid WHERE certificate_number = :cert_number
        $stmt = $pdo->prepare("UPDATE your_clients_table 
                               SET customer_guid = :company_guid 
                               WHERE certificate_number = :cert_number");

        foreach ($certifications as $cert) {
            $iafCompanyGuid = $cert['company_guid'] ?? null;
            $certificateNumber = $cert['certificate_number'] ?? null;

            if ($certificateNumber && $iafCompanyGuid) {
                $stmt->execute([
                    ':company_guid' => $iafCompanyGuid,
                    ':cert_number' => $certificateNumber
                ]);
                $syncedCount += $stmt->rowCount();
            }
        }

        $iafResponse['sync_summary'] = "Successfully updated $syncedCount records in the local database.";

    }
    catch (\PDOException $e) {
        $iafResponse['db_error'] = "Database Sync Failed: " . $e->getMessage();
    }
}

echo json_encode($iafResponse);

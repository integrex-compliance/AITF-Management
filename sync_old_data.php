<!-- this script is for sync old customer and this is bulk sync script  -->

 
<?php
// Script execution timeout removed to ensure large datasets can process
set_time_limit(0);
// Display errors for debugging during execution
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once './dbConnect.php';
header('Content-Type: application/json');

// local api url 
$localUrl = "http://localhost:3000/api/aitfdata";


try {
    // fetch data from database
    $sql = "SELECT * FROM customer_info"; // 
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalCustomers = count($customers);

    $customerArray = [];

    // make query first to execute loop 
    $certStmt = $db->prepare("SELECT 
        c.audit_status as certification_status, 
        c.certification_stage_date as certification_decision_date, 
        cp.certificate_no, 
        cp.registration_date as issue_date, 
        cp.valid_until as expire_date, 
        (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id ORDER BY asi.audit_schedule_id DESC LIMIT 1) as standard_name 
    FROM customer_info c 
    LEFT JOIN certificate_print cp ON c.id = cp.customer_id 
    WHERE c.id = :cid 
    ORDER BY cp.certificate_id DESC LIMIT 1");

    // Loop chalakar saara data ek Array me collect karenge
    foreach ($customers as $data) {
        $certStmt->execute([':cid' => $data['id']]);
        $certInfo = $certStmt->fetch(PDO::FETCH_ASSOC);

        $customerArray[] = [
            "customer_id" => $data['id'],
            "name" => $data['customer_name'],
            "address" => $data['address'],
            "gstin" => $data['gstin_no'],
            "email" => $data['email_id'],
            "contact" => $data['contact'],
            "scope" => $data['scope'],
            "comment" => $data['comment'],
            // New Certificate Details below
            "certificate_no" => $certInfo['certificate_no'] ?? "",
            "certification_status" => $certInfo['certification_status'] ?? "",
            "certification_decision_date" => $certInfo['certification_decision_date'] ?? "",
            "standard_name" => $certInfo['standard_name'] ?? "",
            "issue_date" => $certInfo['issue_date'] ?? "",
            "expire_date" => $certInfo['expire_date'] ?? ""
        ];
    }

    
    $payload = [
        "action" => "bulk_sync_customer",
        "total_records" => $totalCustomers,
        "data" => $customerArray 
    ];

    
    $ch = curl_init($localUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // IPv4 Force
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    
    $finalOutput = [
        "Customer" => $customerArray, // Naming it products array of objects jaisa aapne image 1 me dikhaya
        "response" => json_decode($response, true), // Api ka reply
        "status" => $httpCode >= 200 && $httpCode < 300 ? "success" : "failed",
        "total" => $totalCustomers
    ];

    echo json_encode($finalOutput, JSON_PRETTY_PRINT);

}
catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>

<?php
/*
 * Helper API to check service type
 * Returns the service type for a given service ID
 * Used by Contract Review modal to determine if Rc-1 suffix should be applied
 */

session_start();
include_once './dbConnect.php';

// Set proper JSON headers
header('Content-Type: application/json; charset=utf-8');

// Start output buffering to catch any errors/warnings
ob_start();

// Helper function to output clean JSON
function outputJSON($data) {
    ob_clean();
    echo json_encode($data);
    exit;
}

if (isset($_GET['serviceid']) && !empty($_GET['serviceid'])) {
    try {
        $serviceId = $_GET['serviceid'];
        
        // Get service type from service table
        $serviceTypeSql = "SELECT service_type FROM service WHERE service_id = :service_id";
        $serviceTypeStmt = $db->prepare($serviceTypeSql);
        $serviceTypeStmt->bindParam(':service_id', $serviceId);
        $serviceTypeStmt->execute();
        $serviceTypeResult = $serviceTypeStmt->fetch();
        
        if ($serviceTypeResult) {
            error_log("checkServiceType.php - Service ID: $serviceId, Type: " . $serviceTypeResult['service_type']);
            outputJSON(array(
                'success' => true,
                'service_id' => $serviceId,
                'service_type' => $serviceTypeResult['service_type']
            ));
        } else {
            error_log("checkServiceType.php - Service ID: $serviceId not found");
            outputJSON(array(
                'success' => false,
                'message' => 'Service not found',
                'service_id' => $serviceId
            ));
        }
    } catch (Exception $ex) {
        error_log("checkServiceType.php - Error: " . $ex->getMessage());
        http_response_code(500);
        outputJSON(array(
            'success' => false,
            'error' => true,
            'message' => $ex->getMessage()
        ));
    }
} else {
    http_response_code(400);
    outputJSON(array(
        'success' => false,
        'error' => true,
        'message' => 'Missing serviceid parameter'
    ));
}
?>

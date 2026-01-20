<?php
/*
 * Fetch all certificate numbers for a customer
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

header('Content-Type: application/json; charset=utf-8');
ob_start();

if (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) {
    try {
        $customerId = $_GET['customer_id'];
        $certificates = array();
        
        error_log("GET_CERT_NUMBERS: Fetching for customer_id: " . $customerId);
        
        // Method 1: Get all services for this customer and their certificates
        $serviceSql = "SELECT service_id, service_type FROM service WHERE customer_id = :customer_id ORDER BY service_id";
        $stmt = $db->prepare($serviceSql);
        $stmt->bindParam(':customer_id', $customerId);
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("GET_CERT_NUMBERS: Found " . count($services) . " services");
        
        // For each service, get certificate information
        foreach ($services as $service) {
            $serviceId = $service['service_id'];
            $serviceType = $service['service_type'];
            
            // Get certificate number from certificate_print table (without iso_standard column)
            $certSql = "SELECT certificate_no FROM certificate_print WHERE service_id = :service_id AND certificate_no IS NOT NULL AND certificate_no != ''";
            $certStmt = $db->prepare($certSql);
            $certStmt->bindParam(':service_id', $serviceId);
            $certStmt->execute();
            $certResults = $certStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($certResults)) {
                foreach ($certResults as $cert) {
                    // Try to get ISO standard from audit_application or audit_schedule_id
                    $isoStandard = '';
                    try {
                        $isoSql = "SELECT certification FROM audit_application WHERE service_id = :service_id LIMIT 1";
                        $isoStmt = $db->prepare($isoSql);
                        $isoStmt->bindParam(':service_id', $serviceId);
                        $isoStmt->execute();
                        $isoResult = $isoStmt->fetch(PDO::FETCH_ASSOC);
                        if ($isoResult && !empty($isoResult['certification'])) {
                            $isoStandard = $isoResult['certification'];
                        }
                    } catch (Exception $e) {
                        // Ignore error
                    }
                    
                    $certificates[] = array(
                        'certificate_no' => $cert['certificate_no'],
                        'iso_standard' => $isoStandard,
                        'service_type' => $serviceType,
                        'service_id' => $serviceId
                    );
                    error_log("GET_CERT_NUMBERS: Added cert: " . $cert['certificate_no'] . " from certificate_print");
                }
            }
        }
        
        // Method 2: If still no certificates, try customer_info table
        if (empty($certificates)) {
            $customerSql = "SELECT certification_no, scope FROM customer_info WHERE id = :customer_id AND certification_no IS NOT NULL AND certification_no != ''";
            $customerStmt = $db->prepare($customerSql);
            $customerStmt->bindParam(':customer_id', $customerId);
            $customerStmt->execute();
            $customerResult = $customerStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($customerResult && !empty($customerResult['certification_no'])) {
                // Split if comma-separated
                $certNos = explode(',', $customerResult['certification_no']);
                foreach ($certNos as $certNo) {
                    $certNo = trim($certNo);
                    if (!empty($certNo)) {
                        $certificates[] = array(
                            'certificate_no' => $certNo,
                            'iso_standard' => $customerResult['scope'] ?? '',
                            'service_type' => '',
                            'service_id' => ''
                        );
                        error_log("GET_CERT_NUMBERS: Added cert: " . $certNo . " from customer_info");
                    }
                }
            }
        }
        
        // Remove duplicates based on certificate_no
        $uniqueCertificates = array();
        $seen = array();
        foreach ($certificates as $cert) {
            if (!in_array($cert['certificate_no'], $seen)) {
                $seen[] = $cert['certificate_no'];
                $uniqueCertificates[] = $cert;
            }
        }
        
        error_log("GET_CERT_NUMBERS: Total unique certificates: " . count($uniqueCertificates));
        
        ob_clean();
        echo json_encode(array(
            'success' => true,
            'certificates' => $uniqueCertificates,
            'count' => count($uniqueCertificates)
        ));
    } catch (Exception $ex) {
        error_log("GET_CERT_NUMBERS ERROR: " . $ex->getMessage());
        ob_clean();
        http_response_code(500);
        echo json_encode(array(
            'success' => false,
            'error' => $ex->getMessage()
        ));
    }
} else {
    ob_clean();
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error' => 'Customer ID is required'
    ));
}
?>

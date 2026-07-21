<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
include_once './include/certification_cycle_helper.php';
date_default_timezone_set('Asia/Kolkata');

// Set proper JSON headers
header('Content-Type: application/json; charset=utf-8');

// Start output buffering to catch any errors/warnings
ob_start();

// Helper function to output clean JSON
function outputJSON($data, $statusCode = 200) {
    ob_clean();
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

function getNextAllotmentSequence($db, $customerId, $year, $month) {
    $month = str_pad((int)$month, 2, '0', STR_PAD_LEFT);
    $year = (string)(int)$year;
    $monthName = date('M', mktime(0, 0, 0, (int)$month, 1));
    $yy = substr($year, -2);
    $monthPrefix = 'AITF/' . $monthName . '-' . $yy . '/%';

    // 1. Get max client_count from allotment_count as a number.
    $maxCountFromAllotment = 0;
    $sql1 = "SELECT MAX(CAST(client_count AS UNSIGNED)) as max_count FROM allotment_count WHERE year = :year AND CAST(month AS UNSIGNED) = :month";
    $stmt1 = $db->prepare($sql1);
    $stmt1->bindParam(':year', $year);
    $monthIntForCount = (int)$month;
    $stmt1->bindParam(':month', $monthIntForCount, PDO::PARAM_INT);
    $stmt1->execute();
    $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
    if ($row1 && $row1['max_count'] !== null) {
        $maxCountFromAllotment = (int)$row1['max_count'];
    }

    // 2. Get max sequence from certification_aggrement for the same Agreement month/year.
    $maxCountFromAgreement = 0;
    $sql2 = "SELECT aggrement_no FROM certification_aggrement WHERE YEAR(aggrement_date) = :year AND MONTH(aggrement_date) = :month AND aggrement_no LIKE :prefix";
    $stmt2 = $db->prepare($sql2);
    $monthInt = (int)$month;
    $stmt2->bindParam(':year', $year);
    $stmt2->bindParam(':month', $monthInt, PDO::PARAM_INT);
    $stmt2->bindParam(':prefix', $monthPrefix);
    $stmt2->execute();
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows2 as $row2) {
        $agNo = $row2['aggrement_no'];
        if ($agNo && preg_match('/^AITF\/' . preg_quote($monthName, '/') . '-' . preg_quote($yy, '/') . '\/(\d+)(?:\s+Rc-\d+)?$/i', $agNo, $matches)) {
            $seq = (int)$matches[1];
            if ($seq > $maxCountFromAgreement) {
                $maxCountFromAgreement = $seq;
            }
        }
    }

    $candidate = max($maxCountFromAllotment, $maxCountFromAgreement) + 1;

    // Loop to ensure candidate number doesn't match any existing allotment number
    while (true) {
        $candidateNo = 'AITF/' . $monthName . '-' . $yy . '/' . $candidate;
        
        $sqlCheck = "SELECT COUNT(*) as count FROM certification_aggrement WHERE aggrement_no = :no";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->bindParam(':no', $candidateNo);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->fetch();
        
        if (!$resCheck || (int)$resCheck['count'] === 0) {
            break; // Unique!
        }
        $candidate++;
    }

    return $candidate;
}

function getRequestHeadersSafe() {
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (is_array($headers)) {
            return $headers;
        }
    }

    $headers = array();
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
            $headers[$headerName] = $value;
        }
    }

    return $headers;
}

function getHeaderValue($headerName) {
    $headers = getRequestHeadersSafe();
    $requestedHeader = strtolower($headerName);

    foreach ($headers as $key => $value) {
        if (strtolower($key) === $requestedHeader) {
            return trim($value);
        }
    }

    if ($requestedHeader === 'x-http-authorization' && isset($_SERVER['HTTP_X_HTTP_AUTHORIZATION'])) {
        return trim($_SERVER['HTTP_X_HTTP_AUTHORIZATION']);
    }

    return '';
}

function getExpectedApiKey() {
    $serverName = isset($_SERVER['SERVER_NAME']) ? strtolower($_SERVER['SERVER_NAME']) : '';
    $isLocalhost = in_array($serverName, array('localhost', '127.0.0.1', '::1'), true);

    $apiKeys = array(
        'localhost' => 'local-dev-api-key-12345',
        'live' => 'live-production-api-key-67890'
    );

    return $isLocalhost ? $apiKeys['localhost'] : $apiKeys['live'];
}

function isLoggedInSessionRequest() {
    return (
        isset($_SESSION['id']) && !empty($_SESSION['id']) &&
        isset($_SESSION['username']) && $_SESSION['username'] !== ''
    );
}

function isAuthorizedRequest() {
    if (isLoggedInSessionRequest()) {
        return true;
    }

    $receivedApiKey = getHeaderValue('x-http-authorization');
    $expectedApiKey = getExpectedApiKey();

    if ($receivedApiKey === '' || $expectedApiKey === '') {
        return false;
    }

    return hash_equals($expectedApiKey, $receivedApiKey);
}

if (!isAuthorizedRequest()) {
    outputJSON(array('error' => 'Unauthorized'), 401);
}

if (isset($_GET['customerid']) && !empty($_GET['customerid'])) {
    try {
        // Get customer details
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['customerid']);
        $statement->execute();
        $customerDetails = $statement->fetch();
        
        // Check if customer was found
        if (!$customerDetails) {
            outputJSON(array('error' => true, 'message' => 'Customer not found'));
        }
        
        // If serviceid is provided (for contract review), get the allotment number
        if (isset($_GET['serviceid']) && !empty($_GET['serviceid'])) {
            try {
                // Log the query parameters
                error_log("CONTRACT REVIEW - Customer ID: " . $_GET['customerid'] . ", Service ID: " . $_GET['serviceid']);
                
                // Check if this service is an RC type
                $serviceTypeSql = "SELECT service_type FROM service WHERE service_id = :sid";
                $serviceTypeStmt = $db->prepare($serviceTypeSql);
                $serviceTypeStmt->bindParam(':sid', $_GET['serviceid']);
                $serviceTypeStmt->execute();
                $serviceTypeResult = $serviceTypeStmt->fetch();
                $isRCService = ($serviceTypeResult && $serviceTypeResult['service_type'] == 'RC');
                $isCRService = ($serviceTypeResult && $serviceTypeResult['service_type'] == 'CR');
                
                // Check if customer has previous CA/SA services (indicating this is NOT their first cycle)
                $hasPreviousServices = false;
                if ($isCRService) {
                    $previousServiceSql = "SELECT COUNT(*) as service_count FROM service WHERE customer_id = :cid AND service_type IN ('CA', 'SA-I', 'SA-II') AND service_id < :current_sid";
                    $prevStmt = $db->prepare($previousServiceSql);
                    $prevStmt->bindParam(':cid', $_GET['customerid']);
                    $prevStmt->bindParam(':current_sid', $_GET['serviceid']);
                    $prevStmt->execute();
                    $prevResult = $prevStmt->fetch();
                    $hasPreviousServices = ($prevResult && $prevResult['service_count'] > 0);
                    error_log("CONTRACT REVIEW - Has previous CA/SA services: " . ($hasPreviousServices ? 'YES' : 'NO'));
                }
                
                // STRATEGY 1: Get aggrement_no from certification_aggrement table for THIS specific service
                $allotmentSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :customer_id AND service = :service_id AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1";
                $allotmentStmt = $db->prepare($allotmentSql);
                $allotmentStmt->bindParam(':customer_id', $_GET['customerid']);
                $allotmentStmt->bindParam(':service_id', $_GET['serviceid']);
                $allotmentStmt->execute();
                $allotmentResult = $allotmentStmt->fetch();
                
                // Log what we found
                error_log("CONTRACT REVIEW - Service-specific query result: " . print_r($allotmentResult, true));
                
                // If found for specific service, use it
                if ($allotmentResult && !empty($allotmentResult['aggrement_no'])) {
                    $baseAllotment = $allotmentResult['aggrement_no'];
                    
                    // If this is an RC service OR a CR service after previous CA/SA, append RC suffix
                    if ($isRCService || ($isCRService && $hasPreviousServices)) {
                        // Get the original base allotment from CA contract review (without any RC suffix)
                        // This should be the same agreement number generated in CA section
                        $originalAllotmentSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid AND aggrement_no NOT LIKE '% Rc-%' AND aggrement_no NOT LIKE '%-RC-%' ORDER BY id ASC LIMIT 1";
                        $origStmt = $db->prepare($originalAllotmentSql);
                        $origStmt->bindParam(':cid', $_GET['customerid']);
                        $origStmt->execute();
                        $origResult = $origStmt->fetch();
                        
                        if ($origResult && !empty($origResult['aggrement_no'])) {
                            $baseAllotment = $origResult['aggrement_no'];
                        }
                        
                        // Remove any existing RC suffix to ensure clean base number
                        $baseAllotment = preg_replace('/\s+Rc-\d+$/', '', $baseAllotment);
                        $baseAllotment = preg_replace('/-RC-\d+$/', '', $baseAllotment);
                        
                        // Append RC suffix with format: "{agreement_number} Rc-1"
                        $customerDetails['unique_id'] = $baseAllotment . ' Rc-1';
                        error_log("CONTRACT REVIEW - ✓ Using RC allotment with suffix: " . $customerDetails['unique_id']);
                    } else {
                        $customerDetails['unique_id'] = $baseAllotment;
                        error_log("CONTRACT REVIEW - ✓ Using service-specific allotment: " . $baseAllotment);
                    }
                } else {
                    // STRATEGY 2: Fallback - get the MOST RECENT allotment number for this customer (any service)
                    error_log("CONTRACT REVIEW - No allotment for this service, checking for any customer allotment...");
                    $fallbackSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :customer_id AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1";
                    $fallbackStmt = $db->prepare($fallbackSql);
                    $fallbackStmt->bindParam(':customer_id', $_GET['customerid']);
                    $fallbackStmt->execute();
                    $fallbackResult = $fallbackStmt->fetch();
                    
                    if ($fallbackResult && !empty($fallbackResult['aggrement_no'])) {
                        $baseAllotment = $fallbackResult['aggrement_no'];
                        
                        // If this is an RC service OR a CR service after previous CA/SA, append RC suffix
                        if ($isRCService || ($isCRService && $hasPreviousServices)) {
                            // Get the original base allotment from CA contract review (without any RC suffix)
                            // This should be the same agreement number generated in CA section
                            $originalAllotmentSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid AND aggrement_no NOT LIKE '% Rc-%' AND aggrement_no NOT LIKE '%-RC-%' ORDER BY id ASC LIMIT 1";
                            $origStmt = $db->prepare($originalAllotmentSql);
                            $origStmt->bindParam(':cid', $_GET['customerid']);
                            $origStmt->execute();
                            $origResult = $origStmt->fetch();
                            
                            if ($origResult && !empty($origResult['aggrement_no'])) {
                                $baseAllotment = $origResult['aggrement_no'];
                            }
                            
                            // Remove any existing RC suffix to ensure clean base number
                            $baseAllotment = preg_replace('/\s+Rc-\d+$/', '', $baseAllotment);
                            $baseAllotment = preg_replace('/-RC-\d+$/', '', $baseAllotment);
                            
                            // Append RC suffix with format: "{agreement_number} Rc-1"
                            $customerDetails['unique_id'] = $baseAllotment . ' Rc-1';
                            error_log("CONTRACT REVIEW - ✓ Using fallback RC allotment with suffix: " . $customerDetails['unique_id']);
                        } else {
                            $customerDetails['unique_id'] = $baseAllotment;
                            error_log("CONTRACT REVIEW - ✓ Using fallback (latest) allotment: " . $baseAllotment);
                        }
                    } else {
                        error_log("CONTRACT REVIEW - ✗ No allotment number found for customer at all");
                        
                        // STRATEGY 3: For NEW RC clients - use customer's base unique_id and add RC-1 suffix
                        if ($isRCService || ($isCRService && $hasPreviousServices)) {
                            // Get the customer's base unique_id and add RC-1 suffix
                            $baseUniqueId = $customerDetails['unique_id'] ?? '';
                            
                            if (!empty($baseUniqueId)) {
                                // Remove any existing RC suffix to ensure clean base number
                                $baseUniqueId = preg_replace('/\s+Rc-\d+$/', '', $baseUniqueId);
                                $baseUniqueId = preg_replace('/-RC-\d+$/', '', $baseUniqueId);
                                
                                // Append RC suffix with format: "{unique_id} Rc-1"
                                $customerDetails['unique_id'] = $baseUniqueId . ' Rc-1';
                                error_log("CONTRACT REVIEW - ✓ NEW RC CLIENT: Applied Rc-1 suffix to base unique_id: " . $customerDetails['unique_id']);
                            } else {
                                error_log("CONTRACT REVIEW - ✗ No base unique_id found in customer_info table");
                            }
                        }
                    }
                }
            } catch (Exception $allotmentEx) {
                error_log("CONTRACT REVIEW - Exception: " . $allotmentEx->getMessage());
                // Silent fail - but still apply RC suffix if needed
                if ($isRCService || ($isCRService && $hasPreviousServices)) {
                    $baseUniqueId = $customerDetails['unique_id'] ?? '';
                    if (!empty($baseUniqueId)) {
                        $baseUniqueId = preg_replace('/\s+Rc-\d+$/', '', $baseUniqueId);
                        $baseUniqueId = preg_replace('/-RC-\d+$/', '', $baseUniqueId);
                        $customerDetails['unique_id'] = $baseUniqueId . ' Rc-1';
                        error_log("CONTRACT REVIEW - ✓ RC suffix applied after exception");
                    }
                }
            }
        }
        
        // Final log of what we're returning
        error_log("CONTRACT REVIEW - Final unique_id being returned: " . ($customerDetails['unique_id'] ?? 'NULL'));

        outputJSON($customerDetails);
    } catch (Exception $ex) {
        http_response_code(500);
        outputJSON(array('error' => true, 'message' => $ex->getMessage()));
    }
} else if (isset($_GET['customer_id']) && !empty($_GET['customer_id']) && isset($_GET['service_id']) && !empty($_GET['service_id'])) {
    try {
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['customer_id']);
        $statement->execute();
        $customerDetails = $statement->fetch();

//        $service = "SELECT * FROM service_type_ca WHERE ca_id = :id";
//        $statement = $db->prepare($service);
//        $statement->bindParam(':id', $_GET['service_id']);
//        $statement->execute();
//        $serviceDetails = $statement->fetch();

        $auditNotificatoin = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :id";
        $statement = $db->prepare($auditNotificatoin);
        $statement->bindParam(':id', $_GET['customer_id']);
        $statement->bindParam(':service', $_GET['service_id']);
        $statement->execute();
        $auditDetails = $statement->fetch();
        
        // If auditDetails not found or empty, initialize as array
        if (!$auditDetails) {
            $auditDetails = [];
        }

        $leadAuditor = "SELECT * FROM stage_audit_one WHERE ca_service_id = :service ORDER BY stage_one_id ASC limit 1";
        $statement = $db->prepare($leadAuditor);
        $statement->bindParam(':service', $_GET['service_id']);
        $statement->execute();
        $lead_Auditor = $statement->fetch();

        // For RC: Try BOTH rc_id AND main service_id, use whichever has data
        $serviceIdToUse = $_GET['service_id'];
        $rcId = null;
        $mainServiceId = null;
        
        $rcCheckSql = "SELECT rc.service as main_service_id, s.service_type 
                       FROM service_type_rc rc 
                       INNER JOIN service s ON rc.service = s.service_id
                       WHERE rc.rc_id = :rc_id";
        $rcCheckStmt = $db->prepare($rcCheckSql);
        $rcCheckStmt->bindParam(':rc_id', $_GET['service_id']);
        $rcCheckStmt->execute();
        $rcCheckResult = $rcCheckStmt->fetch();
        
        if ($rcCheckResult && $rcCheckResult['service_type'] == 'RC') {
            $rcId = $_GET['service_id'];
            $mainServiceId = $rcCheckResult['main_service_id'];
            
            // For RC: Fetch audit date from service_type_rc table if audit_dates is missing
            if (empty($auditDetails['audit_dates'])) {
                $rcAuditDateSql = "SELECT audit_date FROM service_type_rc WHERE rc_id = :rc_id";
                $rcAuditStmt = $db->prepare($rcAuditDateSql);
                $rcAuditStmt->bindParam(':rc_id', $rcId);
                $rcAuditStmt->execute();
                $rcAuditDate = $rcAuditStmt->fetch();
                
                $auditDateToUse = null;
                if ($rcAuditDate && !empty($rcAuditDate['audit_date'])) {
                    $auditDateToUse = $rcAuditDate['audit_date'];
                } else {
                    // Fallback: Check service table audit_date
                    $serviceAuditDateSql = "SELECT audit_date FROM service WHERE service_id = :service_id";
                    $serviceAuditStmt = $db->prepare($serviceAuditDateSql);
                    $serviceAuditStmt->bindParam(':service_id', $mainServiceId);
                    $serviceAuditStmt->execute();
                    $serviceAuditDate = $serviceAuditStmt->fetch();
                    
                    if ($serviceAuditDate && !empty($serviceAuditDate['audit_date'])) {
                        $auditDateToUse = $serviceAuditDate['audit_date'];
                    }
                }
                
                if ($auditDateToUse) {
                    // Format the date for display (DD.MM.YYYY format)
                    $auditDateFormatted = date('d.m.Y', strtotime($auditDateToUse));
                    // Ensure auditDetails is an array
                    if (!is_array($auditDetails)) {
                        $auditDetails = (array)$auditDetails;
                    }
                    $auditDetails['audit_dates'] = $auditDateFormatted;
                    if (empty($auditDetails['intimation_date'])) {
                        $auditDetails['intimation_date'] = $auditDateFormatted;
                    }
                }
            }
            
            // Try rc_id first
            $audit = "SELECT certification, assessment_standard, actual_effective FROM audit_application WHERE service_id = :id";
            $statement = $db->prepare($audit);
            $statement->bindParam(':id', $rcId);
            $statement->execute();
            $auditInfo = $statement->fetch();
            
            // If no data with rc_id, try main service_id
            if (!$auditInfo || empty($auditInfo['certification'])) {
                $statement = $db->prepare($audit);
                $statement->bindParam(':id', $mainServiceId);
                $statement->execute();
                $auditInfo = $statement->fetch();
                $serviceIdToUse = $mainServiceId;
            } else {
                $serviceIdToUse = $rcId;
            }
        } else {
            // Not RC, use service_id as-is
            $audit = "SELECT certification, assessment_standard, actual_effective FROM audit_application WHERE service_id = :id";
            $statement = $db->prepare($audit);
            $statement->bindParam(':id', $serviceIdToUse);
            $statement->execute();
            $auditInfo = $statement->fetch();
        }
        
        if (!$auditInfo) {
            $auditInfo = ['certification' => '', 'assessment_standard' => '', 'actual_effective' => ''];
        }

        $auditApp = "SELECT * FROM audit_application WHERE service_id = :id";
        $statement = $db->prepare($auditApp);
        $statement->bindParam(':id', $serviceIdToUse);
        $statement->execute();
        $auditAppInfo = $statement->fetch();
        
        if (!$auditAppInfo) {
            $auditAppInfo = ['certification' => '', 'assessment_standard' => '', 'audit_id' => null];
        }
        
        if (empty($auditInfo['certification']) && !empty($auditAppInfo['certification'])) {
            $auditInfo['certification'] = $auditAppInfo['certification'];
        }
        if (empty($auditInfo['assessment_standard']) && !empty($auditAppInfo['assessment_standard'])) {
            $auditInfo['assessment_standard'] = $auditAppInfo['assessment_standard'];
        }

        // Fetch IAF/NACE info only if audit_id exists
        $iafinfo = null;
        if (!empty($auditAppInfo['audit_id'])) {
            $sql1 = "SELECT * FROM `audit_iaf_nace` where audit_id = :id";
            $statement = $db->prepare($sql1);
            $statement->bindParam(':id', $auditAppInfo["audit_id"]);
            $statement->execute();
            $iafinfo = $statement->fetch();
        }

        $mandays = "SELECT audit_wokrdays FROM `stage_audit_one` where ca_service_id = :id and (`auditor_category` LIKE '%auditor%' OR `auditor_category` LIKE '%team leader%')";
        $statement = $db->prepare($mandays);
        $statement->bindParam(':id', $_GET['service_id']);
        $statement->execute();
        $mandaysCount = $statement->fetchAll();

        $totalDays = 0;
        foreach ($mandaysCount as $days) {
            $totalDays = $totalDays + $days['audit_wokrdays'];
        }

        $leadAuditorList = "SELECT * FROM stage_audit_one WHERE ca_service_id = :service and (`auditor_category` LIKE '%auditor%' OR `auditor_category` LIKE '%team leader%')";
        $statement = $db->prepare($leadAuditorList);
        $statement->bindParam(':service', $_GET['service_id']);
        $statement->execute();
        $lead_Auditors = $statement->fetchAll();
        $customer = "SELECT * FROM stage_audit_one WHERE ca_service_id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['service_id']);
        $statement->execute();
        $customerDetailsx = $statement->fetchAll(PDO::FETCH_ASSOC);
        
            $customer = "SELECT * FROM stage_audit_two WHERE service_id = :id";
            $statement = $db->prepare($customer);
            $statement->bindParam(':id', $_GET['service_id']);
            $statement->execute();
            $customerDetailsy = $statement->fetchAll(PDO::FETCH_ASSOC);


        outputJSON(array($customerDetails, $auditDetails, $lead_Auditor, $auditInfo, $auditAppInfo, $totalDays, $lead_Auditors, $iafinfo, $customerDetailsx, $customerDetailsy));
    } catch (Exception $ex) {
        http_response_code(500);
        outputJSON(array('error' => true, 'message' => $ex->getMessage()));
    }
} else if (isset($_GET['customerID']) && !empty($_GET['customerID']) && isset($_GET['serviceID']) && !empty($_GET['serviceID'])) {
    try {
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['customerID']);
        $statement->execute();
        $customerDetails = $statement->fetch();

        // Fetch agreement number for this service
        try {
            error_log("AUDIT NOTIFICATION - Customer ID: " . $_GET['customerID'] . ", Service ID: " . $_GET['serviceID']);
            
            // Check if this service is an RC type
            $serviceTypeSql = "SELECT service_type FROM service WHERE service_id = :sid";
            $serviceTypeStmt = $db->prepare($serviceTypeSql);
            $serviceTypeStmt->bindParam(':sid', $_GET['serviceID']);
            $serviceTypeStmt->execute();
            $serviceTypeResult = $serviceTypeStmt->fetch();
            $isAuditNotiRCService = ($serviceTypeResult && $serviceTypeResult['service_type'] == 'RC');
            error_log("AUDIT NOTIFICATION - Service type: " . ($serviceTypeResult ? $serviceTypeResult['service_type'] : 'NOT FOUND'));
            
            // Try to get agreement number for THIS specific service
            $agreementSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :customer_id AND service = :service_id AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1";
            $agreementStmt = $db->prepare($agreementSql);
            $agreementStmt->bindParam(':customer_id', $_GET['customerID']);
            $agreementStmt->bindParam(':service_id', $_GET['serviceID']);
            $agreementStmt->execute();
            $agreementResult = $agreementStmt->fetch();
            
            if ($agreementResult && !empty($agreementResult['aggrement_no'])) {
                $baseAllotment = $agreementResult['aggrement_no'];
                
                // If RC service, apply Rc-1 suffix
                if ($isAuditNotiRCService) {
                    // Get the original base allotment from CA contract review (without any RC suffix)
                    $originalAllotmentSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid AND aggrement_no NOT LIKE '% Rc-%' AND aggrement_no NOT LIKE '%-RC-%' ORDER BY id ASC LIMIT 1";
                    $origStmt = $db->prepare($originalAllotmentSql);
                    $origStmt->bindParam(':cid', $_GET['customerID']);
                    $origStmt->execute();
                    $origResult = $origStmt->fetch();
                    
                    if ($origResult && !empty($origResult['aggrement_no'])) {
                        $baseAllotment = $origResult['aggrement_no'];
                    }
                    
                    // Remove any existing RC suffix to ensure clean base number
                    $baseAllotment = preg_replace('/\s+Rc-\d+$/', '', $baseAllotment);
                    $baseAllotment = preg_replace('/-RC-\d+$/', '', $baseAllotment);
                    
                    // Append RC suffix
                    $customerDetails['unique_id'] = $baseAllotment . ' Rc-1';
                    error_log("AUDIT NOTIFICATION - ✓ RC Service: Using agreement with Rc-1 suffix: " . $customerDetails['unique_id']);
                } else {
                    $customerDetails['unique_id'] = $baseAllotment;
                    error_log("AUDIT NOTIFICATION - ✓ Using service-specific agreement: " . $baseAllotment);
                }
            } else {
                // Fallback: Get the most recent agreement number for this customer (any service)
                error_log("AUDIT NOTIFICATION - No agreement for this service, checking for any customer agreement...");
                $fallbackSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :customer_id AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1";
                $fallbackStmt = $db->prepare($fallbackSql);
                $fallbackStmt->bindParam(':customer_id', $_GET['customerID']);
                $fallbackStmt->execute();
                $fallbackResult = $fallbackStmt->fetch();
                
                if ($fallbackResult && !empty($fallbackResult['aggrement_no'])) {
                    $baseAllotment = $fallbackResult['aggrement_no'];
                    
                    // If RC service, apply Rc-1 suffix
                    if ($isAuditNotiRCService) {
                        // Get the original base allotment
                        $originalAllotmentSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid AND aggrement_no NOT LIKE '% Rc-%' AND aggrement_no NOT LIKE '%-RC-%' ORDER BY id ASC LIMIT 1";
                        $origStmt = $db->prepare($originalAllotmentSql);
                        $origStmt->bindParam(':cid', $_GET['customerID']);
                        $origStmt->execute();
                        $origResult = $origStmt->fetch();
                        
                        if ($origResult && !empty($origResult['aggrement_no'])) {
                            $baseAllotment = $origResult['aggrement_no'];
                        }
                        
                        // Remove any existing RC suffix
                        $baseAllotment = preg_replace('/\s+Rc-\d+$/', '', $baseAllotment);
                        $baseAllotment = preg_replace('/-RC-\d+$/', '', $baseAllotment);
                        
                        $customerDetails['unique_id'] = $baseAllotment . ' Rc-1';
                        error_log("AUDIT NOTIFICATION - ✓ RC Service: Using fallback agreement with Rc-1 suffix: " . $customerDetails['unique_id']);
                    } else {
                        $customerDetails['unique_id'] = $baseAllotment;
                        error_log("AUDIT NOTIFICATION - ✓ Using fallback (latest) agreement: " . $baseAllotment);
                    }
                } else {
                    error_log("AUDIT NOTIFICATION - ✗ No agreement number found for customer");
                    
                    // NEW RC CLIENT: Use customer's base unique_id and add RC-1 suffix
                    if ($isAuditNotiRCService) {
                        $baseUniqueId = $customerDetails['unique_id'] ?? '';
                        if (!empty($baseUniqueId)) {
                            // Remove any existing RC suffix
                            $baseUniqueId = preg_replace('/\s+Rc-\d+$/', '', $baseUniqueId);
                            $baseUniqueId = preg_replace('/-RC-\d+$/', '', $baseUniqueId);
                            
                            $customerDetails['unique_id'] = $baseUniqueId . ' Rc-1';
                            error_log("AUDIT NOTIFICATION - ✓ NEW RC CLIENT: Applied Rc-1 suffix to base unique_id: " . $customerDetails['unique_id']);
                        }
                    }
                }
            }
        } catch (Exception $agreementEx) {
            error_log("AUDIT NOTIFICATION - Exception: " . $agreementEx->getMessage());
            // Silent fail - but still apply RC suffix if needed
            if (isset($isAuditNotiRCService) && $isAuditNotiRCService) {
                $baseUniqueId = $customerDetails['unique_id'] ?? '';
                if (!empty($baseUniqueId)) {
                    $baseUniqueId = preg_replace('/\s+Rc-\d+$/', '', $baseUniqueId);
                    $baseUniqueId = preg_replace('/-RC-\d+$/', '', $baseUniqueId);
                    $customerDetails['unique_id'] = $baseUniqueId . ' Rc-1';
                    error_log("AUDIT NOTIFICATION - ✓ RC suffix applied after exception");
                }
            }
        }

        $auditNotificatoin = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :id";
        $statement = $db->prepare($auditNotificatoin);
        $statement->bindParam(':id', $_GET['customerID']);
        $statement->bindParam(':service', $_GET['serviceID']);
        $statement->execute();
        $auditDetails = $statement->fetch();

        $leadAuditor = "SELECT * FROM stage_audit_one WHERE ca_service_id = :service ORDER BY stage_one_id ASC limit 1";
        $statement = $db->prepare($leadAuditor);
        $statement->bindParam(':service', $_GET['serviceID']);
        $statement->execute();
        $lead_Auditor = $statement->fetch();

        $stage1Audit = "SELECT * FROM audit_schedule_id WHERE serviceca_id = :service";
        $statement = $db->prepare($stage1Audit);
        $statement->bindParam(':service', $_GET['serviceID']);
        $statement->execute();
        $stage_audit = $statement->fetch();
        
        error_log("STAGE II NOTIFICATION - Final unique_id being returned: " . ($customerDetails['unique_id'] ?? 'NULL'));

        outputJSON(array($customerDetails, $auditDetails, $lead_Auditor, $stage_audit));
    } catch (Exception $ex) {
        http_response_code(500);
        outputJSON(array('error' => true, 'message' => $ex->getMessage()));
    }
} else if (isset($_GET['customer_ID']) && !empty($_GET['customer_ID']) && isset($_GET['service_ID']) && !empty($_GET['service_ID'])) {
    try {
        // Log incoming request
        error_log("Stage II Request - customer_ID: " . $_GET['customer_ID'] . ", service_ID: " . $_GET['service_ID']);
        
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['customer_ID']);
        $statement->execute();
        $customerDetails = $statement->fetch();
        
        // Check if customer was found
        if (!$customerDetails) {
            error_log("Stage II Error - Customer not found: " . $_GET['customer_ID']);
            outputJSON(array('error' => true, 'message' => 'Customer not found'));
        }

        // First check if this is RC service to get main_service_id
        $rcCheckSql = "SELECT rc.service as main_service_id, s.service_type, rc.audit_date as rc_audit_date
                       FROM service_type_rc rc 
                       INNER JOIN service s ON rc.service = s.service_id
                       WHERE rc.rc_id = :rc_id";
        $rcCheckStmt = $db->prepare($rcCheckSql);
        $rcCheckStmt->bindParam(':rc_id', $_GET['service_ID']);
        $rcCheckStmt->execute();
        $rcCheckResult = $rcCheckStmt->fetch();
        
        // For RC: Try both rc_id and main_service_id when querying stage_twoaudit_id
        $serviceIdToUse = $_GET['service_ID'];
        if ($rcCheckResult && $rcCheckResult['service_type'] == 'RC') {
            // Try main_service_id first (more likely to have saved data)
            $auditNoti = "SELECT * FROM stage_twoaudit_id WHERE service_id = :service and customer_id = :id";
            $statement = $db->prepare($auditNoti);
            $statement->bindParam(':id', $_GET['customer_ID']);
            $statement->bindParam(':service', $rcCheckResult['main_service_id']);
            $statement->execute();
            $auditDetails = $statement->fetch();
            
            // If not found with main_service_id, try rc_id
            if (!$auditDetails || empty($auditDetails)) {
                $auditNoti = "SELECT * FROM stage_twoaudit_id WHERE service_id = :service and customer_id = :id";
                $statement = $db->prepare($auditNoti);
                $statement->bindParam(':id', $_GET['customer_ID']);
                $statement->bindParam(':service', $_GET['service_ID']);
                $statement->execute();
                $auditDetails = $statement->fetch();
            }
        } else {
            $auditNoti = "SELECT * FROM stage_twoaudit_id WHERE service_id = :service and customer_id = :id";
            $statement = $db->prepare($auditNoti);
            $statement->bindParam(':id', $_GET['customer_ID']);
            $statement->bindParam(':service', $_GET['service_ID']);
            $statement->execute();
            $auditDetails = $statement->fetch();
        }
        
        // Ensure auditDetails is an array
        if (!$auditDetails) {
            $auditDetails = [];
        } else if (!is_array($auditDetails)) {
            $auditDetails = (array)$auditDetails;
        }
        
        // For RC: Fetch date from RC service table (will be overridden by saved date if exists)
        if ($rcCheckResult && $rcCheckResult['service_type'] == 'RC') {
            $auditDateToUse = null;
            if (!empty($rcCheckResult['rc_audit_date'])) {
                $auditDateToUse = $rcCheckResult['rc_audit_date'];
            } else {
                // Fallback: Check service table audit_date
                $serviceAuditDateSql = "SELECT audit_date FROM service WHERE service_id = :service_id";
                $serviceAuditStmt = $db->prepare($serviceAuditDateSql);
                $serviceAuditStmt->bindParam(':service_id', $rcCheckResult['main_service_id']);
                $serviceAuditStmt->execute();
                $serviceAuditDate = $serviceAuditStmt->fetch();
                if ($serviceAuditDate && !empty($serviceAuditDate['audit_date'])) {
                    $auditDateToUse = $serviceAuditDate['audit_date'];
                }
            }
            
            // Populate audit_dates from RC service if not already set
            if ($auditDateToUse && empty($auditDetails['audit_dates'])) {
                $auditDateFormatted = date('d.m.Y', strtotime($auditDateToUse));
                $auditDetails['audit_dates'] = $auditDateFormatted;
            }
        }

        $leadAuditor = "SELECT * FROM stage_audit_two WHERE service_id = :service limit 1";
        $statement = $db->prepare($leadAuditor);
        $statement->bindParam(':service', $_GET['service_ID']);
        $statement->execute();
        $lead_Auditor = $statement->fetch();

        $leadAuditorList = "SELECT * FROM stage_audit_two WHERE service_id = :service and (`auditor_category` LIKE '%auditor%' OR `auditor_category` LIKE '%team leader%')";
        $statement = $db->prepare($leadAuditorList);
        $statement->bindParam(':service', $_GET['service_ID']);
        $statement->execute();
        $lead_Auditors = $statement->fetchAll();

        if (!$lead_Auditor || $lead_Auditor == "") {
            $leadAuditor = "SELECT * FROM stage_audit_one WHERE ca_service_id = :service limit 1";
            $statement = $db->prepare($leadAuditor);
            $statement->bindParam(':service', $_GET['service_ID']);
            $statement->execute();
            $lead_Auditor = $statement->fetch();

            $leadAuditorList = "SELECT * FROM stage_audit_one WHERE ca_service_id = :service and (`auditor_category` LIKE '%auditor%' OR `auditor_category` LIKE '%team leader%')";
            $statement = $db->prepare($leadAuditorList);
            $statement->bindParam(':service', $_GET['service_ID']);
            $statement->execute();
            $lead_Auditors = $statement->fetchAll();
        }
        
        // Ensure lead_Auditor is initialized
        if (!$lead_Auditor) {
            $lead_Auditor = [];
        }

        $stage1Audit = "SELECT * FROM audit_schedule_id WHERE serviceca_id = :service";
        $statement = $db->prepare($stage1Audit);
        $statement->bindParam(':service', $_GET['service_ID']);
        $statement->execute();
        $stage_audit = $statement->fetch();
        
        // Ensure stage_audit is initialized
        if (!$stage_audit) {
            $stage_audit = [];
        }
        
        // For RC: Also check audit_scheduleii_id table for saved stage II schedule date
        $stage2Audit = null;
        if ($rcCheckResult && $rcCheckResult['service_type'] == 'RC') {
            // Try main_service_id first
            $stage2AuditSql = "SELECT * FROM audit_scheduleii_id WHERE ca_service = :service";
            $stage2Stmt = $db->prepare($stage2AuditSql);
            $stage2Stmt->bindParam(':service', $rcCheckResult['main_service_id']);
            $stage2Stmt->execute();
            $stage2Audit = $stage2Stmt->fetch();
            
            // If not found, try rc_id
            if (!$stage2Audit || empty($stage2Audit)) {
                $stage2Stmt = $db->prepare($stage2AuditSql);
                $stage2Stmt->bindParam(':service', $_GET['service_ID']);
                $stage2Stmt->execute();
                $stage2Audit = $stage2Stmt->fetch();
            }
        } else {
            $stage2AuditSql = "SELECT * FROM audit_scheduleii_id WHERE ca_service = :service";
            $stage2Stmt = $db->prepare($stage2AuditSql);
            $stage2Stmt->bindParam(':service', $_GET['service_ID']);
            $stage2Stmt->execute();
            $stage2Audit = $stage2Stmt->fetch();
        }
        
        // If stage2Audit has saved date, use it to populate auditDetails (takes priority over RC service date)
        $savedAuditDateFormatted = null; // Initialize variable to avoid undefined variable error
        if ($stage2Audit && !empty($stage2Audit['scheduleii_date'])) {
            $savedDate = $stage2Audit['scheduleii_date'];
            // Handle different date formats
            if (strpos($savedDate, '.') !== false) {
                // Already in d.m.Y format
                $auditDateFormatted = $savedDate;
            } else {
                // Convert from Y-m-d or other format to d.m.Y
                $auditDateFormatted = date('d.m.Y', strtotime($savedDate));
            }
            $savedAuditDateFormatted = $auditDateFormatted; // Save for later use
            $auditDetails['audit_dates'] = $auditDateFormatted;
            
            // Note: stage1info and noti1auditdate will be populated later in the code (lines 606-723)
            // when they are properly initialized, using the $savedAuditDateFormatted variable
        }

        $auditApp = "SELECT * FROM audit_application WHERE service_id = :id";
        $statement = $db->prepare($auditApp);
        $statement->bindParam(':id', $_GET['service_ID']);
        $statement->execute();
        $auditAppInfo = $statement->fetch();
        
        // Ensure auditAppInfo is not false
        if (!$auditAppInfo) {
            $auditAppInfo = [];
        }

        $mandays = "SELECT audit_wokrdays FROM `stage_audit_one` where ca_service_id = :id and (`auditor_category` LIKE '%auditor%' OR `auditor_category` LIKE '%team leader%')";
        $statement = $db->prepare($mandays);
        $statement->bindParam(':id', $_GET['service_ID']);
        $statement->execute();
        $mandaysCount1 = $statement->fetchAll();

        $mandays2 = "SELECT audit_wokrdays FROM stage_audit_two where service_id = :id and (`auditor_category` LIKE '%auditor%' OR `auditor_category` LIKE '%team leader%')";
        $statement = $db->prepare($mandays2);
        $statement->bindParam(":id", $_GET['service_ID']);
        $statement->execute();
        $mandaysCount2 = $statement->fetchAll();

        // For RC: Try both rc_id and main_service_id when querying stage_oneaudit_id
        if ($rcCheckResult && $rcCheckResult['service_type'] == 'RC') {
            // Try main_service_id first
            $stage1schedule = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :id";
            $statement = $db->prepare($stage1schedule);
            $statement->bindParam(":id", $rcCheckResult['main_service_id']);
            $statement->execute();
            $stage1info = $statement->fetch();
            
            // If not found with main_service_id, try rc_id
            if (!$stage1info || empty($stage1info)) {
                $stage1schedule = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :id";
                $statement = $db->prepare($stage1schedule);
                $statement->bindParam(":id", $_GET['service_ID']);
                $statement->execute();
                $stage1info = $statement->fetch();
            }
        } else {
            $stage1schedule = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :id";
            $statement = $db->prepare($stage1schedule);
            $statement->bindParam(":id", $_GET['service_ID']);
            $statement->execute();
            $stage1info = $statement->fetch();
        }
        
        // Ensure stage1info is an array
        if (!$stage1info) {
            $stage1info = [];
        } else if (!is_array($stage1info)) {
            $stage1info = (array)$stage1info;
        }
        
        // Populate stage1info audit_dates - prioritize saved date, then RC service date
        if (isset($savedAuditDateFormatted) && !empty($savedAuditDateFormatted)) {
            // Use saved date from audit_scheduleii_id (takes priority)
            $stage1info['audit_dates'] = $savedAuditDateFormatted;
        } else if ($rcCheckResult && $rcCheckResult['service_type'] == 'RC') {
            // For RC: Populate from RC service date as fallback
            $auditDateToUse = null;
            if (!empty($rcCheckResult['rc_audit_date'])) {
                $auditDateToUse = $rcCheckResult['rc_audit_date'];
            } else {
                $serviceAuditDateSql = "SELECT audit_date FROM service WHERE service_id = :service_id";
                $serviceAuditStmt = $db->prepare($serviceAuditDateSql);
                $serviceAuditStmt->bindParam(':service_id', $rcCheckResult['main_service_id']);
                $serviceAuditStmt->execute();
                $serviceAuditDate = $serviceAuditStmt->fetch();
                if ($serviceAuditDate && !empty($serviceAuditDate['audit_date'])) {
                    $auditDateToUse = $serviceAuditDate['audit_date'];
                }
            }
            
            if ($auditDateToUse) {
                $auditDateFormatted = date('d.m.Y', strtotime($auditDateToUse));
                $stage1info['audit_dates'] = $auditDateFormatted;
            }
        }

        $totalDays = 0;
        $totalDays1 = 0;
        foreach ($mandaysCount1 as $days) {
            $totalDays1 = $totalDays1 + $days['audit_wokrdays'];
        }
        $totalDays2 = 0;
        foreach ($mandaysCount2 as $days) {
            $totalDays2 = $totalDays2 + $days['audit_wokrdays'];
        }

        $sql1 = "SELECT max(id) as id FROM `certification_aggrement` WHERE `customer_id` = :cid order by id desc limit 1";
        $statement = $db->prepare($sql1);
        $statement->bindParam(":cid", $_GET['customer_ID']);
        $statement->execute();
        $maxid = $statement->fetch();

        // Initialize $maxcid with default value
        $maxcid = null;
        
        // Only fetch agreement number if maxid exists
        if ($maxid && !empty($maxid['id'])) {
            $sql2 = "SELECT aggrement_no FROM `certification_aggrement` where id = :id ";
            $statement = $db->prepare($sql2);
            $statement->bindParam(":id", $maxid['id']);
            $statement->execute();
            $maxcid = $statement->fetch();
        }
        
        // For RC: Try both rc_id and main_service_id when querying stage_oneaudit_id for noti1auditdate
        if ($rcCheckResult && $rcCheckResult['service_type'] == 'RC') {
            // Try main_service_id first
            $stage1Noti = "SELECT audit_dates FROM stage_oneaudit_id WHERE ca_service_id = :id";
            $statement = $db->prepare($stage1Noti);
            $statement->bindParam(":id", $rcCheckResult['main_service_id']);
            $statement->execute();
            $noti1auditdate = $statement->fetch();
            
            // If not found with main_service_id, try rc_id
            if (!$noti1auditdate || empty($noti1auditdate['audit_dates'])) {
                $stage1Noti = "SELECT audit_dates FROM stage_oneaudit_id WHERE ca_service_id = :id";
                $statement = $db->prepare($stage1Noti);
                $statement->bindParam(":id", $_GET['service_ID']);
                $statement->execute();
                $noti1auditdate = $statement->fetch();
            }
        } else {
            $stage1Noti = "SELECT audit_dates FROM stage_oneaudit_id WHERE ca_service_id = :id";
            $statement = $db->prepare($stage1Noti);
            $statement->bindParam(":id", $_GET['service_ID']);
            $statement->execute();
            $noti1auditdate = $statement->fetch();
        }
        
        // Ensure noti1auditdate is an array
        if (!$noti1auditdate) {
            $noti1auditdate = [];
        } else if (!is_array($noti1auditdate)) {
            $noti1auditdate = (array)$noti1auditdate;
        }
        
        // For RC: Populate noti1auditdate audit_dates - prioritize saved date, then RC service date
        if ($rcCheckResult && $rcCheckResult['service_type'] == 'RC') {
            // First check if we have saved date from audit_scheduleii_id
            if (isset($savedAuditDateFormatted) && !empty($savedAuditDateFormatted)) {
                $noti1auditdate['audit_dates'] = $savedAuditDateFormatted;
            } else {
                // Fallback to RC service date
                $auditDateToUse = null;
                if (!empty($rcCheckResult['rc_audit_date'])) {
                    $auditDateToUse = $rcCheckResult['rc_audit_date'];
                } else {
                    $serviceAuditDateSql = "SELECT audit_date FROM service WHERE service_id = :service_id";
                    $serviceAuditStmt = $db->prepare($serviceAuditDateSql);
                    $serviceAuditStmt->bindParam(':service_id', $rcCheckResult['main_service_id']);
                    $serviceAuditStmt->execute();
                    $serviceAuditDate = $serviceAuditStmt->fetch();
                    if ($serviceAuditDate && !empty($serviceAuditDate['audit_date'])) {
                        $auditDateToUse = $serviceAuditDate['audit_date'];
                    }
                }
                
                if ($auditDateToUse) {
                    $auditDateFormatted = date('d.m.Y', strtotime($auditDateToUse));
                    $noti1auditdate['audit_dates'] = $auditDateFormatted;
                }
            }
        }
        
        $stage1Inte = "SELECT intimation_date FROM stage_oneaudit_id WHERE ca_service_id = :id";
        $statement = $db->prepare($stage1Inte);
        $statement->bindParam(":id",$_GET['service_ID']);
        $statement->execute();
        $inti1auditdate = $statement->fetch();
        
        // Ensure inti1auditdate is initialized
        if (!$inti1auditdate) {
            $inti1auditdate = [];
        }
        
        $customer = "SELECT * FROM stage_audit_two WHERE service_id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['service_ID']);
        $statement->execute();
        $customerDetailsy = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure customerDetails is not false
        if (!$customerDetails) {
            $customerDetails = [];
        }
        
        // $totalDays = $totalDays1 + $totalDays2;
//        echo json_encode($customerDetails);
        outputJSON(array($customerDetails, $auditDetails, $lead_Auditor, $stage_audit, $auditAppInfo, $totalDays1, $stage1info, $lead_Auditors, $totalDays2, $maxcid, $noti1auditdate, $inti1auditdate, $customerDetailsy));
    } catch (PDOException $pdoEx) {
        ob_clean();
        http_response_code(500);
        error_log("PDO Error in getClientInfo.php (customer_ID/service_ID): " . $pdoEx->getMessage() . " | Line: " . $pdoEx->getLine());
        outputJSON(array('error' => true, 'message' => 'Database error: ' . $pdoEx->getMessage(), 'line' => $pdoEx->getLine(), 'file' => basename($pdoEx->getFile())));
    } catch (Exception $ex) {
        ob_clean();
        http_response_code(500);
        error_log("Error in getClientInfo.php (customer_ID/service_ID): " . $ex->getMessage() . " | Line: " . $ex->getLine());
        outputJSON(array('error' => true, 'message' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => basename($ex->getFile())));
    }
} else if (isset($_GET['cdccustomer_ID']) && !empty($_GET['cdccustomer_ID']) && isset($_GET['cdcservice_ID']) && !empty($_GET['cdcservice_ID'])) {
    try {
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['cdccustomer_ID']);
        $statement->execute();
        $customerDetails = $statement->fetch();


        try {
            $customer = "SELECT * FROM stage_audit_one WHERE ca_service_id = :id";
            $statement = $db->prepare($customer);
            $statement->bindParam(':id', $_GET['cdcservice_ID']);
            $statement->execute();
            $customerDetailsx = $statement->fetchAll(PDO::FETCH_ASSOC);
        
         
        } catch (PDOException $e) {
            // Silent fail - return empty array
            $customerDetailsx = [];
        }
        
            $customer = "SELECT * FROM stage_audit_two WHERE service_id = :id";
            $statement = $db->prepare($customer);
            $statement->bindParam(':id', $_GET['cdcservice_ID']);
            $statement->execute();
            $customerDetailsy = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Fix: Use cdcservice_ID instead of crsvc_id to get the correct audit_application data
        $auditApp = "SELECT * FROM audit_application WHERE service_id = :id";
        $statement = $db->prepare($auditApp);
        // Check if crsvc_id exists and is not empty, otherwise use cdcservice_ID
        $serviceIdToUse = (isset($_GET['crsvc_id']) && !empty($_GET['crsvc_id'])) ? $_GET['crsvc_id'] : $_GET['cdcservice_ID'];
        $statement->bindParam(':id', $serviceIdToUse);
        $statement->execute();
        $auditInfo = $statement->fetch();

        // A CDC accreditation change is the current operational value for the
        // next CDC flow. Keep the original application value as a fallback.
        $latestCdcAccreditationSql = "
            SELECT crr.accreditation
            FROM cdc_report_review crr
            WHERE crr.customer_id = :customer_id
              AND UPPER(TRIM(COALESCE(crr.accreditation, ''))) IN ('NABCB', 'SELF')
            ORDER BY crr.crr_id DESC
            LIMIT 1
        ";
        $latestCdcAccreditationStmt = $db->prepare($latestCdcAccreditationSql);
        $latestCdcAccreditationStmt->bindValue(':customer_id', $_GET['cdccustomer_ID']);
        $latestCdcAccreditationStmt->execute();
        $latestCdcAccreditation = $latestCdcAccreditationStmt->fetchColumn();
        if ($latestCdcAccreditation) {
            if (!is_array($auditInfo)) {
                $auditInfo = array();
            }
            $auditInfo['review_accredition'] = strtoupper(trim($latestCdcAccreditation));
            $auditInfo['accereditation_requirement'] = strtoupper(trim($latestCdcAccreditation));
        }
        
        // For RC services: Fetch ISO standards from ALL services for this customer/agreement
        // First, detect if this is an RC service by checking service_type_rc table
        $rcCheckSql = "SELECT rc.service as main_service_id 
                       FROM service_type_rc rc 
                       WHERE rc.rc_id = :rc_id";
        $rcCheckStmt = $db->prepare($rcCheckSql);
        $rcCheckStmt->bindParam(':rc_id', $_GET['cdcservice_ID']);
        $rcCheckStmt->execute();
        $rcCheckResult = $rcCheckStmt->fetch();
        
        $isRCService = false;
        $mainServiceId = null;
        
        if ($rcCheckResult && !empty($rcCheckResult['main_service_id'])) {
            // This is an RC service, verify service_type
            $serviceTypeSql = "SELECT service_type FROM service WHERE service_id = :sid";
            $serviceTypeStmt = $db->prepare($serviceTypeSql);
            $serviceTypeStmt->bindParam(':sid', $rcCheckResult['main_service_id']);
            $serviceTypeStmt->execute();
            $serviceTypeResult = $serviceTypeStmt->fetch();
            
            if ($serviceTypeResult && $serviceTypeResult['service_type'] == 'RC') {
                $isRCService = true;
                $mainServiceId = $rcCheckResult['main_service_id'];
            }
        }
        
        if ($isRCService) {
            error_log("CDC ISO MERGE: RC service detected (rc_id=" . $_GET['cdcservice_ID'] . ", service_id=" . $mainServiceId . "), fetching ISOs from all services for customer " . $_GET['cdccustomer_ID']);
            
            // FIRST: Get the agreement_no for this RC service
            $agreementSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid AND service = :sid AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1";
            $agreementStmt = $db->prepare($agreementSql);
            $agreementStmt->bindParam(':cid', $_GET['cdccustomer_ID']);
            $agreementStmt->bindParam(':sid', $mainServiceId);
            $agreementStmt->execute();
            $agreementResult = $agreementStmt->fetch();
            
            $agreementNo = null;
            if ($agreementResult && !empty($agreementResult['aggrement_no'])) {
                // Remove any RC suffix to get base agreement number
                $agreementNo = preg_replace('/\s+Rc-\d+$/', '', $agreementResult['aggrement_no']);
                error_log("CDC ISO MERGE: Found agreement_no for RC: " . $agreementNo);
            }
            
            // Get all services for this customer AND agreement (CA, SA-I, SA-II, RC)
            // Filter by agreement_no to ensure we only get services from the SAME agreement cycle
            if ($agreementNo) {
                $allServicesSql = "SELECT DISTINCT s.service_id, s.service_type 
                                  FROM service s 
                                  INNER JOIN certification_aggrement ca ON ca.service = s.service_id AND ca.customer_id = s.customer_id
                                  WHERE s.customer_id = :cid 
                                  AND s.service_type IN ('CA', 'SA-I', 'SA-II', 'RC')
                                  AND (ca.aggrement_no = :agreement OR ca.aggrement_no LIKE :agreement_with_rc)
                                  ORDER BY s.service_id ASC";
                $allServicesStmt = $db->prepare($allServicesSql);
                $allServicesStmt->bindParam(':cid', $_GET['cdccustomer_ID']);
                $allServicesStmt->bindParam(':agreement', $agreementNo);
                $agreementWithRc = $agreementNo . ' Rc-%';
                $allServicesStmt->bindParam(':agreement_with_rc', $agreementWithRc);
                $allServicesStmt->execute();
                $allServices = $allServicesStmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("CDC ISO MERGE: Found " . count($allServices) . " services for customer with agreement_no=" . $agreementNo);
            } else {
                // Fallback: If no agreement found, use customer_id only (old behavior)
                error_log("CDC ISO MERGE: ⚠ No agreement_no found, falling back to customer_id filter");
                $allServicesSql = "SELECT DISTINCT s.service_id, s.service_type 
                                  FROM service s 
                                  WHERE s.customer_id = :cid 
                                  AND s.service_type IN ('CA', 'SA-I', 'SA-II', 'RC')
                                  ORDER BY s.service_id ASC";
                $allServicesStmt = $db->prepare($allServicesSql);
                $allServicesStmt->bindParam(':cid', $_GET['cdccustomer_ID']);
                $allServicesStmt->execute();
                $allServices = $allServicesStmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("CDC ISO MERGE: Found " . count($allServices) . " services for customer (no agreement filter)");
            }
            
            $allIsoStandards = [];
            
            foreach ($allServices as $svc) {
                error_log("CDC ISO MERGE: Processing service_id=" . $svc['service_id'] . ", type=" . $svc['service_type']);
                
                $typeSpecificId = null;
                
                // Get the type-specific ID based on service type
                // IMPORTANT: RC ISOs are stored differently than CA/SA!
                // CA/SA use type-specific IDs (ca_id, sai_id, saii_id)
                // RC uses MAIN service_id directly!
                
                if ($svc['service_type'] == 'CA') {
                    // For CA services, get ca_id from service_type_ca
                    $caSql = "SELECT ca_id FROM service_type_ca WHERE service = :sid";
                    $caStmt = $db->prepare($caSql);
                    $caStmt->bindParam(':sid', $svc['service_id']);
                    $caStmt->execute();
                    $caResult = $caStmt->fetch();
                    if ($caResult) {
                        $typeSpecificId = $caResult['ca_id'];
                        error_log("CDC ISO MERGE:   - Found CA ca_id: " . $typeSpecificId);
                    }
                } elseif ($svc['service_type'] == 'SA-I') {
                    // For SA-I services, get sai_id from service_type_sai
                    $saiSql = "SELECT sai_id FROM service_type_sai WHERE service = :sid";
                    $saiStmt = $db->prepare($saiSql);
                    $saiStmt->bindParam(':sid', $svc['service_id']);
                    $saiStmt->execute();
                    $saiResult = $saiStmt->fetch();
                    if ($saiResult) {
                        $typeSpecificId = $saiResult['sai_id'];
                        error_log("CDC ISO MERGE:   - Found SA-I sai_id: " . $typeSpecificId);
                    }
                } elseif ($svc['service_type'] == 'SA-II') {
                    // For SA-II services, get saii_id from service_type_saii
                    $saiiSql = "SELECT saii_id FROM service_type_saii WHERE service = :sid";
                    $saiiStmt = $db->prepare($saiiSql);
                    $saiiStmt->bindParam(':sid', $svc['service_id']);
                    $saiiStmt->execute();
                    $saiiResult = $saiiStmt->fetch();
                    if ($saiiResult) {
                        $typeSpecificId = $saiiResult['saii_id'];
                        error_log("CDC ISO MERGE:   - Found SA-II saii_id: " . $typeSpecificId);
                    }
                } elseif ($svc['service_type'] == 'RC') {
                    // For RC services pull the rc_id from service_type_rc
                    $rcIdSql = "SELECT rc_id FROM service_type_rc WHERE service = :sid LIMIT 1";
                    $rcIdStmt = $db->prepare($rcIdSql);
                    $rcIdStmt->bindParam(':sid', $svc['service_id']);
                    $rcIdStmt->execute();
                    $rcIdResult = $rcIdStmt->fetch();
                    
                    if ($rcIdResult && !empty($rcIdResult['rc_id'])) {
                        $typeSpecificId = $rcIdResult['rc_id'];
                        error_log("CDC ISO MERGE:   - Found RC rc_id: " . $typeSpecificId . " (service_id=" . $svc['service_id'] . ")");
                    } else {
                        // Fallback to main serrvice id so that older data still works
                        $typeSpecificId = $svc['service_id'];
                        error_log("CDC ISO MERGE:   - ⚠ rc_id missing, falling back to service_id: " . $typeSpecificId);
                    }
                }
                
                // Now fetch ISO standards using the type-specific ID
                if ($typeSpecificId) {
                    // Fetch from audit_application
                    $isoSql = "SELECT certification, assessment_standard FROM audit_application WHERE service_id = :sid";
                    $isoStmt = $db->prepare($isoSql);
                    $isoStmt->bindParam(':sid', $typeSpecificId);
                    $isoStmt->execute();
                    $isoData = $isoStmt->fetch();
                    
                    if ($isoData) {
                        // Collect from certification field
                        if (!empty($isoData['certification'])) {
                            $allIsoStandards[] = $isoData['certification'];
                            error_log("CDC ISO MERGE:   - certification: " . $isoData['certification']);
                        }
                        // Collect from assessment_standard field
                        if (!empty($isoData['assessment_standard'])) {
                            $allIsoStandards[] = $isoData['assessment_standard'];
                            error_log("CDC ISO MERGE:   - assessment_standard: " . $isoData['assessment_standard']);
                        }
                    } else {
                        error_log("CDC ISO MERGE:   - ⚠ No data found in audit_application for service_id=" . $typeSpecificId);
                    }
                    
                    // Also check audit_schedule_id table
                    $scheduleSql = "SELECT schedule_app_standard FROM audit_schedule_id WHERE serviceca_id = :sid AND schedule_app_standard IS NOT NULL AND schedule_app_standard != ''";
                    $scheduleStmt = $db->prepare($scheduleSql);
                    $scheduleStmt->bindParam(':sid', $typeSpecificId);
                    $scheduleStmt->execute();
                    $scheduleResults = $scheduleStmt->fetchAll(PDO::FETCH_COLUMN);
                    if ($scheduleResults) {
                        foreach ($scheduleResults as $scheduleIso) {
                            $allIsoStandards[] = $scheduleIso;
                            error_log("CDC ISO MERGE:   - schedule_app_standard: " . $scheduleIso);
                        }
                    }

                    // RC certificate scopes are saved against rc_id
                    if ($svc['service_type'] == 'RC') {
                        $rcCertSql = "SELECT certificate_scope FROM certificate_print WHERE service_id = :sid AND certificate_scope IS NOT NULL AND certificate_scope != ''";
                        $rcCertStmt = $db->prepare($rcCertSql);
                        $rcCertStmt->bindParam(':sid', $typeSpecificId);
                        $rcCertStmt->execute();
                        $rcCertResults = $rcCertStmt->fetchAll(PDO::FETCH_COLUMN);
                        if ($rcCertResults) {
                            foreach ($rcCertResults as $certIso) {
                                $allIsoStandards[] = $certIso;
                                error_log("CDC ISO MERGE:   - rc certificate_scope: " . $certIso);
                            }
                        }
                    }
                } else {
                    error_log("CDC ISO MERGE:   - ⚠ Could not find type-specific ID for this service");
                }
            }
            
            // ADDITIONAL: Also fetch from the CURRENT RC service being viewed (as a fallback)
            // This ensures we never miss ISOs from the current RC service
            // NOTE: RC ISOs use MAIN service_id, so we need to use mainServiceId (not rc_id)!
            error_log("CDC ISO MERGE: Also checking CURRENT RC service (rc_id=" . $_GET['cdcservice_ID'] . ", main_service_id=" . $mainServiceId . ")");
            $currentRcIsoSql = "SELECT certification, assessment_standard FROM audit_application WHERE service_id = :sid";
            $currentRcIsoStmt = $db->prepare($currentRcIsoSql);
            $currentRcIsoStmt->bindParam(':sid', $_GET['cdcservice_ID']); // rc_id
            $currentRcIsoStmt->execute();
            $currentRcIsoData = $currentRcIsoStmt->fetch();
            
            if (!$currentRcIsoData && $mainServiceId) {
                // Fallback for legacy records that still use main service id
                $currentRcIsoStmt = $db->prepare($currentRcIsoSql);
                $currentRcIsoStmt->bindParam(':sid', $mainServiceId);
                $currentRcIsoStmt->execute();
                $currentRcIsoData = $currentRcIsoStmt->fetch();
            }
            
            if ($currentRcIsoData) {
                if (!empty($currentRcIsoData['certification'])) {
                    $allIsoStandards[] = $currentRcIsoData['certification'];
                    error_log("CDC ISO MERGE:   - CURRENT RC certification: " . $currentRcIsoData['certification']);
                }
                if (!empty($currentRcIsoData['assessment_standard'])) {
                    $allIsoStandards[] = $currentRcIsoData['assessment_standard'];
                    error_log("CDC ISO MERGE:   - CURRENT RC assessment_standard: " . $currentRcIsoData['assessment_standard']);
                }
            } else {
                error_log("CDC ISO MERGE:   - ⚠ No ISO data found in audit_application for rc_id/main_service_id");
            }
            
            // Also check certificate_print for current RC (use main service_id)
            $currentRcCertSql = "SELECT certificate_scope FROM certificate_print WHERE service_id = :sid AND certificate_scope IS NOT NULL AND certificate_scope != ''";
            $currentRcCertStmt = $db->prepare($currentRcCertSql);
            $currentRcCertStmt->bindParam(':sid', $_GET['cdcservice_ID']);
            $currentRcCertStmt->execute();
            $currentRcCertResults = $currentRcCertStmt->fetchAll(PDO::FETCH_COLUMN);
            if ((!$currentRcCertResults || count($currentRcCertResults) === 0) && $mainServiceId) {
                $currentRcCertStmt = $db->prepare($currentRcCertSql);
                $currentRcCertStmt->bindParam(':sid', $mainServiceId);
                $currentRcCertStmt->execute();
                $currentRcCertResults = $currentRcCertStmt->fetchAll(PDO::FETCH_COLUMN);
            }
            if ($currentRcCertResults) {
                foreach ($currentRcCertResults as $certIso) {
                    $allIsoStandards[] = $certIso;
                    error_log("CDC ISO MERGE:   - CURRENT RC certificate_scope: " . $certIso);
                }
            }
            
            // Parse and deduplicate ISO standards
            $uniqueIsos = [];
            foreach ($allIsoStandards as $isoString) {
                if (empty($isoString)) continue;
                
                // Split by various delimiters: comma, newline, slash, ampersand
                $parts = preg_split('/[,;\n\/&]+/', $isoString);
                foreach ($parts as $part) {
                    $cleaned = trim($part);
                    if (!empty($cleaned)) {
                        $uniqueIsos[$cleaned] = true; // Using array key to auto-deduplicate
                    }
                }
            }
            
            // Build the merged ISO list
            $mergedIsoList = implode(', ', array_keys($uniqueIsos));
            
            error_log("CDC ISO MERGE: Found " . count($uniqueIsos) . " unique ISO standards across all services");
            error_log("CDC ISO MERGE: Merged list: " . $mergedIsoList);
            
            // IMPORTANT: Store merged list in a SEPARATE field, do NOT override original RC ISO data
            // Original certification/assessment_standard should contain RC-specific ISOs only (for Audit Plan, headers, etc.)
            // Merged list will be used ONLY for CDC decision blocks
            $auditInfo['merged_iso_list'] = !empty($mergedIsoList) ? $mergedIsoList : '';
            
            error_log("CDC ISO MERGE: ✓ Added merged ISO list as separate field (merged_iso_list)");
            error_log("CDC ISO MERGE: Original RC certification field preserved: " . ($auditInfo['certification'] ?? 'EMPTY'));
        } else {
            error_log("CDC ISO MERGE: Not an RC service, using standard ISO logic");
        }


        $auditNoti = "SELECT * FROM stage_twoaudit_id WHERE service_id = :service and customer_id = :id";
        $statement = $db->prepare($auditNoti);
        $statement->bindParam(':id', $_GET['cdccustomer_ID']);
        $statement->bindParam(':service', $_GET['cdcservice_ID']);
        $statement->execute();
        $auditDetails = $statement->fetch();

        $leadAuditor = "SELECT * FROM stage_audit_two WHERE service_id = :service and (`auditor_category` LIKE '%auditor%' OR `auditor_category` LIKE '%team leader%')";
        $statement = $db->prepare($leadAuditor);
        $statement->bindParam(':service', $_GET['cdcservice_ID']);
        $statement->execute();
        $lead_Auditor = $statement->fetchAll();

        $notiSchidule = "SELECT * FROM audit_schedule_id where serviceca_id = :id";
        $statement = $db->prepare($notiSchidule);
        $statement->bindParam(":id", $_GET['cdcservice_ID']);
        $statement->execute();
        $notiSchedul = $statement->fetch();

        $auditNoti2 = "SELECT * FROM stage_oneaudit_id where ca_service_id = :id";
        $statement = $db->prepare($auditNoti2);
        $statement->bindParam(":id", $_GET['cdcservice_ID']);
        $statement->execute();
        $auditDate = $statement->fetch();

        $notificationRows = "SELECT * FROM stage_audit_one WHERE audit_stage_id = :id and (`auditor_category` LIKE '%auditor%' OR `auditor_category` LIKE '%team leader%')";
        $statement = $db->prepare($notificationRows);
        $statement->bindParam(':id', $auditDate["audit_stage_id"]);
        $statement->execute();
        $notificationInfo = $statement->fetchAll();

        $certPrintDate = "SELECT registration_date FROM certificate_print WHERE service_id = :svc";
        $statement = $db->prepare($certPrintDate);
        $statement->bindParam(":svc", $_GET['cdcservice_ID']);
        $statement->execute();
        $certDate = $statement->fetch();

        $certficationno = "SELECT yearofestablish, certification_no FROM consulting_firm";
        $statement = $db->prepare($certficationno);
        $statement->execute();
        $no = $statement->fetch();

        $sql = "SELECT * FROM cdc_report_review WHERE ca_service_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET['cdcservice_ID']);
        $statement->execute();
        $ca_cdcreport = $statement->fetch();
//        echo json_encode($ca_cdcreport['crr_id']);echo "</br>";

        $notiSchiduleii = "SELECT * FROM audit_scheduleii_id where ca_service = :id";
        $statement = $db->prepare($notiSchiduleii);
        $statement->bindParam(":id", $_GET['cdcservice_ID']);
        $statement->execute();
        $notiSchedulii = $statement->fetch();

        $sql = "SELECT * FROM `stage_oneaudit_id` where ca_service_id = :svc";
        $statement = $db->prepare($sql);
        $statement->bindParam(":svc", $_GET['cdcservice_ID']);
        $statement->execute();
        $auditdatelist = $statement->fetch();

        $sql1 = "SELECT max(id) as id FROM `certification_aggrement` WHERE `customer_id` = :cid order by id desc limit 1";
        $statement = $db->prepare($sql1);
        $statement->bindParam(":cid", $_GET['cdccustomer_ID']);
        $statement->execute();
        $maxid = $statement->fetch();

        $sql2 = "SELECT aggrement_no FROM `certification_aggrement` where id = :id";
        $statement = $db->prepare($sql2);
        $statement->bindParam(":id", $maxid['id']);
        $statement->execute();
        $maxcid = $statement->fetch();
        
        $rev1 = "SELECT review_date FROM `stage_i_audit_id` WHERE `customer_id` = :cid AND ca_service_id = :svc";
        $statement = $db->prepare($rev1);
        $statement->bindParam(":cid", $_GET['cdccustomer_ID']);
        $statement->bindParam(":svc", $_GET['cdcservice_ID']);
        $statement->execute();
        $rev1date = $statement->fetch();
        
        $rev2 = "SELECT review_date FROM `stage_ii_audit_id` WHERE `customer_id` = :cid AND ca_service_id = :svc";
        $statement = $db->prepare($rev2);
        $statement->bindParam(":cid", $_GET['cdccustomer_ID']);
        $statement->bindParam(":svc", $_GET['cdcservice_ID']);
        $statement->execute();
        $rev2date = $statement->fetch();
        
        $rcCount = "SELECT COUNT(service_id) as id FROM `service` WHERE service_type = 'RC' AND customer_id = :cid";
        $statement = $db->prepare($rcCount);
        $statement->bindParam(":cid", $_GET['cdccustomer_ID']);
        $statement->execute();
        $rcSvcCount = $statement->fetch();

//        echo "in -> ".$maxid['id']. $maxcid["aggrement_no"];
//        print_r($auditdatelist);
//        print_r($notiSchedul); echo "</br>";
        outputJSON(array($customerDetails, $auditInfo, $auditDetails, $lead_Auditor, $notiSchedul, $auditDate, $certDate, $no, $ca_cdcreport, $notificationInfo, $notiSchedulii, $auditdatelist, $maxcid, $rev1date, $rev2date, $rcSvcCount,$customerDetailsx, $customerDetailsy));
    } catch (Exception $ex) {
        http_response_code(500);
        outputJSON(array('error' => true, 'message' => $ex->getMessage()));
    }
} else if (isset($_GET['cfcustomer_id']) && !empty($_GET['cfcustomer_id']) && isset($_GET['cfservice_id']) && !empty($_GET['cfservice_id'])) {
    try {
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['cfcustomer_id']);
        $statement->execute();
        $customerDetails = $statement->fetch();

        $auditNoti = "SELECT * FROM stage_twoaudit_id WHERE service_id = :service and customer_id = :id";
        $statement = $db->prepare($auditNoti);
        $statement->bindParam(':id', $_GET['cfcustomer_id']);
        $statement->bindParam(':service', $_GET['cfservice_id']);
        $statement->execute();
        $auditDetails = $statement->fetch();

        $cdcInfo = "SELECT * FROM cdc_report_review WHERE ca_service_id = :service and customer_id = :id";
        $statement = $db->prepare($cdcInfo);
        $statement->bindParam(':id', $_GET['cfcustomer_id']);
        $statement->bindParam(':service', $_GET['cfservice_id']);
        $statement->execute();
        $cdcDetail = $statement->fetch();

        $auditNoti2 = "SELECT * FROM stage_oneaudit_id where ca_service_id = :service";
        $statement = $db->prepare($auditNoti2);
        $statement->bindParam(':service', $_GET['cfservice_id']);
        $statement->execute();
        $auditNoti2Info = $statement->fetch();

        $regDate = "SELECT registration_date FROM certificate_print where service_id = :svc";
        $statement = $db->prepare($regDate);
        $statement->bindParam(":svc", $_GET['cfservice_id']);
        $statement->execute();
        $cfRegDate = $statement->fetch();

        outputJSON(array($customerDetails, $auditDetails, $cdcDetail, $auditNoti2Info, $cfRegDate));
    } catch (Exception $ex) {
        http_response_code(500);
        outputJSON(array('error' => true, 'message' => $ex->getMessage()));
    }
} else if (isset($_GET['customerIDAnx']) && !empty($_GET['customerIDAnx']) && isset($_GET['serviceIDAnx']) && !empty($_GET['serviceIDAnx'])) {
    try {
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['customerIDAnx']);
        $statement->execute();
        $customerDetails = $statement->fetch();

        $auditNoti = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :id";
        $statement = $db->prepare($auditNoti);
        $statement->bindParam(':id', $_GET['customerIDAnx']);
        $statement->bindParam(':service', $_GET['serviceIDAnx']);
        $statement->execute();
        $auditDetails = $statement->fetch();

        $leadAuditor = "SELECT * FROM stage_audit_one WHERE ca_service_id = :service limit 1";
        $statement = $db->prepare($leadAuditor);
        $statement->bindParam(':service', $_GET['serviceIDAnx']);
        $statement->execute();
        $lead_Auditor = $statement->fetch();

        $stage1Audit = "SELECT * FROM audit_schedule_id WHERE serviceca_id = :service";
        $statement = $db->prepare($stage1Audit);
        $statement->bindParam(':service', $_GET['serviceIDAnx']);
        $statement->execute();
        $stage_audit = $statement->fetch();

        $sql1 = "SELECT max(id) as id FROM `certification_aggrement` WHERE `customer_id` = :cid order by id desc limit 1";
        $statement = $db->prepare($sql1);
        $statement->bindParam(":cid", $_GET['customerIDAnx']);
        $statement->execute();
        $maxid = $statement->fetch();

        $sql2 = "SELECT aggrement_no FROM `certification_aggrement` where id = :id";
        $statement = $db->prepare($sql2);
        $statement->bindParam(":id", $maxid['id']);
        $statement->execute();
        $maxcid = $statement->fetch();
        $customer = "SELECT * FROM stage_audit_one WHERE ca_service_id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['serviceIDAnx']);
        $statement->execute();
        $customerDetailsx = $statement->fetchAll(PDO::FETCH_ASSOC);
        
            $customer = "SELECT * FROM stage_audit_two WHERE service_id = :id";
            $statement = $db->prepare($customer);
            $statement->bindParam(':id', $_GET['serviceIDAnx']);
            $statement->execute();
            $customerDetailsy = $statement->fetchAll(PDO::FETCH_ASSOC);
//        echo json_encode($lead_Auditor);
        outputJSON(array($customerDetails, $auditDetails, $lead_Auditor, $stage_audit, $maxcid, $customerDetailsx ,$customerDetailsy));
    } catch (Exception $ex) {
        http_response_code(500);
        outputJSON(array('error' => true, 'message' => $ex->getMessage()));
    }
} else if (isset($_GET['cdcsaicustomer_ID']) && !empty($_GET['cdcsaicustomer_ID']) && isset($_GET['cdcsaiservice_ID']) && !empty($_GET['cdcsaiservice_ID'])) {
    try {
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['cdcsaicustomer_ID']);
        $statement->execute();
        $customerDetails = $statement->fetch();

        $auditApp = "SELECT * FROM audit_application WHERE service_id = :id";
        $statement = $db->prepare($auditApp);
        $statement->bindParam(':id', $_GET['crservice_ID']);
        $statement->execute();
        $auditInfo = $statement->fetch();

        $auditNoti = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :id";
        $statement = $db->prepare($auditNoti);
        $statement->bindParam(':id', $_GET['cdcsaicustomer_ID']);
        $statement->bindParam(':service', $_GET['cdcsaiservice_ID']);
        $statement->execute();
        $auditDetails = $statement->fetch();

        $leadAuditor = "SELECT * FROM stage_audit_one WHERE ca_service_id = :service limit 1";
        $statement = $db->prepare($leadAuditor);
        $statement->bindParam(':service', $_GET['cdcsaiservice_ID']);
        $statement->execute();
        $lead_Auditor = $statement->fetch();

        $auditor = "SELECT * FROM servelance_1_id WHERE service_id = :service";
        $statement = $db->prepare($auditor);
        $statement->bindParam(':service', $_GET['cdcsaiservice_ID']);
        $statement->execute();
        $auditor_list = $statement->fetch();

        $sql = "SELECT * FROM cdc_report_review WHERE customer_id = :id and ca_service_id = :service";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET['cdcsaicustomer_ID']);
        $statement->bindParam(':service', $_GET['caserviceid']);
        $statement->execute();
        $ca_cdcreport = $statement->fetch();
        outputJSON(array($customerDetails, $auditInfo, $auditDetails, $lead_Auditor, $ca_cdcreport, $auditor_list));
    } catch (Exception $ex) {
        http_response_code(500);
        outputJSON(array('error' => true, 'message' => $ex->getMessage()));
    }
} else if (isset($_GET['customer']) && !empty($_GET['customer']) && isset($_GET["idval"]) && !empty($_GET["idval"])) {
    try {
        $existing = "SELECT id, client_count FROM allotment_count WHERE client_id = :id LIMIT 1";
        $statement = $db->prepare($existing);
        $statement->bindParam(":id", $_GET['customer']);
        $statement->execute();
        $existingRow = $statement->fetch(PDO::FETCH_ASSOC);

        $date = date("d-m-Y", strtotime($_GET["idval"]));
        $orderdate = explode('-', $date);
        $month = $orderdate[1];
        $year = $orderdate[2];

        if ($existingRow && isset($existingRow['client_count'])) {
            // Check if year and month are unchanged
            $existingAllotmentQuery = "SELECT year, month FROM allotment_count WHERE client_id = :id LIMIT 1";
            $statement = $db->prepare($existingAllotmentQuery);
            $statement->bindParam(":id", $_GET['customer']);
            $statement->execute();
            $existingAllotment = $statement->fetch(PDO::FETCH_ASSOC);
            
            if ($existingAllotment && $existingAllotment['year'] == $year && $existingAllotment['month'] == $month) {
                $hasAgreement = "SELECT COUNT(*) as cnt FROM certification_aggrement WHERE customer_id = :id";
                $statement = $db->prepare($hasAgreement);
                $statement->bindParam(":id", $_GET['customer']);
                $statement->execute();
                $agreementRow = $statement->fetch(PDO::FETCH_ASSOC);

                if (!empty($agreementRow) && (int)$agreementRow['cnt'] > 0) {
                    outputJSON((int)$existingRow['client_count']);
                    exit;
                }
            }

            $cleanup = "DELETE FROM allotment_count WHERE client_id = :id";
            $statement = $db->prepare($cleanup);
            $statement->bindParam(":id", $_GET['customer']);
            $statement->execute();
        }

        // Generate robust sequence number using helper function
        $counter = getNextAllotmentSequence($db, $_GET['customer'], $year, $month);

        outputJSON($counter);
//        echo json_encode($counter);
    } catch (Exception $ex) {
        http_response_code(500);
        outputJSON(array('error' => true, 'message' => $ex->getMessage()));
    }
}else if (isset($_GET['customer1']) && !empty($_GET['customer1']) && isset($_GET["idval1"]) && !empty($_GET["idval1"])) {
    try {
        $count = "SELECT count(id) as id FROM allotment_count where client_id = :id";
        $statement = $db->prepare($count);
        $statement->bindParam(":id", $_GET['customer1']);
        $statement->execute();
        $counter = $statement->fetch();
        if ($counter['id'] > 0) {
            // Return existing certification date for this client
            $existing = "SELECT certification_date FROM allotment_count WHERE client_id = :id";
            $statement = $db->prepare($existing);
            $statement->bindParam(":id", $_GET['customer1']);
            $statement->execute();
            $existingData = $statement->fetch();
            outputJSON($existingData['certification_date']);
        } else {
            	    $date = date("d-m-Y", strtotime($_GET["idval1"]));
            $orderdate = explode('-', $date);
            $month = $orderdate[1];
            $day = $orderdate[0];
            $year = $orderdate[2];
            $clientCount = "SELECT MAX(id) as max_id FROM `allotment_count` WHERE year = :year AND month = :month";
//            $clientCount = "SELECT client_count FROM allotment_count WHERE year= :year and month = :month ORDER BY client_count DESC LIMIT 1";
            $statement = $db->prepare($clientCount);
            $statement->bindParam(":year", $year);
            $statement->bindParam(":month", $month);
            $statement->execute();
            $count = $statement->fetch();

            // For certification date, we need to ensure the allotment record exists
            $certCounter = 1;
            
            if ($count["max_id"] !== null) {
                $lastCount = "SELECT client_count FROM allotment_count WHERE id = :id";
                $statement = $db->prepare($lastCount);
                $statement->bindParam(":id", $count["max_id"]);
                $statement->execute();
                $lastCountVal = $statement->fetch();
                $certCounter = ($lastCountVal && $lastCountVal["client_count"]) ? $lastCountVal["client_count"] + 1 : 1;
            }
            
            // Insert or update the allotment record with certification date
            $insertOrUpdate = "INSERT INTO allotment_count (client_id, client_count, year, month, certification_date) VALUES (:client_id, :client_count, :year, :month, :cert_date) ON DUPLICATE KEY UPDATE certification_date = :cert_date2";
            $statement = $db->prepare($insertOrUpdate);
            $statement->bindParam(":client_id", $_GET['customer1']);
            $statement->bindParam(":client_count", $certCounter);
            $statement->bindParam(":year", $year);
            $statement->bindParam(":month", $month);
            $statement->bindParam(":cert_date", $_GET["idval1"]);
            $statement->bindParam(":cert_date2", $_GET["idval1"]);
            $statement->execute();
            
            outputJSON($_GET["idval1"]);
        }
//        echo json_encode($counter);
    } catch (Exception $ex) {
        http_response_code(500);
        outputJSON(array('error' => true, 'message' => $ex->getMessage()));
    }
} else if (isset($_GET['customid']) && !empty($_GET['customid']) && isset($_GET['svcID']) && !empty($_GET['svcID'])) {
    try {
        error_log("ASSESSMENT PLANNING ISO: Loading for customer=" . $_GET['customid'] . ", svcID=" . $_GET['svcID']);
        
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['customid']);
        $statement->execute();
        $customerDetails = $statement->fetch();

        // CRITICAL FIX: Check if this is an RC service - same issue as CDC form!
        // For RC: svcID is rc_id, but audit_application uses main service_id
        // For CA/SA: svcID is already the correct type-specific ID (ca_id, sai_id, saii_id)
        
        $serviceIdToUse = $_GET['svcID'];
        
        // Check if svcID is from service_type_rc table
        $rcCheckSql = "SELECT rc.service as main_service_id, s.service_type 
                       FROM service_type_rc rc 
                       INNER JOIN service s ON rc.service = s.service_id
                       WHERE rc.rc_id = :rc_id";
        $rcCheckStmt = $db->prepare($rcCheckSql);
        $rcCheckStmt->bindParam(':rc_id', $_GET['svcID']);
        $rcCheckStmt->execute();
        $rcCheckResult = $rcCheckStmt->fetch();
        
        if ($rcCheckResult && $rcCheckResult['service_type'] == 'RC') {
            // This is an RC service - use main service_id, not rc_id!
            $serviceIdToUse = $rcCheckResult['main_service_id'];
            error_log("ASSESSMENT PLANNING ISO: RC service detected! Using main service_id=" . $serviceIdToUse . " instead of rc_id=" . $_GET['svcID']);
            
            // Also fetch ISOs from all services (CA, SA-I, SA-II, RC) for this customer
            $allServicesSql = "SELECT DISTINCT s.service_id, s.service_type 
                              FROM service s 
                              WHERE s.customer_id = :cid 
                              AND s.service_type IN ('CA', 'SA-I', 'SA-II', 'RC')
                              ORDER BY s.service_id ASC";
            $allServicesStmt = $db->prepare($allServicesSql);
            $allServicesStmt->bindParam(':cid', $_GET['customid']);
            $allServicesStmt->execute();
            $allServices = $allServicesStmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("ASSESSMENT PLANNING ISO: Found " . count($allServices) . " services for customer");
            
            $allIsoStandards = [];
            
            foreach ($allServices as $svc) {
                $typeSpecificId = null;
                
                if ($svc['service_type'] == 'CA') {
                    $caSql = "SELECT ca_id FROM service_type_ca WHERE service = :sid";
                    $caStmt = $db->prepare($caSql);
                    $caStmt->bindParam(':sid', $svc['service_id']);
                    $caStmt->execute();
                    $caResult = $caStmt->fetch();
                    if ($caResult) {
                        $typeSpecificId = $caResult['ca_id'];
                    }
                } elseif ($svc['service_type'] == 'SA-I') {
                    $saiSql = "SELECT sai_id FROM service_type_sai WHERE service = :sid";
                    $saiStmt = $db->prepare($saiSql);
                    $saiStmt->bindParam(':sid', $svc['service_id']);
                    $saiStmt->execute();
                    $saiResult = $saiStmt->fetch();
                    if ($saiResult) {
                        $typeSpecificId = $saiResult['sai_id'];
                    }
                } elseif ($svc['service_type'] == 'SA-II') {
                    $saiiSql = "SELECT saii_id FROM service_type_saii WHERE service = :sid";
                    $saiiStmt = $db->prepare($saiiSql);
                    $saiiStmt->bindParam(':sid', $svc['service_id']);
                    $saiiStmt->execute();
                    $saiiResult = $saiiStmt->fetch();
                    if ($saiiResult) {
                        $typeSpecificId = $saiiResult['saii_id'];
                    }
                } elseif ($svc['service_type'] == 'RC') {
                    // RC uses MAIN service_id directly!
                    $typeSpecificId = $svc['service_id'];
                }
                
                if ($typeSpecificId) {
                    $isoSql = "SELECT certification, assessment_standard FROM audit_application WHERE service_id = :sid";
                    $isoStmt = $db->prepare($isoSql);
                    $isoStmt->bindParam(':sid', $typeSpecificId);
                    $isoStmt->execute();
                    $isoData = $isoStmt->fetch();
                    
                    if ($isoData) {
                        if (!empty($isoData['certification'])) {
                            $allIsoStandards[] = $isoData['certification'];
                            error_log("ASSESSMENT PLANNING ISO:   - " . $svc['service_type'] . " certification: " . $isoData['certification']);
                        }
                        if (!empty($isoData['assessment_standard'])) {
                            $allIsoStandards[] = $isoData['assessment_standard'];
                        }
                    }
                }
            }
            
            // Deduplicate ISOs
            $uniqueIsos = [];
            foreach ($allIsoStandards as $isoString) {
                if (empty($isoString)) continue;
                $parts = preg_split('/[,;\n\/&]+/', $isoString);
                foreach ($parts as $part) {
                    $cleaned = trim($part);
                    if (!empty($cleaned)) {
                        $uniqueIsos[$cleaned] = true;
                    }
                }
            }
            
            $mergedIsoList = implode(', ', array_keys($uniqueIsos));
            error_log("ASSESSMENT PLANNING ISO: Merged list: " . $mergedIsoList);
        }

        $auditApp = "SELECT * FROM audit_application WHERE service_id = :id";
        $statement = $db->prepare($auditApp);
        $statement->bindParam(':id', $serviceIdToUse);
        $statement->execute();
        $auditInfo = $statement->fetch();
        
        // If RC and we have merged ISO list, override the certification field
        if (isset($mergedIsoList) && !empty($mergedIsoList)) {
            $auditInfo['certification'] = $mergedIsoList;
            $auditInfo['assessment_standard'] = $mergedIsoList;
            error_log("ASSESSMENT PLANNING ISO: ✓ Overrode certification with merged list");
        }

        $sql1 = "SELECT * FROM `audit_iaf_nace` where audit_id = :id";
        $statement = $db->prepare($sql1);
        $statement->bindParam(':id', $auditInfo["audit_id"]);
        $statement->execute();
        $iafinfo = $statement->fetch();

        // Check if the current service is RC type for agreement number suffix
        $isAuditNotiRCService = ($rcCheckResult && $rcCheckResult['service_type'] == 'RC');
        
        $sql1 = "SELECT * FROM `certification_aggrement` WHERE `customer_id` = :cid order by id desc limit 1";
        $statement = $db->prepare($sql1);
        $statement->bindParam(":cid", $_GET['customid']);
        $statement->execute();
        $maxid = $statement->fetch();

        $sql2 = "SELECT aggrement_no FROM `certification_aggrement` where id = :id";
        $statement = $db->prepare($sql2);
        $statement->bindParam(":id", $maxid['id']);
        $statement->execute();
        $maxcid = $statement->fetch();
        
        // For RC services, append Rc-1 suffix to agreement number
        if ($isAuditNotiRCService) {
            if ($maxcid && !empty($maxcid['aggrement_no'])) {
                // Get the original base allotment from CA contract review (without any RC suffix)
                $originalAllotmentSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid AND aggrement_no NOT LIKE '% Rc-%' AND aggrement_no NOT LIKE '%-RC-%' ORDER BY id ASC LIMIT 1";
                $origStmt = $db->prepare($originalAllotmentSql);
                $origStmt->bindParam(':cid', $_GET['customid']);
                $origStmt->execute();
                $origResult = $origStmt->fetch();
                
                $baseAllotment = $maxcid['aggrement_no'];
                if ($origResult && !empty($origResult['aggrement_no'])) {
                    $baseAllotment = $origResult['aggrement_no'];
                }
                
                // Remove any existing RC suffix to ensure clean base number
                $baseAllotment = preg_replace('/\s+Rc-\d+$/', '', $baseAllotment);
                $baseAllotment = preg_replace('/-RC-\d+$/', '', $baseAllotment);
                
                // Append RC suffix with format: "{agreement_number} Rc-1"
                $maxcid['aggrement_no'] = $baseAllotment . ' Rc-1';
                error_log("ASSESSMENT PLANNING - ✓ Using RC allotment with suffix: " . $maxcid['aggrement_no']);
            } else {
                // NEW RC CLIENT: No agreement in database, use customer's base unique_id
                $baseUniqueId = $customerDetails['unique_id'] ?? '';
                if (!empty($baseUniqueId)) {
                    // Remove any existing RC suffix to ensure clean base number
                    $baseUniqueId = preg_replace('/\s+Rc-\d+$/', '', $baseUniqueId);
                    $baseUniqueId = preg_replace('/-RC-\d+$/', '', $baseUniqueId);
                    
                    // Create maxcid array if it doesn't exist
                    if (!$maxcid) {
                        $maxcid = array();
                    }
                    
                    // Append RC suffix with format: "{unique_id} Rc-1"
                    $maxcid['aggrement_no'] = $baseUniqueId . ' Rc-1';
                    error_log("ASSESSMENT PLANNING - ✓ NEW RC CLIENT: Applied Rc-1 suffix to base unique_id: " . $maxcid['aggrement_no']);
                } else {
                    error_log("ASSESSMENT PLANNING - ✗ NEW RC CLIENT: No base unique_id found");
                }
            }
        }

        outputJSON(array($customerDetails, $auditInfo, $iafinfo, $maxcid));
    } catch (Exception $ex) {
        error_log("ASSESSMENT PLANNING ISO ERROR: " . $ex->getMessage());
        http_response_code(500);
        outputJSON(array('error' => true, 'message' => $ex->getMessage()));
    }
} else if (isset($_GET['cust_id']) && !empty($_GET['cust_id']) && isset($_GET['cdc_ID']) && !empty($_GET['cdc_ID'])) {
    try {
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['cust_id']);
        $statement->execute();
        $customerDetails = $statement->fetch();

        $cdc = "SELECT * FROM cdc_report_review WHERE ca_service_id = :id";
        $statement = $db->prepare($cdc);
        $statement->bindParam(':id', $_GET['cdc_ID']);
        $statement->execute();
        $cdcInfo = $statement->fetch();

        if ($cdcInfo && is_array($cdcInfo)) {
            $certificateDates = aitfBuildCertificatePrintDates(
                $db,
                $_GET['cust_id'],
                $_GET['cdc_ID'],
                null,
                isset($cdcInfo['audit_type']) ? $cdcInfo['audit_type'] : '',
                $cdcInfo
            );

            if (!empty($certificateDates)) {
                $cdcInfo['certificate_registration_date'] = $certificateDates['registration_date'];
                $cdcInfo['Date'] = $certificateDates['registration_date'];
                $cdcInfo['expiredate'] = $certificateDates['valid_until'];
                $cdcInfo['periodicalaudit'] = $certificateDates['before_date'];
                $cdcInfo['certificate_service_type'] = $certificateDates['service_type'];
            }
        }

        $stage2 = "SELECT * FROM `audit_scheduleii_id` WHERE `ca_service` = :id";
        $statement = $db->prepare($stage2);
        $statement->bindParam(":id", $_GET['cdc_ID']);
        $statement->execute();
        $stage2info = $statement->fetch();

        outputJSON(array($customerDetails, $cdcInfo, $stage2info));
    } catch (Exception $ex) {
        http_response_code(500);
        outputJSON(array('error' => true, 'message' => $ex->getMessage()));
    }
} else if (isset($_GET["taxation"]) && !empty($_GET["taxation"])) {
    try {
        $taxation = "SELECT * FROM taxation";
        $statement = $db->prepare($taxation);
        $statement->execute();
        $taxationDetails = $statement->fetchAll();
        outputJSON($taxationDetails);
    } catch (Exception $ex) {
        
    }
} else if (
        isset($_GET['downloadOptions']) && (int) $_GET['downloadOptions'] === 1 &&
        isset($_GET['crr_id']) && !empty($_GET['crr_id'])
    ) {
    try {
        include_once __DIR__ . '/include/cdc_office_use.php';

        $crrId = (int) $_GET['crr_id'];
        $contextStatement = $db->prepare(
            "SELECT customer_id, ca_service_id
             FROM cdc_report_review
             WHERE crr_id = :crr_id
             LIMIT 1"
        );
        $contextStatement->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
        $contextStatement->execute();
        $cdcContext = $contextStatement->fetch(PDO::FETCH_ASSOC);

        if ($cdcContext) {
            $latestStatement = $db->prepare(
                "SELECT source.crr_id
                 FROM cdc_report_review source
                 WHERE source.customer_id = :customer_id
                   AND source.ca_service_id = :service_id
                   AND EXISTS (
                       SELECT 1
                       FROM cdc_report_review_office_use office_use
                       WHERE office_use.crr_id = source.crr_id
                   )
                 ORDER BY source.crr_id DESC
                 LIMIT 1"
            );
            $latestStatement->bindValue(':customer_id', (int) $cdcContext['customer_id'], PDO::PARAM_INT);
            $latestStatement->bindValue(':service_id', (int) $cdcContext['ca_service_id'], PDO::PARAM_INT);
            $latestStatement->execute();
            $latestCrrId = (int) $latestStatement->fetchColumn();
            if ($latestCrrId > 0) {
                $crrId = $latestCrrId;
            }
        }

        $officeEntries = getCdcOfficeUse($db, $crrId);

        if (!empty($officeEntries)) {
            foreach ($officeEntries as &$officeEntry) {
                $officeEntry['crr_id'] = $crrId;
            }
            unset($officeEntry);
            echo json_encode($officeEntries);
            return;
        }

        $fallbackQuery = "SELECT certificateNo, revCdc FROM cdc_report_review WHERE crr_id = :crr";
        $statement = $db->prepare($fallbackQuery);
        $statement->bindParam(':crr', $crrId, PDO::PARAM_INT);
        $statement->execute();
        $fallback = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

        $certificate = $fallback['certificateNo'] ?? null;
        $revNo = $fallback['revCdc'] ?? null;

        if (!$certificate) {
            echo json_encode([]);
            return;
        }

        echo json_encode([
            [
                'iso_index' => 0,
                'iso_label' => null,
                'certificate_no' => $certificate,
                'rev_no' => $revNo
            ]
        ]);
    } catch (Exception $ex) {
        echo json_encode([]);
    }
} else if (isset($_GET['customer']) && !empty($_GET['customer']) && isset($_GET["consultingfirm"]) && !empty($_GET["consultingfirm"])) {
    try {
        $certno = "SELECT certificateNo FROM `cdc_report_review` WHERE crr_id = :id";
        $statement = $db->prepare($certno);
        $statement->bindParam(":id", $_GET['certid']);
        $statement->execute();
        $certis = $statement->fetch();

        $isoLabel = isset($_GET['iso_label']) ? trim($_GET['iso_label']) : '';
        $isoLabelsRaw = isset($_GET['iso_labels']) ? trim($_GET['iso_labels']) : '';
        $isoIndex = isset($_GET['iso_index']) ? (int) $_GET['iso_index'] : 0;

        $isoLabelList = [];
        if ($isoLabelsRaw !== '') {
            $isoLabelList = array_filter(array_map('trim', explode(',', $isoLabelsRaw)));
        }
        if (empty($isoLabelList) && $isoLabel !== '') {
            $isoLabelList[] = $isoLabel;
        }

        $forceRefresh = isset($_GET['refresh']) && (int) $_GET['refresh'] === 1;

        if(!empty($certis["certificateNo"]) && !$forceRefresh) {
            $storedCertificates = [];

            // Fetch office-use entries if available (multi ISO support)
            $officeUseQuery = "SELECT iso_index, iso_label, certificate_no FROM cdc_report_review_office_use WHERE crr_id = :crr ORDER BY iso_index ASC";
            $statement = $db->prepare($officeUseQuery);
            $statement->bindParam(':crr', $_GET['certid'], PDO::PARAM_INT);
            $statement->execute();
            $officeEntries = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($officeEntries)) {
                foreach ($officeEntries as $entry) {
                    $storedCertificates[] = [
                        'certificate_no' => $entry['certificate_no'],
                        'iso_index' => isset($entry['iso_index']) ? (int) $entry['iso_index'] : 0,
                        'iso_label' => $entry['iso_label'] ?? ''
                    ];
                }
            } else {
                $storedCertificates[] = [
                    'certificate_no' => $certis['certificateNo'],
                    'iso_index' => 0,
                    'iso_label' => $isoLabelList[0] ?? $isoLabel
                ];
            }

            echo json_encode($storedCertificates);
        } else {
            $firm = "SELECT * FROM `consulting_firm`";
            $statement = $db->prepare($firm);
            $statement->execute();
            $firmDetails = $statement->fetch();

            // Determine current master certificate sequence from consulting_firm
            $currentSequence = 0;
            if ($firmDetails && isset($firmDetails['certification_no'])) {
                $currentSequence = (int) $firmDetails['certification_no'];
            }

            // How many certificate numbers will be effectively used for this client
            $isoCount = !empty($isoLabelList) ? count($isoLabelList) : 1;

            // First sequence value to be used for this generation
            $nextSequence = $currentSequence + 1;

            // Last sequence that will be used (for the last ISO)
            $lastSequence = $nextSequence + ($isoCount - 1);

            // Persist the LAST used sequence back to the master (Master Creator)
            $updateMaster = "UPDATE consulting_firm SET certification_no = :no";
            $statement = $db->prepare($updateMaster);
            $statement->bindParam(':no', $lastSequence, PDO::PARAM_INT);
            $statement->execute();

            // Helper to generate a 12-digit certificate number for a given sequence:
            // 7 random digits + fixed "14" + 3-digit suffix derived from sequence
            $buildCertificate = function (int $sequence) {
                $suffix = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
                if (strlen($suffix) > 3) {
                    $suffix = substr($suffix, -3);
                }
                $random7 = rand(1000000, 9999999);
                return (string) $random7 . '14' . $suffix;
            };

            if (!empty($isoLabelList)) {
                $certificates = [];
                $sequence = $nextSequence;
                foreach ($isoLabelList as $idx => $label) {
                    $certificates[] = [
                        'certificate_no' => $buildCertificate($sequence),
                        'iso_index' => $idx,
                        'iso_label' => $label
                    ];
                    $sequence++;
                }
                echo json_encode($certificates);
            } else {
                echo json_encode([
                    'certificate_no' => $buildCertificate($nextSequence),
                    'iso_index' => $isoIndex,
                    'iso_label' => $isoLabel
                ]);
            }
        }
    } catch (Exception $ex) {
        
    }
}

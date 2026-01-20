<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if(isset($_GET['customerId']) && !empty($_GET['customerId']) && isset($_GET['contractId']) && !empty($_GET['contractId'])){
    try {
        $customer = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id',$_GET['customerId']);
        $statement->execute();
        $customerDetails = $statement->fetch();
        
        $contract = "SELECT * FROM contract_review WHERE ca_service_contract = :contractid";
        $statement = $db->prepare($contract);
        $statement->bindParam(':contractid', $_GET['contractId']);
        $statement->execute();
        $contractDetails = $statement->fetchAll();
        
        $contractId = "SELECT * FROM contract_id WHERE contractid = :id";
        $statement = $db->prepare($contractId);
        $statement->bindParam(':id',$_GET['contractId']);
        $statement->execute();
        $contractInfo = $statement->fetch();
        
        // Check if this is an RC service and apply RC-1 suffix to agreement number
        if ($contractInfo && isset($contractInfo['ca_service_id'])) {
            $serviceTypeSql = "SELECT service_type FROM service WHERE service_id = :service_id";
            $serviceTypeStmt = $db->prepare($serviceTypeSql);
            $serviceTypeStmt->bindParam(':service_id', $contractInfo['ca_service_id']);
            $serviceTypeStmt->execute();
            $serviceTypeResult = $serviceTypeStmt->fetch();
            
            if ($serviceTypeResult && $serviceTypeResult['service_type'] == 'RC') {
                error_log("contractDetails.php - RC Service detected, applying RC-1 suffix");
                
                // Get the original base allotment from CA contract review (without any RC suffix)
                $originalAllotmentSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid AND aggrement_no NOT LIKE '% Rc-%' AND aggrement_no NOT LIKE '%-RC-%' ORDER BY id ASC LIMIT 1";
                $origStmt = $db->prepare($originalAllotmentSql);
                $origStmt->bindParam(':cid', $_GET['customerId']);
                $origStmt->execute();
                $origResult = $origStmt->fetch();
                
                if ($origResult && !empty($origResult['aggrement_no'])) {
                    $baseAllotment = $origResult['aggrement_no'];
                    
                    // Remove any existing RC suffix to ensure clean base number
                    $baseAllotment = preg_replace('/\s+Rc-\d+$/', '', $baseAllotment);
                    $baseAllotment = preg_replace('/-RC-\d+$/', '', $baseAllotment);
                    
                    // Append RC suffix with format: "{agreement_number} Rc-1"
                    $customerDetails['unique_id'] = $baseAllotment . ' Rc-1';
                    error_log("contractDetails.php - Applied RC-1 suffix: " . $customerDetails['unique_id']);
                } else {
                    // Fallback: try to get the latest agreement for this customer
                    $fallbackSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :customer_id AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1";
                    $fallbackStmt = $db->prepare($fallbackSql);
                    $fallbackStmt->bindParam(':customer_id', $_GET['customerId']);
                    $fallbackStmt->execute();
                    $fallbackResult = $fallbackStmt->fetch();
                    
                    if ($fallbackResult && !empty($fallbackResult['aggrement_no'])) {
                        $baseAllotment = $fallbackResult['aggrement_no'];
                        
                        // Remove any existing RC suffix to ensure clean base number
                        $baseAllotment = preg_replace('/\s+Rc-\d+$/', '', $baseAllotment);
                        $baseAllotment = preg_replace('/-RC-\d+$/', '', $baseAllotment);
                        
                        // Append RC suffix with format: "{agreement_number} Rc-1"
                        $customerDetails['unique_id'] = $baseAllotment . ' Rc-1';
                        error_log("contractDetails.php - Applied RC-1 suffix (fallback): " . $customerDetails['unique_id']);
                    }
                }
            }
        }
        
        echo json_encode(array($customerDetails,$contractDetails,$contractInfo));
    } catch (Exception $ex) {
        echo $ex;
    }
} else if(isset ($_GET['notificationId']) && !empty ($_GET['notificationId']) && isset ($_GET['serviceid']) && !empty ($_GET['serviceid'])) {
    try {
        $notification = "SELECT * FROM stage_oneaudit_id WHERE audit_stage_id = :notification and ca_service_id = :service";
        $statement = $db->prepare($notification);
        $statement->bindParam(':notification', $_GET['notificationId']);
        $statement->bindParam(':service', $_GET['serviceid']);
        $statement->execute();
        $notificationDetails = $statement->fetch();
        
        $notificationRows = "SELECT * FROM stage_audit_one WHERE audit_stage_id = :id";
        $statement = $db->prepare($notificationRows);
        $statement->bindParam(':id', $_GET['notificationId']);
        $statement->execute();
        $notificationInfo = $statement->fetchAll();
        
        echo json_encode(array($notificationDetails, $notificationInfo));
    } catch (Exception $ex) {
        echo $ex;
    }
} else if(isset ($_GET['scheduleId']) && !empty ($_GET['scheduleId']) && isset ($_GET['serviceID']) && !empty ($_GET['serviceID'])){
    try {
        $scheduleid = "SELECT * FROM audit_schedule_id WHERE audit_schedule_id = :id";
        $statement = $db->prepare($scheduleid);
        $statement->bindParam(':id',$_GET['scheduleId']);
        $statement->execute();
        $scheduleDetails = $statement->fetch();
        
        $schedule = "SELECT * FROM audit_schedule WHERE audit_scheduleid = :schedule and schedule_service_id = :service";
        $statement = $db->prepare($schedule);
        $statement->bindParam(':schedule', $_GET['scheduleId']);
        $statement->bindParam(':service', $_GET['serviceID']);
        $statement->execute();
        $scheduleInfo = $statement->fetchAll();
        
            $customer = "SELECT * FROM stage_audit_two WHERE service_id = :id";
            $statement = $db->prepare($customer);
            $statement->bindParam(':id', $_GET['serviceID']);
            $statement->execute();
            $customerDetailsy = $statement->fetchAll(PDO::FETCH_ASSOC);

            $customer1 = "SELECT * FROM stage_audit_one WHERE ca_service_id = :id";
            $statement = $db->prepare($customer1);
            $statement->bindParam(':id', $_GET['serviceID']);
            $statement->execute();
            $customerDetailsx = $statement->fetchAll();
        
            $customer2 = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :id";
            $statement = $db->prepare($customer2);
            $statement->bindParam(':id', $_GET['serviceID']);
            $statement->execute();
            $customerDetailsz = $statement->fetch();
            
        echo json_encode(array($scheduleDetails,$scheduleInfo, $customerDetailsx, $customerDetailsy, $customerDetailsz));
    } catch (Exception $ex) {
        echo $ex;
    }
} else if(isset ($_GET['schedule_Id']) && !empty ($_GET['schedule_Id']) && isset ($_GET['service_Id']) && !empty ($_GET['service_Id'])){
    try {
        $scheduleii = "SELECT * FROM audit_scheduleii_id WHERE audit_scheduleii_id = :id and ca_service = :service ";
        $statement = $db->prepare($scheduleii);
        $statement->bindParam(':id',$_GET['schedule_Id']);
        $statement->bindParam(':service',$_GET['service_Id']);
        $statement->execute();
        $scheduleiiInfo = $statement->fetch();
        
        // ===============================================
        // ISO MERGE LOGIC FOR RC SERVICES (Release Section)
        // ===============================================
        // Check if this is an RC service and apply ISO merge logic
        $isRCService = false;
        $mainServiceId = null;
        $customerId = null;
        
        // Check if service_Id is an rc_id by querying service_type_rc table
        $rcCheckSql = "SELECT rc_id, service FROM service_type_rc WHERE rc_id = :rcid";
        $rcCheckStmt = $db->prepare($rcCheckSql);
        $rcCheckStmt->bindParam(':rcid', $_GET['service_Id']);
        $rcCheckStmt->execute();
        $rcCheckResult = $rcCheckStmt->fetch();
        
        if ($rcCheckResult) {
            $isRCService = true;
            $mainServiceId = $rcCheckResult['service']; // main service.service_id
            
            // Get customer_id from the main service table
            $customerSql = "SELECT customer_id FROM service WHERE service_id = :sid";
            $customerStmt = $db->prepare($customerSql);
            $customerStmt->bindParam(':sid', $mainServiceId);
            $customerStmt->execute();
            $customerResult = $customerStmt->fetch();
            
            if ($customerResult) {
                $customerId = $customerResult['customer_id'];
                error_log("RELEASE ISO MERGE: RC service detected (rc_id=" . $_GET['service_Id'] . ", service_id=" . $mainServiceId . ", customer_id=" . $customerId . ")");
                
                // Get agreement number for this RC service
                $agreementSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid AND service = :sid AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1";
                $agreementStmt = $db->prepare($agreementSql);
                $agreementStmt->bindParam(':cid', $customerId);
                $agreementStmt->bindParam(':sid', $mainServiceId);
                $agreementStmt->execute();
                $agreementResult = $agreementStmt->fetch();
                
                $agreementNo = null;
                if ($agreementResult && !empty($agreementResult['aggrement_no'])) {
                    // Remove any RC suffix to get base agreement number
                    $agreementNo = preg_replace('/\s+Rc-\d+$/', '', $agreementResult['aggrement_no']);
                    error_log("RELEASE ISO MERGE: Found agreement_no: " . $agreementNo);
                }
                
                // Get all services for this customer and agreement (CA, SA-I, SA-II, RC)
                $allServices = [];
                if ($agreementNo) {
                    $allServicesSql = "SELECT DISTINCT s.service_id, s.service_type 
                                      FROM service s 
                                      LEFT JOIN certification_aggrement ca ON s.service_id = ca.service 
                                      WHERE s.customer_id = :cid 
                                      AND s.service_type IN ('CA', 'SA-I', 'SA-II', 'RC')
                                      AND ca.aggrement_no LIKE :agreement";
                    $allServicesStmt = $db->prepare($allServicesSql);
                    $allServicesStmt->bindParam(':cid', $customerId);
                    $agreementPattern = $agreementNo . '%';
                    $allServicesStmt->bindParam(':agreement', $agreementPattern);
                    $allServicesStmt->execute();
                    $allServices = $allServicesStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    error_log("RELEASE ISO MERGE: Found " . count($allServices) . " services with agreement_no=" . $agreementNo);
                } else {
                    // Fallback: If no agreement found, use customer_id only
                    error_log("RELEASE ISO MERGE: No agreement_no found, falling back to customer_id filter");
                    $allServicesSql = "SELECT DISTINCT s.service_id, s.service_type 
                                      FROM service s 
                                      WHERE s.customer_id = :cid 
                                      AND s.service_type IN ('CA', 'SA-I', 'SA-II', 'RC')";
                    $allServicesStmt = $db->prepare($allServicesSql);
                    $allServicesStmt->bindParam(':cid', $customerId);
                    $allServicesStmt->execute();
                    $allServices = $allServicesStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    error_log("RELEASE ISO MERGE: Found " . count($allServices) . " services for customer (no agreement filter)");
                }
                
                // Collect ISOs from all services
                $allIsoStandards = [];
                
                foreach ($allServices as $svc) {
                    error_log("RELEASE ISO MERGE: Processing service_id=" . $svc['service_id'] . ", type=" . $svc['service_type']);
                    
                    $typeSpecificId = null;
                    
                    // Get type-specific ID based on service type
                    if ($svc['service_type'] == 'CA') {
                        $caSql = "SELECT ca_id FROM service_type_ca WHERE service = :sid";
                        $caStmt = $db->prepare($caSql);
                        $caStmt->bindParam(':sid', $svc['service_id']);
                        $caStmt->execute();
                        $caResult = $caStmt->fetch();
                        if ($caResult) {
                            $typeSpecificId = $caResult['ca_id'];
                            error_log("RELEASE ISO MERGE:   - Found CA ca_id: " . $typeSpecificId);
                        }
                    } elseif ($svc['service_type'] == 'SA-I') {
                        $saiSql = "SELECT sai_id FROM service_type_sai WHERE service = :sid";
                        $saiStmt = $db->prepare($saiSql);
                        $saiStmt->bindParam(':sid', $svc['service_id']);
                        $saiStmt->execute();
                        $saiResult = $saiStmt->fetch();
                        if ($saiResult) {
                            $typeSpecificId = $saiResult['sai_id'];
                            error_log("RELEASE ISO MERGE:   - Found SA-I sai_id: " . $typeSpecificId);
                        }
                    } elseif ($svc['service_type'] == 'SA-II') {
                        $saiiSql = "SELECT saii_id FROM service_type_saii WHERE service = :sid";
                        $saiiStmt = $db->prepare($saiiSql);
                        $saiiStmt->bindParam(':sid', $svc['service_id']);
                        $saiiStmt->execute();
                        $saiiResult = $saiiStmt->fetch();
                        if ($saiiResult) {
                            $typeSpecificId = $saiiResult['saii_id'];
                            error_log("RELEASE ISO MERGE:   - Found SA-II saii_id: " . $typeSpecificId);
                        }
                    } elseif ($svc['service_type'] == 'RC') {
                        $rcSql = "SELECT rc_id FROM service_type_rc WHERE service = :sid";
                        $rcStmt = $db->prepare($rcSql);
                        $rcStmt->bindParam(':sid', $svc['service_id']);
                        $rcStmt->execute();
                        $rcIdResult = $rcStmt->fetch();
                        
                        if ($rcIdResult && !empty($rcIdResult['rc_id'])) {
                            $typeSpecificId = $rcIdResult['rc_id'];
                            error_log("RELEASE ISO MERGE:   - Found RC rc_id: " . $typeSpecificId);
                        } else {
                            $typeSpecificId = $svc['service_id'];
                            error_log("RELEASE ISO MERGE:   - rc_id missing, falling back to service_id: " . $typeSpecificId);
                        }
                    }
                    
                    if ($typeSpecificId) {
                        // Fetch ISOs from audit_application table
                        $isoSql = "SELECT certification, assessment_standard FROM audit_application WHERE service_id = :sid";
                        $isoStmt = $db->prepare($isoSql);
                        $isoStmt->bindParam(':sid', $typeSpecificId);
                        $isoStmt->execute();
                        $isoData = $isoStmt->fetch();
                        
                        if ($isoData) {
                            if (!empty($isoData['certification'])) {
                                $allIsoStandards[] = $isoData['certification'];
                                error_log("RELEASE ISO MERGE:   - certification: " . $isoData['certification']);
                            }
                            if (!empty($isoData['assessment_standard'])) {
                                $allIsoStandards[] = $isoData['assessment_standard'];
                                error_log("RELEASE ISO MERGE:   - assessment_standard: " . $isoData['assessment_standard']);
                            }
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
                                error_log("RELEASE ISO MERGE:   - schedule_app_standard: " . $scheduleIso);
                            }
                        }
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
                
                error_log("RELEASE ISO MERGE: Found " . count($uniqueIsos) . " unique ISO standards");
                error_log("RELEASE ISO MERGE: Merged list: " . $mergedIsoList);
                
                // Override the scheduleii_standards field with merged ISO list
                if (!empty($mergedIsoList) && $scheduleiiInfo) {
                    $scheduleiiInfo['scheduleii_standards'] = $mergedIsoList;
                    error_log("RELEASE ISO MERGE: ✓ Successfully overrode scheduleii_standards with merged ISO list");
                }
            }
        }
        // ===============================================
        // END ISO MERGE LOGIC
        // ===============================================
        
        $scheduleii_info = "SELECT * FROM audit_scheduleii WHERE scheduleii_id = :id ";
        $statement = $db->prepare($scheduleii_info);
        $statement->bindParam(':id',$_GET['schedule_Id']);
        $statement->execute();
        $scheduleii_details = $statement->fetchAll();

        $customer = "SELECT * FROM stage_audit_two WHERE service_id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['service_Id']);
        $statement->execute();
        $customerDetailsy = $statement->fetchAll(PDO::FETCH_ASSOC);

        $customer1 = "SELECT * FROM stage_audit_one WHERE ca_service_id = :id";
        $statement = $db->prepare($customer1);
        $statement->bindParam(':id', $_GET['service_Id']);
        $statement->execute();
        $customerDetailsx = $statement->fetchAll();
        
        $customer3 = "SELECT * FROM stage_twoaudit_id WHERE service_id = :id";
        $statement = $db->prepare($customer3);
        $statement->bindParam(':id', $_GET['service_Id']);
        $statement->execute();
        $customerDetailsz1 = $statement->fetch();
        
        echo json_encode(array($scheduleiiInfo, $scheduleii_details, $customerDetailsx, $customerDetailsy,$customerDetailsz1));
    } catch (Exception $ex) {
        
    }
} else if(isset ($_GET['control_Id']) && !empty ($_GET['control_Id']) && isset ($_GET['serviceId']) && !empty ($_GET['serviceId'])){
    try {
        $controlformat = "SELECT * FROM control_formati_id WHERE control_id = :formatid and caservice_id = :serviceid ";
        $statement = $db->prepare($controlformat);
        $statement->bindParam(':formatid',$_GET['control_Id']);
        $statement->bindParam(':serviceid',$_GET['serviceId']);
        $statement->execute();
        $controlid = $statement->fetch();
        
        $controlDetails = "SELECT * FROM control_formati WHERE control_id = :id";
        $statement = $db->prepare($controlDetails);
        $statement->bindParam(':id',$_GET['control_Id']);
        $statement->execute();
        $controlFormatInfo = $statement->fetchAll();
        
        echo json_encode(array($controlid, $controlFormatInfo));
    } catch (Exception $ex) {
        
    }
} else if(isset ($_GET['control2_id']) && !empty ($_GET['control2_id']) && isset ($_GET['service2_Id']) && !empty ($_GET['service2_Id'])){
    try {
        $controlformat = "SELECT * FROM control_formatii_id WHERE control2_id = :formatid and caservice_id = :serviceid ";
        $statement = $db->prepare($controlformat);
        $statement->bindParam(':formatid',$_GET['control2_id']);
        $statement->bindParam(':serviceid',$_GET['service2_Id']);
        $statement->execute();
        $controlid = $statement->fetch();
        
        $controlDetails = "SELECT * FROM control_formatii WHERE control2_id = :id";
        $statement = $db->prepare($controlDetails);
        $statement->bindParam(':id',$_GET['control2_id']);
        $statement->execute();
        $controlFormatInfo = $statement->fetchAll();
        
        echo json_encode(array($controlid, $controlFormatInfo));
    } catch (Exception $ex) {
        
    }
} else if(isset ($_GET['survay_id']) && !empty ($_GET['survay_id']) && isset ($_GET['survay_service']) && !empty ($_GET['survay_service'])){
    try {
        $survayinfo = "SELECT * FROM customer_survey_id WHERE survey_id = :survay and ca_serviceid = :service ";
        $statement = $db->prepare($survayinfo);
        $statement->bindParam(':survay', $_GET['survay_id']);
        $statement->bindParam(':service', $_GET['survay_service']);
        $statement->execute();
        $survayId = $statement->fetch();
        
        $survayDetails = "SELECT * FROM customer_survey WHERE survayid = :survay and ca_service_id = :service ";
        $statement = $db->prepare($survayDetails);
        $statement->bindParam(':survay', $_GET['survay_id']);
        $statement->bindParam(':service', $_GET['survay_service']);
        $statement->execute();
        $survay_info = $statement->fetchAll();
        
        echo json_encode(array($survayId, $survay_info));
    } catch (Exception $ex) {
        
    }
} else if(isset($_GET['notificationiiId']) && !empty($_GET['notificationiiId']) && isset ($_GET['serviceiiId']) && !empty ($_GET['serviceiiId'])){
    try {
        $stage2IdDetail = "SELECT * FROM stage_twoaudit_id WHERE audit_stageii_id = :id";
        $statement = $db->prepare($stage2IdDetail);
        $statement->bindParam(':id',$_GET['notificationiiId']);
        $statement->execute();
        $stage2IdInfo = $statement->fetch();
        
        $stage2Audit = "SELECT * FROM stage_audit_two WHERE audit_stageii_id = :id";
        $statement = $db->prepare($stage2Audit);
        $statement->bindParam(':id',$_GET['notificationiiId']);
        $statement->execute();
        $stage2AuditInfo = $statement->fetchAll();
        
        echo json_encode(array($stage2IdInfo, $stage2AuditInfo));
    } catch (Exception $ex) {
    echo $ex;    
    }
} else if(isset ($_GET['certificationId']) && !empty ($_GET['certificationId']) && isset ($_GET['serviceii_Id']) && !empty ($_GET['serviceii_Id']) && isset ($_GET["customerid"]) && !empty ($_GET["customerid"])){
    try {
        $certification = "SELECT * FROM certificate_print WHERE certificate_id = :certification and service_id = :service";
        $statement = $db->prepare($certification);
        $statement->bindParam(':certification',$_GET['certificationId']);
        $statement->bindParam(':service',$_GET['serviceii_Id']);
        $statement->execute();
        $certDetails = $statement->fetch();
        
        $uniqueid = "SELECT unique_id FROM customer_info where id = :id";
        $statement = $db->prepare($uniqueid);
        $statement->bindParam(":id", $_GET["customerid"]);
        $statement->execute();
        $ids = $statement->fetch();
        
        echo json_encode(array($certDetails, $ids));
    } catch (Exception $ex) {
        echo $ex;
    }    
} else if(isset ($_GET["servelanceId"]) && !empty ($_GET["servelanceId"]) && isset ($_GET["svcID"]) && !empty ($_GET["svcID"])){
    try {
        $sql = "SELECT * FROM servelance_1_id where servelance_id = :servilenceid and service_id = :service";
        $statement = $db->prepare($sql);
        $statement->bindParam(":servilenceid",$_GET["servelanceId"] );
        $statement->bindParam(":service",$_GET["svcID"] );
        $statement->execute();
        $servilence = $statement->fetch();
        
        $sql2 = "SELECT * FROM `servelance_audit` where servelance_id = :servilenceid and service_id = :service";
        $statement = $db->prepare($sql2);
        $statement->bindParam(":servilenceid",$_GET["servelanceId"] );
        $statement->bindParam(":service",$_GET["svcID"] );
        $statement->execute();
        $servilenceAudit = $statement->fetchAll();
        
        $customer1 = "SELECT * FROM stage_audit_one WHERE ca_service_id = :id";
        $statement = $db->prepare($customer1);
        $statement->bindParam(':id', $_GET['svcID']);
        $statement->execute();
        $customerDetailsx = $statement->fetchAll();
        
        $customer2 = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :id";
        $statement = $db->prepare($customer2);
        $statement->bindParam(':id', $_GET['svcID']);
        $statement->execute();
        $customerDetailsy = $statement->fetchAll();
        
        echo json_encode(array($servilence,$servilenceAudit,$customerDetailsx,$customerDetailsy));
    } catch (Exception $ex) {
        
    }
}
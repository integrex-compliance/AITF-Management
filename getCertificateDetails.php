<?php
/*
 * Get certificate details based on certificate number
 * Returns: standard name, issue date, and expire date
 */

session_start();
include_once './dbConnect.php';
include 'commonutil.php';
date_default_timezone_set('Asia/Kolkata');

// Enable error reporting for debugging
error_log("getCertificateDetails.php called");

if (isset($_GET['certificateNo']) && !empty($_GET['certificateNo'])) {
    try {
        $certNo = trim($_GET['certificateNo']);
        error_log("Looking for certificate: " . $certNo);
        
        // First, try to get from customer_info itself (scope field)
        $sql0 = "SELECT 
                    ci.scope as standard_name,
                    ci.certification_stage_date as issue_date,
                    NULL as expire_date
                FROM customer_info ci
                WHERE ci.certification_no = :certNo
                LIMIT 1";
        
        $statement0 = $db->prepare($sql0);
        $statement0->bindParam(':certNo', $certNo);
        $statement0->execute();
        $certData = $statement0->fetch(PDO::FETCH_ASSOC);
        
        if ($certData && !empty($certData['standard_name'])) {
            error_log("Found certificate data in customer_info table");
        } else {
            $certData = null;
        }
        
        // Try cdc_report_review to get dates and service_id
        if (!$certData) {
            error_log("Trying cdc_report_review for dates");
            
            $sql1 = "SELECT ca_service_id, revDatecdc as issue_date, expiredate as expire_date
                    FROM cdc_report_review
                    WHERE certificateNo = :certNo
                    ORDER BY Date DESC
                    LIMIT 1";
            
            $statement1 = $db->prepare($sql1);
            $statement1->bindParam(':certNo', $certNo);
            $statement1->execute();
            $certData = $statement1->fetch(PDO::FETCH_ASSOC);
            
            if ($certData) {
                error_log("Found certificate dates in cdc_report_review");
                
                // Now get the ISO standard from audit_application - check ALL relevant fields
                if (!empty($certData['ca_service_id'])) {
                    $sql1b = "SELECT certification, assessment_standard, accereditation_requirement 
                              FROM audit_application WHERE service_id = :svcId LIMIT 1";
                    $statement1b = $db->prepare($sql1b);
                    $statement1b->bindParam(':svcId', $certData['ca_service_id']);
                    $statement1b->execute();
                    $stdData = $statement1b->fetch(PDO::FETCH_ASSOC);
                    
                    if ($stdData) {
                        $certData['standard_name'] = '';
                        
                        // Priority 1: Check assessment_standard field first
                        if (!empty($stdData['assessment_standard'])) {
                            $certValue = trim($stdData['assessment_standard']);
                            $certData['standard_name'] = $certValue;
                            error_log("Found standard in assessment_standard: " . $certValue);
                        }
                        // Priority 2: Check accereditation_requirement field
                        elseif (!empty($stdData['accereditation_requirement'])) {
                            $certValue = trim($stdData['accereditation_requirement']);
                            $certData['standard_name'] = $certValue;
                            error_log("Found standard in accereditation_requirement: " . $certValue);
                        }
                        // Priority 3: Check certification field
                        elseif (!empty($stdData['certification'])) {
                            $certValue = trim($stdData['certification']);
                            $certData['standard_name'] = $certValue;
                            error_log("Found standard in certification: " . $certValue);
                        }
                        
                        if (empty($certData['standard_name'])) {
                            error_log("No ISO standard found in any field for service_id: " . $certData['ca_service_id']);
                        }
                    } else {
                        error_log("No audit_application record found for service_id: " . $certData['ca_service_id']);
                        $certData['standard_name'] = '';
                    }
                }
            }
        }
        
        // NEW: Try direct lookup from customer_info + service_type_ca + audit_application
        if (!$certData || empty($certData['standard_name'])) {
            error_log("Trying direct lookup via customer_info -> service_type_ca -> audit_application");
            
            $sqlDirect = "SELECT 
                            stc.service as service_id,
                            ci.certification_stage_date as issue_date
                          FROM customer_info ci
                          LEFT JOIN service_type_ca stc ON ci.id = stc.customer_id
                          WHERE ci.certification_no = :certNo
                          ORDER BY stc.ca_id DESC
                          LIMIT 1";
            
            $stmtDirect = $db->prepare($sqlDirect);
            $stmtDirect->bindParam(':certNo', $certNo);
            $stmtDirect->execute();
            $directData = $stmtDirect->fetch(PDO::FETCH_ASSOC);
            
            if ($directData && !empty($directData['service_id'])) {
                if (!$certData) {
                    $certData = [];
                    $certData['issue_date'] = $directData['issue_date'];
                }
                
                error_log("Found service_id via direct lookup: " . $directData['service_id']);
                
                // Get ISO standard from this service
                $sqlStd = "SELECT certification, assessment_standard, accereditation_requirement 
                          FROM audit_application WHERE service_id = :svcId LIMIT 1";
                $stmtStd = $db->prepare($sqlStd);
                $stmtStd->bindParam(':svcId', $directData['service_id']);
                $stmtStd->execute();
                $stdDirectData = $stmtStd->fetch(PDO::FETCH_ASSOC);
                
                if ($stdDirectData) {
                    if (!empty($stdDirectData['assessment_standard'])) {
                        $certData['standard_name'] = trim($stdDirectData['assessment_standard']);
                        error_log("Found standard from direct lookup (assessment_standard): " . $certData['standard_name']);
                    } elseif (!empty($stdDirectData['accereditation_requirement'])) {
                        $certData['standard_name'] = trim($stdDirectData['accereditation_requirement']);
                        error_log("Found standard from direct lookup (accereditation_requirement): " . $certData['standard_name']);
                    } elseif (!empty($stdDirectData['certification'])) {
                        $certData['standard_name'] = trim($stdDirectData['certification']);
                        error_log("Found standard from direct lookup (certification): " . $certData['standard_name']);
                    }
                }
            }
        }
        
        // NEW: Try lookup from audit_schedule_id table (this often has the standard)
        if (!$certData || empty($certData['standard_name'])) {
            error_log("Trying audit_schedule_id table for ISO standard");
            
            $sqlAuditSched = "SELECT 
                                asi.schedule_app_standard as standard_name,
                                ci.certification_stage_date as issue_date,
                                asi.serviceca_id
                              FROM customer_info ci
                              LEFT JOIN audit_schedule_id asi ON ci.id = asi.customer_ca_id
                              WHERE ci.certification_no = :certNo
                              ORDER BY asi.audit_schedule_id DESC
                              LIMIT 1";
            
            $stmtAuditSched = $db->prepare($sqlAuditSched);
            $stmtAuditSched->bindParam(':certNo', $certNo);
            $stmtAuditSched->execute();
            $auditSchedData = $stmtAuditSched->fetch(PDO::FETCH_ASSOC);
            
            if ($auditSchedData && !empty($auditSchedData['standard_name'])) {
                if (!$certData) {
                    $certData = [];
                    $certData['issue_date'] = $auditSchedData['issue_date'];
                }
                $certData['standard_name'] = trim($auditSchedData['standard_name']);
                error_log("Found standard from audit_schedule_id table: " . $certData['standard_name']);
            }
        }
        
        // If still no data, try to get from customer's latest service
        if (!$certData || empty($certData['standard_name'])) {
            error_log("Trying to get from customer's latest service");
            
            $sql1c = "SELECT cs.service_id, ci.certification_stage_date as issue_date
                      FROM customer_info ci
                      LEFT JOIN ca_services cs ON ci.customer_id = cs.customer_id
                      WHERE ci.certification_no = :certNo
                      ORDER BY cs.service_id DESC
                      LIMIT 1";
            
            $statement1c = $db->prepare($sql1c);
            $statement1c->bindParam(':certNo', $certNo);
            $statement1c->execute();
            $serviceData = $statement1c->fetch(PDO::FETCH_ASSOC);
            
            if ($serviceData && !empty($serviceData['service_id'])) {
                if (!$certData) {
                    $certData = [];
                    $certData['issue_date'] = $serviceData['issue_date'];
                }
                
                // Get ISO standard from this service
                $sql1d = "SELECT certification, assessment_standard, accereditation_requirement 
                          FROM audit_application WHERE service_id = :svcId LIMIT 1";
                $statement1d = $db->prepare($sql1d);
                $statement1d->bindParam(':svcId', $serviceData['service_id']);
                $statement1d->execute();
                $stdData2 = $statement1d->fetch(PDO::FETCH_ASSOC);
                
                if ($stdData2) {
                    if (!empty($stdData2['assessment_standard'])) {
                        $certData['standard_name'] = trim($stdData2['assessment_standard']);
                        error_log("Found standard from latest service (assessment_standard): " . $certData['standard_name']);
                    } elseif (!empty($stdData2['accereditation_requirement'])) {
                        $certData['standard_name'] = trim($stdData2['accereditation_requirement']);
                        error_log("Found standard from latest service (accereditation_requirement): " . $certData['standard_name']);
                    } elseif (!empty($stdData2['certification'])) {
                        $certData['standard_name'] = trim($stdData2['certification']);
                        error_log("Found standard from latest service (certification): " . $certData['standard_name']);
                    }
                }
            }
        }
        
        // Try certificate_print table as fallback
        if (!$certData) {
            error_log("Trying certificate_print table");
            
            $sql2 = "SELECT 
                        cp.certificate_scope as standard_name,
                        cp.registration_date as issue_date,
                        cp.valid_until as expire_date
                    FROM certificate_print cp
                    WHERE cp.certificate_no = :certNo
                    LIMIT 1";
            
            $statement2 = $db->prepare($sql2);
            $statement2->bindParam(':certNo', $certNo);
            $statement2->execute();
            $certData = $statement2->fetch(PDO::FETCH_ASSOC);
            
            if ($certData) {
                error_log("Found certificate in certificate_print table");
            }
        }
        
        if ($certData) {
            // Log the raw data
            error_log("Raw certificate data: " . print_r($certData, true));
            
            // Store original issue date for expire date calculation
            $originalIssueDate = isset($certData['issue_date']) ? $certData['issue_date'] : null;
            
            // Format dates for display
            if (isset($certData['issue_date']) && $certData['issue_date'] && $certData['issue_date'] != '0000-00-00') {
                $certData['issue_date'] = simpleDateConversion($certData['issue_date']);
            } else {
                $certData['issue_date'] = '';
            }
            
            // Calculate expire date if not found (typically 3 years from issue date)
            if ((!isset($certData['expire_date']) || empty($certData['expire_date']) || $certData['expire_date'] == '0000-00-00') 
                && !empty($originalIssueDate) && $originalIssueDate != '0000-00-00') {
                try {
                    $issueDateTime = new DateTime($originalIssueDate);
                    $issueDateTime->modify('+3 years');
                    $issueDateTime->modify('-1 day'); // Typically expires 1 day before the anniversary
                    $certData['expire_date'] = simpleDateConversion($issueDateTime->format('Y-m-d'));
                    error_log("Calculated expire date from issue date: " . $certData['expire_date']);
                } catch (Exception $e) {
                    error_log("Error calculating expire date: " . $e->getMessage());
                    $certData['expire_date'] = '';
                }
            } elseif (isset($certData['expire_date']) && $certData['expire_date'] && $certData['expire_date'] != '0000-00-00') {
                $certData['expire_date'] = simpleDateConversion($certData['expire_date']);
            } else {
                $certData['expire_date'] = '';
            }
            
            // Ensure standard_name is not null
            if (!isset($certData['standard_name'])) {
                $certData['standard_name'] = '';
            }
            
            error_log("Formatted certificate data: " . print_r($certData, true));
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $certData
            ]);
        } else {
            error_log("Certificate not found in any table for: " . $certNo);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Certificate not found for certificate number: ' . $certNo
            ]);
        }
    } catch (Exception $ex) {
        error_log("Error in getCertificateDetails.php: " . $ex->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $ex->getMessage()
        ]);
    }
} else {
    error_log("No certificate number provided");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Certificate number is required'
    ]);
}
?>

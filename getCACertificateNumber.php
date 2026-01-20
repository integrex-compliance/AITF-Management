<?php
/*
 * Get CA certificate number for a customer to be used in SA-I, SA-2, and RC service types
 * This ensures all service types use the same certificate number as the CA service
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

// Enable error reporting for debugging
error_log("getCACertificateNumber.php called");

if (isset($_GET['customerId']) && !empty($_GET['customerId'])) {
    try {
        $customerId = intval($_GET['customerId']);
        $isoLabel = isset($_GET['isoLabel']) ? trim($_GET['isoLabel']) : '';
        
        error_log("=== NEW APPROACH: Looking for certificates for customer: " . $customerId . ", ISO: " . $isoLabel);
        
        // NEW SUPER SIMPLE APPROACH:
        // Step 1: Prefer ONLY CA CDC records for this customer from cdc_report_review table
        // This avoids accidentally using SA-I / SA-II / RC CDC records when fetching
        // the master CA certificate number for surveillance / recertification cycles.

        // Primary query: join through service_type_ca -> service to filter CA services
        $sqlAllCDC = "SELECT crr.crr_id, crr.ca_service_id, crr.certificateNo
                       FROM cdc_report_review crr
                       INNER JOIN service_type_ca ca ON crr.ca_service_id = ca.ca_id
                       INNER JOIN service s ON ca.service = s.service_id
                       WHERE s.customer_id = :customerId
                       ORDER BY crr.Date DESC";
        $stmtAllCDC = $db->prepare($sqlAllCDC);
        $stmtAllCDC->bindParam(':customerId', $customerId, PDO::PARAM_INT);
        $stmtAllCDC->execute();
        $allCDCs = $stmtAllCDC->fetchAll(PDO::FETCH_ASSOC);

        // Fallback to legacy behaviour if, for any reason, the JOIN returns no rows
        if (empty($allCDCs)) {
            error_log("Step 1 (JOIN) returned 0 rows - falling back to legacy CDC query");
            $sqlAllCDC = "SELECT crr_id, ca_service_id, certificateNo FROM cdc_report_review WHERE customer_id = :customerId ORDER BY Date DESC";
            $stmtAllCDC = $db->prepare($sqlAllCDC);
            $stmtAllCDC->bindParam(':customerId', $customerId, PDO::PARAM_INT);
            $stmtAllCDC->execute();
            $allCDCs = $stmtAllCDC->fetchAll(PDO::FETCH_ASSOC);
        }

        error_log("Step 1 - Found " . count($allCDCs) . " CDC records for customer (CA-filtered / fallback)");
        error_log("Step 1 - CDC records: " . print_r($allCDCs, true));
        
        $certRecords = [];
        
        // Step 2: For each CDC, check NEW table (cdc_report_review_office_use)
        if (!empty($allCDCs)) {
            foreach ($allCDCs as $cdc) {
                $crrId = $cdc['crr_id'];
                
                $sqlOffice = "SELECT certificate_no, rev_date, rev_no, expire_date, iso_label 
                             FROM cdc_report_review_office_use 
                             WHERE crr_id = :crrId
                             ORDER BY iso_index ASC";
                $stmtOffice = $db->prepare($sqlOffice);
                $stmtOffice->bindParam(':crrId', $crrId);
                $stmtOffice->execute();
                $officeRecs = $stmtOffice->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($officeRecs)) {
                    error_log("Step 2 - Found " . count($officeRecs) . " office records for crr_id: " . $crrId);
                    
                    // Filter out empty certificates
                    foreach ($officeRecs as $rec) {
                        if (!empty($rec['certificate_no'])) {
                            $certRecords[] = $rec;
                            error_log("Step 2 - Added certificate: " . $rec['certificate_no'] . " for ISO: " . $rec['iso_label']);
                        }
                    }
                }
                
                // If we found certificates, break (we only need from most recent CDC)
                if (!empty($certRecords)) {
                    break;
                }
            }
        }
        
        error_log("Step 3 - Total certificates found: " . count($certRecords));
        error_log("Step 3 - Certificate records: " . print_r($certRecords, true));
        
        $certData = null;
        
        if (!empty($certRecords)) {
            error_log("Found " . count($certRecords) . " certificate records in cdc_report_review_office_use table");
            
            // If ISO label is specified, try to find matching record using LIKE pattern
            if (!empty($isoLabel)) {
                foreach ($certRecords as $record) {
                    // Use case-insensitive partial matching like in customerAccount.php
                    if (!empty($record['iso_label']) && stripos($record['iso_label'], $isoLabel) !== false) {
                        $certData = $record;
                        error_log("Found matching ISO label using LIKE pattern: " . $isoLabel . " matched " . $record['iso_label']);
                        break;
                    }
                }
            }
            
            // If no match found by ISO label, just use the FIRST record (first ISO standard)
            // This ensures SA-I/SA-II/RC get at least one certificate number
            if (!$certData) {
                $certData = $certRecords[0];
                error_log("Using first certificate record: " . $certData['certificate_no'] . " (ISO: " . ($certData['iso_label'] ?: 'N/A') . ")");
            }
        }
        
        // Fallback to cdc_report_review table (OLD table) if not found in NEW table
        if (!$certData && !empty($allCDCs)) {
            error_log("NEW table returned nothing - trying OLD table (cdc_report_review)");
            
            // Check the CDC records we already found for certificate numbers in OLD table
            foreach ($allCDCs as $cdc) {
                if (!empty($cdc['certificateNo'])) {
                    $certData = [
                        'certificate_no' => $cdc['certificateNo'],
                        'rev_date' => '',
                        'rev_no' => '',
                        'expire_date' => '',
                        'iso_label' => ''
                    ];
                    error_log("SUCCESS! Found certificate in OLD table (certificateNo field): " . $cdc['certificateNo']);
                    break;
                }
            }
            
            if (!$certData) {
                error_log("OLD table (certificateNo field) also empty for all CDC records");
            }
        }
        
        // Final fallback to customer_info table
        if (!$certData) {
            error_log("Trying customer_info table for certificate");
            
            $sql3 = "SELECT 
                        certification_no as certificate_no,
                        certification_stage_date as rev_date
                    FROM customer_info
                    WHERE id = :customerId
                    AND certification_no IS NOT NULL
                    AND certification_no != ''
                    LIMIT 1";
            
            $statement3 = $db->prepare($sql3);
            $statement3->bindParam(':customerId', $customerId);
            $statement3->execute();
            $certData = $statement3->fetch(PDO::FETCH_ASSOC);
            
            error_log("Query 3 (customer_info) - Result: " . print_r($certData, true));
            
            if ($certData) {
                error_log("Found certificate in customer_info table: " . $certData['certificate_no']);
            } else {
                error_log("No certificate found in customer_info table");
            }
        }
        
        // Check if we have multiple certificates (for Control Format which needs all)
        if (!empty($certRecords)) {
            error_log("Returning " . count($certRecords) . " certificates");
            
            // Return ALL certificates for Control Format
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'count' => count($certRecords),
                'certificates' => $certRecords,
                // Also include first certificate for backward compatibility (CDC modal)
                'certificate_no' => trim($certRecords[0]['certificate_no']),
                'rev_date' => isset($certRecords[0]['rev_date']) ? $certRecords[0]['rev_date'] : '',
                'rev_no' => isset($certRecords[0]['rev_no']) ? $certRecords[0]['rev_no'] : '',
                'expire_date' => isset($certRecords[0]['expire_date']) ? $certRecords[0]['expire_date'] : '',
                'iso_label' => isset($certRecords[0]['iso_label']) ? $certRecords[0]['iso_label'] : ''
            ]);
        } else if ($certData && !empty($certData['certificate_no'])) {
            error_log("Returning single certificate from fallback: " . $certData['certificate_no']);
            
            // Return single certificate from OLD table or customer_info
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'count' => 1,
                'certificates' => [$certData],
                'certificate_no' => trim($certData['certificate_no']),
                'rev_date' => isset($certData['rev_date']) ? $certData['rev_date'] : '',
                'rev_no' => isset($certData['rev_no']) ? $certData['rev_no'] : '',
                'expire_date' => isset($certData['expire_date']) ? $certData['expire_date'] : '',
                'iso_label' => isset($certData['iso_label']) ? $certData['iso_label'] : ''
            ]);
        } else {
            error_log("No CA certificate found for customer: " . $customerId);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'No CA certificate found for this customer'
            ]);
        }
    } catch (Exception $ex) {
        error_log("Error in getCACertificateNumber.php: " . $ex->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $ex->getMessage()
        ]);
    }
} else {
    error_log("No customer ID provided");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Customer ID is required'
    ]);
}
?>

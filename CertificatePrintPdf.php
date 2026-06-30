<?php

// Prevent premature output that breaks PDF headers
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ob_start();
session_start();

//============================================================+
// File name   : example_003.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 003 for TCPDF class
//               Custom Header and Footer
// 
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
include_once './dbConnect.php';
include_once './include/certification_cycle_helper.php';
date_default_timezone_set('Asia/Kolkata');

/**
 * CERTIFICATE DATE CALCULATION - CLEAN IMPLEMENTATION
 * 
 * Core Rules:
 * - CA_CDC_DATE is the ONLY base date for all calculations
 * - One cycle = 36 months (3 years)
 * - Order in each cycle: SA-I → SA-II → RC
 * - Surveillance count resets at start of every new cycle
 * 
 * Cycle Boundaries:
 * - Cycle 1: CA_CDC_DATE to (CA_CDC_DATE + 36 months - 1 day)
 * - Cycle 2: (CA_CDC_DATE + 36 months) to (CA_CDC_DATE + 72 months - 1 day)
 * - Cycle N: (CA_CDC_DATE + (N-1)*36 months) to (CA_CDC_DATE + N*36 months - 1 day)
 * 
 * Within each cycle (cycle_start = CA_CDC_DATE + (cycle_number - 1) * 36 months):
 * - SA-I: next = cycle_start + 12 months - 1 day, label = "1st Surveillance"
 * - SA-II: next = cycle_start + 24 months - 1 day, label = "2nd Surveillance"  
 * - RC: next = cycle_start + 36 months - 1 day, label = "Re-Certification"
 * 
 * Validity Rule:
 * - validity_until = cycle_start + 36 months - 1 day (SAME for CA, SA-I, SA-II, RC in that cycle)
 */

/**
 * Determine which cycle number based on completed RC count
 * 
 * Cycle 1: Before any RC is completed (CA, SA-I, SA-II, then RC)
 * Cycle 2: After 1st RC is completed (SA-I, SA-II, then RC)
 * Cycle 3: After 2nd RC is completed, etc.
 * 
 * @param int $completedRcCount Number of RCs completed before current service
 * @param string $currentAuditType Current audit type (CA, SA-I, SA-II, RC)
 * @return int The cycle number (1, 2, 3, ...)
 */
if (!function_exists('determineCycleNumber')) {
    function determineCycleNumber($completedRcCount, $currentAuditType)
    {
        $currentAuditType = strtoupper(trim($currentAuditType));
        return max(1, $completedRcCount + 1);
    }
}

/**
 * Get cycle number using SAME logic as customerAccount.php
 * Uses service_type_sai/service_type_saii tables to find position
 * 
 * @param PDO $db Database connection
 * @param int $customerId Customer ID
 * @param string $currentAuditType Current audit type (SA-I, SA-II, RC, CA)
 * @param int $currentCaServiceId Current ca_service_id
 * @return int Cycle index (0 = first cycle, 1 = second cycle, etc.)
 */
if (!function_exists('countCompletedRCs')) {
    function countCompletedRCs(PDO $db, $customerId, $currentAuditType, $currentCaServiceId, $currentCdcDate)
    {
        $cycleIndex = 0;
        $currentAuditType = strtoupper(trim($currentAuditType));
        $currentCaServiceId = (int)$currentCaServiceId;
        
        if (empty($customerId) || $currentCaServiceId <= 0) {
            return 0;
        }

        try {
            $currentServiceId = 0;
            $svcQuery = null;

            if ($currentAuditType === 'CA') {
                $svcQuery = "SELECT service FROM service_type_ca WHERE ca_id = :ca_id";
            } elseif ($currentAuditType === 'SA-I') {
                $svcQuery = "SELECT service FROM service_type_sai WHERE sai_id = :ca_id";
            } elseif ($currentAuditType === 'SA-II') {
                $svcQuery = "SELECT service FROM service_type_saii WHERE saii_id = :ca_id";
            } elseif ($currentAuditType === 'RC') {
                $svcQuery = "SELECT service FROM service_type_rc WHERE rc_id = :ca_id";
            }

            if ($svcQuery !== null) {
                $stmt = $db->prepare($svcQuery);
                $stmt->bindParam(':ca_id', $currentCaServiceId, PDO::PARAM_INT);
                $stmt->execute();
                $svcRow = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($svcRow && isset($svcRow['service'])) {
                    $currentServiceId = (int)$svcRow['service'];
                }
            }

            if ($currentServiceId === 0) {
                return 0;
            }

            if ($currentAuditType === 'SA-I') {
                $allQuery = "SELECT service FROM service_type_sai WHERE customer_id = :cust ORDER BY service ASC";
                $stmt = $db->prepare($allQuery);
                $stmt->bindParam(':cust', $customerId, PDO::PARAM_INT);
                $stmt->execute();
                $allServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($allServices as $idx => $row) {
                    if ((int)$row['service'] === $currentServiceId) {
                        $cycleIndex = $idx;
                        break;
                    }
                }
            } elseif ($currentAuditType === 'SA-II') {
                $allQuery = "SELECT service FROM service_type_saii WHERE customer_id = :cust ORDER BY service ASC";
                $stmt = $db->prepare($allQuery);
                $stmt->bindParam(':cust', $customerId, PDO::PARAM_INT);
                $stmt->execute();
                $allServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($allServices as $idx => $row) {
                    if ((int)$row['service'] === $currentServiceId) {
                        $cycleIndex = $idx;
                        break;
                    }
                }
            } elseif ($currentAuditType === 'RC') {
                $allQuery = "SELECT service_id FROM service WHERE customer_id = :cust AND service_type = 'RC' ORDER BY service_id ASC";
                $stmt = $db->prepare($allQuery);
                $stmt->bindParam(':cust', $customerId, PDO::PARAM_INT);
                $stmt->execute();
                $allServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($allServices as $idx => $row) {
                    if ((int)$row['service_id'] === $currentServiceId) {
                        $cycleIndex = $idx;
                        break;
                    }
                }
            }

        } catch (Exception $e) {
            $cycleIndex = 0;
        }
        
        return $cycleIndex;
    }
}

/**
 * Calculate the cycle start date for a given cycle number
 * 
 * @param DateTime $caCdcDate The original CA CDC date
 * @param int $cycleNumber The cycle number (1, 2, 3, ...)
 * @return DateTime The start date of that cycle
 */
if (!function_exists('getCycleStartDate')) {
    function getCycleStartDate(DateTime $caCdcDate, int $cycleNumber)
    {
        $cycleStart = clone $caCdcDate;
        $monthsToAdd = ($cycleNumber - 1) * 36;
        if ($monthsToAdd > 0) {
            $cycleStart->modify('+' . $monthsToAdd . ' months');
        }
        return $cycleStart;
    }
}

/**
 * Compute certificate dates based on CA CDC date, cycle number, and audit type
 * 
 * @param string $caCdcDateStr The original CA CDC date (base for all calculations)
 * @param int $cycleNumber The cycle number (1, 2, 3, ...)
 * @param string $auditType Current audit type (CA, SA-I, SA-II, RC)
 * @return array [nextAssessmentDate, validityUntilDate, nextAuditLabel]
 */
if (!function_exists('computeCertificateCycleDates')) {
    function computeCertificateCycleDates($caBaseDateStr, $cycleNumber, $auditType)
    {
        if (empty($caBaseDateStr)) {
            return [null, null, ''];
        }

        try {
            $caCdcDate = new DateTime($caBaseDateStr);
        } catch (Exception $e) {
            return [null, null, ''];
        }

        $auditType = strtoupper(trim($auditType));
        $cycleNumber = max(1, (int)$cycleNumber);
        
        $cycleStart = getCycleStartDate($caCdcDate, $cycleNumber);
        
        $validityDate = clone $cycleStart;
        $validityDate->modify('+36 months');
        $validityDate->modify('-1 day');
        
        $nextAssessmentDate = clone $cycleStart;
        $nextAuditLabel = '';
        
        switch ($auditType) {
            case 'CA':
                $nextAssessmentDate->modify('+12 months');
                $nextAssessmentDate->modify('-1 day');
                $nextAuditLabel = '1st surveillance';
                break;
            case 'SA-I':
                $nextAssessmentDate->modify('+24 months');
                $nextAssessmentDate->modify('-1 day');
                $nextAuditLabel = '2nd surveillance';
                break;
            case 'SA-II':
                $nextAssessmentDate->modify('+36 months');
                $nextAssessmentDate->modify('-1 day');
                $nextAuditLabel = 'Re-Certification';
                break;
            case 'RC':
                $nextCycleStart = clone $cycleStart;
                $nextCycleStart->modify('+36 months');
                $nextAssessmentDate = clone $nextCycleStart;
                $nextAssessmentDate->modify('+12 months');
                $nextAssessmentDate->modify('-1 day');
                $nextAuditLabel = '1st surveillance';
                $validityDate = clone $nextCycleStart;
                $validityDate->modify('+36 months');
                $validityDate->modify('-1 day');
                break;
            default:
                $nextAssessmentDate->modify('+12 months');
                $nextAssessmentDate->modify('-1 day');
                $nextAuditLabel = '1st surveillance';
        }

        return [$nextAssessmentDate, $validityDate, $nextAuditLabel];
    }
}

if (!function_exists('resolveNextAuditType')) {
    function resolveNextAuditType($auditType, $nextAuditTypeRaw)
    {
        $auditType = strtoupper(trim((string) $auditType));
        $nextAuditTypeRaw = strtoupper(trim((string) $nextAuditTypeRaw));

        $validStages = ['CA', 'SA-I', 'SA-II', 'RC'];

        if (in_array($nextAuditTypeRaw, $validStages, true)) {
            return $nextAuditTypeRaw;
        }

        switch ($auditType) {
            case 'CA':
                return 'SA-I';
            case 'SA-I':
                return 'SA-II';
            case 'SA-II':
                return 'RC';
            case 'RC':
                return 'SA-I';
            default:
                return $auditType;
        }
    }
}


//  this file is validated by gauarv oli

// First get CDC data - prefer crr_id if available (more reliable)
$cdcInfo = null;
$selectedCrrId = isset($_POST['crr_id']) && !empty($_POST['crr_id']) ? (int) $_POST['crr_id'] : 0;

if ($selectedCrrId > 0) {
    // Use crr_id directly if available (more reliable, especially for RC)
    $cdcRecord = "SELECT * FROM cdc_report_review WHERE crr_id = :crr_id";
    $statement = $db->prepare($cdcRecord);
    $statement->bindParam(":crr_id", $selectedCrrId, PDO::PARAM_INT);
    $statement->execute();
    $cdcInfo = $statement->fetch();
}
//  fixed date validation logic
// Fallback: use ca_service_id if crr_id not available or didn't find record
if (!$cdcInfo || !is_array($cdcInfo)) {
    if (isset($_POST["service"]) && !empty($_POST["service"])) {
        $cdcRecord = "SELECT * FROM cdc_report_review WHERE ca_service_id = :id";
        $statement = $db->prepare($cdcRecord);
        $statement->bindParam(":id", $_POST["service"]);
        $statement->execute();
        $cdcInfo = $statement->fetch();
    }
}

// Validate CDC data exists
if (!$cdcInfo || !is_array($cdcInfo)) {
    die('Error: CDC Report Review data not found. Please ensure the CDC report has been completed.');
}

$auditType = aitfNormalizeCertificationServiceType(isset($cdcInfo['audit_type']) ? $cdcInfo['audit_type'] : '');

// Get client data
$clientDetails = "SELECT * FROM customer_info WHERE id = :id";
$statement = $db->prepare($clientDetails);
$statement->bindParam(':id', $_POST['customer']);
$statement->execute();
$clientInfo = $statement->fetch();

// Validate client data exists
if (!$clientInfo || !is_array($clientInfo)) {
    die('Error: Customer information not found.');
}

// Master base date: prefer FIRST CA CDC date for the customer, fallback to certification_stage_date, then current CDC date
$caBaseDate = null;
try {
    $caCdcQuery = "SELECT MIN(crr.Date) AS first_date FROM cdc_report_review crr 
                   INNER JOIN service_type_ca ca ON crr.ca_service_id = ca.ca_id 
                   INNER JOIN service s ON ca.service = s.service_id 
                   WHERE s.customer_id = :customer_id AND s.service_type = 'CA'";
    $statement = $db->prepare($caCdcQuery);
    $statement->bindParam(':customer_id', $_POST['customer']);
    $statement->execute();
    $caCdcRow = $statement->fetch(PDO::FETCH_ASSOC);
    if ($caCdcRow && !empty($caCdcRow['first_date'])) {
        $caBaseDate = $caCdcRow['first_date'];
    }
} catch (Exception $e) {
    $caBaseDate = null;
}

if (!$caBaseDate && !empty($clientInfo['certification_stage_date'])) {
    $caBaseDate = $clientInfo['certification_stage_date'];
} elseif (!$caBaseDate && isset($cdcInfo['Date'])) {
    $caBaseDate = $cdcInfo['Date'];
}

$regSourceDate = isset($cdcInfo['Date']) ? $cdcInfo['Date'] : $caBaseDate;
$currentCdcDate = isset($cdcInfo['Date']) ? $cdcInfo['Date'] : null;

// NEW SIMPLIFIED LOGIC:
// All date calculations are based ONLY on CA_CDC_DATE
// The cycle is determined by counting same-type services with lower service_id

// Get current service_id - try multiple sources
$currentServiceId = 0;

// Method 1: Use $_POST['service'] directly (most reliable)
if (isset($_POST['service']) && !empty($_POST['service'])) {
    $currentServiceId = (int)$_POST['service'];
}

// Method 2: Get from service_type_ca if POST not available
if ($currentServiceId == 0 && isset($cdcInfo['ca_service_id'])) {
    try {
        // Try different column names that might exist
        $svcQuery = "SELECT * FROM service_type_ca WHERE ca_id = :ca_id LIMIT 1";
        $stmt = $db->prepare($svcQuery);
        $stmt->bindParam(':ca_id', $cdcInfo['ca_service_id'], PDO::PARAM_INT);
        $stmt->execute();
        $svcRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($svcRow) {
            // Check various possible column names
            if (isset($svcRow['service'])) {
                $currentServiceId = (int)$svcRow['service'];
            } elseif (isset($svcRow['service_id'])) {
                $currentServiceId = (int)$svcRow['service_id'];
            } elseif (isset($svcRow['svc_id'])) {
                $currentServiceId = (int)$svcRow['svc_id'];
            }
        }
    } catch (Exception $e) {
        $currentServiceId = 0;
    }
}

// Calculate cycle number once - this will be used for all date calculations
$auditType = aitfNormalizeCertificationServiceType(isset($cdcInfo['audit_type']) ? $cdcInfo['audit_type'] : '');
$customerId = isset($_POST['customer']) ? $_POST['customer'] : null;
$currentCaServiceId = isset($cdcInfo['ca_service_id']) ? (int)$cdcInfo['ca_service_id'] : 0;
$completedRcCount = countCompletedRCs($db, $customerId, $auditType, $currentCaServiceId, $currentCdcDate);
$cycleNumber = determineCycleNumber($completedRcCount, $auditType);

$certificateDates = aitfBuildCertificatePrintDates(
    $db,
    $customerId,
    $currentCaServiceId,
    null,
    $auditType,
    is_array($cdcInfo) ? $cdcInfo : array()
);
if (!empty($certificateDates)) {
    $auditType = $certificateDates['service_type'];
    $regSourceDate = $certificateDates['base_date'];
    $cycleNumber = $certificateDates['cycle_index'] + 1;
}

// DEBUG - remove this after testing
error_log("DEBUG CERTIFICATE: customerId=$customerId, auditType=$auditType, currentCaServiceId=$currentCaServiceId, currentCdcDate=$currentCdcDate, completedRcCount=$completedRcCount, cycleNumber=$cycleNumber");

// Try to get certificate_print data if certificate_id is provided
$certifyInfo = null;
if (isset($_POST['certify_id']) && !empty($_POST['certify_id'])) {
    $certifyDetails = "SELECT * FROM certificate_print WHERE certificate_id = :id";
    $statement = $db->prepare($certifyDetails);
    $statement->bindParam(':id', $_POST['certify_id']);
    $statement->execute();
    $certifyInfo = $statement->fetch();

    // Ensure dates reflect the current certification service, not transition audits.
    if ($certifyInfo && is_array($certifyInfo)) {
        if (!empty($certificateDates)) {
            $certifyInfo['registration_date'] = $certificateDates['base_date'];
            $certifyInfo['before_date'] = $certificateDates['before_date'];
            $certifyInfo['valid_until'] = $certificateDates['valid_until'];
        } elseif (!empty($regSourceDate)) {
            $certifyInfo['registration_date'] = date('Y-m-d', strtotime($regSourceDate));
        }

        if (empty($certificateDates)) {
            [$periodicalDate, $validDate, $nextAuditLabel] = computeCertificateCycleDates($caBaseDate, $cycleNumber, $auditType);
            if ($periodicalDate instanceof DateTime) {
                $certifyInfo['before_date'] = $periodicalDate->format('Y-m-d');
            }
            if ($validDate instanceof DateTime) {
                $certifyInfo['valid_until'] = $validDate->format('Y-m-d');
            }
        }
    }
}

// If no certificate_print data, generate from CDC and customer data
if (!$certifyInfo || !is_array($certifyInfo)) {
    try {
        $registrationDateObj = new DateTime($regSourceDate);
    } catch (Exception $e) {
        $registrationDateObj = new DateTime($currentCdcDate);
    }

    $certificationDate = $registrationDateObj->format('Y-m-d');

    if (!empty($certificateDates)) {
        $certificationDate = $certificateDates['base_date'];
        $validUntilDate = $certificateDates['valid_until'];
        $beforeDate = $certificateDates['before_date'];
        $nextAuditLabel = $certificateDates['next_audit_label'];
    } else {
        [$periodicalDate, $validityDate, $nextAuditLabel] = computeCertificateCycleDates($caBaseDate, $cycleNumber, $auditType);
        $validUntilDate = $validityDate ? $validityDate->format('Y-m-d') : '';
        $beforeDate = $periodicalDate ? $periodicalDate->format('Y-m-d') : '';
    }

    // Create certificate info array from CDC and customer data
    $certifyInfo = array(
        'customer_name' => $clientInfo['customer_name'],
        'customer_address' => $clientInfo['address'],
        'certificate_no' => isset($cdcInfo['certificateNo']) ? $cdcInfo['certificateNo'] : '',
        'certificate_scope' => $clientInfo['scope'],
        'registration_date' => $certificationDate,
        'valid_until' => $validUntilDate,
        'before_date' => $beforeDate
    );
}

$selectedIsoIndex = isset($_POST['iso_index']) && $_POST['iso_index'] !== '' ? (int) $_POST['iso_index'] : null;
$selectedIsoLabel = isset($_POST['iso_label']) ? trim($_POST['iso_label']) : '';
// $selectedCrrId already defined above

$isoEntry = null;
if ($selectedCrrId > 0) {
    $isoQuery = "SELECT iso_index, iso_label, certificate_no, rev_no, rev_date, expire_date, periodical_audit, audit_type, comments
                 FROM cdc_report_review_office_use WHERE crr_id = :crr";
    $isoStatement = $db->prepare($isoQuery);
    $isoStatement->bindParam(':crr', $selectedCrrId, PDO::PARAM_INT);
    $isoStatement->execute();
    $isoEntries = $isoStatement->fetchAll(PDO::FETCH_ASSOC);

    // Try to match specific ISO if index or label provided
    if ($selectedIsoIndex !== null || $selectedIsoLabel !== '') {
        foreach ($isoEntries as $entry) {
            $entryIndex = isset($entry['iso_index']) ? (int) $entry['iso_index'] : null;
            $entryLabel = isset($entry['iso_label']) ? trim($entry['iso_label']) : '';

            $matchesIndex = $selectedIsoIndex !== null && $entryIndex === $selectedIsoIndex;
            $matchesLabel = $selectedIsoLabel !== '' && strcasecmp($entryLabel, $selectedIsoLabel) === 0;

            if ($matchesIndex || $matchesLabel) {
                $isoEntry = $entry;
                break;
            }
        }
    }

    // If no specific match or no index/label provided, use first entry
    if ($isoEntry === null && !empty($isoEntries)) {
        $isoEntry = $isoEntries[0];
    }
}

$certificateNumber = $certifyInfo['certificate_no'];
$certificateRevNo = $cdcInfo['revCdc'];
$certificateRevDate = $cdcInfo['revDatecdc'];
$standardDisplay = isset($cdcInfo['covered_for']) ? trim($cdcInfo['covered_for']) : '';

if ($isoEntry) {
    if (!empty($isoEntry['certificate_no'])) {
        $certificateNumber = $isoEntry['certificate_no'];
    }
    if (!empty($isoEntry['rev_no'])) {
        $certificateRevNo = $isoEntry['rev_no'];
    }
    if (!empty($isoEntry['rev_date'])) {
        $certificateRevDate = $isoEntry['rev_date'];
    }
    
    // Recalculate dates using new simplified function based on CA_CDC_DATE
    if (!empty($certificateDates)) {
        $certifyInfo['registration_date'] = $certificateDates['base_date'];
        $certifyInfo['valid_until'] = $certificateDates['valid_until'];
        $certifyInfo['before_date'] = $certificateDates['before_date'];
    } elseif (!empty($caBaseDate)) {
        $auditType = aitfNormalizeCertificationServiceType(isset($cdcInfo['audit_type']) ? $cdcInfo['audit_type'] : '');
        [$periodicalDate, $validDate, $nextAuditLabel] = computeCertificateCycleDates($caBaseDate, $cycleNumber, $auditType);
        if ($validDate instanceof DateTime) {
            $certifyInfo['valid_until'] = $validDate->format('Y-m-d');
        }
        if ($periodicalDate instanceof DateTime) {
            $certifyInfo['before_date'] = $periodicalDate->format('Y-m-d');
        }
    }
    if (!empty($isoEntry['audit_type'])) {
        $cdcInfo['next_audit_type'] = $isoEntry['audit_type'];
    }
    if (!empty($isoEntry['iso_label'])) {
        $standardDisplay = trim($isoEntry['iso_label']);
    }
}

if ($standardDisplay === '' && $selectedIsoLabel !== '') {
    $standardDisplay = $selectedIsoLabel;
}
if ($standardDisplay === '' && $selectedIsoIndex !== null && isset($isoEntries) && isset($isoEntries[$selectedIsoIndex]['iso_label'])) {
    $standardDisplay = trim($isoEntries[$selectedIsoIndex]['iso_label']);
}

// Get the correct next audit label from our calculation function
// This ensures consistency across all cycles
if (!empty($certificateDates)) {
    $nextaudit = $certificateDates['next_audit_label'];
} else {
    $auditType = aitfNormalizeCertificationServiceType(isset($cdcInfo['audit_type']) ? $cdcInfo['audit_type'] : '');
    [$periodicalDateFinal, $validDateFinal, $nextaudit] = computeCertificateCycleDates($caBaseDate, $cycleNumber, $auditType);
}

$expireDate = "";
$reAuditDate = "";
$restoreDate = "";
$reviewSt = "";

if($cdcInfo["rc_audit_date"] != "" && $cdcInfo["rc_restore_date"]){
$secondMaxSvc = "SELECT MAX(service_id) as id FROM `service` WHERE service_type = 'SA-II' AND customer_id = :cust";
$statement = $db->prepare($secondMaxSvc);
$statement->bindParam(":cust", $_POST['customer']);
$statement->execute();
$maxid = $statement->fetch();

$expdate = "SELECT due_date FROM `ca_service_dates` WHERE `service_id` = '".$maxid['id']."' AND action = 'Expire Letter'";
$statement = $db->prepare($expdate);
//$statement->bindParam(":id", $maxid['id']);
$statement->execute();
$exp_date = $statement->fetch();

$expireDate = date('d.m.Y', strtotime($exp_date['due_date']));

 $reviewSt = '<br><font size="12" text-align: center;>Original Registration Date: ' . date('d.m.Y', strtotime($certifyInfo['registration_date'])) . ', Expired On:'.$expireDate.'</font><br>'
         . '<font size="12" text-align: center;>Recertification Audit Date: ' . date('d.m.Y', strtotime($cdcInfo["rc_audit_date"])) . ', Further Restore On Date:'.date('d.m.Y', strtotime($cdcInfo["rc_restore_date"])).'</font><br>'
         . '<font size="12" text-align: center;>Next assessment before date: ' . date('d.m.Y', strtotime($certifyInfo['before_date'])) . '  (' . $nextaudit . ')</font><br><br>';

 } else {
    $reviewSt = '<font size="12" text-align: center;>Registration Date : ' . date('d.m.Y', strtotime($certifyInfo['registration_date'])) . '</font><br><br>'
            . '<font size="12" text-align: center;>Next assessment before date: ' . date('d.m.Y', strtotime($certifyInfo['before_date'])) . '  (' . $nextaudit . ')</font><br><br>';
 }


if (strlen($certifyInfo['customer_address']) > 29 && strlen($certifyInfo['customer_address']) < 140) {
    $address = '<br><font size="12" text-align: center;>' . nl2br($certifyInfo['customer_address']) . '</font><br>';
} else if (strlen($certifyInfo['customer_address']) > 140 && strlen($certifyInfo['customer_address']) < 400) {
    $address = '<br><font size="12" text-align: center;>' . nl2br($certifyInfo['customer_address']) . '</font>';
} else {
    $address = '<br><font size="12" text-align: center;>' . nl2br($certifyInfo['customer_address']) . '</font><br><br>';
}

// Extend the TCPDF class to create custom Header and Footer
if (!class_exists('MYPDF')) {
class MYPDF extends TCPDF {

    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';

    //Page header
    public function Header() {
        // Logo
        //$image_file = K_PATH_IMAGES.'logo_example.jpg';
        //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 12);
        // Position at 15 mm from bottom
        $this->SetY(+15);
        // Title
        $this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');


        $this->writeHTMLCell($w = '', $h = '', $x = '', $y = '', $this->header, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'L', $autopadding = true);
        $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));

//        $this->Line(5, 5, $this->getPageWidth() - 5, 5);
//
//        $this->Line($this->getPageWidth() - 5, 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, $this->getPageHeight() - 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, 5, 5, $this->getPageHeight() - 5);
    }

//     Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
//        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

}
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('CERTIFICATE');
$pdf->SetSubject('AITF');
$pdf->SetKeywords('AITF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(30, PDF_MARGIN_TOP, 1);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 5);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------
// set font
$pdf->SetFont('dejavusans', '', 10);
if ($cdcInfo['next_audit_type'] == 'SA-II') {
  $saII = '<font size="11" text-align: center;>Validity until ' . date('d.m.Y', strtotime($certifyInfo['valid_until'])) . ' is subject to continually maintaining the requirements<br>
          of the certified management system.</font>';
} else {
  $saII = '<font size="11" text-align: center;>Validity until ' . date('d.m.Y', strtotime($certifyInfo['valid_until'])) . ' is subject to reissue of this certificate through periodical
          assessment with successfully compliance and continually maintaining the requirements<br>
          of the certified management system.</font>';
}

// add a page
$pdf->AddPage();

// Set certificate background using provided JPG/PNG/PDF (prefer images for reliability)
// Only apply background when explicitly requested (e.g., mail flow sets use_bg=1)
$useBg = isset($_POST['use_bg']) && (int)$_POST['use_bg'] === 1;
$certBgJpg = __DIR__ . '/aitfImages/cert.jpg';
$certBgPng = __DIR__ . '/aitfImages/cert_bg.png'; // optional pre-converted asset
$certBgPdf = __DIR__ . '/aitfImages/cert bg.pdf';
$certBgPath = null;
$imgType = '';

// Normalize paths for TCPDF on Windows (forward slashes)
if ($useBg) {
    if (file_exists($certBgJpg)) {
        $certBgJpg = str_replace('\\', '/', realpath($certBgJpg));
    }
    if (file_exists($certBgPng)) {
        $certBgPng = str_replace('\\', '/', realpath($certBgPng));
    }
    if (file_exists($certBgPdf)) {
        $certBgPdf = str_replace('\\', '/', realpath($certBgPdf));
    }
}

// 1) Prefer JPG if provided
if ($useBg && $certBgJpg && file_exists($certBgJpg) && is_readable($certBgJpg)) {
    $certBgPath = $certBgJpg;
    $imgType = 'JPG';
}

// 2) Else PNG asset if present
if ($useBg && !$certBgPath && $certBgPng && file_exists($certBgPng) && is_readable($certBgPng)) {
    $certBgPath = $certBgPng;
    $imgType = 'PNG';
}

// 3) Else convert PDF first page to PNG if Imagick available
if ($useBg && !$certBgPath && $certBgPdf && file_exists($certBgPdf) && is_readable($certBgPdf) && extension_loaded('imagick')) {
    try {
        $im = new Imagick();
        $im->setResolution(300, 300);
        $im->readImage($certBgPdf . '[0]');
        $im->setImageFormat('png');
        $tmpPng = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cert_bg_' . md5($certBgPdf . filemtime($certBgPdf)) . '.png';
        if (!file_exists($tmpPng)) {
            $im->writeImage($tmpPng);
        }
        $certBgPath = $tmpPng;
        $imgType = 'PNG';
    } catch (Exception $e) {
        $certBgPath = null; // will fall back below
        error_log('CERT BG: imagick conversion failed: ' . $e->getMessage());
    }
}

// 4) Else fall back to PDF directly (TCPDF can render if GhostScript/Imagick available)
if ($useBg && !$certBgPath && $certBgPdf && file_exists($certBgPdf) && is_readable($certBgPdf)) {
    $certBgPath = $certBgPdf;
    $imgType = ''; // let TCPDF auto-detect PDF
}

if ($useBg && $certBgPath && file_exists($certBgPath)) {
    $bMargin = $pdf->getBreakMargin();
    $auto_page_break = $pdf->getAutoPageBreak();
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->Image($certBgPath, 0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), $imgType ?: '', '', '', false, 300, '', false, false, 0, true, false, false);
    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    $pdf->setPageMark();
} else if ($useBg) {
    error_log('CERT BG: background file not applied. Checked PNG=' . ($certBgPng ?? '') . ' PDF=' . ($certBgPdf ?? ''));
}

$html = '<br><br><br><br><p  style="text-align: center; text-justify: inter-word;"><font size="16" text-align: center;><b>Certification Awarded to</b></font><br><br>
        <font size="19" text-align: center;><b>' . $certifyInfo['customer_name'] . '</b></font>
        <br>
        <table border="" cellspacing="1" cellpadding="4"> 
    <tr ><td style="text-align:center;" height="100"><font size="19" text-align: center;>' . $address . '</font>
    </td>
    </tr>
    </table>'
        . '<br><br><font size="12" text-align: center;>A.I.T.F. MANAGEMENT CERTIFICATION LIMITED Certify that</font><br>'
        . '<font size="12" text-align: center;> the management system of above organisation has been assessed <br>and found to be in compliance with the requirements of the</font><br><p>'
        . '<p  style="text-align: center; text-justify: inter-word; padding-bottom:10px" ><font size="18" text-align: center;><u>STANDARD</u></font><br>'
        . '<font size="18" text-align: center;><b>' . $standardDisplay . '</b></font><br>'
        . '<p  style="text-align: center; text-justify: inter-word; padding-bottom:10px" ><font size="14" text-align: center;><b><u>SCOPE OF CERTIFICATION</u></b></font><br>'
        . '<br><font size="12" text-align: center;>' . nl2br($certifyInfo["certificate_scope"]) . '</font><br><br>'
        . '<font size="18" text-align: center;><b>Certificate No : ' . $certificateNumber . '</b></font><br><br>'
        . $reviewSt. '<br><font size="11" text-align: center;>' . $saII
        . '<br><font size="10" text-align: center;>Note: This certificate will reissue after each assessment for next due and continuity of compliance
</font><br>'
        . '<br><p  style="text-align: justify; text-justify: inter-word;"><font size="12" text-align: center;><b> For and on behalf of A.I.T.F.</b></font><br><br><br><br>'
        . '<br><font size="10" text-align: center;>  Managing Director</font><br>'
        . '<font size="10" text-align: center;>  Revision No: ' . $certificateRevNo . '<br>  Date: ' . ($certificateRevDate ? date('d.m.Y', strtotime($certificateRevDate)) : '') . '</font><br><br></p>';
// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
if (ob_get_length()) {
    ob_end_clean();
} else {
    @ob_end_clean();
}
$pdf->Output('Certificate.pdf', 'I');

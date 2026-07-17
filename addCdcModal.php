<?php



ob_start(); // Start output buffering to prevent header errors
session_start();
include_once './dbConnect.php';
include_once './include/cdc_office_use.php';
include_once './include/certification_cycle_helper.php';
date_default_timezone_set('Asia/Kolkata');

function parseFormDate($key, &$dateObject = null) {
    $dateObject = null;
    if (!isset($_POST[$key]) || $_POST[$key] === '') {
        return 'null';
    }

    $rawValue = trim($_POST[$key]);

    // Support both 2-digit and 4-digit year variants in strict day-month-year order
    // to avoid PHP guessing month/day and flipping values.
    $supportedFormats = [
        'd.m.Y', // 10.01.2026
        'd.m.y', // 10.01.26
        'd-m-Y', // 10-01-2026
        'd-m-y', // 10-01-26
        'd/m/Y', // 10/01/2026
        'd/m/y', // 10/01/26
        'Y-m-d', // 2026-01-10 (already DB-style)
    ];

    foreach ($supportedFormats as $format) {
        $dt = DateTime::createFromFormat($format, $rawValue);
        if ($dt instanceof DateTime && $dt->format($format) === $rawValue) {
            $dateObject = clone $dt;
            return $dt->format('Y-m-d');
        }
    }

    $timestamp = strtotime($rawValue);
    if ($timestamp !== false) {
        $formatted = date('Y-m-d', $timestamp);
        $dateObject = new DateTime($formatted);
        return $formatted;
    }

    return 'null';
}

function validateCdcDates($applicationDateObj, $receivedDateObj, $reviewDateObj, $customerId) {
    if (!$applicationDateObj) {
        $_SESSION['cdc_error'] = 'Application Date is required.';
        header("Location: customerAccount.php?customerid=" . $customerId);
        exit;
    }

    // Removed validation for Application Received Date and Review Date fields
    // These fields were removed from the CDC form
}

function validateCdcAuditPerformance($customerId) {
    $requiredFields = [
        'ncval' => 'NC',
        'ofival' => 'OFI',
        'oplus' => 'O+ve',
    ];

    foreach ($requiredFields as $field => $label) {
        if (!isset($_POST[$field]) || trim((string) $_POST[$field]) === '') {
            $_SESSION['cdc_error'] = 'Please fill value in Audit Performance section (NC, OFI, O+ve).';
            header("Location: customerAccount.php?customerid=" . $customerId);
            exit;
        }
    }
}

function generateRcClientStatusDates($db, $serviceId, $customerId)
{
    try {
        aitfSyncClientStatusDates($db, $serviceId, $customerId);
    } catch (Exception $ex) {
        error_log("CLIENT STATUS DATES ERROR: " . $ex->getMessage());
    }
}

function activateCustomerAfterGrantedCaCdc($db, $customerId, $caServiceId, $cdcStatus, $certificateNo = '')
{
    if (!$db || empty($customerId) || empty($caServiceId) || $cdcStatus !== 'Granted') {
        return;
    }

    try {
        $caSql = "SELECT ca.service
                  FROM service_type_ca ca
                  INNER JOIN service svc ON ca.service = svc.service_id
                  WHERE ca.ca_id = :ca_service_id
                  AND ca.customer_id = :customer_id
                  AND svc.service_type = 'CA'
                  LIMIT 1";
        $statement = $db->prepare($caSql);
        $statement->bindParam(':ca_service_id', $caServiceId);
        $statement->bindParam(':customer_id', $customerId);
        $statement->execute();
        $caService = $statement->fetch();

        if (!$caService || empty($caService['service'])) {
            return;
        }

        $hasCertificate = trim((string) $certificateNo) !== '';

        if (!$hasCertificate) {
            $certificateSql = "SELECT crr.crr_id
                               FROM cdc_report_review crr
                               LEFT JOIN cdc_report_review_office_use cou
                                   ON cou.crr_id = crr.crr_id
                                   AND TRIM(COALESCE(cou.certificate_no, '')) <> ''
                               WHERE crr.ca_service_id = :ca_service_id
                               AND crr.cdc_status = 'Granted'
                               AND (
                                   TRIM(COALESCE(crr.certificateNo, '')) <> ''
                                   OR cou.crr_id IS NOT NULL
                               )
                               LIMIT 1";
            $statement = $db->prepare($certificateSql);
            $statement->bindParam(':ca_service_id', $caServiceId);
            $statement->execute();
            $hasCertificate = (bool) $statement->fetch();
        }

        if (!$hasCertificate) {
            return;
        }

        $activeStatus = 'Active';
        $updateSql = "UPDATE customer_info
                      SET audit_status = :status
                      WHERE id = :customer_id
                      AND (
                          audit_status IS NULL
                          OR TRIM(audit_status) = ''
                          OR LOWER(REPLACE(REPLACE(TRIM(audit_status), '-', ''), ' ', '')) IN ('inprocess', 'contractinprocess', 'valid', 'granted')
                      )";
        $statement = $db->prepare($updateSql);
        $statement->bindParam(':status', $activeStatus);
        $statement->bindParam(':customer_id', $customerId);
        $statement->execute();
    } catch (Exception $ex) {
        error_log("CDC Active Status Update Error: " . $ex->getMessage());
    }
}

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {

    unset($_SESSION['cdc_error']);

    if (isset($_POST['save_cdc']) || isset($_POST['update_cdc'])) {
        $customerIdForRedirect = isset($_POST['customer_Id']) ? $_POST['customer_Id'] : '';
        validateCdcAuditPerformance($customerIdForRedirect);
    }

    if (isset($_POST['save_cdc'])) {
        error_log("CDC Form submitted with save_cdc. crr_Id value: " . (isset($_POST['crr_Id']) ? $_POST['crr_Id'] : 'NOT SET'));
        if (isset($_POST['crr_Id']) && !empty($_POST['crr_Id'])) {
            error_log("=== ENTERING UPDATE BLOCK (crr_Id exists) === crr_Id: " . $_POST['crr_Id']);
            try {
                if (isset($_POST['iso_decisions'])) {
                    ensureCdcIsoDecisionsTable($db);
                }

                $applicationDateObj = null;
                $receivedDateObj = null;
                $reviewDateObj = null;

                $Date = parseFormDate('Date', $applicationDateObj);
                $receivedDateObj = null;
                $audit_date_raw = isset($_POST['audit_date_CDC']) ? trim($_POST['audit_date_CDC']) : '';
                error_log("CDC UPDATE - audit_date_CDC field value: " . ($audit_date_raw !== '' ? $audit_date_raw : 'NOT SET'));
                $audit_date_for_db = $audit_date_raw;
                $revDate = parseFormDate('revDatecdc', $reviewDateObj);
                $expiredate = parseFormDate('expiredate');
                $periodicaldate = parseFormDate('periodicalaudit');
                $assDate = parseFormDate('assDate');

                $customerIdForRedirect = isset($_POST['customer_Id']) ? $_POST['customer_Id'] : '';
                validateCdcDates($applicationDateObj, $receivedDateObj, $reviewDateObj, $customerIdForRedirect);

                // Capture Member 1 status (review_1_granting)
                $review_1_granting = "";
                if (isset($_POST["cdc-status-mem1-g"])) {
                    $review_1_granting = 'Granted';
                } elseif (isset($_POST['cdc-status-mem1-n'])) {
                    $review_1_granting = 'Not-Granted';
                }

                // Capture Member 2 status (review_2_granting)
                $review_2_granting = "";
                if (isset($_POST["cdc-status-mem2-g"])) {
                    $review_2_granting = 'Granted';
                } elseif (isset($_POST['cdc-status-mem2-n'])) {
                    $review_2_granting = 'Not-Granted';
                }

                // Capture Member 3 status (cdc_status)
                $cdc_status = "";
                if (isset($_POST["cdc-status-mem3-g"])) {
                    $cdc_status = 'Granted';
                } elseif (isset($_POST['cdc-status-mem3-n'])) {
                    $cdc_status = 'Not-Granted';
                }

                if (isset($_POST['Yes1'])) {
                    $Yes1 = 'checked';
                } else {
                    $Yes1 = 'null';
                }
                if (isset($_POST['Yes2'])) {
                    $Yes2 = 'checked';
                } else {
                    $Yes2 = 'null';
                }
                if (isset($_POST['Yes3'])) {
                    $Yes3 = 'checked';
                } else {
                    $Yes3 = 'null';
                }
                if (isset($_POST['No1'])) {
                    $No1 = 'checked';
                } else {
                    $No1 = 'null';
                }
                if (isset($_POST['No2'])) {
                    $No2 = 'checked';
                } else {
                    $No2 = 'null';
                }
                if (isset($_POST['No3'])) {
                    $No3 = 'checked';
                } else {
                    $No3 = 'null';
                }

                $is_cdc_only = isset($_POST['is_cdc_only']) ? (int)$_POST['is_cdc_only'] : 0;
                $service = $_POST['service_Id'];
                $officeUseUpdateSql = $is_cdc_only === 1
                    ? ''
                    : ',`certificateNo`=:certificateNo,`revCdc`=:revCdc,`revDatecdc`=:revDatecdc,`expiredate`=:expiredate,`periodicalaudit`=:periodicalaudit';
                // echo $service;
                $sql = "UPDATE `cdc_report_review` SET `is_cdc_only`=:is_cdc_only,`file_no`=:file_no,`audit_organization`=:audit_organization,`address`=:address,`product_service`=:product_service,`iaf_nacf`=:iaf_nacf,`exclusion`=:exclusion,`audit_date`=:audit_date,`audit_type`=:audit_type,`covered_for`=:covered_for,`accreditation`=:accreditation,`lead_auditor`=:lead_auditor,`technical_expert`=:technical_expert,`assDate`=:assDate,`auditors`=:auditors,`reviewer_1`=:reviewer_1,`reviewer_2`=:reviewer_2,`granted_by`=:granted_by,`system_during`=:system_during,`system_complainte`=:system_complainte,`comments_if_any`=:comments_if_any,`review_1_granting`=:review_1_granting,`review_2_granting`=:review_2_granting,`cdc_status`=:cdc_status,`comments`=:comments,`Date`=:Date" . $officeUseUpdateSql . ",`ncval`=:ncval,`ofival`=:ofival,`oplusval`=:oplusval,`rev1_yes`=:rev1_yes,`rev2_yes`=:rev2_yes,`yes1`=:yes1,`yes2`=:yes2,`yes3`=:yes3,`rev3_yes`=:rev3_yes WHERE crr_id=:crr_Id and ca_service_id=:ca_service_id ";
                $statement = $db->prepare($sql);
                $statement->bindParam(':is_cdc_only', $is_cdc_only);
                $statement->bindParam(':ca_service_id', $service);
                $statement->bindParam(':crr_Id', $_POST['crr_Id']);
                $statement->bindParam(':file_no', $_POST['file_No']);
                $statement->bindParam(':audit_organization', $_POST['audited_Organisation']);
                $statement->bindParam(':address', $_POST['address']);
                $statement->bindParam(':product_service', $_POST['product_service']);
                $statement->bindParam(':iaf_nacf', $_POST['iaf_nace']);
                $statement->bindParam(':exclusion', $_POST['exclusion']);

                $statement->bindParam(':audit_date', $audit_date_for_db);
                $statement->bindParam(':audit_type', $_POST['audit_type']);
                $statement->bindParam(':covered_for', $_POST['covered_for']);
                $statement->bindParam(':accreditation', $_POST['accreditation']);
                $statement->bindParam(':lead_auditor', $_POST['lead_auditor']);
                $statement->bindParam(':technical_expert', $_POST['technical_expert']);
                $statement->bindParam(':assDate', $assDate);
                $statement->bindParam(':auditors', $_POST['auditors']);
                $statement->bindParam(':reviewer_1', $_POST['reviewer-1']);
                $statement->bindParam(':reviewer_2', $_POST['reviewer-2']);
                $statement->bindParam(':granted_by', $_POST['granted-by']);
                $statement->bindParam(':system_during', $_POST['system_during']);
                $statement->bindParam(':system_complainte', $_POST['system_complainte']);
                $statement->bindParam(':comments_if_any', $_POST['comments_if_any']);
                $statement->bindParam(':review_1_granting', $review_1_granting);
                $statement->bindParam(':review_2_granting', $review_2_granting);
                $statement->bindParam(':cdc_status', $cdc_status);

                $statement->bindParam(':comments', $_POST['comments']);
                $statement->bindParam(':Date', $Date);


                if ($is_cdc_only !== 1) {
                    $statement->bindParam(':certificateNo', $_POST['certificateNo']);
                    $statement->bindParam(':revCdc', $_POST['revCdc']);
                    $statement->bindParam(':revDatecdc', $revDate);
                    $statement->bindParam(':expiredate', $expiredate);
                    $statement->bindParam(':periodicalaudit', $periodicaldate);
                }
                $statement->bindParam(':ncval', $_POST['ncval']);
                $statement->bindParam(':ofival', $_POST['ofival']);
                $statement->bindParam(':oplusval', $_POST['oplus']);
                
                // Handle CDC Member checkboxes
                $rev1_yes = isset($_POST['checkrev1_yes']) ? 'Yes' : (isset($_POST['checkrev1_no']) ? 'No' : '');
                $rev2_yes = isset($_POST['checkrev2_yes']) ? 'Yes' : (isset($_POST['checkrev2_no']) ? 'No' : (isset($_POST['checkrev2_na']) ? 'N.A.' : ''));
                $yes1 = isset($_POST['check1_yes']) ? 'Yes' : (isset($_POST['check1_no']) ? 'No' : '');
                $yes2 = isset($_POST['check2_yes']) ? 'Yes' : (isset($_POST['check2_no']) ? 'No' : '');
                $yes3 = isset($_POST['check3_yes']) ? 'Yes' : (isset($_POST['check3_no']) ? 'No' : '');
                $rev3_yes = isset($_POST['checkrev3_yes']) ? 'Yes' : (isset($_POST['checkrev3_no']) ? 'No' : '');
                $statement->bindParam(':rev1_yes', $rev1_yes);
                $statement->bindParam(':rev2_yes', $rev2_yes);
                $statement->bindParam(':yes1', $yes1);
                $statement->bindParam(':yes2', $yes2);
                $statement->bindParam(':yes3', $yes3);
                $statement->bindParam(':rev3_yes', $rev3_yes);

                $statement->execute();

                // Generate dates for CA or SA-I service after UPDATE
                error_log("=== UPDATE - SERVICE TYPE CHECK START ===");
                error_log("UPDATE - Service/CA/SAI ID: " . $service);
                
                // First try CA table
                $getServiceId = "SELECT service FROM service_type_ca WHERE ca_id = :ca_id";
                $statement = $db->prepare($getServiceId);
                $statement->bindParam(':ca_id', $service);
                $statement->execute();
                $serviceData = $statement->fetch();
                
                // If not CA, try SA-I table
                if (!isset($serviceData['service']) || empty($serviceData['service'])) {
                    $getServiceId = "SELECT service FROM service_type_sai WHERE sai_id = :sai_id";
                    $statement = $db->prepare($getServiceId);
                    $statement->bindParam(':sai_id', $service);
                    $statement->execute();
                    $serviceData = $statement->fetch();
                }
                
                // If not SA-I, try SA-II table
                if (!isset($serviceData['service']) || empty($serviceData['service'])) {
                    $getServiceId = "SELECT service FROM service_type_saii WHERE saii_id = :saii_id";
                    $statement = $db->prepare($getServiceId);
                    $statement->bindParam(':saii_id', $service);
                    $statement->execute();
                    $serviceData = $statement->fetch();
                }
                
                error_log("UPDATE - Universal Service ID: " . (isset($serviceData['service']) ? $serviceData['service'] : "NOT FOUND"));
                
                if (isset($serviceData['service'])) {
                    $universalServiceId = $serviceData['service'];
                    
                    // Now get service type
                    $uniService = "SELECT service_type FROM service WHERE service_id = :id";
                    $statement = $db->prepare($uniService);
                    $statement->bindParam(':id', $universalServiceId);
                    $statement->execute();
                    $uniServiceType = $statement->fetch();
                    
                    error_log("UPDATE - Service type: " . (isset($uniServiceType['service_type']) ? $uniServiceType['service_type'] : "NOT SET"));
                    
                    if (isset($uniServiceType['service_type']) && $uniServiceType['service_type'] == "CA") {
                        error_log("=== UPDATE - CA DATE GENERATION START ===");
                        
                        // Delete existing dates
                        $deleteExisting = "DELETE FROM ca_service_dates WHERE service_id = :serviceid AND action IN ('Suspension Reminder', 'Suspend Letter', 'Withdrawal Reminder', 'Withdraw Letter')";
                        $statement = $db->prepare($deleteExisting);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->execute();
                        error_log("UPDATE - Deleted existing dates");
                        
                        // Use the Date being saved
                        $baseCertDate = isset($Date) && !empty($Date) ? $Date : date('Y-m-d');
                        error_log("UPDATE - Base cert date: " . $baseCertDate);
                        
                        // Generate 4 dates
                        $action1 = "Suspension Reminder";
                        $action2 = "Suspend Letter";
                        $action3 = "Withdrawal Reminder";
                        $action4 = "Withdraw Letter";
                        
                        $date1 = $baseCertDate . ' + 11 MONTH';
                        $newdate1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                        
                        $date2 = $baseCertDate . ' + 12 MONTH';
                        $newdate2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                        
                        $date3 = $baseCertDate . ' + 14 MONTH';
                        $newdate3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                        
                        $date4 = $baseCertDate . ' + 16 MONTH';
                        $newdate4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                        
                        // Insert dates
                        $insertQuery = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action, :dueDate)";
                        
                        $statement = $db->prepare($insertQuery);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->bindParam(':action', $action1);
                        $statement->bindParam(':dueDate', $newdate1);
                        $statement->execute();
                        
                        $statement = $db->prepare($insertQuery);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->bindParam(':action', $action2);
                        $statement->bindParam(':dueDate', $newdate2);
                        $statement->execute();
                        
                        $statement = $db->prepare($insertQuery);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->bindParam(':action', $action3);
                        $statement->bindParam(':dueDate', $newdate3);
                        $statement->execute();
                        
                        $statement = $db->prepare($insertQuery);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->bindParam(':action', $action4);
                        $statement->bindParam(':dueDate', $newdate4);
                        $statement->execute();
                        
                        error_log("=== UPDATE - CA DATES INSERTED SUCCESSFULLY ===");
                    } else if (isset($uniServiceType['service_type']) && $uniServiceType['service_type'] == "SA-I") {
                        error_log("=== UPDATE - SA-I CDC: Will generate dates for NEXT SA-II (stored against SA-I) ===");
                        
                        // Get customer info (including service_count)
                        $sqla = "SELECT * FROM `customer_info` WHERE `id` = :customer";
                        $statement = $db->prepare($sqla);
                        $statement->bindParam(':customer', $_POST['customer_Id']);
                        $statement->execute();
                        $customer = $statement->fetch();

                        // Prefer FIRST CA CDC date as master base
                        $caCdcQuery = "SELECT MIN(crr.Date) AS first_date FROM cdc_report_review crr 
                                       INNER JOIN service_type_ca ca ON crr.ca_service_id = ca.ca_id 
                                       INNER JOIN service s ON ca.service = s.service_id 
                                       WHERE s.customer_id = :customer_id AND s.service_type = 'CA'";
                        $statement = $db->prepare($caCdcQuery);
                        $statement->bindParam(':customer_id', $_POST['customer_Id']);
                        $statement->execute();
                        $caCdcResult = $statement->fetch();

                        if (isset($caCdcResult['first_date']) && !empty($caCdcResult['first_date'])) {
                            $baseCertDate = $caCdcResult['first_date'];
                        } elseif (!empty($customer['certification_stage_date'])) {
                            $baseCertDate = $customer['certification_stage_date'];
                        } else {
                            $baseCertDate = date('Y-m-d');
                        }
                        error_log("UPDATE - Using FIRST CA CDC/base certification date for SA-I: " . $baseCertDate);

                        // Compute 36-month cycle offset from service_count
                        $serviceCount = isset($customer['service_count']) ? (int)$customer['service_count'] : 1;
                        $cycleOffsetMonths = 36 * max(0, $serviceCount - 1);

                        // SA-II months from first CA CDC date + cycle offset: 23, 24, 26, 28
                        $si1 = $cycleOffsetMonths + 23;  // Suspension Reminder
                        $si2 = $cycleOffsetMonths + 24;  // Suspend Letter
                        $si3 = $cycleOffsetMonths + 26;  // Withdrawal Reminder
                        $si4 = $cycleOffsetMonths + 28;  // Withdraw Letter

                        // Delete existing SA-II dates from SA-I service
                        $deleteExisting = "DELETE FROM ca_service_dates WHERE service_id = :serviceid AND action IN ('Suspension Reminder', 'Suspend Letter', 'Withdrawal Reminder', 'Withdraw Letter')";
                        $statement = $db->prepare($deleteExisting);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->execute();
                        error_log("UPDATE - Deleted existing SA-II dates from SA-I");

                        // Generate 4 dates for SA-II (store against SA-I service)
                        $action1 = "Suspension Reminder";
                        $action2 = "Suspend Letter";
                        $action3 = "Withdrawal Reminder";
                        $action4 = "Withdraw Letter";

                        $date1 = $baseCertDate . ' + ' . $si1 . ' MONTH';
                        $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                        $newdate1 = date('Y-m-d', strtotime($date1));

                        $date2 = $baseCertDate . ' + ' . $si2 . ' MONTH';
                        $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                        $newdate2 = date('Y-m-d', strtotime($date2));

                        $date3 = $baseCertDate . ' + ' . $si3 . ' MONTH';
                        $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                        $newdate3 = date('Y-m-d', strtotime($date3));

                        $date4 = $baseCertDate . ' + ' . $si4 . ' MONTH';
                        $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                        $newdate4 = date('Y-m-d', strtotime($date4));

                        // Insert dates against SA-I service (for next service SA-II)
                        $insertQuery = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action, :dueDate)";
                        
                        $statement = $db->prepare($insertQuery);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->bindParam(':action', $action1);
                        $statement->bindParam(':dueDate', $newdate1);
                        $statement->execute();
                        
                        $statement = $db->prepare($insertQuery);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->bindParam(':action', $action2);
                        $statement->bindParam(':dueDate', $newdate2);
                        $statement->execute();
                        
                        $statement = $db->prepare($insertQuery);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->bindParam(':action', $action3);
                        $statement->bindParam(':dueDate', $newdate3);
                        $statement->execute();
                        
                        $statement = $db->prepare($insertQuery);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->bindParam(':action', $action4);
                        $statement->bindParam(':dueDate', $newdate4);
                        $statement->execute();
                        
                        error_log("=== UPDATE - SA-II DATES INSERTED SUCCESSFULLY (against SA-I) ===");
                    } else if (isset($uniServiceType['service_type']) && $uniServiceType['service_type'] == "SA-II") {
                        error_log("=== UPDATE - SA-II CDC: Will generate dates for NEXT RC (stored against SA-II) ===");
                        
                        // Get customer ID to find CA CDC date
                        $customerQuery = "SELECT customer_id FROM service WHERE service_id = :id";
                        $statement = $db->prepare($customerQuery);
                        $statement->bindParam(':id', $universalServiceId);
                        $statement->execute();
                        $customerData = $statement->fetch();
                        
                        // Get CA service's CDC date
                        $caCdcQuery = "SELECT crr.Date FROM cdc_report_review crr 
                                       INNER JOIN service_type_ca ca ON crr.ca_service_id = ca.ca_id 
                                       INNER JOIN service s ON ca.service = s.service_id 
                                       WHERE s.customer_id = :customer_id AND s.service_type = 'CA' 
                                       ORDER BY crr.Date DESC LIMIT 1";
                        $statement = $db->prepare($caCdcQuery);
                        $statement->bindParam(':customer_id', $customerData['customer_id']);
                        $statement->execute();
                        $caCdcResult = $statement->fetch();
                        
                        // Use CA CDC date as base, fallback to current date
                        $baseCertDate = isset($caCdcResult['Date']) && !empty($caCdcResult['Date']) ? $caCdcResult['Date'] : date('Y-m-d');
                        error_log("UPDATE - Using CA CDC date as base for RC dates: " . $baseCertDate);
                        
                        // Delete existing RC dates from SA-II service
                        $deleteExisting = "DELETE FROM ca_service_dates WHERE service_id = :serviceid AND action IN ('1st Reminder', '2nd Reminder', 'Expire Letter')";
                        $statement = $db->prepare($deleteExisting);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->execute();
                        error_log("UPDATE - Deleted existing RC dates from SA-II");
                        
                        // RC months from CA CDC date: +34, +35, +36
                        $rc1 = 34;  // 1st Reminder
                        $rc2 = 35;  // 2nd Reminder
                        $rc3 = 36;  // Expire Letter
                        
                        // Generate 3 dates for RC (store against SA-II service)
                        $action1 = "1st Reminder";
                        $action2 = "2nd Reminder";
                        $action3 = "Expire Letter";
                        
                        $date1 = $baseCertDate . ' + ' . $rc1 . ' MONTH';
                        $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                        $newdate1 = date('Y-m-d', strtotime($date1));
                        
                        $date2 = $baseCertDate . ' + ' . $rc2 . ' MONTH';
                        $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                        $newdate2 = date('Y-m-d', strtotime($date2));
                        
                        $date3 = $baseCertDate . ' + ' . $rc3 . ' MONTH';
                        $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                        $newdate3 = date('Y-m-d', strtotime($date3));
                        
                        // Insert dates against SA-II service (for next service RC)
                        $insertQuery = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action, :dueDate)";
                        
                        $statement = $db->prepare($insertQuery);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->bindParam(':action', $action1);
                        $statement->bindParam(':dueDate', $newdate1);
                        $statement->execute();
                        
                        $statement = $db->prepare($insertQuery);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->bindParam(':action', $action2);
                        $statement->bindParam(':dueDate', $newdate2);
                        $statement->execute();
                        
                        $statement = $db->prepare($insertQuery);
                        $statement->bindParam(':serviceid', $universalServiceId);
                        $statement->bindParam(':action', $action3);
                        $statement->bindParam(':dueDate', $newdate3);
                        $statement->execute();
                    }
                }

                if ($cdc_status === 'Granted') {
                    $sqlRcService = "SELECT service FROM service_type_rc WHERE rc_id = :id";
                    $statement = $db->prepare($sqlRcService);
                    $statement->bindParam(':id', $service);
                    $statement->execute();
                    $rcRow = $statement->fetch();
                    if (isset($rcRow['service']) && !empty($rcRow['service'])) {
                        generateRcClientStatusDates($db, $rcRow['service'], $_POST['customer_Id']);
                    }
                }

                if ($is_cdc_only !== 1) {
                    $officeUsePayload = isset($_POST['office_use']) && is_array($_POST['office_use']) ? $_POST['office_use'] : [];
                    $officeUseEntries = normaliseCdcOfficePayload($officeUsePayload);
                    replaceCdcOfficeUse($db, (int) $_POST['crr_Id'], $officeUseEntries);
                }
                activateCustomerAfterGrantedCaCdc($db, $_POST['customer_Id'], $service, $cdc_status, $_POST['certificateNo']);

                // Save or clear ISO decisions for RC services.
                if (isset($_POST['iso_decisions'])) {
                    $isoDecisions = json_decode($_POST['iso_decisions'], true);
                    if (is_array($isoDecisions)) {
                        replaceCdcIsoDecisions($db, (int) $_POST['crr_Id'], $isoDecisions);
                        error_log("CDC UPDATE: Saved " . count(normaliseCdcIsoDecisions($isoDecisions)) . " ISO decisions for crr_id=" . $_POST['crr_Id']);
                    } else {
                        error_log("CDC UPDATE: Ignored invalid ISO decisions JSON for crr_id=" . $_POST['crr_Id']);
                    }
                }

                if (isset($universalServiceId) && !empty($universalServiceId)) {
                    aitfSyncClientStatusDates($db, $universalServiceId, $_POST['customer_Id'], $Date);
                }
                aitfSyncExistingClientStatusDatesForCustomer($db, $_POST['customer_Id'], $Date);

                header("Location: customerAccount.php?customerid=" . $_POST['customer_Id']);
                exit();
            } catch (Exception $ex) {
                error_log("CDC Update (crr_Id) Error: " . $ex->getMessage());
                $_SESSION['cdc_error'] = "Error updating CDC: " . $ex->getMessage();
                header("Location: customerAccount.php?customerid=" . $_POST['customer_Id']);
                exit();
            }
        } else {
            error_log("=== ENTERING INSERT BLOCK (crr_Id empty or not set) === crr_Id value: " . (isset($_POST['crr_Id']) ? "'" . $_POST['crr_Id'] . "'" : 'NOT SET'));

            if (isset($_POST['service_Id']) && !empty($_POST['service_Id'])) {
                error_log("=== service_Id is SET, proceeding with INSERT ===");
                try {
                    if (isset($_POST['iso_decisions'])) {
                        ensureCdcIsoDecisionsTable($db);
                    }

                    $applicationDateObj = null;
                    $receivedDateObj = null;
                    $reviewDateObj = null;

                    $Date = parseFormDate('Date', $applicationDateObj);
                    $receivedDateObj = null;
                    $audit_date_raw = isset($_POST['audit_date_CDC']) ? trim($_POST['audit_date_CDC']) : '';
                    error_log("CDC INSERT - audit_date_CDC field value: " . ($audit_date_raw !== '' ? $audit_date_raw : 'NOT SET'));
                    $audit_date_for_db = $audit_date_raw;
                    $revDate = parseFormDate('revDatecdc', $reviewDateObj);
                    $expiredate = parseFormDate('expiredate');
                    $periodicaldate = parseFormDate('periodicalaudit');
                    $assDate = parseFormDate('assDate');

                    $customerIdForRedirect = isset($_POST['customer_Id']) ? $_POST['customer_Id'] : '';
                    validateCdcDates($applicationDateObj, $receivedDateObj, $reviewDateObj, $customerIdForRedirect);

                    // Capture Member 1 status (review_1_granting)
                    $review_1_granting = "";
                    if (isset($_POST["cdc-status-mem1-g"])) {
                        $review_1_granting = 'Granted';
                    } elseif (isset($_POST['cdc-status-mem1-n'])) {
                        $review_1_granting = 'Not-Granted';
                    }

                    // Capture Member 2 status (review_2_granting)
                    $review_2_granting = "";
                    if (isset($_POST["cdc-status-mem2-g"])) {
                        $review_2_granting = 'Granted';
                    } elseif (isset($_POST['cdc-status-mem2-n'])) {
                        $review_2_granting = 'Not-Granted';
                    }

                    // Capture Member 3 status (cdc_status)
                    $cdc_status = "";
                    if (isset($_POST["cdc-status-mem3-g"])) {
                        $cdc_status = 'Granted';
                    } elseif (isset($_POST['cdc-status-mem3-n'])) {
                        $cdc_status = 'Not-Granted';
                    }

                    if (isset($_POST['Yes1'])) {
                        $Yes1 = 'checked';
                    } else {
                        $Yes1 = 'null';
                    }
                    if (isset($_POST['Yes2'])) {
                        $Yes2 = 'checked';
                    } else {
                        $Yes2 = 'null';
                    }
                    if (isset($_POST['Yes3'])) {
                        $Yes3 = 'checked';
                    } else {
                        $Yes3 = 'null';
                    }
                    if (isset($_POST['No1'])) {
                        $No1 = 'checked';
                    } else {
                        $No1 = 'null';
                    }
                    if (isset($_POST['No2'])) {
                        $No2 = 'checked';
                    } else {
                        $No2 = 'null';
                    }
                    if (isset($_POST['No3'])) {
                        $No3 = 'checked';
 
                    } 
                    
                    
                    else {
                        $No3 = 'null';
                    }

                    $service = $_POST['service_Id'];

                    // this code is validated by Gaurav Oli 

                    $is_cdc_only = isset($_POST['is_cdc_only']) ? (int)$_POST['is_cdc_only'] : 0;
                    $sql = "INSERT INTO `cdc_report_review`(`is_cdc_only`, `ca_service_id`, `customer_id`, `file_no`, `audit_organization`, `address`, `product_service`, `iaf_nacf`, `exclusion`, `audit_date`, `audit_type`, `covered_for`, `accreditation`,`lead_auditor`,`technical_expert`, `assDate`, `auditors`, `reviewer_1`, `reviewer_2`, `granted_by`, `system_during`, `system_complainte`, `comments_if_any`, `review_1_granting`, `review_2_granting`, `cdc_status`, `comments`, `Date`, `certificateNo`, `revCdc`, `revDatecdc`, `expiredate`, `periodicalaudit`, `ncval`, `ofival`, `oplusval`, `rev1_yes`, `rev2_yes`, `yes1`, `yes2`, `yes3`, `rev3_yes`) VALUES (:is_cdc_only,:ca_service_id,:customer_id,:file_no,:audit_organization,:address,:product_service,:iaf_nacf,:exclusion,:audit_date,:audit_type,:covered_for,:accreditation,:lead_auditor,:technical_expert,:assDate,:auditors,:reviewer_1,:reviewer_2,:granted_by,:system_during,:system_complainte,:comments_if_any,:review_1_granting,:review_2_granting,:cdc_status,:comments,:Date,:certificateNo,:revCdc,:revDatecdc,:expiredate,:periodicalaudit,:ncval,:ofival,:oplusval,:rev1_yes,:rev2_yes,:yes1,:yes2,:yes3,:rev3_yes);";
                    $statement = $db->prepare($sql);
                    $statement->bindParam(':is_cdc_only', $is_cdc_only);
                    $statement->bindParam(':ca_service_id', $service);
                    $statement->bindParam(':customer_id', $_POST['customer_Id']);
                    $statement->bindParam(':file_no', $_POST['file_No']);
                    $statement->bindParam(':audit_organization', $_POST['audited_Organisation']);
                    $statement->bindParam(':address', $_POST['address']);
                    $statement->bindParam(':product_service', $_POST['product_service']);
                    $statement->bindParam(':iaf_nacf', $_POST['iaf_nace']);
                    $statement->bindParam(':exclusion', $_POST['exclusion']);

                    $statement->bindParam(':audit_date', $audit_date_for_db);
                    $statement->bindParam(':audit_type', $_POST['audit_type']);
                    $statement->bindParam(':covered_for', $_POST['covered_for']);
                    $statement->bindParam(':accreditation', $_POST['accreditation']);
                    $statement->bindParam(':lead_auditor', $_POST['lead_auditor']);
                    $statement->bindParam(':technical_expert', $_POST['technical_expert']);
                    $statement->bindParam(':assDate', $assDate);
                    $statement->bindParam(':auditors', $_POST['auditors']);
                    $statement->bindParam(':reviewer_1', $_POST['reviewer-1']);
                    $statement->bindParam(':reviewer_2', $_POST['reviewer-2']);
                    $statement->bindParam(':granted_by', $_POST['granted-by']);
                    $statement->bindParam(':system_during', $_POST['system_during']);
                    $statement->bindParam(':system_complainte', $_POST['system_complainte']);
                    $statement->bindParam(':comments_if_any', $_POST['comments_if_any']);
                    $statement->bindParam(':review_1_granting', $review_1_granting);
                    $statement->bindParam(':review_2_granting', $review_2_granting);
                    $statement->bindParam(':cdc_status', $cdc_status);

                    $statement->bindParam(':comments', $_POST['comments']);
                    $statement->bindParam(':Date', $Date);


                    $statement->bindParam(':certificateNo', $_POST['certificateNo']);
                    $statement->bindParam(':revCdc', $_POST['revCdc']);

                    $statement->bindParam(':revDatecdc', $revDate);
                    $statement->bindParam(':expiredate', $expiredate);
                    $statement->bindParam(':periodicalaudit', $periodicaldate);
                    $statement->bindParam(':ncval', $_POST['ncval']);
                    $statement->bindParam(':ofival', $_POST['ofival']);
                    $statement->bindParam(':oplusval', $_POST['oplus']);
                    
                    // Handle CDC Member checkboxes
                    $rev1_yes = isset($_POST['checkrev1_yes']) ? 'Yes' : (isset($_POST['checkrev1_no']) ? 'No' : '');
                    $yes1 = isset($_POST['check1_yes']) ? 'Yes' : (isset($_POST['check1_no']) ? 'No' : '');
                    $rev2_yes = isset($_POST['checkrev2_yes']) ? 'Yes' : (isset($_POST['checkrev2_no']) ? 'No' : (isset($_POST['checkrev2_na']) ? 'N.A.' : ''));
                    $yes2 = isset($_POST['check2_yes']) ? 'Yes' : (isset($_POST['check2_no']) ? 'No' : '');
                    $yes3 = isset($_POST['check3_yes']) ? 'Yes' : (isset($_POST['check3_no']) ? 'No' : '');
                    $rev3_yes = isset($_POST['checkrev3_yes']) ? 'Yes' : (isset($_POST['checkrev3_no']) ? 'No' : '');
                    $statement->bindParam(':rev1_yes', $rev1_yes);
                $statement->bindParam(':rev2_yes', $rev2_yes);
                    $statement->bindParam(':yes1', $yes1);
                    $statement->bindParam(':yes2', $yes2);
                    $statement->bindParam(':yes3', $yes3);
                    $statement->bindParam(':rev3_yes', $rev3_yes);

                    error_log("CDC INSERT BEFORE: service=$service, customer=" . $_POST['customer_Id']);
                    $statement->execute();
                    $newCrrId = (int) $db->lastInsertId();
                    error_log("CDC INSERT AFTER: New record created with crr_id=$newCrrId for service=$service");
                    
                    // Verify the insert
                    $verify = "SELECT crr_id, ca_service_id FROM cdc_report_review WHERE crr_id = :crr_id";
                    $verifyStmt = $db->prepare($verify);
                    $verifyStmt->bindParam(':crr_id', $newCrrId);
                    $verifyStmt->execute();
                    $verifyResult = $verifyStmt->fetch();
                    error_log("CDC VERIFY INSERT: crr_id=" . $verifyResult['crr_id'] . ", ca_service_id=" . $verifyResult['ca_service_id']);
                    
                    // Verify that JOIN will work
                    $joinTest = "SELECT cas.ca_id, crr.crr_id FROM service_type_ca cas LEFT JOIN cdc_report_review crr ON cas.ca_id = crr.ca_service_id WHERE cas.ca_id = :ca_id";
                    $joinStmt = $db->prepare($joinTest);
                    $joinStmt->bindParam(':ca_id', $service);
                    $joinStmt->execute();
                    $joinResult = $joinStmt->fetch();
                    error_log("CDC JOIN TEST: ca_id=" . $joinResult['ca_id'] . ", crr_id from JOIN=" . $joinResult['crr_id']);

                    if ($is_cdc_only !== 1) {
                        $officeUsePayload = isset($_POST['office_use']) && is_array($_POST['office_use']) ? $_POST['office_use'] : [];
                        $officeUseEntries = normaliseCdcOfficePayload($officeUsePayload);
                        if ($newCrrId > 0) {
                            replaceCdcOfficeUse($db, $newCrrId, $officeUseEntries);
                        }
                    }
                    activateCustomerAfterGrantedCaCdc($db, $_POST['customer_Id'], $service, $cdc_status, $_POST['certificateNo']);
                    
                    // Save ISO decisions for RC services.
                    if ($newCrrId > 0 && isset($_POST['iso_decisions'])) {
                        $isoDecisions = json_decode($_POST['iso_decisions'], true);
                        if (is_array($isoDecisions)) {
                            replaceCdcIsoDecisions($db, $newCrrId, $isoDecisions);
                            error_log("CDC INSERT: Saved " . count(normaliseCdcIsoDecisions($isoDecisions)) . " ISO decisions for crr_id=" . $newCrrId);
                        } else {
                            error_log("CDC INSERT: Ignored invalid ISO decisions JSON for crr_id=" . $newCrrId);
                        }
                    }

                    // Update service table with crr_id to mark CDC as complete
                    // Check which service type and update accordingly
                    $updated = false;
                    if ($newCrrId > 0 && $service) {
                        error_log("CDC: Attempting to update service table. newCrrId=$newCrrId, service=$service");
                        $sql1 = "SELECT service FROM `service_type_ca` where ca_id = :id";
                        $statement = $db->prepare($sql1);
                        $statement->bindParam(":id", $service);
                        $statement->execute();
                        $ca = $statement->fetch();
                        error_log("CDC: CA check result: " . print_r($ca, true));
                        
                        if (isset($ca["service"]) && !empty($ca["service"])) {
                            $updateCrrId = "UPDATE service_type_ca SET crr_id = :crr_id WHERE ca_id = :service";
                            $statement = $db->prepare($updateCrrId);
                            $statement->bindParam(':crr_id', $newCrrId);
                            $statement->bindParam(':service', $service);
                            $result = $statement->execute();
                            $rowCount = $statement->rowCount();
                            $updated = true;
                            error_log("CDC: Updated CA service table. Result=$result, Rows affected=$rowCount");
                        } else {
                            // Check for SAI
                            $sql2_check = "SELECT service FROM `service_type_sai` where sai_id = :id";
                            $statement = $db->prepare($sql2_check);
                            $statement->bindParam(":id", $service);
                            $statement->execute();
                            $sai_check = $statement->fetch();
                            
                            if (isset($sai_check["service"]) && !empty($sai_check["service"])) {
                                $updateCrrId = "UPDATE service_type_sai SET crr_id = :crr_id WHERE sai_id = :service";
                                $statement = $db->prepare($updateCrrId);
                                $statement->bindParam(':crr_id', $newCrrId);
                                $statement->bindParam(':service', $service);
                                $statement->execute();
                                $updated = true;
                                error_log("CDC: Updated SAI service table");
                            } else {
                                // Check for SAII
                                $sql3_check = "SELECT service FROM `service_type_saii` where saii_id = :id";
                                $statement = $db->prepare($sql3_check);
                                $statement->bindParam(":id", $service);
                                $statement->execute();
                                $saii_check = $statement->fetch();
                                
                                if (isset($saii_check["service"]) && !empty($saii_check["service"])) {
                                    $updateCrrId = "UPDATE service_type_saii SET crr_id = :crr_id WHERE saii_id = :service";
                                    $statement = $db->prepare($updateCrrId);
                                    $statement->bindParam(':crr_id', $newCrrId);
                                    $statement->bindParam(':service', $service);
                                    $statement->execute();
                                    $updated = true;
                                    error_log("CDC: Updated SAII service table");
                                } else {
                                    // Check for RC
                                    $sql4_check = "SELECT service FROM `service_type_rc` WHERE rc_id = :id";
                                    $statement = $db->prepare($sql4_check);
                                    $statement->bindParam(":id", $service);
                                    $statement->execute();
                                    $rc_check = $statement->fetch();
                                    
                                    if (isset($rc_check["service"]) && !empty($rc_check["service"])) {
                                        $updateCrrId = "UPDATE service_type_rc SET crr_id = :crr_id WHERE rc_id = :service";
                                        $statement = $db->prepare($updateCrrId);
                                        $statement->bindParam(':crr_id', $newCrrId);
                                        $statement->bindParam(':service', $service);
                                        $statement->execute();
                                        $updated = true;
                                        error_log("CDC: Updated RC service table");
                                    } else {
                                        // Check for SP
                                        $sql5_check = "SELECT service FROM `service_type_sp` WHERE sp_id = :id";
                                        $statement = $db->prepare($sql5_check);
                                        $statement->bindParam(":id", $service);
                                        $statement->execute();
                                        $sp_check = $statement->fetch();
                                        
                                        if (isset($sp_check["service"]) && !empty($sp_check["service"])) {
                                            $updateCrrId = "UPDATE service_type_sp SET crr_id = :crr_id WHERE sp_id = :service";
                                            $statement = $db->prepare($updateCrrId);
                                            $statement->bindParam(':crr_id', $newCrrId);
                                            $statement->bindParam(':service', $service);
                                            $statement->execute();
                                            $updated = true;
                                            error_log("CDC: Updated SP service table");
                                        }
                                    }
                                }
                            }
                        }
                        
                        if (!$updated) {
                            error_log("CDC WARNING: No service table was updated! Service ID: $service");
                        }
                        
                        // Generate dates for CA or SA-I service after INSERT
                        if (isset($ca["service"]) && !empty($ca["service"])) {
                            // CA service
                            $universalServiceId = $ca["service"];
                            $uniService = "SELECT service_type FROM service WHERE service_id = :id";
                            $statement = $db->prepare($uniService);
                            $statement->bindParam(':id', $universalServiceId);
                            $statement->execute();
                            $uniServiceType = $statement->fetch();
                            
                            error_log("=== INSERT - CA SERVICE TYPE: " . (isset($uniServiceType['service_type']) ? $uniServiceType['service_type'] : "NOT SET"));
                            
                            if (isset($uniServiceType['service_type']) && $uniServiceType['service_type'] == "CA") {
                                error_log("=== INSERT - CA DATE GENERATION START ===");
                                
                                // Delete existing dates
                                $deleteExisting = "DELETE FROM ca_service_dates WHERE service_id = :serviceid AND action IN ('Suspension Reminder', 'Suspend Letter', 'Withdrawal Reminder', 'Withdraw Letter')";
                                $statement = $db->prepare($deleteExisting);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->execute();
                                
                                // Use the Date being saved
                                $baseCertDate = isset($Date) && !empty($Date) ? $Date : date('Y-m-d');
                                
                                // Generate 4 dates for CA
                                $action1 = "Suspension Reminder";
                                $action2 = "Suspend Letter";
                                $action3 = "Withdrawal Reminder";
                                $action4 = "Withdraw Letter";
                                
                                $date1 = $baseCertDate . ' + 11 MONTH';
                                $newdate1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                                
                                $date2 = $baseCertDate . ' + 12 MONTH';
                                $newdate2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                                
                                $date3 = $baseCertDate . ' + 14 MONTH';
                                $newdate3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                                
                                $date4 = $baseCertDate . ' + 16 MONTH';
                                $newdate4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                                
                                // Insert dates
                                $insertQuery = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action, :dueDate)";
                                
                                $statement = $db->prepare($insertQuery);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->bindParam(':action', $action1);
                                $statement->bindParam(':dueDate', $newdate1);
                                $statement->execute();
                                
                                $statement = $db->prepare($insertQuery);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->bindParam(':action', $action2);
                                $statement->bindParam(':dueDate', $newdate2);
                                $statement->execute();
                                
                                $statement = $db->prepare($insertQuery);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->bindParam(':action', $action3);
                                $statement->bindParam(':dueDate', $newdate3);
                                $statement->execute();
                                
                                $statement = $db->prepare($insertQuery);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->bindParam(':action', $action4);
                                $statement->bindParam(':dueDate', $newdate4);
                                $statement->execute();
                                
                                error_log("=== INSERT - CA DATES INSERTED SUCCESSFULLY ===");
                            }
                        } else if (isset($sai_check["service"]) && !empty($sai_check["service"])) {
                            // SA-I service
                            $universalServiceId = $sai_check["service"];
                            $uniService = "SELECT service_type FROM service WHERE service_id = :id";
                            $statement = $db->prepare($uniService);
                            $statement->bindParam(':id', $universalServiceId);
                            $statement->execute();
                            $uniServiceType = $statement->fetch();
                            
                            error_log("=== INSERT - SA-I SERVICE TYPE: " . (isset($uniServiceType['service_type']) ? $uniServiceType['service_type'] : "NOT SET"));
                            
                            if (isset($uniServiceType['service_type']) && $uniServiceType['service_type'] == "SA-I") {
                                error_log("=== INSERT - SA-I CDC: Will generate dates for NEXT SA-II (stored against SA-I) ===");
                                
                                // Get customer info (including service_count)
                                $sqla = "SELECT * FROM `customer_info` WHERE `id` = :customer";
                                $statement = $db->prepare($sqla);
                                $statement->bindParam(':customer', $_POST['customer_Id']);
                                $statement->execute();
                                $customer = $statement->fetch();

                                // Prefer FIRST CA CDC date as master base
                                $caCdcQuery = "SELECT MIN(crr.Date) AS first_date FROM cdc_report_review crr 
                                               INNER JOIN service_type_ca ca ON crr.ca_service_id = ca.ca_id 
                                               INNER JOIN service s ON ca.service = s.service_id 
                                               WHERE s.customer_id = :customer_id AND s.service_type = 'CA'";
                                $statement = $db->prepare($caCdcQuery);
                                $statement->bindParam(':customer_id', $_POST['customer_Id']);
                                $statement->execute();
                                $caCdcResult = $statement->fetch();

                                if (isset($caCdcResult['first_date']) && !empty($caCdcResult['first_date'])) {
                                    $baseCertDate = $caCdcResult['first_date'];
                                } elseif (!empty($customer['certification_stage_date'])) {
                                    $baseCertDate = $customer['certification_stage_date'];
                                } else {
                                    $baseCertDate = date('Y-m-d');
                                }
                                error_log("INSERT - Using FIRST CA CDC/base certification date for SA-I: " . $baseCertDate);

                                // Compute 36-month cycle offset from service_count
                                $serviceCount = isset($customer['service_count']) ? (int)$customer['service_count'] : 1;
                                $cycleOffsetMonths = 36 * max(0, $serviceCount - 1);

                                // SA-II months from first CA CDC date + cycle offset: 23, 24, 26, 28
                                $si1 = $cycleOffsetMonths + 23;  // Suspension Reminder
                                $si2 = $cycleOffsetMonths + 24;  // Suspend Letter
                                $si3 = $cycleOffsetMonths + 26;  // Withdrawal Reminder
                                $si4 = $cycleOffsetMonths + 28;  // Withdraw Letter

                                // Delete existing SA-II dates from SA-I service
                                $deleteExisting = "DELETE FROM ca_service_dates WHERE service_id = :serviceid AND action IN ('Suspension Reminder', 'Suspend Letter', 'Withdrawal Reminder', 'Withdraw Letter')";
                                $statement = $db->prepare($deleteExisting);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->execute();
                                error_log("INSERT - Deleted existing SA-II dates from SA-I");

                                // Generate 4 dates for SA-II (store against SA-I service)
                                $action1 = "Suspension Reminder";
                                $action2 = "Suspend Letter";
                                $action3 = "Withdrawal Reminder";
                                $action4 = "Withdraw Letter";

                                $date1 = $baseCertDate . ' + ' . $si1 . ' MONTH';
                                $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                                $newdate1 = date('Y-m-d', strtotime($date1));

                                $date2 = $baseCertDate . ' + ' . $si2 . ' MONTH';
                                $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                                $newdate2 = date('Y-m-d', strtotime($date2));

                                $date3 = $baseCertDate . ' + ' . $si3 . ' MONTH';
                                $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                                $newdate3 = date('Y-m-d', strtotime($date3));

                                $date4 = $baseCertDate . ' + ' . $si4 . ' MONTH';
                                $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                                $newdate4 = date('Y-m-d', strtotime($date4));

                                // Insert dates against SA-I service (for next service SA-II)
                                $insertQuery = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action, :dueDate)";
                                
                                $statement = $db->prepare($insertQuery);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->bindParam(':action', $action1);
                                $statement->bindParam(':dueDate', $newdate1);
                                $statement->execute();
                                
                                $statement = $db->prepare($insertQuery);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->bindParam(':action', $action2);
                                $statement->bindParam(':dueDate', $newdate2);
                                $statement->execute();
                                
                                $statement = $db->prepare($insertQuery);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->bindParam(':action', $action3);
                                $statement->bindParam(':dueDate', $newdate3);
                                $statement->execute();
                                
                                $statement = $db->prepare($insertQuery);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->bindParam(':action', $action4);
                                $statement->bindParam(':dueDate', $newdate4);
                                $statement->execute();
                                
                                error_log("=== INSERT - SA-II DATES INSERTED SUCCESSFULLY (against SA-I) ===");
                            }
                        } else if (isset($saii_check["service"]) && !empty($saii_check["service"])) {
                            // SA-II service
                            $universalServiceId = $saii_check["service"];
                            $uniService = "SELECT service_type FROM service WHERE service_id = :id";
                            $statement = $db->prepare($uniService);
                            $statement->bindParam(':id', $universalServiceId);
                            $statement->execute();
                            $uniServiceType = $statement->fetch();
                            
                            error_log("=== INSERT - SA-II SERVICE TYPE: " . (isset($uniServiceType['service_type']) ? $uniServiceType['service_type'] : "NOT SET"));
                            
                            if (isset($uniServiceType['service_type']) && $uniServiceType['service_type'] == "SA-II") {
                                error_log("=== INSERT - SA-II CDC: Will generate dates for NEXT RC (stored against SA-II) ===");
                                
                                // Get customer ID to find CA CDC date
                                $customerQuery = "SELECT customer_id FROM service WHERE service_id = :id";
                                $statement = $db->prepare($customerQuery);
                                $statement->bindParam(':id', $universalServiceId);
                                $statement->execute();
                                $customerData = $statement->fetch();
                                
                                // Get CA service's CDC date
                                $caCdcQuery = "SELECT crr.Date FROM cdc_report_review crr 
                                               INNER JOIN service_type_ca ca ON crr.ca_service_id = ca.ca_id 
                                               INNER JOIN service s ON ca.service = s.service_id 
                                               WHERE s.customer_id = :customer_id AND s.service_type = 'CA' 
                                               ORDER BY crr.Date DESC LIMIT 1";
                                $statement = $db->prepare($caCdcQuery);
                                $statement->bindParam(':customer_id', $customerData['customer_id']);
                                $statement->execute();
                                $caCdcResult = $statement->fetch();
                                
                                // Use CA CDC date as base, fallback to current date
                                $baseCertDate = isset($caCdcResult['Date']) && !empty($caCdcResult['Date']) ? $caCdcResult['Date'] : date('Y-m-d');
                                error_log("INSERT - Using CA CDC date as base for RC dates: " . $baseCertDate);
                                
                                // Delete existing RC dates from SA-II service
                                $deleteExisting = "DELETE FROM ca_service_dates WHERE service_id = :serviceid AND action IN ('1st Reminder', '2nd Reminder', 'Expire Letter')";
                                $statement = $db->prepare($deleteExisting);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->execute();
                                error_log("INSERT - Deleted existing RC dates from SA-II");
                                
                                // RC months from CA CDC date: +34, +35, +36
                                $rc1 = 34;  // 1st Reminder
                                $rc2 = 35;  // 2nd Reminder
                                $rc3 = 36;  // Expire Letter
                                
                                // Generate 3 dates for RC (store against SA-II service)
                                $action1 = "1st Reminder";
                                $action2 = "2nd Reminder";
                                $action3 = "Expire Letter";
                                
                                $date1 = $baseCertDate . ' + ' . $rc1 . ' MONTH';
                                $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                                $newdate1 = date('Y-m-d', strtotime($date1));
                                
                                $date2 = $baseCertDate . ' + ' . $rc2 . ' MONTH';
                                $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                                $newdate2 = date('Y-m-d', strtotime($date2));
                                
                                $date3 = $baseCertDate . ' + ' . $rc3 . ' MONTH';
                                $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                                $newdate3 = date('Y-m-d', strtotime($date3));
                                
                                // Insert dates against SA-II service (for next service RC)
                                $insertQuery = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action, :dueDate)";
                                
                                $statement = $db->prepare($insertQuery);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->bindParam(':action', $action1);
                                $statement->bindParam(':dueDate', $newdate1);
                                $statement->execute();
                                
                                $statement = $db->prepare($insertQuery);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->bindParam(':action', $action2);
                                $statement->bindParam(':dueDate', $newdate2);
                                $statement->execute();
                                
                                $statement = $db->prepare($insertQuery);
                                $statement->bindParam(':serviceid', $universalServiceId);
                                $statement->bindParam(':action', $action3);
                                $statement->bindParam(':dueDate', $newdate3);
                                $statement->execute();
                                
                                error_log("=== INSERT - RC DATES INSERTED SUCCESSFULLY (against SA-II) ===");
                            }
                        }
                    } else {
                        error_log("CDC ERROR: newCrrId or service is empty. newCrrId=$newCrrId, service=$service");
                    }

                    if ($cdc_status === 'Granted') {
                        $sqlRcService = "SELECT service FROM service_type_rc WHERE rc_id = :id";
                        $statement = $db->prepare($sqlRcService);
                        $statement->bindParam(':id', $service);
                        $statement->execute();
                        $rcRow = $statement->fetch();
                        if (isset($rcRow['service']) && !empty($rcRow['service'])) {
                            generateRcClientStatusDates($db, $rcRow['service'], $_POST['customer_Id']);
                        }
                    }

                    if (isset($universalServiceId) && !empty($universalServiceId)) {
                        aitfSyncClientStatusDates($db, $universalServiceId, $_POST['customer_Id'], $Date);
                    } elseif (!empty($_POST['uni_serviceid'])) {
                        aitfSyncClientStatusDates($db, $_POST['uni_serviceid'], $_POST['customer_Id'], $Date);
                    }
                    aitfSyncExistingClientStatusDatesForCustomer($db, $_POST['customer_Id'], $Date);

                    header("Location: customerAccount.php?customerid=" . $_POST['customer_Id']);
                    exit();
                } catch (Exception $ex) {
                    error_log("CDC Insert Error: " . $ex->getMessage());
                    $_SESSION['cdc_error'] = "Error inserting CDC: " . $ex->getMessage();
                    header("Location: customerAccount.php?customerid=" . $_POST['customer_Id']);
                    exit();
                }
            }
        }
    }


    if (isset($_POST['update_cdc'])) {

        if (isset($_POST['crr_Id']) && !empty($_POST['crr_Id'])) {
            try {
                $applicationDateObj = null;
                $receivedDateObj = null;
                $reviewDateObj = null;

                $Date = parseFormDate('Date', $applicationDateObj);
                $audit_dateII = parseFormDate('application_received_date', $receivedDateObj);
                if ($audit_dateII === 'null') {
                    $audit_dateII = parseFormDate('audit_dateII', $receivedDateObj);
                }
                $revDate = parseFormDate('revDatecdc', $reviewDateObj);
                $expiredate = parseFormDate('expiredate');
                $periodicaldate = parseFormDate('periodicalaudit');
                $assDate = parseFormDate('assDate');

                $customerIdForRedirect = isset($_POST['customer_Id']) ? $_POST['customer_Id'] : '';
                validateCdcDates($applicationDateObj, $receivedDateObj, $reviewDateObj, $customerIdForRedirect);

                // Capture Member 1 status (review_1_granting)
                $review_1_granting = "";
                if (isset($_POST["cdc-status-mem1-g"])) {
                    $review_1_granting = 'Granted';
                } elseif (isset($_POST['cdc-status-mem1-n'])) {
                    $review_1_granting = 'Not-Granted';
                }

                // Capture Member 2 status (review_2_granting)
                $review_2_granting = "";
                if (isset($_POST["cdc-status-mem2-g"])) {
                    $review_2_granting = 'Granted';
                } elseif (isset($_POST['cdc-status-mem2-n'])) {
                    $review_2_granting = 'Not-Granted';
                }

                // Capture Member 3 status (cdc_status)
                $cdc_status = "";
                if (isset($_POST["cdc-status-mem3-g"])) {
                    $cdc_status = 'Granted';
                } elseif (isset($_POST['cdc-status-mem3-n'])) {
                    $cdc_status = 'Not-Granted';
                }

                if (isset($_POST['Yes1'])) {
                    $Yes1 = 'checked';
                } else {
                    $Yes1 = 'null';
                }
                if (isset($_POST['Yes2'])) {
                    $Yes2 = 'checked';
                } else {
                    $Yes2 = 'null';
                }
                if (isset($_POST['Yes3'])) {
                    $Yes3 = 'checked';
                } else {
                    $Yes3 = 'null';
                }
                if (isset($_POST['No1'])) {
                    $No1 = 'checked';
                } else {
                    $No1 = 'null';
                }
                if (isset($_POST['No2'])) {
                    $No2 = 'checked';
                } else {
                    $No2 = 'null';
                }
                if (isset($_POST['No3'])) {
                    $No3 = 'checked';
                } else {
                    $No3 = 'null';
                }

                $service = $_POST['service_Id'];
                // echo $service;
                $sql = "UPDATE `cdc_report_review` SET `file_no`=:file_no,`audit_organization`=:audit_organization,`address`=:address,`product_service`=:product_service,`iaf_nacf`=:iaf_nacf,`exclusion`=:exclusion,`audit_date`=:audit_date,`audit_type`=:audit_type,`covered_for`=:covered_for,`accreditation`=:accreditation,`lead_auditor`=:lead_auditor,`technical_expert`=:technical_expert,`assDate`=:assDate,`auditors`=:auditors,`reviewer_1`=:reviewer_1,`reviewer_2`=:reviewer_2,`granted_by`=:granted_by,`system_during`=:system_during,`system_complainte`=:system_complainte,`comments_if_any`=:comments_if_any,`review_1_granting`=:review_1_granting,`review_2_granting`=:review_2_granting,`cdc_status`=:cdc_status,`comments`=:comments,`Date`=:Date,`certificateNo`=:certificateNo,`revCdc`=:revCdc,`revDatecdc`=:revDatecdc,`expiredate`=:expiredate,`periodicalaudit`=:periodicalaudit,`ncval`=:ncval,`ofival`=:ofival,`oplusval`=:oplusval,`rev1_yes`=:rev1_yes,`rev2_yes`=:rev2_yes,`yes1`=:yes1,`yes2`=:yes2,`yes3`=:yes3,`rev3_yes`=:rev3_yes WHERE crr_id=:crr_Id and ca_service_id=:ca_service_id ";
                $statement = $db->prepare($sql);
                $statement->bindParam(':ca_service_id', $service);
                $statement->bindParam(':crr_Id', $_POST['crr_Id']);
                $statement->bindParam(':file_no', $_POST['file_No']);
                $statement->bindParam(':audit_organization', $_POST['audited_Organisation']);
                $statement->bindParam(':audit_date', $audit_dateII);
                $statement->bindParam(':address', $_POST['address']);
                $statement->bindParam(':product_service', $_POST['product_service']);
                $statement->bindParam(':iaf_nacf', $_POST['iaf_nace']);
                $statement->bindParam(':exclusion', $_POST['exclusion']);
                $statement->bindParam(':audit_type', $_POST['audit_type']);
                $statement->bindParam(':covered_for', $_POST['covered_for']);
                $statement->bindParam(':accreditation', $_POST['accreditation']);
                $statement->bindParam(':lead_auditor', $_POST['lead_auditor']);
                $statement->bindParam(':technical_expert', $_POST['technical_expert']);
                $statement->bindParam(':assDate', $assDate);
                $statement->bindParam(':auditors', $_POST['auditors']);
                $statement->bindParam(':reviewer_1', $_POST['reviewer-1']);
                $statement->bindParam(':reviewer_2', $_POST['reviewer-2']);
                $statement->bindParam(':granted_by', $_POST['granted-by']);
                $statement->bindParam(':system_during', $_POST['system_during']);
                $statement->bindParam(':system_complainte', $_POST['system_complainte']);
                $statement->bindParam(':comments_if_any', $_POST['comments_if_any']);
                $statement->bindParam(':review_1_granting', $review_1_granting);
                $statement->bindParam(':review_2_granting', $review_2_granting);
                $statement->bindParam(':cdc_status', $cdc_status);

                $statement->bindParam(':comments', $_POST['comments']);
                $statement->bindParam(':Date', $Date);


                $statement->bindParam(':certificateNo', $_POST['certificateNo']);
                $statement->bindParam(':revCdc', $_POST['revCdc']);

                $statement->bindParam(':revDatecdc', $revDate);
                $statement->bindParam(':expiredate', $expiredate);
                $statement->bindParam(':periodicalaudit', $periodicaldate);
                $statement->bindParam(':ncval', $_POST['ncval']);
                $statement->bindParam(':ofival', $_POST['ofival']);
                $statement->bindParam(':oplusval', $_POST['oplus']);
                
                // Handle CDC Member checkboxes
                $rev1_yes = isset($_POST['checkrev1_yes']) ? 'Yes' : (isset($_POST['checkrev1_no']) ? 'No' : '');
                $rev2_yes = isset($_POST['checkrev2_yes']) ? 'Yes' : (isset($_POST['checkrev2_no']) ? 'No' : (isset($_POST['checkrev2_na']) ? 'N.A.' : ''));
                $yes1 = isset($_POST['check1_yes']) ? 'Yes' : (isset($_POST['check1_no']) ? 'No' : '');
                $yes2 = isset($_POST['check2_yes']) ? 'Yes' : (isset($_POST['check2_no']) ? 'No' : '');
                $yes3 = isset($_POST['check3_yes']) ? 'Yes' : (isset($_POST['check3_no']) ? 'No' : '');
                $rev3_yes = isset($_POST['checkrev3_yes']) ? 'Yes' : (isset($_POST['checkrev3_no']) ? 'No' : '');
                $statement->bindParam(':rev1_yes', $rev1_yes);
                $statement->bindParam(':rev2_yes', $rev2_yes);
                $statement->bindParam(':yes1', $yes1);
                $statement->bindParam(':yes2', $yes2);
                $statement->bindParam(':yes3', $yes3);
                $statement->bindParam(':rev3_yes', $rev3_yes);

                $statement->execute();

                $officeUsePayload = isset($_POST['office_use']) && is_array($_POST['office_use']) ? $_POST['office_use'] : [];
                $officeUseEntries = normaliseCdcOfficePayload($officeUsePayload);
                replaceCdcOfficeUse($db, (int) $_POST['crr_Id'], $officeUseEntries);
                activateCustomerAfterGrantedCaCdc($db, $_POST['customer_Id'], $service, $cdc_status, $_POST['certificateNo']);

                $resolvedServiceId = aitfResolveUniversalServiceId($db, isset($_POST['uni_serviceid']) ? $_POST['uni_serviceid'] : '', $service, isset($_POST['audit_type']) ? $_POST['audit_type'] : '');
                if (!empty($resolvedServiceId)) {
                    aitfSyncClientStatusDates($db, $resolvedServiceId, $_POST['customer_Id'], $Date);
                }
                aitfSyncExistingClientStatusDatesForCustomer($db, $_POST['customer_Id'], $Date);

                header("Location: customerAccount.php?customerid=" . $_POST['customer_Id']);
                exit();
            } catch (Exception $ex) {
                error_log("CDC Update2 Error: " . $ex->getMessage());
                $_SESSION['cdc_error'] = "Error updating CDC: " . $ex->getMessage();
                header("Location: customerAccount.php?customerid=" . $_POST['customer_Id']);
                exit();
            }
        } else {

            if (isset($_POST['service_Id']) && !empty($_POST['service_Id'])) {
                try {


                    $applicationDateObj = null;
                    $receivedDateObj = null;
                    $reviewDateObj = null;

                    $audit_dateII = parseFormDate('application_received_date', $receivedDateObj);
                    $Date = parseFormDate('Date', $applicationDateObj);
                    $revDate = parseFormDate('revDatecdc', $reviewDateObj);
                    $expiredate = parseFormDate('expiredate');
                    $periodicaldate = parseFormDate('periodicalaudit');
                    $assDate = parseFormDate('assDate');

                    $customerIdForRedirect = isset($_POST['customer_Id']) ? $_POST['customer_Id'] : '';
                    validateCdcDates($applicationDateObj, $receivedDateObj, $reviewDateObj, $customerIdForRedirect);

                    // Capture Member 1 status (review_1_granting)
                    $review_1_granting = "";
                    if (isset($_POST["cdc-status-mem1-g"])) {
                        $review_1_granting = 'Granted';
                    } elseif (isset($_POST['cdc-status-mem1-n'])) {
                        $review_1_granting = 'Not-Granted';
                    }

                    // Capture Member 2 status (review_2_granting)
                    $review_2_granting = "";
                    if (isset($_POST["cdc-status-mem2-g"])) {
                        $review_2_granting = 'Granted';
                    } elseif (isset($_POST['cdc-status-mem2-n'])) {
                        $review_2_granting = 'Not-Granted';
                    }

                    // Capture Member 3 status (cdc_status)
                    $cdc_status = "";
                    if (isset($_POST["cdc-status-mem3-g"])) {
                        $cdc_status = 'Granted';
                    } elseif (isset($_POST['cdc-status-mem3-n'])) {
                        $cdc_status = 'Not-Granted';
                    }

                    if (isset($_POST['Yes1'])) {
                        $Yes1 = 'checked';
                    } else {
                        $Yes1 = 'null';
                    }
                    if (isset($_POST['Yes2'])) {
                        $Yes2 = 'checked';
                    } else {
                        $Yes2 = 'null';
                    }
                    if (isset($_POST['Yes3'])) {
                        $Yes3 = 'checked';
                    } else {
                        $Yes3 = 'null';
                    }
                    if (isset($_POST['No1'])) {
                        $No1 = 'checked';
                    } else {
                        $No1 = 'null';
                    }
                    if (isset($_POST['No2'])) {
                        $No2 = 'checked';
                    } else {
                        $No2 = 'null';
                    }
                    if (isset($_POST['No3'])) {
                        $No3 = 'checked';
                    } else {
                        $No3 = 'null';
                    }

                    $service = $_POST['service_Id'];

                    $sql = "INSERT INTO `cdc_report_review`(`ca_service_id`, `customer_id`, `file_no`, `audit_organization`, `address`, `product_service`, `iaf_nacf`, `exclusion`, `audit_date`, `audit_type`, `covered_for`, `accreditation`,`lead_auditor`,`technical_expert`, `assDate`, `auditors`, `reviewer_1`, `reviewer_2`, `granted_by`, `system_during`, `comments_if_any`, `review_1_granting`, `review_2_granting`, `cdc_status`, `comments`, `Date`, `certificateNo`, `revCdc`, `revDatecdc`, `expiredate`, `periodicalaudit`) VALUES (:ca_service_id,:customer_id,:file_no,:audit_organization,:address,:product_service,:iaf_nacf,:exclusion,:audit_date,:audit_type,:covered_for,:accreditation,:lead_auditor,:technical_expert,:assDate,:auditors,:reviewer_1,:reviewer_2,:granted_by,:system_during,:comments_if_any,:review_1_granting,:review_2_granting,:cdc_status,:comments,:Date,:certificateNo,:revCdc,:revDatecdc,:expiredate,:periodicalaudit)";
                    $statement = $db->prepare($sql);
                    $statement->bindParam(':ca_service_id', $service);
                    $statement->bindParam(':customer_id', $_POST['customer_Id']);
                    $statement->bindParam(':file_no', $_POST['file_No']);
                    $statement->bindParam(':audit_organization', $_POST['audited_Organisation']);
                    $statement->bindParam(':address', $_POST['address']);
                    $statement->bindParam(':product_service', $_POST['product_service']);
                    $statement->bindParam(':iaf_nacf', $_POST['iaf_nace']);
                    $statement->bindParam(':exclusion', $_POST['exclusion']);

                    $statement->bindParam(':audit_date', $audit_dateII);
                    $statement->bindParam(':audit_type', $_POST['audit_type']);
                    $statement->bindParam(':covered_for', $_POST['covered_for']);
                    $statement->bindParam(':accreditation', $_POST['accreditation']);
                    $statement->bindParam(':lead_auditor', $_POST['lead_auditor']);
                    $statement->bindParam(':technical_expert', $_POST['technical_expert']);
                    $statement->bindParam(':assDate', $assDate);
                        $statement->bindParam(':auditors', $_POST['auditors']);
                    $statement->bindParam(':reviewer_1', $_POST['reviewer-1']);
                    $statement->bindParam(':reviewer_2', $_POST['reviewer-2']);
                    $statement->bindParam(':granted_by', $_POST['granted-by']);
                    $statement->bindParam(':system_during', $_POST['system_during']);
                    $statement->bindParam(':comments_if_any', $_POST['comments_if_any']);
                    $statement->bindParam(':review_1_granting', $review_1_granting);
                    $statement->bindParam(':review_2_granting', $review_2_granting);
                    $statement->bindParam(':cdc_status', $cdc_status);
    
                    $statement->bindParam(':comments', $_POST['comments']);
                    $statement->bindParam(':Date', $Date);


                    $statement->bindParam(':certificateNo', $_POST['certificateNo']);
                    $statement->bindParam(':revCdc', $_POST['revCdc']);

                    $statement->bindParam(':revDatecdc', $revDate);
                    $statement->bindParam(':expiredate', $expiredate);
                    $statement->bindParam(':periodicalaudit', $periodicaldate);

                    $statement->execute();

                    $officeUsePayload = isset($_POST['office_use']) && is_array($_POST['office_use']) ? $_POST['office_use'] : [];
                    $officeUseEntries = normaliseCdcOfficePayload($officeUsePayload);
                    $newCrrId = (int) $db->lastInsertId();
                    if ($newCrrId > 0) {
                        replaceCdcOfficeUse($db, $newCrrId, $officeUseEntries);
                    }
                    activateCustomerAfterGrantedCaCdc($db, $_POST['customer_Id'], $service, $cdc_status, $_POST['certificateNo']);

                    if ($cdc_status === 'Granted') {
                        $sqlRcService = "SELECT service FROM service_type_rc WHERE rc_id = :id";
                        $statement = $db->prepare($sqlRcService);
                        $statement->bindParam(':id', $service);
                        $statement->execute();
                        $rcRow = $statement->fetch();
                        if (isset($rcRow['service']) && !empty($rcRow['service'])) {
                            generateRcClientStatusDates($db, $rcRow['service'], $_POST['customer_Id']);
                        }
                    }

                    if (isset($universalServiceId) && !empty($universalServiceId)) {
                        aitfSyncClientStatusDates($db, $universalServiceId, $_POST['customer_Id'], $Date);
                    } elseif (!empty($_POST['uni_serviceid'])) {
                        aitfSyncClientStatusDates($db, $_POST['uni_serviceid'], $_POST['customer_Id'], $Date);
                    }
                    aitfSyncExistingClientStatusDatesForCustomer($db, $_POST['customer_Id'], $Date);

                    header("Location: customerAccount.php?customerid=" . $_POST['customer_Id']);
                    exit();
                } catch (Exception $ex) {
                    error_log("CDC Save Error: " . $ex->getMessage());
                    $_SESSION['cdc_error'] = "Error saving CDC: " . $ex->getMessage();
                    header("Location: customerAccount.php?customerid=" . $_POST['customer_Id']);
                    exit();
                }
            }
        }
    }
}



// this code is validated by gaurav oli











// If we reach here and it's a POST request, something went wrong
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerId = isset($_POST['customer_Id']) ? $_POST['customer_Id'] : '';
    if ($customerId) {
        error_log("CDC Form: No matching condition found for POST request");
        header("Location: customerAccount.php?customerid=" . $customerId);
        exit();
    }
}

ob_end_flush();

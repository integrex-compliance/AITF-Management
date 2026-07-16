<?php

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
// Enable error display for debugging but suppress deprecation warnings for PDF generation
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 0); // Disable display to prevent output before PDF headers

ob_start(); // Start output buffering to prevent header errors
session_start();
include_once './dbConnect.php';
include_once './include/cdc_office_use.php';
include_once './include/certification_cycle_helper.php';
date_default_timezone_set('Asia/Kolkata');

// Debug: Check if crr_id is received
error_log("=== CDC PDF REQUEST RECEIVED ===");
error_log("POST Data: " . print_r($_POST, true));



if (isset($_POST['crr_id'])) {
    error_log("CDC PDF: crr_id received = " . $_POST['crr_id']);
} else {
    error_log("CDC PDF ERROR: No crr_id received");
    echo "<h2>DEBUG: No crr_id received in POST data</h2>";
    echo "<pre>POST data: ";
    print_r($_POST);
    echo "</pre>";
    die();
}

require_once('tcpdf_include.php');

function parseCdcInputDate($value)
{
    if (!isset($value) || $value === '') {
        return null;
    }

    $rawValue = trim($value);
    $formats = ['d.m.Y', 'd-m-Y', 'd/m/Y', 'Y-m-d'];

    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $rawValue);
        if ($dt instanceof DateTime && $dt->format($format) === $rawValue) {
            return $dt;
        }
    }

    $timestamp = strtotime($rawValue);
    if ($timestamp !== false) {
        return new DateTime(date('Y-m-d', $timestamp));
    }

    return null;
}

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $customerIdForRedirect = isset($_POST['customer_Id']) ? $_POST['customer_Id'] : '';
    
    // cdcRecomendation.php is used by the customer page for PDF generation. Treat crr_id
    // posts as read-only unless a legacy save flag is explicitly present.
    $isMutationRequest = isset($_POST['save_cdc']) || isset($_POST['update_cdc']);
    $isDownloadRequest = (isset($_POST['crr_id']) && !empty($_POST['crr_id']) && !$isMutationRequest);
    
    error_log("CDC: isDownloadRequest = " . ($isDownloadRequest ? 'YES' : 'NO'));
    error_log("CDC: crr_id = " . (isset($_POST['crr_id']) ? $_POST['crr_id'] : 'NOT SET'));
    error_log("CDC: Date = " . (isset($_POST['Date']) ? $_POST['Date'] : 'NOT SET'));
    
    // Skip date validation for download requests
    if (!$isDownloadRequest) {
        error_log("CDC: Running date validation");
        $applicationDateObj = parseCdcInputDate(isset($_POST['Date']) ? $_POST['Date'] : null);

        $receivedDateObj = null;
        // Check for audit date in order of preference: audit_date_CDC, application_received_date, audit_dateII
        if (isset($_POST['audit_date_CDC'])) {
            $receivedDateObj = parseCdcInputDate($_POST['audit_date_CDC']);
        }
        if (!$receivedDateObj && isset($_POST['application_received_date'])) {
            $receivedDateObj = parseCdcInputDate($_POST['application_received_date']);
        }
        if (!$receivedDateObj && isset($_POST['audit_dateII'])) {
            $receivedDateObj = parseCdcInputDate($_POST['audit_dateII']);
            if ($receivedDateObj) {
                $_POST['application_received_date'] = $receivedDateObj->format('Y-m-d');
            }
        }

        $reviewDateObj = parseCdcInputDate(isset($_POST['revDatecdc']) ? $_POST['revDatecdc'] : null);

        if (!$applicationDateObj) {
            $_SESSION['cdc_error'] = 'Application Date is required.';
            header("Location: customerAccount.php?customerid=" . $customerIdForRedirect);
            exit;
        }

        if (!$receivedDateObj) {
            $_SESSION['cdc_error'] = 'Application Received Date is required and must be a valid date.';
            header("Location: customerAccount.php?customerid=" . $customerIdForRedirect);
            exit;
        }

        if ($receivedDateObj <= $applicationDateObj) {
            $_SESSION['cdc_error'] = 'Application Received Date should always greater than Application Date.';
            header("Location: customerAccount.php?customerid=" . $customerIdForRedirect);
            exit;
        }

        if ($reviewDateObj && $reviewDateObj < $receivedDateObj) {
            $_SESSION['cdc_error'] = 'Review Date must be on or after Application Received Date.';
            header("Location: customerAccount.php?customerid=" . $customerIdForRedirect);
            exit;
        }

        $_POST['Date'] = $applicationDateObj->format('Y-m-d');
        $_POST['application_received_date'] = $receivedDateObj->format('Y-m-d');
        $_POST['audit_dateII'] = $receivedDateObj->format('Y-m-d');
    } else {
        error_log("CDC PDF: Download request detected - skipping date validation");
    }
}

//database details get and display
$file_no = "";
$audit_organization = "";
$address = "";
$product_service = "";
$iaf_nacf = "";
$exclusion = "";
$audit_type = "";
$audit_dates = "";
$covered_for = "";
$accreditation = "";
$lead_auditor = "";
$technical_expert = "";
$assDate = "";
$auditors = "";
$cdc_members = "";
$cdc_members1 = "";
$cdc_members2 = "";
$cdc_members3 = "";
$review1date = "";
$system_during = "";
$system_complaint = "";
$comments_if_any = "";

$yes1 = "";
$no1 = "";
$yes2 = "";
$no2 = "";
$yes3 = "";
$no3 = "";

$Date = "";
$rc_auditdate = "";
$rc_restoredate = "";
$audit_performance = "";
$review1Date = "";
$comments = "";
$rev2Comment = "";
$certificateNo = "";
$revCdc = "";
$revDatecdc = "";
$expiredate = "";
$periodicalaudit = "";
$caServiceStatusUpdate = "";

$nextaudit = '';

// Accept both crr_id (from download button) and crr_Id (from form)
// But only process updates if it's NOT a download-only request
if (((isset($_POST['crr_Id']) && !empty($_POST['crr_Id'])) || (isset($_POST['crr_id']) && !empty($_POST['crr_id']))) && !$isDownloadRequest) {
    try {
        // Use whichever one is set
        $_POST['crr_Id'] = isset($_POST['crr_Id']) ? $_POST['crr_Id'] : $_POST['crr_id'];
        $serviceid = isset($_POST['uni_serviceid']) ? $_POST['uni_serviceid'] : '';
        // Check for audit date field - prioritize audit_date_CDC over audit_dateII
        if (isset($_POST['audit_date_CDC']) && !empty($_POST['audit_date_CDC'])) {
            $audit_dateII = date("Y-m-d", strtotime($_POST['audit_date_CDC']));
        } elseif (isset($_POST['audit_dateII']) && !empty($_POST['audit_dateII'])) {
            $audit_dateII = date("Y-m-d", strtotime($_POST['audit_dateII']));
        } else {
            $audit_dateII = NULL;
        }

        if (isset($_POST['Date']) && !empty($_POST['Date'])) {
            $Date = date("Y-m-d", strtotime($_POST['Date']));
        } else {
            $Date = date('Y-m-d');
        }

        if (isset($_POST['date-restore1']) && !empty($_POST['date-restore1'])) {
            $rc_auditdate = date("Y-m-d", strtotime($_POST['date-restore1']));
        } else {
            $rc_auditdate = NULL;
        }

        if (isset($_POST['date-restore2']) && !empty($_POST['date-restore2'])) {
            $rc_restoredate = date("Y-m-d", strtotime($_POST['date-restore2']));
        } else {
            $rc_restoredate = NULL;
        }

        if (isset($_POST["audit_performance"]) && !empty($_POST["audit_performance"])) {
            $audit_performance = date("Y-m-d", strtotime($_POST['audit_performance']));
        } else {
            $audit_performance = NULL;
        }

        if (isset($_POST["review-date-1"]) && !empty($_POST["review-date-1"])) {
            $review1Date = date("Y-m-d", strtotime($_POST['review-date-1']));
        } else {
            $review1Date = NULL;
        }

        if (isset($_POST['revDatecdc']) && !empty($_POST['revDatecdc'])) {
            $revDate = date("Y-m-d", strtotime($_POST['revDatecdc']));
        } else {
            $revDate = NULL;
        }

        if (isset($_POST['expiredate']) && !empty($_POST['expiredate'])) {
            $expiredate = date("Y-m-d", strtotime($_POST['expiredate']));
        } else {
            $expiredate = NULL;
        }
        if (isset($_POST['assDate']) && !empty($_POST['assDate'])) {
            $assDate = date("Y-m-d", strtotime($_POST['assDate']));
        } else if (!empty($Date) && $Date !== 'null') {
            $assDate = $Date;
        } else {
            $assDate = date('Y-m-d');
        }

        if (isset($_POST['periodicalaudit']) && !empty($_POST['periodicalaudit'])) {
            $periodicaldate = date("Y-m-d", strtotime($_POST['periodicalaudit']));
        } else {
            $periodicaldate = NULL;
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

        $check1 = "";
        $check2 = "";
        $check3 = "";

        if (isset($_POST["check1_yes"])) {
            $check1 = 'Yes';
        }elseif (isset($_POST['check1_no'])) {
            $check1 = 'No';
        }
        if (isset($_POST["check2_yes"])) {
            $check2 = 'Yes';
        }elseif (isset($_POST['check2_no'])) {
            $check2 = 'No';
        }
        if (isset($_POST["check3_yes"])) {
            $check3 = 'Yes';
        }elseif (isset($_POST['check3_no'])) {
            $check3 = 'No';
        }

        $rev1check = "";
        $rev2check = "";

        if (isset($_POST["checkrev1_yes"])) {
            $rev1check = 'Yes';
        }elseif (isset($_POST['checkrev1_no'])) {
            $rev1check = 'No';
        }
        if (isset($_POST["checkrev2_yes"])) {
            $rev2check = 'Yes';
        }elseif (isset($_POST['checkrev2_no'])) {
            $rev2check = 'No';
        }elseif (isset($_POST['checkrev2_na'])) {
            $rev2check = 'N.A.';
        }

        $mem1Grant = "";
        if (isset($_POST["cdc-status-mem1-g"])) {
            $mem1Grant = 'Granted';
        }elseif (isset($_POST['cdc-status-mem1-n'])) {
            $mem1Grant = 'Not-Granted';
        }
        $mem2Grant = "";
        if (isset($_POST["cdc-status-mem2-g"])) {
            $mem2Grant = 'Granted';
        }elseif (isset($_POST['cdc-status-mem2-n'])) {
            $mem2Grant = 'Not-Granted';
        }
        $mem3Grant = "";
        if (isset($_POST["cdc-status-mem3-g"])) {
            $mem3Grant = 'Granted';
        }elseif (isset($_POST['cdc-status-mem3-n'])) {
            $mem3Grant = 'Not-Granted';
        }

        $rev3check = "";
        if (isset($_POST["checkrev3_yes"])) {
            $rev3check = 'Yes';
        }elseif (isset($_POST['checkrev3_no'])) {
            $rev3check = 'No';
        }

        if (isset($_POST['certificateNo']) && !empty($_POST['certificateNo'])) {
            $certNo = $_POST['certificateNo'];
        } else {
            $certNo = "";
        }

        if (isset($_POST['revCdc']) && !empty($_POST['revCdc'])) {
            $revNo = $_POST['revCdc'];
        } else {
            $revNo = "";
        }

        $servicestatus = "Completed";

        $sql = "SELECT ca_service_id FROM `cdc_report_review` where crr_id = :id ";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_POST['crr_Id']);
        $statement->execute();
        $svcid = $statement->fetch();

        $service = $svcid['ca_service_id'];

        $sql1 = "SELECT service FROM `service_type_ca` where ca_id = :id";
        $statement = $db->prepare($sql1);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $ca = $statement->fetch();

        $sqlmaxsvc = "SELECT max(service_id) as id FROM `service` WHERE customer_id = :id";
        $statement = $db->prepare($sqlmaxsvc);
        $statement->bindParam(":id", $_POST['customer_Id']);
        $statement->execute();
        $maxsvcid = $statement->fetch();

        if ($ca["service"]) {
            $caServiceStatusUpdate = "UPDATE service_type_ca SET service_ca_status = :status WHERE ca_id = :service";
            $statement = $db->prepare($caServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $ca["service"]);
            $statement->execute();

            if (($maxsvcid["id"] == $ca["service"]) && ($mem3Grant == "Granted")) {
                $customerStatus = "Active";
                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_POST['customer_Id']);
                $statement->execute();
            }
        }

        $sql2 = "SELECT service FROM `service_type_sai` where sai_id = :id";
        $statement = $db->prepare($sql2);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $sai = $statement->fetch();

        if ($sai["service"]) {
            $saiServiceStatusUpdate = "UPDATE service_type_sai SET service_sai_status = :status WHERE sai_id = :service";
            $statement = $db->prepare($saiServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $sai["service"]);
            $statement->execute();

            if (($maxsvcid["id"] == $sai["service"]) && ($mem3Grant == "Granted")) {
                $customerStatus = "Active";
                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_POST['customer_Id']);
                $statement->execute();
            }
        }

        $sql3 = "SELECT service FROM `service_type_saii` where saii_id = :id";
        $statement = $db->prepare($sql3);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $saii = $statement->fetch();

        if ($saii["service"]) {
            $saiiServiceStatusUpdate = "UPDATE service_type_saii SET service_saii_ststus = :status WHERE saii_id = :service";
            $statement = $db->prepare($saiiServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $saii["service"]);
            $statement->execute();

            if (($maxsvcid["id"] == $saii["service"]) && ($mem3Grant == "Granted")) {
                $customerStatus = "Active";
                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_POST['customer_Id']);
                $statement->execute();
            }
        }

        $sql4 = "SELECT service FROM `service_type_rc` WHERE rc_id = :id";
        $statement = $db->prepare($sql4);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $rc = $statement->fetch();

        if ($rc["service"]) {
            $rcServiceStatusUpdate = "UPDATE service_type_rc SET service_rc_status = :status WHERE rc_id = :service";
            $statement = $db->prepare($rcServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $rc["service"]);
            $statement->execute();

            if (($maxsvcid["id"] == $rc["service"]) && ($mem3Grant == "Granted")) {
                $customerStatus = "Active";
                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_POST['customer_Id']);
                $statement->execute();
            }
        }

        $sql5 = "SELECT service FROM `service_type_sp` WHERE sp_id = :id";
        $statement = $db->prepare($sql5);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $sp = $statement->fetch();

        if ($sp["service"]) {
            $rcServiceStatusUpdate = "UPDATE service_type_sp SET service_sp_status = :status WHERE sp_id = :service";
            $statement = $db->prepare($caServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $sp["service"]);
            $statement->execute();
        }

        $uniService = "SELECT service_type FROM service WHERE service_id = :id";
        $statement = $db->prepare($uniService);
        $statement->bindParam(':id', $_POST['uni_serviceid']);
        $statement->execute();
        $uniServiceType = $statement->fetch();

        if ($uniServiceType['service_type'] == "CA") {
            $sql = "UPDATE customer_info SET certification_no = :certno WHERE id = :id ";
            $statement = $db->prepare($sql);
            $statement->bindParam(":id", $_POST['customer_Id']);
            $statement->bindParam(":certno", $_POST['certificateNo']);
            $statement->execute();

            $certficateno = "SELECT certificateNo FROM `cdc_report_review` WHERE crr_id = :id";
            $statement = $db->prepare($certficateno);
            $statement->bindParam(":id", $_POST['crr_Id']);
            $statement->execute();
            $cert_no = $statement->fetch();

            if (($cert_no["certificateNo"] == "") && isset($revDate) && !empty($revDate) && isset($certNo) && !empty($certNo)) {
//                        $cert_No = $_POST['certificateNo'] + 1;
                $certno = "SELECT certification_no FROM `consulting_firm`";
                $statement = $db->prepare($certno);
                $statement->execute();
                $certiNo = $statement->fetch();

                $cert_No = $certiNo["certification_no"] + 1;

                $updateCertNo = "UPDATE consulting_firm SET certification_no = :no";
                $statement = $db->prepare($updateCertNo);
                $statement->bindParam(":no", $cert_No);
                $statement->execute();
            }
        }

        if ($_POST["servicetype"] == "CA") {
            $updateCustomer = "UPDATE customer_info SET certification_stage_date=:certification_stage_date  WHERE id = :customer";
            $statement = $db->prepare($updateCustomer);
            $statement->bindParam(':customer', $_POST['customer_Id']);
            $statement->bindParam(':certification_stage_date', $Date);
            $statement->execute();
        }

        $updateCustomer = "UPDATE customer_info SET `address` = :add, `scope` = :scope  WHERE id = :customer";
        $statement = $db->prepare($updateCustomer);
        $statement->bindParam(':customer', $_POST['customer_Id']);
        $statement->bindParam(':add', $_POST['address']);
        $statement->bindParam(':scope', $_POST['product_service']);
        $statement->execute();

        $certificationDate = "SELECT service_count, certification_stage_date FROM customer_info WHERE id = :customer";
        $statement = $db->prepare($certificationDate);
        $statement->bindParam(':customer', $_POST['customer_Id']);
        $statement->execute();
        $certifyDate = $statement->fetch();

        $service = $_POST['service_Id'];

        $sql = "UPDATE `cdc_report_review` SET `file_no`= :file_no,`audit_organization`=:audit_organization,`address`=:address,"
                . "`product_service`=:product_service,`iaf_nacf`=:iaf_nacf,`exclusion`=:exclusion,`audit_type`=:audit_type, audit_date = :audit_date,"
                . "`covered_for`=:covered_for,`accreditation`=:accreditation,`lead_auditor`=:lead_auditor,`technical_expert`=:technical_expert,`assDate`=:assDate,"
                . "`auditors`=:auditors,`reviewer_1`=:reviewer_1, reviewer_2 = :reviewer_2, granted_by = :granted_by,"
                . "`system_during`=:system_during, system_complainte = :system_complainte, rev1_yes = :yesrev1, rev2_yes = :yesrev2,`comments_if_any`=:comments_if_any,"
                . " rev3_yes = :yesrev3, review_1_granting = :mem1Grant,`yes1`=:yes1,`yes2`=:yes2,`yes3`=:yes3, review_2_granting = :rev2grant, review_2_comment = :review2comment,"
                . " cdc_status = :cdc_status,`comments`=:comments,`Date`=:Date, rc_audit_date = :rcauditdate , rc_restore_date = :rd_restoredate,"
                . "`certificateNo`=:certificateNo,`revCdc`=:revCdc,`revDatecdc`=:revDatecdc,`expiredate`=:expiredate,"
                . "`periodicalaudit`=:periodicalaudit, periodical_audit_comment = :periodicalauditcomment, next_audit_type = :nextaudittype, "
                . "ncval = :ncval, ofival = :ofival, oplusval = :oplus WHERE crr_id=:crr_Id and ca_service_id=:ca_service_id ";
        $statement = $db->prepare($sql);
        $statement->bindParam(':ca_service_id', $service);
        $statement->bindParam(':crr_Id', $_POST['crr_Id']);
        $statement->bindParam(':file_no', $_POST['file_No']);
        $statement->bindParam(':audit_organization', $_POST['audited_Organisation']);
        $statement->bindParam(':address', $_POST['address']);
        $statement->bindParam(':product_service', $_POST['product_service']);
        $statement->bindParam(':iaf_nacf', $_POST['iaf_nace']);
        $statement->bindParam(':exclusion', $_POST['exclusion']);
        $statement->bindParam(':audit_date', $_POST["audit_date_CDC"]);
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
        $statement->bindParam(':yesrev1', $rev1check);
        $statement->bindParam(':yesrev2', $rev2check);
        $statement->bindParam(':comments_if_any', $_POST['comments_if_any']);
        $statement->bindParam(':mem1Grant', $mem1Grant);
        $statement->bindParam(':yes1', $check1);
        $statement->bindParam(':yes2', $check2);
        $statement->bindParam(':yes3', $check3);
        $statement->bindParam(':rev2grant', $mem2Grant);
        $statement->bindParam(':review2comment', $_POST['comments_if_any_2nd_review']);
        $statement->bindParam(':yesrev3', $rev3check);
        $statement->bindParam(':cdc_status', $mem3Grant);
        $statement->bindParam(':comments', $_POST['comments']);
        $statement->bindParam(':Date', $Date);
        $statement->bindParam(':rcauditdate', $rc_auditdate);
        $statement->bindParam(':rd_restoredate', $rc_restoredate);
        $statement->bindParam(':certificateNo', $certNo);
        $statement->bindParam(':revCdc', $revNo);
        $statement->bindParam(':revDatecdc', $revDate);
        $statement->bindParam(':expiredate', $expiredate);
        $statement->bindParam(':periodicalaudit', $periodicaldate);
        $statement->bindParam(':periodicalauditcomment', $_POST["periodicalAuditcoment"]);
        $statement->bindParam(':nextaudittype', $_POST["nextaudittype"]);
        $statement->bindParam(':ncval', $_POST["ncval"]);
        $statement->bindParam(':ofival', $_POST["ofival"]);
        $statement->bindParam(':oplus', $_POST["oplus"]);
        $statement->execute();

        // Update service table with crr_id to mark CDC as complete
        // Check which service type and update accordingly
        error_log("DEBUG: Updating crr_id for service: " . $service);
        error_log("DEBUG: crr_Id value: " . $_POST['crr_Id']);
        error_log("DEBUG: ca service check: " . (isset($ca["service"]) ? $ca["service"] : "NOT SET"));
        
        if (isset($ca["service"]) && !empty($ca["service"])) {
            error_log("DEBUG: Updating CA service type");
            $updateCrrId = "UPDATE service_type_ca SET crr_id = :crr_id WHERE ca_id = :service";
            $statement = $db->prepare($updateCrrId);
            $statement->bindParam(':crr_id', $_POST['crr_Id']);
            $statement->bindParam(':service', $service);
            $result = $statement->execute();
            error_log("DEBUG: CA Update result: " . ($result ? "SUCCESS" : "FAILED"));
            error_log("DEBUG: Rows affected: " . $statement->rowCount());
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
                $statement->bindParam(':crr_id', $_POST['crr_Id']);
                $statement->bindParam(':service', $service);
                $statement->execute();
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
                    $statement->bindParam(':crr_id', $_POST['crr_Id']);
                    $statement->bindParam(':service', $service);
                    $statement->execute();
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
                        $statement->bindParam(':crr_id', $_POST['crr_Id']);
                        $statement->bindParam(':service', $service);
                        $statement->execute();
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
                            $statement->bindParam(':crr_id', $_POST['crr_Id']);
                            $statement->bindParam(':service', $service);
                            $statement->execute();
                        }
                    }
                }
            }
        }

        $officeUsePayload = isset($_POST['office_use']) && is_array($_POST['office_use']) ? $_POST['office_use'] : [];
        $officeUseEntries = normaliseCdcOfficePayload($officeUsePayload);
        replaceCdcOfficeUse($db, (int) $_POST['crr_Id'], $officeUseEntries);

        if ($mem3Grant == "Granted") {
            $certificate = "SELECT certificate_id FROM `certificate_print` WHERE service_id = :service_id ";
            $statement = $db->prepare($certificate);
            $statement->bindParam(':service_id', $service);
            $statement->execute();
            $certificateid = $statement->fetch();

            if ($certificateid["certificate_id"]) {
                $certficate = "UPDATE `certificate_print` SET customer_name = :clientName, customer_address = :ca_address, `certificate_no`= :certificateNo, `registration_date`= :Date, `valid_until`= :expiredate, `before_date`= :periodicalaudit, certificate_scope = :scope WHERE service_id = :ca_service_id";
                $statement = $db->prepare($certficate);
                $statement->bindParam(':clientName', $_POST['audited_Organisation']);
                $statement->bindParam(':ca_address', $_POST['address']);
                $statement->bindParam(':ca_service_id', $service);
                $statement->bindParam(':certificateNo', $certNo);
                $statement->bindParam(':Date', $Date);
                $statement->bindParam(':expiredate', $expiredate);
                $statement->bindParam(':periodicalaudit', $periodicaldate);
                $statement->bindParam(':scope', $_POST["product_service"]);
                $statement->execute();

                $sqla = "SELECT * FROM `customer_info` WHERE `id` = :customer";
                $statement = $db->prepare($sqla);
                $statement->bindParam(':customer', $_POST['customer_Id']);
                $statement->execute();
                $customer = $statement->fetch();

                $baseCertDate = '';
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

                $action1 = "Suspension Reminder";
                $action2 = "Suspend Letter";
                $action3 = "Withdrawal Reminder";
                $action4 = "Withdraw Letter";
//
                $svccoutnset = $customer["service_count"] - 1;
                $monthadd = 36 * $svccoutnset;

                // sa-1 service
                $si1 = $monthadd + 23;
                $si2 = $monthadd + 24;
                $si3 = $monthadd + 26;
                $si4 = $monthadd + 28;

                // sa-2 service
                $sii1 = $monthadd + 34;
                $sii2 = $monthadd + 35;
                $sii3 = $monthadd + 36;
                $sii4 = $monthadd + 40;

                // rc service
                $rc1 = $monthadd + 47;
                $rc2 = $monthadd + 48;
                $rc3 = $monthadd + 50;
                $rc4 = $monthadd + 52;

                if ($uniServiceType['service_type'] != "SP") {
                    if ($uniServiceType['service_type'] == "CA") {
                        $ca1 = $monthadd + 11;
                        $ca2 = $monthadd + 12;
                        $ca3 = $monthadd + 14;
                        $ca4 = $monthadd + 16;

                        $date1 = $baseCertDate . '+ ' . $ca1 . ' MONTH';
                        //$newdate11 = date('Y-m-d', strtotime($date1));
                        $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                        $newdate1 = date('Y-m-d', strtotime($date1));

                        $date2 = $baseCertDate . '+ ' . $ca2 . ' MONTH';
                        //$newdate22 = date('Y-m-d', strtotime($date2));
                        $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                        $newdate2 = date('Y-m-d', strtotime($date2));

                        $date3 = $baseCertDate . '+ ' . $ca3 . ' MONTH';
                        //$newdate33 = date('Y-m-d', strtotime($date3));
                        $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                        $newdate3 = date('Y-m-d', strtotime($date3));

                        $date4 = $baseCertDate . '+ ' . $ca4 . ' MONTH';
                        //$newdate44 = date('Y-m-d', strtotime($date4));
                        $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                        $newdate4 = date('Y-m-d', strtotime($date4));

//                $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)"; 
                        $auditServeliance1 = "UPDATE ca_service_dates SET due_date = :dueDate1, action = :action1 WHERE service_id = :serviceid and (action = '1st Reminder' or action = 'Suspension Reminder')";
                        $statement = $db->prepare($auditServeliance1);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action1', $action1);
                        $statement->bindParam(':dueDate1', $newdate1);
                        $statement->execute();

//                $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                        $auditServeliance2 = "UPDATE ca_service_dates SET due_date = :dueDate2, action = :action2 WHERE service_id = :serviceid and (action = '2nd Reminder' or action = 'Suspend Letter')";
                        $statement = $db->prepare($auditServeliance2);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action2', $action2);
                        $statement->bindParam(':dueDate2', $newdate2);
                        $statement->execute();

//                $auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate3)";
                        $auditServeliance3 = "UPDATE ca_service_dates SET due_date = :dueDate3, action = :action3 WHERE service_id = :serviceid  and (action = 'Withdrawal Reminder' or action = 'Suspension Letter')";
                        $statement = $db->prepare($auditServeliance3);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action3', $action3);
                        $statement->bindParam(':dueDate3', $newdate3);
                        $statement->execute();

//                $auditServeliance4 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action4, :dueDate4)";
                        $auditServeliance4 = "UPDATE ca_service_dates SET due_date = :dueDate4, action = :action4 WHERE service_id = :serviceid and (action = 'Withdraw Letter' or action = 'Withdrawal Letter')";
                        $statement = $db->prepare($auditServeliance4);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action4', $action4);
                        $statement->bindParam(':dueDate4', $newdate4);
                        $statement->execute();
                    } 
                    else if ($uniServiceType['service_type'] == "SA-I") {

                        $date1 = $baseCertDate . '+ ' . $si1 . ' MONTH';
                        //$newdate11 = date('Y-m-d', strtotime($date1));
                        $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                        $newdate1 = date('Y-m-d', strtotime($date1));

                        $date2 = $baseCertDate . '+ ' . $si2 . ' MONTH';
                        //$newdate22 = date('Y-m-d', strtotime($date2));
                        $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                        $newdate2 = date('Y-m-d', strtotime($date2));

                        $date3 = $baseCertDate . '+ ' . $si3 . ' MONTH';
                        //$newdate33 = date('Y-m-d', strtotime($date3));
                        $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                        $newdate3 = date('Y-m-d', strtotime($date3));

                        $date4 = $baseCertDate . '+ ' . $si4 . ' MONTH';
                        //$newdate44 = date('Y-m-d', strtotime($date4));
                        $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                        $newdate4 = date('Y-m-d', strtotime($date4));
//                $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)";
                        $auditServeliance1 = "UPDATE ca_service_dates SET due_date = :dueDate1, action = 'Suspension Reminder' WHERE service_id = :serviceid and (action = '1st Reminder' or action = 'Suspension Reminder')";
                        $statement = $db->prepare($auditServeliance1);
                        $statement->bindParam(':serviceid', $serviceid);
//                $statement->bindParam(':action1', $action1);
                        $statement->bindParam(':dueDate1', $newdate1);
                        $statement->execute();

//                $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                        $auditServeliance2 = "UPDATE ca_service_dates SET due_date = :dueDate2, action = :action2 WHERE service_id = :serviceid and (action = '2nd Reminder' or action = 'Suspend Letter')";
                        $statement = $db->prepare($auditServeliance2);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action2', $action2);
                        $statement->bindParam(':dueDate2', $newdate2);
                        $statement->execute();

//                $auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate3)";
                        $auditServeliance3 = "UPDATE ca_service_dates SET due_date = :dueDate3, action = :action3 WHERE service_id = :serviceid and (action = 'Suspension Letter' or action = 'Withdrawal Reminder')";
                        $statement = $db->prepare($auditServeliance3);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action3', $action3);
                        $statement->bindParam(':dueDate3', $newdate3);
                        $statement->execute();

//                $auditServeliance4 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action4, :dueDate4)";
                        $auditServeliance4 = "UPDATE ca_service_dates SET due_date = :dueDate4, action = :action4 WHERE service_id = :serviceid and (action = 'Withdraw Letter' or action = 'Withdrawal Letter')";
                        $statement = $db->prepare($auditServeliance4);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action4', $action4);
                        $statement->bindParam(':dueDate4', $newdate4);
                        $statement->execute();
                    } 
                    else if ($uniServiceType['service_type'] == "SA-II") {  // 1st update of CDC to update SA-II
                        $action1 = "1st Reminder";
                        $action2 = "2nd Reminder";
                        $action3 = "Expire Letter";
                        $date1 = $baseCertDate . '+ ' . $sii1 . ' MONTH';
                        //$newdate11 = date('Y-m-d', strtotime($date1));
                        $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                        $newdate1 = date('Y-m-d', strtotime($date1));

                        $date2 = $baseCertDate . '+ ' . $sii2 . ' MONTH';
                        //$newdate22 = date('Y-m-d', strtotime($date2));
                        $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                        $newdate2 = date('Y-m-d', strtotime($date2));

                        $date3 = $baseCertDate . '+ ' . $sii3 . ' MONTH';
                        //$newdate33 = date('Y-m-d', strtotime($date3));
                        $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                        $newdate3 = date('Y-m-d', strtotime($date3));

                        $date4 = $baseCertDate . '+ ' . $sii4 . ' MONTH';
                        //$newdate44 = date('Y-m-d', strtotime($date4));
                        $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                        $newdate4 = date('Y-m-d', strtotime($date4));

//                $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)";
                        $auditServeliance1 = "UPDATE ca_service_dates SET due_date = :dueDate1, action = :action1 WHERE service_id = :serviceid and action = :action1";
                        $statement = $db->prepare($auditServeliance1);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action1', $action1);
                        $statement->bindParam(':dueDate1', $newdate1);
                        $statement->execute();

//                $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                        $auditServeliance2 = "UPDATE ca_service_dates SET due_date = :dueDate2, action = :action2 WHERE service_id = :serviceid and action = :action2";
                        $statement = $db->prepare($auditServeliance2);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action2', $action2);
                        $statement->bindParam(':dueDate2', $newdate2);
                        $statement->execute();

//                        $withdrawRemind = "SELECT count(action_id) as count FROM `ca_service_dates` where service_id = :serviceid and action = 'Expire Reminder'";
//                        $statement = $db->prepare($withdrawRemind);
//                        $statement->bindParam(':serviceid', $serviceid);
//                        $statement->execute();
//                        $withdrawCount = $statement->fetch();
//                $auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate3)";
                        $auditServeliance4 = "UPDATE ca_service_dates SET due_date = :dueDate4 WHERE service_id = :serviceid and action = :action3";
                        $statement = $db->prepare($auditServeliance4);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action3', $action3);
                        $statement->bindParam(':dueDate4', $newdate3);
                        $statement->execute();
                    } 
                    else if ($uniServiceType['service_type'] == "RC") {

                        $date1 = $baseCertDate . '+ ' . $rc1 . ' MONTH';
                        //$newdate11 = date('Y-m-d', strtotime($date1));
                        $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                        $newdate1 = date('Y-m-d', strtotime($date1));

                        $date2 = $baseCertDate . '+ ' . $rc2 . ' MONTH';
                        //$newdate22 = date('Y-m-d', strtotime($date2));
                        $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                        $newdate2 = date('Y-m-d', strtotime($date2));

                        $date3 = $baseCertDate . '+ ' . $rc3 . ' MONTH';
                        //$newdate33 = date('Y-m-d', strtotime($date3));
                        $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                        $newdate3 = date('Y-m-d', strtotime($date3));

                        $date4 = $baseCertDate . '+ ' . $rc4 . ' MONTH';
                        //$newdate44 = date('Y-m-d', strtotime($date4));
                        $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                        $newdate4 = date('Y-m-d', strtotime($date4));

//                $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)";
                        $auditServeliance1 = "UPDATE ca_service_dates SET due_date = :dueDate1, action = :action1 WHERE service_id = :serviceid and (action = '1st Reminder' or action = 'Suspension Reminder')";
                        $statement = $db->prepare($auditServeliance1);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action1', $action1);
                        $statement->bindParam(':dueDate1', $newdate1);
                        $statement->execute();

//                $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                        $auditServeliance2 = "UPDATE ca_service_dates SET due_date = :dueDate2, action = :action2 WHERE service_id = :serviceid and (action = '2nd Reminder' or action = 'Suspend Letter')";
                        $statement = $db->prepare($auditServeliance2);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action2', $action2);
                        $statement->bindParam(':dueDate2', $newdate2);
                        $statement->execute();

//                $auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate3)";
                        $auditServeliance3 = "UPDATE ca_service_dates SET due_date = :dueDate3, action = :action3 WHERE service_id = :serviceid and (action = 'Suspension Letter' or action = 'Withdrawal Reminder')";
                        $statement = $db->prepare($auditServeliance3);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action3', $action3);
                        $statement->bindParam(':dueDate3', $newdate3);
                        $statement->execute();

//                $auditServeliance4 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action4, :dueDate4)";
                        $auditServeliance4 = "UPDATE ca_service_dates SET due_date = :dueDate4, action = :action4 WHERE service_id = :serviceid and (action = 'Withdraw Letter' or action = 'Withdrawal Letter')";
                        $statement = $db->prepare($auditServeliance4);
                        $statement->bindParam(':serviceid', $serviceid);
                $statement->bindParam(':action4', $action4);
                        $statement->bindParam(':dueDate4', $newdate4);
                        $statement->execute();
                    }
                }
            } else {
                $service = "SELECT * FROM service WHERE service_id = :id";
                $statement = $db->prepare($service);
                $statement->bindParam(':id', $_POST['uni_serviceid']);
                $statement->execute();
                $serviceInfo = $statement->fetch();

                $certPrint = "INSERT INTO certificate_print (customer_id, service_id, customer_name, customer_address, certificate_no, certificate_scope, registration_date, valid_until, before_date) "
                        . "VALUES (:customer,:service,:customerName,:customerAddress,:certificationNo, :certifyScope, :certifyRegistrationDate,:certifyValidTill,:certifyBeforeDate)";
                $statement = $db->prepare($certPrint);
                $statement->bindParam(':customer', $_POST['customer_Id']);
                $statement->bindParam(':service', $_POST['service_Id']);
                $statement->bindParam(':customerName', $_POST['audited_Organisation']);
                $statement->bindParam(':customerAddress', $_POST['address']);
                $statement->bindParam(':certificationNo', $certNo);
                $statement->bindParam(':certifyScope', $_POST['product_service']);
                $statement->bindParam(':certifyRegistrationDate', $Date);
                $statement->bindParam(':certifyValidTill', $expiredate);
                $statement->bindParam(':certifyBeforeDate', $periodicaldate);
                $statement->execute();

                if ($serviceInfo['service_type'] == "SP") {
                    $updateService = "UPDATE service SET service_done = 'DONE' WHERE service_id = :id";
                    $statement = $db->prepare($updateService);
                    $statement->bindParam(':id', $_POST['uni_serviceid']);
                    $statement->execute();
                }

                $sqla = "SELECT * FROM `customer_info` WHERE `id` = :customer";
                $statement = $db->prepare($sqla);
                $statement->bindParam(':customer', $_POST['customer_Id']);
                $statement->execute();
                $customer = $statement->fetch();

                $baseCertDate = '';
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

                $sqlc = "SELECT count(certificate_no) as cert FROM `clientlist` where certificate_no = :customer";
                $statement = $db->prepare($sqlc);
                $statement->bindParam(':customer', $_POST['certificateNo']);
                $statement->execute();
                $customercount = $statement->fetch();

                if ($customercount["cert"] < 1) {
                    $sqlb = "INSERT INTO `clientlist`(`certificate_no`, `client_name`, `client_address`, `scope`, `validity_status`) "
                            . "VALUES (:certificateNo, :customer, :address, :scope, :validity)";
                    $statement = $db->prepare($sqlb);
                    $statement->bindParam(':certificateNo', $certNo);
                    $statement->bindParam(':customer', $customer['customer_name']);
                    $statement->bindParam(':address', $customer['address']);
                    $statement->bindParam(':scope', $customer['scope']);
                    $statement->bindParam(':validity', $customer['audit_status']);
                    $statement->execute();
                }

                $action1 = "Suspension Reminder";
                $action2 = "Suspend Letter";
                $action3 = "Withdrawal Reminder";
                $action4 = "Withdraw Letter";

                $svccoutnset = $customer["service_count"] - 1;
                $monthadd = 36 * $svccoutnset;
                // sa-1 service
                $si1 = $monthadd + 23;
                $si2 = $monthadd + 24;
                $si3 = $monthadd + 26;
                $si4 = $monthadd + 28;

                // sa-2 service
                $sii1 = $monthadd + 34;
                $sii2 = $monthadd + 35;
                $sii3 = $monthadd + 36;
                $sii4 = $monthadd + 40;

                // rc service
                $rc1 = $monthadd + 47;
                $rc2 = $monthadd + 48;
                $rc3 = $monthadd + 50;
                $rc4 = $monthadd + 52;

                if ($uniServiceType['service_type'] != "SP") {
                    if ($uniServiceType['service_type'] == "CA") {
                        $date1 = $certifyDate['certification_stage_date'] . '+ 11 MONTH';
                        //$newdate11 = date('Y-m-d', strtotime($date1));
                        $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                        $newdate1 = date('Y-m-d', strtotime($date1));

                        $date2 = $certifyDate['certification_stage_date'] . '+ 12 MONTH';
                        //$newdate22 = date('Y-m-d', strtotime($date2));
                        $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                        $newdate2 = date('Y-m-d', strtotime($date2));

                        $date3 = $certifyDate['certification_stage_date'] . '+ 14 MONTH';
                        //$newdate33 = date('Y-m-d', strtotime($date3));
                        $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                        $newdate3 = date('Y-m-d', strtotime($date3));

                        $date4 = $certifyDate['certification_stage_date'] . '+ 16 MONTH';
                        //$newdate44 = date('Y-m-d', strtotime($date4));
                        $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                        $newdate4 = date('Y-m-d', strtotime($date4));

                        $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)";
                        $statement = $db->prepare($auditServeliance1);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action1', $action1);
                        $statement->bindParam(':dueDate1', $newdate1);
                        $statement->execute();

                        $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                        $statement = $db->prepare($auditServeliance2);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action2', $action2);
                        $statement->bindParam(':dueDate2', $newdate2);
                        $statement->execute();

                        $auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate3)";
                        $statement = $db->prepare($auditServeliance3);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action3', $action3);
                        $statement->bindParam(':dueDate3', $newdate3);
                        $statement->execute();

                        $auditServeliance4 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action4, :dueDate4)";
                        $statement = $db->prepare($auditServeliance4);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action4', $action4);
                        $statement->bindParam(':dueDate4', $newdate4);
                        $statement->execute();
                    } 
                    else if ($uniServiceType['service_type'] == "SA-I") {
                        $date1 = $baseCertDate . '+ ' . $si1 . ' MONTH';
                        //$newdate11 = date('Y-m-d', strtotime($date1));
                        $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                        $newdate1 = date('Y-m-d', strtotime($date1));

                        $date2 = $baseCertDate . '+ ' . $si2 . ' MONTH';
                        //$newdate22 = date('Y-m-d', strtotime($date2));
                        $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                        $newdate2 = date('Y-m-d', strtotime($date2));

                        $date3 = $baseCertDate . '+ ' . $si3 . ' MONTH';
                        //$newdate33 = date('Y-m-d', strtotime($date3));
                        $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                        $newdate3 = date('Y-m-d', strtotime($date3));

                        $date4 = $baseCertDate . '+ ' . $si4 . ' MONTH';
                        //$newdate44 = date('Y-m-d', strtotime($date4));
                        $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                        $newdate4 = date('Y-m-d', strtotime($date4));
                        $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)";
                        $statement = $db->prepare($auditServeliance1);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action1', $action1);
                        $statement->bindParam(':dueDate1', $newdate1);
                        $statement->execute();

                        $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                        $statement = $db->prepare($auditServeliance2);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action2', $action2);
                        $statement->bindParam(':dueDate2', $newdate2);
                        $statement->execute();

                        $auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate3)";
                        $statement = $db->prepare($auditServeliance3);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action3', $action3);
                        $statement->bindParam(':dueDate3', $newdate3);
                        $statement->execute();

                        $auditServeliance4 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action4, :dueDate4)";
                        $statement = $db->prepare($auditServeliance4);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action4', $action4);
                        $statement->bindParam(':dueDate4', $newdate4);
                        $statement->execute();
                    } 
                    else if ($uniServiceType['service_type'] == "SA-II") {    // Update SA-II
                        $action1 = "1st Reminder";
                        $action2 = "2nd Reminder";
                        $action3 = "Expire Letter";

                        $date1 = $certifyDate['certification_stage_date'] . '+ ' . $sii1 . ' MONTH';
                        //$newdate11 = date('Y-m-d', strtotime($date1));
                        $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                        $newdate1 = date('Y-m-d', strtotime($date1));

                        $date2 = $certifyDate['certification_stage_date'] . '+ ' . $sii2 . ' MONTH';
                        //$newdate22 = date('Y-m-d', strtotime($date2));
                        $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                        $newdate2 = date('Y-m-d', strtotime($date2));

                        $date3 = $certifyDate['certification_stage_date'] . '+ ' . $sii3 . ' MONTH';
                        //$newdate33 = date('Y-m-d', strtotime($date3));
                        $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                        $newdate3 = date('Y-m-d', strtotime($date3));

                        $date4 = $certifyDate['certification_stage_date'] . '+ ' . $sii4 . ' MONTH';
                        //$newdate44 = date('Y-m-d', strtotime($date4));
                        $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                        $newdate4 = date('Y-m-d', strtotime($date4));

                        $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)";
                        $statement = $db->prepare($auditServeliance1);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action1', $action1);
                        $statement->bindParam(':dueDate1', $newdate1);
                        $statement->execute();

                        $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                        $statement = $db->prepare($auditServeliance2);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action2', $action2);
                        $statement->bindParam(':dueDate2', $newdate2);
                        $statement->execute();

                        $auditServeliance4 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate4)";
                        $statement = $db->prepare($auditServeliance4);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action3', $action3);
                        $statement->bindParam(':dueDate4', $newdate3);
                        $statement->execute();
                    } 
                    else if ($uniServiceType['service_type'] == "RC") {
                        $date1 = $certifyDate['certification_stage_date'] . '+ ' . $rc1 . ' MONTH';
                        //$newdate11 = date('Y-m-d', strtotime($date1));
                        $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                        $newdate1 = date('Y-m-d', strtotime($date1));

                        $date2 = $certifyDate['certification_stage_date'] . '+ ' . $rc2 . ' MONTH';
                        //$newdate22 = date('Y-m-d', strtotime($date2));
                        $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                        $newdate2 = date('Y-m-d', strtotime($date2));

                        $date3 = $certifyDate['certification_stage_date'] . '+ ' . $rc3 . ' MONTH';
                        //$newdate33 = date('Y-m-d', strtotime($date3));
                        $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                        $newdate3 = date('Y-m-d', strtotime($date3));

                        $date4 = $certifyDate['certification_stage_date'] . '+ ' . $rc4 . ' MONTH';
                        //$newdate44 = date('Y-m-d', strtotime($date4));
                        $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                        $newdate4 = date('Y-m-d', strtotime($date4));

                        $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)";
                        $statement = $db->prepare($auditServeliance1);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action1', $action1);
                        $statement->bindParam(':dueDate1', $newdate1);
                        $statement->execute();

                        $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                        $statement = $db->prepare($auditServeliance2);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action2', $action2);
                        $statement->bindParam(':dueDate2', $newdate2);
                        $statement->execute();

                        $auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate3)";
                        $statement = $db->prepare($auditServeliance3);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action3', $action3);
                        $statement->bindParam(':dueDate3', $newdate3);
                        $statement->execute();

                        $auditServeliance4 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action4, :dueDate4)";
                        $statement = $db->prepare($auditServeliance4);
                        $statement->bindParam(':serviceid', $serviceid);
                        $statement->bindParam(':action4', $action4);
                        $statement->bindParam(':dueDate4', $newdate4);
                        $statement->execute();
                    }
                    if ($uniServiceType['service_type'] == "CA") {
                        $sql = "UPDATE customer_info SET `customer_name`= :name, `certification_no`= :certno, `address`= :address, `scope`= :scope, `aif_nace`= :iafnace WHERE id = :id ";
                        $statement = $db->prepare($sql);
                        $statement->bindParam(":id", $_POST['customer_Id']);
                        $statement->bindParam(":name", $_POST['audited_Organisation']);
                        $statement->bindParam(":certno", $certNo);
                        $statement->bindParam(":address", $_POST['address']);
                        $statement->bindParam(":scope", $_POST['product_service']);
                        $statement->bindParam(":iafnace", $_POST['iaf_nace']);
                        $statement->execute();

                        if (isset($revDate) && !empty($revDate) && isset($certNo) && !empty($certNo)) {
                            $certno = "SELECT certification_no FROM `consulting_firm`";
                            $statement = $db->prepare($certno);
                            $statement->execute();
                            $certiNo = $statement->fetch();

                            $certNo = $certiNo["certification_no"] + 1;

                            $updateCertNo = "UPDATE consulting_firm SET certification_no = :no";
                            $statement = $db->prepare($updateCertNo);
                            $statement->bindParam(":no", $certNo);
                            $statement->execute();
                        }
                    }
                }
            }
        }

        $serviceid = $_POST['uni_serviceid'];
        if (!empty($serviceid)) {
            aitfSyncClientStatusDates($db, $serviceid, $_POST['customer_Id'], isset($Date) ? $Date : '');
        }
        aitfSyncExistingClientStatusDatesForCustomer($db, $_POST['customer_Id'], isset($Date) ? $Date : '');
//        echo "in";
        header("Location: customerAccount.php?customerid=" . $_POST['customer_Id']);
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_POST['service_Id']) && !empty($_POST['service_Id'])) {
        try {
            $serviceid = $_POST['uni_serviceid'];
            error_log("=== CDC SAVE BLOCK ENTERED ===");
            error_log("service_Id: " . $_POST['service_Id']);
            error_log("save_cdc isset: " . (isset($_POST['save_cdc']) ? 'YES' : 'NO'));
            if (isset($_POST['save_cdc'])) {
                error_log("=== INSIDE SAVE_CDC BLOCK ===");

                // Check for audit date field - prioritize audit_date_CDC over audit_dateII
                if (isset($_POST['audit_date_CDC']) && !empty($_POST['audit_date_CDC'])) {
                    $audit_dateII = date("Y-m-d", strtotime($_POST['audit_date_CDC']));
                } elseif (isset($_POST['audit_dateII']) && !empty($_POST['audit_dateII'])) {
                    $audit_dateII = date("Y-m-d", strtotime($_POST['audit_dateII']));
                } else {
                    $audit_dateII = NULL;
                }

                if (isset($_POST["audit_performance"]) && !empty($_POST["audit_performance"])) {
                    $audit_performance = date("Y-m-d", strtotime($_POST['audit_performance']));
                } else {
                    $audit_performance = NULL;
                }

                if (isset($_POST["review-date-1"]) && !empty($_POST["review-date-1"])) {
                    $review1Date = date("Y-m-d", strtotime($_POST['review-date-1']));
                } else {
                    $review1Date = NULL;
                }

                if (isset($_POST['Date']) && !empty($_POST['Date'])) {
                    $Date = date("Y-m-d", strtotime($_POST['Date']));
                } else {
                    $Date = NULL;
                }

                if (isset($_POST['date-restore1']) && !empty($_POST['date-restore1'])) {
                    $rc_auditdate = date("Y-m-d", strtotime($_POST['date-restore1']));
                } else {
                    $rc_auditdate = NULL;
                }

                if (isset($_POST['date-restore2']) && !empty($_POST['date-restore2'])) {
                    $rc_restoredate = date("Y-m-d", strtotime($_POST['date-restore2']));
                } else {
                    $rc_restoredate = NULL;
                }

                if (isset($_POST['revDatecdc']) && !empty($_POST['revDatecdc'])) {
                    $revDate = date("Y-m-d", strtotime($_POST['revDatecdc']));
                } else {
                    $revDate = NULL;
                }

                if (isset($_POST['expiredate']) && !empty($_POST['expiredate'])) {
                    $expiredate = date("Y-m-d", strtotime($_POST['expiredate']));
                } else {
                    $expiredate = NULL;
                }
                        if (isset($_POST['assDate']) && !empty($_POST['assDate'])) {
            $assDate = date("Y-m-d", strtotime($_POST['assDate']));
        } else if (!empty($Date) && $Date !== 'null') {
            $assDate = $Date;
        } else {
            $assDate = date('Y-m-d');
        }

                if (isset($_POST['periodicalaudit']) && !empty($_POST['periodicalaudit'])) {
                    $periodicaldate = date("Y-m-d", strtotime($_POST['periodicalaudit']));
                } else {
                    $periodicaldate = NULL;
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

                $check1 = "";
        $check2 = "";
        $check3 = "";

        if (isset($_POST["check1_yes"])) {
            $check1 = 'Yes';
        }elseif (isset($_POST['check1_no'])) {
            $check1 = 'No';
        }
        if (isset($_POST["check2_yes"])) {
            $check2 = 'Yes';
        }elseif (isset($_POST['check2_no'])) {
            $check2 = 'No';
        }
        if (isset($_POST["check3_yes"])) {
            $check3 = 'Yes';
        }elseif (isset($_POST['check3_no'])) {
            $check3 = 'No';
        }

        $rev1check = "";
        $rev2check = "";

        if (isset($_POST["checkrev1_yes"])) {
            $rev1check = 'Yes';
        }elseif (isset($_POST['checkrev1_no'])) {
            $rev1check = 'No';
        }
        if (isset($_POST["checkrev2_yes"])) {
            $rev2check = 'Yes';
        }elseif (isset($_POST['checkrev2_no'])) {
            $rev2check = 'No';
        }elseif (isset($_POST['checkrev2_na'])) {
            $rev2check = 'N.A.';
        }

        $mem1Grant = "";
        if (isset($_POST["cdc-status-mem1-g"])) {
            $mem1Grant = 'Granted';
        }elseif (isset($_POST['cdc-status-mem1-n'])) {
            $mem1Grant = 'Not-Granted';
        }
        $mem2Grant = "";
        if (isset($_POST["cdc-status-mem2-g"])) {
            $mem2Grant = 'Granted';
        }elseif (isset($_POST['cdc-status-mem2-n'])) {
            $mem2Grant = 'Not-Granted';
        }
        $mem3Grant = "";
        if (isset($_POST["cdc-status-mem3-g"])) {
            $mem3Grant = 'Granted';
        }elseif (isset($_POST['cdc-status-mem3-n'])) {
            $mem3Grant = 'Not-Granted';
        }

        $rev3check = "";
        if (isset($_POST["checkrev3_yes"])) {
            $rev3check = 'Yes';
        }elseif (isset($_POST['checkrev3_no'])) {
            $rev3check = 'No';
        }

                if (isset($_POST['certificateNo']) && !empty($_POST['certificateNo'])) {
                    $certNo = $_POST['certificateNo'];
                } else {
                    $certNo = "";
                }

                if (isset($_POST['revCdc']) && !empty($_POST['revCdc'])) {
                    $revNo = $_POST['revCdc'];
                } else {
                    $revNo = "";
                }

                $servicestatus = "Completed";
                $mem3Status = $mem3Grant;
                if ($mem3Status === "Granted") {
                    $customerStatus = "Active";
                } else {
                    $customerStatus = "Withdrawn";
                }
                $service = $_POST['service_Id'];

                $sql1 = "SELECT service FROM `service_type_ca` where ca_id = :id";
                $statement = $db->prepare($sql1);
                $statement->bindParam(":id", $service);
                $statement->execute();
                $ca = $statement->fetch();

                if ($ca && isset($ca["service"])) {
                    $caServiceStatusUpdate = "UPDATE service_type_ca SET service_ca_status = :status WHERE ca_id = :service";
                    $statement = $db->prepare($caServiceStatusUpdate);
                    $statement->bindParam(':status', $servicestatus);
                    $statement->bindParam(':service', $service);
                    $statement->execute();

                    $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
                    $statement = $db->prepare($updateService);
                    $statement->bindParam(':status', $servicestatus);
                    $statement->bindParam(':service', $ca["service"]);
                    $statement->execute();

                    $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status, `address` = :add, `scope` = :scope where id = :customer";
                    $statement = $db->prepare($updateCustomerStatus);
                    $statement->bindParam(':status', $customerStatus);
                    $statement->bindParam(':add', $_POST['address']);
                    $statement->bindParam(':scope', $_POST['product_service']);
                    $statement->bindParam(':customer', $_POST['customer_Id']);
                    $statement->execute();
                }

                $sql2 = "SELECT service FROM `service_type_sai` where sai_id = :id";
                $statement = $db->prepare($sql2);
                $statement->bindParam(":id", $service);
                $statement->execute();
                $sai = $statement->fetch();

                if ($sai && isset($sai["service"])) {
                    $saiServiceStatusUpdate = "UPDATE service_type_sai SET service_sai_status = :status WHERE sai_id = :service";
                    $statement = $db->prepare($saiServiceStatusUpdate);
                    $statement->bindParam(':status', $servicestatus);
                    $statement->bindParam(':service', $service);
                    $statement->execute();

                    $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
                    $statement = $db->prepare($updateService);
                    $statement->bindParam(':status', $servicestatus);
                    $statement->bindParam(':service', $sai["service"]);
                    $statement->execute();

                    $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status, `address` = :add, `scope` = :scope where id = :customer";
                    $statement = $db->prepare($updateCustomerStatus);
                    $statement->bindParam(':status', $customerStatus);
                    $statement->bindParam(':add', $_POST['address']);
                    $statement->bindParam(':scope', $_POST['product_service']);
                    $statement->bindParam(':customer', $_POST['customer_Id']);
                    $statement->execute();
                }

                $sql3 = "SELECT service FROM `service_type_saii` where saii_id = :id";
                $statement = $db->prepare($sql3);
                $statement->bindParam(":id", $service);
                $statement->execute();
                $saii = $statement->fetch();

                if ($saii && isset($saii["service"])) {
                    $saiiServiceStatusUpdate = "UPDATE service_type_saii SET service_saii_ststus = :status WHERE saii_id = :service";
                    $statement = $db->prepare($saiiServiceStatusUpdate);
                    $statement->bindParam(':status', $servicestatus);
                    $statement->bindParam(':service', $service);
                    $statement->execute();

                    $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
                    $statement = $db->prepare($updateService);
                    $statement->bindParam(':status', $servicestatus);
                    $statement->bindParam(':service', $saii["service"]);
                    $statement->execute();

                    $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status, `address` = :add, `scope` = :scope where id = :customer";
                    $statement = $db->prepare($updateCustomerStatus);
                    $statement->bindParam(':status', $customerStatus);
                    $statement->bindParam(':add', $_POST['address']);
                    $statement->bindParam(':scope', $_POST['product_service']);
                    $statement->bindParam(':customer', $_POST['customer_Id']);
                    $statement->execute();
                }

                $sql4 = "SELECT service FROM `service_type_rc` WHERE rc_id = :id";
                $statement = $db->prepare($sql4);
                $statement->bindParam(":id", $service);
                $statement->execute();
                $rc = $statement->fetch();

                if ($rc && isset($rc["service"])) {
                    $rcServiceStatusUpdate = "UPDATE service_type_rc SET service_rc_status = :status WHERE rc_id = :service";
                    $statement = $db->prepare($rcServiceStatusUpdate);
                    $statement->bindParam(':status', $servicestatus);
                    $statement->bindParam(':service', $service);
                    $statement->execute();

                    $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
                    $statement = $db->prepare($updateService);
                    $statement->bindParam(':status', $servicestatus);
                    $statement->bindParam(':service', $rc["service"]);
                    $statement->execute();

                    $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status, `address` = :add, `scope` = :scope where id = :customer";
                    $statement = $db->prepare($updateCustomerStatus);
                    $statement->bindParam(':status', $customerStatus);
                    $statement->bindParam(':add', $_POST['address']);
                    $statement->bindParam(':scope', $_POST['product_service']);
                    $statement->bindParam(':customer', $_POST['customer_Id']);
                    $statement->execute();
                }

                $sql5 = "SELECT service FROM `service_type_sp` WHERE sp_id = :id";
                $statement = $db->prepare($sql5);
                $statement->bindParam(":id", $service);
                $statement->execute();
                $sp = $statement->fetch();

                if ($sp && isset($sp["service"])) {
                    $rcServiceStatusUpdate = "UPDATE service_type_sp SET service_sp_status = :status WHERE sp_id = :service";
                    $statement = $db->prepare($rcServiceStatusUpdate);
                    $statement->bindParam(':status', $servicestatus);
                    $statement->bindParam(':service', $service);
                    $statement->execute();

                    $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
                    $statement = $db->prepare($updateService);
                    $statement->bindParam(':status', $servicestatus);
                    $statement->bindParam(':service', $sp["service"]);
                    $statement->execute();

                    $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status, `address` = :add, `scope` = :scope where id = :customer";
                    $statement = $db->prepare($updateCustomerStatus);
                    $statement->bindParam(':status', $customerStatus);
                    $statement->bindParam(':add', $_POST['address']);
                    $statement->bindParam(':scope', $_POST['product_service']);
                    $statement->bindParam(':customer', $_POST['customer_Id']);
                    $statement->execute();
                }

                $uniService = "SELECT service_type FROM service WHERE service_id = :id";
                $statement = $db->prepare($uniService);
                $statement->bindParam(':id', $_POST['uni_serviceid']);
                $statement->execute();
                $uniServiceType = $statement->fetch();

                if ($_POST["servicetype"] == "CA") {
                    $updateCustomer = "UPDATE customer_info SET certification_stage_date=:certification_stage_date  WHERE id = :customer";
                    $statement = $db->prepare($updateCustomer);
                    $statement->bindParam(':customer', $_POST['customer_Id']);
                    $statement->bindParam(':certification_stage_date', $Date);
                    $statement->execute();
                }

                $certificationDate = "SELECT certification_stage_date FROM customer_info WHERE id = :customer";
                $statement = $db->prepare($certificationDate);
                $statement->bindParam(':customer', $_POST['customer_Id']);
                $statement->execute();
                $certifyDate = $statement->fetch();

                $sql = "INSERT INTO cdc_report_review (ca_service_id, customer_id, file_no, audit_organization, address, product_service,"
                        . " iaf_nacf, exclusion, audit_type, audit_date, covered_for, accreditation, lead_auditor, technical_expert,assDate, auditors, reviewer_1,"
                        . " reviewer_2, granted_by, system_during, system_complainte, rev1_yes, rev2_yes, comments_if_any, review_1_granting, yes1, yes2, yes3, "
                        . "review_2_comment, review_2_granting, rev3_yes, cdc_status, comments, Date, rc_audit_date, rc_restore_date, certificateNo, revCdc, revDatecdc, expiredate, periodicalaudit, "
                        . "periodical_audit_comment, next_audit_type, ncval, ofival, oplusval) "
                        . "VALUES (:ca_service_id,:customer_id,:file_no,:audit_organization,:address,:product_service,:iaf_nacf,:exclusion,"
                        . ":audit_type,:audit_date, :covered_for,:accreditation,:lead_auditor,:technical_expert,:assDate,:auditors,:reviewer_1, :reviewer_2, "
                        . ":granted_by, :system_during, :system_complainte,:yesrev1,:yesrev2, :comments_if_any,:mem1Grant, :yes1,:yes2,:yes3,"
                        . ":review2comment,:rev2grant,:yesrev3, :cdc_status, :comments,:Date,:rcauditdate, :rd_restoredate, :certificateNo,:revCdc,:revDatecdc, :expiredate,"
                        . ":periodicalaudit,:periodicalauditcomment, :nextaudittype, :ncval, :ofival, :oplus)";
                $statement = $db->prepare($sql);
                $statement->bindParam(':ca_service_id', $service);
                $statement->bindParam(':customer_id', $_POST['customer_Id']);
                $statement->bindParam(':file_no', $_POST['file_No']);
                $statement->bindParam(':audit_organization', $_POST['audited_Organisation']);
                $statement->bindParam(':address', $_POST['address']);
                $statement->bindParam(':product_service', $_POST['product_service']);
                $statement->bindParam(':iaf_nacf', $_POST['iaf_nace']);
                $statement->bindParam(':exclusion', $_POST['exclusion']);
                $statement->bindParam(':audit_type', $_POST['audit_type']);
                $statement->bindParam(':audit_date', $_POST["audit_date_CDC"]);
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
                $statement->bindParam(':yesrev1', $rev1check);
                $statement->bindParam(':yesrev2', $rev2check);
                $statement->bindParam(':comments_if_any', $_POST['comments_if_any']);
                $statement->bindParam(':mem1Grant', $mem1Grant);
                $statement->bindParam(':yes1', $check1);
                $statement->bindParam(':yes2', $check2);
                $statement->bindParam(':yes3', $check3);
                $statement->bindParam(':review2comment', $_POST['comments_if_any_2nd_review']);
                $statement->bindParam(':rev2grant', $mem2Grant);
                $statement->bindParam(':yesrev3', $rev3check);
                $statement->bindParam(':cdc_status', $mem3Grant);
                $statement->bindParam(':comments', $_POST['comments']);
                $statement->bindParam(':Date', $Date);
                $statement->bindParam(':rcauditdate', $rc_auditdate);
                $statement->bindParam(':rd_restoredate', $rc_restoredate);
                $statement->bindParam(':certificateNo', $certNo);
                $statement->bindParam(':revCdc', $revNo);
                $statement->bindParam(':revDatecdc', $revDate);
                $statement->bindParam(':expiredate', $expiredate);
                $statement->bindParam(':periodicalaudit', $periodicaldate);
                $statement->bindParam(':periodicalauditcomment', $_POST["periodicalAuditcoment"]);
                $statement->bindParam(':nextaudittype', $_POST["nextaudittype"]);
                $statement->bindParam(':ncval', $_POST["ncval"]);
                $statement->bindParam(':ofival', $_POST["ofival"]);
                $statement->bindParam(':oplus', $_POST["oplus"]);
                
                error_log("=== CDC INSERT ATTEMPT ===");
                error_log("Service ID: " . $service);
                error_log("Customer ID: " . $_POST['customer_Id']);
                
                try {
                    $result = $statement->execute();
                    error_log("INSERT result: " . ($result ? "SUCCESS" : "FAILED"));
                    if (!$result) {
                        error_log("INSERT error: " . print_r($statement->errorInfo(), true));
                    }
                    $newCrrId = (int) $db->lastInsertId();
                    error_log("New crr_id: " . $newCrrId);
                } catch (Exception $e) {
                    error_log("INSERT Exception: " . $e->getMessage());
                    throw $e;
                }

                $officeUsePayload = isset($_POST['office_use']) && is_array($_POST['office_use']) ? $_POST['office_use'] : [];
                $officeUseEntries = normaliseCdcOfficePayload($officeUsePayload);
                if ($newCrrId > 0) {
                    replaceCdcOfficeUse($db, $newCrrId, $officeUseEntries);
                    error_log("Office use data saved for crr_id: " . $newCrrId);
                }

                $service = "SELECT * FROM service WHERE service_id = :id";
                $statement = $db->prepare($service);
                $statement->bindParam(':id', $_POST['uni_serviceid']);
                $statement->execute();
                $serviceInfo = $statement->fetch();

                if ($mem3Grant == "Granted") {
                    $certPrint = "INSERT INTO certificate_print (customer_id, service_id, customer_name, customer_address, certificate_no, certificate_scope, registration_date, valid_until, before_date) "
                            . "VALUES (:customer,:service,:customerName,:customerAddress,:certificationNo, :certifyScope, :certifyRegistrationDate,:certifyValidTill,:certifyBeforeDate)";
                    $statement = $db->prepare($certPrint);
                    $statement->bindParam(':customer', $_POST['customer_Id']);
                    $statement->bindParam(':service', $_POST['service_Id']);
                    $statement->bindParam(':customerName', $_POST['audited_Organisation']);
                    $statement->bindParam(':customerAddress', $_POST['address']);
                    $statement->bindParam(':certificationNo', $certNo);
                    $statement->bindParam(':certifyScope', $_POST['product_service']);
                    $statement->bindParam(':certifyRegistrationDate', $Date);
                    $statement->bindParam(':certifyValidTill', $expiredate);
                    $statement->bindParam(':certifyBeforeDate', $periodicaldate);
                    $statement->execute();

                    if ($serviceInfo['service_type'] == "SP") {
                        $updateService = "UPDATE service SET service_done = 'DONE' WHERE service_id = :id";
                        $statement = $db->prepare($updateService);
                        $statement->bindParam(':id', $_POST['uni_serviceid']);
                        $statement->execute();
                    }

                    $sqla = "SELECT * FROM `customer_info` WHERE `id` = :customer";
                    $statement = $db->prepare($sqla);
                    $statement->bindParam(':customer', $_POST['customer_Id']);
                    $statement->execute();
                    $customer = $statement->fetch();

                    $sqlc = "SELECT count(certificate_no) as cert FROM `clientlist` where certificate_no = :customer";
                    $statement = $db->prepare($sqlc);
                    $statement->bindParam(':customer', $_POST['certificateNo']);
                    $statement->execute();
                    $customercount = $statement->fetch();

                    if ($customercount["cert"] < 1) {
                        $sqlb = "INSERT INTO `clientlist`(`certificate_no`, `client_name`, `client_address`, `scope`, `validity_status`) "
                                . "VALUES (:certificateNo, :customer, :address, :scope, :validity)";
                        $statement = $db->prepare($sqlb);
                        $statement->bindParam(':certificateNo', $certNo);
                        $statement->bindParam(':customer', $customer['customer_name']);
                        $statement->bindParam(':address', $customer['address']);
                        $statement->bindParam(':scope', $customer['scope']);
                        $statement->bindParam(':validity', $customer['audit_status']);
                        $statement->execute();
                    }

                    $action1 = "Suspension Reminder";
                    $action2 = "Suspend Letter";
                    $action3 = "Withdrawal Reminder";
                    $action4 = "Withdraw Letter";

                    $svccoutnset = $customer["service_count"] - 1;
                    $monthadd = 36 * $svccoutnset;



                    //  new secvice creating here 







                    // sa-1 service
                    $si1 = $monthadd + 23;
                    $si2 = $monthadd + 24;
                    $si3 = $monthadd + 26;
                    $si4 = $monthadd + 28;

                    // sa-2 service
                    $sii1 = $monthadd + 34;
                    $sii2 = $monthadd + 35;
                    $sii3 = $monthadd + 36;
                    $sii4 = $monthadd + 40;

                    // rc service
                    $rc1 = $monthadd + 47;
                    $rc2 = $monthadd + 48;
                    $rc3 = $monthadd + 50;
                    $rc4 = $monthadd + 52;

                    if ($uniServiceType['service_type'] != "SP") {
                        if ($uniServiceType['service_type'] == "CA") {
                            $date1 = $certifyDate['certification_stage_date'] . '+ 11 MONTH';
                            //$newdate11 = date('Y-m-d', strtotime($date1));
                            $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                            $newdate1 = date('Y-m-d', strtotime($date1));

                            $date2 = $certifyDate['certification_stage_date'] . '+ 12 MONTH';
                            //$newdate22 = date('Y-m-d', strtotime($date2));
                            $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                            $newdate2 = date('Y-m-d', strtotime($date2));

                            $date3 = $certifyDate['certification_stage_date'] . '+ 14 MONTH';
                            //$newdate33 = date('Y-m-d', strtotime($date3));
                            $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                            $newdate3 = date('Y-m-d', strtotime($date3));

                            $date4 = $certifyDate['certification_stage_date'] . '+ 16 MONTH';
                            //$newdate44 = date('Y-m-d', strtotime($date4));
                            $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                            $newdate4 = date('Y-m-d', strtotime($date4));

                            $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)";
                            $statement = $db->prepare($auditServeliance1);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action1', $action1);
                            $statement->bindParam(':dueDate1', $newdate1);
                            $statement->execute();

                            $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                            $statement = $db->prepare($auditServeliance2);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action2', $action2);
                            $statement->bindParam(':dueDate2', $newdate2);
                            $statement->execute();

                            $auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate3)";
                            $statement = $db->prepare($auditServeliance3);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action3', $action3);
                            $statement->bindParam(':dueDate3', $newdate3);
                            $statement->execute();

                            $auditServeliance4 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action4, :dueDate4)";
                            $statement = $db->prepare($auditServeliance4);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action4', $action4);
                            $statement->bindParam(':dueDate4', $newdate4);
                            $statement->execute();
                        } 
                        else if ($uniServiceType['service_type'] == "SA-I") {
                            $date1 = $certifyDate['certification_stage_date'] . '+ ' . $si1 . ' MONTH';
                            //$newdate11 = date('Y-m-d', strtotime($date1));
                            $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                            $newdate1 = date('Y-m-d', strtotime($date1));

                            $date2 = $certifyDate['certification_stage_date'] . '+ ' . $si2 . ' MONTH';
                            //$newdate22 = date('Y-m-d', strtotime($date2));
                            $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                            $newdate2 = date('Y-m-d', strtotime($date2));

                            $date3 = $certifyDate['certification_stage_date'] . '+ ' . $si3 . ' MONTH';
                            //$newdate33 = date('Y-m-d', strtotime($date3));
                            $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                            $newdate3 = date('Y-m-d', strtotime($date3));

                            $date4 = $certifyDate['certification_stage_date'] . '+ ' . $si4 . ' MONTH';
                            //$newdate44 = date('Y-m-d', strtotime($date4));
                            $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                            $newdate4 = date('Y-m-d', strtotime($date4));
                            $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)";
                            $statement = $db->prepare($auditServeliance1);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action1', $action1);
                            $statement->bindParam(':dueDate1', $newdate1);
                            $statement->execute();

                            $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                            $statement = $db->prepare($auditServeliance2);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action2', $action2);
                            $statement->bindParam(':dueDate2', $newdate2);
                            $statement->execute();

                            $auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate3)";
                            $statement = $db->prepare($auditServeliance3);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action3', $action3);
                            $statement->bindParam(':dueDate3', $newdate3);
                            $statement->execute();

                            $auditServeliance4 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action4, :dueDate4)";
                            $statement = $db->prepare($auditServeliance4);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action4', $action4);
                            $statement->bindParam(':dueDate4', $newdate4);
                            $statement->execute();
                        } 
                        else if ($uniServiceType['service_type'] == "SA-II") { //
                            $action1 = "1st Reminder";
                            $action2 = "2nd Reminder";
                            $action3 = "Expire Letter";
                            $date1 = $certifyDate['certification_stage_date'] . '+ ' . $sii1 . ' MONTH';
                            //$newdate11 = date('Y-m-d', strtotime($date1));
                            $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                            $newdate1 = date('Y-m-d', strtotime($date1));

                            $date2 = $certifyDate['certification_stage_date'] . '+ ' . $sii2 . ' MONTH';
                            //$newdate22 = date('Y-m-d', strtotime($date2));
                            $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                            $newdate2 = date('Y-m-d', strtotime($date2));

							$date3 = $certifyDate['certification_stage_date'] . '+ ' . $sii3 . ' MONTH';
							$date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
							$newdate3 = date('Y-m-d', strtotime($date3));

                            //$date4 = $certifyDate['certification_stage_date'] . '+ ' . $sii4 . ' MONTH';
                            //$date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                            //$newdate4 = date('Y-m-d', strtotime($date4));

                            $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)";
                            $statement = $db->prepare($auditServeliance1);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action1', $action1);
                            $statement->bindParam(':dueDate1', $newdate1);
                            $statement->execute();

                            $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                            $statement = $db->prepare($auditServeliance2);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action2', $action2);
                            $statement->bindParam(':dueDate2', $newdate2);
                            $statement->execute();
							
							$auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate3)";
							$statement = $db->prepare($auditServeliance3);
							$statement->bindParam(':serviceid', $serviceid);
							$statement->bindParam(':action3', $action3);
							$statement->bindParam(':dueDate3', $newdate3);
							$statement->execute();

                            //$auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action4, :dueDate4)";
                            //$statement = $db->prepare($auditServeliance3);
                            //$statement->bindParam(':serviceid', $serviceid);
                            //$statement->bindParam(':action4', $action3);
                            //$statement->bindParam(':dueDate4', $newdate3);
                            //$statement->execute();
                        } 
                        else if ($uniServiceType['service_type'] == "RC") {
                            $date1 = $certifyDate['certification_stage_date'] . '+ ' . $rc1 . ' MONTH';
                            //$newdate11 = date('Y-m-d', strtotime($date1));
                            $date1 = date("Y-m-d", strtotime('-1 day', strtotime($date1)));
                            $newdate1 = date('Y-m-d', strtotime($date1));

                            $date2 = $certifyDate['certification_stage_date'] . '+ ' . $rc2 . ' MONTH';
                            //$newdate22 = date('Y-m-d', strtotime($date2));
                            $date2 = date("Y-m-d", strtotime('-1 day', strtotime($date2)));
                            $newdate2 = date('Y-m-d', strtotime($date2));

                            $date3 = $certifyDate['certification_stage_date'] . '+ ' . $rc3 . ' MONTH';
                            //$newdate33 = date('Y-m-d', strtotime($date3));
                            $date3 = date("Y-m-d", strtotime('-1 day', strtotime($date3)));
                            $newdate3 = date('Y-m-d', strtotime($date3));

                            $date4 = $certifyDate['certification_stage_date'] . '+ ' . $rc4 . ' MONTH';
                            //$newdate44 = date('Y-m-d', strtotime($date4));
                            $date4 = date("Y-m-d", strtotime('-1 day', strtotime($date4)));
                            $newdate4 = date('Y-m-d', strtotime($date4));

                            $auditServeliance1 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action1, :dueDate1)";
                            $statement = $db->prepare($auditServeliance1);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action1', $action1);
                            $statement->bindParam(':dueDate1', $newdate1);
                            $statement->execute();

                            $auditServeliance2 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action2, :dueDate2)";
                            $statement = $db->prepare($auditServeliance2);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action2', $action2);
                            $statement->bindParam(':dueDate2', $newdate2);
                            $statement->execute();

                            $auditServeliance3 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action3, :dueDate3)";
                            $statement = $db->prepare($auditServeliance3);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action3', $action3);
                            $statement->bindParam(':dueDate3', $newdate3);
                            $statement->execute();

                            $auditServeliance4 = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:serviceid, :action4, :dueDate4)";
                            $statement = $db->prepare($auditServeliance4);
                            $statement->bindParam(':serviceid', $serviceid);
                            $statement->bindParam(':action4', $action4);
                            $statement->bindParam(':dueDate4', $newdate4);
                            $statement->execute();
                        }
                        if ($uniServiceType['service_type'] == "CA") {
                            $sql = "UPDATE customer_info SET `customer_name`= :name, `certification_no`= :certno, `address`= :address, `scope`= :scope, `aif_nace`= :iafnace WHERE id = :id ";
                            $statement = $db->prepare($sql);
                            $statement->bindParam(":id", $_POST['customer_Id']);
                            $statement->bindParam(":name", $_POST['audited_Organisation']);
                            $statement->bindParam(":certno", $certNo);
                            $statement->bindParam(":address", $_POST['address']);
                            $statement->bindParam(":scope", $_POST['product_service']);
                            $statement->bindParam(":iafnace", $_POST['iaf_nace']);
                            $statement->execute();

                            if (isset($revDate) && !empty($revDate) && isset($certNo) && !empty($certNo)) {
//                            echo "in";
//                        $certNo = $_POST['certificateNo'] + 1;
                                $certno = "SELECT certification_no FROM `consulting_firm`";
                                $statement = $db->prepare($certno);
                                $statement->execute();
                                $certiNo = $statement->fetch();

                                $certNo = $certiNo["certification_no"] + 1;

                                $updateCertNo = "UPDATE consulting_firm SET certification_no = :no";
                                $statement = $db->prepare($updateCertNo);
                                $statement->bindParam(":no", $certNo);
                                $statement->execute();
                            }
                        }
                    }
                }
                if (!empty($serviceid)) {
                    aitfSyncClientStatusDates($db, $serviceid, $_POST['customer_Id'], isset($Date) ? $Date : '');
                }
                aitfSyncExistingClientStatusDatesForCustomer($db, $_POST['customer_Id'], isset($Date) ? $Date : '');
                header("Location: customerAccount.php?customerid=" . $_POST['customer_Id']);
            }
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';
    protected $count = '1';

    public function setInfo($info) {
        $this->count = $count;
    }

    //Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        $this->Image($image_file, 17, 5, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 17.5);
        // Title
        $this->SetXY(60, 10);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        //SubTitle
        $this->SetXY(50, 18);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 15, 'CERTIFICATE DECISION COMMITTEE DELIBERATION AND DECISION', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->writeHTMLCell($w = '', $h = '', $x = '', $y = '', $this->header, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'L', $autopadding = true);
        $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));

//        $this->Line(5, 5, $this->getPageWidth() - 5, 5);
//
//        $this->Line($this->getPageWidth() - 5, 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, $this->getPageHeight() - 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, 5, 5, $this->getPageHeight() - 5);
    }

    // Page footer
    public function Footer() {
        $html = '<table border="0"  cellpadding="4" cellspacing="0">';
        $html .= '<tr><td colspan="1">F 7.33</td>';
        $html .= '<td colspan="1">Rev.:04</td>';
        $html .= '<td colspan="1">Date: 01.06.2019</td>';

        $html .= '</tr></table>';
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
//        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->writeHTMLCell(0, 0, '', '', $html, 0, 0, false, "L", true);
        $this->count = $this->count + 1;
        // Position at 15 mm from bottom
//        $this->SetY(-15);
//        // Set font
//        $this->SetFont('helvetica', 'I', 8);
//        // Page number
//        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('CdcRecomendation');
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
$pdf->SetMargins(PDF_MARGIN_LEFT, 25, 5);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

if (isset($_POST['crr_id'])) {
    error_log("CDC PDF Download: crr_id=" . $_POST['crr_id']);
    $crrDetails = "SELECT * FROM cdc_report_review WHERE crr_id = :id";
    $statement = $db->prepare($crrDetails);
    $statement->bindParam(':id', $_POST['crr_id']);
    $statement->execute();
    $crrInfo = $statement->fetch();
    
    if (!$crrInfo) {
        error_log("CDC PDF ERROR: No data found for crr_id=" . $_POST['crr_id']);
        die("Error: CDC record not found");
    }
    error_log("CDC PDF: Data fetched successfully. file_no=" . $crrInfo['file_no']);

    $file_no = $crrInfo['file_no'];
    $audit_organization = strtoupper($crrInfo['audit_organization']);
    $address = $crrInfo['address'];
    $product_service = $crrInfo['product_service'];
    $iaf_nacf = $crrInfo['iaf_nacf'];
    $exclusion = $crrInfo['exclusion'];
    $audit_type = $crrInfo['audit_type'];
    $audit_dates = $crrInfo['audit_date'];
    $covered_for = $crrInfo['covered_for'];
    $accreditation = $crrInfo['accreditation'];
    $lead_auditor = $crrInfo['lead_auditor'];
    $technical_expert = $crrInfo['technical_expert'];
    //$assDate = $crrInfo['assDate'];
    $auditors = $crrInfo['auditors'];
    $cdc_members = $crrInfo['reviewer_1'] . ', ' . $crrInfo['reviewer_2'] . ', ' . $crrInfo['granted_by'];
    $cdc_members1 = $crrInfo['reviewer_1'];
    //$review1date = date("d.m.Y", strtotime($crrInfo['reviewer_1_date']));
    $cdc_members2 = $crrInfo['reviewer_2'];
    $cdc_members3 = $crrInfo['granted_by'];
    $system_during = $crrInfo['system_during'];
    $system_complaint = $crrInfo['system_complainte'];
    $comments_if_any = $crrInfo['comments_if_any'];
    $comments = $crrInfo['comments'];
    $rev2Comment = $crrInfo['review_2_comment'];
    $Date = date("d.m.Y", strtotime($crrInfo['Date']));
    
    // Fetch office use data from the new table
    $officeUseEntries = getCdcOfficeUse($db, (int)$_POST['crr_id']);
    
    // Legacy fields - use as fallback if office_use table is empty
    $certificateNo = $crrInfo['certificateNo'];
    $revCdc = $crrInfo['revCdc'];
    $revDatecdc = "";
    if ($crrInfo['revDatecdc'] != NULL) {
        $revDatecdc = date("d.m.Y", strtotime($crrInfo['revDatecdc']));
    }
    if ($crrInfo['assDate'] != NULL) {
        $assDate = date("d.m.Y", strtotime($crrInfo['assDate']));
    }
    $expiredate = "";
    if ($crrInfo['expiredate'] != NULL) {
        $expiredate = date("d.m.Y", strtotime($crrInfo['expiredate']));
    }
    $periodicalaudit = "";
    if ($crrInfo['periodicalaudit'] != NULL) {
        $periodicalaudit = date("d.m.Y", strtotime($crrInfo['periodicalaudit']));
    }
    $periodiccomm = $crrInfo['periodical_audit_comment'];

    $nextaudit = '';
    if ($crrInfo['next_audit_type'] == 'CA') {
        $nextaudit = '1st surveillance';
    }
    if ($crrInfo['next_audit_type'] == 'SA-I') {
        $nextaudit = '2nd surveillance';
    }
    if ($crrInfo['next_audit_type'] == 'SA-II') {
        $nextaudit = 'Re-Certification';
    }
    if ($crrInfo['next_audit_type'] == 'RC') {
        $nextaudit = '1st surveillance';
    }

    $myes1 = "";
    $mno1 = "";
    $myes2 = "";
    $mno2 = "";
    $mna2 = "";

    $mgrant1 = "";
    $mnotgrant1 = "";

    if ($crrInfo['rev1_yes'] == "Yes") {
        $myes1 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $mno1 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } else {
        $mno1 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $myes1 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }

    if ($crrInfo['rev2_yes'] == "Yes") {
        $myes2 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $mno2 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
        $mna2 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } else if ($crrInfo['rev2_yes'] == "No") {
        $mno2 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $myes2 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
        $mna2 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } else {
        $mna2 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $myes2 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
        $mno2 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }

    if ($crrInfo['review_1_granting'] == "Granted") {
        $mgrant1 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $mnotgrant1 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } else {
        $mnotgrant1 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $mgrant1 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }

    $yes1 = "";
    $no1 = "";
    $yes2 = "";
    $no2 = "";
    $yes3 = "";
    $no3 = "";

    $mgrant2 = "";
    $mnotgrant2 = "";

    if ($crrInfo['yes1'] == "Yes") {
        $yes1 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $no1 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } else {
        $no1 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $yes1 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
    if ($crrInfo['yes2'] == "Yes") {
        $yes2 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $no2 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } else {
        $yes2 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
        $no2 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
    }

    if ($crrInfo['yes3'] == "Yes") {
        $yes3 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $no3 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } else {
        $yes3 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
        $no3 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
    }

    if ($crrInfo['review_2_granting'] == "Granted") {
        $mgrant2 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $mnotgrant2 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } else {
        $mnotgrant2 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $mgrant2 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }

    $myes3 = "";
    $mno3 = "";

    $mgrant3 = "";
    $mnotgrant3 = "";

    if ($crrInfo['rev3_yes'] == "Yes") {
        $myes3 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $mno3 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } else {
        $mno3 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $myes3 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }

    if ($crrInfo['cdc_status'] == "Granted") {
        $mgrant3 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $mnotgrant3 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    } else {
        $mnotgrant3 = '<img src="./images/checkboxChecked.png"  width="10" height="10">';
        $mgrant3 = '<img src="./images/uncheckedCheckbox.png"  width="10" height="10">';
    }
// ---------------------------------------------------------
// set font
    $pdf->SetFont('dejavusans', '', 10);

// add a page
    $pdf->AddPage();

// create some HTML content
    $html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9">
    <table border="1"  cellpadding="6" cellspacing="0" >
    <tr>
        <td>
        <br>
        Agreement No: ' . $file_no . '
        </td>
    </tr>
     <tr>
        <td><br>
        Name of organization to be certified: <b>' . $audit_organization . '</b>
        </td>
    </tr>
     <tr>
        <td>
      Site/s to be certified: ' . $address . '
         
        </td>
    </tr>
    <tr>
        <td><br>
        Product / Service to be certified (Scope): ' . $product_service . '
        </td>
    </tr>
    <tr>
    <td style="width:40%;">Technical area:' . $iaf_nacf . '</td>
        <td style="width:60%;">
        <br>
        Non Applicability of Standards Requirement/s: ' . $exclusion . '</td>
    </tr>
     <tr>
        <td style="width:40%;">
        <br>
        Audit type: ' . $audit_type . '</td>
            <td style="width:60%;">
        <br>
        Date (s): ' . $audit_dates . '</td>
    </tr>
    <tr>
        <td style="width:100%;"><br>
        Standard/s covered for certification: ' . $covered_for . '
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
        <br>
        Accreditation Ref.: ' . $accreditation . '
        </td>
    </tr>
    <tr>
        <td style="width:50%;">
        <br>
        Lead auditor: ' . $lead_auditor . '
        </td>
        <td style="width:50%;">
        <br>
        Technical expert: ' . $technical_expert . '
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
        <br>
        Auditor/s: ' . $auditors . '
        </td>
    </tr>
        <tr>
        <td style="width:100%;">
        <br>
        Assessment / NC (M) Closing date: ' . $assDate . '
        </td>
    </tr>
    <tr bgcolor="#DCDCDC">
        <td style="width:50%;">
        <br>
        <b>(For Recertification)</b>1. Review of the client’s management system performance during certification tenure: ' . $system_during . '<br><br><br>
        </td>
        <td style="width:50%;"><br>
        2. Complaints received if any from the customers of the certified client and other sources: ' . $system_complaint . '<br><br><br></td> 
    </tr>
     <tr>
        <td style="width:25%;">
        <br>
        CDC members: 
        </td>
        <td style="width:25%;">
        <br>
        1. ' . $cdc_members1 . '
        </td>
        <td style="width:25%;">
        <br>
        2. ' . $cdc_members2 . '
        </td>
        <td style="width:25%;">
        <br>
        3. ' . $cdc_members3 . '
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
        <br>
        CDC Date: ' . $Date . '
        </td>
    </tr>
    <tr bgcolor="#DCDCDC">
        <th style="width:70%;" ><br>
        <b>CDC Member 1</b></th>
        <th style="width:30%; corder:white">
        <b></b></th>
    </tr>
    <tr>
        <td style="width:70%;">&nbsp;&nbsp;Audit processes are satisfactorily completed
                        <span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $myes1 . '&nbsp;&nbsp;<label for="Yes">Yes</label>&nbsp;&nbsp;</span>
                         <span> ' . $mno1 . '&nbsp;&nbsp;<label for="No">No*</label>&nbsp;&nbsp;</span>
            <br>
                               <br>
                               Complied as per Annex I (in case of Stage 1) 
                            <span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mna2 . '&nbsp;&nbsp;<label for="Yes">N.A.</label>&nbsp;&nbsp;' . $myes2 . '&nbsp;&nbsp;<label for="Yes">Yes</label>&nbsp;&nbsp;</span>
                            <span> ' . $mno2 . '&nbsp;&nbsp;<label for="No">No*</label>&nbsp;&nbsp;</span>
                               <br>
                               <hr style="border-top: dotted 1px;" />
                               <br>
               *Comments if any:' . $comments_if_any . '
               <br>               
        </td>
        <td style="width:30%;">
            <span> ' . $mgrant1 . '&nbsp;&nbsp;<label for="Yes">Granted</label>&nbsp;&nbsp;</span>
                         <span> ' . $mnotgrant1 . '&nbsp;&nbsp;<label for="No">Not-Granted*</label>&nbsp;&nbsp;</span>
        <br>
        <br><br><br><br>
        ' . $cdc_members1 . ' 
        <br>        
        Name /Signature        
        
        </td>
    </tr>
     <tr bgcolor="#DCDCDC">
        <th style="width:70%;" ><br>
        <b>CDC Member 2</b></th>
        <th style="width:30%;">
        
        <b></b></th>
    </tr>
    <tr>
        <td style="width:70%;">&nbsp;&nbsp;All activities are satisfactorily completed
                        <span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $yes1 . '&nbsp;&nbsp;<label for="Yes">Yes</label>&nbsp;&nbsp;</span>
                         <span> ' . $no1 . '&nbsp;&nbsp;<label for="No">No*</label>&nbsp;&nbsp;</span>
            <br>
                               <br>
                               Certification criteria have been met
                            <span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $yes2 . '&nbsp;&nbsp;<label for="Yes">Yes</label>&nbsp;&nbsp;</span>
                            <span> ' . $no2 . '&nbsp;&nbsp;<label for="No">No*</label>&nbsp;&nbsp;</span>
                               <br>
                               <br>
                               Adequate justification for LA’s recommendation
                             <span> ' . $yes3 . '&nbsp;&nbsp;<label for="Yes">Yes</label>&nbsp;&nbsp;</span>
                            <span> ' . $no3 . '&nbsp;&nbsp;<label for="No">No*</label>&nbsp;&nbsp;</span><br> 
                                <hr style="border-top: dotted 1px;" />
               <br>
               *Comments if any: ' . $rev2Comment . '
               <br>
               
        </td>
        <td style="width:30%;"><br>
        <span> ' . $mgrant2 . '&nbsp;&nbsp;<label for="Yes">Granted</label>&nbsp;&nbsp;</span>
                         <span> ' . $mnotgrant2 . '&nbsp;&nbsp;<label for="No">Not-Granted*</label>&nbsp;&nbsp;</span>
        <br>
        <br>
        <br><br>
        <br><br>
        ' . $cdc_members2 . '
        <br>
        Name/ Signature:
        </td>
    </tr>
<tr bgcolor="#DCDCDC">
        <th style="width:70%;" ><br>
        <b>CDC Member 3</b></th>
        <th style="width:30%;">
        
        <b></b></th>
    </tr>
<tr>
        <td style="width:70%;">&nbsp;&nbsp;Certification criteria have been met.
                        <span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $myes3 . '&nbsp;&nbsp;<label for="Yes">Yes</label>&nbsp;&nbsp;</span>
                         <span> ' . $mno3 . '&nbsp;&nbsp;<label for="No">No*</label>&nbsp;&nbsp;</span>
                        <br>
                        <hr style="border-top: dotted 1px;" />
                        <br>                              
               *Comments if any:' . $comments . '
               <br>               
        </td>
        <td style="width:30%;">
            <span> ' . $mgrant3 . '&nbsp;&nbsp;<label for="Yes">Granted</label>&nbsp;&nbsp;</span>
                         <span> ' . $mnotgrant3 . '&nbsp;&nbsp;<label for="No">Not-Granted*</label>&nbsp;&nbsp;</span>
        <br>
        <br><br><br>
        ' . $cdc_members3 . ' 
        <br>        
        Name /Signature        
        
        </td>
    </tr>
</table>
 
</font></p>';

// output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->lastPage();

    $pdf->AddPage();

    // Generate office use section - use new table data or legacy fields
    $officeUseHtml = '';
    $is_cdc_only = isset($crrInfo['is_cdc_only']) ? (int)$crrInfo['is_cdc_only'] : 0;
    
    if ($is_cdc_only === 0) {
        if (!empty($officeUseEntries)) {
        // Use data from cdc_report_review_office_use table (multi-ISO support)
        foreach ($officeUseEntries as $index => $entry) {
            $isoLabel = !empty($entry['iso_label']) ? htmlspecialchars($entry['iso_label']) : '';
            $certNo = !empty($entry['certificate_no']) ? htmlspecialchars($entry['certificate_no']) : '';
            $revNo = !empty($entry['rev_no']) ? htmlspecialchars($entry['rev_no']) : '';
            $revDate = !empty($entry['rev_date']) ? date("d.m.Y", strtotime($entry['rev_date'])) : '';
            $expDate = !empty($entry['expire_date']) ? date("d.m.Y", strtotime($entry['expire_date'])) : '';
            $nextPeriodical = !empty($entry['periodical_audit']) ? date("d.m.Y", strtotime($entry['periodical_audit'])) : '';
            $auditType = !empty($entry['audit_type']) ? htmlspecialchars($entry['audit_type']) : '';
            $officeComments = !empty($entry['comments']) ? htmlspecialchars($entry['comments']) : '';
            
            // Map audit type to description
            $nextAuditDesc = '';
            if ($auditType == 'CA') $nextAuditDesc = '1st surveillance';
            elseif ($auditType == 'SA-I') $nextAuditDesc = '2nd surveillance';
            elseif ($auditType == 'SA-II') $nextAuditDesc = 'Re-Certification';
            elseif ($auditType == 'RC') $nextAuditDesc = '1st surveillance';
            
            if ($index > 0) {
                $officeUseHtml .= '<tr><td colspan="2"><hr style="border-top: solid 1px #ddd;" /></td></tr>';
            }
            
            if (!empty($isoLabel)) {
                $officeUseHtml .= '<tr><td colspan="2" style="background-color:#f0f0f0;"><b>Standard: ' . $isoLabel . '</b></td></tr>';
            }
            
            $officeUseHtml .= '
            <tr>
                <td style="width:100%;"><br>
                    Certificate No: ' . $certNo . '
                    <br><br>
                    Rev No: ' . $revNo . '
                    <br><br>
                    Rev date: ' . $revDate . '
                </td>
            </tr>
            <tr>
                <td style="width:100%;"><br>
                    Expire on date: ' . $expDate . '
                </td>
            </tr>
            <tr>
                <td style="width:100%;"><br>
                    Next periodical audit to be conducted before date: ' . $nextPeriodical . ' (' . $nextAuditDesc . ')
                </td>
            </tr>
            <tr>
                <td style="width:100%;"><br>
                    Comments if any: ' . $officeComments . '<br><br>
                </td>
            </tr>';
        }
    } else {
        // Fallback to legacy fields if office_use table is empty
        $officeUseHtml = '
        <tr>
            <td style="width:100%;"><br>
                Certificate No: ' . $certificateNo . '
                <br><br>
                Rev No: ' . $revCdc . '
                <br><br>
                Rev date: ' . $revDatecdc . '
            </td>
        </tr>
        <tr>
            <td style="width:100%;"><br>
                Expire on date: ' . $expiredate . '
            </td>
        </tr>
        <tr>
            <td style="width:100%;"><br>
                Next periodical audit to be conducted before date: ' . $periodicalaudit . ' (' . $nextaudit . ')
            </td>
        </tr>
        <tr>
            <td style="width:100%;"><br>
                Comments if any: ' . $periodiccomm . '<br><br>
            </td>
        </tr>';
    }
    } // closes if ($is_cdc_only === 0)
    
    $officeUseSection = '';
    if ($is_cdc_only === 0) {
        $officeUseSection = '
        <tr bgcolor="#DCDCDC">
            <th align="center" style="width:100%;">~ FOR OFFICE USE ~</th>
        </tr>
        ' . $officeUseHtml;
    }

    $html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9"><table border="1"  cellpadding="5" cellspacing="0">
    ' . $officeUseSection . '
     <tr>
        <td style="width:100%;"><br>
            <br>
            Technical operational staff (STO):            
    </td>
    </tr>
  </table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Clean output buffer and send PDF
    ob_end_clean();
    error_log("CDC PDF: PDF generated and sent successfully");
    $pdf->Output('CdcRecomendation.pdf', 'I');
    exit();
}

<?php



session_start();
include_once './dbConnect.php';
include_once './include/cdc_office_use.php';
include_once './include/cdc_audit_date_sync.php';
date_default_timezone_set('Asia/Kolkata');

if(isset ($_GET['report_Id']) && !empty ($_GET['report_Id']) && isset ($_GET['service_id']) && !empty ($_GET['service_id'])) {
    try {
        
        $reportii= "SELECT * FROM stage_ii_audit_id WHERE stage_ii_id = :reportId and ca_service_id = :service";
        $statement = $db->prepare($reportii);
        $statement->bindParam(':reportId', $_GET['report_Id']);
        $statement->bindParam(':service', $_GET['service_id']);
        $statement->execute();
        $reportDetailsData = $statement->fetch();
        
        $reportRowsii = "SELECT * FROM stage_ii_audit WHERE ca_service_id_ii=:service and ca_service_audit_ii = :id";
        $statement = $db->prepare($reportRowsii);
        $statement->bindParam(':id', $_GET['report_Id']);
        $statement->bindParam(':service', $_GET['service_id']);
        $statement->execute();
        $reportInfoData = $statement->fetchAll();
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
        $customer3 = "SELECT * FROM stage_twoaudit_id WHERE service_id = :id";
        $statement = $db->prepare($customer3);
        $statement->bindParam(':id', $_GET['service_id']);
        $statement->execute();
        $customerDetailsz1 = $statement->fetch();
                $customer3 = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :id";
        $statement = $db->prepare($customer3);
        $statement->bindParam(':id', $_GET['service_id']);
        $statement->execute();
        $customerDetailsz2 = $statement->fetch();
        
        echo json_encode(array($reportDetailsData, $reportInfoData, $customerDetailsx, $customerDetailsy,$customerDetailsz1,$customerDetailsz2));
        
        
    } catch (Exception $ex) {

    }
    
}
 if(isset ($_GET['reportId']) && !empty ($_GET['reportId']) && isset ($_GET['serviceid']) && !empty ($_GET['serviceid'])) {
    try {
        $report= "SELECT * FROM stage_i_audit_id WHERE stage_i_id = :reportId and ca_service_id = :service";
        $statement = $db->prepare($report);
        $statement->bindParam(':reportId', $_GET['reportId']);
        $statement->bindParam(':service', $_GET['serviceid']);
        $statement->execute();
        $reportDetails = $statement->fetch();
        
        $reportRows = "SELECT * FROM stage_i_audit WHERE ca_service_audit = :id";
        $statement = $db->prepare($reportRows);
        $statement->bindParam(':id', $_GET['reportId']);
        $statement->execute();
        $reportInfo = $statement->fetchAll();

        $customer = "SELECT * FROM stage_audit_one WHERE ca_service_id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_GET['serviceid']);
        $statement->execute();
        $customerDetailsx = $statement->fetchAll(PDO::FETCH_ASSOC);
        
            $customer = "SELECT * FROM stage_audit_two WHERE service_id = :id";
            $statement = $db->prepare($customer);
            $statement->bindParam(':id', $_GET['serviceid']);
            $statement->execute();
            $customerDetailsy = $statement->fetchAll(PDO::FETCH_ASSOC);
            $customer3 = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :id";
        $statement = $db->prepare($customer3);
        $statement->bindParam(':id', $_GET['serviceid']);
        $statement->execute();
        $customerDetailsz = $statement->fetch();
        
        // Fetch certification standards from audit_application table
        $auditApp = "SELECT certification FROM audit_application WHERE service_id = :id";
        $statement = $db->prepare($auditApp);
        $statement->bindParam(':id', $_GET['serviceid']);
        $statement->execute();
        $auditAppInfo = $statement->fetch();
        
        echo json_encode(array($reportDetails, $reportInfo, $customerDetailsx, $customerDetailsy,$customerDetailsz, $auditAppInfo));
    } catch (Exception $ex) {
        echo $ex;
    }
}



if(isset ($_GET['crr_Id']) && !empty ($_GET['crr_Id']) && isset ($_GET['service']) && !empty ($_GET['service'])) {
    try {
        error_log("reviewReportData.php - CDC Download Request: crr_Id=" . $_GET['crr_Id'] . ", service=" . $_GET['service']);
        
        $crr= "SELECT * FROM `cdc_report_review` WHERE crr_id = :crr_Id";
        $statement = $db->prepare($crr);
        $statement->bindParam(':crr_Id', $_GET['crr_Id']);
        $statement->execute();
        $crrData = $statement->fetch();

        // Pre-format Assessment / NC (M) Closing date (assDate) similar to PDF logic
        if ($crrData && isset($crrData['assDate'])) {
            $rawAss = $crrData['assDate'];
            $crrData['assDate_formatted'] = '';
            if (!empty($rawAss) && $rawAss !== '0000-00-00' && $rawAss !== '0000-00-00 00:00:00' && strtolower(trim($rawAss)) !== 'null') {
                $tsAss = strtotime($rawAss);
                if ($tsAss !== false) {
                    $crrData['assDate_formatted'] = date('d.m.Y', $tsAss);
                }
            }
        }
        
        if (!$crrData) {
            error_log("reviewReportData.php - ERROR: No CDC data found for crr_id=" . $_GET['crr_Id']);
        } else {
            error_log("reviewReportData.php - CDC data found: file_no=" . ($crrData['file_no'] ?? 'N/A') . ", servicetype=" . ($crrData['servicetype'] ?? 'N/A'));
        }
        
        // Use the service ID from the cdc_report_review table itself for consistency
        $serviceIdToUse = $crrData ? $crrData['ca_service_id'] : $_GET['service'];
        aitfSyncCdcAuditDateFromStageNotifications($db, $serviceIdToUse);

        $crr= "SELECT * FROM `cdc_report_review` WHERE crr_id = :crr_Id";
        $statement = $db->prepare($crr);
        $statement->bindParam(':crr_Id', $_GET['crr_Id']);
        $statement->execute();
        $crrData = $statement->fetch();
        
        $rev1 = "SELECT review_date FROM `stage_i_audit_id` WHERE ca_service_id = :svc";
        $statement = $db->prepare($rev1);
//        $statement->bindParam(":cid", $_GET['cdccustomer_ID']);
        $statement->bindParam(":svc", $serviceIdToUse);
        $statement->execute();
        $rev1date = $statement->fetch();
        
        $rev2 = "SELECT review_date FROM `stage_ii_audit_id` WHERE ca_service_id = :svc";
        $statement = $db->prepare($rev2);
//        $statement->bindParam(":cid", $_GET['cdccustomer_ID']);
        $statement->bindParam(":svc", $serviceIdToUse);
        $statement->execute();
        $rev2date = $statement->fetch();
        
        try {
            $customer = "SELECT * FROM stage_audit_one WHERE ca_service_id = :id";
            $statement = $db->prepare($customer);
            $statement->bindParam(':id', $_GET['service']);
            $statement->execute();
            $customerDetailsx = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log or handle error silently to preserve JSON response
            $customerDetailsx = [];
        }
        
             $customer = "SELECT * FROM stage_audit_two WHERE service_id = :id";
            $statement = $db->prepare($customer);
            $statement->bindParam(':id', $_GET['service']);
            $statement->execute();
            $customerDetailsy = $statement->fetchAll(PDO::FETCH_ASSOC);
            
        $customer3 = "SELECT * FROM stage_oneaudit_id WHERE ca_service_id = :id";
        $statement = $db->prepare($customer3);
        $statement->bindParam(':id', $_GET['service']);
        $statement->execute();
        $customerDetailsz = $statement->fetch();
        $customer3 = "SELECT * FROM stage_twoaudit_id WHERE service_id = :id";
        $statement = $db->prepare($customer3);
        $statement->bindParam(':id', $_GET['service']);
        $statement->execute();
        $customerDetailsz1 = $statement->fetch();
            
        $officeUseEntries = [];
        if (!empty($_GET['crr_Id'])) {
            $officeUseEntries = getCdcOfficeUse($db, (int) $_GET['crr_Id']);
        }
        
        // Fetch ISO decisions for RC services
        $isoDecisions = [];
        if (!empty($_GET['crr_Id'])) {
            try {
                $isoDecisions = getCdcIsoDecisions($db, (int) $_GET['crr_Id']);
                error_log("reviewReportData.php - Loaded " . count($isoDecisions) . " ISO decisions for crr_id=" . $_GET['crr_Id']);
            } catch (Exception $isoEx) {
                error_log("reviewReportData.php - ISO decisions fetch error: " . $isoEx->getMessage());
                $isoDecisions = [];
            }
        }

        error_log("reviewReportData.php - Returning CDC data successfully");
        echo json_encode(array($crrData, $rev1date, $rev2date, $customerDetailsx, $customerDetailsy, $customerDetailsz,$customerDetailsz1, $officeUseEntries, $isoDecisions));
        
    } catch (Exception $ex) {
        error_log("reviewReportData.php - Exception: " . $ex->getMessage());
        echo json_encode(array('error' => $ex->getMessage()));
    }
}

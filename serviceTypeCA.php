<?php


session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'commonutil.php';
include 'uploadServiceCA.php';

$file = "Image";

if (isset($_POST['dataService']) && !empty($_POST['dataService']) && isset($_FILES ["file"]) && !( $_FILES['file']['size'] == 0 ) && isset($_FILES ["file"] ["name"])) {
    try {        
        if ($_POST['dataColumn'] == "signed_notification") {
            $column = "signed_notification";
            $applicationForm = FileUploadsCA::uploadFileSignedNotification($file, $_POST["customerid"], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "signed_copy") {
            $column = "signed_copy";
            $applicationForm = FileUploadsCA::uploadFileSignedCopy($file, $_POST["customerid"], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "audit_report_I") {
            $column = "audit_report_I";
            $applicationForm = FileUploadsCA::uploadFileAuditReport($file, $_POST["customerid"], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "signed_notification_II") {
            $column = "signed_notification_II";
            $applicationForm = FileUploadsCA::uploadFileSignedNoti2($file, $_POST["customerid"], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "audit_report_II") {
            $column = "audit_report_II";
            $applicationForm = FileUploadsCA::uploadFileAuditReport2($file, $_POST["customerid"], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "auditor_notes") {
            $column = "auditor_notes";
            $applicationForm = FileUploadsCA::uploadFileAuditorNotes($file, $_POST["customerid"], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "audit_nc_II") {
            $column = "audit_nc_II";
            $applicationForm = FileUploadsCA::uploadFileAuditEvidences($file, $_POST["customerid"], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "customer_satisfaction") {
            $column = "customer_satisfaction";
            $applicationForm = FileUploadsCA::uploadFileCustomerSatisfaction($file, $_POST["customerid"], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "certificate_print") {
            $column = "certificate_print";
            $applicationForm = FileUploadsCA::uploadFileCertificatePrint($file, $_POST["customerid"], $_POST['dataService']);
        }

        $caServiceUpdate = "UPDATE service_type_ca SET " . $column . " = :updateFile WHERE ca_id = :serviceid";
        $statement = $db->prepare($caServiceUpdate);
        $statement->bindParam(':updateFile', $applicationForm);
        $statement->bindParam(':serviceid', $_POST['dataService']);
        $statement->execute();
        echo $_POST['serviceType'];
    } catch (Exception $ex) {
       echo $ex; 
    }
} else {
    if (isset($_POST['customerid']) && !empty($_POST['customerid']) && isset($_FILES ['file']) && !( $_FILES['file']['size'] == 0 ) && isset($_FILES ['file'] ['name'])) {
        try {            
            $applicationForm = FileUploadsCA::uploadFileApplicationForm($file);
            
            $service_id = "INSERT INTO service (service_type, customer_id) VALUES ('CA', :customerID)";
            $statement = $db->prepare($service_id);
            $statement->bindParam(':customerID', $_POST['customerid']);
            $statement->execute();
            
            $sql = "SELECT max(LAST_INSERT_ID(service_id)) as id From service";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $serviceid = $row['id'];
            
            $caService = "INSERT INTO service_type_ca (customer_id, service, application_form) VALUES (:customerID, :serviceid, :appForm)";
            $statement = $db->prepare($caService);
            $statement->bindParam(':customerID', $_POST['customerid']);
            $statement->bindParam(':serviceid', $serviceid);
            $statement->bindParam(':appForm', $applicationForm);
            $statement->execute();
            
            $updateNextService = "UPDATE customer_info SET next_service = 'SA-I' WHERE id = :id";
            $statement = $db->prepare($updateNextService);
            $statement->bindParam(':id',$_POST['customerid']);
            $statement->execute();



             echo $_POST['serviceType'];
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}
<?php


session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'commonutil.php';
include 'uploadServiceSAi.php';

$file = "Image";

if (isset($_POST['dataService']) && !empty($_POST['dataService']) && isset($_FILES ["file"]) && !( $_FILES['file']['size'] == 0 ) && isset($_FILES ["file"] ["name"])) {
    try { 
        if ($_POST['dataColumn'] == "info_update_form") {
            $column = "info_update_form";
            $inputFile = FileUploadsSAi::uploadFileInfoUpdateForm($file, $_POST["customerid"], $_POST['dataService']);
        }

        if ($_POST['dataColumn'] == "signed_noti_sai") {
            $column = "signed_noti_sai";
            $inputFile = FileUploadsSAi::uploadFileSignedNoti($file, $_POST["customerid"], $_POST['dataService']);
        }

        if ($_POST['dataColumn'] == "signed_copy_sai") {
            $column = "signed_copy_sai";
            $inputFile = FileUploadsSAi::uploadFileSignedCopy($file, $_POST["customerid"], $_POST['dataService']);
        }

        if ($_POST['dataColumn'] == "audit_report_sai") {
            $column = "audit_report_sai";
            $inputFile = FileUploadsSAi::uploadFilesaiAuditReport($file, $_POST["customerid"], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "auditor_notes_sai") {
            $column = "auditor_notes_sai";
            $inputFile = FileUploadsSAi::uploadFilesaiAuditorNotes($file, $_POST["customerid"], $_POST['dataService']);
        }

        if ($_POST['dataColumn'] == "audit_evidance_sai") {
            $column = "audit_evidance_sai";
            $inputFile = FileUploadsSAi::uploadFilesaiAuditsaEvidence($file, $_POST["customerid"], $_POST['dataService']);
        }

        if ($_POST['dataColumn'] == "customer_review_sai") {
            $column = "customer_review_sai";
            $inputFile = FileUploadsSAi::uploadFilesaiCustomersaiReview($file, $_POST["customerid"], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "certificate_print") {
            $column = "certificate_print";
            $inputFile = FileUploadsSAi::uploadFilesaiCertificatePrint($file, $_POST["customerid"], $_POST['dataService']);
        }

        $updateSai = "UPDATE service_type_sai SET " . $column . " = :updatefile  WHERE sai_id = :id ";
        $statement = $db->prepare($updateSai);
        $statement->bindParam(':updatefile', $inputFile);
        $statement->bindParam(':id', $_POST['dataService']);
        $statement->execute();

        echo $_POST['serviceType'];
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_POST['customerid']) && !empty($_POST['customerid']) && isset($_FILES ["file"]) && !( $_FILES['file']['size'] == 0 ) && isset($_FILES ["file"] ["name"])) {
        try {            
            $service_id = "INSERT INTO service (service_type, customer_id) VALUES ('SA-I', :customerID)";
            $statement = $db->prepare($service_id);
            $statement->bindParam(':customerID', $_POST['customerid']);
            $statement->execute();

            $sql = "SELECT max(LAST_INSERT_ID(service_id)) as id From service";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $serviceid = $row['id'];

            $saiService = "INSERT INTO service_type_sai (customer_id, service) VALUES (:id, :serviceid)";
            $statement = $db->prepare($saiService);
            $statement->bindParam(':id', $_POST['customerid']);
            $statement->bindParam(':serviceid', $serviceid);
            $statement->execute();

            $sql1 = "SELECT max(LAST_INSERT_ID(sai_id)) as id From service_type_sai";
            $statement = $db->prepare($sql1);
            $statement->execute();
            $row1 = $statement->fetch();
            $saiid = $row1['id'];
            
            $infoUpdateForm = FileUploadsSAi::uploadFileInfoUpdateForm($file, $_POST['customerid'], $saiid);
            
            $updateService = "UPDATE service_type_sai SET info_update_form = :infoUpdate WHERE sai_id = :id";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':id',$saiid);
            $statement->bindParam(':infoUpdate',$infoUpdateForm);
            $statement->execute();
            
            $sql1 = "SELECT service_count FROM customer_info WHERE id = :id";
            $statement = $db->prepare($sql1);
            $statement->bindParam(':id', $_POST['customerid']);
            $statement->execute();
            $svccount = $statement->fetch();
            
            $newcount = $svccount['service_count'] + 1;
            
            $updateNextService = "UPDATE customer_info SET next_service = 'SA-II', service_count = :servicecount WHERE id = :id";
            $statement = $db->prepare($updateNextService);
            $statement->bindParam(':id',$_POST['customerid']);
            $statement->bindParam(':servicecount',$newcount);
            $statement->execute();

           



         

       

            echo $_POST['serviceType'];
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}

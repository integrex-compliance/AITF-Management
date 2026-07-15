<?php


session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'commonutil.php';
include 'uploadServiceSP.php';

$file = "Image";

if (isset($_POST['dataService']) && !empty($_POST['dataService'])) {
    try {
        if ($_POST['dataColumn'] == "sp_signed_noti") {
            $column = "sp_signed_noti";
            $infospForm = FileUploadsSP::uploadFileSignedNotiSP($file,$_POST['spCustomerId'],$_POST['dataService']);
        }
        if ($_POST['dataColumn'] == "sp_signed_copy") {
            $column = "sp_signed_copy";
            $infospForm = FileUploadsSP::uploadFileSignedCopySP($file,$_POST['spCustomerId'],$_POST['dataService']);
        }
        if ($_POST['dataColumn'] == "sp_audit_report") {
            $column = "sp_audit_report";
            $infospForm = FileUploadsSP::uploadFileAuditReportSP($file,$_POST['spCustomerId'],$_POST['dataService']);
        }
        $spServiceUpdate = "UPDATE service_type_sp SET " . $column . " = :updateFile WHERE sp_id = :serviceid";
        $statement = $db->prepare($spServiceUpdate);
        $statement->bindParam(':updateFile', $infospForm);
        $statement->bindParam(':serviceid', $_POST['dataService']);
        $statement->execute();

        echo $_POST['serviceType'];
    } catch (Exception $ex) {
        echo $ex;
    }
} else {

    if (isset($_POST['spCustomerId']) && !empty($_POST['spCustomerId']) && isset($_FILES ["file"]) && !( $_FILES['file']['size'] == 0 ) && isset($_FILES ["file"] ["name"])) {
        try {
            $signedNotiSP = FileUploadsSP::uploadFileSignedNotiSP($file);
            
            $service_id = "INSERT INTO service (service_type, customer_id) VALUES ('SP', :customerID)";
            $statement = $db->prepare($service_id);
            $statement->bindParam(':customerID', $_POST['spCustomerId']);
            $statement->execute();

            $sql = "SELECT max(LAST_INSERT_ID(service_id)) as id From service";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $serviceid = $row['id'];
            
            $spService = "INSERT INTO service_type_sp (sp_customer, service, sp_signed_noti) VALUES (:id, :serviceid, :signedNotiSP)";
            $statement = $db->prepare($spService);
            $statement->bindParam(':id', $_POST['spCustomerId']);
            $statement->bindParam(':serviceid', $serviceid);
            $statement->bindParam(':signedNotiSP', $signedNotiSP);
            $statement->execute();            








            echo $_POST['serviceType'];
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}

<?php


session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'commonutil.php';
include 'uploadServiceTA.php';

$file = "Image";

if (isset($_POST['dataService']) && !empty($_POST['dataService']) && isset($_FILES ["file"]) && !( $_FILES['file']['size'] == 0 ) && isset($_FILES ["file"] ["name"])) {
    try {
        if ($_POST['dataColumn'] == "signed_notification") {
            $column = "signed_notification";
            $applicationForm = FileUploadsTA::uploadFileSignedNotification($file, $_POST["customerId"], $_POST['dataService']);
        }

        if ($_POST['dataColumn'] == "release_schedule") {
            $column = "release_schedule";
            $applicationForm = FileUploadsTA::uploadFileSchedule($file, $_POST["customerId"], $_POST['dataService']);
        }

        if ($_POST['dataColumn'] == "audit_report") {
            $column = "audit_report";
            $applicationForm = FileUploadsTA::uploadAuditReport($file, $_POST["customerId"], $_POST['dataService']);
        }

        if ($_POST['dataColumn'] == "review_report") {
            $column = "review_report";
            $applicationForm = FileUploadsTA::uploadReviewReport($file, $_POST["customerId"], $_POST['dataService']);
        }

        $caServiceUpdate = "UPDATE service_type_ta SET " . $column . " = :updateFile WHERE ta_id = :serviceid";
        $statement = $db->prepare($caServiceUpdate);
        $statement->bindParam(':updateFile', $applicationForm);
        $statement->bindParam(':serviceid', $_POST['dataService']);
        $statement->execute();

        if ($_POST['dataColumn'] == "audit_report") {
            $taSvcStatus = "UPDATE `service_type_ta` SET `service_status`= 'Completed' WHERE ta_id = :serviceid";
            $statement = $db->prepare($taSvcStatus);
            $statement->bindParam(':serviceid', $_POST['dataService']);
            $statement->execute();
            
            $svcStatus = "UPDATE service SET service_status= 'Completed' WHERE customer_id = '".$_POST['customerId']."' AND service_type = 'TA'";
            $statement = $db->prepare($svcStatus);
            $statement->execute();
        }

        echo $_POST['serviceType'];
    } catch (Exception $ex) {
        
    }
}
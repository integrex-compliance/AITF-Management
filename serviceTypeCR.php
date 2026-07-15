<?php


session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'commonutil.php';
include 'uploadServiceCA.php';

$file = "Image";

if (isset($_POST['dataService']) && !empty($_POST['dataService']) && isset($_FILES ["file"]) && !( $_FILES['file']['size'] == 0 ) && isset($_FILES ["file"] ["name"])) {
    try {
        if ($_POST['dataColumn'] == "application_form") {
            $column = "application_form";
            $applicationForm = FileUploadsCA::uploadFileApplicationForm($file, $_POST["customerid"], $_POST['dataService']);
        }
        if ($_POST['dataColumn'] == "certification_aggrement") {
            $column = "certification_aggrement";
            $applicationForm = FileUploadsCA::uploadFileCertificationAgreement($file, $_POST["saiiCustomerid"], $_POST['dataService']);
        }

        $caServiceUpdate = "UPDATE service_type_cr SET " . $column . " = :updateFile WHERE cr_id = :serviceid";
        $statement = $db->prepare($caServiceUpdate);
        $statement->bindParam(':updateFile', $applicationForm);
        $statement->bindParam(':serviceid', $_POST['dataService']);
        $statement->execute();
        echo $_POST['serviceType'];
    } catch (Exception $ex) {
        
    }
} else {
    if (isset($_POST['customerid']) && !empty($_POST['customerid'])) {
        try {
//        $applicationForm = FileUploadsCA::uploadFileApplicationForm($file);

            $service_id = "INSERT INTO service (service_type, customer_id) VALUES ('CR', :customerID)";
            $statement = $db->prepare($service_id);
            $statement->bindParam(':customerID', $_POST['customerid']);
            $statement->execute();

            $sql = "SELECT max(LAST_INSERT_ID(service_id)) as id From service";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $serviceid = $row['id'];

            $caService = "INSERT INTO service_type_cr (service, customer_id) VALUES (:serviceid,:customerID)";
            $statement = $db->prepare($caService);
            $statement->bindParam(':customerID', $_POST['customerid']);
            $statement->bindParam(':serviceid', $serviceid);
            $statement->execute();

            $sql1 = "SELECT max(LAST_INSERT_ID(cr_id)) as id From service_type_cr";
            $statement = $db->prepare($sql1);
            $statement->execute();
            $row1 = $statement->fetch();
            $crid = $row1['id'];

            $applicationForm = FileUploadsCA::uploadFileApplicationForm($file, $_POST['customerid'], $crid);

            $updateNextService = "UPDATE service_type_cr SET application_form = :appForm WHERE cr_id = :id";
            $statement = $db->prepare($updateNextService);
            $statement->bindParam(':id', $crid);
            $statement->bindParam(':appForm', $applicationForm);
            $statement->execute();

            $updateNextService = "UPDATE customer_info SET next_service = 'CA' WHERE id = :id";
            $statement = $db->prepare($updateNextService);
            $statement->bindParam(':id', $_POST['customerid']);
            $statement->execute();
        } catch (Exception $ex) {
            
        }
    }
}

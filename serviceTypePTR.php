<?php


session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'commonutil.php';
include 'uploadServiceSAi.php';

$file = "Image";

if (isset($_POST['dataService']) && !empty($_POST['dataService']) && isset($_FILES ["file"]) && !( $_FILES['file']['size'] == 0 ) && isset($_FILES ["file"] ["name"])) {
    try {
        if ($_POST['dataColumn'] == "audit_report") {
            $column = "audit_report";
            $inputFile = FileUploadsSAi::uploadFileInfoUpdateForm($file);
        }
        
        $updateSai = "UPDATE service_type_ptr SET " . $column . " = :updatefile  WHERE prt_id = :id ";
        $statement = $db->prepare($updateSai);
        $statement->bindParam(':updatefile', $inputFile);
        $statement->bindParam(':id', $_POST['dataService']);
        $statement->execute();

        echo $_POST['serviceType'];
        
    } catch (Exception $ex) {
        echo $ex;
    }
}

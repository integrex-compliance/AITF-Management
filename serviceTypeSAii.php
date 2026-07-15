<?php



session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'commonutil.php';
include 'uploadServiceSAii.php';

$file = "Image";
if (isset($_POST['dataService']) && !empty($_POST['dataService']) && isset($_FILES ["file"]) && !( $_FILES['file']['size'] == 0 ) && isset($_FILES ["file"] ["name"])) {
    try {
        if ($_POST['dataColumn'] == "update_info_saii") {
            $column = "update_info_saii";
            $infosaiiForm = FileUploadsSAii::uploadFileInfoUpdateSaiiForm($file, $_POST["dataid"], $_POST['dataService']);
        }
        if ($_POST['dataColumn'] == "signed_noti_saii") {
            $column = "signed_noti_saii";
            $infosaiiForm = FileUploadsSAii::uploadFileSignedNotisaii($file, $_POST["dataid"], $_POST['dataService']);
        }
        if ($_POST['dataColumn'] == "signed_copy_saii") {
            $column = "signed_copy_saii";
            $infosaiiForm = FileUploadsSAii::uploadFileSignedCopysaii($file, $_POST["dataid"], $_POST['dataService']);
        }
        if ($_POST['dataColumn'] == "audit_report_saii") {
            $column = "audit_report_saii";
            $infosaiiForm = FileUploadsSAii::uploadFileAuditReportsaii($file, $_POST["dataid"], $_POST['dataService']);
        }
        if ($_POST['dataColumn'] == "auditor_notes_saii") {
            $column = "auditor_notes_saii";
            $infosaiiForm = FileUploadsSAii::uploadFileAuditorNotessaii($file, $_POST["dataid"], $_POST['dataService']);
        }
        if ($_POST['dataColumn'] == "audit_evidences_saii") {
            $column = "audit_evidences_saii";
            $infosaiiForm = FileUploadsSAii::uploadFileAuditEvidencessaii($file, $_POST["dataid"], $_POST['dataService']);
        }
        if ($_POST['dataColumn'] == "customer_review_saii") {
            $column = "customer_review_saii";
            $infosaiiForm = FileUploadsSAii::uploadFileCustomerReviewsaii($file, $_POST["dataid"], $_POST['dataService']);
        }
        if ($_POST['dataColumn'] == "certificate_print") {
            $column = "certificate_print";
            $infosaiiForm = FileUploadsSAii::uploadFileCertifcatePrintsaii($file, $_POST["dataid"], $_POST['dataService']);
        }

        $saiiServiceUpdate = "UPDATE service_type_saii SET " . $column . " = :updateFile WHERE saii_id = :serviceid";
        $statement = $db->prepare($saiiServiceUpdate);
        $statement->bindParam(':updateFile', $infosaiiForm);
        $statement->bindParam(':serviceid', $_POST['dataService']);
        $statement->execute();


        echo $_POST['serviceType'];
    } catch (Exception $ex) {        
    }
} else {
    if (isset($_POST['dataid']) && !empty($_POST['dataid']) && isset($_FILES ["file"]) && !( $_FILES['file']['size'] == 0 ) && isset($_FILES ["file"] ["name"])) {
        try {
            $service_id = "INSERT INTO service (service_type, customer_id) VALUES ('SA-II', :customerID)";
            $statement = $db->prepare($service_id);
            $statement->bindParam(':customerID', $_POST['dataid']);
            $statement->execute();

            $sql = "SELECT max(LAST_INSERT_ID(service_id)) as id From service";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $serviceid = $row['id'];

            $saiiService = "INSERT INTO service_type_saii (customer_id, service) VALUES (:id, :serviceid)";
            $statement = $db->prepare($saiiService);
            $statement->bindParam(':id', $_POST['dataid']);
            $statement->bindParam(':serviceid', $serviceid);
            $statement->execute();
            
            $sql1 = "SELECT max(LAST_INSERT_ID(saii_id)) as id From service_type_saii";
            $statement = $db->prepare($sql1);
            $statement->execute();
            $row1 = $statement->fetch();
            $saiiid = $row1['id'];
            
            $infoUpdateForm = FileUploadsSAii::uploadFileInfoUpdateSaiiForm($file, $_POST["dataid"], $saiiid);
            
            $updateService = "UPDATE service_type_saii SET update_info_saii = :infoUpdateForm WHERE saii_id = :id";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':id',$saiiid);
            $statement->bindParam(':infoUpdateForm', $infoUpdateForm);
            $statement->execute();
            
            $updateNextService = "UPDATE customer_info SET next_service = 'RC' WHERE id = :id";
            $statement = $db->prepare($updateNextService);
            $statement->bindParam(':id',$_POST['dataid']);
            $statement->execute();

          

            echo $_POST['serviceType'];
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}

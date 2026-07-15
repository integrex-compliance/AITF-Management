<?php



session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'commonutil.php';
include 'uploadServiceRC.php';

$file = "Image";

if (isset($_POST['dataService']) && !empty($_POST['dataService'])) {
    try {
//        echo $_POST['customerid']." -> ".$_POST['dataService']." -> ".$_FILES['file']['name'];die;
        
        if ($_POST['dataColumn'] == "rc_info_update") {
            $column = "rc_info_update";
            $inputFile = FileUploadsRC::uploadFileInfoUpdateRCForm($file, $_POST['customerid'], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "rc_application_form") {
            $column = "rc_application_form";
            $inputFile = FileUploadsRC::uploadFileApplicationFormrc($file, $_POST['customerid'], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "rc_certification_aggrement") {
            $column = "rc_certification_aggrement";
            $inputFile = FileUploadsRC::uploadFileCertifyAggrementrc($file, $_POST['customerid'], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "rc_signed_noti") {
            $column = "rc_signed_noti";
            $inputFile = FileUploadsRC::uploadFileSignedNotiRC($file, $_POST['customerid'], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "rc_signed_copy") {
            $column = "rc_signed_copy";
            $inputFile = FileUploadsRC::uploadFileSignedCopyRC($file, $_POST['customerid'], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "rc_audit_report") {
            $column = "rc_audit_report";
            $inputFile = FileUploadsRC::uploadFileAuditReportRC($file, $_POST['customerid'], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "auditor_notes_rc") {
            $column = "auditor_notes_rc";
            $inputFile = FileUploadsRC::uploadFileAuditorNotesRC($file, $_POST['customerid'], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "rc_audit_evidence") {
            $column = "rc_audit_evidence";
            $inputFile = FileUploadsRC::uploadFileAuditEvidenceRC($file, $_POST['customerid'], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "rc_customer_review") {
            $column = "rc_customer_review";
            $inputFile = FileUploadsRC::uploadFileCustomerReviewRC($file, $_POST['customerid'], $_POST['dataService']);
        }
        
        if ($_POST['dataColumn'] == "certificate_print") {
            $column = "certificate_print";
            $inputFile = FileUploadsRC::uploadFileCertificateUploadRC($file, $_POST['customerid'], $_POST['dataService']);
        }
        
        $updateRc = "UPDATE service_type_rc SET ".$column." = :file WHERE rc_id = :id ";
        $statement = $db->prepare($updateRc);
        $statement->bindParam(':file' , $inputFile);
        $statement->bindParam(':id',$_POST['dataService']);
        $statement->execute();
        echo $_POST['serviceType'];
    } catch (Exception $ex) {
        
    }
} else {

    if (isset($_POST['customerid']) && !empty($_POST['customerid'])) {
        try {            
            $infoUpdateRC = FileUploadsRC::uploadFileApplicationFormrc($file);
            
            $service_id = "INSERT INTO service (service_type, customer_id) VALUES ('RC', :customerID)";
            $statement = $db->prepare($service_id);
            $statement->bindParam(':customerID', $_POST['customerid']);
            $statement->execute();

            $sql = "SELECT max(LAST_INSERT_ID(service_id)) as id From service";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $serviceid = $row['id'];

            $rcService = "INSERT INTO service_type_rc (rc_customer, service, rc_application_form) VALUES (:id, :serviceid, :infoUpdateRC)";
            $statement = $db->prepare($rcService);
            $statement->bindParam(':id', $_POST['customerid']);
            $statement->bindParam(':serviceid', $serviceid);
            $statement->bindParam(':infoUpdateRC', $infoUpdateRC);
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

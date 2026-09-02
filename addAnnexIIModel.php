<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
require_once('tcpdf_include.php');
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST['service_Id']) && !empty($_POST['service_Id']) && isset($_POST['customer_Id']) && !empty($_POST['customer_Id'])) {
    try {
//        echo $_POST['auditInpard'];
        $aspectPoint = explode('~', $_POST['audit_aspect_hidden_array']);
//         print_r($aspectPoint); echo '<br>'; die();
        $auditresponse = explode('~', $_POST['audit_response_hidden_array']);
        // print_r($auditresponse); echo '<br>';
        $remark = explode('~', $_POST['remark_hidden_array']);

        //print_r($remark);echo '<br>';
        if (isset($_POST['auditDateII']) && !empty($_POST['auditDateII'])) {
            $appDate = date("Y-m-d", strtotime($_POST['auditDateII']));
        } else {
            $appDate = NULL;
        }
        if (isset($_POST['reviewDateAnnex']) && !empty($_POST['reviewDateAnnex'])) {
            $review_date = date("Y-m-d", strtotime($_POST['reviewDateAnnex']));
        } else {
            $review_date = NULL;
        }

        if (isset($_POST['auditInpard']) && !empty($_POST['auditInpard'])) {
            $auditInpard = $_POST['auditInpard'];
        } else {
            $auditInpard = "";
        }
        if (isset($_POST['acceptedII']) && !empty($_POST['acceptedII'])) {
            $acceptedII = $_POST['acceptedII'];
        } else {
            $acceptedII = "";
        }
        
        $auditOrganisation = $_POST['auditOrganisationII'];
        $ReviewerName = $_POST['ReviewerName'];

        require_once __DIR__ . '/include/reviewer_conflict_helper.php';
        $conflictCheck = aitf_check_service_reviewer_conflict($db, $_POST['service_Id'], $ReviewerName, $_POST['customer_Id'] ?? 0);
        if (!empty($conflictCheck['has_conflict'])) {
            $_SESSION['audit_app_conflict_error'] = $conflictCheck['message'];
            header("Location: customerAccount.php?customerid=" . urlencode($_POST['customer_Id']) . "&reviewer_conflict=1");
            exit;
        }

        $_POST['auditDateII'];
        $leadAuditor = $_POST['leadAuditorII'];
        $referenceNumber = $_POST['referenceNumberII'];
//        echo ':customer'. $_POST['customer_Id'].'auditOrganisation'. $auditOrganisation.':auditDate'. $_POST['auditDateII'].':leadAuditor', $leadAuditor.':referenceNumber', $referenceNumber.':initial', $auditInpard.':remark', $_POST['remark'].':reviewer_comments', $_POST['ReviewerComments'].':review_date', $review_date; die;
        $sql = "INSERT INTO `stage_ii_audit_id`(`ca_service_id`, `customer_id`, `audit_Organisation`, `audit_dates`, `lead_auditor`, `reference_number`, `initial`, `accepted`, `remark_check`, `reviewer_comments`,review_date,ReviewerName) VALUES (:id,:customer,:auditOrganisation,:auditDate,:leadAuditor,:referenceNumber,:initial,:Accepted,:remark,:reviewer_comments,:review_date,:ReviewerName)";
//        $sql = $sql = "INSERT INTO stage_ii_audit_id(ca_service_id, customer_id, audit_Organisation, audit_dates, lead_auditor, reference_number, initial, accepted, remark_check) "
//                . "VALUES (:id, :customer, :auditOrganisation, :auditDate, :leadAuditor, :referenceNumber, :initial, :Accepted, :remark)";
        $statement = $db->prepare($sql);
        $statement->bindParam(':id', $_POST['service_Id']);
        $statement->bindParam(':customer', $_POST['customer_Id']);
        $statement->bindParam(':auditOrganisation', $auditOrganisation);
        $statement->bindParam(':auditDate', $_POST['auditDateII']);
        $statement->bindParam(':leadAuditor', $leadAuditor);
        $statement->bindParam(':referenceNumber', $referenceNumber);
        $statement->bindParam(':initial', $auditInpard);
        $statement->bindParam(':Accepted', $acceptedII);
        $statement->bindParam(':remark', $_POST['remark']);
        $statement->bindParam(':reviewer_comments', $_POST['ReviewerComments']);
        $statement->bindParam(':review_date', $review_date);
        $statement->bindParam(':ReviewerName', $ReviewerName);
        $statement->execute();
        
        $sql = "SELECT max(LAST_INSERT_ID(stage_ii_id)) as id From stage_ii_audit_id";
        $statement = $db->prepare($sql);
        $statement->execute();
        $row = $statement->fetch();
        $stageIIid = $row["id"];
        for ($i = 0; $i < count($aspectPoint); ++$i) {
            if ($aspectPoint[$i] != "") {
                $stageI = "INSERT INTO `stage_ii_audit`(`ca_service_audit_ii`, `ca_service_id_ii`, `audit_aspect_ii`, `audit_response_ii`, `remark_ii`) VALUES (:audit,:serviceid,:auditaspect,:auditresponse,:remark)";
                $statement = $db->prepare($stageI);
                $statement->bindParam(':audit', $stageIIid);
                $statement->bindParam(':serviceid', $_POST['service_Id']);
                $statement->bindParam(':auditaspect', $aspectPoint[$i]);
                $statement->bindParam(':auditresponse', $auditresponse[$i]);
                $statement->bindParam(':remark', $remark[$i]);
                $statement->execute();
            }           
        }
        header("Location: customerAccount.php?customerid=" . $_POST['customer_Id']);
    } catch (Exception $ex) {
        echo $ex;
    }
}

if (isset($_POST['service_Id_Edit']) && !empty($_POST['service_Id_Edit']) && isset($_POST['customer_Id_Edit']) && !empty($_POST['customer_Id_Edit'])) {
    try {
        if (isset($_POST['auditDateII']) && !empty($_POST['auditDateII'])) {
            $appDate = date("Y-m-d", strtotime($_POST['auditDateII']));
        } else {
            $appDate = NULL;
        }
        if (isset($_POST['reviewDateAnnexEdit']) && !empty($_POST['reviewDateAnnexEdit'])) {
            $review_date = date("Y-m-d", strtotime($_POST['reviewDateAnnexEdit']));
        } else {
            $review_date = NULL;
        }
        $service = $_POST['service_Id_Edit'];

        $auditOrganisation = $_POST['auditOrganisationIIEdit'];
        $ReviewerName = $_POST['ReviewerNameEdit'] ?? ($_POST['ReviewerName'] ?? '');

        require_once __DIR__ . '/include/reviewer_conflict_helper.php';
        $conflictCheck = aitf_check_service_reviewer_conflict($db, $service, $ReviewerName, $_POST['customer_Id_Edit'] ?? 0);
        if (!empty($conflictCheck['has_conflict'])) {
            $_SESSION['audit_app_conflict_error'] = $conflictCheck['message'];
            header("Location: customerAccount.php?customerid=" . urlencode($_POST['customer_Id_Edit']) . "&reviewer_conflict=1");
            exit;
        }

        $leadAuditor = $_POST['leadAuditorII'];
        $referenceNumber = $_POST['referenceNumberIIEdit'];
        $id = $_POST['review_report_id'];

        if (isset($_POST['editauditInpard']) && !empty($_POST['editauditInpard'])) {
            $auditInpard = $_POST['editauditInpard'];
        } else {
            $auditInpard = "";
        }
        if (isset($_POST['editacceptedII']) && !empty($_POST['editacceptedII'])) {
            $acceptedII = $_POST['editacceptedII'];
        } else {
            $acceptedII = "";
        }

        $sql = "UPDATE `stage_ii_audit_id` SET `audit_Organisation`=:auditOrganisation,`audit_dates`=:auditDate,`lead_auditor`=:leadAuditor,`reference_number`=:referenceNumber,`initial`=:initial,`accepted`=:Accepted,`remark_check`=:eremar,`reviewer_comments`=:reviewer_comments,review_date=:review_date,ReviewerName=:ReviewerName WHERE stage_ii_id=:id";
        $statement = $db->prepare($sql);
        $statement->bindParam(':id', $id);
        $statement->bindParam(':auditOrganisation', $auditOrganisation);
        $statement->bindParam(':auditDate', $_POST['auditDateIIEdit']);
        $statement->bindParam(':leadAuditor', $leadAuditor);
        $statement->bindParam(':referenceNumber', $referenceNumber);
        $statement->bindParam(':initial', $_POST['editauditInpard']);
        $statement->bindParam(':Accepted', $_POST['editacceptedII']);
        $statement->bindParam(':eremar', $_POST['eremark']);
        $statement->bindParam(':reviewer_comments', $_POST['ReviewerCommentsEdit']);
        $statement->bindParam(':review_date', $review_date);
        $statement->bindParam(':ReviewerName', $ReviewerName);
        
        $statement->execute();

        $sql = "UPDATE `stage_ii_audit_id` SET `audit_Organisation`=:auditOrganisation,`audit_dates`=:auditDate,`reference_number`=:referenceNumber, `ReviewerName`=:ReviewerName,`review_date`=:review_date, `reviewer_comments`=:reviewer_comments WHERE stage_ii_id=:id";
        $statement = $db->prepare($sql);
        $statement->bindParam(':id', $id);
        $statement->bindParam(':auditOrganisation', $_POST['auditOrganisationII']);
        $statement->bindParam(':auditDate', $_POST['auditDateII']);
        $statement->bindParam(':referenceNumber', $_POST['referenceNumberII']);
        $statement->bindParam(':ReviewerName', $_POST['ReviewerNameEdit']);
        $statement->bindParam(':reviewer_comments', $_POST['ReviewerComments']);
        $statement->bindParam(':review_date', $review_date);
        
        if ($statement->execute()) {
            echo "Reviewer name updated successfully!";
        } else {
            echo "Error updating reviewer name: " . $statement->errorInfo()[2];
        }
        $auditresponse = explode('~', $_POST['audit_response_hidden_edit']);
        $remark = explode('~', $_POST['remark_hidden_edit']);
        $iddata = explode('~', $_POST['audit_id_update']);

        for ($i = 0; $i < count($iddata); ++$i) {

            if ($iddata[$i] != "") {
                $stageI = "UPDATE `stage_ii_audit` SET `audit_response_ii`=:auditresponse,`remark_ii`=:remark  WHERE id_ii = :stageIid";
                $statement = $db->prepare($stageI);
                $statement->bindParam(':stageIid', $iddata[$i]);
                $statement->bindParam(':auditresponse', $auditresponse[$i]);
                $statement->bindParam(':remark', $remark[$i]);
                $statement->execute();
            }
        }
        header("Location:customerAccount.php?customerid=" . $_POST['customer_Id_Edit']);
    } catch (Exception $ex) {
        echo $ex;
    }
}

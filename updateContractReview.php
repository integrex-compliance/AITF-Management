<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST['contract-id']) && !empty($_POST['contract-id'])) {
    try {
        $contractid = "";
        $service = $_POST['edit-service-id'];
        $customer = $_POST['edit-customer-id'];
        $contractid = $_POST['contract-id'];
        $conclusion=$_POST['conclusionEdit'];
        $reviewer=$_POST['reviewerEdit'];
        if (isset($_POST['dateOfReviewEdit']) && !empty($_POST['dateOfReviewEdit'])) {
            $dateOfReview = date("Y-m-d", strtotime($_POST['dateOfReviewEdit']));
        } else {
            $dateOfReview = 'null';
        }
        
       
        $contractUpdate= "UPDATE contract_id SET review_date=:review_date,conclusion=:conclusion,reviewer=:reviewer WHERE contractid=:id";
        $statement = $db->prepare($contractUpdate);
        $statement->bindParam(':id', $contractid);
        $statement->bindParam(':review_date', $dateOfReview);
        $statement->bindParam(':conclusion', $conclusion);
        $statement->bindParam(':reviewer', $reviewer);
        $statement->execute();
        
        $checkPoint = explode('~', $_POST['contract-chk-point-hidden-edit']);
        $headerName = explode('~', $_POST['contract-header-hidden-edit']);
        $checkSr = explode('~', $_POST['contract-sr-hidden-edit']);
        $reqState = explode('~', $_POST['req-state-acpt-hidden-edit']);
        $reqNotstate = explode('~', $_POST['req-notstate-acpt-hidden-edit']);
        $reqNotacpt = explode('~', $_POST['req-state-notacpt-hidden-edit']);
        $reqNotstateNotacpt = explode('~', $_POST['req-notstate-notacpt-hidden-edit']);
        $remarkAction = explode('~', $_POST['remark-action-hidden-edit']);

        $contractDlt = "DELETE FROM contract_review WHERE ca_service_contract = :contractid ";
        $statement = $db->prepare($contractDlt);
        $statement->bindParam(':contractid', $contractid);
//        $statement->bindParam(':service',$service);
        $statement->execute();


        for ($i = 0; $i < count($checkPoint); $i++) {
            if ($checkPoint[$i] != "") {
                $contract = "INSERT INTO contract_review (ca_service_contract, ca_service, contract_header, sr_no, contract_chk_point, req_stated, req_notstated, req_stat_notacpt, req_notstat_notacpt, remark) "
                        . "VALUES (:contract, :serviceid, :header, :srNo, :chkPoint, :req_state, :req_notstate, :req_notacpt, :req_notstate_notacpt, :remark_action)";
                ;
                $statement = $db->prepare($contract);
                $statement->bindParam(':contract', $contractid);
                $statement->bindParam(':serviceid', $service);
                $statement->bindParam(':header', $headerName[$i]);
                $statement->bindParam(':srNo', $checkSr[$i]);
                $statement->bindParam(':chkPoint', $checkPoint[$i]);
                $statement->bindParam(':req_state', $reqState[$i]);
                $statement->bindParam(':req_notstate', $reqNotstate[$i]);
                $statement->bindParam(':req_notacpt', $reqNotacpt[$i]);
                $statement->bindParam(':req_notstate_notacpt', $reqNotstateNotacpt[$i]);
                $statement->bindParam(':remark_action', $remarkAction[$i]);
                $statement->execute();
            }
        }
//        echo "success";
        header("Location: customerAccount.php?customerid=" . $customer);
    } catch (Exception $ex) {
        echo $ex;
    }
}
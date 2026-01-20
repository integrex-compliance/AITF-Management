<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST['service-id']) && !empty($_POST['service-id']) && isset($_POST['customer-id']) && !empty($_POST['customer-id'])) {
    try {
        echo "in";
        if (isset($_POST['addContractReview'])) {
            $service = $_POST['service-id'];
            $checkPoint = explode('~', $_POST['contract-chk-point-hidden']);
            $headerName = explode('~', $_POST['contract-header-hidden']);
            $checkSr = explode('~', $_POST['contract-sr-hidden']);
            $reqState = explode('~', $_POST['req-state-acpt-hidden']);
            $reqNotstate = explode('~', $_POST['req-notstate-acpt-hidden']);
            $reqNotacpt = explode('~', $_POST['req-state-notacpt-hidden']);
            $reqNotstateNotacpt = explode('~', $_POST['req-notstate-notacpt-hidden']);
            $remarkAction = explode('~', $_POST['remark-action-hidden']);
            $conclusion=$_POST['conclusion'];
            $reviewer=$_POST['reviewer'];
            
            if (isset($_POST['dateOfReview']) && !empty($_POST['dateOfReview'])) {
                $dateOfReview = date("Y-m-d", strtotime($_POST['dateOfReview']));
            } else {
                $dateOfReview = 'null';
            }

            $contractid = "INSERT INTO contract_id (ca_service_id, customer_id,review_date,conclusion,reviewer)"
                    . " VALUES (:id, :customer,:review_date,:conclusion,:reviewer)";
            $statement = $db->prepare($contractid);
            $statement->bindParam(':id', $service);
            $statement->bindParam(':customer', $_POST['customer-id']);
            $statement->bindParam(':review_date',$dateOfReview);
            $statement->bindParam(':conclusion',$conclusion);
            $statement->bindParam(':reviewer',$reviewer);
            $statement->execute();

            $sql = "SELECT max(LAST_INSERT_ID(contractid)) as id From contract_id";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $contractid = $row['id'];


            for ($i = 0; $i < count($checkPoint); ++$i) {
                if ($checkPoint[$i] != "") {
                    $contract = "INSERT INTO contract_review (ca_service_contract, ca_service, contract_header, sr_no, contract_chk_point, req_stated, req_notstated, req_stat_notacpt, req_notstat_notacpt, remark) "
                            . "VALUES (:contract, :serviceid, :header, :srNo, :chkPoint, :req_state, :req_notstate, :req_notacpt, :req_notstate_notacpt, :remark_action)";                    
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
            header("Location: customerAccount.php?customerid=" . $_POST['customer-id']);
        }
    } catch (Exception $ex) {
        echo $ex;
    }
}
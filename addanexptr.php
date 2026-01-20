<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
require_once('tcpdf_include.php');

if (isset($_POST["report-id"]) && !empty($_POST["report-id"])) {
    try {
        if (isset($_POST['auditDateptr']) && !empty($_POST['auditDateptr'])) {
            $appDate = date("Y-m-d", strtotime($_POST['auditDateptr']));
        } else {
            $appDate = NULL;
        }
        if (isset($_POST['reviewDateptr']) && !empty($_POST['reviewDateptr'])) {
            $reviewDate = date("Y-m-d", strtotime($_POST['reviewDateptr']));
        } else {
            $reviewDate = NULL;
        }

        if (isset($_POST['acceptancePtr']) && !empty($_POST['acceptancePtr'])) {
            $acceptance = $_POST['acceptancePtr'];
        } else {
            $acceptance = "";
        }
        $service = $_POST['serviceId-ptr'];

        $aspectPoint = explode('~', $_POST['audit-aspect-hidden_array-ptr']);
        $auditresponse = explode('~', $_POST['audit-response-hidden_array-ptr']);
        $remark = explode('~', $_POST['remark-hidden_array-ptr']);

        $auditOrganisation = $_POST['auditOrganisationName'];
        $leadAuditor = $_POST['leadAuditorPtr'];
        $referenceNumber = $_POST['referenceNumberPtr'];
        $reviewer_comments = $_POST['ReviewerComments1ptr'];

        $id = $_POST['report-id'];

        $sql = "UPDATE stage_i_audit_id SET auditOrganisation=:auditOrganisation,auditDate=:auditDate,leadAuditor=:leadAuditor,referenceNumber=:referenceNumber,accepted=:Accepted,reviewer_comments=:reviewer_comments,review_date=:review_date WHERE stage_i_id=:id";
        $statement = $db->prepare($sql);
        $statement->bindParam(':id', $id);
        $statement->bindParam(':auditOrganisation', $auditOrganisation);
        $statement->bindParam(':auditDate', $_POST['auditDateptr']);
        $statement->bindParam(':leadAuditor', $leadAuditor);
        $statement->bindParam(':referenceNumber', $referenceNumber);
        $statement->bindParam(':Accepted', $_POST['acceptancePtr']);
        $statement->bindParam(':reviewer_comments', $reviewer_comments);
        $statement->bindParam(':review_date', $reviewDate);
        $statement->execute();
        
        $sql1 = "DELETE FROM `stage_i_audit` WHERE `ca_service_audit` = :id";
        $statement = $db->prepare($sql1);
        $statement->bindParam(':id', $id);
        $statement->execute();
        
        for ($i = 0; $i < count($aspectPoint); ++$i) {
            if ($aspectPoint[$i] != "") {
                $stageI = "INSERT INTO stage_i_audit(ca_service_audit, ca_service_id,audit_aspect, audit_response,remark) VALUES (:audit,:serviceid,:auditaspect,:auditresponse,:remark)";
                $statement = $db->prepare($stageI);
                $statement->bindParam(':audit', $id);
                $statement->bindParam(':serviceid', $service);
                $statement->bindParam(':auditaspect', $aspectPoint[$i]);
                $statement->bindParam(':auditresponse', $auditresponse[$i]);
                $statement->bindParam(':remark', $remark[$i]);
                $statement->execute();
            }
        }
        header("Location: customerAccount.php?customerid=" . $_POST['customerId-ptr']);
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else {
    try {

        if (isset($_POST['auditDateptr']) && !empty($_POST['auditDateptr'])) {
            $appDate = date("Y-m-d", strtotime($_POST['auditDateptr']));
        } else {
            $appDate = NULL;
        }
        if (isset($_POST['reviewDateptr']) && !empty($_POST['reviewDateptr'])) {
            $reviewDate = date("Y-m-d", strtotime($_POST['reviewDateptr']));
        } else {
            $reviewDate = NULL;
        }

        if (isset($_POST['acceptancePtr']) && !empty($_POST['acceptancePtr'])) {
            $acceptance = $_POST['acceptancePtr'];
        } else {
            $acceptance = "";
        }
        $service = $_POST['serviceId-ptr'];

        $aspectPoint = explode('~', $_POST['audit-aspect-hidden_array-ptr']);
        $auditresponse = explode('~', $_POST['audit-response-hidden_array-ptr']);
        $remark = explode('~', $_POST['remark-hidden_array-ptr']);

        $auditOrganisation = $_POST['auditOrganisationName'];
        $leadAuditor = $_POST['leadAuditorPtr'];
        $referenceNumber = $_POST['referenceNumberPtr'];
        $reviewer_comments = $_POST['ReviewerComments1ptr'];

        $sql = "INSERT INTO stage_i_audit_id(ca_service_id,customer_id,auditOrganisation, auditDate,leadAuditor, referenceNumber,accepted,reviewer_comments,review_date) VALUES (:id,:customer,:auditOrganisation,:auditDate,:leadAuditor,:referenceNumber,:accepted,:reviewer_comments,:reviewDate)";
        $statement = $db->prepare($sql);
        $statement->bindParam(':id', $service);
        $statement->bindParam(':customer', $_POST['customerId-ptr']);
        $statement->bindParam(':auditOrganisation', $auditOrganisation);
        $statement->bindParam(':auditDate', $_POST['auditDateptr']);
        $statement->bindParam(':leadAuditor', $leadAuditor);
        $statement->bindParam(':referenceNumber', $referenceNumber);
        $statement->bindParam(':reviewer_comments', $reviewer_comments);
        $statement->bindParam(':reviewDate', $reviewDate);
        $statement->bindParam(':accepted', $_POST['acceptancePtr']);
        $statement->execute();

        $sql = "SELECT max(LAST_INSERT_ID(stage_i_id)) as id From stage_i_audit_id";
        $statement = $db->prepare($sql);
        $statement->execute();
        $row = $statement->fetch();
        $stageIid = $row['id'];

        for ($i = 0; $i < count($aspectPoint); ++$i) {
            if ($aspectPoint[$i] != "") {
                $stageI = "INSERT INTO stage_i_audit(ca_service_audit, ca_service_id,audit_aspect, audit_response,remark) VALUES (:audit,:serviceid,:auditaspect,:auditresponse,:remark)";
                $statement = $db->prepare($stageI);
                $statement->bindParam(':audit', $stageIid);
                $statement->bindParam(':serviceid', $service);
                $statement->bindParam(':auditaspect', $aspectPoint[$i]);
                $statement->bindParam(':auditresponse', $auditresponse[$i]);
                $statement->bindParam(':remark', $remark[$i]);
                $statement->execute();
            }
        }
        header("Location: customerAccount.php?customerid=" . $_POST['customerId-ptr']);
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}
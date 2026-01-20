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
if (isset($_POST['control-format2-id']) && !empty($_POST['control-format2-id'])) {
    try {
        $customer = $_POST['control-format2-customer'];
        $cfid = $_POST['control-format2-id'];

        if (isset($_POST['controlFormat2CertDate']) && !empty($_POST['controlFormat2CertDate'])) {
            $controldate = date("Y-m-d", strtotime($_POST['controlFormat2CertDate']));
        } else {
            $controldate = NULL;
        }
        if (isset($_POST['reviewDateControl2']) && !empty($_POST['reviewDateControl2'])) {
            $review_date = date("Y-m-d", strtotime($_POST['reviewDateControl2']));
        } else {
            $review_date = NULL;
        }

        $iddata = explode('~', $_POST['control_format2_idhideden']);
        $question = explode('~', $_POST['risk-question-hidden2']);
        $comment = explode('~', $_POST['comment-hidden2']);
        $risk = explode('~', $_POST['risk-hidden2']);
        $riskDegree = explode('~', $_POST['risk-degree-hidden2']);
        $controls = explode('~', $_POST['controls-hidden2']);
        $srno = explode('~', $_POST['srno-hidden2']);
        $title = explode('~', $_POST['title-hidden2']);

        $cfidedit = "UPDATE control_formatii_id SET control2_client = :client, control2_ref_no = :refno, control2_address= :address, control2_date = :date, remarks = :remarks, username = :username, review_date = :review_date WHERE  control2_id = :id ";
        $statement = $db->prepare($cfidedit);
        $statement->bindParam(':client', $_POST['controlFormat2ClientName']);
        $statement->bindParam(':refno', $_POST['controlFormat2RefNo']);
        $statement->bindParam(':address', $_POST['controlFormat2Address']);
        $statement->bindParam(':remarks', $_POST['cf2remark']);
        $statement->bindParam(':username', $_POST['reviewer']);
        $statement->bindParam(':date', $controldate);
        $statement->bindParam(':review_date', $review_date);
        $statement->bindParam(':id', $cfid);
        $statement->execute();

        $dltcf2 = "DELETE FROM control_formatii WHERE control2_id = :id";
        $statement = $db->prepare($dltcf2);
        $statement->bindParam(':id', $cfid);
        $statement->execute();

        $riskDegreeVal = "";
        for ($i = 0; $i < count($iddata); ++$i) {
            if ($iddata[$i] != "") {
                if ($risk[$i] == "Yes") {
                    $riskDegreeVal = $riskDegree[$i];
                } else {
                    $riskDegreeVal = "";
                }
                $controlFormat = "INSERT INTO control_formatii(control2_id, caservice, control2_sr, control2_title, control2_question, comments2, risks2, risk_degree2, controls2) VALUES (:id,:service,:srno,:title,:question,:comment,:risk,:riskDegree,:controls)";
                $statement = $db->prepare($controlFormat);
                $statement->bindParam(':id', $cfid);
                $statement->bindParam(':service', $_POST['control-format2-service']);
                $statement->bindParam(':srno', $srno[$i]);
                $statement->bindParam(':title', $title[$i]);
                $statement->bindParam(':question', $question[$i]);
                $statement->bindParam(':comment', $comment[$i]);
                $statement->bindParam(':risk', $risk[$i]);
                $statement->bindParam(':riskDegree', $riskDegreeVal);
                $statement->bindParam(':controls', $controls[$i]);
                $statement->execute();
            }
        }
        header("Location: customerAccount.php?customerid=" . $customer);
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_POST['control-format2-service']) && !empty($_POST['control-format2-service'])) {
        try {

            $service = $_POST['control-format2-service'];
            $customer = $_POST['control-format2-customer'];
            if (isset($_POST['controlFormat2CertDate']) && !empty($_POST['controlFormat2CertDate'])) {
                $controldate = date("Y-m-d", strtotime($_POST['controlFormat2CertDate']));
            } else {
                $controldate = NULL;
            }
            if (isset($_POST['reviewDateControl2']) && !empty($_POST['reviewDateControl2'])) {
                $review_date = date("Y-m-d", strtotime($_POST['reviewDateControl2']));
            } else {
                $review_date = NULL;
            }
            //$controldate = date("Y-m-d", strtotime($_POST['controlFormat2CertDate']));
            $question = explode('~', $_POST['risk-question-hidden2']);
            $comment = explode('~', $_POST['comment-hidden2']);
            $risk = explode('~', $_POST['risk-hidden2']);
            $riskDegree = explode('~', $_POST['risk-degree-hidden2']);
            $controls = explode('~', $_POST['controls-hidden2']);
            $srno = explode('~', $_POST['srno-hidden2']);
            $title = explode('~', $_POST['title-hidden2']);

            $service = "SELECT * FROM service WHERE service_id = :id";
            $statement = $db->prepare($service);
            $statement->bindParam(':id', $_POST['cf-service-id']);
            $statement->execute();
            $serviceInfo = $statement->fetch();
//        echo $_POST['cf-service-id'];
            if (isset($_POST['addControlFormat2'])) {

                $format2id = "INSERT INTO control_formatii_id (customer_id, caservice_id, control2_client, control2_ref_no, control2_address,control2_date,remarks,username,review_date) "
                        . " VALUES (:customer,:service,:client,:refno,:address,:date,:remarks,:username,:review_date)";
                $statement = $db->prepare($format2id);
                $statement->bindParam(':customer', $customer);
                $statement->bindParam(':service', $_POST['control-format2-service']);
                $statement->bindParam(':client', $_POST['controlFormat2ClientName']);
                $statement->bindParam(':refno', $_POST['controlFormat2RefNo']);
                $statement->bindParam(':address', $_POST['controlFormat2Address']);
                $statement->bindParam(':remarks', $_POST['cf2remark']);
                $statement->bindParam(':username', $_POST['reviewer']);
                $statement->bindParam(':review_date', $review_date);
                $statement->bindParam(':date', $controldate);
                $statement->execute();

                $sql = "SELECT max(LAST_INSERT_ID(control2_id)) as id From control_formatii_id";
                $statement = $db->prepare($sql);
                $statement->execute();
                $row = $statement->fetch();
                $controlid = $row['id'];
                $riskDegreeVal = "";

                for ($i = 0; $i < count($question); ++$i) {
                    if ($question[$i] != "") {
                        if ($risk[$i] == "Yes") {
                            $riskDegreeVal = $riskDegree[$i];
                        } else {
                            $riskDegreeVal = "";
                        }
                        $controlFormat = "INSERT INTO control_formatii(control2_id, caservice, control2_sr, control2_title, control2_question, comments2, risks2, risk_degree2, controls2) VALUES (:id,:service,:srno,:title,:question,:comment,:risk,:riskDegree,:controls)";
                        $statement = $db->prepare($controlFormat);
                        $statement->bindParam(':id', $controlid);
                        $statement->bindParam(':service', $_POST['control-format2-service']);
                        $statement->bindParam(':srno', $srno[$i]);
                        $statement->bindParam(':title', $title[$i]);
                        $statement->bindParam(':question', $question[$i]);
                        $statement->bindParam(':comment', $comment[$i]);
                        $statement->bindParam(':risk', $risk[$i]);
                        $statement->bindParam(':riskDegree', $riskDegreeVal);
                        $statement->bindParam(':controls', $controls[$i]);
                        $statement->execute();
                    }
                }

                if ($serviceInfo['service_type'] == "CA" || $serviceInfo['service_type'] == "RC") {
                    $updateService = "UPDATE service SET service_done = 'DONE' WHERE service_id = :id";
                    $statement = $db->prepare($updateService);
                    $statement->bindParam(':id', $_POST['cf-service-id']);
                    $statement->execute();
//                echo $serviceInfo['service_type'];
                }
                header("Location: customerAccount.php?customerid=" . $customer);
            }
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}
  
   

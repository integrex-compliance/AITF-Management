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

if (isset($_POST['survay-id']) && !empty($_POST['survay-id'])) {
    try {
        $survatid = $_POST['survay-id'];
        $service = $_POST['survey-service-id'];
        $customer = $_POST['survey-customer-id'];
        $leadAuditor = explode('~', $_POST['leadAuditorHidden']);
        $leadResult = explode('~', $_POST['leadResultHidden']);
        $recommendedAction = explode('~', $_POST['recommendedActionHidden']);

        if (isset($_POST['surveyDate']) && !empty($_POST['surveyDate'])) {
            $survayDate = date("Y-m-d", strtotime($_POST['surveyDate']));
        } else {
            $survayDate = NULL;
        }

        if (isset($_POST['survayAuditDates']) && !empty($_POST['survayAuditDates'])) {
            $auditDate = $_POST['survayAuditDates'];
        } else {
            $auditDate = NULL;
        }
        if (isset($_POST['reviewDateSurvey']) && !empty($_POST['reviewDateSurvey'])) {
            $review_date = date("Y-m-d", strtotime($_POST['reviewDateSurvey']));
        } else {
            $review_date = NULL;
        }
        $initialCert = '';
        $reCert = '';
        $extension = '';
        $auditScope = '';
        $auditSite = '';
        $surveillance = '';

        if (isset($_POST['initial-cert'])) {
            $initialCert = 'true';
        }
        if (isset($_POST['re-cert'])) {
            $reCert = 'true';
        }
        if (isset($_POST['extension'])) {
            $extension = 'true';
        }
        if (isset($_POST['audit_scope'])) {
            $auditScope = 'true';
        }
        if (isset($_POST['audit-site'])) {
            $auditSite = 'true';
        }
        if (isset($_POST['surveillance'])) {
            $surveillance = 'true';
        }

        $resultString = "";
        $resultSatisfy = "";
        $aggregateResult = "";
        $aggregateString = "";

        $auditQuality = $_POST['auditingQuality'];
        $auditPercent = floor(($auditQuality / 50) * 100);
//        echo $auditPercent;

        if ($auditPercent >= "90") {
            $resultString = "Excellent";
        } else if ($auditPercent >= "80" && $auditPercent <= "89") {
            $resultString = "Very Satisfactory";
        } else if ($auditPercent >= "70" && $auditPercent <= "79") {
            $resultString = "Satisfied";
        } else if ($auditPercent >= "60" && $auditPercent <= "69") {
            $resultString = "Partially Satisfied";
        } else if ($auditPercent <= "60") {
            $resultString = "Dissatisfied";
        }
//         echo $resultString;
        $satisfyRating = $_POST['satisfactionRating'];
        $satisfyPercent = floor(($satisfyRating / 35) * 100);
//        echo $satisfyPercent;

        if ($satisfyPercent >= "90") {
            $resultSatisfy = "Excellent";
        } else if ($satisfyPercent >= "80" && $satisfyPercent <= "89") {
            $resultSatisfy = "Very Satisfactory";
        } else if ($satisfyPercent >= "70" && $satisfyPercent <= "79") {
            $resultSatisfy = "Satisfied";
        } else if ($satisfyPercent >= "60" && $satisfyPercent <= "69") {
            $resultSatisfy = "Partially Satisfied";
        } else if ($satisfyPercent <= "60") {
            $resultSatisfy = "Dissatisfied";
        }

        $aggregateResult = floor((($auditPercent + $satisfyPercent) / 200) * 100);

        if ($aggregateResult >= "90") {
            $aggregateString = "Excellent";
        } else if ($aggregateResult >= "80" && $aggregateResult <= "89") {
            $aggregateString = "Very Satisfactory";
        } else if ($aggregateResult >= "70" && $aggregateResult <= "79") {
            $aggregateString = "Satisfied";
        } else if ($aggregateResult >= "60" && $aggregateResult <= "69") {
            $aggregateString = "Partially Satisfied";
        } else if ($aggregateResult <= "60") {
            $aggregateString = "Dissatisfied";
        }

//                echo $aggregateResult;
//        echo $resultSatisfy;
//        $survayidedit = 'UPDATE customer_survey_id SET auditer_org = :auditororg, survey_date = :survaydate,'
//                . 'audit_conducted_date = :auditdate, initial_cert = :initialcert,'
//                . 'auditing_quality = :quality, auditing_percent = :percent'
//                . 'auditing_result = :result, audit_ranking_good = :ranking, audit_ranking_bad = :notranking,'
//                . 'individual_ranking = :auditorranking, mgmt_satisfaction = :satisfactionrating,'
//                . 'mgmt_percent = :satisfactionpercent, mgmt_result = :satisfactionresult, mgmt_ranking_good = :satisfactionranking,'
//                . 'mgmt_ranking_bad = :notsatisfactionranking WHERE survey_id = :id';
        $survayidedit = "UPDATE customer_survey_id SET auditer_org = :auditororg, survey_date = :survaydate, audit_conducted_date = :auditdate, initial_cert = :initialcert, auditing_quality = :quality, auditing_percent = :percent, auditing_result = :result, audit_ranking_good = :ranking, audit_ranking_bad = :notranking, individual_ranking = :auditorranking, mgmt_satisfaction = :satisfactionrating, mgmt_percent = :satisfactionpercent, mgmt_result = :satisfactionresult, mgmt_ranking_good = :satisfactionranking, mgmt_ranking_bad = :notsatisfactionranking, aggregate = :aggregate, aggregate_result = :aggregateString,review_date = :review_date WHERE survey_id = :id";

        $statement = $db->prepare($survayidedit);
        $statement->bindParam(':id', $survatid);
        $statement->bindParam(':auditororg', $_POST['auditOrg']);
        $statement->bindParam(':survaydate', $survayDate);
        $statement->bindParam(':auditdate', $auditDate);
        $statement->bindParam(':initialcert', $_POST['initial-cert']);
        $statement->bindParam(':quality', $_POST['auditingQuality']);
        $statement->bindParam(':percent', $auditPercent);
        $statement->bindParam(':result', $resultString);
        $statement->bindParam(':ranking', $_POST['partiallyRanking']);
        $statement->bindParam(':notranking', $_POST['notExcellentRanking']);
        $statement->bindParam(':auditorranking', $_POST['auditorRanking']);
        $statement->bindParam(':satisfactionrating', $_POST['satisfactionRating']);
        $statement->bindParam(':satisfactionpercent', $satisfyPercent);
        $statement->bindParam(':satisfactionresult', $resultSatisfy);
        $statement->bindParam(':satisfactionranking', $_POST['satisfactionRanking']);
        $statement->bindParam(':notsatisfactionranking', $_POST['notSatisfactionRanking']);
        $statement->bindParam(':aggregate', $aggregateResult);
        $statement->bindParam(':aggregateString', $aggregateString);
        $statement->bindParam(':review_date', $review_date);
        $statement->execute();

        $deleteSurvay = 'DELETE FROM customer_survey WHERE survayid = :survayid';
        $statement = $db->prepare($deleteSurvay);
        $statement->bindParam(':survayid', $survatid);
        $statement->execute();
//        print_r($leadAuditor);
        for ($i = 0; $i < count($leadAuditor); ++$i) {
            if ($leadAuditor[$i] != "") {
                $marks = "";
                if ($leadResult[$i] == "Excellent")
                    $marks = 5;
                else if ($leadResult[$i] == "Very Satisfactory")
                    $marks = 4;
                else if ($leadResult[$i] == "Satisfied")
                    $marks = 3;
                else if ($leadResult[$i] == "Partially Satisfied")
                    $marks = 2;
                else if ($leadResult[$i] == "Dissatisfied")
                    $marks = 1;
                $servayDetails = "INSERT INTO customer_survey (survayid, ca_service_id, lead_auditor, result, marks, action) VALUES (:id,:service,:auditor,:result,:marks,:action)";
                $statement = $db->prepare($servayDetails);
                $statement->bindParam(':id', $survatid);
                $statement->bindParam(':service', $service);
                $statement->bindParam(':auditor', $leadAuditor[$i]);
                $statement->bindParam(':result', $leadResult[$i]);
                $statement->bindParam(':marks', $marks);
                $statement->bindParam(':action', $recommendedAction[$i]);
                $statement->execute();
            }
        }
        header("Location:customerAccount.php?customerid=" . $customer);
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_POST['survey-service-id']) && !empty($_POST['survey-service-id']) && isset($_POST['survey-customer-id']) && !empty($_POST['survey-customer-id'])) {
        try {
            if (isset($_POST['addCustomerSurvey'])) {

                $service = $_POST['survey-service-id'];
                $customer = $_POST['survey-customer-id'];
                $leadAuditor = explode('~', $_POST['leadAuditorHidden']);
                $leadResult = explode('~', $_POST['leadResultHidden']);
                $recommendedAction = explode('~', $_POST['recommendedActionHidden']);

                if (isset($_POST['surveyDate']) && !empty($_POST['surveyDate'])) {
                    $survayDate = date("Y-m-d", strtotime($_POST['surveyDate']));
                } else {
                    $survayDate = NULL;
                }

                if (isset($_POST['survayAuditDates']) && !empty($_POST['survayAuditDates'])) {
                    $auditDate = $_POST['survayAuditDates'];
                } else {
                    $auditDate = NULL;
                }
                if (isset($_POST['reviewDateSurvey']) && !empty($_POST['reviewDateSurvey'])) {
                    $review_date = date("Y-m-d", strtotime($_POST['reviewDateSurvey']));
                } else {
                    $review_date = NULL;
                }


                $resultString = "";
                $resultSatisfy = "";
                $aggregateResult = "";
                $aggregateString = "";

                $auditQuality = $_POST['auditingQuality'];
                $auditPercent = floor(($auditQuality / 50) * 100);
//                echo $auditPercent;

                if ($auditPercent >= "90") {
                    $resultString = "Excellent";
                } else if ($auditPercent >= "80" && $auditPercent <= "89") {
                    $resultString = "Very Satisfactory";
                } else if ($auditPercent >= "70" && $auditPercent <= "79") {
                    $resultString = "Satisfied";
                } else if ($auditPercent >= "60" && $auditPercent <= "69") {
                    $resultString = "Partially Satisfied";
                } else if ($auditPercent <= "60") {
                    $resultString = "Dissatisfied";
                }

                $satisfyRating = $_POST['satisfactionRating'];
                $satisfyPercent = floor(($satisfyRating / 35) * 100);
//                echo $satisfyPercent;

                if ($satisfyPercent >= "90") {
                    $resultSatisfy = "Excellent";
                } else if ($satisfyPercent >= "80" && $satisfyPercent <= "89") {
                    $resultSatisfy = "Very Satisfactory";
                } else if ($satisfyPercent >= "70" && $satisfyPercent <= "79") {
                    $resultSatisfy = "Satisfied";
                } else if ($satisfyPercent >= "60" && $satisfyPercent <= "69") {
                    $resultSatisfy = "Partially Satisfied";
                } else if ($satisfyPercent <= "60") {
                    $resultSatisfy = "Dissatisfied";
                }

                $aggregateResult = floor((($auditPercent + $satisfyPercent) / 200) * 100);

                if ($aggregateResult >= "90") {
                    $aggregateString = "Excellent";
                } else if ($aggregateResult >= "80" && $aggregateResult <= "89") {
                    $aggregateString = "Very Satisfactory";
                } else if ($aggregateResult >= "70" && $aggregateResult <= "79") {
                    $aggregateString = "Satisfied";
                } else if ($aggregateResult >= "60" && $aggregateResult <= "69") {
                    $aggregateString = "Partially Satisfied";
                } else if ($aggregateResult <= "60") {
                    $aggregateString = "Dissatisfied";
                }

                $initialCert = "";

                if (isset($_POST['initial-cert'])) {
                    $initialCert = $_POST['initial-cert'];
                }

                $uniservice = "SELECT * FROM service WHERE service_id = :id";
                $statement = $db->prepare($uniservice);
                $statement->bindParam(':id', $_POST['cs-service-id']);
                $statement->execute();
                $serviceInfo = $statement->fetch();
//                echo $_POST['cs-service-id'];
                //$survayDate = date("Y-m-d", strtotime($_POST['surveyDate']));
                //$auditDate = date("Y-m-d", strtotime($_POST['survayAuditDates']));

                $customerSurveyid = 'INSERT INTO customer_survey_id(ca_serviceid, customer_ca, auditer_org, survey_date, '
                        . 'audit_conducted_date, initial_cert, auditing_quality, auditing_percent, auditing_result, audit_ranking_good, '
                        . 'audit_ranking_bad, individual_ranking, mgmt_satisfaction, mgmt_percent, mgmt_result, '
                        . 'mgmt_ranking_good, mgmt_ranking_bad, aggregate, aggregate_result,review_date) '
                        . 'VALUES (:service, :customer, :auditororg, :survaydate, :auditdate, :initialcert, '
                        . ':quality, :percent, :result, :ranking, :notranking, :auditorranking, :satisfactionrating, :satisfactionpercent,'
                        . ' :satisfactionresult, :satisfactionranking, :notsatisfactionranking, :aggregate, :aggregateString,:review_date)';
                $statement = $db->prepare($customerSurveyid);
                $statement->bindParam(':service', $service);
                $statement->bindParam(':customer', $customer);
                $statement->bindParam(':auditororg', $_POST['auditOrg']);
                $statement->bindParam(':survaydate', $survayDate);
                $statement->bindParam(':auditdate', $auditDate);
                $statement->bindParam(':initialcert', $initialCert);
                $statement->bindParam(':quality', $_POST['auditingQuality']);
                $statement->bindParam(':percent', $auditPercent);
                $statement->bindParam(':result', $resultString);
                $statement->bindParam(':ranking', $_POST['partiallyRanking']);
                $statement->bindParam(':notranking', $_POST['notExcellentRanking']);
                $statement->bindParam(':auditorranking', $_POST['auditorRanking']);
                $statement->bindParam(':satisfactionrating', $_POST['satisfactionRating']);
                $statement->bindParam(':satisfactionpercent', $satisfyPercent);
                $statement->bindParam(':satisfactionresult', $resultSatisfy);
                $statement->bindParam(':satisfactionranking', $_POST['satisfactionRanking']);
                $statement->bindParam(':notsatisfactionranking', $_POST['notSatisfactionRanking']);
                $statement->bindParam(':aggregate', $aggregateResult);
                $statement->bindParam(':aggregateString', $aggregateString);
                $statement->bindParam(':review_date', $review_date);
                $statement->execute();

                $sql = "SELECT max(LAST_INSERT_ID(survey_id)) as id From customer_survey_id";
                $statement = $db->prepare($sql);
                $statement->execute();
                $row = $statement->fetch();
                $survaysql = $row['id'];
//print_r($leadAuditor);
                for ($i = 0; $i < count($leadAuditor); ++$i) {
                    if ($leadAuditor[$i] != "") {
                        $marks = "";
                        if ($leadResult[$i] == "Excellent")
                            $marks = 5;
                        else if ($leadResult[$i] == "Very Satisfactory")
                            $marks = 4;
                        else if ($leadResult[$i] == "Satisfied")
                            $marks = 3;
                        else if ($leadResult[$i] == "Partially Satisfied")
                            $marks = 2;
                        else if ($leadResult[$i] == "Dissatisfied")
                            $marks = 1;
                        $servayDetails = "INSERT INTO customer_survey (survayid, ca_service_id, lead_auditor, result, marks, action) VALUES (:id,:service,:auditor,:result,:marks,:action)";
                        $statement = $db->prepare($servayDetails);
                        $statement->bindParam(':id', $survaysql);
                        $statement->bindParam(':service', $service);
                        $statement->bindParam(':auditor', $leadAuditor[$i]);
                        $statement->bindParam(':result', $leadResult[$i]);
                        $statement->bindParam(':marks', $marks);
                        $statement->bindParam(':action', $recommendedAction[$i]);
                        $statement->execute();
                    }
                }

                if ($serviceInfo['service_type'] == "SA-I" || $serviceInfo['service_type'] == "SA-II") {
                    $updateService = "UPDATE service SET service_done = 'DONE' WHERE service_id = :id";
                    $statement = $db->prepare($updateService);
                    $statement->bindParam(':id', $_POST['cs-service-id']);
                    $statement->execute();
//                    echo $serviceInfo['service_type'];
                }
                header("Location:customerAccount.php?customerid=" . $customer);
            }
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}
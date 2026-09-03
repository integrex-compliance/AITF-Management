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

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {

    if (isset($_POST['addReviewReport'])) {

        if (isset($_POST['serviceId']) && !empty($_POST['serviceId']) && isset($_POST['customerId']) && !empty($_POST['customerId'])) {
            try {
                $rmrk = "";
                if (isset($_POST['eremarkanx1']) && !empty($_POST['eremarkanx1'])) {
                    $rmrk = $_POST['eremarkanx1'];
                } else {
                    $rmrk = NULL;
                }

                if (isset($_POST['auditDate']) && !empty($_POST['auditDate'])) {
                    $appDate = date("Y-m-d", strtotime($_POST['auditDate']));
                } else {
                    $appDate = NULL;
                }
                if (isset($_POST['reviewDate']) && !empty($_POST['reviewDate'])) {
                    $reviewDate = date("Y-m-d", strtotime($_POST['reviewDate']));
                } else {
                    $reviewDate = date("Y-m-d");
                }

                if (isset($_POST['acceptance']) && !empty($_POST['acceptance'])) {
                    $acceptance = $_POST['acceptance'];
                } else {
                    $acceptance = "";
                }
                $service = $_POST['serviceId'];

                $aspectPoint = explode('~', $_POST['audit-aspect-hidden_array']);
                $auditresponse = explode('~', $_POST['audit-response-hidden_array']);
                $remark = explode('~', $_POST['remark-hidden_array']);

                $auditOrganisation = $_POST['auditOrganisation'] ?? '';
                $leadAuditor = $_POST['leadAuditor'] ?? '';
                $referenceNumber = isset($_POST['referenceNumber']) ? trim((string)$_POST['referenceNumber']) : '';
                if ($referenceNumber === '') {
                    $stmtRef = $db->prepare("SELECT aggrement_no FROM certification_aggrement WHERE service = :service AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1");
                    $stmtRef->execute([':service' => $service]);
                    $refRow = $stmtRef->fetch(PDO::FETCH_ASSOC);
                    if ($refRow && !empty($refRow['aggrement_no'])) {
                        $referenceNumber = trim($refRow['aggrement_no']);
                    } else {
                        $stmtRef = $db->prepare("SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :customer AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1");
                        $stmtRef->execute([':customer' => $_POST['customerId']]);
                        $refRow = $stmtRef->fetch(PDO::FETCH_ASSOC);
                        if ($refRow && !empty($refRow['aggrement_no'])) {
                            $referenceNumber = trim($refRow['aggrement_no']);
                        } else {
                            $stmtRef = $db->prepare("SELECT unique_id FROM customer_info WHERE id = :customer AND unique_id IS NOT NULL AND unique_id != ''");
                            $stmtRef->execute([':customer' => $_POST['customerId']]);
                            $refRow = $stmtRef->fetch(PDO::FETCH_ASSOC);
                            if ($refRow && !empty($refRow['unique_id'])) {
                                $referenceNumber = trim($refRow['unique_id']);
                            } else {
                                $referenceNumber = 'AITF/' . date('M-y') . '/' . date('Y');
                            }
                        }
                    }
                }
                $reviewer_comments = $_POST['ReviewerComments1'] ?? '';
                $reviewer_name = $_POST['ReviewerName1'] ?? '';
                $assessmentStandard = $_POST['assessmentStandard'] ?? '';
                $auditDateVal = $_POST['auditDate'] ?? '';

                require_once __DIR__ . '/include/reviewer_conflict_helper.php';
                $conflictCheck = aitf_check_service_reviewer_conflict($db, $service, $reviewer_name, $_POST['customerId'] ?? 0);
                if (!empty($conflictCheck['has_conflict'])) {
                    $_SESSION['audit_app_conflict_error'] = $conflictCheck['message'];
                    header("Location: customerAccount.php?customerid=" . urlencode($_POST['customerId']) . "&reviewer_conflict=1");
                    exit;
                }

                $sql = "INSERT INTO stage_i_audit_id(ca_service_id,customer_id,auditOrganisation, auditDate,leadAuditor, referenceNumber,accepted,reviewer_comments,review_date, remark,reviewer_name, certificationStandered) VALUES (:id,:customer,:auditOrganisation,:auditDate,:leadAuditor,:referenceNumber,:accepted,:reviewer_comments,:reviewDate, :remark, :reviewer_name, :certificationStandered)";
                $statement = $db->prepare($sql);
                $statement->bindParam(':id', $service);
                $statement->bindParam(':customer', $_POST['customerId']);
                $statement->bindParam(':auditOrganisation', $auditOrganisation);
                $statement->bindParam(':auditDate', $auditDateVal);
                $statement->bindParam(':leadAuditor', $leadAuditor);
                $statement->bindParam(':referenceNumber', $referenceNumber);
                $statement->bindParam(':reviewer_comments', $reviewer_comments);
                $statement->bindParam(':reviewer_name', $reviewer_name);
                $statement->bindParam(':reviewDate', $reviewDate);
                $statement->bindParam(':accepted', $acceptance);
                $statement->bindParam(':remark', $rmrk);
                $statement->bindParam(':certificationStandered', $assessmentStandard);
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
                header("Location: customerAccount.php?customerid=" . $_POST['customerId']);
            } catch (Exception $ex) {
                echo $ex;
            }
        }
    }

    if (isset($_POST['updateReviewReport'])) {

        if (isset($_POST['serviceIdEdit']) && !empty($_POST['serviceIdEdit']) && isset($_POST['customerIdEdit']) && !empty($_POST['customerIdEdit'])) {
            try {

                if (isset($_POST['auditDate']) && !empty($_POST['auditDate'])) {
                    $appDate = date("Y-m-d", strtotime($_POST['auditDate']));
                } else {
                    $appDate = NULL;
                }
                $auditOrganisation = $_POST['auditOrganisation'] ?? '';
                $leadAuditor = $_POST['leadAuditor'] ?? '';
                $referenceNumber = isset($_POST['referenceNumber']) ? trim((string)$_POST['referenceNumber']) : '';
                $reviewer_comments = $_POST['ReviewerCommentsEdit1'] ?? '';
                $reviewer_name = $_POST['reviewerNameEdit'] ?? '';
                $acceptedEdit = isset($_POST['acceptedEdit']) && !empty($_POST['acceptedEdit']) ? $_POST['acceptedEdit'] : '';
                $assessmentStandard = $_POST['assessmentStandard'] ?? '';
                $auditDateVal = $_POST['auditDate'] ?? '';

                $id = $_POST['review-report-id'];
                if ($referenceNumber === '') {
                    $stmtRef = $db->prepare("SELECT referenceNumber FROM stage_i_audit_id WHERE stage_i_id = :id");
                    $stmtRef->execute([':id' => $id]);
                    $refRow = $stmtRef->fetch(PDO::FETCH_ASSOC);
                    if ($refRow && !empty($refRow['referenceNumber'])) {
                        $referenceNumber = trim($refRow['referenceNumber']);
                    } else {
                        $stmtRef = $db->prepare("SELECT aggrement_no FROM certification_aggrement WHERE (service = :service OR customer_id = :customer) AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1");
                        $stmtRef->execute([':service' => $_POST['serviceIdEdit'], ':customer' => $_POST['customerIdEdit']]);
                        $refRow = $stmtRef->fetch(PDO::FETCH_ASSOC);
                        if ($refRow && !empty($refRow['aggrement_no'])) {
                            $referenceNumber = trim($refRow['aggrement_no']);
                        } else {
                            $referenceNumber = 'AITF/' . date('M-y') . '/' . date('Y');
                        }
                    }
                }

                require_once __DIR__ . '/include/reviewer_conflict_helper.php';
                $conflictCheck = aitf_check_service_reviewer_conflict($db, $_POST['serviceIdEdit'], $reviewer_name, $_POST['customerIdEdit'] ?? 0);
                if (!empty($conflictCheck['has_conflict'])) {
                    $_SESSION['audit_app_conflict_error'] = $conflictCheck['message'];
                    header("Location: customerAccount.php?customerid=" . urlencode($_POST['customerIdEdit']) . "&reviewer_conflict=1");
                    exit;
                }

                if (isset($_POST['reviewDateEdit']) && !empty($_POST['reviewDateEdit'])) {
                    $reviewDate = date("Y-m-d", strtotime($_POST['reviewDateEdit']));
                } else {
                    $reviewDate = date("Y-m-d");
                }

                //$aspectPoint = explode('~', $_POST['audit-aspect-hidden_edit']);
                $auditresponse = explode('~', $_POST['audit-response-hidden_edit']);
                $remark = explode('~', $_POST['remark-hidden_edit']);
                $iddata = explode('~', $_POST['audit-id-update']);
                //print_r($auditresponse);
                // $formNo = $_POST['formNoEdit'];
                //$rev = $_POST['revEdit'];
                //$issue = $_POST['isueEdit'];
                $rmrk = "";
                if (isset($_POST['eremarkanx1edit']) && !empty($_POST['eremarkanx1edit'])) {
                    $rmrk = $_POST['eremarkanx1edit'];
                } else {
                    $rmrk = NULL;
                }
                //echo $id;
                //echo $auditOrganisation. $leadAuditor. $referenceNumber.$formNo.$rev.$issue.$appDate.$revdate.$isueDate;

                $sql = "UPDATE stage_i_audit_id SET auditOrganisation=:auditOrganisation,auditDate=:auditDate,leadAuditor=:leadAuditor,referenceNumber=:referenceNumber,accepted=:Accepted,reviewer_comments=:reviewer_comments,reviewer_name=:reviewer_name,review_date=:review_date, remark = :remark, certificationStandered = :certificationStandered WHERE stage_i_id=:id";
                $statement = $db->prepare($sql);
                $statement->bindParam(':id', $id);
                $statement->bindParam(':auditOrganisation', $auditOrganisation);
                $statement->bindParam(':auditDate', $auditDateVal);
                $statement->bindParam(':leadAuditor', $leadAuditor);
                $statement->bindParam(':referenceNumber', $referenceNumber);
                $statement->bindParam(':Accepted', $acceptedEdit);
                $statement->bindParam(':reviewer_comments', $reviewer_comments);
                $statement->bindParam(':reviewer_name', $reviewer_name);
                $statement->bindParam(':review_date', $reviewDate);
                $statement->bindParam(':remark', $rmrk);
                $statement->bindParam(':certificationStandered', $assessmentStandard);
                $statement->execute();

                for ($i = 0; $i < count($iddata); ++$i) {
                    if ($iddata[$i] != "") {
                        $stageI = "UPDATE stage_i_audit SET audit_response = :auditresponse,remark =:remark WHERE id = :stageIid";
                        $statement = $db->prepare($stageI);
                        $statement->bindParam(':stageIid', $iddata[$i]);
                        $statement->bindParam(':auditresponse', $auditresponse[$i]);
                        $statement->bindParam(':remark', $remark[$i]);
                        $statement->execute();
                    }
                }
                header("Location: customerAccount.php?customerid=" . $_POST['customerIdEdit']);
            } catch (Exception $ex) {
                echo $ex;
            }
        }
    }
}
 
 

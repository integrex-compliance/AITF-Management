<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

require_once __DIR__ . '/include/reviewer_conflict_helper.php';

// Debug: Log what we received
file_put_contents('debug_assessment.log', "POST Data: " . print_r($_POST, true) . "\n\n", FILE_APPEND);

if (isset($_POST['assessmentId']) && !empty($_POST['assessmentId'])) {
    file_put_contents('debug_assessment.log', "UPDATE MODE - Assessment ID: " . $_POST['assessmentId'] . "\n", FILE_APPEND);
//    echo $_POST['assessmentId']." update "; die;
    try {
        $certBody = "";
        $iafcode = "";
        $validity = NULL;
        $renuval = "";
        $scopeOpt = "";
        $codeAudit = "";
        // Process certification fields - always save if provided
        if (isset($_POST['assessmentCertBody']) && !empty($_POST['assessmentCertBody'])) {
            $certBody = $_POST['assessmentCertBody'];
        }
        if (isset($_POST['assessmentIafCode']) && !empty($_POST['assessmentIafCode'])) {
            $iafcode = $_POST['assessmentIafCode'];
        }
        if (isset($_POST['assessmentCertValidity']) && !empty($_POST['assessmentCertValidity'])) {
            $validity = date("Y-m-d", strtotime($_POST['assessmentCertValidity']));
        }
        if (isset($_POST['assessmentRenewal']) && !empty($_POST['assessmentRenewal'])) {
            $renuval = $_POST['assessmentRenewal'];
        }
        if (isset($_POST['modification']) && !empty($_POST['modification'])) {
            $scopeOpt = is_array($_POST['modification']) ? implode(',', $_POST['modification']) : $_POST['modification'];
        }
        if (isset($_POST['leadAuditor']) && !empty($_POST['leadAuditor'])) {
            $codeAudit = $_POST['leadAuditor'];
        } else {
            $codeAudit = "";
        }

        if (isset($_POST["assessmentImplementationDate"]) && !empty($_POST["assessmentImplementationDate"])) {
            $dateval = date("Y-m-d", strtotime($_POST['assessmentImplementationDate']));
        } else {
            $dateval = NULL;
        }
        $updateAssessment = "UPDATE assessment_planning SET app_client_name = :appClient, app_client_ref = :refNo, "
                . "location = :assessmentLocation, business = :assessmentBusiness, cert_standard = :certRequirement, "
                . "iaf_code = :assessmentPlanIafCode, nace_code = :assessmentNace,nace_class = :assessmentNaceClass, defined_cert_req = :assessmentCertReq, "
                . "app_standard_diff = :assessmentAppStandard, special_aspects = :assessmentSpecialAspects, "
                . "certification_body = :assessmentCertBody, aif_scope = :assessmentIafCode, cert_validatity = :assessmentCertValidity,"
                . "renewal_delay = :assessmentRenewal, modification = :scope, qms_scope = :assessmentQmsScope,"
                . "assessment_personnel = :assessmentPersonnel, need_technical_expert = :assessmentTechnicalExpert,"
                . "availability = :assessmentAvailability, logistic_arrangement = :assessmentLogisticArrangement,"
                . "conflict_interest = :assessmentInterestConflict, qms_implementation_date = :assessmentImplementationDate,"
                . "assessment_comment = :assessmentComment, client_instructions = :audit_response, no_of_employes = :assessmentEffectEmp,"
                . "auditor_man_days = :assessmentManDays, lead_auditor = :assessmentLeadAuditor,"
                . "lead_auditor_code = :leadAuditor, technical_expert = :assessmentTechExpert, auditors = :assessmentAuditors,"
                . "code_auditor = :codeAuditor, document_review_date = :assessmentDicRevPropDate, "
                . "stage_2_audit_date = :assessmentStageIIPropDate, stage_1_date = :assessmentStageIPropDate,"
                . "if_recertification_date = :assessmentRecertPropDate, recommended_reason = :assessmentStageIReason,"
                . "date = :assessmentDate, verified_by = :assessmentVerifiedBy WHERE assessment_id = :assessmentId";

        $statement = $db->prepare($updateAssessment);
        $statement->bindParam(':appClient', $_POST['appClient']);
        $statement->bindParam(':refNo', $_POST['refNo']);
        $statement->bindParam(':assessmentLocation', $_POST['assessmentLocation']);
        $statement->bindParam(':assessmentBusiness', $_POST['assessmentBusiness']);
        $statement->bindParam(':certRequirement', $_POST['certRequirement']);
        $statement->bindParam(':assessmentPlanIafCode', $_POST['assessmentPlaningIafCode']);
        $statement->bindParam(':assessmentNace', $_POST['assessmentNace']);
        $statement->bindParam(':assessmentNaceClass', $_POST['assessmentNaceClass']);
        $statement->bindParam(':assessmentCertReq', $_POST['assessmentCertReq']);
        $statement->bindParam(':assessmentAppStandard', $_POST['assessmentAppStandard']);
        $statement->bindParam(':assessmentSpecialAspects', $_POST['assessmentSpecialAspects']);
        $statement->bindParam(':assessmentCertBody', $certBody);
        $statement->bindParam(':assessmentIafCode', $iafcode);
        $statement->bindParam(':assessmentCertValidity', $validity);
        $statement->bindParam(':assessmentRenewal', $renuval);
        $statement->bindParam(':scope', $scopeOpt);
        $statement->bindParam(':assessmentQmsScope', $_POST['assessmentQmsScope']);
        $statement->bindParam(':assessmentPersonnel', $_POST['assessmentPersonnel']);
        $statement->bindParam(':assessmentTechnicalExpert', $_POST['assessmentTechnicalExpert']);
        $statement->bindParam(':assessmentAvailability', $_POST['assessmentAvailability']);
        $statement->bindParam(':assessmentLogisticArrangement', $_POST['assessmentLogisticArrangement']);
        $statement->bindParam(':assessmentInterestConflict', $_POST['assessmentInterestConflict']);
        $statement->bindParam(':assessmentImplementationDate', $dateval);
        $statement->bindParam(':assessmentComment', $_POST['assessmentComment']);
        $statement->bindParam(':audit_response', $_POST['audit_response']);
        $statement->bindParam(':assessmentEffectEmp', $_POST['assessmentEffectEmp']);
        $statement->bindParam(':assessmentManDays', $_POST['assessmentManDays']);
        $statement->bindParam(':assessmentLeadAuditor', $_POST['assessmentLeadAuditor']);
        $statement->bindParam(':leadAuditor', $codeAudit);
        $statement->bindParam(':assessmentTechExpert', $_POST['assessmentTechExpert']);
        // Use AuditorHidden if available, otherwise fall back to assessmentAuditors
        $auditorsValue = !empty($_POST['AuditorHidden']) ? $_POST['AuditorHidden'] : $_POST['assessmentAuditors'];
        $statement->bindParam(':assessmentAuditors', $auditorsValue);
        $statement->bindParam(':codeAuditor', $_POST['AuditorChkHidden']);
//            $statement->bindParam(':assessmentDocumentReview', $_POST['assessmentDocumentReview']);
        $statement->bindParam(':assessmentDicRevPropDate', $_POST['assessmentDicRevPropDate']);
//            $statement->bindParam(':assessmentStageIIAudit', $_POST['assessmentStageIIAudit']);
        $statement->bindParam(':assessmentStageIIPropDate', $_POST['assessmentStageIIPropDate']);
//            $statement->bindParam(':assessmentStageI', $_POST['assessmentStageI']);
        $statement->bindParam(':assessmentStageIPropDate', $_POST['assessmentStageIPropDate']);
//            $statement->bindParam(':assessmentRecertification', $_POST['assessmentRecertification']);
        $statement->bindParam(':assessmentRecertPropDate', $_POST['assessmentRecertPropDate']);
        $statement->bindParam(':assessmentStageIReason', $_POST['assessmentStageIReason']);
        $statement->bindParam(':assessmentDate', date("Y-m-d", strtotime($_POST['assessmentDate'])));
        $statement->bindParam(':assessmentVerifiedBy', $_POST['assessmentVerifiedBy']);
        $statement->bindParam(':assessmentId', $_POST['assessmentId']);
        $statement->execute();
        file_put_contents('debug_assessment.log', "UPDATE SUCCESS - Redirecting\n", FILE_APPEND);

        header("Location:customerAccount.php?customerid=" . $_POST['assessmentUserId']);
        exit();
    } catch (Exception $ex) {
        file_put_contents('debug_assessment.log', "UPDATE ERROR: " . $ex->getMessage() . "\n", FILE_APPEND);
        echo "Error: " . $ex->getMessage();
        die();
    }
} else {
//    echo "insert"; die;
    file_put_contents('debug_assessment.log', "INSERT MODE\n", FILE_APPEND);
    if (isset($_POST['assessmentUserId']) && !empty($_POST['assessmentUserId']) && isset($_POST['assessmentServiceId']) && !empty($_POST['assessmentServiceId'])) {
        file_put_contents('debug_assessment.log', "INSERT MODE - User ID: " . $_POST['assessmentUserId'] . " Service ID: " . $_POST['assessmentServiceId'] . "\n", FILE_APPEND);
        try {
            $certBody = "";
            $iafcode = "";
            $validity = NULL;
            $renuval = "";
            $scopeOpt = "";
            $codeAudit = "";
            // Process certification fields - always save if provided
            if (isset($_POST['assessmentCertBody']) && !empty($_POST['assessmentCertBody'])) {
                $certBody = $_POST['assessmentCertBody'];
            }
            if (isset($_POST['assessmentIafCode']) && !empty($_POST['assessmentIafCode'])) {
                $iafcode = $_POST['assessmentIafCode'];
            }
            if (isset($_POST['assessmentCertValidity']) && !empty($_POST['assessmentCertValidity'])) {
                $validity = date("Y-m-d", strtotime($_POST['assessmentCertValidity']));
            }
            if (isset($_POST['assessmentRenewal']) && !empty($_POST['assessmentRenewal'])) {
                $renuval = $_POST['assessmentRenewal'];
            }
            if (isset($_POST['modification']) && !empty($_POST['modification'])) {
                $scopeOpt = is_array($_POST['modification']) ? implode(',', $_POST['modification']) : $_POST['modification'];
            }
            if (isset($_POST['leadAuditor']) && !empty($_POST['leadAuditor'])) {
                $codeAudit = $_POST['leadAuditor'];
            } else {
                $codeAudit = "";
            }

            if (isset($_POST["assessmentImplementationDate"]) && !empty($_POST["assessmentImplementationDate"])) {
                $dateval = date("Y-m-d", strtotime($_POST['assessmentImplementationDate']));
            } else {
                $dateval = NULL;
            }

            $assessmentPlanning = "INSERT INTO assessment_planning (service_id,customer_id, app_client_name,app_client_ref, "
                    . "location, business, cert_standard, iaf_code, nace_code, nace_class, defined_cert_req, app_standard_diff, special_aspects,"
                    . "certification_body, aif_scope, cert_validatity, renewal_delay, modification, qms_scope, assessment_personnel, "
                    . "need_technical_expert, availability, logistic_arrangement, conflict_interest, qms_implementation_date, assessment_comment, client_instructions,"
                    . "no_of_employes, auditor_man_days, lead_auditor, lead_auditor_code, technical_expert, auditors, code_auditor,"
                    . "document_review_date, stage_2_audit_date, stage_1_date, if_recertification_date, recommended_reason, date, verified_by)"
                    . "VALUES (:service,:customer,:appClient,:refNo,:assessmentLocation,:assessmentBusiness,:certRequirement,"
                    . ":assessmentPlanIafCode,:assessmentNace,:assessmentNaceClass,:assessmentCertReq,:assessmentAppStandard,:assessmentSpecialAspects,"
                    . ":assessmentCertBody,:assessmentIafCode,:assessmentCertValidity,:assessmentRenewal,:scope,:assessmentQmsScope,"
                    . ":assessmentPersonnel,:assessmentTechnicalExpert,:assessmentAvailability,:assessmentLogisticArrangement,"
                    . ":assessmentInterestConflict,:assessmentImplementationDate,:assessmentComment,:audit_response,:assessmentEffectEmp,:assessmentManDays,"
                    . ":assessmentLeadAuditor,:leadAuditor,:assessmentTechExpert,:assessmentAuditors,:codeAuditor,"
                    . ":assessmentDicRevPropDate,:assessmentStageIIPropDate,:assessmentStageIPropDate,"
                    . ":assessmentRecertPropDate,:assessmentStageIReason,:assessmentDate,:assessmentVerifiedBy)";
            $statement = $db->prepare($assessmentPlanning);
            $statement->bindParam(':service', $_POST['assessmentServiceId']);
            $statement->bindParam(':customer', $_POST['assessmentUserId']);
            $statement->bindParam(':appClient', $_POST['appClient']);
            $statement->bindParam(':refNo', $_POST['refNo']);
            $statement->bindParam(':assessmentLocation', $_POST['assessmentLocation']);
            $statement->bindParam(':assessmentBusiness', $_POST['assessmentBusiness']);
            $statement->bindParam(':certRequirement', $_POST['certRequirement']);
            $statement->bindParam(':assessmentPlanIafCode', $_POST['assessmentPlaningIafCode']);
            $statement->bindParam(':assessmentNace', $_POST['assessmentNace']);
            $statement->bindParam(':assessmentNaceClass', $_POST['assessmentNaceClass']);
            $statement->bindParam(':assessmentCertReq', $_POST['assessmentCertReq']);
            $statement->bindParam(':assessmentAppStandard', $_POST['assessmentAppStandard']);
            $statement->bindParam(':assessmentSpecialAspects', $_POST['assessmentSpecialAspects']);
            $statement->bindParam(':assessmentCertBody', $certBody);
            $statement->bindParam(':assessmentIafCode', $iafcode);
            $statement->bindParam(':assessmentCertValidity', $validity);
            $statement->bindParam(':assessmentRenewal', $renuval);
            $statement->bindParam(':scope', $scopeOpt);
            $statement->bindParam(':assessmentQmsScope', $_POST['assessmentQmsScope']);
            $statement->bindParam(':assessmentPersonnel', $_POST['assessmentPersonnel']);
            $statement->bindParam(':assessmentTechnicalExpert', $_POST['assessmentTechnicalExpert']);
            $statement->bindParam(':assessmentAvailability', $_POST['assessmentAvailability']);
            $statement->bindParam(':assessmentLogisticArrangement', $_POST['assessmentLogisticArrangement']);
            $statement->bindParam(':assessmentInterestConflict', $_POST['assessmentInterestConflict']);
            $statement->bindParam(':assessmentImplementationDate', $dateval);
            $statement->bindParam(':assessmentComment', $_POST['assessmentComment']);
            $statement->bindParam(':audit_response', $_POST['audit_response']);
            $statement->bindParam(':assessmentEffectEmp', $_POST['assessmentEffectEmp']);
            $statement->bindParam(':assessmentManDays', $_POST['assessmentManDays']);
            $statement->bindParam(':assessmentLeadAuditor', $_POST['assessmentLeadAuditor']);
            $statement->bindParam(':leadAuditor', $codeAudit);
            $statement->bindParam(':assessmentTechExpert', $_POST['assessmentTechExpert']);
            // Use AuditorHidden if available, otherwise fall back to assessmentAuditors
            $auditorsValueInsert = !empty($_POST['AuditorHidden']) ? $_POST['AuditorHidden'] : $_POST['assessmentAuditors'];
            $statement->bindParam(':assessmentAuditors', $auditorsValueInsert);
            $statement->bindParam(':codeAuditor', $_POST['AuditorChkHidden']);
//        $statement->bindParam(':assessmentDocumentReview', $_POST['assessmentDocumentReview']);
            $statement->bindParam(':assessmentDicRevPropDate', $_POST['assessmentDicRevPropDate']);
//        $statement->bindParam(':assessmentStageIIAudit', $_POST['assessmentStageIIAudit']);
            $statement->bindParam(':assessmentStageIIPropDate', $_POST['assessmentStageIIPropDate']);
//        $statement->bindParam(':assessmentStageI', $_POST['assessmentStageI']);
            $statement->bindParam(':assessmentStageIPropDate', $_POST['assessmentStageIPropDate']);
//        $statement->bindParam(':assessmentRecertification', $_POST['assessmentRecertification']);
            $statement->bindParam(':assessmentRecertPropDate', $_POST['assessmentRecertPropDate']);
            $statement->bindParam(':assessmentStageIReason', $_POST['assessmentStageIReason']);
            $statement->bindParam(':assessmentDate', date("Y-m-d", strtotime($_POST['assessmentDate'])));
            $statement->bindParam(':assessmentVerifiedBy', $_POST['assessmentVerifiedBy']);
            $statement->execute();
            file_put_contents('debug_assessment.log', "INSERT SUCCESS - Redirecting\n", FILE_APPEND);

            header("Location:customerAccount.php?customerid=" . $_POST['assessmentUserId']);
            exit();
        } catch (Exception $ex) {
            file_put_contents('debug_assessment.log', "INSERT ERROR: " . $ex->getMessage() . "\n", FILE_APPEND);
            echo "Error: " . $ex->getMessage();
            die();
        }
    } else {
        file_put_contents('debug_assessment.log', "INSERT MODE - Missing assessmentUserId or assessmentServiceId\n", FILE_APPEND);
        echo "Error: Missing required parameters (User ID or Service ID)";
        die();
    }
}

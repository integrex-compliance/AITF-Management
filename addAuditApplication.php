<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

function parseAuditApplicationDate($value)
{
    if (!isset($value)) {
        return null;
    }

    $rawValue = trim($value);
    if ($rawValue === '' || strcasecmp($rawValue, 'null') === 0) {
        return null;
    }

    $formats = ['d.m.Y', 'd-m-Y', 'd/m/Y', 'Y-m-d'];
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $rawValue);
        if ($dt instanceof DateTime && $dt->format($format) === $rawValue) {
            return $dt;
        }
    }

    $timestamp = strtotime($rawValue);
    if ($timestamp !== false) {
        return new DateTime(date('Y-m-d', $timestamp));
    }

    return null;
}

function redirectAuditApplicationError($message)
{
    $_SESSION['audit_error'] = $message;

    $customerId = isset($_POST['customerId']) ? $_POST['customerId'] : '';
    $target = 'customerAccount.php';
    if ($customerId !== '') {
        $target .= '?customerid=' . urlencode($customerId);
    }

    header('Location: ' . $target);
    exit;
}

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {

    if (isset($_POST['addApplication'])) {

        $applicationDateObj = parseAuditApplicationDate(isset($_POST['applicationDate']) ? $_POST['applicationDate'] : null);
        $receivedDateObj = parseAuditApplicationDate(isset($_POST['applicationRcvDate']) ? $_POST['applicationRcvDate'] : null);

        if (!$applicationDateObj) {
            redirectAuditApplicationError('Application Date is required and must be a valid date.');
        }

        if (!$receivedDateObj) {
            redirectAuditApplicationError('Application Received Date is required and must be a valid date.');
        }

        if ($receivedDateObj <= $applicationDateObj) {
            redirectAuditApplicationError('Application Received Date must be greater than Application Date.');
        }

        $_POST['applicationDate'] = $applicationDateObj->format('Y-m-d');
        $_POST['applicationRcvDate'] = $receivedDateObj->format('Y-m-d');
        
        if (isset($_POST['auditId']) && !empty($_POST['auditId'])) {
            try {

                if (isset($_POST['applicationDate']) && !empty($_POST['applicationDate'])) {
                    $appDate = date("Y-m-d", strtotime($_POST['applicationDate']));
                } else {
                    $appDate = NULL;
                }

                if (isset($_POST['revDate']) && !empty($_POST['revDate'])) {
                    $revdate = date("Y-m-d", strtotime($_POST['revDate']));
                } else {
                    $revdate = NULL;
                }

                if (isset($_POST['isueDate']) && !empty($_POST['isueDate'])) {
                    $isueDate = date("Y-m-d", strtotime($_POST['isueDate']));
                } else {
                    $isueDate = NULL;
                }
                if (isset($_POST['review_date']) && !empty($_POST['review_date'])) {
                    $review_date = date("Y-m-d", strtotime($_POST['review_date']));
                } else {
                    $review_date = NULL;
                }

                $updateAudit = "UPDATE audit_application SET application_date = :app_date, org_name = :org_name, legal_status = :legal_status, industury_classfication = :industry_classify, legal_group = :legal_part, n_location = :audit_location, n_employee = :employees, n_shift = :shifts, product = :product, process = :processes, certification = :certification, accereditation_requirement = :accreditation, consultancy_services = :consultancy,"
                        . " standard_clauses = :exclusion_clauses, technical_area = :technical_area, legal_obligation = :legal_obligations, conflict_interest = :conflict_interest, statutory_requirement = :statutory_requirement, customer_requirement = :customer_requirement, org_involved = :organization_service, other_requirement = :other_requirement, assessment_certification = :assessment_certification, adequate_resource = :adequate_resource, "
                        . "product_service = :product_service, scope = :scope, competent_team = :competent_team, standard_clause = :standard_clause, risk_statutory = :risk_statutory, risk_conflict = :risk_conflict, conclusion = :conclusion, multisite_eligibility = :multisite, assessment_standard = :assessment, aif_code = :aif_code, review_accredition = :review_accreditation, proposed_scope = :proposed_scope, proposed_month = :proposed_month, "
                        . "man_day_req = :man_days_required, reducing_factor = :designing_development, justification_reducing = :justification_reduction, additive_factor = :additive_factor, actual_effective = :actual_effective, technical_req = :technical_expert, travel_logistic = :travel_logistics, refundable_amount = :application_amount, audit_stage_i = :documentation_review, audit_stage_iI = :certification_recertification, "
                        . "initial_assessment = :initial_assessment, certification_maintenance = :maintenance_certification, aggrement_allotment = :allotment_agreement, comment = :any_comment, form_no = :form_no, date = :rev_date, rev = :rev, isue = :isue, isuedate = :isueDate,review_date = :review_date WHERE audit_id = :auditId";

                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':app_date', $appDate);
                $statement->bindParam(':org_name', $_POST['orgName']);
                $statement->bindParam(':legal_status', $_POST['legalStatus']);
                $statement->bindParam(':industry_classify', $_POST['industryClassify']);
                $statement->bindParam(':legal_part', $_POST['legalPart']);
                $statement->bindParam(':audit_location', $_POST['locationUnderAudit']);
                $statement->bindParam(':employees', $_POST['employees']);
                $statement->bindParam(':shifts', $_POST['shifts']);
                $statement->bindParam(':product', $_POST['product']);
                $statement->bindParam(':processes', $_POST['processes']);
                $statement->bindParam(':certification', $_POST['certificationStandard']);
                $statement->bindParam(':accreditation', $_POST['accreditation']);
                $statement->bindParam(':consultancy', $_POST['consultancyServices']);
                $statement->bindParam(':exclusion_clauses', $_POST['exclusionClauses']);
                $statement->bindParam(':technical_area', $_POST['technicalArea']);
                $statement->bindParam(':legal_obligations', $_POST['legalObligations']);
                $statement->bindParam(':conflict_interest', $_POST['conflictInterest']);
                $statement->bindParam(':statutory_requirement', $_POST['statutoryRequirement']);
                $statement->bindParam(':customer_requirement', $_POST['customerRequirement']);
                $statement->bindParam(':organization_service', $_POST['organizationService']);
                $statement->bindParam(':other_requirement', $_POST['otherRequirement']);
                $statement->bindParam(':assessment_certification', $_POST['assessmentCertification']);
                $statement->bindParam(':adequate_resource', $_POST['adequateResource']);
                $statement->bindParam(':product_service', $_POST['productService']);
                $statement->bindParam(':scope', $_POST['scope']);
                $statement->bindParam(':competent_team', $_POST['competentTeam']);
                $statement->bindParam(':standard_clause', $_POST['standardClause']);
                $statement->bindParam(':risk_statutory', $_POST['riskStatutory']);
                $statement->bindParam(':risk_conflict', $_POST['riskConflict']);
                $statement->bindParam(':conclusion', $_POST['conclusion']);
                $statement->bindParam(':multisite', $_POST['multisiteEligibility']);
                $statement->bindParam(':assessment', $_POST['assessmentStandard']);
                $statement->bindParam(':aif_code', $_POST['aif-code']);
                $statement->bindParam(':review_accreditation', $_POST['review-accreditation']);
                $statement->bindParam(':proposed_scope', $_POST['proposed-scope']);
                $statement->bindParam(':proposed_month', $_POST['proposed-month']);
                $statement->bindParam(':man_days_required', $_POST['man-days-required']);
                $statement->bindParam(':designing_development', $_POST['designingDevelopment']);
                $statement->bindParam(':justification_reduction', $_POST['justificationReduction']);
                $statement->bindParam(':additive_factor', $_POST['additive-factor']);
                $statement->bindParam(':actual_effective', $_POST['actual-effective']);
                $statement->bindParam(':technical_expert', $_POST['technical-expert']);
                $statement->bindParam(':travel_logistics', $_POST['travel-logistics']);
                $statement->bindParam(':application_amount', $_POST['applicationAmount']);
                $statement->bindParam(':documentation_review', $_POST['documentationReview']);
                $statement->bindParam(':certification_recertification', $_POST['certificationRecertification']);
                $statement->bindParam(':initial_assessment', $_POST['initialAssessment']);
                $statement->bindParam(':maintenance_certification', $_POST['maintenanceCertification']);
                $statement->bindParam(':allotment_agreement', $_POST['allotmentAgreement']);
                $statement->bindParam(':any_comment', $_POST['anyComment']);
                $statement->bindParam(':form_no', $_POST['formNo']);
                $statement->bindParam(':rev', $_POST['rev']);
                $statement->bindParam(':rev_date', $revdate);
                $statement->bindParam(':isue', $_POST['isue']);
                $statement->bindParam(':isueDate', $isueDate);
                $statement->bindParam(':auditId', $_POST['auditId']);
                $statement->bindParam(':review_date', $review_date);
                $statement->execute();

                echo "updated";
            } catch (Exception $ex) {
                
            }
        } else {
            if (isset($_POST['serviceId']) && !empty($_POST['serviceId'])) {
                try {
                    echo $_POST['unisvcId'];
                    if (isset($_POST['applicationDate']) && !empty($_POST['applicationDate'])) {
                        $appDate = date("Y-m-d", strtotime($_POST['applicationDate']));
                    } else {
                        $appDate = NULL;
                    }

                    if (isset($_POST['revDate']) && !empty($_POST['revDate'])) {
                        $revdate = date("Y-m-d", strtotime($_POST['revDate']));
                    } else {
                        $revdate = NULL;
                    }

                    if (isset($_POST['isueDate']) && !empty($_POST['isueDate'])) {
                        $isueDate = date("Y-m-d", strtotime($_POST['isueDate']));
                    } else {
                        $isueDate = NULL;
                    }
                    if (isset($_POST['review_date']) && !empty($_POST['review_date'])) {
                        $review_date = date("Y-m-d", strtotime($_POST['review_date']));
                    } else {
                        $review_date = NULL;
                    }

                    $auditApplication = "INSERT INTO audit_application (service_id, uni_svc_id, application_date, org_name, legal_status, industury_classfication, legal_group, n_location, n_employee, n_shift, product, process, certification, accereditation_requirement, consultancy_services, standard_clauses, technical_area, legal_obligation, conflict_interest, statutory_requirement, customer_requirement, org_involved, other_requirement, assessment_certification, adequate_resource, product_service, scope, competent_team, standard_clause, risk_statutory, risk_conflict, conclusion, multisite_eligibility, assessment_standard, aif_code, review_accredition, proposed_scope, proposed_month, man_day_req, reducing_factor, justification_reducing, additive_factor, actual_effective, technical_req, travel_logistic, refundable_amount, audit_stage_i, audit_stage_iI, initial_assessment, certification_maintenance, aggrement_allotment, comment, form_no, date, rev, isue, isuedate,review_date) "
                            . "VALUES (:caserviceid, :uniId, :app_date, :org_name, :legal_status, :industry_classify, :legal_part, :audit_location, :employees, :shifts, :product, :processes, :certification, :accreditation, :consultancy, :exclusion_clauses, :technical_area, :legal_obligations, :conflict_interest, :statutory_requirement, :customer_requirement, :organization_service, :other_requirement, :assessment_certification, :adequate_resource, :product_service, :scope, :competent_team, :standard_clause, :risk_statutory, :risk_conflict, :conclusion, :multisite, :assessment, :aif_code, :review_accreditation, :proposed_scope, :proposed_month, :man_days_required, :designing_development, :justification_reduction, :additive_factor, :actual_effective, :technical_expert, :travel_logistics, :application_amount, :documentation_review, :certification_recertification, :initial_assessment, :maintenance_certification, :allotment_agreement, :any_comment, :form_no, :rev_date, :rev,  :isue, :isueDate,:review_date)";
                    $statement = $db->prepare($auditApplication);
                    $statement->bindParam(':caserviceid', $_POST['serviceId']);
                    $statement->bindParam(':uniId', $_POST['unisvcId']);
                    $statement->bindParam(':app_date', $appDate);
                    $statement->bindParam(':org_name', $_POST['orgName']);
                    $statement->bindParam(':legal_status', $_POST['legalStatus']);
                    $statement->bindParam(':industry_classify', $_POST['industryClassify']);
                    $statement->bindParam(':legal_part', $_POST['legalPart']);
                    $statement->bindParam(':audit_location', $_POST['locationUnderAudit']);
                    $statement->bindParam(':employees', $_POST['employees']);
                    $statement->bindParam(':shifts', $_POST['shifts']);
                    $statement->bindParam(':product', $_POST['product']);
                    $statement->bindParam(':processes', $_POST['processes']);
                    
                    // Handle multiple ISO standards - use assessmentStandard which contains comma-separated values
                    if (isset($_POST['assessmentStandard']) && !empty($_POST['assessmentStandard'])) {
                        $certificationValue = $_POST['assessmentStandard'];
                    } else if (isset($_POST['certificationStandard'])) {
                        // If certificationStandard is an array, join with commas
                        if (is_array($_POST['certificationStandard'])) {
                            $certificationValue = implode(', ', array_filter($_POST['certificationStandard']));
                        } else {
                            $certificationValue = $_POST['certificationStandard'];
                        }
                    } else {
                        $certificationValue = '';
                    }
                    $statement->bindParam(':certification', $certificationValue);
                    
                    // Handle multiple accreditations - use review-accreditation which contains comma-separated values
                    if (isset($_POST['review-accreditation']) && !empty($_POST['review-accreditation'])) {
                        $accreditationValue = $_POST['review-accreditation'];
                    } else if (isset($_POST['accreditation'])) {
                        // If accreditation is an array, join with commas
                        if (is_array($_POST['accreditation'])) {
                            $accreditationValue = implode(', ', array_filter($_POST['accreditation']));
                        } else {
                            $accreditationValue = $_POST['accreditation'];
                        }
                    } else {
                        $accreditationValue = '';
                    }
                    $statement->bindParam(':accreditation', $accreditationValue);
                    
                    $statement->bindParam(':consultancy', $_POST['consultancyServices']);
                    $statement->bindParam(':exclusion_clauses', $_POST['exclusionClauses']);
                    $statement->bindParam(':technical_area', $_POST['technicalArea']);
                    $statement->bindParam(':legal_obligations', $_POST['legalObligations']);
                    $statement->bindParam(':conflict_interest', $_POST['conflictInterest']);
                    $statement->bindParam(':statutory_requirement', $_POST['statutoryRequirement']);
                    $statement->bindParam(':customer_requirement', $_POST['customerRequirement']);
                    $statement->bindParam(':organization_service', $_POST['organizationService']);
                    $statement->bindParam(':other_requirement', $_POST['otherRequirement']);
                    $statement->bindParam(':assessment_certification', $_POST['assessmentCertification']);
                    $statement->bindParam(':adequate_resource', $_POST['adequateResource']);
                    $statement->bindParam(':product_service', $_POST['productService']);
                    $statement->bindParam(':scope', $_POST['scope']);
                    $statement->bindParam(':competent_team', $_POST['competentTeam']);
                    $statement->bindParam(':standard_clause', $_POST['standardClause']);
                    $statement->bindParam(':risk_statutory', $_POST['riskStatutory']);
                    $statement->bindParam(':risk_conflict', $_POST['riskConflict']);
                    $statement->bindParam(':conclusion', $_POST['conclusion']);
                    $statement->bindParam(':multisite', $_POST['multisiteEligibility']);
                    $statement->bindParam(':assessment', $_POST['assessmentStandard']);
                    $statement->bindParam(':aif_code', $_POST['aif-code']);
                    $statement->bindParam(':review_accreditation', $_POST['review-accreditation']);
                    $statement->bindParam(':proposed_scope', $_POST['proposed-scope']);
                    $statement->bindParam(':proposed_month', $_POST['proposed-month']);
                    $statement->bindParam(':man_days_required', $_POST['man-days-required']);
                    $statement->bindParam(':designing_development', $_POST['designingDevelopment']);
                    $statement->bindParam(':justification_reduction', $_POST['justificationReduction']);
                    $statement->bindParam(':additive_factor', $_POST['additive-factor']);
                    $statement->bindParam(':actual_effective', $_POST['actual-effective']);
                    $statement->bindParam(':technical_expert', $_POST['technical-expert']);
                    $statement->bindParam(':travel_logistics', $_POST['travel-logistics']);
                    $statement->bindParam(':application_amount', $_POST['applicationAmount']);
                    $statement->bindParam(':documentation_review', $_POST['documentationReview']);
                    $statement->bindParam(':certification_recertification', $_POST['certificationRecertification']);
                    $statement->bindParam(':initial_assessment', $_POST['initialAssessment']);
                    $statement->bindParam(':maintenance_certification', $_POST['maintenanceCertification']);
                    $statement->bindParam(':allotment_agreement', $_POST['allotmentAgreement']);
                    $statement->bindParam(':any_comment', $_POST['anyComment']);
                    $statement->bindParam(':form_no', $_POST['formNo']);
                    $statement->bindParam(':rev', $_POST['rev']);
                    $statement->bindParam(':rev_date', $revdate);
                    $statement->bindParam(':isue', $_POST['isue']);
                    $statement->bindParam(':isueDate', $isueDate);
                    $statement->bindParam(':review_date', $review_date);
                    $statement->execute();
//                    header("Location: customerAccount.php?customerid=" . $_POST['customerId']);
                } catch (Exception $ex) {
                    
                }
            }
        }
    }

//    if (isset($_POST['Generate'])) {
//
//
//        if (isset($_POST['auditId']) && !empty($_POST['auditId'])) {
//            try {
//
//                if (isset($_POST['applicationDate']) && !empty($_POST['applicationDate'])) {
//                    $appDate = date("Y-m-d", strtotime($_POST['applicationDate']));
//                } else {
//                    $appDate = NULL;
//                }
//
//                if (isset($_POST['revDate']) && !empty($_POST['revDate'])) {
//                    $revdate = date("Y-m-d", strtotime($_POST['revDate']));
//                } else {
//                    $revdate = NULL;
//                }
//
//                if (isset($_POST['isueDate']) && !empty($_POST['isueDate'])) {
//                    $isueDate = date("Y-m-d", strtotime($_POST['isueDate']));
//                } else {
//                    $isueDate = NULL;
//                }
//
//                $updateAudit = "UPDATE audit_application SET application_date = :app_date, org_name = :org_name, legal_status = :legal_status, industury_classfication = :industry_classify, legal_group = :legal_part, n_location = :audit_location, n_employee = :employees, n_shift = :shifts, product = :product, process = :processes, certification = :certification, accereditation_requirement = :accreditation, consultancy_services = :consultancy,"
//                        . " standard_clauses = :exclusion_clauses, technical_area = :technical_area, legal_obligation = :legal_obligations, conflict_interest = :conflict_interest, statutory_requirement = :statutory_requirement, customer_requirement = :customer_requirement, org_involved = :organization_service, other_requirement = :other_requirement, assessment_certification = :assessment_certification, adequate_resource = :adequate_resource, "
//                        . "product_service = :product_service, scope = :scope, competent_team = :competent_team, standard_clause = :standard_clause, risk_statutory = :risk_statutory, risk_conflict = :risk_conflict, conclusion = :conclusion, multisite_eligibility = :multisite, assessment_standard = :assessment, aif_code = :aif_code, review_accredition = :review_accreditation, proposed_scope = :proposed_scope, proposed_month = :proposed_month, "
//                        . "man_day_req = :man_days_required, reducing_factor = :designing_development, justification_reducing = :justification_reduction, additive_factor = :additive_factor, actual_effective = :actual_effective, technical_req = :technical_expert, travel_logistic = :travel_logistics, refundable_amount = :application_amount, audit_stage_i = :documentation_review, audit_stage_iI = :certification_recertification, "
//                        . "initial_assessment = :initial_assessment, certification_maintenance = :maintenance_certification, aggrement_allotment = :allotment_agreement, comment = :any_comment, form_no = :form_no, date = :rev_date, rev = :rev, isue = :isue, isuedate = :isueDate WHERE audit_id = :auditId";
//
//                $statement = $db->prepare($updateAudit);
//                $statement->bindParam(':app_date', $appDate);
//                $statement->bindParam(':org_name', $_POST['orgName']);
//                $statement->bindParam(':legal_status', $_POST['legalStatus']);
//                $statement->bindParam(':industry_classify', $_POST['industryClassify']);
//                $statement->bindParam(':legal_part', $_POST['legalPart']);
//                $statement->bindParam(':audit_location', $_POST['locationUnderAudit']);
//                $statement->bindParam(':employees', $_POST['employees']);
//                $statement->bindParam(':shifts', $_POST['shifts']);
//                $statement->bindParam(':product', $_POST['product']);
//                $statement->bindParam(':processes', $_POST['processes']);
//                $statement->bindParam(':certification', $_POST['certificationStandard']);
//                $statement->bindParam(':accreditation', $_POST['accreditation']);
//                $statement->bindParam(':consultancy', $_POST['consultancyServices']);
//                $statement->bindParam(':exclusion_clauses', $_POST['exclusionClauses']);
//                $statement->bindParam(':technical_area', $_POST['technicalArea']);
//                $statement->bindParam(':legal_obligations', $_POST['legalObligations']);
//                $statement->bindParam(':conflict_interest', $_POST['conflictInterest']);
//                $statement->bindParam(':statutory_requirement', $_POST['statutoryRequirement']);
//                $statement->bindParam(':customer_requirement', $_POST['customerRequirement']);
//                $statement->bindParam(':organization_service', $_POST['organizationService']);
//                $statement->bindParam(':other_requirement', $_POST['otherRequirement']);
//                $statement->bindParam(':assessment_certification', $_POST['assessmentCertification']);
//                $statement->bindParam(':adequate_resource', $_POST['adequateResource']);
//                $statement->bindParam(':product_service', $_POST['productService']);
//                $statement->bindParam(':scope', $_POST['scope']);
//                $statement->bindParam(':competent_team', $_POST['competentTeam']);
//                $statement->bindParam(':standard_clause', $_POST['standardClause']);
//                $statement->bindParam(':risk_statutory', $_POST['riskStatutory']);
//                $statement->bindParam(':risk_conflict', $_POST['riskConflict']);
//                $statement->bindParam(':conclusion', $_POST['conclusion']);
//                $statement->bindParam(':multisite', $_POST['multisiteEligibility']);
//                $statement->bindParam(':assessment', $_POST['assessmentStandard']);
//                $statement->bindParam(':aif_code', $_POST['aif-code']);
//                $statement->bindParam(':review_accreditation', $_POST['review-accreditation']);
//                $statement->bindParam(':proposed_scope', $_POST['proposed-scope']);
//                $statement->bindParam(':proposed_month', $_POST['proposed-month']);
//                $statement->bindParam(':man_days_required', $_POST['man-days-required']);
//                $statement->bindParam(':designing_development', $_POST['designingDevelopment']);
//                $statement->bindParam(':justification_reduction', $_POST['justificationReduction']);
//                $statement->bindParam(':additive_factor', $_POST['additive-factor']);
//                $statement->bindParam(':actual_effective', $_POST['actual-effective']);
//                $statement->bindParam(':technical_expert', $_POST['technical-expert']);
//                $statement->bindParam(':travel_logistics', $_POST['travel-logistics']);
//                $statement->bindParam(':application_amount', $_POST['applicationAmount']);
//                $statement->bindParam(':documentation_review', $_POST['documentationReview']);
//                $statement->bindParam(':certification_recertification', $_POST['certificationRecertification']);
//                $statement->bindParam(':initial_assessment', $_POST['initialAssessment']);
//                $statement->bindParam(':maintenance_certification', $_POST['maintenanceCertification']);
//                $statement->bindParam(':allotment_agreement', $_POST['allotmentAgreement']);
//                $statement->bindParam(':any_comment', $_POST['anyComment']);
//                $statement->bindParam(':form_no', $_POST['formNo']);
//                $statement->bindParam(':rev', $_POST['rev']);
//                $statement->bindParam(':rev_date', $revdate);
//                $statement->bindParam(':isue', $_POST['isue']);
//                $statement->bindParam(':isueDate', $isueDate);
//                $statement->bindParam(':auditId', $_POST['auditId']);
//                $statement->execute();
//            } catch (Exception $ex) {
//                
//            }
//        } else {
//            if (isset($_POST['serviceId']) && !empty($_POST['serviceId'])) {
//                try {
//
//                    if (isset($_POST['applicationDate']) && !empty($_POST['applicationDate'])) {
//                        $appDate = date("Y-m-d", strtotime($_POST['applicationDate']));
//                    } else {
//                        $appDate = NULL;
//                    }
//
//                    if (isset($_POST['revDate']) && !empty($_POST['revDate'])) {
//                        $revdate = date("Y-m-d", strtotime($_POST['revDate']));
//                    } else {
//                        $revdate = NULL;
//                    }
//
//                    if (isset($_POST['isueDate']) && !empty($_POST['isueDate'])) {
//                        $isueDate = date("Y-m-d", strtotime($_POST['isueDate']));
//                    } else {
//                        $isueDate = NULL;
//                    }
//
//                    $auditApplication = "INSERT INTO audit_application (service_id, application_date, org_name, legal_status, industury_classfication, legal_group, n_location, n_employee, n_shift, product, process, certification, accereditation_requirement, consultancy_services, standard_clauses, technical_area, legal_obligation, conflict_interest, statutory_requirement, customer_requirement, org_involved, other_requirement, assessment_certification, adequate_resource, product_service, scope, competent_team, standard_clause, risk_statutory, risk_conflict, conclusion, multisite_eligibility, assessment_standard, aif_code, review_accredition, proposed_scope, proposed_month, man_day_req, reducing_factor, justification_reducing, additive_factor, actual_effective, technical_req, travel_logistic, refundable_amount, audit_stage_i, audit_stage_iI, initial_assessment, certification_maintenance, aggrement_allotment, comment, form_no, date, rev, isue, isuedate) "
//                            . "VALUES (:caserviceid, :app_date, :org_name, :legal_status, :industry_classify, :legal_part, :audit_location, :employees, :shifts, :product, :processes, :certification, :accreditation, :consultancy, :exclusion_clauses, :technical_area, :legal_obligations, :conflict_interest, :statutory_requirement, :customer_requirement, :organization_service, :other_requirement, :assessment_certification, :adequate_resource, :product_service, :scope, :competent_team, :standard_clause, :risk_statutory, :risk_conflict, :conclusion, :multisite, :assessment, :aif_code, :review_accreditation, :proposed_scope, :proposed_month, :man_days_required, :designing_development, :justification_reduction, :additive_factor, :actual_effective, :technical_expert, :travel_logistics, :application_amount, :documentation_review, :certification_recertification, :initial_assessment, :maintenance_certification, :allotment_agreement, :any_comment, :form_no, :rev_date, :rev,  :isue, :isueDate)";
//                    $statement = $db->prepare($auditApplication);
//                    $statement->bindParam(':caserviceid', $_POST['serviceId']);
//                    $statement->bindParam(':app_date', $appDate);
//                    $statement->bindParam(':org_name', $_POST['orgName']);
//                    $statement->bindParam(':legal_status', $_POST['legalStatus']);
//                    $statement->bindParam(':industry_classify', $_POST['industryClassify']);
//                    $statement->bindParam(':legal_part', $_POST['legalPart']);
//                    $statement->bindParam(':audit_location', $_POST['locationUnderAudit']);
//                    $statement->bindParam(':employees', $_POST['employees']);
//                    $statement->bindParam(':shifts', $_POST['shifts']);
//                    $statement->bindParam(':product', $_POST['product']);
//                    $statement->bindParam(':processes', $_POST['processes']);
//                    $statement->bindParam(':certification', $_POST['certificationStandard']);
//                    $statement->bindParam(':accreditation', $_POST['accreditation']);
//                    $statement->bindParam(':consultancy', $_POST['consultancyServices']);
//                    $statement->bindParam(':exclusion_clauses', $_POST['exclusionClauses']);
//                    $statement->bindParam(':technical_area', $_POST['technicalArea']);
//                    $statement->bindParam(':legal_obligations', $_POST['legalObligations']);
//                    $statement->bindParam(':conflict_interest', $_POST['conflictInterest']);
//                    $statement->bindParam(':statutory_requirement', $_POST['statutoryRequirement']);
//                    $statement->bindParam(':customer_requirement', $_POST['customerRequirement']);
//                    $statement->bindParam(':organization_service', $_POST['organizationService']);
//                    $statement->bindParam(':other_requirement', $_POST['otherRequirement']);
//                    $statement->bindParam(':assessment_certification', $_POST['assessmentCertification']);
//                    $statement->bindParam(':adequate_resource', $_POST['adequateResource']);
//                    $statement->bindParam(':product_service', $_POST['productService']);
//                    $statement->bindParam(':scope', $_POST['scope']);
//                    $statement->bindParam(':competent_team', $_POST['competentTeam']);
//                    $statement->bindParam(':standard_clause', $_POST['standardClause']);
//                    $statement->bindParam(':risk_statutory', $_POST['riskStatutory']);
//                    $statement->bindParam(':risk_conflict', $_POST['riskConflict']);
//                    $statement->bindParam(':conclusion', $_POST['conclusion']);
//                    $statement->bindParam(':multisite', $_POST['multisiteEligibility']);
//                    $statement->bindParam(':assessment', $_POST['assessmentStandard']);
//                    $statement->bindParam(':aif_code', $_POST['aif-code']);
//                    $statement->bindParam(':review_accreditation', $_POST['review-accreditation']);
//                    $statement->bindParam(':proposed_scope', $_POST['proposed-scope']);
//                    $statement->bindParam(':proposed_month', $_POST['proposed-month']);
//                    $statement->bindParam(':man_days_required', $_POST['man-days-required']);
//                    $statement->bindParam(':designing_development', $_POST['designingDevelopment']);
//                    $statement->bindParam(':justification_reduction', $_POST['justificationReduction']);
//                    $statement->bindParam(':additive_factor', $_POST['additive-factor']);
//                    $statement->bindParam(':actual_effective', $_POST['actual-effective']);
//                    $statement->bindParam(':technical_expert', $_POST['technical-expert']);
//                    $statement->bindParam(':travel_logistics', $_POST['travel-logistics']);
//                    $statement->bindParam(':application_amount', $_POST['applicationAmount']);
//                    $statement->bindParam(':documentation_review', $_POST['documentationReview']);
//                    $statement->bindParam(':certification_recertification', $_POST['certificationRecertification']);
//                    $statement->bindParam(':initial_assessment', $_POST['initialAssessment']);
//                    $statement->bindParam(':maintenance_certification', $_POST['maintenanceCertification']);
//                    $statement->bindParam(':allotment_agreement', $_POST['allotmentAgreement']);
//                    $statement->bindParam(':any_comment', $_POST['anyComment']);
//                    $statement->bindParam(':form_no', $_POST['formNo']);
//                    $statement->bindParam(':rev', $_POST['rev']);
//                    $statement->bindParam(':rev_date', $revdate);
//                    $statement->bindParam(':isue', $_POST['isue']);
//                    $statement->bindParam(':isueDate', $isueDate);
//                    $statement->execute();
//                } catch (Exception $ex) {
//                    
//                }
//            }
//        }
//    }
}

<?php

// Prevent premature output that breaks PDF headers
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ob_start();
session_start();

//============================================================+
// File name   : example_003.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14g
//
// Description : Example 003 for TCPDF class
//               Custom Header and Footer
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
//database details get and display
$appDate = "";
$appRcvDate = "";
$review_date = "";
$orgName = "";
$legalStatus = "";
$industury_classfication = "";
$legal_group = "";
$n_location = "";
$n_employee = "";
$n_shift = "";
$product = "";
$process = "";
$certification = "";
$accereditation_requirement = "";
$consultancy_services = "";

$yes = "";
$no = "";

$yes1 = "";
$no1 = "";

$yes2 = "";
$no2 = "";

$yes3 = "";
$no3 = "";

$yes4 = "";
$no4 = "";

$yes5 = "";
$no5 = "";

$yes6 = "";
$no6 = "";

$yes7 = "";
$no7 = "";
$conclusion = "";
$conclusionborc = "";
$multisite_eligibility = "";

$standard_clauses = "";
$technical_area = "";
$legal_obligation = "";
$conflict_interest = "";
$statutory_requirement = "";
$customer_requirement = "";
$org_involved = "";
$other_requirement = "";

$assessment_standard = "";
$aif_code = "";
$nace_code = "";
$review_accredition = "";
$proposed_scope = "";
$proposed_month = "";
$man_day_req = "";
$reducing_factor = "";
$justification_reducing = "";
$additive_factor = "";
$actual_effective = "";
$technical_req = "";
$travel_logistic = "";

$refundable_amount = "";
$audit_stage_i = "";
$audit_stage_iI = "";
$initial_assessment = "";
$certification_maintenance = "";
$aggrement_allotment = "";
$applicationAmountDesc = "";
$documentationReviewDesc = "";
$certificationRecertificationDesc = "";
$maintenanceCertificationDesc = "";
$reviewer = "";

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {

    if (isset($_POST['addApplication'])) {

        if (isset($_POST['auditId']) && !empty($_POST['auditId'])) {
            try {
                if (isset($_POST['applicationDate']) && !empty($_POST['applicationDate'])) {
                    $appDate = date("Y-m-d", strtotime($_POST['applicationDate']));
                } else {
                    $appDate = 'null';
                }
                if (isset($_POST['applicationRcvDate']) && !empty($_POST['applicationRcvDate'])) {
                    $appRcvDate = date("Y-m-d", strtotime($_POST['applicationRcvDate']));
                } else {
                    $appRcvDate = 'null';
                }
                if (isset($_POST['review_date']) && !empty($_POST['review_date'])) {
                    $review_date = date("Y-m-d", strtotime($_POST['review_date']));
                } else {
                    $review_date = NULL;
                }

                if (isset($_POST["applicationfor"]) && !empty($_POST["applicationfor"])) {
                    $application = $_POST["applicationfor"];
                } else {
                    $application = NULL;
                }
//                echo $_POST['applicationAmountDesc'] .' -> '.$_POST['documentationReviewDesc'].' -> '.$_POST['certificationRecertificationDesc'].' -> '.$_POST['maintenanceCertificationDesc']; die;
                $totalAmount = $_POST['applicationAmount'] + $_POST['documentationReview'] + $_POST['certificationRecertification'];
                
                if (isset($_POST["customizeiafnace"]) && $_POST["customizeiafnace"] == "customize") {
                    $iafcode = $_POST["customizeiaf_code"];
                    $nacecode = $_POST["customize_nace"];
                    $iaftype = "customize";
                } else {
                    $iafcode = $_POST['selectedSectors'];
                    $nacecode = $_POST['selectedNaceCode'];
                    $naceclass = $_POST['selectedNaceClass'];
                    $iaftype = "basic";
                }
                var_dump($naceclass);
                $iafcode_array = explode(",", $iafcode);
                $nacecode_array = explode(",", $nacecode);
                $naceclass_array = explode(",", $naceclass);
                $combined_strings = [];
                for ($i = 0; $i < count($iafcode_array); $i++) {
                    $combined_strings[] = $iafcode_array[$i] . "/" . $nacecode_array[$i] . "/" . $naceclass_array[$i];
                    }
                $aifNaceCode = implode(', ', $combined_strings);

                $updateAudit = "UPDATE audit_application SET application_for = :appfor, applicationfor0 = :applicationfor0, applicationfor1 = :applicationfor1, applicationfor2 = :applicationfor2, applicationfor3 = :applicationfor3, applicationfor4 = :applicationfor4, application_date = :app_date, application_rcv_date = :app_rcv_date, org_name = :org_name, legal_status = :legal_status, industury_classfication = :industry_classify, legal_group = :legal_part, n_location = :audit_location, n_employee = :employees, n_shift = :shifts, product = :product, process = :processes, certification = :certification, accereditation_requirement = :accreditation, consultancy_services = :consultancy,"
                        . " standard_clauses = :exclusion_clauses, technical_area = :technical_area, legal_obligation = :legal_obligations, conflict_interest = :conflict_interest, statutory_requirement = :statutory_requirement, customer_requirement = :customer_requirement, org_involved = :organization_service, other_requirement = :other_requirement, assessment_certification = :assessment_certification, adequate_resource = :adequate_resource, "
                        . "product_service = :product_service, scope = :scope, competent_team = :competent_team, standard_clause = :standard_clause, risk_statutory = :risk_statutory, risk_conflict = :risk_conflict, conclusionborc = :conclusionborc, conclusion = :conclusion, multisite_eligibility = :multisite, assessment_standard = :assessment, review_accredition = :review_accreditation, proposed_scope = :proposed_scope, proposed_month = :proposed_month, "
                        . "man_day_req = :man_days_required, reducing_factor = :designing_development, justification_reducing = :justification_reduction, additive_factor = :additive_factor, actual_effective = :actual_effective, technical_req = :technical_expert, travel_logistic = :travel_logistics, refundable_amount = :application_amount, application_amount_desc = :application_amount_desc, audit_stage_i = :documentation_review, documentation_review_desc = :documentation_review_desc, audit_stage_iI = :certification_recertification,"
                        . "certification_recertification_desc = :certification_recertification_desc, initial_assessment = :initial_assessment, certification_maintenance = :maintenance_certification, maintenance_certification_desc = :maintenance_certification_desc, reviewer_name = :reviewer_name, review_date = :review_date WHERE audit_id = :auditId";

                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':appfor', $application);
                $statement->bindParam(':app_date', $appDate);
                $statement->bindParam(':app_rcv_date', $appRcvDate);
                $statement->bindParam(':org_name', $_POST['orgName']);
                $statement->bindParam(':legal_status', $_POST['legalStatus']);
                $statement->bindParam(':industry_classify', $_POST['industryClassify']);
                $statement->bindParam(':legal_part', $_POST['legalPart']);
                $statement->bindParam(':audit_location', $_POST['locationUnderAudit']);
                $statement->bindParam(':employees', $_POST['employees']);
                $statement->bindParam(':shifts', $_POST['shifts']);
                $statement->bindParam(':product', $_POST['product']);
                $statement->bindParam(':processes', $_POST['processes']);
                $statement->bindParam(':certification', $_POST['selectedCertifications']);
                $statement->bindParam(':accreditation', $_POST['reviewaccreditation']);
                $statement->bindParam(':consultancy', $_POST['consultancyServices']);
                $statement->bindParam(':exclusion_clauses', $_POST['exclusionClauses']);
                $statement->bindParam(':technical_area', $aifNaceCode);
                $statement->bindParam(':legal_obligations', $_POST['legalObligations']);
                $statement->bindParam(':conflict_interest', $_POST['conflictInterest']);
                $statement->bindParam(':statutory_requirement', $_POST['statutoryRequirement']);
                $statement->bindParam(':customer_requirement', $_POST['customerRequirement']);
                $statement->bindParam(':organization_service', $_POST['organizationService']);
                $statement->bindParam(':other_requirement', $_POST['otherRequirement']);
                $statement->bindParam(':applicationfor2', $_POST['applicationfor2']);
                $statement->bindParam(':applicationfor3', $_POST['applicationfor3']);
                $statement->bindParam(':applicationfor4', $_POST['applicationfor4']);
                $statement->bindParam(':applicationfor1', $_POST['applicationfor1']);
                $statement->bindParam(':applicationfor0', $_POST['applicationfor0']);
                $statement->bindParam(':assessment_certification', $_POST['assessmentCertification']);
                $statement->bindParam(':adequate_resource', $_POST['adequateResource']);
                $statement->bindParam(':product_service', $_POST['productService']);
                $statement->bindParam(':scope', $_POST['scope']);
                $statement->bindParam(':competent_team', $_POST['competentTeam']);
                $statement->bindParam(':standard_clause', $_POST['standardClause']);
                $statement->bindParam(':risk_statutory', $_POST['riskStatutory']);
                $statement->bindParam(':risk_conflict', $_POST['riskConflict']);
                $statement->bindParam(':conclusionborc', $_POST['conclusionborc']);
                $statement->bindParam(':conclusion', $_POST['conclusion']);
                $statement->bindParam(':multisite', $_POST['multisiteEligibility']);
                $statement->bindParam(':assessment', $_POST['assessmentStandard']);
//                $statement->bindParam(':aif_code', $_POST['aif-code']);
//                $statement->bindParam(':nace_code', $_POST['nace-code']);
                $statement->bindParam(':review_accreditation', $_POST['reviewaccreditation']);
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
                $statement->bindParam(':application_amount_desc', $_POST['applicationAmountDesc']);
                $statement->bindParam(':documentation_review', $_POST['documentationReview']);
                $statement->bindParam(':documentation_review_desc', $_POST['documentationReviewDesc']);
                $statement->bindParam(':certification_recertification', $_POST['certificationRecertification']);
                $statement->bindParam(':certification_recertification_desc', $_POST['certificationRecertificationDesc']);
                $statement->bindParam(':initial_assessment', $totalAmount);
                $statement->bindParam(':maintenance_certification', $_POST['maintenanceCertification']);
                $statement->bindParam(':maintenance_certification_desc', $_POST['maintenanceCertificationDesc']);
//                $statement->bindParam(':allotment_agreement', $_POST['allotmentAgreement']);
                $statement->bindParam(':reviewer_name', $_POST['reviewer_name']);
                $statement->bindParam(':review_date', $review_date);
                $statement->bindParam(':auditId', $_POST['auditId']);
                $statement->execute();
                
                
                if (isset($_POST["applicationfor0"]) && $_POST["applicationfor0"] == "Yes") {
                $updateAudit = "UPDATE audit_application SET no_integration = :no_integration,management_review = :management_review,integrated_management = :integrated_management,integrated_approach = :integrated_approach,integrated_responsibilities = :integrated_responsibilities WHERE audit_id = :auditId";
                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':no_integration', $_POST['nointegration']);
                $statement->bindParam(':management_review', $_POST['managementreview']);
                $statement->bindParam(':integrated_management', $_POST['integratedmanagement']);
                $statement->bindParam(':integrated_approach', $_POST['integratedapproach']);
                $statement->bindParam(':integrated_responsibilities', $_POST['integratedresponsibilities']);
                $statement->bindParam(':auditId', $_POST['auditId']);
                $statement->execute();}

                $updateAudit = "UPDATE audit_application SET main_Products = :main_Products,main_RawMaterial = :main_RawMaterial,operation_Involved = :operation_Involved,sources_Product = :sources_Product,complicated_Logistics = :complicated_Logistics WHERE audit_id = :auditId";
                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':main_Products', $_POST['mainProducts']);
                $statement->bindParam(':main_RawMaterial', $_POST['mainRawMaterial']);
                $statement->bindParam(':operation_Involved', $_POST['operationInvolved']);
                $statement->bindParam(':sources_Product', $_POST['sourcesProduct']);
                $statement->bindParam(':complicated_Logistics', $_POST['complicatedLogistics']);
                $statement->bindParam(':auditId', $_POST['auditId']);
                $statement->execute();
                
                if (isset($_POST["applicationfor2"]) && $_POST["applicationfor2"] == "Yes") {
                $updateAudit = "UPDATE audit_application SET environmental_Clearance = :environmental_Clearance, hazardous_Materials = :hazardous_Materials, adverse_Impact = :adverse_Impact, sensitive_Location = :sensitive_Location, archaeological_Site = :archaeological_Site, adverse_History = :adverse_History WHERE audit_id = :auditId";
                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':environmental_Clearance', $_POST['environmentalClearance']);
                $statement->bindParam(':hazardous_Materials', $_POST['hazardousMaterials']);
                $statement->bindParam(':adverse_Impact', $_POST['adverseImpact']);
                $statement->bindParam(':sensitive_Location', $_POST['sensitiveLocation']);
                $statement->bindParam(':archaeological_Site', $_POST['archaeologicalSite']);
                $statement->bindParam(':adverse_History', $_POST['adverseHistory']);
                $statement->bindParam(':auditId', $_POST['auditId']);
                $statement->execute();}

                if (isset($_POST["applicationfor3"]) && $_POST["applicationfor3"] == "Yes") {
                $updateAudit = "UPDATE audit_application SET statutory_Healthandsafety = :statutory_Healthandsafety, hazardousDangerous_Substances = :hazardousDangerous_Substances, healthAndSafety_Risks = :healthAndSafety_Risks, temporary_sites = :temporary_sites, accidents_Rates = :accidents_Rates, Sites_PublicPresent = :Sites_PublicPresent, governmental_Regulators = :governmental_Regulators WHERE audit_id = :auditId";
                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':statutory_Healthandsafety', $_POST['statutoryHealthandsafety']);
                $statement->bindParam(':hazardousDangerous_Substances', $_POST['hazardousDangerousSubstances']);
                $statement->bindParam(':healthAndSafety_Risks', $_POST['healthAndSafetyRisks']);
                $statement->bindParam(':temporary_sites', $_POST['temporarysites']);
                $statement->bindParam(':accidents_Rates', $_POST['accidentsRates']);
                $statement->bindParam(':Sites_PublicPresent', $_POST['SitesPublicPresent']);
                $statement->bindParam(':governmental_Regulators', $_POST['governmentalRegulators']);
                $statement->bindParam(':auditId', $_POST['auditId']);
                $statement->execute();}

                if (isset($_POST["applicationfor4"]) && $_POST["applicationfor4"] == "Yes") {
                $updateAudit = "UPDATE audit_application SET other_StanderedName = :other_StanderedName, other_CheckPoint1 = :other_CheckPoint1, other_CheckPoint2 = :other_CheckPoint2, other_CheckPoint3 = :other_CheckPoint3, other_CheckPoint4 = :other_CheckPoint4, other_CheckPoint5 = :other_CheckPoint5, other_CheckPoint6 = :other_CheckPoint6 WHERE audit_id = :auditId";
                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':other_StanderedName', $_POST['otherStanderedName']);
                $statement->bindParam(':other_CheckPoint1', $_POST['otherCheckPoint1']);
                $statement->bindParam(':other_CheckPoint2', $_POST['otherCheckPoint2']);
                $statement->bindParam(':other_CheckPoint3', $_POST['otherCheckPoint3']);
                $statement->bindParam(':other_CheckPoint4', $_POST['otherCheckPoint4']);
                $statement->bindParam(':other_CheckPoint5', $_POST['otherCheckPoint5']);
                $statement->bindParam(':other_CheckPoint6', $_POST['otherCheckPoint6']);
                $statement->bindParam(':auditId', $_POST['auditId']);
                $statement->execute();}


                if (isset($_POST["customizeiafnace"]) && $_POST["customizeiafnace"] == "customize") {
                    $iafcode = $_POST["customizeiaf_code"];
                    $nacecode = $_POST["customize_nace"];
                    $iaftype = "customize";
                } else {
                    $iafcode = $_POST['selectedSectors'];
                    $nacecode = $_POST['selectedNaceCode'];
                    $naceclass = $_POST['selectedNaceClass'];
                    $iaftype = "basic";
                }
                var_dump($naceclass);
                $iafcode_array = explode(",", $iafcode);
                $nacecode_array = explode(",", $nacecode);
                $naceclass_array = explode(",", $naceclass);
                $combined_strings = [];
                for ($i = 0; $i < count($iafcode_array); $i++) {
                    $combined_strings[] = $iafcode_array[$i] . "/" . $nacecode_array[$i] . "/" . $naceclass_array[$i];
                    }
                $aifNaceCode = implode(', ', $combined_strings);

                $sql1 = "UPDATE `audit_iaf_nace` SET `iaf_code`= :iafcode,`nace_codes`= :nacecode,`nace_class`= :naceclass,`iaf_type`= :iaftype,`nace_class`= :naceclass  WHERE `audit_id` = :audid";
                $statement = $db->prepare($sql1);
                $statement->bindParam(":audid", $_POST['auditId']);
                //$statement->bindParam(":iafid", $_POST["iafid"]);
                $statement->bindParam(":iafcode", $iafcode);
                $statement->bindParam(":nacecode", $nacecode);
                $statement->bindParam(":naceclass", $naceclass);
                $statement->bindParam(":iaftype", $iaftype);
                $statement->bindParam(":naceclass", $naceclass);
                $statement->execute();

                $updateNextService = "UPDATE customer_info SET aif_nace = :aif_code, scope = :scope WHERE id = :id";
                $statement = $db->prepare($updateNextService);
                $statement->bindParam(':aif_code', $aifNaceCode);
                $statement->bindParam(':scope', $_POST["proposed-scope"]);
                $statement->bindParam(':id', $_POST['customerId']);
                $statement->execute();
//                die;
                if ($_POST["pageview"] == "customer") {
                    header("Location: customerAccount.php?customerid=" . $_POST['customerId']);
                } else if ($_POST["pageview"] == "agreement") {
                    header("Location: agreementPage.php");
                }
//                header("Location: customerAccount.php?customerid=" . $_POST['customerId']);
            } catch (Exception $ex) {
                echo $ex;
            }
        } else {
            if (isset($_POST['serviceId']) && !empty($_POST['serviceId'])) {
                try {
                    if (isset($_POST['applicationDate']) && !empty($_POST['applicationDate'])) {
                        $appDate = date("Y-m-d", strtotime($_POST['applicationDate']));
                    } else {
                        $appDate = NULL;
                    }
                    if (isset($_POST['applicationRcvDate']) && !empty($_POST['applicationRcvDate'])) {
                        $appRcvDate = date("Y-m-d", strtotime($_POST['applicationRcvDate']));
                    } else {
                        $appRcvDate = 'null';
                    }
                    if (isset($_POST['review_date']) && !empty($_POST['review_date'])) {
                        $review_date = date("Y-m-d", strtotime($_POST['review_date']));
                    } else {
                        $review_date = NULL;
                    }
                    if (isset($_POST['legalStatus']) && $_POST['legalStatus'] == "Other") {
                        $status = $_POST['otherStatus'];
                    } else {
                        $status = $_POST['legalStatus'];
                    }
                    if (isset($_POST["applicationfor"]) && !empty($_POST["applicationfor"])) {
                        $application = $_POST["applicationfor"];
                    } else {
                        $application = NULL;
                    }

                    if (isset($_POST["customizeiafnace"]) && $_POST["customizeiafnace"] == "customize") {
                        $iafcode = $_POST["customizeiaf_code"];
                        $nacecode = $_POST["customize_nace"];
                        $iaftype = "customize";
                    } else {
                        $iafcode = $_POST['selectedSectors'];
                        $nacecode = $_POST['selectedNaceCode'];
                        $naceclass = $_POST['selectedNaceClass'];
                        $iaftype = "basic";
                    }
                    var_dump($naceclass);
                    $iafcode_array = explode(",", $iafcode);
                    $nacecode_array = explode(",", $nacecode);
                    $naceclass_array = explode(",", $naceclass);
                    $combined_strings = [];
                    for ($i = 0; $i < count($iafcode_array); $i++) {
                        $combined_strings[] = $iafcode_array[$i] . "/" . $nacecode_array[$i] . "/" . $naceclass_array[$i];
                    }
                    $aifNaceCode = implode(', ', $combined_strings);
                    
                    $totalAmount = $_POST['applicationAmount'] + $_POST['documentationReview'] + $_POST['certificationRecertification'];

                    $auditApplication = "INSERT INTO audit_application (service_id, uni_svc_id, application_for, application_date, application_rcv_date, org_name, legal_status, industury_classfication, legal_group, n_location, n_employee, n_shift, product, process, certification, accereditation_requirement, consultancy_services, standard_clauses, technical_area, legal_obligation, conflict_interest, statutory_requirement, customer_requirement, org_involved, other_requirement, assessment_certification, adequate_resource, product_service, scope, competent_team, standard_clause, risk_statutory, risk_conflict, conclusionborc, conclusion, multisite_eligibility, assessment_standard, review_accredition, proposed_scope, proposed_month, man_day_req, reducing_factor, justification_reducing, additive_factor, actual_effective, technical_req, travel_logistic, refundable_amount, application_amount_desc, audit_stage_i, documentation_review_desc, audit_stage_iI, certification_recertification_desc, initial_assessment, certification_maintenance, maintenance_certification_desc, reviewer_name, review_date, applicationfor2, applicationfor3, applicationfor4, applicationfor0) "
                            . "VALUES (:caserviceid, :uniId, :appfor, :app_date, :app_rcv_date, :org_name, :legal_status, :industry_classify, :legal_part, :audit_location, :employees, :shifts, :product, :processes, :certification, :accreditation, :consultancy, :exclusion_clauses, :technical_area, :legal_obligations, :conflict_interest, :statutory_requirement, :customer_requirement, :organization_service, :other_requirement, :assessment_certification, :adequate_resource, :product_service, :scope, :competent_team, :standard_clause, :risk_statutory, :risk_conflict, :conclusionborc, :conclusion, :multisite, :assessment, :review_accreditation, :proposed_scope, :proposed_month, :man_days_required, :designing_development, :justification_reduction, :additive_factor, :actual_effective, :technical_expert, :travel_logistics, :application_amount, :application_amount_desc, :documentation_review, :documentation_review_desc, :certification_recertification, :certification_recertification_desc, :initial_assessment, :maintenance_certification, :maintenance_certification_desc, :reviewer_name, :review_date, :applicationfor2, :applicationfor3, :applicationfor4, :applicationfor0)";
                    $statement = $db->prepare($auditApplication);
                    $statement->bindParam(':caserviceid', $_POST['serviceId']);
                    $statement->bindParam(':uniId', $_POST['unisvcId']);
                    $statement->bindParam(':appfor', $application);
                    $statement->bindParam(':app_date', $appDate);
                    $statement->bindParam(':app_rcv_date', $appRcvDate);
                    $statement->bindParam(':org_name', $_POST['orgName']);
                    $statement->bindParam(':legal_status', $status);
                    $statement->bindParam(':industry_classify', $_POST['industryClassify']);
                    $statement->bindParam(':legal_part', $_POST['legalPart']);
                    $statement->bindParam(':audit_location', $_POST['locationUnderAudit']);
                    $statement->bindParam(':employees', $_POST['employees']);
                    $statement->bindParam(':shifts', $_POST['shifts']);
                    $statement->bindParam(':product', $_POST['product']);
                    $statement->bindParam(':processes', $_POST['processes']);
                    $statement->bindParam(':certification', $_POST['selectedCertifications']);
                    $statement->bindParam(':accreditation', $_POST['reviewaccreditation']);
                    $statement->bindParam(':consultancy', $_POST['consultancyServices']);
                    $statement->bindParam(':exclusion_clauses', $_POST['exclusionClauses']);
                    $statement->bindParam(':technical_area', $aifNaceCode);
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
                    $statement->bindParam(':conclusionborc', $_POST['conclusionborc']);
                    $statement->bindParam(':conclusion', $_POST['conclusion']);
                    $statement->bindParam(':multisite', $_POST['multisiteEligibility']);
                    $statement->bindParam(':assessment', $_POST['assessmentStandard']);
//                    $statement->bindParam(':aif_code', $_POST['aif-code']);
//                    $statement->bindParam(':nace_code', $_POST['nace-code']);
                    $statement->bindParam(':review_accreditation', $_POST['reviewaccreditation']);
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
                    $statement->bindParam(':application_amount_desc', $_POST['applicationAmountDesc']);
                    $statement->bindParam(':documentation_review', $_POST['documentationReview']);
                    $statement->bindParam(':documentation_review_desc', $_POST['documentationReviewDesc']);
                    $statement->bindParam(':certification_recertification', $_POST['certificationRecertification']);
                    $statement->bindParam(':certification_recertification_desc', $_POST['certificationRecertificationDesc']);
                    $statement->bindParam(':initial_assessment', $totalAmount);
                    $statement->bindParam(':maintenance_certification', $_POST['maintenanceCertification']);
                    $statement->bindParam(':maintenance_certification_desc', $_POST['maintenanceCertificationDesc']);
                    $statement->bindParam(':reviewer_name', $_POST['reviewer_name']);
                    $statement->bindParam(':review_date', $review_date);
                    $statement->bindParam(':applicationfor2', $_POST['applicationfor2']);
                    $statement->bindParam(':applicationfor3', $_POST['applicationfor3']);
                    $statement->bindParam(':applicationfor4', $_POST['applicationfor4']);
                    $statement->bindParam(':applicationfor0', $_POST['applicationfor0']);
                    $statement->execute();
                    
                    
                    
                if (isset($_POST["applicationfor0"]) && $_POST["applicationfor0"] == "Yes") {
                $updateAudit = "UPDATE audit_application SET no_integration = :no_integration,management_review = :management_review,integrated_management = :integrated_management,integrated_approach = :integrated_approach,integrated_responsibilities = :integrated_responsibilities WHERE service_id = :caserviceid";
                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':no_integration', $_POST['nointegration']);
                $statement->bindParam(':management_review', $_POST['managementreview']);
                $statement->bindParam(':integrated_management', $_POST['integratedmanagement']);
                $statement->bindParam(':integrated_approach', $_POST['integratedapproach']);
                $statement->bindParam(':integrated_responsibilities', $_POST['integratedresponsibilities']);
                $statement->bindParam(':caserviceid', $_POST['serviceId']);
                $statement->execute();}

                $updateAudit = "UPDATE audit_application SET main_Products = :main_Products,main_RawMaterial = :main_RawMaterial,operation_Involved = :operation_Involved,sources_Product = :sources_Product,complicated_Logistics = :complicated_Logistics WHERE service_id = :caserviceid";
                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':main_Products', $_POST['mainProducts']);
                $statement->bindParam(':main_RawMaterial', $_POST['mainRawMaterial']);
                $statement->bindParam(':operation_Involved', $_POST['operationInvolved']);
                $statement->bindParam(':sources_Product', $_POST['sourcesProduct']);
                $statement->bindParam(':complicated_Logistics', $_POST['complicatedLogistics']);
                $statement->bindParam(':caserviceid', $_POST['serviceId']);
                $statement->execute();
                
                if (isset($_POST["applicationfor2"]) && $_POST["applicationfor2"] == "Yes") {
                $updateAudit = "UPDATE audit_application SET environmental_Clearance = :environmental_Clearance, hazardous_Materials = :hazardous_Materials, adverse_Impact = :adverse_Impact, sensitive_Location = :sensitive_Location, archaeological_Site = :archaeological_Site, adverse_History = :adverse_History WHERE service_id = :caserviceid";
                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':environmental_Clearance', $_POST['environmentalClearance']);
                $statement->bindParam(':hazardous_Materials', $_POST['hazardousMaterials']);
                $statement->bindParam(':adverse_Impact', $_POST['adverseImpact']);
                $statement->bindParam(':sensitive_Location', $_POST['sensitiveLocation']);
                $statement->bindParam(':archaeological_Site', $_POST['archaeologicalSite']);
                $statement->bindParam(':adverse_History', $_POST['adverseHistory']);
                $statement->bindParam(':caserviceid', $_POST['serviceId']);
                $statement->execute();}

                if (isset($_POST["applicationfor3"]) && $_POST["applicationfor3"] == "Yes") {
                $updateAudit = "UPDATE audit_application SET statutory_Healthandsafety = :statutory_Healthandsafety, hazardousDangerous_Substances = :hazardousDangerous_Substances, healthAndSafety_Risks = :healthAndSafety_Risks, temporary_sites = :temporary_sites, accidents_Rates = :accidents_Rates, Sites_PublicPresent = :Sites_PublicPresent, governmental_Regulators = :governmental_Regulators WHERE service_id = :caserviceid";
                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':statutory_Healthandsafety', $_POST['statutoryHealthandsafety']);
                $statement->bindParam(':hazardousDangerous_Substances', $_POST['hazardousDangerousSubstances']);
                $statement->bindParam(':healthAndSafety_Risks', $_POST['healthAndSafetyRisks']);
                $statement->bindParam(':temporary_sites', $_POST['temporarysites']);
                $statement->bindParam(':accidents_Rates', $_POST['accidentsRates']);
                $statement->bindParam(':Sites_PublicPresent', $_POST['SitesPublicPresent']);
                $statement->bindParam(':governmental_Regulators', $_POST['governmentalRegulators']);
                $statement->bindParam(':caserviceid', $_POST['serviceId']);
                $statement->execute();}

                if (isset($_POST["applicationfor4"]) && $_POST["applicationfor4"] == "Yes") {
                $updateAudit = "UPDATE audit_application SET other_StanderedName = :other_StanderedName, other_CheckPoint1 = :other_CheckPoint1, other_CheckPoint2 = :other_CheckPoint2, other_CheckPoint3 = :other_CheckPoint3, other_CheckPoint4 = :other_CheckPoint4, other_CheckPoint5 = :other_CheckPoint5, other_CheckPoint6 = :other_CheckPoint6 WHERE service_id = :caserviceid";
                $statement = $db->prepare($updateAudit);
                $statement->bindParam(':other_StanderedName', $_POST['otherStanderedName']);
                $statement->bindParam(':other_CheckPoint1', $_POST['otherCheckPoint1']);
                $statement->bindParam(':other_CheckPoint2', $_POST['otherCheckPoint2']);
                $statement->bindParam(':other_CheckPoint3', $_POST['otherCheckPoint3']);
                $statement->bindParam(':other_CheckPoint4', $_POST['otherCheckPoint4']);
                $statement->bindParam(':other_CheckPoint5', $_POST['otherCheckPoint5']);
                $statement->bindParam(':other_CheckPoint6', $_POST['otherCheckPoint6']);
                $statement->bindParam(':caserviceid', $_POST['serviceId']);
                $statement->execute();}


                    $sql = "SELECT max(LAST_INSERT_ID(audit_id)) as id From audit_application";
                    $statement = $db->prepare($sql);
                    $statement->execute();
                    $row = $statement->fetch();

                    $auditid = $row['id'];

                    $sql1 = "INSERT INTO `audit_iaf_nace`(`audit_id`, `iaf_id`, `iaf_code`, `nace_codes`, iaf_type, `nace_class`) VALUES "
                            . "(:audid, :iafid, :iafcode, :nacecode, :iaftype, :naceclass)";
                    $statement = $db->prepare($sql1);
                    $statement->bindParam(":audid", $auditid);
                    $statement->bindParam(":iafid", $_POST["iafid"]);
                    $statement->bindParam(":iafcode", $iafcode);
                    $statement->bindParam(":nacecode", $nacecode);
                    $statement->bindParam(":naceclass", $naceclass);
                    $statement->bindParam(":iaftype", $iaftype);
                    $statement->execute();
                    
                    $updateNextService = "UPDATE customer_info SET aif_nace = :aif_code, scope = :scope WHERE id = :id";
                    $statement = $db->prepare($updateNextService);
                    $statement->bindParam(':aif_code', $aifNaceCode);
                    $statement->bindParam(':scope', $_POST["proposed-scope"]);
                    $statement->bindParam(':id', $_POST['customerId']);
                    $statement->execute();

                    $d = new DateTime('first day of this month');
                    $strtDay = $d->format('Y-m-d');
                    $todayDay = date('Y-m-d');
                    if ($todayDay == $strtDay) {
                        $updateCount = "UPDATE audit_count SET counter = '1' WHERE count_id = 1";
                        $statement = $db->prepare($updateCount);
                        $statement->execute();
                    } else {
                        $updateCount = "UPDATE audit_count SET counter = counter + 1 WHERE count_id = 1";
                        $statement = $db->prepare($updateCount);
                        $statement->execute();
                    }
                    if ($_POST["pageview"] == "customer") {
                        header("Location: customerAccount.php?customerid=" . $_POST['customerId']);
                    } else if ($_POST["pageview"] == "agreement") {
                        header("Location: agreementPage.php");
                    }
                } catch (Exception $ex) {
                    echo $ex;
                }
            }
        }
    }
}

if (isset($_POST['auditid'])) {

    $auditDetails = "SELECT * FROM audit_application WHERE audit_id = :id";
    $statement = $db->prepare($auditDetails);
    $statement->bindParam(':id', $_POST['auditid']);
    $statement->execute();
    $auditInfo = $statement->fetch();

    $appDate = date("d-m-Y", strtotime($auditInfo['application_date']));
    $appRcvDate = date("d-m-Y", strtotime($auditInfo['application_rcv_date']));
    $review_date = date("d-m-Y", strtotime($auditInfo['review_date']));
    $orgName = $auditInfo['org_name'];
    $legalStatus = $auditInfo['legal_status'];
    $industury_classfication = $auditInfo['industury_classfication'];
    $legal_group = $auditInfo['legal_group'];
    $n_location = $auditInfo['n_location'];
    $n_employee = $auditInfo['n_employee'];
    $n_shift = $auditInfo['n_shift'];
    $product = $auditInfo['product'];
    $process = $auditInfo['process'];
    $certification = $auditInfo['certification'];
    $accereditation_requirement = $auditInfo['accereditation_requirement'];
    $consultancy_services = $auditInfo['consultancy_services'];
    $standard_clauses = $auditInfo['standard_clauses'];
    $technical_area = $auditInfo['technical_area'];
    $legal_obligation = $auditInfo['legal_obligation'];
    $conflict_interest = $auditInfo['conflict_interest'];
    $statutory_requirement = $auditInfo['statutory_requirement'];
    $customer_requirement = $auditInfo['customer_requirement'];
    $org_involved = $auditInfo['org_involved'];
    $other_requirement = $auditInfo['other_requirement'];
    $assessment_standard = $auditInfo['assessment_standard'];
    $review_accredition = $auditInfo['review_accredition'];
    $proposed_scope = $auditInfo['proposed_scope'];
    $proposed_month = $auditInfo['proposed_month'];
    $man_day_req = $auditInfo['man_day_req'];
    $reducing_factor = $auditInfo['reducing_factor'];
    $justification_reducing = $auditInfo['justification_reducing'];
    $additive_factor = $auditInfo['additive_factor'];
    $actual_effective = $auditInfo['actual_effective'];
    $technical_req = $auditInfo['technical_req'];
    $travel_logistic = $auditInfo['travel_logistic'];
    $refundable_amount = $auditInfo['refundable_amount'];
    $audit_stage_i = $auditInfo['audit_stage_i'];
    $audit_stage_iI = $auditInfo['audit_stage_iI'];
    $initial_assessment = $auditInfo['initial_assessment'];
    $certification_maintenance = $auditInfo['certification_maintenance'];
    $aggrement_allotment = $auditInfo['aggrement_allotment'];
    $applicationAmountDesc = $auditInfo['application_amount_desc'];
    $documentationReviewDesc = $auditInfo['documentation_review_desc'];
    $certificationRecertificationDesc = $auditInfo['certification_recertification_desc'];
    $maintenanceCertificationDesc = $auditInfo['maintenance_certification_desc'];
    $reviewer = $auditInfo['reviewer_name'];
    $mainProducts = $auditInfo['main_Products'];
    $mainRawMaterial = $auditInfo['main_RawMaterial'];
    $operationInvolved = $auditInfo['operation_Involved'];
    $sourcesProduct = $auditInfo['sources_Product'];
    $complicatedLogistics = $auditInfo['complicated_Logistics'];
    $applicationfor2 = $auditInfo['applicationfor2'];
    $applicationfor3 = $auditInfo['applicationfor3'];
    $applicationfor4 = $auditInfo['applicationfor4'];
    $applicationfor0 = $auditInfo['applicationfor0'];
    //0
    $nointegration = $auditInfo['no_integration'];
    $managementreview = $auditInfo['management_review'];
    $integratedmanagement = $auditInfo['integrated_management'];
    $integratedapproach = $auditInfo['integrated_approach'];
    $integratedresponsibilities = $auditInfo['integrated_responsibilities'];
    //2
    $environmentalClearance = $auditInfo['environmental_Clearance'];
    $hazardousMaterials = $auditInfo['hazardous_Materials'];
    $adverseImpact = $auditInfo['adverse_Impact'];
    $sensitiveLocation = $auditInfo['sensitive_Location'];
    $archaeologicalSite = $auditInfo['archaeological_Site'];
    $adverseHistory = $auditInfo['adverse_History'];
    //3
    $statutoryHealthandsafety = $auditInfo['statutory_Healthandsafety'];
    $hazardousDangerousSubstances = $auditInfo['hazardousDangerous_Substances'];
    $healthAndSafetyRisks = $auditInfo['healthAndSafety_Risks'];
    $temporarysites = $auditInfo['temporary_sites'];
    $accidentsRates = $auditInfo['accidents_Rates'];
    $SitesPublicPresent = $auditInfo['Sites_PublicPresent'];
    $governmentalRegulators = $auditInfo['governmental_Regulators'];
    //4
    $otherStanderedName = $auditInfo['other_StanderedName'];
    $otherCheckPoint1 = $auditInfo['other_CheckPoint1'];
    $otherCheckPoint2 = $auditInfo['other_CheckPoint2'];
    $otherCheckPoint3 = $auditInfo['other_CheckPoint3'];
    $otherCheckPoint4 = $auditInfo['other_CheckPoint4'];
    $otherCheckPoint5 = $auditInfo['other_CheckPoint5'];
    $otherCheckPoint6 = $auditInfo['other_CheckPoint6'];


    $sql1 = "SELECT * FROM `audit_iaf_nace` WHERE `audit_id` = :auditid";
    $statement = $db->prepare($sql1);
    $statement->bindParam(":auditid", $_POST['auditid']);
    $statement->execute();
    $iafnaceinfo = $statement->fetch();

    $iafcode = $iafnaceinfo['iaf_code'];
    $iafcode_array = explode(",", $iafcode);
    $nacecode = $iafnaceinfo['nace_codes'];
    $nacecode_array = explode(",", $nacecode);
    $naceclass = $iafnaceinfo['nace_class'];
    $naceclass_array = explode(",", $naceclass);
    $combined_strings = [];
    for ($i = 0; $i < count($iafcode_array); $i++) {
    $combined_strings[] = $iafcode_array[$i] . "/" . $nacecode_array[$i] . "/" . $naceclass_array[$i];
    }
    $combined_string_str = implode(', ', $combined_strings);

    if ($auditInfo['assessment_certification'] == "yes") {
        $yes = "√";
    } else if ($auditInfo['assessment_certification'] == "no") {
        $no = "√";
    }

    if ($auditInfo['adequate_resource'] == "yes") {
        $yes1 = "√";
    } else if ($auditInfo['adequate_resource'] == "no") {
        $no1 = "√";
    }

    if ($auditInfo['product_service'] == "yes") {
        $yes2 = "√";
    } else if ($auditInfo['product_service'] == "no") {
        $no2 = "√";
    }

    if ($auditInfo['scope'] == "yes") {
        $yes3 = "√";
    } else if ($auditInfo['scope'] == "no") {
        $no3 = "√";
    }

    if ($auditInfo['competent_team'] == "yes") {
        $yes4 = "√";
    } else if ($auditInfo['competent_team'] == "no") {
        $no4 = "√";
    }

    if ($auditInfo['standard_clause'] == "yes") {
        $yes5 = "√";
    } else if ($auditInfo['standard_clause'] == "no") {
        $no5 = "√";
    }

    if ($auditInfo['risk_statutory'] == "yes") {
        $yes6 = "√";
    } else if ($auditInfo['risk_statutory'] == "no") {
        $no6 = "√";
    }

    if ($auditInfo['risk_conflict'] == "yes") {
        $yes7 = "√";
    } else if ($auditInfo['risk_conflict'] == "no") {
        $no7 = "√";
    }

    $conclusion = $auditInfo['conclusion'];
    $conclusionborc = $auditInfo['conclusionborc'];
    $multisite_eligibility = $auditInfo['multisite_eligibility'];
}

// Extend the TCPDF class to create custom Header and Footer

class MYPDF extends TCPDF {

    protected $processId = 0;
    protected $header = '';
    protected $footer = '';
    static $errorMsg = '';
    protected $count = '1';

    public function setInfo($info) {
        $this->count = $count;
    }

    //Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        $this->Image($image_file, 17, 5, 43, '', 'JPEG', '', 'T', false, 500, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 17.5);
        // Title
        $this->SetXY(60, 10);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetXY(60, 18);
        $this->SetFont('helvetica', 'B', 12);
        //$this->Ln(14);
        //SubTitle
        $this->Cell(0, 25, 'REVIEW OF AUDIT APPLICATION', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->writeHTMLCell($w = '', $h = '', $x = '', $y = '', $this->header, $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = 'L', $autopadding = true);
        $this->SetLineStyle(array('width' => 0.40, 'color' => array(0, 0, 0)));
//        $this->Line(5, 5, $this->getPageWidth() - 5, 5);
//
//        $this->Line($this->getPageWidth() - 5, 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, $this->getPageHeight() - 5, $this->getPageWidth() - 5, $this->getPageHeight() - 5);
//        $this->Line(5, 5, 5, $this->getPageHeight() - 5);
    }

    // Page footer

    public function Footer() {
        $html = '<font size="9"><table border="1"  cellpadding="4" cellspacing="0">';
        $html .= '<tr><td colspan="1">Form No.F 7.02</td>';
        $html .= '<td colspan="2">Rev.:01, Date: 01.12.2016</td>';
        $html .= '<td colspan="2">Issue.: 01, Date: 01.12.2016</td>';
        $html .= '<td colspan="1">[P a g e ' . $this->count . ' of 3]</td></tr>';
        $html .= '</table></font>';
        // Position at 15 mm from bottom
        $this->SetXY(17, -10);
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
//        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->writeHTMLCell(0, 0, '', '', $html, 0, 0, false, "L", true);
        $this->count = $this->count + 1;
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Review Of Audit Application');
$pdf->SetSubject('AITF');
$pdf->SetKeywords('AITF, PDF, example, test, guide');
// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, 22, 5);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}
// ---------------------------------------------------------
// set font
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

//html content Start Here
$html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9">
    <table border="1"  cellpadding="4" cellspacing="0" >
<tr  bgcolor="#DCDCDC">
     <td align="" colspan="15"><b><u>Input(From Audit application- F 7.01)</u></b></td>     
</tr>
  <tr>
        <td colspan="15">
        <br><span>Application Date:- </span>  <span>' . date("d.m.Y", strtotime($appDate)) . '</span>      
        </td>
</tr>        
<tr>
        <td colspan="15">
        <br><span>Application Received Date:- </span>  <span>' . date("d.m.Y", strtotime($appRcvDate)) . '</span>      
        </td>
</tr>        
<tr>
        <td colspan="15">
        <br><span>Name of the Organisation:-</span> <span>' . $orgName . '</span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Legal Status :- </span> <span>' . $legalStatus . '</span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Industry  Classification:- </span> <span>' . $industury_classfication . '</span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Whether part of large group: <span> </span>' . $legal_group . '</span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>No. site/s cover under audit: </span> <span>' . $n_location . '</span>       
        </td>
</tr>        
<tr>
        <td colspan="15">
        <br><span>No. Employees: Permanent/Temporary/Contract/Seasonal: </span> <span>' . $n_employee . '</span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>No. of  Shifts: </span> <span>' . $n_shift . '</span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Products / Services: </span> <span>' . $product . '</span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Externally provided processes: <span> </span>' . $process . '</span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Certification Required for standard: </span> <span>' . $certification . '</span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Accreditation Requirement: </span><span>' . $accereditation_requirement . ' </span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Consultancy services or in-house training was performed by whom and period of performed?: </span>
        <br><span>' . $consultancy_services . '</span>
        </td>
</tr>    
<tr  bgcolor="#DCDCDC">
        <td align="" colspan="15"><b><u>General analysis &amp; requirement</u></b></td>     
</tr>
<tr>
        <td colspan="15">
        <br><span>Non Applicability of standard: </span> <span> ' . $standard_clauses . '</span>       
        </td>
</tr>        
<tr>
        <td colspan="15">
        <br><span>Technical  Area: </span> <span>' . $naceclass . ' </span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Any Legal Obligations: </span> <span> ' . $legal_obligation . '   </span>              
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Conflict of Interest:  </span> <span> ' . $conflict_interest . '</span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Is there any Statutory / Regulatory requirements: </span> <span> ' . $statutory_requirement . '</span>       
        </td>
</tr>    

<tr>
        <td colspan="15">
        <br><span>Is there any customer specific requirement: </span> <span>' . $customer_requirement . ' </span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Organisation mfg/service risk involved(High/Medium/low) / Class:</span> <span>' . $org_involved . ' </span>       
        </td>
</tr>    
<tr>
        <td colspan="15">
        <br><span>Any other requirements: </span> <span>' . $other_requirement . ' </span>
        </td>
</tr> 
</table>
</font></p>';
$pdf->writeHTML($html, true, false, true, false, '');
    $html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9">
    <table border="1"  cellpadding="4" cellspacing="0" >
<tr  bgcolor="#DCDCDC">
     <td align="" colspan="15"><b><u>Annexture 1 - QMS (Basic Information and also applicable for Quality Management Certification)</u></b></td>     
</tr>
<tr>
        <td colspan="15">
        <br><span>Main products: </span> <span>' . $mainProducts . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Main raw materials used or processed: </span> <span>' . $mainRawMaterial . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Operations involved in the service / manufacturing of your product: </span> <span>' . $operationInvolved . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Processes which are provided by external sources related to the product / service: </span> <span>' . $sourcesProduct . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Any complicated logistics involvingmore than one building or location where workis carriedout: </span> <span>' . $complicatedLogistics . ' </span>
        </td>
</tr> 
</table>
</font></p>';
$pdf->writeHTML($html, true, false, true, false, '');

if ($applicationfor0 == "Yes"){
    $html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9">
    <table border="1"  cellpadding="4" cellspacing="0" >
<tr  bgcolor="#DCDCDC">
     <td align="" colspan="15"><b><u>LEVEL OF INTEGRATION IN CASE OF IMS</u></b></td>     
</tr>
<tr>
        <td colspan="15">
        <br><span>No integration : </span> <span>' . $nointegration . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>An integrated approach to internal audits and management review : </span> <span>' . $managementreview . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Integrated management system documentation to a good level of development together with an integrated approach to policy and objectives : </span> <span>' . $integratedmanagement . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Integrated approach towards management system planning and processes : </span> <span>' . $integratedapproach . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Integrated approach towards improvement mechanisms together with unified management commitment and responsibilities : </span> <span>' . $integratedresponsibilities . ' </span>
        </td>
</tr> 
</table>
</font></p>';
$pdf->writeHTML($html, true, false, true, false, '');

}
if ($applicationfor2 == "Yes"){
    $html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9">
    <table border="1"  cellpadding="4" cellspacing="0" >
<tr  bgcolor="#DCDCDC">
     <td align="" colspan="15"><b><u>ANNEXTURE 2 – EMS (APPLICABLE FOR EMS CERTIFICATION)</u></b></td>     
</tr>
<tr>
        <td colspan="15">
        <br><span>Any Statutory Regulatory Requirements related to Environmental clearance for the product manufactured by organization : </span> <span>' . $environmentalClearance . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Any Hazardous Raw / Processing materials used : </span> <span>' . $hazardousMaterials . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Any adverse impact identified on the environment due to operations involved in the service / manufacturing processes : </span> <span>' . $adverseImpact . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Organisation’slocation(s) particularly environmentallysensitivetoany conditions : </span> <span>' . $sensitiveLocation . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Any nearby e.g. archaeological site, listedbuilding,adjacenttosensitivelocationsetc : </span> <span>' . $archaeologicalSite . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Any history of adverseperformance or operations where anemergency condition would result in asignificantenvironmentalevent : </span> <span>' . $adverseHistory . ' </span>
        </td>
</tr> 
</table>
</font></p>';
$pdf->writeHTML($html, true, false, true, false, '');

}
if ($applicationfor3 == "Yes"){
    $html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9">
    <table border="1"  cellpadding="4" cellspacing="0" >
<tr  bgcolor="#DCDCDC">
     <td align="" colspan="15"><b><u>ANNEXTURE 3 – OHSMS (APPLICABLE FOR OHSMS CERTIFICATION)</u></b></td>     
</tr>
<tr>
        <td colspan="15">
        <br><span>Statutory Regulatory Requirements related to Health and safety for the product manufactured by organization : </span> <span>' . $statutoryHealthandsafety . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Any Hazardous raw / Processing material, dangerous substances used/ Present at the site : </span> <span>' . $hazardousDangerousSubstances . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Identify the personnel’s health and safety risks due to operations involved in the service / manufacturing Processes : </span> <span>' . $healthAndSafetyRisks . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Are there activities carried out on site,temporary sites or sites notunder your control : </span> <span>' . $temporarysites . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Do you have higher than industry average rates of accidents,diseaseorabsence : </span> <span>' . $accidentsRates . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Do you have members of the public present atyour sites in large numbers? (e.g. hospitals,schools, public transporthubs) : </span> <span>' . $SitesPublicPresent . ' </span>
        </td>
</tr>
<tr>
        <td colspan="15">
        <br><span>Are you subject to mandatory visits (audits) by governmental regulators : </span> <span>' . $governmentalRegulators . ' </span>
        </td>
</tr>  
</table>
</font></p>';
$pdf->writeHTML($html, true, false, true, false, '');

}
if ($applicationfor4 == "Yes"){
    $html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9">
    <table border="1"  cellpadding="4" cellspacing="0" >
<tr  bgcolor="#DCDCDC">
     <td align="" colspan="15"><b><u>ANNEXTURE 4 –  (ANY Other Standerd/s)</u></b></td>     
</tr>
<tr>
        <td colspan="15">
        <br><span>Specify the standerd/s : </span> <span>' . $otherStanderedName . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Checkpoint 1 : </span> <span>' . $otherCheckPoint1 . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Checkpoint 2 : </span> <span>' . $otherCheckPoint2 . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Checkpoint 3 : </span> <span>' . $otherCheckPoint3 . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Checkpoint 4 : </span> <span>' . $otherCheckPoint4 . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Checkpoint 5 : </span> <span>' . $otherCheckPoint5 . ' </span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br><span>Checkpoint 6 : </span> <span>' . $otherCheckPoint6 . ' </span>
        </td>
</tr> 
</table>
</font></p>';
$pdf->writeHTML($html, true, false, true, false, '');

}




$html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9">
    <table border="1"  cellpadding="4" cellspacing="0" >
<tr  bgcolor="#DCDCDC">
     <td align="" colspan="15"><b><u>Feasibility Analysis</u></b></td>     
</tr>
<tr>
        <td colspan="15">
        <br><u>Feasibility &amp; Risk Analysis Considerations</u>
      <br><br>AITF team considered the following questions, intended to be all-inclusive in performing certification 
      assessment service. The audit application form and/or Information provided by client have been used as a basis 
      for analyse.  All "no" answers are supported with comments identifying our concerns and/or proposed changes to 
      enable requirements with the decision of accepting/Refusing application.
        </td>
</tr> 
<tr>
        <td colspan="1" align="center">Sr. </td>
        <td colspan="10" align="center">CONSIDERATION : </td>
        <td colspan="2" align="center">YES </td>
        <td colspan="2" align="center">NO</td>
</tr> 
<tr>
      <td colspan="1" align="center">1. </td>
      <td colspan="10" >Is AITF having adequate capacity for providing assessment & certification service.</td>
      <td colspan="2" align="center"> <span> ' . $yes . '</span></td>
      <td colspan="2" align="center"> <span>' . $no . ' </span> </td>
</tr> 
<tr>
      <td colspan="1" align="center">2. </td>
      <td colspan="10" >Does AITF having adequate resources to render the service.</td>
      <td colspan="2" align="center"><span> ' . $yes1 . '</span>  </td>
      <td colspan="2" align="center"><span>' . $no1 . ' </span> </td>
</tr> 
<tr>
      <td colspan="1" align="center">3. </td>
      <td colspan="10" >Is product/service (Scope) adequately defined in application to enable feasibility.</td>
      <td colspan="2" align="center"><span> ' . $yes2 . '</span>  </td>
      <td colspan="2" align="center"><span>' . $no2 . ' </span> </td>
</tr> 
<tr>
      <td colspan="1" align="center">4. </td>
      <td colspan="10" >Within scope.</td>
      <td colspan="2" align="center"><span> ' . $yes3 . '</span> </td>
      <td colspan="2" align="center"><span> ' . $no3 . '</span>  </td>
</tr> 
<tr>
      <td colspan="1" align="center">5. </td>
      <td colspan="10" >AITF having scope auditor including competent team for the certification decision.</td>
      <td colspan="2" align="center"><span> ' . $yes4 . '</span>   </td>
      <td colspan="2" align="center"> <span> ' . $no4 . '</span> </td>
</tr> 
<tr>
      <td colspan="1" align="center">6. </td>
      <td colspan="10" >Non Applicability of standard justified. (Subject to verification during stage I audit)</td>
      <td colspan="2" align="center"><span> ' . $yes5 . '</span>   </td>
      <td colspan="2" align="center"><span> ' . $no5 . '</span>  </td>
</tr> 
<tr>
      <td colspan="1" align="center">7. </td>
      <td colspan="10" >Verified that there is no any risk involved regarding statutory, regulatory, and legal obligations.</td>
      <td colspan="2" align="center"><span> ' . $yes6 . '</span>   </td>
      <td colspan="2" align="center"><span> ' . $no6 . '</span>  </td>
</tr> 
<tr>
      <td colspan="1" align="center">8. </td>
      <td colspan="10" >Verified that there is no risk involved regarding conflict of interest or any  breach of AITF policies</td>
      <td colspan="2" align="center"><span> ' . $yes7 . '</span> </td>
      <td colspan="2" align="center"> <span> ' . $no7 . '</span> </td>
</tr> 
<tr>
        <td colspan="15">
        <br><u>Conclusion</u>: <span> ' . $conclusion . '</span>
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br>A. Feasible: Certification service can perform.
        <br>B. Feasible but changes recommended to perform the service(see attached)
        <br>C. Not Feasible AITF refuse to application
        </td>
</tr> 
<tr>
        <td colspan="15">
        <br>In the event of conclusion(B or C)<u>comments</u>: <span> ' . $conclusionborc . '</span>
        <br></td>
</tr>
</table>
</font></p>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//html content Start Here

$html = '<p style="text-align: justify;text-justify: inter-word;"><font size="9">
            <table border="1"  cellpadding="4" cellspacing="0" >
                <tr>
                    <td colspan="15">
                        <br><span><b><u>Review In the Event of Multisite with eligibility of sampling creator:</u></b></span>  <span>' . $multisite_eligibility . '</span>      
                    </td>
                </tr>
                <tr  bgcolor="#DCDCDC">
                    <td align="" colspan="15"><b>Review Outcome</b>(for inputsto agreement &amp; quotation)</td>     
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Assessment Standard:</span>  <span>' . $assessment_standard . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Technical  Area :</span>  <span>' . $naceclass .'</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Accreditation:</span>  <span>' . $review_accredition . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Proposed scope of certification: </span>  <span>' . $proposed_scope . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Proposed month/year for Initial Audit/ Recertification Audit: </span>  <span>' . $proposed_month . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Man-days required as per IAF MD 5 : </span>  <span>' . $man_day_req . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Reducing Factor,Man-days: </span>  <span>' . $reducing_factor . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Justification of Reducing: </span>  <span>' . $justification_reducing . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Additive Factor If any, Man-days: </span>  <span>' . $additive_factor . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span><u>Actual Effective man-days required:</u></span>  <span>' . $actual_effective . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Requirement of Technical Expert: </span>  <span>' . $technical_req . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Were Travel Logistics Discussed &amp; finalized: </span>  <span>' . $travel_logistic . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Quotation Amount Cost (INR.): (Re/Certification+1 st surveillance+ 2 nd Surveillance):</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="10">
                        <br>'.$applicationAmountDesc.'
                    </td>
                    <td colspan="5">
                        <br>@Rs. <span>' . $refundable_amount . '</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="10">
                        <br>'.$documentationReviewDesc.'
                    </td>
                    <td colspan="5">
                        <br>@Rs. <span>' . $audit_stage_i . '</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="10">
                        <br>'.$certificationRecertificationDesc.'
                    </td>
                    <td colspan="5">
                        <br>@Rs. <span>' . $audit_stage_iI . '</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="10">
                        <br><b>Total Cost of Initial Assessment</b>
                    </td>
                    <td colspan="5">
                        <br>@Rs. <span>' . $initial_assessment . '</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="10">
                        <br>'.$maintenanceCertificationDesc.'
                    </td>
                    <td colspan="5">
                        <br>@Rs. <span>' . $certification_maintenance . '</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Name of reviewer: <b>'.$reviewer.'</b></span><br><br><br><span>Date:' . date("d.m.Y", strtotime($review_date)) . '</span>      
                    </td>
                </tr>
                <tr>
                    <td colspan="15">
                        <br><span>Allotment of Agreement Number : </span><span>' . $aggrement_allotment . '</span><br><br><br><span>Date:</span>
                    </td>
                </tr>
            </table>
        </font></p>';


// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//Close and output PDF document
if (ob_get_length()) {
    ob_end_clean();
} else {
    @ob_end_clean();
}
$pdf->Output('ReviewOfAuditApplication.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

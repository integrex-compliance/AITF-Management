<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
include_once './include/customer_recycle_helper.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_REQUEST['permanent_service_id']) && !empty($_REQUEST['permanent_service_id'])) {
    try {
        aitfPermanentlyDeleteService($db, $_REQUEST['permanent_service_id']);
        echo 'success';
        exit;
    } catch (Exception $ex) {
        echo $ex->getMessage();
        exit;
    }
}

// fetch using condition 

if (isset($_GET['ca_service_id']) && !empty($_GET['ca_service_id']) && isset($_GET['ca_customer_id']) && !empty($_GET['ca_customer_id'])) {
    try {
        if (!isset($_GET['permanent']) || $_GET['permanent'] != '1') {
            $svcId = !empty($_GET['service_id']) ? $_GET['service_id'] : null;
            if (!$svcId && !empty($_GET['ca_service_id'])) {
                $st = $db->prepare("SELECT service FROM service_type_ca WHERE ca_id = :id");
                $st->execute([':id' => $_GET['ca_service_id']]);
                $svcId = $st->fetchColumn();
            }
            aitfSoftDeleteService($db, $svcId, $_GET['ca_customer_id'], 'CA');
            echo 'success';
            exit;
        }

        $stage1AuditId = "SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ";
        $statement = $db->prepare($stage1AuditId);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->execute();
        $stage1Id = $statement->fetch();

        if ($stage1Id) {
            $dltStage1AuditDetails = "DELETE FROM stage_audit_one WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditDetails);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();

            $dltStage1AuditId = "DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditId);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();
        }

        $scheduleAudit1id = "SELECT audit_schedule_id FROM audit_schedule_id WHERE serviceca_id = :service and customer_ca_id = :customer";
        $statement = $db->prepare($scheduleAudit1id);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->execute();
        $audit1Id = $statement->fetch();

        if ($audit1Id) {
            $dltSchedule1AuditDetail = "DELETE FROM audit_schedule WHERE audit_scheduleid = :id";
            $statement = $db->prepare($dltSchedule1AuditDetail);
            $statement->bindParam(':id', $audit1Id['audit_schedule_id']);
            $statement->execute();

            $dltScheduleAuditId = "DELETE FROM audit_schedule_id WHERE audit_schedule_id = :id";
            $statement = $db->prepare($dltScheduleAuditId);
            $statement->bindParam(':id', $audit1Id['audit_schedule_id']);
            $statement->execute();
        }

        $stage2notification = "SELECT audit_stageii_id FROM `stage_twoaudit_id` WHERE service_id = :service AND customer_id = :customer";
        $statement = $db->prepare($stage2notification);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->execute();
        $stage2Id = $statement->fetch();

        if ($stage2Id) {
            $dltnotification2details = "DELETE FROM `stage_audit_two` WHERE `audit_stageii_id` = :id";
            $statement = $db->prepare($dltnotification2details);
            $statement->bindParam(':id', $stage2Id['audit_stageii_id']);
            $statement->execute();
            
            $dltnotification2Id = "DELETE FROM `stage_twoaudit_id` WHERE `audit_stageii_id` = :id";
            $statement = $db->prepare($dltnotification2Id);
            $statement->bindParam(':id', $stage2Id['audit_stageii_id']);
            $statement->execute();
        }

        $scheduleAudit2id = "SELECT audit_scheduleii_id FROM audit_scheduleii_id WHERE ca_service = :service and ca_customer = :customer";
        $statement = $db->prepare($scheduleAudit2id);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->execute();
        $audit2Id = $statement->fetch();

        if ($audit2Id) {
            $dltSchedule2AuditDetail = "DELETE FROM audit_scheduleii WHERE scheduleii_id = :id";
            $statement = $db->prepare($dltSchedule2AuditDetail);
            $statement->bindParam(':id', $audit2Id['audit_scheduleii_id']);
            $statement->execute();

            $dltSchedule2id = "DELETE FROM audit_scheduleii_id WHERE audit_scheduleii_id = :id";
            $statement = $db->prepare($dltSchedule2id);
            $statement->bindParam(':id', $audit2Id['audit_scheduleii_id']);
            $statement->execute();
        }

        $stage1Annex = "SELECT stage_i_id FROM stage_i_audit_id WHERE ca_service_id = :service and customer_id = :customer ";
        $statement = $db->prepare($stage1Annex);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->execute();
        $stage1Annexid = $statement->fetch();

        if ($stage1Annexid) {
            $dltAnnex1Details = "DELETE FROM stage_i_audit WHERE ca_service_audit = :id ";
            $statement = $db->prepare($dltAnnex1Details);
            $statement->bindParam(':id', $stage1Annexid['stage_i_id']);
            $statement->execute();

            $dltAnnex1id = "DELETE FROM stage_i_audit_id WHERE stage_i_id = :id";
            $statement = $db->prepare($dltAnnex1id);
            $statement->bindParam(':id', $stage1Annexid['stage_i_id']);
            $statement->execute();
        }

        $stage2Annex = "SELECT stage_ii_id FROM stage_ii_audit_id WHERE ca_service_id = :service and customer_id = :customer";
        $statement = $db->prepare($stage2Annex);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->execute();
        $stage2Annexid = $statement->fetch();

        if ($stage2Annexid) {
            $dltStageAnnex2Detail = "DELETE FROM stage_ii_audit WHERE ca_service_audit_ii = :id ";
            $statement = $db->prepare($dltStageAnnex2Detail);
            $statement->bindParam(':id', $stage2Annexid['stage_ii_id']);
            $statement->execute();

            $dltStageAnnex2id = "DELETE FROM stage_ii_audit_id WHERE stage_ii_id = :id";
            $statement = $db->prepare($dltStageAnnex2id);
            $statement->bindParam(':id', $stage2Annexid['stage_ii_id']);
            $statement->execute();
        }

        $cdcId = "SELECT crr_id FROM cdc_report_review WHERE ca_service_id = :service and customer_id = :customer";
        $statement = $db->prepare($cdcId);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->execute();
        $cdc_id = $statement->fetch();

        if ($cdc_id) {
            $dltCdc = "DELETE FROM cdc_report_review WHERE crr_id = :id";
            $statement = $db->prepare($dltCdc);
            $statement->bindParam(':id', $cdc_id['crr_id']);
            $statement->execute();
        }

        $certificate = "SELECT certificate_id FROM `certificate_print` where service_id = :service and customer_id = :customer";
        $statement = $db->prepare($certificate);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->execute();
        $cert_id = $statement->fetch();

        if ($cert_id) {
            $dltCertificate = "DELETE FROM `certificate_print` WHERE `certificate_id` = :id";
            $statement = $db->prepare($dltCertificate);
            $statement->bindParam(':id', $cert_id['certificate_id']);
            $statement->execute();
        }

        $cf1Detail = "SELECT control_id FROM control_formati_id WHERE caservice_id = :service and customer_id = :customer";
        $statement = $db->prepare($cf1Detail);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->execute();
        $cf1Id = $statement->fetch();

        if ($cf1Id) {
            $dltCF1Detail = "DELETE FROM control_formati WHERE control_id = :id";
            $statement = $db->prepare($dltCF1Detail);
            $statement->bindParam(':id', $cf1Id['control_id']);
            $statement->execute();

            $dltCF1Id = "DELETE FROM control_formati_id WHERE control_id = :id";
            $statement = $db->prepare($dltCF1Id);
            $statement->bindParam(':id', $cf1Id['control_id']);
            $statement->execute();
        }

        $customerSurvayDetail = "SELECT survey_id FROM customer_survey_id WHERE ca_serviceid = :service and customer_ca = :customer";
        $statement = $db->prepare($customerSurvayDetail);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->execute();
        $customerSurvayId = $statement->fetch();

        if ($customerSurvayId) {
            $dltSurvayDetail = "DELETE FROM customer_survey WHERE survayid = :id";
            $statement = $db->prepare($dltSurvayDetail);
            $statement->bindParam(':id', $customerSurvayId['survey_id']);
            $statement->execute();

            $dltSurvayId = "DELETE FROM customer_survey_id WHERE survey_id = :id";
            $statement = $db->prepare($dltSurvayId);
            $statement->bindParam(':id', $customerSurvayId['survey_id']);
            $statement->execute();
        }

        $cf2Detail = "SELECT control2_id FROM control_formatii_id WHERE caservice_id = :service and customer_id = :customer";
        $statement = $db->prepare($cf2Detail);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->execute();
        $cf2Id = $statement->fetch();

        if ($cf2Id) {
            $dltcf2Detail = "DELETE FROM control_formatii WHERE control2_id = :id";
            $statement = $db->prepare($dltcf2Detail);
            $statement->bindParam(':id', $cf2Id['control2_id']);
            $statement->execute();

            $dltCF2id = "DELETE FROM control_formatii_id WHERE control2_id = :id";
            $statement = $db->prepare($dltCF2id);
            $statement->bindParam(':id', $cf2Id['control2_id']);
            $statement->execute();
        }

        $dltCAService = "DELETE FROM service_type_ca WHERE ca_id = :service";
        $statement = $db->prepare($dltCAService);
        $statement->bindParam(':service', $_GET['ca_service_id']);
        $statement->execute();

        $dltcaDates = "DELETE FROM ca_service_dates WHERE service_id=:service_id";
        $statement = $db->prepare($dltcaDates);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        $dltService = "DELETE FROM service WHERE customer_id=:customer AND service_id=:service_id";
        $statement = $db->prepare($dltService);
        $statement->bindParam(':customer', $_GET['ca_customer_id']);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        $nextsvcupdate = "UPDATE `customer_info` SET `next_service`= 'CA' WHERE `id` = :id";
        $statement = $db->prepare($nextsvcupdate);
        $statement->bindParam(":id", $_GET['ca_customer_id']);
        $statement->execute();

        echo 'success';
    } catch (Exception $ex) {
        echo $ex;
    }
} else if (isset($_GET["ptr_service_id"]) && !empty($_GET["ptr_service_id"]) && isset($_GET["ptr_customer_id"]) && !empty($_GET["ptr_customer_id"])) {
    try {
        if (!isset($_GET['permanent']) || $_GET['permanent'] != '1') {
            $svcId = !empty($_GET['service_id']) ? $_GET['service_id'] : null;
            if (!$svcId && !empty($_GET['ptr_service_id'])) {
                $st = $db->prepare("SELECT service FROM service_type_ptr WHERE prt_id = :id");
                $st->execute([':id' => $_GET['ptr_service_id']]);
                $svcId = $st->fetchColumn();
            }
            aitfSoftDeleteService($db, $svcId, $_GET['ptr_customer_id'], 'PTR');
            echo 'success';
            exit;
        }
        $stage1AuditId = "SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ";
        $statement = $db->prepare($stage1AuditId);
        $statement->bindParam(':service', $_GET['ptr_service_id']);
        $statement->bindParam(':customer', $_GET['ptr_customer_id']);
        $statement->execute();
        $stage1Id = $statement->fetch();

        if ($stage1Id) {
            $dltStage1AuditDetails = "DELETE FROM stage_audit_one WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditDetails);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();

            $dltStage1AuditId = "DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditId);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();
        }

        $sql = "SELECT servelance_id FROM `servelance_1_id` where service_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET['ptr_service_id']);
        $statement->execute();
        $servelance = $statement->fetch();

        if ($servelance) {
            $sql1 = "DELETE FROM `servelance_audit` WHERE `servelance_id` = :id";
            $statement = $db->prepare($sql1);
            $statement->bindParam(":id", $servelance["servelance_id"]);
            $statement->execute();

            $sql2 = "DELETE FROM `servelance_1_id` WHERE `servelance_id` = :id";
            $statement = $db->prepare($sql2);
            $statement->bindParam(":id", $servelance["servelance_id"]);
            $statement->execute();
        }

        $stage1Annex = "SELECT stage_i_id FROM stage_i_audit_id WHERE ca_service_id = :service and customer_id = :customer ";
        $statement = $db->prepare($stage1Annex);
        $statement->bindParam(':service', $_GET['ptr_service_id']);
        $statement->bindParam(':customer', $_GET['ptr_customer_id']);
        $statement->execute();
        $stage1Annexid = $statement->fetch();

        if ($stage1Annexid) {
            $dltAnnex1Details = "DELETE FROM stage_i_audit WHERE ca_service_audit = :id ";
            $statement = $db->prepare($dltAnnex1Details);
            $statement->bindParam(':id', $stage1Annexid['stage_i_id']);
            $statement->execute();

            $dltAnnex1id = "DELETE FROM stage_i_audit_id WHERE stage_i_id = :id";
            $statement = $db->prepare($dltAnnex1id);
            $statement->bindParam(':id', $stage1Annexid['stage_i_id']);
            $statement->execute();
        }

        $dltptrservice = "DELETE FROM `service_type_ptr` WHERE `prt_id` = :id";
        $statement = $db->prepare($dltptrservice);
        $statement->bindParam(":id", $_GET['ptr_service_id']);
        $statement->execute();

        $dltService = "DELETE FROM service WHERE customer_id=:customer AND service_id=:service_id";
        $statement = $db->prepare($dltService);
        $statement->bindParam(':customer', $_GET['ptr_customer_id']);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        echo 'success';
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else if (isset($_GET["cr_service_id"]) && !empty($_GET["cr_service_id"]) && isset($_GET["cr_customer_id"]) && !empty($_GET["cr_customer_id"])) {
    try {
        if (!isset($_GET['permanent']) || $_GET['permanent'] != '1') {
            $svcId = !empty($_GET['service_id']) ? $_GET['service_id'] : null;
            if (!$svcId && !empty($_GET['cr_service_id'])) {
                $st = $db->prepare("SELECT service FROM service_type_cr WHERE cr_id = :id");
                $st->execute([':id' => $_GET['cr_service_id']]);
                $svcId = $st->fetchColumn();
            }
            aitfSoftDeleteService($db, $svcId, $_GET['cr_customer_id'], 'CR');
            echo 'success';
            exit;
        }
        $auditApp = "SELECT audit_id FROM audit_application WHERE service_id = :service ";
        $statement = $db->prepare($auditApp);
        $statement->bindParam(':service', $_GET['cr_service_id']);
        $statement->execute();
        $auditAppId = $statement->fetch();

        if ($auditAppId) {
            $deleteAuditApp = "DELETE FROM audit_application WHERE audit_id = :auditid ";
            $statement = $db->prepare($deleteAuditApp);
            $statement->bindParam(':auditid', $auditAppId['audit_id']);
            $statement->execute();
        }

        $contractDetailId = "SELECT contractid FROM contract_id WHERE ca_service_id = :service and customer_id = :customer";
        $statement = $db->prepare($contractDetailId);
        $statement->bindParam(':service', $_GET['cr_service_id']);
        $statement->bindParam(':customer', $_GET['cr_customer_id']);
        $statement->execute();
        $contractId = $statement->fetch();

        if ($contractId) {
            $contractDelete = "DELETE FROM contract_review WHERE ca_service_contract = :contractid";
            $statement = $db->prepare($contractDelete);
            $statement->bindParam(':contractid', $contractId['contractid']);
            $statement->execute();

            $dltContractId = "DELETE FROM contract_id WHERE contractid = :id ";
            $statement = $db->prepare($dltContractId);
            $statement->bindParam(':id', $contractId['contractid']);
            $statement->execute();
        }

        $certagrement = "SELECT id FROM certification_aggrement WHERE service = :id";
        $statement = $db->prepare($certagrement);
        $statement->bindParam(":id", $_GET['cr_service_id']);
        $statement->execute();
        $certId = $statement->fetch();

        if ($certId) {
            $dltcert = "DELETE FROM `certification_aggrement` WHERE `id` = :id";
            $statement = $db->prepare($dltcert);
            $statement->bindParam(":id", $certId['id']);
            $statement->execute();
        }

        $assessment = "SELECT assessment_id FROM assessment_planning WHERE service_id = :id";
        $statement = $db->prepare($assessment);
        $statement->bindParam(":id", $_GET['cr_service_id']);
        $statement->execute();
        $assessmentId = $statement->fetch();

        if ($assessmentId) {
            $dltAssessment = "DELETE FROM assessment_planning WHERE assessment_id = :id";
            $statement = $db->prepare($dltAssessment);
            $statement->bindParam(":id", $assessmentId["assessment_id"]);
            $statement->execute();
        }

        $dltCrservice = "DELETE FROM `service_type_cr` WHERE `cr_id` = :id";
        $statement = $db->prepare($dltCrservice);
        $statement->bindParam(":id", $_GET['cr_service_id']);
        $statement->execute();

        $dltService = "DELETE FROM service WHERE customer_id=:customer AND service_id=:service_id";
        $statement = $db->prepare($dltService);
        $statement->bindParam(':customer', $_GET['cr_customer_id']);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        echo 'success';
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else if (isset($_GET['sai_service_id']) && !empty($_GET['sai_service_id']) && isset($_GET['sai_customer_id']) && !empty($_GET['sai_customer_id'])) {
    try {
        if (!isset($_GET['permanent']) || $_GET['permanent'] != '1') {
            $svcId = !empty($_GET['service_id']) ? $_GET['service_id'] : null;
            if (!$svcId && !empty($_GET['sai_service_id'])) {
                $st = $db->prepare("SELECT service FROM service_type_sai WHERE sai_id = :id");
                $st->execute([':id' => $_GET['sai_service_id']]);
                $svcId = $st->fetchColumn();
            }
            aitfSoftDeleteService($db, $svcId, $_GET['sai_customer_id'], 'SA-I');
            echo 'success';
            exit;
        }
        $stage1AuditId = "SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ";
        $statement = $db->prepare($stage1AuditId);
        $statement->bindParam(':service', $_GET['sai_service_id']);
        $statement->bindParam(':customer', $_GET['sai_customer_id']);
        $statement->execute();
        $stage1Id = $statement->fetch();

        if ($stage1Id) {
            $dltStage1AuditDetails = "DELETE FROM stage_audit_one WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditDetails);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();

            $dltStage1AuditId = "DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditId);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();
        }

        $sql = "SELECT servelance_id FROM `servelance_1_id` where service_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET['sai_service_id']);
        $statement->execute();
        $servelance = $statement->fetch();

        if ($servelance) {
            $sql1 = "DELETE FROM `servelance_audit` WHERE `servelance_id` = :id";
            $statement = $db->prepare($sql1);
            $statement->bindParam(":id", $servelance["servelance_id"]);
            $statement->execute();

            $sql2 = "DELETE FROM `servelance_1_id` WHERE `servelance_id` = :id";
            $statement = $db->prepare($sql2);
            $statement->bindParam(":id", $servelance["servelance_id"]);
            $statement->execute();
        }

//        $scheduleAudit1id = "SELECT audit_schedule_id FROM audit_schedule_id WHERE serviceca_id = :service and customer_ca_id = :customer";
//        $statement = $db->prepare($scheduleAudit1id);
//        $statement->bindParam(':service', $_GET['sai_service_id']);
//        $statement->bindParam(':customer', $_GET['sai_customer_id']);
//        $statement->execute();
//        $audit1Id = $statement->fetch();
//
//        $dltSchedule1AuditDetail = "DELETE FROM audit_schedule WHERE audit_scheduleid = :id";
//        $statement = $db->prepare($dltSchedule1AuditDetail);
//        $statement->bindParam(':id', $audit1Id['audit_schedule_id']);
//        $statement->execute();
//
//        $dltScheduleAuditId = "DELETE FROM audit_schedule_id WHERE audit_schedule_id = :id";
//        $statement = $db->prepare($dltScheduleAuditId);
//        $statement->bindParam(':id', $audit1Id['audit_schedule_id']);
//        $statement->execute();

        $stage2Annex = "SELECT stage_ii_id FROM stage_ii_audit_id WHERE ca_service_id = :service and customer_id = :customer";
        $statement = $db->prepare($stage2Annex);
        $statement->bindParam(':service', $_GET['sai_service_id']);
        $statement->bindParam(':customer', $_GET['sai_customer_id']);
        $statement->execute();
        $stage2Annexid = $statement->fetch();

        if ($stage2Annexid) {
            $dltStageAnnex2Detail = "DELETE FROM stage_ii_audit WHERE ca_service_audit_ii = :id ";
            $statement = $db->prepare($dltStageAnnex2Detail);
            $statement->bindParam(':id', $stage2Annexid['stage_ii_id']);
            $statement->execute();

            $dltStageAnnex2id = "DELETE FROM stage_ii_audit_id WHERE stage_ii_id = :id";
            $statement = $db->prepare($dltStageAnnex2id);
            $statement->bindParam(':id', $stage2Annexid['stage_ii_id']);
            $statement->execute();
        }

        $cdcId = "SELECT crr_id FROM cdc_report_review WHERE ca_service_id = :service and customer_id = :customer";
        $statement = $db->prepare($cdcId);
        $statement->bindParam(':service', $_GET['sai_service_id']);
        $statement->bindParam(':customer', $_GET['sai_customer_id']);
        $statement->execute();
        $cdc_id = $statement->fetch();

        if ($cdc_id) {
            $dltCdc = "DELETE FROM cdc_report_review WHERE crr_id = :id";
            $statement = $db->prepare($dltCdc);
            $statement->bindParam(':id', $cdc_id['crr_id']);
            $statement->execute();
        }

        $certificate = "SELECT certificate_id FROM `certificate_print` where service_id = :service and customer_id = :customer";
        $statement = $db->prepare($certificate);
        $statement->bindParam(':service', $_GET['sai_service_id']);
        $statement->bindParam(':customer', $_GET['sai_customer_id']);
        $statement->execute();
        $cert_id = $statement->fetch();

        if ($cert_id) {
            $dltCertificate = "DELETE FROM `certificate_print` WHERE `certificate_id` = :id";
            $statement = $db->prepare($dltCertificate);
            $statement->bindParam(':id', $cert_id['certificate_id']);
            $statement->execute();
        }

        $customerSurvayDetail = "SELECT survey_id FROM customer_survey_id WHERE ca_serviceid = :service and customer_ca = :customer";
        $statement = $db->prepare($customerSurvayDetail);
        $statement->bindParam(':service', $_GET['sai_service_id']);
        $statement->bindParam(':customer', $_GET['sai_customer_id']);
        $statement->execute();
        $customerSurvayId = $statement->fetch();

        if ($customerSurvayId) {
            $dltSurvayDetail = "DELETE FROM customer_survey WHERE survayid = :id";
            $statement = $db->prepare($dltSurvayDetail);
            $statement->bindParam(':id', $customerSurvayId['survey_id']);
            $statement->execute();

            $dltSurvayId = "DELETE FROM customer_survey_id WHERE survey_id = :id";
            $statement = $db->prepare($dltSurvayId);
            $statement->bindParam(':id', $customerSurvayId['survey_id']);
            $statement->execute();
        }

        $decrementCount = "UPDATE customer_info SET service_count = service_count - 1 WHERE id = :customer and service_count > 0";
        $statement = $db->prepare($decrementCount);
        $statement->bindParam(':customer', $_GET['sai_customer_id']);
        $statement->execute();

        $dltSaiService = "DELETE FROM service_type_sai WHERE sai_id = :service";
        $statement = $db->prepare($dltSaiService);
        $statement->bindParam(':service', $_GET['sai_service_id']);
        $statement->execute();

        $dltcaDates = "DELETE FROM ca_service_dates WHERE service_id=:service_id";
        $statement = $db->prepare($dltcaDates);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        $dltService = "DELETE FROM service WHERE customer_id=:customer AND service_id=:service_id";
        $statement = $db->prepare($dltService);
        $statement->bindParam(':customer', $_GET['sai_customer_id']);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        $nextsvcupdate = "UPDATE `customer_info` SET `next_service`= 'SA-I' WHERE `id` = :id";
        $statement = $db->prepare($nextsvcupdate);
        $statement->bindParam(":id", $_GET['sai_customer_id']);
        $statement->execute();

        echo 'success';
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET['saii_service_id']) && !empty($_GET['saii_service_id']) && isset($_GET['saii_customer_id']) && !empty($_GET['saii_customer_id'])) {
    try {
        if (!isset($_GET['permanent']) || $_GET['permanent'] != '1') {
            $svcId = !empty($_GET['service_id']) ? $_GET['service_id'] : null;
            if (!$svcId && !empty($_GET['saii_service_id'])) {
                $st = $db->prepare("SELECT service FROM service_type_saii WHERE saii_id = :id");
                $st->execute([':id' => $_GET['saii_service_id']]);
                $svcId = $st->fetchColumn();
            }
            aitfSoftDeleteService($db, $svcId, $_GET['saii_customer_id'], 'SA-II');
            echo 'success';
            exit;
        }
        $stage1AuditId = "SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ";
        $statement = $db->prepare($stage1AuditId);
        $statement->bindParam(':service', $_GET['saii_service_id']);
        $statement->bindParam(':customer', $_GET['saii_customer_id']);
        $statement->execute();
        $stage1Id = $statement->fetch();

        if ($stage1Id) {
            $dltStage1AuditDetails = "DELETE FROM stage_audit_one WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditDetails);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();

            $dltStage1AuditId = "DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditId);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();
        }

        $sql = "SELECT servelance_id FROM `servelance_1_id` where service_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET['saii_service_id']);
        $statement->execute();
        $servelance = $statement->fetch();

        if ($servelance) {
            $sql1 = "DELETE FROM `servelance_audit` WHERE `servelance_id` = :id";
            $statement = $db->prepare($sql1);
            $statement->bindParam(":id", $servelance["servelance_id"]);
            $statement->execute();

            $sql2 = "DELETE FROM `servelance_1_id` WHERE `servelance_id` = :id";
            $statement = $db->prepare($sql2);
            $statement->bindParam(":id", $servelance["servelance_id"]);
            $statement->execute();
        }

        $stage2Annex = "SELECT stage_ii_id FROM stage_ii_audit_id WHERE ca_service_id = :service and customer_id = :customer";
        $statement = $db->prepare($stage2Annex);
        $statement->bindParam(':service', $_GET['saii_service_id']);
        $statement->bindParam(':customer', $_GET['saii_customer_id']);
        $statement->execute();
        $stage2Annexid = $statement->fetch();

        if ($stage2Annexid) {
            $dltStageAnnex2Detail = "DELETE FROM stage_ii_audit WHERE ca_service_audit_ii = :id ";
            $statement = $db->prepare($dltStageAnnex2Detail);
            $statement->bindParam(':id', $stage2Annexid['stage_ii_id']);
            $statement->execute();

            $dltStageAnnex2id = "DELETE FROM stage_ii_audit_id WHERE stage_ii_id = :id";
            $statement = $db->prepare($dltStageAnnex2id);
            $statement->bindParam(':id', $stage2Annexid['stage_ii_id']);
            $statement->execute();
        }

        $cdcId = "SELECT crr_id FROM cdc_report_review WHERE ca_service_id = :service and customer_id = :customer";
        $statement = $db->prepare($cdcId);
        $statement->bindParam(':service', $_GET['saii_service_id']);
        $statement->bindParam(':customer', $_GET['saii_customer_id']);
        $statement->execute();
        $cdc_id = $statement->fetch();

        if ($cdc_id) {
            $dltCdc = "DELETE FROM cdc_report_review WHERE crr_id = :id";
            $statement = $db->prepare($dltCdc);
            $statement->bindParam(':id', $cdc_id['crr_id']);
            $statement->execute();
        }

        $certificate = "SELECT certificate_id FROM `certificate_print` where service_id = :service and customer_id = :customer";
        $statement = $db->prepare($certificate);
        $statement->bindParam(':service', $_GET['saii_service_id']);
        $statement->bindParam(':customer', $_GET['saii_customer_id']);
        $statement->execute();
        $cert_id = $statement->fetch();

        if ($cert_id) {
            $dltCertificate = "DELETE FROM `certificate_print` WHERE `certificate_id` = :id";
            $statement = $db->prepare($dltCertificate);
            $statement->bindParam(':id', $cert_id['certificate_id']);
            $statement->execute();
        }

        $customerSurvayDetail = "SELECT survey_id FROM customer_survey_id WHERE ca_serviceid = :service and customer_ca = :customer";
        $statement = $db->prepare($customerSurvayDetail);
        $statement->bindParam(':service', $_GET['saii_service_id']);
        $statement->bindParam(':customer', $_GET['saii_customer_id']);
        $statement->execute();
        $customerSurvayId = $statement->fetch();

        if ($customerSurvayId) {
            $dltSurvayDetail = "DELETE FROM customer_survey WHERE survayid = :id";
            $statement = $db->prepare($dltSurvayDetail);
            $statement->bindParam(':id', $customerSurvayId['survey_id']);
            $statement->execute();

            $dltSurvayId = "DELETE FROM customer_survey_id WHERE survey_id = :id";
            $statement = $db->prepare($dltSurvayId);
            $statement->bindParam(':id', $customerSurvayId['survey_id']);
            $statement->execute();
        }

        $dltSaiiService = "DELETE FROM service_type_saii WHERE saii_id = :service";
        $statement = $db->prepare($dltSaiiService);
        $statement->bindParam(':service', $_GET['saii_service_id']);
        $statement->execute();

        $dltcaDates = "DELETE FROM ca_service_dates WHERE service_id=:service_id";
        $statement = $db->prepare($dltcaDates);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        $dltService = "DELETE FROM service WHERE customer_id=:customer AND service_id=:service_id";
        $statement = $db->prepare($dltService);
        $statement->bindParam(':customer', $_GET['saii_customer_id']);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        $nextsvcupdate = "UPDATE `customer_info` SET `next_service`= 'SA-II' WHERE `id` = :id";
        $statement = $db->prepare($nextsvcupdate);
        $statement->bindParam(":id", $_GET['saii_customer_id']);
        $statement->execute();

        echo 'success';
    } catch (Exception $ex) {
        echo $ex;
    }
} else if (isset($_GET['rc_service_id']) && !empty($_GET['rc_service_id']) && isset($_GET['rc_customer_id']) && !empty($_GET['rc_customer_id'])) {
    try {
        if (!isset($_GET['permanent']) || $_GET['permanent'] != '1') {
            $svcId = !empty($_GET['service_id']) ? $_GET['service_id'] : null;
            if (!$svcId && !empty($_GET['rc_service_id'])) {
                $st = $db->prepare("SELECT service FROM service_type_rc WHERE rc_id = :id");
                $st->execute([':id' => $_GET['rc_service_id']]);
                $svcId = $st->fetchColumn();
            }
            aitfSoftDeleteService($db, $svcId, $_GET['rc_customer_id'], 'RC');
            echo 'success';
            exit;
        }
        $stage1AuditId = "SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ";
        $statement = $db->prepare($stage1AuditId);
        $statement->bindParam(':service', $_GET['rc_service_id']);
        $statement->bindParam(':customer', $_GET['rc_customer_id']);
        $statement->execute();
        $stage1Id = $statement->fetch();

        if ($stage1Id) {
            $dltStage1AuditDetails = "DELETE FROM stage_audit_one WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditDetails);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();

            $dltStage1AuditId = "DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditId);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();
        }

        $scheduleAudit2id = "SELECT audit_scheduleii_id FROM audit_scheduleii_id WHERE ca_service = :service and ca_customer = :customer";
        $statement = $db->prepare($scheduleAudit2id);
        $statement->bindParam(':service', $_GET['rc_service_id']);
        $statement->bindParam(':customer', $_GET['rc_customer_id']);
        $statement->execute();
        $audit2Id = $statement->fetch();

        if ($audit2Id) {
            $dltSchedule2AuditDetail = "DELETE FROM audit_scheduleii WHERE scheduleii_id = :id";
            $statement = $db->prepare($dltSchedule2AuditDetail);
            $statement->bindParam(':id', $audit2Id['audit_scheduleii_id']);
            $statement->execute();

            $dltSchedule2id = "DELETE FROM audit_scheduleii_id WHERE audit_scheduleii_id = :id";
            $statement = $db->prepare($dltSchedule2id);
            $statement->bindParam(':id', $audit2Id['audit_scheduleii_id']);
            $statement->execute();
        }

        $stage2Annex = "SELECT stage_ii_id FROM stage_ii_audit_id WHERE ca_service_id = :service and customer_id = :customer";
        $statement = $db->prepare($stage2Annex);
        $statement->bindParam(':service', $_GET['rc_service_id']);
        $statement->bindParam(':customer', $_GET['rc_customer_id']);
        $statement->execute();
        $stage2Annexid = $statement->fetch();

        if ($stage2Annexid) {
            $dltStageAnnex2Detail = "DELETE FROM stage_ii_audit WHERE ca_service_audit_ii = :id ";
            $statement = $db->prepare($dltStageAnnex2Detail);
            $statement->bindParam(':id', $stage2Annexid['stage_ii_id']);
            $statement->execute();

            $dltStageAnnex2id = "DELETE FROM stage_ii_audit_id WHERE stage_ii_id = :id";
            $statement = $db->prepare($dltStageAnnex2id);
            $statement->bindParam(':id', $stage2Annexid['stage_ii_id']);
            $statement->execute();
        }

        $cdcId = "SELECT crr_id FROM cdc_report_review WHERE ca_service_id = :service and customer_id = :customer";
        $statement = $db->prepare($cdcId);
        $statement->bindParam(':service', $_GET['rc_service_id']);
        $statement->bindParam(':customer', $_GET['rc_customer_id']);
        $statement->execute();
        $cdc_id = $statement->fetch();

        if ($cdc_id) {
            $dltCdc = "DELETE FROM cdc_report_review WHERE crr_id = :id";
            $statement = $db->prepare($dltCdc);
            $statement->bindParam(':id', $cdc_id['crr_id']);
            $statement->execute();
        }

        $certificate = "SELECT certificate_id FROM `certificate_print` where service_id = :service and customer_id = :customer";
        $statement = $db->prepare($certificate);
        $statement->bindParam(':service', $_GET['rc_service_id']);
        $statement->bindParam(':customer', $_GET['rc_customer_id']);
        $statement->execute();
        $cert_id = $statement->fetch();

        if ($cert_id) {
            $dltCertificate = "DELETE FROM `certificate_print` WHERE `certificate_id` = :id";
            $statement = $db->prepare($dltCertificate);
            $statement->bindParam(':id', $cert_id['certificate_id']);
            $statement->execute();
        }

        $customerSurvayDetail = "SELECT survey_id FROM customer_survey_id WHERE ca_serviceid = :service and customer_ca = :customer";
        $statement = $db->prepare($customerSurvayDetail);
        $statement->bindParam(':service', $_GET['rc_service_id']);
        $statement->bindParam(':customer', $_GET['rc_customer_id']);
        $statement->execute();
        $customerSurvayId = $statement->fetch();

        if ($customerSurvayId) {
            $dltSurvayDetail = "DELETE FROM customer_survey WHERE survayid = :id";
            $statement = $db->prepare($dltSurvayDetail);
            $statement->bindParam(':id', $customerSurvayId['survey_id']);
            $statement->execute();

            $dltSurvayId = "DELETE FROM customer_survey_id WHERE survey_id = :id";
            $statement = $db->prepare($dltSurvayId);
            $statement->bindParam(':id', $customerSurvayId['survey_id']);
            $statement->execute();
        }

        $cf1Detail = "SELECT control_id FROM control_formati_id WHERE caservice_id = :service and customer_id = :customer";
        $statement = $db->prepare($cf1Detail);
        $statement->bindParam(':service', $_GET['rc_service_id']);
        $statement->bindParam(':customer', $_GET['rc_customer_id']);
        $statement->execute();
        $cf1Id = $statement->fetch();

        if ($cf1Id) {
            $dltCF1Detail = "DELETE FROM control_formati WHERE control_id = :id";
            $statement = $db->prepare($dltCF1Detail);
            $statement->bindParam(':id', $cf1Id['control_id']);
            $statement->execute();

            $dltCF1Id = "DELETE FROM control_formati_id WHERE control_id = :id";
            $statement = $db->prepare($dltCF1Id);
            $statement->bindParam(':id', $cf1Id['control_id']);
            $statement->execute();
        }

        $cf2Detail = "SELECT control2_id FROM control_formatii_id WHERE caservice_id = :service and customer_id = :customer";
        $statement = $db->prepare($cf2Detail);
        $statement->bindParam(':service', $_GET['rc_service_id']);
        $statement->bindParam(':customer', $_GET['rc_customer_id']);
        $statement->execute();
        $cf2Id = $statement->fetch();

        if ($cf2Id) {
            $dltcf2Detail = "DELETE FROM control_formatii WHERE control2_id = :id";
            $statement = $db->prepare($dltcf2Detail);
            $statement->bindParam(':id', $cf2Id['control2_id']);
            $statement->execute();

            $dltCF2id = "DELETE FROM control_formatii_id WHERE control2_id = :id";
            $statement = $db->prepare($dltCF2id);
            $statement->bindParam(':id', $cf2Id['control2_id']);
            $statement->execute();
        }

        $dltRcService = "DELETE FROM service_type_rc WHERE rc_id = :service";
        $statement = $db->prepare($dltRcService);
        $statement->bindParam(':service', $_GET['rc_service_id']);
        $statement->execute();

        $dltcaDates = "DELETE FROM ca_service_dates WHERE service_id=:service_id";
        $statement = $db->prepare($dltcaDates);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        $dltService = "DELETE FROM service WHERE customer_id=:customer AND service_id=:service_id";
        $statement = $db->prepare($dltService);
        $statement->bindParam(':customer', $_GET['rc_customer_id']);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        $nextsvcupdate = "UPDATE `customer_info` SET `next_service`= 'RC' WHERE `id` = :id";
        $statement = $db->prepare($nextsvcupdate);
        $statement->bindParam(":id", $_GET['rc_customer_id']);
        $statement->execute();

        echo 'success';
    } catch (Exception $ex) {
        echo $ex;
    }
} else if (isset($_GET['sp_service_id']) && !empty($_GET['sp_service_id']) && isset($_GET['sp_customer_id']) && !empty($_GET['sp_customer_id'])) {
    try {
        if (!isset($_GET['permanent']) || $_GET['permanent'] != '1') {
            $svcId = !empty($_GET['service_id']) ? $_GET['service_id'] : null;
            if (!$svcId && !empty($_GET['sp_service_id'])) {
                $st = $db->prepare("SELECT service FROM service_type_sp WHERE sp_id = :id");
                $st->execute([':id' => $_GET['sp_service_id']]);
                $svcId = $st->fetchColumn();
            }
            aitfSoftDeleteService($db, $svcId, $_GET['sp_customer_id'], 'SP');
            echo 'success';
            exit;
        }
        $stage1AuditId = "SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ";
        $statement = $db->prepare($stage1AuditId);
        $statement->bindParam(':service', $_GET['sp_service_id']);
        $statement->bindParam(':customer', $_GET['sp_customer_id']);
        $statement->execute();
        $stage1Id = $statement->fetch();

        if ($stage1Id) {
            $dltStage1AuditDetails = "DELETE FROM stage_audit_one WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditDetails);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();

            $dltStage1AuditId = "DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditId);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();
        }

        $certagrement = "SELECT id FROM certification_aggrement WHERE service = :id";
        $statement = $db->prepare($certagrement);
        $statement->bindParam(":id", $_GET['sp_service_id']);
        $statement->execute();
        $certId = $statement->fetch();

        if ($certId) {
            $dltcert = "DELETE FROM `certification_aggrement` WHERE `id` = :id";
            $statement = $db->prepare($dltcert);
            $statement->bindParam(":id", $certId['id']);
            $statement->execute();
        }

        $stage2Annex = "SELECT stage_ii_id FROM stage_ii_audit_id WHERE ca_service_id = :service and customer_id = :customer";
        $statement = $db->prepare($stage2Annex);
        $statement->bindParam(':service', $_GET['sp_service_id']);
        $statement->bindParam(':customer', $_GET['sp_customer_id']);
        $statement->execute();
        $stage2Annexid = $statement->fetch();

        if ($stage2Annexid) {
            $dltStageAnnex2Detail = "DELETE FROM stage_ii_audit WHERE ca_service_audit_ii = :id ";
            $statement = $db->prepare($dltStageAnnex2Detail);
            $statement->bindParam(':id', $stage2Annexid['stage_ii_id']);
            $statement->execute();

            $dltStageAnnex2id = "DELETE FROM stage_ii_audit_id WHERE stage_ii_id = :id";
            $statement = $db->prepare($dltStageAnnex2id);
            $statement->bindParam(':id', $stage2Annexid['stage_ii_id']);
            $statement->execute();
        }

        $cdcId = "SELECT crr_id FROM cdc_report_review WHERE ca_service_id = :service and customer_id = :customer";
        $statement = $db->prepare($cdcId);
        $statement->bindParam(':service', $_GET['sp_service_id']);
        $statement->bindParam(':customer', $_GET['sp_customer_id']);
        $statement->execute();
        $cdc_id = $statement->fetch();

        if ($cdc_id) {
            $dltCdc = "DELETE FROM cdc_report_review WHERE crr_id = :id";
            $statement = $db->prepare($dltCdc);
            $statement->bindParam(':id', $cdc_id['crr_id']);
            $statement->execute();
        }

        $certificate = "SELECT certificate_id FROM `certificate_print` where service_id = :service and customer_id = :customer";
        $statement = $db->prepare($certificate);
        $statement->bindParam(':service', $_GET['sp_service_id']);
        $statement->bindParam(':customer', $_GET['sp_customer_id']);
        $statement->execute();
        $cert_id = $statement->fetch();

        if ($cert_id) {
            $dltCertificate = "DELETE FROM `certificate_print` WHERE `certificate_id` = :id";
            $statement = $db->prepare($dltCertificate);
            $statement->bindParam(':id', $cert_id['certificate_id']);
            $statement->execute();
        }

        $dltSPService = "DELETE FROM service_type_sp WHERE sp_id = :service";
        $statement = $db->prepare($dltSPService);
        $statement->bindParam(':service', $_GET['sp_service_id']);
        $statement->execute();

        $dltService = "DELETE FROM service WHERE customer_id=:customer AND service_id=:service_id";
        $statement = $db->prepare($dltService);
        $statement->bindParam(':customer', $_GET['sp_customer_id']);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        echo 'success';
    } catch (Exception $ex) {
        echo $ex;
    }
} else if (isset($_GET["ta_service_id"]) && !empty($_GET["ta_service_id"]) && isset($_GET["ta_customer_id"]) && !empty($_GET["ta_customer_id"])) {
    try {
        if (!isset($_GET['permanent']) || $_GET['permanent'] != '1') {
            $svcId = !empty($_GET['service_id']) ? $_GET['service_id'] : null;
            if (!$svcId && !empty($_GET['ta_service_id'])) {
                $st = $db->prepare("SELECT service_id FROM service_type_ta WHERE ta_id = :id");
                $st->execute([':id' => $_GET['ta_service_id']]);
                $svcId = $st->fetchColumn();
            }
            aitfSoftDeleteService($db, $svcId, $_GET['ta_customer_id'], 'TA');
            echo 'success';
            exit;
        }
        $stage1AuditId = "SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ";
        $statement = $db->prepare($stage1AuditId);
        $statement->bindParam(':service', $_GET['ta_service_id']);
        $statement->bindParam(':customer', $_GET['ta_customer_id']);
        $statement->execute();
        $stage1Id = $statement->fetch();

        if ($stage1Id) {
            $dltStage1AuditDetails = "DELETE FROM stage_audit_one WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditDetails);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();

            $dltStage1AuditId = "DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id ";
            $statement = $db->prepare($dltStage1AuditId);
            $statement->bindParam(':id', $stage1Id['audit_stage_id']);
            $statement->execute();
        }

        $scheduleAudit1id = "SELECT audit_schedule_id FROM audit_schedule_id WHERE serviceca_id = :service and customer_ca_id = :customer";
        $statement = $db->prepare($scheduleAudit1id);
        $statement->bindParam(':service', $_GET['ta_service_id']);
        $statement->bindParam(':customer', $_GET['ta_customer_id']);
        $statement->execute();
        $audit1Id = $statement->fetch();

        if ($audit1Id) {
            $dltSchedule1AuditDetail = "DELETE FROM audit_schedule WHERE audit_scheduleid = :id";
            $statement = $db->prepare($dltSchedule1AuditDetail);
            $statement->bindParam(':id', $audit1Id['audit_schedule_id']);
            $statement->execute();

            $dltScheduleAuditId = "DELETE FROM audit_schedule_id WHERE audit_schedule_id = :id";
            $statement = $db->prepare($dltScheduleAuditId);
            $statement->bindParam(':id', $audit1Id['audit_schedule_id']);
            $statement->execute();
        }

        $stage1Annex = "SELECT stage_i_id FROM stage_i_audit_id WHERE ca_service_id = :service and customer_id = :customer ";
        $statement = $db->prepare($stage1Annex);
        $statement->bindParam(':service', $_GET['ta_service_id']);
        $statement->bindParam(':customer', $_GET['ta_customer_id']);
        $statement->execute();
        $stage1Annexid = $statement->fetch();

        if ($stage1Annexid) {
            $dltAnnex1Details = "DELETE FROM stage_i_audit WHERE ca_service_audit = :id ";
            $statement = $db->prepare($dltAnnex1Details);
            $statement->bindParam(':id', $stage1Annexid['stage_i_id']);
            $statement->execute();

            $dltAnnex1id = "DELETE FROM stage_i_audit_id WHERE stage_i_id = :id";
            $statement = $db->prepare($dltAnnex1id);
            $statement->bindParam(':id', $stage1Annexid['stage_i_id']);
            $statement->execute();
        }

        $dltRcService = "DELETE FROM service_type_rc WHERE rc_id = :service";
        $statement = $db->prepare($dltRcService);
        $statement->bindParam(':service', $_GET['ta_service_id']);
        $statement->execute();

        $dltService = "DELETE FROM service WHERE customer_id=:customer AND service_id=:service_id";
        $statement = $db->prepare($dltService);
        $statement->bindParam(':customer', $_GET['ta_customer_id']);
        $statement->bindParam(':service_id', $_GET['service_id']);
        $statement->execute();

        echo 'success';
    } catch (Exception $ex) {
        echo $ex;
    }
}

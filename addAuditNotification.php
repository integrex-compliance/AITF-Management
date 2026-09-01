<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
include_once './include/cdc_audit_date_sync.php';
require_once __DIR__ . '/include/reviewer_conflict_helper.php';
date_default_timezone_set('Asia/Kolkata');

function validateStage1IntimationDate($db, $serviceId, $intimationDateValue)
{
    if (empty($serviceId) || empty($intimationDateValue)) {
        return true;
    }

    $intimationDate = date('Y-m-d', strtotime($intimationDateValue));
    $assessmentSql = "SELECT `date` FROM `assessment_planning` WHERE `service_id` = :serviceId ORDER BY `assessment_id` DESC LIMIT 1";
    $statement = $db->prepare($assessmentSql);
    $statement->bindParam(':serviceId', $serviceId);
    $statement->execute();
    $assessmentRow = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$assessmentRow || empty($assessmentRow['date'])) {
        return true;
    }

    $assessmentDate = date('Y-m-d', strtotime($assessmentRow['date']));
    return !($intimationDate < $assessmentDate);
}
if (isset($_POST["audit-noti-id-edit"]) && !empty($_POST["audit-noti-id-edit"])) {

    try {
        $notiid = $_POST['audit-noti-id-edit'];
        $service = $_POST['audit-noti-service'];
        $customer = $_POST['audit-noti-customer'];
        $auditors = explode('~', $_POST['auditorNameHidden']);
        $category = explode('~', $_POST['auditorCategoryHidden']);
//        $contact = explode('~', $_POST['auditorContactHidden']);
        $work = explode('~', $_POST['auditorWorkHidden']);
        $acceptance = explode('~', $_POST['auditorAcceptanceHidden']);

        foreach ($auditors as $audIdVal) {
            $audIdVal = trim((string)$audIdVal);
            if ($audIdVal !== '') {
                $conflictCheck = aitf_check_auditor_appointed_as_reviewer($db, $customer, $service, $audIdVal);
                if (!empty($conflictCheck['has_conflict'])) {
                    $_SESSION['audit_app_conflict_error'] = ($conflictCheck['conflicting_name'] ?? 'This user') . " is Reviewer of this client, cannot set as Auditor. Please select another Auditor.";
                    header("Location: customerAccount.php?customerid=" . urlencode($customer) . "&reviewer_conflict=1");
                    exit;
                }
            }
        }

        $auditorlist = "";
        foreach ($auditors as $auditor) {
            $auditorlist = $auditorlist . $auditor . ", ";
        }

        if (isset($_POST['intimationDate']) && !empty($_POST['intimationDate'])) {
            $intimationDate = date('Y-m-d', strtotime($_POST['intimationDate']));
        } else {
            $intimationDate = NULL;
        }

        if (!validateStage1IntimationDate($db, $service, $_POST['intimationDate'])) {
            echo 'Date should be greater than or equal to assessment planning date';
            exit;
        }

        $svcauditdate = "";
        $alldates = "";
        if (isset($_POST["auditDatesHidden"]) && !empty($_POST["auditDatesHidden"])) {
            $auditorsDate = explode(',', $_POST['auditDatesHidden']);
            foreach ($auditorsDate as $row) {
                $alldates = $alldates . date('Y-m-d', strtotime($row)) . ', ';
                $svcauditdate = date('Y-m-d', strtotime($row));
            }
        } else {
            $alldates = NULL;
        }
//        echo $alldates; die;
        $sql = "SELECT ca_service_id FROM `stage_oneaudit_id` where audit_stage_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $notiid);
        $statement->execute();
        $serviceId = $statement->fetch();

        $service = $serviceId["ca_service_id"];

        $servicestatus = "Audit planning";
        $serviceid = "";

        $notidlt = "DELETE FROM stage_audit_one WHERE audit_stage_id = :id";
        $statement = $db->prepare($notidlt);
        $statement->bindParam(':id', $notiid);
        $statement->execute();

        $auditorsDlt = "DELETE FROM `notification_auditor` WHERE `notification_id` = :id";
        $statement = $db->prepare($auditorsDlt);
        $statement->bindParam(':id', $notiid);
        $statement->execute();

        $updateNoti = "UPDATE stage_oneaudit_id SET audit_subject = :auditsub, for_org = :auditorg, intimation_date = "
                . ":intimationdate, location = :location, audit_ref_no = :referance, audit_standard = :standard, "
                . "audit_contact_person = :contactPerson, audit_dates = :auditdates, audit_designation = :designation,appointed_by= :appointedby, mcl_designation= :mcldesignation, "
                . "audit_telephone = :telephone, audit_email = :auditemail, special_audit = :specialaudit, "
                . "auditor_check_last_two= :tllast, auditor_check_comm= :tllastc, auditor_list= :auditorlist WHERE audit_stage_id = :id";
        $statement = $db->prepare($updateNoti);
        $statement->bindParam(':id', $notiid);
        $statement->bindParam(':auditsub', $_POST['auditSubject']);
        $statement->bindParam(':auditorg', $_POST['auditForOrg']);
        $statement->bindParam(':intimationdate', $intimationDate);
        $statement->bindParam(':location', $_POST['auditLocation']);
        $statement->bindParam(':referance', $_POST['auditReferance']);
        $statement->bindParam(':standard', $_POST['auditStandard']);
        $statement->bindParam(':contactPerson', $_POST['auditContactPerson']);
        $statement->bindParam(':auditdates', $_POST['auditDatesHidden']);
        $statement->bindParam(':designation', $_POST['auditDesignation']);
        $statement->bindParam(':mcldesignation', $_POST['mcldesignation']);
        $statement->bindParam(':appointedby', $_POST['appointedby']);
        $statement->bindParam(':telephone', $_POST['auditTelephone']);
        $statement->bindParam(':auditemail', $_POST['auditEmail']);
        $statement->bindParam(':specialaudit', $_POST['stage-i']);
        $statement->bindParam(':tllast', $_POST['tllast']);
        $statement->bindParam(':tllastc', $_POST['tllastyc']);
        $statement->bindParam(':auditorlist', $auditorlist);
        $statement->execute();
        $auditorslist = "";
        for ($i = 0; $i < count($auditors); ++$i) {
            if ($auditors[$i] != "") {
                $sql = "SELECT * FROM `auditors` WHERE `auditors_id` = :id";
                $statement = $db->prepare($sql);
                $statement->bindParam(":id", $auditors[$i]);
                $statement->execute();
                $auditorinfo = $statement->fetch();

                $notificationInsert = "INSERT INTO stage_audit_one (audit_stage_id, ca_service_id, auditor_id, auditor_name, auditor_abbrivation, auditor_category, audit_contact, audit_wokrdays, auditor_acceptance) "
                        . "VALUES (:auditis, :service, :auditor, :auditorname, :auditorabbrevation, :category, :contact, :work, :acceptance)";
                $statement = $db->prepare($notificationInsert);
                $statement->bindParam(':auditis', $notiid);
                $statement->bindParam(':service', $service);
                $statement->bindParam(':auditor', $auditors[$i]);
                $statement->bindParam(':auditorname', $auditorinfo['auditor_name']);
                $statement->bindParam(':auditorabbrevation', $auditorinfo['abbrevation']);
                $statement->bindParam(':category', $category[$i]);
                $statement->bindParam(':contact', $auditorinfo['auditor_number']);
                $statement->bindParam(':work', $work[$i]);
                $statement->bindParam(':acceptance', $acceptance[$i]);
                $statement->execute();
                $auditorslist = $auditorslist . $auditorinfo['auditor_name'] . ", ";

                // insert into notification_auditor table     
                $auditorInfo = "INSERT INTO `notification_auditor`(`client_id`, `service_id`, `notification_id`, `auditor_id`, `auditor_name`, `auditor_catagory`) "
                        . "VALUES (:client, :service, :notification, :auditor_id, :auditor_name, :category)";
                $statement = $db->prepare($auditorInfo);
                $statement->bindParam(':client', $customer);
                $statement->bindParam(':service', $serviceid);
                $statement->bindParam(':notification', $notiid);
                $statement->bindParam(':auditor_id', $auditors[$i]);
                $statement->bindParam(':auditor_name', $auditorinfo['auditor_name']);
                $statement->bindParam(':category', $category[$i]);
                $statement->execute();
            }
        }

        $sql1 = "SELECT service FROM `service_type_ca` where ca_id = :id";
        $statement = $db->prepare($sql1);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $ca = $statement->fetch();

        if ($ca["service"]) {
            $serviceid = $ca["service"];
            $caServiceStatusUpdate = "UPDATE service_type_ca SET service_ca_status = :status, audit_date = :auditdates, stage1_auditor = :auditars WHERE ca_id = :service";
            $statement = $db->prepare($caServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdates', $_POST["auditDatesHidden"]);
            $statement->bindParam(':auditars', $auditorslist);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $ca["service"]);
            $statement->execute();
        }

        $sql2 = "SELECT service FROM `service_type_sai` where sai_id = :id";
        $statement = $db->prepare($sql2);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $sai = $statement->fetch();

        if ($sai["service"]) {
            $serviceid = $sai["service"];
            $saiServiceStatusUpdate = "UPDATE service_type_sai SET service_sai_status = :status, audit_date = :auditdtae, auditors = :auditars WHERE sai_id = :service";
            $statement = $db->prepare($saiServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $_POST["auditDatesHidden"]);
            $statement->bindParam(':auditars', $auditorslist);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $_POST["auditDatesHidden"]);
            $statement->bindParam(':service', $sai["service"]);
            $statement->execute();
        }

        $sql3 = "SELECT service FROM `service_type_saii` where saii_id = :id";
        $statement = $db->prepare($sql3);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $saii = $statement->fetch();

        if ($saii["service"]) {
            $serviceid = $saii["service"];
            $saiiServiceStatusUpdate = "UPDATE service_type_saii SET service_saii_ststus = :status, audit_date = :auditdate, auditors = :auditars WHERE saii_id = :service";
            $statement = $db->prepare($saiiServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdate', $_POST["auditDatesHidden"]);
            $statement->bindParam(':auditars', $auditorslist);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $saii["service"]);
            $statement->execute();
        }

        $sql4 = "SELECT service FROM `service_type_rc` WHERE rc_id = :id";
        $statement = $db->prepare($sql4);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $rc = $statement->fetch();

        if ($rc["service"]) {
            $serviceid = $rc["service"];
            $rcServiceStatusUpdate = "UPDATE service_type_rc SET service_rc_status = :status, audit_date = :auditdate, auditors = :auditars WHERE rc_id = :service";
            $statement = $db->prepare($rcServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdate', $_POST["auditDatesHidden"]);
            $statement->bindParam(':auditars', $auditorslist);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $rc["service"]);
            $statement->execute();
        }

        $sql5 = "SELECT service FROM `service_type_sp` WHERE sp_id = :id";
        $statement = $db->prepare($sql5);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $sp = $statement->fetch();

        if ($sp["service"]) {
            $serviceid = $sp["service"];
            $spServiceStatusUpdate = "UPDATE service_type_sp SET service_sp_status = :status, audit_date = :auditdtae, auditors = :auditors WHERE sp_id = :service";
            $statement = $db->prepare($spServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $_POST["auditDatesHidden"]);
            $statement->bindParam(':auditors', $auditorslist);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $svcauditdate);
            $statement->bindParam(':service', $sp["service"]);
            $statement->execute();
        }

        $sql6 = "SELECT service_id FROM `service_type_ta` WHERE `ta_id` = :id";
        $statement = $db->prepare($sql6);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $ta = $statement->fetch();

        if ($ta["service_id"]) {
            $serviceid = $ta["service_id"];
            $spServiceStatusUpdate = "UPDATE service_type_ta SET service_status = :status, audit_date = :auditdtae, auditors = :auditors WHERE ta_id = :service";
            $statement = $db->prepare($spServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $_POST["auditDatesHidden"]);
            $statement->bindParam(':auditors', $auditorslist);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $svcauditdate);
            $statement->bindParam(':service', $ta["service_id"]);
            $statement->execute();

            $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
            $statement = $db->prepare($updateCustomerStatus);
            $statement->bindParam(':status', $customerStatus);
            $statement->bindParam(':customer', $customer);
            $statement->execute();
        }

        $sql = "UPDATE `stage_oneaudit_id` SET auditor_list = :auditor WHERE audit_stage_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $notiid);
        $statement->bindParam(":auditor", $auditorslist);
        $statement->execute();
        aitfSyncCdcAuditDateFromStageNotifications($db, $service);

        header("Location: customerAccount.php?customerid=" . $customer);
//        echo "success";
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    try {
//        echo "in"; die;
//        $service = "";
        $service = $_POST['audit-noti-service'];
        $customer = $_POST['audit-noti-customer'];
        $auditors = explode('~', $_POST['auditorNameHidden']);
        $category = explode('~', $_POST['auditorCategoryHidden']);
//        $contact = explode('~', $_POST['auditorContactHidden']);
        $work = explode('~', $_POST['auditorWorkHidden']);
        $acceptance = explode('~', $_POST['auditorAcceptanceHidden']);
        $serviceid = "";

        foreach ($auditors as $audIdVal) {
            $audIdVal = trim((string)$audIdVal);
            if ($audIdVal !== '') {
                $conflictCheck = aitf_check_auditor_appointed_as_reviewer($db, $customer, $service, $audIdVal);
                if (!empty($conflictCheck['has_conflict'])) {
                    $_SESSION['audit_app_conflict_error'] = ($conflictCheck['conflicting_name'] ?? 'This user') . " is Reviewer of this client, cannot set as Auditor. Please select another Auditor.";
                    header("Location: customerAccount.php?customerid=" . urlencode($customer) . "&reviewer_conflict=1");
                    exit;
                }
            }
        }

        $svcauditdate = "";
        $alldates = "";
        if (isset($_POST["auditDatesHidden"]) && !empty($_POST["auditDatesHidden"])) {
            $auditorsDate = explode(',', $_POST['auditDatesHidden']);
            foreach ($auditorsDate as $row) {
                $alldates = $alldates . date('Y-m-d', strtotime($row)) . ', ';
                $svcauditdate = date('Y-m-d', strtotime($row));
            }
        } else {
            $alldates = NULL;
        }

        $checkServiceId = !empty($service) ? $service : (isset($_POST['audit-noti-service1']) ? $_POST['audit-noti-service1'] : '');
        if (!validateStage1IntimationDate($db, $checkServiceId, $_POST['intimationDate'])) {
            echo 'Date should be greater than or equal to assessment planning date';
            exit;
        }

        $auditorlist = "";
        foreach ($auditors as $auditor) {
            $auditorlist = $auditorlist . $auditor . ", ";
        }

        $rawServiceType = isset($_POST["audit-service-type"]) ? trim($_POST["audit-service-type"]) : '';
        if (empty($rawServiceType) && isset($_POST["audti-service-type"])) {
            $rawServiceType = trim($_POST["audti-service-type"]);
        }
        if ($rawServiceType === 'service-type-ca') $rawServiceType = 'CA';
        if ($rawServiceType === 'service-type-rc') $rawServiceType = 'RC';
        if ($rawServiceType === 'service-type-sai') $rawServiceType = 'SA-I';
        if ($rawServiceType === 'service-type-saii') $rawServiceType = 'SA-II';
        if ($rawServiceType === 'service-type-sp') $rawServiceType = 'SP';
        if ($rawServiceType === 'service-type-ta') $rawServiceType = 'TA';
        if ($rawServiceType === 'service-type-ptr') $rawServiceType = 'PTR';

        if (in_array($rawServiceType, array('CA', 'RC', 'PTR', 'SP', 'TA'))) {
            $service_id = "INSERT INTO service (service_type, customer_id) VALUES (:svctype, :customerID)";
            $statement = $db->prepare($service_id);
            $statement->bindParam(':customerID', $_POST['audit-noti-customer']);
            $statement->bindParam(':svctype', $rawServiceType);
            $statement->execute();

            $sql = "SELECT max(LAST_INSERT_ID(service_id)) as id From service";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $serviceid = $row['id'];

            if ($rawServiceType == "CA") {
                $casvc = "INSERT INTO `service_type_ca`(`service`, `customer_id`) VALUES (:svc, :customerID)";
                $statement = $db->prepare($casvc);
                $statement->bindParam(":svc", $serviceid);
                $statement->bindParam(':customerID', $_POST['audit-noti-customer']);
                $statement->execute();

                $sql1 = "SELECT max(LAST_INSERT_ID(ca_id)) as id From service_type_ca";
                $statement = $db->prepare($sql1);
                $statement->execute();
                $row1 = $statement->fetch();
                $service = $row1['id'];

                $sql = "UPDATE `customer_info` SET next_service = 'SA-I' WHERE id = :customer";
                $statement = $db->prepare($sql);
                $statement->bindParam(":customer", $_POST['audit-noti-customer']);
                $statement->execute();
            } else if ($rawServiceType == "RC") {
//                echo "in rc insert"; die;
                $rcsvc = "INSERT INTO `service_type_rc`(`service`, `rc_customer`) VALUES (:svc, :customerID)";
                $statement = $db->prepare($rcsvc);
                $statement->bindParam(":svc", $serviceid);
                $statement->bindParam(':customerID', $_POST['audit-noti-customer']);
                $statement->execute();

                $sql1 = "SELECT max(LAST_INSERT_ID(rc_id)) as id From service_type_rc";
                $statement = $db->prepare($sql1);
                $statement->execute();
                $row1 = $statement->fetch();
                $service = $row1['id'];

                $sql = "UPDATE `customer_info` SET next_service = 'SA-I' WHERE id = :customer";
                $statement = $db->prepare($sql);
                $statement->bindParam(":customer", $_POST['audit-noti-customer']);
                $statement->execute();
            } else if ($rawServiceType == "PTR") {
                $ptrsvc = "INSERT INTO service_type_ptr(service, customer_id) VALUES (:svc, :customerID)";
                $statement = $db->prepare($ptrsvc);
                $statement->bindParam(":svc", $serviceid);
                $statement->bindParam(':customerID', $_POST['audit-noti-customer']);
                $statement->execute();

                $sql1 = "SELECT max(LAST_INSERT_ID(prt_id)) as id From service_type_ptr";
                $statement = $db->prepare($sql1);
                $statement->execute();
                $row1 = $statement->fetch();
                $service = $row1['id'];
            } else if ($rawServiceType == "SP") {
                $spservice = "INSERT INTO `service_type_sp`(`service`, `sp_customer`) VALUES (:svc, :customerID)";
                $statement = $db->prepare($spservice);
                $statement->bindParam(":svc", $serviceid);
                $statement->bindParam(':customerID', $_POST['audit-noti-customer']);
                $statement->execute();

                $sql1 = "SELECT max(LAST_INSERT_ID(sp_id)) as id From service_type_sp";
                $statement = $db->prepare($sql1);
                $statement->execute();
                $row1 = $statement->fetch();
                $service = $row1['id'];
            } else if ($rawServiceType == "TA") {
                $taservice = "INSERT INTO `service_type_ta`(`service_id`, `customer_id`) VALUES (:svc, :customerID)";
                $statement = $db->prepare($taservice);
                $statement->bindParam(":svc", $serviceid);
                $statement->bindParam(':customerID', $_POST['audit-noti-customer']);
                $statement->execute();

                $sql1 = "SELECT max(LAST_INSERT_ID(ta_id)) as id From service_type_ta";
                $statement = $db->prepare($sql1);
                $statement->execute();
                $row1 = $statement->fetch();
                $service = $row1['id'];
            }
        }

        if (isset($_POST['intimationDate']) && !empty($_POST['intimationDate'])) {
            $intimationDate = date('Y-m-d', strtotime($_POST['intimationDate']));
        } else {
            $intimationDate = NULL;
        }

        $auditorlist = "";
        foreach ($auditors as $auditor) {
            $auditorlist = $auditorlist . $auditor . ", ";
        }

        $svcauditdate = "";
        $alldates = "";
        if (isset($_POST["auditDatesHidden"]) && !empty($_POST["auditDatesHidden"])) {
            $auditorsDate = explode(',', $_POST['auditDatesHidden']);
            foreach ($auditorsDate as $row) {
                $alldates = $alldates . date('Y-m-d', strtotime($row)) . ',';
                $svcauditdate = date('Y-m-d', strtotime($row));
            }
        } else {
            $alldates = NULL;
        }

        $servicestatus = "Audit planning";
        $customerStatus = "in-process";

        if (isset($_POST['addAuditNoti'])) {
            $auditGenetate = "INSERT INTO stage_oneaudit_id (ca_service_id, ca_customer_id, audit_subject, for_org, "
                    . "intimation_date, location, audit_ref_no, audit_standard, audit_contact_person, audit_dates, "
                    . "audit_designation, appointed_by, mcl_designation, audit_telephone, audit_email, special_audit, auditor_check_last_two, "
                    . "auditor_check_comm, auditor_list) "
                    . "VALUES (:service, :customer,:auditsub, :auditorg, :intimationdate, :location, :referance, "
                    . ":standard, :contactPerson, :auditdates, :designation, :appointedby, :mcldesignation, :telephone , :auditemail, "
                    . ":specialaudit, :tlcheck, :tlcheckcom, :auditorlist)";
            $statement = $db->prepare($auditGenetate);
            $statement->bindParam(':service', $service);
            //$statement->bindParam(':servicetype', $_POST['audti-service-type']);
            $statement->bindParam(':customer', $customer);
            $statement->bindParam(':auditsub', $_POST['auditSubject']);
            $statement->bindParam(':auditorg', $_POST['auditForOrg']);
            $statement->bindParam(':intimationdate', $intimationDate);
            $statement->bindParam(':location', $_POST['auditLocation']);
            $statement->bindParam(':referance', $_POST['auditReferance']);
            $statement->bindParam(':standard', $_POST['auditStandard']);
            $statement->bindParam(':contactPerson', $_POST['auditContactPerson']);
            $statement->bindParam(':auditdates', $_POST["auditDatesHidden"]);
            $statement->bindParam(':designation', $_POST['auditDesignation']);
            $statement->bindParam(':telephone', $_POST['auditTelephone']);
            $statement->bindParam(':auditemail', $_POST['auditEmail']);
            $statement->bindParam(':specialaudit', $_POST['stage-i']);
            $statement->bindParam(':tlcheck', $_POST['tllast']);
            $statement->bindParam(':tlcheckcom', $_POST['tllastyc']);
            $statement->bindParam(':auditorlist', $auditorlist);
            $statement->bindParam(':mcldesignation', $_POST['mcldesignation']);
            $statement->bindParam(':appointedby', $_POST['appointedby']);
            $statement->execute();

            $sql = "SELECT max(LAST_INSERT_ID(audit_stage_id)) as id From stage_oneaudit_id";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $notificationid = $row['id'];
            $auditorslist = "";
            for ($i = 0; $i < count($auditors); ++$i) {
                if ($auditors[$i] != "") {
                    $sql = "SELECT * FROM `auditors` WHERE `auditors_id` = :id";
                    $statement = $db->prepare($sql);
                    $statement->bindParam(":id", $auditors[$i]);
                    $statement->execute();
                    $auditorinfo = $statement->fetch();

                    $notificationInsert = "INSERT INTO stage_audit_one (audit_stage_id, ca_service_id, auditor_id, auditor_name,auditor_abbrivation, auditor_category, audit_contact, audit_wokrdays, auditor_acceptance) "
                            . "VALUES (:auditis, :service, :auditor, :auditorname, :auditorabbrevation, :category, :contact, :work, :acceptance)";
                    $statement = $db->prepare($notificationInsert);
                    $statement->bindParam(':auditis', $notificationid);
                    $statement->bindParam(':service', $service);
                    $statement->bindParam(':auditor', $auditors[$i]);
                    $statement->bindParam(':auditorname', $auditorinfo['auditor_name']);
                    $statement->bindParam(':auditorabbrevation', $auditorinfo['abbrevation']);
                    $statement->bindParam(':category', $category[$i]);
                    $statement->bindParam(':contact', $auditorinfo['auditor_number']);
                    $statement->bindParam(':work', $work[$i]);
                    $statement->bindParam(':acceptance', $acceptance[$i]);
                    $statement->execute();
                    $auditorslist = $auditorslist . $auditorinfo['auditor_name'] . ", ";

                    // insert into notification_auditor table     
                    $auditorInfo = "INSERT INTO `notification_auditor`(`client_id`, `service_id`, `notification_id`, `auditor_id`, `auditor_name`, `auditor_catagory`) "
                            . "VALUES (:client, :service, :notification, :auditor_id, :auditor_name, :category)";
                    $statement = $db->prepare($auditorInfo);
                    $statement->bindParam(':client', $customer);
                    $statement->bindParam(':service', $serviceid);
                    $statement->bindParam(':notification', $notificationid);
                    $statement->bindParam(':auditor_id', $auditors[$i]);
                    $statement->bindParam(':auditor_name', $auditorinfo['auditor_name']);
                    $statement->bindParam(':category', $category[$i]);
                    $statement->execute();
                    //echo
                }
            }

            $sql1 = "SELECT service FROM `service_type_ca` where ca_id = :id";
            $statement = $db->prepare($sql1);
            $statement->bindParam(":id", $service);
            $statement->execute();
            $ca = $statement->fetch();

            if ($ca["service"]) {
                $serviceid = $ca["service"];
                $caServiceStatusUpdate = "UPDATE service_type_ca SET service_ca_status = :status, audit_date = :auditdates, stage1_auditor = :auditars WHERE ca_id = :service";
                $statement = $db->prepare($caServiceStatusUpdate);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdates', $_POST["auditDatesHidden"]);
                $statement->bindParam(':auditars', $auditorslist);
                $statement->bindParam(':service', $service);
                $statement->execute();

                $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':service', $ca["service"]);
                $statement->execute();

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $customer);
                $statement->execute();
            }

            $sql2 = "SELECT service FROM `service_type_sai` where sai_id = :id";
            $statement = $db->prepare($sql2);
            $statement->bindParam(":id", $service);
            $statement->execute();
            $sai = $statement->fetch();

            if ($sai["service"]) {
                $serviceid = $sai["service"];
                $saiServiceStatusUpdate = "UPDATE service_type_sai SET service_sai_status = :status, audit_date = :auditdtae, auditors = :auditors WHERE sai_id = :service";
                $statement = $db->prepare($saiServiceStatusUpdate);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $_POST["auditDatesHidden"]);
                $statement->bindParam(':auditors', $auditorslist);
                $statement->bindParam(':service', $service);
                $statement->execute();

                $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $svcauditdate);
                $statement->bindParam(':service', $sai["service"]);
                $statement->execute();

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $customer);
                $statement->execute();
            }

            $sql3 = "SELECT service FROM `service_type_saii` where saii_id = :id";
            $statement = $db->prepare($sql3);
            $statement->bindParam(":id", $service);
            $statement->execute();
            $saii = $statement->fetch();

            if ($saii["service"]) {
                $serviceid = $saii["service"];
                $saiiServiceStatusUpdate = "UPDATE service_type_saii SET service_saii_ststus = :status, audit_date = :auditdate, auditors = :auditars WHERE saii_id = :service";
                $statement = $db->prepare($saiiServiceStatusUpdate);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdate', $_POST["auditDatesHidden"]);
                $statement->bindParam(':auditars', $auditorslist);
                $statement->bindParam(':service', $service);
                $statement->execute();

                $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdate WHERE service_id = :service";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdate', $svcauditdate);
                $statement->bindParam(':service', $saii["service"]);
                $statement->execute();

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $customer);
                $statement->execute();
            }

            $sql4 = "SELECT service FROM `service_type_rc` WHERE rc_id = :id";
            $statement = $db->prepare($sql4);
            $statement->bindParam(":id", $service);
            $statement->execute();
            $rc = $statement->fetch();

            if ($rc["service"]) {
                $serviceid = $rc["service"];
                $rcServiceStatusUpdate = "UPDATE service_type_rc SET service_rc_status = :status, audit_date = :auditdate, auditors = :auditars WHERE rc_id = :service";
                $statement = $db->prepare($rcServiceStatusUpdate);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdate', $_POST["auditDatesHidden"]);
                $statement->bindParam(':auditars', $auditorslist);
                $statement->bindParam(':service', $service);
                $statement->execute();

                $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdate WHERE service_id = :service";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdate', $svcauditdate);
                $statement->bindParam(':service', $rc["service"]);
                $statement->execute();

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $customer);
                $statement->execute();
            }

            $sql5 = "SELECT service FROM `service_type_sp` WHERE sp_id = :id";
            $statement = $db->prepare($sql5);
            $statement->bindParam(":id", $service);
            $statement->execute();
            $sp = $statement->fetch();

            if ($sp["service"]) {
                $serviceid = $sp["service"];
                $spServiceStatusUpdate = "UPDATE service_type_sp SET service_sp_status = :status, audit_date = :auditdtae, auditors = :auditors WHERE sp_id = :service";
                $statement = $db->prepare($spServiceStatusUpdate);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $_POST["auditDatesHidden"]);
                $statement->bindParam(':auditors', $auditorslist);
                $statement->bindParam(':service', $service);
                $statement->execute();

                $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $svcauditdate);
                $statement->bindParam(':service', $sp["service"]);
                $statement->execute();

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $customer);
                $statement->execute();
            }

            $sql6 = "SELECT service_id FROM `service_type_ta` WHERE `ta_id` = :id";
            $statement = $db->prepare($sql6);
            $statement->bindParam(":id", $service);
            $statement->execute();
            $ta = $statement->fetch();

            if ($ta["service_id"]) {
                $serviceid = $ta["service_id"];
                $spServiceStatusUpdate = "UPDATE service_type_ta SET service_status = :status, audit_date = :auditdtae, auditors = :auditors WHERE ta_id = :service";
                $statement = $db->prepare($spServiceStatusUpdate);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $_POST["auditDatesHidden"]);
                $statement->bindParam(':auditors', $auditorslist);
                $statement->bindParam(':service', $service);
                $statement->execute();

                $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $svcauditdate);
                $statement->bindParam(':service', $ta["service_id"]);
                $statement->execute();

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $customer);
                $statement->execute();
            }

            $sql = "UPDATE `stage_oneaudit_id` SET auditor_list = :auditor WHERE audit_stage_id = :id";
            $statement = $db->prepare($sql);
            $statement->bindParam(":id", $row['id']);
            $statement->bindParam(":auditor", $auditorslist);
            $statement->execute();
            aitfSyncCdcAuditDateFromStageNotifications($db, $service);
            header("Location: customerAccount.php?customerid=" . $customer);
//            echo "success";
        }
    } catch (Exception $ex) {
        echo $ex;
    }
}

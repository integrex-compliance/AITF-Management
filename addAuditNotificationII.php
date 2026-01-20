<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
//echo "page";
if (isset($_POST['audtii-noti-id']) && !empty($_POST['audtii-noti-id']) && isset($_POST['auditii-noti-customer']) && !empty($_POST['auditii-noti-customer'])) {
    try {
        $customer = $_POST['auditii-noti-customer'];
        $auditors = explode('~', $_POST['auditorNameHiddenii']);
        $category = explode('~', $_POST['auditorCategoryHiddenii']);
        $contact = explode('~', $_POST['auditorContactHiddenii']);
        $work = explode('~', $_POST['auditorWorkHiddenii']);
        $acceptance = explode('~', $_POST['auditorAcceptanceHiddenii']);

        $auditorlist = "";
        foreach ($auditors as $auditor) {
            $auditorlist = $auditorlist . $auditor . ", ";
        }

        if (isset($_POST['intimationDateii']) && !empty($_POST['intimationDateii'])) {
            $intimationDate = date('Y-m-d', strtotime($_POST['intimationDateii']));
        } else {
            $intimationDate = NULL;
        }

        $svcauditdate = "";
        $alldates = "";
        if (isset($_POST["auditiiDatesHiddenii"]) && !empty($_POST["auditiiDatesHiddenii"])) {
            $auditorsDate = explode(', ', $_POST['auditiiDatesHiddenii']);
            foreach ($auditorsDate as $row) {
                $alldates = $alldates . date('Y-m-d', strtotime($row)) . ',';
                $svcauditdate = date('Y-m-d', strtotime($row));
            }
        } else {
            $alldates = NULL;
        }

        $servicestatus = "Audit planning";

        $sql = "SELECT service_id FROM `stage_twoaudit_id` where audit_stageii_id = :id ";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_POST['audtii-noti-id']);
        $statement->execute();
        $serviceid = $statement->fetch();

        $service = $serviceid["service_id"];

        $updateSchedule = "UPDATE stage_twoaudit_id SET audit_subject = :auditsub, for_org = :auditorg, "
                . "intimation_date = :intimationdate, location = :location, audit_ref_no = :referance, "
                . "audit_standard = :standard, audit_contact_person = :contactPerson, audit_dates = :auditdates, "
                . "audit_designation = :designation, audit_telephone = :telephone, audit_email = :auditemail, appointed_by= :appointedby, mcl_designation= :mcldesignation, "
                . "stage_ii_of = :specialaudit, auditor_check_last_two= :tllast, auditor_check_comm= :tllastc, auditor_list= :auditorlist"
                . " WHERE audit_stageii_id = :id";
        $statement = $db->prepare($updateSchedule);
        $statement->bindParam(':auditsub', $_POST['auditiiSubject']);
        $statement->bindParam(':auditorg', $_POST['auditiiForOrg']);
        $statement->bindParam(':intimationdate', $intimationDate);
        $statement->bindParam(':location', $_POST['auditiiLocation']);
        $statement->bindParam(':referance', $_POST['auditiiReferance']);
        $statement->bindParam(':standard', $_POST['auditiiStandard']);
        $statement->bindParam(':contactPerson', $_POST['auditiiContactPerson']);
        $statement->bindParam(':auditdates', $_POST["auditiiDatesHiddenii"]);
        $statement->bindParam(':designation', $_POST['auditiiDesignation']);
        $statement->bindParam(':telephone', $_POST['auditiiTelephone']);
        $statement->bindParam(':auditemail', $_POST['auditiiEmail']);
        $statement->bindParam(':mcldesignation', $_POST['mcldesignation']);
        $statement->bindParam(':appointedby', $_POST['appointedby']);
        $statement->bindParam(':specialaudit', $_POST['stage-ii']);
        $statement->bindParam(':tllast', $_POST['tllastedit']);
        $statement->bindParam(':tllastc', $_POST['tllastyc']);
        $statement->bindParam(':auditorlist', $auditorlist);
        $statement->bindParam(':id', $_POST['audtii-noti-id']);
        $statement->execute();

        $deleteAudit = "DELETE FROM stage_audit_two WHERE audit_stageii_id = :id";
        $statement = $db->prepare($deleteAudit);
        $statement->bindParam(':id', $_POST['audtii-noti-id']);
        $statement->execute();
        $auditorslist = "";

        for ($i = 0; $i < count($auditors); ++$i) {
            if ($auditors[$i] != "") {
                $sql = "SELECT * FROM `auditors` WHERE `auditors_id` = :id";
                $statement = $db->prepare($sql);
                $statement->bindParam(":id", $auditors[$i]);
                $statement->execute();
                $auditorinfo = $statement->fetch();

                $notificationInsert = "INSERT INTO stage_audit_two (audit_stageii_id, service_id, auditor_id, auditor_name, auditor_abbrivation, auditor_category, audit_contact, audit_wokrdays, auditor_acceptance) "
                        . "VALUES (:auditis, :service, :auditor, :auditorname,:auditorabbrevation, :category, :contact, :work, :acceptance)";
                $statement = $db->prepare($notificationInsert);
                $statement->bindParam(':auditis', $_POST['audtii-noti-id']);
                $statement->bindParam(':service', $_POST['auditii-noti-service']);
                $statement->bindParam(':auditor', $auditors[$i]);
                $statement->bindParam(':auditorname', $auditorinfo['auditor_name']);
                $statement->bindParam(':auditorabbrevation', $auditorinfo['abbrevation']);
                $statement->bindParam(':category', $category[$i]);
                $statement->bindParam(':contact', $auditorinfo['auditor_number']);
                $statement->bindParam(':work', $work[$i]);
                $statement->bindParam(':acceptance', $acceptance[$i]);
                $statement->execute();
                $auditorslist = $auditorslist . $auditorinfo['auditor_name'] . ", ";
            }
        }

        $sql1 = "SELECT service FROM `service_type_ca` where ca_id = :id";
        $statement = $db->prepare($sql1);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $ca = $statement->fetch();

        if ($ca["service"]) {
            $caServiceStatusUpdate = "UPDATE service_type_ca SET service_ca_status = :status, stage2_audit = :auditdtae, stage2_auditor = :auditors WHERE ca_id = :service";
            $statement = $db->prepare($caServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $_POST["auditiiDatesHiddenii"]);
            $statement->bindParam(':auditors', $auditorslist);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $svcauditdate);
            $statement->bindParam(':service', $ca["service"]);
            $statement->execute();
        }

        $sql2 = "SELECT service FROM `service_type_sai` where sai_id = :id";
        $statement = $db->prepare($sql2);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $sai = $statement->fetch();

        if ($sai["service"]) {
            $saiServiceStatusUpdate = "UPDATE service_type_sai SET service_sai_status = :status WHERE sai_id = :service";
            $statement = $db->prepare($saiServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $sai["service"]);
            $statement->execute();
        }

        $sql3 = "SELECT service FROM `service_type_saii` where saii_id = :id";
        $statement = $db->prepare($sql3);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $saii = $statement->fetch();

        if ($saii["service"]) {
            $saiiServiceStatusUpdate = "UPDATE service_type_saii SET service_saii_ststus = :status, audit_date = :auditdtae WHERE saii_id = :service";
            $statement = $db->prepare($caServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $svcauditdate);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $svcauditdate);
            $statement->bindParam(':service', $saii["service"]);
            $statement->execute();
        }

        $sql4 = "SELECT service FROM `service_type_rc` WHERE rc_id = :id";
        $statement = $db->prepare($sql4);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $rc = $statement->fetch();

        if ($rc["service"]) {
            $rcServiceStatusUpdate = "UPDATE service_type_rc SET service_rc_status = :status, audit_date = :auditdtae WHERE rc_id = :service";
            $statement = $db->prepare($caServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $svcauditdate);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':auditdtae', $svcauditdate);
            $statement->bindParam(':service', $rc["service"]);
            $statement->execute();
        }

        $sql5 = "SELECT service FROM `service_type_sp` WHERE sp_id = :id";
        $statement = $db->prepare($sql5);
        $statement->bindParam(":id", $service);
        $statement->execute();
        $sp = $statement->fetch();

        if ($sp["service"]) {
            $rcServiceStatusUpdate = "UPDATE service_type_sp SET service_sp_status = :status WHERE sp_id = :service";
            $statement = $db->prepare($caServiceStatusUpdate);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $service);
            $statement->execute();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $servicestatus);
            $statement->bindParam(':service', $sp["service"]);
            $statement->execute();
        }

        $sql = "UPDATE `stage_twoaudit_id` SET auditor_list = :auditor WHERE audit_stageii_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_POST['audtii-noti-id']);
        $statement->bindParam(":auditor", $auditorslist);
        $statement->execute();

        header("Location: customerAccount.php?customerid=" . $customer);
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_POST['auditii-noti-customer']) && !empty($_POST['auditii-noti-customer']) && isset($_POST['auditii-noti-service']) && !empty($_POST['auditii-noti-service'])) {
        try {
            $service = $_POST['auditii-noti-service'];
            $customer = $_POST['auditii-noti-customer'];
            $auditors = explode('~', $_POST['auditorNameHiddenii']);
            $category = explode('~', $_POST['auditorCategoryHiddenii']);
//            $contact = explode('~', $_POST['auditorContactHiddenii']);
            $work = explode('~', $_POST['auditorWorkHiddenii']);
            $acceptance = explode('~', $_POST['auditorAcceptanceHiddenii']);

            $auditorlist = "";
            foreach ($auditors as $auditor) {
                $auditorlist = $auditorlist . $auditor . ", ";
            }

            if (isset($_POST['intimationDateii']) && !empty($_POST['intimationDateii'])) {
                $intimationDate = date('Y-m-d', strtotime($_POST['intimationDateii']));
            } else {
                $intimationDate = NULL;
            }

            $svcauditdate = "";
            $alldates = "";
            if (isset($_POST["auditiiDatesHiddenii"]) && !empty($_POST["auditiiDatesHiddenii"])) {
                $auditorsDate = explode(', ', $_POST['auditiiDatesHiddenii']);
                foreach ($auditorsDate as $row) {
                    $alldates = $alldates . date('Y-m-d', strtotime($row)) . ',';
                    $svcauditdate = date('Y-m-d', strtotime($row));
                }
            } else {
                $alldates = NULL;
            }

            $servicestatus = "Audit planning";
            $customerStatus = "in-process";

//            echo $_POST['tllastedit']; die;
            $auditGenetate = "INSERT INTO stage_twoaudit_id (service_id, customer_id, audit_subject, for_org, "
                    . "intimation_date, location, audit_ref_no, audit_standard, audit_contact_person, audit_dates, "
                    . "audit_designation, audit_telephone, audit_email, stage_ii_of, auditor_check_last_two, "
                    . "auditor_check_comm, auditor_list, appointed_by, mcl_designation) "
                    . "VALUES(:service, :customer, :auditsub, :auditorg, :intimationdate, :location, :referance, "
                    . ":standard, :contactPerson, :auditdates, :designation, :telephone, :auditemail, :specialaudit, :tlcheck, :tlcheckcom, :auditorlist, :appointedby, :mcldesignation)";
            $statement = $db->prepare($auditGenetate);
            $statement->bindParam(':service', $service);
            $statement->bindParam(':customer', $customer);
            $statement->bindParam(':auditsub', $_POST['auditiiSubject']);
            $statement->bindParam(':auditorg', $_POST['auditiiForOrg']);
            $statement->bindParam(':intimationdate', $intimationDate);
            $statement->bindParam(':location', $_POST['auditiiLocation']);
            $statement->bindParam(':referance', $_POST['auditiiReferance']);
            $statement->bindParam(':mcldesignation', $_POST['mcldesignation']);
            $statement->bindParam(':appointedby', $_POST['appointedby']);
            $statement->bindParam(':standard', $_POST['auditiiStandard']);
            $statement->bindParam(':contactPerson', $_POST['auditiiContactPerson']);
            $statement->bindParam(':auditdates', $_POST["auditiiDatesHiddenii"]);
            $statement->bindParam(':designation', $_POST['auditiiDesignation']);
            $statement->bindParam(':telephone', $_POST['auditiiTelephone']);
            $statement->bindParam(':auditemail', $_POST['auditiiEmail']);
            $statement->bindParam(':specialaudit', $_POST['stage-ii']);
            $statement->bindParam(':tlcheck', $_POST['tllastedit']);
            $statement->bindParam(':tlcheckcom', $_POST['tllastyc']);
            $statement->bindParam(':auditorlist', $auditorlist);
            $statement->execute();

            $sql = "SELECT max(LAST_INSERT_ID(audit_stageii_id)) as id From stage_twoaudit_id";
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

                    $notificationInsert = "INSERT INTO stage_audit_two (audit_stageii_id, service_id, auditor_id, auditor_name, auditor_abbrivation, auditor_category, audit_contact, audit_wokrdays, auditor_acceptance)"
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
                }
            }

            $sql1 = "SELECT service FROM `service_type_ca` where ca_id = :id";
            $statement = $db->prepare($sql1);
            $statement->bindParam(":id", $service);
            $statement->execute();
            $ca = $statement->fetch();
            if ($ca["service"]) {

                $caServiceStatusUpdate = "UPDATE service_type_ca SET service_ca_status = :status, stage2_audit = :auditdtae, stage2_auditor = :auditors WHERE ca_id = :service";
                $statement = $db->prepare($caServiceStatusUpdate);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $_POST["auditiiDatesHiddenii"]);
                $statement->bindParam(':auditors', $auditorslist);
                $statement->bindParam(':service', $service);
                $statement->execute();

                $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $svcauditdate);
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
                $saiServiceStatusUpdate = "UPDATE service_type_sai SET service_sai_status = :status WHERE sai_id = :service";
                $statement = $db->prepare($saiServiceStatusUpdate);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':service', $service);
                $statement->execute();

                $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':status', $servicestatus);
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
                $saiiServiceStatusUpdate = "UPDATE service_type_saii SET service_saii_ststus = :status, audit_date = :auditdtae WHERE saii_id = :service";
                $statement = $db->prepare($caServiceStatusUpdate);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $svcauditdate);
                $statement->bindParam(':service', $service);
                $statement->execute();

                $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $svcauditdate);
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
                $rcServiceStatusUpdate = "UPDATE service_type_rc SET service_rc_status = :status, audit_date = :auditdtae WHERE rc_id = :service";
                $statement = $db->prepare($caServiceStatusUpdate);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $svcauditdate);
                $statement->bindParam(':service', $service);
                $statement->execute();

                $updateService = "UPDATE service SET service_status = :status, audit_date = :auditdtae WHERE service_id = :service";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':auditdtae', $svcauditdate);
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
                $rcServiceStatusUpdate = "UPDATE service_type_sp SET service_sp_status = :status WHERE sp_id = :service";
                $statement = $db->prepare($caServiceStatusUpdate);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':service', $service);
                $statement->execute();

                $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':status', $servicestatus);
                $statement->bindParam(':service', $sp["service"]);
                $statement->execute();

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $customer);
                $statement->execute();
            }

            $sql = "UPDATE `stage_twoaudit_id` SET auditor_list = :auditor WHERE audit_stageii_id = :id";
            $statement = $db->prepare($sql);
            $statement->bindParam(":id", $row['id']);
            $statement->bindParam(":auditor", $auditorslist);
            $statement->execute();
            header("Location: customerAccount.php?customerid=" . $customer);
//            echo "success";        
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}
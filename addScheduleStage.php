<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST['schedule-service-id']) && !empty($_POST['schedule-service-id']) && isset($_POST['schedule-customer-id']) && !empty($_POST['schedule-customer-id'])) {
    try {
        $service = $_POST['schedule-service-id'];
        $customer = $_POST['schedule-customer-id'];
        $scheduleDate = explode('~', $_POST['scheduleDateHidden']);
//        print_r($scheduleDate); echo '<br>';
        $scheduleTime = explode('~', $_POST['scheduleTime-Hidden']);
//        print_r($scheduleTime); echo '<br>';
        $scheduleActivity = explode('~', $_POST['scheduleActivityHidden']);
//        print_r($scheduleActivity); echo '<br>';
        $scheduleAuditor = explode('~', $_POST['scheduleAuditor-Hidden']);
//        print_r($scheduleAuditor); die;

        if (isset($_POST['scheduleAuditDate']) && !empty($_POST['scheduleAuditDate'])) {
            $dateSchedule = date('Y-m-d', strtotime($_POST['scheduleAuditDate']));
        } else {
            $dateSchedule = NULL;
        }

        if (isset($_POST['addScheduleStage'])) {
            $scheduleid = "INSERT INTO audit_schedule_id (serviceca_id, customer_ca_id, client_name, ref_no, schedule_address, schedule_contact_person, schedule_scope, schedule_app_standard, schedule_aifcode,schedule_nacfcode,schedule_naceclass,schedule_audit_type, schedule_mandays,schedule_audit_date, schedule_auditors, break, break_from, break_to) "
                    . "VALUES (:service,:customer,:client,:refno,:address,:contactperson,:scope,:appstandard,:iafcode,:nacecode,:naceclass,:audittype,:mandays,:auditDate,:auditor,:lunch,:timeFrom,:timeTo)";
            $statement = $db->prepare($scheduleid);
            $statement->bindParam(':service', $service);
            $statement->bindParam(':customer', $customer);
            $statement->bindParam(':client', $_POST['scheduleClientName']);
            $statement->bindParam(':refno', $_POST['scheduleRefNo']);
            $statement->bindParam(':address', $_POST['scheduleAddress']);
            $statement->bindParam(':contactperson', $_POST['scheduleContactPerson']);
            $statement->bindParam(':scope', $_POST['scheduleScope']);
            $statement->bindParam(':appstandard', $_POST['scheduleAppStandard']);
            $statement->bindParam(':iafcode', $_POST['scheduleIAFcode']);
            $statement->bindParam(':nacecode', $_POST['scheduleNacecode']);
            $statement->bindParam(':naceclass', $_POST['scheduleNaceclass']);
            $statement->bindParam(':audittype', $_POST['scheduleAuditType']);
            $statement->bindParam(':mandays', $_POST['scheduleMandays']);
            $statement->bindParam(':auditDate', $dateSchedule);
            $statement->bindParam(':auditor', $_POST['scheduleAuditor']);
            $statement->bindParam(':lunch', $_POST['lunch']);
            $statement->bindParam(':timeFrom', $_POST["timeFrom"]);
            $statement->bindParam(':timeTo', $_POST['timeTo']);
            $statement->execute();
//        echo "2";
            $sql = "SELECT max(LAST_INSERT_ID(audit_schedule_id)) as id From audit_schedule_id";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $schedule_id = $row['id'];
//        echo "3";
            for ($i = 0; $i < count($scheduleActivity); ++$i) {
                if ($scheduleActivity[$i] != "") { $temp=nl2br($scheduleActivity[$i]);
                    $schedule = "INSERT INTO audit_schedule (audit_scheduleid, schedule_service_id, schedule_dates, schedule_time, schedule_activity, schedule_auditors) "
                            . "VALUES (:id,:service,:date,:time,:activity,:auditor)";
                    $statement = $db->prepare($schedule);
                    $statement->bindParam(':id', $schedule_id);
                    $statement->bindParam(':service', $service);
                    $statement->bindParam(':date', $scheduleDate[$i]);
                    $statement->bindParam(':time', $scheduleTime[$i]);
                    $statement->bindParam(':activity', $temp);
                    $statement->bindParam(':auditor', $scheduleAuditor[$i]);
                    $statement->execute();
                }
            }
//        echo "4";
            header("Location: customerAccount.php?customerid=" . $customer);
        }
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_POST['schedule-audit-id-edit']) && !empty($_POST['schedule-audit-id-edit']) && isset($_POST['schedule-service-id-edit']) && !empty($_POST['schedule-service-id-edit'])) {
        try {
//            echo $_POST['schedule-audit-id-edit']; die;
            if (isset($_POST['scheduleAuditDateEdit']) && !empty($_POST['scheduleAuditDateEdit'])) {
                $dateSchedule = date('Y-m-d', strtotime($_POST['scheduleAuditDateEdit']));
            } else {
                $dateSchedule = NULL;
            }

            if (isset($_POST['editstageiRevDate']) && !empty($_POST['editstageiRevDate'])) {
                $revdate = date("Y-m-d", strtotime($_POST['editstageiRevDate']));
            } else {
                $revdate = NULL;
            }

            if (isset($_POST['editstageiIsueDate']) && !empty($_POST['editstageiIsueDate'])) {
                $isueDate = date("Y-m-d", strtotime($_POST['editstageiIsueDate']));
            } else {
                $isueDate = NULL;
            }

            $scheduleId = $_POST['schedule-audit-id-edit'];
            $serviceid = $_POST['schedule-service-id-edit'];
            $customerid = $_POST['schedule-customer-id-edit'];

            $scheduleDate = explode('~', $_POST['scheduleDateHidden-edit']);
           // print_r($scheduleDate);
            $scheduleTime = explode('~', $_POST['schedule1TimeHidden-edit']);
            // print_r($scheduleTime);
            $scheduleActivity = explode('~', $_POST['scheduleActivityHidden-edit']);
            //print_r($scheduleActivity);
            $scheduleAuditor = explode('~', $_POST['scheduleAuditorHidden-edit']);
            //print_r($scheduleAuditor);

            $scheduleIdupdate = "UPDATE audit_schedule_id SET client_name = :client, ref_no = :refno, schedule_address = :address, schedule_contact_person = :contactperson, schedule_scope = :scope, schedule_app_standard = :appstandard, "
                    . "schedule_aifcode = :iafcode,schedule_nacfcode= :nacecode,schedule_naceclass= :naceclass, schedule_audit_type = :audittype, schedule_mandays = :mandays, schedule_audit_date = :auditDate, schedule_auditors = :auditor, stage_i_form = :form_no, stage_i_rev = :rev, "
                    . "stage_i_date = :rev_date, stage_i_isue = :isue, stage_i_isuedate = :isueDate, break = :lunch, break_from = :timeFrom, break_to = :timeTo WHERE audit_schedule_id = :id ";
            $statement = $db->prepare($scheduleIdupdate);
            $statement->bindParam(':client', $_POST['scheduleClientNameEdit']);
            $statement->bindParam(':refno', $_POST['scheduleRefNoEdit']);
            $statement->bindParam(':address', $_POST['scheduleAddressEdit']);
            $statement->bindParam(':contactperson', $_POST['scheduleContactPersonEdit']);
            $statement->bindParam(':scope', $_POST['scheduleScopeEdit']);
            $statement->bindParam(':appstandard', $_POST['scheduleAppStandardEdit']);
            $statement->bindParam(':iafcode', $_POST['scheduleIAFcodeEdit']);
            $statement->bindParam(':nacecode', $_POST['scheduleNacecodeEdit']);
            $statement->bindParam(':naceclass', $_POST['scheduleNaceclassEdit']);
            $statement->bindParam(':audittype', $_POST['scheduleAuditTypeEdit']);
            $statement->bindParam(':mandays', $_POST['scheduleMandaysEdit']);
            $statement->bindParam(':auditDate', $dateSchedule);
            $statement->bindParam(':auditor', $_POST['scheduleAuditorEdit']);
            $statement->bindParam(':id', $scheduleId);
            $statement->bindParam(':form_no', $_POST['editstageiFormNo']);
            $statement->bindParam(':rev', $_POST['editstageiRev']);
            $statement->bindParam(':rev_date', $revdate);
            $statement->bindParam(':isue', $_POST['editstageiIsue']);
            $statement->bindParam(':isueDate', $isueDate);
            $statement->bindParam(':lunch', $_POST['editlunch']);
            $statement->bindParam(':timeFrom', $_POST["edittimeFrom"]);
            $statement->bindParam(':timeTo', $_POST['edittimeTo']);
            $statement->execute();

            $deletSchedule = "DELETE FROM audit_schedule WHERE audit_scheduleid = :id ";
            $statement = $db->prepare($deletSchedule);
            $statement->bindParam(':id', $scheduleId);
            $statement->execute();

            for ($i = 0; $i < count($scheduleAuditor); ++$i) {
                if ($scheduleActivity[$i] != "") { 
                    $schedule = "INSERT INTO audit_schedule (audit_scheduleid, schedule_service_id, schedule_dates, schedule_time, schedule_activity, schedule_auditors) "
                            . "VALUES (:id,:service,:date,:time,:activity,:auditor)";
                    $statement = $db->prepare($schedule);
                    $statement->bindParam(':id', $scheduleId);
                    $statement->bindParam(':service', $serviceid);
                    $statement->bindParam(':date', $scheduleDate[$i]);
                    $statement->bindParam(':time', $scheduleTime[$i]);
                    $statement->bindParam(':activity', $scheduleActivity[$i]);
                    $statement->bindParam(':auditor', $scheduleAuditor[$i]);
                    $statement->execute();
                }
            }
            header("Location: customerAccount.php?customerid=" . $customerid);
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}
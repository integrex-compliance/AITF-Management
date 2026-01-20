<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST["servilence2_id"]) && !empty($_POST["servilence2_id"])) {
    try {
        $service = $_POST['servelance-service-id'];
        $customer = $_POST['servelance-customer-id'];
        $scheduleTime = explode('~', $_POST['schedule2TimeHidden']);
        //print_r($scheduleTime);
        $scheduleClause = explode('~', $_POST['schedule2ClauseHidden']);
        //print_r($scheduleClause);
        $scheduleActivity = explode('~', $_POST['servelance2ActivityHidden']);
//        print_r($scheduleActivity); 
        $scheduleAuditor = explode('~', $_POST['servelance2AuditorHidden']);
        $scheduleDateHidden = $_POST['schedule2DateHidden'];
        $scheduleDate = explode('~', $scheduleDateHidden);
        print_r($scheduleAuditor);

        $sql1 = "UPDATE `servelance_1_id` SET `client_name`=:client,`ios_standards`=:appstandard,"
                . "`sites`=:sites,`mandays`=:mandays,`certification_scope`=:certificationScope, audit_type = :auditType,"
                . "`auditors_name`=:auditor, break=:lunch, time_from=:from, time_to=:to, audit_dates= :audate,"
                . "prepared_by = :preparedby, prepared_date = :preparedDate WHERE `servelance_id` = :servilence_id";
        $statement = $db->prepare($sql1);
        $statement->bindParam(':client', $_POST['scheduleClientName']);
        $statement->bindParam(':appstandard', $_POST['scheduleAppStandard']);
        $statement->bindParam(':sites', $_POST['sites']);
        $statement->bindParam(':mandays', $_POST['scheduleMandays']);
        $statement->bindParam(':certificationScope', $_POST['certificationScope']);
        $statement->bindParam(':auditor', $_POST['scheduleAuditor']);
        $statement->bindParam(':auditType', $_POST['scheduleIIAuditType']);
        $statement->bindParam(':lunch', $_POST['stage2schedule']);
        $statement->bindParam(':from', $_POST['stage2timeFrom']);
        $statement->bindParam(':to', $_POST['stage2timeTo']);
        $statement->bindParam(':audate', $_POST['scheduleIIDate']);
        $statement->bindParam(':servilence_id', $_POST['servilence2_id']);
        $statement->bindParam(':preparedby', $_POST['preparedByScheduleII']);
        $statement->bindParam(':preparedDate', date('Y-m-d', strtotime($_POST['dateStageScheduleII'])));
        $statement->execute();

        $sql2 = "DELETE FROM `servelance_audit` WHERE `servelance_id` = :id";
        $statement = $db->prepare($sql2);
        $statement->bindParam(":id", $_POST['servilence2_id']);
        $statement->execute();

        for ($i = 0; $i < count($scheduleActivity); ++$i) {
            if ($scheduleActivity[$i] != "") {
                $schedule = "INSERT INTO `servelance_audit`(`servelance_id`, `service_id`, `time_schedule`, `activity`, `clause`, `auditor`, `schedule_dates`) "
                        . "VALUES (:id, :service, :time, :activity, :clause,:auditor,:date)";
                $statement = $db->prepare($schedule);
                $statement->bindParam(':id', $_POST['servilence2_id']);
                $statement->bindParam(':service', $service);
                $statement->bindParam(':time', $scheduleTime[$i]);
                $statement->bindParam(':date', $scheduleDate[$i]);
                $statement->bindParam(':activity', $scheduleActivity[$i]);
                $statement->bindParam(':clause', $scheduleClause[$i]);
                $statement->bindParam(':auditor', $scheduleAuditor[$i]);
                $statement->execute();
            }
        }
        header("Location: customerAccount.php?customerid=" . $customer);
    } catch (Exception $ex) {
        
    }
} else {
    try {
        $service = $_POST['servelance-service-id'];
        $customer = $_POST['servelance-customer-id'];
        $scheduleTime = explode('~', $_POST['schedule2TimeHidden']);
        //print_r($scheduleTime);
        $scheduleClause = explode('~', $_POST['schedule2ClauseHidden']);
        //print_r($scheduleClause);
        $scheduleActivity = explode('~', $_POST['servelance2ActivityHidden']);
        $scheduleDate = explode('~', $_POST['schedule2DateHidden']);
//        print_r($scheduleActivity);die;
        $scheduleAuditor = explode('~', $_POST['servelance2AuditorHidden']);
        //print_r($scheduleDate); die;

        if (isset($_POST['addScheduleStage'])) {
            $scheduleid = "INSERT INTO servelance_1_id "
                    . "(`service_id`, `customer_id`, `client_name`, `ios_standards`, `sites`, `mandays`, `certification_scope`, audit_type, `auditors_name`, break, time_from, time_to, audit_dates, prepared_by, prepared_date)"
                    . "VALUES (:service, :customer, :client, :appstandard, :sites, :mandays, :certificationScope, :auditType, :auditor, :lunch, :from, :to, :audate, :preparedby, :preparedDate)";
            $statement = $db->prepare($scheduleid);
            $statement->bindParam(':service', $service);
            $statement->bindParam(':customer', $customer);
            $statement->bindParam(':client', $_POST['scheduleClientName']);
            $statement->bindParam(':appstandard', $_POST['scheduleAppStandard']);
            $statement->bindParam(':sites', $_POST['sites']);
            $statement->bindParam(':mandays', $_POST['scheduleMandays']);
            $statement->bindParam(':certificationScope', $_POST['certificationScope']);
            $statement->bindParam(':auditType', $_POST['scheduleIIAuditType']);
            $statement->bindParam(':auditor', $_POST['scheduleAuditor']);
            $statement->bindParam(':lunch', $_POST['stage2schedule']);
            $statement->bindParam(':from', $_POST['stage2timeFrom']);
            $statement->bindParam(':to', $_POST['stage2timeTo']);
            $statement->bindParam(':audate', $_POST['scheduleIIDate']);
            $statement->bindParam(':preparedby', $_POST['preparedByScheduleII']);
            $statement->bindParam(':preparedDate', date('Y-m-d', strtotime($_POST['dateStageScheduleII'])));
            $statement->execute();
//        echo "2";
            $sql = "SELECT max(LAST_INSERT_ID(servelance_id)) as id From servelance_1_id";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $schedule_id = $row['id'];
//        echo "3";
            for ($i = 0; $i < count($scheduleActivity); ++$i) {
                if ($scheduleActivity[$i] != "") {
                    $temp = nl2br($scheduleActivity[$i]);
                    $schedule = "INSERT INTO `servelance_audit`(`servelance_id`, `service_id`, `time_schedule`, `activity`, `clause`, `auditor`,`schedule_dates`) "
                            . "VALUES (:id, :service, :time, :activity, :clause,:auditor,:date)";
                    $statement = $db->prepare($schedule);
                    $statement->bindParam(':id', $schedule_id);
                    $statement->bindParam(':service', $service);
                    $statement->bindParam(':time', $scheduleTime[$i]);
                    $statement->bindParam(':date', $scheduleDate[$i]);
                    $statement->bindParam(':activity', $temp);
                    $statement->bindParam(':clause', $scheduleClause[$i]);
                    $statement->bindParam(':auditor', $scheduleAuditor[$i]);
                    $statement->execute();
                }
            }
            header("Location: customerAccount.php?customerid=" . $customer);
        }
    } catch (Exception $ex) {
        echo $ex;
    }
}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
require_once('tcpdf_include.php');
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST['scheduleii-id']) && !empty($_POST['scheduleii-id'])) {
    try {
        $scheduleid = $_POST['scheduleii-id'];
        $service = $_POST['scheduleii-service-id'];
        $customer = $_POST['scheduleii-customer-id'];
        $scheduledate = date('Y-m-d', strtotime($_POST['scheduleIIDate']));
        if (isset($_POST['scheduleIIDate']) && !empty($_POST['scheduleIIDate'])) {

            $scheduledate = date('Y-m-d', strtotime($_POST['scheduleIIDate']));
        } else {
            $scheduledate = NULL;
        }

        $dates = explode('~', $_POST['scheduleIIDatesHidden']);
        $time = explode('~', $_POST['scheduleIITimeHidden']);
        $referance = explode('~', $_POST['scheduleIIClaudeReferenceHidden']);
        $auditor = explode('~', $_POST['scheduleIIAuditorHidden']);
        $department = explode('~', $_POST['scheduleIIDepartmentHidden']);

        $updateSchedule = "UPDATE audit_scheduleii_id SET clieint_name = :client, scheduleii_standards = :standard, scheduleii_address = :address, scheduleii_date = :date, scheduleii_cert_scope = :scope, scheduleii_mandays = :mandays, scheduleii_mgmt = :mgmtrep, audit_type = :auditType, scheduleii_auditors = :auditor,"
                . " break = :stageIIlunch, break_from = :stageIItimeFrom, break_to = :stageIItimeTo, prepared_by = :preparedby, prepared_date = :preparedDate WHERE audit_scheduleii_id = :id";
        $statement = $db->prepare($updateSchedule);
        $statement->bindParam(':client', $_POST['scheduleIIClientName']);
        $statement->bindParam(':standard', $_POST['scheduleIIStandard']);
        $statement->bindParam(':address', $_POST['scheduleIIAddress']);
        $statement->bindParam(':date', $_POST['scheduleIIDate']);
        $statement->bindParam(':scope', $_POST['scheduleIICertScope']);
        $statement->bindParam(':mandays', $_POST['scheduleIIMandays']);
        $statement->bindParam(':mgmtrep', $_POST['scheduleIIMgmtRep']);
        $statement->bindParam(':auditType', $_POST['scheduleIIAuditType']);
        $statement->bindParam(':auditor', $_POST['scheduleIIAuditorName']);
        $statement->bindParam(':stageIIlunch', $_POST['stageIIlunch']);
        $statement->bindParam(':stageIItimeFrom', $_POST['stageIItimeFrom']);
        $statement->bindParam(':stageIItimeTo', $_POST['stageIItimeTo']);
        $statement->bindParam(':preparedby', $_POST['preparedByStageII']);
        $statement->bindParam(':preparedDate', date('Y-m-d', strtotime($_POST['dateStageII'])));
        $statement->bindParam(':id', $scheduleid);
        $statement->execute();

        $deleteScheduleii = "DELETE FROM audit_scheduleii WHERE scheduleii_id = :id ";
        $statement = $db->prepare($deleteScheduleii);
        $statement->bindParam(':id', $scheduleid);
        $statement->execute();

        for ($i = 0; $i < count($department); ++$i) {
            if ($department [$i] != "") {
                $newdata = $dates[$i];
                $newtime = $time[$i];
                $scheduleii_details = "INSERT INTO audit_scheduleii (scheduleii_id, ca_service, audit_scheduleii_dates, audit_scheduleii_time, audit_scheduleii_department, clause_ref, audit_scheduleii_auditor)"
                        . "VALUES (:schedule, :service, :auditdate, :audittime, :department, :referance, :auditor)";
                $statement = $db->prepare($scheduleii_details);
                $statement->bindParam(':schedule', $scheduleid);
                $statement->bindParam(':service', $service);
                $statement->bindParam(':auditdate', $newdata);
                $statement->bindParam(':audittime', $newtime);
                $statement->bindParam(':department', $department[$i]);
                $statement->bindParam(':referance', $referance[$i]);
                $statement->bindParam(':auditor', $auditor[$i]);
                $statement->execute();
            }
        }

        header("Location: customerAccount.php?customerid=" . $customer);
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_POST['scheduleii-service-id']) && !empty($_POST['scheduleii-service-id']) && isset($_POST['scheduleii-customer-id']) && !empty($_POST['scheduleii-customer-id'])) {
        try {
            $service = $_POST['scheduleii-service-id'];
            $customer = $_POST['scheduleii-customer-id'];
            //$scheduledate = date('Y-m-d', strtotime($_POST['scheduleIIDate']));
            if (isset($_POST['scheduleIIDate']) && !empty($_POST['scheduleIIDate'])) {
                $scheduledate = date('Y-m-d', strtotime($_POST['scheduleIIDate']));
            } else {
                $scheduledate = NULL;
            }

            $dates = explode('~', $_POST['scheduleIIDatesHidden']);
            $time = explode('~', $_POST['scheduleIITimeHidden']);
            $referance = explode('~', $_POST['scheduleIIClaudeReferenceHidden']);
            $auditor = explode('~', $_POST['scheduleIIAuditorHidden']);
            $department = explode('~', $_POST['scheduleIIDepartmentHidden']);

            if (isset($_POST['addscheduleIIAudit'])) {

                $scheduleii = "INSERT INTO audit_scheduleii_id (ca_service, ca_customer, clieint_name, scheduleii_standards, scheduleii_address, scheduleii_date, scheduleii_cert_scope, scheduleii_mandays, scheduleii_mgmt, audit_type, scheduleii_auditors, break, break_from, break_to, prepared_by, prepared_date) "
                        . "VALUES (:service, :customer, :client, :standard, :address, :date, :scope, :mandays, :mgmtrep, :auditType, :auditor, :stageIIlunch, :stageIItimeFrom, :stageIItimeTo, :preparedby, :preparedDate)";
                $statement = $db->prepare($scheduleii);
                $statement->bindParam(':service', $service);
                $statement->bindParam(':customer', $customer);
                $statement->bindParam(':client', $_POST['scheduleIIClientName']);
                $statement->bindParam(':standard', $_POST['scheduleIIStandard']);
                $statement->bindParam(':address', $_POST['scheduleIIAddress']);
                $statement->bindParam(':date', $_POST['scheduleIIDate']);
                $statement->bindParam(':scope', $_POST['scheduleIICertScope']);
                $statement->bindParam(':mandays', $_POST['scheduleIIMandays']);
                $statement->bindParam(':mgmtrep', $_POST['scheduleIIMgmtRep']);
                $statement->bindParam(':auditType', $_POST['scheduleIIAuditType']);
                $statement->bindParam(':auditor', $_POST['scheduleIIAuditorName']);
                $statement->bindParam(':stageIIlunch', $_POST['stageIIlunch']);
                $statement->bindParam(':stageIItimeFrom', $_POST['stageIItimeFrom']);
                $statement->bindParam(':stageIItimeTo', $_POST['stageIItimeTo']);
                $statement->bindParam(':preparedby', $_POST['preparedByStageII']);
                $statement->bindParam(':preparedDate', date('Y-m-d', strtotime($_POST['dateStageII'])));
                $statement->execute();

                $sql = "SELECT max(LAST_INSERT_ID(audit_scheduleii_id)) as id From audit_scheduleii_id";
                $statement = $db->prepare($sql);
                $statement->execute();
                $row = $statement->fetch();
                $auditscheduleiiid = $row['id'];

                for ($i = 0; $i < count($department); ++$i) {
                    if ($department [$i] != "") {
                        $newdata = $dates[$i];
                        $newtime = $time[$i];
                        $scheduleii_details = "INSERT INTO audit_scheduleii (scheduleii_id, ca_service, audit_scheduleii_dates, audit_scheduleii_time, audit_scheduleii_department, clause_ref, audit_scheduleii_auditor)"
                                . "VALUES (:schedule, :service, :auditdate, :audittime, :department, :referance, :auditor)";
                        $statement = $db->prepare($scheduleii_details);
                        $statement->bindParam(':schedule', $auditscheduleiiid);
                        $statement->bindParam(':service', $service);
                        $statement->bindParam(':auditdate', $newdata);
                        $statement->bindParam(':audittime', $newtime);
                        $statement->bindParam(':department', $department[$i]);
                        $statement->bindParam(':referance', $referance[$i]);
                        $statement->bindParam(':auditor', $auditor[$i]);
                        $statement->execute();
                    }
                }
                header("Location: customerAccount.php?customerid=" . $customer);
            }
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}

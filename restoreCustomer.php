<?php


session_start();
include_once './dbConnect.php';
include_once './include/customer_recycle_helper.php';
date_default_timezone_set('Asia/Kolkata');

$isAjax = isset($_REQUEST['is_ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

// Restore service
if (isset($_REQUEST['restoreServiceId']) || isset($_REQUEST['restore_service_id'])) {
    $serviceId = !empty($_REQUEST['restoreServiceId']) ? $_REQUEST['restoreServiceId'] : $_REQUEST['restore_service_id'];
    try {
        $result = aitfRestoreService($db, $serviceId);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Service restored successfully']);
            exit;
        }
        header("Location: clientRecycle.php?tab=services&msg=service_restored");
        exit;
    } catch (Exception $ex) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $ex->getMessage()]);
            exit;
        }
        echo $ex->getMessage();
        exit;
    }
}

// Permanently delete service
if (isset($_REQUEST['deleteServicePermanent']) || isset($_REQUEST['delete_service_permanent'])) {
    $serviceId = !empty($_REQUEST['deleteServicePermanent']) ? $_REQUEST['deleteServicePermanent'] : $_REQUEST['delete_service_permanent'];
    try {
        $result = aitfPermanentlyDeleteService($db, $serviceId);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Service permanently deleted']);
            exit;
        }
        header("Location: clientRecycle.php?tab=services&msg=service_deleted");
        exit;
    } catch (Exception $ex) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $ex->getMessage()]);
            exit;
        }
        echo $ex->getMessage();
        exit;
    }
}

// Empty Recycle Bin
if ((isset($_REQUEST['action']) && $_REQUEST['action'] === 'empty_bin') || isset($_REQUEST['empty_bin'])) {
    $targetTab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'all';
    try {
        aitfEmptyRecycleBin($db, $targetTab);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Recycle bin has been emptied successfully']);
            exit;
        }
        header("Location: clientRecycle.php?msg=bin_emptied");
        exit;
    } catch (Exception $ex) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $ex->getMessage()]);
            exit;
        }
        echo $ex->getMessage();
        exit;
    }
}

// Restore customer
if (isset($_POST['customerid']) && !empty($_POST['customerid'])) {
    try {
        $customerDlt = "update customer_info set is_deleted = 'NO', deleted_date = NULL WHERE id = :id";
        $statement = $db->prepare($customerDlt);
        $statement->bindParam(':id', $_POST['customerid']);
        $statement->execute();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Client restored successfully']);
            exit;
        }
        header("Location: clientRecycle.php?tab=clients&msg=client_restored");
        exit;
    } catch (Exception $ex) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $ex->getMessage()]);
            exit;
        }
        echo $ex->getMessage();
        exit;
    }
}

if (isset($_POST["customerDlt"]) && !empty($_POST["customerDlt"])) {
    try {

        $sql1 = "DELETE FROM `allotment_count` WHERE `client_id` = :id";
        $statement = $db->prepare($sql1);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql2 = "DELETE FROM `assessment_planning` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql2);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql3 = "SELECT service_id FROM `service` where customer_id = :id";
        $statement = $db->prepare($sql3);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $svcids = $statement->fetchAll();

        foreach ($svcids as $svc) {
            $sql4 = "SELECT audit_id FROM `audit_application` where uni_svc_id = :svcs";
            $statement = $db->prepare($sql4);
            $statement->bindParam(":svcs", $svc["service_id"]);
            $statement->execute();
            $auditid = $statement->fetch();

            $sql5 = "DELETE FROM `audit_iaf_nace` WHERE `audit_id` = :id";
            $statement = $db->prepare($sql5);
            $statement->bindParam(":id", $auditid["audit_id"]);
            $statement->execute();

            $sql6 = "DELETE FROM `audit_application` WHERE `uni_svc_id` = :svcs";
            $statement = $db->prepare($sql6);
            $statement->bindParam(":svcs", $svc["service_id"]);
            $statement->execute();

            $sql = "DELETE FROM `ca_service_dates` WHERE `service_id` = :svcs";
            $statement = $db->prepare($sql);
            $statement->bindParam(":svcs", $svc["service_id"]);
            $statement->execute();
        }

        $sql7 = "SELECT `audit_schedule_id` FROM `audit_schedule_id` WHERE `customer_ca_id` = :id";
        $statement = $db->prepare($sql7);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $auditschedule = $statement->fetchAll();

        foreach ($auditschedule as $auditschedule) {
            $sql8 = "DELETE FROM `audit_schedule` WHERE `audit_scheduleid` = :ids";
            $statement = $db->prepare($sql8);
            $statement->bindParam(":ids", $auditschedule["audit_schedule_id"]);
            $statement->execute();
        }

        $sql9 = "DELETE FROM `audit_schedule_id` WHERE `customer_ca_id` = :id";
        $statement = $db->prepare($sql9);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql10 = "SELECT `audit_scheduleii_id` FROM `audit_scheduleii_id` WHERE `ca_customer` = :id";
        $statement = $db->prepare($sql10);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $schedule2 = $statement->fetchAll();

        foreach ($schedule2 as $schedule) {
            $sql11 = "DELETE FROM `audit_scheduleii` WHERE `scheduleii_id` = :id";
            $statement = $db->prepare($sql11);
            $statement->bindParam(":id", $schedule["audit_scheduleii_id"]);
            $statement->execute();
        }

        $sql12 = "DELETE FROM `audit_scheduleii_id` WHERE `ca_customer` = :id";
        $statement = $db->prepare($sql12);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql13 = "DELETE FROM `cdc_report_review` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql13);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql14 = "DELETE FROM `certificate_print` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql14);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql15 = "SELECT `contractid` FROM `contract_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql15);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $contracts = $statement->fetchAll();

        foreach ($contracts as $contract) {
            $sql16 = "DELETE FROM `contract_review` WHERE `ca_service_contract` = :id";
            $statement = $db->prepare($sql16);
            $statement->bindParam(":id", $contract['contractid']);
            $statement->execute();
        }

        $sql17 = "DELETE FROM `contract_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql17);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql18 = "SELECT `control_id` FROM `control_formati_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql18);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $cfs1 = $statement->fetchAll();

        foreach ($cfs1 as $cfs) {
            $sql19 = "DELETE FROM `control_formati` WHERE `control_id` = :ids";
            $statement = $db->prepare($sql19);
            $statement->bindParam(":ids", $cfs['control_id']);
            $statement->execute();
        }

        $sql20 = "DELETE FROM `control_formati_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql20);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql21 = "SELECT `control2_id` FROM `control_formatii_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql21);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $cfs2 = $statement->fetchAll();

        foreach ($cfs2 as $cfs) {
            $sql19 = "DELETE FROM `control_formatii` WHERE `control2_id` = :ids";
            $statement = $db->prepare($sql19);
            $statement->bindParam(":ids", $cfs['control2_id']);
            $statement->execute();
        }

        $sql20 = "DELETE FROM `control_formatii_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql20);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql21 = "SELECT `survey_id` FROM `customer_survey_id` WHERE `customer_ca` = :id";
        $statement = $db->prepare($sql21);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $survayid = $statement->fetchAll();

        foreach ($survayid as $survay) {
            $sql22 = "DELETE FROM `customer_survey` WHERE `survayid` = :id";
            $statement = $db->prepare($sql22);
            $statement->bindParam(":id", $survay["survey_id"]);
            $statement->execute();
        }

        $sql23 = "DELETE FROM `customer_survey_id` WHERE `customer_ca` = :id";
        $statement = $db->prepare($sql23);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

//        $sql24 = "SELECT `invoice_id` FROM `invoice_details` WHERE `customer_id` = :id";
//        $statement = $db->prepare($sql24);
//        $statement->bindParam(":id", $_POST['customerDlt']);
//        $statement->execute();
//        $invoices = $statement->fetchAll();

//        foreach ($invoices as $inv) {
//            $sql25 = "DELETE FROM `invoice_services` WHERE `invoice_ids` = :id";
//            $statement = $db->prepare($sql25);
//            $statement->bindParam(":id", $inv['invoice_id']);
//            $statement->execute();
//        }
//
//        $sql26 = "DELETE FROM `invoice_details` WHERE `customer_id` = :id ";
//        $statement = $db->prepare($sql26);
//        $statement->bindParam(":id", $_POST['customerDlt']);
//        $statement->execute();
//
//        $sql27 = "DELETE FROM `payment_system_id` WHERE `customer_id` = :id";
//        $statement = $db->prepare($sql27);
//        $statement->bindParam(":id", $_POST['customerDlt']);
//        $statement->execute();
//
//        $sql28 = "DELETE FROM `payment_system` WHERE `customer_id` = :id";
//        $statement = $db->prepare($sql28);
//        $statement->bindParam(":id", $_POST['customerDlt']);
//        $statement->execute();

        $sql29 = "SELECT `servelance_id` FROM `servelance_1_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql29);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $servelances = $statement->fetchAll();

        foreach ($servelances as $servelance) {
            $sql30 = "DELETE FROM `servelance_audit` WHERE `servelance_id` = :id";
            $statement = $db->prepare($sql30);
            $statement->bindParam(":id", $servelance['servelance_id']);
            $statement->execute();
        }

        $sql31 = "DELETE FROM `servelance_1_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql31);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql32 = "SELECT `stage_i_id` FROM `stage_i_audit_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql32);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $stage1audit = $statement->fetchAll();

        foreach ($stage1audit as $audit) {
            $sql33 = "DELETE FROM `stage_i_audit` WHERE `ca_service_audit` = :id";
            $statement = $db->prepare($sql33);
            $statement->bindParam(":id", $audit["stage_i_id"]);
            $statement->execute();
        }

        $sql34 = "DELETE FROM `stage_i_audit_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql34);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql35 = "SELECT `stage_ii_id` FROM `stage_ii_audit_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql35);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $stage2audit = $statement->fetchAll();

        foreach ($stage2audit as $audit) {
            $sql36 = "DELETE FROM `stage_ii_audit` WHERE `ca_service_audit_ii` = :id";
            $statement = $db->prepare($sql36);
            $statement->bindParam(":id", $audit['stage_ii_id']);
            $statement->execute();
        }

        $sql37 = "DELETE FROM `stage_ii_audit_id` WHERE  `customer_id` = :id";
        $statement = $db->prepare($sql37);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql38 = "SELECT `audit_stage_id` FROM `stage_oneaudit_id` WHERE `ca_customer_id` = :id";
        $statement = $db->prepare($sql38);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $stage1 = $statement->fetchAll();

        foreach ($stage1 as $stag) {
            $sql39 = "DELETE FROM `stage_audit_one` WHERE `audit_stage_id` = :id";
            $statement = $db->prepare($sql39);
            $statement->bindParam(":id", $stag['audit_stage_id']);
            $statement->execute();
        }

        $sql40 = "DELETE FROM `stage_oneaudit_id` WHERE `ca_customer_id` = :id";
        $statement = $db->prepare($sql40);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql41 = "SELECT `audit_stageii_id` FROM `stage_twoaudit_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql41);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        $stage1 = $statement->fetchAll();

        foreach ($stage1 as $stag) {
            $sql42 = "DELETE FROM `stage_audit_two` WHERE `audit_stageii_id` = :id";
            $statement = $db->prepare($sql42);
            $statement->bindParam(":id", $stag['audit_stageii_id']);
            $statement->execute();
        }

        $sql43 = "DELETE FROM `stage_twoaudit_id` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql43);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql144 = "DELETE FROM service_type_cr WHERE customer_id = :id";
        $statement = $db->prepare($sql144);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        
        $sql44 = "DELETE FROM `service_type_ca` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql44);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql45 = "DELETE FROM `service_type_sai` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql45);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql46 = "DELETE FROM `service_type_saii` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql46);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql47 = "DELETE FROM `service_type_rc` WHERE `rc_customer` = :id";
        $statement = $db->prepare($sql47);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql48 = "DELETE FROM `service_type_sp` WHERE `sp_customer` = :id";
        $statement = $db->prepare($sql48);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();
        
        $sql244 = "DELETE FROM service_type_ptr WHERE customer_id = :id";
        $statement = $db->prepare($sql244);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $sql49 = "DELETE FROM `service` WHERE `customer_id` = :id";
        $statement = $db->prepare($sql49);
        $statement->bindParam(":id", $_POST['customerDlt']);
        $statement->execute();

        $customerDlt = "delete from customer_info WHERE id = :id";
        $statement = $db->prepare($customerDlt);
        $statement->bindParam(':id', $_POST['customerDlt']);
        $statement->execute();
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Client permanently deleted']);
            exit;
        }
        header("Location: clientRecycle.php?tab=clients&msg=client_deleted");
        exit;
    } catch (Exception $ex) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $ex->getMessage()]);
            exit;
        }
        echo $ex->getMessage();
    }
}

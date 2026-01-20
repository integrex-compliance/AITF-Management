<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST['service']) && !empty($_POST['service'])) {
    try {
        $customerStatus = "in-process";
        $caService = "SELECT service, customer_id FROM service_type_ca WHERE service = :service";
        $statement = $db->prepare($caService);
        $statement->bindParam(':service', $_POST['service']);
        $statement->execute();
        $caServiceID = $statement->fetch();

        if ($caServiceID['service']) {
            $caUpdate = "UPDATE service_type_ca SET service_ca_status = 'Audit planning'  WHERE service = :id ";
            $statement = $db->prepare($caUpdate);
            $statement->bindParam(':id', $_POST['service']);
            $statement->execute();

            $serviceUpdate = "UPDATE service SET service_status = 'Audit planning' WHERE service_id = :id";
            $statement = $db->prepare($serviceUpdate);
            $statement->bindParam(':id', $_POST['service']);
            $statement->execute();
            
            $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
            $statement = $db->prepare($updateCustomerStatus);
            $statement->bindParam(':status', $customerStatus);
            $statement->bindParam(':customer', $caServiceID['customer_id']);
            $statement->execute();
        }

        $saiService = "SELECT service, customer_id FROM service_type_sai WHERE service = :service ";
        $statement = $db->prepare($saiService);
        $statement->bindParam(':service', $_POST['service']);
        $statement->execute();
        $saiServiceID = $statement->fetch();

        if ($saiServiceID['service']) {
            $saiUpdate = "UPDATE service_type_sai SET service_sai_status = 'Audit planning' WHERE service = :id";
            $statement = $db->prepare($saiUpdate);
            $statement->bindParam(':id', $_POST['service']);
            $statement->execute();

            $serviceUpdate = "UPDATE service SET service_status = 'Audit planning' WHERE service_id = :id";
            $statement = $db->prepare($serviceUpdate);
            $statement->bindParam(':id', $_POST['service']);
            $statement->execute();
            
            $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
            $statement = $db->prepare($updateCustomerStatus);
            $statement->bindParam(':status', $customerStatus);
            $statement->bindParam(':customer', $saiServiceID['customer_id']);
            $statement->execute();
        }

        $saiiService = "SELECT service, customer_id FROM service_type_saii WHERE service = :service";
        $statement = $db->prepare($saiiService);
        $statement->bindParam(':service', $_POST['service']);
        $statement->execute();
        $saiiServiceId = $statement->fetch();

        if ($saiiServiceId['service']) {
            $saiiUpdate = "UPDATE service_type_saii SET service_saii_ststus = 'Audit planning'  WHERE service = :id";
            $statement = $db->prepare($saiiUpdate);
            $statement->bindParam(':id', $_POST['service']);
            $statement->execute();

            $serviceUpdate = "UPDATE service SET service_status = 'Audit planning' WHERE service_id = :id";
            $statement = $db->prepare($serviceUpdate);
            $statement->bindParam(':id', $_POST['service']);
            $statement->execute();
            
            $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
            $statement = $db->prepare($updateCustomerStatus);
            $statement->bindParam(':status', $customerStatus);
            $statement->bindParam(':customer', $saiiServiceId['customer_id']);
            $statement->execute();
        }

        $rcService = "SELECT service, rc_customer FROM service_type_rc WHERE service = :service";
        $statement = $db->prepare($rcService);
        $statement->bindParam(':service', $_POST['service']);
        $statement->execute();
        $rcServiceId = $statement->fetch();

        if ($rcServiceId['service']) {
            $saiiUpdate = "UPDATE service_type_rc SET service_rc_status = 'Audit planning' WHERE service = :id";
            $statement = $db->prepare($saiiUpdate);
            $statement->bindParam(':id', $_POST['service']);
            $statement->execute();

            $serviceUpdate = "UPDATE service SET service_status = 'Audit planning' WHERE service_id = :id";
            $statement = $db->prepare($serviceUpdate);
            $statement->bindParam(':id', $_POST['service']);
            $statement->execute();
            
            $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
            $statement = $db->prepare($updateCustomerStatus);
            $statement->bindParam(':status', $customerStatus);
            $statement->bindParam(':customer', $rcServiceId['rc_customer']);
            $statement->execute();
        }

        $spService = "SELECT service, sp_customer FROM service_type_sp WHERE service = :service";
        $statement = $db->prepare($spService);
        $statement->bindParam(':service', $_POST['service']);
        $statement->execute();
        $spServiceId = $statement->fetch();

        if ($spServiceId['service']) {
            $spUpdate = "UPDATE service_type_sp SET service_sp_status = 'Audit planning' WHERE service = :id";
            $statement = $db->prepare($spUpdate);
            $statement->bindParam(':id', $_POST['service']);
            $statement->execute();

            $serviceUpdate = "UPDATE service SET service_status = 'Audit planning' WHERE service_id = :id";
            $statement = $db->prepare($serviceUpdate);
            $statement->bindParam(':id', $_POST['service']);
            $statement->execute();
            
            $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
            $statement = $db->prepare($updateCustomerStatus);
            $statement->bindParam(':status', $customerStatus);
            $statement->bindParam(':customer', $spServiceId['sp_customer']);
            $statement->execute();
        }
        header("Location: auditPlanning.php");
    } catch (Exception $ex) {
        echo $ex;
    }
}
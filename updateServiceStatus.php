<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_GET['id']) && !empty($_GET['id']) && isset($_GET['columnName']) && !empty($_GET['columnName']) && isset($_GET['columnValue']) && !empty($_GET['columnValue']) && isset($_GET['serviceid']) && !empty($_GET['serviceid'])) {
    try {
        //        echo $_GET['columnValue'];
        if ($_GET['columnName'] == "service_ca_status") {
            $caServiceStatusUpdate = "UPDATE service_type_ca SET service_ca_status = :status WHERE ca_id = :service and customer_id = :customer";
            $statement = $db->prepare($caServiceStatusUpdate);
            $statement->bindParam(':status', $_GET['columnValue']);
            $statement->bindParam(':service', $_GET['serviceid']);
            $statement->bindParam(':customer', $_GET['id']);
            $statement->execute();

            $selectService = "SELECT service FROM service_type_ca WHERE ca_id = :service";
            $statement = $db->prepare($selectService);
            $statement->bindParam(':service', $_GET['serviceid']);
            $statement->execute();
            $serviceid = $statement->fetch();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $_GET['columnValue']);
            $statement->bindParam(':service', $serviceid['service']);
            $statement->execute();

            if ($_GET['columnValue'] == "Audit planning") {
                $customerStatus = "in-process";

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_GET['id']);
                $statement->execute();
            }
            if ($_GET['columnValue'] == "Completed") {
                $customerStatus = "Valid";

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_GET['id']);
                $statement->execute();
            }
        }
        if ($_GET['columnName'] == "service_sai_status") {
            $saiServiceStatusUpdate = "UPDATE service_type_sai SET service_sai_status = :status WHERE sai_id = :service and customer_id = :customer";
            $statement = $db->prepare($saiServiceStatusUpdate);
            $statement->bindParam(':status', $_GET['columnValue']);
            $statement->bindParam(':service', $_GET['serviceid']);
            $statement->bindParam(':customer', $_GET['id']);
            $statement->execute();

            $selectService = "SELECT service FROM service_type_sai WHERE sai_id = :service ";
            $statement = $db->prepare($selectService);
            $statement->bindParam(':service', $_GET['serviceid']);
            $statement->execute();
            $serviceid = $statement->fetch();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $_GET['columnValue']);
            $statement->bindParam(':service', $serviceid['service']);
            $statement->execute();

            if ($_GET['columnValue'] == "Audit planning") {
                $customerStatus = "in-process";

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_GET['id']);
                $statement->execute();
            }
            if ($_GET['columnValue'] == "Completed") {
                $customerStatus = "Valid";

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_GET['id']);
                $statement->execute();
            }
        }
        if ($_GET['columnName'] == "service_saii_ststus") {
            $saiiServiceStatusUpdate = "UPDATE service_type_saii SET service_saii_ststus = :status WHERE saii_id = :service and customer_id = :customer";
            $statement = $db->prepare($saiiServiceStatusUpdate);
            $statement->bindParam(':status', $_GET['columnValue']);
            $statement->bindParam(':service', $_GET['serviceid']);
            $statement->bindParam(':customer', $_GET['id']);
            $statement->execute();

            $selectService = "SELECT service FROM service_type_saii WHERE saii_id = :service";
            $statement = $db->prepare($selectService);
            $statement->bindParam(':service', $_GET['serviceid']);
            $statement->execute();
            $serviceid = $statement->fetch();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $_GET['columnValue']);
            $statement->bindParam(':service', $serviceid['service']);
            $statement->execute();

            if ($_GET['columnValue'] == "Audit planning") {
                $customerStatus = "in-process";

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_GET['id']);
                $statement->execute();
            }
            if ($_GET['columnValue'] == "Completed") {
                $customerStatus = "Valid";

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_GET['id']);
                $statement->execute();
            }
        }
        if ($_GET['columnName'] == "service_rc_status") {
            $rcServiceStatusUpdate = "UPDATE service_type_rc SET service_rc_status = :status WHERE rc_id = :service and rc_customer = :customer";
            $statement = $db->prepare($rcServiceStatusUpdate);
            $statement->bindParam(':status', $_GET['columnValue']);
            $statement->bindParam(':service', $_GET['serviceid']);
            $statement->bindParam(':customer', $_GET['id']);
            $statement->execute();

            $selectService = "SELECT service FROM service_type_rc WHERE rc_id = :service";
            $statement = $db->prepare($selectService);
            $statement->bindParam(':service', $_GET['serviceid']);
            $statement->execute();
            $serviceid = $statement->fetch();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $_GET['columnValue']);
            $statement->bindParam(':service', $serviceid['service']);
            $statement->execute();

            if ($_GET['columnValue'] == "Audit planning") {
                $customerStatus = "in-process";

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_GET['id']);
                $statement->execute();
            }
            if ($_GET['columnValue'] == "Completed") {
                $customerStatus = "Valid";

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_GET['id']);
                $statement->execute();
            }
        }
        if ($_GET['columnName'] == "service_sp_status") {
            $spServiceStatusUpdate = "UPDATE service_type_sp SET service_sp_status = :status WHERE sp_id = :service and sp_customer = :customer";
            $statement = $db->prepare($spServiceStatusUpdate);
            $statement->bindParam(':status', $_GET['columnValue']);
            $statement->bindParam(':service', $_GET['serviceid']);
            $statement->bindParam(':customer', $_GET['id']);
            $statement->execute();

            $selectService = "SELECT service FROM service_type_sp WHERE sp_id = :service";
            $statement = $db->prepare($selectService);
            $statement->bindParam(':service', $_GET['serviceid']);
            $statement->execute();
            $serviceid = $statement->fetch();

            $updateService = "UPDATE service SET service_status = :status WHERE service_id = :service";
            $statement = $db->prepare($updateService);
            $statement->bindParam(':status', $_GET['columnValue']);
            $statement->bindParam(':service', $serviceid['service']);
            $statement->execute();

            if ($_GET['columnValue'] == "Audit planning") {
                $customerStatus = "in-process";

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_GET['id']);
                $statement->execute();
            }
            if ($_GET['columnValue'] == "Completed") {
                $customerStatus = "Valid";

                $updateCustomerStatus = "UPDATE `customer_info` SET `audit_status` = :status where id = :customer";
                $statement = $db->prepare($updateCustomerStatus);
                $statement->bindParam(':status', $customerStatus);
                $statement->bindParam(':customer', $_GET['id']);
                $statement->execute();
            }
        }
    } catch (Exception $ex) {
        echo $ex;
    }
}

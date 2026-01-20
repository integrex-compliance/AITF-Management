<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST['certificationId']) && !empty($_POST['certificationId'])) {
    try {
        if (isset($_POST['certifyRegistrationDate']) && !empty($_POST['certifyRegistrationDate'])) {
            $regDate = date("Y-m-d", strtotime($_POST['certifyRegistrationDate']));
        } else {
            $regDate = NULL;
        }
        if (isset($_POST['certifyValidTill']) && !empty($_POST['certifyValidTill'])) {
            $validDate = date("Y-m-d", strtotime($_POST['certifyValidTill']));
        } else {
            $validDate = NULL;
        }
        if (isset($_POST['certifyBeforeDate']) && !empty($_POST['certifyBeforeDate'])) {
            $beforeDate = date("Y-m-d", strtotime($_POST['certifyBeforeDate']));
        } else {
            $beforeDate = NULL;
        }

        $updateCertification = "UPDATE certificate_print SET customer_name = :customerName, customer_address = :customerAddress, certificate_no = :certificationNo, certificate_scope = :certifyScope , registration_date = :certifyRegistrationDate, valid_until = :certifyValidTill, before_date = :certifyBeforeDate WHERE certificate_id = :id";
        $statement = $db->prepare($updateCertification);
        $statement->bindParam(':customerName', $_POST['certifyCustomer']);
        $statement->bindParam(':customerAddress', $_POST['certificationAddress']);
        $statement->bindParam(':certificationNo', $_POST['certificationNo']);
        $statement->bindParam(':certifyScope', $_POST['certifyScope']);
        $statement->bindParam(':certifyRegistrationDate', $regDate);
        $statement->bindParam(':certifyValidTill', $validDate);
        $statement->bindParam(':certifyBeforeDate', $beforeDate);
        $statement->bindParam(':id', $_POST['certificationId']);
        $statement->execute();

        header("Location:customerAccount.php?customerid=" . $_POST['certifyCustomerId']);
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_POST['certifyCustomerId']) && !empty($_POST['certifyCustomerId']) && isset($_POST['certifyServiceId']) && !empty($_POST['certifyServiceId'])) {
        try {
            if (isset($_POST['certifyRegistrationDate']) && !empty($_POST['certifyRegistrationDate'])) {
                $regDate = date("Y-m-d", strtotime($_POST['certifyRegistrationDate']));
            } else {
                $regDate = NULL;
            }
            if (isset($_POST['certifyValidTill']) && !empty($_POST['certifyValidTill'])) {
                $validDate = date("Y-m-d", strtotime($_POST['certifyValidTill']));
            } else {
                $validDate = NULL;
            }
            if (isset($_POST['certifyBeforeDate']) && !empty($_POST['certifyBeforeDate'])) {
                $beforeDate = date("Y-m-d", strtotime($_POST['certifyBeforeDate']));
            } else {
                $beforeDate = NULL;
            }

            $service = "SELECT * FROM service WHERE service_id = :id";
            $statement = $db->prepare($service);
            $statement->bindParam(':id', $_POST['cp-service-id']);
            $statement->execute();
            $serviceInfo = $statement->fetch();

            $certPrint = "INSERT INTO certificate_print (customer_id, service_id, customer_name, customer_address, certificate_no, certificate_scope, registration_date, valid_until, before_date) "
                    . "VALUES (:customer,:service,:customerName,:customerAddress,:certificationNo, :certifyScope, :certifyRegistrationDate,:certifyValidTill,:certifyBeforeDate)";
            $statement = $db->prepare($certPrint);
            $statement->bindParam(':customer', $_POST['certifyCustomerId']);
            $statement->bindParam(':service', $_POST['certifyServiceId']);
            $statement->bindParam(':customerName', $_POST['certifyCustomer']);
            $statement->bindParam(':customerAddress', $_POST['certificationAddress']);
            $statement->bindParam(':certificationNo', $_POST['certificationNo']);
            $statement->bindParam(':certifyScope', $_POST['certifyScope']);
            $statement->bindParam(':certifyRegistrationDate', $regDate);
            $statement->bindParam(':certifyValidTill', $validDate);
            $statement->bindParam(':certifyBeforeDate', $beforeDate);
            $statement->execute();
//        echo "success";

            if ($serviceInfo['service_type'] == "SP") {
                $updateService = "UPDATE service SET service_done = 'DONE' WHERE service_id = :id";
                $statement = $db->prepare($updateService);
                $statement->bindParam(':id', $_POST['cp-service-id']);
                $statement->execute();
            }
            
            header("Location:customerAccount.php?customerid=" . $_POST['certifyCustomerId']);
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}
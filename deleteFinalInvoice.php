<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';

if (isset($_POST["finalId"]) && !empty($_POST["finalId"])) {
    try {
        $deleteFinal = "DELETE FROM invoice_details WHERE `invoice_id` = :id";
//        $deleteProformaInvoice = "UPDATE `invoice_details` SET `is_deleted`= 'Yes' WHERE `invoice_id` = :id";
        $statement = $db->prepare($deleteFinal);
        $statement->bindParam(":id", $_POST["finalId"]);
        $statement->execute();
    } catch (Exception $ex) {
        
    } 


    // this section is for mapping 
    header("location:invoicePage.php");
} elseif (isset($_GET['id']) && !empty($_GET['id']) && isset($_GET['columnName']) && !empty($_GET['columnName']) && isset($_GET['columnValue']) && !empty($_GET['columnValue'])) {
    try {
        if ($_GET['columnName'] == 'comment') {
            $column = 'comment';
            $dataVal = $_GET['columnValue'];
        } 
        if ($_GET['columnName'] == 'certification_stage_date') {
            $column = 'certification_stage_date';
            if (isset($_GET['columnValue']) && !empty($_GET['columnValue'])) {
                $dataVal = date("Y-m-d", strtotime($_GET['columnValue']));
            } else {
                $dataVal = NULL;
            }
        }
        if ($_GET['columnName'] == 'add_to') {
            $column = 'add_to';
            $dataVal = $_GET['columnValue'];
        }
        $updateCustomer = "UPDATE payment_system SET " . $column . " = :updateDeta WHERE payment_id = :customer ";
        $statement = $db->prepare($updateCustomer);
        $statement->bindParam(':updateDeta', $dataVal);
        $statement->bindParam(':customer', $_GET['id']);
        $statement->execute();
        echo 'success';
    } catch (Exception $ex) {
        echo $ex;
    }
} 

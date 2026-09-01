<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_GET['id']) && !empty($_GET['id']) && isset($_GET['columnName'])) {
    try {
        $column = null;
        $dataVal = null;
        
        error_log("updateCustomer.php - ID: " . $_GET['id'] . ", Column: " . $_GET['columnName'] . ", Value: " . (isset($_GET['columnValue']) ? $_GET['columnValue'] : 'NOT SET'));
        
        if ($_GET['columnName'] == 'comment') {
            $column = 'comment';
            $dataVal = isset($_GET['columnValue']) ? TRIM($_GET['columnValue']) : '';
            error_log("Comment update - Column: $column, Value: $dataVal, Customer ID: " . $_GET['id']);
        } else if ($_GET['columnName'] == 'certification_stage_date') {
            $column = 'certification_stage_date';
            if (isset($_GET['columnValue']) && !empty($_GET['columnValue'])) {
                $dataVal = date("Y-m-d", strtotime($_GET['columnValue']));
            } else {
                $dataVal = NULL;
            }
        } else if ($_GET['columnName'] == 'add_to') {
            $column = 'add_to';
            $dataVal = $_GET['columnValue'];
        } else if ($_GET['columnName'] == 'standard_name') {
            $column = 'standard_name';
            $dataVal = TRIM($_GET['columnValue']);
        } else if ($_GET['columnName'] == 'issue_date') {
            $column = 'issue_date';
            if (isset($_GET['columnValue']) && !empty($_GET['columnValue'])) {
                $dataVal = date("Y-m-d", strtotime($_GET['columnValue']));
            } else {
                $dataVal = NULL;
            }
        } else if ($_GET['columnName'] == 'expire_date') {
            $column = 'expire_date';
            if (isset($_GET['columnValue']) && !empty($_GET['columnValue'])) {
                $dataVal = date("Y-m-d", strtotime($_GET['columnValue']));
            } else {
                $dataVal = NULL;
            }
        }
        
        if ($column !== null) {
            $updateCustomer = "UPDATE customer_info SET " . $column . " = :updateDeta WHERE id = :customer ";
            error_log("SQL Query: $updateCustomer");
            error_log("Data Value: $dataVal");
            error_log("Customer ID: " . $_GET['id']);
            
            $statement = $db->prepare($updateCustomer);
            $statement->bindParam(':updateDeta', $dataVal);
            $statement->bindParam(':customer', $_GET['id']);
            $executeResult = $statement->execute();
            
            $rowCount = $statement->rowCount();
            error_log("Execute Result: " . ($executeResult ? 'TRUE' : 'FALSE'));
            error_log("Rows Affected: $rowCount");
            
            if ($executeResult) {
                echo 'success';
                error_log("SUCCESS: Comment updated successfully");
            } else {
                echo 'error: execute failed';
                error_log("ERROR: Execute returned false");
            }
        } else {
            echo 'error: invalid column name';
            error_log("ERROR: Invalid column name - " . $_GET['columnName']);
        }
    } catch (Exception $ex) {
        echo $ex;
    }
} else if(isset ($_POST['customerId']) && !empty ($_POST['customerId'])){
    try {
        $deleteCustomer = "UPDATE `customer_info` SET is_deleted = 'YES', deleted_date = NOW() WHERE id = :id";
        $statement = $db->prepare($deleteCustomer);
        $statement->bindParam(':id', $_POST['customerId']);
        $statement->execute();
        header("Location: clientRecycle.php");
    } catch (Exception $ex) {
        echo $ex;
    }
} else if(isset ($_GET["handler"]) && !empty ($_GET["handler"]) && isset ($_GET["customer"]) && !empty ($_GET["customer"])){
    try {
        $updateCustomer = "UPDATE customer_info SET handler = :handler WHERE id = :customer";
        $statement = $db->prepare($updateCustomer);
        $statement->bindParam(":handler",$_GET["handler"]);
        $statement->bindParam(":customer",$_GET["customer"]);
        $statement->execute();
        echo "success";
    } catch (Exception $ex) {
        echo $ex;
    }
}

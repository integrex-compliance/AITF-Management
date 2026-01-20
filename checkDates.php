<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST["serviceId"]) && !empty($_POST["serviceId"]) && isset($_POST["date"]) && !empty($_POST["date"])) {
    try {
        $applicationDate = "SELECT application_date FROM audit_application where service_id = :service";
        $statement = $db->prepare($applicationDate);
        $statement->bindParam(":service", $_POST["serviceId"]);
        $statement->execute();
        $auditAppDate = $statement->fetch();

        if ($auditAppDate > date("Y-m-d", strtotime($_POST["date"])))
            echo "1";
        else
            echo "0";
    } catch (Exception $ex) {
        
    }
}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if(isset($_POST["auditor_id"]) && !empty($_POST["auditor_id"])){
    try {
        $sql = "UPDATE `auditors` SET `auditor_name`= :auditroName,`auditor_number`= :auditroNumber,`auditor_email`= :auditroEmail, `abbrevation`= :auditorabbr WHERE `auditors_id` = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id",$_POST["auditor_id"]);
        $statement->bindParam(":auditroName",$_POST["auditorName"]);
        $statement->bindParam(":auditroNumber",$_POST["auditorNumber"]);
        $statement->bindParam(":auditroEmail",$_POST["auditorEmail"]);
        $statement->bindParam(":auditorabbr",$_POST["auditorabbr"]);
        $statement->execute();
        header("Location: masterCreator.php");
    } catch (Exception $ex) {
        
    }
} else {
    try {
        $sql = "INSERT INTO `auditors`(`auditor_name`, `auditor_number`, `auditor_email`, `abbrevation`) VALUES (:auditroName, :auditroNumber, :auditroEmail, :auditorabbr)";
        $statement = $db->prepare($sql);
        $statement->bindParam(":auditroName", $_POST["auditorName"]);
        $statement->bindParam(":auditroNumber", $_POST["auditorNumber"]);
        $statement->bindParam(":auditroEmail", $_POST["auditorEmail"]);
        $statement->bindParam(":auditorabbr",$_POST["auditorabbr"]);
        $statement->execute();
        header("Location: masterCreator.php");
    } catch (Exception $ex) {
        
    }
}

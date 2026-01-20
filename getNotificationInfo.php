<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if(isset($_GET["customerid"]) && !empty($_GET["customerid"]) && isset($_GET["auditorid"]) && !empty($_GET["auditorid"])){
    $count = 0;
    $maxService = "SELECT id, auditor_id FROM `notification_auditor` WHERE service_id = (SELECT MAX(service_id) FROM notification_auditor) AND client_id = :client AND auditor_catagory LIKE 'lead%'";
    $statement = $db->prepare($maxService);
    $statement->bindParam(":client",$_GET["customerid"]);
    $statement->execute();
    $maxsvc = $statement->fetch();
    
    if($maxsvc['auditor_id'] == $_GET["auditorid"]){
        $count = $count + 1;
    }
    
    $secondMaxService = "SELECT id, auditor_id FROM `notification_auditor` WHERE service_id < (SELECT MAX(service_id) FROM `notification_auditor`) AND client_id = :client AND auditor_catagory LIKE 'lead%' ORDER BY id DESC LIMIT 1";
    $statement = $db->prepare($secondMaxService);
    $statement->bindParam(":client",$_GET["customerid"]);
    $statement->execute();
    $max2svc = $statement->fetch();
    
    if($max2svc['auditor_id'] == $_GET["auditorid"]){
        $count = $count + 1;
    }
    echo json_encode($count);
}

?>
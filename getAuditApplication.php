<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if(isset($_GET['auditAppId']) && !empty($_GET['auditAppId'])){
    try {       
        $auditApp = "SELECT * FROM audit_application WHERE audit_id = :auditid";
        $statement = $db->prepare($auditApp);
        $statement->bindParam(':auditid',$_GET['auditAppId']);
        $statement->execute();
        $auditDetails = $statement->fetch();
        
        $sql1 = "SELECT * FROM `audit_iaf_nace` WHERE `audit_id` = :id";
        $statement = $db->prepare($sql1);
        $statement->bindParam(":id",$_GET['auditAppId']);
        $statement->execute();
        $auditiaf = $statement->fetch();
        
        echo json_encode(array($auditDetails, $auditiaf));
    } catch (Exception $ex) {
        
    }
}
else if(isset ($_GET['firm_Id']) && !empty ($_GET['firm_Id'])){
    try {
        $firm = "SELECT * FROM consulting_firm WHERE firm_id = :firmId";
        $statement = $db->prepare($firm);
        $statement->bindParam(':firmId',$_GET['firm_Id']);
        $statement->execute();
        $firmDetails = $statement->fetch();
        
        $service = "SELECT * FROM firm_services WHERE firm_id = :firmId";
        $statement = $db->prepare($service);
        $statement->bindParam(':firmId',$_GET['firm_Id']);
        $statement->execute();
        $firmService = $statement->fetchAll();
        
        $abbrevation = "SELECT * FROM firm_abbrevation WHERE firm_id = :firmId";
        $statement = $db->prepare($abbrevation);
        $statement->bindParam(':firmId',$_GET['firm_Id']);
        $statement->execute();
        $firmAbbrevation = $statement->fetchAll();
        
        echo json_encode(array($firmDetails, $firmService, $firmAbbrevation));
        
    } catch (Exception $ex) {
        echo $ex;
    }
} 
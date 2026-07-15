<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
require_once('tcpdf_include.php');

if(isset($_POST['firmId']) && !empty($_POST['firmId'])){
    try {
        $firmService = "DELETE FROM firm_services WHERE firm_id = :id";
        $statement = $db->prepare($firmService);
        $statement->bindParam(':id',$_POST['firmId']);
        $statement->execute();
        
        $firmAbbrevations = "DELETE FROM firm_abbrevation WHERE firm_id = :id";
        $statement = $db->prepare($firmAbbrevations);
        $statement->bindParam(':id',$_POST['firmId']);
        $statement->execute();
        
        $firmDlt = "DELETE FROM consulting_firm WHERE firm_id = :id";
        $statement = $db->prepare($firmDlt);
        $statement->bindParam(':id',$_POST['firmId']);
        $statement->execute();
        
        header("Location: masterCreator.php");
    } catch (Exception $ex) {
        echo $ex;
    }
}

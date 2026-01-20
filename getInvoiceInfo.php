<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if(isset($_GET['invoice']) && !empty($_GET['invoice'])){
    try {
        $invoiceDetail = "SELECT * FROM invoice_details WHERE invoice_id = :id";
        $statement = $db->prepare($invoiceDetail);
        $statement->bindParam(':id',$_GET['invoice']);
        $statement->execute();
        $invoiceInfo = $statement->fetch();
        
        $invoiceService = "SELECT * FROM invoice_services WHERE invoice_ids = :id";
        $statement = $db->prepare($invoiceService);
        $statement->bindParam(':id',$_GET['invoice']);
        $statement->execute();
        $serviceDetails = $statement->fetchAll();
        
        $firmDetail = "SELECT * FROM customer_info WHERE id = :id";
        $statement = $db->prepare($firmDetail);
        $statement->bindParam(':id', $invoiceInfo['customer_id']);
        $statement->execute();
        $firmInfo = $statement->fetch();
        
        echo json_encode(array($invoiceInfo, $serviceDetails, $firmInfo));
    } catch (Exception $ex) {
        echo $ex;
    }
} else if(isset ($_GET['quotation']) && !empty ($_GET['quotation'])){
    try {
        $quotation = "SELECT * FROM quotation_detail WHERE quotation_id = :id";
        $statement = $db->prepare($quotation);
        $statement->bindParam(':id',$_GET['quotation']);
        $statement->execute();
        $quotationInfo = $statement->fetch();
        
        $quotCondition = "SELECT * FROM quotation_condition WHERE quotation_id = :id";
        $statement = $db->prepare($quotCondition);
        $statement->bindParam(':id', $_GET['quotation']);
        $statement->execute();
        $quot_condition = $statement->fetchAll();
        
        $firmDetail = "SELECT * FROM consulting_firm WHERE firm_id = :firmId";
        $statement = $db->prepare($firmDetail);
        $statement->bindParam(':firmId', $quotationInfo['firm_id']);
        $statement->execute();
        $firmInfo = $statement->fetch();
        
        echo json_encode(array($quotationInfo, $firmInfo, $quot_condition));
        
    } catch (Exception $ex) {
        echo $ex;
    }
}

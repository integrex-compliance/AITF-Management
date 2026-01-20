<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_GET['consultingFirm']) && !empty($_GET['consultingFirm'])) {
    try {
        $row_set = array();
        $firmList = "SELECT DISTINCT firm_name, firm_id FROM consulting_firm WHERE firm_name like '" . $_GET['consultingFirm'] . "%'";
        $statement = $db->prepare($firmList);
        $statement->execute();
        if ($statement->rowCount() > 0)
            $firms = $statement->fetchAll();
        foreach ($firms as $data) {
            $new['value'] = htmlentities(stripslashes($data['firm_name']));
            $new['label'] = $data['firm_name'];
            $new['id'] = $data['firm_id'];

            array_push($row_set, $new); //build an array
        }
        echo json_encode($row_set);        
    } catch (Exception $ex) {
        echo $ex;
    }
} else if(isset ($_GET["customersearch"]) && !empty ($_GET["customersearch"])){
    try {
//        $row_set = array();
        $customerlist = "SELECT DISTINCT id, customer_name FROM customer_info where customer_name like '%" . $_GET["customersearch"] . "%' and is_deleted = 'NO'";
        $statement = $db->prepare($customerlist);
        $statement->execute();
        if ($statement->rowCount() > 0)
            $customers = $statement->fetchAll();
//        foreach ($customers as $data) {
//            $new['value'] = htmlentities(stripslashes($data['customer_name']));
//            $new['label'] = $data['customer_name'];
//            $new['id'] = $data['id'];
//
//            array_push($row_set, $new);
//        }
        echo json_encode($customers);
    } catch (Exception $ex) {
        
    }
}

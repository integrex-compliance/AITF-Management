<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
if (isset($_POST['Send']) || isset($_POST['ajax'])) {
    try {

        $cename = $_POST['name'];
        $ceno = $_POST['cno'];

        if (strpos($cename, '.') !== FALSE) {
            $cename = str_replace('.', '', $cename);
        }


        
        $customer = "SELECT * FROM `customer_info` where certification_no = :id and customer_name LIKE '%".$_POST['name']."%'";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_POST['cno']);
        $statement->execute();
        $customerDetails = FALSE;
        while ($custdetails = $statement->fetch()) {
            $custdetails['customer_name'] = str_replace('.', '', $custdetails['customer_name']);
            if (strcasecmp($custdetails['customer_name'], $cename) == 0 && $custdetails['certification_no'] == $ceno) {
                $customerDetails = $custdetails;
                break;
            }
        }
        
       
        if ($customerDetails == FALSE) { 
            print_r($customerDetails); echo "in if";die;
            echo "<script type='text/javascript'>alert('Please verify certificate numeber and Organiztion name as no such client certified by AITF')</script>";
        } else {
           
            echo "<script type='text/javascript'>alert('Client Certificate number=" . $customerDetails['certification_no'] . "\\nClient Name=" . $customerDetails['customer_name'] . "\\n Address=" . $customerDetails['address'] . "\\nCertificate Scope =" . $customerDetails['scope'] . "\\nCertificate Status =" . $customerDetails['audit_status'] . "')</script>";
        }
    } catch (Exception $ex) {
        echo $ex;
    }
}
?>
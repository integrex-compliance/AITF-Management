<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

// Concatenate address fields into a single string
$addressParts = array();
if (isset($_POST['streetAddress']) && !empty($_POST['streetAddress'])) {
    $addressParts[] = trim($_POST['streetAddress']);
}
if (isset($_POST['city']) && !empty($_POST['city'])) {
    $addressParts[] = trim($_POST['city']);
}
if (isset($_POST['state']) && !empty($_POST['state'])) {
    $addressParts[] = trim($_POST['state']);
}
if (isset($_POST['postCode']) && !empty($_POST['postCode'])) {
    $addressParts[] = trim($_POST['postCode']);
}
if (isset($_POST['country']) && !empty($_POST['country'])) {
    $addressParts[] = trim($_POST['country']);
}
$fullAddress = implode(', ', $addressParts);

if (isset($_POST['editCustomerId']) && !empty($_POST['editCustomerId'])) {
    try {
        $string = isset($_POST['addnotes']) ? trim($_POST['addnotes']) : '';
        $updateCustomer = "UPDATE customer_info SET customer_name = :name, gstin_no = :gstinno, address = :address, contact_person = :person, contact = :contact, email_id = :email, scope = :scope, comment = :comment WHERE id = :id";
        $statement = $db->prepare($updateCustomer);
        $statement->bindParam(':name', $_POST['customerName']);
        $statement->bindParam(':gstinno', $_POST['gstin_no']);
        $statement->bindParam(':address', $fullAddress);
        $statement->bindParam(':person', $_POST['customerPersonHidden']);
        $statement->bindParam(':contact', $_POST['customerContactHidden']);
        $statement->bindParam(':email', $_POST['customerContactEmlHidden']);
        $statement->bindParam(':scope', $_POST['scope']);
        $statement->bindParam(':comment', $string);
        $statement->bindParam(':id', $_POST['editCustomerId']);
        $statement->execute();
        header("Location: customerAccount.php?customerid=" . $_POST['editCustomerId']);
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_POST['customerName']) && !empty($_POST['customerName']) ) {
        try {
//            echo "comment value -> ".$_POST['comments']."comment value -> "; die;
            $string = isset($_POST['addnotes']) ? trim($_POST['addnotes']) : '';
            $customerAdd = "INSERT INTO customer_info (customer_name, gstin_no, address, contact_person, contact, email_id, scope, comment) VALUES (:name,:gstinno,:address,:person,:contact,:email,:scope,:comment);";
            $statement = $db->prepare($customerAdd);
            $statement->bindParam(':name', $_POST['customerName']);
            $statement->bindParam(':gstinno', $_POST['gstin_no']);
            $statement->bindParam(':address', $fullAddress);
            $statement->bindParam(':person', $_POST['customerPersonHidden']);
            $statement->bindParam(':contact', $_POST['customerContactHidden']);
            $statement->bindParam(':email', $_POST['customerContactEmlHidden']);
            $statement->bindParam(':scope', $_POST['scope']);
            $statement->bindParam(':comment', $string);
            $statement->execute();

            $customerId = $db->lastInsertId();

            header("Location: customerAccount.php?customerid=" . $customerId);
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}

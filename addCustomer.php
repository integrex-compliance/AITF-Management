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

        // --- SYNC TO LOCAL API START (UPDATE) ---
        $webhookUrl = "http://localhost:3000/api/aitfdata"; 

        // Fetch Certificate Data
        $certStmtUpdate = $db->prepare("SELECT 
            c.audit_status as certification_status, 
            c.certification_stage_date as certification_decision_date, 
            cp.certificate_no, cp.registration_date as issue_date, cp.valid_until as expire_date, 
            (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id ORDER BY asi.audit_schedule_id DESC LIMIT 1) as standard_name 
        FROM customer_info c LEFT JOIN certificate_print cp ON c.id = cp.customer_id WHERE c.id = :cid ORDER BY cp.certificate_id DESC LIMIT 1");
        $certStmtUpdate->execute([':cid' => $_POST['editCustomerId']]);
        $certInfoUpdate = $certStmtUpdate->fetch(PDO::FETCH_ASSOC);

        $dataToSend = [
            "action" => "update_customer",
            "customer_id" => $_POST['editCustomerId'],
            "name" => $_POST['customerName'],
            "address" => $fullAddress,
            "gstin" => $_POST['gstin_no'],
            "email" => $_POST['customerContactEmlHidden'],
            "contact" => $_POST['customerContactHidden'],
            "scope" => $_POST['scope'],
            "comment" => $string,
            // Certificate Details
            "certificate_no" => $certInfoUpdate['certificate_no'] ?? "",
            "certification_status" => $certInfoUpdate['certification_status'] ?? "",
            "certification_decision_date" => $certInfoUpdate['certification_decision_date'] ?? "",
            "standard_name" => $certInfoUpdate['standard_name'] ?? "",
            "issue_date" => $certInfoUpdate['issue_date'] ?? "",
            "expire_date" => $certInfoUpdate['expire_date'] ?? ""
        ];

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataToSend));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Do not hang the page
        // Force IPv4 if localhost resolves to IPv6 and fails
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        
        $result = curl_exec($ch);
        if ($result === false) {
            error_log("API Sync Error UPDATE: " . curl_error($ch));
        } else {
            error_log("API Sync Success UPDATE: " . $result);
        }
        curl_close($ch);
        // --- SYNC TO LOCAL API END ---

        header("Location: customerAccount.php?customerid=" . $_POST['editCustomerId']);
    }
    catch (Exception $ex) {
        echo $ex;
    }
}
else {
    if (isset($_POST['customerName']) && !empty($_POST['customerName'])) {
        try {            //            echo "comment value -> ".$_POST['comments']."comment value -> "; die;
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

            // --- SYNC TO LOCAL API START (INSERT) ---
            $webhookUrl = "http://localhost:3000/api/aitfdata"; 

            // Fetch Certificate Data for new customer (Usually blank except for defaults)
            $certStmtInsert = $db->prepare("SELECT 
                c.audit_status as certification_status, 
                c.certification_stage_date as certification_decision_date, 
                cp.certificate_no, cp.registration_date as issue_date, cp.valid_until as expire_date, 
                (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id ORDER BY asi.audit_schedule_id DESC LIMIT 1) as standard_name 
            FROM customer_info c LEFT JOIN certificate_print cp ON c.id = cp.customer_id WHERE c.id = :cid ORDER BY cp.certificate_id DESC LIMIT 1");
            $certStmtInsert->execute([':cid' => $customerId]);
            $certInfoInsert = $certStmtInsert->fetch(PDO::FETCH_ASSOC);

            $dataToSend = [
                "action" => "new_customer",
                "customer_id" => $customerId,
                "name" => $_POST['customerName'],
                "address" => $fullAddress,
                "gstin" => $_POST['gstin_no'],
                "email" => $_POST['customerContactEmlHidden'],
                "contact" => $_POST['customerContactHidden'],
                "scope" => $_POST['scope'],
                "comment" => $string,
                // Certificate Details
                "certificate_no" => $certInfoInsert['certificate_no'] ?? "",
                "certification_status" => $certInfoInsert['certification_status'] ?? "",
                "certification_decision_date" => $certInfoInsert['certification_decision_date'] ?? "",
                "standard_name" => $certInfoInsert['standard_name'] ?? "",
                "issue_date" => $certInfoInsert['issue_date'] ?? "",
                "expire_date" => $certInfoInsert['expire_date'] ?? ""
            ];

            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataToSend));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Do not hang the page
            // Force IPv4 if localhost resolves to IPv6 and fails
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

            $result = curl_exec($ch);
            if ($result === false) {
                error_log("API Sync Error INSERT: " . curl_error($ch));
            } else {
                error_log("API Sync Success INSERT: " . $result);
            }
            curl_close($ch);
            // --- SYNC TO LOCAL API END --- 

            header("Location: customerAccount.php?customerid=" . $customerId);
        }
        catch (Exception $ex) {
            echo $ex;
        }
    }
}

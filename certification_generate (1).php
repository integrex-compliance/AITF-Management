<?php

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST["certificateid"]) && !empty($_POST["certificateid"])) {
    try {
        // Check if all required POST variables are set
        if (!isset($_POST["newRegistrationDate"]) || !isset($_POST["allotmentNo"]) || !isset($_POST["csId"]) || !isset($_POST["newCount"])) {
            header("Location: customerAccount.php?customerid=" . (isset($_POST['csId']) ? $_POST['csId'] : ''));
            exit;
        }
        
        $date = date("d-m-Y", strtotime($_POST["newRegistrationDate"]));
        $orderdate = explode('-', $date);
        $day = $orderdate[0];
        $month = $orderdate[1];
        $year = $orderdate[2];
        
        $sql = "UPDATE `certification_aggrement` SET `aggrement_date`= :date ,`aggrement_no`= :no  WHERE `id` = :id ";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_POST["certificateid"]);
        $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
        $statement->bindParam(":no", $_POST['allotmentNo']);
        $statement->execute();

        // Update unique_id in customer_info table
        $updateUniqueId = "UPDATE customer_info SET unique_id = :allotment_agreement WHERE id = :id";
        $statement = $db->prepare($updateUniqueId);
        $statement->bindParam(':allotment_agreement', $_POST['allotmentNo']);
        $statement->bindParam(':id', $_POST['csId']);
        $statement->execute();

        $updateCount = "UPDATE allotment_count SET year = :year, month = :month, certification_date = :date, client_count = :count WHERE client_id = :customer";
        $statement = $db->prepare($updateCount);
        $statement->bindParam(":year", $year);
        $statement->bindParam(":month", $month);
        $statement->bindParam(":count", $_POST["newCount"]);
        $statement->bindParam(":customer", $_POST['csId']);
        $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
        $statement->execute();

        header("Location: customerAccount.php?customerid=" . $_POST['csId']);
    } catch (Exception $ex) {
        
    }
} else {
    try {
        // Check if all required POST variables are set
        if (!isset($_POST["service"]) || !isset($_POST["csId"]) || !isset($_POST["newRegistrationDate"]) || !isset($_POST["allotmentNo"])) {
            header("Location: customerAccount.php?customerid=" . (isset($_POST['csId']) ? $_POST['csId'] : ''));
            exit;
        }
        
        // Check if this service is an RC or CR service
        $serviceTypeCheck = "SELECT service_type FROM service WHERE service_id = :sid";
        $stmtCheck = $db->prepare($serviceTypeCheck);
        $stmtCheck->bindParam(':sid', $_POST["service"]);
        $stmtCheck->execute();
        $serviceTypeResult = $stmtCheck->fetch();
        $isRCService = ($serviceTypeResult && $serviceTypeResult['service_type'] == 'RC');
        $isCRService = ($serviceTypeResult && $serviceTypeResult['service_type'] == 'CR');
        
        // Check if customer has previous CA/SA services (for CR)
        $hasPreviousServices = false;
        if ($isCRService) {
            $previousServiceSql = "SELECT COUNT(*) as service_count FROM service WHERE customer_id = :cid AND service_type IN ('CA', 'SA-I', 'SA-II') AND service_id < :current_sid";
            $prevStmt = $db->prepare($previousServiceSql);
            $prevStmt->bindParam(':cid', $_POST["csId"]);
            $prevStmt->bindParam(':current_sid', $_POST["service"]);
            $prevStmt->execute();
            $prevResult = $prevStmt->fetch();
            $hasPreviousServices = ($prevResult && $prevResult['service_count'] > 0);
        }
        
        // For RC services OR CR services after previous CA/SA, reuse the original allotment number (without suffix)
        $allotmentToStore = $_POST["allotmentNo"];
        
        // If allotment contains "Rc-" suffix, strip it to get base number
        if (strpos($allotmentToStore, ' Rc-') !== false) {
            $allotmentToStore = preg_replace('/ Rc-\d+$/', '', $allotmentToStore);
        }
        
        if ($isRCService || ($isCRService && $hasPreviousServices)) {
            // Get the original base allotment number (without any Rc-X suffix)
            $originalAllotment = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid AND aggrement_no NOT LIKE '% Rc-%' ORDER BY id ASC LIMIT 1";
            $stmtOriginal = $db->prepare($originalAllotment);
            $stmtOriginal->bindParam(':cid', $_POST["csId"]);
            $stmtOriginal->execute();
            $originalResult = $stmtOriginal->fetch();
            
            if ($originalResult && !empty($originalResult['aggrement_no'])) {
                // Use the original base number (without Rc suffix) for storage
                $allotmentToStore = $originalResult['aggrement_no'];
            }
        }
        
        echo $_POST["service"]." -> ".$_POST["csId"]." -> ".$_POST["newRegistrationDate"]." -> ".$allotmentToStore;
        $sqlcertagrment = "INSERT INTO `certification_aggrement`(`service`, `customer_id`, `aggrement_date`, `aggrement_no`) VALUES (:svc, :customer, :date, :altno)";
        $statement = $db->prepare($sqlcertagrment);
        $statement->bindParam(":svc", $_POST["service"]);
        $statement->bindParam(":customer", $_POST["csId"]);
        $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
        $statement->bindParam(":altno", $allotmentToStore);
        $statement->execute();

        $client = "SELECT count(id) as id FROM allotment_count where client_id = :id";
        $statement = $db->prepare($client);
        $statement->bindParam(":id", $_POST["csId"]);
        $statement->execute();
        $row = $statement->fetch();
        
        // Only update customer_info.unique_id for first-time services (not RC or CR after CA/SA)
        if (!$row["id"] && !$isRCService && !($isCRService && $hasPreviousServices)) {
            $updateNextService = "UPDATE customer_info SET unique_id = :allotment_agreement WHERE id = :id";
            $statement = $db->prepare($updateNextService);
            $statement->bindParam(':allotment_agreement', $allotmentToStore);
            $statement->bindParam(':id', $_POST['csId']);
            $statement->execute();

            $date = date("d-m-Y", strtotime($_POST["newRegistrationDate"]));
            $orderdate = explode('-', $date);
            $day = $orderdate[0];
            $month = $orderdate[1];
            $year = $orderdate[2];

            $insertCount = "INSERT INTO allotment_count (year, month, client_count, client_id, certification_date) VALUES (:year, :month, :count, :customer, :date)";
            $statement = $db->prepare($insertCount);
            $statement->bindParam(":year", $year);
            $statement->bindParam(":month", $month);
            $statement->bindParam(":count", $_POST["newCount"]);
            $statement->bindParam(":customer", $_POST['csId']);
            $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
            $statement->execute();
        }
        echo "3";
        header("Location: customerAccount.php?customerid=" . $_POST['csId']);
    } catch (Exception $ex) {
        
    }
}
//if (isset($_POST['csId']) && !empty($_POST['csId']) && isset($_POST["generate"]) && !empty($_POST["generate"]) && $_POST["generate"] == "create")  {
//
//    $regDate = $_POST["newRegistrationDate"];
//    $alotno = $_POST['allotmentNo'];
//
//    $updateNextService = "UPDATE customer_info SET unique_id = :allotment_agreement WHERE id = :id";
//    $statement = $db->prepare($updateNextService);
//    $statement->bindParam(':allotment_agreement', $_POST['allotmentNo']);
//    $statement->bindParam(':id', $_POST['csId']);
//    $statement->execute();
//
//    $count = "SELECT count(id) as id FROM allotment_count where client_id = :id";
//    $statement = $db->prepare($count);
//    $statement->bindParam(":id", $_POST['csId']);
//    $statement->execute();
//    $counter = $statement->fetch();
//    if (!$counter["id"] > 0) {
//        $date = date("d-m-Y", strtotime($_POST["newRegistrationDate"]));
//        $orderdate = explode('-', $date);
//        $day = $orderdate[0];
//        $month = $orderdate[1];
//        $year = $orderdate[2];
//
//        $insertCount = "INSERT INTO allotment_count (year, month, client_count, client_id, certification_date) VALUES (:year, :month, :count, :customer, :date)";
//        $statement = $db->prepare($insertCount);
//        $statement->bindParam(":year", $year);
//        $statement->bindParam(":month", $month);
//        $statement->bindParam(":count", $_POST["newCount"]);
//        $statement->bindParam(":customer", $_POST['csId']);
//        $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
//        $statement->execute();
//    } else {
//        $date = date("d-m-Y", strtotime($_POST["newRegistrationDate"]));
//        $orderdate = explode('-', $date);
//        $day = $orderdate[0];
//        $month = $orderdate[1];
//        $year = $orderdate[2];
//
//        $updateCount = "UPDATE allotment_count SET year = :year, month = :month, certification_date = :date, client_count = :count WHERE client_id = :customer";
//        $statement = $db->prepare($updateCount);
//        $statement->bindParam(":year", $year);
//        $statement->bindParam(":month", $month);
//        $statement->bindParam(":count", $_POST["newCount"]);
//        $statement->bindParam(":customer", $_POST['csId']);
//        $statement->bindParam(":date", date("Y-m-d", strtotime($_POST["newRegistrationDate"])));
//        $statement->execute();
//    }
//} 
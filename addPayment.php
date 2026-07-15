<?php

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (isset($_GET["paymentID"]) && !empty($_GET["paymentID"])) {
    try {
        $dltRow = "DELETE FROM payment_system WHERE payment_id = :rowid";
        $statement = $db->prepare($dltRow);
        $statement->bindParam(':rowid', $_GET["paymentID"]);
        $statement->execute();
        echo 'success';
    } catch (Exception $ex) {
        echo $ex;
    }
}

if (isset($_GET["invid"]) && !empty($_GET["invid"])) {
    try {
        $invrcv = "UPDATE `invoice_details` SET `invoice_status`= 'Rcv' WHERE `invoice_id` = :invid";
        $statement = $db->prepare($invrcv);
        $statement->bindParam(':invid', $_GET["invid"]);
        $statement->execute();
        echo 'success';
    } catch (Exception $ex) {
        echo $ex;
    }
}

if(isset($_GET["inv_id"]) && !empty($_GET["inv_id"])){
    try {
        $invrcv = "UPDATE `invoice_details` SET `invoice_status`= 'NotRcv' WHERE `invoice_id` = :invid";
        $statement = $db->prepare($invrcv);
        $statement->bindParam(':invid', $_GET["inv_id"]);
        $statement->execute();
        echo 'success';
    } catch (Exception $ex) {
        echo $ex;
    }
}

if (isset($_GET['payid']) && !empty($_GET['payid'])) {
    try {
        $customer = $_GET['customer'];
        $payid = $_GET['payid'];
        $servicetypes = explode('~', $_GET['serviceType']);
        $decidedamount = explode('~', $_GET['decidedAmount']);
        $paymentreceived = explode('~', $_GET['paymentReceived']);
        $depositdate = explode('~', $_GET['depositDate']);
        $chequedetails = explode('~', $_GET['chequeDetails']);
        $rowi_ds = explode('~', $_GET['rowids']);
        $dlt_rows = explode('~', $_GET['dltRows']);
//        echo $depositdate;
        for ($i = 0; $i < count($servicetypes); ++$i) {
            if ($servicetypes[$i] != "") {
                if (count($depositdate) < ($i + 1) || $depositdate[$i] == "")
                    $date = NULL;
                else
                    $date = date("Y-m-d", strtotime($depositdate[$i]));

                $balance = $decidedamount[$i] - $paymentreceived[$i];
                if (array_key_exists($i, $rowi_ds) && $rowi_ds[$i] != "" && $rowi_ds != null) {
                    if (in_array($rowi_ds[$i], $dlt_rows)) {
                        $dltRow = "DELETE FROM payment_system WHERE payment_sy_id = :payid and payment_id = :rowid";
                        $statement = $db->prepare($dltRow);
                        $statement->bindParam(':payid', $payid);
                        $statement->bindParam(':rowid', $rowi_ds[$i]);
                        $statement->execute();
//                        echo 'in delete ';
                    } else {
                        $updateFayInfo = "UPDATE payment_system SET service_type = :services, amount_decided = :decideAmount, payment_received = :rcvAmount, payment_date = :payDate, cheque_details = :checkdetails, balance = :balance WHERE payment_id = :rowid and payment_sy_id = :payid";
                        $statement = $db->prepare($updateFayInfo);
                        $statement->bindParam(':services', $servicetypes[$i]);
                        $statement->bindParam(':decideAmount', $decidedamount[$i]);
                        $statement->bindParam(':rcvAmount', $paymentreceived[$i]);
                        $statement->bindParam(':payDate', $date);
                        $statement->bindParam(':checkdetails', $chequedetails[$i]);
                        $statement->bindParam(':balance', $balance);
                        $statement->bindParam(':rowid', $rowi_ds[$i]);
                        $statement->bindParam(':payid', $payid);
                        $statement->execute();
//                        echo 'in update ';
                    }
                } else {
                    $paymentDetails = "INSERT INTO payment_system (payment_sy_id, customer_id, service_type, amount_decided, payment_received, payment_date, cheque_details, balance) VALUES (:payid, :customer, :services, :decideAmount, :rcvAmount, :payDate, :checkdetails, :balance)";
                    $statement = $db->prepare($paymentDetails);
                    $statement->bindParam(':payid', $payid);
                    $statement->bindParam(':customer', $customer);
                    $statement->bindParam(':services', $servicetypes[$i]);
                    $statement->bindParam(':decideAmount', $decidedamount[$i]);
                    $statement->bindParam(':rcvAmount', $paymentreceived[$i]);
                    $statement->bindParam(':payDate', $date);
                    $statement->bindParam(':checkdetails', $chequedetails[$i]);
                    $statement->bindParam(':balance', $balance);
                    $statement->execute();
//                    echo 'in insert';
                }
            }
        }
        echo 'success';
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_GET['customer']) && !empty($_GET['customer'])) {
        try {
            $customer = $_GET['customer'];
            $servicetypes = explode('~', $_GET['serviceType']);
            $decidedamount = explode('~', $_GET['decidedAmount']);
            $paymentreceived = explode('~', $_GET['paymentReceived']);
            $depositdate = explode('~', $_GET['depositDate']);
            $chequedetails = explode('~', $_GET['chequeDetails']);

            $payid = "INSERT INTO payment_system_id (customer_id) VALUES (:customer)";
            $statement = $db->prepare($payid);
            $statement->bindParam(':customer', $customer);
            $statement->execute();

            $sql = "SELECT max(LAST_INSERT_ID(payment_sy_id)) as id From payment_system_id";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $pay_id = $row['id'];

            for ($i = 0; $i < count($servicetypes); ++$i) {

                if ($servicetypes[$i] != "") {

                    if (count($depositdate) < ($i + 1) || $depositdate[$i] == "")
                        $date = NULL;
                    else
                        $date = date("Y-m-d", strtotime($depositdate[$i]));

                    $balance = $decidedamount[$i] - $paymentreceived[$i];

                    $paymentDetails = "INSERT INTO payment_system (payment_sy_id, customer_id, service_type, amount_decided, payment_received, payment_date, cheque_details, balance)"
                            . " VALUES (:payid, :customer, :services, :decideAmount, :rcvAmount, :payDate, :checkdetails, :balance)";
                    $statement = $db->prepare($paymentDetails);
                    $statement->bindParam(':payid', $pay_id);
                    $statement->bindParam(':customer', $customer);
                    $statement->bindParam(':services', $servicetypes[$i]);
                    $statement->bindParam(':decideAmount', $decidedamount[$i]);
                    $statement->bindParam(':rcvAmount', $paymentreceived[$i]);
                    $statement->bindParam(':payDate', $date);
                    $statement->bindParam(':checkdetails', $chequedetails[$i]);
                    $statement->bindParam(':balance', $balance);
                    $statement->execute();
                }
            }
            echo 'success';
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}

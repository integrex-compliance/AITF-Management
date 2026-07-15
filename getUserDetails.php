<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_GET['userId']) && !empty($_GET['userId'])) {
    try { 
        $userDetails = "SELECT user_id, user_name, abbreviation, email_id, role FROM user WHERE user_id = :userid";
        $statement = $db->prepare($userDetails);
        $statement->bindParam(':userid', $_GET['userId']);
        $statement->execute();
        $user = $statement->fetch();
        echo json_encode($user);
    } catch (Exception $ex) {
        echo $ex;
    }
} else if (isset($_GET['customerId']) && !empty($_GET['customerId'])) {
    try {
        $customerDetails = "SELECT * FROM customer_details WHERE customer_id = :id";
        $statement = $db->prepare($customerDetails);
        $statement->bindParam(':id', $_GET['customerId']);
        $statement->execute();
        $customer = $statement->fetch();
        echo json_encode($customer);
    } catch (Exception $ex) {
        echo $ex;
    }
} else if (isset($_GET['rowId']) && !empty($_GET['rowId']) && isset($_GET['commentRow'])) {
    try {
        $certCommentCount = "SELECT count(*) as total from certification_body where certification_id = :certid and cert_comment = :commentId";
        $statement = $db->prepare($certCommentCount);
        $statement->bindParam(':certid', $_GET['rowId']);
        $statement->bindParam(':commentId', $_GET['commentRow']);
        $statement->execute();
        $rowCount = $statement->fetch();
        if ($rowCount['total'] > 0) {
            $certCommentRow = "SELECT cert_comment as comment, certification_id as rowid FROM certification_body WHERE certification_id = :certid ";
            $statement = $db->prepare($certCommentRow);
            $statement->bindParam(':certid', $_GET['rowId']);
            $statement->execute();
            $certComment = $statement->fetch();
            echo json_encode($certComment);
        } else {
            $consultingRow = "SELECT consulting_comment as comment, consulting_id as rowid FROM consulting WHERE consulting_id = :certid";
            $statement = $db->prepare($consultingRow);
            $statement->bindParam(':certid', $_GET['rowId']);
            $statement->execute();
            $consultComment = $statement->fetch();
            echo json_encode($consultComment);
        }
    } catch (Exception $ex) {
        echo $ex;
    }
}

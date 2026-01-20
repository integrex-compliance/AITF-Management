<?php

session_start();
include_once 'dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'utility.php';


if (isset($_POST['useremail']) && !empty($_POST['useremail']) && isset($_POST['password']) && !empty($_POST['password'])) {
    try {
        $selectUserEmail = "SELECT count(*) as user from user where email_id = :email";
        
        $statement = $db->prepare($selectUserEmail);
        $statement->bindParam(":email", $_POST['useremail']);
        $statement->execute();
        $rowUserCount = $statement->fetch();
        //print_r($rowUserCount); exit;
        if ($rowUserCount['user'] > 0) {
            $userLogin = "SELECT user_id, user_name, abbreviation, email_id, password, role FROM user WHERE email_id = :useremail";
            $statement = $db->prepare($userLogin);
            $statement->bindParam(':useremail', $_POST['useremail']);
            $statement->execute();
            $row = $statement->fetch();

//            if ($_POST['password'] == $row['password']) 
            if (password_verify($_POST['password'], $row['password'])) {
                $_SESSION['username'] = $row['user_name'];
                $_SESSION['id'] = $row['user_id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['abbrevation'] = $row['abbreviation'];
//                echo $_SESSION['role']; die;
                if (Utitlity::checkForUserAdmin()) {
                    header("Location: homePage.php");
                }
                if (Utitlity::checkForOfficeStaff()) {
//                    echo $_SESSION['role'];
                    header("Location: homePage.php");
                }
                if (Utitlity::checkForUserConsultant()) {
                    header("Location: auditPlanning.php");
                }
                if (Utitlity::checkForUserFinance()) {
                    header("Location: paymentPage.php");
                }
                if (Utitlity::checkForUserMarketing()) {
                    header("Location: marketingPage.php");
                }
                if (Utitlity::checkForMainAdmin()) {
                    header("Location: homePage.php");
                }
//                if(Utitlity::checkForUserConsultant()){
//                    header("Location: homePage.php");
//                }
            } else {
                // Invalid credentials: mark login error in session and redirect to clean login URL
                $_SESSION['login_error'] = true;
                header("Location: index.php");
            }
        } else {
            // Unknown email: treat as login error without exposing details in URL
            $_SESSION['login_error'] = true;
            header("Location: index.php");
        }
    } catch (Exception $exc) {
        // Log error instead of echoing to prevent AJAX parse errors
        error_log('Login Exception: ' . $exc->getTraceAsString());
        $_SESSION['login_error'] = true;
        header("Location: index.php");
    }
} else if (isset($_POST["clientemail"]) && !empty($_POST["clientemail"]) && isset($_POST["clientcid"]) && !empty($_POST["clientcid"])) {
    try {
        $input = preg_replace("/[^a-zA-Z]+/", "", $_POST["clientemail"]);
        $input = strtoupper($input);
        $customercount = "SELECT COUNT(*) AS client FROM `customer_info` WHERE unique_id = :clientpassword";
        $statement = $db->prepare($customercount);
//        $statement->bindParam(':clientemail', $_POST['clientemail']);
        $statement->bindParam(':clientpassword', $_POST['clientcid']);
        $statement->execute();
        $clientc = $statement->fetch();
//        echo $clientc['client']; 
        if ($clientc['client'] == 1) {
            $customer = "SELECT * FROM `customer_info` WHERE unique_id = :clientpassword ";
            $statement = $db->prepare($customer);
//            $statement->bindParam(':clientemail', $_POST['clientemail']);
            $statement->bindParam(':clientpassword', $_POST['clientcid']);
            $statement->execute();
            $customerinfo = $statement->fetch();

            $input1 = preg_replace("/[^a-zA-Z]+/", "", $customerinfo['customer_name']);
            $input1 = strtoupper($input1);
//            echo $input . " -> " . $input1;

            if (strpos($input1, $input) !== false) {
                $_SESSION['username'] = $customerinfo['customer_name'];
                $_SESSION['id'] = $customerinfo['id'];
                $_SESSION['role'] = "client";
                $_SESSION['uniqueid'] = $customerinfo['unique_id'];
//            echo $_SESSION['customername'];
                header("Location: clientportal.php");
            } else {
                // Invalid client login: show generic error banner without query params
                $_SESSION['login_error'] = true;
                header("Location: index.php");
            }
        } else {
            $_SESSION['login_error'] = true;
            header("Location: index.php");
        }
    } catch (Exception $ex) {
        // Log error instead of echoing to prevent AJAX parse errors
        error_log('Client Login Exception: ' . $ex->getMessage());
        $_SESSION['login_error'] = true;
        header("Location: index.php");
    }
} else if (isset($_POST["name"]) && !empty($_POST["name"]) && isset($_POST["cno"]) && !empty($_POST["cno"])) {
    try {
        // Normalize certificate number: remove all non-alphanumeric characters and uppercase
        $rawCertNo = trim($_POST['cno']);
        $normCertNo = strtoupper(preg_replace("/[^A-Za-z0-9]+/", "", $rawCertNo));

        $customerinfo = false;

        // 1) Try to find client via CDC office-use table (latest certificate numbers)
        $sqlCdcOffice = "SELECT ci.*
                         FROM cdc_report_review_office_use cu
                         INNER JOIN cdc_report_review crr ON crr.crr_id = cu.crr_id
                         INNER JOIN customer_info ci ON ci.id = crr.customer_id
                         WHERE UPPER(REPLACE(REPLACE(REPLACE(REPLACE(cu.certificate_no, ' ', ''), '/', ''), '-', ''), '.', '')) = :norm
                         ORDER BY cu.id DESC
                         LIMIT 1";
        $statement = $db->prepare($sqlCdcOffice);
        $statement->bindParam(':norm', $normCertNo);
        $statement->execute();
        $customerinfo = $statement->fetch();

        // 2) Then try certificate_print table (printed certificates)
        if (!$customerinfo) {
            $sqlCertPrint = "SELECT ci.*
                             FROM certificate_print cp
                             INNER JOIN customer_info ci ON ci.id = cp.customer_id
                             WHERE UPPER(REPLACE(REPLACE(REPLACE(REPLACE(cp.certificate_no, ' ', ''), '/', ''), '-', ''), '.', '')) = :norm
                             ORDER BY cp.certificate_id DESC
                             LIMIT 1";
            $statement = $db->prepare($sqlCertPrint);
            $statement->bindParam(':norm', $normCertNo);
            $statement->execute();
            $customerinfo = $statement->fetch();
        }

        // 3) Then try old CDC table field certificateNo
        if (!$customerinfo) {
            $sqlCdcOld = "SELECT ci.*
                          FROM cdc_report_review crr
                          INNER JOIN customer_info ci ON ci.id = crr.customer_id
                          WHERE UPPER(REPLACE(REPLACE(REPLACE(REPLACE(crr.certificateNo, ' ', ''), '/', ''), '-', ''), '.', '')) = :norm
                          ORDER BY crr.crr_id DESC
                          LIMIT 1";
            $statement = $db->prepare($sqlCdcOld);
            $statement->bindParam(':norm', $normCertNo);
            $statement->execute();
            $customerinfo = $statement->fetch();
        }

        // 4) Fallback: try customer_info.certification_no (legacy storage directly on customer)
        if (!$customerinfo) {
            $customer = "SELECT *
                         FROM `customer_info`
                         WHERE UPPER(REPLACE(REPLACE(REPLACE(REPLACE(certification_no, ' ', ''), '/', ''), '-', ''), '.', '')) = :norm
                         ORDER BY id DESC
                         LIMIT 1";
            $statement = $db->prepare($customer);
            $statement->bindParam(':norm', $normCertNo);
            $statement->execute();
            $customerinfo = $statement->fetch();
        }

        if ($customerinfo && isset($customerinfo['id'])) {
            $_SESSION['username'] = "External User";
            $_SESSION['id'] = $customerinfo['id'];
            $_SESSION['role'] = "External";
            header("Location: certifiedClient.php");
        } else {
            // No client found for this certificate number at all
            $_SESSION['validation_error'] = "invalid_client_warning";
            header("Location: validation.php");
        }
    } catch (Exception $ex) {
        $_SESSION['validation_error'] = "invalid_client_warning";
        header("Location: validation.php");
    }
}
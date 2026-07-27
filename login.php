<?php

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
include_once 'dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'utility.php';

$turnstileHelperPath = __DIR__ . '/include/turnstile_helper.php';
if (file_exists($turnstileHelperPath)) {
    require_once $turnstileHelperPath;
}

function aitf_redirect_and_exit($location)
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    header("Location: " . $location);
    exit;
}


if (isset($_POST['useremail']) && !empty($_POST['useremail']) && isset($_POST['password']) && !empty($_POST['password'])) {
    $turnstileToken = trim($_POST['cf-turnstile-response'] ?? '');
    $captchaError = '';
    if (function_exists('aitf_verify_turnstile') && !aitf_verify_turnstile($turnstileToken, $captchaError)) {
        $_SESSION['login_error'] = true;
        $_SESSION['login_notice'] = $captchaError !== '' ? $captchaError : 'Captcha verification failed. Please try again.';
        aitf_redirect_and_exit("loginSelection.php");
    }
    try {
        // Single query fetch instead of count + fetch
        $userLogin = "SELECT user_id, user_name, abbreviation, email_id, password, role FROM user WHERE email_id = :useremail LIMIT 1";
        $statement = $db->prepare($userLogin);
        $statement->bindParam(':useremail', $_POST['useremail']);
        $statement->execute();
        $row = $statement->fetch();

        if ($row && password_verify($_POST['password'], $row['password'])) {
            $_SESSION['username'] = $row['user_name'];
            $_SESSION['id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['abbrevation'] = $row['abbreviation'];
            aitf_mark_session_authenticated();
            if (Utitlity::checkForUserAdmin()) {
                aitf_redirect_and_exit("homePage.php");
            } elseif (Utitlity::checkForOfficeStaff()) {
                aitf_redirect_and_exit("homePage.php");
            } elseif (Utitlity::checkForUserConsultant()) {
                aitf_redirect_and_exit("auditPlanning.php");
            } elseif (Utitlity::checkForUserFinance()) {
                aitf_redirect_and_exit("paymentPage.php");
            } elseif (Utitlity::checkForUserMarketing()) {
                aitf_redirect_and_exit("marketingPage.php");
            } elseif (Utitlity::checkForMainAdmin()) {
                aitf_redirect_and_exit("homePage.php");
            } elseif (Utitlity::checkForStaff()) {
                aitf_redirect_and_exit("homePage.php");
            }

            $_SESSION['login_error'] = true;
            unset($_SESSION['username'], $_SESSION['id'], $_SESSION['role'], $_SESSION['abbrevation']);
            $_SESSION['login_notice'] = 'This account does not have access to a portal. Please contact the administrator.';
            aitf_redirect_and_exit("loginSelection.php");
        } else {
            // Invalid credentials: mark login error in session and redirect to clean login URL
            $_SESSION['login_error'] = true;
            $_SESSION['login_notice'] = 'Invalid username or password. Please try again.';
            aitf_redirect_and_exit("loginSelection.php");
        }
    } catch (Exception $exc) {
        // Log error instead of echoing to prevent AJAX parse errors
        error_log('Login Exception: ' . $exc->getTraceAsString());
        $_SESSION['login_error'] = true;
        $_SESSION['login_notice'] = 'Login could not be completed. Please try again.';
        aitf_redirect_and_exit("loginSelection.php");
    }
} else if (isset($_POST["clientemail"]) && !empty($_POST["clientemail"]) && isset($_POST["clientcid"]) && !empty($_POST["clientcid"])) {
    $turnstileToken = trim($_POST['cf-turnstile-response'] ?? '');
    $captchaError = '';
    if (function_exists('aitf_verify_turnstile') && !aitf_verify_turnstile($turnstileToken, $captchaError)) {
        $_SESSION['login_error'] = true;
        $_SESSION['login_notice'] = $captchaError !== '' ? $captchaError : 'Captcha verification failed. Please try again.';
        aitf_redirect_and_exit("loginSelection.php");
    }
    try {
        $input = preg_replace("/[^a-zA-Z]+/", "", $_POST["clientemail"]);
        $input = strtoupper($input);
        // Single fetch instead of count + fetch
        $customer = "SELECT * FROM `customer_info` WHERE unique_id = :clientpassword LIMIT 1";
        $statement = $db->prepare($customer);
        $statement->bindParam(':clientpassword', $_POST['clientcid']);
        $statement->execute();
        $customerinfo = $statement->fetch();
        //        echo $customerinfo['customer_name'];
        if ($customerinfo) {
            $input1 = preg_replace("/[^a-zA-Z]+/", "", $customerinfo['customer_name']);
            $input1 = strtoupper($input1);

            if (strpos($input1, $input) !== false) {
                $_SESSION['username'] = $customerinfo['customer_name'];
                $_SESSION['id'] = $customerinfo['id'];
                $_SESSION['role'] = "client";
                $_SESSION['uniqueid'] = $customerinfo['unique_id'];
                aitf_mark_session_authenticated();
                aitf_redirect_and_exit("clientportal.php");
            } else {
                // Invalid client login: show generic error banner without query params
                $_SESSION['login_error'] = true;
                $_SESSION['login_notice'] = 'Invalid client name or password. Please try again.';
                aitf_redirect_and_exit("loginSelection.php");
            }
        } else {
            $_SESSION['login_error'] = true;
            $_SESSION['login_notice'] = 'Invalid client name or password. Please try again.';
            aitf_redirect_and_exit("loginSelection.php");
        }
    } catch (Exception $ex) {
        // Log error instead of echoing to prevent AJAX parse errors
        error_log('Client Login Exception: ' . $ex->getMessage());
        $_SESSION['login_error'] = true;
        $_SESSION['login_notice'] = 'Login could not be completed. Please try again.';
        aitf_redirect_and_exit("loginSelection.php");
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
            aitf_mark_session_authenticated();
            aitf_redirect_and_exit("certifiedClient.php");
        } else {
            // No client found for this certificate number at all
            $_SESSION['validation_error'] = "invalid_client_warning";
            aitf_redirect_and_exit("validation.php");
        }
    } catch (Exception $ex) {
        $_SESSION['validation_error'] = "invalid_client_warning";
        aitf_redirect_and_exit("validation.php");
    }
} else {
    $_SESSION['login_notice'] = 'Please use the login form to continue.';
    aitf_redirect_and_exit("loginSelection.php");
}

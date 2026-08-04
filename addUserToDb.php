<?php

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';
date_default_timezone_set('Asia/Kolkata');
include 'utility.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (isset($_POST['directorAddUser']) && $_POST['directorAddUser'] === '1') {
    if (!Utitlity::checkForDirector()) {
        $_SESSION['director_notice'] = 'Only Director can generate employee IDs.';
        header("Location: loginSelection.php?reason=login_required");
        exit;
    }

    $name = trim((string)($_POST['userName'] ?? ''));
    $email = strtolower(trim((string)($_POST['userEmail'] ?? '')));
    $role = trim((string)($_POST['userRole'] ?? ''));
    $contactNo = trim((string)($_POST['contactNo'] ?? ''));

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $role === '' || $contactNo === '') {
        $_SESSION['director_notice'] = 'Please fill name, role, email and contact number.';
        header("Location: directorSpace.php");
        exit;
    }

    try {
        aitf_ensure_rbac_schema($db);

        $existing = $db->prepare("SELECT COUNT(*) FROM user WHERE LOWER(email_id) = :email AND is_deleted = '0'");
        $existing->execute(array(':email' => $email));
        if ((int)$existing->fetchColumn() > 0) {
            $_SESSION['director_notice'] = 'This email already exists in the system.';
            header("Location: directorSpace.php");
            exit;
        }

        $abbr = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        if ($abbr === '') {
            $abbr = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $role), 0, 3));
        }

        $eid = aitf_generate_employee_eid($db);
        $blockedPasswordHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

        $addUser = "INSERT INTO user(user_name, abbreviation, email_id, password, role, e_id, contact_no, account_status)
                    VALUES (:name, :abbr, :email, :password, :role, :eid, :contact_no, 'pending')";
        $statement = $db->prepare($addUser);
        $statement->execute(array(
            ':name' => $name,
            ':abbr' => $abbr,
            ':email' => $email,
            ':password' => $blockedPasswordHash,
            ':role' => $role,
            ':eid' => $eid,
            ':contact_no' => $contactNo
        ));

        try {
            aitf_send_employee_eid_mail(array(
                'user_name' => $name,
                'email_id' => $email,
                'e_id' => $eid
            ));
            $_SESSION['director_notice'] = 'Employee ID generated and sent to ' . $email . '. EID: ' . $eid;
        } catch (Exception $mailError) {
            error_log('Employee EID mail error: ' . $mailError->getMessage());
            $_SESSION['director_notice'] = 'Employee ID generated but email could not be sent. EID: ' . $eid;
        }

        header("Location: directorSpace.php");
        exit;
    } catch (Exception $ex) {
        error_log('Director add user error: ' . $ex->getMessage());
        $_SESSION['director_notice'] = 'Employee ID could not be generated. Please try again.';
        header("Location: directorSpace.php");
        exit;
    }
} else if (isset($_POST['userName']) && !empty($_POST['userName']) && isset($_POST['userEmail']) && !empty($_POST['userEmail']) && isset($_POST['userRole']) && !empty($_POST['userRole']) && isset($_POST['userPassword']) && !empty($_POST['userPassword'])) {
    try {
//        $str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
//        $length = 6;
//        $ucode = substr(str_shuffle(str_repeat($str, 5)), 0, $length);
        $hashedpwd = password_hash($_POST['userPassword'], PASSWORD_DEFAULT);

        $addUser = "INSERT INTO user(user_name, abbreviation, email_id, password, role) VALUES (:name, :userAbbr, :email,:password,:role)";
        $statement = $db->prepare($addUser);
        $statement->bindParam(':name', $_POST['userName']);
        $statement->bindParam(':email', $_POST['userEmail']);
        $statement->bindParam(':userAbbr', $_POST['userAbbr']);
        $statement->bindParam(':password', $hashedpwd);
        $statement->bindParam(':role', $_POST['userRole']);
        $statement->execute();
        header("Location: addUsers.php");
    } catch (Exception $ex) {
        echo $ex;
    }
} else if (isset($_POST['userId']) && !empty($_POST['userId']) && isset($_POST['editUserName']) && !empty($_POST['editUserName']) && isset($_POST['editUserEmail']) && !empty($_POST['editUserEmail']) && isset($_POST['editUserRole']) && !empty($_POST['editUserRole'])) {
    try {
//        $str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
//        $length = 6;
//        $ucode = substr(str_shuffle(str_repeat($str, 5)), 0, $length);
//        $hashedpwd = password_hash($ucode, PASSWORD_DEFAULT);

        $updateUser = "UPDATE user SET user_name = '" . $_POST['editUserName'] . "', abbreviation = '".$_POST['editUserAbbr']."', email_id = '" . $_POST['editUserEmail'] . "', role = '" . $_POST['editUserRole'] . "' WHERE user_id = '" . $_POST['userId'] . "'";
        $statement = $db->prepare($updateUser);
        $statement->execute();
        header("Location: addUsers.php");
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
        try {
            $deleteUser = "DELETE FROM user WHERE user_id = '" . $_POST['user_id'] . "'";
            $statement = $db->prepare($deleteUser);
            $statement->execute();
            header("Location: addUsers.php");
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}

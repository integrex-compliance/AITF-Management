<?php

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'utility.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (isset($_POST['userName']) && !empty($_POST['userName']) && isset($_POST['userEmail']) && !empty($_POST['userEmail']) && isset($_POST['userRole']) && !empty($_POST['userRole']) && isset($_POST['userPassword']) && !empty($_POST['userPassword'])) {
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

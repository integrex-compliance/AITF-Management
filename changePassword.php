<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include 'utility.php';

if (isset($_POST['oldPassword']) && !empty($_POST['oldPassword']) && isset($_POST['newPassword']) && !empty($_POST['newPassword']) && isset($_POST['confirmPassword']) && !empty($_POST['confirmPassword']) && isset($_POST['user_id']) && !empty($_POST['user_id'])) {
    try {
        $oldPassword = "SELECT password FROM user WHERE user_id = :user";
        $statement = $db->prepare($oldPassword);
        $statement->bindParam(':user', $_POST['user_id']);
        $statement->execute();
        $oldPass = $statement->fetch();
        if (password_verify($_POST['oldPassword'], $oldPass['password'])) {
            $hashedpwd = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
            $changePass = "UPDATE user SET password = :newPass WHERE user_id = :id";
            $statement = $db->prepare($changePass);
            $statement->bindParam(':id', $_POST['user_id']);
            $statement->bindParam(':newPass', $hashedpwd);
            $statement->execute();
            $returnPage = Utitlity:: getHomePageForUser();
            header("Location: " . $returnPage);
        } else {
            $_SESSION['error_message'] = "Invalid old password, <a href='" . Utitlity:: getHomePageForUser() . "'>try again </a>";
            header("Location: error");
        }
    } catch (Exception $ex) {
        $_SESSION['error_message'] = "Something wrong happened, please contact admin or <a href='" . Utitlity:: getHomePageForUser() . "'>try again </a>";
        header("Location: error");
    }
} else if (isset($_POST['userID']) && !empty($_POST['userID']) && isset($_POST['resetNewPassword']) && !empty($_POST['resetNewPassword'])) {
    try {
        $hashedpwd = password_hash($_POST['resetNewPassword'], PASSWORD_DEFAULT);
        $passwordReset = "UPDATE user SET password = :newPass  WHERE user_id = :id";
        $statement = $db->prepare($passwordReset);
        $statement->bindParam(':id', $_POST['userID']);
        $statement->bindParam(':newPass', $hashedpwd);
        $statement->execute();
        header("Location: addUsers.php?userid=" . $_POST['userID'] . "&passwd=" . $_POST['resetNewPassword']);
    } catch (Exception $ex) {
        
    }
} 

//public function passwordChange() {
//        $prevPassword = Input::get("prevPassword");
//        $newPassword = bcrypt(Input::get("newPassword"));
//        $conformPassword = Input::get("conformPassword");
//        $userId = Input::get("user_id");
//        $userEmail = Input::get("user_email");
//        $usersPassword = User::where('id', $userId)->pluck('password');
//
//        if (password_verify($prevPassword, $usersPassword)) {
//
//            Mail::send('emails/changePassword', Input::all(), function($message) use ($userEmail) {
//                $message->to($userEmail)->subject("Password Successfully Changed");
//            });
//
//            $changePassword = User::where("id", $userId)->update(["password" => $newPassword]);
//            return Redirect::back()->with($changePassword)->with('message', "Password Change successfully");
//        } else {
//            return Redirect::back()->with('message', "Unable to change password");
//        }
//    }

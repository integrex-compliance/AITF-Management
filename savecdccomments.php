<?php


session_start();
include_once './dbConnect.php';

if(isset($_GET["id"]) && !empty($_GET["id"]) && isset($_GET["columnName"]) && !empty($_GET["columnName"]) && isset($_GET["columnValue"]) && !empty($_GET["columnValue"])){
    try {
        $updatecomment = "UPDATE `cdc_report_review` SET `cdc_obj_comment`= :comment WHERE `crr_id` = :id";
        $statement = $db->prepare($updatecomment);
        $statement->bindParam(":comment",$_GET["columnValue"]);
        $statement->bindParam(":id",$_GET["id"]);
        $statement->execute();
        echo "success";
    } catch (Exception $ex) {
        
    }
} else if(isset($_GET["app_id"]) && !empty($_GET["app_id"]) && isset($_GET["column_Name"]) && !empty($_GET["column_Name"]) && isset($_GET["column_Value"]) && !empty($_GET["column_Value"])){
    try {
        $updatecomment = "UPDATE `audit_application` SET `app_obj_comment`= :comment WHERE `audit_id` = :id";
        $statement = $db->prepare($updatecomment);
        $statement->bindParam(":comment",$_GET["column_Value"]);
        $statement->bindParam(":id",$_GET["app_id"]);
        $statement->execute();
        echo "success";
    } catch (Exception $ex) {
        
    }
}

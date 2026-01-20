<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_GET["iafid"]) && !empty($_GET["iafid"])) {
    try {
        $sql = "SELECT * FROM `nace_codes` where iaf_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET["iafid"]);
        $statement->execute();
        $nace = $statement->fetchAll();
        echo json_encode($nace);
    } catch (Exception $ex) {
        
    }
} elseif (isset($_GET["nace_code"]) && !empty($_GET["nace_code"])) {
    try {
        $sql = "SELECT * FROM `nace_codes` where nace_code = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET["nace_code"]);
        $statement->execute();
        $nace = $statement->fetchAll();
        echo json_encode($nace);
    } catch (Exception $ex) {
        
    }}elseif (isset($_GET["class_code"]) && !empty($_GET["class_code"])) {
        try {
            $sql = "SELECT * FROM `nace2_classes` where nace_class = :id";
            $statement = $db->prepare($sql);
            $statement->bindParam(":id", $_GET["class_code"]);
            $statement->execute();
            $nace = $statement->fetchAll();
            echo json_encode($nace);
        } catch (Exception $ex) {
            
        }}
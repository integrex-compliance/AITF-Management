<?php
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST["nace_code_id"]) && !empty($_POST["nace_code_id"])) {
    try {
        $codearray = explode('~', $_POST['nace_code_array']);
        $descarray = explode('~', $_POST['nace_desc_array']);

        $sql2 = "DELETE FROM `nace2_classes` WHERE `ifa_division_id` = :id AND `nace_group_id` = :id1";
        $statement = $db->prepare($sql2);
        $statement->bindParam(":id", $_POST["iaf_code_id"]);
        $statement->bindParam(":id1", $_POST["nace_code_id"]);
        $statement->execute();
        for ($i = 0; $i < count($codearray); ++$i) {
            if ($codearray[$i] != "") {
                $sql = "INSERT INTO `nace2_classes`(`ifa_division_id`,`nace_group_id`, `nace_class`, `nace_desc`) VALUES (:iafid,:groupId,:nacecode, :nacedesc)";
                $statement = $db->prepare($sql);
                $statement->bindParam(":iafid", $_POST["iaf_code_id"]);
                $statement->bindParam(":groupId", $_POST["nace_code_id"]);
                $statement->bindParam(":nacecode", $codearray[$i]);
                $statement->bindParam(":nacedesc", $descarray[$i]);
                $rr = $statement->execute();
                var_dump($rr);
                print_r($rr);
                if (!$rr) {
                    print_r($statement->errorInfo());
                }
            }
        }
        header("Location: masterCreator.php");
    } catch (Exception $ex) {
        
    }
} else {
    $codearray = explode('~', $_POST['nace_code_array']);
    $descarray = explode('~', $_POST['nace_desc_array']);

    $sql = "INSERT INTO iaf_codes (iaf_code, iaf_desc) VALUES (:code, :desc)";
    $statement = $db->prepare($sql);
    $statement->bindParam(":code", $_POST["newIafInput"]);
    $statement->bindParam(":desc", $_POST["newIafDesc"]);
    $statement->execute();

    $sql = "SELECT max(LAST_INSERT_ID(iaf_id)) as id From iaf_codes";
    $statement = $db->prepare($sql);
    $statement->execute();
    $row = $statement->fetch();

    $iafid = $row['id'];

    for ($i = 0; $i < count($codearray); ++$i) {
        if ($codearray[$i] != "") {
            $sql = "INSERT INTO `nace2_classes`(`iaf_id`, `nace_code`, `nace_desc`) VALUES (:iafid, :nacecode, :nacedesc)";
            $statement = $db->prepare($sql);
            $statement->bindParam(":iafid", $iafid);
            $statement->bindParam(":nacecode", $codearray[$i]);
            $statement->bindParam(":nacedesc", $descarray[$i]);
            $statement->execute();
        }
    }
    header("Location: masterCreator.php");
}

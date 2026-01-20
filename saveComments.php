<?php


session_start();
include_once './dbConnect.php';

if (isset($_GET['rowId']) && !empty($_GET['rowId']) && isset($_GET['column']) && !empty($_GET['column']) && isset($_GET['comment']) && !empty($_GET['comment'])) {
    try {
        if ($_GET['column'] == "comment") {
            $caCommentUpdate = "UPDATE ca_service_dates SET comment = :comment WHERE action_id = :rowId ";
            $statement = $db->prepare($caCommentUpdate);
            $statement->bindParam(':comment', $_GET['comment']);
            $statement->bindParam(':rowId', $_GET['rowId']);
            $statement->execute();
            echo "success";
        }        
    } catch (Exception $ex) {
        echo $ex;
    }
}
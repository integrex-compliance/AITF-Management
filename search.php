<?php


include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_GET['auditor']) && !empty($_GET['auditor'])) {
    try {
        $row_set = array();
        $auditorList = "SELECT DISTINCT auditor_name from auditors  where auditor_name like '" . $_GET['auditor'] . "%'";
        $statement = $db->prepare($auditorList);
        $statement->execute();
        if ($statement->rowCount() > 0)
            $suditors = $statement->fetchAll();
        foreach ($suditors as $data) {
            $new['value'] = htmlentities(stripslashes($data['auditor_name']));
            $new['label'] = $data['auditor_name'];
            array_push($row_set, $new); //build an array
        }
        echo json_encode($row_set);
    } catch (Exception $ex) {
        
    }
}elseif ($_POST["scheduleAuditor_autocomplate"]) {
    
    $sql = "SELECT auditor_name FROM auditors";
    $statement = $db->prepare($sql);
    $statement->execute();
    echo json_encode($statement->fetchAll());
   
    
}

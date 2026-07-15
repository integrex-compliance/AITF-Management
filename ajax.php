<?php
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
//print_r($_POST); exit;
if(!empty($_POST["selectedValue"])){ 
    // Fetch state data based on the specific country 
    $iafCodes = "SELECT * FROM nace_codes where iaf_id = ".$_POST['selectedValue']."";
    $statement = $db->prepare($iafCodes);
    $statement->execute();
    $iafList = $statement->fetchAll();
    if(isset($iafList)){ 
        echo '<option value="">Select Group</option>'; 
        foreach($iafList as $iafListGroups){  
            echo '<option value="'.$iafListGroups['nace_id'].'">'.$iafListGroups['nace_code'].'</option>'; 
        } 
    }else{ 
        echo '<option value="">Group not available</option>'; 
    } 
}
?>

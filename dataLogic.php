 <?php

    /*

     * To change this license header, choose License Headers in Project Properties.

     * To change this template file, choose Tools | Templates

     * and open the template in the editor.

     */



    include_once './dbConnect.php';

    date_default_timezone_set('Asia/Kolkata');



//    auditors

    // $auditors = "SELECT unique_id,certification_no from customer_info where certification_no is not null";
    // $statement = $db->prepare($auditors);
    // $statement->execute();
    // $auditorsList = $statement->fetchAll();
    // foreach ($auditorsList as $value) {
    //     $certificateValue = substr($value["certification_no"], 3);
    //     $length = 3;
    //     $randomletter = rand(100,999);
    //     $finalCertifcationNumber =  $randomletter.''.$certificateValue;
    //     $sql = "UPDATE customer_info SET `certification_no`= :certno WHERE unique_id = :unique_id ";
    //     $statement = $db->prepare($sql);
    //     $statement->bindParam(":unique_id", $value[unique_id]);
    //     $statement->bindParam(":certno", $finalCertifcationNumber);
    //     $statement->execute();
    // }

    // $auditors = "SELECT certificateNo,customer_id from cdc_report_review where certificateNo is not null";
    // $statement = $db->prepare($auditors);
    // $statement->execute();
    // $auditorsList = $statement->fetchAll();
    // foreach ($auditorsList as $value) {
        
    //     $selectSingleData = "SELECT certification_no FROM `customer_info` where id = :id ";
    //     $statement = $db->prepare($selectSingleData);
    //     $statement->bindParam(":id", $value['customer_id']);
    //     $statement->execute();
    //     // print_r($statement);
    //     $svcid = $statement->fetch();
        
    //     $sql = "UPDATE cdc_report_review SET `certificateNo`= :certno WHERE customer_id = :customer_id ";
    //     $statement = $db->prepare($sql);
    //     $statement->bindParam(":customer_id", $value["customer_id"]);
    //     $statement->bindParam(":certno", $svcid["certification_no"]);
    //     $statement->execute();
    // }


    $auditors = "SELECT certificate_no,customer_id from certificate_print where certificate_no is not null";
    $statement = $db->prepare($auditors);
    $statement->execute();
    $auditorsList = $statement->fetchAll();
    foreach ($auditorsList as $value) {
        
        $selectSingleData = "SELECT certification_no FROM `customer_info` where id = :id ";
        $statement = $db->prepare($selectSingleData);
        $statement->bindParam(":id", $value['customer_id']);
        $statement->execute();
        $svcid = $statement->fetch();
        
        $sql = "UPDATE certificate_print SET `certificate_no`= :certno WHERE customer_id = :customer_id ";
        $statement = $db->prepare($sql);
        $statement->bindParam(":customer_id", $value["customer_id"]);
        $statement->bindParam(":certno", $svcid["certification_no"]);
        $statement->execute();
    }



            

  

   // exit;

    

    

    

?>    

    
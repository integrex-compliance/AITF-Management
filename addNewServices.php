<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_GET['auditors_Id']) && !empty($_GET['auditors_Id']) && isset($_GET['newAuditors']) && !empty($_GET['newAuditors'])) {
    try {
        $auditorUpdate = "UPDATE auditors SET auditor_name = :auditor WHERE auditors_id = :id ";
        $statement = $db->prepare($auditorUpdate);
        $statement->bindParam(':auditor', $_GET['newAuditors']);
        $statement->bindParam(':id', $_GET['auditors_Id']);
        $statement->execute();

        $auditors = "SELECT * from auditors";
        $statement = $db->prepare($auditors);
        $statement->execute();
        $auditorsList = $statement->fetchAll();

        echo json_encode($auditorsList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET['newAuditors']) && !empty($_GET['newAuditors'])) {
    try {
        $newAuditor = "INSERT INTO auditors (auditor_name) VALUES (:newAuditors)";
        $statement = $db->prepare($newAuditor);
        $statement->bindParam(':newAuditors', $_GET['newAuditors']);
        $statement->execute();

        $auditors = "SELECT * from auditors";
        $statement = $db->prepare($auditors);
        $statement->execute();
        $auditorsList = $statement->fetchAll();

        echo json_encode($auditorsList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET['auditorId']) && !empty($_GET['auditorId'])) {
    try {
        $removeAuditors = "DELETE FROM auditors WHERE auditors_id = :auditorId";
        $statement = $db->prepare($removeAuditors);
        $statement->bindParam(':auditorId', $_GET['auditorId']);
        $statement->execute();

        $auditors = "SELECT * from auditors";
        $statement = $db->prepare($auditors);
        $statement->execute();
        $auditorsList = $statement->fetchAll();

        echo json_encode($auditorsList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_POST['taxation_id_update']) && !empty($_POST['taxation_id_update'])) {

    try {

        $updateTaxation = "UPDATE taxation SET type_of_tax=:newTaxation,rate=:newRate WHERE taxation_id=:taxation_id_update";
        $statement = $db->prepare($updateTaxation);
        $statement->bindParam(':newTaxation', $_POST['newTaxation']);
        $statement->bindParam(':newRate', $_POST['newRate']);
        $statement->bindParam(':taxation_id_update', $_POST['taxation_id_update']);
        $statement->execute();
        header("Location: masterCreator.php");
    } catch (Exception $ex) {
        
    }
} else if (isset($_POST['newTaxation']) && !empty($_POST['newTaxation']) && isset($_POST['newRate']) && !empty($_POST['newRate'])) {

    try {
        $newTaxation = "INSERT INTO taxation(type_of_tax,rate) VALUES (:newTaxation,:newRate)";
        $statement = $db->prepare($newTaxation);
        $statement->bindParam(':newTaxation', $_POST['newTaxation']);
        $statement->bindParam(':newRate', $_POST['newRate']);
        $statement->execute();

        //        $taxation = "SELECT * FROM taxation";
        //        $statement = $db->prepare($taxation);
        //        $statement->execute();
        //        $taxationList = $statement->fetchAll();
        //
		//        echo json_encode($taxationList);
        header("Location: masterCreator.php");
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET['taxationId']) && !empty($_GET['taxationId'])) {
    try {
        $removeTaxation = "DELETE FROM taxation WHERE taxation_id = :taxationId";
        $statement = $db->prepare($removeTaxation);
        $statement->bindParam(':taxationId', $_GET['taxationId']);
        $statement->execute();

        $taxation = "SELECT * FROM taxation";
        $statement = $db->prepare($taxation);
        $statement->execute();
        $taxationList = $statement->fetchAll();

        echo json_encode($taxationList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET['taxation_Id']) && !empty($_GET['taxation_Id'])) {
    try {

        $taxation = "SELECT * FROM taxation  WHERE taxation_id=:taxation_Id ";
        $statement = $db->prepare($taxation);
        $statement->bindParam(':taxation_Id', $_GET['taxation_Id']);
        $statement->execute();
        $taxationList = $statement->fetch();

        echo json_encode($taxationList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET['auditor_Id']) && !empty($_GET['auditor_Id'])) {
    try {
        $auditor = "SELECT * FROM auditors WHERE auditors_id = :id";
        $statement = $db->prepare($auditor);
        $statement->bindParam(':id', $_GET['auditor_Id']);
        $statement->execute();
        $auditorInfo = $statement->fetch();
        echo json_encode($auditorInfo);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET['newStandards']) && !empty($_GET['newStandards']) && isset($_GET['standards_Id']) && !empty($_GET['standards_Id'])) {
    try {
        $updateStandard = "UPDATE standards SET standards_name = :standards WHERE id = :id";
        $statement = $db->prepare($updateStandard);
        $statement->bindParam(":standards", $_GET['newStandards']);
        $statement->bindParam(":id", $_GET['standards_Id']);
        $statement->execute();

        $standards = "SELECT * FROM standards";
        $statement = $db->prepare($standards);
        $statement->execute();
        $standardsList = $statement->fetchAll();

        echo json_encode($standardsList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET['newStandards']) && !empty($_GET['newStandards'])) {
    try {
        $standardInsert = "INSERT INTO standards (standards_name) VALUES (:standatd)";
        $statement = $db->prepare($standardInsert);
        $statement->bindParam(":standatd", $_GET['newStandards']);
        $statement->execute();

        $standards = "SELECT * FROM standards";
        $statement = $db->prepare($standards);
        $statement->execute();
        $standardsList = $statement->fetchAll();

        echo json_encode($standardsList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET['standatd_Id']) && !empty($_GET['standatd_Id'])) {
    try {
        $standardsinfo = "SELECT * FROM standards where id = :id";
        $statement = $db->prepare($standardsinfo);
        $statement->bindParam(":id", $_GET['standatd_Id']);
        $statement->execute();
        $row = $statement->fetch();
        echo json_encode($row);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["standardId"]) && !empty($_GET["standardId"])) {
    try {
        $standardDlt = "DELETE FROM standards WHERE id = :id";
        $statement = $db->prepare($standardDlt);
        $statement->bindParam(":id", $_GET["standardId"]);
        $statement->execute();

        $standards = "SELECT * FROM standards";
        $statement = $db->prepare($standards);
        $statement->execute();
        $standardsList = $statement->fetchAll();

        echo json_encode($standardsList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["newAccreditation"]) && !empty($_GET["newAccreditation"]) && isset($_GET["accreditation_Id"]) && !empty($_GET["accreditation_Id"])) {
    try {
        $accreditationUpdate = "UPDATE accreditation SET accreditation_name = :accreditation WHERE accreditation_id = :id";
        $statement = $db->prepare($accreditationUpdate);
        $statement->bindParam(":accreditation", $_GET["newAccreditation"]);
        $statement->bindParam(":id", $_GET["accreditation_Id"]);
        $statement->execute();

        $accreditation = "SELECT * FROM accreditation";
        $statement = $db->prepare($accreditation);
        $statement->execute();
        $accreditationList = $statement->fetchAll();

        echo json_encode($accreditationList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["newAccreditation"]) && !empty($_GET["newAccreditation"])) {
    try {
        $accreditation = "INSERT INTO accreditation (accreditation_name) VALUES (:accredation)";
        $statement = $db->prepare($accreditation);
        $statement->bindParam(":accredation", $_GET["newAccreditation"]);
        $statement->execute();

        $accreditation = "SELECT * FROM accreditation";
        $statement = $db->prepare($accreditation);
        $statement->execute();
        $accreditationList = $statement->fetchAll();

        echo json_encode($accreditationList);
    } catch (Exception $ex) {
        
    }
}else if (isset($_GET["newcategories"]) && !empty($_GET["newcategories"]) && isset($_GET["categories_Id"]) && !empty($_GET["categories_Id"])) {
    try {
        $categoriesUpdate = "UPDATE categories SET categories_name = :categories WHERE categories_id = :id";
        $statement = $db->prepare($categoriesUpdate);
        $statement->bindParam(":categories", $_GET["newcategories"]);
        $statement->bindParam(":id", $_GET["categories_Id"]);
        $statement->execute();

        $categories = "SELECT * FROM categories";
        $statement = $db->prepare($categories);
        $statement->execute();
        $categoriesList = $statement->fetchAll();

        echo json_encode($categoriesList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["newcategories"]) && !empty($_GET["newcategories"])) {
    try {
        $categories = "INSERT INTO categories (categories_name) VALUES (:accredation)";
        $statement = $db->prepare($categories);
        $statement->bindParam(":accredation", $_GET["newcategories"]);
        $statement->execute();

        $categories = "SELECT * FROM categories";
        $statement = $db->prepare($categories);
        $statement->execute();
        $categoriesList = $statement->fetchAll();

        echo json_encode($categoriesList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["accreditation_Id"]) && !empty($_GET["accreditation_Id"])) {
    try {
        $accreditation = "SELECT * FROM accreditation where accreditation_id = :id";
        $statement = $db->prepare($accreditation);
        $statement->bindParam(":id", $_GET["accreditation_Id"]);
        $statement->execute();
        $accreditationInfo = $statement->fetch();
        echo json_encode($accreditationInfo);
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else if (isset($_GET["accreditationId"]) && !empty($_GET["accreditationId"])) {
    try {
        $deleteAccredation = "DELETE FROM accreditation WHERE accreditation_id = :id";
        $statement = $db->prepare($deleteAccredation);
        $statement->bindParam(":id", $_GET["accreditationId"]);
        $statement->execute();

        $accreditation = "SELECT * FROM accreditation";
        $statement = $db->prepare($accreditation);
        $statement->execute();
        $accreditationList = $statement->fetchAll();

        echo json_encode($accreditationList);
    } catch (Exception $ex) {
        
    }
}else if (isset($_GET["categories_Id"]) && !empty($_GET["categories_Id"])) {
    try {
        $categories = "SELECT * FROM categories where categories_id = :id";
        $statement = $db->prepare($categories);
        $statement->bindParam(":id", $_GET["categories_Id"]);
        $statement->execute();
        $categoriesInfo = $statement->fetch();
        echo json_encode($categoriesInfo);
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else if (isset($_GET["categoriesId"]) && !empty($_GET["categoriesId"])) {
    try {
        $deleteAccredation = "DELETE FROM categories WHERE categories_id = :id";
        $statement = $db->prepare($deleteAccredation);
        $statement->bindParam(":id", $_GET["categoriesId"]);
        $statement->execute();

        $categories = "SELECT * FROM categories";
        $statement = $db->prepare($categories);
        $statement->execute();
        $categoriesList = $statement->fetchAll();

        echo json_encode($categoriesList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["newLegalStatus"]) && !empty($_GET["newLegalStatus"]) && isset($_GET["legalStatus_Id"]) && !empty($_GET["legalStatus_Id"])) {
    try {
        $updateStatus = "UPDATE legal_ststus SET status = :status WHERE status_id = :id";
        $statement = $db->prepare($updateStatus);
        $statement->bindParam(":status", $_GET["newLegalStatus"]);
        $statement->bindParam(":id", $_GET["legalStatus_Id"]);
        $statement->execute();

        $legalStatus = "SELECT * FROM legal_ststus";
        $statement = $db->prepare($legalStatus);
        $statement->execute();
        $legalStatusList = $statement->fetchAll();

        echo json_encode($legalStatusList);
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else if (isset($_GET["newLegalStatus"]) && !empty($_GET["newLegalStatus"])) {
    $legalStatusInsert = "INSERT INTO legal_ststus (status) VALUES (:ststus)";
    $statement = $db->prepare($legalStatusInsert);
    $statement->bindParam(":ststus", $_GET["newLegalStatus"]);
    $statement->execute();

    $legalStatus = "SELECT * FROM legal_ststus";
    $statement = $db->prepare($legalStatus);
    $statement->execute();
    $legalStatusList = $statement->fetchAll();

    echo json_encode($legalStatusList);
} else if (isset($_GET["legalStatus_Id"]) && !empty($_GET["legalStatus_Id"])) {
    try {
        $statusInfo = "SELECT * FROM legal_ststus WHERE status_id = :id";
        $statement = $db->prepare($statusInfo);
        $statement->bindParam(":id", $_GET["legalStatus_Id"]);
        $statement->execute();
        $status = $statement->fetch();

        echo json_encode($status);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["legalStatusId"]) && !empty($_GET["legalStatusId"])) {
    try {
        $delete = "DELETE FROM legal_ststus WHERE status_id = :id";
        $statement = $db->prepare($delete);
        $statement->bindParam(":id", $_GET["legalStatusId"]);
        $statement->execute();

        $legalStatus = "SELECT * FROM legal_ststus";
        $statement = $db->prepare($legalStatus);
        $statement->execute();
        $legalStatusList = $statement->fetchAll();

        echo json_encode($legalStatusList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["newNace"]) && !empty($_GET["newNace"]) && isset($_GET["nace_Id"]) && !empty($_GET["nace_Id"])) {
    try {
        $updateNace = "UPDATE nace_codes SET nace_code = :nace WHERE nace_id = :id";
        $statement = $db->prepare($updateNace);
        $statement->bindParam(":nace", $_GET['newNace']);
        $statement->bindParam(":id", $_GET["nace_Id"]);
        $statement->execute();

        $naceCodes = "SELECT * FROM nace_codes";
        $statement = $db->prepare($naceCodes);
        $statement->execute();
        $naceCodeList = $statement->fetchAll();

        echo json_encode($naceCodeList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["newNace"]) && !empty($_GET["newNace"])) {
    try {
        $naceInsert = "INSERT INTO nace_codes (nace_code) VALUES (:naceCode)";
        $statement = $db->prepare($naceInsert);
        $statement->bindParam(":naceCode", $_GET["newNace"]);
        $statement->execute();

        $naceCodes = "SELECT * FROM nace_codes";
        $statement = $db->prepare($naceCodes);
        $statement->execute();
        $naceCodeList = $statement->fetchAll();

        echo json_encode($naceCodeList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["nace_Id"]) && !empty($_GET["nace_Id"])) {
    try {
        $naceInfo = "SELECT * FROM nace_codes WHERE nace_id = :id";
        $statement = $db->prepare($naceInfo);
        $statement->bindParam(":id", $_GET["nace_Id"]);
        $statement->execute();
        $naceRow = $statement->fetch();

        echo json_encode($naceRow);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["naceId"]) && !empty($_GET["naceId"])) {
    try {
        $deleteNace = "DELETE FROM nace_codes WHERE nace_id = :naceId";
        $statement = $db->prepare($deleteNace);
        $statement->bindParam(":naceId", $_GET["naceId"]);
        $statement->execute();

        $naceCodes = "SELECT * FROM nace_codes";
        $statement = $db->prepare($naceCodes);
        $statement->execute();
        $naceCodeList = $statement->fetchAll();

        echo json_encode($naceCodeList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["newIaf"]) && !empty($_GET["newIaf"]) && !empty($_GET["iaf_Id"]) && !empty($_GET["iaf_Id"])) {
    try {
        $updateIaf = "UPDATE iaf_codes SET iaf_code = :code WHERE iaf_id = :id";
        $statement = $db->prepare($updateIaf);
        $statement->bindParam(":code", $_GET["newIaf"]);
        $statement->bindParam(":id", $_GET["iaf_Id"]);
        $statement->execute();

        $iafCodes = "SELECT * FROM iaf_codes";
        $statement = $db->prepare($iafCodes);
        $statement->execute();
        $iafList = $statement->fetchAll();

        echo json_encode($iafList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["newIaf"]) && !empty($_GET["newIaf"])) {
    try {
        $insertIaf = "INSERT INTO iaf_codes (iaf_code) VALUES (:iaf)";
        $statement = $db->prepare($insertIaf);
        $statement->bindParam(":iaf", $_GET["newIaf"]);
        $statement->execute();

        $iafCodes = "SELECT * FROM iaf_codes";
        $statement = $db->prepare($iafCodes);
        $statement->execute();
        $iafList = $statement->fetchAll();

        echo json_encode($iafList);
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else if (isset($_GET["iaf_Id"]) && !empty($_GET["iaf_Id"])) {
    try {
        $iafInfo = "SELECT * FROM iaf_codes WHERE iaf_id = :id";
        $statement = $db->prepare($iafInfo);
        $statement->bindParam(":id", $_GET["iaf_Id"]);
        $statement->execute();
        $iafData = $statement->fetch();

        $nacecode = "SELECT * FROM `nace_codes` WHERE iaf_id = :id";
        $statement = $db->prepare($nacecode);
        $statement->bindParam(":id", $_GET["iaf_Id"]);
        $statement->execute();
        $nacecodes = $statement->fetchAll();
        echo json_encode(array($iafData, $nacecodes));
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["group_Id"]) && !empty($_GET["group_Id"]) && isset($_GET["group_Id1"]) && !empty($_GET["group_Id1"])) {
    //print_r($_GET["group_Id"]); exit;
        try {
            $iafInfo = "SELECT * FROM iaf_codes WHERE iaf_id = :id";
            $statement = $db->prepare($iafInfo);
            $statement->bindParam(":id", $_GET["group_Id"]);
            $statement->execute();
            $iafData = $statement->fetch();

            $X = $_GET["group_Id2"];
            $Y = $_GET["group_Id1"];

            $nacecode = "SELECT `nace_code` FROM `nace_codes` WHERE nace_id = :id";
            $statement = $db->prepare($nacecode);
            $statement->bindParam(":id", $_GET["group_Id1"]);
            $statement->execute();
            $nacecodes = $statement->fetchAll();

            $iafInfo1 = "SELECT * FROM `nace2_classes` WHERE nace_group_id = :id";
            $statement = $db->prepare($iafInfo1);
            $statement->bindParam(":id", $Y);
            $statement->execute();
            $naceData = $statement->fetchAll();
            echo json_encode(array($iafData,$nacecodes,$naceData,$X,$Y));
    
        } catch (Exception $ex) {
    }    
} else if (isset($_GET["iafId"]) && !empty($_GET["iafId"])) {
    try {
        $deleteIaf = "DELETE FROM iaf_codes WHERE iaf_id = :id";
        $statement = $db->prepare($deleteIaf);
        $statement->bindParam(":id", $_GET["iafId"]);
        $statement->execute();

        $deleteNace = "DELETE FROM nace_codes WHERE iaf_id = :id";
        $statement = $db->prepare($deleteNace);
        $statement->bindParam(":id", $_GET["iafId"]);
        $statement->execute();

        $deleteclass = "DELETE FROM nace2_classes WHERE ifa_division_id = :id";
        $statement = $db->prepare($deleteclass);
        $statement->bindParam(":id", $_GET["iafId"]);
        $statement->execute();

        $iafCodes = "SELECT * FROM iaf_codes";
        $statement = $db->prepare($iafCodes);
        $statement->execute();
        $iafList = $statement->fetchAll();

        echo json_encode($iafList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["idStatus"]) && !empty($_GET["idStatus"]) && isset($_GET["newStatus"]) && !empty($_GET["newStatus"])) {
    try {
        $updateStatus = "UPDATE customer_status SET status = :status WHERE id = :id";
        $statement = $db->prepare($updateStatus);
        $statement->bindParam(":id", $_GET["idStatus"]);
        $statement->bindParam(":status", $_GET["newStatus"]);
        $statement->execute();

        $customerStatus = "SELECT * FROM customer_status";
        $statement = $db->prepare($customerStatus);
        $statement->execute();
        $status = $statement->fetchAll();
        echo json_encode($status);
    } catch (Exception $e) {
        
    }
} else if (isset($_GET["newStatus"]) && !empty($_GET["newStatus"])) {
    try {
        $sql = "INSERT INTO customer_status (status) VALUES (:newStatus)";
        $statement = $db->prepare($sql);
        $statement->bindParam(":newStatus", $_GET["newStatus"]);
        $statement->execute();

        $customerStatus = "SELECT * FROM customer_status";
        $statement = $db->prepare($customerStatus);
        $statement->execute();
        $status = $statement->fetchAll();
        echo json_encode($status);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["status_Id"]) && !empty($_GET["status_Id"])) {
    try {
        $customerStatus = "SELECT * FROM customer_status where id = :id";
        $statement = $db->prepare($customerStatus);
        $statement->bindParam(":id", $_GET["status_Id"]);
        $statement->execute();
        $status = $statement->fetch();
        echo json_encode($status);
    } catch (Exception $e) {
        
    }
} else if (isset($_GET["statusId"]) && !empty($_GET["statusId"])) {
    try {
        $deleteStatus = "DELETE FROM customer_status WHERE id = :id";
        $statement = $db->prepare($deleteStatus);
        $statement->bindParam(":id", $_GET["statusId"]);
        $statement->execute();

        $customerStatus = "SELECT * FROM customer_status";
        $statement = $db->prepare($customerStatus);
        $statement->execute();
        $status = $statement->fetchAll();
        echo json_encode($status);
    } catch (Exception $e) {
        
    }
} else if (isset($_GET["eidAddto"]) && !empty($_GET["eidAddto"]) && isset($_GET["editAddto"]) && !empty($_GET["editAddto"])) {
    try {
        $updateaddto = "UPDATE service_add_to SET add_to_type = :status WHERE add_to_id = :id";
        $statement = $db->prepare($updateaddto);
        $statement->bindParam(":id", $_GET["eidAddto"]);
        $statement->bindParam(":status", $_GET["editAddto"]);
        $statement->execute();

        $addTos = "SELECT * FROM service_add_to";
        $statement = $db->prepare($addTos);
        $statement->execute();
        $addto = $statement->fetchAll();
        echo json_encode($addto);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["newAddto"]) && !empty($_GET["newAddto"])) {
    try {
        $sql = "INSERT INTO service_add_to (add_to_type) VALUES (:newStatus)";
        $statement = $db->prepare($sql);
        $statement->bindParam(":newStatus", $_GET["newAddto"]);
        $statement->execute();

        $addTos = "SELECT * FROM service_add_to";
        $statement = $db->prepare($addTos);
        $statement->execute();
        $addto = $statement->fetchAll();
        echo json_encode($addto);
    } catch (Exception $exc) {
        echo $exc->getTraceAsString();
    }
} else if (isset($_GET["addto_Id"]) && !empty($_GET["addto_Id"])) {
    try {
        $addto = "SELECT * FROM service_add_to where add_to_id = :id";
        $statement = $db->prepare($addto);
        $statement->bindParam(":id", $_GET["addto_Id"]);
        $statement->execute();
        $status = $statement->fetch();
        echo json_encode($status);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["addtosId"]) && !empty($_GET["addtosId"])) {
    try {
        $deleteaddto = "DELETE FROM service_add_to WHERE add_to_id = :id";
        $statement = $db->prepare($deleteaddto);
        $statement->bindParam(":id", $_GET["addtosId"]);
        $statement->execute();

        $addTos = "SELECT * FROM service_add_to";
        $statement = $db->prepare($addTos);
        $statement->execute();
        $addto = $statement->fetchAll();
        echo json_encode($addto);
    } catch (Exception $e) {
        
    }
} else if (isset($_GET["updateCertNo"]) && !empty($_GET["updateCertNo"]) && isset($_GET["firm_id"]) && !empty($_GET["firm_id"])) {
    try {
        $certnoupdate = "UPDATE consulting_firm SET certification_no = :no WHERE firm_id = :id";
        $statement = $db->prepare($certnoupdate);
        $statement->bindParam(":no", $_GET["updateCertNo"]);
        $statement->bindParam(":id", $_GET["firm_id"]);
        $statement->execute();

        $sql = "SELECT certification_no, firm_id FROM consulting_firm";
        $statement = $db->prepare($sql);
        $statement->execute();
        $no = $statement->fetch();
        echo json_encode($no);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["handler_Id"]) && !empty($_GET["handler_Id"])) {
    $handler = "SELECT * FROM handler where id = :id";
    $statement = $db->prepare($handler);
    $statement->bindParam(":id", $_GET["handler_Id"]);
    $statement->execute();
    $handlerList = $statement->fetch();
    echo json_encode($handlerList);
} else if (isset($_GET["idHandler"]) && !empty($_GET["idHandler"]) && isset($_GET["newHandler"]) && !empty($_GET["newHandler"])) {
    try {
        $update = "UPDATE `handler` SET `handler_name`= :handler WHERE `id` = :id";
        $statement = $db->prepare($update);
        $statement->bindParam(":id", $_GET["idHandler"]);
        $statement->bindParam(":handler", $_GET["newHandler"]);
        $statement->execute();
        
        $handler = "SELECT * FROM handler";
        $statement = $db->prepare($handler);
        $statement->execute();
        $handlerList = $statement->fetchAll();
        echo json_encode($handlerList);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["newHandler"]) && !empty($_GET["newHandler"])) {
    try {
        $createHandler = "INSERT INTO handler (handler_name) VALUES (:handler)";
        $statement = $db->prepare($createHandler);
        $statement->bindParam(":handler", $_GET["newHandler"]);
        $statement->execute();

        $handler = "SELECT * FROM handler";
        $statement = $db->prepare($handler);
        $statement->execute();
        $handlerList = $statement->fetchAll();
        echo json_encode($handlerList);
    } catch (Exception $ex) {
        
    }
} else if(isset ($_GET["handleId"]) && !empty ($_GET["handleId"])){
    try {
        $deleteHandler = "DELETE FROM `handler` WHERE `id` = :id";
        $statement = $db->prepare($deleteHandler);
        $statement->bindParam(":id",$_GET["handleId"]);
        $statement->execute();
        
        $handler = "SELECT * FROM handler";
        $statement = $db->prepare($handler);
        $statement->execute();
        $handlerList = $statement->fetchAll();
        echo json_encode($handlerList);
    } catch (Exception $ex) {
        
    }
}

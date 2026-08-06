<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST["certificationComment"]) && !empty($_POST["certificationComment"])) {
    $sql = "UPDATE `payment_system` SET `comment`=:comment WHERE `payment_id` = :row";
    $statement = $db->prepare($sql);
    $statement->bindParam(":comment", $_POST["certificationComment"]);
    $statement->bindParam(":row", $_POST["cbId"]);
    $statement->execute();
    header("Location: customerAccount.php?customerid=" . $_POST['cbCustomer']);
} else if (isset($_GET["certificationComment"]) && !empty($_GET["certificationComment"])) {
    $sql = "UPDATE `payment_system` SET `comment`=:comment WHERE `payment_id` = :row";
    $statement = $db->prepare($sql);
    $statement->bindParam(":comment", $_GET["certificationComment"]);
    $statement->bindParam(":row", $_GET["cbId"]);
    $statement->execute();
    echo "success";
} else if (isset($_POST['rowid']) && !empty($_POST['rowid'])) {
//    echo $_POST['rowid']; die;
    try {
        $sql = "UPDATE `ca_service_dates` SET `is_deleted`= 'Y', sent_date = '0000-00-00' WHERE `action_id` = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_POST['rowid']);
        $statement->execute();
        if (isset($_POST["page"]) && !empty($_POST["page"])) {
            header("Location: sentintimation.php");
        } else {
            header("Location: intimationLetter.php");
        }
    } catch (Exception $ex) {
        
    }
} else if (isset($_POST["rowidac"]) && !empty($_POST["rowidac"]) && isset($_POST["customer"]) && !empty($_POST["customer"])) {
    try {
        $dateRowSql = "SELECT service_id, action, due_date FROM `ca_service_dates` WHERE `action_id` = :id LIMIT 1";
        $statement = $db->prepare($dateRowSql);
        $statement->bindParam(":id", $_POST['rowidac']);
        $statement->execute();
        $dateRow = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$dateRow) {
            if (isset($_POST["ajax"]) && $_POST["ajax"] == "1") {
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'error', 'message' => 'Letter/action row not found.'));
                exit;
            }
            header("Location: customerAccount.php?customerid=" . $_POST['customer']);
            exit;
        }

        $sql = "UPDATE `ca_service_dates`
                SET `is_deleted`= 'Y', sent_date = '0000-00-00'
                WHERE `service_id` = :service_id
                AND `action` = :action
                AND `due_date` = :due_date";
        $statement = $db->prepare($sql);
        $statement->bindParam(":service_id", $dateRow['service_id']);
        $statement->bindParam(":action", $dateRow['action']);
        $statement->bindParam(":due_date", $dateRow['due_date']);
        $statement->execute();

        if (isset($_POST["ajax"]) && $_POST["ajax"] == "1") {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'success'));
            exit;
        }

        header("Location: customerAccount.php?customerid=" . $_POST['customer']);
    } catch (Exception $ex) {
        if (isset($_POST["ajax"]) && $_POST["ajax"] == "1") {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'message' => 'Unable to delete this letter/action row.'));
            exit;
        }
    }
} else if (isset($_GET["customerid"]) && !empty($_GET["customerid"]) && isset($_GET["customerstatus"]) && !empty($_GET["customerstatus"])) {
    try {
        include_once './include/customer_feature_flags.php';
        if (!function_exists('aitfCustomerFeatureIsEnabled')) {
            // fallback if not loaded
            $manualEnabled = true;
        } else {
            $manualEnabled = aitfCustomerFeatureIsEnabled($db, 'customer_manual_status_change_enabled');
        }
        
        include_once './include/rbac_helper.php';
        if (!$manualEnabled && !Utitlity::checkForDirector()) {
            echo "error_not_allowed: you are not allowd to change status , director needs to allow this feature";
            exit;
        }

        $sql = "UPDATE customer_info SET audit_status = :status WHERE id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET["customerid"]);
        $statement->bindParam(":status", $_GET["customerstatus"]);
        $statement->execute();
        echo "success";
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["servietype"]) && !empty($_GET["servietype"]) && isset($_GET["serviceid"]) && !empty($_GET["serviceid"])) {
    try {
        if ($_GET["servietype"] == "CA") {
            try {
                $sql = "UPDATE service_type_ca SET audit_nc_II = :nc where ca_id = :id";
                $statement = $db->prepare($sql);
                $statement->bindParam(":id", $_GET["serviceid"]);
                $statement->bindParam(":nc", $_GET["nc"]);
                $statement->execute();
                echo "success";
            } catch (Exception $ex) {
                
            }
        } else if ($_GET["servietype"] == "SA-1") {
            try {
                $sql = "UPDATE service_type_sai SET audit_evidance_sai = :nc where sai_id = :id";
                $statement = $db->prepare($sql);
                $statement->bindParam(":id", $_GET["serviceid"]);
                $statement->bindParam(":nc", $_GET["nc"]);
                $statement->execute();
                echo "success";
            } catch (Exception $ex) {
                
            }
        } else if ($_GET["servietype"] == "SA-2") {
            try {
                $sql = "UPDATE service_type_saii SET audit_evidences_saii = :nc where saii_id = :id";
                $statement = $db->prepare($sql);
                $statement->bindParam(":id", $_GET["serviceid"]);
                $statement->bindParam(":nc", $_GET["nc"]);
                $statement->execute();
                echo "success";
            } catch (Exception $ex) {
                
            }
        } else if ($_GET["servietype"] == "RC") {
            try {
                $sql = "UPDATE `service_type_rc` SET rc_audit_evidence = :nc WHERE rc_id = :id";
                $statement = $db->prepare($sql);
                $statement->bindParam(":id", $_GET["serviceid"]);
                $statement->bindParam(":nc", $_GET["nc"]);
                $statement->execute();
                echo "success";
            } catch (Exception $ex) {
                
            }
        } else if ($_GET["servietype"] == "TA") {
            $sql = "UPDATE service_type_ta SET signed_notification = :nc  WHERE ta_id = :id";
            $statement = $db->prepare($sql);
            $statement->bindParam(":id", $_GET["serviceid"]);
            $statement->bindParam(":nc", $_GET["nc"]);
            $statement->execute();
            echo "success";
        }
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["custid"]) && !empty($_GET["custid"])) {
    try {
        $customer = "SELECT * FROM `customer_info` where id = :id";
        $statement = $db->prepare($customer);
        $statement->bindParam(":id", $_GET["custid"]);
        $statement->execute();
        $row = $statement->fetch();
        echo json_encode($row);
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["svcid"]) && !empty($_GET["svcid"]) && isset($_GET["servictype"]) && !empty($_GET["servictype"])) {
    try {
        $sql = "UPDATE service_type_ta SET release_schedule = :nc  WHERE ta_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET["svcid"]);
        $statement->bindParam(":nc", $_GET["nc"]);
        $statement->execute();
        echo "success";
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["svc_id"]) && !empty($_GET["svc_id"]) && isset($_GET["svctype"]) && !empty($_GET["svctype"])) {
    try {
        $sql = "UPDATE service_type_ta SET audit_report = :nc  WHERE ta_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET["svc_id"]);
        $statement->bindParam(":nc", $_GET["nc"]);
        $statement->execute();
        echo "success";
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["svc_type"]) && !empty($_GET["svc_type"]) && isset($_GET["svc_id"]) && !empty($_GET["svc_id"])) {
    try {
        $sql = "UPDATE service_type_sp SET sp_signed_noti = :nc WHERE sp_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET["svc_id"]);
        $statement->bindParam(":nc", $_GET["nc"]);
        $statement->execute();
        echo "success";
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["srvc_type"]) && !empty($_GET["srvc_type"]) && isset($_GET["srvc_id"]) && !empty($_GET["srvc_id"])) {
    try {
        $sql = "UPDATE service_type_sp SET sp_signed_copy = :nc WHERE sp_id = :id";
        $statement = $db->prepare($sql);
        $statement->bindParam(":id", $_GET["srvc_id"]);
        $statement->bindParam(":nc", $_GET["nc"]);
        $statement->execute();
        echo "success";
    } catch (Exception $ex) {
        
    }
} else if (isset($_GET["svccert_type"]) && !empty($_GET["svccert_type"]) && isset($_GET["svccert_id"]) && !empty($_GET["svccert_id"])) {
    try {
        if (isset($_GET["cert"]) && !empty($_GET["cert"])) {            
            $sql = "DELETE FROM `certification_aggrement` WHERE `service` = :id";
            $statement = $db->prepare($sql);
            $statement->bindParam(":id", $_GET["svccert_id"]);
            $statement->execute();
            echo "success";
        } else {
            $sql = "UPDATE service_type_sp SET certificate_option = :nc WHERE sp_id = :id";
            $statement = $db->prepare($sql);
            $statement->bindParam(":id", $_GET["svccert_id"]);
            $statement->bindParam(":nc", $_GET["nc"]);
            $statement->execute();
            echo "success";
        }
    } catch (Exception $ex) {
        
    }
} else if(isset ($_GET["svcType"]) && !empty($_GET["svcType"]) && isset ($_GET["svcId"]) && !empty ($_GET["svcId"])){
    try {
        if ($_GET["svcType"] == "CA") {
            try {
                $sql = "UPDATE service_type_ca SET customer_satisfaction = :nc where ca_id = :id";
                $statement = $db->prepare($sql);
                $statement->bindParam(":id", $_GET["svcId"]);
                $statement->bindParam(":nc", $_GET["nc"]);
                $statement->execute();
                echo "success";
            } catch (Exception $ex) {
                
            }
        } else if ($_GET["svcType"] == "SA-I") {
            try {
                $sql = "UPDATE service_type_sai SET customer_review_sai = :nc where sai_id = :id";
                $statement = $db->prepare($sql);
                $statement->bindParam(":id", $_GET["svcId"]);
                $statement->bindParam(":nc", $_GET["nc"]);
                $statement->execute();
                echo "success";
            } catch (Exception $ex) {
                
            }
        } else if ($_GET["svcType"] == "SA-II") {
            try {
                $sql = "UPDATE service_type_saii SET customer_review_saii = :nc where saii_id = :id";
                $statement = $db->prepare($sql);
                $statement->bindParam(":id", $_GET["svcId"]);
                $statement->bindParam(":nc", $_GET["nc"]);
                $statement->execute();
                echo "success";
            } catch (Exception $ex) {
                
            }
        } else if ($_GET["svcType"] == "RC") {
            try {
                $sql = "UPDATE `service_type_rc` SET rc_customer_review = :nc WHERE rc_id = :id";
                $statement = $db->prepare($sql);
                $statement->bindParam(":id", $_GET["svcId"]);
                $statement->bindParam(":nc", $_GET["nc"]);
                $statement->execute();
                echo "success";
            } catch (Exception $ex) {
                
            }
        }
    } catch (Exception $ex) {
        
    }
}

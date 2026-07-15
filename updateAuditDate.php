<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

/**
 * Normalize incoming audit date strings to database format (Y-m-d).
 *
 * The UI uses jQuery UI datepicker with format `dd.mm.yy`, so we must
 * explicitly handle both 2-digit and 4-digit year variants in *day/month/year*
 * order to avoid PHP/strtotime guessing a US-style month/day/year and
 * flipping values (e.g. 10.01.26 -> 01.10.2026).
 */
function normalizeAuditDate($rawDate)
{
    $rawDate = trim($rawDate);
    if ($rawDate === '') {
        return null;
    }

    // Try a set of explicit, strict formats first
    $formats = array(
        'd.m.Y', // 10.01.2026
        'd.m.y', // 10.01.26
        'd-m-Y', // 10-01-2026
        'd-m-y', // 10-01-26
        'd/m/Y', // 10/01/2026
        'd/m/y', // 10/01/26
        'Y-m-d', // 2026-01-10 (already DB-style)
    );

    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $rawDate);
        if ($dt instanceof DateTime && $dt->format($format) === $rawDate) {
            return $dt->format('Y-m-d');
        }
    }

    // Fallback to strtotime for any other acceptable formats
    $ts = strtotime($rawDate);
    if ($ts !== false) {
        return date('Y-m-d', $ts);
    }

    return null;
}

if (isset($_GET['ID']) && !empty($_GET['ID']) && isset($_GET['column_name']) && !empty($_GET['column_name']) && isset($_GET['tabel_type']) && !empty($_GET['tabel_type']) && isset($_GET['column_value']) && !empty($_GET['column_value']) && isset($_GET['service_id']) && !empty($_GET['service_id'])) {
    try {
        if($_GET["tabel_type"] == "caService"){
            $auditDate = normalizeAuditDate($_GET['column_value']);
            $update = "UPDATE service_type_ca SET audit_date = :date WHERE  service = :service";
            $statement = $db->prepare($update);
            $statement->bindParam(":date", $auditDate);
            $statement->bindParam(":service", $_GET['service_id']);
            $statement->execute();
            
            $updateSvc = "UPDATE service SET audit_date = :date WHERE service_id = :service";
            $statement = $db->prepare($updateSvc);
            $statement->bindParam(":date", $auditDate);
            $statement->bindParam(":service", $_GET['service_id']);
            $statement->execute();
            echo json_encode("success") ;
        } 
        if($_GET["tabel_type"] == "saiService"){
            $auditDate = normalizeAuditDate($_GET['column_value']);
            $update = "UPDATE service_type_sai SET audit_date = :date WHERE  service = :service";
            $statement = $db->prepare($update);
            $statement->bindParam(":date", $auditDate);
            $statement->bindParam(":service", $_GET['service_id']);
            $statement->execute();
            
            $updateSvc = "UPDATE service SET audit_date = :date WHERE service_id = :service";
            $statement = $db->prepare($updateSvc);
            $statement->bindParam(":date", $auditDate);
            $statement->bindParam(":service", $_GET['service_id']);
            $statement->execute();
            echo json_encode("success") ;
        }
        if($_GET["tabel_type"] == "saiiService"){
            $auditDate = normalizeAuditDate($_GET['column_value']);
            $update = "UPDATE service_type_saii SET audit_date = :date WHERE  service = :service";
            $statement = $db->prepare($update);
            $statement->bindParam(":date", $auditDate);
            $statement->bindParam(":service", $_GET['service_id']);
            $statement->execute();
            
            $updateSvc = "UPDATE service SET audit_date = :date WHERE service_id = :service";
            $statement = $db->prepare($updateSvc);
            $statement->bindParam(":date", $auditDate);
            $statement->bindParam(":service", $_GET['service_id']);
            $statement->execute();
            echo json_encode("success") ;
        }
        if($_GET["tabel_type"] == "rcService"){
            $auditDate = normalizeAuditDate($_GET['column_value']);
            $update = "UPDATE service_type_rc SET audit_date = :date WHERE  service = :service";
            $statement = $db->prepare($update);
            $statement->bindParam(":date", $auditDate);
            $statement->bindParam(":service", $_GET['service_id']);
            $statement->execute();
            
            $updateSvc = "UPDATE service SET audit_date = :date WHERE service_id = :service";
            $statement = $db->prepare($updateSvc);
            $statement->bindParam(":date", $auditDate);
            $statement->bindParam(":service", $_GET['service_id']);
            $statement->execute();
            echo json_encode("success") ;
        }
        if($_GET["tabel_type"] == "spaService"){
            $auditDate = normalizeAuditDate($_GET['column_value']);
            $update = "UPDATE service_type_sp SET audit_date = :date WHERE  service = :service";
            $statement = $db->prepare($update);
            $statement->bindParam(":date", $auditDate);
            $statement->bindParam(":service", $_GET['service_id']);
            $statement->execute();
            
            $updateSvc = "UPDATE service SET audit_date = :date WHERE service_id = :service";
            $statement = $db->prepare($updateSvc);
            $statement->bindParam(":date", $auditDate);
            $statement->bindParam(":service", $_GET['service_id']);
            $statement->execute();
            echo json_encode("success") ;
        }
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}

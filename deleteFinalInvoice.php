<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once './dbConnect.php';

if (isset($_POST["finalId"]) && !empty($_POST["finalId"])) {
    try {
        $deleteFinal = "DELETE FROM invoice_details WHERE `invoice_id` = :id";
//        $deleteProformaInvoice = "UPDATE `invoice_details` SET `is_deleted`= 'Yes' WHERE `invoice_id` = :id";
        $statement = $db->prepare($deleteFinal);
        $statement->bindParam(":id", $_POST["finalId"]);
        $statement->execute();
    } catch (Exception $ex) {
        
    } 


    // this section is for mapping 
    header("location:invoicePage.php");
} else {
    $req = array_merge($_GET, $_POST);
    if (isset($req['id']) && !empty($req['id']) && isset($req['columnName']) && !empty($req['columnName']) && isset($req['columnValue'])) {
        try {
            $allowedColumns = [
                'comment' => 'comment',
                'certification_stage_date' => 'certification_stage_date',
                'add_to' => 'add_to',
                'credit_date_booking' => 'credit_date_booking',
                'tan_of_deductor' => 'tan_of_deductor',
                'tds_received' => 'tds_received',
                'tds_received_date' => 'tds_received_date'
            ];

            $colKey = trim($req['columnName']);
            if (isset($allowedColumns[$colKey])) {
                $column = $allowedColumns[$colKey];
                $dataVal = trim((string)$req['columnValue']);

                if ($column === 'certification_stage_date' || $column === 'tds_received_date') {
                    if (!empty($dataVal)) {
                        $parsedTime = strtotime(str_replace('/', '-', $dataVal));
                        $dataVal = ($parsedTime !== false && $parsedTime > 0) ? date("Y-m-d", $parsedTime) : NULL;
                    } else {
                        $dataVal = NULL;
                    }
                } elseif ($column === 'tds_received') {
                    if ($dataVal === '') {
                        $dataVal = NULL;
                    } else {
                        $cleanVal = max(0.0, (float)str_replace(',', '', $dataVal));
                        $dataVal = number_format($cleanVal, 2, '.', '');
                    }
                }

                $isStatement = !empty($req['is_statement']) && $req['is_statement'] !== '0' && $req['is_statement'] !== 'false';
                if ($isStatement) {
                    $updateSql = "UPDATE statement_payments SET " . $column . " = :updateDeta WHERE id = :customer";
                } else {
                    $updateSql = "UPDATE payment_system SET " . $column . " = :updateDeta WHERE payment_id = :customer";
                }

                $statement = $db->prepare($updateSql);
                $statement->bindParam(':updateDeta', $dataVal);
                $statement->bindParam(':customer', $req['id']);
                $statement->execute();
                echo 'success';
            } else {
                echo 'Invalid column';
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
} 

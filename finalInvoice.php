<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (isset($_POST['invoice']) && !empty($_POST['invoice'])) {
    try {
        $cmonth_invoice = "SELECT count(invoice_id) as id FROM `invoice_details` WHERE (invoice_date BETWEEN '" . date('Y-m-01') . "' AND '" . date('Y-m-t') . "') AND invoice_type = 'final'";
        $statement = $db->prepare($cmonth_invoice);
        $statement->execute();
        $monthinvc = $statement->fetch();
//echo $cmonth_invoice.' -> '.date('Y-m-01').' -> '.' -> '.date('Y-m-t').' -> '.$monthinvc["id"]; die;
        if ($monthinvc["id"] == '0') {
            $proformaPrefix = strtoupper(date("Y/M"));
            $proformaPrefix = 'CD/'.$proformaPrefix;
            $proformainv = '1';
            $updateInvoice = "UPDATE consulting_firm SET final_prefix = :prefix,final_invoice_no = :no WHERE firm_id = '1' ";
            $statement = $db->prepare($updateInvoice);
            $statement->bindParam(':prefix', $proformaPrefix);
            $statement->bindParam(':no', $proformainv);
            $statement->execute();
        }

        $finalinvno = "SELECT * FROM consulting_firm";
        $statement = $db->prepare($finalinvno);
        $statement->execute();
        $finvno = $statement->fetch();

        $finalNo = $finvno["final_invoice_no"];
        $invoiceType = 'final';
        $finalprefix = $finvno["final_prefix"];
        $finalInvoiceDate = date("Y-m-d");
        
        $updateToFinal = "UPDATE invoice_details SET invoice_type = :type,final_prefix = :finalprefix, final_invoice_no=:final_invoice_no, invoice_date = :final_invoice_date WHERE invoice_id = :id ";
        $statement = $db->prepare($updateToFinal);
        $statement->bindParam(':type', $invoiceType);
        $statement->bindParam(':id', $_POST['invoice']);
        $statement->bindParam(':finalprefix', $finalprefix);
        $statement->bindParam(':final_invoice_no', $finalNo);
        $statement->bindParam(':final_invoice_date', $finalInvoiceDate);
        $statement->execute();

        $finalNo = $finalNo + 1;

        $updatefinvoiceno = "UPDATE consulting_firm SET final_invoice_no = :no";
        $statement = $db->prepare($updatefinvoiceno);
        $statement->bindParam(":no", $finalNo);
        $statement->execute();

        header("Location: invoicePage.php");
    } catch (Exception $ex) {
        echo $ex;
    }
}
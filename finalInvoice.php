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
        $invoiceInfoQuery = "SELECT invoice_id, invoice_date FROM invoice_details WHERE invoice_id = :id";
        $statement = $db->prepare($invoiceInfoQuery);
        $statement->bindParam(':id', $_POST['invoice']);
        $statement->execute();
        $invoiceInfo = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$invoiceInfo || empty($invoiceInfo['invoice_date'])) {
            throw new Exception('Invoice date not found for final invoice generation.');
        }

        $finalInvoiceDate = date('Y-m-d', strtotime($invoiceInfo['invoice_date']));
        $finalprefix = 'CD/' . strtoupper(date('Y/M', strtotime($finalInvoiceDate)));

        $monthlyInvoiceNoQuery = "SELECT COALESCE(MAX(CAST(final_invoice_no AS UNSIGNED)), 0) AS max_invoice_no
                FROM invoice_details
                WHERE invoice_type = 'final'
                AND final_prefix = :finalprefix
                AND YEAR(invoice_date) = :invoice_year
                AND MONTH(invoice_date) = :invoice_month";
        $statement = $db->prepare($monthlyInvoiceNoQuery);
        $invoiceYear = date('Y', strtotime($finalInvoiceDate));
        $invoiceMonth = date('m', strtotime($finalInvoiceDate));
        $statement->bindParam(':finalprefix', $finalprefix);
        $statement->bindParam(':invoice_year', $invoiceYear);
        $statement->bindParam(':invoice_month', $invoiceMonth);
        $statement->execute();
        $monthlyInvoiceInfo = $statement->fetch(PDO::FETCH_ASSOC);

        $finalNo = (int) $monthlyInvoiceInfo['max_invoice_no'] + 1;
        $invoiceType = 'final';
        
        $updateToFinal = "UPDATE invoice_details SET invoice_type = :type,final_prefix = :finalprefix, final_invoice_no=:final_invoice_no, invoice_date = :final_invoice_date WHERE invoice_id = :id ";
        $statement = $db->prepare($updateToFinal);
        $statement->bindParam(':type', $invoiceType);
        $statement->bindParam(':id', $_POST['invoice']);
        $statement->bindParam(':finalprefix', $finalprefix);
        $statement->bindParam(':final_invoice_no', $finalNo);
        $statement->bindParam(':final_invoice_date', $finalInvoiceDate);
        $statement->execute();

        $nextFinalNo = $finalNo + 1;

        $updatefinvoiceno = "UPDATE consulting_firm SET final_prefix = :prefix, final_invoice_no = :no WHERE firm_id = '1'";
        $statement = $db->prepare($updatefinvoiceno);
        $statement->bindParam(':prefix', $finalprefix);
        $statement->bindParam(":no", $nextFinalNo);
        $statement->execute();

        header("Location: invoicePage.php");
    } catch (Exception $ex) {
        echo $ex;
    }
}

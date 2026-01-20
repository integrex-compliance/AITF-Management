<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
include_once './dbConnect.php';
require_once('tcpdf_include.php');
date_default_timezone_set('Asia/Kolkata');

function uploadFirmLogo($file) {
    $uploaddir = 'uploads/firmLogo/';

    $uploadfile = $uploaddir . basename($_FILES['firmLogo']['name']);
    if (!file_exists($uploaddir)) {
        mkdir($uploaddir, 0777, true);
    }
    //insert into document table
    if (move_uploaded_file($_FILES['firmLogo']['tmp_name'], $uploadfile)) {
        
    }
    // process file upload - end
    return $uploadfile;
}

function uploadInvoiceSign($file) {
    $uploaddir = 'uploads/invoiceSign/';

    $uploadfile = $uploaddir . basename($_FILES['invoiceSign']['name']);
    if (!file_exists($uploaddir)) {
        mkdir($uploaddir, 0777, true);
    }
    //insert into document table
    if (move_uploaded_file($_FILES['invoiceSign']['tmp_name'], $uploadfile)) {
        
    }
    // process file upload - end
    return $uploadfile;
}

$file = "image";
if (isset($_POST['firmId']) && !empty($_POST['firmId'])) {
    try {
        $services = explode('~', $_POST['firmServicesHidden']);
        $bankAbbr = explode('~', $_POST['firmBankAbbrHidden']);

        if (isset($_FILES ["firmLogo"]) && !( $_FILES['firmLogo']['size'] == 0 ) && isset($_FILES ["firmLogo"] ["name"])) {
            $tf = uploadFirmLogo($file);

            $updateLogo = "UPDATE consulting_firm SET firm_logo = :logo WHERE firm_id = :id";
            $statement = $db->prepare($updateLogo);
            $statement->bindParam(':id', $_POST['firmId']);
            $statement->bindParam(':logo', $tf);
            $statement->execute();
        }
        
        if(isset($_FILES["invoiceSign"]) && !($_FILES["invoiceSign"]["size"] == 0) && isset($_FILES["invoiceSign"]["name"])){
            $tf = uploadInvoiceSign($file);
            
            $updateSign = "UPDATE `consulting_firm` SET `signature`= :sign WHERE `firm_id` = :id";
            $statement = $db->prepare($updateSign);
            $statement->bindParam(':id', $_POST['firmId']);
            $statement->bindParam(':sign', $tf);
            $statement->execute();            
        }

        $firmUpdate = "UPDATE consulting_firm SET firm_name = :name, firm_email = :email, firm_address = :address, "
                . "firm_contact = :contact, svc_code = :svccode, cin_no = :cincode, pan_no = :panno, "
                . "firm_bank = :bankname, bank_address = :bankacaddress, bank_ac_no = :bankacno, bank_ifsc_code = :ifsccode,"
                . "bank_micr_no = :micr_no, profarma_prefix = :profarmaPrefix, firm_invoice_no = :invoiceno, final_prefix = :finalPrefix, final_invoice_no = :firmInvoiceFinal, firm_quotation_no = :quotationno, firm_abbrevation = :abbrevation WHERE firm_id = :id";
        $statement = $db->prepare($firmUpdate);
        $statement->bindParam(':id', $_POST['firmId']);
        $statement->bindParam(':name', $_POST['consultingFirmName']);
        $statement->bindParam(':email', $_POST['consultingFirmEmail']);
        $statement->bindParam(':address', $_POST['firmAddress']);
        $statement->bindParam(':contact', $_POST['firmContact']);
        $statement->bindParam(':svccode', $_POST['svcCode']);
        $statement->bindParam(':cincode', $_POST['cinNo']);
        $statement->bindParam(':panno', $_POST['panNo']);
        $statement->bindParam(':bankname', $_POST['bankName']);
        $statement->bindParam(':bankacaddress', $_POST['bankAddress']);
        $statement->bindParam(':bankacno', $_POST['bankAcNo']);
        $statement->bindParam(':ifsccode', $_POST['ifscCode']);
        $statement->bindParam(':micr_no', $_POST['micrNo']);
        $statement->bindParam(':profarmaPrefix', $_POST['profarmaPrefix']);
        $statement->bindParam(':invoiceno', $_POST['firmInvoice']);
        $statement->bindParam(':finalPrefix', $_POST['finalPrefix']);
        $statement->bindParam(':firmInvoiceFinal', $_POST["firmInvoiceFinal"]);
        $statement->bindParam(':quotationno', $_POST['firmQuotation']);
        $statement->bindParam(':abbrevation', $_POST['firmAbbreviation']);
        $statement->execute();

        $dltAbbrevation = "DELETE FROM firm_abbrevation WHERE firm_id = :id";
        $statement = $db->prepare($dltAbbrevation);
        $statement->bindParam(':id', $_POST['firmId']);
        $statement->execute();

        $dltService = "DELETE FROM firm_services WHERE firm_id = :id";
        $statement = $db->prepare($dltService);
        $statement->bindParam(':id', $_POST['firmId']);
        $statement->execute();

        if (isset($_POST['firmId']) && !empty($_POST['firmId'])) {
            for ($i = 0; $i < count($services); ++$i) {
                if ($services[$i] != "") {
                    $firmService = "INSERT INTO firm_services (firm_id, firm_service) VALUES (:id, :serviceDesc)";
                    $statement = $db->prepare($firmService);
                    $statement->bindParam(':id', $_POST['firmId']);
                    $statement->bindParam(':serviceDesc', $services[$i]);
                    $statement->execute();
                }
            }
            for ($i = 0; $i < count($bankAbbr); ++$i) {
                if ($bankAbbr[$i] != '') {
                    $firmAbbrevation = "INSERT INTO firm_abbrevation ( firm_id, abbrevation) VALUES (:id, :abbr)";
                    $statement = $db->prepare($firmAbbrevation);
                    $statement->bindParam(':id', $_POST['firmId']);
                    $statement->bindParam(':abbr', $bankAbbr[$i]);
                    $statement->execute();
                }
            }
        }
        header("Location: masterCreator.php");
//        echo "success";
    } catch (Exception $ex) {
        echo $ex;
    }
} else {
    if (isset($_POST['consultingFirmName']) && !empty($_POST['consultingFirmName'])) {
        try {
            $tf = uploadFirmLogo($file);

            $services = explode('~', $_POST['firmServicesHidden']);
            $bankAbbr = explode('~', $_POST['firmBankAbbrHidden']);

            $firmAdd = "INSERT INTO consulting_firm (firm_name, firm_email, firm_address, firm_logo, firm_contact, svc_code, cin_no, "
                    . "pan_no, firm_bank, bank_address, bank_ac_no, bank_ifsc_code, bank_micr_no, firm_invoice_no, firm_quotation_no, firm_abbrevation) "
                    . "VALUES (:name, :email, :address, :logo, :contact, :panno, :svccode, :cincode, :bankname, :bankacaddress, :bankacno, "
                    . ":ifsccode, :micr_no, :invoiceno, :quotationno, :abbrevation)";
            $statement = $db->prepare($firmAdd);
            $statement->bindParam(':name', $_POST['consultingFirmName']);
            $statement->bindParam(':email', $_POST['consultingFirmEmail']);
            $statement->bindParam(':address', $_POST['firmAddress']);
            $statement->bindParam(':logo', $tf);
            $statement->bindParam(':contact', $_POST['firmContact']);
            $statement->bindParam(':svccode', $_POST['svcCode']);
            $statement->bindParam(':cincode', $_POST['cinNo']);
            $statement->bindParam(':panno', $_POST['panNo']);
            $statement->bindParam(':bankname', $_POST['bankName']);
            $statement->bindParam(':bankacaddress', $_POST['bankAddress']);
            $statement->bindParam(':bankacno', $_POST['bankAcNo']);
            $statement->bindParam(':ifsccode', $_POST['ifscCode']);
            $statement->bindParam(':micr_no', $_POST['micrNo']);
            $statement->bindParam(':invoiceno', $_POST['firmInvoice']);
            $statement->bindParam(':quotationno', $_POST['firmQuotation']);
            $statement->bindParam(':abbrevation', $_POST['firmAbbreviation']);
            $statement->execute();

            $sql = "SELECT max(LAST_INSERT_ID(firm_id)) as id From consulting_firm";
            $statement = $db->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            $firmid = $row['id'];

            if ($firmid > 0) {
                for ($i = 0; $i < count($services); ++$i) {
                    if ($services[$i] != "") {
                        $firmService = "INSERT INTO firm_services (firm_id, firm_service) VALUES (:id, :serviceDesc)";
                        $statement = $db->prepare($firmService);
                        $statement->bindParam(':id', $firmid);
                        $statement->bindParam(':serviceDesc', $services[$i]);
                        $statement->execute();
                    }
                }
                for ($i = 0; $i < count($bankAbbr); ++$i) {
                    if ($bankAbbr[$i] != '') {
                        $firmAbbrevation = "INSERT INTO firm_abbrevation ( firm_id, abbrevation) VALUES (:id, :abbr)";
                        $statement = $db->prepare($firmAbbrevation);
                        $statement->bindParam(':id', $firmid);
                        $statement->bindParam(':abbr', $bankAbbr[$i]);
                        $statement->execute();
                    }
                }
            }
        header("Location: masterCreator.php");
//        echo "success";
        } catch (Exception $ex) {
            echo $ex;
        }
    }
}
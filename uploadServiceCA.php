<?php

//session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class FileUploadsCA {

    static function uploadFileApplicationForm($file, $cid, $crid) {
        $uploaddir = 'uploads/serviceCA/applicationFrom/'.$cid.'/'.$crid.'/'; 
        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }

    static function uploadFileCertificationAgreement($file, $cid, $crid){
        $uploaddir = 'uploads/serviceCA/certificationAggrement/'.$cid.'/'.$crid.'/';
        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }
    
    static function uploadFileSignedNotification($file, $cid, $crid){
        $uploaddir = 'uploads/serviceCA/signednotification/'.$cid.'/'.$crid.'/';

        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }
    
    static function uploadFileSignedCopy($file, $cid, $crid){
        $uploaddir = 'uploads/serviceCA/signedcopy/'.$cid.'/'.$crid.'/';

        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }
    
    static function uploadFileAuditReport($file, $cid, $crid){
        $uploaddir = 'uploads/serviceCA/auditreport/'.$cid.'/'.$crid.'/';

        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }
    
    static function uploadFileSignedNoti2($file, $cid, $crid){
        $uploaddir = 'uploads/serviceCA/signednoti2/'.$cid.'/'.$crid.'/';

        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }
    
    static function uploadFileAuditorNotes($file, $cid, $crid){
        $uploaddir = 'uploads/serviceCA/auditornotes/'.$cid.'/'.$crid.'/';

        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }
    
    static function uploadFileAuditReport2($file, $cid, $crid){
        $uploaddir = 'uploads/serviceCA/auditreport2/'.$cid.'/'.$crid.'/';

        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }
    
    static function uploadFileAuditEvidences($file, $cid, $crid){
        $uploaddir = 'uploads/serviceCA/auditevidences/'.$cid.'/'.$crid.'/';

        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }
    
    static function uploadFileCertificatePrint($file, $cid, $crid){
        $uploaddir = 'uploads/serviceCA/certificateprint/'.$cid.'/'.$crid.'/';

        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }
    
    static function uploadFileCustomerSatisfaction($file, $cid, $crid){
        $uploaddir = 'uploads/serviceCA/customersatisfaction/'.$cid.'/'.$crid.'/';

        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }
    
    static function uploadFileCustomerReview($file, $cid, $crid){
        $uploaddir = 'uploads/serviceCA/customerreview/'.$cid.'/'.$crid.'/';

        $uploadfile = $uploaddir . basename($_FILES['input-customer-review']['name']);
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }
        //insert into document table
        if (move_uploaded_file($_FILES['input-customer-review']['tmp_name'], $uploadfile)) {
            
        }
        // process file upload - end
        return $uploadfile;
    }
}

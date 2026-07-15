<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

class FileUploadsSP {
    
    static function uploadFileSignedNotiSP($file, $cid, $crid) {
        $uploaddir = 'uploads/serviceSP/signedNotiSP/'.$cid.'/'.$crid.'/';

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
    
    static function uploadFileSignedCopySP($file, $cid, $crid) {
        $uploaddir = 'uploads/serviceSP/signedCopySP/'.$cid.'/'.$crid.'/';

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
    
    static function uploadFileAuditReportSP($file, $cid, $crid) {
        $uploaddir = 'uploads/serviceSP/auditReportSP/'.$cid.'/'.$crid.'/';

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
    
}

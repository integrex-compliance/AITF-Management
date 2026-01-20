<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

class FileUploadsSAii {
    
    static function uploadFileInfoUpdateSaiiForm($file, $cid, $saiiid) {
        $uploaddir = 'uploads/serviceSAII/infoUpdateSaii/'.$cid.'/'.$saiiid.'/';

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
    
    static function uploadFileSignedNotisaii($file, $cid, $saiiid) {
        $uploaddir = 'uploads/serviceSAII/signedNotiSaii/'.$cid.'/'.$saiiid.'/';

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
    
    static function uploadFileSignedCopysaii($file, $cid, $saiiid) {
        $uploaddir = 'uploads/serviceSAII/signedCopySaii/'.$cid.'/'.$saiiid.'/';

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
    
    static function uploadFileAuditReportsaii($file, $cid, $saiiid) {
        $uploaddir = 'uploads/serviceSAII/auditReportsaii/'.$cid.'/'.$saiiid.'/';

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
    
    static function uploadFileAuditorNotessaii($file, $cid, $saiiid) {
        $uploaddir = 'uploads/serviceSAII/auditorNotessaii/'.$cid.'/'.$saiiid.'/';

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
    
    static function uploadFileAuditEvidencessaii($file, $cid, $saiiid) {
        $uploaddir = 'uploads/serviceSAII/auditEvidencessaii/'.$cid.'/'.$saiiid.'/';

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
    
    static function uploadFileCustomerReviewsaii($file, $cid, $saiiid) {
        $uploaddir = 'uploads/serviceSAII/customerReviewsaii/'.$cid.'/'.$saiiid.'/';

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
    
    static function uploadFileCertifcatePrintsaii($file, $cid, $saiiid) {
        $uploaddir = 'uploads/serviceSAII/certificateprintsaii/'.$cid.'/'.$saiiid.'/';

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
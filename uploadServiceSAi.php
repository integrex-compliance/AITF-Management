<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once './dbConnect.php';

class FileUploadsSAi {
    
    static function uploadFileInfoUpdateForm($file, $cid, $saiid) {
        $uploaddir = 'uploads/serviceSAI/infoUpdate/'.$cid.'/'.$saiid.'/';

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
    
    static function uploadFileSignedNoti($file, $cid, $saiid) {
        $uploaddir = 'uploads/serviceSAI/signedNoti/'.$cid.'/'.$saiid.'/';

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
    
    static function uploadFileSignedCopy($file, $cid, $saiid) {
        $uploaddir = 'uploads/serviceSAI/signedCopy/'.$cid.'/'.$saiid.'/';

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
    
    static function uploadFilesaiAuditReport($file, $cid, $saiid) {
        $uploaddir = 'uploads/serviceSAI/saiauditReport/'.$cid.'/'.$saiid.'/';

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
    
    static function uploadFilesaiAuditorNotes($file, $cid, $saiid) {
        $uploaddir = 'uploads/serviceSAI/saiauditorNotes/'.$cid.'/'.$saiid.'/';

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
    
    static function uploadFilesaiAuditsaEvidence($file, $cid, $saiid) {
        $uploaddir = 'uploads/serviceSAI/auditsaEvidence/'.$cid.'/'.$saiid.'/';

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
    
    static function uploadFilesaiCustomersaiReview($file, $cid, $saiid) {
        $uploaddir = 'uploads/serviceSAI/customersaiReview/'.$cid.'/'.$saiid.'/';

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
    
    static function uploadFilesaiCertificatePrint($file, $cid, $saiid) {
        $uploaddir = 'uploads/serviceSAI/certificateprint/'.$cid.'/'.$saiid.'/';

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

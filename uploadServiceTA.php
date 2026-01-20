<?php

//session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class FileUploadsTA {

    static function uploadFileSignedNotification($file, $cid, $crid) {
        $uploaddir = 'uploads/serviceTA/signednotification/' . $cid . '/' . $crid . '/';

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
    
    static function uploadFileSchedule($file, $cid, $crid) {
        $uploaddir = 'uploads/serviceTA/schedule/' . $cid . '/' . $crid . '/';

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

    static function uploadAuditReport($file, $cid, $crid) {
        $uploaddir = 'uploads/serviceTA/auditReport/' . $cid . '/' . $crid . '/';

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
    
    static function uploadReviewReport($file, $cid, $crid) {
        $uploaddir = 'uploads/serviceTA/reviewReport/' . $cid . '/' . $crid . '/';

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

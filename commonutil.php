<?php
//session_start();
include_once './dbConnect.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
date_default_timezone_set('Asia/Kolkata');
class Utitlity {
    static function compress($source, $destination, $quality) {
        $info = getimagesize($source);
        if ($info ['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source);
        elseif ($info ['mime'] == 'image/gif')
            $image = imagecreatefromgif($source);
        elseif ($info ['mime'] == 'image/png')
            $image = imagecreatefrompng($source);
        imagejpeg($image, $destination, $quality);
        return $destination;
    }
}
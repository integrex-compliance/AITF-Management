<?php
//session_start();
include_once './dbConnect.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
date_default_timezone_set('Asia/Kolkata');

// Common date helpers (duplicated from header.php for standalone scripts)
if (!function_exists('simpleDateConversion')) {
    function simpleDateConversion($inputStr)
    {
        if ($inputStr === '') {
            return '';
        }
        $parts = explode('-', $inputStr);
        if (count($parts) > 2) {
            return $parts[2] . '.' . $parts[1] . '.' . $parts[0];
        }
        return $inputStr;
    }
}

if (!function_exists('getFlatDate')) {
    function getFlatDate($inputStr)
    {
        if ($inputStr === '') {
            return '';
        }
        $parts = explode('-', $inputStr);
        if (count($parts) > 2) {
            return $parts[0] . $parts[1] . $parts[2];
        }
        return $inputStr;
    }
}

class Utitlity
{
    static function compress($source, $destination, $quality)
    {
        $info = getimagesize($source);
        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source);
        elseif ($info['mime'] == 'image/gif')
            $image = imagecreatefromgif($source);
        elseif ($info['mime'] == 'image/png')
            $image = imagecreatefrompng($source);
        imagejpeg($image, $destination, $quality);
        return $destination;
    }
}

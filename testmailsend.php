<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$mysite = "SC Sports";
$webmaster = "Webmaster @ SC Sports";
$myemail = 'pallavidevane8@gmail.com';

$subject = "Weekly Sports Updates from $mysite";
$message = "<html><body><br>

Thank you for your interest in our weekly updates. You will now be receiving them by email.<br><br>

If you no longer wish to receive these emails, please send an email to webmaster @ SC sports.com.<br><br>
Have Fun!<br><br>

$webmaster

</body></html>";

$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: SC SPORTS <DONOTREPLY@example.com>' . "\r\n";
$headers .= 'to: SC SPORTS <pallavidevane8@gmail.com>' . "\r\n";

echo mail($email, $subject, $message, $headers);

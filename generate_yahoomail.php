<?php

$result="Yet to Process";
if(isset($_POST['submit'])){
require 'PHPMailer/PHPMailerAutoload.php';

$mail = new PHPMailer();
$msg='Name';
$mail->IsSMTP();
$mail->SMTPDebug = 1;
$mail->Port       = 465;
//$mail->SMTPSecure = 'ssl';
$mail->Host     = 'smtp.mail.yahoo.com';
$mail->SMTPAuth = true; 
$mail->Username = 'harishajabe@yahoo.co.in';
$mail->Password = 'jpknlodwmjslvdaf'; 
$mail->SetFrom('harishajabe@yahoo.co.in', 'harish');

$mail->Subject    = "PHPMailer Test Subject via POP before SMTP, basic";

$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";       

$mail->MsgHTML($msg);

$address = "harishajabe@gmail.com";
$mail->AddAddress($address, "Harish");


if(!$mail->send()){
    $result="Something went wrong. Please try again.";
}
else{
    $result="Thanks for contacting us. we'll get back to you soon!";
}

}
echo $result;
?>
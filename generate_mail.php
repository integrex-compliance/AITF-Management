<?php

$result="Yet to Process";
if(isset($_POST['submit'])){
$phpMailerAutoloadCandidates = array(
    __DIR__ . '/PHPMailer/PHPMailerAutoload.php',
    __DIR__ . '/phpmailer/PHPMailerAutoload.php',
    __DIR__ . '/PHPMailerAutoload.php',
);
$phpMailerAutoloadLoaded = false;
foreach ($phpMailerAutoloadCandidates as $phpMailerAutoloadPath) {
    if (file_exists($phpMailerAutoloadPath)) {
        require_once $phpMailerAutoloadPath;
        $phpMailerAutoloadLoaded = true;
        break;
    }
}
if (!$phpMailerAutoloadLoaded) {
    throw new Exception('PHPMailer autoload not found');
}
$mail = new PHPMailer;
$mail->Host='smtp.gmail.com';
$mail->port=587;
$mail->SMTPAuth=true;
$mail->SMTPSecure='tls';
$mail->Username='aitfmcl@gmail.com';
$mail->Password='amqxvynmfsnvvihi';
$mail->Password='aitf@work11';

$mail->setFrom('aitfmcl@gmail.com');

//$mail->addAddress('vaibhavaajbe@gmail.com');
$mail->addAddress('harishajabe@gmail.com');
$mail->addReplyTo('aitfmcl@gmail.com');

$mail->isHTML(true);
$mail->Subject='Form Submission:  testing cc&reply to';
$mail->Body='<h1 align=center>Name :Test Mail <br> Email: test@gmail.com <br> New Form Submission Test Mail</h1>';

$mail->addCC('aitfintimation@gmail.com');


if(!$mail->send()){
    $result="Something went wrong. Please try again.";
}
else{
    $result="Thanks for contacting us. we'll get back to you soon!";
}

}
echo $result;
?>

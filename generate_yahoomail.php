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

require_once __DIR__ . '/include/env_helper.php';
$mailConfig = aitf_get_mail_config();

$mail = new PHPMailer();
$msg='Name';
$mail->IsSMTP();
$mail->SMTPDebug = 0;
$mail->Port = $mailConfig['port'];
$mail->SMTPSecure = $mailConfig['secure'];
$mail->Host = $mailConfig['host'];
$mail->SMTPAuth = ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== '';
$mail->Username = $mailConfig['username'];
$mail->Password = $mailConfig['password'];
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

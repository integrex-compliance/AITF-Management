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
$mailConfigPath = __DIR__ . '/config/mail_config.php';
$mailConfig = file_exists($mailConfigPath) ? require $mailConfigPath : [];
$mailConfig = array_merge(
    [
        'host' => 'smtpout.secureserver.net',
        'port' => 587,
        'secure' => 'tls',
        'username' => '',
        'password' => ''
    ],
    is_array($mailConfig) ? $mailConfig : []
);
$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = $mailConfig['host'];
$mail->Port = $mailConfig['port'];
$mail->SMTPAuth = ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== '';
$mail->SMTPSecure = $mailConfig['secure'];
$mail->Username = $mailConfig['username'];
$mail->Password = $mailConfig['password'];

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

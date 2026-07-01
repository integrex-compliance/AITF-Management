<?php

require 'PHPMailer/PHPMailerAutoload.php';

$mailConfigPath = __DIR__ . '/config/mail_config.php';
$mailConfig = file_exists($mailConfigPath) ? require $mailConfigPath : [];
$mailConfig = array_merge(
    [
        'host' => 'smtpout.secureserver.net',
        'port' => 587,
        'secure' => 'tls',
        'username' => 'certification@aitf.co.in',
        'password' => 'AITF999@@HasTag#'
    ],
    is_array($mailConfig) ? $mailConfig : []
);
$mail = new PHPMailer(true);
e
class AITFServiceEmailSender {

    function sendEmail($toEmails, $toName, $body, $subject) {
        //function sendEmail($toEmails, $toName , $body, $subject,$ismultiple){		
        $mail = new PHPMailer(true);

        //Send mail using gmail
        if (true) {
            global $mailConfig;
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->SMTPAuth = ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== ''; // enable SMTP authentication
            $mail->SMTPSecure = $mailConfig['secure']; // sets the prefix to the servier
            $mail->Host = $mailConfig['host']; // sets GMAIL as the SMTP server
            $mail->Port = $mailConfig['port']; // set the SMTP port for the GMAIL server
            $mail->Username = $mailConfig['username']; // GMAIL username
            $mail->Password = $mailConfig['password']; // GMAIL password
        }


        

        $mail->AddAddress($toEmails, $toName);
        $mail->From = 'test@gmail.com';
        $mail->FromName = 'AITF Service';
        $mail->Subject = $subject; //"Please join Puzzleme; this is really challanging";
        $mail->Body = $body; //
        $mail->IsHTML(true);

        try {
            $mail->Send();
// 			echo "Success!";
        } catch (Exception $e) {
// 			echo "Fail - " . $mail->ErrorInfo;
            throw new Exception($mail->ErrorInfo);
        }
    }

    function sendAttachEmail($toEmails, $toName, $body, $subject, $attachment) {
        //function sendEmail($toEmails, $toName , $body, $subject,$ismultiple){		
        $mail = new PHPMailer(true);

        //Send mail using gmail
        if (true) {
            global $mailConfig;
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->SMTPAuth = ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== ''; // enable SMTP authentication
            $mail->SMTPSecure = $mailConfig['secure']; // sets the prefix to the servier
            $mail->Host = $mailConfig['host']; // sets GMAIL as the SMTP server
            $mail->Port = $mailConfig['port']; // set the SMTP port for the GMAIL server
            $mail->Username = $mailConfig['username']; // GMAIL username
            $mail->Password = $mailConfig['password']; // GMAIL password
        }
               

        $mail->AddAddress($toEmails, $toName);
        $mail->From = $mailConfig['username']; // Use configured email
        $mail->FromName = 'AITF Certification Services';
        $mail->Subject = $subject; //"Please join Puzzleme; this is really challanging";
        $mail->Body = $body; //
        $file_to_attach = $attachment;
        $mail->AddAttachment($file_to_attach, '');

        $mail->IsHTML(true);

        try {
            $mail->Send();
// 			echo "Success!";
        } catch (Exception $e) {
// 			echo "Fail - " . $mail->ErrorInfo;
            throw new Exception($mail->ErrorInfo);
        }
    }

    function sendMultipleAttachmentsEmail($toEmails, $toName, $body, $subject, $attachments) {
        $mail = new PHPMailer(true);

        //Send mail using gmail
        if (true) {
            global $mailConfig;
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->SMTPAuth = ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== ''; // enable SMTP authentication
            $mail->SMTPSecure = $mailConfig['secure']; // sets the prefix to the servier
            $mail->Host = $mailConfig['host']; // sets GMAIL as the SMTP server
            $mail->Port = $mailConfig['port']; // set the SMTP port for the GMAIL server
            $mail->Username = $mailConfig['username']; // GMAIL username
            $mail->Password = $mailConfig['password']; // GMAIL password
        }
               

        $mail->AddAddress($toEmails, $toName);
        $mail->From = $mailConfig['username']; // Use configured email
        $mail->FromName = 'AITF Certification Services';
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        // Add multiple attachments
        if (is_array($attachments)) {
            foreach ($attachments as $file_to_attach) {
                if (file_exists($file_to_attach)) {
                    $mail->AddAttachment($file_to_attach, '');
                }
            }
        }

        $mail->IsHTML(true);

        try {
            $mail->Send();
        } catch (Exception $e) {
            throw new Exception($mail->ErrorInfo);
        }
    }

    function sendQueryEmail($toEmails, $toName, $body, $subject, $fromName, $ismultiple) {

        $mail = new PHPMailer(true);
        //Send mail using gmail
        if (true) {
            global $mailConfig;
            $mail->IsSMTP(); // telling the class to use SMTP
            //$mail->FromName = 'UPuzzledMe';
            $mail->SMTPAuth = ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== ''; // enable SMTP authentication
            $mail->SMTPSecure = $mailConfig['secure']; // sets the prefix to the servier
            $mail->Host = $mailConfig['host']; // sets GMAIL as the SMTP server
            $mail->Port = $mailConfig['port']; // set the SMTP port for the GMAIL server
            $mail->Username = $mailConfig['username']; // GMAIL username
            $mail->Password = $mailConfig['password']; // GMAIL password
        }

        if ($ismultiple) {
            $mail->AddAddress("recruitgunadhya@gmail.com", "Challenge");
            foreach ($toEmails as $toEmail) {
                $mail->addBCC($toEmail, $toEmail);
            }
        } else {
            $mail->AddAddress($toEmails, $toName);
        }
        //$mail->AddAddress($toEmails, $toName);
        $mail->From = 'test@gmail.com';
        $mail->FromName = 'General Service';
        $mail->Subject = $subject; //"Please join Puzzleme; this is really challanging";
        $mail->Body = $body; //
        $mail->IsHTML(true);

        try {
            $mail->Send();
            // 			echo "Success!";
        } catch (Exception $e) {
            // 			echo "Fail - " . $mail->ErrorInfo;
            throw new Exception($mail->ErrorInfo);
        }
    }

}

?>

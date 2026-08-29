<?php

require 'PHPMailer/PHPMailerAutoload.php';
require_once __DIR__ . '/include/env_helper.php';

$mailConfig = aitf_get_mail_config([
    'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Service')
]);
$mail = new PHPMailer(true);

class AITFServiceEmailSender {

    function sendEmail($toEmails, $toName, $body, $subject) {
        $mail = new PHPMailer(true);
        $mailConfig = aitf_get_mail_config([
            'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Service')
        ]);

        if (true) {
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->SMTPAuth = ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== ''; // enable SMTP authentication
            $mail->SMTPSecure = $mailConfig['secure']; // sets the prefix to the servier
            $mail->Host = $mailConfig['host']; // sets GMAIL as the SMTP server
            $mail->Port = $mailConfig['port']; // set the SMTP port for the GMAIL server
            $mail->Username = $mailConfig['username']; // GMAIL username
            $mail->Password = $mailConfig['password']; // GMAIL password
        }

        $mail->AddAddress($toEmails, $toName);
        $mail->From = $mailConfig['from_email'] ?: $mailConfig['username'];
        $mail->FromName = $mailConfig['from_name'] ?: 'AITF Management Certification';
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->IsHTML(true);

        try {
            $mail->Send();
        } catch (Exception $e) {
            throw new Exception($mail->ErrorInfo);
        }
    }

    function sendAttachEmail($toEmails, $toName, $body, $subject, $attachment) {
        $mail = new PHPMailer(true);
        $mailConfig = aitf_get_mail_config([
            'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Service')
        ]);

        if (true) {
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->SMTPAuth = ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== ''; // enable SMTP authentication
            $mail->SMTPSecure = $mailConfig['secure']; // sets the prefix to the servier
            $mail->Host = $mailConfig['host']; // sets GMAIL as the SMTP server
            $mail->Port = $mailConfig['port']; // set the SMTP port for the GMAIL server
            $mail->Username = $mailConfig['username']; // GMAIL username
            $mail->Password = $mailConfig['password']; // GMAIL password
        }

        $mail->AddAddress($toEmails, $toName);
        $mail->From = $mailConfig['from_email'] ?: $mailConfig['username'];
        $mail->FromName = $mailConfig['from_name'] ?: 'AITF Certification Services';
        $mail->Subject = $subject;
        $mail->Body = $body;
        $file_to_attach = $attachment;
        if (!empty($file_to_attach) && file_exists($file_to_attach)) {
            $mail->AddAttachment($file_to_attach, '');
        }

        $mail->IsHTML(true);

        try {
            $mail->Send();
        } catch (Exception $e) {
            throw new Exception($mail->ErrorInfo);
        }
    }

    function sendMultipleAttachmentsEmail($toEmails, $toName, $body, $subject, $attachments) {
        $mail = new PHPMailer(true);
        $mailConfig = aitf_get_mail_config([
            'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Service')
        ]);

        if (true) {
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->SMTPAuth = ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== ''; // enable SMTP authentication
            $mail->SMTPSecure = $mailConfig['secure']; // sets the prefix to the servier
            $mail->Host = $mailConfig['host']; // sets GMAIL as the SMTP server
            $mail->Port = $mailConfig['port']; // set the SMTP port for the GMAIL server
            $mail->Username = $mailConfig['username']; // GMAIL username
            $mail->Password = $mailConfig['password']; // GMAIL password
        }

        $mail->AddAddress($toEmails, $toName);
        $mail->From = $mailConfig['from_email'] ?: $mailConfig['username'];
        $mail->FromName = $mailConfig['from_name'] ?: 'AITF Certification Services';
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
        $mail->From = $mailConfig['from_email'];
        $mail->FromName = $mailConfig['from_name'];
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

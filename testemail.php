<?php

require 'PHPMailerAutoload.php';
$mail = new PHPMailer(true);

class AITFServiceEmailSender {

    function sendEmail($toEmails, $toName, $body, $subject) {
        //function sendEmail($toEmails, $toName , $body, $subject,$ismultiple){		
        $mail = new PHPMailer(true);

        //Send mail using gmail
        if (true) {
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->SMTPAuth = true; // enable SMTP authentication
            $mail->SMTPSecure = "ssl"; // sets the prefix to the servier
            $mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
            $mail->Port = 465; // set the SMTP port for the GMAIL server
            $mail->Username = "recruitgunadhya@gmail.com"; // GMAIL username
            $mail->Password = "latur1102"; // GMAIL password
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
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->SMTPAuth = true; // enable SMTP authentication
            $mail->SMTPSecure = "ssl"; // sets the prefix to the servier
            $mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
            $mail->Port = 465; // set the SMTP port for the GMAIL server
            $mail->Username = "recruitgunadhya@gmail.com"; // GMAIL username
            $mail->Password = "latur1102"; // GMAIL password
        }
               

        $mail->AddAddress($toEmails, $toName);
        $mail->From = 'test@gmail.com';
        $mail->FromName = 'AITF Service';
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

    function sendQueryEmail($toEmails, $toName, $body, $subject, $fromName, $ismultiple) {

        $mail = new PHPMailer(true);
        //Send mail using gmail
        if (true) {
            $mail->IsSMTP(); // telling the class to use SMTP
            //$mail->FromName = 'UPuzzledMe';
            $mail->SMTPAuth = true; // enable SMTP authentication
            $mail->SMTPSecure = "ssl"; // sets the prefix to the servier
            $mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
            $mail->Port = 465; // set the SMTP port for the GMAIL server
            $mail->Username = "recruitgunadhya@gmail.com"; // GMAIL username
            $mail->Password = "latur1102"; // GMAIL password
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
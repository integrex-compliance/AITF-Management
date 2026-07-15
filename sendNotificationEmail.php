<?php


include 'dbConnect.php';
require_once 'testemail.php';



if (isset($_POST['emailTo']) && !empty($_POST['emailTo'])) {
    try {
        if (isset($_FILES['input-notification']) && !( $_FILES['input-notification']['size'] == 0 ) && isset($_FILES ["input-notification"] ["name"])) {
            $attachment = uploadAttachFile($file);
        }
        $customer = "SELECT * FROM customer_info WHERE id = :id ";
        $statement = $db->prepare($customer);
        $statement->bindParam(':id', $_POST['customer']);
        $statement->execute();
        $customerInfo = $statement->fetch();

        $updateAction = "UPDATE ca_service_dates SET status = 'sent' , sent_date = :date where action_id = :id";
        $statement = $db->prepare($updateAction);
        $statement->bindParam(':id', $_POST['action-id']);
        $statement->bindParam(":date", date("Y-m-d"));
        $statement->execute();
//        echo $_POST['action-id'];
        //           *** email send code ***
        if (isset($_FILES['input-notification']) && !( $_FILES['input-notification']['size'] == 0 ) && isset($_FILES ["input-notification"] ["name"])) {           
            $mailSender = new AITFServiceEmailSender();

            $body = "<html>
                    <body>
                        <h1 style=\"font-size:15px;font-weight:bold;font-family:Arial,Helvetica,sans-serif;line-height:28px;color:#999\">Dear Sir/Madam,</h1><br>
                    </body>
                    </html>";

            $subject = $_POST['emailSub'];

            $mailSender->sendAttachEmail($_POST['emailTo'], "", $body, $subject, $attachment);
        } else {
            $mailSender = new AITFServiceEmailSender();

            $body = "<html>
                    <body>
                        <h1 style=\"font-size:15px;font-weight:bold;font-family:Arial,Helvetica,sans-serif;line-height:28px;color:#999\">Dear Sir/Madam,</h1><br>
                    </body>
                </html>";

            $subject = $_POST['emailSub'];

            $mailSender->sendEmail($_POST['emailTo'], "", $body, $subject);
        }
//           *** email send code ends here ***  
        header("Location: intimationLetter.php");
    } catch (Exception $ex) {
        echo $ex;
    }
}

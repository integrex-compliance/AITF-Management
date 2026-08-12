<?php
require_once __DIR__ . '/rbac_helper.php';

if (!function_exists('aitf_ensure_message_schema')) {
    function aitf_ensure_message_schema(PDO $db)
    {
        $sql = "CREATE TABLE IF NOT EXISTS user_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            message TEXT,
            attachment VARCHAR(255) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_read TINYINT(1) DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $db->exec($sql);
    }
}

if (!function_exists('aitf_send_message_notification')) {
    function aitf_send_message_notification($toEmail, $receiverName)
    {
        $body = '<div style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">'
            . '<div style="background-color: #193048; color: #fff; padding: 20px; text-align: center;">'
            . '<h2 style="margin: 0; font-size: 24px;">New Update Received</h2>'
            . '</div>'
            . '<div style="padding: 30px;">'
            . '<p style="font-size: 16px;">Dear <strong>' . aitf_rbac_escape($receiverName) . '</strong>,</p>'
            . '<p style="font-size: 16px;">You have one new update or message from the Director/Admin.</p>'
            . '<p style="font-size: 16px;">Kindly login to your account to view the message and any attached documents, and to reply.</p>'
            . '<div style="text-align: center; margin: 30px 0;">'
            . '<a href="https://aitf.co.in/login.php" style="background-color: #3b82f6; color: #ffffff; text-decoration: none; padding: 12px 25px; font-size: 16px; border-radius: 5px; display: inline-block; font-weight: bold;">Login to AITF Portal</a>'
            . '</div>'
            . '<hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">'
            . '<p style="font-size: 15px; margin-bottom: 5px;">Regards,</p>'
            . '<p style="font-size: 15px; font-weight: bold; margin-top: 0; color: #193048;">AITF Management Certification Limited</p>'
            . '</div></div>';

        aitf_send_html_mail($toEmail, 'AITF Portal - New Update/Message Received', $body);
    }
}

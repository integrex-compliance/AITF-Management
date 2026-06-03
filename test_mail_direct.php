<?php
// Direct test without AJAX
$_POST = array(
    'customer' => 1194,  // From your console log
    'service' => 200609,  // From your console log
    'audit' => 1811,      // From your console log
    'stage' => 'stage1'   // From your console log
);

include 'sendStageNotificationMail.php';
?>

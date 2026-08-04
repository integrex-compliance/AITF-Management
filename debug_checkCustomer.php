<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$logFile = __DIR__ . '/debug_checkCustomer.log';
$logData = date('Y-m-d H:i:s') . " - GET: " . json_encode($_GET) . "\n";
file_put_contents($logFile, $logData, FILE_APPEND);
?>

<?php
session_start();
$_SESSION['role'] = 'Marketing Head';
$_SESSION['id'] = 1;
$_SESSION['username'] = 'marketingh';
include 'invoicePage.php';
?>

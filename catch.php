<?php
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err !== null) {
        file_put_contents("catch_error.txt", print_r($err, true));
    }
});
$_SESSION["username"] = "admin"; $_SESSION["userId"] = 1; $_SESSION["role"] = "admin";
$_GET["customerid"] = 82;
$_SERVER["REQUEST_URI"] = "/public_html/customerAccount.php?customerid=82";
include "customerAccount.php";


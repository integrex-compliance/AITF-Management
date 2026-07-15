<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
session_start();
include './head.php';
include 'utility.php';

$errorMsg = "";
if (isset($_SESSION['error_message']) && $_SESSION['error_message'] != "") {
    $errorMsg = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <div class="container" style="margin-top: 150px; ">
            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="logocontainer" style="text-align:center">
                        <h1 style="color: white; ">AITF</h1>
                        <!--<img class="qblogo" alt="QuickBooks" src="https://prod-qbo.intuitcdn.net/c37/v1606.959/scripts/harmony/images/logo.png" style="height: 50px" />-->

                        <div class="alert alert-danger">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>   
                            <?php echo $errorMsg; ?>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </body>
</html>
<style>
    body{
        background: #193048;
    }
</style>

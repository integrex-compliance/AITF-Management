<!DOCTYPE html>
<html lang="en">
    <!-- Basic -->
    <head>
        <?php include './headSource.php'; ?>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    <!-- Start Banner -->

    <div class="section margin-top_80">
        <div class="container">
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <div class="full">
                        <div class="heading_main text_align_center">
                            <h2><span>VALIDATION OF </span>CERTIFICATE</h2>
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- end section -->

    <div class="section margin-top_50">
        <div class="container">
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <div class="full">
                        <div class="full">
                            <p>To verify the present status of certification of certified client by AITF enter the name of organisation and certificate number:</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section margin-top_50">
        <div class="container">
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <form action="login.php" method="post">

                        <fieldset>

                            <?php if (!isset($errorC) || $errorC == true) { ?>

                                <label for="name" class="blocklabel">Certified organisation name*</label>
                                <p><input name="name" class="input_bg" type="text" id="name" value="" autocomplete="off" size="50" required></p>


                                <label for="certificateno" class="blocklabel">Certificate Number*</label>
                                <p><input name="cno" class="input_bg" type="text" id="cno" value="" autocomplete="off" size="50" required></p>
                                </br>
                                <p>

                                <div class="clearfix"></div>
                                <input name="Send" type="submit" value="SUBMIT" class="button medium align" id="send"/></p>
                            <?php } ?> 					
                        </fieldset>

                    </form> 
                </div>
            </div>
        </div>
    </div>

    <?php include './footerSource.php'; ?>

</body>
</html>
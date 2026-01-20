<!DOCTYPE html>
<html lang="en">
    <!-- Basic -->
    <style> 

        /* bluring the image */
        .gfg { 
            /*filter: blur(5px);*/ 
            width: 100%; 
        } 

        /* designing title and tag line */
        .logo { 
            text-align:center; 
            position: absolute; 
            font-size: 18px; 
            top: 50px; 
            left: 27%; 
            color: white; 
        } 

        h1:hover { 
            background: -webkit-linear-gradient( green, lime); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        } 
    </style> 
    <head>
        <?php include './headSource.php'; ?>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>

    <!-- Start Banner -->
    <div class="conatiner"> 
        <!-- background image -->
        <img class="gfg" src="aitfImages/information-security-iso-01.jpg"> 
        <!-- title and tag line with button -->
    </div> 
    <!-- End Banner -->

    <!-- section -->
    <div class="section margin-top_50">
        <div class="container">
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <div class="full">
                        <div class="heading_main_training text_align_center">
                            <h2><span>MD-QMS </span>ISO 13485:2016</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end section -->
    
    <?php include './footerSource.php'; ?>
</body>
</html>
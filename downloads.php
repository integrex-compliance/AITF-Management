<?php
//    session_start();
$pageIdentifier = "Home Page";
include 'headersource.php';
?>

<head>
    <title>AITF - Assessment &amp; Institute for Technical Forum</title>
    <meta charset="utf-8">
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <!-- Favicon --> 
    <link rel="shortcut icon" href="imageused/fav.ico">
    <!-- this styles only adds some repairs on idevices  -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- Google fonts - witch you want to use - (rest you can just remove) -->
    
    <style>
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }
        
        .online-form-highlight:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(22, 163, 74, 0.4) !important;
        }
        
        .online-form-highlight a:hover {
            background: #f0fdf4 !important;
            transform: scale(1.05);
        }
    </style>
</head>

<body>

    <div class="clearfix"></div>
    <div class="page_title">

        <div class="container">
            <div class="row">
                <div class="col-sm-6">
                    <!--<div class="leaft_title">-->
                        <h1>Downloads</h1>
                    <!--</div>-->
                </div>
                <div class="col-sm-6">
                    <ul>
                        <li><a href="application.php?msg=".urlencode("")>Application form</a></li>
                        <li style="margin-left: 15px;">
                            <a href="info-updation" style="background: #16a34a; color: white; padding: 5px 12px; border-radius: 6px; font-weight: 600; display: inline-block;">
                                <i class="fa fa-star" style="color: #fbbf24;"></i> Fill Info Update Form (Online)
                            </a>
                        </li>
                    </ul>
                    <div class="" style="text-align: right; color: gray;">
                        <a href="#">Downloads</a>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- end page title --> 


    <!-- Contant
    ======================================= -->

    <div class="container">

        <div class="content_left">
            <ul><li><a href="application.php?msg=".urlencode("")>Application form</a></li></ul>
            <ul><li><a href="transition_g.php?msg=".urlencode("")>Transition guideline of ISO 9001:2015</a></li></ul>

            <ul><li><a href="NC_closure.php?msg=".urlencode("")>NC report & closure</a></li></ul>
            <ul><li><a href="use_of_logo.php?msg=".urlencode("")>Use Of Logo Guideline</a></li></ul>
            <ul><li><a href="info_update.php?msg=".urlencode("")>Information updation form (PDF)</a></li></ul>
            
            <!-- Highlighted Online Form Section (Info Update) -->
            <div class="online-form-highlight" style="background: linear-gradient(135deg, #16a34a, #15803d); padding: 20px; border-radius: 12px; margin: 20px 0; box-shadow: 0 8px 24px rgba(22, 163, 74, 0.3); border: 2px solid #16a34a; transition: all 0.3s ease;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="background: #ffffff; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <i class="fa fa-edit" style="font-size: 24px; color: #16a34a;"></i>
                    </div>
                    <div style="flex: 1;">
                        <h3 style="color: #ffffff; margin: 0 0 8px 0; font-size: 20px; font-weight: 600;">
                            <i class="fa fa-star" style="color: #fbbf24; animation: pulse 2s infinite;"></i>
                            Fill Information Updation Form (ONLINE)
                        </h3>
                        <p style="color: #f0fdf4; margin: 0 0 12px 0; font-size: 14px; line-height: 1.5;">
                            Now you can fill and submit your information updation form online! No need to download PDF. Quick, easy, and instant submission.
                        </p>
                        <a href="info-updation" style="display: inline-block; background: #ffffff; color: #16a34a; padding: 10px 25px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s ease;">
                            <i class="fa fa-arrow-right"></i> Fill Form Online Now
                        </a>
                    </div>
                </div>
            </div>

            <!-- Highlighted NC Closing Form Section -->
            <div class="online-form-highlight" style="background: linear-gradient(135deg, #0284c7, #0369a1); padding: 20px; border-radius: 12px; margin: 20px 0; box-shadow: 0 8px 24px rgba(2, 132, 199, 0.3); border: 2px solid #0284c7; transition: all 0.3s ease;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="background: #ffffff; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <i class="fa fa-file-text" style="font-size: 24px; color: #0284c7;"></i>
                    </div>
                    <div style="flex: 1;">
                        <h3 style="color: #ffffff; margin: 0 0 8px 0; font-size: 20px; font-weight: 600;">
                            <i class="fa fa-star" style="color: #fbbf24; animation: pulse 2s infinite;"></i>
                            Fill NC Closing Form (ONLINE)
                        </h3>
                        <p style="color: #f0f9ff; margin: 0 0 12px 0; font-size: 14px; line-height: 1.5;">
                            Submit your Nonconformance Closer Report with root cause analysis and corrective actions directly through our online portal.
                        </p>
                        <a href="nc_closing" style="display: inline-block; background: #ffffff; color: #0284c7; padding: 10px 25px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s ease;">
                            <i class="fa fa-arrow-right"></i> Fill NC Closing Form Now
                        </a>
                    </div>
                </div>
            </div>
            
            <ul><li><a href="circular_auditing_doc.php?msg=".urlencode("")>Circular regarding auditing documents</a></li></ul>
            <ul><li><a href="guidline_g_1_11.php?msg=".urlencode("")>Granting, Refusing, Maintaining, Renewing, suspending, Restoring, Withdrawing, Expanding and Reducing of scope of certification</a></li></ul>
            <!--<ul><li><a href="auditor_guidline.php?msg=".urlencode("")>Guidline to auditors</a></li></ul>-->
            <div class="clearfix mar_top2"></div>
        </div>
    </div>
    <!-- Footer
    ======================================= -->

    <div class="container">
        <div id="footer">
            <div class="down_arrow"></div>

            <div class="one_third">
                <div class="footer_logo">AITF<i></i></div>
                <ul class="address-liste">
                    <li class="icon1">Devi Indrayani Wing B 26, Gat No. 382+3+4/1, Near IT Park,Talawade, Pune - 411 062,<br /> Maharashtra, India</li>
                    <li class="icon2">+91-9422011836</li>
                    <li class="icon4"><a href="mailto:aitfmcl@gmail.com">aitfmcl@gmail.com</a></li>
                </ul>
            </div><!-- end section -->

            <div class="one_third">
                <div class="socials_main">

                    <h4 class="title">fallow<br />us</h4>

                    <ul class="socials_home_links">
                        <li class="one"><a href="#"></a></li>
                        <li class="two"><a href="#"></a></li>
                        <li class="three"><a href="#"></a></li>
                        <li class="four"><a href="#"></a></li>
                        <li class="five"><a href="#"></a></li>
                        <li class="six"><a href="#"></a></li>
                        <li class="seven"><a href="#"></a></li>
                    </ul>

                    <div class="clearfix"></div>
                    <div class="mar_top12"></div>


                    <div id="flickr_badge_wrapper">

                    </div>

                </div>

            </div><!-- end section -->

            <div class="one_third last">

                <div class="newsletter">
                    <h6>newsletter signup</h6>
                    Provide your email to receive latest information
                    <div class="clearfix mar_top1"></div>
                    <form method="get" action="home.html">            
                        <input class="enter_email_input" name="samplees" id="samplees" value="Please enter your email..." onFocus="if (this.value == 'Please enter your email...') {
                                                    this.value = '';
                                                }" onBlur="if (this.value == '') {
                                                            this.value = 'Please enter your email...';
                                                        }" type="text">			
                        <input name="" value="subscribe" class="input_submit" type="submit">
                    </form>          
                </div><!-- end newsletter sign up -->

                <div class="clearfix mar_top2"></div>

                <div class="copyright">

                    Copyright &copy 2014 AITF. All rights reserved<br />
                    Terms of Service | Privacy Policy<br/>
                    Website Developed by AITF Management certification
                </div><!-- end copyrights -->


            </div>

        </div>

        <div class="footer_shadow"></div>


    </div>

    <div class="site_wrapper">
        <div class="container">
            <div class="footer_link">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About ISO</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="application.php?msg=".urlencode("")>Application form</a></li>
            </div>
        </div>
    </div>
    <div id="style-selector">
        <div class="style-selector-wrapper">
            <span class="title">Choose Theme Options</span>
            <div>        
                <span class="title-sub"></span>

                <span class="title-sub2">Predefined Color Skins</span>

                <ul class="styles">     
                    <li><a href="#" onClick="setActiveStyleSheet('default'); return false;"><span class="pre-color-skin1"></span></a></li>
                    <li><a href="#" onClick="setActiveStyleSheet('lightblue'); return false;"><span class="pre-color-skin2"></span></a></li>
                    <li><a href="#" onClick="setActiveStyleSheet('lightgreen'); return false;"><span class="pre-color-skin3"></span></a></li>
                    <li><a href="#" onClick="setActiveStyleSheet('blue'); return false;"><span class="pre-color-skin4"></span></a></li>
                    <li><a href="#" onClick="setActiveStyleSheet('green'); return false;"><span class="pre-color-skin5"></span></a></li>
                    <li><a href="#" onClick="setActiveStyleSheet('red'); return false;"><span class="pre-color-skin6"></span></a></li>
                    <li><a href="#" onClick="setActiveStyleSheet('cyan'); return false;"><span class="pre-color-skin7"></span></a></li>
                    <li><a href="#" onClick="setActiveStyleSheet('purple'); return false;"><span class="pre-color-skin8"></span></a></li>
                    <li><a href="#" onClick="setActiveStyleSheet('yellow'); return false;"><span class="pre-color-skin9"></span></a></li>
                    <li><a href="#" onClick="setActiveStyleSheet('brown'); return false;"><span class="pre-color-skin10"></span></a></li>
                </ul>
                <!-- end Predefined Color Skins --> 

                <a href="#" class="close icon-chevron-right"></a>  

            </div>
        </div>
    </div><!-- end style switcher -->

    <a href="#" class="scrollup">Scroll</a><!-- end scroll to top of the page-->

    <script type="text/javascript">

        jQuery(document).ready(function () {
            jQuery('#mycarousel').jcarousel();
        });

        jQuery(document).ready(function () {
            jQuery('#mycarouseltwo').jcarousel();
        });

    </script>

    <!-- scroll up -->
    <script type="text/javascript">
        $(document).ready(function () {

            $(window).scroll(function () {
                if ($(this).scrollTop() > 100) {
                    $('.scrollup').fadeIn();
                } else {
                    $('.scrollup').fadeOut();
                }
            });

            $('.scrollup').click(function () {
                $("html, body").animate({scrollTop: 0}, 500);
                return false;
            });

        });
    </script>

<!--<script type="text/javascript">

                var tpj=jQuery;
                tpj.noConflict();

                tpj(document).ready(function() {

                if (tpj.fn.cssOriginal!=undefined)
                        tpj.fn.css = tpj.fn.cssOriginal;

                        tpj('.fullwidthbanner').revolution(
                                {
                                        delay:9000,
                                        startwidth:1000,
                                        startheight:570,

                                        onHoverStop:"on",						// Stop Banner Timet at Hover on Slide on/off

                                        thumbWidth:100,							// Thumb With and Height and Amount (only if navigation Tyope set to thumb !)
                                        thumbHeight:50,
                                        thumbAmount:3,

                                        hideThumbs:200,
                                        navigationType:"none",				// bullet, thumb, none
                                        navigationArrows:"solo",				// nexttobullets, solo (old name verticalcentered), none

                                        navigationStyle:"round",				// round,square,navbar,round-old,square-old,navbar-old, or any from the list in the docu (choose between 50+ different item), custom


                                        navigationHAlign:"right",				// Vertical Align top,center,bottom
                                        navigationVAlign:"bottom",					// Horizontal Align left,center,right
                                        navigationHOffset:50,
                                        navigationVOffset:55,

                                        soloArrowLeftHalign:"let",
                                        soloArrowLeftValign:"center",
                                        soloArrowLeftHOffset:0,
                                        soloArrowLeftVOffset:0,

                                        soloArrowRightHalign:"right",
                                        soloArrowRightValign:"center",
                                        soloArrowRightHOffset:0,
                                        soloArrowRightVOffset:0,

                                        touchenabled:"on",						// Enable Swipe Function : on/off



                                        stopAtSlide:-1,							// Stop Timer if Slide "x" has been Reached. If stopAfterLoops set to 0, then it stops already in the first Loop at slide X which defined. -1 means do not stop at any slide. stopAfterLoops has no sinn in this case.
                                        stopAfterLoops:0,						// Stop Timer if All slides has been played "x" times. IT will stop at THe slide which is defined via stopAtSlide:x, if set to -1 slide never stop automatic



                                        fullWidth:"on",

                                        shadow:0								//0 = no Shadow, 1,2,3 = 3 Different Art of Shadows -  (No Shadow in Fullwidth Version !)

                                });




        });
        </script>-->

</body>

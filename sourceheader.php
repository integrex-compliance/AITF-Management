<?php
//    session_start();
$pageIdentifier = "Home Page";
include 'headsource.php';
?>
<div class="image">
    <a href="index.html"><img src="imageused/webheading.jpg"/></a> 
</div>
<div class="site_wrapper">
    <div class="container">
        <div class="top_section">
            <nav id="access" class="access" role="navigation">
                <div id="menu" class="menu">
                    <ul id="tiny">
                        <li><a href="index.html" >Home</a></li>  
                        <li><a href="about.html">About ISO</a></li>
                        <li><a href="#">Policy</a>
                            <ul>
                                <li><a href="organisation_policy.html">Organisation Policy</a></li>
                            </ul>
                        </li>
                        <li><a href="#">Expected</a>
                            <ul>
                                <li><a href="expected_outcomes.html">Expected Outcomes</a></li>
                            </ul>
                        </li>
                        <li><a href="#">Certification</a>
                            <ul>
                                <li><a href="portfolio-one.html">Certification Process</a></li>
                                <li><a href="criterias.html">Criterias</a></li>
                                <li><a href="complaints_appeals.html">Complaints & Appeals</a></li>
                                <li><a href="impartiality_independency.html">Impartiality</a></li>
                                <li><a href="application.php?msg=".urlencode("")>Application form</a></li>
                            </ul>
                        </li>
                        <li><a href="clientList.php">Clients</a></li>
                        <li><a href="downloads.html">Download</a></li>  
                        <li><a href="contact.php">Contact</a></li>
                        <li><a class="logincall">Login</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
    <!-- end top -->
    <div class="caption lfb small_text"  data-x="20" data-y="474" data-speed="900" data-start="3100" data-easing="easeOutExpo"><a href="#" class="get_button">get started now!</a></div>
</div>

<!-- style switcher -->
<script type="text/javascript" src="js/style-switcher/styleswitcher.js"></script>
<link rel="alternate stylesheet" type="text/css" href="css/colors/lightblue.css" title="lightblue" />
<link rel="alternate stylesheet" type="text/css" href="css/colors/lightgreen.css" title="lightgreen" />
<link rel="alternate stylesheet" type="text/css" href="css/colors/blue.css" title="blue" />
<link rel="alternate stylesheet" type="text/css" href="css/colors/green.css" title="green" />
<link rel="alternate stylesheet" type="text/css" href="css/colors/red.css" title="red" />
<link rel="alternate stylesheet" type="text/css" href="css/colors/cyan.css" title="cyan" />
<link rel="alternate stylesheet" type="text/css" href="css/colors/purple.css" title="purple" />
<link rel="alternate stylesheet" type="text/css" href="css/colors/yellow.css" title="yellow" />
<link rel="alternate stylesheet" type="text/css" href="css/colors/brown.css" title="brown" />
<!-- style switcher -->
<!--<script src="js/style-switcher/jquery-1.js"></script>-->
<script src="js/style-switcher/styleselector.js"></script>
<!-- main menu -->
<script type="text/javascript" src="js/mainmenu/ddsmoothmenu.js"></script>
<!--<script type="text/javascript" src="js/mainmenu/jquery-1.7.1.min.js"></script>-->
<script type="text/javascript" src="js/mainmenu/selectnav.js"></script>

<script type="text/javascript" src="js/mainmenu/scripts.js"></script>
<div id="style-selector">
    <div class="style-selector-wrapper">
        <span class="title">Choose Theme Options</span>
        <div>        
            <span class="title-sub"></span>

            <span class="title-sub2">Predefined Color Skins</span>

            <ul class="styles">     
                <li><a href="#" onClick="setActiveStyleSheet('default'); return false;"><span class="pre-color-skin1"></span></a></li>
                <li><a href="#" onClick="setActiveStyleSheet('lightblue');
                                return false;"><span class="pre-color-skin2"></span></a></li>
                <li><a href="#" onClick="setActiveStyleSheet('lightgreen');
                                return false;"><span class="pre-color-skin3"></span></a></li>
                <li><a href="#" onClick="setActiveStyleSheet('blue'); return false;"><span class="pre-color-skin4"></span></a></li>
                <li><a href="#" onClick="setActiveStyleSheet('green');
                                return false;"><span class="pre-color-skin5"></span></a></li>
                <li><a href="#" onClick="setActiveStyleSheet('red'); return false;"><span class="pre-color-skin6"></span></a></li>
                <li><a href="#" onClick="setActiveStyleSheet('cyan');
                                return false;"><span class="pre-color-skin7"></span></a></li>
                <li><a href="#" onClick="setActiveStyleSheet('purple'); return false;"><span class="pre-color-skin8"></span></a></li>
                <li><a href="#" onClick="setActiveStyleSheet('yellow'); return false;"><span class="pre-color-skin9"></span></a></li>
                <li><a href="#" onClick="setActiveStyleSheet('brown');
                                return false;"><span class="pre-color-skin10"></span></a></li>
            </ul>
            <!-- end Predefined Color Skins --> 

            <a href="#" class="close icon-chevron-right"></a>  

        </div>
    </div>
</div><!-- end style switcher -->

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

        $(".logincall").click(function () {
            $("#login-modal").modal("show");
//                    alert("in");
        });

    });
</script>

<script type="text/javascript">

    var tpj = jQuery;
    tpj.noConflict();

    tpj(document).ready(function () {

        if (tpj.fn.cssOriginal != undefined)
            tpj.fn.css = tpj.fn.cssOriginal;

        tpj('.fullwidthbanner').revolution(
                {
                    delay: 9000,
                    startwidth: 1000,
                    startheight: 570,

                    onHoverStop: "on", // Stop Banner Timet at Hover on Slide on/off

                    thumbWidth: 100, // Thumb With and Height and Amount (only if navigation Tyope set to thumb !)
                    thumbHeight: 50,
                    thumbAmount: 3,

                    hideThumbs: 200,
                    navigationType: "none", // bullet, thumb, none
                    navigationArrows: "solo", // nexttobullets, solo (old name verticalcentered), none

                    navigationStyle: "round", // round,square,navbar,round-old,square-old,navbar-old, or any from the list in the docu (choose between 50+ different item), custom


                    navigationHAlign: "right", // Vertical Align top,center,bottom
                    navigationVAlign: "bottom", // Horizontal Align left,center,right
                    navigationHOffset: 50,
                    navigationVOffset: 55,

                    soloArrowLeftHalign: "let",
                    soloArrowLeftValign: "center",
                    soloArrowLeftHOffset: 0,
                    soloArrowLeftVOffset: 0,

                    soloArrowRightHalign: "right",
                    soloArrowRightValign: "center",
                    soloArrowRightHOffset: 0,
                    soloArrowRightVOffset: 0,

                    touchenabled: "on", // Enable Swipe Function : on/off



                    stopAtSlide: -1, // Stop Timer if Slide "x" has been Reached. If stopAfterLoops set to 0, then it stops already in the first Loop at slide X which defined. -1 means do not stop at any slide. stopAfterLoops has no sinn in this case.
                    stopAfterLoops: 0, // Stop Timer if All slides has been played "x" times. IT will stop at THe slide which is defined via stopAtSlide:x, if set to -1 slide never stop automatic



                    fullWidth: "on",

                    shadow: 0								//0 = no Shadow, 1,2,3 = 3 Different Art of Shadows -  (No Shadow in Fullwidth Version !)

                });




    });
</script>
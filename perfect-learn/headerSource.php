
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Premium Modern Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>-->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js" type="text/javascript"></script>
<style type="text/css">
    @media all and (min-width: 992px) {
        .navbar .has-megamenu{position:static!important;}
        .navbar .megamenu{left:0; right:0; width:100%; padding:00px;}
    }
    
    /* ============================================
       🚀 PREMIUM MODERN NAVBAR - 2026 DESIGN
       ============================================ */
    
    /* Glass Morphism Header */
    .top-header {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(20px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
        box-shadow: 0 8px 32px rgba(15, 61, 145, 0.08) !important;
        border-bottom: 1px solid rgba(15, 61, 145, 0.08) !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 9999 !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    .top-header.scrolled {
        background: rgba(255, 255, 255, 0.98) !important;
        box-shadow: 0 12px 48px rgba(15, 61, 145, 0.12) !important;
    }
    
    /* Logo Styling */
    .navbar-brand {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    .navbar-brand:hover {
        transform: scale(1.05) !important;
    }
    
    .navbar-brand img {
        max-height: 90px !important;
        height: 90px !important;
        width: auto !important;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1)) !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    /* Navigation Container */
    .navbar-nav {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        flex-wrap: nowrap !important;
    }
    
    /* Navigation Links - Ultra Modern */
    .navbar-nav .nav-link,
    .navbar-nav .dropdown > .nav-link {
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
        font-weight: 600 !important;
        font-size: 13.5px !important;
        letter-spacing: 0.5px !important;
        padding: 14px 20px !important;
        color: #1a202c !important;
        position: relative !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        text-transform: uppercase !important;
        border-radius: 12px !important;
        background: transparent !important;
    }
    
    .navbar-nav .nav-link b {
        font-family: 'Outfit', sans-serif !important;
        font-weight: 700 !important;
    }
    
    /* Glassmorphic Hover Effect */
    .navbar-nav .nav-link:hover,
    .navbar-nav .dropdown:hover > .nav-link {
        background: linear-gradient(135deg, rgba(15,61,145,0.08) 0%, rgba(30,136,229,0.08) 100%) !important;
        color: #0F3D91 !important;
        transform: translateY(-3px) !important;
        box-shadow: 0 8px 24px rgba(15,61,145,0.15) !important;
    }
    
    /* Animated Underline with Gradient */
    .navbar-nav .nav-link::before {
        content: '' !important;
        position: absolute !important;
        width: 0 !important;
        height: 3px !important;
        bottom: 6px !important;
        left: 50% !important;
        background: linear-gradient(90deg, #0F3D91, #1E88E5, #00BCD4) !important;
        background-size: 200% 100% !important;
        animation: gradientFlow 3s ease infinite !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        transform: translateX(-50%) !important;
        border-radius: 3px !important;
        box-shadow: 0 0 12px rgba(30,136,229,0.5) !important;
    }
    
    .navbar-nav .nav-link:hover::before,
    .navbar-nav .dropdown:hover > .nav-link::before {
        width: 80% !important;
    }
    
    @keyframes gradientFlow {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    /* Premium Dropdown Styling */
    .dropdown {
        position: relative !important;
        display: inline-block !important;
    }
    
    .dropdown-content {
        display: none !important;
        position: absolute !important;
        top: calc(100% + 12px) !important;
        left: 0 !important;
        background: rgba(255, 255, 255, 0.98) !important;
        backdrop-filter: blur(20px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
        min-width: 280px !important;
        padding: 16px 0 !important;
        border-radius: 16px !important;
        box-shadow: 0 24px 72px rgba(15,61,145,0.16), 0 0 0 1px rgba(15,61,145,0.06) !important;
        border: 1px solid rgba(15,61,145,0.1) !important;
        z-index: 10000 !important;
        animation: dropdownSlide 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
        overflow: hidden !important;
    }
    
    @keyframes dropdownSlide {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .dropdown-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #0F3D91, #1E88E5, #00BCD4);
        background-size: 200% 100%;
        animation: gradientFlow 3s ease infinite;
    }
    
    .dropdown-content a {
        font-family: 'Outfit', sans-serif !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 14px 24px !important;
        margin: 4px 12px !important;
        color: #2d3748 !important;
        text-decoration: none !important;
        display: block !important;
        border-radius: 10px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        overflow: hidden !important;
        letter-spacing: 0.3px !important;
    }
    
    .dropdown-content a::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(30,136,229,0.1), transparent);
        transition: left 0.5s ease;
    }
    
    .dropdown-content a:hover::before {
        left: 100%;
    }
    
    .dropdown-content a:hover {
        background: linear-gradient(135deg, rgba(15,61,145,0.1) 0%, rgba(30,136,229,0.12) 100%) !important;
        color: #0F3D91 !important;
        transform: translateX(8px) !important;
        padding-left: 32px !important;
        box-shadow: 0 4px 12px rgba(15,61,145,0.1) !important;
    }
    
    .dropdown-content a::after {
        content: '→';
        position: absolute;
        right: 20px;
        opacity: 0;
        transition: all 0.3s ease;
    }
    
    .dropdown-content a:hover::after {
        opacity: 1;
        right: 16px;
    }
    
    .dropdown:hover .dropdown-content {
        display: block !important;
    }
    
    /* 🎯 ULTIMATE PREMIUM LOGIN BUTTON */
    .nav-item-login {
        margin-left: 16px !important;
    }
    
    .nav-item-login .nav-link.btn-login {
        background: linear-gradient(135deg, #0F3D91 0%, #1E88E5 50%, #00BCD4 100%) !important;
        background-size: 200% 200% !important;
        color: white !important;
        padding: 14px 36px !important;
        border-radius: 50px !important;
        font-family: 'Space Grotesk', 'Outfit', sans-serif !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        letter-spacing: 1.5px !important;
        box-shadow: 0 8px 28px rgba(15,61,145,0.4), 0 0 0 0 rgba(30,136,229,0.4) !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        overflow: hidden !important;
        border: 2px solid rgba(255,255,255,0.2) !important;
        animation: gradientShift 4s ease infinite !important;
    }
    
    @keyframes gradientShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    /* Shine Effect */
    .nav-item-login .nav-link.btn-login::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        left: -50% !important;
        width: 200% !important;
        height: 200% !important;
        background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%) !important;
        transform: scale(0) !important;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    .nav-item-login .nav-link.btn-login:hover::before {
        transform: scale(1) !important;
    }
    
    /* Glow Ring Effect */
    .nav-item-login .nav-link.btn-login::after {
        content: '' !important;
        position: absolute !important;
        inset: -4px !important;
        border-radius: 50px !important;
        padding: 2px !important;
        background: linear-gradient(135deg, #0F3D91, #1E88E5, #00BCD4, #1E88E5, #0F3D91) !important;
        background-size: 300% 300% !important;
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0) !important;
        -webkit-mask-composite: xor !important;
        mask-composite: exclude !important;
        opacity: 0 !important;
        animation: glowRing 3s ease infinite !important;
        transition: opacity 0.4s ease !important;
    }
    
    @keyframes glowRing {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    .nav-item-login .nav-link.btn-login:hover::after {
        opacity: 1 !important;
    }
    
    .nav-item-login .nav-link.btn-login:hover {
        transform: translateY(-5px) scale(1.08) !important;
        box-shadow: 0 16px 48px rgba(15,61,145,0.5), 0 0 40px rgba(30,136,229,0.4), 0 0 60px rgba(0,188,212,0.2) !important;
        border-color: rgba(255,255,255,0.4) !important;
    }
    
    .nav-item-login .nav-link.btn-login b {
        position: relative !important;
        z-index: 2 !important;
        color: white !important;
        text-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
    }
    
    /* Pulse Animation for LOGIN */
    @keyframes loginPulse {
        0%, 100% {
            box-shadow: 0 8px 28px rgba(15,61,145,0.4), 0 0 20px rgba(30,136,229,0.3);
        }
        50% {
            box-shadow: 0 8px 28px rgba(15,61,145,0.4), 0 0 40px rgba(30,136,229,0.5), 0 0 60px rgba(0,188,212,0.3);
        }
    }
    
    .nav-item-login .nav-link.btn-login {
        animation: loginPulse 2s ease-in-out infinite !important;
    }
    
    /* Smooth scroll behavior */
    html {
        scroll-behavior: smooth;
    }
    
    /* Megamenu Enhancement */
    .megamenu {
        background: rgba(255, 255, 255, 0.98) !important;
        backdrop-filter: blur(20px) !important;
        border-radius: 16px !important;
        box-shadow: 0 24px 72px rgba(15,61,145,0.16) !important;
        border: 1px solid rgba(15,61,145,0.1) !important;
    }
    
    .megamenu .title {
        font-family: 'Outfit', sans-serif !important;
        font-weight: 700 !important;
        color: #0F3D91 !important;
        font-size: 14px !important;
        margin-bottom: 12px !important;
    }
    
    .megamenu ul li a {
        font-family: 'Outfit', sans-serif !important;
        font-weight: 500 !important;
        padding: 10px 16px !important;
        border-radius: 8px !important;
        transition: all 0.3s ease !important;
    }
    
    .megamenu ul li a:hover {
        background: linear-gradient(135deg, rgba(15,61,145,0.08), rgba(30,136,229,0.08)) !important;
        color: #0F3D91 !important;
        transform: translateX(4px) !important;
    }
</style>
<script type="text/javascript">
/// Premium Navbar Enhancements

// jquery ready start
$(document).ready(function () {
    // Prevent closing from click inside dropdown
    $(document).on('hover', '.dropdown-menu', function (e) {
        e.stopPropagation();
    });
    
    // Navbar scroll effect
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.top-header').addClass('scrolled');
        } else {
            $('.top-header').removeClass('scrolled');
        }
    });
    
    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(e) {
        var target = $(this.getAttribute('href'));
        if(target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 800, 'swing');
        }
    });
    
    // Add active class to current nav item
    var url = window.location.pathname;
    var filename = url.substring(url.lastIndexOf('/')+1);
    $('.navbar-nav a').each(function(){
        var href = $(this).attr('href');
        if(href === filename) {
            $(this).closest('.dropdown, div').find('.nav-link').first().addClass('active');
        }
    });
    
}); // jquery end
</script>
<!-- LOADER -->
<div id="preloader">
    <div class="loader">
        <img src="images/loader.gif" alt="#" />
    </div>
</div>

<!-- new code started frpm here  -->











<!-- new code ended from here  -->

  




<!-- END LOADER -->

<!-- Start header -->

<header class="top-header">
    <nav class="navbar header-nav navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.html"><img src="aitfImages/newlogo.png" alt="image"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-wd" aria-controls="navbar-wd" aria-expanded="false" aria-label="Toggle navigation"> </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbar-wd">

                <div class="navbar-nav">
                    <div class="dropdown">
                        <div class="nav-link active"><b>HOME</b></div>
                        <div class="dropdown-content">
                            <a href="index.php" style="font-size:13px;"><b>HOME</b></a>
                            <a href="aboutISO.php" style="font-size:13px;"><b>ABOUT ISO</b></a>
                            <a href="policy.php" style="font-size:13px;"><b>POLICY</b></a>
                            <a href="expectedOutcome.php" style="font-size:13px;"><b>EXPECTED OUTCOME</b></a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <div class="nav-link "><b>CERTIFICATION</b></div>
                        <div class="dropdown-content">
                            <a href="certificationProcess.php" style="font-size:13px;"><b>CERTIFICATION PROCESS</b></a>
                            <a href="criteria.php" style="font-size:13px;"><b>CRITERIA</b></a>
                            <a href="complaintsAppeals.php" style="font-size:13px;"><b>Complaints & Appeals</b></a>
                            <a href="impartiality.php" style="font-size:13px;"><b>Impartiality</b></a>
                            <a href="application.php" style="font-size:13px;"><b>Application form</b></a>
                        </div>
                    </div>
                    <!--<div><a class="nav-link active" href="index.html">Home</a></div>-->
                    <!--                    <div class="dropdown">
                                            <div class="nav-link "><b>TRAINING</b></div>
                                            <div class="dropdown-content">
                                                <a href="application.php" style="font-size:13px;"><b>THE CQI & IRCA CERTIFIED LA COURSE</b></a>
                                                <a href="transitionGuideline.php" style="font-size:13px;"><b>EXEMPLAR GLOBAL CERTIFIED LA COURSE</b></a>
                                                <a href="ncClosure.php" style="font-size:13px;"><b>AWARENESS TRAINING COURSE </b></a>
                                                <a href="useOfLogo.php" style="font-size:13px;"><b>INTERNAL AUDITOR TRAINING COURSE</b></a>
                                                <a href="infoUpdationForm.php" style="font-size:13px;"><b>ADVANCED IMPLEMENTATION TRAINING COURSE</b></a>
                                                <a href="circuler.php" style="font-size:13px;"><b>OTHER TRAINING COURSES</b></a>
                                            </div>
                                        </div>-->

                    <div class="has-megamenu">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"><b>TRAINING</b></a>
                        <div class="dropdown-menu megamenu" role="menu" style="z-index:1; padding: 12px 16px; position: absolute;">
                            <div class="row">
                                
                                <div class="col-md-2">
                                    <div class="col-megamenu">
                                        <h5 class="title"><b>THE CQI & IRCA CERTIFIED LA COURSE</b></h5>
                                        <ul class="list-unstyled">
                                            <li><a href="qms_iso_9001.php">QMS – ISO 9001:2015</a></li>
                                            <li><a href="ems_iso_9001.php">EMS – ISO 14001:2015</a></li>
                                            <li><a href="ohsms_iso_45001.php">OHSMS – ISO 45001:2018</a></li>
                                            <li><a href="bcms_iso_22301.php">BCMS – ISO 22301:2012</a></li>
                                            <li><a href="isms_iso_27001.php">ISMS – ISO 27001:2013</a></li>
                                            <li><a href="expantion_iso.php">Forthcoming Expansions</a></li>
                                        </ul>
                                    </div>  <!-- col-megamenu.// -->
                                </div><!-- end col-3 -->
                                
                                <div class="col-md-2">
                                    <div class="col-megamenu">
                                        <h5 class="title"><b>EXEMPLAR GLOBAL CERTIFIED LA COURSE</b></h5>
                                        <ul class="list-unstyled">
                                            <li><a href="qms_iso_9001_02.php">QMS – ISO 9001:2015</a></li>
                                            <li><a href="ems_iso_9001_02.php">EMS – ISO 14001:2015</a></li>
                                            <li><a href="ohsms_iso_45001_02.php">OHSMS – ISO 45001:2018</a></li>
                                            <li><a href="bcms_iso_22301_02.php">BCMS – ISO 22301:2012</a></li>
                                            <li><a href="isms_iso_27001_02.php">ISMS – ISO 27001:2013</a></li>
                                            <li><a href="fsms_iso_22000.php">FSMS – ISO 22000:2018</a></li>
                                        </ul>
                                    </div>  <!-- col-megamenu.// -->
                                </div><!-- end col-3 -->
                                
                                <div class="col-md-2">
                                    <div class="col-megamenu">
                                        <h5 class="title"><b>AWARENESS TRAINING COURSE</b></h5>
                                        <ul class="list-unstyled">
                                            <li><a href="qms_iso_9001_03.php">QMS ISO 9001:2015</a></li>
                                            <li><a href="ems_iso_9001_03.php">EMS ISO 14001:2015</a></li>
                                            <li><a href="ohsms_iso_45001_03.php">OHSMS ISO 45001:2018</a></li>
                                            <li><a href="bcms_iso_22301_03.php">BCMS ISO 22301:2012</a></li>
                                            <li><a href="isms_iso_27001_03.php">ISMS ISO 27001:2013</a></li>
                                        </ul>
                                    </div>  <!-- col-megamenu.// -->
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="col-megamenu">
                                        <h5 class="title"><b>INTERNAL AUDITOR TRAINING COURSE</b></h5>
                                        <ul class="list-unstyled">
                                            <li><a href="qms_iso_9001_04.php">QMS ISO 9001:2015</a></li>
                                            <li><a href="ems_iso_9001_04.php">EMS ISO 14001:2015</a></li>
                                            <li><a href="ohsms_iso_45001_04.php">OHSMS ISO 45001:2018</a></li>
                                            <li><a href="bcms_iso_22301_04.php">BCMS ISO 22301:2012</a></li>
                                            <li><a href="isms_iso_27001_04.php">ISMS ISO 27001:2013</a></li>
                                            <li><a href="integrated_mgmgt_system.php">Integrated Management Systems</a></li>
                                            <li><a href="md_qms_iso.php">MD-QMS ISO 13485:2016 </a></li>
                                        </ul>
                                    </div>  <!-- col-megamenu.// -->
                                </div><!-- end col-3 -->
                                
                                <div class="col-md-2">
                                    <div class="col-megamenu">
                                        <h5 class="title"><b>ADVANCED IMPLEMENTATION TRAINING COURSE</b></h5>
                                        <ul class="list-unstyled">
                                            <li><a href="qms_iso_9001_05.php">QMS ISO 9001:2015</a></li>
                                            <li><a href="ems_iso_9001_05.php">EMS ISO 14001:2015</a></li>
                                            <li><a href="ohsms_iso_45001_05.php">OHSMS ISO 45001:2018</a></li>
                                            <li><a href="bcms_iso_22301_05.php">BCMS ISO 22301:2012</a></li>
                                            <li><a href="isms_iso_27001_05.php">ISMS ISO 27001:2013</a></li>
                                            <li><a href="md_qms_iso_02.php">MD-QMS ISO 13485:2016 </a></li>
                                        </ul>
                                    </div>  <!-- col-megamenu.// -->
                                </div><!-- end col-3 -->
                            </div><!-- end row --> 
                            
                            <div class="col-md-2">
                                <div class="col-megamenu">
                                    <h5 class="title"><b>OTHER TRAINING COURSES </b></h5>
                                    <ul class="list-unstyled">
                                        <li><a href="#">Train the trainer</a></li>
                                        <li><a href="gdpr-2.php">General Data Protection Regulation (GDPR)</a></li>
                                        <li><a href="soft-skills-training.php">Soft Skills Training</a></li>
                                        <li><a href="cyber-security.php">CYBER SECURITY</a></li>
                                        <li><a href="it-disaster-recovery-training-2.php">IT Disaster Recovery</a></li>
                                    </ul>
                                </div>  <!-- col-megamenu.// -->
                            </div><!-- end col-3 -->
                            
                        </div><!-- end row --> 
                    </div> <!-- dropdown-mega-menu.// -->
                </div>
                <div><a class="nav-link" href="validation.php"><b>CERTIFICATE VALIDATION</b></a></div>
                <div><a class="nav-link" href="text/aitf_profile.pdf" target="_blank"><b>AITF PROFILE</b></a></div>
                <div class="dropdown">
                    <div class="nav-link "><b>DOWNLOAD</b></div>
                    <div class="dropdown-content">
                        <a href="application.php" style="font-size:13px;"><b>APPLICATION FORM</b></a>
                        <a href="transitionGuideline.php" style="font-size:13px;"><b>Transition guideline</b></a>
                        <a href="ncClosure.php" style="font-size:13px;"><b>NC report & closure</b></a>
                        <a href="useOfLogo.php" style="font-size:13px;"><b>Use Of Logo Guideline</b></a>
                        <a href="infoUpdationForm.php" style="font-size:13px;"><b>Information updation form</b></a>
                        <a href="circuler.php" style="font-size:13px;"><b>Circular</b></a>
                        <a href="certificationGuideline.php" style="font-size:13px;"><b>CERTIFICATION GUIDELINE</b></a>
                        <a href="auditorGuideline.php" style="font-size:13px;"><b>AUDITOR GUIDELINE</b></a>
                    </div>
                </div>
                <div><a class="nav-link" href="contactUS.php"><b>Contact US</b></a></div>
                <div class="nav-item-login"><a class="nav-link btn-login" href="../loginSelection.php"><b>LOGIN</b></a></div>
            </div>
        </div>
        <!--            <div class="search-box">
                        <input type="email" class="search-txt" placeholder="User Email">
                        <input type="password" class="search-txt" placeholder="Password">
                        <a class="search-btn">
                            <img src="aitfImages/login.jpg" alt="#" />
                        </a>
                    </div>-->

        </div>
    </nav>
</header>
<!-- End header -->
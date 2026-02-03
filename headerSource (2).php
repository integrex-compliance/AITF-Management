
<meta name="viewport" content="width=device-width, initial-scale=1">
<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>-->
<!-- Required JS: jQuery → Popper → Bootstrap -->
<script src="js/outer/jquery.min.js"></script>
<script src="js/outer/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" type="text/javascript"></script>
<style type="text/css">
    @media all and (min-width: 992px) {
        /*.navbar{ padding-top: 0; padding-bottom: 0; }*/
        .navbar .has-megamenu{position:static!important;}
        .navbar .megamenu{left:0; right:0; width:100%; padding:00px;  }
        /*.navbar .nav-link{ padding-top:1rem; padding-bottom:1rem;  }*/
    }
</style>
<script type="text/javascript">
/// some script
// jquery ready start
    $(document).ready(function () {
        // jQuery code

        //////////////////////// Prevent closing from click inside dropdown
        $(document).on('hover', '.dropdown-menu', function (e) {
            e.stopPropagation();
        });


    }); // jquery end
</script>
<!-- LOADER -->
<div id="preloader">
    <div class="loader">
        <div class="modern-spinner">
            <div class="spinner-circle"></div>
            <div class="spinner-text">Loading...</div>
        </div>
    </div>
</div>
<style>
    #preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.95);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    .modern-spinner {
        text-align: center;
    }
    
    .spinner-circle {
        width: 60px;
        height: 60px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        animation: spin 1.5s linear infinite;
        margin: 0 auto 15px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }
    
    .spinner-text {
        color: #2c3e50;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        animation: pulse 2s infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes pulse {
        0% { opacity: 0.6; }
        50% { opacity: 1; }
        100% { opacity: 0.6; }
    }
</style>
<!-- END LOADER -->

<!-- Start header -->
<head>

</head>
<header class="top-header">
    <nav class="navbar header-nav navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.html"><img src="aitfImages/aitf.jpg" alt="image"></a>
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
                  
                    <div class="dropdown">
                        <div class="nav-link active login-list"><b>TRAINING</b></div>
                        <div class="dropdown-content">
                            <a class="logincall" style="font-size:13px;" data-toggle="collapse" data-target="#userlogin-show"><b>EXEMPLAR GLOBAL CERTIFIED LA COURSE</b></a>
                            <div class="row collapse" id="userlogin-show">
                                <div class="col-sm-12">
                                    <ul class="list-unstyled" style="font-size:20 ">
                                        <li><a href="qms_iso_9001_02.php">QMS – ISO 9001:2015</a></li>
                                        <li><a href="ems_iso_9001_02.php">EMS – ISO 14001:2015</a></li>
                                        <li><a href="ohsms_iso_45001_02.php">OHSMS – ISO 45001:2018</a></li>
                                        <li><a href="isms_iso_27001_02.php">ISMS – ISO 27001:2013</a></li>
                                        <li><a href="fsms_iso_22000.php">FSMS – ISO 22000:2018</a></li>
                                    </ul>
                                </div>
                            </div>
                            <a class="logincall" style="font-size:13px;" data-toggle="collapse" data-target="#userlogin-show-1"><b>AWARENESS TRAINING COURSE</b></a>
                            <div class="row collapse" id="userlogin-show-1">
                                <div class="col-sm-12">
                                    <ul class="list-unstyled">
                                        <li><a href="qms_iso_9001_03.php">QMS ISO 9001:2015</a></li>
                                        <li><a href="ems_iso_9001_03.php">EMS ISO 14001:2015</a></li>
                                        <li><a href="ohsms_iso_45001_03.php">OHSMS ISO 45001:2018</a></li>
                                        <li><a href="bcms_iso_22301_03.php">BCMS ISO 22301:2012</a></li>
                                        <li><a href="isms_iso_27001_03.php">ISMS ISO 27001:2013</a></li>
                                    </ul>
                                </div>
                            </div>
                            <a class="logincall" style="font-size:13px;" data-toggle="collapse" data-target="#userlogin-show-2"><b>INTERNAL AUDITOR TRAINING COURSE</b></a>
                            <div class="row collapse" id="userlogin-show-2">
                                <div class="col-sm-12">
                                    <ul class="list-unstyled">
                                        <li><a href="qms_iso_9001_04.php">QMS ISO 9001:2015</a></li>
                                        <li><a href="ems_iso_9001_04.php">EMS ISO 14001:2015</a></li>
                                        <li><a href="ohsms_iso_45001_04.php">OHSMS ISO 45001:2018</a></li>
                                        <li><a href="bcms_iso_22301_04.php">BCMS ISO 22301:2012</a></li>
                                        <li><a href="isms_iso_27001_04.php">ISMS ISO 27001:2013</a></li>
                                        <li><a href="integrated_mgmgt_system.php">Integrated Management Systems</a></li>
                                        <li><a href="md_qms_iso.php">MD-QMS ISO 13485:2016 </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                 
                    <div><a class="nav-link" href="validation.php"><b>CERT VALIDATE</b></a></div>
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


                    <div><a class="nav-link" href="contactUS.php"><b>CONTACT US</b></a></div>
                    <div><a class="nav-link" href="loginSelection.php"><b>LOGIN</b></a></div>
                </div>
            </div>
    </nav>
</header>
<!-- End header -->

<script type="text/javascript">
    $(document).ready(function () {

    });
</script>

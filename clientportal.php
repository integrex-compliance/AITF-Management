-iute12-<!--<div id="wrapper" class="active">-->
<div id="wrapper" class="active client-portal-layout">
    <?php
    include_once './header.php';
    include_once './dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');
    
    $nextService = "";
    
    if (isset($_SESSION["username"]) && !empty($_SESSION["username"]) && $_SESSION["role"] == 'client') {
        try {
            $beforeDate = "";
            $clientDetails = "SELECT * FROM customer_info WHERE id = :id";
            $statement = $db->prepare($clientDetails);
            $statement->bindParam(':id', $_SESSION['id']);
            $statement->execute();
            $clientInfo = $statement->fetch();

            if ($clientInfo["next_service"] == 'CA'){
                $nextService = "Certification Aggrement";
            } else if($clientInfo["next_service"] == 'SA-I'){
                $nextService = "Survelane 1";
            } else if($clientInfo["next_service"] == 'SA-II'){
                $nextService = "Survelane 2";
            } else if($clientInfo["next_service"] == 'RC'){
                $nextService = "Re-Certification";
            }
            
            $allRowCount = "SELECT * FROM service where customer_id = :id order by service_id asc";
            $statement = $db->prepare($allRowCount);
            $statement->bindParam(':id', $_SESSION['id']);
            $statement->execute();
            $allsvcCount = $statement->fetchAll();
            $totalallCount = count($allsvcCount);

            $maxsvcid = "SELECT max(service_id) as id FROM `service` WHERE customer_id = :id AND (service_type != 'CR' AND service_type != 'TA' AND service_type != 'PTR' AND service_type != 'SP')";
            $statement = $db->prepare($maxsvcid);
            $statement->bindParam(':id', $_SESSION['id']);
            $statement->execute();
            $scid = $statement->fetch();

            $currentsvcreminder = "SELECT * FROM `ca_service_dates` where service_id = :id";
            $statement = $db->prepare($currentsvcreminder);
            $statement->bindParam(':id', $scid['id']);
            $statement->execute();
            $currentreminders = $statement->fetchAll();

            $userg = "SELECT * FROM `user_guidelines`";
            $statement = $db->prepare($userg);
            $statement->execute();
            $guidelines = $statement->fetch();

            $svctp = "SELECT `service_type` FROM `service` WHERE `service_id` = :id";
            $statement = $db->prepare($svctp);
            $statement->bindParam(':id', $scid['id']);
            $statement->execute();
            $svctype = $statement->fetch();

            if ($svctype["service_type"] == "CA") {
                $selectsvc = "SELECT `certificate_print` FROM `service_type_ca` WHERE `service` = :id";
                $statement = $db->prepare($selectsvc);
                $statement->bindParam(':id', $scid['id']);
                $statement->execute();
                $certifcate = $statement->fetch();
            } else if ($svctype["service_type"] == "SA-I") {
                $selectsvc = "SELECT `certificate_print` FROM `service_type_sai` WHERE `service` = :id";
                $statement = $db->prepare($selectsvc);
                $statement->bindParam(':id', $scid['id']);
                $statement->execute();
                $certifcate = $statement->fetch();
            } else if ($svctype["service_type"] == "SA-II") {
                $selectsvc = "SELECT `certificate_print` FROM `service_type_saii` WHERE `service` = :id";
                $statement = $db->prepare($selectsvc);
                $statement->bindParam(':id', $scid['id']);
                $statement->execute();
                $certifcate = $statement->fetch();
            } else if ($svctype["service_type"] == "RC") {
                $selectsvc = "SELECT `certificate_print` FROM `service_type_rc` WHERE `service` = :id";
                $statement = $db->prepare($selectsvc);
                $statement->bindParam(':id', $scid['id']);
                $statement->execute();
                $certifcate = $statement->fetch();
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    ?>
    <style>
        .listview {
            list-style-type: none;
            font-family: sans-serif;
        }

        .listview li {
            padding: .7em 15px;
            /*background-color: #efefef;*/  
            border: 1px solid #bbb;
            max-width: 90%;
            cursor: hand;
        }

        .listview li:nth-child(n+2) {
            border-top: 0;
        }

        .listview li:hover { 
            /*background-color: white;*/    
        }

        .item{
            float: left;
            width: 33.3%;
            text-align: center; 
            /*border-bottom: 1px solid #cfcfcf;*/
        }

        .titulo {
            /*text-align: center;*/
            font-family: sans-serif;
            /*color: #444;*/
        }

        .container {
            width: 100%;
            margin: auto;
        }
    </style>
    <!--client portal page-->
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="row" style="">
                <div class="top_qms_page">
                    <div class="row">
                        <div class="col-sm-12 qms_customer_name">
                            <span><i class="fa fa-building"></i></span>
                            <?php
                            echo $clientInfo['customer_name'];
                            ?>
                        </div>
                    </div>
                    <div class="row" style="padding-top: 50px;">
                        <div class="col-sm-6">
                            <div class="row" style="padding: 5px; padding-left: 20px">
                                <div class="col-xs-3">
                                    <span style="color: black" title="GSTIN number">GSTIN number </span>
                                </div>
                                <div class="col-xs-9">
                                    <span style="color: gray"><?php echo $clientInfo['gstin_no'] ?></span>
                                </div>
                            </div>
                            <div class="row" style="padding: 5px; padding-left: 20px">
                                <div class="col-xs-3">
                                    <span style="color: black" title="Unique Id">Unique Id</span>
                                </div>
                                <div class="col-xs-9">
                                    <span style="color: gray"><?php echo $clientInfo['unique_id'] ?></span>
                                </div>
                            </div> 
                            <div class="row" style="padding: 5px; padding-left: 20px">
                                <div class="col-xs-3">
                                    <span style="color: black" title="Address">Address</span>
                                </div>
                                <div class="col-xs-9">
                                    <span style="color: gray"><?php echo $clientInfo['address'] ?></span>
                                </div>
                            </div> 
                            <div class="row" style="padding: 5px; padding-left: 20px">
                                <div class="col-xs-3">
                                    <span style="color: black" title="Contact No.">Contact No.</span>
                                </div>
                                <div class="col-xs-9">
                                    <span style="color: gray"><?php echo $clientInfo['contact'] ?></span>
                                </div> 
                            </div>
                            <div class="row" style="padding: 5px; padding-left: 20px">
                                <div class="col-xs-3">
                                    <span style="color: black" title="Email Id">Email Id</span>
                                </div>
                                <div class="col-xs-9">
                                    <span style="color: gray"><?php echo $clientInfo['email_id'] ?></span>
                                </div>
                            </div>
                            <div class="row" style="padding: 5px; padding-left: 20px">
                                <div class="col-xs-3">
                                    <span style="color: black" title="Scope">Scope</span>
                                </div>
                                <div class="col-xs-9">
                                    <span style="color: gray"><?php echo $clientInfo['scope'] ?></span>
                                </div>
                            </div>
                            <div class="row" style="padding: 5px; padding-left: 20px">
                                <div class="col-xs-3">
                                    <span style="color: black" title="IAF/NACE">IAF/NACE</span>
                                </div>
                                <div class="col-xs-9">
                                    <span style="color: gray"><?php echo $clientInfo['aif_nace'] ?></span>
                                </div>
                            </div>
                            <div class="row" style="padding: 5px; padding-left: 20px">
                                <div class="col-xs-3">
                                    <span style="color: black" title="Certificate No">Certificate No</span>
                                </div>
                                <div class="col-xs-9">
                                    <span style="color: gray"><?php echo $clientInfo["certification_no"]; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="servicebox-bunch new-cr-service-add" >
                                    <div class="servicebox">
                                        <div class="row cr-service-row" id="service-row">
                                            <div class="col-xs-12 qms_customer_name service_title" style="text-align: center" data-row-id="">
                                                Intimation/Reminders
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class='container'>
                                    <ul class="listview">
                                        <li style="height: 30px" style="background-color: #efefef; background-color: white; color: #444;">
                                            <div class="item"><b>Reminder</b></div>
                                            <div class="item"><b>Proposed Due</b></div>
                                            <div class="item"><b>Sent Date</b></div>
                                        </li>
                                        <?php
                                        foreach ($currentreminders as $reminde) {
                                            if ($reminde["action"] == "Suspension Letter" || $reminde["action"] == "Expire Letter") {
                                                $beforeDate = $reminde["due_date"];
                                            }
                                            ?>
                                            <li style="height: 30px">
                                                <div class="item"><?php echo $reminde["action"] ?></div>
                                                <div class="item"><?php echo simpleDateConversion($reminde["due_date"]) ?></div>
                                                <div class="item"><?php echo simpleDateConversion($reminde["sent_date"]) ?></div>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <b>NOTE:</b>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr style="color: slategray; margin: 0px; border: 1px solid slategray" />
                    <div class="row">
                        <div class="col-sm-6 " style="color: black; font-weight: small; ">
                            <div class="row">
                                <div class="col-sm-8">
                                    Certification recommendation Date:
                                </div>
                                <div class="col-sm-4">
                                    <?php if (isset($clientInfo['certification_stage_date'])) echo simpleDateConversion($clientInfo['certification_stage_date']) ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-sm-8">
                                    Current Certification Status:
                                </div>
                                <div class="col-sm-4">
                                    <?php echo $clientInfo["audit_status"]; ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-sm-8">
                                    Next audit Due Type:
                                </div>
                                <div class="col-sm-4">
                                    <?php echo $nextService; ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-sm-8">
                                    Shall Conducted Before:
                                </div>
                                <div class="col-sm-4">
                                    <?php echo simpleDateConversion($beforeDate); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 " style="color: black; font-weight: small; border-left: 1px solid slategray; height: 140px;">
                            <div class="row">
                                <div class="col-sm-12">
                                    Latest Certificate copy:
                                    <span style="padding-left: 20px">
                                        <a  href="<?php echo $certifcate["certificate_print"] ?>" download class="" title="Download Letest Certficate"><i class="fa fa-download"></i></a>
                                    </span>
                                </div>
                            </div> 
                            <br>
                            <div class="row">
                                <div class="col-sm-12">
                                    Guideline to use logo:
                                    <span style="padding-left: 20px">
                                        <a  href="<?php echo $guidelines["logo_guidelines"] ?>" data-id="" download class="customerReviewFileDownload" title="Download Certification Agreement"><i class="fa fa-download"></i></a>
                                    </span>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-sm-12">
                                    Logos Coral/ PDF:
                                    <span style="padding-left: 20px">
                                        <a  href="<?php echo $guidelines["logo_corol"] ?>" data-id="" download class="customerReviewFileDownload" title="Download Certification Agreement"><i class="fa fa-download"></i></a>
                                    </span>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-sm-12">
                                    NC closure report Format:
                                    <span style="padding-left: 20px">
                                        <a  href="<?php echo $guidelines["nc_report_format"] ?>" data-id="" download class="customerReviewFileDownload" title="Download Certification Agreement"><i class="fa fa-download"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr style="color: slategray; margin: 0px; border: 1px solid slategray" />

                    <?php
                    foreach ($allsvcCount as $services) {
                        if ($services['service_type'] == "CR") {
                            $crservice = "SELECT * FROM service_type_cr WHERE service = :service";
                            $statement = $db->prepare($crservice);
                            $statement->bindParam(":service", $services["service_id"]);
                            $statement->execute();
                            $crservicedetails = $statement->fetch();
                            ?> 
                            <div class="servicebox-bunch <?php //echo $class2          ?> new-cr-service-add" >
                                <div class="servicebox">
                                    <div class="row cr-service-row" id="service-row" data-id="<?php echo $crservicedetails['cr_id'] ?>">
                                        <div class="col-xs-12 qms_customer_name service_title" style="text-align: center" data-row-id="">
                                            Contract Review
                                            <span class="serviceStatus"></span>
                                            <a class="deleteServiceCR" id="deleteServiceCR" data-service="<?php echo $crservicedetails['service'] ?>" data-row-id="<?php echo $crservicedetails['cr_id'] ?>" data-customer="<?php echo $_GET['customerid'] ?>"><i class="fa fa-trash-o pull-right " style="color: white; padding-right: 10px;"></i></a>
                                        </div>
                                    </div>
                                    <div class="cr-service-desc<?php echo $crservicedetails['cr_id'] ?>" style="display: <?php // echo $disp                                                        ?>;">
                                        <div class="servicebox-data" id="servicebox-data ">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="serviceContentCompleted"><span>Application Form </span>
                                                        <div style="float: right" class="image-preview form-inline">
                                                            <div class=" ">                                                                                                                                    
                                                                <span style="padding-right: 5px"> <a href="<?php echo $crservicedetails['application_form'] ?>" data-id="" download class="customerReviewFileDownload"><i class="fa fa-download"></i></a> </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6"><?php
                                                    if (isset($crservicedetails['application_form']) && !empty($crservicedetails['application_form']) && empty($crservicedetails['certification_aggrement'])) {
                                                        $addClass2 = "serviceContentwaiting";
                                                    } else if (isset($crservicedetails['certification_aggrement']) && !empty($crservicedetails['certification_aggrement'])) {
                                                        $addClass2 = "serviceContentCompleted";
                                                    } else {
                                                        $addClass2 = "serviceContent";
                                                    }
                                                    ?>
                                                    <div class="<?php echo $addClass2 ?>"><span> Certification Agreement Download</span>
                                                        <div style="float: right" class="image-preview form-inline">                                                            
                                                            <?php if (isset($crservicedetails['certification_aggrement']) && !empty($crservicedetails['certification_aggrement'])) { ?>
                                                                <span style="padding-right: 5px"> 
                                                                    <a  href="<?php echo $crservicedetails['certification_aggrement'] ?>" data-id="" download class="customerReviewFileDownload" title="Download Certification Agreement"><i class="fa fa-download"></i></a>
                                                                </span> 
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>                                                                
                                        </div>
                                    </div>
                                </div>
                            </div> 
                            <?php
                        }
                        if ($services['service_type'] == "CA") {
                            $caServices = "SELECT cas.ca_id, service, signed_notification, signed_copy, audit_report_I, signed_notification_II, audit_report_II, auditor_notes, audit_nc_II, certificate_print, customer_satisfaction, service_ca_status, cas.audit_date as caaudit, soi.audit_stage_id, sia.stage_i_id, siia.stage_ii_id, crr.crr_id, asi.audit_schedule_id, asii.audit_scheduleii_id, sioi.stage_i_id, cfi.control_id, cusi.survey_id, cfii.control2_id, stai.audit_stageii_id, cfp.certificate_id FROM service_type_ca cas left join stage_oneaudit_id soi on cas.ca_id = soi.ca_service_id left join stage_i_audit_id sia on cas.ca_id = sia.ca_service_id left join stage_ii_audit_id siia on cas.ca_id = siia.ca_service_id left join cdc_report_review crr on cas.ca_id = crr.ca_service_id left join audit_schedule_id asi on cas.ca_id = asi.serviceca_id left join audit_scheduleii_id asii on cas.ca_id = asii.ca_service left join stage_i_audit_id sioi on cas.ca_id = sioi.ca_service_id left join control_formati_id cfi on cas.ca_id = cfi.caservice_id left join customer_survey_id cusi on cas.ca_id = cusi.ca_serviceid left join control_formatii_id cfii on cas.ca_id = cfii.caservice_id left join stage_twoaudit_id stai on cas.ca_id = stai.service_id left join certificate_print cfp on cas.ca_id = cfp.service_id WHERE cas.service = :service";
                            $statement = $db->prepare($caServices);
                            $statement->bindParam(":service", $services["service_id"]);
                            $statement->execute();
                            $caservice = $statement->fetch();

                            if ($letestService["c_service"] == $caservice['service']) {
                                $disp = "";
                            } else {
                                $disp = "none";
                            }
                            ?>
                            <div class="servicebox-bunch <?php echo $class1 ?>" style="display: ">
                                <div class="servicebox">
                                    <form id="caService" action="serviceTypeCA.php" method="post" enctype="multipart/form-data">
                                        <div class="row ca-service-row" id="service-row" data-id="<?php echo $caservice['ca_id'] ?>">
                                            <div class="col-xs-12 qms_customer_name service_title" style="text-align: center" data-row-id="">Service type - CA<span class="serviceStatus"></span> <!--<a class="saveServiceCA" id="saveServiceCA" data-row-id=""><i class="fa fa-floppy-o pull-right " style="color:white; padding-right:10px; display:"></i></a>-->
                                                <a class="deleteServiceCA" id="deleteServiceCA" data-service="<?php echo $caservice['service'] ?>" data-row-id="<?php echo $caservice['ca_id'] ?>" data-customer="<?php echo $_GET['customerid'] ?>"><i class="fa fa-trash-o pull-right " style="color: white; padding-right: 10px;"></i></a>
                                            </div>
                                        </div>
                                        <div class="ca-service-desc<?php echo $caservice['ca_id'] ?>" style="display: <?php //echo $disp            ?>;">
                                            <div class="row" style="padding-top: 10px; ">
                                                <div class="col-sm-4 " style="color: black; font-weight: small; ">
                                                    <span>Add to: <?php echo $caservice['service_ca_status']; ?></span>
                                                </div>
                                                <div class="col-sm-4 " style="color: black; font-weight: small; ">
                                                    <span>Audit Date: <?php echo date("d.m.Y", strtotime($caservice["caaudit"])); ?></span>
                                                </div>
                                            </div>
                                            <div class="servicebox-data" id="servicebox-data ">
                                                <div class="row">                                
                                                    <div class="col-sm-6"><?php
                                                        if (isset($caservice['signed_notification']) && !empty($caservice['signed_notification'])) {
                                                            $enterClass = "serviceContentCompleted";
                                                        } else {
                                                            $enterClass = "serviceContentwaiting";
                                                        }
                                                        ?>
                                                        <div class="serviceContentCompleted"><span>Signed Notification Stage I </span>
                                                        <!-- <a class="btn btn-xs btn-warning createAuditApp pull-right" data-row-id="" data-row-count="" title="Add audit application" style="padding: 1px 5px; "><i class="fa fa-plus-circle fa-lg " style="padding:3px"></i></a> -->
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($caservice['signed_notification']) && !empty($caservice['signed_notification']) && !empty($caservice['audit_stage_id'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $caservice['signed_notification'] ?>" data-id="" download class="customerReviewFileDownload" title="Download Signed Notification"><i class="fa fa-download"></i></a>
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($caservice['signed_notification'])) {
                                                            $classShow = "serviceContent";
                                                        } else if (isset($caservice['signed_notification']) && !empty($caservice['signed_notification']) && empty($caservice['signed_copy'])) {
                                                            $classShow = 'serviceContentwaiting';
                                                        } else if (isset($caservice['signed_copy']) && !empty($caservice['signed_copy']) && isset($caservice['signed_notification']) && !empty($caservice['signed_notification'])) {
                                                            $classShow = 'serviceContentCompleted';
                                                        } else {
                                                            $classShow = "serviceContent";
                                                        }
                                                        ?>
                                                        <div class="<?php echo $classShow ?>">
                                                            <span>Schedule Signed Copy</span>
                                                            <div style="float: right" class="image-preview form-inline">                                                                     
                                                                <?php if (isset($caservice['signed_copy']) && !empty($caservice['signed_copy'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $caservice['signed_copy'] ?>" data-id="" download class="customerReviewFileDownload" title="Download Signed Copy"><i class="fa fa-download"></i></a> 
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row"> 
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($caservice['signed_copy'])) {
                                                            $classAdd4 = "serviceContent";
                                                        } else if (isset($caservice['signed_copy']) && !empty($caservice['signed_copy']) && empty($caservice['audit_report_I'])) {
                                                            $classAdd4 = "serviceContentwaiting";
                                                        } else if (isset($caservice['audit_report_I']) && !empty($caservice['audit_report_I']) && isset($caservice['signed_copy']) && !empty($caservice['signed_copy'])) {
                                                            $classAdd4 = 'serviceContentCompleted';
                                                        } else {
                                                            $classAdd4 = "serviceContent";
                                                        }
                                                        ?>
                                                        <div class="<?php echo $classAdd4 ?>">
                                                            <span>Stage - I Audit Report </span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($caservice['audit_report_I']) && !empty($caservice['audit_report_I'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $caservice['audit_report_I'] ?>" data-id="" download class="customerReviewFileDownload" title="Download Signed Copy"><i class="fa fa-download"></i></a> 
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($caservice['signed_copy'])) {
                                                            $classTo = 'serviceContent';
                                                        } else if (isset($caservice['signed_copy']) && !empty($caservice['signed_copy']) && empty($caservice['signed_notification_II'])) {
                                                            $classTo = 'serviceContentwaiting';
                                                        } else if (isset($caservice['audit_scheduleii_id']) && !empty($caservice['audit_scheduleii_id']) && isset($caservice['signed_copy']) && !empty($caservice['signed_copy'])) {
                                                            $classTo = 'serviceContentCompleted';
                                                        } else {
                                                            $classTo = 'serviceContent';
                                                        }
                                                        ?>
                                                        <div class="<?php echo $classTo ?>">
                                                            <span>Signed Notification Stage II, And Copy of Stage II Schedule</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($caservice['signed_notification_II']) && !empty($caservice['signed_notification_II']) && isset($caservice['audit_scheduleii_id']) && !empty($caservice['audit_scheduleii_id'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $caservice['signed_notification_II'] ?>" download class="download" title="Download Application Form">
                                                                            <i class="fa fa-download"></i>
                                                                        </a> 
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($caservice['signed_notification_II'])) {
                                                            $classNoti2 = 'serviceContent';
                                                        } else if (isset($caservice['signed_notification_II']) && !empty($caservice['signed_notification_II']) && empty($caservice['audit_report_II'])) {
                                                            $classNoti2 = 'serviceContentwaiting';
                                                        } else if (isset($caservice['signed_notification_II']) && !empty($caservice['signed_notification_II']) && isset($caservice['audit_report_II']) && !empty($caservice['audit_report_II'])) {
                                                            $classNoti2 = 'serviceContentCompleted';
                                                        } else {
                                                            $classNoti2 = 'serviceContent';
                                                        }
                                                        ?>
                                                        <div class="<?php echo $classNoti2 ?>">
                                                            <span>Stage - II Audit Report, Summery and Checklists</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($caservice['audit_report_II']) && !empty($caservice['audit_report_II']) && isset($caservice['signed_notification_II']) && !empty($caservice['signed_notification_II'])) { ?> 
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $caservice['audit_report_II'] ?>" download class="download" title="Download Application Form">
                                                                            <i class="fa fa-download "></i>
                                                                        </a> 
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($caservice['auditor_notes'])) {
                                                            $evidanceClass = 'serviceContent';
                                                        } else if (isset($caservice['auditor_notes']) && !empty($caservice['auditor_notes']) && empty($caservice['audit_nc_II'])) {
                                                            $evidanceClass = 'serviceContentwaiting';
                                                        } else if (isset($caservice['auditor_notes']) && !empty($caservice['auditor_notes']) && isset($caservice['audit_nc_II']) && !empty($caservice['audit_nc_II'])) {
                                                            $evidanceClass = 'serviceContentCompleted';
                                                        } else {
                                                            $evidanceClass = 'serviceContent';
                                                        }
                                                        ?>
                                                        <div class="caservicedivclass <?php echo $evidanceClass ?>">
                                                            <span>CA Audit NC And evidences of closer</span>
                                                            <?php if (isset($caservice['audit_nc_II']) && !empty($caservice['audit_nc_II']) && $caservice['audit_nc_II'] == "NONC" && isset($caservice['auditor_notes']) && !empty($caservice['auditor_notes'])) { ?> 
                                                                <a class="" title="No NC Flag" style="padding: 1px 5px;">
                                                                    <i class="fa fa-times-circle fa-lg " style="padding: 3px"></i>
                                                                </a>
                                                            <?php } ?>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($caservice['audit_nc_II']) && !empty($caservice['audit_nc_II']) && $caservice['audit_nc_II'] != "NONC" && isset($caservice['auditor_notes']) && !empty($caservice['auditor_notes'])) { ?>                                                    
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $caservice['audit_nc_II'] ?>" download class="download" title="Download Application Form">
                                                                            <i class="fa fa-download "></i>
                                                                        </a> 
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <?php
                        }
                        if ($services['service_type'] == "SA-I") {
                            if (isset($saiCount['service_done']) && $saiCount['service_done'] == "DONE") {
                                $class3 = 'sa-i-service-type';
                                $class4 = "sa-i-service-type";
                            } else if (isset($saiCount['service_done']) && $saiCount['service_done'] == "") {
                                $class3 = "sa-i-service-type";
                                $class4 = "";
                            } else {
                                $class3 = "";
                                $class4 = "sa-i-service-type";
                            }

                            $saIService = "SELECT stsai.sai_id,service, stsai.customer_id, info_update_form, signed_noti_sai, signed_copy_sai, audit_report_sai, auditor_notes_sai, audit_evidance_sai, certificate_print, customer_review_sai, service_sai_status, stsai.audit_date as saiaudit, soai.audit_stage_id, asi.servelance_id, siai.stage_ii_id, crr.crr_id, csi.survey_id, cfp.certificate_id FROM service_type_sai stsai left join stage_oneaudit_id soai on stsai.sai_id = soai.ca_service_id left join servelance_1_id asi on stsai.sai_id = asi.service_id left join stage_ii_audit_id siai on stsai.sai_id = siai.ca_service_id left join cdc_report_review crr on stsai.sai_id = crr.ca_service_id left join customer_survey_id csi on stsai.sai_id = csi.ca_serviceid left join certificate_print cfp on stsai.sai_id = cfp.service_id WHERE stsai.service = :id";
                            $statement = $db->prepare($saIService);
                            $statement->bindParam(':id', $services["service_id"]);
                            $statement->execute();
                            $saiService = $statement->fetch();

                            if ($letestService["c_service"] == $saiService['service']) {
                                $disp = "";
                            } else {
                                $disp = "none";
                            }
                            ?> 
                            <div class="servicebox-bunch <?php echo $class3 ?>" style="display: ">
                                <div class="servicebox" style="display: ">
                                    <form id="saiService" action="serviceTypeSAi.php" method="post" enctype="multipart/form-data">
                                        <div class="row sai-service-row" id="service-row" data-id="<?php echo $saiService['sai_id'] ?>">
                                            <div class="col-xs-12 qms_customer_name service_title" style="text-align: center" data-row-id="">
                                                Service type - SA-I<span class="serviceStatus"></span> 
                                                <a class="deleteServiceSai" id="deleteServiceSai" data-service="<?php echo $saiService['service'] ?>" data-row-id="<?php echo $saiService['sai_id'] ?>" data-customer="<?php echo $_GET['customerid'] ?>">
                                                    <i class="fa fa-trash-o pull-right " style="color: white; padding-right: 10px;"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="sai-service-desc<?php echo $saiService['sai_id'] ?>" style="display: <?php //echo $disp          ?>;">
                                            <div class="row" style="padding-top: 10px; ">
                                                <div class="col-sm-4 " style="color: black; font-weight: small; ">
                                                    <span>Add to: <?php echo $saiService['service_sai_status']; ?></span>
                                                </div>
                                                <div class="col-sm-4 " style="color: black; font-weight: small; ">
                                                    <span>Audit Date: <?php echo date("d.m.Y", strtotime($saiService["saiaudit"])); ?></span>
                                                </div>
                                            </div>
                                            <div class="servicebox-data" id="servicebox-data">
                                                <div class="row">                                        
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (isset($saiService['info_update_form']) && !empty($saiService['info_update_form']) && empty($saiService['signed_noti_sai'])) {
                                                            $saiSignNoti = 'serviceContentwaiting';
                                                        } else if (isset($saiService['info_update_form']) && !empty($saiService['info_update_form']) && isset($saiService['signed_noti_sai']) && !empty($saiService['signed_noti_sai'])) {
                                                            $saiSignNoti = 'serviceContentCompleted';
                                                        } else {
                                                            $saiSignNoti = 'serviceContent';
                                                        }
                                                        ?>
                                                        <div class="<?php echo $saiSignNoti ?>">
                                                            <span>Signed Notification SA I Audit</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($saiService['signed_noti_sai']) && !empty($saiService['signed_noti_sai'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $saiService['signed_noti_sai'] ?>" data-id="" download class="customerReviewFileDownload"><i class="fa fa-download"></i></a> 
                                                                    </span>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($saiService['signed_noti_sai'])) {
                                                            $saiSignedClass = 'serviceContent';
                                                        } else if (isset($saiService['signed_noti_sai']) && !empty($saiService['signed_noti_sai']) && empty($saiService['signed_copy_sai'])) {
                                                            $saiSignedClass = 'serviceContentwaiting';
                                                        } else if (isset($saiService['signed_noti_sai']) && !empty($saiService['signed_noti_sai']) && isset($saiService['signed_copy_sai']) && !empty($saiService['signed_copy_sai'])) {
                                                            $saiSignedClass = 'serviceContentCompleted';
                                                        } else {
                                                            $saiSignedClass = 'serviceContent';
                                                        }
                                                        ?>
                                                        <div class="<?php echo $saiSignedClass ?>"><span>Schedule Signed Copy</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($saiService['signed_copy_sai']) && !empty($saiService['signed_copy_sai'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $saiService['signed_copy_sai'] ?>" data-id="" download class="customerReviewFileDownload"><i class="fa fa-download"></i></a> 
                                                                    </span>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($saiService['signed_copy_sai'])) {
                                                            $saiAuditReportClass = 'serviceContent';
                                                        } else if (isset($saiService['signed_copy_sai']) && !empty($saiService['signed_copy_sai']) && empty($saiService['audit_report_sai'])) {
                                                            $saiAuditReportClass = 'serviceContentwaiting';
                                                        } else if (isset($saiService['signed_copy_sai']) && !empty($saiService['signed_copy_sai']) && isset($saiService['audit_report_sai']) && !empty($saiService['audit_report_sai'])) {
                                                            $saiAuditReportClass = 'serviceContentCompleted';
                                                        } else {
                                                            $saiAuditReportClass = 'serviceContent';
                                                        }
                                                        ?>
                                                        <div class="<?php echo $saiAuditReportClass ?>">
                                                            <span>SA - I Audit Report, Summery and Checklists</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($saiService['audit_report_sai']) && !empty($saiService['audit_report_sai'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $saiService['audit_report_sai'] ?>" data-id="" download class="customerReviewFileDownload"><i class="fa fa-download"></i></a>
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($saiService['auditor_notes_sai'])) {
                                                            $saiNcClsaa = 'serviceContent';
                                                        } else if (isset($saiService['auditor_notes_sai']) && !empty($saiService['auditor_notes_sai']) && empty($saiService['audit_evidance_sai'])) {
                                                            $saiNcClsaa = 'serviceContentwaiting';
                                                        } else if (isset($saiService['auditor_notes_sai']) && !empty($saiService['auditor_notes_sai']) && isset($saiService['audit_evidance_sai']) && !empty($saiService['audit_evidance_sai'])) {
                                                            $saiNcClsaa = 'serviceContentCompleted';
                                                        } else {
                                                            $saiNcClsaa = 'serviceContent';
                                                        }
                                                        ?>
                                                        <div class="<?php echo $saiNcClsaa ?>">
                                                            <span>SA - I Audit NC And evidences of closer</span>
                                                            <?php if (isset($saiService['audit_evidance_sai']) && !empty($saiService['audit_evidance_sai']) && $saiService['audit_evidance_sai'] == "NONC" && isset($saiService['auditor_notes_sai']) && !empty($saiService['auditor_notes_sai'])) { ?> 
                                                                <a class="" title="No NC Flag" style="padding: 1px 5px;">
                                                                    <i class="fa fa-times-circle fa-lg " style="padding: 3px"></i>
                                                                </a>
                                                            <?php } ?>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($saiService['audit_evidance_sai']) && !empty($saiService['audit_evidance_sai']) && $saiService['audit_evidance_sai'] != "NONC" && isset($saiService['auditor_notes_sai']) && !empty($saiService['auditor_notes_sai'])) { ?>                                                    
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $saiService['audit_evidance_sai'] ?>" data-id="" download class="customerReviewFileDownload"><i class="fa fa-download"></i></a>
                                                                    </span>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" class="saiCustomerId" name="saiCustomerId" value="<?php echo $_GET['customerid'] ?>">
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <?php
                        }
                        if ($services['service_type'] == "SA-II") {
                            if (isset($saiiCount['service_done']) && $saiiCount['service_done'] == "DONE") {
                                $class5 = 'sa-ii-service-type';
                                $class6 = "sa-ii-service-type";
                            } else if (isset($saiiCount['service_done']) && $saiiCount['service_done'] == "") {
                                $class5 = "sa-ii-service-type";
                                $class6 = "";
                            } else {
                                $class5 = "";
                                $class6 = "sa-ii-service-type";
                            }

                            $saIIService = "SELECT saii.saii_id, service, update_info_saii, signed_noti_saii, signed_copy_saii, audit_report_saii, auditor_notes_saii, certificate_print, audit_evidences_saii,customer_review_saii, service_saii_ststus, saii.audit_date as saiiaudit, soi.audit_stage_id, asii.servelance_id, siea.stage_ii_id, crr.crr_id, cusi.survey_id, cfp.certificate_id FROM service_type_saii saii left join stage_oneaudit_id soi on saii.saii_id = soi.ca_service_id left join servelance_1_id asii on saii.saii_id = asii.service_id left join stage_ii_audit_id siea on saii.saii_id = siea.ca_service_id left join cdc_report_review crr on saii.saii_id = crr.ca_service_id left join customer_survey_id cusi on saii.saii_id = cusi.ca_serviceid left join certificate_print cfp on saii.saii_id = cfp.service_id WHERE saii.service = :id";
                            $statement = $db->prepare($saIIService);
                            $statement->bindParam(':id', $services["service_id"]);
                            $statement->execute();
                            $saiiService = $statement->fetch();

                            if ($letestService["c_service"] == $saiiService['service']) {
                                $disp = "";
                            } else {
                                $disp = "none";
                            }
                            ?> 
                            <div class="servicebox-bunch <?php echo $class5 ?>" style="display: ">
                                <div class="servicebox" style="display: ">
                                    <form id="saiiService" action="serviceTypeSAii.php" method="post" enctype="multipart/form-data">
                                        <div class="row saii-service-row" id="service-row" data-id="<?php echo $saiiService['saii_id'] ?>">
                                            <div class="col-xs-12 qms_customer_name service_title" style="text-align: center" data-row-id="">
                                                Service type - SA-II<span class="serviceStatus"></span>
                                                <a class="deleteServiceSAII" id="deleteServiceSAII" data-service="<?php echo $saiiService['service'] ?>" data-row-id="<?php echo $saiiService['saii_id'] ?>" data-customer="<?php echo $_GET['customerid'] ?>">
                                                    <i class="fa fa-trash-o pull-right " style="color: white; padding-right: 10px;"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="saii-service-desc<?php echo $saiiService['saii_id'] ?>" style="display: <?php //echo $disp          ?>;">
                                            <div class="row" style="padding-top: 10px; ">
                                                <div class="col-sm-4 " style="color: black; font-weight: small; ">
                                                    <span>Add to: <?php echo $saiiService['service_saii_ststus']; ?></span>
                                                </div>
                                                <div class="col-sm-4 " style="color: black; font-weight: small; ">
                                                    <span>Audit Date: <?php echo date("d.m.Y", strtotime($saiiService["saiiaudit"])); ?></span>
                                                </div>
                                            </div>
                                            <div class="servicebox-data" id="servicebox-data">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($saiiService['update_info_saii'])) {
                                                            $newClass = "serviceContent";
                                                        } else if (isset($saiiService['update_info_saii']) && !empty($saiiService['update_info_saii']) && empty($saiiService['signed_noti_saii'])) {
                                                            $newClass = "serviceContentwaiting";
                                                        } else if (isset($saiiService['signed_noti_saii']) && !empty($saiiService['signed_noti_saii'])) {
                                                            $newClass = "serviceContentCompleted";
                                                        } else {
                                                            $newClass = "serviceContent";
                                                        }
                                                        ?>
                                                        <div class="<?php echo $newClass ?>">
                                                            <span>Signed Notification SA - II Audit</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($saiiService['signed_noti_saii']) && !empty($saiiService['signed_noti_saii'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $saiiService['signed_noti_saii'] ?>" download class="signednotisaiiFileDownload">
                                                                            <i class="fa fa-download"></i>
                                                                        </a>
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($saiiService['servelance_id'])) {
                                                            $newClass = "serviceContent";
                                                        } else if (isset($saiiService['servelance_id']) && !empty($saiiService['servelance_id']) && empty($saiiService['signed_copy_saii'])) {
                                                            $newClass = "serviceContentwaiting";
                                                        } else if (isset($saiiService['signed_copy_saii']) && !empty($saiiService['signed_copy_saii'])) {
                                                            $newClass = "serviceContentCompleted";
                                                        } else {
                                                            $newClass = "serviceContent";
                                                        }
                                                        ?>
                                                        <div class="<?php echo $newClass ?>">
                                                            <span>Schedule Signed Copy</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($saiiService['signed_copy_saii']) && !empty($saiiService['signed_copy_saii'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $saiiService['signed_copy_saii'] ?>" data-id="" download class="signedcopysaiiFileDownload">
                                                                            <i class="fa fa-download"></i>
                                                                        </a>
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($saiiService['signed_copy_saii'])) {
                                                            $newClass = "serviceContent";
                                                        } else if (isset($saiiService['signed_copy_saii']) && !empty($saiiService['signed_copy_saii']) && empty($saiiService['audit_report_saii'])) {
                                                            $newClass = "serviceContentwaiting";
                                                        } else if (isset($saiiService['audit_report_saii']) && !empty($saiiService['audit_report_saii'])) {
                                                            $newClass = "serviceContentCompleted";
                                                        } else {
                                                            $newClass = "serviceContent";
                                                        }
                                                        ?>
                                                        <div class="<?php echo $newClass ?>">
                                                            <span>SA - II Audit Report, Summery and Checklists</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($saiiService['audit_report_saii']) && !empty($saiiService['audit_report_saii'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $saiiService['audit_report_saii'] ?>" data-id="" download class="auditReportFileDownload">
                                                                            <i class="fa fa-download"></i>
                                                                        </a>
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($saiiService['auditor_notes_saii'])) {
                                                            $newClass = "serviceContent";
                                                        } else if (isset($saiiService['auditor_notes_saii']) && !empty($saiiService['auditor_notes_saii']) && empty($saiiService['audit_evidences_saii'])) {
                                                            $newClass = "serviceContentwaiting";
                                                        } else if (isset($saiiService['audit_evidences_saii']) && !empty($saiiService['audit_evidences_saii'])) {
                                                            $newClass = "serviceContentCompleted";
                                                        } else {
                                                            $newClass = "serviceContent";
                                                        }
                                                        ?>
                                                        <div class="<?php echo $newClass ?>">
                                                            <span>SA - II Audit NC And evidences of closer</span>
                                                            <?php if (isset($saiiService['audit_evidences_saii']) && !empty($saiiService['audit_evidences_saii']) && $saiiService['audit_evidences_saii'] == "NONC" && isset($saiiService['auditor_notes_saii']) && !empty($saiiService['auditor_notes_saii'])) { ?> 
                                                                <a class="" title="No NC Flag" style="padding: 1px 5px;">
                                                                    <i class="fa fa-times-circle fa-lg " style="padding: 3px"></i>
                                                                </a>
                                                            <?php } ?>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($saiiService['audit_evidences_saii']) && !empty($saiiService['audit_evidences_saii']) && $saiiService['audit_evidences_saii'] != "NONC" && isset($saiiService['auditor_notes_saii']) && !empty($saiiService['auditor_notes_saii'])) { ?>                                                    
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $saiiService['audit_evidences_saii'] ?>" data-id="" download class="evidencesFileDownload">
                                                                            <i class="fa fa-download"></i>
                                                                        </a>
                                                                    </span>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <?php
                        }
                        if ($services['service_type'] == "RC") {
                            if ($rcCount['service_done'] && $rcCount['service_done'] == "DONE") {
                                $class7 = 'rc-service-type';
                                $class8 = "rc-service-type";
                            } else if (isset($rcCount['service_done']) && $rcCount['service_done'] == "") {
                                $class7 = "rc-service-type";
                                $class8 = "";
                            } else {
                                $class7 = "";
                                $class8 = "rc-service-type";
                            }

                            $rcService = "SELECT strc.rc_id, service, rc_customer, rc_info_update, rc_application_form, rc_certification_aggrement, rc_signed_noti, rc_signed_copy, rc_audit_report, auditor_notes_rc, rc_audit_evidence, certificate_print, rc_customer_review, service_rc_status, strc.audit_date as rcaudit, aoai.audit_stage_id, asi.audit_scheduleii_id, siiai.stage_ii_id,  crr.crr_id, csi.survey_id, cfi.control_id, cf2i.control2_id, cfp.certificate_id FROM service_type_rc strc left join stage_oneaudit_id aoai on strc.rc_id = aoai.ca_service_id  left join audit_scheduleii_id asi on strc.rc_id = asi.`ca_service` left join stage_ii_audit_id siiai on strc.rc_id = siiai.ca_service_id left join cdc_report_review crr on strc.rc_id = crr.ca_service_id left join customer_survey_id csi on strc.rc_id = csi.ca_serviceid left join control_formati_id cfi on strc.rc_id = cfi.caservice_id left join control_formatii_id cf2i on strc.rc_id = cf2i.caservice_id left join certificate_print cfp on strc.rc_id = cfp.service_id WHERE strc.service = :id";
                            $statement = $db->prepare($rcService);
                            $statement->bindParam(':id', $services["service_id"]);
                            $statement->execute();
                            $rcServiceinfo = $statement->fetch();

                            if ($letestService["c_service"] == $rcServiceinfo['service']) {
                                $disp = "";
                            } else {
                                $disp = "none";
                            }
                            ?>
                            <div class="servicebox-bunch <?php echo $class7 ?>" style="display:">
                                <div class="servicebox" style="display: ">
                                    <form id="saiService" action="serviceTypeSAi.php" method="post" enctype="multipart/form-data">
                                        <div class="row rc-service-row" id="service-row" data-id="<?php echo $rcServiceinfo['rc_id'] ?>">
                                            <div class="col-xs-12 qms_customer_name service_title" style="text-align: center" data-row-id="">
                                                Service type - RC <span class="serviceStatus"></span> 
                                                <a class="deleteServiceRC" id="deleteServiceRC" data-service="<?php echo $rcServiceinfo['service'] ?>" data-row-id="<?php echo $rcServiceinfo['rc_id'] ?>" data-customer="<?php echo $_GET['customerid'] ?>">
                                                    <i class="fa fa-trash-o pull-right " style="color: white; padding-right: 10px;"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="rc-service-desc<?php echo $rcServiceinfo['rc_id'] ?>" style="display: <?php // echo $disp                                ?>;">
                                            <div class="row" style="padding-top: 10px; ">
                                                <div class="col-sm-4 " style="color: black; font-weight: small; ">
                                                    <span>Add to: <?php echo $rcServiceinfo['service_rc_status']; ?></span>
                                                </div>
                                                <div class="col-sm-4 " style="color: black; font-weight: small; ">
                                                    <span>Audit Date: <?php echo date("d.m.Y", strtotime($rcServiceinfo["rcaudit"])); ?></span>
                                                </div>
                                            </div>
                                            <div class="servicebox-data" id="servicebox-data">                                    
                                                <div class="row">                                
                                                    <div class="col-sm-6">
                                                        <div class="serviceContentCompleted">
                                                            <span>Signed Notification RC Audit </span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($rcServiceinfo['rc_signed_noti']) && !empty($rcServiceinfo['rc_signed_noti'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $rcServiceinfo['rc_signed_noti'] ?>" data-id="" download class="customerReviewFileDownload">
                                                                            <i class="fa fa-download"></i>
                                                                        </a>
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (isset($rcServiceinfo['rc_signed_noti']) && !empty($rcServiceinfo['rc_signed_noti']) && empty($rcServiceinfo['rc_signed_copy'])) {
                                                            $rcSignCopyClass = 'serviceContentwaiting';
                                                        } else if (isset($rcServiceinfo['rc_signed_noti']) && !empty($rcServiceinfo['rc_signed_noti']) && isset($rcServiceinfo['rc_signed_copy']) && !empty($rcServiceinfo['rc_signed_copy'])) {
                                                            $rcSignCopyClass = 'serviceContentCompleted';
                                                        }
                                                        ?>
                                                        <div class="<?php echo $rcSignCopyClass ?>">
                                                            <span>Schedule Signed Copy</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($rcServiceinfo['rc_signed_copy']) && !empty($rcServiceinfo['rc_signed_copy'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $rcServiceinfo['rc_signed_copy'] ?>" data-id="" download class="customerReviewFileDownload">
                                                                            <i class="fa fa-download"></i>
                                                                        </a>
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">                                
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($rcServiceinfo['rc_signed_copy'])) {
                                                            $rcAudit2Class = 'serviceContent';
                                                        } else if (isset($rcServiceinfo['rc_signed_copy']) && !empty($rcServiceinfo['rc_signed_copy']) && empty($rcServiceinfo['rc_audit_report'])) {
                                                            $rcAudit2Class = 'serviceContentwaiting';
                                                        } else if (isset($rcServiceinfo['rc_signed_copy']) && !empty($rcServiceinfo['rc_signed_copy']) && isset($rcServiceinfo['rc_audit_report']) && !empty($rcServiceinfo['rc_audit_report'])) {
                                                            $rcAudit2Class = 'serviceContentCompleted';
                                                        } else {
                                                            $rcAudit2Class = 'serviceContent';
                                                        }
                                                        ?>
                                                        <div class="<?php echo $rcAudit2Class ?>">
                                                            <span>RC Audit Report, Summery and Checklists/Recertification</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($rcServiceinfo['rc_audit_report']) && !empty($rcServiceinfo['rc_audit_report'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $rcServiceinfo['rc_audit_report'] ?>" data-id="" download class="customerReviewFileDownload">
                                                                            <i class="fa fa-download"></i>
                                                                        </a>
                                                                    </span> 
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($rcServiceinfo['auditor_notes_rc'])) {
                                                            $rcEvidenceClass = 'serviceContent';
                                                        } else if (isset($rcServiceinfo['auditor_notes_rc']) && !empty($rcServiceinfo['auditor_notes_rc']) && empty($rcServiceinfo['rc_audit_evidence'])) {
                                                            $rcEvidenceClass = 'serviceContentwaiting';
                                                        } else if (isset($rcServiceinfo['auditor_notes_rc']) && !empty($rcServiceinfo['auditor_notes_rc']) && isset($rcServiceinfo['rc_audit_evidence']) && !empty($rcServiceinfo['rc_audit_evidence'])) {
                                                            $rcEvidenceClass = 'serviceContentCompleted';
                                                        } else {
                                                            $rcEvidenceClass = 'serviceContent';
                                                        }
                                                        ?>
                                                        <div class="<?php echo $rcEvidenceClass ?>">
                                                            <span>RC Audit NC And evidence of closer</span>
                                                            <?php if (isset($rcServiceinfo['rc_audit_evidence']) && !empty($rcServiceinfo['rc_audit_evidence']) && $rcServiceinfo['rc_audit_evidence'] == "NONC" && isset($rcServiceinfo['auditor_notes_rc']) && !empty($rcServiceinfo['auditor_notes_rc'])) { ?> 
                                                                <a class="" title="No NC Flag" style="padding: 1px 5px;">
                                                                    <i class="fa fa-times-circle fa-lg " style="padding: 3px"></i>
                                                                </a>
                                                            <?php } ?>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($rcServiceinfo['rc_audit_evidence']) && !empty($rcServiceinfo['rc_audit_evidence']) && $rcServiceinfo['rc_audit_evidence'] != "NONC" && isset($rcServiceinfo['auditor_notes_rc']) && !empty($rcServiceinfo['auditor_notes_rc'])) { ?>                                                    
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $rcServiceinfo['rc_audit_evidence'] ?>" data-id="" download class="customerReviewFileDownload">
                                                                            <i class="fa fa-download"></i>
                                                                        </a>
                                                                    </span>  
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <?php
                        }
                        if ($services['service_type'] == "SP") {
                            if (isset($spCount['service_done']) && $spCount['service_done'] == "DONE") {
                                $class9 = 'sp-service-type';
                                $class10 = "sp-service-type";
                            } else if (isset($spCount['service_done']) && $spCount['service_done'] == "") {
                                $class9 = "sp-service-type";
                                $class10 = "";
                            } else {
                                $class9 = "";
                                $class10 = "sp-service-type";
                            }

                            $spService = "SELECT sp.sp_id, sp.service, sp_signed_noti, certificate_option, sp_signed_copy, sp_audit_report, service_sp_status, sp.audit_date as spaudit, soi.audit_stage_id, asi.audit_schedule_id, siea.stage_ii_id, crr.crr_id, cfp.certificate_id, ca.id, aggrement_date, aggrement_no FROM service_type_sp sp left join stage_oneaudit_id soi on sp.sp_id = soi.ca_service_id left join audit_schedule_id asi on sp.sp_id = asi.serviceca_id left join stage_ii_audit_id siea on sp.sp_id = siea.ca_service_id left join cdc_report_review crr on sp.sp_id = crr.ca_service_id left join certificate_print cfp on sp.sp_id = cfp.service_id left join certification_aggrement ca on sp.sp_id = ca.service WHERE sp.service = :id";
                            $statement = $db->prepare($spService);
                            $statement->bindParam(':id', $services["service_id"]);
                            $statement->execute();
                            $spservices = $statement->fetch();

                            if ($letestService["c_service"] == $spservices['service']) {
                                $disp = "";
                            } else {
                                $disp = "none";
                            }
                            ?> 
                            <div class="servicebox-bunch <?php echo $class9 ?>" style="display: ">
                                <div class="servicebox" style="display: ">
                                    <form id="spService" action="serviceTypeSP.php" method="post" enctype="multipart/form-data">
                                        <div class="row sp-service-row" id="service-row" data-id="<?php echo $spservices['sp_id'] ?>">
                                            <div class="col-xs-12 qms_customer_name service_title" style="text-align: center" data-row-id="">
                                                Service type - SP<span class="serviceStatus"></span>
                                                <a class="deleteServiceSP" id="deleteServiceSP" data-service="<?php echo $spservices['service'] ?>" data-row-id="<?php echo $spservices['sp_id'] ?>" data-customer="<?php echo $_GET['customerid'] ?>">
                                                    <i class="fa fa-trash-o pull-right " style="color: white; padding-right: 10px;"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="sp-service-desc<?php echo $spservices['sp_id'] ?>" style="display: <?php // echo $disp                              ?>;">
                                            <div class="servicebox-data" id="servicebox-data">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="serviceContentCompleted"><span>Signed Notification SP Audit</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <span style="padding-right: 5px"> 
                                                                    <a href="<?php echo $spservices['sp_signed_noti'] ?>" data-id="" download class="signednotisaiiFileDownload">
                                                                        <i class="fa fa-download"></i>
                                                                    </a> 
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6"><?php
                                                        if ((isset($spservices['sp_signed_noti']) && !empty($spservices['sp_signed_noti']) || (isset($spservices['certificate_option']) && !empty($spservices['certificate_option']))) && empty($spservices['sp_signed_copy'])) {
                                                            $newClass = "serviceContentwaiting";
                                                        } else if (isset($spservices['sp_signed_copy']) && !empty($spservices['sp_signed_copy'])) {
                                                            $newClass = "serviceContentCompleted";
                                                        }
                                                        ?>
                                                        <div class="<?php echo $newClass ?>"><span>Upload Schedule</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($spservices['sp_signed_copy']) && !empty($spservices['sp_signed_copy']) && $spservices['sp_signed_copy'] != "NONC") { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $spservices['sp_signed_copy'] ?>" data-id="" download class="signednotisaiiFileDownload">
                                                                            <i class="fa fa-download"></i>
                                                                        </a> 
                                                                    </span>
                                                                <?php } ?>
                                                            </div>                                                
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <?php
                                                        if (empty($spservices['sp_signed_copy'])) {
                                                            $newClass = "serviceContent";
                                                        } else if (isset($spservices['sp_signed_copy']) && !empty($spservices['sp_signed_copy']) && empty($spservices['sp_audit_report'])) {
                                                            $newClass = "serviceContentwaiting";
                                                        } else if (isset($spservices['sp_audit_report']) && !empty($spservices['sp_audit_report'])) {
                                                            $newClass = "serviceContentCompleted";
                                                        } else {
                                                            $newClass = "serviceContent";
                                                        }
                                                        ?>
                                                        <div class="<?php echo $newClass ?>">
                                                            <span> Audit Report</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($spservices['sp_audit_report']) && !empty($spservices['sp_audit_report'])) { ?>
                                                                    <span style="padding-right: 5px"> 
                                                                        <a href="<?php echo $spservices['sp_audit_report'] ?>" data-id="" download class="auditReportFileDownload"><i class="fa fa-download"></i></a> 
                                                                    </span>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div> 
                            <?php
                        }
                        if ($services["service_type"] == "PTR") {
                            $ptrService = "SELECT ptr.prt_id, service, ptr.customer_id, audit_report, service_status, audit_date, soi.audit_stage_id, s1i.servelance_id, siai.stage_i_id FROM service_type_ptr ptr left join stage_oneaudit_id soi on ptr.prt_id = soi.ca_service_id left join servelance_1_id s1i on ptr.prt_id = s1i.service_id left join stage_i_audit_id siai on ptr.prt_id = siai.ca_service_id WHERE ptr.service = :id";
                            $statement = $db->prepare($ptrService);
                            $statement->bindParam(':id', $services["service_id"]);
                            $statement->execute();
                            $ptrservices = $statement->fetch();

                            if ($letestService["c_service"] == $ptrservices['service']) {
                                $disp = "";
                            } else {
                                $disp = "none";
                            }
                            ?>
                            <div class="servicebox-bunch <?php echo $class1 ?>" style="display: ">
                                <div class="servicebox">
                                    <form id="caService" action="serviceTypeCA.php" method="post" enctype="multipart/form-data">
                                        <div class="row ptr-service-row" id="service-row" data-id="<?php echo $ptrservices['prt_id'] ?>">
                                            <div class="col-xs-12 qms_customer_name service_title" style="text-align: center" data-row-id="">Service type - PTR<span class="serviceStatus"></span> <!--<a class="saveServiceCA" id="saveServiceCA" data-row-id=""><i class="fa fa-floppy-o pull-right " style="color:white; padding-right:10px; display:"></i></a>-->
                                                <a class="deleteServicePTR" id="deleteServicePTR" data-service="<?php echo $ptrservices['service'] ?>" data-row-id="<?php echo $ptrservices['prt_id'] ?>" data-customer="<?php echo $_GET['customerid'] ?>"><i class="fa fa-trash-o pull-right " style="color: white; padding-right: 10px;"></i></a>
                                            </div>
                                        </div>
                                        <div class="ptr-service-desc<?php echo $ptrservices['prt_id'] ?>" style="display: <?php //echo $disp                           ?>;">
                                            <div class="servicebox-data" id="servicebox-data ">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="serviceContentCompleted"><span>Audit report upload</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <span style="padding-right: 5px"> 
                                                                    <a href="<?php echo $ptrservices['audit_report'] ?>" data-id="" download class="customerReviewFileDownload"><i class="fa fa-download"></i></a> 
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <?php
                        }
                        if ($service['service_type'] == "TA") {
                            $taService = "SELECT ta.ta_id, ta.service_id, signed_notification, release_schedule, audit_report, review_report, service_status, ta.audit_date as taaudit, soi.audit_stage_id, asi.audit_schedule_id, siai.stage_i_id FROM service_type_ta ta left join stage_oneaudit_id soi on ta.ta_id = soi.ca_service_id LEFT JOIN audit_schedule_id asi on ta.ta_id = asi.serviceca_id LEFT JOIN stage_i_audit_id siai on ta.ta_id = siai.ca_service_id WHERE ta.service_id = :id";
                            $statement = $db->prepare($taService);
                            $statement->bindParam(':id', $service["service_id"]);
                            $statement->execute();
                            $taservices = $statement->fetch();
                            if ($letestService["c_service"] == $taservices['service_id']) {
                                $disp = "";
                            } else {
                                $disp = "none";
                            }
                            ?> 
                            <div class="servicebox-bunch" style="display: ">
                                <div class="servicebox" style="display: ">
                                    <form id="spService" action="serviceTypeSP.php" method="post" enctype="multipart/form-data">
                                        <div class="row ta-service-row" id="service-row" data-id="<?php echo $taservices['ta_id'] ?>">
                                            <div class="col-xs-12 qms_customer_name service_title" style="text-align: center" data-row-id="">
                                                Transition Audit<span class="serviceStatus"></span>
                                                <a class="deleteServiceTA" id="deleteServiceTA" data-service="<?php echo $taservices['service_id'] ?>" data-row-id="<?php echo $taservices['ta_id'] ?>" data-customer="<?php echo $_GET['customerid'] ?>">
                                                    <i class="fa fa-trash-o pull-right " style="color: white; padding-right: 10px;"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="ta-service-desc<?php echo $taservices['ta_id'] ?>" style="display: <?php echo $disp ?>;">
                                            <div class="servicebox-data" id="servicebox-data">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="serviceContentCompleted"><span>Signed Notification</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <span style="padding-right: 5px"> 
                                                                    <a href="<?php echo $taservices['signed_notification'] ?>" data-id="" download class="signednotisaiiFileDownload">
                                                                        <i class="fa fa-download"></i>
                                                                    </a> 
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6"><?php
                                                        if (isset($taservices['signed_notification']) && !empty($taservices['signed_notification']) && empty($taservices['audit_report'])) {
                                                            $newClass = "serviceContentwaiting";
                                                        } else if (isset($taservices['audit_report']) && !empty($taservices['audit_report'])) {
                                                            $newClass = "serviceContentCompleted";
                                                        } else {
                                                            $newClass = "serviceContent";
                                                        }
                                                        ?>
                                                        <div class="<?php echo $newClass ?>">
                                                            <span> Audit Report</span>
                                                            <div style="float: right" class="image-preview form-inline">
                                                                <?php if (isset($taservices['audit_report']) && !empty($taservices['audit_report'])) { ?>
                                                                    <span style="padding-right: 5px">
                                                                        <a href="<?php echo $taservices['audit_report'] ?>" data-id="" download class="signednotisaiiFileDownload">
                                                                            <i class="fa fa-download"></i>
                                                                        </a> 
                                                                    </span>
                                                                <?php } ?>
                                                            </div>                                                                                                
                                                        </div>
                                                    </div>
                                                </div>                                  
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div> 
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>            
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#menu-toggle').off('click.clientPortalLock').on('click.clientPortalLock', function (e) {
            e.preventDefault();
            $('#wrapper').addClass('active client-portal-layout');
            $('.container').addClass('active');
        });

        $('#wrapper').addClass('active client-portal-layout');
        $('.container').addClass('active');
    });
</script>

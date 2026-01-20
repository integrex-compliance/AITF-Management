<!--<div id="wrapper" class="active">-->
<div id="wrapper" class="">
    <?php
    include_once './header.php';
    include_once './dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');
    $certifcate = "";
    $nextService = "";
    if (isset($_SESSION["username"]) && !empty($_SESSION["username"]) && $_SESSION["role"] == 'External') {
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
                                            <div class="item"><b>Proposed Plan</b></div>
                                            <div class="item"><b>Actual Due</b></div>
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
                                    <span><b>NOTE:</b></span><br>
                                    <span style="color: black;">1.The above mentioned dates in the column of <b>"proposed plan"</b> are provided with intention to give information about plan of next audit.</span><br>
                                    <span style="color: black">2.The date/s mentioned in the column of <b>"actual due"</b> does it means the relevant reminder/letter has been issued by AITF.</span><br>
                                    <span style="color: black">3.For any queries please contact us on <b>"aitfmcl@gmail.com"</b>.</span>
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
                                    <?php echo $nextService ?>
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
                        <div class="col-sm-6" style="color: black; font-weight: small; border-left: 1px solid slategray; height: 140px;">
                            <div class="row">
                                <div class="col-sm-12">
                                    Latest Certificate copy:
                                    <?php
                                    if ($svctype["service_type"] == "CA") {
                                        $selectsvc = "SELECT `certificate_print` FROM `service_type_ca` WHERE `service` = :id";
                                        $statement = $db->prepare($selectsvc);
                                        $statement->bindParam(':id', $scid['id']);
                                        $statement->execute();
                                        $certifcate = $statement->fetch();
                                        if ($certifcate["certificate_print"] == "") {
                                            ?>
                                            <span>Certificate Is Not Uploaded</span> 
                                        <?php } else {
                                            ?>
                                            <span style="padding-left: 20px">                             
                                                <a href="<?php echo $certifcate["certificate_print"] ?>" download class="" title="Download Letest Certficate (CA)"><i class="fa fa-download"></i></a>
                                            </span>
                                            <?php
                                        }
                                    } else if ($svctype["service_type"] == "SA-I") {
                                        $selectsvc = "SELECT `certificate_print` FROM `service_type_sai` WHERE `service` = :id";
                                        $statement = $db->prepare($selectsvc);
                                        $statement->bindParam(':id', $scid['id']);
                                        $statement->execute();
                                        $certifcate = $statement->fetch();

                                        if ($certifcate["certificate_print"] == "") {
                                            $maxsvcid = "SELECT max(service_id) as id FROM `service` WHERE service_id < (SELECT max(service_id) FROM `service`) AND customer_id = :id AND (service_type != 'CR' AND service_type != 'TA' AND service_type != 'PTR' AND service_type != 'SP')";
                                            $statement = $db->prepare($maxsvcid);
                                            $statement->bindParam(':id', $_SESSION['id']);
                                            $statement->execute();
                                            $caid = $statement->fetch();

                                            $selectsvc = "SELECT `certificate_print` FROM `service_type_ca` WHERE `service` = :id";
                                            $statement = $db->prepare($selectsvc);
                                            $statement->bindParam(':id', $caid['id']);
                                            $statement->execute();
                                            $certifcate = $statement->fetch();
                                            ?>
                                            <span style="padding-left: 20px">                             
                                                <a href="<?php echo $certifcate["certificate_print"] ?>" download class="" title="Download Letest Certficate (CA)"><i class="fa fa-download"></i></a>
                                            </span>
                                        <?php } else { ?>
                                            <span style="padding-left: 20px">                             
                                                <a href="<?php echo $certifcate["certificate_print"] ?>" download class="" title="Download Letest Certficate (SA-I)"><i class="fa fa-download"></i></a>
                                            </span>
                                            <?php
                                        }
                                    } else if ($svctype["service_type"] == "SA-II") {
                                        $selectsvc = "SELECT `certificate_print` FROM `service_type_saii` WHERE `service` = :id";
                                        $statement = $db->prepare($selectsvc);
                                        $statement->bindParam(':id', $scid['id']);
                                        $statement->execute();
                                        $certifcate = $statement->fetch();

                                        if ($certifcate["certificate_print"] == "") {
                                            $maxsvcid = "SELECT max(service_id) as id FROM `service` WHERE service_id < (SELECT max(service_id) FROM `service`) AND customer_id = :id AND (service_type != 'CR' AND service_type != 'TA' AND service_type != 'PTR' AND service_type != 'SP')";
                                            $statement = $db->prepare($maxsvcid);
                                            $statement->bindParam(':id', $_SESSION['id']);
                                            $statement->execute();
                                            $saIid = $statement->fetch();

                                            $selectsvc = "SELECT `certificate_print` FROM `service_type_ca` WHERE `service` = :id";
                                            $statement = $db->prepare($selectsvc);
                                            $statement->bindParam(':id', $saIid['id']);
                                            $statement->execute();
                                            $certifcate = $statement->fetch();
                                            ?>
                                            <span style="padding-left: 20px">
                                                <a href="<?php echo $certifcate["certificate_print"] ?>" download class="" title="Download Letest Certficate (SA-I)"><i class="fa fa-download"></i></a>
                                            </span>
                                        <?php } else {
                                            ?>
                                            <span style="padding-left: 20px">
                                                <a href="<?php echo $certifcate["certificate_print"] ?>" download class="" title="Download Letest Certficate (SA-II)"><i class="fa fa-download"></i></a>
                                            </span>
                                            <?php
                                        }
                                    } else if ($svctype["service_type"] == "RC") {
                                        $selectsvc = "SELECT `certificate_print` FROM `service_type_rc` WHERE `service` = :id";
                                        $statement = $db->prepare($selectsvc);
                                        $statement->bindParam(':id', $scid['id']);
                                        $statement->execute();
                                        $certifcate = $statement->fetch();

                                        if ($certifcate["certificate_print"] == "") {
                                            $maxsvcid = "SELECT max(service_id) as id FROM `service` WHERE service_id < (SELECT max(service_id) FROM `service`) AND customer_id = :id AND (service_type != 'CR' AND service_type != 'TA' AND service_type != 'PTR' AND service_type != 'SP')";
                                            $statement = $db->prepare($maxsvcid);
                                            $statement->bindParam(':id', $_SESSION['id']);
                                            $statement->execute();
                                            $saIIid = $statement->fetch();

                                            $selectsvc = "SELECT `certificate_print` FROM `service_type_ca` WHERE `service` = :id";
                                            $statement = $db->prepare($selectsvc);
                                            $statement->bindParam(':id', $saIIid['id']);
                                            $statement->execute();
                                            $certifcate = $statement->fetch();
                                            ?>
                                            <span style="padding-left: 20px">
                                                <a href="<?php echo $certifcate["certificate_print"] ?>" download class="" title="Download Letest Certficate (SA-II)"><i class="fa fa-download"></i></a>
                                            </span>
                                        <?php } else { ?>
                                            <span style="padding-left: 20px">
                                                <a href="<?php echo $certifcate["certificate_print"] ?>" download class="" title="Download Letest Certficate (RC)"><i class="fa fa-download"></i></a>
                                            </span>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div> 
                            <br>
                            <div class="row">
                                <div class="col-sm-12">
                                    Guideline to use logo:
                                    <span style="padding-left: 20px">
                                        <a href="<?php echo $guidelines["logo_guidelines"] ?>" data-id="" download class="customerReviewFileDownload" title="Download Certification Agreement"><i class="fa fa-download"></i></a>
                                    </span>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-sm-12">
                                    Logos Coral/ PDF:
                                    <span style="padding-left: 20px">
                                        <a href="<?php echo $guidelines["logo_corol"] ?>" data-id="" download class="customerReviewFileDownload" title="Download Certification Agreement"><i class="fa fa-download"></i></a>
                                    </span>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-sm-12">
                                    NC closure report Format:
                                    <span style="padding-left: 20px">
                                        <a href="<?php echo $guidelines["nc_report_format"] ?>" data-id="" download class="customerReviewFileDownload" title="Download Certification Agreement"><i class="fa fa-download"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr style="color: slategray; margin: 0px; border: 1px solid slategray" />
                </div>
            </div>            
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {

    });
</script>
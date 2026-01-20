<div id="wrapper" class="active">
    <?php
//    session_start();
    $pageIdentifier = "audit perf";
    include 'header.php';
    include 'dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    $sql = "SELECT ci.id, customer_name, address, contact, email_id, scope, aif_nace, svc.service_id, service_type, svc.audit_date FROM customer_info ci join service svc on ci.id = svc.customer_id WHERE is_deleted <> 'Y' ORDER BY svc.audit_date DESC";
    $statement = $db->prepare($sql);
    $statement->execute();
    $listOfCustomer = $statement->fetchAll();
    //    print_r($listOfCustomer);

    $services = "SELECT * FROM service_types";
    $statement = $db->prepare($services);
    $statement->execute();
    $servicesTypes = $statement->fetchAll();
    ?>

    <!-- Sidebar -->
    <!-- Sidebar -->

    <style>
        .custom-float-label-control {
            margin-bottom: 0em;
        }
    </style>
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" >
                <div class="row" style="padding-top:10px;">
                    <div class="col-sm-1"><a class="btn btn-info filterBtn"><i class="fa fa-filter "></i></a></div>                            
                    <div class="col-sm-3 float-label-control custom-float-label-control">
                        <select name="service-type" class="form-control service-type" id="service-type">
                            <option value="">Select Service Type</option>
                            <?php foreach ($servicesTypes as $service) { ?>
                                <option value="<?php echo $service['service_type'] ?>">
                                    <?php echo $service['service_type'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-3 float-label-control custom-float-label-control">
                        <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="Audit Date From" value="">
                    </div>
                    <div class="col-sm-3 form-group float-label-control custom-float-label-control">
                        <input type="text" class="filterToDate" id="filterToDate" name="filterToDate" placeholder="Audit Date To" value="">
                    </div>
                </div>
                <div class="row clearfix" style="padding-top:10px;">
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list" width="100%">
                            <thead>
                                <tr>                                    
                                    <th class="">
                                        <small><a>Client</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Service Type</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Audit Date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Rating to auditing quality (A)(Out of 50 Mark)</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>AITF certification activities (B)(Out of 35 Mark)</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Auditor</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Individual marks</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Rating for assessment Process (i.e.(u) of A & B</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>NC</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>OFI</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>O+</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($listOfCustomer as $customer) {
                                    if ($customer["service_type"] == "CA") {
                                        $sql1 = "SELECT * FROM `service_type_ca` where service = :service";
                                        $statement = $db->prepare($sql1);
                                        $statement->bindParam(":service", $customer["service_id"]);
                                        $statement->execute();
                                        $caserviceid = $statement->fetch();

                                        $sql2 = "SELECT * FROM `customer_survey_id` WHERE `ca_serviceid` = :svce";
                                        $statement = $db->prepare($sql2);
                                        $statement->bindParam(":svce", $caserviceid["ca_id"]);
                                        $statement->execute();
                                        $ca_cdc = $statement->fetch();

                                        $sql02 = "SELECT * FROM `customer_survey` where survayid = :survayid";
                                        $statement = $db->prepare($sql02);
                                        $statement->bindParam(":survayid", $ca_cdc["survey_id"]);
                                        $statement->execute();
                                        $cdc_survay = $statement->fetchAll();
                                        $survayAuditor = "";
                                        $survayMarks = "";
                                        foreach ($cdc_survay as $survay) {
                                            $survayAuditor = $survayAuditor . $survay["lead_auditor"] . ", ";
                                            $survayMarks = $survayMarks . $survay["marks"] . ", ";
                                        }

                                        $sql002 = "SELECT * FROM `cdc_report_review` WHERE `ca_service_id` = :svce";
                                        $statement = $db->prepare($sql002);
                                        $statement->bindParam(":svce", $caserviceid["ca_id"]);
                                        $statement->execute();
                                        $ca_cdcval = $statement->fetch();
                                        $caauditdate = explode(',', $caserviceid['stage2_audit']);
                                        if ($ca_cdcval) {
                                            ?>
                                            <tr class="customerAccount" data-id="<?php echo $customer['id'] ?>"  >
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $customer['customer_name'] ?></a></small>
                                                </td>
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $customer["service_type"] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <span><?php echo simpleDateConversion($customer["audit_date"]); ?>~</span><small><a ><?php echo simpleDateConversion($customer["audit_date"]) ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $ca_cdc['auditing_quality'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $ca_cdc['mgmt_satisfaction'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $survayAuditor; ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $survayMarks; ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $ca_cdc['aggregate'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $ca_cdcval['ncval'] ?></a></small>
                                                </td>
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $ca_cdcval['ofival'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $ca_cdcval['oplusval'] ?></a></small>
                                                </td>                                                
                                            </tr>
                                            <?php
                                        }
                                    } else if ($customer["service_type"] == "SA-I") {
                                        $sql1 = "SELECT * FROM service_type_sai WHERE service = :service";
                                        $statement = $db->prepare($sql1);
                                        $statement->bindParam(":service", $customer["service_id"]);
                                        $statement->execute();
                                        $saiserviceid = $statement->fetch();

                                        $sql2 = "SELECT * FROM `customer_survey_id` WHERE `ca_serviceid` = :svce";
                                        $statement = $db->prepare($sql2);
                                        $statement->bindParam(":svce", $saiserviceid["sai_id"]);
                                        $statement->execute();
                                        $sai_cdc = $statement->fetch();

                                        $sql02 = "SELECT * FROM `customer_survey` where survayid = :survayid";
                                        $statement = $db->prepare($sql02);
                                        $statement->bindParam(":survayid", $sai_cdc["survey_id"]);
                                        $statement->execute();
                                        $cdc_survay = $statement->fetchAll();
                                        $survayAuditor = "";
                                        $survayMarks = "";
                                        foreach ($cdc_survay as $survay) {
                                            $survayAuditor = $survayAuditor . $survay["lead_auditor"] . ", ";
                                            $survayMarks = $survayMarks . $survay["marks"] . ", ";
                                        }

                                        $sql002 = "SELECT * FROM `cdc_report_review` WHERE `ca_service_id` = :svce";
                                        $statement = $db->prepare($sql002);
                                        $statement->bindParam(":svce", $saiserviceid["sai_id"]);
                                        $statement->execute();
                                        $sai_cdcval = $statement->fetch();

                                        $saiauditdate = explode(',', $saiserviceid['audit_date']);

                                        if ($sai_cdcval) {
                                            ?>
                                            <tr class="customerAccount" data-id="<?php echo $customer['id'] ?>"  >
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $customer['customer_name'] ?></a></small>
                                                </td>
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $customer["service_type"] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <span><?php echo simpleDateConversion($customer["audit_date"]); ?>~</span><small><a ><?php echo simpleDateConversion($customer["audit_date"]) ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sai_cdc['auditing_quality'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sai_cdc['mgmt_satisfaction'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $survayAuditor; ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $survayMarks; ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sai_cdc['aggregate'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sai_cdcval['ncval'] ?></a></small>
                                                </td>
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sai_cdcval['ofival'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sai_cdcval['oplusval'] ?></a></small>
                                                </td>                                               
                                            </tr>
                                            <?php
                                        }
                                    } else if ($customer["service_type"] == "SA-II") {
                                        $sql1 = "SELECT * FROM service_type_saii WHERE service = :service";
                                        $statement = $db->prepare($sql1);
                                        $statement->bindParam(":service", $customer["service_id"]);
                                        $statement->execute();
                                        $saiiserviceid = $statement->fetch();

                                        $sql2 = "SELECT * FROM `customer_survey_id` WHERE `ca_serviceid` = :svce";
                                        $statement = $db->prepare($sql2);
                                        $statement->bindParam(":svce", $saiiserviceid["saii_id"]);
                                        $statement->execute();
                                        $saii_cdc = $statement->fetch();

                                        $sql02 = "SELECT * FROM `customer_survey` where survayid = :survayid";
                                        $statement = $db->prepare($sql02);
                                        $statement->bindParam(":survayid", $saii_cdc["survey_id"]);
                                        $statement->execute();
                                        $cdc_survay = $statement->fetchAll();
                                        $survayAuditor = "";
                                        $survayMarks = "";

                                        foreach ($cdc_survay as $survay) {
                                            $survayAuditor = $survayAuditor . $survay["lead_auditor"] . ", ";
                                            $survayMarks = $survayMarks . $survay["marks"] . ", ";
                                        }

                                        $sql002 = "SELECT * FROM `cdc_report_review` WHERE `ca_service_id` = :svce";
                                        $statement = $db->prepare($sql002);
                                        $statement->bindParam(":svce", $saiiserviceid["saii_id"]);
                                        $statement->execute();
                                        $saii_cdcval = $statement->fetch();

                                        $saiiauditdate = explode(',', $saiiserviceid['audit_date']);

                                        if ($saii_cdcval) {
                                            ?>
                                            <tr class="customerAccount" data-id="<?php echo $customer['id'] ?>"  >
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $customer['customer_name'] ?></a></small>
                                                </td>
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $customer["service_type"] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <span><?php echo simpleDateConversion($customer["audit_date"]); ?>~</span><small><a ><?php echo simpleDateConversion($customer["audit_date"]) ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $saii_cdc['auditing_quality'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $saii_cdc['mgmt_satisfaction'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $survayAuditor; ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $survayMarks; ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $saii_cdc['aggregate'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $saii_cdcval['ncval'] ?></a></small>
                                                </td>
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $saii_cdcval['ofival'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $saii_cdcval['oplusval'] ?></a></small>
                                                </td>                                               
                                            </tr>
                                            <?php
                                        }
                                    } else if ($customer["service_type"] == "RC") {
                                        $sql1 = "SELECT * FROM service_type_rc WHERE service = :service";
                                        $statement = $db->prepare($sql1);
                                        $statement->bindParam(":service", $customer["service_id"]);
                                        $statement->execute();
                                        $rcserviceid = $statement->fetch();

                                        $sql2 = "SELECT * FROM `customer_survey_id` WHERE `ca_serviceid` = :svce";
                                        $statement = $db->prepare($sql2);
                                        $statement->bindParam(":svce", $rcserviceid["rc_id"]);
                                        $statement->execute();
                                        $rc_cdc = $statement->fetch();

                                        $sql02 = "SELECT * FROM `customer_survey` where survayid = :survayid";
                                        $statement = $db->prepare($sql02);
                                        $statement->bindParam(":survayid", $rc_cdc["survey_id"]);
                                        $statement->execute();
                                        $cdc_survay = $statement->fetchAll();
                                        $survayAuditor = "";
                                        $survayMarks = "";

                                        foreach ($cdc_survay as $survay) {
                                            $survayAuditor = $survayAuditor . $survay["lead_auditor"] . ", ";
                                            $survayMarks = $survayMarks . $survay["marks"] . ", ";
                                        }

                                        $sql002 = "SELECT * FROM `cdc_report_review` WHERE `ca_service_id` = :svce";
                                        $statement = $db->prepare($sql002);
                                        $statement->bindParam(":svce", $rcserviceid["rc_id"]);
                                        $statement->execute();
                                        $rc_cdcval = $statement->fetch();

                                        $rcauditdate = explode(',', $rcserviceid['audit_date']);

                                        if ($rc_cdcval) {
                                            ?>
                                            <tr class="customerAccount" data-id="<?php echo $customer['id'] ?>"  >
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $customer['customer_name'] ?></a></small>
                                                </td>
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $customer["service_type"] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <span><?php echo simpleDateConversion($customer["audit_date"]); ?>~</span><small><a ><?php echo simpleDateConversion($customer["audit_date"]) ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $rc_cdc['auditing_quality'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $rc_cdc['mgmt_satisfaction'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $survayAuditor; ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $survayMarks; ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $rc_cdc['aggregate'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $rc_cdcval['ncval'] ?></a></small>
                                                </td>
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $rc_cdcval['ofival'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $rc_cdcval['oplusval'] ?></a></small>
                                                </td>                                                
                                            </tr>
                                            <?php
                                        }
                                    } else if ($customer["service_type"] == "SP") {
                                        $sql1 = "SELECT * FROM service_type_sp WHERE service = :service";
                                        $statement = $db->prepare($sql1);
                                        $statement->bindParam(":service", $customer["service_id"]);
                                        $statement->execute();
                                        $spserviceid = $statement->fetch();

                                        $sql2 = "SELECT * FROM `customer_survey_id` WHERE `ca_serviceid` = :svce";
                                        $statement = $db->prepare($sql2);
                                        $statement->bindParam(":svce", $spserviceid["sp_id"]);
                                        $statement->execute();
                                        $sp_cdc = $statement->fetch();

                                        $sql02 = "SELECT * FROM `customer_survey` where survayid = :survayid";
                                        $statement = $db->prepare($sql02);
                                        $statement->bindParam(":survayid", $ca_cdc["survey_id"]);
                                        $statement->execute();
                                        $cdc_survay = $statement->fetchAll();
                                        $survayAuditor = "";
                                        $survayMarks = "";

                                        foreach ($cdc_survay as $survay) {
                                            $survayAuditor = $survayAuditor . $survay["lead_auditor"] . ", ";
                                            $survayMarks = $survayMarks . $survay["marks"] . ", ";
                                        }

                                        $sql002 = "SELECT * FROM `cdc_report_review` WHERE `ca_service_id` = :svce";
                                        $statement = $db->prepare($sql002);
                                        $statement->bindParam(":svce", $spserviceid["sp_id"]);
                                        $statement->execute();
                                        $sp_cdcval = $statement->fetch();

                                        $spauditdate = explode(',', $spserviceid['audit_date']);

                                        if ($sp_cdcval) {
                                            ?>
                                            <tr class="customerAccount" data-id="<?php echo $customer['id'] ?>"  >
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $customer['customer_name'] ?></a></small>
                                                </td>
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $customer["service_type"] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <span><?php echo simpleDateConversion($customer["audit_date"]); ?>~</span><small><a ><?php echo simpleDateConversion($customer["audit_date"]) ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sp_cdc['auditing_quality'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sp_cdc['mgmt_satisfaction'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $survayAuditor; ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $survayMarks; ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sp_cdc['aggregate'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sp_cdcval['ncval'] ?></a></small>
                                                </td>
                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sp_cdcval['ofival'] ?></a></small>
                                                </td>
                                                <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                    <small><a ><?php echo $sp_cdcval['oplusval'] ?></a></small>
                                                </td>                                               
                                            </tr>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
    <link media="all" type="text/css" rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css">

    <script type="text/javascript">

        function compareMinDates(filterDate, rowDate) {
            var filterDateSplit = filterDate.split(".");
            var rowDateSplit = rowDate.split(".");
            if (filterDateSplit[2] < rowDateSplit[2]) {
                return true;
            } else if (filterDateSplit[2] == rowDateSplit[2]) {
                if (filterDateSplit[1] < rowDateSplit[1]) {
                    return true;
                } else if (filterDateSplit[1] == rowDateSplit[1]) {
                    if (filterDateSplit[0] < rowDateSplit[0]) {
                        return true;
                    } else if (filterDateSplit[0] == rowDateSplit[0]) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
            return false;
        }

        function compareMaxDates(filterDate, rowDate) {
            var filterDateSplit = filterDate.split(".");
            var rowDateSplit = rowDate.split(".");
            //year comparison
            if (filterDateSplit[2] > rowDateSplit[2]) {
                return true;
            } else if (filterDateSplit[2] == rowDateSplit[2]) {
                if (filterDateSplit[1] > rowDateSplit[1]) {
                    return true;
                } else if (filterDateSplit[1] == rowDateSplit[1]) {
                    if (filterDateSplit[0] > rowDateSplit[0]) {
                        return true;
                    } else if (filterDateSplit[0] == rowDateSplit[0]) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
            return false;
        }

        $.fn.DataTable.ext.search.push(
                function (settings, data, dataIndex) {
                    var serviceType = $(".service-type").val();
                    var fromDate = $(".filterFromDate").val();
                    var toDate = $(".filterToDate").val();
                    var isServiceType = false;
                    var isfromDate = false;
                    var isToDate = false;
                    //            alert(serviceType);
                    if (serviceType == "" && fromDate == "" && toDate == "") {
                        return true;
                    }
                    if (serviceType != "") {
                        var servieTypeStr = data[1] || ""; // use data for the age column
//                    alert(servieTypeStr);
                        if (servieTypeStr.toUpperCase().trim() == serviceType.toUpperCase().trim()) {
                            //                alert("test");
                            isServiceType = true;
                        }
                    } else {
                        isServiceType = true;
                    }
                    var dateValue = data[2] || "";
                    var dateSplit = dateValue.split("~");
                    //            alert(dateSplit[1]);
                    //            alert(fromDate);
                    if (fromDate != "") {
//                      alert(dateValue);
                        isfromDate = compareMinDates(fromDate, dateSplit[1]);
                    } else {
                        isfromDate = true;
                    }
                    if (toDate != "") {
                        isToDate = compareMaxDates(toDate, dateSplit[1]);
                    } else {
                        isToDate = true;
                    }
                    finalResult = (isServiceType && isfromDate && isToDate);
                    return finalResult;
                }
        );

        $(document).ready(function () {

            $('#filterFromDate').datepicker({
                minDate: '-100Y',
                maxDate: '+100Y',
                changeYear: true,
                changeMonth: true,
                yearRange: "-20:+100",
                dateFormat: 'dd.mm.yy'
            });

            $("#filterFromDate").keydown(function (e) {
                //            e.preventDefault();
            });

            $('#filterToDate').datepicker({
                minDate: '-100Y',
                maxDate: '+100Y',
                changeYear: true,
                changeMonth: true,
                yearRange: "-20:+100",
                dateFormat: 'dd.mm.yy'
            });

            $("#filterToDate").keydown(function (e) {
                //            e.preventDefault();
            });



            $('.customerAccount').click(function () {
                var customerID = $(this).attr('data-id');
//            alert(customerID);
                // open popup
//            localStorage['visited'] = customerID;
                var url = 'customerAccount.php'
                var form = $('<form action="' + url + '" method="get">' +
                        '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            });

            $("#menu-toggle").click(function (e) {
                e.preventDefault();
                $("#wrapper").toggleClass("active");
                $(".container").toggleClass("active");
                table.draw();
            });

            var table = $('#tab_customer_list').DataTable({
//            "sScrollX": "100%",
                "scrollY": '70vh',
                "scrollCollapse": true,
                "paging": false,
                "ordering": false,
                "lengthChange": false,
//            "pageLength": 10,
                "paging": false,
                "buttons": [
                    'excel'
                ],
                "columnDefs": [
                    {
                        orderable: false,
                        targets: "no-sort"
                    },
                    {
                        "width": "1%",
                        "targets": 0
                    },
                    {
                        "width": "15%",
                        "targets": 1
                    },
                    {
                        "width": "10%",
                        "targets": 2
                    },
                    {
                        "width": "12%",
                        "targets": 3
                    },
                    {
                        "width": "10%",
                        "targets": 4
                    },
                    {
                        "width": "10%",
                        "targets": 5
                    },
                    {
                        "width": "10%",
                        "targets": 6
                    },
                    {
                        "width": "5%",
                        "targets": 8
                    },
                    {
                        "width": "5%",
                        "targets": 9
                    },
                ]
                        //        "paging":         false
            });
            table.columns.adjust().draw();

            $('.service-type, .filterFromDate, .filterToDate').change(function () {
                var fromDate = $(".filterFromDate").val();
                var toDate = $(".filterToDate").val();
                if (fromDate != "" && toDate != "") {
                    if (!compareMinDates(fromDate, toDate)) {
                        alert("To Date can't be after From Date");
                        $(".filterToDate").val("");
                    }
                }
                //            alert("t");
                table.draw();
            });
        });
    </script>
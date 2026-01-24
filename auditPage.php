<div id="wrapper" class="active">
    <!-- Sidebar -->

    <?php
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
    $pageIdentifier = "Audit Records";
    include 'header.php';
    include 'dbConnect.php';

//    $marketingInfo = "SELECT distinct ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, comment, certification_stage_date, add_to, cas.ca_id, service_ca_status, cas.customer_id, saia.sai_id, saia.customer_id,saia.service_sai_status, saiis.saii_id, saiis.customer_id, saiis.service_saii_ststus, rcs.rc_id, rcs.rc_customer, rcs.service_rc_status, sps.sp_id, sps.sp_customer, sps.service_sp_status FROM customer_info ci left join service_type_ca cas on ci.id = cas.customer_id left join service_type_sai saia on ci.id = saia.customer_id left join service_type_saii saiis on ci.id = saiis.customer_id left join service_type_rc rcs on ci.id = rcs.rc_customer left join service_type_sp sps on ci.id = sps.sp_customer WHERE ((cas.service_ca_status = 'Marketing' and cas.ca_id = (select max(tmp.ca_id) from service_type_ca tmp where tmp.customer_id = ci.id)) or (saia.service_sai_status = 'Marketing' and saia.sai_id = (select max(tmp1.sai_id) from service_type_sai tmp1 where tmp1.customer_id = ci.id) ) or (saiis.service_saii_ststus = 'Marketing' and saiis.saii_id = (select max(tmp2.saii_id) from service_type_saii tmp2 where tmp2.customer_id = ci.id)) or (rcs.service_rc_status = 'Marketing' and rcs.rc_id = (select max(tmp3.rc_id) from service_type_rc tmp3 where tmp3.rc_customer = ci.id) ) or (sps.service_sp_status = 'Marketing' and sps.sp_id = (select max(tmp4.sp_id) from service_type_sp tmp4 where tmp4.sp_customer = ci.id)) )";
    $marketingInfo = "SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, next_service, svc.service_id, service_type, service_status, audit_date FROM customer_info ci left join service svc on ci.id = svc.customer_id where svc.audit_date is not null ORDER BY audit_date DESC";
    $statement = $db->prepare($marketingInfo);
    $statement->execute();
    $allMarketingInfo = $statement->fetchAll();

    $services = "SELECT * FROM service_types";
    $statement = $db->prepare($services);
    $statement->execute();
    $servicesTypes = $statement->fetchAll();

    $auditors = "SELECT * FROM `auditors`";
    $statement = $db->prepare($auditors);
    $statement->execute();
    $auditorsList = $statement->fetchAll();

    // Batch preload service subtype data to avoid per-row queries
    $serviceIdsByType = [
        'CA' => [], 'SA-I' => [], 'SA-II' => [], 'RC' => [], 'SP' => []
    ];
    foreach ($allMarketingInfo as $row) {
        if (isset($serviceIdsByType[$row['service_type']])) {
            $serviceIdsByType[$row['service_type']][] = $row['service_id'];
        }
    }

    $caMap = $saiMap = $saiiMap = $rcMap = $spMap = [];

    $loadMap = function($table, $serviceCol, $filterIds) use (&$db) {
        if (empty($filterIds)) return [];
        $ph = implode(',', array_fill(0, count($filterIds), '?'));
        $sql = "SELECT * FROM {$table} WHERE {$serviceCol} IN ($ph)";
        $stmt = $db->prepare($sql);
        $stmt->execute($filterIds);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $out[$r[$serviceCol]] = $r;
        }
        return $out;
    };

    $caMap = $loadMap('service_type_ca', 'service', $serviceIdsByType['CA']);
    $saiMap = $loadMap('service_type_sai', 'service', $serviceIdsByType['SA-I']);
    $saiiMap = $loadMap('service_type_saii', 'service', $serviceIdsByType['SA-II']);
    $rcMap = $loadMap('service_type_rc', 'service', $serviceIdsByType['RC']);
    $spMap = $loadMap('service_type_sp', 'service', $serviceIdsByType['SP']);
    ?>

    <style>
        .custom-float-label-control {
            margin-bottom: 0em;
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container">

                <div class="row">
                    <div class="col-sm-12 main_content" style="">
                        <div class="row" style="padding-top:10px;">

                            <div class="col-sm-3 float-label-control custom-float-label-control">
                                <select name="service-type" class="form-control service-type" id="service-type">
                                    <option value="">Select Service Type</option>
                                    <option value="ST-1">ST-1</option>
                                        <option value="ST-2">ST-2</option>
                                    <?php foreach ($servicesTypes as $service) { ?>
                                        <option value="<?php echo $service['service_type'] ?>">
                                            <?php echo $service['service_type'] ?>
                                        </option>
                                    <?php } ?>
                                        
                                </select>
                            </div>
                            <!--                            <div class="col-sm-3 float-label-control custom-float-label-control">
                                                            <select name="auditors" class="form-control auditors" id="service-type">
                                                                <option value="">Select Auditor</option>
                            <?php foreach ($auditorsList as $auditor) { ?>
                                                                                <option value="<?php echo $auditor['auditor_name'] ?>">
                                <?php echo $auditor['auditor_name'] ?>
                                                                                </option>
                            <?php } ?>
                                                            </select>
                                                        </div>-->
                            <div class="col-sm-3 float-label-control custom-float-label-control">
                                <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="Audit Date From" value="">
                            </div>
                            <div class="col-sm-3 form-group float-label-control custom-float-label-control">
                                <input type="text" class="filterToDate" id="filterToDate" name="filterToDate" placeholder="Audit Date To" value="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row clearfix">
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list" width="100%">
                            <thead>
                                <tr>
                                    <th class="">
                                        <small><a>Unique ID</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Client Name</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Service Type</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Service Status</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Auditor(s)</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Audit Date(s)</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Present Status</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($allMarketingInfo as $marketingDetail) {
                                    $auditrDetails = "";
                                    $auditDateDetails = "";
                                    if ($marketingDetail["service_type"] == "CA") {
                                        $auditDetails = isset($caMap[$marketingDetail['service_id']]) ? $caMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) { continue; }
                                        $auditdates1 = explode(',', $auditDetails["audit_date"]);
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="" >
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small>ST-1</small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditDetails["stage1_auditor"]; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo $auditdates1[0] ?>~</span><small><?php echo $auditDetails["audit_date"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                            </td>
                                        </tr>
                                        <?php if ($auditDetails["stage2_audit"] != "") { 
                                            $auditdatest2 = explode(',', $auditDetails["stage2_audit"]);
                                            ?>
                                            <tr id='addr<?php echo $marketingDetail['id'] ?>' class="" >
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small><?php echo $marketingDetail['unique_id'] ?></small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small><?php echo $marketingDetail['customer_name'] ?></small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small>ST-2</small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small><?php echo $marketingDetail['service_status'] ?></small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small><?php echo $auditDetails["stage2_auditor"]; ?></small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <span><?php echo $auditdatest2[0] ?>~</span><small><?php echo $auditDetails["stage2_audit"] ?></small>
                                                </td>
                                                <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                    <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else if ($marketingDetail["service_type"] == "SA-I") {
                                        $auditDetails = isset($saiMap[$marketingDetail['service_id']]) ? $saiMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) { continue; }
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="" >
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditDetails["auditors"]; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo simpleDateConversion($marketingDetail["audit_date"]) ?>~</span><small><?php echo $auditDetails["audit_date"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                            </td>
                                        </tr>
                                        <?php
                                    }  else if ($marketingDetail["service_type"] == "SA-II") {
                                        $auditDetails = isset($saiiMap[$marketingDetail['service_id']]) ? $saiiMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) { continue; }
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="" >
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditDetails["auditors"]; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo simpleDateConversion($marketingDetail["audit_date"]) ?>~</span><small><?php echo $auditDetails["audit_date"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                            </td>
                                        </tr>
                                        <?php
                                    } else if ($marketingDetail["service_type"] == "RC") {
                                        $auditDetails = isset($rcMap[$marketingDetail['service_id']]) ? $rcMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) { continue; }
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="" >
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditDetails["auditors"]; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo simpleDateConversion($marketingDetail["audit_date"]) ?>~</span><small><?php echo $auditDetails["audit_date"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                            </td>
                                        </tr>
                                        <?php
                                    } else if ($marketingDetail["service_type"] == "SP") {
                                        $auditDetails = isset($spMap[$marketingDetail['service_id']]) ? $spMap[$marketingDetail['service_id']] : null;
                                        if (!$auditDetails) { continue; }
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="" >
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditDetails["auditors"]; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo simpleDateConversion($marketingDetail["audit_date"]) ?>~</span><small><?php echo $auditDetails["audit_date"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                            </td>
                                        </tr>
                                        <?php
                                    } else if ($marketingDetail["service_type"] == "TA") {
                                        $taSVC = "SELECT audit_date, auditors FROM `service_type_ta` WHERE service_id = :svcid";
                                        $statement = $db->prepare($taSVC);
                                        $statement->bindParam(":svcid", $marketingDetail['service_id']);
                                        $statement->execute();
                                        $auditDetails = $statement->fetch();

                                        $auditrDetails = $auditDetails["auditors"];
                                        $auditDateDetails = $auditDetails["audit_date"];
                                        ?>
                                        <tr id='addr<?php echo $marketingDetail['id'] ?>' class="" >
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['service_status'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditrDetails; ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <span><?php echo simpleDateConversion($marketingDetail["audit_date"]) ?>~</span><small><?php echo $auditDateDetails ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $marketingDetail['audit_status'] ?> (<?php echo $marketingDetail["next_service"] ?>)</small>
                                            </td>
                                        </tr>
                                    <?php }
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

<script type="text/javascript">
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

    //for filtering

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
                    var servieTypeStr = data[2] || ""; // use data for the age column
//                    alert(servieTypeStr);
                    if (servieTypeStr.toUpperCase().trim() == serviceType.toUpperCase().trim()) {
                        //                alert("test");
                        isServiceType = true;
                    }
                } else {
                    isServiceType = true;
                }
                var dateValue = data[5] || "";
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

        $('.customerAccount').click(function () {
            var customerID = $(this).attr('data-id');
//            alert(customerID);
            // open popup
            localStorage['visited'] = customerID;
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                    '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.sendAuditPlaning').click(function () {
            var service = $(this).attr('data-id');
            var customer = $(this).attr('data-customer');
            var url = 'addToAudit.php'
            var form = $('<form action="' + url + '" method="post">' +
                    '<input type="hidden" name="service" value="' + service + '" />' +
                    '<input type="hidden" name="customer" value="' + customer + '" />' +
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
    });

    var table = $('#tab_customer_list').DataTable({
//            "scrollY": "450px",
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": false,
        "ordering": false,
        "lengthChange": false,
//            "pageLength": 10,
        "paging": false,
        "columnDefs": [
            {
                "width": "15%",
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
                "width": "15%",
                "targets": 4
            },
            {
                "width": "15%",
                "targets": 5
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

</script>
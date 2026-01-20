<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->

    <?php
    $pageIdentifier = "Audit Planning";
    include 'header.php';
    include 'dbConnect.php';

//    $auditPlanninginfo = "SELECT distinct ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, comment, certification_stage_date, add_to, cas.ca_id, service_ca_status, cas.customer_id, saia.sai_id, saia.customer_id,saia.service_sai_status, saiis.saii_id, saiis.customer_id, saiis.service_saii_ststus, rcs.rc_id, rcs.rc_customer, rcs.service_rc_status, sps.sp_id, sps.sp_customer, sps.service_sp_status FROM customer_info ci left join service_type_ca cas on ci.id = cas.customer_id left join service_type_sai saia on ci.id = saia.customer_id left join service_type_saii saiis on ci.id = saiis.customer_id left join service_type_rc rcs on ci.id = rcs.rc_customer left join service_type_sp sps on ci.id = sps.sp_customer WHERE ((cas.service_ca_status = 'Audit planning' and cas.ca_id = (select max(tmp.ca_id) from service_type_ca tmp where tmp.customer_id = ci.id)) or (saia.service_sai_status = 'Audit planning' and saia.sai_id = (select max(tmp1.sai_id) from service_type_sai tmp1 where tmp1.customer_id = ci.id) ) or (saiis.service_saii_ststus = 'Audit planning' and saiis.saii_id = (select max(tmp2.saii_id) from service_type_saii tmp2 where tmp2.customer_id = ci.id)) or (rcs.service_rc_status = 'Audit planning' and rcs.rc_id = (select max(tmp3.rc_id) from service_type_rc tmp3 where tmp3.rc_customer = ci.id) ) or (sps.service_sp_status = 'Audit planning' and sps.sp_id = (select max(tmp4.sp_id) from service_type_sp tmp4 where tmp4.sp_customer = ci.id)) )";
//    $auditPlanninginfo = "SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, service_type, service_status, svcdt.action, due_date, sent_date, svcdt.comment FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and svc.service_status = 'Audit planning' and svcdt.action = '1st Remainder' ";
//    $auditPlanninginfo = "SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, service_type, service_status FROM customer_info ci left join service svc on ci.id = svc.customer_id WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and svc.service_status = 'Audit planning'";
    $auditPlanninginfo = "SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, svc.service_type, service_status, 'sa1' as stage1 FROM customer_info ci left join service svc on ci.id = svc.customer_id  WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and svc.service_status = 'Audit planning' union SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, svc.service_type, service_status, 'sa2' as stage1 FROM customer_info ci left join service svc on ci.id = svc.customer_id WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and svc.service_status = 'Audit planning'";
    $statement = $db->prepare($auditPlanninginfo);
    $statement->execute();
    $auditPlanningInfo = $statement->fetchAll();
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
    ?>
    <style>
        .custom-float-label-control {
            margin-bottom: 0em;
        }
    </style>

    <div id="page-content-wrapper">

        <div class="page-content inset">
            <div class="container" >
                <br>
                <br>
                <div class="row clearfix" >
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list" width="100%">
                            <thead>
                                <tr>
                                    <th class="">
                                        <small><a>Client unique id</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Client Name</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Service type</a></small>
                                    </th>
<!--                                    <th class="">
                                        <small><a>Stage</a></small>
                                    </th>-->
                                    <th class="">
                                        <small><a>Audit date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Auditor</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>view details</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($auditPlanningInfo as $auditPlanningDetail) { ?> 
                                    <?php
                                    $serviceid = "";
                                    $saidetails = "";
                                    $auditid = "";
                                    if ($auditPlanningDetail["service_type"] == "CA") {
                                        $sql = "SELECT ca_id FROM `service_type_ca` where service = :service";
                                        $statement = $db->prepare($sql);
                                        $statement->bindParam(":service", $auditPlanningDetail["service_id"]);
                                        $statement->execute();
                                        $svc = $statement->fetch();
                                        $serviceid = $svc["ca_id"];
                                    } else if ($auditPlanningDetail["service_type"] == "SA-I") {
                                        $sql = "SELECT sai_id FROM `service_type_sai` where service = :service";
                                        $statement = $db->prepare($sql);
                                        $statement->bindParam(":service", $auditPlanningDetail["service_id"]);
                                        $statement->execute();
                                        $svc = $statement->fetch();
                                        $serviceid = $svc["sai_id"];
                                    } else if ($auditPlanningDetail["service_type"] == "SA-II") {
                                        $sql = "SELECT saii_id FROM `service_type_saii` where service = :service";
                                        $statement = $db->prepare($sql);
                                        $statement->bindParam(":service", $auditPlanningDetail["service_id"]);
                                        $statement->execute();
                                        $svc = $statement->fetch();
                                        $serviceid = $svc["saii_id"];
                                    } else if ($auditPlanningDetail["service_type"] == "RC") {
                                        $sql = "SELECT rc_id FROM `service_type_rc` where service = :service";
                                        $statement = $db->prepare($sql);
                                        $statement->bindParam(":service", $auditPlanningDetail["service_id"]);
                                        $statement->execute();
                                        $svc = $statement->fetch();
                                        $serviceid = $svc["rc_id"];
                                    } else if ($auditPlanningDetail["service_type"] == "SP") {
                                        $sql = "SELECT sp_id FROM `service_type_sp` where service = :service";
                                        $statement = $db->prepare($sql);
                                        $statement->bindParam(":service", $auditPlanningDetail["service_id"]);
                                        $statement->execute();
                                        $svc = $statement->fetch();
                                        $serviceid = $svc["sp_id"];
                                    }

                                    if ($auditPlanningDetail['stage1'] == "sa1") {
                                        $sai = "SELECT `audit_stage_id`, `audit_dates`, `auditor_list` FROM `stage_oneaudit_id` WHERE `ca_service_id` = :id ";
                                        $statement = $db->prepare($sai);
                                        $statement->bindParam(":id", $serviceid);
                                        $statement->execute();
                                        $saidetails = $statement->fetch();
                                        $auditid = $saidetails["audit_stage_id"];
                                    } else if ($auditPlanningDetail['stage1'] == "sa2") {
                                        $saii = "SELECT `audit_stageii_id`,`audit_dates`, `auditor_list` FROM `stage_twoaudit_id` WHERE `service_id` = :id ";
                                        $statement = $db->prepare($saii);
                                        $statement->bindParam(":id", $serviceid);
                                        $statement->execute();
                                        $saidetails = $statement->fetch();
                                        $auditid = $saidetails["audit_stageii_id"];
                                    }
                                    ?>
                                    <?php if ($saidetails['audit_dates']) { ?>
                                        <tr id='addr<?php echo $auditPlanningDetail['id'] ?>' >
                                            <td class="customerAccount" data-id="<?php echo $auditPlanningDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditPlanningDetail['unique_id'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $auditPlanningDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditPlanningDetail['customer_name'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $auditPlanningDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $auditPlanningDetail['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $auditPlanningDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $saidetails['audit_dates'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $auditPlanningDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $saidetails['auditor_list'] ?></small>
                                            </td>
                                            <td data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <?php if ($auditPlanningDetail['stage1'] == "sa1") { ?>
                                                    <a class="downloadAuditNotification pull-right" data-customer="<?php echo $auditPlanningDetail['id'] ?>" data-caservice-id="<?php echo $serviceid ?>" data-audit="<?php echo $auditid ?>" title="Download Release Audit Notification" style="padding: 1px 5px;">
                                                        <i class="fa fa-download fa-lg " style="padding: 3px"></i>
                                                    </a>
                                                    <?php if ($auditPlanningDetail["service_type"] == "CA") { ?>
                                                        <a class="editAuditNotification pull-right" data-customer="<?php echo $auditPlanningDetail['id'] ?>" data-service="<?php echo $serviceid ?>" data-type="service-type-sai" data-notification="<?php echo $auditid ?>" title="Add Release Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($auditPlanningDetail["service_type"] == "SA-I") { ?>
                                                        <a class="editAuditNotification pull-right" data-customer="<?php echo $auditPlanningDetail['id'] ?>" data-service="<?php echo $serviceid ?>" data-type="service-type-sai" data-notification="<?php echo $auditid ?>" title="Add Release Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($auditPlanningDetail["service_type"] == "SA-II") { ?>
                                                        <a class="editAuditNotification pull-right" data-customer="<?php echo $auditPlanningDetail['id'] ?>" data-service="<?php echo $serviceid ?>" data-notification="<?php echo $auditid ?>" title="Update Release Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($auditPlanningDetail["service_type"] == "RC") { ?>
                                                        <a class="editAuditNotification pull-right" data-customer="<?php echo $auditPlanningDetail['id'] ?>" data-service="<?php echo $serviceid ?>" data-type="service-type-sai" data-notification="<?php echo $auditid ?>" title="Add Release - Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($auditPlanningDetail["service_type"] == "SP") { ?>
                                                        <a class=" editAuditNotification pull-right" data-customer="<?php echo $auditPlanningDetail['id'] ?>" data-service="<?php echo $serviceid ?>" data-notification="<?php echo $auditid ?>" title="Update Release Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                <?php } else if ($auditPlanningDetail['stage1'] == "sa2") { ?>
                                                    <a class="downloadAuditNotificationii pull-right" data-customer="<?php echo $auditPlanningDetail['id'] ?>" data-caservice-id="<?php echo $serviceid ?>" data-audit="<?php echo $auditid ?>" title="Download Release Audit Notification" style="padding: 1px 5px;">
                                                        <i class="fa fa-download fa-lg " style="padding: 3px"></i>
                                                    </a>
                                                    <?php if ($auditPlanningDetail["service_type"] == "CA") { ?>
                                                        <a class="editAuditNotificationii pull-right" data-customer="<?php echo $auditPlanningDetail['id'] ?>" data-service="<?php echo $serviceid ?>" data-notification="<?php echo $auditid ?>" title="Edit Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($auditPlanningDetail["service_type"] == "RC") { ?>
                                                        <a class="editAuditNotification pull-right" data-customer="<?php echo $auditPlanningDetail['id'] ?>" data-service="<?php echo $serviceid ?>" data-type="service-type-sai" data-notification="<?php echo $auditid ?>" title="Add Release - Audit Notification" style="padding: 1px 5px;">
                                                            <i class="fa fa-pencil-square fa-lg " style="padding: 3px"></i>
                                                        </a>
                                                    <?php } ?>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        $('.downloadAuditNotificationii').click(function () {
            var notificationid = $(this).attr('data-audit');
            var url = 'AuditNotificationStage2.php';
            var form = $('<form action="' + url + '" method="post" target="_blank" >' +
                    '<input type="hidden" name="notificationid" value="' + notificationid + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.downloadAuditNotification').click(function () {
//                        alert($(this).attr('data-customer'));
//                        alert($(this).attr('data-audit'));
//                        alert($(this).attr('data-caservice-id'));
            var auditid = $(this).attr('data-audit');
            var service = $(this).attr('data-caservice-id');
            var customer = $(this).attr('data-customer');
            var url = 'AuditNotificationandAssignment.php';
            var form = $('<form action="' + url + '" method="post" target="_blank" >' +
                    '<input type="hidden" name="auditid" value="' + auditid + '" />' +
                    '<input type="hidden" name="serviceid" value="' + service + '" />' +
                    '<input type="hidden" name="customerid" value="' + customer + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

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

        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
            table.draw();
        });
    });

    var table = $('#tab_customer_list').DataTable({
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
                "width": "10%",
                "targets": 0
            },
            {
                "width": "15%",
                "targets": 1
            },
            {
                "width": "15%",
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
        ]
                //        "paging":         false
    });
</script>
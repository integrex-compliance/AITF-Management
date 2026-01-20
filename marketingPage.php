<div id="wrapper" class="active">
 
    <?php
    $pageIdentifier = "Mktg";
    include 'header.php';
    include 'dbConnect.php';
    
//    $marketingInfo = "SELECT distinct ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, comment, certification_stage_date, add_to, cas.ca_id, service_ca_status, cas.customer_id, saia.sai_id, saia.customer_id,saia.service_sai_status, saiis.saii_id, saiis.customer_id, saiis.service_saii_ststus, rcs.rc_id, rcs.rc_customer, rcs.service_rc_status, sps.sp_id, sps.sp_customer, sps.service_sp_status FROM customer_info ci left join service_type_ca cas on ci.id = cas.customer_id left join service_type_sai saia on ci.id = saia.customer_id left join service_type_saii saiis on ci.id = saiis.customer_id left join service_type_rc rcs on ci.id = rcs.rc_customer left join service_type_sp sps on ci.id = sps.sp_customer WHERE ((cas.service_ca_status = 'Marketing' and cas.ca_id = (select max(tmp.ca_id) from service_type_ca tmp where tmp.customer_id = ci.id)) or (saia.service_sai_status = 'Marketing' and saia.sai_id = (select max(tmp1.sai_id) from service_type_sai tmp1 where tmp1.customer_id = ci.id) ) or (saiis.service_saii_ststus = 'Marketing' and saiis.saii_id = (select max(tmp2.saii_id) from service_type_saii tmp2 where tmp2.customer_id = ci.id)) or (rcs.service_rc_status = 'Marketing' and rcs.rc_id = (select max(tmp3.rc_id) from service_type_rc tmp3 where tmp3.rc_customer = ci.id) ) or (sps.service_sp_status = 'Marketing' and sps.sp_id = (select max(tmp4.sp_id) from service_type_sp tmp4 where tmp4.sp_customer = ci.id)) )";
//    $marketingInfo = "SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, service_type, service_status, svcdt.action, due_date, sent_date, svcdt.comment FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and svc.service_status = 'Marketing' and svcdt.action = '1st Remainder'";
    $marketingInfo = "SELECT ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, service_type, service_status FROM customer_info ci left join service svc on ci.id = svc.customer_id WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and svc.service_status = 'Marketing'";
    $statement = $db->prepare($marketingInfo);
    $statement->execute();
    $allMarketingInfo = $statement->fetchAll();
 
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
                                        <small><a>Client Name</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Service type</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Present status</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Next status due on date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Send to audit planning</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Comments</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allMarketingInfo as $marketingDetail) { 
                                        $duedate = "SELECT * FROM `ca_service_dates` WHERE service_id = :id AND action = '1st Reminder'";
                                        $statement = $db->prepare($duedate);
                                        $statement->bindParam(':id', $marketingDetail['service_id']);
                                        $statement->execute();
                                        $dueDateInfo = $statement->fetch();
                                    ?>                                     
                                    <tr id='addr<?php echo $marketingDetail['id'] ?>' class="" >
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
                                            <small><?php echo simpleDateConversion($dueDateInfo['due_date']) ?></small>
                                        </td>
                                        <td data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff; text-align: center;">
                                            <a class="sendAuditPlaning " data-customer="<?php echo $marketingDetail['id'] ?>" data-id="<?php echo $marketingDetail['service_id'] ?>" title="Add to audit planing" style="padding: 1px 5px; "><i class="fa fa-plus-circle " style="padding:3px"></i></a>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $marketingDetail['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $marketingDetail['comment'] ?></small>
                                        </td>                                        
                                    </tr>
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
                
    });
</script>
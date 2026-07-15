<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->
    <?php
 
    $pageIdentifier = "Quot";
    include 'header.php';
    include 'dbConnect.php';

//    $quotationList = "SELECT qd.quotation_id, qd.customer_id, quotationno, quotation_date, free_covers, stage_i_audit, stage_ii_audit, total_cost, maintenance, ci.id, customer_name, ci.address, svc.service_id, service_type FROM quotation_detail qd left join customer_info ci on qd.customer_id = ci.id left join service svc on qd.customer_id = svc.customer_id where svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id)";
//    $quotationList = "SELECT s.service_id, service_type, s.customer_id, ci.customer_name, qd.quotation_id, quotationno, quotation_date, total_cost, 'Quotation' as quotid FROM customer_info ci join service s on ci.id = s.customer_id join quotation_detail qd on ci.id = qd.customer_id where s.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) union SELECT s.service_id, service_type, s.customer_id, ci.customer_name, aa.audit_id as quotation_id, uni_svc_id as quotationno, application_date as quotation_date, '' as total_cost, 'Aggrement' as quotid  FROM customer_info ci join service s on ci.id = s.customer_id join audit_application aa on s.service_id = aa.uni_svc_id where s.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) order by customer_name";
    $quotationList = "SELECT s.service_id, service_type, s.customer_id, ci.customer_name, ci.audit_status, qd.quotation_id, quotationno, quotation_date, total_cost, 'Quotation' as quotid FROM customer_info ci join service s on ci.id = s.customer_id join quotation_detail qd on ci.id = qd.customer_id where s.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id)";
    $statement = $db->prepare($quotationList);
    $statement->execute();
    $quotationInfo = $statement->fetchAll();

    $firmDetails = "SELECT * FROM consulting_firm";
    $statement = $db->prepare($firmDetails);
    $statement->execute();
    $firmInfo = $statement->fetch();
    ?>

    <style>
        .custom-float-label-control {
            margin-bottom: 0em;
        }
    </style>

    <div id="page-content-wrapper">
        <!-- Keep all page content within the page-content inset div! -->
        <div class="page-content inset">
            <div class="row">
                <div class="col-md-12">
                    <div class="container" style="margin-top: 20px;">
                        <div class="row">
                            <div class="col-sm-12 contact-form">
                                <div class="row clearfix">
                                    <div class="col-md-12 table-responsive">
                                        <table class="table table-bordered table-striped table-hover table-sortable" id="table_quotation">
                                            <thead>
                                                <tr>
                                                    <th class="">
                                                        <small><a>Client Name</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Service type</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Agreement/quotation no</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Amount</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Date</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Status</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Edit/View</a></small>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 0;
                                                foreach ($quotationInfo as $quotation) {
                                                    $count = $count + 1;
                                                    ?>
                                                    <tr id='addr0' >
                                                        <td class="customerAccount" data-id="<?php echo $quotation['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $quotation['customer_name'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $quotation['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $quotation['service_type'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $quotation['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $quotation['quotid'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $quotation['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><?php echo $quotation['total_cost'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $quotation['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><?php echo simpleDateConversion($quotation['quotation_date']); ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $quotation['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><?php echo $quotation['audit_status'] ?></small>
                                                            <!--<a class="finalInvoice" data-customer="<?php // echo $quotation['customer_id']            ?>" data-id="<?php //echo $proforma['invoice_id']          ?>" title="Add to final invoice" style="padding: 1px 5px; "><i class="fa fa-plus-circle fa-lg " style="padding:3px"></i></a>-->
                                                        </td>
                                                        <td data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <?php if (isset($quotation['quotid']) && $quotation['quotid'] == "Quotation") { ?>
                                                                <a class="editQuotation" data-firm="<?php echo $quotation['firm_id'] ?>" data-customer="<?php echo $quotation['customer_id'] ?>" data-id="<?php echo $quotation['quotation_id'] ?>" title="Edit Quotation" style="padding: 1px 5px; "><i class="fa fa-pencil-square" style="padding:3px"></i></a>
                                                                <a class="viewQuotation" data-firm="<?php echo $quotation['firm_id'] ?>" data-customer="<?php echo $quotation['customer_id'] ?>" data-id="<?php echo $quotation['quotation_id'] ?>" title="Quotation PDF" style="padding: 1px 5px; "><i class="fa fa-file-pdf-o" style="padding:3px"></i></a>
                                                            <?php
                                                            } else {
                                                                $serviceid = "SELECT * FROM service_type_ca WHERE service = :id";
                                                                $statement = $db->prepare($serviceid);
                                                                $statement->bindParam(':id', $quotation['service_id']);
                                                                $statement->execute();
                                                                $svcid = $statement->fetch();
                                                                ?>
                                                                <a class="editAuditApp" data-audit="<?php echo $quotation['quotation_id'] ?>" data-firm="<?php echo $quotation['firm_id'] ?>" data-customer="<?php echo $quotation['customer_id'] ?>" data-id="<?php echo $quotation['quotation_id'] ?>" title="Edit Quotation" style="padding: 1px 5px; "><i class="fa fa-pencil-square" style="padding:3px"></i></a>
                                                                <a class="viewQuotation" data-service="<?php echo $svcid['ca_id'] ?>" data-customer="<?php echo $quotation['customer_id'] ?>" data-id="<?php echo $quotation['quotation_id'] ?>" title="Quotation PDF" style="padding: 1px 5px; "><i class="fa fa-file-pdf-o" style="padding:3px"></i></a>
                                                    <?php } ?>
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
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">

    var table = $('#table_quotation').DataTable({
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

    $(document).ready(function () {
        $('.certificationBtn').click(function () {
            var ServiceId = $(this).attr('data-service');
            var csId = $(this).attr('data-customer');
            // alert(csId);
            var url = 'Csa.php';
            var form = $('<form action="' + url + '" method="post" target="_blank" >' +
                    '<input type="hidden" name="ServiceId" value="' + ServiceId + '" />' +
                    '<input type="hidden" name="csId" value="' + csId + '" />' +
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

        $('.viewQuotation').click(function () {
            var customerID = $(this).attr('data-customer');
            var firmId = $(this).attr('data-firm');
            var quotation_id = $(this).attr('data-id');
            // open popup
            var url = 'quotationPdf.php'
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                    '<input type="hidden" name="quoteCustomerId" value="' + customerID + '" />' +
                    '<input type="hidden" name="consultingFirmId" value="' + firmId + '" />' +
                    '<input type="hidden" name="quotation_id" value="' + quotation_id + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.editQuotation').click(function () {
            $.ajax({
                url: "getInvoiceInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    quotation: $(this).attr('data-id'),
                },
                success: function (data)
                {
//                    alert(data);
                    $('.conditionDescTd').empty();
                    $('.quotationId').val(data[0]['quotation_id']);
                    $('.quoteCustomerId').val(data[0]['customer_id']);
                    $('.consultingFirmId').val(data[0]['firm_id']);
                    $('.quoteconsultingFirmName').val(data[1]['firm_name']);
                    $('.quotationDt').val(data[0]['quotation_date']);
                    $('.feeCovers').val(data[0]['free_covers']);
                    $('.stage1Audit').val(data[0]['stage_i_audit']);
                    $('.stage2Audit').val(data[0]['stage_ii_audit']);
                    $('.maintenanceAmt').val(data[0]['maintenance']);
                    for (var key in data[2]) {
                        $('.conditionDescTd').append('<div class="row">\n\
                                                    <div class="col-sm-12 form-group float-label-control" style="padding-top:10px;">\n\
                                                    <label for="">Condition Details</label>\n\
                                                    <textarea name="conditionDescription" rows="2" class="form-control form-group conditionDescription" id="conditionDescription" placeholder="Condition Description">' + data[2][key]['condition_desc'] + '</textarea>\n\
                                                    </div>\n\
                                                    </div>');
                    }
                    $('input[type="text"]').trigger('change');
                    $('input[type="number"]').trigger('change');
                    $('#quotation').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

    });
</script>

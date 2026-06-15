<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->

    <?php
    $pageIdentifier = "Finance Page";
    include 'header.php';
    include 'dbConnect.php';

    //    $paymentDetail = "SELECT distinct ci.id, customer_name, unique_id, audit_status, address, contact, email_id, scope, aif_nace, comment, certification_stage_date, add_to, cas.ca_id, service_ca_status, cas.customer_id, saia.sai_id, saia.customer_id,saia.service_sai_status, saiis.saii_id, saiis.customer_id, saiis.service_saii_ststus, rcs.rc_id, rcs.rc_customer, rcs.service_rc_status, sps.sp_id, sps.sp_customer, sps.service_sp_status FROM customer_info ci left join service_type_ca cas on ci.id = cas.customer_id left join service_type_sai saia on ci.id = saia.customer_id left join service_type_saii saiis on ci.id = saiis.customer_id left join service_type_rc rcs on ci.id = rcs.rc_customer left join service_type_sp sps on ci.id = sps.sp_customer WHERE ((cas.service_ca_status = 'Payment' and cas.ca_id = (select max(tmp.ca_id) from service_type_ca tmp where tmp.customer_id = ci.id)) or (saia.service_sai_status = 'Payment' and saia.sai_id = (select max(tmp1.sai_id) from service_type_sai tmp1 where tmp1.customer_id = ci.id) ) or (saiis.service_saii_ststus = 'Payment' and saiis.saii_id = (select max(tmp2.saii_id) from service_type_saii tmp2 where tmp2.customer_id = ci.id)) or (rcs.service_rc_status = 'Payment' and rcs.rc_id = (select max(tmp3.rc_id) from service_type_rc tmp3 where tmp3.rc_customer = ci.id) ) or (sps.service_sp_status = 'Payment' and sps.sp_id = (select max(tmp4.sp_id) from service_type_sp tmp4 where tmp4.sp_customer = ci.id)) )";
//    $paymentDetail = "SELECT ci.id, customer_name,contact, unique_id, audit_status, address, contact, email_id, scope, aif_nace, svc.service_id, service_type, service_status FROM customer_info ci left join service svc on ci.id = svc.customer_id WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and svc.service_status = 'Payment'";
    $paymentDetail = "SELECT ci.id, customer_name, unique_id, certification_no, audit_status, contact_person, contact, ps.payment_id, ps.service_type, amount_decided, payment_received, payment_date, cheque_details, balance, ps.comment FROM customer_info ci join payment_system ps on ps.customer_id = ci.id where ps.balance > 0 and ps.service_type <> 'TDS' and ci.is_deleted = 'NO'";
    $statement = $db->prepare($paymentDetail);
    $statement->execute();
    $allPaymentInfo = $statement->fetchAll();

    ?>
    <style>
        .custom-float-label-control {
            margin-bottom: 0em;
        }

        /* Added for consistency with other pages */
        .invoice-section-title {
            display: inline-block;
            margin: 0 0 15px;
            padding: 8px 16px;
            font-size: 22px;
            font-weight: 700;
            color: #0d4d8b;
            background: #eaf3ff;
            border-left: 4px solid #0d4d8b;
            border-radius: 4px;
        }

        .invoice-filter-panel {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin: 10px 0 20px;
            padding: 12px 15px;
            background: #f8fbff;
            border: 1px solid #d6e4f0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .invoice-filter-item {
            flex: 1;
            min-width: 150px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Search Box Specific Styling */
        .search-item {
            flex: 1.5;
            min-width: 250px;
        }

        .search-item i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #0d4d8b;
            z-index: 1;
            font-size: 14px;
        }

        .search-item input {
            padding-left: 35px !important;
        }

        .invoice-filter-item input {
            width: 100%;
            height: 40px;
            padding: 8px 12px;
            border: 1px solid #bfd2e6;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 13px;
            background-color: #fff;
            color: #333;
            transition: all 0.2s;
        }

        .invoice-filter-item input:focus {
            border-color: #0d4d8b;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.1);
        }

        .btn-report-export {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 40px;
            margin: 0;
            padding: 0 20px;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.2px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: none;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .btn-solid-primary {
            background-color: #0d4d8b;
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(13, 77, 139, 0.1);
        }

        .btn-solid-primary:hover {
            background-color: #0b3d6e;
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.15);
            transform: translateY(-1px);
        }

        .btn-solid-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(13, 77, 139, 0.1);
        }

        #tab_customer_list_filter {
            display: none;
        }
    </style>

    <div id="page-content-wrapper">

        <div class="page-content inset">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <h5 class="invoice-section-title">Payments & Reports</h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 main_content" style="">
                        <div class="invoice-filter-panel">
                            <div class="invoice-filter-item search-item">
                                <i class="fa fa-search"></i>
                                <input type="text" id="paymentClientSearch" placeholder="Search by Client Name...">
                            </div>
                            <div class="invoice-filter-item">
                                <input type="number" id="minAmount" placeholder="Min Amount">
                            </div>
                            <div class="invoice-filter-item">
                                <input type="number" id="maxAmount" placeholder="Max Amount">
                            </div>
                            <div class="invoice-filter-item" style="flex: 0 0 160px;">
                                <button type="button" class="btn-report-export btn-solid-primary downloadPaymentReport"
                                    style="width: 100%;">
                                    <i class="fa fa-cloud-download" style="font-size: 15px;"></i> Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list"
                            width="100%">
                            <thead>
                                <tr>
                                    <th class="">
                                        <small><a>Client Name</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Contact Person</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Phone Number</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Service type</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Payment Pending</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Comments</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPaymentInfo as $paymentInfo) { ?>
                                    <tr id='addr<?php echo $paymentInfo['id'] ?>'>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $paymentInfo['customer_name'] ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $paymentInfo['contact_person'] ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $paymentInfo['contact'] ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $paymentInfo['service_type'] ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $paymentInfo['balance'] ?></small>
                                        </td>
                                        <td data-name="sel"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <div class="table-edit-control">
                                                <textarea rows="3" class="saveChanges"
                                                    data-row="<?php echo $paymentInfo['payment_id'] ?>"
                                                    data-column="comment" name="payComments" value="" placeholder=""><?php if (isset($paymentInfo['comment']))
                                                        echo $paymentInfo['comment'] ?></textarea>
                                                </div>
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
    function debounce(callback, delay) {
        var debounceTimer;
        return function () {
            var context = this;
            var args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                callback.apply(context, args);
            }, delay);
        };
    }

    $.fn.DataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var min = parseInt($('#minAmount').val(), 10);
            var max = parseInt($('#maxAmount').val(), 10);
            var amount = parseFloat(data[4]) || 0; // Column index 4 is "Payment Pending"

            if ((isNaN(min) && isNaN(max)) ||
                (isNaN(min) && amount <= max) ||
                (min <= amount && isNaN(max)) ||
                (min <= amount && amount <= max)) {
                return true;
            }
            return false;
        }
    );

    $(document).ready(function () {

        var debouncedSearch = debounce(function () {
            table.column(0).search($('#paymentClientSearch').val()).draw();
        }, 200);

        $('#paymentClientSearch').on('input', debouncedSearch);

        $('#minAmount, #maxAmount').on('keyup change', function () {
            table.draw();
        });

        $('.downloadPaymentReport').click(function () {
            var minAmount = $('#minAmount').val();
            var maxAmount = $('#maxAmount').val();
            var clientName = $('#paymentClientSearch').val();
            var url = 'exportPaymentReport.php';
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                '<input type="hidden" name="minAmount" value="' + minAmount + '" />' +
                '<input type="hidden" name="maxAmount" value="' + maxAmount + '" />' +
                '<input type="hidden" name="clientName" value="' + clientName + '" />' +
                '</form>');
            $('body').append(form);
            form.submit();
            form.remove();
        });

        $('.saveChanges').change(function (e) {
            saveData($(this));
        });

        function saveData($selector) {

            var checkedValue = $selector.val();
            //            alert(checkedValue);
            $.ajax({
                url: "addComments.php",
                type: "GET",
                //                dataType: 'JSON',
                data: {
                    certificationComment: checkedValue,
                    cbId: $selector.attr('data-row')
                }, error: function (xhr, status, error) {
                    //                    alert("There seems to be issue saving data!");
                },
                success: function (data) {
                    //                    alert(data);
                }
            });

            $selector.removeClass("glowing-border");
            //           $(this).removeClass("form-group ");
            $selector.addClass("lookslike-label");
        }

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
                "width": "5%",
                "targets": 0
            },
            {
                "width": "5%",
                "targets": 1
            },
            {
                "width": "8%",
                "targets": 2
            },
            {
                "width": "7%",
                "targets": 3
            },
            {
                "width": "7%",
                "targets": 4
            },
            {
                "width": "30%",
                "targets": 5
            },
        ]
        //        "paging":         false
    });
    table.columns.adjust().draw();
</script>
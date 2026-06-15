<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->
    <?php
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
    $pageIdentifier = "agreement";
    include 'header.php';
    include 'dbConnect.php';

    //    $quotationList = "SELECT qd.quotation_id, qd.customer_id, quotationno, quotation_date, free_covers, stage_i_audit, stage_ii_audit, total_cost, maintenance, ci.id, customer_name, ci.address, svc.service_id, service_type FROM quotation_detail qd left join customer_info ci on qd.customer_id = ci.id left join service svc on qd.customer_id = svc.customer_id where svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id)";
//    $quotationList = "SELECT s.service_id, service_type, s.customer_id, ci.customer_name, qd.quotation_id, quotationno, quotation_date, total_cost, 'Quotation' as quotid FROM customer_info ci join service s on ci.id = s.customer_id join quotation_detail qd on ci.id = qd.customer_id where s.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) union SELECT s.service_id, service_type, s.customer_id, ci.customer_name, aa.audit_id as quotation_id, uni_svc_id as quotationno, application_date as quotation_date, '' as total_cost, 'Aggrement' as quotid  FROM customer_info ci join service s on ci.id = s.customer_id join audit_application aa on s.service_id = aa.uni_svc_id where s.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) order by customer_name";
//    $quotationList = "SELECT s.service_id, service_type, s.customer_id, ci.customer_name, ci.unique_id, aa.audit_id as quotation_id, uni_svc_id as quotationno, application_date as quotation_date, '' as total_cost, 'Aggrement' as quotid  FROM customer_info ci join service s on ci.id = s.customer_id join audit_application aa on s.service_id = aa.uni_svc_id where s.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and ci.is_deleted = 'NO' order by customer_name";
    $quotationList = "SELECT s.service_id, service_type, s.customer_id, ci.customer_name, ci.audit_status, aa.audit_id as quotation_id, aa.service_id as casvcid, uni_svc_id as quotationno, application_date as quotation_date,'Aggrement' as quotid,refundable_amount,audit_stage_i, audit_stage_iI, initial_assessment, ca.id, ca.aggrement_date, ca.aggrement_no FROM customer_info ci join service s on ci.id = s.customer_id join audit_application aa on s.service_id = aa.uni_svc_id left join certification_aggrement ca on aa.service_id = ca.service where ci.is_deleted = 'NO' order by aggrement_date DESC";
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

        /* Added from invoicePage.php */
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
            color: #333;
            background-color: #fff;
            transition: all 0.2s;
        }

        .invoice-filter-item input:focus {
            border-color: #0d4d8b;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.1);
            background-color: #fff;
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

        #table_quotation_filter {
            display: none;
        }
    </style>

    <div id="page-content-wrapper">
        <!-- Keep all page content within the page-content inset div! -->
        <div class="page-content inset">
            <div class="row">
                <div class="col-md-12">
                    <div class="container" style="margin-top: 20px;">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="invoice-section-title">Certification Agreements & Reports</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 main_content" style="">
                                <div class="invoice-filter-panel">
                                    <div class="invoice-filter-item">
                                        <input type="text" class="filterFromDate" id="filterFromDate"
                                            name="filterFromDate" placeholder="Due From Date" value="">
                                    </div>
                                    <div class="invoice-filter-item">
                                        <input type="text" class="filterToDate" id="filterToDate" name="filterToDate"
                                            placeholder="Due To Date" value="">
                                    </div>
                                    <div class="invoice-filter-item search-item">
                                        <i class="fa fa-search"></i>
                                        <input type="text" id="agreementClientSearch"
                                            placeholder="Search by Client Name...">
                                    </div>
                                    <div class="invoice-filter-item" style="flex: 0 0 160px;">
                                        <button type="button"
                                            class="btn-report-export btn-solid-primary downloadAgreementReport"
                                            style="width: 100%;">
                                            <i class="fa fa-cloud-download" style="font-size: 15px;"></i> Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 contact-form">
                                <div class="row clearfix">
                                    <div class="col-md-12 table-responsive">
                                        <table class="table table-bordered table-striped table-hover table-sortable"
                                            id="table_quotation">
                                            <thead>
                                                <tr>
                                                    <th class="">
                                                        <small><a>Client Name</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Service Type</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Agreement/Quotation No.</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Amount</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Date</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Status</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Edit / View</a></small>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 0;
                                                foreach ($quotationInfo as $quotation) {
                                                    $count = $count + 1;
                                                    $total_cost = $quotation['refundable_amount'] + $quotation['audit_stage_i'] + $quotation['audit_stage_iI'];
                                                    ?>
                                                    <tr id='addr0'>
                                                        <td class="customerAccount"
                                                            data-id="<?php echo $quotation['customer_id'] ?>"
                                                            data-name="desc"
                                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $quotation['customer_name'] ?></small>
                                                        </td>
                                                        <td class="customerAccount"
                                                            data-id="<?php echo $quotation['customer_id'] ?>"
                                                            data-name="sel"
                                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $quotation['service_type'] ?></small>
                                                        </td>
                                                        <td class="customerAccount"
                                                            data-id="<?php echo $quotation['customer_id'] ?>"
                                                            data-name="sel"
                                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $quotation['aggrement_no'] ?></small>
                                                        </td>
                                                        <td class="customerAccount"
                                                            data-id="<?php echo $quotation['customer_id'] ?>"
                                                            data-name="sel"
                                                            style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><?php echo $total_cost ?></small>
                                                        </td>
                                                        <td class="customerAccount"
                                                            data-id="<?php echo $quotation['customer_id'] ?>"
                                                            data-name="sel"
                                                            style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><span><?php echo simpleDateConversion($quotation['aggrement_date']); ?>~</span><?php echo simpleDateConversion($quotation['aggrement_date']); ?></small>
                                                        </td>
                                                        <td class="customerAccount"
                                                            data-id="<?php echo $quotation['customer_id'] ?>"
                                                            data-name="sel"
                                                            style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><?php echo $quotation['audit_status']; ?></small>
                                                            <!--<a class="finalInvoice" data-customer="<?php //  echo $quotation['customer_id']              ?>" data-id="<?php echo $proforma['audit_status'] ?>" title="Add to final invoice" style="padding: 1px 5px; "><i class="fa fa-plus-circle fa-lg " style="padding:3px"></i></a>-->
                                                        </td>
                                                        <td data-name="sel"
                                                            style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <a class="editAuditApp" data-page="agreement"
                                                                data-audit="<?php echo $quotation['quotation_id'] ?>"
                                                                data-firm="<?php echo $quotation['firm_id'] ?>"
                                                                data-customer="<?php echo $quotation['customer_id'] ?>"
                                                                data-id="<?php echo $quotation['quotation_id'] ?>"
                                                                title="Edit Quotation" style="padding: 1px 5px; "><i
                                                                    class="fa fa-pencil-square" style="padding:3px"></i></a>
                                                            <a class="viewQuotation"
                                                                data-service="<?php echo $quotation['casvcid'] ?>"
                                                                data-customer="<?php echo $quotation['customer_id'] ?>"
                                                                data-certfication="<?php echo $quotation['id'] ?>"
                                                                data-id="<?php echo $quotation['quotation_id'] ?>"
                                                                title="Quotation PDF" style="padding: 1px 5px; "><i
                                                                    class="fa fa-file-pdf-o" style="padding:3px"></i></a>
                                                            <!--<a class="downloadcertificate pull-right" data-customer="<?php echo $_GET['customerid'] ?>"  data-service="<?php echo $spservices['sp_id'] ?>" data-certfication="<?php echo $spservices['id'] ?>" title="Download audit application" style="padding: 1px 5px;"><i class="fa fa-download fa-lg " style="padding: 3px"></i></a>-->
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
            //                var serviceType = $(".service-type").val();
            var fromDate = $(".filterFromDate").val();
            var toDate = $(".filterToDate").val();
            //                var isServiceType = false;
            var isfromDate = false;
            var isToDate = false;
            //            alert(serviceType);
            if (fromDate == "" && toDate == "") {
                return true;
            }
            //                if (serviceType != "") {
            //                    var servieTypeStr = data[6] || ""; // use data for the age column
            ////                    alert(servieTypeStr);
            //                    if (servieTypeStr.toUpperCase().trim() == serviceType.toUpperCase().trim()) {
            //                        //                alert("test");
            //                        isServiceType = true;
            //                    }
            //                } else {
            //                    isServiceType = true;
            //                }
            var dateValue = data[4] || "";
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
            finalResult = (isfromDate && isToDate);
            return finalResult;
        }
    );

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

        var debouncedAgreementSearch = debounce(function () {
            table.column(0).search($('#agreementClientSearch').val()).draw();
        }, 200);

        $('#agreementClientSearch').on('input', debouncedAgreementSearch);

        $('.downloadAgreementReport').click(function () {
            var fromDate = $('#filterFromDate').val();
            var toDate = $('#filterToDate').val();
            var clientName = $('#agreementClientSearch').val();
            var url = 'exportAgreementReport.php';
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                '<input type="hidden" name="fromDate" value="' + fromDate + '" />' +
                '<input type="hidden" name="toDate" value="' + toDate + '" />' +
                '<input type="hidden" name="clientName" value="' + clientName + '" />' +
                '</form>');
            $('body').append(form);
            form.submit();
            form.remove();
        });

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

        $('.filterFromDate, .filterToDate').change(function () {
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

        $(".viewQuotation").click(function () {
            var customer = $(this).attr('data-customer');
            var certification = $(this).attr('data-certfication');
            var service = $(this).attr("data-service");
            var url = 'Csa.php';
            var form = $('<form action="' + url + '" method="post" target="_blank" >' +
                '<input type="hidden" name="csId" value="' + customer + '" />' +
                '<input type="hidden" name="certification" value="' + certification + '" />' +
                '<input type="hidden" name="service" value="' + service + '" />' +
                '</form>');
            $('body').append(form);
            form.submit();
        });

        //        $('.viewQuotation').click(function (e) {
        //         //            alert(ServiceId)
        //         var customerID = $(this).attr('data-customer');
        //         var quotation_id ;
        //        var url = '';
        //            var form = '';
        //            $.ajax({
        //                url: "checkCustomer.php",
        //                type: "GET",
        //                dataType: "json",
        //                data: {
        //                    
        //                    customerId: customerID,
        //                },
        //                success: function (data)
        //                {
        //                    if (data[0] == "no deta") {  
        //                         quotation_id = data[1];  alert(data[1]);                    
        //                    } 
        //                    else {
        //                          quotation_id = data[1];
        //                          console.log(quotation_id);
        //                        }
        //                },
        //                error: function (data, status, err)
        //                {
        //                    alert(data[0],data[1]);
        //                }
        //            });
        //             
        //             url = 'Csa.php';
        //                          form = $('<form action="' + url + '" method="post" target="_blank">' +
        //                    '<input type="hidden" name="csId" value="' + customerID + '" />' +
        //                    '<input type="hidden" name="ServiceId" value="' + quotation_id + '" />' +
        //                    '</form>');
        //            $('body').append(form);
        //            form.submit();
        //            
        //        });

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

        $('.editQuotation').click(function () {
            $.ajax({
                url: "getInvoiceInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    quotation: $(this).attr('data-id'),
                },
                success: function (data) {
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
                error: function (data, status, err) {
                    alert(status);
                }
            });
        });

    });
</script>
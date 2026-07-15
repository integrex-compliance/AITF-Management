<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->

    <?php
    $pageIdentifier = "cdc objrctive";
    include 'header.php';
    include 'dbConnect.php';

    $cdcobjlist = "SELECT ci.id, ci.customer_name, svc.service_id, svc.service_type FROM customer_info ci join service svc on ci.id = svc.customer_id where ci.is_deleted = 'NO' and (svc.service_type != 'CR' and svc.service_type != 'PTR' and svc.service_type != 'TA')";
    $statement = $db->prepare($cdcobjlist);
    $statement->execute();
    $listOfCustomer = $statement->fetchAll();

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     * 
     adding debug logs 
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
                <div class="row">
                    <div class="col-sm-12 main_content" style="">
                        <div class="row" style="padding-top:10px;">
                            <div class="col-sm-1"><a class="btn btn-info filterBtn"><i class="fa fa-filter "></i></a></div>                            
                            <div class="col-sm-4 float-label-control custom-float-label-control">
                                <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="Audit Process Date From" value="">
                            </div>
                            <div class="col-sm-4 form-group float-label-control custom-float-label-control">
                                <input type="text" class="filterToDate" id="filterToDate" name="filterToDate" placeholder="Audit Process Date To" value="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix" >
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list" width="100%">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <small><a>Client Name</a></small>
                                    </th>
                                    <th class="no-sort">
                                        <small><a>Audit Type</a></small>
                                    </th>
                                    <th class="no-sort">
                                        <small><a>Audit Process Completed On</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>CDC Date</a></small>
                                    </th>
                                    <th class="no-sort">
                                        <small><a>Days</a></small>
                                    </th>
                                    <th class="no-sort">
                                        <small><a>Remark(s) if any</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $serviceid = '';
                                $datediff = '';
                                foreach ($listOfCustomer as $customer) {
                                    if ($customer['service_type'] == "CA") {
                                        $casvc = "SELECT ca_id FROM `service_type_ca` WHERE service = :service";
                                        $statement = $db->prepare($casvc);
                                        $statement->bindParam(":service", $customer["service_id"]);
                                        $statement->execute();
                                        $caservice = $statement->fetch();
                                        $serviceid = $caservice["ca_id"];
                                    } else if ($customer['service_type'] == "SA-I") {
                                        $saisvc = "SELECT sai_id FROM `service_type_sai` WHERE service =  :service";
                                        $statement = $db->prepare($saisvc);
                                        $statement->bindParam(":service", $customer["service_id"]);
                                        $statement->execute();
                                        $saiservice = $statement->fetch();
                                        $serviceid = $saiservice["sai_id"];
                                    } else if ($customer['service_type'] == "SA-II") {
                                        $saiisvc = "SELECT saii_id FROM `service_type_saii` WHERE service =  :service";
                                        $statement = $db->prepare($saiisvc);
                                        $statement->bindParam(":service", $customer["service_id"]);
                                        $statement->execute();
                                        $saiiservice = $statement->fetch();
                                        $serviceid = $saiiservice["saii_id"];
                                    } else if ($customer['service_type'] == "RC") {
                                        $rcsvc = "SELECT rc_id FROM `service_type_rc` WHERE service =  :service";
                                        $statement = $db->prepare($rcsvc);
                                        $statement->bindParam(":service", $customer["service_id"]);
                                        $statement->execute();
                                        $rcservice = $statement->fetch();
                                        $serviceid = $rcservice["rc_id"];
                                    } else if ($customer['service_type'] == "SP") {
                                        $spsvc = "SELECT sp_id FROM `service_type_sp` WHERE service =  :service";
                                        $statement = $db->prepare($spsvc);
                                        $statement->bindParam(":service", $customer["service_id"]);
                                        $statement->execute();
                                        $spservice = $statement->fetch();
                                        $serviceid = $spservice["sp_id"];
                                    }
                                    $cdccount = "SELECT COUNT(crr_id) as id FROM `cdc_report_review` WHERE ca_service_id = :service";
                                    $statement = $db->prepare($cdccount);
                                    $statement->bindParam(":service", $serviceid);
                                    $statement->execute();
                                    $cdccnt = $statement->fetch();
                                    if ($cdccnt['id'] > 0) {
                                        $cdcdetails = "SELECT crr_id, audit_performance, Date, cdc_obj_comment FROM `cdc_report_review` WHERE ca_service_id = :service";
                                        $statement = $db->prepare($cdcdetails);
                                        $statement->bindParam(":service", $serviceid);
                                        $statement->execute();
                                        $cdcdates = $statement->fetch();
                                        if (isset($cdcdates["audit_performance"]) && !empty($cdcdates["audit_performance"])) {
                                            $date1 = date_create($cdcdates["audit_performance"]);
                                            $date2 = date_create($cdcdates["Date"]);
                                            $diff = date_diff($date1, $date2);
                                            $datediff = $diff->format("%a");
                                            if ($datediff > 5) {
                                                $textcolor = 'red';
                                            } else if ($datediff < 6) {
                                                $textcolor = 'black';
                                            }
                                        } else {
                                            $datediff = "";
                                        }
                                        ?> 
                                        <tr class="" data-id="<?php echo $customer['id'] ?>" >
                                            <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $customer["customer_name"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $customer['service_type'] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><span><?php echo simpleDateConversion($cdcdates['audit_performance']); ?>~</span><?php echo $serviceid." -> ". simpleDateConversion($cdcdates['audit_performance']); ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo simpleDateConversion($cdcdates['Date']); ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small style="color: <?php echo $textcolor ?>"><?php echo $datediff ?></small>
                                            </td>
                                            <td data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                <div class="table-edit-control">
                                                    <textarea rows="3" class="saveChanges" data-row="<?php echo $cdcdates["crr_id"] ?>" data-column="cdc_obj_comment"><?php echo $cdcdates["cdc_obj_comment"] ?></textarea>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
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
                finalResult = (isfromDate && isToDate);
                return finalResult;
            });

    $(document).ready(function () {

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

        $('.saveChanges').change(function (e) {
            saveData($(this));
        });

        function saveData($selector) {

            var checkedValue = $selector.val();
            //            alert(checkedValue);
            $.ajax({
                url: "savecdccomments.php",
                type: "GET",
                //                dataType: 'JSON',
                data: {
                    id: $selector.attr('data-row'),
                    columnName: $selector.attr('data-column'),
                    columnValue: checkedValue,
                },
                error: function (xhr, status, error) {
                    alert("There seems to be issue saving data!");
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
        "ordering": true,
        "lengthChange": false,
//            "pageLength": 10,
        "paging": false,
        "order": [[3, 'asc']],
        "columnDefs": [
            {
                orderable: false,
                targets: "no-sort"
            },
            {
                "width": "15%",
                "targets": 0
            },
            {
                "width": "10%",
                "targets": 1
            },
            {
                "width": "10%",
                "targets": 2
            },
            {
                "width": "10%",
                "targets": 3
            },
            {
                "width": "10%",
                "targets": 4
            },
        ]
    });
//    var data = table
//    .column( 0 )
//    .data()
//    .sort('desc');
    table.columns.adjust().draw();
</script> 

<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->

    <?php
    $pageIdentifier = "app review objrctive";
    include 'header.php';
    include 'dbConnect.php';

    $appobjlist = "SELECT ci.id, ci.customer_name, svc.service_id, svc.service_type FROM customer_info ci join service svc on ci.id = svc.customer_id where ci.is_deleted = 'NO' and svc.service_type = 'CR'";
    $statement = $db->prepare($appobjlist);
    $statement->execute();
    $listOfCustomer = $statement->fetchAll();

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
            <div class="container">
                <br>
                <div class="row">
                    <div class="col-sm-12 main_content" style="">
                        <div class="row" style="padding-top:10px;">
                            <div class="col-sm-1"><a class="btn btn-info filterBtn"><i class="fa fa-filter "></i></a></div>                            
                            <div class="col-sm-4 float-label-control custom-float-label-control">
                                <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="Application Received Date From" value="">
                            </div>
                            <div class="col-sm-4 form-group float-label-control custom-float-label-control">
                                <input type="text" class="filterToDate" id="filterToDate" name="filterToDate" placeholder="Application Received Date To" value="">
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
                                        <small><a>Application Received Date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Review of Application Date</a></small>
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
                                    $appcount = "SELECT COUNT(audit_id) as id  FROM `audit_application` WHERE uni_svc_id =  :service";
                                    $statement = $db->prepare($appcount);
                                    $statement->bindParam(":service", $customer["service_id"]);
                                    $statement->execute();
                                    $appcnt = $statement->fetch();
                                    if ($appcnt['id'] > 0) {
                                        $appdetails = "SELECT audit_id, application_rcv_date, review_date, app_obj_comment FROM `audit_application` WHERE uni_svc_id = :service";
                                        $statement = $db->prepare($appdetails);
                                        $statement->bindParam(":service", $customer["service_id"]);
                                        $statement->execute();
                                        $appdates = $statement->fetch();
//
                                        if (isset($appdates["application_rcv_date"]) && !empty($appdates["application_rcv_date"])) {
                                            $date1 = date_create($appdates["application_rcv_date"]);
                                            $date2 = date_create($appdates["review_date"]);
                                            $diff = date_diff($date1, $date2);
                                            $datediff = $diff->format("%a");
                                            if ($datediff > 2) {
                                                $textcolor = 'red';
                                            } else if ($datediff < 3) {
                                                $textcolor = 'black';
                                            }
                                        } else {
                                            $datediff = "";
                                        }
                                        ?> 
                                        <tr >
                                            <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo $customer["customer_name"] ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><span><?php echo simpleDateConversion($appdates['application_rcv_date']); ?>~</span><?php echo simpleDateConversion($appdates['application_rcv_date']); ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo simpleDateConversion($appdates['review_date']); ?></small>
                                            </td>
                                            <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small style="color: <?php echo $textcolor ?>"><?php echo $datediff ?></small>
                                            </td>
                                            <td data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                <div class="table-edit-control">
                                                    <textarea rows="3" class="saveChanges" data-row="<?php echo $appdates["audit_id"] ?>" data-column="app_obj_comment"><?php echo $appdates["app_obj_comment"] ?></textarea>
                                                </div>
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
                var dateValue = data[1] || "";
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
                    app_id: $selector.attr('data-row'),
                    column_Name: $selector.attr('data-column'),
                    column_Value: checkedValue,
                },
                error: function (xhr, status, error) {
                    alert("There seems to be issue saving data!");
                },
                success: function (data) {
                    alert(data);
                }
            });

            $selector.removeClass("glowing-border");
            //           $(this).removeClass("form-group ");
            $selector.addClass("lookslike-label");
        }

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

        $('.saveChanges').change(function (e) {
            saveData($(this));
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
//            "scrollY": "450px",
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": false,
        "ordering": true,
        "lengthChange": false,
//            "pageLength": 10,
        "paging": false,
        "order": [[2, 'asc']],
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
//            {
//                "width": "10%",
//                "targets": 4
//            },
        ]
                //        "paging":         false
    });
    table.columns.adjust().draw();
</script>
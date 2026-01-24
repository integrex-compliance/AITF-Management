<div id="wrapper" class="active">
    <?php
//    session_start();
    $pageIdentifier = "Home Page";
    include 'header.php';
    include 'dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    $ststus = "YES";
//    $customerList = "SELECT * FROM customer_info where is_deleted <> 'YES' ORDER BY unique_id";
    $customerList = "SELECT ci.* , ac.certification_date FROM customer_info ci left join allotment_count ac on ci.id = ac.client_id where is_deleted <> 'YES' ORDER BY ac.certification_date DESC";
    $statement = $db->prepare($customerList);
    $statement->execute();
    $listOfCustomer = $statement->fetchAll();

    $services = "SELECT * FROM service_types";
    $statement = $db->prepare($services);
    $statement->execute();
    $servicesTypes = $statement->fetchAll();

    $users = "SELECT * FROM `handler`";
    $statement = $db->prepare($users);
    $statement->execute();
    $allUsers = $statement->fetchAll();

    // Build handler lookup to avoid per-row queries
    $handlerMap = array();
    foreach ($allUsers as $handlerRow) {
        $handlerMap[$handlerRow['id']] = $handlerRow['handler_name'];
    }

    // Collect customer ids for batch queries
    $customerIds = array();
    foreach ($listOfCustomer as $c) {
        $customerIds[] = $c['id'];
    }

    $serviceMap = array();
    $serviceNonSpMap = array();
    $dueMap = array();
    $paymentMap = array();

    if (!empty($customerIds)) {
        $inPlaceholders = implode(',', array_fill(0, count($customerIds), '?'));

        // Latest service per customer (any type)
        $sqlLatestService = "SELECT s.* FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
        $stmt = $db->prepare($sqlLatestService);
        $stmt->execute($customerIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $serviceMap[$row['customer_id']] = $row;
        }

        // Latest non-SP service_id per customer for due dates
        $sqlNonSpService = "SELECT s.customer_id, s.service_id FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE service_type <> 'SP' AND customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
        $stmt = $db->prepare($sqlNonSpService);
        $stmt->execute($customerIds);
        $serviceIdsForDates = array();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $serviceNonSpMap[$row['customer_id']] = $row['service_id'];
            $serviceIdsForDates[] = $row['service_id'];
        }

        // Due dates for the latest non-SP service
        if (!empty($serviceIdsForDates)) {
            $inServiceIds = implode(',', array_fill(0, count($serviceIdsForDates), '?'));
            $sqlDates = "SELECT service_id, action, due_date FROM ca_service_dates WHERE service_id IN ($inServiceIds) AND action IN ('Suspension Letter','Expire Letter','Withdrawal Letter')";
            $stmt = $db->prepare($sqlDates);
            $stmt->execute($serviceIdsForDates);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $dueMap[$row['service_id']][$row['action']] = $row['due_date'];
            }
        }

        // Payments per customer
        $sqlPayments = "SELECT customer_id, SUM(balance) as payment_due FROM payment_system WHERE customer_id IN ($inPlaceholders) GROUP BY customer_id";
        $stmt = $db->prepare($sqlPayments);
        $stmt->execute($customerIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $paymentMap[$row['customer_id']] = $row['payment_due'];
        }
    }


    ?>

  

    <style>
        .custom-float-label-control {
            margin-bottom: 0em;
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" >
                
                <div class="row">
                    <div class="col-sm-12 main_content" style="">
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
                                <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="Service Due From" value="">
                            </div>
                            <div class="col-sm-3 form-group float-label-control custom-float-label-control">
                                <input type="text" class="filterToDate" id="filterToDate" name="filterToDate" placeholder="Service Due To" value="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row clearfix" >
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list" width="100%">
                            <thead>
                                <tr>
                                    <th class="" >
                                        <small>Sr.</small>
                                    </th>
                                    <th class="" >
                                        <small><a>Unique Id</a></small>
                                    </th>
                                    <th class="" >
                                        <small><a>Agreement Date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Name of Client</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Contact person </a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Current Status</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Next Ser due on date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Next Ser Type</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Hand</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Comments</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 0;
                                foreach ($listOfCustomer as $customer) {
                                    $count = $count + 1;
                                    // Latest service for this customer
                                    $serviceInfo = isset($serviceMap[$customer['id']]) ? $serviceMap[$customer['id']] : array();

                                    // Due dates from pre-fetched map
                                    $dueInfo = $dueInfoex = $sentInfo = array();
                                    $sid = isset($serviceNonSpMap[$customer['id']]) ? $serviceNonSpMap[$customer['id']] : null;
                                    if ($sid && isset($dueMap[$sid])) {
                                        $dueInfo['due_date'] = isset($dueMap[$sid]['Suspension Letter']) ? $dueMap[$sid]['Suspension Letter'] : null;
                                        $dueInfoex['due_date'] = isset($dueMap[$sid]['Expire Letter']) ? $dueMap[$sid]['Expire Letter'] : null;
                                        $sentInfo['due_date'] = isset($dueMap[$sid]['Withdrawal Letter']) ? $dueMap[$sid]['Withdrawal Letter'] : null;
                                    }

                                    // Payment due from map
                                    $payInfo = array('payment_due' => isset($paymentMap[$customer['id']]) ? $paymentMap[$customer['id']] : null);
                                    ?>
                                    <tr id='addr<?php echo $customer['id'] ?>' class="<?php echo ($customer['audit_status'] === 'Application canceled') ? 'canceled-row' : ''; ?>">
                                        <td data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $count ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><?php if (isset($customer['unique_id'])) echo $customer['unique_id'] ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $customer['certification_date'] ?></a></small>
                                        </td>
                                        <td class="customerAccount<?php echo ($customer['audit_status'] === 'Application canceled') ? ' canceled-status-cell' : ''; ?>" data-id="<?php echo $customer['id'] ?>" data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $customer['customer_name'] ?></a></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><?php echo $customer['contact_person'] ?></small>
                                        </td>
                                        <td class="customerAccount<?php echo ($customer['audit_status'] === 'Application canceled') ? ' canceled-status-cell' : ''; ?>" data-id="<?php echo $customer['id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><?php echo $customer['audit_status']; ?> <?php if($customer['audit_status'] == "in-process" ) echo '('. $serviceInfo["service_type"].')'; ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><span><?php if (isset($dueInfo["due_date"]) && !empty($dueInfo["due_date"])) { echo simpleDateConversion($dueInfo["due_date"]); } else {echo simpleDateConversion($dueInfoex["due_date"]);} ?>~</span><?php if (isset($dueInfo["due_date"]) && !empty($dueInfo["due_date"])) { echo simpleDateConversion($dueInfo["due_date"]); } else {echo simpleDateConversion($dueInfoex["due_date"]);} ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><?php echo $customer['next_service']; ?></small>
                                        </td>
                                        <td data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;"> 
                                            <div class="float-label-control">                                                
                                                <select class="form-control setHandler" data-id="<?php echo $customer['id'] ?>" name="setHandler" style="color: black">
                                                    <?php
                                                    if (isset($customer["handler"]) && !empty($customer["handler"]) && isset($handlerMap[$customer["handler"]])) {
                                                        ?> <option value="<?php echo $customer["handler"]; ?>"><?php echo $handlerMap[$customer["handler"]]; ?></option> <?php } else {
                                                        ?> <option value="">Hand</option> <?php }
                                                    ?>                                                    
                                                    <?php foreach ($allUsers as $users) { ?>
                                                        <option value="<?php echo $users["id"] ?>"><?php echo $users["handler_name"] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </td>
                                        <td data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;"> 
                                            <div class="table-edit-control">  
                                            <!--<small><?php echo $customer['comment'] ?></small>-->
                                                <textarea rows="3" class="saveChanges" data-row="<?php echo $customer['id'] ?>" data-column="comment" name="payComments" value="" placeholder="" ><?php if (isset($customer['comment'])) echo $customer['comment'] ?></textarea>
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

    function _(el) {
        return document.getElementById(el);
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
                    var servieTypeStr = data[7] || ""; // use data for the age column
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

        $(".setHandler").change(function () {
            $handler = $(this).val();
            $customer = $(this).attr("data-id");
            $.ajax({
                url: "updateCustomer.php",
                type: "GET",
                data: {
                    handler: $handler,
                    customer: $customer,
                }, error: function (xhr, status, error) {
                    alert("There seems to be issue saving data!");
                },
                success: function (data) {
//                    alert(data);
                }
            });
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

        $('.saveChanges').keyup(function (e) {
            saveData($(this));
        });

        function saveData($selector) {

            var checkedValue = $selector.val();
            //            alert(checkedValue);
            $.ajax({
                url: "updateCustomer.php",
                type: "GET",
                //                dataType: 'JSON',
                data: {
                    id: $selector.attr('data-row'),
                    columnName: $selector.attr('data-column'),
                    columnValue: checkedValue,
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
                "width": "1%",
                "targets": 0
            },
            {
                "width": "5%",
                "targets": 1
            },
            {
                "width": "5%",
                "targets": 2
            },
            {
                "width": "10%",
                "targets": 3
            },
            {
                "width": "5%",
                "targets": 4
            },
            {
                "width": "5%",
                "targets": 5
            },
            {
                "width": "1%",
                "targets": 6
            },
                        {
                "width": "1%",
                "targets": 7
            },
            {
                "width": "72%",
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
</script>

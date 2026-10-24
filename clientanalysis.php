<div id="wrapper" class="active">
    <?php
//    session_start();
    $pageIdentifier = "Client Analysis";
    include 'header.php';
    include 'dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    $invRole = trim($_SESSION['role'] ?? '');
    $isMarketingHead = in_array($invRole, array('Marketing Head', 'Marketing Executive'), true)
        || strtolower(str_replace(' ', '', $invRole)) === 'marketinghead'
        || (isset($_SESSION['username']) && strtolower(trim($_SESSION['username'])) === 'marketingh');
    $isOfficeStaffA = in_array($invRole, array('Office Staff-A'), true)
        || strtolower(str_replace(array(' ', '-'), '', $invRole)) === 'officestaffa';
    $isRestrictedRole = $isMarketingHead || $isOfficeStaffA;

    if (!aitf_has_access("Client Analysis")) {
        echo "<script>window.location.href='homePage.php';</script>";
        exit;
    }

    $ststus = "YES";
    $extraCustomerFilter = "";
    require_once __DIR__ . '/include/rbac_helper.php';
    if (!aitf_can_access_customer(1191, $invRole)) {
        $extraCustomerFilter .= " AND ci.id != 1191";
    }
    if ($isRestrictedRole) {
        $extraCustomerFilter = " AND (ci.audit_status IS NULL OR (LOWER(ci.audit_status) NOT LIKE '%active%' AND LOWER(ci.audit_status) NOT LIKE '%withdraw%'))";
    }
    $customerList = "SELECT ci.* , ac.certification_date FROM customer_info ci left join allotment_count ac on ci.id = ac.client_id where is_deleted <> 'YES'" . $extraCustomerFilter . " ORDER BY ac.certification_date desc";
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

    // Handler lookup
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

        // Latest service per customer
        $sqlLatestService = "SELECT s.* FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE (is_deleted = 'NO' OR is_deleted IS NULL) AND customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
        $stmt = $db->prepare($sqlLatestService);
        $stmt->execute($customerIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $serviceMap[$row['customer_id']] = $row;
        }

        // Latest non-SP service id per customer (for due dates)
        $sqlNonSpService = "SELECT s.customer_id, s.service_id FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE (is_deleted = 'NO' OR is_deleted IS NULL) AND service_type <> 'SP' AND customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
        $stmt = $db->prepare($sqlNonSpService);
        $stmt->execute($customerIds);
        $serviceIdsForDates = array();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $serviceNonSpMap[$row['customer_id']] = $row['service_id'];
            $serviceIdsForDates[] = $row['service_id'];
        }

        // Due dates for latest non-SP service
        if (!empty($serviceIdsForDates)) {
            $ph = implode(',', array_fill(0, count($serviceIdsForDates), '?'));
            $sqlDates = "SELECT service_id, action, due_date FROM ca_service_dates WHERE service_id IN ($ph) AND action IN ('Suspension Letter','Withdrawal Letter','Expire Letter')";
            $stmt = $db->prepare($sqlDates);
            $stmt->execute($serviceIdsForDates);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $dueMap[$row['service_id']][$row['action']] = $row['due_date'];
            }
        }

        // Payments per customer
        $sqlPayments = "SELECT customer_id, SUM(balance) as balancesum FROM payment_system WHERE customer_id IN ($inPlaceholders) GROUP BY customer_id";
        $stmt = $db->prepare($sqlPayments);
        $stmt->execute($customerIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $paymentMap[$row['customer_id']] = $row['balancesum'];
        }
    }
    ?>
    <style>
        .custom-float-label-control {
            margin-bottom: 0em;
        }

        /* ===== Modern DataTable Footer (Showing entries + Pagination) ===== */
        .dt-bottom-row {
            margin: 0 !important;
            padding: 12px 18px !important;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 10px;
        }
        .dataTables_wrapper .dataTables_info {
            padding: 0 !important;
            margin: 0 !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #475569 !important;
            line-height: 32px;
            display: block !important;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding: 0 !important;
            margin: 0 !important;
            text-align: right !important;
        }
        .dataTables_wrapper .dataTables_paginate ul.pagination {
            margin: 0 !important;
            white-space: nowrap;
        }
        .dataTables_wrapper .dataTables_paginate .pagination > li > a,
        .dataTables_wrapper .dataTables_paginate .pagination > li > span {
            color: #163c6e;
            border-color: #e2e8f0;
            font-size: 12.5px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            margin: 0 2px;
        }
        .dataTables_wrapper .dataTables_paginate .pagination > .active > a,
        .dataTables_wrapper .dataTables_paginate .pagination > .active > span {
            background: linear-gradient(135deg, #163c6e 0%, #1e4b8a 100%) !important;
            border-color: #163c6e !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(22, 60, 110, 0.25);
        }
        .dataTables_wrapper .dataTables_paginate .pagination > .disabled > a,
        .dataTables_wrapper .dataTables_paginate .pagination > .disabled > span {
            color: #94a3b8 !important;
            border-color: #eef2f6 !important;
            background-color: #f8fafc !important;
        }

        html.theme-dark .dt-bottom-row {
            border-top-color: #334155 !important;
            background: #111827 !important;
        }
        html.theme-dark .dataTables_wrapper .dataTables_info {
            color: #94a3b8 !important;
        }
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > li > a,
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > li > span {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
            color: #e5e7eb !important;
        }
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > .active > a,
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > .active > span {
            background: #3b82f6 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
        }
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > .disabled > a,
        html.theme-dark .dataTables_wrapper .dataTables_paginate .pagination > .disabled > span {
            background-color: #111827 !important;
            border-color: #374151 !important;
            color: #64748b !important;
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" >                
                <!--                <div class="row">
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
                                </div>-->

                <div class="row clearfix" >
                    <div class="col-sm-12 main_content table-responsive" style="padding-top:10px;">
                        <table class="table table-hover table-sortable table-striped" id="client-analysis-list" width="100%">
                            <thead>
                                <tr>
<!--                                    <th class="" >
                                        <small>Sr</small>
                                    </th>-->
                                    <th class="" >
                                        <small><a>Unique Id</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Client Name</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Contact person</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Contact No.</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>GSTIN No.</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Status</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Next Ser due date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Agreement Date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Payment Due</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Iaf/Nace</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Next Ser Type</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Hand</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 0;
                                foreach ($listOfCustomer as $customer) {
                                    if ($isRestrictedRole) {
                                        $cStatus = strtolower(trim($customer['audit_status'] ?? ''));
                                        if (strpos($cStatus, 'active') !== false || strpos($cStatus, 'withdraw') !== false) {
                                            continue;
                                        }
                                    }
                                    $count = $count + 1;
                                    $serviceInfo = isset($serviceMap[$customer['id']]) ? $serviceMap[$customer['id']] : array();
                                    $sid = isset($serviceNonSpMap[$customer['id']]) ? $serviceNonSpMap[$customer['id']] : null;
                                    $dueInfo = $sentInfo = $dueInfoex = array();
                                    if ($sid && isset($dueMap[$sid])) {
                                        $dueInfo['due_date'] = isset($dueMap[$sid]['Suspension Letter']) ? $dueMap[$sid]['Suspension Letter'] : null;
                                        $dueInfoex['due_date'] = isset($dueMap[$sid]['Expire Letter']) ? $dueMap[$sid]['Expire Letter'] : null;
                                        $sentInfo['due_date'] = isset($dueMap[$sid]['Withdrawal Letter']) ? $dueMap[$sid]['Withdrawal Letter'] : null;
                                    }
                                    $payInfo = array('balancesum' => isset($paymentMap[$customer['id']]) ? $paymentMap[$customer['id']] : null);
                                    ?>  
                                    <tr class="customerAccount" data-id="<?php echo $customer['id']; ?>">
<!--                                        <td data-name="name" >
                                            <small><?php echo $count ?></small>
                                        </td>-->
                                        <td >
                                            <small><?php if (isset($customer['unique_id'])) echo $customer['unique_id'] ?></small>
                                        </td>
                                        <td >
                                            <small><?php echo $customer['customer_name'] ?></small>
                                        </td>
                                        <td >
                                            <small><?php echo $customer['contact_person'] ?></small>
                                        </td>
                                        <td >
                                            <small><?php echo $customer['contact'] ?></small>
                                        </td>
                                        <td >
                                            <small><?php echo $customer['gstin_no'] ?></small>
                                        </td>
                                        <td >
                                            <small><?php echo $customer['audit_status'] ?></small>
                                        </td>
                                        <td >
                                            <small><span><?php echo getFlatDate($dueInfo['due_date']); ?>~</span><?php if (isset($dueInfo["due_date"])) echo simpleDateConversion($dueInfo["due_date"]); ?></small>
                                        </td>
                                        <td >
                                            <small><span><?php echo getFlatDate($customer['certification_date']); ?>~</span><?php if (isset($dueInfo["due_date"])) echo simpleDateConversion($customer['certification_date']); ?></small>
                                        </td>
                                        <td >
                                            <small><?php echo $payInfo['balancesum']; ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo $customer['aif_nace']; ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo $customer['next_service']; ?></small>
                                        </td>
                                        <td> 
                                            <?php
                                            if (isset($customer["handler"]) && !empty($customer["handler"]) && isset($handlerMap[$customer['handler']])) {
                                                ?>
                                                <small><?php echo $handlerMap[$customer['handler']]; ?></small>
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
<script type="text/javascript">

    function _(el) {
        return document.getElementById(el);
    }

    var isMarketingHead = <?php echo $isMarketingHead ? 'true' : 'false'; ?>;
    var isOfficeStaffA = <?php echo $isOfficeStaffA ? 'true' : 'false'; ?>;
    var isRestrictedRole = <?php echo $isRestrictedRole ? 'true' : 'false'; ?>;

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
            var tableId = settings.nTable.id;
            if (tableId !== 'client-analysis-list') {
                return true;
            }

            if (isRestrictedRole) {
                var statusStr = (data[5] || "").replace(/<[^>]*>/g, "").toLowerCase().trim();
                if (statusStr.indexOf('active') !== -1 || statusStr.indexOf('withdraw') !== -1) {
                    return false;
                }
            }

            var serviceType = ($(".service-type").val() || "").trim();
            var fromDate = ($(".filterFromDate").val() || "").trim();
            var toDate = ($(".filterToDate").val() || "").trim();
            var isServiceType = false;
            var isfromDate = false;
            var isToDate = false;

            if (serviceType == "" && fromDate == "" && toDate == "") {
                return true;
            }
            if (serviceType != "") {
                var servieTypeStr = (data[10] || "").replace(/<[^>]*>/g, "").trim();
                if (servieTypeStr.toUpperCase() == serviceType.toUpperCase()) {
                    isServiceType = true;
                }
            } else {
                isServiceType = true;
            }
            var dateValue = data[6] || "";
            var dateSplit = dateValue.split("~");
            var rowDate = (dateSplit.length > 1 ? dateSplit[1] : dateSplit[0]).replace(/<[^>]*>/g, "").trim();

            if (fromDate != "") {
                isfromDate = compareMinDates(fromDate, rowDate);
            } else {
                isfromDate = true;
            }
            if (toDate != "") {
                isToDate = compareMaxDates(toDate, rowDate);
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

        $('.saveChanges').change(function (e) {
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

    var table = $('#client-analysis-list').DataTable({
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": true,
        "pageLength": 50,
        "deferRender": true,
        "ordering": false,
        "lengthChange": false,
        "info": true,
        "dom": "tr<'row dt-bottom-row'<'col-sm-6 col-xs-12'i><'col-sm-6 col-xs-12'p>>",
        "columnDefs": [
            {
                "width": "8%",
                "targets": 0
            },
            {
                "width": "16%",
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
            {
                "width": "8%",
                "targets": 5
            },
            {
                "width": "10%",
                "targets": 6
            },
            {
                "width": "10%",
                "targets": 7
            },
            {
                "width": "8%",
                "targets": 8
            },
            {
                "width": "8%",
                "targets": 9
            },
            {
                "width": "8%",
                "targets": 10
            },
            {
                "width": "8%",
                "targets": 11
            }
        ],
        "language": {
            "emptyTable": "No data available in table",
            "zeroRecords": "No matching records found",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
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

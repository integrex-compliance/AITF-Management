 <div id="wrapper" class="active">
    <?php
//    session_start();
    $pageIdentifier = "cert issue list";
    include 'header.php';
    include 'dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

//    $sql = "SELECT ci.id, customer_name, address, contact, email_id, scope, aif_nace, svc.service_id, service_type  FROM customer_info ci join service svc on ci.id = svc.customer_id WHERE is_deleted <> 'Y'";
//    $sql = "SELECT distinct ca.aggrement_no, svc.service_id, ci.id, customer_name, address, contact_person, contact, email_id, scope, aif_nace, service_type, aggrement_date  FROM customer_info ci join service svc on ci.id = svc.customer_id right join certification_aggrement ca on ci.id = ca.customer_id WHERE ci.is_deleted <> 'Y' ORDER BY ca.aggrement_date DESC";
//    $sql = "SELECT ci.id, customer_name, address, contact_person, contact, email_id, scope, aif_nace, svc.service_id, service_type  FROM customer_info ci join  service svc on ci.id = svc.customer_id WHERE ci.is_deleted <> 'Y'";
    $sql = "SELECT ci.id, customer_name, address, contact_person, contact, email_id, scope, aif_nace, svc.service_id, service_type, ci.audit_status  FROM customer_info ci join  service svc on ci.id = svc.customer_id WHERE ci.is_deleted <> 'Y' AND (service_type <> 'CR' AND service_type <> 'SP' AND service_type <> 'Transition Audit' AND service_type <> 'PTR')";
    $statement = $db->prepare($sql);
    $statement->execute();
    $listOfCustomer = $statement->fetchAll();
//    print_r($listOfCustomer);    

    $services = "SELECT * FROM service_types";
    $statement = $db->prepare($services);
    $statement->execute();
    $servicesTypes = $statement->fetchAll();

    // Batch preload mappings to avoid per-row queries
    $serviceIdsByType = [
        'CA' => [], 'SA-I' => [], 'SA-II' => [], 'RC' => [], 'SP' => []
    ];

    foreach ($listOfCustomer as $c) {
        if (isset($serviceIdsByType[$c['service_type']])) {
            $serviceIdsByType[$c['service_type']][] = $c['service_id'];
        }
    }

    $caServiceIdMap = [];
    $caServiceIds = [];
    // Helper to load subtype tables
    $loadSubtype = function($table, $col, $serviceIds) use (&$db) {
        if (empty($serviceIds)) return [];
        $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
        $sql = "SELECT {$col} AS subtype_id, service FROM {$table} WHERE service IN ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute($serviceIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    };

    foreach ([
        ['type' => 'CA', 'table' => 'service_type_ca', 'col' => 'ca_id'],
        ['type' => 'SA-I', 'table' => 'service_type_sai', 'col' => 'sai_id'],
        ['type' => 'SA-II', 'table' => 'service_type_saii', 'col' => 'saii_id'],
        ['type' => 'RC', 'table' => 'service_type_rc', 'col' => 'rc_id'],
        ['type' => 'SP', 'table' => 'service_type_sp', 'col' => 'sp_id'],
    ] as $cfg) {
        $rows = $loadSubtype($cfg['table'], $cfg['col'], $serviceIdsByType[$cfg['type']]);
        foreach ($rows as $row) {
            $caServiceIdMap[$row['service']] = $row['subtype_id'];
            $caServiceIds[] = $row['subtype_id'];
        }
    }

    // Load CDC reviews for all subtype ids at once
    $cdcMap = [];
    if (!empty($caServiceIds)) {
        $placeholders = implode(',', array_fill(0, count($caServiceIds), '?'));
        $sqlCdc = "SELECT * FROM cdc_report_review WHERE ca_service_id IN ($placeholders)";
        $stmt = $db->prepare($sqlCdc);
        $stmt->execute($caServiceIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $cdcMap[$row['ca_service_id']] = $row;
        }
    }

    // Office certificate numbers for fallback
    $officeCertMap = [];
    if (!empty($cdcMap)) {
        $crrIds = array_column($cdcMap, 'crr_id');
        $crrIds = array_filter($crrIds, function($v){ return $v !== null && $v !== ''; });
        if (!empty($crrIds)) {
            $placeholders = implode(',', array_fill(0, count($crrIds), '?'));
            $sqlOffice = "SELECT crr_id, certificate_no FROM cdc_report_review_office_use WHERE crr_id IN ($placeholders) AND certificate_no IS NOT NULL AND certificate_no != '' ORDER BY iso_index ASC";
            $stmt = $db->prepare($sqlOffice);
            $stmt->execute($crrIds);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (!isset($officeCertMap[$row['crr_id']])) {
                    $officeCertMap[$row['crr_id']] = $row['certificate_no'];
                }
            }
        }
    }
    ?>

    <!-- Sidebar -->
    <!-- Sidebar -->

    <style>
        .custom-float-label-control {
            margin-bottom: 0em;
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" >
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
                        <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="CDC Issued From" value="">
                    </div>
                    <div class="col-sm-3 form-group float-label-control custom-float-label-control">
                        <input type="text" class="filterToDate" id="filterToDate" name="filterToDate" placeholder="CDC Issued To" value="">
                    </div>
                </div>
                <div class="row clearfix" style="padding-top:10px;">
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_certificate_issue_list" width="100%">
                            <thead>
                                <tr>
<!--                                    <th class="">
                                        <small><a>Sr.</a></small>
                                    </th>-->
                                    <th class="">
                                        <small><a>Cert No.</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Rev No.</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Service Type</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>CDC Date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Issue Date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Client & Address</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Accreditation</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>IAF/NACE</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Scope</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Contact Person & Contact No. & Email</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 0;
                                foreach ($listOfCustomer as $customer) {
                                    $count = $count + 1;
                                    $subtypeId = isset($caServiceIdMap[$customer['service_id']]) ? $caServiceIdMap[$customer['service_id']] : null;
                                    if (!$subtypeId || !isset($cdcMap[$subtypeId])) {
                                        continue;
                                    }

                                    $ca_cdc = $cdcMap[$subtypeId];
                                    $rawIssueDate = $ca_cdc['revDatecdc'];
                                    if ($rawIssueDate == "" || $rawIssueDate == "0000-00-00") {
                                        $rawIssueDate = $ca_cdc['Date'];
                                    }
                                    $issueDateConverted = simpleDateConversion($rawIssueDate);
                                    $issueDateVisible = ($rawIssueDate == "" || $rawIssueDate == "0000-00-00") ? "" : $issueDateConverted;
                                    $certificateNo = $ca_cdc['certificateNo'];
                                    if ($certificateNo == "" || $certificateNo === null) {
                                        if (isset($officeCertMap[$ca_cdc['crr_id']])) {
                                            $certificateNo = $officeCertMap[$ca_cdc['crr_id']];
                                        }
                                    }
                                    ?>
                                    <tr class="customerAccount <?php echo ($customer['audit_status'] === 'Application canceled') ? 'canceled-row' : ''; ?>" data-id="<?php echo $customer['id'] ?>" >
        <!--                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                <small><a ><?php echo $count; ?></a></small>
                                            </td>-->
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $certificateNo ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $ca_cdc['revCdc'] ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $customer["service_type"] ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo simpleDateConversion($ca_cdc['Date']) ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><span><?php echo $issueDateConverted; ?>~</span><a ><?php echo $issueDateVisible; ?></a></small>
                                        </td>
                                        <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $customer['customer_name'] ?></a><br><a ><?php echo $customer['address'] ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $ca_cdc['accreditation'] ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $customer['aif_nace'] ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $ca_cdc['product_service'] ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $customer['contact_person'] ?></a><br><a ><?php echo $customer['email_id'] ?></a></small>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
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
                    finalResult = (isServiceType && isfromDate && isToDate);
                    return finalResult;
                }
        );

        $('.service-type,.filterFromDate, .filterToDate').change(function () {
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

        var table = $('#tab_certificate_issue_list').DataTable({
//            "scrollY": "450px",
            "sScrollX": "100%",
            "scrollY": '70vh',
            "scrollCollapse": true,
            "paging": false,
            "ordering": true,
            "lengthChange": false,
//            "pageLength": 10,
            "paging": false,
            "dom": 'Bfrtip',
            "buttons": [
                'excel'
            ],
//            "aaSorting": [[0, 'desc']],
            "order": [[0, "desc"]],
            "columnDefs": [
                {
                    "width": "1%",
                    "targets": 0
                },
                {
                    "width": "1%",
                    "targets": 1
                },
                {
                    "width": "1%",
                    "targets": 2
                },
                {
                    "width": "1%",
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
                    "width": "3%",
                    "targets": 6
                },
                {
                    "width": "1%",
                    "targets": 7
                },
                {
                    "width": "5%",
                    "targets": 8
                },
                {
                    "width": "5%",
                    "targets": 9
                },
            ]
                    //        "paging":false
        });
        table.columns.adjust().draw();

        $(document).ready(function () {

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

//            var table = $('#tab_certificate_issue_list').DataTable({
////            "scrollY": "450px",
//                "scrollCollapse": true,
//                "lengthChange": false,
////            "pageLength": 10,
//                paging: false,
//                "columnDefs": [
//                    {
//                        "width": "1%",
//                        "targets": 0
//                    },
//                    {
//                        "width": "15%",
//                        "targets": 1
//                    },
//                    {
//                        "width": "10%",
//                        "targets": 2
//                    },
//                    {
//                        "width": "12%",
//                        "targets": 3
//                    },
//                    {
//                        "width": "10%",
//                        "targets": 4
//                    },
//                    {
//                        "width": "10%",
//                        "targets": 5
//                    },
//                    {
//                        "width": "10%",
//                        "targets": 6
//                    },
//                    {
//                        "width": "1%",
//                        "targets": 8
//                    },
//                    {
//                        "width": "25%",
//                        "targets": 9
//                    },
//                ]
//                        //        "paging":         false
//            });

        });
    </script>
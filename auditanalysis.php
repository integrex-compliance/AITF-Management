 <div id="wrapper" class="active">
    <?php
//    session_start();
    $pageIdentifier = "cert issue list";
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

    if (!aitf_has_access("Cert Issue List")) {
        echo "<script>window.location.href='homePage.php';</script>";
        exit;
    }

//    $sql = "SELECT ci.id, customer_name, address, contact, email_id, scope, aif_nace, svc.service_id, service_type  FROM customer_info ci join service svc on ci.id = svc.customer_id WHERE is_deleted <> 'Y'";
//    $sql = "SELECT distinct ca.aggrement_no, svc.service_id, ci.id, customer_name, address, contact_person, contact, email_id, scope, aif_nace, service_type, aggrement_date  FROM customer_info ci join service svc on ci.id = svc.customer_id right join certification_aggrement ca on ci.id = ca.customer_id WHERE ci.is_deleted <> 'Y' ORDER BY ca.aggrement_date DESC";
//    $sql = "SELECT ci.id, customer_name, address, contact_person, contact, email_id, scope, aif_nace, svc.service_id, service_type  FROM customer_info ci join  service svc on ci.id = svc.customer_id WHERE ci.is_deleted <> 'Y'";
    require_once __DIR__ . '/include/rbac_helper.php';
    $audit1191Filter = !aitf_can_access_customer(1191, $invRole) ? " AND ci.id != 1191" : "";
    $sql = "SELECT ci.id, customer_name, address, contact_person, contact, email_id, scope, aif_nace, svc.service_id, service_type, ci.audit_status  FROM customer_info ci join  service svc on ci.id = svc.customer_id WHERE ci.is_deleted <> 'Y'" . $audit1191Filter . " AND (service_type <> 'CR' AND service_type <> 'SP' AND service_type <> 'Transition Audit' AND service_type <> 'PTR')";
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

    // Office certificate data for fallback (multiple ISO support)
    $officeDataMap = [];
    if (!empty($cdcMap)) {
        $crrIds = array_column($cdcMap, 'crr_id');
        $crrIds = array_filter($crrIds, function($v){ return $v !== null && $v !== ''; });
        if (!empty($crrIds)) {
            $placeholders = implode(',', array_fill(0, count($crrIds), '?'));
            $sqlOffice = "SELECT crr_id, certificate_no, rev_no, rev_date FROM cdc_report_review_office_use WHERE crr_id IN ($placeholders) ORDER BY iso_index ASC";
            $stmt = $db->prepare($sqlOffice);
            $stmt->execute($crrIds);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (!isset($officeDataMap[$row['crr_id']])) {
                    $officeDataMap[$row['crr_id']] = $row;
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
                        <input type="text" class="form-control" id="certClientSearch" name="certClientSearch" placeholder="Search Client / Cert No..." autocomplete="off">
                    </div>
                    <div class="col-sm-2 float-label-control custom-float-label-control">
                        <input type="text" class="filterFromDate form-control" id="filterFromDate" name="filterFromDate" placeholder="CDC Issued From" value="" autocomplete="off">
                    </div>
                    <div class="col-sm-2 form-group float-label-control custom-float-label-control">
                        <input type="text" class="filterToDate form-control" id="filterToDate" name="filterToDate" placeholder="CDC Issued To" value="" autocomplete="off">
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
                                    $certificateNo = $ca_cdc['certificateNo'];
                                    $revNo = $ca_cdc['revCdc'];
                                    $rawIssueDate = $ca_cdc['revDatecdc'];

                                    // Fallback to office_use table for modern multi-ISO support
                                    if (isset($officeDataMap[$ca_cdc['crr_id']])) {
                                        $office = $officeDataMap[$ca_cdc['crr_id']];
                                        if (empty($certificateNo)) {
                                            $certificateNo = $office['certificate_no'];
                                        }
                                        if (empty($revNo)) {
                                            $revNo = $office['rev_no'];
                                        }
                                        if (empty($rawIssueDate) || $rawIssueDate == "0000-00-00") {
                                            $rawIssueDate = $office['rev_date'];
                                        }
                                    }

                                    if ($rawIssueDate == "" || $rawIssueDate == "0000-00-00") {
                                        $rawIssueDate = $ca_cdc['Date'];
                                    }
                                    $issueDateConverted = simpleDateConversion($rawIssueDate);
                                    $issueDateVisible = ($rawIssueDate == "" || $rawIssueDate == "0000-00-00") ? "" : $issueDateConverted;
                                    ?>
                                    <tr class="customerAccount <?php echo ($customer['audit_status'] === 'Application canceled') ? 'canceled-row' : ''; ?>" data-id="<?php echo $customer['id'] ?>" >
        <!--                                                <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                <small><a ><?php echo $count; ?></a></small>
                                            </td>-->
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $certificateNo ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $revNo ?></a></small>
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
        function debounce(callback, delay) {
            var timer;
            return function () {
                var context = this;
                var args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function () {
                    callback.apply(context, args);
                }, delay);
            };
        }

        function normalizeSearchText(val) {
            return (val || "").toString().toLowerCase().replace(/\s+/g, " ").trim();
        }

        var isMarketingHead = <?php echo $isMarketingHead ? 'true' : 'false'; ?>;
        var isOfficeStaffA = <?php echo $isOfficeStaffA ? 'true' : 'false'; ?>;
        var isRestrictedRole = <?php echo $isRestrictedRole ? 'true' : 'false'; ?>;

        function compareMinDates(filterDate, rowDate) {
            var filterDateSplit = filterDate.split(".");
            var rowDateSplit = rowDate.split(".");
            if (filterDateSplit.length === 3 && rowDateSplit.length === 3) {
                if (parseInt(filterDateSplit[2], 10) < parseInt(rowDateSplit[2], 10)) {
                    return true;
                } else if (filterDateSplit[2] === rowDateSplit[2]) {
                    if (parseInt(filterDateSplit[1], 10) < parseInt(rowDateSplit[1], 10)) {
                        return true;
                    } else if (filterDateSplit[1] === rowDateSplit[1]) {
                        return parseInt(filterDateSplit[0], 10) <= parseInt(rowDateSplit[0], 10);
                    }
                }
            }
            return false;
        }

        function compareMaxDates(filterDate, rowDate) {
            var filterDateSplit = filterDate.split(".");
            var rowDateSplit = rowDate.split(".");
            if (filterDateSplit.length === 3 && rowDateSplit.length === 3) {
                if (parseInt(filterDateSplit[2], 10) > parseInt(rowDateSplit[2], 10)) {
                    return true;
                } else if (filterDateSplit[2] === rowDateSplit[2]) {
                    if (parseInt(filterDateSplit[1], 10) > parseInt(rowDateSplit[1], 10)) {
                        return true;
                    } else if (filterDateSplit[1] === rowDateSplit[1]) {
                        return parseInt(filterDateSplit[0], 10) >= parseInt(rowDateSplit[0], 10);
                    }
                }
            }
            return false;
        }

        $.fn.DataTable.ext.search.push(
            function (settings, data, dataIndex) {
                var tableId = settings.nTable.id;
                if (tableId !== 'tab_certificate_issue_list') {
                    return true;
                }

                var serviceType = ($(".service-type").val() || "").trim();
                var fromDate = ($(".filterFromDate").val() || "").trim();
                var toDate = ($(".filterToDate").val() || "").trim();
                var clientSearch = ($("#certClientSearch").val() || "").trim();

                var certNo = normalizeSearchText(data[0] || "");
                var servieTypeStr = (data[2] || "").replace(/<[^>]*>/g, "").trim();
                var dateValue = data[4] || "";
                var dateSplit = dateValue.split("~");
                var rowDate = (dateSplit.length > 1 ? dateSplit[1] : (dateSplit[0] || "")).replace(/<[^>]*>/g, "").trim();
                var rowClient = normalizeSearchText(data[5] || "");

                // 1. Service Type filter
                if (serviceType !== "") {
                    if (servieTypeStr.toUpperCase() !== serviceType.toUpperCase()) {
                        return false;
                    }
                }

                // 2. Client Search (search by client name, address, or cert no):
                var searchVal = normalizeSearchText(clientSearch);
                var isCurrentOrRecent = false;
                if (rowDate !== "") {
                    var parts = rowDate.split(".");
                    if (parts.length === 3) {
                        var day = parseInt(parts[0], 10);
                        var month = parseInt(parts[1], 10) - 1;
                        var year = parseInt(parts[2], 10);
                        var issueDate = new Date(year, month, day);
                        var now = new Date();
                        var firstDayOfPrevMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1, 0, 0, 0, 0);
                        if (issueDate >= firstDayOfPrevMonth) {
                            isCurrentOrRecent = true;
                        }
                    }
                }

                // 2. Client Search (search by client name, address, or cert no):
                // When search has text, show all matching records.
                // When search is empty, restrict view to current month + past 1 month for restricted roles.
                if (clientSearch !== "") {
                    var isMatch = (rowClient.indexOf(searchVal) !== -1 || certNo.indexOf(searchVal) !== -1);
                    if (!isMatch) {
                        return false;
                    }
                    // For restricted roles: past records (older than 1 month) are ONLY unlocked when full client name/cert no is typed
                    if (isRestrictedRole && !isCurrentOrRecent) {
                        var isExactMatch = (rowClient === searchVal || certNo === searchVal);
                        if (!isExactMatch) {
                            return false;
                        }
                    }
                } else {
                    // When search is empty:
                    if (isRestrictedRole) {
                        if (!isCurrentOrRecent) {
                            return false;
                        }
                    }
                }

                // 3. Custom Date Range Filters (From Date / To Date)
                if (fromDate !== "" || toDate !== "") {
                    if (rowDate === "") {
                        return false;
                    }
                    var isfromDate = (fromDate !== "") ? compareMinDates(fromDate, rowDate) : true;
                    var isToDate = (toDate !== "") ? compareMaxDates(toDate, rowDate) : true;
                    if (!(isfromDate && isToDate)) {
                        return false;
                    }
                }

                return true;
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
            table.draw();
        });

        var table = $('#tab_certificate_issue_list').DataTable({
            "sScrollX": "100%",
            "scrollY": '70vh',
            "scrollCollapse": true,
            "paging": true,
            "pageLength": 50,
            "ordering": true,
            "order": [[0, "desc"]],
            "lengthChange": false,
            "dom": "tr<'row dt-bottom-row'<'col-sm-6 col-xs-12'i><'col-sm-6 col-xs-12'p>>",
            "columnDefs": [
                { "width": "8%", "targets": 0 },
                { "width": "5%", "targets": 1 },
                { "width": "7%", "targets": 2 },
                { "width": "8%", "targets": 3 },
                { "width": "8%", "targets": 4 },
                { "width": "18%", "targets": 5 },
                { "width": "8%", "targets": 6 },
                { "width": "8%", "targets": 7 },
                { "width": "15%", "targets": 8 },
                { "width": "15%", "targets": 9 },
            ],
            "language": {
                "emptyTable": "No certificate records available in table",
                "zeroRecords": "No client found or incorrect spelling, check the spelling of client",
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

        $(document).ready(function () {
            var debouncedCertSearch = debounce(function () {
                table.draw();
            }, 200);

            $('#certClientSearch').on('input keyup change', debouncedCertSearch);

            $('#filterFromDate').datepicker({
                minDate: '-100Y',
                maxDate: '+100Y',
                changeYear: true,
                changeMonth: true,
                yearRange: "-20:+100",
                dateFormat: 'dd.mm.yy',
                onSelect: function () {
                    table.draw();
                }
            });

            $('#filterToDate').datepicker({
                minDate: '-100Y',
                maxDate: '+100Y',
                changeYear: true,
                changeMonth: true,
                yearRange: "-20:+100",
                dateFormat: 'dd.mm.yy',
                onSelect: function () {
                    table.draw();
                }
            });

            $('.customerAccount').click(function () {
                var customerID = $(this).attr('data-id');
                var url = 'customerAccount.php';
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
    </script>

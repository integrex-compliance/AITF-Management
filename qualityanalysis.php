<div id="wrapper" class="active">
    <?php
//    session_start();
    $pageIdentifier = "audit perf";
    include 'header.php';
    include 'dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    $sql = "SELECT ci.id, customer_name, address, contact, email_id, scope, aif_nace, svc.service_id, service_type, svc.audit_date FROM customer_info ci join service svc on ci.id = svc.customer_id WHERE is_deleted <> 'Y' ORDER BY svc.audit_date DESC";
    $statement = $db->prepare($sql);
    $statement->execute();
    $listOfCustomer = $statement->fetchAll();
    //    print_r($listOfCustomer);

    $services = "SELECT * FROM service_types";
    $statement = $db->prepare($services);
    $statement->execute();
    $servicesTypes = $statement->fetchAll();

    // Fetch auditor list for filter dropdown
    $auditorSql = "SELECT auditor_name FROM auditors ORDER BY auditor_name ASC";
    $statement = $db->prepare($auditorSql);
    $statement->execute();
    $auditorsList = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Batch preload subtype, survey, and CDC data to avoid per-row queries
    $serviceIdsByType = [
        'CA' => [], 'SA-I' => [], 'SA-II' => [], 'RC' => [], 'SP' => []
    ];
    foreach ($listOfCustomer as $c) {
        if (isset($serviceIdsByType[$c['service_type']])) {
            $serviceIdsByType[$c['service_type']][] = $c['service_id'];
        }
    }

    $loadMap = function($table, $serviceCol, $filterIds) use (&$db) {
        if (empty($filterIds)) return [];
        $ph = implode(',', array_fill(0, count($filterIds), '?'));
        $sql = "SELECT * FROM {$table} WHERE {$serviceCol} IN ($ph)";
        $stmt = $db->prepare($sql);
        $stmt->execute($filterIds);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $out[$r[$serviceCol]] = $r;
        }
        return $out;
    };

    $caMap = $loadMap('service_type_ca', 'service', $serviceIdsByType['CA']);
    $saiMap = $loadMap('service_type_sai', 'service', $serviceIdsByType['SA-I']);
    $saiiMap = $loadMap('service_type_saii', 'service', $serviceIdsByType['SA-II']);
    $rcMap = $loadMap('service_type_rc', 'service', $serviceIdsByType['RC']);
    $spMap = $loadMap('service_type_sp', 'service', $serviceIdsByType['SP']);

    // Collect all subtype ids for survey/cdc lookups
    $subtypeIds = [];
    foreach ([$caMap, $saiMap, $saiiMap, $rcMap, $spMap] as $m) {
        foreach ($m as $row) {
            // each map keyed by service, contains its own primary id
            $subtypeIds[] = reset(array_diff_key($row, ['service' => null])); // unused, will override below
        }
    }
    // Better: pull explicit ids
    $subtypeIds = array_merge(
        array_column($caMap, 'ca_id'),
        array_column($saiMap, 'sai_id'),
        array_column($saiiMap, 'saii_id'),
        array_column($rcMap, 'rc_id'),
        array_column($spMap, 'sp_id')
    );
    $subtypeIds = array_filter($subtypeIds, function($v){ return $v !== null && $v !== ''; });

    // customer_survey_id map: ca_serviceid => row (survey_id + auditing_quality, mgmt_satisfaction, aggregate)
    $surveyIdMap = [];
    $surveyDetailMap = []; // subtype_id => [auditing_quality, mgmt_satisfaction, aggregate]
    if (!empty($subtypeIds)) {
        $ph = implode(',', array_fill(0, count($subtypeIds), '?'));
        $sqlSurveyIds = "SELECT ca_serviceid, survey_id, auditing_quality, mgmt_satisfaction, aggregate, ncval, ofival, oplusval FROM customer_survey_id WHERE ca_serviceid IN ($ph)";
        $stmt = $db->prepare($sqlSurveyIds);
        $stmt->execute($subtypeIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $surveyIdMap[$r['ca_serviceid']] = $r['survey_id'];
            $surveyDetailMap[$r['ca_serviceid']] = $r;
        }
    }

    // customer_survey aggregated: survayid => ['auditors'=>string, 'marks'=>string]
    $surveyAgg = [];
    $surveyIds = array_values(array_filter($surveyIdMap));
    if (!empty($surveyIds)) {
        $ph = implode(',', array_fill(0, count($surveyIds), '?'));
        $sqlSurvey = "SELECT survayid, lead_auditor, marks FROM customer_survey WHERE survayid IN ($ph)";
        $stmt = $db->prepare($sqlSurvey);
        $stmt->execute($surveyIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $sid = $r['survayid'];
            if (!isset($surveyAgg[$sid])) {
                $surveyAgg[$sid] = ['auditors' => [], 'marks' => []];
            }
            $surveyAgg[$sid]['auditors'][] = $r['lead_auditor'];
            $surveyAgg[$sid]['marks'][] = $r['marks'];
        }
        // implode to strings
        foreach ($surveyAgg as $sid => $vals) {
            $surveyAgg[$sid]['auditors'] = implode(', ', array_filter($vals['auditors']));
            $surveyAgg[$sid]['marks'] = implode(', ', array_filter($vals['marks']));
        }
    }

    // cdc_report_review map: ca_service_id => row
    $cdcMap = [];
    if (!empty($subtypeIds)) {
        $ph = implode(',', array_fill(0, count($subtypeIds), '?'));
        $sqlCdc = "SELECT * FROM cdc_report_review WHERE ca_service_id IN ($ph)";
        $stmt = $db->prepare($sqlCdc);
        $stmt->execute($subtypeIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $cdcMap[$r['ca_service_id']] = $r;
        }
    }
    ?>

    <!-- Sidebar -->
    <!-- Sidebar -->

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

        body {
            font-family: 'Inter', sans-serif !important;
        }

        #page-content-wrapper {
            background-color: #f0f4f8; /* Softer, cooler background */
            min-height: 100vh;
            padding-bottom: 30px;
        }

        .custom-float-label-control {
            margin-bottom: 0em;
        }

        .filter-section {
            background: #ffffff;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            border-top: 4px solid #6366f1; /* Vibrant accent line */
        }

        .filter-section .form-control {
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            font-size: 14px;
            box-shadow: none;
            transition: all 0.3s ease;
            height: auto;
            width: 100%;
            background-color: #f8fafc;
        }

        .filter-section .form-control:focus {
            border-color: #6366f1;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .btn {
            border-radius: 6px;
            padding: 9px 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        }
        
        .btn-info:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 6px 12px rgba(16, 185, 129, 0.4);
            transform: translateY(-1px);
        }

        .filterBtn {
            padding: 9px 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .downloadQualityReport {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            white-space: nowrap;
        }

        /* Table styling */
        .main_content {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border-top: 4px solid #10b981; /* Another accent line */
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table > thead > tr > th {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff;
            font-weight: 600;
            border-bottom: none;
            padding: 16px 14px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        /* Make first and last header corners rounded slightly */
        .table > thead > tr > th:first-child {
            border-top-left-radius: 6px;
        }
        .table > thead > tr > th:last-child {
            border-top-right-radius: 6px;
        }

        .table > tbody > tr > td {
            padding: 16px 14px;
            vertical-align: middle;
            border-top: 1px solid #f1f5f9 !important;
            border-right: none !important;
            font-size: 14px;
            color: #334155;
            transition: background-color 0.2s ease;
        }

        .table-hover > tbody > tr:hover {
            background-color: #eef2ff !important;
        }

        .table > tbody > tr > td a {
            color: inherit;
            text-decoration: none;
        }

        .table > tbody > tr > td small {
            font-size: 14px;
        }
        
        .dataTables_wrapper .dataTables_scrollBody {
            border-bottom: none !important;
        }
    </style>
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" >
                <div class="row filter-section" style="margin-top:15px;">
                    <div class="col-sm-1" style="display: flex; justify-content: center;">
                        <a class="btn btn-info filterBtn"><i class="fa fa-filter"></i></a>
                    </div>                            
                    <div class="col-sm-2 float-label-control custom-float-label-control">
                        <select name="service-type" class="form-control service-type" id="service-type">
                            <option value="">Select Service Type</option>
                            <?php foreach ($servicesTypes as $service) { ?>
                                <option value="<?php echo $service['service_type'] ?>">
                                    <?php echo $service['service_type'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-2 float-label-control custom-float-label-control">
                        <select name="auditor-filter" class="form-control auditor-filter" id="auditor-filter">
                            <option value="">Select Auditor</option>
                            <?php foreach ($auditorsList as $auditor) { ?>
                                <option value="<?php echo htmlspecialchars($auditor['auditor_name']) ?>">
                                    <?php echo htmlspecialchars($auditor['auditor_name']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-2 float-label-control custom-float-label-control">
                        <input type="text" class="form-control filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="Audit Date From" value="">
                    </div>
                    <div class="col-sm-2 float-label-control custom-float-label-control">
                        <input type="text" class="form-control filterToDate" id="filterToDate" name="filterToDate" placeholder="Audit Date To" value="">
                    </div>
                    <div class="col-sm-3 float-label-control custom-float-label-control">
                        <button type="button" class="btn btn-success downloadQualityReport" style="width:100%;">
                            <i class="fa fa-cloud-download" style="font-size: 15px;"></i> Download Report
                        </button>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-12 main_content">
                        <table class="table table-hover table-sortable" id="tab_customer_list" style="width:100%">
                            <thead>
                                <tr>                                    
                                    <th>Client</th>
                                    <th>Service Type</th>
                                    <th>Audit Date</th>
                                    <th>Rating (A) (Out of 50)</th>
                                    <th>AITF Cert (B) (Out of 35)</th>
                                    <th>Auditor</th>
                                    <th>Individual marks</th>
                                    <th>Rating (A & B)</th>
                                    <th>NC</th>
                                    <th>OFI</th>
                                    <th>O+</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($listOfCustomer as $customer) {
                                    $svcId = $customer['service_id'];
                                    $svcType = $customer['service_type'];

                                    // pick subtype row based on service_type
                                    if ($svcType == 'CA') {
                                        $sub = isset($caMap[$svcId]) ? $caMap[$svcId] : null;
                                    } elseif ($svcType == 'SA-I') {
                                        $sub = isset($saiMap[$svcId]) ? $saiMap[$svcId] : null;
                                    } elseif ($svcType == 'SA-II') {
                                        $sub = isset($saiiMap[$svcId]) ? $saiiMap[$svcId] : null;
                                    } elseif ($svcType == 'RC') {
                                        $sub = isset($rcMap[$svcId]) ? $rcMap[$svcId] : null;
                                    } elseif ($svcType == 'SP') {
                                        $sub = isset($spMap[$svcId]) ? $spMap[$svcId] : null;
                                    } else {
                                        $sub = null;
                                    }

                                    if (!$sub) { continue; }

                                    // subtype ids
                                    $subId = null;
                                    if ($svcType == 'CA') $subId = $sub['ca_id'];
                                    else if ($svcType == 'SA-I') $subId = $sub['sai_id'];
                                    else if ($svcType == 'SA-II') $subId = $sub['saii_id'];
                                    else if ($svcType == 'RC') $subId = $sub['rc_id'];
                                    else if ($svcType == 'SP') $subId = $sub['sp_id'];

                                    if (!$subId) { continue; }

                                    $surveyId = isset($surveyIdMap[$subId]) ? $surveyIdMap[$subId] : null;
                                    $agg = isset($surveyAgg[$surveyId]) ? $surveyAgg[$surveyId] : ['auditors' => '', 'marks' => ''];
                                    $surveyDetail = isset($surveyDetailMap[$subId]) ? $surveyDetailMap[$subId] : null;
                                    $cdc = isset($cdcMap[$subId]) ? $cdcMap[$subId] : null;
                                    if (!$surveyDetail) { continue; }

                                    ?>
                                    <tr class="customerAccount" data-id="<?php echo $customer['id'] ?>"  >
                                        <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $customer['customer_name'] ?></a></small>
                                        </td>
                                        <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $svcType ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <span><?php echo simpleDateConversion($customer["audit_date"]); ?>~</span><small><a ><?php echo simpleDateConversion($customer["audit_date"]) ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $surveyDetail ? $surveyDetail['auditing_quality'] : '' ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $surveyDetail ? $surveyDetail['mgmt_satisfaction'] : '' ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php
                                                // Auditor: use survey auditors, fallback to CDC lead_auditor
                                                $auditorDisplay = $agg['auditors'];
                                                if (empty($auditorDisplay) && $cdc && !empty($cdc['lead_auditor'])) {
                                                    $auditorDisplay = $cdc['lead_auditor'];
                                                }
                                                echo $auditorDisplay;
                                            ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $agg['marks']; ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php echo $surveyDetail ? $surveyDetail['aggregate'] : '' ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php
                                                $ncDisplay = $cdc ? $cdc['ncval'] : '';
                                                if (empty($ncDisplay) && $surveyDetail && !empty($surveyDetail['ncval'])) $ncDisplay = $surveyDetail['ncval'];
                                                echo $ncDisplay;
                                            ?></a></small>
                                        </td>
                                        <td  data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php
                                                $ofiDisplay = $cdc ? $cdc['ofival'] : '';
                                                if (empty($ofiDisplay) && $surveyDetail && !empty($surveyDetail['ofival'])) $ofiDisplay = $surveyDetail['ofival'];
                                                echo $ofiDisplay;
                                            ?></a></small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a ><?php
                                                $oplusDisplay = $cdc ? $cdc['oplusval'] : '';
                                                if (empty($oplusDisplay) && $surveyDetail && !empty($surveyDetail['oplusval'])) $oplusDisplay = $surveyDetail['oplusval'];
                                                echo $oplusDisplay;
                                            ?></a></small>
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
                    var auditorFilter = $(".auditor-filter").val();
                    var fromDate = $(".filterFromDate").val();
                    var toDate = $(".filterToDate").val();
                    var isServiceType = false;
                    var isAuditor = false;
                    var isfromDate = false;
                    var isToDate = false;
                    if (serviceType == "" && auditorFilter == "" && fromDate == "" && toDate == "") {
                        return true;
                    }
                    if (serviceType != "") {
                        var servieTypeStr = data[1] || "";
                        if (servieTypeStr.toUpperCase().trim() == serviceType.toUpperCase().trim()) {
                            isServiceType = true;
                        }
                    } else {
                        isServiceType = true;
                    }
                    // Auditor filter: column 5 (0-indexed)
                    if (auditorFilter != "") {
                        var auditorStr = data[5] || "";
                        if (auditorStr.toUpperCase().trim().indexOf(auditorFilter.toUpperCase().trim()) !== -1) {
                            isAuditor = true;
                        }
                    } else {
                        isAuditor = true;
                    }
                    var dateValue = data[2] || "";
                    var dateSplit = dateValue.split("~");
                    if (fromDate != "") {
                        isfromDate = compareMinDates(fromDate, dateSplit[1]);
                    } else {
                        isfromDate = true;
                    }
                    if (toDate != "") {
                        isToDate = compareMaxDates(toDate, dateSplit[1]);
                    } else {
                        isToDate = true;
                    }
                    finalResult = (isServiceType && isAuditor && isfromDate && isToDate);
                    return finalResult;
                }
        );

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

            var table = $('#tab_customer_list').DataTable({
                "scrollY": '70vh',
                "scrollX": true,
                "scrollCollapse": true,
                "paging": true,
                "pageLength": 50,
                "ordering": false,
                "lengthChange": false,
                "deferRender": true,
                "columnDefs": [
                    {
                        orderable: false,
                        targets: "no-sort"
                    }
                ]
            });
            table.columns.adjust().draw();

            $('.service-type, .auditor-filter, .filterFromDate, .filterToDate').change(function () {
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

            // Download Report - export visible data to Excel
            $('.downloadQualityReport').click(function () {
                var serviceType = $('.service-type').val();
                var auditorFilter = $('.auditor-filter').val();
                var fromDate = $('.filterFromDate').val();
                var toDate = $('.filterToDate').val();
                var url = 'exportQualityAnalysisExcel.php';
                var $form = $('<form>', {
                    method: 'post',
                    action: url
                }).css('display', 'none');
                $('<input>', { type: 'hidden', name: 'serviceType', value: serviceType || '' }).appendTo($form);
                $('<input>', { type: 'hidden', name: 'auditorFilter', value: auditorFilter || '' }).appendTo($form);
                $('<input>', { type: 'hidden', name: 'fromDate', value: fromDate || '' }).appendTo($form);
                $('<input>', { type: 'hidden', name: 'toDate', value: toDate || '' }).appendTo($form);
                $('body').append($form);
                $form[0].submit();
                setTimeout(function () { $form.remove(); }, 1000);
            });
        });
    </script>

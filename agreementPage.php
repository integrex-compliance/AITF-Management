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

    $invRole = trim($_SESSION['role'] ?? '');
    $isMarketingHead = in_array($invRole, array('Marketing Head', 'Marketing Executive'), true)
        || strtolower(str_replace(' ', '', $invRole)) === 'marketinghead'
        || (isset($_SESSION['username']) && strtolower(trim($_SESSION['username'])) === 'marketingh');
    $isOfficeStaff = in_array($invRole, array('Office Staff-A', 'Office Staff-B'), true)
        || in_array(strtolower(str_replace(array(' ', '-'), '', $invRole)), array('officestaffa', 'officestaffb'), true);
    $isRestrictedRole = $isMarketingHead || $isOfficeStaff;

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
            color: #0d4d8b;
            border-color: #e2e8f0;
            font-size: 12.5px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            margin: 0 2px;
        }
        .dataTables_wrapper .dataTables_paginate .pagination > .active > a,
        .dataTables_wrapper .dataTables_paginate .pagination > .active > span {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e4b8a 100%) !important;
            border-color: #0d4d8b !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(13, 77, 139, 0.25);
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

<!-- Report chooser: choose PDF/Excel, then download or email -->
<div id="agreement-report-format-chooser" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.45); z-index:99999;">
    <div style="max-width:440px; margin:10% auto; background:#fff; border-radius:10px; box-shadow:0 10px 40px rgba(0,0,0,0.3); overflow:hidden;">
        <div style="background:#0d4d8b; color:#fff; padding:14px 18px; font-size:15px; font-weight:600;">
            <i class="fa fa-cloud-download" style="margin-right:8px;"></i> Download Agreement Report
        </div>
        <div class="agreement-report-step agreement-report-step-format" style="padding:22px 18px; text-align:center;">
            <p style="font-size:13px; color:#333; margin:0 0 18px;">Which format would you like?</p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <button type="button" class="btn btn-danger agreement-report-format-pdf" style="min-width:130px;">
                    <i class="fa fa-file-pdf-o"></i> PDF
                </button>
                <button type="button" class="btn btn-success agreement-report-format-excel" style="min-width:130px;">
                    <i class="fa fa-file-excel-o"></i> Excel
                </button>
            </div>
            <div style="margin-top:18px;">
                <button type="button" class="btn btn-default btn-sm agreement-report-cancel">Cancel</button>
            </div>
        </div>
        <div class="agreement-report-step agreement-report-step-action" style="display:none; padding:22px 18px; text-align:center;">
            <p style="font-size:13px; color:#333; margin:0 0 4px;">Selected format: <strong class="agreement-report-format-label">Excel</strong></p>
            <p style="font-size:12px; color:#777; margin:0 0 18px;">How would you like to receive it?</p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <button type="button" class="btn btn-primary agreement-report-action-download" style="min-width:150px;">
                    <i class="fa fa-download"></i> Download Report
                </button>
                <button type="button" class="btn btn-warning agreement-report-action-email" style="min-width:150px;">
                    <i class="fa fa-envelope"></i> Get Report on Email
                </button>
            </div>
            <div class="agreement-report-email-box" style="display:none; margin-top:18px; text-align:left;">
                <label style="font-size:12px; font-weight:600; color:#333;">Enter email address</label>
                <input type="email" class="agreement-report-email-input form-control" placeholder="name@example.com" style="margin-top:6px;" autocomplete="off">
                <div class="agreement-report-email-msg" style="font-size:12px; margin-top:8px; min-height:16px;"></div>
                <button type="button" class="btn btn-success agreement-report-email-send" style="width:100%; margin-top:6px;">
                    <i class="fa fa-paper-plane"></i> Get Report
                </button>
            </div>
            <div style="margin-top:18px;">
                <button type="button" class="btn btn-default btn-sm agreement-report-step-back">Back</button>
                <button type="button" class="btn btn-default btn-sm agreement-report-cancel">Cancel</button>
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

    function normalizeSearchText(str) {
        if (!str) return "";
        return str.replace(/<[^>]*>/g, "")
                  .replace(/&amp;/g, '&')
                  .replace(/\s+/g, ' ')
                  .trim()
                  .toLowerCase();
    }

    var isMarketingHead = <?php echo $isMarketingHead ? 'true' : 'false'; ?>;
    var isOfficeStaff = <?php echo $isOfficeStaff ? 'true' : 'false'; ?>;
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
            if (tableId !== 'table_quotation') {
                return true;
            }

            var fromDate = $(".filterFromDate").val() || $("#filterFromDate").val() || "";
            var toDate = $(".filterToDate").val() || $("#filterToDate").val() || "";
            var clientSearch = ($("#agreementClientSearch").val() || "").trim();

            var rowClient = normalizeSearchText(data[0] || "");
            var rowAggNo = normalizeSearchText(data[2] || "");

            var dateValue = data[4] || "";
            var dateSplit = dateValue.split("~");
            var rowDate = (dateSplit.length > 1 ? dateSplit[1] : dateSplit[0]) || "";
            rowDate = rowDate.replace(/<[^>]*>/g, "").trim();

            var searchVal = normalizeSearchText(clientSearch);
            var isCurrentOrRecent = false;
            if (rowDate !== "") {
                var parts = rowDate.split(".");
                if (parts.length === 3) {
                    var day = parseInt(parts[0], 10);
                    var month = parseInt(parts[1], 10) - 1;
                    var year = parseInt(parts[2], 10);
                    var aggDate = new Date(year, month, day);
                    var now = new Date();
                    var firstDayOfPrevMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1, 0, 0, 0, 0);
                    if (aggDate >= firstDayOfPrevMonth) {
                        isCurrentOrRecent = true;
                    }
                }
            }

            // 1. Client Search (search by client name or agreement no):
            // When client search has text, show all matching records.
            // When client search is empty, restrict view to current month + past 1 month (for marketing & office staff roles).
            if (clientSearch !== "") {
                var isMatch = (rowClient.indexOf(searchVal) !== -1 || rowAggNo.indexOf(searchVal) !== -1);
                if (!isMatch) {
                    return false;
                }
                // For restricted roles: past records (older than 1 month) are ONLY unlocked when full client name/agg no is typed
                if (isRestrictedRole && !isCurrentOrRecent) {
                    var isExactMatch = (rowClient === searchVal || rowAggNo === searchVal);
                    if (!isExactMatch) {
                        return false;
                    }
                }
            } else {
                // When client search is empty:
                if (isRestrictedRole) {
                    if (!isCurrentOrRecent) {
                        return false;
                    }
                }
            }

            // 2. Explicit From Date and To Date range filters
            if (fromDate !== "" || toDate !== "") {
                if (rowDate === "") {
                    return false;
                }
                var isfromDate = (fromDate !== "") ? compareMinDates(fromDate, rowDate) : true;
                var isToDate = (toDate !== "") ? compareMaxDates(toDate, rowDate) : true;
                return (isfromDate && isToDate);
            }

            return true;
        }
    );

    var table = $('#table_quotation').DataTable({
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": true,
        "ordering": false,
        "lengthChange": false,
        "pageLength": 50,
        "info": true,
        "dom": "tr<'row dt-bottom-row'<'col-sm-6 col-xs-12'i><'col-sm-6 col-xs-12'p>>",
        "columnDefs": [
            {
                "width": "20%",
                "targets": 0
            },
            {
                "width": "12%",
                "targets": 1
            },
            {
                "width": "18%",
                "targets": 2
            },
            {
                "width": "12%",
                "targets": 3
            },
            {
                "width": "14%",
                "targets": 4
            },
            {
                "width": "12%",
                "targets": 5
            },
            {
                "width": "12%",
                "targets": 6
            }
        ],
        "language": {
            "emptyTable": "No data available in table",
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

        var debouncedAgreementSearch = debounce(function () {
            table.draw();
        }, 200);

        $('#agreementClientSearch').on('input keyup change', debouncedAgreementSearch);

        var pendingAgreementReport = null;
        var pendingAgreementFormat = null;

        function showAgreementReportStep(step) {
            $('.agreement-report-step').hide();
            if (step === 'action') {
                $('.agreement-report-step-action').show();
            } else {
                $('.agreement-report-step-format').show();
            }
        }

        function resetAgreementChooser() {
            pendingAgreementFormat = null;
            $('.agreement-report-email-box').hide();
            $('.agreement-report-email-input').val('');
            $('.agreement-report-email-msg').text('');
            $('.agreement-report-email-send').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
            showAgreementReportStep('format');
        }

        function openAgreementChooser(params) {
            pendingAgreementReport = params;
            resetAgreementChooser();
            $('#agreement-report-format-chooser').css('display', 'block');
        }

        function closeAgreementChooser() {
            $('#agreement-report-format-chooser').hide();
            pendingAgreementReport = null;
            pendingAgreementFormat = null;
        }

        function submitAgreementReportDownload() {
            if (!pendingAgreementReport || !pendingAgreementFormat) {
                return;
            }
            var url = (pendingAgreementFormat === 'excel') ? 'exportAgreementReportExcel.php' : 'exportAgreementReport.php';
            var fallbackName = (pendingAgreementFormat === 'excel') ? 'Agreement_Report.xlsx' : 'Agreement_Report.pdf';
            var $btn = $('.agreement-report-action-download');
            if (!$btn.data('orig')) { $btn.data('orig', $btn.html()); }
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Preparing...');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.responseType = 'blob';
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                $btn.prop('disabled', false).html($btn.data('orig'));
                if (xhr.status === 200) {
                    var cd = xhr.getResponseHeader('Content-Disposition') || '';
                    var m = /filename="?([^"]+)"?/.exec(cd);
                    var fname = m ? m[1] : fallbackName;
                    var objUrl = URL.createObjectURL(xhr.response);
                    var link = document.createElement('a');
                    link.href = objUrl;
                    link.download = fname;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    setTimeout(function () { URL.revokeObjectURL(objUrl); }, 4000);
                    closeAgreementChooser();
                } else {
                    alert('Download failed. Please try again.');
                }
            };
            xhr.onerror = function () {
                $btn.prop('disabled', false).html($btn.data('orig'));
                alert('Download failed. Please try again.');
            };
            xhr.send('fromDate=' + encodeURIComponent(pendingAgreementReport.fromDate) +
                '&toDate=' + encodeURIComponent(pendingAgreementReport.toDate) +
                '&clientName=' + encodeURIComponent(pendingAgreementReport.clientName));
        }

        $('.downloadAgreementReport').click(function () {
            openAgreementChooser({
                fromDate: $('#filterFromDate').val(),
                toDate: $('#filterToDate').val(),
                clientName: $('#agreementClientSearch').val()
            });
        });

        $('.agreement-report-format-pdf').click(function () {
            pendingAgreementFormat = 'pdf';
            $('.agreement-report-format-label').text('PDF');
            $('.agreement-report-email-box').hide();
            showAgreementReportStep('action');
        });

        $('.agreement-report-format-excel').click(function () {
            pendingAgreementFormat = 'excel';
            $('.agreement-report-format-label').text('Excel');
            $('.agreement-report-email-box').hide();
            showAgreementReportStep('action');
        });

        $('.agreement-report-action-download').click(function () {
            submitAgreementReportDownload();
        });

        $('.agreement-report-action-email').click(function () {
            $('.agreement-report-email-msg').text('');
            $('.agreement-report-email-box').show();
            $('.agreement-report-email-input').focus();
        });

        $('.agreement-report-email-send').click(function () {
            if (!pendingAgreementReport || !pendingAgreementFormat) {
                return;
            }
            var email = $.trim($('.agreement-report-email-input').val());
            var $msg = $('.agreement-report-email-msg');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $msg.css('color', '#cc0000').text('Please enter a valid email address.');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            $msg.css('color', '#0d4d8b').text('Sending report, please wait...');

            $.post('emailAgreementReport.php', {
                fromDate: pendingAgreementReport.fromDate,
                toDate: pendingAgreementReport.toDate,
                clientName: pendingAgreementReport.clientName,
                format: pendingAgreementFormat,
                email: email
            }, null, 'json').done(function (res) {
                if (res && res.success) {
                    $msg.css('color', '#1a7f37').text(res.message || 'Report sent successfully.');
                    setTimeout(closeAgreementChooser, 2000);
                } else {
                    $msg.css('color', '#cc0000').text((res && res.message) ? res.message : 'Could not send the email.');
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
                }
            }).fail(function () {
                $msg.css('color', '#cc0000').text('Server error. Please try again.');
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
            });
        });

        $('.agreement-report-step-back').click(function () {
            resetAgreementChooser();
        });

        $('.agreement-report-cancel').click(function () {
            closeAgreementChooser();
        });

        $('#agreement-report-format-chooser').click(function (e) {
            if (e.target === this) {
                closeAgreementChooser();
            }
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

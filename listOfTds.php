<div id="wrapper" class="active">

    <?php

    $pageIdentifier = "TDS list";
    include_once './header.php';
    include_once './dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    $status = $_GET['status'] ?? 'all';
    $startDateStr = $_GET['startDate'] ?? '';
    $endDateStr = $_GET['endDate'] ?? '';
    $clientSearch = $_GET['clientSearch'] ?? '';
    $sortOrder = $_GET['sortOrder'] ?? '';

    $startDate = '';
    $endDate = '';

    if (!empty($startDateStr)) {
        // dd.mm.yy to Y-m-d
        $parts = explode('.', $startDateStr);
        if(count($parts) == 3) {
            $startDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
    }
    if (!empty($endDateStr)) {
        // dd.mm.yy to Y-m-d
        $parts = explode('.', $endDateStr);
        if(count($parts) == 3) {
            $endDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
    }

    $allTds = "SELECT ci.id, customer_name,ci.unique_id,ps.customer_id,ps.service_type as tds, ps.amount_decided,ps.payment_received,ps.payment_date,ps.comment,ps.payment_id, svc.service_id, svc.service_type
               FROM customer_info ci
               left join payment_system ps ON ci.id=ps.customer_id
               left join service svc on ps.customer_id = svc.customer_id
               WHERE svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id)
               and ps.service_type LIKE '%tds%'";

    $params = [];
    if ($status == 'received') {
        $allTds .= " AND ps.payment_received > 0";
    } elseif ($status == 'not_received') {
        $allTds .= " AND (ps.payment_received IS NULL OR ps.payment_received = 0 OR ps.payment_received = '' OR ps.payment_received = '0.00' OR ps.payment_received = '0')";
    }

    if (!empty($clientSearch)) {
        $allTds .= " AND ci.customer_name LIKE :clientSearch";
        $params[':clientSearch'] = '%' . $clientSearch . '%';
    }

    if (!empty($startDate)) {
        $allTds .= " AND ps.payment_date >= :startDate";
        $params[':startDate'] = $startDate;
    }
    if (!empty($endDate)) {
        $allTds .= " AND ps.payment_date <= :endDate";
        $params[':endDate'] = $endDate;
    }

    if ($sortOrder == 'high') {
        $allTds .= " ORDER BY CAST(ps.amount_decided AS DECIMAL(10,2)) DESC";
    } elseif ($sortOrder == 'low') {
        $allTds .= " ORDER BY CAST(ps.amount_decided AS DECIMAL(10,2)) ASC";
    } else {
        $allTds .= " ORDER BY ci.id DESC";
    }

    $statement = $db->prepare($allTds);
    foreach ($params as $key => $val) {
        $statement->bindValue($key, $val);
    }
    $statement->execute();
    $listOfTds = $statement->fetchAll();

    // Also fetch TDS from new Statement system (statement_payments where tds_decided > 0)
    $newTdsQuery = "SELECT ci.id, ci.customer_name, ci.unique_id, sp.customer_id, 
                    CONCAT('TDS (Statement #', sp.id, ')') as tds, 
                    sp.tds_decided as amount_decided, sp.tds_received as payment_received,
                    sp.payment_date, sp.comment, sp.id as payment_id,
                    0 as service_id, sp.service_type
                    FROM customer_info ci
                    INNER JOIN statement_payments sp ON ci.id = sp.customer_id
                    WHERE sp.tds_decided > 0";
    $newTdsParams = [];
    if ($status == 'received') {
        $newTdsQuery .= " AND sp.tds_received > 0";
    } elseif ($status == 'not_received') {
        $newTdsQuery .= " AND (sp.tds_received IS NULL OR sp.tds_received = 0)";
    }
    if (!empty($clientSearch)) {
        $newTdsQuery .= " AND ci.customer_name LIKE :clientSearch2";
        $newTdsParams[':clientSearch2'] = '%' . $clientSearch . '%';
    }
    if (!empty($startDate)) {
        $newTdsQuery .= " AND sp.payment_date >= :startDate2";
        $newTdsParams[':startDate2'] = $startDate;
    }
    if (!empty($endDate)) {
        $newTdsQuery .= " AND sp.payment_date <= :endDate2";
        $newTdsParams[':endDate2'] = $endDate;
    }
    $newTdsQuery .= " ORDER BY sp.id DESC";

    try {
        $stmtNew = $db->prepare($newTdsQuery);
        foreach ($newTdsParams as $key => $val) {
            $stmtNew->bindValue($key, $val);
        }
        $stmtNew->execute();
        $newTdsList = $stmtNew->fetchAll();
        $listOfTds = array_merge($listOfTds, $newTdsList);
    } catch (PDOException $e) {
        // If statement_payments table doesn't exist yet, silently skip
    }
    ?>

    <style>
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
            align-items: flex-end;
            margin: 10px 0 20px;
            padding: 12px 15px;
            background: #f8fbff;
            border: 1px solid #d6e4f0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .invoice-filter-item {
            flex: 1;
            min-width: 150px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .invoice-filter-item label {
            display: block;
            margin-bottom: 6px;
            color: #0d4d8b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            height: 14px;
            line-height: 14px;
        }

        .invoice-filter-item select, .invoice-filter-item input {
            width: 100%;
            height: 38px;
            padding: 8px 12px;
            border: 1px solid #bfd2e6;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 13px;
            color: #333;
            background-color: #fff;
            transition: all 0.2s;
        }

        .invoice-filter-item select:focus, .invoice-filter-item input:focus {
            border-color: #0d4d8b;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.1);
        }

        .btn-filter-group {
            display: flex;
            gap: 8px;
            height: 38px;
            align-items: center;
        }

        .btn-premium {
            height: 38px;
            padding: 0 20px;
            font-weight: 600;
            border-radius: 6px;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
        }

        .btn-print { background: #5bc0de; color: #fff; }
        .btn-print:hover { background: #31b0d5; transform: translateY(-1px); }

        .btn-export { background: #5cb85c; color: #fff; }
        .btn-export:hover { background: #449d44; transform: translateY(-1px); }
    </style>

    <!-- Page content -->
    <div id="page-content-wrapper">
        <!-- Keep all page content within the page-content inset div! -->
        <div class="page-content inset">
            <div class="row">
                <div class="col-md-12">
                    <div class="container-fluid" style="margin-top: 20px;">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 class="invoice-section-title">TDS List & Reports</h2>
                                <form method="GET" action="listOfTds.php" id="filterForm">
                                    <div class="invoice-filter-panel">
                                        <div class="invoice-filter-item">
                                            <label for="status">Payment Status</label>
                                            <select name="status" id="status" onchange="$('#filterForm').submit()">
                                                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Status</option>
                                                <option value="received" <?php echo $status == 'received' ? 'selected' : ''; ?>>Received</option>
                                                <option value="not_received" <?php echo $status == 'not_received' ? 'selected' : ''; ?>>Not Received</option>
                                            </select>
                                        </div>
                                        <div class="invoice-filter-item">
                                            <label for="clientSearch">Client Name</label>
                                            <input type="text" name="clientSearch" id="clientSearch" value="<?php echo $clientSearch; ?>" placeholder="Search Client..." onkeyup="if(event.keyCode == 13) $('#filterForm').submit()">
                                        </div>
                                        <div class="invoice-filter-item">
                                            <label for="startDate">From Date</label>
                                            <input type="text" name="startDate" id="startDate" class="datepicker-tds" value="<?php echo $startDateStr; ?>" autocomplete="off" placeholder="DD.MM.YYYY">
                                        </div>
                                        <div class="invoice-filter-item">
                                            <label for="endDate">To Date</label>
                                            <input type="text" name="endDate" id="endDate" class="datepicker-tds" value="<?php echo $endDateStr; ?>" autocomplete="off" placeholder="DD.MM.YYYY">
                                        </div>
                                        <div class="invoice-filter-item">
                                            <label for="sortOrder">Sort By Amount</label>
                                            <select name="sortOrder" id="sortOrder" onchange="$('#filterForm').submit()">
                                                <option value="" <?php echo $sortOrder == '' ? 'selected' : ''; ?>>Default</option>
                                                <option value="high" <?php echo $sortOrder == 'high' ? 'selected' : ''; ?>>High to Low</option>
                                                <option value="low" <?php echo $sortOrder == 'low' ? 'selected' : ''; ?>>Low to High</option>
                                            </select>
                                        </div>
                                        <div class="invoice-filter-item">
                                            <label>Action</label>
                                            <button type="button" class="btn-premium btn-solid-primary downloadTdsReport" style="width: 100%; height: 38px; background: #0d4d8b; color: #fff; border: none; border-radius: 6px; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">
                                                <i class="fa fa-cloud-download" style="font-size: 15px;"></i> Download Report
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 contact-form">
                                <div class="row clearfix">
                                    <div class="col-md-12 table-responsive">
                                        <table class="table table-bordered table-striped table-hover table-sortable" id="table_finance_details">
                                            <thead>
                                                <tr>
                                                    <th class=""  >
                                                        <small><a>Unique ID</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Client Name</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Service Type</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>TDS amount deducted</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Payment received</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Date</a></small>
                                                    </th>
                                                    <th class="" width="10" >
                                                        <small><a>TDS certificate details</a></small>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($listOfTds as $tds) { ?>
                                                    <tr id='addr0' data-id="<?php echo $tds['customer_id'] ?>">
                                                        <td data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><a data-id="<?php echo $tds['customer_id'] ?>" class="customerAccount"><?php echo $tds['unique_id'] ?></a></small>
                                                        </td>

                                                        <td  class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $tds['customer_name'] ?></small>
                                                        </td>
                                                        <td   class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $tds['tds'] ?></small>
                                                        </td>
                                                        <td  class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $tds['amount_decided'] ?></small>
                                                        </td>
                                                        <td  class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><?php echo $tds['payment_received'] ?></small>
                                                        </td>
                                                        <td   class="customerAccount" data-id="<?php echo $tds['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <small><?php if (isset($tds['payment_date']) && !empty($tds['payment_date'])) {
                                                    echo date("d.m.Y", strtotime($tds['payment_date']));
                                                } ?></small>
                                                        </td>
                                                        <td data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <div class="table-edit-control">
                                                                <textarea rows="3" class="saveChanges" data-row="<?php echo $tds['payment_id'] ?>" data-column="comment" name="payComments" value="" placeholder="" ><?php if (isset($tds['comment'])) echo $tds['comment'] ?></textarea>
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
            </div>
        </div>
    </div>

</div>

<div id="tds-report-format-chooser" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.45); z-index:99999;">
    <div style="max-width:440px; margin:10% auto; background:#fff; border-radius:10px; box-shadow:0 10px 40px rgba(0,0,0,0.3); overflow:hidden;">
        <div style="background:#0d4d8b; color:#fff; padding:14px 18px; font-size:15px; font-weight:600;">
            <i class="fa fa-cloud-download" style="margin-right:8px;"></i> Download TDS Report
        </div>
        <div class="tds-report-step tds-report-step-format" style="padding:22px 18px; text-align:center;">
            <p style="font-size:13px; color:#333; margin:0 0 18px;">Which format would you like?</p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <button type="button" class="btn btn-danger tds-report-format-pdf" style="min-width:130px;">
                    <i class="fa fa-file-pdf-o"></i> PDF
                </button>
                <button type="button" class="btn btn-success tds-report-format-excel" style="min-width:130px;">
                    <i class="fa fa-file-excel-o"></i> Excel
                </button>
            </div>
            <div style="margin-top:18px;">
                <button type="button" class="btn btn-default btn-sm tds-report-cancel">Cancel</button>
            </div>
        </div>
        <div class="tds-report-step tds-report-step-action" style="display:none; padding:22px 18px; text-align:center;">
            <p style="font-size:13px; color:#333; margin:0 0 4px;">Selected format: <strong class="tds-report-format-label">Excel</strong></p>
            <p style="font-size:12px; color:#777; margin:0 0 18px;">How would you like to receive it?</p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <button type="button" class="btn btn-primary tds-report-action-download" style="min-width:150px;">
                    <i class="fa fa-download"></i> Download Report
                </button>
                <button type="button" class="btn btn-warning tds-report-action-email" style="min-width:150px;">
                    <i class="fa fa-envelope"></i> Get Report on Email
                </button>
            </div>
            <div class="tds-report-email-box" style="display:none; margin-top:18px; text-align:left;">
                <label style="font-size:12px; font-weight:600; color:#333;">Enter email address</label>
                <input type="email" class="tds-report-email-input form-control" placeholder="name@example.com" style="margin-top:6px;" autocomplete="off">
                <div class="tds-report-email-msg" style="font-size:12px; margin-top:8px; min-height:16px;"></div>
                <button type="button" class="btn btn-success tds-report-email-send" style="width:100%; margin-top:6px;">
                    <i class="fa fa-paper-plane"></i> Get Report
                </button>
            </div>
            <div style="margin-top:18px;">
                <button type="button" class="btn btn-default btn-sm tds-report-step-back">Back</button>
                <button type="button" class="btn btn-default btn-sm tds-report-cancel">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var pendingTdsReport = null;
        var pendingTdsFormat = null;

        function getTdsReportParams() {
            return {
                status: $('#status').val(),
                startDate: $('#startDate').val(),
                endDate: $('#endDate').val(),
                clientSearch: $('#clientSearch').val(),
                sortOrder: $('#sortOrder').val()
            };
        }

        function showTdsReportStep(step) {
            $('.tds-report-step').hide();
            if (step === 'action') {
                $('.tds-report-step-action').show();
            } else {
                $('.tds-report-step-format').show();
            }
        }

        function resetTdsChooser() {
            pendingTdsFormat = null;
            $('.tds-report-email-box').hide();
            $('.tds-report-email-input').val('');
            $('.tds-report-email-msg').text('');
            $('.tds-report-email-send').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
            showTdsReportStep('format');
        }

        function openTdsChooser(params) {
            pendingTdsReport = params;
            resetTdsChooser();
            $('#tds-report-format-chooser').css('display', 'block');
        }

        function closeTdsChooser() {
            $('#tds-report-format-chooser').hide();
            pendingTdsReport = null;
            pendingTdsFormat = null;
        }

        function submitTdsReportDownload() {
            if (!pendingTdsReport || !pendingTdsFormat) {
                return;
            }
            var url = (pendingTdsFormat === 'excel') ? 'exportTdsReportExcel.php' : 'exportTdsReport.php';
            var fallbackName = (pendingTdsFormat === 'excel') ? 'TDS_Report.xlsx' : 'TDS_Report.pdf';
            var $btn = $('.tds-report-action-download');
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
                    closeTdsChooser();
                } else {
                    alert('Download failed. Please try again.');
                }
            };
            xhr.onerror = function () {
                $btn.prop('disabled', false).html($btn.data('orig'));
                alert('Download failed. Please try again.');
            };
            xhr.send('status=' + encodeURIComponent(pendingTdsReport.status) +
                '&startDate=' + encodeURIComponent(pendingTdsReport.startDate) +
                '&endDate=' + encodeURIComponent(pendingTdsReport.endDate) +
                '&clientSearch=' + encodeURIComponent(pendingTdsReport.clientSearch) +
                '&sortOrder=' + encodeURIComponent(pendingTdsReport.sortOrder));
        }

        $('.downloadTdsReport').click(function () {
            openTdsChooser(getTdsReportParams());
        });

        $('.tds-report-format-pdf').click(function () {
            pendingTdsFormat = 'pdf';
            $('.tds-report-format-label').text('PDF');
            $('.tds-report-email-box').hide();
            showTdsReportStep('action');
        });

        $('.tds-report-format-excel').click(function () {
            pendingTdsFormat = 'excel';
            $('.tds-report-format-label').text('Excel');
            $('.tds-report-email-box').hide();
            showTdsReportStep('action');
        });

        $('.tds-report-action-download').click(function () {
            submitTdsReportDownload();
        });

        $('.tds-report-action-email').click(function () {
            $('.tds-report-email-msg').text('');
            $('.tds-report-email-box').show();
            $('.tds-report-email-input').focus();
        });

        $('.tds-report-email-send').click(function () {
            if (!pendingTdsReport || !pendingTdsFormat) {
                return;
            }
            var email = $.trim($('.tds-report-email-input').val());
            var $msg = $('.tds-report-email-msg');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $msg.css('color', '#cc0000').text('Please enter a valid email address.');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            $msg.css('color', '#0d4d8b').text('Sending report, please wait...');

            $.post('emailTdsReport.php', {
                status: pendingTdsReport.status,
                startDate: pendingTdsReport.startDate,
                endDate: pendingTdsReport.endDate,
                clientSearch: pendingTdsReport.clientSearch,
                sortOrder: pendingTdsReport.sortOrder,
                format: pendingTdsFormat,
                email: email
            }, null, 'json').done(function (res) {
                if (res && res.success) {
                    $msg.css('color', '#1a7f37').text(res.message || 'Report sent successfully.');
                    setTimeout(closeTdsChooser, 2000);
                } else {
                    $msg.css('color', '#cc0000').text((res && res.message) ? res.message : 'Could not send the email.');
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
                }
            }).fail(function () {
                $msg.css('color', '#cc0000').text('Server error. Please try again.');
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
            });
        });

        $('.tds-report-step-back').click(function () {
            resetTdsChooser();
        });

        $('.tds-report-cancel').click(function () {
            closeTdsChooser();
        });

        $('#tds-report-format-chooser').click(function (e) {
            if (e.target === this) {
                closeTdsChooser();
            }
        });

        // Use a slight delay to ensure all scripts are fully loaded
        setTimeout(function() {
            if (typeof $.fn.datepicker !== 'undefined') {
                $('.datepicker-tds').datepicker({
                    minDate: '-100Y',
                    maxDate: '+100Y',
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-20:+100",
                    dateFormat: 'dd.mm.yy',
                    onSelect: function() {
                        $('#filterForm').submit();
                    }
                });
            }
        }, 100);

        var table = $('#table_finance_details').DataTable({
            "scrollY": '70vh',
            "scrollCollapse": true,
            "paging": false,
            "ordering": false,
            "lengthChange": false,
            "columnDefs": [
                { "width": "10%", "targets": 0 },
                { "width": "15%", "targets": 1 },
                { "width": "15%", "targets": 2 },
                { "width": "12%", "targets": 3 },
                { "width": "10%", "targets": 4 },
            ]
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
            $.ajax({
                url: "deleteFinalInvoice.php",
                type: "GET",
                data: {
                    id: $selector.attr('data-row'),
                    columnName: $selector.attr('data-column'),
                    columnValue: checkedValue,
                },
                success: function (data) {
                    // Success logic
                }
            });
            $selector.removeClass("glowing-border");
            $selector.addClass("lookslike-label");
        }

        $('.customerAccount').click(function () {
            var customerID = $(this).attr('data-id');
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                    '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.newCustomer').click(function () {
            $(".addNewCustomer").show();
            $('.newCustomer').hide();
        });

        $('.cancelAdding').click(function () {
            $('.newCustomer').show();
            $(".addNewCustomer").hide();
        });

        table.columns.adjust().draw();
    });
</script>

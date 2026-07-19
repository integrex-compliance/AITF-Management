<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->

    <?php
    $pageIdentifier = "app review objrctive";
    include 'header.php';
    include 'dbConnect.php';

    $appobjlist = "SELECT ci.id, ci.customer_name, svc.service_id, svc.service_type,
                          aa.audit_id, aa.application_rcv_date, aa.review_date, aa.app_obj_comment
                   FROM customer_info ci
                   INNER JOIN service svc ON ci.id = svc.customer_id
                   INNER JOIN audit_application aa ON aa.audit_id = (
                       SELECT MAX(aa_latest.audit_id)
                       FROM audit_application aa_latest
                       WHERE aa_latest.uni_svc_id = svc.service_id
                   )
                   WHERE ci.is_deleted = 'NO'
                     AND svc.service_type = 'CR'
                   ORDER BY aa.application_rcv_date DESC, ci.customer_name ASC";
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

        .app-objective-toolbar {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            padding: 14px;
            margin: 10px 0 16px;
            background: #f8fafc;
            border: 1px solid #dbe4ee;
            border-radius: 6px;
        }

        .app-objective-toolbar .filter-field {
            flex: 1 1 220px;
            min-width: 0;
        }

        .app-objective-toolbar label {
            display: block;
            margin-bottom: 5px;
            color: #374151;
            font-size: 12px;
            font-weight: 600;
        }

        .app-objective-toolbar input {
            width: 100%;
            height: 38px;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #fff;
        }

        .app-objective-toolbar .btn-report {
            height: 38px;
            padding: 8px 15px;
            border: 1px solid #163c6e;
            border-radius: 4px;
            background: #163c6e;
            color: #fff;
            font-weight: 600;
            white-space: nowrap;
        }

        .app-objective-toolbar .btn-report:hover,
        .app-objective-toolbar .btn-report:focus {
            background: #0f2f59;
            color: #fff;
        }

        .app-report-chooser {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 99999;
            padding: 24px;
            background: rgba(15, 23, 42, 0.58);
        }

        .app-report-dialog {
            width: 100%;
            max-width: 460px;
            margin: 9vh auto 0;
            overflow: hidden;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.28);
        }

        .app-report-dialog-head {
            padding: 15px 18px;
            background: #163c6e;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
        }

        .app-report-step {
            padding: 24px 20px;
            text-align: center;
        }

        .app-report-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .app-report-actions .btn {
            min-width: 150px;
        }

        .app-report-email-box {
            display: none;
            margin-top: 18px;
            text-align: left;
        }

        .app-report-email-message {
            min-height: 17px;
            margin-top: 7px;
            font-size: 12px;
        }

        @media (max-width: 600px) {

            .app-objective-toolbar .filter-field,
            .app-objective-toolbar .btn-report {
                width: 100%;
                flex-basis: 100%;
            }

            .app-report-actions .btn {
                width: 100%;
            }
        }

        html.theme-dark .app-objective-toolbar {
            background: #111827;
            border-color: #334155;
        }

        html.theme-dark .app-objective-toolbar label {
            color: #cbd5e1;
        }

        html.theme-dark .app-objective-toolbar input {
            background: #0f172a;
            border-color: #475569;
            color: #e5e7eb;
        }

        html.theme-dark .app-report-dialog {
            background: #111827;
            color: #e5e7eb;
        }

        html.theme-dark .app-report-dialog .form-control {
            background: #0f172a;
            border-color: #475569;
            color: #e5e7eb;
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container">
                <br>
                <div class="row">
                    <div class="col-sm-12 main_content" style="">
                        <div class="app-objective-toolbar">
                            <div class="filter-field">
                                <label for="filterFromDate">Application Received From</label>
                                <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate"
                                    placeholder="DD.MM.YYYY" value="" autocomplete="off">
                            </div>
                            <div class="filter-field">
                                <label for="filterToDate">Application Received To</label>
                                <input type="text" class="filterToDate" id="filterToDate" name="filterToDate"
                                    placeholder="DD.MM.YYYY" value="" autocomplete="off">
                            </div>
                            <div class="filter-field">
                                <label for="objectiveClientSearch">Client Name</label>
                                <input type="text" id="objectiveClientSearch" placeholder="Search by client name"
                                    autocomplete="off">
                            </div>
                            <button type="button" class="btn-report downloadObjectiveReport">
                                <i class="fa fa-cloud-download"></i> Download Report
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list"
                            width="100%">
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
                                foreach ($listOfCustomer as $customer) {
                                    $appdates = $customer;
                                    $datediff = '';
                                    $textcolor = 'black';
                                    if (
                                        !empty($appdates["application_rcv_date"])
                                        && $appdates["application_rcv_date"] !== '0000-00-00'
                                        && !empty($appdates["review_date"])
                                        && $appdates["review_date"] !== '0000-00-00'
                                    ) {
                                        $date1 = date_create($appdates["application_rcv_date"]);
                                        $date2 = date_create($appdates["review_date"]);
                                        $diff = date_diff($date1, $date2);
                                        $datediff = $diff->format("%a");
                                        if ($datediff > 2) {
                                            $textcolor = 'red';
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $customer["customer_name"] ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><span><?php echo simpleDateConversion($appdates['application_rcv_date']); ?>~</span><?php echo simpleDateConversion($appdates['application_rcv_date']); ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo simpleDateConversion($appdates['review_date']); ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $customer['id'] ?>" data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small style="color: <?php echo $textcolor ?>"><?php echo $datediff ?></small>
                                        </td>
                                        <td data-name="sel"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <div class="table-edit-control">
                                                <textarea rows="3" class="saveChanges"
                                                    data-row="<?php echo $appdates["audit_id"] ?>"
                                                    data-column="app_obj_comment"><?php echo $appdates["app_obj_comment"] ?></textarea>
                                            </div>
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

<div id="app-report-chooser" class="app-report-chooser" role="dialog" aria-modal="true"
    aria-labelledby="app-report-title">
    <div class="app-report-dialog">
        <div class="app-report-dialog-head" id="app-report-title">
            <i class="fa fa-file-text-o"></i> Application Review Objective Report
        </div>
        <div class="app-report-step app-report-format-step">
            <p>Select the report format.</p>
            <div class="app-report-actions">
                <button type="button" class="btn btn-danger app-report-format-pdf">
                    <i class="fa fa-file-pdf-o"></i> PDF
                </button>
                <button type="button" class="btn btn-success app-report-format-excel">
                    <i class="fa fa-file-excel-o"></i> Excel
                </button>
            </div>
            <div class="app-report-actions">
                <button type="button" class="btn btn-default btn-sm app-report-close">Cancel</button>
            </div>
        </div>
        <div class="app-report-step app-report-action-step" style="display:none;">
            <p>Selected format: <strong class="app-report-format-label"></strong></p>
            <div class="app-report-actions">
                <button type="button" class="btn btn-primary app-report-download">
                    <i class="fa fa-download"></i> Download
                </button>
                <button type="button" class="btn btn-warning app-report-email">
                    <i class="fa fa-envelope"></i> Send by Email
                </button>
            </div>
            <div class="app-report-email-box">
                <label for="appReportEmail">Email Address</label>
                <input type="email" id="appReportEmail" class="form-control app-report-email-input"
                    placeholder="name@example.com" autocomplete="off">
                <div class="app-report-email-message"></div>
                <button type="button" class="btn btn-success app-report-email-send" style="width:100%;">
                    <i class="fa fa-paper-plane"></i> Send Report
                </button>
            </div>
            <div class="app-report-actions">
                <button type="button" class="btn btn-default btn-sm app-report-back">Back</button>
                <button type="button" class="btn btn-default btn-sm app-report-close">Cancel</button>
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
        var pendingReportFormat = '';

        function resetReportChooser() {
            pendingReportFormat = '';
            $('.app-report-format-step').show();
            $('.app-report-action-step, .app-report-email-box').hide();
            $('.app-report-email-input').val('');
            $('.app-report-email-message').text('');
            $('.app-report-email-send').prop('disabled', false)
                .html('<i class="fa fa-paper-plane"></i> Send Report');
        }

        function closeReportChooser() {
            $('#app-report-chooser').hide();
            resetReportChooser();
        }

        function selectReportFormat(format, label) {
            pendingReportFormat = format;
            $('.app-report-format-label').text(label);
            $('.app-report-format-step').hide();
            $('.app-report-action-step').show();
            $('.app-report-email-box').hide();
        }

        function reportFields() {
            return {
                fromDate: $('#filterFromDate').val(),
                toDate: $('#filterToDate').val(),
                clientName: $('#objectiveClientSearch').val()
            };
        }

        function submitReportDownload() {
            if (!pendingReportFormat) {
                return;
            }
            var fields = reportFields();
            var action = pendingReportFormat === 'excel'
                ? 'exportAppReviewObjectiveReportExcel.php'
                : 'exportAppReviewObjectiveReport.php';
            var $form = $('<form>', { method: 'post', action: action }).hide();
            $.each(fields, function (name, value) {
                $('<input>', { type: 'hidden', name: name, value: value || '' }).appendTo($form);
            });
            $('body').append($form);
            $form[0].submit();
            setTimeout(function () {
                $form.remove();
            }, 1000);
            closeReportChooser();
        }

        $('.downloadObjectiveReport').click(function () {
            resetReportChooser();
            $('#app-report-chooser').show();
        });

        $('.app-report-format-pdf').click(function () {
            selectReportFormat('pdf', 'PDF');
        });

        $('.app-report-format-excel').click(function () {
            selectReportFormat('excel', 'Excel');
        });

        $('.app-report-download').click(submitReportDownload);

        $('.app-report-email').click(function () {
            $('.app-report-email-message').text('');
            $('.app-report-email-box').show();
            $('.app-report-email-input').focus();
        });

        $('.app-report-email-send').click(function () {
            var email = $.trim($('.app-report-email-input').val());
            var $message = $('.app-report-email-message');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $message.css('color', '#b91c1c').text('Please enter a valid email address.');
                return;
            }

            var $button = $(this);
            var fields = reportFields();
            fields.format = pendingReportFormat;
            fields.email = email;
            $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            $message.css('color', '#163c6e').text('Generating and sending the report...');

            $.post('emailAppReviewObjectiveReport.php', fields, null, 'json')
                .done(function (response) {
                    if (response && response.success) {
                        $message.css('color', '#15803d').text(response.message);
                        setTimeout(closeReportChooser, 1800);
                    } else {
                        $message.css('color', '#b91c1c').text(
                            response && response.message ? response.message : 'Could not send the report.'
                        );
                        $button.prop('disabled', false)
                            .html('<i class="fa fa-paper-plane"></i> Send Report');
                    }
                })
                .fail(function () {
                    $message.css('color', '#b91c1c').text('Server error. Please try again.');
                    $button.prop('disabled', false)
                        .html('<i class="fa fa-paper-plane"></i> Send Report');
                });
        });

        $('.app-report-back').click(resetReportChooser);
        $('.app-report-close').click(closeReportChooser);
        $('#app-report-chooser').click(function (event) {
            if (event.target === this) {
                closeReportChooser();
            }
        });

        $('#objectiveClientSearch').on('input', function () {
            table.column(0).search(this.value).draw();
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
                    alert("To Date can't be before From Date");
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
<div id="wrapper" class="active">

    <?php
    
    $pageIdentifier = "TDS list";
    include_once './header.php';
    include_once './dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    $status = $_GET['status'] ?? 'all';
    $startDateStr = $_GET['startDate'] ?? '';
    $endDateStr = $_GET['endDate'] ?? '';

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

    if ($status == 'received') {
        $allTds .= " AND ps.payment_received > 0";
    } elseif ($status == 'not_received') {
        $allTds .= " AND (ps.payment_received IS NULL OR ps.payment_received = 0 OR ps.payment_received = '' OR ps.payment_received = '0.00' OR ps.payment_received = '0')";
    }

    $params = [];
    if (!empty($startDate)) {
        $allTds .= " AND ps.payment_date >= :startDate";
        $params[':startDate'] = $startDate;
    }
    if (!empty($endDate)) {
        $allTds .= " AND ps.payment_date <= :endDate";
        $params[':endDate'] = $endDate;
    }

    $statement = $db->prepare($allTds);
    foreach ($params as $key => $val) {
        $statement->bindValue($key, $val);
    }
    $statement->execute();
    $listOfTds = $statement->fetchAll();
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
                                            <label for="startDate">From Date</label>
                                            <input type="text" name="startDate" id="startDate" class="datepicker-tds" value="<?php echo $startDateStr; ?>" autocomplete="off" placeholder="DD.MM.YYYY">
                                        </div>
                                        <div class="invoice-filter-item">
                                            <label for="endDate">To Date</label>
                                            <input type="text" name="endDate" id="endDate" class="datepicker-tds" value="<?php echo $endDateStr; ?>" autocomplete="off" placeholder="DD.MM.YYYY">
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

<script type="text/javascript">
    function exportReport() {
        var status = $('#status').val();
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();
        var url = 'exportTdsReport.php?status=' + status + '&startDate=' + startDate + '&endDate=' + endDate + '&action=export';
        window.open(url, '_blank');
    }

    $(document).ready(function () {
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
//            "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": false,
        "ordering": false,
        "lengthChange": false,
//            "pageLength": 10,
        "paging": false,
        "columnDefs": [
            {
                "width": "10%",
                "targets": 0
            },
            {
                "width": "15%",
                "targets": 1
            },
            {
                "width": "15%",
                "targets": 2
            },
            {
                "width": "12%",
                "targets": 3
            },
            {
                "width": "10%",
                "targets": 4
            },
        ]
                //        "paging":         false
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
            url: "deleteFinalInvoice.php",
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

    $(document).ready(function () {

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
            //            alert('hi');
            $(".addNewCustomer").show();
            $('.newCustomer').hide();
        });

        $('.cancelAdding').click(function () {
            $('.newCustomer').show();
            $(".addNewCustomer").hide();
        });

        $('.customerAccount').click(function () {
            var customerID = $(this).attr('data-id');
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                    '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        table.columns.adjust().draw();

    });
</script>

<div id="wrapper" class="active">
    <?php
//    session_start();
    $pageIdentifier = "Recycle";
    include 'header.php';
    include 'dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    $trashClient = "SELECT * FROM customer_info where is_deleted = 'YES'";
    $statement = $db->prepare($trashClient);
    $statement->execute();
    $clientList = $statement->fetchAll();
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
                <div class="row clearfix" style="padding-top:10px;">
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list" width="100%">
                            <thead>
                                <tr>
                                    <th class="" style="border-left: 1px solid #ffffff">
                                        <small></small>
                                    </th>
                                    <th class="">
                                        <small><a>Name of Customer</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Current Status</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Ph. No.</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Pay. Due CB</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Pay.Due Cons.</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Next Service due:</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Next Service Type:</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 0;
                                foreach ($clientList as $client) {
                                    ?>
                                    <tr id='addr<?php echo $client['id'] ?>'  data-id="<?php echo $client['id'] ?>">
                                        <td data-name="name" style="width:40px ; border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small>
                                                <a class="restoreCustomer" style="float: left; color: #999; padding-right: " data-id="<?php echo $client['id'] ?>" title="Restore Client"> <i class="fa fa-undo"></i></a> 
                                                <a class="deleteCustomer" style="float: right; color: #999; padding-right: " data-id="<?php echo $client['id'] ?>" title="Delete Client"> <i class="fa fa-trash-o"></i></a>
                                            </small>
                                        </td>
                                        <td data-name="mail" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small><a data-id="<?php echo $client['id'] ?>" class="customerAccount"><?php echo $client['customer_name'] ?></a></small>
                                        </td>
                                        <td data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small></small>
                                        </td>
                                        <td data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <small></small>
                                        </td>
                                        <td data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; text-align: center">
                                            <small></small>
                                        </td>
                                        <td data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; text-align: center">
                                            <small></small>
                                        </td>
                                        <td data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; text-align: center">
                                            <small><span></span></small>
                                        </td>
                                        <td data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;"> 
                                            <small></small>
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

<div id="delete-details" style="display: none">
    <div id="loading-div" class="ui-corner-all">
        <span style="font-size: 30pt;"><i style="padding: 5px;" class="fa fa-warning text-warning"></i></span>
        <div>
            <span style="font-size: 11pt; padding: 10px" class="text-primary meeting-cancel-confirm-message">Are you sure that you want to restore customer?</span>
        </div>
        <div style="padding: 3px;">
            <button title="Click here to delete" class="btn btn-primary btn-round btn-round-sm btn-sm delete-confirm-ok">Yes</button>
            <button title="Click here to cancel" class="btn btn-danger  btn-round btn-round-sm btn-sm delete-cancel">No</button>
        </div>
    </div>
</div>

<div id="delete-customer" style="display: none">
    <div id="loading-div" class="ui-corner-all">
        <span style="font-size:30pt;"><i style="padding:5px;" class="fa fa-warning text-warning"></i></span>
        <div> 
            <span style="font-size:11pt;padding:10px" class="text-primary meeting-cancel-confirm-message" >Are you sure that you want to permanently delete details?</span>
        </div>
        <div style="padding:3px;">
            <button title="Click here to Restore"  class="btn btn-primary btn-round btn-round-sm btn-sm delete-confirm">Ok</button>
            <button title="Click here to cancel" class="btn btn-danger  btn-round btn-round-sm btn-sm delete-canceled">Cancel</button>
        </div>
    </div>
</div>

<style>
    td { .table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot
         > tr > td, .table > tfoot > tr > th, .table > thead > tr > td, .table >
         thead > tr > th { padding:2px;
                           line-height: 1.0;
         }
    }

    #loading-div-background{
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        background: rgba(255, 255, 255, 0.5);
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    #loading-div{
        width: 300px;
        height: 150px;
        background-color: #fff;
        color:#1468b3;
        border: 5px solid #1468b3;
        text-align: center;
        color: #202020;
        position: absolute;
        left: 50%;
        top: 50%;
        margin-left: -150px;
        margin-top: -100px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        opacity: 1.0;
        behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */

    }
</style>

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

    //for filtering

    $.fn.dataTable.ext.search.push(
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
//                                alert(servieTypeStr);
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

        $('.restoreCustomer').click(function () {
            var customerid = $(this).attr('data-id');
            $("#delete-details").show();
            $('.delete-confirm-ok').click(function () {
                var url = 'restoreCustomer.php';
                var form = $('<form action="' + url + '" method="post">' +
                        '<input type="hidden" name="customerid" value="' + customerid + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            });
            $('.delete-cancel').click(function () {
                $('#delete-details').hide();
            });
        });
        
        $('.deleteCustomer').click(function () {
            var customerid = $(this).attr('data-id');
//            alert("customer id "+customerid);
            $("#delete-customer").show();
            $('.delete-confirm').click(function () {
                var url = 'restoreCustomer.php';
                var form = $('<form action="' + url + '" method="post">' +
                        '<input type="hidden" name="customerDlt" value="' + customerid + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            });
            $('.delete-canceled').click(function () {
                $('#delete-customer').hide();
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
                "width": "15%",
                "targets": 1
            },
            {
                "width": "10%",
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
            {
                "width": "10%",
                "targets": 5
            },
            {
                "width": "10%",
                "targets": 6
            },
            {
                "width": "25%",
                "targets": 8
            },
        ]
                //        "paging":         false
    });

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

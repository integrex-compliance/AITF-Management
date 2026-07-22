<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->

    <?php
    $pageIdentifier = "sent letter";
    include 'header.php';
    include 'dbConnect.php';

//    $notice = "SELECT ci.id, customer_name, email_id, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where action = '1st Reminder' and due_date <= now() and status = 'sent' and svcdt.is_deleted = 'N'";
    $notice = "SELECT ci.id, customer_name, email_id, unique_id, svc.service_id, service_type, svcdt.due_date, svcdt.sent_date, action, action_id FROM customer_info ci left join service svc on ci.id = svc.customer_id left join ca_service_dates svcdt on svc.service_id = svcdt.service_id where due_date <= now() and status = 'sent' and svcdt.is_deleted = 'N' order by sent_date desc";
    $statement = $db->prepare($notice);
    $statement->execute();
    $noticeDue = $statement->fetchAll();


    ?>

    <style>
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

    <div id="page-content-wrapper">

        <div id="loading-div-background" >
            <div id="loading-div" class="ui-corner-all text-center">
                <h1><i class="fa fa-spinner fa-spin" ></i></h1>
                <h4 class="meeting-loading-info"> Please wait...</h4>
            </div>
        </div>

        <div class="page-content inset">
            <div class="container" >
                <br>
                <div class="row">
                    <div class="col-sm-12 main_content" style="">
                        <div class="row" style="padding-top:10px;">
                            <div class="col-sm-1"><a class="btn btn-info filterBtn"><i class="fa fa-filter "></i></a></div>                            
                            <div class="col-sm-3 float-label-control custom-float-label-control">
                                <select name="service-type" class="form-control service-type" id="service-type">
                                    <option value="">Select Letter Type</option>
                                    <option value="1st Reminder">1st Reminder</option>
                                    <option value="2nd Reminder">2nd Reminder</option>
                                    <option value="Suspension Reminder">Suspension Reminder</option>
                                    <option value="Suspend Letter">Suspend Letter</option>
                                    <option value="Suspension Letter">Suspension Letter</option>
                                    <option value="Withdrawal Letter">Withdrawal Reminder</option>
                                    <option value="Withdrawal Letter">Withdraw Letter</option>
                                    <option value="Withdrawal Letter">Withdrawal Letter</option>
                                    <option value="Expire Letter">Expire Letter</option>
                                </select>
                            </div>
                            <div class="col-sm-3 float-label-control custom-float-label-control">
                                <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="Sent Date From" value="">
                            </div>
                            <div class="col-sm-3 form-group float-label-control custom-float-label-control">
                                <input type="text" class="filterToDate" id="filterToDate" name="filterToDate" placeholder="Sent Date To" value="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row clearfix" >
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list" width="100%">
                            <thead>
                                <tr>
                                    <th class="">
                                        <small><a>Client Name</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Letter Sent For Service </a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Sent Letter Type</a></small>
                                    </th>
                                    <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff()) { ?>
                                        <th class="">
                                            <small><a>Due On Date</a></small>
                                        </th>
                                    <?php } ?>
                                    <th class="">
                                        <small><a>Sent On Date</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Mailed To</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Action</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody> 
                                <?php
                                foreach ($noticeDue as $notiInfo) {
                                    $url = "";
                                    if ($notiInfo['action'] == "1st Reminder" && $notiInfo["service_type"] == "SA-II") {
                                        $url = "firstReminder.php";
                                    } else if ($notiInfo['action'] == "1st Reminder"){
                                        $url = "firstDue.php";
                                    } else if ($notiInfo['action'] == "2nd Reminder" && $notiInfo["service_type"] == "SA-II") {
                                        $url = "secondReminder.php";
                                    } else if ($notiInfo['action'] == "2nd Reminder") {
                                        $url = "reminder.php";
                                    } else if ($notiInfo['action'] == "Suspension Reminder") {
                                        $url = "suspension.php";
                                    } else if ($notiInfo['action'] == "Suspend Letter" || $notiInfo['action'] == "Suspension Letter") {
                                        $url = "suspension.php";
                                    } else if ($notiInfo['action'] == "Withdrawal Reminder") {
                                        $url = "withdrawRemind.php";
                                    } else if ($notiInfo['action'] == "Withdrawal Letter" || $notiInfo['action'] == "Withdraw Letter") {
                                        $url = "withdrawn.php";
                                    } else if ($notiInfo['action'] == "Expire Letter") {
                                        $url = "saiiWithdrawn.php";
                                    }
                                    ?>
                                    <tr id='addr' >
                                        <td class="customerAccount" data-id="<?php echo $notiInfo['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $notiInfo['customer_name'] ?></small>
                                        </td>
                                        <?php if($notiInfo['service_type'] == "CA" || $notiInfo['service_type'] == "RC"){
                                            $nextSVC = "SA-I";
                                        } else if($notiInfo['service_type'] == "SA-I"){
                                            $nextSVC = "SA-II";
                                        } else if($notiInfo['service_type'] == "SA-II"){
                                            $nextSVC = "RC";
                                        }?>
                                        <td class="customerAccount" data-id="<?php echo $notiInfo['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $nextSVC ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $notiInfo['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $notiInfo['action'] ?></small>
                                        </td>
                                        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff()) { ?>
                                            <td class="customerAccount" data-id="<?php echo $notiInfo['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                                <small><?php echo simpleDateConversion($notiInfo['due_date']) ?></small>
                                            </td>
                                        <?php } ?>
                                        <td class="customerAccount" data-id="<?php echo $notiInfo['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <span><?php echo simpleDateConversion($notiInfo['sent_date']) ?>~</span><small><?php echo simpleDateConversion($notiInfo['sent_date']) ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $notiInfo['id'] ?>" data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo $notiInfo['email_id'] ?></small>
                                        </td>
                                        <td data-name="name" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <a class="notificationGenerate btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['service_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" data-servicetype="<?php echo $notiInfo["service_type"] ?>" data-url="<?php echo $url; ?>" style="float: right;"><i class="fa fa-file-pdf-o"></i></a>
                                            <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
                                                <a class="deleteLetter btn btn-default btn-xs btn-info" title="Generate" data-customer='<?php echo $notiInfo['id'] ?>' data-service='<?php echo $notiInfo['service_id'] ?>' data-action ="<?php echo $notiInfo['action_id'] ?>" style="float: right; margin-right: 10px"><i class="fa fa-trash"></i></a>
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

<script type="text/javascript" >
    function ShowProgressAnimation() {
//        alert("called");
        $("#loading-div-background").show();
//        $("#loading-div-background-resend").show();
    }
    function HideProgressAnimation() {
//        alert("called");
        $("#loading-div-background").hide();
//        $("#loading-div-background-resend").show();
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

        $(".deleteLetter").click(function () {
            var rowid = $(this).attr("data-action");
            var sent = "sentpage";
            var url = 'addComments.php'
            var form = $('<form action="' + url + '" method="post">' +
                    '<input type="hidden" name="rowid" value="' + rowid + '" />' +
                    '<input type="hidden" name="page" value="' + sent + '" />' +
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

        $('.customerAccount').click(function () {
            var customerID = $(this).attr('data-id');
            localStorage['visited'] = customerID;
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                    '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.notificationGenerate').click(function () {
            var customer = $(this).attr('data-customer');
            var service = $(this).attr('data-service');
            var action = $(this).attr('data-action');
            var servicetype = $(this).attr("data-servicetype");
            var url = $(this).attr("data-url");
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                    '<input type="hidden" name="customer" value="' + customer + '" />' +
                    '<input type="hidden" name="service" value="' + service + '" />' +
                    '<input type="hidden" name="action" value="' + action + '" />' +
                    '<input type="hidden" name="servicetype" value="' + servicetype + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });


        var table = $('#tab_customer_list').DataTable({
            "sScrollX": "100%",
            "scrollY": '70vh',
            "scrollCollapse": true,
            "paging": false,
            "ordering": false,
            "lengthChange": false,
//            "pageLength": 10,
            "paging": false,
            "columnDefs": [

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

    });
</script>

<div id="wrapper" class="active">

    <?php
    
    $pageIdentifier = "TDS list";
    include_once './header.php';
    include_once './dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    //$allTds = "SELECT ci.customer_name,ci.unique_id,ps.customer_id,ps.service_type,ps.amount_decided,ps.payment_received,ps.payment_date FROM customer_info ci left join payment_system ps ON ci.id=ps.customer_id WHERE  ps.service_type LIKE '%tds%'";
    $allTds = "SELECT ci.id, customer_name,ci.unique_id,ps.customer_id,ps.service_type as tds, ps.amount_decided,ps.payment_received,ps.payment_date,ps.comment,ps.payment_id, svc.service_id, svc.service_type FROM customer_info ci left join payment_system ps ON ci.id=ps.customer_id left join service svc on ps.customer_id = svc.customer_id WHERE  svc.service_id = (select max(tmp.service_id) from service tmp where tmp.customer_id = ci.id) and ps.service_type LIKE '%tds%'";
    $statement = $db->prepare($allTds);
    $statement->execute();
    $listOfTds = $statement->fetchAll();
    ?>

    <!-- Page content -->
    <div id="page-content-wrapper">
        <!-- Keep all page content within the page-content inset div! -->
        <div class="page-content inset">
            <div class="row">
                <div class="col-md-12">
                    <div class="container" style="margin-top: 20px;">
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

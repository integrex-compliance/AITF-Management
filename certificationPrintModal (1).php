<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="certificationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                Certification Print
            </div>  
            <form action="addCertificationPrint.php" method="POST">
                <div class="modal-body" >
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formCdc">
                            <span><i class="fa ">Certification Print</i></span>
                        </div>
                    </div>
                    <div class="CdC">
                        <div class="row " style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Customer Name</label>
                                <input type="text" class="certifyCustomer" id="certifyCustomer" name="certifyCustomer" placeholder="Client Name" required="">
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Customer Address</label>
                                <textarea name="certificationAddress" rows="4" class="form-control form-group certificationAddress" id="certificationAddress" placeholder="Client Address"></textarea>
                            </div>                            
                        </div>
                        <div class="row " style="padding-top: 10px"> 
                            <div class="col-sm-6 form-group float-label-control">
                                <label> Certification No</label>
                                <input type="text" class="certificationNo" id="certificationNo" name="certificationNo" placeholder="Certification No" required>                               
                                <!--                                <label> Certification No</label>
                                                                <input type="text" class="certificationNo" id="certificationNo" name="certificationNo" placeholder="Certification No" required>-->
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Scope</label>
                                <textarea name="certifyScope" rows="2" class="form-control form-group certifyScope" id="certifyScope" placeholder="Scope"></textarea>
                            </div>                            
                        </div>
                        <div class="row " style="padding-top: 10px">                             
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Registration Date</label>
                                        <input type="text" class="certifyRegistrationDate" id="certifyRegistrationDate" name="certifyRegistrationDate" placeholder="Registration Date" required>                                
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="certifyRegistrationDateError" style="display: none">
                                            <small> <span id="certifyRegistrationDateErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Valid Until</label>
                                        <input type="text" class="certifyValidTill" id="certifyValidTill" name="certifyValidTill" placeholder="Valid Until" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="certifyValidTillDateError" style="display: none">
                                            <small> <span id="certifyValidTillDateErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Before Date</label>
                                        <input type="text" class="certifyBeforeDate" id="certifyBeforeDate" name="certifyBeforeDate" placeholder="Before Date" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="certifyBeforeDateError" style="display: none">
                                            <small> <span id="certifyBeforeDateErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>                                
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="certifyCustomerId" name="certifyCustomerId" value="">
                <input type="hidden" class="certifyServiceId" name="certifyServiceId" value="">
                <input type="hidden" class="certificationId" name="certificationId" value="">
                <input type="hidden" class="cp-service-id" name="cp-service-id" value="">
                <input type="hidden" class="svc-id" name="svc-id" value="">
                <input type="hidden" class="ca-id" name="ca-id" value="">
                <div class="modal-footer">
                    <button type="submit" name="certify-customer" class="btn btn-xs btn-info certify-customer">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="certificateDownloadModal" tabindex="-1" role="dialog" aria-labelledby="certificateDownloadModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="certificateDownloadModalLabel">Select Certificate</h4>
            </div>
            <div class="modal-body">
                <p>Please choose which certificate to download:</p>
                <div id="certificateDownloadOptions" class="certificate-download-options"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" >
    $(function () {
        $('#certifyRegistrationDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#certifyRegistrationDate").keydown(function (e) {
//            e.preventDefault();
        });
        $('#certifyValidTill').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#certifyValidTill").keydown(function (e) {
//            e.preventDefault();
        });
        $('#certifyBeforeDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#certifyBeforeDate").keydown(function (e) {
//            e.preventDefault();
        });
    });

    function validateCertPrint() {
        $isError = false;
        if ($(".certifyRegistrationDate").val() == "") {
            $('#certifyRegistrationDateErrorMsg').text("Date can not be empty");
            $('#certifyRegistrationDateError').show();
            $('#certifyRegistrationDate').addClass('inputHighlight');
            $isError = true;
        } else if ($(".certifyRegistrationDate").val() != "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    certifyRegistrationDate: $('.certifyRegistrationDate').val(),
                    certifyServiceId: $('.ca-id').val(),
                },
                async: false,
                success: function (data) {
//    	                alert(data);
                    if (data == 1) {
                        $('#certifyRegistrationDateErrorMsg').text("Date should be equal to CDC date");
                        $('#certifyRegistrationDateError').show();
                        $('#certifyRegistrationDate').addClass('inputHighlight');
                        $isError = true;
                    } else {
                        $('#certifyRegistrationDateError').hide();
                        $('#certifyRegistrationDate').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#certifyRegistrationDateError').hide();
            $('#certifyRegistrationDate').removeClass('inputHighlight');
        }
        if ($(".certifyValidTill").val() == "") {
            $('#certifyValidTillDateErrorMsg').text("Date can not be empty");
            $('#certifyValidTillDateError').show();
            $('#certifyValidTill').addClass('inputHighlight');
            $isError = true;
        } else if ($(".certifyValidTill").val() != "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    certifyValidTill: $('.certifyValidTill').val(),
                    certifyService_Id: $('.certifyServiceId').val(),
                },
                async: false,
                success: function (data) {
//    	                alert(data);
                    if (data == 1) {
                        $('#certifyValidTillDateErrorMsg').text("Date should be equal to CDC Expire on date");
                        $('#certifyValidTillDateError').show();
                        $('#certifyValidTill').addClass('inputHighlight');
                    } else {
                        $('#certifyValidTillDateError').hide();
                        $('#certifyValidTill').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#certifyValidTillDateError').hide();
            $('#certifyValidTill').removeClass('inputHighlight');
        }
        if ($(".certifyBeforeDate").val() == "") {
            $('#certifyBeforeDateErrorMsg').text("Date can not be empty");
            $('#certifyBeforeDateError').show();
            $('#certifyBeforeDate').addClass('inputHighlight');
            $isError = true;
        } else if ($(".certifyBeforeDate").val() != "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    certifyBeforeDate: $('.certifyBeforeDate').val(),
                    certifyService_ID: $('.certifyServiceId').val(),
                },
                async: false,
                success: function (data) {
//    	                alert(data);
                    if (data == 1) {
                        $('#certifyBeforeDateErrorMsg').text("Date should be equal to Next periodical audit conducted before date");
                        $('#certifyBeforeDateError').show();
                        $('#certifyBeforeDate').addClass('inputHighlight');
                    } else {
                        $('#certifyBeforeDateError').hide();
                        $('#certifyBeforeDate').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#certifyBeforeDateError').hide();
            $('#certifyBeforeDate').removeClass('inputHighlight');
        }
        return $isError;
    }

    $actionRowId = "";
    $actionRowColumn = "";
    $checkedValue = "";

    $(document).ready(function () {

        $(".certify-customer").click(function (e) {
            if (validateCertPrint()) {
                e.preventDefault();
                return;
            }
        });

        $('.certificate-print').click(function () {
//            alert($(this).attr('data-customer')+"->"+$(this).attr('data-service'));
            $(".svc-id").val($(this).attr("data-svcid"));
            $('.certifyCustomerId').val($(this).attr('data-customer'));
            $('.certifyServiceId').val($(this).attr('data-service'));
            $('.cp-service-id').val($(this).attr('data-id'));
            $('.ca-id').val($(this).attr('data-caid'));
//            alert($(this).attr('data-service'));
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    cust_id: $(this).attr('data-customer'),
                    cdc_ID: $(this).attr('data-service'),
                },
                success: function (data)
                { 
//                    alert(data[0]["scope"]);
//                    alert("Database - > " + data[1]['Date'])
//                    var date = new Date(moment(data[1]['Date']).format('DD.MM.YYYY'));
                    var newdate = new Date(data[1]['Date']);
                    newdate.setDate(newdate.getDate() - 1);
                    var nd = new Date(newdate);
                    var revdate = moment(nd).add(36, 'Month');
                    var nxtPeriodical = moment(nd).add(12, 'Month');
//                    alert("3 years -> "+revdate);
//                    alert("3 years -> "+nxtPeriodical)
                    $('.certifyCustomer').val(data[0]['customer_name']);
                    $('.certificationAddress').val(data[0]['address']);
                    $('.certificationNo').val(data[1]['certificateNo']);
                    $(".certifyScope").val(data[0]["scope"]);
                    $('.certifyRegistrationDate').val(moment(data[0]['certification_stage_date']).format('DD.MM.YYYY'));
//                    var revdate = moment($('.certifyRegistrationDate').val()).add(3, 'year');
//                    alert(moment(data[1]['Date']).format('DD.MM.YYYY'));
//                    alert(revdate);
                    $('.certifyValidTill').val(moment(data[1]['expiredate']).format('DD.MM.YYYY'));
                    $('.certifyBeforeDate').val(moment(data[1]['periodicalaudit']).format('DD.MM.YYYY'));
                    $('input[type="text"]').trigger('change');
                    $('.certifyScope').trigger('change');
                    $('.certificationAddress').trigger('change');
                    $('#certificationModal').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.edit-certificate-print').click(function () {
            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    certificationId: $(this).attr('data-certificate'),
                    serviceii_Id: $(this).attr('data-service'),
                    customerid: $(this).attr("data-customer"),
                },
                success: function (data)
                {
//                    alert(data[0]);
                    $('.certificationId').val(data[0]['certificate_id']);
                    $('.certifyCustomerId').val(data[0]['customer_id']);
                    $('.certifyCustomer').val(data[0]['customer_name']);
                    $('.certificationAddress').val(data[0]['customer_address']);
                    $('.certificationNo').val(data[0]['certificate_no']);
                    $(".certifyScope").val(data[0]["certificate_scope"]);
                    $('.certifyRegistrationDate').val(moment(data[0]['registration_date']).format('DD.MM.YYYY'));
                    $('.certifyValidTill').val(moment(data[0]['valid_until']).format('DD.MM.YYYY'));
                    $('.certifyBeforeDate').val(moment(data[0]['before_date']).format('DD.MM.YYYY'));
                    $('.certifyServiceId').val(data[0]["service_id"]);
                    $('input[type="text"]').trigger('change');
//                    $('textarea').trigger('change');
                    $('#certificationModal').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        function renderCertificateOptions(options, context) {
            var $list = $('#certificateDownloadOptions');
            $list.empty();

            options.forEach(function (entry, index) {
                var labelParts = [];
                if (entry.iso_label) {
                    labelParts.push(entry.iso_label);
                }
                if (entry.certificate_no) {
                    labelParts.push('Certificate No: ' + entry.certificate_no);
                }

                var displayLabel = labelParts.join(' | ');
                if (!displayLabel) {
                    displayLabel = 'Certificate ' + (index + 1);
                }

                var $button = $('<button/>', {
                    type: 'button',
                    class: 'btn btn-sm btn-primary certificate-download-option',
                    'data-certify-id': context.certify_id,
                    'data-service': context.service,
                    'data-customer': context.customer,
                    'data-crr': context.crr_id || '',
                    'data-iso-index': entry.iso_index,
                    'data-iso-label': entry.iso_label || '',
                    text: displayLabel
                });

                $list.append($('<div/>', { class: 'option-row', }).append($button));
            });

            $('#certificateDownloadModal').modal('show');
        }

        function submitCertificateForm(payload) {
            var url = 'CertificatePrintPdf.php';
            var form = $('<form action="' + url + '" method="post" target="_blank" >' +
                    '<input type="hidden" name="certify_id" value="' + payload.certify_id + '" />' +
                    '<input type="hidden" name="service" value="' + payload.service + '" />' +
                    '<input type="hidden" name="customer" value="' + payload.customer + '" />' +
                    '<input type="hidden" name="crr_id" value="' + (payload.crr_id || '') + '" />' +
                    '<input type="hidden" name="iso_index" value="' + (payload.iso_index || '') + '" />' +
                    '<input type="hidden" name="iso_label" value="' + (payload.iso_label || '') + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        }

        $('#certificateDownloadOptions').on('click', '.certificate-download-option', function () {
            var $btn = $(this);
            submitCertificateForm({
                certify_id: $btn.data('certify-id'),
                service: $btn.data('service'),
                customer: $btn.data('customer'),
                crr_id: $btn.data('crr'),
                iso_index: $btn.data('iso-index'),
                iso_label: $btn.data('iso-label')
            });
            $('#certificateDownloadModal').modal('hide');
        });

        $('.download-certificate-print').click(function () {
            var context = {
                certify_id: $(this).attr('data-certificate'),
                service: $(this).attr('data-service'),
                customer: $(this).attr('data-customer'),
                crr_id: $(this).attr('data-crr') || $(this).attr('data-notification') || ''
            };

            if (!context.crr_id) {
                submitCertificateForm(context);
                return;
            }

            $.ajax({
                url: 'getClientInfo.php',
                type: 'GET',
                dataType: 'json',
                data: {
                    downloadOptions: 1,
                    crr_id: context.crr_id
                },
                success: function (response) {
                    if (!$.isArray(response) || response.length === 0) {
                        submitCertificateForm(context);
                        return;
                    }

                    if (response.length === 1) {
                        submitCertificateForm($.extend({}, context, {
                            iso_index: response[0].iso_index || '',
                            iso_label: response[0].iso_label || ''
                        }));
                        return;
                    }

                    renderCertificateOptions(response, context);
                },
                error: function () {
                    submitCertificateForm(context);
                }
            });
        });

    });
</script>

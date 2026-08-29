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

<div class="modal fade" id="dscCertificateModal" tabindex="-1" role="dialog" aria-labelledby="dscCertificateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 520px;">
        <div class="modal-content" style="border-radius: 8px; box-shadow: 0 8px 30px rgba(0,0,0,0.3); border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; border-top-left-radius: 8px; border-top-right-radius: 8px; padding: 14px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.9;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="dscCertificateModalLabel" style="font-size: 16px; font-weight: 600; margin: 0; color: #fff;">
                    <i class="fa fa-pencil-square-o" style="margin-right: 8px; color: #54a0ff;"></i> Digital Signature Certificate (DSC) Sign
                </h4>
            </div>
            <div class="modal-body" style="padding: 20px 24px;">
                <!-- Certificate Details Card -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px;">
                    <div style="font-size: 13px; color: #334155; margin-bottom: 5px;">
                        <strong style="color: #0f172a;"><i class="fa fa-building-o text-primary" style="margin-right: 5px;"></i> Client:</strong> <span id="dscCustomerName" style="font-weight: 600; color: #1e3c72;">Loading...</span>
                    </div>
                    <div style="font-size: 13px; color: #334155; margin-bottom: 5px;">
                        <strong style="color: #0f172a;"><i class="fa fa-certificate text-warning" style="margin-right: 5px;"></i> Certificate No:</strong> <span id="dscCertNo" style="font-weight: 600;">-</span>
                    </div>
                    <div id="dscSingleIsoContainer" style="font-size: 13px; color: #334155;">
                        <strong style="color: #0f172a;"><i class="fa fa-check-circle text-success" style="margin-right: 5px;"></i> Standard:</strong> <span id="dscIsoStandard" style="font-weight: 600;">-</span>
                    </div>
                </div>

                <!-- Multi-ISO selection (if customer has multiple ISO standards) -->
                <div id="dscMultiIsoContainer" style="display: none; margin-bottom: 16px;">
                    <label style="font-size: 13px; font-weight: 600; color: #334155; display: block; margin-bottom: 5px;">
                        <i class="fa fa-list-alt text-primary" style="margin-right: 5px;"></i> Select ISO Certificate Standard:
                    </label>
                    <select id="dscIsoSelect" class="form-control" style="border-radius: 6px; font-size: 13px; height: 38px;"></select>
                </div>

                <!-- Live USB Token Status Banner -->
                <div id="dscTokenStatusBanner" style="margin-bottom: 16px; border-radius: 6px; padding: 10px 14px; font-size: 12px; background: #eef2ff; border-left: 3px solid #2a5298;">
                    <div id="dscTokenStatusText" style="color: #1e3c72; font-weight: 600;">
                        <i class="fa fa-shield"></i> Signatory: <b>Managing Director</b> (Class 3 DSC)
                    </div>
                    <div id="dscTokenStatusSub" style="font-size: 11px; color: #475569; margin-top: 2px;">
                        Organization: <b>A.I.T.F. MANAGEMENT CERTIFICATION LIMITED</b>
                    </div>
                </div>

                <!-- Token PIN Input -->
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="dscTokenPin" style="font-size: 13px; font-weight: 600; color: #1e293b; display: block; margin-bottom: 6px;">
                        <i class="fa fa-key" style="color: #e67e22; margin-right: 5px;"></i> Enter USB Token PIN <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="password" id="dscTokenPin" class="form-control" placeholder="Enter Token PIN (e.g. 12345678)" style="border-radius: 6px; height: 40px; font-size: 14px; letter-spacing: 2px;" autocomplete="off">
                    <small style="color: #64748b; font-size: 11px; margin-top: 4px; display: block;">
                        <i class="fa fa-lock" style="margin-right: 4px; color: #10b981;"></i> PIN is verified locally inside USB token hardware (never sent to server or internet).
                    </small>
                    <div id="dscPinError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 6px;">
                        <i class="fa fa-exclamation-circle"></i> <span id="dscPinErrorMsg">Please enter USB Token PIN</span>
                    </div>
                </div>

                <!-- Optional Signing Metadata -->
                <div class="row">
                    <div class="col-xs-6 form-group" style="margin-bottom: 8px;">
                        <label style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Signing Reason</label>
                        <input type="text" id="dscSigningReason" class="form-control input-sm" value="Authorized Certificate Issuance" style="border-radius: 4px; font-size: 12px;">
                    </div>
                    <div class="col-xs-6 form-group" style="margin-bottom: 8px;">
                        <label style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Signing Location</label>
                        <input type="text" id="dscSigningLocation" class="form-control input-sm" value="New Delhi, India" style="border-radius: 4px; font-size: 12px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; padding: 12px 20px;">
                <div class="pull-left" style="padding-top: 6px;">
                    <a href="aitf_dsc_signer/AITF-Signer-Setup.zip" download style="font-size: 11px; color: #475569; text-decoration: none;">
                        <i class="fa fa-download" style="color: #2563eb; margin-right: 4px;"></i> <u>Download Signer App for Windows</u>
                    </a>
                </div>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="border-radius: 4px;">Cancel</button>
                <button type="button" id="btnExecuteDscSign" class="btn btn-primary btn-sm" style="background: #2a5298; border-color: #1e3c72; border-radius: 4px; font-weight: 600; padding: 6px 16px;">
                    <i class="fa fa-pencil-square-o" style="margin-right: 5px;"></i> Sign & Download Certificate
                </button>
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
                    $('.certifyRegistrationDate').val(moment(data[1]['certificate_registration_date'] || data[1]['Date']).format('DD.MM.YYYY'));
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
                    'data-crr': entry.crr_id || context.crr_id || '',
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
            var isoIndex = payload.iso_index;
            if (isoIndex === undefined || isoIndex === null) {
                isoIndex = '';
            }
            var form = $('<form action="' + url + '" method="post" target="_blank" >' +
                    '<input type="hidden" name="certify_id" value="' + payload.certify_id + '" />' +
                    '<input type="hidden" name="service" value="' + payload.service + '" />' +
                    '<input type="hidden" name="customer" value="' + payload.customer + '" />' +
                    '<input type="hidden" name="crr_id" value="' + (payload.crr_id || '') + '" />' +
                    '<input type="hidden" name="iso_index" value="' + isoIndex + '" />' +
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
                            crr_id: response[0].crr_id || context.crr_id,
                            iso_index: response[0].iso_index,
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
        var currentDscContext = {};
        var dscTokenStatusTimer = null;

        function checkUsbTokenStatus() {
            var $banner = $('#dscTokenStatusBanner');
            var $text = $('#dscTokenStatusText');
            var $sub = $('#dscTokenStatusSub');

            var controller = new AbortController();
            var timeoutId = setTimeout(function () { controller.abort(); }, 1800);

            fetch('http://127.0.0.1:8765/status', { method: 'GET', mode: 'cors', signal: controller.signal })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    clearTimeout(timeoutId);
                    if (data && data.connected) {
                        $banner.css({ 'background': '#eafaf1', 'border-left': '4px solid #27ae60' });
                        $text.html('<i class="fa fa-check-circle" style="color:#27ae60; margin-right:5px;"></i> USB Token Connected: <b>' + (data.signer_name || 'Class 3 DSC') + '</b>');
                        $sub.html('Issuer: <b>' + (data.issuer || 'Licensed Indian CA') + '</b> &nbsp;|&nbsp; Valid Until: <b>' + (data.valid_to || 'Active') + '</b>');
                        $('#dscPinError').hide();
                    } else {
                        $banner.css({ 'background': '#fef9e7', 'border-left': '4px solid #f39c12' });
                        $text.html('<i class="fa fa-exclamation-triangle" style="color:#e67e22; margin-right:5px;"></i> USB Token Not Connected');
                        $sub.text('Please plug your physical Class 3 DSC USB Token (Verasys / eMudhra / Capricorn) into the PC.');
                    }
                })
                .catch(function () {
                    clearTimeout(timeoutId);
                    $banner.css({ 'background': '#ebf5fb', 'border-left': '4px solid #3498db' });
                    $text.html('<i class="fa fa-info-circle" style="color:#2980b9; margin-right:5px;"></i> Signer App Not Running on this PC');
                    $sub.html('Please start <b>Start_AITF_Signer.bat</b> or <a href="aitf_dsc_signer/AITF-Signer-Setup.zip" download class="btn btn-xs btn-primary" style="margin-left: 6px; font-weight:600; text-decoration:none;"><i class="fa fa-download"></i> Download Signer Setup (18 KB)</a>');
                });
        }

        $('#dscCertificateModal').on('shown.bs.modal', function () {
            checkUsbTokenStatus();
            if (dscTokenStatusTimer) clearInterval(dscTokenStatusTimer);
            dscTokenStatusTimer = setInterval(checkUsbTokenStatus, 2000);
            setTimeout(function () {
                $('#dscTokenPin').val('').focus();
            }, 300);
        });

        $('#dscCertificateModal').on('hidden.bs.modal', function () {
            if (dscTokenStatusTimer) clearInterval(dscTokenStatusTimer);
            $('#dscTokenPin').val('');
        });

        function resetSignButton() {
            $('#btnExecuteDscSign').prop('disabled', false).html('<i class="fa fa-pencil-square-o" style="margin-right: 5px;"></i> Sign & Download Certificate');
        }

        $('.download-dsc-certificate-print').click(function () {
            var context = {
                certify_id: $(this).attr('data-certificate') || '',
                service: $(this).attr('data-service') || '',
                customer: $(this).attr('data-customer') || '',
                crr_id: $(this).attr('data-crr') || $(this).attr('data-notification') || '',
                iso_index: '',
                iso_label: ''
            };
            currentDscContext = context;

            // Reset inputs and errors
            $('#dscPinError').hide();
            $('#dscCustomerName').text('Loading...');
            $('#dscCertNo').text('Loading...');
            $('#dscIsoStandard').text('Loading...');
            $('#dscMultiIsoContainer').hide();
            $('#dscSingleIsoContainer').show();
            $('#dscIsoSelect').empty();
            resetSignButton();

            // Check Live USB Token Status
            checkUsbTokenStatus();

            // Fetch client details
            $.ajax({
                url: 'getClientInfo.php',
                type: 'GET',
                dataType: 'json',
                data: {
                    cust_id: context.customer,
                    cdc_ID: context.service
                },
                success: function (data) {
                    if (data && data[0]) {
                        $('#dscCustomerName').text(data[0]['customer_name'] || '-');
                        $('#dscCertNo').text((data[1] && data[1]['certificateNo']) ? data[1]['certificateNo'] : '-');
                        $('#dscIsoStandard').text(data[0]['scope'] || 'ISO Management Standard');
                    }
                }
            });

            // Fetch ISO options
            if (context.crr_id) {
                $.ajax({
                    url: 'getClientInfo.php',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        downloadOptions: 1,
                        crr_id: context.crr_id
                    },
                    success: function (response) {
                        if ($.isArray(response) && response.length > 1) {
                            $('#dscSingleIsoContainer').hide();
                            $('#dscMultiIsoContainer').show();
                            var $sel = $('#dscIsoSelect');
                            $sel.empty();
                            response.forEach(function (opt, idx) {
                                var label = (opt.iso_label ? opt.iso_label : 'Standard ' + (idx + 1)) + (opt.certificate_no ? ' (' + opt.certificate_no + ')' : '');
                                $sel.append($('<option>', {
                                    value: opt.iso_index !== undefined ? opt.iso_index : '',
                                    'data-label': opt.iso_label || '',
                                    'data-certno': opt.certificate_no || '',
                                    text: label
                                }));
                            });
                            if (response[0] && response[0].certificate_no) {
                                $('#dscCertNo').text(response[0].certificate_no);
                            }
                        } else if ($.isArray(response) && response.length === 1) {
                            if (response[0].iso_label) {
                                $('#dscIsoStandard').text(response[0].iso_label);
                            }
                            if (response[0].certificate_no) {
                                $('#dscCertNo').text(response[0].certificate_no);
                            }
                            currentDscContext.iso_index = response[0].iso_index;
                            currentDscContext.iso_label = response[0].iso_label || '';
                        }
                    }
                });
            }

            $('#dscCertificateModal').modal('show');
        });

        $('#dscIsoSelect').change(function () {
            var selectedOption = $(this).find('option:selected');
            var certNo = selectedOption.data('certno');
            if (certNo) {
                $('#dscCertNo').text(certNo);
            }
        });

        $('#btnExecuteDscSign').click(function () {
            var pin = $('#dscTokenPin').val().trim();
            if (!pin) {
                $('#dscPinErrorMsg').text('Please enter your USB Token PIN to digitally sign.');
                $('#dscPinError').show();
                $('#dscTokenPin').focus();
                return;
            }
            $('#dscPinError').hide();

            var isoIndex = currentDscContext.iso_index;
            var isoLabel = currentDscContext.iso_label;

            if ($('#dscMultiIsoContainer').is(':visible')) {
                var $selected = $('#dscIsoSelect option:selected');
                isoIndex = $selected.val();
                isoLabel = $selected.data('label') || '';
            }

            $('#btnExecuteDscSign').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Preparing Certificate & Requesting Hardware Token Sign...');

            // Step 1: Request authorized PDF template & HMAC signing ticket from backend (NO PIN SENT TO PHP)
            $.ajax({
                url: 'CertificatePrintPdf.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    certify_id: currentDscContext.certify_id || '',
                    service: currentDscContext.service || '',
                    customer: currentDscContext.customer || '',
                    crr_id: currentDscContext.crr_id || '',
                    iso_index: (isoIndex !== undefined && isoIndex !== null ? isoIndex : ''),
                    iso_label: (isoLabel || ''),
                    is_digitally_signed: 1,
                    output_base64: 1,
                    signing_reason: $('#dscSigningReason').val() || 'Authorized ISO Certificate Issuance',
                    signing_location: $('#dscSigningLocation').val() || 'New Delhi, India',
                    signatory_name: 'Managing Director'
                },
                success: function (pdfRes) {
                    if (!pdfRes || !pdfRes.pdf_base64 || !pdfRes.signing_ticket) {
                        $('#dscPinErrorMsg').text('Could not prepare authorized certificate ticket. Please retry.');
                        $('#dscPinError').show();
                        resetSignButton();
                        return;
                    }

                    $('#btnExecuteDscSign').html('<i class="fa fa-spinner fa-spin"></i> Validating PIN on USB Hardware Token...');

                    // Step 2: Send base64 PDF + Ticket + PIN to Local AITF USB DSC Signer Bridge (LOOPBACK ONLY)
                    fetch('http://127.0.0.1:8765/sign-pdf', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            pdf_base64: pdfRes.pdf_base64,
                            signing_ticket: pdfRes.signing_ticket,
                            pin: pin,
                            reason: $('#dscSigningReason').val() || 'Authorized ISO Certificate Issuance',
                            location: $('#dscSigningLocation').val() || 'New Delhi, India',
                            signatory_name: 'Managing Director'
                        })
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (signRes) {
                        if (signRes && signRes.success && signRes.signed_pdf_base64) {
                            $('#btnExecuteDscSign').html('<i class="fa fa-spinner fa-spin"></i> Verifying & Archiving Signed Certificate on Server...');

                            // Step 3: Upload signed PDF to server for independent verification and permanent archiving
                            $.ajax({
                                url: 'verify_and_save_signed_pdf.php',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    signed_pdf_base64: signRes.signed_pdf_base64,
                                    signing_ticket: pdfRes.signing_ticket,
                                    certificate_number: pdfRes.certificate_number,
                                    signer_meta: {
                                        signer_name: signRes.signer_name || 'Managing Director',
                                        issuer: signRes.issuer || '',
                                        serial_number: signRes.serial_number || '',
                                        thumbprint: signRes.thumbprint || ''
                                    }
                                }),
                                success: function (saveRes) {
                                    if (saveRes && saveRes.success) {
                                        // Step 4: Server verification passed! Trigger direct client download
                                        var byteChars = atob(signRes.signed_pdf_base64);
                                        var byteNumbers = new Array(byteChars.length);
                                        for (var i = 0; i < byteChars.length; i++) {
                                            byteNumbers[i] = byteChars.charCodeAt(i);
                                        }
                                        var byteArray = new Uint8Array(byteNumbers);
                                        var blob = new Blob([byteArray], { type: 'application/pdf' });
                                        var link = document.createElement('a');
                                        link.href = window.URL.createObjectURL(blob);
                                        link.download = saveRes.filename || pdfRes.filename || 'Certificate_DigitallySigned.pdf';
                                        document.body.appendChild(link);
                                        link.click();
                                        document.body.removeChild(link);

                                        $('#dscCertificateModal').modal('hide');
                                        $('#dscTokenPin').val('');
                                        resetSignButton();
                                    } else {
                                        var err = (saveRes && saveRes.error) ? saveRes.error : 'Server verification of signed PDF failed.';
                                        $('#dscPinErrorMsg').text('Verification Failed: ' + err);
                                        $('#dscPinError').show();
                                        resetSignButton();
                                    }
                                },
                                error: function (xhr) {
                                    var err = 'Server-side verification failed. Certificate was not archived.';
                                    try {
                                        var parsed = JSON.parse(xhr.responseText);
                                        if (parsed && parsed.error) err = parsed.error;
                                    } catch (e) {}
                                    $('#dscPinErrorMsg').text(err);
                                    $('#dscPinError').show();
                                    resetSignButton();
                                }
                            });
                        } else {
                            // Show real USB Token PIN error from local signer
                            var err = (signRes && signRes.error) ? signRes.error : 'USB Token authentication failed or invalid PIN.';
                            $('#dscPinErrorMsg').text(err);
                            $('#dscPinError').show();
                            $('#dscTokenPin').focus();
                            resetSignButton();
                        }
                    })
                    .catch(function (fetchErr) {
                        // NO UNSIGNED FALLBACK! Display explicit error if signer bridge is not reachable
                        $('#dscPinErrorMsg').text('Cannot connect to AITF USB DSC Signer. Please ensure AITF-Signer.exe is running on this computer.');
                        $('#dscPinError').show();
                        resetSignButton();
                    });
                },
                error: function () {
                    $('#dscPinErrorMsg').text('Error preparing certificate ticket. Please try again.');
                    $('#dscPinError').show();
                    resetSignButton();
                }
            });
        });

        $('#dscTokenPin').keypress(function (e) {
            if (e.which === 13) {
                $('#btnExecuteDscSign').click();
            }
        });

    });
</script>

<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="customerSurvey" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                Review of feedback
            </div>  

            
            <form action="addCustomerSurvey.php" method="post">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn ">
                            <span><i class="fa  ">Review of feedback</i></span>
                        </div>
                    </div>
                    <div class="">
                        <div class="row " style="padding-top: 10px"> 
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Name of Audited Organization:</label>
                                <input type="text" class="auditOrg" id="auditOrg" name="auditOrg" placeholder="Name of Audited Organisation:" required="">
                            </div>                            
                        </div>
                        <div class="row " > 
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Date of survey:</label>
                                        <input type="text" class="surveyDate" id="surveyDate" name="surveyDate" placeholder="Date of survey:" required>
                                        <div id="surveyDateError" style="display: none; margin-top: 5px;">
                                            <small> <span id="surveyDateErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>                            
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Date(s) of Audit Conducted:</label>
                                <input type="text" class="survayAuditDates" id="survayAuditDates" name="survayAuditDates" placeholder="Date(s) of Audit Conducted:" required>
                            </div> 
                        </div>
                        <div class="row " >                                
                            <div class="col-sm-12" align="center">
                                Type of audit Imparted <br>
                                <span><input class="initial-cert" type="checkbox" name="initial-cert" value="Initial Certification(Stage-II)"> INITIAL CERTIFICATION &emsp;<input class="initial-cert" type="checkbox" name="initial-cert" value="Recertification"> RECERTIFICATION, &emsp;<input class="initial-cert" type="checkbox" name="initial-cert" value="Extension"> EXTENSION</span>                          
                                <br><span>[ Special Audit <input class="initial-cert" type="checkbox" name="initial-cert" value="Scope"> Scope <input class="initial-cert" type="checkbox" name="initial-cert" value="Site"> Site] OR <input class="initial-cert" type="checkbox" name="initial-cert" value="Surveillance"> SURVEILLANCE </span>  
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn ">
                            <span><i class="fa  ">1. Rating of Auditing Quality</i></span>
                        </div>
                    </div>



                    <div class="">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-8 form-group float-label-control">
                                <label>Overall rating of Auditing Quality (A)-</label>
                                <input type="number" min="1" class="auditingQuality" id="auditingQuality" name="auditingQuality" placeholder="Overall rating of Auditing Quality (A)-" required>
                            </div>
                            <!--                            <div class="col-sm-3 form-group float-label-control">
                                                            <label>in % is = </label>
                                                            <input type="number" class="auditingPercent" id="auditingPercent" name="auditingPercent" placeholder="in % is = ">
                                                        </div>-->
                            <div class="col-sm-4 form-group float-label-control">
                                <label>Result</label>
                                <input type="text" class="auditingResult" id="auditingResult" name="auditingResult" placeholder="Result">
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>If Ranking is Partially Satisfied or Dissatisfied, Recommended action to be taken:</label>
                                <input type="text" class="partiallyRanking" id="partiallyRanking" name="partiallyRanking" placeholder="If Ranking is Partially Satisfied or Dissatisfied, Recommended action to be taken:" required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>If Ranking is not Excellent , Recommended action:</label>
                                <input type="text" class="notExcellentRanking" id="notExcellentRanking" name="notExcellentRanking" placeholder="If Ranking is not Excellent , Recommended action:" required>
                            </div>
                        </div>
                    </div>  
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn ">
                            <span><i class="fa  ">2. Rating of Auditor (Individual) Out of 5 Marks.</i></span>
                        </div>
                    </div>
                    <div class="">
                        <div class="row " style="padding-top:10px ">
                            <div class="row">
                                <span> Excellent (5/5 Marks), Very Satisfactory (4/5 Marks), Satisfied (3/5 Marks), Partially Satisfied (2/5 Marks), Dissatisfied (1/5 Marks).</span>
                                <a class=" auditRatingRow pull-right" data-row-id="" data-row-count="" title="Add new cb row" style="padding: 1px 5px; "><i class="fa fa-plus-circle fa-lg " style="padding:3px"></i></a>
                            </div>
                            <div class="row ">
                                <div class="col-xs-6 " style="padding-top: 12px">
                                    <label>Lead Auditor/Auditor/Technical Expert Details</label>
                                </div>
                                <div class="col-xs-3 " style="padding-top: 12px">
                                    <label>Result</label>
                                </div>
                                <div class="col-xs-3 " style="padding-top: 12px">
                                    <label>Action Recommended</label>
                                </div>
                            </div>
                            <div class="auditorRating">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>If Ranking is Partially Satisfied or Dissatisfied, Recommended action to be taken:</label>
                                <input type="text" class="auditorRanking" id="auditorRanking" name="auditorRanking" placeholder="If Ranking is Partially Satisfied or Dissatisfied, Recommended action to be taken:" required>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn ">
                            <span><i class="fa  ">3. Rating for AITF Management Certification Limited (Including certification division staff & committees).</i></span>
                        </div>
                    </div>
                    <div class="row " style="padding-top: 10px">
                        <div class="col-sm-8 form-group float-label-control">
                            <label>Overall rating of satisfaction (B)-</label>
                            <input type="number" min="1" class="satisfactionRating" id="satisfactionRating" name="satisfactionRating" placeholder="Overall rating of satisfaction (B)-" required>
                        </div>
                        <!--                            <div class="col-sm-3 form-group float-label-control">
                                                        <label>in % is = </label>
                                                        <input type="number" class="satisfactionPercent" id="satisfactionPercent" name="satisfactionPercent" placeholder="in % is = ">
                                                    </div>-->
                        <div class="col-sm-4 form-group float-label-control">
                            <label>Result-</label>
                            <input type="text" class="satisfactionResult" id="satisfactionResult" name="satisfactionResult" placeholder="Result-">
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-sm-12 form-group float-label-control">
                            <label>If Ranking is Partially Satisfied or Dissatisfied, Recommended action to be taken:</label>
                            <input type="text" class="satisfactionRanking" id="satisfactionRanking" name="satisfactionRanking" placeholder="If Ranking is Partially Satisfied or Dissatisfied, Recommended action to be taken:" required>
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-sm-6 form-group float-label-control">
                            <label>If Ranking is not Excellent , Recommended action:</label>
                            <input type="text" class="notSatisfactionRanking" id="notSatisfactionRanking" name="notSatisfactionRanking" placeholder="If Ranking is not Excellent , Recommended action:" required>
                        </div>
                        <div class="col-xs-6 form-group float-label-control">
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>Review Date:</label>
                                    <input type="text" class="reviewDateSurvey" id="reviewDateSurvey" name="reviewDateSurvey" placeholder="Review Date:" required> 
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 ">
                                    <div id="reviewDateSurveyError" style="display: none">
                                        <small> <span id="reviewDateSurveyErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                    </div>
                                </div>
                            </div>                                
                        </div>
                    </div>
<!--                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn ">
                            <span><i class="fa  ">4. Audit Performance.</i></span>
                        </div>
                    </div>
                    <div class="row" style="padding-top: 10px">
                        <div class="col-sm-4 form-group float-label-control">
                            <label>NC</label>
                            <input type="text" class="ncval" id="ncval" name="ncval" placeholder="NC" required>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>OFI</label>
                            <input type="text" class="ofival" id="ofival" name="ofival" placeholder="OFI" required>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>O+ve</label>
                            <input type="text" class="oplus" id="oplus" name="oplus" placeholder="O+ve" required>
                        </div>
                    </div>-->
                </div>
                <input type="hidden" class="survey-service-id" name="survey-service-id" value="" >
                <input type="hidden" class="survey-customer-id" name="survey-customer-id" value="" >
                <input type="hidden" class="survay-id" name="survay-id" value="" >
                <input type="hidden" class="cs-service-id" name="cs-service-id" value="" >
                <input type="hidden" class="survey-service-type" name="survey-service-type" value="" >

                <input type="hidden" class="leadAuditorHidden" name="leadAuditorHidden" value="" >
                <input type="hidden" class="leadResultHidden" name="leadResultHidden" value="" >
                <input type="hidden" class="recommendedActionHidden" name="recommendedActionHidden" value="">
                <div class="modal-footer">
                    <button type="submit" name="addCustomerSurvey" class="btn btn-xs btn-info addCustomerSurvey">Save</button>
                    <!--<button type="submit" name="addCustomerSurveyPdf" class="btn btn-xs btn-info addCustomerSurveyPdf">Save & Generate</button>-->
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $('#customerSurvey').on('shown.bs.modal', function () {
            DateChronologyValidator.init({
                customerId: $('.survey-customer-id').val() || $('.customer_Id').val() || $('.customerId').val(),
                typedServiceId: $('.cs-service-id').val() || $('.survey-service-id').val() || $('.service_Id').val() || $('.serviceId').val(),
                serviceType: $('.survey-service-type').val(),
                fields: {
                    'survey_date': { input: '.surveyDate', errorDiv: '#surveyDateError', errorMsg: '#surveyDateErrorMsg' },
                    'feedback_review_date': { input: '.reviewDateSurvey', errorDiv: '#reviewDateSurveyError', errorMsg: '#reviewDateSurveyErrorMsg' }
                },
                submitBtn: '.addCustomerSurvey'
            });
        });

        $('.survayRevDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $(".survayRevDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('.survayIsueDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $(".survayIsueDate").keydown(function (e) {
//            e.preventDefault();
        });

//        $('.survayAuditDates').datepicker({
//            minDate: '-100Y',
//            maxDate: '+100Y',
//            changeYear: true,
//            changeMonth: true,
//            yearRange: "-20:+100",
//            dateFormat: 'dd.mm.yy',
//            autoclose: true
//        });
//        $(".survayAuditDates").keydown(function (e) {
////            e.preventDefault();
//        });

        $('.surveyDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true,
            onSelect: function() {
                $(this).trigger('change');
            }
        });
        $(".surveyDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('.reviewDateSurvey').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true,
            onSelect: function() {
                $(this).trigger('change');
            }
        });
        $(".reviewDateSurvey").keydown(function (e) {
//            e.preventDefault();
        });
    });

    function validateCustomerSurvay() {
        return false; // Handled by DateChronologyValidator
    }

    $(document).ready(function () {

        $(".addCustomerSurvey").click(function (e) {
            if (validateCustomerSurvay()) {
                e.preventDefault();
                return;
            }
        });

        //        code by Pallavi to add customer survey 

        $('.createCustomerSurvey').click(function () {
            var svctype = $(this).attr('data-svc');
            $('.survey-service-type').val(svctype);
            $('.survey-service-id').val($(this).attr('data-service'));
            $('.survey-customer-id').val($(this).attr('data-customer'));
            $('.cs-service-id').val($(this).attr('data-id'));
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    cdccustomer_ID: $(this).attr('data-customer'),
                    cdcservice_ID: $(this).attr('data-service'),
                    crsvc_id: $(this).attr('data-service1'),
                },
                success: function (data)
                {
//                    alert(data[2]['audit_dates']+" -> "+data[4]['schedule_audit_date']);
                    $('.auditOrg').val(data[0]['customer_name']);
//                    $('.survayAuditDates').val(moment(data[2]['audit_dates']).format('DD.MM.YYYY'));
                    // $('.leadAuditorCs').val(data[3]['auditor_name']);
//                     alert(data[11]['audit_dates']);
                    $('.auditorRating').empty();
                    if (data[3] && svctype == "CA") {
//                        alert("in if");
                        for (var key in data[3]) {
                            $('.auditorRating').append('<div class="row addnewauditorrow">\n\
                                                            <div class="col-xs-5 form-group float-label-control" style="padding-top: 12px">\n\
                                                                <label>Lead Auditor/Auditor/Technical Expert Details</label>\n\
                                                                <input type="text" class="leadAuditorCs" id="leadAuditorCs" name="leadAuditorCs" value="' + data[3][key]['auditor_name'] + '" >\n\
                                                            </div>\n\
                                                            <div class="col-xs-3 form-group float-label-control" style="padding-top: 12px">\n\
                                                                <label>Result</label>\n\
                                                                <select name="leadResult" class="form-control leadResult" id="leadResult" required>\n\
                                                                    <option value="">Select</option>\n\
                                                                    <option value="Excellent">Excellent</option>\n\
                                                                    <option value="Very Satisfactory">Very Satisfactory</option>\n\
                                                                    <option value="Satisfied">Satisfied</option>\n\
                                                                    <option value="Partially Satisfied">Partially Satisfied</option>\n\
                                                                    <option value="Dissatisfied">Dissatisfied </option>\n\
                                                                </select>\n\
                                                            </div>\n\
                                                            <div class="col-xs-3 form-group float-label-control" style="padding-top: 12px">\n\
                                                                <label>Action Recommended</label>\n\
                                                                <select name="recommendedAction" class="form-control recommendedAction" id="recommendedAction" >\n\
                                                                    <option value="">Select</option>\n\
                                                                    <option value="Yes">Yes</option>\n\
                                                                    <option value="No">No</option>\n\
                                                                </select>\n\
                                                            </div>\n\
                                                            <div class="col-xs-1 form-group float-label-control addnewauditorcolumn" style="padding-top: 12px">\n\
                                                                <a class="removeNewAuditor" style="float: right" title="Remove Auditors"><i class="fa fa-remove"></i></a>\n\
                                                            </div>\n\
                                                        </div>');
                        }
                    }
                    if (data[9] && svctype != "CA") {
//                        alert("in else if");
                        for (var key in data[9]) {
                            $('.auditorRating').append('<div class="row ">\n\
                                                    <div class="col-xs-6 form-group float-label-control" style="padding-top: 12px">\n\
                                                    <label>Lead Auditor/Auditor/Technical Expert Details</label>\n\
                                                    <input type="text" class="leadAuditorCs" id="leadAuditorCs" name="leadAuditorCs" value="' + data[9][key]['auditor_name'] + '" >\n\
                                                    </div>\n\
                                                    <div class="col-xs-3 form-group float-label-control" style="padding-top: 12px">\n\
                                                    <label>Result</label>\n\
                                                    <select name="leadResult" class="form-control leadResult" id="leadResult" required>\n\
                                                    <option value="">Select</option>\n\
                                                    <option value="Excellent">Excellent</option>\n\
                                                    <option value="Very Satisfactory">Very Satisfactory</option>\n\
                                                    <option value="Satisfied">Satisfied</option>\n\
                                                    <option value="Partially Satisfied">Partially Satisfied</option>\n\
                                                    <option value="Dissatisfied">Dissatisfied </option>\n\
                                                    </select>\n\
                                                    </div>\n\
                                                    <div class="col-xs-3 form-group float-label-control" style="padding-top: 12px">\n\
                                                    <label>Action Recommended</label>\n\
                                                    <select name="recommendedAction" class="form-control recommendedAction" id="recommendedAction" >\n\
                                                    <option value="">Select</option>\n\
                                                    <option value="Yes">Yes</option>\n\
                                                    <option value="No">No</option>\n\
                                                    </select>\n\
                                                    </div>\n\
                                                    </div>');
                        }
                    }
                    if (data[4]['schedule_audit_date'] == undefined && data[2]['audit_dates'] == undefined) {
//                        alert(data[8]['auditors']);
//                        var auditDate = data[11]['audit_dates'].split(', ');
//                        var auditDateLen = auditDate.length;
//                        var max = (Math.max(auditDateLen));
////                    alert(personLen);
//                        for (var key = 0; key < max; key++) {
//                            var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";
//                            $('.survayAuditDates').val(no);
//                        }
                        $('.survayAuditDates').val(data[11]['audit_dates']);
                        var auditor = data[8]['auditors'].split(', ');
                        var auditorLen = auditor.length;
                        var max = (Math.max(auditorLen));
//                        $('.leadAuditorCs').val(auditor[0]);
                    } else if (data[2]['audit_dates'] == undefined) {
//                        alert("in 2");
                        var auditDate = data[4]['schedule_audit_date'].split(', ');
                        var auditDateLen = auditDate.length;
                        var max = (Math.max(auditDateLen));
//                    alert(personLen);
                        for (var key = 0; key < max; key++) {
                            var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";
//                            alert(no);
                            $('.survayAuditDates').val(no);
                        }
//                        alert("else if "+data[4]['schedule_audit_date']);
                        $('.survayAuditDates').val(moment(data[4]['schedule_audit_date']).format('DD.MM.YYYY'));
                        var auditor = data[4]['schedule_auditors'].split(', ');
                        var auditorLen = auditor.length;
                        var max = (Math.max(auditorLen));
//                        $('.leadAuditorCs').val(auditor[0]);
                    } else {
//                        alert("in 3");
//                        var auditDate = data[2]['audit_dates'].split(', ');
//                        var auditDateLen = auditDate.length;
//                        var max = (Math.max(auditDateLen));
////                    alert(personLen);
//                        for (var key = 0; key < max; key++) {
//                            var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";
////                            alert(no);
//                            $('.survayAuditDates').val(no);
//                        }
//                        alert("else "+data[2]['audit_dates']);
//                        $('.survayAuditDates').val(data[2]['audit_dates']);
//                        $('.leadAuditorCs').val(data[3][0]['auditor_name']);
                        var auditorLen = data[3].length;
                        var max = (Math.max(auditorLen));

                        $('.survayAuditDates').val(data[2]['audit_dates']);
                    }
                    $('input[type="text"]').trigger('change');
                    $('input[type="number"]').trigger('change');
                    $('#customerSurvey').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.editCustomerSurvey').click(function () {
            var svctype = $(this).attr('data-svc');
            $('.survey-service-type').val(svctype);
            $('.cs-service-id').val($(this).attr('data-id'));
//            alert($(this).attr('data-row-id'));
            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    survay_id: $(this).attr('data-row-id'),
                    survay_service: $(this).attr('data-service'),
//                    survay_customer: $(this).attr('data-customer'),
                },
                success: function (data)
                {
//                    alert(data[0]["initial_cert"]);
                    $('.auditorRating').empty();

                    $('.survay-id').val(data[0]['survey_id']);
                    $('.survey-service-id').val(data[0]['ca_serviceid']);
                    $('.survey-customer-id').val(data[0]['customer_ca']);
                    $('.auditOrg').val(data[0]['auditer_org']);
                    if (data[0]['review_date'] == "" || data[0]['review_date'] == null)
                        $review_date = "";
                    else
                        $review_date = moment(data[0]['review_date']).format('DD.MM.YYYY');
                    $('.reviewDateSurvey').val($review_date);

                    if (data[0]['survey_date'] == "" || data[0]['survey_date'] == null)
                        $survey_date = "";
                    else
                        $survey_date = moment(data[0]['survey_date']).format('DD.MM.YYYY');
                    $('.surveyDate').val($survey_date);

                    if (data[0]['audit_conducted_date'] == "" || data[0]['audit_conducted_date'] == null)
                        $audit_conducted_date = "";
                    else
                        $audit_conducted_date = data[0]['audit_conducted_date'];

                    $('.survayAuditDates').val($audit_conducted_date);
                    $('.auditingQuality').val(data[0]['auditing_quality']);
                    $('.auditingResult').val(data[0]['auditing_result']);
                    $('.partiallyRanking').val(data[0]['audit_ranking_good']);
                    $('.notExcellentRanking').val(data[0]['audit_ranking_bad']);
                    $('.auditorRanking').val(data[0]['individual_ranking']);
                    $('.satisfactionRating').val(data[0]['mgmt_satisfaction']);
                    $('.satisfactionResult').val(data[0]['mgmt_result']);
                    $('.satisfactionRanking').val(data[0]['mgmt_ranking_good']);
                    $('.notSatisfactionRanking').val(data[0]['mgmt_ranking_bad']);
                    $('.initial-cert[value = "' + data[0]["initial_cert"] + '"]').prop('checked', true);
//                    $(".ncval").val(data[0]['ncval']);
//                    $(".ofival").val(data[0]['ofival']);
//                    $(".oplus").val(data[0]['oplusval']);
//                    alert(data[1]);
                    for (var key in data[1]) {
                        $('.auditorRating').append('<div class="row addnewauditorrow">\n\
                                                    <div class="col-xs-5 form-group float-label-control" style="padding-top: 12px">\n\
                                                    <label>Lead Auditor/Auditor/Technical Expert Details</label>\n\
                                                    <input type="text" class="leadAuditorCs" id="leadAuditorCs" name="leadAuditorCs" value="' + data[1][key]['lead_auditor'] + '" placeholder="Lead Auditor/Auditor/Technical Expert Details">\n\
                                                    </div>\n\
                                                    <div class="col-xs-3 form-group float-label-control" style="padding-top: 12px">\n\
                                                    <label>Result</label>\n\
                                                    <select name="leadResult" class="form-control leadResult" id="leadResult" required>\n\
                                                    <option value="' + data[1][key]['result'] + '">' + data[1][key]['result'] + '</option>\n\
                                                    <option value="Excellent">Excellent</option>\n\
                                                    <option value="Very Satisfactory">Very Satisfactory</option>\n\
                                                    <option value="Satisfied">Satisfied</option>\n\
                                                    <option value="Partially Satisfied">Partially Satisfied</option>\n\
                                                    <option value="Dissatisfied">Dissatisfied </option>\n\
                                                    </select>\n\
                                                    </div>\n\
                                                    <div class="col-xs-3 form-group float-label-control" style="padding-top: 12px">\n\
                                                    <label>Action Recommended</label>\n\
                                                    <select name="recommendedAction" class="form-control recommendedAction" id="recommendedAction" >\n\
                                                    <option value="' + data[1][key]['action'] + '">' + data[1][key]['action'] + '</option>\n\
                                                    <option value="Yes">Yes</option>\n\
                                                    <option value="No">No</option>\n\
                                                    </select>\n\
                                                    </div>\n\
                                                    <div class="col-xs-1 form-group float-label-control addnewauditorcolumn" style="padding-top: 12px">\n\
                                                    <a class="removeNewAuditor" style="float: right" title="Remove Auditors"><i class="fa fa-remove"></i></a>\n\
                                                    </div>\n\
                                                    </div>');
                    }
                    $('input[type="text"]').trigger('change');
                    $('input[type="number"]').trigger('change');
//                    $('textarea').trigger('change');
//                    $('.addCustomerSurveyPdf').hide();

                    $('#customerSurvey').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.initial-cert').change(function () {
            if ((this).checked) {
                $('.initial-cert').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        //autocomlete for Auditor Name
//        $(document).on('keyup.autocomplete', ".leadAuditorCs", function () {
//            $getVal = $(this).val();
//            $(this).autocomplete({
//                source: function (request, response) {
//                    //alert($('.leadAuditorCs').val());
//                    $.ajax({
//                        url: "search.php",
//                        dataType: "json",
//                        type: 'GET',
//                        data: {
//                            auditor: $('.leadAuditorCs').val(),
//                        },
//                        success: function (data) {
//                            //                                                    alert(data);
//                            response(data);
//                        },
//                        error: function (data, status, err) {
//                            //                        alert(status);
//                        }
//                    });
//                },
//                minLength: 1,
//                select: function (event, ui) {
//                },
//                open: function () {
//                    $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
//                },
//                close: function () {
//                    $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
//                }
//            });
//        });


        $('.auditRatingRow').click(function () {

            $('.auditorRating').append('<div class="row addnewauditorrow">\n\
                                <div class="col-xs-5 form-group float-label-control" style="padding-top: 12px">\n\
                                    <label>Lead Auditor/Auditor/Technical Expert Details</label>\n\
                                    <input type="text" class="leadAuditorCs" id="leadAuditorCs" name="leadAuditorCs" placeholder="Lead Auditor/Auditor/Technical Expert Details">\n\
                                </div>\n\
                                <div class="col-xs-3 form-group float-label-control" style="padding-top: 12px">\n\
                                    <label>Result</label>\n\
                                    <select name="leadResult" class="form-control leadResult" id="leadResult" required>\n\
                                    <option value="">Select</option>\n\
                                    <option value="Excellent">Excellent</option>\n\
                                    <option value="Very Satisfactory">Very Satisfactory</option>\n\
                                    <option value="Satisfied">Satisfied</option>\n\
                                    <option value="Partially Satisfied">Partially Satisfied</option>\n\
                                    <option value="Dissatisfied">Dissatisfied</option>\n\
                                    </select>\n\
                                </div>\n\
                                <div class="col-xs-3 form-group float-label-control" style="padding-top: 12px">\n\
                                    <label>Action Recommended</label>\n\
                                    <select name="recommendedAction" class="form-control recommendedAction" id="recommendedAction">\n\
                                        <option value="">Select</option>\n\
                                        <option value="Yes">Yes</option>\n\
                                        <option value="No">No</option>\n\
                                    </select> \n\
                                </div>\n\
                                <div class="col-xs-1 form-group float-label-control addnewauditorcolumn" style="padding-top: 12px">\n\
                                    <a class="removeNewAuditor" style="float: right" title="Remove Auditors"><i class="fa fa-remove"></i></a>\n\
                                </div>\n\
                            </div>');
        });
        
        $('body').on("click", ".removeNewAuditor", function () {
            $(this).parent("div.addnewauditorcolumn").parent('div.addnewauditorrow').remove();
        });

        $('.addCustomerSurvey').click(function () {
            var leadAuditorArray = [];
            $('.leadAuditorCs').each(function () {
                leadAuditorArray.push($(this).val());
            });
            $('.leadAuditorHidden').val(leadAuditorArray.join("~"));

            var leadResultArray = [];
            $('.leadResult').each(function () {
                leadResultArray.push($(this).val());
            });
            $('.leadResultHidden').val(leadResultArray.join("~"));

            var recommendedActionArray = [];
            $('.recommendedAction').each(function () {
                recommendedActionArray.push($(this).val());
            });
            $('.recommendedActionHidden').val(recommendedActionArray.join("~"));
//            alert($('.leadAuditorHidden').val());
        });

        $('.addCustomerSurveyPdf').click(function () {
            var leadAuditorArray = [];
            $('.leadAuditor').each(function () {
                leadAuditorArray.push($(this).val());
            });
            $('.leadAuditorHidden').val(leadAuditorArray.join("~"));

            var leadResultArray = [];
            $('.leadResult').each(function () {
                leadResultArray.push($(this).val());
            });
            $('.leadResultHidden').val(leadResultArray.join("~"));

            var recommendedActionArray = [];
            $('.recommendedAction').each(function () {
                recommendedActionArray.push($(this).val());
            });
            $('.recommendedActionHidden').val(recommendedActionArray.join("~"));
        });

    });

</script>

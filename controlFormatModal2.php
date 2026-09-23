<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="controlFormat2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                CONTROL FORMAT -2
            </div>  
            <form action="addControlFormat2.php" method="post">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formControlFormat2">
                            <span><i class="fa  ">CONFLICT OF INTERESTS& IMPARTIALITY</i></span>
                        </div>
                    </div>
                    <div class="formControlFormat2Div">
                        <div class="row " style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Name of client:</label>
                                <input type="text" class="controlFormat2ClientName" id="controlFormat2ClientName" name="controlFormat2ClientName" placeholder="Name of client:" required>
                            </div>
                            <div class="col-sm-6">
                                <div id="certificateNumbersContainer2">
                                    <!-- Certificate number fields will be dynamically added here -->
                                </div>
                            </div>
                        </div>
                        <div class="row " >                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Address/Location:</label>
                                <input type="text" class="controlFormat2Address" id="controlFormat2Address" name="controlFormat2Address" placeholder="Address/Location:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Date of CDC:</label>
                                        <input type="text" class="controlFormat2CertDate" id="controlFormat2CertDate" name="controlFormat2CertDate" placeholder="Date of CDC:" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="controlFormat2CertDateError" style="display: none">
                                            <small> <span id="controlFormat2CertDateErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>                                
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formControlFormat2Qstn">
                            <span><i class="fa  ">CONFLICT OF INTERESTS& IMPARTIALITY</i></span>
                        </div>
                    </div>
                    <div class="formControlFormat2QstnDiv" style="display: none">
                        <div class="row" style="text-align: center">
                            <span>CONTROL POINT-2 </span><br>
                            <span>Certification Decision Committee (CDC)</span>
                        </div> 
                        <div class="editControl2">
                            <div class="row" >
                                <div class="col-sm-12"> 
                                    <strong>CONSULTANCY</strong>
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>1. Was there any evidence or information to indicate that AITF-MCL has offered or provided consultancy services to the client?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion2" name="hiddenQuestion2" value="Was there any evidence or information to indicate that AITF-MCL has offered or provided consultancy services to the client?">
                                <input type="hidden" class="hiddensrno2" name="hiddensrno2" value="1">
                                <input type="hidden" class="hiddentitle2" name="hiddentitle2" value="CONSULTANCY">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat2Comment" class="form-control controlFormat2Comment" id="controlFormat2Comment" required>
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat2Risk" class="form-control controlFormat2Risk" id="controlFormat2Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat2RiskDegree" class="form-control controlFormat2RiskDegree" id="controlFormat2RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat2Controls" id="controlFormat2Controls" name="controlFormat2Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12"> 
                                    <strong>OTHER SERVICES</strong>
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>2. Did AITF offer any services other than certification services to the client?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion2" name="hiddenQuestion2" value="Did AITF offer any services other than certification services to the client?">
                                <input type="hidden" class="hiddensrno2" name="hiddensrno2" value="1">
                                <input type="hidden" class="hiddentitle2" name="hiddentitle2" value="OTHER SERVICES">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat2Comment" class="form-control controlFormat2Comment" id="controlFormat2Comment" required>
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat2Risk" class="form-control controlFormat2Risk" id="controlFormat2Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat2RiskDegree" class="form-control controlFormat2RiskDegree" id="controlFormat2RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat2Controls" id="controlFormat2Controls" name="controlFormat2Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12"> 
                                    <strong>AUDITOR ALLOCATION</strong>
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>3. Has the auditor signed AITF's policy statement on Confidentiality & Conflict of Interest?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion2" name="hiddenQuestion2" value="Has the auditor signed AITF's policy statement on Confidentiality & Conflict of Interest?">
                                <input type="hidden" class="hiddensrno2" name="hiddensrno2" value="1">
                                <input type="hidden" class="hiddentitle2" name="hiddentitle2" value="AUDITOR ALLOCATION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat2Comment" class="form-control controlFormat2Comment" id="controlFormat2Comment"  required>
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat2Risk" class="form-control controlFormat2Risk" id="controlFormat2Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat2RiskDegree" class="form-control controlFormat2RiskDegree" id="controlFormat2RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat2Controls" id="controlFormat2Controls" name="controlFormat2Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>4.Has the auditor signed AITF's Confidentiality & Impartiality Undertaking F 11.03</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion2" name="hiddenQuestion2" value="Has the auditor signed AITF's Confidentiality & Impartiality Undertaking F 11.03">
                                <input type="hidden" class="hiddensrno2" name="hiddensrno2" value="2">
                                <input type="hidden" class="hiddentitle2" name="hiddentitle2" value="AUDITOR SELECTION/ALLOCATION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat2Comment" class="form-control controlFormat2Comment" id="controlFormat2Comment" required>
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat2Risk" class="form-control controlFormat2Risk" id="controlFormat2Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat2RiskDegree" class="form-control controlFormat2RiskDegree" id="controlFormat2RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat2Controls" id="controlFormat2Controls" name="controlFormat2Controls" placeholder="Controls" >
                                </div>
                            </div>

                            <div class="row" >
                                <div class="col-sm-12"> 
                                    <strong>CERTIFICATION DECISION</strong>
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>5. Did the group or individual that took the decision on granting certification understand the applicable standard and certification requirements, and demonstrated competence to evaluate the audit processes and related recommendations of the audit team?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion2" name="hiddenQuestion2" value="Did the group or individual that took the decision on granting certification understand the applicable standard and certification requirements, and demonstrated competence to evaluate the audit processes and related recommendations of the audit team?">
                                <input type="hidden" class="hiddensrno2" name="hiddensrno2" value="1">
                                <input type="hidden" class="hiddentitle2" name="hiddentitle2" value="CERTIFICATION DECISION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat2Comment" class="form-control controlFormat2Comment" id="controlFormat2Comment" required>
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat2Risk" class="form-control controlFormat2Risk" id="controlFormat2Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat2RiskDegree" class="form-control controlFormat2RiskDegree" id="controlFormat2RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat2Controls" id="controlFormat2Controls" name="controlFormat2Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>6. Has there been any inference to personnel, either internal or external, or committees, who could influence the certification activities, did not act impartially and/or allowed commercial, financial or other pressures to compromise impartiality?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion2" name="hiddenQuestion2" value="Has there been any inference to personnel, either internal or external, or committees, who could influence the certification activities, did not act impartially and/or allowed commercial, financial or other pressures to compromise impartiality?">
                                <input type="hidden" class="hiddensrno2" name="hiddensrno2" value="2">
                                <input type="hidden" class="hiddentitle2" name="hiddentitle2" value="CERTIFICATION DECISION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat2Comment" class="form-control controlFormat2Comment" id="controlFormat2Comment" required>
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat2Risk" class="form-control controlFormat2Risk" id="controlFormat2Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat2RiskDegree" class="form-control controlFormat2RiskDegree" id="controlFormat2RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat2Controls" id="controlFormat2Controls" name="controlFormat2Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>7.Was/is there any link between the certified client and any member of the Certification decision committee?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion2" name="hiddenQuestion2" value="Was/is there any link between the certified client and any member of the Certification decision committee?">
                                <input type="hidden" class="hiddensrno2" name="hiddensrno2" value="3">
                                <input type="hidden" class="hiddentitle2" name="hiddentitle2" value="CERTIFICATION DECISION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat2Comment" class="form-control controlFormat2Comment" id="controlFormat2Comment" required>
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat2Risk" class="form-control controlFormat2Risk" id="controlFormat2Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>                                
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat2RiskDegree" class="form-control controlFormat2RiskDegree" id="controlFormat2RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat2Controls" id="controlFormat2Controls" name="controlFormat2Controls" placeholder="Controls" >
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Remarks:</label>
                                <textarea rows="4"  class="cf2remark" id="cf2remark" name="cf2remark" placeholder="Remarks:"></textarea>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-xs-12 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Name:</label>
                                        <input type="text" class="reviewer" id="reviewer" name="reviewer" placeholder="Name:" required> 
                                    </div>
                                    <div class="col-sm-6 form-group float-label-control">
                                        <label>Review Date:</label>
                                        <input type="text" class="reviewDateControl2" id="reviewDateControl2" name="reviewDateControl2" placeholder="Review Date:" required> 
                                        <div class="row">
                                            <div class="col-sm-12 ">
                                                <div id="reviewDateControl2Error" style="display: none">
                                                    <small> <span id="reviewDateControl2ErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>                                
                            </div>
                        </div>     
                    </div>
                </div>
                <input type="hidden" class="control-format2-service" name="control-format2-service" value="">
                <input type="hidden" class="control-format2-customer" name="control-format2-customer" value="">
                <input type="hidden" class="control-format2-id" name="control-format2-id" value="">
                <input type="hidden" class="control_format2_id" name="control_format2_id" value="">
                <input type="hidden" class="control_format2_idhideden" name="control_format2_idhideden" value="">
                <input type="hidden" class="risk-question-hidden2" name="risk-question-hidden2" value="">
                <input type="hidden" class="comment-hidden2" name="comment-hidden2" value="">
                <input type="hidden" class="risk-hidden2" name="risk-hidden2" value="">
                <input type="hidden" class="risk-degree-hidden2" name="risk-degree-hidden2" value="">
                <input type="hidden" class="controls-hidden2" name="controls-hidden2" value=""> 
                <input type="hidden" class="srno-hidden2" name="srno-hidden2" value=""> 
                <input type="hidden" class="title-hidden2" name="title-hidden2" value="">
                <input type="hidden" class="cf-service-id" name="cf-service-id" value="">
                <div class="modal-footer">
                    <button type="submit" name="addControlFormat2" class="btn btn-xs btn-info addControlFormat2">Save</button>
                    <!--<button type="submit" name="pdfControlFormat2" class="btn btn-xs btn-info pdfControlFormat2">Save & Generate</button>--> 
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .dispClass{
        position: absolute;
        font-weight: normal;
        top: -1.0em;
        left: 1.10em;
        color: black;
        z-index: -1;
        font-size: 0.85em;
        -moz-animation: float-labels 300ms none ease-out;
        -webkit-animation: float-labels 300ms none ease-out;
        -o-animation: float-labels 300ms none ease-out;
        -ms-animation: float-labels 300ms none ease-out;
        -khtml-animation: float-labels 300ms none ease-out;
        animation: float-labels 300ms none ease-out;
        /* There is a bug sometimes pausing the animation. This avoids that.*/
        animation-play-state: running !important;
        -webkit-animation-play-state: running !important;
        /*top: 0.1em;*/
        /*font-size: 1.5em;*/
        animation: none;
        -webkit-animation: none;
        z-index: 1;
        color: black;
    }
    .controlFormat2Comment{
        display: block;
        width: 100%;
        padding: 0.1em 0em 1px 0em;
        border: none;
        border-radius: 0px;
        border-bottom: 1px solid black;
        outline: none;
        margin: 0px;
        background: none;    
    }
    .controlFormat2Risk{
        display: block;
        width: 100%;
        padding: 0.1em 0em 1px 0em;
        border: none;
        border-radius: 0px;
        border-bottom: 1px solid black;
        outline: none;
        margin: 0px;
        background: none; 
    }
    .controlFormat2RiskDegree{
        display: block;
        width: 100%;
        padding: 0.1em 0em 1px 0em;
        border: none;
        border-radius: 0px;
        border-bottom: 1px solid black;
        outline: none;
        margin: 0px;
        background: none;
    }
    #controlFormat2Controls{
        display: block;
        width: 100%;
        padding: 0.1em 0em 1px 0em;
        border: none;
        border-radius: 0px;
        border-bottom: 1px solid black;
        outline: none;
        margin: 0px;
        background: none;
    }
</style>
<script type="text/javascript">

    $(function () {
        $('#controlFormat2CertDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#controlFormat2CertDate").keydown(function (e) {
//            e.preventDefault();
        });
        
        $('#reviewDateControl2').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#reviewDateControl2").keydown(function (e) {
//            e.preventDefault();
        });
    });

    function validateCF2() {
        $isError = false;
        if ($(".controlFormat2CertDate").val() == "") {
            $('#controlFormat2CertDateErrorMsg').text("Date can not be empty");
            $('#controlFormat2CertDateError').show();
            $('#controlFormat2CertDate').addClass('inputHighlight');
            $isError = true;
        } else if ($(".controlFormat2CertDate").val() != "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    controlFormat2CertDate: $('.controlFormat2CertDate').val(),
                    cf2Service_ID: $('.control-format2-service').val(),
                },
                async: false,
                success: function (data) {
//    	                alert(data);
                    if (data == 1) {
                        $('#controlFormat2CertDateErrorMsg').text("Date should be greater than registration date of certificate print");
                        $('#controlFormat2CertDateError').show();
                        $('#controlFormat2CertDate').addClass('inputHighlight');
                        $isError = true;
                    } else {
                        $('#controlFormat2CertDateError').hide();
                        $('#controlFormat2CertDate').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#controlFormat2CertDateError').hide();
            $('#controlFormat2CertDate').removeClass('inputHighlight');
        }
        if ($(".reviewDateControl2").val() == "") {
            $('#reviewDateControl2ErrorMsg').text("Date can not be empty");
            $('#reviewDateControl2Error').show();
            $('#reviewDateControl2').addClass('inputHighlight');
            $isError = true;
        } else if (($(".reviewDateControl2").val() != "") && (moment($('.controlFormat2CertDate').val(), "DD/MM/YYYY") > (moment($('.reviewDateControl2').val(), "DD/MM/YYYY")))) {
            $('#reviewDateControl2ErrorMsg').text("Date should be greater than certification date");
            $('#reviewDateControl2Error').show();
            $('#reviewDateControl2').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#reviewDateControl2Error').hide();
            $('#reviewDateControl2').removeClass('inputHighlight');
        }
        return $isError;
    }

    $(document).ready(function () {

        $(document).on('click', '.createControlFormat2', function () {
            var $btn = $(this);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            var restoreBtn = function() {
                $btn.prop('disabled', false).html(originalHtml);
            };

            var serviceId = $(this).attr('data-service');
            var customerId = $(this).attr('data-customer');
            var id = $(this).attr('data-id');

            $('.control-format2-service').val(serviceId);
            $('.control-format2-customer').val(customerId);
            $('.cf-service-id').val(id);

            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    cfcustomer_id: customerId,
                    cfservice_id: serviceId,
                },
                success: function (data)
                {
                    try {
                        if (!data || !data[0]) {
                            restoreBtn();
                            alert("Data not found");
                            return;
                        }
                        $('.controlFormat2ClientName').val(data[0]['customer_name'] || '');
                        $('.controlFormat2Address').val(data[0]['address'] || '');
                        $('.controlFormat2RefNo').val(data[0]['certification_no'] || '');
                        var certDate = (data[2] && data[2]['revDatecdc']) ? moment(data[2]['revDatecdc']).format('DD.MM.YYYY') : '';
                        $('.controlFormat2CertDate').val(certDate);
                        $('input[type="text"]').trigger('change');

                        $('#controlFormat2').modal('show');
                    } catch (e) {
                        console.error("Error in createControlFormat2:", e);
                    } finally {
                        restoreBtn();
                    }
                },
                error: function (data, status, err)
                {
                    restoreBtn();
                    alert(status);
                }
            });
        });

        $(document).on('click', '.editControlFormat2', function () {
            var $btn = $(this);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            var restoreBtn = function() {
                $btn.prop('disabled', false).html(originalHtml);
            };

            var notificationId = $(this).attr('data-notification');
            var serviceId = $(this).attr('data-service');

            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    control2_id: notificationId,
                    service2_Id: serviceId
                },
                success: function (data)
                {
                    try {
                        if (!data || !data[0]) {
                            restoreBtn();
                            alert("Data not found");
                            return;
                        }
                        $('.editControl2').empty();
                        $('.controlFormat2ClientName').val(data[0]['control2_client'] || '');
                        $('.controlFormat2RefNo').val(data[0]['control2_ref_no'] || '');
                        $('.controlFormat2Address').val(data[0]['control2_address'] || '');
                        $('.cf2remark').val(data[0]['remarks'] || '');
                        $('.reviewer').val(data[0]['username'] || '');

                        var $review_date = "";
                        if (data[0]['review_date'])
                            $review_date = moment(data[0]['review_date']).format('DD.MM.YYYY');
                        $('.reviewDateControl2').val($review_date);

                        var $control2_date = "";
                        if (data[0]['control2_date'])
                            $control2_date = moment(data[0]['control2_date']).format('DD.MM.YYYY');
                        $('.controlFormat2CertDate').val($control2_date);
                        $('.control-format2-service').val(data[0]['caservice_id'] || '');
                        $('.control-format2-customer').val(data[0]['customer_id'] || '');
                        $('.control-format2-id').val(data[0]['control2_id'] || '');

                        if (data[1]) {
                            for (var key in data[1]) {
                                var srnum = data[1][key]['control2_sr'];
                                if (srnum != null && srnum != "" && srnum == 1) {
                                    $('.editControl2').append('<div class="row" ><div class="col-sm-12"><strong>' + (data[1][key]['control2_title'] || '') + '</strong></div></div>');
                                }
                                $('.editControl2').append('<div class="row" >\n\
                                                        <div class="col-sm-12">\n\
                                                        <span>' + (data[1][key]['control2_question'] || '') + '</span>\n\
                                                        </div>\n\
                                                        <input type="hidden" class="hiddenQuestion2" name="hiddenQuestion2" value="' + (data[1][key]['control2_question'] || '') + '">\n\
                                                        <input type="hidden" class="hiddensrno2" name="hiddensrno2" value="' + (data[1][key]['control2_sr'] || '') + '">\n\
                                                        <input type="hidden" class="hiddentitle2" name="hiddentitle2" value="' + (data[1][key]['control2_title'] || '') + '">\n\
                                                        <input type="hidden" class="controlidhidden" name="controlidhidden" value="' + (data[1][key]['control_format2_id'] || '') + '">\n\
                                                        </div>\n\
                                                        <div class="row" style="padding-top: 12px" >\n\
                                                        <div class="col-xs-3 form-group">\n\
                                                        <label class="dispClass">Comments</label>\n\
                                                        <select name="controlFormat2Comment" class="form-control controlFormat2Comment" id="controlFormat2Comment" >\n\
                                                        <option value="' + (data[1][key]['comments2'] || '') + '">' + (data[1][key]['comments2'] || '') + '</option>\n\
                                                        <option value="Yes">Yes</option>\n\
                                                        <option value="No">No</option>\n\
                                                        </select>\n\
                                                        </div>\n\
                                                        <div class="col-xs-3 form-group">\n\
                                                        <label class="dispClass">Risk, if any</label>\n\
                                                        <select name="controlFormat2Risk" class="form-control controlFormat2Risk" id="controlFormat2Risk" >\n\
                                                        <option value="' + (data[1][key]['risks2'] || '') + '">' + (data[1][key]['risks2'] || '') + '</option>\n\
                                                        <option value="Yes">Yes</option>\n\
                                                        <option value="No">No</option>\n\
                                                        </select>\n\
                                                        </div>\n\
                                                        <div class="col-sm-3 form-group ">\n\
                                                        <label class="dispClass">Degree of Risk</label>\n\
                                                        <select name="controlFormat2RiskDegree" class="form-control controlFormat2RiskDegree" id="controlFormat2RiskDegree" >\n\
                                                        <option value="' + (data[1][key]['risk_degree2'] || '') + '">' + (data[1][key]['risk_degree2'] || '') + '</option>\n\
                                                        <option value="High">High</option>\n\
                                                        <option value="Low">Low</option>\n\
                                                        </select>\n\
                                                        </div>\n\
                                                        <div class="col-sm-3 form-group ">\n\
                                                        <label class="dispClass">Controls</label>\n\
                                                        <input type="text" class="controlFormat2Controls" id="controlFormat2Controls" name="controlFormat2Controls" value="' + (data[1][key]['controls2'] || '') + '" placeholder="Controls">\n\
                                                        </div>\n\
                                                        </div>');
                            }
                        }
                        $('input[type="text"]').trigger('change');
                        $('.cf2remark').trigger('change');
                        $('.pdfControlFormat2').hide();
                        $('#controlFormat2').modal('show');
                    } catch (e) {
                        console.error("Error in editControlFormat2:", e);
                    } finally {
                        restoreBtn();
                    }
                },
                error: function (data, status, err)
                {
                    restoreBtn();
                    alert(status);
                }
            });
        });

        $('.formControlFormat2').click(function () {
            $('.formControlFormat2Div').slideToggle('slow');
        });

        $('.formControlFormat2Qstn').click(function () {
            $('.formControlFormat2QstnDiv').slideToggle('slow');
        });


        $('.addControlFormat2').click(function (e) {
            if (validateCF2()) {
                e.preventDefault();
                return;
            }

            var hiddenQuestionArray = [];
            $('.hiddenQuestion2').each(function () {
                hiddenQuestionArray.push($(this).val());
            });
            $('.risk-question-hidden2').val(hiddenQuestionArray.join("~"));

            var controlFormat2CommentArray = [];
            $('.controlFormat2Comment').each(function () {
                controlFormat2CommentArray.push($(this).val());
            });
            $('.comment-hidden2').val(controlFormat2CommentArray.join("~"));

            var controlFormat2RiskArray = [];
            $('.controlFormat2Risk').each(function () {
                controlFormat2RiskArray.push($(this).val());
            });
            $('.risk-hidden2').val(controlFormat2RiskArray.join("~"));

            var controlFormat2RiskDegreeArray = [];
            $('.controlFormat2RiskDegree').each(function () {
                controlFormat2RiskDegreeArray.push($(this).val());
            });
            $('.risk-degree-hidden2').val(controlFormat2RiskDegreeArray.join("~"));

            var controlFormat2ControlsArray = [];
            $('.controlFormat2Controls').each(function () {
                controlFormat2ControlsArray.push($(this).val());
            });
            $('.controls-hidden2').val(controlFormat2ControlsArray.join("~"));

            var hiddensrnoArray = [];
            $('.hiddensrno2').each(function () {
                hiddensrnoArray.push($(this).val());
            });
            $('.srno-hidden2').val(hiddensrnoArray.join("~"));

            var hiddentitleArray = [];
            $('.hiddentitle2').each(function () {
                hiddentitleArray.push($(this).val());
            });
            $('.title-hidden2').val(hiddentitleArray.join("~"));

            var controlidhiddenArray = [];
            $('.controlidhidden').each(function () {
                controlidhiddenArray.push($(this).val());
            });
            $('.control_format2_idhideden').val(controlidhiddenArray.join("~"));
//            alert(controlidhiddenArray);
        });

        $('.pdfControlFormat2').click(function () {

            var hiddenQuestionArray = [];
            $('.hiddenQuestion2').each(function () {
                hiddenQuestionArray.push($(this).val());
            });
            $('.risk-question-hidden2').val(hiddenQuestionArray.join("~"));

            var controlFormat2CommentArray = [];
            $('.controlFormat2Comment').each(function () {
                controlFormat2CommentArray.push($(this).val());
            });
            $('.comment-hidden2').val(controlFormat2CommentArray.join("~"));

            var controlFormat2RiskArray = [];
            $('.controlFormat2Risk').each(function () {
                controlFormat2RiskArray.push($(this).val());
            });
            $('.risk-hidden2').val(controlFormat2RiskArray.join("~"));

            var controlFormat2RiskDegreeArray = [];
            $('.controlFormat2RiskDegree').each(function () {
                controlFormat2RiskDegreeArray.push($(this).val());
            });
            $('.risk-degree-hidden2').val(controlFormat2RiskDegreeArray.join("~"));

            var controlFormat2ControlsArray = [];
            $('.controlFormat2Controls').each(function () {
                controlFormat2ControlsArray.push($(this).val());
            });
            $('.controls-hidden2').val(controlFormat2ControlsArray.join("~"));

            var hiddensrnoArray = [];
            $('.hiddensrno2').each(function () {
                hiddensrnoArray.push($(this).val());
            });
            $('.srno-hidden2').val(hiddensrnoArray.join("~"));

            var hiddentitleArray = [];
            $('.hiddentitle2').each(function () {
                hiddentitleArray.push($(this).val());
            });
            $('.title-hidden2').val(hiddentitleArray.join("~"));
        });


    });

</script>

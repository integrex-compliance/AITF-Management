<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 *
 */
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$allAuditors = "";
$sql = "SELECT auditor_name FROM auditors";
$statement = $db->prepare($sql);
$statement->execute();
$auditors = $statement->fetchAll();

//  Standards 
$standards = "SELECT * FROM standards";
$statement = $db->prepare($standards);
$statement->execute();
$standardsList = $statement->fetchAll();

foreach ($auditors as $name) {
    $allAuditors .= $name['auditor_name'] . ",";
}
?>

<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="assessmentModal" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                ASSESSMENT PLANNING
            </div>
            <form action="addAssessmentPlanning.php" method="POST">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formCdc">
                            <span><i class="fa ">Assessment Planning</i></span>
                        </div>
                    </div>
                    <div class="CdC">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Applicant / Client Name</label> 
                                <input type="text" class="appClient" id="appClient" name="appClient" placeholder="Applicant / Client Name" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label> Agreement No.:</label>
                                <input type="text" class="refNo" id="refNo" name="refNo" placeholder="Agreement No.:" required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Site (s):</label>
                                <textarea rows="4" class="assessmentLocation" id="assessmentLocation" name="assessmentLocation" placeholder="Site(s):" required></textarea>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row ">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Business /Operations (Scope of Certification)</label> 
                                        <input type="text" class="assessmentBusiness" id="assessmentBusiness" name="assessmentBusiness" placeholder="Business /Operations (Scope of Certification)" required>
                                    </div>
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Certification required for standard</label> 
                                <textarea name="certRequirement" class="form-control form-group certRequirement" id="certRequirement" placeholder="Assessment Standard" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Division</label>
                                <input type="text" name="assessmentPlaningIafCode" class="form-control assessmentPlaningIafCode" placeholder="Division">
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Group</label>
                                <input type="text" name="assessmentNace" class="form-control assessmentNace" placeholder="Group">                                                               
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Class</label>
                                <input type="text" name="assessmentNaceClass" class="form-control assessmentNaceClass" placeholder="Class">                                                               
                            </div>


                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Are the certification requirements clearly defined, documented & understood by applicant? Yes / No</label> 
                                <input type="text" class="assessmentCertReq" id="assessmentCertReq" name="assessmentCertReq" placeholder="Are the certification requirements clearly defined, documented & understood by applicant? Yes / No" required>
                            </div>
                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Any difference in applicants understanding pending resolution?  Yes / No</label> 
                                <input type="text" class="assessmentAppStandard" id="assessmentAppStandard" name="assessmentAppStandard" placeholder="Any difference in applicants understanding pending resolution?  Yes / No" required>
                            </div>
                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Special aspects, such as, language, if any</label> 
                                <input type="text" class="assessmentSpecialAspects" id="assessmentSpecialAspects" name="assessmentSpecialAspects" placeholder="Special aspects, such as, language, if any" required>
                            </div>
                        </div>

                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formCdc">
                            <span><i class="fa ">Applicant certified</i></span>
                        </div>
                    </div>
                    <div class="CdC">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-xs-10  ">
                                <span>Is applicant certified previously (Yes/ No) : _____ If Yes provide the information</span>
                            </div>
                            <div class="col-sm-2 form-group float-label-control"><label></label> 
                                <select name="appifyes" class="form-control appifyes" id="appifyes" required>
                                    <option value="">Select</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Certification Body</label> 
                                <input type="text" class="assessmentCertBody" id="assessmentCertBody" name="assessmentCertBody" placeholder="Certification Body" disabled>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Scope (IAF / NACE Code)</label> 
                                <input type="text" class="assessmentIafCode" id="assessmentIafCode" name="assessmentIafCode" placeholder="Scope (IAF / NACE Code)" disabled>
                            </div>
                        </div>
                        <div class="row " style="padding-top: ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Validity of Certificate</label> 
                                <input type="text" class="assessmentCertValidity" id="assessmentCertValidity" name="assessmentCertValidity" placeholder="Validity of Certificate" disabled>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Reasons If delay in renewal</label> 
                                <input type="text" class="assessmentRenewal" id="assessmentRenewal" name="assessmentRenewal" placeholder="Reasons If delay in renewal" disabled>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12 modificationClass" align="">Change / Modification:
                                <span>Scope <input class="modification" type="checkbox" name="modification" value="Scope" disabled> 
                                    Products  <input class="modification" type="checkbox" name="modification" value="Products" disabled> 
                                    Customer base <input class="modification" type="checkbox" name="modification" value="Customer base" disabled>
                                    Organization <input class="modification" type="checkbox" name="modification" value="Organization" disabled> 
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formCdc">
                            <span><i class="fa ">AITF - MCL Capability Assessment</i></span>
                        </div>
                    </div>
                    <div class="CdC">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Availability of QMS Scope</label> 
                                <input type="text" class="assessmentQmsScope" id="assessmentQmsScope" name="assessmentQmsScope" placeholder="Availability of QMS Scope" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Availability of Assessment Personnel</label> 
                                <input type="text" class="assessmentPersonnel" id="assessmentPersonnel" name="assessmentPersonnel" placeholder="Availability of Assessment Personnel" required>
                            </div>
                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Need for Technical Expert</label> 
                                <input type="text" class="assessmentTechnicalExpert" id="assessmentTechnicalExpert" name="assessmentTechnicalExpert" placeholder="Need for Technical Expert" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Availability</label>
                                <input type="text" class="assessmentAvailability" id="assessmentAvailability" name="assessmentAvailability" placeholder="Availability" required>
                            </div>
                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Logistic arrangement</label> 
                                <input type="text" class="assessmentLogisticArrangement" id="assessmentLogisticArrangement" name="assessmentLogisticArrangement" placeholder="Logistic arrangement" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Any conflict of interest</label> 
                                <input type="text" class="assessmentInterestConflict" id="assessmentInterestConflict" name="assessmentInterestConflict" placeholder="Any conflict of interest" required>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formCdc">
                            <span><i class="fa ">Implementation status of the Applicant</i></span>
                        </div>
                    </div>
                    <div class="CdC">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Date of QMS Implementation</label> 
                                <input type="text" class="assessmentImplementationDate" id="assessmentImplementationDate" name="assessmentImplementationDate" placeholder="Date of QMS Implementation">
                            </div>
                            <div class="col-xs-4 audit_aspect">
                                <font size="2">Documented Information Sent by client?</font>
                            </div>
                            <div class="col-xs-2 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Comments</label>
                                <textarea rows="2" class="assessmentComment" id="assessmentComment" name="assessmentComment" placeholder="Comments" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formCdc"><span><i
                                    class="fa ">Tentative Assessment Plan</i></span></div>
                    </div>
                    <div class="CdC">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Effective No. of Employees</label> 
                                <input type="number" min="1" class="assessmentEffectEmp" id="assessmentEffectEmp" name="assessmentEffectEmp" placeholder="Effective No. of Employees" required>
                            </div>
                            <div class="col-xs-6 audit_aspect float-label-control">
                                <label>Auditor Man days</label> 
                                <input type="text" min="1" class="assessmentManDays" id="assessmentManDays" name="assessmentManDays" placeholder="Auditor Man days" required>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-12"><label>Proposed Audit Team</label></div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Lead Auditor</label>
                                <select class="assessmentLeadAuditor form-control" id="assessmentLeadAuditor" name="assessmentLeadAuditor">
                                    <option value="">Choose Lead Auditors</option>
                                    <?php foreach ($auditors as $name) { ?>
                                        <option value="<?php echo $name['auditor_name'] ?>"><?php echo $name['auditor_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-6" align="">Code Auditor <span> 
                                    <input class="leadAuditor" type="checkbox" name="leadAuditor" value="Lead Auditor"></span>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Technical Expert</label> 
                                <input type="text" class="assessmentTechExpert" id="assessmentTechExpert" name="assessmentTechExpert" placeholder="Technical Expert" required>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-6 form-group float-label-control"><label>Auditors</label>
                            <!--<input type="text" class="assessmentAuditors" id="assessmentAuditors" name="assessmentAuditors" placeholder="Auditors" >-->
                                <select class="assessmentAuditors form-control" id="assessmentAuditors" name="assessmentAuditors">
                                    <option value="">Choose Auditors</option>
                                    <?php foreach ($auditors as $name) { ?>
                                        <option value="<?php echo $name['auditor_name'] ?>"><?php echo $name['auditor_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-3" align="">
                                Code Auditor <span><input class="codeAuditor" type="checkbox" name="codeAuditor" value="Auditors"></span>
                            </div>
                            <div class="col-sm-3" align="">
                                <a class="newCodeAuditorAdd" style="float: right" title="Add Multiple Auditors"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        <div class="multipleCodeAuditor"></div>
                        <div class="row " style="">
                            <div class="col-sm-12">
                                <label>Proposed Audit Plan</label>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Review of Documented Information</label> 
                                <input type="text" class="assessmentDicRevPropDate" id="assessmentDicRevPropDate" name="assessmentDicRevPropDate" placeholder="Review of Documented Information" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Stage -2 Audit </label> 
                                <input type="text" class="assessmentStageIIPropDate" id="assessmentStageIIPropDate" name="assessmentStageIIPropDate" placeholder="Stage -2 Audit " required>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Stage I </label> 
                                <input type="text" class="assessmentStageIPropDate" id="assessmentStageIPropDate" name="assessmentStageIPropDate" placeholder="Stage I " required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>If Recertification </label> 
                                <input type="text" class="assessmentRecertPropDate" id="assessmentRecertPropDate" name="assessmentRecertPropDate" placeholder="If Recertification " required>
                            </div>
                        </div>

                        <div class="row " style="">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>If Stage- I Audit not recommended Reason</label> 
                                <input type="text" class="assessmentStageIReason" id="assessmentStageIReason" name="assessmentStageIReason" placeholder="If Stage- I Audit not recommended Reason" required>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-6 ">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Date</label>
                                        <input type="text" class="assessmentDate" id="assessmentDate" name="assessmentDate" placeholder="Date" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <div id="assessmentDateError" style="display: none">
                                            <small><span id="assessmentDateErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Verified by</label>
                                <input type="text" class="assessmentVerifiedBy" id="assessmentVerifiedBy" name="assessmentVerifiedBy" placeholder="Verified by">
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="assessmentUserId" name="assessmentUserId" value=""> 
                <input type="hidden" class="assessmentServiceId" name="assessmentServiceId" value=""> 
                <input type="hidden" class="assessmentId" name="assessmentId" value=""> 
                <input type="hidden" class="leadAuditorHidden" name="leadAuditorHidden" value=""> 
                <input type="hidden" class="leadAuditorChkHidden" name="leadAuditorChkHidden" value=""> 
                <input type="hidden" class="AuditorHidden" name="AuditorHidden" value=""> 
                <input type="hidden" class="AuditorChkHidden" name="AuditorChkHidden" value="">
                <div class="modal-footer">
                    <button type="submit" name="addAssessment" class="btn btn-xs btn-info addAssessment">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">

    function validateAuditPlanDate() {
        $isError = false;
        if ($(".assessmentDate").val() == "") {
            $('#assessmentDateErrorMsg').text("Date can not be empty");
            $('#assessmentDateError').show();
            $('#assessmentDate').addClass('inputHighlight');
            $isError = true;
        } else if ($(".assessmentDate").val() != "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    assessmentDate: $('.assessmentDate').val(),
                    svcid: $(".assessmentServiceId").val(),
                },
                async: false,
                success: function (data) {
//	                alert(data);	                    
                    if (data == 1) {
                        $('#assessmentDateErrorMsg').text("Date should be greater than or equal to contract review date");
                        $('#assessmentDateError').show();
                        $('#assessmentDate').addClass('inputHighlight');
                        $isError = true;
                    } else {
                        $('#assessmentDateError').hide();
                        $('#assessmentDate').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#assessmentDateError').hide();
            $('#assessmentDate').removeClass('inputHighlight');
        }
        return $isError;
    }

    $(document).ready(function () {

        $(".addAssessment").click(function (e) {
//            alert($(".assessmentServiceId").val());
            if (validateAuditPlanDate()) {
                e.preventDefault();
                return;
            }
        });

        $(".appifyes").change(function () {
            if ($(".appifyes").val() == "yes") {
//                $assessmentIafCode = data[0]['scope'];
                $('.assessmentIafCode').val($assessmentIafCode);
                $(".assessmentCertBody").prop("disabled", false);
                $(".assessmentIafCode").prop("disabled", false);
                $(".assessmentCertValidity").prop("disabled", false);
                $(".assessmentRenewal").prop("disabled", false);
                $(".modification").prop("disabled", false);
                $(".assessmentCertBody").prop('required', true);
                $(".assessmentIafCode").prop('required', true);
                $(".assessmentCertValidity").prop('required', true);
                $(".assessmentRenewal").prop('required', true);
                $('input[type="text"]').trigger('change');
//                $(".modification").prop('required', true);
            } else if ($(".appifyes").val() == "no") {
                $('.assessmentIafCode').val("");
                $('.modification').attr('checked', false);
                $(".assessmentCertBody").prop("disabled", true);
                $(".assessmentIafCode").prop("disabled", true);
                $(".assessmentCertValidity").prop("disabled", true);
                $(".assessmentRenewal").prop("disabled", true);
//                $(".modification").prop("disabled", true);
                $(".assessmentCertBody").prop('required', false);
                $(".assessmentIafCode").prop('required', false);
                $(".assessmentCertValidity").prop('required', false);
                $(".assessmentRenewal").prop('required', false);
                $(".modification").prop('required', false);
            }
        });

//    $('.newAuditorAdd').click(function (){
//    $('.multipleAuditor').append('<div class="row newAuditorParent" style="">\n\
//                            <div class="col-sm-6 form-group float-label-control">\n\
//                                <label>Lead Auditor</label>\n\
//                                <input type="text" class="assessmentLeadAuditor" id="assessmentLeadAuditor" name="assessmentLeadAuditor" placeholder="Lead Auditor" >\n\
//                            </div>\n\
//                            <div class="col-sm-3" align="">Code Auditor<span> <input class="leadAuditor" type="checkbox" name="leadAuditor" value="Lead Auditor"></span>\n\
//                            </div>\n\
//                            <div class="col-sm-3 deleteAuditorParent" align="">\n\
//                                <a class="removeAuditor" style="float: right" title="Remove Auditors"><i class="fa fa-remove"></i></a>\n\
//                            </div>\n\
//                        </div>');
//    });
        $('.newCodeAuditorAdd').click(function () {

            var allAuditors = ("<?php echo $allAuditors; ?>");
            var auditors = allAuditors.split(',');
            var option = "";
            for (var key in auditors) {
                if (auditors[key] != null && auditors[key] != "") {
                    option += '<option value="' + auditors[key] + '">' + auditors[key] + '</option>';
                }
            }
            var autitorsSelect = '<select class="assessmentAuditors form-control" id="assessmentAuditors" name="assessmentAuditors">\n\
                                    <option value="" disabled selected>Choose Auditors</option>\n\
                                        ' + option + '\n\
                                </select>';

            $('.multipleCodeAuditor').append('<div class="row newCodeAuditorParent" style="">\n\
                                                <div class="col-sm-6 form-group float-label-control">\n\
                                                    <label>Lead Auditor</label>\n\
                                                    ' + autitorsSelect + '\n\
                                                </div>\n\
                                                <div class="col-sm-3" align="">\n\
                                                    Code Auditor<span> <input class="codeAuditor" type="checkbox" name="codeAuditor" value="Auditors"></span>\n\
                                                </div>\n\
                                                <div class="col-sm-3 deleteCodeAuditorParent" align="">\n\
                                                    <a class="removeCodeAuditor" style="float: right" title="Remove Auditors"><i class="fa fa-remove"></i></a>\n\
                                                </div>\n\
                                            </div>');
        });
//            $('body').on("click", ".removeAuditor", function () {
//    $(this).parent("div.deleteAuditorParent").parent('div.newAuditorParent').remove();
//    });
        $('body').on("click", ".removeCodeAuditor", function () {
            $(this).parent("div.deleteCodeAuditorParent").parent('div.newCodeAuditorParent').remove();
        });
        
        $('.addAssessment').click(function () {
//    var leadAuditorArray = [];
//            $('.assessmentLeadAuditor').each(function () {
//    leadAuditorArray.push($(this).val());
//    });
//            $('.leadAuditorHidden').val(leadAuditorArray.join(", "));
//            var leadAuditorChkArray = [];
//            $('.leadAuditor').each(function () {
//    if ($(this).prop('checked'))
//            $chkLead = $(this).val();
//            else
//            $chkLead = ""
//            leadAuditorChkArray.push($chkLead);
//    });
//            $('.leadAuditorChkHidden').val(leadAuditorChkArray.join(","));
            //alert($('.leadAuditorChkHidden').val());

            var AuditorArray = [];
            $('.assessmentAuditors').each(function () {
                AuditorArray.push($(this).val());
            });
            $('.AuditorHidden').val(AuditorArray.join(", "));
            var AuditorChkArray = [];
            $('.codeAuditor').each(function () {
                if ($(this).prop('checked'))
                    $chkAud = $(this).val();
                else
                    $chkAud = ""
                AuditorChkArray.push($chkAud);
            });
            $('.AuditorChkHidden').val(AuditorChkArray.join(","));
//            alert($('.AuditorChkHidden').val());
//            alert($('.AuditorHidden')).val();
        });

        $assessmentIafCode = "";

        $('.addAssessmentPlanning').click(function () {
//            alert($(this).attr('data-service'));
            $('.assessmentUserId').val($(this).attr('data-customer'));
            $('.assessmentServiceId').val($(this).attr('data-service'));
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customid: $(this).attr('data-customer'),
                    svcID: $(this).attr('data-service')
                },
                success: function (data)
                {
                    //scope, 
                    $assessmentIafCode = data[0]['scope'];
//                    $('.assessmentIafCode').val(data[0]['scope']);
                    $('.appClient').val(data[0]['customer_name']);
                    $('.refNo').val(data[3]['aggrement_no']);
                    $('.assessmentLocation').val(data[0]['address']);
                    $('.assessmentPlaningIafCode').val(data[2]['iaf_code']);
                    $('.assessmentNace').val(data[2]['nace_codes']);
                    //alert(data[2]['nace_codes']);
                    $('.assessmentNaceClass').val(data[2]['nace_class']);
                    //alert(data[2]['nace_class']);
//                    $('.assessmentIafCode').val(data[0]['scope']);
//                    $(".assessmentBusiness").val(data[0]['scope']);
//                    $('.assessmentBusiness').val(data[1]['scope']);
                    $('.certRequirement').val(data[1]['certification']);
                    $(".assessmentBusiness").val(data[1]["proposed_scope"]);
                    $('input[type="text"]').trigger('change');
                    $('.assessmentLocation').trigger('change');
                    $('#assessmentModal').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                },
            });
        });
        $('.modification').change(function () {
            if ((this).checked) {
                $('.modification').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        $('.editAssessmentPlanning').click(function () {
            $.ajax({
                url: "getAssessmentPlanning.php",
                type: "GET",
                dataType: "json",
                data: {
                    assessmentId: $(this).attr('data-assessment'),
                },
                success: function (data)
                {
                    $('.assessmentUserId').val(data['customer_id']);
                    $('.assessmentId').val(data['assessment_id']);
                    $('.appClient').val(data['app_client_name']);
                    $('.refNo').val(data['app_client_ref']);
                    $('.assessmentLocation').val(data['location']);
                    $('.assessmentBusiness').val(data['business']);
                    $('.certRequirement').val(data['cert_standard']);
                    $('.assessmentPlaningIafCode').val(data['iaf_code']);
                    $('.assessmentNace').val(data['nace_code']);
                    $('.assessmentNaceClass').val(data['nace_class']);
                    //alert(data['nace_class']);
                    $('.assessmentCertReq').val(data['defined_cert_req']);
                    $('.assessmentAppStandard').val(data['app_standard_diff']);
                    $('.assessmentSpecialAspects').val(data['special_aspects']);
                    $('.assessmentCertBody').val(data['certification_body']);
                    $('.assessmentIafCode').val(data['aif_scope']);
                    var validdate;
                    var Certifiedpreviously = ""
                    if (data['cert_validatity'] != null && data['cert_validatity'] != "") {
                        validdate = moment(data['cert_validatity']).format("DD.MM.YYYY");
                        Certifiedpreviously = 'yes';
                    } else {
                        validdate = "";
                        Certifiedpreviously = 'no';
                    }
                    $('.assessmentCertValidity').val(validdate);
                    $('.appifyes').val(Certifiedpreviously);
                    $('.assessmentRenewal').val(data['renewal_delay']);
                    $('.modification[value = "' + data["modification"] + '"]').prop('checked', true);
                    $('.assessmentQmsScope').val(data['qms_scope']);
                    $('.assessmentPersonnel').val(data['assessment_personnel']);
                    $('.assessmentTechnicalExpert').val(data['need_technical_expert']);
                    $('.assessmentAvailability').val(data['availability']);
                    $('.assessmentLogisticArrangement').val(data['logistic_arrangement']);
                    $('.assessmentInterestConflict').val(data['conflict_interest']);
                    $('.assessmentImplementationDate').val(moment(data['qms_implementation_date']).format("DD.MM.YYYY"));
                    $('.assessmentComment').val(data['assessment_comment']);
                    $('.audit_response').val(data['client_instructions']);
//                    alert(data['client_instructions']);
                    $('.assessmentEffectEmp').val(data['no_of_employes']);
                    $('.assessmentManDays').val(data['auditor_man_days']);
//                    $('.assessmentAuditors').val(data['auditors']);
//                    if ($.trim(data['code_auditor']) != "")
//                    $('.codeAuditor').prop('checked', true);
                    if (Certifiedpreviously == "yes") {
                        $(".assessmentCertBody").prop("disabled", false);
                        $(".assessmentIafCode").prop("disabled", false);
                        $(".assessmentCertValidity").prop("disabled", false);
                        $(".assessmentRenewal").prop("disabled", false);
                        $(".modification").prop("disabled", false);
                    }
                    var Auditor = data['auditors'].split(', ');
                    var AuditorChk = data['code_auditor'].split(',');
                    var auditorCodeLen = Auditor.length;
                    var auditorCodeChkLen = AuditorChk.length;
                    var max = (Math.max(auditorCodeLen, auditorCodeChkLen));
                    for (var key = 0; key < max; key++) {
                        var aud = key <= auditorCodeLen && Auditor[key] != null && Auditor[key] != "" ? Auditor[key] : "";
                        var chk = key <= auditorCodeChkLen && AuditorChk[key] != null && AuditorChk[key] != "" ? AuditorChk[key] : "";
//                        alert(chk);
                        if (key == 0) {
                            $('.assessmentAuditors').val(aud);
                            if (chk != "")
                                $('.codeAuditor').prop('checked', true);
                        } else {
//                alert(chk);
                            if (chk == "Auditors" && chk != "") {
                                var chkval = "checked";
                            } else {
                                var chkval = "";
                            }

                            $('.multipleCodeAuditor').append('<div class="row newCodeAuditorParent" style="">\n\
                            <div class="col-sm-6 form-group float-label-control">\n\
                                <label>Lead Auditor</label>\n\
                                <input type="text" class="assessmentAuditors" id="assessmentAuditors" name="assessmentAuditors" value="' + aud + '" placeholder="Auditors" >\n\
                            </div>\n\
                            <div class="col-sm-3" align="">Code Auditor<span> <input class="codeAuditor" type="checkbox" name="codeAuditor" value="Auditors" ' + chkval + '></span>\n\
                            </div>\n\
                            <div class="col-sm-3 deleteCodeAuditorParent" align="">\n\
                                <a class="removeCodeAuditor" style="float: right" title="Remove Auditors"><i class="fa fa-remove"></i></a>\n\
                            </div>\n\
                        </div>');
                        }
                    }
                    $('.assessmentDocumentReview').val(data['document_review']);
                    $('.assessmentDicRevPropDate').val(data['document_review_date']);
                    $('.assessmentStageI').val(data['stage_1']);
                    $('.assessmentStageIPropDate').val(data['stage_1_date']);
                    $('.assessmentStageIIAudit').val(data['stage_2_audit']);
                    $('.assessmentStageIIPropDate').val(data['stage_2_audit_date']);
                    $('.assessmentRecertification').val(data['if_recertification']);
                    $('.assessmentRecertPropDate').val(data['if_recertification_date']);
                    $('.assessmentStageIReason').val(data['recommended_reason']);
                    $('.assessmentDate').val(moment(data['date']).format("DD.MM.YYYY"));
                    $('.assessmentVerifiedBy').val(data['verified_by']);
                    $('.assessmentServiceId').val(data["service_id"]);
//                    var ledAuditor = data['lead_auditor'].split(', ');
//                    var ledAuditorChk = data['lead_auditor_code'].split(',');
//                    var auditorLen = ledAuditor.length;
//                    var auditorChkLen = ledAuditorChk.length;
//                    var max = (Math.max(auditorLen, auditorChkLen));
//                    for (var key = 0; key < max; key++) {
//            var aud = key <= auditorLen && ledAuditor[key] != null && ledAuditor[key] != "" ? ledAuditor[key] : "";
//                    var chk = key <= auditorChkLen && ledAuditorChk[key] != null && ledAuditorChk[key] != "" ? ledAuditorChk[key] : "";
////                        alert(chk);
//                    if (key == 0) {
//            $('.assessmentLeadAuditor').val(aud);
//                    if (chk != "")
//                    $('.leadAuditor').prop('checked', true);
//            } else {
////                alert(chk);
//            if (chk == "Lead Auditor" && chk != ""){
//            var chkval = "checked";
//            }
//            else {
//            var chkval = "";
//            }
//
//            $('.multipleAuditor').append('<div class="row newAuditorParent" style="">\n\
//                                                        <div class="col-sm-6 form-group float-label-control">\n\
//                                                        <label>Lead Auditor</label>\n\
//                                                        <input type="text" class="assessmentLeadAuditor" id="assessmentLeadAuditor" name="assessmentLeadAuditor" value="' + aud + '" placeholder="Lead Auditor" >\n\
//                                                        </div>\n\
//                                                        <div class="col-sm-3" align="">Code Auditor<span> <input class="leadAuditor" type="checkbox" name="leadAuditor" value="Lead Auditor" ' + chkval + '></span>\n\
//                                                        </div>\n\
//                                                        <div class="col-sm-3 deleteAuditorParent" align="">\n\
//                                                        <a class="removeAuditor" style="float: right" title="Add Multiple Auditors"><i class="fa fa-remove"></i></a>\n\
//                                                        </div>\n\
//                                                        </div>');
//            }
//            }

                    $('.assessmentLeadAuditor').val(data['lead_auditor']);
                    if ($.trim(data['lead_auditor_code']) != "")
                        $('.leadAuditor').prop('checked', true);
                    $('.assessmentTechExpert').val(data['technical_expert']);
                    $('input[type="text"]').trigger('change');
                    $('input[type="number"]').trigger('change');
                    $('.assessmentLocation').trigger('change');
                    $('.assessmentComment').trigger('change');
                    $('#assessmentModal').modal('show');
//                    alert(data);
                },
                error: function (data, status, err)
                {
                    alert(status);
                },
            });
        });
        $('.downloadAssessmentPlanning').click(function () {
            var assessmentid = $(this).attr('data-assessment');
            var service = $(this).attr('data-caservice-id');
            var customer = $(this).attr('data-customer');
            var url = 'AssessmentPlanning.php';
            var form = $('<form action="' + url + '" method="post" target="_blank" >' +
                    '<input type="hidden" name="assessmentId" value="' + assessmentid + '" />' +
                    '<input type="hidden" name="serviceid" value="' + service + '" />' +
                    '<input type="hidden" name="customerid" value="' + customer + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });
    });
    $(function () {
        $('#assessmentImplementationDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#assessmentImplementationDate").keydown(function (e) {
//            e.preventDefault();
        });
        $('#assessmentCertValidity').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#assessmentCertValidity").keydown(function (e) {
//            e.preventDefault();
        });
//
//        $('#assessmentDicRevPropDate').datepicker({
//            minDate: '-100Y',
//            maxDate: '+100Y',
//            changeYear: true,
//            changeMonth: true,
//            yearRange: "-20:+100",
//            dateFormat: 'dd.mm.yy',
////                    autoclose: true
//        });
//        $("#assessmentDicRevPropDate").keydown(function (e) {
//            e.preventDefault();
//        });

//        $('#assessmentStageIPropDate').datepicker({
//            minDate: '-100Y',
//            maxDate: '+100Y',
//            changeYear: true,
//            changeMonth: true,
//            yearRange: "-20:+100",
//            dateFormat: 'dd.mm.yy',
////                    autoclose: true
//        });
//        $("#assessmentStageIPropDate").keydown(function (e) {
//            e.preventDefault();
//        });

//        $('#assessmentStageIIPropDate').datepicker({
//            minDate: '-100Y',
//            maxDate: '+100Y',
//            changeYear: true,
//            changeMonth: true,
//            yearRange: "-20:+100",
//            dateFormat: 'dd.mm.yy',
////                    autoclose: true
//        });
//        $("#assessmentStageIIPropDate").keydown(function (e) {
//            e.preventDefault();
//        });

//        $('#assessmentRecertPropDate').datepicker({
//            minDate: '-100Y',
//            maxDate: '+100Y',
//            changeYear: true,
//            changeMonth: true,
//            yearRange: "-20:+100",
//            dateFormat: 'dd.mm.yy',
////                    autoclose: true
//        });
//        $("#assessmentRecertPropDate").keydown(function (e) {
//            e.preventDefault();
//        });

        $('#assessmentDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
//                    autoclose: true
        });
        $("#assessmentDate").keydown(function (e) {
//            e.preventDefault();
        });
    });
</script>

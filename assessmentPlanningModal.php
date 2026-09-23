<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 *
 */
include_once './dbConnect.php';
require_once __DIR__ . '/include/auditor_division_helper.php';
aitf_ensure_auditor_division_schema($db);
date_default_timezone_set('Asia/Kolkata');

$allAuditors = "";
$sql = "SELECT auditors_id, auditor_name, auditor_email FROM auditors ORDER BY auditor_name ASC";
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
<script src="js/date-chronology-validation.js?v=2"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="assessmentModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
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
                                <input type="text" class="appClient" id="appClient" name="appClient"
                                    placeholder="Applicant / Client Name" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label> Agreement No.:</label>
                                <input type="text" class="refNo" id="refNo" name="refNo" placeholder="Agreement No.:"
                                    required>
                            </div>`
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Site (s):</label>
                                <textarea rows="4" class="assessmentLocation" id="assessmentLocation"
                                    name="assessmentLocation" placeholder="Site(s):" required></textarea>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row ">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Business /Operations (Scope of Certification)</label>
                                        <input type="text" class="assessmentBusiness" id="assessmentBusiness"
                                            name="assessmentBusiness"
                                            placeholder="Business /Operations (Scope of Certification)" required>
                                    </div>
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Certification required for standard</label>
                                        <textarea name="certRequirement" class="form-control form-group certRequirement"
                                            id="certRequirement" placeholder="Assessment Standard" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Division</label>
                                <input type="text" name="assessmentPlaningIafCode"
                                    class="form-control assessmentPlaningIafCode" placeholder="Division">
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Group</label>
                                <input type="text" name="assessmentNace" class="form-control assessmentNace"
                                    placeholder="Group">
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Class</label>
                                <input type="text" name="assessmentNaceClass" class="form-control assessmentNaceClass"
                                    placeholder="Class">
                            </div>


                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Are the certification requirements clearly defined, documented & understood by
                                    applicant? Yes / No</label>
                                <input type="text" class="assessmentCertReq" id="assessmentCertReq"
                                    name="assessmentCertReq"
                                    placeholder="Are the certification requirements clearly defined, documented & understood by applicant? Yes / No"
                                    required>
                            </div>
                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Any difference in applicants understanding pending resolution? Yes / No</label>
                                <input type="text" class="assessmentAppStandard" id="assessmentAppStandard"
                                    name="assessmentAppStandard"
                                    placeholder="Any difference in applicants understanding pending resolution?  Yes / No"
                                    required>
                            </div>
                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Special aspects, such as, language, if any</label>
                                <input type="text" class="assessmentSpecialAspects" id="assessmentSpecialAspects"
                                    name="assessmentSpecialAspects"
                                    placeholder="Special aspects, such as, language, if any" required>
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
                                <span>Is applicant certified previously (Yes/ No) : _____ If Yes provide the
                                    information</span>
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
                                <input type="text" class="assessmentCertBody" id="assessmentCertBody"
                                    name="assessmentCertBody" placeholder="Certification Body" disabled>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Scope (IAF / NACE Code)</label>
                                <input type="text" class="assessmentIafCode" id="assessmentIafCode"
                                    name="assessmentIafCode" placeholder="Scope (IAF / NACE Code)" disabled>
                            </div>
                        </div>
                        <div class="row " style="padding-top: ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Validity of Certificate</label>
                                <input type="text" class="assessmentCertValidity" id="assessmentCertValidity"
                                    name="assessmentCertValidity" placeholder="Validity of Certificate" disabled>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Reasons If delay in renewal</label>
                                <input type="text" class="assessmentRenewal" id="assessmentRenewal"
                                    name="assessmentRenewal" placeholder="Reasons If delay in renewal" disabled>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12 modificationClass" align="">Change / Modification:
                                <span>Scope <input class="modification" type="checkbox" name="modification"
                                        value="Scope" disabled>
                                    Products <input class="modification" type="checkbox" name="modification"
                                        value="Products" disabled>
                                    Customer base <input class="modification" type="checkbox" name="modification"
                                        value="Customer base" disabled>
                                    Organization <input class="modification" type="checkbox" name="modification"
                                        value="Organization" disabled>
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
                                <input type="text" class="assessmentQmsScope" id="assessmentQmsScope"
                                    name="assessmentQmsScope" placeholder="Availability of QMS Scope" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Availability of Assessment Personnel</label>
                                <input type="text" class="assessmentPersonnel" id="assessmentPersonnel"
                                    name="assessmentPersonnel" placeholder="Availability of Assessment Personnel"
                                    required>
                            </div>
                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Need for Technical Expert</label>
                                <input type="text" class="assessmentTechnicalExpert" id="assessmentTechnicalExpert"
                                    name="assessmentTechnicalExpert" placeholder="Need for Technical Expert" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Availability</label>
                                <input type="text" class="assessmentAvailability" id="assessmentAvailability"
                                    name="assessmentAvailability" placeholder="Availability" required>
                            </div>
                        </div>
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Logistic arrangement</label>
                                <input type="text" class="assessmentLogisticArrangement"
                                    id="assessmentLogisticArrangement" name="assessmentLogisticArrangement"
                                    placeholder="Logistic arrangement" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Any conflict of interest</label>
                                <input type="text" class="assessmentInterestConflict" id="assessmentInterestConflict"
                                    name="assessmentInterestConflict" placeholder="Any conflict of interest" required>
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
                                <input type="text" class="assessmentImplementationDate"
                                    id="assessmentImplementationDate" name="assessmentImplementationDate"
                                    placeholder="Date of QMS Implementation">
                                <div id="qmsImplementationDateError" style="display: none">
                                    <small><span id="qmsImplementationDateErrorMsg"
                                            style="color: #c9513c; font-style: italic;"></span></small>
                                </div>
                            </div>
                            <div class="col-xs-4 audit_aspect">
                                <font size="2">Documented Information Sent by client?</font>
                            </div>
                            <div class="col-xs-2 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response"
                                    required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Comments</label>
                                <textarea rows="2" class="assessmentComment" id="assessmentComment"
                                    name="assessmentComment" placeholder="Comments" required></textarea>
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
                                <input type="number" min="1" class="assessmentEffectEmp" id="assessmentEffectEmp"
                                    name="assessmentEffectEmp" placeholder="Effective No. of Employees" required>
                            </div>
                            <div class="col-xs-6 audit_aspect float-label-control">
                                <label>Auditor Man days</label>
                                <input type="text" min="1" class="assessmentManDays" id="assessmentManDays"
                                    name="assessmentManDays" placeholder="Auditor Man days" required>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-12"><label>Proposed Audit Team</label></div>
                            <div class="col-sm-12 auditor-division-banner-ap" style="display:none; margin: 8px 0;"></div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Lead Auditor</label>
                                <select class="assessmentLeadAuditor form-control" id="assessmentLeadAuditor"
                                    name="assessmentLeadAuditor">
                                    <option value="">Choose Lead Auditors</option>
                                    <?php foreach ($auditors as $name) { ?>
                                        <option value="<?php echo $name['auditor_name'] ?>">
                                            <?php echo $name['auditor_name'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-6" align="">Code Auditor <span>
                                    <input class="leadAuditor" type="checkbox" name="leadAuditor"
                                        value="Lead Auditor"></span>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Technical Expert</label>
                                <input type="text" class="assessmentTechExpert" id="assessmentTechExpert"
                                    name="assessmentTechExpert" placeholder="Technical Expert" required>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-6 form-group float-label-control"><label>Auditors</label>
                                <!--<input type="text" class="assessmentAuditors" id="assessmentAuditors" name="assessmentAuditors" placeholder="Auditors" >-->
                                <select class="assessmentAuditors form-control" id="assessmentAuditors"
                                    name="assessmentAuditors">
                                    <option value="">Choose Auditors</option>
                                    <?php foreach ($auditors as $name) { ?>
                                        <option value="<?php echo $name['auditor_name'] ?>">
                                            <?php echo $name['auditor_name'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-3" align="">
                                Code Auditor <span><input class="codeAuditor" type="checkbox" name="codeAuditor"
                                        value="Auditors"></span>
                            </div>
                            <div class="col-sm-3" align="">
                                <a class="newCodeAuditorAdd" style="float: right" title="Add Multiple Auditors"><i
                                        class="fa fa-plus"></i></a>
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
                                <input type="text" class="assessmentDicRevPropDate" id="assessmentDicRevPropDate"
                                    name="assessmentDicRevPropDate" placeholder="Review of Documented Information"
                                    required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Stage -2 Audit </label>
                                <input type="text" class="assessmentStageIIPropDate" id="assessmentStageIIPropDate"
                                    name="assessmentStageIIPropDate" placeholder="Stage -2 Audit " required>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Stage I </label>
                                <input type="text" class="assessmentStageIPropDate" id="assessmentStageIPropDate"
                                    name="assessmentStageIPropDate" placeholder="Stage I " required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>If Recertification </label>
                                <input type="text" class="assessmentRecertPropDate" id="assessmentRecertPropDate"
                                    name="assessmentRecertPropDate" placeholder="If Recertification " required>
                            </div>
                        </div>

                        <div class="row " style="">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Reason (If stage 1 audit is not recommended)</label>
                                <input type="text" class="assessmentStageIReason" id="assessmentStageIReason"
                                    name="assessmentStageIReason" placeholder="Reason (If stage 1 audit is not recommended)"
                                    required>
                            </div>
                        </div>
                        <div class="row " style="">
                            <div class="col-sm-6 ">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Date</label>
                                        <input type="text" class="assessmentDate" id="assessmentDate"
                                            name="assessmentDate" placeholder="Date" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <div id="assessmentDateError" style="display: none">
                                            <small><span id="assessmentDateErrorMsg"
                                                    style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Verified by</label>
                                <input type="text" class="assessmentVerifiedBy" id="assessmentVerifiedBy"
                                    name="assessmentVerifiedBy" placeholder="Verified by">
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

    $(document).ready(function () {
        $('#assessmentModal').on('shown.bs.modal', function () {
            // Re-initialize chronology validator when modal opens
            DateChronologyValidator.init({
                customerId: $('.assessmentUserId').val() || $('.customerId').val(),
                typedServiceId: $('.assessmentServiceId').val() || $('.serviceId').val(),
                fields: {
                    'qms_implementation_date': { input: '.assessmentImplementationDate', errorDiv: '#qmsImplementationDateError', errorMsg: '#qmsImplementationDateErrorMsg' },
                    'assessment_planning_date': { input: '.assessmentDate', errorDiv: '#assessmentDateError', errorMsg: '#assessmentDateErrorMsg' }
                },
                submitBtn: '.addAssessment'
            });
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
        var aitfAPEligibleAuditors = [];
        var aitfAPClientNaceClasses = [];
        var aitfAPHasNaceClasses = false;
        var aitfAPClientDivisions = [];
        var aitfAPHasDivisions = false;
        var aitfAPAllAuditors = <?php echo json_encode($auditors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

        function isAuditorNameQualifiedAP(audName) {
            if (!audName) return false;
            audName = String(audName).trim();
            if (!aitfAPHasNaceClasses && !aitfAPHasDivisions) return true;
            var eligible = aitfAPEligibleAuditors || [];
            for (var i = 0; i < eligible.length; i++) {
                if (String(eligible[i].auditor_name || '').trim().toLowerCase() === audName.toLowerCase()) {
                    return true;
                }
            }
            return false;
        }

        function hasAtLeastOneQualifiedAuditorAP() {
            // Temporarily bypassed: allow any auditor to be assigned
            return true;
        }

        function updateAuditorRowVisualStatusAP($select) {
            var val = String($select.val() || '').trim();
            var $parent = $select.closest('.form-group');
            $parent.find('.auditor-qualified-badge-ap').remove();

            var isLead = $select.hasClass('assessmentLeadAuditor');
            var $chk = isLead ? $('.leadAuditor') : $select.closest('.row').find('.codeAuditor');

            if (!val || val === '') {
                $select.css({
                    'border': '',
                    'background-color': '',
                    'color': '',
                    'font-weight': '',
                    'box-shadow': ''
                });
                return;
            }

            if (isAuditorNameQualifiedAP(val)) {
                $select.css({
                    'border': '1.5px solid #28a745',
                    'background-color': '#ffffff',
                    'color': '#155724',
                    'font-weight': '600',
                    'box-shadow': '0 0 0 1px rgba(40,167,69,0.15)'
                });
                $select.after('<div class="auditor-qualified-badge-ap" style="margin-top: 4px;"><span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; background: #e8f5e9; color: #1e7e34; border: 1px solid #c3e6cb;"><i class="fa fa-check-circle" style="color: #28a745;"></i> Code Qualified</span></div>');
                if ($chk.length) {
                    $chk.prop('checked', true);
                }
            } else {
                $select.css({
                    'border': '1px solid #ced4da',
                    'background-color': '#ffffff',
                    'color': '#495057',
                    'font-weight': 'normal',
                    'box-shadow': 'none'
                });
                if ($chk.length) {
                    $chk.prop('checked', false);
                }
            }
        }

        function renderAPLeadAuditorOptions(selectedName) {
            var html = '<option value="">Choose Lead Auditors</option>';
            var list = aitfAPAllAuditors || [];
            var found = false;

            if (list && list.length > 0) {
                for (var i = 0; i < list.length; i++) {
                    var a = list[i];
                    var name = a.auditor_name;
                    var isSel = (selectedName && selectedName.trim() === name.trim());
                    if (isSel) found = true;
                    var isQual = isAuditorNameQualifiedAP(name);
                    var qualBadge = isQual ? ' ✓ [Qualified]' : '';
                    var optStyle = isQual ? 'font-weight: bold; color: #155724;' : '';
                    var optTitle = name + (a.auditor_email ? ' (' + a.auditor_email + ')' : '');
                    html += '<option value="' + name + '"' + (isSel ? ' selected' : '') + ' title="' + optTitle + '" data-qualified="' + (isQual ? '1' : '0') + '" style="' + optStyle + '">' + name + qualBadge + '</option>';
                }
            }
            if (selectedName && !found && selectedName.trim() !== '') {
                var isQualFb = isAuditorNameQualifiedAP(selectedName);
                var qualBadgeFb = isQualFb ? ' ✓ [Qualified]' : '';
                html += '<option value="' + selectedName + '" selected data-qualified="' + (isQualFb ? '1' : '0') + '">' + selectedName + qualBadgeFb + '</option>';
            }
            return html;
        }

        function renderAPAuditorOptions(selectedName) {
            return renderAPLeadAuditorOptions(selectedName);
        }

        function renderAPAllAuditorOptions(selectedName) {
            return renderAPLeadAuditorOptions(selectedName);
        }

        function updateDivisionAlertBannerAP(resp) {
            var $banner = $('.auditor-division-banner-ap');
            if (!resp || (!resp.has_nace_classes && !resp.has_divisions)) {
                $banner.html('<div class="alert alert-info" style="padding: 10px 16px; margin-bottom: 14px; font-size: 13px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border: 1px solid #bce8f1; background: #f4fbfd;"><div style="display: flex; align-items: center; gap: 10px;"><i class="fa fa-info-circle" style="font-size: 17px; color: #17a2b8;"></i> <span><strong>Note:</strong> No NACE Class assigned to this client in Contract Review yet. Showing all auditors.</span></div></div>').show();
            } else if (resp.has_nace_classes) {
                var classes = resp.client_nace_classes || [];
                var audCount = (resp.auditors) ? resp.auditors.length : 0;
                var classLabel = (classes.length > 1) ? 'Client NACE Classes' : 'Client NACE Class';
                var classesBadge = '<span style="background: #16a34a; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; margin: 0 4px; letter-spacing: 0.3px;">' + classes.join(', ') + '</span>';
                if (audCount === 0) {
                    $banner.html('<div class="alert alert-warning" style="padding: 10px 16px; margin-bottom: 14px; font-size: 13px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border: 1px solid #ffeeba; background: #fffdf5;"><div style="display: flex; align-items: center; gap: 10px;"><i class="fa fa-exclamation-triangle" style="font-size: 17px; color: #ffc107;"></i> <span><strong>No Qualified auditors found</strong> for ' + classLabel + ': ' + classesBadge + '. Please assign this NACE Class in <a href="masterCreator.php" target="_blank" style="text-decoration: underline; font-weight: bold; color: #856404;">Master Creator</a>.</span></div></div>').show();
                } else {
                    $banner.html('<div class="alert alert-success" style="padding: 10px 16px; margin-bottom: 14px; font-size: 13px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: linear-gradient(135deg, #f0fdf4 0%, #eafbf0 100%); border: 1px solid #bbf7d0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">' +
                        '<div style="display: flex; align-items: center; gap: 10px;">' +
                            '<i class="fa fa-check-circle" style="font-size: 18px; color: #16a34a;"></i> ' +
                            '<span style="color: #166534; font-weight: 500;"><strong>' + classLabel + ':</strong> ' + classesBadge + ' &mdash; Showing <strong>' + audCount + '</strong> Qualified auditors.</span>' +
                        '</div>' +
                        '<button type="button" class="btn btn-xs btn-view-qualified-auditors-ap" style="font-weight: 600; padding: 5px 14px; color: #15803d; background: #ffffff; border: 1.5px solid #22c55e; border-radius: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.06); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 12px;" title="Click to view qualified auditors list">' +
                            '<i class="fa fa-eye" style="color: #16a34a;"></i> View Qualified Auditors (' + audCount + ')' +
                        '</button>' +
                    '</div>').show();
                }
            } else {
                var divs = resp.client_divisions || [];
                var audCount = (resp.auditors) ? resp.auditors.length : 0;
                var divLabel = (divs.length > 1) ? 'Client Divisions' : 'Client Division';
                var divsBadge = '<span style="background: #16a34a; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; margin: 0 4px; letter-spacing: 0.3px;">' + divs.join(', ') + '</span>';
                if (audCount === 0) {
                    $banner.html('<div class="alert alert-warning" style="padding: 10px 16px; margin-bottom: 14px; font-size: 13px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border: 1px solid #ffeeba; background: #fffdf5;"><div style="display: flex; align-items: center; gap: 10px;"><i class="fa fa-exclamation-triangle" style="font-size: 17px; color: #ffc107;"></i> <span><strong>No Qualified auditors found</strong> for ' + divLabel + ': ' + divsBadge + '. Please assign this division in <a href="masterCreator.php" target="_blank" style="text-decoration: underline; font-weight: bold; color: #856404;">Master Creator</a>.</span></div></div>').show();
                } else {
                    $banner.html('<div class="alert alert-success" style="padding: 10px 16px; margin-bottom: 14px; font-size: 13px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: linear-gradient(135deg, #f0fdf4 0%, #eafbf0 100%); border: 1px solid #bbf7d0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">' +
                        '<div style="display: flex; align-items: center; gap: 10px;">' +
                            '<i class="fa fa-check-circle" style="font-size: 18px; color: #16a34a;"></i> ' +
                            '<span style="color: #166534; font-weight: 500;"><strong>' + divLabel + ':</strong> ' + divsBadge + ' &mdash; Showing <strong>' + audCount + '</strong> Qualified auditors.</span>' +
                        '</div>' +
                        '<button type="button" class="btn btn-xs btn-view-qualified-auditors-ap" style="font-weight: 600; padding: 5px 14px; color: #15803d; background: #ffffff; border: 1.5px solid #22c55e; border-radius: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.06); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 12px;" title="Click to view qualified auditors list">' +
                            '<i class="fa fa-eye" style="color: #16a34a;"></i> View Qualified Auditors (' + audCount + ')' +
                        '</button>' +
                    '</div>').show();
                }
            }
        }

        $(document).on('click', '.btn-view-qualified-auditors-ap', function (e) {
            e.preventDefault();
            var list = aitfAPEligibleAuditors || [];
            var codeText = aitfAPHasNaceClasses 
                ? ('Client NACE Class: ' + (aitfAPClientNaceClasses || []).join(', '))
                : ('Client Division: ' + (aitfAPClientDivisions || []).join(', '));
            
            if (!list || list.length === 0) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Qualified Auditors Found',
                        text: 'No auditors have been assigned to ' + codeText + ' in Master Creator.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert('No qualified auditors found for ' + codeText);
                }
                return;
            }

            var tableHtml = '<div style="text-align: left; max-height: 350px; overflow-y: auto;">' +
                '<p style="font-size: 13px; color: #555; margin-bottom: 10px;">Below are the auditors qualified for <strong>' + codeText + '</strong>:</p>' +
                '<table class="table table-bordered table-striped" style="font-size: 12px; margin-bottom: 0;">' +
                '<thead><tr style="background: #0F2742; color: #fff;"><th>#</th><th>Auditor Name</th><th>Email</th><th>Assigned Classes / Divisions</th></tr></thead>' +
                '<tbody>';
            
            for (var i = 0; i < list.length; i++) {
                var aud = list[i];
                var assignedInfo = aud.assigned_nace_classes_display || aud.assigned_divisions_display || '-';
                tableHtml += '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td style="font-weight: 600; color: #0F2742;">' + (aud.auditor_name || '-') + '</td>' +
                    '<td>' + (aud.auditor_email || '-') + '</td>' +
                    '<td><span class="badge" style="background: #28a745;">' + assignedInfo + '</span></td>' +
                    '</tr>';
            }
            tableHtml += '</tbody></table></div>';

            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({
                    title: '<span style="font-size: 18px; color: #0F2742;"><i class="fa fa-users" style="color: #28a745;"></i> Qualified Auditors (' + list.length + ')</span>',
                    html: tableHtml,
                    width: '650px',
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#0F2742'
                });
            } else {
                alert('Qualified Auditors:\n' + list.map(function(a) { return a.auditor_name + ' (' + a.auditor_email + ')'; }).join('\n'));
            }
        });

        function loadEligibleAuditorsForAssessment(customerId, serviceId, callback) {
            $.ajax({
                url: "getEligibleAuditors.php",
                type: "GET",
                dataType: "json",
                data: {
                    customerid: customerId,
                    serviceid: serviceId
                },
                success: function(resp) {
                    aitfAPHasNaceClasses = !!(resp && resp.has_nace_classes);
                    aitfAPClientNaceClasses = (resp && resp.client_nace_classes) ? resp.client_nace_classes : [];
                    aitfAPHasDivisions = !!(resp && resp.has_divisions);
                    aitfAPClientDivisions = (resp && resp.client_divisions) ? resp.client_divisions : [];
                    aitfAPEligibleAuditors = (resp && resp.auditors) ? resp.auditors : [];
                    if (resp && resp.all_auditors) {
                        aitfAPAllAuditors = resp.all_auditors;
                    }
                    updateDivisionAlertBannerAP(resp);
                    if (typeof callback === 'function') {
                        callback(resp);
                    }
                },
                error: function() {
                    aitfAPHasNaceClasses = false;
                    aitfAPHasDivisions = false;
                    updateDivisionAlertBannerAP(null);
                    if (typeof callback === 'function') {
                        callback(null);
                    }
                }
            });
        }

        window.loadEligibleAuditorsForAssessment = loadEligibleAuditorsForAssessment;
        window.renderAPLeadAuditorOptions = renderAPLeadAuditorOptions;
        window.renderAPAuditorOptions = renderAPAuditorOptions;
        window.renderAPAllAuditorOptions = renderAPAllAuditorOptions;
        window.hasAtLeastOneQualifiedAuditorAP = hasAtLeastOneQualifiedAuditorAP;

        function syncAuditorSelectOptionsAP() {
            var selectedAuditors = [];
            var leadAud = String($('.assessmentLeadAuditor').val() || '').trim();
            if (leadAud !== '') {
                selectedAuditors.push(leadAud);
            }
            $('.assessmentAuditors').each(function () {
                var val = String($(this).val() || '').trim();
                if (val !== '') {
                    selectedAuditors.push(val);
                }
            });

            updateAuditorRowVisualStatusAP($('.assessmentLeadAuditor'));

            $('.assessmentLeadAuditor').find('option').each(function () {
                var optVal = String($(this).val() || '').trim();
                if (optVal === '' || optVal === leadAud) {
                    $(this).prop('disabled', false).css('display', '');
                    return;
                }
                if (selectedAuditors.indexOf(optVal) !== -1) {
                    $(this).prop('disabled', true).css('display', 'none');
                } else {
                    $(this).prop('disabled', false).css('display', '');
                }
            });

            $('.assessmentAuditors').each(function () {
                var currentVal = String($(this).val() || '').trim();
                updateAuditorRowVisualStatusAP($(this));
                $(this).find('option').each(function () {
                    var optVal = String($(this).val() || '').trim();
                    if (optVal === '' || optVal === currentVal) {
                        $(this).prop('disabled', false).css('display', '');
                        return;
                    }
                    if (selectedAuditors.indexOf(optVal) !== -1) {
                        $(this).prop('disabled', true).css('display', 'none');
                    } else {
                        $(this).prop('disabled', false).css('display', '');
                    }
                });
            });
        }

        $(document).on('click', '.newCodeAuditorAdd', function (e) {
            var autitorsSelect = '<select class="assessmentAuditors form-control" id="assessmentAuditors" name="assessmentAuditors">\n\
                                    ' + renderAPAllAuditorOptions('') + '\n\
                                </select>';

            $('.multipleCodeAuditor').append('<div class="row newCodeAuditorParent" style="">\n\
                                                <div class="col-sm-6 form-group float-label-control">\n\
                                                    <label>Auditor</label>\n\
                                                    ' + autitorsSelect + '\n\
                                                </div>\n\
                                                <div class="col-sm-3" align="">\n\
                                                    Code Auditor<span> <input class="codeAuditor" type="checkbox" name="codeAuditor" value="Auditors"></span>\n\
                                                </div>\n\
                                                <div class="col-sm-3 deleteCodeAuditorParent" align="">\n\
                                                    <a class="removeCodeAuditor" style="float: right" title="Remove Auditors"><i class="fa fa-remove"></i></a>\n\
                                                </div>\n\
                                            </div>');
            syncAuditorSelectOptionsAP();
        });
        //            $('body').on("click", ".removeAuditor", function () {
        //    $(this).parent("div.deleteAuditorParent").parent('div.newAuditorParent').remove();
        //    });
        $('body').on("click", ".removeCodeAuditor", function () {
            $(this).parent("div.deleteCodeAuditorParent").parent('div.newCodeAuditorParent').remove();
            syncAuditorSelectOptionsAP();
        });

        var aitfAppointedReviewersAP = [];

        function loadAppointedReviewersForAssessmentPlan(customerId, serviceId) {
            aitfAppointedReviewersAP = [];
            if (!customerId && !serviceId) return;
            $.ajax({
                url: "checkReviewerConflict.php",
                type: "GET",
                dataType: "json",
                data: {
                    action: 'get_appointed_reviewers',
                    customerId: customerId,
                    serviceId: serviceId
                },
                success: function(resp) {
                    if (resp && resp.success) {
                        aitfAppointedReviewersAP = resp.reviewers || [];
                    }
                }
            });
        }

        function checkAuditorConflictWithReviewersAP(auditorName) {
            if (!auditorName) return false;
            var normAud = auditorName.toLowerCase().replace(/[^a-z0-9]/g, '');
            if (!normAud) return false;
            for (var i = 0; i < aitfAppointedReviewersAP.length; i++) {
                var normRev = aitfAppointedReviewersAP[i].toLowerCase().replace(/[^a-z0-9]/g, '');
                if (normAud === normRev || (normAud.length >= 5 && normRev.length >= 5 && (normAud.indexOf(normRev) !== -1 || normRev.indexOf(normAud) !== -1))) {
                    return aitfAppointedReviewersAP[i];
                }
            }
            return false;
        }

        function showAuditorConflictAP(auditorName, revName) {
            var nameToDisplay = auditorName || revName || "This user";
            var msg = nameToDisplay + " is Reviewer of this client, cannot set as Auditor.\n\nPlease select another Auditor.";
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({
                    icon: 'error',
                    title: 'Conflict Detected',
                    html: '<div class="aitf-conflict-card">' +
                              '<div class="aitf-conflict-card-title">' +
                                  '<span class="aitf-conflict-card-name">' + nameToDisplay + '</span> is Reviewer of this client, cannot set as Auditor.' +
                              '</div>' +
                              '<div class="aitf-conflict-card-sub">' +
                                  'Please select another Auditor.' +
                              '</div>' +
                          '</div>',
                    confirmButtonText: 'OK, I Understood',
                    confirmButtonColor: '#0F2742',
                    customClass: {
                        popup: 'aitf-conflict-swal',
                        confirmButton: 'aitf-conflict-btn'
                    }
                });
            } else {
                alert(msg);
            }
        }

        $('#assessmentPlanning').on('shown.bs.modal', function () {
            loadAppointedReviewersForAssessmentPlan($('.assessmentUserId').val(), $('.assessmentServiceId').val());
        });

        $(document).on('focus mousedown', '.assessmentLeadAuditor, .assessmentAuditors', function () {
            syncAuditorSelectOptionsAP();
        });

        $(document).on('change', '.assessmentLeadAuditor', function () {
            var $select = $(this);
            var audName = $select.val();
            if (!audName) {
                syncAuditorSelectOptionsAP();
                return;
            }
            var isDup = false;
            $('.assessmentAuditors').each(function () {
                var otherVal = String($(this).val() || '').trim();
                if (otherVal !== '' && otherVal === String(audName).trim()) {
                    isDup = true;
                    return false;
                }
            });
            if (isDup) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Auditor Already Selected',
                        text: audName + ' is already selected as a team auditor in this plan. Please choose a different auditor.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert(audName + ' is already selected as a team auditor in this plan. Please choose a different auditor.');
                }
                $select.val("");
                syncAuditorSelectOptionsAP();
                return;
            }

            var revConflict = checkAuditorConflictWithReviewersAP(audName);
            if (revConflict) {
                showAuditorConflictAP(audName, revConflict);
                $select.val("");
                syncAuditorSelectOptionsAP();
                return;
            }
            syncAuditorSelectOptionsAP();
        });

        $(document).on('change', '.assessmentAuditors', function () {
            var $select = $(this);
            var audName = $select.val();
            if (!audName) {
                syncAuditorSelectOptionsAP();
                return;
            }
            var isDup = false;
            var leadAud = String($('.assessmentLeadAuditor').val() || '').trim();
            if (leadAud !== '' && leadAud === String(audName).trim()) {
                isDup = true;
            }
            if (!isDup) {
                $('.assessmentAuditors').not($select).each(function () {
                    var otherVal = String($(this).val() || '').trim();
                    if (otherVal !== '' && otherVal === String(audName).trim()) {
                        isDup = true;
                        return false;
                    }
                });
            }
            if (isDup) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Auditor Already Selected',
                        text: audName + ' is already selected in this audit plan. Please choose a different auditor.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert(audName + ' is already selected in this audit plan. Please choose a different auditor.');
                }
                $select.val("");
                syncAuditorSelectOptionsAP();
                return;
            }

            var revConflict = checkAuditorConflictWithReviewersAP(audName);
            if (revConflict) {
                showAuditorConflictAP(audName, revConflict);
                $select.val("");
                syncAuditorSelectOptionsAP();
                return;
            }
            syncAuditorSelectOptionsAP();
        });

        $('.addAssessment').click(function (e) {
            // Temporarily disabled: allow any auditor to be saved
            /*
            if (!hasAtLeastOneQualifiedAuditorAP()) {
                e.preventDefault();
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Qualified Auditor Required',
                        text: 'Atleast one qualifed auditor needs to be in the team',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert('Atleast one qualifed auditor needs to be in the team');
                }
                return;
            }
            */

            var seenAuds = {};
            var hasDup = false;
            var dupAudName = "";
            var lead = String($('.assessmentLeadAuditor').val() || '').trim();
            if (lead) {
                seenAuds[lead] = true;
            }
            $('.assessmentAuditors').each(function () {
                var val = String($(this).val() || '').trim();
                if (val) {
                    if (seenAuds[val]) {
                        hasDup = true;
                        dupAudName = val;
                        return false;
                    }
                    seenAuds[val] = true;
                }
            });
            if (hasDup) {
                e.preventDefault();
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Duplicate Auditor',
                        text: (dupAudName ? dupAudName : 'An auditor') + ' is selected multiple times in this assessment plan. Please assign unique auditors.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert('Duplicate auditor selected in assessment plan.');
                }
                return;
            }

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
        });

        $assessmentIafCode = "";

        $(document).on('click', '.addAssessmentPlanning', function () {
            var customerId = $(this).attr('data-customer');
            var serviceId = $(this).attr('data-service');
            $('.assessmentUserId').val(customerId);
            $('.assessmentServiceId').val(serviceId);
            $('.multipleCodeAuditor').empty();
            loadAppointedReviewersForAssessmentPlan(customerId, serviceId);
            loadEligibleAuditorsForAssessment(customerId, serviceId, function() {
                $('.assessmentLeadAuditor').html(renderAPLeadAuditorOptions(''));
                $('.assessmentAuditors').first().html(renderAPAuditorOptions(''));
                syncAuditorSelectOptionsAP();
            });
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customid: customerId,
                    svcID: serviceId
                },
                success: function (data) {
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
                error: function (data, status, err) {
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
        $(document).on('click', '.editAssessmentPlanning', function () {
            var assessmentId = $(this).attr('data-assessment');
            var customerId = $(this).attr('data-customer');
            var serviceId = $(this).attr('data-service');
            $.ajax({
                url: "getAssessmentPlanning.php",
                type: "GET",
                dataType: "json",
                data: {
                    assessmentId: assessmentId,
                },
                success: function (data) {
                    var custId = customerId || data['customer_id'];
                    var svcId = serviceId || data['service_id'];
                    $('.assessmentUserId').val(custId);
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

                    loadAppointedReviewersForAssessmentPlan(custId, svcId);
                    loadEligibleAuditorsForAssessment(custId, svcId, function() {
                        var leadAud = data['lead_auditor'] || '';
                        $('.assessmentLeadAuditor').html(renderAPLeadAuditorOptions(leadAud)).val(leadAud);

                        $('.multipleCodeAuditor').empty();
                        var Auditor = (data['auditors'] || '').split(', ');
                        var AuditorChk = (data['code_auditor'] || '').split(',');
                        var auditorCodeLen = Auditor.length;
                        var auditorCodeChkLen = AuditorChk.length;
                        var max = (Math.max(auditorCodeLen, auditorCodeChkLen));
                        for (var key = 0; key < max; key++) {
                            var aud = key < auditorCodeLen && Auditor[key] != null && Auditor[key] != "" ? Auditor[key].trim() : "";
                            var chk = key < auditorCodeChkLen && AuditorChk[key] != null && AuditorChk[key] != "" ? AuditorChk[key].trim() : "";
                            if (key == 0) {
                                $('.assessmentAuditors').first().html(renderAPAllAuditorOptions(aud)).val(aud);
                                if (chk != "") {
                                    $('.codeAuditor').first().prop('checked', true);
                                } else {
                                    $('.codeAuditor').first().prop('checked', false);
                                }
                            } else {
                                var chkval = (chk == "Auditors" || chk != "") ? "checked" : "";
                                var selHtml = '<select class="assessmentAuditors form-control" name="assessmentAuditors">' + renderAPAllAuditorOptions(aud) + '</select>';
                                $('.multipleCodeAuditor').append('<div class="row newCodeAuditorParent" style="">\n\
                                <div class="col-sm-6 form-group float-label-control">\n\
                                    <label>Auditor</label>\n\
                                    ' + selHtml + '\n\
                                </div>\n\
                                <div class="col-sm-3" align="">Code Auditor<span> <input class="codeAuditor" type="checkbox" name="codeAuditor" value="Auditors" ' + chkval + '></span>\n\
                                </div>\n\
                                <div class="col-sm-3 deleteCodeAuditorParent" align="">\n\
                                    <a class="removeCodeAuditor" style="float: right" title="Remove Auditors"><i class="fa fa-remove"></i></a>\n\
                                </div>\n\
                            </div>');
                            }
                        }
                        syncAuditorSelectOptionsAP();
                    });

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
                error: function (data, status, err) {
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
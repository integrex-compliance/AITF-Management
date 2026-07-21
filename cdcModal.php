<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$allAuditors = "";
$sql = "SELECT auditor_name FROM auditors";
$statement = $db->prepare($sql);
$statement->execute();
$auditors = $statement->fetchAll();

foreach ($auditors as $name) {
    $allAuditors .= $name['auditor_name'] . ",";
}
$sql = "SELECT auditor_name FROM auditors";
$statement = $db->prepare($sql);
$statement->execute();
$auditors1 = $statement->fetchAll(PDO::FETCH_COLUMN);
//  Standards 
$standards = "SELECT * FROM standards";
$statement = $db->prepare($standards);
$statement->execute();
$standardsList = $statement->fetchAll();

$auditors = "SELECT * FROM auditors";
$statement = $db->prepare($standards);
$statement->execute();
$auditorsList = $statement->fetchAll();
?>

<script src="js/date-chronology-validation.js?v=2"></script>
<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="cdcModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false"
    aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="false"
                        aria-modal="true">x</span><span class="sr-only">Close</span></button>
                CERTIFICATE DECISION COMMITTEE DELIBERATION AND DECISION
            </div>
            <form action="addCdcModal.php" method="POST">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn   qms_complete_btn ">
                            <span><i class="fa  ">CDC Recommendation</i></span>
                        </div>
                    </div>
                    <div class="CdC">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Agreement number: </label>
                                <input type="text" class="file_No" id="file_No" name="file_No"
                                    placeholder="Agreement number: " required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Name of organization to be certified:</label>
                                <input type="text" class="audited_Organisation" id="audited_Organisation"
                                    name="audited_Organisation" placeholder="Name of organization to be certified:"
                                    required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Site/s to be certified: </label>
                                <textarea rows="4" class="address" id="address" name="address"
                                    placeholder="Site/s to be certified: " required></textarea>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Product / Service to be certified(Scope): </label>
                                <textarea rows="4" class="product_service" id="product_service" name="product_service"
                                    placeholder="Product / Service to be certified(Scope):" required></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Class/es:</label>
                                <input type="text" readonly class="iaf_nace" id="iaf_nace" name="iaf_nace"
                                    placeholder="Class/es:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Non Applicability of Standards requirement/s:</label>
                                <input type="text" class="exclusion" id="exclusion" name="exclusion"
                                    placeholder="Non Applicability of Standards requirement/s:" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Audit type:</label>
                                <input type="text" class="audit_type" id="audit_type" name="audit_type"
                                    placeholder="Audit type:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Audit Date(s):</label>
                                <input readonly type="text" class="audit_date_CDC" id="audit_date_CDC"
                                    name="audit_date_CDC" placeholder="Audit Date(s):" required>
                            </div>
                        </div>
                        <div class="row">
                            <input type="hidden" name="covered_for" class="covered_for" id="covered_for" value="">
                            <div class="col-sm-6 form-group float-label-control" id="cdcGlobalAccreditationGroup">
                                <label>Standard/s covered for certification:</label>
                                <input type="text" readonly class="form-control cdc-single-standard"
                                    placeholder="Assessment Standard">
                                <label for="accreditation">Accreditation Ref.:</label>
                                <select class="form-control accreditation" id="accreditation" name="accreditation"
                                    required>
                                    <option value="">Select Accreditation</option>
                                    <option value="NABCB">NABCB</option>
                                    <option value="SELF">SELF</option>
                                </select>
                                <small class="cdc-accreditation-feedback text-muted" aria-live="polite"></small>
                            </div>
                            <div class="col-sm-12 form-group" id="cdcIsoAccreditationList" style="display: none;"></div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Lead auditor: </label>
                                <input type="text" readonly class="lead_auditor" id="lead_auditor" name="lead_auditor"
                                    placeholder="Lead auditor:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Technical expert:</label>
                                <input type="text" class="technical_expert" id="technical_expert"
                                    name="technical_expert" placeholder="Technical expert:" required>
                            </div>

                            <div class="col-sm-6 form-group float-label-control">
                                <label>Any Other</label>
                                <input type="text" class="any_ther" id="any_ther" name="any_ther"
                                    placeholder="Any Other:" required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Auditor/s:</label>
                                <input type="text" class="auditors" id="auditors" name="auditors"
                                    placeholder="Auditor/s:">
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Assessment / NC (M) Closing date:</label>
                                        <input type="text" class="assDate" id="assDate" name="assDate"
                                            placeholder="Assessment / NC (M) Closing date:">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="assDateError" style="display: none">
                                            <small><span id="assDateErrorMsg"
                                                    style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--
                                                        <div class="col-sm-6 form-group float-label-control">
                                                            <label>Temp Date</label>
                                                            <input type="text" class="temp_date" id="temp_date" name="temp_date" placeholder="Temp Date" readonly>
                                                        </div>-->
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-12  text-center btn   qms_complete_btn ">
                                <span><i class="fa  ">For Recertification</i></span>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control" align="center">
                                <label>Review of the client's management system performance during certification
                                    tenure:</label>
                                <textarea rows="3" style="overflow:hidden; padding-top: 18px" class="system_during"
                                    id="system_during" name="system_during"
                                    placeholder="Review of the client's management system performance during certification tenure:"></textarea>
                            </div>
                            <div class="col-sm-6 form-group float-label-control" align="center">
                                <label>Complaints received if any from the customers of the certified client and other
                                    sources:</label>
                                <textarea rows="3" style="overflow:hidden; padding-top: 18px" class="system_complaint"
                                    id="system_complaint" name="system_complainte"
                                    placeholder="Complaints received if any from the customers of the certified client and other sources:"></textarea>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-12 text-center btn qms_complete_btn ">
                                <span><i class="fa ">CDC Members</i></span>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px">
                            <div class="col-sm-3 form-group float-label-control">
                                <label>Member 1</label>
                                <select class="form-control reviewer-1" id="reviewer-1" name="reviewer-1">
                                    <option value="">Select Member 1</option>
                                    <?php foreach ($auditors1 as $auditor): ?>
                                        <?php if ($auditor !== 'auditor' && $auditor !== 'allauditors'): ?>
                                            <option value="<?php echo $auditor; ?>"><?php echo $auditor; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-sm-3 form-group float-label-control">
                                <label>Member 2</label>
                                <select class="form-control reviewer-2" id="reviewer-2" name="reviewer-2">
                                    <option value="">Select Member 2</option>
                                    <?php foreach ($auditors1 as $auditor): ?>
                                        <?php if ($auditor !== 'auditor' && $auditor !== 'allauditors'): ?>
                                            <option value="<?php echo $auditor; ?>"><?php echo $auditor; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-3 form-group float-label-control">
                                <label>Member 3</label>
                                <select class="form-control granted-by" id="granted-by" name="granted-by">
                                    <option value="">Select Member 3</option>
                                    <?php foreach ($auditors1 as $auditor): ?>
                                        <?php if ($auditor !== 'auditor' && $auditor !== 'allauditors'): ?>
                                            <option value="<?php echo $auditor; ?>"><?php echo $auditor; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-3 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Date:</label>
                                        <input type="text" class="Date" id="Date" name="Date" placeholder="Date:">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="DateError" style="display: none">
                                            <small><span id="DateErrorMsg"
                                                    style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="rc-restore" style="display: none;">
                            <div class="row" style="">
                                <div class="col-xs-12 text-center btn qms_complete_btn ">
                                    <span><i class="fa ">For Rc</i></span>
                                </div>
                            </div>

                            <div class="row" style="padding-top: 10px">
                                <div class="col-sm-6 form-group float-label-control">
                                    <label>RC-Audit Date</label>
                                    <input type="text" class="date-restore1" id="date-restore1" name="date-restore1"
                                        placeholder="RC-Audit Date">
                                </div>
                                <div class="col-sm-6 form-group float-label-control">
                                    <label>Restore Date</label>
                                    <input type="text" class="date-restore2" id="date-restore2" name="date-restore2"
                                        placeholder="Restore Date">
                                </div>
                            </div>
                        </div>

                        <div class="row" style="">
                            <div class="col-xs-12 text-center btn qms_complete_btn ">
                                <span><i class="fa ">CDC Member 1 </i></span>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px">
                            <div class="col-sm-12">
                                <span>Audit processes are satisfactorily completed &emsp; &emsp; &emsp; &emsp; &emsp;
                                    &emsp;</span>
                                <span>
                                    <input class="checkrev1" type="checkbox" name="checkrev1_yes" id="checkrev1_yes"
                                        value="Yes">Yes
                                    <input class="checkrev1" type="checkbox" name="checkrev1_no" id="checkrev1_no"
                                        value="No">No*
                                </span>
                                <br>
                                <span>Complied as per Annex I (in case of Stage 1) &emsp; &emsp; &emsp; &emsp; &emsp;
                                    &emsp;</span>
                                <span>
                                    <input class="checkrev2" type="checkbox" name="checkrev2_na" id="checkrev2_na"
                                        value="N.A.">N.A.
                                    <input class="checkrev2" type="checkbox" name="checkrev2_yes" id="checkrev2_yes"
                                        value="Yes">Yes
                                    <input class="checkrev2" type="checkbox" name="checkrev2_no" id="checkrev2_no"
                                        value="No">No*
                                </span>
                            </div>
                        </div>

                        <!-- ISO Standards Decision Block for CDC Member 1 (RC only) -->
                        <div class="iso-decision-container" id="iso-decision-member1"
                            style="display: none; margin-top: 15px;">
                            <!-- ISO rows will be dynamically inserted here by JavaScript -->
                        </div>

                        <br>
                        <div class="row" style="padding-top: 10px">
                            <div class="col-sm-7 form-group float-label-control" align="center">
                                <label>*Comments if any</label>
                                <textarea rows="3" style="overflow:hidden" class="comments_if_any" id="comments_if_any"
                                    name="comments_if_any" placeholder="*Comments if any"></textarea>
                            </div>
                            <div class="col-sm-5 form-group ">
                                <input class="checkm1" type="checkbox" name="cdc-status-mem1-g" id="cdc-status-mem1-g"
                                    value="Granted">Granted &emsp; &emsp;
                                <input class="checkm1" type="checkbox" name="cdc-status-mem1-n" id="cdc-status-mem1-n"
                                    value="Not-Granted">Not-Granted
                            </div>
                            <!--                            <div class="col-sm-3 form-group float-label-control">
                                                            <label>Date:</label>
                                                            <input type="text" class="review-date-1" id="review-date-1" name="review-date-1" placeholder="Date:" >
                                                        </div>-->
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-12 text-center btn qms_complete_btn">
                                <span><i class="fa">CDC Member 2 </i></span>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 15px">
                            <div class="col-sm-12">
                                <span>All requirements are satisfactorily completed. &emsp; &emsp; &emsp; &emsp; &emsp;
                                    &emsp; </span><span><input class="check1" type="checkbox" name="check1_yes"
                                        id="check1_yes" value="Yes">Yes &emsp;<input class="check1" type="checkbox"
                                        name="check1_no" id="check1_no" value="No">No*</span> <br>
                                <span>Verified as per Annex II and found satisfactory &emsp; &emsp; &emsp; &emsp; &emsp;
                                    &emsp; </span><span><input class="check2" type="checkbox" name="check2_yes"
                                        id="check2_yes" value="Yes">Yes &emsp;<input class="check2" type="checkbox"
                                        name="check2_no" id="check2_no" value="No">No*</span><br>
                                <span>Adequate justification for LA's recommendation &emsp; &emsp;&nbsp; &emsp; &emsp;
                                    &emsp;</span><span><input class="check3" type="checkbox" name="check3_yes"
                                        id="check3_yes" value="Yes">Yes &emsp;<input class="check3" type="checkbox"
                                        name="check3_no" id="check3_no" value="No">No*</span>
                            </div>

                            <!-- ISO Standards Decision Block for CDC Member 2 (RC only) -->
                            <div class="iso-decision-container" id="iso-decision-member2"
                                style="display: none; margin-top: 15px;">
                                <!-- ISO rows will be dynamically inserted here by JavaScript -->
                            </div>
                            <!--                            <div class="col-sm-3 form-group float-label-control" >
                                                            <label>Date:</label>
                                                            <input type="text" class="audit_performance" id="audit_performance" name="audit_performance" placeholder="Date:" required>
                                                        </div>-->
                        </div>
                        <div class="row" style="padding-top: 10px">
                            <div class="col-sm-7 form-group float-label-control" align="center">
                                <label>*Comments if any</label>
                                <textarea rows="3" style="overflow:hidden" class="comments_if_any_2nd_review"
                                    id="comments_if_any_2nd_review" name="comments_if_any_2nd_review"
                                    placeholder="*Comments if any"></textarea>
                            </div>
                            <div class="col-sm-5 form-group ">
                                <input class="checkm2" type="checkbox" name="cdc-status-mem2-g" id="cdc-status-mem2-g"
                                    value="Granted">Granted &emsp; &emsp;
                                <input class="checkm2" type="checkbox" name="cdc-status-mem2-n" id="cdc-status-mem2-n"
                                    value="Not-Granted">Not-Granted
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 text-center btn qms_complete_btn">
                                <span><i class="fa ">CDC Member 3</i></span>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <span>Certification criteria have been met. &emsp; &emsp; &emsp; &emsp; &emsp; &emsp;
                            </span><span><input class="checkrev3" type="checkbox" name="checkrev3_yes"
                                    id="checkrev3_yes" value="Yes">Yes &emsp;<input class="checkrev3" type="checkbox"
                                    name="checkrev3_no" id="checkrev3_no" value="No">No*</span>
                        </div>

                        <!-- ISO Standards Decision Block for CDC Member 3 (RC only) -->
                        <div class="iso-decision-container" id="iso-decision-member3"
                            style="display: none; margin-top: 15px;">
                            <!-- ISO rows will be dynamically inserted here by JavaScript -->
                        </div>
                        <br><br>
                        <div class="row">
                            <div class="col-sm-5 form-group ">
                                <input class="checkm3" type="checkbox" name="cdc-status-mem3-g" id="cdc-status-mem3-g"
                                    value="Granted" disabled>Granted &emsp; &emsp;
                                <input class="checkm3" type="checkbox" name="cdc-status-mem3-n" id="cdc-status-mem3-n"
                                    value="Not-Granted" disabled>Not-Granted
                            </div>
                            <div class="col-sm-6 form-group float-label-control">

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>*Comments if any</label>
                                <textarea rows="2" class="comments" id="comments" name="comments"
                                    placeholder="*Comments if any"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn " style="position: relative;">
                            <span><i class="fa ">Audit Performance.</i></span>
                            <button type="button" class="btn btn-xs btn-info save_cdc_only"
                                style="position: absolute; right: 10px; top: 5px;">Save CDC Only</button>
                            <input type="hidden" class="is_cdc_only" name="is_cdc_only" value="0">
                        </div>
                    </div>
                    <br>
                    <div class="row" style="padding-top: 10px">
                        <div class="col-sm-4 form-group float-label-control">
                            <label>NC</label>
                            <input type="text" class="ncval" id="ncval" name="ncval" placeholder="NC">
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>OFI</label>
                            <input type="text" class="ofival" id="ofival" name="ofival" placeholder="OFI">
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>O+ve</label>
                            <input type="text" class="oplus" id="oplus" name="oplus" placeholder="O+ve">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 text-center btn qms_complete_btn">
                            <span><i class="fa ">~ FOR OFFICE USE ~</i></span>
                        </div>
                    </div>
                    <br>
                    <div id="cdcOfficeContainer" style="display: none;">
                        <div class="CdcOffice" data-iso-index="0">
                            <div class="row cdc-office-title" style="display:none;">
                                <div class="col-xs-12 text-center">
                                    <strong class="cdc-office-iso-label"></strong>
                                </div>
                            </div>
                            <input type="hidden" class="isoIndexField" data-base-id="isoIndexField"
                                data-field="iso_index" name="office_use[0][iso_index]" value="0">
                            <input type="hidden" class="isoLabelField" data-base-id="isoLabelField"
                                data-field="iso_label" name="office_use[0][iso_label]" value="">
                            <div class="row" style="">
                                <div class="col-xs-4 form-group float-label-control">
                                    <div class="row">
                                        <div class="col-sm-12 form-group float-label-control">
                                            <label>Rev date:</label>
                                            <input type="text" class="revDatecdc" id="revDatecdc"
                                                data-base-id="revDatecdc" data-field="rev_date"
                                                name="office_use[0][rev_date]" placeholder="Rev date:" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 ">
                                            <div id="revDatecdcError" style="display: none">
                                                <small> <span id="revDatecdcErrorMsg"
                                                        style="color: #c9513c; font-style: italic;"></span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4 form-group float-label-control">
                                    <label>Rev No:</label>
                                    <input type="text" class="revCdc" id="revCdc" data-base-id="revCdc"
                                        data-field="rev_no" name="office_use[0][rev_no]" placeholder="Rev No:" required>
                                </div>

                                <div class="col-xs-4 form-group float-label-control">
                                    <label>Certificate No:</label>
                                    <input type="text" class="certificateNo" id="certificateNo"
                                        data-base-id="certificateNo" data-field="certificate_no"
                                        name="office_use[0][certificate_no]" placeholder="Certificate No:" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4 form-group float-label-control">
                                    <label>This Audit Type</label>
                                    <select name="office_use[0][audit_type]" data-field="audit_type"
                                        data-base-id="nextaudittype" class="form-control nextaudittype"
                                        id="nextaudittype" required>
                                        <option value="">Select</option>
                                        <option value="CA">CA</option>
                                        <option value="SA-I">SA-I</option>
                                        <option value="SA-II">SA-II</option>
                                        <option value="RC">RC</option>
                                        <option value="SP">SP</option>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <div class="row">
                                        <div class="col-sm-12 form-group float-label-control">
                                            <label>Expire on date:</label>
                                            <input type="text" class="expiredate" id="expiredate"
                                                data-base-id="expiredate" data-field="expire_date"
                                                name="office_use[0][expire_date]" placeholder="Expire on date:"
                                                required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 ">
                                            <div id="expiredateError" style="display: none">
                                                <small> <span id="expiredateErrorMsg"
                                                        style="color: #c9513c; font-style: italic;"></span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 form-group float-label-control">
                                    <div class="row">
                                        <div class="col-sm-12 form-group float-label-control">
                                            <label>Next periodical audit:</label>
                                            <input type="text" class="periodicalaudit" id="periodicalaudit"
                                                data-base-id="periodicalaudit" data-field="periodical_audit"
                                                name="office_use[0][periodical_audit]"
                                                placeholder="Next periodical audit:" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 ">
                                            <div id="periodicalauditError" style="display: none">
                                                <small> <span id="periodicalauditErrorMsg"
                                                        style="color: #c9513c; font-style: italic;"></span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row ">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>Comment if any</label>
                                    <textarea rows="2" class="periodicalAuditcoment" id="periodicalAuditcoment"
                                        data-base-id="periodicalAuditcoment" data-field="comments"
                                        name="office_use[0][comments]" placeholder="Comments if any"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="revDatecdc" class="legacy-rev-date" value="">
                    <input type="hidden" name="revCdc" class="legacy-rev-no" value="">
                    <input type="hidden" name="certificateNo" class="legacy-certificate-no" value="">
                    <input type="hidden" name="nextaudittype" class="legacy-audit-type" value="">
                    <input type="hidden" name="expiredate" class="legacy-expire-date" value="">
                    <input type="hidden" name="periodicalaudit" class="legacy-periodical-audit" value="">
                    <input type="hidden" name="periodicalAuditcoment" class="legacy-comments" value="">
                    <input type="hidden" name="crr_Id" class="crr_Id" id="crr_Id" value="">
                    <input type="hidden" name="service_Id" class="service_Id" id="service_Id" value="">
                    <input type="hidden" name="customer_Id" class="customer_Id" id="customer_Id" value="">
                    <input type="hidden" name="uni_serviceid" class="uni_serviceid" value="">
                    <input type="hidden" name="servicetype" class="servicetype" value="">
                    <input type="hidden" name="iso_decisions" id="iso_decisions" value="">
                    <input type="hidden" name="iso_metadata" id="iso_metadata" value="">
                    <div class="modal-footer">
                        <button type="submit" name="save_cdc" class="btn btn-xs btn-info save_cdc">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    function normalizeCdcAccreditation(value) {
        var normalized = $.trim(value || '').toUpperCase();
        if (normalized.indexOf('NABCB') !== -1) {
            return 'NABCB';
        }
        if (normalized.indexOf('SELF') !== -1) {
            return 'SELF';
        }
        return '';
    }

    function setCdcAccreditation(value) {
        $('#cdcModel .accreditation').val(normalizeCdcAccreditation(value));
    }

    $(document).ready(function () {
        function validateCdcMembers(selectElement) {
            var $form = $(selectElement).closest('form');
            if ($form.length === 0) {
                // Fallback if not in form
                $form = $(selectElement).closest('.modal-content');
            }
            if ($form.length === 0) {
                $form = $('body');
            }

            var m1 = $form.find('.reviewer-1').val();
            var m2 = $form.find('.reviewer-2').val();
            var m3 = $form.find('.granted-by').val();

            var hasError = false;
            if (m1 && (m1 === m2 || m1 === m3)) hasError = true;
            if (m2 && (m2 === m1 || m2 === m3)) hasError = true;
            if (m3 && (m3 === m1 || m3 === m2)) hasError = true;

            $form.find('#cdcMemberError').remove();

            if (hasError) {
                // Reset the selection immediately
                $(selectElement).val('');

                // Show a styled alert popup
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Member',
                        text: 'CDC members cannot be the same person. Please select distinct members.',
                        confirmButtonColor: '#ea580c'
                    });
                } else {
                    alert('CDC members cannot be the same person. Please select distinct members.');
                }
            }
        }

        $(document).on('change', '.reviewer-1, .reviewer-2, .granted-by', function () {
            validateCdcMembers(this);
        });
    });

    function getIsoEntries(standardText) {
        if (!standardText) {
            return [];
        }
        console.log('Original standardText:', standardText);

        // First, try to split by common delimiters
        var delimiters = [',', ';', '\n', '/', '&', 'and', 'AND', 'And'];

        // Try to find the most appropriate delimiter
        var bestDelimiter = null;
        var maxSplits = 0;

        delimiters.forEach(function (delimiter) {
            var parts = standardText.split(delimiter);
            if (parts.length > maxSplits) {
                maxSplits = parts.length;
                bestDelimiter = delimiter;
            }
        });

        // If no good delimiter found, try to split by common ISO patterns
        if (!bestDelimiter || maxSplits <= 1) {
            // Try to split by common ISO patterns like "ISO 9001:2015, ISO 14001:2015"
            var isoPattern = /(?:ISO\s*\d+:?\s*\d*)/gi;
            var matches = standardText.match(isoPattern);
            if (matches && matches.length > 0) {
                console.log('Found ISOs using pattern matching:', matches);
                return matches.map(function (item) {
                    return item.trim();
                }).filter(function (item) {
                    return item.length > 0;
                });
            }

            // If still no matches, try to split by common ISO prefixes
            var prefixPattern = /(ISO|IATF|AS|OHSAS|HACCP|FSSC|ISO\s*\d+)/gi;
            matches = standardText.split(/(?=ISO\s*\d+|IATF|AS\s*\d+|OHSAS|HACCP|FSSC)/i);
            if (matches && matches.length > 1) {
                console.log('Found ISOs using prefix matching:', matches);
                return matches.map(function (item) {
                    return item.trim();
                }).filter(function (item) {
                    return item.length > 0;
                });
            }

            // If all else fails, return the original text as a single entry
            console.log('No clear delimiter or pattern found, returning as single entry');
            return [standardText.trim()];
        }

        // Split by the best delimiter found
        console.log('Splitting by delimiter:', bestDelimiter);
        var result = standardText.split(bestDelimiter)
            .map(function (item) {
                return item.trim();
            })
            .filter(function (item) {
                return item.length > 0;
            });

        console.log('Parsed ISO entries:', result);
        return result;
    }

    function formatCdcStandardsForDisplay(value) {
        var entries = getIsoEntries(value || '');
        return entries.length ? entries.join('\n') : (value || '');
    }

    function findCdcIsoMetadata(metadata, isoIndex, isoLabel) {
        var exactIndexMatch = null;
        var normalizedLabel = $.trim(isoLabel || '').toUpperCase();

        (Array.isArray(metadata) ? metadata : []).some(function (entry) {
            if (!entry || typeof entry !== 'object') {
                return false;
            }

            if (parseInt(entry.iso_index, 10) === isoIndex) {
                exactIndexMatch = entry;
                return true;
            }

            return false;
        });

        if (exactIndexMatch) {
            return exactIndexMatch;
        }

        var labelMatch = null;
        (Array.isArray(metadata) ? metadata : []).some(function (entry) {
            if ($.trim((entry && entry.iso_label) || '').toUpperCase() === normalizedLabel) {
                labelMatch = entry;
                return true;
            }
            return false;
        });

        return labelMatch;
    }

    function collectCdcIsoMetadata() {
        var metadata = [];
        $('#cdcIsoAccreditationList .cdc-iso-accreditation').each(function () {
            var $select = $(this);
            metadata.push({
                iso_index: parseInt($select.attr('data-iso-index'), 10),
                iso_label: $select.attr('data-iso-label') || '',
                accreditation: normalizeCdcAccreditation($select.val())
            });
        });

        $('#iso_metadata').val(metadata.length ? JSON.stringify(metadata) : '');
        window.cdcIsoMetadata = metadata;

        if (metadata.length && metadata[0].accreditation) {
            $('#cdcModel .accreditation').val(metadata[0].accreditation);
        }

        return metadata;
    }

    function renderCdcIsoAccreditationControls(standardText, metadata) {
        var isoEntries = getIsoEntries(standardText || '');
        var savedMetadata = Array.isArray(metadata) ? metadata : [];
        var $container = $('#cdcIsoAccreditationList');
        var $globalGroup = $('#cdcGlobalAccreditationGroup');
        var globalAccreditation = normalizeCdcAccreditation($('#cdcModel .accreditation').val());

        window.cdcIsoMetadata = savedMetadata;
        $container.empty();

        if (isoEntries.length === 0) {
            $container.hide();
            $globalGroup.show();
            $('#cdcModel .accreditation').attr('required', 'required');
            $globalGroup.find('.cdc-single-standard').val('');
            $('#iso_metadata').val('');
            return;
        }

        $globalGroup.hide();
        $('#cdcModel .accreditation').removeAttr('required');

        var $header = $('<div>', {
            'class': 'row',
            css: {
                marginBottom: '6px',
                fontWeight: '600'
            }
        });
        $header.append(
            $('<div>', { 'class': 'col-sm-6', text: 'Assessment Standard' }),
            $('<div>', { 'class': 'col-sm-6', text: 'Accreditation Ref.' })
        );
        $container.append($header);

        isoEntries.forEach(function (isoLabel, isoIndex) {
            var savedEntry = findCdcIsoMetadata(savedMetadata, isoIndex, isoLabel);
            var accreditation = normalizeCdcAccreditation(
                savedEntry && savedEntry.accreditation ? savedEntry.accreditation : globalAccreditation
            );
            var $row = $('<div>', {
                'class': 'row cdc-iso-accreditation-row',
                css: {
                    marginBottom: '10px'
                }
            });
            var $standardColumn = $('<div>', { 'class': 'col-sm-6' });
            var $standardField = $('<input>', {
                type: 'text',
                readonly: 'readonly',
                'class': 'form-control cdc-iso-standard',
                value: isoLabel
            });
            var $accreditationColumn = $('<div>', { 'class': 'col-sm-6' });
            var $select = $('<select>', {
                'class': 'form-control cdc-iso-accreditation',
                required: 'required'
            })
                .attr('data-iso-index', isoIndex)
                .attr('data-iso-label', isoLabel)
                .append($('<option>', { value: '', text: 'Select Accreditation' }))
                .append($('<option>', { value: 'NABCB', text: 'NABCB' }))
                .append($('<option>', { value: 'SELF', text: 'SELF' }))
                .val(accreditation)
                .data('previous-accreditation', accreditation);
            var $feedback = $('<small>', {
                'class': 'cdc-iso-accreditation-feedback text-muted',
                'aria-live': 'polite'
            });

            $standardColumn.append($standardField);
            $accreditationColumn.append($select, $feedback);
            $row.append($standardColumn, $accreditationColumn);
            $container.append($row);
        });

        $container.show();
        collectCdcIsoMetadata();
    }

    function setCdcCoveredStandards(value, metadata) {
        var formattedValue = formatCdcStandardsForDisplay(value || '');
        $('.covered_for').val(formattedValue);
        renderCdcIsoAccreditationControls(formattedValue, Array.isArray(metadata) ? metadata : []);
    }

    function ensureCdcOfficeSections(isoEntries) {
        var container = $('#cdcOfficeContainer');
        var base = container.find('.CdcOffice').first();

        if (!base.length) {
            console.error(' ERROR: Base CdcOffice section not found! Cannot create sections.');
            return;
        }

        var required = isoEntries ? isoEntries.length : 1;
        if (required === 0) required = 1;
        var sections = container.find('.CdcOffice');

        console.log('ensureCdcOfficeSections: Required =', required, 'Current =', sections.length, 'ISO entries:', isoEntries);

        if (sections.length > required) {
            sections.slice(required).remove();
            sections = container.find('.CdcOffice');
        }

        var created = 0;
        while (sections.length < required) {
            var isoIndex = sections.length;
            var clone = base.clone(false);

            if (!clone.length) {
                console.error(' ERROR: Failed to clone base section at index', isoIndex);
                break;
            }
            clone.attr('data-iso-index', isoIndex);
            clone.data('iso-index', isoIndex);

            // First, store the original IDs of date fields before removing datepicker
            var dateFieldIds = {};
            clone.find('.revDatecdc, .expiredate, .periodicalaudit').each(function () {
                var originalId = $(this).attr('id');
                if (originalId) {
                    dateFieldIds[originalId] = true;
                }
                // Remove all datepicker classes and attributes
                $(this).removeClass('hasDatepicker')
                    .removeData('datepicker')
                    .off('change.chronoValidation blur.chronoValidation') // Remove validation listeners to re-attach later
                    .off('focus')
                    .attr('autocomplete', 'off');
            });

            // Update all IDs including date fields
            clone.find('[id]').each(function () {
                var originalId = $(this).attr('id');
                if (originalId) {
                    $(this).attr('id', originalId + '_iso' + (isoIndex + 1));
                }
            });

            // Ensure date fields have proper IDs even if they were processed above
            clone.find('.revDatecdc').each(function () {
                if (!$(this).attr('id')) {
                    $(this).attr('id', 'revDatecdc_iso' + (isoIndex + 1));
                }
            });
            clone.find('.expiredate').each(function () {
                if (!$(this).attr('id')) {
                    $(this).attr('id', 'expiredate_iso' + (isoIndex + 1));
                }
            });
            clone.find('.periodicalaudit').each(function () {
                if (!$(this).attr('id')) {
                    $(this).attr('id', 'periodicalaudit_iso' + (isoIndex + 1));
                }
            });
            clone.find('[name]').each(function () {
                var field = $(this);
                var originalName = field.attr('name');
                if (!originalName) {
                    return;
                }
                var updatedName = originalName;
                if (updatedName.indexOf('office_use') !== -1) {
                    updatedName = updatedName.replace(/office_use\[\d+\]/, 'office_use[' + isoIndex + ']');
                }
                updatedName = updatedName.replace(/_iso\d+$/g, '');
                field.attr('name', updatedName);
            });

            clone.find('.isoIndexField').val(isoIndex);
            clone.find('.isoLabelField').val('');

            clone.find('.cdc-office-title').show();
            container.append(clone);
            sections = container.find('.CdcOffice');
            created++;
        }

        console.log('ensureCdcOfficeSections: Created', created, 'sections. Total now:', sections.length);

        // Initialize date pickers for all sections including newly cloned ones - with delay
        setTimeout(function () {
            if (typeof initializeCdcDatepickers === 'function') {
                console.log('CDC sections ensured - initializing datepickers');
                initializeCdcDatepickers();
            }
        }, 100);
    }

    function copyCdcValuesToClones() {
        var container = $('#cdcOfficeContainer');
        var sections = container.find('.CdcOffice');
        sections.each(function (index) {
            $(this).attr('data-iso-index', index);
            $(this).data('iso-index', index);
            $(this).find('.isoIndexField').val(index);
            $(this).find('[name]').each(function () {
                var field = $(this);
                var originalName = field.attr('name');
                if (!originalName) {
                    return;
                }
                if (originalName.indexOf('office_use') === -1) {
                    return;
                }
                var updatedName = originalName.replace(/office_use\[\d+\]/, 'office_use[' + index + ']');
                updatedName = updatedName.replace(/_iso\d+$/g, '');
                field.attr('name', updatedName);
            });
        });
    }

    function formatCdcDate(value) {
        if (!value) {
            return '';
        }
        if (typeof moment !== 'undefined') {
            var parsed = moment(value, ['YYYY-MM-DD', 'DD.MM.YYYY', moment.ISO_8601], true);
            if (!parsed.isValid()) {
                parsed = moment(value);
            }
            if (parsed.isValid()) {
                return parsed.format('DD.MM.YYYY');
            }
        }
        return value;
    }

    function normalizeIsoLabelForMatch(label) {
        if (!label) {
            return '';
        }
        return label.toLowerCase()
            .replace(/iso[\s-]*/gi, '')
            .replace(/[\s:]/g, '')
            .trim();
    }

    function deriveCertificateFromBase(baseCertNo, isoIndex) {
        if (!baseCertNo || typeof isoIndex !== 'number' || isoIndex <= 0) {
            return '';
        }
        var base = baseCertNo.toString();
        var match = base.match(/^(.*)(\d+)$/);
        if (match) {
            var prefix = match[1];
            var num = parseInt(match[2], 10);
            if (!isNaN(num)) {
                var derivedNum = num + isoIndex;
                var origLen = match[2].length;
                var derivedStr = derivedNum.toString();
                if (derivedStr.length < origLen) {
                    derivedStr = match[2].substring(0, origLen - derivedStr.length) + derivedStr;
                }
                return $.trim(prefix + derivedStr);
            }
        }
        // If no numeric suffix, append index
        return $.trim(base + isoIndex);
    }

    function resolveCaCertificateNumber(data, isoLabel, isoIndex) {
        if (!data || !data.success) {
            return '';
        }

        var certData = null;

        if (data.certificates && Array.isArray(data.certificates) && data.certificates.length > 0) {
            var certificates = data.certificates;
            var normalizedTargetISO = normalizeIsoLabelForMatch(isoLabel);

            if (normalizedTargetISO) {
                for (var i = 0; i < certificates.length; i++) {
                    var cert = certificates[i];
                    if (cert && cert.iso_label) {
                        var normalizedCertISO = normalizeIsoLabelForMatch(cert.iso_label);
                        if (normalizedCertISO === normalizedTargetISO) {
                            certData = cert;
                            break;
                        }
                    }
                }
            }

            if (!certData && normalizedTargetISO) {
                for (var j = 0; j < certificates.length; j++) {
                    var cert2 = certificates[j];
                    if (cert2 && cert2.iso_label) {
                        var normCert2 = normalizeIsoLabelForMatch(cert2.iso_label);
                        if (normalizedTargetISO.indexOf(normCert2) !== -1 || normCert2.indexOf(normalizedTargetISO) !== -1) {
                            certData = cert2;
                            break;
                        }
                    }
                }
            }

            if (!certData && isoLabel) {
                var targetMatch = isoLabel.match(/\d+/g);
                if (targetMatch && targetMatch.length > 0) {
                    var targetNum = targetMatch[0];
                    for (var k = 0; k < certificates.length; k++) {
                        var cert3 = certificates[k];
                        if (cert3 && cert3.iso_label) {
                            var certNums = cert3.iso_label.match(/\d+/g);
                            if (certNums && certNums.length > 0 && certNums[0] === targetNum) {
                                certData = cert3;
                                break;
                            }
                        }
                    }
                }
            }

            // If we still don't have an exact ISO match, try index-based mapping
            if (!certData && typeof isoIndex === 'number' && isoIndex >= 0 && isoIndex < certificates.length) {
                certData = certificates[isoIndex];
            }

            // Special case: only ONE CA certificate exists but multiple ISO sections
            // (isoIndex > 0). In this scenario, derive additional certificate
            // numbers from the base CA certificate by incrementing the numeric
            // suffix, similar to how CA generates per-ISO numbers on the CDC form.
            if (!certData && certificates.length === 1 && typeof isoIndex === 'number' && isoIndex > 0) {
                var baseCert = certificates[0] && certificates[0].certificate_no
                    ? certificates[0].certificate_no.toString()
                    : '';
                if (baseCert !== '') {
                    var match = baseCert.match(/^(.*)(\d+)$/);
                    if (match) {
                        var prefix = match[1];
                        var number = parseInt(match[2], 10);
                        if (!isNaN(number)) {
                            var newNumber = number + isoIndex;
                            var originalLength = match[2].length;
                            var newNumberStr = newNumber.toString();
                            if (newNumberStr.length < originalLength) {
                                newNumberStr = match[2].substring(0, originalLength - newNumberStr.length) + newNumberStr;
                            }
                            return $.trim(prefix + newNumberStr);
                        }
                    } else {
                        // If no numeric suffix, append index directly
                        return $.trim(baseCert + isoIndex);
                    }
                }
            }

            if (!certData) {
                certData = certificates[0];
            }
        } else if (data.certificate_no) {
            // Only a single certificate returned (no certificates array)
            // If multiple ISO sections are present (isoIndex > 0), derive
            // distinct numbers by incrementing the numeric suffix.
            var baseCertNo = data.certificate_no.toString();
            if (typeof isoIndex === 'number' && isoIndex > 0) {
                var matchSingle = baseCertNo.match(/^(.*)(\d+)$/);
                if (matchSingle) {
                    var prefixSingle = matchSingle[1];
                    var numSingle = parseInt(matchSingle[2], 10);
                    if (!isNaN(numSingle)) {
                        var derived = numSingle + isoIndex;
                        var origLen = matchSingle[2].length;
                        var derivedStr = derived.toString();
                        if (derivedStr.length < origLen) {
                            derivedStr = matchSingle[2].substring(0, origLen - derivedStr.length) + derivedStr;
                        }
                        return $.trim(prefixSingle + derivedStr);
                    }
                }
                // Fallback: append index
                return $.trim(baseCertNo + isoIndex);
            }
            certData = { certificate_no: baseCertNo };
        }

        if (!certData || !certData.certificate_no) {
            return '';
        }

        return $.trim(certData.certificate_no.toString());
    }

    function populateCdcOfficeUseSections(entries) {
        if (!Array.isArray(entries) || !entries.length) {
            return;
        }

        console.log('=== Populating CDC Office Use Sections ===');
        console.log('Entries to populate:', entries);
        console.log('Service type:', $cdcbyservice);

        var container = $('#cdcOfficeContainer');

        // For ALL service types: When loading saved data with multiple entries, create sections for ALL saved entries
        if (entries.length > 0) {
            console.log('Processing', entries.length, 'saved entries for service type:', $cdcbyservice);

            // Extract ISO labels from all entries (keep duplicates - one per entry)
            var savedIsoLabels = [];
            entries.forEach(function (entry, idx) {
                if (entry && entry.iso_label) {
                    var isoLabel = entry.iso_label.trim();
                    savedIsoLabels.push(isoLabel || 'ISO ' + idx);
                } else {
                    savedIsoLabels.push('ISO ' + idx);
                }
            });

            console.log('Creating', entries.length, 'sections for', savedIsoLabels.length, 'entries');
            console.log('ISO labels:', savedIsoLabels);

            // CRITICAL: Ensure we have enough sections - create one per entry
            var currentSections = container.find('.CdcOffice').length;
            console.log('Current sections in DOM:', currentSections, 'Required:', entries.length);

            if (currentSections < entries.length) {
                console.log('Creating', (entries.length - currentSections), 'additional sections');

                // Try using ensureCdcOfficeSections if available
                if (typeof ensureCdcOfficeSections === 'function') {
                    ensureCdcOfficeSections(savedIsoLabels);
                } else {
                    // Fallback: manually clone sections
                    var base = container.find('.CdcOffice').first();
                    if (base.length) {
                        while (container.find('.CdcOffice').length < entries.length) {
                            var clone = base.clone(false);
                            var newIndex = container.find('.CdcOffice').length;
                            clone.attr('data-iso-index', newIndex);
                            clone.data('iso-index', newIndex);

                            // Update name attributes for cloned fields
                            clone.find('[name]').each(function () {
                                var $field = $(this);
                                var oldName = $field.attr('name');
                                if (oldName && oldName.indexOf('office_use') !== -1) {
                                    var newName = oldName.replace(/office_use\[\d+\]/, 'office_use[' + newIndex + ']');
                                    $field.attr('name', newName);
                                }
                            });

                            container.append(clone);
                            console.log('Created section', newIndex, 'via cloning');
                        }
                    }
                }
            }

            // Verify sections were created
            var sectionsAfter = container.find('.CdcOffice');
            console.log('Sections after creation:', sectionsAfter.length);

            if (sectionsAfter.length < entries.length) {
                console.error('❌ ERROR: Only', sectionsAfter.length, 'sections created, need', entries.length);
                // Force create missing sections
                var base = container.find('.CdcOffice').first();
                if (base.length) {
                    while (sectionsAfter.length < entries.length) {
                        var clone = base.clone(false);
                        var newIndex = sectionsAfter.length;
                        clone.attr('data-iso-index', newIndex);
                        clone.data('iso-index', newIndex);

                        // Update name attributes
                        clone.find('[name]').each(function () {
                            var $field = $(this);
                            var oldName = $field.attr('name');
                            if (oldName && oldName.indexOf('office_use') !== -1) {
                                var newName = oldName.replace(/office_use\[\d+\]/, 'office_use[' + newIndex + ']');
                                $field.attr('name', newName);
                            }
                        });

                        container.append(clone);
                        sectionsAfter = container.find('.CdcOffice');
                        console.log('Force created section, total now:', sectionsAfter.length);
                    }
                }
            }

            $('#cdcOfficeContainer').show();

            // Set ISO labels on sections immediately after creation
            var finalSections = container.find('.CdcOffice');
            console.log('Final sections count:', finalSections.length);

            finalSections.each(function (index) {
                if (index < savedIsoLabels.length) {
                    var $sect = $(this);
                    var isoLabel = savedIsoLabels[index];
                    $sect.find('.isoLabelField').val(isoLabel);
                    $sect.find('.isoIndexField').val(index);
                    $sect.attr('data-iso-index', index);
                    $sect.data('iso-index', index);

                    // Show title with ISO label
                    var titleRow = $sect.find('.cdc-office-title');
                    if (savedIsoLabels.length > 1) {
                        titleRow.show();
                        titleRow.find('.cdc-office-iso-label').text(isoLabel);
                    } else {
                        titleRow.hide();
                    }

                    $sect.show();
                    console.log('✓ Created and configured section', index, 'for ISO:', isoLabel);
                }
            });
        }

        // CRITICAL FIX: For RC, match entries to sections by ARRAY INDEX (entries array index = section index)
        // This ensures each entry gets its own section, even if ISO labels are the same
        entries.forEach(function (entry, entryIndex) {
            if (!entry || typeof entry !== 'object') {
                console.warn('Skipping invalid entry at index', entryIndex);
                return;
            }

            var isoLabel = entry.iso_label || '';
            var isoIndex = typeof entry.iso_index !== 'undefined' ? parseInt(entry.iso_index, 10) : entryIndex;
            if (isNaN(isoIndex) || isoIndex < 0) {
                isoIndex = entryIndex;
            }

            console.log('Processing entry', entryIndex, '- ISO:', isoLabel, '- iso_index:', isoIndex);

            // CRITICAL: Use entryIndex to find section (one-to-one mapping)
            var $section = container.find('.CdcOffice').eq(entryIndex);

            // Fallback: Try by iso_index if entryIndex doesn't work
            if (!$section || !$section.length) {
                $section = container.find('.CdcOffice').eq(isoIndex);
                console.log('Using iso_index', isoIndex, 'to find section');
            }

            // Fallback: Try by ISO label
            if ((!$section || !$section.length) && isoLabel) {
                container.find('.CdcOffice').each(function () {
                    var $sect = $(this);
                    var sectIsoLabel = $sect.find('.isoLabelField').val() || '';
                    if (sectIsoLabel.trim() === isoLabel.trim() && !$sect.data('populated')) {
                        $section = $sect;
                        return false; // break
                    }
                });
            }

            if (!$section || !$section.length) {
                console.error('❌ Section not found for entry', entryIndex, '- ISO:', isoLabel, '- iso_index:', isoIndex);
                console.error('Available sections:', container.find('.CdcOffice').length);
                return;
            }

            // Mark as populated to avoid duplicate assignment
            $section.data('populated', true);

            // Update section attributes
            $section.attr('data-iso-index', isoIndex);
            $section.data('iso-index', isoIndex);
            $section.find('.isoIndexField').val(isoIndex);
            $section.find('.isoLabelField').val(isoLabel);

            // Show section and title
            $section.show();
            if (isoLabel) {
                var titleRow = $section.find('.cdc-office-title');
                if (entries.length > 1) {
                    titleRow.show();
                    titleRow.find('.cdc-office-iso-label').text(isoLabel);
                } else {
                    titleRow.hide();
                }
            }
            // this section is added by gaurav oli 
            // Populate all fields
            $section.find('.certificateNo').val(entry.certificate_no || '');
            $section.find('.revCdc').val(entry.rev_no || '').prop('disabled', false);
            $section.find('.revDatecdc').val(formatCdcDate(entry.rev_date));
            $section.find('.expiredate').val(formatCdcDate(entry.expire_date));
            $section.find('.periodicalaudit').val(formatCdcDate(entry.periodical_audit));
            $section.find('.nextaudittype').val(entry.audit_type || '');
            $section.find('.periodicalAuditcoment').val(entry.comments || '');

            $section.data('certRequestBlocked', !!entry.certificate_no);

            console.log('✓ Populated section', entryIndex, 'for ISO:', isoLabel, '- Section visible:', $section.is(':visible'));
        });

        console.log('✓ Populated', entries.length, 'office use sections');

        // Final check: Ensure ALL sections created for entries are visible
        setTimeout(function () {
            var visibleCount = 0;
            var totalSections = container.find('.CdcOffice').length;
            console.log('=== FINAL CHECK: Total sections in DOM:', totalSections, 'Entries:', entries.length);

            // Show ALL sections up to entries.length (one section per entry)
            for (var i = 0; i < entries.length && i < totalSections; i++) {
                var $sect = container.find('.CdcOffice').eq(i);
                if ($sect.length) {
                    $sect.show();
                    var isoLabel = $sect.find('.isoLabelField').val() || '';
                    var certNo = $sect.find('.certificateNo').val() || '';
                    visibleCount++;
                    console.log('✓ Section', i, 'FORCED VISIBLE - ISO:', isoLabel, 'Cert:', certNo);
                }
            }

            console.log('=== FINAL: Total visible sections:', visibleCount, 'out of', entries.length, 'entries ===');

            // Double-check: Count actually visible sections
            var actuallyVisible = container.find('.CdcOffice:visible').length;
            console.log('=== VERIFICATION: Actually visible sections:', actuallyVisible);

            if (actuallyVisible < entries.length) {
                console.error('❌ WARNING: Only', actuallyVisible, 'sections visible, expected', entries.length);
                // Force show all sections again
                container.find('.CdcOffice').slice(0, entries.length).show();
            }
        }, 100);

        // After populating data, check CDC status and initialize datepickers
        setTimeout(function () {
            checkCDCStatus();
            // Initialize date pickers after populating the data
            if (typeof initializeCdcDatepickers === 'function') {
                console.log('CDC office use populated - initializing datepickers');
                initializeCdcDatepickers();
            }
        }, 200);
    }

    var baseCustomerId = <?php echo isset($_GET["customerid"]) ? (int) $_GET["customerid"] : 'null'; ?>;
    var $cdcbyservice = window.$cdcbyservice || "";
    var suppressCertificateRequests = false;

    function updateBaseCustomerId(candidate) {
        if (typeof candidate === 'undefined' || candidate === null) {
            return;
        }
        var parsed = parseInt(candidate, 10);
        if (!isNaN(parsed) && parsed > 0) {
            baseCustomerId = parsed;
        }
    }

    function requestCertificateNumberForSection($section, forceRefresh) {
        if (!$section || !$section.length) {
            return;
        }
        if (!forceRefresh && (suppressCertificateRequests || $section.data('certRequestBlocked'))) {
            return;
        }
        // Always read fresh value from window.$cdcbyservice
        var currentServiceType = window.$cdcbyservice;
        if (!currentServiceType || currentServiceType === '') {
            currentServiceType = $cdcbyservice;
        }
        console.log('requestCertificateNumberForSection - Service Type:', currentServiceType);

        // Allow CA, SA1, SA2, and RC service types to fetch certificate numbers
        if (currentServiceType !== "CA" && currentServiceType !== "SA1" && currentServiceType !== "SA2" && currentServiceType !== "RC") {
            console.log('Skipping certificate request - service type not eligible:', currentServiceType);
            return;
        }
        if (!baseCustomerId) {
            return;
        }
        var dateValue = $.trim($('.Date').val());
        if (dateValue === '') {
            return;
        }

        // For SA1, SA2, and RC, skip the Rev date/Rev No requirement since we're fetching from CA
        var isNonCAService = (currentServiceType === "SA1" || currentServiceType === "SA2" || currentServiceType === "RC");

        // Check if Rev date and Rev No are both filled for this specific section (only for CA service type)
        if (!isNonCAService) {
            var revDateVal = $.trim($section.find('.revDatecdc').val());
            var revNoVal = $.trim($section.find('.revCdc').val());
            if (!forceRefresh && (revDateVal === '' || revNoVal === '')) {
                return;
            }
        }

        var $certificateField = $section.find('.certificateNo').first();
        if (!$certificateField.length) {
            return;
        }
        var isoIndex = parseInt($section.data('iso-index'), 10);
        if (isNaN(isoIndex)) {
            isoIndex = 0;
        }
        // Fallback: if isoIndex is 0 but this is not the first section, use DOM index
        if (isoIndex === 0) {
            var domIndex = $('#cdcOfficeContainer .CdcOffice').index($section);
            if (domIndex > 0) {
                isoIndex = domIndex;
            }
        }
        var isoLabel = $.trim($section.find('.isoLabelField').val());
        var allIsoLabels = [];
        $('#cdcOfficeContainer .isoLabelField').each(function () {
            var labelVal = $.trim($(this).val());
            if (labelVal.length > 0) {
                allIsoLabels.push(labelVal);
            }
        });
        if (!allIsoLabels.length && isoLabel.length > 0) {
            allIsoLabels.push(isoLabel);
        }
        if (!forceRefresh && $.trim($certificateField.val()) !== '') {
            return;
        }
        if (forceRefresh) {
            $certificateField.val('');
            $section.removeData('certRequestBlocked');
        }

        // Note: isNonCAService already defined above based on currentServiceType

        // For CA service type: subsequent ISO sections (index > 0) derive from first section if available
        if (!isNonCAService && isoIndex > 0) {
            var $firstSection = $('#cdcOfficeContainer .CdcOffice[data-iso-index="0"]');
            var firstCertNo = $.trim($firstSection.find('.certificateNo').val());

            if (firstCertNo !== '') {
                // Derive certificate number from first section
                var baseCertNo = firstCertNo;
                var certNoStr = baseCertNo.toString();

                // Extract numeric part from the end
                var match = certNoStr.match(/^(.*)(\d+)$/);
                if (match) {
                    var prefix = match[1];
                    var number = parseInt(match[2], 10);

                    // Increment by current index
                    var newNumber = number + isoIndex;

                    // Preserve leading zeros
                    var originalLength = match[2].length;
                    var newNumberStr = newNumber.toString();
                    if (newNumberStr.length < originalLength) {
                        newNumberStr = match[2].substring(0, originalLength - newNumberStr.length) + newNumberStr;
                    }

                    $certificateField.val(prefix + newNumberStr);
                } else {
                    // If no numeric part found, append the index
                    $certificateField.val(baseCertNo + isoIndex);
                }
                return;
            }
        }

        if ($section.data('certRequestInFlight')) {
            return;
        }
        $section.data('certRequestInFlight', true);

        // Set AJAX URL based on service type (isNonCAService already defined above)
        var ajaxUrl = isNonCAService ? "getCACertificateNumber.php" : "getClientInfo.php";
        var ajaxData = isNonCAService ? {
            customerId: baseCustomerId,
            isoLabel: isoLabel
        } : {
            consultingfirm: '1',
            customer: baseCustomerId,
            certid: $('.crr_Id').val(),
            iso_index: isoIndex,
            iso_label: isoLabel,
            iso_labels: allIsoLabels.join(','),
            refresh: forceRefresh ? 1 : 0
        };

        $.ajax({
            url: ajaxUrl,
            type: "GET",
            dataType: isNonCAService ? "json" : "text",
            data: ajaxData,
            success: function (data) {
                // Handle response from getCACertificateNumber.php for non-CA services
                if (isNonCAService) {
                    console.log('CA Certificate Response for ' + currentServiceType + ':', data);
                    if (!data || !data.success) {
                        console.error('No CA certificate found for ' + currentServiceType + ' service. Response:', data);
                        return;
                    }

                    var caCertNo = resolveCaCertificateNumber(data, isoLabel, isoIndex);

                    // If resolver returned the base certificate (or only one certificate available),
                    // and this is not the first ISO section, derive a unique number by incrementing
                    // the base certificate number's numeric suffix.
                    if (isoIndex > 0) {
                        var baseCertForDerive = '';
                        if (data.certificates && Array.isArray(data.certificates) && data.certificates.length > 0) {
                            baseCertForDerive = data.certificates[0].certificate_no || '';
                        } else if (data.certificate_no) {
                            baseCertForDerive = data.certificate_no;
                        }
                        if (baseCertForDerive) {
                            var derivedCert = deriveCertificateFromBase(baseCertForDerive, isoIndex);
                            if (derivedCert) {
                                caCertNo = derivedCert;
                            }
                        }
                    }

                    if (caCertNo !== '') {
                        $certificateField.val(caCertNo);
                        console.log('✓ Successfully populated CA certificate number for ' + currentServiceType + ': ' + caCertNo);
                    } else {
                        console.warn('Certificate number is empty or no matching ISO found in CA response');
                    }
                    return;
                }

                // Original handling for CA service type
                if (!data || data === 'no certificate') {
                    if (data === 'no certificate') {
                        $section.data('certRequestBlocked', true);
                    }
                    return;
                }
                var resolvedCert = '';
                var parsedValue = data;
                if (typeof parsedValue === 'string') {
                    try {
                        parsedValue = JSON.parse(parsedValue);
                    } catch (err) {
                        parsedValue = parsedValue;
                    }
                }

                if (Array.isArray(parsedValue)) {
                    for (var i = 0; i < parsedValue.length; i++) {
                        var entry = parsedValue[i];
                        if (entry === null || entry === undefined) {
                            continue;
                        }
                        if (typeof entry === 'object') {
                            var matchesIndex = typeof entry.iso_index !== 'undefined' && parseInt(entry.iso_index, 10) === isoIndex;
                            var matchesLabel = isoLabel && typeof entry.iso_label === 'string' && $.trim(entry.iso_label) === isoLabel;
                            if (matchesIndex || matchesLabel) {
                                resolvedCert = entry.certificate_no || '';
                                break;
                            }
                            if (!matchesIndex && !matchesLabel && !resolvedCert && entry.certificate_no) {
                                resolvedCert = entry.certificate_no;
                            }
                        } else {
                            if (i === isoIndex && !resolvedCert) {
                                resolvedCert = entry;
                            }
                        }
                    }
                    if (!resolvedCert && parsedValue.length) {
                        var firstEntry = parsedValue[0];
                        if (firstEntry && typeof firstEntry === 'object' && firstEntry.certificate_no) {
                            resolvedCert = firstEntry.certificate_no;
                        } else if (typeof firstEntry === 'string') {
                            resolvedCert = firstEntry;
                        }
                    }
                } else if (parsedValue && typeof parsedValue === 'object') {
                    if (parsedValue.hasOwnProperty(isoIndex)) {
                        resolvedCert = parsedValue[isoIndex];
                    } else if (isoLabel && parsedValue.hasOwnProperty(isoLabel)) {
                        resolvedCert = parsedValue[isoLabel];
                    } else if (parsedValue.certificate_no) {
                        if (Array.isArray(parsedValue.certificate_no)) {
                            resolvedCert = parsedValue.certificate_no[isoIndex] || parsedValue.certificate_no[0] || '';
                        } else {
                            resolvedCert = parsedValue.certificate_no;
                        }
                    }
                } else if (parsedValue !== null && parsedValue !== undefined) {
                    resolvedCert = parsedValue;
                }

                if (typeof resolvedCert === 'number') {
                    resolvedCert = resolvedCert.toString();
                } else if (typeof resolvedCert !== 'string') {
                    resolvedCert = '';
                }

                resolvedCert = $.trim(resolvedCert.replace(/^"+|"+$/g, ''));
                if (resolvedCert === '') {
                    return;
                }

                $certificateField.val(resolvedCert);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching certificate number:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
            },
            complete: function () {
                $section.removeData('certRequestInFlight');
            }
        });
    }

    function autoPopulateCertificates(forceRefresh) {
        // Always read fresh value from window.$cdcbyservice
        var currentServiceType = window.$cdcbyservice;
        console.log('=== autoPopulateCertificates called ===');
        console.log('window.$cdcbyservice:', window.$cdcbyservice);
        console.log('$cdcbyservice:', $cdcbyservice);
        console.log('currentServiceType:', currentServiceType);
        console.log('forceRefresh:', forceRefresh);

        // If still empty, check $cdcbyservice as fallback
        if (!currentServiceType || currentServiceType === '') {
            currentServiceType = $cdcbyservice;
            console.log('Using fallback $cdcbyservice:', currentServiceType);
        }

        // Allow auto-population for CA, SA1, SA2, and RC service types
        if (currentServiceType !== "CA" && currentServiceType !== "SA1" && currentServiceType !== "SA2" && currentServiceType !== "RC") {
            console.log('Skipping auto-populate - service type not eligible: ' + currentServiceType);
            return;
        }
        var container = $('#cdcOfficeContainer');
        if (!forceRefresh && container.data('skipAutoPopulate')) {
            console.log('Skipping auto-populate - skipAutoPopulate flag is set');
            container.removeData('skipAutoPopulate');
            return;
        }
        console.log('Starting certificate auto-population for ' + currentServiceType);

        // Process sections sequentially to ensure first section completes before others
        var $sections = $('#cdcOfficeContainer .CdcOffice');
        var sectionsArray = $sections.toArray();

        function processSectionSequentially(index) {
            if (index >= sectionsArray.length) {
                return;
            }

            var $section = $(sectionsArray[index]);

            // Skip sections that already have certificate numbers unless force refresh
            if (!forceRefresh && $.trim($section.find('.certificateNo').val()) !== '') {
                processSectionSequentially(index + 1);
                return;
            }

            // Wait a bit for previous section to complete if it's not the first one
            if (index > 0) {
                setTimeout(function () {
                    requestCertificateNumberForSection($section, !!forceRefresh);
                    processSectionSequentially(index + 1);
                }, 300);
            } else {
                requestCertificateNumberForSection($section, !!forceRefresh);
                processSectionSequentially(index + 1);
            }
        }

        processSectionSequentially(0);
    }

    function updateCdcOfficeSections(standardText) {
        var container = $('#cdcOfficeContainer');

        // CRITICAL: If sections were already populated from saved data, skip this function
        if (container.data('hasSavedOfficeData')) {
            console.log('Skipping updateCdcOfficeSections - already populated from saved data');
            return;
        }

        console.log('updateCdcOfficeSections called with standardText:', standardText);

        // CRITICAL FIX: If standardText is empty, try to get it from the covered_for field
        // This handles race conditions where the field hasn't been populated yet
        if (!standardText || standardText.trim() === '') {
            console.log('standardText is empty, reading from .covered_for field...');
            standardText = $('.covered_for').val() || '';
            console.log('Read from field:', standardText);
        }

        // CRITICAL FIX 2: If still empty, this might be an old client - check customer certification/assessment fields
        // Store in window scope for access across handlers
        if (!standardText || standardText.trim() === '') {
            console.log('covered_for still empty, checking window.customerIsoStandards...');
            if (window.customerIsoStandards) {
                standardText = window.customerIsoStandards;
                console.log('Using window.customerIsoStandards:', standardText);
                // Also update the field for consistency
                setCdcCoveredStandards(standardText, window.cdcIsoMetadata || []);
            }
        }

        console.log('Final standardText for parsing:', standardText);
        var isoEntries = getIsoEntries(standardText);
        console.log('Parsed ISO entries:', isoEntries);
        ensureCdcOfficeSections(isoEntries);

        var sections = container.find('.CdcOffice');
        var multipleIso = isoEntries.length > 1;

        sections.each(function (index) {
            var isoLabel = isoEntries[index] || '';
            var titleRow = $(this).find('.cdc-office-title');
            $(this).attr('data-iso-index', index);
            $(this).data('iso-index', index);
            $(this).find('.isoIndexField').val(index);
            $(this).find('.isoLabelField').val(isoLabel);

            if (multipleIso && isoLabel) {
                titleRow.show();
                titleRow.find('.cdc-office-iso-label').text(isoLabel);
            } else {
                titleRow.hide();
                titleRow.find('.cdc-office-iso-label').text('');
            }
        });

        copyCdcValuesToClones();

        // Only auto-populate if no sections have certificate numbers already
        var hasAnyCertificate = false;
        sections.each(function () {
            if ($.trim($(this).find('.certificateNo').val()) !== '') {
                hasAnyCertificate = true;
                return false;
            }
        });

        if (!hasAnyCertificate) {
            autoPopulateCertificates(false);
        }

        // Initialize date pickers for all sections after updating - with delay to ensure DOM is ready
        setTimeout(function () {
            if (typeof initializeCdcDatepickers === 'function') {
                console.log('CDC Office sections updated - initializing datepickers');
                initializeCdcDatepickers();
            }
        }, 150);
    }

    // Function to render ISO decision blocks for RC services
    function renderIsoDecisionBlocks(standardText) {
        // Only show for RC service type
        if ($cdcbyservice !== "RC") {
            $('#iso-decision-member1, #iso-decision-member2, #iso-decision-member3').hide().empty();
            return;
        }

        var isoEntries = getIsoEntries(standardText);

        // If no ISO standards found, hide the blocks
        if (!isoEntries || isoEntries.length === 0) {
            $('#iso-decision-member1, #iso-decision-member2, #iso-decision-member3').hide().empty();
            return;
        }

        // Render for each CDC member
        for (var member = 1; member <= 3; member++) {
            var containerId = '#iso-decision-member' + member;
            var $container = $(containerId);
            $container.empty();

            // Create a table-like structure for ISO decisions
            var html = '<div class="col-sm-12" style="margin-top: 10px; margin-bottom: 10px;">';
            html += '<div style="border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9;">';
            html += '<strong>ISO Standards Decision:</strong><br><br>';

            isoEntries.forEach(function (iso, index) {
                var isoClean = iso.trim();
                if (isoClean) {
                    var radioName = 'iso_decision_m' + member + '_' + index;
                    html += '<div style="margin-bottom: 8px;">';
                    html += '<span style="display: inline-block; min-width: 120px; font-weight: 500;">' + isoClean + ':</span>';
                    html += '<input type="radio" class="iso-radio" name="' + radioName + '" id="' + radioName + '_yes" value="Yes" data-member="' + member + '" data-iso="' + isoClean + '"> Yes &emsp;';
                    html += '<input type="radio" class="iso-radio" name="' + radioName + '" id="' + radioName + '_no" value="No" data-member="' + member + '" data-iso="' + isoClean + '"> No';
                    html += '</div>';
                }
            });

            html += '</div></div>';
            $container.html(html);
            $container.show();
        }

        // Attach event listeners to ISO decision radio buttons
        $(document).off('change.isoDecision', 'input.iso-radio');
        $(document).on('change.isoDecision', 'input.iso-radio', function () {
            var $radio = $(this);
            var isoStandard = $radio.data('iso');
            var decision = $radio.val();
            var member = $radio.data('member');

            console.log('=== ISO DECISION CHANGED ===');
            console.log('Member:', member, 'ISO:', isoStandard, 'Decision:', decision);

            // Update FOR OFFICE USE section based on new decisions
            setTimeout(function () {
                console.log('Triggering FOR OFFICE update...');
                updateForOfficeBasedOnIsoDecisions();
            }, 200);
        });

        // Also attach click event for immediate response
        $(document).off('click.isoDecision', 'input.iso-radio');
        $(document).on('click.isoDecision', 'input.iso-radio', function () {
            var $radio = $(this);
            setTimeout(function () {
                if ($radio.is(':checked')) {
                    console.log('Radio clicked and checked, triggering update...');
                    updateForOfficeBasedOnIsoDecisions();
                }
            }, 300);
        });

        console.log('ISO decision blocks rendered for RC service with ' + isoEntries.length + ' standards');
    }

    // Function to populate existing ISO decisions when editing
    function populateIsoDecisions(isoDecisions) {
        if (!isoDecisions || !Array.isArray(isoDecisions) || isoDecisions.length === 0) {
            return;
        }

        isoDecisions.forEach(function (decision) {
            var member = decision.cdc_member;
            var iso = decision.iso_standard;
            var value = decision.decision;

            // Find the radio button and check it
            $('input.iso-radio[data-member="' + member + '"][data-iso="' + iso + '"][value="' + value + '"]').prop('checked', true);
        });

        console.log('Populated ' + isoDecisions.length + ' ISO decisions');

        // After populating decisions, update FOR OFFICE USE section
        setTimeout(function () {
            updateForOfficeBasedOnIsoDecisions();
        }, 200);
    }

    // Function to collect ISO decisions before form submission
    function collectIsoDecisions() {
        var decisions = [];

        $('input.iso-radio:checked').each(function () {
            var $radio = $(this);
            decisions.push({
                cdc_member: $radio.data('member'),
                iso_standard: $radio.data('iso'),
                decision: $radio.val()
            });
        });

        return decisions;
    }

    // Function to get all ISOs where ALL 3 CDC members have selected "Yes"
    function getApprovedIsos() {
        var approvedIsos = [];
        var isoDecisions = {}; // Track decisions for each ISO by member

        console.log('=== Checking for approved ISOs (all 3 members must say Yes) ===');

        // Collect all decisions
        $('input.iso-radio:checked').each(function () {
            var $radio = $(this);
            var decision = $radio.val();
            var iso = $radio.data('iso');
            var member = $radio.data('member');

            console.log('Found checked radio - Member:', member, 'ISO:', iso, 'Decision:', decision);

            // Initialize ISO tracking if not exists
            if (!isoDecisions[iso]) {
                isoDecisions[iso] = {
                    member1: null,
                    member2: null,
                    member3: null
                };
            }

            // Store decision for this member
            isoDecisions[iso]['member' + member] = decision;
        });

        // Check which ISOs have all 3 members saying "Yes"
        for (var iso in isoDecisions) {
            var decisions = isoDecisions[iso];
            console.log('ISO:', iso, 'Decisions:', decisions);

            if (decisions.member1 === 'Yes' &&
                decisions.member2 === 'Yes' &&
                decisions.member3 === 'Yes') {
                approvedIsos.push(iso);
                console.log('✓ All 3 members approved ISO:', iso);
            } else {
                console.log('✗ Not all members approved ISO:', iso,
                    '(M1:', decisions.member1, 'M2:', decisions.member2, 'M3:', decisions.member3 + ')');
            }
        }

        console.log('Final approved ISOs (all 3 members said Yes):', approvedIsos);
        return approvedIsos;
    }

    // Function to update FOR OFFICE USE section based on ISO decisions
    function updateForOfficeBasedOnIsoDecisions() {
        // Only for RC service type
        if ($cdcbyservice !== "RC") {
            return;
        }

        console.log('=== Updating FOR OFFICE USE based on ISO decisions ===');

        // Get all available ISOs from the covered_for field
        var currentCoveredFor = $('.covered_for').val() || '';
        var allIsoEntries = getIsoEntries(currentCoveredFor);

        // If no ISOs found, try to get from other potential fields
        if (allIsoEntries.length === 0) {
            console.log('No ISOs found in covered_for, checking other fields...');
            var altFields = ['.assessment_standard', '.certification', '.standard'];
            for (var i = 0; i < altFields.length && allIsoEntries.length === 0; i++) {
                var fieldValue = $(altFields[i]).val();
                if (fieldValue) {
                    allIsoEntries = getIsoEntries(fieldValue);
                    if (allIsoEntries.length > 0) {
                        console.log('Found ISOs in field', altFields[i], ':', allIsoEntries);
                        break;
                    }
                }
            }
        }

        console.log('All available ISOs:', allIsoEntries);

        if (allIsoEntries.length === 0) {
            console.log('No ISOs found in any standard field - hiding FOR OFFICE sections');
            $('#cdcOfficeContainer').hide();
            return;
        }

        // NEW ROBUST LOGIC: Check ISO approval status for each ISO individually
        console.log('=== CHECKING ISO APPROVAL STATUS ===');
        var approvedIsos = [];

        allIsoEntries.forEach(function (iso) {
            var isoClean = iso.trim();
            if (!isoClean) return;

            console.log('Checking approval for ISO:', isoClean);

            // Check votes from all 3 members for this specific ISO
            var member1Vote = null;
            var member2Vote = null;
            var member3Vote = null;

            // Get vote from Member 1
            var $m1Radio = $('input.iso-radio[data-member="1"][data-iso="' + isoClean + '"]:checked');
            if ($m1Radio.length > 0) {
                member1Vote = $m1Radio.val();
                console.log('  Member 1 vote:', member1Vote);
            }

            // Get vote from Member 2
            var $m2Radio = $('input.iso-radio[data-member="2"][data-iso="' + isoClean + '"]:checked');
            if ($m2Radio.length > 0) {
                member2Vote = $m2Radio.val();
                console.log('  Member 2 vote:', member2Vote);
            }

            // Get vote from Member 3
            var $m3Radio = $('input.iso-radio[data-member="3"][data-iso="' + isoClean + '"]:checked');
            if ($m3Radio.length > 0) {
                member3Vote = $m3Radio.val();
                console.log('  Member 3 vote:', member3Vote);
            }

            // LOGIC: Show ISO in FOR OFFICE only if ALL members voted "Yes"
            // If ANY member voted "No", don't show it
            // If not all members have voted yet, don't show it
            var allVotedYes = (member1Vote === 'Yes' && member2Vote === 'Yes' && member3Vote === 'Yes');
            var anyVotedNo = (member1Vote === 'No' || member2Vote === 'No' || member3Vote === 'No');

            if (allVotedYes && !anyVotedNo) {
                console.log('  ✓ ISO APPROVED:', isoClean, '- All members voted Yes');
                approvedIsos.push(isoClean);
            } else if (anyVotedNo) {
                console.log('  ✗ ISO REJECTED:', isoClean, '- At least one member voted No');
            } else {
                console.log('  ⏸ ISO PENDING:', isoClean, '- Not all members have voted yet');
            }
        });

        console.log('=== APPROVED ISOs:', approvedIsos);

        // If no ISOs are approved, hide the entire FOR OFFICE section
        if (approvedIsos.length === 0) {
            console.log('No ISOs approved by all members - hiding FOR OFFICE section');
            $('#cdcOfficeContainer').hide();
            return;
        }

        // Show the container since we have approved ISOs
        console.log('✓ Found', approvedIsos.length, 'approved ISOs - showing FOR OFFICE section');
        $('#cdcOfficeContainer').show();

        // Create sections ONLY for approved ISOs
        console.log('Creating sections for approved ISOs...');
        ensureCdcOfficeSections(approvedIsos);

        // Update section labels and populate data ONLY for approved ISOs
        var $sections = $('#cdcOfficeContainer .CdcOffice');
        console.log('Total sections found:', $sections.length);

        approvedIsos.forEach(function (iso, index) {
            var $section = $sections.eq(index);

            if ($section.length) {
                console.log('Setting up section', index, 'for approved ISO:', iso);

                // IMPORTANT: Clear certificate fields to prevent cross-contamination between ISOs
                // This ensures each ISO gets fresh data without residual values from other ISOs
                var existingCertNo = $.trim($section.find('.certificateNo').val());
                var existingRevDate = $.trim($section.find('.revDatecdc').val());
                var existingRevNo = $.trim($section.find('.revCdc').val());

                // Only clear if this is a freshly assigned section (no saved CDC data)
                // Check if this section's ISO label is changing
                var currentIsoLabel = $.trim($section.find('.isoLabelField').val());
                if (currentIsoLabel !== iso) {
                    console.log('ISO label changed from', currentIsoLabel, 'to', iso, '- clearing fields');
                    $section.find('.certificateNo').val('');
                    $section.find('.revDatecdc').val('');
                    $section.find('.revCdc').val('');
                    $section.find('.expiredate').val('');
                }

                // Set the ISO label for this section
                $section.find('.isoLabelField').val(iso);
                $section.attr('data-iso-index', index);
                $section.data('iso-index', index);
                $section.find('.isoIndexField').val(index);

                // Show the section with ISO label
                var titleRow = $section.find('.cdc-office-title');
                if (approvedIsos.length > 1) {
                    titleRow.show();
                    titleRow.find('.cdc-office-iso-label').text(iso);
                } else {
                    titleRow.hide();
                }

                $section.show();

                // Populate CA certificate data for this ISO with index for certificate increment
                console.log('Populating CA certificate data for:', iso, 'at index:', index);
                populateFromCACertificate($section, iso, index);
            } else {
                console.warn('No section found for index', index);
            }
        });

        // Hide any extra sections beyond approved ISOs
        for (var i = approvedIsos.length; i < $sections.length; i++) {
            console.log('Hiding extra section at index:', i);
            $sections.eq(i).hide();
        }

        console.log('✓ FOR OFFICE sections updated for', approvedIsos.length, 'approved ISOs');

        // Ensure all cloned inputs keep unique office_use indexes
        copyCdcValuesToClones();
    }

    // Function to populate a FOR OFFICE section with CA certificate data
    // isoIndex: the index of this ISO in the approved ISOs list (0, 1, 2, ...) - used to increment certificate number
    function populateFromCACertificate($section, isoLabel, isoIndex) {
        if (!baseCustomerId) {
            console.error('No customer ID available');
            return;
        }

        // Default index to 0 if not provided
        isoIndex = isoIndex || 0;

        // Check if this section already has data (when editing existing CDC)
        var existingCertNo = $.trim($section.find('.certificateNo').val());
        var existingRevDate = $.trim($section.find('.revDatecdc').val());
        var existingRevNo = $.trim($section.find('.revCdc').val());

        // If all key fields are already filled, don't overwrite (preserve existing data)
        if (existingCertNo !== '' && existingRevDate !== '' && existingRevNo !== '') {
            console.log('Section already has complete data, skipping CA fetch for:', isoLabel);
            return;
        }

        console.log('Fetching CA certificate for ISO:', isoLabel, 'with index:', isoIndex);

        $.ajax({
            url: "getCACertificateNumber.php",
            type: "GET",
            dataType: "json",
            data: {
                customerId: baseCustomerId,
                isoLabel: isoLabel
            },
            success: function (data) {
                console.log('CA Certificate response:', data);

                if (!data || !data.success) {
                    console.warn('No CA certificate found for ISO:', isoLabel);
                    return;
                }

                // ROBUST ISO-SPECIFIC CERTIFICATE MATCHING
                var certData = null;

                console.log('=== FINDING ISO-SPECIFIC CERTIFICATE ===');
                console.log('Looking for ISO:', isoLabel);
                console.log('Available certificates:', data.certificates);

                // Helper function to normalize ISO labels for comparison
                function normalizeIsoLabel(label) {
                    if (!label) return '';
                    // Remove common prefixes/suffixes and spaces, keep only the core identifier
                    return label.toLowerCase()
                        .replace(/iso[\s-]*/gi, '')  // Remove "ISO" prefix
                        .replace(/[\s:]/g, '')       // Remove spaces and colons
                        .trim();
                }

                // If multiple certificates are returned, find the one matching this ISO
                if (data.certificates && data.certificates.length > 0) {
                    console.log('Found', data.certificates.length, 'certificates in response');

                    var normalizedTargetISO = normalizeIsoLabel(isoLabel);
                    console.log('Normalized target ISO:', normalizedTargetISO);

                    // ROBUST MATCHING: Try multiple matching strategies

                    // Strategy 1: Exact normalized match (best)
                    for (var i = 0; i < data.certificates.length; i++) {
                        var cert = data.certificates[i];
                        if (cert.iso_label) {
                            var normalizedCertISO = normalizeIsoLabel(cert.iso_label);
                            if (normalizedCertISO === normalizedTargetISO) {
                                certData = cert;
                                console.log('✓ MATCH Strategy 1 (Exact normalized):', cert.certificate_no, 'for ISO:', cert.iso_label);
                                break;
                            }
                        }
                    }

                    // Strategy 2: Partial match - target ISO contains cert ISO or vice versa
                    if (!certData) {
                        for (var i = 0; i < data.certificates.length; i++) {
                            var cert = data.certificates[i];
                            if (cert.iso_label && isoLabel) {
                                var normalizedCertISO = normalizeIsoLabel(cert.iso_label);

                                // Check if one contains the other
                                if (normalizedTargetISO.indexOf(normalizedCertISO) !== -1 ||
                                    normalizedCertISO.indexOf(normalizedTargetISO) !== -1) {
                                    certData = cert;
                                    console.log('✓ MATCH Strategy 2 (Partial):', cert.certificate_no, 'for ISO:', cert.iso_label);
                                    break;
                                }
                            }
                        }
                    }

                    // Strategy 3: Extract version numbers and compare (e.g., 45001, 9001)
                    if (!certData) {
                        var targetNumber = isoLabel.match(/\d+/g);
                        if (targetNumber && targetNumber.length > 0) {
                            var targetNum = targetNumber[0]; // Get first number (main ISO number)
                            console.log('Looking for ISO number:', targetNum);

                            for (var i = 0; i < data.certificates.length; i++) {
                                var cert = data.certificates[i];
                                if (cert.iso_label) {
                                    var certNumber = cert.iso_label.match(/\d+/g);
                                    if (certNumber && certNumber.length > 0 && certNumber[0] === targetNum) {
                                        certData = cert;
                                        console.log('✓ MATCH Strategy 3 (Number match):', cert.certificate_no, 'for ISO:', cert.iso_label);
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    // If still no match and only one certificate available, use it
                    if (!certData && data.certificates.length === 1) {
                        certData = data.certificates[0];
                        console.log('✓ Single certificate available, using:', certData.certificate_no);
                    }

                    // If still no match found, log error - DO NOT use random fallback
                    if (!certData) {
                        console.error('✗ NO MATCHING CERTIFICATE FOUND for ISO:', isoLabel);
                        console.error('Available ISOs in certificates:', data.certificates.map(c => c.iso_label).join(', '));
                        // Don't populate any data - better to show empty than wrong certificate
                        return;
                    }
                } else {
                    // Backward compatibility: use the default fields if no certificates array
                    certData = {
                        certificate_no: data.certificate_no,
                        rev_date: data.rev_date,
                        rev_no: data.rev_no,
                        expire_date: data.expire_date,
                        iso_label: data.iso_label
                    };
                    console.log('Using backward compatibility fields');
                }

                console.log('=== END CERTIFICATE SEARCH ===');

                // Populate only empty fields to avoid overwriting user edits
                var $certField = $section.find('.certificateNo');
                var currentCertValue = $.trim($certField.val());
                console.log('Current cert field value for ISO', isoLabel, ':', currentCertValue);

                if (certData && certData.certificate_no && currentCertValue === '') {
                    var certNo = certData.certificate_no.toString().trim();
                    $certField.val(certNo);
                    console.log('✓ Set certificate_no to:', certNo, 'for ISO:', isoLabel);
                } else if (!certData || !certData.certificate_no) {
                    console.log('No certificate_no in certificate data');
                } else {
                    console.log('Certificate field already has value:', currentCertValue, '- skipping');
                }

                var $revDateField = $section.find('.revDatecdc');
                if (certData && certData.rev_date && certData.rev_date !== '0000-00-00' && certData.rev_date !== '' && $.trim($revDateField.val()) === '') {
                    var formattedRevDate = formatDate(certData.rev_date);
                    $revDateField.val(formattedRevDate);
                    console.log('Set rev_date:', formattedRevDate);
                }

                var $revNoField = $section.find('.revCdc');
                if (certData && certData.rev_no && $.trim($revNoField.val()) === '') {
                    $revNoField.val(certData.rev_no).prop('disabled', false);
                    console.log('Set rev_no:', certData.rev_no);
                }

                var $expireField = $section.find('.expiredate');
                if (certData && certData.expire_date && certData.expire_date !== '0000-00-00' && certData.expire_date !== '' && $.trim($expireField.val()) === '') {
                    var formattedExpireDate = formatDate(certData.expire_date);
                    $expireField.val(formattedExpireDate);
                    console.log('Set expire_date:', formattedExpireDate);
                }

                // Set audit type to RC for this cycle (only if empty)
                var $auditTypeField = $section.find('.nextaudittype');
                if ($.trim($auditTypeField.val()) === '') {
                    $auditTypeField.val('RC');
                }

                // Calculate next periodical audit (1 year from rev date) - only if field is empty
                var $periodicalField = $section.find('.periodicalaudit');
                if ($.trim($periodicalField.val()) === '' && certData && certData.rev_date && certData.rev_date !== '0000-00-00' && certData.rev_date !== '') {
                    try {
                        var revDateMoment = moment(certData.rev_date);
                        if (revDateMoment.isValid()) {
                            var nextAuditMoment = revDateMoment.clone().add(1, 'years').subtract(1, 'day');
                            var nextAuditFormatted = nextAuditMoment.format('DD.MM.YYYY');
                            $periodicalField.val(nextAuditFormatted);
                            console.log('Set next_periodical_audit:', nextAuditFormatted);
                        }
                    } catch (e) {
                        console.error('Error calculating next periodical audit:', e);
                    }
                }

                console.log('✓ Successfully populated FOR OFFICE section from CA certificate');

                // Initialize datepickers for this section after populating
                setTimeout(function () {
                    if (typeof initializeCdcDatepickers === 'function') {
                        initializeCdcDatepickers();
                    }
                }, 100);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching CA certificate for ISO ' + isoLabel + ':', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    // Helper function to format date from database format to DD.MM.YYYY
    function formatDate(dateStr) {
        if (!dateStr || dateStr === '0000-00-00' || dateStr === '') {
            return '';
        }

        // If already in DD.MM.YYYY format, return as is
        if (dateStr.match(/^\d{2}\.\d{2}\.\d{4}$/)) {
            return dateStr;
        }

        // Convert from YYYY-MM-DD to DD.MM.YYYY
        var dateMoment = moment(dateStr);
        if (dateMoment.isValid()) {
            return dateMoment.format('DD.MM.YYYY');
        }

        return dateStr;
    }

    // Manual trigger function for testing (expose to window)
    window.manualTriggerForOfficeUpdate = function () {
        console.log('=== MANUAL TRIGGER FOR OFFICE UPDATE ===');
        updateForOfficeBasedOnIsoDecisions();
    };

    // Expose other functions for debugging
    window.debugGetApprovedIsos = function () {
        return getApprovedIsos();
    };

    window.debugCheckIsoRadios = function () {
        console.log('=== DEBUG: All ISO Radio Buttons ===');
        var isoSummary = {};

        $('input.iso-radio').each(function () {
            var $radio = $(this);
            var iso = $radio.data('iso');
            var member = $radio.data('member');
            var value = $radio.val();
            var checked = $radio.is(':checked');

            if (!isoSummary[iso]) {
                isoSummary[iso] = { member1: null, member2: null, member3: null };
            }

            if (checked) {
                isoSummary[iso]['member' + member] = value;
            }

            console.log('Radio:', {
                member: member,
                iso: iso,
                value: value,
                checked: checked,
                name: $radio.attr('name'),
                id: $radio.attr('id')
            });
        });

        console.log('\n=== SUMMARY BY ISO ===');
        for (var iso in isoSummary) {
            var decisions = isoSummary[iso];
            var allYes = (decisions.member1 === 'Yes' && decisions.member2 === 'Yes' && decisions.member3 === 'Yes');
            console.log(iso + ':', decisions, allYes ? '✓ ALL YES' : '✗ NOT ALL YES');
        }
    };


    $(function () {
        updateCdcOfficeSections($('.covered_for').val());

        $('.covered_for').on('input change', function () {
            updateCdcOfficeSections($(this).val());
        });
        $('.covered_for').trigger('change');
        $('.date-restore2').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $(".date-restore2").keydown(function (e) {
            //            e.preventDefault();
        });

        $('.date-restore1').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $(".date-restore1").keydown(function (e) {
            //            e.preventDefault();
        });

        $('.review-date-1').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $(".review-date-1").keydown(function (e) {
            //            e.preventDefault();
        });

        $('.audit_performance').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $(".audit_performance").keydown(function (e) {
            //            e.preventDefault();
        });
        $('#stage2date').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#stage2date").keydown(function (e) {
            //            e.preventDefault();
        });

        // Removed audit_dateII datepicker as the field was removed

        $('#revCdc').prop('disabled', true);

        // Function to initialize date pickers for CDC office use sections
        function initializeCdcDatepickers() {
            console.log('=== Initializing CDC datepickers ===');

            // Find all CdcOffice sections
            var allSections = $('#cdcOfficeContainer .CdcOffice');
            console.log('Found', allSections.length, 'CDC Office sections');

            // Process each section separately
            allSections.each(function (index) {
                var section = $(this);
                console.log('Processing section', index);

                // Find date fields in this section
                var revDateField = section.find('input.revDatecdc');
                var expireDateField = section.find('input.expiredate');
                var periodicalAuditField = section.find('input.periodicalaudit');

                console.log('Section', index, '- Rev date field:', revDateField.attr('id'), 'hasDatepicker:', revDateField.hasClass('hasDatepicker'));
                console.log('Section', index, '- Expire date field:', expireDateField.attr('id'), 'hasDatepicker:', expireDateField.hasClass('hasDatepicker'));
                console.log('Section', index, '- Periodical audit field:', periodicalAuditField.attr('id'), 'hasDatepicker:', periodicalAuditField.hasClass('hasDatepicker'));

                // Destroy existing datepickers first
                if (revDateField.hasClass('hasDatepicker')) {
                    try { revDateField.datepicker('destroy'); } catch (e) { }
                }
                if (expireDateField.hasClass('hasDatepicker')) {
                    try { expireDateField.datepicker('destroy'); } catch (e) { }
                }
                if (periodicalAuditField.hasClass('hasDatepicker')) {
                    try { periodicalAuditField.datepicker('destroy'); } catch (e) { }
                }

                // Initialize revDatecdc
                revDateField.datepicker({
                    minDate: '-100Y',
                    maxDate: '+100Y',
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-20:+100",
                    dateFormat: 'dd.mm.yy',
                    onSelect: function (dateText) {
                        if (dateText !== '') {
                            section.find('.revCdc').prop('disabled', false);

                            // Auto-fill expire date (Rev date + 3 years)
                            try {
                                var revDateMoment = moment(dateText, "DD.MM.YYYY");
                                if (revDateMoment.isValid()) {
                                    // Set expire date (Rev date + 3 years)
                                    var expireDateMoment = revDateMoment.clone().add(3, 'years');
                                    var expireDateFormatted = expireDateMoment.format("DD.MM.YYYY");
                                    section.find('.expiredate').val(expireDateFormatted);
                                    console.log('Auto-filled expire date from datepicker:', expireDateFormatted, 'from rev date:', dateText);

                                    // Set next periodical audit date (Rev date + 1 year - 1 day)
                                    var periodicalAuditMoment = revDateMoment.clone().add(1, 'year').subtract(1, 'day');
                                    var periodicalAuditFormatted = periodicalAuditMoment.format("DD.MM.YYYY");
                                    section.find('.periodicalaudit').val(periodicalAuditFormatted);
                                    console.log('Auto-filled next periodical audit date from datepicker:', periodicalAuditFormatted, 'from rev date:', dateText);
                                }
                            } catch (error) {
                                console.error('Error calculating dates from datepicker:', error);
                            }
                        } else {
                            section.find('.revCdc').prop('disabled', true);
                        }
                        validateCDC();
                    }
                });
                revDateField.off('keydown').on('keydown', function (e) {
                    e.preventDefault();
                });
                console.log('Initialized datepicker on', revDateField.attr('id'));

                // Initialize expiredate
                expireDateField.datepicker({
                    minDate: '-100Y',
                    maxDate: '+100Y',
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-20:+100",
                    dateFormat: 'dd.mm.yy',
                    onSelect: function () {
                        validateCDC();
                    }
                });
                expireDateField.off('keydown').on('keydown', function (e) {
                    e.preventDefault();
                });
                console.log('Initialized datepicker on', expireDateField.attr('id'));

                // Initialize periodicalaudit
                periodicalAuditField.datepicker({
                    minDate: '-100Y',
                    maxDate: '+100Y',
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-20:+100",
                    dateFormat: 'dd.mm.yy',
                    onSelect: function () {
                        validateCDC();
                    }
                });
                periodicalAuditField.off('keydown').on('keydown', function (e) {
                    e.preventDefault();
                });
                console.log('Initialized datepicker on', periodicalAuditField.attr('id'));
            });

            console.log('=== Datepicker initialization complete ===');
        }

        // Initialize on page load
        initializeCdcDatepickers();

        // Add fallback: Initialize datepicker on-demand when user clicks on date fields
        $('#cdcOfficeContainer').on('focus click', '.revDatecdc, .expiredate, .periodicalaudit', function () {
            var $field = $(this);
            var $section = $field.closest('.CdcOffice');

            // Check if datepicker is already initialized
            if (!$field.hasClass('hasDatepicker')) {
                console.log('On-demand datepicker initialization for', $field.attr('class'));

                // Initialize the datepicker
                $field.datepicker({
                    minDate: '-100Y',
                    maxDate: '+100Y',
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-20:+100",
                    dateFormat: 'dd.mm.yy',
                    onSelect: function () {
                        if ($field.hasClass('revDatecdc')) {
                            var dateText = $(this).val();
                            if (dateText !== '') {
                                $section.find('.revCdc').prop('disabled', false);

                                // Auto-fill expire date (Rev date + 3 years)
                                try {
                                    var revDateMoment = moment(dateText, "DD.MM.YYYY");
                                    if (revDateMoment.isValid()) {
                                        // Set expire date (Rev date + 3 years)
                                        var expireDateMoment = revDateMoment.clone().add(3, 'years');
                                        var expireDateFormatted = expireDateMoment.format("DD.MM.YYYY");
                                        $section.find('.expiredate').val(expireDateFormatted);
                                        console.log('Auto-filled expire date from on-demand datepicker:', expireDateFormatted, 'from rev date:', dateText);

                                        // Set next periodical audit date (Rev date + 1 year - 1 day)
                                        var periodicalAuditMoment = revDateMoment.clone().add(1, 'year').subtract(1, 'day');
                                        var periodicalAuditFormatted = periodicalAuditMoment.format("DD.MM.YYYY");
                                        $section.find('.periodicalaudit').val(periodicalAuditFormatted);
                                        console.log('Auto-filled next periodical audit date from on-demand datepicker:', periodicalAuditFormatted, 'from rev date:', dateText);
                                    }
                                } catch (error) {
                                    console.error('Error calculating dates from on-demand datepicker:', error);
                                }
                            } else {
                                $section.find('.revCdc').prop('disabled', true);
                            }
                        }
                        validateCDC();
                    }
                });

                // Prevent keyboard input
                $field.off('keydown').on('keydown', function (e) {
                    e.preventDefault();
                });

                // Open the datepicker immediately
                $field.datepicker('show');
            }
        });

        $('#assDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            onSelect: function () {
                // Validate on date selection
                validateCDC();
            }
        });
        $("#assDate").keydown(function (e) {
            //            e.preventDefault();
        });
        $('.assDate').on('change blur', function () {
            // Validate Assessment/NC Closing Date on change/blur
            if ($(this).val() != "") {
                validateCDC();
            }
        });

        $('#Date').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            onSelect: function () {
                // Validate on date selection
                validateCDC();
            }
        });
        $("#Date").keydown(function (e) {
            //            e.preventDefault();
        });
        $('.Date').on('change blur', function () {
            // Validate CDC Date on change/blur
            if ($(this).val() != "") {
                validateCDC();
            }
        });

        // Auto-fill expire date when Rev date changes (Rev date + 3 years)
        $('#cdcOfficeContainer').on('change blur', '.revDatecdc', function () {
            var $revDateField = $(this);
            var $section = $revDateField.closest('.CdcOffice');
            var $expireDateField = $section.find('.expiredate');
            var revDateValue = $revDateField.val();

            if (revDateValue && revDateValue.trim() !== "") {
                try {
                    // Parse the rev date using moment.js (expected format: dd.mm.yyyy)
                    var revDateMoment = moment(revDateValue, "DD.MM.YYYY");

                    if (revDateMoment.isValid()) {
                        // Set expire date (Rev date + 3 years)
                        var expireDateMoment = revDateMoment.clone().add(3, 'years');
                        var expireDateFormatted = expireDateMoment.format("DD.MM.YYYY");
                        $expireDateField.val(expireDateFormatted);
                        console.log('Auto-filled expire date:', expireDateFormatted, 'from rev date:', revDateValue);

                        // Set next periodical audit date (Rev date + 1 year)
                        var $periodicalAuditField = $section.find('.periodicalaudit');
                        var periodicalAuditMoment = revDateMoment.clone().add(1, 'year');
                        var periodicalAuditFormatted = periodicalAuditMoment.format("DD.MM.YYYY");
                        $periodicalAuditField.val(periodicalAuditFormatted);
                        console.log('Auto-filled next periodical audit date:', periodicalAuditFormatted, 'from rev date:', revDateValue);

                        // Trigger validation after setting both dates
                        validateCDC();
                    }
                } catch (error) {
                    console.error('Error calculating dates:', error);
                }
            }
        });
    });

    function increaseDate($date) {
        var newdate = new Date(moment($date, 'DD.MM.YYYY').format('MM.DD.YYYY'));
        newdate.setDate(newdate.getDate() - 1);
        var nd = new Date(newdate);
        var revdate = moment(nd).add(3, 'year');
        return revdate;
    }

    function increaseDate1year($date) {
        var newdate = new Date(moment($date, 'DD.MM.YYYY').format('MM.DD.YYYY'));
        newdate.setDate(newdate.getDate() - 1);
        var nd = new Date(newdate);
        var revdate = moment(nd).add(1, 'year');
        return revdate;
    }
    function removeUnwantedOptions(selector, optionsToRemove) {
        $(selector + ' option').each(function () {
            const optionText = $(this).text().trim(); // Trim whitespace
            if (optionsToRemove.some(option => option.trim().toLowerCase() === optionText.toLowerCase())) {
                $(this).remove();
            }
        });
    }
    function checkCDCStatus() {
        const cdcStatusMem1G = $('#cdc-status-mem1-g').is(':checked');
        const cdcStatusMem2G = $('#cdc-status-mem2-g').is(':checked');
        const cdcStatusMem3G = $('#cdc-status-mem3-g').is(':checked');

        const cdcOfficeContainer = $('#cdcOfficeContainer');

        // FOR OFFICE USE section should ONLY open when ALL THREE CDC members have "Granted" checked
        if (cdcStatusMem1G && cdcStatusMem2G && cdcStatusMem3G) {
            cdcOfficeContainer.show();
            // Initialize date pickers when the section becomes visible - with delay to ensure rendering
            setTimeout(function () {
                if (typeof initializeCdcDatepickers === 'function') {
                    console.log('CDC Status checked - initializing datepickers');
                    initializeCdcDatepickers();
                }
            }, 100);
        } else {
            cdcOfficeContainer.hide();
        }
    }

    $(document).ready(function () {
        $('#cdcModel').on('shown.bs.modal', function () {
            // Re-initialize chronology validator when modal opens
            DateChronologyValidator.init({
                customerId: $('.customer_Id').val() || $('.customerId').val(),
                typedServiceId: $('.service_Id').val() || $('.serviceId').val(),
                fields: {
                    'nc_closing_date': { input: '.assDate', errorDiv: '#assDateError', errorMsg: '#assDateErrorMsg' },
                    'cdc_date': { input: '.Date', errorDiv: '#DateError', errorMsg: '#DateErrorMsg' },
                    'certificate_issue_date': { input: '.revDatecdc', errorDiv: '#revDatecdcError', errorMsg: '#revDatecdcErrorMsg' }
                },
                submitBtn: '.save_cdc'
            });
        });
    });

    function validateCDC(showAuditPerformanceAlert) {
        showAuditPerformanceAlert = showAuditPerformanceAlert === true;
        console.log('=== validateCDC called ===');
        const applicationDateStr = $('.Date').val();
        // Removed validation for application_received_date and audit_dateII fields

        const parseCdcDate = (value) => {
            if (!value) {
                return null;
            }
            const m = moment(value, 'DD.MM.YYYY', true);
            return m.isValid() ? m : null;
        };

        const applicationDate = parseCdcDate(applicationDateStr);
        var $isError = false;

        // Check if FOR OFFICE USE section is visible and we're not doing a 'Save CDC Only'
        const isCdcOnly = $('.is_cdc_only').val() === '1';
        const isOfficeUseVisible = !isCdcOnly && $('#cdcOfficeContainer').is(':visible');
        console.log('Office section visible for validation:', isOfficeUseVisible);

        // Removed validation rules for Application Received Date and Review Date

        if (showAuditPerformanceAlert) {
            var auditPerformanceFields = [
                { selector: '.ncval', label: 'NC' },
                { selector: '.ofival', label: 'OFI' },
                { selector: '.oplus', label: 'O+ve' }
            ];
            var missingAuditPerformance = [];
            var firstMissingAuditPerformance = null;

            auditPerformanceFields.forEach(function (field) {
                var $field = $(field.selector).first();
                if ($.trim($field.val()) === '') {
                    missingAuditPerformance.push(field.label);
                    $field.addClass('inputHighlight');
                    if (!firstMissingAuditPerformance) {
                        firstMissingAuditPerformance = $field;
                    }
                } else {
                    $field.removeClass('inputHighlight');
                }
            });

            if (missingAuditPerformance.length > 0) {
                $isError = true;
                alert('Please fill value in Audit Performance section (NC, OFI, O+ve).');
                if (firstMissingAuditPerformance) {
                    firstMissingAuditPerformance.focus();
                }
            }
        }

        const auditDateEdit = $('.audit_date_CDC').val();
        const dates = auditDateEdit.split(", ");
        const lastDate = dates[dates.length - 1];
        //        if ($(".Date").val() == "") {
        //            $('#DateErrorMsg').text("Date can not be empty");
        //            $('#DateError').show();
        //            $('#Date').addClass('inputHighlight');
        //            $isError = true;
        //        } else
        // Skip this validation if office section is visible and has data
        var skipDateCheck = false;
        if (isOfficeUseVisible) {
            var hasOfficeData = false;
            $('#cdcOfficeContainer .CdcOffice').each(function () {
                if ($(this).find('.expiredate').val() != "" && $(this).find('.periodicalaudit').val() != "") {
                    hasOfficeData = true;
                    return false; // break loop
                }
            });
            if (hasOfficeData) {
                skipDateCheck = true;
                console.log('Skipping CDC Date check because office section has data');
            }
        }

        if ($(".Date").val() != "" && !skipDateCheck) {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    revDatecdc: $('.Date').val(),
                    serviceID: $('.service_Id').val(),
                    unisvc: $('.uni_serviceid').val(),
                },
                async: false,
                success: function (data) {
                    console.log('checkCustomer.php returned:', data);
                    if (data == 1) {
                        console.log('CDC Date validation FAILED - Date should be greater than stage II audit review date');
                        $('#DateErrorMsg').text("Date should be grearer than stage II audit review date");
                        $('#DateError').show();
                        $('#Date').addClass('inputHighlight');
                        $isError = true;
                    } else {
                        console.log('CDC Date validation PASSED');
                        $('#DateError').hide();
                        $('#Date').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#DateError').hide();
            $('#Date').removeClass('inputHighlight');
        }

        // Only validate FOR OFFICE USE fields if the section is visible
        if (isOfficeUseVisible) {
            console.log('Validating office sections...');
            var sectionCount = $('#cdcOfficeContainer .CdcOffice').length;
            console.log('Number of office sections:', sectionCount);

            // Validate each CdcOffice section separately
            $('#cdcOfficeContainer .CdcOffice').each(function (index) {
                console.log('Checking section', index);
                var $section = $(this);
                var sectionSuffix = index > 0 ? '_iso' + (index + 1) : '';

                // Get fields from this specific section
                var $expiredate = $section.find('.expiredate');
                var $periodicalaudit = $section.find('.periodicalaudit');
                var $revDatecdc = $section.find('.revDatecdc');

                console.log('  Section', index, 'expire date:', $expiredate.val());
                console.log('  Section', index, 'periodical audit:', $periodicalaudit.val());
                console.log('  Section', index, 'rev date:', $revDatecdc.val());

                // Validate expire date
                if ($expiredate.val() == "") {
                    console.log('  Section', index, 'expire date is EMPTY - validation FAILED');
                    $section.find('#expiredateError' + sectionSuffix).show();
                    $section.find('#expiredateErrorMsg' + sectionSuffix).text("Date can not be empty");
                    $expiredate.addClass('inputHighlight');
                    $isError = true;
                } else {
                    $section.find('#expiredateError' + sectionSuffix).hide();
                    $expiredate.removeClass('inputHighlight');
                }

                // Validate periodical audit
                if ($periodicalaudit.val() == "") {
                    console.log('  Section', index, 'periodical audit is EMPTY - validation FAILED');
                    $section.find('#periodicalauditError' + sectionSuffix).show();
                    $section.find('#periodicalauditErrorMsg' + sectionSuffix).text("Date can not be empty");
                    $periodicalaudit.addClass('inputHighlight');
                    $isError = true;
                } else {
                    $section.find('#periodicalauditError' + sectionSuffix).hide();
                    $periodicalaudit.removeClass('inputHighlight');
                }
            });
        }

        // Let DateChronologyValidator handle the chronological checks
        if (DateChronologyValidator.validateAll()) {
            $isError = true;
        }


        console.log('Validation complete. Has errors:', $isError);
        return $isError;
    }

    $(document).ready(function () {
        $('.revCdc').prop('disabled', true);

        // Enable revCdc only when revDatecdc has a value - using event delegation for dynamically added sections
        $('#cdcOfficeContainer').on('input', '.revDatecdc', function () {
            var $section = $(this).closest('.CdcOffice');
            if ($(this).val().trim() !== '') {
                $section.find('.revCdc').prop('disabled', false);
            } else {
                $section.find('.revCdc').prop('disabled', true);
            }
            // Validate on input
            validateCDC();
        });

        // Validate Rev date on change/blur - using event delegation for dynamically added sections
        $('#cdcOfficeContainer').on('change blur', '.revDatecdc', function () {
            if ($(this).val() != "") {
                validateCDC();
            }
        });

        $(".save_cdc").click(function (e) {
            // We only set is_cdc_only to 0 if this click was direct, not triggered programmatically
            if (e.originalEvent !== undefined) {
                $('.is_cdc_only').val('0');
            }

            collectCdcIsoMetadata();
            var hasError = validateCDC(true);
            console.log('=== CDC FORM SUBMISSION ===');
            console.log('Validation result:', hasError);
            console.log('Office section visible:', $('#cdcOfficeContainer').is(':visible'));
            console.log('audit_date_CDC value before submit:', $('.audit_date_CDC').val());
            console.log('Date value before submit:', $('.Date').val());
            console.log('crr_Id value before submission:', $('.crr_Id').val());
            if (hasError) {
                console.log('Form submission prevented due to validation errors');
                e.preventDefault();
                return;
            }
            console.log('✔ Form will be submitted');
        });

        $(".save_cdc_only").click(function (e) {
            e.preventDefault();
            $('.is_cdc_only').val('1');

            // Hide any office-use specific errors that might have been shown previously
            var $officeContainer = $('#cdcOfficeContainer');
            $officeContainer.find('.inputHighlight').removeClass('inputHighlight');
            $officeContainer.find('[id*="Error"]').hide();

            var hasError = validateCDC(true);

            if (!hasError) {
                console.log('✔ Form will be submitted (CDC Only)');
                // Bypass native HTML5 validation for required office fields
                $(this).closest('form').attr('novalidate', 'novalidate');
                // Trigger the real save button, but mark it as triggered so it doesn't reset is_cdc_only
                $(this).closest('form').find('.save_cdc').trigger('click');
            } else {
                console.log('Form submission prevented due to validation errors (CDC Only)');
                $('.is_cdc_only').val('0'); // Reset
            }
        });

        $('#cdcModel form').off('submit.cdcIsoMetadata').on('submit.cdcIsoMetadata', function () {
            collectCdcIsoMetadata();
        });

        $(document).off('change.cdcIsoAccreditation', '#cdcIsoAccreditationList .cdc-iso-accreditation');
        $(document).on('change.cdcIsoAccreditation', '#cdcIsoAccreditationList .cdc-iso-accreditation', function () {
            var $select = $(this);
            var accreditation = normalizeCdcAccreditation($select.val());
            var previousAccreditation = $select.data('previous-accreditation') || '';
            var isoIndex = parseInt($select.attr('data-iso-index'), 10);
            var isoLabel = $select.attr('data-iso-label') || '';
            var crrId = $.trim($('#cdcModel .crr_Id').val());
            var customerId = $.trim($('#cdcModel .customer_Id').val());
            var $feedback = $select.siblings('.cdc-iso-accreditation-feedback');

            if (accreditation !== 'NABCB' && accreditation !== 'SELF') {
                $select.val(previousAccreditation);
                collectCdcIsoMetadata();
                $feedback.removeClass('text-success text-muted').addClass('text-danger')
                    .text('Please select NABCB or SELF.');
                return;
            }

            var confirmChange = function () {
                $select.val(accreditation).data('previous-accreditation', accreditation);
                collectCdcIsoMetadata();

                // New CDC records are saved with the main form submission.
                if (!crrId) {
                    $feedback.removeClass('text-danger text-success').addClass('text-muted')
                        .text('Selected accreditation will be saved with this ISO standard.');
                    return;
                }

                if (!customerId) {
                    $select.val(previousAccreditation).data('previous-accreditation', previousAccreditation);
                    collectCdcIsoMetadata();
                    $feedback.removeClass('text-success text-muted').addClass('text-danger')
                        .text('Customer information is missing.');
                    return;
                }

                $select.prop('disabled', true);
                $feedback.removeClass('text-danger text-success').addClass('text-muted')
                    .text('Updating accreditation...');

                $.ajax({
                    url: 'updateCdcIsoDetails.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        crr_id: crrId,
                        customer_id: customerId,
                        iso_index: isoIndex,
                        iso_label: isoLabel,
                        field: 'accreditation',
                        value: accreditation
                    }
                }).done(function (response) {
                    if (response && response.success) {
                        $select.val(response.value).data('previous-accreditation', response.value);
                        collectCdcIsoMetadata();
                        $('.manual-cdc-accreditation').each(function () {
                            var $headerSelect = $(this);
                            if (
                                String($headerSelect.attr('data-crr-id')) === String(crrId) &&
                                String($headerSelect.attr('data-iso-index')) === String(isoIndex)
                            ) {
                                $headerSelect.val(response.value).attr('data-previous', response.value);
                            }
                        });
                        $feedback.removeClass('text-danger text-muted').addClass('text-success')
                            .text(response.message);
                    } else {
                        $select.val(previousAccreditation).data('previous-accreditation', previousAccreditation);
                        collectCdcIsoMetadata();
                        $feedback.removeClass('text-success text-muted').addClass('text-danger')
                            .text((response && response.message) || 'Unable to update accreditation.');
                    }
                }).fail(function (xhr) {
                    var response = xhr.responseJSON || {};
                    $select.val(previousAccreditation).data('previous-accreditation', previousAccreditation);
                    collectCdcIsoMetadata();
                    $feedback.removeClass('text-success text-muted').addClass('text-danger')
                        .text(response.message || 'Unable to update accreditation.');
                }).always(function () {
                    $select.prop('disabled', false);
                });
            };

            var cancelChange = function () {
                $select.val(previousAccreditation);
                collectCdcIsoMetadata();
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Are you sure you want to change accreditation?',
                    text: 'Accreditation for ' + isoLabel + ' will be changed.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, change it',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        confirmChange();
                    } else {
                        cancelChange();
                    }
                });
            } else if (window.confirm('Are you sure you want to change accreditation?')) {
                confirmChange();
            } else {
                cancelChange();
            }
        });

        $(document).off('change.cdcAccreditation', '#cdcModel .accreditation');
        $(document).on('change.cdcAccreditation', '#cdcModel .accreditation', function () {
            var $select = $(this);
            var accreditation = normalizeCdcAccreditation($select.val());
            var crrId = $.trim($('#cdcModel .crr_Id').val());
            var customerId = $.trim($('#cdcModel .customer_Id').val());
            var $feedback = $('#cdcModel .cdc-accreditation-feedback');
            var previousAccreditation = $select.data('previous-accreditation') || '';

            if (accreditation !== 'NABCB' && accreditation !== 'SELF') {
                $feedback.removeClass('text-success text-muted').addClass('text-danger')
                    .text('Please select NABCB or SELF.');
                return;
            }

            var confirmChange = function () {
                setCdcAccreditation(accreditation);

                // New CDC records are persisted by the main form.
                if (!crrId) {
                    $select.data('previous-accreditation', accreditation);
                    $feedback.removeClass('text-danger text-success').addClass('text-muted')
                        .text('Selected accreditation will be saved with the CDC report.');
                    return;
                }

                if (!customerId) {
                    $feedback.removeClass('text-success text-muted').addClass('text-danger')
                        .text('Customer information is missing.');
                    return;
                }

                $select.prop('disabled', true);
                $feedback.removeClass('text-danger text-success').addClass('text-muted')
                    .text('Updating accreditation...');

                $.ajax({
                    url: 'changeCdcAccreditation.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        crr_id: crrId,
                        customer_id: customerId,
                        iso_label: (getIsoEntries($('.covered_for').val() || '')[0] || ''),
                        accreditation: accreditation
                    }
                }).done(function (response) {
                    if (response && response.success) {
                        setCdcAccreditation(response.accreditation);
                        $select.data('previous-accreditation', response.accreditation);
                        $('.manual-cdc-accreditation').each(function () {
                            var $headerSelect = $(this);
                            if (
                                String($headerSelect.attr('data-crr-id')) === String(crrId) &&
                                String($headerSelect.attr('data-iso-index')) === '0'
                            ) {
                                $headerSelect
                                    .val(response.accreditation)
                                    .attr('data-previous', response.accreditation);
                            }
                        });
                        $feedback.removeClass('text-danger text-muted').addClass('text-success')
                            .text(response.message);
                    } else {
                        $select.val(previousAccreditation);
                        $feedback.removeClass('text-success text-muted').addClass('text-danger')
                            .text((response && response.message) || 'Unable to update accreditation.');
                    }
                }).fail(function (xhr) {
                    var response = xhr.responseJSON || {};
                    $select.val(previousAccreditation);
                    $feedback.removeClass('text-success text-muted').addClass('text-danger')
                        .text(response.message || 'Unable to update accreditation.');
                }).always(function () {
                    $select.prop('disabled', false);
                });
            };

            var cancelChange = function () {
                $select.val(previousAccreditation);
                $feedback.removeClass('text-danger text-success').addClass('text-muted').text('');
            };

            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({
                    title: 'Are you sure you want to change accreditation?',
                    text: 'The selected accreditation will be used in this CDC report and the next workflow.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Change',
                    cancelButtonText: 'Keep Current'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        confirmChange();
                    } else {
                        cancelChange();
                    }
                });
            } else if (window.confirm('Are you sure you want to change accreditation?')) {
                confirmChange();
            } else {
                cancelChange();
            }
        });

        $('#cdcModel').on('shown.bs.modal', function () {
            var currentAccreditation = normalizeCdcAccreditation($('#cdcModel .accreditation').val());
            $('#cdcModel .accreditation').data('previous-accreditation', currentAccreditation);
            $('#cdcModel .cdc-accreditation-feedback').text('').removeClass('text-danger text-success').addClass('text-muted');
        });

        $('.checkm1').change(function () {
            if ((this).checked) {
                $('.checkm1').prop('checked', false);
                $(this).prop('checked', true);
            }
            checkCDCStatus();
        });

        $('.checkm2').change(function () {
            if ((this).checked) {
                $('.checkm2').prop('checked', false);
                $(this).prop('checked', true);
                $("input.checkm3").removeAttr("disabled");
            }
            checkCDCStatus();
        });

        $('.checkm3').change(function () {
            if ((this).checked) {
                $('.checkm3').prop('checked', false);
                $(this).prop('checked', true);
            }
            checkCDCStatus();
        });

        $('.checkrev1').change(function () {
            if ($(this).is(':checked')) {
                $('.checkrev1').not(this).prop('checked', false);
            }
        });

        $('.checkrev2').change(function () {
            if ($(this).is(':checked')) {
                $('.checkrev2').not(this).prop('checked', false);
            }
        });

        $('.checkrev3').change(function () {
            if ((this).checked) {
                $('.checkrev3').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $('.check1').change(function () {
            if ((this).checked) {
                $('.check1').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $('.check2').change(function () {
            if ((this).checked) {
                $('.check2').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $('.check3').change(function () {
            if ((this).checked) {
                $('.check3').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        checkCDCStatus();
        // Select all relevant checkboxes
        $('input[type="checkbox"]').change(function () {
            checkCDCStatus();
        });
        //  code by appa for Review CdcReport Add
        $(document).off('click.cdcModal', '.cdcReport');
        $(document).on('click.cdcModal', '.cdcReport', function () {
            // Clear the saved data flag when opening new CDC
            $('#cdcOfficeContainer').removeData('hasSavedOfficeData');
            $('.crr_Id').val('');
            $('#cdcModel .accreditation').val('');
            $('#iso_metadata').val('');
            window.cdcIsoMetadata = [];

            var customerAttr = $(this).attr('data-customer');
            updateBaseCustomerId(customerAttr);
            $cdcbyservice = (this).hasAttribute('data-svctype') ? $(this).attr('data-svctype') : '';
            $('.service_Id').val($(this).attr('data-service'));
            $('.customer_Id').val($(this).attr('data-customer'));
            $('.uni_serviceid').val($(this).attr('data-id'));
            $('.audit_type').val($(this).attr('audit-data'));
            $(".servicetype").val($(this).attr("data-svctype"));
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    cdccustomer_ID: customerAttr,
                    cdcservice_ID: $(this).attr('data-service'),
                    crsvc_id: $(this).attr('data-service1'),
                },
                success: function (data) {
                    console.log('CDC Modal Data:', data);
                    console.log('Audit Application Data (data[1]):', data[1]);
                    if (data[1]) {
                        console.log('Certification field:', data[1]['certification']);
                        console.log('Assessment Standard field:', data[1]['assessment_standard']);
                    }
                    let leadAuditors = [];
                    let leadAuditors1 = [];
                    let leadAuditors2 = [];
                    let leadAuditors3 = [];
                    let leadAuditors4 = [];

                    if (data[16] && typeof data[16] === 'object') {
                        for (const key in data[16]) {
                            if (data[16].hasOwnProperty(key)) {
                                if (data[16][key]["auditor_category"] == "Team Leader") {
                                    leadAuditors.push(data[16][key].auditor_name);
                                } else if (data[16][key]["auditor_category"] == "Auditor") {
                                    leadAuditors1.push(data[16][key].auditor_name);
                                } else if (data[16][key]["auditor_category"] == "Technical Expert") {
                                    leadAuditors2.push(data[16][key].auditor_name);
                                }
                                else {
                                    const combined = data[16][key].auditor_name + ' (' + data[16][key].auditor_category + ')';
                                    leadAuditors3.push(combined);
                                    leadAuditors4.push(data[16][key].auditor_name);
                                }
                            }
                        }
                    } else {
                        console.error('data[16] is not a valid object or is empty.');
                    }
                    if (data[17] && typeof data[17] === 'object') {
                        for (const key in data[17]) {
                            if (data[17].hasOwnProperty(key)) {
                                if (data[17][key]["auditor_category"] == "Team Leader") {
                                    leadAuditors.push(data[17][key].auditor_name);
                                } else if (data[17][key]["auditor_category"] == "Auditor") {
                                    leadAuditors1.push(data[17][key].auditor_name);
                                } else if (data[17][key]["auditor_category"] == "Technical Expert") {
                                    leadAuditors2.push(data[17][key].auditor_name);
                                }
                                else {
                                    const combined = data[17][key].auditor_name + ' (' + data[17][key].auditor_category + ')';
                                    leadAuditors3.push(combined);
                                    leadAuditors4.push(data[17][key].auditor_name);

                                }
                            }
                        }
                    } else {
                        console.error('data[17] is not a valid object or is empty.');
                    }
                    const uniqueLeadAuditors = [...new Set(leadAuditors)];
                    const uniqueLeadAuditors1 = [...new Set(leadAuditors1)];
                    const uniqueLeadAuditors2 = [...new Set(leadAuditors2)];
                    const uniqueLeadAuditors3 = [...new Set(leadAuditors3)];
                    const uniqueLeadAuditors4 = [...new Set(leadAuditors4)];
                    let leadAuditorString = leadAuditors.join(', ');
                    $leadauditor = leadAuditorString;
                    let auditor = leadAuditors1.join(', ');
                    $auditors = auditor;
                    let technicalexpert = leadAuditors2.join(', ');
                    $technicalexperts = technicalexpert;
                    let anyother = leadAuditors3.join(', ');
                    $anyothers = anyother;
                    let anyother1 = leadAuditors4.join(', ');

                    if (leadAuditorString.length == 0) {
                        $leadauditor = "NA";
                    }
                    if (auditor.length == 0) {
                        $auditors = "NA";
                    }
                    if (technicalexpert.length == 0) {
                        $technicalexperts = "NA";
                    }
                    if (anyother.length == 0) {
                        $anyothers = "NA";
                    }

                    //                    alert(data[15]["id"]);
                    //                    alert(data[0]['certification_stage_date']);
                    //                    $('.audit_dateII').val(moment(data[0]['certification_stage_date']).format('DD.MM.YYYY'));
                    var d1 = moment(data[0]['certification_stage_date']).format('DD.MM.YYYY');
                    var newdate = new Date(moment(d1, 'DD.MM.YYYY').format('MM.DD.YYYY'));
                    //                    alert(data[0]['certification_stage_date']);
                    newdate.setDate(newdate.getDate() - 1);
                    var nd = new Date(newdate);
                    //                    var revdate = moment(nd).add(3, 'year');
                    //                    var nxtPeriodical = moment(nd).add(4, 'year');
                    //                    var nxtExp = moment(nd).add(6, 'year');
                    var nxtPeriodical = "";
                    var nxtExp = "";
                    //                    alert(nd);
                    //alert(data[8]["expiredate"]+" -> "+data[8]['periodicalaudit']);
                    //                    alert(moment(nxtPeriodical).format('DD.MM.YYYY') + " -> " +moment(nxtExp).format('DD.MM.YYYY'))

                    $expCount = (parseInt(data[15]["id"]) + 1);
                    $expAddYear = (3 * $expCount);

                    $nextCount = parseInt(data[15]["id"]);

                    if ($cdcbyservice == "RC") {
                        // Use certification field if it contains multiple ISOs, otherwise use assessment_standard
                        var isoStandards = data[1]['certification'] || data[1]['assessment_standard'] || '';
                        setCdcAccreditation(data[1]['review_accredition']);
                        setCdcCoveredStandards(isoStandards, []);

                        // Store merged ISO list for decision blocks (includes CA+SA+RC ISOs)
                        window.rcMergedIsoList = data[1]['merged_iso_list'] || isoStandards;

                        $addYear = (3 * $nextCount) + 1;

                        nxtPeriodical = moment(nd).add($addYear, 'year');
                        nxtExp = moment(nd).add($expAddYear, 'year')

                        $('.expiredate').val(moment(nxtExp).format('DD.MM.YYYY'));
                        $(".periodicalaudit").val(moment(nxtPeriodical).format('DD.MM.YYYY'));

                    } else if ($cdcbyservice == "SA1") {

                        $addYear = (3 * $nextCount) + 2;

                        nxtPeriodical = moment(nd).add($addYear, 'year');
                        nxtExp = moment(nd).add($expAddYear, 'year');

                        $('.expiredate').val(moment(nxtExp).format('DD.MM.YYYY'));
                        $(".periodicalaudit").val(moment(nxtPeriodical).format('DD.MM.YYYY'));
                        // Use certification field if it contains multiple ISOs, otherwise use assessment_standard
                        var isoStandards = data[1]['certification'] || data[1]['assessment_standard'] || '';
                        setCdcAccreditation(data[1]['review_accredition']);
                        setCdcCoveredStandards(isoStandards, []);

                    } else if ($cdcbyservice == "SA2") {

                        $addYear = (3 * $nextCount) + 3;

                        nxtPeriodical = moment(nd).add($addYear, 'year');
                        nxtExp = moment(nd).add($expAddYear, 'year');

                        $('.expiredate').val(moment(nxtExp).format('DD.MM.YYYY'));
                        $(".periodicalaudit").val(moment(nxtPeriodical).format('DD.MM.YYYY'));

                        // Use certification field if it contains multiple ISOs, otherwise use assessment_standard
                        var isoStandards = data[1]['certification'] || data[1]['assessment_standard'] || '';
                        setCdcAccreditation(data[1]['review_accredition']);
                        setCdcCoveredStandards(isoStandards, []);

                    } else if ($cdcbyservice == "SP") {
                        //alert(data[19]);
                        //                        new code added for special audit ----->>> 19/06/2021
                        $addYear = (3 * $nextCount) + 3;

                        nxtPeriodical = moment(nd).add($addYear, 'year');
                        nxtExp = moment(nd).add($expAddYear, 'year');
                        // Use certification field if it contains multiple ISOs, otherwise use assessment_standard
                        var isoStandards = data[1]['certification'] || data[1]['assessment_standard'] || '';
                        setCdcAccreditation(data[1]['review_accredition']);
                        setCdcCoveredStandards(isoStandards, []);

                        $('.expiredate').val(moment(nxtExp).format('DD.MM.YYYY'));
                        $(".periodicalaudit").val(moment(nxtPeriodical).format('DD.MM.YYYY'));
                    }
                    else {
                        //                        $('.expiredate').val("");
                        // Use certification field if it contains multiple ISOs, otherwise use assessment_standard
                        var isoStandards = data[1]['certification'] || data[1]['assessment_standard'] || '';
                        setCdcAccreditation(data[1]['review_accredition']);
                        setCdcCoveredStandards(isoStandards, []);
                    }

                    console.log('=== CDC ADD MODE - AUDIT DATE POPULATION ===');
                    console.log('Service Type:', $cdcbyservice);

                    // Resolve audit dates with safe fallbacks for old data
                    var resolvedAuditDates = "";

                    if ($cdcbyservice == "CA") {
                        $('.certificateNo').val("");

                        // Collect possible audit date sources (Stage I + Stage II)
                        var caAuditSources = [];
                        if (data[1] && data[1]["audit_dates"]) caAuditSources.push(data[1]["audit_dates"]);
                        if (data[2] && data[2]["audit_dates"]) caAuditSources.push(data[2]["audit_dates"]);

                        console.log('CA - Stage 1 audit_dates:', data[1] ? data[1]["audit_dates"] : 'N/A');
                        console.log('CA - Stage 2 audit_dates:', data[2] ? data[2]["audit_dates"] : 'N/A');

                        resolvedAuditDates = caAuditSources.join(", ");
                    } else {
                        // For RC: Don't set certificate number globally - let ISO-specific increment logic handle it
                        // For SA1/SA2/SP: Set certificate from customer info (legacy behavior)
                        if ($cdcbyservice !== "RC") {
                            $('.certificateNo').val(data[0]['certification_no']);
                            console.log('Non-CA (SA1/SA2/SP) - Setting certificate from customer_info:', data[0]['certification_no']);
                        } else {
                            console.log('RC - Skipping global certificate set, will use ISO-specific incremented certificates');
                        }

                        // For non-CA, primary source is data[5]['audit_dates'] (stage_oneaudit_id)
                        var nonCaSources = [];
                        if (data[5] && data[5]["audit_dates"]) nonCaSources.push(data[5]["audit_dates"]);

                        // If still empty, try additional fallbacks:
                        // - Stage II audit details (data[2] / data[4] equivalents from getClientInfo)
                        if (data[2] && data[2]["audit_dates"]) nonCaSources.push(data[2]["audit_dates"]);
                        if (data[3] && data[3]["audit_dates"]) nonCaSources.push(data[3]["audit_dates"]);

                        // Merge and deduplicate while preserving order
                        resolvedAuditDates = nonCaSources.filter(function (v, i, arr) {
                            return v && arr.indexOf(v) === i;
                        }).join(", ");

                        console.log('Non-CA - Combined audit date sources:', nonCaSources);
                    }

                    // Final safety: never show literal "Invalid date", and trim whitespace
                    if (resolvedAuditDates && typeof resolvedAuditDates === 'string') {
                        resolvedAuditDates = resolvedAuditDates.replace(/Invalid date/gi, '').trim();
                    }

                    $(".audit_date_CDC").val(resolvedAuditDates);
                    console.log('✓ Final audit_date_CDC value:', $(".audit_date_CDC").val());

                    const optionsToRemove = [];

                    // Split each string into an array of values
                    const leadAuditorsArray = leadAuditorString.split(', ');
                    const auditorsArray = auditor.split(', ');
                    const technicalExpertsArray = technicalexpert.split(', ');
                    const anyotherArray = anyother1.split(', ');

                    // Push each value into the optionsToRemove array
                    leadAuditorsArray.forEach(auditor => {
                        optionsToRemove.push(auditor);
                    });
                    auditorsArray.forEach(auditor => {
                        optionsToRemove.push(auditor);
                    });
                    technicalExpertsArray.forEach(expert => {
                        optionsToRemove.push(expert);
                    });
                    anyotherArray.forEach(expert => {
                        optionsToRemove.push(expert);
                    });

                    // Call the function to remove "auditor" and "allauditors"
                    removeUnwantedOptions('.reviewer-1', optionsToRemove);
                    removeUnwantedOptions('.reviewer-2', optionsToRemove);
                    removeUnwantedOptions('.granted-by', optionsToRemove);
                    //                    alert(data[12]['aggrement_no']);
                    $(".auditors").val($auditors);
                    $('.lead_auditor').val($leadauditor);
                    $('.technical_expert').val($technicalexperts);
                    $('.any_ther').val($anyothers);
                    $('.file_No').val(data[12]['aggrement_no']);
                    $('.audited_Organisation').val(data[0]['customer_name']);
                    $('.address').val(data[0]['address']);
                    $('.product_service').val(data[0]['scope']);
                    const pairs = data[0]['aif_nace'].split(",");
                    const extractedParts = pairs.map(pair => {
                        const parts = pair.split("/");
                        return parts[parts.length - 1];
                    });
                    $('.iaf_nace').val(extractedParts);
                    $('.exclusion').val(data[1]['standard_clauses']);
                    //                    alert("Review Date 1 -> " + data[13]["review_date"] + " review date2 -> " + data[14]["review_date"]);
                    //                    if (data[13]["review_date"] == undefined) {
                    //                        $(".review-date-1").val(moment(data[14]["review_date"]).format('DD.MM.YYYY'));
                    //                    } else {
                    //                        $(".review-date-1").val(moment(data[13]["review_date"]).format('DD.MM.YYYY'));
                    //                    }
                    //                    $(".audit_performance").val(moment(data[14]["review_date"]).format('DD.MM.YYYY'));
                    //                    alert(data[12]["review_date"]);
                    //                    if (data[2]['audit_dates'] == undefined) {
                    //                        $(".stage2date").val(data[5]['audit_dates']);
                    //                    } else {
                    //                        $(".stage2date").val(data[2]['audit_dates']);
                    //                    }
                    //                    $('.audit_dateII').val(moment(data[2]['audit_dates']).format('DD.MM.YYYY'));
                    //                    $('.revCdc').val(data[0]['unique_id']);
                    //                    $('.expiredate').val(moment(revdate).format('DD.MM.YYYY'));
                    //                    $('.periodicalaudit').val(moment(nxtPeriodical).format('DD.MM.YYYY'));
                    $('input[type="text"]').trigger('change');
                    $('.address').trigger('change');
                    $('.product_service').trigger('change');
                    if ($cdcbyservice == "CA") {
                        $('.expiredate').val("");
                        $(".periodicalaudit").val("");
                    }
                    $auditType = "";
                    if ($cdcbyservice == "CA") {
                        $auditType = "CA";
                    } else if ($cdcbyservice == "SA1") {
                        $auditType = "SA-I"
                    } else if ($cdcbyservice == "SA2") {
                        $auditType = "SA-II"
                    } else if ($cdcbyservice == "RC") {
                        $auditType = "RC"
                    } else if ($cdcbyservice == "SP") {
                        $auditType = "SP"
                    }

                    $(".nextaudittype").val($auditType);

                    // Check if we have saved office entries
                    var hasSavedOfficeData = data[7] && Array.isArray(data[7]) && data[7].length > 0;

                    // For RC service type, hide FOR OFFICE section initially - UNLESS we have saved data
                    if ($cdcbyservice === "RC") {
                        if (!hasSavedOfficeData) {
                            $('#cdcOfficeContainer').hide();
                            console.log('RC CDC: FOR OFFICE section initially hidden - will show only when all 3 members approve ISOs');
                        } else {
                            console.log('RC CDC: FOR OFFICE section will be shown - has saved data with', data[7].length, 'entries');
                        }
                    } else {
                        // For non-RC services (CA, SA-I, SA-II)
                        if (!hasSavedOfficeData) {
                            // No saved data - initialize based on covered_for field
                            // Add delay to ensure covered_for is populated
                            setTimeout(function () {
                                var coveredForVal = $('.covered_for').val() || '';
                                console.log('Initializing office sections - covered_for:', coveredForVal);
                                updateCdcOfficeSections(coveredForVal);
                            }, 100);
                        } else {
                            // Has saved data - BUT old clients might not have multiple entries in data[7]
                            // even if they have multiple ISOs. So we need to check covered_for field too.
                            console.log('=== OLD CLIENT WITH SAVED OFFICE DATA ===');
                            console.log('Service type:', $cdcbyservice);
                            console.log('Saved entries count:', data[7].length);
                            console.log('Saved entries:', data[7]);

                            // Get ISOs from covered_for field
                            var coveredFor = $('.covered_for').val() || '';
                            console.log('covered_for field value:', coveredFor);

                            var isoEntries = getIsoEntries(coveredFor);
                            console.log('ISOs from getIsoEntries:', isoEntries);
                            console.log('Number of ISOs:', isoEntries.length);

                            // FALLBACK: If covered_for is empty/unparseable, check saved entries for ISO labels
                            if (isoEntries.length === 0 && data[7].length > 0) {
                                console.log('covered_for returned no ISOs, checking saved entries for ISO labels...');
                                data[7].forEach(function (entry) {
                                    if (entry && entry.iso_label) {
                                        isoEntries.push(entry.iso_label);
                                        console.log('Found ISO from saved data:', entry.iso_label);
                                    }
                                });
                                console.log('ISOs from saved entries:', isoEntries);
                            }

                            // FALLBACK 2: If still no ISOs, use saved entries count
                            if (isoEntries.length === 0 && data[7].length > 0) {
                                console.log('No ISO labels found, using saved entries count:', data[7].length);
                                for (var i = 0; i < data[7].length; i++) {
                                    isoEntries.push('ISO ' + (i + 1));
                                }
                            }

                            console.log('Final ISO entries to create sections for:', isoEntries);

                            $('#cdcOfficeContainer').show();

                            // Create sections based on ISO count from covered_for (like new clients)
                            if (isoEntries.length > 0) {
                                ensureCdcOfficeSections(isoEntries);

                                // Set ISO labels on sections
                                var container = $('#cdcOfficeContainer');
                                var sections = container.find('.CdcOffice');
                                sections.each(function (index) {
                                    if (index < isoEntries.length) {
                                        var isoLabel = isoEntries[index];
                                        $(this).find('.isoLabelField').val(isoLabel);
                                        $(this).find('.isoIndexField').val(index);
                                        $(this).attr('data-iso-index', index);
                                        $(this).data('iso-index', index);

                                        var titleRow = $(this).find('.cdc-office-title');
                                        if (isoEntries.length > 1) {
                                            titleRow.show();
                                            titleRow.find('.cdc-office-iso-label').text(isoLabel);
                                        } else {
                                            titleRow.hide();
                                        }
                                        $(this).show();
                                    }
                                });
                            }

                            // Now populate with saved data if available
                            if (data[7].length > 0) {
                                data[7].forEach(function (entry, idx) {
                                    var $section = $('#cdcOfficeContainer .CdcOffice').eq(idx);
                                    if ($section.length && entry) {
                                        $section.find('.certificateNo').val(entry.certificate_no || '');
                                        $section.find('.revCdc').val(entry.rev_no || '').prop('disabled', false);
                                        $section.find('.revDatecdc').val(formatCdcDate(entry.rev_date));
                                        $section.find('.expiredate').val(formatCdcDate(entry.expire_date));
                                        $section.find('.periodicalaudit').val(formatCdcDate(entry.periodical_audit));
                                        $section.find('.nextaudittype').val(entry.audit_type || '');
                                        $section.find('.periodicalAuditcoment').val(entry.comments || '');
                                        console.log('✓ Populated section', idx, 'with saved data');
                                    }
                                });
                            }

                            // Mark as having saved data to prevent updateCdcOfficeSections interference
                            $('#cdcOfficeContainer').data('hasSavedOfficeData', true);

                            console.log('=== OLD CLIENT SETUP COMPLETE - Sections:', $('#cdcOfficeContainer .CdcOffice:visible').length, '===');
                        }
                    }

                    // Render ISO decision blocks for RC services
                    // Use merged ISO list (CA+SA+RC) for decision blocks, not just RC-specific ISOs
                    var isoListForDecisionBlocks = ($cdcbyservice === "RC" && window.rcMergedIsoList) ? window.rcMergedIsoList : $('.covered_for').val();
                    renderIsoDecisionBlocks(isoListForDecisionBlocks);

                    $('#cdcModel').modal('show');

                    // For old clients with saved office data, skip auto-populate to prevent interference
                    if (!hasSavedOfficeData) {
                        autoPopulateCertificates(false);
                    } else {
                        console.log('Skipping autoPopulateCertificates - using saved data');
                    }

                    // Check CDC status after modal is shown
                    setTimeout(function () {
                        checkCDCStatus();
                    }, 200);
                },
                error: function (data, status, err) {
                    alert(status);
                }
            });
            //            $('.update_cdc').hide();
        });

        $('#cdcOfficeContainer').on('click', '.revCdc', function () {
            var $section = $(this).closest('.CdcOffice');
            if ($.trim($section.find('.certificateNo').val()) === '') {
                requestCertificateNumberForSection($section, false);
            }
        });

        $(".Date").change(function () {

            //            $('.expiredate').val("");
            //            var dval = "Invalid date";
            if ($cdcbyservice == "CA") {
                var newdate = new Date(moment($('.Date').val(), 'DD/MM/YYYY').format('MM/DD/YYYY'));
                //            alert(moment($('.Date').val(), 'DD.MM.YYYY').format('MM.DD.YYYY'));
                newdate.setDate(newdate.getDate() - 1);
                var nd = new Date(newdate);
                var revdate = moment(nd).add(3, 'year');
                //            alert(revdate);
                $('.expiredate').val(moment(revdate).format('DD.MM.YYYY'));
                var newdate = new Date(moment($('.Date').val(), 'DD.MM.YYYY').format('MM.DD.YYYY'));
                newdate.setDate(newdate.getDate() - 1);
                var nd = new Date(newdate);
                var nxtPeriodical = moment(nd).add(1, 'year');
                $('.periodicalaudit').val(moment(nxtPeriodical).format('DD.MM.YYYY'));
            }

            var customer = (typeof resolveCustomerId === 'function') ? resolveCustomerId() : (typeof window !== 'undefined' ? window.baseCustomerId : '');
            if (!customer) {
                return;
            }
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    rev_Datecdc: $('.Date').val(),
                    servic_ID: $('.service_Id').val(),
                    uni_svc: $('.uni_serviceid').val(),
                    customer: customer,
                },
                async: false,
                success: function (data) {

                    if ($(".audit_type").val() == "RC") {
                        if (data == '1') {
                            $(".rc-restore").show();
                        }
                    }
                }
            });

            $("input.revDatecdc").removeAttr("readonly");
            $("input.revCdc").removeAttr("readonly");

            // Only auto-populate if no sections have certificate numbers already
            var hasAnyCertificate = false;
            $('#cdcOfficeContainer .CdcOffice').each(function () {
                if ($.trim($(this).find('.certificateNo').val()) !== '') {
                    hasAnyCertificate = true;
                    return false;
                }
            });

            if (!hasAnyCertificate) {
                autoPopulateCertificates(false);
            }

        });

        // Handle Rev date change for individual ISO sections
        $('#cdcOfficeContainer').on('change blur', '.revDatecdc', function () {
            var $section = $(this).closest('.CdcOffice');
            var revDateVal = $.trim($(this).val());
            var revNoVal = $.trim($section.find('.revCdc').val());

            // Only request certificate if both Rev date and Rev No are filled
            if (revDateVal !== '' && revNoVal !== '' && $.trim($section.find('.certificateNo').val()) === '') {
                requestCertificateNumberForSection($section, false);
            }
        });

        // Handle Rev No change for individual ISO sections
        $('#cdcOfficeContainer').on('change blur', '.revCdc', function () {
            var $section = $(this).closest('.CdcOffice');
            var revDateVal = $.trim($section.find('.revDatecdc').val());
            var revNoVal = $.trim($(this).val());

            // Only request certificate if both Rev date and Rev No are filled
            if (revDateVal !== '' && revNoVal !== '' && $.trim($section.find('.certificateNo').val()) === '') {
                requestCertificateNumberForSection($section, false);
            }
        });

        //  code by appa for Review CdcReport Update
        $(document).off('click.cdcModal', '.editcdcReport');
        $(document).on('click.cdcModal', '.editcdcReport', function () {
            // Clear the saved data flag when opening edit CDC - will be set if has saved office data
            $('#cdcOfficeContainer').removeData('hasSavedOfficeData');
            $('#iso_metadata').val('');
            window.cdcIsoMetadata = [];

            var customerAttr = $(this).attr('data-customer');
            updateBaseCustomerId(customerAttr);
            $cdcbyservice = (this).hasAttribute('data-svctype') ? $(this).attr('data-svctype') : '';
            $('.service_Id').val($(this).attr('data-service'));
            $('.customer_Id').val($(this).attr('data-customer'));
            $('.uni_serviceid').val($(this).attr('data-id'));
            $('.crr_Id').val($(this).attr('data-notification'));
            $(".servicetype").val($(this).attr("data-svctype"));
            console.log('CDC Edit - crr_Id set to:', $(this).attr('data-notification'));
            $.ajax({
                url: "reviewReportData.php",
                type: "GET",
                dataType: "json",
                data: {
                    crr_Id: $(this).attr('data-notification'),
                    service: $(this).attr('data-service')
                },
                success: function (data) {
                    console.log('=== EDIT CDC - FULL DATA DUMP ===');
                    console.log('Full data array:', data);
                    console.log('data.length:', data ? data.length : 'N/A');
                    for (var i = 0; i < (data ? data.length : 0); i++) {
                        console.log('data[' + i + ']:', data[i]);
                    }
                    console.log('=== END FULL DATA DUMP ===');

                    if (!Array.isArray(data)) {
                        data = [];
                    }
                    var indexesToNormalise = [0, 1, 2, 3, 4, 5, 6];
                    for (var idx = 0; idx < indexesToNormalise.length; idx++) {
                        var currentIndex = indexesToNormalise[idx];
                        if (!data[currentIndex] || typeof data[currentIndex] !== 'object') {
                            data[currentIndex] = {};
                        }
                    }
                    if (!Array.isArray(data[7])) {
                        data[7] = [];
                    }
                    var hasOfficeEntries = data[7].length > 0;
                    var isNonCAServiceType = ($cdcbyservice === "SA1" || $cdcbyservice === "SA2" || $cdcbyservice === "RC");

                    // For CA service type: suppress auto-populate if office entries exist
                    // For SA1, SA2, RC: don't suppress, we want to fetch from CA
                    suppressCertificateRequests = hasOfficeEntries && !isNonCAServiceType;
                    if (hasOfficeEntries && !isNonCAServiceType) {
                        $('#cdcOfficeContainer').data('skipAutoPopulate', true);
                    }

                    $('.file_No').val(data[0]['file_no']);
                    $('.audited_Organisation').val(data[0]['audit_organization']);
                    $('.address').val(data[0]['address']);
                    $('.product_service').val(data[0]['product_service']);
                    $('.iaf_nace').val(data[0]['iaf_nacf']);
                    $('.exclusion').val(data[0]['exclusion']);
                    $('.audit_type').val(data[0]['audit_type']);

                    // Populate audit dates - prioritize saved CDC audit_date, then fall back to stage audit dates
                    console.log('=== CDC EDIT MODE - AUDIT DATE POPULATION ===');
                    console.log('data[0] audit_date value:', data[0]['audit_date']);
                    console.log('data[5] audit_dates:', data[5] ? data[5]["audit_dates"] : 'N/A');
                    console.log('data[6] audit_dates:', data[6] ? data[6]["audit_dates"] : 'N/A');

                    if (data[0]['audit_date'] && data[0]['audit_date'] !== null && data[0]['audit_date'] !== '') {
                        // If audit_date is saved in CDC report, format and use it
                        // SPECIAL HANDLING: For SA-II we must not allow day/month to flip (10.01.26 -> 01.10.26)
                        var rawAuditDate = data[0]['audit_date'];
                        var savedAuditDate = rawAuditDate;

                        if ($cdcbyservice === "SA2" || $cdcbyservice === "SA1" || $cdcbyservice === "RC") {
                            // Try to normalise common "dd.mm.yy" / "dd.mm.yyyy" formats without re-interpreting d/m
                            var dotPattern = /^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{2,4})$/;
                            var m = dotPattern.exec(rawAuditDate);
                            if (m) {
                                var d = ('0' + m[1]).slice(-2);
                                var mo = ('0' + m[2]).slice(-2);
                                var y = m[3];
                                if (y.length === 2) {
                                    // Assume 20xx for two-digit years
                                    y = '20' + y;
                                }
                                savedAuditDate = d + '.' + mo + '.' + y;
                            } else {
                                // Fallback: only if moment can parse reliably, otherwise keep raw string
                                var mObj = moment(rawAuditDate, [
                                    moment.ISO_8601,
                                    'DD.MM.YYYY',
                                    'DD.MM.YY',
                                    'DD-MM-YYYY',
                                    'DD/MM/YYYY'
                                ], true);
                                if (mObj.isValid()) {
                                    savedAuditDate = mObj.format('DD.MM.YYYY');
                                }
                            }
                        } else {
                            // Existing behaviour for CA, SA-I, RC, SP
                            var mObjGeneric = moment(rawAuditDate);
                            if (mObjGeneric.isValid()) {
                                savedAuditDate = mObjGeneric.format('DD.MM.YYYY');
                            }
                        }

                        $(".audit_date_CDC").val(savedAuditDate);
                        console.log('\u2713 Edit CDC - Using saved audit_date:', savedAuditDate);
                        console.log('\u2713 Field populated with:', $(".audit_date_CDC").val());
                    } else {
                        // Fall back to stage audit dates if audit_date is not saved
                        var auditDates = [data[5]["audit_dates"], data[6]["audit_dates"]].filter(Boolean);
                        var auditDateStr = auditDates.join(", ");
                        $(".audit_date_CDC").val(auditDateStr);
                        console.log('\u2713 Edit CDC - Using stage audit dates:', auditDateStr);
                        console.log('\u2713 Field populated with:', $(".audit_date_CDC").val());
                    }

                    // CRITICAL FIX: For old clients, covered_for might be empty in CDC data
                    // Use certification or assessment_standard as fallback (same as ADD CDC logic)
                    console.log('=== DEBUGGING OLD CLIENT ISO DATA ===');
                    console.log('data[0] (CDC data):', data[0]);
                    console.log('data[1] (Customer/Service data):', data[1]);
                    console.log('data[0][covered_for]:', data[0] ? data[0]['covered_for'] : 'N/A');
                    console.log('data[1][certification]:', data[1] ? data[1]['certification'] : 'N/A');
                    console.log('data[1][assessment_standard]:', data[1] ? data[1]['assessment_standard'] : 'N/A');
                    console.log('data[1][scope]:', data[1] ? data[1]['scope'] : 'N/A');

                    // Try multiple fallback sources for ISO standards
                    var coveredForValue = data[0]['covered_for'] ||
                        (data[1] ? data[1]['certification'] : '') ||
                        (data[1] ? data[1]['assessment_standard'] : '') ||
                        (data[1] ? data[1]['scope'] : '') ||
                        '';
                    console.log('EDIT CDC - Final covered_for value:', coveredForValue);

                    // Store in window scope for access by updateCdcOfficeSections
                    window.customerIsoStandards = coveredForValue;
                    console.log('EDIT CDC - Stored in window.customerIsoStandards:', window.customerIsoStandards);

                    setCdcAccreditation(data[0]['accreditation']);
                    setCdcCoveredStandards(coveredForValue, data[9] || []);

                    // CRITICAL: For old clients without saved office data, immediately initialize sections
                    // This must happen BEFORE the trigger('change') call
                    if (!hasOfficeEntries && $cdcbyservice !== "RC" && coveredForValue) {
                        console.log('EDIT CDC - Immediately initializing FOR OFFICE sections with:', coveredForValue);
                        setTimeout(function () {
                            updateCdcOfficeSections(coveredForValue);
                        }, 150);
                    }

                    $('.system_during').val(data[0]['system_during']);
                    $(".system_complaint").val(data[0]['system_complainte']);

                    // Populate Assessment / NC (M) Closing date for editcdcReport path
                    var rawAssDateEdit = data[0]["assDate_formatted"] || data[0]["assDate"];
                    var assVal = '';
                    if (rawAssDateEdit && rawAssDateEdit !== null && rawAssDateEdit !== '' && String(rawAssDateEdit).toLowerCase() !== 'null') {
                        var mAssEdit = moment(rawAssDateEdit, [
                            moment.ISO_8601,
                            'YYYY-MM-DD',
                            'DD.MM.YYYY',
                            'DD-MM-YYYY',
                            'DD/MM/YYYY'
                        ], true);
                        if (mAssEdit.isValid()) {
                            assVal = mAssEdit.format('DD.MM.YYYY');
                        } else {
                            var mAssEditLoose = moment(rawAssDateEdit);
                            if (mAssEditLoose.isValid()) {
                                assVal = mAssEditLoose.format('DD.MM.YYYY');
                            }
                        }
                    }

                    // Final fallback: if still empty, use CDC Date from data[0]['Date']
                    if ((!assVal || assVal === '') && data[0]['Date']) {
                        var mAssEditFallback = moment(data[0]['Date']);
                        if (mAssEditFallback.isValid()) {
                            assVal = mAssEditFallback.format('DD.MM.YYYY');
                        }
                    }

                    $(".assDate").val(assVal);
                    let leadAuditors = [];
                    let leadAuditors1 = [];
                    let leadAuditors2 = [];
                    let leadAuditors3 = [];

                    if (data[3] && typeof data[3] === 'object') {
                        for (const key in data[3]) {
                            if (data[3].hasOwnProperty(key)) {
                                if (data[3][key]["auditor_category"] == "Team Leader") {
                                    leadAuditors.push(data[3][key].auditor_name);
                                } else if (data[3][key]["auditor_category"] == "Auditor") {
                                    leadAuditors1.push(data[3][key].auditor_name);
                                } else if (data[3][key]["auditor_category"] == "Technical Expert") {
                                    leadAuditors2.push(data[3][key].auditor_name);
                                }
                                else {
                                    leadAuditors3.push(data[3][key].auditor_name);
                                }
                            }
                        }
                    } else {
                        console.error('auditnotifi1 is not a valid object or is empty.');
                    }
                    if (data[4] && typeof data[4] === 'object') {
                        for (const key in data[4]) {
                            if (data[4].hasOwnProperty(key)) {
                                if (data[4][key]["auditor_category"] == "Team Leader") {
                                    leadAuditors.push(data[4][key].auditor_name);
                                } else if (data[4][key]["auditor_category"] == "Auditor") {
                                    leadAuditors1.push(data[4][key].auditor_name);
                                } else if (data[4][key]["auditor_category"] == "Technical Expert") {
                                    leadAuditors2.push(data[4][key].auditor_name);
                                }
                                else {
                                    leadAuditors3.push(data[4][key].auditor_name);
                                }
                            }
                        }
                    } else {
                        console.error('auditnotifi2 is not a valid object or is empty.');
                    }
                    const uniqueLeadAuditors = [...new Set(leadAuditors)];
                    const uniqueLeadAuditors1 = [...new Set(leadAuditors1)];
                    const uniqueLeadAuditors2 = [...new Set(leadAuditors2)];
                    const uniqueLeadAuditors3 = [...new Set(leadAuditors3)];

                    let leadAuditorString = uniqueLeadAuditors.join(', ');
                    $leadauditor = leadAuditorString;
                    const leadAuditor = leadAuditorString;
                    let auditor = uniqueLeadAuditors1.join(', ');
                    $auditors = auditor;
                    const auditors = auditor;
                    let technicalexpert = uniqueLeadAuditors2.join(', ');
                    $technicalexperts = technicalexpert;
                    let anyother = uniqueLeadAuditors3.join(', ');
                    $anyothers = anyother;

                    if (leadAuditorString.length == 0) {
                        $leadauditor = "NA";
                    }
                    if (auditor.length == 0) {
                        $auditors = "NA";
                    }
                    if (technicalexpert.length == 0) {
                        $technicalexperts = "NA";
                    }
                    if (anyother.length == 0) {
                        $anyothers = "NA";
                    }

                    $('.lead_auditor').val($leadauditor);
                    $('.technical_expert').val($technicalexperts);
                    $('.auditors').val($auditors);
                    $('.any_ther').val($anyothers);

                    if ($auditors.endsWith(", ")) {
                        $auditors = $auditors.slice(0, -2);
                    }
                    const optionsToRemove = [];

                    // Split each string into an array of values
                    const leadAuditorsArray = leadAuditorString.split(', ');
                    const auditorsArray = auditor.split(', ');
                    const technicalExpertsArray = technicalexpert.split(', ');
                    const anyotherArray = anyother.split(', ');

                    // Push each value into the optionsToRemove array
                    leadAuditorsArray.forEach(auditor => {
                        optionsToRemove.push(auditor);
                    });
                    auditorsArray.forEach(auditor => {
                        optionsToRemove.push(auditor);
                    });
                    technicalExpertsArray.forEach(expert => {
                        optionsToRemove.push(expert);
                    });
                    anyotherArray.forEach(expert => {
                        optionsToRemove.push(expert);
                    });
                    //alert(optionsToRemove);


                    removeUnwantedOptions('.reviewer-1', optionsToRemove);
                    removeUnwantedOptions('.reviewer-2', optionsToRemove);
                    removeUnwantedOptions('.granted-by', optionsToRemove);
                    console.log('CDC Members - reviewer_1:', data[0]['reviewer_1']);
                    console.log('CDC Members - reviewer_2:', data[0]['reviewer_2']);
                    console.log('CDC Members - granted_by:', data[0]['granted_by']);
                    $(".reviewer-1").val(data[0]['reviewer_1']);
                    $(".reviewer-2").val(data[0]['reviewer_2']);
                    $(".granted-by").val(data[0]['granted_by']);
                    console.log('Checkbox values - rev1_yes:', data[0]["rev1_yes"]);
                    console.log('Checkbox values - rev2_yes:', data[0]["rev2_yes"]);
                    console.log('Checkbox values - review_1_granting:', data[0]["review_1_granting"]);
                    $('.checkrev1[value = "' + data[0]["rev1_yes"] + '"]').prop('checked', true);
                    $('.checkrev2[value = "' + data[0]["rev2_yes"] + '"]').prop('checked', true);

                    $('.checkm1[value = "' + data[0]["review_1_granting"] + '"]').prop('checked', true);

                    $('.comments_if_any').val(data[0]['comments_if_any']);
                    //                    if (data[0]['reviewer_1_date'] == "" || data[0]['reviewer_1_date'] == null)
                    //                        $review1 = "";
                    //                    else
                    //                        $review1 = moment(data[0]['reviewer_1_date']).format('DD.MM.YYYY');
                    ////                    alert($review1);
                    //                    $(".review-date-1").val($review1);
                    $('.check1[value = "' + data[0]["yes1"] + '"]').prop('checked', true);
                    $('.check2[value = "' + data[0]["yes2"] + '"]').prop('checked', true);
                    $('.check3[value = "' + data[0]["yes3"] + '"]').prop('checked', true);

                    $('.checkm2[value = "' + data[0]["review_2_granting"] + '"]').prop('checked', true);
                    //                    if (data[0]['audit_performance'] == "" || data[0]['audit_performance'] == null)
                    //                        $audit_performance = "";
                    //                    else
                    //                        $audit_performance = moment(data[0]['audit_performance']).format('DD.MM.YYYY');
                    //                    $(".audit_performance").val($audit_performance);
                    $(".comments_if_any_2nd_review").val(data[0]['review_2_comment']);

                    $('.checkrev3[value = "' + data[0]["rev3_yes"] + '"]').prop('checked', true);

                    $('.checkm3[value = "' + data[0]["cdc_status"] + '"]').prop('checked', true);
                    //                    alert($('.checkm3[value = "' + data[0]["cdc_status"] + '"]').prop('checked', true).val());
                    if ($('.checkm3[value = "' + data[0]["cdc_status"] + '"]').prop('checked', true).val() != "") {
                        $("input.checkm3").removeAttr("disabled");
                    }
                    $('.comments').val(data[0]['comments']);
                    if (data[0]['Date'] == "" || data[0]['Date'] == null)
                        $Date = "";
                    else
                        $Date = moment(data[0]['Date']).format('DD.MM.YYYY');
                    $('.Date').val($Date);

                    // When ISO-specific office_use entries exist (hasOfficeEntries === true),
                    // their FOR OFFICE USE values (certificate no, dates, etc.) are populated
                    // from data[7] and should not be overwritten by these legacy single-field
                    // values from data[0]. Only apply the legacy values when there are no
                    // office_use records (hasOfficeEntries === false).
                    if (data[0]['expiredate'] == "" || data[0]['expiredate'] == null)
                        $expiredate = "";
                    else
                        $expiredate = moment(data[0]['expiredate']).format('DD.MM.YYYY');

                    if (data[0]['periodicalaudit'] == "" || data[0]['periodicalaudit'] == null)
                        $periodicalaudit = "";
                    else
                        $periodicalaudit = moment(data[0]['periodicalaudit']).format('DD.MM.YYYY');

                    if (data[0]['revDatecdc'] == "" || data[0]['revDatecdc'] == null)
                        $revDatecdc = "";
                    else
                        $revDatecdc = moment(data[0]['revDatecdc']).format('DD.MM.YYYY');

                    if (!hasOfficeEntries) {
                        $('.certificateNo').val(data[0]['certificateNo']);
                        $('.revCdc').val(data[0]['revCdc']).prop('disabled', false);
                        $('.expiredate').val($expiredate);
                        $('.periodicalaudit').val($periodicalaudit);
                        $(".periodicalAuditcoment").val(data[0]["periodical_audit_comment"]);
                        $('.revDatecdc').val($revDatecdc);
                        $(".nextaudittype").val(data[0]["next_audit_type"]);
                    }
                    $(".ncval").val(data[0]['ncval']);
                    $(".ofival").val(data[0]['ofival']);
                    $(".oplus").val(data[0]['oplusval']);
                    $('input[type="text"]').trigger('change');
                    $('.system_during').trigger('change');
                    $('.system_complaint').trigger('change');
                    $('.comments_if_any').trigger('change');
                    $('.comments').trigger('change');
                    $(".comments_if_any_2nd_review").trigger("change");
                    $('.periodicalAuditcoment').trigger('change');
                    $('.covered_for').trigger('change');
                    // revDatecdc and nextaudittype are handled above when there are no
                    // office_use entries; for multi-ISO office_use records, per-ISO values
                    // from data[7] are preserved.

                    if (data[0]["rc_audit_date"] != "" && data[0]["rc_restore_date"]) {
                        $(".rc-restore").show();
                        $(".date-restore1").val(moment(data[0]["rc_audit_date"]).format('DD.MM.YYYY'));
                        $(".date-restore2").val(moment(data[0]["rc_restore_date"]).format('DD.MM.YYYY'));
                    }

                    $('.address').trigger('change');
                    $('.revDatecdc').trigger('change');

                    // Render ISO decision blocks for RC services
                    // Use merged ISO list (CA+SA+RC) for decision blocks, not just RC-specific ISOs
                    var isoListForDecisionBlocks = ($cdcbyservice === "RC" && window.rcMergedIsoList) ? window.rcMergedIsoList : $('.covered_for').val();
                    renderIsoDecisionBlocks(isoListForDecisionBlocks);

                    // Populate existing ISO decisions if available (data[8] should contain ISO decisions)
                    if (data[8] && Array.isArray(data[8]) && data[8].length > 0) {
                        setTimeout(function () {
                            populateIsoDecisions(data[8]);
                        }, 300);
                    }

                    $('#cdcModel').modal('show');
                    if (hasOfficeEntries) {
                        console.log('=== EDIT: LOADING SAVED OFFICE USE DATA ===');
                        console.log('Service type:', $cdcbyservice);
                        console.log('Office entries count:', data[7] ? data[7].length : 0);
                        console.log('Office entries data:', data[7]);

                        // For RC services
                        if ($cdcbyservice === "RC") {
                            $('#cdcOfficeContainer').show();
                            console.log('RC: Showing FOR OFFICE container for saved data');

                            // For RC: When loading saved data, populate all saved ISOs (don't filter by approved ISOs)
                            populateCdcOfficeUseSections(data[7]);

                            console.log('RC: Skipping updateForOfficeBasedOnIsoDecisions - using saved data sections');

                            // CRITICAL FIX: Ensure ALL sections for saved entries are visible
                            setTimeout(function () {
                                var allSections = $('#cdcOfficeContainer .CdcOffice');
                                var entriesCount = data[7] ? data[7].length : 0;
                                console.log('RC: Total sections in DOM:', allSections.length, 'Entries:', entriesCount);

                                $('#cdcOfficeContainer').show();

                                for (var i = 0; i < entriesCount && i < allSections.length; i++) {
                                    var $sect = $(allSections.eq(i));
                                    $sect.show();
                                    var isoLabel = $sect.find('.isoLabelField').val() || '';
                                    var certNo = $sect.find('.certificateNo').val() || '';
                                    console.log('✓ Section', i, 'FORCED VISIBLE - ISO:', isoLabel, 'Cert:', certNo);
                                }

                                if (allSections.length > entriesCount) {
                                    allSections.slice(entriesCount).hide();
                                }

                                var visibleCount = $('#cdcOfficeContainer .CdcOffice:visible').length;
                                console.log('=== FINAL CHECK: Visible sections =', visibleCount, 'out of', entriesCount, 'saved entries ===');

                                if (visibleCount < entriesCount) {
                                    console.error('❌ ERROR: Only', visibleCount, 'sections visible, expected', entriesCount);
                                    allSections.slice(0, entriesCount).show();
                                }
                            }, 600);
                        } else {
                            // For non-RC services (CA, SA-I, SA-II) with saved data
                            console.log('=== EDIT: OLD CLIENT WITH SAVED OFFICE DATA (non-RC) ===');

                            // Get ISOs from covered_for field (same as new client logic)
                            var coveredFor = $('.covered_for').val() || '';
                            console.log('covered_for field value:', coveredFor);

                            var isoEntries = getIsoEntries(coveredFor);
                            console.log('ISOs from getIsoEntries:', isoEntries);
                            console.log('Number of ISOs:', isoEntries.length);

                            // FALLBACK: If covered_for is empty/unparseable, check saved entries for ISO labels
                            if (isoEntries.length === 0 && data[7].length > 0) {
                                console.log('covered_for returned no ISOs, checking saved entries for ISO labels...');
                                data[7].forEach(function (entry) {
                                    if (entry && entry.iso_label) {
                                        isoEntries.push(entry.iso_label);
                                        console.log('Found ISO from saved data:', entry.iso_label);
                                    }
                                });
                                console.log('ISOs from saved entries:', isoEntries);
                            }

                            // FALLBACK 2: If still no ISOs, use saved entries count
                            if (isoEntries.length === 0 && data[7].length > 0) {
                                console.log('No ISO labels found, using saved entries count:', data[7].length);
                                for (var i = 0; i < data[7].length; i++) {
                                    isoEntries.push('ISO ' + (i + 1));
                                }
                            }

                            console.log('Final ISO entries to create sections for:', isoEntries);

                            $('#cdcOfficeContainer').show();

                            // Create sections based on ISO count from covered_for (like new clients)
                            if (isoEntries.length > 0) {
                                ensureCdcOfficeSections(isoEntries);

                                // Set ISO labels on sections
                                var container = $('#cdcOfficeContainer');
                                var sections = container.find('.CdcOffice');
                                sections.each(function (index) {
                                    if (index < isoEntries.length) {
                                        var isoLabel = isoEntries[index];
                                        $(this).find('.isoLabelField').val(isoLabel);
                                        $(this).find('.isoIndexField').val(index);
                                        $(this).attr('data-iso-index', index);
                                        $(this).data('iso-index', index);

                                        var titleRow = $(this).find('.cdc-office-title');
                                        if (isoEntries.length > 1) {
                                            titleRow.show();
                                            titleRow.find('.cdc-office-iso-label').text(isoLabel);
                                        } else {
                                            titleRow.hide();
                                        }
                                        $(this).show();
                                    }
                                });
                            }

                            // Now populate with saved data if available
                            if (data[7].length > 0) {
                                data[7].forEach(function (entry, idx) {
                                    var $section = $('#cdcOfficeContainer .CdcOffice').eq(idx);
                                    if ($section.length && entry) {
                                        $section.find('.certificateNo').val(entry.certificate_no || '');
                                        $section.find('.revCdc').val(entry.rev_no || '').prop('disabled', false);
                                        $section.find('.revDatecdc').val(formatCdcDate(entry.rev_date));
                                        $section.find('.expiredate').val(formatCdcDate(entry.expire_date));
                                        $section.find('.periodicalaudit').val(formatCdcDate(entry.periodical_audit));
                                        $section.find('.nextaudittype').val(entry.audit_type || '');
                                        $section.find('.periodicalAuditcoment').val(entry.comments || '');
                                        console.log('✓ Populated section', idx, 'with saved data');
                                    }
                                });
                            }

                            // Mark as having saved data to prevent updateCdcOfficeSections interference
                            $('#cdcOfficeContainer').data('hasSavedOfficeData', true);

                            console.log('=== EDIT: OLD CLIENT SETUP COMPLETE - Sections:', $('#cdcOfficeContainer .CdcOffice:visible').length, '===');
                        }
                    } else {
                        // Only update based on ISO decisions if there's no saved data
                        if ($cdcbyservice === "RC") {
                            setTimeout(function () {
                                updateForOfficeBasedOnIsoDecisions();
                            }, 300);
                        } else {
                            // For non-RC without saved data, use normal initialization with stored value
                            var isoValue = window.customerIsoStandards || $('.covered_for').val() || '';
                            console.log('EDIT CDC - Initializing sections with:', isoValue);

                            // EMERGENCY FIX: If no ISO value but this is an old client,
                            // check if customer has multiple certificates and create sections accordingly
                            if (!isoValue || isoValue.trim() === '') {
                                console.log('EMERGENCY: No ISO value found, creating default 2 sections for old client');

                                // Create 2 sections by default for old clients
                                ensureCdcOfficeSections(['ISO Standard 1', 'ISO Standard 2']);

                                var container = $('#cdcOfficeContainer');
                                var sections = container.find('.CdcOffice');
                                console.log('Created sections:', sections.length);

                                sections.each(function (index) {
                                    var $section = $(this);
                                    $section.attr('data-iso-index', index);
                                    $section.data('iso-index', index);
                                    $section.find('.isoIndexField').val(index);
                                    $section.find('.isoLabelField').val('ISO ' + (index + 1));

                                    var titleRow = $section.find('.cdc-office-title');
                                    titleRow.show();
                                    titleRow.find('.cdc-office-iso-label').text('ISO ' + (index + 1));

                                    $section.show();
                                    console.log('Setup section', index, '- visible:', $section.is(':visible'));
                                });

                                $('#cdcOfficeContainer').show();
                                console.log('Total visible sections:', container.find('.CdcOffice:visible').length);
                            } else {
                                updateCdcOfficeSections(isoValue);
                            }
                        }
                    }
                    // For SA1, SA2, RC: auto-populate certificate from CA even if office entries exist
                    // For CA: only auto-populate if no office entries
                    var isNonCAService = ($cdcbyservice === "SA1" || $cdcbyservice === "SA2" || $cdcbyservice === "RC");
                    if (!hasOfficeEntries || isNonCAService) {
                        autoPopulateCertificates(false);
                    }
                    suppressCertificateRequests = false;

                    // Check CDC status after modal is shown and data is populated
                    setTimeout(function () {
                        checkCDCStatus();
                    }, 200);

                    // for edit temp
                    //                    alert(data[1]["review_date"]);
                },
                error: function (data, status, err) {
                    alert(status);
                }
            });
        });

        // Ensure FOR OFFICE USE section visibility is checked whenever modal is shown
        $('#cdcModel').on('shown.bs.modal', function () {
            console.log('CDC Modal shown - reinitializing datepickers');
            checkCDCStatus();
            // Reinitialize datepickers after modal is fully shown and sections are rendered
            setTimeout(function () {
                initializeCdcDatepickers();
            }, 200);
        });

        // Collect ISO decisions and sync legacy fields before form submission
        $('form[action="addCdcModal.php"]').on('submit', function (e) {
            var hasError = validateCDC(true);
            if (hasError) {
                e.preventDefault();
                return false;
            }

            var isoDecisions = collectIsoDecisions();
            $('#iso_decisions').val(JSON.stringify(isoDecisions));
            console.log('ISO decisions collected:', isoDecisions);

            // Sync first ISO section data to legacy hidden fields for backward compatibility
            var $firstSection = $('#cdcOfficeContainer .CdcOffice').first();
            if ($firstSection.length > 0) {
                $('.legacy-rev-date').val($firstSection.find('.revDatecdc').val());
                $('.legacy-rev-no').val($firstSection.find('.revCdc').val());
                $('.legacy-certificate-no').val($firstSection.find('.certificateNo').val());
                $('.legacy-audit-type').val($firstSection.find('.nextaudittype').val());
                $('.legacy-expire-date').val($firstSection.find('.expiredate').val());
                $('.legacy-periodical-audit').val($firstSection.find('.periodicalaudit').val());
                $('.legacy-comments').val($firstSection.find('.periodicalAuditcoment').val());
            }
        });

    });

</script>

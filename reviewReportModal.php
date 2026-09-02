<?php

 $sql = "SELECT auditor_name FROM auditors";
$statement = $db->prepare($sql);
$statement->execute();
$auditors1 = $statement->fetchAll(PDO::FETCH_COLUMN);
?>

<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="reviewReport" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                Anex-1: Review of Audit processes
            </div>
            <form action="addreviewReport.php" method="POST">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formStageIAudit">
                            <span><i class="fa  ">Stage I Audit</i></span>
                        </div>
                    </div>
                    <div class="AppStageIAudit">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label> Name of Audited Organisation:</label> 
                                <input type="text" class="auditOrganisation" id="auditOrganisation" name="auditOrganisation" placeholder="Name of Audited Organisation:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Date(s) of Audit Conducted::</label> 
                                <input type="text" class="auditDate" id="applicationDateGet" name="auditDate" placeholder=" Date(s) of Audit Conducted:" required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control"><label>Team Leader</label>
                                <input type="text" class="leadAuditor" id="leadAuditor" name="leadAuditor" placeholder="Lead Auditor:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label> Agreement Number</label> 
                                <input type="text" readonly class="referenceNumber" id="referenceNumber" name="referenceNumber" placeholder="Agreement Number:" required>
                            </div>
                             <div class="col-sm-6 form-group float-label-control">
                                <label>Assessment Standard</label>
                                <input type="text" readonly class="assessmentStandard" id="assessmentStandard" name="assessmentStandard" placeholder="Assessment Standard:" required>
                            </div>
                        </div>
                        
                        <div class="row ">
                            <div class="col-sm-12" align="center">Reviewer Acceptance: For no any conflict of interest with the client.<br>
                                <span> 
                                    <input class="acceptance" type="checkbox" name="acceptance" value="Accepted"> Accepted &emsp;
                                    <input class="acceptance" type="checkbox" name="acceptance" value="Not Accepted"> Not Accepted
                                </span>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div class="row">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formAspectVerify">
                            <span><i class="fa  ">ASPECT TO VERIFY</i></span>
                        </div>
                    </div>

                    <div class="AppVerify" style="display: none"><br>
                        <div class="row" style="">
                            <div class="col-xs-6  "><span>ASPECT TO VERIFY:</span></div>
                            <div class="col-xs-3"><span>Yes/No/NA </span></div>
                            <div class="col-xs-3"><span>REMARK/DETAILS </span></div>
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6 audit_aspect"><font size="2">Certification Agreement signed and accepted by client, Audit notification with declaration of confidentiality and no COI is signed and accepted.</font></div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Certification Agreement signed and accepted by client, Audit notification with declaration of confidentiality and no COI is signed and accepted.">
                        </div>

                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">The review of clients documented information covered, All the elements as per checklist F 7.10 and its applicable annexture/s as per the standards requirements is covered by assessor/s.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="The review of clients documented information covered, All the elements as per checklist F 7.10 and its applicable annexture/s as per the standards requirements is covered by assessor/s.">
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Audit date, duration followed. Site/s address, activities verified, And Total N° employees verified, audit days decided for stage 2 audit.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Audit date, duration followed. Site/s address, activities verified, And Total N° employees verified, audit days decided for stage 2 audit.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Opening and closing meeting done as per scheduled time and meeting prompter covered as per checklist F 7.09.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Opening and closing meeting done as per scheduled time and meeting prompter covered as per checklist F 7.09.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Applicability of statutory & regulatory requirement verified, identified.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Applicability of statutory & regulatory requirement verified, identified.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Scope capability verification was done with considering the requirement of relevant interested parties and internal, external context of organisation.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Scope capability verification was done with considering the requirement of relevant interested parties and internal, external context of organisation.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">If any modification in scope has assessed and/ or scope decided by lead auditor + client is accepted unanimously with sign.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="If any modification in scope has assessed and/ or scope decided by lead auditor + client is accepted unanimously with sign.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Non applicability of standards requirement verified and accepted with Justification.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Non applicability of standards requirement verified and accepted with Justification.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Adequacy of internal audit, Management Review verified.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Adequacy of internal audit, Management Review verified.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">If any process, service provided by external sources that required to realisation of product/ service covered under scope of certification has been identified and control over verified adequately.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="If any process, service provided by external sources that required to realisation of product/ service covered under scope of certification has been identified and control over verified adequately.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Verification was carried out for identification of relevant interested parties of organisation and its needs and expectations.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Verification was carried out for identification of relevant interested parties of organisation and its needs and expectations.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6 audit_aspect">
                                <font size="2">Processes/ functions determined by organization and its interaction (process based approach) verified and considered for preparing stage 2 audit schedule.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Processes/ functions determined by organization and its interaction (process based approach) verified and considered for preparing stage 2 audit schedule.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Risk and opportunities addressed by organisation and actions taken/decided to mitigate the risks.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Risk and opportunities addressed by organisation and actions taken/decided to mitigate the risks.">
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Level of Integration verified (Applicable In case of Integrated MS).</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Level of Integration verified (Applicable In case of Integrated MS).">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Check points as per F 7. 11 (Annex-1) verified and evidence provided by auditor/s (applicable for QMS and all assessments).</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Check points as per F 7. 11 (Annex-1) verified and evidence provided by auditor/s (applicable for QMS and all assessments).">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Check points as per F 7. 11 (Annex-2) verified and evidence provided by auditor/s (applicable for EMS assessment).</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Check points as per F 7. 11 (Annex-2) verified and evidence provided by auditor/s (applicable for EMS assessment).">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6 audit_aspect ">
                                <font size="2">Check points as per F 7. 11 (Annex-3) verified and evidence provided by auditor/s (applicable for OHSMS assessment).</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Check points as per F 7. 11 (Annex-3) verified and evidence provided by auditor/s (applicable for OHSMS assessment).">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6 audit_aspect">
                                <font size="2">If any areas of concerns addressed – Sufficient time given to client for resolve it before stage 2 audit.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="If any areas of concerns addressed – Sufficient time given to client for resolve it before stage 2 audit.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspect">
                                <font size="2">Effectiveness of management system implementation verified for the readiness of stage 2 and conclusion is given by LA.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_response" class="form-control audit_response" id="audit_response" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remark" id="remark" name="remark" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit-aspect-hidden" name="audit-aspect-hidden" value="Effectiveness of management system implementation verified for the readiness of stage 2 and conclusion is given by LA.">
                        </div>
                        <br>
                        <div class="row" style="" align="center">
                            <div class="col-xs-12 form-group float-label-control">
                                <input class="eremarkanx1" type="checkbox" name="eremarkanx1" value="eremark" checked> The stage 1 report has been verified and recommended to conduct stage 2.
                            </div>
                        </div>
                        <div class="row" style="" align="center">
                            <div class="col-xs-12 form-group float-label-control">
                                <label>Reviewer Comments:</label> 
                                <input type="text" class="ReviewerComments1" id="ReviewerComments1" name="ReviewerComments1" placeholder="----Reviewer Comments----">
                            </div>
                        </div>   
                        <div class="row" style="" align="center"> 
                            <div class="col-xs-6 form-group float-label-control">
                                <label>Reviewer Name:</label> 
                                <select class="form-control ReviewerName1" id="ReviewerName1" name="ReviewerName1">
        <option value="">Select Reviewer</option>
        <?php foreach ($auditors1 as $auditor): ?>
            <?php if ($auditor !== 'auditor' && $auditor !== 'allauditors'): ?>
                <option value="<?php echo $auditor; ?>"><?php echo $auditor; ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
                            </div>
                            <div class="col-xs-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Review Date:</label> 
                                        <input type="text" class="reviewDate" id="reviewDate" name="reviewDate" placeholder="Review Date:">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="reviewDateError" style="display: none">
                                            <small><span id="reviewDateErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="audit-aspect-hidden_array" name="audit-aspect-hidden_array" value=""> 
                <input type="hidden" class="audit-response-hidden_array" name="audit-response-hidden_array" value=""> 
                <input type="hidden" class="remark-hidden_array" name="remark-hidden_array" value=""> 
                <input type="hidden" name="serviceId" class="serviceId" id="serviceId" value=""> 
                <input type="hidden" name="customerId" class="customerId" id="customerId" value="">
                <div class="modal-footer">
                    <button type="submit" name="addReviewReport" class="btn btn-xs btn-info addReviewReport">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="updateReviewReport" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                Anex-1: Review of Audit processes
            </div>
            <form action="addreviewReport.php" method="POST">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12 text-center btn qms_complete_btn formStageIAudit">
                            <span><i class="fa">Stage I Audit</i></span>
                        </div>
                    </div>
                    <div class="AppStageIAudit">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Name of Audited Organisation:</label> 
                                <input type="text" class="auditOrganisationEdit" id="auditOrganisation" name="auditOrganisation" placeholder="Name of Audited Organisation:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Date(s) of Audit Conducted::</label> 
                                <input type="text" class="auditDateEdit" id="auditDateEdit" name="auditDate" placeholder=" Date(s) of Audit Conducted:" required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Team Leader</label>
                                <input type="text" class="leadAuditorEdit" id="leadAuditorEdit" name="leadAuditor" placeholder="Lead Auditor:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label> Agreement Number</label> 
                                <input type="text" readonly class="referenceNumberEdit" id="referenceNumber" name="referenceNumber" placeholder="Agreement Number:" required>
                            </div>
                             <div class="col-sm-6 form-group float-label-control">
                                <label>Assessment Standard</label>
                                <input type="text" readonly class="assessmentStandard" id="assessmentStandard" name="assessmentStandard" placeholder="Assessment Standard:" required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12" align="center">Reviewer Acceptance: For no any conflict of interest with the client.<br>
                                <span><input class="acceptedEdit" id="acceptedEdit" type="checkbox" name="acceptedEdit" value="Accepted">Accepted &emsp;
                                    <input class="acceptedEdit" type="checkbox" id="acceptedEdit" name="acceptedEdit" value="Not Accepted"> Not Accepted</span>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div class="row">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formAspectVerify">
                            <span><i class="fa  ">ASPECT TO VERIFY</i></span>
                        </div>
                    </div>
                    <div class="AppVerify" style="display: none"><br>
                        <div class="row" style="">
                            <div class="col-xs-6  "><span>ASPECT TO VERIFY:</span></div>
                            <div class="col-xs-3"><span>Yes/No/NA </span></div>
                            <div class="col-xs-3"><span>REMARK/DETAILS </span></div>
                        </div>
                        <br>
                        <div class="editReviewSelection"></div>
                        <div class="row" style="" align="center">
                            <div class="col-xs-12 form-group float-label-control">
                                <input class="eremarkanx1edit" type="checkbox" name="eremarkanx1edit" value="eremark" checked> The stage 1 report has been verified and recommended to conduct stage 2.
                            </div>
                        </div>
                        <div class="row" style="" align="center">
                            <div class="col-xs-12 form-group float-label-control">
                                <label>Reviewer Comments:</label> 
                                <input type="text" class="ReviewerCommentsEdit1" id="ReviewerCommentsEdit1" name="ReviewerCommentsEdit1" placeholder="----Reviewer Comments----">
                            </div>
                        </div>    
                        <div class="row" style="" align="center"> 
                            <div class="col-xs-6 form-group float-label-control">
                                <label>Reviewer Name:</label> 
                                                                <select class="form-control reviewerNameEdit" id="reviewerNameEdit" name="reviewerNameEdit">
        <option value="">Select Reviewer</option>
        <?php foreach ($auditors1 as $auditor): ?>
            <?php if ($auditor !== 'auditor' && $auditor !== 'allauditors'): ?>
                <option value="<?php echo $auditor; ?>"><?php echo $auditor; ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
                            </div>
                            <div class="col-xs-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Review Date:</label> 
                                        <input type="text" class="reviewDateEdit" id="reviewDateEdit" name="reviewDateEdit" placeholder="Review Date:">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="reviewDateEditError" style="display: none">
                                            <small><span id="reviewDateEditErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <input type="hidden" class="audit-aspect-hidden_edit" name="audit-aspect-hidden_edit" value=""> 
                <input type="hidden" class="audit-response-hidden_edit" name="audit-response-hidden_edit" value=""> 
                <input type="hidden" class="remark-hidden_edit" name="remark-hidden_edit" value=""> 
                <input type="hidden" name="serviceIdEdit" class="serviceIdEdit" id="serviceIdEdit" value="">
                <input type="hidden" name="customerIdEdit" class="customerIdEdit" id="customerIdEdit" value=""> 
                <input type="hidden" name="review-report-id" class="review-report-id" id="review-report-id" value=""> 
                <input type="hidden" name="audit-id-update" class="audit-id-update" id="audit-id-update" value="">
                <div class="modal-footer">
                    <button type="submit" name="updateReviewReport" class="btn btn-xs btn-info updateReviewReport">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
function removeUnwantedOptions(selector, optionsToRemove) {
    $(selector + ' option').each(function() {
        const optionText = $(this).text().trim(); // Trim whitespace
        if (optionsToRemove.some(option => option.trim().toLowerCase() === optionText.toLowerCase())) {
            $(this).remove();
        }
    });
}
    $(function () {
        $('#anex1revDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#anex1revDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#anex1isueDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
//                    autoclose: true
        });
        $("#anex1isueDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#revDateEdit').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#revDateEdit").keydown(function (e) {
//            e.preventDefault();
        });

        $('#isueDateEdit').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
//                    autoclose: true
        });
        $("#isueDateEdit").keydown(function (e) {
//            e.preventDefault();
        });

        $('#reviewDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
//                    autoclose: true
            onSelect: function() {
                // Validate on date selection
                validateAnexModel();
            }
        });
        $("#reviewDate").keydown(function (e) {
//            e.preventDefault();
        });
        $('.reviewDate').on('change blur', function() {
            // Validate Review Date on change/blur
            if ($(this).val() != "") {
                validateAnexModel();
            }
        });

        $('#reviewDateEdit').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
//                    autoclose: true
        });
        $("#reviewDateEdit").keydown(function (e) {
//            e.preventDefault();
        });
    });

    function validateAnexModel() {
        const auditDateEdit = $('.auditDate').val();
        const dates = auditDateEdit.split(", ");
        const lastDate = dates[dates.length - 1];
        $isError = false;
        if ($(".reviewDate").val() == "") {
            $('#reviewDateErrorMsg').text("Review Date cannot be empty");
            $('#reviewDateError').show();
            $('#reviewDate').addClass('inputHighlight');
            $isError = true;
        } else if ($('.reviewDate').val() != "" && (moment($('.reviewDate').val(), "DD.MM.YYYY") < moment(lastDate, "DD.MM.YYYY"))) {
            $('#reviewDateErrorMsg').text("Review Date must be greater than or equal to Stage-I Audit Date");
            $('#reviewDateError').show();
            $('#reviewDate').addClass('inputHighlight');
            $isError = true;
        } else if ($(".reviewDate").val() != "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    reviewDate: $('.reviewDate').val(),
                    svcId: $('.serviceId').val(),
                },
                async: false,
                success: function (data) {
//    	                alert(data);	                    
                    if (data == 1) {
                        $('#reviewDateErrorMsg').text("Review Date must be greater than or equal to Stage-I Audit Date");
                        $('#reviewDateError').show();
                        $('#reviewDate').addClass('inputHighlight');
                        $isError = true;
                    } else {
                        $('#reviewDateError').hide();
                        $('#reviewDate').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#reviewDateError').hide();
            $('#reviewDate').removeClass('inputHighlight');
        }
        return $isError;
    }
    
        function validateAnexModel1() {
            const auditDateEdit = $('.auditDateEdit').val();
            const dates = auditDateEdit.split(", ");
            const lastDate = dates[dates.length - 1];
        $isError = false;
        if ($(".reviewDateEdit").val() == "") {
            $('#reviewDateEditErrorMsg').text("Date can not be empty");
            $('#reviewDateEditError').show();
            $('#reviewDateEdit').addClass('inputHighlight');
            $isError = true;
        }else if ($(".reviewDateEdit").val() != "") {
            if ($('.reviewDateEdit').val() != "" && (moment($('.reviewDateEdit').val(), "DD/MM/YYYY") < moment(lastDate, "DD/MM/YYYY"))) {
            $('#reviewDateEditErrorMsg').text("Date must be greater than or equal to Audit date.");
            $('#reviewDateEditError').show();
            $('#reviewDateEdit').addClass('inputHighlight');
            $isError = true;
        }
        else {
            $('#reviewDateEditError').hide();
            $('#reviewDateEdit').removeClass('inputHighlight');
        }
        } else {
            $('#reviewDateEditError').hide();
            $('#reviewDateEdit').removeClass('inputHighlight');
        }
        return $isError;
    }

    $(document).ready(function () {

        var aitfServiceAuditorsRR = [];

        function loadServiceAuditorsForReviewReport(serviceId, customerId) {
            aitfServiceAuditorsRR = [];
            if (!serviceId) return;
            $.ajax({
                url: "checkReviewerConflict.php",
                type: "POST",
                dataType: "json",
                data: {
                    action: "get_service_auditors",
                    serviceId: serviceId,
                    customerId: customerId || ""
                },
                success: function (resp) {
                    if (resp && resp.auditors) {
                        aitfServiceAuditorsRR = resp.auditors;
                    }
                }
            });
        }

        function checkReviewerConflictWithServiceAuditorsRR(reviewerName) {
            if (!reviewerName || !aitfServiceAuditorsRR || !aitfServiceAuditorsRR.length) return false;
            var normRev = reviewerName.toLowerCase().replace(/[^a-z0-9]/g, '');
            if (!normRev) return false;
            for (var i = 0; i < aitfServiceAuditorsRR.length; i++) {
                var normAud = aitfServiceAuditorsRR[i].toLowerCase().replace(/[^a-z0-9]/g, '');
                if (normRev === normAud || (normRev.length >= 5 && normAud.length >= 5 && (normRev.indexOf(normAud) !== -1 || normAud.indexOf(normRev) !== -1))) {
                    return aitfServiceAuditorsRR[i];
                }
            }
            return false;
        }

        function showServiceReviewerConflictDialogueRR(auditorName, reviewerName) {
            var nameToDisplay = auditorName || reviewerName || "This user";
            var msg = nameToDisplay + " is Auditor of this service, cannot set as Reviewer.\n\nPlease select another Reviewer.";
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({
                    icon: 'error',
                    title: 'Conflict Detected',
                    html: '<div class="aitf-conflict-card">' +
                              '<div class="aitf-conflict-card-title">' +
                                  '<span class="aitf-conflict-card-name">' + nameToDisplay + '</span> is Auditor of this service, cannot set as Reviewer.' +
                              '</div>' +
                              '<div class="aitf-conflict-card-sub">' +
                                  'Please select another Reviewer.' +
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

        $(document).on('change', '.ReviewerName1, .reviewerNameEdit', function () {
            var $select = $(this);
            var revVal = ($select.val() || '').trim();
            if (!revVal) return;

            var confAud = checkReviewerConflictWithServiceAuditorsRR(revVal);
            if (confAud) {
                $select.val("");
                showServiceReviewerConflictDialogueRR(confAud, revVal);
                return;
            }

            var sid = $('.serviceId').val() || $('.serviceIdEdit').val();
            var cid = $('.customerId').val() || $('.customerIdEdit').val();
            if (sid) {
                $.ajax({
                    url: "checkReviewerConflict.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        action: "check_service_reviewer",
                        serviceId: sid,
                        customerId: cid,
                        reviewerName: revVal
                    },
                    success: function (resp) {
                        if (resp && resp.conflict) {
                            $select.val("");
                            showServiceReviewerConflictDialogueRR(resp.conflicting_auditor || revVal, revVal);
                        }
                    }
                });
            }
        });

        //code by app for Review Report (Annex I) Update
        $('.auditStage-I').click(function () {
            $('.serviceId').val($(this).attr('data-service'));
            $('.customerId').val($(this).attr('data-customer'));
            loadServiceAuditorsForReviewReport($(this).attr('data-service'), $(this).attr('data-customer'));
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customer_id: $(this).attr('data-customer'),
                    service_id: $(this).attr('data-service'),
                },
                success: function (data)
                {
                 var allStandards = [];
                 if (data[4]) {
                     if (data[4]['assessment_standard']) allStandards.push(data[4]['assessment_standard'].trim());
                     if (data[4]['certification']) allStandards.push(data[4]['certification'].trim());
                 }
                 if (data[3]) {
                     if (data[3]['assessment_standard']) allStandards.push(data[3]['assessment_standard'].trim());
                     if (data[3]['certification']) allStandards.push(data[3]['certification'].trim());
                 }
                 if (data[1] && data[1]['audit_standard']) {
                     allStandards.push(data[1]['audit_standard'].trim());
                 }
                 
                 if (allStandards.length > 0) {
                     isoStandards = allStandards.join(', ');
                 } else {
                     isoStandards = 'ISO 9001:2015';
                 }
                 
                 // Clean up the standards - handle comma-separated values
                 isoStandards = isoStandards.replace(/,+/g, ', ').replace(/\s*,\s*/g, ', ');
                 
                 // Deduplicate comma-separated values
                 if (isoStandards) {
                     var uniqueStandards = [...new Set(isoStandards.split(',').map(function(s) { return s.trim(); }).filter(function(s) { return s; }))];
                     isoStandards = uniqueStandards.join(', ');
                 }
                 
                 $('.assessmentStandard').val(isoStandards);
                    $('.auditOrganisation').val(data[0]['customer_name']);
//                    $('.auditDate').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
let leadAuditors = [];
let leadAuditors1 = [];
                                        if (data[8] && typeof data[8] === 'object') {
    for (const key in data[8]) {
        if (data[8].hasOwnProperty(key)) {
            if (data[8][key]["auditor_category"] == "Team Leader"){
                                const combined = data[8][key].auditor_name + ' (' + data[8][key].auditor_category + ')';
        leadAuditors.push(combined);
        leadAuditors1.push(data[8][key].auditor_name);
                }else if(data[8][key]["auditor_category"] == "Auditor"){
                                        const combined = data[8][key].auditor_name + ' (' + data[8][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[8][key].auditor_name);

                }else if(data[8][key]["auditor_category"] == "Technical Expert"){
                                        const combined = data[8][key].auditor_name + ' (' + data[8][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[8][key].auditor_name);

                }
        else{
                                const combined = data[8][key].auditor_name + ' (' + data[8][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[8][key].auditor_name);
        }}
    }
} else {
    console.error('auditnotifi1 is not a valid object or is empty.');
}

let leadAuditorString = leadAuditors.join(', ');
$leadauditor = leadAuditorString;
let leadAuditorString1 = leadAuditors1.join(', ');
const optionsToRemove = [];
const leadAuditorsArray = leadAuditorString1.split(', ');
leadAuditorsArray.forEach(auditor => {
  optionsToRemove.push(auditor);
});
removeUnwantedOptions('.ReviewerName1', optionsToRemove);
                    $('.leadAuditor').val($leadauditor);
                    // Enhanced Agreement Number logic with fallback
                    var agreementNumber = '';
                    if (data[0]['unique_id'] && data[0]['unique_id'].trim() !== '') {
                        agreementNumber = data[0]['unique_id'];
                    } else if (data[9] && data[9]['aggrement_no']) {
                        // Fallback to certification agreement if available
                        agreementNumber = data[9]['aggrement_no'];
                    } else {
                        // Generate temporary agreement number without TEMP suffix
                        var today = new Date();
                        var month = today.toLocaleDateString('en-US', {month: 'short'});
                        var year = today.getFullYear().toString().substr(-2);
                        agreementNumber = 'AITF/' + month + '-' + year + '/' + today.getFullYear();
                    }
                    $('.referenceNumber').val(agreementNumber);
                    var auditDate = data[1]['audit_dates'].split(', ');
                    var auditDateLen = auditDate.length;
                    var max = (Math.max(auditDateLen));
//                    alert(personLen);
                    for (var key = 0; key < max; key++) {
                        var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";
                        //$('.auditDate').val(no);
                    }
                    $('.auditDate').val(data[1]['audit_dates']);
                    $('input[type="text"]').trigger('change');
//                    $('textarea').trigger('change');
                    $('#reviewReport').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });
        $('.acceptedEdit').change(function () {
            if ($(this).val() === 'Accepted') {
            // Show the AppVerify section
            $('.AppVerify').slideToggle('slow');
        } else {
            // Hide the AppVerify section
            $('.AppVerify').hide();
        }
        });

        $('.acceptedEdit').change(function () {
            if ((this).checked) {
                $('.acceptedEdit').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $('.editReviewReport').click(function () {
            $('.serviceIdEdit').val($(this).attr('data-service'));
            $('.customerIdEdit').val($(this).attr('data-customer'));
            $('.review-report-id').val($(this).attr('data-notification'));
            loadServiceAuditorsForReviewReport($(this).attr('data-service'), $(this).attr('data-customer'));
            $.ajax({
                url: "reviewReportData.php",
                type: "GET",
                dataType: "json",
                data: {
                    reportId: $(this).attr('data-notification'),
                    serviceid: $(this).attr('data-service')
                },
                success: function (data)
                {
                    $('.auditOrganisationEdit').val(data[0]['auditOrganisation']);
                    if (data[0]['auditDate'] == "" || data[0]['auditDate'] == null)
                        $auditDate = "";
                    else
                        $auditDate = data[0]['auditDate'];
                    if (data[0]['review_date'] == "" || data[0]['review_date'] == null)
                        $review_date = "";
                    else
                        $review_date = moment(data[0]['review_date']).format('DD.MM.YYYY');
                    $('.reviewDateEdit').val($review_date);

                    $('.auditDateEdit').val(data[4]['audit_dates']);
                    let leadAuditors = [];
                    let leadAuditors1 = [];
                    if (data[2] && typeof data[2] === 'object') {
    for (const key in data[2]) {
        if (data[2].hasOwnProperty(key)) {
            if (data[2][key]["auditor_category"] == "Team Leader"){
            const combined = data[2][key].auditor_name + ' (' + data[2][key].auditor_category + ')';
        leadAuditors.push(combined);
        leadAuditors1.push(data[2][key].auditor_name);
                }else if(data[2][key]["auditor_category"] == "Auditor"){
                    const combined = data[2][key].auditor_name + ' (' + data[2][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[2][key].auditor_name);

                }else if(data[2][key]["auditor_category"] == "Technical Expert"){
                    const combined = data[2][key].auditor_name + ' (' + data[2][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[2][key].auditor_name);

                }
        else{
            const combined = data[2][key].auditor_name + ' (' + data[2][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[2][key].auditor_name);

        }}
    }
} else {
    console.error('auditnotifi1 is not a valid object or is empty.');
}


let leadAuditorString = leadAuditors.join(', ');
$leadauditor = leadAuditorString;
let leadAuditorString1 = leadAuditors1.join(', ');
const optionsToRemove = [];
const leadAuditorsArray = leadAuditorString1.split(', ');
leadAuditorsArray.forEach(auditor => {
  optionsToRemove.push(auditor);
});
removeUnwantedOptions('.reviewerNameEdit', optionsToRemove);
                    $('.leadAuditorEdit').val($leadauditor);
                    $('.ReviewerCommentsEdit1').val(data[0]['reviewer_comments']);
                    $('.reviewerNameEdit').val(data[0]['reviewer_name']);
                    $('.referenceNumberEdit').val(data[0]['referenceNumber']);
                    // Enhanced logic for assessment standard in edit mode
                    var editAllStandards = [];
                    if (data[5]) {
                        if (data[5]['assessment_standard']) editAllStandards.push(data[5]['assessment_standard'].trim());
                        if (data[5]['certification']) editAllStandards.push(data[5]['certification'].trim());
                    }
                    if (data[4] && data[4]['audit_standard']) {
                        editAllStandards.push(data[4]['audit_standard'].trim());
                    }
                    if (data[0] && data[0]['certificationStandered']) {
                        editAllStandards.push(data[0]['certificationStandered'].trim());
                    }
                    
                    var editIsoStandards = '';
                    if (editAllStandards.length > 0) {
                        editIsoStandards = editAllStandards.join(', ');
                    } else {
                        editIsoStandards = 'ISO 9001:2015';
                        console.warn('No assessment standard found in edit mode, using default: ISO 9001:2015');
                    }
                    
                    editIsoStandards = editIsoStandards.replace(/,+/g, ', ').replace(/\s*,\s*/g, ', ');
                    if (editIsoStandards) {
                        var uniqueEditStandards = [...new Set(editIsoStandards.split(',').map(function(s) { return s.trim(); }).filter(function(s) { return s; }))];
                        editIsoStandards = uniqueEditStandards.join(', ');
                    }
                    $('.assessmentStandard').val(editIsoStandards);
                    $('.acceptedEdit[value = "' + data[0]["accepted"] + '"]').prop('checked', true).trigger('change');
                    if (data[0]["remark"] == "eremark") {
                        $('.eremarkanx1edit').prop('checked', true);
                    }
                    $('.editReviewSelection').empty();
                    for (var key in data[1]) {
                        $('.editReviewSelection').append(' <div class="row" style="">\n\
                                                        <div class="col-xs-6  audit_aspect">\n\
                                                        <font size="2">' + data[1][key]['audit_aspect'] + '</font>\n\
                                                        </div>\n\
                                                        <div class="col-xs-3 form-group float-label-control"> \n\
                                                        <select name="edit_response" class="form-control edit_response" id="edit_response" required>\n\
                                                        <option value="' + data[1][key]['audit_response'] + '">' + data[1][key]['audit_response'] + '</option>\n\
                                                        <option value="Yes">Yes</option>\n\
                                                        <option value="No">No</option>\n\
                                                        <option value="NA">NA</option>\n\
                                                        </select>\n\
                                                        </div>\n\
                                                        <div class="col-xs-3 form-group float-label-control">\n\
                                                        <label></label>\n\
                                                        <input type="text" class="remark_edit" id="remark_edit" name="remark_edit" placeholder="IF ANY" value="' + data[1][key]['remark'] + '" >\n\
                                                        </div>\n\
                                                        <input type="hidden" class="audit-aspect-edit" name="audit-aspect-edit" value="' + data[1][key]['audit_aspect'] + '" >     \n\
                                                        <input type="hidden" class="audit-id" name="audit-id" id="audit-id" value="' + data[1][key]['id'] + '" >  \n\
                                                        </div>\n\
                                                        <br>');
                    }
                    $('input[type="text"]').trigger('change');
                    //                    $('textarea').trigger('change');
                    $('#updateReviewReport').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.acceptance').change(function () {
            if ((this).checked) {
                $('.acceptance').prop('checked', false);
                $(this).prop('checked', true);
            if ($(this).val() === 'Accepted') {
            // Show the AppVerify section
            $('.AppVerify').slideToggle('slow');
        } else {
            // Hide the AppVerify section
            $('.AppVerify').hide();
            $(".reviewDateEdit").val("");
        }
            }
        });

        $('.formStageIAudit').click(function () {
            $('.AppStageIAudit').slideToggle('slow');
        });

        $('.addReviewReport').click(function (e) {
//            alert($(".eremarkanx1").val());
            if (validateAnexModel()) {
                e.preventDefault();
                return;
            }

            var selectedRev = ($('.ReviewerName1').val() || '').trim();
            var confAud1 = checkReviewerConflictWithServiceAuditorsRR(selectedRev);
            if (confAud1) {
                e.preventDefault();
                $('.ReviewerName1').val('');
                showServiceReviewerConflictDialogueRR(confAud1, selectedRev);
                return false;
            }

            var aspectpoint = [];
            $(".audit-aspect-hidden").each(function () {
                aspectpoint.push($(this).val());
            });
            $('.audit-aspect-hidden_array').val(aspectpoint.join("~"));

            var auditresponse = [];
            $(".audit_response").each(function () {
                auditresponse.push($(this).val());
            });
            $('.audit-response-hidden_array').val(auditresponse.join("~"));

            var remark = [];
            $(".remark").each(function () {
                remark.push($(this).val());
            });
            $('.remark-hidden_array').val(remark.join("~"));

        });

        $('.addReviewReportPdf').click(function () {
            var aspectpoint = [];
            $(".audit-aspect-hidden").each(function () {
                aspectpoint.push($(this).val());
            });
            $('.audit-aspect-hidden_array').val(aspectpoint.join("~"));

            var auditresponse = [];
            $(".audit_response").each(function () {
                auditresponse.push($(this).val());
            });
            $('.audit-response-hidden_array').val(auditresponse.join("~"));

            var remark = [];
            $(".remark").each(function () {
                remark.push($(this).val());
            });
            $('.remark-hidden_array').val(remark.join("~"));
        });

        $('.updateReviewReport').click(function (e) {
            if (validateAnexModel1()) {
                e.preventDefault();
                return;
            }

            var selectedRevEdit = ($('.reviewerNameEdit').val() || '').trim();
            var confAudEdit = checkReviewerConflictWithServiceAuditorsRR(selectedRevEdit);
            if (confAudEdit) {
                e.preventDefault();
                $('.reviewerNameEdit').val('');
                showServiceReviewerConflictDialogueRR(confAudEdit, selectedRevEdit);
                return false;
            }

            var aspectpoint = [];

            $(".audit-aspect-edit").each(function () {
                aspectpoint.push($(this).val());
            });
            $('.audit-aspect-hidden_edit').val(aspectpoint.join("~"));

            var auditresponse = [];
            $(".edit_response").each(function () {
                auditresponse.push($(this).val());
            });
            $('.audit-response-hidden_edit').val(auditresponse.join("~"));

            var remark = [];
            $(".remark_edit").each(function () {
                remark.push($(this).val());
            });
            $('.remark-hidden_edit').val(remark.join("~"));

            var id = [];
            $(".audit-id").each(function () {
                id.push($(this).val());
            });
            $('.audit-id-update').val(id.join("~"));
        });

    });
</script>

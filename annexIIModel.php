<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
  $sql = "SELECT auditor_name FROM auditors";
$statement = $db->prepare($sql);
$statement->execute();
$auditors1 = $statement->fetchAll(PDO::FETCH_COLUMN);
?>

<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="reviewReport1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                Anex-2: Review of Audit processes
            </div>
            <form action="addAnnexIIModel.php" method="POST">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formStageIAuditII">
                            <span><i class="fa  ">Anex-2: Review of Audit processes</i></span>
                        </div>
                    </div>
                    <div class="AppStageIAuditII">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Name of Audited Organization:</label> 
                                <input type="text" class="auditOrganisationII" id="auditOrganisationII" name="auditOrganisationII" placeholder="Name of Audited Organisation:" required="">
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Date(s) of Audit Conducted::</label> 
                                <input readonly type="text" class="auditDateII" id="auditDateII" name="auditDateII" placeholder=" Date(s) of Audit Conducted:" required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Lead Auditor</label>
                                <input readonly type="text" class="leadAuditorII" id="leadAuditorII" name="leadAuditorII" placeholder="Lead Auditor:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label> Agreement number: </label> 
                                <input type="text" readonly class="referenceNumberII" id="referenceNumberII" name="referenceNumberII" placeholder="Agreement Number:" required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12" align="center">Type of audit Imparted <br>
                                <span>
                                    <input class="auditInpard" type="checkbox" name="auditInpard" value="Initial Certification(Stage-II)" checked> Initial Certification(Stage-II) &emsp;
                                    <input class="auditInpard" type="checkbox" name="auditInpard" value="Recertification"> RECERTIFICATION, &emsp;
                                    <input class="auditInpard" type="checkbox" name="auditInpard" value="Survellance"> SURVEILLANCE</span> <br>
                                <span><input class="auditInpard" type="checkbox" name="auditInpard" value="Extension"> Extension [Special Audit 
                                    <input class="auditInpard" type="checkbox" name="auditInpard" value="Scope"> Scope 
                                    <input class="auditInpard" type="checkbox" name="auditInpard" value="Site"> Site] OR 
                                    <input class="auditInpard" type="checkbox" name="auditInpard" value="Other"> Other </span>
                            </div>
                        </div>
                        <br>
                        <div class="row ">
                            <div class="col-sm-12" align="center">Reviewer Acceptance: for no Conflict of interest with the above organization <br>
                                <span> 
                                    <input class="acceptedII" type="checkbox" name="acceptedII" value="Accepted"> Accepted &emsp;
                                    <input class="acceptedII" type="checkbox" name="acceptedII" value="Not Accepted"> Not Accepted
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formAspectVerifyII">
                            <span><i class="fa ">ASPECT TO VERIFY</i></span>
                        </div>
                    </div>

                    <div class="AppVerifyII" style="display: none"><br>
                        <div class="row" style="">
                            <div class="col-xs-6"><span>ASPECT TO VERIFY:</span></div>
                            <div class="col-xs-3"><span>Yes/No/NA </span></div>
                            <div class="col-xs-3"><span>REMARK/DETAILS </span></div>
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6 audit_aspectII">
                                <font size="2">1. Audit notification and assignment of auditor sign prior to audit. </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select></div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="1. Audit notification and assignment of auditor sign prior to audit.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">2. The audit plan was sent to the client on time and audit conducted as per plan.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="2. The audit plan was sent to the client on time and audit conducted as per plan.">
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">3.The lead auditor (and technical expert) is qualified as per F 9.17</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="3. The lead auditor (and technical expert) is qualified as per F 9.17">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">4. Auditor/ Industrial declaration for no conflict of interest has been signed prior to audit.</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="4. Auditor/ Industrial declaration for no conflict of interest has been signed prior to audit.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">5. Opening and closing meeting done as per scheduled time and prompter covered. </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="5. Opening and closing meeting done as per scheduled time and prompter covered.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">6. The audit report covered all the formats applicable as per audit binder. </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="6. The audit report covered all the formats applicable as per audit binder.">
                        </div>

                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">7. Key processes identified and sample covered adequately. Assessment covered the standards requirements as per checkpoint provided in the audit details sheet F 7.26 and its Annexture/s as per the applicable standard/s. </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="7. Key processes identified and sample covered adequately. Assessment covered the standards requirements as per checkpoint provided in the audit details sheet F 7.26 and its Annexture/s as per the applicable standard/s.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">8. Action taken on the Issue/ Areas of concern raise if any during Last periodical audit are verified. </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="8. Action taken on the Issue/ Areas of concern raise if any during Last periodical audit are verified.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">9. If major NC/s, the evidence/s attached for verification and auditor has reviewed, accepted the evidences for correction and corrective actions. </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="9. If major NC/s, the evidence/s attached for verification and auditor has reviewed, accepted the evidences for correction and corrective actions.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">10. If minor NC/s, the auditor has reviewed and accepted the client’s plan for correction and corrective actions. </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="10. If minor NC/s, the auditor has reviewed and accepted the client’s plan for correction and corrective actions.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">11. Audit summery and recommendation of audit result clearly addressed. </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="11. Audit summery and recommendation of audit result clearly addressed.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6 audit_aspectII">
                                <font size="2">12. Frequency of surveillance required is decided by lead auditor. </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="12. Frequency of surveillance required is decided by lead auditor.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">13. Was objectives of the audit have been achieved. </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="13. Was objectives of the audit have been achieved.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">14. Information provided by client in audit application is confirmed by auditor. (Applicable to Initial certification/Re-certification)</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="14. Information provided by client in audit application is confirmed by auditor. (Applicable to Initial certification/Re-certification)">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">15. The effectiveness of correction and corrective action taken by client is verified by auditor for the NC raised during last audit. (Surveillance and Recertification )</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="15. The effectiveness of correction and corrective action taken by client is verified by auditor for the NC raised during last audit. (Surveillance and Recertification )">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6  audit_aspectII">
                                <font size="2">16. All the activities covered in the scope have verified by auditor and in the event if any change in scope from existing, the changes has been audited and details provided by auditor. </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="16. All the activities covered in the scope have verified by auditor and in the event if any change in scope from existing, the changes has been audited and details provided by auditor.">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6 audit_aspectII ">
                                <font size="2">17. Does performance of certified client management system verified and the original certification decision can continue? ( Surveillance Audit/ Recertification) </font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="17. Does performance of certified client management system verified and the original certification decision can continue? ( Surveillance Audit/ Recertification) ">
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-6 audit_aspectII ">
                                <font size="2">18.Contracts have been signed prior to audits. (Applicable to Re-certification/ Transfer audit)</font>
                            </div>
                            <div class="col-xs-3 form-group float-label-control">
                                <select name="audit_responseII" class="form-control audit_responseII" id="audit_responseII" required>
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>
                            <div class="col-xs-3 form-group float-label-control"><label></label> 
                                <input type="text" class="remarkII" id="remarkII" name="remarkII" placeholder="IF ANY">
                            </div>
                            <input type="hidden" class="audit_aspect_hiddenII" name="audit_aspect_hiddenII" value="18.Contracts have been signed prior to audits. (Applicable to Re-certification/ Transfer audit)">
                        </div>
                        <br>
                        <div class="row" style="" align="center">
                            <div class="col-xs-12 form-group float-label-control">
                                <input class="remark" type="checkbox" name="remark" value="YES" checked> All the audit process of audit and audit report has been verified, The information and evidences provided in the audit report are sufficient for recommendation made by auditor and acceptable to AITF. 
                            </div>
                        </div>
                        <div class="row" style="" align="center">
                            <div class="col-xs-12 form-group float-label-control">
                                <input type="text" class="ReviewerComments" id="ReviewerComments" name="ReviewerComments"  placeholder="----Reviewer Comments----">
                            </div>
                            
                        </div>

                        <div class="row" style="" align="center">
                            <div class="col-xs-6 form-group float-label-control">
                                                                <select class="form-control ReviewerName" id="ReviewerName" name="ReviewerName">
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
                                    <div class="col-sm-12"><label>Review Date:</label> 
                                        <input type="text" class="reviewDateAnnex" id="reviewDateAnnex" name="reviewDateAnnex" placeholder="Review Date:" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="reviewDateAnnexError" style="display: none">
                                            <small> <span id="reviewDateAnnexErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" class="audit_aspect_hidden_array" name="audit_aspect_hidden_array" value=""> 
                <input type="hidden" class="audit_response_hidden_array" name="audit_response_hidden_array"  value=""> 
                <input type="hidden" class="remark_hidden_array"  name="remark_hidden_array" value=""> 
                <input type="hidden" name="service_Id" class="service_Id" id="service_Id" value=""> 
                <input type="hidden" name="customer_Id" class="customer_Id" id="customer_Id" value="">
                
                <div class="modal-footer"> 
                    <button type="submit" class="btn btn-xs btn-info add_Review_ReportII">Save</button>
                    <!--<button type="submit" name="add_Review_ReportIIPdf" class="btn btn-xs btn-info add_Review_ReportIIPdf">Save & Generate</button>-->
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="updatestageII" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                Anex-2: Review of Audit processes
            </div>
            <form action="addAnnexIIModel.php" method="POST">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formStageIAuditII">
                            <span><i class="fa  ">Surveillance audit</i></span>
                        </div>
                    </div>
                    <div class="AppStageIAuditII">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label> Name of Audited Organisation:</label> 
                                <input type="text" class="auditOrganisationIIEdit" id="auditOrganisationIIEdit" name="auditOrganisationII" placeholder="Name of Audited Organisation:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Date(s) of Audit Conducted::</label> 
                                <input readonly type="text" class="auditDateIIEdit" id="auditDateIIEdit" name="auditDateII" placeholder=" Date(s) of Audit Conducted:" required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Lead Auditor</label>
                                <input readonly type="text" class="leadAuditorIIEdit" id="leadAuditorIIEdit" name="leadAuditorII" placeholder="Lead Auditor:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label> Agreement Number</label> 
                                <input type="text" readonly class="referenceNumberIIEdit" id="referenceNumberIIEdit" name="referenceNumberII" placeholder="Agreement Number:" required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12 checkbox-group required" align="center">Type of audit Imparted <br>
                                <span><input class="editauditInpard" type="checkbox" name="editauditInpard" value="Initial Certification(Stage-II)"> Initial Certification(Stage-II) &emsp;
                                    <input class="editauditInpard"  type="checkbox" name="editauditInpard" value="Recertification"> RECERTIFICATION, &emsp;
                                    <input class="editauditInpard" type="checkbox" name="editauditInpard" value="Survellance"> SURVEILLANCE</span> <br>
                                <span><input class="editauditInpard" type="checkbox" name="editauditInpard" value="Extension"> Extension [Special Audit 
                                    <input class="editauditInpard" type="checkbox" name="editauditInpard" value="Scope"> Scope 
                                    <input class="editauditInpard" type="checkbox" name="editauditInpard" value="Site"> Site] OR 
                                    <input class="editauditInpard" type="checkbox" name="editauditInpard" value="Other"> Other </span>
                            </div>
                        </div>
                        <br>
                        <div class="row ">
                            <div class="col-sm-12 " align="center">Reviewer Acceptance: for no Conflict of interest with the above organization <br>
                                <span> <input class="editacceptedII" type="checkbox" name="editacceptedII" value="Accepted"> Accepted &emsp;
                                    <input class="editacceptedII" type="checkbox" name="editacceptedII" value="Not Accepted"> Not Accepted</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12  text-center btn   qms_complete_btn formAspectVerifyII">
                            <span><i class="fa  ">ASPECT TO VERIFY</i></span>
                        </div>
                    </div>

                    <div class="AppVerifyII" style="display: none"><br>
                        <div class="row" style="">
                            <div class="col-xs-6  "><span>ASPECT TO VERIFY:</span></div>
                            <div class="col-xs-3"><span>Yes/No/NA </span></div>
                            <div class="col-xs-3"><span>REMARK/DETAILS </span></div>
                        </div>
                        <br>
                        <div class="editstageII"></div>
                        <div class="row" style="" align="center">
                            <div class="col-xs-12 form-group float-label-control">
                                <input class="eremark" type="checkbox" name="eremark" value="YES" checked> All the audit process of audit and audit report has been verified, The information and evidences provided in the audit report are sufficient for recommendation made by auditor and acceptable to AITF. 
                            </div>
                        </div>
                        <div class="row" style="" align="center">
                            <div class="col-xs-12 form-group float-label-control">
                                <label>Reviewer Comments:</label> 
                                <input type="text" class="ReviewerCommentsEdit" id="ReviewerCommentsEdit" name="ReviewerComments"  placeholder="----Reviewer Comments----">
                            </div>
                           
                        </div>

                        <div class="row" style="" align="center">
                            <div class="col-xs-6 form-group float-label-control">
                                <label>Reviewer Name:</label> 
                                                                <select class="form-control ReviewerNameEdit" id="ReviewerNameEdit" name="ReviewerNameEdit">
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
                                    <div class="col-sm-12 form-group float-label-controlF">
                                        <label>Review Date:</label>
                                        <input type="text" class="reviewDateAnnexEdit" id="reviewDateAnnexEdit" name="reviewDateAnnexEdit" placeholder="Review Date:">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 " style="">
                                        <div id="reviewDateAnnexEditError" style="display: none">
                                            <small> <span id="reviewDateAnnexEditErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" class="audit_aspect_hidden_edit"  name="audit_aspect_hidden_edit" value=""> 
                <input type="hidden"  class="audit_response_hidden_edit" name="audit_response_hidden_edit" value=""> 
                <input type="hidden" class="remark_hidden_edit" name="remark_hidden_edit" value=""> 
                <input type="hidden" name="service_Id_Edit" class="service_Id_Edit" id="service_Id_Edit" value=""> 
                <input type="hidden" name="customer_Id_Edit"  class="customer_Id_Edit" id="customer_Id_Edit" value=""> 
                <input type="hidden" name="audit_id_update" class="audit_id_update"  id="audit_id_update" value=""> 
                <input type="hidden" name="review_report_id" class="review_report_id" id="review_report_id" value="">
                <div class="modal-footer">
                    <button type="submit" name="update_Review_ReportII"  class="btn btn-xs btn-info update_Review_ReportII">Save</button>
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

       

        $('#revDateII').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#revDateII").keydown(function (e) {
//            e.preventDefault();
        });

        $('#isueDateII').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
//                    autoclose: true
        });
        $("#isueDateII").keydown(function (e) {
//            e.preventDefault();
        });

    

        $('#revDateII2').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#revDateII2").keydown(function (e) {
//            e.preventDefault();
        });

        $('#isueDateIIEdit').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
//                    autoclose: true
        });
        $("#isueDateIIEdit").keydown(function (e) {
//            e.preventDefault();
        });
        $('#reviewDateAnnex').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
//                    autoclose: true
            onSelect: function() {
                // Validate on date selection
                validateAnex2();
            }
        });
        $("#reviewDateAnnex").keydown(function (e) {
//            e.preventDefault();
        });
        $('.reviewDateAnnex').on('change blur', function() {
            // Validate Review Date on change/blur
            if ($(this).val() != "") {
                validateAnex2();
            }
        });
        $('#reviewDateAnnexEdit').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
//                    autoclose: true
        });
        $("#reviewDateAnnexEdit").keydown(function (e) {
//            e.preventDefault();
        });
    });

    function validateAnex2Edit() {
        const auditDateEdit = $('.auditDateIIEdit').val();
        const dates = auditDateEdit.split(", ");
        const boundaryDate = dates
            .map(dateValue => dateValue.trim())
            .filter(dateValue => dateValue !== "")
            .sort((left, right) => moment(left, "DD.MM.YYYY").valueOf() - moment(right, "DD.MM.YYYY").valueOf())[0];
        $isError = false;
        if ($(".reviewDateAnnexEdit").val() == "") {
            $('#reviewDateAnnexEditErrorMsg').text("Date can not be empty");
            $('#reviewDateAnnexEditError').show();
            $('#reviewDateAnnexEdit').addClass('inputHighlight');
            $isError = true;
        }else if (boundaryDate && $('.reviewDateAnnexEdit').val() != "" && (moment($('.reviewDateAnnexEdit').val(), "DD.MM.YYYY") < moment(boundaryDate, "DD.MM.YYYY"))) {
            $('#reviewDateAnnexEditErrorMsg').text("Date must be greater than or equal to Audit date.");
            $('#reviewDateAnnexEditError').show();
            $('#reviewDateAnnexEdit').addClass('inputHighlight');
            $isError = true;
        }
//        else if ($(".reviewDateAnnexEdit").val() != "") {
////            alert($('.service_Id').val());
//            $.ajax({
//                type: "GET",
//                url: "checkCustomer.php",
//                data: {
//                    reviewDateAnnex: $('.reviewDateAnnexEdit').val(),
//                    service_Id: $('.service_Id').val(),
//                },
//                async: false,
//                success: function (data) {
////    	                alert(data);	                    
//                    if (data == 1) {
//                        $('#reviewDateAnnexEditErrorMsg').text("Date should be greater than audit date");
//                        $('#reviewDateAnnexEditError').show();
//                        $('#reviewDateAnnexEdit').addClass('inputHighlight');
//                        $isError = true;
//                    } else {
//                        $('#reviewDateAnnexEditError').hide();
//                        $('#reviewDateAnnexEdit').removeClass('inputHighlight');
//                    }
//                }
//            });
//        } 
        else {
            $('#reviewDateAnnexEditError').hide();
            $('#reviewDateAnnexEdit').removeClass('inputHighlight');
        }
        return $isError;
    }

    function validateAnex2() {
        const auditDateEdit = $('.auditDateII').val();
        const dates = auditDateEdit.split(", ");
        const boundaryDate = dates
            .map(dateValue => dateValue.trim())
            .filter(dateValue => dateValue !== "")
            .sort((left, right) => moment(left, "DD.MM.YYYY").valueOf() - moment(right, "DD.MM.YYYY").valueOf())[0];
        $isError = false;
        if ($(".reviewDateAnnex").val() == "") {
//            alert("1");
            $('#reviewDateAnnexErrorMsg').text("Review Date cannot be empty");
            $('#reviewDateAnnexError').show();
            $('#reviewDateAnnex').addClass('inputHighlight');
            $isError = true;
        } else if (boundaryDate && $('.reviewDateAnnex').val() != "" && (moment($('.reviewDateAnnex').val(), "DD.MM.YYYY") < moment(boundaryDate, "DD.MM.YYYY"))) {
            $('#reviewDateAnnexErrorMsg').text("Review Date must be greater than or equal to Stage-II Audit Date");
            $('#reviewDateAnnexError').show();
            $('#reviewDateAnnex').addClass('inputHighlight');
            $isError = true;
            }else if ($(".reviewDateAnnex").val() != "") {
//            alert("2");
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    reviewDateAnnex: $('.reviewDateAnnex').val(),
                    service_Id: $('.service_Id').val(),
                },
                async: false,
                success: function (data) {
//    	                alert(data);	                    
                    if (data == 1) {
                        $('#reviewDateAnnexErrorMsg').text("Review Date must be greater than or equal to Stage-II Audit Date");
                        $('#reviewDateAnnexError').show();
                        $('#reviewDateAnnex').addClass('inputHighlight');
                        $isError = true;
                    } else {
                        $('#reviewDateAnnexError').hide();
                        $('#reviewDateAnnex').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#reviewDateAnnexError').hide();
            $('#reviewDateAnnex').removeClass('inputHighlight');
        }
        return $isError;
    }

    $(document).ready(function () {

        // code by appa for Review Report (Annex II) Update
        $('.stageIIAudit').click(function () {
            $('.service_Id').val($(this).attr('data-service'));
            $('.customer_Id').val($(this).attr('data-customer'));
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customer_ID: $(this).attr('data-customer'),
                    service_ID: $(this).attr('data-service'),
                },
                success: function (data)
                {
                    $('.auditOrganisationII').val(data[0]['customer_name']);
//                    $('.auditDateII').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));

let leadAuditors = [];
let leadAuditors1 = [];
                                        if (data[12] && typeof data[12] === 'object') {
    for (const key in data[12]) {
        if (data[12].hasOwnProperty(key)) {
            if (data[12][key]["auditor_category"] == "Team Leader"){
                                const combined = data[12][key].auditor_name + ' (' + data[12][key].auditor_category + ')';
        leadAuditors.push(combined);
        leadAuditors1.push(data[12][key].auditor_name);
                }else if(data[12][key]["auditor_category"] == "Auditor"){
                                        const combined = data[12][key].auditor_name + ' (' + data[12][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[12][key].auditor_name);

                }else if(data[12][key]["auditor_category"] == "Technical Expert"){
                                        const combined = data[12][key].auditor_name + ' (' + data[12][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[12][key].auditor_name);

                }
        else{
                                const combined = data[12][key].auditor_name + ' (' + data[12][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[12][key].auditor_name);
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
removeUnwantedOptions('.ReviewerName', optionsToRemove);

                    $('.leadAuditorII').val($leadauditor);
                    
                    $('.referenceNumberII').val(data[9]['aggrement_no']);
                    var auditDate = data[1]['audit_dates'].split(', ');
                    var auditDateLen = auditDate.length;
                    var max = (Math.max(auditDateLen));
//                    alert(personLen);
                    for (var key = 0; key < max; key++) {
                        var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";
                        $('.auditDateII').val(no);
                    }
                    $('.auditDateII').val(data[1]['audit_dates']);
                    $('input[type="text"]').trigger('change');
                    $('#reviewReport1').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.stageIIAuditSai').click(function () {
            $('.service_Id').val($(this).attr('data-service'));
            $('.customer_Id').val($(this).attr('data-customer'));
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customerIDAnx: $(this).attr('data-customer'),
                    serviceIDAnx: $(this).attr('data-service'),
                },
                success: function (data)
                {
                    $('.auditOrganisationII').val(data[0]['customer_name']);
//                    $('.auditDateII').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
let leadAuditors = [];
let leadAuditors1 = [];
                                        if (data[5] && typeof data[5] === 'object') {
    for (const key in data[5]) {
        if (data[5].hasOwnProperty(key)) {
            if (data[5][key]["auditor_category"] == "Team Leader"){
                                const combined = data[5][key].auditor_name + ' (' + data[5][key].auditor_category + ')';
        leadAuditors.push(combined);
        leadAuditors1.push(data[5][key].auditor_name);
                }else if(data[5][key]["auditor_category"] == "Auditor"){
                                        const combined = data[5][key].auditor_name + ' (' + data[5][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[5][key].auditor_name);

                }else if(data[5][key]["auditor_category"] == "Technical Expert"){
                                        const combined = data[5][key].auditor_name + ' (' + data[5][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[5][key].auditor_name);

                }
        else{
                                const combined = data[5][key].auditor_name + ' (' + data[5][key].auditor_category + ')';
        leadAuditors.push(combined);
                leadAuditors1.push(data[5][key].auditor_name);
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
removeUnwantedOptions('.ReviewerName', optionsToRemove);
                    $('.leadAuditorII').val($leadauditor);
                    $('.referenceNumberII').val(data[4]['aggrement_no']);
                    var auditDate = data[1]['audit_dates'].split(', ');
                    var auditDateLen = auditDate.length;
                    var max = (Math.max(auditDateLen));
//                    alert(personLen);
                    for (var key = 0; key < max; key++) {
                        var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";
                        $('.auditDateII').val(no);
                    }
                    $('.auditDateII').val(data[1]['audit_dates']);
                    $('input[type="text"]').trigger('change');
                    $('#reviewReport1').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.remark').change(function () {
            if ((this).checked)
            {
                $('.remark').val("YES");
            } else if ((this).unchecked)
            {
                $('.remark').val("NO");
            }
        });
        $('.eremark').change(function () {
            if ((this).checked)
            {
                $('.eremark').val("YES");
            }
            if ((this).unchecked)
            {
                $('.eremark').val("NO");
            }
        });
        $('.auditInpard').change(function () {
            if ((this).checked) {
                $('.auditInpard').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $('.acceptedII').change(function () {
            if ((this).checked) {
                $('.acceptedII').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $('.editauditInpard').change(function () {
            if ((this).checked) {
                $('.editauditInpard').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $('.editacceptedII').change(function () {
            if ((this).checked) {
                $('.editacceptedII').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $('.editstageIIAudit2 ').click(function () {
            $(".service_id").val($(this).attr("data-service"));
            //alert($(".service_id").val());
            // alert($(this).attr('data-notification'));
            $('.service_Id_Edit').val($(this).attr('data-service'));
            $('.customer_Id_Edit').val($(this).attr('data-customer'));
            $('.review_report_id').val($(this).attr('data-notification'));

            $.ajax({
                url: "reviewReportData.php",
                type: "GET",
                dataType: "json",
                data: {
                    report_Id: $(this).attr('data-notification'),
                    service_id: $(this).attr('data-service')
                },
                success: function (data)
                {
                                        //alert(data[0]["accepted"]);
                    $('.auditOrganisationIIEdit').val(data[0]['audit_Organisation']);
                    if (data[4]['audit_dates'] == "" || data[4]['audit_dates'] == null)
                        $audit_dates = data[5]['audit_dates'];
                    else
                        $audit_dates = data[4]['audit_dates'];
                    $('.auditDateIIEdit').val($audit_dates);
                    if (data[0]['review_date'] == "" || data[0]['review_date'] == null)
                        $review_date = "";
                    else
                        $review_date = moment(data[0]['review_date']).format('DD.MM.YYYY');
                    $('.reviewDateAnnexEdit').val($review_date);
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

                    $('.leadAuditorIIEdit').val($leadauditor);
                    $('.referenceNumberIIEdit').val(data[0]['reference_number']);
                    $('.editauditInpard[value = "' + data[0]["initial"] + '"]').prop('checked', true);
                    $('.editacceptedII[value = "' + data[0]["accepted"] + '"]').prop('checked', true).trigger('change');

                    $('.ReviewerCommentsEdit').val(data[0]['reviewer_comments']);
                    $('.ReviewerNameEdit').val(data[0]['ReviewerName']); 
                    if (data[0]["remark_check"] == "YES")
                    {
                        $('.eremark').prop('checked', true);
                        $('.eremark').val("YES");
                    } else if (data[0]["remark_check"] == "NO")
                    {
                        $('.eremark').prop('checked', false);
                        $('.eremark').val("NO");
                    }
                    $('.editstageII').empty();
                    for (var key in data[1]) {
                        $('.editstageII').append('<div class="row" style=""> \n\
                                                    <div class="col-xs-6 audit_aspectII "> \n\
                                                    <font size="2">' + data[1][key]['audit_aspect_ii'] + '</font> \n\
                                                    </div> \n\
                                                    <div class="col-xs-3 form-group float-label-control"> \n\
                                                    <select name="audit_responseII" class="form-control audit_responseII_Edit" id="audit_responseII" required> \n\
                                                    <option value="' + data[1][key]['audit_response_ii'] + '">' + data[1][key]['audit_response_ii'] + '</option> \n\
                                                    <option value="Yes">Yes</option> \n\
                                                    <option value="No">No</option> \n\
                                                    <option value="NA">NA</option> \n\
                                                    </select> \n\
                                                    </div> \n\
                                                    <div class="col-xs-3 form-group float-label-control"> \n\
                                                    <label></label> \n\
                                                    <input type="text" class="remarkII_Edit" id="remarkII" name="remarkII" value="' + data[1][key]['remark_ii'] + '" placeholder="IF ANY" > \n\
                                                    </div> \n\
                                                    <input type="hidden" class="audit_aspect_hiddenII_Edit" name="audit_aspect_hiddenII" value="' + data[1][key]['audit_aspect_ii'] + '" >  \n\
                                                    <input type="hidden" class="audit_id" name="audit_id" value="' + data[1][key]['id_ii'] + '" >  \n\
                                                    </div> \n\
                                                    <br>');
                    }
                    $('input[type="text"]').trigger('change');
                    //                    $('textarea').trigger('change');

                    $('#updatestageII').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.editstageIIAudit2Sii ').click(function () {
            // alert($(this).attr('data-notification'));
            $('.service_Id_Edit').val($(this).attr('data-service'));
            $('.customer_Id_Edit').val($(this).attr('data-customer'));
            $('.review_report_id').val($(this).attr('data-notification'));

            $.ajax({
                url: "reviewReportData.php",
                type: "GET",
                dataType: "json",
                data: {
                    report_Id: $(this).attr('data-notification'),
                    service_id: $(this).attr('data-service')
                },
                success: function (data)
                {
                    //alert(data);
                    $('.auditOrganisationIIEdit').val(data[0]['audit_Organisation']);
                    if (data[0]['audit_dates'] == "" || data[0]['audit_dates'] == null)
                        $audit_dates = "";
                    else
                        $audit_dates = moment(data[0]['audit_dates']).format('DD.MM.YYYY');
                    $('.auditDateIIEdit').val($audit_dates);
                    alert("hi");
                    let leadAuditors = [];
                    if (data[2] && typeof data[2] === 'object') {
    for (const key in data[2]) {
        if (data[2].hasOwnProperty(key)) {
            if (data[2][key]["auditor_category"] == "Team Leader"){
            leadAuditors.push(data[2][key].auditor_name);
                }else if(data[2][key]["auditor_category"] == "Auditor"){
                    leadAuditors.push(data[2][key].auditor_name);
                }else if(data[2][key]["auditor_category"] == "Technical Expert"){
                    leadAuditors.push(data[2][key].auditor_name);
                }
        else{
            leadAuditors.push(data[2][key].auditor_name);
        }}
    }
} else {
    console.error('auditnotifi1 is not a valid object or is empty.');
}
if (data[3] && typeof data[3] === 'object') {
    for (const key in data[3]) {
        if (data[3].hasOwnProperty(key)) {
            if (data[3][key]["auditor_category"] == "Team Leader"){
            leadAuditors.push(data[3][key].auditor_name);
                }else if(data[3][key]["auditor_category"] == "Auditor"){
                    leadAuditors.push(data[3][key].auditor_name);
                }else if(data[3][key]["auditor_category"] == "Technical Expert"){
                    leadAuditors.push(data[3][key].auditor_name);
                }
        else{
            leadAuditors.push(data[3][key].auditor_name);
        }}
    }
} else {
    console.error('auditnotifi1 is not a valid object or is empty.');
}

let leadAuditorString = leadAuditors.join(', ');
$leadauditor = leadAuditorString;

                    $('.leadAuditorIIEdit').val($leadauditor);
                    $('.referenceNumberIIEdit').val(data[0]['reference_number']);

                    $('.editauditInpard[value = "' + data[0]["initial"] + '"]').prop('checked', true);
                    $('.editacceptedII[value = "' + data[0]["accepted"] + '"]').prop('checked', true);
                    $('.formNo2').val(data[0]['form_no']);
                    $('.rev2').val(data[0]['rev']);
                    if (data[0]["remark_check"] == "YES")
                        $('.eremark').prop("cheked", true);
                    else if (data[0]["remark_check"] == "NO")
                        $('.eremark').prop("cheked", false);
                    if (data[0]['date_rev'] == "" || data[0]['date_rev'] == null)
                        $date_rev = "";
                    else
                        $date_rev = moment(data[0]['date_rev']).format('DD.MM.YYYY');
                    $('.revDateII2').val($date_rev);
                    $('.isueEdit2').val(data[0]['issue']);
                    if (data[0]['date_issue'] == "" || data[0]['date_issue'] == null)
                        $date_issue = "";
                    else
                        $date_issue = moment(data[0]['date_issue']).format('DD.MM.YYYY');
                    $('.isueDateIIEdit').val($date_issue);
                    $('.ReviewerCommentsEdit').val(data[0]['reviewer_comments']);
                    $('.ReviewerNameEdit').val(data[0]['ReviewerName']); 

                    $('.editstageII').empty();
                    for (var key in data[1]) {
                        // var option= data[1][key]['audit_response_ii'] ;

                        $('.editstageII').append('  <div class="row" style=""> \n\
                 <div class="col-xs-6 audit_aspectII "> \n\
               <font size="2">' + data[1][key]['audit_aspect_ii'] + '</font> \n\
               </div> \n\
                <div class="col-xs-3 form-group float-label-control"> \n\
                                                <select name="audit_responseII" class="form-control audit_responseII_Edit" id="audit_responseII" required> \n\
                                                    <option value="' + data[1][key]['audit_response_ii'] + '">' + data[1][key]['audit_response_ii'] + '</option> \n\
                                                    <option value="Yes">Yes</option> \n\
                                                    <option value="No">No</option> \n\
                                                    <option value="NA">NA</option> \n\
                                                </select> \n\
                                            </div> \n\
                   <div class="col-xs-3 form-group float-label-control"> \n\
                <label></label> \n\
                  <input type="text" class="remarkII_Edit" id="remarkII" name="remarkII" value="' + data[1][key]['remark_ii'] + '" placeholder="IF ANY" required > \n\
                </div> \n\
                <input type="hidden" class="audit_aspect_hiddenII_Edit" name="audit_aspect_hiddenII" value="' + data[1][key]['audit_aspect_ii'] + '" >  \n\
               <input type="hidden" class="audit_id" name="audit_id" value="' + data[1][key]['id_ii'] + '" >  \n\
                                                                        </div> \n\
                                                                        <br>');
                    }
                    $('input[type="text"]').trigger('change');
                    //                    $('textarea').trigger('change');

                    $('#updatestageII').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.editstageIIAudit2SP').click(function () {
            // alert($(this).attr('data-notification'));
            $('.service_Id_Edit').val($(this).attr('data-service'));
            $('.customer_Id_Edit').val($(this).attr('data-customer'));
            $('.review_report_id').val($(this).attr('data-notification'));

            $.ajax({
                url: "reviewReportData.php",
                type: "GET",
                dataType: "json",
                data: {
                    report_Id: $(this).attr('data-notification'),
                    service_id: $(this).attr('data-service')
                },
                success: function (data)
                {
                    //alert(data);
                    $('.auditOrganisationIIEdit').val(data[0]['audit_Organisation']);
                    if (data[0]['audit_dates'] == "" || data[0]['audit_dates'] == null)
                        $audit_dates = "";
                    else
                        $audit_dates = moment(data[0]['audit_dates']).format('DD.MM.YYYY');
                    $('.auditDateIIEdit').val($audit_dates);
                    let leadAuditors = [];
                    if (data[2] && typeof data[2] === 'object') {
    for (const key in data[2]) {
        if (data[2].hasOwnProperty(key)) {
            if (data[2][key]["auditor_category"] == "Team Leader"){
            leadAuditors.push(data[2][key].auditor_name);
                }else if(data[2][key]["auditor_category"] == "Auditor"){
                    leadAuditors.push(data[2][key].auditor_name);
                }else if(data[2][key]["auditor_category"] == "Technical Expert"){
                    leadAuditors.push(data[2][key].auditor_name);
                }
        else{
            leadAuditors.push(data[2][key].auditor_name);
        }}
    }
} else {
    console.error('auditnotifi1 is not a valid object or is empty.');
}
if (data[3] && typeof data[3] === 'object') {
    for (const key in data[3]) {
        if (data[3].hasOwnProperty(key)) {
            if (data[3][key]["auditor_category"] == "Team Leader"){
            leadAuditors.push(data[3][key].auditor_name);
                }else if(data[3][key]["auditor_category"] == "Auditor"){
                    leadAuditors.push(data[3][key].auditor_name);
                }else if(data[3][key]["auditor_category"] == "Technical Expert"){
                    leadAuditors.push(data[3][key].auditor_name);
                }
        else{
            leadAuditors.push(data[3][key].auditor_name);
        }}
    }
} else {
    console.error('auditnotifi1 is not a valid object or is empty.');
}

let leadAuditorString = leadAuditors.join(', ');
$leadauditor = leadAuditorString;

                    $('.leadAuditorIIEdit').val($leadauditor);
                    $('.referenceNumberIIEdit').val(data[0]['reference_number']);

                    $('.editauditInpard[value = "' + data[0]["initial"] + '"]').prop('checked', true);
                    $('.editacceptedII[value = "' + data[0]["accepted"] + '"]').prop('checked', true);
                    $('.formNo2').val(data[0]['form_no']);
                    $('.rev2').val(data[0]['rev']);
                    if (data[0]["remark_check"] == "YES")
                        $('.eremark').prop("cheked", true);
                    else if (data[0]["remark_check"] == "NO")
                        $('.eremark').prop("cheked", false);
                    if (data[0]['date_rev'] == "" || data[0]['date_rev'] == null)
                        $date_rev = "";
                    else
                        $date_rev = moment(data[0]['date_rev']).format('DD.MM.YYYY');
                    $('.revDateII2').val($date_rev);
                    $('.isueEdit2').val(data[0]['issue']);
                    if (data[0]['date_issue'] == "" || data[0]['date_issue'] == null)
                        $date_issue = "";
                    else
                        $date_issue = moment(data[0]['date_issue']).format('DD.MM.YYYY');
                    $('.isueDateIIEdit').val($date_issue);
                    $('.ReviewerCommentsEdit').val(data[0]['reviewer_comments']); 
                    $('.ReviewerNameEdit').val(data[0]['ReviewerName']); 
                    

                    $('.editstageII').empty();
                    for (var key in data[1]) {
                        // var option= data[1][key]['audit_response_ii'] ;

                        $('.editstageII').append('  <div class="row" style=""> \n\
                 <div class="col-xs-6 audit_aspectII "> \n\
               <font size="2">' + data[1][key]['audit_aspect_ii'] + '</font> \n\
               </div> \n\
                <div class="col-xs-3 form-group float-label-control"> \n\
                                                <select name="audit_responseII" class="form-control audit_responseII_Edit" id="audit_responseII" required> \n\
                                                    <option value="' + data[1][key]['audit_response_ii'] + '">' + data[1][key]['audit_response_ii'] + '</option> \n\
                                                    <option value="Yes">Yes</option> \n\
                                                    <option value="No">No</option> \n\
                                                    <option value="NA">NA</option> \n\
                                                </select> \n\
                                            </div> \n\
                   <div class="col-xs-3 form-group float-label-control"> \n\
                <label></label> \n\
                  <input type="text" class="remarkII_Edit" id="remarkII" name="remarkII" value="' + data[1][key]['remark_ii'] + '" placeholder="IF ANY" required > \n\
                </div> \n\
                <input type="hidden" class="audit_aspect_hiddenII_Edit" name="audit_aspect_hiddenII" value="' + data[1][key]['audit_aspect_ii'] + '" >  \n\
               <input type="hidden" class="audit_id" name="audit_id" value="' + data[1][key]['id_ii'] + '" >  \n\
                                                                        </div> \n\
                                                                        <br>');
                    }
                    $('input[type="text"]').trigger('change');
                    //                    $('textarea').trigger('change');

                    $('#updatestageII').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        //autocomlete for Auditor Name
        $(document).on('keyup.autocomplete', ".leadAuditorII", function () {
            $getVal = $(this).val();
            $(this).autocomplete({
                source: function (request, response) {
                    //alert($('.leadAuditorII').val());
                    $.ajax({
                        url: "search.php",
                        dataType: "json",
                        type: 'GET',
                        data: {
                            auditor: $('.leadAuditorII').val(),
                        },
                        success: function (data) {
                            //                                                    alert(data);
                            response(data);
                        },
                        error: function (data, status, err) {
                            //                        alert(status);
                        }
                    });
                },
                minLength: 1,
                select: function (event, ui) {
                },
                open: function () {
                    $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
                },
                close: function () {
                    $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
                }
            });
        });


        $('.formStageIAuditII').click(function () {
            $('.AppStageIAuditII').slideToggle('slow');

        });
        
        $('.acceptedII').change(function () {
            if ((this).checked) {
                $('.acceptedII').prop('checked', false);
                $(this).prop('checked', true);
            if ($(this).val() === 'Accepted') {
            // Show the AppVerify section
            $('.AppVerifyII').slideToggle('slow');
        } else {
            // Hide the AppVerify section
            $('.AppVerifyII').hide();
            $(".reviewDateAnnex").val("");
        }
            }
        });
        
        $('.editacceptedII').change(function () {
            if ($(this).val() === 'Accepted') {
            // Show the AppVerify section
            $('.AppVerifyII').slideToggle('slow');
        } else {
            // Hide the AppVerify section
            $('.AppVerifyII').hide();
        }
        });

        $('.update_Review_ReportII').click(function (e) {
            if (validateAnex2Edit()) {
                e.preventDefault();
                return;
            }

            var aspectpoint = [];
            $(".audit_aspect_hiddenII_Edit").each(function () {
                aspectpoint.push($(this).val());
            });
            $('.audit_aspect_hidden_edit').val(aspectpoint.join("~"));

            var auditresponse = [];
            $(".audit_responseII_Edit").each(function () {
                auditresponse.push($(this).val());
            });
            $('.audit_response_hidden_edit').val(auditresponse.join("~"));

            var remark = [];
            $(".remarkII_Edit").each(function () {
                remark.push($(this).val());
            });
            $('.remark_hidden_edit').val(remark.join("~"));

            var id = [];
            $(".audit_id").each(function () {
                id.push($(this).val());
            });
            $('.audit_id_update').val(id.join("~"));
        });

        $('.add_Review_ReportII').click(function (e) {

            if (validateAnex2()) {
//                alert("in");
                e.preventDefault();
                return;
            }

            var point = [];
            $(".audit_aspect_hiddenII").each(function () {
                point.push($(this).val());
            });
            $('.audit_aspect_hidden_array').val(point.join("~"));

            var response = [];
            $(".audit_responseII").each(function () {
                response.push($(this).val());
            });
            $('.audit_response_hidden_array').val(response.join("~"));

            var remarkII = [];
            $(".remarkII").each(function () {
                remarkII.push($(this).val());
            });
            $('.remark_hidden_array').val(remarkII.join("~"));
        });

        $('.add_Review_ReportIIPdf').click(function () {
            var point = [];
            $(".audit_aspect_hiddenII").each(function () {
                point.push($(this).val());
            });
            $('.audit_aspect_hidden_array').val(point.join("~"));

            var response = [];
            $(".audit_responseII").each(function () {
                response.push($(this).val());
            });
            $('.audit_response_hidden_array').val(response.join("~"));

            var remarkII = [];
            $(".remarkII").each(function () {
                remarkII.push($(this).val());
            });
            $('.remark_hidden_array').val(remarkII.join("~"));
        });

    });

</script>


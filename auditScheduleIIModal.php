<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once './dbConnect.php';

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

<div class="modal fade" id="scheduleStageII" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                Audit Plan - Stage 2 / RE-CERTIFICATION
            </div>  
            <form action="addScheduleIIStage.php" method="post">
                <div class="modal-body" >
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn">
                            <span><i class="fa  ">Audit Plan - Stage 2</i></span>                            
                        </div>
                    </div>
                    <div class="formScheduleIIDiv">
                        <div class="row " style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Client Name:</label>
                                <input type="text" class="scheduleIIClientName" id="scheduleIIClientName" name="scheduleIIClientName" placeholder="Client Name:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Standard:</label>
                                <textarea name="scheduleIIStandard" readonly class="form-control form-group scheduleIIStandard" id="scheduleIIStandard" placeholder="Assessment Standard" required></textarea>
                            </div>
                        </div>                         
                        <div class="row " >                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Site(s):</label>
                                <input type="text" class="scheduleIIAddress" id="scheduleIIAddress" name="scheduleIIAddress" placeholder="Site(s):" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Date(s):</label>
                                <input type="text" class="scheduleIIDate" id="scheduleIIDate" name="scheduleIIDate" placeholder="Date(s):" required>
                            </div>
                        </div>
                        <div class="row " >                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Scope of Certification:</label>
                                <input type="text" class="scheduleIICertScope" id="scheduleIICertScope" name="scheduleIICertScope" placeholder="Scope of Certification:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Mandays:</label>
                                <input type="text" class="scheduleIIMandays" id="scheduleIIMandays" name="scheduleIIMandays" placeholder="Mandays:" required>
                            </div>
                        </div>
                        <div class="row " >                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Client representative:</label>
                                <input type="text" class="scheduleIIMgmtRep" id="scheduleIIMgmtRep" name="scheduleIIMgmtRep" placeholder="Client representative:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Audit Type:</label>
                                <input type="text" class="scheduleIIAuditType" id="scheduleIIAuditType" name="scheduleIIAuditType" value="Initial Stage II audit/ Recertification" placeholder="Audit Type:" required>
                            </div>
                        </div>
                        <div class="row" > 
                            <div class="col-sm-12 form-group float-label-control">
                                <span>Member/s Presence during audit:</span>
                                <input type="text" class="scheduleIIAuditorName" id="scheduleIIAuditorName" name="scheduleIIAuditorName" placeholder="Auditor/s Name:" value="">
                            </div>
                        </div>
                    </div>  
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn">
                            <span><i class="fa  ">Audit Plan - Stage 2</i></span>
                            <a class="newStage2Schedule"  style="float: right" title="Add New"><i class="fa fa-plus-circle"></i></a>
                        </div>
                    </div>
                    <div class="" >
                        <div class="row formScheduleIIDivBtn" style="padding-top: 10px">
                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <input type="text" class="scheduleIIDepartment" name="scheduleIIDepartment" value="Arrive at site" >
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime"  value="09:00 AM" >
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="-" name="scheduleIIClaudeReference" placeholder="Clause reference:">
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">                                        
                                        <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>Opening meeting with senior management, Preparation and planning for audit</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime"  value="09:15 AM" >
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="-" name="scheduleIIClaudeReference" placeholder="Clause reference:">
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>QMS general Processes Verification of actions taken for last audit findings (areas of concerns). Scope of the QMS, organisation processes, external provided processes, products and services, , statutory and regulatory requirements, Understanding the organisation and its context (monitoring and review of information about external and internal issues that considering positive and negative factors); Understanding the needs and expectation of interested parties; Documented Information – Creating, Updating & Controlling, Planning of changes (client's methodology for changes in QMS).</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime"  value="09:45 AM">
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="4.1; 4.2; 4.3;4.4 5.3 6.1; 6,2,6.3 7.1; 7.2; 7.3; 7.4; 7.5 9.1 10.1; 10.2; 10.3" name="scheduleIIClaudeReference" placeholder="Clause reference:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>Top Management Establishing and Communicating the quality policy and the quality objectives.  Quality Objectives and planning, Monitoring measurement analysis and evaluation Organization’s roles, responsibilities & authorities, organizational chart, customer requirements, Customer Satisfaction, Customers complaints, nonconformity and corrective action, Internal audits, Management review; Continual Improvement Actions to address Risks & Opportunities (with mitigation and/or contingency if any)</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime"  value="11:15 AM" >
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="5.1, 5.2, 5.3 6.1, 6.2 7.1, 9.1, 9.2, 9.3, 10.1, 10.2, 10.3 " name="scheduleIIClaudeReference" placeholder="Clause reference:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>Marketing/ Contract review/ Operational Planning and control Customer communication  (Covering Product information, Enquiries, contract handling, amendments, Handling/controlling customer property, Requirements for contingency actions) Determination and review of req. related to product /services, Operational planning and control. Changes to req. related for product/services, Control of changes.</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime"  value="12:15 PM" >
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="8.2; 8.1, 8.5.6 10.1; 10.2; 10.3 " name="scheduleIIClaudeReference" placeholder="Clause reference:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>Design & Dev. Planning; Inputs; Controls ( Review, Verification, Validation); Outputs; Changes</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime"  value="NA" >
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="7.1; 8.3, 10.1; 10.2; 10.3" name="scheduleIIClaudeReference" placeholder="Clause reference:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>Human Resources People; Competence; Awareness; Communication; Organizational Knowledge</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime" value="13.00 PM">
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="7.1, 7.2, 7.3, 7.4, 7.5 10.1; 10.2; 10.3" name="scheduleIIClaudeReference" placeholder="Clause reference:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>Production/ Service Provision Control of production & service provision, Environment for the operation of processes, Identification and traceability, Property belonging to customer / external providers and its control (8.4), Release of products & services (In-process Inspection), Control of nonconforming outputs (In-process).</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:">
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime" required value="14:00 PM">
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="7.1; 8.5, 8.6, 8.7, 10.1; 10.2; 10.3" name="scheduleIIClaudeReference" placeholder="Clause reference:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>Maintenance Infrastructure (Including building, M/c, equipment and its maintenance.</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime" required value="15:30 PM">
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="8.2; 7.1 10.1; 10.2; 10.3" name="scheduleIIClaudeReference" placeholder="Clause reference:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>Quality Control Release of products and service Release of products & services (Final Inspection) & Analysis & evaluation, Person authorizing to release. (Traceability if requirement) Control of nonconforming outputs (final Inspection) - Analysis & evaluation and Corrective Actions Monitoring and measuring resources; Measurement traceability Suitability and evidence of its continuing fitness.</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime" required value="16:00 PM" >
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="7.1; 8.6, 8.7, 9.1 10.1; 10.2; 10.3" name="scheduleIIClaudeReference" placeholder="Clause reference:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>Purchase External providers Control on external providers & Analysis & evaluation (Elaborate all the processes or part of process which is provided by external provider and their evaluation, selection, monitoring of performance, re-evaluation, etc...) Type & extent of control on process, products & services provided by external providers, i.e. controls like (Receiving Inspection) , Control of nonconforming outputs ( Receiving) if any), Information for external providers</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime" required value="17:00 PM">
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="8.4, 8.7 10.1; 10.2; 10.3" name="scheduleIIClaudeReference" placeholder="Clause reference:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>Storage and Sale Preservation (Identification, handling, contamination control, packaging, storage, transmission or transportation & protection); Post Delivery activities (Actions under warranty provision, contractual obligations such as maintenance & supplementary services)</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime" required value="17:30 PM">
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="7.1; 8.5, 10.1; 10.2; 10.3" name="scheduleIIClaudeReference" placeholder="Clause reference:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <input type="text" class="scheduleIIDepartment" name="scheduleIIDepartment" value="Auditor Time">
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime" required value="17:45 PM">
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="-" name="scheduleIIClaudeReference" placeholder="Clause reference:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <input type="text" class="scheduleIIDepartment" name="scheduleIIDepartment" value="Closing Meeting ">
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime" value="18:00 PM">
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="-" name="scheduleIIClaudeReference" placeholder="Clause reference:">
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <div class="row stage1schedulerow" style="">
                                <div class="col-sm-11 form-group float-label-control">
                                    <div class="row form-group float-label-control" style="">
                                        <input type="text" class="scheduleIIDepartment" name="scheduleIIDepartment" value="End of audit.">
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Date:</label>
                                            <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" >
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Time:</label>
                                            <input type="text" class="scheduleIITime" name="scheduleIITime" required value="18:15 PM">
                                        </div>                                
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Clause reference:</label>
                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" value="-" name="scheduleIIClaudeReference" placeholder="Clause reference:">
                                        </div>
                                        <div class="col-sm-3 form-group float-label-control">
                                            <label>Auditor:</label>
                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="col-sm-1 removestage1schedulecol">
                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>
                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>                          
                        </div> 
                        <div class="row" style="">
                            <div class="col-xs-12  text-center btn  qms_complete_btn formSchedule">
                                <span><i class="fa  ">Lunch Time</i></span>
                            </div>
                        </div><br>
                        <div class="row " style="">                                
                            <div class="col-sm-4 form-group float-label-control">
                                <label>Lunch Time</label>
                                <input type="text" class="stageIIlunch" name="stageIIlunch" value="Lunch Time" placeholder="Lunch Time" required>
                            </div>
                            <div class="col-sm-4 form-group float-label-control">
                                <label>Time From:</label>
                                <input type="text" class="stageIItimeFrom" name="stageIItimeFrom" value="13:30 PM" placeholder="Time From:" required>
                            </div>
                            <div class="col-sm-4 form-group float-label-control">
                                <label>Time To:</label>
                                <input type="text" class="stageIItimeTo" name="stageIItimeTo" value="14:00 PM" placeholder="Time To:" required>
                            </div>
                        </div>
                        <div class="row " style="">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Prepared By:</label>
                                <input type="text" class="preparedByStageII" id="preparedByStageII" name="preparedByStageII" value="" placeholder="Prepared By:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Date:</label>
                                <input type="text" class="dateStageII" id="dateStageII" name="dateStageII" value="" placeholder="Date:" required>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="scheduleii-id" class="scheduleii-id" value="" >
                <input type="hidden" name="scheduleii-service-id" class="scheduleii-service-id" value="">
                <input type="hidden" name="scheduleii-customer-id" class="scheduleii-customer-id" value="">
                <input type="hidden" name="scheduleIIDatesHidden" class="scheduleIIDatesHidden" value="" >
                <input type="hidden" name="scheduleIITimeHidden" class="scheduleIITimeHidden" value="" >
                <input type="hidden" name="scheduleIIClaudeReferenceHidden" class="scheduleIIClaudeReferenceHidden" value="" >
                <input type="hidden" name="scheduleIIAuditorHidden" class="scheduleIIAuditorHidden" value="" >
                <input type="hidden" name="scheduleIIDepartmentHidden" class="scheduleIIDepartmentHidden" value="" >
                <div class="modal-footer">
                    <button type="submit" name="addscheduleIIAudit" class="btn btn-xs btn-info addscheduleIIAudit">Save</button>
                    <!--<button type="submit" name="addscheduleIIAuditPdf" class="btn btn-xs btn-info addscheduleIIAuditPdf">Save & Generate</button>-->
                </div>
            </form>
        </div>
    </div>
</div>

<style>

    hr { 
        display: block;
        margin-top: 0.5em;
        margin-bottom: 0.5em;
        margin-left: auto;
        margin-right: auto;
        border-style: inset;
        border-width: 1px;
        border-color: #f5821f;
    } 
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
    #scheduleIIDates{
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
    #scheduleIITime{
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
    #scheduleIIClaudeReference{
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
    #scheduleIIAuditor{
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
//        $('#scheduleIIDate').datepicker({
//            minDate: '-100Y',
//            maxDate: '+100Y',
//            changeYear: true,
//            changeMonth: true,
//            yearRange: "-20:+100",
//            dateFormat: 'dd.mm.yy',
//            autoclose: true
//        });
//        $("#scheduleIIDate").keydown(function (e) {
////            e.preventDefault();
//        });

        $('#dateStageII').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy'
        });

        $("#dateStageII").keydown(function (e) {
            //            e.preventDefault();
        });

        $('#stage2RevDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#stage2RevDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#stage2IsueDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#stage2IsueDate").keydown(function (e) {
//            e.preventDefault();
        });
    });

    $(document).ready(function () {

        $(".newStage2Schedule").click(function () {
//            alert("in");
            $('.formScheduleIIDivBtn').append('<div class="row stage1schedulerow" style="">\n\
                                                <div class="col-sm-11 form-group float-label-control">\n\
                                                    <div class="row form-group float-label-control" style="">\n\
                                                    <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required></textarea>\n\
                                                    </div>\n\
                                                    <div class="row">\n\
                                                        <div class="col-sm-3 form-group ">\n\
                                                            <label class="dispClass">Date:</label>\n\
                                                            <input type="text" class="scheduleIIDates" id="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" value="">\n\
                                                        </div>\n\
                                                        <div class="col-sm-3 form-group ">\n\
                                                            <label class="dispClass">Time:</label>\n\
                                                            <input type="text" class="scheduleIITime" id="scheduleIITime" name="scheduleIITime" placeholder="Time:" value="">\n\
                                                        </div>\n\
                                                        <div class="col-sm-3 form-group ">\n\
                                                            <label class="dispClass">Clause reference:</label>\n\
                                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" name="scheduleIIClaudeReference" placeholder="Clause reference:" value="">\n\
                                                        </div>\n\
                                                        <div class="col-sm-3 form-group ">\n\
                                                            <label class="dispClass">Auditor:</label>\n\
                                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" placeholder="Auditor:">\n\
                                                        </div>\n\
                                                    </div><hr>\n\
                                                </div>\n\
                                                <div class="col-sm-1 removestage1schedulecol">\n\
                                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>\n\
                                                </div>\n\
                                            </div>');
        });

        $(".createStageIISchedule").click(function () {
//            alert("in");
//            $('#scheduleStageII').modal('show');
            $('.scheduleii-customer-id').val($(this).attr('data-customer'));
            $('.scheduleii-service-id').val($(this).attr('data-service'));
            var serviceType = $(this).attr('data-serviceType');
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
                    //alert(data)
                    if (data[4]['aif_code'] == undefined) {
                        var aifnace = data[0]['aif_nace'].split('/');
                        $('.scheduleIAFcode').val(aifnace[0]);
                        $(".scheduleNacecode").val(aifnace[1]);
                        $('.scheduleIICertScope').val(data[0]['scope']);
                    } else {
                        $('.scheduleIAFcode').val(data[4]['aif_code']);
                        $(".scheduleNacecode").val(data[4]["nace_code"]);
                        $('.scheduleIICertScope').val(data[4]['proposed_scope']);
                    }
//                  
                    if (serviceType != "" || serviceType != null) {
                        $('.scheduleAuditType').val(serviceType);
                    }
                    $('.scheduleIIClientName').val(data[0]['customer_name']);
                    $('.scheduleIIAddress').val(data[0]['address']);
                    $('.scheduleRefNo').val(data[0]['unique_id']);

                    $('.scheduleContactPerson').val(data[0]['contact_person']);
                    // Auto-populate Client representative field
                    if (data[0]['contact_person']) {
                        $('.scheduleIIMgmtRep').val(data[0]['contact_person']);
                    }
//                    alert(data[1]['audit_standard']);
//                    alert(data[3]['certification']);
                    if (data[3]['certification']) {
//                        alert("in if");
                        $('.scheduleIIStandard').val(data[3]['certification']);
                    } else {
//                        alert("in else");
                        $('.scheduleIIStandard').val(data[1]['audit_standard']);
                    }
                    $('.scheduleIIMandays').val(data[5]);
                    var auditors = "";
                    var auditorsabv = "";
for (var key in data[6]) {
    auditors += data[6][key].auditor_name + ' (' + data[6][key].auditor_category + ')';
    auditorsabv += data[6][key]["auditor_abbrivation"];
    if (key !== Object.keys(data[6])[Object.keys(data[6]).length - 1]) {
        auditors += ", ";
        auditorsabv += ", ";
    }
}
//                    alert(auditors);
//                    alert(data[1]['intimation_date']);
//                    $('.scheduleAuditDate').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
                    $('.scheduleIIAuditorName').val(auditors);
                    $(".preparedByStageII").val(auditors);
                    $(".dateStageII").val(moment(data[1]['intimation_date']).format('DD.MM.YYYY'));
//                    $('.scheduleDate').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
//                    $('.scheduleIIAuditor').val(data[2]['auditor_name']);
                    $('.scheduleIIAuditor').val(auditorsabv);
                    
                    // Handle audit dates - populate from data[1]['audit_dates'] if available
                    var auditDateValue = '';
                    if (data[1] && data[1]['audit_dates'] && data[1]['audit_dates'].trim() !== '') {
                        auditDateValue = data[1]['audit_dates'];
                    }
                    
                    // Populate date field if we have a value
                    if (auditDateValue && auditDateValue.trim() !== '') {
                        var auditDate = auditDateValue.split(', ');
                        var auditDateLen = auditDate.length;
                        var max = (Math.max(auditDateLen));
                        for (var key = 0; key < max; key++) {
                            var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";
                            $('.scheduleIIDate').val(no);
                            $('.scheduleIIDates').val(no);
                        }
                        $(".scheduleIIDate").val(auditDateValue);
                    }
                    
                    $('input[type="text"]').trigger('change');
                    $('.scheduleAddress').trigger('change');

                    $('#scheduleStageII').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $(document).on('click', '.createStageiiAudit', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $icon = $btn.find('i');
            var origClass = $icon.attr('class') || 'fa fa-plus-circle fa-lg';
            $icon.attr('class', 'fa fa-spinner fa-spin fa-lg');
            $btn.css('pointer-events', 'none');

            function restoreCreatePlanBtn() {
                $icon.attr('class', origClass);
                $btn.css('pointer-events', '');
            }

            $('.scheduleii-customer-id').val($btn.attr('data-customer'));
            $('.scheduleii-service-id').val($btn.attr('data-service'));

            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customer_ID: $btn.attr('data-customer'),
                    service_ID: $btn.attr('data-service'),
                },
                success: function (data)
                {
                    restoreCreatePlanBtn();
                    //alert("hi");
//                    alert(data[3]['schedule_app_standard']);
//                    alert(data[6]['audit_standard']);
                    var auditors = "";
                    var auditorsabv = "";
for (var key in data[7]) {
    auditors += data[7][key].auditor_name + ' (' + data[7][key].auditor_category + ')';
    auditorsabv += data[7][key]["auditor_abbrivation"];
    if (key !== Object.keys(data[7])[Object.keys(data[7]).length - 1]) {
        auditors += ", ";
        auditorsabv += ", ";
    }
}
//                    alert(auditors);
//                    alert(data[5]);
                    $('.scheduleIIClientName').val(data[0]['customer_name']);
                    $('.scheduleIIAddress').val(data[0]['address']);
                    if (data[3]['schedule_app_standard']) {
                        $('.scheduleIIStandard').val(data[3]['schedule_app_standard']);
                    } else {
                        $('.scheduleIIStandard').val(data[6]['audit_standard']);
                    }
//                    $('.scheduleIIDate').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
                    $('.scheduleIICertScope').val(data[0]['scope']);
                    $('.scheduleIIAuditorName').val(auditors);
                    $('.scheduleIIMgmtRep').val(data[1]['audit_contact_person']);
                    // Set Prepared By field with full auditor list (names with categories)
                    $(".preparedByStageII").val(auditors);
//                    $(".dateStageII").val(data[10]["audit_dates"]);
                    
//                    Handle dateStageII field - check multiple sources
                    var dateStageIIValue = '';
                    if (data[10] && data[10]['audit_dates'] && data[10]['audit_dates'].trim() !== '') {
                        dateStageIIValue = data[10]['audit_dates'];
                    } else if (data[6] && data[6]['audit_dates'] && data[6]['audit_dates'].trim() !== '') {
                        dateStageIIValue = data[6]['audit_dates'];
                    } else if (data[1] && data[1]['audit_dates'] && data[1]['audit_dates'].trim() !== '') {
                        dateStageIIValue = data[1]['audit_dates'];
                    }
                    
                    if (dateStageIIValue && dateStageIIValue.trim() !== '') {
                        var auditDate = dateStageIIValue.split(', ');
                        $('.dateStageII').val(auditDate[0]);
                    }
                    
//                    $('.scheduleIIDates').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
//                    $('.scheduleIIAuditor').val(data[2]["auditor_name"]);
                    $('.scheduleIIAuditor').val(auditorsabv);
                    if (data[8])
                    {
                        $('.scheduleIIMandays').val(data[8]);
                    } else {
                        $('.scheduleIIMandays').val(data[5]);
                    }

//                    $('textarea').trigger('change');
                    // Handle audit dates - check multiple sources for RC
                    var auditDateValue = '';
                    
                    // Check data[1] (auditDetails from stage_twoaudit_id)
                    if (data[1] && data[1]['audit_dates']) {
                        var date1 = String(data[1]['audit_dates']).trim();
                        if (date1 !== '' && date1 !== 'undefined' && date1 !== 'null') {
                            auditDateValue = date1;
                        }
                    }
                    
                    // Check data[6] (stage1info from stage_oneaudit_id)
                    if (!auditDateValue && data[6] && data[6]['audit_dates']) {
                        var date6 = String(data[6]['audit_dates']).trim();
                        if (date6 !== '' && date6 !== 'undefined' && date6 !== 'null') {
                            auditDateValue = date6;
                        }
                    }
                    
                    // Check data[10] (noti1auditdate from stage_oneaudit_id)
                    if (!auditDateValue && data[10] && data[10]['audit_dates']) {
                        var date10 = String(data[10]['audit_dates']).trim();
                        if (date10 !== '' && date10 !== 'undefined' && date10 !== 'null') {
                            auditDateValue = date10;
                        }
                    }
                    
                    // Populate the date field if we found a value
                    if (auditDateValue && auditDateValue !== '') {
                        // Handle comma-separated dates
                        var auditDateArray = auditDateValue.split(', ');
                        if (auditDateArray.length > 0 && auditDateArray[0]) {
                            $('.scheduleIIDate').val(auditDateArray[0]);
                            $('.scheduleIIDates').val(auditDateArray[0]);
                        }
                        // Also set the full value
                        $('.scheduleIIDate').val(auditDateValue);
                    }

                    $('input[type="text"]').trigger('change');
                    $('#scheduleStageII').modal('show');
                },
                error: function (data, status, err)
                {
                    restoreCreatePlanBtn();
                    console.log("AJAX Error:", data);
                    console.log("Status:", status);
                    console.log("Error:", err);
                    
                    // Try to parse and show detailed error message
                    var errorMsg = 'Error loading Stage II audit plan data';
                    if (data.responseJSON && data.responseJSON.message) {
                        errorMsg += ':\n' + data.responseJSON.message;
                        if (data.responseJSON.line) {
                            errorMsg += '\n(Line: ' + data.responseJSON.line + ')';
                        }
                    } else if (data.responseText) {
                        errorMsg += ':\n' + data.responseText.substring(0, 200);
                    } else {
                        errorMsg += ':\n' + status;
                    }
                    
                    alert(errorMsg);
                }
            });
        });

        $(document).on('click', '.editStageiiAudit', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $icon = $btn.find('i');
            var origClass = $icon.attr('class') || 'fa fa-pencil-square fa-lg';
            $icon.attr('class', 'fa fa-spinner fa-spin fa-lg');
            $btn.css('pointer-events', 'none');

            function restoreEditPlanBtn() {
                $icon.attr('class', origClass);
                $btn.css('pointer-events', '');
            }

            $('.scheduleii-id').val($btn.attr('data-row-id'));
            $('.scheduleii-customer-id').val($btn.attr('data-customer'));
            $('.scheduleii-service-id').val($btn.attr('data-service'));
            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    schedule_Id: $btn.attr('data-row-id'),
                    service_Id: $btn.attr('data-service'),
                },
                success: function (data)
                {
                    restoreEditPlanBtn();
                    if (!data || !data[0]) {
                        alert("Could not load schedule details.");
                        return;
                    }
                    //alert(data[2][0].auditor_name);
                    $('.scheduleIIClientName').val(data[0]['clieint_name']);
                    $('.scheduleIIStandard').val(data[0]['scheduleii_standards']);
                    $('.scheduleIIAddress').val(data[0]['scheduleii_address']);
                    
                    // Handle Date(s) field - check multiple sources
                    var scheduleIIDateValue = '';
                    
                    // First try data[0]['scheduleii_date'] from audit_scheduleii_id table
                    if (data[0] && data[0]['scheduleii_date'] && data[0]['scheduleii_date'] != '' && data[0]['scheduleii_date'] != null) {
                        scheduleIIDateValue = data[0]['scheduleii_date'];
                    }
                    // Fallback to data[4]['audit_dates'] from stage_twoaudit_id table
                    else if (data[4] && data[4]['audit_dates'] && data[4]['audit_dates'] != '' && data[4]['audit_dates'] != null) {
                        scheduleIIDateValue = data[4]['audit_dates'];
                    }
                    
                    // Set the date field if we have a value
                    if (scheduleIIDateValue && scheduleIIDateValue != '') {
                        $('.scheduleIIDate').val(scheduleIIDateValue);
                    }
                    $('.scheduleIICertScope').val(data[0]['scheduleii_cert_scope']);
                    $('.scheduleIIMandays').val(data[0]['scheduleii_mandays']);
                    $('.scheduleIIMgmtRep').val(data[0]['scheduleii_mgmt']);
                    $('.scheduleIIAuditType').val(data[0]['audit_type']);
                    let leadAuditors = [];
                    let leadAuditors1 = [];
                    if (Array.isArray(data[3]) && data[3].length > 0) {for (const key in data[3]) {
        if (data[3].hasOwnProperty(key)) {

            if (data[3][key]["auditor_category"] == "Team Leader"){
            const combined = data[3][key].auditor_name + ' (' + data[3][key].auditor_category + ')';
        leadAuditors.push(combined);
        leadAuditors1.push(data[3][key].auditor_name);
                }else if(data[3][key]["auditor_category"] == "Auditor"){
                    const combined = data[3][key].auditor_name + ' (' + data[3][key].auditor_category + ')';
        leadAuditors.push(combined);
                }else if(data[3][key]["auditor_category"] == "Technical Expert"){
                    const combined = data[3][key].auditor_name + ' (' + data[3][key].auditor_category + ')';
        leadAuditors.push(combined);
                }
        else{
            const combined = data[3][key].auditor_name + ' (' + data[3][key].auditor_category + ')';
        leadAuditors.push(combined);
        }}

    }
} else {
    for (const key in data[2]) {
        if (data[2].hasOwnProperty(key)) {
            if (data[2][key]["auditor_category"] == "Team Leader"){
            const combined = data[2][key].auditor_name + ' (' + data[2][key].auditor_category + ')';
        leadAuditors.push(combined);
        leadAuditors1.push(data[2][key].auditor_name);
                }else if(data[2][key]["auditor_category"] == "Auditor"){
                    const combined = data[2][key].auditor_name + ' (' + data[2][key].auditor_category + ')';
        leadAuditors.push(combined);
                }else if(data[2][key]["auditor_category"] == "Technical Expert"){
                    const combined = data[2][key].auditor_name + ' (' + data[2][key].auditor_category + ')';
        leadAuditors.push(combined);
                }
        else{
            const combined = data[2][key].auditor_name + ' (' + data[2][key].auditor_category + ')';
        leadAuditors.push(combined);
        }}

    }
}

let leadAuditorString = leadAuditors.join(', ');
let leadAuditorString1 = leadAuditors1.join(', ');
$leadauditor = leadAuditorString;
$leadauditor1 = leadAuditorString1;
                    $('.scheduleIIAuditorName').val($leadauditor);

                    $('.stage2FormNo').val(data[0]['audit_ii_formno']);
                    $('.stage2Rev').val(data[0]['audit_ii_rev']);
                    $(".stageIIlunch").val(data[0]["break"]);
                    $(".stageIItimeFrom").val(data[0]["break_from"]);
                    $(".stageIItimeTo").val(data[0]["break_to"]);
                    
                    // Handle Prepared By field - check saved value first, then build from auditor arrays
                    var preparedByValue = '';
                    if (data[0] && data[0]['prepared_by'] && data[0]['prepared_by'] != '' && data[0]['prepared_by'] != null) {
                        // Use saved value from database
                        preparedByValue = data[0]['prepared_by'];
                    } else {
                        // Build from auditor arrays - include ALL auditors with categories
                        var allAuditorNames = [];
                        
                        // Check if we have auditors in data[3] (preferred) or data[2] (fallback)
                        var auditorSource = (Array.isArray(data[3]) && data[3].length > 0) ? data[3] : data[2];
                        
                        if (auditorSource) {
                            for (const key in auditorSource) {
                                if (auditorSource.hasOwnProperty(key)) {
                                    var auditorName = auditorSource[key].auditor_name;
                                    var auditorCategory = auditorSource[key].auditor_category;
                                    if (auditorName) {
                                        allAuditorNames.push(auditorName + ' (' + auditorCategory + ')');
                                    }
                                }
                            }
                        }
                        
                        preparedByValue = allAuditorNames.join(', ');
                    }
                    $(".preparedByStageII").val(preparedByValue);
                    $(".dateStageII").val(moment(data[0]["prepared_date"]).format('DD.MM.YYYY'));
                    if (data[0]['audit_ii_revdate'] == "" || data[0]['audit_ii_revdate'] == null)
                        $audit_ii_revdate = "";
                    else
                        $audit_ii_revdate = moment(data[0]['audit_ii_revdate']).format('DD.MM.YYYY');
                    $('.stage2RevDate').val($audit_ii_revdate);
                    $('.stage2Isue').val(data[0]['audit_ii_isue']);
                    if (data[0]['audit_ii_isuedate'] == "" || data[0]['audit_ii_isuedate'] == null)
                        $audit_ii_isuedate = "";
                    else
                        $audit_ii_isuedate = moment(data[0]['audit_ii_isuedate']).format('DD.MM.YYYY');
                    $('.stage2IsueDate').val($audit_ii_isuedate);

                    var allAuditors = ("<?php echo $allAuditors; ?>");
                    var auditors = allAuditors.split(',');
                    var option = "";
                    for (var key in auditors) {
                        if (auditors[key] != null && auditors[key] != "") {
                            option += '<option value="' + auditors[key] + '">' + auditors[key] + '</option>';
                        }

                    }

                    $('.formScheduleIIDivBtn').empty();

                    for (var key in data[1]) {
                        var option2 = '<option value="' + data[1][key]['audit_scheduleii_auditor'] + '"selected>' + data[1][key]['audit_scheduleii_auditor'] + '</option>';

                        if (data[1][key]['audit_scheduleii_dates'] == "" || data[1][key]['audit_scheduleii_dates'] == null)
                            $audit_scheduleii_dates = "";
                        else
                            $audit_scheduleii_dates = data[1][key]['audit_scheduleii_dates'];
                        $('.formScheduleIIDivBtn').append('<div class="row stage1schedulerow" style="">\n\
                                                <div class="col-sm-11 form-group float-label-control">\n\
                                                    <div class="row form-group float-label-control" style="">\n\
\n\                                                     <textarea rows="3" class="scheduleIIDepartment" id="scheduleIIDepartment" name="scheduleIIDepartment" required>' + data[1][key]['audit_scheduleii_department'] + '</textarea>\n\
                                                    </div>\n\
                                                    <div class="row">\n\
                                                        <div class="col-sm-3 form-group ">\n\
                                                            <label class="dispClass">Date:</label>\n\
                                                            <input type="text" class="scheduleIIDates" id="scheduleIIDates" name="scheduleIIDates" placeholder="Date:" value="' + $audit_scheduleii_dates + '">\n\
                                                        </div>\n\
                                                        <div class="col-sm-3 form-group ">\n\
                                                            <label class="dispClass">Time:</label>\n\
                                                            <input type="text" class="scheduleIITime" id="scheduleIITime" name="scheduleIITime" placeholder="Time:" value="' + data[1][key]['audit_scheduleii_time'] + '">\n\
                                                        </div>\n\
                                                        <div class="col-sm-3 form-group ">\n\
                                                            <label class="dispClass">Clause reference:</label>\n\
                                                            <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" name="scheduleIIClaudeReference" placeholder="Clause reference:" value="' + data[1][key]['clause_ref'] + '">\n\
                                                        </div>\n\
                                                        <div class="col-sm-3 form-group ">\n\
                                                            <label class="dispClass">Auditor:</label>\n\
                                                            <input type="text" class="scheduleIIAuditor" id="scheduleIIAuditor" name="scheduleIIAuditor" value="' + data[1][key]['audit_scheduleii_auditor'] + '" placeholder="Auditor:">\n\
                                                        </div>\n\
                                                    </div><hr>\n\
                                                </div>\n\
                                                <div class="col-sm-1 removestage1schedulecol">\n\
                                                    <a class="removestage1schedule"><i class="fa fa-times"></i></a>\n\
                                                    <a class="addSchedule"><i class="fa fa-plus"></i></a>\n\
                                                </div>\n\
                                            </div>');
                    }

                    $('input[type="text"]').trigger('change');
//                    $('textarea').trigger('change');
                    $('.addscheduleIIAuditPdf').hide();
                    $('#scheduleStageII').modal('show');
                },
                error: function (data, status, err)
                {
                    restoreEditPlanBtn();
                    alert("Error loading schedule: " + (status || err));
                }
            });
        });

        $(document).on('focus', ".scheduleIIDates", function () {
            if ($(this).hasClass('hasDatepicker') === false) {
                $(this).datepicker({
                    //            minDate: '-100Y',
                    //            maxDate: '-2Y',
                    //            changeYear: true,
                    //            changeMonth: true,
                    //            yearRange: "-100:+0",
                    minDate: '-100Y',
                    maxDate: '+100Y',
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "-20:+100",
                    dateFormat: 'dd.mm.yy',
                    autoclose: true
                });
            }
        });

        $('.formScheduleII').click(function () {
            $('.formScheduleIIDiv').slideToggle('slow');
        });

//        $('.formScheduleIIBtn').click(function () {
//            $('.formScheduleIIDivBtn').slideToggle('slow');
//        });
        $('.formScheduleIIDivBtn').on('click', '.addSchedule', function() {
    // Get the parent row of the clicked "+" button
    const parentRow = $(this).closest('.stage1schedulerow');
    // Clone the parent row
    const newRow = parentRow.clone();
    newRow.find('.scheduleIIDepartment').val('');
newRow.find('.scheduleIITime').val('');
newRow.find('.scheduleIIClaudeReference').val('');
newRow.find('.scheduleIIAuditor').val('');
    // Clear the input fields in the cloned row
    //newRow.find('input, textarea').val('');
    // Insert the cloned row after the parent row
    parentRow.after(newRow);
});
        $('.addscheduleIILink').click(function () {
            var allAuditors = ("<?php echo $allAuditors; ?>");
            var auditors = allAuditors.split(',');
            var option = "";
            for (var key in auditors) {
                if (auditors[key] != null && auditors[key] != "") {
                    option += '<option value="' + auditors[key] + '">' + auditors[key] + '</option>';
                }

            }
            var autitorsSelect = '<select data-row-id="" class="scheduleIIAuditor form-control" id="scheduleIIAuditor" name="scheduleIIAuditor">\n\
                                                                <option value="" disabled selected>Choose Auditors</option>\n\
                                                                    ' + option + '\n\
                                                            </select>';
            $('.formScheduleIIDivBtn').append('<div class="row " >\n\
                            <div class="col-sm-6 form-group float-label-control">\n\
                                <div class="row">\n\
                                    <div class="col-sm-6 form-group float-label-control">\n\
                                        <label>Date:</label>\n\
                                        <input type="text" class="scheduleIIDates" name="scheduleIIDates" placeholder="Date:">\n\
                                    </div>\n\
                                    <div class="col-sm-6 form-group float-label-control">\n\
                                        <label>Time:</label>\n\
                                        <input type="time" class="scheduleIITime"  name="scheduleIITime" placeholder="Time:">\n\
                                    </div>\n\
                                </div>\n\
                                <div class="row">\n\
                                    <div class="col-sm-6 form-group float-label-control">\n\
                                        <label>Clause reference:</label>\n\
                                        <input type="text" class="scheduleIIClaudeReference" id="scheduleIIClaudeReference" name="scheduleIIClaudeReference" placeholder="Clause reference:">\n\
                                    </div>\n\
                                    <div class="col-sm-6 form-group float-label-control">\n\
                                        <label>Auditor:</label>\n\
                                        ' + autitorsSelect + '\n\
                                    </div>\n\
                                </div>\n\
                            </div>\n\
                            <div class="col-sm-6 form-group float-label-control">\n\
                                <div class="row">\n\
                                    <div class="col-sm-12 form-group float-label-control">\n\
                                        <label>Department/ Function/ Process / Activity:</label>\n\
                                        <textarea name="scheduleIIDepartment" rows="4" class="form-control form-group scheduleIIDepartment" id="scheduleIIDepartment" placeholder="Department/ Function/ Process / Activity:"></textarea>\n\
                                    </div>\n\
                                </div>\n\
                            </div>\n\
                        </div>');
            $('input[type="text"]').trigger('change');
//            $('textarea').trigger('change');
        });

        $('.addscheduleIIAudit').click(function () {

            var scheduleIIDatesArray = [];
            $('.scheduleIIDates').each(function () {
                scheduleIIDatesArray.push($(this).val());
            });
            $('.scheduleIIDatesHidden').val(scheduleIIDatesArray.join("~"));

            var scheduleIITimeArray = [];
            $('.scheduleIITime').each(function () {
                scheduleIITimeArray.push($(this).val());
            });
            $('.scheduleIITimeHidden').val(scheduleIITimeArray.join("~"));

            var scheduleIIClaudeReferenceArray = [];
            $('.scheduleIIClaudeReference').each(function () {
                scheduleIIClaudeReferenceArray.push($(this).val());
            });
            $('.scheduleIIClaudeReferenceHidden').val(scheduleIIClaudeReferenceArray.join("~"));

            var scheduleIIAuditorArray = [];
            $('.scheduleIIAuditor').each(function () {
                scheduleIIAuditorArray.push($(this).val());
            });
            $('.scheduleIIAuditorHidden').val(scheduleIIAuditorArray.join("~"));

            var scheduleIIDepartmentArray = [];
            $('.scheduleIIDepartment').each(function () {
                scheduleIIDepartmentArray.push($(this).val());
            });
            $('.scheduleIIDepartmentHidden').val(scheduleIIDepartmentArray.join("~"));

        });

        $('.addscheduleIIAuditPdf').click(function () {
            var scheduleIIDatesArray = [];
            $('.scheduleIIDates').each(function () {
                scheduleIIDatesArray.push($(this).val());
            });
            $('.scheduleIIDatesHidden').val(scheduleIIDatesArray.join("~"));

            var scheduleIITimeArray = [];
            $('.scheduleIITime').each(function () {
                scheduleIITimeArray.push($(this).val());
            });
            $('.scheduleIITimeHidden').val(scheduleIITimeArray.join("~"));

            var scheduleIIClaudeReferenceArray = [];
            $('.scheduleIIClaudeReference').each(function () {
                scheduleIIClaudeReferenceArray.push($(this).val());
            });
            $('.scheduleIIClaudeReferenceHidden').val(scheduleIIClaudeReferenceArray.join("~"));

            var scheduleIIAuditorArray = [];
            $('.scheduleIIAuditor').each(function () {
                scheduleIIAuditorArray.push($(this).val());
            });
            $('.scheduleIIAuditorHidden').val(scheduleIIAuditorArray.join("~"));

            var scheduleIIDepartmentArray = [];
            $('.scheduleIIDepartment').each(function () {
                scheduleIIDepartmentArray.push($(this).val());
            });
            $('.scheduleIIDepartmentHidden').val(scheduleIIDepartmentArray.join("~"));
        });

    });
</script>

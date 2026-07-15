<?php


include_once './dbConnect.php';

$allAuditors = "";
$sql = "SELECT auditor_name FROM auditors";
$statement = $db->prepare($sql);
$statement->execute();
$auditors = $statement->fetchAll();

foreach ($auditors as $name) {
    $allAuditors .= $name['auditor_name'] . ",";
}

//  Standards 
$standards = "SELECT * FROM standards";
$statement = $db->prepare($standards);
$statement->execute();
$standardsList = $statement->fetchAll();

//  nace code6
$naceCodes = "SELECT * FROM nace_codes";
$statement = $db->prepare($naceCodes);
$statement->execute();
$naceCodeList = $statement->fetchAll();

//  IAF code
$iafCodes = "SELECT * FROM iaf_codes";
$statement = $db->prepare($iafCodes);
$statement->execute();
$iafList = $statement->fetchAll();
?>
<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">


<style>
    table, th, td {
        border: 1px solid gray;
        border-collapse: collapse;
    }
</style>

<div class="modal fade" id="stage1servelance" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                Audit Plan - 1st SURVEILLANCE
            </div>  
            <form action="addServelance1.php" method="post">
                <div class="modal-body" >
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formSchedule">
                            <span><i class="fa ">Audit Plan - 1st SURVEILLANCE</i></span>
                        </div>
                    </div>
                    <div class="formScheduleDiv">
                        <div class="row " style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Client Name:</label>
                                <input type="text" class="scheduleClientName" id="scheduleClientName" name="scheduleClientName" placeholder="Client Name:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Applicable Standard:</label>
                                <input type="text" readonly class="scheduleAppStandard" id="scheduleAppStandard" name="scheduleAppStandard" placeholder="Assessment Standard:" required>
                            </div>
                        </div> 
                        <div class="row " style="">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Site(s):</label>
                                <input type="text" class="sites" id="sites" name="sites" placeholder="Site(s):" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Mandays:</label>
                                <input type="text" class="scheduleMandays" id="scheduleMandays" name="scheduleMandays" placeholder="Mandays:" required>
                            </div>
                        </div>     
                        <div class="row">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Scope Of Certification:</label>
                                <input type="text" class="certificationScope" id="certificationScope" name="certificationScope" placeholder="Scope Of Certification:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Audit Type:</label>
                                <input type="text" class="scheduleIAuditType" id="scheduleIAuditType" name="scheduleIAuditType" value="SA - 1" placeholder="Audit Type:" required>
                            </div>
                        </div>
                        <div class="row " style="">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Audit Date:</label>
                                <input type="text" class="scheduleAuditDate" id="scheduleAuditDate" name="scheduleAuditDate" placeholder="Audit Date:" required>
                            </div>

                        </div>
                        <div class="row " style="">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Auditor/s:</label>                                
                                <input type="text" class="scheduleAuditor" id="scheduleAuditor" name="scheduleAuditor" placeholder="Auditor/s:" value="<?php //echo $auditorlist                                           ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn ">
                            <span><i class="fa  ">Audit Plan - 1st SURVEILLANCE</i></span>
                            <a class="stage1servelanceadd"  style="float: right" title="Add New"><i class="fa fa-plus-circle"></i></a>
                        </div>
                    </div>
                    <div class="formScheduleIServelanceDiv" >
                        <br>
                        <div class="row stage1schedulerow" style="">
                            <div class="col-sm-11 form-group float-label-control">
                                <div class="row form-group float-label-control" style="">
                                    <input type="text" class="servelanceActivity" name="servelanceActivity" value="Arrive at site">
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                                            
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="09:00 AM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <input type="text" class="servelanceActivity" name="servelanceActivity" value="Opening meeting with senior management, Preparation and planning for audit" >
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="09:15 AM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="5" class="servelanceActivity" name="servelanceActivity"> - QMS general Processes
Verification of actions taken for last audit findings (areas of concerns). Scope of the QMS, organisation processes, external provided processes, products and services, statutory and regulatory requirements, Understanding the organisation and its context (monitoring and review of information about external and internal issues that considering positive and negative factors); Understanding the needs and expectation of interested parties; Documented Information - Creating, Updating & Controlling, Planning of changes (client's methodology for changes in QMS).</textarea>
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="09:45 AM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="4.1; 4.2; 4.3;4.4,5.3,6.1; 6,2,6.37.1; 7.2; 7.3;7.4; 7.5,9.1,10.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="3" class="servelanceActivity" name="servelanceActivity" > - Top Management
Establishing and Communicating the quality policy and the quality objectives; Quality Objectives and planning, Monitoring measurement analysis and evaluation; Organization's roles, responsibilities & authorities, organizational chart, customer requirements, Customer Satisfaction, Customers complaints, nonconformity and corrective action, Internal audits, Management review; Continual Improvement
Actions to address Risks & Opportunities (with mitigation and/or contingency if any)</textarea>
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="11:45 AM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="5.1, 5.2, 5.3,6.1, 6.2,7.1,9.1, 9.2, 9.3,10.1, 10.2,10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="3" class="servelanceActivity" name="servelanceActivity" > - Marketing/ Contract review/
Operational Planning and control
Customer communication (Covering Product information, Enquiries, contract handling, amendments, Handling/controlling customer property, Requirements for contingency actions), Determination and review of req. related to product /services, Operational planning and control. Changes to req. related for product/services, Control of changes.</textarea>
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="12:45 PM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="8.2; 8.1, 8.5.6, 10.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="2" class="servelanceActivity" name="servelanceActivity" > - Design & Dev.
Planning; Inputs; Controls ( Review, Verification, Validation); Outputs; Changes</textarea>
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="NA" >
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="7.1; 8.3,10.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="3" class="servelanceActivity" name="servelanceActivity" > - Production/ Service Provision
Control of production & service provision, Environment for the operation of processes, Identification and traceability,
Property belonging to customer / external providers and its control (8.4), Release of products & services (In-process Inspection), Control of nonconforming outputs (In-process). </textarea>
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="14:00 PM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="7.1;8.5, 8.6, 8.7,10.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="2" class="servelanceActivity" name="servelanceActivity" > - Maintenance
Infrastructure (Including building, M/c, equipment and its maintenance. </textarea>
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="15:30 PM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="8.2; 7.1, 10.1; 10.2; 10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="3" class="servelanceActivity" name="servelanceActivity" > - Quality Control 
Release of products & services (Final Inspection) & Analysis & evaluation, Person authorizing to release. (Traceability if requirement); Control of nonconforming outputs (final Inspection) - Analysis & evaluation and Corrective Actions
Monitoring and measuring resources; Measurement traceability Suitability and evidence of its continuing fitness.</textarea>
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="16:00 PM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="7.1;8.6, 8.7, 9.110.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="3" class="servelanceActivity" name="servelanceActivity" > - Purchase 
External providers; Control on external providers & Analysis & evaluation (Elaborate all the processes or part of process which is provided by external provider and their evaluation, selection, monitoring of performance, re-evaluation, etc...)
Type & extent of control on process, products & services provided by external providers, i.e. controls like (Receiving Inspection) , Control of nonconforming outputs ( Receiving) if any), Information for external providers</textarea>
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="17:00 PM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="8.4, 8.710.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <input type="text" class="servelanceActivity" name="servelanceActivity" value="Auditor/s Time">
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="17:30 PM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <input type="text" class="servelanceActivity" name="servelanceActivity" value="Closing Meeting">
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="17:45 PM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                                    <input type="text" class="servelanceActivity" name="servelanceActivity" value="End of audit.">
                                </div>
                                <div class="row " style="">
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="18:00 PM" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">
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
                            <input type="text" class="stage1schedule" name="stage1schedule" value="Lunch Time" placeholder="Lunch Time" required>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>Time From:</label>
                            <input type="text" class="stage1timeFrom" name="stage1timeFrom" value="13:30 PM" placeholder="Time From:" required>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>Time To:</label>
                            <input type="text" class="stage1timeTo" name="stage1timeTo" value="14:00 PM" placeholder="Time To:" required>
                        </div>
                    </div>
                    <div class="row " style="">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Prepared By:</label>
                                <input type="text" class="preparedByScheduleI" id="preparedByScheduleI" name="preparedByScheduleI" value="" placeholder="Prepared By:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Date:</label>
                                <input type="text" class="dateStageScheduleI" id="dateStageScheduleI" name="dateStageScheduleI" value="" placeholder="Date:" required>
                            </div>
                        </div>
                </div>
                <input type="hidden" class="scheduleAuditDate" name="scheduleAuditDate" value="">
                <input type="hidden" class="schedule-service-id" name="schedule-service-id" value="">
                <input type="hidden" class="schedule-customer-id " name="schedule-customer-id" value="">
                <input type="hidden" class="servilence_id" name="servilence_id" value="">
                <input type="hidden" class="scheduleTimeHidden" name="scheduleTimeHidden" value="">
                <input type="hidden" class="scheduleClauseHidden" name="scheduleClauseHidden" value="">
                <input type="hidden" class="servelanceActivityHidden" name="servelanceActivityHidden" value="">
                <input type="hidden" class="scheduleAuditorHidden" name="scheduleAuditorHidden" value="">
                <input type="hidden" class="scheduleDateHidden" name="scheduleDateHidden" value="">
                <div class="modal-footer">
                    <button type="submit" name="addScheduleStage" class="btn btn-xs btn-info addScheduleStage">Save</button>  
                    <!--<button type="submit" name="addScheduleStagePdf" class="btn btn-xs btn-info addScheduleStagePdf">Save & Generate</button>-->  
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
    /*    .controlFormat1RiskDegree{
            display: block;
            width: 100%;
            padding: 0.1em 0em 1px 0em;
            border: none;
            border-radius: 0px;
            border-bottom: 1px solid black;
            outline: none;
            margin: 0px;
            background: none;    
        }*/
    #scheduleDateEdit{
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
    #scheduleTimeEdit{
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
    #scheduleAuditorEditCs{
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
        $('#scheduleAuditDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#scheduleAuditDate").keydown(function (e) {
//            e.preventDefault();
        });
        $('#scheduleAuditDateEdit').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#scheduleAuditDateEdit").keydown(function (e) {
//            e.preventDefault();
        });
        $('#stageiRevDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#stageiRevDate").keydown(function (e) {
//            e.preventDefault();
        });
        $('#stageiIsueDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#stageiIsueDate").keydown(function (e) {
//            e.preventDefault();
        });
        $('#editstageiRevDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#editstageiRevDate").keydown(function (e) {
//            e.preventDefault();
        });
        $('#editstageiIsueDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#editstageiIsueDate").keydown(function (e) {
//            e.preventDefault();
        });
    });

    $(document).ready(function () {

        $(".stage1servelanceadd").click(function () {
            $('.formScheduleIServelanceDiv').append('<div class="row stage1schedulerow" style="">\n\
                                                        <div class="col-sm-11 form-group float-label-control">\n\
                                                            <div class="row form-group float-label-control" style="">\n\
                                                                <input type="text" class="servelanceActivity" name="servelanceActivity" value="">\n\
                                                            </div>\n\
                                                            <div class="row " style=""> \n\
                                                            <div class="col-sm-3 form-group ">\n\
                                                                    <label class="dispClass">Date(s):</label>\n\
                                                                    <input type="text" class="scheduleDate" name="scheduleDate" id="scheduleDate" value="" placeholder="Date" >\n\
                                                                </div>\n\
                                                                <div class="col-sm-3 form-group ">\n\
                                                                    <label>Time:</label>\n\
                                                                    <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="" required/>\n\
                                                                </div>\n\
                                                                <div class="col-sm-3 form-group ">\n\
                                                                    <label>Clause Reference:</label>\n\
                                                                    <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="" placeholder="Clause ReferenceS">\n\
                                                                </div>\n\
                                                                <div class="col-sm-3 form-group ">\n\
                                                                    <label>Auditor:</label>\n\
                                                                    <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="" placeholder="Auditor">\n\
                                                                </div>\n\
                                                            </div><hr>\n\
                                                        </div>\n\
                                                        <div class="col-sm-1 removestage1schedulecol">\n\
                                                            <a class="removestage1schedule"><i class="fa fa-times"></i></a>\n\
                                                        </div>\n\
                                                    </div>');
        });

        $('.createStageServelance').click(function () {
            $('.schedule-customer-id').val($(this).attr('data-customer'));
            $('.schedule-service-id').val($(this).attr('data-service'));
            var serviceType = $(this).attr('data-serviceType');
//            alert($('.schedule-service-id').val());
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
                    if (data[4]['aif_code'] == undefined) {
                        var aifnace = data[0]['aif_nace'].split('/');
                        $('.scheduleIAFcode').val(aifnace[0]);
                        $(".scheduleNacecode").val(aifnace[1]);
                        $('.scheduleScope').val(data[0]['scope']);
                    } else {
                        $('.scheduleIAFcode').val(data[4]['aif_code']);
                        $(".scheduleNacecode").val(data[4]["nace_code"]);
                        $('.scheduleScope').val(data[4]['proposed_scope']);
                    }
//                  
                    if (serviceType != "" || serviceType != null) {
                        $('.scheduleAuditType').val(serviceType);
                    }
                    $('.scheduleClientName').val(data[0]['customer_name']);
                    $('.scheduleAddress').val(data[0]['address']);
                    $('.scheduleRefNo').val(data[0]['unique_id']);
                    $(".certificationScope").val(data[0]["scope"]);
                    $('.scheduleContactPerson').val(data[0]['contact_person']);
                    $(".sites").val(data[0]['address']);
//                    alert(data[1]['audit_standard']);
//                    alert(data[3]['certification']);
                    if (data[3]['certification']) {
//                        alert("in if");
                        $('.scheduleAppStandard').val(data[3]['certification']);
                    } else {
//                        alert("in else");
                        $('.scheduleAppStandard').val(data[1]['audit_standard']);
                    }
                    $('.scheduleMandays').val(data[5]);
                    var auditors = "";
                    var auditors1 = "";
                    var auditorsabv = "";
for (var key in data[6]) {
    auditors += data[6][key].auditor_name + ' (' + data[6][key].auditor_category + ')';
    if (data[6][key].auditor_category == "Team Leader"){
        auditors1 += data[6][key].auditor_name
    }
    auditorsabv += data[6][key]["auditor_abbrivation"];
    if (key !== Object.keys(data[6])[Object.keys(data[6]).length - 1]) {
        auditors += ", ";
        auditorsabv += ", ";
    }
}
//                    alert(auditors);
//                    $('.scheduleAuditDate').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
                    $('.scheduleAuditor').val(auditors);
                    $(".preparedByScheduleI").val(auditors1);
                    $(".dateStageScheduleI").val(moment(data[1]['intimation_date']).format('DD.MM.YYYY'));
//                    $('.scheduleDate').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
                    $('.scheduleAuditorSms').val(auditorsabv);
                    var auditDate = data[1]['audit_dates'].split(', ');
                    var auditDateLen = auditDate.length;
                    var max = (Math.max(auditDateLen));
//                    alert(personLen);
                    for (var key = 0; key < max; key++) {
                        var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";
                        $('.scheduleAuditDate').val(data[1]['audit_dates']);
                        $('.scheduleDate').val(no);
                    }
                    $('input[type="text"]').trigger('change');
                    $('.scheduleAddress').trigger('change');
                    $('#stage1servelance').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.editStage1Servelance').click(function () {
            $('.schedule-service-id').val($(this).attr('data-service'));
            $('.schedule-customer-id').val($(this).attr('data-customer'));
            $('.servilence_id').val($(this).attr('data-row-id'));
            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    servelanceId: $(this).attr('data-row-id'),
                    svcID: $(this).attr('data-service'),
                },
                success: function (data)
                {  
                    $(".scheduleClientName").val(data[0]["client_name"]);
                    $(".scheduleAppStandard").val(data[0]["ios_standards"]);
                    $(".sites").val(data[0]["sites"]);
                    $(".scheduleMandays").val(data[0]["mandays"]);
                    $(".certificationScope").val(data[0]["certification_scope"]);
                                        if (data[0]['schedule_audit_date'] == "" || data[0]['schedule_audit_date'] == null)
                        $schedule_audit_date = "";
                    else
                    alert(data[0]['schedule_audit_date']);
                        $schedule_audit_date = moment(data[3]['audit_dates']).format('DD.MM.YYYY');
                    $('.scheduleAuditDate').val(data[3][0].audit_dates);
                    $(".scheduleIAuditType").val(data[0]["audit_type"]);
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
        
                }else if(data[2][key]["auditor_category"] == "Technical Expert"){
                                        const combined = data[2][key].auditor_name + ' (' + data[2][key].auditor_category + ')';
        leadAuditors.push(combined);
        
                }
        else{
                                const combined = data[2][key].auditor_name + ' (' + data[2][key].auditor_category + ')';
        leadAuditors.push(combined);
        
        }}
    }
} else {
    console.error('auditnotifi1 is not a valid object or is empty.');
}

let leadAuditorString = leadAuditors.join(', ');
$leadauditor = leadAuditorString;
let leadAuditorString1 = leadAuditors1.join(', ');
$leadauditor1 = leadAuditorString1;
                    $(".scheduleAuditor").val($leadauditor);
                    $(".stage1schedule").val(data[0]["break"]);
                    $(".stage1timeFrom").val(data[0]["time_from"]);
                    $(".stage1timeTo").val(data[0]["time_to"]);
                    $(".preparedByScheduleI").val($leadauditor1);
                    $(".dateStageScheduleI").val(moment(data[0]["prepared_date"]).format('DD.MM.YYYY'));
                    $('.formScheduleIServelanceDiv').empty();
                    for (var key in data[1]) {
//                        alert(data[1][key]['auditor']);
                        var option2 = '<option value="' + data[1][key]['schedule_auditors'] + '"selected>' + data[1][key]['schedule_auditors'] + '</option>';
                                                if (data[1][key]['schedule_dates'] == "" || data[1][key]['schedule_dates'] == null) {
                            $schedule_dates = "";
                        } else {
//                            $schedule_dates = moment(data[1][key]['schedule_dates']).format('DD.MM.YYYY');
                            $schedule_dates = data[1][key]['schedule_dates'];
                        }
                        //changes by Appa
                        $('.formScheduleIServelanceDiv').append('<div class="row stage1schedulerow" style="">\n\
                                                                    <div class="col-sm-11 form-group float-label-control">\n\
                                                                        <div class="row form-group float-label-control" style="">\n\
                                                                            <textarea rows="3" class="servelanceActivity" name="servelanceActivity">' + data[1][key]['activity'] + '</textarea>\n\
                                                                        </div>\n\
                                                                        <div class="row " style=""> \n\
                                                                            <div class="col-sm-3 form-group ">\n\
                                                                                <label>Date(s):</label>\n\
                                                                                <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date(s)" value="' + $schedule_dates + '" required/>\n\
                                                                            </div>\n\
                                                                            <div class="col-sm-3 form-group ">\n\
                                                                                <label>Time:</label>\n\
                                                                                <input type="text" data-row-id="" class="scheduleTime" name="scheduleTime" placeholder="Time" value="' + data[1][key]['time_schedule'] + '" required/>\n\
                                                                            </div>\n\
                                                                            <div class="col-sm-3 form-group ">\n\
                                                                                <label>Clause Reference:</label>\n\
                                                                                <input type="text" data-row-id="" class="flexdatalist scheduleClause" id="scheduleClause" name="scheduleClause" value="' + data[1][key]['clause'] + '" placeholder="Clause ReferenceS">\n\
                                                                            </div>\n\
                                                                            <div class="col-sm-3 form-group ">\n\
                                                                                <label>Auditor:</label>\n\
                                                                                <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms" value="' + data[1][key]['auditor'] + '" placeholder="Auditor">\n\
                                                                            </div>\n\
                                                                        </div>\n\
                                                                        <hr>\n\
                                                                    </div>\n\
                                                                    <div class="col-sm-1 removestage1schedulecol">\n\
                                                                        <a class="removestage1schedule"><i class="fa fa-times"></i></a>\n\
                                                                        <a class="addSchedule"><i class="fa fa-plus"></i></a>\n\
                                                                    </div>\n\
                                                                </div>');
                    }

                    $('input[type="text"]').trigger('change');
                    // $('textarea').trigger('change');
                    $('#stage1servelance').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.formSchedule').click(function () {
            $('.formScheduleDiv').slideToggle('slow');
        });
//        $('.formScheduleII').click(function (){
//            $('.formScheduleIIDiv').slideToggle('slow');
//        });

        $('.addScheduleStage').click(function () {
            var scheduleDateArray = [];
            $('.scheduleDate').each(function () {
                scheduleDateArray.push($(this).val());
            });
            $('.scheduleDateHidden').val(scheduleDateArray.join("~"));

            var scheduleClauseArray = [];
            $('.scheduleClause').each(function () {
                scheduleClauseArray.push($(this).val());
            });
            $('.scheduleClauseHidden').val(scheduleClauseArray.join("~"));
            var scheduleTimeArray = [];
            $('.scheduleTime').each(function () {
                scheduleTimeArray.push($(this).val());
            });
            $('.scheduleTimeHidden').val(scheduleTimeArray.join("~"));
            var servelanceActivityArray = [];
            $('.servelanceActivity').each(function () {
                servelanceActivityArray.push($(this).val());
            });
            $('.servelanceActivityHidden').val(servelanceActivityArray.join("~"));
            var scheduleAuditorArray = [];
            $('.scheduleAuditorSms').each(function () {
                scheduleAuditorArray.push($(this).val());
            });
            $('.scheduleAuditorHidden').val(scheduleAuditorArray.join("~"));
//            alert($('.scheduleAuditorHidden').val());
        });

        $('.addScheduleStagePdf').click(function () {
            var scheduleDateArray = [];
            $('.scheduleDate').each(function () {
                scheduleDateArray.push($(this).val());
            });
            $('.scheduleDateHidden').val(scheduleDateArray.join("~"));
            var scheduleTimeArray = [];
            $('.scheduleTime').each(function () {
                scheduleTimeArray.push($(this).val());
            });
            $('.scheduleTimeHidden').val(scheduleTimeArray.join("~"));
            var servelanceActivityArray = [];
            $('.servelanceActivity').each(function () {
                servelanceActivityArray.push($(this).val());
            });
            $('.servelanceActivityHidden').val(servelanceActivityArray.join("~"));
            var scheduleAuditorArray = [];
            $('.scheduleAuditorSms').each(function () {
                scheduleAuditorArray.push($(this).val());
            });
            $('.scheduleAuditorHidden').val(scheduleAuditorArray.join("~"));
        });

        $('.addEditScheduleStage').click(function () {
            var scheduleDateArrayEdit = [];
            $('.scheduleDateEdit').each(function () {
                scheduleDateArrayEdit.push($(this).val());
            });
            $('.scheduleDateHidden-edit').val(scheduleDateArrayEdit.join("~"));
            var scheduleTimeArrayEdit = [];
            $('.scheduleTimeEdit').each(function () {
                scheduleTimeArrayEdit.push($(this).val());
            });
            $('.scheduleTimeHidden-edit').val(scheduleTimeArrayEdit.join("~"));
            var servelanceActivityArrayEdit = [];
            $('.servelanceActivity').each(function () {
                servelanceActivityArrayEdit.push($(this).val());
            });
            $('.servelanceActivityHidden-edit').val(servelanceActivityArrayEdit.join("~"));
            var scheduleAuditorArrayEdit = [];
            $('.scheduleAuditorEditCs').each(function () {
                scheduleAuditorArrayEdit.push($(this).val());
            });
            $('.scheduleAuditorHidden-edit').val(scheduleAuditorArrayEdit.join("~"));
        });

        $('.addScheduleLink').click(function () {
            $('.addNewSs').append(' <div class="row " style=""> \n\
                            <div class="col-sm-12 form-group float-label-control">\n\
                                <label>Activity:</label>\n\
                                  <textarea type="text" data-row-id="" class="servelanceActivity" id="servelanceActivity" name="servelanceActivity" value="" placeholder="Activity"></textarea>\n\
                                        </div>\n\
                                      </div>\n\
                                     <div class="row " style=""> \n\
                                  <div class="col-sm-4 form-group float-label-control">\n\
                                         <label>Date(s):</label>\n\
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" value="" placeholder="Date"/>\n\
                                    </div>\n\
                                <div class="col-sm-4 form-group float-label-control">\n\
                               <label>Time:</label>\n\
                                   <input type="time" data-row-id="" class="scheduleTime" name="scheduleTime" value="" placeholder="Time"/>\n\
                                       </div>\n\
                                      <div class="col-sm-4 form-group float-label-control">\n\
                                           <label>Auditor:</label>  \n\
                                            <input type="text" data-row-id="" class="scheduleAuditorSms" id="scheduleAuditorSms" name="scheduleAuditorSms"  placeholder="Auditor">                         \n\
                                         </div>\n\
                                          </div>');
            $('input[type="text"]').trigger('change');
            //$('textarea').trigger('change');
        });
                    $('.formScheduleIServelanceDiv').on('click', '.addSchedule', function() {
    // Get the parent row of the clicked "+" button
    const parentRow = $(this).closest('.stage1schedulerow');
    // Clone the parent row
    const newRow = parentRow.clone();
newRow.find('.servelanceActivity').val('');
newRow.find('.scheduleClause').val('');
newRow.find('.scheduleTime').val('');
newRow.find('.scheduleAuditorSms').val('');
    // Clear the input fields in the cloned row
    //newRow.find('input, textarea').val('');
    // Insert the cloned row after the parent row
    parentRow.after(newRow);
});

        $('.addEditScheduleLink').click(function () {
            $('.formScheduleIIDiv').append('<div class="row " style=""> \n\
                                                <div class="col-sm-12 form-group float-label-control">\n\
                                                    <label>Activity:</label>\n\
                                                    <textarea type="text" data-row-id="" class="servelanceActivity" id="servelanceActivity" name="servelanceActivity" value="" placeholder="Activity"></textarea>\n\
                                                </div>\n\
                                            </div>\n\
                                            <div class="row " style=""> \n\
                                                <div class="col-sm-4 form-group float-label-control">\n\
                                                    <label>Date(s):</label>\n\
                                                    <input type="text" data-row-id="" class="scheduleDateEdit" name="scheduleDateEdit" value="" placeholder="Date"/>\n\
                                                </div>\n\
                                                <div class="col-sm-4 form-group float-label-control">\n\
                                                    <label>Time:</label>\n\
                                                    <input type="time" data-row-id="" class="scheduleTimeEdit" name="scheduleTimeEdit" value="" placeholder="Time"/>\n\
                                                </div>\n\
                                                <div class="col-sm-4 form-group float-label-control">\n\
                                                    <label>Auditor:</label>  \n\
                                                    <input type="text" data-row-id="" class="scheduleAuditorEditCs" id="scheduleAuditorEditCs" name="scheduleAuditorEditCs" value="" placeholder="Auditor">                         \n\
                                                </div>\n\
                                            </div>');
            $('input[type="text"]').trigger('change');
//            $('textarea').trigger('change');
        });

        $(document).on('focus', ".scheduleDate", function () {
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

        $(document).on('focus', ".scheduleDateEdit", function () {
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

    });

</script>

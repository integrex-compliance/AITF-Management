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

<div class="modal fade" id="stage2servelance" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                Audit Plan - 2nd SURVEILLANCE
            </div>  
            <form action="addServelance2.php" method="post">
                <div class="modal-body" >
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formSchedule">
                            <span><i class="fa ">Audit Plan - 2nd SURVEILLANCE</i></span>
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
                                <!--<input type="text" class="scheduleAppStandard" id="scheduleAppStandard" name="scheduleAppStandard" placeholder="Applicable Standard:" required>-->
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
                                <input type="text" class="scheduleIIAuditType" id="scheduleIIAuditType" name="scheduleIIAuditType" value="2nd SURVEILLANCE" placeholder="Audit Type:" required>
                            </div>
                            
                        </div>
                                                <div class="row " style="">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Audit Date:</label>
                                <input readonly type="text" class="scheduleAuditDate" id="scheduleAuditDate" name="scheduleAuditDate" placeholder="Audit Date:" required>
                            </div>

                        </div>
                        <div class="row" style="">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Auditor/s:</label>                                
                                <input type="text" class="scheduleAuditor" id="scheduleAuditor" name="scheduleAuditor" placeholder="Auditor/s:" value="<?php //echo $auditorlist                                          ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn">
                            <span><i class="fa  ">Audit Plan - 2nd SURVEILLANCE</i></span>
                            <a class="stage2servelanceadd"  style="float: right" title="Add New"><i class="fa fa-plus-circle"></i></a>
                        </div>
                    </div>
                    <div class="servelanceII" >
                        <br>
                        <div class="row stage1schedulerow" style="">
                            <div class="col-sm-11 form-group float-label-control">
                                <div class="row form-group float-label-control" style="">
                                    <input type="text" class="servelance2Activity" name="servelance2Activity" value="Arrive at site" >
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>
                                <div class="row " style="">                                                            
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="09:00" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <input type="text" class="servelance2Activity" name="servelance2Activity" value="Opening meeting with senior management, Preparation and planning for audit" >
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="09:15" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="4" class="servelance2Activity" name="servelance2Activity"> -QMS general Processes
Verification of actions taken for last audit findings (areas of concerns).
Scope of the QMS, organisation processes, external provided processes, products and services, statutory and regulatory requirements, 
Understanding the organisation and its context (monitoring and review of information about external and internal issues that considering positive and negative factors); 
Understanding the needs and expectation of interested parties; 
Documented Information, Creating, Updating & Controlling
Planning of changes (client's methodology for changes in QMS).</textarea>
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="09:45" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="4.1; 4.2; 4.3;4.4,5.3,6.1; 6,2,6.37.1; 7.2; 7.3;7.4; 7.5,9.1,10.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="4" class="servelance2Activity" name="servelance2Activity"> -Top Management
Establishing and Communicating the quality policy and the quality objectives. 
Quality Objectives and planning, Monitoring measurement analysis and evaluation
Organization's roles, responsibilities & authorities, organizational chart,
customer requirements, Customer Satisfaction, Customers complaints, nonconformity and corrective action, Internal audits, Management review; Continual Improvement
Actions to address Risks & Opportunities (with mitigation and/or contingency if any)</textarea>
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="11:45" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="5.1, 5.2, 5.3,6.1, 6.2,7.1,9.1, 9.2, 9.3,10.1, 10.2,10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="4" class="servelance2Activity" name="servelance2Activity"> -Marketing/ Contract review/
Operational Planning and control
Customer communication  (Covering Product information, Enquiries, contract handling, amendments, Handling/controlling customer property, Requirements for contingency actions)
Determination and review of req. related to product /services, Operational planning and control. Changes to req. related for product/services, Control of changes.</textarea>
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="12:45" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="8.2; 8.1, 8.5.6, 10.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="2" class="servelance2Activity" name="servelance2Activity"> -Design & Dev.
Planning; Inputs; Controls ( Review, Verification, Validation); Outputs; Changes</textarea>
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="NA" >
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="7.1; 8.3,10.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="3" class="servelance2Activity" name="servelance2Activity" > -Human Resources
People; Competence; Awareness; Communication; 
Organizational Knowledge</textarea> 
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="14:00" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="7.1, 7.2, 7.3,7.4, 7.5,10.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="3" class="servelance2Activity" name="servelance2Activity"> - Production/ Service Provision
Control of production & service provision, Environment for the operation of processes, Identification and traceability,
Property belonging to customer / external providers and its control (8.4), Release of products & services (In-process Inspection), Control of nonconforming outputs (In-process). </textarea>
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="14:30" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="7.1;8.5, 8.6, 8.7,10.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="4" class="servelance2Activity" name="servelance2Activity"> - Quality Control 
Release of products and service
Release of products & services (Final Inspection) & Analysis & evaluation, Person authorizing to release. (Traceability if requirement) 
Control of nonconforming outputs (final Inspection) - Analysis & evaluation and Corrective Actions
Monitoring and measuring resources; Measurement traceability Suitability and evidence of its continuing fitness.</textarea>
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="16:00" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="7.1;8.6, 8.7, 9.1,10.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <textarea rows="3" class="servelance2Activity" name="servelance2Activity" > -Storage and Sale 
Preservation (Identification, handling, contamination control, packaging, storage, transmission or transportation & protection);
Post Delivery activities (Actions under warranty provision, contractual  obligations such as maintenance & supplementary services)</textarea>
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="17:00" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="8.2; 7.1,10.1; 10.2;10.3" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <input type="text" class="servelance2Activity" name="servelance2Activity" value="Auditor Time">
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="17:30" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <input type="text" class="servelance2Activity" name="servelance2Activity" value="Closing Meeting ">
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="17:45" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                                    <input type="text" class="servelance2Activity" name="servelance2Activity" value="End of audit.">
                                </div>
                                <div class="row " style="">
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>
                                    </div>                                
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="18:00" required/>
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Clause Reference:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="" placeholder="Clause ReferenceS">
                                    </div>
                                    <div class="col-sm-3 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">
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
                            <input type="text" class="stage2schedule" name="stage2schedule" value="Lunch Time" placeholder="Lunch Time" required>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>Time From:</label>
                            <input type="text" class="stage2timeFrom" name="stage2timeFrom" value="13:30 PM" placeholder="Time From:" required>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>Time To:</label>
                            <input type="text" class="stage2timeTo" name="stage2timeTo" value="14:00 PM" placeholder="Time To:" required>
                        </div>
                    </div>
                    <div class="row " style="">                                
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Prepared By:</label>
                            <input type="text" class="preparedByScheduleII" id="preparedByScheduleII" name="preparedByScheduleII" value="" placeholder="Prepared By:" required>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Date:</label>
                            <input type="text" class="dateStageScheduleII" id="dateStageScheduleII" name="dateStageScheduleII" value="" placeholder="Date:" required>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="scheduleIIDate" name="scheduleIIDate" value="">
                <input type="hidden" class="servelance-service-id" name="servelance-service-id" value="">
                <input type="hidden" class="servelance-customer-id" name="servelance-customer-id" value="">
                <input type="hidden" class="servilence2_id" name="servilence2_id" value="">
                <input type="hidden" class="schedule2DateHidden" name="schedule2DateHidden" value="">
                <input type="hidden" class="schedule2TimeHidden" name="schedule2TimeHidden" value="">
                <input type="hidden" class="schedule2ClauseHidden" name="schedule2ClauseHidden" value="">
                <input type="hidden" class="servelance2ActivityHidden" name="servelance2ActivityHidden" value="">
                <input type="hidden" class="servelance2AuditorHidden" name="servelance2AuditorHidden" value="">
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
    #schedule2DateEdit{
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
    #schedule2TimeEdit{
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

    $(document).ready(function () {

    $(".stage2servelanceadd").click(function (){
    $('.servelanceII').append(' <div class="row stage1schedulerow" style="">\n\
                                            <div class="col-sm-11 form-group float-label-control">\n\
                                                <div class="row form-group float-label-control" style="">\n\
                                                    <textarea class="servelance2Activity" name="servelance2Activity" ></textarea>\n\
                                                </div>\n\
                                                <div class="row " style="">\n\
                                                <div class="col-sm-3 form-group float-label-control">\n\
                                        <label>Date(s):</label>\n\
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date" required/>\n\
                                    </div>\n\
                                                    <div class="col-sm-3 form-group float-label-control">\n\
                                                        <label>Time:</label>\n\
                                                        <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="" required/>\n\
                                                    </div>\n\
                                                    <div class="col-sm-3 form-group float-label-control">\n\
                                                        <label>Clause Reference:</label>\n\
                                                        <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="" placeholder="Clause ReferenceS">\n\
                                                    </div>\n\
                                                    <div class="col-sm-3 form-group float-label-control">\n\
                                                        <label>Auditor:</label>\n\
                                                        <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="" placeholder="Auditor">\n\
                                                    </div>\n\
                                                </div><hr>\n\
                                            </div>\n\
                                            <div class="col-sm-1 removestage1schedulecol">\n\
                                                <a class="removestage1schedule"><i class="fa fa-times"></i></a>\n\
                                            </div>\n\
                                        </div>');
    });
    $('.servelancestage2').click(function () {
//            alert($(this).attr('data-service'));
    $('.servelance-customer-id').val($(this).attr('data-customer'));
    $('.servelance-service-id').val($(this).attr('data-service'));
//            alert($('.servelance-service-id').val());
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
                            $('.scheduleAuditDate').val(data[10]['audit_dates']);

//                    alert(data[3]['schedule_app_standard']);
//                    alert(data[6]['audit_standard']);
            var auditors = "";
                    var auditors1 = "";
                    var auditorsabv = "";
for (var key in data[7]) {
    auditors += data[7][key].auditor_name + ' (' + data[7][key].auditor_category + ')';
    if (data[7][key].auditor_category == "Team Leader"){
        auditors1 += data[7][key].auditor_name
    }
    auditorsabv += data[7][key]["auditor_abbrivation"];
    if (key !== Object.keys(data[7])[Object.keys(data[7]).length - 1]) {
        auditors += ", ";
        auditorsabv += ", ";
    }
}
//                    alert(auditors);
//                    alert(data[5]);
            $('.scheduleClientName').val(data[0]['customer_name']);
            $('.sites').val(data[0]['address']);
            if (data[3]['schedule_app_standard']) {
            $('.scheduleAppStandard').val(data[3]['schedule_app_standard']);
            } else {
            $('.scheduleAppStandard').val(data[6]['audit_standard']);
            }
//                    $('.scheduleIIDate').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
            $('.certificationScope').val(data[0]['scope']);
            $('.scheduleAuditor').val(auditors);
            $(".preparedByScheduleII").val(auditors1);
            $(".dateStageScheduleII").val(moment(data[11]['intimation_date']).format('DD.MM.YYYY'));
            $('.scheduleIIMgmtRep').val(data[1]['audit_contact_person']);
//                    $('.scheduleIIDates').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
            $('.schedule2AuditorSms').val(auditorsabv);
            if (data[5])
            {
            $('.scheduleMandays').val(data[5]);
            } else {
            $('.scheduleMandays').val(data[8]);
            }

//                    $('textarea').trigger('change');
            if (data[1]['audit_dates'] == undefined) {
            var auditDate = data[6]['audit_dates'].split(', ');
            var auditDateLen = auditDate.length;
            var max = (Math.max(auditDateLen));
//                    alert(personLen);
            for (var key = 0; key < max; key++) {
            var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";
            $('.schedule2Date').val(no);
            $('.schedule2Dates').val(no);
            }
//                        alert("if");
            //$('.schedule2Date').val(data[6]['audit_dates']);
            } else {
            var auditDate = data[1]['audit_dates'].split(', ');
            var auditDateLen = auditDate.length;
            var max = (Math.max(auditDateLen));
//                    alert(personLen);
            for (var key = 0; key < max; key++) {
            var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";
            $('.schedule2Date').val(no);
            $('.schedule2Dates').val(no);
            }
//                        alert("else");
            //$('.schedule2Date').val(data[1]['audit_dates']);
            }
//                    $('#stage2servelance').modal('show');
            $('input[type="text"]').trigger('change');
            $('#stage2servelance').modal('show');
            },
            error: function (data, status, err)
            {
            alert(status);
            }
    });
    });
    $('.editStage2Servelance').click(function () {
    $('.servelance-customer-id').val($(this).attr('data-customer'));
    $('.servelance-service-id').val($(this).attr('data-service'));
    $('.servilence2_id').val($(this).attr('data-row-id'));
//            alert($('.servilence2_id').val());
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
//                    alert(data);  
            $(".scheduleClientName").val(data[0]["client_name"]);
            $(".scheduleAppStandard").val(data[0]["ios_standards"]);
            $(".sites").val(data[0]["sites"]);
            $(".scheduleMandays").val(data[0]["mandays"]);
            $(".certificationScope").val(data[0]["certification_scope"]);
            $(".scheduleIIAuditType").val(data[0]["audit_type"]);
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
            $(".stage2schedule").val(data[0]["break"]);
            $(".stage2timeFrom").val(data[0]["time_from"]);
            $(".stage2timeTo").val(data[0]["time_to"]);
            $(".preparedByScheduleII").val($leadauditor1);
            $('.scheduleAuditDate').val(data[3][0].audit_dates);
            $(".dateStageScheduleII").val(moment(data[0]["prepared_date"]).format('DD.MM.YYYY'));
            $('.servelanceII').empty();
            for (var key in data[1]) {
            var option2 = '<option value="' + data[1][key]['schedule_auditors'] + '"selected>' + data[1][key]['schedule_auditors'] + '</option>';
            if (data[1][key]['schedule_dates'] == "" || data[1][key]['schedule_dates'] == null) {
                            $schedule_dates = "";
                        } else {
//                            $schedule_dates = moment(data[1][key]['schedule_dates']).format('DD.MM.YYYY');
                            $schedule_dates = data[1][key]['schedule_dates'];
                        }
            //changes by Appa
            $('.servelanceII').append('<div class="row stage1schedulerow" style="">\n\
                                                        <div class="col-sm-11 form-group float-label-control">\n\
                                                            <div class="row form-group float-label-control" style="">\n\
                                                                <textarea class="servelance2Activity" name="servelance2Activity" >' + data[1][key]['activity'] + '</textarea>\n\
                                                            </div>\n\
                                                            <div class="row " style="">\n\
                                                                            <div class="col-sm-3 form-group ">\n\
                                                                                <label>Date(s):</label>\n\
                                                                                <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" placeholder="Date(s)" value="' + $schedule_dates + '" required/>\n\
                                                                            </div>\n\
                                                                <div class="col-sm-3 form-group float-label-control">\n\
                                                                    <label>Time:</label>\n\
                                                                    <input type="text" data-row-id="" class="schedule2Time" name="schedule2Time" placeholder="Time" value="' + data[1][key]['time_schedule'] + '" required/>\n\
                                                                </div>\n\
                                                                <div class="col-sm-3 form-group float-label-control">\n\
                                                                    <label>Clause Reference:</label>\n\
                                                                    <input type="text" data-row-id="" class="flexdatalist schedule2Clause" id="schedule2Clause" name="schedule2Clause" value="' + data[1][key]['clause'] + '" placeholder="Clause ReferenceS">\n\
                                                                </div>\n\
                                                                <div class="col-sm-3 form-group float-label-control">\n\
                                                                    <label>Auditor:</label>\n\
                                                                    <input type="text" data-row-id="" class="flexdatalist schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms" value="' + data[1][key]['auditor'] + '" placeholder="Auditor">\n\
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

            $('#stage2servelance').modal('show');
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
    var schedule2DateArray = [];
    $('.schedule2Date').each(function () {
    schedule2DateArray.push($(this).val());
    });
    $('.schedule2DateHidden').val(schedule2DateArray.join("~"));
    
    var schedule2ClauseArray = [];
    $('.schedule2Clause').each(function () {
    schedule2ClauseArray.push($(this).val());
    });
    $('.schedule2ClauseHidden').val(schedule2ClauseArray.join("~"));
    var schedule2TimeArray = [];
    $('.schedule2Time').each(function () {
    schedule2TimeArray.push($(this).val());
    });
    $('.schedule2TimeHidden').val(schedule2TimeArray.join("~"));
    var servelance2ActivityArray = [];
    $('.servelance2Activity').each(function () {
    servelance2ActivityArray.push($(this).val());
    });
    $('.servelance2ActivityHidden').val(servelance2ActivityArray.join("~"));
    var scheduleAuditorArray = [];
    $('.schedule2AuditorSms').each(function () {
    scheduleAuditorArray.push($(this).val());
    });
    $('.servelance2AuditorHidden').val(scheduleAuditorArray.join("~"));
    });
    $('.addScheduleStagePdf').click(function () {
    var schedule2DateArray = [];
    $('.schedule2Date').each(function () {
    schedule2DateArray.push($(this).val());
    });
    $('.schedule2DateHidden').val(schedule2DateArray.join("~"));
    var schedule2TimeArray = [];
    $('.schedule2Time').each(function () {
    schedule2TimeArray.push($(this).val());
    });
    $('.schedule2TimeHidden').val(schedule2TimeArray.join("~"));
    var servelance2ActivityArray = [];
    $('.servelance2Activity').each(function () {
    servelance2ActivityArray.push($(this).val());
    });
    $('.servelance2ActivityHidden').val(servelance2ActivityArray.join("~"));
    var scheduleAuditorArray = [];
    $('.schedule2AuditorSms').each(function () {
    scheduleAuditorArray.push($(this).val());
    });
    $('.servelance2AuditorHidden').val(scheduleAuditorArray.join("~"));
    });
    $('.addEditScheduleStage').click(function () {

    var schedule2DateArrayEdit = [];
    $('.schedule2DateEdit').each(function () {
    schedule2DateArrayEdit.push($(this).val());
    });
    $('.schedule2DateHidden-edit').val(schedule2DateArrayEdit.join("~"));
    var schedule2TimeArrayEdit = [];
    $('.schedule2TimeEdit').each(function () {
    schedule2TimeArrayEdit.push($(this).val());
    });
    $('.schedule2TimeHidden-edit').val(schedule2TimeArrayEdit.join("~"));
    var servelance2ActivityArrayEdit = [];
    $('.servelance2Activity').each(function () {
    servelance2ActivityArrayEdit.push($(this).val());
    });
    $('.servelance2ActivityHidden-edit').val(servelance2ActivityArrayEdit.join("~"));
    var scheduleAuditorArrayEdit = [];
    $('.scheduleAuditorEditCs').each(function () {
    scheduleAuditorArrayEdit.push($(this).val());
    });
    $('.servelance2AuditorHidden-edit').val(scheduleAuditorArrayEdit.join("~"));
    });
    $('.addScheduleLink').click(function () {

    $('.addNewSs').append(' <div class="row " style=""> \n\
                            <div class="col-sm-12 form-group float-label-control">\n\
                                <label>Activity:</label>\n\
                                  <textarea type="text" data-row-id="" class="servelance2Activity" id="servelance2Activity" name="servelance2Activity" value="" placeholder="Activity"></textarea>\n\
                                        </div>\n\
                                      </div>\n\
                                     <div class="row " style=""> \n\
                                  <div class="col-sm-3 form-group float-label-control">\n\
                                         <label>Date(s):</label>\n\
                                        <input type="text" data-row-id="" class="schedule2Date" name="schedule2Date" value="" placeholder="Date"/>\n\
                                    </div>\n\
                                <div class="col-sm-3 form-group float-label-control">\n\
                               <label>Time:</label>\n\
                                   <input type="time" data-row-id="" class="schedule2Time" name="schedule2Time" value="" placeholder="Time"/>\n\
                                       </div>\n\
                                      <div class="col-sm-3 form-group float-label-control">\n\
                                           <label>Auditor:</label>  \n\
                                            <input type="text" data-row-id="" class="schedule2AuditorSms" id="schedule2AuditorSms" name="schedule2AuditorSms"  placeholder="Auditor">                         \n\
                                         </div>\n\
                                          </div>\n\
');
    $('input[type="text"]').trigger('change');
    //$('textarea').trigger('change');
    });
    $('.addEditScheduleLink').click(function () {

    $('.formScheduleIIDiv').append(' <div class="row " style=""> \n\
                            <div class="col-sm-12 form-group float-label-control">\n\
                                <label>Activity:</label>\n\
                                  <textarea type="text" data-row-id="" class="servelance2Activity" id="servelance2Activity" name="servelance2Activity" value="" placeholder="Activity"></textarea>\n\
                                        </div>\n\
                                      </div>\n\
                                     <div class="row " style=""> \n\
                                  <div class="col-sm-4 form-group float-label-control">\n\
                                         <label>Date(s):</label>\n\
                                        <input type="text" data-row-id="" class="schedule2DateEdit" name="schedule2DateEdit" value="" placeholder="Date"/>\n\
                                    </div>\n\
                                <div class="col-sm-4 form-group float-label-control">\n\
                               <label>Time:</label>\n\
                                   <input type="time" data-row-id="" class="schedule2TimeEdit" name="schedule2TimeEdit" value="" placeholder="Time"/>\n\
                                       </div>\n\
                                      <div class="col-sm-4 form-group float-label-control">\n\
                                           <label>Auditor:</label>  \n\
                                            <input type="text" data-row-id="" class="scheduleAuditorEditCs" id="scheduleAuditorEditCs" name="scheduleAuditorEditCs" value="" placeholder="Auditor">                         \n\
                                         </div>\n\
                                          </div>\n\
');
    $('input[type="text"]').trigger('change');
//            $('textarea').trigger('change');
    });
            $('.servelanceII').on('click', '.addSchedule', function() {
    // Get the parent row of the clicked "+" button
    const parentRow = $(this).closest('.stage1schedulerow');
    // Clone the parent row
    const newRow = parentRow.clone();
newRow.find('.servelance2Activity').val('');
newRow.find('.schedule2Time').val('');
newRow.find('.schedule2Clause').val('');
newRow.find('.schedule2AuditorSms').val('');
    // Clear the input fields in the cloned row
    //newRow.find('input, textarea').val('');
    // Insert the cloned row after the parent row
    parentRow.after(newRow);
});
    $(document).on('focus', ".schedule2Date", function () {
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
    $(document).on('focus', ".schedule2DateEdit", function () {
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

</script>

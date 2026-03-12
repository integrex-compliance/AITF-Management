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

<div class="modal fade" id="scheduleStageI" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                Audit Plan - Stage - 01
            </div>  
            <form action="addScheduleStage.php" method="post">
                <div class="modal-body" >
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formSchedule">
                            <span><i class="fa  ">Audit Plan - STAGE 1</i></span>
                        </div>
                    </div>
                    <div class="formScheduleDiv">
                        <div class="row " style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Client Name:</label>
                                <input type="text" class="scheduleClientName" id="scheduleClientName" name="scheduleClientName" placeholder="Client Name:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Agreement No.: </label>
                                <input type="text"  class="scheduleRefNo" id="scheduleRefNo" name="scheduleRefNo" placeholder="Agreement No.:" readonly required>
                            </div>
                        </div> 
                        <div class="row " style="">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Site(s):</label>
                                <textarea rows="4"  class="scheduleAddress" id="scheduleAddress" name="scheduleAddress" placeholder="Site(s)" required></textarea>
                            </div>
                            <div class="col-sm-6 form-group ">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Contact Person:</label>
                                        <input type="text" class="scheduleContactPerson" id="scheduleContactPerson" name="scheduleContactPerson" placeholder="Contact Person:" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Scope:</label>
                                        <input type="text" class="scheduleScope" id="scheduleScope" name="scheduleScope" placeholder="Scope:" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row " style="">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Assessment Standard/s:</label>
                                <!--<input type="text" class="scheduleAppStandard" id="scheduleAppStandard" name="scheduleAppStandard" placeholder="Applicable Standard:" required>-->
                                <textarea name="scheduleAppStandard" readonly class="form-control form-group scheduleAppStandard" id="scheduleAppStandard" placeholder="Assessment Standard/s" required></textarea>
                            </div>
                            <div class="col-sm-3 form-group float-label-control">
                                <label>Division:</label>
                                <input type="text" readonly class="scheduleIAFcode" id="scheduleIAFcode" name="scheduleIAFcode" placeholder="Division:" required>
<!--                                <select name="scheduleIAFcode" class="form-control scheduleIAFcode" id="scheduleIAFcode" required>
                                    <option value="">Select IAF Code</option>
                                <?php foreach ($iafList as $iaf) { ?>
                                                                                                                                                            <option value="<?php echo $iaf["iaf_code"] ?>"><?php echo $iaf["iaf_code"] ?></option>
                                <?php } ?>                                    
                                </select>-->
                            </div>
                            <div class="col-sm-3 form-group float-label-control">
                                <label>Group:</label>
                                <input type="text" readonly class="scheduleNacecode" id="scheduleNacecode" name="scheduleNacecode" placeholder="Group:" required>
<!--                                <select name="scheduleNacecode" class="form-control scheduleNacecode" id="scheduleNacecode" required>
                                    <option value="">Select NACE Code</option>
                                <?php foreach ($naceCodeList as $naceCode) { ?>
                                                                                                                                                            <option value="<?php echo $naceCode["nace_code"] ?>"><?php echo $naceCode["nace_code"] ?></option>
                                <?php } ?>                                    
                                </select>-->
                            </div><div class="col-sm-3 form-group float-label-control">
                                <label>Class/es:</label>
                                <input type="text" readonly class="scheduleNaceclass" id="scheduleNaceclass" name="scheduleNaceclass" placeholder="Class/es:" required>
                            </div>
                        </div>
                        <div class="row " style="">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Audit Type:</label>
                                <input type="text" class="scheduleAuditType" id="scheduleAuditType" name="scheduleAuditType" placeholder="Audit Type:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Mandays:</label>
                                <input type="text" class="scheduleMandays" id="scheduleMandays" name="scheduleMandays" placeholder="Mandays:" required>
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
                                <span>Member/s Presence during audit:</span>                              
                                <input type="text" class="scheduleAuditor" id="scheduleAuditor" name="scheduleAuditor" placeholder="Auditor/s:" value="<?php //echo $auditorlist                              ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn ">
                            <span><i class="fa  ">Audit Plan - STAGE 1</i></span>  
                            <!--                        </div>
                                                    <div class="col-sm-1 form-group float-label-control">-->
                            <a class="newStage1Schedule"  style="float: right" title="Add Multiple Nace Code"><i class="fa fa-plus-circle"></i></a>
                        </div>
                    </div>
                    <div class="formScheduleIIDiv" >
                        <div class="row stage1schedulerow" style="">                                
                            <div class="col-sm-11 form-group float-label-control">
                                <div class="row form-group float-label-control">
                                    <input type="text" class="scheduleActivity" name="scheduleActivity"  value="Arrive at site">
                                </div>
                                <div class="row " style="">                                
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" placeholder="Date" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime1" name="scheduleTime1" placeholder="Time" value="09:00 AM" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms1" id="scheduleAuditorSms1" name="scheduleAuditorSms1" value="" placeholder="Auditor">
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
                                <div class="row form-group float-label-control">
                                    <input type="text" class="scheduleActivity" name="scheduleActivity" value="Opening meeting with Sr. Management" >
                                </div>
                                <div class="row " style="">                                
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" value="" placeholder="Date" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime1" name="scheduleTime1" value="09:15 AM" placeholder="Time" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms1" id="scheduleAuditorSms1" name="scheduleAuditorSms1" value="" placeholder="Auditor">
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
                                <div class="row form-group float-label-control">
                                    <input type="text" class="scheduleActivity" name="scheduleActivity" value="Plant/s tour">
                                </div>
                                <div class="row " style="">                                
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" value="" placeholder="Date" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime1" name="scheduleTime1" value="09:45 AM" placeholder="Time" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms1" id="scheduleAuditorSms1" name="scheduleAuditorSms1" value="" placeholder="Auditor">
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
                                <div class="row form-group float-label-control">
                                    <input type="text" class="scheduleActivity" name="scheduleActivity" value="REVIEW OF CLIENTS DOCUMENTED INFORMATION" >
                                </div>
                                <div class="row " style="">                                
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" value="" placeholder="Date" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime1" name="scheduleTime1" value="10:15 AM" placeholder="Time" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms1" id="scheduleAuditorSms1" name="scheduleAuditorSms1" value="" placeholder="Auditor">
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
                                <div class="row form-group float-label-control">
                                    <textarea rows="6" class="scheduleActivity" name="scheduleActivity" value="">
-Context of organization (External and Internal Issues), Risk and opportunities addressed by organisation and actions taken/decided to mitigate the risks,
-Identification of relevant interested parties by organisation and their needs and expectations, 
-Verify processes/ functions determined by organization and its interaction. (process based approach),
-About adequacy of facilities for the operations and to achieve conformity of product/service applied by organisation for certification, 
-Overview of client's Processes, product/service  provided by external provider and its controls
-Verification of applicable Statutory & Regulatory Requirements (including Product/service Regulatory Requirements)</textarea>
                                </div>
                                <div class="row " style="">                                
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" value="" placeholder="Date" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime1" name="scheduleTime1" value="10:30 AM" placeholder="Time" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms1" id="scheduleAuditorSms1" name="scheduleAuditorSms1" value="" placeholder="Auditor">
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
                                <div class="row form-group float-label-control">
                                    <textarea rows="3" class="scheduleActivity" name="scheduleActivity">-Internal Audits
-Planning, schedule, Reports, Non-conformances, 
-Corrective actions</textarea>
                                </div>
                                <div class="row " style="">                                
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" value="" placeholder="Date" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime1" name="scheduleTime1" value="15:00 PM" placeholder="Time" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms1" id="scheduleAuditorSms1" name="scheduleAuditorSms1" value="" placeholder="Auditor">
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
                                <div class="row form-group float-label-control">
                                    <textarea rows="3" class="scheduleActivity" name="scheduleActivity" >-Management Review 
Planning, Review Inputs, review Outputs, Minutes of the Meeting, Top Management Involvement</textarea>
                                </div>
                                <div class="row " style="">                                
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" value="" placeholder="Date" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime1" name="scheduleTime1" value="15:45 PM" placeholder="Time" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms1" id="scheduleAuditorSms1" name="scheduleAuditorSms1" value="" placeholder="Auditor">
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
                                <div class="row form-group float-label-control">
                                    <input type="text" class="scheduleActivity" name="scheduleActivity" value="Auditor/s Time">
                                </div>
                                <div class="row " style="">                                
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" value="" placeholder="Date" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime1" name="scheduleTime1" value="16:45 PM" placeholder="Time" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms1" id="scheduleAuditorSms1" name="scheduleAuditorSms1" value="" placeholder="Auditor">
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
                                <div class="row form-group float-label-control">
                                    <textarea rows="5" class="scheduleActivity" name="scheduleActivity">- Discussion of the audit results and decision for the date(s) for the Stage 2 (Certification) audit.
- Handover plan/ schedule for stage 2 audit based on context of the organisation with process based approach.  
- Closing meeting </textarea>
                                </div>
                                <div class="row " style="">                                
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Date(s):</label>
                                        <input type="text" data-row-id="" class="scheduleDate" name="scheduleDate" value="" placeholder="Date" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Time:</label>
                                        <input type="text" data-row-id="" class="scheduleTime1" name="scheduleTime1" value="17.00 -18.00" placeholder="Time" required/>
                                    </div>
                                    <div class="col-sm-4 form-group float-label-control">
                                        <label>Auditor:</label>
                                        <input type="text" data-row-id="" class="flexdatalist scheduleAuditorSms1" id="scheduleAuditorSms1" name="scheduleAuditorSms1" value="" placeholder="Auditor">
                                    </div>
                                </div><hr>
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
                    </div>
                    <br>
                    <div class="row " style="">                                
                        <div class="col-sm-4 form-group float-label-control">
                            <label>Lunch Time</label>
                            <input type="text" data-row-id="" class="lunch" name="lunch" value="Lunch Time" placeholder="Lunch Time" required>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>Time From:</label>
                            <input type="text" data-row-id="" class="timeFrom" name="timeFrom" value="13.00 PM" placeholder="Time From:" required>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>Time To:</label>
                            <input type="text" data-row-id="" class="timeTo" name="timeTo" value="13.30 PM" placeholder="Time To:" required>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="schedule-service-id" name="schedule-service-id" >
                <input type="hidden" class="schedule-customer-id " name="schedule-customer-id" >
                <input type="hidden" class="scheduleDateHidden" name="scheduleDateHidden" >
                <input type="hidden" class="scheduleTime-Hidden" name="scheduleTime-Hidden" >
                <input type="hidden" class="scheduleActivityHidden" name="scheduleActivityHidden" >
                <input type="hidden" class="scheduleAuditor-Hidden" name="scheduleAuditor-Hidden" >
                <div class="modal-footer">
                    <button type="submit" name="addScheduleStage" class="btn btn-xs btn-info addScheduleStage">Save</button>  
                    <!--<button type="submit" name="addScheduleStagePdf" class="btn btn-xs btn-info addScheduleStagePdf">Save & Generate</button>-->  
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="scheduleStageIEdit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                Audit Plan - Stage - 01
            </div>  
            <form action="addScheduleStage.php" method="post">
                <div class="modal-body" >
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formSchedule">
                            <span><i class="fa  ">Audit Plan - STAGE 1</i></span>
                        </div>
                    </div>
                    <div class="formScheduleDiv">
                        <div class="row " style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Client Name:</label>
                                <input type="text" class="scheduleClientNameEdit" id="scheduleClientNameEdit" name="scheduleClientNameEdit" placeholder="Client Name:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Agreement No.:</label>
                                <input type="text" readonly class="scheduleRefNoEdit" id="scheduleRefNoEdit" name="scheduleRefNoEdit" placeholder="Agreement No.:" required>
                            </div>
                        </div> 
                        <div class="row " style="">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Site(s):</label>
                                <textarea rows="4"  class="scheduleAddressEdit" id="scheduleAddressEdit" name="scheduleAddressEdit" placeholder="Site(s):" required></textarea>
                            </div>
                            <div class="col-sm-6 form-group ">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Contact Person:</label>
                                        <input type="text" class="scheduleContactPersonEdit" id="scheduleContactPersonEdit" name="scheduleContactPersonEdit" placeholder="Contact Person:" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Scope:</label>
                                        <input type="text" class="scheduleScopeEdit" id="scheduleScopeEdit" name="scheduleScopeEdit" placeholder="Scope:" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row " style="">                                
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Assessment Standard/s:</label>
                                <input type="text" readonly class="scheduleAppStandardEdit" id="scheduleAppStandardEdit" name="scheduleAppStandardEdit" placeholder="Applicable Standard:" required>
                            </div>
                            <div class="col-sm-4 form-group float-label-control">
                                <label>Division:</label>
                                <input type="text" readonly class="scheduleIAFcodeEdit" id="scheduleIAFcodeEdit" name="scheduleIAFcodeEdit" placeholder="Division:" required>
                            </div>
                            <div class="col-sm-4 form-group float-label-control">
                                <label>Group:</label>
                                <input type="text" readonly class="scheduleNacecodeEdit" id="scheduleNacecodeEdit" name="scheduleNacecodeEdit" placeholder="Group:" required>
                            </div>
                            <div class="col-sm-4 form-group float-label-control">
                                <label>Classs/es:</label>
                                <input type="text" readonly class="scheduleNaceclassEdit" id="scheduleNaceclassEdit" name="scheduleNaceclassEdit" placeholder="Classs/es:" required>
                            </div>
                        </div>
                        <div class="row " style="">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Audit Type:</label>
                                <input type="text" class="scheduleAuditTypeEdit" id="scheduleAuditTypeEdit" name="scheduleAuditTypeEdit" placeholder="Audit Type:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Mandays:</label>
                                <input type="text" class="scheduleMandaysEdit" id="scheduleMandaysEdit" name="scheduleMandaysEdit" placeholder="Mandays:" required>
                            </div>
                        </div>
                        <div class="row " style="">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Audit Date:</label>
                                <input type="text" class="scheduleAuditDateEdit" id="scheduleAuditDateEdit" name="scheduleAuditDateEdit" placeholder="Audit Date:" required>
                            </div>                            
                        </div>
                        <div class="row " style=""> 
                            <div class="col-sm-12 form-group float-label-control">
<span>Member/s Presence during audit:</span>
                                <input type="text" class="scheduleAuditorEdit" id="scheduleAuditorEdit" name="scheduleAuditorEdit" placeholder="Auditor/s:">
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn ">
                            <span><i class="fa  ">Audit Plan - STAGE 1</i></span>
                            <a class="editStage1Schedule"  style="float: right" title="Add Multiple Nace Code"><i class="fa fa-plus-circle"></i></a>
                        </div>
                    </div>
                    <div class="row formScheduleIIDiv" >
                        <br>
                        <div class="editSchedules">
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
                        <input type="text" class="editlunch" name="editlunch" value="Lunch Time" placeholder="Lunch Time" required>
                    </div>
                    <div class="col-sm-4 form-group float-label-control">
                        <label>Time From:</label>
                        <input type="text" class="edittimeFrom" name="edittimeFrom" value="" placeholder="Time From:" required>
                    </div>
                    <div class="col-sm-4 form-group float-label-control">
                        <label>Time To:</label>
                        <input type="text" class="edittimeTo" name="edittimeTo" value="" placeholder="Time To:" required>
                    </div>
                </div>
                <input type="hidden" class="schedule-service-id-edit" name="schedule-service-id-edit" value="">
                <input type="hidden" class="schedule-customer-id-edit" name="schedule-customer-id-edit" value="">
                <input type="hidden" class="schedule-audit-id-edit" name="schedule-audit-id-edit" value="">
                <input type="hidden" class="scheduleDateHidden-edit" name="scheduleDateHidden-edit" value="">
                <input type="hidden" class="schedule1TimeHidden-edit" name="schedule1TimeHidden-edit" value="">
                <input type="hidden" class="scheduleActivityHidden-edit" name="scheduleActivityHidden-edit" value="">
                <input type="hidden" class="scheduleAuditorHidden-edit" name="scheduleAuditorHidden-edit" value="">

                <div class="modal-footer">
                    <button type="submit" name="" class="btn btn-xs btn-info addEditScheduleStage">Save</button>                    
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

        $(".newStage1Schedule").click(function () {
            $(".formScheduleIIDiv").append('<div class="row stage1schedulerow" style="">\n\
                                                        <div class="col-sm-11 form-group float-label-control">\n\
                                                            <div class="row form-group float-label-control" style="">\n\
                                                                <textarea rows="2" class="scheduleActivity" name="scheduleActivity" value=""></textarea>\n\
                                                            </div>\n\
                                                            <div class="row " style=""> \n\
                                                                <div class="col-sm-4 form-group ">\n\
                                                                    <label class="dispClass">Date(s):</label>\n\
                                                                    <input type="text" class="scheduleDate" name="scheduleDate" id="scheduleDate" value="" placeholder="Date" >\n\
                                                                </div>\n\
                                                                <div class="col-sm-4 form-group ">\n\
                                                                    <label class="dispClass">Time:</label>\n\
                                                                    <input type="text" class="scheduleTime" id="scheduleTime" name="scheduleTime" value="" placeholder="Time" >\n\
                                                                </div>\n\
                                                                <div class="col-sm-4 form-group ">\n\
                                                                    <label class="dispClass">Auditor1:</label>\n\
                                                                    <input type="text" class="flexdatalist scheduleAuditorSms1" id="scheduleAuditorSms1" name="scheduleAuditorSms1" value="" placeholder="Auditor">\n\
                                                                </div>\n\
                                                            </div><hr>\n\
                                                        </div>\n\
                                                        <div class="col-sm-1 removestage1schedulecol">\n\
                                                            <a class="removestage1schedule"><i class="fa fa-times"></i></a>\n\
                                                        </div>\n\
                                                    </div>');
        });

        $('body').on("click", ".removestage1schedule", function () {
//        alert("Delete this");
            $(this).parent("div.removestage1schedulecol").parent('div.stage1schedulerow').remove();
        });

        $('.createStageSchedule').click(function () {
            var customerId = $(this).attr('data-customer');
            var serviceId = $(this).attr('data-service');
            $('.schedule-customer-id').val(customerId);
            $('.schedule-service-id').val(serviceId);
            
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customer_id: customerId,
                    service_id: serviceId,
                },
                success: function (data)
                {
                    if (data[4]?.aif_code === undefined) {
  // Assuming data[0].aif_nace is a string
  const aifnaceString = data[0].aif_nace;

  const [iafcode, iafnace, iafclass] = aifnaceString
    .split(',')
    .map(value => value.split('/'))
    .reduce((acc, [code, nace, cls]) => {
      acc[0].push(code);
      acc[1].push(nace);
      acc[2].push(cls);
      return acc;
    }, [[], [], []]);

  $('.scheduleIAFcode').val(iafcode.join(','));
  $('.scheduleNacecode').val(iafnace.join(','));
  $('.scheduleNaceclass').val(iafclass.join(','));
  $('.scheduleScope').val(data[0].scope);
} else {
                        $('.scheduleIAFcode').val(data[7]['iaf_code']);
                        $(".scheduleNacecode").val(data[7]["nace_codes"]);
                        $('.scheduleScope').val(data[4]['proposed_scope']);
                    }
//            if (serviceType != "" || serviceType != null) {
//            $('.scheduleAuditType').val(serviceType);
//            }
//                    alert(data[1]["special_audit"]);
                    $('.scheduleAuditType').val(data[1]["special_audit"]);
//                    $('.scheduleAuditType').val(data[7]["nace_codes"]);
                    $('.scheduleClientName').val(data[0]['customer_name']);
                    $('.scheduleAddress').val(data[0]['address']);
                    if (data[0] && data[0]['unique_id'] != null && data[0]['unique_id'] !== "") {
                        $('.scheduleRefNo').val(data[0]['unique_id']);
                        $('.scheduleRefNo').trigger('change');
                    }
                    $('.scheduleContactPerson').val(data[0]['contact_person']);
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
//                    $('.scheduleAuditDate').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
                    $('.scheduleAuditor').val(auditors);
//                    $('.scheduleDate').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
//                    $('.scheduleAuditorSms1').val(data[2]['auditor_name']);
                    $('.scheduleAuditorSms1').val(auditorsabv);
                    var auditDate = data[1]['audit_dates'].split(', ');
                    var auditDateLen = auditDate.length;
                    var max = (Math.max(auditDateLen));
//                    alert(personLen);
                    for (var key = 0; key < max; key++) {
                        var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";
                        $('.scheduleAuditDate').val(no);
                        $('.scheduleDate').val(no);
                    }
                    $('input[type="text"]').trigger('change');
                    $('.scheduleAddress').trigger('change');
//                    alert("in");
                    $('#scheduleStageI').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $(".editStage1Schedule").click(function () {
            $('.editSchedules').append('<div class="row stage1schedulerow" style="">\n\
                                            <div class="col-sm-11 form-group float-label-control">\n\
                                                <div class="row form-group float-label-control" style="">\n\
                                                    <textarea rows="3" class="scheduleActivityEdit" name="scheduleActivityEdit">\</textarea>\n\
                                                </div>\n\
                                                <div class="row " style=""> \n\
                                                    <div class="col-sm-4 form-group ">\n\
                                                        <label class="dispClass">Date(s):</label>\n\
                                                        <input type="text" data-row-id="" class="scheduleDateEdit" name="scheduleDateEdit" id="scheduleDateEdit" value="" placeholder="Date" required/>\n\
                                                    </div>\n\
                                                    <div class="col-sm-4 form-group ">\n\
                                                        <label class="dispClass">Time:</label>\n\
                                                        <input type="text" data-row-id="" class="scheduleTimeEdit" id="scheduleTimeEdit" name="scheduleTimeEdit" value="" placeholder="Time" required/>\n\
                                                    </div>\n\
                                                    <div class="col-sm-4 form-group ">\n\
                                                        <label class="dispClass">Auditor1:</label>\n\
                                                        <input type="text" data-row-id="" class="scheduleAuditorEditCs" id="scheduleAuditorEditCs" name="scheduleAuditorEditCs"  placeholder="Auditor" value="">\n\
                                                    </div>\n\
                                                </div><hr>\n\
                                            </div>\n\
                                            <div class="col-sm-1 removestage1schedulecol">\n\
                                                <a class="removestage1schedule"><i class="fa fa-times"></i></a>\n\
                                            </div>\n\
                                        </div>');
        });

        $('.editStageSchedule').click(function () {
            var customerId = $(this).attr('data-customer');
            var serviceId = $(this).attr('data-service');
            $('.schedule-service-id-edit').val(serviceId);
            $('.schedule-customer-id-edit').val(customerId);
            $('.schedule-audit-id-edit').val($(this).attr('data-row-id'));
            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    scheduleId: $(this).attr('data-row-id'),
                    serviceID: serviceId,
                },
                success: function (data)
                {
//                    alert(data);
                    $('.scheduleClientNameEdit').val(data[0]['client_name']);
                    $('.scheduleRefNoEdit').val(data[0]['ref_no']);
                    $('.scheduleAddressEdit').val(data[0]['schedule_address']);
                    $('.scheduleContactPersonEdit').val(data[0]['schedule_contact_person']);
                    $('.scheduleScopeEdit').val(data[0]['schedule_scope']);
                    $('.scheduleAppStandardEdit').val(data[0]['schedule_app_standard']);
                    $('.scheduleIAFcodeEdit').val(data[0]['schedule_aifcode']);
                    $('.scheduleNacecodeEdit').val(data[0]['schedule_nacfcode']);
                    $('.scheduleNaceclassEdit').val(data[0]['schedule_naceclass']);
                    $('.scheduleAuditTypeEdit').val(data[0]['schedule_audit_type']);
                    $('.scheduleMandaysEdit').val(data[0]['schedule_mandays']);
                    $.ajax({
                        url: "getClientInfo.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            customer_id: customerId,
                            service_id: serviceId,
                        },
                        success: function (clientData) {
                            if (clientData && clientData[0] && clientData[0]['unique_id'] != null && clientData[0]['unique_id'] !== "") {
                                $('.scheduleRefNoEdit').val(clientData[0]['unique_id']);
                                $('.scheduleRefNoEdit').trigger('change');
                            }
                        }
                    });
                    if (data[0]['schedule_audit_date'] == "" || data[0]['schedule_audit_date'] == null)
                        $schedule_audit_date = "";
                    else
                        $schedule_audit_date = moment(data[0]['schedule_audit_date']).format('DD.MM.YYYY');
                    $('.scheduleAuditDateEdit').val(data[4]['audit_dates']);
                                        let leadAuditors = [];
                                        if (data[2] && typeof data[2] === 'object') {
    for (const key in data[2]) {
        if (data[2].hasOwnProperty(key)) {
            if (data[2][key]["auditor_category"] == "Team Leader"){
                        const combined = data[2][key].auditor_name + ' (' + data[2][key].auditor_category + ')';
        leadAuditors.push(combined);
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
                    $('.scheduleAuditorEdit').val($leadauditor);
                    $(".editlunch").val(data[0]["break"]);
                    $(".edittimeFrom").val(data[0]["break_from"]);
                    $(".edittimeTo").val(data[0]["break_to"]);
                    var allAuditors = ("<?php echo $allAuditors; ?>");
                    var auditors = allAuditors.split(',');
                    var option = "";
                    for (var key in auditors) {
                        if (auditors[key] != null && auditors[key] != "") {
                            option += '<option value="' + auditors[key] + '">' + auditors[key] + '</option>';
                        }
                    }
                    $('.editSchedules').empty();
                    for (var key in data[1]) {
                        var option2 = '<option value="' + data[1][key]['schedule_auditors'] + '"selected>' + data[1][key]['schedule_auditors'] + '</option>';
                        if (data[1][key]['schedule_dates'] == "" || data[1][key]['schedule_dates'] == null) {
                            $schedule_dates = "";
                        } else {
//                            $schedule_dates = moment(data[1][key]['schedule_dates']).format('DD.MM.YYYY');
                            $schedule_dates = data[1][key]['schedule_dates'];
                        }

                        //changes by Appa
                        $('.editSchedules').append('<div class="row stage1schedulerow" style="">\n\
                                                        <div class="col-sm-11 form-group float-label-control">\n\
                                                            <div class="row form-group float-label-control" style="">\n\
                                                                <textarea rows="3" class="scheduleActivityEdit" name="scheduleActivityEdit">' + data[1][key]["schedule_activity"] + '\</textarea>\n\
                                                            </div>\n\
                                                            <div class="row " style=""> \n\
                                                                <div class="col-sm-4 form-group ">\n\
                                                                    <label class="dispClass">Date(s):</label>\n\
                                                                    <input type="text" data-row-id="" class="scheduleDateEdit" name="scheduleDateEdit" id="scheduleDateEdit" value="' + $schedule_dates + '" placeholder="Date" required/>\n\
                                                                </div>\n\
                                                                <div class="col-sm-4 form-group ">\n\
                                                                    <label class="dispClass">Time:</label>\n\
                                                                    <input type="text" data-row-id="" class="scheduleTimeEdit" id="scheduleTimeEdit" name="scheduleTimeEdit" value="' + data[1][key]['schedule_time'] + '" placeholder="Time" required/>\n\
                                                                </div>\n\
                                                                <div class="col-sm-4 form-group ">\n\
                                                                    <label class="dispClass">Auditor:</label>\n\
                                                                    <input type="text" data-row-id="" class="scheduleAuditorEditCs" id="scheduleAuditorEditCs" name="scheduleAuditorEditCs"  placeholder="Auditor" value="' + data[1][key]['schedule_auditors'] + '">\n\
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
                    $('#scheduleStageIEdit').modal('show');
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

        $('.addScheduleStage').click(function () {
            var scheduleDateArray = [];
            $('.scheduleDate').each(function () {
                scheduleDateArray.push($(this).val());
            });
            $('.scheduleDateHidden').val(scheduleDateArray.join("~"));
            var scheduleTimeArray = [];
            $('.scheduleTime1').each(function () {
                scheduleTimeArray.push($(this).val());
//                alert($(this).val());
            });
//            alert(scheduleTimeArray);
            $('.scheduleTime-Hidden').val(scheduleTimeArray.join("~"));
            var scheduleActivityArray = [];
            $('.scheduleActivity').each(function () {
                scheduleActivityArray.push($(this).val());
            });
            $('.scheduleActivityHidden').val(scheduleActivityArray.join("~"));
            var scheduleAuditorArray = [];
            $('.scheduleAuditorSms1').each(function () {
                scheduleAuditorArray.push($(this).val());
            });
//            alert(scheduleAuditorArray);
            $('.scheduleAuditor-Hidden').val(scheduleAuditorArray.join("~"));
//            alert($('.scheduleAuditor-Hidden').val());
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
            $('.schedule1TimeHidden-edit').val(scheduleTimeArrayEdit.join("~"));
            var scheduleActivityArrayEdit = [];
            $('.scheduleActivityEdit').each(function () {
                scheduleActivityArrayEdit.push($(this).val());
            });
            $('.scheduleActivityHidden-edit').val(scheduleActivityArrayEdit.join("~"));
            var scheduleAuditorArrayEdit = [];
            $('.scheduleAuditorEditCs').each(function () {
                scheduleAuditorArrayEdit.push($(this).val());
            });
            $('.scheduleAuditorHidden-edit').val(scheduleAuditorArrayEdit.join("~"));
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
                $('.formScheduleII').click(function () {
            $('.formScheduleIIDiv').slideToggle('slow');
        });

//        $('.formScheduleIIBtn').click(function () {
//            $('.formScheduleIIDivBtn').slideToggle('slow');
//        });
        $('.formScheduleIIDiv').on('click', '.addSchedule', function() {
    // Get the parent row of the clicked "+" button
    const parentRow = $(this).closest('.stage1schedulerow');
    // Clone the parent row
    const newRow = parentRow.clone();
newRow.find('.scheduleActivityEdit').val('');
newRow.find('.scheduleTimeEdit').val('');
newRow.find('.scheduleAuditorEditCs').val('');
newRow.find('.scheduleActivity').val('');
newRow.find('.scheduleTime1').val('');
newRow.find('.scheduleAuditorSms1').val('');
    // Clear the input fields in the cloned row
    //newRow.find('input, textarea').val('');
    // Insert the cloned row after the parent row
    parentRow.after(newRow);
});

        $('.addScheduleLink').click(function () {
            $('.addNewSs').append('<div class="row " style="">\n\
                            <div class="col-sm-12 form-group float-label-control">\n\
                            <label>Activity:</label>\n\
                            <textarea type="text" data-row-id="" class="scheduleActivity" id="scheduleActivity" name="scheduleActivity" value="" placeholder="Activity"></textarea>\n\
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
                            <input type="text" data-row-id="" class="scheduleAuditorSms1" id="scheduleAuditorSms1" name="scheduleAuditorSms1"  placeholder="Auditor">\n\
                            </div>\n\
                            </div>');
            $('input[type="text"]').trigger('change');
            //$('textarea').trigger('change');
        });
        $('.addEditScheduleLink').click(function () {
            $('.formScheduleIIDiv').append(' <div class="row " style=""> \n\
                            <div class="col-sm-12 form-group float-label-control">\n\
                                <label>Activity:</label>\n\
                                  <textarea type="text" data-row-id="" class="scheduleActivityEdit" id="scheduleActivityEdit" name="scheduleActivityEdit" value="" placeholder="Activity"></textarea>\n\
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
    });
</script>

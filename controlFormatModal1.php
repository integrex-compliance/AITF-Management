<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="controlFormat1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                CONTROL FORMAT -1
            </div>  
            <form action="addControlFormat1.php" method="post">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formControlFormat1">
                            <span><i class="fa  ">CONFLICT OF INTERESTS& IMPARTIALITY</i></span>
                        </div>
                    </div>
                    <div class="formControlFormat1Div">
                        <div class="row " style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Name of client:</label>
                                <input type="text" class="controlFormat1ClientName" id="controlFormat1ClientName" name="controlFormat1ClientName" placeholder="Name of client:" required="">
                            </div>
                          
                      
                        <div class="col-sm-6">
                            <div id="input-type" class="row">
                                <label class="radio-inline">
                                    <input name="clientreferance" id="clientreferance" class="clientreferance" value="Initial Certification" type="radio" />Initial Certification
                                </label>
                                <label class="radio-inline">
                                    <input name="clientreferance" id="clientreferance" class="clientreferance" value="Recertification" type="radio" />Recertification
                                </label>
                                <label class="radio-inline">
                                    <input name="clientreferance" id="clientreferance" class="clientreferance" value="Other" type="radio" />Other
                                    <input type="text" class="Othertext" id="Othertext" name="Othertext" placeholder="Other" readonly="true">
                                </label>
                            </div>
                        </div>
                    </div>
                        <div class="row " >                                
                            <div class="col-sm-6">
                                <div id="certificateNumbersContainer">
                                    <!-- Certificate number fields will be dynamically added here -->
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Date of review:</label>
                                        <input type="text" class="controlFormat1CertDate" id="controlFormat1CertDate" name="controlFormat1CertDate" placeholder="Date of review:" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="controlFormat1CertDateError" style="display: none">
                                            <small> <span id="controlFormat1CertDateErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>                                
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formControlFormat1Qstn">
                            <span><i class="fa  ">CONFLICT OF INTERESTS& IMPARTIALITY</i></span>
                        </div>
                    </div>
                    <div class="formControlFormat1QstnDiv" style="display: none">
                        <br>
                        <div class="editdiv">
                            <div class="row" >
                                
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>1. Was AITF's top management related to the consultancy organization of the client?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Was AITF's top management related to the consultancy organization of the client?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="1">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="CONSULTANCY">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select> 
                                    <!--<input type="text" class="controlFormat1RiskDegree" id="controlFormat1RiskDegree" name="controlFormat1RiskDegree" placeholder="Degree of Risk">-->
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>2. Did AITF's auditor or individual involved in certification process have any affiliations with the client's consulting organizations? </span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Did AITF's auditor or individual involved in certification process have any affiliations with the client's consulting organizations?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="2">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="CONSULTANCY">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>3. Whether auditor or individual involved in certification process was related to the consulting organisation of the client?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Whether auditor or individual involved in certification process was related to the consulting organisation of the client?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="3">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="CONSULTANCY">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12"> 
                                    <strong>TRAINING, INTERNAL AUDITS & OTHER SERVICES</strong>
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>4. Did AITF provide any training services to the client?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Did AITF provide any training services to the client?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="1">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="TRAINING, INTERNAL AUDITS & OTHER SERVICES">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>5. Did AITF provide any internal audit services to the client?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Did AITF provide any internal audit services to the client?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="2">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="TRAINING, INTERNAL AUDITS & OTHER SERVICES">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>6. Did AITF provide supplier assessment services to the client?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Did AITF provide supplier assessment services to the client?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="3">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="TRAINING, INTERNAL AUDITS & OTHER SERVICES">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>7. Did AITF offer any services other than registration to the client?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Did AITF offer any services other than registration to the client?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="4">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="TRAINING, INTERNAL AUDITS & OTHER SERVICES">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls">
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>8. Did AITF link the success of the client's audit to the amount of salary/bonus paid to the auditors?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Did AITF link the success of the client's audit to the amount of salary/bonus paid to the auditors?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="5">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="TRAINING, INTERNAL AUDITS & OTHER SERVICES">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>9. Did AITF provide its auditing activity to the clients consulting organisation's employee or any managerial position?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Did AITF provide its auditing activity to the clients consulting organisation's employee or any managerial position?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="6">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="TRAINING, INTERNAL AUDITS & OTHER SERVICES">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>10. Is there any information or evidence of acceptance of rewards, incentives, bribes, presents etc., from the client?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Is there any information or evidence of acceptance of rewards, incentives, bribes, presents etc., from the client?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="7">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="TRAINING, INTERNAL AUDITS & OTHER SERVICES">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12"> 
                                    <strong>AUDITOR SELECTION/ALLOCATION</strong>
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>11. Was the selection of auditor done as per the scope of certification of the client?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Was the selection of auditor done as per the scope of certification of the client?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="1">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="AUDITOR SELECTION/ALLOCATION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>12. Did the audit team have adequate technical knowledge and expertise on the processes and geographical area of the client? </span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Did the audit team have adequate technical knowledge and expertise on the processes and geographical area of the client?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="2">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="AUDITOR SELECTION/ALLOCATION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>13. Has the auditor been an ex- employee/ part of the management of the client organization? </span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Has the auditor been an ex- employee/ part of the management of the client organization?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="3">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="AUDITOR SELECTION/ALLOCATION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>14. Has the auditor signed declaration statement on Impartiality & Confidentiality?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Has the auditor signed declaration statement on Impartiality & Confidentiality?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="4">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="AUDITOR SELECTION/ALLOCATION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>15. Whether same team leader was appointed for last two consequence audits?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Whether same team leader was appointed for last two consequence audits?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="5">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="AUDITOR SELECTION/ALLOCATION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12"> 
                                    <strong>CERTIFICATION DECISION</strong>
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>16. Was the audit carried out as per the defined criteria by AITF and contents of audit report are adequate for certification decision? </span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Was the audit carried out as per the defined criteria by AITF and contents of audit report are adequate for certification decision?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="1">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="CERTIFICATION DECISION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>17. Has any audit team member is been a part of AITF's team for certification decision? </span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Has any audit team member is been a part of AITF's team for certification decision?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="2">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="CERTIFICATION DECISION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>18. Did the group or individual that took the decision on granting of certification understand the applicable standard, certification requirements, and competent to evaluate the audit processes and related recommendations of the audit?</span>                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Did the group or individual that took the decision on granting of certification understand the applicable standard, certification requirements, and competent to evaluate the audit processes and related recommendations of the audit?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="3">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="CERTIFICATION DECISION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-sm-12">                            
                                    <span>19. Were there any potential sources of conflict of interests identified in connection with the certification of the client, either from within AITF or from the activities of other persons, bodies?</span>
                                </div>
                                <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="Were there any potential sources of conflict of interests identified in connection with the certification of the client, either from within AITF or from the activities of other persons, bodies?">
                                <input type="hidden" class="hiddensrno" name="hiddensrno" value="4">
                                <input type="hidden" class="hiddentitle" name="hiddentitle" value="CERTIFICATION DECISION">
                            </div>
                            <div class="row" style="padding-top: 12px" >
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Comments</label>
                                    <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-xs-3 form-group float-label-control">
                                    <label>Risk, if any</label>
                                    <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select> 
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Degree of Risk</label>
                                    <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >
                                        <option value="">Select</option>
                                        <option value="High">High</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-sm-3 form-group float-label-control">
                                    <label>Controls</label>
                                    <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" placeholder="Controls" >
                                </div>
                            </div>
                        </div>
                           
                    </div>
                </div>
                <input type="hidden" class="control-format-service" name="control-format-service" value="">
                <input type="hidden" class="control-format-customer" name="control-format-customer" value="">
                <input type="hidden" class="control-format-id" name="control-format-id" value="">
                <input type="hidden" class="controlformatcliref" name="controlformatcliref" value="">
                <input type="hidden" class="risk-question-hidden" name="risk-question-hidden" value="">
                <input type="hidden" class="comment-hidden" name="comment-hidden" value="">
                <input type="hidden" class="risk-hidden" name="risk-hidden" value="">
                <input type="hidden" class="risk-degree-hidden" name="risk-degree-hidden" value="">
                <input type="hidden" class="controls-hidden" name="controls-hidden" value=""> 
                <input type="hidden" class="srno-hidden" name="srno-hidden" value=""> 
                <input type="hidden" class="title-hidden" name="title-hidden" value=""> 
                <input type="hidden" class="controlFormat1Address" name="controlFormat1Address" value="">
                <div class="modal-footer">
                    <button type="submit" name="addControlFormat1" class="btn btn-xs btn-info addControlFormat1">Save</button>
                    <!--<button type="submit" name="addControlFormat1Pdf" class="btn btn-xs btn-info addControlFormat1Pdf">Save & Generate</button>-->
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
    .controlFormat1RiskDegree{
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
    .controlFormat1Comment{
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
    .controlFormat1Risk{
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
    #controlFormat1Controls{
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
        $('#controlFormat1CertDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true,
            onSelect: function() {
                // Validate on date selection
                validateCF1();
            }
        });
        $("#controlFormat1CertDate").keydown(function (e) {
//            e.preventDefault();
        });
        $('.controlFormat1CertDate').on('change blur', function() {
            // Validate Control Format-1 Date on change/blur
            if ($(this).val() != "") {
                validateCF1();
            }
        });        
    });
$(".clientreferance").click(function () {
//            alert($(this).val());
            $referance = $(this).val();
            if ($referance == 'Other')
            {
            $(".Othertext").removeAttr('readonly');
            $(".Othertext").attr('required','');
            $(".othertext").val("");
            }
            else
            {
                $(".othertext").val("");
                $(".Othertext").attr('readonly','true');
                $(".Othertext").removeAttr('required');
                $(".customerReferred").val($referance);
            }
        });
    function validateCF1() {
        $isError = false;
        if ($(".controlFormat1CertDate").val() == "") {
            $('#controlFormat1CertDateErrorMsg').text("CONTROL FORMAT -1 Date cannot be empty");
            $('#controlFormat1CertDateError').show();
            $('#controlFormat1CertDate').addClass('inputHighlight');
            $isError = true;
        } else if ($(".controlFormat1CertDate").val() != "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    controlFormat1CertDate: $('.controlFormat1CertDate').val(),
                    cf1Service_ID: $('.control-format-service').val(),
                },
                async: false,
                success: function (data) {
//    	                alert(data);
                    if (data == 1) {
                        $('#controlFormat1CertDateErrorMsg').text("CONTROL FORMAT -1 Date must be greater than or equal to Review Date or Certificate Issue Date");
                        $('#controlFormat1CertDateError').show();
                        $('#controlFormat1CertDate').addClass('inputHighlight');
                        $isError = true;
                    } else {
                        $('#controlFormat1CertDateError').hide();
                        $('#controlFormat1CertDate').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#controlFormat1CertDateError').hide();
            $('#controlFormat1CertDate').removeClass('inputHighlight');
        }        
        return $isError;
    }

    $(document).ready(function () {
        //        code by Pallavi to add control format 1
        $(document).on('click', '.createControlFormat1', function () {
            var $btn = $(this);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            var restoreBtn = function() {
                $btn.prop('disabled', false).html(originalHtml);
            };

            var customerId = $(this).attr('data-customer');
            var serviceId = $(this).attr('data-service');

            $('.control-format-service').val(serviceId);
            $('.control-format-customer').val(customerId);
            
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
                        $('.controlFormat1ClientName').val(data[0]['customer_name'] || '');
                        $('.controlFormat1Address').val(data[0]['address'] || '');
                        var certDate = (data[2] && data[2]['revDatecdc']) ? moment(data[2]['revDatecdc']).format('DD.MM.YYYY') : '';
                        $('.controlFormat1CertDate').val(certDate);
                        
                        $.ajax({
                            url: "getCertificateNumbers.php",
                            type: "GET",
                            dataType: "json",
                            data: {
                                customer_id: customerId
                            },
                            success: function(certData) {
                                $('#certificateNumbersContainer').empty();
                                
                                if (certData && certData.success && certData.count > 0) {
                                    certData.certificates.forEach(function(cert, index) {
                                        var certField = '<div class="form-group float-label-control" style="margin-bottom: 10px;">' +
                                            '<label>Certificate No. ' + (index + 1) + ':</label>' +
                                            '<input type="text" class="controlFormat1RefNo" name="controlFormat1RefNo[]" ' +
                                            'placeholder="Certificate No.:" value="' + cert.certificate_no + '" required>' +
                                            '</div>';
                                        $('#certificateNumbersContainer').append(certField);
                                    });
                                } else {
                                    var certNo = data[0]['certification_no'] || '';
                                    if (certNo && certNo.indexOf(',') !== -1) {
                                        var certNumbers = certNo.split(',');
                                        certNumbers.forEach(function(cert, index) {
                                            cert = cert.trim();
                                            if (cert) {
                                                var certField = '<div class="form-group float-label-control" style="margin-bottom: 10px;">' +
                                                    '<label>Certificate No. ' + (index + 1) + ':</label>' +
                                                    '<input type="text" class="controlFormat1RefNo" name="controlFormat1RefNo[]" ' +
                                                    'placeholder="Certificate No.:" value="' + cert + '" required>' +
                                                    '</div>';
                                                $('#certificateNumbersContainer').append(certField);
                                            }
                                        });
                                    } else {
                                        var certField = '<div class="form-group float-label-control">' +
                                            '<label>Certificate No.:</label>' +
                                            '<input type="text" class="controlFormat1RefNo" name="controlFormat1RefNo" ' +
                                            'placeholder="Certificate No.:" value="' + certNo + '" required>' +
                                            '</div>';
                                        $('#certificateNumbersContainer').append(certField);
                                    }
                                }
                                $('input[type="text"]').trigger('change');
                            },
                            error: function() {
                                $('#certificateNumbersContainer').empty();
                                var certField = '<div class="form-group float-label-control">' +
                                    '<label>Certificate No.:</label>' +
                                    '<input type="text" class="controlFormat1RefNo" name="controlFormat1RefNo" ' +
                                    'placeholder="Certificate No.:" value="' + (data[0]['certification_no'] || '') + '" required>' +
                                    '</div>';
                                $('#certificateNumbersContainer').append(certField);
                                $('input[type="text"]').trigger('change');
                            }
                        });
                        
                        $('#controlFormat1').modal('show');
                    } catch (e) {
                        console.error("Error in createControlFormat1:", e);
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

        //code need to be reconstruct by Pallavi
        $(document).on('click', '.editControlFormat1', function () {
            var $btn = $(this);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            var restoreBtn = function() {
                $btn.prop('disabled', false).html(originalHtml);
            };

            var customerId = $(this).attr('data-customer');
            var serviceId = $(this).attr('data-service');
            var rowId = $(this).attr('data-row-id');
            
            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    control_Id: rowId,
                    serviceId: serviceId,
                },
                success: function (data)
                {
                    try {
                        if (!data || !data[0]) {
                            restoreBtn();
                            alert("Data not found");
                            return;
                        }
                        $('.control-format-id').val(data[0]['control_id'] || '');
                        $('.control-format-customer').val(data[0]['customer_id'] || '');
                        $('.control-format-service').val(data[0]['caservice_id'] || '');
                        $('.controlFormat1ClientName').val(data[0]['control_client'] || '');
                        $('.controlformatcliref').val(data[0]['cert_type'] || '');
                        $('.controlFormat1Address').val(data[0]['control_address'] || '');
                        
                        var actualCustomerId = customerId || data[0]['customer_id'];
                        $.ajax({
                            url: "getCertificateNumbers.php",
                            type: "GET",
                            dataType: "json",
                            data: {
                                customer_id: actualCustomerId
                            },
                            success: function(certData) {
                                $('#certificateNumbersContainer').empty();
                                
                                if (certData && certData.success && certData.count > 0) {
                                    certData.certificates.forEach(function(cert, index) {
                                        var certField = '<div class="form-group float-label-control" style="margin-bottom: 10px;">' +
                                            '<label>Certificate No. ' + (index + 1) + ':</label>' +
                                            '<input type="text" class="controlFormat1RefNo" name="controlFormat1RefNo[]" ' +
                                            'placeholder="Certificate No.:" value="' + cert.certificate_no + '" required>' +
                                            '</div>';
                                        $('#certificateNumbersContainer').append(certField);
                                    });
                                } else {
                                    var certNo = data[0]['control_ref_no'] || '';
                                    if (certNo && certNo.indexOf(',') !== -1) {
                                        var certNumbers = certNo.split(',');
                                        certNumbers.forEach(function(cert, index) {
                                            cert = cert.trim();
                                            if (cert) {
                                                var certField = '<div class="form-group float-label-control" style="margin-bottom: 10px;">' +
                                                    '<label>Certificate No. ' + (index + 1) + ':</label>' +
                                                    '<input type="text" class="controlFormat1RefNo" name="controlFormat1RefNo[]" ' +
                                                    'placeholder="Certificate No.:" value="' + cert + '" required>' +
                                                    '</div>';
                                                $('#certificateNumbersContainer').append(certField);
                                            }
                                        });
                                    } else {
                                        var certField = '<div class="form-group float-label-control">' +
                                            '<label>Certificate No.:</label>' +
                                            '<input type="text" class="controlFormat1RefNo" name="controlFormat1RefNo" ' +
                                            'placeholder="Certificate No.:" value="' + certNo + '" required>' +
                                            '</div>';
                                        $('#certificateNumbersContainer').append(certField);
                                    }
                                }
                                $('input[type="text"]').trigger('change');
                            },
                            error: function() {
                                $('#certificateNumbersContainer').empty();
                                var certField = '<div class="form-group float-label-control">' +
                                    '<label>Certificate No.:</label>' +
                                    '<input type="text" class="controlFormat1RefNo" name="controlFormat1RefNo" ' +
                                    'placeholder="Certificate No.:" value="' + (data[0]['control_ref_no'] || '') + '" required>' +
                                    '</div>';
                                $('#certificateNumbersContainer').append(certField);
                                $('input[type="text"]').trigger('change');
                            }
                        });

                        if (data[0]['cert_type'] == "Initial Certification") {
                            $('input:radio[name=clientreferance][value="Initial Certification"]').click();
                        } else if (data[0]['cert_type'] == "Recertification") {
                            $('input:radio[name=clientreferance][value="Recertification"]').click();
                        } else {
                            $('input:radio[name=clientreferance][value="Other"]').click();
                            $('.Othertext').val(data[0]['cert_type'] || '');
                        }

                        var $control_date = "";
                        if (data[0]['control_date'])
                            $control_date = moment(data[0]['control_date']).format('DD.MM.YYYY');
                        $('.controlFormat1CertDate').val($control_date);
                        
                        $('.editdiv').empty();
                        if (data[1]) {
                            for (var key in data[1]) {
                                var srno = data[1][key]['control_sr'];
                                if (srno != null && srno != "" && srno == 1) {
                                    $('.editdiv').append('<div class="row" ><div class="col-sm-12"><strong>' + (data[1][key]['control_title'] || '') + '</strong></div></div>');
                                }

                                $('.editdiv').append('<div class="row" >\n\
                                        <div class="col-sm-12">\n\
                                            <span>' + (data[1][key]['control_question'] || '') + '</span>\n\
                                        </div>\n\
                                        <input type="hidden" class="hiddenQuestion" name="hiddenQuestion" value="' + (data[1][key]['control_question'] || '') + '">\n\
                                        <input type="hidden" class="hiddensrno" name="hiddensrno" value="' + (data[1][key]['control_sr'] || '') + '">\n\
                                        <input type="hidden" class="hiddentitle" name="hiddentitle" value="' + (data[1][key]['control_title'] || '') + '">\n\
                                    </div>\n\
                                    <div class="row" style="padding-top: 12px" >\n\
                                        <div class="col-xs-3 form-group">\n\
                                            <label class="dispClass">Comments</label>\n\
                                            <select name="controlFormat1Comment" class="form-control controlFormat1Comment" id="controlFormat1Comment" value="' + (data[1][key]['comments'] || '') + '" >\n\
                                                <option value="' + (data[1][key]['comments'] || '') + '">' + (data[1][key]['comments'] || '') + '</option>\n\
                                                <option value="Yes">Yes</option>\n\
                                                <option value="No">No</option>\n\
                                            </select>\n\
                                        </div>\n\
                                        <div class="col-xs-3 form-group">\n\
                                            <label class="dispClass">Risk, if any</label>\n\
                                            <select name="controlFormat1Risk" class="form-control controlFormat1Risk" id="controlFormat1Risk" >\n\
                                                <option value="' + (data[1][key]['risks'] || '') + '">' + (data[1][key]['risks'] || '') + '</option>\n\
                                                <option value="Yes">Yes</option>\n\
                                                <option value="No">No</option>\n\
                                            </select>\n\
                                        </div>\n\
                                        <div class="col-sm-3 form-group ">\n\
                                            <label class="dispClass">Degree of Risk</label>\n\
                                            <select name="controlFormat1RiskDegree" class="form-control controlFormat1RiskDegree" id="controlFormat1RiskDegree" >\n\
                                                <option value="' + (data[1][key]['risk_degree'] || '') + '">' + (data[1][key]['risk_degree'] || '') + '</option>\n\
                                                <option value="High">High</option>\n\
                                                <option value="Low">Low</option>\n\
                                            </select>\n\
                                        </div>\n\
                                        <div class="col-sm-3 form-group ">\n\
                                            <label class="dispClass">Controls</label>\n\
                                            <input type="text" class="controlFormat1Controls" id="controlFormat1Controls" name="controlFormat1Controls" value="' + (data[1][key]['controls'] || '') + '" placeholder="Controls">\n\
                                        </div>\n\
                                    </div>');
                            }
                        }                    
                        $('input[type="text"]').trigger('change');
                        $('.addControlFormat1Pdf').hide();
                        $('#controlFormat1').modal('show');
                    } catch (e) {
                        console.error("Error in editControlFormat1:", e);
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

        $('.formControlFormat1').click(function () {
            $('.formControlFormat1Div').slideToggle('slow');
        });

        $('.formControlFormat1Qstn').click(function () {
            $('.formControlFormat1QstnDiv').slideToggle('slow');
        });

        $('.addControlFormat1').click(function (e) {
            if (validateCF1()) {
                e.preventDefault();
                return;
            }
            var hiddenQuestionArray = [];
            $('.hiddenQuestion').each(function () {
                hiddenQuestionArray.push($(this).val());
            });
            $('.risk-question-hidden').val(hiddenQuestionArray.join("~"));

            var controlFormat1CommentArray = [];
            $('.controlFormat1Comment').each(function () {
                controlFormat1CommentArray.push($(this).val());
            });
            $('.comment-hidden').val(controlFormat1CommentArray.join("~"));

            var controlFormat1RiskArray = [];
            $('.controlFormat1Risk').each(function () {
                controlFormat1RiskArray.push($(this).val());
            });
            $('.risk-hidden').val(controlFormat1RiskArray.join("~"));

            var controlFormat1RiskDegreeArray = [];
            $('.controlFormat1RiskDegree').each(function () {
                controlFormat1RiskDegreeArray.push($(this).val());
            });
            $('.risk-degree-hidden').val(controlFormat1RiskDegreeArray.join("~"));

            var controlFormat1ControlsArray = [];
            $('.controlFormat1Controls').each(function () {
                controlFormat1ControlsArray.push($(this).val());
            });
            $('.controls-hidden').val(controlFormat1ControlsArray.join("~"));

            var hiddensrnoArray = [];
            $('.hiddensrno').each(function () {
                hiddensrnoArray.push($(this).val());
            });
            $('.srno-hidden').val(hiddensrnoArray.join("~"));

            var hiddentitleArray = [];
            $('.hiddentitle').each(function () {
                hiddentitleArray.push($(this).val());
            });
            $('.title-hidden').val(hiddentitleArray.join("~"));
        });

        $('.addControlFormat1Pdf').click(function () {
            var hiddenQuestionArray = [];
            $('.hiddenQuestion').each(function () {
                hiddenQuestionArray.push($(this).val());
            });
            $('.risk-question-hidden').val(hiddenQuestionArray.join("~"));

            var controlFormat1CommentArray = [];
            $('.controlFormat1Comment').each(function () {
                controlFormat1CommentArray.push($(this).val());
            });
            $('.comment-hidden').val(controlFormat1CommentArray.join("~"));

            var controlFormat1RiskArray = [];
            $('.controlFormat1Risk').each(function () {
                controlFormat1RiskArray.push($(this).val());
            });
            $('.risk-hidden').val(controlFormat1RiskArray.join("~"));

            var controlFormat1RiskDegreeArray = [];
            $('.controlFormat1RiskDegree').each(function () {
                controlFormat1RiskDegreeArray.push($(this).val());
            });
            $('.risk-degree-hidden').val(controlFormat1RiskDegreeArray.join("~"));

            var controlFormat1ControlsArray = [];
            $('.controlFormat1Controls').each(function () {
                controlFormat1ControlsArray.push($(this).val());
            });
            $('.controls-hidden').val(controlFormat1ControlsArray.join("~"));

            var hiddensrnoArray = [];
            $('.hiddensrno').each(function () {
                hiddensrnoArray.push($(this).val());
            });
            $('.srno-hidden').val(hiddensrnoArray.join("~"));

            var hiddentitleArray = [];
            $('.hiddentitle').each(function () {
                hiddentitleArray.push($(this).val());
            });
            $('.title-hidden').val(hiddentitleArray.join("~"));
        });

    });

</script>

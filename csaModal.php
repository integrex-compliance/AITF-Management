<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="csaModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                CERTIFICATION SERVICE AGREEMENT
            </div>
            <form action="Csa.php" method="post">
                <div class="modal-body" >
                    <div class="">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn ">
                            <span><i class="fa  ">FEES STRUCTURE</i></span>
                        </div>
                         <br><br>
                         <div class="row " style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Audit Application Date</label>
                                <input type="text" class="csaDate" id="csaDate" name="csaDate" placeholder="Audit Application Date">
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Name of the Organization</label>
                                <input type="text" class="csaName" id="csaName" name="csaName" placeholder="Name of the Organization">
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">                            
                            <div class="col-xs-6">
                                <span >Address</span>
                            </div>
                        <div class="col-xs-6">
                                <span >Scope,(IAF/NACE Code)</span>
                            </div>
                        </div>                        
                        <div class="row" style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label for="">Address</label>
                                <textarea name="csaAddress" rows="2" class="form-control form-group csaAddress" id="csaAddress" placeholder="Address"></textarea>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label for="">Scope,(IAF/NACE Code)</label>
                                <textarea name="casScope" rows="2" class="form-control form-group casScope" id="casScope" placeholder="Scope,(IAF/NACE Code)"></textarea>
                            </div>
                        </div>
                </div>
                   <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn ">
                            <span><i class="fa  ">Accreditation</i></span>
                        </div>
                       <br><br>
                                            
                        <div class="row" style="">                            
                            <div class="col-xs-9  ">
                                <span >Application:(Fee Covers Administration including work of enquiry realisation Cost & is non - refundable):</span>
                            </div>
                            <div class="col-sm-3 form-group float-label-control" >
                                <label>@RS.</label>
                                <input type="text" class="csaApplicationAmount" id="csaApplicationAmount" name="csaApplicationAmount" placeholder="@RS.">
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px">                            
                            <div class="col-xs-9  ">
                                <span >Initial Audit (Stage 1) - Documentation Review and/or Adequacy Audit:</span>
                            </div>
                            <div class="col-sm-3 form-group float-label-control" >
                                <label>@RS.</label>
                                <input type="text" class="csaDocumentationReview" id="csaDocumentationReview" name="csaDocumentationReview" placeholder="@RS.">
                            </div>
                        </div>
                        <div class="row" style="">                            
                            <div class="col-xs-9  ">
                                <span >Initial Audit (Stage 2) - Certification/Recertification Audit OR______Audit:</span>
                            </div>
                            <div class="col-sm-3 form-group float-label-control">
                                <label>@RS.</label>
                                <input type="text" class="csaCertificationRecertification" id="csaCertificationRecertification" name="csaCertificationRecertification" placeholder="@RS.">
                            </div>
                        </div>
                        <div class="row" style="">                            
                            <div class="col-xs-9  ">
                                <span ><strong>Total Cost of Initial Assessment:</strong></span>
                            </div>
                            <div class="col-sm-3 form-group float-label-control">
                                <label>@RS.</label>
                                <input type="text" class="csaInitialAssessment" id="csaInitialAssessment" name="csaInitialAssessment" placeholder="@RS.">
                            </div>
                        </div>
                        <div class="row" style="">                            
                            <div class="col-xs-9  ">
                                <span >Maintenance of Certification: Total Two surveillance audits with interval of 12 months from the certification stage-II audit. The cost of surveillance for each Year:</span>
                            </div>
                            <div class="col-sm-3 form-group float-label-control">
                                <label>@RS.</label>
                                <input type="text" class="casMaintenanceCertification" id="casMaintenanceCertification" name="casMaintenanceCertification" placeholder="@RS.">
                            </div>
                        </div>
                    </div> 
                </div>    
                <div class="modal-footer">
<!--                    <button type="submit" name="addCas" class="btn btn-xs btn-info addCas">Save</button>-->
                    <button type="submit" name="generateCsa" class="btn btn-xs btn-info generateCsa">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>
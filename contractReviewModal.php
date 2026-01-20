<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet"
      href="css/float-label.css">

<style>
    table,th,td {
        border: 1px solid gray;
        border-collapse: collapse;
    }
</style>

<div class="modal fade" id="contractReview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                Contract Review
            </div>
            <form action="addContractReview.php" method="post">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formContractReview">
                            <span><i class="fa ">Contract Review</i></span>
                        </div>
                    </div>
                    <div class="formContractReviewDiv">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Name of the customer:</label> 
                                <input type="text" class="contractCustomer" id="contractCustomer" name="contractCustomer" placeholder="Name of the customer:" >
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Customer location / Address:</label> 
                                <input type="text" class="customerAddress" id="customerAddress" name="customerAddress" placeholder="Customer location / Address:">
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Contract Agreement No.:</label> 
                                <input type="text" class="contractAgreement" id="contractAgreement" name="contractAgreement" placeholder="Contract Agreement No.:" readonly>
                                <!-- BACKUP: Show current allotment number directly from database -->
                                <div id="allotmentBackup" style="display: none; color: #666; font-size: 12px; margin-top: 5px;">
                                    Current Allotment: <span id="currentAllotment">  </span>
                                </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Date of review:</label> 
                                        <input type="text" class="dateOfReview" id="dateOfReview" name="dateOfReview" placeholder="Date of review:" required="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <div id="dateOfReviewError" style="display: none">
                                            <small><span id="dateOfReviewErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="service-id" name="service-id" value=""> 
                        <input type="hidden" class="customer-id" name="customer-id" value="">
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formContractComment">
                            <span><i class="fa  ">Review Comments (&#10004 Mark at appropriate)</i></span>
                        </div>
                    </div>

                    <div class="formContractCommentDiv" style="display: none">
                        <div class="row">
                            <div class="col-sm-12 table-responsive">
                                <table style="width: 100%">
                                    <tr>
                                        <th class="contractTableHead">Sr.</th>
                                        <th class="contractTableHead">Contract review check point</th>
                                        <th class="contractTableHead">Requirement stated & accepted</th>
                                        <th class="contractTableHead">Requirement not stated to be clarified</th>
                                        <th class="contractTableHead">Requirement stated and not acceptable to be resolved</th>
                                        <th class="contractTableHead">Not stated / Not acceptable</th>
                                        <th class="contractTableHead">Remark and follow up actions, if any</th>
                                    </tr>
                                    <tr>
                                        <td colspan=9 class="contractTableHead"><strong>General</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">1.</td>
                                        <td class="contractTableHead">Process of certification activity</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check1" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check1" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check1" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check1" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action"></td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Process of certification activity">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="1.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="General">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">2.</td>
                                        <td class="contractTableHead">Scope for certification & standard</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check2" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check2" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check2" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check2" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Scope for certification & standard">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="2.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="General">
                                    </tr>
                                    <tr>
                                        <td colspan=9 class="contractTableHead"><strong>Service</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">1.</td>
                                        <td class="contractTableHead">Service agreement for management system assessment (The Services, Warranty, Timing & Progress of the service, Postponement of Services, Duration & termination, Confidentiality, Intellectual Property rights, Payment, Indemnities and limitation of liabilities, force majeure, assignment, Dispute resolution and Governing Law, Publication)</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check3" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check3" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check3" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check3" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Service agreement for management system assessment (The Services, Warranty, Timing & Progress of the service, Postponement of Services, Duration & termination, Confidentiality, Intellectual Property rights, Payment, Indemnities and limitation of liabilities, force majeure, assignment, Dispute resolution and  Governing Law, Publication)">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="1.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Service">
                                    </tr>
                                    <tr>
                                        <td colspan=9 class="contractTableHead"><strong>Processes</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">1.</td>
                                        <td class="contractTableHead">Complains & Appeals</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check4" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check4" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check4" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check4" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Complains & Appeals">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="1.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Processes">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">2.</td>
                                        <td class="contractTableHead">Suspension, Withdrawal, Cancellation of certificate</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check5" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check5" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check5" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check5" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Suspension, Withdrawal, Cancellation of certificate">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="2.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Basic service details">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">3.</td>
                                        <td class="contractTableHead">Miscellaneous</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check6" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check6" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check6" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check6" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action " name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Miscellaneous">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="3.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Basic service details">
                                    </tr>
                                    <tr>
                                        <td colspan=9 class="contractTableHead"><strong>Basic service details</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">1.</td>
                                        <td class="contractTableHead">Scope of service</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check7" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check7" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check7" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check7" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Scope of service">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="1.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Basic service details">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">2.</td>
                                        <td class="contractTableHead">Nonconformity</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check8" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check8" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check8" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check8" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Nonconformity">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="2.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Basic service details">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">3.</td>
                                        <td class="contractTableHead">Corrective actions & surveillance audit</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check9" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check9" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check9" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check9" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Corrective actions & surveillance audit">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="3.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Basic service details">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">4.</td>
                                        <td class="contractTableHead">Certificate of conformity & Retention</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check10" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check10" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check10" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check10" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Certificate of conformity & Retention">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="4.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Basic service details">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">5.</td>
                                        <td class="contractTableHead">Renewal of the certificate</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check11" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check11" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check11" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check11" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Renewal of the certificate">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="5.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Basic service details">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">6.</td>
                                        <td class="contractTableHead">Audit team</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check12" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check12" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check12" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check12" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Audit team">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="6.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Basic service details">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">7.</td>
                                        <td class="contractTableHead">Control of mark</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check13" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check13" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check13" type="checkbox">
                                        </td>
                                        <td class="contractTableHead " style="text-align: center">
                                            <input class="req-notstate-notacpt check13" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Control of mark">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="7.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Basic service details">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">8.</td>
                                        <td class="contractTableHead">Stake holder committee (SHC)
                                            (Impartiality committee)</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check14" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check14" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check14" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check14" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>                                        
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Stake holder committee (SHC) (Impartiality committee)">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="8.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Basic service details">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">9.</td>
                                        <td class="contractTableHead">Confidentiality and independence</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check15" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check15" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check15" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check15" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Confidentiality and independence">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="9.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Basic service details">
                                    </tr>
                                    <tr>
                                        <td colspan=9 class="contractTableHead"><strong>Commercial</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">1.</td>
                                        <td class="contractTableHead">Initial Certification</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check16" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check16" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check16" type="checkbox">
                                        </td>
                                        <td class="contractTableHead check16" style="text-align: center">
                                            <input class="req-notstate-notacpt" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Initial Certification">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="1.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Commercial">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">2.</td>
                                        <td class="contractTableHead">Maintenance of Certification</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check17" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check17" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check17" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check17" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Maintenance of Certification">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="2.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Commercial">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">3.</td>
                                        <td class="contractTableHead">Special Surveillance</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check18" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check18" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check18" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check18" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Special Surveillance">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="3.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Commercial">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">4.</td>
                                        <td class="contractTableHead">Recertification</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check19" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check19" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check19" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check19" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Recertification">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="4.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Commercial">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">5.</td>
                                        <td class="contractTableHead">Follow – up Visits</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check20" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check20" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check20" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check20" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Follow – up Visits">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="5.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Commercial">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">6.</td>
                                        <td class="contractTableHead">Travel and Living Expenses</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check21" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check21" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check21" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check21" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action"></td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Travel and Living Expenses">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="6.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Commercial">
                                    </tr>
                                    <tr>
                                        <td class="contractTableHead">7.</td>
                                        <td class="contractTableHead">Service tax</td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-acpt check22" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-acpt check22" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-state-notacpt check22" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input class="req-notstate-notacpt check22" type="checkbox">
                                        </td>
                                        <td class="contractTableHead" style="text-align: center">
                                            <input type="text" class="remark-action" name="remark-action">
                                        </td>
                                    <input type="hidden" class="contract-chk-point" name="contract-chk-point" value="Service tax">
                                    <input type="hidden" class="contract-sr" name="contract-sr" value="7.">
                                    <input type="hidden" class="contract-header" name="contract-header" value="Commercial">
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row " style="padding-top: 10px">
                        <div class="col-sm-12 form-group float-label-control">
                            <label>Conclusion: </label> 
                            <input type="text" class="conclusion" id="conclusion" name="conclusion" placeholder="Conclusion:" >
                        </div>
                    </div>
                    <div class="row " style="padding-top: 10px">
                        <div class="col-sm-12 form-group float-label-control">
                            <label>Name of Reviewer: </label> 
                            <input type="text" class="reviewer" id="reviewer" name="reviewer" placeholder="Name of reviewer" >
                        </div>
                    </div>
                </div>
                <input type="hidden" class="contract-chk-point-hidden"
                       name="contract-chk-point-hidden" value=""> <input type="hidden"
                       class="contract-sr-hidden" name="contract-sr-hidden" value=""> <input
                       type="hidden" class="contract-header-hidden"
                       name="contract-header-hidden" value=""> <input type="hidden"
                       class="req-state-acpt-hidden" name="req-state-acpt-hidden" value=""> <input
                       type="hidden" class="req-notstate-acpt-hidden"
                       name="req-notstate-acpt-hidden" value=""> <input type="hidden"
                       class="req-state-notacpt-hidden" name="req-state-notacpt-hidden"
                       value=""> <input type="hidden" class="req-notstate-notacpt-hidden"
                       name="req-notstate-notacpt-hidden" value=""> <input type="hidden"
                       class="remark-action-hidden" name="remark-action-hidden" value="">
                <div class="modal-footer">
                    <button type="submit" name="addContractReview"
                            class="btn btn-xs btn-info addContractReview">Save</button>
                    <!--<button type="submit" name="contractReviewPdf" class="btn btn-xs btn-info addContractReviewPdf">save & Generate</button>-->
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editContractReview" tabindex="-1"
     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span
                        aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                Contract Review</div>
            <form action="updateContractReview.php" method="post">
                <div class="modal-body">
                    <div class="row" style="">
                        <div
                            class="col-xs-12  text-center btn  qms_complete_btn formContractReview">
                            <span><i class="fa  ">Contract Review</i></span></div>
                    </div>
                    <div class="formContractReviewDiv">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control"><label>Name of the
                                    customer:</label> <input type="text" class="editContractCustomer"
                                                         id="editContractCustomer" name="editContractCustomer"
                                                         placeholder="Name of the customer:" readonly></div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12 form-group float-label-control"><label>Customer
                                    location / Address:</label> <input type="text"
                                                                   class="editCustomerAddress" id="editCustomerAddress"
                                                                   name="editCustomerAddress" placeholder="Customer location / Address:"
                                                                   readonly></div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12 form-group float-label-control"><label>Contract
                                    Agreement No.:</label> <input type="text" class="editContractAgreement"
                                                              id="editContractAgreement" name="editContractAgreement"
                                                              placeholder="Contract Agreement No.:" readonly></div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-12 form-group float-label-control"><label>Date of
                                    review</label> <input type="text" class="dateOfReviewEdit"
                                                      id="dateOfReviewEdit" name="dateOfReviewEdit"
                                                      placeholder="Date of review:" required></div>
                        </div>

                        <input type="hidden" class="edit-service-id" name="edit-service-id"
                               value=""> <input type="hidden" class="edit-customer-id"
                               name="edit-customer-id" value=""> <input type="hidden"
                               class="contract-id" name="contract-id" value=""></div>
                    <div class="row" style="">
                        <div
                            class="col-xs-12  text-center btn  qms_complete_btn formContractComment">
                            <span><i class="fa  ">Review Comments (&#10004 Mark at appropriate)</i></span>
                        </div>
                    </div>
                    <div class="formContractCommentDiv" style="display: none">
                        <div class="row">
                            <div class="col-sm-12 table-responsive">
                                <table class="editContractTable" style="width: 100%">
                                    <tr>
                                        <th class="contractTableHead">Sr.</th>
                                        <th class="contractTableHead">Contract review check point</th>
                                        <th class="contractTableHead">Requirement stated & accepted</th>
                                        <th class="contractTableHead">Requirement not stated to be clarified</th>
                                        <th class="contractTableHead">Requirement stated and not acceptable to be resolved</th>
                                        <th class="contractTableHead">Not stated / Not acceptable</th>
                                        <th class="contractTableHead">Remark and follow up actions, if any</th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row ">
                        <div class="col-sm-12 form-group float-label-control">
                            <label>Conclusion</label> 
                            <input type="text" class="conclusionEdit" id="conclusionEdit" name="conclusionEdit" placeholder="Conclusion:" >
                        </div>
                    </div>
                    <div class="row " style="padding-top: 10px">
                        <div class="col-sm-12 form-group float-label-control">
                            <label>Name of Reviewer: </label> 
                            <input type="text" class="reviewerEdit" id="reviewerEdit" name="reviewerEdit" placeholder="Name of reviewer" >
                        </div>
                    </div>
                </div>
                <input type="hidden" class="contract-chk-point-hidden-edit" name="contract-chk-point-hidden-edit" value=""> 
                <input type="hidden" class="contract-sr-hidden-edit" name="contract-sr-hidden-edit" value="">
                <input type="hidden" class="contract-header-hidden-edit" name="contract-header-hidden-edit" value=""> 
                <input type="hidden" class="req-state-acpt-hidden-edit" name="req-state-acpt-hidden-edit" value=""> 
                <input type="hidden" class="req-notstate-acpt-hidden-edit" name="req-notstate-acpt-hidden-edit" value=""> 
                <input type="hidden" class="req-state-notacpt-hidden-edit" name="req-state-notacpt-hidden-edit" value=""> 
                <input type="hidden" class="req-notstate-notacpt-hidden-edit" name="req-notstate-notacpt-hidden-edit" value=""> 
                <input type="hidden" class="remark-action-hidden-edit" name="remark-action-hidden-edit" value="">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-info editContractReview">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $('#contractRevDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#contractRevDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#dateOfReview').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true,
            onSelect: function() {
                // Validate on date selection
                validateDateOfReview();
            }
        });
        $("#dateOfReview").keydown(function (e) {
//            e.preventDefault();
        });
        $('.dateOfReview').on('change blur', function() {
            // Validate Date of Review on change/blur
            if ($(this).val() != "") {
                validateDateOfReview();
            }
        });
        $('#dateOfReviewEdit').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#dateOfReviewEdit").keydown(function (e) {
//            e.preventDefault();
        });

        $('#contractIsueDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#contractIsueDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#editContractRevDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#editContractRevDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#editContractIsueDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#editContractIsueDate").keydown(function (e) {
//            e.preventDefault();
        });
    });

    function openInNewTab(url) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function validateContractReview() {
        $isError = false;
        if ($(".dateOfReview").val() == "") {
            $('#dateOfReviewErrorMsg').text("Date can not be empty");
            $('#dateOfReviewError').show();
            $('#dateOfReview').addClass('inputHighlight');
            $isError = true;
        } else if ($(".dateOfReview").val() != "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    reviewDate: $('.dateOfReview').val(),
                    customerid: '<?php echo $_GET["customerid"]; ?>', },
                async: false,
                success: function (data) {
//    	                alert(data);	                    
                    if (data == 1) {
                        $('#dateOfReviewErrorMsg').text("Date should be greater than certification aggrement date");
                        $('#dateOfReviewError').show();
                        $('#dateOfReview').addClass('inputHighlight');
                        $isError = true;
                    } else {
                        $('#dateOfReviewDateErrorMsg').hide();
                        $('#dateOfReviewDate').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#dateOfReviewDateError').hide();
            $('#dateOfReviewDate').removeClass('inputHighlight');
        }
        return $isError;
    }

    // Validation function for Date of Review
    function validateDateOfReview() {
        var isError = false;
        var dateOfReview = $('.dateOfReview').val();
        var customerId = $('.customer-id').val();
        var serviceId = $('.service-id').val();
        
        if (dateOfReview == "") {
            $('#dateOfReviewErrorMsg').text("Date of Review cannot be empty");
            $('#dateOfReviewError').show();
            $('.dateOfReview').addClass('inputHighlight');
            isError = true;
        } else {
            // Check if Date of Review is >= Agreement Date
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    reviewDate: dateOfReview,
                    customerid: customerId
                },
                async: false,
                success: function (data) {
                    if (data == 1) {
                        $('#dateOfReviewErrorMsg').text("Date of Review must be greater than or equal to the Agreement Date");
                        $('#dateOfReviewError').show();
                        $('.dateOfReview').addClass('inputHighlight');
                        isError = true;
                    } else {
                        $('#dateOfReviewError').hide();
                        $('.dateOfReview').removeClass('inputHighlight');
                    }
                },
                error: function (xhr, status, err) {
                    console.log("Error validating date: " + status);
                }
            });
        }
        return isError;
    }

    $(document).ready(function () {

        $(document.body).on('click', '.check1', function () {
//        $('.check1').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check1').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $(document.body).on('click', '.check2', function () {
//            alert("in");
            if ((this).checked) {
                $('.check2').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

$(document.body).on('click', '.check3', function () {
//            alert("in");
            if ((this).checked) {
                $('.check3').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

$(document.body).on('click', '.check4', function () {
//        $('.check4').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check4').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

$(document.body).on('click', '.check5', function () {
//        $('.check5').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check5').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

$(document.body).on('click', '.check6', function () {
//        $('.check6').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check6').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

$(document.body).on('click', '.check7', function () {
//        $('.check7').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check7').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

$(document.body).on('click', '.check8', function () {
//        $('.check8').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check8').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

$(document.body).on('click', '.check9', function () {
//        $('.check9').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check9').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $(document.body).on('click', '.check10', function () {
//        $('.check10').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check10').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
       
       $(document.body).on('click', '.check11', function () {
//        $('.check11').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check11').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $(document.body).on('click', '.check12', function () {
//        $('.check12').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check12').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
       $(document.body).on('click', '.check13', function () { 
//        $('.check13').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check13').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $(document.body).on('click', '.check14', function () {
//        $('.check14').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check14').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $(document.body).on('click', '.check15', function () {
//        $('.check15').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check15').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $(document.body).on('click', '.check16', function () {
//        $('.check16').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check16').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $(document.body).on('click', '.check17', function () {
//        $('.check17').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check17').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $(document.body).on('click', '.check18', function () {
//        $('.check18').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check18').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $(document.body).on('click', '.check19', function () {
//        $('.check19').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check19').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $(document.body).on('click', '.check20', function () {
//        $('.check20').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check20').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $(document.body).on('click', '.check21', function () {
//        $('.check21').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check21').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $(document.body).on('click', '.check22', function () {
//        $('.check22').change(function () {
//            alert("in");
            if ((this).checked) {
                $('.check22').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $(".addContractReview").click(function (e) {
            if (validateContractReview()) {
                e.preventDefault();
                return;
            }
        });

        $('.createContractReview').click(function () {
            var customerId = $(this).attr('data-customer');
            var serviceId = $(this).attr('data-service');
            
            console.log('Contract Review - Opening for Customer:', customerId, 'Service:', serviceId);
            
            $('.service-id').val(serviceId);
            
            // Clear previous values
            $('.contractAgreement').val('');
            $('#contractAgreement').val('');
            
            // Fetch customer data and allotment number EVERY TIME
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customerid: customerId,
                    serviceid: serviceId,
                },
                success: function (data)
                {
                    console.log('Contract Review - Received data:', data);
                    
                    // Populate fields
                    $('.customer-id').val(data['id']);
                    $('.contractCustomer').val(data['customer_name']);
                    $('.customerAddress').val(data['address']);
                    
                    // Get allotment number - ALWAYS fetch fresh from database
                    var allotmentNo = data['unique_id'] || '';
                    console.log('Contract Review - Allotment from server:', allotmentNo);
                    
                    // If unique_id is empty, try to get it from dedicated API
                    if (!allotmentNo || allotmentNo.trim() === '') {
                        console.log('Contract Review - Allotment empty, fetching from get_allotment_only.php');
                        $.ajax({
                            url: "get_allotment_only.php",
                            type: "GET",
                            dataType: "json",
                            async: false, // Make it synchronous to ensure we get the value before showing modal
                            data: {
                                customerid: customerId,
                                serviceid: serviceId,
                            },
                            success: function(response) {
                                console.log('Contract Review - get_allotment_only response:', response);
                                if (response.success && response.allotment_no) {
                                    allotmentNo = response.allotment_no;
                                }
                            }
                        });
                    }
                    
                    // CLIENT-SIDE RC SUFFIX ENFORCEMENT FOR CONTRACT REVIEW
                    // Check if this is an RC service and ensure Rc-1 suffix is present
                    if (allotmentNo && allotmentNo.trim() !== '') {
                        // Check if service is RC type
                        $.ajax({
                            url: "checkServiceType.php",
                            type: "GET",
                            dataType: "json",
                            async: false,
                            data: {
                                serviceid: serviceId
                            },
                            success: function(serviceData) {
                                console.log('Contract Review - Service type check:', serviceData);
                                if (serviceData.service_type === 'RC') {
                                    // Remove any existing RC suffix first
                                    var baseAllotment = allotmentNo.replace(/\s+Rc-\d+$/i, '').replace(/-RC-\d+$/i, '');
                                    // Append Rc-1 suffix
                                    allotmentNo = baseAllotment + ' Rc-1';
                                    console.log('Contract Review - ✓ RC Service: Applied Rc-1 suffix:', allotmentNo);
                                }
                            },
                            error: function() {
                                console.log('Contract Review - Service type check failed, using allotment as-is');
                            }
                        });
                    }
                    
                    // Set Contract Agreement field with the allotment number
                    if (allotmentNo && allotmentNo.trim() !== '') {
                        console.log('Contract Review - Final allotment number:', allotmentNo);
                        $('.contractAgreement').val(allotmentNo);
                        $('#contractAgreement').val(allotmentNo);
                        $('input[name="contractAgreement"]').val(allotmentNo);
                        
                        // Show backup display
                        $('#currentAllotment').text(allotmentNo);
                        $('#allotmentBackup').show();
                    } else {
                        console.warn('Contract Review - No allotment number found!');
                    }
                    
                    // Show modal
                    $('#contractReview').modal('show');
                },
                error: function (data, status, err)
                {
                    console.error('Contract Review - Error loading customer data:', status, err);
                    // Error loading customer data
                    $('#contractReview').modal('show');
                }
            });
        });

        $('.updateContractReview').click(function () {
//            alert($(this).attr('data-contract'));
//            alert($(this).attr('data-customer'));data-service
            var customerId = $(this).attr('data-customer');
            var contractId = $(this).attr('data-contract');
            var serviceId = $(this).attr('data-service');
            
            console.log('Contract Review Edit - Opening for Customer:', customerId, 'Service:', serviceId, 'Contract:', contractId);
            
            $('.edit-customer-id').val(customerId);
            $('.contract-id').val(contractId);
            $('.edit-service-id').val(serviceId);
            
            // Clear previous agreement number
            $('.editContractAgreement').val('');
            
            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    customerId: customerId,
                    contractId: contractId,
                },
                success: function (data)
                {
                    console.log('Contract Review Edit - Received data:', data);
                    
                    $('.editContractCustomer').val(data[0]['customer_name']);
                    $('.editCustomerAddress').val(data[0]['address']);
                    
                    // ALWAYS fetch fresh agreement number from database
                    var agreementNo = data[0]['unique_id'] || '';
                    console.log('Contract Review Edit - Agreement from contractDetails:', agreementNo);
                    
                    // If empty, fetch from getClientInfo or get_allotment_only
                    if (!agreementNo || agreementNo.trim() === '') {
                        console.log('Contract Review Edit - Agreement empty, fetching from getClientInfo');
                        // Try getClientInfo first
                        $.ajax({
                            url: "getClientInfo.php",
                            type: "GET",
                            dataType: "json",
                            async: false,
                            data: {
                                customerid: customerId,
                                serviceid: serviceId,
                            },
                            success: function(clientData) {
                                agreementNo = clientData['unique_id'] || '';
                                console.log('Contract Review Edit - Agreement from getClientInfo:', agreementNo);
                            }
                        });
                        
                        // If still empty, try get_allotment_only
                        if (!agreementNo || agreementNo.trim() === '') {
                            console.log('Contract Review Edit - Still empty, trying get_allotment_only');
                            $.ajax({
                                url: "get_allotment_only.php",
                                type: "GET",
                                dataType: "json",
                                async: false,
                                data: {
                                    customerid: customerId,
                                    serviceid: serviceId,
                                },
                                success: function(response) {
                                    console.log('Contract Review Edit - get_allotment_only response:', response);
                                    if (response.success && response.allotment_no) {
                                        agreementNo = response.allotment_no;
                                    }
                                }
                            });
                        }
                    }
                    
                    // CLIENT-SIDE RC SUFFIX ENFORCEMENT FOR CONTRACT REVIEW EDIT
                    // Check if this is an RC service and ensure Rc-1 suffix is present
                    if (agreementNo && agreementNo.trim() !== '') {
                        // Check if service is RC type
                        $.ajax({
                            url: "checkServiceType.php",
                            type: "GET",
                            dataType: "json",
                            async: false,
                            data: {
                                serviceid: serviceId
                            },
                            success: function(serviceData) {
                                console.log('Contract Review Edit - Service type check:', serviceData);
                                if (serviceData.service_type === 'RC') {
                                    // Remove any existing RC suffix first
                                    var baseAllotment = agreementNo.replace(/\s+Rc-\d+$/i, '').replace(/-RC-\d+$/i, '');
                                    // Append Rc-1 suffix
                                    agreementNo = baseAllotment + ' Rc-1';
                                    console.log('Contract Review Edit - ✓ RC Service: Applied Rc-1 suffix:', agreementNo);
                                }
                            },
                            error: function() {
                                console.log('Contract Review Edit - Service type check failed, using agreement as-is');
                            }
                        });
                    }
                    
                    // Set the agreement number
                    console.log('Contract Review Edit - Final agreement number:', agreementNo);
                    $('.editContractAgreement').val(agreementNo);

                    $('.conclusionEdit').val(data[2]['conclusion']); 
                    $('.reviewerEdit').val(data[2]['reviewer']);
                    $('.editContractFormNo').val(data[2]['contract_form']);
                    $('.editContractRev').val(data[2]['contract_rev']);
                    if (data[2]['review_date'] == "" || data[2]['review_date'] == null)
                        $reviewDate = "";
                    else
                        $reviewDate = moment(data[2]['review_date']).format('DD.MM.YYYY');
                    $('.dateOfReviewEdit').val($reviewDate);

                    if (data[2]['contract_revdate'] == "" || data[2]['contract_revdate'] == null)
                        $appdate = "";
                    else
                        $appdate = moment(data[2]['contract_revdate']).format('DD.MM.YYYY');
                    $('.editContractRevDate').val($appdate);
                    $('.editContractIsue').val(data[2]['contract_isue']);
                    if (data[2]['contract_isuedate'] == "" || data[2]['contract_isuedate'] == null)
                        $isuedate = "";
                    else
                        $isuedate = moment(data[2]['contract_isuedate']).format('DD.MM.YYYY');
                    $('.editContractIsueDate').val($isuedate);

                    $('.appendClass').empty();
                    
                    var count = 1;
                    for (var key in data[1]) {
                        var srno = data[1][key]['sr_no'];
                        var contract_header = data[1][key]['contract_header'];
                        var req_stated = data[1][key]['req_stated'];
                        var req_notstated = data[1][key]['req_notstated'];
                        var req_stat_notacpt = data[1][key]['req_stat_notacpt'];
                        var req_notstat_notacpt = data[1][key]['req_notstat_notacpt'];
                        var remark = data[1][key]['remark'];

                        console.log(contract_header)
                        if (srno != null && srno != "" && srno == 1) {
                            $('.editContractTable').append('<tr class="appendClass"><td colspan=9 class="contractTableHead"><strong>' + contract_header + '</strong></td></tr>');
                        }
                        $checkboxVal1 = "";
                        if (req_stated != null && req_stated != "" && req_stated == 'true') {
                            $checkboxVal1 = "checked";
                        } else {
                            $checkboxVal1 = "";
                        }
                        $checkboxVal2 = "";
                        if (req_notstated != null && req_notstated != "" && req_notstated == 'true') {
                            $checkboxVal2 = "checked";
                        } else {
                            $checkboxVal2 = "";
                        }
                        $checkboxVal3 = "";
                        if (req_stat_notacpt != null && req_stat_notacpt != "" && req_stat_notacpt == 'true') {
                            $checkboxVal3 = "checked";
                        } else {
                            $checkboxVal3 = "";
                        }
                        $checkboxVal4 = "";
                        if (req_notstat_notacpt != null && req_notstat_notacpt != "" && req_notstat_notacpt == 'true') {
                            $checkboxVal4 = "checked";
                        } else {
                            $checkboxVal4 = "";
                        }

                        $('.editContractTable').append('<tr class="appendClass"><td class="contractTableHead">' + data[1][key]['sr_no'] + '</td>\n\
                                                        <td class="contractTableHead">' + data[1][key]['contract_chk_point'] + '</td>\n\
                                                        <td class="contractTableHead" style="text-align: center"><input class="req-state-acpt-edit check'+count+'" type="checkbox" ' + $checkboxVal1 + '></td>\n\
                                                        <td class="contractTableHead" style="text-align: center"><input class="req-notstate-acpt-edit check'+count+'" type="checkbox" ' + $checkboxVal2 + '></td>\n\
                                                        <td class="contractTableHead" style="text-align: center"><input class="req-state-notacpt-edit check'+count+'" type="checkbox" ' + $checkboxVal3 + '></td>\n\
                                                        <td class="contractTableHead" style="text-align: center"><input class="req-notstate-notacpt-edit check'+count+'" type="checkbox" ' + $checkboxVal4 + '></td>\n\
                                                        <td class="contractTableHead" style="text-align: center"><input type="text" class="remark-action-edit" name="remark-action-edit" value="' + remark + '"></td>\n\
                                                        <input type="hidden" class="contract-chk-point-edit" name="contract-chk-point-edit" value="' + data[1][key]['contract_chk_point'] + '">\n\
                                                        <input type="hidden" class="contract-sr-edit" name="contract-sr-edit" value="' + data[1][key]['sr_no'] + '">\n\
                                                        <input type="hidden" class="contract-header-edit" name="contract-header-edit" value="' + data[1][key]['contract_header'] + '"></tr>');
                                                        
                        count++;
                    }
                    $('input[type="text"]').trigger('change');
//                    $('textarea').trigger('change');
                    $('#editContractReview').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });


        $('.formContractComment').click(function () {
            $('.formContractCommentDiv').slideToggle('slow');
        });

        $('.formContractReview').click(function () {
            $('.formContractReviewDiv').slideToggle('slow');
        });

        $('.addContractReview').click(function () {

            var reqStateAcpt = [];
            $('.req-state-acpt').each(function () {
                reqStateAcpt.push($(this).prop('checked'));
            });
            $('.req-state-acpt-hidden').val(reqStateAcpt.join("~"));

            var reqNotStateAcpt = [];
            $('.req-notstate-acpt').each(function () {
                reqNotStateAcpt.push($(this).prop('checked'));
            });
            $('.req-notstate-acpt-hidden').val(reqNotStateAcpt.join("~"));

            var reqStateNotAcpt = [];
            $('.req-state-notacpt').each(function () {
                reqStateNotAcpt.push($(this).prop('checked'));
            });
            $('.req-state-notacpt-hidden').val(reqStateNotAcpt.join("~"));

            var reqNotStateNotAcpt = [];
            $('.req-notstate-notacpt').each(function () {
                reqNotStateNotAcpt.push($(this).prop('checked'));
            });
            $('.req-notstate-notacpt-hidden').val(reqNotStateNotAcpt.join("~"));

            var remarkAction = [];
            $('.remark-action').each(function () {
                remarkAction.push($(this).val());
            });
            $('.remark-action-hidden').val(remarkAction.join("~"));

            var contractChkPoint = [];
            $(".contract-chk-point").each(function () {
                contractChkPoint.push($(this).val());
            });
            $('.contract-chk-point-hidden').val(contractChkPoint.join("~"));

            var contractSr = [];
            $(".contract-sr").each(function () {
                contractSr.push($(this).val());
            });
            $('.contract-sr-hidden').val(contractSr.join("~"));

            var contractHeader = [];
            $(".contract-header").each(function () {
                contractHeader.push($(this).val());
            });
            $('.contract-header-hidden').val(contractHeader.join("~"));

        });

        $('.addContractReviewPdf').click(function () {
            var reqStateAcpt = [];
            $('.req-state-acpt').each(function () {
                reqStateAcpt.push($(this).prop('checked'));
            });
            $('.req-state-acpt-hidden').val(reqStateAcpt.join("~"));

            var reqNotStateAcpt = [];
            $('.req-notstate-acpt').each(function () {
                reqNotStateAcpt.push($(this).prop('checked'));
            });
            $('.req-notstate-acpt-hidden').val(reqNotStateAcpt.join("~"));

            var reqStateNotAcpt = [];
            $('.req-state-notacpt').each(function () {
                reqStateNotAcpt.push($(this).prop('checked'));
            });
            $('.req-state-notacpt-hidden').val(reqStateNotAcpt.join("~"));

            var reqNotStateNotAcpt = [];
            $('.req-notstate-notacpt').each(function () {
                reqNotStateNotAcpt.push($(this).prop('checked'));
            });
            $('.req-notstate-notacpt-hidden').val(reqNotStateNotAcpt.join("~"));

            var remarkAction = [];
            $('.remark-action').each(function () {
                remarkAction.push($(this).prop('checked'));
            });
            $('.remark-action-hidden').val(remarkAction.join("~"));

            var contractChkPoint = [];
            $(".contract-chk-point").each(function () {
                contractChkPoint.push($(this).val());
            });
            $('.contract-chk-point-hidden').val(contractChkPoint.join("~"));

            var contractSr = [];
            $(".contract-sr").each(function () {
                contractSr.push($(this).val());
            });
            $('.contract-sr-hidden').val(contractSr.join("~"));

            var contractHeader = [];
            $(".contract-header").each(function () {
                contractHeader.push($(this).val());
            });
            $('.contract-header-hidden').val(contractHeader.join("~"));

//            openInNewTab('addContractReview.php');
        });

        $('.editContractReview').click(function () {
            var reqStateAcptEdit = [];
            $('.req-state-acpt-edit').each(function () {
                reqStateAcptEdit.push($(this).prop('checked'));
            });
            $('.req-state-acpt-hidden-edit').val(reqStateAcptEdit.join("~"));

            var reqNotStateAcptEdit = [];
            $('.req-notstate-acpt-edit').each(function () {
                reqNotStateAcptEdit.push($(this).prop('checked'));
            });
            $('.req-notstate-acpt-hidden-edit').val(reqNotStateAcptEdit.join("~"));

            var reqStateNotAcptEdit = [];
            $('.req-state-notacpt-edit').each(function () {
                reqStateNotAcptEdit.push($(this).prop('checked'));
            });
            $('.req-state-notacpt-hidden-edit').val(reqStateNotAcptEdit.join("~"));

            var reqNotStateNotAcptEdit = [];
            $('.req-notstate-notacpt-edit').each(function () {
                reqNotStateNotAcptEdit.push($(this).prop('checked'));
            });
            $('.req-notstate-notacpt-hidden-edit').val(reqNotStateNotAcptEdit.join("~"));

            var remarkActionEdit = [];
            $('.remark-action-edit').each(function () {
                remarkActionEdit.push($(this).val());
            });
            $('.remark-action-hidden-edit').val(remarkActionEdit.join("~"));

            var contractChkPointEdit = [];
            $(".contract-chk-point-edit").each(function () {
                contractChkPointEdit.push($(this).val());
            });
            $('.contract-chk-point-hidden-edit').val(contractChkPointEdit.join("~"));

            var contractSrEdit = [];
            $(".contract-sr-edit").each(function () {
                contractSrEdit.push($(this).val());
            });
            $('.contract-sr-hidden-edit').val(contractSrEdit.join("~"));

            var contractHeaderEdit = [];
            $(".contract-header-edit").each(function () {
                contractHeaderEdit.push($(this).val());
            });
            $('.contract-header-hidden-edit').val(contractHeaderEdit.join("~"));
        });

    });
</script>

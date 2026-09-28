<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

//  legal ststus
$legalStatus = "SELECT * FROM legal_ststus";
$statement = $db->prepare($legalStatus);
$statement->execute();
$legalStatusList = $statement->fetchAll();
 
//  Standards 
$standards = "SELECT * FROM standards";
$statement = $db->prepare($standards);
$statement->execute();
$standardsList = $statement->fetchAll();

//  nace code
$naceCodes = "SELECT * FROM nace_codes";
$statement = $db->prepare($naceCodes);
$statement->execute();
$naceCodeList = $statement->fetchAll();

//  IAF code
$iafCodes = "SELECT * FROM iaf_codes";
$statement = $db->prepare($iafCodes);
$statement->execute();
$iafList = $statement->fetchAll();

//  accreditation
$accreditation = "SELECT * FROM accreditation";
$statement = $db->prepare($accreditation);
$statement->execute();
$accreditationList = $statement->fetchAll();

// Reviewers (auditors configured as reviewers in Master Creator)
require_once __DIR__ . '/include/reviewer_conflict_helper.php';
$reviewersList = aitf_get_all_reviewers($db);
?>
<script src="js/date-chronology-validation.js?v=2"></script>
<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="auditApplication" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                Audit Application
            </div>  
            <form action="auditApplication.php" method="post">
                <div class="modal-body" >
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formAuditApplication">
                            <span><i class="fa  ">Input (From Audit application- F 7.01)</i></span>
                        </div>
                    </div>
                    <div class="auditApp">                        
                        <div class="row" style="">                            
                            <div class="col-xs-12">
                                <span ><strong>Application For:</strong></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12" >
                                <span>
                                    <input class="applicationfor" type="checkbox" name="applicationfor" value="1. First certification"> 1. First certification &emsp;
                                    <input class="applicationfor" type="checkbox" name="applicationfor" value="2. Recertification"> 2. Recertification &emsp;
                                    <input class="applicationfor" type="checkbox" name="applicationfor" value="3. Extension"> 3. Extension &emsp;
                                    <input class="applicationfor" type="checkbox" name="applicationfor" value="4. Transfer of certificate"> 4. Transfer of certificate
                                </span>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-sm-6 ">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Application Date</label>
                                        <input type="text" class="applicationDate appDate" id="applicationDate" name="applicationDate" placeholder="Application Date" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <div id="invDateError" style="display: ">
                                            <small><span id="invDateErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Application Received Date</label>
                                        <input type="text" class="applicationRcvDate appDate" id="applicationRcvDate" name="applicationRcvDate" placeholder="Application Received Date" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <div id="rcvDateError" style="display: none">
                                            <small><span id="rcvDateErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Name of the Organization</label>
                                <input type="text" class="orgName" id="orgName" name="orgName" placeholder="Name of the Organization" required>
                            </div>
                        </div>
                        <div class="row">                                
                            <div class="col-sm-3 form-group float-label-control">                                
                                <label>Legal Status</label>
                                <select name="legalStatus" class="form-control legalStatus" id="legalStatus" required>
                                    <option value="">Select </option>
                                    <?php foreach ($legalStatusList as $status) { ?>
                                        <option value="<?php echo $status["status"] ?>"><?php echo $status["status"] ?></option>
                                    <?php } ?>                                    
                                </select>
                            </div>
                            <div class="col-sm-3 " >
                                <div class="row otherLegalStatus form-group float-label-control" style="display:none">
                                    <label>Other Legal Status</label>
                                    <input type="text" class="otherStatus" id="otherStatus" name="otherStatus" placeholder="Other Legal Status" >
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Industry Classification</label>
                                <select name="industryClassify" class="form-control industryClassify" id="industryClassify" required>
                                    <option value="">Select </option>
                                    <option value="Manufacture">Manufacture</option>
                                    <option value="service provider">service provider</option>
                                </select>
                                <!--<input type="text" class="industryClassify" id="industryClassify" name="industryClassify" placeholder="Industry Classification" required>-->
                            </div>
                        </div>
                        <div class="row">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Part of large group</label>
                                <select name="legalPart" class="form-control legalPart" id="legalPart" required>
                                    <option value="">Select </option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>No. site/s cover under audit :</label>
                                <input type="number" min="1" class="locationUnderAudit" id="locationUnderAudit" name="locationUnderAudit" placeholder="No. site/s cover under audit:" required>
                            </div>
                        </div>
                        <div class="row">                                
                            <div class="col-sm-12 form-group float-label-control">
                                <label>No Employees: Permanent/Temporary/Contract/Seasonal</label>
                                <input type="number" min="1" class="employees" id="employees" name="employees" placeholder="No Employees: Permanent/Temporary/Contract/Seasonal" required>
                            </div>                            
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>No. of Shifts</label>
                                <input type="text" class="shifts" id="shifts" name="shifts" placeholder="No. of Shifts" required>
                            </div>
                        </div>
                        <div class="row">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Products / Service</label>
                                <input type="text" class="product" id="product" name="product" placeholder="Products / Service" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Externally provided processes: </label>
                                <input type="text" class="processes" id="processes" name="processes" placeholder="Externally provided processes: " required>
                            </div>
                        </div>
<div class="row basiccert" style="padding-top: 10px">
    <div class="col-sm-6 form-group float-label-control">
        <label>Certification required for standard: </label>
        <select name="certificationStandard[]" class="form-control certificationStandard" required>
            <option value="">Select Assessment Standard</option>
            <?php foreach ($standardsList as $standards) { ?>
                <option value="<?php echo $standards["standards_name"] ?>"><?php echo $standards["standards_name"] ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-sm-6 form-group float-label-control">
        <label>Accreditation Requirement</label>
        <select name="accreditation[]" class="form-control accreditation" required>
            <option value="">Select Accreditation</option>
            <?php foreach ($accreditationList as $accreditation) { ?>
                <option value="<?php echo $accreditation["accreditation_name"] ?>"><?php echo $accreditation["accreditation_name"] ?></option>
            <?php } ?>
        </select>
    </div></div>
    <div class="row">
        <div class="col-sm-12 text-right">
            <a class="addNewSection1" title="Add New Section"><i class="fa fa-plus"></i></a>
        </div>
    </div>
                        <div class="row">                                
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Consultancy services or in-house training was performed by whom and period of performed?</label>
                                <input type="text" class="consultancyServices" id="consultancyServices" name="consultancyServices" placeholder="Consultancy services or in-house training was performed by whom and period of performed?" required>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn generalAnalyse">
                            <span><i class="fa  ">General analysis & requirement</i></span>
                        </div>
                    </div>
                    <div class="generalAnalyseDiv" style="display:none">
                        <div class="row" style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Non Applicability of standard: </label>
                                <input type="text" class="exclusionClauses" id="exclusionClauses" name="exclusionClauses" placeholder="Non Applicability of standard: " required>
                            </div>
                            <!--                            <div class="col-sm-6 form-group float-label-control">
                                                            <label>Technical Area</label>
                                                            <input type="text" class="technicalArea" id="technicalArea" name="technicalArea" placeholder="Technical Area" required>
                                                        </div>-->
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Any Legal Obligations</label>
                                <input type="text" class="legalObligations" id="legalObligations" name="legalObligations" placeholder="Any Legal Obligations" required>
                            </div>
                        </div>
                        <div class="row">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Conflict of Interest</label>
                                <input type="text" class="conflictInterest" id="conflictInterest" name="conflictInterest" placeholder="Conflict of Interest" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Statutory / Regulatory requirement</label>
                                <input type="text" class="statutoryRequirement" id="statutoryRequirement" name="statutoryRequirement" placeholder="Statutory / Regulatory requirement" required>
                            </div>
                        </div>
                        <div class="row">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Customer specific requirement</label>
                                <input type="text" class="customerRequirement" id="customerRequirement" name="customerRequirement" placeholder="Customer specific requirement" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control" >
                                <label>Organization mfg/service risk involved(high/medium/low)class </label>
                                <select name="organizationService" class="form-control organizationService" id="organizationService" required style="margin-top: 20px;">
                                    <option value="">Select </option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div> 
                        </div>
                        
                        
                        
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Any other requirement</label>
                                <input type="text" class="otherRequirement" id="otherRequirement" name="otherRequirement" placeholder="Any other requirement" required>
                            </div>
                        </div>
                             </div>
                        <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn generalAnalyse0">
                            <span><i class="fa  ">Annexture 1 - QMS (Basic Information and also applicable for Quality Management Certification)</i></span>
                        </div>
                    </div>
                    <div class="generalAnalyseDiv0" style="display:none">
                        <div class="row" style="padding-top: 20px">
                        <div class="col-sm-12 form-group float-label-control mainProducts">
                                <label>Main products </label>
                                <input type="text" class="mainProducts" id="mainProducts" name="mainProducts" placeholder="Main products" >
                            </div>
                        </div>
                        
                        <div class="row">
                        <div class="col-sm-12 form-group float-label-control mainRawMaterial">
                                <label>Main raw materials used or processed</label>
                                <input type="text" class="mainRawMaterial" id="mainRawMaterial" name="mainRawMaterial" placeholder="Main raw materials used or processed" >
                            </div>
                        </div>
                        <div class="row">
                        <div class="col-sm-12 form-group float-label-control operationInvolved">
                                <label>Operations involved in the service / manufacturing of your product</label>
                                <input type="text" class="operationInvolved" id="operationInvolved" name="operationInvolved" placeholder="Operations involved in the service / manufacturing of your product" >
                            </div>
                        </div>
                        <div class="row">
                        <div class="col-sm-12 form-group float-label-control sourcesProduct">
                                <label>Processes which are provided by external sources related to the product / service</label>
                                <input type="text" class="sourcesProduct" id="sourcesProduct" name="sourcesProduct" placeholder="Processes which are provided by external sources related to the product / service" >
                            </div>
                        </div>
                        <div class="row">
                        <div class="col-sm-12 form-group float-label-control complicatedLogistics">
                                <label>Any complicated logistics involvingmore than one building or location where workis carriedout</label>
                                <input type="text" class="complicatedLogistics" id="complicatedLogistics" name="complicatedLogistics" placeholder="Any complicated logistics involvingmore than one building or location where workis carriedout" >
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
    <div class="col-xs-12  text-center btn  qms_complete_btn generalAnalyseI">
        <span><i class="fa  ">LEVEL OF INTEGRATION IN CASE OF IMS</i></span>
    </div>
</div>
<div class="generalAnalyseDivI" style="display:none">
<div class="row" style="padding-top: 20px">
        <div class="col-sm-12"  >
            <span ><strong>Applicable  :</strong></span>
            <span>
            <input type="radio" id="applicationforYes0" name="applicationfor0" value="Yes"> Yes
            <input type="radio" id="applicationforNo0" name="applicationfor0" value="No" checked> No
            </span>
        </div>
    </div>
    <div class="row" style="padding-top: 20px">
        <div class="col-sm-12 form-group float-label-control nointegration">
            <label>No integration</label>
            <input type="text" class="nointegration" id="nointegration" name="nointegration" placeholder="No integration" >
        </div>
    </div>
    
    <div class="row">
        <div class="col-sm-12 form-group float-label-control managementreview">
            <label>An integrated approach to internal audits and management review</label>
            <input type="text" class="managementreview" id="managementreview" name="managementreview" placeholder="An integrated approach to internal audits and management review" >
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 form-group float-label-control integratedmanagement">
            <label>Integrated management system documentation to a good level of development together with an integrated approach to policy and objectives</label>
            <input type="text" class="integratedmanagement" id="integratedmanagement" name="integratedmanagement" placeholder="Integrated management system documentation to a good level of development together with an integrated approach to policy and objectives" >
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 form-group float-label-control integratedapproach">
            <label>Integrated approach towards management system planning and processes</label>
            <input type="text" class="integratedapproach" id="integratedapproach" name="integratedapproach" placeholder="Integrated approach towards management system planning and processes" >
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 form-group float-label-control integratedresponsibilities">
            <label>Integrated approach towards improvement mechanisms together with unified management commitment and responsibilities</label>
            <input type="text" class="integratedresponsibilities" id="integratedresponsibilities" name="integratedresponsibilities" placeholder="Integrated approach towards improvement mechanisms together with unified management commitment and responsibilities" >
        </div>
    </div>
</div>


                <!-- EMS -->
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn generalAnalyse1">
                            <span><i class="fa  ">ANNEXTURE 2 – EMS (APPLICABLE FOR EMS CERTIFICATION)</i></span>
                        </div>
                    </div>
                    <div class="generalAnalyseDiv1" style="display:none">
                    <div class="row" style="padding-top: 20px">
                            <div class="col-sm-12"  >
                                <span ><strong>Applicable  :</strong></span>
                                <span>
                                <input type="radio" id="applicationforYes2" name="applicationfor2" value="Yes"> Yes
                                <input type="radio" id="applicationforNo2" name="applicationfor2" value="No" checked> No
                                </span>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 20px">
                            <div class="col-sm-12 form-group float-label-control environmentalClearanceC">
                                <label>Any Statutory Regulatory Requirements related to Environmental clearance for the product manufactured by organization</label>
                                <input type="text" class="environmentalClearance" id="environmentalClearance" name="environmentalClearance" placeholder="Any Statutory Regulatory Requirements related to Environmental clearance for the product manufactured by organization" >
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control hazardousMaterialsC">
                                <label>Any Hazardous Raw / Processing materials used</label>
                                <input type="text" class="hazardousMaterials" id="hazardousMaterials" name="hazardousMaterials" placeholder="Any Hazardous Raw / Processing materials used" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control adverseImpactC">
                                <label>Any adverse impact identified on the environment due to operations involved in the service / manufacturing processes</label>
                                <input type="text" class="adverseImpact" id="adverseImpact" name="adverseImpact" placeholder="Any adverse impact identified on the environment due to operations involved in the service / manufacturing processes" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control sensitiveLocationC">
                                <label>Organisation’slocation(s) particularly environmentallysensitivetoany conditions</label>
                                <input type="text" class="sensitiveLocation" id="sensitiveLocation" name="sensitiveLocation" placeholder="Organisation’slocation(s) particularly environmentallysensitivetoany conditions" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control archaeologicalSiteC">
                                <label>Any nearby e.g. archaeological site, listedbuilding,adjacenttosensitivelocationsetc</label>
                                <input type="text" class="archaeologicalSite" id="archaeologicalSite" name="archaeologicalSite" placeholder="Any nearby e.g. archaeological site, listedbuilding,adjacenttosensitivelocationsetc" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control adverseHistoryC">
                                <label>Any history of adverseperformance or operations where anemergency condition would result in asignificantenvironmentalevent</label>
                                <input type="text" class="adverseHistory" id="adverseHistory" name="adverseHistory" placeholder="Any history of adverseperformance or operations where anemergency condition would result in asignificantenvironmentalevent" >
                            </div>
                        </div>
                    </div>

                <!-- EMS Closed -->


                <!-- OHSMS -->
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn generalAnalyse2">
                            <span><i class="fa">ANNEXTURE 3 – OHSMS (APPLICABLE FOR OHSMS CERTIFICATION)</i></span>
                        </div>
                    </div>
                    <div class="generalAnalyseDiv2" style="display:none">


                        <div class="row" style="padding-top: 20px">
                            <div class="col-sm-12"  >
                                <span ><strong>Applicable  :</strong></span>
                                <span>
                                <input type="radio" id="applicationforYes3" name="applicationfor3" value="Yes"> Yes
                                <input type="radio" id="applicationforNo3" name="applicationfor3" value="No" checked> No
                                </span>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 20px" >
                            <div class="col-sm-12 form-group float-label-control statutoryHealthandsafetyC" >
                                <label>Statutory Regulatory Requirements related to Health and safety for the product manufactured by organization</label>
                                <input type="text" class="statutoryHealthandsafety" id="statutoryHealthandsafety" name="statutoryHealthandsafety" placeholder="Statutory Regulatory Requirements related to Health and safety for the product manufactured by organization" >
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control hazardousDangerousSubstancesC" >
                                <label>Any Hazardous raw / Processing material, dangerous substances used/ Present at the site</label>
                                <input type="text" class="hazardousDangerousSubstances" id="hazardousDangerousSubstances" name="hazardousDangerousSubstances" placeholder="Any Hazardous raw / Processing material, dangerous substances used/ Present at the site" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control healthAndSafetyRisksC">
                                <label>Identify the personnel’s health and safety risks due to operations involved in the service / manufacturing Processes</label>
                                <input type="text" class="healthAndSafetyRisks" id="healthAndSafetyRisks" name="healthAndSafetyRisks" placeholder="Identify the personnel’s health and safety risks due to operations involved in the service / manufacturing Processes" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control temporarysitesC">
                                <label>Are there activities carried out on site,temporary sites or sites notunder your control</label>
                                <input type="text" class="temporarysites" id="temporarysites" name="temporarysites" placeholder="Are there activities carried out on site,temporary sites or sites notunder your control" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control accidentsRatesC">
                                <label>Do you have higher than industry average rates of accidents,diseaseor absence</label>
                                <input type="text" class="accidentsRates" id="accidentsRates" name="accidentsRates" placeholder="Do you have higher than industry average rates of accidents,diseaseorabsence" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control SitesPublicPresentC" >
                                <label>Do you have members of the public present atyour sites in large numbers? (e.g. hospitals,schools, publictransporthubs)</label>
                                <input type="text" class="SitesPublicPresent" id="SitesPublicPresent" name="SitesPublicPresent" placeholder="Do you have members of the public present atyour sites in large numbers? (e.g. hospitals,schools, public transporthubs)" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control governmentalRegulatorsC" >
                                <label>Are you subject to mandatory visits (audits) by governmental regulators</label>
                                <input type="text" class="governmentalRegulators" id="governmentalRegulators" name="governmentalRegulators" placeholder="Are you subject to mandatory visits (audits) by governmental regulators" >
                            </div>
                        </div>
                    </div>

                <!-- OHSMS Closed -->
 

                <!-- Others -->
                <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn generalAnalyse4">
                            <span><i class="fa">ANNEXTURE 4 –  (ANY Other Standerd/s)</i></span>
                        </div>
                    </div>
                    <div class="generalAnalyseDiv4" style="display:none">


                        <div class="row" style="padding-top: 20px">
                            <div class="col-sm-12"  >
                                <span ><strong>Applicable  :</strong></span>
                                <span>
                                    <input class="applicationfor4" id="applicationforYes4"   type="radio" name="applicationfor4" value="Yes"> Yes &emsp;
                                    <input class="applicationfor4" id="applicationforNo4"   type="radio" name="applicationfor4" checked value="No" > No &emsp;
                                </span>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 20px" >
                            <div class="col-sm-12 form-group float-label-control otherStanderedNameC" >
                                <label>Specify the standerd/s</label>
                                <input type="text" class="otherStanderedName" id="otherStanderedName" name="otherStanderedName" placeholder="Specify the standerd/s" >
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control otherCheckPoint1C" >
                                <label>Checkpoint 1</label>
                                <input type="text" class="otherCheckPoint1" id="otherCheckPoint1" name="otherCheckPoint1" placeholder="Checkpoint 1" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control otherCheckPoint2C">
                                <label>Checkpoint 2</label>
                                <input type="text" class="otherCheckPoint2" id="otherCheckPoint2" name="otherCheckPoint2" placeholder="Checkpoint 2" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control otherCheckPoint3C">
                                <label>Checkpoint 3</label>
                                <input type="text" class="otherCheckPoint3" id="otherCheckPoint3" name="otherCheckPoint3" placeholder="Checkpoint 3" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control otherCheckPoint4C">
                                <label>Checkpoint 4</label>
                                <input type="text" class="otherCheckPoint4" id="otherCheckPoint4" name="otherCheckPoint4" placeholder="Checkpoint 4" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control otherCheckPoint5C" >
                                <label>Checkpoint 5</label>
                                <input type="text" class="otherCheckPoint5" id="otherCheckPoint5" name="otherCheckPoint5" placeholder="Checkpoint 5" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control otherCheckPoint6C" >
                                <label>Checkpoint 6</label>
                                <input type="text" class="otherCheckPoint6" id="otherCheckPoint6" name="otherCheckPoint6" placeholder="Checkpoint 6" >
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn fesibilAnalysis">
                            <span><i class="fa  ">Feasibility Analysis</i></span>
                        </div>
                    </div>
                    <div class="fesibilAnalysisDiv" style="display:none">
                        <div class="row" style="">
                            <div class="col-xs-12  ">
                                <span style="text-decoration: underline;">Feasibility & Risk Analysis Considerations</span>
                            </div>
                        </div>                        
                        <br>
                        <div class="row" style="">
                            <div class="col-xs-1">
                                <span >Sr.</span>
                            </div>
                            <div class="col-xs-9  ">
                                <span >CONSIDERATION:</span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-1">
                                <span >1.</span>
                            </div>
                            <div class="col-xs-9  ">
                                <span >Is AITF having adequate capacity for providing assessment & certification service.</span>
                            </div>
                            <div class="col-sm-2 form-group float-label-control">
                                <label></label>
                                <select name="assessmentCertification" class="form-control assessmentCertification" id="assessmentCertification" required >
                                    <option value="">Select</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-1">
                                <span >2.</span>
                            </div>
                            <div class="col-xs-9  ">
                                <span >Does AITF having adequate resources to render the service.</span>
                            </div>
                            <div class="col-sm-2 form-group float-label-control">
                                <label></label>
                                <select name="adequateResource" class="form-control adequateResource" id="adequateResource" required>
                                    <option value="">Select</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-1">
                                <span >3.</span>
                            </div>
                            <div class="col-xs-9  ">
                                <span >Is product/service (Scope) adequately defined in application to enable feasibility.</span>
                            </div>
                            <div class="col-sm-2 form-group float-label-control">
                                <label></label>
                                <select name="productService" class="form-control productService" id="productService" required>
                                    <option value="">Select</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-1">
                                <span >4.</span>
                            </div>
                            <div class="col-xs-9  ">
                                <span >Within scope.</span>
                            </div>
                            <div class="col-sm-2 form-group float-label-control">
                                <label></label>
                                <select name="scope" class="form-control scope" id="scope" required>
                                    <option value="">Select</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-1">
                                <span>5.</span>
                            </div>
                            <div class="col-xs-9  ">
                                <span >AITF having scope auditor including competent team for the certification decision.</span>
                            </div>
                            <div class="col-sm-2 form-group float-label-control">
                                <label></label>
                                <select name="competentTeam" class="form-control competentTeam" id="competentTeam" required>
                                    <option value="">Select</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-1">
                                <span >6.</span>
                            </div>
                            <div class="col-xs-9  ">
                                <span >Non Applicability of standard justified. (Subject to verification during stage I audit)</span>
                            </div>
                            <div class="col-sm-2 form-group float-label-control">
                                <label></label>
                                <select name="standardClause" class="form-control standardClause" id="standardClause" required>
                                    <option value="">Select</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div> 
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-1">
                                <span>7.</span>
                            </div>
                            <div class="col-xs-9  ">
                                <span >Verified that there is no any risk involved regarding statutory, regulatory, and legal obligations.</span>
                            </div>
                            <div class="col-sm-2 form-group float-label-control">
                                <label></label>
                                <select name="riskStatutory" class="form-control riskStatutory" id="riskStatutory" required>
                                    <option value="">Select</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                        </div>

                    
                        <div class="row" style="">
                            <div class="col-xs-1">
                                <span >8.</span>
                            </div>
                            <div class="col-xs-9  ">
                                <span >Verified that there is no risk involved regarding conflict of interest or any  breach of AITF policies.</span>
                            </div>
                            <div class="col-sm-2 form-group float-label-control">
                                <label></label>
                                <select name="riskConflict" class="form-control riskConflict" id="riskConflict" required>
                                    <option value="">Select</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">                                
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Conclusion:</label>
                                <input type="text" class="conclusion" id="conclusion" name="conclusion" placeholder="Conclusion: " required>
                            </div>                        
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-12  ">
                                <span >A. Feasible: Certification service can perform.</span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-12  ">
                                <span >B. Feasible but changes recommended to perform the service (see attached)</span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-12  ">
                                <span >C. Not Feasible AITF refuse to application.</span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="col-xs-12 form-group float-label-control ">
                                <span ><small>In the event of conclusion (B or C) comments. </small></span>
                                <textarea name="conclusionborc" rows="3" class="form-control form-group conclusionborc" id="conclusionborc" placeholder="In the event of conclusion (B or C) comments." required></textarea>

                            </div>
                        </div>                        
                        <div class="row" style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <label for="">Event of Multisite with eligibility of sampling creator</label>
                                <textarea name="multisiteEligibility" rows="3" class="form-control form-group multisiteEligibility" id="multisiteEligibility" placeholder="Review in the Event of Multisite with eligibility of sampling creator" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn review-outcome">
                            <span><i class="fa  ">Review Outcome (for input to agreement & quotation)</i></span>
                        </div>
                    </div>
                    <div class="review-outcomeDiv" style="display:none">
                        <div class="row" style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Assessment Standard</label>
                                <textarea name="assessmentStandard" class="form-control form-group assessmentStandard" id="assessmentStandard" placeholder="Assessment Standard" required></textarea>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Accreditation:</label>
                                <textarea name="reviewaccreditation" class="form-control form-group reviewaccreditation" id="reviewaccreditation" placeholder="Accreditation" required></textarea>
                            </div> 
                        </div>
                        <div class="row basicIaf" style="padding-top: 10px">

    <div class="col-sm-4 form-group float-label-control">

        <label>Select Division</label>

        <select name="iaf-code[]" class="form-control iaf_code" required>

            <option value="">Select Division</option>

            <?php foreach ($iafList as $iaf) { ?>

                <option value="<?php echo $iaf["iaf_code"] ?>" data-id="<?php echo $iaf["iaf_id"]; ?>"data-id1="<?php echo $iaf["iaf_desc"]; ?>" ><?php echo $iaf["iaf_code"] ?> - <?php echo $iaf["iaf_desc"] ?></option>

            <?php } ?>

        </select>

    </div>



    <div class="col-sm-4 form-group float-label-control">

        <label>Select Group</label>

        <select name="nace-code[]" class="form-control nace-code" required>

            <option value="">Select Group</option>

            </select>

    </div>



    <div class="col-sm-4 form-group float-label-control">

        <label>Select NACE Class</label>

        <select name="nace-class[]" class="form-control nace-class" required>

            <option value="">Select NACE Class</option>

            </select>

    </div>

</div>



<div class="row">

    <div class="col-sm-12 text-right">

        <a class="addNewSection" title="Add New Section"><i class="fa fa-plus"></i></a>

    </div>

</div>
                        <div class="newIafToApplication"  style="display: none">
                            <div class="row" style="padding-top: 10px"> 
                                <div class="col-sm-5 form-group float-label-control ">
                                    <label>Multiple Sectors</label>
                                    <input type="text" name="customizeiaf_code" class="form-control customizeiaf_code" placeholder="Multiple Sectors">
                                </div>
                                <div class="col-sm-6 form-group float-label-control">
                                    <label>Customize Technical Area (NACE)</label>
                                    <input type="text" class="customize_nace" id="customize_nace" name="customize_nace" placeholder="Customize Technical Area (NACE)">
                                </div>
                                <div class="col-sm-1 form-group float-label-control">
                                    <a class="removecustomize" style="" title="Rmove Customize Iaf"><i class="fa fa-times"></i></a>
                                </div>
                                <input type="hidden" class="customizeiafnace" name="customizeiafnace" value="basic">
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Proposed scope of certification</label>
                                <textarea rows="2" class="proposed-scope" id="proposed-scope" name="proposed-scope" placeholder="Proposed scope of certification" required></textarea>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Proposed month/year for Initial Audit</label>
                                <input type="text" class="proposed-month" id="proposed-month" name="proposed-month" placeholder="Proposed month/year for Initial Audit" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Man-days required as per IAF MD 5:</label>
                                <input type="text" class="man-days-required" id="man-days-required" name="man-days-required" placeholder="Man-days required as per IAF MD 5:" required>
                            </div>
                        </div>
                        <div class="row" style="">                            
                            <div class="col-xs-12">
                                <span >Reducing Factor, Man-days:</span>
                            </div>
                        </div>                        
                        <div class="row" style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <textarea name="designingDevelopment" rows="2" class="form-control form-group designingDevelopment" id="designingDevelopment" placeholder="Designing and development (cl 8.3), Not applicable: 10%" required></textarea>
                            </div>
                        </div>
                        <div class="row" style="">                            
                            <div class="col-xs-12">
                                <span >Justification of Reducing:</span>
                            </div>
                        </div>                        
                        <div class="row" style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <label for="">Justification of Reducing:</label>
                                <textarea name="justificationReduction" rows="2" class="form-control form-group justificationReduction" id="justificationReduction" placeholder="Justification of Reducing:" required></textarea>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Additive Factor if any, Man-days:</label>
                                <input type="text" class="additive-factor" id="additive-factor" name="additive-factor" placeholder="Additive Factor if any, Man-days:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Actual Effective man-days required:</label>
                                <input type="text" class="actual-effective" id="actual-effective" name="actual-effective" placeholder="Actual Effective man-days required:" required>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px">                                
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Requirement of Technical Expert:</label>
                                <input type="text" class="technical-expert" id="technical-expert" name="technical-expert" placeholder="Requirement of Technical Expert:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Were Travel Logistics Discussed & finalized:</label>
                                <input type="text" class="travel-logistics" id="travel-logistics" name="travel-logistics" placeholder="Were Travel Logistics Discussed & finalized:" required>
                            </div>
                        </div>
                        <div class="row" style="">                            
                            <div class="col-xs-12">
                                <span ><strong>Quotation Amount Cost (INR):(Re/Certification + 1st surveillance + 2nd surveillance):</strong></span>
                            </div>
                        </div>                        
                        <div class="row" style="">                            
                            <div class="col-xs-9 form-group float-label-control">
                                <input type="text" class="applicationAmountDesc" name="applicationAmountDesc"  value="Application:(Fee Covers Administration including work of inquiry realization Cost & is non - refundable):">
                            </div>
                            <div class="col-sm-3 form-group float-label-control" >
                                <label>@RS.</label>
                                <input type="number" min="1" class="applicationAmount" id="applicationAmount" name="applicationAmount" placeholder="@RS." required>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px">
                            <div class="col-xs-9 form-group float-label-control">
                                <input type="text" class="documentationReviewDesc" name="documentationReviewDesc"  value="Initial Audit (Stage 1) - Documentation Review and/or Adequacy Audit:">
                            </div>
                            <div class="col-sm-3 form-group float-label-control" >
                                <label>@RS.</label>
                                <input type="number"  class="documentationReview" id="documentationReview" name="documentationReview" placeholder="@RS." required>
                            </div>
                        </div>
                        <div class="row" style="">                            
                            <div class="col-xs-9 form-group float-label-control">
                                <input type="text" class="certificationRecertificationDesc" name="certificationRecertificationDesc"  value="Initial Audit (Stage 2) - Certification/Recertification Audit OR______Audit:">
                            </div>
                            <div class="col-sm-3 form-group float-label-control">
                                <label>@RS.</label>
                                <input type="number" class="certificationRecertification" id="certificationRecertification" name="certificationRecertification" placeholder="@RS." required>
                            </div>
                        </div>
                        <div class="row" style=""> 
                            <div class="col-xs-9 form-group float-label-control">
                                <input type="text" class="maintenanceCertificationDesc" name="maintenanceCertificationDesc"  value="Maintenance of Certification: Total Two surveillance audits with interval of 12 months from the certification decision. The cost of surveillance for each Year:">
                            </div>
                            <div class="col-sm-3 form-group float-label-control">
                                <label>@RS.</label>
                                <input type="number"  class="maintenanceCertification" id="maintenanceCertification" name="maintenanceCertification" placeholder="@RS." required>
                            </div>
                        </div>
                        <div class="row" style="padding-top: 10px">
                            <div class="col-sm-6 form-group float-label-control">
                                <label style="font-weight: 600;">Reviewer Name:</label>
                                <select class="form-control reviewer_name" id="reviewer_name" name="reviewer_name" required style="border-radius: 4px; height: 38px; font-size: 13.5px; background: #fff;">
                                    <option value="">-- Select Reviewer --</option>
                                    <?php if (!empty($reviewersList)) {
                                        foreach ($reviewersList as $rev) { ?>
                                            <option value="<?php echo htmlspecialchars($rev['auditor_name'], ENT_QUOTES, 'UTF-8'); ?>" data-auditor-id="<?php echo (int)$rev['auditors_id']; ?>">
                                                <?php echo htmlspecialchars($rev['auditor_name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                    <?php }
                                    } ?>
                                </select>
                                <div id="revConflictError" style="display: none; margin-top: 4px;">
                                    <small><span id="revConflictErrorMsg" style="color: #c9513c; font-style: italic; font-weight: bold;"></span></small>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Review Date:</label>
                                        <input type="text" class="review_date" id="review_date" name="review_date" placeholder="Review Date:" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div id="revDateError" style="display: none">
                                            <small><span id="revDateErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>  
                        </div>
                        <!--                        <div class="row" style="padding-top: 10px">                                
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>Comment if any:</label>
                                                        <input type="text" class="anyComment" id="anyComment" name="anyComment" placeholder="Comment if any:">
                                                    </div>                            
                                                </div>-->
                    </div>
                    <input type="hidden" name="serviceId" class="serviceId" id="serviceId" value="">
                    <input type="hidden" name="customerId" class="customerId" id="customerId" value="">
                    <input type="hidden" name="auditId" class="auditId" id="auditId" value="">
                    <input type="hidden" name="unisvcId" class="unisvcId" value="">
                    <input type="hidden" name="pageview" class="pageview" value="">
                    <input type="hidden" name="iafid" class="iafid" value="">
                    <input type="hidden" name="selectedSectors" class="selectedSectors" value="">
                    <input type="hidden" name="selectedNaceCode" class="selectedNaceCode" value="">
                    <input type="hidden" name="selectedNaceClass" class="selectedNaceClass" value="">
                    <input type="hidden" name="selectedCertifications" class="selectedCertifications" value="">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="addApplication" class="btn btn-xs btn-info addApplication">Save</button>                    
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .row_nace{}
</style>
<script type="text/javascript">
document.getElementById('integratedmanagement').addEventListener('focus', function() {
  this.style.height = '70px'; // Adjust the height as needed
});
document.getElementById('adverseHistory').addEventListener('focus', function() {
  this.style.height = '70px'; // Adjust the height as needed
});
document.getElementById('integratedresponsibilities').addEventListener('focus', function() {
  this.style.height = '70px'; // Adjust the height as needed
});
document.getElementById('environmentalClearance').addEventListener('focus', function() {
  this.style.height = '70px'; // Adjust the height as needed
});
document.getElementById('adverseImpact').addEventListener('focus', function() {
  this.style.height = '70px'; // Adjust the height as needed
});
document.getElementById('healthAndSafetyRisks').addEventListener('focus', function() {
  this.style.height = '70px'; // Adjust the height as needed
});
document.getElementById('SitesPublicPresent').addEventListener('focus', function() {
  this.style.height = '70px'; // Adjust the height as needed
});
    $(function () {

        $('#applicationDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#applicationDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#applicationRcvDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#applicationRcvDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#revDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#revDate").keydown(function (e) {
//            e.preventDefault();
        });
        $('#review_date').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#review_date").keydown(function (e) {
//            e.preventDefault();
        });

        $('#isueDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
        });
        $("#isueDate").keydown(function (e) {
//            e.preventDefault();
        });
    });



    $(document).ready(function () {
        function updateTextArea() {
    var assessmentStandard = "";
    var accreditation = "";
    var uniqueStandards = new Set(); // Use a Set to track unique standards
    var uniqueAccreditations = new Set();

    $(".row").each(function() {
        var standard = $(this).find(".certificationStandard").val();
        var accred = $(this).find(".accreditation").val();

        if (standard && accred && !uniqueStandards.has(standard)) {
            assessmentStandard += standard + ",";
            accreditation += accred + ",";
            uniqueStandards.add(standard);
            uniqueAccreditations.add(accred);
        }
    });
    assessmentStandard = assessmentStandard.trim().replace(/,$/, "");
    accreditation = accreditation.trim().replace(/,$/, "");

    // Update text areas only if values have changed
    if ($("#assessmentStandard").val() !== assessmentStandard) {
        $("#assessmentStandard").val(assessmentStandard);
    }
    if ($("#reviewaccreditation").val() !== accreditation) {
        $("#reviewaccreditation").val(accreditation);
    }
}

    // Event listener for changes in select elements
    $(document).on("change", ".certificationStandard, .accreditation", function() {
        updateTextArea();
    });
    $(".addNewSection1").click(function() {
    var sectionIndex = $(".basiccert").length; // Get the index of the new section
    $(this).parent().append(`
        <div class="row basiccert" style="padding-top: 10px">
    <div class="col-sm-6 form-group float-label-control">
        <label>Certification required for standard: </label>
        <select name="certificationStandard[]" class="form-control certificationStandard" required>
            <option value="">Select Assessment Standard</option>
            <?php foreach ($standardsList as $standards) { ?>
                <option value="<?php echo $standards["standards_name"] ?>"><?php echo $standards["standards_name"] ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-sm-6 form-group float-label-control">
        <label>Accreditation Requirement</label>
        <select name="accreditation[]" class="form-control accreditation" required>
            <option value="">Select Accreditation</option>
            <?php foreach ($accreditationList as $accreditation) { ?>
                <option value="<?php echo $accreditation["accreditation_name"] ?>"><?php echo $accreditation["accreditation_name"] ?></option>
            <?php } ?>
        </select>
    </div>
            <a class="removeAddNewSection1" title="Remove New Section"><i class="fa fa-remove"></i></a>
        </div>
    `);
});
$(document).on('click', '.removeAddNewSection1', function() {
    $(this).closest('.basiccert').remove();
    updateTextArea();
});
        $(".addNewSection").click(function() {
  $(this).parent().append(`
    <div class="row basicIaf" style="padding-top: 10px">
        <div class="col-sm-4 form-group float-label-control">
            <label>Select Division</label>
            <select name="iaf-code[]" class="form-control iaf_code" required>
                <option value="">Select Division</option>
                    <?php foreach ($iafList as $iaf) { ?>
                <option value="<?php echo $iaf["iaf_code"] ?>" data-id="<?php echo $iaf["iaf_id"]; ?>"data-id1="<?php echo $iaf["iaf_desc"]; ?>"><?php echo $iaf["iaf_code"] ?> - <?php echo $iaf["iaf_desc"] ?></option>
                    <?php } ?>
            </select>
                    
        </div>

        <div class="col-sm-4 form-group float-label-control">
            <label>Select Group</label>
            <select name="nace-code[]" class="form-control nace-code" required>
                <option value="">Select Group</option>
            </select>
                
        </div>

        <div class="col-sm-4 form-group float-label-control">
            <label>Select NACE Class</label>
            <select name="nace-class[]" class="form-control nace-class" required>
                <option value="">Select NACE Class</option>
            </select>

        </div>
        <a class="removeAddNewSection" style="" title="Remove"><i class="fa fa-remove"></i></a>
    </div>
  `);
});
$('body').on("click", ".removeAddNewSection", function () {
        $(this).closest(".basicIaf").remove();
    });

        $('.applicationfor').change(function () {
            if ((this).checked) {
                $('.applicationfor').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $(".newCustomizeNaceRowAdd").click(function () {
            $(".newIafToApplication").append('<div class="row parentrow">\n\
                                                <div class="col-sm-4 form-group float-label-control ">\n\
                                                <label>Customize IAF Code</label>\n\
                                                <input type="text" name="customizeiaf_code" class="form-control customizeiaf_code" placeholder="Customize IAF Code">\n\
                                                </div>\n\
                                                <div class="col-sm-6 form-group float-label-control">\n\
                                                <label>Customize Nace Code</label>\n\
                                                <input type="text" class="customize_nace" id="customize_nace" name="customize_nace" placeholder="Customize Nace Code" required>\n\
                                                </div>\n\
                                                <div class="col-sm-1 form-group float-label-control" customizecontentrow>\n\
                                                <a class="removeCustomizeNaceRowAdd" style="" title="Remove Nace Code"><i class="fa fa-remove"></i></a>\n\
                                                </div>\n\
                                            </div>');
        });

        $('body').on("click", ".removeCustomizeNaceRowAdd", function () {
//        alert("Atleast one action item required");
            $(this).parent("div.customizecontentrow").parent('div.parentrow').remove();
        });

        $(".customizeIAF").click(function () {
            $(".customizeiafnace").val("customize");
            $('.iaf_code').attr("disabled", true);
            $('.nace-code').attr("disabled", true);
            $(".newIafToApplication").show();
        });

        $(".removecustomize").click(function () {
            $(".customizeiafnace").val("basic");
            $('.iaf_code').attr("disabled", false);
            $('.nace-code').attr("disabled", false);
            $(".newIafToApplication").hide();
        });

        $changedCount = "";

        $(document).on('change', '.iaf_code', function() {
        $iafCode = $(this);
        $iafId = $iafCode.find('option:selected').data('id');
        var iafdesc = $iafCode.find('option:selected').data('id1');
        const $row = $iafCode.closest('.row.basicIaf');
    $.ajax({
        url: "mastergetdata.php",
        type: "GET",
        dataType: "json",
        data: {
            iafid: $iafId
        },
        success: function(data) {
            const $naceCodeSelect = $row.find('.nace-code');
            $naceCodeSelect.empty();
            $naceCodeSelect.append('<option value="">Select Group</option>');
            for (const key in data) {
                $naceCodeSelect.append('<option value="' + data[key]['nace_code'] + '">' + data[key]['nace_code'] + ' - ' + data[key]['nace_desc'] + '</option>');
            }
            $naceCodeSelect.trigger('change');
        },
        error: function(data, status, err) {
            alert(status);
        }
    });
});

$(document).on('change', '.nace-code', function() {
    const $naceCodeSelect = $(this);
    const $iafCode = $naceCodeSelect.closest('.row.basicIaf').find('.iaf_code').val();
    const $naceCode = $naceCodeSelect.val();
    const $naceClassSelect = $naceCodeSelect.closest('.row.basicIaf').find('.nace-class');
    $.ajax({
        url: 'fetch_nace_classes.php',
        type: 'POST',
        dataType: 'json',
        data: { iaf_code: $iafCode, nace_code: $naceCode },
        success: function(response) {
            $naceClassSelect.empty();
            $naceClassSelect.append('<option value="">Select NACE Class</option>');
            $.each(response, function(index, response) {
                $naceClassSelect.append('<option value="' + response.nace_class + '">' + response.nace_class + ' - ' + response.nace_desc + '</option>');
            });
            $naceClassSelect.trigger('change');
        }
    });
});

        $('body').on("click", ".removenewNaceRow", function () {
            $(this).parent("div.customizeremove").parent('div.ncdivadd').remove();
        });

//        $('body').on("change", ".nace-code", function () {
////        alert("Atleast one action item required");
//            alert($(this).val());
//        });

        var aitfAssignedAuditors = [];

        function loadAssignedAuditorsForConflictCheck(customerId, serviceId) {
            aitfAssignedAuditors = [];
            if (!customerId && !serviceId) return;
            $.ajax({
                url: "checkReviewerConflict.php",
                type: "GET",
                dataType: "json",
                data: {
                    action: 'get_assigned_auditors',
                    customerId: customerId,
                    serviceId: serviceId
                },
                success: function(resp) {
                    if (resp && resp.success && resp.auditors) {
                        aitfAssignedAuditors = resp.auditors;
                    }
                }
            });
        }

        function checkConflictBetweenReviewerAndAuditors(reviewerName) {
            // Temporarily disabled
            return false;
        }

        function showReviewerConflictDialogue(conflictingAuditor, reviewerName) {
            var nameToDisplay = conflictingAuditor || reviewerName || "This user";
            var msg = nameToDisplay + " is already assigned as an Auditor for this client, cannot set as Reviewer.\n\nPlease select another Reviewer.";
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({
                    icon: 'error',
                    title: 'Conflict Detected',
                    html: '<div class="aitf-conflict-card">' +
                              '<div class="aitf-conflict-card-title">' +
                                  '<span class="aitf-conflict-card-name">' + nameToDisplay + '</span> is already assigned as Auditor of this client, cannot set as Reviewer.' +
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
            $('#revConflictErrorMsg').text(nameToDisplay + ' is already assigned as Auditor of this client, cannot set as Reviewer. Please select another Reviewer.');
            $('#revConflictError').show();
        }

        $('#reviewer_name').on('change', function () {
            $('#revConflictError').hide();
        });

$('.addApplication').click(function (e) {
    var reviewerVal = ($('#reviewer_name').val() || '').trim();
    if (!reviewerVal) {
        e.preventDefault();
        alert('Please select a Reviewer Name.');
        $('#reviewer_name').focus();
        return false;
    }

    selectedSectors = $('.iaf_code').map(function() {
        return $(this).val();
    }).get();
    $('.selectedSectors').val(selectedSectors);
    selectedNaceCode = $('.nace-code').map(function() {
        return $(this).val();
    }).get();
    $('.selectedNaceCode').val(selectedNaceCode);
    selectedNaceClass = $('.nace-class').map(function() {
        return $(this).val();
    }).get();
    $('.selectedNaceClass').val(selectedNaceClass);
        selectedCertifications = $('.certificationStandard').map(function() {
        return $(this).val();
    }).get();
    $('.selectedCertifications').val(selectedCertifications);

});

        $('.createAuditApp').click(function () {
            var page = $(this).attr("data-page");
//                        alert($(this).attr('data-customer'));
            $('.serviceId').val($(this).attr('data-caservice-id'));
            $('.customerId').val($(this).attr('data-customer'));
            $('.unisvcId').val($(this).attr('data-service'));
            $(".pageview").val(page);
//            alert($('.unisvcId').val());
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customerid: $(this).attr('data-customer'),
                },
                success: function (data)
                {
                    $('.customerId').val(data['id']);
                    $('.orgName').val(data['customer_name']);
                    $('.aif-code').val(data['aif_nace']);
                    $('input[type="text"]').trigger('change');
//                    $('textarea').trigger('change');

                    // Initialize Date Chronology Validation
                    DateChronologyValidator.init({
                        customerId: $('.customerId').val(),
                        typedServiceId: $('.serviceId').val() || $('.unisvcId').val(),
                        fields: {
                            'application_date': { input: '.applicationDate', errorDiv: '#invDateError', errorMsg: '#invDateErrorMsg' },
                            'application_rcv_date': { input: '.applicationRcvDate', errorDiv: '#rcvDateError', errorMsg: '#rcvDateErrorMsg' },
                            'contract_review_date': { input: '.review_date', errorDiv: '#revDateError', errorMsg: '#revDateErrorMsg' }
                        },
                        submitBtn: '.addApplication'
                    });

                    $('.reviewer_name').val('');
                    $('#revConflictError').hide();
                    loadAssignedAuditorsForConflictCheck($('.customerId').val(), $('.serviceId').val() || $('.unisvcId').val());

                    $('#auditApplication').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $(document).on('click', '.editAuditApp', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $icon = $btn.find('i');
            var origClass = $icon.attr('class') || 'fa fa-pencil-square fa-lg';
            $icon.attr('class', 'fa fa-spinner fa-spin fa-lg');
            $btn.css('pointer-events', 'none');

            function restoreAppBtn() {
                $icon.attr('class', origClass);
                $btn.css('pointer-events', '');
            }

            var page = $btn.attr("data-page");
            var customerid = $btn.attr('data-customer');
            $.ajax({
                url: "getAuditApplication.php",
                type: "GET",
                dataType: "json",
                data: {
                    auditAppId: $btn.attr('data-audit'),
                },
                success: function (data)
                {
                    restoreAppBtn();
                    if (!data || !data[0]) {
                        alert("Could not load application details.");
                        return;
                    }
                    try {
                        $('.customerId').val(customerid);
                        $('.auditId').val(data[0]['audit_id']);
                        $('.serviceId').val(data[0]['service_id'] || '');
                    $('.unisvcId').val(data[0]['uni_svc_id'] || '');
                    $('#revConflictError').hide();
                    loadAssignedAuditorsForConflictCheck(customerid, data[0]['service_id'] || data[0]['uni_svc_id']);
                    $('.applicationfor[value = "' + data[0]["application_for"] + '"]').prop('checked', true);
                    var review_date;
                    if (data[0]['review_date'] == "" || data[0]['review_date'] == null)
                        review_date = "";
                    else
                        review_date = moment(data[0]['review_date']).format('DD.MM.YYYY');
                    $('.review_date').val(review_date);

                    if (data[0]['application_date'] == "" || data[0]['application_date'] == null)
                        $appdate = "";
                    else
                        $appdate = moment(data[0]['application_date']).format('DD.MM.YYYY');
                    $('.applicationDate').val($appdate);
                    if (data[0]['application_rcv_date'] == "" || data[0]['application_rcv_date'] == null)
                        $apprcvdate = "";
                    else
                        $apprcvdate = moment(data[0]['application_rcv_date']).format('DD.MM.YYYY');
                    $(".applicationRcvDate").val($apprcvdate);
                    $('.orgName').val(data[0]['org_name']);
                    $('.legalStatus').val(data[0]['legal_status']);
                    $('.industryClassify').val(data[0]['industury_classfication']);
                    $('.legalPart').val(data[0]['legal_group']);
                    $('.locationUnderAudit').val(data[0]['n_location']);
                    $('.employees').val(data[0]['n_employee']);
                    $('.shifts').val(data[0]['n_shift']);
                    $('.product').val(data[0]['product']);
                    $('.processes').val(data[0]['process']);
                    $('.certificationStandard').val(data[0]['certification']);
                    $('.accreditation').val(data[0]['accereditation_requirement']);
                    $('.consultancyServices').val(data[0]['consultancy_services']);
                    $('.exclusionClauses').val(data[0]['standard_clauses']);
                    //$('.technicalArea').val(data[0]['technical_area']);
                    $('.legalObligations').val(data[0]['legal_obligation']);
                    $('.conflictInterest').val(data[0]['conflict_interest']);
                    $('.statutoryRequirement').val(data[0]['statutory_requirement']);
                    $('.customerRequirement').val(data[0]['customer_requirement']);
                    $('.organizationService').val(data[0]['org_involved']);
                    $('.otherRequirement').val(data[0]['other_requirement']);
    $('.mainProducts').val(data[0]['main_Products']);
                    $('.mainRawMaterial').val(data[0]['main_RawMaterial']);
                    $('.operationInvolved').val(data[0]['operation_Involved']);
                    $('.sourcesProduct').val(data[0]['sources_Product']);
                    $('.complicatedLogistics').val(data[0]['complicated_Logistics']);
if (data[0]['applicationfor0'] === 'Yes') {
    $('#applicationforYes0').prop('checked', true).trigger('change');
    $('.nointegration').val(data[0]['no_integration']);
    $('.managementreview').val(data[0]['management_review']);
    $('.integratedmanagement').val(data[0]['integrated_management']);
    $('.integratedapproach').val(data[0]['integrated_approach']);
    $('.integratedresponsibilities').val(data[0]['integrated_responsibilities']);
} else {
    $('#applicationforNo0').prop('checked', true).trigger('change');
}
if (data[0]['applicationfor2'] === 'Yes') {
    $('#applicationforYes2').prop('checked', true).trigger('change');
    $('.environmentalClearance').val(data[0]['environmental_Clearance']);
    $('.hazardousMaterials').val(data[0]['hazardous_Materials']);
    $('.adverseImpact').val(data[0]['adverse_Impact']);
    $('.sensitiveLocation').val(data[0]['sensitive_Location']);
    $('.archaeologicalSite').val(data[0]['archaeological_Site']);
    $('.adverseHistory').val(data[0]['adverse_History']);
} else {
    $('#applicationforNo2').prop('checked', true).trigger('change');
}
if (data[0]['applicationfor3'] === 'Yes') {
    $('#applicationforYes3').prop('checked', true).trigger('change');
    $('.statutoryHealthandsafety').val(data[0]['statutory_Healthandsafety']);
    $('.hazardousDangerousSubstances').val(data[0]['hazardousDangerous_Substances']);
    $('.healthAndSafetyRisks').val(data[0]['healthAndSafety_Risks']);
    $('.temporarysites').val(data[0]['temporary_sites']);
    $('.accidentsRates').val(data[0]['accidents_Rates']);
    $('.SitesPublicPresent').val(data[0]['Sites_PublicPresent']);
    $('.governmentalRegulators').val(data[0]['governmental_Regulators']);
} else {
    $('#applicationforNo3').prop('checked', true).trigger('change');
}
if (data[0]['applicationfor4'] === 'Yes') {
    $('#applicationforYes4').prop('checked', true).trigger('change');
    $('.otherStanderedName').val(data[0]['other_StanderedName']);
    $('.otherCheckPoint1').val(data[0]['other_CheckPoint1']);
    $('.otherCheckPoint2').val(data[0]['other_CheckPoint2']);
    $('.otherCheckPoint3').val(data[0]['other_CheckPoint3']);
    $('.otherCheckPoint4').val(data[0]['other_CheckPoint4']);
    $('.otherCheckPoint5').val(data[0]['other_CheckPoint5']);
    $('.otherCheckPoint6').val(data[0]['other_CheckPoint6']);
} else {
    $('#applicationforNo4').prop('checked', true).trigger('change');
}

                    $('.assessmentCertification').val(data[0]['assessment_certification']);
                    $('.adequateResource').val(data[0]['adequate_resource']);
                    $('.productService').val(data[0]['product_service']);
                    $('.scope').val(data[0]['scope']);
                    $('.competentTeam').val(data[0]['competent_team']);
                    $('.standardClause').val(data[0]['standard_clause']);
                    $('.riskStatutory').val(data[0]['risk_statutory']);
                    $('.riskConflict').val(data[0]['risk_conflict']);
                    $('.conclusionborc').val(data[0]['conclusionborc']);
                    $('.conclusion').val(data[0]['conclusion']);

                    $('.multisiteEligibility').val(data[0]['multisite_eligibility']);
                    $('.assessmentStandard').val(data[0]['assessment_standard']);
                    $('.reviewaccreditation').val(data[0]['review_accredition']);
                    $('.proposed-scope').val(data[0]['proposed_scope']);
                    $('.proposed-month').val(data[0]['proposed_month']);
                    $('.man-days-required').val(data[0]['man_day_req']);
                    $('.designingDevelopment').val(data[0]['reducing_factor']);
                    $('.justificationReduction').val(data[0]['justification_reducing']);
                    $('.additive-factor').val(data[0]['additive_factor']);
                    $('.actual-effective').val(data[0]['actual_effective']);
                    $('.technical-expert').val(data[0]['technical_req']);
                    $('.travel-logistics').val(data[0]['travel_logistic']);
                    $('.applicationAmount').val(data[0]['refundable_amount']);
                    $('.applicationAmountDesc').val(data[0]['application_amount_desc']);
                    $('.documentationReview').val(data[0]['audit_stage_i']);
                    $('.documentationReviewDesc').val(data[0]['documentation_review_desc']);
                    $('.certificationRecertification').val(data[0]['audit_stage_iI']);
                    $('.certificationRecertificationDesc').val(data[0]['certification_recertification_desc']);
                    $('.initialAssessment').val(data[0]['initial_assessment']);
                    $('.maintenanceCertification').val(data[0]['certification_maintenance']);
                    $('.maintenanceCertificationDesc').val(data[0]['maintenance_certification_desc']);
                    $('.allotmentAgreement').val(data[0]['aggrement_allotment']);
                    var currentReviewer = (data[0]["reviewer_name"] || "").trim();
                    if (currentReviewer) {
                        if ($('.reviewer_name option[value="' + currentReviewer + '"]').length === 0) {
                            $('.reviewer_name').append(new Option(currentReviewer, currentReviewer, true, true));
                        }
                        $('.reviewer_name').val(currentReviewer);
                    } else {
                        $('.reviewer_name').val("");
                    }
                    $('.anyComment').val("");
                    var rawCert = data[0]['certification'] || '';
                    var rawAcc = data[0]['accereditation_requirement'] || '';
                    var a = rawCert ? rawCert.split(',') : [];
                    var b = rawAcc ? rawAcc.split(',') : [];
                    $('.certificationStandard').val(a[0] || '').trigger('change');
                    $('.accreditation').val(b[0] || '');
                    if (a.length > 1) {
                        for (let i = 1; i < a.length; i++) {
                            const newSection1 = $('.basiccert').first().clone();
                            newSection1.append('<a class="removeAddNewSection1" style="" title="Remove"><i class="fa fa-remove"></i></a>');
                            newSection1.find('select').val('');
                            $('.addNewSection1').parent().append(newSection1);
                            newSection1.find('.certificationStandard').val(a[i]).trigger('change');
                            newSection1.find('.accreditation').val(b[i]).trigger('change');
                        }
                    }

                    if (data[1] && data[1]["iaf_type"] == "customize") {
                        $(".customizeiafnace").val("customizecodes");
                        $('.iaf_code').attr("disabled", true);
                        $('.nace-code').attr("disabled", true);
                        $(".customizeiaf_code").val(data[1]["iaf_code"]);
                        $(".customize_nace").val(data[1]["nace_codes"]);
                        $(".newIafToApplication").show();
                    } else if (data[1] && data[1]["iaf_type"] == "basic") {
                        //alert("hi");
                        $('.newNaceCoderow').empty();
                        var x = (data[1]['iaf_code'] || '').split(',');
                        var y = (data[1]['nace_codes'] || '').split(',');
                        var z = (data[1]['nace_class'] || '').split(',');
                        $('.iaf_code').val(x[0] || '').trigger('change');
                        setTimeout(function() {
                          $('.nace-code').val(y[0] || '').trigger('change');
                            setTimeout(function() {
                            $('.nace-class').val(z[0] || '').trigger('change');
                          }, 2000);
                        }, 2000);
                        if (x.length > 1) {
                            for (let i = 1; i < x.length; i++) {
                                // Clone the existing section
                                const newSection = $('.basicIaf').first().clone();
                                newSection.append('<a class="removeAddNewSection" style="" title="Remove"><i class="fa fa-remove"></i></a>');

                                // Remove any existing values from cloned section
                                newSection.find('select').val('');

                                // Append the cloned section to the parent container
                                $('.addNewSection').parent().append(newSection);

                                // Populate the values in the cloned section
                                newSection.find('.iaf_code').val(x[i]).trigger('change');
                                setTimeout(function() {
                                    newSection.find('.nace-code').val(y[i]).trigger('change');
                                    setTimeout(function() {
                                        newSection.find('.nace-class').val(z[i]).trigger('change');;
                                    }, 2500);
                                }, 2500);
                            }
                        }
                    }
                    $(".pageview").val(page);
                    $(".nacecode").trigger('change');
                    $(".proposed-scope").trigger('change');
                    $('input[type="text"]').trigger('change');
                    $('input[type="number"]').trigger('change');
//                    $('textarea').trigger('change');
//                    $('.Generate').hide();
                    $('#auditApplication').modal('show');
                    } catch (e) {
                        console.error("Error setting edit Audit Application fields:", e);
                        $('#auditApplication').modal('show');
                    }
                },
                error: function (data, status, err)
                {
                    restoreAppBtn();
                    alert("Error loading application: " + (status || err));
                }
            });
        });

        $('.legalStatus').change(function () {
            if ($('.legalStatus').val() == "Other") {
                $('.otherLegalStatus').show();
            } else {
                $('.otherLegalStatus').hide();
            }
        });

        $('.Generate').click(function (e) {
            if (validateDate()) {
                e.preventDefault();
                return;
            }
        });

        $('.formAuditApplication').click(function () {
            $('.auditApp').slideToggle('slow');
        });

        $('.generalAnalyse').click(function () {
            $('.generalAnalyseDiv').slideToggle('slow');
        });
        $('.generalAnalyseI').click(function () {
            $('.generalAnalyseDivI').slideToggle('slow');
        });
                $('.generalAnalyse0').click(function () {
            $('.generalAnalyseDiv0').slideToggle('slow');
        });

        $('.generalAnalyse1').click(function () {
            $('.generalAnalyseDiv1').slideToggle('slow');
        });

        $('.generalAnalyse2').click(function () {
            $('.generalAnalyseDiv2').slideToggle('slow');
        });

        $('.generalAnalyse4').click(function () {
            $('.generalAnalyseDiv4').slideToggle('slow');
        });

        $('.fesibilAnalysis').click(function () {
            $('.fesibilAnalysisDiv').slideToggle('slow');
        });

        $('.review-outcome').click(function () {
            $('.review-outcomeDiv').slideToggle('slow');
        });});
        $(".statutoryHealthandsafetyC").hide();
        $(".hazardousDangerousSubstancesC").hide();
        $(".healthAndSafetyRisksC").hide();
        $(".temporarysitesC").hide();
        $(".accidentsRatesC").hide();
        $(".SitesPublicPresentC").hide();
        $(".governmentalRegulators").hide();
        $("input[name='applicationfor3']").change(function() {
    if ($(this).val() === 'Yes') {
        $(".statutoryHealthandsafetyC").show();
                $(".hazardousDangerousSubstancesC").show();
                $(".healthAndSafetyRisksC").show();
                $(".temporarysitesC").show();
                $(".accidentsRatesC").show();
                $(".SitesPublicPresentC").show();
                $(".governmentalRegulators").show();
        // ...
    } else {
        $(".statutoryHealthandsafetyC").hide();
                $(".hazardousDangerousSubstancesC").hide();
                $(".healthAndSafetyRisksC").hide();
                $(".temporarysitesC").hide();
                $(".accidentsRatesC").hide();
                $(".SitesPublicPresentC").hide();
                $(".governmentalRegulators").hide();
    }
});


        // 2

        $(".environmentalClearanceC").hide();
        $(".hazardousMaterialsC").hide();
        $(".adverseImpactC").hide();
        $(".sensitiveLocationC").hide();
        $(".archaeologicalSiteC").hide();
        $(".adverseHistoryC").hide();
        $("input[name='applicationfor2']").change(function() {
    if ($(this).val() === 'Yes') {
        // Show related sections
        $(".environmentalClearanceC").show();
                $(".hazardousMaterialsC").show();
                $(".adverseImpactC").show();
                $(".sensitiveLocationC").show();
                $(".archaeologicalSiteC").show();
                $(".adverseHistoryC").show();
                $(".governmentalRegulatorsC").show();
        // ...
    } else {
        // Hide related sections
        $(".environmentalClearanceC").hide();
                $(".hazardousMaterialsC").hide();
                $(".adverseImpactC").hide();
                $(".sensitiveLocationC").hide();
                $(".archaeologicalSiteC").hide();
               
                $(".adverseHistoryC").hide();
        // ...
    }
});

//4
                $(".otherStanderedNameC").hide();
                $(".otherCheckPoint1C").hide();
                $(".otherCheckPoint2C").hide();
                $(".otherCheckPoint3C").hide();
                $(".otherCheckPoint4C").hide();
                $(".otherCheckPoint5C").hide();
                $(".otherCheckPoint6C").hide();
$("input[name='applicationfor4']").change(function() {
    if ($(this).val() === 'Yes') {
        // Show related sections
        $(".otherStanderedNameC").show();
                $(".otherCheckPoint1C").show();
                $(".otherCheckPoint2C").show();
                $(".otherCheckPoint3C").show();
                $(".otherCheckPoint4C").show();
                $(".otherCheckPoint5C").show();
                $(".otherCheckPoint6C").show();
        // ...
    } else {
        // Hide related sections
        $(".otherStanderedNameC").hide();
                $(".otherCheckPoint1C").hide();
                $(".otherCheckPoint2C").hide();
                $(".otherCheckPoint3C").hide();
                $(".otherCheckPoint4C").hide();
                $(".otherCheckPoint5C").hide();
                $(".otherCheckPoint6C").hide();
        // ...
    }
});

        $(".nointegration").hide();
        $(".managementreview").hide();
        $(".integratedmanagement").hide();
        $(".integratedapproach").hide();
        $(".integratedresponsibilities").hide();
        $("input[name='applicationfor0']").change(function() {
    if ($(this).val() === 'Yes') {
        // Show related sections
        $(".nointegration").show();
        $(".managementreview").show();
        $(".integratedmanagement").show();
        $(".integratedapproach").show();
        $(".integratedresponsibilities").show();
        // ...
    } else {
        // Hide related sections
        $(".nointegration").hide();
        $(".managementreview").hide();
        $(".integratedmanagement").hide();
        $(".integratedapproach").hide();
        $(".integratedresponsibilities").hide();
        // ...
    }
});
</script>

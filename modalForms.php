<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$customerStatus = "SELECT * FROM customer_status";
$statement = $db->prepare($customerStatus);
$statement->execute();
$status = $statement->fetchAll();

$sql = "SELECT * FROM `firm_services`";
$statement = $db->prepare($sql);
$statement->execute();
$aitfservices = $statement->fetchAll();

$svclist = "";
foreach ($aitfservices as $svc) {
    $svclist .= $svc["firm_service"] . ",";
}

$customers = "SELECT * FROM `customer_info` where is_deleted = 'NO'";
$statement = $db->prepare($customers);
$statement->execute();
$allCustomers = $statement->fetchAll();

$taxation = "SELECT * FROM `taxation` WHERE type_of_tax = 'CGST'";
$statement = $db->prepare($taxation);
$statement->execute();
$taxationDetails = $statement->fetch();
$csgttax = $taxationDetails["rate"];

$indianGstStates = array(
    'Andaman and Nicobar Islands' => '35',
    'Andhra Pradesh' => '37',
    'Arunachal Pradesh' => '12',
    'Assam' => '18',
    'Bihar' => '10',
    'Chandigarh' => '04',
    'Chhattisgarh' => '22',
    'Dadra and Nagar Haveli and Daman and Diu' => '26',
    'Delhi' => '07',
    'Goa' => '30',
    'Gujarat' => '24',
    'Haryana' => '06',
    'Himachal Pradesh' => '02',
    'Jammu and Kashmir' => '01',
    'Jharkhand' => '20',
    'Karnataka' => '29',
    'Kerala' => '32',
    'Ladakh' => '38',
    'Lakshadweep' => '31',
    'Madhya Pradesh' => '23',
    'Maharashtra' => '27',
    'Manipur' => '14',
    'Meghalaya' => '17',
    'Mizoram' => '15',
    'Nagaland' => '13',
    'Odisha' => '21',
    'Puducherry' => '34',
    'Punjab' => '03',
    'Rajasthan' => '08',
    'Sikkim' => '11',
    'Tamil Nadu' => '33',
    'Telangana' => '36',
    'Tripura' => '16',
    'Uttar Pradesh' => '09',
    'Uttarakhand' => '05',
    'West Bengal' => '19',
    'Other Territory' => '97'
);
?>
<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<div class="modal fade" id="addCertificetionComment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Comment</h4>
            </div>
            <form action="addComments.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">                        
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control" style="padding-top:10px;">
                            <label for="">Comment</label>
                            <textarea type="text" name="certificationComment" rows="4" data-column="cert_comment" class="form-control form-group certificationComment" id="certificationComment" placeholder="Add Comment"></textarea>
                        </div>
                    </div>
                    <input type="hidden" class="cbId" id="cbId" name="cbId" value="">
                    <input type="hidden" class="cbCustomer" id="cbCustomer" name="cbCustomer" value="">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-info saveComment">Save</button>
                </div>                    
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelApplicationModal" tabindex="-1" role="dialog" aria-labelledby="cancelApplicationLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="cancelApplicationLabel">Cancel Application</h4>
            </div>
            <form action="cancelApplication.php" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control" style="padding-top:10px;">
                            <label for="cancelReason">Reason for canceling application</label>
                            <textarea name="cancel_reason" id="cancelReason" rows="4" class="form-control" placeholder="Enter reason"></textarea>
                        </div>
                    </div>
                    <input type="hidden" name="customer_id" id="cancelCustomerId" value="">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-danger">Confirm Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade certificatePrintModal" id="certificatePrintModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Certification Details</h4>
            </div>
            <form action="certification_generate.php" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <div class="row">
                                <div class="col-sm-12 ">
                                    <div class="row">
                                        <div class="col-sm-12 form-group float-label-control">
                                            <label>Agreement Date</label> 
                                            <input type="text" class="registrationDateVal newRegistrationDate" name="newRegistrationDate" placeholder="Agreement Date">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div id="newRegistrationDateError" style="display: none">
                                                <small><span id="newRegistrationDateErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>                            
                            </div>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>Allotment No.</label> 
                                    <input type="text" name="allotmentNo" class="form-control allotmentNo" id="allotmentNo" placeholder="Allotment No." readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="csId" class="csId" id="csId" value=""> 
                <input type="hidden" name="service" class="service" id="service" value=""> 
                <input type="hidden" name="newCount" class="newCount" id="newCount" value="">
                <input type="hidden" name="certificateid" class="certificateid" id="certificateid" value="">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-info addCertification" data-role="<?php
                    if (isset($_SESSION['role'])) {
                        echo $_SESSION['role'];
                    }
                    ?>">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="customerAdd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 780px;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%); color: #ffffff; padding: 14px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color: #ffffff; opacity: 0.9; font-size: 22px; margin-top: -2px;">
                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel" style="color: #ffffff; font-weight: 700; font-size: 16px; margin: 0;">
                    <i class="fa fa-building" style="margin-right: 8px;"></i> Client Information
                </h4>
            </div>
            <form action="addCustomer.php" method="post">
                <div class="modal-body" style="padding: 20px 24px 10px;">
                    
                    <!-- Section 1: Basic Information -->
                    <div style="font-weight: 700; color: #0d4d8b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; padding-bottom: 5px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 6px;">
                        <i class="fa fa-info-circle"></i> Basic Details
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Client / Company Name <span style="color: #dc2626;">*</span></label>
                            <input type="text" class="form-control customerName" id="customerName" name="customerName" placeholder="Enter Client / Company Name" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">
                        </div>
                    </div>
                    <div class="row">
                        <div id="companyNameError" style="display: none; padding-left: 15px; margin-bottom: 8px;">
                            <small><span id="companyNameErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">GSTIN Number</label>                            
                            <input type="text" class="form-control gstin_no" id="gstin_no" name="gstin_no" placeholder="Enter GSTIN Number" onkeypress="return AvoidSpace(event)" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">                                                               
                        </div>
                        <div class="col-sm-6 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Scope of Certification</label>
                            <input type="text" class="form-control scope" id="scope" name="scope" placeholder="Enter Scope" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Comment / Notes</label>
                            <textarea name="addnotes" rows="2" class="form-control addnotes" id="addnotes" placeholder="Enter Comment or Notes" style="border-radius: 6px; border: 1px solid #cbd5e1; min-height: 55px; resize: vertical; font-size: 13px;"></textarea>
                        </div>
                    </div>

                    <!-- Section 2: Address Information -->
                    <div style="font-weight: 700; color: #0d4d8b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin: 16px 0 12px 0; padding-bottom: 5px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 6px;">
                        <i class="fa fa-map-marker"></i> Address Details
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Street / Office & Site Address</label>
                            <textarea name="streetAddress" rows="4" class="form-control streetAddress" id="streetAddress" placeholder="Enter Street Address / Office & Site Address (supports multiple lines for office & site addresses)" style="border-radius: 6px; border: 1px solid #cbd5e1; min-height: 90px; resize: vertical; font-size: 13px; line-height: 1.45;"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">City</label>
                            <input type="text" name="city" class="form-control city" id="city" placeholder="City" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">
                        </div>
                        <div class="col-sm-6 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">State</label>
                            <select name="state" class="form-control state" id="state" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">
                                <option value="">Select State</option>
                                <option value="Andhra Pradesh">Andhra Pradesh</option>
                                <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                <option value="Assam">Assam</option>
                                <option value="Bihar">Bihar</option>
                                <option value="Chhattisgarh">Chhattisgarh</option>
                                <option value="Goa">Goa</option>
                                <option value="Gujarat">Gujarat</option>
                                <option value="Haryana">Haryana</option>
                                <option value="Himachal Pradesh">Himachal Pradesh</option>
                                <option value="Jharkhand">Jharkhand</option>
                                <option value="Karnataka">Karnataka</option>
                                <option value="Kerala">Kerala</option>
                                <option value="Madhya Pradesh">Madhya Pradesh</option>
                                <option value="Maharashtra">Maharashtra</option>
                                <option value="Manipur">Manipur</option>
                                <option value="Meghalaya">Meghalaya</option>
                                <option value="Mizoram">Mizoram</option>
                                <option value="Nagaland">Nagaland</option>
                                <option value="Odisha">Odisha</option>
                                <option value="Punjab">Punjab</option>
                                <option value="Rajasthan">Rajasthan</option>
                                <option value="Sikkim">Sikkim</option>
                                <option value="Tamil Nadu">Tamil Nadu</option>
                                <option value="Telangana">Telangana</option>
                                <option value="Tripura">Tripura</option>
                                <option value="Uttar Pradesh">Uttar Pradesh</option>
                                <option value="Uttarakhand">Uttarakhand</option>
                                <option value="West Bengal">West Bengal</option>
                                <option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
                                <option value="Chandigarh">Chandigarh</option>
                                <option value="Dadra and Nagar Haveli and Daman and Diu">Dadra and Nagar Haveli and Daman and Diu</option>
                                <option value="Delhi">Delhi</option>
                                <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                <option value="Ladakh">Ladakh</option>
                                <option value="Lakshadweep">Lakshadweep</option>
                                <option value="Puducherry">Puducherry</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Post Code / Pin Code</label>
                            <input type="text" name="postCode" class="form-control postCode" id="postCode" placeholder="Enter Post Code (e.g. 400063)" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">
                        </div>
                        <div class="col-sm-6 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Country</label>
                            <input type="text" name="country" class="form-control country" id="country" placeholder="Country" value="India" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">
                        </div>
                    </div>

                    <!-- Section 3: Contact Information -->
                    <div style="font-weight: 700; color: #0d4d8b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin: 16px 0 12px 0; padding-bottom: 5px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 6px;">
                        <i class="fa fa-phone"></i> Contact Details
                    </div>
                    <div class="row">
                        <div class="col-sm-4 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Contact Person</label>
                            <input type="text" class="form-control customerCntctPerson" id="customerCntctPerson" name="customerCntctPerson" placeholder="Contact Person" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">
                            <div id="contactPersonError" style="display: none; margin-top: 4px;">
                                <small><span id="contactPersonErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                            </div>
                        </div>
                        <div class="col-sm-4 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Email Id</label>
                            <input type="email" class="form-control customerEmail" id="customerEmail" name="customerEmail" placeholder="Client Email" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">
                            <div id="contactEmailError" style="display: none; margin-top: 4px;">
                                <small><span id="contactEmailErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                            </div>
                        </div>
                        <div class="col-sm-3 form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Contact No.</label>
                            <input type="text" class="form-control contactNo" id="contactNo" name="contactNo" placeholder="Contact No." style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">
                            <div id="contactNoError" style="display: none; margin-top: 4px;">
                                <small><span id="contactNoErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                            </div>
                        </div>
                        <div class="col-sm-1 form-group" style="margin-bottom: 12px; padding-top: 24px;">
                            <a class="newContactNo" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: #0d4d8b; color: white !important; border-radius: 6px; text-decoration: none; cursor: pointer;" title="Add Multiple Contact"><i class="fa fa-plus"></i></a>
                        </div>
                    </div>
                    <div class="addMultipleContact"></div>
                </div>
                <input type="hidden" class="customerPersonHidden" name="customerPersonHidden" value=""> 
                <input type="hidden" class="customerContactHidden" name="customerContactHidden" value=""> 
                <input type="hidden" class="customerContactEmlHidden" name="customerContactEmlHidden" value=""> 
                <input type="hidden" class="editCustomerId" name="editCustomerId" value="">
                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px; font-weight: 600; padding: 7px 16px;">Cancel</button>
                    <button type="submit" class="btn btn-info addCustomerToService" style="background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%); border: none; border-radius: 6px; padding: 7px 22px; font-weight: 600; box-shadow: 0 2px 6px rgba(13,77,139,0.25);">Save Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade taxationAdd" id="taxationAdd" tabindex="-1"
     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Add Taxation setting for releasing invoice</h4>
            </div>
            <form action="addNewServices.php" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>New Taxation</label> 
                                    <input type="text" class="newTaxation" id="newTaxation" name="newTaxation" placeholder="New Taxation">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <div id="newTaxationError" style="display: none">
                                        <small><span  id="newTaxationErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>New Rate</label>
                                    <input type="text" name="newRate" class="form-control newRate"  id="newRate" placeholder="New Rate">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <div id="newRateError" style="display: none">
                                        <small><span id="newRateErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="taxation_id_update" class="taxation_id_update" id="taxation_id_update" value="">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-info addTaxationToService">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addCaServiceComment" tabindex="-1"
     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">CA Service</h4>
            </div>
            <form action="saveComments.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control" style="padding-top: 10px;">
                            <label for="">Certification Comment</label>
                            <textarea type="text" name="caServiceCommentAdd" rows="4"  data-column="cert_comment"  class="form-control form-group caServiceCommentAdd"  placeholder="Add Comment"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addSaiServiceComment" tabindex="-1"
     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">SA-I Service</h4>
            </div>
            <form action="saveComments.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control" style="padding-top: 10px;">
                            <label for="">Certification Comment</label>
                            <textarea type="text" name="saiServiceCommentAdd" rows="4" data-column="cert_comment" class="form-control form-group saiServiceCommentAdd" placeholder="Add Comment"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addSaiiServiceComment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">SA-II Service</h4>
            </div>
            <form action="saveComments.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control" style="padding-top: 10px;">
                            <label for="">Certification Comment</label>
                            <textarea type="text" name="saiiServiceCommentAdd" rows="4" data-column="cert_comment" class="form-control form-group saiiServiceCommentAdd" placeholder="Add Comment"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addRCServiceComment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">SP Service</h4>
            </div>
            <form action="saveComments.php" method="post"
                  enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control" style="padding-top: 10px;">
                            <label for="">Certification Comment</label>
                            <textarea type="text" name="rcServiceCommentAdd" rows="4" data-column="cert_comment" class="form-control form-group rcServiceCommentAdd" placeholder="Add Comment"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addSPServiceComment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">SP Service</h4>
            </div>
            <form action="saveComments.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control" style="padding-top: 10px;">
                            <label for="">Certification Comment</label>
                            <textarea type="text" name="spServiceCommentAdd" rows="4" data-column="cert_comment" class="form-control form-group spServiceCommentAdd" placeholder="Add Comment"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Invoice</h4>
            </div>
            <form id="invoice-form" action="invoicePdf.php" method="post" target="_blank" enctype="multipart/form-data" onkeypress="return event.keyCode != 13;">
                <input type="hidden"  name="invoiceCustomerId" class="invoiceCustomerId" id="invoiceCustomerId" value="<?php echo $_GET['customerid'] ?>"> <!--<input type="hidden" name="invoiceFirmId" class="invoiceFirmId" id="invoiceFirmId" value="" />-->
                <input type="hidden" name="invoiceId" class="invoiceId" id="invoiceId"  value="">                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Company Name</label>
                            <select name="invoiceconsultingFirmName" class="form-control invoiceconsultingFirmName">
                                <option value="">Company Name</option>
                                <?php foreach ($allCustomers as $customer) { ?> 
                                    <option value="<?php echo $customer["id"] ?>"><?php echo $customer["customer_name"] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>Invoice Date</label>
                                    <input type="text" class="invoiceDt" id="invoiceDt" name="invoiceDt" placeholder="Invoice Date" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <div id="invoiceDtError" style="display: none">
                                        <small><span  id="invoiceDtErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control">
                            <label>Company Address</label> 
                            <input type="text" class="invoiceAddress" id="invoiceAddress" name="invoiceAddress" placeholder="Company Address"  required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label>State</label> 
                            <select class="form-control invoiceState" id="invoiceState" name="invoiceState">
                                <option value="">Select State</option>
                                <?php foreach ($indianGstStates as $sName => $sCode) { ?>
                                    <option value="<?php echo htmlspecialchars($sName); ?>" data-code="<?php echo $sCode; ?>" <?php echo ($sName === 'Maharashtra') ? 'selected' : ''; ?>><?php echo htmlspecialchars($sName); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <label>State Code</label>
                            <input type="text" class="invoiceStateCode" id="invoiceStateCode" name="invoiceStateCode" placeholder="State Code" value="27">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Agreement No</label> 
                            <input type="text" class="invoiceAgreementNo" id="invoiceAgreementNo" name="invoiceAgreementNo" placeholder="Agreement No" value="">
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <label>P.O Ref:</label>
                            <input type="text" class="invoicePoRef" id="invoicePoRef" name="invoicePoRef" placeholder="P.O Ref (Optional)" value="">
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn generalAnalyse">
                            <span><i class="fa ">Add Services</i></span>
                            <a  class="addServiceAmtRow pull-right" id='addServiceAmtRow' style="cursor: pointer;"><i style="margin-top: 9px; color: white;" class="glyphicon glyphicon-plus-sign" title="Add new Service"></i></a>
                        </div>
                    </div>
                    <div class="row" style="padding-top: 10px;">
                        <div class="col-sm-12">
                            <label style="font-weight: 600;">
                                <input type="checkbox" class="includeQtyRate" name="includeQtyRate" value="1"> Include Quantity & Rate
                            </label>
                        </div>
                    </div>
                    <div class='serviceAmtTd'>
                        <div class="row invoiceService invoiceServiceRow" style="padding-top: 10px;">
                            <div class="col-sm-8 form-group float-label-control invoiceServiceCol">
                                <div class="input-group dropdown">
                                    <label for="">Service</label>
                                    <input type="text" class="form-control invoiceService dropdown-toggle" name="invoiceService[]" value="" placeholder="Service">
                                    <ul class="dropdown-menu">
                                        <?php foreach ($aitfservices as $service) { ?>
                                            <li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li>
                                        <?php } ?>
                                    </ul>
                                    <span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span>
                                </div>
                            </div>
                            <div class="col-sm-4 form-group float-label-control invoiceAmountCol" style="padding-top: 10px;">
                                <label for="">Amount</label> 
                                <input type="number" name="invoiceServiceAmt[]" class="form-control form-group invoiceServiceAmt" id="invoiceService" placeholder="Amount">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Net Price</label>
                            <input type="number" class="netPrice" id="netPrice"  placeholder="Net Price" name="netPrice" readonly>
                        </div>
                        <div class="col-sm-6">
                            <label class="row">Select CGST+SGST / IGST</label>
                            <div class="form-group">
                                <div class="col-sm-6">
                                    <label class="radio-inline">
                                        <input name="taxtype" class="taxtype" id="taxtype" value="CGST & SGST" type="radio" checked>CGST+SGST
                                    </label>
                                </div>
                                <div class="col-sm-6">
                                    <label class="radio-inline">
                                        <input name="taxtype" class="taxtype" id="taxtype" value="IGST" type="radio" >IGST
                                    </label>
                                </div>
                            </div>

                            <!--                            <label>Select CGST+SGST / IGST</label> 
                                                        <select name="taxtype" class="form-control taxtype" id="taxtype">
                                                            <option value="">Select Tax</option>
                                                            <option value="CGST & SGST">CGST+SGST</option>
                                                            <option value="IGST">IGST</option>
                                                        </select>-->
                            <!--<input type="text" min="1" class="krushiKalyan"  placeholder="IGST@9%" name="krushiKalyan">-->
                        </div>
                    </div>
                    <br>
                    <div class="row ">
                        <div class="col-sm-4 form-group float-label-control">
                            <label>IGST 18%</label> 
                            <input type="text" class="igst" placeholder="IGST 18%"  name="igst" readonly>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>CGST 9%</label>
                            <input type="text" class="cgst" id="cgst" placeholder="CGST 9%" name="cgst" readonly>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>SGST 9%</label> 
                            <input type="text" class="sgst" id="sgst" placeholder="SGST 9%" name="sgst" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-xs btn-default calculateGST">Reset GST</button>
                    <button type="button" class="btn btn-xs btn-info invoiceGenerate" disabled>Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="finalinvoiceModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Final Invoice</h4>
            </div>
            <form id="final-invoice-id" action="finalInvoicePDF.php" method="post" target="_blank" enctype="multipart/form-data" onkeypress="return event.keyCode != 13;">
                <input type="hidden"  name="invoiceCustomerId" class="invoiceCustomerId" id="invoiceCustomerId" value="<?php echo $_GET['customerid'] ?>" /> <!--<input type="hidden" name="invoiceFirmId" class="invoiceFirmId" id="invoiceFirmId" value="" />-->
                <input type="hidden" name="invoiceId" class="invoiceId" id="invoiceId"  value="" />                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Company Name</label>
                            <select name="invoiceconsultingFirmName" class="form-control invoiceconsultingFirmName">
                                <option value="">Company Name</option>
                                <?php foreach ($allCustomers as $customer) { ?> 
                                    <option value="<?php echo $customer["id"] ?>"><?php echo $customer["customer_name"] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>Invoice Date</label>
                                    <input type="text" class="invoiceDt" id="invoiceDt" name="invoiceDt" placeholder="Invoice Date" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <div id="invoiceDtError" style="display: none">
                                        <small><span  id="invoiceDtErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control">
                            <label>Company Address</label> 
                            <input type="text" class="invoiceAddress" id="invoiceAddress" name="invoiceAddress" placeholder="Company Address"  required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label>State</label> 
                            <select class="form-control invoiceState" id="finalInvoiceState" name="invoiceState">
                                <option value="">Select State</option>
                                <?php foreach ($indianGstStates as $sName => $sCode) { ?>
                                    <option value="<?php echo htmlspecialchars($sName); ?>" data-code="<?php echo $sCode; ?>" <?php echo ($sName === 'Maharashtra') ? 'selected' : ''; ?>><?php echo htmlspecialchars($sName); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <label>State Code</label>
                            <input type="text" class="invoiceStateCode" id="finalInvoiceStateCode" name="invoiceStateCode" placeholder="State Code" value="27">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Agreement No</label> 
                            <input type="text" class="invoiceAgreementNo" id="finalInvoiceAgreementNo" name="invoiceAgreementNo" placeholder="Agreement No" value="">
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <label>P.O Ref:</label>
                            <input type="text" class="invoicePoRef" id="finalInvoicePoRef" name="invoicePoRef" placeholder="P.O Ref (Optional)" value="">
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn generalAnalyse">
                            <span><i class="fa  ">Add Services</i></span>
                            <a  class="addFinalServiceAmtRow pull-right" id='addFinalServiceAmtRow' style="cursor: pointer;"><i style="margin-top: 9px; color: white;" class="glyphicon glyphicon-plus-sign" title="Add new Service"></i></a>                            
                        </div>
                    </div>
                    <div class="row" style="padding-top: 10px;">
                        <div class="col-sm-12">
                            <label style="font-weight: 600;">
                                <input type="checkbox" class="includeFinalQtyRate" name="includeFinalQtyRate" value="1"> Include Quantity & Rate
                            </label>
                        </div>
                    </div>
                    <div class='finalserviceAmtTd'>
                        <!--                        <div class="row invoiceService" style="padding-top: 10px;">
                                                    <div class="col-sm-8 form-group float-label-control">
                                                        <label for="">Service</label>
                                                        <select name="finalInvoiceService[]" class="form-control finalInvoiceService" id="finalInvoiceService">
                                                            <option value="">Select Service</option>
                        <?php foreach ($aitfservices as $service) { ?> 
                                                                                                <option value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></option>
                        <?php } ?>
                                                        </select>
                                                            <input type="text" name="invoiceService[]" class="form-control form-group serviceInvoiceString" id="invoiceService" placeholder="Service">
                                                    </div>
                                                    <div class="col-sm-4 form-group float-label-control" style="padding-top: 10px;">
                                                        <label for="">Amount</label> 
                                                        <input type="number" min="1" name="finalInvoiceServiceAmt[]" class="form-control form-group finalInvoiceServiceAmt" id="finalInvoiceServiceAmt" placeholder="Amount">
                                                    </div>
                                                </div>-->
                    </div>
                    <div class="row ">
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Net Price</label>
                            <input type="number" class="finalNetPrice" id="finalNetPrice"  placeholder="Net Price" name="finalNetPrice" readonly>
                        </div>
                        <div class="col-sm-6 form-group ">
                            <label class="row">Select CGST+SGST / IGST</label>
                            <div class="form-group">
                                <div class="col-sm-6">
                                    <label class="radio-inline">
                                        <input name="finalTaxtype" class="finalTaxtype" id="finalTaxtype" value="CGST & SGST" type="radio">CGST+SGST
                                    </label>
                                </div>
                                <div class="col-sm-6">
                                    <label class="radio-inline">
                                        <input name="finalTaxtype" class="finalTaxtype" id="finalTaxtype" value="IGST" type="radio" >IGST
                                    </label>
                                </div>
                            </div>

                            <!--                            <label>Select CGST+SGST / IGST</label> 
                                                        <select name="finaltaxtype" class="form-control finaltaxtype" id="finaltaxtype">
                                                            <option value="">Select Tax</option>
                                                            <option value="CGST & SGST">CGST+SGST</option>
                                                            <option value="IGST">IGST</option>
                                                        </select>-->
                                                        <!--<input type="text" min="1" class="krushiKalyan"  placeholder="IGST@9%" name="krushiKalyan">-->
                        </div>
                    </div>

                    <div class="row ">
                        <div class="col-sm-4 form-group float-label-control">
                            <label>IGST 18%</label> 
                            <input type="text" class="finaligst" id="finaligst" placeholder="IGST 18%" name="finaligst" readonly>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>CGST 9%</label> 
                            <input type="text" class="finalcgst" id="finalcgst" placeholder="CGST 9%" name="finalcgst" readonly>
                        </div>
                        <div class="col-sm-4 form-group float-label-control">
                            <label>SGST 9%</label> 
                            <input type="text" class="finalsgst" id="finalsgst" placeholder="SGST 9%" name="finalsgst" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-xs btn-default calculateFinalGST">Reset GST</button>
                    <button type="button" class="btn btn-xs btn-info fianlInvoiceGenerate" disabled="">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="quotation" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Quotation</h4>
            </div>
            <form action="quotationPdf.php" method="post" target="_blank" enctype="multipart/form-data">
                <input type="hidden" name="quoteCustomerId" id="quoteCustomerId" value="<?php echo $_GET['customerid'] ?>" /> <!--<input type="hidden" name="consultingFirmId" id="consultingFirmId" value="" />-->
                <input type="hidden" name="quotationId" class="quotationId" id="quotationId" value="" />
                <div class="modal-body">
                    <div class="row"><!--<div class="col-sm-6 form-group float-label-control">
                                                    <label>Consulting Firm</label>
                                                    <input type="text" class="quoteconsultingFirmName" id="consultingFirmName" name="consultingFirmName" placeholder="Consulting Firm" required>
                                                </div>-->
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Quotation Date</label> 
                            <input type="text" class="quotationDt" id="quotationDt" name="quotationDt" placeholder="Quotation Date" required>
                        </div>
                    </div>
                    <!--                    <div class="row">
                                                                                            <div class="col-sm-6 form-group float-label-control">
                                                                                                <label>Quotation No.</label>
                                                                                                <input type="text" class="quotationNo" id="quotationNo" name="quotationNo" placeholder="Quotation No." required>
                                                                                            </div>
                                                                <div class="col-sm-6 form-group float-label-control">
                                                                    <label>Service</label>
                                                                    <input type="text" class="serviceString" id="serviceString" name="serviceString" placeholder="Service" required>
                                                                </div>
                                                            </div>-->
                    <div class="row ">
                        <div class="col-sm-12 form-group float-label-control">
                            <label>Application:(Fee Covers Administration Costs & is non - refundable).</label> 
                            <input type="number" min="1" class="feeCovers" id="feeCovers" name="feeCovers" placeholder="Application: (Fee Covers Administration Costs & is non - refundable).">
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-sm-12 form-group float-label-control">
                            <label>Initial Audit (Stage 1) - Documentation Review and/or Adequacy Audit</label> 
                            <input type="number" min="1" class="stage1Audit" id="stage1Audit" name="stage1Audit" placeholder="Initial Audit (Stage 1) - Documentation Review and/or Adequacy Audit">
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-sm-12 form-group float-label-control">
                            <label>Initial Audit (Stage 2) – Certification Audit</label> 
                            <input type="number" min="1" class="stage2Audit" id="stage2Audit" name="stage2Audit" placeholder="Initial Audit (Stage 2) - Certification Audit">
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-sm-12 form-group float-label-control">
                            <label>Registration amount of certification</label> 
                            <input type="number" min="1" id="maintenanceAmt" class="maintenanceAmt" placeholder="Maintenance of Certification" name="maintenanceAmt">
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn generalAnalyse">
                            <span><i class="fa  ">Add Additional Conditions</i></span>
                            <a class="addConditionRow pull-right" id='addConditionRow' style="cursor: pointer;"><i style="margin-top: 9px; color: white;" class="glyphicon glyphicon-plus-sign" title="Add new Condition"></i></a>
                        </div>
                    </div>
                    <div class='conditionDescTd'>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control" style="padding-top: 10px;">
                                <label for="">Condition Details</label> 
                                <textarea name="conditionDescription" rows="2" class="form-control form-group conditionDescription" id="conditionDescription" placeholder="Condition Description"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="conditionsHidden" id="conditionsHidden" name="conditionsHidden" value="">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-info generateQuotation">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="consultingFirmAdd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">AITF MCL</h4>
            </div>
            <form action="addConsultingFirm.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>Consulting Firm Name</label> 
                                    <input type="text" class="consultingFirmName" id="consultingFirmName" name="consultingFirmName" placeholder="Firm Name">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <div id="consultingFirmNameError" style="display: none">
                                        <small><span id="consultingFirmNameErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>Email Id</label>
                                    <input type="email" class="consultingFirmEmail" id="consultingFirmEmail" name="consultingFirmEmail" placeholder="Consulting Firm Email">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <div id="consultingFirmEmailError" style="display: none">
                                        <small><span id="consultingFirmEmailErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control firmLogoImg" id="firmLogoImg">
                                    <input type="file" name="firmLogo" id="firmLogo" class="form-control firmLogo" accept="image/gif, image/jpeg, image/png" onchange="readURL(this)" src="" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <div id="firmLogoError" style="display: none">
                                        <small><span id="firmLogoErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label for="">Firm Address</label> 
                                    <textarea name="firmAddress" rows="4" class="form-control form-group firmAddress" id="firmAddress" placeholder="Firm Address"></textarea>
                                </div>
                            </div>                            
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <img id="firmImg" alt="" src="" class="img-responsive img-thumbnail">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control" >
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>Firm Contact No.</label> 
                                    <input type="text" class="firmContact" id="firmContact" name="firmContact" placeholder="Firm Contact No.">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <div id="firmContactError" style="display: none">
                                        <small><span id="firmContactErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>Invoice Sign</label> 
                                    <input type="file" name="invoiceSign" id="invoiceSign" class="form-control invoiceSign" accept="image/gif, image/jpeg, image/png" placeholder="Invoice Sign" onchange="readURLSign(this)" src="" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 form-group float-label-control">
                                    <label>Pan No.</label>
                                    <input type="text" class="panNo" id="panNo" name="panNo" placeholder="Pan No.">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <img id="invoice-sign-img" alt="" src="" class="img-responsive img-thumbnail">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label for="">SVC Code</label> 
                            <input type="text" class="svcCode" id="svcCode" name="svcCode" placeholder="SVC Code">
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <label for="">CIN No.</label> 
                            <input type="number" min="1" class="cinNo" id="cinNo" name="cinNo" placeholder="CIN No.">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label for="">Bank Name</label> 
                            <input type="text" class="bankName" id="bankName" name="bankName" placeholder="Bank Name">
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <label for="">Bank Account No.</label> 
                            <input type="number" min="1" class="bankAcNo" id="bankAcNo" name="bankAcNo" placeholder="Bank Account No.">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label for="">Bank Address</label> 
                            <textarea name="bankAddress" rows="2" class="form-control form-group bankAddress" id="bankAddress" placeholder="Bank Address"></textarea>                           
                        </div>

                        <div class="col-sm-6 form-group float-label-control">
                            <label>IFSC Code</label>
                            <input type="text" class="ifscCode" id="ifscCode" name="ifscCode" placeholder="IFSC code" required="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label>MICR No.</label>
                            <input type="text" class="micrNo" id="micrNo" name="micrNo" placeholder="MICR No." required="">
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Firm Abbreviation</label> 
                            <input type="text" class="firmAbbreviation" id="firmAbbreviation" name="firmAbbreviation" placeholder="Firm Abbreviation" required="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3 form-group float-label-control">
                            <label>Proforma Prefix</label> 
                            <input type="text" class="profarmaPrefix" id="profarmaPrefix" name="profarmaPrefix" placeholder="Proforma Prefix" required="">
                        </div>
                        <div class="col-sm-3 form-group float-label-control">
                            <label>Proforma Invoice No.</label> 
                            <input type="number" min="1" class="firmInvoice" id="firmInvoice" name="firmInvoice" placeholder="Proforma Invoice No." required="">
                        </div>
                        <div class="col-sm-3 form-group float-label-control">
                            <label>Final Prefix</label> 
                            <input type="text" class="finalPrefix" id="finalPrefix" name="finalPrefix" placeholder="Final Prefix" required="">
                        </div>
                        <div class="col-sm-3 form-group float-label-control">
                            <label>Final Invoice No.</label> 
                            <input type="number" class="firmInvoiceFinal" id="firmInvoiceFinal" name="firmInvoiceFinal" placeholder="Final Invoice No." required="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Firm Quotation No.</label> 
                            <input type="number" class="firmQuotation" id="firmQuotation" name="firmQuotation" placeholder="Firm Quotation No." required="">
                        </div>
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn generalAnalyse">
                            <span><i class="fa  ">Add Firm Services</i></span> 
                            <a class="addnewrow pull-right" id='addNewFirmDesc' style="cursor: pointer;"><i style="margin-top: 9px; color: white;"class="glyphicon glyphicon-plus-sign" title="Add new firm description"></i></a>
                        </div>
                    </div>
                    <div class='serviceDescTd'>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control">
                                <label for="">Service  Description</label> 
                                <input type="text" name="serviceDescription" rows="2" class="form-control form-group serviceDescription"  id="serviceDescription" placeholder="Service Description">
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="firmServicesHidden" id="firmServicesHidden"  name="firmServicesHidden" value=""> 
                <input type="hidden"  class="firmBankAbbrHidden" id="firmBankAbbrHidden" name="firmBankAbbrHidden" value=""> 
                <input type="hidden" class="firmId" id="firmId" name="firmId" value="">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-info addConsultingFirm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="changeUserPassword" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Change Password</h4>
            </div>
            <form action="changePassword.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control" style="padding-top: 10px;">
                            <label for="">Old Password</label> 
                            <input type="text" name="oldPassword" class="form-control form-group oldPassword" id="oldPassword" placeholder="Old Password">
                            <div id="oldPasswordError" style="display: none">
                                <small><span id="oldPasswordErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control" style="padding-top: 10px;">
                            <label for="">New Password</label> 
                            <input type="text" name="newPassword" class="form-control form-group newPassword" id="newPassword" placeholder="New Password">
                            <div id="newPasswordError" style="display: none">
                                <small><span id="newPasswordErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control" style="padding-top: 10px;">
                            <label for="">Old Password</label> 
                            <input type="text" name="confirmPassword" class="form-control form-group confirmPassword" id="confirmPassword" placeholder="Confirm Password">
                            <div id="confirmPasswordError" style="display: none">
                                <small><span id="confirmPasswordErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="user_id" class="user_id" id="user_id" value="<?php echo $_SESSION['id']; ?>"></div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-info changePassword">Change</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    //Avoid to add space in GSTIN no.
    function AvoidSpace(event) {
        var k = event ? event.which : window.event.keyCode;
        if (k == 32)
            return false;
    }

    $(function () {
        $('body').on("click", ".dropdown-menu a", function () {
//        $('.dropdown-menu a').click(function () {
            console.log($(this).attr('data-value'));
            $(this).closest('.dropdown').find('input.invoiceService').val($(this).attr('data-value'));
        });
    });

    $(function () {
        $('body').on("click", ".dropdown-menu a", function () {
//        $('.dropdown-menu a').click(function () {
            console.log($(this).attr('data-value'));
            $(this).closest('.dropdown').find('input.finalInvoiceService').val($(this).attr('data-value'));
        });
    });

    $(function () {
        $('#quotationDt').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#quotationDt").keydown(function (e) {
//            e.preventDefault();
        });

        $('#invoiceDt').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#invoiceDt").keydown(function (e) {
//            e.preventDefault();
        });

        $(".newRegistrationDate").datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true,
            onSelect: function() {
                // Validate on date selection
                certificationDetails();
                // Trigger change event to generate allotment number
                $(this).trigger('change');
            }
        });
        $(".newRegistrationDate").keydown(function (e) {
            e.preventDefault();
        });
        $(".newRegistrationDate").on('change blur', function() {
            // Validate Agreement Date on change/blur
            if ($(this).val() != "") {
                certificationDetails();
            }
        });
        
        $('#certificatePrintModal').on('shown.bs.modal', function () {
            DateChronologyValidator.init({
                customerId: $('.csId').val() || '<?php echo isset($_GET["customerid"]) ? $_GET["customerid"] : "" ?>',
                typedServiceId: $(".service").val() || '<?php echo isset($_GET["service"]) ? $_GET["service"] : "" ?>',
                fields: {
                    'aggrement_date': { input: '.newRegistrationDate', errorDiv: '#newRegistrationDateError', errorMsg: '#newRegistrationDateErrorMsg' }
                },
                submitBtn: '.addCertificationForm', // Assuming submit button class
                formSelector: '#certificatePrintModal form'
            });
        });
    });
    
    function readURLSign(input) {
//            alert("in");
        if (input.files && input.files[0]) {
            var reader = new FileReader();
//               alert("in"+input.files);
            reader.onload = function (e) {
//                     alert(e.target.result);
                $('#invoice-sign-img')
                        .attr('src', e.target.result)
//                        .width(200)
//                        .height(200);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function readURL(input) {
//            alert("in");
        if (input.files && input.files[0]) {
            var reader = new FileReader();
//               alert("in"+input.files);
            reader.onload = function (e) {
//                     alert(e.target.result);
                $('#firmImg')
                        .attr('src', e.target.result)
                        .width(200)
                        .height(200);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function validateEmail(email) {
        var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
        return re.test(email);
    }

    function certificationDateValidate() {
        $isError = false;
        return $isError;
    }

    function validateTax() {
        $isError = false;
        if ($('.newTaxation').val() == "") {
            $('#newTaxationErrorMsg').text("The taxation can not be empty");
            $('#newTaxationError').show();
            $('#newTaxation').addClass('inputHighlight');
            $isError = true;
        } else if ($('.newTaxation').val() != "" && $('.taxation_id_update').val() == "") {
            $.ajax({
                type: "POST",
                url: "checkCustomer.php",
                data: {taxcation: $('.newTaxation').val()},
                async: false,
                success: function (data) {
//                    alert(data);
                    if (data == 1) {
                        $('#newTaxationErrorMsg').text("The taxation already exist.");
                        $('#newTaxationError').show();
                        $('#newTaxation').addClass('inputHighlight');
                        $isError = true;
                        mainErr = true;
                    } else {
                        $('#newTaxationError').hide();
                        $('#newTaxation').removeClass('inputHighlight');
                        mainErr = false;
                    }
                }
            });
        } else {
            $('#newTaxationError').hide();
            $('#newTaxation').removeClass('inputHighlight');
        }
        if ($('.newRate').val() == "") {
            $('#newRateErrorMsg').text("The taxation rate can not be empty");
            $('#newRateError').show();
            $('#newRate').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#newRateError').hide();
            $('#newRate').removeClass('inputHighlight');
        }
        return $isError;
    }

    function validateFirm() {
        $isError = false;
        if ($('.consultingFirmName').val() == "") {
            $('#consultingFirmNameErrorMsg').text("The consulting firm name can not be empty");
            $('#consultingFirmNameError').show();
            $('#consultingFirmName').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#consultingFirmNameError').hide();
            $('#consultingFirmName').removeClass('inputHighlight');
        }
        if ($('.consultingFirmEmail').val() != "" && !validateEmail($('.consultingFirmEmail').val()))
        {
            $('#consultingFirmEmailErrorMsg').text("The email address is not valid");
            $('#consultingFirmEmailError').show();
            $('.consultingFirmEmail').addClass('inputHighlight');
            $isError = true;
            //    alert("email not valid"+$error);
        } else {
            $('#consultingFirmEmailError').hide();
            $('.consultingFirmEmail').removeClass('inputHighlight');
        }
        if ($(".firmId").val() == "" && $('.firmLogo').val() == "") {
            $('#firmLogoErrorMsg').text("The consulting firm logo can not be empty");
            $('#firmLogoError').show();
            $('#firmLogo').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#firmLogoError').hide();
            $('#firmLogo').removeClass('inputHighlight');
        }
        var format = /^\+?([0-9]{8,})$/;
        if ($('.firmContact').val() == "") {
//            alert('in');
            $('#firmContactErrorMsg ').text("Consulting firm contact can not be empty");
            $('#firmContactError').show();
            $('.firmContact').addClass('inputHighlight');
            $isError = true;
        } else if ($('.firmContact').val() != "" && !$('.firmContact').val().match(format)) {
            $('#firmContactErrorMsg ').text("Please enter valid contact number");
            $('#firmContactError').show();
            $('.firmContact').addClass('inputHighlight');
            $isError = true;
        } else if ($('.firmContact').val().length != 10) {
            $('#firmContactErrorMsg ').text("Please enter contact number of 10 digit");
            $('#firmContactError').show();
            $('.firmContact').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#firmContactError').hide();
            $('.firmContact').removeClass('inputHighlight');
//            $error = true;
        }
        if ($('.firmAbbreviation').val() == "") {
//            alert('in');
            $('#firmAbbreviationErrorMsg ').text("Consulting firm abbreviation can not be empty");
            $('#firmAbbreviationError').show();
            $('.firmAbbreviation').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#firmAbbreviationError').hide();
            $('.firmAbbreviation').removeClass('inputHighlight');
//            $error = true;
        }
        if ($('.firmInvoice').val() == "") {
//            alert('in');
            $('#firmInvoiceErrorMsg ').text("Consulting firm invoice can not be empty");
            $('#firmInvoiceError').show();
            $('.firmInvoice').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#firmInvoiceError').hide();
            $('.firmInvoice').removeClass('inputHighlight');
//            $error = true;
        }
        if ($('.firmQuotation').val() == "") {
//            alert('in');
            $('#firmQuotationErrorMsg ').text("Consulting firm quotation can not be empty");
            $('#firmQuotationError').show();
            $('.firmQuotation').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#firmQuotationError').hide();
            $('.firmQuotation').removeClass('inputHighlight');
//            $error = true;
        }
        return $isError;
    }

    function validateChangePassword() {
        $isError = false;

        if ($('#oldPassword').val() == "") {
//            alert('in');
            $('#oldPasswordErrorMsg ').text("Old Password can not be empty");
            $('#oldPasswordError').show();
            $('#oldPassword').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#oldPasswordError').hide();
            $('#oldPassword').removeClass('inputHighlight');
        }
        if ($('#newPassword').val() == "") {
            $('#newPasswordErrorMsg').text("New password is required");
            $('#newPasswordError').show();
            $('#newPassword').addClass('inputHighlight');
            $isError = true;
//            alert("hi");
        } else if (!validatePassword($('#newPassword').val())) {
            $('#newPasswordErrorMsg').text("Password must contain at least 8 characters,contain at least 1 number,\n\
             contain at least 1 character (a-z A-Z), contains at least 1 special character");
            $('#newPasswordError').show();
            $('#newPassword').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#newPasswordError').hide();
            $('#newPassword').removeClass('inputHighlight');
        }
        if ($('#confirmPassword').val() == "") {
            $('#confirmPasswordErrorMsg').text("Confirm your password");
            $('#confirmPasswordError').show();
            $('#confirmPassword').addClass('inputHighlight');
            $isError = true;
//            alert("hi");
        } else if ($('#newPassword').val() != $('#confirmPassword').val()) {
            $('#confirmPasswordErrorMsg').text("Password not match");
            $('#confirmPasswordError').show();
            $('#confirmPassword').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#confirmPasswordError').hide();
            $('#confirmPassword').removeClass('inputHighlight');
        }
        return $isError;
    }

    function validateCustomer() {
        $isError = false;
        if ($('#customerName').val() == "")
        {
            $('#companyNameErrorMsg').text("The company or client name can not be empty");
            $('#companyNameError').show();
            $('#companyName').addClass('inputHighlight');
            $isError = true;
            //    alert("email not valid"+$error);
        } else if ($('#customerName').val() != "" && $('#contactNo').val() != "" && $('.editCustomerId').val() == "") {
//            alert($('.editCustomerId').val());
//            if ($('.editCustomerId') == "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {customerName: $('#customerName').val(),
                    customerphone: $('#contactNo').val()},

                async: false,
                success: function (data) {
//                    alert(data);
                    if (data == 1) {
                        $('#companyNameErrorMsg').text("The company or client name exist in the system");
                        $('#companyNameError').show();
                        $('#companyName').addClass('inputHighlight');
                        $isError = true;
                        mainErr = true;
                    } else {
                        $('#companyNameError').hide();
                        $('#customerName').removeClass('inputHighlight');
                        mainErr = false;
                    }
                }
            });
//            }
        } else {
            $('#companyNameError').hide();
            $('#customerName').removeClass('inputHighlight');
        }
        if ($('#customerEmail').val() != "" && !validateEmail($('#customerEmail').val()))
        {
            $('#contactEmailErrorMsg').text("The email address is not valid");
            $('#contactEmailError').show();
            $('#customerEmail').addClass('inputHighlight');
            $isError = true;
            //    alert("email not valid"+$error);
        } else {
            $('#contactEmailError').hide();
            $('#customerEmail').removeClass('inputHighlight');
        }
        var format = /^\+?([0-9]{8,})$/;
        if ($('#contactNo').val() == "") {
//            alert('in');
            $('#contactNoErrorMsg ').text("Customer contact can not be empty");
            $('#contactNoError').show();
            $('#contactNo').addClass('inputHighlight');
            $isError = true;
        } else if ($('#contactNo').val() != "" && !$('#contactNo').val().match(format)) {
            $('#contactNoErrorMsg ').text("Please enter valid contact number");
            $('#contactNoError').show();
            $('#contactNo').addClass('inputHighlight');
            $isError = true;
        } else if (($('#contactNo').val().length < 10) || ($('#contactNo').val().length > 11)) {
            $('#contactNoErrorMsg ').text("Please enter contact number of 10 or 11 digits");
            $('#contactNoError').show();
            $('#contactNo').addClass('inputHighlight');
            $isError = true;
        } else if ($('#contactNo').val() != "" && $('#customerName').val() != "" && $('.editCustomerId').val() == "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {contactNo: $('#contactNo').val(),
                    customername: $('#customerName').val()},

                async: false,
                success: function (data) {
//                    alert(data);
                    if (data == 1) {
                        $('#contactNoErrorMsg').text("The company or client with this contact number is exist in the system");
                        $('#contactNoError').show();
                        $('#contactNo').addClass('inputHighlight');
                        $isError = true;
                        mainErr = true;
                    } else {
                        $('#contactNoError').hide();
                        $('#contactNo').removeClass('inputHighlight');
                        mainErr = false;
                    }
                }
            });
        } else {
            $('#contactNoError').hide();
            $('#contactNo').removeClass('inputHighlight');
//            $error = true;
        }
        return $isError;
    }

    function invoiceDate() {
//        alert(moment($('.invoiceDt').val(), "DD/MM/YYYY")+" prev date "+ moment($('.previnvdate').val(), "DD/MM/YYYY"));       
        $isError = false;
        if ($('.invoiceDt').val() == "") {
            $('#invoiceDtErrorMsg').text("Invoice date can not be empty");
            $('#invoiceDtError').show();
            $('#invoiceDt').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#invoiceDtError').hide();
            $('#invoiceDt').removeClass('inputHighlight');
        }
        return $isError;
    }

    function certificationDetails() {
        $isError = false;
        if ($(".newRegistrationDate").val() == "") {
            $('#newRegistrationDateErrorMsg').text("Agreement Date cannot be empty");
            $('#newRegistrationDateError').show();
            $('#newRegistrationDate').addClass('inputHighlight');
            $isError = true;
        } else {
            // Let the Chronology validator handle it
            if (DateChronologyValidator.validateAll()) {
                $isError = true;
            } else {
                $('#newRegistrationDateError').hide();
                $('#newRegistrationDate').removeClass('inputHighlight');
            }
        }
        return $isError;
    }

    $(document).ready(function () {

        $(document).on("change", ".finalInvoiceServiceAmt", function () {
            var sum = 0;
//            var taxrate = <?php echo $csgttax ?>;
            $(".finalInvoiceServiceAmt").each(function () {
                sum += +$(this).val();
            });
//            alert(<?php echo $csgttax ?>);
            $(".finalNetPrice").val(sum);
            $sumofsvc = $(".finalNetPrice").val();
//            alert($("input[name=finalTaxtype]:checked").val());
            $taxtype = $("input[name=finalTaxtype]:checked").val();
            if ($taxtype == "CGST & SGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In cgst <- " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "CGST") {
                                $sbTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".finalcgst").val($sbTax);
                                $(".finalsgst").val($sbTax);
                                $(".finaligst").val("");
                            }
                        }
                        $('.finalcgst').trigger('change');
                        $('.finalsgst"]').trigger('change');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });
            } else if ($taxtype == "IGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In igst -> " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "IGST") {
                                $kkTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".finaligst").val($kkTax);
                                $(".finalcgst").val("");
                                $(".finalsgst").val("");
                            }
                        }
                        $('.finaligst').trigger('change');
                    },
                    error: function (data, status, err)
                    {
//                        alert(status);
                    }
                });
            }
        });

        $(document).on("change", ".invoiceServiceAmt", function () {
            var sum = 0;
//            var taxrate = <?php echo $csgttax ?>;
            $(".invoiceServiceAmt").each(function () {
                sum += +$(this).val();
            });
//            alert(<?php echo $csgttax ?>);
            $(".netPrice").val(sum);
            $sumofsvc = $(".netPrice").val();
//            alert($('.taxtype').val());("input[name=taxtype]:checked")
            $taxtype = $("input[name=taxtype]:checked").val();
            if ($taxtype == "CGST & SGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In cgst <- " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "CGST") {
                                $sbTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".cgst").val($sbTax);
                                $(".sgst").val($sbTax);
                                $(".igst").val("");
                            }
                        }
                        $('.cgst').trigger('change');
                        $('.sgst"]').trigger('change');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });
            } else if ($taxtype == "IGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In igst -> " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "IGST") {
                                $kkTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".igst").val($kkTax);
                                $(".cgst").val("");
                                $(".sgst").val("");
                            }
                        }
                        $('.igst').trigger('change');
                    },
                    error: function (data, status, err)
                    {
//                        alert(status);
                    }
                });
            }
        });

        function myFunction() {
            var texts = document.getElementsById("invoiceService");
            var sum = 0;
            for (var i = 0; i < texts.length; i++) {
                alert(texts.length);
                var aa = parseFloat(texts[i].value);
                if (aa == "NaN" || aa == null || aa == "") {
                    aa = parseFloat("0");
                }
                sum = sum + aa;
            }
            document.getElementById("netPrice").innerHTML = sum;
        }

//        $('.finalInvoiceServiceAmt').each(function () {
//            alert("in");
//        });
//        var sum = 0;
//        $('body').on("change", ".invoiceServiceAmt", function () {
//
////            $('.finalInvoiceServiceAmt').each(function () {
//            sum = sum + parseInt($(this).val());
////            });
////            alert(document.getElementsByName("invoiceService"));
////            var texts = document.getElementsByName("invoiceService");
////            alert(texts.length);
////            var sum = "";
////            for (var i = 0; i < 10; i++) {
////                alert("in for");
////                var n = texts[i].value || 0;
////                sum = sum + n;
////            }
//            $(".netPrice").val(sum);
////            sum = 0;
//        });

        $(".profarmaPrefix").keypress(function () {
            $(".firmInvoice").val('1');
        });

        $(".finalPrefix").keypress(function () {
            $(".firmInvoiceFinal").val('1');
        });

        $('.invoiceconsultingFirmName').change(function () {
            $customerid = $(this).val();
            var $modal = $(this).closest('.modal');
            $.ajax({
                url: "addComments.php",
                type: 'GET',
                dataType: "json",
                data: {
                    custid: $customerid
                },
                success: function (data) {
                    if (data) {
                        $modal.find('.invoiceAddress, #invoiceAddress').val(data["address"] || '');
                        $modal.find(".invoiceCustomerId").val(data["id"] || '');
                        if (data["agreement_no"]) {
                            $modal.find('.invoiceAgreementNo, input[name="invoiceAgreementNo"]').val(data["agreement_no"]);
                        }
                        if (data["state"]) {
                            var custState = data["state"].trim();
                            $modal.find('.invoiceState').val(custState);
                            if (!$modal.find('.invoiceState').val()) {
                                $modal.find('.invoiceState option').filter(function () {
                                    return $(this).text().toLowerCase().trim() === custState.toLowerCase();
                                }).prop('selected', true);
                            }
                            $modal.find('.invoiceState').trigger('change');
                        }
                    }
                },
                error: function (data, status, err) {
                    alert(status);
                }
            });
        });

        var indianStateCodeMap = {
            'andaman and nicobar islands': '35',
            'andhra pradesh': '37',
            'arunachal pradesh': '12',
            'assam': '18',
            'bihar': '10',
            'chandigarh': '04',
            'chhattisgarh': '22',
            'dadra and nagar haveli and daman and diu': '26',
            'daman and diu': '26',
            'dadra and nagar haveli': '26',
            'delhi': '07',
            'goa': '30',
            'gujarat': '24',
            'haryana': '06',
            'himachal pradesh': '02',
            'jammu and kashmir': '01',
            'jharkhand': '20',
            'karnataka': '29',
            'kerala': '32',
            'ladakh': '38',
            'lakshadweep': '31',
            'madhya pradesh': '23',
            'maharashtra': '27',
            'manipur': '14',
            'meghalaya': '17',
            'mizoram': '15',
            'nagaland': '13',
            'odisha': '21',
            'orissa': '21',
            'puducherry': '34',
            'pondicherry': '34',
            'punjab': '03',
            'rajasthan': '08',
            'sikkim': '11',
            'tamil nadu': '33',
            'telangana': '36',
            'tripura': '16',
            'uttar pradesh': '09',
            'uttarakhand': '05',
            'uttaranchal': '05',
            'west bengal': '19',
            'other territory': '97'
        };

        $(document).on('change', '.invoiceState', function () {
            var $modal = $(this).closest('.modal, form');
            var selectedOpt = $(this).find('option:selected');
            var code = selectedOpt.attr('data-code');
            if (!code) {
                var stateName = ($(this).val() || '').toLowerCase().trim();
                code = indianStateCodeMap[stateName] || '';
            }
            if (code) {
                $modal.find('.invoiceStateCode, input[name="invoiceStateCode"]').val(code).trigger('change');
            }
        });

        $(".finaltaxtype").change(function () {
            $sumofsvc = $(".finalNetPrice").val();
//            alert($(this).val());
            $taxtype = $(this).val();
            if ($taxtype == "CGST & SGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In cgst <- " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "CGST") {
                                $sbTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".finalcgst").val($sbTax);
                                $(".finalsgst").val($sbTax);
                                $(".finaligst").val("");
                            }
                        }
                        $('.finalcgst').trigger('change');
                        $('.finalsgst"]').trigger('change');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });

            } else if ($taxtype == "IGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In igst -> " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "IGST") {
                                $kkTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".finaligst").val($kkTax);
                                $(".finalcgst").val("");
                                $(".finalsgst").val("");
                            }
                        }
                        $('.finaligst').trigger('change');
                    },
                    error: function (data, status, err)
                    {
//                        alert(status);
                    }
                });
            }
        });

        $('.taxtype').click(function () {
//            $sumofsvc = 0;
//            $('.invoiceServiceAmt').each(function () {
//                $sumofsvc = $sumofsvc + parseInt($(this).val());
//            });
//            $(".netPrice").val($sumofsvc);
            $sumofsvc = $(".netPrice").val();
//            alert($(this).val());
            $taxtype = $(this).val();
            if ($taxtype == "CGST & SGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In cgst <- " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "CGST") {
                                $sbTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".cgst").val($sbTax);
                                $(".sgst").val($sbTax);
                                $(".igst").val("");
                            }
                        }
                        $('.cgst').trigger('change');
                        $('.sgst"]').trigger('change');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });

            } else if ($taxtype == "IGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In igst -> " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "IGST") {
                                $kkTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".igst").val($kkTax);
                                $(".cgst").val("");
                                $(".sgst").val("");
                            }
                        }
                        $('.igst').trigger('change');
                    },
                    error: function (data, status, err)
                    {
//                        alert(status);
                    }
                });
            }
        });

        $('.invoiceGenerate').click(function (e) {

            $sumofsvc = 0;
            $('.invoiceServiceAmt').each(function () {
                $sumofsvc = $sumofsvc + (+($(this).val() || 0));
            });
            $(".netPrice").val($sumofsvc);

            $sumofsvc = $(".netPrice").val();
            $taxtype = $("input[name=taxtype]:checked").val();
//            alert($(this).val());
//            $taxtype = $(this).val();
            if ($taxtype == "CGST & SGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In cgst <- " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "CGST") {
                                $sbTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".cgst").val($sbTax);
                                $(".sgst").val($sbTax);
                                $(".igst").val("");
                            }
                        }
                        $('.cgst').trigger('change');
                        $('.sgst"]').trigger('change');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });

            } else if ($taxtype == "IGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In igst -> " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "IGST") {
                                $kkTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".igst").val($kkTax);
                                $(".cgst").val("");
                                $(".sgst").val("");
                            }
                        }
                        $('.igst').trigger('change');
                    },
                    error: function (data, status, err)
                    {
//                        alert(status);
                    }
                });
            }

            if (invoiceDate()) {
                e.preventDefault();
                return;
            }

            document.getElementById("invoice-form").submit();
//            $('#invoiceModal').modal('hide');
        });

        $(".fianlInvoiceGenerate").click(function () {

            var sum = 0;
//            var taxrate = <?php echo $csgttax ?>;
            $(".finalInvoiceServiceAmt").each(function () {
                sum = sum + (+($(this).val() || 0));
            });
//            alert(<?php echo $csgttax ?>);
            $(".finalNetPrice").val(sum);
            $sumofsvc = $(".finalNetPrice").val();
//            alert($("input[name=finalTaxtype]:checked").val());
            $taxtype = $("input[name=finalTaxtype]:checked").val();
            if ($taxtype == "CGST & SGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In cgst <- " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "CGST") {
                                $sbTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".finalcgst").val($sbTax);
                                $(".finalsgst").val($sbTax);
                                $(".finaligst").val("");
                            }
                        }
                        $('.finalcgst').trigger('change');
                        $('.finalsgst"]').trigger('change');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });
            } else if ($taxtype == "IGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In igst -> " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "IGST") {
                                $kkTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".finaligst").val($kkTax);
                                $(".finalcgst").val("");
                                $(".finalsgst").val("");
                            }
                        }
                        $('.finaligst').trigger('change');
                    },
                    error: function (data, status, err)
                    {
//                        alert(status);
                    }
                });
            }
            if ($(".finalNetPrice").val() != "") {
                $(".fianlInvoiceGenerate").removeAttr('disabled');
            }
            document.getElementById("final-invoice-id").submit();
            $("#finalinvoiceModal").modal("hide");
        });

        $(".finalNetPrice").click(function () {
            var sum = 0;
//            var taxrate = <?php echo $csgttax ?>;
            $(".finalInvoiceServiceAmt").each(function () {
                sum = sum + (+($(this).val() || 0));
            });
//            alert(<?php echo $csgttax ?>);
            $(".finalNetPrice").val(sum);
            $sumofsvc = $(".finalNetPrice").val();
//            alert($("input[name=finalTaxtype]:checked").val());
            $taxtype = $("input[name=finalTaxtype]:checked").val();
            if ($taxtype == "CGST & SGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In cgst <- " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "CGST") {
                                $sbTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".finalcgst").val($sbTax);
                                $(".finalsgst").val($sbTax);
                                $(".finaligst").val("");
                            }
                        }
                        $('.finalcgst').trigger('change');
                        $('.finalsgst"]').trigger('change');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });
            } else if ($taxtype == "IGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In igst -> " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "IGST") {
                                $kkTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".finaligst").val($kkTax);
                                $(".finalcgst").val("");
                                $(".finalsgst").val("");
                            }
                        }
                        $('.finaligst').trigger('change');
                    },
                    error: function (data, status, err)
                    {
//                        alert(status);
                    }
                });
            }
            $(".fianlInvoiceGenerate").removeAttr('disabled');
        });

        $(".netPrice").click(function () {
//            myFunction();
            $sumofsvc = 0;
            $('.invoiceServiceAmt').each(function () {
                $sumofsvc = $sumofsvc + (+($(this).val() || 0));
            });
            $(".netPrice").val($sumofsvc);

            $sumofsvc = $(".netPrice").val();
            $taxtype = $("input[name=taxtype]:checked").val();
//            alert($(this).val());
//            $taxtype = $(this).val();
            if ($taxtype == "CGST & SGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In cgst <- " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "CGST") {
                                $sbTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".cgst").val($sbTax);
                                $(".sgst").val($sbTax);
                                $(".igst").val("");
                            }
                        }
                        $('.cgst').trigger('change');
                        $('.sgst"]').trigger('change');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });

            } else if ($taxtype == "IGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In igst -> " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "IGST") {
                                $kkTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".igst").val($kkTax);
                                $(".cgst").val("");
                                $(".sgst").val("");
                            }
                        }
                        $('.igst').trigger('change');
                    },
                    error: function (data, status, err)
                    {
//                        alert(status);
                    }
                });
            }
            $(".invoiceGenerate").removeAttr('disabled');
        });

        $(".calculateGST").click(function () {
            $sumofsvc = 0;
            $('.invoiceServiceAmt').each(function () {
                $sumofsvc = $sumofsvc + (+($(this).val() || 0));
            });
            $(".netPrice").val($sumofsvc);

            $sumofsvc = $(".netPrice").val();
            $taxtype = $("input[name=taxtype]:checked").val();
//            alert($(this).val());
//            $taxtype = $(this).val();
            if ($taxtype == "CGST & SGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In cgst <- " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "CGST") {
                                $sbTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".cgst").val($sbTax);
                                $(".sgst").val($sbTax);
                                $(".igst").val("");
                            }
                        }
                        $('.cgst').trigger('change');
                        $('.sgst"]').trigger('change');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });

            } else if ($taxtype == "IGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In igst -> " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "IGST") {
                                $kkTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".igst").val($kkTax);
                                $(".cgst").val("");
                                $(".sgst").val("");
                            }
                        }
                        $('.igst').trigger('change');
                    },
                    error: function (data, status, err)
                    {
//                        alert(status);
                    }
                });
            }
            if ($(".netPrice").val() != "") {
                $(".invoiceGenerate").removeAttr('disabled');
            }
        });

        $(".calculateFinalGST").click(function () {
            var sum = 0;
//            var taxrate = <?php echo $csgttax ?>;
            $(".finalInvoiceServiceAmt").each(function () {
                sum = sum + (+($(this).val() || 0));
            });
//            alert(<?php echo $csgttax ?>);
            $(".finalNetPrice").val(sum);
            $sumofsvc = $(".finalNetPrice").val();
//            alert($("input[name=finalTaxtype]:checked").val());
            $taxtype = $("input[name=finalTaxtype]:checked").val();
            if ($taxtype == "CGST & SGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In cgst <- " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "CGST") {
                                $sbTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".finalcgst").val($sbTax);
                                $(".finalsgst").val($sbTax);
                                $(".finaligst").val("");
                            }
                        }
                        $('.finalcgst').trigger('change');
                        $('.finalsgst"]').trigger('change');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });
            } else if ($taxtype == "IGST") {
                $.ajax({
                    url: "getClientInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation: "taxation",
                    },
                    success: function (data)
                    {
                        for (var key in data) {
//                            alert("In igst -> " + data[key]['type_of_tax']);
                            if (data[key]['type_of_tax'] == "IGST") {
                                $kkTax = (($sumofsvc * data[key]['rate']) / 100);
                                $(".finaligst").val($kkTax);
                                $(".finalcgst").val("");
                                $(".finalsgst").val("");
                            }
                        }
                        $('.finaligst').trigger('change');
                    },
                    error: function (data, status, err)
                    {
//                        alert(status);
                    }
                });
            }
            $(".fianlInvoiceGenerate").removeAttr('disabled');
        });

        $('.addCertification').click(function (e) {
            var newCount = $(".newCount").val();
            var csId = $(".csId").val();
            var regDate = $(".newRegistrationDate").val();
            var allotmentNo = $(".allotmentNo").val();
            var ServiceId = $(".service").val() || '<?php echo isset($_GET['service']) ? $_GET['service'] : '' ?>';
            var generate = $(".certificateid").val();
//            alert(certificationDetails());
            var userrole = $(this).attr("data-role");
//            if (userrole != "Main Admin") {
//                alert("main admin");
            if (certificationDetails()) {
                e.preventDefault();
                return;
            }
//                else {
            var url = 'certification_generate.php';
            var form = $('<form action="' + url + '" method="post" >' +
                    '<input type="hidden" name="newCount" value="' + newCount + '" />' +
                    '<input type="hidden" name="csId" value="' + csId + '" />' +
                    '<input type="hidden" name="ServiceId" value="' + ServiceId + '" />' +
                    '<input type="hidden" name="newRegistrationDate" value="' + regDate + '" />' +
                    '<input type="hidden" name="allotmentNo" value="' + allotmentNo + '" />' +
                    '<input type="hidden" name="certificateid" value="' + generate + '" />' +
                    '</form>');
            $('body').append(form);
//                form.submit();
//                $(".certificatePrintModal").modal("hide");
//                }
//            } else {
//                var url = 'certification_generate.php';
//                var form = $('<form action="' + url + '" method="post" >' +
//                        '<input type="hidden" name="newCount" value="' + newCount + '" />' +
//                        '<input type="hidden" name="csId" value="' + csId + '" />' +
//                        '<input type="hidden" name="ServiceId" value="' + ServiceId + '" />' +
//                        '<input type="hidden" name="newRegistrationDate" value="' + regDate + '" />' +
//                        '<input type="hidden" name="allotmentNo" value="' + allotmentNo + '" />' +
//                        '<input type="hidden" name="certificateid" value="' + generate + '" />' +
//                        '</form>');
//                $('body').append(form);
////                form.submit();
////                $(".certificatePrintModal").modal("hide");
//            }
        });

        $(".newRegistrationDate").change(function () {
//        alert("in");
            // Use runtime-set customer id from hidden field; fallback to GET
            var customer = $('.csId').val() || '<?php echo isset($_GET['customerid']) ? $_GET['customerid'] : '' ?>';
            var idval = $(this).val();
            var serviceId = $('.service').val() || '<?php echo isset($_GET['service']) ? $_GET['service'] : '' ?>';
            
            console.log(' Date changed - Customer:', customer, 'Service ID:', serviceId, 'Date:', idval);
            
            // Use the current field value (no #newRegistrationDate id on input)
            if (idval && idval !== "") {
                // Validate serviceId exists
                if (!serviceId || serviceId === '') {
                    console.error(' Service ID is not set!');
                    generateNormalAllotment(customer, idval);
                    return;
                }
                
                // First, check if this is an RC service
                console.log(' Checking service type for service ID:', serviceId);
                $.ajax({
                    url: "checkCustomer.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        checkServiceType: "true",
                        serviceid: serviceId
                    },
                    success: function (serviceTypeData) {
                        console.log('✅ Service type response:', serviceTypeData);
                        
                        // Check for errors in response
                        if (serviceTypeData && serviceTypeData.error) {
                            console.error('⚠️ API returned error:', serviceTypeData.error);
                            if (serviceTypeData.service_id_checked) {
                                console.error('⚠️ Service ID checked:', serviceTypeData.service_id_checked);
                            }
                        }
                        
                        var isRCService = (serviceTypeData && serviceTypeData.service_type === 'RC');
                        var isCRService = (serviceTypeData && serviceTypeData.service_type === 'CR');
                        console.log('🔍 Is RC?', isRCService, 'Is CR?', isCRService);
                        
                        // Check if CR service has previous CA/SA services
                        if (isCRService) {
                            console.log('📞 CR service detected - checking for previous services...');
                            $.ajax({
                                url: "checkCustomer.php",
                                type: "GET",
                                dataType: "text", // Get raw text first
                                data: {
                                    checkPreviousServices: "true",
                                    customerId: customer,
                                    serviceid: serviceId
                                },
                                success: function (rawResponse) {
                                    console.log('📄 Raw response text:', rawResponse);
                                    
                                    // Try to parse it as JSON
                                    var prevData;
                                    try {
                                        prevData = JSON.parse(rawResponse);
                                        console.log('✅ Parsed JSON response:', prevData);
                                    } catch (e) {
                                        console.error('❌ Failed to parse JSON:', e);
                                        console.error('Raw response was:', rawResponse);
                                        generateNormalAllotment(customer, idval);
                                        return;
                                    }
                                    
                                    console.log('🔍 Response type:', typeof prevData, 'Is array?', Array.isArray(prevData));
                                    console.log('🔍 prevData.has_previous value:', prevData.has_previous, 'type:', typeof prevData.has_previous);
                                    if (prevData.error) {
                                        console.error('⚠️ Error in response:', prevData.error);
                                    }
                                    var hasPreviousServices = (prevData && prevData.has_previous === true);
                                    console.log('🔍 Has previous CA/SA?', hasPreviousServices);
                                    
                                    if (hasPreviousServices) {
                                        console.log('✅ CR is part of RC cycle - fetching base allotment...');
                                        // This CR is for RC cycle, get base allotment with Rc suffix
                                        $.ajax({
                                            url: "checkCustomer.php",
                                            type: "GET",
                                            dataType: "json",
                                            data: {
                                                getBaseAllotment: "true",
                                                customerId: customer,
                                                serviceid: serviceId
                                            },
                                            success: function (rcData) {
                                                console.log('✅ Base allotment response:', rcData);
                                                if (rcData && rcData.base_allotment && rcData.rc_count) {
                                                    // Display with RC suffix
                                                    var allotmentWithSuffix = rcData.base_allotment + ' Rc-' + rcData.rc_count;
                                                    console.log('✅ Setting allotment:', allotmentWithSuffix);
                                                    $('.allotmentNo').val(allotmentWithSuffix);
                                                    $('.newCount').val('');
                                                    $(".allotmentNo").trigger("change");
                                                } else {
                                                    console.error('❌ Invalid RC data received:', rcData);
                                                }
                                            },
                                            error: function (xhr, status, error) {
                                                console.error('❌ Error fetching RC allotment data for CR:', status, error);
                                            }
                                        });
                                    } else {
                                        console.log('ℹ️ First time CR - generating normal allotment');
                                        // First time CR, generate normal allotment
                                        generateNormalAllotment(customer, idval);
                                    }
                                },
                                error: function (xhr, status, error) {
                                    console.error('❌ Error checking previous services:', status, error);
                                    // If check fails, proceed with normal flow
                                    generateNormalAllotment(customer, idval);
                                }
                            });
                        } else if (isRCService) {
                            console.log('📞 RC service detected - fetching base allotment...');
                            // For RC services, get the original base allotment and append RC suffix
                            $.ajax({
                                url: "checkCustomer.php",
                                type: "GET",
                                dataType: "json",
                                data: {
                                    getBaseAllotment: "true",
                                    customerId: customer,
                                    serviceid: serviceId
                                },
                                success: function (rcData) {
                                    console.log('✅ RC Base allotment response:', rcData);
                                    if (rcData && rcData.base_allotment && rcData.rc_count) {
                                        // Display with RC suffix
                                        var allotmentWithSuffix = rcData.base_allotment + ' Rc-' + rcData.rc_count;
                                        console.log('✅ Setting RC allotment:', allotmentWithSuffix);
                                        $('.allotmentNo').val(allotmentWithSuffix);
                                        $('.newCount').val('');
                                        $(".allotmentNo").trigger("change");
                                    } else {
                                        console.error('❌ Invalid RC data:', rcData);
                                    }
                                },
                                error: function (xhr, status, error) {
                                    console.error('❌ Error fetching RC allotment data:', status, error);
                                }
                            });
                        } else {
                            console.log('ℹ️ Non-RC/CR service (likely CA/SA) - generating new allotment');
                            // For non-RC services, generate new allotment number as before
                            generateNormalAllotment(customer, idval);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('❌ Error checking service type:', status, error);
                        console.log('Xhr response:', xhr.responseText);
                        // If service type check fails, proceed with normal flow
                        generateNormalAllotment(customer, idval);
                    }
                });
            }
        });
        
        // Helper function to generate normal allotment number
        function generateNormalAllotment(customer, idval) {
            console.log('📞 Calling getClientInfo.php to generate NEW allotment - Customer:', customer, 'Date:', idval);
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customer: customer,
                    idval: idval
                },
                success: function (data) {
                    console.log('✅ getClientInfo.php response:', data);
                    if (data && data !== "no data" && !isNaN(data)) {
                        var date = idval;
                        var separator = date.indexOf('.') !== -1 ? '.' : (date.indexOf('-') !== -1 ? '-' : '/');
                        var dateArray = date.split(separator);
                        if (dateArray.length >= 3) {
                            var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            var monthNumber = parseInt(dateArray[1], 10) - 1;
                            var monthName = monthNames[monthNumber] || dateArray[1];
                            var year = (typeof moment !== 'undefined') ? moment(dateArray[2], 'YYYY').format('YY') : dateArray[2].slice(-2);
                            var newAllotment = 'AITF/' + monthName + '-' + year + '/' + data;
                            console.log('✅ Generated NEW allotment:', newAllotment);
                            $('.allotmentNo').val(newAllotment);
                            $(".newCount").val(data);
                            $(".allotmentNo").trigger("change");
                        } else {
                            console.error('❌ Unable to parse date for allotment:', idval);
                        }
                    } else {
                        console.error('❌ Invalid data from getClientInfo.php:', data);
                    }
                },
                error: function (xhr, status, err) {
                    console.error('❌ Error calling getClientInfo.php:', status, err);
                    console.log('Xhr response:', xhr.responseText);
                }
            });
        }

        $('.addCustomerToService').click(function (e) {
            var contactPersonArray = [];
            $('.customerCntctPerson').each(function () {
                contactPersonArray.push($(this).val());
            });
            $('.customerPersonHidden').val(contactPersonArray.join(", "));

            var contactNoArray = [];
            $('.contactNo').each(function () {
                contactNoArray.push($(this).val());
            });
            $('.customerContactHidden').val(contactNoArray.join(", "));

            var contactEmailArray = [];
            $('.customerEmail').each(function () {
                contactEmailArray.push($(this).val());
            });
            $('.customerContactEmlHidden').val(contactEmailArray.join(", "));
            if (validateCustomer()) {
                e.preventDefault();
                return;
            }
//            alert($('.customerPersonHidden').val());
        });

        $('.newContactNo').click(function () {
            $('.addMultipleContact').append('<div class="row newContactRowParent" style="margin-top: 10px;">\n\
                                            <div class="col-sm-4 form-group" style="margin-bottom: 12px;">\n\
                                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Contact Person</label>\n\
                                            <input type="text" class="form-control customerCntctPerson" name="customerCntctPerson" placeholder="Contact Person" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">\n\
                                            <div id="contactPersonError" style="display: none; margin-top: 4px;">\n\
                                            <small><span id="contactPersonErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div>\n\
                                            </div>\n\
                                            <div class="col-sm-4 form-group" style="margin-bottom: 12px;">\n\
                                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Email Id</label>\n\
                                            <input type="email" class="form-control customerEmail" name="customerEmail" placeholder="Client Email" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">\n\
                                            <div id="contactEmailError" style="display: none; margin-top: 4px;">\n\
                                            <small><span id="contactEmailErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div>\n\
                                            </div>\n\
                                            <div class="col-sm-3 form-group" style="margin-bottom: 12px;">\n\
                                            <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Contact No.</label>\n\
                                            <input type="text" class="form-control contactNo" name="contactNo" placeholder="Contact No." style="border-radius: 6px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px;">\n\
                                            </div>\n\
                                            <div class="col-sm-1 form-group" style="margin-bottom: 12px; padding-top: 24px;">\n\
                                            <a class="deleteContact" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: #ef4444; color: white !important; border-radius: 6px; text-decoration: none; cursor: pointer;" title="Remove Contact"><i class="fa fa-trash"></i></a>\n\
                                            </div>\n\
                                            </div>');
        });

        $('body').on("click", ".deleteContact", function () {
//        alert("Atleast one action item required");
            $(this).parent("div.deleteNewContactParent").parent('div.newContactRowParent').remove();
        });

        $('.addTaxationToService').click(function (e) {
            if (validateTax()) {
                e.preventDefault();
                return;
            }
        });

        $('.addConsultingFirm').click(function (e) {
            if (validateFirm()) {
                e.preventDefault();
                return;
            }
        });

        $(".addConditionRow").on("click", function () {
            //            $('.actionItemTd').append('<tr><td><textarea placeholder="Enter action item description" required  class="form-control actionitems" rows="1"></textarea></td>\n\
            //                            \n\<td><input type="text"  placeholder="Enter name of employee" class="form-control assignedto" rows="1"></td><td class="deleterow" style="cursor: pointer;"><i style="margin-top: 9px" class="glyphicon glyphicon-remove-sign "></i></td><tr>');
            $('.conditionDescTd').append('<div class="row"><div class="col-sm-12 form-group float-label-control"><label for = "" > Condition Description </label>\n\
                                        <textarea name="conditionDescription" rows="2" class="form-control form-group conditionDescription" id="conditionDescription" placeholder="Condition Description" > </textarea>\n\
                                        </div></div>');
            //            actinitemadd.hide();
            //            $('tr:last-child').after(actinitemadd);
            $('.conditionDescTd').fadeIn("slow");
        });

        $('.generateQuotation').click(function () {
            var firmServiceArray = [];
            $(".conditionDescription").each(function () {
                firmServiceArray.push($(this).val());
            });
            $('.conditionsHidden').val(firmServiceArray.join("~"));
            $('#quotation').modal('hide');
        });

        $('.quoteconsultingFirmName').autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "autoSearch.php",
                    dataType: "json",
                    type: 'GET',
                    data: {
                        consultingFirm: $('.quoteconsultingFirmName').val(),
                    },
                    success: function (data) {
//                        alert(JSON.stringify(data));
                        response(data);
                    },
                    error: function (data, status, err) {
                        //                        alert(status);
                    }
                });
            },
            minLength: 1,
            select: function (event, ui) {
                //            alert(ui.item.id);
//                $("#consultingFirmId").val(ui.item.id);
                $("#consultingFirmId").val(ui.item.id);
                //                    //                    alert(ui.item.id);
            },
            open: function () {
                $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
            },
            close: function () {
                $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
            }
        });

        $('.invoiceconsultingFirmName').autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "autoSearch.php",
                    dataType: "json",
                    type: 'GET',
                    data: {
                        consultingFirm: $('.invoiceconsultingFirmName').val(),
                    },
                    success: function (data) {
//                        alert(JSON.stringify(data));
                        response(data);
                    },
                    error: function (data, status, err) {
                        //                        alert(status);
                    }
                });
            },
            minLength: 1,
            select: function (event, ui) {
                //            alert(ui.item.id);
//                $("#consultingFirmId").val(ui.item.id);
                $("#invoiceFirmId").val(ui.item.id);
                //                    //                    alert(ui.item.id);
            },
            open: function () {
                $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
            },
            close: function () {
                $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
            }
        });

        function round2(val) {
            var n = parseFloat(val);
            if (isNaN(n)) {
                return 0;
            }
            return Math.round((n + Number.EPSILON) * 100) / 100;
        }

        function proformaIncludeQtyRateEnabled() {
            return $('.includeQtyRate').is(':checked');
        }

        function buildProformaServiceRowHtml(isFirst, includeQtyRate, service, qty, rate, amount) {
            var svc = service || '';
            var q = qty || '';
            var r = rate || '';
            var a = amount || '';
            if (includeQtyRate) {
                if (a === '' && q !== '' && r !== '') {
                    a = round2(round2(q) * round2(r));
                }
                if (q === '' && a !== '' && r === '') {
                    q = 1;
                    r = a;
                }
                var removeCol = isFirst ? '' : '<div class = "col-sm-1 removeServiceAmtCol form-group float-label-control" style = "padding-top:10px;" ><a  class="removeServiceAmt pull-right" id="removeServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a></div>';
                return '<div class="row removeServiceAmtRow invoiceServiceRow" style="padding-top: 10px;">\n\
                                        <div class="col-sm-5 form-group float-label-control invoiceServiceCol">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control invoiceService dropdown-toggle" name="invoiceService[]" value="' + svc + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Qty</label>\n\
                                        <input type="number" step="0.01" name="invoiceServiceQty[]" class="form-control form-group invoiceServiceQty" placeholder="Qty" value="' + q + '"></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Rate</label>\n\
                                        <input type="number" step="0.01" name="invoiceServiceRate[]" class="form-control form-group invoiceServiceRate" placeholder="Rate" value="' + r + '"></div>\n\
                                        <div class="col-sm-2 removeSvcAmtVal form-group float-label-control invoiceAmountCol" style="padding-top:10px;">\n\
                                        <label for="">Amount</label>\n\
                                        <input type="number" step="0.01" name="invoiceServiceAmt[]" class="form-control form-group invoiceServiceAmt" id="invoiceService" placeholder="Amount" value="' + a + '" readonly></div>\n\
                                        ' + removeCol + '\n\
                                        </div>';
            }
            var removeCol = isFirst ? '' : '<div class = "col-sm-1 removeServiceAmtCol form-group float-label-control" style = "padding-top:10px;" ><a  class="removeServiceAmt pull-right" id="removeServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a></div>';
            return '<div class="row removeServiceAmtRow invoiceServiceRow" style="padding-top: 10px;">\n\
                                        <div class="col-sm-8 form-group float-label-control invoiceServiceCol">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control invoiceService dropdown-toggle" name="invoiceService[]" value="' + svc + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class = "col-sm-3 removeSvcAmtVal form-group float-label-control invoiceAmountCol" style = "padding-top:10px;" >\n\
                                        <label for = "" > Amount </label>\n\
                                        <input type= "number" step="0.01" name= "invoiceServiceAmt[]" class ="form-control form-group invoiceServiceAmt" id = "invoiceService" placeholder = "Amount" value="' + a + '"> </input>\n\
                                        </div>\n\
                                        ' + removeCol + '\n\
                                        </div>';
        }

        function rebuildProformaServiceRows() {
            var includeQtyRate = proformaIncludeQtyRateEnabled();
            var rows = [];
            $('.serviceAmtTd .invoiceServiceRow').each(function () {
                var $row = $(this);
                var service = $row.find('input[name="invoiceService[]"]').val() || '';
                var qty = $row.find('input[name="invoiceServiceQty[]"]').val() || '';
                var rate = $row.find('input[name="invoiceServiceRate[]"]').val() || '';
                var amt = $row.find('input[name="invoiceServiceAmt[]"]').val() || '';
                rows.push({service: service, qty: qty, rate: rate, amt: amt});
            });
            if (rows.length === 0) {
                rows.push({service: '', qty: '', rate: '', amt: ''});
            }
            $('.serviceAmtTd').empty();
            for (var i = 0; i < rows.length; i++) {
                $('.serviceAmtTd').append(buildProformaServiceRowHtml(i === 0, includeQtyRate, rows[i].service, rows[i].qty, rows[i].rate, rows[i].amt));
            }
            if (includeQtyRate) {
                $('.serviceAmtTd .invoiceServiceRow').each(function () {
                    var $r = $(this);
                    var qty = $r.find('.invoiceServiceQty').val();
                    var rate = $r.find('.invoiceServiceRate').val();
                    var amt = round2(round2(qty) * round2(rate));
                    if (!isNaN(amt)) {
                        $r.find('.invoiceServiceAmt').val(amt).trigger('change');
                    }
                });
            } else {
                $('.serviceAmtTd .invoiceServiceAmt').trigger('change');
            }
        }

        $(document).on('change', '.includeQtyRate', function () {
            rebuildProformaServiceRows();
        });

        $(document).on('input', '.invoiceServiceQty, .invoiceServiceRate', function () {
            if (!proformaIncludeQtyRateEnabled()) {
                return;
            }
            var $row = $(this).closest('.invoiceServiceRow');
            var qty = $row.find('.invoiceServiceQty').val();
            var rate = $row.find('.invoiceServiceRate').val();
            var amt = round2(round2(qty) * round2(rate));
            $row.find('.invoiceServiceAmt').val(amt).trigger('change');
        });

        $(".addServiceAmtRow").on("click", function () {
            $('.serviceAmtTd').append(buildProformaServiceRowHtml(false, proformaIncludeQtyRateEnabled(), '', '', '', ''));
            $('.serviceAmtTd').fadeIn("slow");
//            $(".invoiceGenerate").removeAttr('disabled');
        });

        $('body').on("click", ".removeServiceAmt", function () {
            $(".invoiceGenerate").attr("disabled", true);
//        alert("Atleast one action item required");
            $(this).parent("div.removeServiceAmtCol").parent('div.removeServiceAmtRow').remove();
        });

        function finalIncludeQtyRateEnabled() {
            return $('.includeFinalQtyRate').is(':checked');
        }

        function buildFinalServiceRowHtml(includeQtyRate, service, qty, rate, amount) {
            var svc = service || '';
            var q = qty || '';
            var r = rate || '';
            var a = amount || '';
            if (includeQtyRate) {
                if (a === '' && q !== '' && r !== '') {
                    a = round2(round2(q) * round2(r));
                }
                if (q === '' && a !== '' && r === '') {
                    q = 1;
                    r = a;
                }
                return '<div class="row finalRemoveServiceAmtRow finalInvoiceServiceRow">\n\
                                        <div class="col-sm-5 form-group float-label-control">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control finalInvoiceService dropdown-toggle" name="finalInvoiceService[]" value="' + svc + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Qty</label>\n\
                                        <input type="number" step="0.01" name="finalInvoiceServiceQty[]" class="form-control form-group finalInvoiceServiceQty" placeholder="Qty" value="' + q + '"></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Rate</label>\n\
                                        <input type="number" step="0.01" name="finalInvoiceServiceRate[]" class="form-control form-group finalInvoiceServiceRate" placeholder="Rate" value="' + r + '"></div>\n\
                                        <div class = "col-sm-2 removeSvcAmtVal form-group float-label-control" style = "padding-top:10px;" >\n\
                                        <label for = "" > Amount </label>\n\
                                        <input type= "number" step="0.01" name= "finalInvoiceServiceAmt[]" class ="form-control form-group finalInvoiceServiceAmt" id = "finalInvoiceServiceAmt" placeholder = "Amount" value="' + a + '" readonly> </input>\n\
                                        </div>\n\
                                        <div class = "col-sm-1 finalRemoveServiceAmtCol form-group float-label-control" style = "padding-top:10px;" >\n\
                                        <a  class="finalRemoveServiceAmt pull-right" id="finalRemoveServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a>\n\
                                        </div>\n\
                                        </div>';
            }
            return '<div class="row finalRemoveServiceAmtRow finalInvoiceServiceRow">\n\
                                        <div class="col-sm-8 form-group float-label-control">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control finalInvoiceService dropdown-toggle" name="finalInvoiceService[]" value="' + svc + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class = "col-sm-3 removeSvcAmtVal form-group float-label-control" style = "padding-top:10px;" > \n\
                                        <label for = "" > Amount </label>\n\
                                        <input type= "number" step="0.01" name= "finalInvoiceServiceAmt[]" class ="form-control form-group finalInvoiceServiceAmt" id = "finalInvoiceServiceAmt" placeholder = "Amount" value="' + a + '"> </input>\n\
                                        </div>\n\
                                        <div class = "col-sm-1 finalRemoveServiceAmtCol form-group float-label-control" style = "padding-top:10px;" >\n\
                                        <a  class="finalRemoveServiceAmt pull-right" id="finalRemoveServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a>\n\
                                        </div>\n\
                                        </div>';
        }

        function rebuildFinalServiceRows() {
            var includeQtyRate = finalIncludeQtyRateEnabled();
            var rows = [];
            $('.finalserviceAmtTd .finalInvoiceServiceRow').each(function () {
                var $row = $(this);
                var service = $row.find('input[name="finalInvoiceService[]"]').val() || '';
                var qty = $row.find('input[name="finalInvoiceServiceQty[]"]').val() || '';
                var rate = $row.find('input[name="finalInvoiceServiceRate[]"]').val() || '';
                var amt = $row.find('input[name="finalInvoiceServiceAmt[]"]').val() || '';
                rows.push({service: service, qty: qty, rate: rate, amt: amt});
            });
            $('.finalserviceAmtTd').empty();
            for (var i = 0; i < rows.length; i++) {
                $('.finalserviceAmtTd').append(buildFinalServiceRowHtml(includeQtyRate, rows[i].service, rows[i].qty, rows[i].rate, rows[i].amt));
            }
            if (includeQtyRate) {
                $('.finalserviceAmtTd .finalInvoiceServiceRow').each(function () {
                    var $r = $(this);
                    var qty = $r.find('.finalInvoiceServiceQty').val();
                    var rate = $r.find('.finalInvoiceServiceRate').val();
                    var amt = round2(round2(qty) * round2(rate));
                    if (!isNaN(amt)) {
                        $r.find('.finalInvoiceServiceAmt').val(amt).trigger('change');
                    }
                });
            } else {
                $('.finalserviceAmtTd .finalInvoiceServiceAmt').trigger('change');
            }
        }

        $(document).on('change', '.includeFinalQtyRate', function () {
            rebuildFinalServiceRows();
        });

        $(document).on('input', '.finalInvoiceServiceQty, .finalInvoiceServiceRate', function () {
            if (!finalIncludeQtyRateEnabled()) {
                return;
            }
            var $row = $(this).closest('.finalInvoiceServiceRow');
            var qty = $row.find('.finalInvoiceServiceQty').val();
            var rate = $row.find('.finalInvoiceServiceRate').val();
            var amt = round2(round2(qty) * round2(rate));
            $row.find('.finalInvoiceServiceAmt').val(amt).trigger('change');
        });

        $(".addFinalServiceAmtRow").on("click", function () {
            $('.finalserviceAmtTd').append(buildFinalServiceRowHtml(finalIncludeQtyRateEnabled(), '', '', '', ''));
            $('.finalserviceAmtTd').fadeIn("slow");
            $(".fianlInvoiceGenerate").attr("disabled", true);
        });

        $('body').on("click", ".finalRemoveServiceAmt", function () {
//        alert("Atleast one action item required");
            $(".fianlInvoiceGenerate").attr("disabled", true);
            $(this).parent("div.finalRemoveServiceAmtCol").parent('div.finalRemoveServiceAmtRow').remove();
        });

        $('.addConsultingFirm').click(function (e) {
            var firmServiceArray = [];
            $(".serviceDescription").each(function () {
                firmServiceArray.push($(this).val());
            });
            $('.firmServicesHidden').val(firmServiceArray.join("~"));

            var bankAbbrArray = [];
            $(".bankAbbreviation").each(function () {
                bankAbbrArray.push($(this).val());
            });
            $('.firmBankAbbrHidden').val(bankAbbrArray.join("~"));
            if (validateFirm()) {
                e.preventDefault();
                return;
            }
        });

        $('body').on("click", ".newConsultAbbr", function () {
            $('.newConsultAbbrvation').append('<div class="newAbbrRowParent">\n\
                                                <div class="newAbbrRow col-sm-10 form-group float-label-control ">\n\
                                                <label>Bank Abbreviation</label>\n\
                                                <input type="text" class="bankAbbreviation" id="bankAbbreviation" name="bankAbbreviation" placeholder="Bank Abbreviation" required>\n\
                                                </div><div class="col-sm-2 form-group deleteNewConsultAbbrParent float-label-control">\n\
                                                <a class="deleteNewConsultAbbr" style="float: right;"><i class="fa fa-remove"></i></a>\n\
                                                </div></div>');
        });

        $('body').on("click", ".deleteNewConsultAbbr", function () {
//        alert("Atleast one action item required");
            $(this).parent("div.deleteNewConsultAbbrParent").parent('div.newAbbrRowParent').remove();
        });

        $(".addnewrow").on("click", function () {
            $('.serviceDescTd').append('<div class="row"><div class="col-sm-12 form-group float-label-control">\n\
                                        <label for = "" > Service Description </label>\n\
                                        <input type="text" name = "serviceDescription" rows = "2" class = "form-control form-group serviceDescription" id = "serviceDescription" placeholder = "Service Description" >\n\
                                        </div></div > ');
            $('.serviceDescTd').fadeIn("slow");
        });

//        $('.addCustomerToService').click(function (e) {
//            //alert($('#customerEmail').val());            
//        });

        $('.changePassword').click(function (e) {
            if (validateChangePassword()) {
                e.preventDefault();
                return;
            }
        });

    });
</script>

<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$allAuditorsContact = "";
$allAuditors = "";
$allAuditorsid = "";
$allContact = "";

$sql = "SELECT * FROM auditors";
$statement = $db->prepare($sql);
$statement->execute();
$auditors = $statement->fetchAll();

$sql = "SELECT * FROM categories";
$statement = $db->prepare($sql);
$statement->execute();
$category = $statement->fetchAll();

foreach ($auditors as $name) {
    $allAuditorsid .= $name['auditors_id'] . ",";
    $allAuditors .= $name['auditor_name'] . ",";
    $allAuditorsContact .= $name['auditor_name'] . " - " . $name['auditor_number'] . ",";
    $allContact .= $name['auditor_number'] . ",";
}
?>

<script
src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<style>
    .auditorContactii::-webkit-inner-spin-button,
    .auditorContactii::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .auditorContactii {
        -moz-appearance: textfield;
    }    
</style>

<div class="modal fade" id="auditNotificationII" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                Audit notification with assignment & declaration
            </div>
            <form action="addAuditNotificationII.php" method="post">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formAuditNotification">
                            <span><i class="fa  ">Notification</i></span>
                        </div>
                    </div>
                    <div class="formAuditNotificationDiv">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Subject</label>
                                <input type="text" class="auditiiSubject" id="auditiiSubject" name="auditiiSubject" placeholder="Subject" value="  Intimation for acceptance to the below specified audit, auditor assignment and declaration for
                                       impartiality and confidentiality by AITF audit team member/s." required="">
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>For the organization</label> 
                                <input type="text" class="auditiiForOrg" id="auditiiForOrg" name="auditiiForOrg" placeholder="For the organization" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Date of Intimation</label> 
                                        <input type="text" class="intimationDateii" id="intimationDateii" name="intimationDateii" placeholder="Date of Intimation" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="intimationDateiiError" style="display: none">
                                            <small> <span id="intimationDateiiErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Site(s)</label>
                                <textarea rows="4" class="auditiiLocation" id="auditiiLocation" name="auditiiLocation" placeholder="Site(s):" required></textarea>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row ">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Agreement No:</label>
                                        <input type="text" readonly class="auditiiReferance" id="auditiiReferance" name="auditiiReferance" placeholder="Agreement No:">
                                    </div>
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>For the standard</label> 
                                        <input type="text" readonly class="auditiiStandard" id="auditiiStandard" name="auditiiStandard" placeholder="For the standard">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Contact Person Name:</label> 
                                <input type="text" class="auditiiContactPerson" id="auditiiContactPerson" name="auditiiContactPerson" placeholder="Contact Person Name:" required>
                            </div>
                            <div class="col-sm-6 ">
                                <div class="row">
                                    <div class="col-sm-10 form-group float-label-control">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label>Audit date(s):</label> 
                                                <input type="text" class="auditiiDates" name="auditiiDates" placeholder="Audit date(s):" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12 ">
                                                <div id="auditiiDatesError" style="display: none">
                                                    <small><span id="auditiiDatesErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-1 form-group float-label-control">
                                        <a class="newAuditiiDate" style="" title="Add Multiple AuditDates"><i class="fa fa-plus"></i></a>
                                    </div>
                                </div>
                                <div class="newAuditiiDates"></div>
                            </div>
                            <!--                            <div class="col-sm-6 form-group float-label-control">
                                                                                        <label>Audit date(s):</label>
                                                                                        <input type="text" class="auditiiDates" id="auditiiDates" name="auditiiDates" placeholder="Audit date(s):" required>
                                                                                    </div>--></div>
                        <div class="row ">
                            <div class="col-sm-4 form-group float-label-control">
                                <div class="row form-group float-label-control">
                                    <label>Designation:</label>
                                    <input type="text" class="auditiiDesignation" id="auditiiDesignation" name="auditiiDesignation" placeholder="Designation:" required>
                                </div>
                                <div class="row form-group float-label-control">
                                    <label>Telephone / Mobile:</label> 
                                    <input type="text" class="auditiiTelephone" id="auditiiTelephone" name="auditiiTelephone" placeholder="Telephone / Mobile:" required>
                                </div>
                                <div class="row form-group float-label-control">
                                    <label>E-mail:</label> 
                                    <input type="text" class="auditiiEmail" id="auditiiEmail" name="auditiiEmail" placeholder="E-mail:" required>
                                </div>
                            </div>
                            <div class="col-sm-8 form-group ">
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group ">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii" value="Stage-i">Stage-I Audit
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii" value="Stage-ii" checked>Stage II Audit
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii" value="Surveillance">Surveillance
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii" value="Re-Certification Audit">Re-Certification Audit
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii" value="Follow-up Audit">Follow-up Audit
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii" value="Special Audit">Special Audit ____
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="auditii-noti-service" id="auditii-noti-service" name="auditii-noti-service" value=""> 
                        <input type="hidden" class="auditii-noti-customer" id="auditii-noti-customer" name="auditii-noti-customer" value=""> 
                        <input type="hidden" class="audtii-noti-id" id="audtii-noti-id" name="audtii-noti-id" value="">
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formNotification">
                            <span><i class="fa  ">Declaration for impartiality and confidential by Aitf-MCL team audit team member/s:</i></span>
                        </div>
                    </div>
                    <div class="formNotificationDiv" style="display: none;">
                        <div class="row">
                            <div class="col-sm-12 table-responsive">
                                <table class="table table-bordered table-hover table-sortable service_details" id="notification_detailsii" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th class="" width="20%" style="padding: 2px">
                                                <small>Auditor Name</small>
                                            </th>
                                            <th class="" width="20%" style="padding: 2px">
                                                <small>Category</small>
                                            </th>
                                            <th class="" width="20%" style="padding: 2px">
                                                <small>Work Days</small>
                                            </th>
                                            <th class="" width="20%" style="padding: 2px">
                                                <small>Acceptance</small>
                                                <a class="btn btn-xs btn-info addAuditLinkii pull-right" data-row-id="" data-row-count="" title="Add new cb row" style="padding: 1px 5px;">
                                                    <i class="fa fa-plus-circle fa-lg " style="padding: 3px"></i>
                                                </a>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="editTableDiv">
                                        <tr class="audit_subsectionii previousCBData" id="cb_subsection_0" style="display: ">
                                            <td class="" width="20%" style="padding: 2px;">
                                            <div class="table-edit-control"><!--<input type="text" data-row-id="" class="contractAuditorNameii" id="contractAuditorNameii" name="contractAuditorNameii" value="" placeholder=""/>-->
                                                    <select class="contractAuditorNameii form-control" id="contractAuditorNameii" name="contractAuditorNameii" required>
                                                        <option value="">Choose Auditors</option>
                                                        <?php foreach ($auditors as $name) { ?>
                                                            <option value="<?php echo $name['auditors_id'] ?>"><?php echo $name['auditor_name'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="" width="20%" style="padding: 2px;">
                                                <div class="table-edit-control">
                                                <select class="auditorCategoryii form-control" id="auditorCategoryii" name="auditorCategoryii" required>
                                                        <option value="">Choose Category</option>
                                                        <?php foreach ($category as $name) { ?>
                                                            <option value="<?php echo $name['categories_name'] ?>"><?php echo $name['categories_name'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="" width="20%" style="padding: 2px;">
                                                <div class="table-edit-control">
                                                    <input type="text" min="1" data-row-id="" class="auditorWorkii" id="auditorWorkii" name="depositWorkii" value="" placeholder="" required>
                                                </div>
                                            </td>
                                            <td class="" width="20%" style="padding: 2px;">
                                            <div class="table-edit-control">
                                                    <select class="auditorAcceptanceii form-control" id="auditorAcceptanceii" name="auditorAcceptanceii" required>
                                                        <option value="">Select</option>
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>

                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 table-responsive">
                                <table  class="table table-bordered table-hover table-sortable service_details" id="notification_details" style="width: 100%">
                                    <th> Whether same team leader was appointed for last two consequence audits (Yes  / No )?</th>
                                    <tr>
                                        <td><br>
                                            <div class="col-sm-8 form-group ">   
                                                <div class="col-sm-6 form-group ">
                                                    <input type="checkbox" class="tllasty1" id="tllasty" name="tllasty" value="YES">YES
                                                </div>
                                                <div class="col-sm-6 form-group">
                                                    <input type="checkbox" class="tllastn2" id="tllastn" name="tllastn" value="No">NO
                                                </div>
                                            </div>
                                            <div class="col-sm-4 form-group float-label-control">
                                                <div class="row form-group float-label-control">
                                                    <label></label>
                                                    <input type="text" class="tllastyc1" id="tllastyc" name="tllastyc" placeholder="If yes?:" readonly>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                                <label>Appointed By:</label> 
                                <input type="text" class="appointedby" id="appointedby" name="appointedby" placeholder="Appointed By:" required>
                            </div>
                        <div class="col-sm-6 form-group float-label-control">
                                <label>AITF-MCL Designation:</label> 
                                <input type="text" class="mcldesignation" id="mcldesignation" name="mcldesignation" placeholder="AITF-MCL Designation:" required>
                            </div>
                        <div class="row">
                            <div class="col-sm-12 ">
                                <div id="teamLeader2Error" style="display: none">
                                    <small><span id="teamLeader2ErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="tllastedit1" name="tllastedit" id="tllastedit" value="">
                <input type="hidden" class="auditorNameHiddenii" name="auditorNameHiddenii" value=""> 
                <input type="hidden" class="auditorCategoryHiddenii" name="auditorCategoryHiddenii" value="">
                <input type="hidden" class="auditorContactHiddenii" name="auditorContactHiddenii" value=""> 
                <input type="hidden" class="auditorWorkHiddenii" name="auditorWorkHiddenii" value="">
                <input type="hidden" class="auditorAcceptanceHiddenii" name="auditorAcceptanceHiddenii" value="">
                <input type="hidden" class="auditiiDatesHiddenii" name="auditiiDatesHiddenii" value="">
                <div class="modal-footer">
                    <button type="submit" name="addAuditiiNotiii" class="btn btn-xs btn-info addAuditiiNotiii">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $('#intimationDateii').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true,
            onSelect: function() {
                // Validate on date selection
                validateStage2();
            }
        });
        $("#intimationDateii").keydown(function (e) {
//            e.preventDefault();
        });
        $('.intimationDateii').on('change blur', function() {
            // Validate Intimation Date on change/blur
            if ($(this).val() != "") {
                validateStage2();
            }
        });

        $(document).on('focus', ".auditiiDates", function () {
            if ($(this).hasClass('hasDatepicker') === false) {
                $(this).datepicker({
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

    function validateStage2() {
        $isError = false;
        if ($(".intimationDateii").val() == "") {
            $('#intimationDateiiErrorMsg').text("Intimation Date cannot be empty");
            $('#intimationDateiiError').show();
            $('#intimationDateii').addClass('inputHighlight');
            $isError = true;
        } else if ($(".intimationDateii").val() != "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    intimationDateii: $('.intimationDateii').val(),
                    notisvcId: $('.auditii-noti-service').val(),
                },
                async: false,
                success: function (data) {
    	                //alert(data);	                    
                    if (data == 1) {
                        //alert("hi");
                        $('#intimationDateiiErrorMsg').text("Intimation Date must be greater than Stage-I Audit Date");
                        $('#intimationDateiiError').show();
                        $('#intimationDateii').addClass('inputHighlight');
                        $isError = true;
                    } else {
                        $('#intimationDateiiError').hide();
                        $('#intimationDateii').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#intimationDateiiError').hide();
            $('#intimationDateii').removeClass('inputHighlight');
        }
        $('.auditiiDates').each(function() {
if (($('.auditiiDates').val() != "") && 
    moment($('#intimationDateii').val(), "DD.MM.YYYY") >= moment($('.auditiiDates').val(), "DD.MM.YYYY")) {
    $('#auditiiDatesErrorMsg').text("Audit Date (Stage-II) must be greater than both Intimation Date and Stage-I Audit Date");
    $('#auditiiDatesError').show();
    $('#auditiiDates').addClass('inputHighlight');
    $isError = true;
} else {
    $('#auditiiDatesError').hide();
    $('#auditiiDates').removeClass('inputHighlight');
}});
        if ($(".tllastedit1").val() == "") {
            $('#teamLeader2ErrorMsg').text("Please select atleast one checkbox");
            $('#teamLeader2Error').show();
            $('.tllastedit1').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#teamLeader2Error').hide();
            $('.tllastedit1').removeClass('inputHighlight');
        }
        return $isError;
    }

    $rowCounter = "";
    $rowCB = 1;
    $(document).ready(function () {

        $(".newAuditiiDate").click(function () {
            $(".newAuditiiDates").append('<div class="row newAuditiiRowParent">\n\
                                    <div class="col-sm-10 form-group float-label-control">\n\
                                        <label>Audit date(s):</label>\n\
                                        <input type="text" class="auditiiDates" name="auditiiDates" placeholder="Audit date(s):" required>\n\
                                    </div>\n\
                                    <div class="col-sm-1 form-group float-label-control deleteNewAuditiiParent">\n\
                                        <a class="removeAuditiiDate" style="" title="Remove Multiple AuditDates"><i class="fa fa-remove"></i></a>\n\
                                    </div>\n\
                                </div>');
        });

        $('body').on("click", ".removeAuditiiDate", function () {
//        alert("Atleast one action item required");
            $(this).parent("div.deleteNewAuditiiParent").parent('div.newAuditiiRowParent').remove();
        });

        $('.stage-ii').change(function () {
            if ((this).checked) {
                $('.stage-ii').prop('checked', false);
                $(this).prop('checked', true);
            }
        });
        
        $('.tllasty1').change(function () {
            if ((this).checked) {
                $('.tllasty1').attr("checked");
                $('.tllastn2').removeAttr("checked");
                $('.tllastedit1').val("YES");
//                alert($('.tllastedit1').val());
                $('.tllastyc1').attr("readonly", false);
            }
        });
        
        $('.tllastn2').change(function () {
            if ((this).checked) {
                $('.tllastn2').attr("checked");
                $('.tllasty1').removeAttr("checked");
                $('.tllastedit1').val("No");
//                alert($('.tllastedit1').val());
                $('.tllastyc1').attr("readonly", true);
            }
        });
        
        $('.createAuditNotificationii').click(function () {
//            alert('ho');
            $('.auditii-noti-customer').val($(this).attr('data-customer'));
            $('.auditii-noti-service').val($(this).attr('data-service'));
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customerID: $(this).attr('data-customer'),
                    serviceID: $(this).attr('data-service'),
                },
                success: function (data)
                {
//                    alert(data[3]['schedule_app_standard']);
                    $('.auditiiForOrg').val(data[0]['customer_name']);
//                    $('.intimationDateii').val(moment(data[1]['audit_dates']).format('DD.MM.YYYY'));
                    $('.auditiiLocation').val(data[0]['address']);
                    $('.auditiiReferance').val(data[0]['unique_id']);
                    $('.auditiiTelephone').val(data[0]['contact']);
                    $('.auditiiEmail').val(data[0]['email_id']);
                    $('.auditiiContactPerson').val(data[1]['audit_contact_person']);
                    $('.auditiiDesignation').val(data[1]['audit_designation']);
//                    $('.auditiiDates').val(moment(data[3]['schedule_audit_date']).format('DD.MM.YYYY'));
                    $('.auditiiStandard').val(data[3]['schedule_app_standard']);
                    $('input[type="text"]').trigger('change');
                    $('.auditiiLocation').trigger('change');
                    $('#auditNotificationII').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.editAuditNotificationii').click(function () {
            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    notificationiiId: $(this).attr('data-notification'),
                    serviceiiId: $(this).attr('data-service'),
                },
                success: function (data)
                {
                    $('.editTableDiv').empty();
                    $('.auditiiForOrg').val(data[0]['for_org']);
                    $('.intimationDateii').val(moment(data[0]['intimation_date']).format('DD.MM.YYYY'));
                    $('.auditiiLocation').val(data[0]['location']);
                    $('.auditiiReferance').val(data[0]['audit_ref_no']);
                    $('.auditiiStandard').val(data[0]['audit_standard']);
                    $('.auditiiContactPerson').val(data[0]['audit_contact_person']);
//                    $('.auditiiDates').val(moment(data[0]['audit_dates']).format('DD.MM.YYYY'));
                    $('.auditiiDesignation').val(data[0]['audit_designation']);
                    $('.auditiiTelephone').val(data[0]['audit_telephone']);
                    $('.auditiiEmail').val(data[0]['audit_email']);
                    $('.stage-ii[value = "' + data[0]["stage_ii_of"] + '"]').prop('checked', true);
                    $('.audtii-noti-id').val(data[0]['audit_stageii_id']);
                    $('.auditii-noti-customer').val(data[0]['customer_id']);
                    $('.auditii-noti-service').val(data[0]['service_id']);
                    $('.tllastedit').val(data[0]['auditor_check_last_two']);
                    $('.tllastyc').val(data[0]['auditor_check_comm']);
                    if (data[0]['auditor_check_last_two'] == "YES")
                    {
                        $('.tllasty').prop('checked', true);

                    } else if (data[0]['auditor_check_last_two'] == "NO")
                    {
                        $('.tllastn').prop('checked', true);
                    }

                    $(".newAuditiiDatesEdit").empty();
                    var auditDate = data[0]['audit_dates'].split(', ');
                    var auditDateLen = auditDate.length;
                    var max = (Math.max(auditDateLen));
//                    alert(personLen);
                    for (var key = 0; key < max; key++) {
//                        alert(auditDate[key]);
                        //                        var no=contact[key];                        
                        var no = key <= auditDateLen && auditDate[key] != null && auditDate[key] != "" ? auditDate[key] : "";

                        if (key == 0) {
                            $('.auditiiDates').val(no);
                        } else {
                            $(".newAuditiiDates").empty();
                            $(".newAuditiiDates").append('<div class="row newAuditiiRowParent">\n\
                                    <div class="col-sm-10 form-group float-label-control">\n\
                                        <label>Audit date(s):</label>\n\
                                        <input type="text" class="auditiiDates" name="auditiiDates" placeholder="Audit date(s):" value="' + no + '" required>\n\
                                    </div>\n\
                                    <div class="col-sm-1 form-group float-label-control deleteNewAuditiiParent">\n\
                                        <a class="removeAuditiiDate" style="" title="Remove Multiple AuditDates"><i class="fa fa-remove"></i></a>\n\
                                    </div>\n\
                                </div>');
                        }
                    }

                    var allAuditorsid = ("<?php echo $allAuditorsid ?>");
//            alert(allAuditorsid);
                    var auditorid = allAuditorsid.split(',');
                    var allAuditoridLen = auditorid.length;

                    var allAuditors = ("<?php echo $allAuditors; ?>");
                    var auditors = allAuditors.split(',');
                    var allAuditorsLen = auditors.length;

                    var max = (Math.max(allAuditoridLen, allAuditorsLen));
                    var option = "";
                    option +='<option value="">Choose Auditor</option>';
                    for (var key = 0; key < max; key++) {
                        var audid = key <= allAuditoridLen && auditorid[key] != null && auditorid[key] != "" ? auditorid[key] : "";
                        var audname = key <= allAuditorsLen && auditors[key] != null && auditors[key] != "" ? auditors[key] : "";
                        option += '<option value="' + audid + '">' + audname + '</option>';
                    }

//                    var allAuditors = ("<?php echo $allAuditors; ?>");
//                    var auditors = allAuditors.split(',');
//                    var option = "";
//                    for (var key in auditors) {
//                        if (auditors[key] != null && auditors[key] != "") {
//                            option += '<option value="' + auditors[key] + '">' + auditors[key] + '</option>';
//                        }
//                    }

                    var option1 = "";
                    var contactname = '<?php echo $allAuditorsContact ?>';
                    var contact = '<?php echo $allContact ?>';

                    var ac_name = contactname.split(',');
                    var a_contact = contact.split(',');
                    var auditorNameLen = ac_name.length;
                    var auditorcontactLen = a_contact.length;
                    var max = (Math.max(auditorNameLen, auditorcontactLen));
                    for (var key = 0; key < max; key++) {
                        var audname = key <= auditorNameLen && ac_name[key] != null && ac_name[key] != "" ? ac_name[key] : "";
                        var audcontact = key <= auditorcontactLen && a_contact[key] != null && a_contact[key] != "" ? a_contact[key] : "";
                        option1 += '<option value="' + audcontact + '">' + audname + '</option>';
                    }

                    for (var key in data[1]) {
                        $rowCounter++;
                        var option2 = '<option value="' + data[1][key]['auditor_id'] + '"selected>' + data[1][key]['auditor_name'] + '</option>';
                        var option3 = '<option value="' + data[1][key]['audit_contact'] + '"selected>' + data[1][key]['auditor_name'] + ' - ' + data[1][key]['audit_contact'] + '</option>';
//                        var option2 = '<option value="' + data[1][key]['auditor_name'] + '"selected>' + data[1][key]['auditor_name'] + '</option>';
                        $('.editTableDiv').append('<tr class="audit_subsectionii previousCBData" id="cb_subsection_0" style="display: ">\n\
                                                        <td class="" width="20%" style="padding:2px;">\n\
                                                        <div class="table-edit-control">\n\
                                                          <select data-row-id="" class="contractAuditorNameii form-control" id="contractAuditorNameii" name="contractAuditorNameii">\n\
                                                       ' + option2 + '  \n\
                                                     ' + option + '\n\
                                                          </select>\n\
                                                        </div>\n\
                                                        </td>\n\
                                                        <td class="" width="20%" style="padding:2px;">\n\
        <div class="table-edit-control">\n\
            <select class="auditorCategoryii form-control" id="auditorCategoryii" name="auditorCategoryii" required>\n\
                <option value="">Choose Category</option>\n\
                <?php foreach ($category as $name) { ?>
                    <option value="<?php echo $name['categories_name'] ?>"><?php echo $name['categories_name'] ?></option>\n\
                <?php } ?>\n\
            </select>\n\
        </div>\n\
    </td>\n\
                                                        <td class="" width="20%" style="padding:2px;">\n\
                                                        <div class="table-edit-control">\n\
                                                        <input type="text" min="1" data-row-id="" class="auditorWorkii" id="auditorWorkii" name="auditorWorkii" value="' + data[1][key]['audit_wokrdays'] + '" placeholder="">\n\
                                                        </div>\n\
                                                        </td>\n\
                                                        <td class="" width="20%" style="padding:2px;">\n\
                                                        <div class="table-edit-control">\n\
                                                        <select class="auditorAcceptanceii form-control" id="auditorAcceptanceii" name="auditorAcceptanceii" >\n\
                                                        <option value="' + data[1][key]['auditor_acceptance'] + '">' + data[1][key]['auditor_acceptance'] + '</option><option value="">Select</option><option value="Yes">Yes</option><option value="No">No</option></select></div>\n\
                                                        </td>\n\
                                                        <td class="" width="10%" style="padding:2px;">\
        <button class="btn btn-danger removeRowButton">Remove</button>\
    </td>\
                                                        </tr>');
                                                        const newAuditorCategorySelect = $('.editTableDiv').find('.auditorCategoryii').last();

// Set the selected value based on `data[1][key]['auditor_category']`
newAuditorCategorySelect.val(data[1][key]['auditor_category']);
                    }
                    $rowCB = $rowCounter;
//                    alert(data[0]["auditor_check_last_two"]);
                    if (data[0]["auditor_check_last_two"] == "YES")
                    {
                        $('.tllasty1').prop("checked", true);
                    } else if (data[0]["auditor_check_last_two"] == "No")
                    {
                        $('.tllastn2').prop("checked", true);
                    }
                    $(".tllastedit1").val(data[0]["auditor_check_last_two"]);
                    $('input[type="text"]').trigger('change');
                    $('input[type="number"]').trigger('change');
                    $('.auditiiLocation').trigger('change');
                    $('.appointedby').val(data[0]['appointed_by']);
                    $('.mcldesignation').val(data[0]['mcl_designation']);
                    $('#auditNotificationII').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.downloadAuditNotificationii').click(function () {
            var notificationid = $(this).attr('data-audit');
            var url = 'AuditNotificationStage2.php';
            var form = $('<form action="' + url + '" method="post" target="_blank" >' +
                    '<input type="hidden" name="notificationid" value="' + notificationid + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.addAuditiiNotiii').click(function (e) {

            if (validateStage2()) {
                e.preventDefault();
                return;
            }

            var auditiiDatesArray = [];
            $('.auditiiDates').each(function () {
                auditiiDatesArray.push($(this).val());
            });
            $('.auditiiDatesHiddenii').val(auditiiDatesArray.join(", "));

            var auditorNameArray = [];
            $('.contractAuditorNameii').each(function () {
                auditorNameArray.push($(this).val());
            });
            $('.auditorNameHiddenii').val(auditorNameArray.join("~"));

            var auditorCategoryArray = [];
            $('.auditorCategoryii').each(function () {
                auditorCategoryArray.push($(this).val());
            });
            $('.auditorCategoryHiddenii').val(auditorCategoryArray.join("~"));

            var auditorContactArray = [];
            $(".auditorContactii").each(function () {
                auditorContactArray.push($(this).val());
            });
            $('.auditorContactHiddenii').val(auditorContactArray.join("~"));

            var auditorWorkArray = [];
            $(".auditorWorkii").each(function () {
                auditorWorkArray.push($(this).val());
            });
            $('.auditorWorkHiddenii').val(auditorWorkArray.join("~"));

            var auditorAcceptanceiiArray = [];
            $(".auditorAcceptanceii").each(function () {
                auditorAcceptanceiiArray.push($(this).val());
            });
            $('.auditorAcceptanceHiddenii').val(auditorAcceptanceiiArray.join("~"));
        });

//        $('body').on("change", ".contractAuditorNameii", function () {
//            alert($(this).val());
//        });

        $(document.body).on('click', '.addAuditLinkii', function () {

            var allAuditorsid = ("<?php echo $allAuditorsid ?>");
//            alert(allAuditorsid);
            var auditorid = allAuditorsid.split(',');
            var allAuditoridLen = auditorid.length;

            var allAuditors = ("<?php echo $allAuditors; ?>");
            var auditors = allAuditors.split(',');
            var allAuditorsLen = auditors.length;

            var max = (Math.max(allAuditoridLen, allAuditorsLen));
            var option = "";
            for (var key = 0; key < max; key++) {
                var audid = key <= allAuditoridLen && auditorid[key] != null && auditorid[key] != "" ? auditorid[key] : "";
                var audname = key <= allAuditorsLen && auditors[key] != null && auditors[key] != "" ? auditors[key] : "";
                option += '<option value="' + audid + '">' + audname + '</option>';
            }

//            var allAuditors = ("<?php echo $allAuditors; ?>");
//            var auditors = allAuditors.split(',');
//            var option = "";
//            for (var key in auditors) {
//                if (auditors[key] != null && auditors[key] != "") {
//                    option += '<option value="' + auditors[key] + '">' + auditors[key] + '</option>';
//                }
//
//            }
            var autitorsSelect = '<select data-row-id="" class="contractAuditorNameii form-control" id="contractAuditorNameii" name="contractAuditorNameii">\n\
                                                                <option value="">Choose Auditors</option>\n\
                                                                    ' + option + '\n\
                                                            </select>';


            var option1 = "";
            var contactname = '<?php echo $allAuditorsContact ?>';
            var contact = '<?php echo $allContact ?>';

            var ac_name = contactname.split(',');
            var a_contact = contact.split(',');
            var auditorNameLen = ac_name.length;
            var auditorcontactLen = a_contact.length;
            var max = (Math.max(auditorNameLen, auditorcontactLen));
            for (var key = 0; key < max; key++) {
                var audname = key <= auditorNameLen && ac_name[key] != null && ac_name[key] != "" ? ac_name[key] : "";
                var audcontact = key <= auditorcontactLen && a_contact[key] != null && a_contact[key] != "" ? a_contact[key] : "";
                option1 += '<option value="' + audcontact + '">' + audname + '</option>';
            }

            var autitorsContact = '<select data-row-id="" class="auditorContactii form-control" id="auditorContactii" name="auditorContactii" required >\n\
                                    <option value="">Choose contact</option>\n\
                                    ' + option1 + '\n\
                                </select>';

//            alert($rowCB);
            $count = $rowCB + 1;
            var table = document.getElementById("notification_detailsii");
            var row = table.insertRow($count);

            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            var cell3 = row.insertCell(2);
            var cell4 = row.insertCell(3);

            cell1.style.padding = "0px";
            cell2.style.padding = "0px";
            cell3.style.padding = "0px";
            cell4.style.padding = "0px";

            cell1.innerHTML = '<div class="table-edit-control"><label></label>' + autitorsSelect + '</div>';
            cell2.innerHTML = '<div class="table-edit-control"><label></label><select class="auditorCategoryii form-control" id="auditorCategoryii" name="auditorCategoryii"><?php foreach ($category as $name) { ?><option value="<?php echo $name['categories_name'] ?>" <?php if ($name['categories_name'] == $selectedCategoryId) { echo 'selected'; } ?>><?php echo $name['categories_name'] ?></option><?php } ?></select></div>';
            cell3.innerHTML = '<div class="table-edit-control"><label></label><input type="text" min="1" data-row-id="" class="auditorWorkii" id="auditorWorkii"  name="auditorWorkii" placeholder="" /></div>';
            cell4.innerHTML = '<div class="table-edit-control"><select class="auditorAcceptanceii form-control" id="auditorAcceptanceii" name="auditorAcceptanceii" required><option value="">Select</option><option value="Yes">Yes</option><option value="No">No</option></select></div>';

            row.className = "audit_subsectionii";
            let removeButton = document.createElement('button');
        removeButton.textContent = 'Remove';
        removeButton.classList.add('btn', 'btn-sm', 'btn-danger', 'removeRowButton');

        // Insert the button into the last cell of the new row
        row.insertCell(-1).appendChild(removeButton);
        $rowCB = $count;
});
$(document).on('click', '.removeRowButton', function() {
    $count = $rowCB - 1;
        $(this).closest('tr').remove();
        $rowCB = $count;
    });

    });

</script>

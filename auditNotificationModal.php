<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$allAuditorsContact = "";
$allAuditorsid = "";
$allAuditors = "";
$allContact = "";
$sql = "SELECT * FROM auditors";
$statement = $db->prepare($sql);
$statement->execute();
$auditors = $statement->fetchAll();

$sql = "SELECT * FROM categories";
$statement = $db->prepare($sql);
$statement->execute();
$category = $statement->fetchAll();
//  Standards 
$standards = "SELECT * FROM standards";
$statement = $db->prepare($standards);
$statement->execute();
$standardsList = $statement->fetchAll();

foreach ($auditors as $name) {
    $allAuditorsid .= $name['auditors_id'] . ",";
    $allAuditors .= $name['auditor_name'] . ",";
    $allAuditorsContact .= $name['auditor_name'] . " - " . $name['auditor_number'] . ",";
    $allContact .= $name['auditor_number'] . ",";
}
?>

<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">
<style>
    .auditorContact::-webkit-inner-spin-button,
    .auditorContact::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .auditorContact {
        -moz-appearance: textfield;
    }  
    .auditorContactEdit::-webkit-inner-spin-button,
    .auditorContactEdit::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .auditorContactEdit {
        -moz-appearance: textfield;
    }
</style>

<div class="modal fade" id="auditNotification" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                Audit notification with assignment & declaration
            </div>
            <form action="addAuditNotification.php" method="post">
                <div class="modal-body">
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formAuditNotification">
                            <span><i class="fa  ">Notification</i></span>
                        </div>
                    </div>
                    <div class="formAuditNotificationDiv">
                        <div class="row " style="padding-top: 10px">
                            <div class="col-sm-12 form-group float-label-control"><label>Subject:</label>
                                <input type="text" class="auditSubject" id="auditSubject" name="auditSubject" placeholder="Subject:" value=" Intimation for acceptance to the below specified audit, auditor assignment and declaration for impartiality and confidentiality by AITF audit team member/s." required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>For the organization:</label> 
                                <input type="text" class="auditForOrg" id="auditForOrg" name="auditForOrg" placeholder="For the organization:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 orm-group float-label-control">
                                        <label>Date of Intimation:</label> 
                                        <input type="text" class="intimationDate" id="intimationDate" name="intimationDate" placeholder="Date of Intimation:" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <div id="intimationDateError" style="display: none">
                                            <small><span id="intimationDateErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Site(s):</label>
                                <textarea rows="4" class="auditLocation" id="auditLocation" name="auditLocation" placeholder="Site(s):" required></textarea>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row ">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Agreement No:</label> 
                                        <input readonly type="text"  class="auditReferance" id="auditReferance" name="auditReferance" placeholder="Agreement No:" required>
                                    </div>
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Assessment Standard/s:</label> 
                                        <textarea readonly name="auditStandard" class="form-control form-group auditStandard" id="auditStandard" placeholder="Assessment Standard" required></textarea>
                                        <!--<input type="text" class="auditStandard" id="auditStandard" name="auditStandard" placeholder="For the standard:" required>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Contact Person Name:</label> 
                                <input type="text" class="auditContactPerson" id="auditContactPerson" name="auditContactPerson" placeholder="Contact Person Name:" required>
                            </div>
                            <div class="col-sm-6 ">
                                <div class="row">
                                    <div class="col-sm-10 form-group float-label-control">
                                        <div class="row">
                                            <div class="col-sm-12 form-group float-label-control">
                                                <label>Audit date(s):</label> 
                                                <input type="text" class="auditDates" name="auditDates" placeholder="Audit date(s):" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12 ">
                                                <div id="auditDatesError" style="display: none">
                                                    <small><span id="auditDatesErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-1 form-group float-label-control">
                                        <a class="newAuditDate" style="" title="Add Multiple AuditDates"><i class="fa fa-plus"></i></a>
                                    </div>
                                </div>
                                <div class="newAuditDates"></div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-4 form-group float-label-control">
                                <div class="row form-group float-label-control">
                                    <label>Designation:</label>
                                    <input type="text" class="auditDesignation" id="auditDesignation" name="auditDesignation" placeholder="Designation:" required>
                                </div>
                                <div class="row form-group float-label-control">
                                    <label>Telephone / Mobile:</label> 
                                    <input type="text" class="auditTelephone" id="auditTelephone" name="auditTelephone" placeholder="Telephone / Mobile:" required>
                                </div>
                                <div class="row form-group float-label-control">
                                    <label>E-mail:</label> 
                                    <input type="email" class="auditEmail" id="auditEmail" name="auditEmail" placeholder="E-mail:" required>
                                </div>
                            </div>
                            <div class="col-sm-8 form-group ">
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group ">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i" value="Stage-i" checked>Stage-I Audit
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i" value="Stage-ii">Stage II Audit
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i" value="Surveillance">Surveillance
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i" value="Re-Certification Audit">Re-Certification Audit
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i" value="Follow-up Audit">Follow-up Audit
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i" value="Special Audit">Special Audit ____
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="audit-noti-service" id="audit-noti-service" name="audit-noti-service" value=""> 
                        <input type="hidden" class="audit-noti-service1" id="audit-noti-service1" name="audit-noti-service1" value=""> 
                        <input type="hidden" class="audit-noti-customer" id="audit-noti-customer" name="audit-noti-customer" value=""> 
                        <input type="hidden" class="audti-service-type" id="audti-service-type" name="audti-service-type" value="">
                        <input type="hidden" class="audit-service-type" id="audit-service-type" name="audit-service-type" value="">
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formNotification">
                            <span><i class="fa  ">Declaration for impartiality and confidential by Aitf-MCL team audit team member/s:</i></span>
                        </div>
                    </div>
                    <div class="formNotificationDiv" style="display: none;">
                        <div class="row">
                            <div class="col-sm-12 table-responsive">
                                <table  class="table table-bordered table-hover table-sortable service_details" id="notification_details" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th class="" width="20%" style="padding: 2px"><small>Auditor Name</small></th>
                                            <th class="" width="15%" style="padding: 2px"><small>Category </small></th>
                                            <!--<th class="" width="35%" style="padding: 2px"><small>Contact Number</small></th>-->
                                            <th class="" width="10%" style="padding: 2px"><small>Work Days</small></th>
                                            <th class="" width="10%" style="padding: 2px"><small>Acceptance</small>
                                                <a class="btn btn-xs btn-info addAuditLink pull-right" data-row-id="" data-row-count="" title="Add new cb row" style="padding: 1px 5px;"><i class="fa fa-plus-circle fa-lg " style="padding: 3px"></i></a>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="editedNotification">
                                        <tr class="audit_subsection previousCBData" id="cb_subsection_0"  style="display: ">
                                            <td class="" width="20%" style="padding: 2px;">
                                                <div class="table-edit-control">
                                                    <!--<input type="text" data-row-id="" class="contractAuditorName" id="contractAuditorName" name="contractAuditorName" value="" placeholder=""/>-->
                                                    <select class="contractAuditorName form-control" id="contractAuditorName" name="contractAuditorName" required>
                                                        <option value="">Choose Auditors</option>
                                                        <?php foreach ($auditors as $name) { ?>
                                                            <option value="<?php echo $name['auditors_id'] ?>"><?php echo $name['auditor_name'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="" width="20%" style="padding: 2px;">
                                            <div class="table-edit-control">
                                                <select class="auditorCategory form-control" id="auditorCategory" name="auditorCategory" required>
                                                        <option value="">Choose Category</option>
                                                        <?php foreach ($category as $name) { ?>
                                                            <option value="<?php echo $name['categories_name'] ?>"><?php echo $name['categories_name'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="" width="20%" style="padding: 2px;">
                                                <div class="table-edit-control">
                                                    <input type="text" min="1" data-row-id="" class="auditorWork" id="auditorWork" name="depositWork" value="" placeholder="" required>
                                                </div>
                                            </td>
                                            <td class="" width="20%" style="padding: 2px;">
                                                <div class="table-edit-control">
                                                    <!--<input type="text" data-row-id="" class="contractAuditorName" id="contractAuditorName" name="contractAuditorName" value="" placeholder=""/>-->
                                                    <select class="auditorAcceptance form-control" id="auditorAcceptance" name="auditorAcceptance" required>
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
                                                    <input type="checkbox" class="tllasty" id="tllasty" name="tllasty" value="yes">YES
                                                </div>
                                                <div class="col-sm-6 form-group">
                                                    <input type="checkbox" class="tllastn" id="tllastn" name="tllastn" value="no">NO
                                                </div>
                                            </div>
                                            <div class="col-sm-4 form-group float-label-control">
                                                <div class="row form-group float-label-control">
                                                    <label></label>
                                                    <input type="text" class="tllastyc" id="tllastyc" name="tllastyc" placeholder="If yes?:" readonly>
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
                                <div id="teamLeaderError" style="display: none">
                                    <small><span id="teamLeaderErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="tllast" name="tllast" id="tllast" value="">
                <input type="hidden" class="auditorNameHidden" name="auditorNameHidden" value=""> 
                <input type="hidden" class="auditorCategoryHidden" name="auditorCategoryHidden" value="">
                <input type="hidden" class="auditorWorkHidden" name="auditorWorkHidden" value=""> 
                <input type="hidden" class="auditDatesHidden" name="auditDatesHidden" value="">
                <input type="hidden" class="auditorAcceptanceHidden" name="auditorAcceptanceHidden" value="">
                <input type="hidden" class="audit-noti-id-edit" name="audit-noti-id-edit" value="">
                <div class="modal-footer">
                    <button type="submit" name="addAuditNoti" class="btn btn-xs btn-info addAuditNoti">Save</button>
                </div>
            </form>
        </div>
    </div>
    <div id="auditor-details" style="display: none">
        <div id="loading-div" class="ui-corner-all">
            <span style="font-size: 30pt;"><i style="padding: 5px;" class="fa fa-warning text-warning"></i></span>
            <div><span style="font-size: 11pt; padding: 10px" class="text-primary meeting-cancel-confirm-message">
                    For previous two services act as Lead Auditor</span>
            </div>
            <div style="padding: 3px;">
                <button title="Click here to close" class="btn btn-primary btn-round btn-round-sm btn-sm confirm-ok">Ok</button>                    
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    function formatStage1DateValue(rawValue) {
        if (rawValue === null || rawValue === undefined) {
            return "";
        }

        var value = String(rawValue).trim();
        if (value === "" || value.toLowerCase() === "invalid date") {
            return "";
        }

        var strictDate = moment(value, ['DD.MM.YYYY', 'DD-MM-YYYY', 'DD/MM/YYYY', 'YYYY-MM-DD', 'YYYY/MM/DD'], true);
        if (strictDate.isValid()) {
            return strictDate.format('DD.MM.YYYY');
        }

        var looseDate = moment(value);
        if (looseDate.isValid()) {
            return looseDate.format('DD.MM.YYYY');
        }

        return value;
    }

    function formatStage1DateList(rawValue) {
        if (rawValue === null || rawValue === undefined) {
            return "";
        }

        var value = String(rawValue).trim();
        if (value === "" || value.toLowerCase() === "invalid date") {
            return "";
        }

        var values = value.split(',');
        var formattedValues = [];
        for (var i = 0; i < values.length; i++) {
            var formatted = formatStage1DateValue(values[i]);
            if (formatted !== "") {
                formattedValues.push(formatted);
            }
        }

        return formattedValues.join(', ');
    }

    $(function () {
        $('#intimationDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#intimationDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#intimationDateEdit').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#intimationDateEdit").keydown(function (e) {
//            e.preventDefault();
        });

        $(document).on('focus', ".auditDates", function () {
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

        $(document).on('focus', ".auditDatesEdit", function () {
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

        $('#auditNotiRevDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#auditNotiRevDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#auditNotiIsueDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#auditNotiIsueDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#editAuditNotiRevDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#editAuditNotiRevDate").keydown(function (e) {
//            e.preventDefault();
        });

        $('#editAuditNotiIsueDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true
        });
        $("#editAuditNotiIsueDate").keydown(function (e) {
//            e.preventDefault();
        });
    });

    function validateStage1edit() {
        $isError = false;
        if ($(".intimationDateEdit").val() == "") {
            $('#intimationDateEditErrorMsg').text("Date can not be empty");
            $('#intimationDateEditError').show();
            $('#intimationDateEdit').addClass('inputHighlight');
            $isError = true;
        } else if ($(".intimationDateEdit").val() != "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    intimationDate: $('.intimationDateEdit').val(),
                    svc_id: $('.audit-noti-service-edit').val(),
                },
                async: false,
                success: function (data) {
//		                alert(data);	                    
                    if (data == 1) {
                        $('#intimationDateEditErrorMsg').text("Date should be greater than or equal to assessment planning date");
                        $('#intimationDateEditError').show();
                        $('#intimationDateEdit').addClass('inputHighlight');
                        $isError = true;
                    } else {
                        $('#intimationDateEditError').hide();
                        $('#intimationDateEdit').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#intimationDateEditError').hide();
            $('#intimationDateEdit').removeClass('inputHighlight');
        }

        $('.auditDatesEdit').each(function() {
if (($('.auditDatesEdit').val() != "") && 
    moment($('#intimationDateEdit').val(), "DD.MM.YYYY") >= moment($('.auditDatesEdit').val(), "DD.MM.YYYY")) {
    $('#auditDatesEditErrorMsg').text("Date should be greater than intimation date");
    $('#auditDatesEditError').show();
    $('#auditDatesEdit').addClass('inputHighlight');
    $isError = true;
} else {
    $('#auditDatesEditError').hide();
    $('#auditDatesEdit').removeClass('inputHighlight');
}});
        return $isError;
    }

    function validateStage1() {
        $isError = false;
        if ($(".intimationDate").val() == "") {
            $('#intimationDateErrorMsg').text("Date can not be empty");
            $('#intimationDateError').show();
            $('#intimationDate').addClass('inputHighlight');
            $isError = true;
        } else if ($(".intimationDate").val() != "") {
            $.ajax({
                type: "GET",
                url: "checkCustomer.php",
                data: {
                    intimationDate: $('.intimationDate').val(),
                    svc_id: $('.audit-noti-service1').val(),
                },
                async: false,
                success: function (data) {
//		                alert(data);	                    
                    if (data == 1) {
                        $('#intimationDateErrorMsg').text("Date should be greater than or equal to assessment planning date");
                        $('#intimationDateError').show();
                        $('#intimationDate').addClass('inputHighlight');
                        $isError = true;
                    } else {
                        $('#intimationDateError').hide();
                        $('#intimationDate').removeClass('inputHighlight');
                    }
                }
            });
        } else {
            $('#intimationDateError').hide();
            $('#intimationDate').removeClass('inputHighlight');
        }

        $('.auditDates').each(function() {
if (($('.auditDates').val() != "") && 
    moment($('#intimationDate').val(), "DD.MM.YYYY") >= moment($('.auditDates').val(), "DD.MM.YYYY")) {
    $('#auditDatesErrorMsg').text("Date should be greater than intimation date");
    $('#auditDatesError').show();
    $('#auditDates').addClass('inputHighlight');
    $isError = true;
} else {
    $('#auditDatesError').hide();
    $('#auditDates').removeClass('inputHighlight');
}});
        if ($(".tllast").val() == "") {
            $('#teamLeaderErrorMsg').text("Please select atleast one checkbox");
            $('#teamLeaderError').show();
            $('.tllast').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#teamLeaderError').hide();
            $('.tllast').removeClass('inputHighlight');
        }
        return $isError;
    }

    $rowCounter = "";
    $rowCB = 1;
    $(document).ready(function () {

//        $(".addAuditNoti").click(function (e) {
//            if (validateStage1()) {
//                e.preventDefault();
//                return;
//            }
//        });

        $(".newAuditDate").click(function () {
            $(".newAuditDates").append('<div class="row newAuditRowParent">\n\
                                    <div class="col-sm-10 form-group float-label-control">\n\
                                        <label>Audit date(s):</label>\n\
                                        <input type="text" class="auditDates" name="auditDates" placeholder="Audit date(s):" required>\n\
                                    </div>\n\
                                    <div class="col-sm-1 form-group float-label-control deleteNewAuditParent">\n\
                                        <a class="removeAuditDate" style="" title="Remove Multiple AuditDates"><i class="fa fa-remove"></i></a>\n\
                                    </div>\n\
                                </div>');
        });

        $(".newAuditDateEdit").click(function () {
            $(".newAuditDatesEdit").append('<div class="row newAuditRowParentEdit">\n\
                                    <div class="col-sm-10 form-group float-label-control">\n\
                                        <label>Audit date(s):</label>\n\
                                        <input type="text" class="auditDatesEdit" name="auditDatesEdit" placeholder="Audit date(s):" required>\n\
                                    </div>\n\
                                    <div class="col-sm-1 form-group float-label-control deleteNewAuditParentEdit">\n\
                                        <a class="removeAuditDateEdit" style="" title="Remove Multiple AuditDates"><i class="fa fa-remove"></i></a>\n\
                                    </div>\n\
                                </div>');
        });

//        $('body').on('change',".contractAuditorName", function (){
//            alert($(this).val());
//        });

        $('body').on("click", ".removeAuditDate", function () {
//        alert("Atleast one action item required");
            $(this).parent("div.deleteNewAuditParent").parent('div.newAuditRowParent').remove();
        });

        $('body').on("click", ".removeAuditDateEdit", function () {
//        alert("Atleast one action item required");
            $(this).parent("div.deleteNewAuditParentEdit").parent('div.newAuditRowParentEdit').remove();
        });

        $('.stage-i').change(function () {
            if ((this).checked) {
                $('.stage-i').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $('.editstage-i').change(function () {
            if ((this).checked) {
                $('.editstage-i').prop('checked', false);
                $(this).prop('checked', true);
            }
        });

        $('.tllastn').change(function () {
            if ((this).checked) {
                $('.tllastn').attr("checked");
                $('.tllasty').removeAttr("checked");
                $('.tllastyc').val("");
                $('.tllastyc').attr("readonly", true);
                $('.tllast').val("NO");
            }
        });

        $('.tllasty').change(function () {
            if ((this).checked) {
                $('.tllasty').attr("checked");
                $('.tllastn').removeAttr("checked");
                $('.tllast').val("YES");
                $('.tllastyc').attr("readonly", false);
            }
        });

        $customerid = "";
        $('.createAuditNotification').click(function () {
            $('.audit-noti-service').val($(this).attr('data-service1'));
            $('.audit-noti-service1').val($(this).attr('data-service'));
            $('.audit-noti-customer').val($(this).attr('data-customer'));
            $(".audit-service-type").val($(this).attr("data-svc"));
            $customerid = $(this).attr('data-customer');

            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customid: $(this).attr('data-customer'),
                    svcID: $(this).attr('data-service'),
                    svcids: $(this).attr('data-service1'),
                },
                success: function (data)
                {
                    $('.auditForOrg').val(data[0]['customer_name']);
                    $('.auditLocation').val(data[0]['address']);
                    $('.auditTelephone').val(data[0]['contact']);
                    $('.auditEmail').val(data[0]['email_id']);
                    $('.auditReferance').val(data[3]['aggrement_no']);
                    $('.auditContactPerson').val(data[0]['contact_person']);
//                    $('.auditDates').val(moment(data[1]['application_date']).format('DD.MM.YYYY'));
                    $('.auditStandard').val(data[1]['certification']);
                    $('input[type="text"]').trigger('change');
                    $('.auditLocation').trigger('change');
                    $('#auditNotification').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $(".contractAuditorName").change(function () {
//            alert($(this).val());
            $.ajax({
                url: "getNotificationInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customerid: $customerid,
                    auditorid: $(this).val(),
                },
                success: function (data)
                {
//                    alert(data);
                    if (data == '2') {
                        $("#auditor-details").show();
                        $('.confirm-ok').click(function () {
                            $('#auditor-details').hide();
                        });
                        $(".contractAuditorName").val("");
                    }
                },
                error: function (data, status, err)
                {
                    // Suppress debug alert; keep silent on error
                }
            });
        });

        
        $('.editAuditNotification').click(function () {
            $('.audit-noti-id-edit').val($(this).attr('data-notification'));
            $('.audit-noti-service').val($(this).attr('data-service1'));
            $('.audit-noti-service1').val($(this).attr('data-service'));
            $('.audit-noti-customer').val($(this).attr('data-customer'));
            $(".audit-service-type").val($(this).attr("data-svc"));
            $customerid = $(this).attr('data-customer');
            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    notificationId: $(this).attr('data-notification'),
                    serviceid: $(this).attr('data-service')
                },
                success: function (data)
                {
                    $(".auditSubject").val(data[0]['audit_subject']);
                    $('.auditForOrg').val(data[0]['for_org']);

                    var $intimation_date = formatStage1DateValue(data[0]['intimation_date']);
                    $('.intimationDate').val($intimation_date);

                    $('.auditLocation').val(data[0]['location']);
                    $('.auditReferance').val(data[0]['audit_ref_no']);
                    $('.auditStandard').val(data[0]['audit_standard']);
                    $('.auditContactPerson').val(data[0]['audit_contact_person']);

                    var rawAuditDates = data[0]['audit_dates'];
                    var $audit_dates = formatStage1DateList(rawAuditDates);
                    if ($audit_dates === "") {
                        $audit_dates = $intimation_date;
                    }
                    $('.auditDates').val($audit_dates);

                    $('.auditDesignation').val(data[0]['audit_designation']);
                    $('.auditTelephone').val(data[0]['audit_telephone']);
                    $('.auditEmail').val(data[0]['audit_email']);
                    $('.stage-i[value = "' + data[0]["special_audit"] + '"]').prop('checked', true).trigger("change");

                    // Build multiple Audit date(s) inputs from the final resolved value
                    $(".newAuditDates").empty();
                    var auditDatesSource = $audit_dates && $audit_dates !== "" ? $audit_dates : (rawAuditDates || "");
                    if (auditDatesSource && typeof auditDatesSource === 'string') {
                        var auditDate = auditDatesSource.split(',');
                        var auditDateLen = auditDate.length;
                        var max = (Math.max(auditDateLen));
//                    alert(personLen);
                        for (var key = 0; key < max; key++) {
//                        alert(auditDate[key]);
                            //                        var no=contact[key];                        
                            var no = key <= auditDateLen && auditDate[key] != null && String(auditDate[key]).trim() !== "" ? formatStage1DateValue(auditDate[key]) : "";
//                        alert(no);
                            if (no === "") {
                                continue;
                            }
                            if (key == 0) {
                                $('.auditDates').val(no);
                            } else {
                                $('.newAuditDates').append('<div class="row newAuditRowParent">\n\
                                        <div class="col-sm-10 form-group float-label-control">\n\
                                            <label>Audit date(s):</label>\n\
                                            <input type="text" class="auditDates" name="auditDates" placeholder="Audit date(s):" value="' + no + '">\n\
                                        </div>\n\
                                        <div class="col-sm-1 form-group float-label-control deleteNewAuditParent">\n\
                                            <a class="removeAuditDate" style="" title="Remove Multiple AuditDates"><i class="fa fa-remove"></i></a>\n\
                                        </div>\n\
                                    </div>');
                            }
                        }
                    }
                    var allAuditors = ("<?php echo $allAuditors; ?>");
//                    var auditors = allAuditors.split(',');
//                    var option = "";
//                    for (var key in auditors) {
//                        if (auditors[key] != null && auditors[key] != "") {
//                            option += '<option value="' + auditors[key] + '">' + auditors[key] + '</option>';
//                        }
//                    }

                    var allAuditorsid = ("<?php echo $allAuditorsid ?>");
//            alert(allAuditorsid);
                    var auditorid = allAuditorsid.split(',');
                    var allAuditoridLen = auditorid.length;

                    var allAuditors = ("<?php echo $allAuditors; ?>");
                    var auditors = allAuditors.split(',');
                    var allAuditorsLen = auditors.length;

                    var max = (Math.max(allAuditoridLen, allAuditorsLen));
                    var option = "";
                    option += '<option value="">Choose Auditor</option>';
                    for (var key = 0; key < max; key++) {
                        var audid = key <= allAuditoridLen && auditorid[key] != null && auditorid[key] != "" ? auditorid[key] : "";
                        var audname = key <= allAuditorsLen && auditors[key] != null && auditors[key] != "" ? auditors[key] : "";
                        option += '<option value="' + audid + '">' + audname + '</option>';
                    }

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
                    $(".removable_auditor_info").remove();
                    for (var key in data[1]) {
                        $rowCounter++;
                        if(key == 0){
                            $(".contractAuditorName").val(data[1][0]['auditor_id']);
                            $(".auditorCategory").val(data[1][0]['auditor_category']);
                            $(".auditorWork").val(data[1][0]['audit_wokrdays']);
                            $(".auditorAcceptance").val(data[1][0]['auditor_acceptance']);
                        } else {
                        var option2 = '<option value="' + data[1][key]['auditor_id'] + '"selected>' + data[1][key]['auditor_name'] + '</option>';
                        var option3 = '<option value="' + data[1][key]['audit_contact'] + '"selected>' + data[1][key]['auditor_name'] + ' - ' + data[1][key]['audit_contact'] + '</option>';
//                        var option2 = '<option value="' + data[1][key]['auditor_name'] + '"selected>' + data[1][key]['auditor_name'] + '</option>';
                        $('.editedNotification').append('<tr class="audit_subsection removable_auditor_info" id="cb_subsection_0" style="display: ">\n\
                                                        <td class="" width="20%" style="padding:2px;">\n\
                                                        <div class="table-edit-control">\n\
                                                          <select data-row-id="" class="contractAuditorName form-control" id="contractAuditorName" name="contractAuditorName">\n\
                                                       ' + option2 + '  \n\
                                                     ' + option + '\n\
                                                          </select>\n\
                                                        </div>\n\
                                                        </td>\n\
                                                        <td class="" width="20%" style="padding:2px;">\n\
        <div class="table-edit-control">\n\
            <select class="auditorCategory form-control" id="auditorCategory" name="auditorCategory" required>\n\
                <option value="">Choose Category</option>\n\
                <?php foreach ($category as $name) { ?>
                    <option value="<?php echo $name['categories_name'] ?>"><?php echo $name['categories_name'] ?></option>\n\
                <?php } ?>\n\
            </select>\n\
        </div>\n\
    </td>\n\
                                                        <td class="" width="20%" style="padding:2px;">\n\
                                                        <div class="table-edit-control">\n\
                                                        <input type="text" min="1" data-row-id="" class="auditorWork" id="auditorWork" name="auditorWorkii" value="' + data[1][key]['audit_wokrdays'] + '" placeholder="">\n\
                                                        </div>\n\
                                                        </td>\n\
                                                        <td class="" width="20%" style="padding:2px;">\n\
                                                        <div class="table-edit-control">\n\
                                                        <select class="auditorAcceptance form-control" id="auditorAcceptance" name="auditorAcceptance" >\n\
                                                        <option value="' + data[1][key]['auditor_acceptance'] + '">' + data[1][key]['auditor_acceptance'] + '</option><option value="">Select</option><option value="Yes">Yes</option><option value="No">No</option></select></div>\n\
                                                        </td>\n\
                                                        <td class="" width="10%" style="padding:2px;">\
        <button class="btn btn-danger removeRowButton">Remove</button>\
    </td>\
                                                        </tr>');
                                                        const newAuditorCategorySelect = $('.editedNotification').find('.auditorCategory').last();
newAuditorCategorySelect.val(data[1][key]['auditor_category']);

                    }
                    }$rowCB = $rowCounter;
//                    alert(data[0]["auditor_check_last_two"]);
                    if (data[0]["auditor_check_last_two"] == "YES")
                    {
                        $('.tllasty').prop("checked", true);
                    } else if (data[0]["auditor_check_last_two"] == "NO")
                    {
                        $('.tllastn').prop("checked", true);
                    }
                    $('.tllast').val(data[0]["auditor_check_last_two"]);
                    $('.tllastyc').val(data[0]["auditor_check_comm"]);
                    $('input[type="text"]').trigger('change');
                    $('.auditLocation').trigger('change');
                    $('.appointedby').val(data[0]['appointed_by']);
                    $('.mcldesignation').val(data[0]['mcl_designation']);

                    $('#auditNotification').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.updateAuditNoti').click(function (e) {

            var auditDatesEditArrayEdit = [];
            $('.auditDatesEdit').each(function () {
                auditDatesEditArrayEdit.push($(this).val());
            });
            $('.auditDatesEditHiddenEdit').val(auditDatesEditArrayEdit.join(", "));
//            alert($('.auditDatesEditHiddenEdit').val());
            var auditorNameArrayEdit = [];
            $('.auditorNameEdit').each(function () {
                auditorNameArrayEdit.push($(this).val());
            });
            $('.auditorNameHiddenEdit').val(auditorNameArrayEdit.join("~"));

            var auditorCategoryArrayEdit = [];
            $('.auditorCategoryEdit').each(function () {
                auditorCategoryArrayEdit.push($(this).val());
            });
            $('.auditorCategoryHiddenEdit').val(auditorCategoryArrayEdit.join("~"));

            var auditorWorkArrayEdit = [];
            $('.auditorWorkEdit').each(function () {
                auditorWorkArrayEdit.push($(this).val());
            });
            $('.auditorWorkHiddenEdit').val(auditorWorkArrayEdit.join("~"));

            var auditorAcceptanceArrayEdit = [];
            $('.auditorAcceptanceEdit').each(function () {
                auditorAcceptanceArrayEdit.push($(this).val());
            });
            $('.auditorAcceptanceHiddenEdit').val(auditorAcceptanceArrayEdit.join("~"));
//            alert($('.auditorContactHiddenEdit').val());

            if (validateStage1edit()) {
                e.preventDefault();
                return;
            }

        });
        $(document.body).on('click', '.addAuditLink', function () {

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
            var autitorsSelect = '<select data-row-id="" class="contractAuditorName form-control" id="contractAuditorName" name="contractAuditorName" >\n\
                                    <option value="">Choose Auditors</option>\n\
                                    ' + option + '\n\
                                </select>';

            $count = $rowCB + 1;
            var table = document.getElementById("notification_details");
            var row = table.insertRow($count);

            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            var cell3 = row.insertCell(2);
            var cell4 = row.insertCell(3);

            cell1.style.padding = "0px";
            cell2.style.padding = "0px";
            cell3.style.padding = "0px";
            cell4.style.padding = "0px";

            cell1.innerHTML = '<div class="table-edit-control"><label></label> ' + autitorsSelect + '</div>';
            cell2.innerHTML = '<div class="table-edit-control"><label></label><select class="auditorCategory form-control" id="auditorCategory" name="auditorCategory"><?php foreach ($category as $name) { ?><option value="<?php echo $name['categories_name'] ?>" <?php if ($name['categories_name'] == $selectedCategoryId) { echo 'selected'; } ?>><?php echo $name['categories_name'] ?></option><?php } ?></select></div>';
            cell3.innerHTML = '<div class="table-edit-control"><label></label><input type="text" min="1" data-row-id="" class="auditorWork" id="auditorWork"  name="auditorWork" placeholder="" /></div>';
            cell4.innerHTML = '<div class="table-edit-control"><select class="auditorAcceptance form-control" id="auditorAcceptance" name="auditorAcceptance" required><option value="">Select</option><option value="Yes">Yes</option><option value="No">No</option></select></div>';

            row.className = "audit_subsection";
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

        $('.addAuditNoti').click(function (e) {
            if (validateStage1()) {
                e.preventDefault();
                return;
            }

            var auditDateArray = [];
            $('.auditDates').each(function () {
                auditDateArray.push($(this).val());
            });
            $('.auditDatesHidden').val(auditDateArray.join(", "));

            var auditorNameArray = [];
            $('.contractAuditorName').each(function () {
//                alert($(this).val());
                auditorNameArray.push($(this).val());
            });
            $('.auditorNameHidden').val(auditorNameArray.join("~"));
//            alert($('.auditorNameHidden').val());

            var auditorCategoryArray = [];
            $('.auditorCategory').each(function () {
                auditorCategoryArray.push($(this).val());
            });
            $('.auditorCategoryHidden').val(auditorCategoryArray.join("~"));

            var auditorWorkArray = [];
            $(".auditorWork").each(function () {
                auditorWorkArray.push($(this).val());
            });
            $('.auditorWorkHidden').val(auditorWorkArray.join("~"));

            var auditorAcceptArray = [];
            $(".auditorAcceptance").each(function () {
                auditorAcceptArray.push($(this).val());
            });
            $('.auditorAcceptanceHidden').val(auditorAcceptArray.join("~"));

        });

        $('.addAuditNotiFdf').click(function () {
            var auditorNameArray = [];
            $('.contractAuditorName').each(function () {
                auditorNameArray.push($(this).val());
            });
            $('.auditorNameHidden').val(auditorNameArray.join("~"));

            var auditorCategoryArray = [];
            $('.auditorCategory').each(function () {
                auditorCategoryArray.push($(this).val());
            });
            $('.auditorCategoryHidden').val(auditorCategoryArray.join("~"));

            var auditorContactArray = [];
            $(".auditorContact").each(function () {
                auditorContactArray.push($(this).val());
            });
            $('.auditorContactHidden').val(auditorContactArray.join("~"));

            var auditorWorkArray = [];
            $(".auditorWork").each(function () {
                auditorWorkArray.push($(this).val());
            });
            $('.auditorWorkHidden').val(auditorWorkArray.join("~"));
        });

        $('.formAuditNotification').click(function () {
            $('.formAuditNotificationDiv').slideToggle('slow');
        });

        $('.formNotification').click(function () {
            $('.formNotificationDiv').slideToggle('slow');
        });

    });

</script>
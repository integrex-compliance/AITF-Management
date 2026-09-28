<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once './dbConnect.php';
require_once __DIR__ . '/include/auditor_division_helper.php';
aitf_ensure_auditor_division_schema($db);
date_default_timezone_set('Asia/Kolkata');

$allAuditorsContact = "";
$allAuditorsid = "";
$allAuditors = "";
$allContact = "";
$allAuditorsList = array();
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
    $audEmail = trim($name['auditor_email'] ?? '');
    $audDisplay = $name['auditor_name'] . ($audEmail !== '' ? ' (' . $audEmail . ')' : '');
    $allAuditorsid .= $name['auditors_id'] . ",";
    $allAuditors .= $audDisplay . ",";
    $allAuditorsContact .= $name['auditor_name'] . " - " . $name['auditor_number'] . ",";
    $allContact .= $name['auditor_number'] . ",";
    $allAuditorsList[] = array(
        'auditors_id' => (string)$name['auditors_id'],
        'auditor_name' => $name['auditor_name'],
        'auditor_email' => $audEmail,
        'auditor_number' => $name['auditor_number'] ?? ''
    );
}
?>

<script src="js/date-chronology-validation.js?v=2"></script>
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

<div class="modal fade" id="auditNotification" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
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
                                <input type="text" class="auditSubject" id="auditSubject" name="auditSubject"
                                    placeholder="Subject:"
                                    value=" Intimation for acceptance to the below specified audit, auditor assignment and declaration for impartiality and confidentiality by AITF audit team member/s."
                                    required>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>For the organization:</label>
                                <input type="text" class="auditForOrg" id="auditForOrg" name="auditForOrg"
                                    placeholder="For the organization:" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 orm-group float-label-control">
                                        <label>Date of Intimation:</label>
                                        <input type="text" class="intimationDate" id="intimationDate"
                                            name="intimationDate" placeholder="Date of Intimation:" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <div id="intimationDateError" style="display: none">
                                            <small><span id="intimationDateErrorMsg"
                                                    style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Site(s):</label>
                                <textarea rows="4" class="auditLocation" id="auditLocation" name="auditLocation"
                                    placeholder="Site(s):" required></textarea>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row ">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Agreement No:</label>
                                        <input readonly type="text" class="auditReferance" id="auditReferance"
                                            name="auditReferance" placeholder="Agreement No:" required>
                                    </div>
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Assessment Standard/s:</label>
                                        <textarea readonly name="auditStandard"
                                            class="form-control form-group auditStandard" id="auditStandard"
                                            placeholder="Assessment Standard" required></textarea>
                                        <!--<input type="text" class="auditStandard" id="auditStandard" name="auditStandard" placeholder="For the standard:" required>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Contact Person Name:</label>
                                <input type="text" class="auditContactPerson" id="auditContactPerson"
                                    name="auditContactPerson" placeholder="Contact Person Name:" required>
                            </div>
                            <div class="col-sm-6 ">
                                <div class="row">
                                    <div class="col-sm-10 form-group float-label-control">
                                        <div class="row">
                                            <div class="col-sm-12 form-group float-label-control">
                                                <label>Audit date(s):</label>
                                                <input type="text" class="auditDates" name="auditDates"
                                                    placeholder="Audit date(s):" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12 ">
                                                <div id="auditDatesError" style="display: none">
                                                    <small><span id="auditDatesErrorMsg"
                                                            style="color: #c9513c; font-style: italic;"></span></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-1 form-group float-label-control">
                                        <a class="newAuditDate" style="" title="Add Multiple AuditDates"><i
                                                class="fa fa-plus"></i></a>
                                    </div>
                                </div>
                                <div class="newAuditDates"></div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-4 form-group float-label-control">
                                <div class="row form-group float-label-control">
                                    <label>Designation:</label>
                                    <input type="text" class="auditDesignation" id="auditDesignation"
                                        name="auditDesignation" placeholder="Designation:" required>
                                </div>
                                <div class="row form-group float-label-control">
                                    <label>Telephone / Mobile:</label>
                                    <input type="text" class="auditTelephone" id="auditTelephone" name="auditTelephone"
                                        placeholder="Telephone / Mobile:" required>
                                </div>
                                <div class="row form-group float-label-control">
                                    <label>E-mail:</label>
                                    <input type="email" class="auditEmail" id="auditEmail" name="auditEmail"
                                        placeholder="E-mail:" required>
                                </div>
                            </div>
                            <div class="col-sm-8 form-group ">
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group ">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i"
                                            value="Stage-i" checked>Stage-I Audit
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i"
                                            value="Stage-ii">Stage II Audit
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i"
                                            value="Surveillance">Surveillance
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i"
                                            value="Re-Certification Audit">Re-Certification Audit
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i"
                                            value="Follow-up Audit">Follow-up Audit
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-i" id="stage-i" name="stage-i"
                                            value="Special Audit">Special Audit ____
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="audit-noti-service" id="audit-noti-service"
                            name="audit-noti-service" value="">
                        <input type="hidden" class="audit-noti-service1" id="audit-noti-service1"
                            name="audit-noti-service1" value="">
                        <input type="hidden" class="audit-noti-customer" id="audit-noti-customer"
                            name="audit-noti-customer" value="">
                        <input type="hidden" class="audti-service-type" id="audti-service-type"
                            name="audti-service-type" value="">
                        <input type="hidden" class="audit-service-type" id="audit-service-type"
                            name="audit-service-type" value="">
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formNotification">
                            <span><i class="fa  ">Declaration for impartiality and confidential by Aitf-MCL team audit
                                    team member/s:</i></span>
                        </div>
                    </div>
                    <div class="formNotificationDiv" style="display: none;">
                        <div class="auditor-division-banner-s1" style="display:none; margin: 10px 0;"></div>
                        <div class="row">
                            <div class="col-sm-12 table-responsive">
                                <table class="table table-bordered table-hover table-sortable service_details"
                                    id="notification_details" style="width: 100%">
                                    <thead>
                                        <tr style="background: #f8fafc;">
                                            <th width="36%" style="padding: 8px 6px; vertical-align: middle;">
                                                <small style="font-weight: 700; color: #475569; text-transform: uppercase;">Auditor Name</small>
                                            </th>
                                            <th width="24%" style="padding: 8px 6px; vertical-align: middle;">
                                                <small style="font-weight: 700; color: #475569; text-transform: uppercase;">Category</small>
                                            </th>
                                            <th width="14%" style="padding: 8px 6px; vertical-align: middle;">
                                                <small style="font-weight: 700; color: #475569; text-transform: uppercase;">Work Days</small>
                                            </th>
                                            <th width="16%" style="padding: 8px 6px; vertical-align: middle;">
                                                <small style="font-weight: 700; color: #475569; text-transform: uppercase;">Acceptance</small>
                                            </th>
                                            <th width="10%" style="padding: 8px 6px; text-align: center; vertical-align: middle;">
                                                <a class="btn btn-xs btn-primary addAuditLink" data-row-id=""
                                                    data-row-count="" title="Add Auditor" style="padding: 3px 8px; border-radius: 4px; font-weight: 600;">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="editedNotification">
                                        <tr class="audit_subsection previousCBData" id="cb_subsection_0"
                                            style="display: ">
                                            <td width="36%" style="padding: 6px; vertical-align: middle;">
                                                <div class="table-edit-control">
                                                    <!--<input type="text" data-row-id="" class="contractAuditorName" id="contractAuditorName" name="contractAuditorName" value="" placeholder=""/>-->
                                                    <select class="contractAuditorName form-control"
                                                        id="contractAuditorName" name="contractAuditorName" required style="width: 100%;">
                                                        <option value="">Choose Auditors</option>
                                                        <?php foreach ($auditors as $name) { 
                                                            $audEmail = trim($name['auditor_email'] ?? '');
                                                        ?>
                                                            <option value="<?php echo $name['auditors_id'] ?>" data-email="<?php echo htmlspecialchars($audEmail); ?>">
                                                                <?php echo htmlspecialchars($name['auditor_name']); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td width="24%" style="padding: 6px; vertical-align: middle;">
                                                <div class="table-edit-control">
                                                    <select class="auditorCategory form-control" id="auditorCategory"
                                                        name="auditorCategory" required style="width: 100%;">
                                                        <option value="">Choose Category</option>
                                                        <?php foreach ($category as $name) { ?>
                                                            <option value="<?php echo $name['categories_name'] ?>">
                                                                <?php echo $name['categories_name'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td width="14%" style="padding: 6px; vertical-align: middle;">
                                                <div class="table-edit-control">
                                                    <input type="text" min="1" data-row-id="" class="auditorWork form-control"
                                                        id="auditorWork" name="depositWork" value="" placeholder=""
                                                        required style="width: 100%;">
                                                </div>
                                            </td>
                                            <td width="16%" style="padding: 6px; vertical-align: middle;">
                                                <div class="table-edit-control">
                                                    <select class="auditorAcceptance form-control"
                                                        id="auditorAcceptance" name="auditorAcceptance" required style="width: 100%;">
                                                        <option value="">Select</option>
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td width="10%" style="padding: 6px; text-align: center; vertical-align: middle; color: #94a3b8;">
                                                &mdash;
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 table-responsive">
                                <table class="table table-bordered table-hover table-sortable service_details"
                                    id="notification_details" style="width: 100%">
                                    <th> Whether same team leader was appointed for last two consequence audits (Yes /
                                        No )?</th>
                                    <tr>
                                        <td><br>
                                            <div class="col-sm-8 form-group ">
                                                <div class="col-sm-6 form-group ">
                                                    <input type="checkbox" class="tllasty" id="tllasty" name="tllasty"
                                                        value="yes">YES
                                                </div>
                                                <div class="col-sm-6 form-group">
                                                    <input type="checkbox" class="tllastn" id="tllastn" name="tllastn"
                                                        value="no">NO
                                                </div>
                                            </div>
                                            <div class="col-sm-4 form-group float-label-control">
                                                <div class="row form-group float-label-control">
                                                    <label></label>
                                                    <input type="text" class="tllastyc" id="tllastyc" name="tllastyc"
                                                        placeholder="If yes?:" readonly>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <label>Appointed By:</label>
                            <input type="text" class="appointedby" id="appointedby" name="appointedby"
                                placeholder="Appointed By:" required>
                        </div>
                        <div class="col-sm-6 form-group float-label-control">
                            <label>AITF-MCL Designation:</label>
                            <input type="text" class="mcldesignation" id="mcldesignation" name="mcldesignation"
                                placeholder="AITF-MCL Designation:" required>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 ">
                                <div id="teamLeaderError" style="display: none">
                                    <small><span id="teamLeaderErrorMsg"
                                            style="color: #c9513c; font-style: italic;"></span></small>
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
                <button title="Click here to close"
                    class="btn btn-primary btn-round btn-round-sm btn-sm confirm-ok">Ok</button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        $('#auditNotification').on('shown.bs.modal', function () {
            // Re-initialize chronology validator when modal opens
            DateChronologyValidator.init({
                customerId: $('.audit-noti-customer').val() || $('.customerId').val(),
                typedServiceId: $('.audit-noti-service').val() || $('.serviceId').val(),
                fields: {
                    'stage1_intimation_date': { input: '.intimationDate', errorDiv: '#intimationDateError', errorMsg: '#intimationDateErrorMsg' },
                    'stage1_audit_date': { input: '.auditDates', errorDiv: '#auditDatesError', errorMsg: '#auditDatesErrorMsg' },
                    'intimation_date': { input: '.intimationDate', errorDiv: '#intimationDateError', errorMsg: '#intimationDateErrorMsg' },
                    'audit_date': { input: '.auditDates', errorDiv: '#auditDatesError', errorMsg: '#auditDatesErrorMsg' }
                },
                submitBtn: '.addAuditNoti'
            });
        });
    });

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
            autoclose: true,
            onSelect: function () {
                validateStage1();
            }
        });
        $("#intimationDate").keydown(function (e) {
            //            e.preventDefault();
        });

        $('.intimationDate').on('change blur', function () {
            if ($(this).val() !== "") {
                validateStage1();
            } else {
                $('#intimationDateError').hide();
                $('#intimationDate').removeClass('inputHighlight');
            }
        });

        $(document).on('change blur', '.auditDates', function () {
            if ($(this).val() !== "") {
                validateStage1();
            } else {
                $('#auditDatesError').hide();
                $('#auditDates').removeClass('inputHighlight');
            }
        });

        $('#intimationDateEdit').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true,
            onSelect: function () {
                validateStage1edit();
            }
        });
        $("#intimationDateEdit").keydown(function (e) {
            //            e.preventDefault();
        });

        $('.intimationDateEdit').on('change blur', function () {
            if ($(this).val() !== "") {
                validateStage1edit();
            } else {
                $('#intimationDateEditError').hide();
                $('#intimationDateEdit').removeClass('inputHighlight');
            }
        });

        $(document).on('change blur', '.auditDatesEdit', function () {
            if ($(this).val() !== "") {
                validateStage1edit();
            } else {
                $('#auditDatesEditError').hide();
                $('#auditDatesEdit').removeClass('inputHighlight');
            }
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
                    svc_id: $('.audit-noti-service').val() || $('.audit-noti-service1').val(),
                    svc_id1: $('.audit-noti-service1').val(),
                    service_type: $('.audit-service-type').val(),
                    cust_id: $('.audit-noti-customer').val()
                },
                async: false,
                success: function (data) {
                    data = $.trim(data);
                    if (data !== "0" && data !== "") {
                        var svcType = $('.audit-service-type').val();
                        if (svcType === 'service-type-sai' || svcType === 'service-type-saii') {
                            $('#intimationDateEditErrorMsg').text("Date should be greater than or equal to information updation review date (" + data + ")");
                        } else {
                            $('#intimationDateEditErrorMsg').text("Date should be greater than or equal to assessment planning date (" + data + ")");
                        }
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

        $('.auditDatesEdit').each(function () {
            if (($('.auditDatesEdit').val() != "") &&
                moment($('#intimationDateEdit').val(), "DD.MM.YYYY") >= moment($('.auditDatesEdit').val(), "DD.MM.YYYY")) {
                $('#auditDatesEditErrorMsg').text("Date should be greater than intimation date");
                $('#auditDatesEditError').show();
                $('#auditDatesEdit').addClass('inputHighlight');
                $isError = true;
            } else {
                $('#auditDatesEditError').hide();
                $('#auditDatesEdit').removeClass('inputHighlight');
            }
        });
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
                    svc_id: $('.audit-noti-service').val() || $('.audit-noti-service1').val(),
                    svc_id1: $('.audit-noti-service1').val(),
                    service_type: $('.audit-service-type').val(),
                    cust_id: $('.audit-noti-customer').val()
                },
                async: false,
                success: function (data) {
                    data = $.trim(data);
                    if (data !== "0" && data !== "") {
                        var svcType = $('.audit-service-type').val();
                        if (svcType === 'service-type-sai' || svcType === 'service-type-saii') {
                            $('#intimationDateErrorMsg').text("Date should be greater than or equal to information updation review date (" + data + ")");
                        } else {
                            $('#intimationDateErrorMsg').text("Date should be greater than or equal to assessment planning date (" + data + ")");
                        }
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

        $('.auditDates').each(function () {
            if (($('.auditDates').val() != "") &&
                moment($('#intimationDate').val(), "DD.MM.YYYY") >= moment($('.auditDates').val(), "DD.MM.YYYY")) {
                $('#auditDatesErrorMsg').text("Date should be greater than intimation date");
                $('#auditDatesError').show();
                $('#auditDates').addClass('inputHighlight');
                $isError = true;
            } else {
                $('#auditDatesError').hide();
                $('#auditDates').removeClass('inputHighlight');
            }
        });
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

        var aitfAppointedReviewers = [];
        var aitfAppointedReviewerAuditorIds = [];

        function loadAppointedReviewersForAuditorCheck(customerId, serviceId) {
            aitfAppointedReviewers = [];
            aitfAppointedReviewerAuditorIds = [];
            if (!customerId && !serviceId) return;
            $.ajax({
                url: "checkReviewerConflict.php",
                type: "GET",
                dataType: "json",
                data: {
                    action: 'get_appointed_reviewers',
                    customerId: customerId,
                    serviceId: serviceId
                },
                success: function(resp) {
                    if (resp && resp.success) {
                        aitfAppointedReviewers = resp.reviewers || [];
                        aitfAppointedReviewerAuditorIds = resp.auditor_ids || [];
                    }
                }
            });
        }

        function checkAuditorConflictWithReviewers(auditorId, auditorName) {
            // Temporarily disabled
            return false;
        }

        function showAuditorReviewerConflictDialogue(auditorName, conflictingReviewer) {
            var nameToDisplay = auditorName || conflictingReviewer || "This user";
            var msg = nameToDisplay + " is Reviewer of this client, cannot set as Auditor.\n\nPlease select another Auditor.";
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({
                    icon: 'error',
                    title: 'Conflict Detected',
                    html: '<div class="aitf-conflict-card">' +
                              '<div class="aitf-conflict-card-title">' +
                                  '<span class="aitf-conflict-card-name">' + nameToDisplay + '</span> is Reviewer of this client, cannot set as Auditor.' +
                              '</div>' +
                              '<div class="aitf-conflict-card-sub">' +
                                  'Please select another Auditor.' +
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
        }

        var aitfNotificationEligibleAuditors = [];
        var aitfNotificationClientNaceClasses = [];
        var aitfNotificationHasNaceClasses = false;
        var aitfNotificationClientDivisions = [];
        var aitfNotificationHasDivisions = false;
        var aitfNotificationFilterMode = 'none';
        var aitfNotificationAllAuditors = <?php echo json_encode($allAuditorsList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

        function renderNotificationAuditorOptions(selectedAuditorId, fallbackName) {
            var html = '<option value="">Choose Auditors</option>';
            var isFiltered = (aitfNotificationHasNaceClasses || aitfNotificationHasDivisions);
            var list = isFiltered ? aitfNotificationEligibleAuditors : aitfNotificationAllAuditors;
            var foundSelected = false;

            if (isFiltered && (!list || list.length === 0)) {
                if (aitfNotificationHasNaceClasses) {
                    html += '<option value="" disabled>No auditors assigned to NACE Class ' + aitfNotificationClientNaceClasses.join(', ') + ' in Master Creator</option>';
                } else {
                    html += '<option value="" disabled>No auditors assigned to Division ' + aitfNotificationClientDivisions.join(', ') + ' in Master Creator</option>';
                }
            } else if (list && list.length > 0) {
                for (var i = 0; i < list.length; i++) {
                    var a = list[i];
                    var aid = String(a.auditors_id);
                    var isSel = (selectedAuditorId && String(selectedAuditorId) === aid);
                    if (isSel) foundSelected = true;
                    var email = (a.auditor_email && a.auditor_email.trim() !== '') ? ' (' + a.auditor_email.trim() + ')' : '';
                    html += '<option value="' + aid + '"' + (isSel ? ' selected' : '') + ' data-email="' + (a.auditor_email || '') + '">' + a.auditor_name + email + '</option>';
                }
            }

            if (selectedAuditorId && !foundSelected && fallbackName) {
                html += '<option value="' + selectedAuditorId + '" selected>' + fallbackName + '</option>';
            }

            return html;
        }

        function isAuditorIdQualified(aid) {
            if (!aid) return false;
            aid = String(aid).trim();
            if (!aitfNotificationHasNaceClasses && !aitfNotificationHasDivisions) {
                return true;
            }
            var eligible = aitfNotificationEligibleAuditors || [];
            for (var i = 0; i < eligible.length; i++) {
                if (String(eligible[i].auditors_id).trim() === aid) {
                    return true;
                }
            }
            return false;
        }

        function hasAtLeastOneQualifiedAuditor() {
            // Temporarily bypassed: allow any auditor to be assigned
            return true;
        }

        function updateAuditorRowVisualStatus($select) {
            var val = String($select.val() || '').trim();
            var $row = $select.closest('tr');
            var $parent = $select.closest('.table-edit-control');
            $parent.find('.auditor-qualified-badge').remove();

            if (!val || val === '') {
                $row.css({
                    'background-color': '',
                    'border-left': ''
                });
                $select.css({
                    'border': '',
                    'background-color': '',
                    'color': '',
                    'font-weight': '',
                    'box-shadow': ''
                });
                return;
            }

            if (isAuditorIdQualified(val)) {
                $row.css({
                    'background-color': '#f4fbf6',
                    'border-left': '4px solid #28a745'
                });
                $select.css({
                    'border': '1.5px solid #28a745',
                    'background-color': '#ffffff',
                    'color': '#155724',
                    'font-weight': '600',
                    'box-shadow': '0 0 0 1px rgba(40,167,69,0.15)'
                });
                $select.after('<div class="auditor-qualified-badge" style="margin-top: 4px;"><span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; background: #e8f5e9; color: #1e7e34; border: 1px solid #c3e6cb;"><i class="fa fa-check-circle" style="color: #28a745;"></i> Code Qualified</span></div>');
            } else {
                $row.css({
                    'background-color': '#ffffff',
                    'border-left': '4px solid transparent'
                });
                $select.css({
                    'border': '1px solid #ced4da',
                    'background-color': '#ffffff',
                    'color': '#495057',
                    'font-weight': 'normal',
                    'box-shadow': 'none'
                });
            }
        }

        function renderNotificationAuditorOptions(selectedAuditorId, fallbackName) {
            var html = '<option value="">Choose Auditors</option>';
            var list = aitfNotificationAllAuditors;
            var foundSelected = false;

            if (list && list.length > 0) {
                for (var i = 0; i < list.length; i++) {
                    var a = list[i];
                    var aid = String(a.auditors_id);
                    var isSel = (selectedAuditorId && String(selectedAuditorId) === aid);
                    if (isSel) foundSelected = true;
                    var isQual = isAuditorIdQualified(aid);
                    var qualBadge = isQual ? ' ✓ [Qualified]' : '';
                    var optStyle = isQual ? 'font-weight: bold; color: #155724;' : '';
                    var optTitle = a.auditor_name + (a.auditor_email ? ' (' + a.auditor_email + ')' : '');
                    html += '<option value="' + aid + '"' + (isSel ? ' selected' : '') + ' data-email="' + (a.auditor_email || '') + '" title="' + optTitle + '" data-qualified="' + (isQual ? '1' : '0') + '" style="' + optStyle + '">' + a.auditor_name + qualBadge + '</option>';
                }
            }

            if (selectedAuditorId && !foundSelected && fallbackName) {
                var isQualFb = isAuditorIdQualified(selectedAuditorId);
                var qualBadgeFb = isQualFb ? ' ✓ [Qualified]' : '';
                html += '<option value="' + selectedAuditorId + '" selected data-qualified="' + (isQualFb ? '1' : '0') + '">' + fallbackName + qualBadgeFb + '</option>';
            }

            return html;
        }

        function renderNotificationAllAuditorOptions(selectedAuditorId, fallbackName) {
            return renderNotificationAuditorOptions(selectedAuditorId, fallbackName);
        }

        function syncAuditorSelectOptions() {
            var selectedAuditorIds = [];
            $('.contractAuditorName').each(function () {
                var val = String($(this).val() || '').trim();
                if (val !== '') {
                    selectedAuditorIds.push(val);
                }
            });

            $('.contractAuditorName').each(function () {
                var currentVal = String($(this).val() || '').trim();
                updateAuditorRowVisualStatus($(this));
                $(this).find('option').each(function () {
                    var optVal = String($(this).val() || '').trim();
                    if (optVal === '') {
                        $(this).prop('disabled', false).css('display', '');
                        return;
                    }
                    if (optVal === currentVal) {
                        $(this).prop('disabled', false).css('display', '');
                        return;
                    }
                    if (selectedAuditorIds.indexOf(optVal) !== -1) {
                        $(this).prop('disabled', true).css('display', 'none');
                    } else {
                        $(this).prop('disabled', false).css('display', '');
                    }
                });
            });
        }

        function updateDivisionAlertBanner(resp) {
            var $banner = $('.auditor-division-banner-s1');
            if (!resp || (!resp.has_nace_classes && !resp.has_divisions)) {
                $banner.html('<div class="alert alert-info" style="padding: 10px 16px; margin-bottom: 14px; font-size: 13px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border: 1px solid #bce8f1; background: #f4fbfd;"><div style="display: flex; align-items: center; gap: 10px;"><i class="fa fa-info-circle" style="font-size: 17px; color: #17a2b8;"></i> <span><strong>Note:</strong> No NACE Class assigned to this client in Contract Review yet. Showing all auditors.</span></div></div>').show();
            } else if (resp.has_nace_classes) {
                var classes = resp.client_nace_classes || [];
                var audCount = (resp.auditors) ? resp.auditors.length : 0;
                var classLabel = (classes.length > 1) ? 'Client NACE Classes' : 'Client NACE Class';
                var classesBadge = '<span style="background: #16a34a; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; margin: 0 4px; letter-spacing: 0.3px;">' + classes.join(', ') + '</span>';
                if (audCount === 0) {
                    $banner.html('<div class="alert alert-warning" style="padding: 10px 16px; margin-bottom: 14px; font-size: 13px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border: 1px solid #ffeeba; background: #fffdf5;"><div style="display: flex; align-items: center; gap: 10px;"><i class="fa fa-exclamation-triangle" style="font-size: 17px; color: #ffc107;"></i> <span><strong>No Qualified auditors found</strong> for ' + classLabel + ': ' + classesBadge + '. Please assign this NACE Class in <a href="masterCreator.php" target="_blank" style="text-decoration: underline; font-weight: bold; color: #856404;">Master Creator</a>.</span></div></div>').show();
                } else {
                    $banner.html('<div class="alert alert-success" style="padding: 10px 16px; margin-bottom: 14px; font-size: 13px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: linear-gradient(135deg, #f0fdf4 0%, #eafbf0 100%); border: 1px solid #bbf7d0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">' +
                        '<div style="display: flex; align-items: center; gap: 10px;">' +
                            '<i class="fa fa-check-circle" style="font-size: 18px; color: #16a34a;"></i> ' +
                            '<span style="color: #166534; font-weight: 500;"><strong>' + classLabel + ':</strong> ' + classesBadge + ' &mdash; Showing <strong>' + audCount + '</strong> Qualified auditors.</span>' +
                        '</div>' +
                        '<button type="button" class="btn btn-xs btn-view-qualified-auditors-s1" style="font-weight: 600; padding: 5px 14px; color: #15803d; background: #ffffff; border: 1.5px solid #22c55e; border-radius: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.06); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 12px;" title="Click to view qualified auditors list">' +
                            '<i class="fa fa-eye" style="color: #16a34a;"></i> View Qualified Auditors (' + audCount + ')' +
                        '</button>' +
                    '</div>').show();
                }
            } else {
                var divs = resp.client_divisions || [];
                var audCount = (resp.auditors) ? resp.auditors.length : 0;
                var divLabel = (divs.length > 1) ? 'Client Divisions' : 'Client Division';
                var divsBadge = '<span style="background: #16a34a; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; margin: 0 4px; letter-spacing: 0.3px;">' + divs.join(', ') + '</span>';
                if (audCount === 0) {
                    $banner.html('<div class="alert alert-warning" style="padding: 10px 16px; margin-bottom: 14px; font-size: 13px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border: 1px solid #ffeeba; background: #fffdf5;"><div style="display: flex; align-items: center; gap: 10px;"><i class="fa fa-exclamation-triangle" style="font-size: 17px; color: #ffc107;"></i> <span><strong>No Qualified auditors found</strong> for ' + divLabel + ': ' + divsBadge + '. Please assign this division in <a href="masterCreator.php" target="_blank" style="text-decoration: underline; font-weight: bold; color: #856404;">Master Creator</a>.</span></div></div>').show();
                } else {
                    $banner.html('<div class="alert alert-success" style="padding: 10px 16px; margin-bottom: 14px; font-size: 13px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: linear-gradient(135deg, #f0fdf4 0%, #eafbf0 100%); border: 1px solid #bbf7d0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">' +
                        '<div style="display: flex; align-items: center; gap: 10px;">' +
                            '<i class="fa fa-check-circle" style="font-size: 18px; color: #16a34a;"></i> ' +
                            '<span style="color: #166534; font-weight: 500;"><strong>' + divLabel + ':</strong> ' + divsBadge + ' &mdash; Showing <strong>' + audCount + '</strong> Qualified auditors.</span>' +
                        '</div>' +
                        '<button type="button" class="btn btn-xs btn-view-qualified-auditors-s1" style="font-weight: 600; padding: 5px 14px; color: #15803d; background: #ffffff; border: 1.5px solid #22c55e; border-radius: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.06); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 12px;" title="Click to view qualified auditors list">' +
                            '<i class="fa fa-eye" style="color: #16a34a;"></i> View Qualified Auditors (' + audCount + ')' +
                        '</button>' +
                    '</div>').show();
                }
            }
        }

        $(document).on('click', '.btn-view-qualified-auditors-s1', function (e) {
            e.preventDefault();
            var list = aitfNotificationEligibleAuditors || [];
            var codeText = aitfNotificationHasNaceClasses 
                ? ('Client NACE Class: ' + (aitfNotificationClientNaceClasses || []).join(', '))
                : ('Client Division: ' + (aitfNotificationClientDivisions || []).join(', '));
            
            if (!list || list.length === 0) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Qualified Auditors Found',
                        text: 'No auditors have been assigned to ' + codeText + ' in Master Creator.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert('No qualified auditors found for ' + codeText);
                }
                return;
            }

            var tableHtml = '<div style="text-align: left; max-height: 350px; overflow-y: auto;">' +
                '<p style="font-size: 13px; color: #555; margin-bottom: 10px;">Below are the auditors qualified for <strong>' + codeText + '</strong>:</p>' +
                '<table class="table table-bordered table-striped" style="font-size: 12px; margin-bottom: 0;">' +
                '<thead><tr style="background: #0F2742; color: #fff;"><th>#</th><th>Auditor Name</th><th>Email</th><th>Assigned Classes / Divisions</th></tr></thead>' +
                '<tbody>';
            
            for (var i = 0; i < list.length; i++) {
                var aud = list[i];
                var assignedInfo = aud.assigned_nace_classes_display || aud.assigned_divisions_display || '-';
                tableHtml += '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td style="font-weight: 600; color: #0F2742;">' + (aud.auditor_name || '-') + '</td>' +
                    '<td>' + (aud.auditor_email || '-') + '</td>' +
                    '<td><span class="badge" style="background: #28a745;">' + assignedInfo + '</span></td>' +
                    '</tr>';
            }
            tableHtml += '</tbody></table></div>';

            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({
                    title: '<span style="font-size: 18px; color: #0F2742;"><i class="fa fa-users" style="color: #28a745;"></i> Qualified Auditors (' + list.length + ')</span>',
                    html: tableHtml,
                    width: '650px',
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#0F2742'
                });
            } else {
                alert('Qualified Auditors:\n' + list.map(function(a) { return a.auditor_name + ' (' + a.auditor_email + ')'; }).join('\n'));
            }
        });

        function loadEligibleAuditorsForNotification(customerId, serviceId, callback) {
            $.ajax({
                url: "getEligibleAuditors.php",
                type: "GET",
                dataType: "json",
                data: {
                    customerid: customerId,
                    serviceid: serviceId
                },
                success: function(resp) {
                    aitfNotificationFilterMode = resp ? resp.filter_mode : 'none';
                    aitfNotificationHasNaceClasses = !!(resp && resp.has_nace_classes);
                    aitfNotificationClientNaceClasses = (resp && resp.client_nace_classes) ? resp.client_nace_classes : [];
                    aitfNotificationHasDivisions = !!(resp && resp.has_divisions);
                    aitfNotificationClientDivisions = (resp && resp.client_divisions) ? resp.client_divisions : [];
                    aitfNotificationEligibleAuditors = (resp && resp.auditors) ? resp.auditors : [];
                    if (resp && resp.all_auditors) {
                        aitfNotificationAllAuditors = resp.all_auditors;
                    }
                    updateDivisionAlertBanner(resp);
                    if (typeof callback === 'function') {
                        callback(resp);
                    }
                },
                error: function() {
                    aitfNotificationHasNaceClasses = false;
                    aitfNotificationHasDivisions = false;
                    updateDivisionAlertBanner(null);
                    if (typeof callback === 'function') {
                        callback(null);
                    }
                }
            });
        }

        $customerid = "";
        $(document).on('click', '.createAuditNotification', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $icon = $btn.find('i');
            var origClass = $icon.attr('class') || 'fa fa-plus-circle fa-lg';
            $icon.attr('class', 'fa fa-spinner fa-spin fa-lg');
            $btn.css('pointer-events', 'none');

            function restoreCreateBtn() {
                $icon.attr('class', origClass);
                $btn.css('pointer-events', '');
            }

            $('.audit-noti-service').val($btn.attr('data-service1') || '');
            $('.audit-noti-service1').val($btn.attr('data-service') || '');
            $('.audit-noti-customer').val($btn.attr('data-customer'));
            
            var rawSvcType = $btn.attr("data-type") || $btn.attr("data-svc") || "";
            if (rawSvcType === 'service-type-sai') rawSvcType = 'SA-I';
            else if (rawSvcType === 'service-type-saii') rawSvcType = 'SA-II';
            else if (rawSvcType === 'service-type-rc') rawSvcType = 'RC';
            else if (rawSvcType === 'service-type-ca') rawSvcType = 'CA';
            else if (rawSvcType === 'service-type-sp') rawSvcType = 'SP';
            else if (rawSvcType === 'service-type-ta') rawSvcType = 'TA';
            else if (rawSvcType === 'service-type-ptr') rawSvcType = 'PTR';
            
            $(".audit-service-type").val(rawSvcType);
            $("#audti-service-type").val(rawSvcType);
            $customerid = $btn.attr('data-customer');
            var svcId = $btn.attr('data-service1') || $btn.attr('data-service');

            loadAppointedReviewersForAuditorCheck($customerid, svcId);

            loadEligibleAuditorsForNotification($customerid, svcId, function() {
                $(".removable_auditor_info").remove();
                $(".contractAuditorName").html(renderNotificationAuditorOptions(''));
                syncAuditorSelectOptions();
            });

            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customid: $btn.attr('data-customer'),
                    svcID: $btn.attr('data-service'),
                    svcids: $btn.attr('data-service1'),
                },
                success: function (data) {
                    restoreCreateBtn();
                    if (!data || !data[0]) {
                        alert("Could not load client info.");
                        return;
                    }
                    try {
                        $('.auditForOrg').val((data[0] && data[0]['customer_name']) || '');
                        $('.auditLocation').val((data[0] && data[0]['address']) || '');
                        $('.auditTelephone').val((data[0] && data[0]['contact']) || '');
                        $('.auditEmail').val((data[0] && data[0]['email_id']) || '');
                        $('.auditReferance').val((data[3] && data[3]['aggrement_no']) || (data[0] && data[0]['unique_id']) || '');
                        $('.auditContactPerson').val((data[0] && data[0]['contact_person']) || '');
                        $('.auditStandard').val((data[1] && data[1]['certification']) || '');
                        $('input[type="text"]').trigger('change');
                        $('.auditLocation').trigger('change');
                        $('#auditNotification').modal('show');
                    } catch (e) {
                        console.error("Error setting create Stage I fields:", e);
                        $('#auditNotification').modal('show');
                    }
                },
                error: function (data, status, err) {
                    restoreCreateBtn();
                    alert("Error loading client info: " + (status || err));
                }
            });
        });

        $(document).on('focus mousedown', '.contractAuditorName', function () {
            syncAuditorSelectOptions();
        });

        $(document).on('change', ".contractAuditorName", function () {
            var $select = $(this);
            var auditorId = $select.val();
            var auditorName = $select.find('option:selected').text().trim();
            if (!auditorId) {
                syncAuditorSelectOptions();
                return;
            }

            var isDuplicate = false;
            $('.contractAuditorName').not($select).each(function () {
                var otherVal = String($(this).val() || '').trim();
                if (otherVal !== '' && otherVal === String(auditorId).trim()) {
                    isDuplicate = true;
                    return false;
                }
            });
            if (isDuplicate) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Auditor Already Selected',
                        text: (auditorName ? auditorName : 'This auditor') + ' is already selected in this audit team. Please choose a different auditor.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert((auditorName ? auditorName : 'This auditor') + ' is already selected in this audit team. Please choose a different auditor.');
                }
                $select.val("");
                syncAuditorSelectOptions();
                return;
            }

            var conflict = checkAuditorConflictWithReviewers(auditorId, auditorName);
            if (conflict) {
                showAuditorReviewerConflictDialogue(auditorName, conflict);
                $select.val("");
                syncAuditorSelectOptions();
                return;
            }

            // Existing check for previous 2 services as lead auditor
            $.ajax({
                url: "getNotificationInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customerid: $customerid,
                    auditorid: auditorId,
                },
                success: function (data) {
                    if (data == '2') {
                        $("#auditor-details").show();
                        $('.confirm-ok').click(function () {
                            $('#auditor-details').hide();
                        });
                        $select.val("");
                        syncAuditorSelectOptions();
                    } else {
                        syncAuditorSelectOptions();
                    }
                },
                error: function (data, status, err) {
                    syncAuditorSelectOptions();
                }
            });
        });


        $(document).on('click', '.editAuditNotification', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var notifId = $btn.attr('data-notification');
            var svcId = $btn.attr('data-service1') || $btn.attr('data-service');
            var custId = $btn.attr('data-customer');

            if (!notifId || !svcId) {
                alert('Notification details missing.');
                return;
            }

            var $icon = $btn.find('i');
            var origClass = $icon.attr('class') || 'fa fa-pencil-square fa-lg';
            $icon.attr('class', 'fa fa-spinner fa-spin fa-lg');
            $btn.css('pointer-events', 'none');

            var btnRestored = false;
            function restoreBtn() {
                if (!btnRestored) {
                    btnRestored = true;
                    $icon.attr('class', origClass);
                    $btn.css('pointer-events', '');
                }
            }

            $('.audit-noti-id-edit').val(notifId);
            $('.audit-noti-service').val($btn.attr('data-service1') || $btn.attr('data-service') || '');
            $('.audit-noti-service1').val($btn.attr('data-service') || '');
            $('.audit-noti-customer').val(custId);
            
            var rawSvcType = $btn.attr("data-type") || $btn.attr("data-svc") || "";
            if (rawSvcType === 'service-type-sai') rawSvcType = 'SA-I';
            else if (rawSvcType === 'service-type-saii') rawSvcType = 'SA-II';
            else if (rawSvcType === 'service-type-rc') rawSvcType = 'RC';
            else if (rawSvcType === 'service-type-ca') rawSvcType = 'CA';
            else if (rawSvcType === 'service-type-sp') rawSvcType = 'SP';
            else if (rawSvcType === 'service-type-ta') rawSvcType = 'TA';
            else if (rawSvcType === 'service-type-ptr') rawSvcType = 'PTR';
            
            $(".audit-service-type").val(rawSvcType);
            $("#audti-service-type").val(rawSvcType);
            $customerid = custId;

            // Fire conflict check in background
            loadAppointedReviewersForAuditorCheck($customerid, svcId);

            // Fire eligible auditors in parallel
            loadEligibleAuditorsForNotification($customerid, svcId, function() {
                syncAuditorSelectOptions();
            });

            // Fire contract details simultaneously
            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    notificationId: notifId,
                    serviceid: svcId
                },
                success: function (data) {
                    restoreBtn();
                    if (!data || !data[0]) {
                        alert("Could not load notification details.");
                        return;
                    }

                    try {
                        var main = data[0] || {};
                        var rows = data[1] || [];

                        $(".auditSubject").val(main['audit_subject'] || '');
                        $('.auditForOrg').val(main['for_org'] || '');

                        var $intimation_date = formatStage1DateValue(main['intimation_date']);
                        $('.intimationDate').val($intimation_date);

                        $('.auditLocation').val(main['location'] || '');
                        $('.auditReferance').val(main['audit_ref_no'] || '');
                        // Non-blocking fetch to ensure latest authoritative agreement number
                        $.ajax({
                            url: "getClientInfo.php",
                            type: "GET",
                            dataType: "json",
                            data: {
                                customer_id: custId,
                                service_id: svcId,
                            },
                            success: function (clientData) {
                                if (clientData && clientData[0] && clientData[0]['unique_id'] != null && clientData[0]['unique_id'] !== "") {
                                    $('.auditReferance').val(clientData[0]['unique_id']);
                                    $('.auditReferance').trigger('change');
                                }
                            }
                        });
                        $('.auditStandard').val(main['audit_standard'] || '');
                        $('.auditContactPerson').val(main['audit_contact_person'] || '');

                        var rawAuditDates = main['audit_dates'];
                        var $audit_dates = formatStage1DateList(rawAuditDates);
                        if ($audit_dates === "") {
                            $audit_dates = $intimation_date;
                        }
                        $('.auditDates').val($audit_dates);

                        $('.auditDesignation').val(main['audit_designation'] || '');
                        $('.auditTelephone').val(main['audit_telephone'] || '');
                        $('.auditEmail').val(main['audit_email'] || '');
                        if (main["special_audit"]) {
                            $('.stage-i[value = "' + main["special_audit"] + '"]').prop('checked', true).trigger("change");
                        }

                        // Build multiple Audit date(s) inputs from the final resolved value
                        $(".newAuditDates").empty();
                        var auditDatesSource = $audit_dates && $audit_dates !== "" ? $audit_dates : (rawAuditDates || "");
                        if (auditDatesSource && typeof auditDatesSource === 'string') {
                            var auditDate = auditDatesSource.split(',');
                            var auditDateLen = auditDate.length;
                            var max = (Math.max(auditDateLen));
                            for (var key = 0; key < max; key++) {
                                var no = key <= auditDateLen && auditDate[key] != null && String(auditDate[key]).trim() !== "" ? formatStage1DateValue(auditDate[key]) : "";
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

                        $(".removable_auditor_info").remove();
                        $rowCounter = 0;
                        if (rows && rows.length > 0) {
                            for (var key = 0; key < rows.length; key++) {
                                $rowCounter++;
                                var audId = rows[key]['auditor_id'] || '';
                                var audName = rows[key]['auditor_name'] || '';
                                if (key == 0) {
                                    $(".contractAuditorName").html(renderNotificationAuditorOptions(audId, audName));
                                    $(".contractAuditorName").val(audId);
                                    $(".auditorCategory").val(rows[0]['auditor_category'] || '');
                                    $(".auditorWork").val(rows[0]['audit_wokrdays'] || '');
                                    $(".auditorAcceptance").val(rows[0]['auditor_acceptance'] || '');
                                } else {
                                    var audOptions = renderNotificationAllAuditorOptions(audId, audName);
                                    $('.editedNotification').append('<tr class="audit_subsection removable_auditor_info" id="cb_subsection_' + key + '" style="display: ">\n\
                                                                <td width="36%" style="padding: 6px; vertical-align: middle;">\n\
                                                                <div class="table-edit-control">\n\
                                                                  <select data-row-id="" class="contractAuditorName form-control" id="contractAuditorName" name="contractAuditorName" style="width: 100%;">\n\
                                                                ' + audOptions + '\n\
                                                                  </select>\n\
                                                                </div>\n\
                                                                </td>\n\
                                                                <td width="24%" style="padding: 6px; vertical-align: middle;">\n\
                                        <div class="table-edit-control">\n\
                                            <select class="auditorCategory form-control" id="auditorCategory" name="auditorCategory" required style="width: 100%;">\n\
                                                <option value="">Choose Category</option>\n\
                                                <?php foreach ($category as $name) { ?>\n\
                                                    <option value="<?php echo $name['categories_name'] ?>"><?php echo $name['categories_name'] ?></option>\n\
                                                <?php } ?>\n\
                                            </select>\n\
                                        </div>\n\
                                    </td>\n\
                                                                <td width="14%" style="padding: 6px; vertical-align: middle;">\n\
                                                                    <div class="table-edit-control">\n\
                                                                        <input type="text" min="1" data-row-id="" class="auditorWork form-control" id="auditorWork" name="auditorWorkii" value="' + (rows[key]['audit_wokrdays'] || '') + '" placeholder="" style="width: 100%;">\n\
                                                                    </div>\n\
                                                                </td>\n\
                                                                <td width="16%" style="padding: 6px; vertical-align: middle;">\n\
                                                                    <div class="table-edit-control">\n\
                                                                        <select class="auditorAcceptance form-control" id="auditorAcceptance" name="auditorAcceptance" style="width: 100%;">\n\
                                                                            <option value="' + (rows[key]['auditor_acceptance'] || '') + '">' + (rows[key]['auditor_acceptance'] || 'Select') + '</option><option value="">Select</option><option value="Yes">Yes</option><option value="No">No</option></select></div>\n\
                                                                </td>\n\
                                                                <td width="10%" style="padding: 6px; text-align: center; vertical-align: middle;">\n\
                                                                    <button type="button" class="btn btn-sm btn-danger removeRowButton" style="padding: 3px 8px; border-radius: 4px;">Remove</button>\n\
                                                                </td>\n\
                                                                                        </tr> ');
                                    const newAuditorCategorySelect = $('.editedNotification').find('.auditorCategory').last();
                                    newAuditorCategorySelect.val(rows[key]['auditor_category'] || '');
                                }
                            }
                        } else {
                            $(".contractAuditorName").html(renderNotificationAuditorOptions(''));
                        }
                        $rowCB = $rowCounter > 0 ? $rowCounter : 1;
                        syncAuditorSelectOptions();

                        if (main["auditor_check_last_two"] == "YES") {
                            $('.tllasty').prop("checked", true);
                        } else if (main["auditor_check_last_two"] == "NO") {
                            $('.tllastn').prop("checked", true);
                        }
                        $('.tllast').val(main["auditor_check_last_two"] || '');
                        $('.tllastyc').val(main["auditor_check_comm"] || '');
                        $('input[type="text"]').trigger('change');
                        $('.auditLocation').trigger('change');
                        $('.appointedby').val(main['appointed_by'] || '');
                        $('.mcldesignation').val(main['mcl_designation'] || '');

                        $('#auditNotification').modal('show');
                    } catch (e) {
                        console.error('Error setting edit Stage I notification fields:', e);
                        $('#auditNotification').modal('show');
                    }
                },
                error: function (data, status, err) {
                    restoreBtn();
                    alert("Error loading notification details: " + (status || err));
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

            // Temporarily disabled: allow any auditor to be saved
            /*
            if (!hasAtLeastOneQualifiedAuditor()) {
                e.preventDefault();
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Qualified Auditor Required',
                        text: 'Atleast one qualifed auditor needs to be in the team',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert('Atleast one qualifed auditor needs to be in the team');
                }
                return;
            }
            */

        });
        $(document.body).on('click', '.addAuditLink', function (e) {
            if (e) e.preventDefault();

            var autitorsSelect = '<select data-row-id="" class="contractAuditorName form-control" id="contractAuditorName" name="contractAuditorName" style="width: 100%;">\n\
                                    ' + renderNotificationAllAuditorOptions('') + '\n\
                                </select>';

            $count = $rowCB + 1;
            var table = document.getElementById("notification_details");
            var row = table.insertRow($count);

            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            var cell3 = row.insertCell(2);
            var cell4 = row.insertCell(3);
            var cell5 = row.insertCell(4);

            cell1.style.padding = "6px";
            cell1.style.verticalAlign = "middle";
            cell1.style.width = "36%";

            cell2.style.padding = "6px";
            cell2.style.verticalAlign = "middle";
            cell2.style.width = "24%";

            cell3.style.padding = "6px";
            cell3.style.verticalAlign = "middle";
            cell3.style.width = "14%";

            cell4.style.padding = "6px";
            cell4.style.verticalAlign = "middle";
            cell4.style.width = "16%";

            cell5.style.padding = "6px";
            cell5.style.verticalAlign = "middle";
            cell5.style.textAlign = "center";
            cell5.style.width = "10%";

            cell1.innerHTML = '<div class="table-edit-control">' + autitorsSelect + '</div>';
            cell2.innerHTML = '<div class="table-edit-control"><select class="auditorCategory form-control" id="auditorCategory" name="auditorCategory" required style="width: 100%;"><option value="">Choose Category</option><?php foreach ($category as $name) { ?><option value="<?php echo $name['categories_name'] ?>" <?php if ($name['categories_name'] == $selectedCategoryId) {
                        echo 'selected';
                    } ?>><?php echo $name['categories_name'] ?></option><?php } ?></select></div>';
            cell3.innerHTML = '<div class="table-edit-control"><input type="text" min="1" data-row-id="" class="auditorWork form-control" id="auditorWork" name="auditorWork" placeholder="" style="width: 100%;" /></div>';
            cell4.innerHTML = '<div class="table-edit-control"><select class="auditorAcceptance form-control" id="auditorAcceptance" name="auditorAcceptance" required style="width: 100%;"><option value="">Select</option><option value="Yes">Yes</option><option value="No">No</option></select></div>';

            row.className = "audit_subsection";
            let removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.textContent = 'Remove';
            removeButton.classList.add('btn', 'btn-sm', 'btn-danger', 'removeRowButton');
            removeButton.style.padding = '3px 8px';
            removeButton.style.borderRadius = '4px';

            cell5.appendChild(removeButton);
            $rowCB = $count;
            syncAuditorSelectOptions();
        });
        $(document).on('click', '.removeRowButton', function () {
            $count = $rowCB - 1;
            $(this).closest('tr').remove();
            $rowCB = $count;
            syncAuditorSelectOptions();
        });

        $('.addAuditNoti').click(function (e) {
            if (validateStage1()) {
                e.preventDefault();
                return;
            }

            // Temporarily disabled: allow any auditor to be saved
            /*
            if (!hasAtLeastOneQualifiedAuditor()) {
                e.preventDefault();
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Qualified Auditor Required',
                        text: 'Atleast one qualifed auditor needs to be in the team',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert('Atleast one qualifed auditor needs to be in the team');
                }
                return;
            }
            */

            var seenAuditors = {};
            var hasDuplicateAuditor = false;
            var duplicateAuditorName = "";
            $('.contractAuditorName').each(function () {
                var val = String($(this).val() || '').trim();
                if (val !== '') {
                    if (seenAuditors[val]) {
                        hasDuplicateAuditor = true;
                        duplicateAuditorName = $(this).find('option:selected').text().trim();
                        return false;
                    }
                    seenAuditors[val] = true;
                }
            });
            if (hasDuplicateAuditor) {
                e.preventDefault();
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Duplicate Auditor',
                        text: (duplicateAuditorName ? duplicateAuditorName : 'An auditor') + ' is selected multiple times. Please assign unique auditors.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert('Duplicate auditor selected in audit team.');
                }
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

        $('.addAuditNotiFdf').click(function (e) {
            // Temporarily disabled: allow any auditor to be saved
            /*
            if (!hasAtLeastOneQualifiedAuditor()) {
                if (e) e.preventDefault();
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Qualified Auditor Required',
                        text: 'Atleast one qualifed auditor needs to be in the team',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert('Atleast one qualifed auditor needs to be in the team');
                }
                return false;
            }
            */

            var seenAuditors = {};
            var hasDuplicateAuditor = false;
            var duplicateAuditorName = "";
            $('.contractAuditorName').each(function () {
                var val = String($(this).val() || '').trim();
                if (val !== '') {
                    if (seenAuditors[val]) {
                        hasDuplicateAuditor = true;
                        duplicateAuditorName = $(this).find('option:selected').text().trim();
                        return false;
                    }
                    seenAuditors[val] = true;
                }
            });
            if (hasDuplicateAuditor) {
                if (e) e.preventDefault();
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Duplicate Auditor',
                        text: (duplicateAuditorName ? duplicateAuditorName : 'An auditor') + ' is selected multiple times. Please assign unique auditors.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0F2742'
                    });
                } else {
                    alert('Duplicate auditor selected in audit team.');
                }
                return false;
            }

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
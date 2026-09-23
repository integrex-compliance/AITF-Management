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

$auditorOptionsHtml = '<option value="">Choose Auditors</option>';
$categoryOptionsHtml = '<option value="">Choose Category</option>';

foreach ($auditors as $name) {
    $audEmail = trim($name['auditor_email'] ?? '');
    $audDisplay = $name['auditor_name'] . ($audEmail !== '' ? ' (' . $audEmail . ')' : '');
    $allAuditorsid .= $name['auditors_id'] . ",";
    $allAuditors .= $audDisplay . ",";
    $allAuditorsContact .= $name['auditor_name'] . " - " . $name['auditor_number'] . ",";
    $allContact .= $name['auditor_number'] . ",";
    $auditorOptionsHtml .= '<option value="' . htmlspecialchars($name['auditors_id'], ENT_QUOTES, 'UTF-8') . '" data-email="' . htmlspecialchars($audEmail, ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars($audDisplay, ENT_QUOTES, 'UTF-8') . '</option>';
}

foreach ($category as $name) {
    $categoryName = $name['categories_name'];
    $categoryOptionsHtml .= '<option value="' . htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') . '</option>';
}
?>

<script src="js/date-chronology-validation.js?v=2"></script>
<script src="js/form-fancy-control.js"></script>
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

<div class="modal fade" id="auditNotificationII" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
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
                                <input type="text" class="auditiiSubject" id="auditiiSubject" name="auditiiSubject"
                                    placeholder="Subject" value="  Intimation for acceptance to the below specified audit, auditor assignment and declaration for
                                       impartiality and confidentiality by AITF audit team member/s." required="">
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>For the organization</label>
                                <input type="text" class="auditiiForOrg" id="auditiiForOrg" name="auditiiForOrg"
                                    placeholder="For the organization" required>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Date of Intimation</label>
                                        <input type="text" class="intimationDateii" id="intimationDateii"
                                            name="intimationDateii" placeholder="Date of Intimation" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 ">
                                        <div id="intimationDateiiError" style="display: none">
                                            <small> <span id="intimationDateiiErrorMsg"
                                                    style="color: #c9513c; font-style: italic;"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Site(s)</label>
                                <textarea rows="4" class="auditiiLocation" id="auditiiLocation" name="auditiiLocation"
                                    placeholder="Site(s):" required></textarea>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <div class="row ">
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>Agreement No:</label>
                                        <input type="text" readonly class="auditiiReferance" id="auditiiReferance"
                                            name="auditiiReferance" placeholder="Agreement No:">
                                    </div>
                                    <div class="col-sm-12 form-group float-label-control">
                                        <label>For the standard</label>
                                        <input type="text" readonly class="auditiiStandard" id="auditiiStandard"
                                            name="auditiiStandard" placeholder="For the standard">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Contact Person Name:</label>
                                <input type="text" class="auditiiContactPerson" id="auditiiContactPerson"
                                    name="auditiiContactPerson" placeholder="Contact Person Name:" required>
                            </div>
                            <div class="col-sm-6 ">
                                <div class="row">
                                    <div class="col-sm-10 form-group float-label-control">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label>Audit date(s):</label>
                                                <input type="text" class="auditiiDates" name="auditiiDates"
                                                    placeholder="Audit date(s):" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12 ">
                                                <div id="auditiiDatesError" style="display: none">
                                                    <small><span id="auditiiDatesErrorMsg"
                                                            style="color: #c9513c; font-style: italic;"></span></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-1 form-group float-label-control">
                                        <a class="newAuditiiDate" style="" title="Add Multiple AuditDates"><i
                                                class="fa fa-plus"></i></a>
                                    </div>
                                </div>
                                <div class="newAuditiiDates"></div>
                            </div>
                            <!--                            <div class="col-sm-6 form-group float-label-control">
                                                                                        <label>Audit date(s):</label>
                                                                                        <input type="text" class="auditiiDates" id="auditiiDates" name="auditiiDates" placeholder="Audit date(s):" required>
                                                                                    </div>-->
                        </div>
                        <div class="row ">
                            <div class="col-sm-4 form-group float-label-control">
                                <div class="row form-group float-label-control">
                                    <label>Designation:</label>
                                    <input type="text" class="auditiiDesignation" id="auditiiDesignation"
                                        name="auditiiDesignation" placeholder="Designation:" required>
                                </div>
                                <div class="row form-group float-label-control">
                                    <label>Telephone / Mobile:</label>
                                    <input type="text" class="auditiiTelephone" id="auditiiTelephone"
                                        name="auditiiTelephone" placeholder="Telephone / Mobile:" required>
                                </div>
                                <div class="row form-group float-label-control">
                                    <label>E-mail:</label>
                                    <input type="text" class="auditiiEmail" id="auditiiEmail" name="auditiiEmail"
                                        placeholder="E-mail:" required>
                                </div>
                            </div>
                            <div class="col-sm-8 form-group ">
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group ">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii"
                                            value="Stage-i">Stage-I Audit
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii"
                                            value="Stage-ii" checked>Stage II Audit
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii"
                                            value="Surveillance">Surveillance
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii"
                                            value="Re-Certification Audit">Re-Certification Audit
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii"
                                            value="Follow-up Audit">Follow-up Audit
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <input type="checkbox" class="stage-ii" id="stage-ii" name="stage-ii"
                                            value="Special Audit">Special Audit ____
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="auditii-noti-service" id="auditii-noti-service"
                            name="auditii-noti-service" value="">
                        <input type="hidden" class="auditii-noti-customer" id="auditii-noti-customer"
                            name="auditii-noti-customer" value="">
                        <input type="hidden" class="audtii-noti-id" id="audtii-noti-id" name="audtii-noti-id" value="">
                    </div>
                    <div class="row" style="">
                        <div class="col-xs-12  text-center btn  qms_complete_btn formNotification">
                            <span><i class="fa  ">Declaration for impartiality and confidential by Aitf-MCL team audit
                                    team member/s:</i></span>
                        </div>
                    </div>
                    <div class="formNotificationDiv" style="display: none;">
                        <div class="auditor-division-banner-s2" style="display:none; margin: 10px 0;"></div>
                        <div class="row">
                            <div class="col-sm-12 table-responsive">
                                <table class="table table-bordered table-hover table-sortable service_details"
                                    id="notification_detailsii" style="width: 100%">
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
                                                <a class="btn btn-xs btn-primary addAuditLinkii" data-row-id=""
                                                    data-row-count="" title="Add Auditor" style="padding: 3px 8px; border-radius: 4px; font-weight: 600;">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="editTableDiv">
                                        <tr class="audit_subsectionii previousCBData" id="cb_subsection_0"
                                            style="display: ">
                                            <td width="36%" style="padding: 6px; vertical-align: middle;">
                                                <div class="table-edit-control">
                                                    <!--<input type="text" data-row-id="" class="contractAuditorNameii" id="contractAuditorNameii" name="contractAuditorNameii" value="" placeholder=""/>-->
                                                    <select class="contractAuditorNameii form-control"
                                                        id="contractAuditorNameii" name="contractAuditorNameii"
                                                        required style="width: 100%;">
                                                        <?php echo $auditorOptionsHtml; ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td width="24%" style="padding: 6px; vertical-align: middle;">
                                                <div class="table-edit-control">
                                                    <select class="auditorCategoryii form-control"
                                                        id="auditorCategoryii" name="auditorCategoryii" required style="width: 100%;">
                                                        <option value="">Choose Category</option>
                                                        <?php foreach ($category as $name) { ?>
                                                            <option value="<?php echo $name['categories_name'] ?>">
                                                                <?php echo $name['categories_name'] ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td width="14%" style="padding: 6px; vertical-align: middle;">
                                                <div class="table-edit-control">
                                                    <input type="text" min="1" data-row-id="" class="auditorWorkii form-control"
                                                        id="auditorWorkii" name="depositWorkii" value="" placeholder=""
                                                        required style="width: 100%;">
                                                </div>
                                            </td>
                                            <td width="16%" style="padding: 6px; vertical-align: middle;">
                                                <div class="table-edit-control">
                                                    <select class="auditorAcceptanceii form-control"
                                                        id="auditorAcceptanceii" name="auditorAcceptanceii" required style="width: 100%;">
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
                                                    <input type="checkbox" class="tllasty1" id="tllasty" name="tllasty"
                                                        value="YES">YES
                                                </div>
                                                <div class="col-sm-6 form-group">
                                                    <input type="checkbox" class="tllastn2" id="tllastn" name="tllastn"
                                                        value="No">NO
                                                </div>
                                            </div>
                                            <div class="col-sm-4 form-group float-label-control">
                                                <div class="row form-group float-label-control">
                                                    <label></label>
                                                    <input type="text" class="tllastyc1" id="tllastyc" name="tllastyc"
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
                                <div id="teamLeader2Error" style="display: none">
                                    <small><span id="teamLeader2ErrorMsg"
                                            style="color: #c9513c; font-style: italic;"></span></small>
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
                    <button type="submit" name="addAuditiiNotiii"
                        class="btn btn-xs btn-info addAuditiiNotiii">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    var auditIIDefaultAuditorOptionsHtml = <?php echo json_encode($auditorOptionsHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    var auditIIAuditorOptionsHtml = auditIIDefaultAuditorOptionsHtml;
    var auditIICategoryOptionsHtml = <?php echo json_encode($categoryOptionsHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    var aitfNotificationEligibleAuditorsII = [];
    var aitfNotificationAllAuditorsII = <?php echo json_encode($auditors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    var aitfNotificationClientNaceClassesII = [];
    var aitfNotificationHasNaceClassesII = false;
    var aitfNotificationClientDivisionsII = [];
    var aitfNotificationHasDivisionsII = false;

    function isAuditorIdQualifiedII(aid) {
        if (!aid) return false;
        aid = String(aid).trim();
        if (!aitfNotificationHasNaceClassesII && !aitfNotificationHasDivisionsII) {
            return true;
        }
        var eligible = aitfNotificationEligibleAuditorsII || [];
        for (var i = 0; i < eligible.length; i++) {
            if (String(eligible[i].auditors_id).trim() === aid) {
                return true;
            }
        }
        return false;
    }

    function hasAtLeastOneQualifiedAuditorII() {
        // Temporarily bypassed: allow any auditor to be assigned
        return true;
    }

    function updateAuditorRowVisualStatusII($select) {
        var val = String($select.val() || '').trim();
        var $row = $select.closest('tr');
        var $parent = $select.closest('.table-edit-control');
        $parent.find('.auditor-qualified-badge-ii').remove();

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

        if (isAuditorIdQualifiedII(val)) {
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
            $select.after('<div class="auditor-qualified-badge-ii" style="margin-top: 4px;"><span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; background: #e8f5e9; color: #1e7e34; border: 1px solid #c3e6cb;"><i class="fa fa-check-circle" style="color: #28a745;"></i> Code Qualified</span></div>');
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

    function renderNotificationAuditorOptionsII(selectedAuditorId, fallbackName) {
        var html = '<option value="">Choose Auditors</option>';
        var list = aitfNotificationAllAuditorsII;
        var foundSelected = false;

        if (list && list.length > 0) {
            for (var i = 0; i < list.length; i++) {
                var a = list[i];
                var aid = String(a.auditors_id);
                var isSel = (selectedAuditorId && String(selectedAuditorId) === aid);
                if (isSel) foundSelected = true;
                var isQual = isAuditorIdQualifiedII(aid);
                var qualBadge = isQual ? ' ✓ [Qualified]' : '';
                var optStyle = isQual ? 'font-weight: bold; color: #155724;' : '';
                var optTitle = a.auditor_name + (a.auditor_email ? ' (' + a.auditor_email + ')' : '');
                html += '<option value="' + aid + '"' + (isSel ? ' selected' : '') + ' data-email="' + (a.auditor_email || '') + '" title="' + optTitle + '" data-qualified="' + (isQual ? '1' : '0') + '" style="' + optStyle + '">' + a.auditor_name + qualBadge + '</option>';
            }
        }

        if (selectedAuditorId && !foundSelected && fallbackName) {
            var isQualFb = isAuditorIdQualifiedII(selectedAuditorId);
            var qualBadgeFb = isQualFb ? ' ✓ [Qualified]' : '';
            html += '<option value="' + selectedAuditorId + '" selected data-qualified="' + (isQualFb ? '1' : '0') + '">' + fallbackName + qualBadgeFb + '</option>';
        }

        return html;
    }

    function renderNotificationAllAuditorOptionsII(selectedAuditorId, fallbackName) {
        return renderNotificationAuditorOptionsII(selectedAuditorId, fallbackName);
    }

    function syncAuditorSelectOptionsII() {
        var selectedAuditorIds = [];
        $('.contractAuditorNameii').each(function () {
            var val = String($(this).val() || '').trim();
            if (val !== '') {
                selectedAuditorIds.push(val);
            }
        });

        $('.contractAuditorNameii').each(function () {
            var currentVal = String($(this).val() || '').trim();
            updateAuditorRowVisualStatusII($(this));
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

    function updateDivisionAlertBannerII(resp) {
        var $banner = $('.auditor-division-banner-s2');
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
                    '<button type="button" class="btn btn-xs btn-view-qualified-auditors-s2" style="font-weight: 600; padding: 5px 14px; color: #15803d; background: #ffffff; border: 1.5px solid #22c55e; border-radius: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.06); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 12px;" title="Click to view qualified auditors list">' +
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
                    '<button type="button" class="btn btn-xs btn-view-qualified-auditors-s2" style="font-weight: 600; padding: 5px 14px; color: #15803d; background: #ffffff; border: 1.5px solid #22c55e; border-radius: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.06); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 12px;" title="Click to view qualified auditors list">' +
                        '<i class="fa fa-eye" style="color: #16a34a;"></i> View Qualified Auditors (' + audCount + ')' +
                    '</button>' +
                '</div>').show();
            }
        }
    }

    $(document).on('click', '.btn-view-qualified-auditors-s2', function (e) {
        e.preventDefault();
        var list = aitfNotificationEligibleAuditorsII || [];
        var codeText = aitfNotificationHasNaceClassesII 
            ? ('Client NACE Class: ' + (aitfNotificationClientNaceClassesII || []).join(', '))
            : ('Client Division: ' + (aitfNotificationClientDivisionsII || []).join(', '));
        
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

    function loadEligibleAuditorsForStageII(customerId, serviceId, callback) {
        $.ajax({
            url: "getEligibleAuditors.php",
            type: "GET",
            dataType: "json",
            data: {
                customerid: customerId,
                serviceid: serviceId
            },
            success: function(resp) {
                aitfNotificationHasNaceClassesII = !!(resp && resp.has_nace_classes);
                aitfNotificationClientNaceClassesII = (resp && resp.client_nace_classes) ? resp.client_nace_classes : [];
                aitfNotificationHasDivisionsII = !!(resp && resp.has_divisions);
                aitfNotificationClientDivisionsII = (resp && resp.client_divisions) ? resp.client_divisions : [];
                aitfNotificationEligibleAuditorsII = (resp && resp.auditors) ? resp.auditors : [];
                if (resp && resp.all_auditors) {
                    aitfNotificationAllAuditorsII = resp.all_auditors;
                }
                auditIIAuditorOptionsHtml = renderNotificationAuditorOptionsII('');
                updateDivisionAlertBannerII(resp);
                if (typeof callback === 'function') {
                    callback(resp);
                }
            },
            error: function() {
                auditIIAuditorOptionsHtml = auditIIDefaultAuditorOptionsHtml;
                aitfNotificationHasNaceClassesII = false;
                aitfNotificationHasDivisionsII = false;
                updateDivisionAlertBannerII(null);
                if (typeof callback === 'function') {
                    callback(null);
                }
            }
        });
    }

    function buildAuditIIAuditorRow(auditor) {
        auditor = auditor || {};

        var $row = $('<tr class="audit_subsectionii previousCBData"></tr>');
        var optHtml = auditor.isSecondOrLater
            ? renderNotificationAllAuditorOptionsII(auditor.auditor_id, auditor.auditor_name)
            : renderNotificationAuditorOptionsII(auditor.auditor_id, auditor.auditor_name);

        var $auditorSelect = $('<select class="contractAuditorNameii form-control" name="contractAuditorNameii" required style="width: 100%; min-width: 260px;"></select>')
            .html(optHtml);
        var $categorySelect = $('<select class="auditorCategoryii form-control" name="auditorCategoryii" required></select>')
            .html(auditIICategoryOptionsHtml);
        var $workDays = $('<input type="text" min="1" class="auditorWorkii form-control" name="auditorWorkii" required>');
        var $acceptance = $('<select class="auditorAcceptanceii form-control" name="auditorAcceptanceii" required>'
            + '<option value="">Select</option><option value="Yes">Yes</option><option value="No">No</option></select>');

        if (auditor.auditor_id) {
            $auditorSelect.val(auditor.auditor_id);
        }
        $categorySelect.val(auditor.auditor_category || '');
        $workDays.val(auditor.audit_wokrdays || '');
        $acceptance.val(auditor.auditor_acceptance || '');

        $row.append($('<td width="36%" style="padding: 6px; vertical-align: middle;"><div class="table-edit-control"></div></td>')
            .find('.table-edit-control').append($auditorSelect).end());
        $row.append($('<td width="24%" style="padding: 6px; vertical-align: middle;"><div class="table-edit-control"></div></td>')
            .find('.table-edit-control').append($categorySelect).end());
        $row.append($('<td width="14%" style="padding: 6px; vertical-align: middle;"><div class="table-edit-control"></div></td>')
            .find('.table-edit-control').append($workDays).end());
        $row.append($('<td width="16%" style="padding: 6px; vertical-align: middle;"><div class="table-edit-control"></div></td>')
            .find('.table-edit-control').append($acceptance).end());

        if (auditor.removable) {
            $row.append('<td width="10%" style="padding: 6px; text-align: center; vertical-align: middle;"><button type="button" class="btn btn-sm btn-danger removeRowButton" style="padding: 3px 8px; border-radius: 4px;">Remove</button></td>');
        } else {
            $row.append('<td width="10%" style="padding: 6px; text-align: center; vertical-align: middle; color: #94a3b8;">&mdash;</td>');
        }

        return $row;
    }

    function resetAuditIIAuditorRows() {
        $('.editTableDiv').empty().append(buildAuditIIAuditorRow({ removable: false, isSecondOrLater: false }));
        $rowCounter = 1;
        $rowCB = 1;
        syncAuditorSelectOptionsII();
    }

    var aitfAppointedReviewersII = [];
    var aitfAppointedReviewerAuditorIdsII = [];

    function loadAppointedReviewersForAuditorCheckII(customerId, serviceId) {
        aitfAppointedReviewersII = [];
        aitfAppointedReviewerAuditorIdsII = [];
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
                    aitfAppointedReviewersII = resp.reviewers || [];
                    aitfAppointedReviewerAuditorIdsII = resp.auditor_ids || [];
                }
            }
        });
    }

    function checkAuditorConflictWithReviewersII(auditorId, auditorName) {
        if (!auditorId && !auditorName) return false;

        if (auditorId && aitfAppointedReviewerAuditorIdsII.indexOf(parseInt(auditorId, 10)) !== -1) {
            return auditorName || 'This auditor';
        }

        if (auditorName) {
            var normAud = auditorName.toLowerCase().replace(/[^a-z0-9]/g, '');
            if (normAud) {
                for (var i = 0; i < aitfAppointedReviewersII.length; i++) {
                    var normRev = aitfAppointedReviewersII[i].toLowerCase().replace(/[^a-z0-9]/g, '');
                    if (normAud === normRev || (normAud.length >= 5 && normRev.length >= 5 && (normAud.indexOf(normRev) !== -1 || normRev.indexOf(normAud) !== -1))) {
                        return aitfAppointedReviewersII[i];
                    }
                }
            }
        }
        return false;
    }

    function showAuditorReviewerConflictDialogueII(auditorName, conflictingReviewer) {
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

    $(document).ready(function () {
        $('#auditNotificationII').on('shown.bs.modal', function () {
            var cidII = $('.auditii-noti-customer').val() || $('.customerId').val();
            var sidII = $('.auditii-noti-service').val() || $('.serviceId').val();
            loadAppointedReviewersForAuditorCheckII(cidII, sidII);

            // Re-initialize chronology validator when modal opens
            DateChronologyValidator.init({
                customerId: cidII,
                typedServiceId: sidII,
                fields: {
                    'stage2_intimation_date': { input: '.intimationDateii', errorDiv: '#intimationDateiiError', errorMsg: '#intimationDateiiErrorMsg' },
                    'stage2_audit_date': { input: '.auditiiDates', errorDiv: '#auditiiDatesError', errorMsg: '#auditiiDatesErrorMsg' },
                    'intimation_date': { input: '.intimationDateii', errorDiv: '#intimationDateiiError', errorMsg: '#intimationDateiiErrorMsg' },
                    'audit_date': { input: '.auditiiDates', errorDiv: '#auditiiDatesError', errorMsg: '#auditiiDatesErrorMsg' }
                },
                submitBtn: '.addAuditiiNotiii' // Replace with actual submit button class if different
            });
        });
        $('#intimationDateii').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy',
            autoclose: true,
            onSelect: function () {
                // Validate on date selection
                validateStage2();
            }
        });
        $("#intimationDateii").keydown(function (e) {
            //            e.preventDefault();
        });
        $('.intimationDateii').on('change blur', function () {
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
        } else {
            // Let the Chronology validator handle it
            if (DateChronologyValidator.validateAll()) {
                $isError = true;
            } else {
                $('#intimationDateiiError').hide();
                $('#intimationDateii').removeClass('inputHighlight');
            }
        }
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

        $(document).on('click', '.createAuditNotificationii', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var custId = $btn.attr('data-customer');
            var svcId = $btn.attr('data-service');

            var $icon = $btn.find('i');
            var origClass = $icon.attr('class') || 'fa fa-plus-circle fa-lg';
            $icon.attr('class', 'fa fa-spinner fa-spin fa-lg');
            $btn.css('pointer-events', 'none');

            function restoreCreateBtn() {
                $icon.attr('class', origClass);
                $btn.css('pointer-events', '');
            }

            $('.auditii-noti-customer').val(custId);
            $('.auditii-noti-service').val(svcId);
            loadAppointedReviewersForAuditorCheckII(custId, svcId);
            loadEligibleAuditorsForStageII(custId, svcId, function() {
                resetAuditIIAuditorRows();
            });
            $.ajax({
                url: "getClientInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    customerID: custId,
                    serviceID: svcId,
                },
                success: function (data) {
                    restoreCreateBtn();
                    if (!data || !data[0]) {
                        alert("Could not load client info.");
                        return;
                    }
                    try {
                        $('.auditiiForOrg').val((data[0] && data[0]['customer_name']) || '');
                        $('.auditiiLocation').val((data[0] && data[0]['address']) || '');
                        $('.auditiiReferance').val((data[0] && data[0]['unique_id']) || '');
                        $('.auditiiTelephone').val((data[0] && data[0]['contact']) || '');
                        $('.auditiiEmail').val((data[0] && data[0]['email_id']) || '');
                        $('.auditiiContactPerson').val((data[1] && data[1]['audit_contact_person']) || '');
                        $('.auditiiDesignation').val((data[1] && data[1]['audit_designation']) || '');
                        var stdVal = (data[3] && data[3]['schedule_app_standard']) || (data[1] && data[1]['certification']) || '';
                        $('.auditiiStandard').val(stdVal);
                        $('input[type="text"]').trigger('change');
                        $('.auditiiLocation').trigger('change');
                        $('#auditNotificationII').modal('show');
                    } catch (e) {
                        console.error("Error setting create Stage II fields:", e);
                        $('#auditNotificationII').modal('show');
                    }
                },
                error: function (data, status, err) {
                    restoreCreateBtn();
                    alert("Error loading client info: " + (status || err));
                }
            });
        });

        $(document).on('click', '.editAuditNotificationii', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var notifId = $btn.attr('data-notification');
            var svcId = $btn.attr('data-service');
            var custId = $btn.attr('data-customer') || $('.auditii-noti-customer').val();

            if (!notifId || !svcId) {
                alert('Notification details missing.');
                return;
            }

            // Visual feedback: show spinner on clicked icon
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

            // Fire conflict check in background
            loadAppointedReviewersForAuditorCheckII(custId, svcId);

            // Fire eligible auditors in parallel
            loadEligibleAuditorsForStageII(custId, svcId, function(resp) {
                syncAuditorSelectOptionsII();
            });

            // Fire contract details simultaneously
            $.ajax({
                url: "contractDetails.php",
                type: "GET",
                dataType: "json",
                data: {
                    notificationiiId: notifId,
                    serviceiiId: svcId
                },
                success: function (data) {
                    restoreBtn();
                    if (!data || !data[0]) {
                        alert("Could not load notification details.");
                        return;
                    }

                    try {
                        $('.editTableDiv').empty();
                        var main = data[0] || {};
                        var rows = data[1] || [];

                        $('.auditiiForOrg').val(main['for_org'] || '');

                        var intimDate = main['intimation_date'];
                        if (intimDate && intimDate !== '0000-00-00' && typeof moment !== 'undefined') {
                            $('.intimationDateii').val(moment(intimDate).format('DD.MM.YYYY'));
                        } else {
                            $('.intimationDateii').val(intimDate || '');
                        }

                        $('.auditiiLocation').val(main['location'] || '');
                        $('.auditiiReferance').val(main['audit_ref_no'] || '');
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
                                    $('.auditiiReferance').val(clientData[0]['unique_id']);
                                    $('.auditiiReferance').trigger('change');
                                }
                            }
                        });
                        $('.auditiiStandard').val(main['audit_standard'] || '');
                        $('.auditiiContactPerson').val(main['audit_contact_person'] || '');
                        $('.auditiiDesignation').val(main['audit_designation'] || '');
                        $('.auditiiTelephone').val(main['audit_telephone'] || '');
                        $('.auditiiEmail').val(main['audit_email'] || '');

                        if (main['stage_ii_of']) {
                            $('.stage-ii[value="' + main['stage_ii_of'] + '"]').prop('checked', true);
                        }
                        $('.audtii-noti-id').val(main['audit_stageii_id'] || notifId);
                        $('.auditii-noti-customer').val(main['customer_id'] || custId);
                        $('.auditii-noti-service').val(main['service_id'] || svcId);
                        $('.tllastedit').val(main['auditor_check_last_two'] || '');
                        $('.tllastyc').val(main['auditor_check_comm'] || '');
                        if (main['auditor_check_last_two'] === "YES") {
                            $('.tllasty').prop('checked', true);
                        } else if (main['auditor_check_last_two'] === "NO") {
                            $('.tllastn').prop('checked', true);
                        }

                        $(".newAuditiiDatesEdit").empty();
                        $(".newAuditiiDates").empty();
                        var rawAuditDate = (main['audit_dates'] && typeof main['audit_dates'] === 'string') ? main['audit_dates'] : '';
                        var auditDate = rawAuditDate ? rawAuditDate.split(/,\s*/) : [];
                        var auditDateLen = auditDate.length;

                        for (var key = 0; key < auditDateLen; key++) {
                            var no = auditDate[key] ? String(auditDate[key]).trim() : '';
                            if (key === 0) {
                                $('.auditiiDates').val(no);
                            } else if (no !== '') {
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
                        if (auditDateLen === 0) {
                            $('.auditiiDates').val('');
                        }

                        $rowCounter = 0;
                        if (rows && rows.length > 0) {
                            for (var r = 0; r < rows.length; r++) {
                                $rowCounter++;
                                var isFirst = ($rowCounter === 1);
                                var audId = rows[r]['auditor_id'] || '';
                                var audName = rows[r]['auditor_name'] || '';
                                var rowOptions = isFirst
                                    ? renderNotificationAuditorOptionsII(audId, audName)
                                    : renderNotificationAllAuditorOptionsII(audId, audName);
                                var removeBtnHtml = isFirst
                                    ? '<td width="10%" style="padding: 6px; text-align: center; vertical-align: middle; color: #94a3b8;">&mdash;</td>'
                                    : '<td width="10%" style="padding: 6px; text-align: center; vertical-align: middle;"><button type="button" class="btn btn-sm btn-danger removeRowButton" style="padding: 3px 8px; border-radius: 4px;">Remove</button></td>';

                                $('.editTableDiv').append('<tr class="audit_subsectionii previousCBData" id="cb_subsection_' + r + '">\n\
                                    <td width="36%" style="padding: 6px; vertical-align: middle;">\n\
                                        <div class="table-edit-control">\n\
                                            <select data-row-id="" class="contractAuditorNameii form-control" id="contractAuditorNameii" name="contractAuditorNameii" style="width: 100%;">\n\
                                                ' + rowOptions + '\n\
                                            </select>\n\
                                        </div>\n\
                                    </td>\n\
                                    <td width="24%" style="padding: 6px; vertical-align: middle;">\n\
                                        <div class="table-edit-control">\n\
                                            <select class="auditorCategoryii form-control" name="auditorCategoryii" required style="width: 100%;">\n\
                                                ' + auditIICategoryOptionsHtml + '\n\
                                            </select>\n\
                                        </div>\n\
                                    </td>\n\
                                    <td width="14%" style="padding: 6px; vertical-align: middle;">\n\
                                        <div class="table-edit-control">\n\
                                            <input type="text" min="1" data-row-id="" class="auditorWorkii form-control" id="auditorWorkii" name="auditorWorkii" value="' + (rows[r]['audit_wokrdays'] || '') + '" placeholder="" style="width: 100%;">\n\
                                        </div>\n\
                                    </td>\n\
                                    <td width="16%" style="padding: 6px; vertical-align: middle;">\n\
                                        <div class="table-edit-control">\n\
                                            <select class="auditorAcceptanceii form-control" id="auditorAcceptanceii" name="auditorAcceptanceii" style="width: 100%;">\n\
                                                <option value="' + (rows[r]['auditor_acceptance'] || '') + '">' + (rows[r]['auditor_acceptance'] || 'Select') + '</option><option value="">Select</option><option value="Yes">Yes</option><option value="No">No</option></select></div>\n\
                                    </td>\n\
                                    ' + removeBtnHtml + '\n\
                                </tr>');

                                var newAuditorCategorySelect = $('.editTableDiv').find('.auditorCategoryii').last();
                                if (rows[r]['auditor_category']) {
                                    newAuditorCategorySelect.val(rows[r]['auditor_category']);
                                }
                            }
                        }
                        if ($rowCounter === 0) {
                            $('.editTableDiv').append(buildAuditIIAuditorRow({ removable: false, isSecondOrLater: false }));
                        }

                        $rowCounter = $('.editTableDiv tr').length;
                        $rowCB = $rowCounter;
                        syncAuditorSelectOptionsII();

                        if (main["auditor_check_last_two"] === "YES") {
                            $('.tllasty1').prop("checked", true);
                        } else if (main["auditor_check_last_two"] === "No" || main["auditor_check_last_two"] === "NO") {
                            $('.tllastn2').prop("checked", true);
                        }
                        $(".tllastedit1").val(main["auditor_check_last_two"] || '');
                        $('input[type="text"]').trigger('change');
                        $('input[type="number"]').trigger('change');
                        $('.auditiiLocation').trigger('change');
                        $('.appointedby').val(main['appointed_by'] || '');
                        $('.mcldesignation').val(main['mcl_designation'] || '');

                        $('#auditNotificationII').modal('show');
                    } catch (e) {
                        console.error('Error populating Stage II Notification form:', e);
                        $('#auditNotificationII').modal('show');
                    }
                },
                error: function (xhr, status, err) {
                    restoreBtn();
                    alert("Error loading notification: " + (status || err));
                }
            });
        });

        $(document).on('focus mousedown', '.contractAuditorNameii', function () {
            syncAuditorSelectOptionsII();
        });

        $(document).on('change', ".contractAuditorNameii", function () {
            var $select = $(this);
            var auditorId = $select.val();
            var auditorName = $select.find('option:selected').text().trim();
            if (!auditorId) {
                syncAuditorSelectOptionsII();
                return;
            }

            var isDuplicate = false;
            $('.contractAuditorNameii').not($select).each(function () {
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
                syncAuditorSelectOptionsII();
                return;
            }

            var conflict = checkAuditorConflictWithReviewersII(auditorId, auditorName);
            if (conflict) {
                showAuditorReviewerConflictDialogueII(auditorName, conflict);
                $select.val("");
                syncAuditorSelectOptionsII();
                return;
            }

            var custId = $('.auditii-noti-customer').val() || $('.customerId').val();
            if (custId) {
                $.ajax({
                    url: "getNotificationInfo.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        customerid: custId,
                        auditorid: auditorId,
                    },
                    success: function (data) {
                        if (data == '2') {
                            if (typeof Swal !== 'undefined' && Swal.fire) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Lead Auditor Limit',
                                    text: 'For previous two services acted as Lead Auditor',
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#0F2742'
                                });
                            } else {
                                alert('For previous two services acted as Lead Auditor');
                            }
                            $select.val("");
                            syncAuditorSelectOptionsII();
                        } else {
                            syncAuditorSelectOptionsII();
                        }
                    },
                    error: function () {
                        syncAuditorSelectOptionsII();
                    }
                });
            } else {
                syncAuditorSelectOptionsII();
            }
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

            // Temporarily disabled: allow any auditor to be saved
            /*
            if (!hasAtLeastOneQualifiedAuditorII()) {
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
            $('.contractAuditorNameii').each(function () {
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

        $(document.body).on('click', '.addAuditLinkii', function (e) {
            e.preventDefault();

            $('.editTableDiv').append(buildAuditIIAuditorRow({ removable: true, isSecondOrLater: true }));
            $rowCounter = $('.editTableDiv tr').length;
            $rowCB = $rowCounter;
            syncAuditorSelectOptionsII();

        });

        $(document).on('click', '.removeRowButton', function (e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            $rowCounter = $('.editTableDiv tr').length;
            $rowCB = $rowCounter;
            syncAuditorSelectOptionsII();
        });

    });

</script>

<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once './dbConnect.php';
require_once __DIR__ . '/include/auditor_division_helper.php';
date_default_timezone_set('Asia/Kolkata');
$iafCodes = "SELECT * FROM iaf_codes";
$statement = $db->prepare($iafCodes);
$statement->execute();
$iafList = $statement->fetchAll();
$naceClassesGrouped = aitf_get_all_nace_classes_grouped($db);
?>
<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<style>
    .auditorNumber::-webkit-inner-spin-button,
    .auditorNumber::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .auditorNumber {
        -moz-appearance: textfield;
    }
</style>

<div class="modal fade iafnacemodel" id="" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Division/Group Configuration</h4>
            </div>
            <form action="addIafnacecodes.php" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="row">
                            <div class="col-sm-4 form-group float-label-control">
                                <label>IAF Code</label>
                                <input type="text" class="newIafInput" id="newIafInput" name="newIafInput"
                                    placeholder="Division">
                            </div>
                            <div class="col-sm-7 form-group float-label-control">
                                <label>IAF Description</label>
                                <input type="text" class="newIafDesc" id="newIafDesc" name="newIafDesc"
                                    placeholder="Division Description">
                            </div>
                            <div class="col-sm-1 form-group float-label-control">
                                <a class="newNaceAdd" style="" title="Add Multiple Nace"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        <div class="newnaceaddrow">
                            <div class="row ">
                                <div class="col-sm-4 form-group float-label-control">
                                    <label>Nace Code</label>
                                    <input type="text" class="newNaceCode" id="newNaceCode" name="newNaceCode"
                                        placeholder="Group">
                                </div>
                                <div class="col-sm-7 form-group float-label-control">
                                    <label>Technical Area</label>
                                    <input type="text" class="newNaceDesc" id="newNaceDesc" name="newNaceDesc"
                                        placeholder="Group Description">
                                </div>
                                <div class="col-sm-1 form-group float-label-control">
                                    <a class="removeNaceCode" style="" title="Remove Nace"><i
                                            class="fa fa-close"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="nace_code_array" class="nace_code_array" id="nace_code_array" value="">
                <input type="hidden" name="nace_desc_array" class="nace_desc_array" id="nace_desc_array" value="">
                <input type="hidden" name="iaf_code_id" class="iaf_code_id" id="iaf_code_id" value="">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-info addAifNaceCode">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade iafnaceClassmodel" id="" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">NACE Class Configuration</h4>
            </div>
            <form action="addIafnaceclasses.php" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="row">

                            <div class="col-sm-4 form-group float-label-control">
                                <label>Division</label>
                                <input type="text" class="newIafInput1" id="newIafInput1" name="newIafInput1"
                                    placeholder="Division">
                            </div>
                            <div class="col-sm-7 form-group float-label-control">
                                <label>Group</label>
                                <input type="text" class="newIafDesc1" id="newIafDesc1" name="newIafDesc1"
                                    placeholder="Group">
                            </div>
                            <div class="col-sm-1 form-group float-label-control">
                                <a class="newNaceClassAdd" style="" title="Add Multiple Nace"><i
                                        class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        <div class="newnaceaddrowClass">
                            <div class="row ">
                                <div class="col-sm-4 form-group float-label-control">
                                    <label>Nace Class Code</label>
                                    <input type="text" class="newNaceCode1" id="newNaceCode1" name="newNaceClass1"
                                        placeholder="Nace Class Code">
                                </div>
                                <div class="col-sm-7 form-group float-label-control">
                                    <label>Technical Area</label>
                                    <input type="text" class="newNaceDesc1" id="newNaceDesc1" name="newNaceClassDesc1"
                                        placeholder="Technical Area">
                                </div>
                                <div class="col-sm-1 form-group float-label-control">
                                    <a class="removeNaceCode" style="" title="Remove Nace"><i
                                            class="fa fa-close"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="nace_code_array" class="nace_code_array" id="nace_code_array" value="">
                <input type="hidden" name="nace_desc_array" class="nace_desc_array" id="nace_desc_array" value="">
                <input type="hidden" name="iaf_code_id" class="iaf_code_id" id="iaf_code_id" value="">
                <input type="hidden" name="nace_code_id" class="nace_code_id" id="nace_code_id" value="">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-info addAifNaceCode1">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade auditoradd" id="" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Add Auditor</h4>
            </div>
            <form action="addAuditors.php" method="post" class="auditoraddform">
                <div class="modal-body">
                    <div class="row">
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Auditor Name</label>
                                <input type="text" class="auditorName" id="auditorName" name="auditorName"
                                    placeholder="Auditor Name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Auditor Number</label>
                                <input type="number" class="auditorNumber" id="auditorNumber" name="auditorNumber"
                                    placeholder="Auditor Number">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Auditor Email</label>
                                <input type="email" class="auditorEmail" id="auditorEmail" name="auditorEmail"
                                    placeholder="Auditor Email">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Auditor Abbreviation</label>
                                <input type="text" class="auditorabbr" id="auditorabbr" name="auditorabbr"
                                    placeholder="Auditor Abbreviation">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group" style="margin-top: 5px; margin-bottom: 12px;">
                                <label style="font-weight: 600; color: #1e3a8a; display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                    <span><i class="fa fa-tags"></i> NACE Class Assignment</span>
                                    <small class="text-muted nacodeSelectedCount" style="font-weight: normal; color: #64748b;">0 Selected</small>
                                </label>
                                <div class="dropdown nacode-assignment-wrapper" style="position: relative;">
                                    <button type="button" class="btn btn-default btn-block dropdown-toggle nacodeDropdownToggleBtn" id="nacodeDropdownToggleBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="text-align: left; display: flex; justify-content: space-between; align-items: center; padding: 7px 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: none;">
                                        <span class="nacodeSummaryText" style="color: #475569; font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            Click to assign NACE Classes...
                                        </span>
                                        <span class="caret" style="margin-left: 8px; color: #64748b;"></span>
                                    </button>
                                    <div class="dropdown-menu nacodeDropdownMenu" aria-labelledby="nacodeDropdownToggleBtn" style="width: 100%; min-width: 360px; max-height: 340px; overflow-y: auto; padding: 10px; border-radius: 6px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #cbd5e1; z-index: 1060;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0;">
                                            <span style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Select NACE Classes</span>
                                            <div>
                                                <button type="button" class="btn btn-xs btn-link nacodeSelectAllBtn" style="padding: 0 4px; font-size: 11px; color: #2563eb; text-decoration: none; font-weight: 600;">Select All</button>
                                                <span style="color: #cbd5e1;">|</span>
                                                <button type="button" class="btn btn-xs btn-link nacodeClearAllBtn" style="padding: 0 4px; font-size: 11px; color: #ef4444; text-decoration: none; font-weight: 600;">Clear</button>
                                            </div>
                                        </div>
                                        <div style="margin-bottom: 8px;">
                                            <input type="text" class="form-control input-sm nacodeFilterInput" placeholder="Search class (e.g. 25.62, machining, metals)..." style="font-size: 12px; height: 28px; padding: 4px 8px; border-radius: 4px;">
                                        </div>
                                        <div class="nacodeGroupList" style="display: flex; flex-direction: column; gap: 8px;">
                                            <?php 
                                            $naceGroups = !empty($naceClassesGrouped) ? $naceClassesGrouped : array();
                                            foreach ($naceGroups as $divCode => $group) { 
                                                $dCode = htmlspecialchars($divCode, ENT_QUOTES, 'UTF-8');
                                                $dDesc = htmlspecialchars($group['division_desc'], ENT_QUOTES, 'UTF-8');
                                            ?>
                                                <div class="nacodeDivisionSection" data-division="<?php echo $dCode; ?>" style="border: 1px solid #e2e8f0; border-radius: 5px; overflow: hidden; background: #fff;">
                                                    <div style="background: #f1f5f9; padding: 5px 8px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
                                                        <span style="font-size: 11.5px; font-weight: 700; color: #1e3a8a;">
                                                            Division <?php echo $dCode; ?> <small style="font-weight: normal; color: #475569;">(<?php echo count($group['classes']); ?> classes)</small>
                                                        </span>
                                                        <button type="button" class="btn btn-xs btn-link nacodeSelectGroupBtn" data-division="<?php echo $dCode; ?>" style="padding: 0; font-size: 11px; color: #2563eb; text-decoration: none; font-weight: 600;">
                                                            Select Div <?php echo $dCode; ?>
                                                        </button>
                                                    </div>
                                                    <div class="nacodeGroupItems" style="padding: 4px 6px; display: flex; flex-direction: column; gap: 2px;">
                                                        <?php foreach ($group['classes'] as $cls) {
                                                            $cCode = htmlspecialchars($cls['nace_class'], ENT_QUOTES, 'UTF-8');
                                                            $cDesc = htmlspecialchars($cls['nace_desc'], ENT_QUOTES, 'UTF-8');
                                                            $searchTerms = strtolower($dCode . ' ' . $dDesc . ' ' . $cCode . ' ' . $cDesc);
                                                        ?>
                                                            <label class="nacodeItemLabel" data-search="<?php echo $searchTerms; ?>" data-division="<?php echo $dCode; ?>" style="display: flex; align-items: flex-start; gap: 6px; margin: 0; padding: 3px 6px; border-radius: 3px; cursor: pointer; font-weight: normal; font-size: 11.5px; color: #1e293b; transition: background 0.15s; user-select: none;">
                                                                <input type="checkbox" name="assigned_nace_classes[]" value="<?php echo $cCode; ?>" class="nacodeClassCheckbox nacodeDiv-<?php echo $dCode; ?>" style="margin: 2px 0 0 0; width: 14px; height: 14px; flex-shrink: 0; cursor: pointer;">
                                                                <span style="line-height: 1.3;">
                                                                    <strong style="color: #0f172a;"><?php echo $cCode; ?></strong> &mdash; <span style="color: #475569;"><?php echo $cDesc; ?></span>
                                                                </span>
                                                            </label>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="nacodeSelectedBadges" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 5px;"></div>
                                <small class="text-muted" style="display: block; margin-top: 4px; font-size: 11.5px;">Only auditors assigned to a client's NACE class will be selectable during CA and SA-1 auditor assignment.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group" style="padding-top: 5px;">
                                <label style="cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" class="isReviewerCheckbox" id="isReviewerCheckbox" name="is_reviewer" value="1" style="width: 16px; height: 16px; margin: 0;">
                                    <span style="font-weight: 600; color: #047857;"><i class="fa fa-check-circle"></i> Also Assign as Reviewer</span>
                                </label>
                                <small class="text-muted" style="display: block; margin-left: 24px;">This auditor will also be selectable in the Review Application reviewer list.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="auditor_id" class="auditor_id" id="auditor_id" value="">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-info addAuditor">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="paswordReset" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span
                        class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Reset Password</h4>
            </div>
            <form action="changePassword.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control">
                            <strong>Name : </strong><span class="userNameToReset"></span><br>
                            <strong>User Email : </strong><span class="userEmailToReset"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8 form-group float-label-control">
                            <label for="">Reset Password</label>
                            <input type="password" name="resetNewPassword"
                                class="form-control form-group resetNewPassword" id="resetNewPassword"
                                placeholder="Reset Password">
                        </div>
                        <div class="col-sm-2 form-group float-label-control">
                            <a class="passwordShow" title="Show password"><i class="fa fa-eye"></i></a><a
                                class="passwordHide" title="Hide password" style="display: none"><i
                                    class="fa fa-eye-slash"></i></a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control">
                            <div id="resetNewPasswordError" style="display: none">
                                <small><span id="resetNewPasswordErrorMsg"
                                        style="color: #c9513c; font-style: italic; "></span></small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8 form-group float-label-control">
                            <label for="">Confirm Password</label>
                            <input type="password" name="confirmResetNewPassword"
                                class="form-control form-group confirmResetNewPassword" id="confirmResetNewPassword"
                                placeholder="Confirm Password">
                        </div>
                        <!--                            <div class="col-sm-2 form-group float-label-control" >
                                                        <a class="passwordShow" title="Show password"><i class="fa fa-eye"></i></a><a class="passwordHide" title="Hide password" style="display: none"><i class="fa fa-eye-slash"></i></a>
                                                    </div>-->
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group float-label-control">
                            <div id="confirmResetNewPasswordError" style="display: none">
                                <small><span id="confirmResetNewPasswordErrorMsg"
                                        style="color: #c9513c; font-style: italic; "></span></small>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" class="userID" id="userID" name="userID" value="">
                    <!--                        <input type="hidden" class="editAbbreviationHidden" id="editAbbreviationHidden" name="editAbbreviationHidden" value="">
                    <input type="hidden" class="editAbbreviationIDHidden" id="editAbbreviationIDHidden" name="editAbbreviationIDHidden" value="">-->
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-info resetUserPassword">Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    function validatePassword(pass) {
        //        alert(pass);
        return pass.match(/^(?=.*?[a-zA-Z])(?=.*?[0-9])(?=.*?[@#%$])[a-zA-Z0-9@#%$]{8,16}$/);
        //        return pass.match(/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])([a-zA-Z0-9]{8,})$/);
    }

    var err = true;
    var mainErr = false;
    function validatePasswordReset() {
        $isError = false;
        if ($('#resetNewPassword').val() == "") {
            $('#resetNewPasswordErrorMsg').text("Password is required");
            $('#resetNewPasswordError').show();
            $('#resetNewPassword').addClass('inputHighlight');
            $isError = true;
            //            alert("hi");
        } else if (!validatePassword($('#resetNewPassword').val())) {
            $('#resetNewPasswordErrorMsg').text("Password must contain at least 8 characters, contain at least 1 number, contain at least 1 lowercase character (a-z),contain at least 1 uppercase character (A-Z), contains at least 1 special character");
            $('#resetNewPasswordError').show();
            $('#resetNewPassword').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#resetNewPasswordError').hide();
            $('#resetNewPassword').removeClass('inputHighlight');
        }
        if ($('#confirmResetNewPassword').val() == "") {
            $('#confirmResetNewPasswordErrorMsg').text("Confirm your password");
            $('#confirmResetNewPasswordError').show();
            $('#confirmResetNewPassword').addClass('inputHighlight');
            $isError = true;
            //            alert("hi");
        } else if ($('#resetNewPassword').val() != $('#confirmResetNewPassword').val()) {
            $('#confirmResetNewPasswordErrorMsg').text("Password not match");
            $('#confirmResetNewPasswordError').show();
            $('#confirmResetNewPassword').addClass('inputHighlight');
            $isError = true;
        } else {
            $('#confirmResetNewPasswordError').hide();
            $('#confirmResetNewPassword').removeClass('inputHighlight');
        }
        return $isError;
    }

    $(document).ready(function () {

        $('.resetUserPassword').click(function (e) {
            if (validatePasswordReset()) {
                e.preventDefault();
                return;
            }
        });

        $('.passwordShow').click(function () {
            //            $('.newPassword').type = 'text';
            $('.passwordHide').show();
            document.getElementById('resetNewPassword').type = 'text';
            document.getElementById('userPassword').type = 'text';
            document.getElementById('confirmResetNewPassword').type = 'text';
            $('.passwordShow').hide();
            //            document.getElementById('newPassword').type = 'text';
        });

        $('.passwordHide').click(function () {
            $('.passwordShow').show();
            document.getElementById('resetNewPassword').type = 'password';
            document.getElementById('userPassword').type = 'password';
            document.getElementById('confirmResetNewPassword').type = 'password';
            $('.passwordHide').hide();
        });

        $(".addAifNaceCode").click(function () {
            var nacecode = [];
            $(".newNaceCode").each(function () {
                nacecode.push($(this).val());
            });
            $('.nace_code_array').val(nacecode.join("~"));

            var nacedesc = [];
            $(".newNaceDesc").each(function () {
                nacedesc.push($(this).val());
            });
            $('.nace_desc_array').val(nacedesc.join("~"));
            //            alert($('.nace_code_array').val());
            //            alert($('.nace_desc_array').val());
        });
        $(".addAifNaceCode1").click(function () {
            var nacecode = [];
            $(".newNaceCode1").each(function () {
                nacecode.push($(this).val());
            });
            $('.nace_code_array').val(nacecode.join("~"));

            var nacedesc = [];
            $(".newNaceDesc1").each(function () {
                nacedesc.push($(this).val());
            });
            $('.nace_desc_array').val(nacedesc.join("~"));
            //            alert($('.nace_code_array').val());
            //            alert($('.nace_desc_array').val());
        });
        $(".newNaceAdd").click(function () {
            $(".newnaceaddrow").append('<div class="row parentrow">\n\
                                <div class="col-sm-4 form-group float-label-control">\n\
                                <label>Nace Code</label>\n\
                                <input type="text" class="newNaceCode" id="newNaceCode" name="newNaceCode" placeholder="Nace Code">\n\
                            </div>\n\
                            <div class="col-sm-7 form-group float-label-control">\n\
                                <label>Technical Area</label>\n\
                                <input type="text" class="newNaceDesc" id="newNaceDesc" name="newNaceDesc" placeholder="Technical Area">\n\
                            </div>\n\
                            <div class="col-sm-1 form-group float-label-control contentrow">\n\
                                <a class="removeNaceCode" style="" title="Remove Nace"><i class="fa fa-close"></i></a>\n\
                            </div></div>');
        });
        $(".newNaceClassAdd").click(function () {
            $(".newnaceaddrowClass").append('<div class="row parentrow">\n\
                                <div class="col-sm-4 form-group float-label-control">\n\
                                <label>Nace Code</label>\n\
                                <input type="text" class="newNaceCode1" id="newNaceCode1" name="newNaceCode1" placeholder="Class">\n\
                            </div>\n\
                            <div class="col-sm-7 form-group float-label-control">\n\
                                <label>Technical Area</label>\n\
                                <input type="text" class="newNaceDesc1" id="newNaceDesc1" name="newNaceDesc1" placeholder="Technical Area">\n\
                            </div>\n\
                            <div class="col-sm-1 form-group float-label-control contentrow">\n\
                                <a class="removeNaceCode" style="" title="Remove Nace"><i class="fa fa-close"></i></a>\n\
                            </div></div>');
        });
        $('body').on("click", ".removeNaceCode", function () {
            //        alert("Atleast one action item required");
            $(this).parent("div.contentrow").parent('div.parentrow').remove();
        });
    });
</script>

<!-- Standards Add/Edit Modal -->
<div class="modal fade standardsAddModal" tabindex="-1" role="dialog" aria-labelledby="standardsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="standardsModalLabel">Add Standard</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 form-group float-label-control">
                        <label>Standard Name</label>
                        <input type="text" name="newStandardsInput" class="form-control newStandardsInput" id="newStandardsInput" placeholder="Standard Name" required>
                    </div>
                </div>
                <div id="newStandardsError" style="display: none">
                    <small><span id="newStandardsErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                </div>
            </div>
            <input type="hidden" class="standardId" name="standardId" value="">
            <div class="modal-footer">
                <a class="btn btn-info btn-sm createStandards"><i class="fa fa-check"></i> Save</a>
            </div>
        </div>
    </div>
</div>

<!-- Accreditation Add/Edit Modal -->
<div class="modal fade accreditationAddModal" tabindex="-1" role="dialog" aria-labelledby="accreditationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="accreditationModalLabel">Add Accreditation</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 form-group float-label-control">
                        <label>Accreditation Name</label>
                        <input type="text" name="newAccreditationInput" class="form-control newAccreditationInput" id="newAccreditationInput" placeholder="Accreditation Name" required>
                    </div>
                </div>
                <div id="newAccreditationError" style="display: none">
                    <small><span id="newAccreditationErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                </div>
            </div>
            <input type="hidden" class="accreditationId" name="accreditationId" value="">
            <div class="modal-footer">
                <a class="btn btn-info btn-sm createAccreditation"><i class="fa fa-check"></i> Save</a>
            </div>
        </div>
    </div>
</div>

<!-- Legal Status Add/Edit Modal -->
<div class="modal fade legalStatusAddModal" tabindex="-1" role="dialog" aria-labelledby="legalStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="legalStatusModalLabel">Add Legal Status</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 form-group float-label-control">
                        <label>Legal Status</label>
                        <input type="text" name="newLegalStatusInput" class="form-control newLegalStatusInput" id="newLegalStatusInput" placeholder="Legal Status" required>
                    </div>
                </div>
                <div id="newLegalStatusError" style="display: none">
                    <small><span id="newLegalStatusErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                </div>
            </div>
            <input type="hidden" class="legalStatusId" name="legalStatusId" value="">
            <div class="modal-footer">
                <a class="btn btn-info btn-sm createLegalStatus"><i class="fa fa-check"></i> Save</a>
            </div>
        </div>
    </div>
</div>

<!-- Status Add/Edit Modal -->
<div class="modal fade statusAddModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="statusModalLabel">Add Status</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 form-group float-label-control">
                        <label>Status</label>
                        <input type="text" name="newStatusInput" class="form-control newStatusInput" id="newStatusInput" placeholder="Status" required>
                    </div>
                </div>
                <div id="newStatusError" style="display: none">
                    <small><span id="newStatusErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                </div>
            </div>
            <input type="hidden" class="statusId" name="statusId" value="">
            <div class="modal-footer">
                <a class="btn btn-info btn-sm createStatus"><i class="fa fa-check"></i> Save</a>
            </div>
        </div>
    </div>
</div>

<!-- Categories Add/Edit Modal -->
<div class="modal fade categoriesAddModal" tabindex="-1" role="dialog" aria-labelledby="categoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="categoriesModalLabel">Add Category</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 form-group float-label-control">
                        <label>Category Name</label>
                        <input type="text" name="newcategoriesInput" class="form-control newcategoriesInput" id="newcategoriesInput" placeholder="Category Name" required>
                    </div>
                </div>
                <div id="newcategoriesError" style="display: none">
                    <small><span id="newcategoriesErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                </div>
            </div>
            <input type="hidden" class="categoriesId" name="categoriesId" value="">
            <div class="modal-footer">
                <a class="btn btn-info btn-sm createcategories"><i class="fa fa-check"></i> Save</a>
            </div>
        </div>
    </div>
</div>

<!-- Add-To Add/Edit Modal -->
<div class="modal fade addtoAddModal" tabindex="-1" role="dialog" aria-labelledby="addtoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="addtoModalLabel">Add Add-To</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 form-group float-label-control">
                        <label>Add-To Type</label>
                        <input type="text" name="newAddtoInput" class="form-control newAddtoInput" id="newAddtoInput" placeholder="Add-To Type" required>
                    </div>
                </div>
                <div id="newAddToError" style="display: none">
                    <small><span id="newAddToErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                </div>
            </div>
            <input type="hidden" class="addtoId" name="addtoId" value="">
            <div class="modal-footer">
                <a class="btn btn-info btn-sm createAddTo"><i class="fa fa-check"></i> Save</a>
            </div>
        </div>
    </div>
</div>

<!-- Handler Add/Edit Modal -->
<div class="modal fade handlerAddModal" tabindex="-1" role="dialog" aria-labelledby="handlerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="handlerModalLabel">Add Handler</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 form-group float-label-control">
                        <label>Handler Name</label>
                        <input type="text" name="newHandlerInput" class="form-control newHandlerInput" id="newHandlerInput" placeholder="Handler Name" required>
                    </div>
                </div>
                <div id="newHandlerError" style="display: none">
                    <small><span id="newHandlerErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                </div>
            </div>
            <input type="hidden" class="handlerId" name="handlerId" value="">
            <div class="modal-footer">
                <a class="btn btn-info btn-sm createHandler"><i class="fa fa-check"></i> Save</a>
            </div>
        </div>
    </div>
</div>

<!-- AI Rule Add/Edit Modal -->
<div class="modal fade aiRuleAddModal" tabindex="-1" role="dialog" aria-labelledby="aiRuleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">x</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="aiRuleModalLabel">Add AI Review Rule</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-3 form-group">
                        <label>Rule Code <span style="color:red;">*</span></label>
                        <input type="text" name="aiRuleCode" class="form-control aiRuleCode" placeholder="e.g. ISO-DATE-001" maxlength="80" required>
                    </div>
                    <div class="col-sm-5 form-group">
                        <label>Title <span style="color:red;">*</span></label>
                        <input type="text" name="aiRuleTitle" class="form-control aiRuleTitle" placeholder="Rule Title" maxlength="255" required>
                    </div>
                    <div class="col-sm-3 form-group">
                        <label>Category</label>
                        <input type="text" name="aiRuleCategory" class="form-control aiRuleCategory" placeholder="Dates, Scope, etc" maxlength="120">
                    </div>
                    <div class="col-sm-1 form-group">
                        <label>Active</label><br>
                        <input type="checkbox" name="aiRuleActive" class="aiRuleActive" checked>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-7 form-group">
                        <label>Rule Text <span style="color:red;">*</span></label>
                        <textarea name="aiRuleText" class="form-control aiRuleText" rows="4" required></textarea>
                    </div>
                    <div class="col-sm-5 form-group">
                        <label>Expected Value / Evidence Source</label>
                        <textarea name="aiRuleExpected" class="form-control aiRuleExpected" rows="4"></textarea>
                    </div>
                </div>
                <div id="newAiRuleError" style="display: none; margin-top: 10px;">
                    <small><span id="newAiRuleErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                </div>
            </div>
            <input type="hidden" class="aiRuleId" name="aiRuleId" value="">
            <div class="modal-footer">
                <a class="btn btn-success btn-sm createAiRule"><i class="fa fa-check"></i> Save Rule</a>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>

    function groupFind(selectedValue) {
        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            data: { selectedValue },
            success: function (html) {
                $('#group_id').html(html);
            }
        });
    }
</script>

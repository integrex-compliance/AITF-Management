<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
$iafCodes = "SELECT * FROM iaf_codes";
$statement = $db->prepare($iafCodes);
$statement->execute();
$iafList = $statement->fetchAll();
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

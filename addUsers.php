<div id="wrapper" class="active">  
    <!-- Sidebar -->
    <!-- Sidebar -->
    <?php
    $pageIdentifier = "Add User";

    include_once './header.php';
    include_once './dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    $allUser = "SELECT * FROM user WHERE is_deleted = '0'";
    $statement = $db->prepare($allUser);
    $statement->bindParam(':userId', $_SESSION['id']);
    $statement->execute();
    $allUserList = $statement->fetchAll();

    $userRoles = "SELECT * FROM user_role";
    $statement = $db->prepare($userRoles);
    $statement->execute();
    $allRoles = $statement->fetchAll();
    ?>
    <div id="page-content-wrapper" >
        <div class="page-content inset">
            <div class="container"  style="margin-top: 80px">
                <div class="row" style="margin-top: ">
                    <div class="col-sm-6 col-sm-offset-2">
                        <div class="panel panel-default">

                            <div class="panel-heading" style="background: #193048;">
                                <div class="row">
                                    <div class="col-sm-12 qms_customer_name">
                                        <span><i class="fa fa-user-plus"></i></span> Add User
                                    </div>
                                </div>
                            </div>

                            <form action="addUserToDb.php" method="post">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-sm-6 form-group ">
                                            <div class="row">
                                                <div class="col-sm-12 form-group float-label-control">
                                                    <label>Name</label>
                                                    <input type="text" class="userName" id="userName" name="userName" placeholder="Name" >
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12 form-group float-label-control">
                                                    <div id="userNameError" style="display: none">
                                                        <small><span id="userNameErrorMsg" style="color: #c9513c; font-style: italic; " ></span></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="col-sm-6 form-group ">
                                            <div class="row">
                                                <div class="col-sm-12 form-group float-label-control">
                                                    <label>User Abbreviation</label>
                                                    <input type="text" class="userAbbr" id="userAbbr" name="userAbbr" placeholder="User Abbreviation" >
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12 form-group float-label-control">
                                                    <div id="userAbbrError" style="display: none">
                                                        <small><span id="userAbbrErrorMsg" style="color: #c9513c; font-style: italic; " ></span></small>
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12 form-group float-label-control">
                                                <label>Email Id</label>
                                                <input type="email" class="userEmail" id="userEmail" name="userEmail" placeholder="User Email" >
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12 form-group float-label-control">
                                                <div id="userEmailError" style="display: none">
                                                    <small><span id="userEmailErrorMsg" style="color: #c9513c; font-style: italic; " ></span></small>
                                                </div> 
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6 form-group ">
                                                <div class="row">
                                                    <div class="col-xs-10 form-group float-label-control">
                                                        <label>Password</label>
                                                        <input type="password" class="userPassword" id="userPassword" name="userPassword" placeholder="User Password" >
                                                    </div>
                                                    <div class="col-xs-2 form-group float-label-control" >
                                                        <a class="passwordShow" title="Show password"><i class="fa fa-eye"></i></a><a class="passwordHide" title="Hide password" style="display: none"><i class="fa fa-eye-slash"></i></a>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <div id="passwordError" style="display: none">
                                                            <small><span id="passwordErrorMsg" style="color: #c9513c; font-style: italic; " ></span></small>
                                                        </div> 
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="col-sm-6 form-group ">
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>User Type</label>
                                                        <select name="userRole" class="form-control userRole" id="userRole">
                                                            <option value="">Select User Type</option>
                                                            <?php foreach ($allRoles as $role) { ?> 
                                                                <option value="<?php echo $role['role'] ?>"><?php echo $role['role'] ?></option>                                                    
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <div id="userRoleError" style="display: none">
                                                            <small><span id="userRoleErrorMsg" style="color: #c9513c; font-style: italic; " ></span></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>                                                                        
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-xs btn-info addUser">Add</button>
                                </div>
                            </form>

                        </div>
                    </div>

                    <div class="row " >
                        <div class="col-sm-6 col-sm-offset-2 updateUserDiv" style="display: none;">
                            <div class="panel panel-default">
                                <div class="panel-heading" style="background: #193048;">
                                    <div class="row">
                                        <div class="col-sm-12 qms_customer_name">
                                            <span><i class="fa fa-user"></i></span> Update User
                                        </div>
                                    </div>
                                </div>
                                <form action="addUserToDb.php" method="post">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>User Name</label>
                                                        <input type="text" class="editUserName" id="editUserName" name="editUserName" placeholder="User Name" >
                                                    </div>                                
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <div id="editUserNameError" style="display: none">
                                                            <small><span id="editUserNameErrorMsg" style="color: #c9513c; font-style: italic; " ></span></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>User Abbreviation</label>
                                                        <input type="text" class="editUserAbbr" id="editUserAbbr" name="editUserAbbr" placeholder="User Abbreviation" >
                                                    </div>                                
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 form-group ">
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>Email Id</label>
                                                        <input type="email" class="editUserEmail" id="editUserEmail" name="editUserEmail" placeholder="User Email" >
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <div id="editUserEmailError" style="display: none">
                                                            <small><span id="editUserEmailErrorMsg" style="color: #c9513c; font-style: italic; " ></span></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group float-label-control">
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>User Role</label>
                                                        <select name="editUserRole" class="form-control editUserRole" id="editUserRole">
                                                            <option value="">Select User Type</option>
                                                            <?php foreach ($allRoles as $role) { ?> 
                                                                <option value="<?php echo $role['role'] ?>"><?php echo $role['role'] ?></option>                                                    
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <div id="editUserRoleError" style="display: none">
                                                            <small><span id="editUserRoleErrorMsg" style="color: #c9513c; font-style: italic; " ></span></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" class="userId" id="userId" name="userId" value="">
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-xs btn-info updateCancel">Cancel</button>
                                            <button type="submit" class="btn btn-xs btn-info updateUser">Update</button>                                            
                                        </div>
                                    </div>
                                </form>                            
                            </div>
                        </div>
                    </div>

                    <div class="row" >
                        <div class="col-sm-6 col-sm-offset-2">
                            <div class="row" style="">
                                <div class="col-xs-4  text-center btn  qms_complete_btn ">
                                    <span><i class="fa  ">All Users</i></span>
                                </div>
                                <div class="col-xs-8  text-center btn qms_complete_btn newService">
                                    <span><i class="fa  "> Role</i></span>
                                </div>
                            </div>
                            <div class="row" style="">
                                <div class="well" style="max-height: 300px;overflow: auto;">
                                    <ul class="list-group checked-list-box operateServices">
                                        <?php foreach ($allUserList as $user) { ?>                                                                  
                                            <li class="list-group-item"><?php echo $user['user_name'] ?><?php if ($_SESSION['id'] != $user['user_id']) { ?><a class="deleteUser" data-id="<?php echo $user['user_id'] ?>" style="float: right"><i class="fa fa-trash"></i></a><?php } ?><a class="editUser" data-id="<?php echo $user['user_id'] ?>" style="float: right; padding-right: 10px;"><i class="fa fa-edit"></i></a><a class="resetPassword" data-id="<?php echo $user['user_id'] ?>" data-name="<?php echo $user['user_name'] ?>" data-email="<?php echo $user['email_id'] ?>" title="Reset password" style="float: right; padding-right: 10px;"><i class="fa fa-repeat"></i></a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="delete-details" style="display: none">

                        <div id="loading-div" class="ui-corner-all">
                            <span style="font-size:30pt;"><i style="padding:5px;" class="fa fa-warning text-warning"></i></span>
                            <div> 
                                <span style="font-size:11pt;padding:10px" class="text-primary meeting-cancel-confirm-message" >Are you sure that you want to delete details?</span>
                            </div>
                            <div style="padding:3px;">
                                <button title="Click here to delete"  class="btn btn-primary btn-round btn-round-sm btn-sm delete-confirm-ok">Ok</button>
                                <button title="Click here to cancel" class="btn btn-danger  btn-round btn-round-sm btn-sm delete-cancel">Cancel</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
        .btn-circle {
            width: 30px;
            height: 30px;
            text-align: center;
            padding: 6px 0;
            font-size: 12px;
            line-height: 1.428571429;
            border-radius: 15px;
        }
        #loading-div-background{
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.5);
            width: 100%;
            height: 100%;
        }

        #loading-div{
            width: 300px;
            height: 150px;
            background-color: #fff;
            color:#1468b3;
            border: 5px solid #1468b3;
            text-align: center;
            color: #202020;
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -150px;
            margin-top: -100px;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            opacity: 1.0;
            behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */

        }

        #loading-div-user-info{
            width: 350px;
            background-color: #fff;
            color:#1468b3;
            border: 1px solid gray;
            color: #202020;
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -150px;
            margin-top: -100px;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            opacity: 1.0;
            behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */

        }

        #loading-div-background-resend{
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.5);
            width: 100%;
            height: 100%;
        }

        #loading-div-background-meetedit{
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.5);
            width: 100%;
            height: 100%;
        }
    </style>
</div>
    <script type="text/javascript">

        function validateEmail(email) {
            var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
            return re.test(email);
        }

        function  validatePassword(pass)
        {
            //        alert(pass);
            return pass.match(/^(?=.*?[a-zA-Z])(?=.*?[0-9])(?=.*?[@#%$])[a-zA-Z0-9@#%$]{8,16}$/);
            //        (?=.*?[a-zA-Z])(?=.*?[0-9])(?=.*?[@#%$])[a-zA-Z0-9@#%$]{8,16}$
        }

        var err = true;
        var mainErr = false;

        function validateUserUpdate() {
            $isError = false;
            if ($('#editUserName').val() == "") {
                //            alert('in');
                $('#editUserNameErrorMsg ').text("User name can not be empty");
                $('#editUserNameError').show();
                $('#editUserName').addClass('inputHighlight');
                $isError = true;
            }
            else {
                $('#editUserNameError').hide();
                $('#editUserName').removeClass('inputHighlight');
                //            $error = true;
            }
            if ($('#editUserEmail').val() == "") {
                //            alert('in');
                $('#editUserEmailErrorMsg ').text("User email can not be empty");
                $('#editUserEmailError').show();
                $('#editUserEmail').addClass('inputHighlight');
                $isError = true;
            }
            else if ($('#editUserEmail').val() != "" && !validateEmail($('#editUserEmail').val()))
            {
                $('#editUserEmailErrorMsg').text("The email address is not valid");
                $('#editUserEmailError').show();
                $('#editUserEmail').addClass('inputHighlight');
                $isError = true;
                //    alert("email not valid"+$error);
            } else {
                $('#editUserEmailError').hide();
                $('#editUserEmail').removeClass('inputHighlight');
                //            $error = true;
            }
            if ($('#editUserRole').val() == "") {
                //            alert('in');
                $('#editUserRoleErrorMsg ').text("User role can not be empty");
                $('#editUserRoleError').show();
                $('#editUserRole').addClass('inputHighlight');
                $isError = true;
            }
            else {
                $('#editUserRoleError').hide();
                $('#editUserRole').removeClass('inputHighlight');
                //            $error = true;
            }
            return $isError;
        }

        function validateUser()
        {
            $isError = false;
            if ($('#userName').val() == "") {
                //            alert('in');
                $('#userNameErrorMsg ').text("User name can not be empty");
                $('#userNameError').show();
                $('#userName').addClass('inputHighlight');
                $isError = true;
            }
            else {
                $('#userNameError').hide();
                $('#userName').removeClass('inputHighlight');
                //            $error = true;
            }
            if ($('#userEmail').val() == "") {
                //            alert('in');
                $('#userEmailErrorMsg ').text("User email can not be empty");
                $('#userEmailError').show();
                $('#userEmail').addClass('inputHighlight');
                $isError = true;
            } else if ($('#userEmail').val() != "" && !validateEmail($('#userEmail').val()))
            {
                $('#userEmailErrorMsg').text("The email address is not valid");
                $('#userEmailError').show();
                $('#userEmail').addClass('inputHighlight');
                $isError = true;
                //    alert("email not valid"+$error);
            } else if ($('#userEmail').val() != "") {
                $.ajax({
                    type: "POST",
                    url: "checkCustomer.php",
                    data: {userEmail: $('#userEmail').val()},
                    async: false,
                    success: function (data) {
                        //                    alert(data);
                        if (data == 1) {
                            $('#userEmailErrorMsg').text("Email is already exist in the system");
                            $('#userEmailError').show();
                            $('#userEmail').addClass('inputHighlight');
                            mainErr = true;
                            $isError = true;
                        } else {
                            $('#userEmailError').hide();
                            $('#userEmail').removeClass('inputHighlight');
                            mainErr = false;
                        }
                    }
                });
            }
            else {
                $('#userEmailError').hide();
                $('#userEmail').removeClass('inputHighlight');
                //            $error = true;
            }
            if ($('#userPassword').val() == "") {

                $('#passwordErrorMsg').text("Password is required");
                $('#passwordError').show();
                $('#userPassword').addClass('inputHighlight');
                $isError = true;
                //            alert("hi");
            } else if (!validatePassword($('#userPassword').val())) {
                $('#passwordErrorMsg').text("Password must contain at least 8 characters, contain at least 1 number, contain at least 1 lowercase character (a-z),contain at least 1 uppercase character (A-Z), contains at least 1 special character");
                $('#passwordError').show();
                $('#userPassword').addClass('inputHighlight');
                $isError = true;
            }
            else {
                $('#passwordError').hide();
                $('#userPassword').removeClass('inputHighlight');
            }
            if ($('#userRole').val() == "") {
                //            alert('in');
                $('#userRoleErrorMsg ').text("User role can not be empty");
                $('#userRoleError').show();
                $('#userRole').addClass('inputHighlight');
                $isError = true;
            }
            else {
                $('#userRoleError').hide();
                $('#userRole').removeClass('inputHighlight');
                //            $error = true;
            }
            return $isError;
        }

        $(document).ready(function () {
            $('.addUser').click(function (e) {
                //            alert('hi');
                if (validateUser()) {
                    e.preventDefault();
                    return;
                }
            });

            $('.updateUser').click(function (e) {
                if (validateUserUpdate()) {
                    e.preventDefault();
                    return;
                }
            });

            $('.resetPassword').click(function () {
                $('.userNameToReset').html($(this).attr('data-name'));
                $('.userEmailToReset').html($(this).attr('data-email'));
                $('.userID').val($(this).attr('data-id'));
                //                alert($('.userID').val());
                $('#paswordReset').modal('show');
            });

            if ("<?php echo isset($_GET['userid']) ?>")
                $("#verification-modal").modal("show");

            $('.editUser').click(function () {
                //            alert($(this).attr('data-id'));
                $.ajax({
                    url: "getUserDetails.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        userId: $(this).attr('data-id'),
                    },
                    success: function (data)
                    {
                        //                    alert(data['user_name']);
                        $('.editUserName').val(data['user_name']);
                        $('.editUserAbbr').val(data['abbreviation']);
                        $('.editUserEmail').val(data['email_id']);
                        $('.editUserRole').val(data['role']);
                        $('.userId').val(data['user_id']);
                        $('.updateUserDiv').show();
                        $("input").trigger("change");
                        $('textarea').trigger('change');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });
            });

            $('.updateCancel').click(function () {
                $('.updateUserDiv').hide();
            });

            $('.deleteUser').click(function () {
                $id = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    var userid = $id;
                    var url = 'addUserToDb.php';
                    var form = $('<form action="' + url + '" method="post">' +
                            '<input type="hidden" name="user_id" value="' + userid + '" />' +
                            '</form>');
                    $('body').append(form);
                    form.submit();
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });

            });

            $("#menu-toggle").click(function (e) {
                e.preventDefault();
                $("#wrapper").toggleClass("active");

                $(".container").toggleClass("active");

                //                 $('.dataTable').resize();

                //                alert("after");

                table.draw();
            });

        });

    </script>

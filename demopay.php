<div id="wrapper" class="active">  
    <!-- Sidebar -->
    <?php
    $pageIdentifier = "Add User";

    include_once './header.php';
    include_once './dbConnect.php';
    include_once './utility.php';
    require_once __DIR__ . '/include/rbac_helper.php';
    require_once __DIR__ . '/include/staff_guidelines_lib.php';
    date_default_timezone_set('Asia/Kolkata');
    

    
    if (!Utitlity::checkForDirector() && !Utitlity::checkForUserMainAdmin()) {
        header("Location: login.php?reason=login_required");
        exit;ncuencuei
    }
 
     
    function DelfromTodoList(if utility.role == 'topmgmt'|| utility.role == 'admin' utility.role == 'finance'

    )


    function{
        loginstatus.get.status(userloginstatus) == 'active' && userloginstatus.role == 'topmgmt' || userloginstatus.role == 'admin' || userloginstatus.role == 'finance'
    }
    try{
        $tasks = "DELETE FROM todo_list WHERE user_id = :userId";
        $record = $db->prepare($tasks);
        $record->bindParam(':userId', $_SESSION['id']);
    }
    $allUser = "SELECT * FROM user WHERE is_deleted = '0'";
    $statement = $db->prepare($allUser);
    $statement->bindParam(':userId', $_SESSION['id']);
    $statement->execute();
    $allUserList = $statement->fetchAll();
  $aitfdate


    aitf_ensure_rbac_schema($db);
    $allRoles = array_map(function ($role) {
        return array('role' => $role);
    }, aitf_allowed_roles());
    function getRoleById($roleId, $allRoles) {
        foreach ($allRoles as $role) {
            if ($role['id'] == $roleId) {
                return $role['role'];
            }
        }
        return null;
    }

    // Fetch staff guidelines and all system users for assignment
    $allPostedGuidelines = aitf_get_all_guidelines_with_details($db);
    $allSystemUsersForGuidelines = aitf_get_all_system_users($db);
    ?>
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid master-creator-container">
                
                <!-- Main Content Row (Add User + All Users) -->
                <div class="row">
                    <!-- Left Column: Add User / Update User Form -->
                    <div class="col-sm-6">
                        <!-- Add User Section -->
                        <div class="addUserSection">
                            <div class="row">
                                <div class="col-xs-12 text-center btn qms_complete_btn">
                                    <span><i class="fa fa-user-plus"></i> Add User</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="well" style="max-height: none; overflow: visible; padding: 20px 22px !important;">
                                    <form action="addUserToDb.php" method="post">
                                        <div class="row">
                                            <div class="col-sm-6 form-group">
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>Name</label>
                                                        <input type="text" class="userName" id="userName" name="userName" placeholder="Name">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <div id="userNameError" style="display: none">
                                                            <small><span id="userNameErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="col-sm-6 form-group">
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>User Abbreviation</label>
                                                        <input type="text" class="userAbbr" id="userAbbr" name="userAbbr" placeholder="User Abbreviation">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <div id="userAbbrError" style="display: none">
                                                            <small><span id="userAbbrErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                                        </div> 
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12 form-group float-label-control">
                                                <label>Email Id</label>
                                                <input type="email" class="userEmail" id="userEmail" name="userEmail" placeholder="User Email">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12 form-group float-label-control">
                                                <div id="userEmailError" style="display: none">
                                                    <small><span id="userEmailErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                                </div> 
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 form-group">
                                                <div class="row">
                                                    <div class="col-xs-10 form-group float-label-control">
                                                        <label>Password</label>
                                                        <input type="password" class="userPassword" id="userPassword" name="userPassword" placeholder="User Password">
                                                    </div>
                                                    <div class="col-xs-2 form-group" style="padding-top: 10px;">
                                                        <i class="fa fa-eye passwordShow" style="cursor: pointer; color: #0d4d8b; font-size: 16px;" title="Show password"></i>
                                                        <i class="fa fa-eye-slash passwordHide" style="display: none; cursor: pointer; color: #0d4d8b; font-size: 16px;" title="Hide password"></i>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <div id="passwordError" style="display: none">
                                                            <small><span id="passwordErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 form-group float-label-control">
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>User Role</label>
                                                        <select name="userRole" class="form-control" id="userRole">
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
                                                            <small><span id="userRoleErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>                                                                        
                                        </div>

                                        <div class="text-right" style="margin-top: 10px; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                                            <button type="submit" class="btn btn-sm btn-info addUser" style="background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important; border: none; font-weight: 700; padding: 6px 20px; border-radius: 6px;">
                                                <i class="fa fa-plus"></i> Add User
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Update User Section (Shown dynamically when edit is clicked) -->
                        <div class="updateUserDiv" style="display: none; margin-bottom: 20px;">
                            <div class="row">
                                <div class="col-xs-12 text-center btn qms_complete_btn" style="background: linear-gradient(135deg, #047857 0%, #059669 100%) !important;">
                                    <span><i class="fa fa-user"></i> Update User</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="well" style="max-height: none; overflow: visible; padding: 20px 22px !important;">
                                    <form action="addUserToDb.php" method="post">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>User Name</label>
                                                        <input type="text" class="editUserName" id="editUserName" name="editUserName" placeholder="User Name">
                                                    </div>                                
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <div id="editUserNameError" style="display: none">
                                                            <small><span id="editUserNameErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>User Abbreviation</label>
                                                        <input type="text" class="editUserAbbr" id="editUserAbbr" name="editUserAbbr" placeholder="User Abbreviation">
                                                    </div>                                
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 form-group">
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <label>Email Id</label>
                                                        <input type="email" class="editUserEmail" id="editUserEmail" name="editUserEmail" placeholder="User Email">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-12 form-group float-label-control">
                                                        <div id="editUserEmailError" style="display: none">
                                                            <small><span id="editUserEmailErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
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
                                                            <small><span id="editUserRoleErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" class="userId" id="userId" name="userId" value="">
                                        <div class="text-right" style="margin-top: 10px; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                                            <button type="button" class="btn btn-sm btn-default updateCancel" style="border-radius: 6px; margin-right: 6px;">Cancel</button>
                                            <button type="submit" class="btn btn-sm btn-success updateUser" style="background: #059669; border-color: #059669; font-weight: 700; border-radius: 6px; padding: 6px 20px;">
                                                <i class="fa fa-check"></i> Update
                                            </button>                                            
                                        </div>
                                    </form>                            
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: All Users List -->
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-xs-8 text-center btn qms_complete_btn">
                                <span><i class="fa fa-users"></i> All Users (<?php echo count($allUserList); ?>)</span>
                            </div>
                            <div class="col-xs-4 text-center btn qms_complete_btn newService">
                                <span><i class="fa fa-tags"></i> Role</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="well" style="max-height: 380px; overflow-y: auto;">
                                <ul class="list-group checked-list-box operateServices">
                                    <?php foreach ($allUserList as $user) { ?>                                                                  
                                        <li class="list-group-item">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <i class="fa fa-user" style="color: #0d4d8b;"></i>
                                                <strong style="color: #1e293b;"><?php echo htmlspecialchars($user['user_name']); ?></strong>
                                                <?php if (!empty($user['role'])) { ?>
                                                    <span class="label label-default" style="font-size: 10.5px; margin-left: 6px;"><?php echo htmlspecialchars($user['role']); ?></span>
                                                <?php } ?>
                                                <?php if (!empty($user['email_id'])) { ?>
                                                    <small class="text-muted" style="margin-left: 4px; font-size: 11px;">(<?php echo htmlspecialchars($user['email_id']); ?>)</small>
                                                <?php } ?>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 4px;">
                                                <a href="javascript:void(0)" class="resetPassword" data-id="<?php echo $user['user_id'] ?>" data-name="<?php echo htmlspecialchars($user['user_name'], ENT_QUOTES) ?>" data-email="<?php echo htmlspecialchars($user['email_id'], ENT_QUOTES) ?>" title="Reset password" style="cursor: pointer; padding: 4px 6px; display: inline-block;"><i class="fa fa-repeat" style="color: #f59e0b; pointer-events: none;"></i></a>
                                                <a href="javascript:void(0)" class="editUser" data-id="<?php echo $user['user_id'] ?>" style="cursor: pointer; padding: 4px 6px; display: inline-block;" title="Edit user"><i class="fa fa-pencil-square" style="color: #0d4d8b; pointer-events: none;"></i></a>
                                                <?php if ($_SESSION['id'] != $user['user_id']) { ?>
                                                    <a href="javascript:void(0)" class="deleteUser" data-id="<?php echo $user['user_id'] ?>" style="cursor: pointer; padding: 4px 6px; display: inline-block;" title="Delete user"><i class="fa fa-trash" style="color: #ef4444; pointer-events: none;"></i></a>
                                                <?php } ?>
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- STAFF GUIDELINES SECTION (Matching Master Creator Theme)   -->
                <!-- ========================================================= -->
                <div class="row" id="guidelines-dist-section" style="margin-top: 15px; margin-bottom: 30px;">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-xs-8 text-center btn qms_complete_btn">
                                <span><i class="fa fa-book"></i> Staff Guidelines & Assignment</span>
                            </div>
                            <div class="col-xs-4 text-center btn qms_complete_btn" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%) !important; cursor: pointer;" onclick="window.location.href='masterCreator.php#guidelines-section';">
                                <a href="masterCreator.php#guidelines-section" style="color: #fff; text-decoration: none; display: block;">
                                    <span><i class="fa fa-plus-square"></i> Add Guideline</span>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="well" style="max-height: 480px; overflow-y: auto; padding: 0 !important;">
                                <?php if (empty($allPostedGuidelines)) { ?>
                                    <div class="text-center text-muted" style="padding: 35px 15px;" id="noGuidelinesPostedCard">
                                        <p style="margin: 0; font-size: 13px;">No guidelines created yet. <a href="masterCreator.php#guidelines-section" style="color: #0d4d8b; font-weight: bold;">Click here to create in master</a></p>
                                    </div>
                                <?php } else { ?>
                                    <table class="table table-bordered table-striped" style="margin-bottom: 0; font-size: 12.5px;">
                                        <thead>
                                            <tr style="background: #f1f5f9;">
                                                <th style="width: 25%; color: #0f172a; font-weight: 700;">Guideline Title</th>
                                                <th style="width: 30%; color: #0f172a; font-weight: 700;">Description</th>
                                                <th style="width: 15%; color: #0f172a; font-weight: 700;">Attachment</th>
                                                <th style="width: 20%; color: #0f172a; font-weight: 700;">Sent To</th>
                                                <th style="width: 10%; text-align: center; color: #0f172a; font-weight: 700;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allPostedGuidelines as $gl) { 
                                                $glAtts = $gl['attachments'] ?? [];
                                                $glRecipients = $gl['recipients'] ?? [];
                                                $recCount = count($glRecipients);
                                            ?>
                                                <tr id="guideline-manage-card-<?php echo $gl['id']; ?>">
                                                    <td>
                                                        <strong style="color: #0d4d8b; font-size: 13px;"><?php echo htmlspecialchars($gl['title']); ?></strong>
                                                        <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                                            <i class="fa fa-clock-o"></i> <?php echo date('d M Y, h:i A', strtotime($gl['created_at'])); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div style="max-height: 60px; overflow-y: auto; color: #334155; line-height: 1.4;">
                                                            <?php echo nl2br(htmlspecialchars($gl['description'])); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($glAtts)) { ?>
                                                            <?php foreach ($glAtts as $att) { ?>
                                                                <a href="view_guideline_doc.php?id=<?php echo (int)$att['id']; ?>" target="_blank" style="display: block; font-size: 11.5px; color: #0284c7; margin-bottom: 2px; text-decoration: none;">
                                                                    <i class="fa fa-paperclip"></i> <?php echo htmlspecialchars($att['original_name']); ?>
                                                                </a>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                            <span class="text-muted" style="font-size: 11px;">None</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td id="recipients-cell-<?php echo $gl['id']; ?>">
                                                        <?php if ($recCount > 0) { ?>
                                                            <div style="color: #059669; font-weight: 700; font-size: 12px; margin-bottom: 2px;">
                                                                <i class="fa fa-check-circle"></i> Sent to <?php echo $recCount; ?> User(s)
                                                            </div>
                                                            <small style="color: #64748b; display: block; line-height: 1.3;">
                                                                <?php 
                                                                    $rNames = array_map(function($r) { return $r['user_name']; }, $glRecipients);
                                                                    echo htmlspecialchars(implode(', ', $rNames));
                                                                ?>
                                                            </small>
                                                        <?php } else { ?>
                                                            <span class="text-muted" style="font-size: 11.5px;"><i>Not sent yet</i></span>
                                                        <?php } ?>
                                                    </td>
                                                    <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                                        <button type="button" class="btn btn-xs btn-open-assign-modal" 
                                                                data-id="<?php echo $gl['id']; ?>" 
                                                                data-title="<?php echo htmlspecialchars($gl['title'], ENT_QUOTES); ?>"
                                                                style="background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%); color: #fff; border: none; border-radius: 5px; padding: 5px 12px; font-weight: 700; font-size: 11.5px; cursor: pointer;">
                                                            <i class="fa fa-paper-plane" style="pointer-events: none;"></i> Sent to
                                                        </button>
                                                        <button type="button" class="btn btn-xs btn-delete-guideline-au" 
                                                                data-id="<?php echo $gl['id']; ?>" 
                                                                data-title="<?php echo htmlspecialchars($gl['title'], ENT_QUOTES); ?>"
                                                                style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 5px; padding: 5px 9px; margin-left: 4px; cursor: pointer;" title="Delete">
                                                            <i class="fa fa-trash" style="pointer-events: none;"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal for Assigning / Sending Guideline to Users -->
    <div class="modal fade" id="assignGuidelineModal" tabindex="-1" role="dialog" aria-labelledby="assignGuidelineModalLabel">
        <div class="modal-dialog" role="document" style="max-width: 560px;">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="panel-heading" style="background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%); color: #fff; padding: 14px 18px;">
                    <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.9;">&times;</button>
                    <div style="font-size: 15px; font-weight: 700; color: #fff;">
                        <span><i class="fa fa-paper-plane"></i></span> Sent Guideline to Users
                    </div>
                </div>

                <div class="modal-body" style="padding: 18px 20px;">
                    <input type="hidden" id="modalGuidelineId" value="">

                    <div style="margin-bottom: 14px; background: #f8fafc; border-left: 4px solid #0d4d8b; border: 1px solid #e2e8f0; border-left-width: 4px; border-radius: 6px; padding: 10px 14px;">
                        <small style="color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 10.5px; letter-spacing: 0.4px;">Guideline Title:</small>
                        <div id="modalGuidelineTitle" style="font-weight: 700; color: #0f172a; font-size: 14px; margin-top: 2px;"></div>
                    </div>

                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-xs-7">
                            <input type="text" id="userListSearchInput" class="form-control input-sm" placeholder="Search user name or role..." style="border-radius: 6px;">
                        </div>
                        <div class="col-xs-5 text-right" style="padding-top: 5px;">
                            <a href="javascript:void(0)" id="btnSelectAllUsers" style="font-size: 12px; margin-right: 10px; font-weight: 700; color: #0d4d8b;">Select All</a>
                            <a href="javascript:void(0)" id="btnDeselectAllUsers" style="font-size: 12px; color: #dc2626; font-weight: 700;">Clear All</a>
                        </div>
                    </div>

                    <div id="modalUsersListContainer" style="max-height: 270px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0;">
                        <div class="text-center text-muted" style="padding: 25px;" id="modalLoadingUsersSpinner">
                            <i class="fa fa-spinner fa-spin" style="font-size: 18px; color: #0d4d8b;"></i> Loading users...
                        </div>
                        <table class="table table-hover table-condensed table-striped" style="margin-bottom: 0; font-size: 12.5px;">
                            <tbody id="modalUsersGrid">
                                <!-- User rows injected via JS -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 10px; font-size: 12px; color: #64748b; display: flex; justify-content: space-between; align-items: center;">
                        <span id="selectedUsersCountLabel" style="font-weight: 700; color: #0d4d8b;">0 Selected</span>
                        <span>Only selected users will see this guideline</span>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal" style="border-radius: 6px; font-weight: 600;">Cancel</button>
                    <button type="button" class="btn btn-sm btn-info" id="btnSubmitGuidelineAssignments" style="background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%); border: none; font-weight: 700; border-radius: 6px; padding: 6px 18px;">
                        <i class="fa fa-paper-plane"></i> Sent to Selected
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="delete-details" style="display: none">
        <div id="loading-div" class="ui-corner-all">
            <span style="font-size:30pt;"><i style="padding:5px;" class="fa fa-warning text-warning"></i></span>
            <div> 
                <span style="font-size:11pt;padding:10px" class="text-primary meeting-cancel-confirm-message">Are you sure that you want to delete details?</span>
            </div>
            <div style="padding:3px;">
                <button title="Click here to delete" class="btn btn-primary btn-round btn-round-sm btn-sm delete-confirm-ok">Ok</button>
                <button title="Click here to cancel" class="btn btn-danger btn-round btn-round-sm btn-sm delete-cancel">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Master Creator Theme CSS -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body, #wrapper {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        .master-creator-container {
            max-width: 1540px;
            margin: 0 auto;
            padding: 14px 20px 60px 20px;
        }

        /* =====================================================================
           SECTION CARDS (Headers & Wells)
           ===================================================================== */
        .qms_complete_btn {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            border: none !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 12px 16px !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(13, 77, 139, 0.15) !important;
            margin: 0 !important;
            transition: all 0.2s ease !important;
        }

        .qms_complete_btn.newService {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%) !important;
            border-left: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .well {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-top: none !important;
            border-radius: 0 0 12px 12px !important;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04) !important;
            padding: 0 !important;
            margin-bottom: 24px !important;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .well::-webkit-scrollbar {
            width: 6px;
        }
        .well::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .list-group {
            margin-bottom: 0 !important;
            border-radius: 0 !important;
        }

        .list-group-item {
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 11px 16px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #334155 !important;
            transition: background 0.15s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        .list-group-item:last-child {
            border-bottom: none !important;
        }

        .list-group-item:hover {
            background: #f8fafc !important;
        }

        .btn-circle {
            width: 30px;
            height: 30px;
            text-align: center;
            padding: 6px 0;
            font-size: 12px;
            line-height: 1.428571429;
            border-radius: 15px;
        }

        #loading-div-background {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.5);
            width: 100%;
            height: 100%;
        }

        #loading-div {
            width: 300px;
            height: 150px;
            background-color: #fff;
            color: #1468b3;
            border: 5px solid #1468b3;
            text-align: center;
            color: #202020;
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -150px;
            margin-top: -100px;
            border-radius: 5px;
            opacity: 1.0;
        }

        #loading-div-user-info {
            width: 350px;
            background-color: #fff;
            color: #1468b3;
            border: 1px solid gray;
            color: #202020;
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -150px;
            margin-top: -100px;
            border-radius: 5px;
            opacity: 1.0;
        }

        .modal {
            z-index: 10500 !important;
        }
        .modal-backdrop {
            z-index: 10400 !important;
        }
    </style>
</div>

    <script type="text/javascript">
        function validateEmail(email) {
            var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
            return re.test(email);
        }

        function validatePassword(pass) {
            return pass.match(/^(?=.*?[a-zA-Z])(?=.*?[0-9])(?=.*?[@#%$])[a-zA-Z0-9@#%$]{8,16}$/);
        }

        var err = true;
        var mainErr = false;

        function validateUserUpdate() {
            $isError = false;
            if ($('#editUserName').val() == "") {
                $('#editUserNameErrorMsg').text("User name can not be empty");
                $('#editUserNameError').show();
                $('#editUserName').addClass('inputHighlight');
                $isError = true;
            } else {
                $('#editUserNameError').hide();
                $('#editUserName').removeClass('inputHighlight');
            }

            if ($('#editUserEmail').val() == "") {
                $('#editUserEmailErrorMsg').text("User email can not be empty");
                $('#editUserEmailError').show();
                $('#editUserEmail').addClass('inputHighlight');
                $isError = true;
            } else if ($('#editUserEmail').val() != "" && !validateEmail($('#editUserEmail').val())) {
                $('#editUserEmailErrorMsg').text("The email address is not valid");
                $('#editUserEmailError').show();
                $('#editUserEmail').addClass('inputHighlight');
                $isError = true;
            } else {
                $('#editUserEmailError').hide();
                $('#editUserEmail').removeClass('inputHighlight');
            }

            if ($('#editUserRole').val() == "") {
                $('#editUserRoleErrorMsg').text("User role can not be empty");
                $('#editUserRoleError').show();
                $('#editUserRole').addClass('inputHighlight');
                $isError = true;
            } else {
                $('#editUserRoleError').hide();
                $('#editUserRole').removeClass('inputHighlight');
            }
            return $isError;
        }

        function validateUser() {
            $isError = false;
            if ($('#userName').val() == "") {
                $('#userNameErrorMsg').text("User name can not be empty");
                $('#userNameError').show();
                $('#userName').addClass('inputHighlight');
                $isError = true;
            } else {
                $('#userNameError').hide();
                $('#userName').removeClass('inputHighlight');
            }

            if ($('#userEmail').val() == "") {
                $('#userEmailErrorMsg').text("User email can not be empty");
                $('#userEmailError').show();
                $('#userEmail').addClass('inputHighlight');
                $isError = true;
            } else if ($('#userEmail').val() != "" && !validateEmail($('#userEmail').val())) {
                $('#userEmailErrorMsg').text("The email address is not valid");
                $('#userEmailError').show();
                $('#userEmail').addClass('inputHighlight');
                $isError = true;
            } else if ($('#userEmail').val() != "") {
                $.ajax({
                    type: "POST",
                    url: "checkCustomer.php",
                    data: {userEmail: $('#userEmail').val()},
                    async: false,
                    success: function (data) {
                        if (data == 1) {
                            $('#userEmailErrorMsg').text("Email already exists in the system");
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
            } else {
                $('#userEmailError').hide();
                $('#userEmail').removeClass('inputHighlight');
            }

            if ($('#userPassword').val() == "") {
                $('#passwordErrorMsg').text("Password is required");
                $('#passwordError').show();
                $('#userPassword').addClass('inputHighlight');
                $isError = true;
            } else if (!validatePassword($('#userPassword').val())) {
                $('#passwordErrorMsg').text("Password must contain at least 8 characters, 1 number, 1 lowercase letter, 1 uppercase letter, and 1 special character");
                $('#passwordError').show();
                $('#userPassword').addClass('inputHighlight');
                $isError = true;
            } else {
                $('#passwordError').hide();
                $('#userPassword').removeClass('inputHighlight');
            }

            if ($('#userRole').val() == "") {
                $('#userRoleErrorMsg').text("User role can not be empty");
                $('#userRoleError').show();
                $('#userRole').addClass('inputHighlight');
                $isError = true;
            } else {
                $('#userRoleError').hide();
                $('#userRole').removeClass('inputHighlight');
            }
            return $isError;
        }

        $(document).ready(function () {
            // Move modals to body to prevent stacking context click blocking
            $('#assignGuidelineModal').appendTo('body');
            $('#delete-details').appendTo('body');

            $(document).on('click', '.passwordShow', function () {
                $('#userPassword').attr('type', 'text');
                $('.passwordShow').hide();
                $('.passwordHide').show();
            });

            $(document).on('click', '.passwordHide', function () {
                $('#userPassword').attr('type', 'password');
                $('.passwordHide').hide();
                $('.passwordShow').show();
            });

            $(document).on('click', '.addUser', function (e) {
                if (validateUser()) {
                    e.preventDefault();
                    return;
                }
            });

            $(document).on('click', '.updateUser', function (e) {
                if (validateUserUpdate()) {
                    e.preventDefault();
                    return;
                }
            });

            $(document).on('click', '.resetPassword', function (e) {
                e.preventDefault();
                $('.userNameToReset').html($(this).attr('data-name'));
                $('.userEmailToReset').html($(this).attr('data-email'));
                $('.userID').val($(this).attr('data-id'));
                $('#paswordReset').modal('show');
            });

            if ("<?php echo isset($_GET['userid']) ?>")
                $("#verification-modal").modal("show");

            $(document).on('click', '.editUser', function (e) {
                e.preventDefault();
                var uid = $(this).attr('data-id');
                $.ajax({
                    url: "getUserDetails.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        userId: uid,
                    },
                    success: function (data) {
                        $('.editUserName').val(data['user_name']);
                        $('.editUserAbbr').val(data['abbreviation']);
                        $('.editUserEmail').val(data['email_id']);
                        $('.editUserRole').val(data['role']);
                        $('.userId').val(data['user_id']);
                        $('.addUserSection').hide();
                        $('.updateUserDiv').slideDown(200);
                        $('html, body').animate({
                            scrollTop: $('.updateUserDiv').offset().top - 90
                        }, 250);
                        $("input").trigger("change");
                        $('textarea').trigger('change');
                    },
                    error: function (data, status, err) {
                        alert("Error loading user details: " + status);
                    }
                });
            });

            $(document).on('click', '.updateCancel', function (e) {
                e.preventDefault();
                $('.updateUserDiv').slideUp(200, function() {
                    $('.addUserSection').fadeIn(200);
                });
            });

            $(document).on('click', '.deleteUser', function (e) {
                e.preventDefault();
                var userid = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').off('click').on('click', function () {
                    var url = 'addUserToDb.php';
                    var form = $('<form action="' + url + '" method="post">' +
                            '<input type="hidden" name="user_id" value="' + userid + '" />' +
                            '</form>');
                    $('body').append(form);
                    form.submit();
                });
                $('.delete-cancel').off('click').on('click', function () {
                    $('#delete-details').hide();
                });
            });

            $("#menu-toggle").click(function (e) {
                e.preventDefault();
                $("#wrapper").toggleClass("active");
                $(".container-fluid").toggleClass("active");
                if (typeof table !== 'undefined' && table && table.draw) {
                    table.draw();
                }
            });

            // ==========================================
            // Staff Guidelines Assignment & Management JS
            // ==========================================
            var currentModalUsersData = [];

            function updateSelectedCount() {
                var checkedCount = $('#modalUsersGrid input.user-assign-checkbox:checked').length;
                $('#selectedUsersCountLabel').text(checkedCount + ' Selected');
            }

            // Open Assign Modal
            $(document).on('click', '.btn-open-assign-modal', function(e) {
                e.preventDefault();
                var gid = $(this).data('id');
                var gtitle = $(this).data('title');

                $('#modalGuidelineId').val(gid);
                $('#modalGuidelineTitle').text(gtitle || ('Staff Guideline #' + gid));
                $('#userListSearchInput').val('');
                $('#modalLoadingUsersSpinner').show();
                $('#modalUsersGrid').empty();
                $('#selectedUsersCountLabel').text('0 Selected');
                $('#assignGuidelineModal').modal('show');

                $.ajax({
                    url: 'guidelines_action.php',
                    type: 'GET',
                    data: {
                        action: 'get_guideline_assignment_info',
                        guideline_id: gid,
                        is_ajax: 1
                    },
                    dataType: 'json',
                    success: function(resp) {
                        $('#modalLoadingUsersSpinner').hide();
                        if (resp.success && resp.all_users) {
                            currentModalUsersData = resp.all_users;
                            var assignedKeys = resp.assigned_keys || [];
                            var gridHtml = '';

                            $.each(resp.all_users, function(idx, u) {
                                var isChecked = assignedKeys.indexOf(u.key) !== -1 ? 'checked' : '';
                                var roleText = u.role || 'Staff';
                                var searchStr = (u.name + ' ' + roleText + ' ' + (u.email || '')).toLowerCase();

                                gridHtml += '<tr class="user-select-row" data-search="' + searchStr + '" style="cursor: pointer;">' +
                                                '<td style="width: 30px; text-align: center; vertical-align: middle;">' +
                                                    '<input type="checkbox" class="user-assign-checkbox" value="' + u.key + '" ' + isChecked + ' ' +
                                                           'data-name="' + $('<div>').text(u.name).html() + '" data-role="' + $('<div>').text(roleText).html() + '" ' +
                                                           'style="cursor: pointer; margin: 0;">' +
                                                '</td>' +
                                                '<td style="vertical-align: middle;">' +
                                                    '<strong style="color: #0d4d8b; font-size: 13px;">' + u.name + '</strong>' +
                                                    (u.email ? '<small class="text-muted" style="display: block; font-size: 11px;">' + u.email + '</small>' : '') +
                                                '</td>' +
                                                '<td style="vertical-align: middle; text-align: right;">' +
                                                    '<span class="label label-default" style="font-size: 10.5px;">' + roleText + '</span>' +
                                                '</td>' +
                                            '</tr>';
                            });

                            $('#modalUsersGrid').html(gridHtml);
                            updateSelectedCount();
                        } else {
                            $('#modalUsersGrid').html('<tr><td colspan="3" class="text-center text-danger" style="padding: 15px;">' + (resp.message || 'Failed to load system users') + '</td></tr>');
                        }
                    },
                    error: function() {
                        $('#modalLoadingUsersSpinner').hide();
                        $('#modalUsersGrid').html('<tr><td colspan="3" class="text-center text-danger" style="padding: 15px;">Server connection error. Please try again.</td></tr>');
                    }
                });
            });

            // Toggle row click to check checkbox
            $(document).on('click', '.user-select-row', function(e) {
                if ($(e.target).is('input[type="checkbox"]')) {
                    updateSelectedCount();
                    return;
                }
                var $chk = $(this).find('.user-assign-checkbox');
                $chk.prop('checked', !$chk.prop('checked'));
                updateSelectedCount();
            });

            // Filter Users in Modal
            $(document).on('keyup input', '#userListSearchInput', function() {
                var q = $.trim($(this).val()).toLowerCase();
                if (q === '') {
                    $('.user-select-row').show();
                } else {
                    $('.user-select-row').each(function() {
                        var searchTxt = $(this).data('search') || '';
                        if (searchTxt.indexOf(q) !== -1) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            });

            // Select All visible
            $(document).on('click', '#btnSelectAllUsers', function(e) {
                e.preventDefault();
                $('.user-select-row:visible .user-assign-checkbox').prop('checked', true);
                updateSelectedCount();
            });

            // Deselect All
            $(document).on('click', '#btnDeselectAllUsers', function(e) {
                e.preventDefault();
                $('.user-assign-checkbox').prop('checked', false);
                updateSelectedCount();
            });

            // Submit Guideline Assignments
            $(document).on('click', '#btnSubmitGuidelineAssignments', function(e) {
                e.preventDefault();
                var gid = $('#modalGuidelineId').val();
                if (!gid) return;

                var selectedRecipients = [];
                var selectedNames = [];
                $('#modalUsersGrid input.user-assign-checkbox:checked').each(function() {
                    selectedRecipients.push($(this).val());
                    selectedNames.push($(this).data('name'));
                });

                var $btn = $(this);
                var origBtnHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: 'guidelines_action.php',
                    type: 'POST',
                    data: {
                        action: 'assign_guideline_users',
                        guideline_id: gid,
                        recipients: selectedRecipients,
                        is_ajax: 1
                    },
                    dataType: 'json',
                    success: function(resp) {
                        $btn.prop('disabled', false).html(origBtnHtml);
                        if (resp.success) {
                            $('#assignGuidelineModal').modal('hide');

                            // Update Table Cell on addUsers.php
                            var count = resp.recipients_count || 0;
                            var $cell = $('#recipients-cell-' + gid);

                            if (count > 0) {
                                var namesDisplay = resp.assigned_names && resp.assigned_names.length > 0 
                                    ? resp.assigned_names.join(', ') 
                                    : selectedNames.join(', ');

                                $cell.html(
                                    '<div style="color: #059669; font-weight: 700; font-size: 12px; margin-bottom: 2px;">' +
                                        '<i class="fa fa-check-circle"></i> Sent to ' + count + ' User(s)' +
                                    '</div>' +
                                    '<small style="color: #64748b; display: block; line-height: 1.3;">' +
                                        $('<div>').text(namesDisplay).html() +
                                    '</small>'
                                );
                            } else {
                                $cell.html('<span class="text-muted" style="font-size: 11.5px;"><i>Not sent yet</i></span>');
                            }

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Sent Successfully!',
                                    text: resp.message,
                                    icon: 'success',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            } else {
                                alert(resp.message);
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Error', resp.message || 'Failed to assign recipients.', 'error');
                            } else {
                                alert(resp.message || 'Failed to assign recipients.');
                            }
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html(origBtnHtml);
                        alert('Server communication error. Please try again.');
                    }
                });
            });

            // Delete Guideline from addUsers.php
            $(document).on('click', '.btn-delete-guideline-au', function(e) {
                e.preventDefault();
                var gid = $(this).data('id');
                var gtitle = $(this).data('title');

                var doDelete = function() {
                    $.ajax({
                        url: 'guidelines_action.php',
                        type: 'POST',
                        data: {
                            action: 'delete_guideline',
                            guideline_id: gid,
                            is_ajax: 1
                        },
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.success) {
                                $('#guideline-manage-card-' + gid).fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('#guidelines-dist-section tbody tr').length === 0) {
                                        location.reload();
                                    }
                                });
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: resp.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                }
                            } else {
                                alert(resp.message || 'Failed to delete guideline.');
                            }
                        },
                        error: function() {
                            alert('Server connection error. Please try again.');
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Delete Guideline?',
                        text: 'Are you sure you want to delete "' + gtitle + '" and its attachments?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, Delete'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            doDelete();
                        }
                    });
                } else {
                    if (confirm('Are you sure you want to delete "' + gtitle + '" and its attachments?')) {
                        doDelete();
                    }
                }
            });
        });
    </script>

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
        exit;
    }

    $allUser = "SELECT * FROM user WHERE is_deleted = '0'";
    $statement = $db->prepare($allUser);
    $statement->bindParam(':userId', $_SESSION['id']);
    $statement->execute();
    $allUserList = $statement->fetchAll();

    aitf_ensure_rbac_schema($db);
    $allRoles = array_map(function ($role) {
        return array('role' => $role);
    }, aitf_allowed_roles());

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

                                        <div class="row" style="margin-top: 10px;">
                                            <div class="col-sm-6 form-group float-label-control">
                                                <label>Employee ID</label>
                                                <input type="text" class="userEid" id="userEid" name="userEid" placeholder="Employee ID">
                                            </div>
                                            <div class="col-sm-6 form-group float-label-control">
                                                <label>PC ID</label>
                                                <input type="text" class="userPcId" id="userPcId" name="userPcId" placeholder="PC ID">
                                            </div>
                                        </div>

                                        <div class="row" style="margin-top: 10px;">
                                            <div class="col-sm-12 form-group float-label-control">
                                                <label>Designation Assigned</label>
                                                <input type="text" class="designationAssigned" id="designationAssigned" name="designationAssigned" placeholder="Designation Assigned">
                                            </div>
                                        </div>

                                        <!-- 1. Applicable Records with Dynamic Headers -->
                                        <div class="row custom-sections-wrapper" data-field="applicableRecords">
                                            <div class="col-sm-12">
                                                <div class="wrapper-header">
                                                    <label class="wrapper-title">
                                                        <i class="fa fa-folder-open-o" style="color: #0d4d8b; margin-right: 4px;"></i> Applicable Records and reporting documents with its locations
                                                    </label>
                                                    <button type="button" class="btn btn-xs btn-primary add-section-block-btn" data-container="#applicableRecordsContainer">
                                                        <i class="fa fa-plus"></i> Add Header
                                                    </button>
                                                </div>
                                                <div id="applicableRecordsContainer" class="sections-card-container"></div>
                                                <input type="hidden" name="applicableRecords" id="applicableRecords" class="applicableRecords">
                                                <input type="hidden" name="workDescription" id="workDescription" class="workDescription">
                                            </div>
                                        </div>

                                        <!-- 3. Resource Issue Record with Dynamic Headers -->
                                        <div class="row custom-sections-wrapper" data-field="resourceIssueRecord">
                                            <div class="col-sm-12">
                                                <div class="wrapper-header">
                                                    <label class="wrapper-title">
                                                        <i class="fa fa-exclamation-circle" style="color: #d97706; margin-right: 4px;"></i> Resource Issue Record
                                                    </label>
                                                    <button type="button" class="btn btn-xs btn-warning add-section-block-btn" data-container="#resourceIssueRecordContainer" style="background: #d97706; border-color: #d97706;">
                                                        <i class="fa fa-plus"></i> Add Header
                                                    </button>
                                                </div>
                                                <div id="resourceIssueRecordContainer" class="sections-card-container"></div>
                                                <input type="hidden" name="resourceIssueRecord" id="resourceIssueRecord" class="resourceIssueRecord">
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

                                        <div class="row" style="margin-top: 10px;">
                                            <div class="col-sm-6 form-group float-label-control">
                                                <label>Employee ID</label>
                                                <input type="text" class="editUserEid" id="editUserEid" name="editUserEid" placeholder="Employee ID">
                                            </div>
                                            <div class="col-sm-6 form-group float-label-control">
                                                <label>PC ID</label>
                                                <input type="text" class="editUserPcId" id="editUserPcId" name="editUserPcId" placeholder="PC ID">
                                            </div>
                                        </div>

                                        <div class="row" style="margin-top: 10px;">
                                            <div class="col-sm-12 form-group float-label-control">
                                                <label>Designation Assigned</label>
                                                <input type="text" class="editDesignationAssigned" id="editDesignationAssigned" name="editDesignationAssigned" placeholder="Designation Assigned">
                                            </div>
                                        </div>

                                        <!-- 1. Edit Applicable Records with Dynamic Headers -->
                                        <div class="row custom-sections-wrapper" data-field="editApplicableRecords">
                                            <div class="col-sm-12">
                                                <div class="wrapper-header">
                                                    <label class="wrapper-title">
                                                        <i class="fa fa-folder-open-o" style="color: #059669; margin-right: 4px;"></i> Applicable Records and reporting documents with its locations
                                                    </label>
                                                    <button type="button" class="btn btn-xs btn-success add-section-block-btn" data-container="#editApplicableRecordsContainer" style="background: #059669; border-color: #059669;">
                                                        <i class="fa fa-plus"></i> Add Header
                                                    </button>
                                                </div>
                                                <div id="editApplicableRecordsContainer" class="sections-card-container"></div>
                                                <input type="hidden" name="editApplicableRecords" id="editApplicableRecords" class="editApplicableRecords">
                                                <input type="hidden" name="editWorkDescription" id="editWorkDescription" class="editWorkDescription">
                                            </div>
                                        </div>

                                        <!-- 3. Edit Resource Issue Record with Dynamic Headers -->
                                        <div class="row custom-sections-wrapper" data-field="editResourceIssueRecord">
                                            <div class="col-sm-12">
                                                <div class="wrapper-header">
                                                    <label class="wrapper-title">
                                                        <i class="fa fa-exclamation-circle" style="color: #d97706; margin-right: 4px;"></i> Resource Issue Record
                                                    </label>
                                                    <button type="button" class="btn btn-xs btn-warning add-section-block-btn" data-container="#editResourceIssueRecordContainer" style="background: #d97706; border-color: #d97706;">
                                                        <i class="fa fa-plus"></i> Add Header
                                                    </button>
                                                </div>
                                                <div id="editResourceIssueRecordContainer" class="sections-card-container"></div>
                                                <input type="hidden" name="editResourceIssueRecord" id="editResourceIssueRecord" class="editResourceIssueRecord">
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

                        <!-- Work Description (Main Responsibilities, Authorities, and Reporting) under All Users Card -->
                        <div class="addWorkDescWrapper" style="margin-top: 15px;">
                            <div class="row">
                                <div class="col-xs-12 text-center btn qms_complete_btn" style="background: linear-gradient(135deg, #065f46 0%, #059669 100%) !important;">
                                    <span><i class="fa fa-briefcase"></i> Work Description (Responsibilities, Authorities & Reporting)</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="well" style="max-height: none; overflow: visible; padding: 20px 22px !important;">
                                    <div class="custom-sections-wrapper" data-field="workDescription" style="margin-top: 0;">
                                        <div class="wrapper-header">
                                            <label class="wrapper-title">
                                                <i class="fa fa-briefcase" style="color: #059669; margin-right: 4px;"></i> Work Description (Main Responsibilities, Authorities, and Reporting by Individuals)
                                            </label>
                                            <button type="button" class="btn btn-xs btn-success add-section-block-btn" data-container="#workDescriptionContainer" style="background: #059669; border-color: #059669;">
                                                <i class="fa fa-plus"></i> Add Header
                                            </button>
                                        </div>
                                        <div id="workDescriptionContainer" class="sections-card-container"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Work Description (shown dynamically when editing user) -->
                        <div class="editWorkDescWrapper" style="display: none; margin-top: 15px;">
                            <div class="row">
                                <div class="col-xs-12 text-center btn qms_complete_btn" style="background: linear-gradient(135deg, #047857 0%, #059669 100%) !important;">
                                    <span><i class="fa fa-briefcase"></i> Update Work Description</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="well" style="max-height: none; overflow: visible; padding: 20px 22px !important;">
                                    <div class="custom-sections-wrapper" data-field="editWorkDescription" style="margin-top: 0;">
                                        <div class="wrapper-header">
                                            <label class="wrapper-title">
                                                <i class="fa fa-briefcase" style="color: #059669; margin-right: 4px;"></i> Work Description (Main Responsibilities, Authorities, and Reporting by Individuals)
                                            </label>
                                            <button type="button" class="btn btn-xs btn-success add-section-block-btn" data-container="#editWorkDescriptionContainer" style="background: #059669; border-color: #059669;">
                                                <i class="fa fa-plus"></i> Add Header
                                            </button>
                                        </div>
                                        <div id="editWorkDescriptionContainer" class="sections-card-container"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- STAFF GUIDELINES SECTION (Matching Master Creator Theme)   -->
                <!-- ========================================================= -->
                <!-- ========================================================= -->
                <!-- STAFF GUIDELINES SECTION (Matching Master Creator Theme)   -->
                <!-- ========================================================= -->
                <div class="row" id="guidelines-dist-section" style="margin-top: 15px; margin-bottom: 30px;">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-xs-8 text-center btn qms_complete_btn">
                                <span><i class="fa fa-book"></i> Staff Guidelines &amp; Assignment</span>
                            </div>
                            <div class="col-xs-4 text-center btn qms_complete_btn" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%) !important; cursor: pointer;" data-toggle="modal" data-target="#quickAddGuidelineModal">
                                <span><i class="fa fa-plus-square"></i> Add Guideline</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="well" style="max-height: 480px; overflow-y: auto; padding: 0 !important;">
                                <?php if (empty($allPostedGuidelines)) { ?>
                                    <div class="text-center text-muted" style="padding: 35px 15px;" id="noGuidelinesPostedCard">
                                        <i class="fa fa-folder-open-o" style="font-size: 32px; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>
                                        <p style="margin: 0; font-size: 13.5px; font-weight: 600; color: #475569;">No staff guidelines created yet.</p>
                                        <p style="margin: 4px 0 14px 0; font-size: 12px; color: #94a3b8;">Create guidelines here or in Master Creator, then send them to users to appear in their My Dashboard.</p>
                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#quickAddGuidelineModal" style="background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%); border: none; font-weight: 700; border-radius: 6px; padding: 6px 16px; box-shadow: 0 2px 6px rgba(13,77,139,0.25);">
                                            <i class="fa fa-plus-circle"></i> Create New Guideline
                                        </button>
                                        <a href="masterCreator.php#guidelines-section" class="btn btn-sm btn-default" style="margin-left: 8px; border-radius: 6px; font-weight: 600;">
                                            <i class="fa fa-external-link"></i> Open Master Creator
                                        </a>
                                    </div>
                                <?php } else { ?>
                                    <table class="table table-bordered table-striped" style="margin-bottom: 0; font-size: 12.5px;">
                                        <thead>
                                            <tr style="background: #f1f5f9;">
                                                <th style="width: 24%; color: #0f172a; font-weight: 700;">Guideline Title</th>
                                                <th style="width: 26%; color: #0f172a; font-weight: 700;">Description</th>
                                                <th style="width: 15%; color: #0f172a; font-weight: 700;">Attachment</th>
                                                <th style="width: 17%; color: #0f172a; font-weight: 700;">Sent To</th>
                                                <th style="width: 18%; text-align: center; color: #0f172a; font-weight: 700;">Action</th>
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
                                                        <button type="button" class="btn btn-sm btn-open-assign-modal" 
                                                                data-id="<?php echo $gl['id']; ?>" 
                                                                data-title="<?php echo htmlspecialchars($gl['title'], ENT_QUOTES); ?>"
                                                                style="background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%); color: #fff; border: none; border-radius: 6px; padding: 6px 12px; font-weight: 700; font-size: 11.5px; cursor: pointer; box-shadow: 0 2px 6px rgba(13,77,139,0.25);">
                                                            <i class="fa fa-paper-plane" style="pointer-events: none;"></i> Send Guideline to User
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-delete-guideline-au" 
                                                                data-id="<?php echo $gl['id']; ?>" 
                                                                data-title="<?php echo htmlspecialchars($gl['title'], ENT_QUOTES); ?>"
                                                                style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 6px 9px; margin-left: 4px; cursor: pointer;" title="Delete">
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

    <!-- Quick Add Guideline Modal -->
    <div class="modal fade" id="quickAddGuidelineModal" tabindex="-1" role="dialog" aria-labelledby="quickAddGuidelineModalLabel">
        <div class="modal-dialog" role="document" style="max-width: 580px;">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="panel-heading" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); color: #fff; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 15px; font-weight: 700; color: #fff;">
                        <span><i class="fa fa-plus-circle"></i></span> Create New Staff Guideline
                    </div>
                    <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.9; margin: 0; font-size: 22px;">&times;</button>
                </div>
                <form id="quickAddGuidelineForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create_guideline">
                    <input type="hidden" name="is_ajax" value="1">
                    <div class="modal-body" style="padding: 20px;">
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label style="font-size: 12px; font-weight: 700; color: #0f172a;">Guideline Subject / Title <span class="text-danger">*</span></label>
                            <input type="text" name="guideline_title" class="form-control" placeholder="e.g. Audit Directives & Procedural SOP 2026" required style="border-radius: 6px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label style="font-size: 12px; font-weight: 700; color: #0f172a;">Guideline Instructions / Description</label>
                            <textarea name="guideline_description" class="form-control" rows="4" placeholder="Enter full work guidelines, procedural steps, or compliance directives..." style="border-radius: 6px; font-size: 12.5px;"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <label style="font-size: 12px; font-weight: 700; color: #0f172a; margin: 0;">Attach Files / Documents</label>
                                <button type="button" id="btnQuickAddMoreAtt" class="btn btn-xs btn-default" style="font-size: 11px; color: #0d4d8b; font-weight: 600;">+ Add Another File</button>
                            </div>
                            <div id="quickAttContainer">
                                <div class="quick-att-row" style="margin-bottom: 6px; display: flex; gap: 6px;">
                                    <input type="file" name="guideline_files[]" class="form-control input-sm" style="font-size: 12px; border-radius: 6px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <a href="masterCreator.php#guidelines-section" style="font-size: 12px; color: #64748b; text-decoration: none;">
                            <i class="fa fa-external-link"></i> Or open in Master Creator
                        </a>
                        <div>
                            <button type="button" class="btn btn-sm btn-default" data-dismiss="modal" style="border-radius: 6px; font-weight: 600;">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary" id="btnSubmitQuickGuideline" style="background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%); border: none; font-weight: 700; border-radius: 6px; padding: 6px 18px; box-shadow: 0 2px 6px rgba(13,77,139,0.25);">
                                <i class="fa fa-check"></i> Post Guideline
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Assigning / Sending Guideline to Users -->
    <div class="modal fade" id="assignGuidelineModal" tabindex="-1" role="dialog" aria-labelledby="assignGuidelineModalLabel">
        <div class="modal-dialog" role="document" style="max-width: 620px;">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="panel-heading" style="background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%); color: #fff; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 15px; font-weight: 700; color: #fff;">
                        <span><i class="fa fa-paper-plane"></i></span> Send Guideline to Users
                    </div>
                    <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.9; margin: 0; font-size: 22px;">&times;</button>
                </div>

                <div class="modal-body" style="padding: 18px 20px;">
                    <input type="hidden" id="modalGuidelineId" value="">

                    <div style="margin-bottom: 14px; background: #f8fafc; border-left: 4px solid #0d4d8b; border: 1px solid #e2e8f0; border-left-width: 4px; border-radius: 6px; padding: 10px 14px;">
                        <small style="color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 10.5px; letter-spacing: 0.4px;">Guideline Subject:</small>
                        <div id="modalGuidelineTitle" style="font-weight: 700; color: #0f172a; font-size: 14px; margin-top: 2px;"></div>
                    </div>

                    <!-- Role Quick Filter Pills -->
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px;">
                        <button type="button" class="btn btn-xs btn-user-filter active" data-filter="all" style="border-radius: 14px; padding: 3px 12px; font-weight: 700; background: #0d4d8b; color: #fff; border: 1px solid #0d4d8b;">All (<span id="filterCountAll">0</span>)</button>
                        <button type="button" class="btn btn-xs btn-default btn-user-filter" data-filter="staff" style="border-radius: 14px; padding: 3px 12px; font-weight: 600;">Office Staff</button>
                        <button type="button" class="btn btn-xs btn-default btn-user-filter" data-filter="auditor" style="border-radius: 14px; padding: 3px 12px; font-weight: 600;">Auditors</button>
                        <button type="button" class="btn btn-xs btn-default btn-user-filter" data-filter="admin" style="border-radius: 14px; padding: 3px 12px; font-weight: 600;">Admin / Mgmt</button>
                    </div>

                    <!-- Search & Quick Selection Actions -->
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-xs-6">
                            <input type="text" id="userListSearchInput" class="form-control input-sm" placeholder="Search user name, email, or role..." style="border-radius: 6px;">
                        </div>
                        <div class="col-xs-6 text-right" style="padding-top: 5px;">
                            <a href="javascript:void(0)" id="btnSelectAllUsers" style="font-size: 11.5px; margin-right: 8px; font-weight: 700; color: #0d4d8b;"><i class="fa fa-check-square-o"></i> Select All</a>
                            <a href="javascript:void(0)" id="btnSelectAllAuditors" style="font-size: 11.5px; margin-right: 8px; font-weight: 600; color: #059669;">+ All Auditors</a>
                            <a href="javascript:void(0)" id="btnDeselectAllUsers" style="font-size: 11.5px; color: #dc2626; font-weight: 700;"><i class="fa fa-times"></i> Clear</a>
                        </div>
                    </div>

                    <div id="modalUsersListContainer" style="max-height: 290px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0; background: #fff;">
                        <div class="text-center text-muted" style="padding: 25px;" id="modalLoadingUsersSpinner">
                            <i class="fa fa-spinner fa-spin" style="font-size: 18px; color: #0d4d8b;"></i> Loading all system users...
                        </div>
                        <table class="table table-hover table-condensed table-striped" style="margin-bottom: 0; font-size: 12.5px;">
                            <tbody id="modalUsersGrid">
                                <!-- User rows injected via JS -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 10px; font-size: 12px; color: #64748b; display: flex; justify-content: space-between; align-items: center;">
                        <span id="selectedUsersCountLabel" style="font-weight: 700; color: #0d4d8b; font-size: 12.5px;">0 Selected</span>
                        <span style="color: #64748b;"><i class="fa fa-info-circle"></i> Will appear instantly in recipient's My Dashboard</span>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal" style="border-radius: 6px; font-weight: 600;">Cancel</button>
                    <button type="button" class="btn btn-sm btn-info" id="btnSubmitGuidelineAssignments" style="background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%); border: none; font-weight: 700; border-radius: 6px; padding: 7px 20px; box-shadow: 0 2px 6px rgba(13,77,139,0.3);">
                        <i class="fa fa-paper-plane"></i> Send Guideline to Selected (<span id="submitCountBadge">0</span>)
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

        /* Dynamic Header + Description Section Cards */
        .section-entry-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px 16px 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            clear: both;
            box-sizing: border-box;
            transition: all 0.15s ease;
        }
        .section-entry-card:hover {
            border-color: #cbd5e1;
            background: #ffffff;
            box-shadow: 0 3px 8px rgba(15, 23, 42, 0.06);
        }
        .section-entry-card .entry-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .section-entry-card .entry-card-title {
            font-size: 11.5px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .section-entry-card .entry-card-remove {
            color: #ef4444;
            background: transparent;
            border: none;
            padding: 2px 6px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.15s ease;
        }
        .section-entry-card .entry-card-remove:hover {
            color: #b91c1c;
            text-decoration: underline;
        }
        .custom-sections-wrapper {
            margin-top: 18px;
            margin-bottom: 20px;
            clear: both;
        }
        .custom-sections-wrapper .wrapper-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .custom-sections-wrapper .wrapper-title {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin: 0;
        }
        .custom-sections-wrapper .add-section-block-btn {
            border-radius: 6px;
            font-weight: 700;
            padding: 4px 12px;
            font-size: 11.5px;
            cursor: pointer;
        }
        .sections-card-container {
            margin-top: 10px;
            margin-bottom: 16px;
            clear: both;
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

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function createSectionCardHtml(headerVal, descVal, headerPlaceholder, descPlaceholder) {
            headerVal = headerVal || '';
            descVal = descVal || '';
            headerPlaceholder = headerPlaceholder || 'Header / Category Title (e.g. IT Support, HR Activities...)';
            descPlaceholder = descPlaceholder || 'Description / Details (one item per line)...';

            return '<div class="section-entry-card">' +
                '<div class="entry-card-header">' +
                    '<span class="entry-card-title"><i class="fa fa-bookmark" style="color: #0d4d8b;"></i> Section Header / Category</span>' +
                    '<button type="button" class="entry-card-remove remove-section-block-btn"><i class="fa fa-trash-o"></i> Remove</button>' +
                '</div>' +
                '<input type="text" class="form-control section-header-val" value="' + escapeHtml(headerVal) + '" placeholder="' + escapeHtml(headerPlaceholder) + '" style="border: 1px solid #cbd5e1 !important; border-radius: 6px !important; padding: 8px 12px !important; font-size: 13px; font-weight: 600; background: #ffffff !important; margin-bottom: 12px; width: 100%; display: block; box-sizing: border-box;">' +
                '<div style="margin-bottom: 6px;">' +
                    '<span class="entry-card-title"><i class="fa fa-align-left text-muted"></i> Description / Details (one per line)</span>' +
                '</div>' +
                '<textarea class="form-control section-desc-val" rows="3" placeholder="' + escapeHtml(descPlaceholder) + '" style="border: 1px solid #cbd5e1 !important; border-radius: 6px !important; padding: 10px 12px !important; font-size: 13px; resize: vertical; min-height: 85px; background: #ffffff !important; line-height: 1.5; margin-bottom: 6px; width: 100%; display: block; box-sizing: border-box;">' + escapeHtml(descVal) + '</textarea>' +
            '</div>';
        }

        function populateSectionContainer(containerSelector, rawData, defaultHdr, defaultDesc) {
            var $c = $(containerSelector);
            $c.empty();
            var items = [];
            if (rawData && typeof rawData === 'string' && rawData.trim() !== '') {
                try {
                    var parsed = JSON.parse(rawData);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        items = parsed;
                    }
                } catch (e) {
                    var lines = rawData.split(/\r\n|\r|\n/);
                    var currentHdr = '';
                    var currentLines = [];
                    lines.forEach(function(l) {
                        var tl = l.trim();
                        if (tl === '') return;
                        var match = tl.match(/^([A-Za-z0-9\s\&\(\)\/]+)[\-\:]$/);
                        if (match && match[1].trim().length > 3) {
                            if (currentLines.length > 0 || currentHdr !== '') {
                                items.push({ header: currentHdr, desc: currentLines.join('\n') });
                                currentLines = [];
                            }
                            currentHdr = match[1].trim();
                        } else {
                            currentLines.push(tl.replace(/^\s*\d+[\.\)\-]\s*/, ''));
                        }
                    });
                    if (currentLines.length > 0 || currentHdr !== '') {
                        items.push({ header: currentHdr, desc: currentLines.join('\n') });
                    }
                }
            }

            if (items.length === 0) {
                items = [{ header: '', desc: '' }];
            }

            items.forEach(function(item) {
                $c.append(createSectionCardHtml(item.header || '', item.desc || '', defaultHdr, defaultDesc));
            });
        }

        function serializeSectionContainer(containerSelector) {
            var items = [];
            $(containerSelector).find('.section-entry-card').each(function() {
                var hdr = $.trim($(this).find('.section-header-val').val());
                var desc = $.trim($(this).find('.section-desc-val').val());
                if (hdr !== '' || desc !== '') {
                    items.push({ header: hdr, desc: desc });
                }
            });
            return items.length > 0 ? JSON.stringify(items) : '';
        }

        function serializeAllAddSections() {
            $('#applicableRecords').val(serializeSectionContainer('#applicableRecordsContainer'));
            $('#workDescription').val(serializeSectionContainer('#workDescriptionContainer'));
            $('#resourceIssueRecord').val(serializeSectionContainer('#resourceIssueRecordContainer'));
        }

        function serializeAllEditSections() {
            $('#editApplicableRecords').val(serializeSectionContainer('#editApplicableRecordsContainer'));
            $('#editWorkDescription').val(serializeSectionContainer('#editWorkDescriptionContainer'));
            $('#editResourceIssueRecord').val(serializeSectionContainer('#editResourceIssueRecordContainer'));
        }

        $(document).ready(function () {
            // Move modals to body to prevent stacking context click blocking
            $('#assignGuidelineModal').appendTo('body');
            $('#delete-details').appendTo('body');

            // Initialize dynamic section containers for Add User
            populateSectionContainer('#applicableRecordsContainer', '', 'e.g. Codebase Access, Hosting, Email Accounts...', 'Enter applicable records and documents with its locations (one per line)...');
            populateSectionContainer('#workDescriptionContainer', '', 'e.g. IT & Software Development, HR Activities...', 'Enter responsibilities, authorities and reporting items (one per line)...');
            populateSectionContainer('#resourceIssueRecordContainer', '', 'e.g. Hardware Issues, Access Permissions...', 'Enter resource issue records (one per line)...');

            $(document).on('click', '.add-section-block-btn', function(e) {
                e.preventDefault();
                var target = $(this).data('container');
                var hPlaceholder = 'Header / Category Title (e.g. IT Support, HR Activities...)';
                var dPlaceholder = 'Enter details / points (one per line)...';
                if (target.indexOf('Applicable') !== -1) {
                    hPlaceholder = 'e.g. Codebase Access, Hosting, Email Accounts...';
                    dPlaceholder = 'Enter applicable records and documents with its locations (one per line)...';
                } else if (target.indexOf('Work') !== -1) {
                    hPlaceholder = 'e.g. IT & Software Development, HR Activities...';
                    dPlaceholder = 'Enter responsibilities, authorities and reporting items (one per line)...';
                } else if (target.indexOf('Resource') !== -1) {
                    hPlaceholder = 'e.g. Hardware Issues, Access Permissions...';
                    dPlaceholder = 'Enter resource issue records (one per line)...';
                }
                $(target).append(createSectionCardHtml('', '', hPlaceholder, dPlaceholder));
            });

            $(document).on('click', '.remove-section-block-btn', function(e) {
                e.preventDefault();
                var $card = $(this).closest('.section-entry-card');
                var $container = $card.parent();
                if ($container.find('.section-entry-card').length > 1) {
                    $card.remove();
                } else {
                    $card.find('.section-header-val').val('');
                    $card.find('.section-desc-val').val('');
                }
            });

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
                serializeAllAddSections();
                if (validateUser()) {
                    e.preventDefault();
                    return;
                }
            });

            $(document).on('click', '.updateUser', function (e) {
                serializeAllEditSections();
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
                        $('.editUserEid').val(data['e_id'] || '');
                        $('.editUserPcId').val(data['pc_id'] || '');
                        $('.editDesignationAssigned').val(data['designation_assigned'] || '');
                        
                        populateSectionContainer('#editApplicableRecordsContainer', data['applicable_records'] || '', 'e.g. Codebase Access, Hosting, Email Accounts...', 'Enter applicable records and documents with its locations (one per line)...');
                        populateSectionContainer('#editWorkDescriptionContainer', data['work_description'] || '', 'e.g. IT & Software Development, HR Activities...', 'Enter responsibilities, authorities and reporting items (one per line)...');
                        populateSectionContainer('#editResourceIssueRecordContainer', data['resource_issue_record'] || '', 'e.g. Hardware Issues, Access Permissions...', 'Enter resource issue records (one per line)...');

                        $('.userId').val(data['user_id']);
                        $('.addUserSection, .addWorkDescWrapper').hide();
                        $('.updateUserDiv, .editWorkDescWrapper').slideDown(200);
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
                $('.editUserEid').val('');
                $('.editUserPcId').val('');
                $('.editDesignationAssigned').val('');
                populateSectionContainer('#editApplicableRecordsContainer', '');
                populateSectionContainer('#editWorkDescriptionContainer', '');
                populateSectionContainer('#editResourceIssueRecordContainer', '');
                $('.updateUserDiv, .editWorkDescWrapper').slideUp(200, function() {
                    $('.addUserSection, .addWorkDescWrapper').fadeIn(200);
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
                $('#submitCountBadge').text(checkedCount);
            }

            // Quick Add Guideline: Add More Attachment Row
            $('#btnQuickAddMoreAtt').on('click', function(e) {
                e.preventDefault();
                var newRow = $(
                    '<div class="quick-att-row" style="margin-bottom: 6px; display: flex; gap: 6px;">' +
                        '<input type="file" name="guideline_files[]" class="form-control input-sm" style="font-size: 12px; border-radius: 6px;">' +
                        '<button type="button" class="btn btn-xs btn-danger btn-remove-quick-att" style="border-radius: 6px; padding: 4px 8px;" title="Remove file"><i class="fa fa-times"></i></button>' +
                    '</div>'
                );
                $('#quickAttContainer').append(newRow);
            });

            $(document).on('click', '.btn-remove-quick-att', function(e) {
                e.preventDefault();
                $(this).closest('.quick-att-row').remove();
            });

            // Quick Add Guideline Form Submit
            $('#quickAddGuidelineForm').on('submit', function(e) {
                e.preventDefault();
                var $btn = $('#btnSubmitQuickGuideline');
                var origHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Posting...');

                var formData = new FormData(this);

                $.ajax({
                    url: 'guidelines_action.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(resp) {
                        $btn.prop('disabled', false).html(origHtml);
                        if (resp.success) {
                            $('#quickAddGuidelineModal').modal('hide');
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Guideline Created!',
                                    text: resp.message,
                                    icon: 'success',
                                    confirmButtonColor: '#0d4d8b'
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                alert(resp.message);
                                location.reload();
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Error', resp.message || 'Failed to create guideline', 'error');
                            } else {
                                alert(resp.message || 'Failed to create guideline');
                            }
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html(origHtml);
                        alert('Server communication error. Please try again.');
                    }
                });
            });

            // Open Assign Modal
            $(document).on('click', '.btn-open-assign-modal', function(e) {
                e.preventDefault();
                var gid = $(this).data('id');
                var gtitle = $(this).data('title');

                $('#modalGuidelineId').val(gid);
                $('#modalGuidelineTitle').text(gtitle || ('Staff Guideline #' + gid));
                $('#userListSearchInput').val('');
                $('.btn-user-filter').removeClass('active').css({'background': '', 'color': '', 'border-color': ''});
                $('.btn-user-filter[data-filter="all"]').addClass('active').css({'background': '#0d4d8b', 'color': '#fff', 'border-color': '#0d4d8b'});
                $('#modalLoadingUsersSpinner').show();
                $('#modalUsersGrid').empty();
                $('#selectedUsersCountLabel').text('0 Selected');
                $('#submitCountBadge').text('0');
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
                            $('#filterCountAll').text(resp.all_users.length);
                            var assignedKeys = resp.assigned_keys || [];
                            var gridHtml = '';

                            $.each(resp.all_users, function(idx, u) {
                                var isChecked = assignedKeys.indexOf(u.key) !== -1 ? 'checked' : '';
                                var roleText = u.role || 'Staff';
                                var roleLower = roleText.toLowerCase();
                                var categoryType = 'staff';
                                var badgeStyle = 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;';

                                if (roleLower.indexOf('audit') !== -1 || u.user_type === 'auditor') {
                                    categoryType = 'auditor';
                                    badgeStyle = 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;';
                                } else if (roleLower.indexOf('admin') !== -1 || roleLower.indexOf('mgmt') !== -1 || roleLower.indexOf('director') !== -1) {
                                    categoryType = 'admin';
                                    badgeStyle = 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;';
                                }

                                var searchStr = (u.name + ' ' + roleText + ' ' + (u.email || '')).toLowerCase();
                                
                                // Initials
                                var cleanN = (u.name || '').replace(/[^a-zA-Z0-9\s]/g, '').trim();
                                var parts = cleanN.split(/\s+/);
                                var initials = parts.length >= 2 ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase() : (cleanN.substring(0, 2).toUpperCase() || 'U');

                                gridHtml += '<tr class="user-select-row" data-category="' + categoryType + '" data-search="' + searchStr + '" style="cursor: pointer;">' +
                                                '<td style="width: 36px; text-align: center; vertical-align: middle;">' +
                                                    '<input type="checkbox" class="user-assign-checkbox" value="' + u.key + '" ' + isChecked + ' ' +
                                                           'data-name="' + $('<div>').text(u.name).html() + '" data-role="' + $('<div>').text(roleText).html() + '" ' +
                                                           'data-category="' + categoryType + '" style="cursor: pointer; width: 16px; height: 16px; margin: 0;">' +
                                                '</td>' +
                                                '<td style="vertical-align: middle;">' +
                                                    '<div style="display: flex; align-items: center; gap: 9px;">' +
                                                        '<div style="width: 28px; height: 28px; border-radius: 50%; background: #f1f5f9; color: #0d4d8b; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; border: 1px solid #cbd5e1; flex-shrink: 0;">' +
                                                            initials +
                                                        '</div>' +
                                                        '<div>' +
                                                            '<strong style="color: #0f172a; font-size: 13px;">' + $('<div>').text(u.name).html() + '</strong>' +
                                                            (u.email ? '<small class="text-muted" style="display: block; font-size: 11px;">' + $('<div>').text(u.email).html() + '</small>' : '') +
                                                        '</div>' +
                                                    '</div>' +
                                                '</td>' +
                                                '<td style="vertical-align: middle; text-align: right; width: 130px;">' +
                                                    '<span class="label" style="font-size: 11px; padding: 3px 8px; border-radius: 4px; ' + badgeStyle + '">' + $('<div>').text(roleText).html() + '</span>' +
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

            // Filter Pills in Modal
            $(document).on('click', '.btn-user-filter', function(e) {
                e.preventDefault();
                $('.btn-user-filter').removeClass('active').css({'background': '', 'color': '', 'border-color': ''});
                $(this).addClass('active').css({'background': '#0d4d8b', 'color': '#fff', 'border-color': '#0d4d8b'});
                var filter = $(this).data('filter');
                filterModalUsers();
            });

            // Filter Users in Modal (Search text)
            $(document).on('keyup input', '#userListSearchInput', function() {
                filterModalUsers();
            });

            function filterModalUsers() {
                var q = $.trim($('#userListSearchInput').val()).toLowerCase();
                var activeFilter = $('.btn-user-filter.active').data('filter') || 'all';

                $('.user-select-row').each(function() {
                    var $row = $(this);
                    var searchTxt = $row.data('search') || '';
                    var category = $row.data('category') || 'staff';

                    var matchesSearch = (q === '' || searchTxt.indexOf(q) !== -1);
                    var matchesFilter = (activeFilter === 'all' || category === activeFilter);

                    if (matchesSearch && matchesFilter) {
                        $row.show();
                    } else {
                        $row.hide();
                    }
                });
            }

            // Select All visible
            $(document).on('click', '#btnSelectAllUsers', function(e) {
                e.preventDefault();
                $('.user-select-row:visible .user-assign-checkbox').prop('checked', true);
                updateSelectedCount();
            });

            // Select All Auditors
            $(document).on('click', '#btnSelectAllAuditors', function(e) {
                e.preventDefault();
                $('.user-select-row[data-category="auditor"]:visible .user-assign-checkbox').prop('checked', true);
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
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending Guideline...');

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
                                    title: 'Guideline Sent Successfully!',
                                    text: resp.message + ' The selected users can now access this directive in their My Dashboard under Work Guidelines.',
                                    icon: 'success',
                                    confirmButtonColor: '#0d4d8b'
                                });
                            } else {
                                alert(resp.message);
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Error', resp.message || 'Failed to send guideline.', 'error');
                            } else {
                                alert(resp.message || 'Failed to send guideline.');
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

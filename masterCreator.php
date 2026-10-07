<div id="wrapper" class="active">
    <?php

    $pageIdentifier = "Master Data";
    include_once './header.php';
    include_once './dbConnect.php';
    include_once './include/customer_feature_flags.php';
    require_once __DIR__ . '/include/auditor_portal_helper.php';
    require_once __DIR__ . '/include/auditor_division_helper.php';
    require_once __DIR__ . '/include/rbac_helper.php';
    require_once __DIR__ . '/include/staff_guidelines_lib.php';
    date_default_timezone_set('Asia/Kolkata');

    if (!Utitlity::checkForDirector()) {
        header("Location: login.php?reason=login_required");
        exit;
    }

    $featureToggleMessage = '';
    aitf_ensure_auditor_portal_schema($db);
    aitf_ensure_auditor_division_schema($db);
    $auditorKeyMessage = trim((string)($_SESSION['auditor_key_notice'] ?? ''));
    unset($_SESSION['auditor_key_notice']);
    $canManageCustomerFeatureToggles = Utitlity::checkForUserMainAdmin();
    if ($canManageCustomerFeatureToggles && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_customer_feature_toggles') {
        aitfCustomerFeatureSet($db, 'customer_docora_ai_visible', isset($_POST['customer_docora_ai_visible']), isset($_SESSION['username']) ? $_SESSION['username'] : '');
        aitfCustomerFeatureSet($db, 'customer_date_graph_visible', isset($_POST['customer_date_graph_visible']), isset($_SESSION['username']) ? $_SESSION['username'] : '');
        aitfCustomerFeatureSet($db, 'customer_letters_edit_icon_visible', isset($_POST['customer_letters_edit_icon_visible']), isset($_SESSION['username']) ? $_SESSION['username'] : '');
        aitfCustomerFeatureSet($db, 'customer_letters_delete_icon_visible', isset($_POST['customer_letters_delete_icon_visible']), isset($_SESSION['username']) ? $_SESSION['username'] : '');
        aitfCustomerFeatureSet($db, 'audit_date_conflict_visible', isset($_POST['audit_date_conflict_visible']), isset($_SESSION['username']) ? $_SESSION['username'] : '');
        aitfCustomerFeatureSet($db, 'audit_member_conflict_visible', isset($_POST['audit_member_conflict_visible']), isset($_SESSION['username']) ? $_SESSION['username'] : '');
        aitfCustomerFeatureSet($db, 'customer_manual_status_change_enabled', isset($_POST['customer_manual_status_change_enabled']), isset($_SESSION['username']) ? $_SESSION['username'] : '');
        aitfCustomerFeatureSet($db, 'customer_accreditation_change_enabled', isset($_POST['customer_accreditation_change_enabled']), isset($_SESSION['username']) ? $_SESSION['username'] : '');
        aitfCustomerFeatureSet($db, 'full_invoice_access_enabled', isset($_POST['full_invoice_access_enabled']), isset($_SESSION['username']) ? $_SESSION['username'] : '');
        $featureToggleMessage = 'Client features control buttons.';
    }

    // AI Review Rules handling (docoraAI now GoAI)
    include_once './include/ai_document_review_lib.php';
    aitfAiEnsureSchema($db);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_ai_rule') {
        $aiRuleId = (int) $_POST['ai_rule_id'];
        $aiActive = (int) $_POST['ai_active'];
        $stmt = $db->prepare("UPDATE ai_review_rules SET is_active = :active, updated_by = :updated_by WHERE id = :id");
        $stmt->execute(array(
            ':active' => $aiActive,
            ':updated_by' => isset($_SESSION['username']) ? $_SESSION['username'] : '',
            ':id' => $aiRuleId
        ));
    }

    $customerDocoraAiVisible = aitfCustomerFeatureIsEnabled($db, 'customer_docora_ai_visible');
    $customerDateGraphVisible = aitfCustomerFeatureIsEnabled($db, 'customer_date_graph_visible');
    $customerLettersEditIconVisible = aitfCustomerFeatureIsEnabled($db, 'customer_letters_edit_icon_visible');
    $customerLettersDeleteIconVisible = aitfCustomerFeatureIsEnabled($db, 'customer_letters_delete_icon_visible');
    $auditDateConflictVisible = aitfCustomerFeatureIsEnabled($db, 'audit_date_conflict_visible');
    $auditMemberConflictVisible = aitfCustomerFeatureIsEnabled($db, 'audit_member_conflict_visible');
    $customerManualStatusChangeEnabled = aitfCustomerFeatureIsEnabled($db, 'customer_manual_status_change_enabled');
    $customerAccreditationChangeEnabled = aitfCustomerFeatureIsEnabled($db, 'customer_accreditation_change_enabled');
    $fullInvoiceAccessEnabled = aitfCustomerFeatureIsEnabled($db, 'full_invoice_access_enabled');

    //    auditors
    $auditors = "SELECT * from auditors";
    $statement = $db->prepare($auditors);
    $statement->execute();
    $auditorsList = $statement->fetchAll();
    $auditorDivisionsMap = aitf_get_auditor_divisions_map($db);
    $auditorNaceClassesMap = aitf_get_auditor_nace_classes_map($db);

    //  Taxation
    $taxations = "SELECT * from taxation";
    $statement = $db->prepare($taxations);
    $statement->execute();
    $taxationList = $statement->fetchAll();

    //  Consulting firm
    $consultingFirm = "SELECT * FROM consulting_firm";
    $statement = $db->prepare($consultingFirm);
    $statement->execute();
    $firmDetails = $statement->fetch();

    //  Standards
    $standards = "SELECT * FROM standards";
    $statement = $db->prepare($standards);
    $statement->execute();
    $standardsList = $statement->fetchAll();

    //  accreditation
    $accreditation = "SELECT * FROM accreditation";
    $statement = $db->prepare($accreditation);
    $statement->execute();
    $accreditationList = $statement->fetchAll();

    $categories = "SELECT * FROM categories";
    $statement = $db->prepare($categories);
    $statement->execute();
    $categoriesList = $statement->fetchAll();

    //  legal ststus
    $legalStatus = "SELECT * FROM legal_ststus";
    $statement = $db->prepare($legalStatus);
    $statement->execute();
    $legalStatusList = $statement->fetchAll();

    //  nace code
    $naceCodes = "SELECT * FROM nace_codes";
    $statement = $db->prepare($naceCodes);
    $statement->execute();
    $naceCodeList = $statement->fetchAll();

    //  IAF code
    $iafCodes = "SELECT * FROM iaf_codes";
    $statement = $db->prepare($iafCodes);
    $statement->execute();
    $iafList = $statement->fetchAll();

    $customerStatus = "SELECT * FROM customer_status";
    $statement = $db->prepare($customerStatus);
    $statement->execute();
    $status = $statement->fetchAll();

    $addTos = "SELECT * FROM service_add_to";
    $statement = $db->prepare($addTos);
    $statement->execute();
    $addto = $statement->fetchAll();

    $handlers = "SELECT * from handler";
    $statement = $db->prepare($handlers);
    $statement->execute();
    $handlersList = $statement->fetchAll();

    // Staff Guidelines Data
    $allMasterGuidelines = aitf_get_all_guidelines_with_details($db);
    ?>
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid master-creator-container" style="max-width: 1540px; margin: 0 auto; padding: 14px 20px 60px 20px;">
                
                <!-- Compact Master Creator Header -->
                <div class="mc-hero-header">
                    <div class="mc-hero-left">
                        <h1 class="mc-hero-title"><i class="fa fa-database" style="color: #0d4d8b; font-size: 16px; margin-right: 8px;"></i> masterCreator</h1>
                    </div>
                    <div class="mc-hero-stats">
                        <div class="mc-stat-pill">
                            <span class="mc-stat-icon icon-blue"><i class="fa fa-user-secret"></i></span>
                            <div>
                                <div class="mc-stat-num"><?php echo count($auditorsList); ?></div>
                                <div class="mc-stat-lbl">Auditors</div>
                            </div>
                        </div>
                        <div class="mc-stat-pill">
                            <span class="mc-stat-icon icon-green"><i class="fa fa-certificate"></i></span>
                            <div>
                                <div class="mc-stat-num"><?php echo count($standardsList); ?></div>
                                <div class="mc-stat-lbl">Standards</div>
                            </div>
                        </div>
                        <div class="mc-stat-pill">
                            <span class="mc-stat-icon icon-purple"><i class="fa fa-shield"></i></span>
                            <div>
                                <div class="mc-stat-num"><?php echo count($accreditationList); ?></div>
                                <div class="mc-stat-lbl">Accreditations</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php if ($auditorKeyMessage !== '') { ?>
                        <div class="col-sm-12">
                            <div class="alert alert-info" style="border-radius: 10px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <?php echo htmlspecialchars($auditorKeyMessage, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-12  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">AITF MCL</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateTaxation">
                                    <?php //foreach ($firmDetails as $firm) { ?>
                                    <li class="list-group-item">
                                        <a class="editFirm" data-id="<?php echo $firmDetails['firm_id'] ?>"
                                            style="float: left" title="Edit AITF MCL"><i
                                                class="fa fa-pencil-square"></i></a>&emsp;<?php echo $firmDetails['firm_name'] ?>
                                        <a class="deleteFirm" data-id="<?php echo $firmDetails['firm_id'] ?>"
                                            style="float: right" title="Delete AITF MCL"><i class="fa fa-trash"></i></a>
                                    </li>
                                    <?php // } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newTaxation"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-8  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Present Taxation setting for releasing invoices</i></span>
                            </div>
                            <div class="col-xs-4  text-center btn qms_complete_btn addNewTaxation">
                                <span><i class="fa fa-plus-square "> Add Tax</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateTaxation">
                                    <?php foreach ($taxationList as $taxation) { ?>
                                        <li class="list-group-item">
                                            <a class="editTaxation"
                                                edit-Taxation-id="<?php echo $taxation['taxation_id'] ?>"
                                                style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;
                                            <span><b>Type of tax: </b> <?php echo $taxation['type_of_tax']; ?> </span>
                                            &emsp;<span><b>Rate: </b> <?php
                                            echo $taxation['rate'];
                                            echo '%';
                                            ?>
                                            </span><a class="deleteTaxation"
                                                taxation-id="<?php echo $taxation['taxation_id'] ?>"
                                                title="Delete taxcation" style="float: right"><i
                                                    class="fa fa-trash"></i></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newTaxation"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-5  text-center btn  qms_complete_btn ">
                                <span><i class="fa fa-users"></i> Auditors</span>
                            </div>
                            <div class="col-xs-3  text-center btn qms_complete_btn addNewAuditors" style="padding-left:2px; padding-right:2px;">
                                <span><i class="fa fa-plus-square"></i> Auditor</span>
                            </div>
                            <div class="col-xs-4  text-center btn qms_complete_btn addNewReviewer" style="background: linear-gradient(135deg, #059669, #10b981); border-color: #059669; color: #fff; padding-left:2px; padding-right:2px;">
                                <span><i class="fa fa-user-plus"></i> Add Reviewer</span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateAuditors">
                                    <?php foreach ($auditorsList as $auditor) { 
                                        $audId = (int)$auditor['auditors_id'];
                                        $assignedClasses = isset($auditorNaceClassesMap[$audId]) ? $auditorNaceClassesMap[$audId] : array();
                                        $assignedDivs = isset($auditorDivisionsMap[$audId]) ? $auditorDivisionsMap[$audId] : array();
                                    ?>
                                        <li class="list-group-item" data-auditor-id="<?php echo $audId; ?>">
                                            <a class="editAuditor" data-id="<?php echo $audId; ?>"
                                                style="float: left"><i
                                                    class="fa fa-pencil-square"></i></a>&emsp;<?php echo $auditor['auditor_name'] ?>
                                            <?php if (!empty($assignedClasses)) { ?>
                                                <span class="label label-info auditor-division-tag" style="font-size: 10px; background-color: #2563eb; margin-left: 6px; padding: 2px 6px; border-radius: 3px;" title="Assigned NACE Classes: <?php echo htmlspecialchars(implode(', ', $assignedClasses)); ?>"><i class="fa fa-tags"></i> Class: <?php echo htmlspecialchars(implode(', ', $assignedClasses)); ?></span>
                                            <?php } elseif (!empty($assignedDivs)) { ?>
                                                <span class="label label-info auditor-division-tag" style="font-size: 10px; background-color: #0284c7; margin-left: 6px; padding: 2px 6px; border-radius: 3px;" title="Assigned Divisions: <?php echo htmlspecialchars(implode(', ', $assignedDivs)); ?>"><i class="fa fa-tags"></i> Div: <?php echo htmlspecialchars(implode(', ', $assignedDivs)); ?></span>
                                            <?php } else { ?>
                                                <span class="label label-default auditor-division-tag" style="font-size: 10px; color: #94a3b8; background-color: #f1f5f9; border: 1px dashed #cbd5e1; margin-left: 6px; padding: 2px 6px; border-radius: 3px;" title="No NACE class assigned"><i class="fa fa-tags"></i> No Class</span>
                                            <?php } ?>
                                            <a class="deleteAuditor" data-id="<?php echo $audId; ?>"
                                                style="float: right"><i class="fa fa-trash"></i></a>
                                            <button type="button"
                                                class="btn btn-xs btn-primary generateAuditorKey"
                                                data-id="<?php echo (int)$auditor['auditors_id']; ?>"
                                                data-name="<?php echo htmlspecialchars($auditor['auditor_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-email="<?php echo htmlspecialchars(!empty($auditor['login_email']) ? $auditor['login_email'] : $auditor['auditor_email'], ENT_QUOTES, 'UTF-8'); ?>"
                                                title="Generate auditor login key"
                                                style="float: right; margin-right: 8px;">
                                                <i class="fa fa-key"></i> Generate Key
                                            </button>
                                            <?php if (!empty($auditor['is_reviewer'])) { ?>
                                                <button type="button"
                                                    class="btn btn-xs btn-success toggleReviewerBtn"
                                                    data-id="<?php echo (int)$auditor['auditors_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($auditor['auditor_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-status="1"
                                                    title="Designated as Reviewer. Click to toggle role"
                                                    style="float: right; margin-right: 8px;">
                                                    <i class="fa fa-check-circle"></i> Reviewer
                                                </button>
                                            <?php } else { ?>
                                                <button type="button"
                                                    class="btn btn-xs btn-default toggleReviewerBtn"
                                                    data-id="<?php echo (int)$auditor['auditors_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($auditor['auditor_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-status="0"
                                                    title="Click to assign as Reviewer"
                                                    style="float: right; margin-right: 8px; border: 1px dashed #10b981; color: #047857;">
                                                    <i class="fa fa-plus"></i> Add Reviewer
                                                </button>
                                            <?php } ?>
                                            <?php if (!empty($auditor['login_enabled'])) { ?>
                                                <span class="label label-success" style="float:right; margin-right:8px;">Portal Enabled</span>
                                            <?php } ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newAuditors"></div>
                    </div>

                    <!-- Modal to Add / Assign Reviewer from Auditors -->
                    <div class="modal fade" id="addReviewerModal" tabindex="-1" role="dialog" aria-labelledby="addReviewerModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content" style="border-radius: 10px; overflow: hidden;">
                                <div class="modal-header" style="background: linear-gradient(135deg, #059669, #10b981); color: #fff; padding: 16px 20px;">
                                    <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.9;"><span>&times;</span></button>
                                    <h4 class="modal-title" id="addReviewerModalLabel" style="font-weight: 600;"><i class="fa fa-user-plus"></i> Add / Assign Reviewer</h4>
                                </div>
                                <div class="modal-body" style="padding: 20px;">
                                    <p style="color: #475569; font-size: 13.5px; margin-bottom: 15px;">
                                        Select an auditor below to designate them as a <strong>Reviewer</strong>. The auditor will also remain an auditor, and their name will appear in the <strong>Review Application</strong> dropdown.
                                    </p>
                                    <div class="form-group">
                                        <label style="font-weight: 600; color: #1e293b;">Select Auditor <span style="color: #dc2626;">*</span></label>
                                        <select class="form-control selectAuditorForReviewer" id="selectAuditorForReviewer" style="border-radius: 6px; height: 42px; font-size: 14px;">
                                            <option value="">-- Choose Auditor --</option>
                                            <?php foreach ($auditorsList as $aud) { ?>
                                                <option value="<?php echo (int)$aud['auditors_id']; ?>" data-is-reviewer="<?php echo !empty($aud['is_reviewer']) ? 1 : 0; ?>">
                                                    <?php echo htmlspecialchars($aud['auditor_name'], ENT_QUOTES, 'UTF-8') . (!empty($aud['is_reviewer']) ? ' [Currently Reviewer]' : ''); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 14px 20px;">
                                    <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                                    <button type="button" class="btn btn-success" id="confirmAssignReviewerBtn" style="border-radius: 6px; background: #059669; border-color: #059669; font-weight: 600;">
                                        <i class="fa fa-check"></i> Set as Reviewer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-8  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Standards</i></span>
                            </div>
                            <div class="col-xs-4  text-center btn qms_complete_btn addNewStandards">
                                <span><i class="fa fa-plus-square "> Add Standards</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateStandards">
                                    <?php foreach ($standardsList as $standards) { ?>
                                        <li class="list-group-item">
                                            <a class="editStandards" data-id="<?php echo $standards['id'] ?>"
                                                style="float: left"><i
                                                    class="fa fa-pencil-square"></i></a>&emsp;<?php echo $standards['standards_name'] ?>
                                            <a class="deleteStandards" data-id="<?php echo $standards['id'] ?>"
                                                style="float: right"><i class="fa fa-trash"></i></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newStandards"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-8  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Accreditation</i></span>
                            </div>
                            <div class="col-xs-4  text-center btn qms_complete_btn addNewAccreditation">
                                <span><i class="fa fa-plus-square "> Add Accreditation</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateAccreditation">
                                    <?php foreach ($accreditationList as $accreditation) { ?>
                                        <li class="list-group-item">
                                            <a class="editAccreditation"
                                                data-id="<?php echo $accreditation['accreditation_id'] ?>"
                                                style="float: left"><i
                                                    class="fa fa-pencil-square"></i></a>&emsp;<?php echo $accreditation['accreditation_name'] ?>
                                            <a class="deleteAccreditation"
                                                data-id="<?php echo $accreditation['accreditation_id'] ?>"
                                                style="float: right"><i class="fa fa-trash"></i></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newAccreditation"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-8  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Legal Status</i></span>
                            </div>
                            <div class="col-xs-4  text-center btn qms_complete_btn addNewLegalStatus">
                                <span><i class="fa fa-plus-square "> Add Legal Status</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateLegalStatus">
                                    <?php foreach ($legalStatusList as $legalStatus) { ?>
                                        <li class="list-group-item">
                                            <a class="editLegalStatus" data-id="<?php echo $legalStatus['status_id'] ?>"
                                                style="float: left"><i
                                                    class="fa fa-pencil-square"></i></a>&emsp;<?php echo $legalStatus['status'] ?>
                                            <a class="deleteLegalStatus" data-id="<?php echo $legalStatus['status_id'] ?>"
                                                style="float: right"><i class="fa fa-trash"></i></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newLegalStatus"></div>
                    </div>

                </div>

                <div class="row">
                    <!--                    <div class="col-sm-6">
                                            <div class="row" style="">
                                                <div class="col-xs-8  text-center btn  qms_complete_btn ">
                                                    <span><i class="fa ">NACE Code</i></span>
                                                </div>
                                                <div class="col-xs-4  text-center btn qms_complete_btn addNewNace">
                                                    <span><i class="fa fa-plus-square "> Add NACE Code</i></span>
                                                </div>
                                            </div>
                                            <div class="row" style="">
                                                <div class="well" style="max-height: 200px; overflow: auto;">
                                                    <ul class="list-group checked-list-box operateNace">
                    <?php foreach ($naceCodeList as $naceCode) { ?>
                                                                    <li class="list-group-item">
                                                                        <a class="editNace" data-id="<?php echo $naceCode['nace_id'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $naceCode['nace_code'] ?>
                                                                        <a class="deleteNace" data-id="<?php echo $naceCode['nace_id'] ?>" style="float: right"><i class="fa fa-trash"></i></a>
                                                                    </li>
                    <?php } ?>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="row newNace"></div>
                                        </div>-->
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-8  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Division</i></span>
                            </div>
                            <div class="col-xs-4  text-center btn qms_complete_btn addNewIaf">
                                <span><i class="fa fa-plus-square "> Add Division/Group</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateIaf">
                                    <?php foreach ($iafList as $iafCode) { ?>
                                        <li class="list-group-item">
                                            <a class="editIaf" data-id="<?php echo $iafCode['iaf_id'] ?>"
                                                style="float: left"><i
                                                    class="fa fa-pencil-square"></i></a>&emsp;<?php echo $iafCode['iaf_code'] ?>
                                            <a class="deleteIaf" data-id="<?php echo $iafCode['iaf_id'] ?>"
                                                style="float: right"><i class="fa fa-trash"></i></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newIaf"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-12  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Nace Classes</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">

                                <ul class="list-group checked-list-box operateNace">
                                    <?php foreach ($naceCodeList as $naceCode) { ?>
                                        <li class="list-group-item">
                                            <a class="editNaceClass" data-id="<?php echo $naceCode['iaf_id'] ?>"
                                                data-id1="<?php echo $naceCode['nace_id'] ?>"
                                                data-id2="<?php echo $naceCode['nace_code'] ?>" style="float: left"><i
                                                    class="fa fa-pencil-square"></i></a>&emsp;<?php echo $naceCode['nace_code'] ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newNace"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-8  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Status</i></span>
                            </div>
                            <div class="col-xs-4  text-center btn qms_complete_btn addNewStatus">
                                <span><i class="fa fa-plus-square "> Add Status</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateStatus">
                                    <?php foreach ($status as $stat) { ?>
                                        <li class="list-group-item">
                                            <a class="editStatus" data-id="<?php echo $stat['id'] ?>" style="float: left"><i
                                                    class="fa fa-pencil-square"></i></a>&emsp;<?php echo $stat['status'] ?>
                                            <a class="deleteStatus" data-id="<?php echo $stat['id'] ?>"
                                                style="float: right"><i class="fa fa-trash"></i></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newStatus"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-8  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Categories</i></span>
                            </div>
                            <div class="col-xs-4  text-center btn qms_complete_btn addNewcategories">
                                <span><i class="fa fa-plus-square "> Add categories</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operatecategories">
                                    <?php foreach ($categoriesList as $categories) { ?>
                                        <li class="list-group-item">
                                            <a class="editcategories" data-id="<?php echo $categories['categories_id'] ?>"
                                                style="float: left"><i
                                                    class="fa fa-pencil-square"></i></a>&emsp;<?php echo $categories['categories_name'] ?>
                                            <a class="deletecategories" data-id="<?php echo $categories['categories_id'] ?>"
                                                style="float: right"><i class="fa fa-trash"></i></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newcategories"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-8  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Add To</i></span>
                            </div>
                            <div class="col-xs-4  text-center btn qms_complete_btn addNewAddto">
                                <span><i class="fa fa-plus-square "> Add Add-to</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateAddto">
                                    <?php foreach ($addto as $tos) { ?>
                                        <li class="list-group-item">
                                            <a class="editAddtos" data-id="<?php echo $tos['add_to_id'] ?>"
                                                style="float: left"><i
                                                    class="fa fa-pencil-square"></i></a>&emsp;<?php echo $tos['add_to_type'] ?>
                                            <a class="deleteAddto" data-id="<?php echo $tos['add_to_id'] ?>"
                                                style="float: right"><i class="fa fa-trash"></i></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newAddto"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-8  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Handler</i></span>
                            </div>
                            <div class="col-xs-4  text-center btn qms_complete_btn addNewHandler">
                                <span><i class="fa fa-plus-square ">Add Handler</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateHandlers">
                                    <?php foreach ($handlersList as $hndl) { ?>
                                        <li class="list-group-item">
                                            <a class="editHandlers" data-id="<?php echo $hndl['id'] ?>"
                                                style="float: left"><i
                                                    class="fa fa-pencil-square"></i></a>&emsp;<?php echo $hndl['handler_name'] ?>
                                            <a class="deleteHandlers" data-id="<?php echo $hndl['id'] ?>"
                                                style="float: right"><i class="fa fa-trash"></i></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newHandler"></div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row" style="">
                            <div class="col-xs-12  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Certificate No.</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateCertNo">
                                    <li class="list-group-item">
                                        <a class="editCertificationNo" data-id="<?php echo $firmDetails['firm_id'] ?>"
                                            data-val="<?php echo $firmDetails['certification_no'] ?>"
                                            style="float: left"><i
                                                class="fa fa-pencil-square"></i></a>&emsp;<?php echo $firmDetails['certification_no'] ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row changeCertification"></div>
                    </div>
                </div>
                <!-- Customer Feature Toggles Section -->
                <?php if ($canManageCustomerFeatureToggles) { ?>
                    <div class="row" style="margin-top: 30px;">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-xs-12 text-center btn qms_complete_btn" id="master-toggle-btn" style="cursor: pointer;">
                                    <span><i class="fa fa-toggle-on" id="master-toggle-icon"></i> Controls and Permissions</span>
                                </div>
                            </div>
                            <div class="row" style="">
                                <div class="well" style="max-height: 300px; overflow: auto;">
                                    <form id="feature-toggles-form" method="post" action="masterCreator.php">
                                        <input type="hidden" name="action" value="save_customer_feature_toggles">
                                        <ul class="list-group">
                                            <li class="list-group-item">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong>GO ai</strong>
                                                        <small style="display: block; color: #777;">Customer account GO ai review button visibility</small>
                                                    </div>
                                                    <label class="modern-switch">
                                                        <input type="checkbox" class="feature-toggle-checkbox" name="customer_docora_ai_visible" value="1" <?php echo $customerDocoraAiVisible ? 'checked' : ''; ?>>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong>Date Graph</strong>
                                                        <small style="display: block; color: #777;">Customer chronological dates graph visibility</small>
                                                    </div>
                                                    <label class="modern-switch">
                                                        <input type="checkbox" class="feature-toggle-checkbox" name="customer_date_graph_visible" value="1" <?php echo $customerDateGraphVisible ? 'checked' : ''; ?>>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong>Letters Edit Icon</strong>
                                                        <small style="display: block; color: #777;">Customer account letters edit icon visibility</small>
                                                    </div>
                                                    <label class="modern-switch">
                                                        <input type="checkbox" class="feature-toggle-checkbox" name="customer_letters_edit_icon_visible" value="1" <?php echo $customerLettersEditIconVisible ? 'checked' : ''; ?>>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong>Letters Delete Icon</strong>
                                                        <small style="display: block; color: #777;">Customer account letters delete icon visibility</small>
                                                    </div>
                                                    <label class="modern-switch">
                                                        <input type="checkbox" class="feature-toggle-checkbox" name="customer_letters_delete_icon_visible" value="1" <?php echo $customerLettersDeleteIconVisible ? 'checked' : ''; ?>>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong>Audit Date Conflict</strong>
                                                        <small style="display: block; color: #777;">Audit Page date conflict panel visibility</small>
                                                    </div>
                                                    <label class="modern-switch">
                                                        <input type="checkbox" class="feature-toggle-checkbox" name="audit_date_conflict_visible" value="1" <?php echo $auditDateConflictVisible ? 'checked' : ''; ?>>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong>Audit Member Conflict</strong>
                                                        <small style="display: block; color: #777;">Audit Page member conflict panel visibility</small>
                                                    </div>
                                                    <label class="modern-switch">
                                                        <input type="checkbox" class="feature-toggle-checkbox" name="audit_member_conflict_visible" value="1" <?php echo $auditMemberConflictVisible ? 'checked' : ''; ?>>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-group-item" style="border-top: 2px solid #ddd;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong>Manual Status Change</strong>
                                                        <small style="display: block; color: #777;">Allow manual client status override</small>
                                                    </div>
                                                    <label class="modern-switch">
                                                        <input type="checkbox" class="feature-toggle-checkbox" name="customer_manual_status_change_enabled" value="1" <?php echo $customerManualStatusChangeEnabled ? 'checked' : ''; ?>>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong>Allow Accreditation Change</strong>
                                                        <small style="display: block; color: #777;">Allow manual accreditation change</small>
                                                    </div>
                                                    <label class="modern-switch">
                                                        <input type="checkbox" class="feature-toggle-checkbox" name="customer_accreditation_change_enabled" value="1" <?php echo $customerAccreditationChangeEnabled ? 'checked' : ''; ?>>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong>Allow Full Invoice Access</strong>
                                                        <small style="display: block; color: #777;">Allow full invoice list view for other role</small>
                                                    </div>
                                                    <label class="modern-switch">
                                                        <input type="checkbox" class="feature-toggle-checkbox" name="full_invoice_access_enabled" value="1" <?php echo $fullInvoiceAccessEnabled ? 'checked' : ''; ?>>
                                                        <span class="slider"></span>
                                                    </label>
                                                </div>
                                            </li>
                                        </ul>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <!-- AI Rules Section -->
                <div class="row" style="margin-top: 30px;">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-xs-8 text-center btn qms_complete_btn">
                                <span><i class="fa fa-magic"> AI Review Rules</i></span>
                            </div>
                            <div class="col-xs-4 text-center btn qms_complete_btn addNewAiRule">
                                <span><i class="fa fa-plus-square"> Add AI Rule</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 300px; overflow: auto;">
                                <ul class="list-group checked-list-box operateAiRules">
                                    <?php
                                    // Fetch AI Review Rules
                                    include_once './include/ai_document_review_lib.php';
                                    aitfAiEnsureSchema($db);
                                    $aiRulesStmt = $db->prepare("SELECT * FROM ai_review_rules ORDER BY is_active DESC, category ASC, rule_code ASC");
                                    $aiRulesStmt->execute();
                                    $aiRulesList = $aiRulesStmt->fetchAll();
                                    foreach ($aiRulesList as $aiRule) {
                                        $statusBadge = $aiRule['is_active'] == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>';
                                        ?>
                                        <li class="list-group-item">
                                            <a class="editAiRule" data-id="<?php echo $aiRule['id'] ?>" style="float: left"
                                                title="Edit AI Rule"><i class="fa fa-pencil-square"></i></a>&emsp;
                                            <strong><?php echo htmlspecialchars($aiRule['rule_code']) ?></strong> -
                                            <?php echo htmlspecialchars($aiRule['title']) ?>
                                            <?php echo $statusBadge; ?>
                                            <a class="deleteAiRule" data-id="<?php echo $aiRule['id'] ?>"
                                                style="float: right" title="Delete AI Rule"><i class="fa fa-trash"></i></a>
                                            <form method="post" action="masterCreator.php"
                                                style="display: inline; float: right; margin-right: 10px;"
                                                class="toggleAiRuleForm">
                                                <input type="hidden" name="action" value="toggle_ai_rule">
                                                <input type="hidden" name="ai_rule_id" value="<?php echo $aiRule['id']; ?>">
                                                <input type="hidden" name="ai_active"
                                                    value="<?php echo $aiRule['is_active'] == 1 ? 0 : 1; ?>">
                                                <button type="submit"
                                                    class="btn btn-xs <?php echo $aiRule['is_active'] == 1 ? 'btn-warning' : 'btn-success'; ?>"
                                                    style="padding: 2px 6px;">
                                                    <?php echo $aiRule['is_active'] == 1 ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newAiRule"></div>
                    </div>
                </div>

              
                <!-- POST GUIDELINES FOR STAFF SECTION                        -->
            
                <div class="row" id="guidelines-section" style="margin-top: 30px; margin-bottom: 25px;">
                    <div class="col-sm-6">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background: #193048; color: #fff;">
                                <div class="row">
                                    <div class="col-sm-12 qms_customer_name" style="color: #fff; font-size: 14px;">
                                        <span><i class="fa fa-book"></i></span> Post Guideline for Staff
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body" style="padding: 15px;">
                                <div class="alert alert-info" style="font-size: 12px; padding: 8px 12px; margin-bottom: 15px;">
                                    <i class="fa fa-info-circle"></i> Guidelines posted here are stored in the master pool. You can send and assign them to specific users from the <a href="addUsers.php#guidelines-dist-section" style="font-weight: bold; text-decoration: underline;">Add User</a> page.
                                </div>

                                <form id="postStaffGuidelineForm" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="create_guideline">
                                    <input type="hidden" name="is_ajax" value="1">

                                    <div class="form-group" style="margin-bottom: 12px;">
                                        <label style="font-size: 12px; font-weight: 600; color: #333;">Guideline Title <span class="text-danger">*</span></label>
                                        <input type="text" name="guideline_title" id="guidelineTitle" class="form-control" required placeholder="Enter guideline title or subject">
                                    </div>

                                    <div class="form-group" style="margin-bottom: 15px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                            <label style="font-size: 12px; font-weight: 600; color: #333; margin: 0;">Attach Document(s)</label>
                                            <button type="button" class="btn btn-xs btn-default" id="btnAddMoreAttachment" style="font-size: 11px;">
                                                <i class="fa fa-plus"></i> Add More File
                                            </button>
                                        </div>
                                        <div id="attachmentsContainer">
                                            <div class="attachment-input-row" style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                                                <input type="file" name="guideline_files[]" class="form-control input-sm" style="font-size: 12px;">
                                                <button type="button" class="btn btn-default btn-sm btn-remove-att-row" style="color: #c9513c; display: none;" title="Remove">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <button type="submit" class="btn btn-sm btn-info" id="btnSubmitGuideline" style="background: #193048; border-color: #193048; font-weight: 600; padding: 6px 16px;">
                                            <i class="fa fa-paper-plane"></i> Post Guideline
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background: #193048; color: #fff;">
                                <div class="row">
                                    <div class="col-xs-8 qms_customer_name" style="color: #fff; font-size: 14px;">
                                        <span><i class="fa fa-list"></i></span> Master Guidelines List
                                    </div>
                                    <div class="col-xs-4 text-right">
                                        <span class="badge" style="background: #fff; color: #193048; font-size: 11px;" id="postedGuidelinesCount">
                                            <?php echo count($allMasterGuidelines); ?> Total
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body" style="padding: 0;">
                                <div style="max-height: 380px; overflow-y: auto;">
                                    <div id="masterGuidelinesListContainer">
                                        <?php if (empty($allMasterGuidelines)) { ?>
                                            <div class="text-center text-muted" style="padding: 40px 15px;" id="noMasterGuidelinesNotice">
                                                <p style="margin: 0; font-size: 13px;">No guidelines posted yet. Use the form on the left to add guidelines to the master pool.</p>
                                            </div>
                                        <?php } else { ?>
                                            <table class="table table-bordered table-striped" style="margin-bottom: 0; font-size: 12px;">
                                                <thead>
                                                    <tr style="background: #f5f5f5;">
                                                        <th style="width: 50%;">Guideline</th>
                                                        <th style="width: 30%;">Status</th>
                                                        <th style="width: 20%; text-align: center;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($allMasterGuidelines as $mg) { 
                                                        $mAtts = $mg['attachments'] ?? [];
                                                        $mRecipients = $mg['recipients'] ?? [];
                                                        $mRecCount = count($mRecipients);
                                                        $mDate = date('d M Y, h:i A', strtotime($mg['created_at']));
                                                    ?>
                                                        <tr id="mc-guideline-item-<?php echo $mg['id']; ?>" class="mc-guideline-item">
                                                            <td>
                                                                <strong style="color: #193048;"><?php echo htmlspecialchars($mg['title']); ?></strong>
                                                                <div style="font-size: 11px; color: #777; margin-top: 2px;">
                                                                    <i class="fa fa-clock-o"></i> <?php echo $mDate; ?>
                                                                    <?php if (!empty($mAtts)) { ?>
                                                                        &nbsp;|&nbsp; <i class="fa fa-paperclip"></i> <?php echo count($mAtts); ?> doc(s)
                                                                    <?php } ?>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <?php if ($mRecCount > 0) { ?>
                                                                    <span class="label label-success" style="font-size: 10.5px;">Sent to <?php echo $mRecCount; ?> User(s)</span>
                                                                <?php } else { ?>
                                                                    <span class="label label-warning" style="font-size: 10.5px;">Not Sent Yet</span>
                                                                <?php } ?>
                                                            </td>
                                                            <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                                                <a href="addUsers.php#guidelines-dist-section" class="btn btn-xs btn-primary" style="padding: 2px 6px; font-size: 11px;" title="Send to users on Add Users page">
                                                                    <i class="fa fa-paper-plane"></i> Send
                                                                </a>
                                                                <button type="button" class="btn btn-xs btn-danger btn-delete-mc-guideline" 
                                                                        data-id="<?php echo $mg['id']; ?>" 
                                                                        data-title="<?php echo htmlspecialchars($mg['title'], ENT_QUOTES); ?>"
                                                                        style="padding: 2px 5px; margin-left: 2px;" title="Delete">
                                                                    <i class="fa fa-trash"></i>
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

                <div id="delete-details" style="display: none">
                    <div id="loading-div" class="ui-corner-all">
                        <span style="font-size: 30pt;"><i style="padding: 5px;"
                                class="fa fa-warning text-warning"></i></span>
                        <div>
                            <span style="font-size: 11pt; padding: 10px"
                                class="text-primary meeting-cancel-confirm-message">Are you sure that you want to delete
                                details?</span>
                        </div>
                        <div style="padding: 3px;">
                            <button title="Click here to delete"
                                class="btn btn-primary btn-round btn-round-sm btn-sm delete-confirm-ok">Ok</button>
                            <button title="Click here to cancel"
                                class="btn btn-danger  btn-round btn-round-sm btn-sm delete-cancel">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        function filename(path) {
            path = path.substring(path.lastIndexOf("/") + 1);
            return (path.match(/[^.]+(\.[^?#]+)?/) || [])[0];
        }

        function validateAuditor() {
            $isError = false;
            if ($('.newAuditorsInput').val() == "") {
                //            alert('in');
                $('#newAuditorsErrorMsg').text("The auditor can not be empty");
                $('#newAuditorsError').show();
                $('#newAuditors').addClass('inputHighlight');
                $isError = true;
            } else if ($('.newAuditorsInput').val() != "" && $('.auditorId').val() == "") {
                //            if ($('.editCustomerId') == "") {
                $.ajax({
                    type: "POST",
                    url: "checkCustomer.php",
                    data: { auditor: $('.newAuditorsInput').val() },
                    async: false,
                    success: function (data) {
                        // alert(data);
                        if (data == 1) {
                            $('#newAuditorsErrorMsg').text("The auditor already exist in the system");
                            $('#newAuditorsError').show();
                            $('#newAuditorsInput').addClass('inputHighlight');
                            mainErr = true;
                            $isError = true;
                        } else {
                            $('#newAuditorsError').hide();
                            $('#newAuditorsInput').removeClass('inputHighlight');
                            mainErr = false;
                        }
                    }
                });
                //            }
            } else {
                $('#newAuditorsError').hide();
                $('#newAuditorsInput').removeClass('inputHighlight');
            }
            return $isError;
        }

        $(document).ready(function () {
            
            // ==========================================
            // Staff Guidelines Logic
            // ==========================================
            // Dynamic Add More Attachment
            $('#btnAddMoreAttachment').on('click', function(e) {
                e.preventDefault();
                var newRow = $(
                    '<div class="attachment-input-row" style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">' +
                        '<div style="flex-grow: 1;">' +
                            '<input type="file" name="guideline_files[]" class="form-control" style="border-radius: 8px; height: 38px; padding: 5px 10px; font-size: 12px; border-color: #cbd5e1;">' +
                        '</div>' +
                        '<button type="button" class="btn btn-danger btn-sm btn-remove-att-row" style="border-radius: 8px; padding: 5px 10px;" title="Remove attachment">' +
                            '<i class="fa fa-trash"></i>' +
                        '</button>' +
                    '</div>'
                );
                $('#attachmentsContainer').append(newRow);
            });

            // Remove Attachment Row
            $(document).on('click', '.btn-remove-att-row', function(e) {
                e.preventDefault();
                $(this).closest('.attachment-input-row').remove();
            });

            // Post Guideline AJAX Submission
            $('#postStaffGuidelineForm').on('submit', function(e) {
                e.preventDefault();

                var $form = $(this);
                var $btn = $('#btnSubmitGuideline');
                var origBtnHtml = $btn.html();

                var title = $.trim($('#guidelineTitle').val());
                if (title === '') {
                    alert('Please enter guideline title.');
                    $('#guidelineTitle').focus();
                    return false;
                }

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Posting Guideline...');

                var formData = new FormData(this);

                $.ajax({
                    url: 'guidelines_action.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        $btn.prop('disabled', false).html(origBtnHtml);
                        if (res.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Guideline Posted!',
                                    text: res.message,
                                    icon: 'success',
                                    confirmButtonColor: '#0d4d8b'
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                alert(res.message);
                                location.reload();
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Error', res.message || 'Failed to post guideline', 'error');
                            } else {
                                alert(res.message || 'Failed to post guideline');
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.prop('disabled', false).html(origBtnHtml);
                        alert('Error communicating with server. Please try again.');
                    }
                });
            });

            // Delete Guideline from Master Creator
            $(document).on('click', '.btn-delete-mc-guideline', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var title = $(this).data('title');

                var doDelete = function() {
                    $.ajax({
                        url: 'guidelines_action.php',
                        type: 'POST',
                        data: {
                            action: 'delete_guideline',
                            guideline_id: id,
                            is_ajax: 1
                        },
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.success) {
                                $('#mc-guideline-item-' + id).fadeOut(300, function() {
                                    $(this).remove();
                                    var count = $('.mc-guideline-item').length;
                                    $('#postedGuidelinesCount').text(count + ' Posted');
                                    if (count === 0) {
                                        $('#masterGuidelinesListContainer').html(
                                            '<div class="text-center text-muted" style="padding: 60px 15px;" id="noMasterGuidelinesNotice">' +
                                                '<i class="fa fa-folder-open-o" style="font-size: 40px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>' +
                                                '<p style="margin: 0; font-size: 13.5px;">No guidelines posted yet. Use the form on the left to post directives for staff.</p>' +
                                            '</div>'
                                        );
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
                            alert('Server connection error.');
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Delete Guideline?',
                        text: 'Are you sure you want to delete "' + title + '" and its attachments?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, Delete'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            doDelete();
                        }
                    });
                } else {
                    if (confirm('Are you sure you want to delete "' + title + '"?')) {
                        doDelete();
                    }
                }
            });

            // Feature Toggles Logic
            var updateMasterToggleState = function() {
                var allChecked = $('.feature-toggle-checkbox').length === $('.feature-toggle-checkbox:checked').length;
                var $icon = $('#master-toggle-icon');
                if (allChecked) {
                    $icon.removeClass('fa-toggle-off').addClass('fa-toggle-on');
                } else {
                    $icon.removeClass('fa-toggle-on').addClass('fa-toggle-off');
                }
            };
            
            updateMasterToggleState();
            
            $('.feature-toggle-checkbox').on('change', function() {
                updateMasterToggleState();
                
                // Submit form via AJAX
                var form = $('#feature-toggles-form');
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        // success (silent save)
                    }
                });
            });
            
            $('#master-toggle-btn').on('click', function() {
                var allChecked = $('.feature-toggle-checkbox').length === $('.feature-toggle-checkbox:checked').length;
                var targetState = !allChecked; // If all checked, turn all off. Else turn all on.
                
                $('.feature-toggle-checkbox').prop('checked', targetState);
                updateMasterToggleState();
                
                // Submit form via AJAX
                var form = $('#feature-toggles-form');
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        // success
                    }
                });
            });

            $("#menu-toggle").click(function (e) {
                e.preventDefault();
                $("#wrapper").toggleClass("active");
                $(".container").toggleClass("active");
                table.draw();
            });

            $("body").on("click", ".createHandler", function () {
                //            alert($('.addtoId').val());
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        newHandler: $('.newHandlerInput ').val(),
                        idHandler: $('.handlerId').val(),
                    },
                    success: function (data) {
                        //                        alert(data);
                        $('.newHandler').empty();
                        $('.operateHandlers').empty();
                        for (var i in data) {
                            $('.operateHandlers').append('<li class="list-group-item"><a class="editHandlers" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['handler_name'] + '<a class="deleteHandlers" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err) {
                        alert(status);
                        $('.newHandler').empty();
                    }
                });
            });

            $("body").on("click", ".editHandlers", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        handler_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        $('.newHandlerInput').val(data["handler_name"]);
                        $('.handlerId').val(data["id"]);
                        $('#handlerModalLabel').text('Edit Handler');
                        $("input").trigger("change");
                        $(".handlerAddModal").modal("show");
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });
            $("body").on("click", ".editNaceClass", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        group_Id: $(this).attr('data-id'),
                        group_Id1: $(this).attr('data-id1'),
                        group_Id2: $(this).attr('data-id2'),
                    },
                    success: function (data) {
                        //console.log(data[2]);
                        $(".newIafInput1").val(data[0]["iaf_code"]);
                        $(".newIafDesc1").val(data[3]);
                        $(".iaf_code_id").val(data[0]["iaf_id"]);
                        $(".nace_code_id").val(data[4]);
                        $(".newnaceaddrowClass").empty();
                        //var data = JSON.parse(data);
                        for (var key in data[2]) {
                            //alert("nace class "+data[2][key]["nace_class"]);
                            $(".newnaceaddrowClass").append('<div class="row parentrow">\n\
                                                            <div class="col-sm-4 form-group float-label-control">\n\
                                                            <label>Nace Code</label>\n\
                                                            <input type="text" class="newNaceCode1" id="newNaceCode1" name="newNaceCode1" placeholder="Nace Code" value="' + data[2][key]["nace_class"] + '">\n\
                                                            </div>\n\
                                                            <div class="col-sm-7 form-group float-label-control">\n\
                                                            <label>Technical Area</label>\n\
                                                            <input type="text" class="newNaceDesc1" id="newNaceDesc1" name="newNaceDesc1" placeholder="Technical Area" value="' + data[2][key]["nace_desc"] + '">\n\
                                                            </div>\n\
                                                            <div class="col-sm-1 form-group float-label-control contentrow">\n\
                                                            <a class="removeNaceCode" style="" title="Remove Nace"><i class="fa fa-close"></i></a>\n\
                                                            </div></div>');
                        }
                        $("input").trigger("change");
                        $(".iafnaceClassmodel").modal("show");
                    },
                    error: function (data, status, err) {
                        alert(err);
                    }
                });
            });
            $("body").on("click", ".deleteHandlers", function () {
                $id = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    $.ajax({
                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            handleId: $id,
                        },
                        success: function (data) {
                            //    	                            alert(data);
                            $('#delete-details').hide();
                            $('.newHandler').empty();
                            $('.operateHandlers').empty();
                            for (var i in data) {
                                $('.operateHandlers').append('<li class="list-group-item"><a class="editHandlers" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['handler_name'] + '<a class="deleteHandlers" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err) {
                            alert(status);
                        }
                    });
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });

            $("body").on("click", ".deleteStatus", function (e) {
                $id = $(this).attr('data-id');

                $('#delete-details').show();

                $('.delete-confirm-ok').click(function () {
                    $.ajax({

                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {

                            statusId: $id,
                        },
                        success: function (data) {
                            //    	                            alert(data);
                            $('#delete-details').hide();
                            $('.newStatus').empty();
                            $('.operateStatus').empty();
                            for (var i in data) {
                                $('.operateStatus').append('<li class="list-group-item"><a class="editStatus" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['status'] + '<a class="deleteStatus" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err) {
                            alert(status);
                        }
                    });
                });

                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });

            $("body").on("click", ".deleteAddto", function () {
                $id = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    $.ajax({
                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            addtosId: $id,
                        },
                        success: function (data) {
                            //    	                            alert(data);
                            $('#delete-details').hide();
                            $('.newAddto').empty();
                            $('.operateAddto').empty();
                            for (var i in data) {
                                $('.operateAddto').append('<li class="list-group-item"><a class="editAddtos" data-id="' + data[i]['add_to_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['add_to_type'] + '<a class="deleteAddto" data-id="' + data[i]['add_to_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err) {
                            alert(status);
                        }
                    });
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });

            $("body").on("click", ".createStatus", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        newStatus: $('#newStatusInput').val(),
                        idStatus: $('.statusId').val(),
                    },
                    success: function (data) {
                        // alert(data);
                        $('.newStatus').empty();
                        $('.operateStatus').empty();
                        for (var i in data) {
                            $('.operateStatus').append('<li class="list-group-item"><a class="editStatus" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['status'] + '<a class="deleteStatus" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err) {
                        //alert(status);
                        $('.newNace').empty();
                    }
                });
            });

            $("body").on("click", ".editCertSubmit", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        updateCertNo: $(".updateCertNo").val(),
                        firm_id: $('.firm_id').val(),
                    },
                    success: function (data) {
                        //                        alert(data);
                        $('.operateCertNo').empty();
                        $('.changeCertification').empty();
                        $('.operateCertNo').append('<li class="list-group-item"><a class="editCertificationNo" data-id="' + data['firm_id'] + '" data-val="' + data['certification_no'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data['certification_no'] + '</li>');

                    },
                    error: function (data, status, err) {
                        alert(status);
                        $('.changeCertification').empty();
                    }
                });
            });

            $("body").on("click", ".editcreateAddTo", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        editAddto: $(".newAddtoInpute").val(),
                        eidAddto: $('.addtoIde').val(),
                    },
                    success: function (data) {
                        //                        alert(data);
                        $('.newAddto').empty();
                        $('.operateAddto').empty();
                        for (var i in data) {
                            $('.operateAddto').append('<li class="list-group-item"><a class="editAddtos" data-id="' + data[i]['add_to_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['add_to_type'] + '<a class="deleteAddto" data-id="' + data[i]['add_to_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err) {
                        alert(status);
                        $('.newNace').empty();
                    }
                });
            });

            $("body").on("click", ".createAddTo", function () {
                //            alert($('.addtoId').val());
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        newAddto: $('.newAddtoInput').val(),
                        editAddto: $(".newAddtoInpute").val(),
                        idAddto: $('.addtoId').val(),
                    },
                    success: function (data) {
                        //                        alert(data);
                        $('.newAddto').empty();
                        $('.operateAddto').empty();
                        for (var i in data) {
                            $('.operateAddto').append('<li class="list-group-item"><a class="editAddtos" data-id="' + data[i]['add_to_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['add_to_type'] + '<a class="deleteAddto" data-id="' + data[i]['add_to_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err) {
                        alert(status);
                        $('.newNace').empty();
                    }
                });
            });

            $("body").on("click", ".editCertificationNo", function () {
                $id = $(this).attr("data-id");
                $val = $(this).attr("data-val");
                //                alert($id + " val " + $val);
                $('.changeCertification').empty();
                $('.changeCertification').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                                        <div class="col-sm-12 form-group float-label-control">\n\
                                                        <input type="text" name="updateCertNo" class="form-control updateCertNo" placeholder="New Add To" value="' + $val + '"></div>\n\
                                                        <input type="hidden" class="firm_id" name="firm_id" value="' + $id + '">\n\
                                                        <a class="btn btn-info btn-xs editCertSubmit" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                                        <div id="newAddToError" style="display: none">\n\
                                                        <small><span id="newAddToErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                                        </div>\n\
                                                        </div>\n\
                                                        </div>');
            });

            $("body").on("click", ".editAddtos", function () {
                //            alert($(this).attr('data-id'));
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        addto_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        $('.newAddtoInput').val(data["add_to_type"]);
                        $('.addtoId').val(data["add_to_id"]);
                        $('#addtoModalLabel').text('Edit Add-To');
                        $("input").trigger("change");
                        $(".addtoAddModal").modal("show");
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });

            $("body").on("click", ".editStatus", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        status_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        $('.newStatusInput').val(data["status"]);
                        $('.statusId').val(data["id"]);
                        $('#statusModalLabel').text('Edit Status');
                        $("input").trigger("change");
                        $(".statusAddModal").modal("show");
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });

            /* --- NACE Class Assignment UI Handlers in Master Creator --- */
            function updateNacodeSelectionDisplay() {
                var checkedClasses = [];
                $('.nacodeClassCheckbox:checked').each(function () {
                    checkedClasses.push($(this).val());
                });

                var count = checkedClasses.length;
                $('.nacodeSelectedCount').text(count + ' Selected');

                if (count === 0) {
                    $('.nacodeSummaryText').text('Click to assign NACE Classes...').css('color', '#475569');
                    $('.nacodeSelectedBadges').empty();
                } else {
                    var summaryStr = count <= 4 ? count + ' Classes (' + checkedClasses.join(', ') + ')' : count + ' NACE Classes Selected';
                    $('.nacodeSummaryText').text(summaryStr).css('color', '#1e40af');

                    var badgesHtml = '';
                    checkedClasses.forEach(function (code) {
                        badgesHtml += '<span class="badge" style="background:#dbeafe; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; padding:3px 7px; border-radius:12px; font-weight:600; display:inline-flex; align-items:center; gap:4px;">' +
                            code + ' <a href="javascript:void(0)" class="nacodeRemoveBadge" data-val="' + code + '" style="color:#1e40af; text-decoration:none; margin-left:2px; font-size:12px;" title="Remove Class ' + code + '">&times;</a></span>';
                    });
                    $('.nacodeSelectedBadges').html(badgesHtml);
                }
            }

            $(document).on('change', '.nacodeClassCheckbox', function () {
                updateNacodeSelectionDisplay();
            });

            $(document).on('click', '.nacodeRemoveBadge', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var clsVal = $(this).attr('data-val');
                $('.nacodeClassCheckbox[value="' + clsVal + '"]').prop('checked', false).trigger('change');
            });

            $(document).on('click', '.nacodeSelectGroupBtn', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var divCode = $(this).attr('data-division');
                var $checkboxes = $('.nacodeDiv-' + divCode + ':visible');
                var allChecked = $checkboxes.length > 0 && $checkboxes.filter(':checked').length === $checkboxes.length;
                $checkboxes.prop('checked', !allChecked);
                updateNacodeSelectionDisplay();
            });

            $(document).on('click', '.nacodeSelectAllBtn', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $('.nacodeItemLabel:visible .nacodeClassCheckbox').prop('checked', true);
                updateNacodeSelectionDisplay();
            });

            $(document).on('click', '.nacodeClearAllBtn', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $('.nacodeClassCheckbox').prop('checked', false);
                updateNacodeSelectionDisplay();
            });

            $(document).on('keyup input', '.nacodeFilterInput', function () {
                var term = $(this).val().toLowerCase().trim();
                $('.nacodeDivisionSection').each(function () {
                    var $sec = $(this);
                    var hasVisible = false;
                    $sec.find('.nacodeItemLabel').each(function () {
                        var searchData = $(this).attr('data-search') || '';
                        if (!term || searchData.indexOf(term) !== -1) {
                            $(this).show();
                            hasVisible = true;
                        } else {
                            $(this).hide();
                        }
                    });
                    if (hasVisible) {
                        $sec.show();
                    } else {
                        $sec.hide();
                    }
                });
            });

            $(document).on('click', '.nacodeDropdownMenu', function (e) {
                e.stopPropagation();
            });

            $('body').on('click', ".editAuditor", function (e) {
                //            var auditorid = $(this).attr('data-id');
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        auditor_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        $(".auditorName").val(data['auditor_name']);
                        $(".auditorNumber").val(data['auditor_number']);
                        $(".auditorEmail").val(data['auditor_email']);
                        $(".auditor_id").val(data['auditors_id']);
                        $(".auditorabbr").val(data['abbrevation']);
                        $(".isReviewerCheckbox").prop("checked", data['is_reviewer'] == 1);
                        
                        $('.nacodeClassCheckbox').prop('checked', false);
                        $('.nacodeFilterInput').val('');
                        $('.nacodeItemLabel, .nacodeDivisionSection').show();
                        if (data['assigned_nace_classes'] && Array.isArray(data['assigned_nace_classes'])) {
                            data['assigned_nace_classes'].forEach(function(code) {
                                $('.nacodeClassCheckbox[value="' + code + '"]').prop('checked', true);
                            });
                        }
                        updateNacodeSelectionDisplay();

                        $("input").trigger("change");
                        $(".auditoradd").modal("show");
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });

            /* --- Generate Auditor Key Handler --- */
            $("body").on("click", ".generateAuditorKey", function (e) {
                e.preventDefault();
                e.stopPropagation();
                var btn = $(this);
                var auditorId = btn.attr('data-id');
                var auditorName = btn.attr('data-name');
                var defaultEmail = btn.attr('data-email') || '';

                // Build email prompt modal
                var modalHtml = '<div class="modal fade" id="auditorKeyModal" tabindex="-1" role="dialog">' +
                    '<div class="modal-dialog" role="document">' +
                    '<div class="modal-content" style="border-radius:12px;overflow:hidden;">' +
                    '<div class="modal-header" style="background:linear-gradient(135deg,#0F2742,#1e3a5f);color:#fff;border:0;padding:20px 24px;">' +
                    '<button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8;text-shadow:none;"><span>&times;</span></button>' +
                    '<h4 class="modal-title"><i class="fa fa-key"></i> Send EID</h4>' +
                        '</div>' +
                        '<div class="modal-body" style="padding:24px;">' +
                        '<p style="margin-bottom:16px;color:#555;">Send Employee ID (EID) for <strong>' + auditorName + '</strong> to their email address.</p>' +
                        '<div class="form-group">' +
                        '<label style="font-weight:600;margin-bottom:6px;">Email Address <span style="color:#e53e3e;">*</span></label>' +
                        '<input type="email" class="form-control" id="auditorKeyEmail" value="' + defaultEmail + '" placeholder="auditor@email.com" style="border-radius:8px;padding:10px 14px;border:1.5px solid #ddd;" required>' +
                        '<small class="text-muted">EID will be sent to this email</small>' +
                        '</div>' +
                        '</div>' +
                        '<div class="modal-footer" style="border-top:1px solid #eee;padding:16px 24px;">' +
                        '<button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius:8px;">Cancel</button>' +
                        '<button type="button" class="btn btn-primary" id="confirmGenerateKey" style="border-radius:8px;background:linear-gradient(135deg,#0F2742,#1e3a5f);border:0;padding:8px 24px;">' +
                        '<i class="fa fa-paper-plane"></i> Send EID</button>' +
                        '</div>' +
                        '</div></div></div>';

                    // Remove old modal if any
                    $('#auditorKeyModal').remove();
                    $('body').append(modalHtml);
                    $('#auditorKeyModal').modal('show');

                    // Handle confirm click
                    $('#confirmGenerateKey').off('click').on('click', function () {
                        var email = $('#auditorKeyEmail').val().trim();
                        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                            $('#auditorKeyEmail').css('border-color', '#e53e3e').focus();
                            return;
                        }

                        var confirmBtn = $(this);
                        confirmBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');

                        $.ajax({
                            url: 'auditorKeyAction.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                auditor_id: auditorId,
                                email: email
                            },
                            success: function (resp) {
                                $('#auditorKeyModal').modal('hide');
                                if (resp.success) {
                                    // Update the button's data-email
                                    btn.attr('data-email', email);
                                    // Add or update Portal Enabled badge
                                    var li = btn.closest('li');
                                    if (li.find('.label-success').length === 0) {
                                        btn.after('<span class="label label-success" style="float:right; margin-right:8px;">Portal Enabled</span>');
                                    }
                                    // Show success alert
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'EID Sent!',
                                            html: 'Employee ID (EID) has been sent to<br><strong>' + email + '</strong>',
                                            confirmButtonColor: '#0F2742'
                                        });
                                    } else {
                                        alert('Success! EID sent to ' + email);
                                    }
                                } else {
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: resp.message || 'Failed to generate key.',
                                            confirmButtonColor: '#0F2742'
                                        });
                                    } else {
                                        alert('Error: ' + (resp.message || 'Failed to generate key.'));
                                    }
                                }
                            },
                            error: function () {
                                $('#auditorKeyModal').modal('hide');
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Server Error',
                                        text: 'Could not connect to server. Please try again.',
                                        confirmButtonColor: '#0F2742'
                                    });
                                } else {
                                    alert('Server error. Please try again.');
                                }
                            }
                        });
                    });

                    // Cleanup modal on hide
                    $('#auditorKeyModal').on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                });

            /* --- Toggle Auditor Reviewer Role Handler --- */
            $("body").on("click", ".toggleReviewerBtn", function (e) {
                e.preventDefault();
                e.stopPropagation();
                var btn = $(this);
                var auditorId = btn.attr('data-id');
                var auditorName = btn.attr('data-name');
                var currentStatus = btn.attr('data-status');
                var newStatus = (currentStatus == "1") ? 0 : 1;

                var promptText = (newStatus === 1)
                    ? 'Assign "' + auditorName + '" as Reviewer?'
                    : 'Remove Reviewer role from "' + auditorName + '"?';

                var doToggle = function() {
                    btn.prop('disabled', true);
                    $.ajax({
                        url: 'toggleReviewerAction.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            auditor_id: auditorId,
                            is_reviewer: newStatus
                        },
                        success: function (resp) {
                            btn.prop('disabled', false);
                            if (resp.success) {
                                if (resp.is_reviewer == 1) {
                                    btn.attr('data-status', '1')
                                       .removeClass('btn-default')
                                       .addClass('btn-success')
                                       .css({'border': '', 'color': ''})
                                       .attr('title', 'Designated as Reviewer. Click to toggle role')
                                       .html('<i class="fa fa-check-circle"></i> Reviewer');
                                } else {
                                    btn.attr('data-status', '0')
                                       .removeClass('btn-success')
                                       .addClass('btn-default')
                                       .css({'border': '1px dashed #10b981', 'color': '#047857'})
                                       .attr('title', 'Click to assign as Reviewer')
                                       .html('<i class="fa fa-plus"></i> Add Reviewer');
                                }
                                $('#selectAuditorForReviewer option[value="' + auditorId + '"]').text(
                                    auditorName + (resp.is_reviewer == 1 ? ' [Currently Reviewer]' : '')
                                );
                                if (typeof Swal !== 'undefined' && Swal.fire) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: resp.is_reviewer == 1 ? 'Reviewer Assigned' : 'Reviewer Role Removed',
                                        text: resp.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    alert(resp.message);
                                }
                            } else {
                                alert(resp.message || 'Failed to update reviewer status.');
                            }
                        },
                        error: function () {
                            btn.prop('disabled', false);
                            alert('Network or server error updating reviewer status.');
                        }
                    });
                };

                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        title: promptText,
                        text: (newStatus === 1) ? 'This auditor will be selectable in the Review Application reviewer list.' : 'This auditor will no longer appear as a reviewer.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: newStatus === 1 ? '#059669' : '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: newStatus === 1 ? 'Yes, Set as Reviewer' : 'Yes, Remove'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            doToggle();
                        }
                    });
                } else {
                    if (confirm(promptText)) {
                        doToggle();
                    }
                }
            });

            /* --- Add Reviewer Modal Trigger --- */
            $('.addNewReviewer').click(function () {
                $('#selectAuditorForReviewer').val('');
                $('#addReviewerModal').modal('show');
            });

            $('#confirmAssignReviewerBtn').click(function () {
                var auditorId = $('#selectAuditorForReviewer').val();
                if (!auditorId) {
                    alert('Please select an auditor first.');
                    return;
                }
                var selectedOption = $('#selectAuditorForReviewer option:selected');
                var auditorName = selectedOption.text().replace('[Currently Reviewer]', '').trim();

                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: 'toggleReviewerAction.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        auditor_id: auditorId,
                        is_reviewer: 1
                    },
                    success: function (resp) {
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Set as Reviewer');
                        $('#addReviewerModal').modal('hide');
                        if (resp.success) {
                            var rowBtn = $('.operateAuditors').find('.toggleReviewerBtn[data-id="' + auditorId + '"]');
                            if (rowBtn.length) {
                                rowBtn.attr('data-status', '1')
                                      .removeClass('btn-default')
                                      .addClass('btn-success')
                                      .css({'border': '', 'color': ''})
                                      .attr('title', 'Designated as Reviewer. Click to toggle role')
                                      .html('<i class="fa fa-check-circle"></i> Reviewer');
                            }
                            $('#selectAuditorForReviewer option[value="' + auditorId + '"]').text(auditorName + ' [Currently Reviewer]');
                            if (typeof Swal !== 'undefined' && Swal.fire) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Reviewer Assigned!',
                                    text: resp.message,
                                    confirmButtonColor: '#059669'
                                });
                            } else {
                                alert(resp.message);
                            }
                        } else {
                            alert(resp.message || 'Failed to assign reviewer.');
                        }
                    },
                    error: function () {
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Set as Reviewer');
                        alert('Server error while assigning reviewer.');
                    }
                });
            });

            $("body").on("click", ".editStandards", function (e) {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        standatd_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        $('.newStandardsInput').val(data['standards_name']);
                        $('.standardId').val(data['id']);
                        $('#standardsModalLabel').text('Edit Standard');
                        $("input").trigger("change");
                        $(".standardsAddModal").modal("show");
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });

            $("body").on("click", ".editAccreditation", function (e) {
                //            alert($(this).attr('data-id'));
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        accreditation_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        $('.newAccreditationInput').val(data['accreditation_name']);
                        $('.accreditationId').val(data['accreditation_id']);
                        $('#accreditationModalLabel').text('Edit Accreditation');
                        $("input").trigger("change");
                        $(".accreditationAddModal").modal("show");
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });
            $("body").on("click", ".editcategories", function (e) {
                //            alert($(this).attr('data-id'));
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        categories_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        $('.newcategoriesInput').val(data['categories_name']);
                        $('.categoriesId').val(data['categories_id']);
                        $('#categoriesModalLabel').text('Edit Category');
                        $("input").trigger("change");
                        $(".categoriesAddModal").modal("show");
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });

            $("body").on("click", ".editLegalStatus", function (e) {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        legalStatus_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        $('.newLegalStatusInput').val(data['status']);
                        $('.legalStatusId').val(data['status_id']);
                        $('#legalStatusModalLabel').text('Edit Legal Status');
                        $("input").trigger("change");
                        $(".legalStatusAddModal").modal("show");
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });

            $("body").on("click", ".editNace", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        nace_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        //                        $('.newNace').empty();
                        //                        $('.newNace').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                        //                                            <div class="col-sm-12 form-group float-label-control">\n\
                        //                                            <input type="text" name="newNaceInput" class="form-control newNaceInput" id="newNaceInput" placeholder="New NACE Code" value="' + data['nace_code'] + '"></div>\n\
                        //                                            <input type="hidden" class="naceId" name="naceId" value="' + data['nace_id'] + '">\n\
                        //                                            <a class="btn btn-info btn-xs createNace" style="float: right;"><i class="fa fa-check"></i></a>\n\
                        //                                            <div id="newLegalStatusError" style="display: none">\n\
                        //                                            <small><span id="newLegalStatusErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                        //                                            </div>\n\
                        //                                            </div>\n\
                        //                                            </div>');
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });

            $("body").on("click", ".editIaf", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        iaf_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        //                        alert(data[1]);
                        $(".newIafInput").val(data[0]["iaf_code"]);
                        $(".newIafDesc").val(data[0]["iaf_desc"]);
                        $(".iaf_code_id").val(data[0]["iaf_id"]);
                        //                        alert($(".iaf_code_id").val());
                        $(".newnaceaddrow").empty();
                        for (var key in data[1]) {
                            //                           alert("nace codes "+data[1][key]["nace_code"]);                            
                            $(".newnaceaddrow").append('<div class="row parentrow">\n\
                                                            <div class="col-sm-4 form-group float-label-control">\n\
                                                            <label>Nace Code</label>\n\
                                                            <input type="text" class="newNaceCode" id="newNaceCode" name="newNaceCode" placeholder="Nace Code" value="' + data[1][key]["nace_code"] + '">\n\
                                                            </div>\n\
                                                            <div class="col-sm-7 form-group float-label-control">\n\
                                                            <label>Technical Area</label>\n\
                                                            <input type="text" class="newNaceDesc" id="newNaceDesc" name="newNaceDesc" placeholder="Technical Area" value="' + data[1][key]["nace_desc"] + '">\n\
                                                            </div>\n\
                                                            <div class="col-sm-1 form-group float-label-control contentrow">\n\
                                                            <a class="removeNaceCode" style="" title="Remove Nace"><i class="fa fa-close"></i></a>\n\
                                                            </div></div>');
                        }
                        $("input").trigger("change");
                        $(".iafnacemodel").modal("show");
                        //                        alert(data);
                        //                        $('.newIaf').empty();
                        //                        $('.newIaf').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                        //                                            <div class="col-sm-12 form-group float-label-control">\n\
                        //                                            <input type="text" name="newIafInput" class="form-control newIafInput" id="newIafInput" placeholder="New IAF Code" value="' + data['iaf_code'] + '"></div>\n\
                        //                                            <input type="hidden" class="iafId" name="iafId" value="' + data['iaf_id'] + '">\n\
                        //                                            <a class="btn btn-info btn-xs createIaf" style="float: right;"><i class="fa fa-check"></i></a>\n\
                        //                                            <div id="newLegalStatusError" style="display: none">\n\
                        //                                            <small><span id="newLegalStatusErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                        //                                            </div>\n\
                        //                                            </div>\n\
                        //                                            </div>');
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });

            $('.deleteFirm').click(function () {
                var firmId = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    var url = 'firmDelete.php'
                    var form = $('<form action="' + url + '" method="post">' +
                        '<input type="hidden" name="firmId" value="' + firmId + '" />' +
                        '</form>');
                    $('body').append(form);
                    form.submit();
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });

            $('.editFirm').click(function () {
                //            alert($(this).attr('data-id'));
                $.ajax({
                    url: "getAuditApplication.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        firm_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        $('.new-bank').empty();
                        $('.serviceDescTd').empty();
                        //                    var name = (filename(data[0]['firm_logo']));
                        $('.firmId').val(data[0]['firm_id']);
                        $('.consultingFirmName').val(data[0]['firm_name']);
                        $('.consultingFirmEmail').val(data[0]['firm_email']);
                        $('.firmAddress').val(data[0]['firm_address']);
                        $('.firmContact').val(data[0]['firm_contact']);
                        $('.panNo').val(data[0]['pan_no']);
                        $('.svcCode').val(data[0]['svc_code']);
                        $('.cinNo').val(data[0]['cin_no']);
                        $('.bankName').val(data[0]['firm_bank']);
                        $('.bankAcNo').val(data[0]['bank_ac_no']);
                        $('.bankAddress').val(data[0]['bank_address']);
                        $('.ifscCode').val(data[0]['bank_ifsc_code']);
                        $('.micrNo').val(data[0]['bank_micr_no']);
                        $('.firmAbbreviation').val(data[0]['firm_abbrevation']);
                        $('.profarmaPrefix').val(data[0]['profarma_prefix']);
                        $('.firmInvoice').val(data[0]['firm_invoice_no']);
                        $(".finalPrefix").val(data[0]['final_prefix']);
                        $(".firmInvoiceFinal").val(data[0]['final_invoice_no']);
                        $('.firmQuotation').val(data[0]['firm_quotation_no']);
                        $('#firmImg').attr("src", data[0]['firm_logo']);
                        //                    $('#firmLogo').val(name);
                        for (var i in data[1]) {
                            $('.serviceDescTd').append('<div class="row">\n\
                                                        <div class="col-sm-12 form-group float-label-control">\n\
                                                        <label for="">Service Description</label>\n\
                                                        <input type="text" name="serviceDescription" rows="2" class="form-control form-group serviceDescription" id="serviceDescription" value="' + data[1][i]['firm_service'] + '" placeholder="Service Description">\n\
                                                        </div>\n\
                                                        </div>');
                        }
                        for (var i in data[2]) {
                            if (i == 0) {
                                $('.new-bank').append('<div class="newAbbrRow col-sm-10 form-group float-label-control">\n\
                                                        <label>Bank Abbreviation</label>\n\
                                                        <input type="text" class="bankAbbreviation" id="bankAbbreviation" name="bankAbbreviation" value="' + data[2][i]['abbrevation'] + '" placeholder="Bank Abbreviation" >\n\
                                                        </div>\n\
                                                        <div class="col-sm-2 form-group float-label-control">\n\
                                                        <a class=" newConsultAbbr" style="float: right;" title="New Abbreviation Add"><i class="fa fa-plus"></i></a>\n\
                                                        </div>');
                            } else {
                                $('.new-bank').append('<div class="newAbbrRowParent"><div class="newAbbrRow col-sm-10 form-group float-label-control ">\n\
                                                    <label>Bank Abbreviation</label>\n\
                                                    <input type="text" class="bankAbbreviation" id="bankAbbreviation" name="bankAbbreviation" value="' + data[2][i]['abbrevation'] + '" placeholder="Bank Abbreviation" >\n\
                                                    </div><div class="col-sm-2 form-group deleteNewConsultAbbrParent float-label-control">\n\
                                                    <a class="deleteNewConsultAbbr" style="float: right;"><i class="fa fa-remove"></i></a>\n\
                                                    </div></div>');
                            }
                        }
                        $('input[type="text"]').trigger('change');
                        $('textarea').trigger('change');
                        $('input[type="number"]').trigger('change');
                        //                    $('body').trigger("change", '.bankAbbreviation', function () {});
                        $('#consultingFirmAdd').modal('show');
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });

            $('.addNewAuditors').click(function () {
                $(".auditorName").val("");
                $(".auditorNumber").val("");
                $(".auditorEmail").val("");
                $(".auditor_id").val("");
                $(".auditorabbr").val("");
                $(".isReviewerCheckbox").prop("checked", false);
                $('.nacodeClassCheckbox').prop('checked', false);
                $('.nacodeFilterInput').val('');
                $('.nacodeItemLabel, .nacodeDivisionSection').show();
                updateNacodeSelectionDisplay();
                $(".auditoradd").modal("show");
                //                $('.newAuditors').empty();
                //                $('.newAuditors').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                //                                            <div class="col-sm-12 form-group float-label-control">\n\
                //                                            <input type="text" name="newAuditorsInput" class="form-control newAuditorsInput" id="newAuditorsInput" placeholder="New Auditors" value=""></div>\n\
                //                                                    <input type="hidden" class="auditorId" name="auditorId" value="">\n\
                //                                            <a class="btn btn-info btn-xs createAuditors" style="float: right;"><i class="fa fa-check"></i></a>\n\
                //                                            <div id="newAuditorsError" style="display: none">\n\
                //                                            <small><span id="newAuditorsErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                //                                            </div></div></div>');
            });

            $('.addNewStandards').click(function () {
                $('.newStandardsInput').val('');
                $('.standardId').val('');
                $('#standardsModalLabel').text('Add Standard');
                $(".standardsAddModal").modal("show");
            });

            $(".addNewAccreditation").click(function () {
                $('.newAccreditationInput').val('');
                $('.accreditationId').val('');
                $('#accreditationModalLabel').text('Add Accreditation');
                $(".accreditationAddModal").modal("show");
            });
            $(".addNewcategories").click(function () {
                $('.newcategoriesInput').val('');
                $('.categoriesId').val('');
                $('#categoriesModalLabel').text('Add Category');
                $(".categoriesAddModal").modal("show");
            });

            $(".addNewLegalStatus").click(function () {
                $('.newLegalStatusInput').val('');
                $('.legalStatusId').val('');
                $('#legalStatusModalLabel').text('Add Legal Status');
                $(".legalStatusAddModal").modal("show");
            });

            $(".addNewNace").click(function () {
                $(".iafnaceClassmodel").modal("show");
                // $('.newNace').empty();
                // $('.newNace').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                //                             <div class="col-sm-12 form-group float-label-control">\n\
                //                             <input type="text" name="newNaceInput" class="form-control newNaceInput" id="newNaceInput" placeholder="New NACE Code" value=""></div>\n\
                //                             <input type="hidden" class="naceId" name="naceId" value="">\n\
                //                             <a class="btn btn-info btn-xs createNace" style="float: right;"><i class="fa fa-check"></i></a>\n\
                //                             <div id="newLegalStatusError" style="display: none">\n\
                //                             <small><span id="newLegalStatusErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                //                             </div>\n\
                //                             </div>\n\
                //                             </div>');
            });

            $(".addNewIaf").click(function () {

                $(".iafnacemodel").modal("show");
                //               
            });

            $(".addNewStatus").click(function () {
                $('.newStatusInput').val('');
                $('.statusId').val('');
                $('#statusModalLabel').text('Add Status');
                $(".statusAddModal").modal("show");
            });

            $(".addNewAddto").click(function () {
                $('.newAddtoInput').val('');
                $('.addtoId').val('');
                $('#addtoModalLabel').text('Add Add-To');
                $(".addtoAddModal").modal("show");
            });

            $(".addNewHandler").click(function () {
                $('.newHandlerInput').val('');
                $('.handlerId').val('');
                $('#handlerModalLabel').text('Add Handler');
                $(".handlerAddModal").modal("show");
            });

            $('.addNewTaxation').click(function () {
                $('.newTaxation').val("");
                $('.newRate').val("");
                $('.taxation_id_update').val("");
                $('#taxationAdd').modal('show');
            });

            $('.addNewFirm').click(function () {
                $('#consultingFirmAdd').modal("show");
            });

            //edit Taxation
            $('.editTaxation').click(function () {
                $id = $(this).attr('edit-Taxation-id');
                //alert($id);
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        taxation_Id: $id,
                    },
                    success: function (data) {
                        //alert(data);
                        $('.newTaxation').val(data['type_of_tax']);
                        $('.newRate').val(data['rate']);
                        $('.taxation_id_update').val(data['taxation_id']);
                        $('#taxationAdd').modal('show');
                    },
                    error: function (data, status, err) {
                        alert(status);
                    }
                });
            });

            $('body').on('click', ".createAuditors", function (e) {
                //            alert($('.auditorId').val());
                if (validateAuditor()) {
                    e.preventDefault();
                    return;
                }
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        newAuditors: $('#newAuditorsInput').val(),
                        auditors_Id: $('.auditorId').val(),
                    },
                    success: function (data) {
                        //                    alert(data);
                        $('.newAuditors').empty();
                        $('.operateAuditors').empty();
                        for (var i in data) {
                            var reviewerBtn = (data[i]['is_reviewer'] == 1)
                                ? '<button type="button" class="btn btn-xs btn-success toggleReviewerBtn" data-id="' + data[i]['auditors_id'] + '" data-name="' + data[i]['auditor_name'] + '" data-status="1" style="float: right; margin-right: 8px;"><i class="fa fa-check-circle"></i> Reviewer</button>'
                                : '<button type="button" class="btn btn-xs btn-default toggleReviewerBtn" data-id="' + data[i]['auditors_id'] + '" data-name="' + data[i]['auditor_name'] + '" data-status="0" style="float: right; margin-right: 8px; border: 1px dashed #10b981; color: #047857;"><i class="fa fa-plus"></i> Add Reviewer</button>';
                            var portalKeyBtn = '<button type="button" class="btn btn-xs btn-primary generateAuditorKey" data-id="' + data[i]['auditors_id'] + '" data-name="' + data[i]['auditor_name'] + '" data-email="' + (data[i]['login_email'] || data[i]['auditor_email'] || '') + '" style="float: right; margin-right: 8px;"><i class="fa fa-key"></i> Generate Key</button>';
                            var portalBadge = (data[i]['login_enabled'] == 1) ? '<span class="label label-success" style="float:right; margin-right:8px;">Portal Enabled</span>' : '';
                            $('.operateAuditors').append('<li class="list-group-item" data-auditor-id="' + data[i]['auditors_id'] + '"><a class="editAuditor" data-id="' + data[i]['auditors_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['auditor_name'] + '<a class="deleteAuditor" data-id="' + data[i]['auditors_id'] + '" style="float: right"><i class="fa fa-trash"></i></a>' + portalKeyBtn + reviewerBtn + portalBadge + '</li>');
                        }
                    },
                    error: function (data, status, err) {
                        $('.newAuditors').empty();
                        //                    alert(status);
                    }
                });
            });

            $('body').on('click', ".createStandards", function (e) {
                if (validateAuditor()) {
                    e.preventDefault();
                    return;
                }
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        newStandards: $('#newStandardsInput').val(),
                        standards_Id: $('.standardId').val(),
                    },
                    success: function (data) {
                        $('.newStandards').empty();
                        $('.operateStandards').empty();
                        for (var i in data) {
                            $('.operateStandards').append('<li class="list-group-item"><a class="editStandards" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['standards_name'] + '<a class="deleteStandards" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err) {
                        $('.newStandards').empty();
                        //                    
                    }
                });
            });

            $("body").on("click", ".createAccreditation", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        newAccreditation: $('#newAccreditationInput').val(),
                        accreditation_Id: $('.accreditationId').val(),
                    },
                    success: function (data) {
                        //                    alert(data);
                        $('.newAccreditation').empty();
                        $('.operateAccreditation').empty();
                        for (var i in data) {
                            $('.operateAccreditation').append('<li class="list-group-item"><a class="editAccreditation" data-id="' + data[i]['accreditation_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['accreditation_name'] + '<a class="deleteAccreditation" data-id="' + data[i]['accreditation_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err) {
                        //                    alert(status);
                        $('.newAccreditation').empty();
                    }
                });
            });
            $("body").on("click", ".createcategories", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        newcategories: $('#newcategoriesInput').val(),
                        categories_Id: $('.categoriesId').val(),
                    },
                    success: function (data) {
                        //                    alert(data);
                        $('.newcategories').empty();
                        $('.operatecategories').empty();
                        for (var i in data) {
                            $('.operatecategories').append('<li class="list-group-item"><a class="editcategories" data-id="' + data[i]['categories_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['categories_name'] + '<a class="deletecategories" data-id="' + data[i]['categories_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err) {
                        //                    alert(status);
                        $('.newcategories').empty();
                    }
                });
            });

            $("body").on("click", ".createLegalStatus", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        newLegalStatus: $('#newLegalStatusInput').val(),
                        legalStatus_Id: $('.legalStatusId').val(),
                    },
                    success: function (data) {
                        //                    alert(data);
                        $('.newLegalStatus').empty();
                        $('.operateLegalStatus').empty();
                        for (var i in data) {
                            $('.operateLegalStatus').append('<li class="list-group-item"><a class="editLegalStatus" data-id="' + data[i]['status_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['status'] + '<a class="deleteLegalStatus" data-id="' + data[i]['status_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err) {
                        //                    alert(status);
                        $('.newLegalStatus').empty();
                    }
                });
            });

            $("body").on("click", ".createNace", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        newNace: $('#newNaceInput').val(),
                        nace_Id: $('.naceId').val(),
                    },
                    success: function (data) {
                        //                    alert(data);
                        $('.newNace').empty();
                        $('.operateNace').empty();
                        for (var i in data) {
                            $('.operateNace').append('<li class="list-group-item"><a class="editNace" data-id="' + data[i]['nace_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['nace_code'] + '<a class="deleteNace" data-id="' + data[i]['nace_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err) {
                        //                    alert(status);
                        $('.newNace').empty();
                    }
                });
            });

            $("body").on("click", ".createIaf", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        newIaf: $('#newIafInput').val(),
                        iaf_Id: $('.iafId').val(),
                    },
                    success: function (data) {
                        //                    alert(data);
                        $('.newIaf').empty();
                        $('.operateIaf').empty();
                        for (var i in data) {
                            $('.operateIaf').append('<li class="list-group-item"><a class="editIaf" data-id="' + data[i]['iaf_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['iaf_code'] + '<a class="deleteIaf" data-id="' + data[i]['iaf_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err) {
                        //                    alert(status);
                        $('.newNace').empty();
                    }
                });
            });

            $('body').on('click', ".deleteAuditor", function (e) {
                $id = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    $.ajax({
                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            auditorId: $id,
                        },
                        success: function (data) {
                            //                    alert(data);
                            $('#delete-details').hide();
                            $('.operateAuditors').empty();
                            for (var i in data) {
                                var reviewerBtn = (data[i]['is_reviewer'] == 1)
                                    ? '<button type="button" class="btn btn-xs btn-success toggleReviewerBtn" data-id="' + data[i]['auditors_id'] + '" data-name="' + data[i]['auditor_name'] + '" data-status="1" style="float: right; margin-right: 8px;"><i class="fa fa-check-circle"></i> Reviewer</button>'
                                    : '<button type="button" class="btn btn-xs btn-default toggleReviewerBtn" data-id="' + data[i]['auditors_id'] + '" data-name="' + data[i]['auditor_name'] + '" data-status="0" style="float: right; margin-right: 8px; border: 1px dashed #10b981; color: #047857;"><i class="fa fa-plus"></i> Add Reviewer</button>';
                                var portalKeyBtn = '<button type="button" class="btn btn-xs btn-primary generateAuditorKey" data-id="' + data[i]['auditors_id'] + '" data-name="' + data[i]['auditor_name'] + '" data-email="' + (data[i]['login_email'] || data[i]['auditor_email'] || '') + '" style="float: right; margin-right: 8px;"><i class="fa fa-key"></i> Generate Key</button>';
                                var portalBadge = (data[i]['login_enabled'] == 1) ? '<span class="label label-success" style="float:right; margin-right:8px;">Portal Enabled</span>' : '';
                                $('.operateAuditors').append('<li class="list-group-item" data-auditor-id="' + data[i]['auditors_id'] + '"><a class="editAuditor" data-id="' + data[i]['auditors_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['auditor_name'] + '<a class="deleteAuditor" data-id="' + data[i]['auditors_id'] + '" style="float: right"><i class="fa fa-trash"></i></a>' + portalKeyBtn + reviewerBtn + portalBadge + '</li>');
                            }
                        },
                        error: function (data, status, err) {
                            alert(status);
                        }
                    });
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });

            $("body").on("click", ".deleteStandards", function () {
                //        alert($(this).attr('data-id'));
                var id = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    $.ajax({
                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            standardId: id,
                        },
                        success: function (data) {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newStandards').empty();
                            $('.operateStandards').empty();
                            for (var i in data) {
                                $('.operateStandards').append('<li class="list-group-item"><a class="editStandards" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['standards_name'] + '<a class="deleteStandards" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err) {
                            alert(status);
                        }
                    });
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });

            $("body").on("click", ".deleteAccreditation", function () {
                var id = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    $.ajax({
                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            accreditationId: id,
                        },
                        success: function (data) {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newAccreditation').empty();
                            $('.operateAccreditation').empty();
                            for (var i in data) {
                                $('.operateAccreditation').append('<li class="list-group-item"><a class="editAccreditation" data-id="' + data[i]['accreditation_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['accreditation_name'] + '<a class="deleteAccreditation" data-id="' + data[i]['accreditation_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err) {
                            alert(status);
                        }
                    });
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });
            $("body").on("click", ".deletecategories", function () {
                var id = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    $.ajax({
                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            categoriesId: id,
                        },
                        success: function (data) {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newcategories').empty();
                            $('.operatecategories').empty();
                            for (var i in data) {
                                $('.operatecategories').append('<li class="list-group-item"><a class="editcategories" data-id="' + data[i]['categories_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['categories_name'] + '<a class="deletecategories" data-id="' + data[i]['categories_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err) {
                            alert(status);
                        }
                    });
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });

            $("body").on("click", ".deleteLegalStatus", function () {
                var id = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    $.ajax({
                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            legalStatusId: id,
                        },
                        success: function (data) {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newLegalStatus').empty();
                            $('.operateLegalStatus').empty();
                            for (var i in data) {
                                $('.operateLegalStatus').append('<li class="list-group-item"><a class="editLegalStatus" data-id="' + data[i]['status_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['status'] + '<a class="deleteLegalStatus" data-id="' + data[i]['status_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err) {
                            alert(status);
                        }
                    });
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });

            $("body").on("click", ".deleteNace", function () {
                var id = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    $.ajax({
                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            naceId: id,
                        },
                        success: function (data) {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newNace').empty();
                            $('.operateNace').empty();
                            for (var i in data) {
                                $('.operateNace').append('<li class="list-group-item"><a class="editNace" data-id="' + data[i]['nace_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['nace_code'] + '<a class="deleteNace" data-id="' + data[i]['nace_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err) {
                            alert(status);
                        }
                    });
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });

            $("body").on("click", ".deleteIaf", function () {
                var id = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    $.ajax({
                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            iafId: id,
                        },
                        success: function (data) {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newIaf').empty();
                            $('.operateIaf').empty();
                            for (var i in data) {
                                $('.operateIaf').append('<li class="list-group-item"><a class="editIaf" data-id="' + data[i]['iaf_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['iaf_code'] + '<a class="deleteIaf" data-id="' + data[i]['iaf_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                            window.location.reload();
                        },
                        error: function (data, status, err) {
                            alert(status);
                        }
                    });
                });
            });

            // AI Rules handlers
            $(".addNewAiRule").click(function () {
                $('.newAiRule').empty();
                $('.newAiRule').append('<div class="col-sm-12"><div class="well">\n\
                    <h4>Add New AI Review Rule</h4>\n\
                    <div class="row">\n\
                        <div class="col-sm-3 form-group">\n\
                            <label>Rule Code <span style="color:red;">*</span></label>\n\
                            <input type="text" name="aiRuleCode" class="form-control aiRuleCode" placeholder="e.g. ISO-DATE-001" maxlength="80" required>\n\
                        </div>\n\
                        <div class="col-sm-5 form-group">\n\
                            <label>Title <span style="color:red;">*</span></label>\n\
                            <input type="text" name="aiRuleTitle" class="form-control aiRuleTitle" placeholder="Rule Title" maxlength="255" required>\n\
                        </div>\n\
                        <div class="col-sm-3 form-group">\n\
                            <label>Category</label>\n\
                            <input type="text" name="aiRuleCategory" class="form-control aiRuleCategory" placeholder="Dates, Scope, etc" maxlength="120">\n\
                        </div>\n\
                        <div class="col-sm-1 form-group">\n\
                            <label>Active</label><br>\n\
                            <input type="checkbox" name="aiRuleActive" class="aiRuleActive" checked>\n\
                        </div>\n\
                    </div>\n\
                    <div class="row">\n\
                        <div class="col-sm-7 form-group">\n\
                            <label>Rule Text <span style="color:red;">*</span></label>\n\
                            <textarea name="aiRuleText" class="form-control aiRuleText" rows="4" required></textarea>\n\
                        </div>\n\
                        <div class="col-sm-5 form-group">\n\
                            <label>Expected Value / Evidence Source</label>\n\
                            <textarea name="aiRuleExpected" class="form-control aiRuleExpected" rows="4"></textarea>\n\
                        </div>\n\
                    </div>\n\
                    <input type="hidden" class="aiRuleId" name="aiRuleId" value="">\n\
                    <a class="btn btn-success btn-sm createAiRule"><i class="fa fa-check"></i> Save Rule</a>\n\
                    <a class="btn btn-default btn-sm cancelAiRule"><i class="fa fa-times"></i> Cancel</a>\n\
                    <div id="newAiRuleError" style="display: none; margin-top: 10px;">\n\
                        <small><span id="newAiRuleErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>\n\
                    </div>\n\
                </div></div>');
            });

            $("body").on("click", ".cancelAiRule", function () {
                $('.newAiRule').empty();
            });

            $("body").on("click", ".editAiRule", function () {
                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        ai_rule_Id: $(this).attr('data-id'),
                    },
                    success: function (data) {
                        $('.newAiRule').empty();
                        $('.newAiRule').append('<div class="col-sm-12"><div class="well">\n\
                            <h4>Edit AI Review Rule</h4>\n\
                            <div class="row">\n\
                                <div class="col-sm-3 form-group">\n\
                                    <label>Rule Code <span style="color:red;">*</span></label>\n\
                                    <input type="text" name="aiRuleCode" class="form-control aiRuleCode" placeholder="e.g. ISO-DATE-001" maxlength="80" value="' + data['rule_code'] + '" required>\n\
                                </div>\n\
                                <div class="col-sm-5 form-group">\n\
                                    <label>Title <span style="color:red;">*</span></label>\n\
                                    <input type="text" name="aiRuleTitle" class="form-control aiRuleTitle" placeholder="Rule Title" maxlength="255" value="' + data['title'] + '" required>\n\
                                </div>\n\
                                <div class="col-sm-3 form-group">\n\
                                    <label>Category</label>\n\
                                    <input type="text" name="aiRuleCategory" class="form-control aiRuleCategory" placeholder="Dates, Scope, etc" maxlength="120" value="' + (data['category'] || '') + '">\n\
                                </div>\n\
                                <div class="col-sm-1 form-group">\n\
                                    <label>Active</label><br>\n\
                                    <input type="checkbox" name="aiRuleActive" class="aiRuleActive" ' + (data['is_active'] == 1 ? 'checked' : '') + '>\n\
                                </div>\n\
                            </div>\n\
                            <div class="row">\n\
                                <div class="col-sm-7 form-group">\n\
                                    <label>Rule Text <span style="color:red;">*</span></label>\n\
                                    <textarea name="aiRuleText" class="form-control aiRuleText" rows="4" required>' + (data['rule_text'] || '') + '</textarea>\n\
                                </div>\n\
                                <div class="col-sm-5 form-group">\n\
                                    <label>Expected Value / Evidence Source</label>\n\
                                    <textarea name="aiRuleExpected" class="form-control aiRuleExpected" rows="4">' + (data['expected_value'] || '') + '</textarea>\n\
                                </div>\n\
                            </div>\n\
                            <input type="hidden" class="aiRuleId" name="aiRuleId" value="' + data['id'] + '">\n\
                            <a class="btn btn-success btn-sm createAiRule"><i class="fa fa-check"></i> Update Rule</a>\n\
                            <a class="btn btn-default btn-sm cancelAiRule"><i class="fa fa-times"></i> Cancel</a>\n\
                            <div id="newAiRuleError" style="display: none; margin-top: 10px;">\n\
                                <small><span id="newAiRuleErrorMsg" style="color: #c9513c; font-style: italic;"></span></small>\n\
                            </div>\n\
                        </div></div>');
                    },
                    error: function (data, status, err) {
                        alert("Error loading AI rule: " + status);
                    }
                });
            });

            $("body").on("click", ".createAiRule", function () {
                var ruleCode = $('.aiRuleCode').val().trim().toUpperCase();
                var ruleTitle = $('.aiRuleTitle').val().trim();
                var ruleCategory = $('.aiRuleCategory').val().trim();
                var ruleText = $('.aiRuleText').val().trim();
                var ruleExpected = $('.aiRuleExpected').val().trim();
                var ruleActive = $('.aiRuleActive').is(':checked') ? 1 : 0;
                var ruleId = $('.aiRuleId').val();

                // Validation
                if (ruleCode === '' || ruleTitle === '' || ruleText === '') {
                    $('#newAiRuleErrorMsg').text("Rule code, title, and rule text are required.");
                    $('#newAiRuleError').show();
                    return;
                }

                $.ajax({
                    url: "addNewServices.php",
                    type: "GET",
                    dataType: "json",
                    data: {
                        newAiRule: 'save',
                        aiRuleId: ruleId,
                        aiRuleCode: ruleCode,
                        aiRuleTitle: ruleTitle,
                        aiRuleCategory: ruleCategory,
                        aiRuleText: ruleText,
                        aiRuleExpected: ruleExpected,
                        aiRuleActive: ruleActive
                    },
                    success: function (data) {
                        $('.newAiRule').empty();
                        $('.operateAiRules').empty();
                        for (var i in data) {
                            var statusBadge = data[i]['is_active'] == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>';
                            var toggleBtn = data[i]['is_active'] == 1 ?
                                '<button type="submit" class="btn btn-xs btn-warning" style="padding: 2px 6px;">Deactivate</button>' :
                                '<button type="submit" class="btn btn-xs btn-success" style="padding: 2px 6px;">Activate</button>';
                            $('.operateAiRules').append('<li class="list-group-item">\n\
                                <a class="editAiRule" data-id="' + data[i]['id'] + '" style="float: left" title="Edit AI Rule"><i class="fa fa-pencil-square"></i></a>&emsp;\n\
                                <strong>' + data[i]['rule_code'] + '</strong> - ' + data[i]['title'] + ' ' + statusBadge + '\n\
                                <a class="deleteAiRule" data-id="' + data[i]['id'] + '" style="float: right" title="Delete AI Rule"><i class="fa fa-trash"></i></a>\n\
                                <form method="post" action="masterCreator.php" style="display: inline; float: right; margin-right: 10px;" class="toggleAiRuleForm">\n\
                                    <input type="hidden" name="action" value="toggle_ai_rule">\n\
                                    <input type="hidden" name="ai_rule_id" value="' + data[i]['id'] + '">\n\
                                    <input type="hidden" name="ai_active" value="' + (data[i]['is_active'] == 1 ? 0 : 1) + '">\n\
                                    ' + toggleBtn + '\n\
                                </form>\n\
                            </li>');
                        }
                    },
                    error: function (data, status, err) {
                        $('#newAiRuleErrorMsg').text("Error saving AI rule: " + status);
                        $('#newAiRuleError').show();
                    }
                });
            });

            $("body").on("click", ".deleteAiRule", function () {
                var id = $(this).attr('data-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    $.ajax({
                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            aiRuleId: id,
                        },
                        success: function (data) {
                            $('#delete-details').hide();
                            $('.newAiRule').empty();
                            $('.operateAiRules').empty();
                            for (var i in data) {
                                var statusBadge = data[i]['is_active'] == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>';
                                var toggleBtn = data[i]['is_active'] == 1 ?
                                    '<button type="submit" class="btn btn-xs btn-warning" style="padding: 2px 6px;">Deactivate</button>' :
                                    '<button type="submit" class="btn btn-xs btn-success" style="padding: 2px 6px;">Activate</button>';
                                $('.operateAiRules').append('<li class="list-group-item">\n\
                                    <a class="editAiRule" data-id="' + data[i]['id'] + '" style="float: left" title="Edit AI Rule"><i class="fa fa-pencil-square"></i></a>&emsp;\n\
                                    <strong>' + data[i]['rule_code'] + '</strong> - ' + data[i]['title'] + ' ' + statusBadge + '\n\
                                    <a class="deleteAiRule" data-id="' + data[i]['id'] + '" style="float: right" title="Delete AI Rule"><i class="fa fa-trash"></i></a>\n\
                                    <form method="post" action="masterCreator.php" style="display: inline; float: right; margin-right: 10px;" class="toggleAiRuleForm">\n\
                                        <input type="hidden" name="action" value="toggle_ai_rule">\n\
                                        <input type="hidden" name="ai_rule_id" value="' + data[i]['id'] + '">\n\
                                        <input type="hidden" name="ai_active" value="' + (data[i]['is_active'] == 1 ? 0 : 1) + '">\n\
                                        ' + toggleBtn + '\n\
                                    </form>\n\
                                </li>');
                            }
                        },
                        error: function (data, status, err) {
                            alert("Error deleting AI rule: " + status);
                        }
                    });
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });

            $('body').on('click', ".deleteTaxation", function (e) {
                $id = $(this).attr('taxation-id');
                $('#delete-details').show();
                $('.delete-confirm-ok').click(function () {
                    $.ajax({
                        url: "addNewServices.php",
                        type: "GET",
                        dataType: "json",
                        data: {
                            taxationId: $id,
                        },
                        success: function (data) {
                            //                    alert(data);
                            $('#delete-details').hide();
                            $('.operateTaxation').empty();
                            for (var i in data) {
                                $('.operateTaxation').append('<li class="list-group-item"><a class="editTaxation" edit-Taxation-id="' + data[i]['taxation_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<span><b>Type of tax: </b>' + data[i]['type_of_tax'] + '</span>&emsp;<span><b>Rate: </b>' + data[i]['rate'] + '%</span><a class="deleteTaxation" taxation-id="' + data[i]['taxation_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err) {
                            alert(status);
                        }
                    });
                });
                $('.delete-cancel').click(function () {
                    $('#delete-details').hide();
                });
            });

        });
    </script>
    <style>
        /* =====================================================================
           MASTER CREATOR — MODERN CLEAN SAAS DESIGN SYSTEM
           ===================================================================== */
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
           COMPACT HERO HEADER
           ===================================================================== */
        .mc-hero-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #eff6ff 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 18px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            position: relative;
            overflow: hidden;
        }

        .mc-hero-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0d4d8b 0%, #2563eb 50%, #38bdf8 100%);
        }

        .mc-hero-title {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
        }

        .mc-hero-stats {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .mc-stat-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 12px;
            display: flex;
            align-items: center;
            gap: 9px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .mc-stat-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.04);
        }

        .mc-stat-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .mc-stat-icon.icon-blue { background: #eff6ff; color: #2563eb; }
        .mc-stat-icon.icon-green { background: #ecfdf5; color: #059669; }
        .mc-stat-icon.icon-purple { background: #f5f3ff; color: #7c3aed; }

        .mc-stat-num {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .mc-stat-lbl {
            font-size: 9.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            line-height: 1;
            margin-top: 2px;
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

        .qms_complete_btn.addNewTaxation,
        .qms_complete_btn.addNewAuditors,
        .qms_complete_btn.addNewStandards,
        .qms_complete_btn.addNewAccreditation,
        .qms_complete_btn.addNewLegalStatus,
        .qms_complete_btn.addNewIaf,
        .qms_complete_btn.addNewNace,
        .qms_complete_btn.addNewStatus,
        .qms_complete_btn.addNewcategories,
        .qms_complete_btn.addNewAddto,
        .qms_complete_btn.addNewHandler,
        .qms_complete_btn.addNewAiRule {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%) !important;
            border-left: 1px solid rgba(255, 255, 255, 0.2) !important;
            cursor: pointer !important;
        }

        .qms_complete_btn.addNewTaxation:hover,
        .qms_complete_btn.addNewAuditors:hover,
        .qms_complete_btn.addNewStandards:hover,
        .qms_complete_btn.addNewAccreditation:hover,
        .qms_complete_btn.addNewLegalStatus:hover,
        .qms_complete_btn.addNewIaf:hover,
        .qms_complete_btn.addNewNace:hover,
        .qms_complete_btn.addNewStatus:hover,
        .qms_complete_btn.addNewcategories:hover,
        .qms_complete_btn.addNewAddto:hover,
        .qms_complete_btn.addNewHandler:hover,
        .qms_complete_btn.addNewAiRule:hover {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%) !important;
            color: #ffffff !important;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.1) !important;
        }

        .well {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-top: none !important;
            border-radius: 0 0 12px 12px !important;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04) !important;
            padding: 0 !important;
            margin-bottom: 24px !important;
            max-height: 230px !important;
            overflow-y: auto !important;
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

        /* Edit and Delete Icons */
        .list-group-item a.editFirm,
        .list-group-item a.editTaxation,
        .list-group-item a.editAuditor,
        .list-group-item a.editStandards,
        .list-group-item a.editAccreditation,
        .list-group-item a.editLegalStatus,
        .list-group-item a.editIaf,
        .list-group-item a.editNaceClass,
        .list-group-item a.editStatus,
        .list-group-item a.editcategories,
        .list-group-item a.editAddtos,
        .list-group-item a.editHandlers,
        .list-group-item a.editCertificationNo,
        .list-group-item a.editAiRule {
            color: #0d4d8b !important;
            font-size: 14px !important;
            padding: 4px 7px !important;
            border-radius: 5px !important;
            transition: all 0.15s !important;
            margin-right: 8px !important;
        }

        .list-group-item a.editFirm:hover,
        .list-group-item a.editTaxation:hover,
        .list-group-item a.editAuditor:hover,
        .list-group-item a.editStandards:hover,
        .list-group-item a.editAccreditation:hover,
        .list-group-item a.editLegalStatus:hover,
        .list-group-item a.editIaf:hover,
        .list-group-item a.editNaceClass:hover,
        .list-group-item a.editStatus:hover,
        .list-group-item a.editcategories:hover,
        .list-group-item a.editAddtos:hover,
        .list-group-item a.editHandlers:hover,
        .list-group-item a.editCertificationNo:hover,
        .list-group-item a.editAiRule:hover {
            background: #eff6ff !important;
            color: #2563eb !important;
        }

        .list-group-item a.deleteFirm,
        .list-group-item a.deleteTaxation,
        .list-group-item a.deleteAuditor,
        .list-group-item a.deleteStandards,
        .list-group-item a.deleteAccreditation,
        .list-group-item a.deleteLegalStatus,
        .list-group-item a.deleteIaf,
        .list-group-item a.deleteStatus,
        .list-group-item a.deletecategories,
        .list-group-item a.deleteAddto,
        .list-group-item a.deleteHandlers,
        .list-group-item a.deleteAiRule {
            color: #ef4444 !important;
            font-size: 14px !important;
            padding: 4px 7px !important;
            border-radius: 5px !important;
            transition: all 0.15s !important;
        }

        .list-group-item a.deleteFirm:hover,
        .list-group-item a.deleteTaxation:hover,
        .list-group-item a.deleteAuditor:hover,
        .list-group-item a.deleteStandards:hover,
        .list-group-item a.deleteAccreditation:hover,
        .list-group-item a.deleteLegalStatus:hover,
        .list-group-item a.deleteIaf:hover,
        .list-group-item a.deleteStatus:hover,
        .list-group-item a.deletecategories:hover,
        .list-group-item a.deleteAddto:hover,
        .list-group-item a.deleteHandlers:hover,
        .list-group-item a.deleteAiRule:hover {
            background: #fef2f2 !important;
            color: #dc2626 !important;
        }

        .generateAuditorKey {
            background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%) !important;
            border: none !important;
            border-radius: 6px !important;
            padding: 4px 10px !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            color: #ffffff !important;
            box-shadow: 0 2px 5px rgba(13, 77, 139, 0.2) !important;
            transition: transform 0.15s !important;
        }

        .generateAuditorKey:hover {
            transform: scale(1.03) !important;
            color: #ffffff !important;
        }

        .label-success {
            background-color: #10b981 !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            font-weight: 700 !important;
            padding: 3px 8px !important;
            font-size: 10.5px !important;
        }

        /* Modern Toggle Switch */
        .modern-switch {
            position: relative;
            display: inline-block;
            width: 42px;
            height: 22px;
            margin: 0;
        }
        .modern-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .modern-switch .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .3s;
            border-radius: 22px;
        }
        .modern-switch .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }
        .modern-switch input:checked + .slider {
            background-color: #0d4d8b;
        }
        .modern-switch input:checked + .slider:before {
            transform: translateX(20px);
        }

        /* Dynamic form popups */
        .newTaxation, .newAuditors, .newStandards, .newAccreditation,
        .newLegalStatus, .newIaf, .newNace, .newStatus, .newcategories,
        .newAddto, .newHandler, .changeCertification, .newAiRule {
            margin-bottom: 20px;
        }

        /* Buttons & Badges */
        .btn-circle {
            width: 30px;
            height: 30px;
            text-align: center;
            padding: 6px 0;
            font-size: 12px;
            line-height: 1.428571429;
            border-radius: 15px;
        }

        /* =====================================================================
           DARK MODE SUPPORT
           ===================================================================== */
        html.theme-dark {
            --hpd-bg: #0a0e14;
            --hpd-surface: #111722;
            --hpd-surface-2: #161d2b;
            --hpd-border: #232c3d;
            --hpd-border-2: #2e3a4f;
            --hpd-text: #dbe3f0;
            --hpd-text-dim: #93a1b8;
            --hpd-text-faint: #6b7890;
            --hpd-accent: #4a9eff;
        }

        html.theme-dark body, 
        html.theme-dark #page-content-wrapper,
        html.theme-dark .page-content.inset {
            background: var(--hpd-bg);
            color: var(--hpd-text);
        }

        html.theme-dark .mc-hero-header {
            background: linear-gradient(135deg, #111722 0%, #0d131e 100%);
            border-color: var(--hpd-border);
        }
        html.theme-dark .mc-hero-title { color: #f8fafc; }
        html.theme-dark .mc-stat-pill {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
        }
        html.theme-dark .mc-stat-num { color: #f8fafc; }

        html.theme-dark .qms_complete_btn {
            background: linear-gradient(135deg, #16243d 0%, #0d1b33 100%) !important;
            border-color: var(--hpd-border-2);
        }

        html.theme-dark .well {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
        }

        html.theme-dark .list-group-item {
            background: var(--hpd-surface) !important;
            border-color: var(--hpd-border) !important;
            color: var(--hpd-text) !important;
        }

        html.theme-dark .list-group-item:hover {
            background: var(--hpd-surface-2) !important;
        }

        html.theme-dark .list-group-item a.editFirm,
        html.theme-dark .list-group-item a.editTaxation,
        html.theme-dark .list-group-item a.editAuditor,
        html.theme-dark .list-group-item a.editStandards,
        html.theme-dark .list-group-item a.editAccreditation,
        html.theme-dark .list-group-item a.editLegalStatus,
        html.theme-dark .list-group-item a.editIaf,
        html.theme-dark .list-group-item a.editNaceClass,
        html.theme-dark .list-group-item a.editStatus,
        html.theme-dark .list-group-item a.editcategories,
        html.theme-dark .list-group-item a.editAddtos,
        html.theme-dark .list-group-item a.editHandlers,
        html.theme-dark .list-group-item a.editCertificationNo,
        html.theme-dark .list-group-item a.editAiRule {
            color: #60a5fa !important;
        }

        html.theme-dark .form-control {
            background: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }

        html.theme-dark .form-control:focus {
            border-color: var(--hpd-accent);
            box-shadow: 0 0 0 4px rgba(74, 158, 255, 0.18);
        }
    </style>

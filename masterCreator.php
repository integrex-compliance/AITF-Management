<div id="wrapper" class="active">
    <?php
  
    $pageIdentifier = "Master Data";
    include_once './header.php';
    include_once './dbConnect.php';
    include_once './include/customer_feature_flags.php';
    date_default_timezone_set('Asia/Kolkata');

    $featureToggleMessage = '';
    $canManageCustomerFeatureToggles = Utitlity::checkForUserMainAdmin();
    if ($canManageCustomerFeatureToggles && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_customer_feature_toggles') {
        aitfCustomerFeatureSet($db, 'customer_docora_ai_visible', isset($_POST['customer_docora_ai_visible']), isset($_SESSION['username']) ? $_SESSION['username'] : '');
        aitfCustomerFeatureSet($db, 'customer_date_graph_visible', isset($_POST['customer_date_graph_visible']), isset($_SESSION['username']) ? $_SESSION['username'] : '');
        $featureToggleMessage = 'Customer account feature visibility updated.';
    }

    $customerDocoraAiVisible = aitfCustomerFeatureIsEnabled($db, 'customer_docora_ai_visible');
    $customerDateGraphVisible = aitfCustomerFeatureIsEnabled($db, 'customer_date_graph_visible');

//    auditors
    $auditors = "SELECT * from auditors";
    $statement = $db->prepare($auditors);
    $statement->execute();
    $auditorsList = $statement->fetchAll();

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
    ?>
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" style="margin-top: 60px;">
                <?php if ($canManageCustomerFeatureToggles) { ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="well customer-feature-toggle-panel">
                                <form method="post" action="masterCreator.php">
                                    <input type="hidden" name="action" value="save_customer_feature_toggles">
                                    <div class="feature-toggle-header">
                                        <div>
                                            <div class="feature-toggle-kicker">Super Admin Controls</div>
                                            <h4 class="feature-toggle-title">Customer Account Feature Visibility</h4>
                                            <p class="feature-toggle-note">Off karne par feature customerAccount.php me hide rahega jab tak yahan se dobara on nahi kiya jata.</p>
                                        </div>
                                        <button type="submit" class="btn btn-info feature-toggle-save"><i class="fa fa-save"></i> Save Toggles</button>
                                    </div>
                                    <?php if ($featureToggleMessage !== '') { ?>
                                        <div class="alert alert-success" style="margin: 12px 0 0;"><?php echo htmlspecialchars($featureToggleMessage, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php } ?>
                                    <div class="feature-toggle-grid">
                                        <label class="feature-toggle-card">
                                            <span>
                                                <strong>docoraAI</strong>
                                                <small>Customer account AI review button</small>
                                            </span>
                                            <input type="checkbox" name="customer_docora_ai_visible" value="1" <?php echo $customerDocoraAiVisible ? 'checked' : ''; ?>>
                                            <span class="feature-switch"><span></span></span>
                                        </label>
                                        <label class="feature-toggle-card">
                                            <span>
                                                <strong>Date Graph</strong>
                                                <small>Customer chronological dates graph</small>
                                            </span>
                                            <input type="checkbox" name="customer_date_graph_visible" value="1" <?php echo $customerDateGraphVisible ? 'checked' : ''; ?>>
                                            <span class="feature-switch"><span></span></span>
                                        </label>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="row">
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
                                        <a class="editFirm" data-id="<?php echo $firmDetails['firm_id'] ?>" style="float: left" title="Edit AITF MCL"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $firmDetails['firm_name'] ?>
                                        <a class="deleteFirm" data-id="<?php echo $firmDetails['firm_id'] ?>" style="float: right" title="Delete AITF MCL"><i class="fa fa-trash"></i></a>
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
                                            <a class="editTaxation" edit-Taxation-id="<?php echo $taxation['taxation_id'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp; <span><b>Type of tax: </b> <?php echo $taxation['type_of_tax']; ?> </span> &emsp;<span><b>Rate: </b> <?php
                                                echo $taxation['rate'];
                                                echo '%';
                                                ?> 
                                            </span><a class="deleteTaxation" taxation-id="<?php echo $taxation['taxation_id'] ?>" title="Delete taxcation" style="float: right"><i class="fa fa-trash"></i></a>
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
                            <div class="col-xs-8  text-center btn  qms_complete_btn ">
                                <span><i class="fa ">Auditors</i></span>
                            </div>
                            <div class="col-xs-4  text-center btn qms_complete_btn addNewAuditors">
                                <span><i class="fa fa-plus-square "> Add Auditors</i></span>
                            </div>
                        </div>
                        <div class="row" style="">
                            <div class="well" style="max-height: 200px; overflow: auto;">
                                <ul class="list-group checked-list-box operateAuditors">
                                    <?php foreach ($auditorsList as $auditor) { ?>
                                        <li class="list-group-item">
                                            <a class="editAuditor" data-id="<?php echo $auditor['auditors_id'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $auditor['auditor_name'] ?>
                                            <a class="deleteAuditor" data-id="<?php echo $auditor['auditors_id'] ?>" style="float: right"><i class="fa fa-trash"></i></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newAuditors"></div>
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
                                            <a class="editStandards" data-id="<?php echo $standards['id'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $standards['standards_name'] ?>
                                            <a class="deleteStandards" data-id="<?php echo $standards['id'] ?>" style="float: right"><i class="fa fa-trash"></i></a>
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
                                            <a class="editAccreditation" data-id="<?php echo $accreditation['accreditation_id'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $accreditation['accreditation_name'] ?>
                                            <a class="deleteAccreditation" data-id="<?php echo $accreditation['accreditation_id'] ?>" style="float: right"><i class="fa fa-trash"></i></a>
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
                                            <a class="editLegalStatus" data-id="<?php echo $legalStatus['status_id'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $legalStatus['status'] ?>
                                            <a class="deleteLegalStatus" data-id="<?php echo $legalStatus['status_id'] ?>" style="float: right"><i class="fa fa-trash"></i></a>
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
                                            <a class="editIaf" data-id="<?php echo $iafCode['iaf_id'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $iafCode['iaf_code'] ?>
                                            <a class="deleteIaf" data-id="<?php echo $iafCode['iaf_id'] ?>" style="float: right"><i class="fa fa-trash"></i></a>
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
                                            <a class="editNaceClass" data-id="<?php echo $naceCode['iaf_id'] ?>" data-id1="<?php echo $naceCode['nace_id'] ?>" data-id2="<?php echo $naceCode['nace_code'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $naceCode['nace_code'] ?>
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
                                            <a class="editStatus" data-id="<?php echo $stat['id'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $stat['status'] ?>
                                            <a class="deleteStatus" data-id="<?php echo $stat['id'] ?>" style="float: right"><i class="fa fa-trash"></i></a>
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
                                            <a class="editcategories" data-id="<?php echo $categories['categories_id'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $categories['categories_name'] ?>
                                            <a class="deletecategories" data-id="<?php echo $categories['categories_id'] ?>" style="float: right"><i class="fa fa-trash"></i></a>
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
                                            <a class="editAddtos" data-id="<?php echo $tos['add_to_id'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $tos['add_to_type'] ?>
                                            <a class="deleteAddto" data-id="<?php echo $tos['add_to_id'] ?>" style="float: right"><i class="fa fa-trash"></i></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row newAddto"></div>
                    </div><div class="col-sm-6">
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
                                            <a class="editHandlers" data-id="<?php echo $hndl['id'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $hndl['handler_name'] ?>
                                            <a class="deleteHandlers" data-id="<?php echo $hndl['id'] ?>" style="float: right"><i class="fa fa-trash"></i></a>
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
                                        <a class="editCertificationNo" data-id="<?php echo $firmDetails['firm_id'] ?>" data-val="<?php echo $firmDetails['certification_no'] ?>" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<?php echo $firmDetails['certification_no'] ?>
                                    </li>                                    
                                </ul>
                            </div>
                        </div>
                        <div class="row changeCertification"></div>
                    </div>
                </div>
                <div id="delete-details" style="display: none">
                    <div id="loading-div" class="ui-corner-all">
                        <span style="font-size: 30pt;"><i style="padding: 5px;" class="fa fa-warning text-warning"></i></span>
                        <div>
                            <span style="font-size: 11pt; padding: 10px" class="text-primary meeting-cancel-confirm-message">Are you sure that you want to delete details?</span>
                        </div>
                        <div style="padding: 3px;">
                            <button title="Click here to delete" class="btn btn-primary btn-round btn-round-sm btn-sm delete-confirm-ok">Ok</button>
                            <button title="Click here to cancel" class="btn btn-danger  btn-round btn-round-sm btn-sm delete-cancel">Cancel</button>
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
                    data: {auditor: $('.newAuditorsInput').val()},
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
                    success: function (data)
                    {
//                        alert(data);
                        $('.newHandler').empty();
                        $('.operateHandlers').empty();
                        for (var i in data) {
                            $('.operateHandlers').append('<li class="list-group-item"><a class="editHandlers" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['handler_name'] + '<a class="deleteHandlers" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        //    	                    alert(data);                    

                        $('.newHandler').empty();
                        $('.newHandler').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newHandlerInput" class="form-control newHandlerInput" id="newHandlerInput" placeholder="New Handler" value="' + data["handler_name"] + '"></div>\n\
                                            <input type="hidden" class="handlerId" name="handlerId" value="' + data["id"] + '">\n\
                                            <a class="btn btn-info btn-xs createHandler" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newHandlerError" style="display: none">\n\
                                            <small><span id="newHandlerErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div>\n\
                                            </div>\n\
                                            </div>');
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
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
                    error: function (data, status, err)
                    {
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
                        success: function (data)
                        {
                            //    	                            alert(data);
                            $('#delete-details').hide();
                            $('.newHandler').empty();
                            $('.operateHandlers').empty();
                            for (var i in data) {
                                $('.operateHandlers').append('<li class="list-group-item"><a class="editHandlers" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['handler_name'] + '<a class="deleteHandlers" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err)
                        {
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
                        success: function (data)
                        {
                            //    	                            alert(data);
                            $('#delete-details').hide();
                            $('.newStatus').empty();
                            $('.operateStatus').empty();
                            for (var i in data) {
                                $('.operateStatus').append('<li class="list-group-item"><a class="editStatus" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['status'] + '<a class="deleteStatus" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err)
                        {
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
                        success: function (data)
                        {
                            //    	                            alert(data);
                            $('#delete-details').hide();
                            $('.newAddto').empty();
                            $('.operateAddto').empty();
                            for (var i in data) {
                                $('.operateAddto').append('<li class="list-group-item"><a class="editAddtos" data-id="' + data[i]['add_to_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['add_to_type'] + '<a class="deleteAddto" data-id="' + data[i]['add_to_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err)
                        {
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
                    success: function (data)
                    {
                        // alert(data);
                        $('.newStatus').empty();
                        $('.operateStatus').empty();
                        for (var i in data) {
                            $('.operateStatus').append('<li class="list-group-item"><a class="editStatus" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['status'] + '<a class="deleteStatus" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
//                        alert(data);
                        $('.operateCertNo').empty();
                        $('.changeCertification').empty();
                        $('.operateCertNo').append('<li class="list-group-item"><a class="editCertificationNo" data-id="' + data['firm_id'] + '" data-val="' + data['certification_no'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data['certification_no'] + '</li>');

                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
//                        alert(data);
                        $('.newAddto').empty();
                        $('.operateAddto').empty();
                        for (var i in data) {
                            $('.operateAddto').append('<li class="list-group-item"><a class="editAddtos" data-id="' + data[i]['add_to_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['add_to_type'] + '<a class="deleteAddto" data-id="' + data[i]['add_to_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
//                        alert(data);
                        $('.newAddto').empty();
                        $('.operateAddto').empty();
                        for (var i in data) {
                            $('.operateAddto').append('<li class="list-group-item"><a class="editAddtos" data-id="' + data[i]['add_to_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['add_to_type'] + '<a class="deleteAddto" data-id="' + data[i]['add_to_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        $('.newAddto').empty();
                        $('.newAddto').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                                        <div class="col-sm-12 form-group float-label-control">\n\
                                                        <input type="text" name="newAddtoInput" class="form-control newAddtoInpute" placeholder="New Add To" value="' + data["add_to_type"] + '"></div>\n\
                                                        <input type="hidden" class="addtoIde" name="addtoId" value="' + data["add_to_id"] + '">\n\
                                                        <a class="btn btn-info btn-xs editcreateAddTo" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                                        <div id="newAddToError" style="display: none">\n\
                                                        <small><span id="newAddToErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                                        </div>\n\
                                                        </div>\n\
                                                        </div>');
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        //    	                    alert(data);                    

                        $('.newStatus').empty();
                        $('.newStatus').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                                        <div class="col-sm-12 form-group float-label-control">\n\
                                                        <input type="text" name="newStatusInput" class="form-control newStatusInput" id="newStatusInput" placeholder="New Status" value="' + data["status"] + '"></div>\n\
                                                        <input type="hidden" class="statusId" name="statusId" value="' + data["id"] + '">\n\
                                                        <a class="btn btn-info btn-xs createStatus" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                                        <div id="newLegalStatusError" style="display: none">\n\
                                                        <small><span id="newLegalStatusErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                                        </div>\n\
                                                        </div>\n\
                                                        </div>');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
                    }
                });
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
                    success: function (data)



                    
                    {
                        $(".auditorName").val(data['auditor_name']);
                        $(".auditorNumber").val(data['auditor_number']);
                        $(".auditorEmail").val(data['auditor_email']);
                        $(".auditor_id").val(data['auditors_id']);
                        $(".auditorabbr").val(data['abbrevation']);
                        $("input").trigger("change");
                        $(".auditoradd").modal("show");
                        //                    alert(data);
//                        $('.newAuditors').empty();
//                        $('.newAuditors').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
//                                            <div class="col-sm-12 form-group float-label-control">\n\
//                                            <input type="text" name="newAuditorsInput" class="form-control newAuditorsInput" id="newAuditorsInput" placeholder="New Auditors" value="' + data['auditor_name'] + '"></div>\n\
//                                            <a class="btn btn-info btn-xs createAuditors" style="float: right;"><i class="fa fa-check"></i></a>\n\
//                                            <input type="hidden" class="auditorId" name="auditorId" value="' + data['auditors_id'] + '">\n\
//                                            <div id="newAuditorsError" style="display: none">\n\
//                                            <small><span id="newAuditorsErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
//                                            </div></div></div>');
                    },
                    error: function (data, status, err)
                    {
                        alert(status);
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
                    success: function (data)
                    {
                        //                    alert(data);
                        $('.newStandards').empty();
                        $('.newStandards').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newStandardsInput" class="form-control newStandardsInput" id="newStandardsInput" placeholder="New Standards" value="' + data['standards_name'] + '"></div>\n\
                                            <input type="hidden" class="standardId" name="standardId" value="' + data['id'] + '">\n\
                                            <a class="btn btn-info btn-xs createStandards" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newStandardsError" style="display: none">\n\
                                            <small><span id="newStandardsErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div></div></div>');
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        //                    alert(data);
                        $('.newAccreditation').empty();
                        $('.newAccreditation').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newAccreditationInput" class="form-control newAccreditationInput" id="newAccreditationInput" placeholder="New Accreditation" value="' + data['accreditation_name'] + '"></div>\n\
                                            <input type="hidden" class="accreditationId" name="accreditationId" value="' + data['accreditation_id'] + '">\n\
                                            <a class="btn btn-info btn-xs createAccreditation" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newAccreditationError" style="display: none">\n\
                                            <small><span id="newAccreditationErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div></div></div>');
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        //                    alert(data);
                        $('.newcategories').empty();
                        $('.newcategories').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newcategoriesInput" class="form-control newcategoriesInput" id="newcategoriesInput" placeholder="New categories" value="' + data['categories_name'] + '"></div>\n\
                                            <input type="hidden" class="categoriesId" name="categoriesId" value="' + data['categories_id'] + '">\n\
                                            <a class="btn btn-info btn-xs createcategories" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newcategoriesError" style="display: none">\n\
                                            <small><span id="newcategoriesErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div></div></div>');
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        //                    alert(data);
                        $('.newLegalStatus').empty();
                        $('.newLegalStatus').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newLegalStatusInput" class="form-control newLegalStatusInput" id="newLegalStatusInput" placeholder="New Legal Status" value="' + data['status'] + '"></div>\n\
                                            <input type="hidden" class="legalStatusId" name="legalStatusId" value="' + data['status_id'] + '">\n\
                                            <a class="btn btn-info btn-xs createLegalStatus" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newLegalStatusError" style="display: none">\n\
                                            <small><span id="newLegalStatusErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div>\n\
                                            </div>\n\
                                            </div>');
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
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
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
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
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
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
                    error: function (data, status, err)
                    {
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
                $('.newStandards').empty();
                $('.newStandards').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newStandardsInput" class="form-control newStandardsInput" id="newStandardsInput" placeholder="New Standards" value=""></div>\n\
                                            <input type="hidden" class="standardId" name="standardId" value="">\n\
                                            <a class="btn btn-info btn-xs createStandards" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newStandardsError" style="display: none">\n\
                                            <small><span id="newStandardsErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div></div></div>');
            });

            $(".addNewAccreditation").click(function () {
                $('.newAccreditation').empty();
                $('.newAccreditation').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newAccreditationInput" class="form-control newAccreditationInput" id="newAccreditationInput" placeholder="New Accreditation" value=""></div>\n\
                                            <input type="hidden" class="accreditationId" name="accreditationId" value="">\n\
                                            <a class="btn btn-info btn-xs createAccreditation" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newAccreditationError" style="display: none">\n\
                                            <small><span id="newAccreditationErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div></div></div>');
            });
            $(".addNewcategories").click(function () {
                $('.newcategories').empty();
                $('.newcategories').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newcategoriesInput" class="form-control newcategoriesInput" id="newcategoriesInput" placeholder="New categories" value=""></div>\n\
                                            <input type="hidden" class="categoriesId" name="categoriesId" value="">\n\
                                            <a class="btn btn-info btn-xs createcategories" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newcategoriesError" style="display: none">\n\
                                            <small><span id="newcategoriesErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div></div></div>');
            });

            $(".addNewLegalStatus").click(function () {
                $('.newLegalStatus').empty();
                $('.newLegalStatus').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newLegalStatusInput" class="form-control newLegalStatusInput" id="newLegalStatusInput" placeholder="New Legal Status" value=""></div>\n\
                                            <input type="hidden" class="legalStatusId" name="legalStatusId" value="">\n\
                                            <a class="btn btn-info btn-xs createLegalStatus" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newLegalStatusError" style="display: none">\n\
                                            <small><span id="newLegalStatusErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div>\n\
                                            </div>\n\
                                            </div>');
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
                $('.newStatus').empty();
                $('.newStatus').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newStatusInput" class="form-control newStatusInput" id="newStatusInput" placeholder="New Status" value=""></div>\n\
                                            <input type="hidden" class="statusId" name="statusId" value="">\n\
                                            <a class="btn btn-info btn-xs createStatus" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newLegalStatusError" style="display: none">\n\
                                            <small><span id="newLegalStatusErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div>\n\
                                            </div>\n\
                                            </div>');

            });

            $(".addNewAddto").click(function () {
                $('.newAddto').empty();
                $('.newAddto').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newAddtoInput" class="form-control newAddtoInput" id="newAddtoInput" placeholder="New Add To" value=""></div>\n\
                                            <input type="hidden" class="addtoId" name="addtoId" value="">\n\
                                            <a class="btn btn-info btn-xs createAddTo" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newAddToError" style="display: none">\n\
                                            <small><span id="newAddToErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div>\n\
                                            </div>\n\
                                            </div>');
            });
            
            $(".addNewHandler").click(function (){
                $('.newHandler').empty();
                $('.newHandler').append('<div class="col-sm-12"><div class="well" style="height: 100px;">\n\
                                            <div class="col-sm-12 form-group float-label-control">\n\
                                            <input type="text" name="newHandlerInput" class="form-control newHandlerInput" id="newHandlerInput" placeholder="New Handler" value=""></div>\n\
                                            <input type="hidden" class="handlerId" name="handlerId" value="">\n\
                                            <a class="btn btn-info btn-xs createHandler" style="float: right;"><i class="fa fa-check"></i></a>\n\
                                            <div id="newHandlerError" style="display: none">\n\
                                            <small><span id="newHandlerErrorMsg" style="color: #c9513c; font-style: italic;" ></span></small>\n\
                                            </div>\n\
                                            </div>\n\
                                            </div>');
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
                    success: function (data)
                    {
                        //alert(data);
                        $('.newTaxation').val(data['type_of_tax']);
                        $('.newRate').val(data['rate']);
                        $('.taxation_id_update').val(data['taxation_id']);
                        $('#taxationAdd').modal('show');
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        //                    alert(data);
                        $('.newAuditors').empty();
                        $('.operateAuditors').empty();
                        for (var i in data) {
                            $('.operateAuditors').append('<li class="list-group-item"><a class="editAuditor" data-id="' + data[i]['auditors_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['auditor_name'] + '<a class="deleteAuditor" data-id="' + data[i]['auditors_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        $('.newStandards').empty();
                        $('.operateStandards').empty();
                        for (var i in data) {
                            $('.operateStandards').append('<li class="list-group-item"><a class="editStandards" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['standards_name'] + '<a class="deleteStandards" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        //                    alert(data);
                        $('.newAccreditation').empty();
                        $('.operateAccreditation').empty();
                        for (var i in data) {
                            $('.operateAccreditation').append('<li class="list-group-item"><a class="editAccreditation" data-id="' + data[i]['accreditation_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['accreditation_name'] + '<a class="deleteAccreditation" data-id="' + data[i]['accreditation_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        //                    alert(data);
                        $('.newcategories').empty();
                        $('.operatecategories').empty();
                        for (var i in data) {
                            $('.operatecategories').append('<li class="list-group-item"><a class="editcategories" data-id="' + data[i]['categories_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['categories_name'] + '<a class="deletecategories" data-id="' + data[i]['categories_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        //                    alert(data);
                        $('.newLegalStatus').empty();
                        $('.operateLegalStatus').empty();
                        for (var i in data) {
                            $('.operateLegalStatus').append('<li class="list-group-item"><a class="editLegalStatus" data-id="' + data[i]['status_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['status'] + '<a class="deleteLegalStatus" data-id="' + data[i]['status_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        //                    alert(data);
                        $('.newNace').empty();
                        $('.operateNace').empty();
                        for (var i in data) {
                            $('.operateNace').append('<li class="list-group-item"><a class="editNace" data-id="' + data[i]['nace_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['nace_code'] + '<a class="deleteNace" data-id="' + data[i]['nace_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err)
                    {
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
                    success: function (data)
                    {
                        //                    alert(data);
                        $('.newIaf').empty();
                        $('.operateIaf').empty();
                        for (var i in data) {
                            $('.operateIaf').append('<li class="list-group-item"><a class="editIaf" data-id="' + data[i]['iaf_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['iaf_code'] + '<a class="deleteIaf" data-id="' + data[i]['iaf_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                        }
                    },
                    error: function (data, status, err)
                    {
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
                        success: function (data)
                        {
                            //                    alert(data);
                            $('#delete-details').hide();
                            $('.operateAuditors').empty();
                            for (var i in data) {
                                $('.operateAuditors').append('<li class="list-group-item"><a class="editAuditor" data-id="' + data[i]['auditors_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['auditor_name'] + '<a class="deleteAuditor" data-id="' + data[i]['auditors_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err)
                        {
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
                        success: function (data)
                        {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newStandards').empty();
                            $('.operateStandards').empty();
                            for (var i in data) {
                                $('.operateStandards').append('<li class="list-group-item"><a class="editStandards" data-id="' + data[i]['id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['standards_name'] + '<a class="deleteStandards" data-id="' + data[i]['id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err)
                        {
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
                        success: function (data)
                        {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newAccreditation').empty();
                            $('.operateAccreditation').empty();
                            for (var i in data) {
                                $('.operateAccreditation').append('<li class="list-group-item"><a class="editAccreditation" data-id="' + data[i]['accreditation_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['accreditation_name'] + '<a class="deleteAccreditation" data-id="' + data[i]['accreditation_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err)
                        {
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
                        success: function (data)
                        {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newcategories').empty();
                            $('.operatecategories').empty();
                            for (var i in data) {
                                $('.operatecategories').append('<li class="list-group-item"><a class="editcategories" data-id="' + data[i]['categories_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['categories_name'] + '<a class="deletecategories" data-id="' + data[i]['categories_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err)
                        {
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
                        success: function (data)
                        {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newLegalStatus').empty();
                            $('.operateLegalStatus').empty();
                            for (var i in data) {
                                $('.operateLegalStatus').append('<li class="list-group-item"><a class="editLegalStatus" data-id="' + data[i]['status_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['status'] + '<a class="deleteLegalStatus" data-id="' + data[i]['status_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err)
                        {
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
                        success: function (data)
                        {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newNace').empty();
                            $('.operateNace').empty();
                            for (var i in data) {
                                $('.operateNace').append('<li class="list-group-item"><a class="editNace" data-id="' + data[i]['nace_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['nace_code'] + '<a class="deleteNace" data-id="' + data[i]['nace_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err)
                        {
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
                        success: function (data)
                        {
                            //                        alert(data);
                            $('#delete-details').hide();
                            $('.newIaf').empty();
                            $('.operateIaf').empty();
                            for (var i in data) {
                                $('.operateIaf').append('<li class="list-group-item"><a class="editIaf" data-id="' + data[i]['iaf_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;' + data[i]['iaf_code'] + '<a class="deleteIaf" data-id="' + data[i]['iaf_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                            window.location.reload();
                        },
                        error: function (data, status, err)
                        {
                            alert(status);
                        }
                    });
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
                        success: function (data)
                        {
                            //                    alert(data);
                            $('#delete-details').hide();
                            $('.operateTaxation').empty();
                            for (var i in data) {
                                $('.operateTaxation').append('<li class="list-group-item"><a class="editTaxation" edit-Taxation-id="' + data[i]['taxation_id'] + '" style="float: left"><i class="fa fa-pencil-square"></i></a>&emsp;<span><b>Type of tax: </b>' + data[i]['type_of_tax'] + '</span>&emsp;<span><b>Rate: </b>' + data[i]['rate'] + '%</span><a class="deleteTaxation" taxation-id="' + data[i]['taxation_id'] + '" style="float: right"><i class="fa fa-trash"></i></a></li>');
                            }
                        },
                        error: function (data, status, err)
                        {
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
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            opacity: 1.0;
            behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */
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
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            opacity: 1.0;
            behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */
        }

        #loading-div-background-resend {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.5);
            width: 100%;
            height: 100%;
        }

        #loading-div-background-meetedit {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.5);
            width: 100%;
            height: 100%;
        }

        .customer-feature-toggle-panel {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #111827 100%);
            border: 1px solid rgba(59, 130, 246, 0.24);
            border-radius: 12px;
            color: #e5eefc;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.22);
        }

        .feature-toggle-header {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            justify-content: space-between;
        }

        .feature-toggle-kicker {
            color: #93c5fd;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.09em;
            text-transform: uppercase;
        }

        .feature-toggle-title {
            color: #fff;
            font-size: 18px;
            font-weight: 800;
            margin: 4px 0;
        }

        .feature-toggle-note {
            color: #b6c3d6;
            margin: 0;
        }

        .feature-toggle-save {
            border-radius: 999px;
            font-weight: 700;
            padding: 8px 16px;
        }

        .feature-toggle-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            margin-top: 16px;
        }

        .feature-toggle-card {
            align-items: center;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin: 0;
            padding: 14px;
        }

        .feature-toggle-card strong {
            color: #fff;
            display: block;
            font-size: 15px;
            margin-bottom: 2px;
        }

        .feature-toggle-card small {
            color: #aab8cc;
            display: block;
            font-weight: 500;
        }

        .feature-toggle-card input {
            display: none;
        }

        .feature-switch {
            background: #475569;
            border-radius: 999px;
            flex: 0 0 auto;
            height: 28px;
            padding: 3px;
            transition: background 0.2s ease;
            width: 54px;
        }

        .feature-switch span {
            background: #fff;
            border-radius: 50%;
            display: block;
            height: 22px;
            transition: transform 0.2s ease;
            width: 22px;
        }

        .feature-toggle-card input:checked + .feature-switch {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }

        .feature-toggle-card input:checked + .feature-switch span {
            transform: translateX(26px);
        }

        /* =====================================================================
           PREMIUM DARK MODE  —  masterCreator.php
           Palette mirrors homePage.php: deep charcoal surfaces, soft slate text,
           jewel-toned accents. Toggle handled globally (head.php + js/app.js).
           ===================================================================== */
        html.theme-dark {
            --hpd-bg:        #0a0e14;
            --hpd-surface:   #111722;
            --hpd-surface-2: #161d2b;
            --hpd-border:    #232c3d;
            --hpd-border-2:  #2e3a4f;
            --hpd-text:      #dbe3f0;
            --hpd-text-dim:  #93a1b8;
            --hpd-text-faint:#6b7890;
            --hpd-accent:    #4a9eff;
        }

        html.theme-dark #page-content-wrapper {
            background: var(--hpd-bg);
            color: var(--hpd-text);
        }
        html.theme-dark .page-content.inset { background: var(--hpd-bg); }

        /* Well containers (list panels) */
        html.theme-dark .well {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.4);
        }

        /* List groups */
        html.theme-dark .list-group-item {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
            color: var(--hpd-text);
        }
        html.theme-dark .list-group-item:hover { background: var(--hpd-surface-2); }
        html.theme-dark .list-group-item a { color: var(--hpd-text-dim); }
        html.theme-dark .list-group-item a:hover { color: #7fb6ef; }
        html.theme-dark .list-group-item b { color: #eaf1fb; }

        /* Section heading buttons stay orange — reads well on dark; just deepen border */
        html.theme-dark .qms_complete_btn { border-color: var(--hpd-border-2); }

        /* Panels */
        html.theme-dark .panel {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
        }
        html.theme-dark .panel-heading {
            background: linear-gradient(180deg, #161d2b 0%, #121926 100%);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark .panel-title { color: #eaf1fb; }
        html.theme-dark .panel-body { color: var(--hpd-text); }

        /* Tables */
        html.theme-dark .table { color: var(--hpd-text); }
        html.theme-dark .table thead th {
            background: linear-gradient(180deg, #161d2b 0%, #121926 100%);
            border-bottom: 1px solid var(--hpd-border-2) !important;
            color: #aeb9cc;
        }
        html.theme-dark .table tbody td,
        html.theme-dark .table tbody th {
            border-top: 1px solid var(--hpd-border) !important;
            color: var(--hpd-text);
        }
        html.theme-dark .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(255, 255, 255, 0.018); }
        html.theme-dark .table-hover tbody tr:hover td { background-color: var(--hpd-surface-2); }

        /* Form controls */
        html.theme-dark .form-control {
            background: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark .form-control:focus {
            border-color: var(--hpd-accent);
            box-shadow: 0 0 0 4px rgba(74, 158, 255, 0.18);
            background: var(--hpd-surface-2);
        }
        html.theme-dark .form-control::placeholder { color: var(--hpd-text-faint); }
        html.theme-dark .form-control[disabled],
        html.theme-dark .form-control[readonly] { background: #0e131c; color: var(--hpd-text-dim); }
        html.theme-dark select.form-control option { background: var(--hpd-surface-2); color: var(--hpd-text); }
        html.theme-dark .form-group label,
        html.theme-dark .control-label,
        html.theme-dark .float-label-control label { color: var(--hpd-text-dim); }
        html.theme-dark .input-group-addon {
            background: #1c2534;
            border-color: var(--hpd-border-2);
            color: var(--hpd-text-dim);
        }

        /* Modals (shared model includes) */
        html.theme-dark .modal-content {
            background: var(--hpd-surface);
            border: 1px solid var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark .modal-header,
        html.theme-dark .modal-footer { border-color: var(--hpd-border); }
        html.theme-dark .modal-title { color: var(--hpd-text); }
        html.theme-dark .modal-body { color: var(--hpd-text); }
        html.theme-dark .close { color: var(--hpd-text); text-shadow: none; opacity: 0.7; }
        html.theme-dark .parentrow { border-color: var(--hpd-border); }

        /* Buttons */
        html.theme-dark .btn-info {
            background: linear-gradient(135deg, #1d63b0 0%, #2f81d6 100%);
            border-color: #2f81d6;
            color: #eaf1fb;
        }
        html.theme-dark .btn-info:hover,
        html.theme-dark .btn-info:focus { background: linear-gradient(135deg, #2470c4 0%, #3a8ee0 100%); color: #fff; }
        html.theme-dark .btn-default {
            background: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark .btn-default:hover { background: #1f2a3d; color: #fff; }

        /* Loading overlays */
        html.theme-dark #loading-div-background,
        html.theme-dark #loading-div-background-resend,
        html.theme-dark #loading-div-background-meetedit { background: rgba(4, 8, 14, 0.6); }
        html.theme-dark #loading-div,
        html.theme-dark #loading-div-user-info {
            background-color: var(--hpd-surface);
            border-color: var(--hpd-accent);
            color: var(--hpd-text);
        }
    </style>

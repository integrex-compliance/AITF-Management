<?php
error_reporting(E_WARNING | E_PARSE);
ob_start();
session_start();

include 'head.php';
include 'modalForms.php';
include 'masterModel.php';
include 'auditApplicationModal.php';
include 'contractReviewModal.php';
include 'auditNotificationModal.php';
include 'reviewReportModal.php';
include 'annexIIModel.php';
include 'scheduleStageModal.php';
include 'cdcModal.php';
include 'auditScheduleIIModal.php';
include 'controlFormatModal1.php';
include 'customerSurveyModal.php';
include 'controlFormatModal2.php';
include 'csaModal.php';
include 'assessmentPlanningModal.php';
include 'auditNotificationIIModal.php';
include 'certificationPrintModal.php';
include 'schedule1servelance.php';
include 'schedule2servelance.php';
include 'utility.php';
?>
<head>
    <title>AITF</title>
</head>
<style>
    /*@media print{@page {size: landscape}}*/

    /* Slightly compact styling for Application Received menu item */
    .sidebar-nav a.app-rec-nav {
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }
</style>

<!--<div id="wrapper" class="active">-->
<?php
if (!Utitlity::checkIfLoggedIn()) {
    header("Location: login");
    exit;
} else if (isset($pageIdentifier) && $pageIdentifier == "Home Page") {
    $addClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Add User") {
    $addUserClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Master Data") {
    $addMasterClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Mktg") {
    $addMarketingClass = "active";
    if (!(Utitlity::checkForUserMarketing() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Audit Planning") {
    $addWorkingClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForUserConsultant() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Finance Page") {
    $addFinanceClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForUserFinance() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "TDS list") {
    $addtds = "active";
    if (!(Utitlity::checkForMainAdmin() || Utitlity::checkForUserAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Due letter") {
    $adddueClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForMainAdmin() || Utitlity::checkForOfficeStaff())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "sent letter") {
    $addsentClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForMainAdmin() || Utitlity::checkForOfficeStaff())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Invoices") {
    $addInvoiceClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Quot") {
    $addquotClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "agreement") {
    $addagreementClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Recycle") {
    $addRecycleClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Audit Records") {
    $addAuditClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "cert issue list") {
    $addcertissueClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "audit perf") {
    $addauditperClass = "active";
    if (Utitlity::checkExternalUser()) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Client Analysis") {
    $clientanalysis = "active";
    if (Utitlity::checkExternalUser()) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "cdc objrctive") {
    $cdcobj = "active";
    if (Utitlity::checkExternalUser()) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "app review objrctive") {
    $apprevobj = "active";
    if (Utitlity::checkExternalUser()) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Application Received") {
    $addAppReceivedClass = "active";
    if (!(Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Document Upload") {
    $docobj = "active";
    if (Utitlity::checkExternalUser()) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "IAF Dashboard") {
    $iafClass = "active";
    if (Utitlity::checkExternalUser()) {
        header("Location: login");
        exit;
    }
}

function simpleDateConversion($inputStr) {
    if ($inputStr === "") { 
        return "";
    }
    $strSplit = explode("-", $inputStr);
    if (sizeOf($strSplit) > 1)
        return $strSplit[2] . "." . $strSplit[1] . "." . $strSplit[0];
    else
        return $inputStr;
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end   
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function getFlatDate($inputStr) {
    if ($inputStr === "") {
        return "";
    }
    $strSplit = explode("-", $inputStr);
    if (sizeOf($strSplit) > 1)
        return $strSplit[0] . "" . $strSplit[1] . "" . $strSplit[2];
    else
        return $inputStr;
}
?>

<div id="sidebar-wrapper" class="hidden-xs">
    <ul id="sidebar_menu" class="sidebar-nav">
        <li class="sidebar-brand"><a class="" id="menu-toggle" href="#">AITF<span id="main_icon" class="glyphicon glyphicon-align-justify"></span></a></li>
    </ul>
    <ul class="sidebar-nav" id="sidebar">
            <li><a href="iaf_dashboard.php" class="animate <?php echo isset($iafClass) ? $iafClass : ''; ?>">IAF DASHBOARD<i class="sub_icon fa fa-dashboard fa-2x" style="vertical-align:middle"></i></a></li>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff()) { ?>
            <?php $unreadBadge = isset($GLOBALS['unread_app_count']) ? (int)$GLOBALS['unread_app_count'] : 0; ?>
            <li>
                <a href="applicationReceived.php" class="animate app-rec-nav <?php echo $addAppReceivedClass ?>">
                    APPLICATION RECEIVED
                    <?php if ($unreadBadge > 0) { ?>
                        <span style="margin-left:8px; padding:2px 7px; border-radius:999px; background:#ef4444; color:#fff; font-size:11px; font-weight:700;"><?php echo $unreadBadge; ?></span>
                    <?php } ?>
                </a>
            </li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
            <li><a href="homePage.php" class="animate <?php echo $addClass ?>">CLIENTS<i class="sub_icon fa fa-building fa-2x" style="vertical-align:middle"></i></a></li>       
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForUserMarketing() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
            <li><a href="marketingPage.php" class="animate <?php echo $addMarketingClass ?>">MARKETING<i class="sub_icon fa fa-briefcase fa-2x" style="vertical-align:middle"></i></a></li>        
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForUserFinance() || Utitlity::checkForUserAdmin()) { ?>
            <li><a href="paymentPage.php" class="animate <?php echo $addFinanceClass ?>">PAYMENT<i class="sub_icon fa fa-money fa-2x" style="vertical-align:middle"></i></a></li>       
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff() || Utitlity::checkForUserConsultant()) { ?>
            <li><a href="auditPlanning.php" class="animate <?php echo $addWorkingClass ?>">AUDIT PLANNING<i class="sub_icon fa fa-money fa-2x" style="vertical-align:middle"></i></a></li>        
            <!--<li><a href="idealPage.php" class="animate ">IDEAL<i class="sub_icon fa fa-briefcase fa-2x" style="vertical-align:middle"></i></a></li>-->            
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin()) { ?>
            <li><a href="masterCreator.php" class="animate <?php echo $addMasterClass ?>">MASTER CREATOR<i class="sub_icon fa fa-wrench  fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>            
            <li><a href="listOfTds.php" class="animate <?php echo $addtds ?>">TDS DEDUCTED<i class="sub_icon fa fa-list  fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
            <li><a href="intimationLetter.php" class="animate <?php echo $adddueClass ?>">INTIMATION DUE<i class="sub_icon fa fa-envelope-o fa-2x" style="vertical-align:middle"></i></a></li>
            <li><a href="sentintimation.php" class="animate <?php echo $addsentClass ?>">INTIMATION SENT<i class="sub_icon fa fa-envelope fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
            <!--<li><a href="clientList.php" class="animate">CLIENT LIST<i class="sub_icon fa fa-list  fa-2x" style="vertical-align:middle"></i></a></li>-->
            <li><a href="invoicePage.php" class="animate <?php echo $addInvoiceClass ?>">INVOICES<i class="sub_icon fa fa-inr fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
            <li><a href="quotationPage.php" class="animate <?php echo $addquotClass ?>">QUOTATIONS<i class="sub_icon fa fa-quote-left fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff()) { ?>
            <li><a href="agreementPage.php" class="animate <?php echo $addagreementClass ?>">AGREEMENTS<i class="sub_icon fa fa-quote-left fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff()) { ?>
            <li><a href="auditPage.php" class="animate <?php echo $addAuditClass ?>">AUDIT LIST<i class="sub_icon fa fa-list-alt fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin()) { ?>
            <li><a href="addUsers.php" class="animate <?php echo $addUserClass ?>">ADD USER<i class="sub_icon fa fa-user-plus fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin()) { ?>
            <li><a href="clientRecycle.php" class="animate <?php echo $addRecycleClass ?>">RECYCLE BIN<i class="sub_icon fa fa-trash-o fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (!(Utitlity::checkClient() || Utitlity::checkExternalUser())) { ?>
            <li><a href="auditanalysis.php" class="animate <?php echo $addcertissueClass ?>">CERT. ISSUED LIST<i class="sub_icon fa fa-newspaper-o fa-2x" style="vertical-align:middle"></i></a></li>
            <li><a href="qualityanalysis.php" class="animate <?php echo $addauditperClass ?>">AUDIT PERFORMANCE<i class="sub_icon fa fa-recycle fa-2x" style="vertical-align:middle"></i></a></li>
            <li><a href="clientanalysis.php" class="animate <?php echo $clientanalysis ?>">CLIENT ANALYSIS<i class="sub_icon fa fa-eye fa-2x" style="vertical-align:middle"></i></a></li>
            <!--<li><a href="cdcobjective.php" class="animate <?php echo $cdcobj ?>">CDC OBJECTIVE<i class="sub_icon fa fa-eye fa-2x" style="vertical-align:middle"></i></a></li>-->
            <li><a href="apprevobjective.php" class="animate <?php echo $apprevobj ?>">APP REV OBJECTIVE<i class="sub_icon fa fa-eye fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
            <li><a href="uploadingStatus.php" class="animate <?php echo $docobj ?>">DOC.UPLOAD STATUS<i class="sub_icon fa fa-eye fa-2x" style="vertical-align:middle"></i></a></li>
    </ul>
</div>
<!--do not display the customer add to customer-->
<?php if (!(Utitlity::checkClient() || Utitlity::checkExternalUser())) { ?> 
    <div class="float-button-wrapper" style="text-align:center">
        <a class="addAccountCss" style="text-align:center"><span class=" pull-right glyphicon glyphicon-plus "></span></a>
    </div>
<?php } ?>

<nav class="navbar navbar-fixed-top navbar-bootsnipp animate " role="navigation">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header ">
            <button type="button" class="navbar-toggle " data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only ">Toggle navigation</span>
                <span class="icon-bar "></span>
                <span class="icon-bar "></span>
                <span class="icon-bar "></span>
            </button>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse " id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                <!--Adding hidden menu items here -->
                <li class="visible-xs"><a href="iaf_dashboard.php" class="animate <?php //echo isset($iafClass) ? $iafClass : ''; ?>">IAF DASHBOARD</a></li>
                <li class="visible-xs"><a href="homePage.php" class="animate <?php //echo $addClass              ?>">CLIENTS</a></li>
                <li class="visible-xs"><a href="marketingPage.php" class="animate <?php //echo $addMarketingClass              ?>">MARKETING</a></li>
                <li class="visible-xs"><a href="paymentPage.php" class="animate <?php //echo $addMarketingClass              ?>">PAYMENT</a></li>
                <li class="visible-xs"><a href="auditPlanning.php" class="animate <?php //echo $addWorkingClass              ?>">AUDIT PLANNING</a></li>                
                <li class="visible-xs"><a href="masterCreator.php" class="animate <?php //echo $addFinanceClass              ?>">MASTER CREATOR</a></li>
                <li class="visible-xs"><a href="listOfTds.php" class="animate <?php //echo $addMarketingSetupClass              ?>">TDS DEDUCTED</a></li>
                <li class="visible-xs"><a href="intimationLetter.php" class="animate <?php //echo $addMasterClass              ?> ">INTEMATION LETTERS</a></li>
                <li class="visible-xs"><a href="clientList.php" class="animate <?php //echo $addUserClass              ?>">CLIENT LIST</a></li>
                <li class="visible-xs"><a href="invoicePage.php" class="animate <?php //echo $addUserClass              ?>">INVOICE</a></li>
                <li class="visible-xs"><a href="quotationPage.php" class="animate <?php //echo $addUserClass              ?>">QUOTATION</a></li>
                <li class="visible-xs"><a href="addUsers.php" class="animate <?php //echo $addUserClass              ?>">ADD USER</a></li>
                <li class="visible-xs"><a href="clientRecycle.php" class="animate <?php //echo $addUserClass              ?>">RECYCLE BIN</a></li>
                <!--<li><a href="clientRecycle.php" class="animate">RECYCLE CLIENT<i class="sub_icon fa fa-refresh fa-2x" style="vertical-align:middle"></i></a></li>-->
                <!-- Adding ends here -->
                <?php if (!(Utitlity::checkClient() || Utitlity::checkExternalUser())) { ?> 
                <li>
                    <input class="dropdown-toggle animate searchcustomer" data-toggle="dropdown" placeholder="Search Client" style="margin-top: 10px; padding-left: 30px; z-index: 2; border: 2px solid #dadada; border-radius: 7px;" />
                    <ul class="dropdown-menu clientlistappend" role="menu" style="max-height: 300px; overflow-y: auto;">
                        <!--<li class="client-list" data-id="" style="text-align: left">aabbcc</li>-->
                    </ul>
                    <style>
                        .client-list:hover,
                        .client-list.active-item {
                            background-color: #4CAF50 !important;
                            color: white !important;
                        }
                        .client-list {
                            transition: background-color 0.2s;
                        }
                        .clientlistappend {
                            max-height: 300px;
                            overflow-y: auto;
                        }
                    </style>
                </li>
                <?php } ?>
                <?php if (isset($_SESSION['username'])) { ?>
                    <li><a class=" dropdown-toggle  animate " data-toggle="dropdown" href="#"><?php echo $_SESSION['username']; ?>&nbsp;</a>
                        <ul class=" dropdown-menu " role="menu">
                            <li><a class="passwordChange" href="#" data-id="<?php echo $_SESSION['id']; ?>">Change Password</a></li>
                        </ul>
                    </li>
                    <li><a href="#" class="animate" data-toggle="modal" data-target="#logoutConfirmModal">Logout</a></li>
                        <?php } ?>
            </ul>
        </div>
    </div>
</nav>

<script type="text/javascript">
    $(document).ready(function () {
        var selectedIndex = -1;
        
        $(".searchcustomer").keydown(function (e) {
            var items = $(".clientlistappend .client-list");
            
            // Arrow Down - Navigate down
            if (e.keyCode === 40) {
                e.preventDefault();
                if (selectedIndex < items.length - 1) {
                    selectedIndex++;
                    updateSelection(items);
                }
            }
            // Arrow Up - Navigate up  
            else if (e.keyCode === 38) {
                e.preventDefault();
                if (selectedIndex > 0) {
                    selectedIndex--;
                    updateSelection(items);
                }
            }
            // Enter - Select highlighted item
            else if (e.keyCode === 13) {
                e.preventDefault();
                if (selectedIndex >= 0 && selectedIndex < items.length) {
                    $(items[selectedIndex]).click();
                }
            }
            // Escape - Close dropdown
            else if (e.keyCode === 27) {
                $(".clientlistappend").empty();
                selectedIndex = -1;
            }
        });
        
        function updateSelection(items) {
            items.removeClass('active-item');
            if (selectedIndex >= 0 && selectedIndex < items.length) {
                $(items[selectedIndex]).addClass('active-item');
                // Scroll to the selected item
                var dropdown = $(".clientlistappend");
                var item = $(items[selectedIndex]);
                var itemTop = item.position().top;
                var itemHeight = item.outerHeight();
                var dropdownHeight = dropdown.height();
                var scrollTop = dropdown.scrollTop();
                
                if (itemTop + itemHeight > dropdownHeight) {
                    dropdown.scrollTop(scrollTop + itemTop + itemHeight - dropdownHeight);
                } else if (itemTop < 0) {
                    dropdown.scrollTop(scrollTop + itemTop);
                }
            }
        }
        
        $(".searchcustomer").keyup(function (e) {
            // Skip if arrow keys or enter
            if (e.keyCode === 38 || e.keyCode === 40 || e.keyCode === 13 || e.keyCode === 27) {
                return;
            }
            
            selectedIndex = -1; // Reset selection on new search
            
            $.ajax({
                url: 'autoSearch.php',
                type: 'GET',
                dataType: "json",
                data: {
                    customersearch: $(this).val(),
                },
                success: function (data) {
                    $(".clientlistappend").empty();
//                    console.log(data);
                    for (var key in data) {
                        $(".clientlistappend").append('<li class="client-list" style="text-align: left; padding: 8px 12px; cursor: pointer;" data-id="' + data[key]['id'] + '">' + data[key]['customer_name'] + '</li>');
                    }
                    
                    // Show dropdown if there are results
                    if (data.length > 0) {
                        $(".clientlistappend").show();
                    }
                }
            });
        });

        $('body').on("click", ".client-list", function () {
            var customerID = $(this).attr('data-id');
//            alert(customerID);
            // open popup
//            localStorage['visited'] = customerID;
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                    '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
//        alert("Atleast one action item required");
//            $(this).parent("div.deleteNewAuditParentEdit").parent('div.newAuditRowParentEdit').remove();
        });
//        alert(window.parent.document.body.style.zoom = 0.9);
        $('.addAccountCss').click(function () {
            $("#customerAdd").modal("show");
        });

        $('.passwordChange').click(function () {
            $('#changeUserPassword').modal("show");
        });



    });
</script>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" role="dialog" aria-labelledby="logoutConfirmLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutConfirmLabel">Confirm Logout</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p>Are you sure you want to logout?</p>
            </div>
            <div class="modal-footer logout-footer">
                <button type="button" class="btn logout-btn-cancel" data-dismiss="modal">Cancel</button>
                <a href="logout.php" class="btn logout-btn-confirm">Yes, Logout</a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal Backdrop with Blur */
    .modal-backdrop.show {
        opacity: 1 !important;
        background: rgba(0, 0, 0, 0.8) !important;
        backdrop-filter: blur(8px) brightness(0.6);
        -webkit-backdrop-filter: blur(8px) brightness(0.6);
    }
    
    /* Remove default Bootstrap backdrop */
    .modal-backdrop.fade {
        opacity: 0;
    }
    
    .modal-backdrop.fade.show {
        opacity: 1 !important;
    }

    /* Modal Animation */
    @keyframes modalSlideIn {
        from { transform: translateY(30px) scale(0.98); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    #logoutConfirmModal .modal-dialog {
        max-width: 380px;
        margin: 10vh auto;
        animation: modalSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        transform-origin: center center;
        font-family: 'Segoe UI', 'Roboto', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
    }
    
    #logoutConfirmModal .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 60px -10px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        transform: translateZ(0);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        background: #fff;
        position: relative;
        z-index: 1;
    }

    #logoutConfirmModal .modal-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4f46e5, #7c3aed);
    }

    #logoutConfirmModal .modal-header {
        border: none;
        padding: 24px 28px 16px;
        background: #fff;
        position: relative;
    }

    #logoutConfirmModal .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        letter-spacing: -0.4px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Segoe UI', 'Roboto', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    #logoutConfirmModal .modal-title::before {
        content: '⚠️';
        font-size: 22px;
    }

    #logoutConfirmModal .modal-body {
        padding: 12px 28px 24px;
        font-size: 15.5px;
        line-height: 1.7;
        background: #fff;
        color: #475569;
        text-align: center;
    }

    #logoutConfirmModal .modal-body p {
        margin: 0;
        color: #475569;
        font-size: 16px;
        line-height: 1.7;
        font-family: 'Segoe UI', 'Roboto', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
        font-weight: 400;
        letter-spacing: 0.2px;
    }

    #logoutConfirmModal .logout-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding: 20px 28px 24px;
        text-align: center;
        background: #fcfcfd;
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    /* Button Styles */
    #logoutConfirmModal .btn {
        min-width: 120px;
        border-radius: 10px;
        font-size: 14.5px;
        font-weight: 500;
        padding: 11px 24px;
        letter-spacing: 0.3px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        transform: translateZ(0);
        border: none;
        font-weight: 600;
    }

    #logoutConfirmModal .btn:active {
        transform: translateY(1px) !important;
    }

    /* Cancel Button - Red */
    #logoutConfirmModal .logout-btn-cancel {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        color: #b91c1c;
        border: 1px solid #fecaca;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.15);
        transition: all 0.3s ease;
    }

    #logoutConfirmModal .logout-btn-cancel:hover {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        color: #991b1b;
        border-color: #fca5a5;
    }

    /* Confirm Button - Purple */
    #logoutConfirmModal .logout-btn-confirm {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    #logoutConfirmModal .logout-btn-confirm::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #4338ca, #6d28d9);
        z-index: -1;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    #logoutConfirmModal .logout-btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
    }

    #logoutConfirmModal .logout-btn-confirm:hover::before {
        opacity: 1;
    }

    /* Close Button */
    #logoutConfirmModal .close {
        font-size: 22px;
        font-weight: 300;
        opacity: 0.6;
        transition: all 0.2s ease;
        padding: 6px 10px;
        margin: -12px -10px -6px auto;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.03);
    }

    #logoutConfirmModal .close:hover {
        opacity: 1;
        background: rgba(0, 0, 0, 0.06);
        transform: rotate(90deg);
    }

    #logoutConfirmModal .close:hover {
        opacity: 1;
        background: rgba(255, 255, 255, 0.15);
    }
    
    /* Modal Content Styling */
    #logoutConfirmModal .modal-body:before {
        content: '⚠️';
        display: block;
        font-size: 36px;
        text-align: center;
        margin-bottom: 15px;
        opacity: 0.9;
    }
</style>
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
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-ZH2QWSFTB0"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-ZH2QWSFTB0');
    </script>
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
} else if (isset($pageIdentifier) && $pageIdentifier == "Admin Dashboard") {
    $addAdminDashboardClass = "active";
    if (!Utitlity::checkForUserMainAdmin()) {
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
} else if (isset($pageIdentifier) && $pageIdentifier == "Bills") {
    $addBillsClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
        header("Location: login");
        exit;
    }
} else if (isset($pageIdentifier) && $pageIdentifier == "Suppliers") {
    $addSuppliersClass = "active";
    if (!(Utitlity::checkForUserAdmin() || Utitlity::checkForUserFinance() || Utitlity::checkForOfficeStaff() || Utitlity::checkForMainAdmin())) {
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
} else if (isset($pageIdentifier) && $pageIdentifier == "Job Applications") {
    $addJobAppClass = "active";
    if (!(Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff())) {
        header("Location: login");
        exit;
    }
}

function simpleDateConversion($inputStr)
{
    if ($inputStr === "") {
        return "";
    }
    $strSplit = explode("-", $inputStr);
    if (sizeOf($strSplit) > 1)
        return $strSplit[2] . "." . $strSplit[1] . "." . $strSplit[0];
    else
        return $inputStr;
}

function startsWith($haystack, $needle)
{
    // search backwards starting from haystack length characters from the end   
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function getFlatDate($inputStr)
{
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
        <li class="sidebar-brand" style="height: 65px; overflow: hidden;"><a class="" id="menu-toggle" href="#" style="display: block; height: 65px; background: linear-gradient(180deg, #081524 0%, #053c6d 100%); padding: 5px 10px; text-decoration: none; box-sizing: border-box;">
                <div style="display: inline-flex; flex-direction: column; align-items: center; justify-content: center; width: 120px; vertical-align: middle; height: 100%;">
                    <span style="font-family: 'Poppins', 'Inter', sans-serif; font-weight: 800; font-size: 26px; letter-spacing: 1px; line-height: 1;">
                        <span style="color: #CF2120;">A</span><span style="color: #ffffff;">IT</span><span style="color: #CF2120;">F</span>
                    </span>
                    <span style="font-family: 'Poppins', 'Inter', sans-serif; font-weight: 600; font-size: 11px; color: #ffffff; letter-spacing: 2px; margin-top: 2px; line-height: 1;">ERP</span>
                </div>
                <span id="main_icon" class="glyphicon glyphicon-align-justify" style="float: right; margin-top: 16px; margin-right: 15px; color: #ffffff; font-size: 18px;"></span></a></li>
    </ul>
    <ul class="sidebar-nav" id="sidebar">
        <?php /* Admin Dashboard hidden from left sidebar menu. if (Utitlity::checkForUserMainAdmin()) { ?>
            <li><a href="adminDashboard.php"
                    class="animate <?php echo isset($addAdminDashboardClass) ? $addAdminDashboardClass : ''; ?>">ADMIN
                    DASHBOARD<i class="sub_icon fa fa-line-chart fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } */ ?>
        <li><a href="iaf_dashboard.php" class="animate <?php echo isset($iafClass) ? $iafClass : ''; ?>">IAF DASHBOARD<i
                    class="sub_icon fa fa-dashboard fa-2x" style="vertical-align:middle"></i></a></li>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff()) { ?>
            <?php $unreadBadge = isset($GLOBALS['unread_app_count']) ? (int) $GLOBALS['unread_app_count'] : 0; ?>
            <li>
                <a href="applicationReceived.php" class="animate app-rec-nav <?php echo $addAppReceivedClass ?>">
                    APPLICATION RECEIVED
                    <?php if ($unreadBadge > 0) { ?>
                        <span
                            style="margin-left:8px; padding:2px 7px; border-radius:999px; background:#ef4444; color:#fff; font-size:11px; font-weight:700;"><?php echo $unreadBadge; ?></span>
                    <?php } ?>
                </a>
            </li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
            <li><a href="homePage.php" class="animate <?php echo $addClass ?>">CLIENTS<i
                        class="sub_icon fa fa-building fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForUserMarketing() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
            <li><a href="marketingPage.php" class="animate <?php echo $addMarketingClass ?>">MARKETING<i
                        class="sub_icon fa fa-briefcase fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForUserFinance() || Utitlity::checkForUserAdmin()) { ?>
            <li><a href="paymentPage.php" class="animate <?php echo $addFinanceClass ?>">PAYMENT<i
                        class="sub_icon fa fa-money fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff() || Utitlity::checkForUserConsultant()) { ?>
            <li><a href="auditPlanning.php" class="animate <?php echo $addWorkingClass ?>">AUDIT PLANNING<i
                        class="sub_icon fa fa-money fa-2x" style="vertical-align:middle"></i></a></li>
            <!--<li><a href="idealPage.php" class="animate ">IDEAL<i class="sub_icon fa fa-briefcase fa-2x" style="vertical-align:middle"></i></a></li>-->
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin()) { ?>
            <li><a href="masterCreator.php" class="animate <?php echo $addMasterClass ?>">MASTER CREATOR<i
                        class="sub_icon fa fa-wrench  fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
            <li><a href="listOfTds.php" class="animate <?php echo $addtds ?>">TDS DEDUCTED<i
                        class="sub_icon fa fa-list  fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
            <li><a href="intimationLetter.php" class="animate <?php echo $adddueClass ?>">INTIMATION DUE<i
                        class="sub_icon fa fa-envelope-o fa-2x" style="vertical-align:middle"></i></a></li>
            <li><a href="sentintimation.php" class="animate <?php echo $addsentClass ?>">INTIMATION SENT<i
                        class="sub_icon fa fa-envelope fa-2x" style="vertical-align:middle"></i></a></li>
            <li><a href="inbox.php" target="_blank"
                    class="animate <?php echo isset($addInboxClass) ? $addInboxClass : ''; ?>">INBOX<i
                        class="sub_icon fa fa-inbox fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
            <!--<li><a href="clientList.php" class="animate">CLIENT LIST<i class="sub_icon fa fa-list  fa-2x" style="vertical-align:middle"></i></a></li>-->
            <li><a href="invoicePage.php" class="animate <?php echo $addInvoiceClass ?>">INVOICES<i
                        class="sub_icon fa fa-inr fa-2x" style="vertical-align:middle"></i></a></li>
            <li><a href="billsPage.php" class="animate <?php echo isset($addBillsClass) ? $addBillsClass : ''; ?>">BILLS<i
                        class="sub_icon fa fa-file-text-o fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForUserFinance() || Utitlity::checkForOfficeStaff()) { ?>
            <li><a href="suppliers.php" class="animate <?php echo isset($addSuppliersClass) ? $addSuppliersClass : ''; ?>">SUPPLIERS<i
                        class="sub_icon fa fa-address-card-o fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff() || Utitlity::checkForStaff()) { ?>
            <li><a href="quotationPage.php" class="animate <?php echo $addquotClass ?>">QUOTATIONS<i
                        class="sub_icon fa fa-quote-left fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff()) { ?>
            <li><a href="agreementPage.php" class="animate <?php echo $addagreementClass ?>">AGREEMENTS<i
                        class="sub_icon fa fa-quote-left fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>



        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin() || Utitlity::checkForOfficeStaff()) { ?>
            <li><a href="auditPage.php" class="animate <?php echo $addAuditClass ?>">AUDIT LIST<i
                        class="sub_icon fa fa-list-alt fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin()) { ?>
            <li><a href="addUsers.php" class="animate <?php echo $addUserClass ?>">ADD USER<i
                        class="sub_icon fa fa-user-plus fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (Utitlity::checkForUserMainAdmin() || Utitlity::checkForUserAdmin()) { ?>
            <li><a href="clientRecycle.php" class="animate <?php echo $addRecycleClass ?>">RECYCLE BIN<i
                        class="sub_icon fa fa-trash-o fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <?php if (!(Utitlity::checkClient() || Utitlity::checkExternalUser())) { ?>
            <li><a href="auditanalysis.php" class="animate <?php echo $addcertissueClass ?>">CERT. ISSUED LIST<i
                        class="sub_icon fa fa-newspaper-o fa-2x" style="vertical-align:middle"></i></a></li>
            <li><a href="qualityanalysis.php" class="animate <?php echo $addauditperClass ?>">AUDIT PERFORMANCE<i
                        class="sub_icon fa fa-recycle fa-2x" style="vertical-align:middle"></i></a></li>
            <li><a href="clientanalysis.php" class="animate <?php echo $clientanalysis ?>">CLIENT ANALYSIS<i
                        class="sub_icon fa fa-eye fa-2x" style="vertical-align:middle"></i></a></li>
            <!--<li><a href="cdcobjective.php" class="animate <?php echo $cdcobj ?>">CDC OBJECTIVE<i class="sub_icon fa fa-eye fa-2x" style="vertical-align:middle"></i></a></li>-->
            <li><a href="apprevobjective.php" class="animate <?php echo $apprevobj ?>">APP REV OBJECTIVE<i
                        class="sub_icon fa fa-eye fa-2x" style="vertical-align:middle"></i></a></li>
        <?php } ?>
        <li><a href="uploadingStatus.php" class="animate <?php echo $docobj ?>">DOC.UPLOAD STATUS<i
                    class="sub_icon fa fa-eye fa-2x" style="vertical-align:middle"></i></a></li>
    </ul>
</div>
<nav class="navbar navbar-fixed-top modern-navbar" role="navigation">
    <div class="navbar-container">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>

        <div class="navbar-content">
            <?php if (!(Utitlity::checkClient() || Utitlity::checkExternalUser())) { ?>
                <div class="navbar-left">
                    <div class="dropdown add-new-dropdown">
                        <a href="#" class="dropdown-toggle modern-add-btn" data-toggle="dropdown" title="Add New">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            <span>Add New</span>
                            <i class="fa fa-chevron-down" aria-hidden="true" style="font-size: 11px;"></i>
                        </a>
                        <div class="dropdown-menu add-new-menu" role="menu">
                            <div class="add-new-panel">
                                <a href="#" class="addAccountCss add-new-item add-new-item-client">
                                    <span class="add-new-ico"><i class="fa fa-building"></i></span>
                                    <span class="add-new-text">
                                        <span class="add-new-title">Add New Client</span>
                                        <span class="add-new-sub">Create a new client profile</span>
                                    </span>
                                    <span class="add-new-arrow"><i class="fa fa-angle-right"></i></span>
                                </a>
                                <a href="suppliers.php?add=1" class="add-new-item add-new-item-supplier">
                                    <span class="add-new-ico"><i class="fa fa-truck"></i></span>
                                    <span class="add-new-text">
                                        <span class="add-new-title">Add New Supplier</span>
                                        <span class="add-new-sub">Register a new supplier</span>
                                    </span>
                                    <span class="add-new-arrow"><i class="fa fa-angle-right"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="navbar-center">
                    <div class="search-wrapper">
                        <i class="fa fa-search search-icon" aria-hidden="true"></i>
                        <input class="dropdown-toggle searchcustomer modern-search" data-toggle="dropdown" placeholder="Search Client" autocomplete="off" />
                        <ul class="dropdown-menu clientlistappend" role="menu"></ul>
                    </div>
                </div>
            <?php } ?>

            <div class="navbar-right">
                <button type="button" id="themeToggle" class="modern-card-btn" aria-label="Toggle dark mode" title="Dark Mode">
                    <i class="fa fa-moon-o" aria-hidden="true"></i>
                </button>

                <?php if (isset($_SESSION['username'])) { ?>
                    <div class="dropdown modern-user-dropdown">
                        <a class="dropdown-toggle modern-card-btn modern-profile-card" data-toggle="dropdown" href="#" role="button">
                            <div class="user-avatar">
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-greeting">Hi,</span>
                                <span class="user-name"><?php echo $_SESSION['username']; ?></span>
                            </div>
                            <i class="fa fa-chevron-down dropdown-arrow" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu modern-dropdown-menu" role="menu">
                            <li><a class="passwordChange" href="#" data-id="<?php echo $_SESSION['id']; ?>"><i class="fa fa-key"></i> Change Password</a></li>
                        </ul>
                    </div>

                    <a href="#" class="modern-card-btn modern-logout-card" data-toggle="modal" data-target="#logoutConfirmModal" title="Logout">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                        <span>Logout</span>
                    </a>
                <?php } ?>
            </div>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav visible-xs">
                <?php if (Utitlity::checkForUserMainAdmin()) { ?>
                    <li><a href="adminDashboard.php">ADMIN DASHBOARD</a></li>
                <?php } ?>
                <li><a href="iaf_dashboard.php">IAF DASHBOARD</a></li>
                <li><a href="homePage.php">CLIENTS</a></li>
                <li><a href="marketingPage.php">MARKETING</a></li>
                <li><a href="paymentPage.php">PAYMENT</a></li>
                <li><a href="auditPlanning.php">AUDIT PLANNING</a></li>
                <li><a href="masterCreator.php">MASTER CREATOR</a></li>
                <li><a href="listOfTds.php">TDS DEDUCTED</a></li>
                <li><a href="intimationLetter.php">INTIMATION LETTERS</a></li>
                <li><a href="inbox.php" target="_blank">INBOX</a></li>
                <li><a href="clientList.php">CLIENT LIST</a></li>
                <li><a href="invoicePage.php">INVOICE</a></li>
                <li><a href="billsPage.php">BILLS</a></li>
                <li><a href="suppliers.php">SUPPLIERS</a></li>
                <li><a href="quotationPage.php">QUOTATION</a></li>
                <li><a href="addUsers.php">ADD USER</a></li>
                <li><a href="clientRecycle.php">RECYCLE BIN</a></li>
            </ul>
        </div>
    </div>
</nav>

<style>
/* Navbar Base */
.modern-navbar {
    background: #ffffff;
    border: none;
    border-bottom: 1px solid #e5e7eb;
    height: 72px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    min-height: 72px;
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    z-index: 999;
    font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
}

.navbar-container {
    height: 100%;
    padding: 0;
    margin-left: 250px;
}

.navbar-content {
    display: grid;
    grid-template-columns: 200px 1fr auto;
    align-items: center;
    height: 72px;
    padding: 0 30px;
    gap: 40px;
}

/* Left Section - Add Client Button */
.navbar-left {
    display: flex;
    align-items: center;
    grid-column: 1;
    height: 72px;
    margin-left: 0;
    padding-top: 0;
}

.modern-add-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 48px;
    padding: 0 20px;
    background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 14px;
    font-family: 'Inter', 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(37,99,235,0.24);
    white-space: nowrap;
    letter-spacing: -0.01em;
}

.modern-add-btn:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #6d28d9 100%);
    box-shadow: 0 4px 16px rgba(37,99,235,0.36);
    text-decoration: none;
    color: #ffffff !important;
}

.modern-add-btn:active {
    box-shadow: 0 2px 8px rgba(37,99,235,0.24);
}

.modern-add-btn i {
    font-size: 15px;
}

.add-new-dropdown {
    position: relative;
}

/* ---- Premium "Add New" action dropdown ----
   .add-new-menu is the Bootstrap/Popper positioning shell (kept transparent so
   Popper's inline transform never conflicts with our open animation).
   .add-new-panel carries all the visuals and the open animation. */
.add-new-menu {
    min-width: 336px;
    margin: 0;
    padding: 0;
    border: 0;
    background: transparent;
    box-shadow: none;
    z-index: 10001;
}

.add-new-panel {
    margin-top: 10px;
    width: 336px;
    border: 1px solid #e6ebf3;
    border-radius: 18px;
    padding: 8px;
    background: #ffffff;
    box-shadow: 0 2px 6px rgba(15,23,42,0.05), 0 18px 44px rgba(15,23,42,0.14);
}

/* Open animation: fade + slide-down + slight scale */
.add-new-menu.show .add-new-panel {
    transform-origin: top center;
    animation: addNewMenuIn 0.24s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes addNewMenuIn {
    from { opacity: 0; transform: translateY(-8px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Each action row: icon chip + title/subtitle + chevron */
.add-new-panel > a.add-new-item {
    display: flex;
    align-items: center;
    gap: 13px;
    height: 64px;
    padding: 0 12px;
    border-radius: 13px;
    color: #0f172a;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.22s cubic-bezier(0.4,0,0.2,1),
                transform 0.22s cubic-bezier(0.4,0,0.2,1);
}

.add-new-panel > a.add-new-item + a.add-new-item { margin-top: 4px; }

.add-new-ico {
    width: 46px;
    height: 46px;
    flex: 0 0 auto;
    border-radius: 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 18px;
    box-shadow: 0 5px 13px rgba(15,23,42,0.14);
    transition: transform 0.22s cubic-bezier(0.34,1.56,0.64,1);
}

.add-new-text {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.3;
    min-width: 0;
    flex: 1 1 auto;
}

.add-new-title { font-size: 14px; font-weight: 700; letter-spacing: -0.01em; }
.add-new-sub { font-size: 11.5px; font-weight: 500; color: #94a3b8; margin-top: 1px; }

.add-new-arrow {
    flex: 0 0 auto;
    color: #cbd5e1;
    font-size: 16px;
    opacity: 0;
    transform: translateX(-4px);
    transition: opacity 0.22s ease, transform 0.22s ease;
}
.add-new-item:hover .add-new-arrow { opacity: 1; transform: translateX(0); }
.add-new-item:hover .add-new-ico { transform: scale(1.08); }

/* Client = blue */
.add-new-item-client .add-new-ico { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
.add-new-panel > a.add-new-item-client:hover {
    background: #eff6ff;
    color: #1d4ed8;
    transform: translateX(3px);
}
.add-new-item-client:hover .add-new-arrow { color: #1d4ed8; }

/* Supplier = orange */
.add-new-item-supplier .add-new-ico { background: linear-gradient(135deg, #f5821f 0%, #d9631a 100%); }
.add-new-panel > a.add-new-item-supplier:hover {
    background: #fff5eb;
    color: #c2620f;
    transform: translateX(3px);
}
.add-new-item-supplier:hover .add-new-arrow { color: #c2620f; }

.add-new-item-supplier:hover .add-new-sub,
.add-new-item-client:hover .add-new-sub { color: inherit; opacity: 0.7; }

/* Dark theme */
html.theme-dark .add-new-panel { background: #111827; border-color: #1f2937; box-shadow: 0 2px 6px rgba(0,0,0,0.4), 0 18px 44px rgba(0,0,0,0.5); }
html.theme-dark .add-new-panel > a.add-new-item { color: #e5e7eb; }
html.theme-dark .add-new-sub { color: #64748b; }
html.theme-dark .add-new-item-client:hover { background: rgba(37,99,235,0.16); color: #93c5fd; }
html.theme-dark .add-new-item-supplier:hover { background: rgba(245,130,31,0.16); color: #fdba74; }

/* Responsive */
@media (max-width: 420px) {
    .add-new-menu { min-width: 300px; }
    .add-new-panel { width: 300px; }
}

/* Center Section - Search */
.navbar-center {
    display: flex;
    justify-content: flex-start;
    grid-column: 2;
    padding: 0 20px;
}

.search-wrapper {
    position: relative;
    width: 100%;
    max-width: 520px;
    isolation: isolate;
}

.search-wrapper::before {
    content: "";
    position: absolute;
    inset: -1px;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(37,99,235,0.18), rgba(124,58,237,0.16));
    opacity: 0;
    transform: scale(0.98);
    transition: all 0.25s ease;
    z-index: -1;
}

.search-wrapper:focus-within::before {
    opacity: 1;
    transform: scale(1);
}

.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transform: translateY(-50%);
    color: #2563eb;
    font-size: 15px;
    pointer-events: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #eff6ff 0%, #eef2ff 100%);
    box-shadow: inset 0 0 0 1px rgba(37,99,235,0.08);
    transition: all 0.25s ease;
}

.search-wrapper:focus-within .search-icon {
    color: #ffffff;
    background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
    box-shadow: 0 8px 18px rgba(37,99,235,0.22);
}

.modern-search {
    width: 100%;
    height: 48px;
    padding: 0 22px 0 62px;
    border: 1px solid #dbe3ef;
    border-radius: 16px;
    font-family: 'Inter', 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 500;
    color: #0f172a;
    background: #ffffff;
    transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
    box-shadow: 0 10px 28px rgba(15,23,42,0.06), inset 0 1px 0 rgba(255,255,255,0.75);
    letter-spacing: -0.01em;
}

.modern-search::placeholder {
    color: #8a98ad;
    font-weight: 500;
}

.modern-search:hover {
    border-color: #b9c6da;
    background: #fbfdff;
}

.modern-search:focus {
    outline: none;
    border-color: #7aa2f7;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.12), 0 14px 34px rgba(37,99,235,0.12);
}

.clientlistappend {
    width: 100%;
    max-height: 320px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 18px 45px rgba(15,23,42,0.16);
    margin-top: 10px;
    padding: 6px;
    background: #ffffff;
    position: absolute;
    z-index: 9999;
}

.client-list {
    padding: 11px 14px !important;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f1f5f9;
    border-radius: 10px;
    color: #334155;
    font-size: 14px;
    font-weight: 500;
}

.client-list:last-child {
    border-bottom: none;
}

.client-list:hover, .client-list.active-item {
    background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%) !important;
    color: #ffffff !important;
}

/* Right Section - Separate Cards */
.navbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
    grid-column: 3;
}

/* Base Card Style */
.modern-card-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    text-decoration: none;
}

.modern-card-btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    text-decoration: none;
}

/* Dark Mode Card */
.modern-card-btn:not(.modern-profile-card):not(.modern-logout-card) {
    width: 44px;
    height: 44px;
    color: #374151;
    font-size: 17px;
}

/* Profile Card */
.modern-user-dropdown {
    position: relative;
}

.modern-profile-card {
    gap: 10px;
    padding: 6px 14px 6px 6px;
    min-width: 160px;
    color: #1f2937 !important;
}

.user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #ffffff;
    flex-shrink: 0;
    font-size: 15px;
}

.user-info {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}

.user-greeting {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1.2;
}

.user-name {
    font-size: 13px;
    color: #1f2937;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}

.dropdown-arrow {
    font-size: 9px;
    color: #9ca3af;
    transition: transform 0.2s ease;
    flex-shrink: 0;
}

.modern-user-dropdown.open .dropdown-arrow {
    transform: rotate(180deg);
}

/* Logout Card */
.modern-logout-card {
    gap: 7px;
    padding: 10px 16px;
    background: #fef2f2 !important;
    border-color: #fee2e2;
    color: #dc2626 !important;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
}

.modern-logout-card:hover {
    background: #fee2e2 !important;
    border-color: #fecaca;
    color: #b91c1c !important;
}

.modern-logout-card i {
    font-size: 14px;
}

.modern-dropdown-menu {
    min-width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin-top: 8px;
    padding: 6px;
    background: #ffffff;
}

.modern-dropdown-menu li {
    list-style: none;
}

.modern-dropdown-menu li a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    color: #374151;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s ease;
    font-size: 13px;
    font-weight: 500;
}

.modern-dropdown-menu li a:hover {
    background: #f3f4f6;
    color: #6366f1;
}

.modern-dropdown-menu li a i {
    font-size: 13px;
    width: 14px;
}

/* Mobile Toggle */
.navbar-toggle {
    display: none;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 10px;
    background: #ffffff;
    margin: 10px 0;
}

.navbar-toggle .icon-bar {
    background-color: #374151;
    height: 2px;
    border-radius: 1px;
}

/* Mobile Menu */
.navbar-collapse {
    display: none;
    border-top: 1px solid #e5e7eb;
    background: #ffffff;
    padding: 10px 0;
    position: absolute;
    top: 64px;
    left: 0;
    right: 0;
}

.navbar-collapse.in {
    display: block;
}

.navbar-collapse .nav {
    margin: 0;
}

.navbar-collapse .nav > li > a {
    padding: 14px 24px;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.navbar-collapse .nav > li > a:hover {
    background: #f9fafb;
    color: #4f46e5;
}

/* Responsive Design */
@media (max-width: 991px) {
    .navbar-container {
        margin-left: 0;
    }
    
    .navbar-content {
        flex-wrap: wrap;
        height: auto;
        padding: 12px 20px;
    }

    .navbar-toggle {
        display: block;
    }

    .navbar-left {
        order: 2;
        width: 100%;
        justify-content: center;
        margin: 12px 0 0 0;
    }

    .navbar-center {
        order: 1;
        width: 100%;
        max-width: 100%;
        margin: 0;
    }

    .navbar-right {
        order: 3;
        width: 100%;
        justify-content: space-between;
        margin-top: 12px;
    }
}

@media (max-width: 768px) {
    .modern-add-btn span, .modern-logout-card span {
        display: none;
    }

    .modern-add-btn {
        padding: 10px 12px;
        min-width: 44px;
    }

    .modern-profile-card {
        min-width: auto;
        max-width: 140px;
    }

    .user-name {
        max-width: 70px;
    }
}

@media (max-width: 480px) {
    .navbar-container {
        padding: 0 16px;
    }
    
    .navbar-right {
        gap: 8px;
    }
    
    .modern-icon-btn {
        width: 38px;
        height: 38px;
    }
}
</style>

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
                        var isSupplier = data[key]['customer_category'] === 'supplier';
                        var label = isSupplier ? '<span style="float:right; background:#ede9fe; color:#5b21b6; border-radius:999px; padding:2px 7px; font-size:10px; font-weight:700;">SUPPLIER</span>' : '';
                        var itemStyle = isSupplier ? 'text-align: left; padding: 8px 12px; cursor: pointer; border-left: 4px solid #6d28d9;' : 'text-align: left; padding: 8px 12px; cursor: pointer;';
                        $(".clientlistappend").append('<li class="client-list" style="' + itemStyle + '" data-id="' + data[key]['id'] + '">' + data[key]['customer_name'] + label + '</li>');
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
        from {
            transform: translateY(30px) scale(0.98);
            opacity: 0;
        }

        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
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

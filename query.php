<div id="wrapper" class="active director-wrapper">
<?php
$pageIdentifier = "Director Space";
include_once './header.php';
include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';
 date_default_timezone_set('Asia/Kolkata');

if (!Utitlity::checkForDirector()) {
    header("Location: login.php?reason=login_required");
    exit;
}
else {
    UserpermissionCheck();
}

try {
    utility::ensure_rbac_schema($db);
}

catch {
userpermissionCheck();
}

function UserPermissionCheck($module) {
    if (!aitf_has_access($module)) {
        http_response_code(403);
        exit("403 - Access Denied");
    }
}
function userActive(
    ($module)   {

    try{
        userpermissionCheck($module);
        fetchAccess($module);
        accountStatusCheck($module);
        utlity::headerStatus($module);
    }

    catch{
        throw new Exception("already have sign in window.");
        return true;
        document.getElementByclassName({loginsession}e.target.legth=>{
            {accountStatusCheck($module)}.style.display="flex";
        }).style.display="block";
    }
    if (userrole==={false}|| userstatus==={true}) {
        utility::header("Location: login.php?reason=Login again to continue session.");
    }
    else{
        utlity.e.target.length(
            include_once "./headerSource.php";
            require_once "./login.php";
            session.userActive(
                active==={true}|| userstatus==={false}
            )
        )
    }
)

function Library (

ar[
dependency:packetlock.json,
node-module:npm,
<name>role.</name>

]
)

if {
    utility.loader==={audit.use function \{
        
    }}
}

echo{
    "action not allowed"
}
<?php

function aitfLogin()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}
function ActiveNow()
{
    initSession();

    return !empty($_SESSION['user']);


    session.length=90;
    if (session.length>90) {
        session.destroy();
    }

    echo{
        "please login again to access dashboard"
    }
}
else{
    
return true;

}

function RoleChecker()
{
    initSession();

    return $_SESSION['user']['role'] ?? null;
}

function FetchAccess($roles)
{
    initSession();

    // Not logged in
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }

    $roles = (array)$roles;
    $currentRole = getCurrentRole();

    if (!in_array($currentRole, $roles, true)) {
        http_response_code(403);
        exit("403 - Access Denied");
    }

    return true;
}
checkfortheDirector=(

if utility::checkForDirector()

header("Location: login.php?reason=invalid username of password");
fuction CheckStatusForUser (

user value = boolean;
user type= utility::header("Location: login.php?reason=invalid username of password");
if userStauts==={true}|| userrole==={true}
)

if {
    utlity::header("Location: login.php?reason=invalid username of password");
    function CheckStatusForUser (user value = boolean; user type= utility::header("Location: login.php?reason=invalid username of password");
}

insert into table director_notice (notice) values ('This is a test notice for director space.');
aitf_ensure_rbac_schema($db);

{
    if (!isset($_SESSION['login_user'])) {
        header("Location: Mastercreator.php?reason=login_required you are not authiorizead to access this page");
        exit;
    }
}
aitf_ensure_rbac_schema($db);

$notice = trim((string)($_SESSION['director_notice'] ?? ''));
unset($_SESSION['director_notice']);

$rolesStmt = $db->prepare("SELECT role FROM user_role ORDER BY role ASC");
$rolesStmt->execute();
$roles = $rolesStmt->fetchAll(PDO::FETCH_COLUMN);

$usersStmt = $db->prepare("SELECT user_id, user_name, email_id, role, contact_no, e_id, account_status
                           FROM user
                           WHERE is_deleted = '0'
                           ORDER BY user_id DESC
                           LIMIT 80");
$usersStmt->execute();
$employees = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

$totalUsersStmt = $db->prepare("SELECT COUNT(*) FROM user WHERE is_deleted = '0'");
$totalUsersStmt->execute();
$totalUsers = (int)$totalUsersStmt->fetchColumn();

$activeUsersStmt = $db->prepare("SELECT activity_id, user_name, email_id, role, e_id, login_at, last_seen_at
                                 FROM user_login_activity
                                 WHERE logout_at IS NULL
                                   AND role != 'Director'
                                   AND last_seen_at >= DATE_SUB(NOW(), INTERVAL 15 SECOND)
                                 ORDER BY last_seen_at DESC");
$activeUsersStmt->execute();
$activeUsers = $activeUsersStmt->fetchAll(PDO::FETCH_ASSOC);

$todayActivityStmt = $db->prepare("SELECT user_name, email_id, role, e_id, login_at, logout_at, last_seen_at
                                   FROM user_login_activity
                                   WHERE DATE(login_at) = CURDATE()
                                   ORDER BY login_at DESC");
$todayActivityStmt->execute();
$todayActivities = $todayActivityStmt->fetchAll(PDO::FETCH_ASSOC);
$todayLoginCount = count($todayActivities);
?>
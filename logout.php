<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
include_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/daily_auth_helper.php';
if (isset($db) && $db instanceof PDO) {
    aitf_record_logout_activity($db);
    aitf_revoke_daily_session_token($db);
}
aitf_destroy_session();
header('Location: login.php');
exit;
?>

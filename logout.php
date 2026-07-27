<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
aitf_destroy_session();
header('Location: loginSelection.php');
exit;
?>

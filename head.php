<?php
if (!isset($GLOBALS['unread_app_count'])) {
    if (!isset($db)) {
        @include_once 'dbConnect.php';
    }
    $GLOBALS['unread_app_count'] = 0;
    if (isset($db)) {
        try {
            $stmtBadgeHead = $db->query("SELECT COUNT(*) FROM online_audit_applications WHERE read_flag = 0");
            $GLOBALS['unread_app_count'] = (int)$stmtBadgeHead->fetchColumn();
        } catch (Exception $e) {
            $GLOBALS['unread_app_count'] = 0;
        }
    }
}
?>
<link media="all" type="text/css" rel="stylesheet" href="css/bootstrap/css/bootstrap.min.css">
<script src="js/moment.min.js"></script>
<script src="css/bootstrap/js/jquery.min.js"></script>
<script src="css/bootstrap/js/bootstrap.min.js"></script>
<script src="css/bootstrap/js/jquery-ui.js"></script>
<link href="css/jquery-ui.css" rel="stylesheet">
<link media="all" type="text/css" rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="css/app.css">
<link rel="stylesheet" href="css/basic.css">
<link rel="stylesheet" href="css/float-label.css">
<link rel="stylesheet" href="css/editable-table.css">
<link rel="stylesheet" href="css/spinner.css">
<link href="css/jquery.flexdatalist.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script src="js/form-fancy-control.js"></script>
<script src="js/jquery.flexdatalist.js"></script>
<script src="js/app.js"></script>
<script type="text/javascript" src="./tinymce/tinymce.min.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
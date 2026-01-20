<div id="wrapper" class="active">
<?php
$pageIdentifier = "Application Received";
include 'dbConnect.php';

date_default_timezone_set('Asia/Kolkata');

$applications = [];
$unreadCount = 0;
if (isset($db)) {
    try {
        $stmt = $db->query("SELECT * FROM online_audit_applications ORDER BY created_at DESC");
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($applications as $row) {
            if (isset($row['read_flag']) && (int)$row['read_flag'] === 0) {
                $unreadCount++;
            }
        }
    } catch (Exception $e) {
        $applications = [];
    }
}
$GLOBALS['unread_app_count'] = $unreadCount;
include 'header.php';
?>
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" >
                <br>
                <br>
                <style>
                    .app-rec-wrapper {
                        max-width: 900px;
                        margin: 0 auto;
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    }
                    .app-rec-card {
                        background: #ffffff;
                        border-radius: 14px;
                        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.10);
                        padding: 16px 20px 10px 20px;
                        border: 1px solid #e5e7eb;
                        border-top: 4px solid #2563eb;
                    }
                    .app-rec-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 10px;
                    }
                    .app-rec-title {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .app-rec-title-icon {
                        width: 32px;
                        height: 32px;
                        border-radius: 9999px;
                        background: linear-gradient(135deg, #2563eb, #1d4ed8);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #ffffff;
                        box-shadow: 0 6px 12px rgba(37, 99, 235, 0.45);
                    }
                    .app-rec-title-icon .fa {
                        font-size: 15px;
                    }
                    .app-rec-title-text-main {
                        font-size: 16px;
                        font-weight: 600;
                        color: #0f172a;
                        letter-spacing: 0.02em;
                    }
                    .app-rec-title-text-sub {
                        font-size: 11px;
                        color: #6b7280;
                    }
                    .app-rec-count {
                        padding: 4px 10px;
                        border-radius: 9999px;
                        background: #eff6ff;
                        color: #1d4ed8;
                        font-size: 11px;
                        display: inline-flex;
                        align-items: center;
                        gap: 4px;
                    }
                    .app-rec-count .fa {
                        font-size: 11px;
                    }
                    .app-rec-unread {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 6px 10px;
                        border-radius: 9999px;
                        background: #fef3c7;
                        color: #b45309;
                        font-size: 11px;
                        font-weight: 700;
                        box-shadow: 0 4px 12px rgba(180, 83, 9, 0.18);
                    }
                    .app-rec-unread .fa {
                        font-size: 12px;
                    }
                    #tab_online_applications thead th {
                        background: linear-gradient(to right, #eef2ff, #e0f2fe);
                        border-color: #e5e7eb !important;
                        font-size: 12px;
                        font-weight: 600;
                        color: #1f2937;
                    }
                    #tab_online_applications tbody td {
                        font-size: 13px;
                        vertical-align: middle;
                        border-top: 1px solid #eef2f7;
                    }
                    #tab_online_applications th,
                    #tab_online_applications td {
                        text-align: left;
                    }
                    #tab_online_applications th:first-child,
                    #tab_online_applications td:first-child {
                        width: 70px;
                        text-align: center;
                    }
                    .app-rec-row td {
                        transition: background-color 0.18s ease, box-shadow 0.18s ease, transform 0.12s ease;
                    }
                    .app-rec-row:hover td {
                        background: #eff6ff;
                        box-shadow: inset 3px 0 0 #2563eb;
                    }
                    .app-rec-date-icon {
                        margin-right: 6px;
                        color: #9ca3af;
                    }
                    .app-rec-org-icon {
                        margin-right: 6px;
                        color: #9ca3af;
                    }
                    .app-rec-org a {
                        font-weight: 600;
                        color: #1d4ed8;
                        text-decoration: none;
                    }
                    .app-rec-org a:hover {
                        text-decoration: underline;
                    }
                    .app-rec-row.unread td {
                        background: #ecfdf3;
                        box-shadow: inset 3px 0 0 #16a34a;
                        position: relative;
                    }
                    .app-rec-row.read td {
                        background: #ffffff;
                    }
                    .app-rec-unread-pill {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 3px 8px;
                        margin-left: 6px;
                        border-radius: 999px;
                        background: #dcfce7;
                        color: #166534;
                        font-size: 10px;
                        font-weight: 700;
                    }
                </style>
                <div class="row clearfix" >
                    <div class="col-sm-12 main_content">
                        <div class="app-rec-wrapper">
                        <div class="app-rec-card table-responsive">
                            <div class="app-rec-header">
                                <div class="app-rec-title">
                                    <div class="app-rec-title-icon"><i class="fa fa-list-ul"></i></div>
                                    <div>
                                        <div class="app-rec-title-text-main">Application Received</div>
                                        <div class="app-rec-title-text-sub">Latest online applications</div>
                                    </div>
                                </div>
                                <div class="app-rec-count"><i class="fa fa-database"></i><span><?php echo !empty($applications) ? count($applications) : 0; ?></span></div>
                                <?php if ($unreadCount > 0) { ?>
                                    <div class="app-rec-unread"><i class="fa fa-bell"></i><span><?php echo (int)$unreadCount; ?> unread</span></div>
                                <?php } ?>
                            </div>
                            <table class="table table-hover table-sortable" id="tab_online_applications" width="100%">
                                <thead>
                                    <tr>
                                        <th><small>Sr.</small></th>
                                        <th><small>Submitted On</small></th>
                                        <th><small>Organization</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (!empty($applications)) {
                                    $sr = 0;
                                    foreach ($applications as $row) {
                                        $sr++;
                                        $displayDate = '';
                                        if (!empty($row['created_at'])) {
                                            $timestamp = strtotime($row['created_at']);
                                            $displayDate = $timestamp ? date('d M Y, h:i A', $timestamp) : $row['created_at'];
                                        }
                                        ?>
                                        <?php $isUnread = isset($row['read_flag']) && (int)$row['read_flag'] === 0; ?>
                                        <tr class="app-rec-row <?php echo $isUnread ? 'unread' : 'read'; ?>">
                                            <td><small><?php echo $sr; ?></small></td>
                                            <td><small><i class="fa fa-clock-o app-rec-date-icon"></i><?php echo htmlspecialchars($displayDate, ENT_QUOTES, 'UTF-8'); ?></small></td>
                                            <td class="app-rec-org">
                                                <small>
                                                    <i class="fa fa-building-o app-rec-org-icon"></i>
                                                    <a href="applicationReceivedView.php?id=<?php echo (int)$row['id']; ?>"><?php echo htmlspecialchars($row['org_name'], ENT_QUOTES, 'UTF-8'); ?></a>
                                                    <?php if ($isUnread) { ?><span class="app-rec-unread-pill"><i class="fa fa-bell"></i>Unread</span><?php } ?>
                                                </small>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="3"><small>No applications received yet.</small></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

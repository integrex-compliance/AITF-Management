<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Guidelines";
    include_once './header.php';
    include_once './dbConnect.php';
    require_once __DIR__ . '/include/rbac_helper.php';
    require_once __DIR__ . '/include/staff_guidelines_lib.php';

    date_default_timezone_set('Asia/Kolkata');

    $userRole = $_SESSION['role'] ?? '';
    $userId = $_SESSION['id'] ?? 0;
    $userName = $_SESSION['username'] ?? '';
    $userEmail = $_SESSION['email'] ?? ($_SESSION['email_id'] ?? '');
    $isTopMgmt = aitf_is_top_management_role($userRole) || Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin();

    // Fetch guidelines strictly for current user
    $guidelines = aitf_get_guidelines_for_current_user($db, $userId, $userRole, $userEmail);
    ?>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" style="margin-top: 30px; margin-bottom: 60px;">

                <!-- Page Header Panel -->
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background: #193048; color: #fff;">
                                <div class="row">
                                    <div class="col-xs-7 qms_customer_name" style="color: #fff; font-size: 15px;">
                                        <span><i class="fa fa-book"></i></span> Staff Guidelines
                                    </div>
                                    <div class="col-xs-5 text-right">
                                        <?php if ($isTopMgmt) { ?>
                                            <a href="addUsers.php#guidelines-dist-section" class="btn btn-xs btn-default" style="font-size: 11px; margin-right: 4px;">
                                                <i class="fa fa-paper-plane"></i> Sent to Users
                                            </a>
                                            <a href="masterCreator.php" class="btn btn-xs btn-info" style="font-size: 11px;">
                                                <i class="fa fa-plus"></i> Master Creator
                                            </a>
                                        <?php } else { ?>
                                            <span class="label label-info" style="font-size: 11px;">
                                                <i class="fa fa-user"></i> <?php echo htmlspecialchars($userName ?: $userRole); ?>
                                            </span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guidelines List Section -->
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <?php if (empty($guidelines)) { ?>
                            <div class="panel panel-default">
                                <div class="panel-body text-center text-muted" style="padding: 40px 20px;">
                                    <i class="fa fa-folder-open-o" style="font-size: 36px; color: #bbb; margin-bottom: 10px; display: block;"></i>
                                    <h4 style="color: #555; margin-top: 0;">
                                        <?php echo $isTopMgmt ? 'No Guidelines Created Yet' : 'No Guidelines Assigned to You'; ?>
                                    </h4>
                                    <p style="font-size: 13px; margin-bottom: 0;">
                                        <?php if ($isTopMgmt) { ?>
                                            No guidelines have been posted in the system yet. Use Master Creator to post new guidelines.
                                        <?php } else { ?>
                                            You do not have any guidelines assigned to your user account at this moment.
                                        <?php } ?>
                                    </p>
                                    <?php if ($isTopMgmt) { ?>
                                        <div style="margin-top: 15px;">
                                            <a href="masterCreator.php" class="btn btn-xs btn-info" style="background: #193048; border-color: #193048; padding: 5px 12px;">
                                                <i class="fa fa-plus"></i> Create Guideline in Master
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <?php foreach ($guidelines as $g) { 
                                $createdTime = strtotime($g['created_at']);
                                $formattedDate = date('d M Y, h:i A', $createdTime);
                                $postedBy = !empty($g['created_by']) ? $g['created_by'] : 'Director';
                                if (in_array(strtolower(trim($postedBy)), ['main admin', 'top mgmt', 'admin', 'director'], true)) {
                                    $postedBy = 'Director';
                                }
                                $attachments = $g['attachments'] ?? [];
                                $recipients = $g['recipients'] ?? [];
                                $recCount = count($recipients);
                            ?>
                                <div class="panel panel-default guideline-card" id="guideline-card-<?php echo $g['id']; ?>" style="border-left: 4px solid #193048; margin-bottom: 20px;">
                                    <div class="panel-heading" style="background: #fbfbfb; padding: 12px 15px; border-bottom: 1px solid #eee;">
                                        <div class="row">
                                            <div class="col-sm-8">
                                                <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #193048;">
                                                    <i class="fa fa-file-text-o" style="color: #193048; margin-right: 5px;"></i>
                                                    <?php echo htmlspecialchars($g['title']); ?>
                                                </h4>
                                                <div style="font-size: 11.5px; color: #777; margin-top: 3px;">
                                                    <i class="fa fa-clock-o"></i> Posted on <strong><?php echo $formattedDate; ?></strong> by <strong><?php echo htmlspecialchars($postedBy); ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-sm-4 text-right">
                                                <?php if ($isTopMgmt) { ?>
                                                    <?php if ($recCount > 0) { ?>
                                                        <span class="label label-success" style="font-size: 10.5px; padding: 3px 6px;">
                                                            <i class="fa fa-users"></i> Sent to <?php echo $recCount; ?> User(s)
                                                        </span>
                                                    <?php } else { ?>
                                                        <span class="label label-warning" style="font-size: 10.5px; padding: 3px 6px;">
                                                            <i class="fa fa-clock-o"></i> Not Sent Yet
                                                        </span>
                                                    <?php } ?>
                                                    <a href="addUsers.php#guidelines-dist-section" class="btn btn-xs btn-primary" style="margin-left: 5px; font-size: 11px; padding: 2px 6px;" title="Manage recipients">
                                                        <i class="fa fa-paper-plane"></i> Sent
                                                    </a>
                                                    <button type="button" class="btn btn-xs btn-danger btn-delete-guideline" 
                                                            data-id="<?php echo $g['id']; ?>" 
                                                            data-title="<?php echo htmlspecialchars($g['title'], ENT_QUOTES); ?>"
                                                            style="margin-left: 2px; padding: 2px 5px;" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                <?php } else { ?>
                                                    <span class="label label-success" style="font-size: 10.5px; padding: 3px 6px;">
                                                        <i class="fa fa-check-circle"></i> Assigned to You
                                                    </span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body" style="padding: 15px;">
                                        <!-- Guideline Description -->
                                        <div style="font-size: 13px; color: #333; line-height: 1.6; margin-bottom: 12px; white-space: pre-line;">
                                            <?php echo nl2br(htmlspecialchars($g['description'])); ?>
                                        </div>

                                        <!-- For Management: Show Assigned Recipients List -->
                                        <?php if ($isTopMgmt && !empty($recipients)) { ?>
                                            <div style="margin-bottom: 12px; background: #f9f9f9; border: 1px solid #eee; border-radius: 4px; padding: 8px 12px;">
                                                <strong style="font-size: 11.5px; color: #555; display: block; margin-bottom: 4px;">
                                                    <i class="fa fa-users text-primary"></i> Assigned Recipients:
                                                </strong>
                                                <span style="font-size: 12px; color: #444;">
                                                    <?php 
                                                        $rNames = array_map(function($r) { 
                                                            return htmlspecialchars($r['user_name']) . (!empty($r['user_role']) ? ' <small class="text-muted">(' . htmlspecialchars($r['user_role']) . ')</small>' : ''); 
                                                        }, $recipients);
                                                        echo implode(', ', $rNames);
                                                    ?>
                                                </span>
                                            </div>
                                        <?php } ?>

                                        <!-- Attachments Section -->
                                        <?php if (!empty($attachments)) { ?>
                                            <div style="background: #f5f5f5; border: 1px solid #e3e3e3; border-radius: 4px; padding: 10px 12px; margin-top: 10px;">
                                                <strong style="font-size: 12px; color: #555; display: block; margin-bottom: 6px;">
                                                    <i class="fa fa-paperclip"></i> Attached Document(s):
                                                </strong>
                                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                                    <?php foreach ($attachments as $att) { 
                                                        $iconInfo = aitf_get_file_icon_info($att['original_name']);
                                                        $fileSizeFormatted = aitf_format_file_size($att['file_size']);
                                                    ?>
                                                        <a href="view_guideline_doc.php?id=<?php echo (int)$att['id']; ?>" target="_blank" 
                                                           class="btn btn-xs btn-default" style="text-align: left; font-size: 11.5px; padding: 4px 8px; background: #fff; border-color: #ccc;">
                                                            <i class="<?php echo $iconInfo['icon']; ?>" style="color: <?php echo $iconInfo['color']; ?>; margin-right: 4px;"></i>
                                                            <strong><?php echo htmlspecialchars($att['original_name']); ?></strong>
                                                            <small class="text-muted" style="margin-left: 3px;">(<?php echo $fileSizeFormatted; ?>)</small>
                                                            <i class="fa fa-external-link" style="font-size: 9px; margin-left: 4px; color: #888;"></i>
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete Guideline handler (Management only)
    $('.btn-delete-guideline').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var title = $(this).data('title');

        var confirmDelete = function() {
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
                        $('#guideline-card-' + id).fadeOut(300, function() {
                            $(this).remove();
                            if ($('.guideline-card').length === 0) {
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
                text: 'Are you sure you want to delete "' + title + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    confirmDelete();
                }
            });
        } else {
            if (confirm('Are you sure you want to delete "' + title + '"?')) {
                confirmDelete();
            }
        }
    });
});
</script>

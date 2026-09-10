<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Guidelines";
    include_once './header.php';
    include_once './dbConnect.php';
    require_once __DIR__ . '/include/rbac_helper.php';
    require_once __DIR__ . '/include/staff_guidelines_lib.php';

    date_default_timezone_set('Asia/Kolkata');

    $userRole = $_SESSION['role'] ?? '';
    $isTopMgmt = aitf_is_top_management_role($userRole);

    // Fetch guidelines for the current user's role
    $guidelines = aitf_get_guidelines_for_user($db, $userRole);
    ?>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid" style="max-width: 1440px; margin: 0 auto; padding: 25px 20px 90px 20px;">

                <!-- Clean Page Header (News & Updates Style) -->
                <div class="guidelines-clean-header">
                    <div class="header-text-group">
                        <div class="header-icon-box">
                            <i class="fa fa-book"></i>
                        </div>
                        <div>
                            <h1 class="page-clean-title">Staff Guidelines</h1>
                            <p class="page-clean-subtitle">
                                 standard operating procedures.
                            </p>
                        </div>
                    </div>
                    <?php if ($isTopMgmt) { ?>
                     
                    <?php } ?>
                </div>

                <!-- Guidelines Cards Section (News Feed Style) -->
                <?php if (empty($guidelines)) { ?>
                    <div class="empty-clean-state">
                        <div class="empty-icon-circle">
                            <i class="fa fa-folder-open-o"></i>
                        </div>
                        <h3 class="empty-title">No Guidelines Available</h3>
                     
                    </div>
                <?php } else { ?>

                    <div class="clean-news-grid">
                        <?php foreach ($guidelines as $g) { 
                            $createdTime = strtotime($g['created_at']);
                            $formattedDate = date('d M Y, h:i A', $createdTime);
                            $postedBy = !empty($g['created_by']) ? $g['created_by'] : 'Director';
                            if (in_array(strtolower(trim($postedBy)), ['main admin', 'top mgmt', 'admin', 'director'], true)) {
                                $postedBy = 'Director';
                            }
                            $targetRoleText = ($g['target_role'] === 'All' || $g['target_role'] === 'All Roles' || $g['target_role'] === 'All Staff') ? 'All Staff' : $g['target_role'];
                            $badgeStyle = aitf_get_role_badge_style($g['target_role']);
                            $attachments = $g['attachments'] ?? [];
                        ?>
                            <div class="clean-news-card" id="guideline-card-<?php echo $g['id']; ?>">
                                
                                <!-- Decorative Top Color Bar -->
                                <div class="card-top-accent-bar" style="background: <?php echo $badgeStyle['text']; ?>;"></div>

                                <div class="card-body-clean">
                                    
                                    <!-- Top Meta: Role Badge + Delete (for Management) -->
                                    <div class="card-top-meta">
                                        <span class="clean-badge" style="background: <?php echo $badgeStyle['bg']; ?>; color: <?php echo $badgeStyle['text']; ?>; border: 1.5px solid <?php echo $badgeStyle['border']; ?>;">
                                            <i class="<?php echo $badgeStyle['icon']; ?>"></i> FOR: <?php echo strtoupper(htmlspecialchars($targetRoleText)); ?>
                                        </span>

                                        <?php if ($isTopMgmt) { ?>
                                            <button type="button" class="btn-delete-guideline" 
                                                    data-id="<?php echo $g['id']; ?>" 
                                                    data-title="<?php echo htmlspecialchars($g['title'], ENT_QUOTES); ?>"
                                                    title="Delete this guideline">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        <?php } ?>
                                    </div>

                                    <!-- Posted at Date & Author Meta Line -->
                                    <div class="card-posted-meta">
                                        <i class="fa fa-clock-o"></i> 
                                        <span>posted at <strong><?php echo $formattedDate; ?></strong> posted by <strong><?php echo htmlspecialchars($postedBy); ?></strong></span>
                                    </div>

                                    <!-- Headline / Title -->
                                    <h2 class="card-headline">
                                        <?php echo htmlspecialchars($g['title']); ?>
                                    </h2>

                                    <!-- Description / Body in highlighted container -->
                                    <div class="card-excerpt-box">
                                        <?php echo nl2br(htmlspecialchars($g['description'])); ?>
                                    </div>

                                    <!-- Attachments Section (News & Updates Doc Pills) -->
                                    <?php if (!empty($attachments)) { ?>
                                        <div class="card-docs-wrapper">
                                            <div class="docs-label">
                                                <span><i class="fa fa-paperclip text-primary"></i> <strong>Attached Document(s):</strong></span>
                                                <span class="read-hint-badge"><i class="fa fa-eye"></i> Click to Read Online</span>
                                            </div>
                                            <div class="docs-pills-list">
                                                <?php foreach ($attachments as $att) { 
                                                    $iconInfo = aitf_get_file_icon_info($att['original_name']);
                                                    $fileSizeFormatted = aitf_format_file_size($att['file_size']);
                                                    $viewUrl = 'view_guideline_doc.php?id=' . (int)$att['id'];
                                                ?>
                                                    <a href="<?php echo $viewUrl; ?>" target="_blank" rel="noopener noreferrer" 
                                                       class="pill-doc-download" 
                                                       title="Click to open and read document online in browser">
                                                        <div class="doc-icon-wrap" style="color: <?php echo $iconInfo['color']; ?>;">
                                                            <i class="<?php echo $iconInfo['icon']; ?>"></i>
                                                        </div>
                                                        <div class="doc-info-wrap">
                                                            <span class="pill-doc-name"><?php echo htmlspecialchars($att['original_name']); ?></span>
                                                            <span class="pill-doc-size"><?php echo $fileSizeFormatted; ?></span>
                                                        </div>
                                                        <div class="doc-open-btn">
                                                            <i class="fa fa-external-link"></i> Read
                                                        </div>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <!-- Card Footer Line -->
                                    <div class="card-footer-line">
                                        <span class="directive-tag">
                                            <i class="fa fa-shield text-success"></i> Official AITF Directive
                                        </span>
                                        <?php if (!empty($attachments)) { ?>
                                            <a href="view_guideline_doc.php?id=<?php echo (int)$attachments[0]['id']; ?>" target="_blank" rel="noopener noreferrer" class="btn-read-online">
                                                Read Online <i class="fa fa-arrow-right"></i>
                                            </a>
                                        <?php } else { ?>
                                            <span class="notice-only-badge">
                                                <i class="fa fa-check-circle"></i> Instruction Notice
                                            </span>
                                        <?php } ?>
                                    </div>

                                </div>
                            </div>
                        <?php } ?>
                    </div>

                <?php } ?>

            </div>
        </div>
    </div>
</div>

<style>
/* Modern News & Updates Look & Feel for Guidelines */
:root {
    --aitf-blue: #0d4d8b;
    --aitf-navy: #052c54;
    --aitf-orange: #f5821f;
    --aitf-bg: #f8fafc;
    --aitf-border: #e2e8f0;
    --aitf-text: #0f172a;
    --aitf-muted: #64748b;
    --card-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
    --card-hover-shadow: 0 16px 32px -4px rgba(13, 77, 139, 0.12), 0 4px 12px -2px rgba(0, 0, 0, 0.04);
}

body {
    background-color: var(--aitf-bg);
    color: var(--aitf-text);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* Page Header */
.guidelines-clean-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 28px;
    padding: 8px 4px 20px 4px;
    border-bottom: 2px solid #e2e8f0;
}

.header-text-group {
    display: flex;
    align-items: center;
    gap: 16px;
}

.header-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, #0d4d8b 0%, #052c54 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    box-shadow: 0 6px 16px rgba(13, 77, 139, 0.25);
    flex-shrink: 0;
}

.page-clean-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #0a2540;
    margin: 0 0 4px 0;
    letter-spacing: -0.5px;
    line-height: 1.2;
}

.page-clean-subtitle {
    font-size: 0.98rem;
    color: #64748b;
    margin: 0;
    font-weight: 400;
}

.btn-post-action {
    background: linear-gradient(135deg, #0d4d8b 0%, #0369a1 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.94rem;
    padding: 10px 22px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(13, 77, 139, 0.25);
    transition: all 0.25s ease;
    text-decoration: none !important;
}

.btn-post-action:hover {
    background: linear-gradient(135deg, #0f5aa3 0%, #0d4d8b 100%);
    box-shadow: 0 6px 18px rgba(13, 77, 139, 0.35);
    transform: translateY(-2px);
    color: #fff !important;
}

/* Grid Layout */
.clean-news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 26px;
    margin-bottom: 60px;
}

/* Cards */
.clean-news-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: var(--card-shadow);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, border-color 0.25s ease;
    position: relative;
}

.clean-news-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--card-hover-shadow);
    border-color: #cbd5e1;
}

/* Top Accent Line */
.card-top-accent-bar {
    height: 4.5px;
    width: 100%;
}

.card-body-clean {
    padding: 24px 26px 22px 26px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

/* Top Meta Row */
.card-top-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.clean-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.btn-delete-guideline {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #ef4444;
    font-size: 14px;
    cursor: pointer;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.btn-delete-guideline:hover {
    color: #ffffff;
    background: #dc2626;
    border-color: #dc2626;
    transform: scale(1.05);
}

/* Posted at Date & Author Meta Line */
.card-posted-meta {
    font-size: 0.88rem;
    color: #64748b;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    line-height: 1.4;
}

.card-posted-meta i {
    color: #94a3b8;
    font-size: 14px;
}

.card-posted-meta strong {
    color: #1e293b;
    font-weight: 700;
}

/* Card Headline / Title */
.card-headline {
    font-size: 1.32rem;
    font-weight: 800;
    line-height: 1.35;
    margin: 0 0 14px 0;
    color: #0f172a;
    letter-spacing: -0.3px;
}

/* Excerpt / Body Highlight Box */
.card-excerpt-box {
    font-size: 0.96rem;
    color: #334155;
    line-height: 1.7;
    margin: 0 0 18px 0;
    flex-grow: 1;
    background: #f8fafc;
    border-left: 4px solid #0d4d8b;
    border-radius: 0 10px 10px 0;
    padding: 14px 18px;
    word-break: break-word;
}

/* Attachments Wrapper */
.card-docs-wrapper {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 18px;
}

.docs-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.read-hint-badge {
    font-size: 0.74rem;
    color: #059669;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 600;
}

.docs-pills-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Interactive Pill Document Item */
.pill-doc-download {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 10px 14px;
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.pill-doc-download:hover {
    border-color: #3b82f6;
    background: #f0f7ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    text-decoration: none !important;
}

.doc-icon-wrap {
    font-size: 22px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
}

.doc-info-wrap {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    overflow: hidden;
}

.pill-doc-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: #1e40af;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}

.pill-doc-size {
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 500;
    margin-top: 1px;
}

.doc-open-btn {
    background: #0ea5e9;
    color: #ffffff;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
    transition: background 0.2s;
}

.pill-doc-download:hover .doc-open-btn {
    background: #0284c7;
}

/* Card Footer Line */
.card-footer-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
    margin-top: auto;
}

.directive-tag {
    font-size: 0.84rem;
    color: #64748b;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-read-online {
    background: linear-gradient(135deg, #0d4d8b 0%, #0369a1 100%);
    color: #ffffff !important;
    font-size: 0.88rem;
    font-weight: 700;
    padding: 8px 18px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 3px 10px rgba(13, 77, 139, 0.2);
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.btn-read-online:hover {
    background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
    box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
    transform: translateY(-2px);
    color: #ffffff !important;
    text-decoration: none !important;
}

.notice-only-badge {
    font-size: 0.84rem;
    color: #059669;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Empty State */
.empty-clean-state {
    text-align: center;
    padding: 70px 20px;
    background: #ffffff;
    border: 1.5px dashed var(--aitf-border);
    border-radius: 16px;
    margin-top: 20px;
    box-shadow: var(--card-shadow);
}

.empty-icon-circle {
    width: 72px;
    height: 72px;
    background: #f1f5f9;
    color: #94a3b8;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    margin: 0 auto 16px auto;
}

.empty-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 8px 0;
}

.empty-desc {
    font-size: 0.98rem;
    color: #64748b;
    max-width: 500px;
    margin: 0 auto;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .clean-news-grid {
        grid-template-columns: 1fr;
    }
    .guidelines-clean-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .btn-post-action {
        width: 100%;
        justify-content: center;
    }
}
</style>

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
                            if ($('.clean-news-card').length === 0) {
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

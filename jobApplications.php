<div id="wrapper" class="active">
<?php
$pageIdentifier = "Job Applications";
include 'dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

// Auto-create table
if (isset($db)) {
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS job_applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            contact_no VARCHAR(20) NOT NULL,
            apply_for VARCHAR(80) NULL,
            message TEXT,
            resume_path VARCHAR(500),
            resume_original_name VARCHAR(255),
            read_flag TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        try {
            $db->exec("ALTER TABLE job_applications ADD COLUMN apply_for VARCHAR(80) NULL AFTER contact_no");
        } catch (Exception $ignored) {
        }
    } catch (Exception $e) {
        error_log('Job applications table: ' . $e->getMessage());
    }
}

$jobApps = [];
$unreadJobCount = 0;
if (isset($db)) {
    try {
        $stmt = $db->query("SELECT * FROM job_applications ORDER BY created_at DESC");
        $jobApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($jobApps as $row) {
            if (isset($row['read_flag']) && (int)$row['read_flag'] === 0) {
                $unreadJobCount++;
            }
        }
    } catch (Exception $e) {
        $jobApps = [];
    }
}

include 'header.php';
?>

<style>
    .job-apps-card {
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.10);
        border-top: 4px solid #2563eb;
        padding: 0;
        overflow: hidden;
        margin-bottom: 30px;
    }

    .job-apps-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 24px 28px 18px 28px;
        border-bottom: 1px solid #f1f5f9;
    }

    .job-apps-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .job-apps-header-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-radius: 12px;
        color: #fff;
        font-size: 22px;
    }

    .job-apps-header h2 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.3px;
    }

    .job-apps-badge-total {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e0e7ff;
        color: #2563eb;
        font-size: 13px;
        font-weight: 700;
        border-radius: 20px;
        padding: 3px 14px;
        margin-left: 8px;
        letter-spacing: 0.2px;
    }

    .job-apps-badge-unread {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ef4444;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        border-radius: 20px;
        padding: 3px 13px;
        margin-left: 6px;
        letter-spacing: 0.2px;
        animation: pulseUnread 2s ease-in-out infinite;
    }

    @keyframes pulseUnread {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.25); }
        50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    }

    .job-apps-table-wrap {
        overflow-x: auto;
        padding: 0 0 0 0;
    }

    .job-apps-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
        font-size: 14px;
    }

    .job-apps-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 600;
        font-size: 12.5px;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        padding: 13px 16px;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
        text-align: left;
    }

    .job-apps-table tbody tr {
        transition: background 0.18s;
    }

    .job-apps-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }

    .job-apps-table tbody tr:hover {
        background: #eff6ff;
    }

    .job-apps-table tbody tr.job-row-unread {
        background: #fef9ee;
    }

    .job-apps-table tbody tr.job-row-unread:hover {
        background: #fef3c7;
    }

    .job-apps-table tbody td {
        padding: 13px 16px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .job-apps-table tbody td.job-name-cell {
        font-weight: 600;
        color: #1e293b;
    }

    .job-apps-table tbody td.job-email-cell a {
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
    }

    .job-apps-table tbody td.job-email-cell a:hover {
        text-decoration: underline;
    }

    .job-msg-truncated {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #64748b;
        cursor: help;
    }

    .job-badge-new {
        display: inline-block;
        background: #ef4444;
        color: #fff;
        font-size: 11.5px;
        font-weight: 700;
        border-radius: 6px;
        padding: 3px 12px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .job-badge-read {
        display: inline-block;
        background: #22c55e;
        color: #fff;
        font-size: 11.5px;
        font-weight: 700;
        border-radius: 6px;
        padding: 3px 12px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .job-resume-link {
        color: #2563eb;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: color 0.18s;
    }

    .job-resume-link:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    .job-resume-link i {
        font-size: 15px;
    }

    .job-no-resume {
        color: #94a3b8;
        font-size: 13px;
        font-style: italic;
    }

    .job-action-btns {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .job-btn-read {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 7px;
        padding: 6px 14px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.18s, box-shadow 0.18s;
        white-space: nowrap;
    }

    .job-btn-read:hover {
        background: #1d4ed8;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.18);
    }

    .job-btn-delete {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 7px;
        padding: 6px 14px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.18s, color 0.18s, box-shadow 0.18s;
        white-space: nowrap;
    }

    .job-btn-delete:hover {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.18);
    }

    .job-empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .job-empty-state-icon {
        font-size: 52px;
        color: #cbd5e1;
        margin-bottom: 16px;
    }

    .job-empty-state h4 {
        color: #64748b;
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 6px 0;
    }

    .job-empty-state p {
        color: #94a3b8;
        font-size: 14px;
        margin: 0;
    }

    .job-date-cell {
        white-space: nowrap;
        color: #64748b;
        font-size: 13px;
    }

    .job-serial-cell {
        color: #94a3b8;
        font-weight: 600;
        font-size: 13px;
    }

    @media (max-width: 768px) {
        .job-apps-header {
            padding: 18px 16px 14px 16px;
        }
        .job-apps-header h2 {
            font-size: 18px;
        }
        .job-apps-table tbody td,
        .job-apps-table thead th {
            padding: 10px 10px;
            font-size: 13px;
        }
    }
</style>

<div id="page-content-wrapper">
    <div class="page-content inset">
        <div class="container">
            <br><br>

            <div class="job-apps-card">
                <!-- Header -->
                <div class="job-apps-header">
                    <div class="job-apps-header-left">
                        <div class="job-apps-header-icon">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div>
                            <h2>
                                Job Applications
                                <span class="job-apps-badge-total"><?php echo count($jobApps); ?></span>
                                <?php if ($unreadJobCount > 0): ?>
                                    <span class="job-apps-badge-unread"><?php echo $unreadJobCount; ?> New</span>
                                <?php endif; ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <?php if (count($jobApps) > 0): ?>
                <div class="job-apps-table-wrap">
                    <table class="job-apps-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Applicant Name</th>
                                <th>Email</th>
                                <th>Contact No</th>
                                <th>Apply For</th>
                                <th>Message</th>
                                <th>Resume</th>
                                <th>Date &amp; Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobApps as $index => $app):
                                $isUnread = isset($app['read_flag']) && (int)$app['read_flag'] === 0;
                                $msgFull = isset($app['message']) ? htmlspecialchars($app['message']) : '';
                                $msgShort = mb_strlen($app['message'] ?? '') > 50
                                    ? htmlspecialchars(mb_substr($app['message'], 0, 50)) . '&hellip;'
                                    : $msgFull;
                                $formattedDate = isset($app['created_at'])
                                    ? date('d.m.Y H:i', strtotime($app['created_at']))
                                    : '-';
                            ?>
                            <tr class="<?php echo $isUnread ? 'job-row-unread' : ''; ?>">
                                <td class="job-serial-cell"><?php echo $index + 1; ?></td>
                                <td class="job-name-cell"><?php echo htmlspecialchars($app['full_name'] ?? ''); ?></td>
                                <td class="job-email-cell">
                                    <a href="mailto:<?php echo htmlspecialchars($app['email'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($app['email'] ?? ''); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($app['contact_no'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($app['apply_for'] ?? '-'); ?></td>
                                <td>
                                    <div class="job-msg-truncated" title="<?php echo $msgFull; ?>">
                                        <?php echo $msgShort ?: '<span style="color:#94a3b8;font-style:italic;">—</span>'; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($app['resume_path'])): ?>
                                        <a class="job-resume-link" href="<?php echo htmlspecialchars($app['resume_path']); ?>" target="_blank" download>
                                            <i class="fa fa-download"></i>
                                            <?php echo htmlspecialchars($app['resume_original_name'] ?? 'Download'); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="job-no-resume">No resume</span>
                                    <?php endif; ?>
                                </td>
                                <td class="job-date-cell"><?php echo $formattedDate; ?></td>
                                <td>
                                    <?php if ($isUnread): ?>
                                        <span class="job-badge-new">New</span>
                                    <?php else: ?>
                                        <span class="job-badge-read">Read</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="job-action-btns">
                                        <?php if ($isUnread): ?>
                                            <button class="job-btn-read" onclick="markJobRead(<?php echo (int)$app['id']; ?>)">
                                                <i class="fa fa-check"></i> Mark Read
                                            </button>
                                        <?php endif; ?>
                                        <button class="job-btn-delete" onclick="deleteJobApp(<?php echo (int)$app['id']; ?>)">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="job-empty-state">
                    <div class="job-empty-state-icon">
                        <i class="fa fa-inbox"></i>
                    </div>
                    <h4>No Applications Yet</h4>
                    <p>Job applications will appear here once candidates submit their details.</p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
</div>

<script>
function markJobRead(id) {
    $.ajax({
        url: 'submitJobApplication.php',
        type: 'POST',
        data: { action: 'mark_read', job_id: id },
        success: function(response) {
            location.reload();
        }
    });
}

function deleteJobApp(id) {
    if (confirm('Are you sure you want to delete this application?')) {
        $.ajax({
            url: 'submitJobApplication.php',
            type: 'POST',
            data: { action: 'delete', job_id: id },
            success: function(response) {
                location.reload();
            }
        });
    }
}
</script>

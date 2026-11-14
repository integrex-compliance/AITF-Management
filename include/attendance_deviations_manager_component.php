<?php
/**
 * Attendance Deviations Manager Component
 * Embedded within taskManager.php under the "Deviations" tab.
 * Accessible to Top Management, Admin, and Finance.
 */

if (!isset($db) || !($db instanceof PDO)) {
    require_once dirname(__DIR__) . '/dbConnect.php';
}
require_once __DIR__ . '/attendance_deviation_lib.php';

$loggedInUserRole = (string)($_SESSION['role'] ?? '');
$canManageDeviations = aitf_can_manage_deviations($loggedInUserRole);

if (!$canManageDeviations) {
    echo '<div class="alert alert-danger" style="margin: 20px 0; border-radius: 8px;">You do not have permission to view or manage attendance deviations.</div>';
    return;
}

$initialCounts = aitf_get_deviation_counts($db);
$initialDeviations = aitf_get_all_deviations($db, ['status' => 'all']);

// All system users for staff filter
$allStaffUsers = [];
try {
    $stStmt = $db->query("SELECT user_id, user_name, role FROM user WHERE is_deleted = '0' OR is_deleted = 0 OR is_deleted IS NULL ORDER BY user_name ASC");
    $allStaffUsers = $stStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>

<!-- Attendance Deviations Management Styles -->
<style>
.dev-mgr-wrapper {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    margin-bottom: 24px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

/* Stat Cards */
.dev-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.dev-stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
}

.dev-stat-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}

.dev-stat-card.is-active {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

.dev-stat-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    flex-shrink: 0;
}
.dev-stat-icon-wrap.icon-total { background: #eff6ff; color: #2563eb; }
.dev-stat-icon-wrap.icon-pending { background: #fffbeb; color: #d97706; }
.dev-stat-icon-wrap.icon-approved { background: #f0fdf4; color: #16a34a; }
.dev-stat-icon-wrap.icon-rejected { background: #fef2f2; color: #dc2626; }

.dev-stat-val {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

.dev-stat-lbl {
    font-size: 12.5px;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Control & Filter Bar */
.dev-control-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 20px;
}

.dev-status-pills {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.dev-status-btn {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #475569;
    font-size: 12.5px;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
}

.dev-status-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}

.dev-status-btn.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
}
.dev-status-btn.active .badge {
    background: #ffffff;
    color: #2563eb;
}

.dev-table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow-x: auto;
    background: #ffffff;
}

.dev-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.dev-table th {
    background: #f8fafc;
    color: #334155;
    font-weight: 700;
    padding: 12px 16px;
    border-bottom: 2px solid #e2e8f0;
    text-align: left;
    white-space: nowrap;
}

.dev-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    color: #1e293b;
}

.dev-table tr:hover td {
    background: #fbfcfe;
}

.dev-badge-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.dev-badge-status.badge-pending {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}
.dev-badge-status.badge-approved {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.dev-badge-status.badge-rejected {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.dev-btn-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid transparent;
}
.dev-btn-approve {
    background: #10b981;
    color: #ffffff;
    border-color: #10b981;
}
.dev-btn-approve:hover {
    background: #059669;
    border-color: #059669;
    color: #ffffff;
}
.dev-btn-reject {
    background: #ef4444;
    color: #ffffff;
    border-color: #ef4444;
}
.dev-btn-reject:hover {
    background: #dc2626;
    border-color: #dc2626;
    color: #ffffff;
}

/* Radio buttons in approve modal */
.dev-mark-option-card {
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 10px 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    transition: all 0.15s ease;
    background: #ffffff;
}
.dev-mark-option-card:hover {
    border-color: #2563eb;
    background: #f8fafc;
}
.dev-mark-option-card.is-selected {
    border-color: #2563eb;
    background: #eff6ff;
}
.dev-mark-option-card input[type="radio"] {
    margin: 0;
    cursor: pointer;
}
</style>

<div class="dev-mgr-wrapper">

    <!-- Section Title & Instructions -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 14px;">
        <div>
            <h3 style="margin: 0; font-size: 19px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 9px;">
                <i class="fa fa-calendar-check-o" style="color: #2563eb;"></i> Attendance Deviations & Regularization
            </h3>
            <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">
                Review, approve, or reject attendance deviation requests submitted by staff members.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" id="btnRefreshDeviations" class="btn btn-default btn-sm" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 7px; font-weight: 600; color: #334155; font-size: 12.5px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa fa-refresh"></i> Refresh
            </button>
        </div>
    </div>

    <!-- 1. Metric Stat Cards -->
    <div class="dev-stats-grid">
        <div class="dev-stat-card is-active" data-status-filter="all">
            <div class="dev-stat-icon-wrap icon-total">
                <i class="fa fa-list"></i>
            </div>
            <div>
                <div class="dev-stat-val" id="statDevTotal"><?php echo $initialCounts['total']; ?></div>
                <div class="dev-stat-lbl">Total Requests</div>
            </div>
        </div>

        <div class="dev-stat-card" data-status-filter="pending">
            <div class="dev-stat-icon-wrap icon-pending">
                <i class="fa fa-clock-o"></i>
            </div>
            <div>
                <div class="dev-stat-val" style="color: #d97706;" id="statDevPending"><?php echo $initialCounts['pending']; ?></div>
                <div class="dev-stat-lbl">Pending Review</div>
            </div>
        </div>

        <div class="dev-stat-card" data-status-filter="approved">
            <div class="dev-stat-icon-wrap icon-approved">
                <i class="fa fa-check-circle"></i>
            </div>
            <div>
                <div class="dev-stat-val" style="color: #16a34a;" id="statDevApproved"><?php echo $initialCounts['approved']; ?></div>
                <div class="dev-stat-lbl">Approved</div>
            </div>
        </div>

        <div class="dev-stat-card" data-status-filter="rejected">
            <div class="dev-stat-icon-wrap icon-rejected">
                <i class="fa fa-times-circle"></i>
            </div>
            <div>
                <div class="dev-stat-val" style="color: #dc2626;" id="statDevRejected"><?php echo $initialCounts['rejected']; ?></div>
                <div class="dev-stat-lbl">Rejected</div>
            </div>
        </div>
    </div>

    <!-- 2. Controls & Search Filter Bar -->
    <div class="dev-control-bar">
        <!-- Status Tabs / Pills -->
        <div class="dev-status-pills">
            <button type="button" class="dev-status-btn active" data-status="all">
                All Requests <span class="badge" id="pillBadgeTotal" style="margin-left: 4px;"><?php echo $initialCounts['total']; ?></span>
            </button>
            <button type="button" class="dev-status-btn" data-status="pending">
                <i class="fa fa-clock-o" style="color: #d97706;"></i> Pending <span class="badge" id="pillBadgePending" style="margin-left: 4px; background: #d97706;"><?php echo $initialCounts['pending']; ?></span>
            </button>
            <button type="button" class="dev-status-btn" data-status="approved">
                <i class="fa fa-check-circle" style="color: #16a34a;"></i> Approved <span class="badge" id="pillBadgeApproved" style="margin-left: 4px;"><?php echo $initialCounts['approved']; ?></span>
            </button>
            <button type="button" class="dev-status-btn" data-status="rejected">
                <i class="fa fa-times-circle" style="color: #dc2626;"></i> Rejected <span class="badge" id="pillBadgeRejected" style="margin-left: 4px;"><?php echo $initialCounts['rejected']; ?></span>
            </button>
        </div>

        <!-- Search & Filter Controls -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <!-- Filter by Staff -->
            <select id="devFilterStaff" class="form-control input-sm" style="height: 34px; font-size: 12.5px; border-radius: 7px; width: 180px;">
                <option value="">All Staff Members</option>
                <?php foreach ($allStaffUsers as $su): ?>
                    <option value="<?php echo (int)$su['user_id']; ?>">
                        <?php echo htmlspecialchars($su['user_name']); ?> (<?php echo htmlspecialchars($su['role']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Text Search Filter -->
            <div style="position: relative; min-width: 200px;">
                <input type="text" id="devSearchInput" class="form-control input-sm" placeholder="Search staff, date, issue..." style="height: 34px; border-radius: 7px; padding-left: 28px; font-size: 12.5px;">
                <i class="fa fa-search" style="position: absolute; left: 9px; top: 10px; color: #94a3b8; font-size: 12px;"></i>
            </div>
        </div>
    </div>

    <!-- 3. Deviations Data Table -->
    <div class="dev-table-wrap">
        <table class="dev-table" id="devMainTable">
            <thead>
                <tr>
                    <th style="width: 50px;">#ID</th>
                    <th style="width: 180px;">Staff Member</th>
                    <th style="width: 140px;">Attendance Date</th>
                    <th>Explained Issue</th>
                    <th style="width: 120px; text-align: center;">Proof</th>
                    <th style="width: 140px;">Status</th>
                    <th style="width: 160px;">Reviewed By</th>
                    <th style="width: 170px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="devTableBody">
                <!-- Dynamically populated via JS -->
            </tbody>
        </table>
    </div>

</div>

<!-- 4. Modal: Approve Deviation & Mark Day Attendance -->
<div class="modal fade" id="modalApproveDeviation" tabindex="-1" role="dialog" aria-labelledby="modalApproveDeviationLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 500px; margin-top: 60px;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px rgba(0,0,0,0.12);">
            <div class="modal-header" style="background: linear-gradient(135deg, #15803d 0%, #10b981 100%); color: #ffffff; padding: 16px 20px;">
                <h5 class="modal-title" id="modalApproveDeviationLabel" style="font-weight: 700; font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-check-circle"></i> Approve Attendance Deviation
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #ffffff; opacity: 0.85; text-shadow: none; font-size: 20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px; background: #ffffff;">
                <input type="hidden" id="approveDevId" value="">

                <!-- Summary Info Box -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px; margin-bottom: 18px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="color: #166534;">Staff Member:</span>
                        <strong id="approveModalUserName" style="color: #0f172a;">--</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="color: #166534;">Deviation Date:</span>
                        <strong id="approveModalAttDate" style="color: #0f172a;">--</strong>
                    </div>
                    <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #bbf7d0; font-size: 12px; color: #166534;">
                        <strong>Staff Issue:</strong> <span id="approveModalIssue">--</span>
                    </div>
                </div>

                <!-- Mark this day as Selection (Requirement) -->
                <div style="margin-bottom: 14px;">
                    <label style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: block;">
                        Mark this day as: <span style="color: #dc2626;">*</span>
                    </label>

                    <label class="dev-mark-option-card is-selected">
                        <input type="radio" name="modal_marked_as" value="present" checked>
                        <div style="flex: 1;">
                            <strong style="color: #15803d; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa fa-check-circle" style="color: #16a34a;"></i> Present (Full Day Attendance)
                            </strong>
                            <div style="font-size: 11.5px; color: #64748b;">Mark full day attendance present in records.</div>
                        </div>
                    </label>

                    <label class="dev-mark-option-card">
                        <input type="radio" name="modal_marked_as" value="halfday">
                        <div style="flex: 1;">
                            <strong style="color: #c2410c; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa fa-hourglass-half" style="color: #ea580c;"></i> Half Day
                            </strong>
                            <div style="font-size: 11.5px; color: #64748b;">Mark as half day shift (4.5 hours credited).</div>
                        </div>
                    </label>

                    <label class="dev-mark-option-card">
                        <input type="radio" name="modal_marked_as" value="late">
                        <div style="flex: 1;">
                            <strong style="color: #b45309; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa fa-clock-o" style="color: #d97706;"></i> Late Login
                            </strong>
                            <div style="font-size: 11.5px; color: #64748b;">Mark attendance with late login status.</div>
                        </div>
                    </label>

                    <label class="dev-mark-option-card">
                        <input type="radio" name="modal_marked_as" value="leave">
                        <div style="flex: 1;">
                            <strong style="color: #6b21a8; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa fa-calendar-minus-o" style="color: #7c3aed;"></i> Leave
                            </strong>
                            <div style="font-size: 11.5px; color: #64748b;">Mark officially as Leave for this day.</div>
                        </div>
                    </label>
                </div>

                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 9px 12px; font-size: 11.5px; color: #1e40af;">
                    <i class="fa fa-info-circle"></i> An automated approval confirmation email will be dispatched to the staff member immediately.
                </div>
            </div>
            <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px; font-size: 13px; font-weight: 600;">Cancel</button>
                <button type="button" id="btnConfirmApproveDev" class="btn btn-success" style="background: #10b981; border-color: #10b981; border-radius: 6px; font-size: 13px; font-weight: 600; padding: 6px 18px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa fa-check"></i> Confirm & Mark Attendance
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 5. Modal: Reject Deviation (Reason Compulsory) -->
<div class="modal fade" id="modalRejectDeviation" tabindex="-1" role="dialog" aria-labelledby="modalRejectDeviationLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 500px; margin-top: 60px;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px rgba(0,0,0,0.12);">
            <div class="modal-header" style="background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%); color: #ffffff; padding: 16px 20px;">
                <h5 class="modal-title" id="modalRejectDeviationLabel" style="font-weight: 700; font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-times-circle"></i> Reject Attendance Deviation
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #ffffff; opacity: 0.85; text-shadow: none; font-size: 20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px; background: #ffffff;">
                <input type="hidden" id="rejectDevId" value="">

                <!-- Summary Info Box -->
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 14px; margin-bottom: 18px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="color: #991b1b;">Staff Member:</span>
                        <strong id="rejectModalUserName" style="color: #0f172a;">--</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="color: #991b1b;">Deviation Date:</span>
                        <strong id="rejectModalAttDate" style="color: #0f172a;">--</strong>
                    </div>
                    <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #fca5a5; font-size: 12px; color: #991b1b;">
                        <strong>Staff Issue:</strong> <span id="rejectModalIssue">--</span>
                    </div>
                </div>

                <!-- Rejection Reason Textarea (Requirement: Compulsory) -->
                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 6px; display: block;">
                        Rejection Reason (Compulsory): <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea id="rejectModalReason" class="form-control" rows="4" placeholder="Explain why this request is rejected (e.g. insufficient proof, unverified absence, discrepancy in timings)..." required style="font-size: 13px; border-radius: 8px;"></textarea>
                    <span style="font-size: 11.5px; color: #64748b; margin-top: 4px; display: block;">
                        This rejection reason will be sent directly to the user in their email notification.
                    </span>
                </div>
            </div>
            <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px; font-size: 13px; font-weight: 600;">Cancel</button>
                <button type="button" id="btnConfirmRejectDev" class="btn btn-danger" style="background: #ef4444; border-color: #ef4444; border-radius: 6px; font-size: 13px; font-weight: 600; padding: 6px 18px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa fa-times"></i> Confirm Rejection
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 6. Modal: Proof Attachment Viewer -->
<div class="modal fade" id="modalProofViewer" tabindex="-1" role="dialog" aria-labelledby="modalProofViewerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="margin-top: 50px;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: 1px solid #cbd5e1;">
            <div class="modal-header" style="background: #0f172a; color: #ffffff; padding: 14px 20px;">
                <h5 class="modal-title" id="modalProofViewerLabel" style="font-weight: 700; font-size: 15px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-paperclip"></i> Submitted Proof Attachment
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #ffffff; opacity: 0.85; text-shadow: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px; text-align: center; background: #f8fafc; min-height: 250px;" id="proofViewerBody">
                <!-- Preview image or PDF frame inserted via JS -->
            </div>
            <div class="modal-footer" style="padding: 10px 20px; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between;">
                <a id="proofDownloadLink" href="#" target="_blank" download class="btn btn-sm btn-default" style="font-size: 12.5px; font-weight: 600;">
                    <i class="fa fa-download"></i> Download File
                </a>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="font-size: 12.5px; font-weight: 600;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Deviations Manager Script -->
<script>
(function() {
    var rawDeviations = <?php echo json_encode($initialDeviations, JSON_UNESCAPED_SLASHES); ?> || [];
    var currentFilterStatus = 'all';

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        var d = parseInt(parts[2], 10);
        var m = monthNames[parseInt(parts[1], 10) - 1] || parts[1];
        var y = parts[0];
        return d + ' ' + m + ' ' + y;
    }

    function renderDeviationsTable() {
        var statusFilter = currentFilterStatus;
        var staffFilter = $('#devFilterStaff').val();
        var searchQ = $.trim($('#devSearchInput').val()).toLowerCase();

        var filtered = rawDeviations.filter(function(item) {
            if (statusFilter !== 'all' && item.status !== statusFilter) {
                return false;
            }
            if (staffFilter && String(item.user_id) !== String(staffFilter)) {
                return false;
            }
            if (searchQ) {
                var searchBlob = (item.user_name + ' ' + item.user_role + ' ' + item.issue_reason + ' ' + item.attendance_date).toLowerCase();
                if (searchBlob.indexOf(searchQ) === -1) {
                    return false;
                }
            }
            return true;
        });

        if (filtered.length === 0) {
            $('#devTableBody').html(
                '<tr>' +
                    '<td colspan="8" style="text-align: center; color: #64748b; padding: 36px 20px;">' +
                        '<i class="fa fa-calendar-times-o" style="font-size: 26px; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>' +
                        'No attendance deviations found matching the current filters.' +
                    '</td>' +
                '</tr>'
            );
            return;
        }

        var html = '';
        filtered.forEach(function(item) {
            var id = item.id;
            var userName = escapeHtml(item.user_name || 'Staff');
            var userRole = escapeHtml(item.user_role || 'Staff');
            var userEmail = escapeHtml(item.user_email || '');
            var attDate = item.attendance_date;
            var formattedAttDate = formatDate(attDate);
            var issue = escapeHtml(item.issue_reason || '');
            var proof = item.attachment_path || '';
            var status = item.status;
            var markedAs = item.approval_status_marked || '';

            // Status Badge
            var statusBadge = '';
            var actionBtns = '';

            if (status === 'pending') {
                statusBadge = '<span class="dev-badge-status badge-pending"><i class="fa fa-clock-o"></i> Pending Review</span>';
                actionBtns = 
                    '<div style="display: flex; gap: 6px; justify-content: center;">' +
                        '<button type="button" class="dev-btn-action dev-btn-approve btn-open-approve" data-id="' + id + '" data-user="' + userName + '" data-date="' + formattedAttDate + '" data-issue="' + issue + '" title="Accept deviation & mark attendance">' +
                            '<i class="fa fa-check"></i> Accept' +
                        '</button>' +
                        '<button type="button" class="dev-btn-action dev-btn-reject btn-open-reject" data-id="' + id + '" data-user="' + userName + '" data-date="' + formattedAttDate + '" data-issue="' + issue + '" title="Reject deviation">' +
                            '<i class="fa fa-times"></i> Reject' +
                        '</button>' +
                    '</div>';
            } else if (status === 'approved') {
                var markedLabel = (markedAs === 'present' ? 'Present' : (markedAs === 'halfday' ? 'Half Day' : (markedAs === 'leave' ? 'Leave' : (markedAs === 'late' ? 'Late Login' : markedAs))));
                statusBadge = '<span class="dev-badge-status badge-approved" title="Marked as: ' + markedLabel + '"><i class="fa fa-check-circle"></i> Approved: ' + markedLabel + '</span>';
                actionBtns = '<span style="color: #16a34a; font-size: 12px; font-weight: 600;"><i class="fa fa-check"></i> Marked as ' + markedLabel + '</span>';
            } else if (status === 'rejected') {
                statusBadge = '<span class="dev-badge-status badge-rejected" title="' + escapeHtml(item.rejection_reason || '') + '"><i class="fa fa-times-circle"></i> Rejected</span>';
                actionBtns = '<span style="color: #dc2626; font-size: 11.5px; font-weight: 600;" title="' + escapeHtml(item.rejection_reason || '') + '"><i class="fa fa-ban"></i> Request Rejected</span>';
            }

            // Proof Cell
            var proofHtml = '<span style="color: #94a3b8; font-style: italic; font-size: 12px;">No proof</span>';
            if (proof) {
                proofHtml = '<button type="button" class="btn btn-xs btn-default btn-view-proof" data-url="' + escapeHtml(proof) + '" style="font-size: 11.5px; border-radius: 5px; color: #2563eb; font-weight: 600; border-color: #cbd5e1;">' +
                    '<i class="fa fa-paperclip"></i> View Proof' +
                '</button>';
            }

            // Reviewed By Info
            var reviewerInfo = '--';
            if (item.action_by_name) {
                reviewerInfo = '<div><strong style="color: #0f172a;">' + escapeHtml(item.action_by_name) + '</strong> (' + escapeHtml(item.action_by_role || '') + ')</div>' +
                    (item.action_at ? '<div style="font-size: 11px; color: #64748b;">' + item.action_at.substring(0, 16) + '</div>' : '');
                if (status === 'rejected' && item.rejection_reason) {
                    reviewerInfo += '<div style="margin-top: 4px; font-size: 11px; color: #dc2626; line-height: 1.3;" title="' + escapeHtml(item.rejection_reason) + '">' +
                        '<strong>Reason:</strong> ' + escapeHtml(item.rejection_reason) +
                    '</div>';
                }
            }

            html += '<tr>' +
                '<td><span style="color: #64748b; font-weight: 600;">#' + id + '</span></td>' +
                '<td>' +
                    '<div style="font-weight: 700; color: #0f172a;">' + userName + '</div>' +
                    '<div style="font-size: 11.5px; color: #64748b;">' + userRole + (userEmail ? ' &bull; ' + userEmail : '') + '</div>' +
                '</td>' +
                '<td>' +
                    '<span style="font-weight: 600; color: #1e40af; background: #eff6ff; padding: 3px 8px; border-radius: 6px; border: 1px solid #bfdbfe; font-size: 12px; white-space: nowrap;">' +
                        '<i class="fa fa-calendar"></i> ' + formattedAttDate +
                    '</span>' +
                '</td>' +
                '<td>' +
                    '<div style="max-width: 280px; word-break: break-word; line-height: 1.4; font-size: 12.5px;">' +
                        issue +
                    '</div>' +
                '</td>' +
                '<td style="text-align: center;">' + proofHtml + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' + reviewerInfo + '</td>' +
                '<td style="text-align: center;">' + actionBtns + '</td>' +
            '</tr>';
        });

        $('#devTableBody').html(html);
    }

    function refreshDeviationsData() {
        var $btn = $('#btnRefreshDeviations');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Refreshing...');

        $.ajax({
            url: 'attendance_deviation_action.php',
            type: 'GET',
            data: { action: 'get_deviations' },
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Refresh');
                if (resp && resp.success) {
                    rawDeviations = resp.deviations || [];
                    var counts = resp.counts || {};

                    $('#statDevTotal, #pillBadgeTotal').text(counts.total || 0);
                    $('#statDevPending, #pillBadgePending').text(counts.pending || 0);
                    $('#statDevApproved, #pillBadgeApproved').text(counts.approved || 0);
                    $('#statDevRejected, #pillBadgeRejected').text(counts.rejected || 0);

                    // Update parent tab badge counter
                    if (typeof updateParentDevBadge === 'function') {
                        updateParentDevBadge(counts.pending || 0);
                    }

                    renderDeviationsTable();
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Refresh');
            }
        });
    }

    // Expose refresh function globally
    window.refreshDeviationsList = refreshDeviationsData;

    $(document).ready(function() {
        renderDeviationsTable();

        // Metric Card Click Filter
        $('.dev-stat-card').on('click', function() {
            var st = $(this).data('status-filter');
            $('.dev-stat-card').removeClass('is-active');
            $(this).addClass('is-active');

            $('.dev-status-btn').removeClass('active');
            $('.dev-status-btn[data-status="' + st + '"]').addClass('active');

            currentFilterStatus = st;
            renderDeviationsTable();
        });

        // Status Button Filter
        $('.dev-status-btn').on('click', function() {
            var st = $(this).data('status');
            $('.dev-status-btn').removeClass('active');
            $(this).addClass('active');

            $('.dev-stat-card').removeClass('is-active');
            $('.dev-stat-card[data-status-filter="' + st + '"]').addClass('is-active');

            currentFilterStatus = st;
            renderDeviationsTable();
        });

        // Search and User Filter
        $('#devSearchInput, #devFilterStaff').on('input change', function() {
            renderDeviationsTable();
        });

        // Refresh Button
        $('#btnRefreshDeviations').on('click', function() {
            refreshDeviationsData();
        });

        // View Proof click
        $(document).on('click', '.btn-view-proof', function() {
            var url = $(this).data('url');
            if (!url) return;

            var ext = url.split('.').pop().toLowerCase();
            $('#proofDownloadLink').attr('href', url);

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) !== -1) {
                $('#proofViewerBody').html('<img src="' + escapeHtml(url) + '" style="max-width: 100%; max-height: 520px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 1px solid #cbd5e1;" alt="Proof Image">');
            } else if (ext === 'pdf') {
                $('#proofViewerBody').html('<iframe src="' + escapeHtml(url) + '" style="width: 100%; height: 500px; border: none; border-radius: 8px;"></iframe>');
            } else {
                $('#proofViewerBody').html(
                    '<div style="padding: 30px;">' +
                        '<i class="fa fa-file-text-o" style="font-size: 38px; color: #2563eb; margin-bottom: 12px; display: block;"></i>' +
                        '<p style="font-size: 14px; color: #334155;">Document file preview is not supported directly in the browser.</p>' +
                        '<a href="' + escapeHtml(url) + '" target="_blank" download class="btn btn-primary" style="font-weight: 600;"><i class="fa fa-download"></i> Download Document</a>' +
                    '</div>'
                );
            }
            $('#modalProofViewer').modal('show');
        });

        // Option cards selection styling in Approve modal
        $(document).on('click', '.dev-mark-option-card', function() {
            $('.dev-mark-option-card').removeClass('is-selected');
            $(this).addClass('is-selected');
            $(this).find('input[type="radio"]').prop('checked', true);
        });

        // Open Approve Modal
        $(document).on('click', '.btn-open-approve', function() {
            var id = $(this).data('id');
            var user = $(this).data('user');
            var date = $(this).data('date');
            var issue = $(this).data('issue');

            $('#approveDevId').val(id);
            $('#approveModalUserName').text(user);
            $('#approveModalAttDate').text(date);
            $('#approveModalIssue').text(issue);

            // Default select 'present'
            $('.dev-mark-option-card').removeClass('is-selected');
            $('.dev-mark-option-card input[value="present"]').prop('checked', true).closest('.dev-mark-option-card').addClass('is-selected');

            $('#modalApproveDeviation').modal('show');
        });

        // Confirm Approve via AJAX
        $('#btnConfirmApproveDev').on('click', function() {
            var devId = $('#approveDevId').val();
            var markedAs = $('input[name="modal_marked_as"]:checked').val() || 'present';

            var $btn = $(this);
            var origHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Approving...');

            $.ajax({
                url: 'attendance_deviation_action.php',
                type: 'POST',
                data: {
                    action: 'approve_deviation',
                    deviation_id: devId,
                    marked_as: markedAs
                },
                dataType: 'json',
                success: function(resp) {
                    $btn.prop('disabled', false).html(origHtml);
                    $('#modalApproveDeviation').modal('hide');

                    if (resp && resp.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deviation Approved',
                                text: resp.message || 'Deviation has been approved and attendance recorded.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert(resp.message || 'Deviation approved successfully!');
                        }
                        refreshDeviationsData();
                    } else {
                        var err = (resp && resp.error) ? resp.error : 'Failed to approve deviation.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Approval Error', text: err });
                        } else {
                            alert(err);
                        }
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(origHtml);
                    var err = 'Failed to communicate with server.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                        err = xhr.responseJSON.error;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: err });
                    } else {
                        alert(err);
                    }
                }
            });
        });

        // Open Reject Modal
        $(document).on('click', '.btn-open-reject', function() {
            var id = $(this).data('id');
            var user = $(this).data('user');
            var date = $(this).data('date');
            var issue = $(this).data('issue');

            $('#rejectDevId').val(id);
            $('#rejectModalUserName').text(user);
            $('#rejectModalAttDate').text(date);
            $('#rejectModalIssue').text(issue);
            $('#rejectModalReason').val('');

            $('#modalRejectDeviation').modal('show');
        });

        // Confirm Reject via AJAX (Compulsory reason check)
        $('#btnConfirmRejectDev').on('click', function() {
            var devId = $('#rejectDevId').val();
            var reason = $.trim($('#rejectModalReason').val());

            if (!reason) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Reason Required',
                        text: 'A rejection reason is compulsory. Please explain why this request is being rejected.'
                    });
                } else {
                    alert('A rejection reason is compulsory. Please explain why this request is being rejected.');
                }
                $('#rejectModalReason').focus();
                return;
            }

            var $btn = $(this);
            var origHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Rejecting...');

            $.ajax({
                url: 'attendance_deviation_action.php',
                type: 'POST',
                data: {
                    action: 'reject_deviation',
                    deviation_id: devId,
                    rejection_reason: reason
                },
                dataType: 'json',
                success: function(resp) {
                    $btn.prop('disabled', false).html(origHtml);
                    $('#modalRejectDeviation').modal('hide');

                    if (resp && resp.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deviation Rejected',
                                text: resp.message || 'Deviation has been rejected and user notified.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert(resp.message || 'Deviation rejected successfully!');
                        }
                        refreshDeviationsData();
                    } else {
                        var err = (resp && resp.error) ? resp.error : 'Failed to reject deviation.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Rejection Error', text: err });
                        } else {
                            alert(err);
                        }
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(origHtml);
                    var err = 'Failed to communicate with server.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                        err = xhr.responseJSON.error;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: err });
                    } else {
                        alert(err);
                    }
                }
            });
        });

    });
})();
</script>

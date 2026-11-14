<?php
/**
 * Attendance Deviation Library
 * Handles attendance deviation requests, proofs, management approvals/rejections,
 * database updates, and email notifications.
 */

require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/rbac_helper.php';
require_once dirname(__DIR__) . '/testemail.php';

if (!function_exists('aitf_can_manage_deviations')) {
    /**
     * Check if a given role can view/approve/reject attendance deviations.
     * Allowed: Top Mgmt, Director, Main Admin, Admin, and Finance.
     */
    function aitf_can_manage_deviations($role = null) {
        if ($role === null) {
            $role = $_SESSION['role'] ?? '';
        }
        $r = strtolower(trim((string)$role));
        $allowed = [
            'top mgmt', 'top management', 'mgmt', 'management',
            'director', 'main admin', 'admin', 'finance'
        ];
        if (in_array($r, $allowed, true)) {
            return true;
        }
        if (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($role)) {
            return true;
        }
        if (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin())) {
            return true;
        }
        return false;
    }
}

if (!function_exists('aitf_get_user_info_for_deviation')) {
    /**
     * Fetch user's name, email, and role for deviation submission.
     */
    function aitf_get_user_info_for_deviation(PDO $db, $userId) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return null;
        }

        try {
            $stmt = $db->prepare("SELECT user_id, user_name, email_id, role FROM user WHERE user_id = :uid LIMIT 1");
            $stmt->execute([':uid' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'user_id' => (int)$row['user_id'],
                    'name' => trim((string)$row['user_name']),
                    'email' => trim((string)$row['email_id']),
                    'role' => trim((string)$row['role'])
                ];
            }
        } catch (Exception $e) {
            error_log("Error in aitf_get_user_info_for_deviation: " . $e->getMessage());
        }

        // Fallback to auditors table
        try {
            $aStmt = $db->prepare("SELECT auditors_id, auditor_name, auditor_email, login_email FROM auditors WHERE auditors_id = :aid LIMIT 1");
            $aStmt->execute([':aid' => $userId]);
            $aRow = $aStmt->fetch(PDO::FETCH_ASSOC);
            if ($aRow) {
                $email = !empty($aRow['login_email']) ? trim($aRow['login_email']) : trim((string)$aRow['auditor_email']);
                return [
                    'user_id' => (int)$aRow['auditors_id'],
                    'name' => trim((string)$aRow['auditor_name']),
                    'email' => $email,
                    'role' => 'Auditor'
                ];
            }
        } catch (Exception $e) {
            error_log("Error in aitf_get_user_info_for_deviation auditor lookup: " . $e->getMessage());
        }

        return null;
    }
}

if (!function_exists('aitf_send_deviation_email')) {
    /**
     * Send email notification to user regarding deviation request status.
     * Wrapped in try-catch so mail issues never disrupt the database transaction.
     */
    function aitf_send_deviation_email($toEmail, $toName, $subject, $htmlContent) {
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            error_log("aitf_send_deviation_email: Invalid or empty recipient email: " . var_export($toEmail, true));
            return false;
        }

        $emailTemplate = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>' . htmlspecialchars($subject) . '</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 20px; color: #1e293b; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .email-header { background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%); color: #ffffff; padding: 24px; text-align: center; }
        .email-header h2 { margin: 0; font-size: 20px; font-weight: 700; letter-spacing: -0.3px; }
        .email-header p { margin: 6px 0 0 0; font-size: 13px; opacity: 0.9; }
        .email-body { padding: 28px 24px; font-size: 14.5px; line-height: 1.6; color: #334155; }
        .email-box { background: #f8fafc; border-left: 4px solid #2563eb; border-radius: 6px; padding: 14px 18px; margin: 18px 0; }
        .email-box.box-approved { border-left-color: #10b981; background: #f0fdf4; }
        .email-box.box-rejected { border-left-color: #ef4444; background: #fef2f2; }
        .email-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 24px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2>AITF Management Certification</h2>
            <p>Attendance Management System</p>
        </div>
        <div class="email-body">' . $htmlContent . '</div>
        <div class="email-footer">
            &copy; ' . date('Y') . ' AITF Management Certification Limited. All rights reserved.<br>
            This is an automated notification, please do not reply directly to this email.
        </div>
    </div>
</body>
</html>';

        try {
            $sender = new AITFServiceEmailSender();
            return $sender->sendEmail($toEmail, $toName, $emailTemplate, $subject);
        } catch (Exception $e) {
            error_log("aitf_send_deviation_email failed for {$toEmail}: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('aitf_create_deviation')) {
    /**
     * Submit an attendance deviation request.
     */
    function aitf_create_deviation(PDO $db, array $data, $file = null) {
        $userId = (int)($data['user_id'] ?? 0);
        $date = trim((string)($data['attendance_date'] ?? ''));
        $issue = trim((string)($data['issue_reason'] ?? ''));

        if ($userId <= 0) {
            return ['success' => false, 'error' => 'Invalid user identification.'];
        }

        if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return ['success' => false, 'error' => 'Please provide a valid attendance date (YYYY-MM-DD).'];
        }

        // Cannot submit future dates
        if (strtotime($date) > strtotime(date('Y-m-d'))) {
            return ['success' => false, 'error' => 'Deviation cannot be applied for future dates.'];
        }

        if (empty($issue)) {
            return ['success' => false, 'error' => 'Please explain your issue for this attendance day.'];
        }

        // Check if pending deviation already exists for this user and date
        try {
            $chk = $db->prepare("SELECT id, status FROM aitf_attendance_deviations WHERE user_id = :uid AND attendance_date = :dt AND status = 'pending' LIMIT 1");
            $chk->execute([':uid' => $userId, ':dt' => $date]);
            if ($chk->fetch()) {
                return ['success' => false, 'error' => 'You already have a pending deviation request for ' . date('d M Y', strtotime($date)) . '. Management will review it soon.'];
            }
        } catch (Exception $e) {
            error_log("aitf_create_deviation check error: " . $e->getMessage());
        }

        // Fetch user metadata
        $userInfo = aitf_get_user_info_for_deviation($db, $userId);
        $userName = $userInfo['name'] ?? ($_SESSION['username'] ?? 'Staff Member');
        $userRole = $userInfo['role'] ?? ($_SESSION['role'] ?? 'Staff');
        $userEmail = !empty($userInfo['email']) ? $userInfo['email'] : ($_SESSION['email'] ?? '');

        // Handle File Attachment (Proof)
        $attachmentPath = null;
        if (!empty($file) && isset($file['name']) && !empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__) . '/uploads/attendance_deviations';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            $origName = basename($file['name']);
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];

            if (!in_array($ext, $allowedExts, true)) {
                return ['success' => false, 'error' => 'Invalid proof file format. Allowed: JPG, PNG, WEBP, PDF, DOC.'];
            }

            if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
                return ['success' => false, 'error' => 'Attachment file exceeds maximum size limit (10MB).'];
            }

            $newFileName = 'dev_' . $userId . '_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 8) . '.' . $ext;
            $destination = $uploadDir . '/' . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $attachmentPath = 'uploads/attendance_deviations/' . $newFileName;
            } else {
                error_log("Failed to move uploaded deviation proof to " . $destination);
            }
        }

        // Insert into database
        try {
            $stmt = $db->prepare("
                INSERT INTO aitf_attendance_deviations 
                (user_id, user_name, user_role, user_email, attendance_date, issue_reason, attachment_path, status, created_at)
                VALUES 
                (:uid, :uname, :urole, :uemail, :dt, :issue, :att, 'pending', NOW())
            ");
            $stmt->execute([
                ':uid' => $userId,
                ':uname' => $userName,
                ':urole' => $userRole,
                ':uemail' => $userEmail,
                ':dt' => $date,
                ':issue' => $issue,
                ':att' => $attachmentPath
            ]);
            $deviationId = (int)$db->lastInsertId();

            // Send Confirmation Email to User immediately
            // Requirement: "or jis user ne devaiation kiya h uske mail m deaviaton dalte hi ek mail ki we have recceived your deavation top managaement will review in your deavviation and help you soon"
            if (!empty($userEmail)) {
                $formattedDate = date('l, d F Y', strtotime($date));
                $subject = "Attendance Deviation Request Received - " . date('d M Y', strtotime($date));
                $html = '<p>Dear <strong>' . htmlspecialchars($userName) . '</strong>,</p>
                <p>We have received your attendance deviation request for <strong>' . htmlspecialchars($formattedDate) . '</strong>.</p>
                <div class="email-box">
                    <strong>Submission Summary:</strong><br>
                    <strong>Date:</strong> ' . htmlspecialchars($formattedDate) . '<br>
                    <strong>Explained Issue:</strong> ' . nl2br(htmlspecialchars($issue)) . '<br>' .
                    (!empty($attachmentPath) ? '<strong>Proof Attached:</strong> Yes (Submitted for verification)<br>' : '') .
                    '<strong>Status:</strong> <span style="color: #d97706; font-weight: 700;">Pending Review</span>
                </div>
                <p>Top management will review your deviation and help you soon.</p>
                <p>Best regards,<br><strong>Management Team</strong><br>AITF Management Certification Limited</p>';

                aitf_send_deviation_email($userEmail, $userName, $subject, $html);
            }

            return [
                'success' => true,
                'deviation_id' => $deviationId,
                'message' => 'Attendance deviation submitted successfully. A confirmation email has been sent to your email address.'
            ];
        } catch (Exception $e) {
            error_log("aitf_create_deviation insert error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while saving deviation: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('aitf_get_user_deviations_map')) {
    /**
     * Get user deviations keyed by attendance_date for quick calendar rendering.
     */
    function aitf_get_user_deviations_map(PDO $db, $userId) {
        $userId = (int)$userId;
        $map = [];
        if ($userId <= 0) {
            return $map;
        }

        try {
            $stmt = $db->prepare("
                SELECT id, user_id, user_name, attendance_date, issue_reason, attachment_path,
                       status, approval_status_marked, action_by_name, action_by_role,
                       rejection_reason, created_at, action_at
                FROM aitf_attendance_deviations
                WHERE user_id = :uid
                ORDER BY attendance_date DESC, id DESC
            ");
            $stmt->execute([':uid' => $userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $dt = $r['attendance_date'];
                // Keep the latest record for that date
                if (!isset($map[$dt])) {
                    $statusLabel = ucfirst($r['status']);
                    $markedLabel = '';
                    if ($r['status'] === 'approved' && !empty($r['approval_status_marked'])) {
                        $labels = [
                            'present' => 'Present',
                            'halfday' => 'Half Day',
                            'leave' => 'Leave',
                            'late' => 'Late Login'
                        ];
                        $markedLabel = $labels[$r['approval_status_marked']] ?? ucfirst($r['approval_status_marked']);
                    }
                    $map[$dt] = [
                        'id' => (int)$r['id'],
                        'date' => $dt,
                        'status' => $r['status'],
                        'status_label' => $statusLabel,
                        'approval_status_marked' => $r['approval_status_marked'],
                        'marked_label' => $markedLabel,
                        'issue_reason' => $r['issue_reason'],
                        'attachment_path' => $r['attachment_path'],
                        'action_by_name' => $r['action_by_name'],
                        'action_by_role' => $r['action_by_role'],
                        'rejection_reason' => $r['rejection_reason'],
                        'created_at' => $r['created_at'],
                        'action_at' => $r['action_at']
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("aitf_get_user_deviations_map error: " . $e->getMessage());
        }

        return $map;
    }
}

if (!function_exists('aitf_get_all_deviations')) {
    /**
     * Fetch all deviations for Management / Task Manager page with filters.
     */
    function aitf_get_all_deviations(PDO $db, array $filters = []) {
        $where = [];
        $params = [];

        $status = strtolower(trim((string)($filters['status'] ?? 'all')));
        if ($status !== '' && $status !== 'all' && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $where[] = "d.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($filters['user_id'])) {
            $where[] = "d.user_id = :uid";
            $params[':uid'] = (int)$filters['user_id'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim((string)$filters['search']) . '%';
            $where[] = "(d.user_name LIKE :s1 OR d.user_role LIKE :s2 OR d.issue_reason LIKE :s3 OR d.attendance_date LIKE :s4)";
            $params[':s1'] = $search;
            $params[':s2'] = $search;
            $params[':s3'] = $search;
            $params[':s4'] = $search;
        }

        if (!empty($filters['date_from'])) {
            $where[] = "d.attendance_date >= :dfrom";
            $params[':dfrom'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "d.attendance_date <= :dto";
            $params[':dto'] = $filters['date_to'];
        }

        $sql = "SELECT d.* FROM aitf_attendance_deviations d";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY CASE WHEN d.status = 'pending' THEN 0 ELSE 1 END, d.created_at DESC, d.id DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("aitf_get_all_deviations error: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('aitf_get_deviation_counts')) {
    /**
     * Get aggregate counts of deviations for dashboard badges and filters.
     */
    function aitf_get_deviation_counts(PDO $db) {
        $counts = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        try {
            $stmt = $db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM aitf_attendance_deviations
            ");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $counts['total'] = (int)($row['total'] ?? 0);
                $counts['pending'] = (int)($row['pending'] ?? 0);
                $counts['approved'] = (int)($row['approved'] ?? 0);
                $counts['rejected'] = (int)($row['rejected'] ?? 0);
            }
        } catch (Exception $e) {
            error_log("aitf_get_deviation_counts error: " . $e->getMessage());
        }
        return $counts;
    }
}

if (!function_exists('aitf_approve_deviation')) {
    /**
     * Approve an attendance deviation and update attendance table.
     * Options for marked status: 'present', 'halfday', 'leave', 'late'
     */
    function aitf_approve_deviation(PDO $db, $deviationId, $markedStatus, array $adminUser) {
        $deviationId = (int)$deviationId;
        $markedStatus = strtolower(trim((string)$markedStatus));

        $validStatuses = [
            'present' => 'Present',
            'halfday' => 'Half Day',
            'leave' => 'Leave',
            'late' => 'Late Login'
        ];

        if (!array_key_exists($markedStatus, $validStatuses)) {
            return ['success' => false, 'error' => 'Invalid attendance status selection. Options: Present, Half Day, Leave, Late Login.'];
        }

        $statusHumanLabel = $validStatuses[$markedStatus];

        // Fetch deviation record
        try {
            $stmt = $db->prepare("SELECT * FROM aitf_attendance_deviations WHERE id = :id FOR UPDATE");
            $db->beginTransaction();
            $stmt->execute([':id' => $deviationId]);
            $dev = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dev) {
                $db->rollBack();
                return ['success' => false, 'error' => 'Deviation record not found.'];
            }

            if ($dev['status'] !== 'pending') {
                $db->rollBack();
                return ['success' => false, 'error' => 'This deviation request has already been ' . $dev['status'] . '.'];
            }

            $userId = (int)$dev['user_id'];
            $attDate = $dev['attendance_date'];
            $userName = $dev['user_name'];
            $userEmail = $dev['user_email'];

            // 1. Update Deviation record
            $upDev = $db->prepare("
                UPDATE aitf_attendance_deviations 
                SET status = 'approved',
                    approval_status_marked = :mstatus,
                    action_by_user_id = :ab_uid,
                    action_by_name = :ab_name,
                    action_by_role = :ab_role,
                    action_at = NOW()
                WHERE id = :id
            ");
            $upDev->execute([
                ':mstatus' => $markedStatus,
                ':ab_uid' => (int)($adminUser['user_id'] ?? 0),
                ':ab_name' => (string)($adminUser['user_name'] ?? 'Management'),
                ':ab_role' => (string)($adminUser['role'] ?? 'Top Mgmt'),
                ':id' => $deviationId
            ]);

            // 2. Update or Insert in aitf_attendance
            // Determine default shift times based on marked status
            $inTime = null;
            $outTime = null;
            $workingHours = 0;

            if ($markedStatus === 'present') {
                $inTime = '09:30:00';
                $outTime = '18:30:00';
                $workingHours = 8.5;
            } else if ($markedStatus === 'late') {
                $inTime = '10:05:00';
                $outTime = '18:30:00';
                $workingHours = 8.0;
            } else if ($markedStatus === 'halfday') {
                $inTime = '09:30:00';
                $outTime = '14:00:00';
                $workingHours = 4.5;
            } else if ($markedStatus === 'leave') {
                $inTime = null;
                $outTime = null;
                $workingHours = 0;
            }

            $approverTag = " [Deviation Approved: Marked as {$statusHumanLabel} by {$adminUser['user_name']} on " . date('d M Y') . "]";

            $chkAtt = $db->prepare("SELECT id, remarks, in_time, out_time FROM aitf_attendance WHERE user_id = :uid AND attendance_date = :dt LIMIT 1 FOR UPDATE");
            $chkAtt->execute([':uid' => $userId, ':dt' => $attDate]);
            $existingAtt = $chkAtt->fetch(PDO::FETCH_ASSOC);

            if ($existingAtt) {
                // Update existing record
                $existingRemarks = trim((string)($existingAtt['remarks'] ?? ''));
                $newRemarks = ($existingRemarks !== '') ? ($existingRemarks . ' ' . $approverTag) : $approverTag;

                // If in_time already exists, retain it; otherwise use default
                $finalInTime = !empty($existingAtt['in_time']) ? $existingAtt['in_time'] : $inTime;
                $finalOutTime = !empty($existingAtt['out_time']) ? $existingAtt['out_time'] : $outTime;

                $upAtt = $db->prepare("
                    UPDATE aitf_attendance 
                    SET status = :st,
                        remarks = :rm,
                        in_time = COALESCE(in_time, :intime),
                        out_time = COALESCE(out_time, :outtime),
                        total_working_hours = CASE WHEN total_working_hours IS NULL OR total_working_hours = 0 THEN :hrs ELSE total_working_hours END,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $upAtt->execute([
                    ':st' => $markedStatus,
                    ':rm' => $newRemarks,
                    ':intime' => $finalInTime,
                    ':outtime' => $finalOutTime,
                    ':hrs' => $workingHours,
                    ':id' => $existingAtt['id']
                ]);
            } else {
                // Insert new record in aitf_attendance
                $insAtt = $db->prepare("
                    INSERT INTO aitf_attendance 
                    (user_id, system_code, attendance_date, status, in_time, out_time, total_working_hours, remarks, updated_at)
                    VALUES 
                    (:uid, 'aitf', :dt, :st, :intime, :outtime, :hrs, :rm, NOW())
                ");
                $insAtt->execute([
                    ':uid' => $userId,
                    ':dt' => $attDate,
                    ':st' => $markedStatus,
                    ':intime' => $inTime,
                    ':outtime' => $outTime,
                    ':hrs' => $workingHours,
                    ':rm' => $approverTag
                ]);
            }

            $db->commit();

            // 3. Send Approval Email to User
            // Requirement: "user deaviataion is reviewd and approved ,and {date} jis din ka deaviataion dala tha vo likho marked as {peresnet , half day ............} jo management ne kiya h vo likh k do"
            if (!empty($userEmail)) {
                $formattedAttDate = date('l, d F Y', strtotime($attDate));
                $subject = "Attendance Deviation Approved - " . date('d M Y', strtotime($attDate));
                $html = '<p>Dear <strong>' . htmlspecialchars($userName) . '</strong>,</p>
                <p>Your attendance deviation request has been reviewed and approved.</p>
                <div class="email-box box-approved">
                    <strong>Approval Details:</strong><br>
                    <strong>Date:</strong> ' . htmlspecialchars($formattedAttDate) . '<br>
                    <strong>Marked As:</strong> <span style="color: #15803d; font-size: 16px; font-weight: 700;">' . htmlspecialchars($statusHumanLabel) . '</span><br>
                    <strong>Reviewed By:</strong> ' . htmlspecialchars($adminUser['user_name'] ?? 'Management') . ' (' . htmlspecialchars($adminUser['role'] ?? 'Top Mgmt') . ')<br>
                    <strong>Approval Time:</strong> ' . date('d M Y, h:i A') . '
                </div>
                <p>Your attendance records for <strong>' . htmlspecialchars($formattedAttDate) . '</strong> have been updated in the system accordingly.</p>
                <p>Best regards,<br><strong>Management Team</strong><br>AITF Management Certification Limited</p>';

                aitf_send_deviation_email($userEmail, $userName, $subject, $html);
            }

            return [
                'success' => true,
                'message' => "Deviation approved successfully! Attendance for {$formattedAttDate} marked as {$statusHumanLabel}."
            ];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("aitf_approve_deviation error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while approving deviation: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('aitf_reject_deviation')) {
    /**
     * Reject an attendance deviation request. Rejection reason is strictly mandatory.
     */
    function aitf_reject_deviation(PDO $db, $deviationId, $rejectionReason, array $adminUser) {
        $deviationId = (int)$deviationId;
        $rejectionReason = trim((string)$rejectionReason);

        if (empty($rejectionReason)) {
            return ['success' => false, 'error' => 'A rejection reason is strictly required. Please specify why the deviation is being rejected.'];
        }

        try {
            $stmt = $db->prepare("SELECT * FROM aitf_attendance_deviations WHERE id = :id FOR UPDATE");
            $db->beginTransaction();
            $stmt->execute([':id' => $deviationId]);
            $dev = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dev) {
                $db->rollBack();
                return ['success' => false, 'error' => 'Deviation record not found.'];
            }

            if ($dev['status'] !== 'pending') {
                $db->rollBack();
                return ['success' => false, 'error' => 'This deviation request has already been ' . $dev['status'] . '.'];
            }

            $userName = $dev['user_name'];
            $userEmail = $dev['user_email'];
            $attDate = $dev['attendance_date'];

            // Update Deviation record to rejected
            $upDev = $db->prepare("
                UPDATE aitf_attendance_deviations 
                SET status = 'rejected',
                    rejection_reason = :reason,
                    action_by_user_id = :ab_uid,
                    action_by_name = :ab_name,
                    action_by_role = :ab_role,
                    action_at = NOW()
                WHERE id = :id
            ");
            $upDev->execute([
                ':reason' => $rejectionReason,
                ':ab_uid' => (int)($adminUser['user_id'] ?? 0),
                ':ab_name' => (string)($adminUser['user_name'] ?? 'Management'),
                ':ab_role' => (string)($adminUser['role'] ?? 'Top Mgmt'),
                ':id' => $deviationId
            ]);

            $db->commit();

            // Send Rejection Email to User
            // Requirement: "or fail hone mai bhi ek messagge ayega after carefully concideration or deaviation your requiest is rejecct and message of rejection { or maangement jab rejecrt karega to usko ek reaseon likhna compulsory hoga , or vo reson usko show kar do message m }"
            if (!empty($userEmail)) {
                $formattedAttDate = date('l, d F Y', strtotime($attDate));
                $subject = "Attendance Deviation Request Rejected - " . date('d M Y', strtotime($attDate));
                $html = '<p>Dear <strong>' . htmlspecialchars($userName) . '</strong>,</p>
                <p>After careful consideration of your deviation, your request has been rejected.</p>
                <div class="email-box box-rejected">
                    <strong>Rejection Details:</strong><br>
                    <strong>Date:</strong> ' . htmlspecialchars($formattedAttDate) . '<br>
                    <strong>Status:</strong> <span style="color: #dc2626; font-weight: 700;">Rejected</span><br>
                    <strong>Reason of Rejection:</strong> ' . nl2br(htmlspecialchars($rejectionReason)) . '<br>
                    <strong>Reviewed By:</strong> ' . htmlspecialchars($adminUser['user_name'] ?? 'Management') . ' (' . htmlspecialchars($adminUser['role'] ?? 'Top Mgmt') . ')
                </div>
                <p>If you have any queries regarding this decision, please speak directly with your department manager or management.</p>
                <p>Best regards,<br><strong>Management Team</strong><br>AITF Management Certification Limited</p>';

                aitf_send_deviation_email($userEmail, $userName, $subject, $html);
            }

            return [
                'success' => true,
                'message' => 'Attendance deviation request has been rejected, and notification email has been dispatched to the user.'
            ];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("aitf_reject_deviation error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while rejecting deviation: ' . $e->getMessage()];
        }
    }
}

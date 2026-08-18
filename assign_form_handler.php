<?php
ob_start();
session_start();
include 'dbConnect.php';
include 'utility.php';
require_once 'PHPMailerAutoload.php';

header('Content-Type: application/json');

if (!Utitlity::checkForUserMainAdmin()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'assign_auditor') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$formType = $_POST['form_type'] ?? '';
$formId = (int)($_POST['form_id'] ?? 0);
$auditorId = (int)($_POST['auditor_id'] ?? 0);

if (!$formType || !$formId || !$auditorId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Get Auditor Email
$stmtAuditor = $db->prepare("SELECT user_name, email_id FROM user WHERE user_id = ? AND role = 'Auditor'");
$stmtAuditor->execute([$auditorId]);
$auditor = $stmtAuditor->fetch(PDO::FETCH_ASSOC);

if (!$auditor) {
    echo json_encode(['status' => 'error', 'message' => 'Auditor not found']);
    exit;
}

$table = '';
if ($formType === 'audit_application') $table = 'online_audit_applications';
elseif ($formType === 'info_updation') $table = 'information_updation_forms';
elseif ($formType === 'nc_closing_form') $table = 'nc_closing_forms';
elseif ($formType === 'job_application') $table = 'job_applications';
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid form type']);
    exit;
}

try {
    $assignMessage = trim($_POST['assign_message'] ?? '');
    
    $stmt = $db->prepare("UPDATE $table SET assigned_to_auditor_id = ?, admin_assign_message = ? WHERE id = ?");
    $stmt->execute([$auditorId, $assignMessage, $formId]);

    // Send email to auditor
    if (!empty($auditor['email_id'])) {
        $to = $auditor['email_id'];
        $subject = "AITF has sent you a form, please review it";
        
        $message = "<html><body style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>";
        $message .= "<div style='background-color: #2563eb; color: #fff; padding: 20px; text-align: center;'>";
        $message .= "<h2 style='margin: 0; font-size: 22px;'>New Assignment ($formType)</h2>";
        $message .= "</div>";
        $message .= "<div style='padding: 30px;'>";
        $message .= "<p style='font-size: 16px;'>Hello <strong>" . htmlspecialchars($auditor['user_name']) . "</strong>,</p>";
        $message .= "<p style='font-size: 16px;'>AITF has assigned a new form to you. Please login to your dashboard to review the details.</p>";
        
        if (!empty($assignMessage)) {
            $message .= "<div style='background-color: #f8fafc; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0; border-radius: 4px;'>";
            $message .= "<p style='margin: 0 0 5px 0; color: #64748b; font-size: 13px; text-transform: uppercase; font-weight: bold;'>Message from Admin:</p>";
            $message .= "<p style='margin: 0; font-size: 15px; font-style: italic;'>" . nl2br(htmlspecialchars($assignMessage)) . "</p>";
            $message .= "</div>";
        }
        
        $message .= "<div style='text-align: center; margin: 30px 0;'>";
        $message .= "<a href='https://aitf.co.in/login.php' style='background-color: #3b82f6; color: #ffffff; text-decoration: none; padding: 12px 25px; font-size: 16px; border-radius: 5px; display: inline-block; font-weight: bold;'>Go to Dashboard</a>";
        $message .= "</div>";
        $message .= "</div></body></html>";
        
        require_once 'include/env_helper.php';
        $mailConfig = aitf_get_mail_config();
        
        $mailer = new PHPMailer;
        if (!empty($mailConfig['host'])) {
            $mailer->isSMTP();
            $mailer->Host = $mailConfig['host'];
            $mailer->SMTPAuth = !empty($mailConfig['username']);
            $mailer->Username = $mailConfig['username'];
            $mailer->Password = $mailConfig['password'];
            $mailer->SMTPSecure = $mailConfig['secure'];
            $mailer->Port = $mailConfig['port'];
        }
        
        $mailer->setFrom($mailConfig['from_email'] ?: 'no-reply@aitf.com', $mailConfig['from_name'] ?: 'AITF');
        $mailer->addAddress($to, htmlspecialchars($auditor['user_name']));
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $message;
        
        @$mailer->send();
    }
    
    ob_end_clean();
    echo json_encode(['status' => 'success', 'message' => 'Assigned successfully']);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

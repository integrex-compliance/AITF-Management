<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

include_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/testemail.php';

register_shutdown_function(function () {
    $err = error_get_last();
    if (!$err) {
        return;
    }
    $fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
    if (!in_array($err['type'], $fatalTypes, true)) {
        return;
    }
    if (headers_sent() === false) {
        header('Content-Type: application/json');
        http_response_code(500);
    }
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    echo json_encode(array(
        'success' => false,
        'message' => 'Server error: ' . ($err['message'] ?? 'Unknown error')
    ));
});

try {
    if (!function_exists('aitfGeneratePdfBinary')) {
        function aitfGeneratePdfBinary($scriptPath, $postData) {
            $originalPost = $_POST;
            $_POST = is_array($postData) ? $postData : [];
            $db = isset($GLOBALS['db']) ? $GLOBALS['db'] : null;

            $startLevel = ob_get_level();
            ob_start();
            $result = include $scriptPath;
            $buffer = '';
            if (ob_get_level() > $startLevel) {
                $buffer = ob_get_clean();
            }

            $_POST = $originalPost;

            if (is_string($result) && strlen($result) > 100) {
                return $result;
            }
            if (is_string($buffer) && strlen($buffer) > 100) {
                return $buffer;
            }
            return '';
        }
    }

    $customerId = isset($_POST['customer']) ? intval($_POST['customer']) : 0;
    $serviceId = isset($_POST['service']) ? intval($_POST['service']) : 0;
    $auditId = isset($_POST['audit']) ? intval($_POST['audit']) : 0;
    $stage = isset($_POST['stage']) ? trim($_POST['stage']) : '';

    if ($customerId <= 0 || $serviceId <= 0 || $auditId <= 0) {
        throw new Exception('Invalid request parameters.');
    }

    if ($stage !== 'stage1' && $stage !== 'stage2') {
        throw new Exception('Invalid stage.');
    }

    // Get customer info
    $customerStmt = $db->prepare("SELECT customer_name, email_id FROM customer_info WHERE id = ? LIMIT 1");
    $customerStmt->execute(array($customerId));
    $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer || empty($customer['email_id'])) {
        throw new Exception('Customer email not found.');
    }

    if (!filter_var($customer['email_id'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid customer email.');
    }

    $stageLabel = ($stage === 'stage2') ? 'Stage II' : 'Stage I';
    $auditDates = '';
    $attachments = array();

    if ($stage === 'stage1') {
        // Get Stage I notification
        $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_oneaudit_id WHERE audit_stage_id = ? AND ca_customer_id = ? AND ca_service_id = ? LIMIT 1");
        $notificationStmt->execute(array($auditId, $customerId, $serviceId));
        $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);

        if (!$notification) {
            throw new Exception('Stage I notification not found.');
        }

        $auditDates = isset($notification['audit_dates']) ? trim($notification['audit_dates']) : '';
        
        // Generate notification PDF
        $pdfBinary1 = aitfGeneratePdfBinary(__DIR__ . '/AuditNotificationandAssignment.php', array(
            'auditid' => $auditId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));
        
        if (is_string($pdfBinary1) && strlen($pdfBinary1) > 100) {
            $tempFile1 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'stage1_notif_' . uniqid() . '.pdf';
            if (file_put_contents($tempFile1, $pdfBinary1) !== false) {
                $attachments[] = $tempFile1;
            }
        }
        
        // Get audit schedule
        $scheduleStmt = $db->prepare("SELECT audit_schedule_id FROM audit_schedule_id WHERE customer_ca_id = ? AND serviceca_id = ? LIMIT 1");
        $scheduleStmt->execute(array($customerId, $serviceId));
        $scheduleData = $scheduleStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($scheduleData && !empty($scheduleData['audit_schedule_id'])) {
            $pdfBinary2 = aitfGeneratePdfBinary(__DIR__ . '/AuditSchedule.php', array(
                'auditscheduleid' => $scheduleData['audit_schedule_id'],
                'serviceid' => $serviceId,
                'customerid' => $customerId,
                'output_mode' => 'S'
            ));
            
            if (is_string($pdfBinary2) && strlen($pdfBinary2) > 100) {
                $tempFile2 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'stage1_plan_' . uniqid() . '.pdf';
                if (file_put_contents($tempFile2, $pdfBinary2) !== false) {
                    $attachments[] = $tempFile2;
                }
            }
        }
        
    } else {
        // Stage 2
        $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_twoaudit_id WHERE audit_stageii_id = ? AND customer_id = ? AND service_id = ? LIMIT 1");
        $notificationStmt->execute(array($auditId, $customerId, $serviceId));
        $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);

        if (!$notification) {
            throw new Exception('Stage II notification not found.');
        }

        $auditDates = isset($notification['audit_dates']) ? trim($notification['audit_dates']) : '';
        
        // Generate Stage 2 notification PDF
        $pdfBinary1 = aitfGeneratePdfBinary(__DIR__ . '/AuditNotificationStage2.php', array(
            'notificationid' => $auditId,
            'output_mode' => 'S'
        ));
        
        if (is_string($pdfBinary1) && strlen($pdfBinary1) > 100) {
            $tempFile1 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'stage2_notif_' . uniqid() . '.pdf';
            if (file_put_contents($tempFile1, $pdfBinary1) !== false) {
                $attachments[] = $tempFile1;
            }
        }
        
        // Get Stage 2 audit schedule
        $scheduleStmt = $db->prepare("SELECT audit_scheduleii_id FROM audit_scheduleii_id WHERE ca_customer = ? AND ca_service = ? LIMIT 1");
        $scheduleStmt->execute(array($customerId, $serviceId));
        $scheduleData = $scheduleStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($scheduleData && !empty($scheduleData['audit_scheduleii_id'])) {
            $pdfBinary2 = aitfGeneratePdfBinary(__DIR__ . '/SurveillanceAudit2.php', array(
                'auditnotiid' => $scheduleData['audit_scheduleii_id'],
                'serviceid' => $serviceId,
                'customerid' => $customerId,
                'output_mode' => 'S'
            ));
            
            if (is_string($pdfBinary2) && strlen($pdfBinary2) > 100) {
                $tempFile2 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'stage2_plan_' . uniqid() . '.pdf';
                if (file_put_contents($tempFile2, $pdfBinary2) !== false) {
                    $attachments[] = $tempFile2;
                }
            }
        }
    }

    if (count($attachments) === 0) {
        throw new Exception('No PDF documents could be generated.');
    }

    // Create email body
    $subject = $stageLabel . ' Audit Documents - ' . $customer['customer_name'];
    
    $body = '<html><body style="font-family: Arial, sans-serif; margin: 0; padding: 0;">';
    $body .= '<div style="max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px;">';
    $body .= '<div style="background: #fff; border-radius: 8px; overflow: hidden;">';
    $body .= '<div style="background: linear-gradient(135deg, #2196F3, #1976D2); padding: 30px; text-align: center;">';
    $body .= '<h1 style="color: #fff; margin: 0; font-size: 24px;">AITF Certification Services</h1>';
    $body .= '<p style="color: #e3f2fd; margin: 8px 0 0 0;">' . htmlspecialchars($stageLabel) . ' Audit Documents</p>';
    $body .= '</div>';
    $body .= '<div style="padding: 30px;">';
    $body .= '<p style="font-size: 16px; color: #333; margin: 0 0 20px 0;">Dear <strong>' . htmlspecialchars($customer['customer_name']) . '</strong>,</p>';
    $body .= '<p style="color: #555; line-height: 1.6;">We are pleased to share the ' . htmlspecialchars($stageLabel) . ' audit documents with you. Please find the attached PDF files.</p>';
    
    if (!empty($auditDates)) {
        $body .= '<div style="background: #f0f0f0; padding: 15px; border-left: 4px solid #2196F3; margin: 20px 0;">';
        $body .= '<strong>Audit Date(s):</strong> ' . htmlspecialchars($auditDates);
        $body .= '</div>';
    }
    
    $body .= '<p style="color: #555; line-height: 1.6; margin: 20px 0;">Please review the attached documents carefully. Should you have any questions, feel free to contact us.</p>';
    $body .= '<p style="color: #555;">Best regards,<br><strong>AITF Certification Team</strong></p>';
    $body .= '</div>';
    $body .= '<div style="background: #f5f5f5; padding: 20px; text-align: center; border-top: 1px solid #e0e0e0;">';
    $body .= '<p style="margin: 0; font-size: 12px; color: #999;">Email: <a href="mailto:certification@aitf.co.in" style="color: #2196F3; text-decoration: none;">certification@aitf.co.in</a> | Phone: +91-9422011836</p>';
    $body .= '</div></div></div></body></html>';

    // Send email
    $mailSender = new AITFServiceEmailSender();
    
    try {
        $mailSender->sendMultipleAttachmentsEmail(
            $customer['email_id'], 
            $customer['customer_name'], 
            $body, 
            $subject, 
            $attachments
        );
        
        $count = count($attachments);
        $message = $stageLabel . ' audit documents (' . $count . ' PDF' . ($count > 1 ? 's' : '') . ') sent successfully to ' . $customer['email_id'];
        
        echo json_encode(array('success' => true, 'message' => $message));
        
    } catch (Exception $mailEx) {
        throw new Exception('Email sending failed: ' . $mailEx->getMessage());
    } finally {
        // Clean up temp files
        foreach ($attachments as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }
    
} catch (Exception $ex) {
    echo json_encode(array('success' => false, 'message' => $ex->getMessage()));
}

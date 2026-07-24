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
        function aitfGeneratePdfBinary($scriptPath, $postData)
        {
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

        // Generate ONLY Stage 1 notification PDF
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

    } else {
        // Stage 2
        $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_twoaudit_id WHERE audit_stageii_id = ? AND customer_id = ? AND service_id = ? LIMIT 1");
        $notificationStmt->execute(array($auditId, $customerId, $serviceId));
        $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);

        if (!$notification) {
            throw new Exception('Stage II notification not found.');
        }

        $auditDates = isset($notification['audit_dates']) ? trim($notification['audit_dates']) : '';

        // Generate ONLY Stage 2 notification PDF
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
    }

    if (count($attachments) === 0) {
        throw new Exception('No notification PDF document could be generated.');
    }

    // Create email body
    $attachmentName = $stageLabel . ' Audit Notification';
    $subject = $attachmentName . ' - ' . $customer['customer_name'];

    $body = '<html><body style="font-family: Arial, sans-serif; font-size: 14px; color: #333333; line-height: 1.6; padding: 15px;">';
    $body .= 'Dear <strong>' . htmlspecialchars($customer['customer_name']) . '</strong>,<br><br>';
    $body .= 'We would like to inform you that your <strong>ISO certification is currently under pending action</strong> due to pending certification requirements.<br><br>';
    $body .= 'Please find the attached <strong>' . htmlspecialchars($attachmentName) . '</strong> for complete details, including the reason and the actions needed.<br><br>';

    if (!empty($auditDates)) {
        $body .= '<strong>Audit Date(s):</strong> ' . htmlspecialchars($auditDates) . '<br><br>';
    }

    $body .= 'Kindly review the letter and share your <strong>corrective action plan with supporting documents</strong> within the mentioned timeline. Once we receive and verify the required information, we will move ahead accordingly.<br><br>';
    $body .= 'If you have already completed the necessary actions, please ignore this email and send us the relevant documents for our review.<br><br>';
    $body .= 'Sincerely,<br><strong>AITF Certification</strong><br><br>';
    $body .= '<strong>Attachment:</strong> ' . htmlspecialchars($attachmentName);
    $body .= '</body></html>';

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
        $message = $stageLabel . ' audit notification sent successfully to ' . $customer['email_id'];

        // Record entry in ca_service_dates for sentintimation.php
        try {
            $actionName = ($stage === 'stage2') ? 'Stage 2 Notification' : 'Stage 1 Notification';

            $mainServiceId = 0;
            $stmtCa = $db->prepare("SELECT service FROM service_type_ca WHERE ca_id = ? LIMIT 1");
            $stmtCa->execute(array($serviceId));
            $caRow = $stmtCa->fetch(PDO::FETCH_ASSOC);
            if ($caRow && !empty($caRow['service'])) {
                $mainServiceId = intval($caRow['service']);
            } else {
                $stmtSvc = $db->prepare("SELECT service_id FROM service WHERE service_id = ? LIMIT 1");
                $stmtSvc->execute(array($serviceId));
                $svcRow = $stmtSvc->fetch(PDO::FETCH_ASSOC);
                if ($svcRow && !empty($svcRow['service_id'])) {
                    $mainServiceId = intval($svcRow['service_id']);
                } else {
                    $stmtSvcMax = $db->prepare("SELECT max(service_id) as max_svc FROM service WHERE customer_id = ?");
                    $stmtSvcMax->execute(array($customerId));
                    $maxRow = $stmtSvcMax->fetch(PDO::FETCH_ASSOC);
                    if ($maxRow && !empty($maxRow['max_svc'])) {
                        $mainServiceId = intval($maxRow['max_svc']);
                    }
                }
            }

            if ($mainServiceId > 0) {
                $nowDate = date('Y-m-d H:i:s');
                $checkDateStmt = $db->prepare("SELECT action_id FROM ca_service_dates WHERE service_id = ? AND action = ? AND is_deleted = 'N' LIMIT 1");
                $checkDateStmt->execute(array($mainServiceId, $actionName));
                $existingDate = $checkDateStmt->fetch(PDO::FETCH_ASSOC);

                if ($existingDate) {
                    $updateDateStmt = $db->prepare("UPDATE ca_service_dates SET sent_date = ?, status = 'sent', due_date = COALESCE(due_date, ?) WHERE action_id = ?");
                    $updateDateStmt->execute(array($nowDate, $nowDate, $existingDate['action_id']));
                } else {
                    $insertDateStmt = $db->prepare("INSERT INTO ca_service_dates (service_id, action, due_date, sent_date, status, is_deleted) VALUES (?, ?, ?, ?, 'sent', 'N')");
                    $insertDateStmt->execute(array($mainServiceId, $actionName, $nowDate, $nowDate));
                }
            }
        } catch (Exception $dbEx) {
            error_log("sendStageNotificationMail: Failed to record ca_service_dates entry: " . $dbEx->getMessage());
        }

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

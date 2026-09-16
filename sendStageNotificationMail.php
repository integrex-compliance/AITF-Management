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
    $customEmail = isset($_POST['email']) ? trim($_POST['email']) : '';

    if ($customerId <= 0 || $serviceId <= 0 || $auditId <= 0) {
        throw new Exception('Invalid request parameters.');
    }

    if ($stage !== 'stage1' && $stage !== 'stage2') {
        throw new Exception('Invalid stage specified.');
    }

    // Get customer info
    $customerStmt = $db->prepare("SELECT id, customer_name, email_id, unique_id FROM customer_info WHERE id = ? LIMIT 1");
    $customerStmt->execute(array($customerId));
    $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        throw new Exception('Customer record not found.');
    }

    $recipientEmail = !empty($customEmail) ? $customEmail : trim($customer['email_id'] ?? '');

    if (empty($recipientEmail)) {
        throw new Exception('Customer email address is not specified.');
    }

    // Validate email addresses
    $emailList = preg_split('/[,;]+/', $recipientEmail);
    $validEmails = [];
    foreach ($emailList as $eml) {
        $eml = trim($eml);
        if ($eml !== '' && filter_var($eml, FILTER_VALIDATE_EMAIL)) {
            $validEmails[] = $eml;
        }
    }
    if (empty($validEmails)) {
        throw new Exception('Please enter a valid recipient email address.');
    }
    $finalRecipientEmail = implode(', ', $validEmails);

    $stageLabel = ($stage === 'stage2') ? 'Stage II' : 'Stage I';
    $auditDates = '';
    $attachments = array();
    $cleanClientName = preg_replace('/[^A-Za-z0-9_-]+/', '_', trim($customer['customer_name']));

    if ($stage === 'stage1') {
        // 1. Get Stage I notification record
        $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_oneaudit_id WHERE audit_stage_id = ? AND ca_customer_id = ? AND ca_service_id = ? LIMIT 1");
        $notificationStmt->execute(array($auditId, $customerId, $serviceId));
        $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);

        if (!$notification) {
            // Fallback by audit_stage_id
            $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_oneaudit_id WHERE audit_stage_id = ? LIMIT 1");
            $notificationStmt->execute(array($auditId));
            $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$notification) {
            throw new Exception('Stage I notification not found.');
        }

        $auditDates = isset($notification['audit_dates']) ? trim($notification['audit_dates']) : '';

        // 2. Generate Stage 1 Notification PDF
        $pdfBinary1 = aitfGeneratePdfBinary(__DIR__ . '/AuditNotificationandAssignment.php', array(
            'auditid' => $auditId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));

        if (is_string($pdfBinary1) && strlen($pdfBinary1) > 100) {
            $tempFile1 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Stage_I_Audit_Notification_' . $cleanClientName . '_' . uniqid() . '.pdf';
            if (file_put_contents($tempFile1, $pdfBinary1) !== false) {
                $attachments[] = $tempFile1;
            }
        }

        // 3. Find and Generate Stage 1 Audit Plan PDF
        $scheduleId = 0;
        $schedStmt = $db->prepare("SELECT audit_schedule_id FROM audit_schedule_id WHERE serviceca_id = ? AND customer_ca_id = ? ORDER BY audit_schedule_id DESC LIMIT 1");
        $schedStmt->execute(array($serviceId, $customerId));
        $schedRow = $schedStmt->fetch(PDO::FETCH_ASSOC);
        if ($schedRow && !empty($schedRow['audit_schedule_id'])) {
            $scheduleId = intval($schedRow['audit_schedule_id']);
        } else {
            $schedStmt2 = $db->prepare("SELECT audit_schedule_id FROM audit_schedule_id WHERE serviceca_id = ? ORDER BY audit_schedule_id DESC LIMIT 1");
            $schedStmt2->execute(array($serviceId));
            $schedRow2 = $schedStmt2->fetch(PDO::FETCH_ASSOC);
            if ($schedRow2 && !empty($schedRow2['audit_schedule_id'])) {
                $scheduleId = intval($schedRow2['audit_schedule_id']);
            } else {
                $schedStmt3 = $db->prepare("SELECT audit_schedule_id FROM audit_schedule_id WHERE customer_ca_id = ? ORDER BY audit_schedule_id DESC LIMIT 1");
                $schedStmt3->execute(array($customerId));
                $schedRow3 = $schedStmt3->fetch(PDO::FETCH_ASSOC);
                if ($schedRow3 && !empty($schedRow3['audit_schedule_id'])) {
                    $scheduleId = intval($schedRow3['audit_schedule_id']);
                }
            }
        }

        if ($scheduleId <= 0) {
            throw new Exception("Cannot send only notification! Stage 1 Audit Plan is not generated yet. Please make/generate the audit plan first.");
        }

        $pdfBinaryPlan = aitfGeneratePdfBinary(__DIR__ . '/AuditSchedule.php', array(
            'auditscheduleid' => $scheduleId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));

        if (!is_string($pdfBinaryPlan) || strlen($pdfBinaryPlan) <= 100) {
            throw new Exception("Unable to generate Stage 1 Audit Plan PDF. Please ensure the audit plan is properly configured.");
        }

        $tempFilePlan = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Stage_I_Audit_Plan_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempFilePlan, $pdfBinaryPlan) === false) {
            throw new Exception("Failed to create Stage 1 Audit Plan PDF file.");
        }
        $attachments[] = $tempFilePlan;

    } else {
        // Stage 2
        // 1. Get Stage II notification record
        $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_twoaudit_id WHERE audit_stageii_id = ? AND customer_id = ? AND service_id = ? LIMIT 1");
        $notificationStmt->execute(array($auditId, $customerId, $serviceId));
        $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);

        if (!$notification) {
            $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_twoaudit_id WHERE audit_stageii_id = ? LIMIT 1");
            $notificationStmt->execute(array($auditId));
            $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$notification) {
            throw new Exception('Stage II notification not found.');
        }

        $auditDates = isset($notification['audit_dates']) ? trim($notification['audit_dates']) : '';

        // 2. Generate Stage 2 Notification PDF
        $pdfBinary1 = aitfGeneratePdfBinary(__DIR__ . '/AuditNotificationStage2.php', array(
            'notificationid' => $auditId,
            'output_mode' => 'S'
        ));

        if (is_string($pdfBinary1) && strlen($pdfBinary1) > 100) {
            $tempFile1 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Stage_II_Audit_Notification_' . $cleanClientName . '_' . uniqid() . '.pdf';
            if (file_put_contents($tempFile1, $pdfBinary1) !== false) {
                $attachments[] = $tempFile1;
            }
        }

        // 3. Find and Generate Stage 2 Audit Plan PDF
        $schedule2Id = 0;
        $sched2Stmt = $db->prepare("SELECT audit_scheduleii_id FROM audit_scheduleii_id WHERE ca_service = ? AND ca_customer = ? ORDER BY audit_scheduleii_id DESC LIMIT 1");
        $sched2Stmt->execute(array($serviceId, $customerId));
        $sched2Row = $sched2Stmt->fetch(PDO::FETCH_ASSOC);
        if ($sched2Row && !empty($sched2Row['audit_scheduleii_id'])) {
            $schedule2Id = intval($sched2Row['audit_scheduleii_id']);
        } else {
            $sched2Stmt2 = $db->prepare("SELECT audit_scheduleii_id FROM audit_scheduleii_id WHERE ca_service = ? ORDER BY audit_scheduleii_id DESC LIMIT 1");
            $sched2Stmt2->execute(array($serviceId));
            $sched2Row2 = $sched2Stmt2->fetch(PDO::FETCH_ASSOC);
            if ($sched2Row2 && !empty($sched2Row2['audit_scheduleii_id'])) {
                $schedule2Id = intval($sched2Row2['audit_scheduleii_id']);
            } else {
                $sched2Stmt3 = $db->prepare("SELECT audit_scheduleii_id FROM audit_scheduleii_id WHERE ca_customer = ? ORDER BY audit_scheduleii_id DESC LIMIT 1");
                $sched2Stmt3->execute(array($customerId));
                $sched2Row3 = $sched2Stmt3->fetch(PDO::FETCH_ASSOC);
                if ($sched2Row3 && !empty($sched2Row3['audit_scheduleii_id'])) {
                    $schedule2Id = intval($sched2Row3['audit_scheduleii_id']);
                }
            }
        }

        if ($schedule2Id <= 0) {
            throw new Exception("Cannot send only notification! Stage 2 Audit Plan is not generated yet. Please make/generate the audit plan first.");
        }

        $pdfBinaryPlan2 = aitfGeneratePdfBinary(__DIR__ . '/SurveillanceAudit2.php', array(
            'auditnotiid' => $schedule2Id,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));

        if (!is_string($pdfBinaryPlan2) || strlen($pdfBinaryPlan2) <= 100) {
            throw new Exception("Unable to generate Stage 2 Audit Plan PDF. Please ensure the audit plan is properly configured.");
        }

        $tempFilePlan2 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Stage_II_Audit_Plan_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempFilePlan2, $pdfBinaryPlan2) === false) {
            throw new Exception("Failed to create Stage 2 Audit Plan PDF file.");
        }
        $attachments[] = $tempFilePlan2;
    }

    if (count($attachments) < 2) {
        throw new Exception('Cannot send email! Both Audit Notification and Audit Plan PDFs must be generated together.');
    }


    // 4. Create Email Body for Client
    $subject = $stageLabel . ' Audit Notification and Audit Plan - ' . $customer['customer_name'];

    $body = '<html><body style="font-family: Arial, sans-serif; font-size: 14px; color: #333333; line-height: 1.6; padding: 15px;">';
    $body .= 'Dear <strong>' . htmlspecialchars($customer['customer_name']) . '</strong> Team,<br><br>';
    $body .= 'Greetings from <strong>A.I.T.F. Management Certification Limited</strong>.<br><br>';
    $body .= 'Please find attached the <strong>' . htmlspecialchars($stageLabel) . ' Audit Notification</strong> and <strong>Audit Plan</strong> for the ISO Certification audit of your organization.<br><br>';

    if (!empty($auditDates)) {
        $body .= '<div style="background:#f1f5f9; border-left:4px solid #2563eb; padding:8px 12px; margin-bottom:12px;">';
        $body .= '<strong>Scheduled Audit Date(s):</strong> <span style="color:#0f172a; font-weight:600;">' . htmlspecialchars($auditDates) . '</span>';
        $body .= '</div>';
    }

    $body .= 'You are requested to review the attached notification and audit plan carefully. Please ensure all relevant processes, personnel, and records are available as per the scheduled audit plan.<br><br>';
    $body .= '<div style="margin-top: 20px; font-size: 13.5px; color: #475569;">';
    $body .= '<strong>Attachments:</strong><br>';
    $body .= '1. ' . htmlspecialchars($stageLabel) . ' Audit Notification<br>';
    if (count($attachments) > 1) {
        $body .= '2. ' . htmlspecialchars($stageLabel) . ' Audit Plan<br>';
    }
    $body .= '</div>';

    // Signoff
    $body .= '<div style="margin-top: 28px; border-top: 1px solid #e2e8f0; padding-top: 18px;">';
    $body .= '<p style="margin: 0 0 6px; color: #475569;">Regards,</p>';
    $body .= '<p style="margin: 0; line-height: 1.45;">';
    $body .= '<strong>AITF Management Certification Limited.</strong><br>';
    $body .= '<strong>QCI-NABCB Accredited Certification Body.</strong><br><br>';
    $body .= '<strong>Authorised Signatory</strong>';
    $body .= '</p>';
    $body .= '</div>';

    // Footer
    $body .= '<div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 20px; font-size: 12px; color: #64748b; line-height: 1.5; margin-top: 24px; border-radius: 4px;">';
    $body .= '<strong>AITF Management Certification Limited</strong><br>';
    $body .= 'Devi Indrayani, Wing B-26, Near IT Park, Talawade, Pune &ndash; 411062, Maharashtra, India<br>';
    $body .= 'Email: <a href="mailto:certification@aitf.co.in" style="color: #2563eb; text-decoration: none;">certification@aitf.co.in</a> | Website: <a href="https://www.aitf.co.in" target="_blank" style="color: #2563eb; text-decoration: none;">www.aitf.co.in</a>';
    $body .= '</div>';
    $body .= '</body></html>';


    // 5. Send email to client with CC: aitfmcl@gmail.com
    $mailSender = new AITFServiceEmailSender();
    $ccEmail = 'aitfmcl@gmail.com';

    try {
        $mailSender->sendMultipleAttachmentsEmail(
            $validEmails,
            $customer['customer_name'],
            $body,
            $subject,
            $attachments,
            $ccEmail
        );

        $nowDate = date('Y-m-d H:i:s');
        $sentBy = $_SESSION['username'] ?? 'Staff';

        // 6. Send confirmation email to certification@aitf.co.in with attachments
        try {
            $confirmSubject = 'Confirmation: ' . $stageLabel . ' Audit Notification & Audit Plan Sent - ' . $customer['customer_name'];
            $confirmBody = '<html><body style="font-family: Arial, sans-serif; font-size: 14px; color: #333333; line-height: 1.6; padding: 15px;">';
            $confirmBody .= 'Dear AITF Certification Team,<br><br>';
            $confirmBody .= 'This is an automated confirmation that the <strong>' . htmlspecialchars($stageLabel) . ' Audit Notification and Audit Plan</strong> have been successfully emailed to the client.<br><br>';
            $confirmBody .= '<div style="background:#f8fafc; border:1px solid #cbd5e1; border-left:4px solid #10b981; padding:12px 16px; border-radius:4px; margin-bottom:15px;">';
            $confirmBody .= '<p style="margin:4px 0;"><strong>Client Name:</strong> <span style="color:#0f172a; font-weight:600;">' . htmlspecialchars($customer['customer_name']) . '</span></p>';
            $confirmBody .= '<p style="margin:4px 0;"><strong>Recipient Email:</strong> <span style="color:#2563eb; font-weight:600;">' . htmlspecialchars($finalRecipientEmail) . '</span></p>';
            $confirmBody .= '<p style="margin:4px 0;"><strong>CC:</strong> ' . htmlspecialchars($ccEmail) . '</p>';
            if (!empty($customer['unique_id'])) {
                $confirmBody .= '<p style="margin:4px 0;"><strong>Agreement / Unique ID:</strong> ' . htmlspecialchars($customer['unique_id']) . '</p>';
            }
            if (!empty($auditDates)) {
                $confirmBody .= '<p style="margin:4px 0;"><strong>Scheduled Audit Date(s):</strong> ' . htmlspecialchars($auditDates) . '</p>';
            }
            $confirmBody .= '<p style="margin:4px 0;"><strong>Dispatched Date & Time:</strong> ' . date('d.m.Y h:i A', strtotime($nowDate)) . '</p>';
            $confirmBody .= '<p style="margin:4px 0;"><strong>Dispatched By:</strong> ' . htmlspecialchars($sentBy) . '</p>';
            $confirmBody .= '</div>';
            $confirmBody .= 'Copies of the sent ' . htmlspecialchars($stageLabel) . ' Notification and Audit Plan are attached to this email for your records.<br><br>';
            $confirmBody .= 'Sincerely,<br><strong>AITF System</strong>';
            $confirmBody .= '</body></html>';

            $mailSender->sendMultipleAttachmentsEmail(
                'certification@aitf.co.in',
                'AITF Certification',
                $confirmBody,
                $confirmSubject,
                $attachments
            );
        } catch (Exception $confirmEx) {
            error_log("sendStageNotificationMail: Failed to send confirmation email to certification@aitf.co.in: " . $confirmEx->getMessage());
        }

        // 7. Record entry in ca_service_dates for sent tracking
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

        $message = $stageLabel . ' Notification and Audit Plan successfully sent to ' . $finalRecipientEmail . ' (CC: ' . $ccEmail . ').';
        echo json_encode(array(
            'success' => true,
            'message' => $message,
            'recipient' => $finalRecipientEmail,
            'sent_at' => date('d.m.Y h:i A', strtotime($nowDate))
        ));

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

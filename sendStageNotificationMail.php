<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
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
    $universalServiceId = isset($_POST['universalservice']) ? intval($_POST['universalservice']) : 0;
    $auditId = isset($_POST['audit']) ? intval($_POST['audit']) : 0;
    $planIdFromReq = isset($_POST['planid']) ? intval($_POST['planid']) : 0;
    $stage = isset($_POST['stage']) ? strtolower(trim($_POST['stage'])) : '';
    $customEmail = isset($_POST['email']) ? trim($_POST['email']) : '';

    // Normalize stage aliases
    if ($stage === 'sa1' || $stage === 'sa-1' || $stage === 'sa_1') {
        $stage = 'sai';
    } else if ($stage === 'sa2' || $stage === 'sa-2' || $stage === 'sa_2') {
        $stage = 'saii';
    }

    if ($customerId <= 0 || $serviceId <= 0 || $auditId <= 0) {
        throw new Exception('Invalid request parameters. Customer, Service and Notification IDs are required.');
    }

    $validStages = array('stage1', 'stage2', 'sai', 'saii', 'rc');
    if (!in_array($stage, $validStages, true)) {
        throw new Exception('Invalid stage specified: ' . htmlspecialchars($stage));
    }

    // Determine Stage Label and ca_service_dates action name
    switch ($stage) {
        case 'stage2':
            $stageLabel = 'Stage II';
            $actionName = 'Stage 2 Notification';
            break;
        case 'sai':
            $stageLabel = 'Surveillance Audit I (SA-I)';
            $actionName = 'SA-I Notification';
            break;
        case 'saii':
            $stageLabel = 'Surveillance Audit II (SA-II)';
            $actionName = 'SA-II Notification';
            break;
        case 'rc':
            $stageLabel = 'Recertification (RC)';
            $actionName = 'RC Notification';
            break;
        case 'stage1':
        default:
            $stageLabel = 'Stage I';
            $actionName = 'Stage 1 Notification';
            break;
    }

    // Resolve Universal Service ID (service.service_id)
    $mainServiceId = $universalServiceId;
    if ($mainServiceId <= 0) {
        if ($stage === 'stage1' || $stage === 'stage2') {
            $stmtCa = $db->prepare("SELECT service FROM service_type_ca WHERE ca_id = ? LIMIT 1");
            $stmtCa->execute(array($serviceId));
            $caRow = $stmtCa->fetch(PDO::FETCH_ASSOC);
            if ($caRow && !empty($caRow['service'])) {
                $mainServiceId = intval($caRow['service']);
            }
        } else if ($stage === 'sai') {
            $stmtSai = $db->prepare("SELECT service FROM service_type_sai WHERE sai_id = ? LIMIT 1");
            $stmtSai->execute(array($serviceId));
            $saiRow = $stmtSai->fetch(PDO::FETCH_ASSOC);
            if ($saiRow && !empty($saiRow['service'])) {
                $mainServiceId = intval($saiRow['service']);
            }
        } else if ($stage === 'saii') {
            $stmtSaii = $db->prepare("SELECT service FROM service_type_saii WHERE saii_id = ? LIMIT 1");
            $stmtSaii->execute(array($serviceId));
            $saiiRow = $stmtSaii->fetch(PDO::FETCH_ASSOC);
            if ($saiiRow && !empty($saiiRow['service'])) {
                $mainServiceId = intval($saiiRow['service']);
            }
        } else if ($stage === 'rc') {
            $stmtRc = $db->prepare("SELECT service FROM service_type_rc WHERE rc_id = ? LIMIT 1");
            $stmtRc->execute(array($serviceId));
            $rcRow = $stmtRc->fetch(PDO::FETCH_ASSOC);
            if ($rcRow && !empty($rcRow['service'])) {
                $mainServiceId = intval($rcRow['service']);
            }
        }
    }

    if ($mainServiceId <= 0) {
        $stmtSvc = $db->prepare("SELECT service_id FROM service WHERE service_id = ? LIMIT 1");
        $stmtSvc->execute(array($serviceId));
        $svcRow = $stmtSvc->fetch(PDO::FETCH_ASSOC);
        if ($svcRow && !empty($svcRow['service_id'])) {
            $mainServiceId = intval($svcRow['service_id']);
        } else {
            $stmtSvcMax = $db->prepare("SELECT max(service_id) as max_svc FROM service WHERE customer_id = ? AND (is_deleted = 'NO' OR is_deleted IS NULL)");
            $stmtSvcMax->execute(array($customerId));
            $maxRow = $stmtSvcMax->fetch(PDO::FETCH_ASSOC);
            if ($maxRow && !empty($maxRow['max_svc'])) {
                $mainServiceId = intval($maxRow['max_svc']);
            }
        }
    }

    // Duplicate Check: If already sent, block re-sending
    if ($mainServiceId > 0) {
        $alreadyActions = array($actionName);
        if ($stage === 'sai') {
            $alreadyActions[] = 'SA-1 Notification';
        } else if ($stage === 'saii') {
            $alreadyActions[] = 'SA-2 Notification';
        }
        $inClause = implode(',', array_fill(0, count($alreadyActions), '?'));
        $checkSentParams = array_merge(array($mainServiceId), $alreadyActions);
        $checkSentStmt = $db->prepare("SELECT id as action_id, sent_date FROM audit_notification_sent_log WHERE service_id = ? AND action IN ($inClause) AND status = 'sent' AND (is_deleted = 'N' OR is_deleted IS NULL) LIMIT 1");
        $checkSentStmt->execute($checkSentParams);
        $alreadySent = $checkSentStmt->fetch(PDO::FETCH_ASSOC);
        if (!$alreadySent) {
            $checkSentStmt2 = $db->prepare("SELECT action_id, sent_date FROM ca_service_dates WHERE service_id = ? AND action IN ($inClause) AND status = 'sent' AND (is_deleted = 'N' OR is_deleted IS NULL) LIMIT 1");
            $checkSentStmt2->execute($checkSentParams);
            $alreadySent = $checkSentStmt2->fetch(PDO::FETCH_ASSOC);
        }

        if ($alreadySent) {
            $formattedSentDate = 'earlier';
            if (!empty($alreadySent['sent_date'])) {
                $ts = strtotime($alreadySent['sent_date']);
                if ($ts) {
                    $formattedSentDate = (date('H:i:s', $ts) === '00:00:00') ? date('d.m.Y', $ts) : date('d.m.Y h:i A', $ts);
                }
            }
            throw new Exception($stageLabel . " notification and audit plan have already been sent on " . $formattedSentDate . ". Duplicate sending is not allowed.");
        }
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

    $auditDates = '';
    $attachments = array();
    $cleanClientName = preg_replace('/[^A-Za-z0-9_-]+/', '_', trim($customer['customer_name']));

    // Branch by stage to generate Notification PDF and Audit Plan PDF
    if ($stage === 'stage1') {
        // 1. Notification
        $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_oneaudit_id WHERE audit_stage_id = ? LIMIT 1");
        $notificationStmt->execute(array($auditId));
        $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);
        if (!$notification) {
            throw new Exception('Stage I notification record not found.');
        }
        $auditDates = trim($notification['audit_dates'] ?? '');

        $pdfBinaryNoti = aitfGeneratePdfBinary(__DIR__ . '/AuditNotificationandAssignment.php', array(
            'auditid' => $auditId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));
        if (!is_string($pdfBinaryNoti) || strlen($pdfBinaryNoti) <= 100) {
            throw new Exception('Unable to generate Stage I Audit Notification PDF.');
        }
        $tempNoti = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Stage_I_Audit_Notification_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempNoti, $pdfBinaryNoti) === false) {
            throw new Exception('Failed to write Stage I Audit Notification PDF.');
        }
        $attachments[] = $tempNoti;

        // 2. Plan
        $scheduleId = $planIdFromReq;
        if ($scheduleId <= 0) {
            $schedStmt = $db->prepare("SELECT audit_schedule_id FROM audit_schedule_id WHERE serviceca_id = ? ORDER BY audit_schedule_id DESC LIMIT 1");
            $schedStmt->execute(array($serviceId));
            $schedRow = $schedStmt->fetch(PDO::FETCH_ASSOC);
            if ($schedRow && !empty($schedRow['audit_schedule_id'])) {
                $scheduleId = intval($schedRow['audit_schedule_id']);
            }
        }
        if ($scheduleId <= 0) {
            throw new Exception("Cannot send only notification! Make audit plan also. (Stage I Audit Plan is not generated yet)");
        }
        $pdfBinaryPlan = aitfGeneratePdfBinary(__DIR__ . '/AuditSchedule.php', array(
            'auditscheduleid' => $scheduleId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));
        if (!is_string($pdfBinaryPlan) || strlen($pdfBinaryPlan) <= 100) {
            throw new Exception("Unable to generate Stage I Audit Plan PDF. Please ensure the audit plan is properly configured.");
        }
        $tempPlan = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Stage_I_Audit_Plan_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempPlan, $pdfBinaryPlan) === false) {
            throw new Exception('Failed to write Stage I Audit Plan PDF.');
        }
        $attachments[] = $tempPlan;

    } else if ($stage === 'stage2') {
        // 1. Notification
        $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_twoaudit_id WHERE audit_stageii_id = ? LIMIT 1");
        $notificationStmt->execute(array($auditId));
        $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);
        if (!$notification) {
            throw new Exception('Stage II notification record not found.');
        }
        $auditDates = trim($notification['audit_dates'] ?? '');

        $pdfBinaryNoti = aitfGeneratePdfBinary(__DIR__ . '/AuditNotificationStage2.php', array(
            'notificationid' => $auditId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));
        if (!is_string($pdfBinaryNoti) || strlen($pdfBinaryNoti) <= 100) {
            throw new Exception('Unable to generate Stage II Audit Notification PDF.');
        }
        $tempNoti = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Stage_II_Audit_Notification_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempNoti, $pdfBinaryNoti) === false) {
            throw new Exception('Failed to write Stage II Audit Notification PDF.');
        }
        $attachments[] = $tempNoti;

        // 2. Plan
        $schedule2Id = $planIdFromReq;
        if ($schedule2Id <= 0) {
            $sched2Stmt = $db->prepare("SELECT audit_scheduleii_id FROM audit_scheduleii_id WHERE ca_service = ? ORDER BY audit_scheduleii_id DESC LIMIT 1");
            $sched2Stmt->execute(array($serviceId));
            $sched2Row = $sched2Stmt->fetch(PDO::FETCH_ASSOC);
            if ($sched2Row && !empty($sched2Row['audit_scheduleii_id'])) {
                $schedule2Id = intval($sched2Row['audit_scheduleii_id']);
            }
        }
        if ($schedule2Id <= 0) {
            throw new Exception("Cannot send only notification! Make audit plan also. (Stage II Audit Plan is not generated yet)");
        }
        $pdfBinaryPlan2 = aitfGeneratePdfBinary(__DIR__ . '/SurveillanceAudit2.php', array(
            'auditnotiid' => $schedule2Id,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));
        if (!is_string($pdfBinaryPlan2) || strlen($pdfBinaryPlan2) <= 100) {
            throw new Exception("Unable to generate Stage II Audit Plan PDF. Please ensure the audit plan is properly configured.");
        }
        $tempPlan2 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Stage_II_Audit_Plan_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempPlan2, $pdfBinaryPlan2) === false) {
            throw new Exception('Failed to write Stage II Audit Plan PDF.');
        }
        $attachments[] = $tempPlan2;

    } else if ($stage === 'sai') {
        // SA-I Notification (from stage_oneaudit_id, rendered by AuditNotificationandAssignment.php)
        $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_oneaudit_id WHERE audit_stage_id = ? LIMIT 1");
        $notificationStmt->execute(array($auditId));
        $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);
        if (!$notification) {
            throw new Exception('SA-I notification record not found.');
        }
        $auditDates = trim($notification['audit_dates'] ?? '');

        $pdfBinaryNoti = aitfGeneratePdfBinary(__DIR__ . '/AuditNotificationandAssignment.php', array(
            'auditid' => $auditId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));
        if (!is_string($pdfBinaryNoti) || strlen($pdfBinaryNoti) <= 100) {
            throw new Exception('Unable to generate SA-I Audit Notification PDF.');
        }
        $tempNoti = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'SA_I_Audit_Notification_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempNoti, $pdfBinaryNoti) === false) {
            throw new Exception('Failed to write SA-I Audit Notification PDF.');
        }
        $attachments[] = $tempNoti;

        // SA-I Audit Plan (from servelance_1_id, rendered by Servelance1.php)
        $planId = $planIdFromReq;
        if ($planId <= 0) {
            $pStmt = $db->prepare("SELECT servelance_id FROM servelance_1_id WHERE service_id = ? ORDER BY servelance_id DESC LIMIT 1");
            $pStmt->execute(array($serviceId));
            $pRow = $pStmt->fetch(PDO::FETCH_ASSOC);
            if ($pRow && !empty($pRow['servelance_id'])) {
                $planId = intval($pRow['servelance_id']);
            }
        }
        if ($planId <= 0) {
            throw new Exception("Cannot send only notification! Make audit plan also. (SA-I Audit Plan is not generated yet)");
        }
        $pdfBinaryPlan = aitfGeneratePdfBinary(__DIR__ . '/Servelance1.php', array(
            'auditnotiid' => $planId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));
        if (!is_string($pdfBinaryPlan) || strlen($pdfBinaryPlan) <= 100) {
            throw new Exception("Unable to generate SA-I Audit Plan PDF. Please ensure the audit plan is properly configured.");
        }
        $tempPlan = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'SA_I_Audit_Plan_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempPlan, $pdfBinaryPlan) === false) {
            throw new Exception('Failed to write SA-I Audit Plan PDF.');
        }
        $attachments[] = $tempPlan;

    } else if ($stage === 'saii') {
        // SA-II Notification (from stage_oneaudit_id, rendered by AuditNotificationandAssignment.php)
        $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_oneaudit_id WHERE audit_stage_id = ? LIMIT 1");
        $notificationStmt->execute(array($auditId));
        $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);
        if (!$notification) {
            throw new Exception('SA-II notification record not found.');
        }
        $auditDates = trim($notification['audit_dates'] ?? '');

        $pdfBinaryNoti = aitfGeneratePdfBinary(__DIR__ . '/AuditNotificationandAssignment.php', array(
            'auditid' => $auditId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));
        if (!is_string($pdfBinaryNoti) || strlen($pdfBinaryNoti) <= 100) {
            throw new Exception('Unable to generate SA-II Audit Notification PDF.');
        }
        $tempNoti = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'SA_II_Audit_Notification_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempNoti, $pdfBinaryNoti) === false) {
            throw new Exception('Failed to write SA-II Audit Notification PDF.');
        }
        $attachments[] = $tempNoti;

        // SA-II Audit Plan (from servelance_1_id, rendered by Servelance2.php)
        $planId = $planIdFromReq;
        if ($planId <= 0) {
            $pStmt = $db->prepare("SELECT servelance_id FROM servelance_1_id WHERE service_id = ? ORDER BY servelance_id DESC LIMIT 1");
            $pStmt->execute(array($serviceId));
            $pRow = $pStmt->fetch(PDO::FETCH_ASSOC);
            if ($pRow && !empty($pRow['servelance_id'])) {
                $planId = intval($pRow['servelance_id']);
            }
        }
        if ($planId <= 0) {
            throw new Exception("Cannot send only notification! Make audit plan also. (SA-II Audit Plan is not generated yet)");
        }
        $pdfBinaryPlan = aitfGeneratePdfBinary(__DIR__ . '/Servelance2.php', array(
            'auditnotiid' => $planId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));
        if (!is_string($pdfBinaryPlan) || strlen($pdfBinaryPlan) <= 100) {
            throw new Exception("Unable to generate SA-II Audit Plan PDF. Please ensure the audit plan is properly configured.");
        }
        $tempPlan = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'SA_II_Audit_Plan_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempPlan, $pdfBinaryPlan) === false) {
            throw new Exception('Failed to write SA-II Audit Plan PDF.');
        }
        $attachments[] = $tempPlan;

    } else if ($stage === 'rc') {
        // RC Notification (from stage_oneaudit_id, rendered by AuditNotificationandAssignment.php)
        $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_oneaudit_id WHERE audit_stage_id = ? LIMIT 1");
        $notificationStmt->execute(array($auditId));
        $notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);
        if (!$notification) {
            throw new Exception('RC notification record not found.');
        }
        $auditDates = trim($notification['audit_dates'] ?? '');

        $pdfBinaryNoti = aitfGeneratePdfBinary(__DIR__ . '/AuditNotificationandAssignment.php', array(
            'auditid' => $auditId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));
        if (!is_string($pdfBinaryNoti) || strlen($pdfBinaryNoti) <= 100) {
            throw new Exception('Unable to generate RC Audit Notification PDF.');
        }
        $tempNoti = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'RC_Audit_Notification_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempNoti, $pdfBinaryNoti) === false) {
            throw new Exception('Failed to write RC Audit Notification PDF.');
        }
        $attachments[] = $tempNoti;

        // RC Audit Plan (from audit_scheduleii_id, rendered by SurveillanceAudit2.php)
        $scheduleId = $planIdFromReq;
        if ($scheduleId <= 0) {
            $pStmt = $db->prepare("SELECT audit_scheduleii_id FROM audit_scheduleii_id WHERE ca_service = ? ORDER BY audit_scheduleii_id DESC LIMIT 1");
            $pStmt->execute(array($serviceId));
            $pRow = $pStmt->fetch(PDO::FETCH_ASSOC);
            if ($pRow && !empty($pRow['audit_scheduleii_id'])) {
                $scheduleId = intval($pRow['audit_scheduleii_id']);
            }
        }
        if ($scheduleId <= 0) {
            throw new Exception("Cannot send only notification! Make audit plan also. (RC Audit Plan is not generated yet)");
        }
        $pdfBinaryPlan = aitfGeneratePdfBinary(__DIR__ . '/SurveillanceAudit2.php', array(
            'auditnotiid' => $scheduleId,
            'customerid' => $customerId,
            'serviceid' => $serviceId,
            'output_mode' => 'S'
        ));
        if (!is_string($pdfBinaryPlan) || strlen($pdfBinaryPlan) <= 100) {
            throw new Exception("Unable to generate RC Audit Plan PDF. Please ensure the audit plan is properly configured.");
        }
        $tempPlan = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'RC_Audit_Plan_' . $cleanClientName . '_' . uniqid() . '.pdf';
        if (file_put_contents($tempPlan, $pdfBinaryPlan) === false) {
            throw new Exception('Failed to write RC Audit Plan PDF.');
        }
        $attachments[] = $tempPlan;
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
    $body .= '2. ' . htmlspecialchars($stageLabel) . ' Audit Plan<br>';
    $body .= '</div>';

    // Standardized Signoff
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

        // 7. Record entry in audit_notification_sent_log for sent tracking
        try {
            if ($mainServiceId > 0) {
                $checkDateStmt = $db->prepare("SELECT id FROM audit_notification_sent_log WHERE service_id = ? AND action = ? AND is_deleted = 'N' LIMIT 1");
                $checkDateStmt->execute(array($mainServiceId, $actionName));
                $existingDate = $checkDateStmt->fetch(PDO::FETCH_ASSOC);

                if ($existingDate) {
                    $updateDateStmt = $db->prepare("UPDATE audit_notification_sent_log SET sent_date = ?, status = 'sent', recipient_email = ?, sent_by = ?, customer_id = ?, stage = ? WHERE id = ?");
                    $updateDateStmt->execute(array($nowDate, $finalRecipientEmail, $sentBy, $customerId, $stage, $existingDate['id']));
                } else {
                    $insertDateStmt = $db->prepare("INSERT INTO audit_notification_sent_log (service_id, customer_id, stage, action, sent_date, recipient_email, sent_by, status, is_deleted) VALUES (?, ?, ?, ?, ?, ?, ?, 'sent', 'N')");
                    $insertDateStmt->execute(array($mainServiceId, $customerId, $stage, $actionName, $nowDate, $finalRecipientEmail, $sentBy));
                }
            }
        } catch (Exception $dbEx) {
            error_log("sendStageNotificationMail: Failed to record audit_notification_sent_log entry: " . $dbEx->getMessage());
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

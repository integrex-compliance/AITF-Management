<?php
/**
 * checkPendingLetters.php
 * AJAX endpoint: Returns JSON list of overdue unsent letters.
 * Used by the admin popup warning system to alert about failed automation mails.
 * 
 * Returns: { "pending": [ { customer_name, unique_id, customer_id, action, due_date, action_id } ] }
 */

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Kolkata');

include_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

// Only allow logged-in users
if (empty($_SESSION['login_user'])) {
    echo json_encode(['pending' => [], 'error' => 'Not logged in']);
    exit;
}

include_once __DIR__ . '/include/certification_cycle_helper.php';

try {
    $latestCertificationService = aitfLatestCertificationServiceSubquery('ci');

    // Fetch all overdue unsent letters (due_date <= today AND status != 'sent')
    // These are letters that SHOULD have been sent (by automation or manually) but haven't
    $validActions = "'Suspension Reminder', 'Suspend Letter', 'Withdrawal Reminder', 'Withdrawal Letter', 'Withdraw Letter', '1st Reminder', '2nd Reminder', 'Expire Letter'";

    $query = "SELECT ci.id AS customer_id, ci.customer_name, ci.unique_id, 
                     svcdt.action, svcdt.due_date, svcdt.action_id
              FROM customer_info ci 
              LEFT JOIN service svc ON ci.id = svc.customer_id 
              LEFT JOIN ca_service_dates svcdt ON svc.service_id = svcdt.service_id 
              WHERE svc.service_id = " . $latestCertificationService . " 
                AND svcdt.action IN (" . $validActions . ") 
                AND svcdt.due_date <= CURDATE() 
                AND svcdt.status != 'sent' 
                AND svcdt.is_deleted = 'N'
                AND (svcdt.is_intimation_deleted IS NULL OR svcdt.is_intimation_deleted = 'N')
                AND (ci.audit_status IS NULL OR ci.audit_status <> 'Withdrawn')
                AND (ci.audit_status IS NULL OR ci.audit_status <> 'Application canceled')
              ORDER BY svcdt.due_date ASC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'pending' => $pending,
        'count' => count($pending),
        'checked_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode([
        'pending' => [],
        'count' => 0,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

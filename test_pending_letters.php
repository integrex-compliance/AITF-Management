<?php
// Quick test script to check pending letters
include 'dbConnect.php';
include_once 'include/certification_cycle_helper.php';

$latestCertificationService = aitfLatestCertificationServiceSubquery('ci');
$validActions = "'Suspension Reminder', 'Suspend Letter', 'Withdrawal Reminder', 'Withdrawal Letter', 'Withdraw Letter', '1st Reminder', '2nd Reminder', 'Expire Letter'";

// Query WITHOUT the withdrawn/canceled filter to see ALL pending
$query = "SELECT ci.id AS customer_id, ci.customer_name, ci.unique_id, ci.audit_status,
                 svcdt.action, svcdt.due_date, svcdt.action_id, svcdt.status as letter_status
          FROM customer_info ci 
          LEFT JOIN service svc ON ci.id = svc.customer_id 
          LEFT JOIN ca_service_dates svcdt ON svc.service_id = svcdt.service_id 
          WHERE svc.service_id = " . $latestCertificationService . " 
            AND svcdt.action IN (" . $validActions . ") 
            AND svcdt.due_date <= CURDATE() 
            AND svcdt.status != 'sent' 
            AND svcdt.is_deleted = 'N'
          ORDER BY svcdt.due_date ASC LIMIT 20";

$stmt = $db->prepare($query);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== ALL PENDING LETTERS (no status filter) ===\n";
echo "Found: " . count($rows) . " records\n\n";
foreach ($rows as $r) {
    echo "Customer: " . $r['customer_name'] . " | ID: " . $r['customer_id'] 
         . " | audit_status: " . $r['audit_status']
         . " | Letter: " . $r['action'] 
         . " | Due: " . $r['due_date'] 
         . " | Letter Status: " . $r['letter_status'] . "\n";
}

// Now with the filter
$query2 = "SELECT ci.id AS customer_id, ci.customer_name, ci.audit_status, svcdt.action
          FROM customer_info ci 
          LEFT JOIN service svc ON ci.id = svc.customer_id 
          LEFT JOIN ca_service_dates svcdt ON svc.service_id = svcdt.service_id 
          WHERE svc.service_id = " . $latestCertificationService . " 
            AND svcdt.action IN (" . $validActions . ") 
            AND svcdt.due_date <= CURDATE() 
            AND svcdt.status != 'sent' 
            AND svcdt.is_deleted = 'N'
            AND (ci.audit_status IS NULL OR ci.audit_status <> 'Withdrawn')
            AND (ci.audit_status IS NULL OR ci.audit_status <> 'Application canceled')
          ORDER BY svcdt.due_date ASC LIMIT 20";

$stmt2 = $db->prepare($query2);
$stmt2->execute();
$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== PENDING LETTERS (with status filter - used by popup) ===\n";
echo "Found: " . count($rows2) . " records\n\n";
foreach ($rows2 as $r) {
    echo "Customer: " . $r['customer_name'] . " | audit_status: " . $r['audit_status'] . " | Letter: " . $r['action'] . "\n";
}

<?php
// Simple test script to debug mail sending
session_start();
include_once './dbConnect.php';

// Set test parameters - CHANGE THESE to match your data
$testCustomerId = 1; // Change to a valid customer ID
$testServiceId = 1;  // Change to a valid service ID  
$testAuditId = 1;    // Change to a valid audit ID
$testStage = 'stage1'; // or 'stage2'

echo "<h2>Testing Stage Mail Functionality</h2>";
echo "<p>Customer ID: $testCustomerId</p>";
echo "<p>Service ID: $testServiceId</p>";
echo "<p>Audit ID: $testAuditId</p>";
echo "<p>Stage: $testStage</p>";
echo "<hr>";

// Test 1: Check customer
$customerStmt = $db->prepare("SELECT customer_name, email_id FROM customer_info WHERE id = :id LIMIT 1");
$customerStmt->bindParam(':id', $testCustomerId, PDO::PARAM_INT);
$customerStmt->execute();
$customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

if ($customer) {
    echo "<p>✓ Customer found: " . $customer['customer_name'] . "</p>";
    echo "<p>✓ Email: " . $customer['email_id'] . "</p>";
} else {
    echo "<p>✗ Customer not found!</p>";
    exit;
}

// Test 2: Check notification
if ($testStage === 'stage1') {
    $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_oneaudit_id WHERE audit_stage_id = :audit_id AND ca_customer_id = :customer_id AND ca_service_id = :service_id LIMIT 1");
} else {
    $notificationStmt = $db->prepare("SELECT audit_dates FROM stage_twoaudit_id WHERE audit_stageii_id = :audit_id AND customer_id = :customer_id AND service_id = :service_id LIMIT 1");
}
$notificationStmt->bindParam(':audit_id', $testAuditId, PDO::PARAM_INT);
$notificationStmt->bindParam(':customer_id', $testCustomerId, PDO::PARAM_INT);
$notificationStmt->bindParam(':service_id', $testServiceId, PDO::PARAM_INT);
$notificationStmt->execute();
$notification = $notificationStmt->fetch(PDO::FETCH_ASSOC);

if ($notification) {
    echo "<p>✓ Notification found</p>";
    echo "<p>✓ Audit dates: " . ($notification['audit_dates'] ?? 'Not set') . "</p>";
} else {
    echo "<p>✗ Notification not found!</p>";
    echo "<p>Query: " . $notificationStmt->queryString . "</p>";
}

echo "<hr>";
echo "<p><a href='customerAccount.php'>Go back to Customer Account</a></p>";
?>

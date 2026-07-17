<?php
include_once 'dbConnect.php';

$cid = 581;
echo "\n--- service table for 581 ---\n";
$sql = "SELECT service_id, service_type, service_status, audit_date FROM service WHERE customer_id = :id ORDER BY service_id ASC";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $cid]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$cid = 1133;
echo "\n--- service table for 1133 ---\n";
$stmt->execute([':id' => $cid]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

?>

<?php
include 'dbConnect.php';

echo "Suspended clients in customer_info:\n";
$sql = "SELECT id, customer_name, audit_status FROM customer_info WHERE audit_status = 'Suspended'";
$stmt = $db->query($sql);
$suspendedClients = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($suspendedClients as $c) {
    $sql2 = "SELECT COUNT(*) as cnt FROM allotment_count WHERE client_id = " . $c['id'];
    $stmt2 = $db->query($sql2);
    $cnt = $stmt2->fetchColumn();
    echo $c['id'] . " - " . $c['customer_name'] . " (allotments: " . $cnt . ")\n";
}

echo "\nhomepage.php simulation for Suspended tab:\n";
$customerList = "SELECT ci.* , ac.certification_date FROM customer_info ci left join allotment_count ac on ci.id = ac.client_id where is_deleted <> 'YES' AND audit_status = 'Suspended'";
$stmt = $db->query($customerList);
$listOfCustomer = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total rows in homepage.php for Suspended: " . count($listOfCustomer) . "\n";

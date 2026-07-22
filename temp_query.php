<?php
require 'dbConnect.php';
$stmt = $db->query("SELECT service_id, audit_date FROM service WHERE audit_date LIKE '2031%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

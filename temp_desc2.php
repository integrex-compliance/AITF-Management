<?php
require 'dbConnect.php';
$stmt = $db->query("DESCRIBE service_type_rc");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

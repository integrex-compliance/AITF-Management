<?php
require 'dbConnect.php';
$stmt = $db->query("DESCRIBE service");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

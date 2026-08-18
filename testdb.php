<?php
include 'dbConnect.php';

try {
    $stmt = $db->query("DESCRIBE nc_closing_forms");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}

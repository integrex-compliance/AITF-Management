<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'dbConnect.php';
global $db;
try {
    $stmt = $db->query("DESCRIBE user");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>

<?php
include 'dbConnect.php';

try {
    $sql = "ALTER TABLE cdc_report_review ADD COLUMN is_cdc_only TINYINT(1) DEFAULT 0 AFTER cdc_status";
    $db->exec($sql);
    echo "Column is_cdc_only added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>

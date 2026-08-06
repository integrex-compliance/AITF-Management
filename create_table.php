<?php
include 'dbConnect.php';
try {
    $db->exec("CREATE TABLE IF NOT EXISTS employment_records (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        name VARCHAR(255), 
        age INT, 
        email VARCHAR(255), 
        mobile VARCHAR(50), 
        ctc VARCHAR(100), 
        job_profile VARCHAR(100), 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo 'Table created successfully.';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

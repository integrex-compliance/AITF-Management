<?php
session_start();
include_once './dbConnect.php';

echo "<h2>Fix Missing File Directories</h2>";

if (!isset($_SESSION['id'])) {
    die("Please login first");
}

// Get all file paths from database that should exist
$sql = "SELECT DISTINCT certification_aggrement FROM service_type_cr WHERE certification_aggrement IS NOT NULL AND certification_aggrement != ''";
$statement = $db->prepare($sql);
$statement->execute();
$filePaths = $statement->fetchAll(PDO::FETCH_COLUMN);

echo "<h3>Creating Missing Directories:</h3>";

$created = 0;
$errors = 0;

foreach ($filePaths as $filePath) {
    $directory = dirname($filePath);
    
    if (!is_dir($directory)) {
        if (mkdir($directory, 0777, true)) {
            echo "<p style='color: green;'> Created: " . htmlspecialchars($directory) . "</p>";
            $created++;
        } else {
            echo "<p style='color: red;'> Failed to create: " . htmlspecialchars($directory) . "</p>";
            $errors++;
        }
    } else {
        echo "<p style='color: blue;'>📁 Already exists: " . htmlspecialchars($directory) . "</p>";
    }
}

echo "<hr>";
echo "<h3>Summary:</h3>";
echo "<p><strong>Directories created:</strong> $created</p>";
echo "<p><strong>Errors:</strong> $errors</p>";

if ($created > 0) {
    echo "<p style='color: green;'><strong> Directory structure fixed!</strong></p>";
    echo "<p>You can now upload files using the upload buttons in the customer accounts.</p>";
} else {
    echo "<p><strong>All required directories already exist.</strong></p>";
}

echo "<p><a href='debug_customer_files.php'>← Back to Customer Files Debug</a></p>";
?>

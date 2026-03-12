<?php
// Simple test file to verify server accessibility
echo "IAF API Test - Server is working!<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Server name: " . $_SERVER['SERVER_NAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";

// Test database connection
include_once './dbConnect.php';
if ($db) {
    echo "Database connection: SUCCESS<br>";
    
    // Test a simple query
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM customer_info LIMIT 1");
        $result = $stmt->fetch();
        echo "Database query: SUCCESS - Found " . $result['count'] . " customers<br>";
    } catch (Exception $e) {
        echo "Database query: FAILED - " . $e->getMessage() . "<br>";
    }
} else {
    echo "Database connection: FAILED<br>";
}

// Check if IAF files exist
$files = ['iaf_api.php', 'iaf_sync.php', 'iaf_webhook.php', 'config/iaf_config.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "File $file: EXISTS<br>";
    } else {
        echo "File $file: MISSING<br>";
    }
}

// Test certificate lookup
if (isset($_GET['cert_no']) && !empty($_GET['cert_no'])) {
    try {
        $certNo = trim($_GET['cert_no']);
        $sql = "SELECT customer_name, certification_no, audit_status FROM customer_info WHERE certification_no LIKE :certNo LIMIT 1";
        $stmt = $db->prepare($sql);
        $certParam = "%{$certNo}%";
        $stmt->bindParam(':certNo', $certParam);
        $stmt->execute();
        $cert = $stmt->fetch();
        
        if ($cert) {
            echo "<h3>Certificate Found:</h3>";
            echo "Client: " . htmlspecialchars($cert['customer_name']) . "<br>";
            echo "Certificate: " . htmlspecialchars($cert['certification_no']) . "<br>";
            echo "Status: " . htmlspecialchars($cert['audit_status']) . "<br>";
        } else {
            echo "<h3>No certificate found for: " . htmlspecialchars($certNo) . "</h3>";
        }
    } catch (Exception $e) {
        echo "Certificate lookup error: " . $e->getMessage() . "<br>";
    }
}

echo "<br><h3>API Test Links:</h3>";
echo '<a href="test_iaf.php?cert_no=71361761496">Test Certificate Lookup</a><br>';
echo '<a href="iaf_api.php?certificate_no=71361761496">Test IAF API</a><br>';
?>

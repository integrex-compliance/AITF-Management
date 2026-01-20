<?php
session_start();
include_once './dbConnect.php';

echo "<h2>Debug Allotment Number Issue</h2>";

if (!isset($_SESSION['id'])) {
    die("Please login first");
}

// Get customer ID and service ID from URL or show form
$customerId = isset($_GET['customerid']) ? intval($_GET['customerid']) : 0;
$serviceId = isset($_GET['serviceid']) ? intval($_GET['serviceid']) : 0;

if (!$customerId || !$serviceId) {
    echo "<h3>Enter Customer and Service ID to debug:</h3>";
    echo "<form method='GET'>";
    echo "Customer ID: <input type='number' name='customerid' value='$customerId' required><br><br>";
    echo "Service ID: <input type='number' name='serviceid' value='$serviceId' required><br><br>";
    echo "<input type='submit' value='Debug'>";
    echo "</form>";
    
    // Show recent customers for reference
    echo "<h4>Recent Customers with Services:</h4>";
    try {
        $sql = "SELECT ci.id as customer_id, ci.customer_name, cr.cr_id as service_id, ci.unique_id 
                FROM customer_info ci 
                JOIN service_type_cr cr ON ci.id = cr.customer_id 
                ORDER BY ci.id DESC LIMIT 10";
        $statement = $db->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll();
        
        if ($results) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Customer Name</th><th>Customer ID</th><th>Service ID</th><th>Original unique_id</th><th>Debug Link</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                echo "<td>" . $row['customer_id'] . "</td>";
                echo "<td>" . $row['service_id'] . "</td>";
                echo "<td>" . $row['unique_id'] . "</td>";
                echo "<td><a href='?customerid=" . $row['customer_id'] . "&serviceid=" . $row['service_id'] . "'>Debug</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    exit;
}

echo "<h3>Debugging Customer ID: $customerId, Service ID: $serviceId</h3>";

try {
    // Step 1: Check customer info
    echo "<h4>1. Customer Information:</h4>";
    $customerSql = "SELECT * FROM customer_info WHERE id = :id";
    $customerStmt = $db->prepare($customerSql);
    $customerStmt->bindParam(':id', $customerId);
    $customerStmt->execute();
    $customerData = $customerStmt->fetch();
    
    if ($customerData) {
        echo "<p><strong>Customer Name:</strong> " . $customerData['customer_name'] . "</p>";
        echo "<p><strong>Original unique_id:</strong> " . $customerData['unique_id'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Customer not found!</p>";
        exit;
    }
    
    // Step 2: Check if allotment_count table exists
    echo "<h4>2. Allotment Count Table Check:</h4>";
    try {
        $checkTableSql = "SHOW TABLES LIKE 'allotment_count'";
        $checkStmt = $db->prepare($checkTableSql);
        $checkStmt->execute();
        $tableExists = $checkStmt->fetch();
        
        if ($tableExists) {
            echo "<p style='color: green;'>✅ allotment_count table exists</p>";
            
            // Step 3: Check for allotment records
            echo "<h4>3. Allotment Records for this Customer/Service:</h4>";
            $allotmentSql = "SELECT * FROM allotment_count WHERE customer_id = :customer_id AND service_id = :service_id ORDER BY id DESC";
            $allotmentStmt = $db->prepare($allotmentSql);
            $allotmentStmt->bindParam(':customer_id', $customerId);
            $allotmentStmt->bindParam(':service_id', $serviceId);
            $allotmentStmt->execute();
            $allotmentRecords = $allotmentStmt->fetchAll();
            
            if ($allotmentRecords) {
                echo "<p style='color: green;'>✅ Found " . count($allotmentRecords) . " allotment record(s)</p>";
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr><th>ID</th><th>Customer ID</th><th>Service ID</th><th>Allotment No</th><th>Created Date</th></tr>";
                foreach ($allotmentRecords as $record) {
                    echo "<tr>";
                    echo "<td>" . $record['id'] . "</td>";
                    echo "<td>" . $record['customer_id'] . "</td>";
                    echo "<td>" . $record['service_id'] . "</td>";
                    echo "<td><strong>" . $record['allotment_no'] . "</strong></td>";
                    echo "<td>" . (isset($record['created_date']) ? $record['created_date'] : 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                $latestAllotment = $allotmentRecords[0]['allotment_no'];
                echo "<p><strong>Latest Allotment Number:</strong> $latestAllotment</p>";
            } else {
                echo "<p style='color: red;'>❌ No allotment records found for Customer: $customerId, Service: $serviceId</p>";
                
                // Check if there are any allotment records for this customer at all
                $customerAllotmentSql = "SELECT * FROM allotment_count WHERE customer_id = :customer_id";
                $customerAllotmentStmt = $db->prepare($customerAllotmentSql);
                $customerAllotmentStmt->bindParam(':customer_id', $customerId);
                $customerAllotmentStmt->execute();
                $customerAllotments = $customerAllotmentStmt->fetchAll();
                
                if ($customerAllotments) {
                    echo "<p style='color: orange;'>⚠️ But found " . count($customerAllotments) . " allotment record(s) for this customer with different service IDs:</p>";
                    echo "<ul>";
                    foreach ($customerAllotments as $record) {
                        echo "<li>Service ID: " . $record['service_id'] . " - Allotment: " . $record['allotment_no'] . "</li>";
                    }
                    echo "</ul>";
                }
            }
        } else {
            echo "<p style='color: red;'>❌ allotment_count table does not exist!</p>";
        }
    } catch (Exception $tableEx) {
        echo "<p style='color: red;'>❌ Error checking allotment_count table: " . $tableEx->getMessage() . "</p>";
    }
    
    // Step 4: Test the actual getClientInfo.php call
    echo "<h4>4. Test getClientInfo.php Response:</h4>";
    $testUrl = "getClientInfo.php?customerid=$customerId&serviceid=$serviceId";
    echo "<p><strong>Test URL:</strong> <a href='$testUrl' target='_blank'>$testUrl</a></p>";
    
    try {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Cookie: " . $_SERVER['HTTP_COOKIE'] . "\r\n"
            ]
        ]);
        $response = file_get_contents("http://localhost/AITF_PROJECT/AITF_PROJECT/backup2/public_html/$testUrl", false, $context);
        
        echo "<p><strong>Raw Response:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: scroll;'>";
        echo htmlspecialchars($response);
        echo "</pre>";
        
        $data = json_decode($response, true);
        if ($data && isset($data['unique_id'])) {
            echo "<p><strong>Extracted unique_id:</strong> " . $data['unique_id'] . "</p>";
            
            if (strpos($data['unique_id'], 'AITF/') === 0) {
                echo "<p style='color: green;'>✅ SUCCESS: Allotment number is being returned!</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Using original unique_id instead of allotment number</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Error parsing response or unique_id not found</p>";
        }
        
    } catch (Exception $responseEx) {
        echo "<p style='color: red;'>❌ Error testing getClientInfo.php: " . $responseEx->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ General Error: " . $e->getMessage() . "</p>";
}
?>

<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
form input[type="number"] { padding: 5px; margin: 5px 0; width: 100px; }
form input[type="submit"] { padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; }
</style>

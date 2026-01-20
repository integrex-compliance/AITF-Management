<?php
session_start();
include_once './dbConnect.php';

// Test file to verify comment update works
$testCustomerId = isset($_GET['customer_id']) ? $_GET['customer_id'] : null;

if ($testCustomerId) {
    echo "<h2>Testing Comment Update for Customer ID: $testCustomerId</h2>";
    
    // First, get current comment
    try {
        $sql = "SELECT id, customer_name, comment FROM customer_info WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $testCustomerId);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        
        if ($customer) {
            echo "<h3>Current Data:</h3>";
            echo "<pre>";
            print_r($customer);
            echo "</pre>";
            
            // Now test update
            if (isset($_GET['test_update'])) {
                $testComment = "Test update at " . date('Y-m-d H:i:s');
                $updateSql = "UPDATE customer_info SET comment = :comment WHERE id = :id";
                $updateStmt = $db->prepare($updateSql);
                $updateStmt->bindParam(':comment', $testComment);
                $updateStmt->bindParam(':id', $testCustomerId);
                $result = $updateStmt->execute();
                $rowCount = $updateStmt->rowCount();
                
                echo "<h3>Update Test Result:</h3>";
                echo "Execute Result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";
                echo "Rows Affected: $rowCount<br>";
                echo "Test Comment: $testComment<br>";
                
                // Read again
                $stmt->execute();
                $updatedCustomer = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<h3>After Update:</h3>";
                echo "<pre>";
                print_r($updatedCustomer);
                echo "</pre>";
            } else {
                echo "<br><a href='?customer_id=$testCustomerId&test_update=1'>Click to Test Update</a>";
            }
        } else {
            echo "<p style='color:red;'>Customer not found!</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<h2>Provide customer_id in URL parameter</h2>";
    echo "<p>Example: test_comment_update.php?customer_id=123</p>";
}
?>

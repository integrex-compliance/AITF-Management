<?php
include_once './dbConnect.php';
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Compare Certificate Numbers</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .client { border: 2px solid #333; padding: 15px; margin: 20px 0; border-radius: 8px; }
        .client h2 { margin-top: 0; color: #fff; padding: 10px; border-radius: 5px; }
        .working { background: #d4edda; }
        .working h2 { background: #28a745; }
        .notworking { background: #f8d7da; }
        .notworking h2 { background: #dc3545; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; font-weight: bold; }
        .highlight { background: #fff3cd; font-weight: bold; }
        .empty { color: #dc3545; font-style: italic; }
        .found { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔍 Certificate Number Comparison</h1>
    <p><strong>Purpose:</strong> Show exactly where certificate numbers are stored in the database</p>
    
    <?php
    // Client 1: SHRI BHAVANI ENTERPRISES (Working)
    $client1Id = 198; // SHRI BHAVANI
    $client1Name = "SHRI BHAVANI ENTERPRISES";
    
    // Client 2: 2 P Technologies (Not Working)
    $client2Id = 1163; // 2 P Technologies
    $client2Name = "2 P Technologies";
    
    function displayClientData($db, $clientId, $clientName, $isWorking) {
        echo "<div class='client " . ($isWorking ? "working" : "notworking") . "'>";
        echo "<h2>" . ($isWorking ? "✅ WORKING" : "❌ NOT WORKING") . ": " . htmlspecialchars($clientName) . " (ID: $clientId)</h2>";
        
        // Get customer_info data
        $sql = "SELECT id, customer_name, certification_no, audit_status FROM customer_info WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $clientId);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>1. customer_info Table</h3>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>Customer Name</td><td>" . htmlspecialchars($customer['customer_name']) . "</td></tr>";
        echo "<tr><td>Audit Status</td><td>" . htmlspecialchars($customer['audit_status']) . "</td></tr>";
        echo "<tr class='highlight'><td><strong>certification_no</strong></td><td>";
        if (!empty($customer['certification_no'])) {
            echo "<span class='found'>" . htmlspecialchars($customer['certification_no']) . " ← THIS IS WHY IT WORKS!</span>";
        } else {
            echo "<span class='empty'>(EMPTY - THIS IS THE PROBLEM!)</span>";
        }
        echo "</td></tr>";
        echo "</table>";
        
        // Get audit schedule with CDC check
        $sql3 = "SELECT schedule_app_standard, serviceca_id FROM audit_schedule_id WHERE customer_ca_id = :id";
        $stmt3 = $db->prepare($sql3);
        $stmt3->bindParam(':id', $clientId);
        $stmt3->execute();
        $schedules = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>2. audit_schedule_id Table (ISO Standards) + CDC Records</h3>";
        if ($schedules) {
            echo "<table>";
            echo "<tr><th>ISO Standards</th><th>Service CA ID</th><th>CDC Certificate No</th></tr>";
            foreach ($schedules as $sch) {
                // Check CDC table for each service
                $cdcSql = "SELECT certificateNo FROM cdc_report_review WHERE ca_service_id = :svcId ORDER BY Date DESC LIMIT 1";
                $cdcStmt = $db->prepare($cdcSql);
                $cdcStmt->bindParam(':svcId', $sch['serviceca_id']);
                $cdcStmt->execute();
                $cdcData = $cdcStmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($sch['schedule_app_standard']) . "</td>";
                echo "<td><strong>" . $sch['serviceca_id'] . "</strong></td>";
                echo "<td>";
                if ($cdcData && !empty($cdcData['certificateNo'])) {
                    echo "<span class='found'>" . htmlspecialchars($cdcData['certificateNo']) . "</span>";
                } else {
                    echo "<span class='empty'>(No certificate in CDC table)</span>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='empty'>No ISO standards found</p>";
        }
        
        echo "<h3>📊 Summary</h3>";
        if (!empty($customer['certification_no'])) {
            echo "<p class='found'><strong>Certificate Number Source:</strong> customer_info.certification_no = " . htmlspecialchars($customer['certification_no']) . "</p>";
            echo "<p>✅ This is why the certificate number displays on the customer page!</p>";
        } else {
            echo "<p class='empty'><strong>Problem:</strong> customer_info.certification_no is EMPTY</p>";
            echo "<p>❌ Without this value OR CDC records, no certificate number can be displayed!</p>";
            echo "<p><strong>Solution:</strong> Either:</p>";
            echo "<ul>";
            echo "<li>Update customer_info.certification_no field in database, OR</li>";
            echo "<li>Create CDC records with certificate numbers for each ISO standard</li>";
            echo "</ul>";
        }
        
        echo "</div>";
    }
    
    displayClientData($db, $client1Id, $client1Name, true);
    displayClientData($db, $client2Id, $client2Name, false);
    ?>
    
    <div style="background: #e7f3ff; border: 2px solid #0066cc; padding: 20px; margin: 20px 0; border-radius: 8px;">
        <h2>🎯 Conclusion</h2>
        <p><strong>The code logic IS THE SAME for both clients!</strong></p>
        <p>The difference is in the <strong>database data</strong>:</p>
        <ul>
            <li><strong>SHRI BHAVANI:</strong> Has certificate number in <code>customer_info.certification_no</code> field ✅</li>
            <li><strong>2 P Technologies:</strong> Does NOT have certificate number in <code>customer_info.certification_no</code> field ❌</li>
        </ul>
        <p><strong>To fix:</strong> Add the certificate number(s) to the database for 2 P Technologies</p>
    </div>
</body>
</html>

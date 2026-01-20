<?php
include_once './dbConnect.php';
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Find Certificate Numbers</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .result { background: white; border: 2px solid #333; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .found { background: #d4edda; border-color: #28a745; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; font-weight: bold; }
        .highlight { background: #fff3cd; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔍Find Certificate Numbers for Customer 1196</h1>
    
    <?php
    $certNum1 = '2170014510';
    $certNum2 = '2170014511';
    $customerId = 1196;
    
    echo "<div class='result'>";
    echo "<h2>Searching for Certificate Numbers:</h2>";
    echo "<ul>";
    echo "<li><strong>$certNum1</strong> (ISO 14001:2015)</li>";
    echo "<li><strong>$certNum2</strong> (ISO 27001:2022)</li>";
    echo "</ul>";
    echo "<p><strong>Customer ID: $customerId</strong></p>";
    echo "</div>";
    
    // Search in cdc_report_review table
    $sql = "SELECT crr_id, ca_service_id, customer_id, certificateNo, revDatecdc, expiredate 
            FROM cdc_report_review 
            WHERE certificateNo IN (:cert1, :cert2)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':cert1', $certNum1);
    $stmt->bindParam(':cert2', $certNum2);
    $stmt->execute();
    $cdcRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($cdcRecords) {
        echo "<div class='result found'>";
        echo "<h2> FOUND in cdc_report_review table!</h2>";
        echo "<table>";
        echo "<tr><th>CDC ID</th><th>ca_service_id</th><th>customer_id</th><th>Certificate No</th><th>Issue Date</th><th>Expire Date</th></tr>";
        foreach ($cdcRecords as $rec) {
            echo "<tr>";
            echo "<td>{$rec['crr_id']}</td>";
            echo "<td class='highlight'><strong>{$rec['ca_service_id']}</strong></td>";
            echo "<td>{$rec['customer_id']}</td>";
            echo "<td class='highlight'><strong>{$rec['certificateNo']}</strong></td>";
            echo "<td>{$rec['revDatecdc']}</td>";
            echo "<td>{$rec['expiredate']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>These are the ACTUAL ca_service_id values we need to query!</strong></p>";
        echo "</div>";
    } else {
        echo "<div class='result'>";
        echo "<h2> NOT FOUND in cdc_report_review table</h2>";
        echo "<p>Certificate numbers do not exist in the table yet.</p>";
        echo "</div>";
    }
    
    // Check cdc_report_review_office_use table (NEW table)
    echo "<div class='result'>";
    echo "<h2>Search in cdc_report_review_office_use (NEW table):</h2>";
    $sqlOffice = "SELECT * FROM cdc_report_review_office_use WHERE certificate_no IN (:cert1, :cert2)";
    $stmtOffice = $db->prepare($sqlOffice);
    $stmtOffice->bindParam(':cert1', $certNum1);
    $stmtOffice->bindParam(':cert2', $certNum2);
    $stmtOffice->execute();
    $officeRecords = $stmtOffice->fetchAll(PDO::FETCH_ASSOC);
    
    if ($officeRecords) {
        echo "<div style='background:#d4edda; padding:10px; margin:10px 0;'>";
        echo "<h3> FOUND in NEW table!</h3>";
        echo "</div>";
        echo "<table>";
        echo "<tr><th>id</th><th>crr_id</th><th>iso_index</th><th>iso_label</th><th>certificate_no</th><th>rev_date</th><th>expire_date</th></tr>";
        foreach ($officeRecords as $rec) {
            echo "<tr>";
            echo "<td>{$rec['id']}</td>";
            echo "<td class='highlight'><strong>{$rec['crr_id']}</strong></td>";
            echo "<td>{$rec['iso_index']}</td>";
            echo "<td>{$rec['iso_label']}</td>";
            echo "<td class='highlight'><strong>{$rec['certificate_no']}</strong></td>";
            echo "<td>{$rec['rev_date']}</td>";
            echo "<td>{$rec['expire_date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p> NOT FOUND in office_use table</p>";
    }
    echo "</div>";
    
    // Also check what service IDs exist for this customer
    echo "<div class='result'>";
    echo "<h2>Service IDs for Customer $customerId:</h2>";
    $sql2 = "SELECT audit_schedule_id, serviceca_id, schedule_app_standard 
             FROM audit_schedule_id 
             WHERE customer_ca_id = :custId";
    $stmt2 = $db->prepare($sql2);
    $stmt2->bindParam(':custId', $customerId);
    $stmt2->execute();
    $schedules = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    if ($schedules) {
        echo "<table>";
        echo "<tr><th>Schedule ID</th><th>serviceca_id</th><th>ISO Standard</th></tr>";
        foreach ($schedules as $sch) {
            echo "<tr>";
            echo "<td>{$sch['audit_schedule_id']}</td>";
            echo "<td class='highlight'><strong>{$sch['serviceca_id']}</strong></td>";
            echo "<td>{$sch['schedule_app_standard']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // Check ALL CDC records for this customer
    echo "<div class='result'>";
    echo "<h2>All CDC Records for Customer $customerId:</h2>";
    $sql3 = "SELECT crr_id, ca_service_id, certificateNo, revDatecdc 
             FROM cdc_report_review 
             WHERE customer_id = :custId";
    $stmt3 = $db->prepare($sql3);
    $stmt3->bindParam(':custId', $customerId);
    $stmt3->execute();
    $allCdc = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
    if ($allCdc) {
        echo "<table>";
        echo "<tr><th>CDC ID</th><th>ca_service_id</th><th>Certificate No</th><th>Issue Date</th></tr>";
        foreach ($allCdc as $cdc) {
            echo "<tr>";
            echo "<td>{$cdc['crr_id']}</td>";
            echo "<td class='highlight'><strong>{$cdc['ca_service_id']}</strong></td>";
            echo "<td>" . ($cdc['certificateNo'] ? htmlspecialchars($cdc['certificateNo']) : '(empty)') . "</td>";
            echo "<td>{$cdc['revDatecdc']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No CDC records found for customer $customerId</p>";
    }
    echo "</div>";
    ?>
</body>
</html>

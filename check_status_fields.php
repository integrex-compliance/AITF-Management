<?php
/**
 * Check what status fields are available for 2 P TECHNOLOGIES
 */

include_once './dbConnect.php';
header('Content-Type: text/html; charset=utf-8');

echo "<style>
    body{font-family:Arial;padding:20px;background:#f5f5f5;} 
    table{border-collapse:collapse;width:100%;margin:15px 0;background:white;} 
    th,td{border:1px solid #ddd;padding:10px;text-align:left;} 
    th{background:#4CAF50;color:white;} 
    .section{background:white;padding:20px;margin:15px 0;border-radius:5px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
    h2{color:#333;border-bottom:2px solid #4CAF50;padding-bottom:10px;}
    .highlight{background:#fff9c4;font-weight:bold;}
</style>";

echo "<h1>🔍 Status Fields Check for 2 P TECHNOLOGIES</h1>";

try {
    $customerId = 1163;
    
    // 1. Customer Info Status (SHARED)
    echo "<div class='section'>";
    echo "<h2>1. Customer Info Table (SHARED for all ISOs)</h2>";
    $custQuery = "SELECT audit_status, certification_stage_date FROM customer_info WHERE id = :custId";
    $custStmt = $db->prepare($custQuery);
    $custStmt->bindParam(':custId', $customerId);
    $custStmt->execute();
    $custData = $custStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Field</th><th>Value</th><th>Description</th></tr>";
    echo "<tr><td class='highlight'>audit_status</td><td><strong>{$custData['audit_status']}</strong></td><td>Overall customer certification status (Withdrawn, Certified, etc.)</td></tr>";
    echo "<tr><td>certification_stage_date</td><td>{$custData['certification_stage_date']}</td><td>Shared decision date</td></tr>";
    echo "</table>";
    echo "<p><strong>Note:</strong> This is a SHARED field - same value for all ISO standards</p>";
    echo "</div>";
    
    // 2. CDC Report Review Status (PER SERVICE)
    echo "<div class='section'>";
    echo "<h2>2. CDC Report Review Table (PER Service/ISO)</h2>";
    
    $schedQuery = "SELECT serviceca_id, schedule_app_standard FROM audit_schedule_id WHERE customer_ca_id = :custId";
    $schedStmt = $db->prepare($schedQuery);
    $schedStmt->bindParam(':custId', $customerId);
    $schedStmt->execute();
    $schedules = $schedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Service ID</th><th>ISO Standard</th><th>cdc_status</th><th>Date</th><th>Description</th></tr>";
    
    foreach ($schedules as $sch) {
        $standards = explode(',', $sch['schedule_app_standard']);
        
        $cdcQuery = "SELECT cdc_status, Date FROM cdc_report_review WHERE ca_service_id = :svcId ORDER BY Date DESC LIMIT 1";
        $cdcStmt = $db->prepare($cdcQuery);
        $cdcStmt->bindParam(':svcId', $sch['serviceca_id']);
        $cdcStmt->execute();
        $cdcData = $cdcStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cdcData) {
            foreach ($standards as $std) {
                $std = trim($std);
                echo "<tr>";
                echo "<td><strong>{$sch['serviceca_id']}</strong></td>";
                echo "<td>{$std}</td>";
                echo "<td class='highlight' style='background:" . ($cdcData['cdc_status'] == 'Granted' ? '#90EE90' : '#ffcccc') . ";'><strong>{$cdcData['cdc_status']}</strong></td>";
                echo "<td>{$cdcData['Date']}</td>";
                echo "<td>CDC Committee Decision (Granted/Not Granted)</td>";
                echo "</tr>";
            }
        }
    }
    echo "</table>";
    echo "<p><strong>Note:</strong> This is ISO-specific but tracks CDC DECISION, not certification status</p>";
    echo "</div>";
    
    // 3. Check service_type_ca for per-service status
    echo "<div class='section'>";
    echo "<h2>3. Service Type CA Table (Check for per-service status)</h2>";
    
    $svcQuery = "SELECT ca_id, service, service_ca_status FROM service_type_ca WHERE customer_id = :custId";
    $svcStmt = $db->prepare($svcQuery);
    $svcStmt->bindParam(':custId', $customerId);
    $svcStmt->execute();
    $services = $svcStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($services) {
        echo "<table>";
        echo "<tr><th>CA ID</th><th>Service</th><th>service_ca_status</th></tr>";
        foreach ($services as $svc) {
            echo "<tr>";
            echo "<td>{$svc['ca_id']}</td>";
            echo "<td>{$svc['service']}</td>";
            echo "<td class='highlight'><strong>" . ($svc['service_ca_status'] ?? '(empty)') . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No records found</p>";
    }
    echo "</div>";
    
    // Explanation
    echo "<div class='section' style='background:#fff3cd;border:2px solid #856404;'>";
    echo "<h2>📊 Status Fields Explanation</h2>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Scope</th><th>Values</th><th>Usage</th></tr>";
    echo "<tr>";
    echo "<td><strong>customer_info.audit_status</strong></td>";
    echo "<td>SHARED (all ISOs)</td>";
    echo "<td>Withdrawn, Certified, Suspended, etc.</td>";
    echo "<td>Overall customer status - your screenshot showed 'Withdrawn'</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><strong>cdc_report_review.cdc_status</strong></td>";
    echo "<td>PER ISO</td>";
    echo "<td>Granted, Not Granted</td>";
    echo "<td>CDC committee decision - currently showing 'Granted'</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><strong>service_type_ca.service_ca_status</strong></td>";
    echo "<td>PER Service</td>";
    echo "<td>Various status values</td>";
    echo "<td>Service-level status (if used)</td>";
    echo "</tr>";
    echo "</table>";
    
    echo "<h3>🎯 Which Status Should We Show?</h3>";
    echo "<p><strong>Current behavior:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Before my changes:</strong> Showed <code>customer_info.audit_status</code> = '<strong>{$custData['audit_status']}</strong>' (same for all ISOs)</li>";
    echo "<li><strong>After my changes:</strong> Showing <code>cdc_report_review.cdc_status</code> = '<strong>Granted</strong>' (per ISO, but different meaning)</li>";
    echo "</ul>";
    
    echo "<p><strong>Options:</strong></p>";
    echo "<ol>";
    echo "<li><strong>Keep showing CDC Status (Granted/Not Granted)</strong> - Shows CDC committee decision per ISO</li>";
    echo "<li><strong>Go back to audit_status (Withdrawn/Certified)</strong> - Shows overall certification status (same for all ISOs)</li>";
    echo "<li><strong>Show audit_status for 1st ISO, CDC status for 2nd+</strong> - Hybrid approach</li>";
    echo "<li><strong>Don't show status for 2nd+ ISOs</strong> - Only show for primary ISO</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<?php
/**
 * Check if Issue/Expire dates are the same for both ISOs or different
 */

include_once './dbConnect.php';
header('Content-Type: text/html; charset=utf-8');

echo "<style>
    body{font-family:Arial;padding:20px;background:#f5f5f5;} 
    table{border-collapse:collapse;width:100%;margin:15px 0;background:white;} 
    th,td{border:1px solid #ddd;padding:10px;text-align:left;} 
    th{background:#4CAF50;color:white;} 
    .same{background:#ffcccc;} 
    .different{background:#90EE90;}
    .section{background:white;padding:20px;margin:15px 0;border-radius:5px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
    h2{color:#333;border-bottom:2px solid #4CAF50;padding-bottom:10px;}
</style>";

echo "<h1>🔍 Check ISO-Specific Dates for 2 P TECHNOLOGIES</h1>";

try {
    // Get customer
    $customerId = 1163; // 2 P TECHNOLOGIES
    
    // Get audit schedules with service IDs
    $sql = "SELECT audit_schedule_id, serviceca_id, schedule_app_standard, customer_ca_id
            FROM audit_schedule_id 
            WHERE customer_ca_id = :custId
            ORDER BY audit_schedule_id DESC";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':custId', $customerId);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='section'>";
    echo "<h2>Step 1: Audit Schedule Records</h2>";
    echo "<table>";
    echo "<tr><th>Schedule ID</th><th>Service CA ID</th><th>ISO Standards</th></tr>";
    foreach ($schedules as $sch) {
        echo "<tr>";
        echo "<td>{$sch['audit_schedule_id']}</td>";
        echo "<td><strong>{$sch['serviceca_id']}</strong></td>";
        echo "<td>{$sch['schedule_app_standard']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // For each service, check dates
    echo "<div class='section'>";
    echo "<h2>Step 2: CDC Report Review Records (Per Service ID)</h2>";
    echo "<p>These should have DIFFERENT dates if each ISO was processed separately.</p>";
    echo "<table>";
    echo "<tr><th>Service ID</th><th>CDC ID</th><th>CDC Status</th><th>Decision Date (Date field)</th><th>Issue Date (revDatecdc)</th><th>Expire Date (expiredate)</th></tr>";
    
    $cdcDates = [];
    foreach ($schedules as $sch) {
        $svcId = $sch['serviceca_id'];
        
        $cdcQuery = "SELECT crr_id, cdc_status, Date, revDatecdc, expiredate 
                     FROM cdc_report_review 
                     WHERE ca_service_id = :svcId 
                     ORDER BY Date DESC LIMIT 1";
        $cdcStmt = $db->prepare($cdcQuery);
        $cdcStmt->bindParam(':svcId', $svcId);
        $cdcStmt->execute();
        $cdcData = $cdcStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cdcData) {
            $cdcDates[$svcId] = $cdcData;
            echo "<tr>";
            echo "<td><strong>{$svcId}</strong></td>";
            echo "<td>{$cdcData['crr_id']}</td>";
            echo "<td style='font-weight:bold;'>{$cdcData['cdc_status']}</td>";
            echo "<td style='font-weight:bold;'>{$cdcData['Date']}</td>";
            echo "<td>{$cdcData['revDatecdc']}</td>";
            echo "<td>{$cdcData['expiredate']}</td>";
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td><strong>{$svcId}</strong></td>";
            echo "<td colspan='5' style='color:red;'>No CDC record found</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    echo "</div>";
    
    // Check office_use table
    echo "<div class='section'>";
    echo "<h2>Step 3: CDC Office Use Table (Per ISO Standard)</h2>";
    echo "<p>This is where each ISO should have its OWN certificate number and dates.</p>";
    
    $allCrrIds = array_column($cdcDates, 'crr_id');
    if (!empty($allCrrIds)) {
        $placeholders = implode(',', array_fill(0, count($allCrrIds), '?'));
        $officeQuery = "SELECT crr_id, iso_index, iso_label, certificate_no, rev_date, expire_date 
                       FROM cdc_report_review_office_use 
                       WHERE crr_id IN ($placeholders)
                       ORDER BY crr_id, iso_index";
        $officeStmt = $db->prepare($officeQuery);
        $officeStmt->execute($allCrrIds);
        $officeRecords = $officeStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($officeRecords) {
            echo "<table>";
            echo "<tr><th>CDC ID</th><th>ISO Index</th><th>ISO Label</th><th>Certificate No</th><th>Rev Date (Issue)</th><th>Expire Date</th></tr>";
            
            $dateGroups = [];
            foreach ($officeRecords as $rec) {
                $dateKey = $rec['rev_date'] . '|' . $rec['expire_date'];
                $dateGroups[$dateKey][] = $rec['iso_label'];
                
                echo "<tr>";
                echo "<td>{$rec['crr_id']}</td>";
                echo "<td>{$rec['iso_index']}</td>";
                echo "<td style='font-weight:bold;'>{$rec['iso_label']}</td>";
                echo "<td style='background:" . (!empty($rec['certificate_no']) ? '#90EE90' : '#ffcccc') . ";'>" . 
                     ($rec['certificate_no'] ?? '(empty)') . "</td>";
                echo "<td style='font-weight:bold;'>{$rec['rev_date']}</td>";
                echo "<td style='font-weight:bold;'>{$rec['expire_date']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Analyze if dates are same or different
            echo "<h3>📊 Date Analysis</h3>";
            if (count($dateGroups) == 1) {
                echo "<p class='same' style='padding:15px;border-radius:5px;'><strong>⚠️ SAME DATES:</strong> All ISO standards have the <strong>SAME</strong> rev_date and expire_date.</p>";
                echo "<p>This means they were likely certified on the same day, which is valid but unusual for multi-ISO clients.</p>";
            } else {
                echo "<p class='different' style='padding:15px;border-radius:5px;'><strong>✓ DIFFERENT DATES:</strong> ISO standards have different dates!</p>";
                foreach ($dateGroups as $dates => $isos) {
                    list($revDate, $expireDate) = explode('|', $dates);
                    echo "<p><strong>Date Group:</strong> Rev: {$revDate}, Expire: {$expireDate}<br>";
                    echo "<strong>ISOs:</strong> " . implode(', ', $isos) . "</p>";
                }
            }
        } else {
            echo "<p style='color:red;font-weight:bold;'>❌ No records in office_use table!</p>";
            echo "<p>This means certificate numbers and ISO-specific dates need to be entered in the CDC Report 'For Office Use' section.</p>";
        }
    } else {
        echo "<p style='color:red;'>No CDC records found to check office_use table.</p>";
    }
    echo "</div>";
    
    // Check customer_info shared dates
    echo "<div class='section'>";
    echo "<h2>Step 4: Shared Customer Info Dates</h2>";
    $custQuery = "SELECT certification_stage_date FROM customer_info WHERE id = :custId";
    $custStmt = $db->prepare($custQuery);
    $custStmt->bindParam(':custId', $customerId);
    $custStmt->execute();
    $custData = $custStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>customer_info.certification_stage_date:</strong> {$custData['certification_stage_date']}</p>";
    echo "<p style='color:#666;font-style:italic;'>This is a SHARED field and will be the same for all ISO standards if used as fallback.</p>";
    echo "</div>";
    
    // Conclusion
    echo "<div class='section' style='background:#e7f3ff;border:2px solid #0066cc;'>";
    echo "<h2>🎯 Conclusion</h2>";
    echo "<p><strong>What you're seeing on the customer page depends on:</strong></p>";
    echo "<ol>";
    echo "<li><strong>If office_use table HAS data:</strong> Each ISO shows its own rev_date and expire_date (can be same or different)</li>";
    echo "<li><strong>If office_use table is EMPTY:</strong> Both ISOs show the same dates from customer_info (shared fallback)</li>";
    echo "</ol>";
    echo "<p><strong>To have DIFFERENT dates for each ISO:</strong> Enter separate dates in the CDC Report 'For Office Use' section for each ISO standard.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<?php
/*
 * Debug script for 2 P TECHNOLOGIES - Check multi-ISO support
 */

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Debug: 2 P TECHNOLOGIES - Multi-ISO Standards</h1>";
echo "<style>
    body{font-family:Arial;padding:20px;background:#f5f5f5;} 
    table{border-collapse:collapse;width:100%;margin:15px 0;background:white;} 
    th,td{border:1px solid #ddd;padding:10px;text-align:left;} 
    th{background:#4CAF50;color:white;} 
    .success{color:green;font-weight:bold;} 
    .error{color:red;font-weight:bold;}
    .section{background:white;padding:20px;margin:15px 0;border-radius:5px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
    .header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;padding:15px;border-radius:5px;margin-bottom:15px;}
</style>";

try {
    // Find customer by name
    $sql = "SELECT * FROM customer_info WHERE customer_name LIKE '%2 P TECHNOLOGIES%' OR customer_name LIKE '%2P TECHNOLOGIES%'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        echo "<p class='error'>✗ Customer '2 P TECHNOLOGIES' not found!</p>";
        exit;
    }
    
    $customerId = $customer['id'];
    echo "<div class='section'>";
    echo "<h2>✓ Customer Found</h2>";
    echo "<p><strong>ID:</strong> {$customerId}</p>";
    echo "<p><strong>Name:</strong> {$customer['customer_name']}</p>";
    echo "<p><strong>Certificate No:</strong> <span style='background:yellow;padding:5px;font-weight:bold;'>" . 
         (!empty($customer['certification_no']) ? htmlspecialchars($customer['certification_no']) : '(EMPTY - THIS IS THE PROBLEM!)') . 
         "</span></p>";
    echo "<p><strong>Certification Stage Date:</strong> {$customer['certification_stage_date']}</p>";
    echo "<p><strong>Audit Status:</strong> {$customer['audit_status']}</p>";
    echo "</div>";
    
    // Get all audit schedules
    echo "<div class='section'>";
    echo "<h2>Audit Schedule Records</h2>";
    $sql2 = "SELECT 
                audit_schedule_id,
                serviceca_id,
                schedule_app_standard,
                ref_no,
                schedule_audit_date
             FROM audit_schedule_id 
             WHERE customer_ca_id = :custId
             ORDER BY audit_schedule_id DESC";
    $stmt2 = $db->prepare($sql2);
    $stmt2->bindParam(':custId', $customerId);
    $stmt2->execute();
    $schedules = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    if ($schedules) {
        echo "<p class='success'>Found " . count($schedules) . " audit schedule(s)</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Service ID</th><th>Standard(s)</th><th>Ref No</th><th>Split Standards</th></tr>";
        foreach ($schedules as $sch) {
            $standards = $sch['schedule_app_standard'];
            $splitStandards = array_map('trim', explode(',', $standards));
            
            echo "<tr>";
            echo "<td>{$sch['audit_schedule_id']}</td>";
            echo "<td>{$sch['serviceca_id']}</td>";
            echo "<td style='background:#fff9c4;'><strong>" . htmlspecialchars($standards) . "</strong></td>";
            echo "<td>{$sch['ref_no']}</td>";
            echo "<td>";
            foreach ($splitStandards as $idx => $std) {
                echo "<span style='display:block;padding:3px;background:" . ($idx % 2 == 0 ? '#e3f2fd' : '#f3e5f5') . ";margin:2px;'>";
                echo ($idx + 1) . ". " . htmlspecialchars($std);
                echo "</span>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>No audit schedules found</p>";
    }
    echo "</div>";
    
    // Get CDC report review data - Check for certificate numbers
    echo "<div class='section'>";
    echo "<h2>CDC Report Review Records (Certificate Numbers)</h2>";
    
    // First, try to find by service IDs from audit schedule
    $serviceIds = array();
    foreach ($schedules as $sch) {
        if (!empty($sch['serviceca_id'])) {
            $serviceIds[] = $sch['serviceca_id'];
        }
    }
    
    if (!empty($serviceIds)) {
        echo "<p><strong>Looking for CDC records with service IDs:</strong> " . implode(', ', $serviceIds) . "</p>";
        
        $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
        $sql3 = "SELECT 
                    crr_id,
                    ca_service_id,
                    certificateNo,
                    revDatecdc,
                    expiredate,
                    Date as created_date
                 FROM cdc_report_review 
                 WHERE ca_service_id IN ($placeholders)
                 ORDER BY Date DESC";
        $stmt3 = $db->prepare($sql3);
        $stmt3->execute($serviceIds);
        $cdcRecords = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        
        if ($cdcRecords) {
            echo "<p class='success'>✓ Found " . count($cdcRecords) . " CDC record(s) matching service IDs</p>";
            echo "<table>";
            echo "<tr><th>CDC ID</th><th>Service ID</th><th>Certificate No (OLD)</th><th>CDC Status</th><th>Decision Date</th><th>Issue Date</th><th>Expire Date</th></tr>";
            foreach ($cdcRecords as $cdc) {
                $hasValue = !empty(trim($cdc['certificateNo']));
                $certHighlight = $hasValue ? 'background:#90EE90;font-weight:bold;' : 'background:#ffcccc;';
                
                // Get cdc_status for this record
                $statusQuery = "SELECT cdc_status, Date FROM cdc_report_review WHERE crr_id = :crrId";
                $statusStmt = $db->prepare($statusQuery);
                $statusStmt->bindParam(':crrId', $cdc['crr_id']);
                $statusStmt->execute();
                $statusData = $statusStmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<tr>";
                echo "<td>{$cdc['crr_id']}</td>";
                echo "<td><strong>{$cdc['ca_service_id']}</strong></td>";
                echo "<td style='{$certHighlight}'>" . 
                     ($hasValue ? '<strong>' . htmlspecialchars($cdc['certificateNo']) . '</strong>' : '(EMPTY - Use office_use table)') . 
                     "</td>";
                echo "<td style='background:" . (!empty($statusData['cdc_status']) ? '#90EE90' : '#ffcccc') . ";font-weight:bold;'>" . 
                     (!empty($statusData['cdc_status']) ? htmlspecialchars($statusData['cdc_status']) : '(empty)') . "</td>";
                echo "<td>" . (!empty($statusData['Date']) ? $statusData['Date'] : '(empty)') . "</td>";
                echo "<td>" . (!empty($cdc['revDatecdc']) ? $cdc['revDatecdc'] : '(empty)') . "</td>";
                echo "<td>" . (!empty($cdc['expiredate']) ? $cdc['expiredate'] : '(empty)') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p style='margin-top:10px;'><strong>Note:</strong> The <code>cdc_status</code> and <code>Date</code> fields are <strong>ISO-specific</strong> and should differ for each ISO standard.</p>";
        } else {
            echo "<p class='error'>✗ No CDC records found for service IDs: " . implode(', ', $serviceIds) . "</p>";
            echo "<p>This means no certificates have been generated in the 'For Office Use' section yet.</p>";
        }
    } else {
        echo "<p class='error'>✗ No service IDs found in audit schedule</p>";
    }
    
    // Also check service_type_ca table for ALL services
    echo "<h3>All Services for this Customer (service_type_ca table)</h3>";
    $sql4 = "SELECT ca_id, service, customer_id FROM service_type_ca WHERE customer_id = :custId";
    $stmt4 = $db->prepare($sql4);
    $stmt4->bindParam(':custId', $customerId);
    $stmt4->execute();
    $services = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    
    if ($services) {
        echo "<p>Found " . count($services) . " service record(s) in service_type_ca:</p>";
        echo "<table>";
        echo "<tr><th>CA ID</th><th>Service/Standard</th></tr>";
        foreach ($services as $svc) {
            echo "<tr><td><strong>{$svc['ca_id']}</strong></td><td>" . htmlspecialchars($svc['service']) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No services found in service_type_ca table</p>";
    }
    
    // Check service_ca table for ALL service records
    echo "<h3>All Service Records (service_ca table)</h3>";
    $sql5 = "SELECT service_id, serviceca_id, customer_id FROM service_ca WHERE customer_id = :custId";
    $stmt5 = $db->prepare($sql5);
    $stmt5->bindParam(':custId', $customerId);
    $stmt5->execute();
    $serviceRecords = $stmt5->fetchAll(PDO::FETCH_ASSOC);
    
    if ($serviceRecords) {
        echo "<p>Found " . count($serviceRecords) . " service record(s) in service_ca:</p>";
        echo "<table>";
        echo "<tr><th>Service ID</th><th>Service CA ID</th><th>Has CDC Record?</th></tr>";
        foreach ($serviceRecords as $svcRec) {
            // Check if CDC record exists
            $cdcCheck = "SELECT COUNT(*) as cnt FROM cdc_report_review WHERE ca_service_id = :svcId";
            $cdcStmt = $db->prepare($cdcCheck);
            $cdcStmt->bindParam(':svcId', $svcRec['serviceca_id']);
            $cdcStmt->execute();
            $cdcCount = $cdcStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
            
            echo "<tr>";
            echo "<td><strong>{$svcRec['service_id']}</strong></td>";
            echo "<td><strong>{$svcRec['serviceca_id']}</strong></td>";
            echo "<td>" . ($cdcCount > 0 ? "<span style='color:green'>Yes ({$cdcCount})</span>" : "<span style='color:red'>No</span>") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No service records found in service_ca table</p>";
    }
    
    echo "</div>";
    
    // Check NEW cdc_report_review_office_use table
    echo "<div class='section'>";
    echo "<h2>NEW Office Use Table (cdc_report_review_office_use)</h2>";
    echo "<p>This table stores separate certificate numbers for each ISO standard.</p>";
    
    if (!empty($serviceIds)) {
        $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
        
        // First get all crr_ids
        $crrQuery = "SELECT crr_id, ca_service_id FROM cdc_report_review WHERE ca_service_id IN ($placeholders)";
        $crrStmt = $db->prepare($crrQuery);
        $crrStmt->execute($serviceIds);
        $crrRecords = $crrStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($crrRecords) {
            echo "<p><strong>Found " . count($crrRecords) . " CDC record(s) with crr_id:</strong></p>";
            
            $allCrrIds = array_column($crrRecords, 'crr_id');
            $crrPlaceholders = implode(',', array_fill(0, count($allCrrIds), '?'));
            
            $officeQuery = "SELECT id, crr_id, iso_index, iso_label, certificate_no, rev_date, expire_date 
                           FROM cdc_report_review_office_use 
                           WHERE crr_id IN ($crrPlaceholders)
                           ORDER BY crr_id, iso_index";
            $officeStmt = $db->prepare($officeQuery);
            $officeStmt->execute($allCrrIds);
            $officeRecords = $officeStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($officeRecords) {
                echo "<p class='success'>✓ Found " . count($officeRecords) . " certificate(s) in office_use table!</p>";
                echo "<table>";
                echo "<tr><th>ID</th><th>CDC ID</th><th>ISO Index</th><th>ISO Label</th><th>Certificate No</th><th>Rev Date</th><th>Expire Date</th></tr>";
                foreach ($officeRecords as $rec) {
                    $hasCert = !empty(trim($rec['certificate_no']));
                    $highlight = $hasCert ? 'background:#90EE90;font-weight:bold;' : 'background:#ffcccc;';
                    echo "<tr>";
                    echo "<td>{$rec['id']}</td>";
                    echo "<td><strong>{$rec['crr_id']}</strong></td>";
                    echo "<td>{$rec['iso_index']}</td>";
                    echo "<td style='background:#fff9c4;'><strong>" . htmlspecialchars($rec['iso_label']) . "</strong></td>";
                    echo "<td style='{$highlight}'>" . ($hasCert ? htmlspecialchars($rec['certificate_no']) : '(empty)') . "</td>";
                    echo "<td>" . ($rec['rev_date'] ?? '(empty)') . "</td>";
                    echo "<td>" . ($rec['expire_date'] ?? '(empty)') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "<p style='color:green;font-weight:bold;'>✓ THIS IS THE CORRECT TABLE TO USE FOR MULTI-ISO CLIENTS!</p>";
            } else {
                echo "<p class='error'>✗ No records found in office_use table</p>";
                echo "<p>The certificate numbers need to be entered in the 'For Office Use' section of CDC Report.</p>";
            }
        } else {
            echo "<p class='error'>No CDC records found with crr_id</p>";
        }
    }
    
    echo "</div>";
    
    // Simulate the page logic
    echo "<div class='section'>";
    echo "<h2>Simulated Certificate Display</h2>";
    
    $allCertificates = array();
    
    foreach ($schedules as $standard) {
        $standardNames = array_map('trim', explode(',', $standard['schedule_app_standard']));
        
        foreach ($standardNames as $singleStandard) {
            if (empty($singleStandard)) continue;
            
            $certInfo = array();
            $certInfo['standard_name'] = $singleStandard;
            $certInfo['serviceca_id'] = $standard['serviceca_id'];
            $certInfo['certification_no'] = '';
            $certInfo['cdc_status'] = '';
            $certInfo['decision_date'] = '';
            
            // SAME LOGIC AS customerAccount.php
            // FIRST: Try NEW cdc_report_review_office_use table
            if (!empty($standard['serviceca_id'])) {
                // Get the crr_id and ISO-specific status/dates for this service
                $crrQuery = "SELECT crr_id, cdc_status, Date as decision_date FROM cdc_report_review WHERE ca_service_id = :svcId ORDER BY Date DESC LIMIT 1";
                $crrStmt = $db->prepare($crrQuery);
                $crrStmt->bindParam(':svcId', $standard['serviceca_id']);
                $crrStmt->execute();
                $crrData = $crrStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($crrData && !empty($crrData['crr_id'])) {
                    echo "<p style='color:blue;'>→ Found crr_id: {$crrData['crr_id']} for service_id: {$standard['serviceca_id']}</p>";
                    
                    // Store ISO-specific status and decision date
                    if (!empty($crrData['cdc_status'])) {
                        $certInfo['cdc_status'] = $crrData['cdc_status'];
                        echo "<p style='color:blue;'>→ CDC Status for {$singleStandard}: <strong>{$crrData['cdc_status']}</strong></p>";
                    }
                    if (!empty($crrData['decision_date'])) {
                        $certInfo['decision_date'] = $crrData['decision_date'];
                        echo "<p style='color:blue;'>→ Decision Date for {$singleStandard}: <strong>{$crrData['decision_date']}</strong></p>";
                    }
                    
                    // Try to get certificate from the NEW office_use table by matching ISO label
                    $officeUseQuery = "SELECT certificate_no, rev_date, expire_date 
                                      FROM cdc_report_review_office_use 
                                      WHERE crr_id = :crrId 
                                      AND iso_label LIKE :isoLabel
                                      AND certificate_no IS NOT NULL 
                                      AND certificate_no != ''
                                      ORDER BY iso_index ASC LIMIT 1";
                    $officeStmt = $db->prepare($officeUseQuery);
                    $officeStmt->bindParam(':crrId', $crrData['crr_id']);
                    $isoLikePattern = '%' . $singleStandard . '%';
                    $officeStmt->bindParam(':isoLabel', $isoLikePattern);
                    $officeStmt->execute();
                    $officeData = $officeStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($officeData && !empty(trim($officeData['certificate_no']))) {
                        $certInfo['certification_no'] = trim($officeData['certificate_no']);
                        echo "<p style='color:green;'>✓ Found cert number in office_use table for {$singleStandard}: <strong>" . htmlspecialchars($certInfo['certification_no']) . "</strong></p>";
                    } else {
                        echo "<p style='color:orange;'>⚠ No cert number in office_use table for {$singleStandard} (searching for ISO label like '{$isoLikePattern}')</p>";
                    }
                } else {
                    echo "<p style='color:orange;'>⚠ No crr_id found for service_id: {$standard['serviceca_id']}</p>";
                }
                
                // FALLBACK to old CDC table if not found
                if (empty($certInfo['certification_no'])) {
                    $cdcQuery = "SELECT certificateNo, revDatecdc, expiredate 
                                 FROM cdc_report_review 
                                 WHERE ca_service_id = :svcId 
                                 AND certificateNo IS NOT NULL 
                                 AND certificateNo != ''
                                 ORDER BY Date DESC LIMIT 1";
                    $cdcStmt = $db->prepare($cdcQuery);
                    $cdcStmt->bindParam(':svcId', $standard['serviceca_id']);
                    $cdcStmt->execute();
                    $cdcData = $cdcStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($cdcData && !empty(trim($cdcData['certificateNo']))) {
                        $certInfo['certification_no'] = trim($cdcData['certificateNo']);
                        echo "<p style='color:green;'>✓ Found cert number in OLD CDC table for {$singleStandard}: <strong>" . htmlspecialchars($certInfo['certification_no']) . "</strong></p>";
                    }
                }
            }
            
            // FALLBACK: Use customer_info.certification_no
            if (empty($certInfo['certification_no']) && !empty($customer['certification_no'])) {
                $certInfo['certification_no'] = $customer['certification_no'];
                echo "<p style='color:blue;'>→ Using fallback cert number from customer_info for {$singleStandard}: <strong>" . htmlspecialchars($certInfo['certification_no']) . "</strong></p>";
            }
            
            if (empty($certInfo['certification_no'])) {
                echo "<p style='color:red;'>✗ No certificate number found for {$singleStandard}</p>";
            }
            
            $certInfo['issue_date'] = isset($customer['certification_stage_date']) ? $customer['certification_stage_date'] : '';
            $certInfo['expire_date'] = '';
            
            $allCertificates[] = $certInfo;
        }
    }
    
    // Remove duplicates
    $uniqueCertificates = array();
    $seenStandards = array();
    foreach ($allCertificates as $cert) {
        $key = $cert['standard_name'];
        if (!isset($seenStandards[$key])) {
            $uniqueCertificates[] = $cert;
            $seenStandards[$key] = true;
        }
    }
    $allCertificates = $uniqueCertificates;
    
    echo "<p class='success'><strong>Total Unique Standards: " . count($allCertificates) . "</strong></p>";
    
    $certCount = count($allCertificates);
    for ($i = 0; $i < $certCount; $i++) {
        $cert = $allCertificates[$i];
        
        if ($certCount > 1) {
            echo "<div class='header'>";
            echo "<i class='fa fa-certificate'></i> ISO STANDARD " . ($i + 1) . " OF " . $certCount . " - " . htmlspecialchars($cert['standard_name']);
            echo "</div>";
        }
        
        $certStatus = !empty($cert['cdc_status']) ? $cert['cdc_status'] : '(not set)';
        $decisionDate = !empty($cert['decision_date']) ? $cert['decision_date'] : '(not set)';
        
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th><th>Source</th></tr>";
        echo "<tr><td><strong>Certificate No</strong></td><td>" . htmlspecialchars($cert['certification_no'] ?? '(empty)') . "</td><td>office_use table</td></tr>";
        echo "<tr><td><strong>Standard Name</strong></td><td style='background:#90EE90;font-weight:bold;'>" . htmlspecialchars($cert['standard_name']) . "</td><td>audit_schedule_id</td></tr>";
        echo "<tr><td><strong>Certification Status</strong></td><td style='background:" . (!empty($cert['cdc_status']) ? '#90EE90' : '#ffcccc') . ";font-weight:bold;'>" . htmlspecialchars($certStatus) . "</td><td>cdc_report_review.cdc_status</td></tr>";
        echo "<tr><td><strong>Decision Date</strong></td><td style='background:" . (!empty($cert['decision_date']) ? '#90EE90' : '#ffcccc') . ";'>" . htmlspecialchars($decisionDate) . "</td><td>cdc_report_review.Date</td></tr>";
        echo "<tr><td><strong>Issue Date</strong></td><td>" . htmlspecialchars($cert['issue_date']) . "</td><td>office_use.rev_date</td></tr>";
        echo "<tr><td><strong>Expire Date</strong></td><td>" . htmlspecialchars($cert['expire_date']) . "</td><td>office_use.expire_date</td></tr>";
        echo "<tr><td><strong>Service ID</strong></td><td>" . htmlspecialchars($cert['serviceca_id']) . "</td><td>audit_schedule_id</td></tr>";
        echo "</table>";
        
        if ($i < $certCount - 1) {
            echo "<div style='margin:20px 0;border-bottom:3px double #999;'></div>";
        }
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='error'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><p><small>Debug completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>

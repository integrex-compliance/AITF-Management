<?php
include_once './dbConnect.php';

$certNo = '665818121416'; // The certificate number from your screenshot

echo "<h1>Debug Certificate: $certNo</h1>";
echo "<hr>";

// Test 1: What's in audit_application?
echo "<h2>1. Check audit_application table - CERTIFICATION FIELD</h2>";
$sql1 = "SELECT aa.audit_id, aa.service_id, aa.certification, aa.accereditation_requirement 
         FROM audit_application aa
         INNER JOIN cdc_report_review crr ON aa.service_id = crr.ca_service_id
         WHERE crr.certificateNo = '$certNo'
         LIMIT 5";
$result1 = $db->query($sql1);
$rows1 = $result1->fetchAll(PDO::FETCH_ASSOC);

if ($rows1) {
    echo "<table border='1' cellpadding='5' style='width:100%'>";
    echo "<tr><th>audit_id</th><th>service_id</th><th>certification (Should be ISO Standard)</th><th>Contains 'ISO'?</th><th>accreditation</th></tr>";
    foreach ($rows1 as $row) {
        $containsISO = (stripos($row['certification'], 'ISO') !== false);
        $colorCert = $containsISO ? 'green' : 'red';
        $checkMark = $containsISO ? '✓ YES' : '✗ NO (WRONG!)';
        $checkColor = $containsISO ? 'green' : 'red';
        
        echo "<tr>";
        echo "<td>{$row['audit_id']}</td>";
        echo "<td>{$row['service_id']}</td>";
        echo "<td><strong style='color:$colorCert'>{$row['certification']}</strong></td>";
        echo "<td style='color:$checkColor; font-weight:bold'>$checkMark</td>";
        echo "<td>{$row['accereditation_requirement']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show warning if certification doesn't contain ISO
    foreach ($rows1 as $row) {
        if (stripos($row['certification'], 'ISO') === false) {
            echo "<div style='background:#ffcccc; padding:15px; margin:10px 0; border:2px solid red;'>";
            echo "<h3 style='color:red'>⚠️ PROBLEM FOUND!</h3>";
            echo "<p><strong>The 'certification' field contains:</strong> <code>{$row['certification']}</code></p>";
            echo "<p><strong>This looks like NACE codes, NOT an ISO standard!</strong></p>";
            echo "<p><strong>SOLUTION:</strong> The ISO standard (ISO 9001:2015, ISO 14001:2015, etc.) should be stored in the <code>certification</code> field of the <code>audit_application</code> table.</p>";
            echo "<p>Currently it's storing: {$row['certification']} (which appears to be technical area/NACE codes)</p>";
            echo "</div>";
            break;
        }
    }
} else {
    echo "<p style='color:red'>NO DATA in audit_application for this certificate</p>";
}

// Test 2: What's in cdc_report_review?
echo "<h2>2. Check cdc_report_review table</h2>";
$sql2 = "SELECT crr_id, ca_service_id, certificateNo, product_service, revDatecdc, expiredate 
         FROM cdc_report_review 
         WHERE certificateNo = '$certNo'
         ORDER BY Date DESC
         LIMIT 3";
$result2 = $db->query($sql2);
$rows2 = $result2->fetchAll(PDO::FETCH_ASSOC);

if ($rows2) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>crr_id</th><th>ca_service_id</th><th>certificateNo</th><th>product_service</th><th>revDatecdc</th><th>expiredate</th></tr>";
    foreach ($rows2 as $row) {
        echo "<tr>";
        echo "<td>{$row['crr_id']}</td>";
        echo "<td>{$row['ca_service_id']}</td>";
        echo "<td>{$row['certificateNo']}</td>";
        echo "<td>{$row['product_service']}</td>";
        echo "<td>{$row['revDatecdc']}</td>";
        echo "<td>{$row['expiredate']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>NO DATA in cdc_report_review for this certificate</p>";
}

// Test 3: The JOIN query we're using
echo "<h2>3. The JOIN query (what the code is actually running)</h2>";
$sql3 = "SELECT 
            aa.certification as standard_name,
            crr.revDatecdc as issue_date,
            crr.expiredate as expire_date,
            crr.ca_service_id
          FROM cdc_report_review crr
          LEFT JOIN audit_application aa ON crr.ca_service_id = aa.service_id
          WHERE crr.certificateNo = '$certNo' 
          ORDER BY crr.Date DESC LIMIT 1";
$result3 = $db->query($sql3);
$row3 = $result3->fetch(PDO::FETCH_ASSOC);

if ($row3) {
    echo "<div style='background:#e0ffe0; padding:10px; margin:10px 0;'>";
    echo "<strong>Standard Name:</strong> <span style='color:" . (empty($row3['standard_name']) ? 'red' : 'green') . "; font-size:18px'>" . 
         ($row3['standard_name'] ?: 'EMPTY/NULL') . "</span><br>";
    echo "<strong>Issue Date:</strong> " . ($row3['issue_date'] ?: 'EMPTY') . "<br>";
    echo "<strong>Expire Date:</strong> " . ($row3['expire_date'] ?: 'EMPTY') . "<br>";
    echo "<strong>Service ID:</strong> " . ($row3['ca_service_id'] ?: 'EMPTY') . "<br>";
    echo "</div>";
    
    // If standard_name is empty, try direct query
    if (empty($row3['standard_name']) && !empty($row3['ca_service_id'])) {
        echo "<h3>4. Direct query to audit_application using service_id: {$row3['ca_service_id']}</h3>";
        $sql4 = "SELECT certification FROM audit_application WHERE service_id = '{$row3['ca_service_id']}'";
        $result4 = $db->query($sql4);
        $row4 = $result4->fetch(PDO::FETCH_ASSOC);
        
        if ($row4) {
            echo "<div style='background:#ffffcc; padding:10px;'>";
            echo "<strong>Certification from direct query:</strong> <span style='color:green; font-size:18px'>" . 
                 ($row4['certification'] ?: 'EMPTY') . "</span>";
            echo "</div>";
        } else {
            echo "<p style='color:red'>No audit_application record found for service_id: {$row3['ca_service_id']}</p>";
        }
    }
} else {
    echo "<p style='color:red'>JOIN query returned NO DATA</p>";
}

// Test 4: Check customer_info
echo "<h2>5. Check customer_info table</h2>";
$sql5 = "SELECT id, customer_name, certification_no, aif_nace FROM customer_info WHERE certification_no = '$certNo'";
$result5 = $db->query($sql5);
$row5 = $result5->fetch(PDO::FETCH_ASSOC);

if ($row5) {
    echo "<div style='background:#e0e0ff; padding:10px;'>";
    echo "<strong>Customer ID:</strong> {$row5['id']}<br>";
    echo "<strong>Customer Name:</strong> {$row5['customer_name']}<br>";
    echo "<strong>Certificate No:</strong> {$row5['certification_no']}<br>";
    echo "<strong>Technical Area (aif_nace):</strong> <span style='color:blue'>" . ($row5['aif_nace'] ?: 'EMPTY') . "</span><br>";
    echo "</div>";
} else {
    echo "<p style='color:red'>Customer not found</p>";
}

?>
<hr>
<h2>Summary</h2>
<p>Open this page in your browser to see where the ISO standard data is stored.</p>
<p>URL: <code>http://localhost/AITF_PROJECT/backup2/public_html/debug_cert.php</code></p>

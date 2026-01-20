<?php
include_once './dbConnect.php';

// Check for certificate 133174214444
$certNo = '133174214444';

echo "<h1>Finding ISO Standard for Certificate: $certNo</h1>";
echo "<style>
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
    .found { background-color: #dff0d8; font-weight: bold; }
    .empty { color: #999; font-style: italic; }
    .error { color: red; font-weight: bold; }
</style>";

// Get customer_id first
$sqlCustomer = "SELECT customer_id, company_name FROM customer_info WHERE certification_no = :certNo";
$stmt = $db->prepare($sqlCustomer);
$stmt->bindParam(':certNo', $certNo);
$stmt->execute();
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    die("<p class='error'>Certificate not found in customer_info table!</p>");
}

echo "<h2>Customer: " . htmlspecialchars($customer['company_name']) . " (ID: " . $customer['customer_id'] . ")</h2>";

// METHOD 1: Check all service records for this customer
echo "<h3>Method 1: Checking ALL Services for this Customer</h3>";
$sqlServices = "SELECT service_id, service_type, service_status FROM ca_services WHERE customer_id = :custId ORDER BY service_id DESC";
$stmt = $db->prepare($sqlServices);
$stmt->bindParam(':custId', $customer['customer_id']);
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table>";
echo "<tr><th>Service ID</th><th>Service Type</th><th>Status</th><th>Certification</th><th>Assessment Standard</th></tr>";

$foundStandard = null;
foreach ($services as $service) {
    $sqlAudit = "SELECT certification, assessment_standard FROM audit_application WHERE service_id = :svcId";
    $stmtAudit = $db->prepare($sqlAudit);
    $stmtAudit->bindParam(':svcId', $service['service_id']);
    $stmtAudit->execute();
    $auditData = $stmtAudit->fetch(PDO::FETCH_ASSOC);
    
    $cert = $auditData['certification'] ?? '';
    $assess = $auditData['assessment_standard'] ?? '';
    
    $certDisplay = $cert ? htmlspecialchars($cert) : '<span class="empty">Empty</span>';
    $assessDisplay = $assess ? htmlspecialchars($assess) : '<span class="empty">Empty</span>';
    
    $rowClass = '';
    if ($assess && stripos($assess, 'ISO') !== false) {
        $rowClass = 'class="found"';
        if (!$foundStandard) $foundStandard = $assess;
    } elseif ($cert && stripos($cert, 'ISO') !== false) {
        $rowClass = 'class="found"';
        if (!$foundStandard) $foundStandard = $cert;
    }
    
    echo "<tr $rowClass>";
    echo "<td>" . $service['service_id'] . "</td>";
    echo "<td>" . htmlspecialchars($service['service_type']) . "</td>";
    echo "<td>" . htmlspecialchars($service['service_status']) . "</td>";
    echo "<td>" . $certDisplay . "</td>";
    echo "<td>" . $assessDisplay . "</td>";
    echo "</tr>";
}
echo "</table>";

if ($foundStandard) {
    echo "<p class='found' style='font-size: 18px; padding: 15px;'>✓ ISO STANDARD FOUND: " . htmlspecialchars($foundStandard) . "</p>";
} else {
    echo "<p class='error'>✗ No ISO standard found in any service</p>";
}

// METHOD 2: Check CDC Report Review
echo "<h3>Method 2: CDC Report Review Table</h3>";
$sqlCdc = "SELECT ca_service_id, revDatecdc, expiredate FROM cdc_report_review WHERE certificateNo = :certNo ORDER BY Date DESC LIMIT 1";
$stmt = $db->prepare($sqlCdc);
$stmt->bindParam(':certNo', $certNo);
$stmt->execute();
$cdcData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cdcData) {
    echo "<p>Service ID from CDC: <strong>" . $cdcData['ca_service_id'] . "</strong></p>";
    
    // Get audit application for this service
    $sqlAudit = "SELECT certification, assessment_standard FROM audit_application WHERE service_id = :svcId";
    $stmt = $db->prepare($sqlAudit);
    $stmt->bindParam(':svcId', $cdcData['ca_service_id']);
    $stmt->execute();
    $auditData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($auditData) {
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>Certification</td><td>" . ($auditData['certification'] ? htmlspecialchars($auditData['certification']) : '<span class="empty">Empty</span>') . "</td></tr>";
        echo "<tr><td>Assessment Standard</td><td>" . ($auditData['assessment_standard'] ? htmlspecialchars($auditData['assessment_standard']) : '<span class="empty">Empty</span>') . "</td></tr>";
        echo "</table>";
    }
} else {
    echo "<p class='error'>No CDC report found for this certificate</p>";
}

// METHOD 3: Check Certificate Print
echo "<h3>Method 3: Certificate Print Table</h3>";
$sqlPrint = "SELECT certificate_scope FROM certificate_print WHERE certificate_no = :certNo";
$stmt = $db->prepare($sqlPrint);
$stmt->bindParam(':certNo', $certNo);
$stmt->execute();
$printData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($printData && $printData['certificate_scope']) {
    echo "<p>Certificate Scope: <strong>" . htmlspecialchars($printData['certificate_scope']) . "</strong></p>";
} else {
    echo "<p class='error'>No certificate_print record found</p>";
}

// METHOD 4: Check Assessment Planning
echo "<h3>Method 4: Assessment Planning Table</h3>";
$sqlAssess = "SELECT cert_standard, assessment_standard FROM assessment_planning WHERE customer_id = :custId ORDER BY assess_id DESC LIMIT 1";
$stmt = $db->prepare($sqlAssess);
$stmt->bindParam(':custId', $customer['customer_id']);
$stmt->execute();
$assessData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($assessData) {
    echo "<table>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>Cert Standard</td><td>" . ($assessData['cert_standard'] ? htmlspecialchars($assessData['cert_standard']) : '<span class="empty">Empty</span>') . "</td></tr>";
    echo "<tr><td>Assessment Standard</td><td>" . ($assessData['assessment_standard'] ? htmlspecialchars($assessData['assessment_standard']) : '<span class="empty">Empty</span>') . "</td></tr>";
    echo "</table>";
} else {
    echo "<p class='error'>No assessment planning found</p>";
}

echo "<hr>";
echo "<h2>RECOMMENDATION</h2>";
if ($foundStandard) {
    echo "<p style='background: #dff0d8; padding: 20px; font-size: 16px;'>";
    echo "The ISO standard <strong>" . htmlspecialchars($foundStandard) . "</strong> should be displayed in the Standard Name field.";
    echo "</p>";
} else {
    echo "<p style='background: #f2dede; padding: 20px; font-size: 16px;'>";
    echo "No ISO standard found in the database for this certificate. Please check if the data was entered during the audit application process.";
    echo "</p>";
}

?>

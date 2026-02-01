<?php
include_once './dbConnect.php';

echo "Populating service data from aitf_web to varkly_web...<br>";

// Connect to source database (aitf_web)
try {
    $source_conn = new PDO('mysql:host=localhost;dbname=aitf_web;charset=utf8', 'root', '');
    $source_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to source database (aitf_web)<br>";
} catch (Exception $e) {
    die("Failed to connect to source database: " . $e->getMessage());
}

// Tables to copy (non-client data)
$tables_to_copy = [
    'service',
    'service_types', 
    'service_type_ca',
    'service_type_cr',
    'service_type_ptr',
    'service_type_rc',
    'service_type_sai',
    'service_type_saii',
    'service_type_sp',
    'service_type_ta',
    'standards',
    'categories',
    'accreditation',
    'iaf_codes',
    'nace_codes',
    'nace2_classes',
    'legal_ststus',
    'customer_status',
    'service_add_to',
    'taxation',
    'auditors',
    'handler',
    'consulting_firm',
    'firm_abbrevation',
    'firm_services'
];

foreach ($tables_to_copy as $table) {
    echo "<br>Processing table: $table<br>";
    
    try {
        // Get data from source
        $stmt = $source_conn->query("SELECT * FROM $table");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($data)) {
            echo "No data found in $table<br>";
            continue;
        }
        
        // Clear target table
        $db->query("DELETE FROM $table");
        echo "Cleared existing data from $table<br>";
        
        // Get column names
        $columns = array_keys($data[0]);
        $column_list = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        
        // Insert data
        $insert_stmt = $db->prepare("INSERT INTO $table ($column_list) VALUES ($placeholders)");
        
        $inserted_count = 0;
        foreach ($data as $row) {
            $values = array_values($row);
            if ($insert_stmt->execute($values)) {
                $inserted_count++;
            }
        }
        
        echo "✓ Inserted $inserted_count rows into $table<br>";
        
    } catch (Exception $e) {
        echo "✗ Error processing $table: " . $e->getMessage() . "<br>";
    }
}

echo "<br><strong>Data population completed!</strong><br>";

// Verify the data
echo "<br>Verification:<br>";
$verification_tables = ['service', 'service_types', 'standards', 'categories', 'accreditation'];

foreach ($verification_tables as $table) {
    try {
        $count = $db->query("SELECT COUNT(*) as count FROM $table")->fetch();
        echo "$table: " . $count['count'] . " rows<br>";
    } catch (Exception $e) {
        echo "$table: Error - " . $e->getMessage() . "<br>";
    }
}

$source_conn = null;
?>

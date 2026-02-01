<?php
include_once './dbConnect.php';

echo "Checking service data in varkly_web database...<br>";

try {
    // Check service table
    $service_count = $db->query("SELECT COUNT(*) as count FROM service")->fetch();
    echo "Service table: " . $service_count['count'] . " rows<br>";
    
    // Check service_types table
    $service_types_count = $db->query("SELECT COUNT(*) as count FROM service_types")->fetch();
    echo "Service types table: " . $service_types_count['count'] . " rows<br>";
    
    // Check all service type tables
    $service_type_tables = [
        'service_type_ca', 'service_type_cr', 'service_type_ptr', 
        'service_type_rc', 'service_type_sai', 'service_type_saii', 
        'service_type_sp', 'service_type_ta'
    ];
    
    echo "<br>Service type specific tables:<br>";
    foreach ($service_type_tables as $table) {
        try {
            $count = $db->query("SELECT COUNT(*) as count FROM $table")->fetch();
            echo "$table: " . $count['count'] . " rows<br>";
        } catch (Exception $e) {
            echo "$table: Error - " . $e->getMessage() . "<br>";
        }
    }
    
    // Check other related tables
    echo "<br>Other related tables:<br>";
    $related_tables = ['standards', 'categories', 'accreditation', 'iaf_codes', 'nace_codes'];
    
    foreach ($related_tables as $table) {
        try {
            $count = $db->query("SELECT COUNT(*) as count FROM $table")->fetch();
            echo "$table: " . $count['count'] . " rows<br>";
        } catch (Exception $e) {
            echo "$table: Error - " . $e->getMessage() . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

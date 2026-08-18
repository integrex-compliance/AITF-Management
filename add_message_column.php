<?php
require 'dbConnect.php';
$tables = ['online_audit_applications', 'information_updation_forms', 'nc_closing_forms', 'job_applications'];

foreach ($tables as $t) {
    try {
        $db->exec("ALTER TABLE $t ADD COLUMN admin_assign_message TEXT NULL");
        echo "Added column to $t\n";
    } catch (PDOException $e) {
        echo "Skipped $t: " . $e->getMessage() . "\n";
    }
}

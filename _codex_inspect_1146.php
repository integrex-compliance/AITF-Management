<?php

require __DIR__ . '/dbConnect.php';

$customerId = 1146;
$queries = array(
    'customer' => "SELECT id, customer_name, certification_no, audit_status FROM customer_info WHERE id = $customerId",
    'services' => "SELECT service_id, service_type, audit_date FROM service WHERE customer_id = $customerId ORDER BY service_id DESC",
    'schedules' => "SELECT audit_schedule_id, customer_ca_id, serviceca_id, schedule_app_standard, ref_no FROM audit_schedule_id WHERE customer_ca_id = $customerId ORDER BY audit_schedule_id DESC",
    'cdc' => "SELECT crr_id, ca_service_id, customer_id, covered_for, accreditation, cdc_status, certificateNo, Date FROM cdc_report_review WHERE customer_id = $customerId ORDER BY crr_id DESC",
    'office_use' => "SELECT cou.id, cou.crr_id, cou.iso_index, cou.iso_label, cou.certificate_no, cou.rev_date, cou.expire_date FROM cdc_report_review_office_use cou INNER JOIN cdc_report_review crr ON crr.crr_id = cou.crr_id WHERE crr.customer_id = $customerId ORDER BY cou.crr_id DESC, cou.iso_index ASC, cou.id ASC",
    'metadata' => "SELECT cim.id, cim.crr_id, cim.iso_index, cim.iso_label, cim.accreditation, cim.certification_status FROM cdc_iso_metadata cim INNER JOIN cdc_report_review crr ON crr.crr_id = cim.crr_id WHERE crr.customer_id = $customerId ORDER BY cim.crr_id DESC, cim.iso_index ASC",
);

foreach ($queries as $label => $sql) {
    echo "=== $label ===", PHP_EOL;
    foreach ($db->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo json_encode($row, JSON_UNESCAPED_SLASHES), PHP_EOL;
    }
}

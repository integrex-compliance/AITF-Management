<?php

require __DIR__ . '/dbConnect.php';
require __DIR__ . '/include/cdc_legacy_iso_fallback.php';

$customerId = 1146;
$queries = array(
    'customer' => "SELECT id, customer_name, certification_no, comment, audit_status FROM customer_info WHERE id = $customerId",
    'services' => "SELECT service_id, service_type, audit_date FROM service WHERE customer_id = $customerId ORDER BY service_id DESC",
    'ca_services' => "SELECT ca_id, service, customer_id FROM service_type_ca WHERE customer_id = $customerId ORDER BY ca_id DESC",
    'schedules' => "SELECT audit_schedule_id, serviceca_id, schedule_app_standard, ref_no FROM audit_schedule_id WHERE customer_ca_id = $customerId ORDER BY audit_schedule_id DESC",
    'applications' => "SELECT aa.audit_id, aa.uni_svc_id, aa.service_id, aa.certification, aa.assessment_standard, aa.accereditation_requirement, aa.review_accredition FROM audit_application aa INNER JOIN service s ON s.service_id = aa.uni_svc_id WHERE s.customer_id = $customerId ORDER BY aa.audit_id DESC",
    'cdc' => "SELECT crr_id, ca_service_id, covered_for, accreditation, cdc_status, certificateNo, Date, revDatecdc, expiredate FROM cdc_report_review WHERE customer_id = $customerId ORDER BY crr_id DESC",
    'office_use' => "SELECT cou.* FROM cdc_report_review_office_use cou INNER JOIN cdc_report_review crr ON crr.crr_id = cou.crr_id WHERE crr.customer_id = $customerId ORDER BY cou.crr_id DESC, cou.iso_index ASC",
    'metadata' => "SELECT cim.* FROM cdc_iso_metadata cim INNER JOIN cdc_report_review crr ON crr.crr_id = cim.crr_id WHERE crr.customer_id = $customerId ORDER BY cim.crr_id DESC, cim.iso_index ASC",
    'certificate_print' => "SELECT certificate_id, customer_id, service_id, certificate_no, registration_date, valid_until, certificate_scope FROM certificate_print WHERE customer_id = $customerId ORDER BY certificate_id DESC",
);

foreach ($queries as $label => $sql) {
    echo "=== $label ===", PHP_EOL;
    foreach ($db->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;
    }
}

echo "=== reconstructed_entries ===", PHP_EOL;
foreach (aitfBuildLegacyCdcIsoEntries($db, 1472) as $entry) {
    echo json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;
}

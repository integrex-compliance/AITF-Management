<?php
include 'dbConnect.php';

$sql = "SELECT id, audit_status, customer_name, is_deleted FROM customer_info WHERE is_deleted <> 'YES'";
$stmt = $db->query($sql);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total active clients in DB: " . count($clients) . "\n\n";

$sql2 = "SELECT customer_id, service_id, service_type FROM service ORDER BY service_id ASC";
$stmt2 = $db->query($sql2);
$services = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$serviceMap = [];
foreach ($services as $svc) {
    // get latest
    $serviceMap[$svc['customer_id']] = $svc; 
}

function hpNormalizeStatus($value) {
    $status = trim((string) $value);
    if ($status === '') return 'No Status';
    $key = strtolower(str_replace(['-', '_', ' '], '', $status));
    if (in_array($key, ['active', 'valid', 'granted'])) return 'Active';
    if (in_array($key, ['suspended', 'suspend'])) return 'Suspended';
    if ($key === 'inprocess') return 'In-process';
    if ($key === 'contractinprocess') return 'Contract In-process';
    if (in_array($key, ['withdrawn', 'withdraw'])) return 'Withdrawn';
    if (in_array($key, ['applicationcanceled', 'applicationcancelled', 'cancelled', 'canceled'])) return 'Application canceled';
    if (in_array($key, ['expired', 'expire'])) return 'Expired';
    return $status;
}

$statusCounts = [];
$dbStatusCounts = [];

foreach ($clients as $c) {
    $dbStat = $c['audit_status'];
    if (!isset($dbStatusCounts[$dbStat])) $dbStatusCounts[$dbStat] = 0;
    $dbStatusCounts[$dbStat]++;

    $norm = hpNormalizeStatus($c['audit_status']);
    $ns = $norm;
    if ($norm === 'Application canceled') {
        $ns = 'Application canceled';
    } else {
        $cid = $c['id'];
        $svc = $serviceMap[$cid] ?? null;
        if (empty($svc)) {
            $ns = 'New Enquiry';
        } else if (strtoupper(trim($svc['service_type'])) === 'CR') {
            $ns = 'Contract-Review';
        } else {
            $ns = $norm;
        }
    }
    
    if (!isset($statusCounts[$ns])) $statusCounts[$ns] = 0;
    $statusCounts[$ns]++;
}

echo "DB audit_status exact counts:\n";
print_r($dbStatusCounts);

echo "\nhomepage.php derived status counts:\n";
print_r($statusCounts);

<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include_once './dbConnect.php';
require_once __DIR__ . '/include/auditor_division_helper.php';

$customerId = isset($_GET['customerid']) ? (int)$_GET['customerid'] : (isset($_GET['customid']) ? (int)$_GET['customid'] : 0);
$serviceId = isset($_GET['serviceid']) ? (int)$_GET['serviceid'] : (isset($_GET['svcID']) ? (int)$_GET['svcID'] : null);

$data = aitf_get_eligible_auditors_for_client($db, $customerId, $serviceId);
echo json_encode($data);
exit;

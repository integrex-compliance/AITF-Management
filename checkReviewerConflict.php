<?php
header('Content-Type: application/json');
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/reviewer_conflict_helper.php';

$action = trim((string)($_REQUEST['action'] ?? ''));
$customerId = trim((string)($_REQUEST['customerId'] ?? $_REQUEST['customerid'] ?? ''));
$serviceId = trim((string)($_REQUEST['serviceId'] ?? $_REQUEST['service_id'] ?? ''));
$reviewerName = trim((string)($_REQUEST['reviewerName'] ?? $_REQUEST['reviewer_name'] ?? ''));
$auditor = trim((string)($_REQUEST['auditor'] ?? $_REQUEST['auditorId'] ?? $_REQUEST['auditor_id'] ?? $_REQUEST['auditorName'] ?? $_REQUEST['auditor_name'] ?? ''));

if ($action === 'get_service_auditors') {
    $assigned = aitf_get_auditors_for_service($db, $serviceId, $customerId);
    echo json_encode(array(
        'success' => true,
        'auditors' => $assigned['names'],
        'auditor_ids' => $assigned['ids']
    ));
    exit;
}

if ($action === 'check_service_reviewer') {
    echo json_encode(array(
        'conflict' => false,
        'conflicting_auditor' => '',
        'reviewer_name' => $reviewerName,
        'message' => '',
        'auditors' => array()
    ));
    exit;
}

if ($action === 'get_assigned_auditors') {
    $assigned = aitf_get_assigned_auditors_for_client($db, $customerId, $serviceId);
    echo json_encode(array(
        'success' => true,
        'auditors' => $assigned['names'],
        'auditor_ids' => $assigned['ids']
    ));
    exit;
}

if ($action === 'get_appointed_reviewers') {
    $appointed = aitf_get_appointed_reviewers_for_client($db, $customerId, $serviceId);
    echo json_encode(array(
        'success' => true,
        'reviewers' => $appointed['names'],
        'auditor_ids' => $appointed['auditor_ids']
    ));
    exit;
}

if ($action === 'get_reviewers') {
    $reviewers = aitf_get_all_reviewers($db);
    echo json_encode(array(
        'success' => true,
        'reviewers' => $reviewers
    ));
    exit;
}

// When checking if an Auditor can be appointed (has this person already been appointed as Reviewer?)
if ($action === 'check_auditor' || ($auditor !== '' && $reviewerName === '')) {
    echo json_encode(array(
        'conflict' => false,
        'conflicting_reviewer' => '',
        'auditor_name' => $auditor,
        'message' => ''
    ));
    exit;
}

// When checking if a Reviewer can be appointed (has this person already been assigned as Auditor?)
echo json_encode(array(
    'conflict' => false,
    'auditor_name' => '',
    'message' => ''
));
exit;

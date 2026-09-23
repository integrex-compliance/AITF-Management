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
    $result = aitf_check_service_reviewer_conflict($db, $serviceId, $reviewerName, $customerId);
    echo json_encode(array(
        'conflict' => !empty($result['has_conflict']),
        'conflicting_auditor' => $result['conflicting_auditor'] ?? '',
        'reviewer_name' => $result['reviewer_name'] ?? '',
        'message' => $result['message'] ?? '',
        'auditors' => $result['auditors'] ?? array()
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
    $result = aitf_check_auditor_appointed_as_reviewer($db, $customerId, $serviceId, $auditor);
    echo json_encode(array(
        'conflict' => !empty($result['has_conflict']),
        'conflicting_reviewer' => $result['conflicting_name'] ?? '',
        'auditor_name' => $result['auditor_name'] ?? '',
        'message' => $result['message'] ?? ''
    ));
    exit;
}

// When checking if a Reviewer can be appointed (has this person already been assigned as Auditor?)
$result = aitf_check_auditor_reviewer_conflict($db, $customerId, $serviceId, $reviewerName);

echo json_encode(array(
    'conflict' => !empty($result['has_conflict']),
    'auditor_name' => $result['conflicting_name'] ?? '',
    'message' => $result['message'] ?? ''
));
exit;

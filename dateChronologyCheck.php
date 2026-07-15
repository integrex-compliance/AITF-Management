<?php
/**
 * Date Chronology Check AJAX Endpoint
 * 
 * Returns all chronological dates for a service to enable client-side
 * date validation in forms/modals.
 *
 * Parameters:
 *   customer_id      - Required. The customer ID.
 *   typed_service_id - Required. The typed service ID (ca_id/sai_id/saii_id/rc_id/cr_id).
 *   service_type     - Optional. If omitted, auto-detected from typed_service_id.
 *
 * Returns JSON: { dates: {...}, service_type: "CA"|"SA-I"|"SA-II"|"RC", rules: [...] }
 */

session_start();
include_once './dbConnect.php';
include_once './include/date_graph_helpers.php';
date_default_timezone_set('Asia/Kolkata');

header('Content-Type: application/json; charset=utf-8');

$customerId = isset($_GET['customer_id']) ? trim($_GET['customer_id']) : '';
$typedServiceId = isset($_GET['typed_service_id']) ? trim($_GET['typed_service_id']) : '';
$serviceType = isset($_GET['service_type']) ? trim($_GET['service_type']) : '';

if (empty($customerId) || empty($typedServiceId)) {
    echo json_encode(array('error' => 'Missing customer_id or typed_service_id'));
    exit;
}

// Auto-detect service type if not provided
$universalServiceId = '';
if (empty($serviceType)) {
    $serviceInfo = caDateGraphResolveServiceInfo($db, $typedServiceId);
    if (!empty($serviceInfo)) {
        $serviceType = $serviceInfo['service_type'];
        $universalServiceId = $serviceInfo['universal_service_id'];
    }
} else {
    // Normalize service type aliases
    $typeMap = array('SA1' => 'SA-I', 'SA2' => 'SA-II');
    if (isset($typeMap[$serviceType])) {
        $serviceType = $typeMap[$serviceType];
    }
    // Look up universal service ID
    $serviceInfo = caDateGraphResolveServiceInfo($db, $typedServiceId);
    if (!empty($serviceInfo)) {
        $universalServiceId = $serviceInfo['universal_service_id'];
    }
}

// For CR type (contract review), find the parent CA/RC service to get the full chain
$actualTypedServiceId = $typedServiceId;
$actualServiceType = $serviceType;
if ($serviceType === 'CR') {
    // CR service dates are part of CA or RC chain. Find the parent CA or RC.
    $parentRow = caDateGraphFetchOne($db,
        "SELECT s.service_id, s.service_type, 
                COALESCE(ca.ca_id, sai.sai_id, saii.saii_id, rc.rc_id) AS typed_id
         FROM service s
         LEFT JOIN service_type_ca ca ON ca.service = s.service_id
         LEFT JOIN service_type_sai sai ON sai.service = s.service_id
         LEFT JOIN service_type_saii saii ON saii.service = s.service_id
         LEFT JOIN service_type_rc rc ON rc.service = s.service_id
         WHERE s.customer_id = :customer_id
         AND s.service_type IN ('CA', 'RC')
         AND s.service_id <= :uni_svc_id
         ORDER BY s.service_id DESC
         LIMIT 1",
        array(':customer_id' => $customerId, ':uni_svc_id' => $universalServiceId)
    );
    if (!empty($parentRow['typed_id'])) {
        $actualTypedServiceId = $parentRow['typed_id'];
        $actualServiceType = $parentRow['service_type'];
        $universalServiceId = $parentRow['service_id'];
    } else {
        // Fallback: return dates for the CR service itself
        $actualServiceType = 'CA';
    }
}

if (empty($actualServiceType) || !in_array($actualServiceType, array('CA', 'SA-I', 'SA-II', 'RC'))) {
    echo json_encode(array('error' => 'Could not determine service type', 'service_type' => $serviceType));
    exit;
}

// Fetch all chronological dates
$dates = caDateGraphFetchChronologyForService($db, $customerId, $actualTypedServiceId, $actualServiceType, $universalServiceId);

// Build validation rules based on service type
$rules = array();

if ($actualServiceType === 'CA') {
    $rules = array(
        array('field' => 'application_date',        'must_be_after' => 'qms_implementation_date',  'label' => 'Application Date',                 'after_label' => 'QMS Implementation Date', 'strict' => true),
        array('field' => 'application_rcv_date',     'must_be_after' => 'application_date',         'label' => 'Application Received Date',         'after_label' => 'Application Date'),
        array('field' => 'contract_review_date',     'must_be_after' => 'application_rcv_date',     'label' => 'Review Date',                       'after_label' => 'Application Received Date'),
        array('field' => 'aggrement_date',           'must_be_after' => 'contract_review_date',     'label' => 'Agreement Date',                    'after_label' => 'Review Date'),
        array('field' => 'assessment_planning_date', 'must_be_after' => 'aggrement_date',           'label' => 'Assessment Planning Date',           'after_label' => 'Agreement Date'),
        array('field' => 'stage1_intimation_date',   'must_be_after' => 'assessment_planning_date', 'label' => 'Stage-I Intimation Date',            'after_label' => 'Assessment Planning Date'),
        array('field' => 'stage1_audit_date',        'must_be_after' => 'stage1_intimation_date',   'label' => 'Stage-I Audit Date',                 'after_label' => 'Stage-I Intimation Date'),
        array('field' => 'stage1_audit_date',        'must_be_after' => 'qms_implementation_date',  'label' => 'Stage-I Audit Date',                 'after_label' => 'QMS Implementation Date + 2 months', 'offset_months' => 2),
        array('field' => 'stage1_review_date',       'must_be_after' => 'stage1_audit_date',        'label' => 'Stage-I Review Date',                'after_label' => 'Stage-I Audit Date'),
        array('field' => 'stage2_intimation_date',   'must_be_after' => 'stage1_review_date',       'label' => 'Stage-II Intimation Date',           'after_label' => 'Stage-I Review Date'),
        array('field' => 'stage2_intimation_date',   'must_be_after' => 'stage1_audit_date',        'label' => 'Stage-II Intimation Date',           'after_label' => 'Stage-I Audit Date'),
        array('field' => 'stage2_intimation_date',   'must_be_after' => 'stage1_intimation_date',   'label' => 'Stage-II Intimation Date',           'after_label' => 'Stage-I Intimation Date'),
        array('field' => 'stage2_intimation_date',   'must_be_after' => 'assessment_planning_date', 'label' => 'Stage-II Intimation Date',           'after_label' => 'Assessment Planning Date'),
        array('field' => 'stage2_intimation_date',   'must_be_after' => 'application_date',         'label' => 'Stage-II Intimation Date',           'after_label' => 'Application Date'),
        array('field' => 'stage2_audit_date',        'must_be_after' => 'stage2_intimation_date',   'label' => 'Stage-II Audit Date',                'after_label' => 'Stage-II Intimation Date'),
        array('field' => 'nc_closing_date',          'must_be_after' => 'stage2_audit_date',        'label' => 'Assessment / NC(M) Closing Date',    'after_label' => 'Stage-II Audit Date'),
        array('field' => 'stage2_review_date',       'must_be_after' => 'nc_closing_date',          'label' => 'Stage-II Review Date',               'after_label' => 'Assessment / NC(M) Closing Date'),
        array('field' => 'cdc_date',                 'must_be_after' => 'stage2_review_date',       'label' => 'CDC Date',                           'after_label' => 'Stage-II Review Date'),
        array('field' => 'certificate_issue_date',   'must_be_after' => 'cdc_date',                 'label' => 'Rev Date / Certificate Issue Date',  'after_label' => 'CDC Date'),
        array('field' => 'survey_date',              'must_be_after' => 'certificate_issue_date',   'label' => 'Feedback Survey Date',               'after_label' => 'Rev Date / Certificate Issue Date'),
    );
} elseif ($actualServiceType === 'SA-I' || $actualServiceType === 'SA-II') {
    $rules = array(
        array('field' => 'intimation_date',          'must_be_after' => 'info_review_date',         'label' => 'Intimation Date',                    'after_label' => 'Information Updation Review Date'),
        array('field' => 'audit_date',               'must_be_after' => 'intimation_date',          'label' => 'Audit Date',                         'after_label' => 'Intimation Date'),
        array('field' => 'nc_closing_date',          'must_be_after' => 'audit_date',               'label' => 'Assessment / NC(M) Closing Date',    'after_label' => 'Audit Date'),
        array('field' => 'sa_review_date',           'must_be_after' => 'nc_closing_date',          'label' => 'Review Date',                        'after_label' => 'Assessment / NC(M) Closing Date'),
        array('field' => 'cdc_date',                 'must_be_after' => 'sa_review_date',           'label' => 'CDC Date',                           'after_label' => 'Review Date'),
        array('field' => 'certificate_issue_date',   'must_be_after' => 'cdc_date',                 'label' => 'Rev Date / Issue Date',              'after_label' => 'CDC Date'),
        array('field' => 'survey_date',              'must_be_after' => 'certificate_issue_date',   'label' => 'Feedback Survey Date',               'after_label' => 'Rev Date / Issue Date'),
    );
} elseif ($actualServiceType === 'RC') {
    $rules = array(
        array('field' => 'application_date',        'must_be_after' => 'qms_implementation_date',  'label' => 'Application Date',                  'after_label' => 'QMS Implementation Date', 'strict' => true),
        array('field' => 'application_rcv_date',     'must_be_after' => 'application_date',         'label' => 'Application Received Date',         'after_label' => 'Application Date'),
        array('field' => 'contract_review_date',     'must_be_after' => 'application_rcv_date',     'label' => 'Review Date',                       'after_label' => 'Application Received Date'),
        array('field' => 'aggrement_date',           'must_be_after' => 'contract_review_date',     'label' => 'Agreement Date',                    'after_label' => 'Review Date'),
        array('field' => 'assessment_planning_date', 'must_be_after' => 'aggrement_date',           'label' => 'Assessment Planning Date',           'after_label' => 'Agreement Date'),
        array('field' => 'intimation_date',          'must_be_after' => 'assessment_planning_date', 'label' => 'Intimation Date',                    'after_label' => 'Assessment Planning Date'),
        array('field' => 'audit_date',               'must_be_after' => 'intimation_date',          'label' => 'Audit Date',                         'after_label' => 'Intimation Date'),
        array('field' => 'nc_closing_date',          'must_be_after' => 'audit_date',               'label' => 'Assessment / NC(M) Closing Date',    'after_label' => 'Audit Date'),
        array('field' => 'rc_review_date',           'must_be_after' => 'nc_closing_date',          'label' => 'Review Date',                        'after_label' => 'Assessment / NC(M) Closing Date'),
        array('field' => 'cdc_date',                 'must_be_after' => 'rc_review_date',           'label' => 'CDC Date',                           'after_label' => 'Review Date'),
        array('field' => 'certificate_issue_date',   'must_be_after' => 'cdc_date',                 'label' => 'Rev Date / Issue Date',              'after_label' => 'CDC Date'),
        array('field' => 'survey_date',              'must_be_after' => 'certificate_issue_date',   'label' => 'Feedback Survey Date',               'after_label' => 'Rev Date / Issue Date'),
    );
}

echo json_encode(array(
    'dates' => $dates,
    'service_type' => $actualServiceType,
    'rules' => $rules,
));

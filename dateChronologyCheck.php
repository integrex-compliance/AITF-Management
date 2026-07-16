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

// A CR starts a fresh contract-review cycle. Keep its own IDs so dates from the
// previous CA cycle are not reused while the new Application form is being filled.
$actualTypedServiceId = $typedServiceId;
$actualServiceType = $serviceType;
$isRecertificationContractReview = false;
if ($serviceType === 'CR') {
    $nextCycleRow = caDateGraphFetchOne(
        $db,
        "SELECT service_type
         FROM service
         WHERE customer_id = :customer_id
         AND service_id > :uni_svc_id
         AND service_type IN ('CA', 'RC')
         ORDER BY service_id ASC
         LIMIT 1",
        array(':customer_id' => $customerId, ':uni_svc_id' => $universalServiceId)
    );

    if (!empty($nextCycleRow['service_type'])) {
        $actualServiceType = $nextCycleRow['service_type'];
    } else {
        $previousCycleRow = caDateGraphFetchOne(
            $db,
            "SELECT service_type
             FROM service
             WHERE customer_id = :customer_id
             AND service_id < :uni_svc_id
             AND service_type IN ('CA', 'SA-I', 'SA-II', 'RC')
             ORDER BY service_id DESC
             LIMIT 1",
            array(':customer_id' => $customerId, ':uni_svc_id' => $universalServiceId)
        );

        $actualServiceType = (!empty($previousCycleRow['service_type']) && $previousCycleRow['service_type'] === 'SA-II')
            ? 'RC'
            : 'CA';
    }

    $isRecertificationContractReview = ($actualServiceType === 'RC');
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

    if (!$isRecertificationContractReview) {
        array_unshift(
            $rules,
            array('field' => 'application_date', 'must_be_after' => 'qms_implementation_date', 'label' => 'Application Date', 'after_label' => 'QMS Implementation Date', 'strict' => true)
        );
    }
}

echo json_encode(array(
    'dates' => $dates,
    'service_type' => $actualServiceType,
    'rules' => $rules,
));

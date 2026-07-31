<?php
session_start();
require_once('tcpdf_include.php');
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
@set_time_limit(0);

while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

function dgrHasValue($value)
{
    $value = trim((string) $value);
    return $value !== '' && strtolower($value) !== 'null' && $value !== '0000-00-00' && $value !== '0000-00-00 00:00:00';
}

function dgrParseOneDate($value)
{
    $value = trim((string) $value);
    if (!dgrHasValue($value)) {
        return null;
    }

    $value = preg_replace('/\s+/', ' ', $value);
    $formats = array('Y-m-d', 'Y/m/d', 'Y.m.d', 'd.m.Y', 'd-m-Y', 'd/m/Y', 'd M Y', 'd F Y', 'M d Y', 'F d Y');

    foreach ($formats as $format) {
        $date = DateTime::createFromFormat('!' . $format, $value);
        if ($date instanceof DateTime) {
            $errors = DateTime::getLastErrors();
            if (($errors === false || ($errors['warning_count'] == 0 && $errors['error_count'] == 0)) && $date->format($format) === $value) {
                return array(
                    'iso' => $date->format('Y-m-d'),
                    'display' => $date->format('d.m.Y'),
                    'timestamp' => $date->getTimestamp(),
                );
            }
        }
    }

    $timestamp = strtotime($value);
    if ($timestamp !== false) {
        return array(
            'iso' => date('Y-m-d', $timestamp),
            'display' => date('d.m.Y', $timestamp),
            'timestamp' => $timestamp,
        );
    }

    return null;
}

function dgrParseDates($value)
{
    if (!dgrHasValue($value)) {
        return array();
    }

    $value = trim((string) $value);
    $candidates = array();
    $patterns = array(
        '/\b\d{4}[-\/.]\d{1,2}[-\/.]\d{1,2}\b/',
        '/\b\d{1,2}[-\/.]\d{1,2}[-\/.]\d{4}\b/',
        '/\b\d{1,2}\s+[A-Za-z]{3,9}\s+\d{4}\b/',
        '/\b[A-Za-z]{3,9}\s+\d{1,2}\s+\d{4}\b/',
    );

    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $value, $matches)) {
            foreach ($matches[0] as $match) {
                $candidates[] = trim($match);
            }
        }
    }

    if (empty($candidates)) {
        $candidates[] = $value;
    }

    $dates = array();
    $seen = array();
    foreach ($candidates as $candidate) {
        $parsed = dgrParseOneDate($candidate);
        if ($parsed && !isset($seen[$parsed['iso']])) {
            $parsed['raw'] = $candidate;
            $dates[] = $parsed;
            $seen[$parsed['iso']] = true;
        }
    }

    return $dates;
}

function dgrFetchOne($db, $sql, array $params)
{
    try {
        $statement = $db->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : array();
    } catch (Exception $ex) {
        error_log('Date graph report fetch failed: ' . $ex->getMessage());
        return array();
    }
}

function dgrFetchAll($db, $sql, array $params = array())
{
    try {
        $statement = $db->prepare($sql);
        $statement->execute($params);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $rows ? $rows : array();
    } catch (Exception $ex) {
        error_log('Date graph report fetch failed: ' . $ex->getMessage());
        return array();
    }
}

function dgrFirstValue(array $row, array $fields)
{
    foreach ($fields as $field) {
        if (isset($row[$field]) && dgrHasValue($row[$field])) {
            return $row[$field];
        }
    }
    return '';
}

function dgrAddEntry(array &$entries, $serviceType, $section, $label, $value, $source, $typedServiceId, $universalServiceId)
{
    $entries[] = array(
        'service_type' => $serviceType,
        'section' => $section,
        'label' => $label,
        'value' => dgrHasValue($value) ? trim((string) $value) : '',
        'source' => $source,
        'typed_service_id' => $typedServiceId,
        'universal_service_id' => $universalServiceId,
        'dates' => dgrParseDates($value),
        'status' => 'missing',
        'note' => '',
    );
}

function dgrFormatMultiDateValue(array $rows, $dateField, $labelField = '')
{
    $values = array();
    foreach ($rows as $row) {
        if (!isset($row[$dateField]) || !dgrHasValue($row[$dateField])) {
            continue;
        }
        $label = ($labelField !== '' && isset($row[$labelField]) && trim((string) $row[$labelField]) !== '') ? trim((string) $row[$labelField]) . ': ' : '';
        $values[] = $label . $row[$dateField];
    }
    return implode('; ', $values);
}

function dgrInfoReviewDate($db, $typedServiceId, $customerId)
{
    $row = dgrFetchOne(
        $db,
        "SELECT review_date, info_form_received_date FROM info_update_review WHERE service_id = :service_id AND customer_id = :customer_id ORDER BY id DESC LIMIT 1",
        array(':service_id' => $typedServiceId, ':customer_id' => $customerId)
    );

    $dateValue = dgrFirstValue($row, array('review_date', 'info_form_received_date'));
    if (dgrHasValue($dateValue)) {
        return $dateValue;
    }

    $jsonFile = __DIR__ . '/uploads/reviews/data_' . $typedServiceId . '_' . $customerId . '.json';
    if (is_file($jsonFile)) {
        $data = json_decode(file_get_contents($jsonFile), true);
        if (is_array($data)) {
            return dgrFirstValue($data, array('review_date', 'info_form_received_date'));
        }
    }

    return '';
}

function dgrCertificateIssueDate($db, array $cdcRow)
{
    if (empty($cdcRow) || empty($cdcRow['crr_id'])) {
        return '';
    }

    $officeRows = dgrFetchAll(
        $db,
        "SELECT iso_label, rev_date FROM cdc_report_review_office_use WHERE crr_id = :crr_id AND rev_date IS NOT NULL AND rev_date <> '0000-00-00' ORDER BY iso_index ASC",
        array(':crr_id' => $cdcRow['crr_id'])
    );
    $officeDates = dgrFormatMultiDateValue($officeRows, 'rev_date', 'iso_label');
    if (dgrHasValue($officeDates)) {
        return $officeDates;
    }

    return dgrFirstValue($cdcRow, array('revDatecdc', 'Date'));
}

function dgrFetchAssessmentPlanning($db, $customerId, array $serviceIds)
{
    $seen = array();
    $fallback = array();
    foreach ($serviceIds as $serviceId) {
        $serviceId = trim((string) $serviceId);
        if ($serviceId === '' || isset($seen[$serviceId])) {
            continue;
        }
        $seen[$serviceId] = true;

        $row = dgrFetchOne(
            $db,
            "SELECT assessment_id, service_id, qms_implementation_date, date
             FROM assessment_planning
             WHERE customer_id = :customer_id
             AND service_id = :service_id
             ORDER BY
                CASE
                    WHEN qms_implementation_date IS NOT NULL AND qms_implementation_date <> '' AND qms_implementation_date <> '0000-00-00' THEN 0
                    WHEN date IS NOT NULL AND date <> '' AND date <> '0000-00-00' THEN 1
                    ELSE 2
                END,
                assessment_id DESC
             LIMIT 1",
            array(':customer_id' => $customerId, ':service_id' => $serviceId)
        );

        if (empty($row)) {
            continue;
        }
        if (empty($fallback)) {
            $fallback = $row;
        }
        if (dgrFirstValue($row, array('qms_implementation_date', 'date')) !== '') {
            return $row;
        }
    }

    return $fallback;
}

function dgrFetchCdc($db, $customerId, $typedServiceId)
{
    $latest = dgrFetchOne(
        $db,
        "SELECT crr_id, assDate, Date, revDatecdc
         FROM cdc_report_review
         WHERE customer_id = :customer_id
         AND ca_service_id = :typed_id
         ORDER BY crr_id DESC
         LIMIT 1",
        array(':customer_id' => $customerId, ':typed_id' => $typedServiceId)
    );

    if (empty($latest) || dgrHasValue(isset($latest['assDate']) ? $latest['assDate'] : '')) {
        return $latest;
    }

    $closingDateRow = dgrFetchOne(
        $db,
        "SELECT assDate
         FROM cdc_report_review
         WHERE customer_id = :customer_id
         AND ca_service_id = :typed_id
         AND assDate IS NOT NULL
         AND assDate <> ''
         AND assDate <> '0000-00-00'
         ORDER BY crr_id DESC
         LIMIT 1",
        array(':customer_id' => $customerId, ':typed_id' => $typedServiceId)
    );

    if (!empty($closingDateRow['assDate'])) {
        $latest['assDate'] = $closingDateRow['assDate'];
    }

    return $latest;
}

function dgrResolveCrServiceForCa($db, $customerId, $caUniversalServiceId)
{
    $row = dgrFetchOne(
        $db,
        "SELECT crs.cr_id AS typed_service_id, s.service_id AS universal_service_id
         FROM service_type_cr crs
         INNER JOIN service s ON crs.service = s.service_id
         WHERE s.customer_id = :customer_id
         AND s.service_type = 'CR'
         AND s.service_id <= :ca_service_id
         ORDER BY s.service_id DESC
         LIMIT 1",
        array(':customer_id' => $customerId, ':ca_service_id' => $caUniversalServiceId)
    );

    if (!empty($row['typed_service_id'])) {
        return $row;
    }

    $row = dgrFetchOne(
        $db,
        "SELECT crs.cr_id AS typed_service_id, s.service_id AS universal_service_id
         FROM service_type_cr crs
         INNER JOIN service s ON crs.service = s.service_id
         WHERE s.customer_id = :customer_id
         AND s.service_type = 'CR'
         ORDER BY s.service_id DESC
         LIMIT 1",
        array(':customer_id' => $customerId)
    );

    return !empty($row['typed_service_id']) ? $row : array();
}

function dgrAddProposedDates(array &$entries, $db, $serviceType, $section, $typedServiceId, $universalServiceId, array $actions)
{
    if (empty($universalServiceId)) {
        foreach ($actions as $label) {
            dgrAddEntry($entries, $serviceType, $section, $label, '', 'ca_service_dates.due_date', $typedServiceId, $universalServiceId);
        }
        return;
    }

    $rows = dgrFetchAll(
        $db,
        "SELECT action, due_date FROM ca_service_dates WHERE service_id = :service_id AND is_deleted = 'N' ORDER BY due_date ASC, action_id ASC",
        array(':service_id' => $universalServiceId)
    );
    $datesByAction = array();
    foreach ($rows as $row) {
        if (isset($row['action']) && !isset($datesByAction[$row['action']])) {
            $datesByAction[$row['action']] = isset($row['due_date']) ? $row['due_date'] : '';
        }
    }

    foreach ($actions as $action => $label) {
        dgrAddEntry($entries, $serviceType, $section, $label, isset($datesByAction[$action]) ? $datesByAction[$action] : '', 'ca_service_dates.due_date', $typedServiceId, $universalServiceId);
    }
}

function dgrEntryMinTimestamp(array $entry)
{
    if (empty($entry['dates'])) {
        return null;
    }
    return min(array_map(function ($date) { return $date['timestamp']; }, $entry['dates']));
}

function dgrEntryMaxTimestamp(array $entry)
{
    if (empty($entry['dates'])) {
        return null;
    }
    return max(array_map(function ($date) { return $date['timestamp']; }, $entry['dates']));
}

function dgrEntryInternalDatesOk(array $entry)
{
    if (empty($entry['dates'])) {
        return true;
    }

    $dates = $entry['dates'];
    for ($i = 1; $i < count($dates); $i++) {
        if ($dates[$i]['timestamp'] < $dates[$i - 1]['timestamp']) {
            return false;
        }
    }

    return true;
}

function dgrValidationIssueForEntry(array $entry, array $workflowContext, $lastTimestamp, $lastLabel)
{
    $label = isset($entry['label']) ? $entry['label'] : '';
    $serviceType = isset($entry['service_type']) ? $entry['service_type'] : '';
    $section = isset($entry['section']) ? $entry['section'] : '';
    $minTimestamp = dgrEntryMinTimestamp($entry);

    if ($label === 'Application Date' && isset($workflowContext['QMS Implementation Date'])) {
        $qmsMaxTimestamp = dgrEntryMaxTimestamp($workflowContext['QMS Implementation Date']);
        if ($qmsMaxTimestamp !== null && $minTimestamp !== null && $minTimestamp <= $qmsMaxTimestamp) {
            return 'Application Date should be after QMS Implementation Date';
        }
    }

    if ($serviceType === 'CA' && $label === 'Stage-I Audit Date' && isset($workflowContext['QMS Implementation Date'])) {
        $qmsMaxTimestamp = dgrEntryMaxTimestamp($workflowContext['QMS Implementation Date']);
        if ($qmsMaxTimestamp !== null && $minTimestamp !== null) {
            $minimumStageOneTimestamp = strtotime('+2 months', $qmsMaxTimestamp);
            if ($minimumStageOneTimestamp !== false && $minTimestamp < $minimumStageOneTimestamp) {
                return 'Stage-I Audit Date should be at least 2 months after QMS Implementation Date';
            }
        }
    }

    if ($label === 'Assessment / NC(M) Closing Date') {
        $auditReferenceLabel = ($serviceType === 'CA') ? 'Stage-II Audit Date' : 'Audit Date';
        if (isset($workflowContext[$auditReferenceLabel])) {
            $auditMaxTimestamp = dgrEntryMaxTimestamp($workflowContext[$auditReferenceLabel]);
            if ($auditMaxTimestamp !== null && $minTimestamp !== null && $minTimestamp < $auditMaxTimestamp) {
                return $label . ' should be greater than or equal to ' . $auditReferenceLabel;
            }
        }
        return '';
    }

    if ($lastTimestamp !== null && $minTimestamp !== null && $minTimestamp < $lastTimestamp) {
        return $section . ' - ' . $label . ' should be greater than ' . $lastLabel;
    }

    return '';
}

function dgrBuildForCustomer($db, $customerId)
{
    $entries = array();
    if (empty($db) || empty($customerId)) {
        return array('entries' => array(), 'summary' => array('ok' => 0, 'issue' => 0, 'missing' => 0, 'total' => 0));
    }

    $services = dgrFetchAll(
        $db,
        "SELECT s.service_id, s.service_type, ca.ca_id, sai.sai_id, saii.saii_id, rc.rc_id
         FROM service s
         LEFT JOIN service_type_ca ca ON ca.service = s.service_id
         LEFT JOIN service_type_sai sai ON sai.service = s.service_id
         LEFT JOIN service_type_saii saii ON saii.service = s.service_id
         LEFT JOIN service_type_rc rc ON rc.service = s.service_id
         WHERE s.customer_id = :customer_id
         AND s.service_type IN ('CA', 'SA-I', 'SA-II', 'RC')
         ORDER BY s.service_id ASC",
        array(':customer_id' => $customerId)
    );

    foreach ($services as $service) {
        $serviceEntryStart = count($entries);
        $serviceType = isset($service['service_type']) ? $service['service_type'] : '';
        $universalServiceId = isset($service['service_id']) ? $service['service_id'] : '';
        $typedServiceId = '';

        if ($serviceType === 'CA') {
            $typedServiceId = isset($service['ca_id']) ? $service['ca_id'] : '';
        } elseif ($serviceType === 'SA-I') {
            $typedServiceId = isset($service['sai_id']) ? $service['sai_id'] : '';
        } elseif ($serviceType === 'SA-II') {
            $typedServiceId = isset($service['saii_id']) ? $service['saii_id'] : '';
        } elseif ($serviceType === 'RC') {
            $typedServiceId = isset($service['rc_id']) ? $service['rc_id'] : '';
        }

        if (empty($typedServiceId)) {
            continue;
        }

        if ($serviceType === 'CA' || $serviceType === 'RC') {
            $section = ($serviceType === 'RC') ? 'RC Contract Review' : 'Contract Review';
            $contractTypedServiceId = $typedServiceId;
            $contractUniversalServiceId = $universalServiceId;

            $crService = dgrResolveCrServiceForCa($db, $customerId, $universalServiceId);
            if (!empty($crService['typed_service_id'])) {
                $contractTypedServiceId = $crService['typed_service_id'];
                $contractUniversalServiceId = $crService['universal_service_id'];
            }

            $auditApplication = dgrFetchOne(
                $db,
                "SELECT application_date, application_rcv_date, review_date FROM audit_application WHERE (service_id = :typed_id OR uni_svc_id = :service_id) ORDER BY audit_id DESC LIMIT 1",
                array(':typed_id' => $contractTypedServiceId, ':service_id' => $contractUniversalServiceId)
            );
            $agreement = dgrFetchOne(
                $db,
                "SELECT aggrement_date FROM certification_aggrement WHERE customer_id = :customer_id AND (service = :typed_id OR service = :service_id) ORDER BY id DESC LIMIT 1",
                array(':customer_id' => $customerId, ':typed_id' => $contractTypedServiceId, ':service_id' => $contractUniversalServiceId)
            );
            $assessment = dgrFetchAssessmentPlanning($db, $customerId, array($contractTypedServiceId, $typedServiceId, $contractUniversalServiceId, $universalServiceId));
            $assessmentTypedServiceId = isset($assessment['service_id']) && dgrHasValue($assessment['service_id']) ? $assessment['service_id'] : $contractTypedServiceId;

            dgrAddEntry($entries, $serviceType, $section, 'QMS Implementation Date', dgrFirstValue($assessment, array('qms_implementation_date')), 'assessment_planning.qms_implementation_date', $assessmentTypedServiceId, $contractUniversalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'Application Date', dgrFirstValue($auditApplication, array('application_date')), 'audit_application.application_date', $contractTypedServiceId, $contractUniversalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'Application Received Date', dgrFirstValue($auditApplication, array('application_rcv_date')), 'audit_application.application_rcv_date', $contractTypedServiceId, $contractUniversalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'Review Date', dgrFirstValue($auditApplication, array('review_date')), 'audit_application.review_date', $contractTypedServiceId, $contractUniversalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'Agreement Date', dgrFirstValue($agreement, array('aggrement_date')), 'certification_aggrement.aggrement_date', $contractTypedServiceId, $contractUniversalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'Assessment Planning Date', dgrFirstValue($assessment, array('date')), 'assessment_planning.date', $assessmentTypedServiceId, $contractUniversalServiceId);
        }

        $stageOne = dgrFetchOne($db, "SELECT intimation_date, audit_dates FROM stage_oneaudit_id WHERE ca_service_id = :typed_id ORDER BY audit_stage_id DESC LIMIT 1", array(':typed_id' => $typedServiceId));
        $stageTwoNotice = dgrFetchOne($db, "SELECT intimation_date, audit_dates FROM stage_twoaudit_id WHERE service_id = :typed_id ORDER BY audit_stageii_id DESC LIMIT 1", array(':typed_id' => $typedServiceId));
        $stageIReview = dgrFetchOne($db, "SELECT auditDate, review_date FROM stage_i_audit_id WHERE ca_service_id = :typed_id ORDER BY stage_i_id DESC LIMIT 1", array(':typed_id' => $typedServiceId));
        $stageIIReview = dgrFetchOne($db, "SELECT audit_dates, review_date FROM stage_ii_audit_id WHERE ca_service_id = :typed_id ORDER BY stage_ii_id DESC LIMIT 1", array(':typed_id' => $typedServiceId));
        $surveillancePlan = dgrFetchOne($db, "SELECT audit_dates, prepared_date FROM servelance_1_id WHERE service_id = :typed_id ORDER BY servelance_id DESC LIMIT 1", array(':typed_id' => $typedServiceId));
        $rcAuditPlan = dgrFetchOne($db, "SELECT scheduleii_date, prepared_date FROM audit_scheduleii_id WHERE ca_service = :typed_id ORDER BY audit_scheduleii_id DESC LIMIT 1", array(':typed_id' => $typedServiceId));
        $cdc = dgrFetchCdc($db, $customerId, $typedServiceId);
        $survey = dgrFetchOne($db, "SELECT survey_date, review_date FROM customer_survey_id WHERE customer_ca = :customer_id AND ca_serviceid = :typed_id ORDER BY survey_id DESC LIMIT 1", array(':customer_id' => $customerId, ':typed_id' => $typedServiceId));

        if ($serviceType === 'CA') {
            dgrAddEntry($entries, 'CA', 'CA', 'Stage-I Intimation Date', dgrFirstValue($stageOne, array('intimation_date')), 'stage_oneaudit_id.intimation_date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'CA', 'CA', 'Stage-I Audit Date', dgrFirstValue($stageIReview, array('auditDate')) ?: dgrFirstValue($stageOne, array('audit_dates')), 'stage_i_audit_id.auditDate / stage_oneaudit_id.audit_dates', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'CA', 'CA', 'Stage-I Review Date', dgrFirstValue($stageIReview, array('review_date')), 'stage_i_audit_id.review_date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'CA', 'CA', 'Stage-II Intimation Date', dgrFirstValue($stageTwoNotice, array('intimation_date')), 'stage_twoaudit_id.intimation_date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'CA', 'CA', 'Stage-II Audit Date', dgrFirstValue($stageIIReview, array('audit_dates')) ?: dgrFirstValue($stageTwoNotice, array('audit_dates')), 'stage_ii_audit_id.audit_dates / stage_twoaudit_id.audit_dates', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'CA', 'CA', 'Stage-II Review Date', dgrFirstValue($stageIIReview, array('review_date')), 'stage_ii_audit_id.review_date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'CA', 'CA', 'Assessment / NC(M) Closing Date', dgrFirstValue($cdc, array('assDate', 'Date')), 'cdc_report_review.assDate', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'CA', 'CA', 'CDC Date', dgrFirstValue($cdc, array('Date')), 'cdc_report_review.Date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'CA', 'CA', 'Rev Date / Certificate Issue Date', dgrCertificateIssueDate($db, $cdc), 'cdc_report_review_office_use.rev_date / cdc_report_review.revDatecdc', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'CA', 'CA', 'Feedback Survey Date', dgrFirstValue($survey, array('survey_date', 'review_date')), 'customer_survey_id.survey_date', $typedServiceId, $universalServiceId);
            dgrAddProposedDates($entries, $db, 'CA', 'After CA - Proposed Dates for SA-I', $typedServiceId, $universalServiceId, array(
                'Suspension Reminder' => 'Suspension Reminder Proposed Date',
                'Suspend Letter' => 'Suspend Letter Proposed Date',
                'Withdrawal Reminder' => 'Withdrawal Reminder Proposed Date',
                'Withdraw Letter' => 'Withdraw Letter Proposed Date',
            ));
        } elseif ($serviceType === 'SA-I' || $serviceType === 'SA-II') {
            $section = $serviceType;
            dgrAddEntry($entries, $serviceType, $section, 'Information Updation Review Date', dgrInfoReviewDate($db, $typedServiceId, $customerId), 'info_update_review.review_date / uploads/reviews JSON', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'Intimation Date', dgrFirstValue($stageOne, array('intimation_date')), 'stage_oneaudit_id.intimation_date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'Audit Date', dgrFirstValue($stageIIReview, array('audit_dates')) ?: dgrFirstValue($surveillancePlan, array('audit_dates')) ?: dgrFirstValue($stageOne, array('audit_dates')), 'stage_ii_audit_id.audit_dates / servelance_1_id.audit_dates', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'Review Date', dgrFirstValue($stageIIReview, array('review_date')), 'stage_ii_audit_id.review_date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'Assessment / NC(M) Closing Date', dgrFirstValue($cdc, array('assDate', 'Date')), 'cdc_report_review.assDate', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'CDC Date', dgrFirstValue($cdc, array('Date')), 'cdc_report_review.Date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'Rev Date / Issue Date', dgrCertificateIssueDate($db, $cdc), 'cdc_report_review_office_use.rev_date / cdc_report_review.revDatecdc', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, $serviceType, $section, 'Feedback Survey Date', dgrFirstValue($survey, array('survey_date', 'review_date')), 'customer_survey_id.survey_date', $typedServiceId, $universalServiceId);

            if ($serviceType === 'SA-I') {
                dgrAddProposedDates($entries, $db, 'SA-I', 'After SA-I - Proposed Dates for SA-II', $typedServiceId, $universalServiceId, array(
                    'Suspension Reminder' => 'Suspension Reminder Proposed Date',
                    'Suspend Letter' => 'Suspend Letter Proposed Date',
                    'Withdrawal Reminder' => 'Withdrawal Reminder Proposed Date',
                    'Withdraw Letter' => 'Withdraw Letter Proposed Date',
                ));
            } else {
                dgrAddProposedDates($entries, $db, 'SA-II', 'After SA-II - Proposed Dates for RC', $typedServiceId, $universalServiceId, array(
                    '1st Reminder' => '1st Reminder Proposed Date',
                    '2nd Reminder' => '2nd Reminder Proposed Date',
                    'Expire Letter' => 'Expire Letter Proposed Date',
                ));
            }
        } elseif ($serviceType === 'RC') {
            dgrAddEntry($entries, 'RC', 'RC Audit', 'Intimation Date', dgrFirstValue($stageOne, array('intimation_date')) ?: dgrFirstValue($stageTwoNotice, array('intimation_date')), 'stage_oneaudit_id.intimation_date / stage_twoaudit_id.intimation_date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'RC', 'RC Audit', 'Audit Date', dgrFirstValue($stageIIReview, array('audit_dates')) ?: dgrFirstValue($rcAuditPlan, array('scheduleii_date')) ?: dgrFirstValue($stageOne, array('audit_dates')), 'stage_ii_audit_id.audit_dates / audit_scheduleii_id.scheduleii_date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'RC', 'RC Audit', 'Review Date', dgrFirstValue($stageIIReview, array('review_date')) ?: dgrFirstValue($stageIReview, array('review_date')), 'stage_ii_audit_id.review_date / stage_i_audit_id.review_date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'RC', 'RC Audit', 'Assessment / NC(M) Closing Date', dgrFirstValue($cdc, array('assDate', 'Date')), 'cdc_report_review.assDate', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'RC', 'RC Audit', 'CDC Date', dgrFirstValue($cdc, array('Date')), 'cdc_report_review.Date', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'RC', 'RC Audit', 'Rev Date / Issue Date', dgrCertificateIssueDate($db, $cdc), 'cdc_report_review_office_use.rev_date / cdc_report_review.revDatecdc', $typedServiceId, $universalServiceId);
            dgrAddEntry($entries, 'RC', 'RC Audit', 'Feedback Survey Date', dgrFirstValue($survey, array('survey_date', 'review_date')), 'customer_survey_id.survey_date', $typedServiceId, $universalServiceId);
        }

        $workflowKey = $serviceType . ':' . $universalServiceId . ':' . $typedServiceId;
        for ($i = $serviceEntryStart; $i < count($entries); $i++) {
            $entries[$i]['workflow_key'] = $workflowKey;
        }
    }

    $lastTimestamp = null;
    $lastLabel = '';
    $lastWorkflowKey = '';
    $workflowContext = array();
    $summary = array('ok' => 0, 'issue' => 0, 'missing' => 0, 'total' => count($entries));

    foreach ($entries as $index => $entry) {
        $workflowKey = isset($entry['workflow_key']) ? $entry['workflow_key'] : '';
        if ($workflowKey !== $lastWorkflowKey) {
            $lastTimestamp = null;
            $lastLabel = '';
            $workflowContext = array();
            $lastWorkflowKey = $workflowKey;
        }

        if (empty($entry['dates'])) {
            $entries[$index]['status'] = 'missing';
            $entries[$index]['note'] = 'Date not available';
            $summary['missing']++;
            continue;
        }

        $internalOk = dgrEntryInternalDatesOk($entry);
        $minTimestamp = dgrEntryMinTimestamp($entry);
        $maxTimestamp = dgrEntryMaxTimestamp($entry);
        $validationIssue = $internalOk ? dgrValidationIssueForEntry($entry, $workflowContext, $lastTimestamp, $lastLabel) : '';

        if (!$internalOk) {
            $entries[$index]['status'] = 'issue';
            $entries[$index]['note'] = $entry['label'] . ' dates should be in correct order';
            $summary['issue']++;
        } elseif ($validationIssue !== '') {
            $entries[$index]['status'] = 'issue';
            $entries[$index]['note'] = $validationIssue;
            $summary['issue']++;
        } else {
            $entries[$index]['status'] = 'ok';
            $entries[$index]['note'] = $entry['section'] . ' - ' . $entry['label'] . ' date is correct';
            $summary['ok']++;
        }

        $workflowContext[$entry['label']] = $entry;

        if ($lastTimestamp === null || $maxTimestamp > $lastTimestamp) {
            $lastTimestamp = $maxTimestamp;
            $lastLabel = $entry['section'] . ' - ' . $entry['label'];
        }
    }

    return array('entries' => $entries, 'summary' => $summary);
}

function dgrDateDisplay(array $entry)
{
    if (empty($entry['dates'])) {
        return 'Not available';
    }
    $parts = array();
    foreach ($entry['dates'] as $date) {
        $parts[] = $date['display'];
    }
    return implode(', ', $parts);
}

function dgrIssueLabel(array $entry, $label)
{
    $serviceType = isset($entry['service_type']) ? trim($entry['service_type']) : '';
    $label = trim((string) $label);
    if ($serviceType !== '' && strpos($label, $serviceType . ' - ') === 0) {
        return $label;
    }
    return $serviceType !== '' ? $serviceType . ' - ' . $label : $label;
}

function dgrCompactIssue(array $entry)
{
    $sectionLabel = ($entry['section'] === $entry['service_type']) ? $entry['label'] : $entry['section'] . ' - ' . $entry['label'];
    $left = dgrIssueLabel($entry, $sectionLabel);
    $note = isset($entry['note']) ? $entry['note'] : '';

    if (strpos($note, ' should be greater than or equal to ') !== false) {
        $parts = explode(' should be greater than or equal to ', $note, 2);
        return dgrIssueLabel($entry, $parts[0]) . ' < ' . dgrIssueLabel($entry, $parts[1]);
    }

    if (strpos($note, ' should be greater than ') !== false) {
        $parts = explode(' should be greater than ', $note, 2);
        return dgrIssueLabel($entry, $parts[0]) . ' < ' . dgrIssueLabel($entry, $parts[1]);
    }

    if ($note === 'Application Date should be after QMS Implementation Date') {
        return $left . ' <= QMS Implementation Date';
    }

    if ($note === 'Stage-I Audit Date should be at least 2 months after QMS Implementation Date') {
        return $left . ' < QMS Implementation Date + 2 months';
    }

    if (strpos($note, 'dates should be in correct order') !== false) {
        return $left . ' has dates out of order';
    }

    return $note;
}

function dgrHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (!$db) {
    die('Database connection failed.');
}

$customers = dgrFetchAll(
    $db,
    "SELECT DISTINCT c.id, c.customer_name
     FROM customer_info c
     INNER JOIN service s ON s.customer_id = c.id
     WHERE s.service_type IN ('CA', 'SA-I', 'SA-II', 'RC')
     ORDER BY c.customer_name ASC"
);

$clientsWithIssues = array();
$overall = array('clients_scanned' => 0, 'clients_with_issues' => 0, 'issue_dates' => 0, 'ok' => 0, 'missing' => 0, 'total' => 0);

foreach ($customers as $customer) {
    $overall['clients_scanned']++;
    $graph = dgrBuildForCustomer($db, $customer['id']);
    $overall['ok'] += (int) $graph['summary']['ok'];
    $overall['missing'] += (int) $graph['summary']['missing'];
    $overall['total'] += (int) $graph['summary']['total'];

    $issues = array();
    foreach ($graph['entries'] as $entry) {
        if (isset($entry['status']) && $entry['status'] === 'issue') {
            $issues[] = $entry;
        }
    }

    if (!empty($issues)) {
        $overall['clients_with_issues']++;
        $overall['issue_dates'] += count($issues);
        $clientsWithIssues[] = array(
            'customer' => $customer,
            'summary' => $graph['summary'],
            'issues' => $issues,
        );
    }
}

$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Date Graph Overall Analysis Report');
$pdf->SetMargins(8, 9, 8);
$pdf->SetAutoPageBreak(true, 10);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 8);

$html = '<style>
    h1 { font-size: 17px; margin: 0 0 5px; }
    h2 { font-size: 11px; margin: 8px 0 4px; background-color: #eef2f7; padding: 5px; }
    .meta { color: #555; font-size: 8px; }
    .summary td { border: 1px solid #cbd5e1; padding: 4px; font-size: 8px; }
    table.issue { border-collapse: collapse; width: 100%; margin-bottom: 7px; }
    table.issue th { background-color: #fee2e2; color: #7f1d1d; border: 1px solid #c9c9c9; padding: 4px; font-weight: bold; }
    table.issue td { border: 1px solid #d6d6d6; padding: 4px; vertical-align: top; }
    .small { font-size: 7px; color: #555; }
</style>';

$html .= '<h1>Date Graph Overall Analysis Report</h1>';
$html .= '<div class="meta">Generated: ' . dgrHtml(date('d.m.Y h:i A')) . '</div>';
$html .= '<br><table class="summary" cellpadding="3" cellspacing="0"><tr>';
$html .= '<td><b>Clients Scanned:</b> ' . (int) $overall['clients_scanned'] . '</td>';
$html .= '<td><b>Clients With Issues:</b> ' . (int) $overall['clients_with_issues'] . '</td>';
$html .= '<td><b>Date Issues:</b> ' . (int) $overall['issue_dates'] . '</td>';
$html .= '<td><b>Valid:</b> ' . (int) $overall['ok'] . '</td>';
$html .= '<td><b>Missing:</b> ' . (int) $overall['missing'] . '</td>';
$html .= '<td><b>Total Dates:</b> ' . (int) $overall['total'] . '</td>';
$html .= '</tr></table><br>';

if (empty($clientsWithIssues)) {
    $html .= '<p>No chronological date issues found in scanned client records.</p>';
} else {
    foreach ($clientsWithIssues as $clientIssue) {
        $customer = $clientIssue['customer'];
        $summary = $clientIssue['summary'];
        $issues = $clientIssue['issues'];

        $html .= '<h2>' . dgrHtml($customer['customer_name']) . ' <span class="small">Customer ID: ' . (int) $customer['id'] . ' | Issues: ' . count($issues) . ' | Valid: ' . (int) $summary['ok'] . ' | Missing: ' . (int) $summary['missing'] . ' | Total: ' . (int) $summary['total'] . '</span></h2>';
        $html .= '<table class="issue" cellpadding="3" cellspacing="0">';
        $html .= '<thead><tr><th width="4%">#</th><th width="15%">Service</th><th width="16%">Date Field</th><th width="14%">Date</th><th width="30%">Issue</th><th width="21%">Source</th></tr></thead><tbody>';

        $i = 1;
        foreach ($issues as $entry) {
            $serviceLabel = $entry['service_type'] . '<br><span class="small">Typed: ' . dgrHtml($entry['typed_service_id']) . ' | Service: ' . dgrHtml($entry['universal_service_id']) . '</span>';
            $fieldLabel = $entry['section'] . ' - ' . $entry['label'];
            $dateValue = dgrDateDisplay($entry);
            if (!empty($entry['value']) && $entry['value'] !== $dateValue) {
                $dateValue .= '<br><span class="small">Raw: ' . dgrHtml($entry['value']) . '</span>';
            }

            $html .= '<tr>';
            $html .= '<td width="4%">' . $i . '</td>';
            $html .= '<td width="15%">' . $serviceLabel . '</td>';
            $html .= '<td width="16%">' . dgrHtml($fieldLabel) . '</td>';
            $html .= '<td width="14%">' . $dateValue . '</td>';
            $html .= '<td width="30%">' . dgrHtml(dgrCompactIssue($entry)) . '</td>';
            $html .= '<td width="21%">' . dgrHtml($entry['source']) . '</td>';
            $html .= '</tr>';
            $i++;
        }

        $html .= '</tbody></table>';
    }
}

$pdf->writeHTML($html, true, false, true, false, '');
while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf->Output('Date Graph Overall Analysis Report.pdf', 'I');
exit;

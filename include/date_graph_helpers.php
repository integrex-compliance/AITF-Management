<?php
/**
 * Date Graph Helper Functions
 * 
 * Shared functions used by the Date Graph in customerAccount.php
 * and the chronological date validation endpoint (dateChronologyCheck.php).
 */

if (!function_exists('caDateGraphHasValue')) {
    function caDateGraphHasValue($value)
    {
        $value = trim((string) $value);
        return $value !== '' && strtolower($value) !== 'null' && $value !== '0000-00-00' && $value !== '0000-00-00 00:00:00';
    }

    function caDateGraphParseOneDate($value)
    {
        $value = trim((string) $value);
        if (!caDateGraphHasValue($value)) {
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

    function caDateGraphParseDates($value)
    {
        if (!caDateGraphHasValue($value)) {
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
            $parsed = caDateGraphParseOneDate($candidate);
            if ($parsed && !isset($seen[$parsed['iso']])) {
                $parsed['raw'] = $candidate;
                $dates[] = $parsed;
                $seen[$parsed['iso']] = true;
            }
        }

        return $dates;
    }

    function caDateGraphFetchOne($db, $sql, array $params)
    {
        try {
            $statement = $db->prepare($sql);
            $statement->execute($params);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            return $row ? $row : array();
        } catch (Exception $ex) {
            error_log('Date graph fetch failed: ' . $ex->getMessage());
            return array();
        }
    }

    function caDateGraphFetchAll($db, $sql, array $params)
    {
        try {
            $statement = $db->prepare($sql);
            $statement->execute($params);
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $rows ? $rows : array();
        } catch (Exception $ex) {
            error_log('Date graph fetch failed: ' . $ex->getMessage());
            return array();
        }
    }

    function caDateGraphFirstValue(array $row, array $fields)
    {
        foreach ($fields as $field) {
            if (isset($row[$field]) && caDateGraphHasValue($row[$field])) {
                return $row[$field];
            }
        }
        return '';
    }

    function caDateGraphFetchAssessmentPlanning($db, $customerId, array $serviceIds)
    {
        $seen = array();
        $fallback = array();
        foreach ($serviceIds as $serviceId) {
            $serviceId = trim((string) $serviceId);
            if ($serviceId === '' || isset($seen[$serviceId])) {
                continue;
            }
            $seen[$serviceId] = true;

            $row = caDateGraphFetchOne(
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
            if (caDateGraphFirstValue($row, array('qms_implementation_date', 'date')) !== '') {
                return $row;
            }
        }

        return $fallback;
    }

    function caDateGraphFetchCdc($db, $customerId, $typedServiceId)
    {
        $latest = caDateGraphFetchOne(
            $db,
            "SELECT crr_id, assDate, Date, revDatecdc
             FROM cdc_report_review
             WHERE customer_id = :customer_id
             AND ca_service_id = :typed_id
             ORDER BY crr_id DESC
             LIMIT 1",
            array(':customer_id' => $customerId, ':typed_id' => $typedServiceId)
        );

        if (empty($latest) || caDateGraphHasValue(isset($latest['assDate']) ? $latest['assDate'] : '')) {
            return $latest;
        }

        $closingDateRow = caDateGraphFetchOne(
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

    function caDateGraphResolveCrServiceForCa($db, $customerId, $caUniversalServiceId)
    {
        $row = caDateGraphFetchOne(
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

        $row = caDateGraphFetchOne(
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

    /**
     * Fetch all chronological dates for a specific service.
     * Returns a flat associative array of date keys => ISO date values.
     */
    function caDateGraphFetchChronologyForService($db, $customerId, $typedServiceId, $serviceType, $universalServiceId)
    {
        $dates = array();

        // --- Contract Review dates (CA and RC) ---
        if ($serviceType === 'CA' || $serviceType === 'RC') {
            $contractTypedServiceId = $typedServiceId;
            $contractUniversalServiceId = $universalServiceId;

            $crService = caDateGraphResolveCrServiceForCa($db, $customerId, $universalServiceId);
            if (!empty($crService['typed_service_id'])) {
                $contractTypedServiceId = $crService['typed_service_id'];
                $contractUniversalServiceId = $crService['universal_service_id'];
            }

            $assessment = caDateGraphFetchAssessmentPlanning($db, $customerId, array($contractTypedServiceId, $typedServiceId, $contractUniversalServiceId, $universalServiceId));
            $auditApplication = caDateGraphFetchOne(
                $db,
                "SELECT application_date, application_rcv_date, review_date FROM audit_application WHERE (service_id = :typed_id OR uni_svc_id = :service_id) ORDER BY audit_id DESC LIMIT 1",
                array(':typed_id' => $contractTypedServiceId, ':service_id' => $contractUniversalServiceId)
            );
            $agreement = caDateGraphFetchOne(
                $db,
                "SELECT aggrement_date FROM certification_aggrement WHERE customer_id = :customer_id AND (service = :typed_id OR service = :service_id) ORDER BY id DESC LIMIT 1",
                array(':customer_id' => $customerId, ':typed_id' => $contractTypedServiceId, ':service_id' => $contractUniversalServiceId)
            );

            $dates['qms_implementation_date'] = caDateGraphFirstValue($assessment, array('qms_implementation_date'));
            $dates['application_date'] = caDateGraphFirstValue($auditApplication, array('application_date'));
            $dates['application_rcv_date'] = caDateGraphFirstValue($auditApplication, array('application_rcv_date'));
            $dates['contract_review_date'] = caDateGraphFirstValue($auditApplication, array('review_date'));
            $dates['aggrement_date'] = caDateGraphFirstValue($agreement, array('aggrement_date'));
            $dates['assessment_planning_date'] = caDateGraphFirstValue($assessment, array('date'));
        }

        // --- SA-I/SA-II specific: Information Updation Review ---
        if ($serviceType === 'SA-I' || $serviceType === 'SA-II') {
            $infoRow = caDateGraphFetchOne(
                $db,
                "SELECT review_date, info_form_received_date FROM info_update_review WHERE service_id = :service_id AND customer_id = :customer_id ORDER BY id DESC LIMIT 1",
                array(':service_id' => $typedServiceId, ':customer_id' => $customerId)
            );
            $dates['info_review_date'] = caDateGraphFirstValue($infoRow, array('review_date', 'info_form_received_date'));
        }

        // --- Audit Notification (Stage-I) ---
        $stageOne = caDateGraphFetchOne(
            $db,
            "SELECT intimation_date, audit_dates FROM stage_oneaudit_id WHERE ca_service_id = :typed_id ORDER BY audit_stage_id DESC LIMIT 1",
            array(':typed_id' => $typedServiceId)
        );

        if ($serviceType === 'CA') {
            $dates['stage1_intimation_date'] = caDateGraphFirstValue($stageOne, array('intimation_date'));

            // Stage-I Audit Date from review report, fallback to notification
            $stageIReview = caDateGraphFetchOne(
                $db,
                "SELECT auditDate, review_date FROM stage_i_audit_id WHERE ca_service_id = :typed_id ORDER BY stage_i_id DESC LIMIT 1",
                array(':typed_id' => $typedServiceId)
            );
            $dates['stage1_audit_date'] = caDateGraphFirstValue($stageIReview, array('auditDate'));
            if (!caDateGraphHasValue($dates['stage1_audit_date'])) {
                $dates['stage1_audit_date'] = caDateGraphFirstValue($stageOne, array('audit_dates'));
            }
            $dates['stage1_review_date'] = caDateGraphFirstValue($stageIReview, array('review_date'));

            // Stage-II Notification
            $stageTwoNotice = caDateGraphFetchOne(
                $db,
                "SELECT intimation_date, audit_dates FROM stage_twoaudit_id WHERE service_id = :typed_id ORDER BY audit_stageii_id DESC LIMIT 1",
                array(':typed_id' => $typedServiceId)
            );
            $dates['stage2_intimation_date'] = caDateGraphFirstValue($stageTwoNotice, array('intimation_date'));

            // Stage-II Audit Date
            $stageIIReview = caDateGraphFetchOne(
                $db,
                "SELECT audit_dates, review_date FROM stage_ii_audit_id WHERE ca_service_id = :typed_id ORDER BY stage_ii_id DESC LIMIT 1",
                array(':typed_id' => $typedServiceId)
            );
            $dates['stage2_audit_date'] = caDateGraphFirstValue($stageIIReview, array('audit_dates'));
            if (!caDateGraphHasValue($dates['stage2_audit_date'])) {
                $dates['stage2_audit_date'] = caDateGraphFirstValue($stageTwoNotice, array('audit_dates'));
            }
            $dates['stage2_review_date'] = caDateGraphFirstValue($stageIIReview, array('review_date'));
        } elseif ($serviceType === 'SA-I' || $serviceType === 'SA-II') {
            $dates['intimation_date'] = caDateGraphFirstValue($stageOne, array('intimation_date'));

            $stageIIReview = caDateGraphFetchOne(
                $db,
                "SELECT audit_dates, review_date FROM stage_ii_audit_id WHERE ca_service_id = :typed_id ORDER BY stage_ii_id DESC LIMIT 1",
                array(':typed_id' => $typedServiceId)
            );
            $surveillancePlan = caDateGraphFetchOne(
                $db,
                "SELECT audit_dates, prepared_date FROM servelance_1_id WHERE service_id = :typed_id ORDER BY servelance_id DESC LIMIT 1",
                array(':typed_id' => $typedServiceId)
            );
            $auditDate = caDateGraphFirstValue($stageIIReview, array('audit_dates'));
            if (!caDateGraphHasValue($auditDate)) {
                $auditDate = caDateGraphFirstValue($surveillancePlan, array('audit_dates'));
            }
            if (!caDateGraphHasValue($auditDate)) {
                $auditDate = caDateGraphFirstValue($stageOne, array('audit_dates'));
            }
            $dates['audit_date'] = $auditDate;
            $dates['sa_review_date'] = caDateGraphFirstValue($stageIIReview, array('review_date'));
        } elseif ($serviceType === 'RC') {
            $stageTwoNotice = caDateGraphFetchOne(
                $db,
                "SELECT intimation_date, audit_dates FROM stage_twoaudit_id WHERE service_id = :typed_id ORDER BY audit_stageii_id DESC LIMIT 1",
                array(':typed_id' => $typedServiceId)
            );
            $dates['intimation_date'] = caDateGraphFirstValue($stageOne, array('intimation_date'));
            if (!caDateGraphHasValue($dates['intimation_date'])) {
                $dates['intimation_date'] = caDateGraphFirstValue($stageTwoNotice, array('intimation_date'));
            }

            $stageIIReview = caDateGraphFetchOne(
                $db,
                "SELECT audit_dates, review_date FROM stage_ii_audit_id WHERE ca_service_id = :typed_id ORDER BY stage_ii_id DESC LIMIT 1",
                array(':typed_id' => $typedServiceId)
            );
            $rcAuditPlan = caDateGraphFetchOne(
                $db,
                "SELECT scheduleii_date, prepared_date FROM audit_scheduleii_id WHERE ca_service = :typed_id ORDER BY audit_scheduleii_id DESC LIMIT 1",
                array(':typed_id' => $typedServiceId)
            );
            $auditDate = caDateGraphFirstValue($stageIIReview, array('audit_dates'));
            if (!caDateGraphHasValue($auditDate)) {
                $auditDate = caDateGraphFirstValue($rcAuditPlan, array('scheduleii_date'));
            }
            if (!caDateGraphHasValue($auditDate)) {
                $auditDate = caDateGraphFirstValue($stageOne, array('audit_dates'));
            }
            $dates['audit_date'] = $auditDate;

            $stageIReview = caDateGraphFetchOne(
                $db,
                "SELECT auditDate, review_date FROM stage_i_audit_id WHERE ca_service_id = :typed_id ORDER BY stage_i_id DESC LIMIT 1",
                array(':typed_id' => $typedServiceId)
            );
            $dates['rc_review_date'] = caDateGraphFirstValue($stageIIReview, array('review_date'));
            if (!caDateGraphHasValue($dates['rc_review_date'])) {
                $dates['rc_review_date'] = caDateGraphFirstValue($stageIReview, array('review_date'));
            }
        }

        // --- CDC dates (all service types) ---
        $cdc = caDateGraphFetchCdc($db, $customerId, $typedServiceId);
        $dates['nc_closing_date'] = caDateGraphFirstValue($cdc, array('assDate'));
        $dates['cdc_date'] = caDateGraphFirstValue($cdc, array('Date'));
        $dates['certificate_issue_date'] = caDateGraphFirstValue($cdc, array('revDatecdc'));

        // CDC office use rev_date (higher priority for certificate issue date)
        if (!empty($cdc['crr_id'])) {
            $officeRows = caDateGraphFetchAll(
                $db,
                "SELECT iso_label, rev_date FROM cdc_report_review_office_use WHERE crr_id = :crr_id AND rev_date IS NOT NULL AND rev_date <> '0000-00-00' ORDER BY iso_index ASC",
                array(':crr_id' => $cdc['crr_id'])
            );
            if (!empty($officeRows)) {
                $officeDateParts = array();
                foreach ($officeRows as $officeRow) {
                    if (caDateGraphHasValue($officeRow['rev_date'])) {
                        $officeDateParts[] = $officeRow['rev_date'];
                    }
                }
                if (!empty($officeDateParts)) {
                    $dates['certificate_issue_date'] = implode(', ', $officeDateParts);
                }
            }
        }

        // --- Survey date ---
        $survey = caDateGraphFetchOne(
            $db,
            "SELECT survey_date, review_date FROM customer_survey_id WHERE customer_ca = :customer_id AND ca_serviceid = :typed_id ORDER BY survey_id DESC LIMIT 1",
            array(':customer_id' => $customerId, ':typed_id' => $typedServiceId)
        );
        $dates['survey_date'] = caDateGraphFirstValue($survey, array('survey_date', 'review_date'));

        // Parse all dates to ISO format for easy comparison
        $result = array();
        foreach ($dates as $key => $value) {
            $iso = '';
            if (caDateGraphHasValue($value)) {
                $parsed = caDateGraphParseOneDate($value);
                if ($parsed) {
                    $iso = $parsed['iso'];
                }
            }
            $result[$key] = $iso;
        }

        return $result;
    }

    /**
     * Determine the service type and universal_service_id from a typed_service_id.
     */
    function caDateGraphResolveServiceInfo($db, $typedServiceId)
    {
        // Try CA
        $row = caDateGraphFetchOne($db,
            "SELECT ca.ca_id, s.service_id, s.service_type FROM service_type_ca ca INNER JOIN service s ON ca.service = s.service_id WHERE ca.ca_id = :id LIMIT 1",
            array(':id' => $typedServiceId));
        if (!empty($row['ca_id'])) {
            return array('service_type' => 'CA', 'universal_service_id' => $row['service_id'], 'typed_service_id' => $row['ca_id']);
        }

        // Try SA-I
        $row = caDateGraphFetchOne($db,
            "SELECT sai.sai_id, s.service_id, s.service_type FROM service_type_sai sai INNER JOIN service s ON sai.service = s.service_id WHERE sai.sai_id = :id LIMIT 1",
            array(':id' => $typedServiceId));
        if (!empty($row['sai_id'])) {
            return array('service_type' => 'SA-I', 'universal_service_id' => $row['service_id'], 'typed_service_id' => $row['sai_id']);
        }

        // Try SA-II
        $row = caDateGraphFetchOne($db,
            "SELECT saii.saii_id, s.service_id, s.service_type FROM service_type_saii saii INNER JOIN service s ON saii.service = s.service_id WHERE saii.saii_id = :id LIMIT 1",
            array(':id' => $typedServiceId));
        if (!empty($row['saii_id'])) {
            return array('service_type' => 'SA-II', 'universal_service_id' => $row['service_id'], 'typed_service_id' => $row['saii_id']);
        }

        // Try RC
        $row = caDateGraphFetchOne($db,
            "SELECT rc.rc_id, s.service_id, s.service_type FROM service_type_rc rc INNER JOIN service s ON rc.service = s.service_id WHERE rc.rc_id = :id LIMIT 1",
            array(':id' => $typedServiceId));
        if (!empty($row['rc_id'])) {
            return array('service_type' => 'RC', 'universal_service_id' => $row['service_id'], 'typed_service_id' => $row['rc_id']);
        }

        // Try CR (contract review service, used by audit application / certification agreement)
        $row = caDateGraphFetchOne($db,
            "SELECT crs.cr_id, s.service_id, s.service_type FROM service_type_cr crs INNER JOIN service s ON crs.service = s.service_id WHERE crs.cr_id = :id LIMIT 1",
            array(':id' => $typedServiceId));
        if (!empty($row['cr_id'])) {
            return array('service_type' => 'CR', 'universal_service_id' => $row['service_id'], 'typed_service_id' => $row['cr_id']);
        }

        return array();
    }
}

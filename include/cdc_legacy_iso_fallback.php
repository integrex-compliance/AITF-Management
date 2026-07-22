<?php

include_once __DIR__ . '/cdc_iso_metadata.php';

if (!function_exists('aitfCdcLegacySplitValues')) {
    function aitfCdcLegacySplitValues($value)
    {
        $parts = preg_split('/\s*(?:\|\||,|;|\r\n|\r|\n)\s*/', trim((string) $value));
        $values = array();
        $seen = array();

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            $key = strtolower(preg_replace('/\s+/', ' ', $part));
            if (!isset($seen[$key])) {
                $values[] = $part;
                $seen[$key] = true;
            }
        }

        return $values;
    }
}

if (!function_exists('aitfCdcLegacySplitAccreditations')) {
    function aitfCdcLegacySplitAccreditations($value)
    {
        $values = array();
        $parts = preg_split('/\s*(?:\|\||,|;|\r\n|\r|\n)\s*/', trim((string) $value));
        foreach ($parts as $part) {
            $part = strtoupper(trim($part));
            if (in_array($part, array('NABCB', 'SELF'), true)) {
                $values[] = $part;
            }
        }

        if (empty($values) && preg_match_all('/NABCB|SELF/i', (string) $value, $matches)) {
            foreach ($matches[0] as $match) {
                $values[] = strtoupper($match);
            }
        }

        return $values;
    }
}

if (!function_exists('aitfCdcLegacyNextCertificateNumber')) {
    function aitfCdcLegacyNextCertificateNumber($certificateNumber, $offset)
    {
        $certificateNumber = trim((string) $certificateNumber);
        $offset = (int) $offset;

        if ($offset <= 0 || $certificateNumber === '') {
            return $certificateNumber;
        }

        if (!preg_match('/^(.*?)(\d{3})$/', $certificateNumber, $matches)) {
            return '';
        }

        $nextSuffix = (int) $matches[2] + $offset;
        if ($nextSuffix > 999) {
            return '';
        }

        return $matches[1] . str_pad((string) $nextSuffix, 3, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('aitfBuildLegacyCdcIsoEntries')) {
    function aitfBuildLegacyCdcIsoEntries(PDO $db, $crrId)
    {
        $crrId = (int) $crrId;
        if ($crrId <= 0) {
            return array();
        }

        $statement = $db->prepare(
            "SELECT
                crr.*,
                ci.certification_no AS customer_certificate_no,
                ci.audit_status AS customer_status,
                (
                    SELECT cp.certificate_no
                    FROM certificate_print cp
                    WHERE cp.customer_id = crr.customer_id
                      AND cp.service_id = crr.ca_service_id
                      AND TRIM(COALESCE(cp.certificate_no, '')) <> ''
                    ORDER BY cp.certificate_id DESC
                    LIMIT 1
                ) AS printed_certificate_no
             FROM cdc_report_review crr
             INNER JOIN customer_info ci ON ci.id = crr.customer_id
             WHERE crr.crr_id = :crr_id
             LIMIT 1"
        );
        $statement->bindValue(':crr_id', $crrId, PDO::PARAM_INT);
        $statement->execute();
        $cdc = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$cdc) {
            return array();
        }

        $application = array();
        $applicationStatement = $db->prepare(
            "SELECT
                aa.certification,
                aa.assessment_standard,
                aa.accereditation_requirement,
                aa.review_accredition
             FROM audit_application aa
             INNER JOIN service s ON s.service_id = aa.uni_svc_id
             WHERE s.customer_id = :customer_id
             ORDER BY aa.audit_id DESC
             LIMIT 1"
        );
        $applicationStatement->bindValue(':customer_id', (int) $cdc['customer_id'], PDO::PARAM_INT);
        $applicationStatement->execute();
        $application = $applicationStatement->fetch(PDO::FETCH_ASSOC) ?: array();

        $standardCandidates = array(
            aitfCdcLegacySplitValues($cdc['covered_for'] ?? ''),
            aitfCdcLegacySplitValues($application['assessment_standard'] ?? ''),
            aitfCdcLegacySplitValues($application['certification'] ?? ''),
        );

        $scheduleStatement = $db->prepare(
            "SELECT schedule_app_standard
             FROM audit_schedule_id
             WHERE customer_ca_id = :customer_id
               AND TRIM(COALESCE(schedule_app_standard, '')) <> ''
             ORDER BY audit_schedule_id DESC"
        );
        $scheduleStatement->bindValue(':customer_id', (int) $cdc['customer_id'], PDO::PARAM_INT);
        $scheduleStatement->execute();
        $scheduleStandards = array();
        foreach ($scheduleStatement->fetchAll(PDO::FETCH_COLUMN) as $scheduleValue) {
            foreach (aitfCdcLegacySplitValues($scheduleValue) as $standard) {
                $scheduleStandards[] = $standard;
            }
        }
        $standardCandidates[] = aitfCdcLegacySplitValues(implode(',', $scheduleStandards));

        $standards = array();
        foreach ($standardCandidates as $candidate) {
            if (count($candidate) > count($standards)) {
                $standards = $candidate;
            }
        }
        if (empty($standards)) {
            return array();
        }

        $accreditationCandidates = array(
            aitfCdcLegacySplitAccreditations($cdc['accreditation'] ?? ''),
            aitfCdcLegacySplitAccreditations($application['review_accredition'] ?? ''),
            aitfCdcLegacySplitAccreditations($application['accereditation_requirement'] ?? ''),
        );
        $accreditations = array();
        foreach ($accreditationCandidates as $candidate) {
            if (count($candidate) > count($accreditations)) {
                $accreditations = $candidate;
            }
        }

        $certificateCandidates = aitfCdcLegacySplitValues($cdc['certificateNo'] ?? '');
        if (empty($certificateCandidates)) {
            $certificateCandidates = aitfCdcLegacySplitValues($cdc['customer_certificate_no'] ?? '');
        }
        if (empty($certificateCandidates)) {
            $certificateCandidates = aitfCdcLegacySplitValues($cdc['printed_certificate_no'] ?? '');
        }
        $baseCertificateNumber = $certificateCandidates[0] ?? '';

        $metadataByIndex = array();
        foreach (getCdcIsoMetadata($db, $crrId) as $metadata) {
            $metadataIndex = isset($metadata['iso_index']) ? (int) $metadata['iso_index'] : count($metadataByIndex);
            $metadataByIndex[$metadataIndex] = $metadata;
        }

        $entries = array();
        foreach ($standards as $isoIndex => $standard) {
            $metadata = $metadataByIndex[$isoIndex] ?? array();
            $certificateNumber = $certificateCandidates[$isoIndex] ?? '';
            if ($certificateNumber === '' && $baseCertificateNumber !== '') {
                $certificateNumber = aitfCdcLegacyNextCertificateNumber($baseCertificateNumber, $isoIndex);
            }

            $accreditation = strtoupper(trim((string) ($metadata['accreditation'] ?? '')));
            if (!in_array($accreditation, array('NABCB', 'SELF'), true)) {
                $accreditation = isset($accreditations[$isoIndex])
                    ? $accreditations[$isoIndex]
                    : ($accreditations[0] ?? '');
            }

            $certificationStatus = trim((string) ($metadata['certification_status'] ?? ''));
            if ($certificationStatus === '') {
                $certificationStatus = trim((string) ($cdc['customer_status'] ?? ''));
            }
            if ($certificationStatus === '') {
                $certificationStatus = trim((string) ($cdc['cdc_status'] ?? ''));
            }

            $entries[] = array(
                'crr_id' => $crrId,
                'iso_index' => (int) $isoIndex,
                'iso_label' => trim((string) $standard),
                'certificate_no' => $certificateNumber,
                'rev_no' => $cdc['revCdc'] ?? '',
                'rev_date' => $cdc['revDatecdc'] ?? '',
                'expire_date' => $cdc['expiredate'] ?? '',
                'periodical_audit' => $cdc['periodicalaudit'] ?? '',
                'audit_type' => $cdc['audit_type'] ?? '',
                'comments' => $cdc['comments'] ?? '',
                'accreditation' => $accreditation,
                'certification_status' => $certificationStatus,
                'serviceca_id' => $cdc['ca_service_id'] ?? '',
                'decision_date' => $cdc['Date'] ?? '',
            );
        }

        return $entries;
    }
}

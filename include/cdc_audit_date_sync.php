<?php

function aitfCleanAuditDateValue($value)
{
    if ($value === null) {
        return '';
    }

    $value = trim((string)$value);
    if ($value === '' || strtolower($value) === 'null' || strtolower($value) === 'invalid date') {
        return '';
    }

    $value = preg_replace('/\s+/', ' ', $value);
    return trim($value, " \t\n\r\0\x0B,");
}

function aitfSplitAuditDateValues($value)
{
    $value = aitfCleanAuditDateValue($value);
    if ($value === '') {
        return array();
    }

    $parts = preg_split('/\s*,\s*/', $value);
    $dates = array();
    foreach ($parts as $part) {
        $part = aitfCleanAuditDateValue($part);
        if ($part !== '') {
            $dates[] = $part;
        }
    }

    return $dates;
}

function aitfGetLatestStageAuditDates($db, $serviceId)
{
    $dates = array();
    $queries = array(
        "SELECT audit_dates FROM stage_oneaudit_id WHERE ca_service_id = :service_id ORDER BY audit_stage_id DESC LIMIT 1",
        "SELECT audit_dates FROM stage_twoaudit_id WHERE service_id = :service_id ORDER BY audit_stageii_id DESC LIMIT 1"
    );

    foreach ($queries as $query) {
        $statement = $db->prepare($query);
        $statement->bindParam(':service_id', $serviceId);
        $statement->execute();
        $auditDates = $statement->fetchColumn();

        foreach (aitfSplitAuditDateValues($auditDates) as $date) {
            if (!in_array($date, $dates, true)) {
                $dates[] = $date;
            }
        }
    }

    return implode(', ', $dates);
}

function aitfSyncCdcAuditDateFromStageNotifications($db, $serviceId)
{
    if (!$db || empty($serviceId)) {
        return false;
    }

    $auditDates = aitfGetLatestStageAuditDates($db, $serviceId);
    if ($auditDates === '') {
        return false;
    }

    $statement = $db->prepare(
        "UPDATE cdc_report_review
         SET audit_date = :audit_date
         WHERE ca_service_id = :service_id"
    );
    $statement->bindParam(':audit_date', $auditDates);
    $statement->bindParam(':service_id', $serviceId);
    $statement->execute();

    return $statement->rowCount() > 0;
}

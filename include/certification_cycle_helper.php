<?php

function aitfCertificationServiceTypes()
{
    return array('CA', 'SA-I', 'SA-II', 'RC');
}

function aitfNextCertificationServiceType($serviceType)
{
    $sequence = array(
        'CA' => 'SA-I',
        'SA-I' => 'SA-II',
        'SA-II' => 'RC',
        'RC' => 'SA-I',
    );

    return isset($sequence[$serviceType]) ? $sequence[$serviceType] : '';
}

function aitfNormalizeCertificationServiceType($serviceType)
{
    $value = strtoupper(trim((string) $serviceType));
    $compact = preg_replace('/[^A-Z0-9]/', '', $value);

    if ($compact === 'CA') {
        return 'CA';
    }

    if (in_array($compact, array('SAI', 'SA1', 'SURVEILLANCEI', 'SURVEILLANCE1', 'FIRSTSURVEILLANCE'), true)) {
        return 'SA-I';
    }

    if (in_array($compact, array('SAII', 'SA2', 'SURVEILLANCEII', 'SURVEILLANCE2', 'SECONDSURVEILLANCE'), true)) {
        return 'SA-II';
    }

    if (in_array($compact, array('RC', 'RECERTIFICATION', 'RECERTIFICATIONAUDIT'), true)) {
        return 'RC';
    }

    return '';
}

function aitfClientStatusActionsForServiceType($serviceType)
{
    return array_keys(aitfClientStatusOffsetsForServiceType($serviceType));
}

function aitfAllClientStatusActions()
{
    return array(
        'Suspension Reminder',
        'Suspend Letter',
        'Withdrawal Reminder',
        'Withdrawal Letter',
        'Withdraw Letter',
        '1st Reminder',
        '2nd Reminder',
        'Expire Letter',
    );
}

function aitfClientStatusOffsetsForServiceType($serviceType)
{
    if ($serviceType === 'CA') {
        return array(
            'Suspension Reminder' => 11,
            'Suspend Letter' => 12,
            'Withdrawal Reminder' => 14,
            'Withdraw Letter' => 16,
        );
    }

    if ($serviceType === 'SA-I') {
        return array(
            'Suspension Reminder' => 23,
            'Suspend Letter' => 24,
            'Withdrawal Reminder' => 26,
            'Withdraw Letter' => 28,
        );
    }

    if ($serviceType === 'SA-II') {
        return array(
            '1st Reminder' => 34,
            '2nd Reminder' => 35,
            'Expire Letter' => 36,
        );
    }

    if ($serviceType === 'RC') {
        return array(
            'Suspension Reminder' => 47,
            'Suspend Letter' => 48,
            'Withdrawal Reminder' => 50,
            'Withdraw Letter' => 52,
        );
    }

    return array();
}

function aitfGetUniversalService($db, $serviceId)
{
    if (empty($db) || empty($serviceId)) {
        return null;
    }

    $statement = $db->prepare("SELECT service_id, customer_id, service_type FROM service WHERE service_id = :service_id LIMIT 1");
    $statement->execute(array(':service_id' => $serviceId));
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row ? $row : null;
}

function aitfGetUniversalServiceIdFromTypedService($db, $typedServiceId, $serviceType)
{
    if (empty($db) || empty($typedServiceId) || empty($serviceType)) {
        return null;
    }

    $queries = array(
        'CA' => "SELECT service FROM service_type_ca WHERE ca_id = :id LIMIT 1",
        'SA-I' => "SELECT service FROM service_type_sai WHERE sai_id = :id LIMIT 1",
        'SA-II' => "SELECT service FROM service_type_saii WHERE saii_id = :id LIMIT 1",
        'RC' => "SELECT service FROM service_type_rc WHERE rc_id = :id LIMIT 1",
        'SP' => "SELECT service FROM service_type_sp WHERE sp_id = :id LIMIT 1",
        'TA' => "SELECT service_id AS service FROM service_type_ta WHERE ta_id = :id LIMIT 1",
        'PTR' => "SELECT service FROM service_type_ptr WHERE prt_id = :id LIMIT 1",
    );

    if (!isset($queries[$serviceType])) {
        return null;
    }

    $statement = $db->prepare($queries[$serviceType]);
    $statement->execute(array(':id' => $typedServiceId));
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return ($row && !empty($row['service'])) ? $row['service'] : null;
}

function aitfResolveUniversalServiceId($db, $universalServiceId, $typedServiceId = null, $serviceType = null)
{
    if (!empty($universalServiceId)) {
        $service = aitfGetUniversalService($db, $universalServiceId);
        if ($service) {
            return $service['service_id'];
        }
    }

    if (!empty($typedServiceId) && !empty($serviceType)) {
        return aitfGetUniversalServiceIdFromTypedService($db, $typedServiceId, $serviceType);
    }

    return null;
}

function aitfGetBaseCertificationDate($db, $customerId, $fallbackDate = '')
{
    if (empty($db) || empty($customerId)) {
        return !empty($fallbackDate) ? $fallbackDate : date('Y-m-d');
    }

    $statement = $db->prepare(
        "SELECT MIN(crr.Date) AS first_date
         FROM cdc_report_review crr
         INNER JOIN service_type_ca ca ON crr.ca_service_id = ca.ca_id
         INNER JOIN service s ON ca.service = s.service_id
         WHERE s.customer_id = :customer_id
         AND s.service_type = 'CA'
         AND crr.Date IS NOT NULL
         AND crr.Date <> ''
         AND crr.Date <> '0000-00-00'"
    );
    $statement->execute(array(':customer_id' => $customerId));
    $caCdcResult = $statement->fetch(PDO::FETCH_ASSOC);

    if ($caCdcResult && !empty($caCdcResult['first_date'])) {
        return $caCdcResult['first_date'];
    }

    $statement = $db->prepare("SELECT certification_stage_date FROM customer_info WHERE id = :customer_id LIMIT 1");
    $statement->execute(array(':customer_id' => $customerId));
    $customer = $statement->fetch(PDO::FETCH_ASSOC);

    if ($customer && !empty($customer['certification_stage_date']) && $customer['certification_stage_date'] !== '0000-00-00') {
        return $customer['certification_stage_date'];
    }

    return !empty($fallbackDate) ? $fallbackDate : date('Y-m-d');
}

function aitfGetServiceCycleIndex($db, $customerId, $serviceId, $serviceType)
{
    if (empty($db) || empty($customerId) || empty($serviceId) || !in_array($serviceType, aitfCertificationServiceTypes(), true)) {
        return 0;
    }

    $statement = $db->prepare(
        "SELECT service_id
         FROM service
         WHERE customer_id = :customer_id
         AND service_type = :service_type
         ORDER BY service_id ASC"
    );
    $statement->execute(array(
        ':customer_id' => $customerId,
        ':service_type' => $serviceType,
    ));
    $services = $statement->fetchAll(PDO::FETCH_COLUMN);

    if (!$services) {
        return 0;
    }

    $serviceOrder = array_values($services);
    $index = array_search((string) $serviceId, array_map('strval', $serviceOrder), true);

    if ($index === false) {
        return max(0, count($serviceOrder) - 1);
    }

    return max(0, (int) $index);
}

function aitfGetTypedServiceMap($serviceType = '')
{
    $map = array(
        'CA' => array(
            'table' => 'service_type_ca',
            'typed_column' => 'ca_id',
            'service_column' => 'service',
            'customer_column' => 'customer_id',
        ),
        'SA-I' => array(
            'table' => 'service_type_sai',
            'typed_column' => 'sai_id',
            'service_column' => 'service',
            'customer_column' => 'customer_id',
        ),
        'SA-II' => array(
            'table' => 'service_type_saii',
            'typed_column' => 'saii_id',
            'service_column' => 'service',
            'customer_column' => 'customer_id',
        ),
        'RC' => array(
            'table' => 'service_type_rc',
            'typed_column' => 'rc_id',
            'service_column' => 'service',
            'customer_column' => 'rc_customer',
        ),
    );

    if ($serviceType !== '') {
        return isset($map[$serviceType]) ? $map[$serviceType] : null;
    }

    return $map;
}

function aitfFetchCertificationServiceByTypedId($db, $typedServiceId, $serviceType, $customerId = null)
{
    $map = aitfGetTypedServiceMap($serviceType);
    if (!$map || empty($typedServiceId)) {
        return null;
    }

    $sql = "SELECT s.service_id, s.customer_id, s.service_type, st." . $map['typed_column'] . " AS typed_service_id
            FROM " . $map['table'] . " st
            INNER JOIN service s ON st." . $map['service_column'] . " = s.service_id
            WHERE st." . $map['typed_column'] . " = :typed_id
            AND s.service_type = :service_type
            LIMIT 1";
    $statement = $db->prepare($sql);
    $statement->execute(array(
        ':typed_id' => $typedServiceId,
        ':service_type' => $serviceType,
    ));
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    if (!empty($customerId) && (string) $row['customer_id'] !== (string) $customerId) {
        return null;
    }

    return $row;
}

function aitfFetchTypedServiceIdForUniversalService($db, $universalServiceId, $serviceType, $customerId = null)
{
    $map = aitfGetTypedServiceMap($serviceType);
    if (!$map || empty($universalServiceId)) {
        return null;
    }

    $sql = "SELECT st." . $map['typed_column'] . " AS typed_service_id
            FROM " . $map['table'] . " st
            WHERE st." . $map['service_column'] . " = :service_id";
    $params = array(':service_id' => $universalServiceId);

    if (!empty($customerId)) {
        $sql .= " AND st." . $map['customer_column'] . " = :customer_id";
        $params[':customer_id'] = $customerId;
    }

    $sql .= " LIMIT 1";

    $statement = $db->prepare($sql);
    $statement->execute($params);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return ($row && !empty($row['typed_service_id'])) ? $row['typed_service_id'] : null;
}

function aitfResolveCertificationService($db, $customerId = null, $typedServiceId = null, $universalServiceId = null, $serviceType = '')
{
    if (empty($db)) {
        return null;
    }

    $serviceType = aitfNormalizeCertificationServiceType($serviceType);

    if (!empty($universalServiceId)) {
        $service = aitfGetUniversalService($db, $universalServiceId);
        if ($service) {
            $resolvedType = aitfNormalizeCertificationServiceType($service['service_type']);
            if (in_array($resolvedType, aitfCertificationServiceTypes(), true)) {
                if (!empty($customerId) && (string) $service['customer_id'] !== (string) $customerId) {
                    return null;
                }

                $typedId = !empty($typedServiceId)
                    ? $typedServiceId
                    : aitfFetchTypedServiceIdForUniversalService($db, $service['service_id'], $resolvedType, $service['customer_id']);

                return array(
                    'service_id' => $service['service_id'],
                    'typed_service_id' => $typedId,
                    'customer_id' => $service['customer_id'],
                    'service_type' => $resolvedType,
                );
            }
        }
    }

    if (!empty($typedServiceId) && $serviceType !== '') {
        $typedService = aitfFetchCertificationServiceByTypedId($db, $typedServiceId, $serviceType, $customerId);
        if ($typedService) {
            return array(
                'service_id' => $typedService['service_id'],
                'typed_service_id' => $typedService['typed_service_id'],
                'customer_id' => $typedService['customer_id'],
                'service_type' => $typedService['service_type'],
            );
        }
    }

    if (!empty($typedServiceId)) {
        foreach (aitfCertificationServiceTypes() as $candidateType) {
            $typedService = aitfFetchCertificationServiceByTypedId($db, $typedServiceId, $candidateType, $customerId);
            if ($typedService) {
                return array(
                    'service_id' => $typedService['service_id'],
                    'typed_service_id' => $typedService['typed_service_id'],
                    'customer_id' => $typedService['customer_id'],
                    'service_type' => $typedService['service_type'],
                );
            }
        }
    }

    return null;
}

function aitfIsUsableDateValue($dateValue)
{
    return !empty($dateValue) && $dateValue !== '0000-00-00';
}

function aitfDateAfterMonthsMinusOneDay($baseDate, $months)
{
    if (!aitfIsUsableDateValue($baseDate)) {
        return '';
    }

    try {
        $date = new DateTime($baseDate);
        if ((int) $months !== 0) {
            $date->modify('+' . (int) $months . ' months');
        }
        $date->modify('-1 day');
        return $date->format('Y-m-d');
    } catch (Exception $ex) {
        return '';
    }
}

function aitfBuildCertificatePrintDates($db, $customerId, $typedServiceId = null, $universalServiceId = null, $serviceType = '', array $cdcInfo = array())
{
    $service = aitfResolveCertificationService($db, $customerId, $typedServiceId, $universalServiceId, $serviceType);
    if (!$service) {
        return array();
    }

    if (empty($customerId)) {
        $customerId = $service['customer_id'];
    }

    if (empty($typedServiceId)) {
        $typedServiceId = $service['typed_service_id'];
    }

    if (empty($cdcInfo) && !empty($typedServiceId)) {
        $sql = "SELECT *
                FROM cdc_report_review
                WHERE ca_service_id = :typed_service_id
                AND customer_id = :customer_id
                ORDER BY crr_id DESC
                LIMIT 1";
        $statement = $db->prepare($sql);
        $statement->execute(array(
            ':typed_service_id' => $typedServiceId,
            ':customer_id' => $customerId,
        ));
        $cdcInfo = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$cdcInfo) {
            $cdcInfo = array();
        }
    }

    $registrationDate = '';
    if (isset($cdcInfo['Date']) && aitfIsUsableDateValue($cdcInfo['Date'])) {
        $registrationDate = $cdcInfo['Date'];
    }

    $baseDate = aitfGetBaseCertificationDate($db, $customerId, $registrationDate);
    $serviceType = $service['service_type'];
    $serviceCycleIndex = aitfGetServiceCycleIndex($db, $customerId, $service['service_id'], $serviceType);
    $certificateCycleIndex = ($serviceType === 'RC') ? $serviceCycleIndex + 1 : $serviceCycleIndex;
    $cycleOffsetMonths = 36 * max(0, $certificateCycleIndex);

    $beforeOffsets = array(
        'CA' => 12,
        'SA-I' => 24,
        'SA-II' => 36,
        'RC' => 12,
    );
    $nextAuditLabels = array(
        'CA' => '1st surveillance',
        'SA-I' => '2nd surveillance',
        'SA-II' => 'Re-Certification',
        'RC' => '1st surveillance',
    );

    if ($registrationDate === '') {
        $registrationDate = $baseDate;
    }

    return array(
        'customer_id' => $customerId,
        'service_type' => $serviceType,
        'universal_service_id' => $service['service_id'],
        'typed_service_id' => $typedServiceId,
        'base_date' => $baseDate,
        'cycle_index' => $certificateCycleIndex,
        'registration_date' => $registrationDate,
        'valid_until' => aitfDateAfterMonthsMinusOneDay($baseDate, $cycleOffsetMonths + 36),
        'before_date' => aitfDateAfterMonthsMinusOneDay($baseDate, $cycleOffsetMonths + $beforeOffsets[$serviceType]),
        'next_audit_label' => $nextAuditLabels[$serviceType],
    );
}

function aitfBuildClientStatusDates($db, $serviceId, $customerId = null, $fallbackBaseDate = '')
{
    $service = aitfGetUniversalService($db, $serviceId);
    if (!$service) {
        return array();
    }

    $serviceType = $service['service_type'];
    if (!in_array($serviceType, aitfCertificationServiceTypes(), true)) {
        return array();
    }

    if (empty($customerId)) {
        $customerId = $service['customer_id'];
    }

    $baseDate = aitfGetBaseCertificationDate($db, $customerId, $fallbackBaseDate);
    $cycleIndex = aitfGetServiceCycleIndex($db, $customerId, $serviceId, $serviceType);
    $cycleOffset = 36 * $cycleIndex;
    $offsets = aitfClientStatusOffsetsForServiceType($serviceType);
    $dates = array();

    foreach ($offsets as $action => $months) {
        $dateExpr = $baseDate . ' + ' . ($months + $cycleOffset) . ' MONTH';
        $dates[$action] = date('Y-m-d', strtotime('-1 day', strtotime($dateExpr)));
    }

    return $dates;
}

function aitfSyncClientStatusDates($db, $serviceId, $customerId = null, $fallbackBaseDate = '')
{
    $expectedDates = aitfBuildClientStatusDates($db, $serviceId, $customerId, $fallbackBaseDate);
    if (empty($expectedDates)) {
        return array();
    }

    $expectedActions = array_keys($expectedDates);
    $obsoleteActions = array_values(array_diff(aitfAllClientStatusActions(), $expectedActions));

    $insertSql = "INSERT INTO ca_service_dates(service_id, action, due_date) VALUES (:service_id, :action, :due_date)";
    $insertStatement = $db->prepare($insertSql);
    $selectStatement = $db->prepare(
        "SELECT action_id, status, is_deleted
         FROM ca_service_dates
         WHERE service_id = :service_id
         AND action = :action
         ORDER BY
             CASE WHEN status = 'sent' THEN 0 ELSE 1 END,
             CASE WHEN is_deleted = 'N' THEN 0 ELSE 1 END,
             action_id ASC"
    );
    $updateStatement = $db->prepare(
        "UPDATE ca_service_dates
         SET action = :action, due_date = :due_date, is_deleted = 'N'
         WHERE action_id = :action_id"
    );
    $hideDuplicateStatement = $db->prepare(
        "UPDATE ca_service_dates
         SET action = :action, due_date = :due_date, is_deleted = 'Y'
         WHERE action_id = :action_id"
    );

    foreach ($expectedDates as $action => $dueDate) {
        $selectStatement->execute(array(
            ':service_id' => $serviceId,
            ':action' => $action,
        ));
        $existingRows = $selectStatement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($existingRows) && $action === 'Withdraw Letter') {
            $legacyStatement = $db->prepare(
                "SELECT action_id, status, is_deleted
                 FROM ca_service_dates
                 WHERE service_id = :service_id
                 AND action = 'Withdrawal Letter'
                 ORDER BY
                     CASE WHEN status = 'sent' THEN 0 ELSE 1 END,
                     CASE WHEN is_deleted = 'N' THEN 0 ELSE 1 END,
                     action_id ASC"
            );
            $legacyStatement->execute(array(':service_id' => $serviceId));
            $existingRows = $legacyStatement->fetchAll(PDO::FETCH_ASSOC);
        }

        if (empty($existingRows)) {
            $insertStatement->execute(array(
                ':service_id' => $serviceId,
                ':action' => $action,
                ':due_date' => $dueDate,
            ));
            continue;
        }

        $canonicalRow = array_shift($existingRows);
        $updateStatement->execute(array(
            ':action' => $action,
            ':due_date' => $dueDate,
            ':action_id' => $canonicalRow['action_id'],
        ));

        foreach ($existingRows as $duplicateRow) {
            $hideDuplicateStatement->execute(array(
                ':action' => $action,
                ':due_date' => $dueDate,
                ':action_id' => $duplicateRow['action_id'],
            ));
        }
    }

    if (!empty($obsoleteActions)) {
        $obsoletePlaceholders = implode(',', array_fill(0, count($obsoleteActions), '?'));
        $params = array_merge(array($serviceId), $obsoleteActions);
        $statement = $db->prepare("UPDATE ca_service_dates SET is_deleted = 'Y' WHERE service_id = ? AND action IN ($obsoletePlaceholders)");
        $statement->execute($params);
    }

    return $expectedDates;
}

function aitfSyncExistingClientStatusDatesForCustomer($db, $customerId, $fallbackBaseDate = '')
{
    if (empty($db) || empty($customerId)) {
        return;
    }

    $actions = aitfAllClientStatusActions();
    $placeholders = implode(',', array_fill(0, count($actions), '?'));
    $params = array_merge(array($customerId), $actions);

    $statement = $db->prepare(
        "SELECT DISTINCT s.service_id
         FROM service s
         INNER JOIN ca_service_dates csd ON csd.service_id = s.service_id
         WHERE s.customer_id = ?
         AND s.service_type IN ('CA', 'SA-I', 'SA-II', 'RC')
         AND csd.action IN ($placeholders)"
    );
    $statement->execute($params);
    $serviceIds = $statement->fetchAll(PDO::FETCH_COLUMN);

    foreach ($serviceIds as $serviceId) {
        aitfSyncClientStatusDates($db, $serviceId, $customerId, $fallbackBaseDate);
    }
}

function aitfFetchClientStatusDates($db, $serviceId, array $actions = array())
{
    if (empty($serviceId)) {
        return array();
    }

    if (empty($actions)) {
        $service = aitfGetUniversalService($db, $serviceId);
        $actions = $service ? aitfClientStatusActionsForServiceType($service['service_type']) : array();
    }

    if (empty($actions)) {
        return array();
    }

    $placeholders = implode(',', array_fill(0, count($actions), '?'));
    $params = array_merge(array($serviceId), $actions);

    $statement = $db->prepare(
        "SELECT MIN(action_id) as action_id, service_id, action, due_date, MAX(sent_date) as sent_date, MAX(is_deleted) as is_deleted
         FROM ca_service_dates
         WHERE service_id = ?
         AND action IN ($placeholders)
         AND is_deleted = 'N'
         GROUP BY service_id, action, due_date
         ORDER BY due_date ASC"
    );
    $statement->execute($params);
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array_values(array_filter($rows, function ($row) {
        return !isset($row['is_deleted']) || $row['is_deleted'] !== 'Y';
    }));
}

function aitfLatestCertificationServiceSubquery($aliasName)
{
    return "(SELECT MAX(tmp.service_id)
             FROM service tmp
             WHERE tmp.customer_id = $aliasName.id
             AND tmp.service_type IN ('CA', 'SA-I', 'SA-II', 'RC'))";
}

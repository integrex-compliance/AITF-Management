<?php

require_once __DIR__ . '/iaf_certsearch_helper.php';

function iafDashboardHasRealValue($value)
{
    $value = trim((string) $value);
    return $value !== '' && $value !== '0000-00-00' && strtoupper($value) !== 'N/A';
}

function iafDashboardStandardKey($standardName)
{
    $standardName = strtoupper(trim((string) $standardName));
    if ($standardName === '') {
        return 'NO_STANDARD';
    }

    if (preg_match('/(9001|14001|45001|18001|27001|22000|22301|13485|50001|20000|21001)/', $standardName, $matches)) {
        return 'ISO_' . $matches[1];
    }

    return preg_replace('/[^A-Z0-9]+/', '_', $standardName);
}

function iafDashboardNormalizeApplicationStatus($value)
{
    $status = trim((string) $value);
    if ($status === '') {
        return 'No Status';
    }

    $key = strtolower(str_replace(['-', '_', ' '], '', $status));
    if ($key === 'active' || $key === 'valid' || $key === 'granted' || $key === 'completed' || $key === 'issued') {
        return 'Active';
    }
    if ($key === 'suspended' || $key === 'suspend') {
        return 'Suspended';
    }
    if ($key === 'inprocess' || $key === 'pending') {
        return 'In-process';
    }
    if ($key === 'contractinprocess') {
        return 'Contract In-process';
    }
    if ($key === 'withdrawn' || $key === 'withdraw') {
        return 'Withdrawn';
    }
    if ($key === 'applicationcanceled' || $key === 'applicationcancelled' || $key === 'cancelled' || $key === 'canceled') {
        return 'Application canceled';
    }
    if ($key === 'expired' || $key === 'expire') {
        return 'Expired';
    }

    return $status;
}

function iafDashboardDeriveApplicationStatus(array $client)
{
    $norm = iafDashboardNormalizeApplicationStatus($client['audit_status'] ?? ($client['certification_status'] ?? ''));
    if ($norm === 'Application canceled') {
        return $norm;
    }

    $latestServiceId = trim((string) ($client['latest_service_id'] ?? ''));
    $latestServiceType = trim((string) ($client['latest_service_type'] ?? ''));
    if ($latestServiceId === '' && $latestServiceType === '') {
        $latestServiceId = trim((string) ($client['svc_id'] ?? ''));
        $latestServiceType = trim((string) ($client['svc_type'] ?? ''));
    }

    if ($latestServiceId === '' && $latestServiceType === '') {
        return 'New Enquiry';
    }
    if (strtoupper($latestServiceType) === 'CR') {
        return 'Contract-Review';
    }

    return $norm;
}

function iafDashboardStatusSqlExpression()
{
    $statusKey = "LOWER(REPLACE(REPLACE(REPLACE(TRIM(COALESCE(c.audit_status, '')), '-', ''), '_', ''), ' ', ''))";

    return "CASE
        WHEN $statusKey IN ('applicationcanceled', 'applicationcancelled', 'cancelled', 'canceled') THEN 'Application canceled'
        WHEN latest_svc.service_id IS NULL THEN 'New Enquiry'
        WHEN UPPER(TRIM(latest_svc.service_type)) = 'CR' THEN 'Contract-Review'
        WHEN $statusKey IN ('active', 'valid', 'granted', 'completed', 'issued') THEN 'Active'
        WHEN $statusKey IN ('suspended', 'suspend') THEN 'Suspended'
        WHEN $statusKey IN ('inprocess', 'pending') THEN 'In-process'
        WHEN $statusKey = 'contractinprocess' THEN 'Contract In-process'
        WHEN $statusKey IN ('withdrawn', 'withdraw') THEN 'Withdrawn'
        WHEN $statusKey IN ('expired', 'expire') THEN 'Expired'
        WHEN $statusKey = '' THEN 'No Status'
        ELSE TRIM(c.audit_status)
    END";
}

function iafDashboardSortStatuses(array $statuses)
{
    $priority = [
        'New Enquiry',
        'Contract-Review',
        'In-process',
        'Active',
        'Contract In-process',
        'Withdrawn',
        'Suspended',
        'Expired',
        'Application canceled',
        'No Status',
    ];
    $priorityMap = array_flip($priority);
    $statuses = array_values(array_unique(array_filter(array_map('trim', $statuses), 'strlen')));

    usort($statuses, function ($a, $b) use ($priorityMap) {
        $aKnown = array_key_exists($a, $priorityMap);
        $bKnown = array_key_exists($b, $priorityMap);
        if ($aKnown && $bKnown) {
            return $priorityMap[$a] <=> $priorityMap[$b];
        }
        if ($aKnown) {
            return -1;
        }
        if ($bKnown) {
            return 1;
        }
        return strcasecmp($a, $b);
    });

    return $statuses;
}

function iafDashboardFetchStatusOptions(PDO $db)
{
    $statusExpr = iafDashboardStatusSqlExpression();
    $sql = "SELECT DISTINCT $statusExpr AS application_status
        FROM customer_info c
        INNER JOIN (
            SELECT customer_id, MAX(service_id) AS latest_service_id
            FROM service
            WHERE customer_id IS NOT NULL
            GROUP BY customer_id
        ) latest_idx ON latest_idx.customer_id = c.id
        LEFT JOIN service latest_svc ON latest_svc.service_id = latest_idx.latest_service_id
        WHERE c.is_deleted <> 'YES'
        ORDER BY application_status ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute();

    return iafDashboardSortStatuses($stmt->fetchAll(PDO::FETCH_COLUMN));
}

function iafDashboardRowScore(array $client)
{
    $status = strtolower(trim((string) ($client['certification_status'] ?? '')));
    $score = 0;

    if (iafDashboardHasRealValue($client['certificate_number'] ?? '')) {
        $score += 1000000000;
    }
    if (iafDashboardHasRealValue($client['certificate_issue_date'] ?? '') || iafDashboardHasRealValue($client['issue_date'] ?? '')) {
        $score += 100000000;
    }
    if (iafDashboardHasRealValue($client['expire_date'] ?? '') || iafDashboardHasRealValue($client['expiry_date'] ?? '')) {
        $score += 100000000;
    }
    if (iafDashboardHasRealValue($client['cdc_office_use_id'] ?? '')) {
        $score += 10000000;
    }
    if (iafDashboardHasRealValue($client['certificate_tracking_id'] ?? '')) {
        $score += 10000000;
    }
    if ($status === 'granted' || $status === 'completed' || strpos($status, 'grant') !== false || strpos($status, 'active') !== false || strpos($status, 'valid') !== false) {
        $score += 1;
    }

    return $score + min(max((int) ($client['svc_id'] ?? 0), 0), 9999999);
}

function iafDashboardNormalizeClient(array $client)
{
    $client = iafCertSearchNormalizeClientData($client);
    $client['certification_status'] = iafDashboardDeriveApplicationStatus($client);
    $client['certification_scope'] = iafCertSearchFirstValue($client, ['certification_scope', 'proposed_scope', 'certificate_scope', 'scope']);
    $client['scheme_name'] = iafCertSearchFirstValue($client, ['scheme_name', 'schema_name']);
    $client['accreditation_body'] = iafCertSearchNormalizeAccreditationBodyName(iafCertSearchFirstValue($client, ['accreditation_body', 'accreditation_body_name', 'accreditation_requirement', 'accereditation_requirement'], 'NABCB'));
    $client['certificate_issue_date'] = iafCertSearchFirstValue($client, ['certificate_issue_date']);
    $client['issue_date'] = iafCertSearchFirstValue($client, ['issue_date', 'certificate_issue_date']);
    $client['expire_date'] = iafCertSearchFirstValue($client, ['expire_date', 'expiry_date']);

    return $client;
}

function iafDashboardDedupeRows(array $rows)
{
    $deduped = [];
    foreach ($rows as $client) {
        $client = iafDashboardNormalizeClient($client);
        $standardKey = iafDashboardStandardKey($client['standard_name'] ?? '');
        $dedupeKey = ($client['internal_client_id'] ?? '') . '|' . $standardKey;

        if (!isset($deduped[$dedupeKey]) || iafDashboardRowScore($client) > iafDashboardRowScore($deduped[$dedupeKey])) {
            $deduped[$dedupeKey] = $client;
        }
    }

    return array_values($deduped);
}

function iafDashboardServiceRowsSql($whereSql = '')
{
    $currentServiceId = "CASE
        WHEN UPPER(svc.service_type) = 'CA' THEN stca.ca_id
        WHEN UPPER(svc.service_type) IN ('SA-I', 'SA1', 'SAI') THEN stsai.sai_id
        WHEN UPPER(svc.service_type) IN ('SA-II', 'SA2', 'SAII') THEN stsaii.saii_id
        WHEN UPPER(svc.service_type) = 'RC' THEN strc.rc_id
        ELSE svc.service_id
    END";
    $certificatePrintExpiry = "CASE
        WHEN UPPER(TRIM(cdc.audit_type)) = 'CA'
            THEN DATE_SUB(DATE_ADD(COALESCE(ca_dates.certificate_issue_date, c.certification_stage_date, cdc.Date), INTERVAL 36 MONTH), INTERVAL 1 DAY)
        ELSE NULL
    END";

    return "SELECT
        c.id as internal_client_id,
        NULLIF(TRIM(c.unique_id), '') as customer_guid,
        NULLIF(TRIM(c.unique_id), '') as unique_id,
        c.customer_name,
        'India' as country,
        c.address,
        c.scope,
        svc.service_id as svc_id,
        svc.service_type as svc_type,
        c.audit_status as audit_status,
        latest_svc.service_id as latest_service_id,
        latest_svc.service_type as latest_service_type,
        cou.id as cdc_office_use_id,
        cou.iso_index as iso_index,
        COALESCE(
            NULLIF(TRIM(cdc.cdc_status), ''),
            NULLIF(TRIM(stca.service_ca_status), ''),
            NULLIF(TRIM(stsai.service_sai_status), ''),
            NULLIF(TRIM(stsaii.service_saii_ststus), ''),
            NULLIF(TRIM(strc.service_rc_status), ''),
            NULLIF(TRIM(svc.service_status), ''),
            c.audit_status
        ) as certification_status,
        cp.certificate_id as certificate_tracking_id,
        COALESCE(NULLIF(cou.certificate_no, ''), NULLIF(cp.certificate_no, ''), NULLIF(cdc.certificateNo, '')) as certificate_number,
        COALESCE(ca_dates.certificate_issue_date, c.certification_stage_date) as certificate_issue_date,
        COALESCE(NULLIF(NULLIF(cou.rev_date, ''), '0000-00-00'), NULLIF(NULLIF(cdc.revDatecdc, ''), '0000-00-00'), NULLIF(NULLIF(cp.registration_date, ''), '0000-00-00'), NULLIF(NULLIF(cdc.Date, ''), '0000-00-00')) as issue_date,
        COALESCE(ca_dates.certificate_issue_date, c.certification_stage_date) as original_issue_date,
        COALESCE(NULLIF(NULLIF(cp.valid_until, ''), '0000-00-00'), $certificatePrintExpiry, NULLIF(NULLIF(cou.expire_date, ''), '0000-00-00'), NULLIF(NULLIF(cdc.expiredate, ''), '0000-00-00')) as expiry_date,
        COALESCE(NULLIF(NULLIF(cp.valid_until, ''), '0000-00-00'), $certificatePrintExpiry, NULLIF(NULLIF(cou.expire_date, ''), '0000-00-00'), NULLIF(NULLIF(cdc.expiredate, ''), '0000-00-00')) as expire_date,
        cp.certificate_scope,
        COALESCE(NULLIF(aa.proposed_scope, ''), cp.certificate_scope, c.scope) as certification_scope,
        COALESCE(
            NULLIF(TRIM(aa.accereditation_requirement), ''),
            NULLIF(TRIM(aa.review_accredition), '')
        ) as accreditation_body_name,
        COALESCE(
            NULLIF(TRIM(aa.accereditation_requirement), ''),
            NULLIF(TRIM(aa.review_accredition), '')
        ) as accreditation_body,
        NULLIF(TRIM(aa.accereditation_requirement), '') as accreditation_requirement,
        COALESCE(
            NULLIF(cou.iso_label, ''),
            NULLIF(aa.assessment_standard, ''),
            (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id AND asi.serviceca_id = $currentServiceId ORDER BY asi.audit_schedule_id DESC LIMIT 1),
            (SELECT asi2.schedule_app_standard FROM audit_schedule_id asi2 WHERE asi2.customer_ca_id = c.id ORDER BY asi2.audit_schedule_id DESC LIMIT 1)
        ) as standard_name,
        '' as schema_name,
        '' as scheme_name,
        aa.n_location as site
    FROM customer_info c
    INNER JOIN service svc ON svc.customer_id = c.id
    LEFT JOIN service latest_svc ON latest_svc.service_id = (
        SELECT MAX(s_latest.service_id)
        FROM service s_latest
        WHERE s_latest.customer_id = c.id
    )
    LEFT JOIN service_type_ca stca ON stca.service = svc.service_id
    LEFT JOIN service_type_sai stsai ON stsai.service = svc.service_id
    LEFT JOIN service_type_saii stsaii ON stsaii.service = svc.service_id
    LEFT JOIN service_type_rc strc ON strc.service = svc.service_id
    LEFT JOIN (
        SELECT cp_s.service_id, MAX(cp_s.certificate_id) as certificate_id
        FROM certificate_print cp_s
        GROUP BY cp_s.service_id
    ) cp_latest ON cp_latest.service_id = $currentServiceId
    LEFT JOIN certificate_print cp ON cp.certificate_id = cp_latest.certificate_id
    LEFT JOIN cdc_report_review cdc ON cdc.crr_id = COALESCE(stca.crr_id, stsai.crr_id, stsaii.crr_id, strc.crr_id)
    LEFT JOIN cdc_report_review_office_use cou ON cou.crr_id = cdc.crr_id
    LEFT JOIN (
        SELECT svc0.customer_id,
            COALESCE(
                MIN(NULLIF(cdc_ca.Date, '0000-00-00')),
                MIN(NULLIF(cou_ca.rev_date, '0000-00-00'))
            ) as certificate_issue_date
        FROM service svc0
        INNER JOIN service_type_ca ca0 ON ca0.service = svc0.service_id
        LEFT JOIN cdc_report_review cdc_ca ON cdc_ca.ca_service_id = ca0.ca_id AND cdc_ca.customer_id = svc0.customer_id
        LEFT JOIN cdc_report_review_office_use cou_ca ON cou_ca.crr_id = cdc_ca.crr_id
        WHERE UPPER(svc0.service_type) = 'CA'
        GROUP BY svc0.customer_id
    ) ca_dates ON ca_dates.customer_id = c.id
    LEFT JOIN audit_application aa ON aa.audit_id = (
        SELECT MAX(aa_s.audit_id) FROM audit_application aa_s
        WHERE aa_s.uni_svc_id = svc.service_id OR aa_s.service_id = svc.service_id OR aa_s.service_id = $currentServiceId
    )
    WHERE NOT (UPPER(svc.service_type) = 'CR' AND cp.certificate_id IS NULL AND cdc.crr_id IS NULL)
    $whereSql";
}

function iafDashboardFetchRows(PDO $db, $offset = 0, $limit = 30, $search = '', $status = '')
{
    $offset = max(0, (int) $offset);
    $limit = min(50, max(1, (int) $limit));
    $rawLimit = $limit;
    $search = trim((string) $search);
    $status = trim((string) $status);
    $statusExpr = iafDashboardStatusSqlExpression();
    $useDedupedPagination = ($search !== '' || $status !== '');
    $prefetchLimit = min(250, max($rawLimit * 4, $rawLimit + 1));

    // Pagination must be based on the final deduped dashboard rows, not raw
    // services. A client can have multiple service rows for the same displayed
    // certificate/standard, so raw-service offsets can repeat rows and keep
    // "Next" enabled after the filtered client list is exhausted.
    $idSql = "SELECT svc.service_id
        FROM customer_info c
        INNER JOIN service svc ON svc.customer_id = c.id
        LEFT JOIN (
            SELECT customer_id, MAX(service_id) AS latest_service_id
            FROM service
            WHERE customer_id IS NOT NULL
            GROUP BY customer_id
        ) latest_idx ON latest_idx.customer_id = c.id
        LEFT JOIN service latest_svc ON latest_svc.service_id = latest_idx.latest_service_id
        WHERE 1=1";
    $idParams = [];
    if ($search !== '') {
        $idSql .= " AND c.customer_name LIKE ?";
        $idParams[] = '%' . $search . '%';
    }
    if ($status !== '') {
        $idSql .= " AND $statusExpr = ?";
        $idParams[] = $status;
    }
    $idSql .= " ORDER BY c.id DESC, svc.service_id ASC";
    if (!$useDedupedPagination) {
        $idSql .= " LIMIT $prefetchLimit OFFSET $offset";
    }

    $idStmt = $db->prepare($idSql);
    $idStmt->execute($idParams);
    $serviceIds = array_map('intval', $idStmt->fetchAll(PDO::FETCH_COLUMN));

    if (empty($serviceIds)) {
        return [
            'rows' => [],
            'next_offset' => $offset,
            'has_more' => false,
        ];
    }

    if (!$useDedupedPagination) {
        $whereSql = " AND svc.service_id IN (" . implode(',', array_fill(0, count($serviceIds), '?')) . ")";
        $sql = iafDashboardServiceRowsSql($whereSql) . "
            ORDER BY c.id DESC, svc.service_id ASC, cou.iso_index ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($serviceIds);
        $rawRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rows = array_slice(iafDashboardDedupeRows($rawRows), 0, $rawLimit);

        return [
            'rows' => $rows,
            'next_offset' => $offset + $rawLimit,
            'has_more' => count($serviceIds) > $rawLimit,
        ];
    }

    $rawRows = [];
    foreach (array_chunk($serviceIds, 250) as $serviceIdChunk) {
        $whereSql = " AND svc.service_id IN (" . implode(',', array_fill(0, count($serviceIdChunk), '?')) . ")";
        $sql = iafDashboardServiceRowsSql($whereSql) . "
            ORDER BY c.id DESC, svc.service_id ASC, cou.iso_index ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($serviceIdChunk);
        $chunkRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($chunkRows)) {
            $rawRows = array_merge($rawRows, $chunkRows);
        }
    }

    $dedupedRows = iafDashboardDedupeRows($rawRows);
    $rows = array_slice($dedupedRows, $offset, $rawLimit);
    $nextOffset = $offset + count($rows);

    return [
        'rows' => $rows,
        'next_offset' => $nextOffset,
        'has_more' => count($dedupedRows) > $nextOffset,
    ];
}

function iafDashboardFetchClientRow(PDO $db, $clientId, $svcId, $officeUseId = 0)
{
    $whereSql = " AND c.id = :client_id";
    $params = [':client_id' => $clientId];

    if ($svcId !== '' && $svcId !== null) {
        $whereSql .= " AND svc.service_id = :svc_id";
        $params[':svc_id'] = $svcId;
    }
    if ((int) $officeUseId > 0) {
        $whereSql .= " AND cou.id = :office_use_id";
        $params[':office_use_id'] = (int) $officeUseId;
    }

    $sql = iafDashboardServiceRowsSql($whereSql) . "
        ORDER BY cou.iso_index ASC
        LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? iafDashboardNormalizeClient($row) : null;
}

/**
 * Minimal identity lookup used by the delete flow. We only need the fields
 * required to rebuild the Certified Entity GUID and address/country for the
 * ce-by-guid resolve call — not the full dashboard row.
 *
 * The GUID IS our system's agreement / unique id (e.g. "AITF/January-26/1"),
 * generated in Contract Review under the certificate agreement section and
 * stored in certification_aggrement.aggrement_no. That is exactly the value
 * sent as certified_entity_unique_id on insert, so the Certified Entity on the
 * IAF portal is keyed by it. We prefer customer_info.unique_id when present
 * (it mirrors the same value) and fall back to the original (earliest)
 * agreement number, since some clients have an empty customer_info.unique_id.
 */
function iafDashboardFetchClientIdentity(PDO $db, $clientId)
{
    $stmt = $db->prepare("SELECT
            c.id AS internal_client_id,
            c.customer_name,
            c.address,
            COALESCE(
                NULLIF(TRIM(c.unique_id), ''),
                (
                    SELECT NULLIF(TRIM(ca.aggrement_no), '')
                    FROM certification_aggrement ca
                    WHERE ca.customer_id = c.id
                      AND TRIM(COALESCE(ca.aggrement_no, '')) <> ''
                    ORDER BY ca.aggrement_date ASC, ca.id ASC
                    LIMIT 1
                )
            ) AS unique_id,
            COALESCE(
                NULLIF(TRIM(c.unique_id), ''),
                (
                    SELECT NULLIF(TRIM(ca.aggrement_no), '')
                    FROM certification_aggrement ca
                    WHERE ca.customer_id = c.id
                      AND TRIM(COALESCE(ca.aggrement_no, '')) <> ''
                    ORDER BY ca.aggrement_date ASC, ca.id ASC
                    LIMIT 1
                )
            ) AS customer_guid,
            'India' AS country
        FROM customer_info c
        WHERE c.id = :cid
        LIMIT 1");
    $stmt->execute([':cid' => (int) $clientId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function iafDashboardFormatDate($date)
{
    if (!iafDashboardHasRealValue($date)) {
        return '-';
    }

    $date = trim((string) $date);
    foreach (['Y-m-d', 'd.m.Y', 'd-m-Y', 'd/m/Y'] as $format) {
        $dateTime = DateTime::createFromFormat('!' . $format, $date);
        if ($dateTime instanceof DateTime && $dateTime->format($format) === $date) {
            return $dateTime->format('d M Y');
        }
    }

    $time = strtotime($date);
    return $time ? date('d M Y', $time) : '-';
}

function iafDashboardStatusBadge(array $client)
{
    $displayStatus = iafDashboardNormalizeApplicationStatus($client['certification_status'] ?? '');
    $statusKey = strtolower(str_replace(['-', '_', ' '], '', $displayStatus));
    $badgeClass = 'status-default';

    if ($statusKey === 'active' || $statusKey === 'granted' || $statusKey === 'valid' || $statusKey === 'completed' || $statusKey === 'issued') {
        $badgeClass = 'status-active';
        $displayStatus = 'Active';
    } elseif ($statusKey === 'inprocess' || $statusKey === 'contractreview' || $statusKey === 'newenquiry' || $statusKey === 'contractinprocess' || $statusKey === 'pending') {
        $badgeClass = 'status-pending';
    } elseif ($statusKey === 'suspended' || $statusKey === 'expired' || $statusKey === 'withdrawn' || $statusKey === 'applicationcanceled' || $statusKey === 'cancelled' || $statusKey === 'canceled') {
        $badgeClass = 'status-expired';
    }

    return [
        'class' => $badgeClass,
        'label' => $displayStatus !== '' ? $displayStatus : 'Unknown',
    ];
}

function iafDashboardRenderRow(array $c)
{
    $insertId = 'btn-insert-' . ($c['internal_client_id'] ?? '') . '-' . (($c['svc_id'] ?? '') ?: '0') . '-' . (($c['cdc_office_use_id'] ?? '') ?: '0') . '-' . substr(md5(($c['standard_name'] ?? '') . '|' . ($c['certificate_number'] ?? '')), 0, 10);
    $clientJson = htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8');
    $clientId = htmlspecialchars($c['internal_client_id'] ?? '', ENT_QUOTES, 'UTF-8');
    $rowKey = htmlspecialchars(($c['internal_client_id'] ?? '') . '|' . (($c['svc_id'] ?? '') ?: '0') . '|' . (($c['cdc_office_use_id'] ?? '') ?: '0') . '|' . iafDashboardStandardKey($c['standard_name'] ?? ''), ENT_QUOTES, 'UTF-8');

    $statusBadge = iafDashboardStatusBadge($c);
    $badgeClass = $statusBadge['class'];
    $displayStatus = $statusBadge['label'];

    ob_start();
    ?>
    <tr data-row-key="<?php echo $rowKey; ?>">
        <td>
            <div class="action-btns-container">
                <button class="action-btn btn-insert"
                    id="<?php echo htmlspecialchars($insertId, ENT_QUOTES, 'UTF-8'); ?>"
                    onclick='handleAction("insert", <?php echo $clientJson; ?>, this)'>
                    <i class="fa fa-cloud-upload"></i> Insert
                </button>
                <button class="action-btn btn-update"
                    onclick='handleAction("update", <?php echo $clientJson; ?>, this)'>
                    <i class="fa fa-refresh"></i> Update
                </button>
                <button class="action-btn btn-delete"
                    onclick='handleAction("delete", "<?php echo $clientId; ?>", this)'>
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </td>
        <td class="company-name">
            <a href="customerAccount.php?customerid=<?php echo $clientId; ?>" class="company-link" target="_blank">
                <?php echo htmlspecialchars($c['customer_name'] ?? ''); ?>
            </a>
        </td>
        <td><?php echo htmlspecialchars($c['country'] ?? 'India'); ?></td>
        <td>
            <div class="cell-ellipsis" title="<?php echo htmlspecialchars($c['address'] ?? ''); ?>">
                <?php echo htmlspecialchars($c['address'] ?? ''); ?>
            </div>
        </td>
        <td><?php echo htmlspecialchars(($c['accreditation_body'] ?? '') ?: 'National Accreditation Board for Certification Bodies'); ?></td>
        <td><?php echo htmlspecialchars(($c['standard_name'] ?? '') ?: 'N/A'); ?></td>
        <td><span style="font-weight: 600; color: #475569;"><?php echo htmlspecialchars($c['scheme_name'] ?? ''); ?></span></td>
        <td>
            <div class="cell-ellipsis" title="<?php echo htmlspecialchars($c['certification_scope'] ?? ''); ?>">
                <?php echo htmlspecialchars(($c['certification_scope'] ?? '') ?: 'N/A'); ?>
            </div>
        </td>
        <td>
            <?php if (!empty($c['certificate_number'])): ?>
                <span class="cert-number"><?php echo htmlspecialchars($c['certificate_number']); ?></span>
            <?php else: ?>
                <span style="color: #94a3b8;">Pending</span>
            <?php endif; ?>
        </td>
        <td><span class="status-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($displayStatus); ?></span></td>
        <td><?php echo htmlspecialchars($c['certification_type'] ?? 'Management System'); ?></td>
        <td><?php echo iafDashboardFormatDate($c['certificate_issue_date'] ?? ''); ?></td>
        <td><?php echo iafDashboardFormatDate($c['issue_date'] ?? ''); ?></td>
        <td><?php echo iafDashboardFormatDate($c['expire_date'] ?? ''); ?></td>
    </tr>
    <?php
    return trim(ob_get_clean());
}

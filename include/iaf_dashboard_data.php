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

function iafDashboardRowScore(array $client)
{
    $status = strtolower(trim((string) ($client['certification_status'] ?? '')));
    $score = 0;

    if (iafDashboardHasRealValue($client['certificate_number'] ?? '')) {
        $score += 1000;
    }
    if ($status === 'granted' || strpos($status, 'grant') !== false || strpos($status, 'active') !== false || strpos($status, 'valid') !== false) {
        $score += 200;
    }
    if (iafDashboardHasRealValue($client['certificate_issue_date'] ?? '') || iafDashboardHasRealValue($client['issue_date'] ?? '')) {
        $score += 80;
    }
    if (iafDashboardHasRealValue($client['expire_date'] ?? '') || iafDashboardHasRealValue($client['expiry_date'] ?? '')) {
        $score += 80;
    }
    if (iafDashboardHasRealValue($client['cdc_office_use_id'] ?? '')) {
        $score += 20;
    }
    if (iafDashboardHasRealValue($client['certificate_tracking_id'] ?? '')) {
        $score += 10;
    }

    return $score;
}

function iafDashboardNormalizeClient(array $client)
{
    $client = iafCertSearchNormalizeClientData($client);
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
        COALESCE(cou.rev_date, cp.registration_date, cdc.Date) as issue_date,
        COALESCE(ca_dates.certificate_issue_date, c.certification_stage_date) as original_issue_date,
        COALESCE(NULLIF(NULLIF(cp.valid_until, ''), '0000-00-00'), $certificatePrintExpiry, NULLIF(NULLIF(latest_customer_cp.valid_until, ''), '0000-00-00'), NULLIF(NULLIF(cou.expire_date, ''), '0000-00-00'), NULLIF(NULLIF(cdc.expiredate, ''), '0000-00-00')) as expiry_date,
        COALESCE(NULLIF(NULLIF(cp.valid_until, ''), '0000-00-00'), $certificatePrintExpiry, NULLIF(NULLIF(latest_customer_cp.valid_until, ''), '0000-00-00'), NULLIF(NULLIF(cou.expire_date, ''), '0000-00-00'), NULLIF(NULLIF(cdc.expiredate, ''), '0000-00-00')) as expire_date,
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
    LEFT JOIN (
        SELECT latest_cp.customer_id, cp_latest_customer.valid_until
        FROM (
            SELECT customer_id, MAX(certificate_id) as certificate_id
            FROM (
                SELECT ca_cp.customer_id, cp_ca.certificate_id
                FROM service_type_ca ca_cp
                INNER JOIN certificate_print cp_ca ON cp_ca.service_id = ca_cp.ca_id
                UNION ALL
                SELECT sai_cp.customer_id, cp_sai.certificate_id
                FROM service_type_sai sai_cp
                INNER JOIN certificate_print cp_sai ON cp_sai.service_id = sai_cp.sai_id
                UNION ALL
                SELECT saii_cp.customer_id, cp_saii.certificate_id
                FROM service_type_saii saii_cp
                INNER JOIN certificate_print cp_saii ON cp_saii.service_id = saii_cp.saii_id
                UNION ALL
                SELECT rc_cp.rc_customer as customer_id, cp_rc.certificate_id
                FROM service_type_rc rc_cp
                INNER JOIN certificate_print cp_rc ON cp_rc.service_id = rc_cp.rc_id
            ) all_customer_cp
            GROUP BY customer_id
        ) latest_cp
        INNER JOIN certificate_print cp_latest_customer ON cp_latest_customer.certificate_id = latest_cp.certificate_id
    ) latest_customer_cp ON latest_customer_cp.customer_id = c.id
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

function iafDashboardFetchRows(PDO $db, $offset = 0, $limit = 12, $search = '')
{
    $offset = max(0, (int) $offset);
    $limit = min(50, max(1, (int) $limit));
    $rawLimit = $limit;
    $params = [];
    $whereSql = '';

    if (trim((string) $search) !== '') {
        $whereSql .= " AND c.customer_name LIKE :search";
        $params[':search'] = '%' . trim((string) $search) . '%';
    }

    $sql = iafDashboardServiceRowsSql($whereSql) . "
        ORDER BY c.id DESC, svc.service_id ASC, cou.iso_index ASC
        LIMIT $rawLimit OFFSET $offset";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rawRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $rows = array_slice(iafDashboardDedupeRows($rawRows), 0, $limit);

    return [
        'rows' => $rows,
        'next_offset' => $offset + count($rawRows),
        'has_more' => count($rawRows) === $rawLimit,
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

function iafDashboardRenderRow(array $c)
{
    $insertId = 'btn-insert-' . ($c['internal_client_id'] ?? '') . '-' . (($c['svc_id'] ?? '') ?: '0') . '-' . (($c['cdc_office_use_id'] ?? '') ?: '0') . '-' . substr(md5(($c['standard_name'] ?? '') . '|' . ($c['certificate_number'] ?? '')), 0, 10);
    $clientJson = htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8');
    $clientId = htmlspecialchars($c['internal_client_id'] ?? '', ENT_QUOTES, 'UTF-8');
    $rowKey = htmlspecialchars(($c['internal_client_id'] ?? '') . '|' . (($c['svc_id'] ?? '') ?: '0') . '|' . (($c['cdc_office_use_id'] ?? '') ?: '0') . '|' . iafDashboardStandardKey($c['standard_name'] ?? ''), ENT_QUOTES, 'UTF-8');

    $hasCert = !empty($c['certificate_number']);
    $statusStr = strtolower(trim((string) ($c['certification_status'] ?? '')));
    $badgeClass = 'status-default';
    $displayStatus = ($c['certification_status'] ?? '') ?: 'Unknown';

    if ($hasCert || $statusStr === 'granted' || $statusStr === 'completed' || $statusStr === 'valid') {
        $badgeClass = 'status-active';
        $displayStatus = 'Granted';
    } elseif (strpos($statusStr, 'active') !== false || strpos($statusStr, 'issue') !== false || strpos($statusStr, 'grant') !== false) {
        $badgeClass = 'status-active';
    } elseif (strpos($statusStr, 'pending') !== false || strpos($statusStr, 'in-process') !== false || strpos($statusStr, 'in process') !== false || strpos($statusStr, 'audit') !== false) {
        $badgeClass = 'status-pending';
        $displayStatus = 'In Process';
    } elseif (strpos($statusStr, 'suspend') !== false || strpos($statusStr, 'expir') !== false || strpos($statusStr, 'withdraw') !== false) {
        $badgeClass = 'status-expired';
    }

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

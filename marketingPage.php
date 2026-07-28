<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Mktg";
    include 'header.php';
    include 'dbConnect.php';
    date_default_timezone_set('Asia/Kolkata');

    function marketingText($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    function marketingNormalizeStatus($value)
    {
        $status = trim((string) $value);
        if ($status === '') {
            return 'No Status';
        }

        $key = strtolower(str_replace(array('-', '_', ' '), '', $status));
        if ($key === 'active' || $key === 'valid' || $key === 'granted' || $key === 'certified') {
            return 'Active';
        }
        if ($key === 'suspended' || $key === 'suspend') {
            return 'Suspended';
        }
        if ($key === 'expired' || $key === 'expire') {
            return 'Expired';
        }
        if ($key === 'withdrawn' || $key === 'withdraw') {
            return 'Withdrawn';
        }
        if ($key === 'applicationcanceled' || $key === 'applicationcancelled' || $key === 'cancelled' || $key === 'canceled') {
            return 'Application canceled';
        }
        if ($key === 'inprocess') {
            return 'In-process';
        }
        if ($key === 'contractinprocess') {
            return 'Contract In-process';
        }

        return $status;
    }

    function marketingDate($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '-';
        }

        $datePart = substr($value, 0, 10);
        return function_exists('simpleDateConversion') ? simpleDateConversion($datePart) : date('d.m.Y', strtotime($datePart));
    }

    function marketingDaysOld($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            $from = new DateTime(substr($value, 0, 10));
            $to = new DateTime(date('Y-m-d'));
            return (int) $from->diff($to)->format('%r%a');
        } catch (Exception $ex) {
            return null;
        }
    }

    function marketingAgeLabel($value)
    {
        $days = marketingDaysOld($value);
        if ($days === null) {
            return 'Date not updated';
        }
        if ($days <= 0) {
            return 'Today';
        }
        if ($days === 1) {
            return '1 day';
        }
        if ($days < 30) {
            return $days . ' days';
        }
        if ($days < 365) {
            $months = floor($days / 30);
            return $months . ' month' . ($months > 1 ? 's' : '');
        }
        $years = floor($days / 365);
        $months = floor(($days % 365) / 30);
        return $years . ' year' . ($years > 1 ? 's' : '') . ($months > 0 ? ' ' . $months . ' month' . ($months > 1 ? 's' : '') : '');
    }

    function marketingColumnMap($db, $table)
    {
        $columns = array();
        if (!$db) {
            return $columns;
        }

        try {
            $stmt = $db->query("SHOW COLUMNS FROM `" . $table . "`");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $columns[$row['Field']] = true;
            }
        } catch (Exception $ex) {
            error_log($ex->getMessage());
        }

        return $columns;
    }

    function marketingFieldExpr($columns, $alias, $column, $as)
    {
        if (isset($columns[$column])) {
            return $alias . ".`" . $column . "` AS `" . $as . "`";
        }
        return "NULL AS `" . $as . "`";
    }

    function marketingStatusDate($client, $bucket)
    {
        $cdcDate = isset($client['latest_cdc_date']) ? $client['latest_cdc_date'] : '';
        $issueDate = isset($client['latest_issue_date']) ? $client['latest_issue_date'] : '';
        $dueDate = isset($client['latest_due_date']) ? $client['latest_due_date'] : '';
        $certDate = isset($client['certification_stage_date']) ? $client['certification_stage_date'] : '';
        $updatedDate = isset($client['updated_at']) ? $client['updated_at'] : '';
        $createdDate = isset($client['created_at']) ? $client['created_at'] : '';
        $addDate = isset($client['add_to']) ? $client['add_to'] : '';

        if ($bucket === 'New Client') {
            return $addDate !== '' ? $addDate : ($createdDate !== '' ? $createdDate : $certDate);
        }

        if ($bucket === 'Current Marketing') {
            return $cdcDate !== '' ? $cdcDate : ($issueDate !== '' ? $issueDate : ($dueDate !== '' ? $dueDate : ($addDate !== '' ? $addDate : $createdDate)));
        }

        if ($cdcDate !== '') {
            return $cdcDate;
        }
        if ($issueDate !== '') {
            return $issueDate;
        }
        if ($dueDate !== '') {
            return $dueDate;
        }
        if ($updatedDate !== '') {
            return $updatedDate;
        }
        if ($certDate !== '') {
            return $certDate;
        }
        return $addDate !== '' ? $addDate : $createdDate;
    }

    function marketingContactLine($client)
    {
        $parts = array();
        if (!empty($client['contact_person'])) {
            $parts[] = $client['contact_person'];
        }
        if (!empty($client['contact'])) {
            $parts[] = $client['contact'];
        }
        if (!empty($client['email_id'])) {
            $parts[] = $client['email_id'];
        }
        return empty($parts) ? '-' : implode(' | ', $parts);
    }

    $statusSections = array(
        'New Client' => array(
            'title' => 'New Clients',
            'tone' => 'new',
            'icon' => 'fa-user-plus',
            'accent' => '#2563eb',
            'soft' => '#eff6ff',
            'label' => 'Fresh opportunity',
            'message' => 'New client enquiry is waiting. Contact the client and move the opportunity forward.'
        ),
        'Current Marketing' => array(
            'title' => 'Current Marketing Pipeline',
            'tone' => 'marketing',
            'icon' => 'fa-bullhorn',
            'accent' => '#0891b2',
            'soft' => '#ecfeff',
            'label' => 'Follow-up pipeline',
            'message' => 'Client is already in Marketing status. Follow up and send to audit planning when ready.'
        ),
        'Suspended' => array(
            'title' => 'Suspended Clients',
            'tone' => 'suspended',
            'icon' => 'fa-pause-circle',
            'accent' => '#d97706',
            'soft' => '#fff7ed',
            'label' => 'Recovery required',
            'message' => 'Client is suspended. It is time to contact the client for recovery and next action.'
        ),
        'Expired' => array(
            'title' => 'Expired Clients',
            'tone' => 'expired',
            'icon' => 'fa-calendar-times-o',
            'accent' => '#dc2626',
            'soft' => '#fef2f2',
            'label' => 'Renewal due',
            'message' => 'Client certificate is expired. Contact the client for renewal or recertification.'
        ),
        'Withdrawn' => array(
            'title' => 'Withdrawn Clients',
            'tone' => 'withdrawn',
            'icon' => 'fa-sign-out',
            'accent' => '#9333ea',
            'soft' => '#faf5ff',
            'label' => 'Win-back list',
            'message' => 'Client is withdrawn. Reconnect and check whether a fresh application is possible.'
        ),
        'Application canceled' => array(
            'title' => 'Canceled Applications',
            'tone' => 'canceled',
            'icon' => 'fa-ban',
            'accent' => '#475569',
            'soft' => '#f8fafc',
            'label' => 'Reason pending',
            'message' => 'Application was canceled. Contact the client and capture the reason or revival opportunity.'
        )
    );

    $sectionClients = array();
    foreach ($statusSections as $key => $meta) {
        $sectionClients[$key] = array();
    }

    $dbError = '';
    $allMarketingClients = array();

    if (!$db) {
        $dbError = 'Database connection is not available.';
    } else {
        try {
            $customerColumns = marketingColumnMap($db, 'customer_info');
            $serviceColumns = marketingColumnMap($db, 'service');

            $marketingInfo = "SELECT
                    ci.`id`,
                    ci.`customer_name`,
                    " . marketingFieldExpr($customerColumns, 'ci', 'unique_id', 'unique_id') . ",
                    " . marketingFieldExpr($customerColumns, 'ci', 'audit_status', 'audit_status') . ",
                    " . marketingFieldExpr($customerColumns, 'ci', 'address', 'address') . ",
                    " . marketingFieldExpr($customerColumns, 'ci', 'contact_person', 'contact_person') . ",
                    " . marketingFieldExpr($customerColumns, 'ci', 'contact', 'contact') . ",
                    " . marketingFieldExpr($customerColumns, 'ci', 'email_id', 'email_id') . ",
                    " . marketingFieldExpr($customerColumns, 'ci', 'scope', 'scope') . ",
                    " . marketingFieldExpr($customerColumns, 'ci', 'comment', 'comment') . ",
                    " . marketingFieldExpr($customerColumns, 'ci', 'add_to', 'add_to') . ",
                    " . marketingFieldExpr($customerColumns, 'ci', 'created_at', 'created_at') . ",
                    " . marketingFieldExpr($customerColumns, 'ci', 'updated_at', 'updated_at') . ",
                    " . marketingFieldExpr($customerColumns, 'ci', 'certification_stage_date', 'certification_stage_date') . ",
                    svc.`service_id`,
                    " . marketingFieldExpr($serviceColumns, 'svc', 'service_type', 'service_type') . ",
                    " . marketingFieldExpr($serviceColumns, 'svc', 'service_status', 'service_status') . ",
                    " . marketingFieldExpr($serviceColumns, 'svc', 'audit_date', 'audit_date') . ",
                    COALESCE(stca.ca_id, stsai.sai_id, stsaii.saii_id, strc.rc_id, stsp.sp_id) AS service_type_id,
                    cdc.crr_id AS cdc_report_id,
                    cdc.cdc_status,
                    cdc.audit_type AS cdc_audit_type,
                    COALESCE(
                        NULLIF(NULLIF(cou.rev_date, ''), '0000-00-00'),
                        NULLIF(NULLIF(cdc.revDatecdc, ''), '0000-00-00'),
                        NULLIF(NULLIF(cp.registration_date, ''), '0000-00-00'),
                        NULLIF(NULLIF(cdc.`Date`, ''), '0000-00-00')
                    ) AS latest_issue_date,
                    COALESCE(
                        NULLIF(NULLIF(cdc.`Date`, ''), '0000-00-00'),
                        NULLIF(NULLIF(cou.rev_date, ''), '0000-00-00'),
                        NULLIF(NULLIF(cdc.revDatecdc, ''), '0000-00-00'),
                        NULLIF(NULLIF(cp.registration_date, ''), '0000-00-00'),
                        NULLIF(NULLIF(cdc.audit_date, ''), '0000-00-00')
                    ) AS latest_cdc_date,
                    COALESCE(
                        NULLIF(NULLIF(cp.valid_until, ''), '0000-00-00'),
                        NULLIF(NULLIF(cou.expire_date, ''), '0000-00-00'),
                        NULLIF(NULLIF(cdc.expiredate, ''), '0000-00-00')
                    ) AS latest_expiry_date,
                    svcdt.`action` AS latest_action,
                    svcdt.`due_date` AS latest_due_date,
                    svcdt.`comment` AS latest_comment
                FROM customer_info ci
                LEFT JOIN (
                    SELECT s.*
                    FROM service s
                    INNER JOIN (
                        SELECT customer_id, MAX(service_id) AS max_service_id
                        FROM service
                        GROUP BY customer_id
                    ) latest_service ON latest_service.customer_id = s.customer_id
                    AND latest_service.max_service_id = s.service_id
                ) svc ON ci.id = svc.customer_id
                LEFT JOIN service_type_ca stca ON stca.service = svc.service_id
                LEFT JOIN service_type_sai stsai ON stsai.service = svc.service_id
                LEFT JOIN service_type_saii stsaii ON stsaii.service = svc.service_id
                LEFT JOIN service_type_rc strc ON strc.service = svc.service_id
                LEFT JOIN service_type_sp stsp ON stsp.service = svc.service_id
                LEFT JOIN cdc_report_review cdc ON cdc.crr_id = COALESCE(stca.crr_id, stsai.crr_id, stsaii.crr_id, strc.crr_id, stsp.crr_id)
                    OR (
                        cdc.customer_id = ci.id
                        AND cdc.ca_service_id = COALESCE(stca.ca_id, stsai.sai_id, stsaii.saii_id, strc.rc_id, stsp.sp_id)
                        AND cdc.crr_id = (
                            SELECT MAX(cdc_s.crr_id)
                            FROM cdc_report_review cdc_s
                            WHERE cdc_s.customer_id = ci.id
                            AND cdc_s.ca_service_id = COALESCE(stca.ca_id, stsai.sai_id, stsaii.saii_id, strc.rc_id, stsp.sp_id)
                        )
                    )
                LEFT JOIN cdc_report_review_office_use cou ON cou.crr_id = cdc.crr_id
                    AND cou.id = (
                        SELECT MAX(cou_s.id)
                        FROM cdc_report_review_office_use cou_s
                        WHERE cou_s.crr_id = cdc.crr_id
                    )
                LEFT JOIN certificate_print cp ON cp.service_id = COALESCE(stca.ca_id, stsai.sai_id, stsaii.saii_id, strc.rc_id, stsp.sp_id, svc.service_id)
                    AND cp.certificate_id = (
                        SELECT MAX(cp_s.certificate_id)
                        FROM certificate_print cp_s
                        WHERE cp_s.service_id = COALESCE(stca.ca_id, stsai.sai_id, stsaii.saii_id, strc.rc_id, stsp.sp_id, svc.service_id)
                    )
                LEFT JOIN (
                    SELECT d.*
                    FROM ca_service_dates d
                    INNER JOIN (
                        SELECT service_id, MAX(action_id) AS max_action_id
                        FROM ca_service_dates
                        WHERE is_deleted = 'N'
                        GROUP BY service_id
                    ) latest_date ON latest_date.service_id = d.service_id
                    AND latest_date.max_action_id = d.action_id
                ) svcdt ON svc.service_id = svcdt.service_id
                WHERE (ci.is_deleted IS NULL OR ci.is_deleted <> 'YES')
                ORDER BY ci.id DESC";

            $statement = $db->prepare($marketingInfo);
            $statement->execute();
            $allMarketingClients = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($allMarketingClients as $client) {
                $normalizedStatus = marketingNormalizeStatus(isset($client['audit_status']) ? $client['audit_status'] : '');
                $serviceStatus = trim((string) (isset($client['service_status']) ? $client['service_status'] : ''));
                $bucket = '';

                if ($normalizedStatus === 'Application canceled') {
                    $bucket = 'Application canceled';
                } else if ($normalizedStatus === 'Suspended' || $normalizedStatus === 'Expired' || $normalizedStatus === 'Withdrawn') {
                    $bucket = $normalizedStatus;
                } else if (empty($client['service_id'])) {
                    $bucket = 'New Client';
                } else if (strtolower($serviceStatus) === 'marketing') {
                    $bucket = 'Current Marketing';
                }

                if ($bucket !== '' && isset($sectionClients[$bucket])) {
                    $client['marketing_bucket'] = $bucket;
                    $client['status_since'] = marketingStatusDate($client, $bucket);
                    $sectionClients[$bucket][] = $client;
                }
            }
        } catch (Exception $ex) {
            error_log($ex->getMessage());
            $dbError = 'Unable to load marketing client data. Please check the database query/logs.';
        }
    }

    $totalActionClients = 0;
    foreach ($sectionClients as $rows) {
        $totalActionClients += count($rows);
    }
    ?>

    <style>
        #page-content-wrapper,
        #page-content-wrapper * {
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
        }
        #page-content-wrapper .fa {
            font-family: FontAwesome !important;
        }
        .marketing-page {
            background: #eef3f8;
            min-height: 100vh;
            padding: 22px 24px 36px;
        }
        .marketing-hero {
            background: linear-gradient(135deg, #0f172a 0%, #145369 54%, #263238 100%);
            color: #ffffff;
            border-radius: 8px;
            padding: 28px 30px;
            margin-bottom: 18px;
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.18);
            position: relative;
            overflow: hidden;
        }
        .marketing-hero:after {
            content: "";
            position: absolute;
            right: 0;
            top: 0;
            width: 38%;
            height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.12));
            pointer-events: none;
        }
        .marketing-hero h1 {
            margin: 0 0 8px;
            font-size: 30px;
            font-weight: 800;
            letter-spacing: 0;
        }
        .marketing-hero p {
            margin: 0;
            color: #cbd5e1;
            font-size: 14px;
            line-height: 1.6;
        }
        .marketing-hero-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 18px;
        }
        .marketing-stat {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            padding: 14px 16px;
            position: relative;
            z-index: 1;
        }
        .marketing-stat b {
            display: block;
            font-size: 24px;
            line-height: 1;
            margin-bottom: 6px;
        }
        .marketing-stat span {
            color: #cbd5e1;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .marketing-summary-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }
        .marketing-summary-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px;
            min-height: 104px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
            border-top: 4px solid #94a3b8;
        }
        .marketing-summary-card .summary-icon {
            width: 34px;
            height: 34px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            margin-bottom: 10px;
        }
        .marketing-summary-card b {
            display: block;
            color: #0f172a;
            font-size: 24px;
            line-height: 1;
        }
        .marketing-summary-card span {
            display: block;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            margin-top: 6px;
        }
        .marketing-summary-card small {
            display: block;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
            margin-top: 10px;
            text-transform: uppercase;
        }
        .tone-new { border-top-color: #2563eb; background: #f8fbff; }
        .tone-marketing { border-top-color: #0891b2; background: #f4fdff; }
        .tone-suspended { border-top-color: #d97706; background: #fffaf3; }
        .tone-expired { border-top-color: #dc2626; background: #fff7f7; }
        .tone-withdrawn { border-top-color: #9333ea; background: #fcf8ff; }
        .tone-canceled { border-top-color: #475569; background: #f8fafc; }
        .tone-new .summary-icon,
        .section-new .section-icon { background: #2563eb; }
        .tone-marketing .summary-icon,
        .section-marketing .section-icon { background: #0891b2; }
        .tone-suspended .summary-icon,
        .section-suspended .section-icon { background: #d97706; }
        .tone-expired .summary-icon,
        .section-expired .section-icon { background: #dc2626; }
        .tone-withdrawn .summary-icon,
        .section-withdrawn .section-icon { background: #9333ea; }
        .tone-canceled .summary-icon,
        .section-canceled .section-icon { background: #475569; }
        .marketing-section {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 18px;
            overflow: hidden;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
            border-left: 5px solid #94a3b8;
        }
        .section-new { border-left-color: #2563eb; }
        .section-marketing { border-left-color: #0891b2; }
        .section-suspended { border-left-color: #d97706; }
        .section-expired { border-left-color: #dc2626; }
        .section-withdrawn { border-left-color: #9333ea; }
        .section-canceled { border-left-color: #475569; }
        .marketing-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            border-bottom: 1px solid #eef2f7;
            background: #fbfdff;
        }
        .section-new .marketing-section-head { background: #eff6ff; }
        .section-marketing .marketing-section-head { background: #ecfeff; }
        .section-suspended .marketing-section-head { background: #fff7ed; }
        .section-expired .marketing-section-head { background: #fef2f2; }
        .section-withdrawn .marketing-section-head { background: #faf5ff; }
        .section-canceled .marketing-section-head { background: #f8fafc; }
        .marketing-section-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .section-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 38px;
        }
        .marketing-section-title h2 {
            margin: 0;
            font-size: 18px;
            color: #0f172a;
            font-weight: 800;
        }
        .marketing-section-title p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 13px;
        }
        .section-count {
            min-width: 86px;
            text-align: center;
            border-radius: 8px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            padding: 8px 12px;
            color: #0f172a;
            background: rgba(255, 255, 255, 0.72);
        }
        .section-count b {
            display: block;
            font-size: 22px;
            line-height: 1;
        }
        .section-count span {
            display: block;
            margin-top: 4px;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 800;
        }
        .section-new .section-count b { color: #1d4ed8; }
        .section-marketing .section-count b { color: #0e7490; }
        .section-suspended .section-count b { color: #b45309; }
        .section-expired .section-count b { color: #b91c1c; }
        .section-withdrawn .section-count b { color: #7e22ce; }
        .section-canceled .section-count b { color: #334155; }
        .marketing-table-wrap {
            padding: 14px 16px 18px;
        }
        .marketing-table {
            width: 100% !important;
            border-collapse: collapse;
        }
        .marketing-table thead th {
            background: #f8fafc;
            color: #475569;
            border-bottom: 1px solid #e2e8f0 !important;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0;
            padding: 10px 12px !important;
        }
        .marketing-table tbody td {
            color: #334155;
            font-size: 13px;
            vertical-align: middle !important;
            border-top: 1px solid #eef2f7 !important;
            padding: 10px 12px !important;
        }
        .client-name-link {
            color: #0f172a;
            font-weight: 800;
            cursor: pointer;
        }
        .client-name-link:hover {
            color: #2563eb;
        }
        .client-subtext {
            display: block;
            margin-top: 2px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
        }
        .date-source {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 6px;
            padding: 4px 8px;
            font-weight: 800;
            font-size: 11px;
            background: #f1f5f9;
            color: #334155;
            white-space: nowrap;
        }
        .status-pill.tone-new { background: #dbeafe; color: #1d4ed8; }
        .status-pill.tone-marketing { background: #cffafe; color: #0e7490; }
        .status-pill.tone-suspended { background: #ffedd5; color: #b45309; }
        .status-pill.tone-expired { background: #fee2e2; color: #b91c1c; }
        .status-pill.tone-withdrawn { background: #f3e8ff; color: #7e22ce; }
        .status-pill.tone-canceled { background: #e2e8f0; color: #334155; }
        .age-strong {
            color: #0f172a;
            font-weight: 800;
            white-space: nowrap;
        }
        .contact-actions {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }
        .contact-actions a,
        .contact-actions button {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .contact-actions a:hover,
        .contact-actions button:hover {
            border-color: #2563eb;
            color: #2563eb;
        }
        .empty-state,
        .db-error {
            margin: 14px 16px 18px;
            padding: 18px;
            border-radius: 8px;
            background: #f8fafc;
            color: #64748b;
            border: 1px dashed #cbd5e1;
            text-align: center;
            font-weight: 700;
        }
        .db-error {
            background: #fff1f2;
            color: #be123c;
            border-color: #fecdd3;
        }
        @media (max-width: 1200px) {
            .marketing-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 768px) {
            .marketing-page {
                padding: 14px;
            }
            .marketing-hero-stats,
            .marketing-summary-grid {
                grid-template-columns: 1fr;
            }
            .marketing-section-head {
                align-items: flex-start;
                flex-direction: column;
            }
            .section-count {
                width: 100%;
            }
        }
    </style>

    <div id="page-content-wrapper">
        <div class="page-content inset marketing-page">
            <div class="marketing-hero">
                <h1>Marketing Follow-up Desk</h1>
        
                <div class="marketing-hero-stats">
                    <div class="marketing-stat">
                        <b><?php echo number_format($totalActionClients); ?></b>
                        <span>Total clients to contact</span>
                    </div>
                    <div class="marketing-stat">
                        <b><?php echo number_format(count($sectionClients['New Client'])); ?></b>
                        <span>New client enquiries</span>
                    </div>
                    <div class="marketing-stat">
                        <b><?php echo date('d.m.Y'); ?></b>
                        <span>Review date</span>
                    </div>
                </div>
            </div>

            <?php if ($dbError !== '') { ?>
                <div class="db-error"><?php echo marketingText($dbError); ?></div>
            <?php } ?>

            <div class="marketing-summary-grid">
                <?php foreach ($statusSections as $sectionKey => $sectionMeta) { ?>
                    <div class="marketing-summary-card tone-<?php echo marketingText($sectionMeta['tone']); ?>">
                        <div class="summary-icon"><i class="fa <?php echo marketingText($sectionMeta['icon']); ?>"></i></div>
                        <b><?php echo number_format(count($sectionClients[$sectionKey])); ?></b>
                        <span><?php echo marketingText($sectionMeta['title']); ?></span>
                        <small><?php echo marketingText($sectionMeta['label']); ?></small>
                    </div>
                <?php } ?>
            </div>

            <?php foreach ($statusSections as $sectionKey => $sectionMeta) {
                $rows = $sectionClients[$sectionKey];
                ?>
                <section class="marketing-section section-<?php echo marketingText($sectionMeta['tone']); ?>">
                    <div class="marketing-section-head">
                        <div class="marketing-section-title">
                            <div class="section-icon"><i class="fa <?php echo marketingText($sectionMeta['icon']); ?>"></i></div>
                            <div>
                                <h2><?php echo marketingText($sectionMeta['title']); ?></h2>
                                <p><?php echo number_format(count($rows)); ?> clients need follow-up. <?php echo marketingText($sectionMeta['message']); ?></p>
                            </div>
                        </div>
                        <div class="section-count">
                            <b><?php echo number_format(count($rows)); ?></b>
                            <span>Clients</span>
                        </div>
                    </div>

                    <?php if (empty($rows)) { ?>
                        <div class="empty-state">No clients available in this section.</div>
                    <?php } else { ?>
                        <div class="marketing-table-wrap table-responsive">
                            <table class="table table-hover marketing-table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Latest CDC Date</th>
                                        <th>Time</th>
                                        <th>Latest Service</th>
                                        <th>Next Action</th>
                                        <th>Contact</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $client) {
                                        $statusSince = isset($client['status_since']) ? $client['status_since'] : '';
                                        $daysOld = marketingDaysOld($statusSince);
                                        $email = trim((string) (isset($client['email_id']) ? $client['email_id'] : ''));
                                        $phone = trim((string) (isset($client['contact']) ? $client['contact'] : ''));
                                        $displayStatus = $sectionKey === 'New Client' ? 'New Client' : ($sectionKey === 'Current Marketing' ? 'Marketing' : $sectionKey);
                                        $dateSource = 'Fallback date';
                                        if (!empty($client['latest_cdc_date']) && $statusSince === $client['latest_cdc_date']) {
                                            $dateSource = 'CDC date';
                                        } else if (!empty($client['latest_issue_date']) && $statusSince === $client['latest_issue_date']) {
                                            $dateSource = 'Certificate issue date';
                                        } else if (!empty($client['latest_due_date']) && $statusSince === $client['latest_due_date']) {
                                            $dateSource = 'Due date';
                                        } else if (!empty($client['certification_stage_date']) && $statusSince === $client['certification_stage_date']) {
                                            $dateSource = 'Decision date';
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="client-name-link customerAccount" data-id="<?php echo (int) $client['id']; ?>">
                                                    <?php echo marketingText($client['customer_name']); ?>
                                                </span>
                                                <span class="client-subtext"><?php echo !empty($client['unique_id']) ? marketingText($client['unique_id']) : 'Client ID: ' . (int) $client['id']; ?></span>
                                            </td>
                                            <td>
                                                <?php echo marketingText(marketingContactLine($client)); ?>
                                            </td>
                                            <td>
                                                <span class="status-pill tone-<?php echo marketingText($sectionMeta['tone']); ?>"><?php echo marketingText($displayStatus); ?></span>
                                            </td>
                                            <td>
                                                <?php echo marketingText(marketingDate($statusSince)); ?>
                                                <span class="date-source"><?php echo marketingText($dateSource); ?></span>
                                            </td>
                                            <td data-order="<?php echo $daysOld === null ? 0 : (int) $daysOld; ?>"><span class="age-strong"><?php echo marketingText(marketingAgeLabel($statusSince)); ?></span></td>
                                            <td>
                                                <?php echo !empty($client['service_type']) ? marketingText($client['service_type']) : '-'; ?>
                                                <span class="client-subtext"><?php echo !empty($client['service_status']) ? marketingText($client['service_status']) : 'No service'; ?><?php echo !empty($client['cdc_status']) ? ' | CDC: ' . marketingText($client['cdc_status']) : ''; ?></span>
                                                <?php if (!empty($client['latest_expiry_date'])) { ?>
                                                    <span class="client-subtext">Expiry: <?php echo marketingText(marketingDate($client['latest_expiry_date'])); ?></span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php echo !empty($client['latest_action']) ? marketingText($client['latest_action']) : 'Contact client'; ?>
                                                <?php if (!empty($client['latest_due_date'])) { ?>
                                                    <span class="client-subtext">Due: <?php echo marketingText(marketingDate($client['latest_due_date'])); ?></span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="contact-actions">
                                                    <?php if ($email !== '') { ?>
                                                        <a href="mailto:<?php echo marketingText($email); ?>" title="Email client"><i class="fa fa-envelope"></i></a>
                                                    <?php } ?>
                                                    <?php if ($phone !== '') { ?>
                                                        <a href="tel:<?php echo marketingText($phone); ?>" title="Call client"><i class="fa fa-phone"></i></a>
                                                    <?php } ?>
                                                    <button type="button" class="customerAccount" data-id="<?php echo (int) $client['id']; ?>" title="Open client account"><i class="fa fa-folder-open"></i></button>
                                                    <?php if (!empty($client['service_id']) && strtolower(trim((string) $client['service_status'])) === 'marketing') { ?>
                                                        <button type="button" class="sendAuditPlaning" data-customer="<?php echo (int) $client['id']; ?>" data-id="<?php echo (int) $client['service_id']; ?>" title="Send to audit planning"><i class="fa fa-plus-circle"></i></button>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </section>
            <?php } ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('.customerAccount').click(function () {
            var customerID = $(this).attr('data-id');
            if (!customerID) {
                return;
            }
            localStorage['visited'] = customerID;
            var form = $('<form action="customerAccount.php" method="get">' +
                    '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.sendAuditPlaning').click(function () {
            var service = $(this).attr('data-id');
            var customer = $(this).attr('data-customer');
            var form = $('<form action="addToAudit.php" method="post">' +
                    '<input type="hidden" name="service" value="' + service + '" />' +
                    '<input type="hidden" name="customer" value="' + customer + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
            if ($.fn.DataTable) {
                $('.marketing-table').DataTable().columns.adjust();
            }
        });

        if ($.fn.DataTable) {
            $('.marketing-table').DataTable({
                scrollX: true,
                paging: false,
                ordering: true,
                order: [[4, 'desc']],
                lengthChange: false,
                info: false,
                language: {
                    search: "Search clients:"
                }
            });
        }
    });
</script>

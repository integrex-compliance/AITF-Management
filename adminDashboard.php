<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Top Mgmt Dashboard";
    include 'header.php';
    include 'dbConnect.php';
    include_once './include/certification_cycle_helper.php';
    require_once __DIR__ . '/include/rbac_helper.php';
    date_default_timezone_set('Asia/Kolkata');

    if (!Utitlity::checkForDirector()) {
        header("Location: login.php?reason=login_required");
        exit;
    }

    function dashMoney($value)
    {
        $num = (float)$value;
        $num = round($num, 2);
        $parts = explode('.', $num);
        $whole = $parts[0];
        $decimal = isset($parts[1]) ? str_pad($parts[1], 2, '0', STR_PAD_RIGHT) : '00';

        $lastThree = substr($whole, -3);
        $restUnits = substr($whole, 0, -3);
        if ($restUnits != '' && $restUnits != '-') {
            $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
            $whole = $restUnits . "," . $lastThree;
        }
        return 'Rs.&nbsp;' . $whole . '.' . $decimal;
    }

    function dashInt($value)
    {
        return number_format((int) $value);
    }

    function dashPercent($part, $total)
    {
        $total = (float) $total;
        if ($total <= 0) {
            return '0%';
        }
        return number_format(((float) $part / $total) * 100, 1) . '%';
    }

    function dashDate($value)
    {
        if (!isset($value) || $value === '' || $value === '0000-00-00') {
            return '-';
        }
        return simpleDateConversion(substr($value, 0, 10));
    }

    function dashFetchOne($db, $sql, $params = array())
    {
        if (!$db) {
            return array();
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : array();
    }

    function dashFetchAll($db, $sql, $params = array())
    {
        if (!$db) {
            return array();
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function dashEnsurePaymentAliasTable($db)
    {
        if (!$db) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS payment_client_alias (
            alias_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer_ref VARCHAR(120) NOT NULL,
            client_name VARCHAR(255) NOT NULL,
            customer_id INT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_customer_ref (customer_ref)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $stmt = $db->prepare($sql);
        $stmt->execute();
    }

    function dashAmountExpr($field)
    {
        return "CAST(REPLACE(REPLACE(REPLACE($field, ',', ''), 'Rs.', ''), '₹', '') AS DECIMAL(12,2))";
    }

    function dashToAmount($value)
    {
        $clean = str_replace(array(',', 'Rs.', '₹', ' '), '', (string) $value);
        if ($clean === '' || !is_numeric($clean)) {
            return 0.0;
        }
        return (float) $clean;
    }

    function dashNormalizeKey($value)
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '') {
            return '';
        }
        return str_replace(array(" ", "\t", "\n", "\r"), '', $value);
    }

    function dashNormalizeClientStatus($value)
    {
        $status = trim((string) $value);
        if ($status === '') {
            return 'No Status';
        }

        $key = strtolower(str_replace(array('-', '_', ' '), '', $status));

        if ($key === 'active' || $key === 'valid' || $key === 'granted') {
            return 'Active';
        }
        if ($key === 'suspended' || $key === 'suspend') {
            return 'Suspended';
        }
        if ($key === 'inprocess') {
            return 'Inprocess';
        }
        if ($key === 'contractinprocess') {
            return 'Contract-Inprocess';
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

    function dashDerivedClientStatus($client, $latestServiceMap)
    {
        $norm = dashNormalizeClientStatus(isset($client['audit_status']) ? $client['audit_status'] : '');

        // Cancelled applications are terminal and should not be moved into review buckets.
        if ($norm === 'Application canceled') {
            return 'Application canceled';
        }

        $cid = isset($client['id']) ? (int) $client['id'] : 0;
        $service = ($cid > 0 && isset($latestServiceMap[$cid])) ? $latestServiceMap[$cid] : null;

        if (empty($service)) {
            return 'Awaiting Review';
        }

        if (isset($service['service_type']) && strtoupper(trim($service['service_type'])) === 'CR') {
            return 'Contract-Review';
        }

        return $norm;
    }

    function dashCandidateNumericId($value)
    {
        $raw = strtoupper(trim((string) $value));
        if ($raw === '') {
            return '';
        }

        if (preg_match('/^[0-9]+$/', $raw)) {
            return (string) ((int) $raw);
        }

        if (preg_match('/^[0-9]+\\.0+$/', $raw)) {
            return (string) ((int) $raw);
        }

        $clean = str_replace(array('ID', ':', '-', '#', ' '), '', $raw);
        if ($clean !== '' && ctype_digit($clean)) {
            return (string) ((int) $clean);
        }

        return '';
    }

    function dashCertificationRecordKey($client)
    {
        $uniqueId = dashNormalizeKey(isset($client['unique_id']) ? $client['unique_id'] : '');
        $certificationNo = dashNormalizeKey(isset($client['certification_no']) ? $client['certification_no'] : '');

        if ($uniqueId === '' || $certificationNo === '') {
            return '';
        }

        return implode('|', array(
            $uniqueId,
            $certificationNo,
            dashNormalizeKey(isset($client['updated_cerf']) ? $client['updated_cerf'] : ''),
            dashNormalizeKey(dashNormalizeClientStatus(isset($client['audit_status']) ? $client['audit_status'] : '')),
            trim((string) (isset($client['certification_stage_date']) ? $client['certification_stage_date'] : '')),
            dashNormalizeKey(isset($client['standard_for']) ? $client['standard_for'] : '')
        ));
    }

    function dashResolveClientFromRow($row, $customerIdNameMap, $customerUniqueNameMap, $customerNameMap, $serviceIdNameMap, $paymentAliasNameMap, $paymentAliasIdMap)
    {
        $resolvedName = isset($row['customer_name']) ? trim((string) $row['customer_name']) : '';
        $resolvedId = isset($row['resolved_customer_id']) ? (int) $row['resolved_customer_id'] : 0;

        $candidates = array(
            isset($row['customer_id']) ? $row['customer_id'] : '',
            isset($row['ps_customer_id']) ? $row['ps_customer_id'] : '',
            isset($row['psi_customer_id']) ? $row['psi_customer_id'] : '',
            isset($row['ps_group_customer_id']) ? $row['ps_group_customer_id'] : ''
        );

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                continue;
            }

            $normalized = dashNormalizeKey($candidate);

            if ($normalized !== '' && isset($paymentAliasNameMap[$normalized])) {
                if ($resolvedName === '') {
                    $resolvedName = $paymentAliasNameMap[$normalized];
                }
                if ($resolvedId <= 0 && isset($paymentAliasIdMap[$normalized])) {
                    $resolvedId = (int) $paymentAliasIdMap[$normalized];
                }
                if ($resolvedName !== '') {
                    break;
                }
            }

            if ($normalized !== '' && isset($customerUniqueNameMap[$normalized])) {
                if ($resolvedName === '') {
                    $resolvedName = $customerUniqueNameMap[$normalized]['name'];
                }
                if ($resolvedId <= 0) {
                    $resolvedId = (int) $customerUniqueNameMap[$normalized]['id'];
                }
                if ($resolvedName !== '' && $resolvedId > 0) {
                    break;
                }
            }

            $numericCandidate = dashCandidateNumericId($candidate);

            if ($numericCandidate !== '' && isset($paymentAliasNameMap[$numericCandidate])) {
                if ($resolvedName === '') {
                    $resolvedName = $paymentAliasNameMap[$numericCandidate];
                }
                if ($resolvedId <= 0 && isset($paymentAliasIdMap[$numericCandidate])) {
                    $resolvedId = (int) $paymentAliasIdMap[$numericCandidate];
                }
                if ($resolvedName !== '') {
                    break;
                }
            }

            if ($numericCandidate !== '' && isset($customerIdNameMap[$numericCandidate])) {
                if ($resolvedName === '') {
                    $resolvedName = $customerIdNameMap[$numericCandidate]['name'];
                }
                if ($resolvedId <= 0) {
                    $resolvedId = (int) $customerIdNameMap[$numericCandidate]['id'];
                }
                if ($resolvedName !== '' && $resolvedId > 0) {
                    break;
                }
            }

            if ($numericCandidate !== '' && isset($serviceIdNameMap[$numericCandidate])) {
                if ($resolvedName === '') {
                    $resolvedName = $serviceIdNameMap[$numericCandidate]['name'];
                }
                if ($resolvedId <= 0) {
                    $resolvedId = (int) $serviceIdNameMap[$numericCandidate]['id'];
                }
                if ($resolvedName !== '' && $resolvedId > 0) {
                    break;
                }
            }

            if ($normalized !== '' && isset($customerNameMap[$normalized])) {
                if ($resolvedName === '') {
                    $resolvedName = $customerNameMap[$normalized]['name'];
                }
                if ($resolvedId <= 0) {
                    $resolvedId = (int) $customerNameMap[$normalized]['id'];
                }
                if ($resolvedName !== '' && $resolvedId > 0) {
                    break;
                }
            }
        }

        $fallbackRef = '';
        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '') {
                $fallbackRef = $candidate;
                break;
            }
        }

        $row['customer_name'] = $resolvedName;
        $row['resolved_customer_id'] = $resolvedId;
        $row['fallback_ref'] = $fallbackRef;
        return $row;
    }

    dashEnsurePaymentAliasTable($db);

    $dashboardType = isset($_GET['dashboard_type']) ? $_GET['dashboard_type'] : 'business';
    $allowedDashboards = array('business', 'invoice', 'letters', 'payments', 'clients');
    if (!in_array($dashboardType, $allowedDashboards)) {
        $dashboardType = 'business';
    }

    $invoiceType = isset($_GET['invoice_type']) ? $_GET['invoice_type'] : 'final';
    $allowedInvoiceTypes = array('all', 'proforma', 'final');
    if (!in_array($invoiceType, $allowedInvoiceTypes)) {
        $invoiceType = 'final';
    }

    $invoiceStatus = isset($_GET['invoice_status']) ? $_GET['invoice_status'] : 'all';
    $allowedInvoiceStatuses = array('all', 'Rcv', 'NotRcv');
    if (!in_array($invoiceStatus, $allowedInvoiceStatuses)) {
        $invoiceStatus = 'all';
    }


    $letterAction = isset($_GET['letter_action']) ? $_GET['letter_action'] : 'all';
    $letterStatus = isset($_GET['letter_status']) ? $_GET['letter_status'] : 'all';
    $dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

    $invoiceWhere = "WHERE is_deleted = 'No'";
    $invoiceParams = array();
    if ($invoiceType !== 'all') {
        $invoiceWhere .= " AND invoice_type = ?";
        $invoiceParams[] = $invoiceType;
    }
    if ($invoiceStatus !== 'all') {
        $invoiceWhere .= " AND invoice_status = ?";
        $invoiceParams[] = $invoiceStatus;
    }
    if ($dateFrom !== '') {
        $invoiceWhere .= " AND invoice_date >= ?";
        $invoiceParams[] = $dateFrom;
    }
    if ($dateTo !== '') {
        $invoiceWhere .= " AND invoice_date <= ?";
        $invoiceParams[] = $dateTo;
    }

    $recentInvoiceWhere = "WHERE inv.is_deleted = 'No'";
    $recentInvoiceParams = array();
    if ($invoiceType !== 'all') {
        $recentInvoiceWhere .= " AND inv.invoice_type = ?";
        $recentInvoiceParams[] = $invoiceType;
    }
    if ($invoiceStatus !== 'all') {
        $recentInvoiceWhere .= " AND inv.invoice_status = ?";
        $recentInvoiceParams[] = $invoiceStatus;
    }
    if ($dateFrom !== '') {
        $recentInvoiceWhere .= " AND inv.invoice_date >= ?";
        $recentInvoiceParams[] = $dateFrom;
    }
    if ($dateTo !== '') {
        $recentInvoiceWhere .= " AND inv.invoice_date <= ?";
        $recentInvoiceParams[] = $dateTo;
    }

    $letterWhere = "WHERE svcdt.is_deleted = 'N'";
    $letterParams = array();
    if ($letterAction !== 'all') {
        $letterWhere .= " AND svcdt.action = ?";
        $letterParams[] = $letterAction;
    }
    if ($letterStatus === 'sent') {
        $letterWhere .= " AND svcdt.status = 'sent'";
    } else if ($letterStatus === 'pending') {
        $letterWhere .= " AND svcdt.status <> 'sent' AND svcdt.due_date <= CURDATE()";
    } else if ($letterStatus === 'upcoming') {
        $letterWhere .= " AND svcdt.status <> 'sent' AND svcdt.due_date > CURDATE()";
    }
    // Exclude withdrawn clients to match intimationLetter.php
    $letterWhere .= " AND (ci.audit_status IS NULL OR ci.audit_status <> 'Withdrawn')";

    if ($dateFrom !== '') {
        $letterWhere .= " AND svcdt.due_date >= ?";
        $letterParams[] = $dateFrom;
    }
    if ($dateTo !== '') {
        $letterWhere .= " AND svcdt.due_date <= ?";
        $letterParams[] = $dateTo;
    }

    $moneyExpr = dashAmountExpr('final_amount');
    $invoiceTotals = dashFetchOne($db, "SELECT COUNT(*) total_count, COALESCE(SUM($moneyExpr),0) total_amount, COALESCE(SUM(CASE WHEN invoice_status = 'Rcv' THEN $moneyExpr ELSE 0 END),0) received_amount, COALESCE(SUM(CASE WHEN invoice_status <> 'Rcv' THEN $moneyExpr ELSE 0 END),0) pending_amount FROM invoice_details $invoiceWhere", $invoiceParams);
    $invoiceTypeRows = dashFetchAll($db, "SELECT invoice_type label, COUNT(*) total, COALESCE(SUM($moneyExpr),0) amount FROM invoice_details $invoiceWhere GROUP BY invoice_type ORDER BY invoice_type", $invoiceParams);

    // Invoice Trend - Focus on current year if no dates selected
    $currentYear = date('Y');
    $trendWhere = $invoiceWhere;
    if ($dateFrom === '' && $dateTo === '') {
        $trendWhere .= " AND YEAR(invoice_date) = $currentYear";
    }
    $invoiceTrendRows = dashFetchAll($db, "SELECT DATE_FORMAT(invoice_date, '%b %Y') label, COUNT(*) total, COALESCE(SUM($moneyExpr),0) amount FROM invoice_details $trendWhere GROUP BY YEAR(invoice_date), MONTH(invoice_date) ORDER BY YEAR(invoice_date) ASC, MONTH(invoice_date) ASC LIMIT 12", $invoiceParams);

    $recentInvoices = dashFetchAll($db, "SELECT inv.invoice_id, inv.customer_id, inv.invoice_type, inv.invoice_status, inv.invoice_date, inv.final_amount, ci.customer_name FROM invoice_details inv LEFT JOIN customer_info ci ON inv.customer_id = ci.id $recentInvoiceWhere ORDER BY inv.invoice_date DESC, inv.invoice_id DESC LIMIT 8", $recentInvoiceParams);
    $invoiceTrendOrdered = $invoiceTrendRows;
    $invoiceTrendFirst = !empty($invoiceTrendOrdered) ? reset($invoiceTrendOrdered) : array('amount' => 0, 'total' => 0, 'label' => '-');
    $invoiceTrendLast = !empty($invoiceTrendOrdered) ? end($invoiceTrendOrdered) : array('amount' => 0, 'total' => 0, 'label' => '-');
    $invoiceTrendDiff = (float) (isset($invoiceTrendLast['amount']) ? $invoiceTrendLast['amount'] : 0) - (float) (isset($invoiceTrendFirst['amount']) ? $invoiceTrendFirst['amount'] : 0);
    $invoiceTrendDirection = $invoiceTrendDiff > 0 ? 'up' : ($invoiceTrendDiff < 0 ? 'down' : 'flat');

    $letterTotals = dashFetchOne($db, "SELECT COUNT(*) total_letters, SUM(CASE WHEN svcdt.status = 'sent' THEN 1 ELSE 0 END) sent_letters, SUM(CASE WHEN svcdt.status <> 'sent' AND svcdt.due_date <= CURDATE() THEN 1 ELSE 0 END) due_letters, SUM(CASE WHEN svcdt.status <> 'sent' AND svcdt.due_date > CURDATE() THEN 1 ELSE 0 END) upcoming_letters FROM ca_service_dates svcdt LEFT JOIN service svc ON svcdt.service_id = svc.service_id LEFT JOIN customer_info ci ON svc.customer_id = ci.id $letterWhere", $letterParams);
    $letterActionRows = dashFetchAll($db, "SELECT svcdt.action label, COUNT(*) total, SUM(CASE WHEN svcdt.status = 'sent' THEN 1 ELSE 0 END) sent, SUM(CASE WHEN svcdt.status <> 'sent' AND svcdt.due_date <= CURDATE() THEN 1 ELSE 0 END) due FROM ca_service_dates svcdt LEFT JOIN service svc ON svcdt.service_id = svc.service_id LEFT JOIN customer_info ci ON svc.customer_id = ci.id $letterWhere GROUP BY svcdt.action ORDER BY total DESC", $letterParams);
    $recentLetters = dashFetchAll($db, "SELECT ci.id customer_id, ci.customer_name, svc.service_type, svcdt.action, svcdt.due_date, svcdt.sent_date, svcdt.status FROM ca_service_dates svcdt LEFT JOIN service svc ON svcdt.service_id = svc.service_id LEFT JOIN customer_info ci ON svc.customer_id = ci.id $letterWhere ORDER BY svcdt.due_date DESC, svcdt.action_id DESC LIMIT 8", $letterParams);

    // Lists for Modal view
    $allClientsDetailRows = dashFetchAll($db, "SELECT id, customer_name, audit_status, unique_id, certification_no, updated_cerf, certification_stage_date, standard_for FROM customer_info WHERE is_deleted <> 'YES' ORDER BY customer_name ASC, id ASC");
    $latestServiceMap = array();
    $allClientIds = array();
    foreach ($allClientsDetailRows as $clientRow) {
        if (isset($clientRow['id'])) {
            $allClientIds[] = (int) $clientRow['id'];
        }
    }
    $allClientIds = array_values(array_unique(array_filter($allClientIds)));
    if (!empty($allClientIds)) {
        $servicePlaceholders = implode(',', array_fill(0, count($allClientIds), '?'));
        $latestServiceRows = dashFetchAll($db, "SELECT s.customer_id, s.service_id, s.service_type FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE customer_id IN ($servicePlaceholders) AND (is_deleted = 'NO' OR is_deleted IS NULL) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid", $allClientIds);
        foreach ($latestServiceRows as $serviceRow) {
            $latestServiceMap[(int) $serviceRow['customer_id']] = $serviceRow;
        }
    }

    $allClientsDetail = array();
    $seenCertificationRecords = array();

    foreach ($allClientsDetailRows as $client) {
        $recordKey = dashCertificationRecordKey($client);
        if ($recordKey !== '' && isset($seenCertificationRecords[$recordKey])) {
            continue;
        }
        if ($recordKey !== '') {
            $seenCertificationRecords[$recordKey] = true;
        }
        $allClientsDetail[] = $client;
    }

    // Group clients by status for the breakdown modal
    $clientsByStatus = array();
    foreach ($allClientsDetail as $client) {
        $st = dashDerivedClientStatus($client, $latestServiceMap);
        $client['derived_status'] = $st;
        if (!isset($clientsByStatus[$st])) {
            $clientsByStatus[$st] = array();
        }
        $clientsByStatus[$st][] = $client;
    }

    // Define the specific actions tracked in intimationLetter.php
    $intimationActions = array('Suspension Reminder', 'Suspend Letter', 'Withdrawal Reminder', 'Withdrawal Letter', 'Withdraw Letter', '1st Reminder', '2nd Reminder', 'Expire Letter');
    $placeholders = implode(',', array_fill(0, count($intimationActions), '?'));
    $latestCertificationService = aitfLatestCertificationServiceSubquery('ci');

    // Fetch current pending letters matching intimationLetter.php logic (latest certification service only)
    $pendingLettersDetail = dashFetchAll($db, "SELECT ci.id as customer_id, ci.customer_name, svcdt.action, svcdt.due_date
        FROM ca_service_dates svcdt
        JOIN service svc ON svcdt.service_id = svc.service_id
        JOIN customer_info ci ON svc.customer_id = ci.id
        JOIN (
            SELECT customer_id, MAX(service_id) AS max_sid
            FROM service
            WHERE service_type IN ('CA', 'SA-I', 'SA-II', 'RC')
            GROUP BY customer_id
        ) latest ON svc.service_id = latest.max_sid
        WHERE svcdt.status <> 'sent'
        AND svcdt.due_date <= CURDATE()
        AND svcdt.is_deleted = 'N'
        AND ci.is_deleted <> 'YES'
        AND (ci.audit_status IS NULL OR ci.audit_status <> 'Withdrawn')
        AND svcdt.action IN ($placeholders)
        ORDER BY svcdt.due_date ASC", $intimationActions);
    $currentDueCount = count($pendingLettersDetail);

    // IMPORTANT: Dashboard totals should reflect the *current* state.
    // We compute pending as SUM(max(balance,0)). This will automatically:
    // - decrease when payment_received is added (balance reduces)
    // - increase back if a received entry is deleted (old balance row comes back)
    // As long as addPayment.php keeps balance = amount_decided - payment_received per row.
    $paymentTotals = dashFetchOne($db, "SELECT COUNT(*) rows_count, COALESCE(SUM(CASE WHEN " . dashAmountExpr('balance') . " > 0 THEN " . dashAmountExpr('balance') . " ELSE 0 END),0) pending_amount, COALESCE(SUM(" . dashAmountExpr('payment_received') . "),0) received_amount, COALESCE(SUM(" . dashAmountExpr('amount_decided') . "),0) decided_amount FROM payment_system WHERE service_type <> 'TDS'");
    $paymentServiceRows = dashFetchAll($db, "SELECT service_type label, COALESCE(SUM(CASE WHEN " . dashAmountExpr('balance') . " > 0 THEN " . dashAmountExpr('balance') . " ELSE 0 END),0) amount, COUNT(*) total FROM payment_system WHERE service_type <> 'TDS' GROUP BY service_type ORDER BY amount DESC LIMIT 8");
    $recentPayments = dashFetchAll($db, "SELECT
            ps.payment_sy_id as payment_group_id,
            COALESCE(ci1.id, ci2.id, ci3.id, ci4.id, ci5.id, ci6.id, ci7.id, ci8.id) as resolved_customer_id,
            NULLIF(TRIM(ps.customer_id), '') as ps_customer_id,
            NULLIF(TRIM(psi.customer_id), '') as psi_customer_id,
            NULLIF(TRIM(psg.group_customer_id), '') as ps_group_customer_id,
            COALESCE(NULLIF(TRIM(ps.customer_id), ''), NULLIF(TRIM(psi.customer_id), ''), NULLIF(TRIM(psg.group_customer_id), '')) as customer_id,
            COALESCE(
                NULLIF(TRIM(ci1.customer_name), ''),
                NULLIF(TRIM(ci2.customer_name), ''),
                NULLIF(TRIM(ci3.customer_name), ''),
                NULLIF(TRIM(ci4.customer_name), ''),
                NULLIF(TRIM(ci5.customer_name), ''),
                NULLIF(TRIM(ci6.customer_name), ''),
                NULLIF(TRIM(ci7.customer_name), ''),
                NULLIF(TRIM(ci8.customer_name), ''),
                NULLIF(TRIM(oa1.org_name), ''),
                NULLIF(TRIM(oa2.org_name), '')
            ) as customer_name,
            ps.service_type, ps.amount_decided, ps.payment_received, ps.balance, ps.payment_date, ps.cheque_details
        FROM (
            SELECT * FROM payment_system WHERE service_type <> 'TDS' ORDER BY payment_date DESC, payment_id DESC LIMIT 10
        ) ps
        LEFT JOIN payment_system_id psi ON ps.payment_sy_id = psi.payment_sy_id
        LEFT JOIN (SELECT payment_sy_id, MAX(NULLIF(TRIM(customer_id), '')) as group_customer_id FROM payment_system GROUP BY payment_sy_id) psg ON ps.payment_sy_id = psg.payment_sy_id
        LEFT JOIN customer_info ci1 ON TRIM(ps.customer_id) = CAST(ci1.id AS CHAR)
        LEFT JOIN customer_info ci2 ON TRIM(ps.customer_id) = TRIM(ci2.unique_id)
        LEFT JOIN customer_info ci3 ON TRIM(psi.customer_id) = CAST(ci3.id AS CHAR)
        LEFT JOIN customer_info ci4 ON TRIM(psi.customer_id) = TRIM(ci4.unique_id)
        LEFT JOIN customer_info ci5 ON REPLACE(UPPER(TRIM(ps.customer_id)), 'ID:', '') = CAST(ci5.id AS CHAR)
        LEFT JOIN customer_info ci6 ON REPLACE(UPPER(TRIM(psi.customer_id)), 'ID:', '') = CAST(ci6.id AS CHAR)
        LEFT JOIN customer_info ci7 ON TRIM(psg.group_customer_id) = CAST(ci7.id AS CHAR)
        LEFT JOIN customer_info ci8 ON TRIM(psg.group_customer_id) = TRIM(ci8.unique_id)
        LEFT JOIN online_audit_applications oa1 ON TRIM(ps.customer_id) = CAST(oa1.id AS CHAR)
        LEFT JOIN online_audit_applications oa2 ON TRIM(psi.customer_id) = CAST(oa2.id AS CHAR)
        ORDER BY ps.payment_date DESC, ps.payment_id DESC LIMIT 10");

    $clientTotals = array(
        'total_clients' => count($allClientsDetail),
        'canceled_clients' => isset($clientsByStatus['Application canceled']) ? count($clientsByStatus['Application canceled']) : 0,
        'withdrawn_clients' => isset($clientsByStatus['Withdrawn']) ? count($clientsByStatus['Withdrawn']) : 0,
        'deleted_clients' => 0
    );
    $clientStatusRows = array();
    foreach ($clientsByStatus as $statusLabel => $statusClients) {
        $clientStatusRows[] = array(
            'label' => $statusLabel,
            'total' => count($statusClients)
        );
    }
    usort($clientStatusRows, function ($a, $b) {
        if ($a['total'] == $b['total']) {
            return strcmp($a['label'], $b['label']);
        }
        return ($a['total'] < $b['total']) ? 1 : -1;
    });
    $normalizedClientTotals = array();
    foreach ($clientStatusRows as $sRow) {
        $normalizedClientTotals[$sRow['label']] = $sRow['total'];
    }

    $statusMetaParts = array();
    foreach ($clientStatusRows as $sRow) {
        $statusMetaParts[] = $sRow['label'] . ": " . $sRow['total'];
    }
    $clientStatusMeta = implode(' | ', $statusMetaParts);

    $clientServiceRows = dashFetchAll($db, "SELECT service_type label, COUNT(*) total FROM service WHERE (is_deleted = 'NO' OR is_deleted IS NULL) GROUP BY service_type ORDER BY total DESC LIMIT 8");
    $applicationTotals = dashFetchOne($db, "SELECT COUNT(*) online_total, SUM(CASE WHEN read_flag = 0 THEN 1 ELSE 0 END) unread_total FROM online_audit_applications");
    $auditApplicationTotals = dashFetchOne($db, "SELECT COUNT(*) audit_total, SUM(CASE WHEN review_date IS NOT NULL THEN 1 ELSE 0 END) reviewed_total FROM audit_application");
    $recentClients = dashFetchAll($db, "SELECT ci.*, (SELECT COUNT(*) FROM service svc WHERE svc.customer_id = ci.id AND (svc.is_deleted = 'NO' OR svc.is_deleted IS NULL)) as service_count, (SELECT COALESCE(SUM(CASE WHEN " . dashAmountExpr('ps.balance') . " > 0 THEN " . dashAmountExpr('ps.balance') . " ELSE 0 END),0) FROM payment_system ps WHERE ps.customer_id = ci.id AND ps.service_type <> 'TDS') as pending_amount FROM (SELECT id, customer_name, unique_id, audit_status, next_service, contact_person, contact, email_id FROM customer_info WHERE is_deleted <> 'YES' ORDER BY id DESC LIMIT 10) ci ORDER BY ci.id DESC");
    foreach ($recentClients as $idx => $row) {
        $recentClients[$idx]['derived_status'] = dashDerivedClientStatus($row, $latestServiceMap);
    }
    $recentOnlineApplications = dashFetchAll($db, "SELECT id, org_name, contact_person, phone, email, standard_applied, created_at, read_flag FROM online_audit_applications ORDER BY created_at DESC LIMIT 8");

    // Pending Payments Detailed List
    // Robust pending list: group by payment_sy_id (one client payment ledger)
    // and compute pending from decided - received (NOT from stored balance), because
    // older rows may have inconsistent balance formats.
    $pendingPaymentsDetail = dashFetchAll($db, "SELECT
            sub.payment_sy_id as payment_group_id,
            COALESCE(ci1.id, ci2.id, ci3.id, ci4.id, ci5.id, ci6.id, ci7.id, ci8.id) as resolved_customer_id,
            sub.ps_customer_id,
            NULLIF(TRIM(psi.customer_id), '') as psi_customer_id,
            sub.ps_customer_id as ps_group_customer_id,
            COALESCE(sub.ps_customer_id, NULLIF(TRIM(psi.customer_id), '')) as customer_id,
            COALESCE(
                NULLIF(TRIM(ci1.customer_name), ''),
                NULLIF(TRIM(ci2.customer_name), ''),
                NULLIF(TRIM(ci3.customer_name), ''),
                NULLIF(TRIM(ci4.customer_name), ''),
                NULLIF(TRIM(ci5.customer_name), ''),
                NULLIF(TRIM(ci6.customer_name), ''),
                NULLIF(TRIM(ci7.customer_name), ''),
                NULLIF(TRIM(ci8.customer_name), ''),
                NULLIF(TRIM(oa1.org_name), ''),
                NULLIF(TRIM(oa2.org_name), '')
            ) as customer_name,
            sub.entry_count,
            sub.pending_amount
        FROM (
            SELECT payment_sy_id,
                   MAX(NULLIF(TRIM(customer_id), '')) as ps_customer_id,
                   COUNT(*) as entry_count,
                   GREATEST(SUM(" . dashAmountExpr('amount_decided') . ") - SUM(" . dashAmountExpr('payment_received') . "), 0) as pending_amount
            FROM payment_system
            WHERE service_type <> 'TDS'
            GROUP BY payment_sy_id
            HAVING pending_amount > 0
        ) sub
        LEFT JOIN payment_system_id psi ON sub.payment_sy_id = psi.payment_sy_id
        LEFT JOIN customer_info ci1 ON sub.ps_customer_id = CAST(ci1.id AS CHAR)
        LEFT JOIN customer_info ci2 ON sub.ps_customer_id = TRIM(ci2.unique_id)
        LEFT JOIN customer_info ci3 ON TRIM(psi.customer_id) = CAST(ci3.id AS CHAR)
        LEFT JOIN customer_info ci4 ON TRIM(psi.customer_id) = TRIM(ci4.unique_id)
        LEFT JOIN customer_info ci5 ON REPLACE(UPPER(sub.ps_customer_id), 'ID:', '') = CAST(ci5.id AS CHAR)
        LEFT JOIN customer_info ci6 ON REPLACE(UPPER(TRIM(psi.customer_id)), 'ID:', '') = CAST(ci6.id AS CHAR)
        LEFT JOIN customer_info ci7 ON sub.ps_customer_id = CAST(ci7.id AS CHAR)
        LEFT JOIN customer_info ci8 ON sub.ps_customer_id = TRIM(ci8.unique_id)
        LEFT JOIN online_audit_applications oa1 ON sub.ps_customer_id = CAST(oa1.id AS CHAR)
        LEFT JOIN online_audit_applications oa2 ON TRIM(psi.customer_id) = CAST(oa2.id AS CHAR)
        ORDER BY sub.pending_amount DESC");

    $customerLookupRows = dashFetchAll($db, "SELECT id, unique_id, customer_name FROM customer_info");
    $serviceLookupRows = dashFetchAll($db, "SELECT svc.service_id, ci.id as customer_id, ci.customer_name FROM service svc LEFT JOIN customer_info ci ON svc.customer_id = ci.id WHERE (svc.is_deleted = 'NO' OR svc.is_deleted IS NULL)");
    $paymentAliasRows = dashFetchAll($db, "SELECT customer_ref, client_name, customer_id FROM payment_client_alias");
    $customerIdNameMap = array();
    $customerUniqueNameMap = array();
    $customerNameMap = array();
    $serviceIdNameMap = array();
    $paymentAliasNameMap = array();
    $paymentAliasIdMap = array();
    foreach ($customerLookupRows as $lookupRow) {
        $lookupId = isset($lookupRow['id']) ? (int) $lookupRow['id'] : 0;
        $lookupName = isset($lookupRow['customer_name']) ? trim((string) $lookupRow['customer_name']) : '';
        $lookupUnique = isset($lookupRow['unique_id']) ? trim((string) $lookupRow['unique_id']) : '';

        if ($lookupId > 0 && $lookupName !== '') {
            $customerIdNameMap[(string) $lookupId] = array('id' => $lookupId, 'name' => $lookupName);
        }

        $normalizedUnique = dashNormalizeKey($lookupUnique);
        if ($normalizedUnique !== '' && $lookupName !== '') {
            $customerUniqueNameMap[$normalizedUnique] = array('id' => $lookupId, 'name' => $lookupName);
        }

        $normalizedName = dashNormalizeKey($lookupName);
        if ($normalizedName !== '' && $lookupName !== '') {
            $customerNameMap[$normalizedName] = array('id' => $lookupId, 'name' => $lookupName);
        }
    }

    foreach ($serviceLookupRows as $serviceRow) {
        $serviceId = isset($serviceRow['service_id']) ? (int) $serviceRow['service_id'] : 0;
        $serviceCustomerId = isset($serviceRow['customer_id']) ? (int) $serviceRow['customer_id'] : 0;
        $serviceCustomerName = isset($serviceRow['customer_name']) ? trim((string) $serviceRow['customer_name']) : '';
        if ($serviceId > 0 && $serviceCustomerId > 0 && $serviceCustomerName !== '') {
            $serviceIdNameMap[(string) $serviceId] = array('id' => $serviceCustomerId, 'name' => $serviceCustomerName);
        }
    }

    foreach ($paymentAliasRows as $aliasRow) {
        $aliasRef = isset($aliasRow['customer_ref']) ? trim((string) $aliasRow['customer_ref']) : '';
        $aliasName = isset($aliasRow['client_name']) ? trim((string) $aliasRow['client_name']) : '';
        $aliasCustomerId = isset($aliasRow['customer_id']) ? (int) $aliasRow['customer_id'] : 0;
        if ($aliasRef === '' || $aliasName === '') {
            continue;
        }

        $aliasKey = dashNormalizeKey($aliasRef);
        if ($aliasKey !== '') {
            $paymentAliasNameMap[$aliasKey] = $aliasName;
            if ($aliasCustomerId > 0) {
                $paymentAliasIdMap[$aliasKey] = $aliasCustomerId;
            }
        }

        $aliasNumeric = dashCandidateNumericId($aliasRef);
        if ($aliasNumeric !== '') {
            $paymentAliasNameMap[$aliasNumeric] = $aliasName;
            if ($aliasCustomerId > 0) {
                $paymentAliasIdMap[$aliasNumeric] = $aliasCustomerId;
            }
        }
    }

    foreach ($recentPayments as $idx => $paymentRow) {
        $recentPayments[$idx] = dashResolveClientFromRow($paymentRow, $customerIdNameMap, $customerUniqueNameMap, $customerNameMap, $serviceIdNameMap, $paymentAliasNameMap, $paymentAliasIdMap);
    }

    foreach ($pendingPaymentsDetail as $idx => $paymentRow) {
        $pendingPaymentsDetail[$idx] = dashResolveClientFromRow($paymentRow, $customerIdNameMap, $customerUniqueNameMap, $customerNameMap, $serviceIdNameMap, $paymentAliasNameMap, $paymentAliasIdMap);
    }

    // $pendingPaymentsDetail is already client-wise (GROUP BY payment_sy_id)
    // Ensure numeric types + deterministic sorting.
    foreach ($pendingPaymentsDetail as $idx => $row) {
        $pendingPaymentsDetail[$idx]['pending_amount'] = isset($row['pending_amount']) ? (float) $row['pending_amount'] : 0.0;
        $pendingPaymentsDetail[$idx]['entry_count'] = isset($row['entry_count']) ? (int) $row['entry_count'] : 0;
        $pendingPaymentsDetail[$idx]['resolved_customer_id'] = isset($row['resolved_customer_id']) ? (int) $row['resolved_customer_id'] : 0;
        $pendingPaymentsDetail[$idx]['payment_group_id'] = isset($row['payment_group_id']) ? (int) $row['payment_group_id'] : 0;
    }

    usort($pendingPaymentsDetail, function ($a, $b) {
        $aAmt = isset($a['pending_amount']) ? (float) $a['pending_amount'] : 0.0;
        $bAmt = isset($b['pending_amount']) ? (float) $b['pending_amount'] : 0.0;
        if ($aAmt === $bAmt) {
            return 0;
        }
        return ($aAmt < $bAmt) ? 1 : -1;
    });

    // Keep card total and modal total consistent with client-wise pending summary
    $paymentTotals['pending_amount'] = 0;
    foreach ($pendingPaymentsDetail as $pendingClientRow) {
        $paymentTotals['pending_amount'] += isset($pendingClientRow['pending_amount']) ? (float) $pendingClientRow['pending_amount'] : 0;
    }

    $letterActionOptions = dashFetchAll($db, "SELECT DISTINCT action FROM ca_service_dates WHERE is_deleted = 'N' ORDER BY action");
    $totalInvoiceAmount = isset($invoiceTotals['total_amount']) ? $invoiceTotals['total_amount'] : 0;
    $receivedInvoiceAmount = isset($invoiceTotals['received_amount']) ? $invoiceTotals['received_amount'] : 0;
    $pendingInvoiceAmount = isset($invoiceTotals['pending_amount']) ? $invoiceTotals['pending_amount'] : 0;
    $totalClients = isset($clientTotals['total_clients']) ? $clientTotals['total_clients'] : 0;
    $activeClients = $totalClients - (isset($clientTotals['deleted_clients']) ? $clientTotals['deleted_clients'] : 0);
    $cardSets = array(
        'business' => array(
            array('label' => 'Final Invoice Value', 'value' => dashMoney($totalInvoiceAmount), 'meta' => dashInt(isset($invoiceTotals['total_count']) ? $invoiceTotals['total_count'] : 0) . ' invoices generated', 'icon' => 'fa-inr', 'raw' => $totalInvoiceAmount, 'format' => 'money'),
            array('label' => 'Payment Pending', 'value' => dashMoney(isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0), 'meta' => 'Click to view pending payment', 'icon' => 'fa-credit-card', 'raw' => isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0, 'format' => 'money', 'id' => 'pendingPaymentsCard'),
            array('label' => 'Total Due Letters', 'value' => dashInt(isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0), 'meta' => 'Current Due: ' . dashInt($currentDueCount), 'icon' => 'fa-envelope-o', 'raw' => isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0, 'format' => 'int', 'id' => 'dueLettersCard'),
            array('label' => 'Total Registered Clients', 'value' => dashInt($activeClients), 'meta' => 'Click to view status breakdown', 'icon' => 'fa-building', 'raw' => $activeClients, 'format' => 'int', 'id' => 'totalClientsCard')
        ),
        'invoice' => array(
            array('label' => 'Final Invoice Value', 'value' => dashMoney($totalInvoiceAmount), 'meta' => 'Filtered total invoice amount', 'icon' => 'fa-inr', 'raw' => $totalInvoiceAmount, 'format' => 'money'),
            array('label' => 'Received Value', 'value' => dashMoney($receivedInvoiceAmount), 'meta' => dashPercent($receivedInvoiceAmount, $totalInvoiceAmount) . ' recovery', 'icon' => 'fa-check-circle', 'raw' => $receivedInvoiceAmount, 'format' => 'money'),
            array('label' => 'Pending Value', 'value' => dashMoney($pendingInvoiceAmount), 'meta' => dashPercent($pendingInvoiceAmount, $totalInvoiceAmount) . ' still open', 'icon' => 'fa-clock-o', 'raw' => $pendingInvoiceAmount, 'format' => 'money'),
            array('label' => 'Invoices Count', 'value' => dashInt(isset($invoiceTotals['total_count']) ? $invoiceTotals['total_count'] : 0), 'meta' => 'Generated in selected range', 'icon' => 'fa-file-text-o', 'raw' => isset($invoiceTotals['total_count']) ? $invoiceTotals['total_count'] : 0, 'format' => 'int')
        ),
        'letters' => array(
            array('label' => 'Total Letters', 'value' => dashInt(isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0), 'meta' => 'All filtered letters', 'icon' => 'fa-envelope', 'raw' => isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0, 'format' => 'int'),
            array('label' => 'Total Due Letters', 'value' => dashInt(isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0), 'meta' => 'Current Due: ' . dashInt($currentDueCount), 'icon' => 'fa-exclamation-circle', 'raw' => isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0, 'format' => 'int', 'id' => 'dueLettersCard'),
            array('label' => 'Sent Letters', 'value' => dashInt(isset($letterTotals['sent_letters']) ? $letterTotals['sent_letters'] : 0), 'meta' => dashPercent(isset($letterTotals['sent_letters']) ? $letterTotals['sent_letters'] : 0, isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0) . ' completed', 'icon' => 'fa-send', 'raw' => isset($letterTotals['sent_letters']) ? $letterTotals['sent_letters'] : 0, 'format' => 'int'),
            array('label' => 'Upcoming', 'value' => dashInt(isset($letterTotals['upcoming_letters']) ? $letterTotals['upcoming_letters'] : 0), 'meta' => 'Not due yet', 'icon' => 'fa-calendar', 'raw' => isset($letterTotals['upcoming_letters']) ? $letterTotals['upcoming_letters'] : 0, 'format' => 'int')
        ),
        'payments' => array(
            array('label' => 'Payment Decided', 'value' => dashMoney(isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0), 'meta' => 'Total committed fee', 'icon' => 'fa-calculator', 'raw' => isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0, 'format' => 'money'),
            array('label' => 'Payment Received', 'value' => dashMoney(isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0), 'meta' => dashPercent(isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0, isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0) . ' collected', 'icon' => 'fa-bank', 'raw' => isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0, 'format' => 'money'),
            array('label' => 'Payment Pending', 'value' => dashMoney(isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0), 'meta' => 'Click to view pending payment', 'icon' => 'fa-hourglass-half', 'raw' => isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0, 'format' => 'money', 'id' => 'pendingPaymentsCard'),
            array('label' => 'Open Payment Rows', 'value' => dashInt(isset($paymentTotals['rows_count']) ? $paymentTotals['rows_count'] : 0), 'meta' => 'Service payment entries', 'icon' => 'fa-list', 'raw' => isset($paymentTotals['rows_count']) ? $paymentTotals['rows_count'] : 0, 'format' => 'int')
        ),
        'clients' => array(
            array('label' => 'Total Registered Clients', 'value' => dashInt($activeClients), 'meta' => $clientStatusMeta, 'icon' => 'fa-building', 'raw' => $activeClients, 'format' => 'int'),
            array('label' => 'Online Applications', 'value' => dashInt(isset($applicationTotals['online_total']) ? $applicationTotals['online_total'] : 0), 'meta' => dashInt(isset($applicationTotals['unread_total']) ? $applicationTotals['unread_total'] : 0) . ' unread', 'icon' => 'fa-inbox', 'raw' => isset($applicationTotals['online_total']) ? $applicationTotals['online_total'] : 0, 'format' => 'int'),
            array('label' => 'Audit Applications', 'value' => dashInt(isset($auditApplicationTotals['audit_total']) ? $auditApplicationTotals['audit_total'] : 0), 'meta' => dashInt(isset($auditApplicationTotals['reviewed_total']) ? $auditApplicationTotals['reviewed_total'] : 0) . ' reviewed', 'icon' => 'fa-clipboard', 'raw' => isset($auditApplicationTotals['audit_total']) ? $auditApplicationTotals['audit_total'] : 0, 'format' => 'int'),
            array('label' => 'Withdrawn Clients', 'value' => dashInt(isset($normalizedClientTotals['Withdrawn']) ? $normalizedClientTotals['Withdrawn'] : 0), 'meta' => 'Customer lifecycle risk', 'icon' => 'fa-sign-out', 'raw' => isset($normalizedClientTotals['Withdrawn']) ? $normalizedClientTotals['Withdrawn'] : 0, 'format' => 'int')
        )
    );
    $dashboardCards = isset($cardSets[$dashboardType]) ? $cardSets[$dashboardType] : $cardSets['business'];
    $insightSets = array(
        'business' => array(
            array('title' => 'Live Business Snapshot', 'text' => 'Invoices, payments, clients and letters are calculated from database rows at page load.'),
            array('title' => 'Collection Health', 'text' => dashPercent(isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0, isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0) . ' of decided payment is marked received.'),
            array('title' => 'Action Load', 'text' => dashInt(isset($applicationTotals['unread_total']) ? $applicationTotals['unread_total'] : 0) . ' unread applications and ' . dashInt(isset($letterTotals['due_letters']) ? $letterTotals['due_letters'] : 0) . ' due letters need action.')
        ),
        'invoice' => array(
            array('title' => 'Invoice Recovery', 'text' => dashPercent($receivedInvoiceAmount, $totalInvoiceAmount) . ' of filtered invoice value is received.'),
            array('title' => 'Open Invoice Value', 'text' => dashMoney($pendingInvoiceAmount) . ' is still marked not received.'),
            array('title' => 'Invoice Volume', 'text' => dashInt(isset($invoiceTotals['total_count']) ? $invoiceTotals['total_count'] : 0) . ' invoices match the selected filters.')
        ),
        'letters' => array(
            array('title' => 'Letter Completion', 'text' => dashPercent(isset($letterTotals['sent_letters']) ? $letterTotals['sent_letters'] : 0, isset($letterTotals['total_letters']) ? $letterTotals['total_letters'] : 0) . ' filtered letters are sent.'),
            array('title' => 'Due Queue', 'text' => dashInt(isset($letterTotals['due_letters']) ? $letterTotals['due_letters'] : 0) . ' letters are due and pending.'),
            array('title' => 'Upcoming Work', 'text' => dashInt(isset($letterTotals['upcoming_letters']) ? $letterTotals['upcoming_letters'] : 0) . ' letters are scheduled for future dates.')
        ),
        'payments' => array(
            array('title' => 'Payment Collection', 'text' => dashMoney(isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0) . ' has been received against decided payment.'),
            array('title' => 'Pending Collection', 'text' => dashMoney(isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0) . ' is open in payment rows.'),
            array('title' => 'Service Exposure', 'text' => dashInt(isset($paymentTotals['rows_count']) ? $paymentTotals['rows_count'] : 0) . ' service payment entries are being tracked.')
        ),
        'clients' => array(
            array('title' => 'Client Base', 'text' => dashInt($activeClients) . ' active client accounts are available to open.'),
            array('title' => 'Applications', 'text' => dashInt(isset($applicationTotals['online_total']) ? $applicationTotals['online_total'] : 0) . ' online applications and ' . dashInt(isset($auditApplicationTotals['audit_total']) ? $auditApplicationTotals['audit_total'] : 0) . ' audit applications exist.'),
            array('title' => 'Client Risk', 'text' => dashInt(isset($normalizedClientTotals['Withdrawn']) ? $normalizedClientTotals['Withdrawn'] : 0) . ' withdrawn and ' . dashInt(isset($normalizedClientTotals['Application canceled']) ? $normalizedClientTotals['Application canceled'] : 0) . ' canceled clients are visible.')
        )
    );
    $selectedInsights = isset($insightSets[$dashboardType]) ? $insightSets[$dashboardType] : $insightSets['business'];

    $chartData = array(
        'invoiceTrend' => $invoiceTrendOrdered,
        'invoiceTypes' => $invoiceTypeRows,
        'letterActions' => $letterActionRows,
        'paymentServices' => $paymentServiceRows,
        'clientStatuses' => $clientStatusRows,
        'clientServices' => $clientServiceRows,
        'letterTotals' => array(
            array('label' => 'Due', 'total' => (int) (isset($letterTotals['due_letters']) ? $letterTotals['due_letters'] : 0)),
            array('label' => 'Sent', 'total' => (int) (isset($letterTotals['sent_letters']) ? $letterTotals['sent_letters'] : 0)),
            array('label' => 'Upcoming', 'total' => (int) (isset($letterTotals['upcoming_letters']) ? $letterTotals['upcoming_letters'] : 0))
        )
    );
    ?>

    <style>
        /* ==========================================================
           ADMIN DASHBOARD — MODERN EXECUTIVE SAAS DESIGN SYSTEM
           ========================================================== */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        html, body, #wrapper, #page-content-wrapper, .admin-dash-wrap {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            background-color: #f8fafc !important;
            color: #0f172a !important;
            -webkit-font-smoothing: antialiased !important;
        }

        .admin-dash-wrap {
            min-height: 100vh;
            padding-bottom: 48px;
        }

        .admin-dashboard {
            max-width: 1540px !important;
            margin: 0 auto !important;
            padding: 10px 18px 60px 18px !important;
        }

        /* Top Bar */
        .dash-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
        }

        .dash-section-title {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            padding: 8px 18px;
            font-size: 17px;
            font-weight: 800;
            color: #ffffff;
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%);
            border-radius: 9px;
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.22);
            letter-spacing: -0.2px;
        }

        .dash-module-badge {
            display: inline-block;
            margin-left: 6px;
            padding: 2px 10px;
            border-radius: 12px;
            color: #0d4d8b;
            background: #ffffff;
            font-size: 11px;
            font-weight: 800;
            vertical-align: middle;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
            letter-spacing: 0.3px;
        }

        .live-pulse {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 20px;
            background: #ecfdf5;
            color: #047857;
            font-weight: 700;
            font-size: 12px;
            border: 1px solid #a7f3d0;
            box-shadow: 0 1px 3px rgba(16, 185, 129, 0.1);
        }

        .live-pulse span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
            animation: dashPulse 1.6s infinite;
        }

        @keyframes dashPulse {
            70% {
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* Module Switcher Pills */
        .dash-module-nav {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            gap: 8px;
            margin-bottom: 12px;
            padding-bottom: 4px;
            scrollbar-width: thin;
        }

        .dash-module-tab {
            flex: 0 0 auto;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: 36px;
            padding: 0 16px;
            border-radius: 20px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none !important;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            cursor: pointer;
        }

        .dash-module-tab:hover {
            background: #f1f5f9;
            color: #0d4d8b;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .dash-module-tab.active {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            color: #ffffff !important;
            border-color: #0d4d8b !important;
            box-shadow: 0 2px 8px rgba(13, 77, 139, 0.25) !important;
            font-weight: 700 !important;
        }

        /* Filter Panel */
        .dash-filter {
            margin: 0 0 16px 0;
            display: grid;
            grid-template-columns: repeat(8, minmax(0, 1fr));
            gap: 10px;
            align-items: flex-end;
            padding: 14px 18px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.03);
        }

        .dash-filter-item {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-width: 0;
        }

        .dash-filter-item label {
            display: block !important;
            color: #0d4d8b !important;
            font-size: 11px !important;
            text-transform: uppercase !important;
            font-weight: 700 !important;
            margin: 0 0 5px 0 !important;
            padding: 0 !important;
            height: 16px !important;
            line-height: 16px !important;
            white-space: nowrap !important;
            letter-spacing: 0.5px !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .dash-filter .form-control {
            width: 100% !important;
            height: 38px !important;
            min-height: 38px !important;
            max-height: 38px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            color: #0f172a !important;
            background: #f8fafc !important;
            font-weight: 500 !important;
            font-size: 12.5px !important;
            padding: 0 10px !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            transition: all 0.15s ease !important;
        }

        .dash-filter select.form-control {
            padding-right: 28px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 8px center !important;
            background-size: 9px !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            cursor: pointer !important;
        }

        .dash-filter .form-control:focus {
            border-color: #0d4d8b !important;
            background-color: #ffffff !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12) !important;
        }

        .dash-btn {
            width: 100% !important;
            height: 38px !important;
            min-height: 38px !important;
            max-height: 38px !important;
            border-radius: 8px !important;
            border: 0 !important;
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 12.5px !important;
            padding: 0 12px !important;
            box-shadow: 0 2px 6px rgba(13, 77, 139, 0.2) !important;
            transition: all 0.15s ease;
            cursor: pointer !important;
            white-space: nowrap !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }

        .dash-btn:hover {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%) !important;
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.28) !important;
            transform: translateY(-1px);
            color: #ffffff !important;
        }

        /* KPI Cards Grid */
        .dash-card-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(190px, 1fr));
            gap: 14px;
            margin-top: 14px;
        }

        .dash-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 18px 16px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
            position: relative;
            overflow: hidden;
            min-height: 115px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .dash-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
            border-color: #cbd5e1;
        }

        .dash-card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            height: 3.5px;
            background: linear-gradient(90deg, #0d4d8b 0%, #2563eb 100%);
        }

        .dash-card:nth-child(2)::before {
            background: linear-gradient(90deg, #059669 0%, #10b981 100%);
        }

        .dash-card:nth-child(3)::before {
            background: linear-gradient(90deg, #d97706 0%, #f59e0b 100%);
        }

        .dash-card:nth-child(4)::before {
            background: linear-gradient(90deg, #7c3aed 0%, #8b5cf6 100%);
        }

        .dash-card .icon {
            position: absolute;
            right: 14px;
            top: 14px;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            transition: all 0.2s ease;
        }

        .dash-card:nth-child(2) .icon { background: #ecfdf5; color: #059669; }
        .dash-card:nth-child(3) .icon { background: #fff7ed; color: #ea580c; }
        .dash-card:nth-child(4) .icon { background: #f5f3ff; color: #7c3aed; }

        .dash-card:hover .icon {
            transform: scale(1.08);
        }

        .dash-card .label {
            color: #64748b;
            display: block;
            padding: 0;
            font-size: 11px;
            text-align: left;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            background: transparent;
            margin-bottom: 4px;
            padding-right: 38px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dash-card .value {
            color: #0f172a !important;
            font-size: 22px !important;
            font-weight: 800 !important;
            margin: 2px 0 4px !important;
            white-space: nowrap !important;
            word-break: keep-all !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            line-height: 1.2 !important;
            letter-spacing: -0.3px !important;
            display: block !important;
        }

        .dash-card .meta {
            color: #64748b;
            margin: 0;
            font-size: 11.5px;
            font-weight: 600;
            padding-right: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dash-spark {
            position: absolute;
            left: 16px;
            right: 16px;
            bottom: 3px;
            height: 3px;
            display: flex;
            align-items: flex-end;
            gap: 3px;
            opacity: 0.3;
            pointer-events: none;
        }

        .dash-spark span {
            flex: 1;
            min-width: 3px;
            border-radius: 2px 2px 0 0;
            background: #cbd5e1;
        }

        .dash-spark span:nth-child(2n) { background: #93c5fd; }
        .dash-spark span:nth-child(3n) { background: #fdba74; }

        /* Insight Strip */
        .insight-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-top: 14px;
        }

        .insight-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px 14px 48px;
            color: #475569;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.03);
            line-height: 1.45;
            position: relative;
            overflow: hidden;
            min-height: 80px;
            font-weight: 500;
            font-size: 12.5px;
        }

        .insight-box:before {
            content: "";
            position: absolute;
            left: 14px;
            top: 14px;
            width: 22px;
            height: 22px;
            border-radius: 6px;
            background: linear-gradient(135deg, #0d4d8b, #1e40af);
            box-shadow: 0 2px 6px rgba(13, 77, 139, 0.25);
        }

        .insight-box:after {
            content: "";
            position: absolute;
            left: 21px;
            top: 21px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ffffff;
        }

        .insight-box:nth-child(2):before {
            background: linear-gradient(135deg, #059669, #10b981);
        }

        .insight-box:nth-child(3):before {
            background: linear-gradient(135deg, #d97706, #f59e0b);
        }

        .insight-box strong {
            display: block;
            color: #0f172a;
            font-size: 13.5px;
            margin-bottom: 3px;
            font-weight: 700;
        }

        .insight-box strong + br {
            display: none;
        }

        /* Section Grids & Panels */
        .dash-section-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(330px, 1fr);
            gap: 14px;
            margin-top: 14px;
        }

        .dash-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.03);
            overflow: hidden;
        }

        .dash-panel h3 {
            margin: 0 0 14px;
            color: #0f172a;
            font-size: 15px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: -0.2px;
        }

        .dash-panel h3:before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }

        .dash-chart {
            width: 100%;
            height: 280px;
            display: block;
        }

        /* Tables */
        .dash-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12.5px;
        }

        .dash-table th {
            color: #475569 !important;
            text-transform: uppercase;
            font-size: 11px;
            border: none;
            border-bottom: 2px solid #e2e8f0;
            padding: 10px 12px;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
            font-weight: 700;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .dash-table th:first-child {
            border-radius: 6px 0 0 0;
        }

        .dash-table th:last-child {
            border-radius: 0 6px 0 0;
        }

        .dash-table td {
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            padding: 9px 12px;
            vertical-align: middle;
            font-weight: 500;
        }

        .dash-table small {
            color: #64748b;
            font-weight: 500;
        }

        .dash-table tr.clickable-row {
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .dash-table tr.clickable-row:hover td {
            background: #f1f5f9;
            color: #0f172a;
        }

        .dash-pill {
            display: inline-block;
            border-radius: 20px;
            padding: 2px 9px;
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            font-size: 11px;
            border: 1px solid #bae6fd;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .letter-rank-list {
            display: flex;
            flex-direction: column;
            gap: 9px;
            max-height: 320px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .letter-rank-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 9px 12px;
            background: #f8fafc;
        }

        .letter-rank-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: #0f172a;
            font-weight: 700;
            font-size: 12px;
        }

        .letter-rank-count {
            flex: 0 0 auto;
            color: #0d4d8b;
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 700;
        }

        .letter-rank-track {
            height: 5px;
            border-radius: 10px;
            background: #e2e8f0;
            overflow: hidden;
            margin: 7px 0 5px;
        }

        .letter-rank-fill {
            height: 100%;
            border-radius: 10px;
            background: linear-gradient(90deg, #0d4d8b, #10b981);
        }

        .trend-chip span {
            display: block;
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .trend-chip strong {
            display: block;
            color: #0f172a;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .trend-chip.up {
            border-color: #a7f3d0;
            background: #ecfdf5;
        }

        .trend-chip.up strong {
            color: #047857;
        }

        .trend-chip.down {
            border-color: #fecaca;
            background: #fef2f2;
        }

        .trend-chip.down strong {
            color: #dc2626;
        }

        /* Modals */
        .modal-content {
            border-radius: 14px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.2) !important;
            overflow: hidden !important;
        }

        .modal-header {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            color: #ffffff !important;
            padding: 14px 20px !important;
            border: none !important;
        }

        .modal-header h4 {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 16px !important;
            margin: 0 !important;
        }

        .modal-header .close {
            color: #ffffff !important;
            opacity: 0.85 !important;
            font-size: 24px !important;
            line-height: 1 !important;
        }

        .modal-header .close:hover {
            opacity: 1 !important;
        }

        /* =====================================================================
           DARK MODE SUPPORT
           ===================================================================== */
        html.theme-dark,
        html.theme-dark body,
        html.theme-dark #wrapper,
        html.theme-dark #page-content-wrapper,
        html.theme-dark .admin-dash-wrap {
            background-color: #0a0e14 !important;
            color: #dbe3f0 !important;
        }

        html.theme-dark .dash-module-tab {
            background: #111722 !important;
            border-color: #232c3d !important;
            color: #93a1b8 !important;
        }

        html.theme-dark .dash-module-tab:hover {
            background: #161d2b !important;
            color: #ffffff !important;
        }

        html.theme-dark .dash-module-tab.active {
            background: #1e3a5f !important;
            border-color: #2e5a8f !important;
            color: #ffffff !important;
        }

        html.theme-dark .dash-filter {
            background: #111722 !important;
            border-color: #232c3d !important;
        }

        html.theme-dark .dash-filter label {
            color: #7aa9f5 !important;
        }

        html.theme-dark .dash-filter .form-control {
            background-color: #161d2b !important;
            border-color: #2e3a4f !important;
            color: #dbe3f0 !important;
        }

        html.theme-dark .dash-card {
            background: #111722 !important;
            border-color: #232c3d !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3) !important;
        }

        html.theme-dark .dash-card:hover {
            border-color: #2e3a4f !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45) !important;
        }

        html.theme-dark .dash-card .value {
            color: #f1f5f9 !important;
        }

        html.theme-dark .dash-card .label,
        html.theme-dark .dash-card .meta {
            color: #93a1b8 !important;
        }

        html.theme-dark .dash-card .icon {
            background: #161d2b !important;
            color: #60a5fa !important;
        }

        html.theme-dark .insight-box {
            background: #111722 !important;
            border-color: #232c3d !important;
            color: #cbd5e1 !important;
        }

        html.theme-dark .insight-box strong {
            color: #f1f5f9 !important;
        }

        html.theme-dark .dash-panel {
            background: #111722 !important;
            border-color: #232c3d !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3) !important;
        }

        html.theme-dark .dash-panel h3 {
            color: #f1f5f9 !important;
        }

        html.theme-dark .dash-table th {
            background: #161d2b !important;
            border-bottom-color: #2e3a4f !important;
            color: #dbe3f0 !important;
        }

        html.theme-dark .dash-table td {
            color: #cbd5e1 !important;
            border-bottom-color: #232c3d !important;
        }

        html.theme-dark .dash-table tr.clickable-row:hover td {
            background: #161d2b !important;
            color: #f1f5f9 !important;
        }

        html.theme-dark .dash-pill {
            background: rgba(56, 189, 248, 0.15) !important;
            border-color: rgba(56, 189, 248, 0.3) !important;
            color: #7dd3fc !important;
        }

        html.theme-dark .trend-chip {
            background: #161d2b !important;
            border-color: #232c3d !important;
        }

        html.theme-dark .trend-chip strong {
            color: #f1f5f9 !important;
        }

        html.theme-dark .letter-rank-item {
            background: #161d2b !important;
            border-color: #232c3d !important;
        }

        html.theme-dark .letter-rank-head {
            color: #f1f5f9 !important;
        }

        html.theme-dark .letter-rank-track {
            background: #1e293b !important;
        }

        html.theme-dark .modal-content {
            background: #111722 !important;
            color: #dbe3f0 !important;
            border: 1px solid #232c3d !important;
        }

        html.theme-dark .modal-body table td {
            color: #cbd5e1 !important;
            border-bottom-color: #1e293b !important;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1100px) {
            .dash-filter {
                grid-template-columns: repeat(3, 1fr);
            }

            .dash-card-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .dash-section-grid,
            .insight-strip {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .admin-dashboard {
                padding: 10px 8px 40px !important;
            }

            .dash-filter,
            .dash-card-grid,
            .trend-summary {
                grid-template-columns: 1fr;
            }

            .dash-chart {
                height: 260px;
            }
        }
    </style>

    <div id="page-content-wrapper" class="admin-dash-wrap">
        <div class="page-content inset">
            <div class="admin-dashboard">
                <div class="dash-top-bar">
                    <h2 class="dash-section-title">
                        <i class="fa fa-line-chart"></i> Top Mgmt Dashboard
                        <span class="dash-module-badge"><?php echo ucfirst($dashboardType); ?></span>
                    </h2>
                    <div class="live-pulse"><span></span> Real time Values</div>
                </div>

                <div class="dash-module-nav">
                    <a href="adminDashboard.php?dashboard_type=business" class="dash-module-tab <?php echo $dashboardType === 'business' ? 'active' : ''; ?>">
                        <i class="fa fa-line-chart"></i> Business Insights
                    </a>
                    <a href="adminDashboard.php?dashboard_type=invoice" class="dash-module-tab <?php echo $dashboardType === 'invoice' ? 'active' : ''; ?>">
                        <i class="fa fa-file-text-o"></i> Invoices
                    </a>
                    <a href="adminDashboard.php?dashboard_type=letters" class="dash-module-tab <?php echo $dashboardType === 'letters' ? 'active' : ''; ?>">
                        <i class="fa fa-envelope-o"></i> Due Letters
                    </a>
                    <a href="adminDashboard.php?dashboard_type=payments" class="dash-module-tab <?php echo $dashboardType === 'payments' ? 'active' : ''; ?>">
                        <i class="fa fa-money"></i> Payments
                    </a>
                    <a href="adminDashboard.php?dashboard_type=clients" class="dash-module-tab <?php echo $dashboardType === 'clients' ? 'active' : ''; ?>">
                        <i class="fa fa-building-o"></i> Clients
                    </a>
                </div>

                <form class="dash-filter" method="get" action="adminDashboard.php">
                    <div class="dash-filter-item">
                        <label>Dashboard</label>
                        <select name="dashboard_type" class="form-control">
                            <option value="business" <?php echo $dashboardType === 'business' ? 'selected' : ''; ?>>Business Insights</option>
                            <option value="invoice" <?php echo $dashboardType === 'invoice' ? 'selected' : ''; ?>>Invoice</option>
                            <option value="letters" <?php echo $dashboardType === 'letters' ? 'selected' : ''; ?>>Due Letters</option>
                            <option value="payments" <?php echo $dashboardType === 'payments' ? 'selected' : ''; ?>>Payments</option>
                            <option value="clients" <?php echo $dashboardType === 'clients' ? 'selected' : ''; ?>>Clients</option>
                        </select>
                    </div>
                    <div class="dash-filter-item">
                        <label>Invoice Type</label>
                        <select name="invoice_type" class="form-control">
                            <option value="all" <?php echo $invoiceType === 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="proforma" <?php echo $invoiceType === 'proforma' ? 'selected' : ''; ?>>Proforma</option>
                            <option value="final" <?php echo $invoiceType === 'final' ? 'selected' : ''; ?>>Final</option>
                        </select>
                    </div>
                    <div class="dash-filter-item">
                        <label>Invoice Status</label>
                        <select name="invoice_status" class="form-control">
                            <option value="all" <?php echo $invoiceStatus === 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="Rcv" <?php echo $invoiceStatus === 'Rcv' ? 'selected' : ''; ?>>Received</option>
                            <option value="NotRcv" <?php echo $invoiceStatus === 'NotRcv' ? 'selected' : ''; ?>>Not Received</option>
                        </select>
                    </div>
                    <div class="dash-filter-item">
                        <label>Letter</label>
                        <select name="letter_action" class="form-control">
                            <option value="all" <?php echo $letterAction === 'all' ? 'selected' : ''; ?>>All Letters</option>
                            <?php foreach ($letterActionOptions as $actionRow) { ?>
                                <option value="<?php echo htmlspecialchars($actionRow['action']); ?>" <?php echo $letterAction === $actionRow['action'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($actionRow['action']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="dash-filter-item">
                        <label>Letter Status</label>
                        <select name="letter_status" class="form-control">
                            <option value="all" <?php echo $letterStatus === 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="pending" <?php echo $letterStatus === 'pending' ? 'selected' : ''; ?>>Due Pending</option>
                            <option value="sent" <?php echo $letterStatus === 'sent' ? 'selected' : ''; ?>>Sent</option>
                            <option value="upcoming" <?php echo $letterStatus === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                        </select>
                    </div>
                    <div class="dash-filter-item">
                        <label>Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>">
                    </div>
                    <div class="dash-filter-item">
                        <label>Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>">
                    </div>
                    <div class="dash-filter-item dash-filter-btn">
                        <label>&nbsp;</label>
                        <button type="submit" class="dash-btn"><i class="fa fa-filter"></i> Apply</button>
                    </div>
                </form>

                <div class="dash-card-grid">
                    <?php foreach ($dashboardCards as $cardIndex => $card) { ?>
                        <div class="dash-card <?php echo isset($card['id']) ? 'clickable-card' : ''; ?>" <?php echo isset($card['id']) ? 'id="' . $card['id'] . '"' : ''; ?> <?php echo isset($card['id']) ? 'style="cursor:pointer;"' : ''; ?>>
                            <i class="fa <?php echo $card['icon']; ?> icon"></i>
                            <span class="label"><?php echo $card['label']; ?></span>
                            <div class="value dash-count" data-raw="<?php echo htmlspecialchars($card['raw']); ?>"
                                data-format="<?php echo htmlspecialchars($card['format']); ?>"><?php echo $card['value']; ?>
                            </div>
                            <div class="meta"><?php echo $card['meta']; ?></div>
                            <div class="dash-spark">
                                <?php for ($spark = 0; $spark < 9; $spark++) { ?>
                                    <span style="height: <?php echo 5 + (($spark + $cardIndex * 2) % 7) * 2; ?>px"></span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div
                    class="dash-section-grid <?php echo ($dashboardType === 'business' || $dashboardType === 'invoice') ? '' : 'hidden-dashboard'; ?>">
                    <div class="dash-panel">
                        <h3><i class="fa fa-line-chart" style="color: #0d4d8b; font-size: 14px;"></i> Monthly Invoice Trend</h3>
                        <div class="trend-summary">
                            <div class="trend-chip">
                                <span>First Month</span>
                                <strong><?php echo htmlspecialchars(isset($invoiceTrendFirst['label']) ? $invoiceTrendFirst['label'] : '-'); ?>
                                    /
                                    <?php echo dashMoney(isset($invoiceTrendFirst['amount']) ? $invoiceTrendFirst['amount'] : 0); ?></strong>
                            </div>
                            <div class="trend-chip">
                                <span>Latest Month</span>
                                <strong><?php echo htmlspecialchars(isset($invoiceTrendLast['label']) ? $invoiceTrendLast['label'] : '-'); ?>
                                    /
                                    <?php echo dashMoney(isset($invoiceTrendLast['amount']) ? $invoiceTrendLast['amount'] : 0); ?></strong>
                            </div>
                            <div class="trend-chip <?php echo $invoiceTrendDirection; ?>">
                                <span>Movement</span>
                                <strong><?php echo $invoiceTrendDirection === 'up' ? 'Up' : ($invoiceTrendDirection === 'down' ? 'Down' : 'Flat'); ?>
                                    <?php echo dashMoney(abs($invoiceTrendDiff)); ?></strong>
                            </div>
                        </div>
                        <div id="invoiceTrendChart" class="dash-chart" style="min-height: 290px;"></div>
                    </div>
                    <div class="dash-panel">
                        <h3><i class="fa fa-pie-chart" style="color: #f59e0b; font-size: 14px;"></i> Invoice Type Split</h3>
                        <div id="invoiceTypeChart" class="dash-chart" style="min-height: 290px;"></div>
                    </div>
                </div>



                <div
                    class="dash-section-grid <?php echo ($dashboardType === 'business' || $dashboardType === 'payments' || $dashboardType === 'clients') ? '' : 'hidden-dashboard'; ?>">
                    <div class="dash-panel">
                        <h3><i class="fa fa-bar-chart" style="color: #10b981; font-size: 14px;"></i> Pending Payment by Service</h3>
                        <div id="paymentChart" class="dash-chart" style="min-height: 290px;"></div>
                    </div>
                    <div class="dash-panel">
                        <h3><i class="fa fa-users" style="color: #8b5cf6; font-size: 14px;"></i> Client Status Distribution</h3>
                        <div id="clientDotChart" class="dash-chart" style="min-height: 290px;"></div>
                    </div>
                </div>

                <div class="<?php echo ($dashboardType === 'business' || $dashboardType === 'invoice') ? '' : 'hidden-dashboard'; ?>" style="margin-top: 16px;">
                    <div class="dash-panel">
                        <h3>Recent Invoices</h3>
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentInvoices as $row) { ?>
                                    <tr class="clickable-row open-client"
                                        data-customer="<?php echo (int) $row['customer_id']; ?>">
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td><span
                                                class="dash-pill"><?php echo htmlspecialchars($row['invoice_type']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['invoice_status']); ?></td>
                                        <td><?php echo dashDate($row['invoice_date']); ?></td>
                                        <td><?php echo dashMoney($row['final_amount']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div
                    class="dash-section-grid <?php echo ($dashboardType === 'business' || $dashboardType === 'payments') ? '' : 'hidden-dashboard'; ?>">
                    <div class="dash-panel">
                        <h3>Payment Information</h3>
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Service</th>
                                    <th>Decided</th>
                                    <th>Received</th>
                                    <th>Pending</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPayments as $row) {
                                    $recentDisplayName = !empty($row['customer_name']) ? $row['customer_name'] : 'Client';
                                ?>
                                    <tr class="clickable-row open-client"
                                        data-customer="<?php echo (int) $row['resolved_customer_id']; ?>">
                                        <td><?php echo htmlspecialchars($recentDisplayName); ?></td>
                                        <td><span
                                                class="dash-pill"><?php echo htmlspecialchars($row['service_type']); ?></span>
                                        </td>
                                        <td><?php echo dashMoney($row['amount_decided']); ?></td>
                                        <td><?php echo dashMoney($row['payment_received']); ?></td>
                                        <td><?php echo dashMoney($row['balance']); ?></td>
                                        <td><?php echo dashDate($row['payment_date']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="dash-panel">
                        <h3>Payment Focus</h3>
                        <table class="dash-table">
                            <tbody>
                                <tr>
                                    <td>Collection Ratio</td>
                                    <td><strong><?php echo dashPercent(isset($paymentTotals['received_amount']) ? $paymentTotals['received_amount'] : 0, isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0); ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Pending Ratio</td>
                                    <td><strong><?php echo dashPercent(isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0, isset($paymentTotals['decided_amount']) ? $paymentTotals['decided_amount'] : 0); ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Pending Collection</td>
                                    <td><strong><?php echo dashMoney(isset($paymentTotals['pending_amount']) ? $paymentTotals['pending_amount'] : 0); ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Payment Rows</td>
                                    <td><strong><?php echo dashInt(isset($paymentTotals['rows_count']) ? $paymentTotals['rows_count'] : 0); ?></strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div
                    class="dash-section-grid <?php echo ($dashboardType === 'business' || $dashboardType === 'clients') ? '' : 'hidden-dashboard'; ?>">
                    <div class="dash-panel">
                        <h3>Client Details</h3>
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Unique ID</th>
                                    <th>Status</th>
                                    <th>Next Service</th>
                                    <th>Services</th>
                                    <th>Pending</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentClients as $row) { ?>
                                    <tr class="clickable-row open-client" data-customer="<?php echo (int) $row['id']; ?>">
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?><br><small><?php echo htmlspecialchars($row['contact_person']); ?>
                                                / <?php echo htmlspecialchars($row['contact']); ?></small></td>
                                        <td><?php echo htmlspecialchars($row['unique_id']); ?></td>
                                        <td><span
                                                class="dash-pill"><?php echo htmlspecialchars(isset($row['derived_status']) ? $row['derived_status'] : $row['audit_status']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['next_service']); ?></td>
                                        <td><?php echo dashInt($row['service_count']); ?></td>
                                        <td><?php echo dashMoney($row['pending_amount']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="dash-panel">
                        <h3>Online Applications</h3>
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Organization</th>
                                    <th>Contact</th>
                                    <th>Standard</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOnlineApplications as $row) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['org_name']); ?><br><small><?php echo dashDate($row['created_at']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['contact_person']); ?><br><small><?php echo htmlspecialchars($row['phone']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['standard_applied']); ?></td>
                                        <td><span
                                                class="dash-pill"><?php echo ((int) $row['read_flag'] === 1) ? 'read' : 'unread'; ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- All Clients Modal -->
    <div class="modal fade" id="clientsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <div class="modal-header" style="background: linear-gradient(135deg, #155e95, #1f7a8c); color:#fff; padding: 20px 25px;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity: 0.8; font-size: 28px;">&times;</button>
                    <h4 class="modal-title" style="font-weight: 900; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-building"></i> Registered Client Status Breakdown
                    </h4>
                </div>
                <div class="modal-body" style="padding: 30px; background: #f8fafc;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">
                        <?php
                        $statusColors = [
                            'Active' => '#1f9d68',
                            'Withdrawn' => '#d14f62',
                            'Expired' => '#ef7b45',
                            'Inprocess' => '#155e95',
                            'In-process' => '#155e95',
                            'Awaiting Review' => '#7c3aed',
                            'Contract-Review' => '#2563eb',
                            'Contract-Inprocess' => '#7c3aed',
                            'Suspended' => '#b45309',
                            'Application canceled' => '#64748b',
                            'No Status' => '#94a3b8'
                        ];
                        $maxStatusTotal = 0;
                        foreach ($clientStatusRows as $sRow) { $maxStatusTotal = max($maxStatusTotal, $sRow['total']); }

                        foreach ($clientStatusRows as $sRow) {
                            $label = $sRow['label'];
                            $color = isset($statusColors[$label]) ? $statusColors[$label] : '#155e95';
                            $percent = $maxStatusTotal > 0 ? round(($sRow['total'] / $maxStatusTotal) * 100) : 0;
                            $statusClients = isset($clientsByStatus[$label]) ? $clientsByStatus[$label] : [];
                        ?>
                            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; flex-direction: column;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                    <span style="color: #64748b; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo htmlspecialchars($label); ?></span>
                                    <div style="width: 10px; height: 10px; border-radius: 50%; background: <?php echo $color; ?>; box-shadow: 0 0 0 4px <?php echo $color; ?>20;"></div>
                                </div>
                                <div style="font-size: 24px; font-weight: 900; color: #0f172a; margin-bottom: 8px;"><?php echo dashInt($sRow['total']); ?> <small style="font-size: 13px; color: #94a3b8; font-weight: 600;">Clients</small></div>
                                <div style="height: 5px; background: #f1f5f9; border-radius: 999px; overflow: hidden; margin-bottom: 15px;">
                                    <div style="height: 100%; width: <?php echo $percent; ?>%; background: <?php echo $color; ?>; border-radius: 999px;"></div>
                                </div>

                                <button class="btn btn-default btn-xs toggle-names" data-target="list-<?php echo md5($label); ?>" style="width: 100%; font-weight: 700; color: <?php echo $color; ?>; border-color: <?php echo $color; ?>40; background: <?php echo $color; ?>08; border-radius: 6px;">View Clients <i class="fa fa-chevron-down" style="font-size: 9px; margin-left: 4px;"></i></button>

                                <div id="list-<?php echo md5($label); ?>" style="display: none; margin-top: 12px; max-height: 180px; overflow-y: auto; border-top: 1px dashed #e2e8f0; padding-top: 10px;">
                                    <ul style="list-style: none; padding: 0; margin: 0;">
                                        <?php foreach ($statusClients as $sc) { ?>
                                            <li class="open-client" data-customer="<?php echo $sc['id']; ?>" style="padding: 6px 8px; font-size: 12px; color: #334155; border-bottom: 1px solid #f8fafc; cursor: pointer; border-radius: 4px; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                                <i class="fa fa-user-circle-o" style="color: #cbd5e1; margin-right: 6px;"></i> <?php echo htmlspecialchars($sc['customer_name']); ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="modal-footer" style="background: #fff; border-top: 1px solid #f1f5f9; padding: 15px 25px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 700; border-radius: 8px; padding: 8px 20px;">Close Window</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Due Letters Modal -->
    <div class="modal fade" id="lettersModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <div class="modal-header" style="background: linear-gradient(135deg, #0d4d8b, #083b6a); color:#fff; padding: 20px 25px;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity: 0.8; font-size: 28px;">&times;</button>
                    <h4 class="modal-title" style="font-weight: 900; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-envelope-o"></i> Current Due Letters Breakdown
                    </h4>
                </div>
                <div class="modal-body" style="padding: 0; background: #fff;">
                    <div class="table-responsive">
                        <table class="table table-hover dash-table" style="margin-bottom: 0;">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th style="padding: 15px 25px;">Client Name</th>
                                    <th style="padding: 15px 25px;">Action</th>
                                    <th style="padding: 15px 25px; text-align: center;">Due Date</th>
                                    <th style="padding: 15px 25px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendingLettersDetail)) { ?>
                                    <tr>
                                        <td colspan="4" style="padding: 40px; text-align: center; color: #64748b; font-weight: 600;">
                                            <i class="fa fa-check-circle" style="font-size: 40px; color: #10b981; display: block; margin-bottom: 10px;"></i>
                                            No pending due letters!
                                        </td>
                                    </tr>
                                <?php } else {
                                    foreach ($pendingLettersDetail as $pLetter) { ?>
                                        <tr class="clickable-row">
                                            <td style="padding: 15px 25px; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($pLetter['customer_name']); ?></td>
                                            <td style="padding: 15px 25px;"><span class="dash-pill"><?php echo htmlspecialchars($pLetter['action']); ?></span></td>
                                            <td style="padding: 15px 25px; text-align: center; font-weight: 600; color: #ef4444;"><?php echo dashDate($pLetter['due_date']); ?></td>
                                            <td style="padding: 15px 25px; text-align: center;">
                                                <button class="btn btn-default btn-xs open-client" data-customer="<?php echo (int) $pLetter['customer_id']; ?>" style="border-radius: 6px; font-weight: 700; padding: 4px 12px; border-color: #e2e8f0;">Details</button>
                                            </td>
                                        </tr>
                                <?php } } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="background: #fff; border-top: 1px solid #f1f5f9; padding: 15px 25px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 700; border-radius: 8px; padding: 8px 20px;">Close</button>
                    <a href="intimationLetter.php" class="btn btn-primary" style="background: #0d4d8b; border: none; font-weight: 700; border-radius: 8px; padding: 8px 20px;">Open Letters</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Pending Modal -->
    <div class="modal fade" id="paymentsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <div class="modal-header" style="background: linear-gradient(135deg, #0d4d8b, #083b6a); color:#fff; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
                    <h4 class="modal-title" style="font-weight: 800; font-size: 16px; letter-spacing: -0.2px; display: flex; align-items: center; gap: 10px; margin: 0;">
                        <i class="fa fa-credit-card" style="color: #60a5fa;"></i> Outstanding Payment Breakdown
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity: 0.85; font-size: 24px; margin: 0; padding: 0; line-height: 1;">&times;</button>
                </div>
                <div class="modal-body" style="padding: 0; background: #fff;">
                    <div class="table-responsive">
                        <table class="table table-hover dash-table" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th style="padding: 12px 20px;">Client Name</th>
                                    <th style="padding: 12px 20px; text-align: right;">Pending Amount</th>
                                    <th style="padding: 12px 20px; text-align: center; width: 110px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalPending = 0;
                                if (empty($pendingPaymentsDetail)) { ?>
                                    <tr>
                                        <td colspan="3" style="padding: 40px; text-align: center; color: #64748b; font-weight: 600;">
                                            <i class="fa fa-check-circle" style="font-size: 38px; color: #10b981; display: block; margin-bottom: 10px;"></i>
                                            All payments are up to date!
                                        </td>
                                    </tr>
                                <?php } else {
                                    foreach ($pendingPaymentsDetail as $pay) {
                                        $clientPending = isset($pay['pending_amount']) ? (float) $pay['pending_amount'] : 0;
                                        $totalPending += $clientPending;
                                        $resolvedCustomerId = isset($pay['resolved_customer_id']) ? (int) $pay['resolved_customer_id'] : 0;
                                        $fallbackRef = isset($pay['fallback_ref']) ? trim((string)$pay['fallback_ref']) : '';
                                        $paymentGroupId = isset($pay['payment_group_id']) ? (int) $pay['payment_group_id'] : 0;
                                        $displayName = !empty($pay['customer_name'])
                                            ? $pay['customer_name']
                                            : (!empty($fallbackRef)
                                                ? ('Client Ref ' . $fallbackRef)
                                                : ($paymentGroupId > 0 ? ('Client Payment #' . $paymentGroupId) : 'Client'));
                                ?>
                                    <tr class="clickable-row">
                                        <td style="padding: 13px 20px; font-weight: 700; color: #1e293b;">
                                            <i class="fa fa-building-o" style="color: #0d4d8b; margin-right: 8px; font-size: 13px;"></i>
                                            <?php echo htmlspecialchars($displayName); ?>
                                        </td>
                                        <td style="padding: 13px 20px; text-align: right; font-weight: 800; color: #dc2626; font-size: 13.5px; font-family: Inter, Segoe UI, sans-serif;">
                                            <?php echo dashMoney($clientPending); ?>
                                        </td>
                                        <td style="padding: 13px 20px; text-align: center;">
                                            <?php if ($resolvedCustomerId > 0) { ?>
                                                <button class="btn btn-default btn-xs open-client" data-customer="<?php echo $resolvedCustomerId; ?>" style="border-radius: 6px; font-weight: 700; padding: 4px 14px; border: 1px solid #cbd5e1; background: #f8fafc; color: #0d4d8b; transition: all 0.2s;">
                                                    View <i class="fa fa-arrow-right" style="font-size: 10px; margin-left: 3px;"></i>
                                                </button>
                                            <?php } else { ?>
                                                <button class="btn btn-default btn-xs" disabled style="border-radius: 6px; font-weight: 700; padding: 4px 12px; border-color: #e2e8f0; opacity: 0.5; cursor: not-allowed;">N/A</button>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } } ?>
                            </tbody>
                            <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0;">
                                <tr>
                                    <td style="padding: 14px 20px; font-weight: 800; color: #64748b; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Total Pending Collection</td>
                                    <td style="padding: 14px 20px; text-align: right; font-weight: 900; font-size: 15px; color: #dc2626; font-family: Inter, Segoe UI, sans-serif;"><?php echo dashMoney($totalPending); ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="background: #fff; border-top: 1px solid #f1f5f9; padding: 14px 24px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 700; border-radius: 8px; padding: 7px 18px; border: 1px solid #cbd5e1;">Close</button>
                    <a href="paymentReceive.php" class="btn btn-primary" style="background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%); border: none; font-weight: 700; border-radius: 8px; padding: 7px 18px; box-shadow: 0 2px 6px rgba(13, 77, 139, 0.25);">Record Payment</a>
                </div>
            </div>
        </div>
    </div>

    <form id="openClientForm" action="customerAccount.php" method="post" style="display:none;">
        <input type="hidden" name="customerid" id="openClientId" value="">
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    var dashData = <?php echo json_encode($chartData); ?>;
    var chartInstances = {};

    function compactNumber(value) {
        value = Number(value) || 0;
        if (Math.abs(value) >= 10000000) {
            return (value / 10000000).toFixed(1).replace('.0', '') + 'Cr';
        }
        if (Math.abs(value) >= 100000) {
            return (value / 100000).toFixed(1).replace('.0', '') + 'L';
        }
        if (Math.abs(value) >= 1000) {
            return (value / 1000).toFixed(1).replace('.0', '') + 'K';
        }
        return Math.round(value);
    }

    function initApexCharts() {
        if (typeof ApexCharts === 'undefined') {
            setTimeout(initApexCharts, 120);
            return;
        }

        var isDark = $('html').hasClass('theme-dark');
        var fontFam = 'Inter, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif';
        var gridColor = isDark ? '#232c3d' : '#f1f5f9';
        var labelColor = isDark ? '#94a3b8' : '#64748b';
        var titleColor = isDark ? '#dbe3f0' : '#0f172a';

        // 1. Pending Payment by Service
        var payEl = document.querySelector('#paymentChart');
        if (payEl) {
            if (chartInstances['paymentChart']) { chartInstances['paymentChart'].destroy(); }
            var payRows = dashData.paymentServices || [];
            var payCategories = payRows.map(function(r) { return r.label || 'Other'; });
            var payAmounts = payRows.map(function(r) { return parseFloat(r.amount || 0); });

            var payOptions = {
                series: [{
                    name: 'Pending Amount',
                    data: payAmounts
                }],
                chart: {
                    type: 'bar',
                    height: 290,
                    toolbar: { show: false },
                    fontFamily: fontFam,
                    background: 'transparent'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '46%',
                        distributed: true,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                colors: ['#0d4d8b', '#10b981', '#059669', '#3b82f6', '#6366f1', '#8b5cf6', '#d97706', '#ea580c'],
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return '₹' + compactNumber(val);
                    },
                    offsetY: -22,
                    style: {
                        fontSize: '11px',
                        fontWeight: 700,
                        colors: [titleColor]
                    }
                },
                legend: { show: false },
                xaxis: {
                    categories: payCategories,
                    labels: {
                        rotate: -25,
                        rotateAlways: true,
                        style: {
                            fontSize: '11px',
                            fontWeight: 600,
                            colors: labelColor
                        },
                        trim: true,
                        maxHeight: 65
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return '₹' + compactNumber(val);
                        },
                        style: {
                            colors: labelColor,
                            fontSize: '11px'
                        }
                    }
                },
                grid: {
                    borderColor: gridColor,
                    strokeDashArray: 4
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function(val) {
                            return 'Rs. ' + Number(val).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                }
            };
            chartInstances['paymentChart'] = new ApexCharts(payEl, payOptions);
            chartInstances['paymentChart'].render();
        }

        // 2. Client Status Distribution
        var clientEl = document.querySelector('#clientDotChart');
        if (clientEl) {
            if (chartInstances['clientDotChart']) { chartInstances['clientDotChart'].destroy(); }
            var clientRows = dashData.clientStatuses || [];
            var statusCats = clientRows.map(function(r) { return r.label || ''; });
            var statusCounts = clientRows.map(function(r) { return parseInt(r.total || 0); });
            var statusColorsMap = {
                'Active': '#10b981',
                'Withdrawn': '#ef4444',
                'Expired': '#f59e0b',
                'Application canceled': '#64748b',
                'Application cancel': '#64748b',
                'Inprocess': '#3b82f6',
                'In-process': '#3b82f6',
                'Suspended': '#d97706',
                'Awaiting Review': '#8b5cf6',
                'Contract-Review': '#6366f1'
            };
            var customColors = statusCats.map(function(c) { return statusColorsMap[c] || '#0d4d8b'; });

            var clientOptions = {
                series: [{
                    name: 'Total Clients',
                    data: statusCounts
                }],
                chart: {
                    type: 'bar',
                    height: 290,
                    toolbar: { show: false },
                    fontFamily: fontFam,
                    background: 'transparent'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        horizontal: true,
                        barHeight: '56%',
                        distributed: true,
                        dataLabels: {
                            position: 'right'
                        }
                    }
                },
                colors: customColors,
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val + ' Clients';
                    },
                    offsetX: 10,
                    style: {
                        fontSize: '11px',
                        fontWeight: 700,
                        colors: [titleColor]
                    }
                },
                legend: { show: false },
                xaxis: {
                    labels: {
                        style: {
                            colors: labelColor,
                            fontSize: '11px'
                        }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    categories: statusCats,
                    labels: {
                        style: {
                            colors: isDark ? '#dbe3f0' : '#334155',
                            fontSize: '12px',
                            fontWeight: 600
                        }
                    }
                },
                grid: {
                    borderColor: gridColor,
                    strokeDashArray: 4
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function(val) {
                            return val + ' Registered Clients';
                        }
                    }
                }
            };
            chartInstances['clientDotChart'] = new ApexCharts(clientEl, clientOptions);
            chartInstances['clientDotChart'].render();
        }

        // 3. Invoice Trend (Revenue & Count)
        var trendEl = document.querySelector('#invoiceTrendChart');
        if (trendEl) {
            if (chartInstances['invoiceTrendChart']) { chartInstances['invoiceTrendChart'].destroy(); }
            var trendRows = dashData.invoiceTrend || [];
            var trendMonths = trendRows.map(function(r) { return r.label || ''; });
            var trendAmounts = trendRows.map(function(r) { return parseFloat(r.amount || 0); });
            var trendCounts = trendRows.map(function(r) { return parseInt(r.total || 0); });

            var trendOptions = {
                series: [
                    {
                        name: 'Invoice Revenue',
                        type: 'area',
                        data: trendAmounts
                    },
                    {
                        name: 'Invoice Count',
                        type: 'line',
                        data: trendCounts
                    }
                ],
                chart: {
                    height: 290,
                    type: 'line',
                    toolbar: { show: false },
                    fontFamily: fontFam,
                    background: 'transparent'
                },
                stroke: {
                    curve: 'smooth',
                    width: [3, 2.5]
                },
                colors: ['#0d4d8b', '#f59e0b'],
                fill: {
                    type: ['gradient', 'solid'],
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.5,
                        gradientToColors: ['#3b82f6'],
                        inverseColors: false,
                        opacityFrom: 0.5,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                markers: {
                    size: [0, 5],
                    strokeColors: '#ffffff',
                    strokeWidth: 2,
                    hover: { size: 7 }
                },
                xaxis: {
                    categories: trendMonths,
                    labels: {
                        style: {
                            colors: labelColor,
                            fontSize: '11px',
                            fontWeight: 600
                        }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: [
                    {
                        title: {
                            text: 'Revenue (₹)',
                            style: { color: labelColor, fontSize: '11px', fontWeight: 600 }
                        },
                        labels: {
                            formatter: function(val) {
                                return '₹' + compactNumber(val);
                            },
                            style: { colors: labelColor, fontSize: '11px' }
                        }
                    },
                    {
                        opposite: true,
                        title: {
                            text: 'Invoices',
                            style: { color: '#f59e0b', fontSize: '11px', fontWeight: 600 }
                        },
                        labels: {
                            formatter: function(val) {
                                return Math.round(val);
                            },
                            style: { colors: '#f59e0b', fontSize: '11px' }
                        }
                    }
                ],
                grid: {
                    borderColor: gridColor,
                    strokeDashArray: 4
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    labels: { colors: isDark ? '#dbe3f0' : '#475569' },
                    markers: { radius: 12 }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function(y, opt) {
                            if (opt.seriesIndex === 0) {
                                return 'Rs. ' + Number(y).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                            return y + ' Invoices';
                        }
                    }
                }
            };
            chartInstances['invoiceTrendChart'] = new ApexCharts(trendEl, trendOptions);
            chartInstances['invoiceTrendChart'].render();
        }

        // 4. Invoice Type Split (Donut)
        var donutEl = document.querySelector('#invoiceTypeChart');
        if (donutEl) {
            if (chartInstances['invoiceTypeChart']) { chartInstances['invoiceTypeChart'].destroy(); }
            var typeRows = dashData.invoiceTypes || [];
            var typeLabels = typeRows.map(function(r) { return r.label || ''; });
            var typeCounts = typeRows.map(function(r) { return parseInt(r.total || 0); });

            var donutOptions = {
                series: typeCounts,
                labels: typeLabels,
                chart: {
                    type: 'donut',
                    height: 290,
                    fontFamily: fontFam,
                    background: 'transparent'
                },
                colors: ['#0d4d8b', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                stroke: {
                    show: true,
                    colors: [isDark ? '#111722' : '#ffffff'],
                    width: 3
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '13px',
                                    fontWeight: 600,
                                    color: labelColor
                                },
                                value: {
                                    show: true,
                                    fontSize: '22px',
                                    fontWeight: 800,
                                    color: titleColor,
                                    formatter: function(val) { return val; }
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '12px',
                                    fontWeight: 600,
                                    color: labelColor,
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: { colors: isDark ? '#dbe3f0' : '#475569' },
                    markers: { radius: 12 }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function(val) {
                            return val + ' Invoices';
                        }
                    }
                }
            };
            chartInstances['invoiceTypeChart'] = new ApexCharts(donutEl, donutOptions);
            chartInstances['invoiceTypeChart'].render();
        }
    }

    function formatCounter(value, format) {
        value = Number(value) || 0;
        if (format === 'money') {
            return 'Rs.\u00A0' + value.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        return Math.round(value).toLocaleString('en-IN');
    }

    function animateCounters() {
        $('.dash-count').each(function () {
            var el = $(this);
            var target = parseFloat(el.attr('data-raw') || '0');
            var format = el.attr('data-format');
            var start = null;
            function tick(timestamp) {
                if (!start) start = timestamp;
                var progress = Math.min((timestamp - start) / 900, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.text(formatCounter(target * eased, format));
                if (progress < 1) {
                    window.requestAnimationFrame(tick);
                } else {
                    el.text(formatCounter(target, format));
                }
            }
            window.requestAnimationFrame(tick);
        });
    }

    $(document).ready(function () {
        initApexCharts();
        animateCounters();

        $('#themeToggle').on('click', function() {
            setTimeout(initApexCharts, 100);
        });

        $('#totalClientsCard').on('click', function () {
            $('#clientsModal').modal('show');
        });
        $('#dueLettersCard').on('click', function () {
            $('#lettersModal').modal('show');
        });
        $('#pendingPaymentsCard').on('click', function () {
            $('#paymentsModal').modal('show');
        });

        $(document).on('click', '.toggle-names', function (e) {
            e.stopPropagation();
            var targetId = $(this).attr('data-target');
            var $list = $('#' + targetId);
            var $icon = $(this).find('i');

            $list.slideToggle(200);
            if ($icon.hasClass('fa-chevron-down')) {
                $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $(this).text('Hide Clients ').append($icon);
            } else {
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $(this).text('View Clients ').append($icon);
            }
        });

        $('[name="dashboard_type"]').on('change', function () { $(this).closest('form').submit(); });
        $('.open-client').on('click', function () {
            var customerId = $(this).attr('data-customer');
            if (!customerId || customerId === '0') {
                return;
            }
            $('#openClientId').val(customerId);
            $('#openClientForm').submit();
        });
        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
            setTimeout(initApexCharts, 250);
        });
    });
</script>

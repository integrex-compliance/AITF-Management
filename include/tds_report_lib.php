<?php
require_once __DIR__ . '/invoice_report_lib.php';
require_once __DIR__ . '/payment_service_names.php';

function tdsReportDateForSql($dateValue) {
    if ($dateValue === '') {
        return '';
    }
    $parts = explode('.', $dateValue);
    if (count($parts) === 3) {
        return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
    return date('Y-m-d', strtotime($dateValue));
}

function tdsReportDisplayDate($dateValue) {
    if (empty($dateValue)) {
        return '-';
    }
    return date('d.m.Y', strtotime($dateValue));
}

function tdsReportTitle($status) {
    if ($status === 'not_received') {
        return 'TDS NOT RECEIVED REPORT';
    }
    if ($status === 'received') {
        return 'TDS RECEIVED REPORT';
    }
    return 'TDS ALL REPORT';
}

function tdsReportFilterText($status, $startDateStr, $endDateStr, $clientSearch) {
    $filters = 'STATUS: ' . strtoupper($status);
    if ($clientSearch !== '') {
        $filters .= ' | CLIENT: ' . strtoupper($clientSearch);
    }
    if ($startDateStr !== '') {
        $filters .= ' | FROM: ' . $startDateStr;
    }
    if ($endDateStr !== '') {
        $filters .= ' | TO: ' . $endDateStr;
    }
    return $filters;
}

function fetchTdsReportData($db, $status, $startDateStr, $endDateStr, $clientSearch, $sortOrder) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $tdsRole = trim($_SESSION['role'] ?? '');
    $isMarketingHead = in_array($tdsRole, array('Marketing Head', 'Marketing Executive'), true)
        || strtolower(str_replace(' ', '', $tdsRole)) === 'marketinghead'
        || (isset($_SESSION['username']) && strtolower(trim($_SESSION['username'])) === 'marketingh');
    $isOfficeStaff = in_array($tdsRole, array('Office Staff-A', 'Office Staff-B'), true)
        || in_array(strtolower(str_replace(array(' ', '-'), '', $tdsRole)), array('officestaffa', 'officestaffb'), true);
    $isRestrictedRole = $isMarketingHead || $isOfficeStaff;

    $startDate = tdsReportDateForSql($startDateStr);
    $endDate = tdsReportDateForSql($endDateStr);

    $sql = "SELECT ci.id, ci.customer_name, ci.unique_id, ps.service_type AS tds, ps.service_type, ps.payment_header, svc.service_type AS svc_service_type,
                   COALESCE(NULLIF(ps.tds_amount, 0), ps.amount_decided, ps.payment_received) as amount_decided,
                   COALESCE(NULLIF(ps.tds_amount, 0), ps.payment_received, ps.amount_decided) as payment_received,
                   ps.payment_date, ps.tds_received_date, ps.credit_date_booking, ps.tan_of_deductor, ps.comment
            FROM customer_info ci
            LEFT JOIN payment_system ps ON ci.id = ps.customer_id
            LEFT JOIN service svc ON ps.customer_id = svc.customer_id
            WHERE (svc.service_id = (SELECT MAX(tmp.service_id) FROM service tmp WHERE tmp.customer_id = ci.id) OR svc.service_id IS NULL)
            AND (ps.service_type LIKE '%tds%' OR (ps.tds_amount IS NOT NULL AND ps.tds_amount > 0))";

    $params = array();
    if ($status === 'received') {
        $sql .= " AND (ps.payment_received > 0 OR ps.tds_amount > 0)";
    } elseif ($status === 'not_received') {
        $sql .= " AND (ps.payment_received IS NULL OR ps.payment_received = 0 OR ps.payment_received = '' OR ps.payment_received = '0.00' OR ps.payment_received = '0') AND (ps.tds_amount IS NULL OR ps.tds_amount = 0)";
    }
    if ($clientSearch !== '') {
        $params[':clientSearch'] = '%' . $clientSearch . '%';
        if ($isRestrictedRole && $startDate === '' && $endDate === '') {
            $params[':exactClient'] = $clientSearch;
            $sql .= " AND (ci.customer_name = :exactClient OR ci.unique_id = :exactClient OR ps.payment_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01') OR ps.payment_date IS NULL OR ps.payment_date = '' OR ps.payment_date = '0000-00-00') AND (ci.customer_name LIKE :clientSearch OR ci.unique_id LIKE :clientSearch)";
        } else {
            $sql .= " AND (ci.customer_name LIKE :clientSearch OR ci.unique_id LIKE :clientSearch)";
        }
    } else {
        if ($isRestrictedRole && $startDate === '' && $endDate === '') {
            $sql .= " AND (ps.payment_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01') OR ps.payment_date IS NULL OR ps.payment_date = '' OR ps.payment_date = '0000-00-00')";
        }
    }
    if ($startDate !== '') {
        $sql .= " AND ps.payment_date >= :startDate";
        $params[':startDate'] = $startDate;
    }
    if ($endDate !== '') {
        $sql .= " AND ps.payment_date <= :endDate";
        $params[':endDate'] = $endDate;
    }

    if ($sortOrder === 'high') {
        $sql .= " ORDER BY CAST(COALESCE(NULLIF(ps.tds_amount, 0), ps.amount_decided, ps.payment_received) AS DECIMAL(10,2)) DESC";
    } elseif ($sortOrder === 'low') {
        $sql .= " ORDER BY CAST(COALESCE(NULLIF(ps.tds_amount, 0), ps.amount_decided, ps.payment_received) AS DECIMAL(10,2)) ASC";
    } else {
        $sql .= " ORDER BY ci.id DESC";
    }

    $tdsList = array();
    try {
        $statement = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $statement->bindValue($key, $val);
        }
        $statement->execute();
        $tdsList = $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $tdsList = array();
    }

    // Also fetch statement_payments TDS
    try {
        $spSql = "SELECT ci.id, ci.customer_name, ci.unique_id, 
                         sp.service_type as tds, sp.service_type, '' as payment_header, '' as svc_service_type, 1 as is_statement,
                         sp.tds_decided as amount_decided, sp.tds_received as payment_received,
                         sp.payment_date, sp.tds_received_date, sp.credit_date_booking, sp.tan_of_deductor, sp.comment
                  FROM customer_info ci
                  INNER JOIN statement_payments sp ON ci.id = sp.customer_id
                  WHERE sp.tds_decided > 0";
        $spParams = array();
        if ($status === 'received') {
            $spSql .= " AND sp.tds_received > 0";
        } elseif ($status === 'not_received') {
            $spSql .= " AND (sp.tds_received IS NULL OR sp.tds_received = 0)";
        }
        if ($clientSearch !== '') {
            $spSql .= " AND (ci.customer_name LIKE :spClient OR ci.unique_id LIKE :spClient)";
            $spParams[':spClient'] = '%' . $clientSearch . '%';
        }
        if ($startDate !== '') {
            $spSql .= " AND sp.payment_date >= :spStartDate";
            $spParams[':spStartDate'] = $startDate;
        }
        if ($endDate !== '') {
            $spSql .= " AND sp.payment_date <= :spEndDate";
            $spParams[':spEndDate'] = $endDate;
        }
        $spSql .= " ORDER BY sp.id DESC";

        $spStmt = $db->prepare($spSql);
        foreach ($spParams as $key => $val) {
            $spStmt->bindValue($key, $val);
        }
        $spStmt->execute();
        $spList = $spStmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($spList)) {
            $tdsList = array_merge($tdsList, $spList);
        }
    } catch (Throwable $e) {
        // Silently skip if statement_payments doesn't exist
    }

    return array('tdsList' => $tdsList);
}

function tdsReceivedAmount($row) {
    return cleanAmount($row['payment_received']);
}

function tdsDeductedAmount($row) {
    return cleanAmount($row['amount_decided']);
}

function buildTdsReportXlsxBytes($data, $status, $startDateStr, $endDateStr, $clientSearch, $sortOrder) {
    $tdsList = $data['tdsList'];
    $xlsx = new SimpleXlsx();
    $xlsx->freezePanesAbove(5);

    $widths = array(6, 16, 34, 24, 18, 18, 14, 34);
    foreach ($widths as $idx => $width) {
        $xlsx->setColWidth($idx + 1, $width);
    }

    $xlsx->addRow(array(array('v' => 'AITF MANAGEMENT CERTIFICATION LIMITED', 's' => 1, 't' => 's')));
    $xlsx->merge('A1:H1');
    $xlsx->setRowHeight(1, 28);

    $xlsx->addRow(array(array('v' => tdsReportTitle($status) . ' (' . tdsReportFilterText($status, $startDateStr, $endDateStr, $clientSearch) . ')', 's' => 2, 't' => 's')));
    $xlsx->merge('A2:H2');
    $xlsx->setRowHeight(2, 20);

    $accentRow = array();
    for ($i = 0; $i < 8; $i++) {
        $accentRow[] = array('v' => '', 's' => 38, 't' => 's');
    }
    $xlsx->addRow($accentRow);
    $xlsx->setRowHeight(3, 8);

    $headers = array('Sr.', 'Unique ID', 'Client Name', 'Invoice Header', 'TDS Deducted (Rs.)', 'Payment Received (Rs.)', 'Date', 'TDS Certificate Details');
    $headerRow = array();
    foreach ($headers as $header) {
        $headerRow[] = array('v' => $header, 's' => 4, 't' => 's');
    }
    $xlsx->addRow($headerRow);
    $xlsx->setRowHeight(4, 26);

    $totalDeducted = 0;
    $totalReceived = 0;
    $receivedCount = 0;
    $pendingCount = 0;
    $count = 1;

    if (empty($tdsList)) {
        $xlsx->addRow(array(array('v' => 'No records found', 's' => 7, 't' => 's')));
        $xlsx->merge('A5:H5');
    } else {
        foreach ($tdsList as $tds) {
            $deducted = tdsDeductedAmount($tds);
            $received = tdsReceivedAmount($tds);
            $totalDeducted += $deducted;
            $totalReceived += $received;
            $isReceived = $received > 0;
            if ($isReceived) {
                $receivedCount++;
                $sCenter = 26; $sClient = 27; $sText = 28; $sAmount = 29;
            } else {
                $pendingCount++;
                $sCenter = 32; $sClient = 33; $sText = 34; $sAmount = 35;
            }

            $xlsx->addRow(array(
                array('v' => $count++, 's' => $sCenter, 't' => 'n'),
                array('v' => $tds['unique_id'], 's' => $sText, 't' => 's'),
                array('v' => $tds['customer_name'], 's' => $sClient, 't' => 's'),
                array('v' => resolveTdsInvoiceHeader($tds), 's' => $sText, 't' => 's'),
                array('v' => round($deducted, 2), 's' => $sAmount, 't' => 'n'),
                array('v' => round($received, 2), 's' => $sAmount, 't' => 'n'),
                array('v' => tdsReportDisplayDate($tds['tds_received_date'] ?? ''), 's' => $sCenter, 't' => 's'),
                array('v' => $tds['comment'], 's' => $sText, 't' => 's'),
            ));
        }
    }

    $xlsx->addRow(array(array('v' => '', 's' => 0, 't' => 's')));
    $dataRowCount = empty($tdsList) ? 1 : count($tdsList);
    $curRow = 4 + $dataRowCount + 1;
    $pendingAmount = $totalDeducted - $totalReceived;

    $curRow++;
    $xlsx->addRow(array(array('v' => 'TDS REPORT SUMMARY', 's' => 16, 't' => 's')));
    $xlsx->merge('A' . $curRow . ':C' . $curRow);

    $summaryRows = array(
        array('TOTAL CASES', count($tdsList), 20),
        array('TOTAL TDS DEDUCTED', round($totalDeducted, 2), 19),
        array('TOTAL PAYMENT RECEIVED', round($totalReceived, 2), 19),
        array('TOTAL PENDING AMOUNT', round($pendingAmount, 2), 15),
        array('RECEIVED RECORDS', $receivedCount, 20),
        array('PENDING RECORDS', $pendingCount, 20),
    );

    foreach ($summaryRows as $line) {
        $curRow++;
        $xlsx->addRow(array(
            array('v' => $line[0], 's' => ($line[0] === 'TOTAL PENDING AMOUNT' ? 14 : 18), 't' => 's'),
            null,
            array('v' => $line[1], 's' => $line[2], 't' => 'n'),
        ));
        $xlsx->merge('A' . $curRow . ':B' . $curRow);
    }

    $curRow++;
    $xlsx->addRow(array(array('v' => 'PENDING AMOUNT IN WORDS: ' . strtoupper(convertToWord($pendingAmount)), 's' => 17, 't' => 's')));
    $xlsx->merge('A' . $curRow . ':H' . $curRow);

    return $xlsx->toString('TDS Report');
}

<?php
require_once __DIR__ . '/invoice_report_lib.php';

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
    $startDate = tdsReportDateForSql($startDateStr);
    $endDate = tdsReportDateForSql($endDateStr);

    $sql = "SELECT ci.id, ci.customer_name, ci.unique_id, ps.service_type AS tds, ps.amount_decided,
                   ps.payment_received, ps.payment_date, ps.comment
            FROM customer_info ci
            LEFT JOIN payment_system ps ON ci.id = ps.customer_id
            LEFT JOIN service svc ON ps.customer_id = svc.customer_id
            WHERE svc.service_id = (SELECT MAX(tmp.service_id) FROM service tmp WHERE tmp.customer_id = ci.id)
            AND ps.service_type LIKE '%tds%'";

    $params = array();
    if ($status === 'received') {
        $sql .= " AND ps.payment_received > 0 AND ps.payment_received IS NOT NULL AND ps.payment_received <> ''";
    } elseif ($status === 'not_received') {
        $sql .= " AND (ps.payment_received IS NULL OR ps.payment_received = 0 OR ps.payment_received = '' OR ps.payment_received = '0.00' OR ps.payment_received = '0')";
    }
    if ($clientSearch !== '') {
        $sql .= " AND ci.customer_name LIKE :clientSearch";
        $params[':clientSearch'] = '%' . $clientSearch . '%';
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
        $sql .= " ORDER BY CAST(ps.amount_decided AS DECIMAL(10,2)) DESC";
    } elseif ($sortOrder === 'low') {
        $sql .= " ORDER BY CAST(ps.amount_decided AS DECIMAL(10,2)) ASC";
    } else {
        $sql .= " ORDER BY ci.id DESC";
    }

    $statement = $db->prepare($sql);
    foreach ($params as $key => $val) {
        $statement->bindValue($key, $val);
    }
    $statement->execute();

    return array('tdsList' => $statement->fetchAll(PDO::FETCH_ASSOC));
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

    $headers = array('Sr.', 'Unique ID', 'Client Name', 'Service Type', 'TDS Deducted (Rs.)', 'Payment Received (Rs.)', 'Date', 'TDS Certificate Details');
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
                array('v' => $tds['tds'], 's' => $sText, 't' => 's'),
                array('v' => round($deducted, 2), 's' => $sAmount, 't' => 'n'),
                array('v' => round($received, 2), 's' => $sAmount, 't' => 'n'),
                array('v' => tdsReportDisplayDate($tds['payment_date']), 's' => $sCenter, 't' => 's'),
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

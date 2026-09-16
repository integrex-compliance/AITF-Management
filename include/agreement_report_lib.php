<?php
/**
 * Shared Agreement Report query and Excel builder.
 */
require_once __DIR__ . '/invoice_report_lib.php';

function agreementReportDateForSql($dateValue) {
    if ($dateValue === '') {
        return '';
    }
    return date("Y-m-d", strtotime(str_replace('.', '-', $dateValue)));
}

function agreementReportDisplayDate($dateValue) {
    if (empty($dateValue)) {
        return '';
    }
    return function_exists('simpleDateConversion') ? simpleDateConversion($dateValue) : date('d.m.Y', strtotime($dateValue));
}

function agreementReportAmount($row) {
    return cleanAmount($row['refundable_amount']) + cleanAmount($row['audit_stage_i']) + cleanAmount($row['audit_stage_iI']);
}

function agreementReportStatusBucket($status) {
    $status = strtoupper(trim((string) $status));
    if (strpos($status, 'SUSP') !== false || strpos($status, 'WITHDRAW') !== false || strpos($status, 'CANCEL') !== false || strpos($status, 'DELETE') !== false) {
        return 'red';
    }
    if (strpos($status, 'CERT') !== false || strpos($status, 'ACTIVE') !== false || strpos($status, 'VALID') !== false || strpos($status, 'APPRO') !== false) {
        return 'green';
    }
    return 'blue';
}

function fetchAgreementReportData($db, $fromDate, $toDate, $clientName) {
    $sql = "SELECT s.service_id, s.service_type, s.customer_id, ci.customer_name, ci.audit_status,
                   aa.audit_id AS quotation_id, aa.service_id AS casvcid, aa.uni_svc_id AS quotationno,
                   aa.application_date AS quotation_date, aa.refundable_amount, aa.audit_stage_i, aa.audit_stage_iI,
                   ca.aggrement_date, ca.aggrement_no, ca.mail_sent, ca.mail_sent_at, ca.mail_sent_to
            FROM customer_info ci
            JOIN service s ON ci.id = s.customer_id
            JOIN audit_application aa ON s.service_id = aa.uni_svc_id
            LEFT JOIN certification_aggrement ca ON aa.service_id = ca.service
            WHERE ci.is_deleted = 'NO'";

    $params = array();

    $invRole = trim($_SESSION['role'] ?? '');
    $isMarketingHead = in_array($invRole, array('Marketing Head', 'Marketing Executive'), true)
        || strtolower(str_replace(' ', '', $invRole)) === 'marketinghead'
        || (isset($_SESSION['username']) && strtolower(trim($_SESSION['username'])) === 'marketingh');
    $isOfficeStaff = in_array($invRole, array('Office Staff-A', 'Office Staff-B'), true)
        || in_array(strtolower(str_replace(array(' ', '-'), '', $invRole)), array('officestaffa', 'officestaffb'), true);
    $isRestrictedRole = $isMarketingHead || $isOfficeStaff;

    if ($fromDate !== '') {
        $sql .= " AND ca.aggrement_date >= :fromDate";
        $params[':fromDate'] = agreementReportDateForSql($fromDate);
    }
    if ($toDate !== '') {
        $sql .= " AND ca.aggrement_date <= :toDate";
        $params[':toDate'] = agreementReportDateForSql($toDate);
    }
    if ($clientName !== '') {
        $params[':clientName'] = '%' . $clientName . '%';
        if ($isRestrictedRole && $fromDate === '' && $toDate === '') {
            $params[':exactClient'] = $clientName;
            $sql .= " AND (ci.customer_name = :exactClient OR ca.aggrement_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')) AND ci.customer_name LIKE :clientName";
        } else {
            $sql .= " AND ci.customer_name LIKE :clientName";
        }
    } else {
        if ($isRestrictedRole && $fromDate === '' && $toDate === '') {
            $sql .= " AND ca.aggrement_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')";
        }
    }

    $sql .= " ORDER BY ca.aggrement_date DESC, ca.id DESC";

    try {
        $statement = $db->prepare($sql);
        foreach ($params as $key => &$val) {
            $statement->bindParam($key, $val);
        }
        unset($val);
        $statement->execute();
        return array('agreements' => $statement->fetchAll(PDO::FETCH_ASSOC));
    } catch (Throwable $e) {
        $fallbackSql = str_replace(", ca.mail_sent, ca.mail_sent_at, ca.mail_sent_to", "", $sql);
        $statement = $db->prepare($fallbackSql);
        foreach ($params as $key => &$val) {
            $statement->bindParam($key, $val);
        }
        unset($val);
        $statement->execute();
        return array('agreements' => $statement->fetchAll(PDO::FETCH_ASSOC));
    }
}

function agreementReportFilterText($fromDate, $toDate, $clientName) {
    $filters = '';
    if ($fromDate !== '' || $toDate !== '') {
        $filters = 'For Period: ' . ($fromDate !== '' ? $fromDate : 'Start') . ' to ' . ($toDate !== '' ? $toDate : 'End');
    }
    if ($clientName !== '') {
        $filters .= ($filters !== '' ? ', ' : '') . 'Client: ' . strtoupper($clientName);
    }
    return $filters === '' ? 'All Records' : $filters;
}

function buildAgreementReportXlsxBytes($data, $fromDate, $toDate, $clientName) {
    $agreements = $data['agreements'];
    $xlsx = new SimpleXlsx();
    $xlsx->freezePanesAbove(5);

    $widths = array(6, 34, 22, 22, 14, 16, 24, 22);
    foreach ($widths as $idx => $width) {
        $xlsx->setColWidth($idx + 1, $width);
    }

    $xlsx->addRow(array(array('v' => 'AITF MANAGEMENT CERTIFICATION LIMITED', 's' => 1, 't' => 's')));
    $xlsx->merge('A1:H1');
    $xlsx->setRowHeight(1, 28);

    $xlsx->addRow(array(array('v' => 'CERTIFICATION AGREEMENT REPORT (' . agreementReportFilterText($fromDate, $toDate, $clientName) . ')', 's' => 2, 't' => 's')));
    $xlsx->merge('A2:H2');
    $xlsx->setRowHeight(2, 20);

    $accentRow = array();
    for ($i = 0; $i < 8; $i++) {
        $accentRow[] = array('v' => '', 's' => 38, 't' => 's');
    }
    $xlsx->addRow($accentRow);
    $xlsx->setRowHeight(3, 8);

    $headers = array('Sr.', 'Client Name', 'Service Type', 'Agreement No.', 'Date', 'Amount (Rs.)', 'Status', 'Mail Status');
    $headerRow = array();
    foreach ($headers as $header) {
        $headerRow[] = array('v' => $header, 's' => 4, 't' => 's');
    }
    $xlsx->addRow($headerRow);
    $xlsx->setRowHeight(4, 26);

    $totalAmount = 0;
    $greenCount = 0;
    $redCount = 0;
    $otherCount = 0;
    $count = 1;

    if (empty($agreements)) {
        $xlsx->addRow(array(array('v' => 'No records found', 's' => 7, 't' => 's')));
        $xlsx->merge('A5:H5');
    } else {
        foreach ($agreements as $agreement) {
            $amount = agreementReportAmount($agreement);
            $totalAmount += $amount;
            $bucket = agreementReportStatusBucket($agreement['audit_status']);

            if ($bucket === 'green') {
                $greenCount++;
                $sCenter = 26; $sClient = 27; $sText = 28; $sAmount = 29; $sStatus = 31;
            } elseif ($bucket === 'red') {
                $redCount++;
                $sCenter = 32; $sClient = 33; $sText = 34; $sAmount = 35; $sStatus = 37;
            } else {
                $otherCount++;
                $sCenter = 7; $sClient = 6; $sText = 5; $sAmount = 8; $sStatus = 10;
            }

            $mailStatusText = 'Not Sent';
            if (!empty($agreement['mail_sent'])) {
                $mailStatusText = 'Sent';
                if (!empty($agreement['mail_sent_at'])) {
                    $mailStatusText .= ' (' . date('d.m.Y h:i A', strtotime($agreement['mail_sent_at'])) . ')';
                }
            }

            $xlsx->addRow(array(
                array('v' => $count++, 's' => $sCenter, 't' => 'n'),
                array('v' => $agreement['customer_name'], 's' => $sClient, 't' => 's'),
                array('v' => $agreement['service_type'], 's' => $sText, 't' => 's'),
                array('v' => $agreement['aggrement_no'], 's' => $sText, 't' => 's'),
                array('v' => agreementReportDisplayDate($agreement['aggrement_date']), 's' => $sCenter, 't' => 's'),
                array('v' => round($amount, 2), 's' => $sAmount, 't' => 'n'),
                array('v' => $agreement['audit_status'], 's' => $sStatus, 't' => 's'),
                array('v' => $mailStatusText, 's' => $sText, 't' => 's'),
            ));
        }
    }

    $xlsx->addRow(array(array('v' => '', 's' => 0, 't' => 's')));
    $dataRowCount = empty($agreements) ? 1 : count($agreements);
    $curRow = 4 + $dataRowCount + 1;

    $curRow++;
    $xlsx->addRow(array(array('v' => 'CERTIFICATION AGREEMENT REPORT SUMMARY', 's' => 16, 't' => 's')));
    $xlsx->merge('A' . $curRow . ':C' . $curRow);

    $summaryRows = array(
        array('TOTAL NUMBER OF AGREEMENTS', count($agreements), 'count'),
        array('TOTAL AGREEMENT AMOUNT', round($totalAmount, 2), 'money'),
        array('GREEN STATUS RECORDS', $greenCount, 'green'),
        array('RED STATUS RECORDS', $redCount, 'red'),
        array('OTHER STATUS RECORDS', $otherCount, 'count'),
    );
    foreach ($summaryRows as $line) {
        $curRow++;
        $labelStyle = ($line[2] === 'money') ? 14 : 18;
        $valueStyle = ($line[2] === 'money') ? 15 : 20;
        if ($line[2] === 'green') { $labelStyle = 12; $valueStyle = 20; }
        if ($line[2] === 'red') { $labelStyle = 25; $valueStyle = 20; }
        $xlsx->addRow(array(
            array('v' => $line[0], 's' => $labelStyle, 't' => 's'),
            null,
            array('v' => $line[1], 's' => $valueStyle, 't' => is_numeric($line[1]) ? 'n' : 's'),
        ));
        $xlsx->merge('A' . $curRow . ':B' . $curRow);
    }

    $curRow++;
    $xlsx->addRow(array(array('v' => 'AMOUNT IN WORDS: ' . strtoupper(convertToWord($totalAmount)), 's' => 17, 't' => 's')));
    $xlsx->merge('A' . $curRow . ':H' . $curRow);

    return $xlsx->toString('Agreements');
}
